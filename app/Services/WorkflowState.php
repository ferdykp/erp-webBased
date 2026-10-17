<?php
namespace App\Services;

use App\Models\{Booking,BookingBatch,DosimeterRecord,DosimeterDetail,ProductTest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Stage rules shared by UI and backend. Role privileges never bypass these rules. */
class WorkflowState
{
    public static function handedOver(Booking $booking): bool
    {
        if (DB::transactionLevel() > 0) return DB::table('order_dispatches')->where('booking_id',$booking->id)->where('status','dispatched')->lockForUpdate()->first() !== null;
        $booking->loadMissing('dispatchRecord');
        return $booking->dispatchRecord?->status === 'dispatched';
    }
    public static function canReceive(Booking $booking): bool
    {
        $booking->loadMissing(['placementDetails','batches','palletContents']);
        return in_array($booking->status,['pending','approved'],true) && !$booking->arrival_time
            && $booking->placementDetails->isEmpty() && $booking->batches->isEmpty()
            && $booking->palletContents->isEmpty() && !self::handedOver($booking);
    }
    public static function canArchive(Booking $booking): bool
    {
        $booking->loadMissing(['palletContents','placementDetails','batches']);
        $noStock=$booking->palletContents->isEmpty() && !$booking->placementDetails->contains(fn($p)=>$p->status === 'placed');
        return $noStock && (self::handedOver($booking) || (!$booking->arrival_time && in_array($booking->status,['pending','cancelled'],true) && $booking->batches->isEmpty()));
    }
    public static function canEdit(Booking $booking): bool
    {
        return $booking->status === 'pending' && self::canReceive($booking);
    }
    public static function canPlan(Booking $booking): bool
    {
        $booking->loadMissing(['batches','products']);
        return (bool)$booking->arrival_time && in_array($booking->status,['pending','approved','processing'],true)
            && !self::handedOver($booking) && (int)$booking->batches->sum('quantity') < (int)$booking->products->sum('quantity');
    }
    public static function dosimetryFinal(Booking $booking, ?int $recordId=null): bool
    {
        $query=DosimeterRecord::where('booking_id',$booking->id)->when($recordId,fn($q)=>$q->whereKey($recordId));
        if (DB::transactionLevel() > 0) $query->lockForUpdate();
        $record=$query->first();
        if (!$record) return false;
        $details=$record->details();
        if (DB::transactionLevel() > 0) $details->lockForUpdate();
        $rows=$details->get();
        return $record->tablet_quantity > 0 && $rows->count() === (int)$record->tablet_quantity
            && !$rows->contains(fn($d)=>blank($d->dosimeter_number) || $d->absorbance === null || $d->dose_kgy === null);
    }

    private static function require(bool $allowed): void
    {
        if (!$allowed) {
            $name=request()->route()?->getName() ?? '';
            $field=match(true) { str_contains($name,'dosimeter')=>'dosimetry', str_contains($name,'parameter')=>'parameters', str_contains($name,'dispatch')=>'dispatch', default=>'workflow' };
            throw ValidationException::withMessages([$field=>'This stage is closed or not yet available. Refresh the order and continue from its current stage.']);
        }
    }
    public static function enforce(Request $request, array $roots): void
    {
        if ($request->isMethodSafe() || ($request->isMethod('DELETE') && !$request->routeIs('admin.dosimeter.details.destroy'))) return;
        $name=$request->route()->getName() ?? '';
        $batch=null; $booking=null; $test=null;
        foreach ($roots as $root) {
            if ($root instanceof Booking) $booking=$root;
            if ($root instanceof BookingBatch) { $batch=$root; $booking=$root->booking; }
            if ($root instanceof DosimeterRecord) $booking=$root->booking;
            if ($root instanceof DosimeterDetail) $booking=Booking::find(DosimeterRecord::find($root->dosimeter_record_id)?->booking_id);
            if ($root instanceof ProductTest) $test=$root;
        }
        // Lock parent first, matching receiving, production and dispatch lock ordering.
        if ($booking) $booking=Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
        if ($batch) $batch=BookingBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
        if ($test) {
            $test=ProductTest::whereKey($test->id)->lockForUpdate()->firstOrFail();
            if (str_contains($name,'dosimeters.update')) {
                self::require(!$test->dosimeters()->exists() || $test->dosimeters()->where(fn($q)=>$q->whereNull('absorbance')->orWhereNull('dose_kgy')->orWhereNull('dosimeter_number'))->exists());
            } else self::require($test->status !== 'completed');
        }
        if ($booking) {
            self::require(!self::handedOver($booking));
            self::require($booking->status !== 'cancelled');
            if (in_array($name,['admin.bookings.update','admin.bookings.update-status'],true)) self::require(self::canEdit($booking));
            if (str_contains($name,'dosimeter')) self::require($booking->status === 'completed' && !self::dosimetryFinal($booking, is_numeric($request->route('record_id') ?? $request->input('record_id')) ? (int)($request->route('record_id') ?? $request->input('record_id')) : null));
            if (str_contains($name,'relocat')) self::require(!$booking->batches()->whereIn('status',['processing','done'])->exists());
            if (str_contains($name,'quality.review')) self::require(DB::table('quality_reviews')->where('booking_id',$booking->id)->lockForUpdate()->first() === null);
        }
        if ($batch) {
            self::require(!in_array($batch->status,['done','cancelled'],true));
            if (str_contains($name,'parameter') || str_contains($name,'templates.apply')) self::require(in_array($batch->status,['pending','waiting'],true));
        }
    }
}

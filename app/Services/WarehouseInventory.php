<?php

namespace App\Services;

use App\Models\{Booking, Pallet, PalletContent, PlacementDetail};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseInventory
{
    public static function audit(string $action, string $type, int $id, array $details = []): void
    {
        DB::table('operational_audit_events')->insert([
            'admin_id' => auth('admin')->id(), 'action' => $action, 'subject_type' => $type,
            'subject_id' => $id, 'details' => json_encode($details), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public static function assertConsistent(Pallet $slot): void
    {
        $contents = $slot->contents()->lockForUpdate()->get();
        if ($contents->contains(fn ($row) => $row->quantity <= 0)
            || (int) $slot->filled_boxes !== (int) $contents->sum('quantity')
            || $slot->status !== ($contents->isEmpty() ? 'empty' : 'filled')) {
            throw ValidationException::withMessages(['inventory' => "Inventory at Line {$slot->line} / Slot {$slot->slot_section} needs reconciliation."]);
        }
    }

    public static function sync(Pallet $slot): void
    {
        $quantity = (int) $slot->contents()->orderBy('id')->lockForUpdate()->get()->sum('quantity');
        // Legacy cache only. Display quantities grouped by unit from contents, never this mixed total.
        $slot->update(['filled_boxes' => $quantity, 'status' => $quantity ? 'filled' : 'empty', 'current_booking_id' => null]);
    }

    public function receive(int $bookingId, array $allocations, array $staff): void
    {
        DB::transaction(function () use ($bookingId, $allocations, $staff) {
            $booking = Booking::whereKey($bookingId)->lockForUpdate()->firstOrFail();
            if ($booking->booking_type !== 'regular' || !WorkflowState::canReceive($booking)) {
                throw ValidationException::withMessages(['receiving' => 'This order has already been received or cannot receive goods.']);
            }
            $products = $booking->products()->lockForUpdate()->get()->keyBy('id');
            $requested = collect($allocations)->groupBy('product_id');
            if ($requested->keys()->map(fn ($id) => (int) $id)->sort()->values()->all() !== $products->keys()->sort()->values()->all()) {
                throw ValidationException::withMessages(['allocations' => 'Allocate every ordered product, using products from this order only.']);
            }
            foreach ($products as $product) {
                if ((int) $requested[$product->id]->sum('quantity') !== (int) $product->quantity) {
                    throw ValidationException::withMessages(['allocations' => "Allocated quantity must match {$product->product_name}: {$product->quantity} {$product->unit}."]);
                }
            }
            $previousSlots = SuperadminAccess::overriding() ? PalletContent::where('booking_id',$bookingId)->pluck('pallet_id') : collect();
            $ids = collect($allocations)->pluck('pallet_id')->merge($previousSlots)->unique()->sort();
            $slots = Pallet::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if ($slots->count() !== $ids->count()) throw ValidationException::withMessages(['allocations' => 'A selected warehouse location no longer exists.']);
            foreach ($slots as $slot) { if (SuperadminAccess::overriding()) self::sync($slot); self::assertConsistent($slot); }
            if (SuperadminAccess::overriding()) {
                PalletContent::where('booking_id',$bookingId)->delete();
                PlacementDetail::where('booking_id',$bookingId)->where('status','placed')->delete();
            }
            foreach ($allocations as $i => $allocation) {
                $product = $products[$allocation['product_id']];
                $slot = $slots[$allocation['pallet_id']];
                if ((int) $allocation['quantity'] <= 0) throw ValidationException::withMessages(['allocations' => 'Quantities must be positive whole numbers.']);
                $ownership = ['booking_id' => $bookingId, 'booking_product_id' => $product->id,
                    'unit' => $product->unit, 'quantity' => (int) $allocation['quantity']];
                PalletContent::create($ownership + ['pallet_id' => $slot->id, 'product_name' => $product->product_name]);
                PlacementDetail::create($ownership + ['line' => $slot->line, 'slot_section' => $slot->slot_section, 'sequence' => $i + 1, 'status' => 'placed']);
            }
            foreach ($slots as $slot) self::sync($slot);
            $booking->update($staff + ['arrival_time' => now()]);
            if (SuperadminAccess::overriding()) {
                DB::table('order_dispatches')->where('booking_id',$bookingId)->where('status','dispatched')
                    ->update(['status'=>'reopened','updated_at'=>now()]);
            }
            self::audit('goods.received', 'booking', $bookingId, ['allocations' => $allocations]);
        }, 3);
    }
}

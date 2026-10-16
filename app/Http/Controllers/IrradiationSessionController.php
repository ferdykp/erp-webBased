<?php

namespace App\Http\Controllers;

use App\Models\{Booking, BookingBatch};
use App\Services\WarehouseInventory;
use Carbon\Carbon;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IrradiationSessionController extends Controller
{
    public function store(Request $request, BookingBatch $batch): RedirectResponse
    {
        abort_unless(in_array(auth('admin')->user()?->role, ['superadmin', 'production'], true), 403);
        $data = $request->validate([
            'started_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'ended_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:started_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        // datetime-local inputs are facility local time, independent of the browser timezone.
        $start = Carbon::parse($data['started_at'], config('app.timezone'));
        $end = Carbon::parse($data['ended_at'], config('app.timezone'));
        if ($end->isFuture()) throw ValidationException::withMessages(['ended_at' => 'Record a run only after it has finished. End time cannot be in the future.']);
        DB::transaction(function () use ($batch, $data, $start, $end) {
            $booking = Booking::whereKey($batch->booking_id)->lockForUpdate()->firstOrFail();
            $locked = BookingBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'processing' || !$booking->arrival_time || $booking->status !== 'processing') {
                throw ValidationException::withMessages(['started_at' => 'Start production first, then record the completed run.']);
            }
            if (!$locked->production_line_id || $locked->target_dose <= 0 || $locked->beam_speed <= 0 || !in_array($locked->loading_mode, ['single_side','double_side'], true)) {
                throw ValidationException::withMessages(['started_at' => 'Complete valid machine settings before recording a run.']);
            }
            DB::table('production_lines')->where('id', $locked->production_line_id)->lockForUpdate()->first();
            $conflict = DB::table('irradiation_sessions as s')
                ->join('booking_batches as b', 'b.id', '=', 's.booking_batch_id')
                ->join('bookings as o', 'o.id', '=', 'b.booking_id')
                ->where('s.production_line_id', $locked->production_line_id)->whereNull('s.voided_at')
                ->where('s.started_at', '<', $end->toDateTimeString())
                ->where('s.ended_at', '>', $start->toDateTimeString())
                ->select('s.id', 's.started_at', 's.ended_at', 'b.batch_number', 'o.booking_code')->first();
            if ($conflict) {
                throw ValidationException::withMessages(['started_at' => "Run conflicts with {$conflict->booking_code} / Batch {$conflict->batch_number}, run #{$conflict->id}: {$conflict->started_at} to {$conflict->ended_at} (".config('app.timezone')."). Check the actual times or correct the incorrect run. A run may start exactly when another ends."]);
            }
            $id = DB::table('irradiation_sessions')->insertGetId([
                'booking_batch_id' => $locked->id, 'production_line_id' => $locked->production_line_id,
                'started_at' => $start->toDateTimeString(), 'ended_at' => $end->toDateTimeString(),
                'duration_minutes' => (int) $start->diffInMinutes($end),
                'target_dose' => $locked->target_dose, 'beam_speed' => $locked->beam_speed,
                'freq' => $locked->freq, 'scan_gear' => $locked->scan_gear, 'loading_mode' => $locked->loading_mode,
                'notes' => $data['notes'] ?? null, 'recorded_by' => auth('admin')->id(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->syncTimes($locked);
            WarehouseInventory::audit('irradiation.recorded', 'booking_batch', $locked->id, ['session_id' => $id, 'start' => $start, 'end' => $end]);
        }, 3);
        return redirect()->route('admin.workspace.production-board', ['batch' => $batch->id])->with('success', 'Run recorded. Add another run or finish the batch.');
    }

    public function void(Request $request, BookingBatch $batch, int $session): RedirectResponse
    {
        abort_unless(in_array(auth('admin')->user()?->role, ['superadmin', 'production'], true), 403);
        $data = $request->validate(['reason' => 'required|string|min:8|max:2000', 'confirmed' => 'accepted']);
        DB::transaction(function () use ($batch, $session, $data) {
            Booking::whereKey($batch->booking_id)->lockForUpdate()->firstOrFail();
            $locked = BookingBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'processing') throw ValidationException::withMessages(['reason' => 'Completed batch history is locked. Corrections require a formal quality review.']);
            DB::table('production_lines')->where('id', $locked->production_line_id)->lockForUpdate()->first();
            $run = DB::table('irradiation_sessions')->where('id', $session)->where('booking_batch_id', $batch->id)->lockForUpdate()->first();
            abort_unless($run, 404);
            if ($run->voided_at) throw ValidationException::withMessages(['reason' => 'This run has already been voided.']);
            DB::table('irradiation_sessions')->where('id', $session)->update(['voided_at' => now(), 'voided_by' => auth('admin')->id(), 'void_reason' => $data['reason'], 'updated_at' => now()]);
            $this->syncTimes($locked);
            WarehouseInventory::audit('irradiation.voided', 'booking_batch', $batch->id, ['session_id' => $session, 'original' => $run, 'reason' => $data['reason']]);
        }, 3);
        return redirect()->route('admin.workspace.production-board', ['batch' => $batch->id])->with('success', 'Incorrect run voided. Its history is preserved. Record the corrected run.');
    }

    private function syncTimes(BookingBatch $batch): void
    {
        $runs = DB::table('irradiation_sessions')->where('booking_batch_id', $batch->id)->whereNull('voided_at');
        $batch->update(['offline_at' => (clone $runs)->min('started_at'), 'total_duration' => (int) $runs->sum('duration_minutes')]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingBatch;
use App\Services\WarehouseInventory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IrradiationSessionController extends Controller
{
    /**
     * Record a completed irradiation run.
     *
     * Production is recorded manually.
     * Overlapping sessions are allowed but flagged in audit.
     */
    public function store(
        Request $request,
        BookingBatch $batch
    ): RedirectResponse {
        $this->authorizeProduction();

        /*
        |--------------------------------------------------------------------------
        | 1. Validate Input
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([
            'started_at' => [
                'required',
                'date_format:Y-m-d\TH:i',
            ],
            'ended_at' => [
                'required',
                'date_format:Y-m-d\TH:i',
                'after:started_at',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. Parse Facility Local Time
        |--------------------------------------------------------------------------
        */

        $timezone = config('app.timezone', 'Asia/Jakarta');

        $start = Carbon::createFromFormat(
            '!Y-m-d\TH:i',
            $data['started_at'],
            $timezone
        );

        $end = Carbon::createFromFormat(
            '!Y-m-d\TH:i',
            $data['ended_at'],
            $timezone
        );

        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages([
                'ended_at' => 'End time must be after start time.',
            ]);
        }

        if ($end->isFuture()) {
            throw ValidationException::withMessages([
                'ended_at' =>
                'Record a run only after it has finished.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Save Run Inside Transaction
        |--------------------------------------------------------------------------
        */

        $result = DB::transaction(function () use (
            $batch,
            $data,
            $start,
            $end,
            $timezone
        ) {
            $booking = Booking::whereKey($batch->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            $locked = BookingBatch::whereKey($batch->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | 4. Validate Production State
            |--------------------------------------------------------------------------
            */

            if (
                $locked->status !== 'processing' ||
                !$booking->arrival_time ||
                $booking->status !== 'processing'
            ) {
                throw ValidationException::withMessages([
                    'started_at' =>
                    'Start production before recording a completed run.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 5. Validate Machine Settings
            |--------------------------------------------------------------------------
            */

            if (
                !$locked->production_line_id ||
                $locked->target_dose <= 0 ||
                $locked->beam_speed <= 0 ||
                !in_array(
                    $locked->loading_mode,
                    ['single_side', 'double_side'],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'started_at' =>
                    'Complete machine settings before recording a run.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 6. Lock Machine
            |--------------------------------------------------------------------------
            |
            | Preserve transaction consistency.
            | This does not block overlapping manual records.
            |
            */

            DB::table('production_lines')
                ->where('id', $locked->production_line_id)
                ->lockForUpdate()
                ->first();

            /*
            |--------------------------------------------------------------------------
            | 7. Detect Overlapping Runs
            |--------------------------------------------------------------------------
            |
            | Overlap is informational.
            | No automatic rejection.
            |
            */

            $conflicts = DB::table('irradiation_sessions as s')
                ->join(
                    'booking_batches as b',
                    'b.id',
                    '=',
                    's.booking_batch_id'
                )
                ->join(
                    'bookings as o',
                    'o.id',
                    '=',
                    'b.booking_id'
                )
                ->where(
                    's.production_line_id',
                    $locked->production_line_id
                )
                ->whereNull('s.voided_at')
                ->where(
                    's.started_at',
                    '<',
                    $end->toDateTimeString()
                )
                ->where(
                    's.ended_at',
                    '>',
                    $start->toDateTimeString()
                )
                ->select(
                    's.id',
                    's.started_at',
                    's.ended_at',
                    'b.batch_number',
                    'o.booking_code'
                )
                ->orderBy('s.started_at')
                ->get();

            $overlapSummary = $conflicts
                ->map(function ($conflict) {
                    return [
                        'session_id' => $conflict->id,
                        'booking_code' => $conflict->booking_code,
                        'batch_number' => $conflict->batch_number,
                        'started_at' => $conflict->started_at,
                        'ended_at' => $conflict->ended_at,
                    ];
                })
                ->all();

            /*
            |--------------------------------------------------------------------------
            | 8. Insert Actual Irradiation Run
            |--------------------------------------------------------------------------
            */

            $sessionId = DB::table('irradiation_sessions')
                ->insertGetId([
                    'booking_batch_id' => $locked->id,
                    'production_line_id' =>
                    $locked->production_line_id,

                    'started_at' =>
                    $start->toDateTimeString(),

                    'ended_at' =>
                    $end->toDateTimeString(),

                    'duration_minutes' =>
                    (int) $start->diffInMinutes($end),

                    'target_dose' => $locked->target_dose,
                    'beam_speed' => $locked->beam_speed,
                    'freq' => $locked->freq,
                    'scan_gear' => $locked->scan_gear,
                    'loading_mode' => $locked->loading_mode,

                    'notes' => $data['notes'] ?? null,

                    'recorded_by' => auth('admin')->id(),

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | 9. Synchronize Batch Times
            |--------------------------------------------------------------------------
            */

            $this->syncTimes($locked);

            /*
            |--------------------------------------------------------------------------
            | 10. Audit Recording
            |--------------------------------------------------------------------------
            |
            | Preserve the actual recorded values and overlap references.
            |
            */

            WarehouseInventory::audit(
                'irradiation.recorded',
                'booking_batch',
                $locked->id,
                [
                    'session_id' => $sessionId,
                    'production_line_id' =>
                    $locked->production_line_id,

                    'started_at' =>
                    $start->toDateTimeString(),

                    'ended_at' =>
                    $end->toDateTimeString(),

                    'timezone' => $timezone,

                    'duration_minutes' =>
                    (int) $start->diffInMinutes($end),

                    'overlap_detected' =>
                    count($overlapSummary) > 0,

                    'overlapping_runs' => $overlapSummary,
                ]
            );

            return [
                'session_id' => $sessionId,
                'overlap_count' => count($overlapSummary),
            ];
        }, 3);

        /*
        |--------------------------------------------------------------------------
        | 11. Redirect with Feedback
        |--------------------------------------------------------------------------
        */

        if ($result['overlap_count'] > 0) {
            return redirect()
                ->route(
                    'admin.workspace.production-board',
                    ['batch' => $batch->id]
                )
                ->with(
                    'warning',
                    'Irradiation run saved successfully. ' .
                        'Warning: The recorded time overlaps with ' .
                        $result['overlap_count'] .
                        ' other run(s) on the same machine. ' .
                        'Please verify the production records.'
                );
        }

        return redirect()
            ->route(
                'admin.workspace.production-board',
                ['batch' => $batch->id]
            )
            ->with(
                'success',
                'Irradiation run saved successfully. ' .
                    'Add another run or finish the batch.'
            );
    }

    /**
     * Void an incorrect irradiation run.
     *
     * Preserve the original record and audit history.
     */
    public function void(
        Request $request,
        BookingBatch $batch,
        int $session
    ): RedirectResponse {
        $this->authorizeProduction();

        /*
        |--------------------------------------------------------------------------
        | 1. Validate Correction Request
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([
            'reason' => [
                'required',
                'string',
                'min:8',
                'max:2000',
            ],
            'confirmed' => [
                'accepted',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. Void Inside Transaction
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $batch,
            $session,
            $data
        ) {
            Booking::whereKey($batch->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            $locked = BookingBatch::whereKey($batch->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'processing') {
                throw ValidationException::withMessages([
                    'reason' =>
                    'Completed batch history is locked. ' .
                        'Corrections require an authorized review.',
                ]);
            }

            DB::table('production_lines')
                ->where('id', $locked->production_line_id)
                ->lockForUpdate()
                ->first();

            $run = DB::table('irradiation_sessions')
                ->where('id', $session)
                ->where('booking_batch_id', $locked->id)
                ->lockForUpdate()
                ->first();

            abort_unless($run, 404);

            if ($run->voided_at !== null) {
                throw ValidationException::withMessages([
                    'reason' =>
                    'This irradiation run has already been voided.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Soft Void Run
            |--------------------------------------------------------------------------
            */

            DB::table('irradiation_sessions')
                ->where('id', $session)
                ->update([
                    'voided_at' => now(),
                    'voided_by' => auth('admin')->id(),
                    'void_reason' => $data['reason'],
                    'updated_at' => now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | 4. Synchronize Batch Times
            |--------------------------------------------------------------------------
            */

            $this->syncTimes($locked);

            /*
            |--------------------------------------------------------------------------
            | 5. Audit Correction
            |--------------------------------------------------------------------------
            */

            WarehouseInventory::audit(
                'irradiation.voided',
                'booking_batch',
                $locked->id,
                [
                    'session_id' => $session,
                    'original' => (array) $run,
                    'reason' => $data['reason'],
                    'voided_by' => auth('admin')->id(),
                ]
            );
        }, 3);

        return redirect()
            ->route(
                'admin.workspace.production-board',
                ['batch' => $batch->id]
            )
            ->with(
                'success',
                'Incorrect run voided. ' .
                    'Its history has been preserved. ' .
                    'You can now record the corrected run.'
            );
    }

    /**
     * Synchronize recorded irradiation times with the batch.
     */
    private function syncTimes(BookingBatch $batch): void
    {
        $runs = DB::table('irradiation_sessions')
            ->where('booking_batch_id', $batch->id)
            ->whereNull('voided_at');

        $firstStart = (clone $runs)->min('started_at');

        $totalMinutes = (clone $runs)->sum(
            'duration_minutes'
        );

        $batch->update([
            'offline_at' => $firstStart,
            'total_duration' => (int) $totalMinutes,
        ]);
    }

    /**
     * Ensure only authorized production personnel
     * can manage irradiation runs.
     */
    private function authorizeProduction(): void
    {
        abort_unless(
            in_array(
                auth('admin')->user()?->role,
                ['superadmin', 'production'],
                true
            ),
            403
        );
    }
}

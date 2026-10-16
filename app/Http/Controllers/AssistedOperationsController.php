<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Pallet;
use App\Models\PalletContent;
use App\Models\Porter;
use App\Models\ProductionLine;
use App\Models\WarehousePic;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssistedOperationsController extends Controller
{
    /**
     * Display the assisted receiving workspace.
     *
     * Only bookings that have not been received can
     * access the receiving form.
     */
    public function receive(Booking $booking): View|RedirectResponse
    {
        $booking->load([
            'products',
            'customer',
            'placementDetails',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 1. Validate Booking Type
        |--------------------------------------------------------------------------
        */

        if ($booking->booking_type !== 'regular') {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Validate Product Count
        |--------------------------------------------------------------------------
        |
        | Smart Receiving currently supports one product per booking.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | 3. Check Existing Receiving Records
        |--------------------------------------------------------------------------
        |
        | Receiving is considered recorded when:
        |
        | - Arrival time exists.
        | - Placement details exist.
        | - Pallet contents exist for the booking.
        |
        | This prevents duplicate receiving when historical
        | records are partially inconsistent.
        |
        */

        $hasReceivingRecord =
            $booking->arrival_time !== null
            || $booking->placementDetails->isNotEmpty()
            || PalletContent::query()
            ->where('booking_id', $booking->id)
            ->exists();

        /*
        |--------------------------------------------------------------------------
        | 4. Check Receiving Eligibility
        |--------------------------------------------------------------------------
        */

        $isReceivingAllowed =
            in_array(
                $booking->status,
                ['pending', 'approved'],
                true
            )
            && ! $hasReceivingRecord;

        if (! $isReceivingAllowed) {
            return redirect()
                ->route('admin.bookings')
                ->with(
                    'info',
                    $hasReceivingRecord
                        ? 'Goods receiving has already been recorded for this order. Review the order details before making any corrections.'
                        : 'This order is no longer eligible for goods receiving.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Find Available Warehouse Locations
        |--------------------------------------------------------------------------
        |
        | A location is available only when:
        |
        | - Pallet status is empty.
        | - No booking owns the location.
        | - No filled quantity is recorded.
        | - No active pallet contents exist.
        |
        | This query is for displaying available locations.
        | The receiving transaction must still lock and
        | revalidate the selected pallet before updating it.
        |
        */

        // Semua lokasi boleh menerima beberapa booking; stok fisik tetap diverifikasi operator.
        $slots = Pallet::query()
            ->with(['contents.booking'])->withSum('contents as active_quantity', 'quantity')
            ->orderBy('line')
            ->orderBy('slot_section')
            ->get(['id', 'line', 'slot_section', 'status', 'filled_boxes', 'current_booking_id']);

        /*
        |--------------------------------------------------------------------------
        | 6. Load Active Warehouse Staff
        |--------------------------------------------------------------------------
        */

        $porters = Porter::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $warehousePics = WarehousePic::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 7. Render Smart Receiving Workspace
        |--------------------------------------------------------------------------
        */

        return view($booking->products->count() === 1 ? 'admin.workspace.smart-receiving' : 'admin.workspace.multi-receiving', [
            'booking' => $booking,
            'slots' => $slots,
            'porters' => $porters,
            'warehousePics' => $warehousePics,
        ]);
    }

    /**
     * Display the assisted batch planning workspace.
     */
    public function batchPlan(\Illuminate\Http\Request $request, Booking $booking): View|RedirectResponse
    {
        $booking->load([
            'products',
            'batches',
            'customer',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 1. Validate Booking Type
        |--------------------------------------------------------------------------
        */

        if ($booking->booking_type !== 'regular') {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Check Batch Planning Eligibility
        |--------------------------------------------------------------------------
        |
        | Batch planning becomes available after:
        |
        | - Goods have been received.
        | - The booking has been approved.
        |
        */

        $isEligible =
            $booking->arrival_time !== null
            && in_array(
                $booking->status,
                ['pending', 'approved', 'processing'],
                true
            );

        if (! $isEligible) {
            return redirect()
                ->route('admin.bookings')
                ->with(
                    'warning',
                    'Receive goods before setting up production.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Calculate Allocated Quantity
        |--------------------------------------------------------------------------
        |
        | Preserve existing behavior:
        | Pending batches do not count as allocated.
        |
        */

        $selectedProduct = $booking->products->firstWhere('id', $request->integer('product')) ?? $booking->products->first();
        abort_unless($selectedProduct, 422);
        $allocated = $booking->batches
            ->filter(fn ($batch) => (int) $batch->booking_product_id === $selectedProduct->id || (!$batch->booking_product_id && $booking->products->count() === 1))
            ->sum('quantity');

        $totalQuantity = (int) $selectedProduct->quantity;

        $remaining = max(
            0,
            $totalQuantity - (int) $allocated
        );

        /*
        |--------------------------------------------------------------------------
        | 4. Load Machines And Staff
        |--------------------------------------------------------------------------
        */

        $machines = ProductionLine::query()
            ->orderBy('name')
            ->get();

        $porters = Porter::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 5. Render Smart Batch Planning
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.workspace.smart-batch-planning',
            [
                'booking' => $booking,
                'remaining' => $remaining,
                'selectedProduct' => $selectedProduct,
                'machines' => $machines,
                'porters' => $porters,
            ]
        );
    }
}

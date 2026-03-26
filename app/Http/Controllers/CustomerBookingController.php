<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BookingSlot;
use App\Models\BookingProduct;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;



class CustomerBookingController extends Controller
{

    public function index()
    {
        $data = Booking::with(['products'])
            ->where('customer_id', auth('customer')->id())
            ->latest()
            ->paginate(10);

        return view('customer.dashboard', compact('data'));
    }

    public function create(BookingSlot $slot)
    {
        return view('customer.booking.create');
    }


    // public function store(Request $request)
    // {
    //     // dd(auth('customer')->user(), auth('customer')->user()->customer);
    //     $request->validate([
    //         'product_name' => 'required|string',
    //         'product_type' => 'required|string',
    //         'quantity' => 'required|integer|min:1',
    //         'unit' => 'required|string',
    //         // 'target_dose' => 'required|string',
    //         'dmin' => 'required|string',
    //         'dmax' => 'required|string',
    //         'gross_weight_per_pcs' => 'required|string',
    //         'expect_temp' => 'nullable|string',
    //         // 'dimension_pack' => 'required|string',
    //         'dim_length' => 'required|numeric',
    //         'dim_width' => 'required|numeric',
    //         'dim_height' => 'required|numeric',

    //     ]);

    //     $dimension_string = $request->dim_length . 'x' . $request->dim_width . 'x' . $request->dim_height . 'cm';

    //     DB::transaction(function () use ($request, $dimension_string) {

    //         $booking = Booking::create([
    //             'booking_code' => 'EB-' . strtoupper(\Str::random(6)),
    //             // 'ticket_code' => 'TCK-' . strtoupper(\Str::random(8)),
    //             // 'customer_id' => auth('customer')->id(),
    //             'customer_id' => auth('customer')->user()->customer->id,
    //             'user_id' => Auth::guard('customer')->id(),
    //             'status' => 'pending',
    //             'qr_token' => \Str::uuid(),
    //         ]);

    //         BookingProduct::create([
    //             'booking_id' => $booking->id,
    //             'product_name' => $request->product_name,
    //             'product_type' => $request->product_type,
    //             'quantity' => $request->quantity,
    //             'unit' => $request->unit,
    //             // 'target_dose' => $request->target_dose,
    //             'dmin' => $request->dmin,
    //             'dmax' => $request->dmax,
    //             // 'dimension_pack' => $request->dimension_pack,
    //             'dimension_pack' => $dimension_string,
    //             'gross_weight_per_pcs' => $request->gross_weight_per_pcs,
    //             'expect_temp' => $request->expect_temp
    //         ]);
    //         // $booking->qr_token = Str::uuid();
    //         $booking->save();
    //     });

    //     return redirect()->route('customer.dashboard')
    //         ->with('success', 'Booking created successfully');
    // }
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string',
            'product_type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string',
            // 'target_dose' => 'required|string',
            'dmin' => 'required|string',
            'dmax' => 'required|string',
            'gross_weight_per_pcs' => 'required|string',
            'expect_temp' => 'nullable|string',
            // 'dimension_pack' => 'required|string',
            'dim_length' => 'required|numeric',
            'dim_width' => 'required|numeric',
            'dim_height' => 'required|numeric',

        ]);

        $dimension_string = $request->dim_length . 'x' . $request->dim_width . 'x' . $request->dim_height . 'cm';

        DB::transaction(function () use ($request, $dimension_string) {

            $booking = Booking::create([
                'booking_code' => 'EB-' . strtoupper(\Str::random(6)),
                // 'ticket_code' => 'TCK-' . strtoupper(\Str::random(8)),
                'customer_id' => auth('customer')->id(),
                'status' => 'pending',
                'qr_token' => \Str::uuid(),
            ]);

            BookingProduct::create([
                'booking_id' => $booking->id,
                'product_name' => $request->product_name,
                'product_type' => $request->product_type,
                'quantity' => $request->quantity,
                'unit' => $request->unit,
                // 'target_dose' => $request->target_dose,
                'dmin' => $request->dmin,
                'dmax' => $request->dmax,
                // 'dimension_pack' => $request->dimension_pack,
                'dimension_pack' => $dimension_string,
                'gross_weight_per_pcs' => $request->gross_weight_per_pcs,
                'expect_temp' => $request->expect_temp
            ]);
            // $booking->qr_token = Str::uuid();
            $booking->save();
        });

        return redirect()->route('customer.dashboard')
            ->with('success', 'Booking created successfully');
    }
    public function show($id)
    {
        $booking = Booking::with(['products'])
            ->findOrFail($id);

        return view('customer.booking.booking_detail', compact('booking'));
    }

    public function print($id)
    {
        $booking = Booking::with(['products'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('customer.booking.ticket_pdf', compact('booking'));

        return $pdf->stream('ticket-' . $booking->ticket_code . '.pdf');
    }

    public function destroy(int $id)
    {
        Booking::findOrFail($id)->delete();

        return redirect('customer.index');
    }
}

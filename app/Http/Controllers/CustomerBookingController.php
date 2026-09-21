<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BookingProduct;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;



class CustomerBookingController extends Controller
{

    public function index()
    {
        $user = Auth::guard('customer')->user();
        abort_unless($user && $user->customer, 403);

        $data = Booking::with('products')
            ->where('customer_id', $user->customer->id)
            ->latest()
            ->paginate(10);

        return view('customer.dashboard.index', compact('data'));
    }

    public function create()
    {
        $user = Auth::guard('customer')->user();
        if (!$user?->customer || !$user->customer->profile_completed) {
            return redirect()->route('customer.profile.complete')
                ->withErrors(['error' => 'Lengkapi profile customer sebelum membuat booking.']);
        }

        return view('customer.booking.create');
    }
    public function store(Request $request)
    {
        $user = Auth::guard('customer')->user();
        if (!$user?->customer || !$user->customer->profile_completed) {
            return redirect()->route('customer.profile.complete')
                ->withErrors(['error' => 'Lengkapi profile customer sebelum membuat booking.']);
        }

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'dmin' => 'required|numeric|min:0',
            'dmax' => 'nullable|numeric|gte:dmin',
            'dim_length' => 'required|numeric|min:0.001',
            'dim_width' => 'required|numeric|min:0.001',
            'dim_height' => 'required|numeric|min:0.001',
            'net_weight_pcs' => 'nullable|numeric|min:0',
            'gross_weight_per_pcs' => 'required|numeric|min:0',
            'expect_temp' => 'nullable|string|max:100',
        ]);

        $quantity = (int) $validated['quantity'];
        $volumePerPcs = (float) $validated['dim_length'] * (float) $validated['dim_width'] * (float) $validated['dim_height'];
        $volumeTotal = $volumePerPcs * $quantity;
        $netPerPcs = (float) ($validated['net_weight_pcs'] ?? 0);
        $grossPerPcs = (float) $validated['gross_weight_per_pcs'];
        $totalNet = $netPerPcs * $quantity;
        $totalGross = $grossPerPcs * $quantity;
        $dimension = rtrim(rtrim((string) $validated['dim_length'], '0'), '.') . 'x' .
            rtrim(rtrim((string) $validated['dim_width'], '0'), '.') . 'x' .
            rtrim(rtrim((string) $validated['dim_height'], '0'), '.');

        $booking = DB::transaction(function () use ($user, $validated, $quantity, $volumePerPcs, $volumeTotal, $netPerPcs, $grossPerPcs, $totalNet, $totalGross, $dimension) {
            $booking = Booking::create([
                'user_id' => $user->id,
                'customer_id' => $user->customer->id,
                'booking_code' => $this->nextBookingCode(),
                'booking_type' => 'regular',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'qr_token' => Str::uuid(),
                'total_price' => 0,
            ]);

            BookingProduct::create([
                'booking_id' => $booking->id,
                'product_name' => $validated['product_name'],
                'product_type' => $validated['product_type'],
                'quantity' => $quantity,
                'unit' => $validated['unit'],
                'dmin' => $validated['dmin'],
                'dmax' => $validated['dmax'] ?? null,
                'dimension_pack' => $dimension,
                'vol_per_pcs' => $volumePerPcs,
                'vol_total' => $volumeTotal,
                'net_weight_pcs' => $netPerPcs,
                'total_net_weight' => $totalNet,
                'gross_weight_per_pcs' => $grossPerPcs,
                'total_gross_weight' => $totalGross,
                'expect_temp' => $validated['expect_temp'] ?? null,
                'density_gross' => $volumeTotal > 0 ? $totalGross / $volumeTotal : 0,
                'density_nett' => $volumeTotal > 0 ? $totalNet / $volumeTotal : 0,
            ]);

            return $booking;
        });

        return redirect()->route('customer.dashboard')->with('success', 'Order #' . $booking->booking_code . ' berhasil dibuat.');
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

    public function show($id)
    {
        $user = Auth::guard('customer')->user();
        $booking = Booking::with('products')
            ->where('customer_id', $user->customer->id)
            ->findOrFail($id);

        return view('customer.booking.booking_detail', compact('booking'));
    }

    public function print($id)
    {
        $user = Auth::guard('customer')->user();
        $booking = Booking::with('products')
            ->where('customer_id', $user->customer->id)
            ->findOrFail($id);

        $pdf = Pdf::loadView('customer.booking.ticket_pdf', compact('booking'));
        return $pdf->stream('ticket-' . $booking->booking_code . '.pdf');
    }

    public function destroy(int $id)
    {
        $user = Auth::guard('customer')->user();
        $booking = Booking::where('customer_id', $user->customer->id)->findOrFail($id);
        abort_if(!in_array($booking->status, ['pending', 'cancelled'], true), 403);
        $booking->delete();

        return redirect()->route('customer.dashboard')->with('success', 'Booking deleted.');
    }

    private function nextBookingCode(): string
    {
        $prefix = now()->format('ymd');
        $lastCode = Booking::where('booking_code', 'like', $prefix . '%')
            ->orderByDesc('booking_code')
            ->value('booking_code');
        $lastSequence = $lastCode ? (int) substr($lastCode, strlen($prefix)) : 0;
        return $prefix . str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
    }

}

<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\BookingSlot;
use Illuminate\Http\Request;

class AdminSlotController extends Controller
{
    public function index()
    {
        $slots = BookingSlot::latest()->paginate(10);
        return view('admin.slots.index', compact('slots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'session' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'capacity' => 'required|integer|min:1',
        ]);
        $exists = BookingSlot::where('date', $request->date)
            ->where('session', $request->session)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Session already exists for this date');
        }

        BookingSlot::create([
            'date' => $request->date,
            'session' => $request->session,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'capacity' => $request->capacity,
            'booked_count' => 0,
            'status' => 'open',
        ]);

        return back()->with('success', 'Slot created successfully');
    }

    public function update(Request $request, BookingSlot $slot)
    {
        $request->validate([
            'date' => 'required|date',
            'session' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'capacity' => 'required|integer|min:1',
            'status' => 'required'
        ]);

        $slot->update([
            'date' => $request->date,
            'session' => $request->session,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'capacity' => $request->capacity,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Slot updated');
    }


    public function generate()
    {
        DB::transaction(function () {

            for ($i = 0; $i < 30; $i++) {

                $date = Carbon::today()->addDays($i);

                $sessions = [
                    ['Morning', '09:00', '11:00'],
                    ['Afternoon', '13:00', '15:00'],
                    ['Evening', '16:00', '18:00'],
                ];

                foreach ($sessions as $s) {

                    // prevent duplicate
                    if (!BookingSlot::where('date', $date)
                        ->where('session', $s[0])
                        ->exists()) {

                        BookingSlot::create([
                            'date' => $date,
                            'session' => $s[0],
                            'start_time' => $s[1],
                            'end_time' => $s[2],
                            'capacity' => 10,
                            'booked_count' => 0,
                            'status' => 'open'
                        ]);
                    }
                }
            }
        });

        return back()->with('success', '30 days slots generated');
    }

    public function calendar()
    {
        $slots = BookingSlot::all();

        $events = $slots->map(function ($slot) {
            return [
                'title' => $slot->session . ' (' . $slot->booked_count . '/' . $slot->capacity . ')',
                'start' => $slot->date . 'T' . $slot->start_time,
                'end' => $slot->date . 'T' . $slot->end_time,
                'color' => $slot->status == 'open' ? '#16a34a' : '#dc2626'
            ];
        });

        return view('admin.slots.calendar', compact('events'));
    }


    public function destroy(BookingSlot $slot)
    {
        $slot->delete();
        return back()->with('success', 'Slot deleted');
    }
}

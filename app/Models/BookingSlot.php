<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingSlot extends Model
{
    protected $fillable = [
        'session',
        'date',
        'start_time',
        'end_time',
        'capacity',
        'booked_count',
        'status'
    ];

    // public function bookings(): HasMany
    // {
    //     return $this->hasMany(Booking::class);
    // }
}

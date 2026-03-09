<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlacementDetail extends Model
{
    protected $fillable = [
        'booking_id',
        'sequence',    // 1, 2, 3...
        // 'pallet_use',
        'quantity',    // 30, 30, 14...
        'line',
        'slot_section',
        'status'       // 'planned', 'placed'
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

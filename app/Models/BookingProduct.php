<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingProduct extends Model
{
    protected $fillable = [
        'booking_id',
        'product_name',
        'product_type',
        'quantity',
        'unit',
        // 'target_dose'
        'dmin',
        'dmax',
        'dimension_pack',
        'gross_weight_per_pcs',
        'expect_temp',

        'vol_per_pcs',
        'vol_total',
        'net_weight_pcs',
        'total_net_weight',
        'gross_weight_pcs',
        'total_gross_weight'
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

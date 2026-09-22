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
        // 'gross_weight_pcs',
        'total_gross_weight',
        'density_gross',
        'density_nett'
    ];



    protected $casts = [
        'quantity' => 'integer',
        'dmin' => 'float',
        'dmax' => 'float',
        'vol_per_pcs' => 'float',
        'vol_total' => 'float',
        'net_weight_pcs' => 'float',
        'total_net_weight' => 'float',
        'gross_weight_per_pcs' => 'float',
        'total_gross_weight' => 'float',
        'density_gross' => 'float',
        'density_nett' => 'float',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

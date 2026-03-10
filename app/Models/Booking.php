<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'customer_id',
        'arrival_time',
        'pic_warehouse',
        'status',
        'qr_token'
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(BookingProduct::class, 'booking_id');
    }

    public function pallets(): HasMany
    {
        return $this->hasMany(Pallet::class, 'current_booking_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(BookingBatch::class);
    }

    // Di dalam class Booking
    public function placementDetails()
    {
        return $this->hasMany(PlacementDetail::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    // public function porter(): BelongsTo
    // {
    //     // Kita hubungkan kolom porter_name di bookings ke kolom name di porters
    //     return $this->belongsTo(Porter::class, 'porter_name', 'name');
    // }
}

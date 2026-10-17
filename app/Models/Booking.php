<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use \App\Models\Concerns\Recoverable;

    protected $fillable = [
        'booking_code',
        'booking_type',
        'customer_id',
        'user_id',
        'arrival_time',
        'pic_warehouse',
        'porter_name',
        'status',
        'qr_token',
        'payment_status',
        'total_price',
        'created_at'
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
        'total_price' => 'float',
    ];

    public function dispatchRecord(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\OrderDispatch::class);
    }
    public function palletContents(): HasMany
    {
        return $this->hasMany(\App\Models\PalletContent::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function products(): HasMany
    {
        return $this->hasMany(BookingProduct::class, 'booking_id');
    }

    public function pallets(): BelongsToMany
    {
        // Keanggotaan pallet berasal dari isi aktif, bukan current_booking_id tunggal.
        return $this->belongsToMany(Pallet::class, 'pallet_contents', 'booking_id', 'pallet_id')->distinct();
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehousePic()
    {
        return $this->belongsTo(WarehousePic::class, 'pic_warehouse');
    }
}

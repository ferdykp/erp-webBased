<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Porter extends Model
{
    protected $fillable = ['name', 'phone', 'is_active'];

    // Relasi ke Booking (Sebagai PIC Utama)
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'porter_name', 'name');
    }

    // Relasi ke BookingBatch (Sebagai Porter per Batch)
    public function batches(): HasMany
    {
        return $this->hasMany(BookingBatch::class, 'porter_name', 'name');
    }
}

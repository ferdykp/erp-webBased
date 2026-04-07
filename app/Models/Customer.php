<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// class Customer extends Model
class Customer extends Authenticatable

{
    // app/Models/Customer.php
    protected $fillable = [
        'user_id',           // Pastikan ini ada
        'company_name',
        'industry',
        'email',
        'npwp',              // Tambahkan sesuai migrasi
        'phone',
        'website',
        'notes',
        'status',
        'profile_completed',
    ];

    // protected $hidden = [
    //     'password'
    // ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
    // Di dalam class Customer (App\Models\Customer)
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }
}

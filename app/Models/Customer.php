<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// class Customer extends Model
class Customer extends Authenticatable

{
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_name',
        'pic_name',
        'phone',
        'address',
        'profile_completed',
        'status'
    ];

    protected $hidden = [
        'password'
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}

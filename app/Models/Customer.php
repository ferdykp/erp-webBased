<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'customer_code',
        'company_name',
        'industry',
        'npwp',
        'phone',
        'email',
        'website',
        'notes',
        'profile_completed',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            $date = date('Ymd');
            $lastCustomer = self::whereDate('created_at', date('Y-m-d'))
                ->latest('id')
                ->first();

            $number = $lastCustomer ? (intval(substr($lastCustomer->customer_code, -3)) + 1) : 1;
            $customer->customer_code = 'CUS-' . $date . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}

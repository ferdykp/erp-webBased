<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'address_line',
        'city',
        'province',
        'postal_code',
        'country',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

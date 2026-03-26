<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'position',
        'email',
        'phone',
        'whatsapp',
        'is_primary'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

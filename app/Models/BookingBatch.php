<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingBatch extends Model
{
    protected $fillable = ['booking_id', 'porter_name', 'batch_number', 'quantity', 'unit', 'status'];
}

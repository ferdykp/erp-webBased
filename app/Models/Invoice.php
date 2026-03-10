<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'booking_id',
        'invoice_number',
        'type',
        'subtotal',
        'tax',
        'total',
        'status',
        'invoice_date',
        'due_date',
        'notes'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function totalPaid()
    {
        return $this->payments()->sum('amount');
    }

    public function balance()
    {
        return $this->total - $this->totalPaid();
    }

    public function isPaid()
    {
        return $this->balance() <= 0;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DosimeterRecord extends Model
{
    protected $fillable = ['booking_id', 'tablet_quantity', 'image'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DosimeterDetail::class, 'dosimeter_record_id')->orderBy('tablet_number', 'asc');
    }
}

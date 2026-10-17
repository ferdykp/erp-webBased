<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosimeterDetail extends Model
{
    use \App\Models\Concerns\Recoverable;

    protected $fillable = ['dosimeter_record_id', 'booking_batch_id', 'tablet_number', 'dosimeter_number', 'absorbance', 'dose_kgy', 'image'];



    protected $casts = [
        'tablet_number' => 'integer',
        'absorbance' => 'float',
        'dose_kgy' => 'float',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(DosimeterRecord::class, 'dosimeter_record_id');
    }
}

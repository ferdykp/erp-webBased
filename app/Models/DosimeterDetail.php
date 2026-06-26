<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosimeterDetail extends Model
{
    protected $fillable = ['dosimeter_record_id', 'tablet_number', 'dosimeter_number', 'absorbance', 'dose_kgy', 'image'];

    public function record(): BelongsTo
    {
        return $this->belongsTo(DosimeterRecord::class, 'dosimeter_record_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchQa extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'actual_dose',
        'visual_check',
        'indicator_check',
        'is_damaged',
        'damaged_qty',
        'damage_description',
        'qa_notes',
        'inspected_at'
    ];

    // Casting agar data lebih mudah diolah di Controller
    protected $casts = [
        'is_damaged' => 'boolean',
        'actual_dose' => 'float',
        'damaged_qty' => 'integer',
        'inspected_at' => 'datetime'
    ];

    /**
     * Relasi balik ke Batch
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(BookingBatch::class);
    }
}

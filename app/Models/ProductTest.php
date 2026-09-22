<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductTest extends Model
{
    protected $fillable = [
        'test_code',
        'requester_name',
        'requester_organization',
        'requester_contact',
        'sample_name',
        'quantity',
        'unit',
        'dmin',
        'dmax',
        'expected_temperature',
        'length_cm',
        'width_cm',
        'height_cm',
        'net_weight_kg',
        'gross_weight_kg',
        'notes',
        'production_line_id',
        'target_dose',
        'beam_speed',
        'loading_mode',
        'freq',
        'scan_gear',
        'process_notes',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'dmin' => 'float',
        'dmax' => 'float',
        'length_cm' => 'float',
        'width_cm' => 'float',
        'height_cm' => 'float',
        'net_weight_kg' => 'float',
        'gross_weight_kg' => 'float',
        'target_dose' => 'float',
        'beam_speed' => 'float',
        'freq' => 'float',
        'scan_gear' => 'float',
        'processed_at' => 'datetime',
    ];

    public function productionLine(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class);
    }

    public function dosimeters(): HasMany
    {
        return $this->hasMany(ProductTestDosimeter::class)->orderBy('sequence');
    }

    public function getDimensionLabelAttribute(): string
    {
        $values = [$this->length_cm, $this->width_cm, $this->height_cm];
        if (collect($values)->filter(fn ($value) => $value !== null)->isEmpty()) {
            return '-';
        }

        return collect($values)
            ->map(fn ($value) => $value === null ? '-' : \App\Support\NumberFormatter::smart($value, 3))
            ->implode(' × ') . ' cm';
    }
}

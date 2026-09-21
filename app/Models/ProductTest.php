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
        'quantity' => 'decimal:3',
        'dmin' => 'decimal:4',
        'dmax' => 'decimal:4',
        'length_cm' => 'decimal:3',
        'width_cm' => 'decimal:3',
        'height_cm' => 'decimal:3',
        'net_weight_kg' => 'decimal:4',
        'gross_weight_kg' => 'decimal:4',
        'target_dose' => 'decimal:4',
        'beam_speed' => 'decimal:4',
        'freq' => 'decimal:4',
        'scan_gear' => 'decimal:4',
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
            ->map(fn ($value) => $value === null ? '-' : rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.'))
            ->implode(' × ') . ' cm';
    }
}

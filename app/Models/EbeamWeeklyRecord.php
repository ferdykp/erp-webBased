<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EbeamWeeklyRecord extends Model
{
    protected $fillable = ['production_line_id', 'record_date', 'week_start', 'readings'];

    protected $casts = [
        'record_date' => 'date',
        'week_start' => 'date',
        'readings' => 'array',
    ];

    public function productionLine(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class)->withTrashed();
    }

    /**
     * Return the split meter/time values, while still reading reports saved
     * before the form separated those values into individual inputs.
     */
    public function getEquipmentOperatingTimeAttribute(): array
    {
        $data = data_get($this->readings ?? [], 'equipment_time', []);
        $parts = [];

        foreach (['turned_on', 'shut_down'] as $event) {
            $meter = data_get($data, "{$event}_meter", '');
            $time = data_get($data, "{$event}_time", '');
            $legacy = trim((string) data_get($data, $event, ''));

            if (($meter === '' || $time === '') && preg_match('/^([0-9]+(?:[.,][0-9]+)?)\s+([0-9]{1,2})[:.]([0-9]{2})$/', $legacy, $matches)) {
                $meter = $meter !== '' ? $meter : $matches[1];
                $time = $time !== '' ? $time : sprintf('%02d:%s', $matches[2], $matches[3]);
            }

            $parts[$event . '_meter'] = $meter;
            $parts[$event . '_time'] = $time;
        }

        return $parts;
    }
}

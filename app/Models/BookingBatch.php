<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk tabel booking_batches.
 *
 * Kolom asli: booking_id, batch_number, quantity, unit, porter_name, status
 * Kolom tambahan Layer 3: production_line_id, target_dose, beam_speed, loading_mode
 */
class BookingBatch extends Model
{
    protected $fillable = [
        'booking_id',
        'porter_name',
        'batch_number',
        'quantity',
        'unit',
        'status',
        // Layer 3 - Production Parameters
        'production_line_id',
        'target_dose',
        'beam_speed',
        'loading_mode',
        'freq',
        'scan_gear'
    ];

    /**
     * Relasi: Batch ini milik booking mana.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Relasi: Batch ini menggunakan mesin produksi mana.
     */
    public function productionLine(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class);
    }

    // app/Models/Batch.php

    public function qa()
    {
        return $this->hasOne(BatchQa::class, 'batch_id', 'id');
    }
}

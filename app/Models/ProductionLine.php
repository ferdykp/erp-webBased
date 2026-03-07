<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model untuk tabel master mesin penyinaran (production_lines).
 *
 * Kolom:
 * - id
 * - name (string) → Nama mesin, contoh: "Mesin 1", "Mesin 2"
 * - created_at, updated_at
 */
class ProductionLine extends Model
{
    protected $fillable = ['name'];

    /**
     * Relasi: Satu mesin bisa digunakan oleh banyak batch.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(BookingBatch::class);
    }
}

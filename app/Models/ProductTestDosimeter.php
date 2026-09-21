<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTestDosimeter extends Model
{
    protected $fillable = [
        'product_test_id',
        'sequence',
        'dosimeter_number',
        'position',
        'absorbance',
        'dose_kgy',
    ];

    protected $casts = [
        'absorbance' => 'decimal:4',
        'dose_kgy' => 'decimal:4',
    ];

    public function productTest(): BelongsTo
    {
        return $this->belongsTo(ProductTest::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehousePic extends Model
{
    use HasFactory;

    protected $table = 'warehouse_pics';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'shift',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

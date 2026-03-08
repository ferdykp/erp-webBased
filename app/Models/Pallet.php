<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pallet extends Model
{
    protected $fillable = [
        // 'pallet_number',
        'status',
        'current_booking_id',
        'line',
        'slot_section',
        // 'box_capacity',
        'filled_boxes' // Pastikan ini ada    
    ];

    public function booking(): BelongsTo
    {
        // Pastikan foreign key-nya adalah 'current_booking_id'
        return $this->belongsTo(Booking::class, 'current_booking_id');
    }
    // Di dalam class Pallet
    public function placementDetails()
    {
        // Mengambil semua detail penempatan yang berada di petak ini
        return PlacementDetail::where('line', $this->line)
            ->where('slot_section', $this->slot_section);
    }
    public function contents()
    {
        return $this->hasMany(PalletContent::class, 'pallet_id');
    }
}

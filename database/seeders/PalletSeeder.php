<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pallet;

class PalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            Pallet::create([
                'pallet_number' => 'PLT-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'status' => 'empty'
            ]);
        }
    }
}

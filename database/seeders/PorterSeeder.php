<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PorterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Budi Santoso'],
            ['name' => 'Andi Wijaya'],
            ['name' => 'Siti Aminah'],
            ['name' => 'Agus Prayogo'],
            ['name' => 'Iwan Fals'],
        ];

        foreach ($data as $porter) {
            \App\Models\Porter::updateOrCreate(['name' => $porter['name']], $porter);
        }
    }
}

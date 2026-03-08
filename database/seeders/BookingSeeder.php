<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Booking;
use App\Models\BookingProduct;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $customers = Customer::all();

            if ($customers->isEmpty()) {
                $this->command->info('No customers found. Please seed customers first.');
                return;
            }

            $products = collect([
                'Masker Medis',
                'Alat Bedah Set',
                'Jarum Suntik 3ml',
                'Sarung Tangan Lateks',
                'Botol Farmasi',
                'Kasa Steril'
            ])->shuffle();

            foreach ($products as $productName) {

                $customer = $customers->random();

                $booking = Booking::create([
                    'booking_code' => 'EB-' . strtoupper(Str::random(6)),
                    'customer_id' => $customer->id,
                    'status' => 'pending',
                    'qr_token' => (string) Str::uuid(),
                ]);

                $dmin = collect([10, 15, 25])->random();
                $dmax = $dmin + 10;

                BookingProduct::create([
                    'booking_id' => $booking->id,
                    'product_name' => $productName,

                    'product_type' => collect([
                        'Alat Kesehatan',
                        'Farmasi',
                        'Laboratorium',
                        'Kemasan Makanan'
                    ])->random(),

                    'quantity' => rand(50, 200),
                    'unit' => collect(['box', 'carton', 'pack'])->random(),

                    'dmin' => $dmin,
                    'dmax' => $dmax,
                    'expect_temp' => collect([25, 20, 18, 30])->random(),
                    'dimension_pack' => rand(30, 60) . 'x' . rand(20, 40) . 'x' . rand(20, 40) . ' cm',
                    'gross_weight_per_pcs' => rand(1, 15),
                ]);
            }
        });
    }
}

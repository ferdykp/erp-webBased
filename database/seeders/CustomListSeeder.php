<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Customer;

class CustomListSeeder extends Seeder
{
    public function run(): void
    {
        // Demo data only. Never truncate existing production records.
        DB::transaction(function () {
            for ($i = 1; $i <= 15; $i++) {

                $companyName = "PT Company $i";
                $picName     = "PIC Customer $i";
                $email       = "customer$i@mail.com";

                /**
                 * 1. USER
                 */
                $user = User::firstOrCreate([
                    'email' => $email,
                ], [
                    'username' => strtolower(Str::slug($picName)) . '-demo-' . $i,
                    'password' => Hash::make('12345678'),
                    'role'     => 'customer',
                ]);

                /**
                 * 2. CUSTOMER (TANPA NAME ❗)
                 */
                $customer = Customer::firstOrCreate([
                    'user_id' => $user->id,
                ], [
                    'company_name'      => $companyName,
                    'industry'          => ['Technology', 'Logistik', 'Retail'][rand(0, 2)],
                    'email'             => $email,
                    'status'            => 'active',
                    'profile_completed' => true
                ]);

                /**
                 * 3. ADDRESS
                 */
                $customer->addresses()->firstOrCreate([
                    'type' => 'office',
                ], [
                    'address_line' => "Jl. Sudirman No.$i",
                    'city'         => ['Jakarta', 'Bandung', 'Surabaya'][rand(0, 2)],
                    'country'      => 'Indonesia',
                ]);

                /**
                 * 4. CONTACT (PIC DI SINI ✅)
                 */
                $customer->contacts()->firstOrCreate([
                    'email' => $email,
                ], [
                    'name'       => $picName,
                    'phone'      => '08' . rand(1111111111, 9999999999),
                    'is_primary' => true
                ]);
            }

        });

        $this->command?->info('Data contoh pelanggan ditambahkan tanpa menghapus data lama.');
    }
}

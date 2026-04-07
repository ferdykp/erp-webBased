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
        DB::beginTransaction();

        try {

            // 🔥 Disable FK biar aman truncate
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            DB::table('customer_contacts')->truncate();
            DB::table('customer_addresses')->truncate();
            DB::table('bookings')->truncate();
            DB::table('customers')->truncate();
            DB::table('users')->truncate();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            for ($i = 1; $i <= 15; $i++) {

                $companyName = "PT Company $i";
                $picName     = "PIC Customer $i";
                $email       = "customer$i@mail.com";

                /**
                 * 1. USER
                 */
                $user = User::create([
                    'username' => strtolower(Str::slug($picName)) . rand(10, 99),
                    'email'    => $email,
                    'password' => Hash::make('12345678'),
                    // 'role'     => 'customer',
                ]);

                /**
                 * 2. CUSTOMER (TANPA NAME ❗)
                 */
                $customer = Customer::create([
                    'user_id'           => $user->id,
                    'company_name'      => $companyName,
                    'industry'          => ['Technology', 'Logistik', 'Retail'][rand(0, 2)],
                    'email'             => $email,
                    'status'            => 'active',
                    'profile_completed' => true
                ]);

                /**
                 * 3. ADDRESS
                 */
                $customer->addresses()->create([
                    'type'         => 'office',
                    'address_line' => "Jl. Sudirman No.$i",
                    'city'         => ['Jakarta', 'Bandung', 'Surabaya'][rand(0, 2)],
                    'country'      => 'Indonesia',
                ]);

                /**
                 * 4. CONTACT (PIC DI SINI ✅)
                 */
                $customer->contacts()->create([
                    'name'       => $picName,
                    'phone'      => '08' . rand(1111111111, 9999999999),
                    'email'      => $email,
                    'is_primary' => true
                ]);
            }

            DB::commit();

            echo "Seeder berhasil dijalankan ✅\n";
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }
}

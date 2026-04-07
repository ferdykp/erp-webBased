<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
// use Illuminate\Support\Facades\Log;
// use App\Models\CustomerAddress; // Pastikan Model ini ada
// use App\Models\CustomerContact; // Pastikan Model ini ada
// use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CustomerProfileController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::with(['contacts'])->latest()->paginate(10);
        $query = Customer::with(['contacts', 'addresses']); // Load relasi contacts

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('company_name', 'like', "%{$search}%")
                ->orWhereHas('contacts', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        $customers = $query->latest()->paginate(10);
        return view('admin.customerList.index', compact('customers'));
    }
    public function showCompleteProfile()
    {
        return view('customer.profile.complete');
    }


    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'name'         => 'required|string|max:255',
            'address_line' => 'required|string',
            'contact_phone' => 'nullable|string', // Pastikan divalidasi juga
        ]);

        try {
            DB::beginTransaction();

            // 1. Buat User
            $user = User::create([
                'username' => strtolower(str_replace(' ', '', $request->name)) . rand(10, 99),
                'email'    => $request->email,
                'password' => Hash::make('12345678'),
                'role'     => 'customer',
            ]);

            // 2. Buat Customer (Pastikan user_id dan name sudah fillable di Model)
            $customer = Customer::create([
                'user_id'           => $user->id,
                'name'              => $request->name,
                'company_name'      => $request->company_name,
                'industry'          => $request->industry ?? '-',
                'email'             => $request->email,
                'status'            => 'active',
                'profile_completed' => true
            ]);

            // 3. Simpan Alamat
            $customer->addresses()->create([
                'type'         => 'office',
                'address_line' => $request->address_line,
                'city'         => $request->city ?? '-',
                'country'      => 'Indonesia',
            ]);

            // 4. Simpan Contact
            $customer->contacts()->create([
                'name'       => $request->name,
                'phone'      => $request->contact_phone ?? '-',
                'email'      => $request->email,
                'is_primary' => true
            ]);

            DB::commit();
            return redirect()->route('admin.customerList.index')->with('success', 'Customer & Akun berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            // Gunakan \Log untuk melihat detail error di storage/logs/laravel.log jika masih gagal
            \Log::error($e->getMessage());
            return back()->withErrors(['error' => 'Gagal: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Fungsi untuk Customer melengkapi profile sendiri
     */
    public function completeProfile(Request $request)
    {
        $request->validate([
            'company_name'  => 'required|string|max:255',
            'industry'      => 'required|string',
            'email'         => 'required|email', // Email Company
            'address_line'  => 'required|string',
            'city'          => 'required|string',
            'pic_name'      => 'required|string|max:255',
            'pic_email'     => 'required|email',
            'phone'         => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // 1. Simpan ke customers (tambahkan kolom email perusahaan)
            $customer = Customer::create([
                'user_id'           => $user->id,
                'company_name'      => $request->company_name,
                'email'             => $request->email,
                'industry'          => $request->industry, // Pastikan ini ada
                'npwp'              => $request->npwp,     // Pastikan ini ada
                'phone'             => $request->phone,    // Jika ingin simpan di sini
                'profile_completed' => true,
                'status'            => 'active'
            ]);

            // 2. Simpan Alamat (dengan kota dan pos)
            $customer->addresses()->create([
                'type'         => 'office',
                'address_line' => $request->address_line,
                'city'         => $request->city,
                'postal_code'  => $request->postal_code,
            ]);

            // 3. Simpan Contact PIC (dengan email spesifik PIC)
            $customer->contacts()->create([
                'name'       => $request->pic_name,
                'email'      => $request->pic_email,
                'phone'      => $request->phone,
                'position'   => $request->position,
                'is_primary' => true
            ]);

            $user->update(['profile_completed' => true]);

            DB::commit();
            return redirect()->route('customer.dashboard')->with('success', 'Profil Berhasil Dibuat!');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Ambil kode error SQL
            $errorCode = $e->errorInfo[1];

            // Mapping error spesifik
            $errorMessage = match ($errorCode) {
                1062 => "Data gagal disimpan: Email Company '{$request->email}' sudah terdaftar di sistem kami.",
                1452 => "Data gagal disimpan: Relasi user tidak ditemukan. Silakan login ulang.",
                1364 => "Data gagal disimpan: Ada kolom wajib di database yang belum terisi.",
                default => "Terjadi kesalahan database: " . $e->getMessage() // Untuk debugging dev
            };

            return back()->withErrors(['error' => $errorMessage])->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => "Terjadi kesalahan sistem: " . $e->getMessage()])->withInput();
        }
    }

    /**
     * Menampilkan form edit profil
     */

    public function edit()
    {
        $user = Auth::user();
        $user->load(['customer.addresses', 'customer.contacts']);

        return view('customer.profile.profile_edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            return back()->withErrors(['error' => 'Profil customer tidak ditemukan']);
        }

        // ✅ VALIDASI (SUDAH FIX)
        $request->validate([
            'username'         => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'company_name'     => 'required|string|max:255',
            'address_line'     => 'required|string',
            'contact_name'     => 'required|string|max:255', // ✅ ini penting
            'contact_phone'    => 'required|string|max:20',
            'contact_position' => 'nullable|string|max:255',
            'contact_whatsapp' => 'nullable|string|max:20',
            'industry'         => 'nullable|string',
            'city'             => 'nullable|string',
            'npwp'             => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // ✅ 1. UPDATE USER
            $user->update([
                'username' => $request->username,
                'email'    => $request->email,
            ]);

            // ✅ 2. UPDATE CUSTOMER
            $customer->update([
                'company_name' => $request->company_name,
                'industry'     => $request->industry,
                'npwp'         => $request->npwp,
            ]);

            // ✅ 3. UPDATE ADDRESS
            $customer->addresses()->updateOrCreate(
                ['type' => 'office'],
                [
                    'address_line' => $request->address_line,
                    'city'         => $request->city,
                    'country'      => 'Indonesia',
                ]
            );

            // ✅ 4. UPDATE CONTACT (INI YANG PALING PENTING)
            $customer->contacts()->updateOrCreate(
                ['is_primary' => true],
                [
                    'name'     => $request->contact_name, // ✅ mapping benar
                    'position' => $request->contact_position,
                    'phone'    => $request->contact_phone,
                    'whatsapp' => $request->contact_whatsapp,
                    'email'    => $request->email,
                ]
            );

            DB::commit();

            return redirect()->route('customer.profile')
                ->with('success', 'Profil berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Update Profile Error: ' . $e->getMessage());

            return back()->withErrors([
                'error' => 'Gagal menyimpan: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function updateAdmin(Request $request, $id)
    {
        $customer = Customer::with(['contacts', 'addresses', 'user'])->findOrFail($id);

        $request->validate([
            'company_name' => 'required',
            'email'        => 'required|email',
            'contact_name' => 'required',
            'contact_phone' => 'required',
            'address_line' => 'required',
        ]);

        try {
            DB::beginTransaction();

            // Update user
            $customer->user->update([
                'email' => $request->email
            ]);

            // Update customer
            $customer->update([
                'company_name' => $request->company_name,
                'industry'     => $request->industry,
                'email'        => $request->email,
            ]);

            // Update address
            $customer->addresses()->updateOrCreate(
                ['type' => 'office'],
                [
                    'address_line' => $request->address_line,
                    'country' => 'Indonesia'
                ]
            );

            // Update contact
            $customer->contacts()->updateOrCreate(
                ['is_primary' => true],
                [
                    'name'  => $request->contact_name,
                    'phone' => $request->contact_phone,
                    'email' => $request->email
                ]
            );

            DB::commit();

            return back()->with('success', 'Customer updated!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage());
        }
    }
}

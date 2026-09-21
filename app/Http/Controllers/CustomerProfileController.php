<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
// use Illuminate\Support\Facades\Log;
// use App\Models\CustomerAddress; // Pastikan Model ini ada
// use App\Models\CustomerContact; // Pastikan Model ini ada
// use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CustomerProfileController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $customers = Customer::query()
            // KUNCI PERBAIKAN: Tambahkan eager loading untuk relasi yang dibutuhkan modal
            ->with(['contacts', 'addresses', 'bookings.products'])
            ->when($search, function ($query) use ($search) {
                $query->where('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        // Jika ini request AJAX untuk pencarian
        if ($request->ajax()) {
            return view('admin.customerList.table', compact('customers'))->render();
        }

        return view('admin.customerList.index', compact('customers', 'search'));
    }
    public function showCompleteProfile()
    {
        $user = Auth::guard('customer')->user();
        if ($user?->customer?->profile_completed) {
            return redirect()->route('customer.dashboard');
        }
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
        $user = Auth::guard('customer')->user();
        abort_unless($user, 403);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'industry' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id, 'unique:customers,email,' . ($user->customer?->id ?? 'NULL')],
            'address_line' => 'required|string|max:1000',
            'city' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'pic_name' => 'required|string|max:255',
            'pic_email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'position' => 'nullable|string|max:255',
            'npwp' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($user, $validated) {
            // Registration already creates the customer row. Update it instead of
            // creating a duplicate profile for the same user.
            $customer = Customer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $validated['company_name'],
                    'email' => $validated['email'],
                    'industry' => $validated['industry'],
                    'npwp' => $validated['npwp'] ?? null,
                    'phone' => $validated['phone'],
                    'profile_completed' => true,
                    'status' => 'active',
                ]
            );

            $customer->addresses()->updateOrCreate(
                ['type' => 'office'],
                [
                    'address_line' => $validated['address_line'],
                    'city' => $validated['city'],
                    'postal_code' => $validated['postal_code'] ?? null,
                    'country' => 'Indonesia',
                ]
            );

            $customer->contacts()->updateOrCreate(
                ['is_primary' => true],
                [
                    'name' => $validated['pic_name'],
                    'email' => $validated['pic_email'],
                    'phone' => $validated['phone'],
                    'position' => $validated['position'] ?? null,
                    'is_primary' => true,
                ]
            );
        });

        return redirect()->route('customer.dashboard')->with('success', 'Profil berhasil dilengkapi.');
    }

    /**
     * Menampilkan form edit profil
     */

    public function edit()
    {
        $user = Auth::guard('customer')->user();
        $user->load(['customer.addresses', 'customer.contacts']);

        return view('customer.profile.profile_edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('customer')->user();
        $customer = $user->customer;

        if (!$customer) {
            return back()->withErrors(['error' => 'Profil customer tidak ditemukan']);
        }

        // ✅ VALIDASI (SUDAH FIX)
        $request->validate([
            'username'         => 'required|string|max:255',
            'email'            => ['required', 'email', 'unique:users,email,' . $user->id, 'unique:customers,email,' . $customer->id],
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
                'email'        => $request->email,
                'phone'        => $request->contact_phone,
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


    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:customer'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::guard('customer')->user()->update(['password' => Hash::make($validated['password'])]);
        return back()->with('success', 'Password berhasil diperbarui.');
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

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $customer = Customer::findOrFail($id);

            // Hapus User terkait jika ada (opsional, tergantung logic Anda)
            if ($customer->user_id) {
                User::where('id', $customer->user_id)->delete();
            }

            // Alamat dan Contact biasanya terhapus otomatis jika menggunakan onCascadeDelete di migration
            // Jika tidak, hapus manual:
            $customer->addresses()->delete();
            $customer->contacts()->delete();
            $customer->delete();

            DB::commit();
            return back()->with('success', 'Customer deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete: ' . $e->getMessage()]);
        }
    }
}

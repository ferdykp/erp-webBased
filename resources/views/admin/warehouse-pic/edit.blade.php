@extends('admin.layout.app')

@section('title', 'Edit PIC Warehouse')

@section('content')
    <div class="max-w-2xl p-6 mx-auto bg-white rounded-lg shadow-sm">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-800">Edit PIC Warehouse</h1>
            <p class="text-sm text-slate-500">Pembaruan data petugas penanggung jawab gudang.</p>
        </div>

        <form action="{{ route('admin.warehouse-pics.update', $warehousePic->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <!-- Nama -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Nama Lengkap <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $warehousePic->name) }}"
                        @class([
                            'w-full mt-1 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500',
                            'border-red-500' => $errors->has('name'),
                            'border-slate-300' => !$errors->has('name'),
                        ]) required>
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Alamat Email <span
                            class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $warehousePic->email) }}"
                        @class([
                            'w-full mt-1 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500',
                            'border-red-500' => $errors->has('email'),
                            'border-slate-300' => !$errors->has('email'),
                        ]) required>
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telepon -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Nomor Telepon</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $warehousePic->phone) }}"
                        @class([
                            'w-full mt-1 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500',
                            'border-red-500' => $errors->has('phone'),
                            'border-slate-300' => !$errors->has('phone'),
                        ])>
                    @error('phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Shift Kerja -->
                <div>
                    <label for="shift" class="block text-sm font-medium text-slate-700">Shift Kerja</label>
                    <select name="shift" id="shift"
                        class="w-full mt-1 rounded-md shadow-sm border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="" disabled>Pilih Shift</option>
                        <option value="Morning" {{ old('shift', $warehousePic->shift) == 'Morning' ? 'selected' : '' }}>
                            Morning (06:00 - 14:00)</option>
                        <option value="Afternoon" {{ old('shift', $warehousePic->shift) == 'Afternoon' ? 'selected' : '' }}>
                            Afternoon (14:00 - 22:00)</option>
                        <option value="Night" {{ old('shift', $warehousePic->shift) == 'Night' ? 'selected' : '' }}>Night
                            (22:00 - 06:00)</option>
                    </select>
                    @error('shift')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="is_active" name="is_active" type="checkbox" value="1"
                            {{ old('is_active', $warehousePic->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-slate-700">Akun Aktif</label>
                        <p class="text-slate-500">Ubah status ke non-aktif jika petugas sedang cuti atau tidak bertugas.</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end pt-6 mt-6 space-x-3 border-t border-slate-200">
                <a href="{{ route('admin.warehouse-pics.index') }}"
                    class="px-4 py-2 text-sm font-medium bg-white border rounded-md border-slate-300 text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    Perbarui PIC
                </button>
            </div>
        </form>
    </div>
@endsection

@extends('admin.layout.app')

@section('title', 'Edit Porter')

@section('content')
    <div class="max-w-2xl p-6 mx-auto bg-white rounded-lg shadow-sm">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-800">Edit Data Porter</h1>
            <p class="text-sm text-slate-500">Perbarui informasi porter di bawah ini.</p>
        </div>

        <form action="{{ route('admin.porter.update', $porter->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <!-- Nama -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Nama Lengkap <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $porter->name) }}"
                        class="w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                        required>
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No. Telepon -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Nomor Telepon</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $porter->phone) }}"
                        class="w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Aktif (Checkbox) -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="is_active" name="is_active" type="checkbox" value="1"
                            {{ old('is_active', $porter->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-slate-700">Porter Aktif</label>
                        <p class="text-slate-500">Tentukan apakah porter ini aktif bertugas atau ditangguhkan sementara.</p>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="flex justify-end pt-6 mt-6 space-x-3 border-t border-slate-200">
                <a href="{{ route('admin.porter.index') }}"
                    class="px-4 py-2 text-sm font-medium bg-white border rounded-md border-slate-300 text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Perbarui Porter
                </button>
            </div>
        </form>
    </div>
@endsection

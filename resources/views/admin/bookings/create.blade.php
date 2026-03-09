@extends('admin.layout.app')

@section('content')
    <div class="w-full space-y-6">
        <div class="flex flex-col gap-2 mb-4">
            <h2 class="text-3xl font-black tracking-tight text-gray-900">
                Admin <span class="text-blue-600">Manual Booking</span>
            </h2>
            <p class="text-sm font-medium text-gray-400">Membuatkan pesanan sterilisasi untuk customer.</p>
        </div>

        <div class="bg-white shadow-sm border border-gray-100 rounded-[2rem] overflow-hidden">
            <div class="p-8">
                <form action="{{ route('admin.bookings.store') }}" method="POST" class="space-y-8">
                    @csrf

                    {{-- SELECT CUSTOMER --}}
                    <div class="p-6 space-y-3 border border-blue-100 bg-blue-50/50 rounded-3xl">
                        <label class="text-[11px] font-black text-blue-600 uppercase tracking-widest ml-1">Customer
                            Owner</label>
                        <div class="relative">
                            <i class="absolute text-blue-400 -translate-y-1/2 left-4 top-1/2 fa-solid fa-user-tie"></i>
                            <select name="customer_id"
                                class="w-full pl-11 pr-4 py-3.5 bg-white border border-blue-100 rounded-2xl focus:ring-4 focus:ring-blue-100 outline-none font-bold text-gray-700"
                                required>
                                <option value="" disabled selected>Pilih Customer...</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Sisanya copy paste dari form customer Anda, sesuaikan input namenya jika perlu --}}
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Product
                                Name</label>
                            <input type="text" name="product_name"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 outline-none font-bold text-gray-700"
                                required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Product
                                Type</label>
                            <input type="text" name="product_type"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 outline-none font-bold text-gray-700"
                                required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Minimum Dose
                                Required
                            </label>
                            <div class="relative">
                                <i class="absolute text-gray-400 -translate-y-1/2 left-4 top-1/2 fa-solid fa-box-open"></i>
                                <input type="number" name="dmin" value="{{ old('dmin') }}" placeholder="5"
                                    class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700 {{ $errors->has('field') ? 'border-red-500' : 'border-gray-100' }}"
                                    required>
                                <span
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-700 uppercase">kGy</span>

                            </div>
                            @error('dmin')
                                <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Maximum Dose
                                Required</label>
                            <div class="relative">
                                <i class="absolute text-gray-400 -translate-y-1/2 left-4 top-1/2 fa-solid fa-tags"></i>
                                <input type="number" name="dmax" value="{{ old('dmax') }}" placeholder="7"
                                    class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700 {{ $errors->has('field') ? 'border-red-500' : 'border-gray-100' }}"
                                    required>
                                <span
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-700 uppercase">kGy</span>


                            </div>
                            @error('dmax')
                                <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">
                                Dimension Pack (Length x Width x Height)
                            </label>

                            <div class="flex items-center space-x-2">
                                <div class="relative flex-1">
                                    <i
                                        class="absolute text-xs text-gray-400 -translate-y-1/2 left-3 top-1/2 fa-solid fa-arrows-left-right"></i>
                                    <input type="number" name="dim_length" value="{{ old('dim_length') }}" placeholder="P"
                                        class="w-full pl-9 pr-2 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700 text-center"
                                        required>
                                </div>

                                <span class="font-bold text-gray-400">×</span>

                                <div class="relative flex-1">
                                    <input type="number" name="dim_width" value="{{ old('dim_width') }}" placeholder="L"
                                        class="w-full px-2 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700 text-center"
                                        required>
                                </div>

                                <span class="font-bold text-gray-400">×</span>

                                <div class="relative flex-1">
                                    <input type="number" name="dim_height" value="{{ old('dim_height') }}" placeholder="T"
                                        class="w-full pl-2 pr-8 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700 text-center"
                                        required>
                                    <span
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-gray-400 uppercase">cm</span>
                                </div>
                            </div>

                            @if ($errors->has('dim_length') || $errors->has('dim_width') || $errors->has('dim_height'))
                                <p class="text-[10px] font-bold text-red-500 ml-1">Semua dimensi harus diisi lengkap.</p>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Gross Weight
                                Per Pieces</label>
                            <div class="relative">
                                <i class="absolute text-gray-400 -translate-y-1/2 left-4 top-1/2 fa-solid fa-tags"></i>
                                <input type="number" name="gross_weight_per_pcs" value="{{ old('gross_weight_per_pcs') }}"
                                    placeholder="7"
                                    class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700 {{ $errors->has('field') ? 'border-red-500' : 'border-gray-100' }}"
                                    required>
                                <span
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-700 uppercase">Kg</span>


                            </div>
                            @error('gross_weight_per_pcs')
                                <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        {{-- QUANTITY --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Total
                                Quantity</label>
                            <input type="number" name="quantity" value="{{ old('quantity') }}" placeholder="0"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700 {{ $errors->has('field') ? 'border-red-500' : 'border-gray-100' }}"
                                required>
                            @error('quantity')
                                <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- UNIT --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Satuan
                                (UOM)</label>
                            <select name="unit"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700 {{ $errors->has('field') ? 'border-red-500' : 'border-gray-100' }}"
                                required>
                                <option value="" disabled selected>Choose Unit</option>
                                <option value="sack">Sack</option>
                                <option value="drum">Drum</option>
                                <option value="box">Box / Dus</option>
                            </select>
                            @error('unit')
                                <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Required
                                Temperature
                                (Celcius)</label>
                            <div class="relative">
                                <input type="text" name="expect_temp" value="{{ old('expect_temp') }}"
                                    placeholder="Leave Blank If None"
                                    class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700 {{ $errors->has('field') ? 'border-red-500' : 'border-gray-100' }}"
                                    required>
                                <span
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-700 uppercase">Celcius</span>
                            </div>
                            @error('expect_temp')
                                <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- TARGET DOSE --}}
                        {{-- <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Target Dose
                                (kGy)</label>
                            <div class="relative">
                                <input type="text" name="target_dose" value="{{ old('target_dose') }}"
                                    placeholder="Contoh: 25"
                                    class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700 {{ $errors->has('field') ? 'border-red-500' : 'border-gray-100' }}"
                                    required>
                                <span
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-700 uppercase">kGy</span>
                            </div>
                            @error('target_dose')
                                <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                            @enderror
                        </div> --}}
                    </div>
                    <div class="flex items-center justify-between pt-8 border-t border-gray-50">
                        <a href="{{ route('admin.bookings') }}"
                            class="text-sm font-bold text-gray-400 hover:text-gray-700">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-10 py-4 text-sm font-black tracking-widest text-white uppercase transition-all bg-blue-600 shadow-xl rounded-2xl hover:bg-blue-700 active:scale-95">
                            Simpan Booking <i class="ml-2 fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

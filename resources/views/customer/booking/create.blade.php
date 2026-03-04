@extends('layouts.master')

@section('content')
    <div class="w-full space-y-6">

        {{-- HEADER SECTION --}}
        <div class="flex flex-col gap-2 mb-4">
            <h2 class="text-3xl font-black tracking-tight text-gray-900">
                New <span class="text-blue-600">Booking</span>
            </h2>
            <p class="text-sm font-medium text-gray-400">
                Silahkan lengkapi data barang yang akan disterilisasi menggunakan E-Beam.
            </p>
        </div>

        <div class="bg-white shadow-sm border border-gray-100 rounded-[2rem] overflow-hidden">

            {{-- FORM HEADER --}}
            <div class="px-8 py-8 border-b border-gray-50 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex items-center justify-center w-12 h-12 bg-blue-600 shadow-lg rounded-2xl shadow-blue-100">
                            <i class="text-xl text-white fa-solid fa-file-signature"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black tracking-tight text-gray-900">Isi Detail Produk</h2>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-0.5">Product Information
                            </p>
                        </div>
                    </div>

                    <div class="hidden md:block">
                        <span
                            class="px-4 py-2 text-[10px] font-black text-blue-600 bg-blue-50 rounded-full uppercase tracking-tighter">
                            E-Beam Sterilization
                        </span>
                    </div>
                </div>
            </div>

            {{-- BODY --}}
            <div class="p-8">
                <form action="{{ route('customer.booking.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-8">
                    @csrf
                    {{-- Input hidden slot_id dihapus karena tidak lagi menggunakan sesi --}}

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        {{-- PRODUCT NAME --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Product
                                Name</label>
                            <div class="relative">
                                <i class="absolute text-gray-400 -translate-y-1/2 left-4 top-1/2 fa-solid fa-box-open"></i>
                                <input type="text" name="product_name" value="{{ old('product_name') }}"
                                    placeholder="Contoh: Masker Medis / Alat Bedah"
                                    class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700 {{ $errors->has('field') ? 'border-red-500' : 'border-gray-100' }}"
                                    required>
                            </div>
                            @error('product_name')
                                <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Product
                                Type</label>
                            <div class="relative">
                                <i class="absolute text-gray-400 -translate-y-1/2 left-4 top-1/2 fa-solid fa-tags"></i>
                                <input type="text" name="product_type" value="{{ old('product_type') }}"
                                    placeholder="Contoh: Alat Kesehatan / Farmasi"
                                    class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700 {{ $errors->has('field') ? 'border-red-500' : 'border-gray-100' }}"
                                    required>
                            </div>
                            @error('product_type')
                                <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                            @enderror
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


                    {{-- ACTION BUTTONS --}}
                    <div class="flex items-center justify-between pt-8 border-t border-gray-50">
                        <a href="{{ route('customer.dashboard') }}"
                            class="flex items-center gap-2 px-6 py-3 text-sm font-bold text-gray-400 transition-colors hover:text-gray-700 group">
                            <i class="transition-transform fa-solid fa-arrow-left group-hover:-translate-x-1"></i>
                            Batal
                        </a>

                        <button type="submit"
                            class="px-10 py-4 text-sm font-black tracking-widest text-white uppercase transition-all bg-blue-600 shadow-xl rounded-2xl shadow-blue-200 hover:bg-blue-700 hover:shadow-blue-300 active:scale-95">
                            Buat Booking <i class="ml-2 fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- INFO CARD --}}
        <div class="p-6 border bg-amber-50 border-amber-100 rounded-3xl">
            <div class="flex gap-4">
                <i class="mt-1 text-amber-500 fa-solid fa-circle-info"></i>
                <p class="text-xs font-bold leading-relaxed text-amber-700">
                    <span class="block mb-1 font-black tracking-wider uppercase">Reminder:</span>
                    After making a booking, you will receive a booking code. Please bring your items to the sterilization
                    location.
                    The processing schedule will be determined after the items have been verified on-site.
                </p>
            </div>
        </div>
    </div>
@endsection

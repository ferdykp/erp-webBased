@extends('admin.layout.app')

@section('title', 'Product Finish')

@section('content')

    <div class="w-full pb-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Product Finish</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">
                    Step 3: review batch yang sudah berstatus <span class="font-semibold text-emerald-600">Finish</span> dan
                    tersimpan di database.
                </p>
            </div>
        </div>

        {{-- ═══ TABEL BATCH DONE ═══ --}}
        @php
            $doneRows = [];
            foreach ($bookings as $booking) {
                $product = $booking->products->first();
                // Mengambil batch dengan status done beserta data QA-nya
                foreach ($booking->batches->where('status', 'done') as $batch) {
                    $doneRows[] = [
                        'booking' => $booking,
                        'product' => $product,
                        'batch' => $batch,
                        'qa' => $batch->qa, // Pastikan relasi 'qa' sudah ada di model BookingBatch
                    ];
                }
            }
        @endphp

        <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-black text-slate-700">
                    <i class="mr-2 fa-solid fa-circle-check text-emerald-600"></i>Daftar Batch Finish
                </h3>
            </div>

            @if (empty($doneRows))
                <div class="py-20 text-center">
                    <div class="flex flex-col items-center gap-4">
                        <div class="flex items-center justify-center w-24 h-24 rounded-full bg-slate-50">
                            <i class="text-4xl fa-solid fa-box-archive text-slate-200"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-600">Belum Ada Batch Finish</h3>
                        <p class="max-w-xs text-sm text-slate-400">
                            Batch yang sudah melewati Quality Control akan otomatis muncul di daftar ini.
                        </p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-3">
                        <thead>
                            <tr class="text-[10px] font-black tracking-[0.18em] text-slate-500 uppercase">
                                <th class="px-6 py-3">Booking & Customer</th>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3 text-center">Batch Info</th>
                                <th class="px-6 py-3 text-center">Target vs Actual</th>
                                <th class="px-6 py-3 text-center">QA Status</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($doneRows as $row)
                                @php
                                    $booking = $row['booking'];
                                    $product = $row['product'];
                                    $batch = $row['batch'];
                                    $qa = $row['qa'];

                                    // Ambil data semua pallet terkait booking ini untuk dilempar ke Modal
                                    $palletData = \App\Models\PalletContent::with('pallet')
                                        ->where('booking_id', $booking->id)
                                        ->get()
                                        ->map(function ($item) {
                                            return [
                                                'id' => $item->id,
                                                'qty' => $item->quantity,
                                                'loc' => "Line {$item->pallet->line} - Petak {$item->pallet->slot_section}",
                                            ];
                                        });
                                @endphp
                                <tr
                                    class="transition-all bg-white border shadow-sm group rounded-2xl border-slate-100 hover:border-emerald-200">
                                    {{-- Kolom 1: Customer --}}
                                    <td class="px-6 py-4 align-middle">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="flex items-center justify-center text-xs font-black shadow-sm w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-700">
                                                {{ strtoupper(substr($booking->customer->company_name ?? '?', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-800">#{{ $booking->booking_code }}
                                                </p>
                                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-tighter">
                                                    {{ $booking->customer->company_name ?? 'Guest' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kolom 2: Product (Clean Version) --}}
                                    <td class="px-6 py-4 align-middle">
                                        <p class="text-sm font-black text-slate-700">{{ $product->product_name ?? '-' }}</p>
                                        <p class="text-[10px] text-slate-400 italic">Production Line:
                                            {{ $batch->productionLine->name ?? '-' }}</p>
                                    </td>

                                    {{-- Kolom 3: Batch Info --}}
                                    <td class="px-6 py-4 text-center align-middle">
                                        <div class="inline-flex flex-col">
                                            <span class="text-xs font-black text-slate-700">Batch
                                                #{{ $batch->batch_number }}</span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase">
                                                {{ number_format($batch->quantity) }} {{ $batch->unit }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Kolom 4: Dose Info --}}
                                    <td class="px-6 py-4 text-center align-middle">
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-bold text-slate-400">T:
                                                {{ (int) $batch->target_dose }}
                                                | <span class="text-emerald-600">A: {{ $qa->actual_dose ?? '-' }}</span>
                                            </span>
                                            <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">kGy
                                                Unit</span>
                                        </div>
                                    </td>

                                    {{-- Kolom 5: QA Status --}}
                                    <td class="px-6 py-4 text-center align-middle">
                                        @if ($qa && $qa->visual_check == 'pass')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-black text-emerald-700 uppercase bg-emerald-50 rounded-xl">
                                                <i class="fa-solid fa-circle-check"></i> Passed
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-black text-red-700 uppercase bg-red-50 rounded-xl">
                                                <i class="fa-solid fa-circle-xmark"></i> Rejected
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom 6: Main Actions --}}
                                    <td class="px-6 py-4 text-right align-middle">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Tombol Kelola Relokasi Pallet --}}
                                            <button type="button"
                                                onclick="openRelocationModal('{{ $batch->batch_number }}', {{ json_encode($palletData) }})"
                                                class="p-2.5 text-amber-500 hover:bg-amber-50 rounded-xl transition-all active:scale-90"
                                                title="Manage Pallets Relocation">
                                                <i class="text-lg fa-solid fa-truck-ramp-box"></i>
                                            </button>

                                            {{-- Tombol Detail Final --}}
                                            <button onclick="openFinishDetailModal(this)"
                                                data-batch-id="{{ $batch->id }}" data-booking-id="{{ $booking->id }}"
                                                data-payment-status="{{ $booking->payment_status }}"
                                                data-booking-code="{{ $booking->booking_code }}"
                                                data-company-name="{{ $booking->customer->company_name ?? '-' }}"
                                                data-product-name="{{ $product->product_name ?? '-' }}"
                                                data-batch-no="{{ $batch->batch_number }}"
                                                data-quantity="{{ number_format($batch->quantity) }}"
                                                data-unit="{{ $batch->unit }}"
                                                data-line="{{ $batch->productionLine->name ?? '-' }}"
                                                data-target-dose="{{ (int) $batch->target_dose }}"
                                                data-freq="{{ (int) $batch->freq . ' Hz' }}"
                                                data-beam-speed="{{ (int) $batch->beam_speed . ' m/s' }}"
                                                data-scan-gear="{{ (int) $batch->scan_gear }}"
                                                data-loading-mode="{{ $batch->loading_mode }}"
                                                data-actual-dose="{{ $qa->actual_dose ?? '-' }}"
                                                data-visual="{{ strtoupper($qa->visual_check ?? '-') }}"
                                                data-indicator="{{ strtoupper($qa->indicator_check ?? '-') }}"
                                                data-damaged="{{ $qa->is_damaged ? 'YES (' . $qa->damaged_qty . ' Box)' : 'NO' }}"
                                                data-damage-desc="{{ $qa->damage_description ?? '-' }}"
                                                data-qa-notes="{{ $qa->qa_notes ?? 'No additional notes' }}"
                                                class="p-2.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all active:scale-90">
                                                <i class="text-lg fa-solid fa-arrow-right-to-bracket"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ MODAL RELOKASI PALLET (POST-IRRADIATION) ═══ --}}
    <div id="relocationModal"
        class="fixed inset-0 z-[200] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-4xl rounded-[3rem] shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

            <div class="flex items-center justify-between px-10 py-8 bg-white border-b border-slate-50">
                <div>
                    <h3 class="text-xl font-black text-slate-800">Pallet Management</h3>
                    <p id="modal_batch_title" class="mt-1 text-xs font-bold tracking-widest uppercase text-emerald-600">
                        BATCH #---</p>
                </div>
                <button onclick="closeRelocationModal()" class="transition-all text-slate-300 hover:text-red-500">
                    <i class="text-2xl fa-solid fa-circle-xmark"></i>
                </button>
            </div>

            <div class="flex flex-1 overflow-hidden">
                {{-- Sisi Kiri: Daftar Pallet (Scrollable) --}}
                <div class="w-1/2 p-8 overflow-y-auto border-r border-slate-50 bg-slate-50/50">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Select Pallet to Move
                    </h4>
                    <div id="pallet_list_container" class="space-y-3">
                    </div>
                </div>

                {{-- Sisi Kanan: Form Relokasi --}}
                <div class="w-1/2 p-8 bg-white">
                    <div id="empty_selection_state"
                        class="flex flex-col items-center justify-center h-full space-y-4 text-center">
                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 text-slate-200">
                            <i class="text-2xl fa-solid fa-hand-pointer"></i>
                        </div>
                        <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Select a pallet from the
                            left<br>to start relocation</p>
                    </div>

                    <form id="relocationForm" action="{{ route('admin.production.relocate-pallet') }}" method="POST"
                        class="hidden space-y-6">
                        @csrf
                        <input type="hidden" name="pallet_content_id" id="relocate_content_id">

                        <div class="p-6 bg-emerald-50 rounded-[2rem] border border-emerald-100">
                            <p class="text-[9px] font-black text-emerald-600 uppercase mb-2">Selected Pallet Info</p>
                            <div class="flex items-end justify-between">
                                <div>
                                    <p id="selected_pallet_loc" class="text-lg font-black text-slate-800">-</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Current Location</p>
                                </div>
                                <div class="text-right">
                                    <p id="selected_pallet_qty" class="text-lg font-black text-emerald-700">-</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Quantity</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">New
                                Destination</label>
                            <select name="new_pallet_id" required
                                class="w-full px-6 py-4 text-sm font-bold transition-all border-none bg-slate-50 rounded-2xl focus:ring-4 focus:ring-emerald-500/10">
                                <option value="">-- Select Target Slot --</option>
                                @foreach ($allLocations ?? [] as $loc)
                                    <option value="{{ $loc->id }}">Line {{ $loc->line }} | Petak
                                        {{ $loc->slot_section }} ({{ $loc->filled_boxes }} boxes in it)</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit"
                            class="w-full py-5 text-xs font-black tracking-widest text-white uppercase transition-all shadow-lg bg-emerald-600 rounded-2xl shadow-emerald-200 hover:bg-emerald-700">
                            Move Pallet Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- DETAIL MODAL --}}
    <div id="finishDetailModal"
        class="fixed inset-0 z-[160] hidden items-center justify-center bg-slate-900/70 backdrop-blur-md p-4 transition-all duration-300">

        <div id="modalContent"
            class="bg-white w-full max-w-5xl rounded-[3rem] shadow-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]">

            {{-- Header: Elegant & Bold --}}
            <div class="flex items-center justify-between px-10 bg-white border-b py-7 border-slate-100">
                <div class="flex items-center gap-5">
                    <div
                        class="flex items-center justify-center border shadow-sm w-14 h-14 text-emerald-600 bg-emerald-50 rounded-2xl border-emerald-100">
                        <i class="text-2xl fa-solid fa-certificate"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-black tracking-tight text-slate-800">Batch Final Review</h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span id="finishBookingCode"
                                class="text-[11px] font-black tracking-widest text-emerald-600 uppercase">ORDER #---</span>
                            <span class="text-slate-300">•</span>
                            <span id="finishBatchNo" class="text-[11px] font-black text-slate-400 uppercase">BATCH
                                #--</span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="closeFinishDetailModal()"
                    class="flex items-center justify-center w-12 h-12 transition-all border bg-slate-50 rounded-2xl text-slate-400 hover:text-red-500 hover:bg-red-50 active:scale-90">
                    <i class="text-xl fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Body: Two Column Balanced Layout --}}
            <div class="px-10 py-8 overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">

                    {{-- Kolom Kiri: Informasi Produk & QA (8 Kolom) --}}
                    <div class="space-y-6 lg:col-span-8">

                        {{-- Info Card --}}
                        <div class="grid grid-cols-2 gap-6 p-8 bg-slate-50 rounded-[2.5rem] border border-slate-100">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Customer
                                    Entity</p>
                                <p id="finishCustomerName" class="text-lg font-black leading-tight text-slate-800">-</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Product
                                    Name</p>
                                <p id="finishProductName" class="text-lg font-black leading-tight text-slate-800">-</p>
                            </div>
                            <div class="pt-4 border-t border-slate-200/60">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Total
                                    Quantity</p>
                                <p id="finishBatchInfo" class="text-base font-bold text-slate-700">-</p>
                            </div>
                            <div class="pt-4 border-t border-slate-200/60">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Production
                                    Line</p>
                                <p id="finishLine" class="text-base font-bold text-slate-700">-</p>
                            </div>
                            <div class="pt-4 border-t border-slate-200/60">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Frequency
                                </p>
                                <p id="finishFreq" class="text-base font-bold text-slate-700">-</p>
                            </div>
                            <div class="pt-4 border-t border-slate-200/60">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Beam Speed
                                </p>
                                <p id="finishSpeed" class="text-base font-bold text-slate-700">-</p>
                            </div>
                            <div class="pt-4 border-t border-slate-200/60">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Scan Gear
                                </p>
                                <p id="finishGear" class="text-base font-bold text-slate-700">-</p>
                            </div>
                            <div class="pt-4 border-t border-slate-200/60">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Loading
                                    Mode
                                </p>
                                <p id="finishLoading" class="text-base font-bold text-slate-700">-</p>
                            </div>
                        </div>

                        {{-- QA Results Table Style --}}
                        <div class="p-8 border-2 border-slate-100 rounded-[2.5rem]">
                            <h4
                                class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 mb-6 flex items-center">
                                <i class="mr-3 fa-solid fa-microscope text-emerald-500"></i> Quality Control Result
                            </h4>
                            <div class="grid grid-cols-3 gap-8">
                                <div class="space-y-1">
                                    <p class="text-[10px] font-black text-slate-400 uppercase">Visual</p>
                                    <p id="finishVisual" class="text-sm font-black text-slate-800">-</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[10px] font-black text-slate-400 uppercase">Indicator</p>
                                    <p id="finishIndicator" class="text-sm font-black text-slate-800">-</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[10px] font-black text-slate-400 uppercase">Damaged</p>
                                    <p id="finishDamaged" class="text-sm font-black text-red-600">-</p>
                                </div>
                            </div>
                            {{-- Damage Desc --}}
                            <div id="damageDescWrapper"
                                class="hidden p-5 mt-6 border bg-red-50 rounded-2xl border-red-100/50">
                                <p class="text-[9px] font-black text-red-400 uppercase mb-1">Damage Details</p>
                                <p id="finishDamageDesc" class="text-sm italic font-medium text-red-700">-</p>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="p-6 border bg-blue-50/40 rounded-2xl border-blue-100/50">
                            <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2">QA Special Notes
                            </p>
                            <p id="finishQaNotes" class="text-sm italic font-medium text-slate-600">--</p>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Dose & Payment (4 Kolom) --}}
                    <div class="space-y-6 lg:col-span-4">

                        {{-- Dose Highlights --}}
                        <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl shadow-slate-200">
                            <div class="mb-6">
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Target Dose
                                </p>
                                <p id="finishTargetDose" class="text-2xl italic font-black">-- <span
                                        class="text-xs font-normal text-slate-500">kGy</span></p>
                            </div>
                            <div class="pt-6 border-t border-slate-800">
                                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Actual
                                    Dose</p>
                                <p id="finishActualDose" class="text-4xl font-black text-emerald-400">-- <span
                                        class="text-xs font-normal uppercase text-emerald-600">kGy</span></p>
                            </div>
                        </div>

                        {{-- Payment Status Card --}}
                        <div id="paymentStatusContainer"
                            class="relative p-6 border border-slate-200 rounded-[2rem] bg-white transition-all duration-500 overflow-hidden shadow-sm">

                            {{-- Decorative Background (Optional for Clean Look) --}}
                            <div class="absolute top-0 right-0 w-24 h-24 -mt-10 -mr-10 opacity-5">
                                <i class="fa-solid fa-file-invoice-dollar text-7xl"></i>
                            </div>

                            <div class="relative flex flex-col items-center text-center">
                                {{-- Ikon: Dikecilkan sedikit agar lebih proporsional --}}
                                <div id="paymentIcon"
                                    class="flex items-center justify-center w-12 h-12 mb-3 border shadow-inner border-slate-100 rounded-2xl bg-slate-50">
                                    <i class="text-xl fa-solid fa-money-bill-transfer"></i>
                                </div>

                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Billing
                                    Status</p>

                                {{-- Status Text: Bold tapi tidak terlalu lebar --}}
                                <h4 id="paymentStatusText"
                                    class="text-base font-black tracking-tight uppercase text-slate-800">
                                    -
                                </h4>

                                {{-- Form Update: Dibuat lebih menyatu (Seamless) --}}
                                <div class="w-full pt-5 mt-5 border-t border-slate-100">
                                    <form id="updatePaymentForm" method="POST" class="space-y-2">
                                        @csrf
                                        @method('PUT')
                                        <div class="flex flex-col gap-2">
                                            <label
                                                class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Update
                                                Status</label>
                                            <select name="payment_status" onchange="this.form.submit()"
                                                class="w-full px-4 py-2.5 text-[10px] font-black uppercase border-slate-100 rounded-xl bg-slate-50/50 hover:bg-white hover:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none cursor-pointer">
                                                <option value="unpaid">Set as UNPAID</option>
                                                <option value="paid">Set as PAID</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Alert Status: Dibuat lebih ringkas (Compact) --}}
                        <div id="unpaidAlert"
                            class="flex items-center hidden gap-4 p-4 mt-4 transition-all duration-300 border border-red-100 bg-red-50 rounded-2xl">
                            <div
                                class="flex items-center justify-center flex-shrink-0 w-10 h-10 text-white bg-red-600 shadow-md rounded-xl shadow-red-200">
                                <i class="text-sm fa-solid fa-lock"></i>
                            </div>
                            <div class="text-left">
                                <p class="text-[10px] font-black text-red-600 uppercase tracking-tighter leading-tight">
                                    Certificate Locked
                                </p>
                                <p class="text-[9px] font-medium text-red-500/80">Payment settlement required.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer: Actions --}}
            <div class="flex items-center justify-between px-10 py-8 border-t bg-slate-50 border-slate-100">
                <p class="text-[10px] font-medium text-slate-400 italic">Please verify all data before issuing the
                    certificate.</p>
                <div class="flex items-center gap-4">
                    <button type="button" onclick="closeFinishDetailModal()"
                        class="px-8 py-4 text-xs font-black uppercase transition-all text-slate-500 hover:text-slate-800">
                        Cancel
                    </button>

                    <a id="printCertificateBtn" href="#" target="_blank"
                        class="flex items-center gap-3 px-10 py-4 text-xs font-black text-white uppercase transition-all shadow-xl bg-emerald-600 rounded-2xl hover:bg-emerald-700 shadow-emerald-200 active:scale-95">
                        <i class="text-lg fa-solid fa-print"></i>
                        Print Official Certificate
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Styling Scrollbar Modern */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }
    </style>

@endsection

@push('scripts')
    <script>
        function openRelocationModal(batchNo, pallets) {
            document.getElementById('modal_batch_title').innerText = 'BATCH #' + batchNo;
            const container = document.getElementById('pallet_list_container');
            container.innerHTML = ''; // Reset list

            pallets.forEach((p, index) => {
                const item = document.createElement('div');
                item.className =
                    "group p-5 bg-white border border-slate-100 rounded-2xl cursor-pointer hover:border-emerald-500 transition-all shadow-sm";
                item.onclick = () => selectPalletForMove(p.id, p.loc, p.qty);

                item.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-500 group-hover:bg-emerald-100 group-hover:text-emerald-600">
                        P${index + 1}
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-800">${p.loc}</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Pre-Irradiation Slot</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-black text-emerald-600">${p.qty} Box</p>
                </div>
            </div>
        `;
                container.appendChild(item);
            });

            // Reset state kanan
            document.getElementById('relocationForm').classList.add('hidden');
            document.getElementById('empty_selection_state').classList.remove('hidden');

            const modal = document.getElementById('relocationModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function selectPalletForMove(id, loc, qty) {
            document.getElementById('empty_selection_state').classList.add('hidden');
            const form = document.getElementById('relocationForm');
            form.classList.remove('hidden');

            document.getElementById('relocate_content_id').value = id;
            document.getElementById('selected_pallet_loc').innerText = loc;
            document.getElementById('selected_pallet_qty').innerText = qty + ' Boxes';
        }

        function closeRelocationModal() {
            document.getElementById('relocationModal').classList.replace('flex', 'hidden');
        }

        // Tambahan: Close modal saat klik background
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('relocationModal');
            if (e.target === modal) closeRelocationModal();
        });


        function openFinishDetailModal(button) {
            const modal = document.getElementById('finishDetailModal');
            const content = document.getElementById('modalContent');

            // Ambil data dari atribut dataset tombol
            const d = button.dataset;

            // Isi Konten Modal
            document.getElementById('finishBookingCode').textContent = `ORDER #${d.bookingCode}`;
            document.getElementById('finishCustomerName').textContent = d.companyName;
            document.getElementById('finishProductName').textContent = d.productName;
            document.getElementById('finishBatchNo').textContent = `BATCH #${d.batchNo}`;
            document.getElementById('finishBatchInfo').textContent = `${d.quantity} ${d.unit}`;
            document.getElementById('finishLine').textContent = d.line;
            document.getElementById('finishTargetDose').textContent = `${d.targetDose} kGy`;
            document.getElementById('finishActualDose').textContent = `${d.actualDose} kGy`;

            document.getElementById('finishFreq').textContent = d.freq;
            document.getElementById('finishSpeed').textContent = d.beamSpeed;
            document.getElementById('finishGear').textContent = d.scanGear;
            document.getElementById('finishLoading').textContent = d.loadingMode;

            document.getElementById('finishVisual').textContent = d.visual;
            document.getElementById('finishIndicator').textContent = d.indicator;
            document.getElementById('finishDamaged').textContent = d.damaged;
            document.getElementById('finishQaNotes').textContent = d.qaNotes;

            // 2. Logika Pembayaran & Tombol Print
            const paymentStatus = d.paymentStatus; // 'paid' atau 'unpaid'
            const printBtn = document.getElementById('printCertificateBtn');
            const unpaidAlert = document.getElementById('unpaidAlert');
            const paymentCont = document.getElementById('paymentStatusContainer');
            const paymentText = document.getElementById('paymentStatusText');
            const paymentIcon = document.getElementById('paymentIcon');
            const updateForm = document.getElementById('updatePaymentForm');
            // Pastikan path URL ini sesuai dengan route yang didefinisikan
            updateForm.action = `/admin/bookings/${d.bookingId}/payment-status`;
            if (paymentStatus === 'paid') {
                // UI JIKA SUDAH BAYAR
                paymentText.textContent = "PAID & VERIFIED";
                paymentCont.className =
                    "p-6 border border-emerald-100 bg-emerald-50/50 rounded-[2rem] flex items-center justify-between";
                paymentText.className = "text-sm font-black uppercase italic tracking-tighter text-emerald-600";
                paymentIcon.className =
                    "flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600";

                printBtn.classList.remove('hidden'); // Tampilkan tombol print
                unpaidAlert.classList.add('hidden');
                printBtn.href = `/admin/production/batches/${d.batchId}/certificate`;
            } else {
                // UI JIKA BELUM BAYAR
                paymentText.textContent = "AWAITING PAYMENT";
                paymentCont.className =
                    "p-6 border border-red-100 bg-red-50/50 rounded-[2rem] flex items-center justify-between";
                paymentText.className = "text-sm font-black uppercase italic tracking-tighter text-red-600";
                paymentIcon.className = "flex items-center justify-center w-12 h-12 rounded-2xl bg-red-100 text-red-600";

                printBtn.classList.add('hidden'); // Sembunyikan tombol print
                unpaidAlert.classList.remove('hidden');
            }

            // Logika Deskripsi Kerusakan
            const damageWrapper = document.getElementById('damageDescWrapper');
            if (d.damaged.includes('YES')) {
                damageWrapper.classList.remove('hidden');
                document.getElementById('finishDamageDesc').textContent = d.damageDesc;
            } else {
                damageWrapper.classList.add('hidden');
            }

            // Munculkan Modal dengan Animasi
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);

            const batchId = button.getAttribute('data-batch-id');
            document.getElementById('printCertificateBtn').href = `/admin/production/batches/${batchId}/certificate`;
        }

        function closeFinishDetailModal() {
            const modal = document.getElementById('finishDetailModal');
            const content = document.getElementById('modalContent');

            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.replace('flex', 'hidden');
            }, 300);
        }
    </script>
@endpush

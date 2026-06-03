@extends('admin.layout.app')

@section('title', 'Product Finish')

@section('content')

    <div class="w-full pb-12 space-y-6">

        {{-- ═══ HEADER ═══ --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 mb-3 text-xs font-semibold tracking-widest uppercase border rounded-full bg-emerald-50 text-emerald-600 border-emerald-100">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Step 3 of 3
                </div>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">Product Finish</h1>
                <p class="mt-1 text-sm text-gray-400">
                    Batch berstatus <span class="font-medium text-emerald-600">Finish</span> yang telah melewati Quality
                    Control.
                </p>
            </div>
        </div>

        {{-- ═══ TABEL ═══ --}}
        @php
            $doneRows = [];
            foreach ($bookings as $booking) {
                $product = $booking->products->first();
                foreach ($booking->batches->where('status', 'done') as $batch) {
                    $doneRows[] = [
                        'booking' => $booking,
                        'product' => $product,
                        'batch' => $batch,
                        'qa' => $batch->qa,
                    ];
                }
            }
        @endphp

        <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">

            {{-- Table Header Bar --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800">Daftar Batch Finish</h2>
                        <p class="text-xs text-gray-400">{{ count($doneRows) }} batch tersedia</p>
                    </div>
                </div>
            </div>

            @if (empty($doneRows))
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center gap-3 px-6 py-24 text-center">
                    <div class="flex items-center justify-center w-16 h-16 border border-gray-100 rounded-2xl bg-gray-50">
                        <svg class="text-gray-300 w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600">Belum Ada Batch Finish</h3>
                        <p class="max-w-xs mt-1 text-xs text-gray-400">Batch yang sudah melewati Quality Control akan
                            otomatis muncul di sini.</p>
                    </div>
                </div>
            @else
                {{-- Desktop Table --}}
                <div class="hidden overflow-x-auto lg:block">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-50 bg-gray-50/60">
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                    Booking & Customer</th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                    Product</th>
                                <th
                                    class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                    Batch</th>
                                <th
                                    class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                    Target / Actual</th>
                                <th
                                    class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                    QA</th>
                                <th
                                    class="px-6 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($doneRows as $row)
                                @php
                                    $booking = $row['booking'];
                                    $product = $row['product'];
                                    $batch = $row['batch'];
                                    $qa = $row['qa'];
                                    $palletData = \App\Models\PalletContent::with('pallet')
                                        ->where('booking_id', $booking->id)
                                        ->get()
                                        ->map(
                                            fn($item) => [
                                                'id' => $item->id,
                                                'qty' => $item->quantity,
                                                'loc' => "Line {$item->pallet->line} - Petak {$item->pallet->slot_section}",
                                            ],
                                        );
                                @endphp
                                <tr class="transition-colors hover:bg-gray-50/60 group">
                                    <td class="px-6 py-4 align-middle">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center justify-center flex-shrink-0 text-xs font-bold w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700">
                                                {{ strtoupper(substr($booking->customer->company_name ?? '?', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">#{{ $booking->booking_code }}
                                                </p>
                                                <p class="text-xs text-gray-400 mt-0.5">
                                                    {{ $booking->customer->company_name ?? 'Guest' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <p class="text-sm font-medium text-gray-800">{{ $product->product_name ?? '-' }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $batch->productionLine->name ?? '-' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <p class="text-sm font-semibold text-gray-800">Batch #{{ $batch->batch_number }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ number_format($batch->quantity) }}
                                            {{ $batch->unit }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <div class="inline-flex items-center gap-1.5 text-xs">
                                            <span class="text-gray-400">T: {{ (int) $batch->target_dose }}</span>
                                            <span class="text-gray-200">/</span>
                                            <span class="font-semibold text-emerald-600">A:
                                                {{ $qa->actual_dose ?? '-' }}</span>
                                        </div>
                                        <p class="text-[10px] text-gray-300 mt-0.5">kGy</p>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        @if ($qa && $qa->visual_check == 'pass')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full">
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>Passed
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold text-red-600 bg-red-50 border border-red-100 rounded-full">
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right align-middle">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button"
                                                onclick="openRelocationModal('{{ $batch->batch_number }}', {{ json_encode($palletData) }})"
                                                class="inline-flex items-center justify-center w-8 h-8 transition-colors rounded-lg text-amber-500 hover:bg-amber-50"
                                                title="Kelola Relokasi Pallet">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                                </svg>
                                            </button>
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
                                                data-offline-at="{{ $batch->offline_at }}"
                                                data-finished-at="{{ $batch->finished_at }}"
                                                data-total-duration="{{ $batch->total_duration }}"
                                                class="inline-flex items-center justify-center w-8 h-8 text-gray-400 transition-colors rounded-lg hover:bg-emerald-50 hover:text-emerald-600"
                                                title="Lihat Detail">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile / Tablet Card List --}}
                <div class="divide-y divide-gray-100 lg:hidden">
                    @foreach ($doneRows as $row)
                        @php
                            $booking = $row['booking'];
                            $product = $row['product'];
                            $batch = $row['batch'];
                            $qa = $row['qa'];
                            $palletData = \App\Models\PalletContent::with('pallet')
                                ->where('booking_id', $booking->id)
                                ->get()
                                ->map(
                                    fn($item) => [
                                        'id' => $item->id,
                                        'qty' => $item->quantity,
                                        'loc' => "Line {$item->pallet->line} - Petak {$item->pallet->slot_section}",
                                    ],
                                );
                        @endphp
                        <div class="p-5 space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center flex-shrink-0 w-10 h-10 text-sm font-bold rounded-xl bg-emerald-50 text-emerald-700">
                                        {{ strtoupper(substr($booking->customer->company_name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">#{{ $booking->booking_code }}</p>
                                        <p class="text-xs text-gray-400">{{ $booking->customer->company_name ?? 'Guest' }}
                                        </p>
                                    </div>
                                </div>
                                @if ($qa && $qa->visual_check == 'pass')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full flex-shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>Passed
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold text-red-600 bg-red-50 border border-red-100 rounded-full flex-shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>Rejected
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-3 gap-3 p-4 bg-gray-50 rounded-xl">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Product</p>
                                    <p class="text-xs font-semibold text-gray-700 mt-0.5 truncate">
                                        {{ $product->product_name ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Batch</p>
                                    <p class="text-xs font-semibold text-gray-700 mt-0.5">#{{ $batch->batch_number }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Dose</p>
                                    <p class="text-xs font-semibold text-emerald-600 mt-0.5">{{ $qa->actual_dose ?? '-' }}
                                        kGy</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button"
                                    onclick="openRelocationModal('{{ $batch->batch_number }}', {{ json_encode($palletData) }})"
                                    class="flex items-center justify-center flex-1 gap-2 px-4 text-xs font-medium transition-colors border rounded-lg h-9 text-amber-600 bg-amber-50 border-amber-100 hover:bg-amber-100">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                    </svg>Relokasi
                                </button>
                                <button onclick="openFinishDetailModal(this)" data-batch-id="{{ $batch->id }}"
                                    data-booking-id="{{ $booking->id }}"
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
                                    data-offline-at="{{ $batch->offline_at }}"
                                    data-finished-at="{{ $batch->finished_at }}"
                                    data-total-duration="{{ $batch->total_duration }}"
                                    class="flex items-center justify-center flex-1 gap-2 px-4 text-xs font-medium transition-colors border rounded-lg h-9 text-emerald-700 bg-emerald-50 border-emerald-100 hover:bg-emerald-100">
                                    Detail<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>


    {{-- ═══ MODAL RELOKASI PALLET ═══ --}}
    <div id="relocationModal"
        class="fixed inset-0 z-50 items-center justify-center hidden p-4 bg-gray-900/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-3xl rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Pallet Management</h3>
                    <p id="modal_batch_title" class="text-xs text-emerald-600 font-medium mt-0.5">BATCH #---</p>
                </div>
                <button onclick="closeRelocationModal()"
                    class="flex items-center justify-center w-8 h-8 text-gray-400 transition-colors rounded-lg hover:bg-red-50 hover:text-red-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-col flex-1 overflow-hidden md:flex-row">
                <div
                    class="w-full p-5 overflow-y-auto border-b border-gray-100 md:w-1/2 md:border-b-0 md:border-r bg-gray-50/50">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Pilih Pallet</p>
                    <div id="pallet_list_container" class="space-y-2"></div>
                </div>

                <div class="w-full p-5 bg-white md:w-1/2">
                    <div id="empty_selection_state"
                        class="flex flex-col items-center justify-center h-full gap-3 py-10 text-center">
                        <div
                            class="flex items-center justify-center w-12 h-12 border border-gray-100 rounded-xl bg-gray-50">
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zm-7.518-.267A8.25 8.25 0 1120.25 10.5M8.288 14.212A5.25 5.25 0 1117.25 10.5" />
                            </svg>
                        </div>
                        <p class="text-xs text-gray-400">Pilih pallet dari kiri<br>untuk memulai relokasi</p>
                    </div>

                    <form id="relocationForm" action="{{ route('admin.production.relocate-pallet') }}" method="POST"
                        class="hidden space-y-5">
                        @csrf
                        <input type="hidden" name="pallet_content_id" id="relocate_content_id">

                        <div class="p-4 border bg-emerald-50 border-emerald-100 rounded-xl">
                            <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider mb-2">Pallet
                                Dipilih</p>
                            <div class="flex items-end justify-between">
                                <div>
                                    <p id="selected_pallet_loc" class="text-sm font-semibold text-gray-800">-</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">Lokasi saat ini</p>
                                </div>
                                <div class="text-right">
                                    <p id="selected_pallet_qty" class="text-sm font-semibold text-emerald-700">-</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">Jumlah</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Tujuan
                                Baru</label>
                            <select name="new_pallet_id" required
                                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 outline-none transition-all">
                                <option value="">-- Pilih Slot Target --</option>
                                @foreach ($allLocations ?? [] as $loc)
                                    <option value="{{ $loc->id }}">Line {{ $loc->line }} | Petak
                                        {{ $loc->slot_section }} ({{ $loc->filled_boxes }} kotak)</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit"
                            class="w-full h-10 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-colors active:scale-[0.98]">
                            Pindahkan Pallet
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- ═══ DETAIL & EDIT MODAL ═══ --}}
    <div id="finishDetailModal"
        class="fixed inset-0 z-50 items-center justify-center hidden p-4 bg-gray-900/50 backdrop-blur-sm">
        <div id="modalContent"
            class="bg-white w-full max-w-5xl rounded-2xl shadow-xl overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <div
                        class="flex items-center justify-center w-10 h-10 border rounded-xl bg-emerald-50 border-emerald-100">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Batch Final Review & Edit</h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span id="finishBookingCode" class="text-xs font-semibold text-emerald-600">ORDER #---</span>
                            <span class="text-gray-200">·</span>
                            <span id="finishBatchNo" class="text-xs text-gray-400">BATCH #--</span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="closeFinishDetailModal()"
                    class="flex items-center justify-center w-8 h-8 text-gray-400 transition-colors rounded-lg hover:bg-red-50 hover:text-red-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Form Wrapper Utama --}}
            <form action="{{ route('admin.production.update-duration') }}" method="POST"
                class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="batch_id" id="formBatchId">

                {{-- Body Scrollable --}}
                <div class="flex-1 px-6 py-6 space-y-6 overflow-y-auto">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

                        {{-- Kiri: Info Readonly & Form Input Waktu (8 col) --}}
                        <div class="space-y-5 lg:col-span-8">

                            {{-- 🟢 SEKSI FORM EDIT WAKTU & DURASI PRODUCTION (EDITABLE) --}}
                            <div class="p-5 space-y-4 border border-emerald-100 bg-emerald-50/20 rounded-2xl">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <p class="text-xs font-bold tracking-wider uppercase text-emerald-800">Penyesuaian
                                        Waktu & Durasi Kerja</p>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div>
                                        <label class="text-[11px] font-semibold text-gray-500 block mb-1">Waktu Masuk
                                            (Offline / Start)</label>
                                        <input type="datetime-local" id="editOfflineAt" name="offline_at" step="1"
                                            class="w-full px-3 py-2 text-xs transition-all bg-white border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-gray-500 block mb-1">Waktu Selesai
                                            (Finished / End)</label>
                                        <input type="datetime-local" id="editFinishedAt" name="finished_at"
                                            step="1"
                                            class="w-full px-3 py-2 text-xs transition-all bg-white border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-bold text-emerald-700 block mb-1">Total Duration
                                            (Menit)</label>
                                        <input type="number" id="editTotalDuration" name="total_duration" readonly
                                            min="0" required
                                            class="w-full px-3 py-2 text-xs font-bold transition-all bg-white border-2 rounded-lg outline-none border-emerald-200 text-emerald-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    </div>
                                </div>
                            </div>

                            {{-- Info Grid Statis --}}
                            <div
                                class="grid grid-cols-2 gap-4 p-5 border border-gray-100 sm:grid-cols-4 bg-gray-50 rounded-xl">
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">
                                        Customer</p>
                                    <p id="finishCustomerName" class="text-sm font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">
                                        Product</p>
                                    <p id="finishProductName" class="text-sm font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">
                                        Quantity</p>
                                    <p id="finishBatchInfo" class="text-sm font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Line
                                    </p>
                                    <p id="finishLine" class="text-sm font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">
                                        Frequency</p>
                                    <p id="finishFreq" class="text-sm font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Beam
                                        Speed</p>
                                    <p id="finishSpeed" class="text-sm font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Scan
                                        Gear</p>
                                    <p id="finishGear" class="text-sm font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">
                                        Loading Mode</p>
                                    <p id="finishLoading" class="text-sm font-semibold text-gray-800">-</p>
                                </div>
                            </div>

                            {{-- QA Results --}}
                            <div class="p-5 border border-gray-100 rounded-xl">
                                <p
                                    class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                                    </svg>Quality Control
                                </p>
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="p-3 rounded-lg bg-gray-50">
                                        <p class="text-[10px] text-gray-400 uppercase font-medium tracking-wider mb-1">
                                            Visual</p>
                                        <p id="finishVisual" class="text-sm font-semibold text-gray-800">-</p>
                                    </div>
                                    <div class="p-3 rounded-lg bg-gray-50">
                                        <p class="text-[10px] text-gray-400 uppercase font-medium tracking-wider mb-1">
                                            Indicator</p>
                                        <p id="finishIndicator" class="text-sm font-semibold text-gray-800">-</p>
                                    </div>
                                    <div class="p-3 rounded-lg bg-gray-50">
                                        <p class="text-[10px] text-gray-400 uppercase font-medium tracking-wider mb-1">
                                            Damaged</p>
                                        <p id="finishDamaged" class="text-sm font-semibold text-red-600">-</p>
                                    </div>
                                </div>
                                <div id="damageDescWrapper"
                                    class="hidden p-3 mt-3 border border-red-100 rounded-lg bg-red-50">
                                    <p class="text-[10px] font-semibold text-red-400 uppercase tracking-wider mb-1">Detail
                                        Kerusakan</p>
                                    <p id="finishDamageDesc" class="text-sm italic font-medium text-red-700">-</p>
                                </div>
                            </div>

                            {{-- QA Notes --}}
                            <div class="p-4 border border-blue-100 bg-blue-50/60 rounded-xl">
                                <p class="text-[10px] font-semibold text-blue-400 uppercase tracking-wider mb-1">QA Notes
                                </p>
                                <p id="finishQaNotes" class="text-sm italic text-gray-600">--</p>
                            </div>
                        </div>

                        {{-- Kanan: Dose + Payment (4 col) --}}
                        <div class="space-y-4 lg:col-span-4">
                            {{-- Dose Card --}}
                            <div class="p-5 text-white bg-gray-900 rounded-xl">
                                <div class="mb-4">
                                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Target
                                        Dose</p>
                                    <p id="finishTargetDose" class="text-xl font-semibold">-- <span
                                            class="text-xs font-normal text-gray-500">kGy</span></p>
                                </div>
                                <div class="pt-4 border-t border-gray-800">
                                    <p class="text-[10px] font-semibold text-emerald-500 uppercase tracking-wider mb-1">
                                        Actual Dose</p>
                                    <p id="finishActualDose" class="text-3xl font-bold text-emerald-400">-- <span
                                            class="text-xs font-normal uppercase text-emerald-600">kGy</span></p>
                                </div>
                            </div>

                            {{-- Payment Card --}}
                            {{-- Payment Card (Interactive Dropdown) --}}
                            <div id="paymentStatusContainer"
                                class="p-5 transition-all bg-white border border-gray-100 rounded-xl">
                                <div class="flex flex-col items-center gap-3 text-center">
                                    <div id="paymentIcon"
                                        class="flex items-center justify-center w-10 h-10 border border-gray-100 rounded-xl bg-gray-50">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                            stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                        </svg>
                                    </div>
                                    <div class="w-full">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">
                                            Billing Status</p>

                                        {{-- Tampilan text jika sudah Paid --}}
                                        <h4 id="paymentStatusText" class="hidden text-sm font-bold text-gray-800 mt-0.5">-
                                        </h4>

                                        {{-- Dropdown interaktif jika masih Unpaid --}}
                                        <div id="paymentSelectWrapper" class="hidden mt-1">
                                            <select id="editPaymentStatus" name="payment_status"
                                                onchange="triggerPaymentUpdate(this)"
                                                class="w-full px-3 py-1.5 text-xs font-semibold text-center text-red-700 bg-red-50 border border-red-200 rounded-lg outline-none focus:ring-2 focus:ring-red-500/20 transition-all cursor-pointer appearance-none">
                                                <option value="unpaid" class="font-semibold text-red-700 bg-white">
                                                    AWAITING PAYMENT (UNPAID)</option>
                                                <option value="paid" class="font-semibold bg-white text-emerald-700">✨
                                                    MARK AS PAID (LUNAS)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Unpaid Alert --}}
                            <div id="unpaidAlert"
                                class="flex items-center hidden gap-3 p-4 border border-red-100 bg-red-50 rounded-xl">
                                <div
                                    class="flex items-center justify-center flex-shrink-0 w-8 h-8 text-white bg-red-500 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-red-600">Certificate Locked</p>
                                    <p class="text-[11px] text-red-400">Selesaikan pembayaran terlebih dahulu.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Modal Action --}}
                <div
                    class="flex flex-col gap-3 px-6 py-5 border-t border-gray-100 sm:flex-row sm:items-center sm:justify-between bg-gray-50/60">
                    <p class="text-xs text-gray-400">Perubahan data waktu akan langsung memperbarui database produksi.</p>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="closeFinishDetailModal()"
                            class="px-5 text-xs font-medium text-gray-500 transition-colors rounded-lg h-9 hover:text-gray-800 hover:bg-gray-100">
                            Batal
                        </button>

                        {{-- Button Simpan Perubahan Form --}}
                        <button type="submit"
                            class="h-9 px-5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors active:scale-[0.98]">
                            Simpan Perubahan
                        </button>

                        {{-- Link Cetak Sertifikat --}}
                        <a id="printCertificateBtn" href="#" target="_blank"
                            class="inline-flex items-center gap-2 h-9 px-5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors active:scale-[0.98]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                            </svg>Cetak Sertifikat
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Form khusus untuk update status billing/payment agar terpisah --}}
    <form id="updatePaymentForm" method="POST" class="hidden">
        @csrf
        @method('PUT')
        <input type="hidden" name="payment_status" id="payment_status_input">
    </form>

@endsection

@push('scripts')
    <script>
        // ═══ RELOCATION MODAL ═══
        function openRelocationModal(batchNo, pallets) {
            document.getElementById('modal_batch_title').innerText = 'BATCH #' + batchNo;
            const container = document.getElementById('pallet_list_container');
            container.innerHTML = '';

            pallets.forEach((p, index) => {
                const item = document.createElement('div');
                item.className =
                    'group p-4 bg-white border border-gray-100 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/30 transition-all';
                item.onclick = () => selectPalletForMove(p.id, p.loc, p.qty);
                item.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-500 group-hover:bg-emerald-100 group-hover:text-emerald-600">
                                P${index + 1}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">${p.loc}</p>
                                <p class="text-[10px] text-gray-400">Pre-Irradiation</p>
                            </div>
                        </div>
                        <p class="text-sm font-semibold text-emerald-600">${p.qty} Box</p>
                    </div>
                `;
                container.appendChild(item);
            });

            document.getElementById('relocationForm').classList.add('hidden');
            document.getElementById('empty_selection_state').classList.remove('hidden');

            const modal = document.getElementById('relocationModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function selectPalletForMove(id, loc, qty) {
            document.getElementById('empty_selection_state').classList.add('hidden');
            document.getElementById('relocationForm').classList.remove('hidden');
            document.getElementById('relocate_content_id').value = id;
            document.getElementById('selected_pallet_loc').innerText = loc;
            document.getElementById('selected_pallet_qty').innerText = qty + ' Boxes';
        }

        function closeRelocationModal() {
            const modal = document.getElementById('relocationModal');
            modal.classList.replace('flex', 'hidden');
        }

        // Close modal when click outside background overlay
        window.addEventListener('click', function(e) {
            const rm = document.getElementById('relocationModal');
            if (e.target === rm) closeRelocationModal();
            const dm = document.getElementById('finishDetailModal');
            if (e.target === dm) closeFinishDetailModal();
        });


        // ═══ DETAIL & EDIT FORM MODAL ENGINE ═══
        function openFinishDetailModal(button) {
            const modal = document.getElementById('finishDetailModal');
            const content = document.getElementById('modalContent');
            const d = button.dataset;

            // Bind ID Utama untuk update form
            document.getElementById('formBatchId').value = d.batchId;

            // Judul Modal Atas
            document.getElementById('finishBookingCode').textContent = `ORDER #${d.bookingCode}`;
            document.getElementById('finishBatchNo').textContent = `BATCH #${d.batchNo}`;

            // Pengisian Teks Deskriptif Statis
            document.getElementById('finishCustomerName').textContent = d.companyName;
            document.getElementById('finishProductName').textContent = d.productName;
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

            // ═══ SISTEM INPUT EDIT WAKTU & DURASI ═══
            const rawOffline = d.offlineAt;
            const rawFinished = d.finishedAt;
            const savedDuration = d.totalDuration;

            // Normalisasi & Masukkan nilai tanggal ke HTML5 Input picker (Ganti spasi ke karakter 'T')
            document.getElementById('editOfflineAt').value = rawOffline ? rawOffline.replace(' ', 'T').substring(0, 19) :
                '';
            document.getElementById('editFinishedAt').value = rawFinished ? rawFinished.replace(' ', 'T').substring(0, 19) :
                '';

            // Set durasi kerja tersimpan, jika kosong bantu hitung menitnya secara asinkron
            const durationField = document.getElementById('editTotalDuration');
            if (savedDuration && savedDuration !== 'null' && savedDuration !== '') {
                durationField.value = savedDuration;
            } else {
                calculateAutoMinutes();
            }

            // Pasang Listener: Jika penanggalan digeser manual, kalkulator menit mendeteksi otomatis otomatis
            document.getElementById('editOfflineAt').addEventListener('change', calculateAutoMinutes);
            document.getElementById('editFinishedAt').addEventListener('change', calculateAutoMinutes);

            // ═══ VALIDASI PAYMENT & LINK DOWNLOAD SERTIFIKAT ═══
            // ═══ VALIDASI PAYMENT & LINK DOWNLOAD SERTIFIKAT (MODIFIED) ═══
            const isPaid = d.paymentStatus === 'paid';
            const printBtn = document.getElementById('printCertificateBtn');
            const unpaidAlert = document.getElementById('unpaidAlert');
            const paymentCont = document.getElementById('paymentStatusContainer');
            const paymentText = document.getElementById('paymentStatusText');
            const paymentSelectWrapper = document.getElementById('paymentSelectWrapper');
            const paymentSelectInput = document.getElementById('editPaymentStatus');
            const paymentIcon = document.getElementById('paymentIcon');

            // Setup Action Route URL ke Form Tag Pembayaran Terpisah
            const paymentForm = document.getElementById('updatePaymentForm');
            paymentForm.action = `/admin/bookings/${d.bookingId}/payment-status`;

            if (isPaid) {
                // Sembunyikan dropdown select, tampilkan text biasa (Read-only jika sudah lunas)
                paymentSelectWrapper.classList.add('hidden');
                paymentText.classList.remove('hidden');

                paymentText.textContent = 'PAID & VERIFIED';
                paymentText.className = 'text-sm font-bold text-emerald-600 mt-0.5';
                paymentCont.className = 'p-5 border border-emerald-100 rounded-xl bg-emerald-50/40 transition-all';
                paymentIcon.className =
                    'flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-100 border border-emerald-100';
                paymentIcon.querySelector('svg').className = 'w-5 h-5 text-emerald-600';

                printBtn.classList.remove('hidden');
                unpaidAlert.classList.add('hidden');
                printBtn.href = `/admin/production/batches/${d.batchId}/certificate`;
            } else {
                // Sembunyikan text biasa, tampilkan dropdown select jika belum bayar (Editable)
                paymentText.classList.add('hidden');
                paymentSelectWrapper.classList.remove('hidden');

                // Reset value dropdown kembali ke default 'unpaid' setiap modal dibuka
                paymentSelectInput.value = 'unpaid';

                paymentCont.className = 'p-5 border border-red-100 rounded-xl bg-red-50/40 transition-all';
                paymentIcon.className =
                    'flex items-center justify-center w-10 h-10 rounded-xl bg-red-100 border border-red-100';
                paymentIcon.querySelector('svg').className = 'w-5 h-5 text-red-500';

                printBtn.classList.add('hidden');
                unpaidAlert.classList.remove('hidden');
            }
            // Damage Description Wrapper
            const damageWrapper = document.getElementById('damageDescWrapper');
            if (d.damaged.includes('YES')) {
                damageWrapper.classList.remove('hidden');
                document.getElementById('finishDamageDesc').textContent = d.damageDesc;
            } else {
                damageWrapper.classList.add('hidden');
            }

            // Buka Modal dengan transisi css
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        // Kalkulator asinkron berbasis selisih Menit
        function calculateAutoMinutes() {
            const startVal = document.getElementById('editOfflineAt').value;
            const endVal = document.getElementById('editFinishedAt').value;
            const durationInput = document.getElementById('editTotalDuration');

            if (startVal && endVal) {
                const startTime = new Date(startVal);
                const endTime = new Date(endVal);
                const diffMs = endTime - startTime;

                if (diffMs > 0) {
                    const diffMins = Math.floor(diffMs / 1000 / 60);
                    durationInput.value = diffMins;
                } else {
                    durationInput.value = 0;
                }
            }
        }

        function closeFinishDetailModal() {
            const modal = document.getElementById('finishDetailModal');
            const content = document.getElementById('modalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.classList.replace('flex', 'hidden'), 300);
        }

        function triggerPaymentUpdate(selectElement) {
            if (selectElement.value === 'paid') {
                if (confirm(
                        'Apakah Anda yakin ingin memverifikasi pembayaran ini secara manual? Setelah diubah ke PAID, status tidak dapat dikembalikan ke UNPAID dari halaman ini.'
                    )) {
                    const form = document.getElementById('updatePaymentForm');
                    document.getElementById('payment_status_input').value = 'paid';
                    form.submit();
                } else {
                    selectElement.value = 'unpaid'; // Reset jika dibatalkan
                }
            }
        }
    </script>
@endpush

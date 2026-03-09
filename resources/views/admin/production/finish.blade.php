@extends('admin.layout.app')

@section('title', 'Product Finish')

@section('content')

    <div class="w-full pb-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Product Finish</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">
                    Step 3: review batch yang sudah berstatus <span class="font-semibold text-emerald-600">Finish</span>.
                </p>
            </div>
        </div>

        {{-- ═══ TABEL BATCH DONE ═══ --}}
        @php
            $doneRows = [];
            foreach ($bookings as $booking) {
                $product = $booking->products->first();
                foreach ($booking->batches->where('status', 'done') as $batch) {
                    $doneRows[] = [
                        'booking' => $booking,
                        'product' => $product,
                        'batch' => $batch,
                    ];
                }
            }
        @endphp

        <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] p-8">
            <h3 class="mb-4 text-lg font-black text-slate-700">
                <i class="fa-solid fa-circle-check mr-2 text-emerald-600"></i>Daftar Batch Finish
            </h3>

            @if (empty($doneRows))
                <div class="py-12 text-center">
                    <div class="flex flex-col items-center gap-4">
                        <div class="flex items-center justify-center w-20 h-20 rounded-full bg-slate-100">
                            <i class="text-3xl fa-solid fa-box-archive text-slate-300"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-600">Belum Ada Batch Finish</h3>
                        <p class="text-sm text-slate-400">
                            Batch yang sudah selesai dari tahap <strong>Process Product Irradiation</strong>
                            akan muncul di sini.
                        </p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-3">
                        <thead>
                            <tr class="text-[10px] font-black tracking-[0.18em] text-slate-500 uppercase">
                                <th class="px-6 py-3">Booking</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3 text-center">Batch</th>
                                <th class="px-6 py-3 text-center">Qty</th>
                                <th class="px-6 py-3 text-center">Line</th>
                                <th class="px-6 py-3 text-center">Target Dose</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($doneRows as $row)
                                @php
                                    $booking = $row['booking'];
                                    $product = $row['product'];
                                    $batch = $row['batch'];
                                @endphp
                                <tr class="bg-white rounded-2xl shadow-sm border border-slate-100">
                                    <td class="px-6 py-4 align-middle">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center justify-center w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 font-black text-xs">
                                                {{ strtoupper(substr($booking->customer->name ?? '?', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-800">#{{ $booking->booking_code }}</p>
                                                <p class="text-[11px] font-semibold text-slate-400">
                                                    {{ ucfirst($booking->status) }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $booking->customer->name ?? 'Guest' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $product->product_name ?? '-' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-xs font-black text-emerald-700 uppercase bg-emerald-50 rounded-lg">
                                            Batch #{{ $batch->batch_number }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $batch->quantity }} {{ $batch->unit }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $batch->productionLine->name ?? '-' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $batch->target_dose ? $batch->target_dose . ' kGy' : '-' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 text-[10px] font-black text-emerald-700 uppercase bg-emerald-50 rounded-lg">
                                            <i class="fa-solid fa-check mr-1"></i> Finished
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right align-middle">
                                        <button
                                            onclick="openFinishDetailModal(this)"
                                            data-booking-code="{{ $booking->booking_code }}"
                                            data-customer-name="{{ $booking->customer->name ?? 'Guest' }}"
                                            data-product-name="{{ $product->product_name ?? '-' }}"
                                            data-quantity="{{ $batch->quantity }}"
                                            data-unit="{{ $batch->unit }}"
                                            data-line="{{ $batch->productionLine->name ?? '-' }}"
                                            data-target-dose="{{ $batch->target_dose ?? '' }}"
                                            data-beam-speed="{{ $batch->beam_speed ?? '' }}"
                                            data-loading-mode="{{ $batch->loading_mode ?? '' }}"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-black uppercase rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-900 hover:text-white transition-all active:scale-95">
                                            <i class="fa-solid fa-eye"></i>
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    {{-- DETAIL MODAL --}}
    <div id="finishDetailModal"
        class="fixed inset-0 z-[160] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-6">
        <div class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl p-10 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-black text-slate-800">Detail Product Finish</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Ringkasan parameter batch yang sudah selesai proses penyinaran.
                    </p>
                </div>
                <button type="button" onclick="closeFinishDetailModal()"
                    class="flex items-center justify-center w-9 h-9 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="p-4 border border-slate-100 rounded-2xl bg-slate-50/70">
                    <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Booking</p>
                    <p id="finishBookingCode" class="text-sm font-black text-slate-800">#-</p>
                    <p class="mt-2 text-[10px] font-black text-slate-400 uppercase mb-1">Customer</p>
                    <p id="finishCustomerName" class="text-sm font-bold text-slate-700">-</p>
                    <p class="mt-2 text-[10px] font-black text-slate-400 uppercase mb-1">Product</p>
                    <p id="finishProductName" class="text-sm font-bold text-slate-700">-</p>
                </div>
                <div class="p-4 border border-slate-100 rounded-2xl bg-slate-50/70 space-y-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Batch</p>
                    <p id="finishBatchInfo" class="text-sm font-black text-slate-800">-</p>
                    <p class="text-[10px] font-black text-slate-400 uppercase mt-3 mb-1">Production Line</p>
                    <p id="finishLine" class="text-sm font-bold text-slate-700">-</p>
                    <p class="text-[10px] font-black text-slate-400 uppercase mt-3 mb-1">Target Dose (kGy)</p>
                    <p id="finishTargetDose" class="text-sm font-bold text-slate-700">-</p>
                    <p class="text-[10px] font-black text-slate-400 uppercase mt-3 mb-1">Beam Speed (m/s)</p>
                    <p id="finishBeamSpeed" class="text-sm font-bold text-slate-700">-</p>
                    <p class="text-[10px] font-black text-slate-400 uppercase mt-3 mb-1">Loading Mode</p>
                    <p id="finishLoadingMode" class="text-sm font-bold text-slate-700">-</p>
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button type="button" onclick="closeFinishDetailModal()"
                    class="px-6 py-3 text-xs font-black uppercase rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openFinishDetailModal(button) {
            const modal = document.getElementById('finishDetailModal');

            const bookingCode = button.getAttribute('data-booking-code');
            const customerName = button.getAttribute('data-customer-name');
            const productName = button.getAttribute('data-product-name');
            const quantity = button.getAttribute('data-quantity');
            const unit = button.getAttribute('data-unit');
            const line = button.getAttribute('data-line');
            const targetDose = button.getAttribute('data-target-dose');
            const beamSpeed = button.getAttribute('data-beam-speed');
            const loadingMode = button.getAttribute('data-loading-mode');

            modal.querySelector('#finishBookingCode').textContent = `#${bookingCode}`;
            modal.querySelector('#finishCustomerName').textContent = customerName;
            modal.querySelector('#finishProductName').textContent = productName;
            modal.querySelector('#finishBatchInfo').textContent = `${quantity} ${unit}`;
            modal.querySelector('#finishLine').textContent = line || '-';
            modal.querySelector('#finishTargetDose').textContent = targetDose ? `${targetDose} kGy` : '-';
            modal.querySelector('#finishBeamSpeed').textContent = beamSpeed || '-';
            modal.querySelector('#finishLoadingMode').textContent = loadingMode || '-';

            modal.classList.replace('hidden', 'flex');
        }

        function closeFinishDetailModal() {
            document.getElementById('finishDetailModal').classList.replace('flex', 'hidden');
        }
    </script>
@endpush


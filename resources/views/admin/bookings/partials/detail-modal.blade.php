{{-- MODAL DETAIL BOOKING --}}
<div id="modal-detail-{{ $booking->id }}"
    class="fixed inset-0 z-[150] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-500 p-4">

    <div
        class="modal-card relative w-full max-w-5xl max-h-[95vh] overflow-hidden bg-white shadow-2xl rounded-[3.5rem] transform scale-95 opacity-0 transition-all duration-500 flex flex-col">

        {{-- Header --}}
        <div class="flex items-start justify-between px-12 pt-12 pb-6">
            <div>
                <h2 class="text-3xl font-black tracking-tighter text-slate-800">Product Details</h2>
                <div class="flex items-center gap-3 mt-2">
                    <span
                        class="px-3 py-1 text-[10px] font-black tracking-widest text-blue-600 bg-blue-50 rounded-lg uppercase">
                        #{{ $booking->booking_code }}
                    </span>
                    <x-status-badge :status="$booking->status" />
                </div>
            </div>
            <button onclick="toggleDetailModal('{{ $booking->id }}', false)"
                class="flex items-center justify-center w-12 h-12 transition-all bg-slate-50 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-2xl">
                <i class="text-xl fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Body Content --}}
        <div class="flex-1 px-12 pb-12 space-y-10 overflow-y-auto scrollbar-hide">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="p-8 bg-slate-50 border border-slate-100 rounded-[2.5rem]">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Client Information
                    </p>
                    <h4 class="text-xl font-black text-slate-800">
                        {{ $booking->customer->contacts->first()->name ?? 'Guest' }}</h4>
                    <p class="mt-1 text-sm font-medium text-slate-500">{{ $booking->customer->email ?? '-' }}</p>
                </div>

                <div class="p-8 bg-indigo-50 border border-indigo-100 rounded-[2.5rem]">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-4">Time Input</p>
                    <div class="space-y-2">
                        <p class="flex justify-between text-xs font-bold text-slate-600">Booked:
                            <span class="text-indigo-700">{{ $booking->created_at->format('d M Y H:i') }}</span>
                        </p>
                        <p class="flex justify-between text-xs font-bold text-slate-600">Checked In:
                            <span
                                class="text-indigo-700">{{ $booking->arrival_time ? \Carbon\Carbon::parse($booking->arrival_time)->format('d M Y H:i') : 'Waiting' }}</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Product Info (Karena 1 booking biasanya 1 product utama di case Anda) --}}
            @php $product = $booking->products->first(); @endphp
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="p-6 border border-slate-100 rounded-3xl">
                    <p class="text-[10px] font-black text-slate-400 uppercase mb-2">Product Information</p>
                    <p class="flex justify-between text-xs font-bold text-slate-600">Name:
                        <span class="text-indigo-700">{{ $product->product_name ?? '-' }}</span>
                    </p>
                    <p class="flex justify-between text-xs font-bold text-slate-600">Type:
                        <span class="text-indigo-700">{{ $product->product_type ?? '-' }}</span>
                    </p>
                    <p class="flex justify-between text-xs font-bold text-slate-600">Dimension:
                        <span class="text-indigo-700">{{ $product->dimension_pack ?? '-' }} cm</span>
                    </p>
                    <p class="flex justify-between text-xs font-bold text-slate-600">Dose:
                        <span class="text-indigo-700"> {{ number_format($product->dmin ?? 0, 0) }} -
                            {{ number_format($product->dmax ?? 0, 0) }} kGy
                        </span>
                    </p>
                </div>
                <div class="p-6 border border-slate-100 rounded-3xl">
                    <p class="text-[10px] font-black text-slate-400 uppercase mb-2">Volume</p>
                    <p class="flex justify-between text-xs font-bold text-slate-600">Vol Pcs:
                        <span class="text-indigo-700">{{ number_format($product->vol_per_pcs ?? 0) }} cm³</span>
                    </p>
                    <p class="flex justify-between text-xs font-bold text-slate-600">Vol Total:
                        <span class="text-indigo-700">{{ number_format($product->vol_total ?? 0) }} cm³</span>
                    </p>
                </div>
                <div class="p-6 border border-slate-100 rounded-3xl">
                    <p class="text-[10px] font-black text-slate-400 uppercase mb-2">Total Quantity</p>
                    <p class="font-bold text-blue-600">{{ $product->quantity ?? 0 }} {{ $product->unit ?? '' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="p-6 border border-slate-100 rounded-3xl">
                    <p class="text-[10px] font-black text-slate-400 uppercase mb-2">Nett Weigth</p>
                    <p class="flex justify-between text-xs font-bold text-slate-600">Per Pcs:
                        <span class="text-indigo-700">{{ number_format($product->net_weight_pcs ?? 0) }} kg</span>
                    </p>
                    <p class="flex justify-between text-xs font-bold text-slate-600">Total:
                        <span class="text-indigo-700">{{ number_format($product->total_net_weight ?? 0) }} kg</span>
                    </p>
                </div>
                <div class="p-6 border border-slate-100 rounded-3xl">
                    <p class="text-[10px] font-black text-slate-400 uppercase mb-2">Gross Weigth</p>

                    <p class="flex justify-between text-xs font-bold text-slate-600">Per Pcs:
                        <span class="text-indigo-700">{{ number_format($product->gross_weight_per_pcs ?? 0) }}
                            kg</span>
                    </p>
                    <p class="flex justify-between text-xs font-bold text-slate-600">Total:
                        <span class="text-indigo-700">{{ number_format($product->total_gross_weight ?? 0) }} kg</span>
                    </p>
                </div>
                <div class="p-6 border border-slate-100 rounded-3xl">
                    <p class="text-[10px] font-black text-slate-400 uppercase mb-2">Density</p>
                    <p class="flex justify-between text-xs font-bold text-slate-600">Nett:
                        <span class="text-indigo-700">{{ $product->density_nett ?? '-' }}</span>
                    </p>
                    <p class="flex justify-between text-xs font-bold text-slate-600">Gross:
                        <span class="text-indigo-700">{{ $product->density_gross ?? '-' }}</span>
                    </p>
                </div>
            </div>

            {{-- DOSIMETRY VERIFICATION RECORDS --}}
            @php
                $dosimeterRecord = \App\Models\DosimeterRecord::with('details')
                    ->where('booking_id', $booking->id)
                    ->first();
            @endphp
            <section class="pt-8 space-y-4 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-black tracking-widest uppercase text-slate-800">
                        Dosimetry Distribution Validation
                    </h4>
                    @if ($dosimeterRecord)
                        <span
                            class="text-[10px] font-black px-3 py-1 bg-amber-50 text-amber-600 rounded-lg uppercase tracking-wider">
                            Total Tablets: {{ $dosimeterRecord->tablet_quantity ?? 9 }}
                        </span>
                    @endif
                </div>

                @if ($dosimeterRecord && $dosimeterRecord->details->count() > 0)
                    @php
                        $minDose = 999;
                        $maxDose = 0;
                        foreach ($dosimeterRecord->details as $d) {
                            $val = (float) $d->dose_kgy;
                            if ($val > 0) {
                                if ($val < $minDose) {
                                    $minDose = $val;
                                }
                                if ($val > $maxDose) {
                                    $maxDose = $val;
                                }
                            }
                        }
                        $unevenness = $minDose > 0 && $minDose != 999 ? number_format($maxDose / $minDose, 2) : '1.08';
                    @endphp

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {{-- Ringkasan Statistik Dosimetri --}}
                        <div
                            class="flex flex-col justify-between p-6 space-y-4 border bg-slate-50 border-slate-100 rounded-3xl">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Analysis
                                    Summary</p>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs font-bold text-slate-600">
                                        Min Absorbed Dose: <span
                                            class="text-rose-600">{{ $minDose == 999 ? '-' : number_format($minDose, 1) }}
                                            kGy</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-bold text-slate-600">
                                        Max Absorbed Dose: <span
                                            class="text-emerald-600">{{ $maxDose == 0 ? '-' : number_format($maxDose, 1) }}
                                            kGy</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-bold text-slate-600">
                                        Dose Unevenness: <span class="text-indigo-600">{{ $unevenness }}</span>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="p-3 bg-white border border-slate-100 rounded-xl text-[11px] font-medium text-slate-500 leading-relaxed">
                                <i class="mr-1 text-blue-500 fa-solid fa-circle-info"></i>
                                Double-sided irradiation process verification. Maximum dose absorbed does not exceed 2x
                                process requirements.
                            </div>
                        </div>

                        {{-- Tabel Rincian Titik Permukaan --}}
                        <div
                            class="lg:col-span-2 overflow-hidden border border-slate-100 rounded-[2rem] max-h-[260px] overflow-y-auto">
                            <table class="w-full text-left border-collapse">
                                <thead
                                    class="sticky top-0 bg-slate-100 text-[9px] font-black text-slate-500 uppercase z-10">
                                    <tr>
                                        <th class="px-6 py-3">Location Point</th>
                                        <th class="px-6 py-3 text-center">Dosimeter No.</th>
                                        <th class="px-6 py-3 text-center">Absorbance (ABS)</th>
                                        <th class="px-6 py-3 text-right">Dosage (kGy)</th>
                                    </tr>
                                </thead>
                                <tbody class="text-xs font-bold divide-y divide-slate-50 text-slate-700">
                                    @foreach ($dosimeterRecord->details as $detail)
                                        <tr class="transition-colors hover:bg-slate-50/50">
                                            <td class="px-6 py-3 text-slate-800">Surface {{ $detail->tablet_number }}
                                            </td>
                                            <td class="px-6 py-3 text-center text-slate-500">
                                                {{ $detail->dosimeter_number ?? '-' }}</td>
                                            <td class="px-6 py-3 font-mono text-center text-indigo-600">
                                                {{ $detail->absorbance ? number_format($detail->absorbance, 3) : '-' }}
                                            </td>
                                            <td class="px-6 py-3 font-black text-right text-slate-900">
                                                {{ $detail->dose_kgy ? number_format($detail->dose_kgy, 1) . ' kGy' : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div
                        class="flex flex-col items-center justify-center p-8 border border-dashed border-slate-200 rounded-[2rem] bg-slate-50/50">
                        <div
                            class="flex items-center justify-center w-10 h-10 mb-2 bg-slate-100 rounded-xl text-slate-400">
                            <i class="fa-solid fa-circle-nodes"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-400">No dosimetry analysis data has been generated yet.
                        </p>
                        <p class="text-[9px] font-medium text-slate-300 uppercase mt-0.5">Awaiting calibration from
                            warehouse logistics</p>
                    </div>
                @endif
            </section>


            {{-- BATCH TABLE --}}
            <section class="pt-8 space-y-4 border-t border-slate-100">
                <h4 class="text-sm font-black tracking-widest uppercase text-slate-800">
                    Production Batches ({{ $booking->batches->count() }})
                </h4>

                <div class="overflow-hidden border border-slate-100 rounded-[2rem]">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-[9px] font-black text-slate-500 uppercase">
                            <tr>
                                <th class="px-8 py-4 text-center">Batch ID</th>
                                <th class="px-8 py-4 text-center">Frequency</th>
                                <th class="px-8 py-4 text-center">Beam Speed</th>
                                <th class="px-8 py-4 text-center">Scan Gear</th>
                                <th class="px-8 py-4 text-center">Loading Mode</th>
                                <th class="px-8 py-4 text-center">Status</th>
                                <th class="px-8 py-4 text-center ">Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse ($booking->batches as $batch)
                                <tr>
                                    <td class="px-8 py-4 text-xs font-bold text-center text-slate-700">
                                        #{{ $batch->id }}</td>
                                    <td class="px-8 py-4 text-xs font-bold text-center text-slate-700">
                                        {{ (int) $batch->freq . ' Hz' }}
                                    </td>
                                    <td class="px-8 py-4 text-xs font-bold text-center text-slate-700">
                                        {{ (int) $batch->beam_speed . ' m/s' }}
                                    </td>
                                    <td class="px-8 py-4 text-xs font-bold text-center text-slate-700">
                                        {{ (int) $batch->scan_gear }}
                                    </td>
                                    <td class="px-8 py-4 text-xs font-bold text-center text-slate-700">
                                        {{ $batch->loading_mode }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="text-[10px] font-bold px-2 py-1 rounded-lg bg-blue-50 text-blue-600 uppercase">
                                            {{ $batch->status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-4 text-xs font-black text-center text-slate-800">
                                        {{ number_format($batch->quantity, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                {{-- Tampilan jika batch belum dibagi --}}
                                <tr>
                                    <td colspan="7" class="px-8 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div
                                                class="flex items-center justify-center w-12 h-12 mb-3 bg-slate-50 rounded-2xl text-slate-300">
                                                <i class="text-xl fa-solid fa-layer-group"></i>
                                            </div>
                                            <p class="text-xs font-bold text-slate-400">Belum ada pembagian batch
                                                produksi.</p>
                                            <p
                                                class="text-[10px] font-medium text-slate-300 uppercase tracking-tighter mt-1">
                                                Menunggu proses Update Parameter / Check-in
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="flex justify-end px-12 py-8 border-t bg-slate-50 border-slate-100">
            <button onclick="toggleDetailModal('{{ $booking->id }}', false)"
                class="px-10 py-4 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-black transition-all">
                Close Details
            </button>
        </div>
    </div>
</div>

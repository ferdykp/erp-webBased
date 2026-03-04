<div class="flex justify-center w-full p-4 antialiased sm:p-8 bg-slate-50 font-inter">
    <div class="w-full max-w-md overflow-hidden bg-white border border-gray-200 shadow-2xl rounded-3xl">

        <div class="px-8 pt-10 pb-6 text-center">
            <div class="inline-block p-3 mb-6 bg-white border border-gray-100 shadow-sm rounded-2xl">
                {!! QrCode::size(140)->margin(1)->generate($booking->booking_code) !!}
            </div>
            <p class="text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase mb-1">Booking Reference</p>
            <h2 class="font-mono text-2xl font-bold tracking-tight text-slate-900">#{{ $booking->booking_code }}</h2>
        </div>

        <div class="relative flex items-center px-4">
            <div
                class="absolute left-0 w-6 h-6 -ml-3 bg-slate-50 border border-gray-200 rounded-full shadow-[inset_-2px_0_4px_rgba(0,0,0,0.05)]">
            </div>
            <div class="w-full border-t-2 border-gray-100 border-dashed"></div>
            <div
                class="absolute right-0 w-6 h-6 -mr-3 bg-slate-50 border border-gray-200 rounded-full shadow-[inset_2px_0_4px_rgba(0,0,0,0.05)]">
            </div>
        </div>

        <div class="px-8 py-8 space-y-8">

            <div class="flex justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-[10px] font-black tracking-wider text-gray-400 uppercase">Status</p>
                    @php
                        $statusClass = match ($booking->status) {
                            'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                            'completed' => 'bg-blue-50 text-blue-700 border-blue-100',
                            default => 'bg-slate-50 text-slate-700 border-slate-100',
                        };
                    @endphp
                    <span
                        class="inline-flex px-3 py-1 text-[11px] font-bold uppercase rounded-full border {{ $statusClass }}">
                        {{ $booking->status }}
                    </span>
                </div>
                <div class="space-y-1 text-right">
                    <p class="text-[10px] font-black tracking-wider text-gray-400 uppercase">Arrival Time</p>
                    @if ($booking->slot)
                        <p class="text-sm font-bold text-slate-800">
                            {{ \Carbon\Carbon::parse($booking->slot->date)->format('D, d M Y') }}</p>
                        <p class="text-[11px] font-medium text-slate-500">{{ $booking->slot->start_time }} -
                            {{ $booking->slot->end_time }}</p>
                    @else
                        <p class="text-sm italic font-bold text-slate-300 text-nowrap">Waiting for slot</p>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <p class="text-[10px] font-black tracking-wider text-gray-400 uppercase">Items & Technical Specs</p>

                <div class="space-y-3">
                    @foreach ($booking->products as $product)
                        <div class="p-4 border border-slate-50 bg-slate-50/50 rounded-2xl">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">{{ $product->product_name }}</h4>
                                    <p class="text-[10px] font-semibold text-blue-600 uppercase">
                                        {{ $product->product_type }}</p>
                                </div>
                                <span
                                    class="px-2 py-1 text-[11px] font-black bg-white border border-slate-200 rounded-lg shadow-sm">
                                    {{ $product->quantity }} {{ $product->unit }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 pt-3 border-t gap-y-3 gap-x-2 border-slate-200/60">
                                <div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Dose Range
                                    </p>
                                    <p class="text-[11px] font-bold text-slate-700">{{ $product->dmin }} -
                                        {{ $product->dmax }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Temp.
                                        Expect</p>
                                    <p class="text-[11px] font-bold text-slate-700">{{ $product->expect_temp }}°C</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Pack
                                        Dimension</p>
                                    <p class="text-[11px] font-bold text-slate-700 leading-tight">
                                        {{ $product->dimension_pack ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Gross
                                        Weight</p>
                                    <p class="text-[11px] font-bold text-slate-700">
                                        {{ $product->gross_weight_per_pcs }} kg/pcs</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="px-8 py-6 text-center border-t border-gray-50 bg-gray-50/50">
            <p class="text-[11px] font-medium text-slate-400 leading-relaxed">
                Please present this digital ticket and technical specifications upon arrival at the facility.
            </p>
        </div>
    </div>
</div>

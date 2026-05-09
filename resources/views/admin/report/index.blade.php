@extends('admin.layout.app')

@section('title', 'Order Reports')

@section('content')
    <div class="w-full px-2 pb-10 mx-auto space-y-6 sm:px-4 md:px-0 md:space-y-8">

        {{-- HEADER SECTION --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between lg:px-2">
            <div class="space-y-1">
                <h2 class="text-3xl font-black tracking-tighter sm:text-4xl md:text-5xl text-slate-800">
                    {{ $pageTitle ?? 'Master Reporting' }}
                </h2>
                <p class="text-xs font-bold tracking-widest uppercase md:text-sm text-slate-400">
                    {{ isset($category) ? strtoupper($category) . ' Official Report Generation' : 'Generate and download transaction reports.' }}
                </p>
            </div>

            {{-- Counter Badge Mobile-Friendly --}}
            <div
                class="flex items-center self-start gap-3 px-4 py-2 bg-white border shadow-sm border-slate-100 rounded-2xl md:self-center">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Total Records:
                    {{ $reports->total() }}</span>
            </div>
        </div>

        {{-- MAIN CONTENT CARD --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[2rem] sm:rounded-[3rem] overflow-hidden">

            {{-- Desktop Table View (Hidden on very small screens if needed, but here we use overflow) --}}
            <div class="p-4 overflow-x-auto sm:p-8">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead class="hidden md:table-header-group">
                        <tr class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">
                            <th class="px-8 py-4">Customer & Order ID</th>
                            <th class="px-6 py-4 text-center">Date Created</th>
                            <th class="px-6 py-4 text-center">Product Item</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-8 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="block md:table-row-group">
                        @foreach ($reports as $report)
                            <tr
                                class="flex flex-col mb-4 transition-all duration-300 bg-white border md:table-row md:mb-0 group hover:bg-slate-50/80 border-slate-50 rounded-3xl md:border-none">

                                {{-- Customer Info --}}
                                <td
                                    class="px-6 py-6 md:px-8 md:rounded-l-[2.5rem] md:border-y md:border-l border-transparent group-hover:border-slate-100">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 font-black transition-colors shadow-inner text-emerald-700 bg-emerald-50 rounded-2xl group-hover:bg-emerald-100 shrink-0">
                                            {{ strtoupper(substr($report->customer->contacts->first()->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-black text-slate-800 text-[14px] leading-tight truncate">
                                                {{ $report->customer->company_name ?? 'Unnamed Company' }}
                                            </p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase mt-1 tracking-wider">
                                                #{{ $report->booking_code }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Date (Responsive Grid for Mobile) --}}
                                <td
                                    class="flex items-center justify-between px-6 py-4 border-t border-transparent md:py-6 md:text-center md:border-y group-hover:border-slate-100 md:table-cell md:border-t-transparent border-slate-50">
                                    <span class="text-[10px] font-black uppercase text-slate-400 md:hidden">Date</span>
                                    <div class="text-right md:text-center">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $report->created_at->format('d M Y') }}</p>
                                        <p class="text-[10px] text-slate-400 uppercase font-bold">
                                            {{ $report->created_at->format('H:i') }} WIB</p>
                                    </div>
                                </td>

                                {{-- Product --}}
                                <td
                                    class="flex items-center justify-between px-6 py-4 border-t border-transparent md:py-6 md:text-center md:border-y group-hover:border-slate-100 md:table-cell md:border-t-transparent border-slate-50">
                                    <span class="text-[10px] font-black uppercase text-slate-400 md:hidden">Product</span>
                                    <div
                                        class="inline-block px-4 py-1.5 bg-slate-50 rounded-full border border-slate-100 group-hover:bg-white text-right md:text-center">
                                        <p class="text-[11px] font-black text-slate-700">
                                            {{ $report->products->first()->product_name ?? 'N/A' }}
                                        </p>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td
                                    class="flex items-center justify-between px-6 py-4 border-t border-transparent md:py-6 md:text-center md:border-y group-hover:border-slate-100 md:table-cell md:border-t-transparent border-slate-50">
                                    <span class="text-[10px] font-black uppercase text-slate-400 md:hidden">Status</span>
                                    <x-status-badge :status="$report->status" />
                                </td>

                                {{-- Action: Download Excel --}}
                                <td
                                    class="px-6 py-6 md:px-8 md:py-6 md:rounded-r-[2.5rem] md:border-y md:border-r border-transparent group-hover:border-slate-100 border-t md:border-t-transparent border-slate-50">
                                    @php
                                        $typeMapping = [
                                            'unirradiated-card' => 'jts_unirradiated_card',
                                            'delivery-outbound' => 'jts_delivery_outbound',
                                            'delivery-inbound' => 'jts_delivery_inbound',
                                            'irradiated-card' => 'jts_irradiated_card',
                                            'daily-work' => 'nuc_daily_work',
                                            'processing-record' => 'nuc_processing_record',
                                            'delivery-form' => 'nuc_delivery_form',
                                            'daily-schedule' => 'nuc_daily_schedule',
                                            'equipment-record' => 'nuc_equipment_record',
                                        ];

                                        $finalType = isset($activeType) ? $typeMapping[$activeType] ?? 'all' : 'all';
                                        $btnColor =
                                            isset($category) && $category == 'jts'
                                                ? 'bg-emerald-600 hover:bg-emerald-700'
                                                : 'bg-blue-600 hover:bg-blue-700';
                                    @endphp

                                    <div class="flex items-center justify-center md:justify-end">
                                        <a href="{{ route('admin.report.export-excel', ['id' => $report->id, 'type' => $finalType]) }}"
                                            class="flex items-center justify-center w-full md:w-auto gap-3 px-6 py-4 md:py-3 text-[10px] font-black text-white uppercase transition-all {{ $btnColor }} rounded-2xl hover:shadow-xl active:scale-95 md:group-hover:translate-x-[-5px]">
                                            <i class="text-sm fa-solid fa-file-excel"></i>
                                            Download Excel
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="px-6 py-8 border-t border-slate-50 bg-slate-50/20 sm:px-10">
                {{ $reports->links() }}
            </div>
        </div>

        {{-- Footer Info --}}
        <div class="text-center md:text-left lg:px-2">
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em]">
                System generated report • {{ date('Y') }} Administrator Portal
            </p>
        </div>
    </div>
@endsection

@extends('admin.layout.app')

@section('title', 'Order Reports')

@section('content')
    <div class="w-full pb-10 space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Master Reporting</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Generate and download transaction reports.</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Tombol Export Semua (Opsional) --}}
                {{-- <a href="{{ route('admin.report.export-excel') }}"
                    class="flex items-center gap-2 px-6 py-3 text-xs font-black text-white uppercase transition-all shadow-lg bg-emerald-600 rounded-2xl hover:bg-emerald-700 shadow-emerald-100">
                    <i class="fa-solid fa-file-csv"></i> Export All Data
                </a> --}}
            </div>
        </div>

        {{-- TABLE REPORT --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[3rem] overflow-hidden">
            <div class="flex items-center justify-between p-8 border-b border-slate-50 bg-slate-50/30">
                <h3 class="text-lg font-bold tracking-tight text-slate-800">Transaction History</h3>
                <div class="flex items-center gap-3 px-4 py-2 bg-white border shadow-sm border-slate-200 rounded-xl">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[10px] font-black uppercase text-slate-400">Records: {{ $reports->total() }}</span>
                </div>
            </div>

            <div class="p-8 overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">
                            <th class="px-8 py-4">Customer & Order ID</th>
                            <th class="px-6 py-4 text-center">Date Created</th>
                            <th class="px-6 py-4 text-center">Product Item</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-8 py-4 text-right">Download Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr class="transition-all duration-300 bg-white group hover:bg-slate-50/80">
                                {{-- Customer Info --}}
                                <td
                                    class="px-8 py-6 rounded-l-[2.5rem] border-y border-l border-transparent group-hover:border-slate-100 shadow-sm">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 font-black transition-colors shadow-inner text-emerald-700 bg-emerald-50 rounded-2xl group-hover:bg-emerald-100">
                                            {{ strtoupper(substr($report->customer->contacts->first()->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 text-[14px] leading-none">
                                                {{ $report->customer->company_name ?? 'Unnamed Company' }}
                                            </p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase mt-1 tracking-wider">
                                                #{{ $report->booking_code }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Date --}}
                                <td class="px-6 py-6 text-center border-transparent border-y group-hover:border-slate-100">
                                    <p class="text-sm font-bold text-slate-700">
                                        {{ $report->created_at->format('d M Y') }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold">
                                        {{ $report->created_at->format('H:i') }} WIB</p>
                                </td>

                                {{-- Product --}}
                                <td class="px-6 py-6 text-center border-transparent border-y group-hover:border-slate-100">
                                    <div
                                        class="inline-block px-4 py-1.5 bg-slate-50 rounded-full border border-slate-100 group-hover:bg-white">
                                        <p class="text-[12px] font-black text-slate-700">
                                            {{ $report->products->first()->product_name ?? 'N/A' }}
                                        </p>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-6 text-center border-transparent border-y group-hover:border-slate-100">
                                    <x-status-badge :status="$report->status" />
                                </td>

                                {{-- Action: Download Excel --}}
                                <td
                                    class="px-8 py-6 rounded-r-[2.5rem] border-y border-r border-transparent group-hover:border-slate-100">
                                    <div class="flex items-center justify-end">
                                        {{-- Gunakan route yang kita buat sebelumnya untuk export per baris --}}
                                        <a href="{{ route('admin.report.export-excel', $report->id) }}"
                                            class="flex items-center gap-3 px-6 py-3 text-[10px] font-black text-white uppercase transition-all bg-emerald-600 rounded-2xl hover:bg-emerald-700 hover:shadow-xl hover:shadow-emerald-100 active:scale-95 group-hover:translate-x-[-5px]">
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
            <div class="px-10 py-8 border-t border-slate-50 bg-slate-50/20">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
@endsection

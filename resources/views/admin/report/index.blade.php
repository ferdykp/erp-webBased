@extends('admin.layout.app')

@section('title', isset($category) ? ($pageTitle ?? 'Report') : 'Report Center')

@section('content')
    @php
        $adminRole = auth('admin')->user()?->role;
        $canNuctech = in_array($adminRole, ['superadmin', 'manager', 'production']);
        $canJts = in_array($adminRole, ['superadmin', 'manager', 'cargo_admin']);
    @endphp

    <div class="mx-auto w-full max-w-[1600px] space-y-6 pb-10 sm:space-y-8">
        <section class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="min-w-0">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.14em] text-blue-700">
                        <i class="fa-solid fa-file-lines"></i>
                        Reporting
                    </span>
                    @isset($category)
                        <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-500">
                            {{ $category === 'nuctech' ? 'Technical · Nuctech' : 'Logistics · JTS' }}
                        </span>
                    @endisset
                </div>

                <h1 class="text-2xl font-black tracking-tight text-slate-950 sm:text-3xl lg:text-4xl">
                    {{ $pageTitle ?? 'Report Center' }}
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    @isset($category)
                        Pilih order yang akan dibuatkan dokumen, kemudian download report dalam format Excel.
                    @else
                        Pilih jenis laporan operasional yang dibutuhkan. Laporan technical Nuctech dan logistics JTS tetap menggunakan data order regular yang sama.
                    @endisset
                </p>
            </div>

            @isset($category)
                <a href="{{ route('admin.report.index') }}"
                    class="inline-flex min-h-11 items-center justify-center gap-2 self-start rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 xl:self-auto">
                    <i class="fa-solid fa-arrow-left"></i>
                    Report Center
                </a>
            @endisset
        </section>

        @if(!isset($category))
            <section class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-400">Regular Orders</p>
                            <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ number_format($totalBookings ?? 0) }}</p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <i class="fa-solid fa-box"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-400">Completed</p>
                            <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ number_format($completedBookings ?? 0) }}</p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-400">Customers</p>
                            <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ number_format($totalCustomers ?? 0) }}</p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                            <i class="fa-solid fa-building-user"></i>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-5 xl:grid-cols-2">
                @if($canNuctech)
                    <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 p-5 sm:p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                    <i class="fa-solid fa-file-shield text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-indigo-500">Technical</p>
                                    <h2 class="mt-1 text-lg font-black text-slate-900">Nuctech Operational Reports</h2>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">Dokumen proses, jadwal, delivery, dan pencatatan operasi equipment.</p>
                                </div>
                            </div>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($nuctechReports as $slug => $definition)
                                <a href="{{ route('admin.report.nuctech', $slug) }}"
                                    class="group flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50 sm:px-6">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-xs font-black text-slate-400 transition group-hover:border-indigo-200 group-hover:text-indigo-600">
                                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-slate-800">{{ $definition['short'] }}</p>
                                        <p class="mt-0.5 text-[11px] leading-4 text-slate-400">{{ $definition['description'] }}</p>
                                    </div>
                                    <i class="fa-solid fa-arrow-right text-xs text-slate-300 transition group-hover:translate-x-1 group-hover:text-indigo-500"></i>
                                </a>
                            @endforeach
                        </div>
                    </article>
                @endif

                @if($canJts)
                    <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 p-5 sm:p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                    <i class="fa-solid fa-truck-ramp-box text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-emerald-600">Logistics</p>
                                    <h2 class="mt-1 text-lg font-black text-slate-900">JTS Logistics Reports</h2>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">Dokumen identifikasi material serta inbound dan outbound delivery.</p>
                                </div>
                            </div>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($jtsReports as $slug => $definition)
                                <a href="{{ route('admin.report.jts', $slug) }}"
                                    class="group flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50 sm:px-6">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-xs font-black text-slate-400 transition group-hover:border-emerald-200 group-hover:text-emerald-600">
                                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-slate-800">{{ $definition['short'] }}</p>
                                        <p class="mt-0.5 text-[11px] leading-4 text-slate-400">{{ $definition['description'] }}</p>
                                    </div>
                                    <i class="fa-solid fa-arrow-right text-xs text-slate-300 transition group-hover:translate-x-1 group-hover:text-emerald-500"></i>
                                </a>
                            @endforeach
                        </div>
                    </article>
                @endif
            </section>
        @else
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <p class="text-sm font-black text-slate-900">Available Orders</p>
                        <p class="mt-1 text-[11px] text-slate-400">{{ number_format($reports->total()) }} regular order(s) available for this report.</p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-500">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Excel Export
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px] text-left">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70 text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-400">
                                <th class="px-6 py-3.5">Order</th>
                                <th class="px-5 py-3.5">Customer</th>
                                <th class="px-5 py-3.5">Product</th>
                                <th class="px-5 py-3.5">Created</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($reports as $report)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-black text-slate-900">{{ $report->booking_code }}</p>
                                        <p class="mt-1 text-[10px] font-medium text-slate-400">ID #{{ $report->id }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="max-w-[220px] truncate text-xs font-bold text-slate-700">{{ $report->customer?->company_name ?? 'Unnamed Customer' }}</p>
                                        <p class="mt-1 max-w-[220px] truncate text-[10px] text-slate-400">{{ $report->customer?->contacts?->first()?->name ?? 'No contact' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="max-w-[220px] truncate text-xs font-semibold text-slate-600">{{ $report->products->first()?->product_name ?? 'N/A' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-xs font-bold text-slate-600">{{ $report->created_at?->format('d M Y') }}</p>
                                        <p class="mt-1 text-[10px] text-slate-400">{{ $report->created_at?->format('H:i') }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <x-status-badge :status="$report->status" />
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.report.export-excel', ['id' => $report->id, 'type' => $activeExportType]) }}"
                                            class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 text-[10px] font-extrabold uppercase tracking-[0.08em] text-white transition hover:bg-blue-600">
                                            <i class="fa-solid fa-file-excel text-emerald-400"></i>
                                            Download
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <i class="fa-solid fa-inbox"></i>
                                        </div>
                                        <p class="mt-4 text-sm font-bold text-slate-700">No order data available</p>
                                        <p class="mt-1 text-xs text-slate-400">Regular order akan muncul di sini setelah dibuat.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($reports->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                        {{ $reports->links() }}
                    </div>
                @endif
            </section>
        @endif
    </div>
@endsection

@extends('admin.layout.app')

@section('title', 'Weekly Monitoring')

@section('content')
@php($canEdit = in_array(auth('admin')->user()?->role, ['superadmin', 'production'], true))
<div class="mx-auto max-w-7xl space-y-6">
    <div class="page-heading-card flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">E-Beam Operations</p>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Weekly Monitoring</h1>
            <p class="mt-1 text-sm text-slate-500">Satu Weekly Record per mesin untuk setiap minggu.</p>
        </div>
        @if($canEdit)
            <a href="{{ route('admin.ebeam-weekly.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700">
                <i class="fa-solid fa-plus"></i> Buat Weekly Record
            </a>
        @endif
    </div>

    <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        <label class="text-xs font-bold text-slate-600">Minggu (pilih tanggal dalam minggu)
            <input type="date" name="week" value="{{ request('week') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </label>
        <label class="text-xs font-bold text-slate-600">Mesin
            <select name="machine" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua mesin</option>
                @foreach($machines as $machine)
                    <option value="{{ $machine->id }}" @selected(request('machine') == $machine->id)>{{ $machine->name }}</option>
                @endforeach
            </select>
        </label>
        <div class="flex gap-2">
            <button class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Filter</button>
            <a href="{{ route('admin.ebeam-weekly.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:border-slate-400 hover:bg-slate-200 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="app-data-table min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-extrabold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Mesin</th><th class="px-5 py-3">Minggu</th><th class="px-5 py-3">Tanggal Weekly Record</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $record)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-4 font-bold text-slate-800">{{ $record->productionLine?->name ?? 'Mesin diarsipkan' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $record->week_start->format('d M Y') }} – {{ $record->week_start->copy()->addDays(6)->format('d M Y') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $record->record_date->format('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    @if($canEdit)<a href="{{ route('admin.ebeam-weekly.edit', $record) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">Buka / Edit</a>@endif
                                    <a href="{{ route('admin.ebeam-weekly.download', $record) }}" onclick="return confirm('Are you sure you want to download this Weekly Record PDF?')" class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-100"><i class="fa-solid fa-download mr-1"></i> PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-slate-500">Belum ada Weekly Record untuk filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>@endif
    </div>
</div>
@endsection

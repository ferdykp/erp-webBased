@extends('admin.layout.app')

@section('title', 'Product Testing')

@section('content')
<div class="space-y-5 sm:space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-600">Independent Technical Workflow</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Product Testing</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">Trial, research, dose finding, dan Operational Qualification. Modul ini terpisah dari customer sterilization order sehingga tidak memakai booking slot, warehouse check-in, pallet, atau porter.</p>
        </div>
        <a href="{{ route('admin.testing.create') }}" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99]"><i class="fa-solid fa-plus"></i> New Product Test</a>
    </div>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
        <div class="flex min-h-24 items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-200/30 sm:p-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fa-solid fa-flask-vial"></i></div>
            <div><span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Test</span><strong class="mt-1 block text-xl font-extrabold tracking-tight text-slate-900">{{ number_format($stats['total']) }}</strong></div>
        </div>
        <div class="flex min-h-24 items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-200/30 sm:p-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid fa-sliders"></i></div>
            <div><span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Need Process Parameter</span><strong class="mt-1 block text-xl font-extrabold tracking-tight text-slate-900">{{ number_format($stats['pending']) }}</strong></div>
        </div>
        <div class="flex min-h-24 items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-200/30 sm:p-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-circle-check"></i></div>
            <div><span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Completed</span><strong class="mt-1 block text-xl font-extrabold tracking-tight text-slate-900">{{ number_format($stats['completed']) }}</strong></div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-200/30 sm:p-5">
        <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]">
            <label class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input name="search" value="{{ $search }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-3.5 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Search code, sample, requester, institution...">
            </label>
            <select name="status" class="w-full min-w-0 cursor-pointer rounded-xl border border-slate-200 bg-white px-3.5 py-3 pr-9 text-base font-medium text-slate-800 outline-none hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm">
                <option value="">All status</option>
                <option value="parameter_pending" @selected($status === 'parameter_pending')>Need Process Parameter</option>
                <option value="completed" @selected($status === 'completed')>Completed</option>
            </select>
            <div class="flex gap-2">
                <button class="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99] md:flex-none"><i class="fa-solid fa-filter"></i> Filter</button>
                @if($search || $status)
                    <a href="{{ route('admin.testing.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.99]"><i class="fa-solid fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-3 3xl:grid-cols-4 4xl:grid-cols-5">
        @forelse ($tests as $test)
            @php
                $isComplete = $test->status === 'completed';
                $requester = $test->requester_name ?: ($test->requester_organization ?: 'Internal / not specified');
            @endphp
            <article class="flex min-w-0 flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 transition duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md hover:shadow-slate-200/40">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-blue-50 px-2 py-1 text-[9px] font-extrabold uppercase tracking-wider text-blue-700">{{ $test->test_code }}</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-bold {{ $isComplete ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                <i class="fa-solid {{ $isComplete ? 'fa-circle-check' : 'fa-clock' }}"></i>
                                {{ $isComplete ? 'Completed' : 'Need Parameter' }}
                            </span>
                        </div>
                        <h2 class="mt-3 truncate text-lg font-bold tracking-tight text-slate-900">{{ $test->sample_name }}</h2>
                        <p class="mt-1 truncate text-sm text-slate-500"><i class="fa-regular fa-user mr-1.5 text-slate-400"></i>{{ $requester }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500"><i class="fa-solid fa-flask"></i></div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-px overflow-hidden min-[420px]:grid-cols-2 rounded-xl border border-slate-200 bg-slate-200">
                    <div class="min-w-0 bg-slate-50 p-3"><span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">Quantity</span><b class="mt-1 block truncate text-xs font-semibold text-slate-700">{{ $test->quantity !== null ? \App\Support\NumberFormatter::integer($test->quantity) . ' ' . ($test->unit ?: '') : 'Not specified' }}</b></div>
                    <div class="min-w-0 bg-slate-50 p-3"><span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">Reference Dose</span><b class="mt-1 block truncate text-xs font-semibold text-slate-700">{{ $test->dmin !== null ? \App\Support\NumberFormatter::smart($test->dmin, 4) : '-' }}{{ $test->dmax !== null ? ' – '.\App\Support\NumberFormatter::smart($test->dmax, 4) : '' }}{{ $test->dmin !== null || $test->dmax !== null ? ' kGy' : '' }}</b></div>
                    <div class="min-w-0 bg-slate-50 p-3"><span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">Production Unit</span><b class="mt-1 block truncate text-xs font-semibold text-slate-700">{{ $test->productionLine?->name ?: 'Not set' }}</b></div>
                    <div class="min-w-0 bg-slate-50 p-3"><span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">Dosimeter</span><b class="mt-1 block truncate text-xs font-semibold text-slate-700">{{ $test->dosimeters->count() ? $test->dosimeters->count().' reading(s)' : 'No data' }}</b></div>
                </div>

                @if($test->notes)
                    <p class="mt-4 line-clamp-2 rounded-xl bg-slate-50 px-3.5 py-3 text-xs leading-5 text-slate-600">{{ $test->notes }}</p>
                @endif

                <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                    @if(!$isComplete)
                        <a href="{{ route('admin.testing.parameters', $test) }}" class="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99]"><i class="fa-solid fa-sliders"></i> Process Parameter</a>
                    @else
                        <a href="{{ route('admin.testing.report', $test) }}" class="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99]"><i class="fa-solid fa-file-lines"></i> Open Report</a>
                    @endif
                    <a href="{{ route('admin.testing.edit', $test) }}" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-xs text-slate-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600" title="Edit test data"><i class="fa-solid fa-pen"></i></a>
                    @if($isComplete)
                        <a href="{{ route('admin.testing.parameters', $test) }}" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-xs text-slate-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600" title="Edit process parameter"><i class="fa-solid fa-sliders"></i></a>
                    @endif
                    @if(auth('admin')->user()?->role === 'superadmin')
                        <form method="POST" action="{{ route('admin.testing.destroy', $test) }}" onsubmit="return confirm('Delete this product test?')">@csrf @method('DELETE')<button class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-xs text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
                    @endif
                </div>
            </article>
        @empty
            <div class="flex min-h-72 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center md:col-span-2 2xl:col-span-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-400"><i class="fa-solid fa-flask-vial"></i></div>
                <h3 class="mt-4 text-sm font-bold text-slate-800">No product test yet</h3>
                <p class="mt-1 max-w-md text-xs leading-5 text-slate-400">Create a technical test record for trial, research, dose finding, or Operational Qualification.</p>
                <a href="{{ route('admin.testing.create') }}" class="mt-5 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99]"><i class="fa-solid fa-plus"></i> Create First Test</a>
            </div>
        @endforelse
    </section>

    @if($tests->hasPages())
        <div class="pt-2">{{ $tests->links() }}</div>
    @endif
</div>
@endsection

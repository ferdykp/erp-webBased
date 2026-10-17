@extends('admin.layout.app')

@section('title', 'Production Center')

@section('content')
    <div class="pb-10 mx-auto space-y-6 max-w-none" x-data="{
        selected: {{ (int) request('batch', 0) ?: 'null' }},
        panel: 'overview',
        view: 'all'
    }">
        {{-- HEADER --}}
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.16em] text-slate-500">
                    E-Beam Operations
                </p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight">
                    Production Center
                </h1>
                <p class="mt-2 text-sm text-slate-500">
                    Set up batches, enter machine settings and record irradiation hours.
                </p>
            </div>

            @if (in_array(auth('admin')->user()->role, ['superadmin', 'production']))
                <a href="{{ route('admin.production.parameter') }}"
                    class="rounded-xl border bg-white px-4 py-2.5 text-sm font-semibold text-slate-700">
                    Create Batch ↗
                </a>
            @endif
        </header>

        {{-- STATUS SUMMARY --}}
        <div class="grid gap-3 sm:grid-cols-4">
            @foreach ([
            'pending' => 'Planned',
            'waiting' => 'Ready',
            'processing' => 'In Progress',
            'done' => 'Completed',
        ] as $state => $name)
                <button type="button" @click="view = '{{ $state }}'"
                    :class="view === '{{ $state }}' ? 'ring-2 ring-blue-500' : ''"
                    class="p-4 text-left bg-white border rounded-2xl hover:border-blue-400">
                    <p class="text-xs font-semibold text-slate-500">
                        {{ $name }}
                    </p>
                    <p class="mt-2 text-3xl font-bold">
                        {{ number_format($counts[$state] ?? 0) }}
                    </p>
                </button>
            @endforeach
        </div>

        {{-- FILTER --}}
        <form method="GET" class="flex flex-col gap-3 sm:flex-row">
            <input name="search" value="{{ request('search') }}" placeholder="Search order number..."
                aria-label="Search booking"
                class="flex-1 min-w-0 px-4 py-3 text-sm bg-white border rounded-xl border-slate-200">

            <select name="machine" aria-label="Production unit" class="px-4 py-3 text-sm bg-white border rounded-xl">
                <option value="">All Machines</option>

                @foreach ($machines as $machine)
                    <option value="{{ $machine->id }}" @selected(request('machine') == $machine->id)>
                        {{ $machine->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-5 py-3 text-sm font-semibold text-white rounded-xl bg-slate-950">
                Filter
            </button>
        </form>

        {{-- RECEIVED ORDERS --}}
        @if ($receivedOrders->isNotEmpty())
            <section class="p-5 border border-blue-200 rounded-2xl bg-blue-50">
                <h2 class="text-base font-bold text-slate-900">
                    Received Orders — Create Batch
                </h2>

                <p class="mt-1 text-sm text-slate-600">
                    Goods received. You can create a batch without an approval step.
                </p>

                <div class="grid gap-3 mt-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($receivedOrders as $order)
                        <div class="p-4 bg-white border border-blue-100 rounded-xl">
                            <p class="font-bold text-slate-900">
                                {{ $order->booking_code }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ $order->customer?->company_name }}
                                · {{ number_format($order->products->sum('quantity')) }} units
                            </p>

                            <p
                                class="mt-2 text-xs font-semibold {{ $order->status === 'pending' ? 'text-amber-700' : 'text-emerald-700' }}">
                                {{ !$order->arrival_time ? 'Receiving data needs review' : 'Ready for batch setup' }}
                            </p>

                            <a href="{{ route('admin.workspace.show', [
                                'booking' => $order,
                                'stage' => 'preparation',
                            ]) }}"
                                class="inline-flex px-3 py-2 mt-3 text-xs font-bold text-white bg-blue-600 rounded-lg">
                                {{ !$order->arrival_time ? 'Review Receiving' : 'Create Batch' }} →
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- MAIN CONTENT --}}
        <div class="grid min-h-[490px] gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(340px,430px)]">

            {{-- BATCH LIST --}}
            <section class="min-w-0 p-4 bg-white border rounded-2xl sm:p-5">
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach ([
            'all' => 'All Batches',
            'pending' => 'Planned',
            'waiting' => 'Ready',
            'processing' => 'In Progress',
            'done' => 'Completed',
        ] as $key => $label)
                        <button type="button" @click="view = '{{ $key }}'"
                            :class="view === '{{ $key }}'
                                ?
                                'bg-slate-950 text-white' :
                                'bg-slate-100 text-slate-600'"
                            class="px-3 py-2 text-xs font-semibold rounded-lg">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div class="space-y-2">
                    @forelse ($batches as $batch)
                        <button type="button" x-cloak x-show="view === 'all' || view === '{{ $batch->status }}'"
                            @click="selected = {{ $batch->id }}; panel = 'overview'"
                            :class="selected === {{ $batch->id }} ?
                                'border-blue-500 bg-blue-50' :
                                'border-slate-200 bg-white'"
                            class="w-full p-4 text-left border rounded-xl hover:border-blue-400">
                            <div class="flex justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-950">
                                        {{ $batch->booking?->booking_code ?? 'Order not found' }}
                                        <span class="ml-2 font-normal text-slate-400">
                                            / Batch {{ $batch->batch_number }}
                                        </span>
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $batch->booking?->customer?->company_name ?? 'Customer not found' }}
                                        · {{ $batch->quantity }} {{ $batch->unit }}
                                        · {{ $batch->productionLine?->name ?? 'Machine not selected' }}
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 self-start rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-bold uppercase">
                                    {{ [
                                        'pending' => 'Planned',
                                        'waiting' => 'Ready',
                                        'processing' => 'In Progress',
                                        'done' => 'Completed',
                                    ][$batch->status] ?? $batch->status }}
                                </span>
                            </div>
                        </button>
                    @empty
                        <div class="p-10 text-sm text-center border border-dashed rounded-xl text-slate-500">
                            No matching batches.
                        </div>
                    @endforelse
                </div>

                <div class="mt-5">
                    {{ $batches->links() }}
                </div>
            </section>

            {{-- BATCH DETAIL --}}
            <aside class="min-w-0 p-5 bg-white border rounded-2xl">

                <div x-cloak x-show="selected === null"
                    class="flex items-center justify-center text-sm text-center min-h-64 text-slate-500">
                    Select a batch to view settings and next steps.
                </div>

                @foreach ($batches as $batch)
                    <div x-cloak x-show="selected === {{ $batch->id }}" class="space-y-4">
                        {{-- BATCH HEADER --}}
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-semibold text-slate-400">
                                    {{ $batch->booking?->booking_code }}
                                </p>

                                <h2 class="mt-1 text-xl font-bold">
                                    Batch {{ $batch->batch_number }}
                                </h2>
                            </div>

                            <a href="{{ route('admin.workspace.show', [
                                'booking' => $batch->booking_id,
                                'stage' => 'irradiation',
                            ]) }}"
                                class="text-xs font-semibold text-blue-700">
                                Open Order ↗
                            </a>
                        </div>

                        {{-- BATCH INFORMATION --}}
                        <div class="grid grid-cols-2 gap-2 p-4 text-xs rounded-xl bg-slate-50">
                            <span class="text-slate-500">Machine</span>
                            <strong class="text-right">
                                {{ $batch->productionLine?->name ?? 'Not selected' }}
                            </strong>

                            <span class="text-slate-500">Quantity</span>
                            <strong class="text-right">
                                {{ $batch->quantity }} {{ $batch->unit }}
                            </strong>

                            <span class="text-slate-500">Target Dose</span>
                            <strong class="text-right">
                                {{ $batch->target_dose ?? '—' }}
                            </strong>

                            <span class="text-slate-500">Speed</span>
                            <strong class="text-right">
                                {{ $batch->beam_speed ?? '—' }}
                            </strong>
                        </div>

                        @if (in_array(auth('admin')->user()->role, ['superadmin', 'production']))
                            {{-- MACHINE SETTINGS --}}
                            @if (in_array($batch->status, ['pending', 'waiting']))
                                <form method="POST"
                                    action="{{ route('admin.production.batches.parameter.update', $batch->id) }}"
                                    class="space-y-3">
                                    @csrf
                                    @method('PUT')

                                    <h3 class="text-sm font-semibold">
                                        Machine Settings
                                    </h3>

                                    <label class="block text-xs text-slate-600">
                                        Machine

                                        <select name="production_line_id" class="w-full p-2 mt-1 text-sm border rounded-lg">
                                            <option value="">Select machine</option>

                                            @foreach ($machines as $machine)
                                                <option value="{{ $machine->id }}" @selected($batch->production_line_id === $machine->id)>
                                                    {{ $machine->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <div class="grid grid-cols-2 gap-3">
                                        @foreach ([
            'target_dose' => 'Target Dose',
            'beam_speed' => 'Beam Speed',
            'freq' => 'Frequency',
            'scan_gear' => 'Scan Gear',
        ] as $field => $label)
                                            <label class="block text-xs text-slate-600">
                                                {{ $label }}

                                                <input type="number" step="any" min="0"
                                                    name="{{ $field }}" value="{{ $batch->{$field} }}"
                                                    class="w-full p-2 mt-1 text-sm border rounded-lg">
                                            </label>
                                        @endforeach
                                    </div>

                                    <label class="block text-xs text-slate-600">
                                        Loading Mode

                                        <select name="loading_mode" required
                                            class="w-full p-2 mt-1 text-sm border rounded-lg">
                                            <option value="">Select mode</option>
                                            <option value="single_side" @selected($batch->loading_mode === 'single_side')>
                                                Single Side
                                            </option>
                                            <option value="double_side" @selected($batch->loading_mode === 'double_side')>
                                                Double Side
                                            </option>
                                        </select>
                                    </label>

                                    <button type="submit"
                                        class="w-full rounded-xl border border-blue-600 px-4 py-2.5 text-sm font-semibold text-blue-700">
                                        Save Settings
                                    </button>
                                </form>
                            @endif

                            {{-- COMPLETED RUNS --}}
                            @if ($batch->status === 'done')
                                @php
                                    $completedRuns = \Illuminate\Support\Facades\DB::table('irradiation_sessions')
                                        ->where('booking_batch_id', $batch->id)
                                        ->whereNull('voided_at')
                                        ->orderBy('started_at')
                                        ->get();
                                @endphp

                                <section class="p-4 space-y-3 text-sm border rounded-xl bg-slate-50">
                                    <h3 class="font-semibold">
                                        Completed Runs
                                    </h3>

                                    <p>
                                        {{ $completedRuns->count() }} runs
                                        · {{ $completedRuns->sum('duration_minutes') }} total minutes
                                        · {{ config('app.timezone') }}
                                    </p>

                                    @foreach ($completedRuns as $run)
                                        <p class="text-xs">
                                            {{ $run->started_at }}
                                            →
                                            {{ $run->ended_at }}
                                            · {{ $run->duration_minutes }} minutes
                                        </p>
                                    @endforeach

                                    <a href="{{ route('admin.dosimeter.show', $batch->booking_id) }}"
                                        class="block px-4 py-3 font-semibold text-center text-white bg-blue-600 rounded-lg">
                                        Next: Record Dosimetry
                                    </a>

                                    <a href="{{ route('admin.workspace.show', [
                                        'booking' => $batch->booking_id,
                                        'stage' => 'documents',
                                    ]) }}"
                                        class="block font-semibold text-center text-blue-700">
                                        Open Order Documents
                                    </a>
                                </section>
                            @endif

                            {{-- READY FOR PRODUCTION --}}
                            @if ($batch->status === 'waiting')
                                <div class="p-4 space-y-3 border border-blue-200 rounded-xl bg-blue-50">
                                    <h3 class="text-sm font-bold text-slate-900">
                                        Ready for Production
                                    </h3>

                                    <p class="text-xs text-slate-600">
                                        Start the production workflow now. Enter the actual
                                        irradiation dates and times only after a run is complete.
                                        No timer starts automatically.
                                    </p>

                                    <form method="POST"
                                        action="{{ route('admin.production.batches.start', $batch->id) }}">
                                        @csrf
                                        @method('PUT')

                                        <button type="submit"
                                            class="w-full px-4 py-3 text-sm font-bold text-white bg-blue-600 rounded-xl">
                                            Start Production
                                        </button>
                                    </form>
                                </div>
                            @endif

                            {{-- IRRADIATION RUNS --}}
                            @if ($batch->status === 'processing')
                                @php
                                    $sessions = \Illuminate\Support\Facades\DB::table('irradiation_sessions')
                                        ->where('booking_batch_id', $batch->id)
                                        ->whereNull('voided_at')
                                        ->orderBy('started_at')
                                        ->get();
                                @endphp

                                <div class="p-4 space-y-3 border border-blue-200 rounded-xl bg-blue-50"
                                    x-data="{
                                        showRun: {{ $sessions->isEmpty() || $errors->has('started_at') || $errors->has('ended_at') ? 'true' : 'false' }}
                                    }">
                                    <h3 class="text-sm font-bold text-slate-900">
                                        Irradiation Runs
                                    </h3>

                                    <p class="text-xs text-slate-600">
                                        Times use {{ config('app.timezone') }}.
                                        When a run is finished, enter its actual start and end times.
                                        For multiple runs, add one record per run.
                                    </p>

                                    <p class="text-xs font-semibold text-slate-700">
                                        {{ $sessions->count() }}
                                        {{ \Illuminate\Support\Str::plural('run', $sessions->count()) }}
                                        · {{ number_format($sessions->sum('duration_minutes')) }}
                                        total minutes
                                    </p>

                                    @foreach ($sessions as $i => $session)
                                        <div
                                            class="px-3 py-2 text-xs bg-white border border-blue-100 rounded-lg text-slate-700">
                                            <div class="font-semibold">
                                                Run {{ $i + 1 }}
                                                · {{ $session->duration_minutes }} minutes
                                            </div>

                                            <div class="mt-1">
                                                {{ \Carbon\Carbon::parse($session->started_at)->format('d/m/Y H:i') }}
                                                –
                                                {{ \Carbon\Carbon::parse($session->ended_at)->format('d/m/Y H:i') }}
                                            </div>
                                        </div>

                                        <details class="text-xs">
                                            <summary class="cursor-pointer text-slate-600">
                                                Correct run #{{ $session->id }}
                                            </summary>

                                            <form method="POST"
                                                action="{{ route('admin.production.sessions.void', [$batch->id, $session->id]) }}"
                                                class="mt-2 space-y-2">
                                                @csrf

                                                <p>
                                                    Void this incorrect record, then enter the corrected run.
                                                    Original evidence remains in the audit history.
                                                </p>

                                                <label class="block">
                                                    Correction reason

                                                    <input name="reason" required minlength="8" maxlength="2000"
                                                        class="w-full p-2 mt-1 border rounded">
                                                </label>

                                                <label class="flex gap-2">
                                                    <input type="checkbox" name="confirmed" value="1" required>
                                                    I confirm this run was recorded incorrectly.
                                                </label>

                                                <button type="submit" class="px-3 py-2 border rounded-lg">
                                                    Void Incorrect Run
                                                </button>
                                            </form>
                                        </details>
                                    @endforeach

                                    @if ($sessions->isNotEmpty())
                                        <button type="button" @click="showRun = !showRun"
                                            class="w-full rounded-xl border border-blue-300 bg-white px-4 py-2.5 text-sm font-semibold text-blue-700"
                                            x-text="showRun ? 'Cancel Additional Run' : '+ Add Another Run'"></button>
                                    @endif

                                    <form x-cloak x-show="showRun" method="POST"
                                        action="{{ route('admin.production.sessions.store', $batch->id) }}"
                                        class="space-y-3">
                                        @csrf

                                        <h4 class="text-xs font-bold text-slate-900">
                                            {{ $sessions->isEmpty() ? 'Record Completed Run' : 'Record Additional Run' }}
                                        </h4>

                                        <label class="block text-xs font-semibold">
                                            Actual Start

                                            <input required type="datetime-local" name="started_at"
                                                value="{{ old('started_at') }}"
                                                class="w-full p-2 mt-1 text-sm border rounded-lg">
                                        </label>

                                        <label class="block text-xs font-semibold">
                                            Actual End

                                            <input required type="datetime-local" name="ended_at"
                                                value="{{ old('ended_at') }}"
                                                class="w-full p-2 mt-1 text-sm border rounded-lg">
                                        </label>

                                        <label class="block text-xs font-semibold">
                                            Notes (optional)

                                            <textarea name="notes" maxlength="2000" class="w-full p-2 mt-1 text-sm border rounded-lg">{{ old('notes') }}</textarea>
                                        </label>

                                        <button type="submit"
                                            class="w-full px-4 py-3 text-sm font-bold text-white bg-blue-600 rounded-lg">
                                            Save Run
                                        </button>
                                    </form>

                                    @if ($sessions->isEmpty())
                                        <p class="text-xs text-amber-800">
                                            Finish a real irradiation run before entering its times.
                                            Batch completion requires at least one run.
                                        </p>
                                    @endif
                                </div>
                            @endif

                            {{-- FINISH BATCH --}}
                            @if ($batch->status === 'processing' && $sessions->isNotEmpty())
                                <div class="p-3 text-xs border rounded-xl border-amber-200 bg-amber-50 text-amber-900">
                                    Finishing records the QA results.
                                    A failed inspection keeps the batch open for review.
                                </div>

                                <form method="POST" action="{{ route('admin.production.batches.finish', $batch->id) }}"
                                    class="space-y-3">
                                    @csrf
                                    @method('PUT')

                                    <h3 class="text-sm font-semibold">
                                        Finish Batch & Record Checks
                                    </h3>

                                    {{-- MEASURED DOSE --}}
                                    <label class="block text-xs">
                                        Measured Dose

                                        <input required name="actual_dose" type="number" min="0" step="any"
                                            class="w-full p-2 mt-1 text-sm border rounded-lg">
                                    </label>

                                    {{-- VISUAL CHECK --}}
                                    <label class="block text-xs">
                                        Visual Check

                                        <select required name="visual_check"
                                            class="w-full p-2 mt-1 text-sm border rounded-lg">
                                            <option value="" disabled selected>
                                                Select result
                                            </option>
                                            <option value="pass">Pass</option>
                                            <option value="fail">Fail</option>
                                        </select>
                                    </label>

                                    {{-- INDICATOR --}}
                                    <label class="block text-xs">
                                        Indicator

                                        <select required name="indicator_check"
                                            class="w-full p-2 mt-1 text-sm border rounded-lg">
                                            <option value="" disabled selected>
                                                Select result
                                            </option>
                                            <option value="changed">Changed</option>
                                            <option value="no_change">Unchanged</option>
                                        </select>
                                    </label>

                                    {{-- DAMAGE ASSESSMENT --}}
                                    <div x-data="{
                                        isDamaged: @js(old('is_damaged', ''))
                                    }" class="space-y-3">
                                        <label class="block text-xs font-medium text-slate-700">
                                            Damage

                                            <select name="is_damaged" x-model="isDamaged"
                                                @change="
                                                if (isDamaged !== 'yes') {
                                                    $refs.damagedQty.value = '';
                                                    $refs.damageDescription.value = '';
                                                }
                                            "
                                                required
                                                class="w-full p-2 mt-1 text-sm bg-white border rounded-lg border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                                <option value="" disabled>Select result</option>
                                                <option value="no">No</option>
                                                <option value="yes">Yes</option>
                                            </select>
                                        </label>

                                        {{-- SHOW ONLY WHEN DAMAGE = YES --}}
                                        <div x-cloak x-show="isDamaged === 'yes'" x-transition.opacity.duration.150ms
                                            class="space-y-3">
                                            <label class="block text-xs font-medium text-slate-700">
                                                Damaged Quantity

                                                <input x-ref="damagedQty" name="damaged_qty" type="number"
                                                    min="1" max="{{ $batch->quantity }}"
                                                    value="{{ old('damaged_qty') }}" :required="isDamaged === 'yes'"
                                                    :disabled="isDamaged !== 'yes'"
                                                    placeholder="Enter damaged quantity"
                                                    class="w-full p-2 mt-1 text-sm bg-white border rounded-lg border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                            </label>

                                            <label class="block text-xs font-medium text-slate-700">
                                                Damage Details

                                                <textarea x-ref="damageDescription" name="damage_description" rows="3" maxlength="500"
                                                    :required="isDamaged === 'yes'" :disabled="isDamaged !== 'yes'" placeholder="Describe the damage..."
                                                    class="w-full p-2 mt-1 text-sm bg-white border rounded-lg border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ old('damage_description') }}</textarea>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- QA NOTES --}}
                                    <label class="block text-xs">
                                        Check Notes (optional)

                                        <textarea name="qa_notes" class="w-full p-2 mt-1 text-sm border rounded-lg" rows="2"></textarea>
                                    </label>

                                    <button type="submit"
                                        class="w-full px-4 py-3 text-sm font-semibold text-white rounded-xl bg-slate-950">
                                        Save Results & Finish Batch
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                @endforeach
            </aside>
        </div>

        <p class="text-xs text-slate-500">
            Completed stages are read-only. Continue with the next available step.
        </p>
    </div>
@endsection

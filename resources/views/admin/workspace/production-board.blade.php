@extends('admin.layout.app')
@section('title','Production Center')
@section('content')
<div class="mx-auto max-w-none space-y-6 pb-10" x-data="{ selected: {{ (int) request('batch', 0) ?: 'null' }}, panel: 'overview', view: 'all' }">
<header class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[.16em] text-slate-500">E-Beam Operations</p><h1 class="mt-2 text-3xl font-bold tracking-tight">Production Center</h1><p class="mt-2 text-sm text-slate-500">Set up batches, enter machine settings and record irradiation hours.</p></div>@if(in_array(auth('admin')->user()->role,['superadmin','production']))<a class="rounded-xl border bg-white px-4 py-2.5 text-sm font-semibold text-slate-700" href="{{ route('admin.production.parameter') }}">Create Batch ↗</a>@endif</header>
<div class="grid gap-3 sm:grid-cols-4">@foreach(['pending'=>'Planned','waiting'=>'Ready','processing'=>'In Progress','done'=>'Completed'] as $state=>$name)<button type="button" @click="view='{{ $state }}'" class="rounded-2xl border bg-white p-4 text-left hover:border-blue-400" :class="view==='{{ $state }}'?'ring-2 ring-blue-500':''"><p class="text-xs font-semibold text-slate-500">{{ $name }}</p><p class="mt-2 text-3xl font-bold">{{ number_format($counts[$state] ?? 0) }}</p></button>@endforeach</div>
<form method="GET" class="flex flex-col gap-3 sm:flex-row"><input name="search" value="{{ request('search') }}" placeholder="Search order number..." aria-label="Search booking" class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"><select name="machine" aria-label="Production unit" class="rounded-xl border bg-white px-4 py-3 text-sm"><option value="">All Machines</option>@foreach($machines as $machine)<option value="{{ $machine->id }}" @selected(request('machine') == $machine->id)>{{ $machine->name }}</option>@endforeach</select><button class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white">Filter</button></form>
@if($receivedOrders->isNotEmpty())
<section class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
  <h2 class="text-base font-bold text-slate-900">Received Orders — Create Batch</h2>
  <p class="mt-1 text-sm text-slate-600">Goods received. You can create a batch without an approval step.</p>
  <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
   @foreach($receivedOrders as $order)
    <div class="rounded-xl border border-blue-100 bg-white p-4">
     <p class="font-bold text-slate-900">{{ $order->booking_code }}</p>
     <p class="mt-1 text-xs text-slate-500">{{ $order->customer?->company_name }} · {{ number_format($order->products->sum('quantity')) }} units</p>
     <p class="mt-2 text-xs font-semibold {{ $order->status === 'pending' ? 'text-amber-700' : 'text-emerald-700' }}">{{ !$order->arrival_time ? 'Receiving data needs review' : 'Ready for batch setup' }}</p>
     <a href="{{ route('admin.workspace.show', ['booking' => $order, 'stage' => 'preparation']) }}" class="mt-3 inline-flex rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white">{{ !$order->arrival_time ? 'Review Receiving' : 'Create Batch' }} →</a>
    </div>
   @endforeach
  </div>
</section>
@endif
<div class="grid min-h-[490px] gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(340px,430px)]">
<section class="min-w-0 rounded-2xl border bg-white p-4 sm:p-5"><div class="mb-4 flex flex-wrap gap-2">@foreach(['all'=>'All Batches','pending'=>'Planned','waiting'=>'Ready','processing'=>'In Progress','done'=>'Completed'] as $key=>$label)<button type="button" @click="view='{{ $key }}'" :class="view==='{{ $key }}'?'bg-slate-950 text-white':'bg-slate-100 text-slate-600'" class="rounded-lg px-3 py-2 text-xs font-semibold">{{ $label }}</button>@endforeach</div>
<div class="space-y-2">@forelse($batches as $batch)
<button type="button" x-cloak x-show="view==='all'||view==='{{ $batch->status }}'" @click="selected={{ $batch->id }};panel='overview'" :class="selected==={{ $batch->id }}?'border-blue-500 bg-blue-50':'border-slate-200 bg-white'" class="w-full rounded-xl border p-4 text-left hover:border-blue-400"><div class="flex justify-between gap-3"><div><p class="text-sm font-semibold text-slate-950">{{ $batch->booking?->booking_code ?? 'Order not found' }} <span class="ml-2 font-normal text-slate-400">/ Batch {{ $batch->batch_number }}</span></p><p class="mt-1 text-xs text-slate-500">{{ $batch->booking?->customer?->company_name ?? 'Customer not found' }} · {{ $batch->quantity }} {{ $batch->unit }} · {{ $batch->productionLine?->name ?? 'Machine not selected' }}</p></div><span class="shrink-0 self-start rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-bold uppercase">{{ ['pending'=>'Planned','waiting'=>'Ready','processing'=>'In Progress','done'=>'Completed'][$batch->status] ?? $batch->status }}</span></div></button>
@empty<div class="rounded-xl border border-dashed p-10 text-center text-sm text-slate-500">No matching batches.</div>@endforelse</div><div class="mt-5">{{ $batches->links() }}</div></section>
<aside class="min-w-0 rounded-2xl border bg-white p-5"><div x-cloak x-show="selected===null" class="flex min-h-64 items-center justify-center text-center text-sm text-slate-500">Select a batch to view settings and next steps.</div>
@foreach($batches as $batch)
<div x-cloak x-show="selected==={{ $batch->id }}" x-cloak class="space-y-4"><div class="flex items-start justify-between gap-2"><div><p class="text-xs font-semibold text-slate-400">{{ $batch->booking?->booking_code }}</p><h2 class="mt-1 text-xl font-bold">Batch {{ $batch->batch_number }}</h2></div><a href="{{ route('admin.workspace.show',['booking'=>$batch->booking_id,'stage'=>'irradiation']) }}" class="text-xs font-semibold text-blue-700">Open Order ↗</a></div>
<div class="grid grid-cols-2 gap-2 rounded-xl bg-slate-50 p-4 text-xs"><span class="text-slate-500">Machine</span><strong class="text-right">{{ $batch->productionLine?->name ?? 'Not selected' }}</strong><span class="text-slate-500">Quantity</span><strong class="text-right">{{ $batch->quantity }} {{ $batch->unit }}</strong><span class="text-slate-500">Target Dose</span><strong class="text-right">{{ $batch->target_dose ?? '—' }}</strong><span class="text-slate-500">Speed</span><strong class="text-right">{{ $batch->beam_speed ?? '—' }}</strong></div>
@if(in_array(auth('admin')->user()->role,['superadmin','production']))
@if(in_array($batch->status,['pending','waiting']))<form method="POST" action="{{ route('admin.production.batches.parameter.update',$batch->id) }}" class="space-y-3">@csrf @method('PUT')<h3 class="text-sm font-semibold">Machine Settings</h3><label class="block text-xs text-slate-600">Machine<select name="production_line_id" class="mt-1 w-full rounded-lg border p-2 text-sm"><option value="">Select machine</option>@foreach($machines as $machine)<option value="{{ $machine->id }}" @selected($batch->production_line_id === $machine->id)>{{ $machine->name }}</option>@endforeach</select></label><div class="grid grid-cols-2 gap-3">@foreach(['target_dose'=>'Target Dose','beam_speed'=>'Beam Speed','freq'=>'Frequency','scan_gear'=>'Scan Gear'] as $field=>$label)<label class="block text-xs text-slate-600">{{ $label }}<input type="number" step="any" min="0" name="{{ $field }}" value="{{ $batch->{$field} }}" class="mt-1 w-full rounded-lg border p-2 text-sm"></label>@endforeach</div><label class="block text-xs text-slate-600">Loading Mode<select name="loading_mode" required class="mt-1 w-full rounded-lg border p-2 text-sm"><option value="">Select mode</option><option value="single_side" @selected($batch->loading_mode === 'single_side')>Single Side</option><option value="double_side" @selected($batch->loading_mode === 'double_side')>Double Side</option></select></label><button class="w-full rounded-xl border border-blue-600 px-4 py-2.5 text-sm font-semibold text-blue-700">Save Settings</button></form>@endif
@if($batch->status === 'done')
@php $completedRuns = \Illuminate\Support\Facades\DB::table('irradiation_sessions')->where('booking_batch_id', $batch->id)->whereNull('voided_at')->orderBy('started_at')->get(); @endphp
<section class="rounded-xl border bg-slate-50 p-4 text-sm space-y-3"><h3 class="font-semibold">Completed Runs</h3><p>{{ $completedRuns->count() }} runs · {{ $completedRuns->sum('duration_minutes') }} total minutes · {{ config('app.timezone') }}</p>@foreach($completedRuns as $run)<p class="text-xs">{{ $run->started_at }} → {{ $run->ended_at }} · {{ $run->duration_minutes }} minutes</p>@endforeach
<a href="{{ route('admin.dosimeter.show', $batch->booking_id) }}" class="block rounded-lg bg-blue-600 px-4 py-3 text-center font-semibold text-white">Next: Record Dosimetry</a><a href="{{ route('admin.workspace.show', ['booking'=>$batch->booking_id, 'stage'=>'documents']) }}" class="block text-center font-semibold text-blue-700">Open Order Documents</a></section>
@endif
@if($batch->status === 'waiting')
<div class="rounded-xl border border-blue-200 bg-blue-50 p-4 space-y-3">
    <h3 class="text-sm font-bold text-slate-900">Ready for Production</h3>
    <p class="text-xs text-slate-600">Start the production workflow now. Enter the actual irradiation dates and times only after a run is complete. No timer starts automatically.</p>
    <form method="POST" action="{{ route('admin.production.batches.start', $batch->id) }}">
        @csrf @method('PUT')
        <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white">Start Production</button>
    </form>
</div>
@endif
@if($batch->status === 'processing')
@php $sessions = \Illuminate\Support\Facades\DB::table('irradiation_sessions')->where('booking_batch_id', $batch->id)->whereNull('voided_at')->orderBy('started_at')->get(); @endphp
<div class="rounded-xl border border-blue-200 bg-blue-50 p-4 space-y-3" x-data="{ showRun: {{ ($sessions->isEmpty() || $errors->has('started_at') || $errors->has('ended_at')) ? 'true' : 'false' }} }">
    <h3 class="text-sm font-bold text-slate-900">Irradiation Runs</h3>
    <p class="text-xs text-slate-600">Times use {{ config('app.timezone') }}. When a run is finished, enter its actual start and end times. For multiple runs, add one record per run.</p>
    <p class="text-xs font-semibold text-slate-700">{{ $sessions->count() }} {{ \Illuminate\Support\Str::plural('run', $sessions->count()) }} · {{ number_format($sessions->sum('duration_minutes')) }} total minutes</p>
    @foreach($sessions as $i => $session)
        <div class="rounded-lg border border-blue-100 bg-white px-3 py-2 text-xs text-slate-700">
            <div class="font-semibold">Run {{ $i + 1 }} · {{ $session->duration_minutes }} minutes</div>
            <div class="mt-1">{{ \Carbon\Carbon::parse($session->started_at)->format('d/m/Y H:i') }} – {{ \Carbon\Carbon::parse($session->ended_at)->format('d/m/Y H:i') }}</div>
        </div>
        <details class="text-xs"><summary class="cursor-pointer text-slate-600">Correct run #{{ $session->id }}</summary>
            <form method="POST" action="{{ route('admin.production.sessions.void', [$batch->id, $session->id]) }}" class="mt-2 space-y-2">@csrf
                <p>Void this incorrect record, then enter the corrected run. Original evidence remains in the audit history.</p>
                <label class="block">Correction reason<input name="reason" required minlength="8" maxlength="2000" class="mt-1 w-full rounded border p-2"></label>
                <label class="flex gap-2"><input type="checkbox" name="confirmed" value="1" required> I confirm this run was recorded incorrectly.</label>
                <button class="rounded-lg border px-3 py-2">Void Incorrect Run</button>
            </form>
        </details>
    @endforeach
    @if($sessions->isNotEmpty())
        <button type="button" @click="showRun = !showRun" class="w-full rounded-xl border border-blue-300 bg-white px-4 py-2.5 text-sm font-semibold text-blue-700" x-text="showRun ? 'Cancel Additional Run' : '+ Add Another Run'"></button>
    @endif
    <form x-cloak x-show="showRun" method="POST" action="{{ route('admin.production.sessions.store', $batch->id) }}" class="space-y-3">
        @csrf
        <h4 class="text-xs font-bold text-slate-900">{{ $sessions->isEmpty() ? 'Record Completed Run' : 'Record Additional Run' }}</h4>
        <label class="block text-xs font-semibold">Actual Start<input required type="datetime-local" name="started_at" value="{{ old('started_at') }}" class="mt-1 w-full rounded-lg border p-2 text-sm"></label>
        <label class="block text-xs font-semibold">Actual End<input required type="datetime-local" name="ended_at" value="{{ old('ended_at') }}" class="mt-1 w-full rounded-lg border p-2 text-sm"></label>
        <label class="block text-xs font-semibold">Notes (optional)<textarea name="notes" maxlength="2000" class="mt-1 w-full rounded-lg border p-2 text-sm">{{ old('notes') }}</textarea></label>
        <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-bold text-white">Save Run</button>
    </form>
    @if($sessions->isEmpty())
        <p class="text-xs text-amber-800">Finish a real irradiation run before entering its times. Batch completion requires at least one run.</p>
    @endif
</div>
@endif
@if($batch->status==='processing' && $sessions->isNotEmpty())<div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900">Finishing records the QA results in the existing workflow. A failed inspection will not mark the batch as complete.</div><form method="POST" action="{{ route('admin.production.batches.finish',$batch->id) }}" class="space-y-3">@csrf @method('PUT')<h3 class="text-sm font-semibold">Finish Batch & Record Checks</h3><label class="block text-xs">Measured Dose<input required name="actual_dose" type="number" min="0" step="any" class="mt-1 w-full rounded-lg border p-2 text-sm"></label><label class="block text-xs">Visual Check<select required name="visual_check" class="mt-1 w-full rounded-lg border p-2 text-sm"><option value="" disabled selected>Select result</option><option value="pass">Pass</option><option value="fail">Fail</option></select></label><label class="block text-xs">Indicator<select required name="indicator_check" class="mt-1 w-full rounded-lg border p-2 text-sm"><option value="" disabled selected>Select result</option><option value="changed">Changed</option><option value="no_change">Unchanged</option></select></label><label class="block text-xs">Damage<select required name="is_damaged" class="mt-1 w-full rounded-lg border p-2 text-sm"><option value="" disabled selected>Select result</option><option value="no">No</option><option value="yes">Yes</option></select></label><label class="block text-xs">Damaged Quantity (if any)<input name="damaged_qty" type="number" min="1" max="{{ $batch->quantity }}" class="mt-1 w-full rounded-lg border p-2 text-sm"></label><label class="block text-xs">Damage Details<input name="damage_description" maxlength="500" class="mt-1 w-full rounded-lg border p-2 text-sm"></label><label class="block text-xs">Check Notes (optional)<textarea name="qa_notes" class="mt-1 w-full rounded-lg border p-2 text-sm" rows="2"></textarea></label><button class="w-full rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white">Save Results & Finish Batch</button></form>@endif
@endif
</div>
@endforeach
</aside></div>
<p class="text-xs text-slate-500">Batch execution uses the existing server-side validation and authorization. Formal QA release and order dispatch are not automatically inferred from this workspace.</p>
</div>
@endsection

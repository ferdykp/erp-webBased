@extends('admin.layout.app')
@section('title', 'Order Workflow')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
<div class="flex flex-wrap items-center justify-between gap-3"><a href="{{ route('admin.workspace.orders') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-950">← Orders</a><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold capitalize text-blue-700">{{ str_replace('_', ' ', $booking->status) }}</span></div>
<div><p class="text-xs uppercase tracking-widest text-slate-500">Order Workflow</p><h1 class="mt-1 text-3xl font-bold tracking-tight">{{ $booking->booking_code }}</h1><p class="mt-2 text-sm text-slate-500">{{ $booking->customer?->company_name ?? 'Customer not specified' }}</p></div>
<div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-slate-700">
 <p class="font-semibold text-blue-900"><i class="fa-solid fa-route mr-2"></i>Order workflow</p>
 <p class="mt-1">Customer → Order → Receive goods → Production setup → Irradiation sessions → Dosimetry → Documents → Handover.</p>
 <a class="mt-3 inline-flex items-center rounded-lg bg-blue-700 px-4 py-2 font-semibold text-white" href="{{ route('admin.workspace.show',['booking'=>$booking,'stage'=>$nextStage]) }}">{{ \App\Services\WorkflowState::handedOver($booking) ? 'View Process Summary' : 'Continue Workflow' }} <i class="fa-solid fa-arrow-right ml-2"></i></a>
</div>
<div class="grid gap-4 sm:grid-cols-3"><div class="rounded-xl border bg-white p-5"><p class="text-xs text-slate-500">Products</p><p class="mt-2 text-2xl font-semibold">{{ $booking->products->count() }}</p></div><div class="rounded-xl border bg-white p-5"><p class="text-xs text-slate-500">Production Batches</p><p class="mt-2 text-2xl font-semibold">{{ $booking->batches->count() }}</p></div><div class="rounded-xl border bg-white p-5"><p class="text-xs text-slate-500">Completed Batches</p><p class="mt-2 text-2xl font-semibold">{{ $booking->batches->where('status', 'done')->count() }}</p></div></div>

<nav class="flex gap-2 overflow-x-auto border-b border-slate-200 pb-2" aria-label="Order stages">@foreach(['summary'=>'Overview','receiving'=>'Receiving','preparation'=>'Production Setup','irradiation'=>'Irradiation','quality'=>'Dosimetry','documents'=>'Documents','handover'=>'Handover'] as $key => $label)<a href="{{ route('admin.workspace.show',['booking'=>$booking,'stage'=>$key]) }}" class="shrink-0 rounded-lg px-4 py-2 text-sm font-semibold {{ $stage === $key ? 'bg-slate-950 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}" @if($stage === $key) aria-current="page" @endif>{{ $label }}</a>@endforeach</nav>
@if(($booking->status === 'completed' && !\App\Services\WorkflowState::handedOver($booking)) && in_array(auth('admin')->user()->role, ['superadmin','manager','cargo_admin']))
<a href="{{ route('admin.dispatch.show', $booking) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white"><i class="fa-solid fa-truck"></i> Dispatch Goods</a>
@endif
<section class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
@if($stage === 'summary')<h2 class="text-xl font-semibold">Overview order</h2><p class="mt-2 text-sm text-slate-500">Key information, receiving status and workflow progress.</p><dl class="mt-5 grid gap-4 sm:grid-cols-2"><div><dt class="text-xs text-slate-500">Order Status</dt><dd class="mt-1 font-semibold capitalize">{{ $booking->status }}</dd></div><div><dt class="text-xs text-slate-500">Company</dt><dd class="mt-1 font-semibold">{{ $booking->customer?->company_name ?? '—' }}</dd></div><div><dt class="text-xs text-slate-500">Received At</dt><dd class="mt-1 font-semibold">{{ $booking->arrival_time?->format('d M Y, H:i') ?? 'Not received' }}</dd></div><div><dt class="text-xs text-slate-500">Recorded pallet slots</dt><dd class="mt-1 font-semibold">{{ $booking->placementDetails->count() }}</dd></div></dl>
@elseif($stage === 'receiving')<h2 class="text-xl font-semibold">Receiving</h2><p class="mt-2 text-sm text-slate-500">Verify quantities, select receiving staff and assign warehouse locations. Shared slots are allowed.</p><p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm">Recorded pallet slots: <strong>{{ $booking->placementDetails->count() }}</strong></p>@if(in_array(auth('admin')->user()->role,['superadmin','cargo_admin']) && (\App\Services\WorkflowState::canReceive($booking)))<a class="mt-5 inline-block rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white" href="{{ route('admin.smart.receive',$booking) }}">Receive Goods & Select Storage Slot →</a>@endif
@elseif($stage === 'preparation')
<h2 class="text-xl font-semibold">Production Setup</h2>
<p class="mt-2 text-sm text-slate-500">Once goods are received, create batches and enter machine settings. No order approval needed.</p>
@if(in_array(auth('admin')->user()->role,['superadmin','production']) && \App\Services\WorkflowState::canPlan($booking))
<a href="{{ route('admin.smart.batch-plan',$booking) }}" class="mt-5 inline-flex items-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Process — Set Machine Parameters <i class="fa-solid fa-arrow-right ml-2"></i></a>
@endif
@elseif($stage === 'irradiation')<h2 class="text-xl font-semibold">Irradiation Runs</h2><div class="mt-5 space-y-3">@forelse($booking->batches as $batch)<div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-4"><div><p class="font-semibold">Batch {{ $batch->batch_number }}</p><p class="mt-1 text-xs text-slate-500">{{ $batch->quantity }} {{ $batch->unit }} · {{ $batch->productionLine?->name ?? 'Machine not selected' }}</p></div><span class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold capitalize">{{ $batch->status }}</span></div>@empty<p class="rounded-lg bg-slate-50 p-5 text-sm text-slate-500">No production batch yet. Create one in Production Setup.</p>@endforelse</div>@if(in_array(auth('admin')->user()->role,['superadmin','production']))<a href="{{ route('admin.workspace.production-board', ['search'=>$booking->booking_code]) }}" class="mt-5 inline-block text-sm font-semibold text-blue-700 hover:underline">Record Runs & Finish Batch →</a>@endif
@elseif($stage === 'quality')<h2 class="text-xl font-semibold">Dosimetry</h2><p class="mt-2 text-sm text-slate-500">Record and verify measured dosimeter results after production.</p><div class="mt-5 space-y-2">@foreach($booking->batches as $batch)<div class="flex justify-between rounded-lg bg-slate-50 p-4 text-sm"><span>Batch {{ $batch->batch_number }}</span><span class="font-semibold">{{ $batch->qa ? 'QA results recorded' : 'QA results not recorded' }}</span></div>@endforeach</div>@if(in_array(auth('admin')->user()->role,['superadmin','production']))<a href="{{ route('admin.dosimeter.show', ['booking_id'=>$booking->id]) }}" class="mt-5 inline-block text-sm font-semibold text-blue-700 hover:underline">Open Dosimetry Results →</a>@endif
@elseif($stage === 'documents')<h2 class="text-xl font-semibold">Order Documents</h2>@if($booking->status === 'completed')<a href="{{ route('admin.orders.report.pdf', $booking) }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white"><i class="fa-solid fa-file-pdf"></i> Download Irradiation Process Report (PDF)</a>@endif<p class="mt-2 text-sm text-slate-500">Generate existing operational Excel documents for this order. Document generation does not constitute formal QA approval or product release.</p>
@php($docRole=auth('admin')->user()->role)
<div class="mt-5 grid gap-3 sm:grid-cols-2">
@if(in_array($docRole,['superadmin','manager','production']))
@foreach(['nuc_delivery_form'=>'Processing & Delivery Form','nuc_processing_record'=>'Irradiation Processing Record','nuc_equipment_record'=>'Equipment Operation Record','nuc_daily_work'=>'Workshop Daily Work','nuc_daily_schedule'=>'Daily Processing Schedule'] as $format=>$name)
<a class="flex items-center justify-between rounded-xl border border-slate-200 p-4 text-sm font-semibold hover:border-blue-400" href="{{ route('admin.report.export-excel',['id'=>$booking->id,'type'=>$format]) }}"><span>{{ $name }}</span><i class="fa-solid fa-download text-slate-400"></i></a>
@endforeach
@endif
@if(in_array($docRole,['superadmin','manager','cargo_admin']))
@foreach(['jts_delivery_inbound'=>'Delivery Inbound','jts_unirradiated_card'=>'Unirradiated Material Card','jts_irradiated_card'=>'Irradiated Material Card','jts_delivery_outbound'=>'Delivery Outbound'] as $format=>$name)
<a class="flex items-center justify-between rounded-xl border border-slate-200 p-4 text-sm font-semibold hover:border-blue-400" href="{{ route('admin.report.export-excel',['id'=>$booking->id,'type'=>$format]) }}"><span>{{ $name }}</span><i class="fa-solid fa-download text-slate-400"></i></a>
@endforeach
@endif
</div><a href="{{ route('admin.report.index') }}" class="mt-5 inline-block text-sm font-semibold text-blue-700 hover:underline">Browse all order documents →</a>
@else
<h2 class="text-xl font-semibold">Dispatch Goods</h2>
<p class="mt-2 text-sm text-slate-600">Dispatch only after physically checking the goods and quantities. Stock belonging to other orders stays in its locations.</p>
<div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm"><p>Status: <strong>{{ $dispatch?->status === 'dispatched' ? 'Dispatched' : ($dispatch?->status === 'approved' ? 'Ready for physical verification' : 'Not dispatched') }}</strong></p><p class="mt-1">Recorded locations: {{ $booking->placementDetails->count() }}</p></div>
@if(in_array(auth('admin')->user()->role,['superadmin','manager','cargo_admin']))
<a href="{{ route('admin.dispatch.show',$booking) }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white"><i class="fa-solid fa-truck-ramp-box"></i> Open Dispatch Form</a>
@endif
@endif
</section>



</div>
@endsection

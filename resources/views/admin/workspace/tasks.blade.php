@extends('admin.layout.app')
@section('title', 'Action Items')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
  <div><p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Operations</p><h1 class="mt-1 text-3xl font-semibold tracking-tight">Action Items</h1><p class="mt-2 text-sm text-slate-500">Choose the next task available for your role.</p></div>
  <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">Tasks reflect the next operational stage available to your role. Open an order to continue.</div>
  <div class="space-y-3">@forelse($tasks as $task)
    <a href="{{ route('admin.workspace.show', ['booking'=>$task['booking'], 'stage'=>$task['stage']]) }}" class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-5 transition hover:border-slate-400">
      <div><span class="text-xs font-medium text-slate-500">{{ $task['label'] }} · {{ $task['booking']->booking_code }}</span><h2 class="mt-1 font-semibold text-slate-950">{{ $task['title'] }}</h2><p class="mt-1 text-sm text-slate-500">{{ $task['booking']->customer?->company_name ?? 'Company not found' }}</p></div><span class="shrink-0 text-sm font-semibold">Open Order →</span>
    </a>
  @empty<div class="rounded-xl border bg-white p-10 text-center text-sm text-slate-500">No action items right now.</div>@endforelse</div>
</div>
@endsection

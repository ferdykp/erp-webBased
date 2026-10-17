@extends('admin.layout.app')
@section('title', 'Operational Audit Trail')
@section('content')
<div class="mx-auto max-w-6xl space-y-6 p-2"><div><a href="{{ route('admin.workspace.tasks') }}" class="text-sm text-blue-600">← My Tasks</a><h1 class="mt-3 text-3xl font-bold">Operational Audit Trail</h1></div>
@if(session('success'))<div class="rounded-xl bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="rounded-xl bg-red-50 p-4 text-red-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="rounded-xl border bg-white p-5">
    <p class="mb-4 text-sm text-slate-600">Recorded actions include the actor, reason and available data snapshots. Actions before audit logging was introduced are not retroactively included.</p>
    @forelse($events as $e)
        @php($details = json_decode($e->details ?? '{}', true))
        <article class="min-w-0 border-b border-slate-200 py-4 text-sm last:border-0">
            <div class="flex flex-wrap items-center justify-between gap-2"><strong class="break-all text-slate-950">{{ $e->action }}</strong><time class="text-xs text-slate-500">{{ $e->created_at }}</time></div>
            <p class="mt-1 break-words text-slate-600">{{ $e->subject_type }} #{{ $e->subject_id }} · Admin #{{ $e->admin_id }}</p>
            @if(!empty($details['route']))<p class="mt-1 break-all text-xs text-slate-500">{{ $details['method'] ?? '' }} {{ $details['route'] }}</p>@endif
            @if(!empty($details['reason']))<p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-amber-950"><span class="font-semibold">Reason:</span> {{ $details['reason'] }}</p>@endif
            <details class="mt-3 rounded-lg border border-slate-200">
                <summary class="cursor-pointer px-3 py-2 font-medium text-blue-700">View recorded data and snapshots</summary>
                <pre class="max-h-96 overflow-auto whitespace-pre-wrap break-all border-t bg-slate-50 p-3 text-xs leading-relaxed text-slate-700">{{ $details === null ? $e->details : json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        </article>
    @empty<p>No events recorded.</p>@endforelse
    <div class="mt-5">{{ $events->links() }}</div>
</div>
</div>
@endsection

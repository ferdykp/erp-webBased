@extends('admin.layout.app')
@section('title','Trash')
@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <header><p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Data retention</p><h1 class="mt-2 text-3xl font-bold text-slate-950">Trash</h1><p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">Deleted records remain recoverable with their original workflow stage, relationships and evidence. Restoring a completed order does not reopen receiving or production. Permanent deletion is disabled.</p></header>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    @forelse($entries as $entry)
        <article class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-5 last:border-0">
            <div class="min-w-0"><p class="break-words font-semibold text-slate-950">{{ $entry->label }}</p><p class="mt-1 text-sm text-slate-500">{{ \Illuminate\Support\Str::headline(class_basename($entry->model_type)) }} #{{ $entry->record_id }} · {{ $entry->created_at }} · {{ $entry->deleted_by ? 'Admin #'.$entry->deleted_by : 'Customer / system' }}</p></div>
            <form method="POST" action="{{ route('admin.trash.restore',$entry->id) }}" data-confirm-action="Restore this record to its original workflow stage?" data-record-name="{{ $entry->label }}">@csrf<button class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-800">Restore Record</button></form>
        </article>
    @empty<div class="p-12 text-center"><h2 class="font-semibold text-slate-900">Trash is empty</h2><p class="mt-2 text-sm text-slate-500">Records moved to Trash will appear here.</p></div>@endforelse
    </div>
    {{ $entries->links() }}
</div>
@endsection

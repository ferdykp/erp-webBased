@extends('admin.layout.app')
@section('title', 'Document Drafts')
@section('content')
<div class="mx-auto max-w-6xl space-y-6 p-2"><div><a href="{{ route('admin.workspace.tasks') }}" class="text-sm text-blue-600">← My Tasks</a><h1 class="mt-3 text-3xl font-bold">Document Drafts</h1></div>
@if(session('success'))<div class="rounded-xl bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="rounded-xl bg-red-50 p-4 text-red-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="rounded-xl border bg-white p-5"><p class="font-semibold">{{ $booking->booking_code }}</p><p class="mt-2 text-sm text-gray-500">Create immutable draft snapshots from recorded order data. These snapshots are not certificates or issued reports.</p>@if(!\App\Services\WorkflowState::handedOver($booking))<form method="POST" action="{{ route('admin.enhancements.documents.generate',$booking) }}" class="mt-4 flex flex-wrap gap-3">@csrf<select name="document_type" class="rounded-lg border p-3"><option value="receiving_summary">Receiving summary</option><option value="production_summary">Production summary</option><option value="quality_summary">Quality summary</option></select><button class="rounded-lg bg-gray-900 px-5 py-3 text-white">Generate draft snapshot</button></form>@endif</div>
<div class="rounded-xl border bg-white p-5"><h2 class="font-bold">Draft history</h2>@forelse($drafts as $d)<details class="my-3 rounded-lg border p-3"><summary class="cursor-pointer">{{ $d->document_type }} · v{{ $d->version }} · {{ $d->created_at }} · {{ $d->status }}</summary><pre class="mt-3 overflow-x-auto text-xs">{{ json_encode(json_decode($d->snapshot, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details>@empty<p class="mt-3 text-sm text-gray-500">No drafts.</p>@endforelse</div>
</div>
@endsection

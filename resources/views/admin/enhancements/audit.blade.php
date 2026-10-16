@extends('admin.layout.app')
@section('title', 'Operational Audit Trail')
@section('content')
<div class="mx-auto max-w-6xl space-y-6 p-2"><div><a href="{{ route('admin.workspace.tasks') }}" class="text-sm text-blue-600">← My Tasks</a><h1 class="mt-3 text-3xl font-bold">Operational Audit Trail</h1></div>
@if(session('success'))<div class="rounded-xl bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="rounded-xl bg-red-50 p-4 text-red-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="rounded-xl border bg-white p-5"><p class="text-sm text-gray-500">Audit events for newly introduced assisted operations. Existing legacy actions are not retroactively included.</p>@forelse($events as $e)<div class="border-b py-3 text-sm"><strong>{{ $e->action }}</strong> · {{ $e->subject_type }} #{{ $e->subject_id }} · Admin #{{ $e->admin_id }}<p class="mt-1 text-gray-500">{{ $e->created_at }} · {{ $e->details }}</p></div>@empty<p>No events recorded.</p>@endforelse<div class="mt-5">{{ $events->links() }}</div></div>
</div>
@endsection

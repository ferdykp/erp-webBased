@extends('admin.layout.app')
@section('title', 'Dispatch Goods')
@section('content')
    <div class="max-w-5xl pb-12 mx-auto space-y-6">
        <a href="{{ route('admin.workspace.show', ['booking' => $booking, 'stage' => 'documents']) }}"
            class="text-sm font-medium text-blue-700">← Back to Order Workflow</a>
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Dispatch Goods</h1>
            <p class="mt-2 text-sm text-slate-500">Order {{ $booking->booking_code }} ·
                {{ $booking->customer?->company_name }}</p>
        </div>
        @if (session('success'))
            <div class="p-4 text-sm rounded-xl bg-emerald-50 text-emerald-800">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="p-4 text-sm text-red-700 border border-red-200 rounded-xl bg-red-50"><strong>Action could not be
                    completed.</strong>
                <ul class="pl-5 mt-2 list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <section class="p-6 bg-white border rounded-2xl border-slate-200">
            <h2 class="text-lg font-semibold">Dispatch Readiness</h2>
            <dl class="grid gap-4 mt-4 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-slate-500">Production Status</dt>
                    <dd class="mt-1 font-semibold capitalize">{{ $booking->status }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Recorded Quality Note</dt>
                    <dd class="mt-1 font-semibold capitalize">
                        {{ in_array($review?->decision, ['hold', 'needs_correction'], true) ? str_replace('_', ' ', $review->decision) : 'None recorded' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Dispatch Status</dt>
                    <dd class="mt-1 font-semibold">{{ $dispatch?->status === 'dispatched' ? 'Dispatched' : 'Awaiting physical verification' }}</dd>
                </div>
            </dl>
            <div class="p-4 mt-5 text-sm rounded-lg bg-slate-50 text-slate-600">Stored quantities:
                <strong>{{ $contents->groupBy(fn($row) => $row->unit ?? 'unverified unit')->map(fn($rows, $unit) => number_format($rows->sum('quantity')).' '.$unit)->implode(', ') }}</strong> · Locations:
                <strong>{{ $locations->count() }}</strong>. Completed production does not automatically dispatch goods.
            </div>
            <div class="grid gap-2 mt-4 sm:grid-cols-2">
                @forelse($locations as $location)
                    <div class="px-4 py-3 text-sm border rounded-lg border-slate-200">Line {{ $location->line }} / Slot
                        {{ $location->slot_section }} ·
                        {{ $contents->where('pallet_id', $location->id)->groupBy(fn($row) => $row->unit ?? 'unverified unit')->map(fn($rows, $unit) => $rows->sum('quantity').' '.$unit)->implode(', ') }}</div>
                @empty<p class="text-sm text-slate-500">No active stored goods. Review the dispatch history or inventory.</p>
                @endforelse
            </div>
        </section>

        @if (count($verificationWarnings))
            <section role="status" class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                <h2 class="font-semibold">Verification Differences</h2>
                <p class="mt-2">These differences are informational. Dispatch can continue without any approval.</p>
                <ul class="mt-3 list-disc pl-5 space-y-1">@foreach ($verificationWarnings as $warning)<li>{{ $warning }}</li>@endforeach</ul>
            </section>
        @endif

        @if ($dispatch?->status === 'dispatched')
            <section class="p-6 border rounded-2xl border-emerald-200 bg-emerald-50">

                <h2 class="text-lg font-semibold text-emerald-900">
                    Goods Dispatched
                </h2>

                <p class="mt-2 text-sm text-emerald-800">
                    Reference: {{ $dispatch->reference }}
                </p>

                <p class="mt-1 text-sm text-emerald-800">
                    Dispatched: {{ $dispatch->dispatched_at }}
                </p>

                <p class="mt-1 text-sm text-emerald-800">
                    Quantity:
                    {{ collect(json_decode($dispatch->quantity_by_unit ?? '{}', true))->map(fn($quantity, $unit) => number_format($quantity).' '.$unit)->implode(', ') ?: number_format($dispatch->dispatched_quantity).' (legacy quantity)' }}
                </p>

                <p class="mt-3 text-sm text-emerald-700">
                    Warehouse stock has been updated.
                </p>

            </section>
        @elseif($readinessIssue)
            <div role="status" class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900"><strong>Complete the next step before dispatch.</strong><p class="mt-2">{{ $readinessIssue }}</p><a href="{{ route('admin.workspace.show', $booking) }}" class="mt-3 inline-block font-semibold underline">Open Order Workflow</a></div>
        @elseif(in_array(auth('admin')->user()->role, ['superadmin', 'cargo_admin']))
            <form method="POST" action="{{ route('admin.dispatch.confirm', $booking) }}"
                class="p-6 space-y-5 bg-white border rounded-2xl border-slate-200" x-data="{ showConfirm: false, submitting: false }" @keydown.escape.window="showConfirm = false"
                @submit.prevent="showConfirm = true">

                @csrf

                <div>
                    <h2 class="text-lg font-semibold text-slate-900">
                        Dispatch Goods
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Verify the goods before confirming dispatch.
                    </p>
                </div>

                <label class="block text-sm font-medium text-slate-700">
                    Dispatch Reference

                    <input type="text" name="reference" value="{{ old('reference') }}" maxlength="150" required
                        placeholder="Delivery note number" class="w-full p-3 mt-2 border border-slate-200 rounded-xl">
                </label>

                <label class="block text-sm font-medium text-slate-700">
                    Notes (Optional)

                    <textarea name="dispatch_notes" rows="3" maxlength="2000" placeholder="Additional details"
                        class="w-full p-3 mt-2 border border-slate-200 rounded-xl">{{ old('dispatch_notes') }}</textarea>
                </label>

                <div class="p-4 space-y-3 rounded-xl bg-slate-50">

                    <label class="flex items-start gap-3 text-sm">
                        <input type="checkbox" name="quantity_confirmed" value="1" required class="mt-1">

                        <span>
                            I verified the goods and quantity.
                        </span>
                    </label>

                    <label class="flex items-start gap-3 text-sm">
                        <input type="checkbox" name="physical_handover" value="1" required class="mt-1">

                        <span>
                            The goods have been physically handed over.
                        </span>
                    </label>

                </div>

                <button type="submit"
                    class="w-full px-5 py-3 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700">
                    Confirm Dispatch
                </button>

                <!-- Confirmation Modal -->
                <div @click.self="showConfirm = false" @keydown.escape.stop="showConfirm = false" data-ui-modal role="dialog" aria-modal="true" tabindex="-1" x-cloak x-show="showConfirm" x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50">

                    <div class="w-full max-w-md p-6 bg-white shadow-xl rounded-2xl">

                        <div class="flex items-center justify-center w-12 h-12 mb-4 rounded-full bg-amber-50">
                            <i class="text-xl text-amber-600 fa-solid fa-triangle-exclamation"></i>
                        </div>

                        <h3 class="text-xl font-semibold text-slate-900">
                            Confirm Dispatch?
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            Goods from this order will be removed
                            from warehouse inventory.
                            Other orders will not be affected.
                        </p>

                        @if(count($verificationWarnings))<p class="mt-3 text-sm text-amber-800">{{ count($verificationWarnings) }} verification difference(s) will be recorded with this dispatch. No approval is required.</p>@endif
                        <div class="flex gap-3 mt-6">

                            <button type="button" @click="showConfirm = false"
                                class="flex-1 px-4 py-3 text-sm font-semibold border rounded-xl border-slate-200">
                                Cancel
                            </button>

                            <button type="button" @click="if (!submitting && $el.closest('form').reportValidity()) { submitting = true; $el.closest('form').submit(); }" :disabled="submitting"
                                class="flex-1 px-4 py-3 text-sm font-semibold text-white bg-blue-600 rounded-xl">
                                Confirm
                            </button>

                        </div>
                    </div>
                </div>

            </form>
        @else
            <div class="p-5 text-sm border text-slate-600 rounded-xl border-slate-200 bg-slate-50">
                You do not have permission to dispatch goods.
            </div>
        @endif

    </div>
@endsection

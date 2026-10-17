@extends('admin.layout.app')
@section('title', 'Production Parameter Templates')
@section('content')
    <div class="max-w-6xl p-2 mx-auto space-y-6">
        <div><a href="{{ route('admin.workspace.tasks') }}" class="text-sm text-blue-600">← My Tasks</a>
            <h1 class="mt-3 text-3xl font-bold">Production Parameter Templates</h1>
        </div>
        @if (session('success'))
            <div class="p-4 text-green-800 rounded-xl bg-green-50">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="p-4 text-red-700 rounded-xl bg-red-50">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <div class="p-5 bg-white border rounded-xl">
            <h2 class="font-semibold">Versioned operational presets</h2>
            <p class="text-sm text-gray-500">Templates require Manager approval. Operators must confirm suitability for each
                product and batch; this page does not change machine settings.</p>
        </div>
        @if (in_array(auth('admin')->user()->role, ['superadmin', 'production']))
            <form class="grid gap-3 p-5 bg-white border rounded-xl sm:grid-cols-2" method="POST"
                action="{{ route('admin.enhancements.templates.store') }}">@csrf<input name="name" required
                    placeholder="Template name" class="p-3 border rounded-lg"><select name="production_line_id" required
                    class="p-3 border rounded-lg">
                    @foreach ($machines as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
                @foreach (['target_dose' => 'Target dose (kGy)', 'beam_speed' => 'Beam speed', 'freq' => 'Frequency', 'scan_gear' => 'Scan gear'] as $key => $label)
                    <input name="{{ $key }}" type="number" step="any"
                        {{ in_array($key, ['target_dose', 'beam_speed']) ? 'required' : '' }}
                        placeholder="{{ $label }}" class="p-3 border rounded-lg">
                @endforeach
                <label class="text-sm">Loading Mode<select name="loading_mode" required class="mt-1 w-full rounded-lg border p-3"><option value="">Select mode</option><option value="single_side">Single Side</option><option value="double_side">Double Side</option></select></label>
                <button class="p-3 text-white bg-gray-900 rounded-lg">Create draft template</button>
            </form>
        @endif
        <div class="space-y-3">
            @foreach ($templates as $t)
                <div class="p-5 bg-white border rounded-xl">
                    <p class="font-semibold">{{ $t->name }} <span
                            class="text-sm text-gray-500">({{ $t->approval_status }})</span></p>
                    <p class="mt-2 text-sm">Dose: {{ $t->target_dose }} · Beam speed: {{ $t->beam_speed }} · Loading:
                        {{ $t->loading_mode }}</p>
                    @if (($t->approval_status === 'approved') && in_array(auth('admin')->user()->role, ['superadmin', 'production']))
                        <div class="p-3 mt-3 rounded-lg bg-slate-50">
                            <p class="mb-2 text-xs font-semibold">Prepare a batch using this approved template</p>
                            <form method="POST" id="template-apply-{{ $t->id }}"
                                class="flex flex-wrap items-center gap-2"
                                onsubmit="if(!this.batch_id.value){event.preventDefault();return;} this.action='{{ url('/admin/batches') }}/'+this.batch_id.value+'/apply-template';">
                                @csrf<input type="hidden" name="template_id" value="{{ $t->id }}"><select
                                    name="batch_id" required class="p-2 text-sm border rounded-lg">
                                    <option value="">Select unstarted batch</option>
                                    @foreach ($editableBatches as $b)
                                        <option value="{{ $b->id }}">{{ $b->booking?->booking_code }} · Batch
                                            {{ $b->batch_number }}</option>
                                    @endforeach
                                </select>
                                <label class="text-xs"><input type="checkbox" name="confirmed" value="1" required> I
                                    have verified product requirements</label><button
                                    class="px-3 py-2 text-xs font-semibold text-white rounded-lg bg-slate-900">Apply to
                                    batch</button>
                            </form>
                        </div>
                    @endif
                    @if (($t->approval_status === 'draft') && in_array(auth('admin')->user()->role, ['superadmin', 'manager']))
                        <form method="POST" action="{{ route('admin.enhancements.templates.approve', $t->id) }}"
                            class="flex flex-wrap gap-2 mt-3">@csrf<select name="decision" required
                                class="p-2 border rounded-lg">
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select><input name="approval_notes" class="p-2 border rounded-lg"
                                placeholder="Reason / validation reference"><button
                                class="px-4 py-2 text-white bg-gray-900 rounded-lg">Save decision</button></form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endsection

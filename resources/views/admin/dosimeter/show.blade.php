@extends('admin.layout.app')

@section('title', 'Input Absorbance - ' . $booking->booking_code)

@section('content')

    <div class="w-full mx-auto space-y-6 max-w-none">

        {{-- Breadcrumb & Back Button --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.dosimeter.index') }}"
                class="flex items-center text-sm font-semibold text-gray-500 transition-colors hover:text-blue-600">
                <i class="mr-2 fa-solid fa-arrow-left"></i> Back to List
            </a>
            <span class="px-3 py-1 text-xs font-bold tracking-wider text-blue-600 uppercase rounded-full bg-blue-50">
                Dosimeter Configuration
            </span>
        </div>

        <div class="flex flex-wrap gap-3"><a
                href="{{ route('admin.workspace.show', ['booking' => $booking, 'stage' => 'quality']) }}"
                class="px-4 py-3 text-sm font-semibold bg-white border rounded-xl">Open Order Workflow</a><a
                href="{{ route('admin.workspace.show', ['booking' => $booking, 'stage' => 'documents']) }}"
                class="px-4 py-3 text-sm font-semibold text-white bg-blue-600 rounded-xl">Next: Order Documents</a></div>
        @if ($evidenceLocked)
            <p class="p-4 text-sm rounded-xl bg-slate-100">Measurements are locked until production is complete, and after
                dispatch. Existing evidence remains available for viewing.</p>
        @endif
        <p id="dosimetry-error" role="alert"
            class="hidden p-4 text-sm text-red-800 border border-red-200 rounded-xl bg-red-50" tabindex="-1"></p>
        @if ($records->count() > 1)
            <nav aria-label="Dosimetry sets" class="flex flex-wrap gap-2">
                @foreach ($records as $set)
                    <a href="{{ route('admin.dosimeter.show', ['booking_id' => $booking->id, 'record' => $set->id]) }}"
                        class="rounded-xl border px-4 py-2 text-sm {{ $record?->id === $set->id ? 'bg-blue-600 text-white' : 'bg-white' }}">Dosimetry
                        Set #{{ $set->id }} · {{ $set->tablet_quantity }} rows</a>
                @endforeach
            </nav>
        @endif
        {{-- Booking Info Header Card --}}
        <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div>
                    <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-1">Booking Code</p>
                    <h2 class="text-xl font-bold text-gray-800">{{ $booking->booking_code }}</h2>
                </div>
                <div>
                    <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-1">Customer</p>
                    <p class="text-sm font-bold text-gray-700">
                        {{ $booking->customer->company_name ?? $booking->customer->name }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-1">Product Name</p>
                    <p class="text-sm font-bold text-gray-700">{{ $booking->products->first()?->product_name ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Main Interaction Section --}}
        <div class="bg-white border border-gray-100 shadow-md rounded-[2rem] overflow-hidden">

            {{-- STEP 1: Tablet Quantity Configuration --}}
            @php
                $hasData =
                    $record &&
                    ($record->details->whereNotNull('absorbance')->count() > 0 ||
                        $record->details->whereNotNull('dosimeter_number')->count() > 0);

                $firstDetailImage = $record?->details->where('tablet_number', 1)->first()?->image;
            @endphp

            <div id="quantity-section" class="p-8 border-b border-gray-50 bg-gray-50/30 {{ $hasData ? 'hidden' : '' }}">
                <form id="form-tablet-quantity">
                    @csrf
                    <input type="hidden" id="booking_id" value="{{ $booking->id }}">
                    <div class="max-w-xl">
                        <label for="tablet_quantity" class="block mb-2 text-sm font-bold text-gray-700">
                            Step 1: Set Dosage Tablet Quantity
                        </label>
                        <div class="flex gap-3">
                            <input type="number" id="tablet_quantity" min="1" max="50"
                                value="{{ $record ? $record->tablet_quantity : '' }}" placeholder="Input number (e.g. 9)"
                                class="flex-1 px-4 py-3 text-sm font-semibold transition-all bg-white border border-gray-200 outline-none rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
                                required>
                            <button type="submit" @disabled($evidenceLocked)
                                class="px-6 py-3 text-sm font-bold text-white transition-all bg-gray-900 shadow-lg hover:bg-black rounded-xl active:scale-95">
                                Generate
                            </button>
                        </div>
                        <p class="mt-2 text-[11px] text-gray-400 font-medium">
                            <i class="mr-1 fa-solid fa-circle-info"></i> Changing quantity preserves existing measurements.
                            Rows containing recorded data cannot be removed.
                        </p>
                    </div>
                </form>
            </div>

            {{-- STEP 2: Absorbance Input Section --}}
            <div id="absorbance-section" class="p-8 {{ $record ? '' : 'hidden' }}">
                <div class="flex flex-col justify-between gap-4 mb-8 md:flex-row md:items-center">
                    <div>
                        <h3 class="text-lg font-black tracking-tight text-gray-800">Step 2: Dosimeter Logs & Absorbance</h3>
                        <p class="text-xs font-medium text-gray-400 mt-0.5">Precisely recorded spectrometer data, dosimeter
                            numbers, and automatic calibrated polynomial calculations.</p>

                        <div
                            class="mt-2 text-[11px] bg-amber-50 border border-amber-100 text-amber-800 px-3 py-1.5 rounded-lg font-mono inline-block">
                            <span class="font-bold">Formula:</span> y = 13.099x³ + 8.7891x² + 57.786x - 2.423 <span
                                class="mx-1 text-gray-400">|</span> (x = Absorbance, y = Absorbed Dose)
                        </div>
                    </div>

                    <button type="button" id="btn-edit-mode"
                        class="self-start md:self-center px-4 py-2 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 transition-all rounded-xl {{ $hasData && !$evidenceLocked ? '' : 'hidden' }}">
                        <i class="mr-1 fa-solid fa-pen-to-square"></i> Edit Values
                    </button>
                </div>

                {{-- TAMPILAN GAMBAR LOG --}}
                <div
                    class="view-wrapper mb-6 p-5 bg-gray-50 border border-gray-100 rounded-2xl {{ $hasData && $firstDetailImage ? '' : 'hidden' }}">
                    <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2">Attached Log Image
                    </p>
                    @if ($firstDetailImage)
                        <a href="{{ asset('storage/' . $firstDetailImage) }}" target="_blank" class="inline-block">
                            <img src="{{ asset('storage/' . $firstDetailImage) }}"
                                class="transition-all border border-gray-200 shadow-sm max-h-48 rounded-xl hover:opacity-90">
                        </a>
                    @endif
                </div>

                <form id="form-absorbance-values" enctype="multipart/form-data">
                    @csrf

                    {{-- Grid Input Tablet --}}
                    <div id="dynamic-inputs-container"
                        class="mb-8 grid grid-cols-1 gap-4 min-[420px]:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 2xl:gap-5">
                        @if ($record)
                            @foreach ($record->details as $detail)
                                <div
                                    class="p-5 transition-all duration-300 bg-white border border-gray-100 shadow-sm group rounded-2xl">
                                    <div class="flex items-center justify-between gap-3 mb-4">
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                            Tablet #{{ $detail->tablet_number }}
                                        </span>

                                        <div class="flex items-center gap-2">
                                            @unless ($evidenceLocked)
                                                @if ($record->details->count() > 1)
                                                    <button type="button"
                                                        onclick="openDeleteDosimeterModal(
                        {{ $detail->id }},
                        {{ $detail->tablet_number }},
                        @js($detail->dosimeter_number)
                    )"
                                                        class="inline-flex items-center justify-center w-8 h-8 text-red-500 transition-colors rounded-lg hover:bg-red-50"
                                                        title="Move dosimeter row to Trash"
                                                        aria-label="Delete Tablet #{{ $detail->tablet_number }}">
                                                        <i class="text-xs fa-solid fa-trash-can"></i>
                                                    </button>
                                                @endif
                                            @endunless

                                            <i class="text-sm text-blue-100 fa-solid fa-flask"></i>
                                        </div>
                                    </div>


                                    <div class="input-wrapper space-y-3 {{ $hasData ? 'hidden' : '' }}">
                                        <label class="block text-xs font-semibold text-gray-500">Measured Batch
                                            <select name="batch_id[{{ $detail->tablet_number }}]"
                                                class="w-full px-3 py-2 mt-1 text-sm border border-gray-200 rounded-xl">
                                                <option value="">
                                                    {{ $booking->products->count() > 1 ? 'Unassigned (comparison warning)' : 'Order product (single-product order)' }}
                                                </option>
                                                @foreach ($booking->batches->where('status', 'done') as $batch)
                                                    <option value="{{ $batch->id }}" @selected((int) $detail->booking_batch_id === $batch->id)>Batch
                                                        {{ $batch->batch_number }} ·
                                                        {{ $booking->products->firstWhere('id', $batch->booking_product_id)?->product_name ?? 'Product needs verification' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <div>
                                            <label
                                                class="block mb-1 text-[10px] font-bold text-gray-400 uppercase">Dosimeter
                                                No.</label>
                                            <input type="text" name="dosimeter_number[{{ $detail->tablet_number }}]"
                                                value="{{ $detail->dosimeter_number }}" placeholder="e.g. D-01" required
                                                class="w-full px-3 py-2 text-sm font-bold transition-all border border-gray-200 outline-none rounded-xl bg-gray-50/30 focus:bg-white focus:ring-4 focus:ring-blue-50">
                                        </div>

                                        <div>
                                            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase">
                                                Position
                                            </label>

                                            <input type="text" name="position[{{ $detail->tablet_number }}]"
                                                value="{{ $detail->position }}"
                                                placeholder="e.g. Top Left, Center, Bottom Right" maxlength="150"
                                                class="w-full px-3 py-2 text-sm font-semibold transition-all border border-gray-200 outline-none rounded-xl bg-gray-50/30 focus:bg-white focus:ring-4 focus:ring-blue-50">
                                        </div>

                                        <div>
                                            <label
                                                class="block mb-1 text-[10px] font-bold text-gray-400 uppercase">Absorbance
                                                Value</label>
                                            <div class="relative">
                                                <input type="number" step="0.0001" min="0" max="5"
                                                    name="absorbance[{{ $detail->tablet_number }}]"
                                                    value="{{ $detail->absorbance !== null ? \App\Support\NumberFormatter::smart($detail->absorbance, 4) : '' }}"
                                                    placeholder="0.0000" required
                                                    class="w-full py-2 pl-3 pr-12 text-sm font-bold transition-all border border-gray-200 outline-none rounded-xl bg-gray-50/30 focus:bg-white focus:ring-4 focus:ring-blue-50">
                                                <div
                                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-[10px] font-bold text-gray-300">
                                                    ABS</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="view-wrapper space-y-2 py-1 px-1 {{ $hasData ? '' : 'hidden' }}">
                                        <p class="text-xs text-slate-500">Measured batch:
                                            {{ $detail->booking_batch_id ? $booking->batches->firstWhere('id', $detail->booking_batch_id)?->batch_number ?? 'Invalid reference' : ($booking->products->count() === 1 ? 'Order product' : 'Unassigned (comparison warning)') }}
                                        </p>
                                        <div>
                                            <div
                                                class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">
                                                Dosimeter No.</div>
                                            <span
                                                class="text-sm font-bold text-gray-800 label-dosimeter-value">{{ $detail->dosimeter_number ?? '-' }}</span>
                                        </div>

                                        <div>
                                            <div
                                                class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">
                                                Position
                                            </div>

                                            <span class="text-sm font-bold text-gray-800">
                                                {{ $detail->position ?: '-' }}
                                            </span>
                                        </div>

                                        <div class="pt-2">
                                            <div
                                                class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">
                                                Absorbance</div>
                                            <span
                                                class="text-lg font-black text-gray-800 display-value">{{ $detail->absorbance !== null ? \App\Support\NumberFormatter::smart($detail->absorbance, 4) : '-' }}</span>
                                            <span class="text-xs font-bold text-gray-400 ml-0.5">ABS</span>
                                        </div>

                                        @if ($detail->dose_kgy !== null)
                                            <div class="pt-3 mt-3 border-t border-gray-100 border-dashed">
                                                <div
                                                    class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-0.5">
                                                    Absorbed Dose</div>
                                                <span
                                                    class="text-xl font-black text-emerald-600">{{ \App\Support\NumberFormatter::smart($detail->dose_kgy, 4) }}</span>
                                                <span class="text-xs font-bold text-emerald-400 ml-0.5">kGy</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    {{-- SINGLE INPUT GAMBAR --}}
                    <div id="single-image-wrapper"
                        class="input-wrapper mb-6 p-5 border border-dashed border-gray-200 rounded-2xl bg-gray-50/50 {{ $hasData ? 'hidden' : '' }}">
                        <label class="block mb-1.5 text-xs font-bold text-gray-700">Upload Attachment / Scan Image (Single
                            File)</label>
                        <input type="file" name="global_image" accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all">
                        <p class="mt-1.5 text-[10px] text-gray-400 font-medium">Format: JPEG, PNG, JPG (Max. 2MB). Gambar
                            ini akan disimpan pada detail tablet pertama.</p>
                    </div>

                    {{-- Bagian Tombol Aksi Form --}}
                    <div id="form-actions"
                        class="flex flex-col items-center justify-between gap-4 pt-6 border-t md:flex-row border-gray-50 {{ $hasData ? 'hidden' : '' }}">
                        <p class="max-w-sm text-xs font-medium text-center text-gray-400 md:text-left">
                            Make sure all values and dosimeter numbers are correct before saving.
                        </p>
                        <div class="flex w-full gap-3 md:w-auto">
                            <button type="button" id="btn-cancel-edit"
                                class="hidden w-full px-6 py-4 text-sm font-bold text-gray-500 transition-all bg-gray-100 hover:bg-gray-200 rounded-2xl md:w-auto">
                                Cancel
                            </button>
                            <button type="submit"
                                class="w-full px-10 py-4 text-sm font-black text-white transition-all shadow-xl md:w-auto bg-emerald-500 hover:bg-emerald-600 rounded-2xl shadow-emerald-100 hover:shadow-emerald-200 active:scale-95">
                                Save Data
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- DELETE DOSIMETER MODAL --}}
    <div id="deleteDosimeterModal" data-ui-modal role="dialog" aria-modal="true" aria-labelledby="deleteDosimeterTitle"
        tabindex="-1"
        class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">

        <div class="w-full max-w-md p-6 bg-white shadow-2xl rounded-2xl sm:p-8">

            <div class="flex items-center justify-center mb-5 text-red-600 w-14 h-14 rounded-2xl bg-red-50">
                <i class="text-xl fa-solid fa-trash-can"></i>
            </div>

            <h3 id="deleteDosimeterTitle" class="text-xl font-bold text-slate-900">
                Move Dosimeter Row to Trash?
            </h3>

            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                You are about to move to Trash
                <span id="deleteTabletLabel" class="font-bold text-slate-900"></span>.
                Remaining tablets will be renumbered automatically.
            </p>

            <div class="p-3 mt-4 border border-amber-200 bg-amber-50 rounded-xl">
                <p class="text-xs leading-relaxed text-amber-800">
                    <i class="mr-1 fa-solid fa-triangle-exclamation"></i>
                    Measurement data will be removed from the active set.
                    This action will be recorded in the audit history.
                </p>
            </div>

            <p id="deleteDosimeterError" role="alert" class="hidden mt-4 text-sm text-red-600"></p>

            <div class="flex gap-3 mt-6">
                <button data-modal-dismiss type="button" onclick="closeDeleteDosimeterModal()"
                    class="flex-1 px-4 py-3 text-sm font-semibold bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200">
                    Cancel
                </button>

                <button type="button" id="confirmDeleteDosimeterButton" onclick="submitDeleteDosimeter()"
                    class="flex-1 px-4 py-3 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700">
                    Move to Trash
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let selectedDosimeterDetailId = null;
            let deletingDosimeter = false;

            function openDeleteDosimeterModal(detailId, tabletNumber, dosimeterNumber) {
                if (deletingDosimeter) return;

                selectedDosimeterDetailId = detailId;

                const modal = document.getElementById('deleteDosimeterModal');
                const label = document.getElementById('deleteTabletLabel');
                const error = document.getElementById('deleteDosimeterError');

                label.textContent = `Tablet #${tabletNumber}${
        dosimeterNumber ? ` (${dosimeterNumber})` : ''
    }`;

                error.textContent = '';
                error.classList.add('hidden');

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.focus();
            }

            function closeDeleteDosimeterModal() {
                if (deletingDosimeter) return;

                const modal = document.getElementById('deleteDosimeterModal');

                modal.classList.add('hidden');
                modal.classList.remove('flex');

                selectedDosimeterDetailId = null;
            }

            async function submitDeleteDosimeter() {
                if (!selectedDosimeterDetailId || deletingDosimeter) return;

                const button = document.getElementById('confirmDeleteDosimeterButton');
                const error = document.getElementById('deleteDosimeterError');

                deletingDosimeter = true;
                button.disabled = true;
                button.textContent = 'Deleting...';

                const urlTemplate = @json(route('admin.dosimeter.details.destroy', ['detail' => '__DETAIL__']));

                const url = urlTemplate.replace(
                    '__DETAIL__',
                    encodeURIComponent(selectedDosimeterDetailId)
                );

                try {
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': @json(csrf_token()),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            confirmed: true,
                            confirm_delete: true
                        })
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok || data.status !== 'success') {
                        const message = Object.values(data.errors || {})
                            .flat()
                            .join(' ');

                        throw new Error(
                            message || data.message || 'Failed to delete dosimeter row.'
                        );
                    }

                    window.location.href = data.redirect;

                } catch (err) {
                    error.textContent = err.message;
                    error.classList.remove('hidden');
                } finally {
                    deletingDosimeter = false;
                    button.disabled = false;
                    button.textContent = 'Move to Trash';
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '{{ csrf_token() }}';

                let currentRecordId = "{{ $record ? $record->id : '' }}";

                const showError = message => {
                    const panel = document.getElementById('dosimetry-error');
                    panel.textContent = message;
                    panel.classList.remove('hidden');
                    panel.focus();
                };
                const send = async (form, url, body, json = false) => {
                    if (form.dataset.busy === 'true') return;
                    form.dataset.busy = 'true';
                    const buttons = form.querySelectorAll('button[type="submit"]');
                    buttons.forEach(button => button.disabled = true);
                    document.getElementById('dosimetry-error').classList.add('hidden');
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                ...(json ? {
                                    'Content-Type': 'application/json'
                                } : {})
                            },
                            body: json ? JSON.stringify(body) : body,
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok || data.status !== 'success') {
                            showError(Object.values(data.errors || {}).flat().join(' ') || data.message ||
                                'Data could not be saved. Reload and try again.');
                            return;
                        }
                        if (data.redirect) window.location.href = data.redirect;
                        else window.location.reload();
                    } catch (error) {
                        showError('Connection failed. Your entries are still here. Try saving again.');
                    } finally {
                        form.dataset.busy = 'false';
                        buttons.forEach(button => button.disabled = false);
                    }
                };
                document.getElementById('form-tablet-quantity').addEventListener('submit', function(event) {
                    event.preventDefault();
                    send(this, @json(route('admin.dosimeter.store-quantity')), {
                        booking_id: document.getElementById('booking_id').value,
                        record_id: currentRecordId || null,
                        tablet_quantity: document.getElementById('tablet_quantity').value
                    }, true);
                });
                document.getElementById('form-absorbance-values').addEventListener('submit', function(event) {
                    event.preventDefault();
                    if (!currentRecordId) {
                        showError('Generate the dosimeter rows first.');
                        return;
                    }
                    send(this, `/admin/dosimeter/store-absorbance/${currentRecordId}`, new FormData(this));
                });
                // ACTION 3: Toggle Edit Mode
                const btnEditMode = document.getElementById('btn-edit-mode');
                const btnCancelEdit = document.getElementById('btn-cancel-edit');
                const quantitySection = document.getElementById('quantity-section');
                const formActions = document.getElementById('form-actions');
                const singleImageWrapper = document.getElementById('single-image-wrapper');

                if (btnEditMode) {
                    btnEditMode.addEventListener('click', function() {
                        document.querySelectorAll('.view-wrapper').forEach(el => el.classList.add('hidden'));
                        document.querySelectorAll('.input-wrapper').forEach(el => el.classList.remove(
                            'hidden'));
                        quantitySection.classList.remove('hidden');
                        formActions.classList.remove('hidden');
                        if (singleImageWrapper) singleImageWrapper.classList.remove('hidden');
                        btnCancelEdit.classList.remove('hidden');
                        this.classList.add('hidden');
                    });
                }

                if (btnCancelEdit) {
                    btnCancelEdit.addEventListener('click', function() {
                        document.querySelectorAll('.view-wrapper').forEach(el => el.classList.remove('hidden'));
                        document.querySelectorAll('.input-wrapper').forEach(el => el.classList.add('hidden'));
                        quantitySection.classList.add('hidden');
                        formActions.classList.add('hidden');
                        if (singleImageWrapper) singleImageWrapper.classList.add('hidden');
                        btnEditMode.classList.remove('hidden');
                    });
                }

            });
        </script>
    @endpush
@endsection

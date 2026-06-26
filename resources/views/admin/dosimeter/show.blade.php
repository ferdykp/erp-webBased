@extends('admin.layout.app')

@section('title', 'Input Absorbance - ' . $booking->booking_code)

@section('content')

    <div class="max-w-5xl mx-auto space-y-6">

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
                            <button type="submit"
                                class="px-6 py-3 text-sm font-bold text-white transition-all bg-gray-900 shadow-lg hover:bg-black rounded-xl active:scale-95">
                                Generate
                            </button>
                        </div>
                        <p class="mt-2 text-[11px] text-gray-400 font-medium">
                            <i class="mr-1 fa-solid fa-circle-info"></i> Changing quantity will reset existing configuration
                            and values for this booking.
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
                            numbers, and automatic cubic spline calculations.</p>

                        <div
                            class="mt-2 text-[11px] bg-amber-50 border border-amber-100 text-amber-800 px-3 py-1.5 rounded-lg font-mono inline-block">
                            <span class="font-bold">Formula:</span> y = 13.099x³ + 8.7891x² + 57.786x - 2.423 <span
                                class="mx-1 text-gray-400">|</span> (x = Absorbance, y = Absorbed Dose)
                        </div>
                    </div>

                    <button type="button" id="btn-edit-mode"
                        class="self-start md:self-center px-4 py-2 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 transition-all rounded-xl {{ $hasData ? '' : 'hidden' }}">
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
                    <div id="dynamic-inputs-container" class="grid grid-cols-2 gap-5 mb-8 lg:grid-cols-3">
                        @if ($record)
                            @foreach ($record->details as $detail)
                                <div
                                    class="p-5 transition-all duration-300 bg-white border border-gray-100 shadow-sm group rounded-2xl">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tablet
                                            #{{ $detail->tablet_number }}</span>
                                        <i class="text-sm text-blue-100 fa-solid fa-flask"></i>
                                    </div>

                                    <div class="input-wrapper space-y-3 {{ $hasData ? 'hidden' : '' }}">
                                        <div>
                                            <label
                                                class="block mb-1 text-[10px] font-bold text-gray-400 uppercase">Dosimeter
                                                No.</label>
                                            <input type="text" name="dosimeter_number[{{ $detail->tablet_number }}]"
                                                value="{{ $detail->dosimeter_number }}" placeholder="e.g. D-01" required
                                                class="w-full px-3 py-2 text-sm font-bold transition-all border border-gray-200 outline-none rounded-xl bg-gray-50/30 focus:bg-white focus:ring-4 focus:ring-blue-50">
                                        </div>

                                        <div>
                                            <label
                                                class="block mb-1 text-[10px] font-bold text-gray-400 uppercase">Absorbance
                                                Value</label>
                                            <div class="relative">
                                                <input type="number" step="0.0001" min="0" max="5"
                                                    name="absorbance[{{ $detail->tablet_number }}]"
                                                    value="{{ $detail->absorbance }}" placeholder="0.0000" required
                                                    class="w-full py-2 pl-3 pr-12 text-sm font-bold transition-all border border-gray-200 outline-none rounded-xl bg-gray-50/30 focus:bg-white focus:ring-4 focus:ring-blue-50">
                                                <div
                                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-[10px] font-bold text-gray-300">
                                                    ABS</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="view-wrapper space-y-2 py-1 px-1 {{ $hasData ? '' : 'hidden' }}">
                                        <div>
                                            <div
                                                class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">
                                                Dosimeter No.</div>
                                            <span
                                                class="text-sm font-bold text-gray-800 label-dosimeter-value">{{ $detail->dosimeter_number ?? '-' }}</span>
                                        </div>

                                        <div class="pt-2">
                                            <div
                                                class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">
                                                Absorbance</div>
                                            <span
                                                class="text-lg font-black text-gray-800 display-value">{{ $detail->absorbance ? (float) $detail->absorbance : '-' }}</span>
                                            <span class="text-xs font-bold text-gray-400 ml-0.5">ABS</span>
                                        </div>

                                        @if ($detail->dose_kgy !== null)
                                            <div class="pt-3 mt-3 border-t border-gray-100 border-dashed">
                                                <div
                                                    class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-0.5">
                                                    Absorbed Dose</div>
                                                <span
                                                    class="text-xl font-black text-emerald-600">{{ (float) number_format($detail->dose_kgy, 4) }}</span>
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '{{ csrf_token() }}';

                let currentRecordId = "{{ $record ? $record->id : '' }}";

                // ACTION 1: Generate Kuantitas Tablet
                document.getElementById('form-tablet-quantity').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const quantity = document.getElementById('tablet_quantity').value;
                    const bookingId = document.getElementById('booking_id').value;

                    fetch("{{ route('admin.dosimeter.store-quantity') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                booking_id: bookingId,
                                tablet_quantity: quantity
                            })
                        })
                        .then(response => {
                            if (!response.ok) throw response;
                            return response.json();
                        })
                        .then(res => {
                            if (res.status === 'success') {
                                currentRecordId = res.data.id;
                                renderDynamicColumns(res.data.details);

                                document.getElementById('btn-edit-mode').classList.add('hidden');
                                document.getElementById('form-actions').classList.remove('hidden');
                                document.getElementById('btn-cancel-edit').classList.add('hidden');
                                document.getElementById('single-image-wrapper').classList.remove('hidden');
                            }
                        })
                        .catch(async (err) => {
                            console.error(err);
                            alert('Gagal generate baris tablet.');
                        });
                });

                // ACTION 2: Simpan Data via FormData
                document.getElementById('form-absorbance-values').addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (!currentRecordId || String(currentRecordId).trim() === '') {
                        alert('Record ID tidak ditemukan. Silakan isi Step 1 kembali.');
                        return;
                    }

                    const formData = new FormData(this);
                    let hasEmptyField = false;

                    const inputs = document.querySelectorAll('#dynamic-inputs-container input');
                    inputs.forEach(input => {
                        if (input.hasAttribute('required') && (!input.value || input.value.trim() ===
                                '')) {
                            hasEmptyField = true;
                            input.classList.add('border-red-500');
                        } else {
                            input.classList.remove('border-red-500');
                        }
                    });

                    if (hasEmptyField) {
                        alert('Semua kolom Dosimeter No. dan Absorbance Value wajib diisi!');
                        return;
                    }

                    fetch(`/admin/dosimeter/store-absorbance/${currentRecordId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: formData
                        })
                        .then(async (response) => {
                            if (!response.ok) throw response;
                            return response.json();
                        })
                        .then(res => {
                            if (res.status === 'success' && res.redirect) {
                                window.location.href = res.redirect;
                            } else {
                                alert('Error: ' + res.message);
                            }
                        })
                        .catch(async (err) => {
                            console.error(err);
                            alert('Terjadi kesalahan sistem saat menyimpan data.');
                        });
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

                function renderDynamicColumns(details) {
                    const container = document.getElementById('dynamic-inputs-container');
                    container.innerHTML = '';

                    details.forEach(detail => {
                        const box = document.createElement('div');
                        box.className = 'p-5 bg-white border border-gray-100 rounded-2xl shadow-sm';
                        box.innerHTML = `
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tablet #${detail.tablet_number}</span>
                                <i class="text-sm text-blue-100 fa-solid fa-flask"></i>
                            </div>
                            <div class="space-y-3 input-wrapper">
                                <div>
                                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase">Dosimeter No.</label>
                                    <input type="text" name="dosimeter_number[${detail.tablet_number}]" placeholder="e.g. D-01" required
                                           class="w-full px-3 py-2 text-sm font-bold transition-all border border-gray-200 outline-none rounded-xl bg-gray-50/50 focus:bg-white focus:ring-4 focus:ring-blue-50">
                                </div>
                                <div>
                                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase">Absorbance Value</label>
                                    <div class="relative">
                                        <input type="number" step="0.0001" min="0" max="5" name="absorbance[${detail.tablet_number}]" placeholder="0.0000" required
                                               class="w-full py-2 pl-3 pr-12 text-sm font-bold transition-all border border-gray-200 outline-none rounded-xl bg-gray-50/50 focus:bg-white focus:ring-4 focus:ring-blue-50">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 text-[10px] font-bold text-gray-300 uppercase">Abs</div>
                                    </div>
                                </div>
                            </div>
                            <div class="hidden px-1 py-1 space-y-2 view-wrapper">
                                <div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Dosimeter No.</div>
                                    <span class="text-sm font-bold text-gray-800 label-dosimeter-value">-</span>
                                </div>
                                <div class="pt-2">
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Absorbance</div>
                                    <span class="text-lg font-black text-gray-800 display-value">-</span>
                                    <span class="text-xs font-bold text-gray-400 ml-0.5">ABS</span>
                                </div>
                            </div>
                        `;
                        container.appendChild(box);
                    });

                    document.getElementById('absorbance-section').classList.remove('hidden');
                }
            });
        </script>
    @endpush
@endsection

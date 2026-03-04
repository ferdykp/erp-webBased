    <div id="warehouseModal"
        class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-6">
        <div
            class="bg-white w-full max-w-5xl rounded-[3.5rem] shadow-2xl relative max-h-[90vh] flex flex-col overflow-hidden">

            {{-- Header & Progress Indicator --}}
            <div class="px-12 pt-12 pb-6 border-b border-slate-50">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-3xl font-black text-slate-800">Check-in Process</h3>
                        <p class="text-sm font-medium tracking-widest uppercase text-slate-500">Code: <span
                                id="display_booking_code" class="text-blue-600"></span></p>
                    </div>
                    <button onclick="closeWarehouseModal()"
                        class="p-4 transition-all bg-slate-50 hover:bg-red-50 rounded-2xl group">
                        <i class="fa-solid fa-xmark text-slate-400 group-hover:text-red-500"></i>
                    </button>
                </div>

                {{-- Step Tracker --}}
                <div class="flex items-center justify-between max-w-2xl mx-auto">
                    <div class="flex flex-col items-center gap-2 step-item active" data-step="1">
                        <div
                            class="flex items-center justify-center w-10 h-10 font-bold text-white bg-blue-600 rounded-full shadow-lg step-circle shadow-blue-100">
                            1</div>
                        <span class="text-[10px] font-black uppercase text-slate-400">Verify</span>
                    </div>
                    <div class="flex-1 h-px mx-4 bg-slate-100"></div>
                    <div class="flex flex-col items-center gap-2 step-item" data-step="2">
                        <div
                            class="flex items-center justify-center w-10 h-10 font-bold rounded-full step-circle bg-slate-100 text-slate-400">
                            2</div>
                        <span class="text-[10px] font-black uppercase text-slate-400">Batching</span>
                    </div>
                    <div class="flex-1 h-px mx-4 bg-slate-100"></div>
                    <div class="flex flex-col items-center gap-2 step-item" data-step="3">
                        <div
                            class="flex items-center justify-center w-10 h-10 font-bold rounded-full step-circle bg-slate-100 text-slate-400">
                            3</div>
                        <span class="text-[10px] font-black uppercase text-slate-400">Placement</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.bookings.checkin') }}" method="POST" id="checkInForm"
                class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="booking_code" id="modal_booking_code">

                <div class="flex-1 px-12 py-10 overflow-y-auto scrollbar-hide">

                    {{-- STEP 1: VERIFICATION --}}
                    <div class="step-content" id="step1">
                        <div class="p-10 bg-blue-50/40 border border-blue-100 rounded-[3rem] mb-8">
                            <h4 class="mb-8 text-lg font-black text-slate-800">Mencocokkan Data Aktual</h4>

                            {{-- Row 1: Utama --}}
                            <div class="grid grid-cols-2 gap-8 mb-8 md:grid-cols-4">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Product
                                    </p>
                                    <p id="check_product_name" class="text-lg font-black text-slate-800">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Category
                                    </p>
                                    <p id="check_product_type" class="font-bold text-slate-600">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Booked
                                        Qty</p>
                                    <p class="font-black text-slate-800"><span id="check_qty">0</span> <span
                                            id="check_unit"></span></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Expect.
                                        Temp</p>
                                    <p class="font-black text-blue-600"><span id="check_temp">-</span>°C</p>
                                </div>
                            </div>

                            {{-- Row 2: Detail Teknis --}}
                            <div class="grid grid-cols-2 gap-8 pt-8 border-t border-blue-100/50 md:grid-cols-3">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dose
                                        Range</p>
                                    <p class="font-black text-emerald-600"><span id="check_dmin">-</span> - <span
                                            id="check_dmax">-</span> kGy</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dimension
                                        / Pack</p>
                                    <p id="check_dimension" class="font-bold text-slate-700">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Weight /
                                        Pcs</p>
                                    <p class="font-bold text-slate-700"><span id="check_weight">-</span> kg</p>
                                </div>
                            </div>
                        </div>
                        {{-- Form Confirmation --}}
                        <div class="space-y-4">
                            <label
                                class="flex items-center gap-4 p-6 transition-all border-2 cursor-pointer border-slate-100 rounded-3xl hover:border-blue-500 group">
                                <input type="checkbox" required
                                    class="w-6 h-6 text-blue-600 rounded-lg border-slate-200 focus:ring-blue-500">
                                <span class="text-sm font-bold text-slate-600 group-hover:text-slate-800">Saya
                                    mengonfirmasi data fisik yang datang sesuai dengan spesifikasi teknis di
                                    atas.</span>
                            </label>
                            <input type="text" name="pic_warehouse" required
                                placeholder="Nama PIC Warehouse Penanggung Jawab"
                                class="w-full px-8 py-5 text-sm font-bold border-none bg-slate-50 rounded-3xl focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- STEP 2: BATCHING & STAFF --}}
                    <div class="hidden step-content" id="step2">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-lg font-black text-slate-800">Pembagian Batch & Porter</h4>
                            <div class="flex items-center gap-4">
                                <span id="cap_badge"
                                    class="px-5 py-2 text-xs font-black bg-slate-100 rounded-2xl text-slate-600">
                                    Total: <span id="current_total_display">0</span> / <span
                                        id="total_qty_display">0</span>
                                </span>
                                <button type="button" onclick="addBatchField()"
                                    class="px-6 py-2 bg-blue-600 text-white text-[10px] font-black uppercase rounded-xl hover:bg-blue-700">+
                                    Add Batch</button>
                            </div>
                        </div>
                        <div id="batchContainer" class="space-y-4">
                        </div>
                    </div>

                    {{-- STEP 3: MANUAL PLACEMENT --}}
                    <div class="hidden step-content" id="step3">
                        <h4 class="mb-6 text-lg font-black text-slate-800">Input Lokasi Penempatan</h4>
                        <div class="flex gap-4 p-6 mb-8 border bg-amber-50 border-amber-100 rounded-3xl">
                            <i class="mt-1 fa-solid fa-circle-info text-amber-500"></i>
                            <p class="text-xs font-bold leading-relaxed text-amber-700">Silahkan input kode palet atau
                                lokasi secara manual sesuai dengan posisi yang diletakkan oleh porter di lapangan.</p>
                        </div>
                        <div id="placementContainer" class="space-y-4">
                        </div>
                    </div>

                </div>

                {{-- Navigation Buttons --}}
                <div class="flex gap-4 px-12 py-8 border-t border-slate-50">
                    <button type="button" id="prevBtn" onclick="changeStep(-1)"
                        class="flex-1 hidden py-5 text-xs font-black uppercase text-slate-400 bg-slate-50 rounded-3xl hover:bg-slate-100">Previous</button>
                    <button type="button" id="nextBtn" onclick="changeStep(1)"
                        class="flex-1 py-5 text-xs font-black text-white uppercase bg-blue-600 shadow-xl rounded-3xl hover:bg-blue-700 shadow-blue-100">Continue</button>
                    <button type="submit" id="finalSubmitBtn"
                        class="flex-1 hidden py-5 text-xs font-black text-white uppercase shadow-xl bg-emerald-500 rounded-3xl hover:bg-emerald-600 shadow-emerald-100">Confirm
                        & Save Arrival</button>
                </div>
            </form>
        </div>
    </div>

<div id="qcModal"
    class="fixed inset-0 z-[200] hidden items-start justify-center bg-slate-900/60 backdrop-blur-sm p-4 md:p-10 overflow-y-auto custom-scrollbar">

    <div
        class="bg-white w-full max-w-4xl rounded-[3rem] shadow-2xl overflow-hidden animate-in zoom-in duration-300 my-auto shadow-blue-900/20">

        {{-- Header: Dibuat Sticky agar tetap terlihat saat scroll --}}
        <div class="sticky top-0 z-10 p-6 text-center border-b border-slate-100 bg-white/80 backdrop-blur-md">
            <button type="button" onclick="closeQCModal()"
                class="absolute transition-colors right-8 top-6 text-slate-400 hover:text-slate-600">
                <i class="text-xl fa-solid fa-xmark"></i>
            </button>

            <div id="qcIcon" class="flex items-center justify-center mx-auto mb-3 shadow-sm w-14 h-14 rounded-2xl">
                <i class="text-xl fa-solid fa-shield-check"></i>
            </div>
            <h3 class="text-xl font-black tracking-tight text-slate-800">Final Quality Control Check</h3>
            <p id="qcTargetStatus" class="text-[9px] font-black uppercase tracking-[0.3em] text-blue-600 mt-1"></p>
        </div>

        <form id="qcForm" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="status" id="qcStatusInput">

            <div class="p-6 space-y-8 md:p-8">
                {{-- Customer & Summary Grid --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    {{-- Customer Details Card --}}
                    <div
                        class="p-5 border border-slate-100 rounded-[2rem] bg-white shadow-sm hover:border-blue-100 transition-colors">
                        <p class="text-[9px] font-black text-slate-400 uppercase mb-4 tracking-widest">Customer Details
                        </p>
                        <div class="space-y-3">
                            <div class="flex items-center gap-4">
                                <div id="qcCustAvatar"
                                    class="flex items-center justify-center w-10 h-10 font-black text-blue-600 rounded-xl bg-blue-50">
                                    ?</div>
                                <div>
                                    <h4 id="qcCustName" class="text-sm font-black leading-tight text-slate-800">Customer
                                        Name</h4>
                                    <p id="qcCode" class="text-[10px] font-bold text-blue-500 uppercase">#BOK-000</p>
                                </div>
                            </div>
                            <div class="pt-3 space-y-2 border-t border-slate-50">
                                <div class="flex justify-between items-center text-[10px]">
                                    <span class="font-bold uppercase text-slate-400">PIC Warehouse:</span>
                                    <span id="qcPic" class="font-black text-slate-700">-</span>
                                </div>
                                <div class="flex justify-between items-center text-[10px]">
                                    <span class="font-bold uppercase text-slate-400">Arrival Time:</span>
                                    <span id="qcArrival" class="font-black text-slate-700">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Product Summary Card --}}
                    <div class="p-5 border border-slate-100 rounded-[2rem] bg-slate-50/50">
                        <p class="text-[9px] font-black text-slate-400 uppercase mb-4 tracking-widest">Product Summary
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-4 bg-white border shadow-sm rounded-2xl border-slate-100">
                                <p class="text-[8px] font-black text-slate-400 uppercase">Total Pallets</p>
                                <p id="qcPalletCount" class="text-lg font-black text-slate-800">0</p>
                            </div>
                            <div class="p-4 bg-white border shadow-sm rounded-2xl border-slate-100">
                                <p class="text-[8px] font-black text-slate-400 uppercase">Total Qty</p>
                                <p id="qcTotalQty" class="text-lg font-black text-emerald-600">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Production Distribution Section --}}
                <section id="qc_batch_result_section" class="space-y-4">
                    <div class="flex items-center justify-between px-2">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex items-center justify-center w-9 h-9 bg-emerald-50 rounded-xl text-emerald-600">
                                <i class="text-sm fa-solid fa-layer-group"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Production
                                    Distribution</p>
                                <h4 class="text-xs font-black tracking-tighter uppercase text-slate-800">Detail Hasil
                                    Pembagian Batch</h4>
                            </div>
                        </div>
                        <span id="qc_batch_count_badge"
                            class="px-3 py-1 bg-emerald-100 text-emerald-700 text-[9px] font-black rounded-full uppercase">
                            0 Batches</span>
                    </div>

                    <div class="overflow-x-auto border border-emerald-100 rounded-[2rem] shadow-sm bg-white">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead class="bg-emerald-50/50 text-[9px] font-black text-emerald-600 uppercase">
                                <tr>
                                    <th class="px-6 py-4">Batch ID</th>
                                    <th class="px-5 py-4">Porter Spec</th>
                                    <th class="px-5 py-4">Product Details</th>
                                    <th class="px-5 py-4 text-center">Range Dose</th>
                                    <th class="px-6 py-4 text-right">Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="qc_detail_batch_table_body" class="divide-y divide-emerald-50/50">
                                {{-- Injected by JS --}}
                            </tbody>
                            <tfoot class="font-black bg-slate-50/80 text-slate-800">
                                <tr>
                                    <td colspan="4"
                                        class="px-6 py-4 text-[10px] tracking-widest uppercase text-slate-400">Total
                                        Accumulation</td>
                                    <td id="qc_batch_total_sum"
                                        class="px-6 py-4 text-sm italic text-right text-emerald-700">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                {{-- Validation Checkboxes --}}
                <div class="grid grid-cols-1 gap-3 pt-4 border-t md:grid-cols-2 border-slate-100">
                    <label
                        class="flex items-start gap-3 p-4 border-2 border-slate-50 rounded-[1.5rem] hover:border-blue-200 transition-all cursor-pointer group">
                        <input type="checkbox" required
                            class="w-5 h-5 mt-1 text-blue-600 transition-all rounded-md border-slate-200 focus:ring-blue-500">
                        <span
                            class="text-[10px] leading-relaxed font-bold text-slate-500 group-hover:text-slate-700 transition-colors italic">
                            Data distribusi dan kuantitas sudah sesuai dengan fisik barang.
                        </span>
                    </label>
                    <label
                        class="flex items-start gap-3 p-4 border-2 border-slate-50 rounded-[1.5rem] hover:border-blue-200 transition-all cursor-pointer group">
                        <input type="checkbox" required
                            class="w-5 h-5 mt-1 text-blue-600 transition-all rounded-md border-slate-200 focus:ring-blue-500">
                        <span
                            class="text-[10px] leading-relaxed font-bold text-slate-500 group-hover:text-slate-700 transition-colors italic">
                            Produk layak diproses di Irradiation (Rad-Room).
                        </span>
                    </label>
                </div>
            </div>

            {{-- Footer: Dibuat Sticky di Bawah --}}
            <div class="sticky bottom-0 flex gap-3 p-6 bg-white border-t border-slate-100">
                <button type="button" onclick="closeQCModal()"
                    class="flex-1 py-4 text-[11px] font-black uppercase text-slate-400 hover:text-slate-600 transition-colors">
                    Cancel
                </button>
                <button type="submit" id="qcSubmitBtn"
                    class="flex-[2] py-4 text-[11px] font-black text-white uppercase shadow-lg rounded-2xl active:scale-95 transition-all">
                    Confirm & Start
                </button>
            </div>
        </form>
    </div>
</div>

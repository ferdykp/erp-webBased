{{-- MODAL DETAIL BOOKING --}}
<div id="detailModal"
    class="fixed inset-0 z-[150] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-500 p-4">

    <div id="detailCard"
        class="relative w-full max-w-5xl max-h-[95vh] overflow-hidden bg-white shadow-2xl rounded-[3.5rem] transform scale-95 opacity-0 transition-all duration-500 flex flex-col">

        {{-- Header --}}
        <div class="flex items-start justify-between px-12 pt-12 pb-6">
            <div>
                <h2 class="text-3xl font-black tracking-tighter text-slate-800">Reservation Details</h2>
                <div class="flex items-center gap-3 mt-2">
                    <span id="detail_booking_code"
                        class="px-3 py-1 text-[10px] font-black tracking-widest text-blue-600 bg-blue-50 rounded-lg uppercase"></span>
                    <span id="detail_status_badge"
                        class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg"></span>
                </div>
            </div>
            <button onclick="closeDetailModal()"
                class="flex items-center justify-center w-12 h-12 transition-all bg-slate-50 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-2xl">
                <i class="text-xl fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Body Content --}}
        <div class="flex-1 px-12 pb-12 space-y-10 overflow-y-auto scrollbar-hide">

            {{-- INFO GRID: Client & Scheduling --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="p-8 bg-slate-50 border border-slate-100 rounded-[2.5rem]">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Client Information
                    </p>
                    <h4 class="text-xl font-black text-slate-800" id="detail_customer_name">-</h4>
                    <p class="mt-1 text-sm font-medium text-slate-500" id="detail_customer_email">-</p>
                </div>

                <div class="p-8 bg-indigo-50 border border-indigo-100 rounded-[2.5rem]">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-4">Scheduling</p>
                    <div class="space-y-2">
                        <p class="flex justify-between text-xs font-bold text-slate-600">Booked:
                            <span class="text-indigo-700" id="detail_booking_date">-</span>
                        </p>
                        <p class="flex justify-between text-xs font-bold text-slate-600">Arrived:
                            <span class="text-indigo-700" id="detail_arrival_time">-</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- RESOURCE & PALLET SUMMARY --}}
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                {{-- PIC Section --}}
                <div class="md:col-span-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Warehouse Resource
                    </p>
                    <div class="flex items-center gap-4 group">
                        <div
                            class="flex items-center justify-center w-12 h-12 bg-white border shadow-sm border-slate-100 rounded-2xl">
                            <i class="text-blue-600 fa-solid fa-user-tie"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">PIC Warehouse</p>
                            <p class="text-sm font-black text-slate-800" id="detail_pic_warehouse">-</p>
                        </div>
                    </div>
                </div>

                {{-- Pallet Section --}}
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Allocated Pallets
                        </p>
                        <span class="text-[9px] font-bold text-blue-500 bg-blue-50 px-2 py-0.5 rounded-md uppercase"
                            id="pallet_count_text">0 Pallets</span>
                    </div>
                    <div id="detail_pallets_list" class="flex flex-wrap gap-2 pt-1">
                        {{-- Injected by JS --}}
                    </div>
                </div>
            </div>

            {{-- BATCH DISTRIBUTION TABLE --}}
            <section id="batch_result_section" class="pt-8 space-y-4 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex items-center justify-center w-10 h-10 bg-emerald-50 rounded-xl text-emerald-600">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Production
                                Distribution</p>
                            <h4 class="text-sm font-black text-slate-800">Detail Hasil Pembagian Batch & Produk</h4>
                        </div>
                    </div>
                    <span id="batch_count_badge"
                        class="px-4 py-1.5 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-full uppercase tracking-widest">0
                        Batches</span>
                </div>

                <div class="overflow-hidden border border-emerald-100 rounded-[2.5rem] shadow-sm">
                    <table class="w-full text-left">
                        <thead class="bg-emerald-50/50 text-[9px] font-black text-emerald-600 uppercase">
                            <tr>
                                <th class="px-8 py-5">Batch ID</th>
                                <th class="px-6 py-5">Porter Specification</th>
                                <th class="px-6 py-5">Product Details</th>
                                <th class="px-6 py-5 text-center">Range Dose</th>
                                <th class="px-8 py-5 text-right">Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="detail_batch_table_body" class="divide-y divide-emerald-50/50">
                            {{-- Injected by JS --}}
                        </tbody>
                        <tfoot class="font-black bg-slate-50 text-slate-800">
                            <tr>
                                <td colspan="4" class="px-8 py-5 text-xs tracking-widest uppercase text-slate-400">
                                    Total Accumulation</td>
                                <td id="batch_total_sum" class="px-8 py-5 text-sm text-right text-emerald-700">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>

        {{-- Footer --}}
        <div class="flex justify-end px-12 py-8 border-t bg-slate-50 border-slate-100">
            <button onclick="closeDetailModal()"
                class="px-10 py-4 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-black transition-all">
                Close Details
            </button>
        </div>
    </div>
</div>

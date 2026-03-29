<div id="orderDetailModal"
    class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div
        class="bg-white w-full max-w-5xl rounded-[2.5rem] shadow-2xl relative max-h-[90vh] flex flex-col overflow-hidden">

        <div class="px-6 pt-10 pb-6 border-b md:px-12 border-slate-50 shrink-0">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-2xl font-black text-slate-800">Verify & Payment</h3>
                    <p class="text-xs font-medium tracking-widest uppercase text-slate-500">Code: <span
                            id="display_booking_code" class="text-blue-600"></span></p>
                </div>
                <button type="button" onclick="closeOrderDetailModal()"
                    class="p-3 transition-all bg-slate-50 hover:bg-red-50 rounded-2xl">
                    <i class="fa-solid fa-xmark text-slate-400"></i>
                </button>
            </div>

            <div class="flex items-center justify-center max-w-md gap-4 mx-auto">
                <div id="step1_indicator" class="flex flex-col items-center gap-2">
                    <div
                        class="flex items-center justify-center w-10 h-10 font-bold text-white bg-blue-600 rounded-full shadow-lg shadow-blue-100">
                        1</div>
                    <span class="text-[10px] font-black uppercase text-blue-600">Verify</span>
                </div>
                <div class="flex-1 h-px bg-slate-100"></div>
                <div id="step2_indicator" class="flex flex-col items-center gap-2">
                    <div id="step2_circle"
                        class="flex items-center justify-center w-10 h-10 font-bold transition-all rounded-full bg-slate-100 text-slate-400">
                        2</div>
                    <span id="step2_text" class="text-[10px] font-black uppercase text-slate-400">Payment</span>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.bookings.store') }}" method="POST" id="finalForm"
            class="flex flex-col flex-1 overflow-hidden">
            @csrf
            <input type="hidden" name="customer_id" id="final_customer_id">
            <input type="hidden" name="product_name" id="final_product_name">
            <input type="hidden" name="product_type" id="final_product_type">
            <input type="hidden" name="quantity" id="final_qty">
            <input type="hidden" name="unit" id="final_unit">
            <input type="hidden" name="dmin" id="final_dmin">
            <input type="hidden" name="dmax" id="final_dmax">
            <input type="hidden" name="dimension_pack" id="final_dim_pack">
            <input type="hidden" name="expect_temp" id="final_temp">
            <input type="hidden" name="total_price" id="final_total_price">
            <input type="hidden" name="gross_weight_per_pcs" id="final_gross_pcs">
            <input type="hidden" name="total_gross_weight" id="final_gross_total">

            <input type="hidden" name="vol_per_pcs" id="final_vol_per_pcs">
            <input type="hidden" name="vol_total" id="final_vol_total">
            <input type="hidden" name="net_weight_pcs" id="final_net_weight_pcs">
            <input type="hidden" name="total_net_weight" id="final_total_net_weight">

            <div id="modalScrollArea"
                class="flex-1 px-6 md:px-12 py-8 overflow-y-auto 
                [&::-webkit-scrollbar]:w-2 
                [&::-webkit-scrollbar-track]:bg-transparent 
                [&::-webkit-scrollbar-thumb]:bg-slate-200 
                [&::-webkit-scrollbar-thumb]:rounded-full
                hover:[&::-webkit-scrollbar-thumb]:bg-slate-300">

                <div id="content_step1" class="space-y-8">
                    <div class="p-6 md:p-8 bg-blue-50/40 border border-blue-100 rounded-[2.5rem]">
                        <h4 class="mb-6 text-sm font-black tracking-widest uppercase text-slate-800">Data Ringkasan</h4>
                        <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Product</p>
                                <p id="check_product_name" class="font-bold text-slate-800">-</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Qty</p>
                                <p class="font-bold text-slate-800"><span id="check_qty">0</span> <span id="check_unit"
                                        class="text-xs uppercase"></span></p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Dose</p>
                                <p class="font-bold text-emerald-600"><span id="check_dmin">0</span> - <span
                                        id="check_dmax">0</span> kGy</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Dimension</p>
                                <p id="check_dimension" class="font-bold text-slate-700">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Volume/Pcs (m³)</label>
                            <input type="number" step="0.000001" name="vol_per_pcs" id="mod_vol_pcs" readonly
                                class="w-full px-4 py-3 font-bold border-none bg-slate-50 rounded-xl text-slate-600 focus:ring-0">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Total Volume
                                (m³)</label>
                            <input type="number" step="0.000001" name="vol_total" id="mod_vol_total" readonly
                                class="w-full px-4 py-3 font-bold border-none bg-slate-50 rounded-xl text-slate-600 focus:ring-0">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Net Weight/Pcs
                                (kg)</label>
                            <input type="number" step="0.01" name="net_weight_pcs" id="mod_net_pcs" readonly
                                class="w-full px-4 py-3 font-bold border-none bg-slate-50 rounded-xl text-slate-600 focus:ring-0">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Total Net Weight
                                (kg)</label>
                            <input type="number" step="0.01" name="total_net_weight" id="mod_net_total" readonly
                                class="w-full px-4 py-3 font-bold border-none bg-slate-50 rounded-xl text-slate-600 focus:ring-0">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Gross Weight/Pcs
                                (kg)</label>
                            <input type="number" step="0.01" name="gross_weight_pcs" id="mod_gross_pcs" readonly
                                class="w-full px-4 py-3 font-bold border-none bg-slate-50 rounded-xl text-slate-600 focus:ring-0">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Total Gross Weight
                                (kg)</label>
                            <input type="number" step="0.01" name="total_gross_weight" id="mod_gross_total"
                                readonly
                                class="w-full px-4 py-3 font-bold border-none bg-slate-50 rounded-xl text-slate-600 focus:ring-0">
                        </div>
                    </div>

                    <label
                        class="flex items-center gap-4 p-6 transition-all border-2 border-dashed cursor-pointer border-slate-100 rounded-3xl hover:border-blue-400">
                        <input type="checkbox" id="confirm_verify"
                            class="w-6 h-6 text-blue-600 rounded-lg focus:ring-0">
                        <span class="text-xs font-bold text-slate-500">Saya mengonfirmasi data fisik telah sesuai
                            dengan spesifikasi di atas.</span>
                    </label>

                    <button type="button" onclick="toStep2()"
                        class="w-full py-4 font-black tracking-widest text-white uppercase transition-all bg-blue-600 shadow-xl rounded-2xl hover:bg-blue-700 shadow-blue-100">
                        Lanjut ke Pembayaran <i class="ml-2 fa-solid fa-arrow-right"></i>
                    </button>
                </div>

                <div id="content_step2" class="hidden space-y-6">
                    <div class="bg-white border-2 border-slate-100 rounded-[2.5rem] overflow-hidden shadow-sm">
                        <div class="flex items-center justify-between px-8 py-4 border-b bg-slate-50 border-slate-100">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Rincian
                                Biaya</span>
                            <span class="text-[10px] font-bold py-1 px-3 bg-blue-100 text-blue-700 rounded-full">Rp 500
                                / dose</span>
                        </div>

                        <div class="p-8 space-y-4">
                            <div class="flex items-start justify-between text-slate-600">
                                <div>
                                    <p class="text-xs font-bold tracking-tighter uppercase">Harga Dasar (Subtotal)</p>
                                    <p class="text-[10px] text-slate-400 italic">500 × <span id="calc_qty">0</span>
                                        qty × <span id="calc_nett">0</span> kg × <span id="calc_dmin">0</span> kGy</p>
                                </div>
                                <span class="font-bold" id="display_subtotal">Rp 0</span>
                            </div>

                            <div class="flex items-center justify-between text-slate-600">
                                <p class="text-xs font-bold tracking-tighter uppercase text-slate-400">Pajak (PPN 11%)
                                </p>
                                <span class="font-bold text-slate-400" id="display_tax">Rp 0</span>
                            </div>

                            <div
                                class="flex items-center justify-between pt-4 border-t-2 border-dashed border-slate-100">
                                <p class="text-sm font-black tracking-widest uppercase text-slate-800">Total Bayar</p>
                                <h4 class="text-3xl font-black text-blue-600" id="display_total_price">Rp 0</h4>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border bg-slate-50 rounded-3xl border-slate-100">
                        <label
                            class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-4 block text-center">Gunakan
                            PPN dalam perhitungan?</label>
                        <div class="flex gap-4">
                            <label
                                class="flex-1 relative flex items-center justify-center p-3 border-2 bg-white rounded-2xl cursor-pointer has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 transition-all shadow-sm">
                                <input type="radio" name="use_ppn" value="0" checked
                                    onchange="calculatePrice()" class="absolute opacity-0">
                                <span class="text-sm font-black text-slate-500 group-checked:text-blue-600">TANPA
                                    PPN</span>
                            </label>
                            <label
                                class="flex-1 relative flex items-center justify-center p-3 border-2 bg-white rounded-2xl cursor-pointer has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 transition-all shadow-sm">
                                <input type="radio" name="use_ppn" value="0.11" onchange="calculatePrice()"
                                    class="absolute opacity-0">
                                <span class="text-sm font-black text-slate-500 group-checked:text-blue-600">PPN
                                    11%</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-6 border border-emerald-100 bg-emerald-50/30 rounded-3xl">
                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 shrink-0">
                                <i class="fa-solid fa-wallet"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black tracking-tight uppercase text-slate-800">Status
                                    Pembayaran</h4>
                                <p class="text-[10px] text-slate-500">Tentukan status invoice pesanan ini.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <label
                                class="relative flex items-center gap-3 p-4 border-2 bg-white rounded-2xl cursor-pointer hover:border-blue-500 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="payment_status" value="unpaid" checked
                                    class="absolute opacity-0">
                                <i class="fa-solid fa-clock text-slate-400"></i>
                                <div class="flex flex-col">
                                    <span class="font-black text-slate-800 uppercase text-[10px]">Bayar Nanti</span>
                                    <span
                                        class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">Invoicing</span>
                                </div>
                            </label>
                            <label
                                class="relative flex items-center gap-3 p-4 border-2 bg-white rounded-2xl cursor-pointer hover:border-blue-500 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="payment_status" value="paid"
                                    class="absolute opacity-0">
                                <i class="fa-solid fa-check-double text-slate-400"></i>
                                <div class="flex flex-col">
                                    <span class="font-black text-slate-800 uppercase text-[10px]">Sudah Bayar</span>
                                    <span
                                        class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">Lunas</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 pt-4 md:flex-row">
                        <button type="button" onclick="toStep1()"
                            class="flex-1 py-4 font-bold tracking-widest uppercase transition-all bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200">Kembali</button>
                        <button type="submit"
                            class="flex-[2] py-4 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-100 transition-all">Simpan
                            Order Sekarang</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function calculatePrice() {
        // 1. Ambil data input
        const qty = parseFloat(document.getElementById('final_qty').value) || 0;
        const nett = parseFloat(document.getElementById('mod_net_pcs').value) || 0;
        const dmin = parseFloat(document.getElementById('final_dmin').value) || 0;
        const pricePerDose = 500;

        // 2. Hitung Harga Dasar (Subtotal)
        const subtotal = pricePerDose * qty * nett * dmin;

        // 3. Hitung Pajak
        const ppnInput = document.querySelector('input[name="use_ppn"]:checked');
        const ppnRate = ppnInput ? parseFloat(ppnInput.value) : 0;
        const taxAmount = subtotal * ppnRate;
        const grandTotal = subtotal + taxAmount;

        // 4. Formatter Rupiah
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        });

        // 5. Update UI Breakdown
        document.getElementById('display_subtotal').innerText = formatter.format(subtotal);
        document.getElementById('display_tax').innerText = formatter.format(taxAmount);
        document.getElementById('display_total_price').innerText = formatter.format(grandTotal);

        // 6. Update Detail Rumus (Kecil)
        document.getElementById('calc_qty').innerText = qty.toLocaleString('id-ID');
        document.getElementById('calc_nett').innerText = nett.toLocaleString('id-ID');
        document.getElementById('calc_dmin').innerText = dmin.toLocaleString('id-ID');

        // 7. SYNC DATA KE HIDDEN INPUT (PENTING!)
        // Pastikan ID ini ada di input hidden Anda
        document.getElementById('final_total_price').value = grandTotal;

        // Sinkronisasi Gross Weight (Penyebab Error 1364)
        const modGrossPcs = document.getElementById('mod_gross_pcs').value;
        document.getElementById('final_gross_pcs').value = modGrossPcs;

        const modGrossTotal = document.getElementById('mod_gross_total').value;
        document.getElementById('final_gross_total').value = modGrossTotal;

        // Sinkronisasi Volume & Net
        document.getElementById('final_vol_per_pcs').value = document.getElementById('mod_vol_pcs').value;
        document.getElementById('final_vol_total').value = document.getElementById('mod_vol_total').value;
        document.getElementById('final_net_weight_pcs').value = document.getElementById('mod_net_pcs').value;
        document.getElementById('final_total_net_weight').value = document.getElementById('mod_net_total').value;
    }

    // Tambahkan Event Listener Submit agar kalkulasi ulang tepat sebelum data terkirim
    document.getElementById('finalForm').addEventListener('submit', function(e) {
        calculatePrice();
    });

    function toStep2() {
        if (!document.getElementById('confirm_verify').checked) {
            alert("Harap konfirmasi kesesuaian data fisik!");
            return;
        }

        // Jalankan Kalkulasi Harga sebelum pindah
        calculatePrice();

        document.getElementById('content_step1').classList.add('hidden');
        document.getElementById('content_step2').classList.remove('hidden');

        document.getElementById('modalScrollArea').scrollTop = 0;

        const circle = document.getElementById('step2_circle');
        const text = document.getElementById('step2_text');
        circle.classList.replace('bg-slate-100', 'bg-blue-600');
        circle.classList.replace('text-slate-400', 'text-white');
        text.classList.replace('text-slate-400', 'text-blue-600');
    }

    function toStep1() {
        document.getElementById('content_step2').classList.add('hidden');
        document.getElementById('content_step1').classList.remove('hidden');

        document.getElementById('modalScrollArea').scrollTop = 0;

        const circle = document.getElementById('step2_circle');
        const text = document.getElementById('step2_text');
        circle.classList.replace('bg-blue-600', 'bg-slate-100');
        circle.classList.replace('text-white', 'text-slate-400');
        text.classList.replace('text-blue-600', 'text-slate-400');
    }
</script>

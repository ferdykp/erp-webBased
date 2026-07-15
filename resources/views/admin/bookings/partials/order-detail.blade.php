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

        {{-- <form action="{{ route('admin.bookings.store') }}" method="POST" id="finalForm"
            class="flex flex-col flex-1 overflow-hidden">
            @csrf --}}

        <form
            action="{{ isset($booking) ? route('admin.bookings.update', $booking->id) : route('admin.bookings.store') }}"
            method="POST" id="finalForm" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            @if (isset($booking))
                @method('PUT')
                <input type="hidden" name="status" value="{{ $booking->status }}">
            @endif
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
            <input type="hidden" name="density_gross" id="final_density_gross">
            <input type="hidden" name="density_nett" id="final_density_nett">
            {{-- Letakkan kode ini di dalam <form> yang ada di order-detail.blade.php --}}
            <input type="hidden" id="final_booking_code" name="booking_code" value="{{ $booking->booking_code }}">
            <input type="hidden" id="final_created_at" name="created_at"
                value="{{ $booking->created_at ? $booking->created_at->format('Y-m-d') : '' }}">


            <div id="modalScrollArea"
                class="flex-1 px-6 md:px-12 py-8 overflow-y-auto
                [&::-webkit-scrollbar]:w-2
                [&::-webkit-scrollbar-track]:bg-transparent
                [&::-webkit-scrollbar-thumb]:bg-slate-200
                [&::-webkit-scrollbar-thumb]:rounded-full">

                <div id="content_step1" class="space-y-8">
                    <div class="p-6 md:p-8 bg-blue-50/40 border border-blue-100 rounded-[2.5rem]">
                        <h4 class="mb-6 text-sm font-black tracking-widest uppercase text-slate-800">Data
                        </h4>
                        <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Product</p>
                                <p id="check_product_name" class="font-bold text-slate-800">-</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Qty</p>
                                <p class="font-bold text-slate-800"><span id="check_qty">0</span> <span
                                        id="check_unit" class="text-xs uppercase"></span></p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Dose</p>
                                <p class="font-bold text-emerald-600"><span id="check_dmin"></span> - <span
                                        id="check_dmax"></span> kGy</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Dimension</p>
                                <p id="check_dimension" class="font-bold text-slate-700">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Volume/Pcs
                                (cm³)</label>
                            <input type="text" id="mod_vol_pcs" readonly
                                class="w-full px-4 py-3 font-bold border-none bg-slate-50 rounded-xl text-slate-600 focus:ring-0">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Total Volume
                                (cm³)</label>
                            <input type="text" id="mod_vol_total" readonly
                                class="w-full px-4 py-3 font-bold border-none bg-slate-50 rounded-xl text-slate-600 focus:ring-0">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Net Weight/Pcs
                                (kg)</label>
                            <input type="text" id="mod_net_pcs" readonly
                                class="w-full px-4 py-3 font-bold border-none bg-slate-50 rounded-xl text-slate-600 focus:ring-0">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Total Net Weight
                                (kg)</label>
                            <input type="text" id="mod_net_total" readonly
                                class="w-full px-4 py-3 font-bold border-none bg-slate-50 rounded-xl text-slate-600 focus:ring-0">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Density Gross</label>
                            <input type="text" id="mod_density_gross" readonly
                                class="w-full px-4 py-3 font-bold border-none bg-slate-50 rounded-xl text-slate-600 focus:ring-0">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Density Nett</label>
                            <input type="text" id="mod_density_nett" readonly
                                class="w-full px-4 py-3 font-bold border-none bg-slate-50 rounded-xl text-slate-600 focus:ring-0">
                        </div>
                    </div>

                    <label
                        class="flex items-center gap-4 p-6 transition-all border-2 border-dashed cursor-pointer border-slate-100 rounded-3xl hover:border-blue-400">
                        <input type="checkbox" id="confirm_verify"
                            class="w-6 h-6 text-blue-600 rounded-lg focus:ring-0">
                        <span class="text-xs font-bold text-slate-500">I confirm the physical data is correct
                            with the above specifications.</span>
                    </label>

                    <button type="button" onclick="toStep2()"
                        class="w-full py-4 font-black tracking-widest text-white uppercase transition-all bg-blue-600 shadow-xl rounded-2xl hover:bg-blue-700 shadow-blue-100">
                        Next to Payment <i class="ml-2 fa-solid fa-arrow-right"></i>
                    </button>
                </div>

                <div id="content_step2" class="hidden space-y-6">
                    <div class="bg-white border-2 border-slate-100 rounded-[2.5rem] overflow-hidden shadow-sm">
                        <div class="flex items-center justify-between px-8 py-4 border-b bg-slate-50 border-slate-100">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Detail
                                Price</span>
                            <span class="text-[10px] font-bold py-1 px-3 bg-blue-100 text-blue-700 rounded-full">Rp 500
                                / dose*kg</span>
                        </div>

                        <div class="p-8 space-y-4">
                            <div class="flex items-start justify-between text-slate-600">
                                <div>
                                    <p class="text-xs font-bold tracking-tighter uppercase">Base Price (Subtotal)</p>
                                    <p class="text-[10px] text-slate-400 italic">500 × <span id="calc_qty">0</span>
                                        qty × <span id="calc_nett">0</span> kg × <span id="calc_dmin">0</span> kGy</p>
                                </div>
                                <span class="font-bold" id="display_subtotal">Rp 0</span>
                            </div>

                            <div class="flex items-center justify-between text-slate-600">
                                <p class="text-xs font-bold tracking-tighter uppercase text-slate-400">Tax (PPN 11%)
                                </p>
                                <span class="font-bold text-slate-400" id="display_tax">Rp 0</span>
                            </div>

                            <div
                                class="flex items-center justify-between pt-4 border-t-2 border-dashed border-slate-100">
                                <p class="text-sm font-black tracking-widest uppercase text-slate-800">Total Price</p>
                                <h4 class="text-3xl font-black text-blue-600" id="display_total_price">Rp 0</h4>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border bg-slate-50 rounded-3xl border-slate-100">
                        <label
                            class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-4 block text-center">Use
                            Tax (PPN11%) or not?</label>
                        <div class="flex gap-4">
                            <label
                                class="flex-1 relative flex items-center justify-center p-3 border-2 bg-white rounded-2xl cursor-pointer has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 transition-all shadow-sm">
                                <input type="radio" name="use_ppn" value="0" checked
                                    onchange="calculatePrice()" class="absolute opacity-0">
                                <span class="text-sm font-black text-slate-500">Without Tax</span>
                            </label>
                            <label
                                class="flex-1 relative flex items-center justify-center p-3 border-2 bg-white rounded-2xl cursor-pointer has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 transition-all shadow-sm">
                                <input type="radio" name="use_ppn" value="0.11" onchange="calculatePrice()"
                                    class="absolute opacity-0">
                                <span class="text-sm font-black text-slate-500">Tax (PPN 11%)</span>
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
                                <h4 class="text-sm font-black tracking-tight uppercase text-slate-800">
                                    Payment Status</h4>
                                <p class="text-[10px] text-slate-500">Specify the invoice status of this order.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <label
                                class="relative flex items-center gap-3 p-4 border-2 bg-white rounded-2xl cursor-pointer hover:border-blue-500 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="payment_status" value="unpaid" checked
                                    class="absolute opacity-0">
                                <i class="fa-solid fa-clock text-slate-400"></i>
                                <div class="flex flex-col">
                                    <span class="font-black text-slate-800 uppercase text-[10px]">Pay Later</span>
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
                                    <span class="font-black text-slate-800 uppercase text-[10px]">Payment Done</span>
                                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">Paid
                                        Off</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 pt-4 md:flex-row">
                        <button type="button" onclick="toStep1()"
                            class="flex-1 py-4 font-bold tracking-widest uppercase transition-all bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200">Back</button>
                        <button type="submit"
                            class="flex-[2] py-4 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-100 transition-all">Submit
                            Order</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    /**
     * Helper: Menghapus nol berlebih di belakang koma agar tampilan rapi
     */
    function formatNum(num, decimals = 4) {
        if (!num) return "0";
        return parseFloat(parseFloat(num).toFixed(decimals)).toString();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const existingCode = "{{ $booking->booking_code ?? '' }}";

        if (existingCode !== "") {
            // Jika mode EDIT: Gunakan kode yang sudah ada, jangan generate baru
            if (document.getElementById('display_booking_code_input')) {
                document.getElementById('display_booking_code_input').value = existingCode;
            }
            if (document.getElementById('display_booking_code')) {
                document.getElementById('display_booking_code').innerText = existingCode;
            }
        } else {
            // Jika mode CREATE: Jalankan generator
            generateBookingCode();
        }
    });

    function generateBookingCode() {
        const now = new Date();
        const year = now.getFullYear().toString().slice(-2);
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const day = now.getDate().toString().padStart(2, '0');
        const randomSeq = Math.floor(Math.random() * 900 + 100);
        const code = `${year}${month}${day}${randomSeq}`;

        if (document.getElementById('display_booking_code_input')) {
            document.getElementById('display_booking_code_input').value = code;
        }
        if (document.getElementById('display_booking_code')) {
            document.getElementById('display_booking_code').innerText = code;
        }
    }

    function openVerifyModal() {
        // Ambil Input Utama
        const customer = document.getElementById('in_customer_id');
        const prodName = document.getElementById('in_product_name');
        const qtyInput = document.getElementById('in_qty');

        if (!customer.value || !prodName.value || !qtyInput.value) {
            alert("Harap isi Nama Customer, Nama Produk, dan Quantity.");
            return;
        }

        const val = {
            customer_id: customer.value,
            product: prodName.value,
            type: document.getElementById('in_product_type').value || '-',
            qty: parseFloat(qtyInput.value) || 0,
            unit: document.getElementById('in_unit').value,
            dmin: parseFloat(document.getElementById('in_dmin').value) || 0,
            dmax: parseFloat(document.getElementById('in_dmax').value),
            net_pcs: parseFloat(document.getElementById('in_net_pcs').value) || 0,
            gross_pcs: parseFloat(document.getElementById('in_gross_pcs').value) || 0,
            l: parseFloat(document.getElementById('in_length').value) || 0,
            w: parseFloat(document.getElementById('in_width').value) || 0,
            h: parseFloat(document.getElementById('in_height').value) || 0,
            temp: document.getElementById('in_temp').value || '-'
        };

        // Kalkulasi Fisik (DALAM CM)
        const vol_pcs = val.l * val.w * val.h;
        const vol_total = vol_pcs * val.qty;
        const net_total = val.net_pcs * val.qty;
        const gross_total = val.gross_pcs * val.qty;

        // Tampilkan Ringkasan
        document.getElementById('check_product_name').innerText = val.product;
        document.getElementById('check_qty').innerText = formatNum(val.qty, 0);
        document.getElementById('check_unit').innerText = val.unit;
        document.getElementById('check_dmin').innerText = formatNum(val.dmin, 2);
        document.getElementById('check_dmax').innerText = formatNum(val.dmax, 2);
        document.getElementById('check_dimension').innerText = `${val.l}x${val.w}x${val.h} cm`;

        // Isi Input Review (Readonly)
        document.getElementById('mod_vol_pcs').value = formatNum(vol_pcs, 2);
        document.getElementById('mod_vol_total').value = formatNum(vol_total, 2);
        document.getElementById('mod_net_pcs').value = formatNum(val.net_pcs, 2);
        document.getElementById('mod_net_total').value = formatNum(net_total, 2);

        const dNett = vol_total > 0 ? (net_total / vol_total) : 0;
        const dGross = vol_total > 0 ? (gross_total / vol_total) : 0;
        document.getElementById('mod_density_nett').value = formatNum(dNett, 6);
        document.getElementById('mod_density_gross').value = formatNum(dGross, 6);

        // =========================================================================
        // FIX SINKRONISASI TANGGAL & BOOKING CODE KE DALAM HIDDEN INPUT FORM MODAL
        // =========================================================================
        const inputBookingCode = document.getElementById('in_booking_code');
        if (inputBookingCode) {
            // Ambil elemen hidden input modal baik menggunakan ID maupun Name untuk keandalan penuh
            const targetCodeField = document.getElementById('final_booking_code') || document.querySelector(
                '#finalForm input[name="booking_code"]');
            if (targetCodeField) {
                targetCodeField.value = inputBookingCode.value;
            }
            if (document.getElementById('display_booking_code')) {
                document.getElementById('display_booking_code').innerText = inputBookingCode.value;
            }
        }

        const inputCreatedAt = document.getElementById('in_created_at');
        if (inputCreatedAt) {
            // Targetkan hidden input created_at di form verifikasi modal
            const targetDateField = document.getElementById('final_created_at') || document.querySelector(
                '#finalForm input[name="created_at"]');
            if (targetDateField) {
                targetDateField.value = inputCreatedAt.value;
            }
        }
        // =========================================================================
        // Sync ke Hidden Inputs Bawaan Lainnya
        document.getElementById('final_customer_id').value = val.customer_id;
        document.getElementById('final_product_name').value = val.product;
        document.getElementById('final_product_type').value = val.type;
        document.getElementById('final_qty').value = val.qty;
        document.getElementById('final_unit').value = val.unit;
        document.getElementById('final_dmin').value = val.dmin;
        document.getElementById('final_dmax').value = val.dmax;
        document.getElementById('final_dim_pack').value = `${val.l}x${val.w}x${val.h}`;
        document.getElementById('final_temp').value = val.temp;
        document.getElementById('final_vol_per_pcs').value = vol_pcs;
        document.getElementById('final_vol_total').value = vol_total;
        document.getElementById('final_net_weight_pcs').value = val.net_pcs;
        document.getElementById('final_total_net_weight').value = net_total;
        document.getElementById('final_gross_pcs').value = val.gross_pcs;
        document.getElementById('final_gross_total').value = gross_total;
        document.getElementById('final_density_gross').value = dGross;
        document.getElementById('final_density_nett').value = dNett;

        calculatePrice();

        const modal = document.getElementById('orderDetailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function calculatePrice() {
        const qty = parseFloat(document.getElementById('final_qty').value) || 0;
        const nettPcs = parseFloat(document.getElementById('final_net_weight_pcs').value) || 0;
        const dmin = parseFloat(document.getElementById('final_dmin').value) || 0;

        const subtotal = 500 * qty * nettPcs * dmin;
        const ppnRate = parseFloat(document.querySelector('input[name="use_ppn"]:checked').value);
        const tax = subtotal * ppnRate;
        const total = subtotal + tax;

        // UI Display
        const idr = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        });
        document.getElementById('display_subtotal').innerText = idr.format(subtotal);
        document.getElementById('display_tax').innerText = idr.format(tax);
        document.getElementById('display_total_price').innerText = idr.format(total);

        // Update Rumus
        document.getElementById('calc_qty').innerText = formatNum(qty, 0);
        document.getElementById('calc_nett').innerText = formatNum(nettPcs, 2);
        document.getElementById('calc_dmin').innerText = formatNum(dmin, 2);

        document.getElementById('final_total_price').value = total.toFixed(2);
    }

    function toStep2() {
        if (!document.getElementById('confirm_verify').checked) {
            alert("Harap centang konfirmasi data!");
            return;
        }
        calculatePrice();
        document.getElementById('content_step1').classList.add('hidden');
        document.getElementById('content_step2').classList.remove('hidden');

        document.getElementById('step2_circle').classList.replace('bg-slate-100', 'bg-blue-600');
        document.getElementById('step2_circle').classList.replace('text-slate-400', 'text-white');
        document.getElementById('step2_text').classList.replace('text-slate-400', 'text-blue-600');
    }

    function toStep1() {
        document.getElementById('content_step2').classList.add('hidden');
        document.getElementById('content_step1').classList.remove('hidden');

        document.getElementById('step2_circle').classList.replace('bg-blue-600', 'bg-slate-100');
        document.getElementById('step2_circle').classList.replace('text-white', 'text-slate-400');
        document.getElementById('step2_text').classList.replace('text-blue-600', 'text-slate-400');
    }

    function closeOrderDetailModal() {
        document.getElementById('orderDetailModal').classList.add('hidden');
        document.getElementById('orderDetailModal').classList.remove('flex');
    }
</script>

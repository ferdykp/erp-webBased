@extends('admin.layout.app')

@section('title', 'Make Order')

@section('content')
    <div class="w-full space-y-6">
        <div class="flex flex-col gap-2 mb-4">
            <h2 class="text-3xl font-black tracking-tight text-gray-900">
                Admin <span class="text-blue-600">Manual Booking</span>
            </h2>
            <p class="text-sm font-medium text-gray-400">Silakan isi detail produk untuk kalkulasi otomatis.</p>
        </div>

        <div class="bg-white shadow-sm border border-gray-100 rounded-[2rem] overflow-hidden">
            <div class="p-8">
                <form id="mainBookingForm" class="space-y-8">
                    @csrf
                    {{-- SELECT CUSTOMER --}}
                    <div class="p-6 space-y-3 border border-blue-100 bg-blue-50/50 rounded-3xl">
                        <label class="text-[11px] font-black text-blue-600 uppercase tracking-widest ml-1">Customer
                            Owner</label>
                        <select id="in_customer_id"
                            class="w-full px-4 py-3.5 bg-white border border-blue-100 rounded-2xl font-bold text-gray-700">
                            <option value="" disabled selected>Pilih Customer...</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->contacts->first()->name }}
                                    ({{ $customer->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Product
                                Name</label>
                            <input type="text" id="in_product_name"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Product
                                Type</label>
                            <input type="text" id="in_product_type"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Dose Range
                                (kGy)</label>
                            <div class="flex gap-4">
                                <input type="number" id="in_dmin" placeholder="Min"
                                    class="w-1/2 px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                                <input type="number" id="in_dmax" placeholder="Max"
                                    class="w-1/2 px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Net Weight /
                                Pcs (kg)</label>
                            <input type="number" step="0.01" id="in_net_pcs"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Dimension (P
                                x L x T) cm</label>
                            <div class="flex items-center space-x-2">
                                <input type="number" id="in_length" placeholder="P"
                                    class="w-1/3 px-2 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-center font-bold">
                                <input type="number" id="in_width" placeholder="L"
                                    class="w-1/3 px-2 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-center font-bold">
                                <input type="number" id="in_height" placeholder="T"
                                    class="w-1/3 px-2 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-center font-bold">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Gross Weight
                                / Pcs (kg)</label>
                            <input type="number" step="0.01" id="in_gross_pcs"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <div class="space-y-2">
                            <label
                                class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Quantity</label>
                            <input type="number" id="in_qty"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Unit</label>
                            <select id="in_unit"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                                <option value="box">BOX / DUS</option>
                                <option value="sack">SACK</option>
                                <option value="drum">DRUM</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Temp
                                Req.</label>
                            <input type="text" id="in_temp" placeholder="None"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-8 border-t">
                        <a href="{{ route('admin.bookings') }}" class="text-sm font-bold text-gray-400">Batal</a>
                        <button type="button" onclick="openVerifyModal()"
                            class="px-10 py-4 text-sm font-black text-white transition-all bg-blue-600 shadow-xl rounded-2xl hover:bg-blue-700">
                            Review Order <i class="ml-2 fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('admin.bookings.partials.order-detail')

    <script>
        function openVerifyModal() {
            // Ambil Elemen
            const customer = document.getElementById('in_customer_id');
            const prodName = document.getElementById('in_product_name');
            const qty = document.getElementById('in_qty');

            // Validasi Dasar
            if (!customer.value || !prodName.value || !qty.value) {
                alert("Harap isi Nama Customer, Nama Produk, dan Quantity terlebih dahulu.");
                return;
            }

            // Ambil Nilai
            const val = {
                customer_id: customer.value,
                product: prodName.value,
                type: document.getElementById('in_product_type').value || '-',
                qty: parseFloat(qty.value) || 0,
                unit: document.getElementById('in_unit').value,
                dmin: document.getElementById('in_dmin').value || 0,
                dmax: document.getElementById('in_dmax').value || 0,
                net_pcs: parseFloat(document.getElementById('in_net_pcs').value) || 0,
                gross_pcs: parseFloat(document.getElementById('in_gross_pcs').value) || 0,
                l: parseFloat(document.getElementById('in_length').value) || 0,
                w: parseFloat(document.getElementById('in_width').value) || 0,
                h: parseFloat(document.getElementById('in_height').value) || 0,
                temp: document.getElementById('in_temp').value || '-'
            };

            // Kalkulasi
            const vol_pcs = (val.l * val.w * val.h) / 1000000;
            const vol_total = vol_pcs * val.qty;
            const net_total = val.net_pcs * val.qty;
            const gross_total = val.gross_pcs * val.qty;

            // Isi Data ke Modal (Gunakan try-catch untuk debugging)
            try {
                document.getElementById('display_booking_code').innerText = 'DRAFT-' + Math.floor(Math.random() * 9000 +
                    1000);
                document.getElementById('check_product_name').innerText = val.product;
                document.getElementById('check_qty').innerText = val.qty;
                document.getElementById('check_unit').innerText = val.unit;
                document.getElementById('check_dmin').innerText = val.dmin;
                document.getElementById('check_dmax').innerText = val.dmax;
                document.getElementById('check_dimension').innerText = `${val.l}x${val.w}x${val.h} cm`;

                // Input fields di modal
                document.getElementById('mod_vol_pcs').value = vol_pcs.toFixed(6);
                document.getElementById('mod_vol_total').value = vol_total.toFixed(6);
                document.getElementById('mod_net_pcs').value = val.net_pcs;
                document.getElementById('mod_net_total').value = net_total.toFixed(2);
                document.getElementById('mod_gross_pcs').value = val.gross_pcs;
                document.getElementById('mod_gross_total').value = gross_total.toFixed(2);

                // Hidden inputs untuk submit
                document.getElementById('final_customer_id').value = val.customer_id;
                document.getElementById('final_product_name').value = val.product;
                document.getElementById('final_product_type').value = val.type;
                document.getElementById('final_qty').value = val.qty;
                document.getElementById('final_unit').value = val.unit;
                document.getElementById('final_dmin').value = val.dmin;
                document.getElementById('final_dmax').value = val.dmax;
                document.getElementById('final_dim_pack').value = `${val.l}x${val.w}x${val.h}`;
                document.getElementById('final_temp').value = val.temp;

                // Show Modal
                const modal = document.getElementById('orderDetailModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } catch (e) {
                console.error("Kesalahan sinkronisasi ID elemen:", e);
            }
        }

        function closeOrderDetailModal() {
            const modal = document.getElementById('orderDetailModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
@endsection

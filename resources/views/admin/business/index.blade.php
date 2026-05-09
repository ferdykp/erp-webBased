@extends('admin.layout.app')

@section('title', 'Customer Monitoring')

@section('content')
    <div class="w-full pb-10 space-y-6 md:space-y-8" x-data="{ openModal: false, selectedCustomer: {} }">

        {{-- HEADER SECTION --}}
        <div class="flex flex-col justify-between gap-6 px-2 lg:flex-row lg:items-center">
            <div>
                <h2 class="text-2xl font-black tracking-tight text-center md:text-3xl text-slate-800 lg:text-left">Customer
                    Monitoring</h2>
                <p class="text-xs font-medium text-center md:text-sm text-slate-500 lg:text-left">Nuctech ERP - Business
                    Management System</p>
            </div>

            <form action="{{ route('admin.business.index') }}" method="GET" class="relative w-full group lg:w-auto">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search company or PIC..."
                    class="w-full py-3.5 pl-12 pr-4 text-sm transition-all bg-white border outline-none lg:w-80 border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 shadow-sm">
                <i
                    class="absolute transition-colors -translate-y-1/2 fa-solid fa-magnifying-glass left-5 top-1/2 text-slate-400 group-focus-within:text-indigo-500"></i>
            </form>
        </div>

        {{-- TABLE CARD --}}
        <div class="bg-white border border-slate-100 rounded-[2rem] md:rounded-[2.5rem] overflow-hidden shadow-sm">
            <div class="overflow-x-auto overflow-y-hidden">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead
                        class="bg-slate-50/50 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-50">
                        <tr>
                            <th class="px-6 py-5 md:px-8">Company Info</th>
                            <th class="px-6 py-5">PIC Detail</th>
                            <th class="px-6 py-5 text-center">Status</th>
                            <th class="px-6 py-5 text-right md:px-8">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($customers as $customer)
                            <tr class="transition-colors hover:bg-slate-50/80 group">
                                <td class="px-6 py-5 md:px-8">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-sm font-bold md:text-base text-slate-800">{{ $customer->company_name ?? 'Unnamed Company' }}</span>
                                        <span class="text-[11px] text-slate-400 mt-0.5">{{ $customer->email }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-sm font-bold text-slate-600">
                                        {{ $customer->contacts->first()?->name ?? 'No PIC' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 flex items-center gap-1.5 mt-1">
                                        <i class="fa-solid fa-phone text-[9px]"></i>
                                        {{ $customer->contacts->first()?->phone ?? 'No Phone' }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="{{ $customer->status === 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-tight">
                                        {{ $customer->status ?? 'pending' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right md:px-8">
                                    <button @click="openModal = true; selectedCustomer = {{ json_encode($customer) }}"
                                        class="px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all bg-white border shadow-sm border-slate-200 hover:border-indigo-500 hover:text-indigo-600 rounded-xl active:scale-95">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-slate-50 bg-slate-50/30">
                {{ $customers->links() }}
            </div>
        </div>

        {{-- MODAL DETAIL (Responsive Design) --}}
        <div x-show="openModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4 bg-slate-900/60 backdrop-blur-sm"
            x-cloak>

            <div @click.away="openModal = false" x-show="openModal"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="translate-y-0 sm:scale-100"
                class="bg-slate-50 w-full max-w-5xl h-[90vh] sm:h-auto max-h-[95vh] overflow-y-auto rounded-t-[2.5rem] sm:rounded-[3rem] shadow-2xl border-t sm:border border-white">

                {{-- MODAL HEADER --}}
                <div
                    class="sticky top-0 z-20 flex items-center justify-between p-6 border-b md:p-8 bg-white/80 backdrop-blur-md border-slate-100">
                    <div class="flex items-center gap-3 text-left md:gap-4">
                        <div
                            class="flex items-center justify-center w-10 h-10 text-white bg-indigo-500 shadow-lg md:w-12 md:h-12 rounded-2xl">
                            <i class="text-lg md:text-xl fa-building fa-solid"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black leading-tight md:text-xl text-slate-800"
                                x-text="selectedCustomer.company_name"></h3>
                            <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest"
                                x-text="'ID: #' + selectedCustomer.id"></p>
                        </div>
                    </div>
                    <button @click="openModal = false"
                        class="flex items-center justify-center w-10 h-10 transition-all rounded-full hover:bg-rose-50 hover:text-rose-500 text-slate-300">
                        <i class="text-xl fa-solid fa-circle-xmark"></i>
                    </button>
                </div>

                {{-- MODAL BODY --}}
                <div class="p-6 space-y-6 md:p-8 md:space-y-8">
                    <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div
                            class="lg:col-span-2 bg-white p-6 md:p-8 rounded-[2rem] border border-slate-100 shadow-sm space-y-6">
                            <h4
                                class="text-[10px] font-black text-indigo-500 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 bg-indigo-500 rounded-full"></span> Company Profile
                            </h4>
                            <div class="grid grid-cols-1 gap-6 text-left sm:grid-cols-2">
                                <div class="space-y-1">
                                    <label
                                        class="text-[10px] text-slate-400 font-black uppercase block tracking-tighter">PIC
                                        Name</label>
                                    <p class="text-sm font-bold text-slate-700"
                                        x-text="selectedCustomer.contacts?.[0]?.name || '-'"></p>
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="text-[10px] text-slate-400 font-black uppercase block tracking-tighter">Email
                                        Address</label>
                                    <p class="text-sm font-bold break-all text-slate-700" x-text="selectedCustomer.email">
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="text-[10px] text-slate-400 font-black uppercase block tracking-tighter">Phone
                                        Number</label>
                                    <p class="text-sm font-bold text-slate-700"
                                        x-text="selectedCustomer.contacts?.[0]?.phone || '-'"></p>
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="text-[10px] text-slate-400 font-black uppercase block tracking-tighter">Status</label>
                                    <span
                                        :class="selectedCustomer.profile_completed ? 'text-emerald-600 bg-emerald-50' :
                                            'text-amber-600 bg-amber-50'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-black uppercase">
                                        <i :class="selectedCustomer.profile_completed ? 'fa-circle-check' : 'fa-circle-exclamation'"
                                            class="fa-solid"></i>
                                        <span
                                            x-text="selectedCustomer.profile_completed ? 'Verified' : 'Incomplete'"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-indigo-600 p-6 md:p-8 rounded-[2rem] shadow-xl shadow-indigo-100 text-white relative overflow-hidden flex flex-col justify-between min-h-[160px]">
                            <div class="relative z-10">
                                <h4 class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.2em] mb-4">
                                    Registered Office</h4>
                                <p class="text-sm italic font-medium leading-relaxed"
                                    x-text="selectedCustomer.address || 'No address provided yet.'"></p>
                            </div>
                            <i
                                class="absolute bottom-[-10px] right-[-10px] text-6xl fa-solid fa-map-location-dot opacity-10"></i>
                        </div>
                    </section>

                    {{-- PRODUCT TABLE INSIDE MODAL --}}
                    <section class="bg-white p-6 md:p-8 rounded-[2rem] border border-slate-100 shadow-sm">
                        <h4
                            class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Product History
                        </h4>

                        <div class="-mx-6 overflow-x-auto md:mx-0">
                            <table class="w-full text-sm text-left min-w-[600px]">
                                <thead class="text-[10px] font-black uppercase text-slate-400 border-b border-slate-50">
                                    <tr>
                                        <th class="px-6 py-4">Product Info</th>
                                        <th class="px-4 py-4 text-center">Qty</th>
                                        <th class="px-4 py-4 text-center">Status</th>
                                        <th class="px-6 py-4 text-right">Dosage Request</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <template x-for="booking in selectedCustomer.bookings" :key="booking.id">
                                        <template x-for="product in booking.products" :key="product.id">
                                            <tr class="transition-colors hover:bg-slate-50/50">
                                                <td class="px-6 py-4">
                                                    <div class="font-bold text-slate-700" x-text="product.product_name">
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 font-medium"
                                                        x-text="'REF: ' + booking.booking_code"></div>
                                                </td>
                                                <td class="px-4 py-4 text-center">
                                                    <span
                                                        class="px-2.5 py-1 bg-slate-100 rounded-lg font-black text-slate-600 text-[11px]"
                                                        x-text="product.quantity + ' ' + (product.unit || 'Box')"></span>
                                                </td>
                                                <td class="px-4 py-4 text-center">
                                                    <span
                                                        class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter"
                                                        x-text="booking.status"></span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col items-end gap-1.5">
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="text-[8px] font-black text-slate-300 uppercase italic">Min</span>
                                                            <span
                                                                class="px-2 py-1 bg-slate-50 border border-slate-100 rounded-md text-[10px] font-black text-slate-700 min-w-[55px] text-center"
                                                                x-text="Number(product.dmin).toFixed(0) + ' kGy'"></span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="text-[8px] font-black text-slate-300 uppercase italic">Max</span>
                                                            <span
                                                                class="px-2 py-1 bg-slate-50 border border-slate-100 rounded-md text-[10px] font-black text-slate-700 min-w-[55px] text-center"
                                                                x-text="Number(product.dmax).toFixed(0) + ' kGy'"></span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection

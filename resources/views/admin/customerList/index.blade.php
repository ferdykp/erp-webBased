@extends('admin.layout.app')

@section('title', 'Customer List')

@section('content')
    <div class="w-full pb-10 space-y-6 md:space-y-8" x-data="{
        openProfile: false,
        openCreate: false,
        openEdit: false,
        openDelete: false,
        selectedCustomer: {}
    }">

        {{-- HEADER --}}
        <div class="flex flex-col justify-between gap-6 px-2 lg:flex-row lg:items-center">
            <div>
                <h2 class="text-2xl font-black tracking-tight md:text-3xl text-slate-800">Customer List</h2>
                <p class="text-xs font-medium md:text-sm text-slate-500">Nuctech ERP - Business Management System</p>
            </div>

            <div class="flex flex-col flex-1 max-w-2xl gap-3 sm:flex-row">
                <div class="relative flex-1 group">
                    <input type="text" id="search-input" name="search" value="{{ request('search') }}"
                        placeholder="Search company or PIC..."
                        class="w-full py-3 pl-12 pr-4 text-sm transition-all bg-white border outline-none border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500">
                    <i
                        class="absolute transition-colors -translate-y-1/2 fa-solid fa-magnifying-glass left-5 top-1/2 text-slate-400 group-focus-within:text-blue-500"></i>
                </div>
                @if (in_array(auth()->user()->role, ['superadmin']))
                    <button @click="openCreate = true"
                        class="flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold text-white transition-all bg-blue-600 shadow-lg rounded-2xl shadow-blue-200 hover:bg-blue-700 active:scale-95">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add Customer</span>
                    </button>
                @endif

            </div>
        </div>

        {{-- TABLE CARD --}}
        <div id="customer-data-container"
            class="bg-white border border-slate-100 rounded-[2rem] md:rounded-[2.5rem] overflow-hidden shadow-sm">
            @include('admin.customerList.table')
        </div>

        {{-- MODAL CREATE (Mobile Responsive) --}}
        <div x-show="openCreate" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-data="{
                step: 1,
                totalSteps: 2,
                validateAndNext() {
                    const currentStepEl = document.getElementById('step-' + this.step + '-content');
                    const inputs = currentStepEl.querySelectorAll('[required]');
                    let isValid = true;
                    inputs.forEach(input => {
                        if (!input.checkValidity()) {
                            input.reportValidity();
                            isValid = false;
                        }
                    });
                    if (isValid) this.step++;
                },
                prevStep() { if (this.step > 1) this.step-- }
            }"
            class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4 bg-slate-900/60 backdrop-blur-sm"
            x-cloak>

            <div @click.away="openCreate = false; step = 1" x-show="openCreate"
                x-transition:enter="transition ease-out duration-300 delay-100"
                x-transition:enter-start="opacity-0 translate-y-full sm:scale-95 sm:translate-y-8"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100 sm:translate-y-0"
                class="bg-white w-full max-w-2xl rounded-t-[2.5rem] sm:rounded-[2.5rem] shadow-2xl overflow-hidden max-h-[95vh] overflow-y-auto">

                {{-- STEP TRACKER --}}
                <div class="px-6 pt-10 pb-6 md:px-10 bg-slate-50/50">
                    <div class="relative flex items-center justify-between max-w-md mx-auto">
                        <div class="absolute top-5 left-0 w-full h-[2px] bg-slate-200 -z-0">
                            <div class="h-full transition-all duration-700 bg-blue-500"
                                :style="`width: ${step === 1 ? '0%' : '100%'}`"></div>
                        </div>

                        <div class="relative z-10 flex flex-col items-center">
                            <div :class="step >= 1 ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-200' :
                                'bg-white border-slate-200 text-slate-400'"
                                class="flex items-center justify-center w-10 h-10 transition-all duration-500 bg-white border-2 rounded-2xl">
                                <template x-if="step > 1"><i class="text-xs fa-solid fa-check"></i></template>
                                <template x-if="step === 1"><span class="text-xs font-black">01</span></template>
                            </div>
                        </div>

                        <div class="relative z-10 flex flex-col items-center">
                            <div :class="step >= 2 ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-200' :
                                'bg-white border-slate-200 text-slate-400'"
                                class="flex items-center justify-center w-10 h-10 transition-all duration-500 bg-white border-2 rounded-2xl">
                                <span class="text-xs font-black">02</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <h4 class="text-lg font-black text-slate-800"
                            x-text="step === 1 ? 'Company Information' : 'Contact Details'"></h4>
                        <p class="text-xs font-medium text-slate-400">Please fill in the required fields</p>
                    </div>
                </div>

                <form action="{{ route('admin.customerList.store') }}" method="POST" class="px-6 pb-10 md:px-10">
                    @csrf

                    {{-- STEP 1: COMPANY --}}
                    <div x-show="step === 1" id="step-1-content" x-transition:enter="transition duration-300"
                        class="mt-6 space-y-5">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Company
                                Name</label>
                            <input type="text" name="company_name" required placeholder="Legal company name"
                                class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all">
                        </div>
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Industry</label>
                                <input type="text" name="industry" placeholder="e.g. Logistics"
                                    class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Tax ID
                                    (NPWP)</label>
                                <input type="text" name="npwp" placeholder="00.000.000.0-000.000"
                                    class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Address</label>
                            <textarea name="address_line" required rows="3" placeholder="Full company address..."
                                class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all"></textarea>
                        </div>
                    </div>

                    {{-- STEP 2: PIC --}}
                    <div x-show="step === 2" id="step-2-content" x-transition:enter="transition duration-300"
                        class="mt-6 space-y-5 text-left">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">PIC
                                Name</label>
                            <input type="text" name="name" required placeholder="Legal full name"
                                class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all">
                        </div>
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-2 text-left">
                                <label
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Phone</label>
                                <input type="text" name="contact_phone" required placeholder="+62..."
                                    class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all">
                            </div>
                            <div class="space-y-2 text-left">
                                <label
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Email</label>
                                <input type="email" name="email" required placeholder="pic@company.com"
                                    class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER BUTTONS --}}
                    <div class="flex items-center justify-between pt-6 mt-10 border-t border-slate-50">
                        <button type="button" x-show="step > 1" @click="prevStep()"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-bold transition-colors text-slate-400 hover:text-slate-800">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        <div x-show="step === 1"></div>

                        <div class="flex items-center gap-3">
                            <button type="button" @click="openCreate = false; step = 1"
                                class="px-5 py-2.5 text-sm font-bold text-slate-400 hover:text-rose-500 transition-colors">Cancel</button>

                            <button type="button" x-show="step < totalSteps" @click="validateAndNext()"
                                class="px-8 py-3.5 text-xs font-black tracking-widest text-white uppercase bg-slate-900 rounded-2xl hover:bg-blue-600 transition-all active:scale-95 shadow-lg shadow-slate-200">
                                Next Step
                            </button>

                            <button type="submit" x-show="step === totalSteps"
                                class="px-8 py-3.5 text-xs font-black tracking-widest text-white uppercase bg-blue-600 rounded-2xl hover:bg-blue-700 transition-all active:scale-95 shadow-lg shadow-blue-200">
                                Finish Setup
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL PROFILE --}}
        {{-- MODAL PROFILE (Comprehensive Detail) --}}
        <div x-show="openProfile" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4 bg-slate-900/60 backdrop-blur-sm"
            x-cloak>

            <div @click.away="openProfile = false" x-show="openProfile"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="translate-y-0 sm:scale-100"
                class="bg-slate-50 w-full max-w-5xl h-[90vh] sm:h-auto max-h-[95vh] overflow-y-auto rounded-t-[2.5rem] sm:rounded-[3rem] shadow-2xl border-t sm:border border-white">

                {{-- MODAL HEADER --}}
                <div
                    class="sticky top-0 z-20 flex items-center justify-between p-6 border-b md:p-8 bg-white/80 backdrop-blur-md border-slate-100">
                    <div class="flex items-center gap-3 text-left md:gap-4">
                        <div
                            class="flex items-center justify-center w-10 h-10 text-white bg-blue-600 shadow-lg md:w-12 md:h-12 rounded-2xl">
                            <i class="text-lg md:text-xl fa-building fa-solid"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black leading-tight md:text-xl text-slate-800"
                                x-text="selectedCustomer.company_name"></h3>
                            <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest"
                                x-text="'CUSTOMER ID: #' + selectedCustomer.id"></p>
                        </div>
                    </div>
                    <button @click="openProfile = false"
                        class="flex items-center justify-center w-10 h-10 transition-all rounded-full hover:bg-rose-50 hover:text-rose-500 text-slate-300">
                        <i class="text-2xl fa-solid fa-circle-xmark"></i>
                    </button>
                </div>

                {{-- MODAL BODY --}}
                <div class="p-6 space-y-6 md:p-8 md:space-y-8">
                    <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {{-- LEFT COL: BASIC INFO --}}
                        <div
                            class="lg:col-span-2 bg-white p-6 md:p-8 rounded-[2rem] border border-slate-100 shadow-sm space-y-6">
                            <h4
                                class="text-[10px] font-black text-blue-600 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 bg-blue-600 rounded-full"></span> Company Profile
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
                                        class="text-[10px] text-slate-400 font-black uppercase block tracking-tighter">Account
                                        Status</label>
                                    <span
                                        :class="selectedCustomer.status === 'active' ? 'text-emerald-600 bg-emerald-50' :
                                            'text-amber-600 bg-amber-50'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-black uppercase">
                                        <i :class="selectedCustomer.status === 'active' ? 'fa-circle-check' :
                                            'fa-circle-exclamation'"
                                            class="fa-solid"></i>
                                        <span x-text="selectedCustomer.status"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT COL: ADDRESS CARD --}}
                        <div
                            class="bg-blue-600 p-6 md:p-8 rounded-[2rem] shadow-xl shadow-blue-100 text-white relative overflow-hidden flex flex-col justify-between min-h-[160px]">
                            <div class="relative z-10">
                                <h4 class="text-[10px] font-black text-blue-200 uppercase tracking-[0.2em] mb-4">Registered
                                    Office</h4>
                                <p class="text-sm italic font-medium leading-relaxed"
                                    x-text="selectedCustomer.addresses?.[0]?.address_line || 'No address provided yet.'">
                                </p>
                            </div>
                            <i
                                class="absolute bottom-[-10px] right-[-10px] text-6xl fa-solid fa-map-location-dot opacity-10"></i>
                        </div>
                    </section>

                    {{-- PRODUCT HISTORY TABLE --}}
                    <section class="bg-white p-6 md:p-8 rounded-[2rem] border border-slate-100 shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <h4
                                class="text-[10px] font-black text-emerald-500 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Product History
                            </h4>
                        </div>

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
                                    {{-- Loop through bookings and their products --}}
                                    <template x-if="selectedCustomer.bookings && selectedCustomer.bookings.length > 0">
                                        <template x-for="booking in selectedCustomer.bookings" :key="booking.id">
                                            <template x-for="product in booking.products" :key="product.id">
                                                <tr class="transition-colors hover:bg-slate-50/50">
                                                    <td class="px-6 py-4">
                                                        <div class="font-bold text-slate-700"
                                                            x-text="product.product_name"></div>
                                                        <div class="text-[10px] text-slate-400 font-medium"
                                                            x-text="'REF: ' + (booking.booking_code || 'N/A')"></div>
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
                                                                    x-text="(product.dmin ? Number(product.dmin).toFixed(0) : '0') + ' kGy'"></span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <span
                                                                    class="text-[8px] font-black text-slate-300 uppercase italic">Max</span>
                                                                <span
                                                                    class="px-2 py-1 bg-slate-50 border border-slate-100 rounded-md text-[10px] font-black text-slate-700 min-w-[55px] text-center"
                                                                    x-text="(product.dmax ? Number(product.dmax).toFixed(0) : '0') + ' kGy'"></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                        </template>
                                    </template>

                                    {{-- Empty State for History --}}
                                    <template x-if="!selectedCustomer.bookings || selectedCustomer.bookings.length === 0">
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 italic text-center text-slate-400">
                                                No product history found for this customer.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        {{-- MODAL EDIT --}}
        {{-- MODAL EDIT (Step-by-Step matching Create Modal) --}}
        <div x-show="openEdit" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-data="{
                editStep: 1,
                totalSteps: 2,
                validateAndNext() {
                    this.editStep++;
                },
                prevStep() { if (this.editStep > 1) this.editStep-- }
            }"
            class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4 bg-slate-900/60 backdrop-blur-sm"
            x-cloak>

            <div @click.away="openEdit = false; editStep = 1" x-show="openEdit"
                x-transition:enter="transition ease-out duration-300 delay-100"
                x-transition:enter-start="opacity-0 translate-y-full sm:scale-95 sm:translate-y-8"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100 sm:translate-y-0"
                class="bg-white w-full max-w-2xl rounded-t-[2.5rem] sm:rounded-[2.5rem] shadow-2xl overflow-hidden max-h-[95vh] overflow-y-auto">

                {{-- STEP TRACKER --}}
                <div class="px-6 pt-10 pb-6 md:px-10 bg-slate-50/50">
                    <div class="relative flex items-center justify-between max-w-md mx-auto">
                        <div class="absolute top-5 left-0 w-full h-[2px] bg-slate-200 -z-0">
                            <div class="h-full transition-all duration-700 bg-blue-500"
                                :style="`width: ${editStep === 1 ? '0%' : '100%'}`"></div>
                        </div>

                        <div class="relative z-10 flex flex-col items-center">
                            <div :class="editStep >= 1 ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-200' :
                                'bg-white border-slate-200 text-slate-400'"
                                class="flex items-center justify-center w-10 h-10 transition-all duration-500 bg-white border-2 rounded-2xl">
                                <template x-if="editStep > 1"><i class="text-xs fa-solid fa-check"></i></template>
                                <template x-if="editStep === 1"><span class="text-xs font-black">01</span></template>
                            </div>
                        </div>

                        <div class="relative z-10 flex flex-col items-center">
                            <div :class="editStep >= 2 ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-200' :
                                'bg-white border-slate-200 text-slate-400'"
                                class="flex items-center justify-center w-10 h-10 transition-all duration-500 bg-white border-2 rounded-2xl">
                                <span class="text-xs font-black">02</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <h4 class="text-lg font-black text-slate-800"
                            x-text="editStep === 1 ? 'Update Company Info' : 'Update Contact Details'"></h4>
                        <p class="text-xs font-medium text-slate-400">Modify the fields you wish to change</p>
                    </div>
                </div>

                <form :action="`/admin/customerList/${selectedCustomer.id}`" method="POST" class="px-6 pb-10 md:px-10">
                    @csrf
                    @method('PUT')

                    {{-- STEP 1: COMPANY --}}
                    <div x-show="editStep === 1" x-transition:enter="transition duration-300" class="mt-6 space-y-5">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Company
                                Name</label>
                            <input type="text" name="company_name" x-model="selectedCustomer.company_name" required
                                class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all">
                        </div>
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Industry</label>
                                <input type="text" name="industry" x-model="selectedCustomer.industry"
                                    class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Tax ID
                                    (NPWP)</label>
                                <input type="text" name="npwp" x-model="selectedCustomer.npwp"
                                    class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all">
                            </div>
                        </div>
                        <div class="space-y-2 text-left">
                            <label
                                class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Address</label>
                            <textarea name="address_line" required rows="3"
                                x-model="selectedCustomer.addresses && selectedCustomer.addresses.length > 0 ? selectedCustomer.addresses[0].address_line : ''"
                                class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all"></textarea>
                        </div>
                    </div>

                    {{-- STEP 2: PIC (Update ini agar sinkron dengan Controller updateAdmin) --}}
                    <div x-show="editStep === 2" x-transition:enter="transition duration-300"
                        class="mt-6 space-y-5 text-left">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">PIC
                                Name</label>

                            {{-- GANTI name="name" MENJADI name="contact_name" --}}
                            <input type="text" name="contact_name" required
                                :value="selectedCustomer.contacts && selectedCustomer.contacts.length > 0 ? selectedCustomer
                                    .contacts[0].name : ''"
                                class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all">
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-2 text-left">
                                <label
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Phone</label>

                                {{-- PASTIKAN name="contact_phone" --}}
                                <input type="text" name="contact_phone" required
                                    :value="selectedCustomer.contacts && selectedCustomer.contacts.length > 0 ? selectedCustomer
                                        .contacts[0].phone : ''"
                                    class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all">
                            </div>

                            <div class="space-y-2 text-left">
                                <label
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Email</label>
                                <input type="email" name="email" required x-model="selectedCustomer.email"
                                    class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-transparent rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border focus:bg-white outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER BUTTONS --}}
                    <div class="flex items-center justify-between pt-6 mt-10 border-t border-slate-50">
                        <button type="button" x-show="editStep > 1" @click="prevStep()"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-bold transition-colors text-slate-400 hover:text-slate-800">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        <div x-show="editStep === 1"></div>

                        <div class="flex items-center gap-3">
                            <button type="button" @click="openEdit = false; editStep = 1"
                                class="px-5 py-2.5 text-sm font-bold text-slate-400 hover:text-rose-500 transition-colors">Discard</button>

                            <button type="button" x-show="editStep < totalSteps" @click="validateAndNext()"
                                class="px-8 py-3.5 text-xs font-black tracking-widest text-white uppercase bg-slate-900 rounded-2xl hover:bg-blue-600 transition-all active:scale-95 shadow-lg shadow-slate-200">
                                Next Step
                            </button>

                            <button type="submit" x-show="editStep === totalSteps"
                                class="px-8 py-3.5 text-xs font-black tracking-widest text-white uppercase bg-blue-600 rounded-2xl hover:bg-blue-700 transition-all active:scale-95 shadow-lg shadow-blue-200">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL DELETE CONFIRMATION --}}
        <div x-show="openDelete" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>

            <div @click.away="openDelete = false" x-show="openDelete"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden p-8 text-center">

                {{-- ICON --}}
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 bg-rose-50 rounded-3xl">
                    <i class="text-3xl fa-solid fa-triangle-exclamation text-rose-500"></i>
                </div>

                {{-- TEXT --}}
                <h3 class="mb-2 text-xl font-black text-slate-800">Delete Customer?</h3>
                <p class="mb-8 text-sm font-medium leading-relaxed text-slate-500">
                    Are you sure you want to delete <span class="font-bold text-slate-800"
                        x-text="selectedCustomer.company_name"></span>?
                    This action cannot be undone and all associated data will be removed.
                </p>

                {{-- ACTIONS --}}
                <div class="flex flex-col gap-3 sm:flex-row">
                    <button @click="openDelete = false"
                        class="flex-1 px-6 py-4 text-sm font-bold transition-all bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200 active:scale-95">
                        Keep Customer
                    </button>

                    <form :action="`/admin/customerList/${selectedCustomer.id}`" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full px-6 py-4 text-sm font-bold text-white transition-all shadow-lg bg-rose-500 rounded-2xl hover:bg-rose-600 active:scale-95 shadow-rose-200">
                            Yes, Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div> {{-- AKHIR DARI DIV X-DATA UTAMA --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const container = document.getElementById('customer-data-container');

            const fetchCustomers = (searchValue = '', url = "{{ route('admin.customerList.index') }}") => {
                const fetchUrl = new URL(url);
                if (searchValue) fetchUrl.searchParams.set('search', searchValue);

                container.style.opacity = '0.5';

                fetch(fetchUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        container.innerHTML = html;
                        container.style.opacity = '1';

                        // RE-INITIALIZE ALPINE
                        if (window.Alpine) {
                            window.Alpine.discoverUninitializedComponents((el) => {
                                window.Alpine.initializeComponent(el);
                            });
                        }
                        window.history.pushState({}, '', fetchUrl);
                    });
            };

            let timeout = null;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => fetchCustomers(this.value), 500);
            });

            container.addEventListener('click', function(e) {
                const link = e.target.closest('.pagination a, [role="navigation"] a');
                if (link) {
                    e.preventDefault();
                    fetchCustomers(searchInput.value, link.href);
                    container.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
@endsection

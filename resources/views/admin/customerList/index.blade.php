@extends('admin.layout.app')

@section('title', 'Customer List')

@section('content')
    <div class="w-full pb-10 space-y-8" x-data="{
        openProfile: false,
        openCreate: false,
        openEdit: false,
        selectedCustomer: {}
    }">

        {{-- HEADER --}}
        <div class="flex flex-col justify-between gap-4 px-2 md:flex-row md:items-center">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-slate-800">Customer Management</h2>
                <p class="text-sm text-slate-500">Nuctech ERP - Business Management System</p>
            </div>

            <form action="{{ route('admin.customerList.index') }}" method="GET" class="relative group">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search company or PIC..."
                    class="w-full py-3 pl-12 pr-4 text-sm transition-all bg-white border outline-none md:w-80 border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500">
                <i class="absolute -translate-y-1/2 fa-solid fa-magnifying-glass left-5 top-1/2 text-slate-400"></i>
            </form>
        </div>

        {{-- TABLE --}}
        <div class="bg-white border border-slate-100 rounded-[2.5rem] overflow-hidden shadow-sm">
            <div class="flex items-center justify-between p-6">
                <h3 class="text-lg font-semibold text-slate-800">Customer List</h3>
                <button @click="openCreate = true"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold transition-all rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Customer</span>
                </button>
            </div>

            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                    <tr>
                        <th class="px-8 py-5">Company</th>
                        <th class="px-6 py-5">PIC</th>
                        <th class="px-6 py-5">Contact</th>
                        <th class="px-6 py-5 text-center">Status</th>
                        <th class="px-8 py-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($customers as $customer)
                        <tr class="hover:bg-slate-50">
                            <td class="px-8 py-6">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800">{{ $customer->company_name ?? '-' }}</span>
                                    <span class="text-xs text-slate-400">{{ $customer->email }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="text-sm font-semibold text-slate-700">
                                    {{ $customer->contacts->first()->name ?? '-' }}
                            </td>
                            <td class="px-6 py-6 text-sm text-slate-500">{{ $customer->contacts->first()->phone ?? '-' }}
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span
                                    class="{{ $customer->status == 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }} px-3 py-1 rounded-lg text-[10px] font-black uppercase">
                                    {{ $customer->status }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <button @click="openProfile = true; selectedCustomer = {{ json_encode($customer) }}"
                                    class="px-5 py-2 text-xs font-bold bg-white border border-slate-200 rounded-xl hover:border-indigo-500 hover:text-indigo-600">
                                    View Profile
                                </button>

                                <button @click="openEdit = true; selectedCustomer = {{ json_encode($customer) }}"
                                    class="px-4 py-2 text-xs font-bold text-yellow-600 border border-yellow-200 bg-yellow-50 rounded-xl hover:bg-yellow-100">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-6 border-t border-slate-50 bg-slate-50/30">
                {{ $customers->links() }}
            </div>
        </div>

        {{-- MODAL CREATE CUSTOMER --}}
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
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>

            <div @click.away="openCreate = false; step = 1" x-show="openCreate"
                x-transition:enter="transition ease-out duration-300 delay-100"
                x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden border border-white">

                {{-- STEP TRACKER --}}
                <div class="px-10 pt-12 pb-8 bg-gradient-to-b from-slate-50 to-white">
                    <div class="relative flex items-center justify-between">
                        <div class="absolute top-5 left-0 w-full h-[2px] bg-slate-100 -z-0">
                            <div class="h-full transition-all duration-700 bg-blue-500"
                                :style="`width: ${step === 1 ? '0%' : '100%'}`"></div>
                        </div>

                        <div class="relative z-10 flex flex-col items-start pr-4 bg-white">
                            <div :class="step >= 1 ? 'bg-blue-600 border-blue-600 text-white shadow-lg' :
                                'bg-white border-slate-200 text-slate-400'"
                                class="flex items-center justify-center w-10 h-10 transition-all duration-500 border-2 rounded-2xl">
                                <template x-if="step > 1"><i class="text-xs fa-solid fa-check"></i></template>
                                <template x-if="step === 1"><span class="text-xs font-black">01</span></template>
                            </div>
                            <div class="mt-3 text-left">
                                <p class="text-[10px] font-black uppercase tracking-widest"
                                    :class="step >= 1 ? 'text-blue-600' : 'text-slate-400'">Company</p>
                                <p class="text-sm font-bold text-slate-800">General Info</p>
                            </div>
                        </div>

                        <div class="relative z-10 flex flex-col items-end pl-4 text-right bg-white">
                            <div :class="step >= 2 ? 'bg-blue-600 border-blue-600 text-white shadow-lg' :
                                'bg-white border-slate-200 text-slate-400'"
                                class="flex items-center justify-center w-10 h-10 ml-auto transition-all duration-500 border-2 rounded-2xl">
                                <span class="text-xs font-black">02</span>
                            </div>
                            <div class="mt-3">
                                <p class="text-[10px] font-black uppercase tracking-widest"
                                    :class="step >= 2 ? 'text-blue-600' : 'text-slate-400'">Contact</p>
                                <p class="text-sm font-bold text-slate-800">Person in Charge</p>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.customerList.store') }}" method="POST">
                    @csrf
                    {{-- Pesan Error Validasi Laravel --}}
                    @if ($errors->any())
                        <div class="px-10 mb-4">
                            <div class="p-4 text-xs text-red-600 bg-red-50 rounded-2xl">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="px-10 pb-10">
                        {{-- STEP 1 --}}
                        <div x-show="step === 1" id="step-1-content" x-transition:enter="transition duration-500"
                            class="space-y-6 text-left">
                            <div class="grid grid-cols-2 gap-6 text-left">
                                <div class="col-span-2">
                                    <label
                                        class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Company
                                        Name</label>
                                    <input type="text" name="company_name" required
                                        placeholder="Enter legal company name"
                                        class="w-full px-5 py-3.5 text-sm font-medium bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 outline-none">
                                </div>
                                <div class="col-span-1">
                                    <label
                                        class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Industry</label>
                                    <input type="text" name="industry" placeholder="e.g. Technology"
                                        class="w-full px-5 py-3.5 text-sm font-medium bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 outline-none">
                                </div>
                                <div class="col-span-1">
                                    <label
                                        class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Tax
                                        ID (NPWP)</label>
                                    <input type="text" name="npwp" placeholder="00.000.000.0-000.000"
                                        class="w-full px-5 py-3.5 text-sm font-medium bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 outline-none">
                                </div>
                                <div class="col-span-2">
                                    <label
                                        class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Address</label>
                                    <textarea name="address_line" required rows="2" placeholder="Full street address..."
                                        class="w-full px-5 py-3.5 text-sm font-medium bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 outline-none"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- STEP 2 --}}
                        <div x-show="step === 2" id="step-2-content" x-transition:enter="transition duration-500"
                            class="space-y-6 text-left">
                            <div class="grid grid-cols-2 gap-6 text-left">
                                <div class="col-span-2">
                                    <label
                                        class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Full
                                        Name (PIC)</label>
                                    {{-- PERBAIKAN: name diubah dari 'contact_name' ke 'name' --}}
                                    <input type="text" name="name" required placeholder="Legal full name"
                                        class="w-full px-5 py-3.5 text-sm font-medium bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 outline-none">
                                </div>
                                <div class="col-span-1">
                                    <label
                                        class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Phone</label>
                                    <input type="text" name="contact_phone" required placeholder="+62..."
                                        class="w-full px-5 py-3.5 text-sm font-medium bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 outline-none">
                                </div>
                                <div class="col-span-1">
                                    <label
                                        class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Email</label>
                                    <input type="email" name="email" required placeholder="pic@company.com"
                                        class="w-full px-5 py-3.5 text-sm font-medium bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 outline-none">
                                </div>
                            </div>
                        </div>

                        {{-- FOOTER BUTTONS --}}
                        <div class="flex items-center justify-between mt-12">
                            <button type="button" x-show="step > 1" @click="prevStep()"
                                class="flex items-center gap-2 px-6 py-3 text-sm font-bold text-slate-400 hover:text-slate-800">
                                <i class="fa-solid fa-chevron-left text-[10px]"></i> Back
                            </button>
                            <div x-show="step === 1"></div>

                            <div class="flex items-center gap-4">
                                <button type="button" @click="openCreate = false; step = 1"
                                    class="px-6 py-3 text-sm font-bold text-slate-400 hover:text-red-500">Cancel</button>

                                <button type="button" x-show="step < totalSteps" @click="validateAndNext()"
                                    class="px-10 py-4 text-xs font-black tracking-widest text-white uppercase transition-all bg-slate-900 rounded-2xl hover:bg-blue-600">
                                    Continue
                                </button>

                                <button type="submit" x-show="step === totalSteps"
                                    class="px-10 py-4 text-xs font-black tracking-widest text-white uppercase transition-all bg-blue-600 shadow-lg rounded-2xl hover:bg-blue-700 shadow-blue-200">
                                    Complete Setup
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL PROFILE --}}
        <div x-show="openProfile" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>

            <div @click.away="openProfile = false"
                class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden border border-white">

                <div class="flex items-center justify-between p-8 border-b border-slate-50 bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-black text-slate-800" x-text="selectedCustomer.company_name"></h3>
                        <p class="mt-1 text-xs font-bold tracking-widest text-blue-600 uppercase"
                            x-text="selectedCustomer.industry || 'General Industry'"></p>
                    </div>
                    <button @click="openProfile = false"
                        class="flex items-center justify-center w-10 h-10 transition-colors bg-white shadow-sm rounded-xl text-slate-400 hover:text-red-500">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <label class="block mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">PIC
                                Name</label>
                            <p class="text-sm font-bold text-slate-700"
                                x-text="selectedCustomer.contacts && selectedCustomer.contacts.length > 0 ? selectedCustomer.contacts[0].name : '-'">
                            </p>
                        </div>

                        <div>
                            <label class="block mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">PIC
                                Phone</label>
                            <p class="text-sm font-bold text-slate-700"
                                x-text="selectedCustomer.contacts && selectedCustomer.contacts.length > 0 ? selectedCustomer.contacts[0].phone : '-'">
                            </p>
                        </div>

                        <div>
                            <label class="block mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Email
                                Address</label>
                            <p class="text-sm font-bold text-slate-700" x-text="selectedCustomer.email"></p>
                        </div>

                        ...
                    </div>

                    <div class="p-6 border rounded-2xl bg-slate-50 border-slate-100">
                        <label
                            class="block mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Registered
                            Address</label>
                        <p class="text-sm font-medium leading-relaxed text-slate-600"
                            x-text="selectedCustomer.addresses && selectedCustomer.addresses.length > 0 ? selectedCustomer.addresses[0].address_line : 'No address registered'">
                        </p>
                    </div>

                    <div class="p-6 border rounded-2xl bg-slate-50 border-slate-100">
                        <label
                            class="block mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Registered
                            Address</label>
                        <p class="text-sm font-medium leading-relaxed text-slate-600"
                            x-text="selectedCustomer.address || 'No address registered'"></p>
                    </div>
                </div>

                <div class="flex justify-end px-8 py-6 border-t bg-slate-50/50 border-slate-50">
                    <button @click="openProfile = false"
                        class="px-8 py-3 text-xs font-black tracking-widest text-white uppercase transition-all bg-slate-900 rounded-xl hover:bg-slate-800">
                        Close Profile
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL EDIT CUSTOMER --}}
        {{-- MODAL EDIT CUSTOMER --}}
        <div x-show="openEdit" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>

            <div @click.away="openEdit = false" x-show="openEdit"
                x-transition:enter="transition ease-out duration-300 delay-100"
                x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden border border-white">

                {{-- MODAL HEADER --}}
                <div class="px-10 pt-10 pb-6 bg-gradient-to-b from-slate-50 to-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex items-center justify-center w-12 h-12 bg-amber-100 rounded-2xl text-amber-600">
                                <i class="text-lg fa-solid fa-pen-to-square"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-800">Edit Customer</h3>
                                <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Updating: <span
                                        class="text-indigo-600" x-text="selectedCustomer.company_name"></span></p>
                            </div>
                        </div>
                        <button @click="openEdit = false" class="transition-colors text-slate-400 hover:text-red-500">
                            <i class="text-xl fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <form :action="`/admin/customerList/${selectedCustomer.id}`" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="px-10 pb-10 space-y-6">

                        {{-- SECTION 1: COMPANY INFO --}}
                        <div class="grid grid-cols-2 gap-5">
                            <div class="col-span-2">
                                <label
                                    class="block mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Company
                                    Name</label>
                                <div class="relative group">
                                    <i
                                        class="absolute transition-colors -translate-y-1/2 left-5 top-1/2 fa-solid fa-building text-slate-300 group-focus-within:text-blue-500"></i>
                                    <input type="text" name="company_name" x-model="selectedCustomer.company_name"
                                        required
                                        class="w-full pl-12 pr-5 py-3.5 text-sm font-semibold bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500/20 focus:bg-white focus:ring-[6px] focus:ring-blue-500/5 outline-none transition-all">
                                </div>
                            </div>

                            <div class="col-span-1">
                                <label
                                    class="block mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Industry</label>
                                <input type="text" name="industry" x-model="selectedCustomer.industry"
                                    class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500/20 focus:bg-white focus:ring-[6px] focus:ring-blue-500/5 outline-none transition-all">
                            </div>

                            <div class="col-span-1">
                                <label
                                    class="block mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Company
                                    Email</label>
                                <input type="email" name="email" x-model="selectedCustomer.email"
                                    class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500/20 focus:bg-white focus:ring-[6px] focus:ring-blue-500/5 outline-none transition-all">
                            </div>

                            <div class="col-span-2">
                                <label
                                    class="block mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Address</label>
                                <textarea name="address_line" rows="2"
                                    x-text="selectedCustomer.addresses && selectedCustomer.addresses.length ? selectedCustomer.addresses[0].address_line : ''"
                                    class="w-full px-5 py-3.5 text-sm font-semibold bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500/20 focus:bg-white focus:ring-[6px] focus:ring-blue-500/5 outline-none transition-all"></textarea>
                            </div>
                        </div>

                        {{-- DIVIDER --}}
                        <div class="relative flex items-center py-2">
                            <div class="flex-grow border-t border-slate-100"></div>
                            <span
                                class="flex-shrink mx-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">PIC
                                Details</span>
                            <div class="flex-grow border-t border-slate-100"></div>
                        </div>

                        {{-- SECTION 2: CONTACT INFO --}}
                        <div class="grid grid-cols-2 gap-5">
                            <div class="col-span-2">
                                <label
                                    class="block mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">PIC
                                    Name</label>
                                <div class="relative group">
                                    <i
                                        class="absolute transition-colors -translate-y-1/2 left-5 top-1/2 fa-solid fa-user-tie text-slate-300 group-focus-within:text-blue-500"></i>
                                    <input type="text" name="contact_name" x-model="selectedCustomer.contacts[0].name"
                                        class="w-full pl-12 pr-5 py-3.5 text-sm font-semibold bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500/20 focus:bg-white focus:ring-[6px] focus:ring-blue-500/5 outline-none transition-all">
                                </div>
                            </div>

                            <div class="col-span-2">
                                <label
                                    class="block mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Contact
                                    Phone</label>
                                <div class="relative group">
                                    <i
                                        class="absolute transition-colors -translate-y-1/2 left-5 top-1/2 fa-solid fa-phone text-slate-300 group-focus-within:text-blue-500"></i>
                                    <input type="text" name="contact_phone"
                                        x-model="selectedCustomer.contacts[0].phone"
                                        class="w-full pl-12 pr-5 py-3.5 text-sm font-semibold bg-slate-50 border-2 border-transparent rounded-2xl focus:border-blue-500/20 focus:bg-white focus:ring-[6px] focus:ring-blue-500/5 outline-none transition-all">
                                </div>
                            </div>
                        </div>

                        {{-- FOOTER BUTTONS --}}
                        <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-slate-50">
                            <button type="button" @click="openEdit = false"
                                class="px-8 py-4 text-xs font-black tracking-widest uppercase transition-all text-slate-400 hover:text-red-500">
                                Discard
                            </button>

                            <button type="submit"
                                class="px-10 py-4 text-xs font-black tracking-widest text-white uppercase transition-all bg-blue-600 shadow-lg rounded-2xl hover:bg-blue-700 shadow-blue-200 active:scale-95">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@extends('admin.layout.app')

@section('title', 'Customer List')

@section('content')
    <div class="w-full pb-10 space-y-8" x-data="{
        openProfile: false,
        openCreate: false,
        selectedCustomer: {}
    }">

        {{-- HEADER --}}
        <div class="flex flex-col justify-between gap-4 px-2 md:flex-row md:items-center">

            <div>
                <h2 class="text-3xl font-black tracking-tight text-slate-800">
                    Customer Management
                </h2>

                <p class="text-sm text-slate-500">
                    Nuctech ERP - Business Management System
                </p>
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

                <h3 class="text-lg font-semibold text-slate-800">
                    Daftar Customer
                </h3>


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

                                    <span class="font-bold text-slate-800">
                                        {{ $customer->company_name ?? '-' }}
                                    </span>

                                    <span class="text-xs text-slate-400">
                                        {{ $customer->email }}
                                    </span>

                                </div>

                            </td>


                            <td class="px-6 py-6">

                                <div class="text-sm font-semibold text-slate-700">
                                    {{ $customer->pic_name ?? $customer->name }}
                                </div>

                            </td>


                            <td class="px-6 py-6 text-sm text-slate-500">
                                {{ $customer->phone ?? '-' }}
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

                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>


            {{-- PAGINATION --}}
            <div class="p-6 border-t border-slate-50 bg-slate-50/30">
                {{ $customers->links() }}
            </div>

        </div>



        {{-- MODAL PROFILE --}}
        <div x-show="openProfile"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-md" x-cloak>

            <div @click.away="openProfile = false" class="bg-white w-full max-w-3xl rounded-[2rem] shadow-xl">

                <div class="flex items-center justify-between p-6 border-b">

                    <div>

                        <h3 class="text-lg font-bold text-slate-800" x-text="selectedCustomer.company_name"></h3>

                        <p class="text-xs text-slate-400" x-text="'Customer ID: #' + selectedCustomer.id"></p>

                    </div>

                    <button @click="openProfile = false" class="text-slate-400 hover:text-red-500">

                        <i class="text-lg fa-solid fa-xmark"></i>

                    </button>

                </div>



                <div class="p-6 space-y-6">

                    <div class="grid grid-cols-2 gap-6">

                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">PIC Name</label>
                            <p class="text-sm font-semibold text-slate-700"
                                x-text="selectedCustomer.pic_name || selectedCustomer.name"></p>
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">Email</label>
                            <p class="text-sm font-semibold text-slate-700" x-text="selectedCustomer.email"></p>
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">Phone</label>
                            <p class="text-sm font-semibold text-slate-700" x-text="selectedCustomer.phone || '-'"></p>
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">Status</label>
                            <p class="text-sm font-semibold text-slate-700" x-text="selectedCustomer.status"></p>
                        </div>

                    </div>

                    <div>

                        <label class="text-xs font-bold uppercase text-slate-400">
                            Address
                        </label>

                        <p class="text-sm text-slate-600" x-text="selectedCustomer.address || '-'"></p>

                    </div>

                </div>

            </div>

        </div>



        {{-- MODAL CREATE CUSTOMER --}}
        <div x-show="openCreate"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-md" x-cloak>

            <div @click.away="openCreate = false" class="bg-white w-full max-w-2xl rounded-[2rem] shadow-xl">

                <div class="flex items-center justify-between p-6 border-b">

                    <h3 class="text-lg font-bold text-slate-800">
                        Create Customer
                    </h3>

                    <button @click="openCreate = false" class="text-slate-400 hover:text-red-500">
                        <i class="text-lg fa-solid fa-xmark"></i>
                    </button>

                </div>


                <form action="{{ route('admin.customerList.store') }}" method="POST" class="p-6 space-y-5">

                    @csrf

                    <div class="grid grid-cols-2 gap-4">

                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">
                                Customer Name
                            </label>

                            <input type="text" name="name" required
                                class="w-full px-3 py-2 mt-1 text-sm border rounded-xl border-slate-200">
                        </div>


                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">
                                Email
                            </label>

                            <input type="email" name="email" required
                                class="w-full px-3 py-2 mt-1 text-sm border rounded-xl border-slate-200">
                        </div>


                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">
                                Company Name
                            </label>

                            <input type="text" name="company_name" required
                                class="w-full px-3 py-2 mt-1 text-sm border rounded-xl border-slate-200">
                        </div>


                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">
                                PIC Name
                            </label>

                            <input type="text" name="pic_name" required
                                class="w-full px-3 py-2 mt-1 text-sm border rounded-xl border-slate-200">
                        </div>


                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">
                                Phone
                            </label>

                            <input type="text" name="phone"
                                class="w-full px-3 py-2 mt-1 text-sm border rounded-xl border-slate-200">
                        </div>


                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">
                                Password
                            </label>

                            <input type="password" name="password"
                                class="w-full px-3 py-2 mt-1 text-sm border rounded-xl border-slate-200">
                        </div>

                    </div>


                    <div>

                        <label class="text-xs font-bold uppercase text-slate-400">
                            Address
                        </label>

                        <textarea name="address" rows="3" class="w-full px-3 py-2 mt-1 text-sm border rounded-xl border-slate-200"></textarea>

                    </div>


                    <div class="flex justify-end gap-3 pt-4">

                        <button type="button" @click="openCreate=false"
                            class="px-4 py-2 text-sm font-bold border rounded-xl">
                            Cancel
                        </button>

                        <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-blue-600 rounded-xl">
                            Save Customer
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
@endsection

@extends('admin.layout.app')

@section('content')
<div class="w-full pb-10 space-y-8" x-data="{ openModal: false, selectedCustomer: {} }">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-2">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Customer Monitoring</h2>
            <p class="text-sm text-slate-500">Nuctech ERP - Business Management System</p>
        </div>
        
        <form action="{{ route('admin.business.index') }}" method="GET" class="relative group">
            <input type="text" name="search" value="{{ $search ?? '' }}" 
                   placeholder="Search company or PIC..." 
                   class="w-full md:w-80 pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-sm">
            <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500"></i>
        </form>
    </div>

    <div class="bg-white border border-slate-100 rounded-[2.5rem] overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/50 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                <tr>
                    <th class="px-8 py-5">Company Info</th>
                    <th class="px-6 py-5">PIC Detail</th>
                    <th class="px-6 py-5 text-center">Status</th>
                    <th class="px-8 py-5 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach ($customers as $customer)
                <tr class="hover:bg-slate-50/80 transition-colors group">
                    <td class="px-8 py-6">
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800 text-base">{{ $customer->company_name ?? 'Unnamed Company' }}</span>
                            <span class="text-xs text-slate-400">{{ $customer->email }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-6">
                        <div class="text-sm font-medium text-slate-600">{{ $customer->pic_name ?? $customer->name }}</div>
                        <div class="text-[11px] text-slate-400">{{ $customer->phone }}</div>
                    </td>
                    <td class="px-6 py-6 text-center">
                        <span class="{{ $customer->status === 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} px-3 py-1 rounded-lg text-[10px] font-black uppercase">
                            {{ $customer->status ?? 'pending' }}
                        </span>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <button 
                            @click="openModal = true; selectedCustomer = {{ json_encode($customer) }}"
                            class="bg-white border border-slate-200 hover:border-indigo-500 hover:text-indigo-600 px-5 py-2 rounded-xl text-xs font-bold transition-all shadow-sm">
                            View Profile
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

    <div x-show="openModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-md" x-cloak>
        
        <div @click.away="openModal = false" class="bg-slate-50 w-full max-w-5xl max-h-[90vh] overflow-y-auto rounded-[3rem] shadow-2xl border border-white">
            
            <div class="p-8 bg-white border-b border-slate-100 flex justify-between items-center sticky top-0 z-10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                        <i class="fa-solid fa-building text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-800" x-text="selectedCustomer.company_name"></h3>
                        <p class="text-xs text-slate-400" x-text="'Customer ID: #' + selectedCustomer.id"></p>
                    </div>
                </div>
                <button @click="openModal = false" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-rose-50 hover:text-rose-500 transition-all text-slate-300">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-8 space-y-6">
                <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm space-y-6">
                        <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Company Profile Details</h4>
                        <div class="grid grid-cols-2 gap-y-6 gap-x-4">
                            <div>
                                <label class="text-[10px] text-slate-400 font-bold uppercase block mb-1">PIC Name</label>
                                <p class="text-sm font-bold text-slate-700" x-text="selectedCustomer.pic_name || selectedCustomer.name"></p>
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Email</label>
                                <p class="text-sm font-bold text-slate-700" x-text="selectedCustomer.email"></p>
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Contact</label>
                                <p class="text-sm font-bold text-slate-700" x-text="selectedCustomer.phone || '-'"></p>
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Profile Verification</label>
                                <span :class="selectedCustomer.profile_completed ? 'text-emerald-500' : 'text-amber-500'" class="text-xs font-bold flex items-center gap-1">
                                    <i :class="selectedCustomer.profile_completed ? 'fa-circle-check' : 'fa-circle-exclamation'" class="fa-solid"></i>
                                    <span x-text="selectedCustomer.profile_completed ? 'Verified' : 'Incomplete'"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-indigo-600 p-8 rounded-[2.5rem] shadow-lg shadow-indigo-100 text-white space-y-4">
                        <h4 class="text-[10px] font-black text-indigo-200 uppercase tracking-widest">Office Address</h4>
                        <p class="text-sm font-medium leading-relaxed italic" x-text="selectedCustomer.address || 'No address provided yet.'"></p>
                        <i class="fa-solid fa-map-location-dot text-4xl opacity-20 float-right mt-4"></i>
                    </div>
                </section>

                <section class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                    <h4 class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-6">Product History Monitoring</h4>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-[10px] font-black uppercase text-slate-400 border-b border-slate-50">
                                <tr>
                                    <th class="px-4 py-3">Product Info</th>
                                    <th class="px-4 py-3 text-center">Qty</th>
                                    <th class="px-4 py-3 text-right">Dosage Request</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <template x-for="booking in selectedCustomer.bookings" :key="booking.id">
                                    <template x-for="product in booking.products" :key="product.id">
                                        <tr class="group">
                                            <td class="px-4 py-4">
                                                <div class="font-bold text-slate-700" x-text="product.name"></div>
                                                <div class="text-[10px] text-slate-400" x-text="'Ref Order: ' + booking.booking_code"></div>
                                            </td>
                                            <td class="px-4 py-4 text-center font-medium text-slate-600" x-text="product.qty + ' Pcs'"></td>
                                            <td class="px-4 py-4 text-right">
                                                <span :class="product.dmax > 50 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-600'" 
                                                      class="px-3 py-1.5 rounded-xl text-[10px] font-black"
                                                      x-text="product.dmax + ' kGy'"></span>
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
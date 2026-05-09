<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[800px] lg:min-w-full">
        <thead
            class="bg-slate-50/50 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-50">
            <tr>
                <th class="px-6 py-5 md:px-8">Company</th>
                <th class="px-6 py-5">PIC Info</th>
                <th class="px-6 py-5 text-center">Status</th>
                <th class="px-6 py-5 text-right md:px-8">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse ($customers as $customer)
                <tr class="transition-colors hover:bg-slate-50/80 group">
                    <td class="px-6 py-5 md:px-8">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex items-center justify-center w-10 h-10 text-xs font-bold uppercase rounded-xl bg-slate-100 text-slate-500">
                                {{ substr($customer->company_name, 0, 2) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800">{{ $customer->company_name ?? '-' }}</span>
                                <span class="text-xs text-slate-400">{{ $customer->email }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <span
                            class="text-sm font-bold text-slate-700">{{ $customer->contacts->first()->name ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-5 text-center">
                        <span
                            class="px-3 py-1 rounded-lg text-[10px] font-black uppercase {{ $customer->status == 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                            {{ $customer->status }}
                        </span>
                    </td>
                    <td class="px-6 py-5 text-right md:px-8">
                        <div class="flex justify-end gap-2">
                            <button @click="openProfile = true; selectedCustomer = {{ json_encode($customer) }}"
                                class="p-2.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button @click="openEdit = true; selectedCustomer = {{ json_encode($customer) }}"
                                class="p-2.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition-all">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button @click="selectedCustomer = {{ json_encode($customer) }}; openDelete = true"
                                class="p-2.5 transition-all bg-white rounded-xl text-slate-400 border-slate-200 hover:text-rose-600 hover:border-rose-100 hover:bg-rose-50">
                                <i class="text-md fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-20 text-center text-slate-400">No customers found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="p-6 bg-white border-t border-slate-50">
    {{ $customers->links() }}
</div>

@extends('admin.layout.app')
@section('title', 'Porter Hub')

@section('content')
    <div class="w-full pb-20 space-y-6">

        {{-- ═══ TOP BAR: BRANDING & ACTION ═══ --}}
        <div class="flex flex-col gap-4 pb-6 border-b sm:flex-row sm:items-center sm:justify-between border-slate-100">
            <div>
                <nav class="flex mb-1.5 text-[10px] font-bold tracking-widest text-slate-400 uppercase gap-2">
                    <span>Logistics Staffing</span>
                    <span>&middot;</span>
                    <span class="text-indigo-600">Ground Operations</span>
                </nav>
                <h2 class="text-2xl font-black tracking-tight text-slate-800 md:text-3xl">
                    Porter <span class="text-indigo-600">Management</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Deploy, oversee, and optimize on-duty warehouse porters.</p>
            </div>

            <div>
                <a href="{{ route('admin.porter.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] rounded-xl shadow-sm transition-all whitespace-nowrap">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>Register New Porter</span>
                </a>
            </div>
        </div>

        {{-- ═══ PORTER DIRECTORY CONTAINER ═══ --}}
        <div class="overflow-hidden bg-white border shadow-sm border-slate-200/70 rounded-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr
                            class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                            <th class="py-3.5 px-6">Personnel Information</th>
                            <th class="py-3.5 px-6">Contact / Phone</th>
                            <th class="py-3.5 px-6">Deployment Status</th>
                            <th class="py-3.5 px-6 text-center">Operational Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($porters as $porter)
                            <tr class="transition-colors hover:bg-slate-50/40 group">
                                {{-- Avatar & Name --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200/60 flex items-center justify-center font-bold text-slate-600 text-[11px] uppercase group-hover:bg-indigo-50 group-hover:text-indigo-600 group-hover:border-indigo-100 transition-colors">
                                            {{ substr($porter->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <p class="font-bold tracking-tight uppercase text-slate-800">{{ $porter->name }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">ID:
                                                PTR-{{ str_pad($porter->id, 4, '0', STR_PAD_LEFT) }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Phone --}}
                                <td class="px-6 py-4 font-mono font-medium text-slate-500">
                                    {{ $porter->phone ?? '—' }}
                                </td>

                                {{-- Status Badges --}}
                                <td class="px-6 py-4">
                                    @if ($porter->is_active)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 uppercase tracking-wide">
                                            <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200 uppercase tracking-wide">
                                            <span class="w-1 h-1 rounded-full bg-slate-400"></span> Inactive
                                        </span>
                                    @endif
                                </td>

                                {{-- Action Controls --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('admin.porter.edit', $porter->id) }}"
                                            class="flex items-center justify-center transition-all bg-white border rounded-lg w-7 h-7 text-slate-400 border-slate-200 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50/50 shadow-2xs"
                                            title="Edit Profile">
                                            <i class="fa-solid fa-pen text-[10px]"></i>
                                        </a>

                                        <form action="{{ route('admin.porter.destroy', $porter->id) }}" method="POST"
                                            onsubmit="return confirm('Revoke this porter profile permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="flex items-center justify-center transition-all bg-white border rounded-lg w-7 h-7 text-slate-400 border-slate-200 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50/50 shadow-2xs"
                                                title="Delete Profile">
                                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-16 text-center">
                                    <div
                                        class="flex items-center justify-center w-12 h-12 mx-auto mb-3 border rounded-full bg-slate-50 border-slate-200/60 text-slate-300">
                                        <i class="text-base fa-solid fa-user-gear"></i>
                                    </div>
                                    <h4 class="text-xs font-bold tracking-wider uppercase text-slate-800">No Porters
                                        Available</h4>
                                    <p class="text-[11px] text-slate-400 mt-1">Please register new personnel to assign
                                        ground handling jobs.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ═══ FOOTER & PAGINATION ═══ --}}
            @if ($porters->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                    {{ $porters->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

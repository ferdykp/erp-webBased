@extends('admin.layout.app')

@section('content')
    <div class="w-full px-2 py-4 mx-auto space-y-6 sm:px-4 md:px-6 lg:py-8">

        {{-- CARD CONTAINER --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[1.5rem] sm:rounded-[2.5rem] overflow-hidden">

            {{-- HEADER ACTION --}}
            <div
                class="flex flex-col gap-4 px-6 py-6 border-b sm:flex-row sm:items-center sm:justify-between border-slate-50 md:px-10">
                <div>
                    <h2 class="text-2xl font-black tracking-tighter text-slate-800 md:text-3xl">
                        List <span class="text-blue-600">Admin</span>
                    </h2>
                    <p class="mt-1 text-xs font-bold tracking-widest uppercase text-slate-400">
                        Manajemen Hak Akses Pengguna
                    </p>
                </div>
                @if (in_array(auth()->user()->role, ['superadmin']))
                    <a href="{{ route('admin.profile.create') }}"
                        class="flex items-center justify-center gap-2 px-6 py-3 text-sm font-black text-white transition-all bg-emerald-500 rounded-xl sm:rounded-2xl hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-100">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add User</span>
                    </a>
                @endif
            </div>

            {{-- TABLE SECTION --}}
            <div class="p-2 sm:p-6 md:p-8">
                <div class="overflow-x-auto border rounded-2xl border-slate-50">
                    <table id="datatable" class="w-full text-left border-separate border-spacing-y-2">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th
                                    class="px-4 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-center rounded-l-xl">
                                    No</th>
                                <th class="px-4 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">User
                                    Details</th>
                                <th
                                    class="px-4 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hidden md:table-cell text-center">
                                    Role</th>
                                @if (in_array(auth()->user()->role, ['superadmin']))
                                    <th
                                        class="px-4 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-center rounded-r-xl">
                                        Action</th>
                                @endif
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($admins as $index => $user)
                                <tr class="transition-all group hover:bg-slate-50">
                                    {{-- NO --}}
                                    <td
                                        class="px-4 py-4 text-sm font-bold text-center border-l border-y border-slate-50 rounded-l-2xl text-slate-400">
                                        {{ $index + 1 }}
                                    </td>

                                    {{-- USER INFO --}}
                                    <td class="px-4 py-4 border-y border-slate-50">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center justify-center w-10 h-10 font-black text-blue-600 rounded-xl bg-blue-50 shrink-0">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <span
                                                    class="text-sm font-black leading-tight uppercase truncate text-slate-800">{{ $user->name }}</span>
                                                <span
                                                    class="text-[11px] font-medium text-slate-400 truncate">{{ $user->email }}</span>
                                                {{-- Mobile-only badge --}}
                                                <div class="mt-1 md:hidden">
                                                    <span
                                                        class="px-2 py-0.5 text-[9px] font-black uppercase rounded-md {{ $user->role === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                                                        {{ $user->role }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- ROLE (Desktop Only) --}}
                                    <td class="hidden px-4 py-4 text-center border-y border-slate-50 md:table-cell">
                                        <span
                                            class="px-3 py-1 text-[10px] font-black uppercase rounded-lg {{ $user->role === 'admin' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-slate-50 text-slate-500 border border-slate-100' }}">
                                            {{ $user->role }}
                                        </span>
                                    </td>

                                    {{-- ACTION --}}
                                    @if (in_array(auth()->user()->role, ['superadmin']))
                                        <td class="px-4 py-4 border-r border-y border-slate-50 rounded-r-2xl">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('admin.profile.edit', $user->id) }}"
                                                    class="flex items-center justify-center text-blue-600 transition-all bg-white border shadow-sm w-9 h-9 border-slate-100 rounded-xl hover:bg-blue-600 hover:text-white hover:border-blue-600">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>

                                                <form action="{{ route('admin.profile.destroy', $user->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Apakah Anda Yakin Ingin Menghapus Admin Ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="flex items-center justify-center text-red-500 transition-all bg-white border shadow-sm w-9 h-9 border-slate-100 rounded-xl hover:bg-red-500 hover:text-white hover:border-red-500">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>

                                            </div>
                                        </td>
                                    @endif

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="px-4 py-12 text-center border border-dashed border-slate-100 rounded-3xl">
                                        <div class="flex flex-col items-center">
                                            <i class="mb-3 text-4xl text-slate-200 fa-solid fa-user-slash"></i>
                                            <p class="text-sm font-bold tracking-widest uppercase text-slate-400">Tidak ada
                                                user yang terdaftar</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- INFO FOOTER --}}
        <div class="text-center">
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em]">
                Total Admin Terdaftar: {{ count($admins) }} Orang
            </p>
        </div>
    </div>
@endsection

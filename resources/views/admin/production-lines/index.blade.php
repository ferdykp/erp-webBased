@extends('admin.layout.app')

@section('title', 'Master Mesin Penyinaran')

@section('content')

    <div class="w-full pb-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Master Mesin</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Kelola daftar mesin penyinaran (Production Lines).</p>
            </div>
            <button onclick="document.getElementById('createModal').classList.replace('hidden','flex')"
                class="flex items-center gap-2 px-6 py-3 text-sm font-black text-white bg-blue-600 shadow-lg rounded-2xl hover:bg-blue-700 active:scale-95 transition-all shadow-blue-100">
                <i class="fa-solid fa-plus"></i>
                Tambah Mesin
            </button>
        </div>

        {{-- TABLE --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[3rem] overflow-hidden">
            <div class="p-6 overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-[10px] font-black tracking-[0.2em] text-slate-600 uppercase">
                            <th class="px-8 py-4">#</th>
                            <th class="px-6 py-4">Nama Mesin</th>
                            <th class="px-6 py-4 text-center">Dibuat</th>
                            <th class="px-8 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productionLines as $index => $line)
                            <tr class="transition-all duration-300 bg-white group hover:bg-slate-50/50">
                                <td
                                    class="px-8 py-6 rounded-l-[2rem] border-y border-l border-transparent group-hover:border-slate-100">
                                    <span class="text-sm font-bold text-slate-400">{{ $index + 1 }}</span>
                                </td>
                                <td class="px-6 py-6 border-transparent border-y group-hover:border-slate-100">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 font-black text-blue-700 bg-blue-50 rounded-xl">
                                            <i class="fa-solid fa-gear"></i>
                                        </div>
                                        <span class="text-sm font-black text-slate-800">{{ $line->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6 text-center border-transparent border-y group-hover:border-slate-100">
                                    <span
                                        class="text-sm font-bold text-slate-500">{{ $line->created_at->format('d M Y') }}</span>
                                </td>
                                <td
                                    class="px-8 py-6 rounded-r-[2rem] border-y border-r border-transparent group-hover:border-slate-100">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Edit Button --}}
                                        <button onclick="openEditModal({{ $line->id }}, '{{ addslashes($line->name) }}')"
                                            class="flex items-center justify-center w-9 h-9 text-amber-600 bg-amber-50 rounded-xl hover:bg-amber-600 hover:text-white transition-all">
                                            <i class="text-xs fa-solid fa-pen"></i>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form action="{{ route('admin.production-lines.destroy', $line) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus mesin ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="flex items-center justify-center w-9 h-9 text-red-600 bg-red-50 rounded-xl hover:bg-red-600 hover:text-white transition-all">
                                                <i class="text-xs fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-16 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-slate-100">
                                            <i class="text-2xl fa-solid fa-gear text-slate-300"></i>
                                        </div>
                                        <p class="text-sm font-bold text-slate-400">Belum ada data mesin.</p>
                                        <p class="text-xs text-slate-400">Klik tombol "Tambah Mesin" untuk memulai.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══ CREATE MODAL ═══ --}}
    <div id="createModal"
        class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-6">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl p-10">
            <h3 class="mb-6 text-2xl font-black text-slate-800">Tambah Mesin Baru</h3>
            <form action="{{ route('admin.production-lines.store') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block mb-2 text-xs font-black text-slate-400 uppercase">Nama Mesin</label>
                    <input type="text" name="name" required placeholder='Contoh: "Mesin 1"'
                        class="w-full px-6 py-4 text-sm font-bold border-none bg-slate-50 rounded-2xl focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex gap-3">
                    <button type="button"
                        onclick="document.getElementById('createModal').classList.replace('flex','hidden')"
                        class="flex-1 py-4 text-sm font-black bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-4 text-sm font-black text-white bg-blue-600 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ EDIT MODAL ═══ --}}
    <div id="editModal"
        class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-6">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl p-10">
            <h3 class="mb-6 text-2xl font-black text-slate-800">Edit Mesin</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-6">
                    <label class="block mb-2 text-xs font-black text-slate-400 uppercase">Nama Mesin</label>
                    <input type="text" name="name" id="editName" required
                        class="w-full px-6 py-4 text-sm font-bold border-none bg-slate-50 rounded-2xl focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('editModal').classList.replace('flex','hidden')"
                        class="flex-1 py-4 text-sm font-black bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-4 text-sm font-black text-white bg-blue-600 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openEditModal(id, name) {
            document.getElementById('editName').value = name;
            document.getElementById('editForm').action = `/admin/production-lines/${id}`;
            document.getElementById('editModal').classList.replace('hidden', 'flex');
        }
    </script>
@endpush
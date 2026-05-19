@extends('admin.layout.app')

@section('title', 'Daftar PIC Warehouse')

@section('content')
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <div class="flex flex-col justify-between mb-6 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-xl font-bold text-slate-800">PIC Warehouse</h1>
                <p class="text-sm text-slate-500">Kelola daftar petugas penanggung jawab gudang.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('admin.warehouse-pics.create') }}"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="mr-2 fa-solid fa-plus"></i> Tambah PIC
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto border rounded-lg border-slate-200">
            <table class="w-full text-sm text-left border-collapse text-slate-600">
                <thead class="text-xs uppercase border-b bg-slate-50 text-slate-700 border-slate-200">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Nama</th>
                        <th class="px-6 py-3 font-semibold">Email</th>
                        <th class="px-6 py-3 font-semibold">No. Telepon</th>
                        <th class="px-6 py-3 font-semibold">Shift Kerja</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                        <th class="px-6 py-3 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($pics as $pic)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $pic->name }}</td>
                            <td class="px-6 py-4">{{ $pic->email }}</td>
                            <td class="px-6 py-4">{{ $pic->phone ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $pic->shift ?? 'Belum Diatur' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($pic->is_active)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Non-Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('admin.warehouse-pics.edit', $pic->id) }}"
                                        class="inline-flex items-center p-1.5 text-blue-600 rounded-md hover:bg-blue-50"
                                        title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.warehouse-pics.destroy', $pic->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus petugas PIC ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center p-1.5 text-red-600 rounded-md hover:bg-red-50"
                                            title="Hapus">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                Data PIC Warehouse belum tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $pics->links() }}
        </div>
    </div>
@endsection

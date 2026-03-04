@extends('admin.layout.app')

@section('title', 'Slot Management')

@section('content')
    <div class="w-full space-y-6">

        {{-- HEADER & STATS --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-800">Slot Management</h2>
                <p class="text-sm text-gray-500">Manage and generate availability sessions for bookings.</p>
            </div>
            <form action="{{ route('admin.slots.generate') }}" method="POST" onsubmit="return confirmGenerate()">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 font-semibold text-white transition-all bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200">
                    <i class="text-sm fa-solid fa-magic-wand-sparkles"></i>
                    Auto Generate 30 Days
                </button>
            </form>
        </div>

        {{-- NOTIFICATION --}}
        @if (session('success') || session('error'))
            <div id="status-alert" class="fixed z-50 top-24 right-6 animate-fade-in">
                <div
                    class="px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 {{ session('success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-100' : 'bg-rose-50 text-rose-800 border border-rose-100' }}">
                    <i
                        class="fa-solid {{ session('success') ? 'fa-circle-check text-emerald-500' : 'fa-circle-exclamation text-rose-500' }} text-xl"></i>
                    <span class="font-medium">{{ session('success') ?? session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-3xl">

            {{-- FORM ADD SLOT (Collapsible feel) --}}
            <div class="p-8 border-b bg-gray-50/50">
                <h3 class="mb-5 text-sm font-bold tracking-widest text-gray-400 uppercase">Add New Manual Slot</h3>
                <form action="{{ route('admin.slots.store') }}" method="POST"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    @csrf
                    <div class="space-y-1">
                        <label class="ml-1 text-xs font-bold text-gray-600">Date</label>
                        <input type="date" name="date"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
                            required>
                    </div>

                    <div class="space-y-1">
                        <label class="ml-1 text-xs font-bold text-gray-600">Session</label>
                        <select name="session"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
                            required>
                            <option value="Morning">Morning</option>
                            <option value="Afternoon">Afternoon</option>
                            <option value="Evening">Evening</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="ml-1 text-xs font-bold text-gray-600">Start</label>
                        <input type="time" name="start_time"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
                            required>
                    </div>

                    <div class="space-y-1">
                        <label class="ml-1 text-xs font-bold text-gray-600">End</label>
                        <input type="time" name="end_time"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
                            required>
                    </div>

                    <div class="space-y-1">
                        <label class="ml-1 text-xs font-bold text-gray-600">Capacity</label>
                        <input type="number" name="capacity" placeholder="0"
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
                            required>
                    </div>

                    <div class="flex items-end">
                        <button type="submit"
                            class="w-full px-4 py-2.5 font-bold text-white bg-gray-900 rounded-xl hover:bg-black transition-all shadow-md active:scale-95">
                            Add Slot
                        </button>
                    </div>
                </form>
            </div>

            {{-- TABLE --}}
            <div class="p-4 overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-xs tracking-widest text-gray-400 uppercase border-b border-gray-100">
                            <th class="px-4 py-4 font-semibold">Date</th>
                            <th class="px-4 py-4 font-semibold">Session</th>
                            <th class="px-4 py-4 font-semibold text-center">Time Range</th>
                            <th class="px-4 py-4 font-semibold text-center">Capacity</th>
                            <th class="px-4 py-4 font-semibold text-center">Booked</th>
                            <th class="px-4 py-4 font-semibold text-center">Status</th>
                            <th class="px-4 py-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($slots as $slot)
                            <tr class="transition-all group hover:bg-gray-50/50">
                                <td class="px-4 py-4 font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($slot->date)->format('d M Y') }}</td>
                                <td class="px-4 py-4">
                                    <span
                                        class="px-3 py-1 text-xs font-bold rounded-lg {{ $slot->session == 'Morning' ? 'bg-amber-100 text-amber-700' : ($slot->session == 'Afternoon' ? 'bg-blue-100 text-blue-700' : 'bg-indigo-100 text-indigo-700') }}">
                                        {{ $slot->session }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 font-mono text-sm italic text-center text-gray-500">
                                    {{ $slot->start_time }} - {{ $slot->end_time }}
                                </td>
                                <td class="px-4 py-4 font-semibold text-center text-gray-700">{{ $slot->capacity }}</td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-full max-w-[50px] bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-full bg-indigo-500"
                                                style="width: {{ ($slot->booked_count / $slot->capacity) * 100 }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold">{{ $slot->booked_count }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-tighter {{ $slot->status == 'open' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $slot->status == 'open' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                                        {{ $slot->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                            onclick="openEditModal({{ $slot->id }}, '{{ $slot->date }}', '{{ $slot->session }}', '{{ $slot->start_time }}', '{{ $slot->end_time }}', {{ $slot->capacity }}, '{{ $slot->status }}')"
                                            class="p-2 text-indigo-600 transition-all rounded-lg bg-indigo-50 hover:bg-indigo-600 hover:text-white">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('admin.slots.destroy', $slot->id) }}" method="POST"
                                            onsubmit="return confirm('Delete this slot?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button
                                                class="p-2 transition-all rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="mb-3 text-5xl text-gray-200 fa-solid fa-folder-open"></i>
                                        <p class="font-medium text-gray-400">No slot data available</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="px-8 py-6 border-t border-gray-100 bg-gray-50/50">
                <div
                    class="flex items-center justify-between px-4 py-4 mt-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                    <div class="flex justify-start flex-1">
                        @if ($slots->onFirstPage())
                            <span
                                class="px-3 py-2 text-sm font-medium text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </span>
                        @else
                            <a href="{{ $slots->previousPageUrl() }}"
                                class="px-3 py-2 text-sm font-medium text-gray-700 transition-all bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                        @endif
                    </div>

                    <div class="items-center hidden gap-1 md:flex">
                        @foreach ($slots->getUrlRange(1, $slots->lastPage()) as $page => $url)
                            @if ($page == $slots->currentPage())
                                <span
                                    class="z-10 px-4 py-2 text-sm font-bold text-white bg-blue-600 border border-blue-600 rounded-lg shadow-sm">
                                    {{ $page }}
                                </span>
                            @elseif ($page == 1 || $page == $slots->lastPage() || abs($page - $slots->currentPage()) <= 1)
                                <a href="{{ $url }}"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 transition-all bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    {{ $page }}
                                </a>
                            @elseif ($page == 2 || $page == $slots->lastPage() - 1)
                                <span class="px-2 text-gray-400">...</span>
                            @endif
                        @endforeach
                    </div>

                    <div class="flex justify-end flex-1">
                        @if ($slots->hasMorePages())
                            <a href="{{ $slots->nextPageUrl() }}"
                                class="px-3 py-2 text-sm font-medium text-gray-700 transition-all bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @else
                            <span
                                class="px-3 py-2 text-sm font-medium text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mt-3 text-center">
                    <p class="text-xs italic text-gray-500">
                        Showing <span class="font-semibold">{{ $slots->firstItem() }}</span> to <span
                            class="font-semibold">{{ $slots->lastItem() }}</span> of <span
                            class="font-semibold">{{ $slots->total() }}</span> slots
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div id="editModal"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 ease-out">

        {{-- Modal Card --}}
        <div id="modalCard"
            class="relative w-full max-w-xl p-8 transition-all duration-300 ease-out transform scale-95 bg-white shadow-2xl opacity-0 rounded-3xl">

            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Edit Session Slot</h2>
                    <p class="text-sm text-gray-500">Modify the existing slot details below.</p>
                </div>
                <button onclick="closeEditModal()" class="text-gray-400 transition-colors hover:text-gray-600">
                    <i class="text-2xl fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editForm" method="POST" class="space-y-5">
                @csrf @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    {{-- Input fields sama seperti sebelumnya --}}
                    <div class="col-span-1 space-y-1">
                        <label class="ml-1 text-xs font-bold text-gray-500 uppercase">Date</label>
                        <input type="date" name="date" id="edit_date"
                            class="w-full px-4 py-2 border border-gray-200 outline-none rounded-xl focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="col-span-1 space-y-1">
                        <label class="ml-1 text-xs font-bold text-gray-500 uppercase">Session</label>
                        <select name="session" id="edit_session"
                            class="w-full px-4 py-2 border border-gray-200 outline-none rounded-xl focus:ring-2 focus:ring-indigo-500">
                            <option value="Morning">Morning</option>
                            <option value="Afternoon">Afternoon</option>
                            <option value="Evening">Evening</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="ml-1 text-xs font-bold text-gray-500 uppercase">Start Time</label>
                        <input type="time" name="start_time" id="edit_start_time"
                            class="w-full px-4 py-2 border border-gray-200 outline-none rounded-xl focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="space-y-1">
                        <label class="ml-1 text-xs font-bold text-gray-500 uppercase">End Time</label>
                        <input type="time" name="end_time" id="edit_end_time"
                            class="w-full px-4 py-2 border border-gray-200 outline-none rounded-xl focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="space-y-1">
                        <label class="ml-1 text-xs font-bold text-gray-500 uppercase">Capacity</label>
                        <input type="number" name="capacity" id="edit_capacity"
                            class="w-full px-4 py-2 border border-gray-200 outline-none rounded-xl focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="space-y-1">
                        <label class="ml-1 text-xs font-bold text-gray-500 uppercase">Status</label>
                        <select name="status" id="edit_status"
                            class="w-full px-4 py-2 border border-gray-200 outline-none rounded-xl focus:ring-2 focus:ring-indigo-500">
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" onclick="closeEditModal()"
                        class="px-6 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-800">Cancel</button>
                    <button type="submit"
                        class="px-8 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg transition-all active:scale-95">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, date, session, start_time, end_time, capacity, status) {
            const modal = document.getElementById('editModal');
            const card = document.getElementById('modalCard');
            const form = document.getElementById('editForm');

            // 1. Fill Data
            document.getElementById('edit_date').value = date;
            document.getElementById('edit_session').value = session;
            document.getElementById('edit_start_time').value = start_time;
            document.getElementById('edit_end_time').value = end_time;
            document.getElementById('edit_capacity').value = capacity;
            document.getElementById('edit_status').value = status;
            form.action = `/admin/slots/${id}`;

            // 2. Trigger Animation (Smooth Open)
            // Hilangkan pembatas klik & munculkan overlay
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');

            // Munculkan card dengan efek scale up
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            const card = document.getElementById('modalCard');

            // 1. Trigger Animation (Smooth Close)
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.remove('opacity-100');
                modal.classList.add('opacity-0', 'pointer-events-none');
            }, 200); // Durasi ini sedikit lebih cepat dari transisi CSS agar terasa snappy
        }

        // Close on overlay click
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
@endsection

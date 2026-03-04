@extends('admin.layout.app')

@section('title', 'Slot Calendar')

@section('content')
    <div class="w-full space-y-6">

        {{-- Header Section --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-800">Visual Schedule</h2>
                <p class="text-sm text-gray-500">View and manage all booking slots in a monthly calendar format.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.slots.index') }}"
                    class="inline-flex items-center px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                    <i class="mr-2 fa-solid fa-list"></i> List View
                </a>
            </div>
        </div>

        {{-- Calendar Card --}}
        {{-- Menggunakan Arbitrary Variants untuk styling internal FullCalendar --}}
        <div
            class="p-6 bg-white border border-gray-100 shadow-sm rounded-3xl
        [&_.fc-toolbar-title]:text-xl [&_.fc-toolbar-title]:font-bold [&_.fc-toolbar-title]:text-gray-800
        [&_.fc-button]:bg-white [&_.fc-button]:border-gray-200 [&_.fc-button]:text-gray-700 [&_.fc-button]:font-bold [&_.fc-button]:rounded-xl [&_.fc-button]:px-4 [&_.fc-button]:py-2 [&_.fc-button]:transition-all
        [&_.fc-button:hover]:bg-gray-50 [&_.fc-button:hover]:border-gray-300 [&_.fc-button:hover]:text-gray-900
        [&_.fc-button-active]:!bg-indigo-600 [&_.fc-button-active]:!border-indigo-600 [&_.fc-button-active]:!text-white
        [&_.fc-col-header-cell-cushion]:text-xs [&_.fc-col-header-cell-cushion]:font-bold [&_.fc-col-header-cell-cushion]:uppercase [&_.fc-col-header-cell-cushion]:text-gray-400
        [&_.fc-daygrid-day-number]:text-sm [&_.fc-daygrid-day-number]:font-medium [&_.fc-daygrid-day-number]:p-2
        [&_.fc-theme-standard]:border-gray-100
        [&_.fc-scrollgrid]:rounded-2xl [&_.fc-scrollgrid]:overflow-hidden [&_.fc-scrollgrid]:border-gray-100">

            <div id='calendar' class="min-h-[600px]"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                height: 'auto',
                events: @json($events),
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },
                // Styling event secara dinamis via JS menggunakan Tailwind ClassList
                eventDidMount: function(info) {
                    const session = info.event.extendedProps.session;

                    // Reset default styles
                    info.el.style.border = 'none';
                    info.el.classList.add('rounded-lg', 'px-2', 'py-1', 'text-xs', 'font-bold',
                        'border-l-4', 'shadow-sm', 'mb-1');

                    if (session === 'Morning') {
                        info.el.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-400');
                    } else if (session === 'Afternoon') {
                        info.el.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-400');
                    } else {
                        info.el.classList.add('bg-indigo-50', 'text-indigo-700', 'border-indigo-400');
                    }
                }
            });

            calendar.render();
        });
    </script>
@endsection

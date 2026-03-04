{{-- Sidebar Customer --}}
<aside id="sidebar" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:block
           w-72 h-[calc(100vh-2rem)] my-4 ml-4">
    {{-- Margin & Height calculation --}}

    <div class="flex flex-col h-full px-6 py-8 bg-white border border-gray-100 shadow-sm rounded-[2.5rem]">

        {{-- LOGO SECTION --}}
        <div class="flex items-center px-2 mb-10">
            <a href="{{ route('customer.dashboard') }}" class="flex items-center space-x-3 group">
                <div
                    class="p-2.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg shadow-blue-200 group-hover:scale-110 transition-transform duration-300">
                    <i class="text-lg text-white fa-solid fa-leaf"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-black leading-none tracking-tight text-gray-800">Beam<span
                            class="text-blue-600">App</span></span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Customer
                        Portal</span>
                </div>
            </a>
        </div>

        {{-- NAVIGATION --}}
        <div class="flex-1 pr-2 space-y-8 overflow-y-auto custom-scrollbar">
            {{-- Services Group --}}
            <div>
                <p class="px-4 mb-4 text-[10px] font-extrabold text-gray-400 uppercase tracking-[0.2em]">Services</p>
                <nav class="space-y-2">
                    <a href="{{ route('customer.dashboard') }}"
                        class="group flex items-center gap-3 px-4 py-3.5 text-sm font-bold transition-all rounded-2xl
                        {{ request()->routeIs('customer.dashboard') ? 'bg-blue-600 text-white shadow-xl shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-blue-600' }}">
                        <i class="w-5 text-lg text-center fa-solid fa-house-chimney"></i>
                        <span>Home</span>
                    </a>

                    <a href="{{ route('customer.booking.create') }}"
                        class="group flex items-center gap-3 px-4 py-3.5 text-sm font-bold transition-all rounded-2xl
                        {{ request()->routeIs('customer.booking.create') ? 'bg-blue-600 text-white shadow-xl shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-blue-600' }}">
                        <i class="w-5 text-lg text-center fa-solid fa-calendar-plus"></i>
                        <span>New Booking</span>
                    </a>
                </nav>
            </div>

            {{-- Personal Group --}}
            <div>
                <p class="px-4 mb-4 text-[10px] font-extrabold text-gray-400 uppercase tracking-[0.2em]">Personal</p>
                <nav class="space-y-2">
                    <a href="{{ route('customer.profile') }}"
                        class="group flex items-center gap-3 px-4 py-3.5 text-sm font-bold transition-all rounded-2xl
                        {{ request()->routeIs('customer.profile*') ? 'bg-blue-600 text-white shadow-xl shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-blue-600' }}">
                        <i class="w-5 text-lg text-center fa-solid fa-circle-user"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="{{ route('customer.history') }}" {{-- Hubungkan ke route history nanti --}}
                        class="group flex items-center gap-3 px-4 py-3.5 text-sm font-bold text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-all rounded-2xl">
                        <i class="w-5 text-lg text-center fa-solid fa-receipt"></i>
                        <span>History</span>
                    </a>
                </nav>
            </div>
        </div>

        {{-- USER INFO & LOGOUT --}}
        <div class="pt-6 mt-auto border-t border-gray-50">
            <form action="{{ route('customer.logout') }}" method="POST">
                @csrf
                <button
                    class="flex items-center w-full gap-3 px-4 py-3.5 text-sm font-bold text-gray-400 transition-all rounded-2xl hover:bg-red-50 hover:text-red-500 group">
                    <i
                        class="w-5 text-center transition-transform fa-solid fa-right-from-bracket group-hover:translate-x-1"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>

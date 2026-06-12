<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Beam Admin</title>
    @vite(['resources/css/app.css'])

    <script src="https://cdn.jsdelivr.net/優美/tailwindcss@3.4.1"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4 font-sans antialiased bg-slate-50">

    <div class="max-w-md w-full bg-white border border-slate-100 shadow-2xl rounded-[2rem] p-8 text-center space-y-6">

        {{-- DYNAMIC ICON IDENTIFIER BASED ON STATUS CODE --}}
        <div class="flex items-center justify-center w-16 h-16 mx-auto rounded-2xl">
            @switch(trim($__env->yieldContent('code')))
                @case('404')
                    <div class="flex items-center justify-center w-16 h-16 text-blue-600 bg-blue-50 rounded-2xl animate-bounce">
                        <i class="text-2xl fa-solid fa-compass"></i>
                    </div>
                @break

                @case('403')
                @case('401')
                    <div class="flex items-center justify-center w-16 h-16 bg-amber-50 rounded-2xl text-amber-500">
                        <i class="text-2xl fa-solid fa-user-shield"></i>
                    </div>
                @break

                @case('419')
                    <div class="flex items-center justify-center w-16 h-16 text-orange-500 bg-orange-50 rounded-2xl">
                        <i class="text-2xl fa-solid fa-clock-rotate-left"></i>
                    </div>
                @break

                @default
                    <div class="flex items-center justify-center w-16 h-16 bg-rose-50 rounded-2xl text-rose-500">
                        <i class="text-2xl fa-solid fa-triangle-exclamation"></i>
                    </div>
            @endswitch
        </div>

        {{-- ERROR CONTEXT --}}
        <div class="space-y-2">
            <h1 class="text-6xl font-black tracking-tight text-slate-800">@yield('code')</h1>
            <h2 class="text-xl font-extrabold text-slate-700">@yield('title')</h2>

            <p class="px-2 text-sm font-medium leading-relaxed text-slate-400">
                @hasSection('custom_message')
                    @yield('custom_message')
                @else
                    @yield('message'). Please verify the action or return to the main operational control dashboard.
                @endif
            </p>
        </div>

        {{-- ACTION BUTTON --}}
        {{-- <div class="pt-2">
            @if (trim($__env->yieldContent('code')) === '419' || trim($__env->yieldContent('code')) === '401')
                <a href="{{ route('admin.login') }}"
                    class="block w-full py-4 text-sm font-black tracking-widest text-white uppercase transition-all bg-blue-600 shadow-xl rounded-2xl shadow-blue-100 hover:bg-blue-700 active:scale-95">
                    Return to Login
                </a>
            @else
                <a href="{{ route('admin.dashboard') }}"
                    class="block w-full py-4 text-sm font-black tracking-widest text-white uppercase transition-all shadow-xl bg-slate-900 rounded-2xl hover:bg-black active:scale-95">
                    Back to Dashboard
                </a>
            @endif
        </div> --}}
    </div>

</body>

</html>

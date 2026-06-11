@extends('admin.layout.app')

@section('content')
    <div class="w-full px-2 py-4 mx-auto space-y-6 sm:px-4 md:px-6 lg:py-8">

        {{-- COMPACT HEADER --}}
        <div class="px-6 py-6 bg-white border border-slate-100 shadow-sm rounded-[1.5rem] sm:rounded-[2.5rem] md:px-10">
            <h3 class="text-2xl font-black tracking-tighter text-slate-800 md:text-3xl">
                Edit <span class="text-blue-600">Profile</span>
            </h3>
            <p class="mt-1 text-xs font-bold tracking-widest uppercase text-slate-400">
                Update your account information and security settings
            </p>
        </div>

        {{-- MAIN CONTAINER --}}
        <div class="space-y-6">

            {{-- PERSONAL INFORMATION FORM --}}
            <form action="{{ route('admin.profile.update', $user->id) }}" method="POST"
                class="bg-white p-6 sm:p-8 md:p-10 border border-slate-100 shadow-sm rounded-[1.5rem] sm:rounded-[2.5rem]">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-3 mb-8">
                    <div class="flex items-center justify-center w-10 h-10 text-blue-600 bg-blue-50 rounded-xl">
                        <i class="text-sm fa-solid fa-user-gear"></i>
                    </div>
                    <h4 class="text-lg font-black text-slate-800">Account Information</h4>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-2">
                    <div class="group">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">Full
                            Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-5 py-4 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600">
                        @error('name')
                            <p class="flex items-center gap-1 mt-2 ml-1 text-xs font-bold text-red-500">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="group">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">Email
                            Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full px-5 py-4 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600">
                        @error('email')
                            <p class="flex items-center gap-1 mt-2 ml-1 text-xs font-bold text-red-500">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="pt-8">
                    <button type="submit"
                        class="w-full px-10 py-4 text-sm font-black tracking-widest text-white uppercase transition-all bg-blue-600 shadow-xl shadow-blue-100 rounded-2xl md:w-fit hover:bg-blue-700 active:scale-95">
                        Save Changes
                    </button>
                </div>
            </form>

            {{-- PASSWORD UPDATE FORM --}}
            <form action="{{ route('admin.profile.password') }}" method="POST"
                class="bg-white p-6 sm:p-8 md:p-10 border border-slate-100 shadow-sm rounded-[1.5rem] sm:rounded-[2.5rem]">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-3 mb-8">
                    <div class="flex items-center justify-center w-10 h-10 bg-amber-50 rounded-xl text-amber-600">
                        <i class="text-sm fa-solid fa-lock"></i>
                    </div>
                    <h4 class="text-lg font-black text-slate-800">Password Security</h4>
                </div>

                <div class="space-y-5 sm:space-y-6">

                    {{-- CURRENT PASSWORD --}}
                    <div class="group">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-amber-600">
                            Current Password
                        </label>
                        <div class="relative flex items-center">
                            <input type="password" id="current_password" name="current_password" required
                                class="w-full px-5 py-4 pr-12 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-amber-100/50 focus:border-amber-500">
                            <button type="button" onclick="togglePassword('current_password', 'icon-current')"
                                class="absolute z-10 mt-2 right-4 text-slate-400 hover:text-amber-600 focus:outline-none">
                                <i id="icon-current" class="text-base fa-solid fa-eye"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="flex items-center gap-1 mt-2 ml-1 text-xs font-bold text-red-500">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- NEW & CONFIRM PASSWORD --}}
                    <div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-2">

                        {{-- NEW PASSWORD --}}
                        <div class="group">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">
                                New Password
                            </label>
                            <div class="relative flex items-center">
                                <input type="password" id="password" name="password" required minlength="8"
                                    class="w-full px-5 py-4 pr-12 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600">
                                <button type="button" onclick="togglePassword('password', 'icon-new')"
                                    class="absolute z-10 mt-2 right-4 text-slate-400 hover:text-blue-600 focus:outline-none">
                                    <i id="icon-new" class="text-base fa-solid fa-eye"></i>
                                </button>
                            </div>
                            {{-- Minimum Requirement --}}
                            <p id="length-info"
                                class="mt-2 ml-1 text-xs font-bold text-slate-400 transition-colors flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-info text-[10px]"></i> Minimum 8 characters
                            </p>
                        </div>

                        {{-- CONFIRM PASSWORD --}}
                        <div class="group">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">
                                Confirm New Password
                            </label>
                            <div class="relative flex items-center">
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                    class="w-full px-5 py-4 pr-12 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600">
                                <button type="button" onclick="togglePassword('password_confirmation', 'icon-confirm')"
                                    class="absolute z-10 mt-2 right-4 text-slate-400 hover:text-blue-600 focus:outline-none">
                                    <i id="icon-confirm" class="text-base fa-solid fa-eye"></i>
                                </button>
                            </div>
                            {{-- Match Status --}}
                            <p id="match-info"
                                class="mt-2 ml-1 text-xs font-bold text-slate-400 transition-colors flex items-center gap-1.5 opacity-0">
                                <i id="match-icon" class="fa-solid fa-circle-info text-[10px]"></i> <span
                                    id="match-text">Passwords do not match</span>
                            </p>
                        </div>

                    </div>
                    @error('password')
                        <p class="flex items-center gap-1 mt-2 ml-1 text-xs font-bold text-red-500">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror

                    <div class="flex flex-col-reverse gap-4 pt-4 sm:flex-row sm:items-center">
                        <a href="{{ route('admin.profile') }}"
                            class="flex items-center justify-center px-8 py-4 text-sm font-black tracking-widest uppercase transition-all text-slate-400 hover:text-slate-600">
                            Cancel
                        </a>
                        <button type="submit" id="btn-submit-password"
                            class="w-full px-10 py-4 text-sm font-black tracking-widest text-white uppercase transition-all shadow-xl bg-slate-900 rounded-2xl md:w-fit hover:bg-black active:scale-95 shadow-slate-200">
                            Update Password
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="pt-4 text-center">
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em]">
                Encrypted Security System • SSL Active
            </p>
        </div>
    </div>

    <script>
        // Toggle view/hide password inputs
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Live Dynamic Form Validation
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');
        const lengthInfo = document.getElementById('length-info');
        const matchInfo = document.getElementById('match-info');
        const matchIcon = document.getElementById('match-icon');
        const matchText = document.getElementById('match-text');

        function validatePasswords() {
            const passVal = passwordInput.value;
            const confirmVal = confirmInput.value;

            // 1. Minimum 8 Characters Checker
            if (passVal.length >= 8) {
                lengthInfo.classList.remove('text-slate-400', 'text-red-500');
                lengthInfo.classList.add('text-emerald-600');
                lengthInfo.querySelector('i').className = 'fa-solid fa-circle-check text-[10px]';
            } else if (passVal.length > 0) {
                lengthInfo.classList.remove('text-slate-400', 'text-emerald-600');
                lengthInfo.classList.add('text-red-500');
                lengthInfo.querySelector('i').className = 'fa-solid fa-circle-xmark text-[10px]';
            } else {
                lengthInfo.className =
                    'mt-2 ml-1 text-xs font-bold text-slate-400 transition-colors flex items-center gap-1.5';
                lengthInfo.querySelector('i').className = 'fa-solid fa-circle-info text-[10px]';
            }

            // 2. Password Similarity Match Checker
            if (confirmVal.length > 0) {
                matchInfo.classList.remove('opacity-0');
                if (passVal === confirmVal) {
                    matchInfo.classList.remove('text-slate-400', 'text-red-500');
                    matchInfo.classList.add('text-emerald-600');
                    matchIcon.className = 'fa-solid fa-circle-check text-[10px]';
                    matchText.innerText = 'Passwords match';
                } else {
                    matchInfo.classList.remove('text-slate-400', 'text-emerald-600');
                    matchInfo.classList.add('text-red-500');
                    matchIcon.className = 'fa-solid fa-circle-xmark text-[10px]';
                    matchText.innerText = 'Passwords do not match';
                }
            } else {
                matchInfo.classList.add('opacity-0');
            }
        }

        passwordInput.addEventListener('input', validatePasswords);
        confirmInput.addEventListener('input', validatePasswords);
    </script>
@endsection

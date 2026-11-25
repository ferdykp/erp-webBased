@extends('layouts.master')

@section('title', 'Customer sign in · BeamCustom')

@section('content')
    <main class="relative flex min-h-[100dvh] items-center justify-center overflow-hidden bg-slate-100 px-4 py-8 sm:px-6 lg:px-8">
        <div aria-hidden="true" class="pointer-events-none absolute -left-32 -top-40 h-96 w-96 rounded-full bg-cyan-200/50 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-48 -right-24 h-[28rem] w-[28rem] rounded-full bg-blue-200/50 blur-3xl"></div>

        <div class="relative grid w-full max-w-6xl overflow-hidden rounded-[2rem] border border-white/80 bg-white shadow-[0_32px_100px_-40px_rgba(15,23,42,0.35)] lg:min-h-[690px] lg:grid-cols-[1.05fr_0.95fr]">
            <section class="relative hidden flex-col justify-between overflow-hidden bg-slate-950 p-10 text-white sm:p-12 lg:flex xl:p-16">
                <div aria-hidden="true" class="absolute inset-0 bg-[radial-gradient(ellipse_at_15%_15%,rgba(6,182,212,0.34),transparent_45%),radial-gradient(ellipse_at_90%_85%,rgba(37,99,235,0.34),transparent_40%)]"></div>
                <div aria-hidden="true" class="absolute inset-0 opacity-[0.12] [background-image:linear-gradient(rgba(255,255,255,.18)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.18)_1px,transparent_1px)] [background-size:48px_48px] [mask-image:linear-gradient(to_bottom,black,transparent_80%)]"></div>

                <div class="relative z-10">
                    <a href="{{ route('landing') }}" class="inline-flex items-center rounded-xl bg-white px-3 py-2.5">
                        <img src="{{ asset('img/logo-txt-removebg.png') }}" alt="BeamCustom" class="h-8 w-auto object-contain">
                    </a>
                </div>

                <div class="relative z-10 max-w-lg py-14">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-semibold tracking-wide text-cyan-100 backdrop-blur">
                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-300 shadow-[0_0_12px_rgba(103,232,249,0.8)]"></span>
                        BEAMCUSTOM · CUSTOMER PORTAL
                    </div>
                    <h1 class="text-4xl font-semibold leading-[1.1] tracking-tight xl:text-5xl">Your service journey,<br><span class="text-cyan-300">all in one place.</span></h1>
                    <p class="mt-6 max-w-md text-base leading-7 text-slate-300">Create service requests, follow order progress, and find your account information in one secure portal.</p>
                </div>

                <div class="relative z-10 flex items-center justify-between border-t border-white/15 pt-5 text-xs text-slate-400">
                    <span>Customer portal</span>
                    <span class="inline-flex items-center gap-2"><svg class="h-4 w-4 text-cyan-300" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 5 6v5c0 4.6 3 8.1 7 10 4-1.9 7-5.4 7-10V6l-7-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>Protected sign in</span>
                </div>
            </section>

            <section class="flex items-center justify-center px-6 py-10 sm:px-12 lg:px-14 xl:px-20">
                <div class="w-full max-w-md">
                    <a href="{{ route('landing') }}" class="mb-10 inline-flex items-center rounded-lg bg-white px-2 py-1.5 lg:hidden">
                        <img src="{{ asset('img/logo-txt-removebg.png') }}" alt="BeamCustom" class="h-8 w-auto object-contain">
                    </a>
                    <div class="mb-8">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700">Customer portal</p>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Welcome back</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-500">Sign in to manage and track your BeamCustom service requests.</p>
                    </div>

                    <form action="{{ route('customer.login') }}" method="POST" class="space-y-5">
                        @csrf
                        <div>
                            <label for="customer_email" class="mb-2 block text-sm font-semibold text-slate-700">Email address</label>
                            <input id="customer_email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="name@example.com"
                                @class(['w-full rounded-xl border bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10', 'border-rose-400' => $errors->has('email'), 'border-slate-200' => !$errors->has('email')])>
                            @error('email')<p class="mt-2 text-sm font-medium text-rose-600" role="alert">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label for="customer_password" class="text-sm font-semibold text-slate-700">Password</label>
                                <a href="{{ route('customer.password.request') }}" class="text-xs font-semibold text-cyan-800 transition hover:text-cyan-950 hover:underline">Forgot password?</a>
                            </div>
                            <div class="relative">
                                <input id="customer_password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 pr-16 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10">
                                <button type="button" data-password-toggle="customer_password" aria-label="Show password" aria-pressed="false" class="absolute inset-y-0 right-3 my-auto rounded-lg px-2 text-xs font-semibold text-slate-500 transition hover:bg-slate-200/70 hover:text-slate-900 focus-visible:outline-cyan-700">Show</button>
                            </div>
                        </div>

                        <label class="flex cursor-pointer items-center gap-3 text-sm text-slate-600">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-slate-300 text-cyan-700 focus:ring-cyan-600">
                            <span>Remember this device</span>
                        </label>

                        <button type="submit" class="group flex w-full items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition hover:bg-cyan-800 focus-visible:outline-cyan-700 active:scale-[0.99]">
                            Sign in to customer portal
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </form>

                    <p class="mt-7 text-center text-sm text-slate-500">New to BeamCustom? <a href="{{ route('customer.register') }}" class="font-semibold text-cyan-800 transition hover:text-cyan-950 hover:underline">Create an account</a></p>
                    <p class="mt-6 border-t border-slate-100 pt-5 text-center text-xs leading-5 text-slate-400">Need help accessing your account?<br><span class="font-medium text-slate-500">Contact your BeamCustom representative.</span></p>
                </div>
            </section>
        </div>
    </main>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);
                const visible = input.type === 'password';
                input.type = visible ? 'text' : 'password';
                button.textContent = visible ? 'Hide' : 'Show';
                button.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
                button.setAttribute('aria-pressed', String(visible));
            });
        });
    </script>
@endsection

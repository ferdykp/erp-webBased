@extends('layouts.master')

@section('content')
    <div class="min-h-screen overflow-x-hidden font-sans bg-slate-50 text-slate-900">
        <nav
            class="sticky top-0 z-50 flex items-center justify-between px-6 py-4 border-b bg-white/80 backdrop-blur-md border-slate-100 md:px-12">
            <div class="flex items-center gap-3">
                {{-- <div  class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-200 animate-[spin_8s_linear_infinite]"> --}}
                <div class="flex items-center justify-center h-10 text-white w-25 rounded-xl ">

                    {{-- <i class="text-xl fa-solid fa-atom"></i> --}}
                    <img src="{{ asset('img/logo-txt-removebg.png') }}" class="h-10 w-25" alt="">
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-black leading-none tracking-tighter text-slate-800">E-BEAM</span>
                    <span class="text-[10px] font-bold tracking-[0.3em] text-blue-600 uppercase leading-none">Nuctech
                        System</span>
                </div>
            </div>

            <div>
                <a href="{{ route('admin.login') }}"
                    class="px-6 py-2.5 text-xs font-black tracking-widest text-blue-600 border-2 border-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all duration-300">
                    SIGN IN
                </a>
            </div>
        </nav>

        <main class="relative px-6 pt-12 pb-24 md:px-12 lg:pt-20 lg:pb-32">
            <div
                class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-[500px] h-[500px] bg-blue-100/40 rounded-full blur-[100px] -z-10">
            </div>
            <div
                class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-[400px] h-[400px] bg-indigo-100/30 rounded-full blur-[80px] -z-10">
            </div>

            <div class="grid items-center gap-16 mx-auto max-w-7xl lg:grid-cols-2">
                <div class="order-2 lg:order-1">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 mb-8 text-[11px] font-black tracking-[0.2em] text-blue-600 uppercase bg-blue-50 rounded-full">
                        <span class="relative flex w-2 h-2">
                            <span
                                class="absolute inline-flex w-full h-full bg-blue-400 rounded-full opacity-75 animate-ping"></span>
                            <span class="relative inline-flex w-2 h-2 bg-blue-600 rounded-full"></span>
                        </span>
                        Centralized Irradiation Operations Management System
                    </div>

                    {{-- <h1 class="text-5xl font-black leading-[1.1] text-slate-900 md:text-7xl mb-8 tracking-tight">
                        Precision <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-500">Electron
                            Beam</span> <br>
                        Technology.
                    </h1> --}}
                    <h1 class="text-5xl font-black leading-[1.1] text-slate-900 md:text-7xl mb-8 tracking-tight">
                        Integrated <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-500">
                            E-Beam Management System
                        </span> <br>
                        {{-- Platform --}}
                    </h1>

                    {{-- <p class="max-w-lg mb-12 text-lg leading-relaxed text-slate-500">
                        Platform digital terintegrasi untuk pemesanan layanan iradiasi berkas elektron. Sterilisasi produk
                        medis, modifikasi material, dan pengawetan pangan dengan standar akurasi dosis tinggi.
                    </p> --}}
                    <p class="max-w-lg mb-12 text-lg leading-relaxed text-slate-500">
                        A centralized digital system designed to manage irradiation scheduling,
                        operational monitoring, and structured reporting within electron beam
                        facilities. Built for industrial reliability, operational transparency,
                        and high-precision control.
                    </p>

                    <div class="flex flex-col gap-5 sm:flex-row">
                        <a href="{{ route('customer.login') }}"
                            class="flex items-center justify-center gap-3 px-10 py-5 text-sm font-black text-white transition-all duration-300 bg-blue-600 shadow-xl group rounded-2xl shadow-blue-200 hover:bg-blue-700 hover:-translate-y-1 active:scale-95">
                            BOOK A SESSION
                            <i class="transition-transform fa-solid fa-arrow-right group-hover:translate-x-1"></i>
                        </a>
                    </div>

                    <div class="flex items-center gap-10 pt-10 mt-16 border-t border-slate-100">
                        <div class="flex flex-col">
                            <span class="text-3xl font-black leading-none text-slate-800">24h Operational</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Continuous
                                Operational Readiness</span>
                        </div>
                        <div class="w-px h-12 bg-slate-200"></div>
                        <div class="flex flex-col">
                            <span class="text-3xl font-black leading-none text-slate-800">99.9% Dose Accuracy</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">High-Precision
                                Dose Control
                                Accuracy</span>
                        </div>
                    </div>
                </div>

                <div class="relative order-1 lg:order-2">
                    <div
                        class="absolute inset-0 border-[40px] border-slate-100/50 rounded-[4rem] -m-10 -z-10 animate-pulse">
                    </div>

                    <div class="relative bg-white p-5 rounded-[3.5rem] shadow-2xl border border-slate-100 group">
                        <div class="relative rounded-[2.8rem] overflow-hidden aspect-[4/5] lg:aspect-square">
                            <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&q=80&w=1000"
                                alt="Technology"
                                class="w-full h-full object-cover grayscale-[0.2] group-hover:scale-105 transition-transform duration-700">

                            <div class="absolute inset-0 bg-gradient-to-t from-blue-900/40 to-transparent"></div>
                        </div>

                        <div
                            class="absolute -bottom-10 -right-6 md:right-10 bg-white p-6 rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] border border-slate-50 flex items-center gap-4 max-w-[260px]">
                            <div
                                class="flex items-center justify-center flex-shrink-0 w-14 h-14 bg-emerald-50 text-emerald-500 rounded-2xl">
                                <i class="text-2xl fa-solid fa-shield-virus"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</p>
                                <p class="text-sm font-black leading-tight text-slate-900">ISO Certified Sterilization
                                    System</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <section class="px-6 py-24 bg-white md:px-12">
            <div class="mx-auto max-w-7xl">
                <div class="max-w-3xl mb-16">
                    <h2 class="mb-6 text-4xl font-black text-slate-900">
                        Integrated Booking & Monitoring Platform
                    </h2>
                    <p class="text-lg leading-relaxed text-slate-500">
                        An integrated platform designed to manage structured irradiation scheduling,
                        real-time operational monitoring, and digital reporting within a unified
                        control dashboard. Developed to enhance transparency, efficiency,
                        and operational precision across irradiation facilities. </p>
                </div>

                <div class="grid gap-10 md:grid-cols-3">
                    <div class="p-8 border bg-slate-50 rounded-3xl border-slate-100">
                        <i class="mb-6 text-3xl text-blue-600 fa-solid fa-calendar-check"></i>
                        <h3 class="mb-3 text-lg font-black">Structured Session Scheduling</h3>
                        <p class="text-sm leading-relaxed text-slate-500">
                            Session-based allocation framework with automated conflict
                            validation, real-time availability control, and secure
                            digital booking confirmation. </p>
                    </div>

                    <div class="p-8 border bg-slate-50 rounded-3xl border-slate-100">
                        <i class="mb-6 text-3xl text-indigo-600 fa-solid fa-chart-line"></i>
                        <h3 class="mb-3 text-lg font-black">Live Operational Monitoring</h3>
                        <p class="text-sm leading-relaxed text-slate-500">
                            Continuous visibility of machine status, active irradiation
                            sessions, and facility utilization metrics through
                            real-time operational tracking.
                    </div>

                    <div class="p-8 border bg-slate-50 rounded-3xl border-slate-100">
                        <i class="mb-6 text-3xl text-emerald-600 fa-solid fa-file-export"></i>
                        <h3 class="mb-3 text-lg font-black">Compliance & Documentation Module</h3>
                        <p class="text-sm leading-relaxed text-slate-500">
                            Automated session documentation, structured report
                            generation, and comprehensive audit trail management
                            to support regulatory and internal compliance standards. </p>
                    </div>
                </div>
            </div>
        </section>
        <section class="px-6 py-24 bg-slate-50 md:px-12">
            <div class="mx-auto text-center max-w-7xl">
                <h2 class="mb-16 text-4xl font-black text-slate-900">
                    How The System Works
                </h2>

                <div class="grid gap-12 text-left md:grid-cols-4">
                    <div>
                        <span class="text-5xl font-black text-blue-600">01</span>
                        <h4 class="mt-4 mb-3 font-black">Secure Authentication
                        </h4>
                        <p class="text-sm text-slate-500">
                            Authorized personnel access the system through
                            role-based authentication and credential validation. </p>
                    </div>

                    <div>
                        <span class="text-5xl font-black text-blue-600">02</span>
                        <h4 class="mt-4 mb-3 font-black">Session Allocation
                        </h4>
                        <p class="text-sm text-slate-500">
                            Available irradiation slots are digitally structured
                            and allocated based on controlled scheduling parameters. </p>
                    </div>

                    <div>
                        <span class="text-5xl font-black text-blue-600">03</span>
                        <h4 class="mt-4 mb-3 font-black">Digital Verification
                        </h4>
                        <p class="text-sm text-slate-500">
                            QR-based confirmation ensures booking validation
                            and controlled facility access. </p>
                    </div>

                    <div>
                        <span class="text-5xl font-black text-blue-600">04</span>
                        <h4 class="mt-4 mb-3 font-black">Operational Execution & Documentation
                        </h4>
                        <p class="text-sm text-slate-500">
                            Sessions are monitored in real time and recorded
                            for structured reporting and post-operation review. </p>
                    </div>
                </div>
            </div>
        </section>
        <section class="px-6 py-24 bg-white md:px-12">
            <div class="grid items-center gap-16 mx-auto max-w-7xl lg:grid-cols-2">

                <div>
                    <h2 class="mb-8 text-4xl font-black text-slate-900">
                        Engineered for Industrial Reliability </h2>

                    <ul class="space-y-5 text-slate-600">
                        <li>✔ Role-based access control architecture</li>
                        <li>✔ Real-time irradiation session monitoring</li>
                        <li>✔ End-to-end operational traceability</li>
                        <li>✔ Secure authentication and data integrity protection</li>
                        <li>✔ Scalable multi-site deployment capability</li>
                        <li>✔ Structured reporting for audit readiness</li>
                    </ul>
                </div>

                <div class="p-10 text-center bg-slate-100 rounded-3xl">
                    <i class="mb-6 text-6xl text-blue-600 fa-solid fa-server"></i>
                    <p class="text-sm text-slate-500">
                        Secure web-based architecture designed for
                        high reliability and operational transparency.
                    </p>
                </div>
            </div>
        </section>
        <section class="px-6 py-20 text-white bg-gradient-to-r from-blue-600 to-indigo-600 md:px-12">
            <div class="mx-auto text-center max-w-7xl">
                <h2 class="mb-6 text-4xl font-black">
                    Optimize Irradiation Operations Through Integrated Digital Control </h2>
                <a href="{{ route('customer.login') }}"
                    class="inline-block px-10 py-4 mt-6 font-black text-blue-600 transition bg-white rounded-2xl hover:bg-slate-100">
                    ACCESS OPERATIONAL PORTAL </a>
            </div>
        </section>

        <footer class="px-6 py-10 bg-white border-t md:px-12 border-slate-100">
            <div
                class="flex flex-col items-center justify-between gap-8 mx-auto text-center max-w-7xl md:flex-row md:text-left">
                <div class="flex flex-col gap-2">
                    <span class="text-sm font-black tracking-tighter uppercase text-slate-800">NUCTECH IRRADIATION</span>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">© {{ date('Y') }}
                        Advanced Irradiation Systems & Digital Operational Management.</p>
                </div>
                <div class="flex items-center gap-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    <a href="#" class="transition-colors hover:text-blue-600">Documentation</a>
                    <a href="#" class="transition-colors hover:text-blue-600">Support</a>
                    <a href="#" class="transition-colors hover:text-blue-600">Safety Protocol</a>
                </div>
            </div>
        </footer>
    </div>
@endsection

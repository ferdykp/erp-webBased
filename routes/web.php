<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Customer Controllers
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\UserCustomController;

// Admin Controllers
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AdminProductionController;
use App\Http\Controllers\AdminProductionLineController;
use App\Http\Controllers\AdminSlotController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\DosimeterController;
use App\Http\Controllers\PorterController;
use App\Http\Controllers\WarehousePicController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC LANDING & REDIRECTOR
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('customer.dashboard');
    }
    return view('landing');
})->name('landing');


/*
|--------------------------------------------------------------------------
| 2. CUSTOMER ROUTES (GUEST & AUTHENTICATED)
|--------------------------------------------------------------------------
*/
Route::prefix('customer')->middleware('nocache')->group(function () {
    // Guest Routes
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
    Route::post('/login', [CustomerAuthController::class, 'login']);
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

    // Authenticated Customer Routes (Guard: Web)
    Route::middleware(['auth'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');

        // Profile Completion
        Route::get('/profile/complete', [CustomerProfileController::class, 'showCompleteProfile'])->name('customer.profile.complete');
        Route::post('/profile/complete', [CustomerProfileController::class, 'completeProfile'])->name('customer.profile.complete.store');

        // Booking System
        Route::get('/booking/create', [CustomerBookingController::class, 'create'])->name('customer.booking.create');
        Route::post('/booking/store', [CustomerBookingController::class, 'store'])->name('customer.booking.store');
        Route::get('/booking/{id}', [CustomerBookingController::class, 'show'])->name('customer.booking.show');
        Route::get('/booking/{id}/print', [CustomerBookingController::class, 'print'])->name('customer.booking.print');

        // General Profile & Settings
        Route::get('/profile', [UserCustomController::class, 'index'])->name('customer.profile');
        Route::get('/profile/edit', [CustomerProfileController::class, 'edit'])->name('customer.profile.edit');
        Route::put('/profile/update', [CustomerProfileController::class, 'update'])->name('customer.profile.update');
        Route::put('/profile/password', [CustomerProfileController::class, 'updatePassword'])->name('customer.profile.password');
        Route::get('/history', [UserCustomController::class, 'history'])->name('customer.history');
    });
});


/*
|--------------------------------------------------------------------------
| 3. ADMIN AUTH ROUTES (GUEST ONLY)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware('nocache')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});


/*
|--------------------------------------------------------------------------
| 4. SECURE ADMIN ROUTES (HARUS LOGIN SEBAGAI ADMIN)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:admin', 'nocache'])
    ->group(function () {

        /*
        |------------------------------------------------------------------
        | A. AKSES BERSAMA (Semua Staff Admin Tanpa Terkecuali)
        |------------------------------------------------------------------
        | Catatan: Route dengan parameter dinamis seperti {id} dipindah ke bawah 
        | agar tidak menabrak route statis (seperti /profile/create).
        */
        Route::get('/dashboard', [AdminBookingController::class, 'index'])->name('admin.dashboard');
        Route::get('/profile', [UserAdminController::class, 'index'])->name('admin.profile');
        Route::put('/profile/password', [UserAdminController::class, 'updatePassword'])->name('admin.profile.password');

        // Route::middleware('role:superadmin')

        /*
        |------------------------------------------------------------------
        | B. ROLE: PRODUCTION, MANAGER, SUPERADMIN (Sisi Teknis & Lab)
        |------------------------------------------------------------------
        */
        Route::middleware(['role:superadmin|production'])->group(function () {
            // Data Dosimeter (Sekarang aman dari Cargo Admin)
            Route::get('/dosimeter', [DosimeterController::class, 'index'])->name('admin.dosimeter.index');
            Route::get('/dosimeter/show/{booking_id}', [DosimeterController::class, 'show'])->name('admin.dosimeter.show');
            Route::post('/dosimeter/store-quantity', [DosimeterController::class, 'storeQuantity'])->name('admin.dosimeter.store-quantity');
            Route::post('/dosimeter/store-absorbance/{record_id}', [DosimeterController::class, 'storeAbsorbance'])->name('admin.dosimeter.store-absorbance');
            Route::get('/report/export-word/{id}', [ReportController::class, 'exportWord'])->name('admin.report.export-word');

            // Step 1 – Process Parameter Setting
            Route::get('/production/parameter', [AdminProductionController::class, 'parameterSetting'])->name('admin.production.parameter');
            Route::put('/production/batches/{batch}/parameter', [AdminProductionController::class, 'storeParameter'])->name('admin.production.batches.parameter.update');
            Route::post('/production/process', [AdminProductionController::class, 'processBooking'])->name('admin.production.process');

            // Step 2 – Batch Queue & Start Irradiation
            Route::get('/production/', [AdminProductionController::class, 'index'])->name('admin.production.index');
            Route::get('/production/batch-queue', [AdminProductionController::class, 'batchQueue'])->name('admin.production.batch-queue');
            Route::post('/production/batches', [AdminProductionController::class, 'storeBatch'])->name('admin.production.batches.store');
            Route::put('/production/batches/{batch}/start', [AdminProductionController::class, 'startIrradiation'])->name('admin.production.batches.start');
            Route::get('/production/offline', [AdminProductionController::class, 'offline'])->name('admin.production.offline');

            // Step 3 – Product Finish & Certificate
            Route::get('/production/finish', [AdminBookingController::class, 'finishIndex'])->name('admin.production.finish');
            Route::put('/production/batches/{batch}/finish', [AdminProductionController::class, 'finishBatch'])->name('admin.production.batches.finish');
            Route::get('/production/batches/{id}/certificate', [AdminProductionController::class, 'printCertificate'])->name('admin.production.certificate');

            // Reporting Teknis Nuctech
            // Route::get('/report/nuctech/{type}', [ReportController::class, 'nuctechView'])->name('admin.report.nuctech');
            Route::post('/production/batches/update-duration', [AdminProductionController::class, 'updateDuration'])->name('admin.production.update-duration');
        });

        /*
        |------------------------------------------------------------------
        | C. ROLE: CARGO ADMIN, MANAGER, SUPERADMIN (Logistik & Gudang)
        |------------------------------------------------------------------
        */
        Route::middleware(['role:superadmin|manager|cargo_admin|production'])->group(function () {
            // Customer Management CRUD
            Route::get('/customerList', [CustomerProfileController::class, 'index'])->name('admin.customerList.index');
            // Route::post('/customerList/create', [CustomerProfileController::class, 'create'])->name('admin.customerList.create');
            // Route::post('/customerList/store', [CustomerProfileController::class, 'store'])->name('admin.customerList.store');
            // Route::put('/customerList/{id}', [CustomerProfileController::class, 'updateAdmin'])->name('admin.customerList.update');
            // Route::delete('/customerList/{id}', [CustomerProfileController::class, 'destroy'])->name('admin.customerList.destroy');

            // Booking & Check-in
            Route::get('/bookings', [AdminBookingController::class, 'allOrder'])->name('admin.bookings');
            // Route::get('/bookings/create', [AdminBookingController::class, 'create'])->name('admin.bookings.create');
            // Route::get('/bookings/generate-code', [AdminBookingController::class, 'generateCode']);
            Route::get('/bookings/status/{status}', [AdminBookingController::class, 'statusPage'])->name('admin.bookings.status');
            // Route::post('/bookings/store', [AdminBookingController::class, 'store'])->name('admin.bookings.store');
            // Route::post('/bookings/checkin', [AdminBookingController::class, 'checkIn'])->name('admin.bookings.checkin');
            // Route::post('/bookings/{id}/placement', [AdminBookingController::class, 'storePlacement'])->name('admin.bookings.storePlacement');
            // Route::put('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus'])->name('admin.bookings.update-status');
            // Route::get('/bookings/{id}/invoice', [AdminBookingController::class, 'previewInvoice'])->name('admin.bookings.invoice');
            // Route::put('/bookings/{id}/payment-status', [AdminProductionController::class, 'updatePaymentStatus'])->name('admin.bookings.paymentStatus');
            // Route::get('/bookings/{id}/edit', [AdminBookingController::class, 'edit'])->name('admin.bookings.edit');
            // Route::put('/bookings/{id}', [AdminBookingController::class, 'update'])->name('admin.bookings.update');

            // Relokasi Produksi
            Route::post('/production/relocate', [AdminBookingController::class, 'relocatePallet'])->name('admin.production.relocate-pallet');

            // Slot Gudang Management
            Route::get('/slots', [AdminSlotController::class, 'index'])->name('admin.slots.index');
            Route::post('/slots', [AdminSlotController::class, 'store'])->name('admin.slots.store');
            Route::post('/slots/generate', [AdminSlotController::class, 'generate'])->name('admin.slots.generate');
            Route::put('/slots/{slot}', [AdminSlotController::class, 'update'])->name('admin.slots.update');

            // Pallet Gudang Management
            Route::get('/pallets', [AdminBookingController::class, 'palletIndex'])->name('admin.pallets.index');
            Route::post('/pallets/store', [AdminBookingController::class, 'palletStore'])->name('admin.pallets.store');
            Route::post('/pallets/generate', [AdminBookingController::class, 'palletGenerate'])->name('admin.pallets.generate');
            Route::post('/pallets/add-layout', [AdminBookingController::class, 'addLayout'])->name('admin.pallets.add-layout');

            // Master Data Resources (Porter & PIC)
            Route::resource('porters', PorterController::class)->names('admin.porter');
            Route::resource('warehouse-pics', WarehousePicController::class)->names('admin.warehouse-pics');

            // Laporan Logistik & Umum
            // Route::get('/report', [ReportController::class, 'index'])->name('admin.report.index');
            // Route::get('/report/jts/{type}', [ReportController::class, 'jtsView'])->name('admin.report.jts');
            // Route::get('/report/export/{id}/{type}', [ReportController::class, 'exportExcel'])->name('admin.report.export-excel');
        });

        /*
        |------------------------------------------------------------------
        | D. ROLE: MANAGER & SUPERADMIN (Manajemen User & Approval Bisnis)
        |------------------------------------------------------------------
        */
        Route::middleware(['role:superadmin|manager'])->group(function () {
            // Monitor Bisnis & Approval
            Route::get('/business', [AdminBookingController::class, 'businessIndex'])->name('admin.business.index');
            Route::get('/business/{id}/detail', [AdminBookingController::class, 'businessDetail'])->name('admin.business.detail');
            Route::put('/business/{id}/approve', [AdminBookingController::class, 'businessApprove'])->name('admin.business.approve');

            // Manajemen Akun Staf Admin Internal
            Route::get('/profile/list', [UserAdminController::class, 'profileList'])->name('admin.profile.profileList');
            // Route::get('/profile/create', [UserAdminController::class, 'create'])->name('admin.profile.create');
            // Route::post('/profile/store', [UserAdminController::class, 'store'])->name('admin.profile.store');

            Route::get('/report', [ReportController::class, 'index'])->name('admin.report.index');
            Route::get('/report/jts/{type}', [ReportController::class, 'jtsView'])->name('admin.report.jts');
            Route::get('/report/export/{id}/{type}', [ReportController::class, 'exportExcel'])->name('admin.report.export-excel');
            Route::get('/report/nuctech/{type}', [ReportController::class, 'nuctechView'])->name('admin.report.nuctech');



            // Master Data Mesin Penyinaran 
            Route::resource('production-lines', AdminProductionLineController::class)
                ->except(['show', 'create', 'edit'])
                ->names('admin.production-lines');
        });

        /*
        |------------------------------------------------------------------
        | E. ROLE: SUPERADMIN ONLY (Keamanan Tinggi & Tindakan Destruktif)
        |------------------------------------------------------------------
        */
        Route::middleware(['role:superadmin|production'])->group(function () {
            Route::post('/customerList/create', [CustomerProfileController::class, 'create'])->name('admin.customerList.create');
            Route::post('/customerList/store', [CustomerProfileController::class, 'store'])->name('admin.customerList.store');
            Route::put('/customerList/{id}', [CustomerProfileController::class, 'updateAdmin'])->name('admin.customerList.update');
            Route::delete('/customerList/{id}', [CustomerProfileController::class, 'destroy'])->name('admin.customerList.destroy');

            Route::get('/bookings/create', [AdminBookingController::class, 'create'])->name('admin.bookings.create');
            Route::get('/bookings/generate-code', [AdminBookingController::class, 'generateCode']);
            // Route::get('/bookings/status/{status}', [AdminBookingController::class, 'statusPage'])->name('admin.bookings.status');
            Route::post('/bookings/store', [AdminBookingController::class, 'store'])->name('admin.bookings.store');
            Route::post('/bookings/checkin', [AdminBookingController::class, 'checkIn'])->name('admin.bookings.checkin');
            Route::post('/bookings/{id}/placement', [AdminBookingController::class, 'storePlacement'])->name('admin.bookings.storePlacement');
            Route::put('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus'])->name('admin.bookings.update-status');
            Route::get('/bookings/{id}/invoice', [AdminBookingController::class, 'previewInvoice'])->name('admin.bookings.invoice');
            Route::put('/bookings/{id}/payment-status', [AdminProductionController::class, 'updatePaymentStatus'])->name('admin.bookings.paymentStatus');
            Route::get('/bookings/{id}/edit', [AdminBookingController::class, 'edit'])->name('admin.bookings.edit');
            Route::put('/bookings/{id}', [AdminBookingController::class, 'update'])->name('admin.bookings.update');



            // Hanya Superadmin yang boleh menghapus data krusial atau akun orang lain
            Route::delete('/profile/{id}/destroy', [UserAdminController::class, 'destroy'])->name('admin.profile.destroy');
            Route::get('/profile/create', [UserAdminController::class, 'create'])->name('admin.profile.create');
            Route::post('/profile/store', [UserAdminController::class, 'store'])->name('admin.profile.store');

            Route::delete('/bookings/{id}', [AdminBookingController::class, 'destroy'])->name('admin.bookings.destroy');
            Route::delete('/slots/{slot}', [AdminSlotController::class, 'destroy'])->name('admin.slots.destroy');
            Route::delete('/pallets/{id}', [AdminBookingController::class, 'palletDestroy'])->name('admin.pallets.destroy');
        });

        /*
        |------------------------------------------------------------------
        | F. AMAN SINKRONISASI BERSAMA (Route Diletakkan Paling Bawah)
        |------------------------------------------------------------------
        | Sengaja ditaruh di paling bawah agar tidak menabrak route statis 
        | seperti '/profile/create' atau '/profile/list'.
        */
        Route::get('/profile/{id}/edit', [UserAdminController::class, 'edit'])->name('admin.profile.edit');
        Route::put('/profile/{id}/update', [UserAdminController::class, 'update'])->name('admin.profile.update');
    });

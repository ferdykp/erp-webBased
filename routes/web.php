<?php

use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AdminProductionController;
use App\Http\Controllers\AdminProductionLineController;
use App\Http\Controllers\AdminSlotController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\PorterController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\UserCustomController;
use App\Http\Controllers\WarehousePicController;

Route::get('/', function () {
    // Cek apakah ada yang login di guard default
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('customer.dashboard');
    }

    return view('landing');
})->name('landing');
// Route::get('/', function () {
//     if (Auth::guard('admin')->check()) {
//         return redirect()->route('admin.dashboard');
//     }
//     return redirect()->route('admin.login');
// });

Route::prefix('customer')->middleware('nocache')->group(function () {
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
    Route::post('/register', [CustomerAuthController::class, 'register']);

    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
    Route::post('/login', [CustomerAuthController::class, 'login']);

    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
    // Route::post('/logout', function () {
    //     Auth::guard('customer')->logout();
    //     return redirect()->route('landing');
    // })->name('customer.logout');
});

Route::prefix('customer')
    ->middleware(['auth', 'nocache']) // Pakai auth standar (guard web)
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])
            ->name('customer.dashboard');

        // --- PROFILE COMPLETION (Lengkapi Profil) ---
        // Diubah agar name-nya konsisten dengan redirect di Controller
        Route::get('/profile/complete', [CustomerProfileController::class, 'showCompleteProfile'])
            ->name('customer.profile.complete'); // Disederhanakan

        Route::post('/profile/complete', [CustomerProfileController::class, 'completeProfile'])
            ->name('customer.profile.complete.store');

        // --- BOOKING SYSTEM ---
        Route::get('/booking/create', [CustomerBookingController::class, 'create'])
            ->name('customer.booking.create');

        Route::post('/booking/store', [CustomerBookingController::class, 'store'])
            ->name('customer.booking.store');

        Route::get('/booking/{id}', [CustomerBookingController::class, 'show'])
            ->name('customer.booking.show');

        Route::get('/booking/{id}/print', [CustomerBookingController::class, 'print'])
            ->name('customer.booking.print');

        // --- GENERAL PROFILE & SETTINGS ---
        Route::get('/profile', [UserCustomController::class, 'index'])
            ->name('customer.profile');

        Route::get('/profile/edit', [CustomerProfileController::class, 'edit'])
            ->name('customer.profile.edit');

        Route::put('/profile/update', [CustomerProfileController::class, 'update'])
            ->name('customer.profile.update');

        Route::put('/profile/password', [CustomerProfileController::class, 'updatePassword'])
            ->name('customer.profile.password');

        Route::get('/history', [UserCustomController::class, 'history'])
            ->name('customer.history');

        // Pastikan route ini ada di dalam group customer Anda
        // Route::put('/profile/update', [UserCustomController::class, 'update'])
        //     ->name('customer.profile.update');

        Route::put('/profile/password', [UserCustomController::class, 'updatePassword'])
            ->name('customer.profile.password');
    });



// --- ADMIN AUTH ---
Route::prefix('admin')->middleware('nocache')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// Route::prefix('admin')
//     ->middleware(['auth:admin', 'nocache'])
//     ->group(function () {

//         Route::get('/dashboard', [AdminBookingController::class, 'index'])->name('admin.dashboard');

//         Route::get('/business', [AdminBookingController::class, 'businessIndex'])->name('admin.business.index');
//         Route::get('/business/{id}/detail', [AdminBookingController::class, 'businessDetail'])->name('admin.business.detail');
//         Route::put('/business/{id}/approve', [AdminBookingController::class, 'businessApprove'])->name('admin.business.approve');

//         Route::get('/bookings', [AdminBookingController::class, 'allOrder'])->name('admin.bookings');
//         Route::get('/bookings/status/{status}', [AdminBookingController::class, 'statusPage'])->name('admin.bookings.status');
//         Route::put('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus'])->name('admin.bookings.update');
//         Route::post('/bookings/checkin', [AdminBookingController::class, 'checkIn'])->name('admin.bookings.checkin');

//         Route::get('/bookings/generate-code', [AdminBookingController::class, 'generateCode']);

//         Route::get('/admin/bookings/{id}/edit', [AdminBookingController::class, 'edit'])->name('admin.bookings.edit');
//         Route::put('/admin/bookings/{id}', [AdminBookingController::class, 'update'])->name('admin.bookings.update');
//         Route::delete('/bookings/{id}', [AdminBookingController::class, 'destroy'])->name('admin.bookings.destroy');

//         // Slot Management
//         Route::get('/slots', [AdminSlotController::class, 'index'])->name('admin.slots.index');
//         Route::post('/slots', [AdminSlotController::class, 'store'])->name('admin.slots.store');
//         Route::put('/slots/{slot}', [AdminSlotController::class, 'update'])->name('admin.slots.update');
//         Route::post('/slots/generate', [AdminSlotController::class, 'generate'])->name('admin.slots.generate');
//         Route::delete('/slots/{slot}', [AdminSlotController::class, 'destroy'])->name('admin.slots.destroy');

//         // Pallet Management
//         Route::get('/pallets', [AdminBookingController::class, 'palletIndex'])->name('admin.pallets.index');
//         Route::post('/pallets/store', [AdminBookingController::class, 'palletStore'])->name('admin.pallets.store');
//         Route::post('/pallets/generate', [AdminBookingController::class, 'palletGenerate'])->name('admin.pallets.generate');
//         Route::delete('/pallets/{id}', [AdminBookingController::class, 'palletDestroy'])->name('admin.pallets.destroy');
//         Route::post('/pallets/add-layout', [AdminBookingController::class, 'addLayout'])->name('admin.pallets.add-layout');

//         Route::get('/bookings/{id}/invoice', [AdminBookingController::class, 'previewInvoice'])
//             ->name('admin.bookings.invoice');

//         // Tambahkan route ini di bawah route /bookings/checkin
//         Route::post('/bookings/{id}/placement', [AdminBookingController::class, 'storePlacement'])
//             ->name('admin.bookings.storePlacement');

//         Route::get('/customerList', [CustomerProfileController::class, 'index'])->name('admin.customerList.index');
//         Route::post('customerList/create', [CustomerProfileController::class, 'create'])->name('admin.customerList.create');
//         Route::post(
//             '/customerList/store',
//             [CustomerProfileController::class, 'store']
//         )->name('admin.customerList.store');
//         Route::put('/customerList/{id}', [CustomerProfileController::class, 'updateAdmin'])
//             ->name('admin.customerList.update');
//         Route::delete('/customerList/{id}', [CustomerProfileController::class, 'destroy'])->name('admin.customerList.destroy');


//         Route::get('/profile', [UserAdminController::class, 'index'])->name('admin.profile');
//         Route::get('/profile/edit', [UserAdminController::class, 'edit'])->name('admin.profile.edit');
//         Route::put('/profile/update', [UserAdminController::class, 'update'])->name('admin.profile.update');
//         Route::get('/profile/create', [UserAdminController::class, 'create'])->name('admin.profile.create');
//         Route::post('/profile/store', [UserAdminController::class, 'store'])->name('admin.profile.store');
//         Route::get('/profile/list', [UserAdminController::class, 'profileList'])->name('admin.profile.profileList');
//         Route::get('/profile/destroy', [UserAdminController::class, 'destroy'])->name('admin.profile.destroy');
//         Route::put('/profile/password', [UserAdminController::class, 'updatePassword'])->name('admin.profile.password');
//         Route::get('/bookings/create', [AdminBookingController::class, 'create'])->name('admin.bookings.create');
//         Route::post('/bookings/store', [AdminBookingController::class, 'store'])->name('admin.bookings.store');

//         Route::prefix('report')->group(function () {
//             // Halaman list utama (tabel history)
//             Route::get('/', [ReportController::class, 'index'])->name('admin.report.index');

//             Route::get('/export/{id}/{type}', [ReportController::class, 'exportExcel'])
//                 ->name('admin.report.export-excel');

//             // Route untuk Sidebar JTS
//             Route::get('/jts/{type}', [ReportController::class, 'jtsView'])->name('admin.report.jts');

//             // Route untuk Sidebar Nuctech
//             Route::get('/nuctech/{type}', [ReportController::class, 'nuctechView'])->name('admin.report.nuctech');
//         });


//         // Master Data Mesin Penyinaran (CRUD)
//         Route::resource('production-lines', AdminProductionLineController::class)
//             ->except(['show', 'create', 'edit'])
//             ->names('admin.production-lines');

//         // Step 1 – Process Parameter Setting (Process Set)
//         Route::get('/production/parameter', [AdminProductionController::class, 'parameterSetting'])
//             ->name('admin.production.parameter');
//         Route::put('/production/batches/{batch}/parameter', [AdminProductionController::class, 'storeParameter'])
//             ->name('admin.production.batches.parameter.update');
//         Route::post('/production/process', [AdminProductionController::class, 'processBooking'])
//             ->name('admin.production.process');

//         // Step 2 – Batch Queue (legacy, tidak muncul di sidebar)
//         Route::get('/production/batch-queue', [AdminProductionController::class, 'batchQueue'])
//             ->name('admin.production.batch-queue');
//         Route::post('/production/batches', [AdminProductionController::class, 'storeBatch'])
//             ->name('admin.production.batches.store');
//         Route::put('/production/batches/{batch}/start', [AdminProductionController::class, 'startIrradiation'])
//             ->name('admin.production.batches.start');

//         // Step 2 – Process Product Irradiation (In Irradiation)
//         Route::get('/production/offline', [AdminProductionController::class, 'offline'])
//             ->name('admin.production.offline');

//         // Step 3 – Product Finish
//         Route::get('/production/finish', [AdminProductionController::class, 'finishPage'])
//             ->name('admin.production.finish');
//         Route::put('/production/batches/{batch}/finish', [AdminProductionController::class, 'finishBatch'])
//             ->name('admin.production.batches.finish');
//         Route::get('/production/batches/{id}/certificate', [AdminProductionController::class, 'printCertificate'])
//             ->name('admin.production.certificate');

//         Route::get('/production/finish', [AdminBookingController::class, 'finishIndex'])->name('admin.production.finish');
//         Route::post('/production/relocate', [AdminBookingController::class, 'relocatePallet'])->name('admin.production.relocate-pallet');

//         // Cari baris ini di bagian bawah routes/web.php
//         Route::put('/bookings/{id}/payment-status', [AdminProductionController::class, 'updatePaymentStatus']) // Ganti ke AdminProductionController
//             ->name('admin.bookings.paymentStatus');

//         // Master Data Porter (CRUD)
//         Route::resource('porters', PorterController::class)->names([
//             'index'   => 'admin.porter.index',
//             'create'  => 'admin.porter.create',
//             'store'   => 'admin.porter.store',
//             'edit'    => 'admin.porter.edit',
//             'update'  => 'admin.porter.update',
//             'destroy' => 'admin.porter.destroy',
//         ]);

//         // Master Data PIC Warehouse (CRUD)
//         Route::resource('warehouse-pics', WarehousePicController::class)->names([
//             'index'   => 'admin.warehouse-pics.index',
//             'create'  => 'admin.warehouse-pics.create',
//             'store'   => 'admin.warehouse-pics.store',
//             'edit'    => 'admin.warehouse-pics.edit',
//             'update'  => 'admin.warehouse-pics.update',
//             'destroy' => 'admin.warehouse-pics.destroy',
//         ]);
//     });

// =========================================================================
// ROUTE PUBLIK / LOGIN (TIDAK MEMERLUKAN LOGIN ROLE)
// =========================================================================
Route::prefix('admin')->middleware('nocache')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});


// =========================================================================
// ROUTE SECURE (HARUS LOGIN SEBAGAI ADMIN)
// =========================================================================
Route::prefix('admin')
    ->middleware(['auth:admin', 'nocache'])
    ->group(function () {

        // --- AKSES BERSAMA (Semua Role Tanpa Terkecuali Bisa Mengakses Ini) ---
        Route::get('/dashboard', [AdminBookingController::class, 'index'])->name('admin.dashboard');
        Route::get('/profile', [UserAdminController::class, 'index'])->name('admin.profile');
        // Route::get('/profile/edit', [UserAdminController::class, 'edit'])->name('admin.profile.edit');
        Route::get('/profile/{id}/edit', [UserAdminController::class, 'edit'])->name('admin.profile.edit');
        Route::put('/profile/{id}/update', [UserAdminController::class, 'update'])->name('admin.profile.update');
        // Route::put('/profile/update', [UserAdminController::class, 'update'])->name('admin.profile.update');
        Route::put('/profile/password', [UserAdminController::class, 'updatePassword'])->name('admin.profile.password');


        // =================================================================
        // 1. ROLE KHUSUS: SUPERADMIN & MANAGER (Bisnis, Approval, & User Management)
        // =================================================================
        Route::middleware(['role:superadmin|manager'])->group(function () {
            // Monitor Bisnis & Approval Order Baru
            Route::get('/business', [AdminBookingController::class, 'businessIndex'])->name('admin.business.index');
            Route::get('/business/{id}/detail', [AdminBookingController::class, 'businessDetail'])->name('admin.business.detail');
            Route::put('/business/{id}/approve', [AdminBookingController::class, 'businessApprove'])->name('admin.business.approve');

            // Manajemen User Admin Internal
            Route::get('/profile/create', [UserAdminController::class, 'create'])->name('admin.profile.create');
            Route::post('/profile/store', [UserAdminController::class, 'store'])->name('admin.profile.store');
            Route::get('/profile/list', [UserAdminController::class, 'profileList'])->name('admin.profile.profileList');
            // 2. Route untuk Superadmin/Manager mengedit AKUN ORANG LAIN dari tabel List (Wajib pakai {id})
            // Route::get('/profile/{id}/edit', [UserAdminController::class, 'edit'])->name('admin.profile.edit');
            Route::delete('/profile/{id}/destroy', [UserAdminController::class, 'destroy'])->name('admin.profile.destroy');

            // Master Data Mesin Penyinaran 
            Route::resource('production-lines', AdminProductionLineController::class)
                ->except(['show', 'create', 'edit'])
                ->names('admin.production-lines');
        });

        // Hapus Data Krusial (Hanya dibatasi untuk Superadmin saja demi keamanan)
        Route::middleware(['role:superadmin'])->group(function () {
            Route::delete('/bookings/{id}', [AdminBookingController::class, 'destroy'])->name('admin.bookings.destroy');
            Route::delete('/slots/{slot}', [AdminSlotController::class, 'destroy'])->name('admin.slots.destroy');
            Route::delete('/pallets/{id}', [AdminBookingController::class, 'palletDestroy'])->name('admin.pallets.destroy');
        });


        // =================================================================
        // 2. ROLE: CARGO ADMIN (Manajemen Order, Customer, Gudang, & Check-In)
        // =================================================================
        Route::middleware(['role:superadmin|manager|cargo_admin'])->group(function () {
            // Customer Management CRUD
            Route::get('/customerList', [CustomerProfileController::class, 'index'])->name('admin.customerList.index');
            Route::post('customerList/create', [CustomerProfileController::class, 'create'])->name('admin.customerList.create');
            Route::post('/customerList/store', [CustomerProfileController::class, 'store'])->name('admin.customerList.store');
            Route::put('/customerList/{id}', [CustomerProfileController::class, 'updateAdmin'])->name('admin.customerList.update');
            Route::delete('/customerList/{id}', [CustomerProfileController::class, 'destroy'])->name('admin.customerList.destroy');

            // Booking Creation, Edit, Inbound & Check-in
            Route::get('/bookings/create', [AdminBookingController::class, 'create'])->name('admin.bookings.create');
            Route::post('/bookings/store', [AdminBookingController::class, 'store'])->name('admin.bookings.store');
            Route::post('/bookings/checkin', [AdminBookingController::class, 'checkIn'])->name('admin.bookings.checkin');
            Route::post('/bookings/{id}/placement', [AdminBookingController::class, 'storePlacement'])->name('admin.bookings.storePlacement');
            Route::post('/production/relocate', [AdminBookingController::class, 'relocatePallet'])->name('admin.production.relocate-pallet');
            Route::get('/bookings/{id}/invoice', [AdminBookingController::class, 'previewInvoice'])->name('admin.bookings.invoice');
            Route::put('/bookings/{id}/payment-status', [AdminProductionController::class, 'updatePaymentStatus'])->name('admin.bookings.paymentStatus');

            // Slot Gudang Management
            Route::get('/slots', [AdminSlotController::class, 'index'])->name('admin.slots.index');
            Route::post('/slots', [AdminSlotController::class, 'store'])->name('admin.slots.store');
            Route::put('/slots/{slot}', [AdminSlotController::class, 'update'])->name('admin.slots.update');
            Route::post('/slots/generate', [AdminSlotController::class, 'generate'])->name('admin.slots.generate');

            // Pallet Gudang Management
            Route::get('/pallets', [AdminBookingController::class, 'palletIndex'])->name('admin.pallets.index');
            Route::post('/pallets/store', [AdminBookingController::class, 'palletStore'])->name('admin.pallets.store');
            Route::post('/pallets/generate', [AdminBookingController::class, 'palletGenerate'])->name('admin.pallets.generate');
            Route::post('/pallets/add-layout', [AdminBookingController::class, 'addLayout'])->name('admin.pallets.add-layout');

            // Master Data Porter & PIC Warehouse CRUD
            Route::resource('porters', PorterController::class)->names('admin.porter');
            Route::resource('warehouse-pics', WarehousePicController::class)->names('admin.warehouse-pics');

            // JTS Reporting (Manifest Logistik Gudang Cargo)
            Route::get('/report/jts/{type}', [ReportController::class, 'jtsView'])->name('admin.report.jts');
        });


        // =================================================================
        // 3. ROLE: PRODUCTION (Staff Produksi / Operasional Penyinaran)
        // =================================================================
        Route::middleware(['role:superadmin|manager|production'])->group(function () {
            // Step 1 – Process Parameter Setting
            Route::get('/production/parameter', [AdminProductionController::class, 'parameterSetting'])->name('admin.production.parameter');
            Route::put('/production/batches/{batch}/parameter', [AdminProductionController::class, 'storeParameter'])->name('admin.production.batches.parameter.update');
            Route::post('/production/process', [AdminProductionController::class, 'processBooking'])->name('admin.production.process');

            // Step 2 – Batch Queue & Start Irradiation
            Route::get('/production/batch-queue', [AdminProductionController::class, 'batchQueue'])->name('admin.production.batch-queue');
            Route::post('/production/batches', [AdminProductionController::class, 'storeBatch'])->name('admin.production.batches.store');
            Route::put('/production/batches/{batch}/start', [AdminProductionController::class, 'startIrradiation'])->name('admin.production.batches.start');
            Route::get('/production/offline', [AdminProductionController::class, 'offline'])->name('admin.production.offline');

            // Step 3 – Product Finish & Certificate
            Route::get('/production/finish', [AdminBookingController::class, 'finishIndex'])->name('admin.production.finish');
            Route::put('/production/batches/{batch}/finish', [AdminProductionController::class, 'finishBatch'])->name('admin.production.batches.finish');
            Route::get('/production/batches/{id}/certificate', [AdminProductionController::class, 'printCertificate'])->name('admin.production.certificate');

            // Nuctech Reporting (Log Khusus Penyinaran Teknis)
            Route::get('/report/nuctech/{type}', [ReportController::class, 'nuctechView'])->name('admin.report.nuctech');
        });


        // =================================================================
        // ROUTE AKSES BERSAMA (Bisa dipantau oleh Superadmin, Manager, dan Cargo)
        // =================================================================
        Route::middleware(['role:superadmin|manager|cargo_admin'])->group(function () {
            Route::get('/bookings', [AdminBookingController::class, 'allOrder'])->name('admin.bookings');
            Route::get('/bookings/status/{status}', [AdminBookingController::class, 'statusPage'])->name('admin.bookings.status');
            Route::put('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus'])->name('admin.bookings.update');
            Route::get('/bookings/generate-code', [AdminBookingController::class, 'generateCode']);
            Route::get('/admin/bookings/{id}/edit', [AdminBookingController::class, 'edit'])->name('admin.bookings.edit');
            Route::put('/admin/bookings/{id}', [AdminBookingController::class, 'update'])->name('admin.bookings.update');

            // General Report List & Export Excel
            Route::get('/report', [ReportController::class, 'index'])->name('admin.report.index');
            Route::get('/report/export/{id}/{type}', [ReportController::class, 'exportExcel'])->name('admin.report.export-excel');
        });
    });

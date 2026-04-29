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
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\UserCustomController;

// Route::get('/', function () {
//     return view('admin.login');
// });
// Route::get('/', function () {

//     if (Auth::guard('customer')->check()) {
//         return redirect()->route('customer.dashboard');
//     }

//     if (Auth::guard('admin')->check()) {
//         return redirect()->route('admin.dashboard');
//     }

//     return view('landing');
// })->name('landing');
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

Route::prefix('admin')
    ->middleware(['auth:admin', 'nocache'])
    ->group(function () {

        Route::get('/dashboard', [AdminBookingController::class, 'index'])->name('admin.dashboard');

        Route::get('/business', [AdminBookingController::class, 'businessIndex'])->name('admin.business.index');
        Route::get('/business/{id}/detail', [AdminBookingController::class, 'businessDetail'])->name('admin.business.detail');
        Route::put('/business/{id}/approve', [AdminBookingController::class, 'businessApprove'])->name('admin.business.approve');

        Route::get('/bookings', [AdminBookingController::class, 'allOrder'])->name('admin.bookings');
        Route::get('/bookings/status/{status}', [AdminBookingController::class, 'statusPage'])->name('admin.bookings.status');
        Route::put('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus'])->name('admin.bookings.update');
        Route::post('/bookings/checkin', [AdminBookingController::class, 'checkIn'])->name('admin.bookings.checkin');

        Route::get('/bookings/generate-code', [AdminBookingController::class, 'generateCode']);

        Route::get('/admin/bookings/{id}/edit', [AdminBookingController::class, 'edit'])->name('admin.bookings.edit');
        Route::put('/admin/bookings/{id}', [AdminBookingController::class, 'update'])->name('admin.bookings.update');

        // Slot Management
        Route::get('/slots', [AdminSlotController::class, 'index'])->name('admin.slots.index');
        Route::post('/slots', [AdminSlotController::class, 'store'])->name('admin.slots.store');
        Route::put('/slots/{slot}', [AdminSlotController::class, 'update'])->name('admin.slots.update');
        Route::post('/slots/generate', [AdminSlotController::class, 'generate'])->name('admin.slots.generate');
        Route::delete('/slots/{slot}', [AdminSlotController::class, 'destroy'])->name('admin.slots.destroy');

        // Pallet Management
        Route::get('/pallets', [AdminBookingController::class, 'palletIndex'])->name('admin.pallets.index');
        Route::post('/pallets/store', [AdminBookingController::class, 'palletStore'])->name('admin.pallets.store');
        Route::post('/pallets/generate', [AdminBookingController::class, 'palletGenerate'])->name('admin.pallets.generate');
        Route::delete('/pallets/{id}', [AdminBookingController::class, 'palletDestroy'])->name('admin.pallets.destroy');
        Route::post('/pallets/add-layout', [AdminBookingController::class, 'addLayout'])->name('admin.pallets.add-layout');

        // Invoices & Profile
        // Route::get('/bookings/{id}/invoice', [AdminBookingController::class, 'downloadInvoice'])->name('admin.bookings.invoice');
        // Route::get('/bookings/{id}/preview', [AdminBookingController::class, 'previewInvoice'])->name('admin.bookings.preview');
        // Preview (HTML)
        Route::get('/bookings/{id}/invoice', [AdminBookingController::class, 'previewInvoice'])
            ->name('admin.bookings.invoice');

        // Export PDF
        // Route::get('/bookings/{id}/invoice/export', [AdminBookingController::class, 'downloadInvoice'])
        //     ->name('admin.bookings.invoice.export');

        // Tambahkan route ini di bawah route /bookings/checkin
        Route::post('/bookings/{id}/placement', [AdminBookingController::class, 'storePlacement'])
            ->name('admin.bookings.storePlacement');

        Route::get('/customerList', [CustomerProfileController::class, 'index'])->name('admin.customerList.index');
        Route::post('customerList/create', [CustomerProfileController::class, 'create'])->name('admin.customerList.create');
        Route::post(
            '/customerList/store',
            [CustomerProfileController::class, 'store']
        )->name('admin.customerList.store');
        Route::put('/customerList/{id}', [CustomerProfileController::class, 'updateAdmin'])
            ->name('admin.customerList.update');


        Route::get('/profile', [UserAdminController::class, 'index'])->name('admin.profile');
        Route::get('/profile/edit', [UserAdminController::class, 'edit'])->name('admin.profile.edit');
        Route::put('/profile/update', [UserAdminController::class, 'update'])->name('admin.profile.update');
        Route::get('/profile/create', [UserAdminController::class, 'create'])->name('admin.profile.create');
        Route::post('/profile/store', [UserAdminController::class, 'store'])->name('admin.profile.store');
        Route::get('/profile/list', [UserAdminController::class, 'profileList'])->name('admin.profile.profileList');
        Route::get('/profile/destroy', [UserAdminController::class, 'destroy'])->name('admin.profile.destroy');
        Route::put('/profile/password', [UserAdminController::class, 'updatePassword'])->name('admin.profile.password');
        Route::get('/bookings/create', [AdminBookingController::class, 'create'])->name('admin.bookings.create');
        Route::post('/bookings/store', [AdminBookingController::class, 'store'])->name('admin.bookings.store');

        Route::get('/report', [ReportController::class, 'index'])->name('admin.report.index');
        Route::get('/report/export/{id}', [ReportController::class, 'exportExcel'])
            ->name('admin.report.export-excel');
        Route::get('/report/export-pdf', [ReportController::class, 'exportPdf'])->name('admin.report.export-pdf');
        // ====================================================================
        // Layer 3 – Production Management
        // Akses dibatasi untuk role: technologist, production_engineer, admin
        // ====================================================================
        Route::middleware(['role:technologist,production_engineer,admin'])->group(function () {

            // Master Data Mesin Penyinaran (CRUD)
            Route::resource('production-lines', AdminProductionLineController::class)
                ->except(['show', 'create', 'edit'])
                ->names('admin.production-lines');

            // Step 1 – Process Parameter Setting (Process Set)
            Route::get('/production/parameter', [AdminProductionController::class, 'parameterSetting'])
                ->name('admin.production.parameter');
            Route::put('/production/batches/{batch}/parameter', [AdminProductionController::class, 'storeParameter'])
                ->name('admin.production.batches.parameter.update');
            Route::post('/production/process', [AdminProductionController::class, 'processBooking'])
                ->name('admin.production.process');

            // Step 2 – Batch Queue (legacy, tidak muncul di sidebar)
            Route::get('/production/batch-queue', [AdminProductionController::class, 'batchQueue'])
                ->name('admin.production.batch-queue');
            Route::post('/production/batches', [AdminProductionController::class, 'storeBatch'])
                ->name('admin.production.batches.store');
            Route::put('/production/batches/{batch}/start', [AdminProductionController::class, 'startIrradiation'])
                ->name('admin.production.batches.start');

            // Step 2 – Process Product Irradiation (In Irradiation)
            Route::get('/production/offline', [AdminProductionController::class, 'offline'])
                ->name('admin.production.offline');

            // Step 3 – Product Finish
            Route::get('/production/finish', [AdminProductionController::class, 'finishPage'])
                ->name('admin.production.finish');
            Route::put('/production/batches/{batch}/finish', [AdminProductionController::class, 'finishBatch'])
                ->name('admin.production.batches.finish');
            Route::get('/production/batches/{id}/certificate', [AdminProductionController::class, 'printCertificate'])
                ->name('admin.production.certificate');

            Route::get('/production/finish', [AdminBookingController::class, 'finishIndex'])->name('admin.production.finish');
            Route::post('/production/relocate', [AdminBookingController::class, 'relocatePallet'])->name('admin.production.relocate-pallet');

            // Cari baris ini di bagian bawah routes/web.php
            Route::put('/bookings/{id}/payment-status', [AdminProductionController::class, 'updatePaymentStatus']) // Ganti ke AdminProductionController
                ->name('admin.bookings.paymentStatus');
        });
    });



    // Route::middleware(['role:technologist,production_engineer,admin'])->group(function () {

//     // Master Data Mesin Penyinaran (CRUD)
//     Route::resource('production-lines', AdminProductionLineController::class)
//         ->except(['show', 'create', 'edit'])
//         ->names('admin.production-lines');

//     // Step 1 – Process Parameter Setting (Process Set)
//     Route::get('/production/parameter', [AdminProductionController::class, 'parameterSetting'])
//         ->name('admin.production.parameter');
//     Route::put('/production/batches/{batch}/parameter', [AdminProductionController::class, 'storeParameter'])
//         ->name('admin.production.batches.parameter.update');
//     Route::post('/production/process', [AdminProductionController::class, 'processBooking'])
//         ->name('admin.production.process');

//     // Step 2 – Batch Queue (legacy, tidak muncul di sidebar)
//     Route::get('/production/batch-queue', [AdminProductionController::class, 'batchQueue'])
//         ->name('admin.production.batch-queue');
//     Route::post('/production/batches', [AdminProductionController::class, 'storeBatch'])
//         ->name('admin.production.batches.store');
//     Route::put('/production/batches/{batch}/start', [AdminProductionController::class, 'startIrradiation'])
//         ->name('admin.production.batches.start');

//     // Step 2 – Process Product Irradiation (In Irradiation)
//     Route::get('/production/offline', [AdminProductionController::class, 'offline'])
//         ->name('admin.production.offline');

//     // Step 3 – Product Finish
//     Route::get('/production/finish', [AdminProductionController::class, 'finishPage'])
//         ->name('admin.production.finish');
//     Route::put('/production/batches/{batch}/finish', [AdminProductionController::class, 'finishBatch'])
//         ->name('admin.production.batches.finish');
// });

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PegawaiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root ke login admin
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes (not authenticated)
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
    });

    // Authenticated routes
    Route::middleware('admin.auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Admin Management
        Route::resource('admins', AdminController::class);

        // Pegawai Management
        Route::resource('pegawai', PegawaiController::class);

        // OPD Management (moved inside admin middleware)
        Route::prefix('opds')->group(function () {
            Route::get('/', [OpdController::class, 'index'])->name('opds.index');
            Route::get('/{id}', [OpdController::class, 'show'])->name('opds.show');
            Route::get('/{id}/peta-jabatan', [OpdController::class, 'petaJabatan'])->name('opds.peta-jabatan');
            Route::get('/{id}/export', [OpdController::class, 'export'])->name('opds.export');
            Route::put('/{id}', [OpdController::class, 'update'])->name('opds.update');
            Route::delete('/{id}', [OpdController::class, 'destroy'])->name('opds.destroy');

            // CRUD Jabatan dalam OPD
            Route::post('/{opd}/jabatan', [OpdController::class, 'storeJabatan'])->name('opds.jabatan.store');
            Route::put('/{opd}/jabatan/{jabatan}', [OpdController::class, 'updateJabatan'])->name('opds.jabatan.update');
            Route::delete('/{opd}/jabatan/{jabatan}', [OpdController::class, 'destroyJabatan'])->name('opds.jabatan.destroy');

            // CRUD Bagian dalam OPD
            Route::post('/{opd}/bagian', [App\Http\Controllers\BagianController::class, 'store'])->name('opds.bagian.store');
            Route::put('/{opd}/bagian/{bagian}', [App\Http\Controllers\BagianController::class, 'update'])->name('opds.bagian.update');
            Route::delete('/{opd}/bagian/{bagian}', [App\Http\Controllers\BagianController::class, 'destroy'])->name('opds.bagian.destroy');

            // CRUD ASN dalam OPD
            Route::post('/{opd}/asn', [OpdController::class, 'storeAsn'])->name('opds.asn.store');
            Route::put('/{opd}/asn/{asn}', [OpdController::class, 'updateAsn'])->name('opds.asn.update');
            Route::delete('/{opd}/asn/{asn}', [OpdController::class, 'destroyAsn'])->name('opds.asn.destroy');
        });

        // API routes
        Route::prefix('api')->group(function () {
            Route::get('/opds/{id}/tree', [OpdController::class, 'getOpdTree'])->name('api.opds.tree');
            Route::get('/jabatan/{id}/asns', [OpdController::class, 'getJabatanAsns'])->name('api.jabatan.asns');
            Route::get('/bagian/{id}/detail', [App\Http\Controllers\BagianController::class, 'getDetail'])->name('api.bagian.detail');
            Route::get('/opds/{id}/jabatans', [PegawaiController::class, 'getJabatanByOpd'])->name('api.opds.jabatans');
        });
    });
});

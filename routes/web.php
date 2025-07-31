<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpdController;

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

// Redirect root ke daftar OPD
Route::get('/', function () {
    return redirect()->route('opds.index');
});

// Routes untuk OPD
Route::get('/opds', [OpdController::class, 'index'])->name('opds.index');
Route::get('/opds/{id}', [OpdController::class, 'show'])->name('opds.show');
Route::put('/opds/{id}', [OpdController::class, 'update'])->name('opds.update');
Route::delete('/opds/{id}', [OpdController::class, 'destroy'])->name('opds.destroy');

// Routes untuk CRUD Jabatan dalam OPD
Route::post('/opds/{opd}/jabatan', [OpdController::class, 'storeJabatan'])->name('opds.jabatan.store');
Route::put('/opds/{opd}/jabatan/{jabatan}', [OpdController::class, 'updateJabatan'])->name('opds.jabatan.update');
Route::delete('/opds/{opd}/jabatan/{jabatan}', [OpdController::class, 'destroyJabatan'])->name('opds.jabatan.destroy');

// Routes untuk CRUD Bagian dalam OPD
Route::post('/opds/{opd}/bagian', [App\Http\Controllers\BagianController::class, 'store'])->name('opds.bagian.store');
Route::put('/opds/{opd}/bagian/{bagian}', [App\Http\Controllers\BagianController::class, 'update'])->name('opds.bagian.update');
Route::delete('/opds/{opd}/bagian/{bagian}', [App\Http\Controllers\BagianController::class, 'destroy'])->name('opds.bagian.destroy');

// Routes untuk CRUD ASN dalam OPD
Route::post('/opds/{opd}/asn', [OpdController::class, 'storeAsn'])->name('opds.asn.store');
Route::put('/opds/{opd}/asn/{asn}', [OpdController::class, 'updateAsn'])->name('opds.asn.update');
Route::delete('/opds/{opd}/asn/{asn}', [OpdController::class, 'destroyAsn'])->name('opds.asn.destroy');

// API route untuk tree structure
Route::get('/api/opds/{id}/tree', [OpdController::class, 'getOpdTree'])->name('api.opds.tree');

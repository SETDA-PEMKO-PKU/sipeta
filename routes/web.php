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

// API route untuk tree structure
Route::get('/api/opds/{id}/tree', [OpdController::class, 'getOpdTree'])->name('api.opds.tree');

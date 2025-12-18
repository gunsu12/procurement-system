<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\ProcurementController::class, 'index'])->name('home');

Route::prefix('procurement')->name('procurement.')->group(function () {
    Route::get('/', [App\Http\Controllers\ProcurementController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\ProcurementController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\ProcurementController::class, 'store'])->name('store');
    Route::get('/{procurement}', [App\Http\Controllers\ProcurementController::class, 'show'])->name('show');
    Route::post('/{procurement}/approve', [App\Http\Controllers\ProcurementController::class, 'approve'])->name('approve');
    Route::post('/{procurement}/reject', [App\Http\Controllers\ProcurementController::class, 'reject'])->name('reject');
});

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
    Route::get('/unit', [App\Http\Controllers\ReportController::class, 'unit'])->name('unit');
    Route::get('/outstanding', [App\Http\Controllers\ReportController::class, 'outstanding'])->name('outstanding');
});

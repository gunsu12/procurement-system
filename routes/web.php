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
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::prefix('procurement')->name('procurement.')->group(function () {
    Route::get('/', [App\Http\Controllers\ProcurementController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\ProcurementController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\ProcurementController::class, 'store'])->name('store');
    Route::get('/{procurement}', [App\Http\Controllers\ProcurementController::class, 'show'])->name('show');
    Route::get('/{procurement}/edit', [App\Http\Controllers\ProcurementController::class, 'edit'])->name('edit');
    Route::put('/{procurement}', [App\Http\Controllers\ProcurementController::class, 'update'])->name('update');
    Route::post('/{procurement}/approve', [App\Http\Controllers\ProcurementController::class, 'approve'])->name('approve');
    Route::post('/{procurement}/reject', [App\Http\Controllers\ProcurementController::class, 'reject'])->name('reject');
});

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
    Route::get('/unit', [App\Http\Controllers\ReportController::class, 'unit'])->name('unit');
    Route::get('/outstanding', [App\Http\Controllers\ReportController::class, 'outstanding'])->name('outstanding');
    Route::get('/timeline', [App\Http\Controllers\ReportController::class, 'timeline'])->name('timeline');
});

Route::resource('divisions', App\Http\Controllers\DivisionController::class);
Route::get('/ajax/units', [App\Http\Controllers\UnitController::class, 'getUnits'])->name('ajax.units');
Route::resource('units', App\Http\Controllers\UnitController::class);
Route::resource('users', App\Http\Controllers\UserController::class);
Route::resource('companies', App\Http\Controllers\CompanyController::class);

Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
Route::get('/profile/password', [App\Http\Controllers\ProfileController::class, 'changePassword'])->name('profile.password');
Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

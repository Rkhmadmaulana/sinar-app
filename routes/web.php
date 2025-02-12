<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RajalController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->name('dashboard');

Route::match(['get', 'post'], '/rajal', [RajalController::class, 'poliklinik'])->name('poliklinik');
Route::match(['get', 'post'], '/allpoliklinikkhusus/{kd_poli}', [RajalController::class, 'allpoliklinikkhusus'])->name('allpoliklinikkhusus');
Route::match(['get', 'post'], '/penunjang/{kd_poli}', [RajalController::class, 'penunjang'])->name('penunjang');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RajalController;
use App\Http\Controllers\RanapController;
use App\Http\Controllers\LaporanController;

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

//rm rajal
Route::match(['get', 'post'], '/rajal', [RajalController::class, 'poliklinik'])->name('poliklinik');
Route::match(['get', 'post'], '/allpoliklinikkhusus/{kd_poli}', [RajalController::class, 'allpoliklinikkhusus'])->name('allpoliklinikkhusus');
Route::match(['get', 'post'], '/penunjang/{kd_poli}', [RajalController::class, 'penunjang'])->name('penunjang');

//rm ranap 
Route::match(['get', 'post'], '/ranap', [RanapController::class, 'ranap'])->name('ranap');

//laporan rm
Route::match(['get', 'post'], '/kunjunganrajal', [LaporanController::class, 'kunjunganrajal'])->name('kunjunganrajal'); // Menampilkan laporan kunjungan rawat jalan
Route::match(['get', 'post'], '/kunjunganranap', [LaporanController::class, 'kunjunganranap'])->name('kunjunganranap'); // Menampilkan laporan kunjungan rawat inap
Route::match(['get', 'post'], '/penyakitterbanyak', [LaporanController::class, 'penyakitterbanyak'])->name('penyakitterbanyak');// Menampilkan laporan penyakit terbanyak
Route::match(['get', 'post'], '/penyakitmenular', [LaporanController::class, 'penyakitmenular'])->name('penyakitmenular'); // Menampilkan laporan penyakit menular
Route::match(['get', 'post'], '/igd', [LaporanController::class, 'igd'])->name('igd');// Menampilkan laporan IGD
Route::match(['get', 'post'], '/kematian', [LaporanController::class, 'kematian'])->name('kematian');// Menampilkan laporan kematian
Route::match(['get', 'post'], '/pertumbuhan', [LaporanController::class, 'pertumbuhan'])->name('pertumbuhan');// Menampilkan laporan pertumbuhan


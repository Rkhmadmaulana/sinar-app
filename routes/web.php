<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RajalController;
use App\Http\Controllers\RanapController;
use App\Http\Controllers\KinerjaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;

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

Route::get('/', [LoginController::class, 'index'])->name('login');
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login-proses', [LoginController::class, 'login_proses'])->name('login-proses');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->name('dashboard');

//admin
Route::match(['get', 'post'], '/account', [AdminController::class, 'account'])->name('account'); // Menampilkan akun
Route::match(['get', 'post'], '/hakacc', [AdminController::class, 'hakacc'])->name('hakacc'); // Mengatur hak akses akun
Route::match(['get', 'post'], '/copy_access', [AdminController::class, 'copy_account'])->name('copy_access'); // Mengatur Copy hak akses akun
Route::match(['get', 'post'], '/deleteacc/{userId}', [AdminController::class, 'deleteacc'])->name('deleteacc'); // Menghapus akun


//rm rajal
Route::match(['get', 'post'], '/rajal', [RajalController::class, 'poliklinik'])->name('poliklinik');
Route::match(['get', 'post'], '/allpoliklinikkhusus/{kd_poli}', [RajalController::class, 'allpoliklinikkhusus'])->name('allpoliklinikkhusus');
Route::match(['get', 'post'], '/penunjang/{kd_poli}', [RajalController::class, 'penunjang'])->name('penunjang');
Route::match(['get', 'post'], '/igdk', [RajalController::class, 'igdk'])->name('igdk');
Route::match(['get', 'post'], '/hdl', [RajalController::class, 'hdl'])->name('hemodialisa');
Route::match(['get', 'post'], '/lab', [RajalController::class, 'lab'])->name('lab');
Route::match(['get', 'post'], '/radiologi', [RajalController::class, 'radiologi'])->name('radiologi');

//rm ranap 
Route::match(['get', 'post'], '/ranap', [RanapController::class, 'ranap'])->name('ranap');


//laporan rm
Route::match(['get', 'post'], '/kunjunganrajal', [LaporanController::class, 'kunjunganrajal'])->name('kunjunganrajal'); // Menampilkan laporan kunjungan rawat jalan
Route::match(['get', 'post'], '/kunjunganranap', [LaporanController::class, 'kunjunganranap'])->name('kunjunganranap'); // Menampilkan laporan kunjungan rawat inap
Route::match(['get', 'post'], '/penyakitterbanyak', [LaporanController::class, 'penyakitterbanyak'])->name('penyakitterbanyak'); // Menampilkan laporan penyakit terbanyak
Route::match(['get', 'post'], '/penyakitmenular', [LaporanController::class, 'penyakitmenular'])->name('penyakitmenular'); // Menampilkan laporan penyakit menular
Route::match(['get', 'post'], '/igd', [LaporanController::class, 'igd'])->name('igd'); // Menampilkan laporan IGD
Route::match(['get', 'post'], '/operasi', [LaporanController::class, 'operasi'])->name('operasi'); // Menampilkan laporan IGD
Route::match(['get', 'post'], '/kematian', [LaporanController::class, 'kematian'])->name('kematian'); // Menampilkan laporan kematian
Route::match(['get', 'post'], '/pertumbuhan', [LaporanController::class, 'pertumbuhan'])->name('pertumbuhan'); // Menampilkan laporan pertumbuhan
Route::match(['get', 'post'], '/laporan_radlab', [LaporanController::class, 'laporan_radlab'])->name('laporan_radlab'); // Menampilkan laporan kunjungan rawat jalan

// kinerja
Route::match(['get', 'post'], '/kinerja', [KinerjaController::class, 'kinerja'])->name('kinerja');
Route::match(['get', 'post'], '/setjumlahbed', [KinerjaController::class, 'setjumlahbed'])->name('setjumlahbed');

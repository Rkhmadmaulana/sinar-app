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
Route::match(['get', 'post'], '/kelengkapan', [LaporanController::class, 'kelengkapanrm'])->name('kelengkapan'); // Menampilkan laporan kelengkapan rekam medis
Route::get('/modalrm', [LaporanController::class, 'getModalContent'])->name('modalrm'); // Menampilkan modal content
Route::get('/erm_ranap', [LaporanController::class, 'getERMContent'])->name('erm_ranap'); // Menampilkan modal content
Route::get('/erm_ranap_cppt', [LaporanController::class, 'getERMCPPT'])->name('erm_ranap_cppt'); // Menampilkan berkas cppt
Route::get('/erm_ranap_medis_igd', [LaporanController::class, 'getERMMedisIGD'])->name('erm_ranap_medis_igd'); // Menampilkan berkas awal medis igd
Route::get('/erm_ranap_medis_umum', [LaporanController::class, 'getERMMedisUmum'])->name('erm_ranap_medis_umum'); // Menampilkan berkas awal medis umum
Route::get('/erm_ranap_catatan_perkembangan', [LaporanController::class, 'getERMCatatanPerkembangan'])->name('erm_ranap_catatan_perkembangan');
Route::get('/erm_ranap_persetujuan_umum', [LaporanController::class, 'getERMPersetujuanUmum'])->name('erm_ranap_persetujuan_umum'); // Menampilkan berkas persetujuan umum


Route::match(['get', 'post'], '/kunjunganrajal', [LaporanController::class, 'kunjunganrajal'])->name('kunjunganrajal'); // Menampilkan laporan kunjungan rawat jalan
Route::match(['get', 'post'], '/kunjunganranap', [LaporanController::class, 'kunjunganranap'])->name('kunjunganranap'); // Menampilkan laporan kunjungan rawat inap
Route::match(['get', 'post'], '/penyakitterbanyak', [LaporanController::class, 'penyakitterbanyak'])->name('penyakitterbanyak'); // Menampilkan laporan penyakit terbanyak
Route::match(['get', 'post'], '/penyakitmenular', [LaporanController::class, 'penyakitmenular'])->name('penyakitmenular'); // Menampilkan laporan penyakit menular
Route::match(['get', 'post'], '/igd', [LaporanController::class, 'igd'])->name('igd'); // Menampilkan laporan IGD
Route::match(['get', 'post'], '/operasi', [LaporanController::class, 'operasi'])->name('operasi'); // Menampilkan laporan IGD
Route::match(['get', 'post'], '/kematian', [LaporanController::class, 'kematian'])->name('kematian'); // Menampilkan laporan kematian
Route::match(['get', 'post'], '/pertumbuhan', [LaporanController::class, 'pertumbuhan'])->name('pertumbuhan'); // Menampilkan laporan pertumbuhan
Route::match(['get', 'post'], '/laporan_radlab', [LaporanController::class, 'laporan_radlab'])->name('laporan_radlab'); // Menampilkan laporan kunjungan rawat jalan
Route::match(['get', 'post'], '/ibudanbayi', [LaporanController::class, 'ibudanbayi'])->name('ibudanbayi');


//laporan rm
Route::match(['get', 'post'], '/totalresep', [LaporanController::class, 'totalresep'])->name('totalresep'); // Menampilkan laporan total resep di farmasi
Route::match(['get', 'post'], '/detailresep', [LaporanController::class, 'detailresep'])->name('detailresep');
Route::get('/modalfarmasi', [LaporanController::class, 'getModalResep'])->name('modalfarmasi'); // Menampilkan modal content


// kinerja
Route::match(['get', 'post'], '/kinerja', [KinerjaController::class, 'kinerja'])->name('kinerja');
Route::match(['get', 'post'], '/setjumlahbed', [KinerjaController::class, 'setjumlahbed'])->name('setjumlahbed');

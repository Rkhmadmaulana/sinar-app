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

Route::get('/', function() {
    if (session()->has('authenticated')) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login-proses', [LoginController::class, 'login_proses'])->name('login-proses');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/profil', function () {
    return view('profil');
})->name('profil');

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->name('dashboard')->middleware(\App\Http\Middleware\CheckAuthenticated::class);

Route::middleware([\App\Http\Middleware\CheckAuthenticated::class])->group(function () {

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
    Route::post('/kelengkapan/simpan', [LaporanController::class, 'simpanKelengkapan'])->name('kelengkapan.simpan');


    Route::get('/erm_ranap', [LaporanController::class, 'getERMContent'])->name('erm_ranap'); // Menampilkan modal content
    Route::get('/erm_ranap_cppt', [LaporanController::class, 'getERMCPPT'])->name('erm_ranap_cppt'); // Menampilkan berkas cppt
    Route::get('/erm_ranap_medis_igd', [LaporanController::class, 'getERMMedisIGD'])->name('erm_ranap_medis_igd'); // Menampilkan berkas awal medis igd
    Route::get('/erm_ranap_medis_umum', [LaporanController::class, 'getERMMedisUmum'])->name('erm_ranap_medis_umum'); // Menampilkan berkas awal medis umum
    Route::get('/erm_ranap_catatan_perkembangan', [LaporanController::class, 'getERMCatatanPerkembangan'])->name('erm_ranap_catatan_perkembangan');
    Route::get('/erm_ranap_persetujuan_umum', [LaporanController::class, 'getERMPersetujuanUmum'])->name('erm_ranap_persetujuan_umum'); // Menampilkan berkas persetujuan umum
    Route::get('/erm_ranap_rekonsiliasi_obat', [LaporanController::class, 'getERMRekonsiliasiObat'])->name('erm_ranap_rekonsiliasi_obat');
    Route::get('/erm_ranap_cpo', [LaporanController::class, 'getERMCPO'])->name('erm_ranap_cpo');
    Route::get('/erm_ranap_penunjang', [LaporanController::class, 'getERMPenunjang'])->name('erm_ranap_penunjang');
    Route::get('/erm_ranap_resume', [LaporanController::class, 'getERMResume'])->name('erm_ranap_resume');
    Route::get('/erm_ranap_ews', [LaporanController::class, 'getERMEWS'])->name('erm_ranap_ews');
    Route::get('/erm_ranap_partograf', [LaporanController::class, 'getERMPartograf'])->name('erm_ranap_partograf');
    Route::get('/erm_ranap_sep', [LaporanController::class, 'getERMSEP'])->name('erm_ranap_sep');
    Route::get('/erm_ranap_pra_op', [LaporanController::class, 'getERMPraOp'])->name('erm_ranap_pra_op');
    Route::get('/erm_ranap_pra_sedasi', [LaporanController::class, 'getERMPraSedasi'])->name('erm_ranap_pra_sedasi');
    Route::get('/erm_ranap_laporan_op', [LaporanController::class, 'getERMLaporanOp'])->name('erm_ranap_laporan_op');
    Route::get('/erm_ranap_laporan_op2', [LaporanController::class, 'getERMLaporanOp2'])->name('erm_ranap_laporan_op2');
    Route::get('/erm_ranap_laporan_op3', [LaporanController::class, 'getERMLaporanOp3'])->name('erm_ranap_laporan_op3');
    Route::get('/erm_ranap_laporan_op4', [LaporanController::class, 'getERMLaporanOp4'])->name('erm_ranap_laporan_op4');
    Route::get('/erm_ranap_berkas_digital', [LaporanController::class, 'getERMBerkasDigital'])->name('erm_ranap_berkas_digital');

    Route::get('/erm_dpjp', [LaporanController::class, 'getERMDPJP'])->name('erm_dpjp'); // Menampilkan dpjp
    Route::get('/erm_perencanaan_pemulangan', [LaporanController::class, 'getERMRencanaPemulangan'])->name('erm_perencanaan_pemulangan'); // Menampilkan perencanaan pemulangan
    Route::get('/erm_transfer_pasien_antar_ruang', [LaporanController::class, 'getERMTransferAntarRuang'])->name('erm_transfer_pasien_antar_ruang'); // Menampilkan transfer pasien antar ruang
    Route::get('/erm_catatan_observasi_ranap', [LaporanController::class, 'getERMCatatanObservasi'])->name('erm_catatan_observasi_ranap');
    Route::get('/erm_data_triase_igd', [LaporanController::class, 'getERMTriaseIGD'])->name('erm_data_triase_igd');
    Route::get('/erm_edukasi_pasien_keluarga_rj', [LaporanController::class, 'getERMEdukasi'])->name('erm_edukasi_pasien_keluarga_rj');
    Route::get('/erm_ranap_resikoanak', [LaporanController::class, 'getERMRESIKOANAK'])->name('erm_ranap_resikoanak'); // Menampilkan berkas resiko anak
    Route::get('/erm_ranap_resikolansia', [LaporanController::class, 'getERMRESIKOLANSIA'])->name('erm_ranap_resikolansia'); // Menampilkan berkas resiko lansia
    Route::get('/erm_ranap_icta', [LaporanController::class, 'getERMICTA'])->name('erm_ranap_icta'); // Menampilkan berkas ricta
    Route::get('/erm_ranap_resiko_gabungan', [App\Http\Controllers\LaporanController::class, 'getERMRESIKOGABUNGAN'])->name('erm_ranap_resikogabungan');
    Route::get('/erm_penandaanop', [App\Http\Controllers\LaporanController::class, 'getERMPENANDAANOP'])->name('erm_penandaanop');
    Route::get('/erm_checklistpreop', [App\Http\Controllers\LaporanController::class, 'getERMCHECKLISTPREOP'])->name('erm_checklistpreop');
    Route::get('/erm_penilaianprean', [App\Http\Controllers\LaporanController::class, 'getERMPENILAIANPREAN'])->name('erm_penilaianprean');
    Route::get('/erm_laporananestesi', [App\Http\Controllers\LaporanController::class, 'getERMLAPORANANESTESI'])->name('erm_laporananestesi');
    Route::get('/erm_signoutsebelummenutupluka', [App\Http\Controllers\LaporanController::class, 'getERMSIGNOUT'])->name('erm_signoutsebelummenutupluka');
    Route::get('/erm_persetujuanpenolakan', [App\Http\Controllers\LaporanController::class, 'getERMPP'])->name('erm_persetujuanpenolakan');


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

    //laporan pasien meninggal
    Route::get('/laporan/pasien-meninggal', [LaporanController::class, 'pasienMeninggal'])->name('laporan.pasien-meninggal');
    Route::get('/laporan/get-bangsal', [LaporanController::class, 'getBangsal'])->name('laporan.get-bangsal');
    Route::get('/laporan/get-bangsal/meninggal', [LaporanController::class, 'getBangsalMeninggal'])->name('laporan.get-bangsal-meninggal');

    //laporan rujukan keluar
    Route::get('/laporan/rujukan-keluar', [LaporanController::class, 'laporanRujukanKeluar'])->name('laporan.rujukan-keluar');

    //laporan rujukan masuk
    Route::get('/laporan/rujukan-masuk', [LaporanController::class, 'laporanRujukanMasuk'])->name('laporan.rujukan-masuk');

    //Laporan Persalinan
    Route::get('/laporan/persalinan/detail/{no_rawat}', [LaporanController::class, 'getPersalinanDetail'])
     ->name('laporan.persalinan.detail');
     Route::get('/laporan/persalinan', [LaporanController::class, 'laporanPersalinan'])
    ->name('laporan.laporan_persalinan');
    
    //laporan rujukan rekap
    Route::get('/laporan/rujukan-rekap', [LaporanController::class, 'laporanRujukanRekap'])->name('laporan.rujukan-rekap');
    Route::get('/laporan/rujukan-rekap/detail', [LaporanController::class, 'laporanRujukanRekapDetail'])->name('laporan.rujukan-rekap.detail');
    
    //Morbiditas pasien rawat jalan
    Route::match(['get', 'post'], '/morbiditas-rawat-jalan', [LaporanController::class, 'morbiditasRawatJalan'])->name('morbiditas-rawat-jalan');
    Route::get('/laporan/morbiditas-rawat-jalan/excel', [LaporanController::class, 'exportMorbiditasRawatJalanExcel'])->name('morbiditas-rawat-jalan.excel');

    //Morbiditas pasien rawat inap
    Route::match(['get', 'post'], '/morbiditas-rawat-inap', [LaporanController::class, 'morbiditasRawatInap'])->name('morbiditas-rawat-inap');
    Route::get('/laporan/morbiditas-rawat-inap/excel', [LaporanController::class, 'exportMorbiditasRawatInapExcel'])->name('morbiditas-rawat-inap.excel');


    // kinerja
    Route::match(['get', 'post'], '/kinerja', [KinerjaController::class, 'kinerja'])->name('kinerja');
    Route::match(['get', 'post'], '/setjumlahbed', [KinerjaController::class, 'setjumlahbed'])->name('setjumlahbed');
    
    Route::get('/berkas-image/{path}', function($path) {
        $fullPath = base_path('../webapps/berkasrawat/pages/upload/' . $path);
        if(file_exists($fullPath)) {
            $type = mime_content_type($fullPath);
            header('Content-Type: '.$type);
            readfile($fullPath);
            exit;
        }
        return response('File not found', 404);
    })->where('path', '.*');

});

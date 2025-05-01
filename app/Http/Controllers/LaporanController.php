<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{

    public function kelengkapanrm(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Check if $tgl1 is empty, if so, set it to the first day of the current month
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }
        // Check if $tgl2 is empty, if so, set it to today's date
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }
        // Format the dates
        if (!empty($tgl1Input) && !empty($tgl2Input)) {
            $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal

        // Start Ambil Semua Nomor Rawat
        $sqlnr = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->where('a.status_lanjut', '=', 'Ranap')
            ->orderBy('a.no_rawat', 'desc')
            ->get();
        // End Ambil Semua Nomor Rawat

        return view('rm.laporan_rm.kelengkapan_rm', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,

            'nmr_rwt' => $sqlnr
        ]);
    }

    //ambil NO RAWAT pasien
    public function getModalContent(Request $request)
    {
        // Ambil data berdasarkan ID
        $id = $request->query('id'); 
        $data = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.no_rawat', '=', $id)->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $data1 = DB::table('reg_periksa as a')
                ->join('resume_pasien as b', 'b.no_rawat', '=', 'a.no_rawat')
                ->where('b.no_rawat', '=', $id)->first();
        if(!empty($data1)){
            $data1 = 'L';
        }else{
            $data1 = 'TL';
        }
        $data2 = DB::table('reg_periksa as a')
                ->join('resume_pasien_ranap as b', 'b.no_rawat', '=', 'a.no_rawat')
                ->where('b.no_rawat', '=', $id)->first();
        if(!empty($data2)){
            $data2 = 'L';
        }else{
            $data2 = 'TL';
        }

        // Kirim data ke view modal-content.blade.php
        return view('rm.laporan_rm.modal-content', [
            'data' => $data,
            'data2' => $data1,
            'data3' => $data2
        ]);
    }
    //ambil NO RAWAT pasien
    public function getERMContent(Request $request)
    {
        // Ambil data berdasarkan ID
        $id = $request->query('id'); 
        $data = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.no_rawat', '=', $id)
                ->where('a.status_lanjut', '=', 'Ranap')
                ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $general_consent = DB::table('surat_persetujuan_umum as a')
                ->join('surat_persetujuan_umum_pembuat_pernyataan as b', 'b.no_surat', '=', 'a.no_surat')
                ->where('a.no_rawat', '=', $id)->first();

        $ews_dewasa = DB::table('pemantauan_pews_dewasa as a')
                ->where('a.no_rawat', '=', $id)->get();
        $ews_anak = DB::table('pemantauan_pews_anak as a')
                ->where('a.no_rawat', '=', $id)->get();
        $ews_neonatus = DB::table('pemantauan_ews_neonatus as a')
                ->where('a.no_rawat', '=', $id)->get();
        if(!empty($ews_dewasa)){
            $ews = $ews_dewasa;
        }elseif(!empty($ews_anak)){
            $ews = $ews_anak;
        }elseif(!empty($ews_neonatus)){
            $ews = $ews_neonatus;
        }else{
            $ews = null;
        }

        $awal_keperawatan_anak = DB::table('penilaian_awal_keperawatan_ranap_bayi as a')
                ->where('a.no_rawat', '=', $id)->get();

        $awal_keperawatan_dewasa = DB::table('penilaian_awal_keperawatan_ranap as a')
                ->where('a.no_rawat', '=', $id)->get();

        if(!empty($awal_keperawatan_anak)){
            $awal_keperawatan = $awal_keperawatan_anak;
        }elseif(!empty($awal_keperawatan_dewasa)){
            $awal_keperawatan = $awal_keperawatan_dewasa;
        }else{
            $awal_keperawatan = null;
        }

        $awal_medis_umum = DB::table('penilaian_medis_ranap as a')
                ->where('a.no_rawat', '=', $id)->get();

        $partograf = DB::table('berkas_digital_perawatan as a')
                ->where('a.no_rawat', '=', $id)
                ->where('a.kode', '=', '012')->get();

        $rekonsiliasi_obat = DB::table('rekonsiliasi_obat as a')
                ->where('a.no_rawat', '=', $id)->get();
        
        // if($rekonsiliasi_obat){
        //     $no_rekonsiliasi = $rekonsiliasi_obat['no_rekonsiliasi'];
        //     $rekonsiliasi_obat_detail = DB::table('rekonsiliasi_obat_detail_obat as a')
        //         ->where('a.no_rekonsiliasi', '=', $no_rekonsiliasi)->get();
        // }

        $grafik_suhunadi = DB::table('berkas_digital_perawatan as a')
                ->where('a.no_rawat', '=', $id)
                ->where('a.kode', '=', '014')->get();

        $soap = DB::table('pemeriksaan_ranap as a')
                ->join('pegawai as b', 'b.nik', '=', 'a.nip')
                ->where('a.no_rawat', '=', $id)->get();

        $soapigd = DB::table('pemeriksaan_ralan as a')
                ->join('pegawai as b', 'b.nik', '=', 'a.nip')
                ->where('a.no_rawat', '=', $id)->get();

        $ctt_keperawatan = DB::table('catatan_keperawatan_ranap as a')
                ->where('a.no_rawat', '=', $id)->get();

        $ctt_penggunaan_obat = "";

        $permintaan_lab = DB::table('permintaan_lab as a')
                ->where('a.no_rawat', '=', $id)->get();

        $periksa_lab = DB::table('periksa_lab as a')
                ->where('a.no_rawat', '=', $id)->get();

        $detail_periksa_lab = DB::table('detail_periksa_lab as a')
                ->where('a.no_rawat', '=', $id)->get();
        
        $permintaan_radiologi = DB::table('permintaan_radiologi as a')
                ->where('a.no_rawat', '=', $id)->get();

        $periksa_radiologi = DB::table('periksa_radiologi as a')
                ->where('a.no_rawat', '=', $id)->get();

        $hasil_radiologi = DB::table('hasil_radiologi as a')
                ->where('a.no_rawat', '=', $id)->get();

        $edukasi = "";

        $px_keluar = DB::table('resume_pasien_ranap as a')
                ->where('a.no_rawat', '=', $id)->get();

        $px_pulang_djiwa = ""; //tdk ada di db

        $discharge_planning = ""; //tdk ada di db

        $form_dpjp = DB::table('dpjp_ranap as a')
                ->where('a.no_rawat', '=', $id)->get();

        $triase = "";

        $suket_triase = "";

        $awal_medis_igd = DB::table('penilaian_medis_igd as a')
                ->where('a.no_rawat', '=', $id)->get();

        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm', [
            'row' => $data,
            'soap_ranap' => $soap,
            'soap_igd' => $soapigd,
            'ctt_keperawatan' => $ctt_keperawatan,
            'general_consent' => $general_consent,
            'ews' => $ews,
            'awal_keperawatan_ranap' => $awal_keperawatan,
            'awal_medis_umum' => $awal_medis_umum,
            'awal_med_igd' => $awal_medis_igd,
        ]);
    }

    public function getERMCPPT(Request $request)
    {
        // Ambil data berdasarkan ID
        $id = $request->query('id'); 
        $data = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.no_rawat', '=', $id)
                ->where('a.status_lanjut', '=', 'Ranap')
                ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $cppt = DB::table('pemeriksaan_ranap as a')
                ->join('pegawai as b', 'b.nik', '=', 'a.nip')
                ->where('a.no_rawat', '=', $id)->get();

        $cpptigd = DB::table('pemeriksaan_ralan as a')
                ->join('pegawai as b', 'b.nik', '=', 'a.nip')
                ->where('a.no_rawat', '=', $id)->get();

        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_cppt', [
            'row' => $data,
            'cppt_ranap' => $cppt,
            'cppt_igd' => $cpptigd,
        ]);
    }

    
    public function getERMMedisIGD(Request $request)
    {
        // Ambil data berdasarkan ID
        $id = $request->query('id'); 
        $data = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.no_rawat', '=', $id)
                ->where('a.status_lanjut', '=', 'Ranap')
                ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $awal_medis_igd = DB::table('penilaian_medis_igd as a')
                ->where('a.no_rawat', '=', $id)->get();

        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_medis_igd', [
            'row' => $data,
            'awal_med_igd' => $awal_medis_igd,
        ]);
    }

    public function getERMMedisUmum(Request $request)
    {
        // Ambil data berdasarkan ID
        $id = $request->query('id'); 
        $data = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.no_rawat', '=', $id)
                ->where('a.status_lanjut', '=', 'Ranap')
                ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $awal_medis_umum = DB::table('penilaian_medis_ranap as a')
                ->where('a.no_rawat', '=', $id)->get();

        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_medis_umum', [
            'row' => $data,
            'awal_medis_umum' => $awal_medis_umum,
        ]);
    }

    public function getERMCatatanPerkembangan(Request $request)
    {
        // Ambil data berdasarkan ID
        $id = $request->query('id'); 
        $data = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.no_rawat', '=', $id)
                ->where('a.status_lanjut', '=', 'Ranap')
                ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $awal_keperawatan_anak = DB::table('penilaian_awal_keperawatan_ranap_bayi as a')
                ->where('a.no_rawat', '=', $id)->get();

        $awal_keperawatan_dewasa = DB::table('penilaian_awal_keperawatan_ranap as a')
                ->where('a.no_rawat', '=', $id)->get();

        if(!empty($awal_keperawatan_anak)){
            $awal_keperawatan = $awal_keperawatan_anak;
        }elseif(!empty($awal_keperawatan_dewasa)){
            $awal_keperawatan = $awal_keperawatan_dewasa;
        }else{
            $awal_keperawatan = null;
        }

        $ctt_keperawatan = DB::table('catatan_keperawatan_ranap as a')
                ->where('a.no_rawat', '=', $id)->get();

        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_catatan_perkembangan', [
            'row' => $data,
            'ctt_keperawatan' => $ctt_keperawatan,
            'awal_keperawatan_ranap' => $awal_keperawatan,
        ]);
    }

    public function getERMPersetujuanUmum(Request $request)
    {
        // Ambil data berdasarkan ID
        $id = $request->query('id'); 
        $data = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.no_rawat', '=', $id)
                ->where('a.status_lanjut', '=', 'Ranap')
                ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $persetujuan_umum = DB::table('surat_persetujuan_umum as a')
                ->join('pegawai as b', 'b.nik', '=', 'a.nip')
                ->where('a.no_rawat', '=', $id)
                ->get();

        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_persetujuan_umum', [
            'row' => $data,
            'persetujuan_umum' => $persetujuan_umum,
        ]);
    }

    public function getERMRekonsiliasiObat(Request $request)
    {
        // Ambil data berdasarkan ID
        $id = $request->query('id'); 
        $data = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.no_rawat', '=', $id)
                ->where('a.status_lanjut', '=', 'Ranap')
                ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $rekonsiliasi_obat = DB::table('rekonsiliasi_obat as a')
                ->join('pegawai as b', 'b.nik', '=', 'a.nip')
                ->where('a.no_rawat', '=', $id)->get();

        $detail_rekonsiliasi_obat = DB::table('rekonsiliasi_obat_detail_obat as rod')
                ->join('rekonsiliasi_obat as ro', 'rod.no_rekonsiliasi', '=', 'ro.no_rekonsiliasi')
                ->where('ro.no_rawat', '=', $id)
                ->select('*')
                ->get();

        // Kirim data ke view erm.blade.php 
        return view('rm.laporan_rm.berkas_rm.erm_rekonsiliasi_obat', [
            'row' => $data,
            'rekonsiliasi_obat' => $rekonsiliasi_obat,
            'detail_rekonsiliasi_obat' => $detail_rekonsiliasi_obat,
        ]);
    }

    public function getERMCPO(Request $request)
    {
        // Ambil data berdasarkan ID
        $id = $request->query('id'); 
        $data = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.no_rawat', '=', $id)
                ->where('a.status_lanjut', '=', 'Ranap')
                ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $cpo = DB::table('pemberian_obat as a')
                ->leftJoin('pegawai as b', 'b.nik', '=', 'a.nip_petugas1')
                ->leftJoin('pegawai as c', 'c.nik', '=', 'a.nip_petugas2')
                ->where('a.no_rawat', '=', $id)
                ->select('a.*', 'b.nama as nama_petugas1', 'c.nama as nama_petugas2')
                ->get();

            
        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_cpo', [
            'row' => $data,
            'cpo' => $cpo,
        ]);
    }

    public function getERMPenunjang(Request $request)
    {
        // Ambil data berdasarkan ID
        $id = $request->query('id'); 
        $data = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.no_rawat', '=', $id)
                ->where('a.status_lanjut', '=', 'Ranap')
                ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $lab = DB::table('detail_periksa_lab as dpl')
                ->join('permintaan_lab as pl', 'dpl.no_rawat', '=', 'pl.no_rawat')
                ->join('dokter as b', 'b.kd_dokter', '=', 'pl.dokter_perujuk')
                ->join('jns_perawatan_lab as jpl', 'jpl.kd_jenis_prw', '=', 'dpl.kd_jenis_prw')
                ->join('template_laboratorium as d', 'd.id_template', '=', 'dpl.id_template')
                ->where('dpl.no_rawat', $id)
                ->select(
                    'dpl.no_rawat',
                    'jpl.nm_perawatan',
                    'dpl.kd_jenis_prw',
                    'dpl.id_template',
                    'd.satuan',
                    'dpl.nilai',
                    'dpl.nilai_rujukan',
                    'dpl.tgl_periksa',
                    'dpl.jam',
                    'dpl.keterangan',
                    'd.pemeriksaan',
                    'b.nm_dokter'
                )
                ->orderBy('dpl.tgl_periksa', 'desc')
                ->get()
                ->groupBy('nm_perawatan');

        $radiologi = DB::table('hasil_radiologi as hr')
                ->join('permintaan_radiologi as pr', 'hr.no_rawat', '=', 'pr.no_rawat')
                ->join('dokter as b', 'b.kd_dokter', '=', 'pr.dokter_perujuk')
                ->select(
                    'hr.tgl_periksa',
                    'hr.jam',
                    'hr.hasil',
                    'b.nm_dokter')
                ->where('hr.no_rawat', '=', $id)
                ->get(); 

        // Kirim data ke view erm.blade.php 
        return view('rm.laporan_rm.berkas_rm.erm_penunjang', [
            'row' => $data,
            'lab' => $lab,
            'radiologi' => $radiologi,
        ]);
    }

    public function getERMResume(Request $request)
    {
        // Ambil data berdasarkan ID
        $id = $request->query('id'); 
        $data = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->where('a.no_rawat', '=', $id)
                ->where('a.status_lanjut', '=', 'Ranap')
                ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $resume = DB::table('resume_pasien_ranap as a')
                ->join('pegawai as b', 'b.nik', '=', 'a.nip')
                ->where('a.no_rawat', '=', $id)
                ->get();

            
        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_resume', [
            'row' => $data,
            'resume' => $resume,
        ]);
    }

    public function kunjunganrajal(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Check if $tgl1 is empty, if so, set it to the first day of the current month
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }
        // Check if $tgl2 is empty, if so, set it to today's date
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }
        // Format the dates
        if (!empty($tgl1Input) && !empty($tgl2Input)) {
            $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal

        // start SQL ANGGOTA POLRI
        $sqlanggotapolri = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('c.golongan_polri', '=', '1')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as anggota_polri'), DB::raw('COUNT(a.no_rkm_medis) as kunjungan_anggota_polri'))
            ->first();
        // end SQL ANGGOTA POLRI

        // start SQL ANGGOTA PNS
        $sqlanggotapns = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.golongan_polri', '=', '2')
                    ->orwhere('c.golongan_polri', '=', '7')
                    ->orwhere('c.golongan_polri', '=', '8')
                    ->orwhere('c.golongan_polri', '=', '10');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as anggota_pns'), DB::raw('COUNT(a.no_rkm_medis) as kunjungan_anggota_pns'))
            ->first();
        // end SQL ANGGOTA PNS

        // start SQL Keluarga Polri
        $sqlanggotakelpolri = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.golongan_polri', '=', '9')
                    ->orwhere('c.golongan_polri', '=', '3');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as anggota_kel_polri'), DB::raw('COUNT(a.no_rkm_medis) as kunjungan_kel_polri'))
            ->first();
        // end SQL Keluarga Polri

        // start SQL ANGGOTA SISWA DIKBANG
        $sqlanggotadikbang = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.golongan_polri', '=', '4')
                    ->orwhere('c.golongan_polri', '=', '6');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as siswa_dikbang'), DB::raw('COUNT(a.no_rkm_medis) as kunjungan_siswa_dikbang'))
            ->first();
        // end SQL ANGGOTA SISWA DIKBANG

        // start SQL ANGGOTA SISWA DIKTUK
        $sqlanggotadiktuk = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('c.golongan_polri', '=', '5')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as siswa_diktuk'), DB::raw('COUNT(a.no_rkm_medis) as kunjungan_siswa_diktuk'))
            ->first();
        // end SQL ANGGOTA SISWA DIKTUK

        // Start Total pasien bpjs khusus
        $pasien_total_khusus_pengunjung =
            $sqlanggotapolri->anggota_polri +
            $sqlanggotapns->anggota_pns +
            $sqlanggotadikbang->siswa_dikbang  +
            $sqlanggotadiktuk->siswa_diktuk  +
            $sqlanggotakelpolri->anggota_kel_polri;

        $pasien_total_khusus_kunjungan =
            $sqlanggotapolri->kunjungan_anggota_polri +
            $sqlanggotapns->kunjungan_anggota_pns +
            $sqlanggotadikbang->kunjungan_siswa_dikbang  +
            $sqlanggotadiktuk->kunjungan_siswa_diktuk  +
            $sqlanggotakelpolri->kunjungan_kel_polri;
        // End Total pasien bpjs khusus

        // start SQL pasien bpjs
        $sqlpasienbpjs = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->where('a.kd_pj', '=', 'BPJ')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as pasienbpjs'), DB::raw('COUNT(a.no_rkm_medis) as kunjungan_pasienbpjs'))
            ->first();
        $total_pengunjung_bpjs = $sqlpasienbpjs->pasienbpjs - $pasien_total_khusus_pengunjung;
        $total_kunjungan_bpjs = $sqlpasienbpjs->kunjungan_pasienbpjs - $pasien_total_khusus_kunjungan;
        // end SQL pasien bpjs

        // start SQL pasien UMUM
        $sqlpasienumum = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->where('a.kd_pj', '=', 'UMU')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as pasienumum'), DB::raw('COUNT(a.no_rkm_medis) as kunjungan_pasienumum'))
            ->first();
        // end SQL pasien UMUM

        // start SQL pasien other
        $sqlpasienother = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->where('a.kd_pj', '!=', 'UMU')
            ->where('a.kd_pj', '!=', 'BPJ')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rkm_medis) as pasienother'), DB::raw('COUNT(a.no_rkm_medis) as kunjungan_pasienother'))
            ->first();
        // end SQL pasien other

        $total_pengunjung =   $pasien_total_khusus_pengunjung + $total_pengunjung_bpjs + $sqlpasienumum->pasienumum + $sqlpasienother->pasienother;
        $total_kunjungan =   $pasien_total_khusus_kunjungan + $total_kunjungan_bpjs + $sqlpasienumum->kunjungan_pasienumum + $sqlpasienother->kunjungan_pasienother;

        return view('rm.laporan_rm.kunjungan_rajal', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,

            'anggotapolri' => $sqlanggotapolri,
            'anggotapns' => $sqlanggotapns,
            'anggotakelpolri' => $sqlanggotakelpolri,
            'dikbang' => $sqlanggotadikbang,
            'diktuk' => $sqlanggotadiktuk,
            'pasien_umum' => $sqlpasienumum,
            'pasien_other' => $sqlpasienother,
            'total_pengunjung_bpjs' => $total_pengunjung_bpjs,
            'total_kunjungan_bpjs' => $total_kunjungan_bpjs,
            'total_pengunjung' => $total_pengunjung,
            'total_kunjungan' => $total_kunjungan,
        ]);
    }

    public function kunjunganranap(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Check if $tgl1 is empty, if so, set it to the first day of the current month
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }
        // Check if $tgl2 is empty, if so, set it to today's date
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }
        // Format the dates
        if (!empty($tgl1Input) && !empty($tgl2Input)) {
            $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal

        // start SQL ANGGOTA POLRI
        $sqlanggotapolri = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d', 'd.no_rawat', '=', 'a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('c.golongan_polri', '=', '1')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as anggota_polri'))
            ->first();
        // end SQL ANGGOTA POLRI
        // start SQL ANGGOTA PNS
        $sqlanggotapns = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d', 'd.no_rawat', '=', 'a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.golongan_polri', '=', '2')
                    ->orwhere('c.golongan_polri', '=', '7')
                    ->orwhere('c.golongan_polri', '=', '8')
                    ->orwhere('c.golongan_polri', '=', '10');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as anggota_pns'))
            ->first();
        // end SQL ANGGOTA PNS

        // start SQL Keluarga Polri
        $sqlanggotakelpolri = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d', 'd.no_rawat', '=', 'a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.golongan_polri', '=', '3')
                    ->orwhere('c.golongan_polri', '=', '9');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as anggota_kel_polri'))
            ->first();
        // end SQL Keluarga Polri

        // start SQL ANGGOTA SISWA DIKBANG
        $sqlanggotadikbang = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d', 'd.no_rawat', '=', 'a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.golongan_polri', '=', '4')
                    ->orwhere('c.golongan_polri', '=', '6');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as siswa_dikbang'))
            ->first();
        // end SQL ANGGOTA SISWA DIKBANG
        // start SQL ANGGOTA SISWA DIKTUK
        $sqlanggotadiktuk = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('pasien_polri as c', 'c.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d', 'd.no_rawat', '=', 'a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('c.golongan_polri', '=', '5')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as siswa_diktuk'))
            ->first();
        // end SQL ANGGOTA SISWA DIKTUK

        // Start Total pasien bpjs khusus
        $pasien_total_khusus_pengunjung =
            $sqlanggotapolri->anggota_polri +
            $sqlanggotapns->anggota_pns +
            $sqlanggotadikbang->siswa_dikbang  +
            $sqlanggotadiktuk->siswa_diktuk  +
            $sqlanggotakelpolri->anggota_kel_polri;
        // End Total pasien bpjs khusus

        // start SQL pasien bpjs
        $sqlpasienbpjs = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d', 'd.no_rawat', '=', 'a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'BPJ')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pasienbpjs'))
            ->first();
        $total_pengunjung_bpjs = $sqlpasienbpjs->pasienbpjs - $pasien_total_khusus_pengunjung;
        // end SQL pasien bpjs

        // start SQL pasien UMUM
        $sqlpasienumum = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d', 'd.no_rawat', '=', 'a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '=', 'UMU')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pasienumum'))
            ->first();
        // end SQL pasien UMUM

        // start SQL pasien other
        $sqlpasienother = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as d', 'd.no_rawat', '=', 'a.no_rawat')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->where('a.kd_pj', '!=', 'UMU')
            ->where('a.kd_pj', '!=', 'BPJ')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('d.tgl_keluar', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(a.no_rkm_medis) as pasienother'))
            ->first();
        // end SQL pasien other
        $total_pengunjung =   $pasien_total_khusus_pengunjung + $total_pengunjung_bpjs + $sqlpasienumum->pasienumum + $sqlpasienother->pasienother;
        return view('rm.laporan_rm.kunjungan_ranap', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,

            'anggotapolri' => $sqlanggotapolri,
            'anggotapns' => $sqlanggotapns,
            'anggotakelpolri' => $sqlanggotakelpolri,
            'dikbang' => $sqlanggotadikbang,
            'diktuk' => $sqlanggotadiktuk,
            'pasien_umum' => $sqlpasienumum,
            'pasien_other' => $sqlpasienother,
            'total_pengunjung_bpjs' => $total_pengunjung_bpjs,
            'total_pengunjung' => $total_pengunjung,

        ]);
    }

    public function penyakitterbanyak(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Check if $tgl1 is empty, if so, set it to the first day of the current month
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }
        // Check if $tgl2 is empty, if so, set it to today's date
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }
        // Format the dates
        if (!empty($tgl1Input) && !empty($tgl2Input)) {
            $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal

        // Start Penyakit terbanyak Ranap
        $sqldiagnosa = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as b', 'b.no_rawat', '=', 'a.no_rawat')
            ->join('penyakit as c', 'c.kd_penyakit', '=', 'b.kd_penyakit')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->groupBy('c.kd_penyakit', 'c.nm_penyakit') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(c.nm_penyakit, 30) as nama'), 'c.kd_penyakit as kode', DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        // End Penyakit terbanyak Ranap

        // Start Penyakit terbanyak Ralan
        $sqldiagnosaralan = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as b', 'b.no_rawat', '=', 'a.no_rawat')
            ->join('penyakit as c', 'c.kd_penyakit', '=', 'b.kd_penyakit')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->groupBy('c.kd_penyakit', 'c.nm_penyakit') // Menambahkan klausa groupBy
            ->select(DB::raw('LEFT(c.nm_penyakit, 30) as nama'), 'c.kd_penyakit as kode', DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        // End Penyakit terbanyak Ralan

        // start SQL pasien Baru
        $sqlpasienbaru = DB::table('reg_periksa as a')
            ->where('a.stts_daftar', '=', 'Baru')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pasienbaru'))
            ->first();
        // end SQL pasien Baru
        return view('rm.laporan_rm.penyakit_terbanyak', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,

            'diagnosa' => $sqldiagnosa,
            'diagnosa_ralan' => $sqldiagnosaralan,
            'pasien_baru' => $sqlpasienbaru,
        ]);
    }

    public function penyakitmenular(Request $request)
    {

        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Check if $tgl1 is empty, if so, set it to the first day of the current month
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }
        // Check if $tgl2 is empty, if so, set it to today's date
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }
        // Format the dates
        if (!empty($tgl1Input) && !empty($tgl2Input)) {
            $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal

        // START HIV
        // START ANGGOTA 
        $sqlanggotahiv = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '1')
            ->where('c.kd_penyakit', 'like', '%B20%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
            ->first();
        // END ANGGOTA

        // START pns 
        $sqlpnshiv = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '2')
                    ->orwhere('b.golongan_polri', '=', '7')
                    ->orwhere('b.golongan_polri', '=', '8')
                    ->orwhere('b.golongan_polri', '=', '10');
            })
            ->where('c.kd_penyakit', 'like', '%B20%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
            ->first();
        // END pns

        // START Dikbang
        $sqldikbanghiv = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '6')
                    ->orwhere('b.golongan_polri', '=', '4');
            })
            ->where('c.kd_penyakit', 'like', '%B20%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
            ->first();
        // END Dikbang

        // START Diktuk
        $sqldiktukhiv = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '5')
            ->where('c.kd_penyakit', 'like', '%B20%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
            ->first();
        // END Diktuk

        // START Kel polri
        $sqlkelpolrihiv = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '9')
                    ->orwhere('b.golongan_polri', '=', '3');
            })
            ->where('c.kd_penyakit', 'like', '%B20%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
            ->first();
        // END Kel polri

        // START Umum
        $sqlumuhiv = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'UMU')
            ->where('c.kd_penyakit', 'like', '%B20%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
            ->first();
        // END Umum

        //start total bpjs khusus
        $total_khusus_hiv =
            $sqlanggotahiv->hiv +
            $sqlpnshiv->hiv +
            $sqldikbanghiv->hiv  +
            $sqldiktukhiv->hiv  +
            $sqlkelpolrihiv->hiv;
        //end total bpjs khusus

        // START bpjs
        $sqlbpjshiv = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('c.kd_penyakit', 'like', '%B20%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
            ->first();
        $total_bpjshiv = $sqlbpjshiv->hiv - $total_khusus_hiv;
        // END bpjs
        // START lainnya
        $sqllainnyahiv = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '!=', 'UMU')
            ->where('a.kd_pj', '!=', 'BPJ')
            ->where('c.kd_penyakit', 'like', '%B20%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hiv'))
            ->first();
        // END lainnya
        $total_hiv = $total_khusus_hiv + $total_bpjshiv + $sqllainnyahiv->hiv + $sqlumuhiv->hiv;
        // END HIV

        // START tb
        // START ANGGOTA 
        $sqlanggotatb = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '1')
            ->where('c.kd_penyakit', 'like', '%A15%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
            ->first();
        // END ANGGOTA

        // START pns 
        $sqlpnstb = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '2')
                    ->orwhere('b.golongan_polri', '=', '7')
                    ->orwhere('b.golongan_polri', '=', '8')
                    ->orwhere('b.golongan_polri', '=', '10');
            })
            ->where('c.kd_penyakit', 'like', '%A15%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
            ->first();
        // END pns

        // START Dikbang
        $sqldikbangtb = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '4')
                    ->orwhere('b.golongan_polri', '=', '6');
            })
            ->where('c.kd_penyakit', 'like', '%A15%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
            ->first();
        // END Dikbang

        // START Diktuk
        $sqldiktuktb = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '5')
            ->where('c.kd_penyakit', 'like', '%A15%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
            ->first();
        // END Diktuk

        // START Kel polri
        $sqlkelpolritb = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '9')
                    ->orwhere('b.golongan_polri', '=', '3');
            })
            ->where('c.kd_penyakit', 'like', '%A15%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
            ->first();
        // END Kel polri

        // START Umum
        $sqlumutb = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'UMU')
            ->where('c.kd_penyakit', 'like', '%A15%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
            ->first();
        // END Umum

        //start total bpjs khusus
        $total_khusus_tb =
            $sqlanggotatb->tb +
            $sqlpnstb->tb +
            $sqldikbangtb->tb  +
            $sqldiktuktb->tb  +
            $sqlkelpolritb->tb;
        //end total bpjs khusus

        // START bpjs
        $sqlbpjstb = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('c.kd_penyakit', 'like', '%A15%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
            ->first();
        $total_bpjstb = $sqlbpjstb->tb - $total_khusus_tb;
        // END bpjs
        // START lainnya
        $sqllainnyatb = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '!=', 'UMU')
            ->where('a.kd_pj', '!=', 'BPJ')
            ->where('c.kd_penyakit', 'like', '%A15%')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as tb'))
            ->first();
        // END lainnya
        $total_tb = $total_khusus_tb + $total_bpjstb + $sqllainnyatb->tb + $sqlumutb->tb;
        // END tb

        // START malaria
        // START ANGGOTA 
        $sqlanggotamalaria = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '1')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B50%')
                    ->orWhere('c.kd_penyakit', 'like', '%B51%')
                    ->orWhere('c.kd_penyakit', 'like', '%B52%')
                    ->orWhere('c.kd_penyakit', 'like', '%B53%')
                    ->orWhere('c.kd_penyakit', 'like', '%B54%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
            ->first();
        // END ANGGOTA

        // START pns 
        $sqlpnsmalaria = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '2')
                    ->orwhere('b.golongan_polri', '=', '7')
                    ->orwhere('b.golongan_polri', '=', '8')
                    ->orwhere('b.golongan_polri', '=', '10');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B50%')
                    ->orWhere('c.kd_penyakit', 'like', '%B51%')
                    ->orWhere('c.kd_penyakit', 'like', '%B52%')
                    ->orWhere('c.kd_penyakit', 'like', '%B53%')
                    ->orWhere('c.kd_penyakit', 'like', '%B54%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
            ->first();
        // END pns

        // START Dikbang
        $sqldikbangmalaria = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '4')
                    ->orwhere('b.golongan_polri', '=', '6');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B50%')
                    ->orWhere('c.kd_penyakit', 'like', '%B51%')
                    ->orWhere('c.kd_penyakit', 'like', '%B52%')
                    ->orWhere('c.kd_penyakit', 'like', '%B53%')
                    ->orWhere('c.kd_penyakit', 'like', '%B54%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
            ->first();
        // END Dikbang

        // START Diktuk
        $sqldiktukmalaria = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '5')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B50%')
                    ->orWhere('c.kd_penyakit', 'like', '%B51%')
                    ->orWhere('c.kd_penyakit', 'like', '%B52%')
                    ->orWhere('c.kd_penyakit', 'like', '%B53%')
                    ->orWhere('c.kd_penyakit', 'like', '%B54%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
            ->first();
        // END Diktuk

        // START Kel polri
        $sqlkelpolrimalaria = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '9')
                    ->orwhere('b.golongan_polri', '=', '3');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B50%')
                    ->orWhere('c.kd_penyakit', 'like', '%B51%')
                    ->orWhere('c.kd_penyakit', 'like', '%B52%')
                    ->orWhere('c.kd_penyakit', 'like', '%B53%')
                    ->orWhere('c.kd_penyakit', 'like', '%B54%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
            ->first();
        // END Kel polri

        // START Umum
        $sqlumumalaria = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'UMU')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B50%')
                    ->orWhere('c.kd_penyakit', 'like', '%B51%')
                    ->orWhere('c.kd_penyakit', 'like', '%B52%')
                    ->orWhere('c.kd_penyakit', 'like', '%B53%')
                    ->orWhere('c.kd_penyakit', 'like', '%B54%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
            ->first();
        // END Umum

        //start total bpjs khusus
        $total_khusus_malaria =
            $sqlanggotamalaria->malaria +
            $sqlpnsmalaria->malaria +
            $sqldikbangmalaria->malaria  +
            $sqldiktukmalaria->malaria  +
            $sqlkelpolrimalaria->malaria;
        //end total bpjs khusus

        // START bpjs
        $sqlbpjsmalaria = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B50%')
                    ->orWhere('c.kd_penyakit', 'like', '%B51%')
                    ->orWhere('c.kd_penyakit', 'like', '%B52%')
                    ->orWhere('c.kd_penyakit', 'like', '%B53%')
                    ->orWhere('c.kd_penyakit', 'like', '%B54%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
            ->first();
        $total_bpjsmalaria = $sqlbpjsmalaria->malaria - $total_khusus_malaria;
        // END bpjs
        // START lainnya
        $sqllainnyamalaria = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '!=', 'UMU')
            ->where('a.kd_pj', '!=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B50%')
                    ->orWhere('c.kd_penyakit', 'like', '%B51%')
                    ->orWhere('c.kd_penyakit', 'like', '%B52%')
                    ->orWhere('c.kd_penyakit', 'like', '%B53%')
                    ->orWhere('c.kd_penyakit', 'like', '%B54%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as malaria'))
            ->first();
        // END lainnya
        $total_malaria = $total_khusus_malaria + $total_bpjsmalaria + $sqllainnyamalaria->malaria + $sqlumumalaria->malaria;
        // END malaria

        // START dbd
        // START ANGGOTA 
        $sqlanggotadbd = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '1')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%A90%')
                    ->orWhere('c.kd_penyakit', 'like', '%A91%')
                    ->orWhere('c.kd_penyakit', 'like', '%A92%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
            ->first();
        // END ANGGOTA

        // START pns 
        $sqlpnsdbd = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '2')
                    ->orwhere('b.golongan_polri', '=', '7')
                    ->orwhere('b.golongan_polri', '=', '8')
                    ->orwhere('b.golongan_polri', '=', '10');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%A90%')
                    ->orWhere('c.kd_penyakit', 'like', '%A91%')
                    ->orWhere('c.kd_penyakit', 'like', '%A92%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
            ->first();
        // END pns

        // START Dikbang
        $sqldikbangdbd = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '4')
                    ->orwhere('b.golongan_polri', '=', '6');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%A90%')
                    ->orWhere('c.kd_penyakit', 'like', '%A91%')
                    ->orWhere('c.kd_penyakit', 'like', '%A92%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
            ->first();
        // END Dikbang

        // START Diktuk
        $sqldiktukdbd = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '5')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%A90%')
                    ->orWhere('c.kd_penyakit', 'like', '%A91%')
                    ->orWhere('c.kd_penyakit', 'like', '%A92%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
            ->first();
        // END Diktuk

        // START Kel polri
        $sqlkelpolridbd = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '9')
                    ->orwhere('b.golongan_polri', '=', '3');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%A90%')
                    ->orWhere('c.kd_penyakit', 'like', '%A91%')
                    ->orWhere('c.kd_penyakit', 'like', '%A92%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
            ->first();
        // END Kel polri

        // START Umum
        $sqlumudbd = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'UMU')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%A90%')
                    ->orWhere('c.kd_penyakit', 'like', '%A91%')
                    ->orWhere('c.kd_penyakit', 'like', '%A92%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
            ->first();
        // END Umum

        //start total bpjs khusus
        $total_khusus_dbd =
            $sqlanggotadbd->dbd +
            $sqlpnsdbd->dbd +
            $sqldikbangdbd->dbd  +
            $sqldiktukdbd->dbd  +
            $sqlkelpolridbd->dbd;
        //end total bpjs khusus

        // START bpjs
        $sqlbpjsdbd = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%A90%')
                    ->orWhere('c.kd_penyakit', 'like', '%A91%')
                    ->orWhere('c.kd_penyakit', 'like', '%A92%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
            ->first();
        $total_bpjsdbd = $sqlbpjsdbd->dbd - $total_khusus_dbd;
        // END bpjs
        // START lainnya
        $sqllainnyadbd = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '!=', 'UMU')
            ->where('a.kd_pj', '!=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%A90%')
                    ->orWhere('c.kd_penyakit', 'like', '%A91%')
                    ->orWhere('c.kd_penyakit', 'like', '%A92%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as dbd'))
            ->first();
        // END lainnya
        $total_dbd = $total_khusus_dbd + $total_bpjsdbd + $sqllainnyadbd->dbd + $sqlumudbd->dbd;
        // END dbd

        // START pms
        // START ANGGOTA 
        $sqlanggotapms = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '1')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%Q50%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q56%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
            ->first();
        // END ANGGOTA

        // START pns 
        $sqlpnspms = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '2')
                    ->orwhere('b.golongan_polri', '=', '7')
                    ->orwhere('b.golongan_polri', '=', '8')
                    ->orwhere('b.golongan_polri', '=', '10');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%Q50%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q56%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
            ->first();
        // END pns

        // START Dikbang
        $sqldikbangpms = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '4')
                    ->orwhere('b.golongan_polri', '=', '6');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%Q50%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q56%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
            ->first();
        // END Dikbang

        // START Diktuk
        $sqldiktukpms = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '5')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%Q50%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q56%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
            ->first();
        // END Diktuk

        // START Kel polri
        $sqlkelpolripms = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '9')
                    ->orwhere('b.golongan_polri', '=', '3');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%Q50%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q56%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
            ->first();
        // END Kel polri

        // START Umum
        $sqlumupms = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'UMU')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%Q50%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q56%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
            ->first();
        // END Umum

        //start total bpjs khusus
        $total_khusus_pms =
            $sqlanggotapms->pms +
            $sqlpnspms->pms +
            $sqldikbangpms->pms  +
            $sqldiktukpms->pms  +
            $sqlkelpolripms->pms;
        //end total bpjs khusus

        // START bpjs
        $sqlbpjspms = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%Q50%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q56%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
            ->first();
        $total_bpjspms = $sqlbpjspms->pms - $total_khusus_pms;
        // END bpjs
        // START lainnya
        $sqllainnyapms = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '!=', 'UMU')
            ->where('a.kd_pj', '!=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%Q50%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q51%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q52%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q53%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q54%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q55%')
                    ->orWhere('c.kd_penyakit', 'like', '%Q56%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as pms'))
            ->first();
        // END lainnya
        $total_pms = $total_khusus_pms + $total_bpjspms + $sqllainnyapms->pms + $sqlumupms->pms;
        // END pms

        // START hepatitis
        // START ANGGOTA 
        $sqlanggotahepatitis = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '1')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B15%')
                    ->orWhere('c.kd_penyakit', 'like', '%B16%')
                    ->orWhere('c.kd_penyakit', 'like', '%B17%')
                    ->orWhere('c.kd_penyakit', 'like', '%B18%')
                    ->orWhere('c.kd_penyakit', 'like', '%B19%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
            ->first();
        // END ANGGOTA

        // START pns 
        $sqlpnshepatitis = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '2')
                    ->orwhere('b.golongan_polri', '=', '7')
                    ->orwhere('b.golongan_polri', '=', '8')
                    ->orwhere('b.golongan_polri', '=', '10');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B15%')
                    ->orWhere('c.kd_penyakit', 'like', '%B16%')
                    ->orWhere('c.kd_penyakit', 'like', '%B17%')
                    ->orWhere('c.kd_penyakit', 'like', '%B18%')
                    ->orWhere('c.kd_penyakit', 'like', '%B19%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
            ->first();
        // END pns

        // START Dikbang
        $sqldikbanghepatitis = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '4')
                    ->orwhere('b.golongan_polri', '=', '6');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B15%')
                    ->orWhere('c.kd_penyakit', 'like', '%B16%')
                    ->orWhere('c.kd_penyakit', 'like', '%B17%')
                    ->orWhere('c.kd_penyakit', 'like', '%B18%')
                    ->orWhere('c.kd_penyakit', 'like', '%B19%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
            ->first();
        // END Dikbang

        // START Diktuk
        $sqldiktukhepatitis = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '5')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B15%')
                    ->orWhere('c.kd_penyakit', 'like', '%B16%')
                    ->orWhere('c.kd_penyakit', 'like', '%B17%')
                    ->orWhere('c.kd_penyakit', 'like', '%B18%')
                    ->orWhere('c.kd_penyakit', 'like', '%B19%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
            ->first();
        // END Diktuk

        // START Kel polri
        $sqlkelpolrihepatitis = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '3')
                    ->orwhere('b.golongan_polri', '=', '9');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B15%')
                    ->orWhere('c.kd_penyakit', 'like', '%B16%')
                    ->orWhere('c.kd_penyakit', 'like', '%B17%')
                    ->orWhere('c.kd_penyakit', 'like', '%B18%')
                    ->orWhere('c.kd_penyakit', 'like', '%B19%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
            ->first();
        // END Kel polri

        // START Umum
        $sqlumuhepatitis = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'UMU')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B15%')
                    ->orWhere('c.kd_penyakit', 'like', '%B16%')
                    ->orWhere('c.kd_penyakit', 'like', '%B17%')
                    ->orWhere('c.kd_penyakit', 'like', '%B18%')
                    ->orWhere('c.kd_penyakit', 'like', '%B19%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
            ->first();
        // END Umum

        //start total bpjs khusus
        $total_khusus_hepatitis =
            $sqlanggotahepatitis->hepatitis +
            $sqlpnshepatitis->hepatitis +
            $sqldikbanghepatitis->hepatitis  +
            $sqldiktukhepatitis->hepatitis  +
            $sqlkelpolrihepatitis->hepatitis;
        //end total bpjs khusus

        // START bpjs
        $sqlbpjshepatitis = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B15%')
                    ->orWhere('c.kd_penyakit', 'like', '%B16%')
                    ->orWhere('c.kd_penyakit', 'like', '%B17%')
                    ->orWhere('c.kd_penyakit', 'like', '%B18%')
                    ->orWhere('c.kd_penyakit', 'like', '%B19%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
            ->first();
        $total_bpjshepatitis = $sqlbpjshepatitis->hepatitis - $total_khusus_hepatitis;
        // END bpjs
        // START lainnya
        $sqllainnyahepatitis = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '!=', 'UMU')
            ->where('a.kd_pj', '!=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B15%')
                    ->orWhere('c.kd_penyakit', 'like', '%B16%')
                    ->orWhere('c.kd_penyakit', 'like', '%B17%')
                    ->orWhere('c.kd_penyakit', 'like', '%B18%')
                    ->orWhere('c.kd_penyakit', 'like', '%B19%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as hepatitis'))
            ->first();
        // END lainnya
        $total_hepatitis = $total_khusus_hepatitis + $total_bpjshepatitis + $sqllainnyahepatitis->hepatitis + $sqlumuhepatitis->hepatitis;
        // END hepatitis

        // START covid
        // START ANGGOTA 
        $sqlanggotacovid = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '1')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B34.2%')
                    ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
            ->first();
        // END ANGGOTA

        // START pns 
        $sqlpnscovid = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '2')
                    ->orwhere('b.golongan_polri', '=', '7')
                    ->orwhere('b.golongan_polri', '=', '8')
                    ->orwhere('b.golongan_polri', '=', '10');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B34.2%')
                    ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
            ->first();
        // END pns

        // START Dikbang
        $sqldikbangcovid = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '4')
                    ->orwhere('b.golongan_polri', '=', '6');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B34.2%')
                    ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
            ->first();
        // END Dikbang

        // START Diktuk
        $sqldiktukcovid = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where('b.golongan_polri', '=', '5')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B34.2%')
                    ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
            ->first();
        // END Diktuk

        // START Kel polri
        $sqlkelpolricovid = DB::table('reg_periksa as a')
            ->join('pasien_polri as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('b.golongan_polri', '=', '9')
                    ->orwhere('b.golongan_polri', '=', '3');
            })
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B34.2%')
                    ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
            ->first();
        // END Kel polri

        // START Umum
        $sqlumucovid = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'UMU')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B34.2%')
                    ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
            ->first();
        // END Umum

        //start total bpjs khusus
        $total_khusus_covid =
            $sqlanggotacovid->covid +
            $sqlpnscovid->covid +
            $sqldikbangcovid->covid  +
            $sqldiktukcovid->covid  +
            $sqlkelpolricovid->covid;
        //end total bpjs khusus

        // START bpjs
        $sqlbpjscovid = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B34.2%')
                    ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
            ->first();
        $total_bpjscovid = $sqlbpjscovid->covid - $total_khusus_covid;
        // END bpjs
        // START lainnya
        $sqllainnyacovid = DB::table('reg_periksa as a')
            ->join('diagnosa_pasien as c', 'c.no_rawat', '=', 'a.no_rawat')
            ->where('a.kd_pj', '!=', 'UMU')
            ->where('a.kd_pj', '!=', 'BPJ')
            ->where(function ($query) {
                $query->where('c.kd_penyakit', 'like', '%B34.2%')
                    ->orWhere('c.kd_penyakit', 'like', '%B97.2%');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('COUNT(DISTINCT a.no_rawat) as covid'))
            ->first();
        // END lainnya
        $total_covid = $total_khusus_covid + $total_bpjscovid + $sqllainnyacovid->covid + $sqlumucovid->covid;
        // END covid

        return view('rm.laporan_rm.penyakit_menular', [

            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,

            'anggotahiv' => $sqlanggotahiv,
            'pnshiv' => $sqlpnshiv,
            'dikbanghiv' => $sqldikbanghiv,
            'diktukhiv' => $sqldiktukhiv,
            'kelpolrihiv' => $sqlkelpolrihiv,
            'umumhiv' => $sqlumuhiv,
            'bpjshiv' => $total_bpjshiv,
            'otherhiv' => $sqllainnyahiv,
            'total_hiv' => $total_hiv,

            'anggotatb' => $sqlanggotatb,
            'pnstb' => $sqlpnstb,
            'dikbangtb' => $sqldikbangtb,
            'diktuktb' => $sqldiktuktb,
            'kelpolritb' => $sqlkelpolritb,
            'umumtb' => $sqlumutb,
            'bpjstb' => $total_bpjstb,
            'othertb' => $sqllainnyatb,
            'total_tb' => $total_tb,

            'anggotamalaria' => $sqlanggotamalaria,
            'pnsmalaria' => $sqlpnsmalaria,
            'dikbangmalaria' => $sqldikbangmalaria,
            'diktukmalaria' => $sqldiktukmalaria,
            'kelpolrimalaria' => $sqlkelpolrimalaria,
            'umummalaria' => $sqlumumalaria,
            'bpjsmalaria' => $total_bpjsmalaria,
            'othermalaria' => $sqllainnyamalaria,
            'total_malaria' => $total_malaria,

            'anggotadbd' => $sqlanggotadbd,
            'pnsdbd' => $sqlpnsdbd,
            'dikbangdbd' => $sqldikbangdbd,
            'diktukdbd' => $sqldiktukdbd,
            'kelpolridbd' => $sqlkelpolridbd,
            'umumdbd' => $sqlumudbd,
            'bpjsdbd' => $total_bpjsdbd,
            'otherdbd' => $sqllainnyadbd,
            'total_dbd' => $total_dbd,

            'anggotapms' => $sqlanggotapms,
            'pnspms' => $sqlpnspms,
            'dikbangpms' => $sqldikbangpms,
            'diktukpms' => $sqldiktukpms,
            'kelpolripms' => $sqlkelpolripms,
            'umumpms' => $sqlumupms,
            'bpjspms' => $total_bpjspms,
            'otherpms' => $sqllainnyapms,
            'total_pms' => $total_pms,

            'anggotahepatitis' => $sqlanggotahepatitis,
            'pnshepatitis' => $sqlpnshepatitis,
            'dikbanghepatitis' => $sqldikbanghepatitis,
            'diktukhepatitis' => $sqldiktukhepatitis,
            'kelpolrihepatitis' => $sqlkelpolrihepatitis,
            'umumhepatitis' => $sqlumuhepatitis,
            'bpjshepatitis' => $total_bpjshepatitis,
            'otherhepatitis' => $sqllainnyahepatitis,
            'total_hepatitis' => $total_hepatitis,

            'anggotacovid' => $sqlanggotacovid,
            'pnscovid' => $sqlpnscovid,
            'dikbangcovid' => $sqldikbangcovid,
            'diktukcovid' => $sqldiktukcovid,
            'kelpolricovid' => $sqlkelpolricovid,
            'umumcovid' => $sqlumucovid,
            'bpjscovid' => $total_bpjscovid,
            'othercovid' => $sqllainnyacovid,
            'total_covid' => $total_covid,
        ]);
    }

    public function igd(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Check if $tgl1 is empty, if so, set it to the first day of the current month
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }
        // Check if $tgl2 is empty, if so, set it to today's date
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }
        // Format the dates
        if (!empty($tgl1Input) && !empty($tgl2Input)) {
            $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal

        // Start macam kasus Igd
        $sqligd = DB::table('reg_periksa as a')
            ->join('data_triase_igd as b', 'b.no_rawat', '=', 'a.no_rawat')
            ->join('master_triase_macam_kasus as c', 'c.kode_kasus', '=', 'b.kode_kasus')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->groupBy('c.macam_kasus') // Menambahkan klausa groupBy
            ->select('c.macam_kasus as kasus', DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->get();
        // End macam kasus Igd

        return view('rm.laporan_rm.laporan_igd', [

            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,
            'igd' => $sqligd,
        ]);
    }

    public function operasi(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Check if $tgl1 is empty, if so, set it to the first day of the current month
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }
        // Check if $tgl2 is empty, if so, set it to today's date
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }
        // Format the dates
        if (!empty($tgl1Input) && !empty($tgl2Input)) {
            $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal

        // Start macam jenis operasi
        $sqlop = DB::table('reg_periksa as a')
            ->join('kamar_inap as b', 'b.no_rawat', '=', 'a.no_rawat')
            ->join('booking_operasi as c', 'c.no_rawat', '=', 'b.no_rawat')
            ->join('paket_operasi as d', 'd.kode_paket', '=', 'c.kode_paket')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2])->whereBetween('c.tanggal', [$tgl1, $tgl2]);
            })
            ->whereIn('c.status', ['Proses Operasi', 'Selesai'])
            ->groupBy('d.nm_perawatan') // Menambahkan klausa groupBy
            ->select('d.nm_perawatan as jenis_op', DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->get();
        // End macam jenis operasi

        return view('rm.laporan_rm.kegiatan_operasi', [

            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,
            'op' => $sqlop,
        ]);
    }

    public function kematian(Request $request)
    {
        //format tanggal
                // Get input values
                $tgl1Input = $request->input('tgl1');
                $tgl2Input = $request->input('tgl2');

                // Check if $tgl1 is empty, if so, set it to the first day of the current month
                if (empty($tgl1Input)) {
                    $tgl1 = new \DateTime(date('Y-m-01'));
                } else {
                    $tgl1 = new \DateTime($tgl1Input);
                }
                // Check if $tgl2 is empty, if so, set it to today's date
                if (empty($tgl2Input)) {
                    $tgl2 = new \DateTime();
                } else {
                    $tgl2 = new \DateTime($tgl2Input);
                }
                // Format the dates
                if (!empty($tgl1Input) && !empty($tgl2Input)) {
                    $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
                } else {
                    $startDate = new \DateTime('first day of this month');
                    $endDate = new \DateTime('today');
                    $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
                }

                $formattedTgl1 = $tgl1->format('Y-m-d');
                $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal
        
        // Start Pasien Meninggal Anggota
            $meninggal_anggota = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts = "Meninggal"
                AND pasien_polri.golongan_polri = "1"
                AND kd_pj = "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts_pulang = "Meninggal"
                AND pasien_polri.golongan_polri = "1"
                AND kd_pj = "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal Anggota

        // Start Pasien Meninggal PNS
            $meninggal_pns = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts = "Meninggal"
                AND (pasien_polri.golongan_polri = "2" OR pasien_polri.golongan_polri = "7" OR pasien_polri.golongan_polri = "8" OR pasien_polri.golongan_polri = "10")
                AND kd_pj = "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts_pulang = "Meninggal"
                AND (pasien_polri.golongan_polri = "2" OR pasien_polri.golongan_polri = "7" OR pasien_polri.golongan_polri = "8" OR pasien_polri.golongan_polri = "10")
                AND kd_pj = "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal pns

        // Start Pasien Meninggal keluarga
            $meninggal_keluarga = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts = "Meninggal"
                AND (pasien_polri.golongan_polri = "3" OR pasien_polri.golongan_polri = "9" )
                AND kd_pj = "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts_pulang = "Meninggal"
                AND (pasien_polri.golongan_polri = "3" OR pasien_polri.golongan_polri = "9" )
                AND kd_pj = "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal keluarga

        // Start Pasien Meninggal Dikbang
            $meninggal_dikbang = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts = "Meninggal"
                AND (pasien_polri.golongan_polri = "4" OR pasien_polri.golongan_polri = "6" )
                AND kd_pj = "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts_pulang = "Meninggal"
                AND (pasien_polri.golongan_polri = "4" OR pasien_polri.golongan_polri = "6" )
                AND kd_pj = "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal Dikbang

        // Start Pasien Meninggal diktuk
            $meninggal_diktuk = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts = "Meninggal"
                AND pasien_polri.golongan_polri = "5" 
                AND kd_pj = "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                JOIN pasien_polri on pasien_polri.no_rkm_medis=reg_periksa.no_rkm_medis
                where stts_pulang = "Meninggal"
                AND pasien_polri.golongan_polri = "5"
                AND kd_pj = "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal diktuk

        // Start Pasien Meninggal umum
            $meninggal_umum = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                where stts = "Meninggal"
                AND kd_pj = "PJ2"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                where stts_pulang = "Meninggal"
                AND kd_pj = "PJ2"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal umum
        
        // START Total BPJS KHUSUS
            $total_bpjs_khusus= $meninggal_anggota->total+$meninggal_pns->total+$meninggal_keluarga->total+$meninggal_dikbang->total+$meninggal_diktuk->total;
        // END Total BPJS KHUSUS
        // Start Pasien Meninggal bpjs
            $meninggal_bpjs = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                where stts = "Meninggal"
                AND kd_pj = "BPJ"

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                where stts_pulang = "Meninggal"
                AND kd_pj = "BPJ"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
            $total_bpjs = $meninggal_bpjs->total; 
        // End Pasien Meninggal bpjs

                    // ditambahkan oleh the ihsan -- START --

         // Start Pasien Meninggal bpjs
            $meninggal_ranap = DB::table('kamar_inap')
            ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
            ->where('kamar_inap.stts_pulang', 'Meninggal')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select([
                DB::raw('COUNT(DISTINCT kamar_inap.no_rawat) as total2')
            ])
            ->first();

        // End Pasien Meninggal bpjs

        // Start Pasien Meninggal igd
            $meninggal_igd = DB::table('reg_periksa')
            ->where('reg_periksa.stts', 'Meninggal')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('kamar_inap')
                    ->whereRaw('kamar_inap.no_rawat = reg_periksa.no_rawat');
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select([
                DB::raw('COUNT(DISTINCT reg_periksa.no_rawat) as total2')
            ])
        ->first();
        // End Pasien Meninggal igd
        $total_meninggal2 = $meninggal_igd->total2 +  $meninggal_ranap->total2 ;

                    // ditambahkan oleh ihsan -- END --
                    
        // Start Pasien Meninggal lainnya
            $meninggal_lainnya = DB::table(DB::raw('(
                SELECT no_rawat,tgl_registrasi
                FROM reg_periksa 
                where stts = "Meninggal"
                AND kd_pj != "BPJ"
                AND kd_pj != "PJ2"
                

                UNION

                SELECT reg_periksa.no_rawat,reg_periksa.tgl_registrasi
                FROM kamar_inap Join reg_periksa on reg_periksa.no_rawat=kamar_inap.no_rawat
                where stts_pulang = "Meninggal"
                AND kd_pj != "BPJ"
                AND kd_pj != "PJ2"
            ) as r'))
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2]);
                })
                ->select([
                    DB::raw('COUNT(DISTINCT r.no_rawat) as total')
                ])
                ->first();
        // End Pasien Meninggal lainnya
            $total_meninggal = $total_bpjs_khusus + $meninggal_umum->total + $total_bpjs + $meninggal_lainnya->total ;
            
            
        return view('rm.laporan_rm.laporan_kematian',[ 
        
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,

            'anggota' => $meninggal_anggota,
            'pns' => $meninggal_pns,
            'keluarga' => $meninggal_keluarga,
            'dikbang' => $meninggal_dikbang,
            'diktuk' => $meninggal_diktuk,
            'umum' => $meninggal_umum,
            'bpjs' => $total_bpjs,
            'ranap' => $meninggal_ranap,
            'igd' => $meninggal_igd,
            'lainnya' => $meninggal_lainnya,
            'total' => $total_meninggal,
            'total2' => $total_meninggal2,
        ]);
    }

    public function pertumbuhan(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Check if $tgl1 is empty, if so, set it to the first day of the current month
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));

            // Modifikasi tanggal untuk mundur satu bulan
            $tglSebelum = clone $tgl1; // Salin objek untuk tanggal sebelumnya
            $tglSebelum->modify('-1 month');

            // Format tanggal sebagai 'Y-m-01' untuk mendapatkan awal bulan sebulan sebelumnya
            $tglAwalSebelum = clone $tglSebelum; // Salin objek untuk tanggal awal bulan sebelumnya
            $tglAwalSebelum->modify('first day of this month');

            // Format tanggal sebagai 'Y-m-t' untuk mendapatkan akhir bulan sebulan sebelumnya
            $tglAkhirSebelum = clone $tglSebelum; // Salin objek untuk tanggal akhir bulan sebelumnya
            $tglAkhirSebelum->modify('last day of this month');

            // Format tanggal sebelumnya sebagai 'd F Y'
            $formattedTglAwalSebelum = $tglAwalSebelum->format('d F Y');
            $formattedTglAkhirSebelum = $tglAkhirSebelum->format('d F Y');
        } else {
            $tgl1 = new \DateTime($tgl1Input);

            // Modifikasi tanggal untuk mundur satu bulan
            $tglSebelum = clone $tgl1; // Salin objek untuk tanggal sebelumnya
            $tglSebelum->modify('-1 month');

            // Format tanggal sebagai 'Y-m-01' untuk mendapatkan awal bulan sebulan sebelumnya
            $tglAwalSebelum = clone $tglSebelum; // Salin objek untuk tanggal awal bulan sebelumnya
            $tglAwalSebelum->modify('first day of this month');

            // Format tanggal sebagai 'Y-m-t' untuk mendapatkan akhir bulan sebulan sebelumnya
            $tglAkhirSebelum = clone $tglSebelum; // Salin objek untuk tanggal akhir bulan sebelumnya
            $tglAkhirSebelum->modify('last day of this month');

            // Format tanggal sebelumnya sebagai 'd F Y'
            $formattedTglAwalSebelum = $tglAwalSebelum->format('d F Y');
            $formattedTglAkhirSebelum = $tglAkhirSebelum->format('d F Y');
        }
        // Check if $tgl2 is empty, if so, set it to today's date
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }
        // Format the dates
        if (!empty($tgl1Input) && !empty($tgl2Input)) {
            $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal

        // Start Rawat Jalan
        $sqlrajalnow = DB::table('reg_periksa as a')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->where('a.stts', '!=', 'Batal')
            ->where('a.kd_poli', '!=', 'IGDK')
            // ->where('a.kd_poli', '!=', 'IRM')
            ->where('a.kd_poli', '!=', 'RAD')
            ->where('a.kd_poli', '!=', 'LAB')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->orderBy('total', 'desc')
            ->first();

        $sqlrajalsebelum = DB::table('reg_periksa as a')
            ->where('a.status_lanjut', '=', 'Ralan')
            ->where('a.stts', '!=', 'Batal')
            ->where('a.kd_poli', '!=', 'IGDK')
            // ->where('a.kd_poli', '!=', 'IRM')
            ->where('a.kd_poli', '!=', 'RAD')
            ->where('a.kd_poli', '!=', 'LAB')
            ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
                return $query->whereBetween('a.tgl_registrasi', [$tglAwalSebelum, $tglAkhirSebelum]);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->orderBy('total', 'desc')
            ->first();

        $pertumbuhan_ralan = number_format((($sqlrajalnow->total - $sqlrajalsebelum->total) / $sqlrajalsebelum->total) * 100, 2);
        // End Rawat Jalan

        // Start Rawat Inap
        $sqlranapnow = DB::table('reg_periksa as a')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->orderBy('total', 'desc')
            ->first();

        $sqlranapsebelum = DB::table('reg_periksa as a')
            ->where('a.status_lanjut', '=', 'Ranap')
            ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
                return $query->whereBetween('a.tgl_registrasi', [$tglAwalSebelum, $tglAkhirSebelum]);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->orderBy('total', 'desc')
            ->first();

        $pertumbuhan_ranap = number_format((($sqlranapnow->total - $sqlranapsebelum->total) / $sqlranapsebelum->total) * 100, 2);
        // End Rawat Inap

        // Start IGD
        $sqligdnow = DB::table('reg_periksa as a')
            ->where('a.stts', '!=', 'Batal')
            ->where('a.kd_poli', '=', 'IGDK')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->orderBy('total', 'desc')
            ->first();

        $sqligdsebelum = DB::table('reg_periksa as a')
            ->where('a.stts', '!=', 'Batal')
            ->where('a.kd_poli', '=', 'IGDK')
            ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
                return $query->whereBetween('a.tgl_registrasi', [$tglAwalSebelum, $tglAkhirSebelum]);
            })
            ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
            ->orderBy('total', 'desc')
            ->first();

        $pertumbuhan_igd = number_format((($sqligdnow->total - $sqligdsebelum->total) / $sqligdsebelum->total) * 100, 2);
        // End IGD

        // Start IRM
        // $sqlirmnow = DB::table('reg_periksa as a')
        // ->where('a.status_lanjut', '=', 'Ralan')
        // ->where('a.stts', '!=', 'Batal')
        // ->where('a.kd_poli', '=', 'IRM')
        // ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
        //     return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
        // })
        // ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
        // ->orderBy('total', 'desc')
        // ->first();

        // $sqlirmsebelum = DB::table('reg_periksa as a')
        // ->where('a.status_lanjut', '=', 'Ralan')
        // ->where('a.stts', '!=', 'Batal')
        // ->where('a.kd_poli', '=', 'IRM')
        // ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
        //     return $query->whereBetween('a.tgl_registrasi', [$tglAwalSebelum, $tglAkhirSebelum]);
        // })
        // ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
        // ->orderBy('total', 'desc')
        // ->first();

        // $pertumbuhan_irm = number_format((($sqlirmnow->total-$sqlirmsebelum->total ) / $sqlirmsebelum->total) * 100 , 2);
        // End IRM

        // Start Lab
        $sqllabnow = DB::table('periksa_lab as a')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_periksa', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('count(DISTINCT CONCAT(a.no_rawat, a.tgl_periksa)) as total'))
            ->first();

        $sqllabsebelum = DB::table('periksa_lab as a')
            ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
                return $query->whereBetween('a.tgl_periksa', [$tglAwalSebelum, $tglAkhirSebelum]);
            })
            ->select(DB::raw('count(DISTINCT CONCAT(a.no_rawat, a.tgl_periksa)) as total'))
            ->first();

        $pertumbuhan_lab = number_format((($sqllabnow->total - $sqllabsebelum->total) / $sqllabsebelum->total) * 100, 2);
        // End Lab

        // Start rad
        $sqlradnow = DB::table('periksa_radiologi as a')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_periksa', [$tgl1, $tgl2]);
            })
            ->select(DB::raw('count(DISTINCT CONCAT(a.no_rawat, a.tgl_periksa)) as total'))
            ->first();

        $sqlradsebelum = DB::table('periksa_radiologi as a')
            ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
                return $query->whereBetween('a.tgl_periksa', [$tglAwalSebelum, $tglAkhirSebelum]);
            })
            ->select(DB::raw('count(DISTINCT CONCAT(a.no_rawat, a.tgl_periksa)) as total'))
            ->first();

        $pertumbuhan_rad = number_format((($sqlradnow->total - $sqlradsebelum->total) / $sqlradsebelum->total) * 100, 2);
        // End rad

        // Start operasi
        // $sqloperasinow = DB::table('operasi as a')
        // ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
        //     return $query->whereBetween('a.tgl_operasi', [$tgl1, $tgl2]);
        // })
        // ->select(DB::raw('count(DISTINCT CONCAT(a.no_rawat, a.tgl_operasi)) as total'))
        // ->first();

        // $sqloperasisebelum = DB::table('operasi as a')
        // ->when($tglAwalSebelum && $tglAkhirSebelum, function ($query) use ($tglAwalSebelum, $tglAkhirSebelum) {
        //     return $query->whereBetween('a.tgl_operasi', [$tglAwalSebelum, $tglAkhirSebelum]);
        // })
        // ->select(DB::raw('count(DISTINCT CONCAT(a.no_rawat, a.tgl_operasi)) as total'))
        // ->first();

        // $pertumbuhan_operasi = number_format((($sqloperasinow->total - $sqloperasisebelum->total) / $sqloperasisebelum->total) * 100, 2);
        // End operasi

        return view('rm.laporan_rm.pertumbuhan', [

            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,
            'dari' => $formattedTglAwalSebelum,
            'sampai' => $formattedTglAkhirSebelum,

            'sqlrajal' => $sqlrajalnow,
            'pertumbuhan_ralan' => $pertumbuhan_ralan,

            'sqlranap' => $sqlranapnow,
            'pertumbuhan_ranap' => $pertumbuhan_ranap,

            'sqligd' => $sqligdnow,
            'pertumbuhan_igd' => $pertumbuhan_igd,

            // 'sqlirm'=>$sqlirmnow,
            // 'pertumbuhan_irm'=>$pertumbuhan_irm,

            'sqllab' => $sqllabnow,
            'pertumbuhan_lab' => $pertumbuhan_lab,

            'sqlrad' => $sqlradnow,
            'pertumbuhan_rad' => $pertumbuhan_rad,

            // 'sqloperasi'=>$sqloperasinow,
            // 'pertumbuhan_operasi'=>$pertumbuhan_operasi,



        ]);
    }
    public function laporan_radlab(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Check if $tgl1 is empty, if so, set it to the first day of the current month
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }
        // Check if $tgl2 is empty, if so, set it to today's date
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }
        // Format the dates
        if (!empty($tgl1Input) && !empty($tgl2Input)) {
            $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal

        // Start permintaan_radiologi
        $sqlTotalRadiologi = DB::table('permintaan_radiologi')
            ->where('tgl_hasil', '<>', '0000-00-00')
            ->whereBetween('tgl_hasil', [$tgl1, $tgl2])
            ->count();

        // Start permintaan_lab
        $sqlTotalLab = DB::table('permintaan_lab')
            ->where('tgl_hasil', '<>', '0000-00-00')
            ->whereBetween('tgl_hasil', [$tgl1, $tgl2])
            ->count();

        return view('rm.laporan_rm.laporan_radlab', [
            'tgl1' => $tgl1->format('Y-m-d'),
            'tgl2' => $tgl2->format('Y-m-d'),
            'tgllap' => $tanggal,
            'totalRadiologi' => $sqlTotalRadiologi,
            'totalLab' => $sqlTotalLab
        ]);
    }


    public function totalresep(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Check if $tgl1 is empty, if so, set it to the first day of the current month
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }
        // Check if $tgl2 is empty, if so, set it to today's date
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }
        // Format the dates
        if (!empty($tgl1Input) && !empty($tgl2Input)) {
            $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal

        //Start Total Resep BPJS
        $jumlah_resep_bpjs = DB::table('reg_periksa as r')
        ->join('resep_obat as ro', 'r.no_rawat', '=', 'ro.no_rawat')
        ->where('r.kd_pj', 'BPJ')
        ->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2])
        ->count('ro.no_resep'); // Hanya menghitung jumlah resep

        //Start Total Resep UMUM
        $jumlah_resep_umum = DB::table('reg_periksa as r')
        ->join('resep_obat as ro', 'r.no_rawat', '=', 'ro.no_rawat')
        ->where('r.kd_pj', 'PJ2')
        ->whereBetween('r.tgl_registrasi', [$tgl1, $tgl2])
        ->count('ro.no_resep'); // Hanya menghitung jumlah resep

        $total_resep = $jumlah_resep_bpjs + $jumlah_resep_umum;

        return view('rm.laporan_farmasi.total_resep', [

            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,
        
            'tgllap' => $tanggal,
        
            'jumlah_resep_bpjs' => $jumlah_resep_bpjs, // Data jumlah resep yang diambil dari query
            'jumlah_resep_umum' => $jumlah_resep_umum, // Data jumlah resep yang diambil dari query
            'total_resep' => $total_resep
        ]);        
    }

    public function detailresep(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Check if $tgl1 is empty, if so, set it to the first day of the current month
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }
        // Check if $tgl2 is empty, if so, set it to today's date
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }
        // Format the dates
        if (!empty($tgl1Input) && !empty($tgl2Input)) {
            $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal
        
        // Query untuk mendapatkan no_resep berdasarkan tgl_registrasi
        $detail_resep = DB::table('resep_obat as ro')
            ->join('resep_dokter as rd', 'ro.no_resep', '=', 'rd.no_resep')
            ->join('reg_periksa as rp', 'ro.no_rawat', '=', 'rp.no_rawat')
            ->whereBetween('rp.tgl_registrasi', [$formattedTgl1, $formattedTgl2])
            ->select('rd.no_resep')
            
            ->union(
                DB::table('resep_obat as ro')
                    ->join('resep_dokter_racikan_detail as rrd', 'ro.no_resep', '=', 'rrd.no_resep')
                    ->join('reg_periksa as rp', 'ro.no_rawat', '=', 'rp.no_rawat')
                    ->whereBetween('rp.tgl_registrasi', [$formattedTgl1, $formattedTgl2])
                    ->select('rrd.no_resep')
            )
            ->orderBy('no_resep', 'desc')
            ->get();

        return view('rm.laporan_farmasi.detail_resep', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,
            'tgllap' => $tanggal,
            'detail_resep' => $detail_resep,
                ]);
    }

    //detail resep
    public function getModalResep(Request $request)
    {
        // Ambil data berdasarkan ID
        $id = $request->query('id'); 
        
        // Jalankan query untuk mendapatkan no_resep dan daftar nama barang
        $data = DB::table(DB::raw("(
            SELECT no_resep, kode_brng FROM resep_dokter
            UNION ALL
            SELECT no_resep, kode_brng FROM resep_dokter_racikan_detail
        ) as r"))
        ->join('databarang as d', 'r.kode_brng', '=', 'd.kode_brng')
        ->where('r.no_resep', '=', $id)
        ->select('r.no_resep', DB::raw("GROUP_CONCAT(d.nama_brng ORDER BY d.kode_brng ASC SEPARATOR ', ') AS daftar_nama_brng"))
        ->groupBy('r.no_resep')
        ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        return view('rm.laporan_farmasi.modal_resep', [
            'data' => $data,
        ]);
    }

    // by ihsan
    public function ibudanbayi(Request $request)
    {

        //format tanggal
                // Get input values
                $tgl1Input = $request->input('tgl1');
                $tgl2Input = $request->input('tgl2');

                // Check if $tgl1 is empty, if so, set it to the first day of the current month
                if (empty($tgl1Input)) {
                    $tgl1 = new \DateTime(date('Y-m-01'));

                    // Modifikasi tanggal untuk mundur satu bulan
                    $tglSebelum = clone $tgl1; // Salin objek untuk tanggal sebelumnya
                    $tglSebelum->modify('-1 month');

                    // Format tanggal sebagai 'Y-m-01' untuk mendapatkan awal bulan sebulan sebelumnya
                    $tglAwalSebelum = clone $tglSebelum; // Salin objek untuk tanggal awal bulan sebelumnya
                    $tglAwalSebelum->modify('first day of this month');

                    // Format tanggal sebagai 'Y-m-t' untuk mendapatkan akhir bulan sebulan sebelumnya
                    $tglAkhirSebelum = clone $tglSebelum; // Salin objek untuk tanggal akhir bulan sebelumnya
                    $tglAkhirSebelum->modify('last day of this month');

                    // Format tanggal sebelumnya sebagai 'd F Y'
                    $formattedTglAwalSebelum = $tglAwalSebelum->format('d F Y');
                    $formattedTglAkhirSebelum = $tglAkhirSebelum->format('d F Y');

                } else {
                    $tgl1 = new \DateTime($tgl1Input);

                    // Modifikasi tanggal untuk mundur satu bulan
                    $tglSebelum = clone $tgl1; // Salin objek untuk tanggal sebelumnya
                    $tglSebelum->modify('-1 month');

                    // Format tanggal sebagai 'Y-m-01' untuk mendapatkan awal bulan sebulan sebelumnya
                    $tglAwalSebelum = clone $tglSebelum; // Salin objek untuk tanggal awal bulan sebelumnya
                    $tglAwalSebelum->modify('first day of this month');

                    // Format tanggal sebagai 'Y-m-t' untuk mendapatkan akhir bulan sebulan sebelumnya
                    $tglAkhirSebelum = clone $tglSebelum; // Salin objek untuk tanggal akhir bulan sebelumnya
                    $tglAkhirSebelum->modify('last day of this month');

                    // Format tanggal sebelumnya sebagai 'd F Y'
                    $formattedTglAwalSebelum = $tglAwalSebelum->format('d F Y');
                    $formattedTglAkhirSebelum = $tglAkhirSebelum->format('d F Y');

                }
                // Check if $tgl2 is empty, if so, set it to today's date
                if (empty($tgl2Input)) {
                    $tgl2 = new \DateTime();
                } else {
                    $tgl2 = new \DateTime($tgl2Input);
                }
                // Format the dates
                if (!empty($tgl1Input) && !empty($tgl2Input)) {
                    $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
                } else {
                    $startDate = new \DateTime('first day of this month');
                    $endDate = new \DateTime('today');
                    $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
                }

                $formattedTgl1 = $tgl1->format('Y-m-d');
                $formattedTgl2 = $tgl2->format('Y-m-d');
        //end format tanggal

                // Start Bayi Lahir hidup
                $bayi_lahir= DB::table(DB::raw('bayi_kelahiran'))
                    ->join('reg_periksa', 'bayi_kelahiran.no_rawat', '=', 'reg_periksa.no_rawat') // Join dengan reg_periksa
                    ->where('bayi_kelahiran.kondisi_janin', 'livebirth') // Filter hanya yang lahir hidup
                    ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                        return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]); // Gunakan tgl_registrasi
                    })
                    ->select([
                        DB::raw('COUNT(DISTINCT bayi_kelahiran.no_rawat) as total')
                    ])
                ->first();
                // End Bayi Lahir hidup

                // Start Bayi Lahir tidak hidup
                $bayi_mati = DB::table('bayi_kelahiran')
                ->join('reg_periksa', 'bayi_kelahiran.no_rawat', '=', 'reg_periksa.no_rawat') // Join dengan reg_periksa
                ->where('bayi_kelahiran.kondisi_janin', '!=', 'livebirth') // Filter bayi yang tidak "livebirth"
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]); // Gunakan tgl_registrasi
                })
                ->select([
                    DB::raw('COUNT(DISTINCT bayi_kelahiran.no_rawat) as total')
                ])
                ->first();
                // End Bayi Lahir tidak hidup
                

                // Start Bayi mati ranap
                $bayi_matiranap = DB::table('kamar_inap as a')
                ->join('reg_periksa as r', 'r.no_rawat', '=', 'a.no_rawat')
                ->join('pasien as p', 'p.no_rkm_medis', '=', 'r.no_rkm_medis')
                ->whereRaw('DATEDIFF(CURDATE(), p.tgl_lahir) < 1900') // Hanya pasien bayi
                ->where('a.stts_pulang', '=', 'Meninggal')
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('a.tgl_keluar', [$tgl1, $tgl2]);
                })
                ->select(DB::raw('count(DISTINCT a.no_rawat) as total'))
                ->first();
                // End Bayi mati ranap
                $total_lahirmati = ($bayi_lahir->total ?? 0) + ($bayi_matiranap->total ?? 0) + ($bayi_mati->total ?? 0);

                // Start Bayi Lahir >= 2,5 KG
                $bayi_lahir_2setkilolebih = DB::table('bayi_kelahiran')
                ->join('reg_periksa', 'bayi_kelahiran.no_rawat', '=', 'reg_periksa.no_rawat') // Join dengan reg_periksa
                ->where('bayi_kelahiran.kondisi_janin', 'livebirth') // Hanya yang lahir hidup
                ->where('bayi_kelahiran.berat_lahir', '>=', 2500) // Berat lahir >= 2500 gram
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]); // Gunakan tgl_registrasi
                })
                ->select([
                    DB::raw('COUNT(DISTINCT bayi_kelahiran.no_rawat) as total')
                ])
                ->first();
                // End Bayi Lahir >= 2,5 KG

                // Start Bayi Lahir < 2,5 KG
                $bayi_lahir_2setkilokur = DB::table('bayi_kelahiran')
                ->join('reg_periksa', 'bayi_kelahiran.no_rawat', '=', 'reg_periksa.no_rawat') // Join dengan reg_periksa
                ->where('bayi_kelahiran.kondisi_janin', 'livebirth') // Hanya yang lahir hidup
                ->where(function ($query) {
                    $query->where('bayi_kelahiran.berat_lahir', '<', 2500) // Berat lahir < 2500 gram
                        ->orWhereNull('bayi_kelahiran.berat_lahir'); // Atau berat lahir kosong (NULL)
                })
                ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                    return $query->whereBetween('reg_periksa.tgl_registrasi', [$tgl1, $tgl2]); // Gunakan tgl_registrasi
                })
                ->select([
                    DB::raw('COUNT(DISTINCT bayi_kelahiran.no_rawat) as total')
                ])
                ->first();
                // End Bayi Lahir < 2,5 KG
                $total_berat = ($bayi_lahir_2setkilolebih->total ?? 0) + ($bayi_lahir_2setkilokur->total ?? 0);


        return view('rm.laporan_rm.ibudanbayi', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,
            'dari'=>$formattedTglAwalSebelum,
            'sampai'=>$formattedTglAkhirSebelum,


            'bayilahir'=>$bayi_lahir,
            'bayimati'=>$bayi_mati,
            'bayimatiranap'=>$bayi_matiranap,

            'bayi25'=>$bayi_lahir_2setkilolebih,
            'bayi24'=>$bayi_lahir_2setkilokur,

            'total_lahirmati' => $total_lahirmati,
            'total_berat' => $total_berat,
        ]); // by Ihsan

    }
}

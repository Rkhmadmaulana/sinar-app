<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF; 
use App\Exports\PasienMeninggalExport;
use Maatwebsite\Excel\Facades\Excel; //

class LaporanController extends Controller{
    public function kelengkapanrm(Request $request){
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
            ->join(DB::raw('
            (
                SELECT no_rawat, kd_kamar
                FROM kamar_inap
                WHERE (no_rawat, tgl_masuk, jam_masuk) IN (
                    SELECT no_rawat, MAX(tgl_masuk), MAX(jam_masuk)
                    FROM kamar_inap
                    GROUP BY no_rawat
                )
            ) as ki'), 'a.no_rawat', '=', 'ki.no_rawat')
            ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
            ->join('bangsal as bang', 'k.kd_bangsal', '=', 'bang.kd_bangsal')
            ->leftJoin('kelengkapan_rm as krm', 'a.no_rawat', '=', 'krm.no_rawat')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->where('a.status_lanjut', '=', 'Ranap')
            ->orderBy('a.no_rawat', 'desc')
            ->select('a.no_rawat', 'a.no_rkm_medis', 'b.nm_pasien', 'a.status_lanjut', 'bang.nm_bangsal', 'krm.verif_all')
            ->get();

        return view('rm.laporan_rm.kelengkapan_rm', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,
            'tgllap' => $tanggal,
            'nmr_rwt' => $sqlnr,
        ]);
    }

    //ambil NO RAWAT pasien
    public function getModalContent(Request $request){
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.no_rawat', '=', $id)
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Ambil data kelengkapan jika sudah ada
        $kelengkapan = DB::table('kelengkapan_rm')->where('no_rawat', $id)->first();

        $list = [
            'verif_sep' => ['label' => 'SEP BPJS', 'route' => 'erm_ranap_sep'],
            'verif_resume' => ['label' => 'Ringkasan Pasien Keluar Rawat Inap (Resume Medis)', 'route' => 'erm_ranap_resume'],
            'verif_general_consent' => ['label' => 'General Consent', 'route' => 'erm_ranap_persetujuan_umum'],
            'verif_ews' => ['label' => 'EWS Neonatus/PEWS Anak/PEWS Dewasa/MEOWS Obstetri', 'route' => 'erm_ranap_ews'],
            'verif_partograf' => ['label' => 'Partograf', 'route' => 'erm_ranap_partograf'],
            'verif_asesmen_awal_medis' => ['label' => 'Asesmen Awal Medis', 'route' => 'erm_ranap_medis_umum'],
            'verif_rekonsiliasi_obat' => ['label' => 'Rekonsiliasi Obat', 'route' => 'erm_ranap_rekonsiliasi_obat'],
            'verif_cppt' => ['label' => 'CPPT', 'route' => 'erm_ranap_cppt'],
            'verif_ctt_perkembangan' => ['label' => 'Catatan Perkembangan/Keperawatan Rawat Inap', 'route' => 'erm_ranap_catatan_perkembangan'],
            'verif_cpo' => ['label' => 'CPO', 'route' => 'erm_ranap_cpo'],
            'verif_penunjang' => ['label' => 'Pemeriksaan Penunjang Medis', 'route' => 'erm_ranap_penunjang'],
            'verif_edu_informasi' => ['label' => 'Asesmen Kebutuhan Edukasi Dan Informasi', 'route' => 'erm_edukasi_pasien_keluarga_rj'],
            'verif_discharge_planning' => ['label' => 'Discharge Planning', 'route' => 'erm_perencanaan_pemulangan'],
            'verif_dpjp' => ['label' => 'Form DPJP', 'route' => 'erm_dpjp'],
            'verif_triase' => ['label' => 'Triase', 'route' => 'erm_data_triase_igd'],
            'verif_assesmen_igd' => ['label' => 'Asesmen Gawat Darurat', 'route' => 'erm_ranap_medis_igd'],
            'verif_transfer_pasien' => ['label' => 'Transfer Pasien Antar Ruangan', 'route' => 'erm_transfer_pasien_antar_ruang'],
            'verif_observasi_ttv' => ['label' => 'Observasi TTV', 'route' => 'erm_catatan_observasi_ranap'],
            'verif_risiko_jatuh' => ['label' => 'Asesmen Resiko Jatuh Anak / Dewasa / Lansia', 'route' => 'erm_ranap_resikogabungan'],
            'verif_informed_consent_anastesi' => ['label' => 'Informed Consent', 'route' => 'erm_ranap_icta'],
            'verif_penandaan_op' => ['label' => 'Penandaan Operasi Pria / Perempuan', 'route' => 'erm_penandaanop'],
            'verif_serah_terima_pasien_op' => ['label' => 'Checklist Serah Terima Pasien Pre Operatif', 'route' => 'erm_checklistpreop'],
            'verif_penilaian_pra_anastesi' => ['label' => 'Penilaian Pra Anastesi', 'route' => 'erm_penilaianprean'],

            'verif_praop' => ['label' => 'Penilaian Pra Operasi', 'route' => 'erm_ranap_pra_op'],
            'verif_pra_sedasi' => ['label' => 'Penilaian Pra Sedasi', 'route' => 'erm_ranap_pra_sedasi'],
            'verif_laporanop' => ['label' => 'Laporan Operasi', 'route' => 'erm_ranap_laporan_op'],
            'verif_berkas_digital' => ['label' => 'Berkas Digital', 'route' => 'erm_ranap_berkas_digital'],

            // 'verif_laporan_anastesi' => ['label' => 'Laporan Anastesi', 'route' => 'erm_laporananestesi'],
            'verif_inventaris_kasa' => ['label' => 'Sign Out Sebelum Menutup Luka / Inventaris Kasa', 'route' => 'erm_signoutsebelummenutupluka'],
            
        ];

        return view('rm.laporan_rm.modal-content', [
            'data' => $data,
            'kelengkapan' => $kelengkapan,
            'list' => $list
        ]);
    }

    public function simpanKelengkapan(Request $request){
        // === CASE: hanya update status verif_all override dari tombol Verifikasi/Batal ===
        if ($request->filled('no_rawat') && $request->exists('verif_all_override')) {
            $status = $request->input('verif_all_override') ? 1 : 0;

            DB::table('kelengkapan_rm')->updateOrInsert(
                ['no_rawat' => $request->no_rawat],
                [
                    'verif_all' => $status,
                    'time_stamp' => now(),
                    'nip' => session()->get('nik')
                ]
            );

            return response()->json(['status' => 'success']);
        }

        // === CASE: simpan form dari modal ===
        $validated = $request->validate([
            'no_rawat' => 'required',
            'no_rkm_medis' => 'required',
        ]);

        $nip = session()->get('nik');
        $cekPetugas = DB::table('petugas')->where('nip', $nip)->exists();

        if (!$cekPetugas) {
            return redirect()->back()->with('error', 'User tidak valid sebagai petugas.');
        }

        $data = [
            'no_rawat' => $request->no_rawat,
            'no_rkm_medis' => $request->no_rkm_medis,
            'nip' => $nip,
            'time_stamp' => now(),
        ];

        $fields = [
            'verif_sep',
            'verif_resume',
            'verif_general_consent',
            'verif_ews',
            'verif_partograf',
            'verif_asesmen_awal_medis',
            'verif_rekonsiliasi_obat',
            'verif_cppt',
            'verif_ctt_perkembangan',
            'verif_cpo',
            'verif_penunjang',
            'verif_edu_informasi',
            'verif_discharge_planning',
            'verif_dpjp',
            'verif_triase',
            'verif_assesmen_igd',
            'verif_transfer_pasien',
            'verif_observasi_ttv',
            'verif_risiko_jatuh',
            'verif_informed_consent_anastesi',
            'verif_penandaan_op',
            'verif_serah_terima_pasien_op',
            'verif_penilaian_pra_anastesi',
            'verif_praop', 
            'verif_pra_sedasi', 
            'verif_laporanop',
            'verif_berkas_digital',
            'verif_inventaris_kasa',
            'verif_persetujuan_tindakan_kedokteran',
        ];

        foreach ($fields as $field) {
            $data[$field] = $request->has($field) ? 1 : 0;
        }

        // Jika override dari checkbox di modal
        if ($request->has('verif_all_override')) {
            $data['verif_all'] = 1;
        } else {
            $data['verif_all'] = collect($fields)->every(fn($field) => $request->has($field)) ? 1 : 0;
        }

        DB::table('kelengkapan_rm')->updateOrInsert(
            ['no_rawat' => $request->no_rawat],
            $data
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.'
        ]);
    }

    //ambil NO RAWAT pasien
    public function getERMContent(Request $request){
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
        if (!empty($ews_dewasa)) {
            $ews = $ews_dewasa;
        } elseif (!empty($ews_anak)) {
            $ews = $ews_anak;
        } elseif (!empty($ews_neonatus)) {
            $ews = $ews_neonatus;
        } else {
            $ews = null;
        }

        $awal_keperawatan_anak = DB::table('penilaian_awal_keperawatan_ranap_bayi as a')
            ->where('a.no_rawat', '=', $id)->get();

        $awal_keperawatan_dewasa = DB::table('penilaian_awal_keperawatan_ranap as a')
            ->where('a.no_rawat', '=', $id)->get();

        if (!empty($awal_keperawatan_anak)) {
            $awal_keperawatan = $awal_keperawatan_anak;
        } elseif (!empty($awal_keperawatan_dewasa)) {
            $awal_keperawatan = $awal_keperawatan_dewasa;
        } else {
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

    public function getERMCPPT(Request $request){
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


    public function getERMMedisIGD(Request $request){
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

    public function getERMMedisUmum(Request $request){
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

    public function getERMCatatanPerkembangan(Request $request){
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

        $ctt_kep = DB::table('catatan_keperawatan_ranap as a')
            ->join('pegawai as b', 'b.nik', '=', 'a.nip')
            ->where('a.no_rawat', '=', $id)
            ->get();

        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_catatan_perkembangan', [
            'row' => $data,
            'ctt_kep' => $ctt_kep,
        ]);
    }

    public function getERMPersetujuanUmum(Request $request){
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

    public function getERMRekonsiliasiObat(Request $request){
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

    public function getERMCPO(Request $request){
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

    public function getERMPenunjang(Request $request){
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

        // $lab = DB::table('detail_periksa_lab as dpl')
        //         ->join('permintaan_lab as pl', 'dpl.no_rawat', '=', 'pl.no_rawat')
        //         ->join('dokter as b', 'b.kd_dokter', '=', 'pl.dokter_perujuk')
        //         ->join('jns_perawatan_lab as jpl', 'jpl.kd_jenis_prw', '=', 'dpl.kd_jenis_prw')
        //         ->join('template_laboratorium as d', 'd.id_template', '=', 'dpl.id_template')
        //         ->where('dpl.no_rawat', $id)
        //         ->select(
        //             'dpl.no_rawat',
        //             'jpl.nm_perawatan',
        //             'dpl.kd_jenis_prw',
        //             'dpl.id_template',
        //             'd.satuan',
        //             'dpl.nilai',
        //             'dpl.nilai_rujukan',
        //             'dpl.tgl_periksa',
        //             'dpl.jam',
        //             'dpl.keterangan',
        //             'd.pemeriksaan',
        //             'b.nm_dokter'
        //         )
        //         ->orderBy('dpl.tgl_periksa', 'desc')
        //         ->get()
        //         ->groupBy('nm_perawatan');


        $lab = DB::table('permintaan_lab as pl')
            ->join('detail_periksa_lab as dpl', function ($join) {
                $join->on('pl.no_rawat', '=', 'dpl.no_rawat');
                // Tidak bisa join pakai noorder karena tidak ada di dpl
            })
            ->join('template_laboratorium as tl', 'dpl.id_template', '=', 'tl.id_template')
            ->join('dokter as b', 'b.kd_dokter', '=', 'pl.dokter_perujuk')
            ->select(
                'pl.noorder',
                'pl.no_rawat',
                'pl.tgl_permintaan',
                'pl.jam_permintaan',
                'pl.tgl_hasil',
                'pl.jam_hasil',
                'pl.dokter_perujuk',
                DB::raw("GROUP_CONCAT(tl.Pemeriksaan ORDER BY tl.urut SEPARATOR '|') as daftar_pemeriksaan"),
                DB::raw("GROUP_CONCAT(dpl.nilai ORDER BY tl.urut SEPARATOR '|') as daftar_nilai"),
                DB::raw("GROUP_CONCAT(tl.satuan ORDER BY tl.urut SEPARATOR '|') as daftar_satuan"),
                DB::raw("GROUP_CONCAT(dpl.nilai_rujukan ORDER BY tl.urut SEPARATOR '|') as daftar_rujukan"),
                DB::raw("GROUP_CONCAT(dpl.keterangan ORDER BY tl.urut SEPARATOR '|') as daftar_keterangan"),
                DB::raw('b.nm_dokter as nm_dokter'),
            )
            ->where('pl.no_rawat', $id)
            ->whereRaw('dpl.tgl_periksa = pl.tgl_permintaan') // Tambahan penting (cocokkan tanggal)
            ->groupBy(
                'pl.noorder',
                'pl.no_rawat',
                'pl.tgl_permintaan',
                'pl.jam_permintaan',
                'pl.tgl_hasil',
                'pl.jam_hasil',
                'pl.dokter_perujuk',
                'b.nm_dokter'
            )
            ->orderBy('pl.tgl_permintaan')
            ->orderBy('pl.noorder')
            ->orderBy('pl.dokter_perujuk')
            ->get();


        $radiologi = DB::table('hasil_radiologi as hr')
            ->join('permintaan_radiologi as pr', 'hr.no_rawat', '=', 'pr.no_rawat')
            ->join('dokter as b', 'b.kd_dokter', '=', 'pr.dokter_perujuk')
            ->select(
                'hr.tgl_periksa',
                'hr.jam',
                'hr.hasil',
                'b.nm_dokter'
            )
            ->where('hr.no_rawat', '=', $id)
            ->get();

        // Kirim data ke view erm.blade.php 
        return view('rm.laporan_rm.berkas_rm.erm_penunjang', [
            'row' => $data,
            'lab' => $lab,
            'noRawat' => $id,
            'radiologi' => $radiologi,
        ]);
    }

    public function getERMResume(Request $request){
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
            ->leftJoin('dokter as d', 'a.kd_dokter', '=', 'd.kd_dokter')
            ->where('a.no_rawat', '=', $id)
            ->select('a.*', 'd.nm_dokter as dokter_resume')
            ->get();

        $dpjp_dokter = DB::table('dpjp_ranap as d')
            ->join('dokter as k', 'd.kd_dokter', '=', 'k.kd_dokter')
            ->where('d.no_rawat', '=', $id)
            ->select('d.kd_dokter', 'k.nm_dokter')
            ->get();


        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_resume', [
            'row' => $data,
            'resume' => $resume,
            'dpjp_dokter' => $dpjp_dokter,
        ]);
    }

    public function getERMEWS(Request $request){
        $id = $request->query('id');
        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.no_rawat', '=', $id)
            ->where('a.status_lanjut', '=', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $ews_dewasa = DB::table('pemantauan_pews_dewasa as e')
            ->leftJoin('petugas as p', 'e.nip', '=', 'p.nip')
            ->select('e.*', 'p.nama as nama')
            ->where('e.no_rawat', $id)
            ->get();

        $ews_anak = DB::table('pemantauan_pews_anak as e')
            ->leftJoin('petugas as p', 'e.nip', '=', 'p.nip')
            ->select('e.*', 'p.nama as nama')
            ->where('e.no_rawat', $id)
            ->get();

        $ews_neonatus = DB::table('pemantauan_ews_neonatus as e')
            ->leftJoin('petugas as p', 'e.nip', '=', 'p.nip')
            ->select('e.*', 'p.nama as nama')
            ->where('e.no_rawat', $id)
            ->get();

        $ews_obstetri = DB::table('pemantauan_meows_obstetri as e')
            ->leftJoin('petugas as p', 'e.nip', '=', 'p.nip')
            ->select('e.*', 'p.nama as nama')
            ->where('e.no_rawat', $id)
            ->get();

        if ($ews_dewasa->isNotEmpty()) {
            $ews = $ews_dewasa;
            $table = 'pemantauan_pews_dewasa';
        } elseif ($ews_anak->isNotEmpty()) {
            $ews = $ews_anak;
            $table = 'pemantauan_pews_anak';
        } elseif ($ews_neonatus->isNotEmpty()) {
            $ews = $ews_neonatus;
            $table = 'pemantauan_ews_neonatus';
        } elseif ($ews_obstetri->isNotEmpty()) {
            $ews = $ews_obstetri;
            $table = 'pemantauan_meows_obstetri';
        } else {
            $ews = [];
            $table = null;
        }

        return view('rm.laporan_rm.berkas_rm.erm_ews', [
            'row' => $data,
            'ews' => $ews,
            'table' => $table
        ]);
    }

    public function getERMPartograf(Request $request){
        // Ambil ID dari query string
        $id = $request->query('id');

        // Validasi data pasien dari reg_periksa
        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.no_rawat', '=', $id)
            ->where('a.status_lanjut', '=', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Ambil data partograf berkas digital
        $berkas = DB::table('berkas_digital_perawatan')
            ->where('kode', '012')
            ->where('no_rawat', $id)
            ->where(function ($query) {
                $query->where('lokasi_file', 'LIKE', '%.jpg')
                    ->orWhere('lokasi_file', 'LIKE', '%.jpeg');
            })
            ->orderBy('no_rawat', 'desc')
            ->get();

        if ($berkas->isEmpty()) {
            $berkas = collect();
        }

        return view('rm.laporan_rm.berkas_rm.erm_partograf', [
            'row' => $data,
            'berkas' => $berkas,
        ]);
    }

    public function getERMBerkasDigital(Request $request){
        // Ambil ID dari query string
        $id = $request->query('id');

        // Validasi data pasien dari reg_periksa
        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.no_rawat', '=', $id)
            ->where('a.status_lanjut', '=', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Ambil data partograf berkas digital
        $berkas = DB::table('berkas_digital_perawatan')
            // ->where('kode', '012')
            ->where('no_rawat', $id)
            ->where(function ($query) {
                $query->where('lokasi_file', 'LIKE', '%.jpg')
                    ->orWhere('lokasi_file', 'LIKE', '%.jpeg');
            })
            ->orderBy('no_rawat', 'desc')
            ->get();

        if ($berkas->isEmpty()) {
            $berkas = collect(); // kosong tapi tidak error di view
        }

        return view('rm.laporan_rm.berkas_rm.erm_berkas_digital', [
            'row' => $data,
            'berkas' => $berkas,
        ]);
    }

    public function getERMSEP(Request $request){
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

        $sep = DB::table('reg_periksa as r')
                ->join('bridging_sep as b', 'r.no_rawat', '=', 'b.no_rawat')
                ->select(
                    'r.no_rawat',
                    'r.status_lanjut',
                    'b.no_kartu',
                    'b.no_sep',
                    'b.tglsep',
                    'b.tanggal_lahir',
                    'b.notelep',
                    'b.jnspelayanan',
                    'b.tglpulang',
                    'b.nmpolitujuan',
                    'b.nmdpdjp',
                    'b.diagawal',
                    'b.nmdiagnosaawal',
                    'b.peserta',
                    'b.tujuankunjungan',
                    'b.klsrawat',
                    'b.klsnaik',
                    'b.catatan')
                ->where('r.no_rawat', '=', $id)
                ->get();
            
        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_sep', [
            'row' => $data,
            'sep' => $sep,
        ]);
    }

    public function getERMDPJP(Request $request){
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

        // Ambil data DPJP (dokter yang bertanggung jawab)
        $dpjp = DB::table('dpjp_ranap as a')
            ->join('dokter as b', 'b.kd_dokter', '=', 'a.kd_dokter')
            ->where('a.no_rawat', '=', $id)
            ->select('a.kd_dokter', 'b.nm_dokter')
            ->get();

        // Kirim data ke view erm_dpjp.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_dpjp', [
            'row' => $data,
            'dpjp_ranap' => $dpjp, // kirim data dpjp ke view
        ]);
    }

    public function getERMRencanaPemulangan(Request $request){
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

        // Ambil data rencana pemulangan
        $perencanaan_pemulangan = DB::table('perencanaan_pemulangan as a')
            ->join('pegawai as b', 'b.nik', '=', 'a.nip')
            ->where('a.no_rawat', '=', $id)->get();

        // Kirim data ke view erm_perencanaan_pemulangan.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_perencanaan_pemulangan', [
            'row' => $data,
            'perencanaan_pemulangan' => $perencanaan_pemulangan, // kirim data perencanaan pemulangan ke view
        ]);
    }

    public function getERMTransferAntarRuang(Request $request){
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

        // Ambil data transfer pasien antar ruang
        $transfer_pasien_antar_ruang = DB::table('transfer_pasien_antar_ruang as a')
            ->join('pegawai as b', 'b.nik', '=', 'a.nip_menerima')
            ->join('pegawai as c', 'c.nik', '=', 'a.nip_menyerahkan')
            ->join('reg_periksa as d', 'd.no_rawat', '=', 'a.no_rawat')
            ->join('pasien as e', 'e.no_rkm_medis', '=', 'd.no_rkm_medis')
            ->where('a.no_rawat', '=', $id)
            ->select(
                'a.*',
                'b.nama as nama_petugas_menerima',
                'c.nama as nama_petugas_menyerahkan',
                'e.nm_pasien',
                'e.tgl_lahir'
            )
            ->get();


        // Kirim data ke view erm_cppt.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_transfer_pasien_antar_ruang', [
            'row' => $data,
            'transfer_pasien_antar_ruang' => $transfer_pasien_antar_ruang, // kirim data perencanaan pemulangan ke view
        ]);
    }

    public function getERMCatatanObservasi(Request $request){
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

        // Ambil data catatan obervasi ranap
        $catatan_observasi_ranap = DB::table('catatan_observasi_ranap as a')
            ->join('pegawai as b', 'b.nik', '=', 'a.nip')
            ->where('a.no_rawat', '=', $id)
            ->select('a.*', 'b.nama as nama_petugas')
            ->get();

        // Kirim data ke view erm_catatan_observasi_ranap.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_catatan_observasi_ranap', [
            'row' => $data,
            'catatan_observasi_ranap' => $catatan_observasi_ranap, // kirim data perencanaan pemulangan ke view
        ]);
    }

    public function getERMTriaseIGD(Request $request){
        $id = $request->query('id');

        // Ambil data pasien dan rawat inap
        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.no_rawat', '=', $id)
            ->where('a.status_lanjut', '=', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $table = "data_triase_igdprimer";
        $table2 = "data_triase_igdsekunder";

        // Ambil data triase lengkap dengan semua join
        $data_triase_igd = DB::table('data_triase_igd as dt')
            ->leftJoin('master_triase_macam_kasus as mk', 'dt.kode_kasus', '=', 'mk.kode_kasus')
            ->leftJoin('data_triase_igdprimer as dp', 'dt.no_rawat', '=', 'dp.no_rawat')
            ->leftJoin('data_triase_igdsekunder as ds', 'dt.no_rawat', '=', 'ds.no_rawat')

            // Join ke pegawai untuk nik primer dan sekunder
            ->leftJoin('pegawai as pg1', 'dp.nik', '=', 'pg1.nik')
            ->leftJoin('pegawai as pg2', 'ds.nik', '=', 'pg2.nik')

            // Skala
            ->leftJoin('data_triase_igddetail_skala1 as s1', 'dt.no_rawat', '=', 's1.no_rawat')
            ->leftJoin('data_triase_igddetail_skala2 as s2', 'dt.no_rawat', '=', 's2.no_rawat')
            ->leftJoin('data_triase_igddetail_skala3 as s3', 'dt.no_rawat', '=', 's3.no_rawat')
            ->leftJoin('data_triase_igddetail_skala4 as s4', 'dt.no_rawat', '=', 's4.no_rawat')
            ->leftJoin('data_triase_igddetail_skala5 as s5', 'dt.no_rawat', '=', 's5.no_rawat')

            // Master skala
            ->leftJoin('master_triase_skala1 as ms1', 's1.kode_skala1', '=', 'ms1.kode_skala1')
            ->leftJoin('master_triase_skala2 as ms2', 's2.kode_skala2', '=', 'ms2.kode_skala2')
            ->leftJoin('master_triase_skala3 as ms3', 's3.kode_skala3', '=', 'ms3.kode_skala3')
            ->leftJoin('master_triase_skala4 as ms4', 's4.kode_skala4', '=', 'ms4.kode_skala4')
            ->leftJoin('master_triase_skala5 as ms5', 's5.kode_skala5', '=', 'ms5.kode_skala5')

            // Pemeriksaan
            ->leftJoin('master_triase_pemeriksaan as mp1', 'ms1.kode_pemeriksaan', '=', 'mp1.kode_pemeriksaan')
            ->leftJoin('master_triase_pemeriksaan as mp2', 'ms2.kode_pemeriksaan', '=', 'mp2.kode_pemeriksaan')
            ->leftJoin('master_triase_pemeriksaan as mp3', 'ms3.kode_pemeriksaan', '=', 'mp3.kode_pemeriksaan')
            ->leftJoin('master_triase_pemeriksaan as mp4', 'ms4.kode_pemeriksaan', '=', 'mp4.kode_pemeriksaan')
            ->leftJoin('master_triase_pemeriksaan as mp5', 'ms5.kode_pemeriksaan', '=', 'mp5.kode_pemeriksaan')

            // Filter
            ->where('dt.no_rawat', '=', $id)

            // Select kolom
            ->select(
                'dt.no_rawat',
                'dt.tgl_kunjungan',
                'dt.cara_masuk',
                'alat_transportasi',
                'alasan_kedatangan',
                'keterangan_kedatangan',
                'dt.tekanan_darah',
                'dt.nadi',
                'dt.pernapasan',
                'dt.suhu',
                'dt.saturasi_o2',
                'dt.nyeri',
                'mk.macam_kasus as nama_kasus',

                DB::raw("CASE 
                WHEN s1.kode_skala1 IS NOT NULL THEN 'IMMEDIATE'
                WHEN s2.kode_skala2 IS NOT NULL THEN 'EMERGENCY'
                WHEN s3.kode_skala3 IS NOT NULL THEN 'URGENCY'
                WHEN s4.kode_skala4 IS NOT NULL THEN 'SEMI URGENCY'
                WHEN s5.kode_skala5 IS NOT NULL THEN 'NON URGENCY'
                ELSE 'Skala Tidak Diketahui'
            END as skala_triase"),

                // Data Primer dan Sekunder
                'dp.keluhan_utama',
                'dp.kebutuhan_khusus',
                'dp.plan as plan_primer',
                'dp.catatan as catatan_primer',
                'dp.tanggaltriase as tanggaltriase_primer',
                'dp.nik as nik_primer',
                'pg1.nama as nama_primer',
                'ds.anamnesa_singkat',
                'ds.plan as plan_sekunder',
                'ds.catatan as catatan_sekunder',
                'ds.tanggaltriase as tanggaltriase_sekunder',
                'ds.nik as nik_sekunder',
                'pg2.nama as nama_sekunder',

                // Pemeriksaan
                'mp1.nama_pemeriksaan as pemeriksaan_skala1',
                'mp2.nama_pemeriksaan as pemeriksaan_skala2',
                'mp3.nama_pemeriksaan as pemeriksaan_skala3',
                'mp4.nama_pemeriksaan as pemeriksaan_skala4',
                'mp5.nama_pemeriksaan as pemeriksaan_skala5',

                // Pengkajian
                'ms1.pengkajian_skala1',
                'ms2.pengkajian_skala2',
                'ms3.pengkajian_skala3',
                'ms4.pengkajian_skala4',
                'ms5.pengkajian_skala5'
            )
            ->get();

        $data_triase_igd = collect($data_triase_igd)->groupBy('tgl_kunjungan')->map(function ($group) {
            $first = $group->first();

            // Gabungkan semua pemeriksaan dari semua baris di group
            $allPemeriksaan = collect();

            foreach ($group as $item) {
                $allPemeriksaan = $allPemeriksaan->merge([
                    ['nama' => $item->pemeriksaan_skala1, 'pengkajian' => $item->pengkajian_skala1],
                    ['nama' => $item->pemeriksaan_skala2, 'pengkajian' => $item->pengkajian_skala2],
                    ['nama' => $item->pemeriksaan_skala3, 'pengkajian' => $item->pengkajian_skala3],
                    ['nama' => $item->pemeriksaan_skala4, 'pengkajian' => $item->pengkajian_skala4],
                    ['nama' => $item->pemeriksaan_skala5, 'pengkajian' => $item->pengkajian_skala5],
                ]);
            }

            // Filter data yang nama-nya null dan hilangkan duplikat berdasarkan 'nama'
            $first->pemeriksaan = $allPemeriksaan
                ->filter(fn($p) => !empty($p['nama']))
                ->unique('nama')
                ->values();

            return $first;
        })->values();



        // Kirim ke view
        return view('rm.laporan_rm.berkas_rm.erm_data_triase_igd', [
            'row' => $data,
            'data_triase_igd' => $data_triase_igd,
            'table' => $table,
            'table2' => $table2

        ]);
    }

    public function getERMEdukasi(Request $request){
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

        // Ambil data DPJP (dokter yang bertanggung jawab)
        $edukasi_pasien_keluarga_rj = DB::table('edukasi_pasien_keluarga_rj as a')
            ->join('reg_periksa as r', 'a.no_rawat', '=', 'r.no_rawat') // join ke reg_periksa untuk ambil no_rkm_medis
            ->join('pasien as p', 'r.no_rkm_medis', '=', 'p.no_rkm_medis') // join ke pasien untuk ambil pendidikan (pnd)
            ->join('pegawai as b', 'b.nik', '=', 'a.nip') // join ke pegawai untuk ambil nama petugas
            ->select('a.*', 'p.pnd as pendidikan', 'b.nama as nama_petugas')
            ->where('a.no_rawat', '=', $id)
            ->get();

        // Kirim data ke view erm_edukasi_pasien_keluarga_rj.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_edukasi_pasien_keluarga_rj', [
            'row' => $data,
            'edukasi_pasien_keluarga_rj' => $edukasi_pasien_keluarga_rj, // kirim data dpjp ke view
        ]);
    }

    public function getERMPP(Request $request){
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select('a.no_rawat', 'a.tgl_registrasi', 'a.jam_reg', 'b.nm_pasien')
            ->where('a.no_rawat', $id)
            ->where('a.status_lanjut', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $persetujuanpenolakan = DB::table('signout_sebelum_menutup_luka as pp')
            ->leftJoin('dokter as dbedah', 'pp.kd_dokter_bedah', '=', 'dbedah.kd_dokter')
            ->leftJoin('petugas as pok', 'pp.nip_perawat_ok', '=', 'pok.nip')
            ->select(
                'pp.*',
                'dbedah.nm_dokter as nama_dokter_bedah',
                'pok.nama as nama_perawat'
            )

            ->where('no_rawat', '=', $id)
            ->get();

        return view('rm.laporan_rm.berkas_rm.erm_persetujuanpenolakan', [
            'data' => $data,
            'persetujuanpenolakan' => $persetujuanpenolakan,
        ]);
    }

    public function getERMSIGNOUT(Request $request){
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select('a.no_rawat', 'a.tgl_registrasi', 'a.jam_reg', 'b.nm_pasien')
            ->where('a.no_rawat', $id)
            ->where('a.status_lanjut', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $signoutsebelummenutupluka = DB::table('signout_sebelum_menutup_luka as ssml')
            ->leftJoin('dokter as dbedah', 'ssml.kd_dokter_bedah', '=', 'dbedah.kd_dokter')
            ->leftJoin('dokter as danestesi', 'ssml.kd_dokter_anestesi', '=', 'danestesi.kd_dokter')
            ->leftJoin('petugas as pok', 'ssml.nip_perawat_ok', '=', 'pok.nip')
            ->select(
                'ssml.*',
                'dbedah.nm_dokter as nama_dokter_bedah',
                'danestesi.nm_dokter as nama_dokter_anestesi',
                'pok.nama as nama_perawat_ok'
            )

            ->where('no_rawat', '=', $id)
            ->get();

        return view('rm.laporan_rm.berkas_rm.erm_signoutsebelummenutupluka', [
            'data' => $data,
            'signoutsebelummenutupluka' => $signoutsebelummenutupluka,
        ]);
    }

    public function getERMPENILAIANPREAN(Request $request){
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select('a.no_rawat', 'a.tgl_registrasi', 'a.jam_reg', 'b.nm_pasien')
            ->where('a.no_rawat', $id)
            ->where('a.status_lanjut', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $penilaianprean = DB::table('penilaian_pre_anestesi as ppa')
            ->leftJoin('dokter as dktr', 'ppa.kd_dokter', '=', 'dktr.kd_dokter')
            ->select(
                'ppa.*',
                'dktr.nm_dokter as dokterprean'
            )
            ->where('no_rawat', '=', $id)
            ->get();

        return view('rm.laporan_rm.berkas_rm.erm_penilaianprean', [
            'data' => $data,
            'penilaianprean' => $penilaianprean,
        ]);
    }

    public function getERMPraOp(Request $request){
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select(
                'a.no_rawat',
                'a.tgl_registrasi',
                'a.jam_reg',
                'a.status_lanjut',
                'b.nm_pasien')
            ->where('a.no_rawat', $id)
            ->where('a.status_lanjut', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $penilaianpraop = DB::table('penilaian_pre_operasi as a')
            ->leftJoin('dokter as d', 'a.kd_dokter', '=', 'd.kd_dokter')
            ->select(
                'a.*',
                'd.nm_dokter')
            ->where('no_rawat', '=', $id)
            ->first();

        return view('rm.laporan_rm.berkas_rm.erm_praop', [
            'row' => $data,
            'ppo' => $penilaianpraop,
        ]);

    }

    public function getERMPraSedasi(Request $request){
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select(
                'a.no_rawat',
                'a.tgl_registrasi',
                'a.jam_reg',
                'a.status_lanjut',
                'b.nm_pasien')
            ->where('a.no_rawat', $id)
            ->where('a.status_lanjut', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $sedasi = DB::table('asesmen_pra_sedasi as a')
            ->leftJoin('dokter as d', 'a.kd_dokter', '=', 'd.kd_dokter')
            ->select(
                'a.*',
                'd.nm_dokter')
            ->where('no_rawat', '=', $id)
            ->first();

        return view('rm.laporan_rm.berkas_rm.erm_pra_sedasi', [
            'row' => $data,
            'sedasi' => $sedasi,
        ]);

    }

    public function getERMLaporanOp(Request $request){
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select('a.no_rawat', 'a.tgl_registrasi', 'a.jam_reg', 'b.nm_pasien', 'a.status_lanjut')
            ->where('a.no_rawat', $id)
            ->where('a.status_lanjut', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $laporanop = DB::table('laporan_operasi as lo')
            ->join('instruksi_operasi as io', 'lo.no_rawat', '=', 'io.no_rawat')
            ->join('operasi as op', 'lo.no_rawat', '=', 'op.no_rawat')
            ->leftJoin('dokter as d', 'op.operator1', '=', 'd.kd_dokter')
            ->select(
                'lo.no_rawat',
                'lo.tanggal',
                'lo.selesaioperasi',
                'lo.diagnosa_preop',
                'lo.diagnosa_postop',
                'lo.permintaan_pa',
                'lo.jaringan_dieksekusi',
                'lo.laporan_operasi',
                'io.instruksi',
                'io.jenis_operasi',
                'op.operator1',
                'd.nm_dokter')
            ->where('lo.no_rawat', '=', $id)
            ->first();

        return view('rm.laporan_rm.berkas_rm.erm_laporanop', [
            'row' => $data,
            'laporanop' => $laporanop,
        ]);

    }

    public function getERMLAPORANANESTESI(Request $request){
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select('a.no_rawat', 'a.tgl_registrasi', 'a.jam_reg', 'b.nm_pasien')
            ->where('a.no_rawat', $id)
            ->where('a.status_lanjut', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $laporananestesi = DB::table('monitoring_score_anestesi')
            ->where('no_rawat', '=', $id)
            ->get();

        return view('rm.laporan_rm.berkas_rm.erm_laporananestesi', [
            'data' => $data,
            'laporananestesi' => $laporananestesi,
        ]);
    }

    public function getERMCHECKLISTPREOP(Request $request){
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select('a.no_rawat', 'a.tgl_registrasi', 'a.jam_reg', 'b.nm_pasien')
            ->where('a.no_rawat', $id)
            ->where('a.status_lanjut', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $checklistpreop = DB::table('checklist_pre_operasi as cpo')
            ->leftJoin('dokter as dbedah', 'cpo.kd_dokter_bedah', '=', 'dbedah.kd_dokter')
            ->leftJoin('dokter as danestesi', 'cpo.kd_dokter_anestesi', '=', 'danestesi.kd_dokter')
            ->leftJoin('petugas as pruangan', 'cpo.nip_petugas_ruangan', '=', 'pruangan.nip')
            ->leftJoin('petugas as pok', 'cpo.nip_perawat_ok', '=', 'pok.nip')
            ->select(
                'cpo.*',
                'dbedah.nm_dokter as nama_dokter_bedah',
                'danestesi.nm_dokter as nama_dokter_anestesi',
                'pruangan.nama as nama_petugas_ruangan',
                'pok.nama as nama_perawat_ok'
            )
            ->where('cpo.no_rawat', '=', $id)
            ->get();

        return view('rm.laporan_rm.berkas_rm.erm_checklistpreop', [
            'data' => $data,
            'checklistpreop' => $checklistpreop,
        ]);
    }

    public function getERMPENANDAANOP(Request $request){
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select('a.no_rawat', 'a.tgl_registrasi', 'a.jam_reg', 'b.nm_pasien')
            ->where('a.no_rawat', $id)
            ->where('a.status_lanjut', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $penandaan = DB::table('berkas_digital_perawatan')
            ->where('kode', '009')
            ->where('no_rawat', $id)
            ->where(function ($query) {
                $query->where('lokasi_file', 'LIKE', '%.jpg')
                    ->orWhere('lokasi_file', 'LIKE', '%.jpeg');
            })
            ->orderBy('no_rawat', 'desc')
            ->get();

        if ($penandaan->isEmpty()) {
            $penandaan = collect();
        }

        return view('rm.laporan_rm.berkas_rm.erm_penandaanop', [
            'data' => $data,
            'penandaan' => $penandaan,
        ]);
    }

    public function getERMICTA(Request $request){
        // Ambil data berdasarkan ID
        $id = $request->query('id');
        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select('a.no_rawat', 'a.tgl_registrasi', 'a.jam_reg', 'b.nm_pasien') // tambah kolom penting
            ->where('a.no_rawat', '=', $id)
            ->where('a.status_lanjut', '=', 'Ranap')
            ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Ambil data Informed Consent
        $icta = DB::table('persetujuan_penolakan_tindakan as i')
            ->leftJoin('petugas as p', 'i.nip', '=', 'p.nip')
            ->select('i.*', 'p.nama as nama_petugas')
            ->where('i.no_rawat', '=', $id)
            ->get();

        // Kirim data ke view
        return view('rm.laporan_rm.berkas_rm.erm_icta', [
            'row' => $data,
            'icta' => $icta,
        ]);
    }

    public function getERMRESIKOGABUNGAN(Request $request){
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select('a.no_rawat', 'a.tgl_registrasi', 'a.jam_reg', 'b.nm_pasien')
            ->where('a.no_rawat', '=', $id)
            ->where('a.status_lanjut', '=', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $resiko_anak = DB::table('penilaian_lanjutan_resiko_jatuh_anak as e')
            ->leftJoin('petugas as p', 'e.nip', '=', 'p.nip')
            ->select('e.*', 'p.nama as nama')
            ->where('e.no_rawat', $id)
            ->get();

        $resiko_lansia = DB::table('penilaian_lanjutan_resiko_jatuh_lansia as e')
            ->leftJoin('petugas as p', 'e.nip', '=', 'p.nip')
            ->select('e.*', 'p.nama as nama')
            ->where('e.no_rawat', $id)
            ->get();

        $resiko_dewasa = DB::table('penilaian_lanjutan_resiko_jatuh_dewasa as e')
            ->leftJoin('petugas as p', 'e.nip', '=', 'p.nip')
            ->select('e.*', 'p.nama as nama')
            ->where('e.no_rawat', $id)
            ->get();

        if ($resiko_anak->isNotEmpty()) {
            $resiko = $resiko_anak;
            $table = 'penilaian_lanjutan_resiko_jatuh_anak';
        } elseif ($resiko_lansia->isNotEmpty()) {
            $resiko = $resiko_lansia;
            $table = 'penilaian_lanjutan_resiko_jatuh_lansia';
        } elseif ($resiko_dewasa->isNotEmpty()) {
            $resiko = $resiko_dewasa;
            $table = 'penilaian_lanjutan_resiko_jatuh_dewasa';
        } else {
            $resiko = [];
            $table = null;
        }

        return view('rm.laporan_rm.berkas_rm.erm_resiko_gabungan', [
            'row' => $data,
            'resiko' => $resiko,
            'table' => $table
        ]);
    }

    public function getERMRESIKOANAK(Request $request){
        // Ambil data berdasarkan ID
        $id = $request->query('id');
        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select('a.no_rawat', 'a.tgl_registrasi', 'a.jam_reg', 'b.nm_pasien') // tambah kolom penting
            ->where('a.no_rawat', '=', $id)
            ->where('a.status_lanjut', '=', 'Ranap')
            ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Ambil data asesmen resiko jatuh anak
        $resiko_anak = DB::table('penilaian_lanjutan_resiko_jatuh_anak')
            ->where('no_rawat', '=', $id)
            ->get();

        // Kirim data ke view
        return view('rm.laporan_rm.berkas_rm.erm_resiko_anak', [
            'row' => $data,
            'resiko_anak' => $resiko_anak,
        ]);
    }

    public function getERMRESIKOLANSIA(Request $request){
        // Ambil data berdasarkan ID
        $id = $request->query('id');
        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select('a.no_rawat', 'a.tgl_registrasi', 'a.jam_reg', 'b.nm_pasien') // tambah kolom penting
            ->where('a.no_rawat', '=', $id)
            ->where('a.status_lanjut', '=', 'Ranap')
            ->first();

        // Pastikan data ditemukan
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Ambil data asesmen resiko jatuh lansia
        $resiko_lansia = DB::table('penilaian_lanjutan_resiko_jatuh_lansia')
            ->where('no_rawat', '=', $id)
            ->get();

        // Kirim data ke view
        return view('rm.laporan_rm.berkas_rm.erm_resiko_lansia', [
            'row' => $data,
            'resiko_lansia' => $resiko_lansia,
        ]);
    }


    public function kunjunganrajal(Request $request){
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

    public function kunjunganranap(Request $request){
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

    public function penyakitterbanyak(Request $request){
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

    public function penyakitmenular(Request $request){

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

    public function igd(Request $request){
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

    public function operasi(Request $request){
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

    public function kematian(Request $request){
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
        $total_bpjs_khusus = $meninggal_anggota->total + $meninggal_pns->total + $meninggal_keluarga->total + $meninggal_dikbang->total + $meninggal_diktuk->total;
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
        $total_meninggal2 = $meninggal_igd->total2 +  $meninggal_ranap->total2;

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
        $total_meninggal = $total_bpjs_khusus + $meninggal_umum->total + $total_bpjs + $meninggal_lainnya->total;


        return view('rm.laporan_rm.laporan_kematian', [

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

    public function pertumbuhan(Request $request){
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
    public function laporan_radlab(Request $request){
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


    public function totalresep(Request $request){
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

    public function detailresep(Request $request){
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
    public function getModalResep(Request $request){
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
    public function ibudanbayi(Request $request){

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
        $bayi_lahir = DB::table(DB::raw('bayi_kelahiran'))
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
            'dari' => $formattedTglAwalSebelum,
            'sampai' => $formattedTglAkhirSebelum,


            'bayilahir' => $bayi_lahir,
            'bayimati' => $bayi_mati,
            'bayimatiranap' => $bayi_matiranap,

            'bayi25' => $bayi_lahir_2setkilolebih,
            'bayi24' => $bayi_lahir_2setkilokur,

            'total_lahirmati' => $total_lahirmati,
            'total_berat' => $total_berat,
        ]); // by Ihsan

    }

    public function pasienMeninggal(Request $request){
        $user = Auth::user();
        $tanggalAwal = $request->input('tanggal_awal', date('Y-m-01'));
        $tanggalAkhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $bangsal = $request->input('bangsal', '');

        $data = [];
        $totalData = [];
        
        if ($request->has('tanggal_awal') && $request->has('tanggal_akhir')) {
            // Query data pasien meninggal
            $query = DB::select("
                SELECT tgl_registrasi, no_rawat, no_rkm_medis, jenis_pasien, nm_pasien, 
                    alamat, jk, no_ktp, tgl_lahir, umurdaftar, png_jawab, nm_penyakit, 
                    kd_penyakit, prioritas, kd_dokter, nm_dokter, kd_sps, nm_sps, 
                    status_lanjut, kd_kamar, kelas, tgl_masuk, jam_masuk, tgl_keluar, 
                    jam_keluar, stts_pulang, kd_bangsal, nm_bangsal, kd_dokter_dpjp, 
                    nm_dokter_dpjp, json_dpjp
                FROM laporan_sensus_pasien_ranap t
                WHERE t.tgl_masuk >= ?
                AND t.tgl_masuk <= ?
                AND t.stts_pulang = 'Meninggal'
                " . ($bangsal ? "AND t.kd_bangsal = ?" : "") . "
                GROUP BY t.no_rawat
                ORDER BY t.tgl_masuk ASC, t.no_rkm_medis ASC, -t.prioritas DESC
            ", $bangsal ? [$tanggalAwal, $tanggalAkhir, $bangsal] : [$tanggalAwal, $tanggalAkhir]);
            
            $data = collect($query)->map(function ($item, $index) {
                // Hitung selisih waktu untuk menentukan meninggal < 48 jam atau >= 48 jam
                $waktuMasuk = Carbon::parse($item->tgl_masuk . ' ' . $item->jam_masuk);
                $waktuKeluar = Carbon::parse($item->tgl_keluar . ' ' . $item->jam_keluar);
                $selisihJam = $waktuMasuk->diffInHours($waktuKeluar);
                $kurangDari48Jam = $selisihJam < 48;
                
                // Hitung umur dari tanggal lahir
                $umur = null;
                if (!empty($item->tgl_lahir)) {
                    try {
                        $tglLahir = Carbon::parse($item->tgl_lahir);
                        $tglSekarang = Carbon::parse($item->tgl_keluar);
                    
                        // Hitung umur dalam tahun
                        $umurTahun = $tglLahir->age;
                    
                        // Jika umur kurang dari 1 tahun, tampilkan dalam bulan
                        if ($umurTahun < 1) {
                            $umurBulan = $tglLahir->diffInMonths($tglSekarang);
                            
                            // Jika kurang dari 1 bulan, tampilkan dalam hari
                            if ($umurBulan < 1) {
                                $umurHari = $tglLahir->diffInDays($tglSekarang);
                                $umur = $umurHari . ' hari';
                            } else {
                                $umur = $umurBulan . ' bln';
                            }
                        } else {
                            $umur = $umurTahun;
                        }
                    } catch (\Exception $e) {
                        $umur = null;
                    }
                }
                
                return (object)[
                    'no' => $index + 1,
                    'tgl_masuk' => $item->tgl_masuk,
                    'tgl_keluar' => $item->tgl_keluar,
                    'no_rkm_medis' => $item->no_rkm_medis,
                    'nm_pasien' => $item->nm_pasien,
                    'jk' => $item->jk,
                    'tgl_lahir' => $item->tgl_lahir,
                    'umur' => $umur,
                    'png_jawab' => $item->png_jawab,
                    'meninggal_kurang_48jam' => ($item->stts_pulang == 'Meninggal' && $kurangDari48Jam) ? 'Ya' : '-',
                    'meninggal_lebih_48jam' => ($item->stts_pulang == 'Meninggal' && !$kurangDari48Jam) ? 'Ya' : '-',
                    'kd_penyakit' => $item->kd_penyakit,
                    'nm_penyakit' => $item->nm_penyakit,
                    'item' => $item,
                ];
            });
            
            // Buat data untuk tabel kedua (ringkasan diagnosa)
            $totalData = $this->hitungTotalDiagnosa($data);
        }
        
         // Dapatkan daftar bangsal menggunakan method getBangsal
        $daftarBangsalResponse = $this->getBangsalData($tanggalAwal, $tanggalAkhir, true);
        $daftarBangsal = $daftarBangsalResponse['data'];
            
        return view('rm.laporan_rm.pasien-meninggal', [
            'data' => $data,
            'totalData' => $totalData,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'bangsal' => $bangsal,
            'daftarBangsal' => $daftarBangsal,
            'title' => 'Daftar Pasien Meninggal',
            'breadcrumbs' => [
                ['url' => '/dashboard', 'name' => 'Dashboard'],
                ['url' => '#', 'name' => 'Laporan'],
                ['url' => '#', 'name' => 'Daftar Pasien Meninggal', 'active' => true],
            ],
        ]);
    }
    

    protected function hitungTotalDiagnosa($data){
        $diagnosaMap = [];
        
        foreach ($data as $item) {
            $diagnosaKey = $item->kd_penyakit;
            $diagnosaText = $item->nm_penyakit . ' (' . $item->kd_penyakit . ')';
            
            if (!isset($diagnosaMap[$diagnosaKey])) {
                $diagnosaMap[$diagnosaKey] = [
                    'diagnosa' => $diagnosaText,
                    'laki' => 0,
                    'perempuan' => 0,
                    'umur_lt_1' => 0,
                    'umur_lt_4' => 0,
                    'umur_lt_9' => 0,
                    'umur_lt_14' => 0,
                    'umur_lt_19' => 0,
                    'umur_lt_44' => 0,
                    'umur_lt_54' => 0,
                    'umur_lt_59' => 0,
                    'umur_lt_69' => 0,
                    'umur_lt_70' => 0,
                    'umur_null' => 0,
                    'meninggal_kurang_48jam' => 0,
                    'meninggal_lebih_48jam' => 0,
                ];
            }
            
            // Hitung jenis kelamin
            if ($item->jk == 'L') {
                $diagnosaMap[$diagnosaKey]['laki']++;
            } else if ($item->jk == 'P') {
                $diagnosaMap[$diagnosaKey]['perempuan']++;
            }
            
            // Hitung kelompok umur
            if ($item->umur === null) {
                $diagnosaMap[$diagnosaKey]['umur_null']++;
            } else {
                // Jika format umur dalam bulan (misal: "7 bln")
                if (is_string($item->umur) && strpos($item->umur, 'bln') && strpos($item->umur, 'hari') !== false) {
                    // Ini berarti umur < 1 tahun
                    $diagnosaMap[$diagnosaKey]['umur_lt_1']++;
                } else {
                    // Umur dalam format numerik (tahun)
                    $umurNumerik = is_numeric($item->umur) ? $item->umur : 0;
                    
                    if ($umurNumerik < 1) {
                        $diagnosaMap[$diagnosaKey]['umur_lt_1']++;
                    } else if ($umurNumerik < 4) {
                        $diagnosaMap[$diagnosaKey]['umur_lt_4']++;
                    } else if ($umurNumerik < 9) {
                        $diagnosaMap[$diagnosaKey]['umur_lt_9']++;
                    } else if ($umurNumerik < 14) {
                        $diagnosaMap[$diagnosaKey]['umur_lt_14']++;
                    } else if ($umurNumerik < 19) {
                        $diagnosaMap[$diagnosaKey]['umur_lt_19']++;
                    } else if ($umurNumerik < 44) {
                        $diagnosaMap[$diagnosaKey]['umur_lt_44']++;
                    } else if ($umurNumerik < 54) {
                        $diagnosaMap[$diagnosaKey]['umur_lt_54']++;
                    } else if ($umurNumerik < 59) {
                        $diagnosaMap[$diagnosaKey]['umur_lt_59']++;
                    } else if ($umurNumerik < 69) {
                        $diagnosaMap[$diagnosaKey]['umur_lt_69']++;
                    } else {
                        $diagnosaMap[$diagnosaKey]['umur_lt_70']++;
                    }
                }
            }
            
            // Hitung meninggal
            if ($item->meninggal_kurang_48jam == 'Ya') {
                $diagnosaMap[$diagnosaKey]['meninggal_kurang_48jam']++;
            }
            
            if ($item->meninggal_lebih_48jam == 'Ya') {
                $diagnosaMap[$diagnosaKey]['meninggal_lebih_48jam']++;
            }
        }
        
        // Convert to array with index
        $result = [];
        $index = 1;
        foreach ($diagnosaMap as $key => $value) {
            $value['no'] = $index++;
            $result[] = $value;
        }
        
        return $result;
    }

    public function exportPasienMeninggalPdf(Request $request){
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $bangsal = $request->input('bangsal');
        
        // Dapatkan data yang sama dengan view
        $data = $this->getPasienMeninggalData($tanggalAwal, $tanggalAkhir, $bangsal);
        $totalData = $this->hitungTotalDiagnosa($data);
        
        $pdf = PDF::loadView('rm.laporan_rm.pasien-meninggal-pdf', [
            'data' => $data,
            'totalData' => $totalData,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
        ]);
        
        return $pdf->download('laporan-pasien-meninggal-' . $tanggalAwal . '-' . $tanggalAkhir . '.pdf');
    }

    public function exportPasienMeninggalExcel(Request $request){
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $bangsal = $request->input('bangsal');
        $fileName = 'laporan-pasien-meninggal-' . $tanggalAwal . '-' . $tanggalAkhir . '.xlsx';
        
        return Excel::download(new PasienMeninggalExport($tanggalAwal, $tanggalAkhir, $bangsal), $fileName);
    }

    protected function getPasienMeninggalData($tanggalAwal, $tanggalAkhir, $bangsal = null){
        $query = DB::select("
            SELECT tgl_registrasi, no_rawat, no_rkm_medis, jenis_pasien, nm_pasien, 
                alamat, jk, no_ktp, tgl_lahir, umurdaftar, png_jawab, nm_penyakit, 
                kd_penyakit, prioritas, kd_dokter, nm_dokter, kd_sps, nm_sps, 
                status_lanjut, kd_kamar, kelas, tgl_masuk, jam_masuk, tgl_keluar, 
                jam_keluar, stts_pulang, kd_bangsal, nm_bangsal, kd_dokter_dpjp, 
                nm_dokter_dpjp, json_dpjp
            FROM laporan_sensus_pasien_ranap t
            WHERE t.tgl_masuk >= ?
            AND t.tgl_masuk <= ?
            AND t.stts_pulang = 'Meninggal'
            " . ($bangsal ? "AND t.kd_bangsal = ?" : "") . "
            GROUP BY t.no_rawat
            ORDER BY t.tgl_masuk ASC, t.no_rkm_medis ASC, -t.prioritas DESC
        ", $bangsal ? [$tanggalAwal, $tanggalAkhir, $bangsal] : [$tanggalAwal, $tanggalAkhir]);
        
        return collect($query)->map(function ($item, $index) {
            // Hitung selisih waktu untuk menentukan meninggal < 48 jam atau >= 48 jam
            $waktuMasuk = Carbon::parse($item->tgl_masuk . ' ' . $item->jam_masuk);
            $waktuKeluar = Carbon::parse($item->tgl_keluar . ' ' . $item->jam_keluar);
            $selisihJam = $waktuMasuk->diffInHours($waktuKeluar);
            $kurangDari48Jam = $selisihJam < 48;
            
            // Hitung umur dari tanggal lahir
            $umur = null;
            if (!empty($item->tgl_lahir)) {
                try {
                    $tglLahir = Carbon::parse($item->tgl_lahir);
                    $umur = $tglLahir->age;
                } catch (\Exception $e) {
                    $umur = null;
                }
            }
            
            return (object)[
                'no' => $index + 1,
                'tgl_masuk' => $item->tgl_masuk,
                'no_rkm_medis' => $item->no_rkm_medis,
                'nm_pasien' => $item->nm_pasien,
                'jk' => $item->jk,
                'tgl_lahir' => $item->tgl_lahir,
                'umur' => $umur,
                'png_jawab' => $item->png_jawab,
                'meninggal_kurang_48jam' => ($item->stts_pulang == 'Meninggal' && $kurangDari48Jam) ? 'Ya' : '-',
                'meninggal_lebih_48jam' => ($item->stts_pulang == 'Meninggal' && !$kurangDari48Jam) ? 'Ya' : '-',
                'kd_penyakit' => $item->kd_penyakit,
                'nm_penyakit' => $item->nm_penyakit,
                'item' => $item,
            ];
        });
    }

    public function getBangsal(Request $request){
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');
        
        $data = $this->getBangsalData($tanggalAwal, $tanggalAkhir);
        
        return response()->json([
            'success' => true,
            'data' => $data['data']
        ]);
    }

    public function getBangsalMeninggal(Request $request){
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');
        
        $data = $this->getBangsalData($tanggalAwal, $tanggalAkhir, true);
        
        return response()->json([
            'success' => true,
            'data' => $data['data']
        ]);
    }
    private function getBangsalData($tanggalAwal, $tanggalAkhir, $meninggal = false){
        // Jika ada tanggal awal dan akhir, ambil bangsal berdasarkan data pasien
        if ($tanggalAwal && $tanggalAkhir) {
            
            $bangsal = DB::table('laporan_sensus_pasien_ranap as t')
                ->join('bangsal', 'bangsal.kd_bangsal', '=', 't.kd_bangsal')
                ->select('bangsal.kd_bangsal', 'bangsal.nm_bangsal')
                ->where('t.tgl_masuk', '>=', $tanggalAwal)
                ->where('t.tgl_masuk', '<=', $tanggalAkhir);
            
            if($meninggal){
                $bangsal = $bangsal->where('t.stts_pulang', '=', 'Meninggal');
            }else{
                $bangsal = $bangsal->where('t.stts_pulang', '!=', '-')
                    ->where('t.stts_pulang', '!=', 'Pindah Kamar');
            }
            $bangsal = $bangsal->groupBy('bangsal.kd_bangsal', 'bangsal.nm_bangsal')
                ->orderBy('bangsal.nm_bangsal', 'asc')
                ->get();
        } else {
            // Jika tidak ada tanggal, ambil semua bangsal aktif
            $bangsal = DB::table('bangsal')
                ->select('kd_bangsal', 'nm_bangsal')
                ->where('status', '1');
                if($meninggal){
                    $bangsal = $bangsal->where('t.stts_pulang', '=', 'Meninggal');
                }
                $bangsal = $bangsal->orderBy('nm_bangsal', 'asc')
                    ->get();
        }
        
        return [
            'data' => $bangsal
        ];
    }  



    // Laporan RUJUKAN KELUAR
    public function laporanRujukanKeluar(Request $request){
        $tanggalAwal = $request->input('tanggal_awal') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $keyword = $request->input('keyword') ?? '';

        // Base query untuk data rujukan
        $baseQuery = DB::table('rujuk')
            ->select(
                'rujuk.no_rujuk',
                'rujuk.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'rujuk.rujuk_ke',
                'rujuk.tgl_rujuk',
                'rujuk.jam',
                'rujuk.keterangan_diagnosa',
                'rujuk.kd_dokter',
                'dokter.nm_dokter',
                'rujuk.kat_rujuk',
                'rujuk.ambulance',
                'rujuk.keterangan'
            )
            ->join('reg_periksa', 'rujuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('dokter', 'rujuk.kd_dokter', '=', 'dokter.kd_dokter')
            ->whereBetween('rujuk.tgl_rujuk', [$tanggalAwal, $tanggalAkhir]);

        // Tambahkan kondisi pencarian ke baseQuery jika keyword ada
        if (!empty($keyword)) {
            $baseQuery->where(function($query) use ($keyword) {
                $query->where('rujuk.no_rujuk', 'like', "%$keyword%")
                    ->orWhere('rujuk.no_rawat', 'like', "%$keyword%")
                    ->orWhere('reg_periksa.no_rkm_medis', 'like', "%$keyword%")
                    ->orWhere('pasien.nm_pasien', 'like', "%$keyword%")
                    ->orWhere('rujuk.rujuk_ke', 'like', "%$keyword%")
                    ->orWhere('rujuk.keterangan_diagnosa', 'like', "%$keyword%")
                    ->orWhere('rujuk.kd_dokter', 'like', "%$keyword%")
                    ->orWhere('dokter.nm_dokter', 'like', "%$keyword%");
            });
        }

        // Query untuk data yang ditampilkan di tabel
        $data = clone $baseQuery;
        $data = $data->orderBy('rujuk.no_rujuk')->paginate(15);

        // Total pasien (menggunakan baseQuery yang sudah termasuk keyword jika ada)
        $totalPasien = (clone $baseQuery)->count();

        // Pasien per tanggal rujuk
        $pasienPerTanggal = (clone $baseQuery)
            ->select('tgl_rujuk', DB::raw('count(*) as total'))
            ->groupBy('tgl_rujuk')
            ->orderBy('tgl_rujuk')
            ->pluck('total', 'tgl_rujuk')
            ->toArray();

        // Pasien per tempat rujuk
        $pasienPerTempatRujuk = (clone $baseQuery)
            ->select('rujuk_ke', DB::raw('count(*) as total'))
            ->groupBy('rujuk_ke')
            ->orderByRaw('count(*) DESC')
            ->pluck('total', 'rujuk_ke')
            ->toArray();

        // Pasien per diagnosa
        $pasienPerDiagnosa = (clone $baseQuery)
            ->select('keterangan_diagnosa', DB::raw('count(*) as total'))
            ->groupBy('keterangan_diagnosa')
            ->orderByRaw('count(*) DESC')
            ->pluck('total', 'keterangan_diagnosa')
            ->toArray();

        return view('rm.laporan_rm.rujukan_keluar', compact(
            'data', 
            'tanggalAwal', 
            'tanggalAkhir', 
            'keyword', 
            'totalPasien', 
            'pasienPerTanggal', 
            'pasienPerTempatRujuk', 
            'pasienPerDiagnosa'
        ));
    }

    
    // Laporan RUJUKAN MASUK
    public function laporanRujukanMasuk(Request $request){
        // Set default dates (current month) if not provided
        $tanggalAwal = $request->input('tanggal_awal') ?? date('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? date('Y-m-d');
        $keyword = $request->input('keyword', '');

        // Build the base query
        $baseQuery = DB::table('reg_periksa')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('rujuk_masuk', 'reg_periksa.no_rawat', '=', 'rujuk_masuk.no_rawat')
            ->join('penyakit', 'penyakit.kd_penyakit', '=', 'rujuk_masuk.kd_penyakit')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->select(
                'rujuk_masuk.perujuk',
                'rujuk_masuk.alamat as alamat_perujuk',
                'rujuk_masuk.no_rujuk',
                'reg_periksa.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'reg_periksa.almt_pj',
                DB::raw("CONCAT(reg_periksa.umurdaftar,' ',reg_periksa.sttsumur) as umur"),
                'reg_periksa.tgl_registrasi',
                'rujuk_masuk.jm_perujuk',
                'rujuk_masuk.dokter_perujuk',
                'rujuk_masuk.kd_penyakit',
                'penyakit.nm_penyakit',
                'rujuk_masuk.kategori_rujuk',
                'rujuk_masuk.keterangan',
                'rujuk_masuk.no_balasan',
                'poliklinik.nm_poli'
            )
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir]);

        // Apply keyword search if provided
        if(!empty($keyword)) {
            $baseQuery->where(function($q) use ($keyword) {
                $searchTerm = '%' . $keyword . '%';
                $q->where('rujuk_masuk.perujuk', 'like', $searchTerm)
                ->orWhere('reg_periksa.no_rawat', 'like', $searchTerm)
                ->orWhere('reg_periksa.no_rkm_medis', 'like', $searchTerm)
                ->orWhere('pasien.nm_pasien', 'like', $searchTerm)
                ->orWhere('reg_periksa.almt_pj', 'like', $searchTerm)
                ->orWhere('rujuk_masuk.no_rujuk', 'like', $searchTerm)
                ->orWhere('rujuk_masuk.dokter_perujuk', 'like', $searchTerm)
                ->orWhere('penyakit.nm_penyakit', 'like', $searchTerm)
                ->orWhere('rujuk_masuk.kategori_rujuk', 'like', $searchTerm)
                ->orWhere('rujuk_masuk.keterangan', 'like', $searchTerm)
                ->orWhere('rujuk_masuk.no_balasan', 'like', $searchTerm)
                ->orWhere('rujuk_masuk.kd_penyakit', 'like', $searchTerm);
            });
        }

        // Clone the query for pagination
        $dataQuery = clone $baseQuery;
        $data = $dataQuery->orderBy('reg_periksa.tgl_registrasi', 'desc')
                        ->paginate(15);

        // For statistics - use the base query (not paginated)
        $totalPasien = $baseQuery->count();

        // Group data for statistics (using separate queries)
        $pasienPerPerujuk = DB::table('reg_periksa')
            ->join('rujuk_masuk', 'reg_periksa.no_rawat', '=', 'rujuk_masuk.no_rawat')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->when(!empty($keyword), function($q) use ($keyword, $baseQuery) {
                // Reapply the same conditions from the base query
                // This is a simplified version - you may need to adjust
                $searchTerm = '%' . $keyword . '%';
                $q->where('rujuk_masuk.perujuk', 'like', $searchTerm);
            })
            ->select('rujuk_masuk.perujuk', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('rujuk_masuk.perujuk')
            ->orderByDesc('jumlah')
            ->get()
            ->pluck('jumlah', 'perujuk')
            ->toArray();

        $pasienPerTanggal = DB::table('reg_periksa')
            ->join('rujuk_masuk', 'reg_periksa.no_rawat', '=', 'rujuk_masuk.no_rawat')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->when(!empty($keyword), function($q) use ($keyword, $baseQuery) {
                // Simplified reapplication of search conditions
                $searchTerm = '%' . $keyword . '%';
                $q->where('rujuk_masuk.perujuk', 'like', $searchTerm);
            })
            ->select('reg_periksa.tgl_registrasi', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('reg_periksa.tgl_registrasi')
            ->orderBy('reg_periksa.tgl_registrasi')
            ->get()
            ->pluck('jumlah', 'tgl_registrasi')
            ->toArray();

        $pasienPerDiagnosa = DB::table('reg_periksa')
            ->join('rujuk_masuk', 'reg_periksa.no_rawat', '=', 'rujuk_masuk.no_rawat')
            ->join('penyakit', 'penyakit.kd_penyakit', '=', 'rujuk_masuk.kd_penyakit')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->when(!empty($keyword), function($q) use ($keyword, $baseQuery) {
                // Simplified reapplication of search conditions
                $searchTerm = '%' . $keyword . '%';
                $q->where('rujuk_masuk.perujuk', 'like', $searchTerm);
            })
            ->select(
                DB::raw('CONCAT(penyakit.kd_penyakit, " - ", penyakit.nm_penyakit) as diagnosa'), 
                DB::raw('COUNT(*) as jumlah')
            )
            ->groupBy('penyakit.kd_penyakit', 'penyakit.nm_penyakit')
            ->orderByDesc('jumlah')
            ->get()
            ->pluck('jumlah', 'diagnosa')
            ->toArray();

        $pasienPerPoli = DB::table('reg_periksa')
            ->join('rujuk_masuk', 'reg_periksa.no_rawat', '=', 'rujuk_masuk.no_rawat')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->when(!empty($keyword), function($q) use ($keyword, $baseQuery) {
                // Simplified reapplication of search conditions
                $searchTerm = '%' . $keyword . '%';
                $q->where('rujuk_masuk.perujuk', 'like', $searchTerm);
            })
            ->select('poliklinik.nm_poli', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('poliklinik.nm_poli')
            ->orderByDesc('jumlah')
            ->get()
            ->pluck('jumlah', 'nm_poli')
            ->toArray();

        return view('rm.laporan_rm.rujukan_masuk', [
            'data' => $data,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'keyword' => $keyword,
            'totalPasien' => $totalPasien,
            'pasienPerPerujuk' => $pasienPerPerujuk,
            'pasienPerTanggal' => $pasienPerTanggal,
            'pasienPerDiagnosa' => $pasienPerDiagnosa,
            'pasienPerPoli' => $pasienPerPoli
        ]);
    }
    
    
    // Laporan RUJUKAN REKAP
    public function laporanRujukanRekap(Request $request){
        // Get input parameters
        $tanggalAwal = $request->input('tanggal_awal') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // Define specialization mapping with empty collections
        $spesialisasiMap = $this->getSpecializationMapping();

        // Initialize total data structure
        $totalData = [
            'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
            'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
            'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
        ];

        // Get rujukan masuk data
        $rujukanMasukData = DB::table('rujuk_masuk')
            ->join('reg_periksa', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('penyakit', 'rujuk_masuk.kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->select(
                'rujuk_masuk.no_rawat',
                'rujuk_masuk.perujuk',
                'rujuk_masuk.alamat',
                'rujuk_masuk.kategori_rujuk',
                'reg_periksa.kd_poli',
                'poliklinik.nm_poli',
                'penyakit.nm_penyakit',
                'rujuk_masuk.kd_penyakit'
            )
            ->get();

        // Process rujukan masuk data for "Diterima Dari"
        foreach ($rujukanMasukData as $data) {
            // Determine the category for diterima_dari
            $category = 'puskesmas'; // Default category

            if (preg_match('/klinik|poskes/i', $data->perujuk)) {
                $category = 'faskes_lain';

            } elseif (preg_match('/rsud/i', $data->perujuk) && !preg_match('/rsud.*kotabaru/i', $data->perujuk)) {
                $category = 'rs_lain';
            } elseif (preg_match('/poli/i', $data->perujuk) || preg_match('/rsud.*kotabaru/i', $data->perujuk)) {
                // Skip entries with "Poli" in the name
                continue;
            }
            // Determine specialization based on data
            $spesialisasi = $this->determineSpecialization($data, $spesialisasiMap);

            // Increment counter for diterima dari
            $spesialisasiMap[$spesialisasi]['data']['diterima_dari'][$category]['value']++;
            $totalData['diterima_dari'][$category]['value']++;
            $spesialisasiMap[$spesialisasi]['data']['diterima_dari']['all']['value']++;
            $totalData['diterima_dari']['all']['value']++;

            // Add kd_poli to array if not exists
            if (!in_array($data->kd_poli, $spesialisasiMap[$spesialisasi]['data']['diterima_dari'][$category]['kode_poli'])) {
                $spesialisasiMap[$spesialisasi]['data']['diterima_dari'][$category]['kode_poli'][] = $data->kd_poli;
            }

            // Add kd_poli to total data
            if (!in_array($data->kd_poli, $totalData['diterima_dari'][$category]['kode_poli'])) {
                $totalData['diterima_dari'][$category]['kode_poli'][] = $data->kd_poli;
            }
        }

        // Get rujukan keluar data dengan diagnosa dari diagnosa_pasien
        $rujukanKeluarData = DB::table('rujuk')
            ->join('reg_periksa', 'rujuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join(DB::raw('(SELECT no_rawat, MIN(prioritas) as min_prioritas FROM diagnosa_pasien GROUP BY no_rawat) as dp'), 'rujuk.no_rawat', '=', 'dp.no_rawat')
            ->join('diagnosa_pasien', function($join) {
                $join->on('rujuk.no_rawat', '=', 'diagnosa_pasien.no_rawat')
                     ->on('dp.min_prioritas', '=', 'diagnosa_pasien.prioritas');
            })
            ->leftJoin('penyakit', 'diagnosa_pasien.kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->select(
                'rujuk.no_rawat',
                'rujuk.rujuk_ke',
                'rujuk.kat_rujuk',
                'reg_periksa.kd_poli',
                'diagnosa_pasien.kd_penyakit',
                'penyakit.nm_penyakit'
            )
            ->get();

        // Match rujukan keluar with rujukan masuk to identify "dikembalikan ke"
        foreach ($rujukanKeluarData as $keluarData) {
            // Try to find matching rujukan masuk
            $matchingMasuk = $rujukanMasukData->where('no_rawat', $keluarData->no_rawat)->first();

            // Jika ada rujukan masuk sebagai perujul awal
            if ($matchingMasuk) {
                // Determine if it is a return referral (dikembalikan ke)
                if ($keluarData->rujuk_ke == $matchingMasuk->perujuk) {
                    // This is a return referral (dikembalikan ke)
                    $returnCategory = 'faskes_asal'; // Default category
                    if (preg_match('/puskesmas/i', $keluarData->rujuk_ke)) {
                        $returnCategory = 'puskesmas';
                    } elseif (preg_match('/rs/i', $keluarData->rujuk_ke)) {
                        $returnCategory = 'rs_asal';
                    }

                    // Determine specialization based on the matching masuk data
                    $spesialisasi = $this->determineSpecialization($matchingMasuk, $spesialisasiMap);

                    // Increment counter for dikembalikan ke
                    $spesialisasiMap[$spesialisasi]['data']['dikembalikan_ke'][$returnCategory]['value']++;
                    $totalData['dikembalikan_ke'][$returnCategory]['value']++;

                    // Add kd_poli to array if not exists
                    if (!in_array($keluarData->kd_poli, $spesialisasiMap[$spesialisasi]['data']['dikembalikan_ke'][$returnCategory]['kode_poli'])) {
                        $spesialisasiMap[$spesialisasi]['data']['dikembalikan_ke'][$returnCategory]['kode_poli'][] = $keluarData->kd_poli;
                    }

                    // Add kd_poli to total data
                    if (!in_array($keluarData->kd_poli, $totalData['dikembalikan_ke'][$returnCategory]['kode_poli'])) {
                        $totalData['dikembalikan_ke'][$returnCategory]['kode_poli'][] = $keluarData->kd_poli;
                    }
                } else {
                    // This is a referral to another place (dirujuk keluar)
                    
                    // Determine specialization based on the matching masuk data
                    $spesialisasi = $this->determineSpecialization($matchingMasuk, $spesialisasiMap);

                    // Increment counter for dirujuk keluar
                    $spesialisasiMap[$spesialisasi]['data']['dirujuk_keluar']['all']['value']++;
                    $totalData['dirujuk_keluar']['all']['value']++;

                    // Add kd_poli to array if not exists
                    if (!in_array($keluarData->kd_poli, $spesialisasiMap[$spesialisasi]['data']['dirujuk_keluar']['all']['kode_poli'])) {
                        $spesialisasiMap[$spesialisasi]['data']['dirujuk_keluar']['all']['kode_poli'][] = $keluarData->kd_poli;
                    }

                    // Add kd_poli to total data
                    if (!in_array($keluarData->kd_poli, $totalData['dirujuk_keluar']['all']['kode_poli'])) {
                        $totalData['dirujuk_keluar']['all']['kode_poli'][] = $keluarData->kd_poli;
                    }
                }
            } else { 
              // Jika tidak ada , maka rujukan keluar biasa  
                //if($keluarData->no_rawat != '2025/07/01/000369' && $keluarData->no_rawat != '2025/06/24/000348')
                //    throw new \Exception(json_encode($keluarData));
              // Determine specialization based on the matching 
                $spesialisasi = $this->determineSpecialization($keluarData, $spesialisasiMap);

                // Increment counter for dirujuk keluar
                $spesialisasiMap[$spesialisasi]['data']['dirujuk_keluar']['all']['value']++;
                $totalData['dirujuk_keluar']['all']['value']++;

                // Add kd_poli to array if not exists
                if (!in_array($keluarData->kd_poli, $spesialisasiMap[$spesialisasi]['data']['dirujuk_keluar']['all']['kode_poli'])) {
                    $spesialisasiMap[$spesialisasi]['data']['dirujuk_keluar']['all']['kode_poli'][] = $keluarData->kd_poli;
                }

                // Add kd_poli to total data
                if (!in_array($keluarData->kd_poli, $totalData['dirujuk_keluar']['all']['kode_poli'])) {
                    $totalData['dirujuk_keluar']['all']['kode_poli'][] = $keluarData->kd_poli;
                }
            }
        }

        // Konversi array kode_poli menjadi string
        foreach ($spesialisasiMap as &$spec) {
            foreach (['diterima_dari', 'dikembalikan_ke', 'dirujuk_keluar'] as $mainCategory) {
                foreach ($spec['data'][$mainCategory] as &$category) {
                    $category['kode_poli_str'] = implode(',', $category['kode_poli']);
                }
            }
        }

        // Lakukan hal yang sama untuk totalData
        foreach (['diterima_dari', 'dikembalikan_ke', 'dirujuk_keluar'] as $mainCategory) {
            foreach ($totalData[$mainCategory] as &$category) {
                $category['kode_poli_str'] = implode(',', $category['kode_poli']);
            }
        }

        // Convert the map to a sequential array for the view
        $spesialisasi = array_values($spesialisasiMap);

        return view('rm.laporan_rm.rujukan_rekap', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'spesialisasi' => $spesialisasi,
            'totalData' => $totalData
        ]);
    }

    /**
     * Show the detailed data for a specific rujukan category and source
     */
    public function laporanRujukanRekapDetail(Request $request){
        $category = $request->input('category'); // diterima_dari or dikembalikan_ke
        $source = $request->input('source');     // puskesmas, rs_lain, faskes_lain, etc.
        $kdPoli = $request->input('kd_poli');    // kode poli from the url parameter
        $specKey = $request->input('spec_key');  // Used to identify the specialization
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');
        
        // Get specialization info if spec_key is provided
        $spesialisasiInfo = null;
        $specName = null;
        if ($specKey) {
            $spesialisasiMap = $this->getSpecializationMapping();
            if (isset($spesialisasiMap[$specKey])) {
                $spesialisasiInfo = $spesialisasiMap[$specKey];
                $specName = $spesialisasiInfo['nama'];
            }
        }
        
        // Base query
        if ($category === 'diterima_dari') {
            // Query for "Diterima Dari" category
            $query = DB::table('rujuk_masuk')
                ->join('reg_periksa', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                ->leftJoin('penyakit', 'rujuk_masuk.kd_penyakit', '=', 'penyakit.kd_penyakit')
                ->select(
                    'reg_periksa.no_rawat',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    'pasien.jk',
                    'reg_periksa.tgl_registrasi',
                    'rujuk_masuk.perujuk',
                    'rujuk_masuk.alamat',
                    'poliklinik.nm_poli',
                    'reg_periksa.kd_poli',
                    'penyakit.nm_penyakit',
                    'rujuk_masuk.kategori_rujuk',
                    'rujuk_masuk.keterangan'
                )
                ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
                ->whereRaw("rujuk_masuk.perujuk NOT LIKE '%rsud%kotabaru%'");

            // Filter based on source
            switch ($source) {
                case 'puskesmas':
                    $query->whereRaw("rujuk_masuk.perujuk NOT LIKE '%klinik%'")
                        ->whereRaw("rujuk_masuk.perujuk NOT LIKE '%poskes%'")
                        ->whereRaw("rujuk_masuk.perujuk NOT LIKE '%rsud%'")
                        ->whereRaw("rujuk_masuk.perujuk NOT LIKE '%poli%'");
                    //throw new \Exception($query->toSql);
                    break;
                case 'rs_lain':
                    $query->whereRaw("rujuk_masuk.perujuk LIKE '%rsud%'");
                    break;
                case 'faskes_lain':
                    $query->where(function($q) {
                        $q->whereRaw("rujuk_masuk.perujuk LIKE '%klinik%'")
                        ->orWhereRaw("rujuk_masuk.perujuk LIKE '%poskes%'");
                    });
                    //throw new \Exception($query->toSql());
                    break;
            }
            
            // If spec_key is provided, we need to filter by spesialisasi
            if ($specKey) {
                // Get all data first
                $allData = clone $query;
                $allData = $allData->get();
                
                // Filter the data based on the spec_key
                $spesialisasiMap = $this->getSpecializationMapping();
                $filteredData = collect();
                
                foreach ($allData as $item) {
                    $itemSpesialisasi = $this->determineSpecialization($item, $spesialisasiMap);
                    if ($itemSpesialisasi === $specKey) {
                        $filteredData->push($item);
                    }
                }
                 
                $data = $filteredData;
            } else {
                // If spec_key is not provided but kd_poli is, filter by kd_poli
                if (!empty($kdPoli)) {
                    $poliArray = explode(',', $kdPoli);
                    $query->whereIn('reg_periksa.kd_poli', $poliArray);
                }
                
                $data = $query->get();
            }
        } else {
            // Query for "Dikembalikan Ke" category
            $query = DB::table('rujuk')
                ->join('reg_periksa', 'rujuk.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                ->leftJoin('rujuk_masuk', 'rujuk.no_rawat', '=', 'rujuk_masuk.no_rawat')
                ->leftJoin('penyakit as penyakit_rujuk', 'rujuk_masuk.kd_penyakit', '=', 'penyakit_rujuk.kd_penyakit')
                ->leftJoin('diagnosa_pasien', function($join) {
                    $join->on('diagnosa_pasien.no_rawat', '=', 'rujuk.no_rawat');
                })
                ->leftJoin('penyakit as penyakit_diagnosa', 'diagnosa_pasien.kd_penyakit', '=', 'penyakit_diagnosa.kd_penyakit')
                ->select(
                    'reg_periksa.no_rawat',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    'pasien.jk',
                    'reg_periksa.tgl_registrasi',
                    DB::raw('COALESCE(NULLIF(rujuk_masuk.perujuk, ""), "-") as perujuk_asal'),
                    'rujuk.rujuk_ke as tujuan_dirujuk',
                    'poliklinik.nm_poli',
                    'reg_periksa.kd_poli',
                    // Use COALESCE to prioritize rujuk_masuk diagnosis, fallback to diagnosa_pasien
                    DB::raw('COALESCE(NULLIF(penyakit_rujuk.nm_penyakit, ""), NULLIF(penyakit_diagnosa.nm_penyakit, ""), "-") as nm_penyakit'),
                    DB::raw('COALESCE(rujuk_masuk.kd_penyakit, diagnosa_pasien.kd_penyakit) as kd_penyakit'),
                    'rujuk_masuk.kategori_rujuk',
                    'rujuk.keterangan'
                )
                ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
                ->groupBy(
                    'reg_periksa.no_rawat',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    'pasien.jk',
                    'reg_periksa.tgl_registrasi',
                    'rujuk_masuk.perujuk',
                    'rujuk.rujuk_ke',
                    'poliklinik.nm_poli',
                    'reg_periksa.kd_poli',
                    'nm_penyakit',
                    'kd_penyakit',
                    'rujuk_masuk.kategori_rujuk',
                    'rujuk.keterangan'
                );
            
    
    // Filter based on source
    switch ($source) {
        case 'puskesmas':
            $query->whereRaw("rujuk.rujuk_ke LIKE '%puskesmas%'");
            break;
        case 'rs_asal':
            $query->whereRaw("rujuk.rujuk_ke LIKE '%rs%'");
            break;
        case 'faskes_asal':
            $query->whereRaw("rujuk.rujuk_ke NOT LIKE '%puskesmas%'")
                ->whereRaw("rujuk.rujuk_ke NOT LIKE '%rs%'");
            break;
    }

    // If spec_key is provided, we need to filter by spesialisasi
    if ($specKey) {
        // Get all data first
        $allData = clone $query;
        $allData = $allData->get();
    
        // Filter the data based on the spec_key
        $spesialisasiMap = $this->getSpecializationMapping();
        $filteredData = collect();
    
        foreach ($allData as $item) {
            // Create a masuk-like object for specialization determination
            $masukLike = (object) [
                'kategori_rujuk' => $item->kategori_rujuk,
                'kd_poli' => $item->kd_poli,
                'nm_penyakit' => $item->nm_penyakit
            ];
        
            $itemSpesialisasi = $this->determineSpecialization($masukLike, $spesialisasiMap);
            if ($itemSpesialisasi === $specKey) {
                $filteredData->push($item);
            }
        }
        
        $data = $filteredData;
    } else {
        // If spec_key is not provided but kd_poli is, filter by kd_poli
        if (!empty($kdPoli)) {
            $poliArray = explode(',', $kdPoli);
            $query->whereIn('reg_periksa.kd_poli', $poliArray);
        }
    
        $data = $query->get();
    }
        }
        
        return view('rm.laporan_rm.rujukan_rekap_detail', [
            'data' => $data,
            'category' => $category, 
            'source' => $source,
            'kd_poli' => $kdPoli,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'spesialisasiInfo' => $spesialisasiInfo,
            'specName' => $specName
        ]);
    }

    /**
     * Get the rujukan masuk data for the specified date range
     */
    private function getRujukanMasukData($tanggalAwal, $tanggalAkhir){
        return DB::table('rujuk_masuk')
            ->join('reg_periksa', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('penyakit', 'rujuk_masuk.kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->select(
                'rujuk_masuk.no_rawat',
                'rujuk_masuk.perujuk',
                'rujuk_masuk.alamat',
                'rujuk_masuk.kategori_rujuk',
                'reg_periksa.kd_poli',
                'poliklinik.nm_poli',
                'penyakit.nm_penyakit',
                'rujuk_masuk.kd_penyakit'
            )
            ->get();
    }

    /**
     * Process the rujukan keluar data for the "Dikembalikan Ke" category
     */
    private function processRujukanKeluar($tanggalAwal, $tanggalAkhir, $rujukanMasukData, &$spesialisasiMap, &$totalData){
        // Get rujukan keluar data
        $rujukanKeluarData = DB::table('rujuk')
            ->join('reg_periksa', 'rujuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->whereBetween('rujuk.tgl_rujuk', [$tanggalAwal, $tanggalAkhir])
            ->select(
                'rujuk.no_rawat',
                'rujuk.rujuk_ke',
                'rujuk.kat_rujuk',
                'reg_periksa.kd_poli'
            )
            ->get();
        
        // Match rujukan keluar with rujukan masuk to identify "dikembalikan ke"
        foreach ($rujukanKeluarData as $keluarData) {
            // Try to find matching rujukan masuk
            $matchingMasuk = $rujukanMasukData->where('no_rawat', $keluarData->no_rawat)->first();
            
            if ($matchingMasuk) {
                // This is a return referral (dikembalikan ke)
                $returnCategory = $this->determineReturnCategory($keluarData->rujuk_ke);
                $spesialisasi = $this->determineSpecialization($matchingMasuk, $spesialisasiMap);
                
                // Increment counter for dikembalikan ke
                $spesialisasiMap[$spesialisasi]['data']['dikembalikan_ke'][$returnCategory]['value']++;
                $totalData['dikembalikan_ke'][$returnCategory]['value']++;
                
                // Add kd_poli to array if not exists
                if (!in_array($keluarData->kd_poli, $spesialisasiMap[$spesialisasi]['data']['dikembalikan_ke'][$returnCategory]['kode_poli'])) {
                    $spesialisasiMap[$spesialisasi]['data']['dikembalikan_ke'][$returnCategory]['kode_poli'][] = $keluarData->kd_poli;
                }
                
                // Add kd_poli to total data
                if (!in_array($keluarData->kd_poli, $totalData['dikembalikan_ke'][$returnCategory]['kode_poli'])) {
                    $totalData['dikembalikan_ke'][$returnCategory]['kode_poli'][] = $keluarData->kd_poli;
                }
            }
        }
    }

    /**
     * Prepare the specialization data for the view by converting kode_poli arrays to strings
     */
    private function prepareSpesialisasiDataForView(&$spesialisasiMap, &$totalData){
        foreach ($spesialisasiMap as &$spec) {
            foreach (['diterima_dari', 'dikembalikan_ke'] as $mainCategory) {
                foreach ($spec['data'][$mainCategory] as &$category) {
                    $category['kode_poli_str'] = implode(',', $category['kode_poli']);
                }
            }
        }

        foreach (['diterima_dari', 'dikembalikan_ke'] as $mainCategory) {
            foreach ($totalData[$mainCategory] as &$category) {
                $category['kode_poli_str'] = implode(',', $category['kode_poli']);
            }
        }
    }

    /**
     * Determine the return category based on the rujuk_ke field
     */
    private function determineReturnCategory($rujukKe){
        if (preg_match('/puskesmas/i', $rujukKe)) {
            return 'puskesmas';
        } elseif (preg_match('/rs/i', $rujukKe)) {
            return 'rs_asal';
        } else {
            return 'faskes_asal';
        }
    }

    /**
     * Determine specialization based on referring data
     */
    private function determineSpecialization($data, $spesialisasiMap){
        // First try to match by category
        if (isset($data->kategori_rujuk) && $data->kategori_rujuk != '-' && $data->kategori_rujuk != '') {
            foreach ($spesialisasiMap as $key => $spec) {
                if (isset($spec['kategori']) && strtolower($spec['kategori']) == strtolower($data->kategori_rujuk)) {
                    return $key;
                }
            }
        }
        
        // Handle special case for Saraf (K11) - check if it's stroke-related first
        if (isset($data->kd_poli) && $data->kd_poli == 'K11') {
            // Check if diagnosis or other fields contain stroke indicators
            if (isset($data->nm_penyakit) && $data->nm_penyakit && 
                (stripos($data->nm_penyakit, 'stroke') !== false)) {
                return 'saraf_stroke';
            }
            
            // If not stroke-related, categorize as non-stroke saraf
            return 'saraf_non_stroke';
        }
        
        // Then try to match by poli
        if (isset($data->kd_poli) && $data->kd_poli) {
            foreach ($spesialisasiMap as $key => $spec) {
                if (isset($spec['kd_poli']) && in_array($data->kd_poli, $spec['kd_poli'])) {
                    return $key;
                }
            }
        }
        
        // Try to match by diagnosis pattern
        if (isset($data->nm_penyakit) && $data->nm_penyakit) {
            $diagnosis = strtolower($data->nm_penyakit);
            
            // Check specifically for stroke pattern first to prioritize stroke classification
            if (stripos($diagnosis, 'stroke') !== false) {
                return 'saraf_stroke';
            }
            
            // Then check other patterns
            foreach ($spesialisasiMap as $key => $spec) {
                if (isset($spec['pattern'])) {
                    foreach ($spec['pattern'] as $pattern) {
                        if (stripos($diagnosis, strtolower($pattern)) !== false) {
                            return $key;
                        }
                    }
                }
            }
        }
        
        // Default to other specialization if no match
        return 'spesialisasi_lain';
    }

    /**
     * Fetch detailed rujukan data based on category, source, and specialization
     */
    private function fetchRujukanDetailData($category, $source, $specKey, $tanggalAwal, $tanggalAkhir){
        // Get specialization info and poli codes if specKey is provided
        $poliCodes = [];
        if ($specKey) {
            $spesialisasiMap = $this->getSpecializationMapping();
            if (isset($spesialisasiMap[$specKey]['kd_poli'])) {
                $poliCodes = $spesialisasiMap[$specKey]['kd_poli'];
            }
        }

        if ($category === 'diterima_dari') {
            // Query for "Diterima Dari" category
            $query = DB::table('rujuk_masuk')
                ->join('reg_periksa', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                ->leftJoin('penyakit', 'rujuk_masuk.kd_penyakit', '=', 'penyakit.kd_penyakit')
                ->select(
                    'reg_periksa.no_rawat',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    'pasien.jk',
                    'reg_periksa.tgl_registrasi',
                    'rujuk_masuk.perujuk',
                    'rujuk_masuk.alamat',
                    'poliklinik.nm_poli',
                    'reg_periksa.kd_poli',
                    'penyakit.nm_penyakit',
                    'rujuk_masuk.kategori_rujuk',
                    'rujuk_masuk.keterangan'
                )
                ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir]);

            // Add source filter
            switch ($source) {
                case 'puskesmas':
                    $query->whereRaw("rujuk_masuk.perujuk NOT LIKE '%klinik%'")
                        ->whereRaw("rujuk_masuk.perujuk NOT LIKE '%poskes%'")
                        ->whereRaw("(rujuk_masuk.perujuk NOT LIKE '%rsud%' OR rujuk_masuk.perujuk LIKE '%rsud%kotabaru%')")
                        ->whereRaw("rujuk_masuk.perujuk NOT LIKE '%poli%'");
                    break;
                case 'rs_lain':
                    $query->whereRaw("rujuk_masuk.perujuk LIKE '%rsud%'")
                        ->whereRaw("rujuk_masuk.perujuk NOT LIKE '%rsud%kotabaru%'");
                    break;
                case 'faskes_lain':
                    $query->where(function($q) {
                        $q->whereRaw("rujuk_masuk.perujuk LIKE '%klinik%'")
                        ->orWhereRaw("rujuk_masuk.perujuk LIKE '%poskes%'");
                    });
                    break;
            }

            // Add poli filter if specific specialization is provided
            if (!empty($poliCodes)) {
                $query->whereIn('reg_periksa.kd_poli', $poliCodes);
            }
        } else {
            // Query for "Dikembalikan Ke" category
            $query = DB::table('rujuk')
                ->join('reg_periksa', 'rujuk.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->join('rujuk_masuk', 'rujuk.no_rawat', '=', 'rujuk_masuk.no_rawat')
                ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                ->leftJoin('penyakit', 'rujuk_masuk.kd_penyakit', '=', 'penyakit.kd_penyakit')
                ->select(
                    'reg_periksa.no_rawat',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    'pasien.jk',
                    'reg_periksa.tgl_registrasi',
                    'rujuk_masuk.perujuk as perujuk_asal',
                    'rujuk.rujuk_ke as tujuan_dirujuk',
                    'poliklinik.nm_poli',
                    'reg_periksa.kd_poli',
                    'penyakit.nm_penyakit',
                    'rujuk_masuk.kategori_rujuk',
                    'rujuk.keterangan'
                )
                ->whereBetween('rujuk.tgl_rujuk', [$tanggalAwal, $tanggalAkhir]);
                
            // Add source filter
            switch ($source) {
                case 'puskesmas':
                    $query->whereRaw("rujuk.rujuk_ke LIKE '%puskesmas%'");
                    break;
                case 'rs_asal':
                    $query->whereRaw("rujuk.rujuk_ke LIKE '%rs%'");
                    break;
                case 'faskes_asal':
                    $query->whereRaw("rujuk.rujuk_ke NOT LIKE '%puskesmas%'")
                        ->whereRaw("rujuk.rujuk_ke NOT LIKE '%rs%'");
                    break;
            }
            
            // Add poli filter if specific specialization is provided
            if (!empty($poliCodes)) {
                $query->whereIn('reg_periksa.kd_poli', $poliCodes);
            }
        }
        
        return $query->get();
    }

    /**
     * Get the complete specialization mapping with default structure
     */
    private function getSpecializationMapping(){
        return [
            'penyakit_dalam' => [
                'key' => 'penyakit_dalam',
                'nama' => 'Penyakit Dalam',
                'kd_poli' => ['INT', 'PDL', 'K9', 'K10'], // Kode poli untuk penyakit dalam
                'pattern' => ['diabetes', 'hipertensi', 'jantung', 'gastritis'], // Pola kata dalam diagnosa
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'bedah' => [
                'key' => 'bedah',
                'nama' => 'Bedah',
                'kd_poli' => ['BED', 'BDH', 'K1', 'K18', 'K20'], // Kode poli untuk bedah
                'kategori' => 'Bedah', // Kategori rujukan
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'kesehatan_anak' => [
                'key' => 'kesehatan_anak',
                'nama' => 'Kesehatan Anak',
                'kd_poli' => ['ANK', 'KSA', 'K0'],
                'kategori' => 'Anak',
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            //'kesehatan_remaja' => [
            //    'key' => 'kesehatan_remaja',
            //    'nama' => 'Kesehatan Remaja',
            //    'kd_poli' => ['REM', 'KSR'],
            //    'data' => [
            //        'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
            //        'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]]
            //    ]
            //],
            'obstetri' => [
                 'key' => 'obstetri',
                 'nama' => 'Obstetri',
                 'kd_poli' => ['OBS'],
                 'kategori' => 'Kebidanan',
                 'pattern' => ['kehamilan', 'melahirkan', 'hamil', 'persalinan', 'ante natal', 'antenatal', 'post natal', 'postnatal', 'obstetri', 'pregnancy', 'delivery', 'birth', 'labor', 'delivery', 'prenatal', 'postpartum', 'obstetric', 'maternal', 'gravida', 'gestational', 'antepartum', 'intrapartum', 'placenta praevia', 'oligohydramnios'],
                 'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
             ],
            'ginekologi' => [
                 'key' => 'ginekologi',
                 'nama' => 'Ginekologi',
                 'kd_poli' => ['GYN', 'KDK'],
                 'kategori' => 'Kandungan',
                 'pattern' => ['kandungan', 'ginekologi', 'kista', 'mioma', 'endometriosis', 'menstruasi', 'haid', 'keputihan', 'tumor ovarium', 'gynecology', 'gynecological', 'ovarian cyst', 'cyst ovarium', 'myoma', 'fibroids', 'menstruation', 'menstrual', 'ovarian', 'uterine', 'vaginal', 'pelvic', 'dysmenorrhea', 'amenorrhea', 'menorrhagia', 'ovary', 'uterus', 'cervix uteri', 'cervical cancer', 'cervical tumor', 'contact bleeding', 'postcoital', 'classical hydatidiform mole', 'pre-eclampsia', 'bartholin'],
                 'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
             ],
            'keluarga_berencana' => [
                'key' => 'keluarga_berencana',
                'nama' => 'Keluarga Berencana',
                'kd_poli' => ['KBR'],
                'kategori' => 'KB',
                'pattern' => ['kb', 'keluarga berencana', 'kontrasepsi', 'spiral', 'iud', 'pil kb', 'suntik kb', 'vasektomi', 'tubektomi', 'family planning', 'contraception', 'contraceptive', 'birth control', 'iud', 'contraceptive implant', 'sterilization', 'vasectomy', 'tubal ligation', 'hormonal', 'barrier method', 'condom', 'pill', 'injection', 'depo'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'saraf_non_stroke' => [
                'key' => 'saraf_non_stroke',
                'nama' => 'Saraf (Non Stroke)',
                'kd_poli' => ['SAR', 'NFL', 'K11'],
                'pattern' => ['saraf', 'neurologis'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'jiwa' => [
                'key' => 'jiwa',
                'nama' => 'Jiwa',
                'kd_poli' => ['JIW', 'PSK', 'K17'],
                'pattern' => ['jiwa', 'psikiatri', 'depresi'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'tht' => [
                'key' => 'tht',
                'nama' => 'THT',
                'kd_poli' => ['THT', 'K7'],
                'pattern' => ['telinga', 'hidung', 'tenggorokan'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'mata' => [
                'key' => 'mata',
                'nama' => 'Mata',
                'kd_poli' => ['MAT', 'K6'],
                'pattern' => ['mata', 'katarak'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            //'kulit_kelamin' => [
            //    'key' => 'kulit_kelamin',
            //    'nama' => 'Kulit dan Kelamin',
            //    'kd_poli' => ['KLT', 'KKL'],
            //    'pattern' => ['kulit', 'dermatitis'],
            //    'data' => [
            //        'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
            //        'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]]
            //    ]
            //],
            'gigi_mulut' => [
                'key' => 'gigi_mulut',
                'nama' => 'Gigi dan Mulut',
                'kd_poli' => ['GIG', 'GGM', 'K2', 'K3'],
                'pattern' => ['gigi', 'mulut'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'radiologi' => [
                'key' => 'radiologi',
                'nama' => 'Radiologi',
                'kd_poli' => ['RAD'],
                'pattern' => ['radiologi', 'rontgen'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'paru' => [
                'key' => 'paru',
                'nama' => 'Paru',
                'kd_poli' => ['PAR', 'PRM', 'K13', 'K8'],
                'pattern' => ['paru', 'pneumonia', 'bronchitis'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            //'kardiologi' => [
            //    'key' => 'kardiologi',
            //    'nama' => 'Kardiologi',
            //    'kd_poli' => ['KAR', 'JNT'],
            //    'pattern' => ['jantung', 'kardiologi'],
            //    'data' => [
            //        'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
            //        'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]]
            //    ]
            //],
            //'kanker' => [
            //    'key' => 'kanker',
            //    'nama' => 'Kanker',
            //    'kd_poli' => ['KNK', 'ONK'],
            //    'pattern' => ['kanker', 'tumor', 'onkologi'],
            //    'data' => [
            //        'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
            //        'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]]
            //    ]
            //],
            'uronefrologi' => [
                'key' => 'uronefrologi',
                'nama' => 'Uronefrologi',
                'kd_poli' => ['URO', 'GJL'],
                'pattern' => ['ginjal', 'urin', 'kencing', 'cystitis'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],         
            'saraf_stroke' => [
                'key' => 'saraf_stroke',
                'nama' => 'Saraf (Stroke)',
                'kd_poli' => ['STR', 'K11'],
                'pattern' => ['stroke'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'spesialisasi_lain' => [
                'key' => 'spesialisasi_lain',
                'nama' => 'Spesialisasi Lain',
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
        ];
    }


    // Morbiditas Rawat Jalan
    public function morbiditasRawatJalan(Request $request){
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->format('Y-m-d'));
        $tanggalAkhir = $request->input('tanggal_akhir', now()->endOfMonth()->format('Y-m-d'));

        $data = DB::table('reg_periksa as rp')
            ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
            ->join('diagnosa_pasien as dp', 'rp.no_rawat', '=', 'dp.no_rawat')
            ->join('penyakit as py', 'dp.kd_penyakit', '=', 'py.kd_penyakit')
            ->where('rp.status_lanjut', '=', 'Ralan')
            ->where('dp.status_penyakit', 'Baru')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->select(
                'py.kd_penyakit',
                'py.nm_penyakit',
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(HOUR, p.tgl_lahir, rp.tgl_registrasi) < 1 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as kurang_1hr_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(HOUR, p.tgl_lahir, rp.tgl_registrasi) < 1 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as kurang_1hr_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(DAY, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 1 AND 23 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_1_23hr_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(DAY, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 1 AND 23 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_1_23hr_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(MONTH, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 8 AND 28 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_8_28hr_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(MONTH, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 8 AND 28 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_8_28hr_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(MONTH, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 2 AND 3 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_2_3bln_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(MONTH, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 2 AND 3 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_2_3bln_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(MONTH, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 3 AND 6 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_3_6bln_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(MONTH, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 3 AND 6 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_3_6bln_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(MONTH, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 6 AND 11 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_6_11bln_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(MONTH, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 6 AND 11 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_6_11bln_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 1 AND 4 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_1_4th_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 1 AND 4 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_1_4th_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 5 AND 9 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_5_9_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 5 AND 9 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_5_9_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 10 AND 14 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_10_14_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 10 AND 14 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_10_14_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 15 AND 19 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_15_19_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 15 AND 19 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_15_19_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 20 AND 24 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_20_24_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 20 AND 24 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_20_24_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 25 AND 29 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_25_29_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 25 AND 29 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_25_29_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 30 AND 34 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_30_34_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 30 AND 34 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_30_34_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 35 AND 39 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_35_39_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 35 AND 39 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_35_39_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 40 AND 44 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_40_44_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 40 AND 44 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_40_44_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 45 AND 49 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_45_49_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 45 AND 49 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_45_49_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 50 AND 54 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_50_54_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 50 AND 54 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_50_54_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 55 AND 59 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_55_59_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 55 AND 59 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_55_59_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 60 AND 64 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_60_64_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 60 AND 64 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_60_64_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 65 AND 69 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_65_69_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 65 AND 69 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_65_69_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 70 AND 74 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_70_74_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 70 AND 74 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_70_74_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 75 AND 79 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_75_79_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 75 AND 79 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_75_79_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 80 AND 84 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as age_80_84_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) BETWEEN 80 AND 84 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as age_80_84_P'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) >= 85 AND p.jk = "L" THEN 1 ELSE 0 END), 0), "-") as lebih_85_L'),
                DB::raw('COALESCE(NULLIF(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, rp.tgl_registrasi) >= 85 AND p.jk = "P" THEN 1 ELSE 0 END), 0), "-") as lebih_85_P'),
                DB::raw('COALESCE(NULLIF(COUNT(CASE WHEN p.jk = "L" THEN 1 END), 0), "-") as total_L'),
                DB::raw('COALESCE(NULLIF(COUNT(CASE WHEN p.jk = "P" THEN 1 END), 0), "-") as total_P'),
                DB::raw('COALESCE(NULLIF(COUNT(*), 0), "-") as total_kasus_baru'),
                DB::raw('COALESCE(NULLIF(COUNT(DISTINCT CASE WHEN p.jk = "L" THEN rp.no_rawat END), 0), "-") as kunjungan_L'),
                DB::raw('COALESCE(NULLIF(COUNT(DISTINCT CASE WHEN p.jk = "P" THEN rp.no_rawat END), 0), "-") as kunjungan_P'),
                DB::raw('COALESCE(NULLIF(COUNT(DISTINCT rp.no_rawat), 0), "-") as total_kunjungan')
            )
            ->groupBy('py.kd_penyakit', 'py.nm_penyakit')
            ->get();

        return view('rm.laporan_rm.morbiditas_rawat_jalan', compact('data', 'tanggalAwal', 'tanggalAkhir'));
    }
}

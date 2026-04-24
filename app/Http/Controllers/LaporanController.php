<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use App\Exports\PasienMeninggalExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class LaporanController extends Controller
{
    /**
     * Helper: Return partial view for AJAX or master view for full page.
     */
    private function laporanView(Request $request, $viewKey, array $data, $tabKey = null)
    {
        if ($request->ajax()) {
            return view('rm.laporan_rm.partials.' . $viewKey, $data);
        }
        $data['activeTab'] = $tabKey ?? $viewKey;
        return view('rm.laporan_rm.master', $data);
    }

    /**
     * Master entry point for Laporan Rekam Medis
     */
    public function laporanRmIndex()
    {
        // Default: redirect to kelengkapan with master layout
        return redirect()->route('kelengkapan');
    }

    public function getPersalinanDetail($encoded)
    {
        try {
            $no_rawat = base64_decode($encoded);
            $tanggal = request()->query('tanggal');
            $jam = request()->query('jam');

            // Ambil data persalinan beserta nama pegawai
            $persalinan = DB::table('catatan_persalinan as cp')
                ->leftJoin('pegawai as pg', 'cp.nip', '=', 'pg.nik') // atau sesuaikan jika bukan 'nik'
                ->select('cp.*', 'pg.nama as nama_petugas')
                ->where('cp.no_rawat', $no_rawat)
                ->whereDate('cp.mulai', $tanggal)
                ->whereTime('cp.mulai', $jam)
                ->first();

            $kebidanan = DB::table('catatan_observasi_ranap_kebidanan')
                ->leftJoin('pegawai', 'catatan_observasi_ranap_kebidanan.nip', '=', 'pegawai.nik')
                ->leftJoin('reg_periksa', 'catatan_observasi_ranap_kebidanan.no_rawat', '=', 'reg_periksa.no_rawat')
                ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
                ->select(
                    'catatan_observasi_ranap_kebidanan.*',
                    'pegawai.nama as nama_petugas',
                    'dokter.nm_dokter'
                )
                ->where('catatan_observasi_ranap_kebidanan.no_rawat', $no_rawat)
                ->whereDate('catatan_observasi_ranap_kebidanan.tgl_perawatan', $tanggal) // ✅ filter tanggal saja
                ->orderBy('catatan_observasi_ranap_kebidanan.jam_rawat')
                ->get();

            // ❗ Jika dua-duanya kosong, kirim pesan
            if (!$persalinan && $kebidanan->isEmpty()) {
                return response()->json([
                    'message' => 'Data tidak ada atau belum diisi untuk ruangan ini.',
                    'persalinan' => null,
                    'kebidanan' => [],
                ]);
            }

            // ✅ Kalau salah satu atau dua-duanya ada, kirim semuanya
            return response()->json([
                'persalinan' => $persalinan,
                'kebidanan' => $kebidanan,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function laporanPersalinan(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $keyword = $request->input('keyword') ?? '';

        $gabunganQuery = DB::table(function ($query) use ($tanggalAwal, $tanggalAkhir, $keyword) {
            $q1 = DB::table('catatan_persalinan as cp')
                ->join('reg_periksa as rp', 'cp.no_rawat', '=', 'rp.no_rawat')
                ->join('pasien as ps',     'rp.no_rkm_medis', '=', 'ps.no_rkm_medis')
                ->join('dokter as d',       'rp.kd_dokter',   '=', 'd.kd_dokter')   // ← join dokter
                ->select(
                    'cp.no_rawat',
                    'rp.no_rkm_medis',
                    'ps.nm_pasien',
                    'cp.mulai as tanggal',
                    'd.nm_dokter'                              // ← select nama dokter
                )
                ->whereBetween('cp.mulai', [$tanggalAwal, $tanggalAkhir]);

            $q2 = DB::table('catatan_observasi_ranap_kebidanan as ko')
                ->join('reg_periksa as rp', 'ko.no_rawat', '=', 'rp.no_rawat')
                ->join('pasien as ps',     'rp.no_rkm_medis', '=', 'ps.no_rkm_medis')
                ->join('dokter as d',       'rp.kd_dokter',   '=', 'd.kd_dokter')   // ← join dokter
                ->select(
                    'ko.no_rawat',
                    'rp.no_rkm_medis',
                    'ps.nm_pasien',
                    'ko.tgl_perawatan as tanggal',
                    'd.nm_dokter'                              // ← select nama dokter
                )
                ->whereBetween('ko.tgl_perawatan', [$tanggalAwal, $tanggalAkhir]);

            if (!empty($keyword)) {
                $q1->where(function ($q) use ($keyword) {
                    $q->where('cp.no_rawat', 'like', "%$keyword%")
                        ->orWhere('rp.no_rkm_medis', 'like', "%$keyword%")
                        ->orWhere('ps.nm_pasien', 'like', "%$keyword%");
                });
                $q2->where(function ($q) use ($keyword) {
                    $q->where('ko.no_rawat', 'like', "%$keyword%")
                        ->orWhere('rp.no_rkm_medis', 'like', "%$keyword%")
                        ->orWhere('ps.nm_pasien', 'like', "%$keyword%");
                });
            }

            $query->fromSub($q1->union($q2), 'gabungan');
        }, 'gabungan')
            ->orderBy('tanggal', 'desc')
            ->groupBy('no_rawat', 'no_rkm_medis', 'nm_pasien', 'tanggal', 'nm_dokter')  // ← tambahkan nm_dokter
            ->paginate(15);

        return view('rm.laporan_rm.laporan_persalinan', [
            'data'         => $gabunganQuery,
            'tanggalAwal'  => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'keyword'      => $keyword,
        ]);
    }

    // Kelengkapan RM
    public function kelengkapanrm(Request $request)
    {
        //format tanggal
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }

        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }

        if (!empty($tgl1Input) && !empty($tgl2Input)) {
            $tanggal = $tgl1->format('d F Y') . ' S/D ' . $tgl2->format('d F Y');
        } else {
            $startDate = new \DateTime('first day of this month');
            $endDate = new \DateTime('today');
            $tanggal = 'Tanggal ' . $startDate->format('d F Y') . ' S/D ' . $endDate->format('d F Y');
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');

        // Untuk summary cards - ambil semua data
        $sqlnr = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join(DB::raw("(
                SELECT no_rawat, kd_kamar, tgl_keluar, stts_pulang
                FROM (
                    SELECT 
                        no_rawat, 
                        kd_kamar,
                        tgl_keluar,
                        stts_pulang,
                        ROW_NUMBER() OVER (
                            PARTITION BY no_rawat 
                            ORDER BY tgl_keluar DESC, jam_keluar DESC
                        ) AS rn
                    FROM kamar_inap
                    WHERE stts_pulang != 'Pindah Kamar'
                ) AS ranked_ki
                WHERE rn = 1
            ) as ki"), 'a.no_rawat', '=', 'ki.no_rawat')
            ->leftJoin('kelengkapan_rm as krm', 'a.no_rawat', '=', 'krm.no_rawat')
            ->whereBetween('a.tgl_registrasi', [$formattedTgl1, $formattedTgl2])
            ->where('a.status_lanjut', '=', 'Ranap')
            ->get();

        $totalData = $sqlnr->count();
        $terverifikasi = $sqlnr->where('verif_all', 1)->count();
        $belumVerifikasi = $totalData - $terverifikasi;

        // HAPUS LOGIKA PERHITUNGAN BERKAS LENGKAP/TIDAK LENGKAP DISINI
        // Kita hanya mengandalkan status verif_all

        return $this->laporanView($request, 'kelengkapan', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,
            'tgllap' => $tanggal,
            'totalData' => $totalData,
            'terverifikasi' => $terverifikasi,
            'belumVerifikasi' => $belumVerifikasi,
            // 'berkasLengkap' dan 'berkasTidakLengkap' dihapus dari return
        ], 'kelengkapan');
    }

    //ambil NO RAWAT pasien
    public function getModalContent(Request $request)
    {
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join('kamar_inap as ki', 'a.no_rawat', '=', 'ki.no_rawat')
            ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
            ->where('a.no_rawat', '=', $id)
            ->where('ki.stts_pulang', '!=', 'Pindah Kamar')
            ->orderBy('ki.tgl_keluar', 'desc')
            ->orderBy('ki.jam_keluar', 'desc')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Cek apakah pasien operasi (Untuk keperluan tampilan modal saja)
        $isOperasi = DB::table('laporan_operasi')->where('no_rawat', $id)->exists() ||
            DB::table('laporan_operasi_2')->where('no_rawat', $id)->exists() ||
            DB::table('laporan_operasi_3')->where('no_rawat', $id)->exists() ||
            DB::table('laporan_operasi_4')->where('no_rawat', $id)->exists();

        // Ambil data kelengkapan jika sudah ada
        $kelengkapan = DB::table('kelengkapan_rm')->where('no_rawat', $id)->first();
        $loggedInUserNip = session()->get('nik');

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
            'verif_tatatertib_icu' => ['label' => 'Tata Tertib ICU', 'route' => 'erm_tatatertib'],
            'verif_persetujuan_icu' => ['label' => 'Persetujuan ICU/PICU', 'route' => 'erm_persetujuanicu'],
            'verif_berkas_digital' => ['label' => 'Berkas Digital', 'route' => 'erm_ranap_berkas_digital'],
        ];

        // Berkas khusus operasi
        $listOperasi = [
            'verif_informed_consent_anastesi' => ['label' => 'Informed Consent', 'route' => $isOperasi ? 'erm_ranap_icta' : '#'],
            'verif_penandaan_op' => ['label' => 'Penandaan Operasi Pria / Perempuan', 'route' => $isOperasi ? 'erm_penandaanop' : '#'],
            'verif_serah_terima_pasien_op' => ['label' => 'Checklist Serah Terima Pasien Pre Operatif', 'route' => $isOperasi ? 'erm_checklistpreop' : '#'],
            'verif_penilaian_pra_anastesi' => ['label' => 'Penilaian Pra Anastesi', 'route' => $isOperasi ? 'erm_penilaianprean' : '#'],
            'verif_praop' => ['label' => 'Penilaian Pra Operasi', 'route' => $isOperasi ? 'erm_ranap_pra_op' : '#'],
            'verif_pra_sedasi' => ['label' => 'Penilaian Pra Sedasi', 'route' => $isOperasi ? 'erm_ranap_pra_sedasi' : '#'],
            'verif_laporanop' => ['label' => 'Laporan Operasi 1', 'route' => $isOperasi ? 'erm_ranap_laporan_op' : '#'],
            'verif_laporanop2' => ['label' => 'Laporan Operasi 2', 'route' => $isOperasi ? 'erm_ranap_laporan_op2' : '#'],
            'verif_laporanop3' => ['label' => 'Laporan Operasi 3', 'route' => $isOperasi ? 'erm_ranap_laporan_op3' : '#'],
            'verif_laporanop4' => ['label' => 'Laporan Operasi 4', 'route' => $isOperasi ? 'erm_ranap_laporan_op4' : '#'],
            'verif_anamnese_anestesi' => ['label' => 'Laporan Anestesi / Anamnese Anestesi', 'route' => $isOperasi ? 'erm_ranap_anamnese_anestesi' : '#'],
            'verif_laporan_sedasi' => ['label' => 'Laporan Sedasi', 'route' => $isOperasi ? 'erm_ranap_laporan_sedasi' : '#'],
            'verif_inventaris_kasa' => ['label' => 'Sign Out Sebelum Menutup Luka / Inventaris Kasa', 'route' => $isOperasi ? 'erm_signoutsebelummenutupluka' : '#'],
        ];

        return view('rm.laporan_rm.modal-content', [
            'data' => $data,
            'kelengkapan' => $kelengkapan,
            'list' => $list,
            'listOperasi' => $listOperasi,
            'isOperasi' => $isOperasi,
            'loggedInUserNip' => $loggedInUserNip
        ]);
    }

    public function simpanKelengkapan(Request $request)
    {
        // === CASE: hanya update status verif_all override dari tombol Verifikasi/Batal ===
        if ($request->filled('no_rawat') && $request->exists('verif_all_override')) {
            $status = $request->input('verif_all_override') ? 1 : 0;
            $noRawat = $request->no_rawat;

            // Validasi petugas untuk AJAX request
            $nip = session()->get('nik');
            $cekPetugas = DB::table('petugas')->where('nip', $nip)->exists();

            if (!$cekPetugas) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak valid sebagai petugas.'
                ], 403);
            }

            // HAPUS LOGIKA PENGECEKAN KELENGKAPAN BERKAS (isLengkap)
            // Verifikasi sekarang bersifat manual/bebas tanpa validasi kelengkapan item

            DB::table('kelengkapan_rm')->updateOrInsert(
                ['no_rawat' => $request->no_rawat],
                ['verif_all' => $status, 'time_stamp' => now(), 'nip' => $nip]
            );

            // Kembalikan status sukses tanpa flag is_lengkap
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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak valid sebagai petugas.'
                ], 403);
            }
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
            'verif_laporanop2',
            'verif_laporanop3',
            'verif_laporanop4',
            'verif_berkas_digital',
            'verif_inventaris_kasa',
            'verif_anamnese_anestesi',
            'verif_laporan_sedasi',
            'verif_tatatertib_icu',
            'verif_persetujuan_icu',
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

    // Lanjutan dari Kelengkapan
    public function kelengkapanJson(Request $request)
    {
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');
        $bangsalFilter = $request->input('bangsal');

        // Format tanggal sama seperti method kelengkapanrm
        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }

        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');

        // Query data dengan filter bangsal
        $query = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->join(DB::raw("(
                SELECT no_rawat, kd_kamar, tgl_keluar, stts_pulang
                FROM (
                    SELECT 
                        no_rawat, 
                        kd_kamar,
                        tgl_keluar,
                        stts_pulang,
                        ROW_NUMBER() OVER (
                            PARTITION BY no_rawat 
                            ORDER BY tgl_keluar DESC, jam_keluar DESC
                        ) AS rn
                    FROM kamar_inap
                    WHERE stts_pulang != 'Pindah Kamar'
                ) AS ranked_ki
                WHERE rn = 1
            ) as ki"), 'a.no_rawat', '=', 'ki.no_rawat')
            ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
            ->join('bangsal as bang', 'k.kd_bangsal', '=', 'bang.kd_bangsal')
            ->leftJoin('kelengkapan_rm as krm', 'a.no_rawat', '=', 'krm.no_rawat')
            ->whereBetween('a.tgl_registrasi', [$formattedTgl1, $formattedTgl2])
            ->where('a.status_lanjut', '=', 'Ranap');

        // Tambahkan filter bangsal jika ada
        if (!empty($bangsalFilter) && $bangsalFilter !== 'semua') {
            $query->where('bang.kd_bangsal', $bangsalFilter);
        }

        $sqlnr = $query->orderBy('a.no_rawat', 'desc')
            ->select(
                'a.no_rawat',
                'a.no_rkm_medis',
                'b.nm_pasien',
                'a.status_lanjut',
                'bang.nm_bangsal',
                'bang.kd_bangsal',
                'krm.verif_all',
                'ki.stts_pulang',
                'ki.tgl_keluar'
            )
            ->get();

        // HAPUS LOGIKA PERHITUNGAN is_lengkap
        // Kita kirimkan data mentah saja, frontend akan menghitung summary berdasarkan verif_all

        return response()->json([
            'data' => $sqlnr
        ]);
    }

    public function getBangsalRanapOptions(Request $request)
    {
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        // Set default jika tanggal kosong
        $tgl1 = !empty($tgl1Input) ? $tgl1Input : date('Y-m-01');
        $tgl2 = !empty($tgl2Input) ? $tgl2Input : date('Y-m-d');

        // Query untuk mengambil bangsal yang memiliki pasien rawat inap dalam rentang tanggal
        $bangsal = DB::table('kamar_inap as ki')
            ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
            ->join('bangsal as b', 'k.kd_bangsal', '=', 'b.kd_bangsal')
            ->whereBetween(DB::raw('DATE(ki.tgl_masuk)'), [$tgl1, $tgl2])
            ->where('b.status', '1')
            ->select('b.kd_bangsal', 'b.nm_bangsal')
            ->distinct()
            ->orderBy('b.nm_bangsal')
            ->get();

        return response()->json($bangsal);
    }

    public function exportKelengkapanExcel(Request $request)
    {
        try {
            $request->validate([
                'tgl1' => 'required|date',
                'tgl2' => 'required|date|after_or_equal:tgl1',
            ]);

            $tgl1 = $request->input('tgl1');
            $tgl2 = $request->input('tgl2');
            $bangsalFilter = $request->input('bangsal');

            $fileName = 'Kelengkapan_RM_' . $tgl1 . '_sampai_' . $tgl2 . '.xlsx';

            // Query
            $query = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join(DB::raw("(
                    SELECT no_rawat, kd_kamar, tgl_keluar, stts_pulang
                    FROM (
                        SELECT no_rawat, kd_kamar, tgl_keluar, stts_pulang,
                               ROW_NUMBER() OVER (PARTITION BY no_rawat ORDER BY tgl_keluar DESC, jam_keluar DESC) AS rn
                        FROM kamar_inap
                        WHERE stts_pulang != 'Pindah Kamar'
                    ) AS ranked_ki
                    WHERE rn = 1
                ) as ki"), 'a.no_rawat', '=', 'ki.no_rawat')
                ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
                ->join('bangsal as bang', 'k.kd_bangsal', '=', 'bang.kd_bangsal')
                ->leftJoin('kelengkapan_rm as krm', 'a.no_rawat', '=', 'krm.no_rawat')
                ->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2])
                ->where('a.status_lanjut', '=', 'Ranap');

            if (!empty($bangsalFilter) && $bangsalFilter !== 'semua') {
                $query->where('bang.kd_bangsal', $bangsalFilter);
            }

            $data = $query->orderBy('a.no_rawat', 'desc')
                ->select('b.nm_pasien', 'krm.*', 'a.no_rkm_medis')
                ->get();

            if ($data->isEmpty()) {
                return redirect()->back()->with('warning', 'Tidak ada data untuk diekspor pada periode yang dipilih.');
            }

            if (ob_get_length()) ob_end_clean();

            return $this->generateKelengkapanExcel($data, $fileName, $tgl1, $tgl2, $bangsalFilter);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat membuat file Excel: ' . $e->getMessage());
        }
    }

    private function generateKelengkapanExcel($data, $fileName, $tgl1, $tgl2, $bangsalFilter)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);

        // Daftar field kelengkapan
        $checklistFields = [
            'verif_sep' => 'SEP BPJS',
            'verif_resume' => 'Resume Medis',
            'verif_general_consent' => 'General Consent',
            'verif_ews' => 'EWS',
            'verif_partograf' => 'Partograf',
            'verif_asesmen_awal_medis' => 'A. Awal Medis',
            'verif_rekonsiliasi_obat' => 'Rekonsiliasi Obat',
            'verif_cppt' => 'CPPT',
            'verif_ctt_perkembangan' => 'C. Keperawatan',
            'verif_cpo' => 'CPO',
            'verif_penunjang' => 'Penunjang Medis',
            'verif_edu_informasi' => 'Edukasi',
            'verif_discharge_planning' => 'Discharge Planning',
            'verif_dpjp' => 'DPJP',
            'verif_triase' => 'Triase',
            'verif_assesmen_igd' => 'A. Gawat Darurat',
            'verif_transfer_pasien' => 'Transfer Ruangan',
            'verif_observasi_ttv' => 'Observasi TTV',
            'verif_risiko_jatuh' => 'Resiko Jatuh',
            'verif_informed_consent_anastesi' => 'Informed Consent',
            'verif_penandaan_op' => 'Penanda Operasi',
            'verif_serah_terima_pasien_op' => 'Checklist Serah Terima',
            'verif_penilaian_pra_anastesi' => 'Pra Anastesi',
            'verif_praop' => 'Pra Operasi',
            'verif_pra_sedasi' => 'Pra Sedasi',
            'verif_laporanop' => 'Operasi 1',
            'verif_laporanop2' => 'Operasi 2',
            'verif_laporanop3' => 'Operasi 3',
            'verif_laporanop4' => 'Operasi 4',
            'verif_berkas_digital' => 'Berkas Digital',
            'verif_inventaris_kasa' => 'Sign Out',
            'verif_tatatertib_icu' => 'Tata Tertib ICU',
            'verif_persetujuan_icu' => 'Persetujuan ICU/PICU',
        ];

        // STYLING
        $titleStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ];
        $subtitleStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '006699']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ];
        $headerStyle = [
            'font' => ['bold' => true, 'italic' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E8449']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]]
        ];

        // JUDUL
        $lastCol = 'C';
        foreach ($checklistFields as $_) $lastCol++;
        $lastCol++; 

        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->setCellValue('A1', 'LAPORAN KELENGKAPAN REKAM MEDIS');
        $sheet->getStyle('A1')->applyFromArray($titleStyle);
        $sheet->getRowDimension('1')->setRowHeight(30);

        $bangsalName = "Semua Bangsal";
        if (!empty($bangsalFilter) && $bangsalFilter !== 'semua') {
            $bangsalName = DB::table('bangsal')->where('kd_bangsal', $bangsalFilter)->value('nm_bangsal');
        }
        $periode = date('d/m/Y', strtotime($tgl1)) . ' - ' . date('d/m/Y', strtotime($tgl2));
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->setCellValue('A2', 'Bangsal: ' . $bangsalName . ' | Periode: ' . $periode);
        $sheet->getStyle('A2')->applyFromArray($subtitleStyle);
        $sheet->getRowDimension('2')->setRowHeight(22);

        // HEADER KOLOM
        $headerRow = 4;
        $sheet->setCellValue('A' . $headerRow, 'No.');
        $sheet->setCellValue('B' . $headerRow, 'No. MR');
        $sheet->setCellValue('C' . $headerRow, 'Nama Pasien');

        $col = 'D';
        foreach ($checklistFields as $label) {
            $sheet->setCellValue($col . $headerRow, $label);
            $sheet->getColumnDimension($col)->setWidth(5);
            $sheet->getStyle($col . $headerRow)->getAlignment()->setTextRotation(90);
            $col++;
        }
        $sheet->setCellValue($col . $headerRow, 'Sign Out');
        $sheet->getColumnDimension($col)->setWidth(10);
        $sheet->getStyle($col . $headerRow)->getAlignment()->setTextRotation(90);

        $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $headerRow)->applyFromArray($headerStyle);
        $sheet->getRowDimension($headerRow)->setRowHeight(120);

        // DATA
        $row = $headerRow + 1;
        $no = 1;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValueExplicit('B' . $row, $item->no_rkm_medis, DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $item->nm_pasien);

            $col = 'D';
            foreach ($checklistFields as $field => $label) {
                $value = (isset($item->$field) && $item->$field == 1) ? '1' : '';
                $sheet->setCellValue($col . $row, $value);
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $col++;
            }

            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':' . $lastCol . $row)
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EAF2F8');
            }

            $row++;
        }

        // FINAL STYLING
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('B' . $headerRow . ':B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $borderStyle = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '99A3A4']]]];
        $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $lastRow)->applyFromArray($borderStyle);

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->freezePane('D' . ($headerRow + 1));

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }

    public function verifikasiOtomatisBatch(Request $request)
    {
        set_time_limit(300); // 5 menit

        try {
            // Validasi user
            $nip = session()->get('nik');
            $allowedUsers = ['199305082020122015', '198611162020122005', '23.05.034', 'ridahayati', '0011'];

            if (!in_array($nip, $allowedUsers)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk verifikasi otomatis'
                ], 403);
            }

            $tgl1 = $request->input('tgl1');
            $tgl2 = $request->input('tgl2');
            $bangsalFilter = $request->input('bangsal');

            // Query semua pasien
            $query = DB::table('reg_periksa as a')
                ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
                ->join(DB::raw("(
                    SELECT no_rawat, kd_kamar, tgl_keluar, stts_pulang
                    FROM (
                        SELECT 
                            no_rawat, kd_kamar, tgl_keluar, stts_pulang,
                            ROW_NUMBER() OVER (PARTITION BY no_rawat ORDER BY tgl_keluar DESC, jam_keluar DESC) AS rn
                        FROM kamar_inap
                        WHERE stts_pulang != 'Pindah Kamar'
                    ) AS ranked_ki
                    WHERE rn = 1
                ) as ki"), 'a.no_rawat', '=', 'ki.no_rawat')
                ->join('kamar as k', 'ki.kd_kamar', '=', 'k.kd_kamar')
                ->join('bangsal as bang', 'k.kd_bangsal', '=', 'bang.kd_bangsal')
                ->whereBetween('a.tgl_registrasi', [$tgl1, $tgl2])
                ->where('a.status_lanjut', '=', 'Ranap');

            if (!empty($bangsalFilter) && $bangsalFilter !== 'semua') {
                $query->where('bang.kd_bangsal', $bangsalFilter);
            }

            $pasienList = $query->select('a.no_rawat', 'a.no_rkm_medis', 'k.kd_bangsal')->get();

            if ($pasienList->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada pasien dalam periode yang dipilih'
                ], 404);
            }

            // Statistik
            $totalPasien = $pasienList->count();
            $totalVerifiedFields = 0;
            $fieldStats = [];

            // Extract no_rawat values for batch processing
            $noRawatList = $pasienList->pluck('no_rawat')->toArray();

            // Get existing kelengkapan data for all patients at once
            $existingKelengkapan = DB::table('kelengkapan_rm')
                ->whereIn('no_rawat', $noRawatList)
                ->get()
                ->keyBy('no_rawat');

            // Cek operasi untuk semua pasien sekaligus
            $operasiRecords = DB::table('laporan_operasi')
                ->whereIn('no_rawat', $noRawatList)
                ->pluck('no_rawat')
                ->merge(
                    DB::table('laporan_operasi_2')
                        ->whereIn('no_rawat', $noRawatList)
                        ->pluck('no_rawat')
                )
                ->merge(
                    DB::table('laporan_operasi_3')
                        ->whereIn('no_rawat', $noRawatList)
                        ->pluck('no_rawat')
                )
                ->merge(
                    DB::table('laporan_operasi_4')
                        ->whereIn('no_rawat', $noRawatList)
                        ->pluck('no_rawat')
                )
                ->unique()
                ->toArray();

            // Bangsal untuk bayi baru lahir
            $bayiBaruLahirBangsal = ['RB012', 'RB013', 'RB014'];

            // Prepare data for batch processing
            $updateData = [];

            // Store field labels from the first patient (they're the same for all)
            $fieldLabels = [];

            foreach ($pasienList as $pasien) {
                $noRawat = $pasien->no_rawat;
                $isOperasi = in_array($noRawat, $operasiRecords);
                $isBayiBaruLahir = in_array($pasien->kd_bangsal, $bayiBaruLahirBangsal);

                // Initialize update data for this patient
                $updateData[$noRawat] = [
                    'no_rawat' => $noRawat,
                    'no_rkm_medis' => $pasien->no_rkm_medis,
                    'nip' => $nip,
                    'time_stamp' => now(),
                ];

                // Get existing kelengkapan for this patient
                $kelengkapan = $existingKelengkapan->get($noRawat);

                // ===== DEFINISI FIELD DENGAN TABEL YANG BENAR =====
                $fieldsToCheck = [
                    'verif_sep' => [
                        'label' => 'SEP BPJS',
                        'table' => 'bridging_sep',
                        'condition' => ['no_rawat' => $noRawat]
                    ],
                    'verif_resume' => [
                        'label' => 'Resume Medis',
                        'table' => 'resume_pasien_ranap',
                        'condition' => ['no_rawat' => $noRawat]
                    ],
                    'verif_general_consent' => [
                        'label' => 'General Consent',
                        'table' => 'surat_persetujuan_umum',
                        'condition' => ['no_rawat' => $noRawat]
                    ],
                    'verif_ews' => [
                        'label' => 'EWS',
                        'check' => 'custom_ews'
                    ],
                    'verif_asesmen_awal_medis' => [
                        'label' => 'Asesmen Awal Medis',
                        'check' => 'custom_asesmen_medis'
                    ],
                    'verif_rekonsiliasi_obat' => [
                        'label' => 'Rekonsiliasi Obat',
                        'table' => 'rekonsiliasi_obat',
                        'condition' => ['no_rawat' => $noRawat]
                    ],
                    'verif_cppt' => [
                        'label' => 'CPPT',
                        'check' => 'custom_cppt'
                    ],
                    'verif_ctt_perkembangan' => [
                        'label' => 'Catatan Perkembangan',
                        'table' => 'catatan_keperawatan_ranap',
                        'condition' => ['no_rawat' => $noRawat]
                    ],
                    'verif_cpo' => [
                        'label' => 'CPO',
                        'table' => 'pemberian_obat',
                        'condition' => ['no_rawat' => $noRawat]
                    ],
                    'verif_penunjang' => [
                        'label' => 'Pemeriksaan Penunjang',
                        'check' => 'custom_penunjang'
                    ],
                    'verif_edu_informasi' => [
                        'label' => 'Edukasi',
                        'table' => 'edukasi_pasien_keluarga_rj',
                        'condition' => ['no_rawat' => $noRawat]
                    ],
                    'verif_discharge_planning' => [
                        'label' => 'Discharge Planning',
                        'table' => 'perencanaan_pemulangan',
                        'condition' => ['no_rawat' => $noRawat]
                    ],
                    'verif_dpjp' => [
                        'label' => 'DPJP',
                        'table' => 'dpjp_ranap',
                        'condition' => ['no_rawat' => $noRawat]
                    ],
                    'verif_tatatertib_icu' => [
                        'label' => 'Tata Tertib ICU',
                        'table' => 'tata_tertib_icu',
                        'condition' => ['no_rawat' => $noRawat]
                    ],
                    'verif_risiko_jatuh' => [
                        'label' => 'Risiko Jatuh',
                        'check' => 'custom_risiko_jatuh'
                    ],
                ];

                // Field untuk NON-bayi baru lahir
                if (!$isBayiBaruLahir) {
                    $fieldsToCheck['verif_triase'] = [
                        'label' => 'Triase',
                        'table' => 'data_triase_igd',
                        'condition' => ['no_rawat' => $noRawat]
                    ];
                    $fieldsToCheck['verif_assesmen_igd'] = [
                        'label' => 'Asesmen IGD',
                        'table' => 'penilaian_medis_igd',
                        'condition' => ['no_rawat' => $noRawat]
                    ];
                    $fieldsToCheck['verif_transfer_pasien'] = [
                        'label' => 'Transfer Pasien',
                        'table' => 'transfer_pasien_antar_ruang',
                        'condition' => ['no_rawat' => $noRawat]
                    ];
                    $fieldsToCheck['verif_observasi_ttv'] = [
                        'label' => 'Observasi TTV',
                        'table' => 'catatan_observasi_ranap',
                        'condition' => ['no_rawat' => $noRawat]
                    ];
                }

                // Field operasi
                if ($isOperasi) {
                    $fieldsToCheck = array_merge($fieldsToCheck, [
                        'verif_informed_consent_anastesi' => [
                            'label' => 'Informed Consent Anestesi',
                            'table' => 'persetujuan_penolakan_tindakan',
                            'condition' => ['no_rawat' => $noRawat]
                        ],
                        'verif_penandaan_op' => [
                            'label' => 'Penandaan Operasi',
                            'check' => 'custom_penandaan_op'
                        ],
                        'verif_serah_terima_pasien_op' => [
                            'label' => 'Checklist Serah Terima',
                            'table' => 'checklist_pre_operasi',
                            'condition' => ['no_rawat' => $noRawat]
                        ],
                        'verif_penilaian_pra_anastesi' => [
                            'label' => 'Pra Anestesi',
                            'table' => 'penilaian_pre_anestesi',
                            'condition' => ['no_rawat' => $noRawat]
                        ],
                        'verif_praop' => [
                            'label' => 'Pra Operasi',
                            'table' => 'penilaian_pre_operasi',
                            'condition' => ['no_rawat' => $noRawat]
                        ],
                        'verif_pra_sedasi' => [
                            'label' => 'Pra Sedasi',
                            'table' => 'asesmen_pra_sedasi',
                            'condition' => ['no_rawat' => $noRawat]
                        ],
                        'verif_laporanop' => [
                            'label' => 'Laporan Operasi 1',
                            'table' => 'laporan_operasi',
                            'condition' => ['no_rawat' => $noRawat]
                        ],
                        'verif_laporanop2' => [
                            'label' => 'Laporan Operasi 2',
                            'table' => 'laporan_operasi_2',
                            'condition' => ['no_rawat' => $noRawat]
                        ],
                        'verif_laporanop3' => [
                            'label' => 'Laporan Operasi 3',
                            'table' => 'laporan_operasi_3',
                            'condition' => ['no_rawat' => $noRawat]
                        ],
                        'verif_laporanop4' => [
                            'label' => 'Laporan Operasi 4',
                            'table' => 'laporan_operasi_4',
                            'condition' => ['no_rawat' => $noRawat]
                        ],
                        'verif_inventaris_kasa' => [
                            'label' => 'Inventaris Kasa',
                            'table' => 'signout_sebelum_menutup_luka',
                            'condition' => ['no_rawat' => $noRawat]
                        ],
                        'verif_anamnese_anestesi' => [
                            'label' => 'Anamnese Anestesi',
                            'table' => 'pemeriksaan_anestesi',
                            'condition' => ['no_rawat' => $noRawat]
                        ],
                        'verif_laporan_sedasi' => [
                            'label' => 'Laporan Sedasi',
                            'table' => 'laporan_sedasi',
                            'condition' => ['no_rawat' => $noRawat]
                        ],
                    ]);
                }

                // Store labels from the first patient only
                if (empty($fieldLabels)) {
                    foreach ($fieldsToCheck as $field => $config) {
                        $fieldLabels[$field] = $config['label'];
                    }
                }

                // Initialize all fields to 0
                foreach ($fieldsToCheck as $field => $config) {
                    $updateData[$noRawat][$field] = 0;
                }

                // Check if already verified manually
                if ($kelengkapan) {
                    foreach ($fieldsToCheck as $field => $config) {
                        if (isset($kelengkapan->$field) && $kelengkapan->$field == 1) {
                            $updateData[$noRawat][$field] = 1;
                        }
                    }
                }
            }

            // BATCH PROCESSING BY FIELD
            // Group fields by table for batch queries
            $tableFields = [];
            $customFields = [];

            foreach ($pasienList as $pasien) {
                $noRawat = $pasien->no_rawat;
                $isOperasi = in_array($noRawat, $operasiRecords);
                $isBayiBaruLahir = in_array($pasien->kd_bangsal, $bayiBaruLahirBangsal);

                // Define fields for this patient type
                $fieldsToCheck = [
                    'verif_sep' => ['table' => 'bridging_sep'],
                    'verif_resume' => ['table' => 'resume_pasien_ranap'],
                    'verif_general_consent' => ['table' => 'surat_persetujuan_umum'],
                    'verif_rekonsiliasi_obat' => ['table' => 'rekonsiliasi_obat'],
                    'verif_ctt_perkembangan' => ['table' => 'catatan_keperawatan_ranap'],
                    'verif_cpo' => ['table' => 'pemberian_obat'],
                    'verif_edu_informasi' => ['table' => 'edukasi_pasien_keluarga_rj'],
                    'verif_discharge_planning' => ['table' => 'perencanaan_pemulangan'],
                    'verif_dpjp' => ['table' => 'dpjp_ranap'],
                    'verif_tatatertib_icu' => ['table' => 'tata_tertib_icu'],
                ];

                // Custom checks
                $customFieldsToCheck = [
                    'verif_ews' => 'custom_ews',
                    'verif_asesmen_awal_medis' => 'custom_asesmen_medis',
                    'verif_cppt' => 'custom_cppt',
                    'verif_penunjang' => 'custom_penunjang',
                    'verif_risiko_jatuh' => 'custom_risiko_jatuh',
                ];

                // Field untuk NON-bayi baru lahir
                if (!$isBayiBaruLahir) {
                    $fieldsToCheck['verif_triase'] = ['table' => 'data_triase_igd'];
                    $fieldsToCheck['verif_assesmen_igd'] = ['table' => 'penilaian_medis_igd'];
                    $fieldsToCheck['verif_transfer_pasien'] = ['table' => 'transfer_pasien_antar_ruang'];
                    $fieldsToCheck['verif_observasi_ttv'] = ['table' => 'catatan_observasi_ranap'];
                }

                // Field operasi
                if ($isOperasi) {
                    $fieldsToCheck = array_merge($fieldsToCheck, [
                        'verif_informed_consent_anastesi' => ['table' => 'persetujuan_penolakan_tindakan'],
                        'verif_penandaan_op' => ['table' => 'berkas_digital_perawatan'],
                        'verif_serah_terima_pasien_op' => ['table' => 'checklist_pre_operasi'],
                        'verif_penilaian_pra_anastesi' => ['table' => 'penilaian_pre_anestesi'],
                        'verif_praop' => ['table' => 'penilaian_pre_operasi'],
                        'verif_pra_sedasi' => ['table' => 'asesmen_pra_sedasi'],
                        'verif_laporanop' => ['table' => 'laporan_operasi'],
                        'verif_laporanop2' => ['table' => 'laporan_operasi_2'],
                        'verif_laporanop3' => ['table' => 'laporan_operasi_3'],
                        'verif_laporanop4' => ['table' => 'laporan_operasi_4'],
                        'verif_inventaris_kasa' => ['table' => 'signout_sebelum_menutup_luka'],
                        'verif_anamnese_anestesi' => ['table' => 'pemeriksaan_anestesi'],
                        'verif_laporan_sedasi' => ['table' => 'laporan_sedasi'],
                    ]);

                    $customFieldsToCheck['verif_penandaan_op'] = 'custom_penandaan_op';
                }

                // Group fields by table
                foreach ($fieldsToCheck as $field => $config) {
                    $table = $config['table'];
                    if (!isset($tableFields[$table])) {
                        $tableFields[$table] = [];
                    }
                    $tableFields[$table][] = ['field' => $field, 'no_rawat' => $noRawat];
                }

                // Group custom fields
                foreach ($customFieldsToCheck as $field => $checkType) {
                    if (!isset($customFields[$checkType])) {
                        $customFields[$checkType] = [];
                    }
                    $customFields[$checkType][] = ['field' => $field, 'no_rawat' => $noRawat];
                }
            }

            // Process table-based fields in batches
            foreach ($tableFields as $table => $fieldList) {
                // Extract unique no_rawat values for this table
                $noRawatValues = array_unique(array_column($fieldList, 'no_rawat'));

                // Get all records for this table
                $records = DB::table($table)
                    ->whereIn('no_rawat', $noRawatValues)
                    ->select('no_rawat')
                    ->distinct()
                    ->get()
                    ->pluck('no_rawat')
                    ->toArray();

                // Update the data for matching records
                foreach ($fieldList as $item) {
                    $field = $item['field'];
                    $noRawat = $item['no_rawat'];

                    if (in_array($noRawat, $records)) {
                        $updateData[$noRawat][$field] = 1;
                        $totalVerifiedFields++;

                        if (!isset($fieldStats[$field])) {
                            $fieldStats[$field] = [
                                'label' => $fieldLabels[$field], // Use stored label
                                'count' => 0
                            ];
                        }
                        $fieldStats[$field]['count']++;
                    }
                }
            }

            // Process custom fields in batches
            foreach ($customFields as $checkType => $fieldList) {
                // Extract unique no_rawat values for this check type
                $noRawatValues = array_unique(array_column($fieldList, 'no_rawat'));

                // Get all records that match the custom check
                $matchingRecords = $this->batchCustomExistsCheck($checkType, $noRawatValues);

                // Update the data for matching records
                foreach ($fieldList as $item) {
                    $field = $item['field'];
                    $noRawat = $item['no_rawat'];

                    if (in_array($noRawat, $matchingRecords)) {
                        $updateData[$noRawat][$field] = 1;
                        $totalVerifiedFields++;

                        if (!isset($fieldStats[$field])) {
                            $fieldStats[$field] = [
                                'label' => $fieldLabels[$field], // Use stored label
                                'count' => 0
                            ];
                        }
                        $fieldStats[$field]['count']++;
                    }
                }
            }

            // Set verif_all to 1 for all patients
            foreach ($updateData as $noRawat => &$data) {
                $data['verif_all'] = 1;
            }

            // Batch update using transaction
            DB::transaction(function () use ($updateData) {
                foreach ($updateData as $data) {
                    $noRawat = $data['no_rawat'];
                    unset($data['no_rawat']); // Remove no_rawat from data array

                    DB::table('kelengkapan_rm')->updateOrInsert(
                        ['no_rawat' => $noRawat],
                        $data
                    );
                }
            });

            uasort($fieldStats, function ($a, $b) {
                return $b['count'] - $a['count'];
            });

            return response()->json([
                'status' => 'success',
                'total_pasien' => $totalPasien,
                'success_count' => $totalPasien, // All patients processed
                'failed_count' => 0,
                'skipped_count' => 0,
                'total_verified_fields' => $totalVerifiedFields,
                'details' => array_values($fieldStats),
                'message' => "Verifikasi selesai untuk {$totalPasien} pasien"
            ]);
        } catch (\Exception $e) {
            \Log::error('Batch verification error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Batch custom exists check
    private function batchCustomExistsCheck($checkType, $noRawatValues)
    {
        switch ($checkType) {
            case 'custom_ews':
                $pewsDewasa = DB::table('pemantauan_pews_dewasa')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                $pewsAnak = DB::table('pemantauan_pews_anak')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                $ewsNeonatus = DB::table('pemantauan_ews_neonatus')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                $meowsObstetri = DB::table('pemantauan_meows_obstetri')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                return array_unique(array_merge($pewsDewasa, $pewsAnak, $ewsNeonatus, $meowsObstetri));

            case 'custom_asesmen_medis':
                $medisRanap = DB::table('penilaian_medis_ranap')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                $keperawatanRanap = DB::table('penilaian_awal_keperawatan_ranap')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                $keperawatanBayi = DB::table('penilaian_awal_keperawatan_ranap_bayi')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                return array_unique(array_merge($medisRanap, $keperawatanRanap, $keperawatanBayi));

            case 'custom_cppt':
                $pemeriksaanRanap = DB::table('pemeriksaan_ranap')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                $pemeriksaanRalan = DB::table('pemeriksaan_ralan')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                return array_unique(array_merge($pemeriksaanRanap, $pemeriksaanRalan));

            case 'custom_penunjang':
                $periksaLab = DB::table('periksa_lab')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                $periksaRadiologi = DB::table('periksa_radiologi')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                return array_unique(array_merge($periksaLab, $periksaRadiologi));

            case 'custom_risiko_jatuh':
                $resikoJatuhAnak = DB::table('penilaian_lanjutan_resiko_jatuh_anak')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                $resikoJatuhDewasa = DB::table('penilaian_lanjutan_resiko_jatuh_dewasa')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                $resikoJatuhLansia = DB::table('penilaian_lanjutan_resiko_jatuh_lansia')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->pluck('no_rawat')
                    ->toArray();

                return array_unique(array_merge($resikoJatuhAnak, $resikoJatuhDewasa, $resikoJatuhLansia));

            case 'custom_penandaan_op':
                return DB::table('berkas_digital_perawatan')
                    ->whereIn('no_rawat', $noRawatValues)
                    ->where('kode', '009')
                    ->pluck('no_rawat')
                    ->toArray();

            default:
                return [];
        }
    }
    // Akhir Kelengkapan RM
    ////////////////////////////////////////////////////


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

    public function getERMEWS(Request $request)
    {
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

    public function getERMPartograf(Request $request)
    {
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

    public function getERMBerkasDigital(Request $request)
    {
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

    public function getERMSEP(Request $request)
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
                'b.catatan'
            )
            ->where('r.no_rawat', '=', $id)
            ->get();

        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_sep', [
            'row' => $data,
            'sep' => $sep,
        ]);
    }

    public function getERMDPJP(Request $request)
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

    public function getERMTataTertib(Request $request)
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

        // Ambil data Tata Tertib ICU
        $tatatertib = DB::table('tata_tertib_icu')
            ->where('no_rawat', '=', $id)
            ->orderBy('tanggal', 'DESC')
            ->first();

        // Kirim data ke view erm_tatatertib.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_tatatertib', [
            'row' => $data,
            'tatatertib' => $tatatertib,
        ]);
    }

    public function getERMPersetujuanICU(Request $request)
    {
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->where('a.no_rawat', '=', $id)
            ->where('a.status_lanjut', '=', 'Ranap')
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $persetujuanicu = DB::table('persetujuan_icu')
            ->where('no_rawat', '=', $id)
            ->orderBy('tanggal', 'DESC')
            ->first();

        return view('rm.laporan_rm.berkas_rm.erm_persetujuanicu', [
            'row' => $data,
            'persetujuanicu' => $persetujuanicu,
        ]);
    }

    public function getERMRencanaPemulangan(Request $request)
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

    public function getERMTransferAntarRuang(Request $request)
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

    public function getERMCatatanObservasi(Request $request)
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

    public function getERMTriaseIGD(Request $request)
    {
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

    public function getERMEdukasi(Request $request)
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

    public function getERMPP(Request $request)
    {
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

    public function getERMSIGNOUT(Request $request)
    {
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

    public function getERMPENILAIANPREAN(Request $request)
    {
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

    public function getERMPraOp(Request $request)
    {
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select(
                'a.no_rawat',
                'a.tgl_registrasi',
                'a.jam_reg',
                'a.status_lanjut',
                'b.nm_pasien'
            )
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
                'd.nm_dokter'
            )
            ->where('no_rawat', '=', $id)
            ->first();

        return view('rm.laporan_rm.berkas_rm.erm_praop', [
            'row' => $data,
            'ppo' => $penilaianpraop,
        ]);
    }

    public function getERMPraSedasi(Request $request)
    {
        $id = $request->query('id');

        $data = DB::table('reg_periksa as a')
            ->join('pasien as b', 'b.no_rkm_medis', '=', 'a.no_rkm_medis')
            ->select(
                'a.no_rawat',
                'a.tgl_registrasi',
                'a.jam_reg',
                'a.status_lanjut',
                'b.nm_pasien'
            )
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
                'd.nm_dokter'
            )
            ->where('no_rawat', '=', $id)
            ->first();

        return view('rm.laporan_rm.berkas_rm.erm_pra_sedasi', [
            'row' => $data,
            'sedasi' => $sedasi,
        ]);
    }

    public function getERMLaporanOp(Request $request)
    {
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
                'd.nm_dokter'
            )
            ->where('lo.no_rawat', '=', $id)
            ->first();

        return view('rm.laporan_rm.berkas_rm.erm_laporanop', [
            'row' => $data,
            'laporanop' => $laporanop,
        ]);
    }

    public function getERMLaporanOp2(Request $request)
    {
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

        $laporanop2 = DB::table('operasi as op')
            ->leftJoin('laporan_operasi_2 as lo', function ($join) {
                $join->on('lo.no_rawat', '=', 'op.no_rawat')
                    ->whereColumn('op.tgl_operasi', '=', 'lo.tanggal');
            })
            ->leftJoin('instruksi_operasi_2 as io', function ($join) {
                $join->on('io.no_rawat', '=', 'op.no_rawat')
                    ->whereColumn('op.tgl_operasi', '=', 'io.tanggal');
            })
            ->leftJoin('dokter as d', 'op.operator1', '=', 'd.kd_dokter')
            ->select(
                'lo.no_rawat',
                'lo.tanggal',
                'lo.selesaioperasi',
                'lo.diagnosa_preop',
                'lo.diagnosa_postop',
                'lo.permintaan_pa',
                'lo.jaringan_dieksekusi',
                'lo.laporan_operasi_2',
                'io.instruksi',
                'io.jenis_operasi',
                'op.operator1',
                'd.nm_dokter'
            )
            ->where('op.no_rawat', '=', $id)
            ->orderBy('op.tgl_operasi', 'asc')
            ->offset(1)
            ->limit(1)
            ->first();

        return view('rm.laporan_rm.berkas_rm.erm_laporanop2', [
            'row' => $data,
            'laporanop2' => $laporanop2,
        ]);
    }

    public function getERMLaporanOp3(Request $request)
    {
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

        $laporanop3 = DB::table('operasi as op')
            ->leftJoin('laporan_operasi_3 as lo', function ($join) {
                $join->on('lo.no_rawat', '=', 'op.no_rawat')
                    ->whereColumn('op.tgl_operasi', '=', 'lo.tanggal');
            })
            ->leftJoin('instruksi_operasi_3 as io', function ($join) {
                $join->on('io.no_rawat', '=', 'op.no_rawat')
                    ->whereColumn('op.tgl_operasi', '=', 'io.tanggal');
            })
            ->leftJoin('dokter as d', 'op.operator1', '=', 'd.kd_dokter')
            ->select(
                'lo.no_rawat',
                'lo.tanggal',
                'lo.selesaioperasi',
                'lo.diagnosa_preop',
                'lo.diagnosa_postop',
                'lo.permintaan_pa',
                'lo.jaringan_dieksekusi',
                'lo.laporan_operasi_3',
                'io.instruksi',
                'io.jenis_operasi',
                'op.operator1',
                'd.nm_dokter'
            )
            ->where('op.no_rawat', '=', $id)
            ->orderBy('op.tgl_operasi', 'asc')
            ->offset(2)
            ->limit(1)
            ->first();

        return view('rm.laporan_rm.berkas_rm.erm_laporanop3', [
            'row' => $data,
            'laporanop3' => $laporanop3,
        ]);
    }

    public function getERMLaporanOp4(Request $request)
    {
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

        $laporanop4 = DB::table('operasi as op')
            ->leftJoin('laporan_operasi_4 as lo', function ($join) {
                $join->on('lo.no_rawat', '=', 'op.no_rawat')
                    ->whereColumn('op.tgl_operasi', '=', 'lo.tanggal');
            })
            ->leftJoin('instruksi_operasi_4 as io', function ($join) {
                $join->on('io.no_rawat', '=', 'op.no_rawat')
                    ->whereColumn('op.tgl_operasi', '=', 'io.tanggal');
            })
            ->leftJoin('dokter as d', 'op.operator1', '=', 'd.kd_dokter')
            ->select(
                'lo.no_rawat',
                'lo.tanggal',
                'lo.selesaioperasi',
                'lo.diagnosa_preop',
                'lo.diagnosa_postop',
                'lo.permintaan_pa',
                'lo.jaringan_dieksekusi',
                'lo.laporan_operasi_4',
                'io.instruksi',
                'io.jenis_operasi',
                'op.operator1',
                'd.nm_dokter'
            )
            ->where('op.no_rawat', '=', $id)
            ->orderBy('op.tgl_operasi', 'asc')
            ->offset(3)
            ->limit(1)
            ->first();

        return view('rm.laporan_rm.berkas_rm.erm_laporanop4', [
            'row' => $data,
            'laporanop4' => $laporanop4,
        ]);
    }

    public function getERMAnamneseAnestesi(Request $request)
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

        $anamnesean = DB::table('pemeriksaan_anestesi as a')
            ->leftJoin('dokter as d', 'd.kd_dokter', '=', 'a.nip')
            ->select('a.*', 'd.nm_dokter')
            ->where('a.no_rawat', '=', $id)
            ->get();

        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_anamnese_anestesi', [
            'row' => $data,
            'anamnese_anestesi' => $anamnesean,
        ]);
    }

    public function getERMLaporanSedasi(Request $request)
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

        $sedasi = DB::table('laporan_sedasi as ls')
            ->join('operasi as o', function ($join) {
                $join->on('o.no_rawat', '=', 'ls.no_rawat')
                    ->whereRaw('DATE(o.tgl_operasi) = ls.tanggal_tindakan');
            })
            ->join('dokter as d', 'd.kd_dokter', '=', 'o.dokter_anestesi')
            ->select('ls.*', 'o.dokter_anestesi', 'd.nm_dokter')
            ->where('ls.no_rawat', '=', $id)
            ->get();

        // Kirim data ke view erm.blade.php
        return view('rm.laporan_rm.berkas_rm.erm_laporan_sedasi', [
            'row' => $data,
            'laporan_sedasi' => $sedasi,
        ]);
    }

    public function getERMLAPORANANESTESI(Request $request)
    {
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

    public function getERMCHECKLISTPREOP(Request $request)
    {
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

    public function getERMPENANDAANOP(Request $request)
    {
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

    public function getERMICTA(Request $request)
    {
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

    public function getERMRESIKOGABUNGAN(Request $request)
    {
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

    public function getERMRESIKOANAK(Request $request)
    {
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

    public function getERMRESIKOLANSIA(Request $request)
    {
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


    //START GAWIAN KU


    public function kunjunganrajal(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');

        $tahun = date('Y', strtotime($request->tgl1));

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
        $tahun = $tgl1->format('Y');
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

        
        // ===============================
        // RALAN → SEMBUH
        // ===============================
        $sembuhRalan = DB::table('reg_periksa as rp')
        ->leftJoin('kamar_inap as ki', 'ki.no_rawat', '=', 'rp.no_rawat')
        ->leftJoin('rujuk as r', 'r.no_rawat', '=', 'rp.no_rawat')
        ->leftJoin('pasien_mati as pm', 'pm.no_rkm_medis', '=', 'rp.no_rkm_medis')
        ->where('rp.status_lanjut', 'Ralan')
        ->whereYear('rp.tgl_registrasi', $tahun)
        ->whereNull('ki.no_rawat')
        ->whereNull('r.no_rawat')
        ->whereNull('pm.no_rkm_medis')
        ->distinct('rp.no_rkm_medis')
        ->count('rp.no_rkm_medis');


        // ===============================
        // RALAN → MASUK RANAP
        // ===============================
        $ranapRalan = DB::table('reg_periksa as rp')
            ->join('kamar_inap as ki', 'ki.no_rawat', '=', 'rp.no_rawat')
            ->whereYear('rp.tgl_registrasi', $tahun)
            ->distinct('rp.no_rkm_medis')
            ->count('rp.no_rkm_medis');


        // ===============================
        // TOTAL
        // ===============================
        $totalRalan = $sembuhRalan + $ranapRalan;

        // lainnya = total ralan sebenarnya - kategori di atas
        $totalSemuaRalan = DB::table('reg_periksa')
            ->where('status_lanjut', 'Ralan')
            ->whereYear('tgl_registrasi', $tahun)
            ->distinct('no_rkm_medis')
            ->count('no_rkm_medis');

        $lainnyaRalan = $totalSemuaRalan - $sembuhRalan;

        // ===============================
        // PERSENTASE
        // ===============================
        $persenSembuhRalan  = $totalSemuaRalan > 0 ? round(($sembuhRalan / $totalSemuaRalan)*100,2) : 0;
        $persenRanapRalan   = $totalSemuaRalan > 0 ? round(($ranapRalan / $totalSemuaRalan)*100,2) : 0;
        $persenLainnyaRalan = $totalSemuaRalan > 0 ? round(($lainnyaRalan / $totalSemuaRalan)*100,2) : 0;

        // Check if PDF download is requested
        if ($request->has('download_pdf')) {
            return $this->generateKunjunganRajalPDF(
                $formattedTgl1,
                $formattedTgl2,
                $tanggal,
                $tahun,
                $sqlanggotapolri,
                $sqlanggotapns,
                $sqlanggotakelpolri,
                $sqlanggotadikbang,
                $sqlanggotadiktuk,
                $sqlpasienumum,
                $sqlpasienother,
                $total_pengunjung_bpjs,
                $total_kunjungan_bpjs,
                $total_pengunjung,
                $total_kunjungan,
                $sembuhRalan,
                $ranapRalan,
                $lainnyaRalan,
                $totalSemuaRalan,
                $persenSembuhRalan,
                $persenRanapRalan,
                $persenLainnyaRalan
            );
        }

        return $this->laporanView($request, 'kunjungan_rajal', [
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

            'tahun' => $tahun,
            'sembuhRalan' => $sembuhRalan,
            'ranapRalan' => $ranapRalan,
            'lainnyaRalan' => $lainnyaRalan,
            'totalSemuaRalan' => $totalSemuaRalan,
            'persenSembuhRalan' => $persenSembuhRalan,
            'persenRanapRalan' => $persenRanapRalan,
            'persenLainnyaRalan' => $persenLainnyaRalan
        ], 'rajal');
    }

    private function generateKunjunganRajalPDF(
        $formattedTgl1,
        $formattedTgl2,
        $tanggal,
        $tahun,
        $anggotapolri,
        $anggotapns,
        $anggotakelpolri,
        $dikbang,
        $diktuk,
        $pasien_umum,
        $pasien_other,
        $total_pengunjung_bpjs,
        $total_kunjungan_bpjs,
        $total_pengunjung,
        $total_kunjungan,
        $sembuhRalan,
        $ranapRalan,
        $lainnyaRalan,
        $totalSemuaRalan,
        $persenSembuhRalan,
        $persenRanapRalan,
        $persenLainnyaRalan
    ) {
        // Get hospital info
        $hospitalInfo = DB::table('setting')->first();

        $pdf = PDF::loadView('rm.laporan_rm.kunjungan_rajal_pdf', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,
            'tgllap' => $tanggal,
            'tahun' => $tahun,
            'anggotapolri' => $anggotapolri,
            'anggotapns' => $anggotapns,
            'anggotakelpolri' => $anggotakelpolri,
            'dikbang' => $dikbang,
            'diktuk' => $diktuk,
            'pasien_umum' => $pasien_umum,
            'pasien_other' => $pasien_other,
            'total_pengunjung_bpjs' => $total_pengunjung_bpjs,
            'total_kunjungan_bpjs' => $total_kunjungan_bpjs,
            'total_pengunjung' => $total_pengunjung,
            'total_kunjungan' => $total_kunjungan,
            'sembuhRalan' => $sembuhRalan,
            'ranapRalan' => $ranapRalan,
            'lainnyaRalan' => $lainnyaRalan,
            'totalSemuaRalan' => $totalSemuaRalan,
            'persenSembuhRalan' => $persenSembuhRalan,
            'persenRanapRalan' => $persenRanapRalan,
            'persenLainnyaRalan' => $persenLainnyaRalan,
            'hospitalInfo' => $hospitalInfo
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');

        // Generate filename
        $filename = 'Laporan_Pasien_Rawat_Jalan_' . date('d-m-Y', strtotime($formattedTgl1)) . '_sd_' . date('d-m-Y', strtotime($formattedTgl2)) . '.pdf';

        return $pdf->download($filename);
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

        // Check if PDF download is requested
        if ($request->has('download_pdf')) {
            return $this->generateKunjunganRanapPDF(
                $formattedTgl1,
                $formattedTgl2,
                $tanggal,
                $sqlanggotapolri,
                $sqlanggotapns,
                $sqlanggotakelpolri,
                $sqlanggotadikbang,
                $sqlanggotadiktuk,
                $sqlpasienumum,
                $sqlpasienother,
                $total_pengunjung_bpjs,
                $total_pengunjung
            );
        }

        return $this->laporanView($request, 'kunjungan_ranap', [
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

        ], 'ranap');
    }

    private function generateKunjunganRanapPDF(
        $formattedTgl1,
        $formattedTgl2,
        $tanggal,
        $anggotapolri,
        $anggotapns,
        $anggotakelpolri,
        $dikbang,
        $diktuk,
        $pasien_umum,
        $pasien_other,
        $total_pengunjung_bpjs,
        $total_pengunjung
    ) {
        // Get hospital info
        $hospitalInfo = DB::table('setting')->first();

        $pdf = PDF::loadView('rm.laporan_rm.kunjungan_ranap_pdf', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,
            'tgllap' => $tanggal,
            'anggotapolri' => $anggotapolri,
            'anggotapns' => $anggotapns,
            'anggotakelpolri' => $anggotakelpolri,
            'dikbang' => $dikbang,
            'diktuk' => $diktuk,
            'pasien_umum' => $pasien_umum,
            'pasien_other' => $pasien_other,
            'total_pengunjung_bpjs' => $total_pengunjung_bpjs,
            'total_pengunjung' => $total_pengunjung,
            'hospitalInfo' => $hospitalInfo
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');

        // Generate filename
        $filename = 'Laporan_Pasien_Rawat_Ranap_' . date('d-m-Y', strtotime($formattedTgl1)) . '_sd_' . date('d-m-Y', strtotime($formattedTgl2)) . '.pdf';

        return $pdf->download($filename);
    }

    public function penyakitterbanyak(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');
        $limitPenyakit = $request->input('limit_penyakit', 10);

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
            ->limit($limitPenyakit)
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
            ->limit($limitPenyakit)
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

        // Check if PDF download is requested
        if ($request->has('download_pdf')) {
            return $this->generatePenyakitTerbanyakPDF(
                $formattedTgl1,
                $formattedTgl2,
                $tanggal,
                $sqldiagnosa,
                $sqldiagnosaralan,
                $sqlpasienbaru,
                $limitPenyakit
            );
        }

        return $this->laporanView($request, 'penyakit_terbanyak', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,

            'diagnosa' => $sqldiagnosa,
            'diagnosa_ralan' => $sqldiagnosaralan,
            'pasien_baru' => $sqlpasienbaru,
            'limit_penyakit' => $limitPenyakit,
        ], 'penyakterbanyak');
    }

    private function generatePenyakitTerbanyakPDF(
        $formattedTgl1,
        $formattedTgl2,
        $tanggal,
        $diagnosa,
        $diagnosa_ralan,
        $pasien_baru,
        $limitPenyakit = 10
    ) {
        // Get hospital info
        $hospitalInfo = DB::table('setting')->first();

        $pdf = PDF::loadView('rm.laporan_rm.penyakit_terbanyak_pdf', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,
            'tgllap' => $tanggal,
            'diagnosa' => $diagnosa,
            'diagnosa_ralan' => $diagnosa_ralan,
            'pasien_baru' => $pasien_baru,
            'hospitalInfo' => $hospitalInfo,
            'limit_penyakit' => $limitPenyakit
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');

        // Generate filename
        $filename = 'Laporan_Penyakit_Terbanyak_' . date('d-m-Y', strtotime($formattedTgl1)) . '_sd_' . date('d-m-Y', strtotime($formattedTgl2)) . '.pdf';

        return $pdf->download($filename);
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

        // Check if PDF download is requested
        if ($request->has('download_pdf')) {
            return $this->generatePenyakitMenularPDF(
                $formattedTgl1,
                $formattedTgl2,
                $tanggal,
                $sqlanggotahiv, $sqlpnshiv, $sqldikbanghiv, $sqldiktukhiv, $sqlkelpolrihiv, $sqlumuhiv, $total_bpjshiv, $sqllainnyahiv, $total_hiv,
                $sqlanggotatb, $sqlpnstb, $sqldikbangtb, $sqldiktuktb, $sqlkelpolritb, $sqlumutb, $total_bpjstb, $sqllainnyatb, $total_tb,
                $sqlanggotamalaria, $sqlpnsmalaria, $sqldikbangmalaria, $sqldiktukmalaria, $sqlkelpolrimalaria, $sqlumumalaria, $total_bpjsmalaria, $sqllainnyamalaria, $total_malaria,
                $sqlanggotadbd, $sqlpnsdbd, $sqldikbangdbd, $sqldiktukdbd, $sqlkelpolridbd, $sqlumudbd, $total_bpjsdbd, $sqllainnyadbd, $total_dbd,
                $sqlanggotapms, $sqlpnspms, $sqldikbangpms, $sqldiktukpms, $sqlkelpolripms, $sqlumupms, $total_bpjspms, $sqllainnyapms, $total_pms,
                $sqlanggotahepatitis, $sqlpnshepatitis, $sqldikbanghepatitis, $sqldiktukhepatitis, $sqlkelpolrihepatitis, $sqlumuhepatitis, $total_bpjshepatitis, $sqllainnyahepatitis, $total_hepatitis,
                $sqlanggotacovid, $sqlpnscovid, $sqldikbangcovid, $sqldiktukcovid, $sqlkelpolricovid, $sqlumucovid, $total_bpjscovid, $sqllainnyacovid, $total_covid
            );
        }

        return $this->laporanView($request, 'penyakit_menular', [

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
        ], 'penyakitmenular');
    }

    private function generatePenyakitMenularPDF(
        $formattedTgl1,
        $formattedTgl2,
        $tanggal,
        $anggotahiv, $pnshiv, $dikbanghiv, $diktukhiv, $kelpolrihiv, $umumhiv, $bpjshiv, $otherhiv, $total_hiv,
        $anggotatb, $pnstb, $dikbangtb, $diktuktb, $kelpolritb, $umumtb, $bpjstb, $othertb, $total_tb,
        $anggotamalaria, $pnsmalaria, $dikbangmalaria, $diktukmalaria, $kelpolrimalaria, $umummalaria, $bpjsmalaria, $othermalaria, $total_malaria,
        $anggotadbd, $pnsdbd, $dikbangdbd, $diktukdbd, $kelpolridbd, $umumdbd, $bpjsdbd, $otherdbd, $total_dbd,
        $anggotapms, $pnspms, $dikbangpms, $diktukpms, $kelpolripms, $umumpms, $bpjspms, $otherpms, $total_pms,
        $anggotahepatitis, $pnshepatitis, $dikbanghepatitis, $diktukhepatitis, $kelpolrihepatitis, $umumhepatitis, $bpjshepatitis, $otherhepatitis, $total_hepatitis,
        $anggotacovid, $pnscovid, $dikbangcovid, $diktukcovid, $kelpolricovid, $umumcovid, $bpjscovid, $othercovid, $total_covid
    ) {
        // Get hospital info
        $hospitalInfo = DB::table('setting')->first();

        $pdf = PDF::loadView('rm.laporan_rm.penyakit_menular_pdf', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,
            'tgllap' => $tanggal,
            'anggotahiv' => $anggotahiv, 'pnshiv' => $pnshiv, 'dikbanghiv' => $dikbanghiv, 'diktukhiv' => $diktukhiv, 'kelpolrihiv' => $kelpolrihiv, 'umumhiv' => $umumhiv, 'bpjshiv' => $bpjshiv, 'otherhiv' => $otherhiv, 'total_hiv' => $total_hiv,
            'anggotatb' => $anggotatb, 'pnstb' => $pnstb, 'dikbangtb' => $dikbangtb, 'diktuktb' => $diktuktb, 'kelpolritb' => $kelpolritb, 'umumtb' => $umumtb, 'bpjstb' => $bpjstb, 'othertb' => $othertb, 'total_tb' => $total_tb,
            'anggotamalaria' => $anggotamalaria, 'pnsmalaria' => $pnsmalaria, 'dikbangmalaria' => $dikbangmalaria, 'diktukmalaria' => $diktukmalaria, 'kelpolrimalaria' => $kelpolrimalaria, 'umummalaria' => $umummalaria, 'bpjsmalaria' => $bpjsmalaria, 'othermalaria' => $othermalaria, 'total_malaria' => $total_malaria,
            'anggotadbd' => $anggotadbd, 'pnsdbd' => $pnsdbd, 'dikbangdbd' => $dikbangdbd, 'diktukdbd' => $diktukdbd, 'kelpolridbd' => $kelpolridbd, 'umumdbd' => $umumdbd, 'bpjsdbd' => $bpjsdbd, 'otherdbd' => $otherdbd, 'total_dbd' => $total_dbd,
            'anggotapms' => $anggotapms, 'pnspms' => $pnspms, 'dikbangpms' => $dikbangpms, 'diktukpms' => $diktukpms, 'kelpolripms' => $kelpolripms, 'umumpms' => $umumpms, 'bpjspms' => $bpjspms, 'otherpms' => $otherpms, 'total_pms' => $total_pms,
            'anggotahepatitis' => $anggotahepatitis, 'pnshepatitis' => $pnshepatitis, 'dikbanghepatitis' => $dikbanghepatitis, 'diktukhepatitis' => $diktukhepatitis, 'kelpolrihepatitis' => $kelpolrihepatitis, 'umumhepatitis' => $umumhepatitis, 'bpjshepatitis' => $bpjshepatitis, 'otherhepatitis' => $otherhepatitis, 'total_hepatitis' => $total_hepatitis,
            'anggotacovid' => $anggotacovid, 'pnscovid' => $pnscovid, 'dikbangcovid' => $dikbangcovid, 'diktukcovid' => $diktukcovid, 'kelpolricovid' => $kelpolricovid, 'umumcovid' => $umumcovid, 'bpjscovid' => $bpjscovid, 'othercovid' => $othercovid, 'total_covid' => $total_covid,
            'hospitalInfo' => $hospitalInfo
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');

        // Generate filename
        $filename = 'Laporan_Penyakit_Menular_' . date('d-m-Y', strtotime($formattedTgl1)) . '_sd_' . date('d-m-Y', strtotime($formattedTgl2)) . '.pdf';

        return $pdf->download($filename);
    }

    //START LAPORAN IGD

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

        $tahun = $request->input('tahun', $tgl1->format('Y'));
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

        // START GAWIANKU

        // Wajib didefinisikan dulu
        $pulang = 0;
        $rri = $rri ?? 0; // kalau rri sudah dihitung sebelumnya
        $rujukKeluar = 0;
        $meninggal = 0;
        $lainnya = 0;


        $pulang = DB::table('reg_periksa as rp')
        ->leftJoin('kamar_inap as ki', 'ki.no_rawat', '=', 'rp.no_rawat')
        ->leftJoin('rujuk as r', 'r.no_rawat', '=', 'rp.no_rawat')
        ->leftJoin('pasien_mati as pm', 'pm.no_rkm_medis', '=', 'rp.no_rkm_medis')
        ->whereIn('rp.kd_poli', ['IGDK', 'igd', 'PNK'])
        ->whereIn('rp.stts', ['Sudah', 'Belum'])
        ->whereNull('ki.no_rawat')      // tidak rawat inap
        ->whereNull('r.no_rawat')       // tidak rujuk keluar
        ->whereNull('pm.no_rkm_medis')  // tidak meninggal
        ->whereYear('rp.tgl_registrasi', $tahun)
        ->distinct('rp.no_rkm_medis')
        ->count('rp.no_rkm_medis');

        $rri = DB::table('reg_periksa as rp')
        ->join('kamar_inap as ki', 'ki.no_rawat', '=', 'rp.no_rawat')
        ->whereIn('rp.kd_poli', ['IGDK', 'igd', 'PNK'])
        ->whereYear('rp.tgl_registrasi', $tahun)
        ->distinct('rp.no_rkm_medis')
        ->count('rp.no_rkm_medis');

        $rujukKeluar = DB::table('reg_periksa as rp')
        ->join('rujuk as r', 'r.no_rawat', '=', 'rp.no_rawat')
        ->whereIn('rp.kd_poli', ['IGDK', 'igd', 'PNK'])
        ->whereYear('rp.tgl_registrasi', $tahun)
        ->distinct('rp.no_rkm_medis')
        ->count('rp.no_rkm_medis');

        $meninggalIgd = DB::table('reg_periksa as rp')
        ->join('pasien_mati as pm', 'pm.no_rkm_medis', '=', 'rp.no_rkm_medis')
        ->leftJoin('kamar_inap as ki', 'ki.no_rawat', '=', 'rp.no_rawat')
        ->leftJoin('rujuk as r', 'r.no_rawat', '=', 'rp.no_rawat')
        ->whereIn('rp.kd_poli', ['IGDK', 'igd', 'PNK'])
        ->whereYear('rp.tgl_registrasi', $tahun)
        ->whereNull('ki.no_rawat')   // ❌ belum masuk rawat inap
        ->whereNull('r.no_rawat')    // ❌ belum dirujuk
        ->distinct('rp.no_rkm_medis')
        ->count('rp.no_rkm_medis');

        $lainnya = DB::table('reg_periksa as rp')
        ->leftJoin('kamar_inap as ki', 'ki.no_rawat', '=', 'rp.no_rawat')
        ->leftJoin('rujuk as r', 'r.no_rawat', '=', 'rp.no_rawat')
        ->leftJoin('pasien_mati as pm', 'pm.no_rkm_medis', '=', 'rp.no_rkm_medis')
        ->whereIn('rp.kd_poli', ['IGDK', 'igd', 'PNK'])
        ->whereYear('rp.tgl_registrasi', $tahun)

        ->whereNull('ki.no_rawat')   // bukan RRI
        ->whereNull('r.no_rawat')    // bukan rujuk
        ->whereNull('pm.no_rkm_medis')   // bukan meninggal

        // kalau sembuh ditentukan dari stts tertentu, misalnya:
        ->whereNotIn('rp.stts', ['Sudah'])  // sesuaikan definisi sembuhmu

        ->distinct('rp.no_rkm_medis')
        ->count('rp.no_rkm_medis');

        // Total dihitung dari SEMUA baris yang ditampilkan
        $total = $pulang + $rri + $rujukKeluar + $meninggalIgd + $lainnya;

        // Persentase
        $persenPulang = $total > 0 ? round(($pulang / $total) * 100, 2) : 0;
        $persenRri = $total > 0 ? round(($rri / $total) * 100, 2) : 0;
        $persenRujuk = $total > 0 ? round(($rujukKeluar / $total) * 100, 2) : 0;
        $persenMeninggalIgd = $total > 0 ? round(($meninggalIgd / $total) * 100, 2): 0;
        $persenLainnya = $total > 0 ? round(($lainnya / $total) * 100, 2) : 0;

        $rekapBulanan = DB::select("
            SELECT
                MONTH(tgl_registrasi) AS bulan,
                COUNT(CASE WHEN kd_poli in ('IGDK', 'igd') THEN 1 END) AS igd,
                COUNT(CASE WHEN kd_poli = 'PNK' THEN 1 END) AS ponek
            FROM reg_periksa
            WHERE YEAR(tgl_registrasi) = ?
            GROUP BY MONTH(tgl_registrasi)
            ORDER BY bulan
        ", [$tahun]);

        $bulan = [
            1=>'Januari','Februari','Maret','April','Mei','Juni',
            'Juli','Agustus','September','Oktober','November','Desember'
        ];
    

        // Ubah hasil query jadi collection dan keyBy bulan
        $data = collect($rekapBulanan)->keyBy('bulan');
        
        $rows = [];

        $totalIgd = 0;
        $totalPonek = 0;
        
        for ($i = 1; $i <= 12; $i++) {
            $igd   = $data[$i]->igd   ?? 0;
            $ponek = $data[$i]->ponek ?? 0;
        
            $rows[] = [
                'bulan' => $bulan[$i],
                'igd'   => $igd,
                'ponek' => $ponek,
            ];
        
            $totalIgd   += $igd;
            $totalPonek += $ponek;
        }

        // data kematian pasien igd dan ponek
        $sort  = $request->get('sort', 'ps.nm_pasien');
        $order = $request->get('order', 'asc');

        // whitelist kolom sortable (ANTI SQL INJECTION)
        $allowedSort = [
            'nm_pasien' => 'ps.nm_pasien',
            'no_rkm_medis' => 'pm.no_rkm_medis',
            'kd_poli' => 'rp.kd_poli'
        ];

        $sortColumn = $allowedSort[$sort] ?? 'ps.nm_pasien';

        // ===============================
        // DATA KEMATIAN PASIEN IGD & PONEK
        // ===============================
        $dataKematian = DB::table('pasien_mati as pm')
        ->join('pasien as ps', 'ps.no_rkm_medis', '=', 'pm.no_rkm_medis')
        ->join('reg_periksa as rp', 'rp.no_rkm_medis', '=', 'ps.no_rkm_medis')
        ->whereYear('rp.tgl_registrasi', $tahun)
        ->whereIn('rp.kd_poli', ['IGDK', 'igd', 'PNK'])
        ->select(
            'pm.no_rkm_medis',
            'ps.nm_pasien',
            'ps.alamat',
            'rp.kd_poli',
            'pm.icd1',
            'pm.icd2',
            'pm.icd3',
            'pm.icd4'
        )
        ->distinct()
        ->orderBy($sortColumn, $order)
        ->get();

        // ===============================
        // TOP 10 PENYAKIT IGD + PONEK
        // ===============================
        $topPenyakit = DB::table('reg_periksa as rp')
        ->join('diagnosa_pasien as dp', 'dp.no_rawat', '=', 'rp.no_rawat')
        ->join('penyakit as p', 'p.kd_penyakit', '=', 'dp.kd_penyakit')
        ->whereIn('rp.kd_poli', ['IGDK','igd'])
        ->whereYear('rp.tgl_registrasi', $tahun)
        ->select(
            'dp.kd_penyakit',
            'p.nm_penyakit',
            DB::raw('COUNT(DISTINCT rp.no_rawat) as jumlah_kasus')
        )
        ->groupBy('dp.kd_penyakit','p.nm_penyakit')
        ->orderByDesc('jumlah_kasus')
        ->limit(10)
        ->get();

        // Check if PDF download is requested - CEK SEBELUM return view
        if ($request->has('download_pdf')) {
            return $this->generateIgdPDF(
                $formattedTgl1,
                $formattedTgl2,
                $tanggal,
                $tahun,
                $sqligd,
                $pulang, $rri, $rujukKeluar, $meninggalIgd, $lainnya, $total,
                $persenPulang, $persenRri, $persenRujuk, $persenMeninggalIgd, $persenLainnya,
                $bulan, $data, $totalIgd, $totalPonek,
                $topPenyakit
            );
        }

        return $this->laporanView($request, 'laporan_igd', [

            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,
            'igd' => $sqligd,

            // START GAWIANKU
            // hasil siap tampil
            'rows' => $rows,
            'totalIgd' => $totalIgd,
            'totalPonek' => $totalPonek,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'data' => $data,
            'dataKematian' => $dataKematian,
            'sort' => $sort,
            'order' => $order,

            'rri' => $rri,
            'pulang' => $pulang,
            'rujukKeluar' => $rujukKeluar,
            'meninggalIgd' => $meninggalIgd,
            'lainnya' => $lainnya,


            'total' => $total,
            'persenRri' => $persenRri,
            'persenPulang' => $persenPulang,
            'persenRujuk' => $persenRujuk,
            'persenMeninggalIgd' => $persenMeninggalIgd,
            'persenLainnya' => $persenLainnya,

            'topPenyakit' => $topPenyakit,

        ], 'igd');
    }

    private function generateIgdPDF(
        $formattedTgl1,
        $formattedTgl2,
        $tanggal,
        $tahun,
        $igd,
        $pulang, $rri, $rujukKeluar, $meninggalIgd, $lainnya, $total,
        $persenPulang, $persenRri, $persenRujuk, $persenMeninggalIgd, $persenLainnya,
        $bulan, $data, $totalIgd, $totalPonek,
        $topPenyakit
    ) {
        // Get hospital info
        $hospitalInfo = DB::table('setting')->first();

        $pdf = PDF::loadView('rm.laporan_rm.laporan_igd_pdf', [
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,
            'tgllap' => $tanggal,
            'tahun' => $tahun,
            'igd' => $igd,
            'pulang' => $pulang,
            'rri' => $rri,
            'rujukKeluar' => $rujukKeluar,
            'meninggalIgd' => $meninggalIgd,
            'lainnya' => $lainnya,
            'total' => $total,
            'persenPulang' => $persenPulang,
            'persenRri' => $persenRri,
            'persenRujuk' => $persenRujuk,
            'persenMeninggalIgd' => $persenMeninggalIgd,
            'persenLainnya' => $persenLainnya,
            'bulan' => $bulan,
            'data' => $data,
            'totalIgd' => $totalIgd,
            'totalPonek' => $totalPonek,
            'topPenyakit' => $topPenyakit,
            'hospitalInfo' => $hospitalInfo
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');

        // Generate filename
        $filename = 'Laporan_IGD_' . date('d-m-Y', strtotime($formattedTgl1)) . '_sd_' . date('d-m-Y', strtotime($formattedTgl2)) . '.pdf';

        return $pdf->download($filename);
    }

    // END LAPORAN IGD

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

        // PDF Download - CEK SEBELUM return view
        if ($request->has('download_pdf')) {
            $pdf = PDF::loadView('rm.laporan_rm.kegiatan_operasi_pdf', [
                'tgl1' => $formattedTgl1,
                'tgl2' => $formattedTgl2,
                'tgllap' => $tanggal,
                'op' => $sqlop,
            ]);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Laporan_Kegiatan_Operasi_' . $formattedTgl1 . '_sd_' . $formattedTgl2 . '.pdf';
            return $pdf->download($filename);
        }

        return $this->laporanView($request, 'kegiatan_operasi', [

            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,

            'tgllap' => $tanggal,
            'op' => $sqlop,
        ], 'operasi');
    }

    public function kematian(Request $request)
    {
        //format tanggal
        // Get input values
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');
        $limitDiagnosaKematian = $request->input('limit_diagnosa_kematian', 20);

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

        // Start Diagnosa Penyebab Kematian
        $diagnosaKematian = DB::table('reg_periksa as rp')
            ->join('diagnosa_pasien as dp', 'dp.no_rawat', '=', 'rp.no_rawat')
            ->join('penyakit as p', 'p.kd_penyakit', '=', 'dp.kd_penyakit')
            ->where(function($q) {
                $q->where('rp.stts', 'Meninggal')
                  ->orWhereExists(function($subq) {
                      $subq->select(DB::raw(1))
                          ->from('kamar_inap as ki')
                          ->whereRaw('ki.no_rawat = rp.no_rawat')
                          ->where('ki.stts_pulang', 'Meninggal');
                  });
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('rp.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(
                'p.kd_penyakit',
                DB::raw('LEFT(p.nm_penyakit, 60) as nm_penyakit'),
                DB::raw('COUNT(DISTINCT rp.no_rawat) as total')
            )
            ->groupBy('p.kd_penyakit', 'p.nm_penyakit')
            ->orderBy('total', 'desc')
            ->limit($limitDiagnosaKematian)
            ->get();

        $totalSemuaDiagnosa = $diagnosaKematian->sum('total');
        // End Diagnosa Penyebab Kematian

        // PDF Download - CEK SEBELUM return view
        if ($request->has('download_pdf')) {
            $pdf = PDF::loadView('rm.laporan_rm.laporan_kematian_pdf', [
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
                'diagnosaKematian' => $diagnosaKematian,
                'totalSemuaDiagnosa' => $totalSemuaDiagnosa,
                'limitDiagnosaKematian' => $limitDiagnosaKematian,
            ]);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Laporan_Kematian_' . $formattedTgl1 . '_sd_' . $formattedTgl2 . '.pdf';
            return $pdf->download($filename);
        }

        return $this->laporanView($request, 'laporan_kematian', [

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
            'diagnosaKematian' => $diagnosaKematian,
            'totalSemuaDiagnosa' => $totalSemuaDiagnosa,
            'limitDiagnosaKematian' => $limitDiagnosaKematian,
        ], 'kematian');
    }

    public function downloadDiagnosaKematianExcel(Request $request)
    {
        // Ambil parameter yang sama dengan method kematian()
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');
        $limitDiagnosaKematian = $request->input('limit_diagnosa_kematian', 20);

        if (empty($tgl1Input)) {
            $tgl1 = new \DateTime(date('Y-m-01'));
        } else {
            $tgl1 = new \DateTime($tgl1Input);
        }
        if (empty($tgl2Input)) {
            $tgl2 = new \DateTime();
        } else {
            $tgl2 = new \DateTime($tgl2Input);
        }

        $formattedTgl1 = $tgl1->format('Y-m-d');
        $formattedTgl2 = $tgl2->format('Y-m-d');

        // Format judul tanggal
        $dateStartFormatted = strtoupper($tgl1->format('d F Y'));
        $dateEndFormatted = strtoupper($tgl2->format('d F Y'));
        $periodTitle = $dateStartFormatted . ' S/D ' . $dateEndFormatted;

        // Query diagnosa kematian (sama dengan method kematian)
        $diagnosaKematian = DB::table('reg_periksa as rp')
            ->join('diagnosa_pasien as dp', 'dp.no_rawat', '=', 'rp.no_rawat')
            ->join('penyakit as p', 'p.kd_penyakit', '=', 'dp.kd_penyakit')
            ->where(function($q) {
                $q->where('rp.stts', 'Meninggal')
                  ->orWhereExists(function($subq) {
                      $subq->select(DB::raw(1))
                          ->from('kamar_inap as ki')
                          ->whereRaw('ki.no_rawat = rp.no_rawat')
                          ->where('ki.stts_pulang', 'Meninggal');
                  });
            })
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('rp.tgl_registrasi', [$tgl1, $tgl2]);
            })
            ->select(
                'p.kd_penyakit',
                DB::raw('LEFT(p.nm_penyakit, 60) as nm_penyakit'),
                DB::raw('COUNT(DISTINCT rp.no_rawat) as total')
            )
            ->groupBy('p.kd_penyakit', 'p.nm_penyakit')
            ->orderBy('total', 'desc')
            ->limit($limitDiagnosaKematian)
            ->get();

        $totalSemuaDiagnosa = $diagnosaKematian->sum('total');

        // Buat Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Title Header
        $sheet->setCellValue('A1', $limitDiagnosaKematian . ' PENYAKIT PENYEBAB KEMATIAN PASIEN RAWAT INAP MENURUT BAB ICD-X DI RUMAH SAKIT');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'KABUPATEN KOTABARU');
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', $periodTitle);
        $sheet->mergeCells('A3:D3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Table Header (Baris 4 & 5)
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Diagnosa');
        $sheet->setCellValue('C4', 'Jumlah');
        $sheet->setCellValue('D4', '%');

        // Merge header rows
        $sheet->mergeCells('A4:A5');
        $sheet->mergeCells('B4:B5');
        $sheet->mergeCells('C4:C5');
        $sheet->mergeCells('D4:D5');

        // Baris 5 sub-header (kosong karena tidak ada sub-kolom)
        // Style Header
        $headerRange = 'A4:D5';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ]
        ]);

        // 3. Isi Data
        $row = 6;
        $totalJumlah = 0;

        foreach ($diagnosaKematian as $idx => $item) {
            $persen = $totalSemuaDiagnosa > 0 ? round($item->total / $totalSemuaDiagnosa * 100, 1) : 0;

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $item->nm_penyakit . ' (' . $item->kd_penyakit . ')');
            $sheet->setCellValue('C' . $row, $item->total);
            $sheet->setCellValue('D' . $row, $persen);

            $totalJumlah += $item->total;
            $row++;
        }

        // Baris Total
        $sheet->setCellValue('A' . $row, 'J u m l a h');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->setCellValue('C' . $row, $totalJumlah);
        $sheet->setCellValue('D' . $row, '100%');

        // Style Data Rows
        $dataRange = 'A6:D' . ($row - 1);
        if ($row > 6) {
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            // Kolom diagnosa rata kiri
            $sheet->getStyle('B6:B' . ($row - 1))->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'wrapText' => true]
            ]);
        }

        // Style Baris Total
        $totalRange = 'A' . $row . ':D' . $row;
        $sheet->getStyle($totalRange)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Column Width
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(55);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(10);

        $sheet->getRowDimension(4)->setRowHeight(25);
        $sheet->getRowDimension(5)->setRowHeight(25);

        // Nama File
        $fileName = 'Data_Diagnosa_Kematian_' . date('d-m-Y', strtotime($formattedTgl1)) . '_sd_' . date('d-m-Y', strtotime($formattedTgl2)) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
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

        // PDF Download - CEK SEBELUM return view
        if ($request->has('download_pdf')) {
            $pdf = PDF::loadView('rm.laporan_rm.pertumbuhan_pdf', [
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
                'sqllab' => $sqllabnow,
                'pertumbuhan_lab' => $pertumbuhan_lab,
                'sqlrad' => $sqlradnow,
                'pertumbuhan_rad' => $pertumbuhan_rad,
            ]);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Laporan_Pertumbuhan_' . $formattedTgl1 . '_sd_' . $formattedTgl2 . '.pdf';
            return $pdf->download($filename);
        }

        return $this->laporanView($request, 'pertumbuhan', [

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



        ], 'pertumbuhan');
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

        // PDF Download - CEK SEBELUM return view
        if ($request->has('download_pdf')) {
            $pdf = PDF::loadView('rm.laporan_rm.laporan_radlab_pdf', [
                'tgl1' => $tgl1->format('Y-m-d'),
                'tgl2' => $tgl2->format('Y-m-d'),
                'tgllap' => $tanggal,
                'totalRadiologi' => $sqlTotalRadiologi,
                'totalLab' => $sqlTotalLab
            ]);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Laporan_Radiologi_Laboratorium_' . $formattedTgl1 . '_sd_' . $formattedTgl2 . '.pdf';
            return $pdf->download($filename);
        }

        return $this->laporanView($request, 'laporan_radlab', [
            'tgl1' => $tgl1->format('Y-m-d'),
            'tgl2' => $tgl2->format('Y-m-d'),
            'tgllap' => $tanggal,
            'totalRadiologi' => $sqlTotalRadiologi,
            'totalLab' => $sqlTotalLab
        ], 'radlab');
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

        // PDF Download - CEK SEBELUM return view
        if ($request->has('download_pdf')) {
            $pdf = PDF::loadView('rm.laporan_rm.ibudanbayi_pdf', [
                'tgl1' => $formattedTgl1,
                'tgl2' => $formattedTgl2,
                'tgllap' => $tanggal,
                'bayilahir' => $bayi_lahir,
                'bayimati' => $bayi_mati,
                'bayimatiranap' => $bayi_matiranap,
                'bayi25' => $bayi_lahir_2setkilolebih,
                'bayi24' => $bayi_lahir_2setkilokur,
                'total_lahirmati' => $total_lahirmati,
                'total_berat' => $total_berat,
            ]);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Laporan_Ibu_dan_Bayi_' . $formattedTgl1 . '_sd_' . $formattedTgl2 . '.pdf';
            return $pdf->download($filename);
        }

        return $this->laporanView($request, 'ibudanbayi', [
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
        ], 'ibudanbayi'); // by Ihsan

    }

    // PASIEN MENINGGAL
    public function pasienMeninggal(Request $request)
    {
        $user = Auth::user();
        $tanggalAwal = $request->input('tanggal_awal', date('Y-m-01'));
        $tanggalAkhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $bangsal = $request->input('bangsal', '');

        $data = [];
        $totalData = [];

        if ($request->has('tanggal_awal') && $request->has('tanggal_akhir')) {

            // Query data pasien meninggal
            $query = DB::select("
                SELECT 
                    tgl_registrasi, no_rawat, no_rkm_medis, jenis_pasien, nm_pasien,
                    alamat, jk, no_ktp, tgl_lahir, umurdaftar, png_jawab, nm_penyakit,
                    kd_penyakit, prioritas, kd_dokter, nm_dokter, kd_sps, nm_sps,
                    status_lanjut, kd_kamar, kelas, tgl_masuk, jam_masuk, tgl_keluar,
                    jam_keluar, stts_pulang, kd_bangsal, nm_bangsal, kd_dokter_dpjp,
                    nm_dokter_dpjp, json_dpjp
                FROM (
                    SELECT *,
                        ROW_NUMBER() OVER (
                            PARTITION BY no_rawat 
                            ORDER BY -prioritas DESC, kd_penyakit ASC
                        ) as rn
                    FROM laporan_sensus_pasien_ranap t
                    WHERE t.tgl_masuk >= ?
                    AND t.tgl_masuk <= ?
                    AND t.stts_pulang = 'Meninggal'
                    " . ($bangsal ? "AND t.kd_bangsal = ?" : "") . "
                ) ranked
                WHERE rn = 1
                ORDER BY tgl_masuk ASC, no_rkm_medis ASC
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
                    'nm_bangsal' => $item->nm_bangsal,
                    'item' => $item,
                ];
            });

            // Buat data untuk tabel kedua (ringkasan diagnosa)
            $totalData = $this->hitungTotalDiagnosa($data);

            // Check if PDF download is requested
            if ($request->has('download_pdf')) {
                return $this->generatePasienMeninggalPDF($tanggalAwal, $tanggalAkhir, $bangsal, $data, $totalData);
            }
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

    private function generatePasienMeninggalPDF($tanggalAwal, $tanggalAkhir, $bangsal, $data, $totalData)
    {
        // Get hospital info
        $hospitalInfo = DB::table('setting')->first();

        // Get bangsal name if specific bangsal is selected
        $bangsalName = '';
        if ($bangsal) {
            $bangsalInfo = DB::table('bangsal')->where('kd_bangsal', $bangsal)->first();
            $bangsalName = $bangsalInfo ? $bangsalInfo->nm_bangsal : '';
        }

        // Calculate additional statistics
        $totalPasien = $data->count();
        $totalLaki = $data->where('jk', 'L')->count();
        $totalPerempuan = $data->where('jk', 'P')->count();
        $totalMeninggalKurang48 = $data->where('meninggal_kurang_48jam', 'Ya')->count();
        $totalMeninggalLebih48 = $data->where('meninggal_lebih_48jam', 'Ya')->count();

        // Group by bangsal
        $pasienPerBangsal = $data->groupBy('nm_bangsal')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->toArray();

        $pdf = PDF::loadView('rm.laporan_rm.pasien_meninggal_pdf', [
            'data' => $data,
            'totalData' => $totalData,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'bangsal' => $bangsal,
            'bangsalName' => $bangsalName,
            'totalPasien' => $totalPasien,
            'totalLaki' => $totalLaki,
            'totalPerempuan' => $totalPerempuan,
            'totalMeninggalKurang48' => $totalMeninggalKurang48,
            'totalMeninggalLebih48' => $totalMeninggalLebih48,
            'pasienPerBangsal' => $pasienPerBangsal,
            'hospitalInfo' => $hospitalInfo
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');

        // Generate filename
        $filename = 'Laporan_Pasien_Meninggal_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir));
        if (!empty($bangsalName)) {
            $filename .= '_' . str_replace(' ', '_', $bangsalName);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }


    protected function hitungTotalDiagnosa($data)
    {
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

    public function getBangsal(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $data = $this->getBangsalData($tanggalAwal, $tanggalAkhir);

        return response()->json([
            'success' => true,
            'data' => $data['data']
        ]);
    }

    public function getBangsalMeninggal(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $data = $this->getBangsalData($tanggalAwal, $tanggalAkhir, true);

        return response()->json([
            'success' => true,
            'data' => $data['data']
        ]);
    }
    private function getBangsalData($tanggalAwal, $tanggalAkhir, $meninggal = false)
    {
        // Jika ada tanggal awal dan akhir, ambil bangsal berdasarkan data pasien
        if ($tanggalAwal && $tanggalAkhir) {

            $bangsal = DB::table('laporan_sensus_pasien_ranap as t')
                ->join('bangsal', 'bangsal.kd_bangsal', '=', 't.kd_bangsal')
                ->select('bangsal.kd_bangsal', 'bangsal.nm_bangsal')
                ->where('t.tgl_masuk', '>=', $tanggalAwal)
                ->where('t.tgl_masuk', '<=', $tanggalAkhir);

            if ($meninggal) {
                $bangsal = $bangsal->where('t.stts_pulang', '=', 'Meninggal');
            } else {
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
            if ($meninggal) {
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
    public function laporanRujukanKeluar(Request $request)
    {
        // Set default dates (current month) if not provided
        $tanggalAwal = $request->input('tanggal_awal') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // Check if PDF download is requested
        if ($request->has('download_pdf')) {
            $keyword = $request->input('keyword', '');

            // Build base query for PDF
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

            // Apply keyword search if provided
            if (!empty($keyword)) {
                $baseQuery->where(function ($q) use ($keyword) {
                    $searchTerm = '%' . $keyword . '%';
                    $q->where('rujuk.no_rujuk', 'like', $searchTerm)
                        ->orWhere('rujuk.no_rawat', 'like', $searchTerm)
                        ->orWhere('reg_periksa.no_rkm_medis', 'like', $searchTerm)
                        ->orWhere('pasien.nm_pasien', 'like', $searchTerm)
                        ->orWhere('rujuk.rujuk_ke', 'like', $searchTerm)
                        ->orWhere('rujuk.keterangan_diagnosa', 'like', $searchTerm)
                        ->orWhere('rujuk.kd_dokter', 'like', $searchTerm)
                        ->orWhere('dokter.nm_dokter', 'like', $searchTerm)
                        ->orWhere('rujuk.kat_rujuk', 'like', $searchTerm)
                        ->orWhere('rujuk.keterangan', 'like', $searchTerm);
                });
            }

            return $this->generateRujukanKeluarPDF($tanggalAwal, $tanggalAkhir, $keyword, $baseQuery);
        }

        // Check if this is an AJAX request for DataTables
        if ($request->ajax()) {
            return $this->getRujukanKeluarDataTables($request);
        }

        // Build base query for statistics
        $baseQuery = DB::table('rujuk')
            ->join('reg_periksa', 'rujuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('dokter', 'rujuk.kd_dokter', '=', 'dokter.kd_dokter')
            ->whereBetween('rujuk.tgl_rujuk', [$tanggalAwal, $tanggalAkhir]);

        // Calculate statistics
        $totalPasien = $baseQuery->count();

        // Pasien per tanggal rujuk
        $pasienPerTanggal = DB::table('rujuk')
            ->join('reg_periksa', 'rujuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->whereBetween('rujuk.tgl_rujuk', [$tanggalAwal, $tanggalAkhir])
            ->select('rujuk.tgl_rujuk', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('rujuk.tgl_rujuk')
            ->orderBy('rujuk.tgl_rujuk')
            ->get()
            ->pluck('jumlah', 'tgl_rujuk')
            ->toArray();

        // Pasien per tempat rujuk
        $pasienPerTempatRujuk = DB::table('rujuk')
            ->join('reg_periksa', 'rujuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->whereBetween('rujuk.tgl_rujuk', [$tanggalAwal, $tanggalAkhir])
            ->select('rujuk.rujuk_ke', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('rujuk.rujuk_ke')
            ->orderByDesc('jumlah')
            ->get()
            ->pluck('jumlah', 'rujuk_ke')
            ->toArray();

        // Pasien per diagnosa
        $pasienPerDiagnosa = DB::table('rujuk')
            ->join('reg_periksa', 'rujuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->whereBetween('rujuk.tgl_rujuk', [$tanggalAwal, $tanggalAkhir])
            ->select('rujuk.keterangan_diagnosa', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('rujuk.keterangan_diagnosa')
            ->orderByDesc('jumlah')
            ->get()
            ->pluck('jumlah', 'keterangan_diagnosa')
            ->toArray();

        return view('rm.laporan_rm.rujukan_keluar', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'totalPasien' => $totalPasien,
            'pasienPerTanggal' => $pasienPerTanggal,
            'pasienPerTempatRujuk' => $pasienPerTempatRujuk,
            'pasienPerDiagnosa' => $pasienPerDiagnosa
        ]);
    }

    private function getRujukanKeluarDataTables(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $query = DB::table('rujuk')
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

        $datatables = datatables()->of($query)
            ->addIndexColumn()
            ->editColumn('tgl_rujuk', function ($row) {
                return date('d-m-Y', strtotime($row->tgl_rujuk));
            })
            ->filterColumn('tgl_rujuk', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(rujuk.tgl_rujuk,'%d-%m-%Y') like ?", ["%$keyword%"]);
            });

        return $datatables->make(true);
    }

    private function generateRujukanKeluarPDF($tanggalAwal, $tanggalAkhir, $keyword, $baseQuery)
    {
        // Get all data (not paginated) for PDF
        $allData = $baseQuery->orderBy('rujuk.tgl_rujuk', 'desc')->get();

        // Calculate statistics for PDF
        $totalPasien = $allData->count();

        // Group data for statistics
        $pasienPerTanggal = $allData->groupBy('tgl_rujuk')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortKeys()
            ->toArray();

        $pasienPerTempatRujuk = $allData->groupBy('rujuk_ke')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->toArray();

        $pasienPerDiagnosa = $allData->groupBy('keterangan_diagnosa')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->toArray();

        $pasienPerDokter = $allData->groupBy('nm_dokter')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->toArray();

        // Get hospital info
        $hospitalInfo = DB::table('setting')->first();

        $pdf = PDF::loadView('rm.laporan_rm.rujukan_keluar_pdf', [
            'data' => $allData,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'keyword' => $keyword,
            'totalPasien' => $totalPasien,
            'pasienPerTanggal' => $pasienPerTanggal,
            'pasienPerTempatRujuk' => $pasienPerTempatRujuk,
            'pasienPerDiagnosa' => $pasienPerDiagnosa,
            'pasienPerDokter' => $pasienPerDokter,
            'hospitalInfo' => $hospitalInfo
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');

        // Generate filename
        $filename = 'Laporan_Rujukan_Keluar_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir));
        if (!empty($keyword)) {
            $filename .= '_' . str_replace(' ', '_', $keyword);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }


    // Laporan RUJUKAN MASUK
    public function laporanRujukanMasuk(Request $request)
    {
        // Set default dates (current month) if not provided
        $tanggalAwal = $request->input('tanggal_awal') ?? date('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? date('Y-m-d');

        // Check if PDF download is requested
        if ($request->has('download_pdf')) {
            $keyword = $request->input('keyword', '');

            // Build base query for PDF
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

            // Apply keyword search if provided (sama seperti kode asli)
            if (!empty($keyword)) {
                $baseQuery->where(function ($q) use ($keyword) {
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

            return $this->generateRujukanMasukPDF($tanggalAwal, $tanggalAkhir, $keyword, $baseQuery);
        }

        // Check if this is an AJAX request for DataTables
        if ($request->ajax()) {
            return $this->getRujukanMasukDataTables($request);
        }

        // Build base query for statistics
        $baseQuery = DB::table('reg_periksa')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('rujuk_masuk', 'reg_periksa.no_rawat', '=', 'rujuk_masuk.no_rawat')
            ->join('penyakit', 'penyakit.kd_penyakit', '=', 'rujuk_masuk.kd_penyakit')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir]);

        // Calculate statistics
        $totalPasien = $baseQuery->count();

        // Group data for statistics
        $pasienPerPerujuk = DB::table('reg_periksa')
            ->join('rujuk_masuk', 'reg_periksa.no_rawat', '=', 'rujuk_masuk.no_rawat')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->select('rujuk_masuk.perujuk', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('rujuk_masuk.perujuk')
            ->orderByDesc('jumlah')
            ->get()
            ->pluck('jumlah', 'perujuk')
            ->toArray();

        $pasienPerTanggal = DB::table('reg_periksa')
            ->join('rujuk_masuk', 'reg_periksa.no_rawat', '=', 'rujuk_masuk.no_rawat')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
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
            ->select('poliklinik.nm_poli', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('poliklinik.nm_poli')
            ->orderByDesc('jumlah')
            ->get()
            ->pluck('jumlah', 'nm_poli')
            ->toArray();

        return view('rm.laporan_rm.rujukan_masuk', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'totalPasien' => $totalPasien,
            'pasienPerPerujuk' => $pasienPerPerujuk,
            'pasienPerTanggal' => $pasienPerTanggal,
            'pasienPerDiagnosa' => $pasienPerDiagnosa,
            'pasienPerPoli' => $pasienPerPoli
        ]);
    }

    private function getRujukanMasukDataTables(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $query = DB::table('reg_periksa')
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

        $datatables = datatables()->of($query)
            ->addIndexColumn()
            ->editColumn('tgl_registrasi', function ($row) {
                return date('d-m-Y', strtotime($row->tgl_registrasi));
            })
            ->editColumn('diagnosa', function ($row) {
                return $row->kd_penyakit . ' - ' . $row->nm_penyakit;
            })
            ->filterColumn('umur', function ($query, $keyword) {
                $query->whereRaw(
                    "CONCAT(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) like ?",
                    ["%$keyword%"]
                );
            })
            ->filterColumn('tgl_registrasi', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(reg_periksa.tgl_registrasi,'%d-%m-%Y') like ?", ["%$keyword%"]);
            })
            ->filterColumn('diagnosa', function ($query, $keyword) {
                $query->whereRaw("CONCAT(penyakit.kd_penyakit, ' - ', penyakit.nm_penyakit) like ?", ["%$keyword%"]);
            });

        return $datatables->make(true);
    }

    private function generateRujukanMasukPDF($tanggalAwal, $tanggalAkhir, $keyword, $baseQuery)
    {
        // Get all data (not paginated) for PDF
        $allData = $baseQuery->orderBy('reg_periksa.tgl_registrasi', 'desc')->get();

        // Calculate statistics for PDF
        $totalPasien = $allData->count();

        // Group data for statistics
        $pasienPerPerujuk = $allData->groupBy('perujuk')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->toArray();

        $pasienPerTanggal = $allData->groupBy('tgl_registrasi')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortKeys()
            ->toArray();

        $pasienPerDiagnosa = $allData->groupBy(function ($item) {
            return $item->kd_penyakit . ' - ' . $item->nm_penyakit;
        })
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->toArray();

        $pasienPerPoli = $allData->groupBy('nm_poli')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->toArray();

        // Get hospital info
        $hospitalInfo = DB::table('setting')->first();

        $pdf = PDF::loadView('rm.laporan_rm.rujukan_masuk_pdf', [
            'data' => $allData,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'keyword' => $keyword,
            'totalPasien' => $totalPasien,
            'pasienPerPerujuk' => $pasienPerPerujuk,
            'pasienPerTanggal' => $pasienPerTanggal,
            'pasienPerDiagnosa' => $pasienPerDiagnosa,
            'pasienPerPoli' => $pasienPerPoli,
            'hospitalInfo' => $hospitalInfo
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');

        // Generate filename
        $filename = 'Laporan_Rujukan_Masuk_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir));
        if (!empty($keyword)) {
            $filename .= '_' . str_replace(' ', '_', $keyword);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }
}

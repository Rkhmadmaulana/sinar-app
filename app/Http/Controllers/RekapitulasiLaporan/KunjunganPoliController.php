<?php

namespace App\Http\Controllers\RekapitulasiLaporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KunjunganPoliController extends Controller
{
    /**
     * Default penyakit/kasus dengan kode ICD-10
     */
    private function getDefaultPenyakit(): array
    {
        return [
            [
                'id'        => 'kanker',
                'nama'      => 'Kanker',
                'kode_icd'  => 'C00-C97',
                'deskripsi' => 'Neoplasma ganas',
                'color'     => '#FF6B6B',
            ],
            [
                'id'        => 'jantung',
                'nama'      => 'Jantung',
                'kode_icd'  => 'I20, I50',
                'deskripsi' => 'Angina pectoris dan Gagal jantung',
                'color'     => '#4ECDC4',
            ],
            [
                'id'        => 'jantung_kongenital',
                'nama'      => 'Jantung Kongenital',
                'kode_icd'  => 'Q20-Q28',
                'deskripsi' => 'Malformasi kongenital sistem kardiovaskular',
                'color'     => '#45B7D1',
            ],
            [
                'id'        => 'stroke',
                'nama'      => 'Stroke',
                'kode_icd'  => 'I60-I69',
                'deskripsi' => 'Penyakit serebrovaskular',
                'color'     => '#96CEB4',
            ],
            [
                'id'        => 'urenefrologi',
                'nama'      => 'Urenefrologi',
                'kode_icd'  => 'N00-N39',
                'deskripsi' => 'Penyakit sistem ginjal dan saluran kemih',
                'color'     => '#FFEAA7',
            ],
        ];
    }

    private function getDefaultYears(): array
    {
        return [(int) date('Y')];
    }

    /**
     * Parse ICD-10 code range untuk query
     */
    private function parseICDRange(string $kode_icd): array
    {
        $ranges = [];
        $parts  = array_map('trim', explode(',', $kode_icd));

        foreach ($parts as $part) {
            if (strpos($part, '-') !== false) {
                [$start, $end] = explode('-', $part, 2);
                $ranges[] = [
                    'type'         => 'range',
                    'start_letter' => substr($start, 0, 1),
                    'start_num'    => (int) substr($start, 1),
                    'end_letter'   => substr($end, 0, 1),
                    'end_num'      => (int) substr($end, 1),
                    'start_full'   => $start,
                    'end_full'     => $end,
                ];
            } else {
                $ranges[] = [
                    'type'      => 'single',
                    'full_code' => $part,
                ];
            }
        }

        return $ranges;
    }

    /**
     * Build WHERE clause for ICD range — returns ['clause' => string, 'bindings' => array]
     */
    private function buildICDWhereClause(array $ranges, string $field = 'dp.kd_penyakit'): array
    {
        $conditions = [];
        $bindings   = [];

        foreach ($ranges as $range) {
            if ($range['type'] === 'single') {
                $conditions[] = "({$field} LIKE ?)";
                $bindings[]   = $range['full_code'] . '%';
            } else {
                // Use string comparison — ICD codes are fixed-format (letter + 2-digit number)
                $startCode    = $range['start_letter'] . sprintf('%02d', $range['start_num']);
                $endCode      = $range['end_letter']   . sprintf('%02d', $range['end_num']) . 'Z';
                $conditions[] = "({$field} >= ? AND {$field} <= ?)";
                $bindings[]   = $startCode;
                $bindings[]   = $endCode;
            }
        }

        return [
            'clause'   => '(' . implode(' OR ', $conditions) . ')',
            'bindings' => $bindings,
        ];
    }

    /**
     * Get data Rawat Jalan per penyakit dan tahun
     */
    private function getRawatJalanData(array $penyakit, array $years): array
    {
        $data = [];

        foreach ($penyakit as $p) {
            $ranges   = $this->parseICDRange($p['kode_icd']);
            $icdWhere = $this->buildICDWhereClause($ranges);

            $data[$p['id']] = [
                'nama'     => $p['nama'],
                'kode_icd' => $p['kode_icd'],
                'color'    => $p['color'],
                'years'    => [],
            ];

            foreach ($years as $year) {
                $base = fn() => DB::table('reg_periksa as rp')
                    ->join('diagnosa_pasien as dp', 'rp.no_rawat', '=', 'dp.no_rawat')
                    ->where('rp.status_lanjut', 'Ralan')
                    ->whereYear('rp.tgl_registrasi', $year)
                    ->whereRaw($icdWhere['clause'], $icdWhere['bindings'])
                    ->where('dp.prioritas', 1);

                $pasienBaru = (clone $base())
                    ->where('rp.stts_daftar', 'Baru')
                    ->distinct('rp.no_rawat')
                    ->count('rp.no_rawat');

                $kunjungan = (clone $base())
                    ->where('rp.stts_daftar', 'Lama')
                    ->distinct('rp.no_rawat')
                    ->count('rp.no_rawat');

                $data[$p['id']]['years'][$year] = [
                    'pasien_baru' => $pasienBaru,
                    'kunjungan'   => $kunjungan,
                    'total'       => $pasienBaru + $kunjungan,
                ];
            }
        }

        return $data;
    }

    /**
     * Get data Rawat Inap per penyakit dan tahun
     */
    private function getRawatInapData(array $penyakit, array $years): array
    {
        $data = [];

        foreach ($penyakit as $p) {
            $ranges   = $this->parseICDRange($p['kode_icd']);
            $icdWhere = $this->buildICDWhereClause($ranges);

            $data[$p['id']] = [
                'nama'     => $p['nama'],
                'kode_icd' => $p['kode_icd'],
                'color'    => $p['color'],
                'years'    => [],
            ];

            foreach ($years as $year) {
                $base = fn() => DB::table('kamar_inap as ki')
                    ->join('reg_periksa as rp', 'ki.no_rawat', '=', 'rp.no_rawat')
                    ->join('diagnosa_pasien as dp', 'rp.no_rawat', '=', 'dp.no_rawat')
                    ->where('rp.status_lanjut', 'Ranap')
                    ->whereRaw($icdWhere['clause'], $icdWhere['bindings'])
                    ->where('dp.prioritas', 1);

                $jumlahPasien = (clone $base())
                    ->whereYear('ki.tgl_masuk', $year)
                    ->distinct('rp.no_rawat')
                    ->count('rp.no_rawat');

                $keluarMeninggal = (clone $base())
                    ->whereYear('ki.tgl_keluar', $year)
                    ->where('ki.stts_pulang', 'Meninggal')
                    ->distinct('rp.no_rawat')
                    ->count('rp.no_rawat');

                $data[$p['id']]['years'][$year] = [
                    'jumlah_pasien'   => $jumlahPasien,
                    'keluar_meninggal'=> $keluarMeninggal,
                    'total'           => $jumlahPasien,   // total = jumlah_pasien (meninggal adalah subset)
                ];
            }
        }

        return $data;
    }

    /**
     * Hitung totals per year dari data array
     */
    private function calcTotalsRajal(array $rawatJalanData, array $years): array
    {
        $totals = [];
        foreach ($years as $year) {
            $totals[$year] = ['pasien_baru' => 0, 'kunjungan' => 0, 'total' => 0];
            foreach ($rawatJalanData as $row) {
                $y = $row['years'][$year] ?? [];
                $totals[$year]['pasien_baru'] += $y['pasien_baru'] ?? 0;
                $totals[$year]['kunjungan']   += $y['kunjungan']   ?? 0;
                $totals[$year]['total']        += $y['total']       ?? 0;
            }
        }
        return $totals;
    }

    private function calcTotalsRanap(array $rawatInapData, array $years): array
    {
        $totals = [];
        foreach ($years as $year) {
            $totals[$year] = ['jumlah_pasien' => 0, 'keluar_meninggal' => 0, 'total' => 0];
            foreach ($rawatInapData as $row) {
                $y = $row['years'][$year] ?? [];
                $totals[$year]['jumlah_pasien']    += $y['jumlah_pasien']    ?? 0;
                $totals[$year]['keluar_meninggal'] += $y['keluar_meninggal'] ?? 0;
                $totals[$year]['total']             += $y['total']            ?? 0;
            }
        }
        return $totals;
    }

    private function generateRandomColor(): string
    {
        $colors = ['#FF6B6B','#4ECDC4','#45B7D1','#96CEB4','#FFEAA7','#DDA0DD','#98D8C8','#F7DC6F','#BB8FCE','#85C1E9'];
        return $colors[array_rand($colors)];
    }

    // ──────────────────────────────────────────────────────────────────────────────
    // ROUTES
    // ──────────────────────────────────────────────────────────────────────────────

    /**
     * GET /kunjungan-poli — tampilkan halaman utama
     */
    public function index()
    {
        $penyakit = session('kunjungan_poli_penyakit', $this->getDefaultPenyakit());
        $years    = session('kunjungan_poli_years',    $this->getDefaultYears());

        $rawatJalanData   = $this->getRawatJalanData($penyakit, $years);
        $rawatInapData    = $this->getRawatInapData($penyakit, $years);
        $rawatJalanTotals = $this->calcTotalsRajal($rawatJalanData, $years);
        $rawatInapTotals  = $this->calcTotalsRanap($rawatInapData,  $years);

        return view('rm.laporan_rm.kunjungan_poli', compact(
            'penyakit', 'years',
            'rawatJalanData', 'rawatInapData',
            'rawatJalanTotals', 'rawatInapTotals'
        ));
    }

    /**
     * POST /kunjungan-poli/tambah-penyakit
     */
    public function tambahPenyakit(Request $request)
    {
        $request->validate([
            'nama_penyakit' => 'required|string|max:100',
            'kode_icd'      => 'required|string|max:100',
            'deskripsi'     => 'nullable|string|max:255',
        ]);

        $penyakit = session('kunjungan_poli_penyakit', $this->getDefaultPenyakit());

        $penyakit[] = [
            'id'        => 'custom_' . time(),
            'nama'      => $request->nama_penyakit,
            'kode_icd'  => $request->kode_icd,
            'deskripsi' => $request->deskripsi ?? '',
            'color'     => $this->generateRandomColor(),
        ];

        session(['kunjungan_poli_penyakit' => $penyakit]);

        return redirect()->route('kunjungan-poli')->with('success', 'Penyakit berhasil ditambahkan.');
    }

    /**
     * POST /kunjungan-poli/hapus-penyakit
     */
    public function hapusPenyakit(Request $request)
    {
        $removeId = $request->input('penyakit_id');
        $penyakit = session('kunjungan_poli_penyakit', $this->getDefaultPenyakit());

        $penyakit = array_values(array_filter($penyakit, fn($p) => $p['id'] !== $removeId));
        session(['kunjungan_poli_penyakit' => $penyakit]);

        return redirect()->route('kunjungan-poli')->with('success', 'Penyakit berhasil dihapus.');
    }

    /**
     * POST /kunjungan-poli/tambah-tahun
     */
    public function tambahTahun(Request $request)
    {
        $request->validate(['new_year' => 'required|integer|min:2000|max:2100']);

        $years   = session('kunjungan_poli_years', $this->getDefaultYears());
        $newYear = (int) $request->new_year;

        if (!in_array($newYear, $years)) {
            $years[] = $newYear;
            sort($years);
            session(['kunjungan_poli_years' => $years]);
        }

        return redirect()->route('kunjungan-poli')->with('success', 'Tahun berhasil ditambahkan.');
    }

    /**
     * POST /kunjungan-poli/hapus-tahun
     */
    public function hapusTahun(Request $request)
    {
        $years      = session('kunjungan_poli_years', $this->getDefaultYears());
        $removeYear = (int) $request->input('year');

        if (count($years) <= 1) {
            return redirect()->route('kunjungan-poli')->with('error', 'Minimal harus ada satu tahun.');
        }

        $years = array_values(array_filter($years, fn($y) => $y !== $removeYear));
        session(['kunjungan_poli_years' => $years]);

        return redirect()->route('kunjungan-poli')->with('success', 'Tahun berhasil dihapus.');
    }

    /**
     * POST /kunjungan-poli/reset
     */
    public function resetDefault()
    {
        session()->forget(['kunjungan_poli_penyakit', 'kunjungan_poli_years']);
        return redirect()->route('kunjungan-poli')->with('success', 'Data berhasil direset ke default.');
    }

    /**
     * GET /kunjungan-poli/detail — tampilkan detail data
     */
    public function detail(Request $request)
    {
        $penyakitId = $request->input('penyakit_id');
        $year       = (int) $request->input('year');
        $type       = $request->input('type');       // 'rajal' | 'ranap'
        $category   = $request->input('category');   // 'pasien_baru' | 'kunjungan' | 'jumlah_pasien' | 'keluar_meninggal'

        // Validasi sederhana
        abort_if(!in_array($type, ['rajal', 'ranap']), 400);
        abort_if(!in_array($category, ['pasien_baru','kunjungan','jumlah_pasien','keluar_meninggal']), 400);

        // Cari penyakit
        $allPenyakit     = session('kunjungan_poli_penyakit', $this->getDefaultPenyakit());
        $selectedPenyakit = collect($allPenyakit)->firstWhere('id', $penyakitId);

        abort_if(!$selectedPenyakit, 404, 'Penyakit tidak ditemukan.');

        $ranges   = $this->parseICDRange($selectedPenyakit['kode_icd']);
        $icdWhere = $this->buildICDWhereClause($ranges);

        if ($type === 'rajal') {
            $query = DB::table('reg_periksa as rp')
                ->join('pasien as p',          'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
                ->join('diagnosa_pasien as dp', 'rp.no_rawat',     '=', 'dp.no_rawat')
                ->join('penyakit as pk',        'dp.kd_penyakit',  '=', 'pk.kd_penyakit')
                ->join('poliklinik as pol',     'rp.kd_poli',      '=', 'pol.kd_poli')
                ->where('rp.status_lanjut', 'Ralan')
                ->whereYear('rp.tgl_registrasi', $year)
                ->whereRaw($icdWhere['clause'], $icdWhere['bindings'])
                ->where('dp.prioritas', 1)
                ->select(
                    'rp.no_rawat', 'rp.no_rkm_medis', 'p.nm_pasien', 'p.jk', 'p.umur',
                    'rp.tgl_registrasi', 'pol.nm_poli', 'rp.stts_daftar',
                    'pk.kd_penyakit', 'pk.nm_penyakit'
                );

            if ($category === 'pasien_baru') {
                $query->where('rp.stts_daftar', 'Baru');
            } elseif ($category === 'kunjungan') {
                $query->where('rp.stts_daftar', 'Lama');
            }

            $data = $query->orderBy('rp.tgl_registrasi', 'desc')->get();

        } else {
            $query = DB::table('kamar_inap as ki')
                ->join('reg_periksa as rp',    'ki.no_rawat',     '=', 'rp.no_rawat')
                ->join('pasien as p',           'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
                ->join('diagnosa_pasien as dp', 'rp.no_rawat',     '=', 'dp.no_rawat')
                ->join('penyakit as pk',        'dp.kd_penyakit',  '=', 'pk.kd_penyakit')
                ->join('bangsal as b',          'ki.kd_bangsal',   '=', 'b.kd_bangsal')
                ->where('rp.status_lanjut', 'Ranap')
                ->whereYear('ki.tgl_masuk', $year)
                ->whereRaw($icdWhere['clause'], $icdWhere['bindings'])
                ->where('dp.prioritas', 1)
                ->select(
                    'rp.no_rawat', 'rp.no_rkm_medis', 'p.nm_pasien', 'p.jk', 'p.umur',
                    'ki.tgl_masuk', 'ki.tgl_keluar', 'b.nm_bangsal', 'ki.stts_pulang',
                    'pk.kd_penyakit', 'pk.nm_penyakit'
                );

            if ($category === 'keluar_meninggal') {
                $query->where('ki.stts_pulang', 'Meninggal');
            }

            $data = $query->orderBy('ki.tgl_masuk', 'desc')->get();
        }

        return view('rm.laporan_rm.kunjungan_poli_detail', compact(
            'selectedPenyakit', 'year', 'type', 'category', 'data'
        ));
    }
}

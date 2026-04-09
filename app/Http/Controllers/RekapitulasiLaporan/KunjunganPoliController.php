<?php

namespace App\Http\Controllers\RekapitulasiLaporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

    private function getMonthLabels(): array
    {
        return [
            1  => 'Jan', 2  => 'Feb', 3  => 'Mar', 4  => 'Apr',
            5  => 'Mei', 6  => 'Jun', 7  => 'Jul', 8  => 'Agt',
            9  => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
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
    private function getRawatJalanData(array $penyakit, array $years, bool $showMonths = false): array
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
                $base = fn($month = null) => DB::table('reg_periksa as rp')
                    ->join('diagnosa_pasien as dp', 'rp.no_rawat', '=', 'dp.no_rawat')
                    ->where('rp.status_lanjut', 'Ralan')
                    ->whereYear('rp.tgl_registrasi', $year)
                    ->when($month, fn($q, $m) => $q->whereMonth('rp.tgl_registrasi', $m))
                    ->whereRaw($icdWhere['clause'], $icdWhere['bindings'])
                    ->where('dp.prioritas', 1);

                if ($showMonths) {
                    $totalPB = 0;
                    $totalK  = 0;
                    for ($month = 1; $month <= 12; $month++) {
                        $pasienBaru = (clone $base($month))
                            ->where('rp.stts_daftar', 'Baru')
                            ->distinct('rp.no_rawat')
                            ->count('rp.no_rawat');

                        $kunjungan = (clone $base($month))
                            ->where('rp.stts_daftar', 'Lama')
                            ->distinct('rp.no_rawat')
                            ->count('rp.no_rawat');

                        $data[$p['id']]['years'][$year][$month] = [
                            'pasien_baru' => $pasienBaru,
                            'kunjungan'   => $kunjungan,
                            'total'       => $pasienBaru + $kunjungan,
                        ];

                        $totalPB += $pasienBaru;
                        $totalK  += $kunjungan;
                    }
                    $data[$p['id']]['years'][$year]['_total'] = [
                        'pasien_baru' => $totalPB,
                        'kunjungan'   => $totalK,
                        'total'       => $totalPB + $totalK,
                    ];
                } else {
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
        }

        return $data;
    }

    /**
     * Get data Rawat Inap per penyakit dan tahun
     */
    private function getRawatInapData(array $penyakit, array $years, bool $showMonths = false): array
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
                $base = fn($month = null) => DB::table('kamar_inap as ki')
                    ->join('reg_periksa as rp', 'ki.no_rawat', '=', 'rp.no_rawat')
                    ->join('diagnosa_pasien as dp', 'rp.no_rawat', '=', 'dp.no_rawat')
                    ->where('rp.status_lanjut', 'Ranap')
                    ->whereRaw($icdWhere['clause'], $icdWhere['bindings'])
                    ->where('dp.prioritas', 1);

                if ($showMonths) {
                    $totalJP = 0;
                    $totalKM = 0;
                    for ($month = 1; $month <= 12; $month++) {
                        $jumlahPasien = (clone $base($month))
                            ->whereYear('ki.tgl_masuk', $year)
                            ->whereMonth('ki.tgl_masuk', $month)
                            ->distinct('rp.no_rawat')
                            ->count('rp.no_rawat');

                        $keluarMeninggal = (clone $base($month))
                            ->whereYear('ki.tgl_keluar', $year)
                            ->whereMonth('ki.tgl_keluar', $month)
                            ->where('ki.stts_pulang', 'Meninggal')
                            ->distinct('rp.no_rawat')
                            ->count('rp.no_rawat');

                        $data[$p['id']]['years'][$year][$month] = [
                            'jumlah_pasien'    => $jumlahPasien,
                            'keluar_meninggal' => $keluarMeninggal,
                            'total'             => $jumlahPasien,
                        ];

                        $totalJP += $jumlahPasien;
                        $totalKM += $keluarMeninggal;
                    }
                    $data[$p['id']]['years'][$year]['_total'] = [
                        'jumlah_pasien'    => $totalJP,
                        'keluar_meninggal' => $totalKM,
                        'total'             => $totalJP,
                    ];
                } else {
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
                        'total'           => $jumlahPasien,
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * Hitung totals per year dari data array
     */
    private function calcTotalsRajal(array $rawatJalanData, array $years, bool $showMonths = false): array
    {
        $totals = [];
        foreach ($years as $year) {
            if ($showMonths) {
                for ($month = 1; $month <= 12; $month++) {
                    $totals[$year][$month] = ['pasien_baru' => 0, 'kunjungan' => 0, 'total' => 0];
                }
                $totals[$year]['_total'] = ['pasien_baru' => 0, 'kunjungan' => 0, 'total' => 0];
                foreach ($rawatJalanData as $row) {
                    for ($m = 1; $m <= 12; $m++) {
                        $y = $row['years'][$year][$m] ?? [];
                        $totals[$year][$m]['pasien_baru'] += $y['pasien_baru'] ?? 0;
                        $totals[$year][$m]['kunjungan']   += $y['kunjungan']   ?? 0;
                        $totals[$year][$m]['total']        += $y['total']       ?? 0;
                    }
                    $yAll = $row['years'][$year]['_total'] ?? [];
                    $totals[$year]['_total']['pasien_baru'] += $yAll['pasien_baru'] ?? 0;
                    $totals[$year]['_total']['kunjungan']   += $yAll['kunjungan']   ?? 0;
                    $totals[$year]['_total']['total']        += $yAll['total']       ?? 0;
                }
            } else {
                $totals[$year] = ['pasien_baru' => 0, 'kunjungan' => 0, 'total' => 0];
                foreach ($rawatJalanData as $row) {
                    $y = $row['years'][$year] ?? [];
                    $totals[$year]['pasien_baru'] += $y['pasien_baru'] ?? 0;
                    $totals[$year]['kunjungan']   += $y['kunjungan']   ?? 0;
                    $totals[$year]['total']        += $y['total']       ?? 0;
                }
            }
        }
        return $totals;
    }

    private function calcTotalsRanap(array $rawatInapData, array $years, bool $showMonths = false): array
    {
        $totals = [];
        foreach ($years as $year) {
            if ($showMonths) {
                for ($month = 1; $month <= 12; $month++) {
                    $totals[$year][$month] = ['jumlah_pasien' => 0, 'keluar_meninggal' => 0, 'total' => 0];
                }
                $totals[$year]['_total'] = ['jumlah_pasien' => 0, 'keluar_meninggal' => 0, 'total' => 0];
                foreach ($rawatInapData as $row) {
                    for ($m = 1; $m <= 12; $m++) {
                        $y = $row['years'][$year][$m] ?? [];
                        $totals[$year][$m]['jumlah_pasien']    += $y['jumlah_pasien']    ?? 0;
                        $totals[$year][$m]['keluar_meninggal'] += $y['keluar_meninggal'] ?? 0;
                        $totals[$year][$m]['total']             += $y['total']            ?? 0;
                    }
                    $yAll = $row['years'][$year]['_total'] ?? [];
                    $totals[$year]['_total']['jumlah_pasien']    += $yAll['jumlah_pasien']    ?? 0;
                    $totals[$year]['_total']['keluar_meninggal'] += $yAll['keluar_meninggal'] ?? 0;
                    $totals[$year]['_total']['total']             += $yAll['total']            ?? 0;
                }
            } else {
                $totals[$year] = ['jumlah_pasien' => 0, 'keluar_meninggal' => 0, 'total' => 0];
                foreach ($rawatInapData as $row) {
                    $y = $row['years'][$year] ?? [];
                    $totals[$year]['jumlah_pasien']    += $y['jumlah_pasien']    ?? 0;
                    $totals[$year]['keluar_meninggal'] += $y['keluar_meninggal'] ?? 0;
                    $totals[$year]['total']             += $y['total']            ?? 0;
                }
            }
        }
        return $totals;
    }

    private function generateRandomColor(): string
    {
        $colors = ['#FF6B6B','#4ECDC4','#45B7D1','#96CEB4','#FFEAA7','#DDA0DD','#98D8C8','#F7DC6F','#BB8FCE','#85C1E9'];
        return $colors[array_rand($colors)];
    }

    /**
     * Helper: hitung jumlah sub-kolom per tahun
     * Tanpa bulan = 2 (PB + K atau JP + KM)
     * Dengan bulan = 12*2 + 2 = 26 (12 bulan × 2 + total tahunan)
     */
    private function subColsPerYear(bool $showMonths): int
    {
        return $showMonths ? (12 * 2 + 2) : 2;
    }

    // ──────────────────────────────────────────────────────────────────────────────
    // ROUTES
    // ──────────────────────────────────────────────────────────────────────────────

    /**
     * GET /kunjungan-poli — tampilkan halaman utama
     */
    public function index()
    {
        $penyakit   = session('kunjungan_poli_penyakit', $this->getDefaultPenyakit());
        $years      = session('kunjungan_poli_years',    $this->getDefaultYears());
        $showMonths = session('kunjungan_poli_show_months', false);

        $rawatJalanData   = $this->getRawatJalanData($penyakit, $years, $showMonths);
        $rawatInapData    = $this->getRawatInapData($penyakit, $years, $showMonths);
        $rawatJalanTotals = $this->calcTotalsRajal($rawatJalanData, $years, $showMonths);
        $rawatInapTotals  = $this->calcTotalsRanap($rawatInapData,  $years, $showMonths);
        $monthLabels      = $this->getMonthLabels();

        return view('rm.laporan_rm.kunjungan_poli', compact(
            'penyakit', 'years', 'showMonths', 'monthLabels',
            'rawatJalanData', 'rawatInapData',
            'rawatJalanTotals', 'rawatInapTotals'
        ));
    }

    /**
     * POST /kunjungan-poli/toggle-months
     */
    public function toggleMonths()
    {
        $current = session('kunjungan_poli_show_months', false);
        session(['kunjungan_poli_show_months' => !$current]);

        return redirect()->route('kunjungan-poli')->with('success',
            !$current ? 'Tampilan bulan diaktifkan.' : 'Tampilan bulan dinonaktifkan.');
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
        session()->forget([
            'kunjungan_poli_penyakit',
            'kunjungan_poli_years',
            'kunjungan_poli_show_months',
        ]);
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
        $month      = $request->input('month');      // optional: 1-12

        // Validasi sederhana
        abort_if(!in_array($type, ['rajal', 'ranap']), 400);
        abort_if(!in_array($category, ['pasien_baru','kunjungan','jumlah_pasien','keluar_meninggal']), 400);

        // Cari penyakit
        $allPenyakit      = session('kunjungan_poli_penyakit', $this->getDefaultPenyakit());
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
                ->when($month, fn($q, $m) => $q->whereMonth('rp.tgl_registrasi', $m))
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
                ->when($month, fn($q, $m) => $q->whereMonth('ki.tgl_masuk', $m))
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
            'selectedPenyakit', 'year', 'type', 'category', 'month', 'data'
        ));
    }

    /**
     * GET /kunjungan-poli/export-pdf
     */
    public function exportPdf()
    {
        $penyakit   = session('kunjungan_poli_penyakit', $this->getDefaultPenyakit());
        $years      = session('kunjungan_poli_years',    $this->getDefaultYears());
        $showMonths = session('kunjungan_poli_show_months', false);

        $rawatJalanData   = $this->getRawatJalanData($penyakit, $years, $showMonths);
        $rawatInapData    = $this->getRawatInapData($penyakit, $years, $showMonths);
        $rawatJalanTotals = $this->calcTotalsRajal($rawatJalanData, $years, $showMonths);
        $rawatInapTotals  = $this->calcTotalsRanap($rawatInapData,  $years, $showMonths);

        $hospitalInfo = DB::table('setting')->first();
        $monthLabels  = $this->getMonthLabels();

        $pdf = PDF::loadView('rm.laporan_rm.kunjungan_poli_pdf', [
            'penyakit'         => $penyakit,
            'years'            => $years,
            'showMonths'       => $showMonths,
            'monthLabels'      => $monthLabels,
            'rawatJalanData'   => $rawatJalanData,
            'rawatInapData'    => $rawatInapData,
            'rawatJalanTotals' => $rawatJalanTotals,
            'rawatInapTotals'  => $rawatInapTotals,
            'hospitalInfo'     => $hospitalInfo,
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('disable-smart-shrinking', true);

        $filename = 'Kunjungan_Poli_' . implode('_', $years) . '_' . date('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * GET /kunjungan-poli/export-excel
     */
    public function exportExcel()
    {
        $penyakit   = session('kunjungan_poli_penyakit', $this->getDefaultPenyakit());
        $years      = session('kunjungan_poli_years',    $this->getDefaultYears());
        $showMonths = session('kunjungan_poli_show_months', false);

        $rawatJalanData   = $this->getRawatJalanData($penyakit, $years, $showMonths);
        $rawatInapData    = $this->getRawatInapData($penyakit, $years, $showMonths);
        $rawatJalanTotals = $this->calcTotalsRajal($rawatJalanData, $years, $showMonths);
        $rawatInapTotals  = $this->calcTotalsRanap($rawatInapData,  $years, $showMonths);

        $hospitalInfo = DB::table('setting')->first();
        $monthLabels  = $this->getMonthLabels();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kunjungan Poli');

        $subCols = $this->subColsPerYear($showMonths);

        // ── Title ──────────────────────────────────────────
        $sheet->setCellValue('A1', 'DATA KUNJUNGAN POLI BERDASARKAN KASUS/PENYAKIT');
        if ($hospitalInfo) {
            $sheet->setCellValue('A2', $hospitalInfo->nama_instansi ?? 'Rumah Sakit');
        }
        $periodText = 'Tahun: ' . implode(', ', $years);
        if ($showMonths) {
            $periodText .= ' (per Bulan)';
        }
        $sheet->setCellValue('A3', $periodText);

        $endColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + (count($years) * $subCols * 2));
        $sheet->mergeCells('A1:' . $endColLetter . '1');
        $sheet->mergeCells('A2:' . $endColLetter . '2');
        $sheet->mergeCells('A3:' . $endColLetter . '3');

        $sheet->getStyle('A2:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $titleStyle = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:' . $endColLetter . '1')->applyFromArray($titleStyle);
        $sheet->getStyle('A2:' . $endColLetter . '3')->getFont()->setBold(true);

        // ── Table headers ──────────────────────────────────
        $startRow = 5;
        $col = 1;

        // Row 5 - Main headers
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Kasus/Penyakit (Kode ICD-10)');
        $colRajalStart = 3;
        $colRanapStart = 3 + (count($years) * $subCols);

        $sheet->setCellValue([$colRajalStart, 5], 'Rawat Jalan');
        $sheet->mergeCells([$colRajalStart, 5, $colRajalStart + (count($years) * $subCols) - 1, 5]);

        $sheet->setCellValue([$colRanapStart, 5], 'Rawat Inap');
        $sheet->mergeCells([$colRanapStart, 5, $colRanapStart + (count($years) * $subCols) - 1, 5]);

        if ($showMonths) {
            // Row 6 - Year headers
            $col = 3;
            foreach ($years as $year) {
                $sheet->setCellValue([$col, 6], $year);
                $sheet->mergeCells([$col, 6, $col + $subCols - 1, 6]);
                $col += $subCols;
            }
            foreach ($years as $year) {
                $sheet->setCellValue([$col, 6], $year);
                $sheet->mergeCells([$col, 6, $col + $subCols - 1, 6]);
                $col += $subCols;
            }

            // Row 7 - Month headers + Total
            $col = 3;
            foreach ($years as $year) {
                for ($m = 1; $m <= 12; $m++) {
                    $sheet->setCellValue([$col, 7], $monthLabels[$m]);
                    $sheet->mergeCells([$col, 7, $col + 1, 7]);
                    $col += 2;
                }
                $sheet->setCellValue([$col, 7], 'Total');
                $sheet->mergeCells([$col, 7, $col + 1, 7]);
                $col += 2;
            }
            foreach ($years as $year) {
                for ($m = 1; $m <= 12; $m++) {
                    $sheet->setCellValue([$col, 7], $monthLabels[$m]);
                    $sheet->mergeCells([$col, 7, $col + 1, 7]);
                    $col += 2;
                }
                $sheet->setCellValue([$col, 7], 'Total');
                $sheet->mergeCells([$col, 7, $col + 1, 7]);
                $col += 2;
            }

            // Row 8 - Sub headers
            $col = 3;
            foreach ($years as $year) {
                for ($m = 1; $m <= 12; $m++) {
                    $sheet->setCellValue([$col, 8], 'PB');
                    $sheet->setCellValue([$col + 1, 8], 'K');
                    $col += 2;
                }
                $sheet->setCellValue([$col, 8], 'PB');
                $sheet->setCellValue([$col + 1, 8], 'K');
                $col += 2;
            }
            foreach ($years as $year) {
                for ($m = 1; $m <= 12; $m++) {
                    $sheet->setCellValue([$col, 8], 'JP');
                    $sheet->setCellValue([$col + 1, 8], 'KM');
                    $col += 2;
                }
                $sheet->setCellValue([$col, 8], 'JP');
                $sheet->setCellValue([$col + 1, 8], 'KM');
                $col += 2;
            }

            // Merge No & Penyakit
            $sheet->mergeCells('A5:A8');
            $sheet->mergeCells('B5:B8');

            // Header style
            $headerStyle = [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ];
            $lastCol = 2 + (count($years) * $subCols * 2);
            $sheet->getStyle('A5:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol) . '8')
                ->applyFromArray($headerStyle);

            // ── Data rows ─────────────────────────────────
            $row = 9;
            $no = 1;
            foreach ($rawatJalanData as $id => $rajal) {
                $ranap = $rawatInapData[$id] ?? null;
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $rajal['nama'] . ' (' . $rajal['kode_icd'] . ')');

                $col = 3;
                foreach ($years as $year) {
                    for ($m = 1; $m <= 12; $m++) {
                        $yd = $rajal['years'][$year][$m] ?? ['pasien_baru' => 0, 'kunjungan' => 0];
                        $sheet->setCellValue([$col, $row], $yd['pasien_baru']);
                        $sheet->setCellValue([$col + 1, $row], $yd['kunjungan']);
                        $col += 2;
                    }
                    $yAll = $rajal['years'][$year]['_total'] ?? ['pasien_baru' => 0, 'kunjungan' => 0];
                    $sheet->setCellValue([$col, $row], $yAll['pasien_baru']);
                    $sheet->setCellValue([$col + 1, $row], $yAll['kunjungan']);
                    $col += 2;
                }
                foreach ($years as $year) {
                    for ($m = 1; $m <= 12; $m++) {
                        $yi = $ranap['years'][$year][$m] ?? ['jumlah_pasien' => 0, 'keluar_meninggal' => 0];
                        $sheet->setCellValue([$col, $row], $yi['jumlah_pasien']);
                        $sheet->setCellValue([$col + 1, $row], $yi['keluar_meninggal']);
                        $col += 2;
                    }
                    $yAll = $ranap['years'][$year]['_total'] ?? ['jumlah_pasien' => 0, 'keluar_meninggal' => 0];
                    $sheet->setCellValue([$col, $row], $yAll['jumlah_pasien']);
                    $sheet->setCellValue([$col + 1, $row], $yAll['keluar_meninggal']);
                    $col += 2;
                }
                $row++;
            }

            // ── Total row ─────────────────────────────────
            $sheet->setCellValue('A' . $row, 'JUMLAH');
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $col = 3;
            foreach ($years as $year) {
                for ($m = 1; $m <= 12; $m++) {
                    $tj = $rawatJalanTotals[$year][$m] ?? ['pasien_baru' => 0, 'kunjungan' => 0];
                    $sheet->setCellValue([$col, $row], $tj['pasien_baru']);
                    $sheet->setCellValue([$col + 1, $row], $tj['kunjungan']);
                    $col += 2;
                }
                $tjAll = $rawatJalanTotals[$year]['_total'] ?? ['pasien_baru' => 0, 'kunjungan' => 0];
                $sheet->setCellValue([$col, $row], $tjAll['pasien_baru']);
                $sheet->setCellValue([$col + 1, $row], $tjAll['kunjungan']);
                $col += 2;
            }
            foreach ($years as $year) {
                for ($m = 1; $m <= 12; $m++) {
                    $ti = $rawatInapTotals[$year][$m] ?? ['jumlah_pasien' => 0, 'keluar_meninggal' => 0];
                    $sheet->setCellValue([$col, $row], $ti['jumlah_pasien']);
                    $sheet->setCellValue([$col + 1, $row], $ti['keluar_meninggal']);
                    $col += 2;
                }
                $tiAll = $rawatInapTotals[$year]['_total'] ?? ['jumlah_pasien' => 0, 'keluar_meninggal' => 0];
                $sheet->setCellValue([$col, $row], $tiAll['jumlah_pasien']);
                $sheet->setCellValue([$col + 1, $row], $tiAll['keluar_meninggal']);
                $col += 2;
            }

        } else {
            // ── WITHOUT MONTHS (original behavior) ────────
            $col = 3;
            foreach ($years as $year) {
                $sheet->setCellValue([$col, 6], $year);
                $sheet->mergeCells([$col, 6, $col + 1, 6]);
                $col += 2;
            }
            foreach ($years as $year) {
                $sheet->setCellValue([$col, 6], $year);
                $sheet->mergeCells([$col, 6, $col + 1, 6]);
                $col += 2;
            }

            $col = 3;
            foreach ($years as $year) {
                $sheet->setCellValue([$col, 7], 'Pasien Baru');
                $sheet->setCellValue([$col + 1, 7], 'Kunjungan');
                $col += 2;
            }
            foreach ($years as $year) {
                $sheet->setCellValue([$col, 7], 'Jml Pasien');
                $sheet->setCellValue([$col + 1, 7], 'Keluar Meninggal');
                $col += 2;
            }

            $sheet->mergeCells('A5:A7');
            $sheet->mergeCells('B5:B7');

            $headerStyle = [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ];
            $lastCol = 2 + (count($years) * 4);
            $sheet->getStyle('A5:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol) . '7')
                ->applyFromArray($headerStyle);

            // Data rows
            $row = 8;
            $no = 1;
            foreach ($rawatJalanData as $id => $rajal) {
                $ranap = $rawatInapData[$id] ?? null;
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $rajal['nama'] . ' (' . $rajal['kode_icd'] . ')');

                $col = 3;
                foreach ($years as $year) {
                    $yd = $rajal['years'][$year] ?? ['pasien_baru' => 0, 'kunjungan' => 0];
                    $sheet->setCellValue([$col, $row], $yd['pasien_baru']);
                    $sheet->setCellValue([$col + 1, $row], $yd['kunjungan']);
                    $col += 2;
                }
                foreach ($years as $year) {
                    $yi = $ranap['years'][$year] ?? ['jumlah_pasien' => 0, 'keluar_meninggal' => 0];
                    $sheet->setCellValue([$col, $row], $yi['jumlah_pasien']);
                    $sheet->setCellValue([$col + 1, $row], $yi['keluar_meninggal']);
                    $col += 2;
                }
                $row++;
            }

            // Total row
            $sheet->setCellValue('A' . $row, 'JUMLAH');
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $col = 3;
            foreach ($years as $year) {
                $tj = $rawatJalanTotals[$year] ?? ['pasien_baru' => 0, 'kunjungan' => 0];
                $sheet->setCellValue([$col, $row], $tj['pasien_baru']);
                $sheet->setCellValue([$col + 1, $row], $tj['kunjungan']);
                $col += 2;
            }
            foreach ($years as $year) {
                $ti = $rawatInapTotals[$year] ?? ['jumlah_pasien' => 0, 'keluar_meninggal' => 0];
                $sheet->setCellValue([$col, $row], $ti['jumlah_pasien']);
                $sheet->setCellValue([$col + 1, $row], $ti['keluar_meninggal']);
                $col += 2;
            }
        }

        // ── Total row style ───────────────────────────────
        $totalStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']],
        ];
        $lastColFinal = 2 + (count($years) * $subCols * 2);
        $sheet->getStyle('A' . $row . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColFinal) . $row)
            ->applyFromArray($totalStyle);

        // Auto size columns
        foreach (range('A', \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColFinal)) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Border Style
        $headerRow = $showMonths ? 5 : 5;
        $lastDataRow = $row;

        $sheet->getStyle(
            'A' . $headerRow . ':' .
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColFinal) . $lastDataRow
        )->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        $firstDataRow = $showMonths ? 9 : 8;
        $sheet->getStyle(
            'C' . $firstDataRow . ':' .
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColFinal) . $lastDataRow
        )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Write file
        $writer = new Xlsx($spreadsheet);
        $filename = 'Kunjungan_Poli_' . implode('_', $years) . '_' . date('Y-m-d_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'kunjungan_poli_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}

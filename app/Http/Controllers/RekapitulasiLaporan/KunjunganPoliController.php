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
     * Get data Rawat Jalan per poliklinik for each penyakit and year.
     * Uses a subquery to first get DISTINCT no_rawat matching the ICD filter,
     * then groups by kd_poli + stts_daftar. This avoids duplicate-count issues
     * caused by JOIN with diagnosa_pasien (one no_rawat can have multiple diagnoses).
     */
    private function getRawatJalanPoliData(array $penyakit, array $years, bool $showMonths = false): array
    {
        $data = [];

        // Lookup poli names separately
        $poliNames = DB::table('poliklinik')
            ->pluck('nm_poli', 'kd_poli')
            ->toArray();

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
                if ($showMonths) {
                    $totalAllPB = 0;
                    $totalAllK  = 0;
                    for ($month = 1; $month <= 12; $month++) {
                        $result = $this->queryPoliBreakdown($year, $month, $icdWhere);
                        $totalAllPB += $result['totalPB'];
                        $totalAllK  += $result['totalK'];
                        $data[$p['id']]['years'][$year][$month] = [
                            'poli'  => $result['poliData'],
                            'total' => [
                                'pasien_baru' => $result['totalPB'],
                                'kunjungan'   => $result['totalK'],
                                'total'       => $result['totalPB'] + $result['totalK'],
                            ],
                        ];
                    }
                    // Yearly total (aggregate of all months)
                    $yearlyPoli = [];
                    foreach ($data[$p['id']]['years'][$year] as $m => $mData) {
                        if ($m === '_total') continue;
                        foreach ($mData['poli'] as $kdP => $pd) {
                            if (!isset($yearlyPoli[$kdP])) {
                                $yearlyPoli[$kdP] = [
                                    'nm_poli'     => $pd['nm_poli'],
                                    'pasien_baru' => 0,
                                    'kunjungan'   => 0,
                                    'total'       => 0,
                                ];
                            }
                            $yearlyPoli[$kdP]['pasien_baru'] += $pd['pasien_baru'];
                            $yearlyPoli[$kdP]['kunjungan']   += $pd['kunjungan'];
                            $yearlyPoli[$kdP]['total']       += $pd['total'];
                        }
                    }
                    uasort($yearlyPoli, fn($a, $b) => $b['total'] <=> $a['total']);
                    $data[$p['id']]['years'][$year]['_total'] = [
                        'poli'  => $yearlyPoli,
                        'total' => [
                            'pasien_baru' => $totalAllPB,
                            'kunjungan'   => $totalAllK,
                            'total'       => $totalAllPB + $totalAllK,
                        ],
                    ];
                } else {
                    $result = $this->queryPoliBreakdown($year, null, $icdWhere);
                    $data[$p['id']]['years'][$year] = [
                        'poli'  => $result['poliData'],
                        'total' => [
                            'pasien_baru' => $result['totalPB'],
                            'kunjungan'   => $result['totalK'],
                            'total'       => $result['totalPB'] + $result['totalK'],
                        ],
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * Core query: get poli breakdown for a specific year (and optionally month).
     * Uses a subquery approach to avoid duplicate-count from JOIN with diagnosa_pasien.
     */
    private function queryPoliBreakdown(int $year, ?int $month, array $icdWhere): array
    {
        $poliNames = DB::table('poliklinik')->pluck('nm_poli', 'kd_poli')->toArray();

        // Step 1: Get DISTINCT no_rawat that match the diagnosis filter.
        // This matches exactly what getRawatJalanData does.
        $sub = DB::table('reg_periksa as rp')
            ->join('diagnosa_pasien as dp', 'rp.no_rawat', '=', 'dp.no_rawat')
            ->where('rp.status_lanjut', 'Ralan')
            ->whereYear('rp.tgl_registrasi', $year)
            ->when($month, fn($q, $m) => $q->whereMonth('rp.tgl_registrasi', $m))
            ->whereRaw($icdWhere['clause'], $icdWhere['bindings'])
            ->where('dp.prioritas', 1)
            ->distinct()
            ->pluck('rp.no_rawat');

        // Step 2: From those distinct no_rawat, group by kd_poli + stts_daftar
        // using only reg_periksa (no JOIN, so no duplicates).
        if ($sub->isEmpty()) {
            return ['poliData' => [], 'totalPB' => 0, 'totalK' => 0];
        }

        $rows = DB::table('reg_periksa')
            ->whereIn('no_rawat', $sub)
            ->select('kd_poli', 'stts_daftar', DB::raw('COUNT(*) as total'))
            ->groupBy('kd_poli', 'stts_daftar')
            ->orderBy('kd_poli')
            ->get();

        $poliData = [];
        $totalPB  = 0;
        $totalK   = 0;

        foreach ($rows as $row) {
            $kd = $row->kd_poli;
            if (!isset($poliData[$kd])) {
                $poliData[$kd] = [
                    'nm_poli'     => $poliNames[$kd] ?? $kd,
                    'pasien_baru' => 0,
                    'kunjungan'   => 0,
                    'total'       => 0,
                ];
            }
            $count = (int) $row->total;
            if ($row->stts_daftar === 'Baru') {
                $poliData[$kd]['pasien_baru'] = $count;
                $totalPB += $count;
            } else {
                $poliData[$kd]['kunjungan'] = $count;
                $totalK += $count;
            }
            $poliData[$kd]['total'] += $count;
        }

        uasort($poliData, fn($a, $b) => $b['total'] <=> $a['total']);

        return ['poliData' => $poliData, 'totalPB' => $totalPB, 'totalK' => $totalK];
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

    /**
     * Get all available polikliniks from DB for the poli selector
     */
    private function getAllAvailablePolis(): array
    {
        return DB::table('poliklinik')
            ->where('status', '1')
            ->orderBy('nm_poli')
            ->pluck('nm_poli', 'kd_poli')
            ->toArray();
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
        $showPoli   = session('kunjungan_poli_show_poli', false);

        $rawatJalanData   = $this->getRawatJalanData($penyakit, $years, $showMonths);
        $rawatInapData    = $this->getRawatInapData($penyakit, $years, $showMonths);
        $rawatJalanTotals = $this->calcTotalsRajal($rawatJalanData, $years, $showMonths);
        $rawatInapTotals  = $this->calcTotalsRanap($rawatInapData,  $years, $showMonths);
        $monthLabels      = $this->getMonthLabels();

        // Only compute poli data when filter is active
        $rawatJalanPoliData = null;
        $selectedPolis = session('kunjungan_poli_selected_polis', []);
        $allAvailablePolis = [];
        if ($showPoli) {
            $rawatJalanPoliData = $this->getRawatJalanPoliData($penyakit, $years, $showMonths);
            $allAvailablePolis = $this->getAllAvailablePolis();
        }

        return view('rm.laporan_rm.kunjungan_poli', compact(
            'penyakit', 'years', 'showMonths', 'showPoli', 'monthLabels',
            'rawatJalanData', 'rawatInapData',
            'rawatJalanTotals', 'rawatInapTotals',
            'rawatJalanPoliData', 'selectedPolis', 'allAvailablePolis'
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
     * POST /kunjungan-poli/toggle-poli
     */
    public function togglePoli()
    {
        $current = session('kunjungan_poli_show_poli', false);
        $newState = !$current;

        session(['kunjungan_poli_show_poli' => $newState]);

        return redirect()->route('kunjungan-poli')->with('success',
            $newState ? 'Filter poli rawat jalan diaktifkan.' : 'Filter poli rawat jalan dinonaktifkan.');
    }

    /**
     * POST /kunjungan-poli/set-poli
     */
    public function setSelectedPolis(Request $request)
    {
        $request->validate([
            'polis' => 'nullable|array',
            'polis.*' => 'string',
        ]);

        $selected = $request->input('polis', []);

        session(['kunjungan_poli_selected_polis' => $selected]);

        return redirect()->route('kunjungan-poli')->with('success',
            count($selected) > 0
                ? count($selected) . ' poli dipilih.'
                : 'Semua poli ditampilkan.');
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
            'kunjungan_poli_show_poli',
            'kunjungan_poli_selected_polis',
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
        $showPoli   = session('kunjungan_poli_show_poli', false);
        $selectedPolis = session('kunjungan_poli_selected_polis', []);

        $rawatJalanData   = $this->getRawatJalanData($penyakit, $years, $showMonths);
        $rawatInapData    = $this->getRawatInapData($penyakit, $years, $showMonths);
        $rawatJalanTotals = $this->calcTotalsRajal($rawatJalanData, $years, $showMonths);
        $rawatInapTotals  = $this->calcTotalsRanap($rawatInapData,  $years, $showMonths);

        $rawatJalanPoliData = null;
        if ($showPoli) {
            $rawatJalanPoliData = $this->getRawatJalanPoliData($penyakit, $years, $showMonths);
        }

        $hospitalInfo = DB::table('setting')->first();
        $monthLabels  = $this->getMonthLabels();

        $pdf = PDF::loadView('rm.laporan_rm.kunjungan_poli_pdf', [
            'penyakit'           => $penyakit,
            'years'              => $years,
            'showMonths'         => $showMonths,
            'showPoli'           => $showPoli,
            'selectedPolis'      => $selectedPolis,
            'monthLabels'        => $monthLabels,
            'rawatJalanData'     => $rawatJalanData,
            'rawatInapData'      => $rawatInapData,
            'rawatJalanTotals'   => $rawatJalanTotals,
            'rawatInapTotals'    => $rawatInapTotals,
            'rawatJalanPoliData' => $rawatJalanPoliData,
            'hospitalInfo'       => $hospitalInfo,
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
        $penyakit      = session('kunjungan_poli_penyakit', $this->getDefaultPenyakit());
        $years         = session('kunjungan_poli_years',    $this->getDefaultYears());
        $showMonths    = session('kunjungan_poli_show_months', false);
        $showPoli      = session('kunjungan_poli_show_poli', false);
        $selectedPolis = session('kunjungan_poli_selected_polis', []);

        $rawatJalanData   = $this->getRawatJalanData($penyakit, $years, $showMonths);
        $rawatInapData    = $this->getRawatInapData($penyakit, $years, $showMonths);
        $rawatJalanTotals = $this->calcTotalsRajal($rawatJalanData, $years, $showMonths);
        $rawatInapTotals  = $this->calcTotalsRanap($rawatInapData,  $years, $showMonths);

        $rawatJalanPoliData = null;
        if ($showPoli) {
            $rawatJalanPoliData = $this->getRawatJalanPoliData($penyakit, $years, $showMonths);
        }

        $hospitalInfo = DB::table('setting')->first();
        $monthLabels  = $this->getMonthLabels();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kunjungan Poli');

        $subCols = $this->subColsPerYear($showMonths);

        // ══════════════════════════════════════════════════
        // POLI MODE EXPORT
        // ══════════════════════════════════════════════════
        if ($showPoli && $rawatJalanPoliData) {
            $this->buildExcelPoliSheet(
                $sheet, $penyakit, $years, $showMonths, $monthLabels,
                $rawatJalanData, $rawatJalanPoliData, $rawatJalanTotals,
                $selectedPolis, $hospitalInfo, $subCols
            );
        } else {
            $this->buildExcelNormalSheet(
                $sheet, $penyakit, $years, $showMonths, $monthLabels,
                $rawatJalanData, $rawatInapData, $rawatJalanTotals, $rawatInapTotals,
                $hospitalInfo, $subCols
            );
        }

        // ── Write file ────────────────────────────────────
        $writer = new Xlsx($spreadsheet);
        $filename = 'Kunjungan_Poli_' . implode('_', $years) . '_' . date('Y-m-d_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'kunjungan_poli_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Build Excel sheet for NORMAL mode (original with Rawat Jalan + Rawat Inap)
     */
    private function buildExcelNormalSheet(
        $sheet, array $penyakit, array $years, bool $showMonths, array $monthLabels,
        array $rawatJalanData, array $rawatInapData,
        array $rawatJalanTotals, array $rawatInapTotals,
        $hospitalInfo, int $subCols
    ): void {
        // ── Title ──────────────────────────────────────────
        $sheet->setCellValue('A1', 'DATA KUNJUNGAN POLI BERDASARKAN KASUS/PENYAKIT');
        if ($hospitalInfo) {
            $sheet->setCellValue('A2', $hospitalInfo->nama_instansi ?? 'Rumah Sakit');
        }
        $periodText = 'Tahun: ' . implode(', ', $years);
        if ($showMonths) $periodText .= ' (per Bulan)';
        $sheet->setCellValue('A3', $periodText);

        $endColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + (count($years) * $subCols * 2));
        $sheet->mergeCells('A1:' . $endColLetter . '1');
        $sheet->mergeCells('A2:' . $endColLetter . '2');
        $sheet->mergeCells('A3:' . $endColLetter . '3');
        $sheet->getStyle('A2:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $titleStyle = ['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        $sheet->getStyle('A1:' . $endColLetter . '1')->applyFromArray($titleStyle);
        $sheet->getStyle('A2:' . $endColLetter . '3')->getFont()->setBold(true);

        // ── Table headers ──────────────────────────────────
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Kasus/Penyakit (Kode ICD-10)');
        $colRajalStart = 3;
        $colRanapStart = 3 + (count($years) * $subCols);
        $sheet->setCellValue([$colRajalStart, 5], 'Rawat Jalan');
        $sheet->mergeCells([$colRajalStart, 5, $colRajalStart + (count($years) * $subCols) - 1, 5]);
        $sheet->setCellValue([$colRanapStart, 5], 'Rawat Inap');
        $sheet->mergeCells([$colRanapStart, 5, $colRanapStart + (count($years) * $subCols) - 1, 5]);

        if ($showMonths) {
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
            $col = 3;
            foreach ($years as $year) {
                for ($m = 1; $m <= 12; $m++) {
                    $sheet->setCellValue([$col, 8], 'PB'); $sheet->setCellValue([$col+1, 8], 'K'); $col += 2;
                }
                $sheet->setCellValue([$col, 8], 'PB'); $sheet->setCellValue([$col+1, 8], 'K'); $col += 2;
            }
            foreach ($years as $year) {
                for ($m = 1; $m <= 12; $m++) {
                    $sheet->setCellValue([$col, 8], 'JP'); $sheet->setCellValue([$col+1, 8], 'KM'); $col += 2;
                }
                $sheet->setCellValue([$col, 8], 'JP'); $sheet->setCellValue([$col+1, 8], 'KM'); $col += 2;
            }
            $sheet->mergeCells('A5:A8'); $sheet->mergeCells('B5:B8');
            $lastCol = 2 + (count($years) * $subCols * 2);
            $sheet->getStyle('A5:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol) . '8')
                ->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);

            $row = 9; $no = 1;
            foreach ($rawatJalanData as $id => $rajal) {
                $ranap = $rawatInapData[$id] ?? null;
                $sheet->setCellValue('A'.$row, $no++);
                $sheet->setCellValue('B'.$row, $rajal['nama'].' ('.$rajal['kode_icd'].')');
                $col = 3;
                foreach ($years as $year) {
                    for ($m = 1; $m <= 12; $m++) {
                        $yd = $rajal['years'][$year][$m] ?? ['pasien_baru'=>0,'kunjungan'=>0];
                        $sheet->setCellValue([$col,$row], $yd['pasien_baru']); $sheet->setCellValue([$col+1,$row], $yd['kunjungan']); $col += 2;
                    }
                    $yAll = $rajal['years'][$year]['_total'] ?? ['pasien_baru'=>0,'kunjungan'=>0];
                    $sheet->setCellValue([$col,$row], $yAll['pasien_baru']); $sheet->setCellValue([$col+1,$row], $yAll['kunjungan']); $col += 2;
                }
                foreach ($years as $year) {
                    for ($m = 1; $m <= 12; $m++) {
                        $yi = $ranap['years'][$year][$m] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0];
                        $sheet->setCellValue([$col,$row], $yi['jumlah_pasien']); $sheet->setCellValue([$col+1,$row], $yi['keluar_meninggal']); $col += 2;
                    }
                    $yAll = $ranap['years'][$year]['_total'] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0];
                    $sheet->setCellValue([$col,$row], $yAll['jumlah_pasien']); $sheet->setCellValue([$col+1,$row], $yAll['keluar_meninggal']); $col += 2;
                }
                $row++;
            }
            // Total row
            $sheet->setCellValue('A'.$row, 'JUMLAH'); $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col = 3;
            foreach ($years as $year) {
                for ($m = 1; $m <= 12; $m++) {
                    $tj = $rawatJalanTotals[$year][$m] ?? ['pasien_baru'=>0,'kunjungan'=>0];
                    $sheet->setCellValue([$col,$row], $tj['pasien_baru']); $sheet->setCellValue([$col+1,$row], $tj['kunjungan']); $col += 2;
                }
                $tjAll = $rawatJalanTotals[$year]['_total'] ?? ['pasien_baru'=>0,'kunjungan'=>0];
                $sheet->setCellValue([$col,$row], $tjAll['pasien_baru']); $sheet->setCellValue([$col+1,$row], $tjAll['kunjungan']); $col += 2;
            }
            foreach ($years as $year) {
                for ($m = 1; $m <= 12; $m++) {
                    $ti = $rawatInapTotals[$year][$m] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0];
                    $sheet->setCellValue([$col,$row], $ti['jumlah_pasien']); $sheet->setCellValue([$col+1,$row], $ti['keluar_meninggal']); $col += 2;
                }
                $tiAll = $rawatInapTotals[$year]['_total'] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0];
                $sheet->setCellValue([$col,$row], $tiAll['jumlah_pasien']); $sheet->setCellValue([$col+1,$row], $tiAll['keluar_meninggal']); $col += 2;
            }
        } else {
            $col = 3;
            foreach ($years as $year) { $sheet->setCellValue([$col,6],$year); $sheet->mergeCells([$col,6,$col+1,6]); $col += 2; }
            foreach ($years as $year) { $sheet->setCellValue([$col,6],$year); $sheet->mergeCells([$col,6,$col+1,6]); $col += 2; }
            $col = 3;
            foreach ($years as $year) { $sheet->setCellValue([$col,7],'Pasien Baru'); $sheet->setCellValue([$col+1,7],'Kunjungan'); $col += 2; }
            foreach ($years as $year) { $sheet->setCellValue([$col,7],'Jml Pasien'); $sheet->setCellValue([$col+1,7],'Keluar Meninggal'); $col += 2; }
            $sheet->mergeCells('A5:A7'); $sheet->mergeCells('B5:B7');
            $lastCol = 2 + (count($years) * 4);
            $sheet->getStyle('A5:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol) . '7')
                ->applyFromArray(['font'=>['bold'=>true],'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN]]]);
            $row = 8; $no = 1;
            foreach ($rawatJalanData as $id => $rajal) {
                $ranap = $rawatInapData[$id] ?? null;
                $sheet->setCellValue('A'.$row, $no++); $sheet->setCellValue('B'.$row, $rajal['nama'].' ('.$rajal['kode_icd'].')');
                $col = 3;
                foreach ($years as $year) {
                    $yd = $rajal['years'][$year] ?? ['pasien_baru'=>0,'kunjungan'=>0];
                    $sheet->setCellValue([$col,$row],$yd['pasien_baru']); $sheet->setCellValue([$col+1,$row],$yd['kunjungan']); $col += 2;
                }
                foreach ($years as $year) {
                    $yi = $ranap['years'][$year] ?? ['jumlah_pasien'=>0,'keluar_meninggal'=>0];
                    $sheet->setCellValue([$col,$row],$yi['jumlah_pasien']); $sheet->setCellValue([$col+1,$row],$yi['keluar_meninggal']); $col += 2;
                }
                $row++;
            }
            $sheet->setCellValue('A'.$row, 'JUMLAH'); $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col = 3;
            foreach ($years as $year) { $tj=$rawatJalanTotals[$year]??['pasien_baru'=>0,'kunjungan'=>0]; $sheet->setCellValue([$col,$row],$tj['pasien_baru']); $sheet->setCellValue([$col+1,$row],$tj['kunjungan']); $col+=2; }
            foreach ($years as $year) { $ti=$rawatInapTotals[$year]??['jumlah_pasien'=>0,'keluar_meninggal'=>0]; $sheet->setCellValue([$col,$row],$ti['jumlah_pasien']); $sheet->setCellValue([$col+1,$row],$ti['keluar_meninggal']); $col+=2; }
        }

        // ── Styles ────────────────────────────────────────
        $lastColFinal = 2 + (count($years) * $subCols * 2);
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColFinal);
        $sheet->getStyle('A'.$row.':'.$lastColLetter.$row)->applyFromArray(['font'=>['bold'=>true],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'F8F9FA']]]);
        foreach (range('A', $lastColLetter) as $cid) $sheet->getColumnDimension($cid)->setAutoSize(true);
        $headerRow = 5;
        $sheet->getStyle('A'.$headerRow.':'.$lastColLetter.$row)->applyFromArray(['borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN]]]);
        $firstDataRow = $showMonths ? 9 : 8;
        $sheet->getStyle('C'.$firstDataRow.':'.$lastColLetter.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * Build Excel sheet for POLI mode (Rawat Jalan per Poli, no Rawat Inap)
     */
    private function buildExcelPoliSheet(
        $sheet, array $penyakit, array $years, bool $showMonths, array $monthLabels,
        array $rawatJalanData, array $rawatJalanPoliData, array $rawatJalanTotals,
        array $selectedPolis, $hospitalInfo, int $subCols
    ): void {
        // ── Title ──────────────────────────────────────────
        $sheet->setCellValue('A1', 'DATA KUNJUNGAN POLI BERDASARKAN KASUS/PENYAKIT (per Poliklinik)');
        if ($hospitalInfo) $sheet->setCellValue('A2', $hospitalInfo->nama_instansi ?? 'Rumah Sakit');
        $periodText = 'Tahun: ' . implode(', ', $years);
        if ($showMonths) $periodText .= ' (per Bulan)';
        $sheet->setCellValue('A3', $periodText);

        $endColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + (count($years) * $subCols));
        $sheet->mergeCells('A1:'.$endColLetter.'1');
        $sheet->mergeCells('A2:'.$endColLetter.'2');
        $sheet->mergeCells('A3:'.$endColLetter.'3');
        $sheet->getStyle('A2:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:'.$endColLetter.'1')->applyFromArray(['font'=>['bold'=>true,'size'=>14,'color'=>['rgb'=>'1F4E79']],'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('A2:'.$endColLetter.'3')->getFont()->setBold(true);

        // ── Headers ────────────────────────────────────────
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Kasus/Penyakit (Kode ICD-10)');
        $sheet->setCellValue([3, 5], 'Rawat Jalan per Poli');
        $sheet->mergeCells([3, 5, 2 + (count($years) * $subCols), 5]);

        if ($showMonths) {
            $col = 3;
            foreach ($years as $year) { $sheet->setCellValue([$col,6],$year); $sheet->mergeCells([$col,6,$col+$subCols-1,6]); $col+=$subCols; }
            $col = 3;
            foreach ($years as $year) {
                for ($m=1;$m<=12;$m++) { $sheet->setCellValue([$col,7],$monthLabels[$m]); $sheet->mergeCells([$col,7,$col+1,7]); $col+=2; }
                $sheet->setCellValue([$col,7],'Total'); $sheet->mergeCells([$col,7,$col+1,7]); $col+=2;
            }
            $col = 3;
            foreach ($years as $year) {
                for ($m=1;$m<=12;$m++) { $sheet->setCellValue([$col,8],'PB'); $sheet->setCellValue([$col+1,8],'K'); $col+=2; }
                $sheet->setCellValue([$col,8],'PB'); $sheet->setCellValue([$col+1,8],'K'); $col+=2;
            }
            $sheet->mergeCells('A5:A8'); $sheet->mergeCells('B5:B8');
            $lastCol = 2 + (count($years) * $subCols);
            $sheet->getStyle('A5:'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol).'8')
                ->applyFromArray(['font'=>['bold'=>true],'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN]]]);
            $dataRow = 9;
        } else {
            $col = 3;
            foreach ($years as $year) { $sheet->setCellValue([$col,6],$year); $sheet->mergeCells([$col,6,$col+1,6]); $col+=2; }
            $col = 3;
            foreach ($years as $year) { $sheet->setCellValue([$col,7],'Pasien Baru'); $sheet->setCellValue([$col+1,7],'Kunjungan'); $col+=2; }
            $sheet->mergeCells('A5:A7'); $sheet->mergeCells('B5:B7');
            $lastCol = 2 + (count($years) * 2);
            $sheet->getStyle('A5:'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol).'7')
                ->applyFromArray(['font'=>['bold'=>true],'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN]]]);
            $dataRow = 8;
        }

        // ── Data rows with poli sub-rows ──────────────────
        $no = 1;
        foreach ($rawatJalanData as $id => $rajal) {
            $poliYearData = $rawatJalanPoliData[$id]['years'] ?? [];

            // Build refPolis list
            $refPolis = [];
            $srcPoli = $showMonths
                ? ($poliYearData[$years[0] ?? 0]['_total']['poli'] ?? [])
                : ($poliYearData[$years[0] ?? 0]['poli'] ?? []);
            foreach ($srcPoli as $kdP => $pd) {
                if (count($selectedPolis) > 0 && !in_array($kdP, $selectedPolis)) continue;
                $refPolis[$kdP] = $pd;
            }
            $subRowCount = count($refPolis) > 0 ? (1 + count($refPolis)) : 1;

            // Main row (penyakit aggregate)
            $sheet->setCellValue('A'.$dataRow, $no++);
            $sheet->mergeCells('A'.$dataRow.':A'.($dataRow + $subRowCount - 1));
            $sheet->setCellValue('B'.$dataRow, $rajal['nama'].' ('.$rajal['kode_icd'].')');
            $sheet->mergeCells('B'.$dataRow.':B'.($dataRow + $subRowCount - 1));
            $sheet->getStyle('A'.$dataRow.':B'.($dataRow+$subRowCount-1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $col = 3;
            foreach ($years as $year) {
                if ($showMonths) {
                    for ($m=1;$m<=12;$m++) {
                        $yd = $rajal['years'][$year][$m] ?? ['pasien_baru'=>0,'kunjungan'=>0];
                        $sheet->setCellValue([$col,$dataRow],$yd['pasien_baru']); $sheet->setCellValue([$col+1,$dataRow],$yd['kunjungan']); $col+=2;
                    }
                    $yAll = $rajal['years'][$year]['_total'] ?? ['pasien_baru'=>0,'kunjungan'=>0];
                    $sheet->setCellValue([$col,$dataRow],$yAll['pasien_baru']); $sheet->setCellValue([$col+1,$dataRow],$yAll['kunjungan']); $col+=2;
                } else {
                    $yd = $rajal['years'][$year] ?? ['pasien_baru'=>0,'kunjungan'=>0];
                    $sheet->setCellValue([$col,$dataRow],$yd['pasien_baru']); $sheet->setCellValue([$col+1,$dataRow],$yd['kunjungan']); $col+=2;
                }
            }
            // Bold the main row
            $sheet->getStyle('A'.$dataRow.':'.$endColLetter.$dataRow)->applyFromArray(['font'=>['bold'=>true],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'F0F7FF']]]);
            $dataRow++;

            // Sub-rows (per poli)
            foreach ($refPolis as $kdPoli => $poli) {
                $sheet->setCellValue('B'.$dataRow, '  > '.$poli['nm_poli']);
                $col = 3;
                foreach ($years as $year) {
                    if ($showMonths) {
                        for ($m=1;$m<=12;$m++) {
                            $pMonth = $poliYearData[$year][$m]['poli'][$kdPoli] ?? null;
                            $pb = $pMonth['pasien_baru'] ?? 0; $kj = $pMonth['kunjungan'] ?? 0;
                            $sheet->setCellValue([$col,$dataRow],$pb); $sheet->setCellValue([$col+1,$dataRow],$kj); $col+=2;
                        }
                        $pAll = $poliYearData[$year]['_total']['poli'][$kdPoli] ?? null;
                        $pb = $pAll['pasien_baru'] ?? 0; $kj = $pAll['kunjungan'] ?? 0;
                        $sheet->setCellValue([$col,$dataRow],$pb); $sheet->setCellValue([$col+1,$dataRow],$kj); $col+=2;
                    } else {
                        $pYear = $poliYearData[$year]['poli'][$kdPoli] ?? null;
                        $pb = $pYear['pasien_baru'] ?? 0; $kj = $pYear['kunjungan'] ?? 0;
                        $sheet->setCellValue([$col,$dataRow],$pb); $sheet->setCellValue([$col+1,$dataRow],$kj); $col+=2;
                    }
                }
                $sheet->getStyle('B'.$dataRow)->getFont()->setItalic(true);
                $dataRow++;
            }
        }

        // ── Total row ──────────────────────────────────────
        $sheet->setCellValue('A'.$dataRow, 'JUMLAH'); $sheet->mergeCells('A'.$dataRow.':B'.$dataRow);
        $sheet->getStyle('A'.$dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $col = 3;
        foreach ($years as $year) {
            if ($showMonths) {
                for ($m=1;$m<=12;$m++) {
                    $tj=$rawatJalanTotals[$year][$m]??['pasien_baru'=>0,'kunjungan'=>0];
                    $sheet->setCellValue([$col,$dataRow],$tj['pasien_baru']); $sheet->setCellValue([$col+1,$dataRow],$tj['kunjungan']); $col+=2;
                }
                $tjAll=$rawatJalanTotals[$year]['_total']??['pasien_baru'=>0,'kunjungan'=>0];
                $sheet->setCellValue([$col,$dataRow],$tjAll['pasien_baru']); $sheet->setCellValue([$col+1,$dataRow],$tjAll['kunjungan']); $col+=2;
            } else {
                $tj=$rawatJalanTotals[$year]??['pasien_baru'=>0,'kunjungan'=>0];
                $sheet->setCellValue([$col,$dataRow],$tj['pasien_baru']); $sheet->setCellValue([$col+1,$dataRow],$tj['kunjungan']); $col+=2;
            }
        }
        $sheet->getStyle('A'.$dataRow.':'.$endColLetter.$dataRow)->applyFromArray(['font'=>['bold'=>true],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'F8F9FA']]]);

        // ── Borders & auto-size ────────────────────────────
        $lastColFinal = 2 + (count($years) * $subCols);
        $lastColLetter2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColFinal);
        foreach (range('A', $lastColLetter2) as $cid) $sheet->getColumnDimension($cid)->setAutoSize(true);
        $sheet->getStyle('A5:'.$lastColLetter2.$dataRow)->applyFromArray(['borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN]]]);
        $firstDataRow = $showMonths ? 9 : 8;
        $sheet->getStyle('C'.$firstDataRow.':'.$lastColLetter2.$dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}

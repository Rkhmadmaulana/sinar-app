<?php

namespace App\Http\Controllers;

use App\Charts\Chart;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RajalController extends Controller
{
    /**
     * Menampilkan data poliklinik umum (mengecualikan poli penunjang)
     */
    public function poliklinik(Chart $chart, Request $request)
    {
        $filters = $this->getFilters($request);
        
        // Data form
        $data = $this->getDashboardData($filters, 'general');

        return view('rm.rajal.poliklinik', array_merge($filters, $data, [
            'pilihan_poli' => $this->getPilihanPoli(),
            'pilihan_dokter' => $this->getPilihanDokter($filters['kdpoli']),
            'pilihan_cara_bayar' => $this->getPilihanCaraBayar(),
        ]));
    }

    /**
     * Menampilkan data poliklinik khusus (HDL, LAB, RAD, dll) secara umum
     */
    public function allpoliklinikkhusus(Chart $chart, Request $request, $kd_poli = null)
    {
        $filters = $this->getFilters($request, $kd_poli);
        
        // Data dashboard
        $data = $this->getDashboardData($filters, 'specific', function ($query) use ($kd_poli) {
            return $query->where('reg_periksa.kd_poli', $kd_poli);
        });

        return view('rm.rajal.poliklinikkhusus', array_merge($filters, $data, [
            'pilihan_dokter' => $this->getPilihanDokter($kd_poli),
            'pilihan_cara_bayar' => $this->getPilihanCaraBayar(),
        ]));
    }

    /**
     * Menampilkan data IGD
     */
    public function igdk(Chart $chart, Request $request, $kd_poli = 'IGDK')
    {
        $filters = $this->getFilters($request, $kd_poli);
        
        // Khusus IGD: Filter dokter spesifik
        $filters['allowed_doctors'] = ['D15', 'D17', 'dr.sofi'];

        $data = $this->getDashboardData($filters, 'igd');

        return view('rm.rajal.igdk', array_merge($filters, $data, [
            'pilihan_dokter' => $this->getPilihanDokterIGD($filters),
            'pilihan_cara_bayar' => $this->getPilihanCaraBayar(),
        ]));
    }

    /**
     * Menampilkan data Hemodialisa (HDL)
     */
    public function hdl(Chart $chart, Request $request, $kd_poli = 'HDL')
    {
        $filters = $this->getFilters($request, $kd_poli);
        
        // Khusus HDL: Filter dokter spesifik
        $filters['allowed_doctors'] = ['D57'];

        $data = $this->getDashboardData($filters, 'hdl');

        return view('rm.rajal.hemodialisa', array_merge($filters, $data, [
            'pilihan_dokter' => $this->getPilihanDokter($kd_poli),
            'pilihan_cara_bayar' => $this->getPilihanCaraBayar(),
        ]));
    }

    /**
     * Menampilkan data Laboratorium
     */
    public function lab(Chart $chart, Request $request, $kd_poli = 'LAB')
    {
        $filters = $this->getFilters($request, $kd_poli);
        $data = $this->getDashboardData($filters, 'lab');

        return view('rm.rajal.lab', array_merge($filters, $data, [
            'pilihan_dokter' => $this->getPilihanDokter($kd_poli),
            'pilihan_cara_bayar' => $this->getPilihanCaraBayar(),
        ]));
    }

    /**
     * Menampilkan data Radiologi
     */
    public function radiologi(Chart $chart, Request $request, $kd_poli = 'RAD')
    {
        $filters = $this->getFilters($request, $kd_poli);
        $data = $this->getDashboardData($filters, 'rad');

        return view('rm.rajal.radiologi', array_merge($filters, $data, [
            'pilihan_dokter' => $this->getPilihanDokter($kd_poli),
            'pilihan_cara_bayar' => $this->getPilihanCaraBayar(),
        ]));
    }

    // ================= HELPER FUNCTIONS ==================

    /**
     * Mengambil filter input dari request
     */
    private function getFilters(Request $request, $kd_poli = null)
    {
        return [
            'tgl1' => $request->input('tgl1'),
            'tgl2' => $request->input('tgl2'),
            'kdpoli' => $kd_poli ?? $request->input('poli'),
            'kddokter' => $request->input('dokter'),
            'cara_bayarpj' => $request->input('cara_bayar'),
            'status' => $request->input('status'),
            'kd_pj' => $request->input('cara_bayar') // Alias untuk view
        ];
    }

    /**
     * Mengambil semua data statistik dashboard
     * 
     * @param array $filters
     * @param string $mode 'general', 'specific', 'igd', 'hdl', 'lab', 'rad'
     * @param callable|null $customQueryModifier
     * @return array
     */
    private function getDashboardData(array $filters, string $mode, callable $customQueryModifier = null)
    {
        // 1. Data Line Chart (Kunjungan Umum & BPJS)
        $chartData = $this->getMergedLineChartData($filters, $mode);
        
        // 2. Data Statistik Umum (Pie/Bar Charts)
        $baseQuery = $this->buildBaseQuery($filters, $mode, $customQueryModifier);
        
        // Poli
        $poliQuery = clone $baseQuery;
        $poliData = $this->getGenericStats(
            $poliQuery->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli'), 
            'poliklinik.nm_poli', 
            'LEFT(poliklinik.nm_poli, 20) as nama_poli', 
            'poliklinik.kd_poli', 
            'poliklinik.nm_poli'
        );
        
        // Dokter
        $dokterQuery = clone $baseQuery->join('dokter', 'dokter.kd_dokter', '=', 'reg_periksa.kd_dokter');
        $dokterData = $this->getGenericStats(
            $dokterQuery, 
            'nama', // <--- SAMA dengan 'as nama' (sebelumnya 'dokter.nm_dokter' salah)
            'LEFT(dokter.nm_dokter, 20) as nama', 
            'dokter.kd_dokter', 
            'dokter.nm_dokter'
        );

        // Cara Bayar
        $caraBayarQuery = clone $baseQuery->join('penjab', 'penjab.kd_pj', '=', 'reg_periksa.kd_pj');
        $caraBayarData = $this->getGenericStats(
            $caraBayarQuery, 
            'nama_cara_bayar', // <--- Parameter baru
            'png_jawab as nama_cara_bayar, reg_periksa.kd_pj as cara_bayar', // <--- Select nama & kode
            'reg_periksa.kd_pj', 
            'png_jawab'
        );

        // Status (Sudah/Batal/Lainnya)
        $sttsData = $this->getGenericStats($baseQuery, 'stts', 'stts', 'stts', 'stts');

        // Pasien Baru/Lama
        $sttsDaftarData = $this->getGenericStats($baseQuery, 'stts_daftar', 'stts_daftar', 'stts_daftar', 'stts_daftar');

                // --- DATA PASIEN (Base untuk JK dan Geografis) ---
        $pasienQuery = clone $baseQuery->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis');

        // KABUPATEN - BATASI 10 TERATAS
        $kabQuery = clone $pasienQuery->join('kabupaten', 'kabupaten.kd_kab', '=', 'pasien.kd_kab');
        $kabData = $this->getGenericStats(
            $kabQuery, 
            'kab',
            'LEFT(kabupaten.nm_kab, 30) as kab', 
            'kabupaten.nm_kab', 
            'kabupaten.nm_kab',
            10  // UBAH DARI 20 JADI 10
        );

        // KECAMATAN - BATASI 10 TERATAS
        $kecQuery = clone $pasienQuery->join('kecamatan', 'kecamatan.kd_kec', '=', 'pasien.kd_kec');
        $kecData = $this->getGenericStats(
            $kecQuery, 
            'kecamatan',
            'kecamatan.nm_kec as kecamatan', 
            'kecamatan.nm_kec', 
            'kecamatan.nm_kec',
            10  // UBAH DARI 20 JADI 10
        );

        // KELURAHAN - BATASI 10 TERATAS
        $kelQuery = clone $pasienQuery->join('kelurahan', 'kelurahan.kd_kel', '=', 'pasien.kd_kel');
        $kelData = $this->getGenericStats(
            $kelQuery, 
            'kel',
            'LEFT(kelurahan.nm_kel, 30) as kel', 
            'kelurahan.nm_kel', 
            'kelurahan.nm_kel',
            10  // UBAH DARI 20 JADI 10
        );
        
        // JK (Jenis Kelamin)
        // Perbaikan: 'jk' sudah benar jika select pasien.jk
        $jkData = $this->getGenericStats(
            $pasienQuery, 
            'jk', 
            'pasien.jk as jk', 
            'pasien.jk', 
            'pasien.jk'
        );

        $jkData['labels'] = array_map(function($label) {
            if (str_starts_with($label, 'L:')) {
                return str_replace('L:', 'Laki-Laki:', $label);
            } elseif (str_starts_with($label, 'P:')) {
                return str_replace('P:', 'Perempuan:', $label);
            }
            return $label;
        }, $jkData['labels']);

         // Perujuk
        // Di Controller, ubah limit jadi 15
        $rujukData = $this->getGenericStats(
            clone $baseQuery->join('rujuk_masuk', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat'), 
            'perujuk', 
            'LEFT(rujuk_masuk.perujuk, 25) as perujuk',  // UBAH dari 30 jadi 25
            'rujuk_masuk.perujuk', 
            'rujuk_masuk.perujuk',
            15  // UBAH dari 30 jadi 15
        );

        // Prosedur (ICD 9)
        $prosedurData = [];
        if (!in_array($filters['kdpoli'], ['LAB', 'RAD'])) {
            $procQuery = clone $baseQuery
                ->join('prosedur_pasien', 'prosedur_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('icd9', 'icd9.kode', '=', 'prosedur_pasien.kode');
            $prosedurData = $this->getGenericStats(
                $procQuery, 
                'nama', // <--- SAMA dengan 'as nama' (sebelumnya 'deskripsi_pendek' salah)
                'LEFT(icd9.deskripsi_pendek, 30) as nama', 
                'icd9.kode', 
                'icd9.deskripsi_pendek',
                10
            );
        }

        // Diagnosa (ICD 10)
        $diagQuery = clone $baseQuery
            ->join('diagnosa_pasien', 'diagnosa_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('penyakit', 'penyakit.kd_penyakit', '=', 'diagnosa_pasien.kd_penyakit');
        $diagnosaData = $this->getGenericStats(
            $diagQuery, 
            'nama', // <--- SAMA dengan 'as nama' (sebelumnya 'nm_penyakit' salah)
            'LEFT(penyakit.nm_penyakit, 30) as nama', 
            'penyakit.kd_penyakit', 
            'penyakit.nm_penyakit',
            10
        );

        // Pelayanan (Tindakan)
        $pelayananData = $this->getPelayananStats($filters, $mode);

        //throw new \Exception(json_encode($kecData));
        return array_merge([
            // Chart Line
            'umum' => $chartData['umum'],
            'bpjs' => $chartData['bpjs'],
            'labelstat' => $chartData['labels'],
            'judul_line' => $chartData['judul'],
            'subjudul_line' => $chartData['subjudul'],
            
            // Data Arrays formatted (percentages included in labels usually, or separate if needed)
            'data' => $poliData['data'], 
            'labels' => $poliData['labels'],
            'judul_pie_poli' => 'Data Kunjungan Per Poli',
            'subjudul_pie_poli' => $chartData['subjudul'],
            'warnapoli' => $this->getColors(),

            // Dokter
            'datadokter' => $dokterData['data'],
            'labeldokter' => $dokterData['labels'],
            'judul_pie_dokter' => ($mode === 'igd') ? 'Data Kunjungan Ibu Hamil' : 'Data Kunjungan Per Dokter',
            'subjudul_pie_dokter' => $chartData['subjudul'],
            'warnadokter' => $this->getColors(),

            // Cara Bayar
            'datacara_bayar' => $caraBayarData['data'],
            'labelcara_bayar' => $caraBayarData['labels'],
            'judul_pie_cara_bayar' => 'Data Kunjungan Cara Bayar',
            'subjudul_pie_cara_bayar' => '',
            'warnabayar' => $this->getColors(),

            // Status
            'datastts' => $sttsData['data'],
            'labelsstts' => $sttsData['labels'],
            'judul_pie_stts' => 'Data Kunjungan Per Status',
            'subjudul_pie_stts' => '',
            'warnastts' => ['#7FFF00', '#DC143C'],

            // Status Daftar (Baru/Lama)
            'data_stts_daftar' => $sttsDaftarData['data'],
            'labels_stts_daftar' => $sttsDaftarData['labels'],
            'judul_bar_stts_daftar' => 'Data Kunjungan Pasien Lama dan Baru',
            'subjudul_bar_stts_daftar' => '',
            'warnastts_daftar' => ['#3cb371', '#ffa500'],

            // JK
            'data_jk' => $jkData['data'],
            'labels_jk' => $jkData['labels'],
            'judul_bar_jk' => 'Data Kunjungan Jenkel',
            'subjudul_bar_jk' => '',
            'warnajk' => ['#ffa500', '#3cb371'],

            // Geografis
            'data_sql_kab' => $kabData['data'],
            'labels_kab' => $kabData['labels'],
            'judul_pie_sql_kab' => 'Data Kunjungan Per Kabupaten',
            'subjudul_pie_sql_kab' => $chartData['subjudul'],
            'warna_sql_Kabupaten' => ['#FFD700'],

            'data_kecamatan' => $kecData['data'],
            'labels_kecamatan' => $kecData['labels'],
            'judul_pie_kecamatan' => 'Data Kunjungan Per Kecamatan',
            'subjudul_pie_kecamatan' => '',
            'warnakec' => ['#ADFF2F'],

            'data_sql_kel' => $kelData['data'],
            'labels_kel' => $kelData['labels'],
            'judul_pie_sql_kel' => 'Data Kunjungan kelurahan',
            'subjudul_pie_sql_kel' => $chartData['subjudul'],
            'warna_sql_kelurahan' => ['#4169E1'],

            // Perujuk
            'data_sql_rujuk_masuk' => $rujukData['data'],
            'labels_rujuk_masuk' => $rujukData['labels'],
            'judul_pie_sql_rujuk_masuk' => 'Data Perujuk Masuk',
            'subjudul_pie_sql_rujuk_masuk' => $chartData['subjudul'],
            'warnaperujuk' => ['#00FFFF', '#3cb371'],

            // Prosedur & Diagnosa
            'data_sqlprosedur' => $prosedurData['data'] ?? [],
            'labelsprosedur' => $prosedurData['labels'] ?? [],
            'judul_pie_sqlprosedur' => 'Data Prosedur (ICD9)',
            'subjudul_pie_sqlprosedur' => $chartData['subjudul'],
            'warna_sqlprosedur' => ['#0da168'],

            'data_sqldiagnosa' => $diagnosaData['data'],
            'labelsdiagnosa' => $diagnosaData['labels'],
            'judul_pie_sqldiagnosa' => 'Data Diagnosa (ICD10)',
            'subjudul_pie_sqldiagnosa' => $chartData['subjudul'],
            'warna_sqldiagnosa' => ['#9ea10d'],

            // Pelayanan
            'datapel' => $pelayananData['data'],
            'labelspel' => $pelayananData['labels'],
            'judul_pie_pel' => 'Data Trend Pelayanan Poliklinik',
            'subjudul_pie_pel' => $chartData['subjudul'],
            'warnapel' => ['#008FFB'],

        ], $this->getSubJudulSpecifics($mode, $chartData['subjudul']));
    }

    /**
     * Membangun Query dasar berdasarkan filter dan mode
     */
    private function buildBaseQuery(array $filters, string $mode, callable $customModifier = null)
    {
        $query = DB::table('reg_periksa')
            ->where('reg_periksa.status_lanjut', 'Ralan');

        // Terapkan Filter Tanggal
        $query->when($filters['tgl1'] && $filters['tgl2'], function ($q) use ($filters) {
            return $q->whereBetween('tgl_registrasi', [$filters['tgl1'], $filters['tgl2']]);
        }, function ($q) {
            return $q->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        });

        // Terapkan Filter Dokter (Termasuk list dokter khusus untuk IGD/HDL)
        if (isset($filters['allowed_doctors'])) {
            $query->whereIn('reg_periksa.kd_dokter', $filters['allowed_doctors']);
        } else {
            $query->when($filters['kddokter'], function ($q) use ($filters) {
                return $q->where('reg_periksa.kd_dokter', $filters['kddokter']);
            });
        }

        // Terapkan Filter Status
        $query->when($filters['status'], function ($q) use ($filters) {
            return $q->where('reg_periksa.stts', $filters['status']);
        }, function ($q) use ($mode) {
            // Logic default status jika tidak dipilih
            if ($mode === 'igd') {
                return $q->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')->orWhere('reg_periksa.stts', 'Belum');
                });
            }
            return $q->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')->orWhere('reg_periksa.stts', 'Batal');
            });
        });

        // Terapkan Filter Cara Bayar
        $query->when($filters['cara_bayarpj'], function ($q) use ($filters) {
            return $q->where('reg_periksa.kd_pj', $filters['cara_bayarpj']);
        });

        // Modifikasi Khusus (IGD Logic khusus, dll)
        if ($customModifier) {
            $customModifier($query);
        }

        return $query;
    }

    /**
     * Mengambil data statistik generik (Group By + Count) dan memformatnya
     */
    private function getGenericStats($query, $labelField, $selectField, $groupField1, $groupField2 = null, $limit = null)
    {
        $dataQuery = clone $query;
        
        $dataQuery->groupBy($groupField1, $groupField2)
             ->select(DB::raw("$selectField"), DB::raw('count(*) as total'))
             ->orderBy('total', 'desc');

        if ($limit) {
            $dataQuery->limit($limit);
        }

        $results = $dataQuery->get();
        
        return $this->formatChartData($results, $labelField);
    }

    /**
     * Mengambil data Pelayanan (Rawat JL Dr/Pr)
     */
    private function getPelayananStats(array $filters, string $mode)
    {
        // Tentukan tabel berdasarkan poli
        $tableR = 'reg_periksa'; // Default
        $joinTable = '';
        $joinTableService = '';

        if ($mode === 'lab') {
            $joinTable = 'periksa_lab';
            $joinTableService = 'jns_perawatan_lab';
        } elseif ($mode === 'rad') {
            $joinTable = 'periksa_radiologi';
            $joinTableService = 'jns_perawatan_radiologi';
        } else {
            // Poli Umum / IGD / HDL
            $joinTable = DB::raw('(
                SELECT no_rawat, kd_jenis_prw FROM rawat_jl_dr
                UNION
                SELECT no_rawat, kd_jenis_prw FROM rawat_jl_drpr
                UNION
                SELECT no_rawat, kd_jenis_prw FROM rawat_jl_pr 
            ) as r');
            $joinTableService = 'jns_perawatan as j';
        }

        $query = DB::table($tableR)
            ->join($joinTable, 'r.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('reg_periksa.status_lanjut', 'Ralan');

        // Filter Tanggal & Status (sama seperti buildBaseQuery tapi perlu manual disini karena join kompleks)
        $query->when($filters['tgl1'] && $filters['tgl2'], function ($q) use ($filters) {
            return $q->whereBetween('tgl_registrasi', [$filters['tgl1'], $filters['tgl2']]);
        }, function ($q) {
            return $q->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        });

        // Filter Poli Khusus untuk Lab/Rad jika perlu
        if (in_array($mode, ['lab', 'rad'])) {
             $query->when($filters['kdpoli'], function ($q) use ($filters) {
                return $q->where('reg_periksa.kd_poli', $filters['kdpoli']);
            });
        }

        $query->rightJoin($joinTableService, 'r.kd_jenis_prw', '=', 'j.kd_jenis_prw')
            ->groupBy('j.nm_perawatan')
            ->select(['j.nm_perawatan', DB::raw('COUNT(j.nm_perawatan) as total')])
            ->limit(10)
            ->orderBy('total', 'desc');

        $results = $query->get();
        return $this->formatChartData($results, 'nm_perawatan');
    }

    /**
     * Menggabungkan fungsi getChartData 1-7 menjadi satu fungsi dinamis
     */
    private function getMergedLineChartData(array $filters, string $mode)
    {
        $umumData = $this->getLineChartDataQuery('PJ2', $filters, $mode);
        $bpjsData = $this->getLineChartDataQuery('BPJ', $filters, $mode);

        // Sorting dan merging
        $umumData = $umumData->sortBy(['year', 'month']);
        $bpjsData = $bpjsData->sortBy(['year', 'month']);

        $allMonths = $umumData->pluck('month')->merge($bpjsData->pluck('month'))->unique()->sort();

        $mergedData = $allMonths->map(function ($month) use ($umumData, $bpjsData) {
            $umum = $umumData->where('month', $month)->first();
            $bpjs = $bpjsData->where('month', $month)->first();

            return [
                'year' => $umum ? $umum->year : ($bpjs ? $bpjs->year : null),
                'month' => $month,
                'month_name' => $umum ? $umum->month_name : ($bpjs ? $bpjs->month_name : null),
                'umum_total' => $umum ? $umum->total : 0,
                'bpjs_total' => $bpjs ? $bpjs->total : 0,
            ];
        });

        $subjudul = !empty($filters['tgl1']) && !empty($filters['tgl2']) 
            ? 'Tanggal ' . date('d F Y', strtotime($filters['tgl1'])) . ' S/D ' . date('d F Y', strtotime($filters['tgl2'])) 
            : 'Tanggal ' . date('d F Y', strtotime('first day of this month')) . ' S/D ' . date('d F Y', strtotime('today'));

        return [
            'umum' => $mergedData->pluck('umum_total')->toArray(),
            'bpjs' => $mergedData->pluck('bpjs_total')->toArray(),
            'labels' => $mergedData->pluck('month_name')->toArray(),
            'judul' => 'Data Kunjungan Umum dan BPJS',
            'subjudul' => $subjudul
        ];
    }

    private function getLineChartDataQuery(string $kd_pj, array $filters, string $mode)
    {
        $query = DB::table('reg_periksa')
            ->where('status_lanjut', 'Ralan')
            ->where('kd_pj', $kd_pj);

        // Logika Exclude/Include Poli berdasarkan Mode
        if ($mode === 'general') {
            $query->whereNotIn('kd_poli', ['HDL', 'LAB', 'RAD', 'IGDK', 'MCU', 'IRM']);
        } elseif (in_array($mode, ['igd', 'hdl', 'lab', 'rad', 'specific'])) {
            $query->where('kd_poli', $filters['kdpoli']);
        }

        // Terapkan Filters
        $query->when($filters['tgl1'] && $filters['tgl2'], function ($q) use ($filters) {
            return $q->whereBetween('tgl_registrasi', [$filters['tgl1'], $filters['tgl2']]);
        }, function ($q) {
            return $q->whereBetween('tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        });

        if (isset($filters['allowed_doctors'])) {
            $query->whereIn('kd_dokter', $filters['allowed_doctors']);
        } elseif ($filters['kddokter']) {
            $query->where('kd_dokter', $filters['kddokter']);
        }

        $query->when($filters['status'], function ($q) use ($filters) {
            return $q->where('stts', $filters['status']);
        }, function ($q) use ($mode) {
            if ($mode === 'igd') {
                return $q->where(function ($query) {
                    $query->where('stts', 'Sudah')->orWhere('stts', 'Belum');
                });
            }
            return $q->where(function ($query) {
                $query->where('stts', 'Sudah')->orWhere('stts', 'Batal');
            });
        });

        return $query->groupBy('kd_pj', DB::raw('YEAR(tgl_registrasi)'), DB::raw('MONTH(tgl_registrasi)'))
            ->select(
                'kd_pj',
                DB::raw('YEAR(tgl_registrasi) as year'),
                DB::raw('MONTH(tgl_registrasi) as month'),
                DB::raw('count(*) as total')
            )
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }

    /**
     * Helper: Format collection menjadi array data (persentase) dan labels
     */
    private function formatChartData(Collection $collection, string $nameField)
    {
        $data = $collection->pluck('total')->toArray();
        $totalSum = array_sum($data);

        $labels = [];
        foreach ($collection as $index => $item) {
            $name = $item->$nameField ?? $item->nama_poli ?? 'Unknown';
            $count = $item->total;
            
            // Perbaikan perhitungan persentase
            $perc = $totalSum > 0 ? round(($count / $totalSum) * 100, 2) : 0;
            
            // PENTING: Batasi panjang nama maksimal 30 karakter
            $shortName = mb_strlen($name) > 30 ? mb_substr($name, 0, 27) . '...' : $name;
            
            $labels[] = "$shortName: $count ($perc%)";
        }

        return [
            'data' => $data,
            'labels' => $labels
        ];
    }

    // ================= DATA SELECT OPTIONS ==================

    private function getPilihanPoli()
    {
        return DB::table('poliklinik')
            ->where('kd_poli', '!=', 'HDL')
            ->where('kd_poli', '!=', 'LAB')
            ->where('kd_poli', '!=', 'RAD')
            ->where('kd_poli', '!=', 'IGDK')
            ->where('kd_poli', '!=', 'MCU')
            ->where('kd_poli', '!=', 'IRM')
            ->select('kd_poli', 'nm_poli')
            ->get();
    }

    private function getPilihanDokter($kdpoli = null)
    {
        $query = DB::table('dokter')
            ->join('jadwal', 'jadwal.kd_dokter', '=', 'dokter.kd_dokter')
            ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
            ->select('dokter.kd_dokter', 'dokter.nm_dokter');

        if ($kdpoli) {
            $query->where('jadwal.kd_poli', $kdpoli);
        }
        return $query->get();
    }

    private function getPilihanDokterIGD($filters)
    {
        // Khusus logic IGD: join ke reg_periksa untuk dapat tanggal range
        return DB::table('dokter')
            ->join('reg_periksa', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('reg_periksa.kd_poli', 'IGDK')
            ->whereIn('reg_periksa.kd_dokter', ['D15', 'D17', 'dr.sofi'])
            ->when($filters['tgl1'] && $filters['tgl2'], function ($q) use ($filters) {
                return $q->whereBetween('reg_periksa.tgl_registrasi', [$filters['tgl1'], $filters['tgl2']]);
            })
            ->groupBy('dokter.kd_dokter', 'dokter.nm_dokter')
            ->select('dokter.kd_dokter', 'dokter.nm_dokter')
            ->get();
    }

    private function getPilihanCaraBayar()
    {
        return DB::table('penjab')->select('kd_pj', 'png_jawab')->get();
    }

    private function getColors()
    {
        return [
            '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0',
            '#80effe', '#0077B5', '#ff6384', '#c9cbcf', '#0057ff',
            '#00a9f4', '#2ccdc9', '#5e72e4'
        ];
    }

    private function getSubJudulSpecifics($mode, $defaultSub)
    {
        // Menyesuaikan judul jika ada kebutuhan khusus
        return [
            'subjudul_pie_poli' => $defaultSub,
            'subjudul_pie_dokter' => $defaultSub,
            //'judul_pie_kecamatan' => $defaultSub,
            // dst jika ada perbedaan
        ];
    }
}
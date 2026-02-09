<?php

namespace App\Http\Controllers;

use App\Charts\Chart;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
     * Updated to include gender breakdowns for tooltips.
     * Diagnosa & Prosedur using fresh queries to avoid Cartesian Product.
     */
    private function getDashboardData(array $filters, string $mode, callable $customQueryModifier = null)
    {
        // 1. Data Line Chart
        $chartData = $this->getMergedLineChartData($filters, $mode);
        
        // 2. Data Statistik Umum (Base Query)
        $baseQuery = $this->buildBaseQuery($filters, $mode, $customQueryModifier);
        
        // --- HELPER: Join Gender Data ---
        $baseQueryWithGender = clone $baseQuery->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis');

        // POLI
        $poliQuery = clone $baseQueryWithGender->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli');
        $poliData = $this->getGenericStatsWithGender(
            $poliQuery, 
            'poliklinik.nm_poli',
            'poliklinik.nm_poli as nama_poli', 
            'poliklinik.kd_poli', 
            'poliklinik.nm_poli'
        );
        
        // DOKTER
        $dokterQuery = clone $baseQueryWithGender->join('dokter', 'dokter.kd_dokter', '=', 'reg_periksa.kd_dokter');
        $dokterData = $this->getGenericStatsWithGender(
            $dokterQuery, 
            'nama', 
            'dokter.nm_dokter as nama', 
            'dokter.kd_dokter', 
            'dokter.nm_dokter'
        );

        // CARA BAYAR
        $caraBayarQuery = clone $baseQueryWithGender->join('penjab', 'penjab.kd_pj', '=', 'reg_periksa.kd_pj');
        $caraBayarData = $this->getGenericStatsWithGender(
            $caraBayarQuery, 
            'nama_cara_bayar', 
            'png_jawab as nama_cara_bayar, reg_periksa.kd_pj as cara_bayar', 
            'reg_periksa.kd_pj', 
            'png_jawab'
        );

        // STATUS (Sudah/Batal) - No gender needed for status
        $sttsData = $this->getGenericStats(clone $baseQuery, 'stts', 'stts', 'stts', 'stts');

        // STATUS DAFTAR (Baru/Lama)
        $sttsDaftarData = $this->getGenericStatsWithGender(clone $baseQueryWithGender, 'stts_daftar', 'stts_daftar', 'stts_daftar', 'stts_daftar');

        // GEOGRAFIS (Kab, Kec, Kel)
        $kabQuery = clone $baseQueryWithGender->join('kabupaten', 'kabupaten.kd_kab', '=', 'pasien.kd_kab');
        $kabData = $this->getGenericStatsWithGender(
            $kabQuery, 
            'kab',
            'kabupaten.nm_kab as kab', 
            'kabupaten.nm_kab', 
            'kabupaten.nm_kab',
            10
        );

        $kecQuery = clone $baseQueryWithGender->join('kecamatan', 'kecamatan.kd_kec', '=', 'pasien.kd_kec');
        $kecData = $this->getGenericStatsWithGender(
            $kecQuery, 
            'kecamatan',
            'kecamatan.nm_kec as kecamatan', 
            'kecamatan.nm_kec', 
            'kecamatan.nm_kec',
            10
        );

        $kelQuery = clone $baseQueryWithGender->join('kelurahan', 'kelurahan.kd_kel', '=', 'pasien.kd_kel');
        $kelData = $this->getGenericStatsWithGender(
            $kelQuery, 
            'kel',
            'kelurahan.nm_kel as kel', 
            'kelurahan.nm_kel', 
            'kelurahan.nm_kel',
            10
        );
        
        // JENIS KELAMIN
        $jkData = $this->getGenericStats(
            clone $baseQueryWithGender, 
            'jk', 
            'pasien.jk as jk', 
            'pasien.jk', 
            'pasien.jk'
        );
        $jkData['labels'] = array_map(function($label) {
            return str_replace(['L', 'P'], ['Laki-Laki', 'Perempuan'], $label);
        }, $jkData['labels']);

        // PERUJUK
        $rujukQuery = clone $baseQueryWithGender->join('rujuk_masuk', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat');
        $rujukData = $this->getGenericStatsWithGender(
            $rujukQuery, 
            'perujuk', 
            'rujuk_masuk.perujuk as perujuk',  
            'rujuk_masuk.perujuk', 
            'rujuk_masuk.perujuk',
            15
        );

        // ================= PERBAIKAN QUERY (FRESH) =================

        // PROCEDUR (ICD 9) - Fresh Query
        $prosedurData = [];
        if (!in_array($filters['kdpoli'], ['LAB', 'RAD'])) {
            $procQuery = DB::table('reg_periksa')
                ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis') // For Gender
                ->join('prosedur_pasien', 'prosedur_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('icd9', 'icd9.kode', '=', 'prosedur_pasien.kode')
                ->where('reg_periksa.status_lanjut', 'Ralan');

            // Apply Filters
            $procQuery->when($filters['tgl1'] && $filters['tgl2'], function ($q) use ($filters) {
                return $q->whereBetween('reg_periksa.tgl_registrasi', [$filters['tgl1'], $filters['tgl2']]);
            }, function ($q) {
                return $q->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
            });

            $procQuery->when($filters['status'], function ($q) use ($filters) {
                return $q->where('reg_periksa.stts', $filters['status']);
            }, function ($q) {
                return $q->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')->orWhere('reg_periksa.stts', 'Batal');
                });
            });

            if ($filters['kddokter']) {
                $procQuery->where('reg_periksa.kd_dokter', $filters['kddokter']);
            }
            if ($filters['cara_bayarpj']) {
                $procQuery->where('reg_periksa.kd_pj', $filters['cara_bayarpj']);
            }

            $prosedurData = $this->getGenericStatsWithGender(
                $procQuery, 
                'nama', 
                'icd9.deskripsi_pendek as nama, icd9.kode as kode_icd', 
                'icd9.kode', 
                'icd9.deskripsi_pendek',
                10
            );
        }

        // DIAGNOSA (ICD 10) - Fresh Query
        $diagQuery = DB::table('reg_periksa')
            ->join('diagnosa_pasien', 'diagnosa_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('penyakit', 'penyakit.kd_penyakit', '=', 'diagnosa_pasien.kd_penyakit')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where(function($q) {
                $q->where('penyakit.kd_penyakit', 'NOT LIKE', 'Z%')
                ->where('penyakit.kd_penyakit', 'NOT LIKE', 'O%')
                ->where('penyakit.kd_penyakit', 'NOT LIKE', 'P%')
                ->where('penyakit.kd_penyakit', 'NOT LIKE', 'T%')
                ->where('penyakit.kd_penyakit', 'NOT LIKE', 'S%');
            });

        // Apply Filters
        $diagQuery->when($filters['tgl1'] && $filters['tgl2'], function ($q) use ($filters) {
            return $q->whereBetween('reg_periksa.tgl_registrasi', [$filters['tgl1'], $filters['tgl2']]);
        }, function ($q) {
            return $q->whereBetween('reg_periksa.tgl_registrasi', [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('today'))]);
        });

        $diagQuery->when($filters['status'], function ($q) use ($filters) {
            return $q->where('reg_periksa.stts', $filters['status']);
        }, function ($q) {
            return $q->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')->orWhere('reg_periksa.stts', 'Batal');
            });
        });

        if ($filters['kddokter']) {
            $diagQuery->where('reg_periksa.kd_dokter', $filters['kddokter']);
        }
        if ($filters['cara_bayarpj']) {
            $diagQuery->where('reg_periksa.kd_pj', $filters['cara_bayarpj']);
        }

        $diagnosaData = $this->getGenericStatsWithGender(
            $diagQuery, 
            'nama', 
            'penyakit.nm_penyakit as nama, penyakit.kd_penyakit as kode_icd',
            'penyakit.kd_penyakit', 
            'penyakit.nm_penyakit',
            10,
            true // Parameter baru: merge diabetes
        );

        // Pelayanan
        $pelayananData = $this->getPelayananStats($filters, $mode);

        // Merge and Return
        return array_merge([
            'umum' => $chartData['umum'],
            'bpjs' => $chartData['bpjs'],
            'labelstat' => $chartData['labels'],
            'judul_line' => $chartData['judul'],
            'subjudul_line' => $chartData['subjudul'],
            
            // POLI
            'data' => $poliData['data'], 
            'labels' => $poliData['labels'],
            'percentages_poli' => $poliData['percentages'] ?? [],
            'tooltip_gender' => $poliData['gender_data'], 
            'judul_pie_poli' => 'Data Kunjungan Per Poli',
            'subjudul_pie_poli' => $chartData['subjudul'],
            'warnapoli' => $this->getColors(),

            // DOKTER
            'datadokter' => $dokterData['data'],
            'labeldokter' => $dokterData['labels'],
            'percentages_dokter' => $dokterData['percentages'] ?? [],
            'tooltip_gender_dokter' => $dokterData['gender_data'], 
            'judul_pie_dokter' => ($mode === 'igd') ? 'Data Kunjungan Ibu Hamil' : 'Data Kunjungan Per Dokter',
            'subjudul_pie_dokter' => $chartData['subjudul'],
            'warnadokter' => $this->getColors(),

            // CARA BAYAR
            'datacara_bayar' => $caraBayarData['data'],
            'labelcara_bayar' => $caraBayarData['labels'],
            'percentages_cara_bayar' => $caraBayarData['percentages'] ?? [],
            'tooltip_gender_cara_bayar' => $caraBayarData['gender_data'], 
            'judul_pie_cara_bayar' => 'Data Kunjungan Cara Bayar',
            'subjudul_pie_cara_bayar' => '',
            'warnabayar' => $this->getColors(),

            // STATUS
            'datastts' => $sttsData['data'],
            'labelsstts' => $sttsData['labels'],
            'tooltip_gender_stts' => [], // No gender needed usually
            'judul_pie_stts' => 'Data Kunjungan Per Status',
            'subjudul_pie_stts' => '',
            'warnastts' => ['#7FFF00', '#DC143C'],

            // STATUS DAFTAR
            'data_stts_daftar' => $sttsDaftarData['data'],
            'labels_stts_daftar' => $sttsDaftarData['labels'],
            'percentages_stts_daftar' => $sttsDaftarData['percentages'] ?? [],
            'tooltip_gender_stts_daftar' => $sttsDaftarData['gender_data'], 
            'judul_bar_stts_daftar' => 'Data Kunjungan Pasien Lama dan Baru',
            'subjudul_bar_stts_daftar' => '',
            'warnastts_daftar' => ['#3cb371', '#ffa500'],

            // JK
            'data_jk' => $jkData['data'],
            'labels_jk' => $jkData['labels'],
            'tooltip_gender_jk' => [],
            'judul_bar_jk' => 'Data Kunjungan Jenkel',
            'subjudul_bar_jk' => '',
            'warnajk' => ['#ffa500', '#3cb371'],

            // GEOGRAFIS
            'data_sql_kab' => $kabData['data'],
            'labels_kab' => $kabData['labels'],
            'percentages_kab' => $kabData['percentages'] ?? [],
            'tooltip_gender_kab' => $kabData['gender_data'], 
            'judul_pie_sql_kab' => 'Data Kunjungan Per Kabupaten',
            'subjudul_pie_sql_kab' => $chartData['subjudul'],
            'warna_sql_Kabupaten' => ['#FFD700'],

            'data_kecamatan' => $kecData['data'],
            'labels_kecamatan' => $kecData['labels'],
            'percentages_kecamatan' => $kecData['percentages'] ?? [],
            'tooltip_gender_kecamatan' => $kecData['gender_data'], 
            'judul_pie_kecamatan' => 'Data Kunjungan Per Kecamatan',
            'subjudul_pie_kecamatan' => '',
            'warnakec' => ['#ADFF2F'],

            'data_sql_kel' => $kelData['data'],
            'labels_kel' => $kelData['labels'],
            'percentages_kel' => $kelData['percentages'] ?? [],
            'tooltip_gender_kel' => $kelData['gender_data'], 
            'judul_pie_sql_kel' => 'Data Kunjungan kelurahan',
            'subjudul_pie_sql_kel' => $chartData['subjudul'],
            'warna_sql_kelurahan' => ['#4169E1'],

            // PERUJUK
            'data_sql_rujuk_masuk' => $rujukData['data'],
            'labels_rujuk_masuk' => $rujukData['labels'],
            'percentages_rujuk_masuk' => $rujukData['percentages'] ?? [],
            'tooltip_gender_rujuk' => $rujukData['gender_data'], 
            'judul_pie_sql_rujuk_masuk' => 'Data Perujuk Masuk',
            'subjudul_pie_sql_rujuk_masuk' => $chartData['subjudul'],
            'warnaperujuk' => ['#00FFFF', '#3cb371'],

            // Prosedur & Diagnosa
            'data_sqlprosedur' => $prosedurData['data'] ?? [],
            'labelsprosedur' => $prosedurData['labels'] ?? [],
            'percentages_prosedur' => $prosedurData['percentages'] ?? [],
            'fullnames_prosedur' => $prosedurData['fullNames'] ?? [],
            'kode_prosedur' => $prosedurData['kode_icd'] ?? [],
            'tooltip_gender_prosedur' => $prosedurData['gender_data'] ?? [], 
            'judul_pie_sqlprosedur' => 'Data Prosedur (ICD9)',
            'subjudul_pie_sqlprosedur' => $chartData['subjudul'],
            'warna_sqlprosedur' => ['#0da168'],

            'data_sqldiagnosa' => $diagnosaData['data'],
            'labelsdiagnosa' => $diagnosaData['labels'],
            'percentages_diagnosa' => $diagnosaData['percentages'],
            'fullnames_diagnosa' => $diagnosaData['fullNames'], 
            'kode_diagnosa' => $diagnosaData['kode_icd'],
            'tooltip_gender_diagnosa' => $diagnosaData['gender_data'], 
            'judul_pie_sqldiagnosa' => 'Data Diagnosa (ICD10)',
            'subjudul_pie_sqldiagnosa' => $chartData['subjudul'],
            'warna_sqldiagnosa' => ['#9ea10d'],

            // Pelayanan
            'datapel' => $pelayananData['data'],
            'labelspel' => $pelayananData['labels'],
            'percentages_pelayanan' => $pelayananData['percentages'] ?? [],
            'fullnames_pelayanan' => $pelayananData['fullNames'] ?? [],
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
     * Helper function to get Stats WITH Gender Breakdown
     */
    private function getGenericStatsWithGender($query, $labelField, $selectField, $groupField1, $groupField2 = null, $limit = null, $mergeDiabetes = false)
    {
        // Jika merge diabetes, gunakan subquery approach
        if ($mergeDiabetes) {
            // Clone untuk subquery
            $subquery = clone $query;
            
            $subquery->select(
                DB::raw("CASE 
                    WHEN penyakit.kd_penyakit LIKE 'E11%' 
                        OR penyakit.kd_penyakit LIKE 'E12%' 
                        OR penyakit.kd_penyakit LIKE 'E13%' 
                        OR penyakit.kd_penyakit LIKE 'E14%' 
                    THEN 'Diabetes Mellitus'
                    ELSE penyakit.nm_penyakit 
                END as nama"),
                DB::raw("CASE 
                    WHEN penyakit.kd_penyakit LIKE 'E11%' 
                        OR penyakit.kd_penyakit LIKE 'E12%' 
                        OR penyakit.kd_penyakit LIKE 'E13%' 
                        OR penyakit.kd_penyakit LIKE 'E14%' 
                    THEN 'E11-E14'
                    ELSE penyakit.kd_penyakit 
                END as kode_icd"),
                'pasien.jk'
            );
            
            // Query utama dari subquery
            $dataQuery = DB::table(DB::raw("({$subquery->toSql()}) as transformed"))
                ->mergeBindings($subquery)
                ->select(
                    'nama',
                    'kode_icd',
                    'kode_icd as group_key',
                    'jk',
                    DB::raw('count(*) as total')
                )
                ->groupBy('nama', 'kode_icd', 'jk');
            
            $rawResults = $dataQuery->get();
            
            // Aggregate manual
            $aggregated = [];
            foreach ($rawResults as $row) {
                $key = $row->group_key;
                
                if (!isset($aggregated[$key])) {
                    $aggregated[$key] = (object)[
                        'nama' => $row->nama,
                        'kode_icd' => $row->kode_icd,
                        'group_key' => $row->group_key,
                        'total' => 0,
                        'gender' => ['L' => 0, 'P' => 0]
                    ];
                }
                
                $aggregated[$key]->total += $row->total;
                
                $jk = strtoupper($row->jk);
                if ($jk === 'L' || $jk === 'P') {
                    $aggregated[$key]->gender[$jk] += $row->total;
                }
            }
            
            // Sort dan limit
            $results = collect(array_values($aggregated))
                ->sortByDesc('total')
                ->take($limit ?? 10)
                ->values();
            
            // Format output
            $formattedData = [
                'data' => $results->pluck('total')->toArray(),
                'labels' => $results->pluck('nama')->toArray(),
                'kode_icd' => $results->pluck('kode_icd')->toArray(),
                'fullNames' => $results->pluck('nama')->toArray(),
                'gender_data' => $results->pluck('gender')->toArray(),
            ];
            
            // Hitung persentase
            $totalSum = array_sum($formattedData['data']);
            $formattedData['percentages'] = array_map(function($count) use ($totalSum) {
                return $totalSum > 0 ? round(($count / $totalSum) * 100, 2) : 0;
            }, $formattedData['data']);
            
            return $formattedData;
        }
        
        // Logic original untuk non-diabetes
        $dataQuery = clone $query;
        $dataQuery->groupBy($groupField1, $groupField2)
            ->select(
                DB::raw("$selectField"), 
                DB::raw("$groupField1 as group_key"),
                DB::raw('count(*) as total')
            )
            ->orderBy('total', 'desc');
        if ($limit) $dataQuery->limit($limit);
        
        $results = $dataQuery->get();

        $genderQuery = clone $query;
        $genderQuery->groupBy($groupField1, $groupField2, 'pasien.jk')
            ->select(
                DB::raw("$selectField"), 
                DB::raw("$groupField1 as group_key"),
                'pasien.jk', 
                DB::raw('count(*) as total')
            );
        
        $genderResults = $genderQuery->get();

        $formattedData = $this->formatChartData($results, $labelField);
        
        $kodeArray = [];
        foreach ($results as $item) {
            $kodeArray[] = $item->kode_icd ?? $item->group_key;
        }
        $formattedData['kode_icd'] = $kodeArray; 

        $genderMap = []; 
        foreach ($genderResults as $row) {
            $key = $row->group_key;
            if (!isset($genderMap[$key])) {
                $genderMap[$key] = ['L' => 0, 'P' => 0];
            }
            $jk = strtoupper($row->jk);
            if ($jk === 'L' || $jk === 'P') {
                $genderMap[$key][$jk] += (int)$row->total;
            }
        }

        $finalGenderData = [];
        foreach ($results as $item) {
            $key = $item->group_key;
            $finalGenderData[] = $genderMap[$key] ?? ['L' => 0, 'P' => 0];
        }

        $formattedData['gender_data'] = $finalGenderData;

        return $formattedData;
    }

    /**
     * Mengambil data statistik generik (Group By + Count) tanpa gender breakdown
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

        // Filter Tanggal & Status
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
    private function formatChartData(Collection $collection, string $nameField, bool $useCode = false)
    {
        $data = $collection->pluck('total')->toArray();
        $totalSum = array_sum($data);

        $labels = [];
        $percentages = [];
        $fullNames = []; // ← TAMBAH: untuk tooltip
        
        foreach ($collection as $index => $item) {
            $name = $item->$nameField ?? $item->nama_poli ?? 'Unknown';
            $count = $item->total;
            
            $perc = $totalSum > 0 ? round(($count / $totalSum) * 100, 2) : 0;
            
            // ← LOGIKA BARU: Gunakan kode atau nama
            //if ($useCode && isset($item->group_key)) {
            //    // Untuk diagnosa/prosedur: gunakan kode (group_key)
            //    $labels[] = $item->group_key;
            //} else {
                // Untuk yang lain: gunakan nama (max 30 char)
                //$shortName = mb_strlen($name) > 30 ? mb_substr($name, 0, 27) . '...' : $name;
                //$labels[] = $shortName;
            //}

            $labels[] = $name;
            
            $percentages[] = $perc;
            $fullNames[] = $name; // ← Nama lengkap untuk tooltip
        }

        return [
            'data' => $data,
            'labels' => $labels,
            'percentages' => $percentages,
            'fullNames' => $fullNames // ← TAMBAH
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

    public function downloadDiagnosaExcel(Request $request)
    {
        $filters = $this->getFilters($request);
        
        // Query data diagnosa (KHUSUS PASIEN BARU)
        $diagQuery = DB::table('reg_periksa')
            ->join('diagnosa_pasien', 'diagnosa_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('penyakit', 'penyakit.kd_penyakit', '=', 'diagnosa_pasien.kd_penyakit')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->where('reg_periksa.stts_daftar', 'Baru') // Filter KHUSUS Pasien Baru
            ->where(function($q) {
                $q->where('penyakit.kd_penyakit', 'NOT LIKE', 'Z%')
                ->where('penyakit.kd_penyakit', 'NOT LIKE', 'O%')
                ->where('penyakit.kd_penyakit', 'NOT LIKE', 'P%')
                ->where('penyakit.kd_penyakit', 'NOT LIKE', 'T%')
                ->where('penyakit.kd_penyakit', 'NOT LIKE', 'S%');
            });

        // Apply Filters (Date, Status, etc)
        $diagQuery->when($filters['tgl1'] && $filters['tgl2'], function ($q) use ($filters) {
            return $q->whereBetween('reg_periksa.tgl_registrasi', [$filters['tgl1'], $filters['tgl2']]);
        }, function ($q) {
            return $q->whereBetween('reg_periksa.tgl_registrasi', [
                date('Y-m-d', strtotime('first day of this month')), 
                date('Y-m-d', strtotime('today'))
            ]);
        });

        $diagQuery->when($filters['status'], function ($q) use ($filters) {
            return $q->where('reg_periksa.stts', $filters['status']);
        }, function ($q) {
            return $q->where(function ($query) {
                $query->where('reg_periksa.stts', 'Sudah')->orWhere('reg_periksa.stts', 'Batal');
            });
        });

        if ($filters['kddokter']) $diagQuery->where('reg_periksa.kd_dokter', $filters['kddokter']);
        if ($filters['cara_bayarpj']) $diagQuery->where('reg_periksa.kd_pj', $filters['cara_bayarpj']);
        if ($filters['kdpoli']) $diagQuery->where('reg_periksa.kd_poli', $filters['kdpoli']);

        // Ambil data Pasien Baru
        $diagnosaData = $this->getGenericStatsWithGender(
            $diagQuery, 
            'nama', 
            'penyakit.nm_penyakit as nama, penyakit.kd_penyakit as kode_icd',
            'penyakit.kd_penyakit', 
            'penyakit.nm_penyakit',
            10,
            true // merge diabetes
        );
        
        // Ambil total kunjungan (Total Patient Visits)
        // Catatan: Kolom "Total Jumlah Kunjungan" biasanya menghitung SEMUA kunjungan (Baru + Lama)
        // Jadi kita query ULANG tanpa filter stts_daftar untuk kolom G ini.
        $totalKunjunganPerDiagnosa = [];
        
        for ($i = 0; $i < count($diagnosaData['kode_icd']); $i++) {
            $kodeICD = $diagnosaData['kode_icd'][$i];
            
            // Query KUNJUNGAN TOTAL (Baru & Lama)
            $queryKunjungan = DB::table('reg_periksa')
                ->join('diagnosa_pasien', 'diagnosa_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('penyakit', 'penyakit.kd_penyakit', '=', 'diagnosa_pasien.kd_penyakit')
                ->where('reg_periksa.status_lanjut', 'Ralan');
                // HILANGKAN filter stts_daftar di sini agar semua kunjungan terhitung
            
            if ($kodeICD == 'E11-E14') {
                $queryKunjungan->where(function($q) {
                    $q->where('penyakit.kd_penyakit', 'LIKE', 'E11%')
                    ->orWhere('penyakit.kd_penyakit', 'LIKE', 'E12%')
                    ->orWhere('penyakit.kd_penyakit', 'LIKE', 'E13%')
                    ->orWhere('penyakit.kd_penyakit', 'LIKE', 'E14%');
                });
            } else {
                $queryKunjungan->where('penyakit.kd_penyakit', $kodeICD);
            }
            
            $queryKunjungan->when($filters['tgl1'] && $filters['tgl2'], function ($q) use ($filters) {
                return $q->whereBetween('reg_periksa.tgl_registrasi', [$filters['tgl1'], $filters['tgl2']]);
            }, function ($q) {
                return $q->whereBetween('reg_periksa.tgl_registrasi', [
                    date('Y-m-d', strtotime('first day of this month')), 
                    date('Y-m-d', strtotime('today'))
                ]);
            });

            if ($filters['kddokter']) $queryKunjungan->where('reg_periksa.kd_dokter', $filters['kddokter']);
            if ($filters['cara_bayarpj']) $queryKunjungan->where('reg_periksa.kd_pj', $filters['cara_bayarpj']);
            if ($filters['kdpoli']) $queryKunjungan->where('reg_periksa.kd_poli', $filters['kdpoli']);
            
            $queryKunjungan->when($filters['status'], function ($q) use ($filters) {
                return $q->where('reg_periksa.stts', $filters['status']);
            }, function ($q) {
                return $q->where(function ($query) {
                    $query->where('reg_periksa.stts', 'Sudah')->orWhere('reg_periksa.stts', 'Batal');
                });
            });
            
            $totalKunjunganPerDiagnosa[$i] = $queryKunjungan->distinct('reg_periksa.no_rawat')->count('reg_periksa.no_rawat');
        }
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $tgl2 = $filters['tgl2'] ?? date('Y-m-d');
        $tahun = date('Y', strtotime($tgl2));
        
        // 1. Title Header (Baris 1-3)
        $sheet->setCellValue('A1', '10 PENYAKIT TERBANYAK PADA PASIEN RAWAT JALAN MENURUT BAB ICD-X DI RUMAH SAKIT');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'KABUPATEN KOTABARU');
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A3', 'TAHUN ' . $tahun);
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Table Header (Baris 4 & 5) - STRUKTUR BENAR
        
        // Baris 4 (Header Utama)
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'ICD-X');
        $sheet->setCellValue('C4', 'Golongan Sebab Sakit');
        $sheet->setCellValue('D4', 'Pasien Baru'); // Merge D4:F4
        $sheet->setCellValue('G4', 'Total Jumlah Kunjungan'); // Merge G4:G5

        // Merge Vertikal
        $sheet->mergeCells('A4:A5');
        $sheet->mergeCells('B4:B5');
        $sheet->mergeCells('C4:C5');
        $sheet->mergeCells('G4:G5');

        // Merge Horizontal Pasien Baru
        $sheet->mergeCells('D4:F4');

        // Baris 5 (Sub Header)
        $sheet->setCellValue('D5', 'Laki-laki');
        $sheet->setCellValue('E5', 'Perempuan');
        $sheet->setCellValue('F5', 'Jumlah');

        // Style Header
        $headerRange = 'A4:G5';
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
        
        // Isi Data
        $row = 6;
        $totalLakiLaki = 0;
        $totalPerempuan = 0;
        $totalJumlah = 0;
        $grandTotalKunjungan = 0;
        
        $maxData = min(10, count($diagnosaData['data']));
        
        for ($i = 0; $i < $maxData; $i++) {
            $kodeICD = $diagnosaData['kode_icd'][$i] ?? '';
            $namaPenyakit = $diagnosaData['labels'][$i] ?? 'Unknown';
            
            // Data ini sudah TERFILTER PASIEN BARU (L+P)
            $genderData = $diagnosaData['gender_data'][$i] ?? ['L' => 0, 'P' => 0];
            $lakiLaki = $genderData['L'] ?? 0;
            $perempuan = $genderData['P'] ?? 0;
            $jumlah = $lakiLaki + $perempuan; 
            
            $totalKunjunganDiagnosa = $totalKunjunganPerDiagnosa[$i] ?? 0;
            
            $sheet->setCellValue('A' . $row, ($i + 1));
            $sheet->setCellValue('B' . $row, $kodeICD);
            $sheet->setCellValue('C' . $row, $namaPenyakit);
            $sheet->setCellValue('D' . $row, $lakiLaki);
            $sheet->setCellValue('E' . $row, $perempuan);
            $sheet->setCellValue('F' . $row, $jumlah);
            $sheet->setCellValue('G' . $row, $totalKunjunganDiagnosa);
            
            $totalLakiLaki += $lakiLaki;
            $totalPerempuan += $perempuan;
            $totalJumlah += $jumlah;
            $grandTotalKunjungan += $totalKunjunganDiagnosa;
            
            $row++;
        }
        
        // Baris Total
        $sheet->setCellValue('A' . $row, 'J u m l a h');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->setCellValue('D' . $row, $totalLakiLaki);
        $sheet->setCellValue('E' . $row, $totalVisitsPerempuan = $totalPerempuan); // Corrected variable name typo if any
        $sheet->setCellValue('F' . $row, $totalJumlah);
        $sheet->setCellValue('G' . $row, $grandTotalKunjungan);
        
        // Style Data Rows
        $dataRange = 'A6:G' . ($row - 1);
        if($row > 6) {
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }
        
        // Style Baris Total
        $totalRange = 'A' . $row . ':G' . $row;
        $sheet->getStyle($totalRange)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        
        // Column Width
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(50);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(25);
        
        $sheet->getRowDimension(4)->setRowHeight(25);
        $sheet->getRowDimension(5)->setRowHeight(25);
        
        $fileName = 'Data_Diagnosa_ICD10_' . $tahun . '_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function getTotalKunjungan(array $filters)
    {
        $query = DB::table('reg_periksa')
            ->where('status_lanjut', 'Ralan');
        
        // Filter poli (exclude khusus)
        $query->whereNotIn('kd_poli', ['HDL', 'LAB', 'RAD', 'IGDK', 'MCU', 'IRM']);
        
        // Filter tanggal
        if (!empty($filters['tgl1']) && !empty($filters['tgl2'])) {
            $query->whereBetween('tgl_registrasi', [$filters['tgl1'], $filters['tgl2']]);
        } else {
            $query->whereBetween('tgl_registrasi', [
                date('Y-m-d', strtotime('first day of this month')), 
                date('Y-m-d')
            ]);
        }
        
        // Filter tambahan
        if (!empty($filters['kdpoli'])) {
            $query->where('kd_poli', $filters['kdpoli']);
        }
        if (!empty($filters['kddokter'])) {
            $query->where('kd_dokter', $filters['kddokter']);
        }
        if (!empty($filters['kd_pj'])) {
            $query->where('kd_pj', $filters['kd_pj']);
        }
        if (!empty($filters['status'])) {
            $query->where('stts', $filters['status']);
        } else {
            $query->where(function($q) {
                $q->where('stts', 'Sudah')->orWhere('stts', 'Batal');
            });
        }
        
        return $query->count();
    }
}
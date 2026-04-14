<?php

namespace App\Http\Controllers;

use App\Charts\Chart;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RanapController extends Controller
{
    public function ranap(Chart $chart, Request $request)
    {
        // --- 1. HANDLE INPUT ---
        $tgl1Input = $request->input('tgl1');
        $tgl2Input = $request->input('tgl2');
        $kodekamar = $request->input('kode_kamar');
        $kodepj = $request->input('kodepj');

        // Format Tanggal
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

        // Ambil Data Dashboard (cached 2 min)
        $cacheKey = 'ranap_dashboard_' . md5(serialize([$formattedTgl1, $formattedTgl2, $kodekamar, $kodepj]));
        $data = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($formattedTgl1, $formattedTgl2, $kodekamar, $kodepj, $tgl1, $tgl2) {
            return $this->getDashboardData($formattedTgl1, $formattedTgl2, $kodekamar, $kodepj, $tgl1, $tgl2);
        });
        
        // Ambil Data Dropdown (Pilihan)
        $pilihan_cara_bayar = DB::table('penjab')->select('kd_pj', 'png_jawab')->get();
        $pilihan_kamar = DB::table('bangsal')->select('kd_bangsal', 'nm_bangsal')->get();

        // Return View
        $viewData = array_merge([
            'tgl1' => $formattedTgl1,
            'tgl2' => $formattedTgl2,
            'kodekamar' => $kodekamar,
            'kodepj' => $kodepj,
            'pilihan_cara_bayar' => $pilihan_cara_bayar,
            'pilihan_kamar' => $pilihan_kamar,
        ], $data);
        $viewData['layout'] = $request->ajax() ? 'layout.raw' : 'layout.app';
        $viewData['isAjax'] = $request->ajax();
        return view('rm.ranap.ranap', $viewData);
    }

    /**
     * Logic Utama Pengambilan Data
     */
    private function getDashboardData($tgl1, $tgl2, $kodekamar, $kodepj, $objTgl1, $objTgl2)
    {
        // Subjudul Umum
        $subjudul_line = $objTgl1->format('d F Y') . ' S/D ' . $objTgl2->format('d F Y');

        // --- 1. LINE CHART: CARA BAYAR (KUNJUNGAN) ---
        $insuranceCodes = ['PJ2', 'BPJ', 'PJ3', 'PJ4', 'PJ7', 'PJ8'];
        $lineChartData = $this->getMergedLineChartData($insuranceCodes, $tgl1, $tgl2, $kodekamar, 'insurance');

        // --- 2. LINE CHART: KELAS KAMAR ---
        $roomClasses = ['Kelas 3', 'Kelas 2', 'Kelas 1', 'Kelas Utama', 'Kelas VIP', 'Kelas VVIP'];
        $lineChartDataKelas = $this->getMergedLineChartData($roomClasses, $tgl1, $tgl2, $kodekamar, 'kelas', $kodepj);

        // --- 3. PREPARE BASE QUERY ---
        // Kita butuh query dasar yang join ke kamar_inap karena semua chart Ranap butuh tanggal masuk
        $baseQuery = DB::table('kamar_inap as a')
            ->join('reg_periksa as b', 'a.no_rawat', '=', 'b.no_rawat')
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_masuk', [$tgl1, $tgl2]);
            })
            ->when($kodekamar, function ($query) use ($kodekamar) {
                return $query->where('a.kd_kamar', 'like', '%' . $kodekamar . '%');
            })
            ->when($kodepj, function ($query) use ($kodepj) {
                return $query->where('b.kd_pj', $kodepj);
            });

        // --- 4. DATA CHART (PIE/BAR) ---

        // A. Cara Bayar
        $queryCaraBayar = clone $baseQuery->join('penjab', 'penjab.kd_pj', '=', 'b.kd_pj');
        $dataCaraBayar = $this->getGenericStats(
            $queryCaraBayar,
            'png_jawab', // Key Field harus sama dengan property object
            'b.kd_pj as cara_bayar, penjab.png_jawab',
            'b.kd_pj',
            'png_jawab'
        );

        // B. Kelas Kamar
        $queryKelas = clone $baseQuery->join('kamar as c', 'a.kd_kamar', '=', 'c.kd_kamar');
        $dataKelas = $this->getGenericStats(
            $queryKelas,
            'kelas', // Key Field sama dengan alias SQL
            'c.kelas as kelas',
            'c.kelas',
            'c.kelas'
        );

        // C. Geografis (Butuh join ke pasien)
        $queryPasien = clone $baseQuery->join('pasien', 'pasien.no_rkm_medis', '=', 'b.no_rkm_medis');

        // Kabupaten
        $queryKab = clone $queryPasien->join('kabupaten', 'kabupaten.kd_kab', '=', 'pasien.kd_kab');
        $dataKab = $this->getGenericStats(
            $queryKab,
            'kab', // Sama dengan alias '... as kab'
            'LEFT(kabupaten.nm_kab, 30) as kab',
            'kabupaten.nm_kab',
            'kabupaten.nm_kab',
            20
        );

        // Kecamatan
        $queryKec = clone $queryPasien->join('kecamatan', 'kecamatan.kd_kec', '=', 'pasien.kd_kec');
        $dataKec = $this->getGenericStats(
            $queryKec,
            'kecamatan', // Sama dengan alias '... as kecamatan'
            'kecamatan.nm_kec as kecamatan',
            'kecamatan.nm_kec',
            'kecamatan.nm_kec',
            20
        );

        // Kelurahan
        $queryKel = clone $queryPasien->join('kelurahan', 'kelurahan.kd_kel', '=', 'pasien.kd_kel');
        $dataKel = $this->getGenericStats(
            $queryKel,
            'kel', // Sama dengan alias '... as kel'
            'LEFT(kelurahan.nm_kel, 30) as kel',
            'kelurahan.nm_kel',
            'kelurahan.nm_kel',
            20
        );

        // D. Prosedur (ICD9)
        $queryProsedur = clone $baseQuery
            ->join('prosedur_pasien', 'prosedur_pasien.no_rawat', '=', 'a.no_rawat')
            ->join('icd9', 'icd9.kode', '=', 'prosedur_pasien.kode');
        $dataProsedur = $this->getGenericStats(
            $queryProsedur,
            'nama', // Sama dengan alias '... as nama'
            'LEFT(icd9.deskripsi_pendek, 30) as nama',
            'icd9.kode',
            'icd9.deskripsi_pendek',
            20
        );

        // E. Diagnosa (ICD10)
        $queryDiagnosa = clone $baseQuery
            ->join('diagnosa_pasien', 'diagnosa_pasien.no_rawat', '=', 'a.no_rawat')
            ->join('penyakit', 'penyakit.kd_penyakit', '=', 'diagnosa_pasien.kd_penyakit');
        $dataDiagnosa = $this->getGenericStats(
            $queryDiagnosa,
            'nama', // Sama dengan alias '... as nama'
            'LEFT(penyakit.nm_penyakit, 30) as nama',
            'penyakit.kd_penyakit',
            'penyakit.nm_penyakit',
            20
        );

        // F. Pelayanan Dokter (Rawat Inap Dr)
        $queryPelDokter = DB::table('rawat_inap_drpr as r')
            ->join('kamar_inap as a', 'a.no_rawat', '=', 'r.no_rawat')
            ->leftJoin('dokter as j', 'r.kd_dokter', '=', 'j.kd_dokter')
            ->whereBetween('r.tgl_perawatan', [$tgl1, $tgl2])
            ->when($kodepj, function ($q) use ($kodepj) {
                return $q->where('r.kd_pj', $kodepj); // Asumsi ada relasi atau butuh join reg_periksa jika diperlukan
            })
            ->groupBy('j.nm_dokter')
            ->select('j.nm_dokter', DB::raw('COUNT(j.nm_dokter) as total'))
            ->orderByDesc('total')
            ->get();
        $dataPelDokter = $this->formatChartData($queryPelDokter, 'nm_dokter');

        // G. Pelayanan Perawat (Rawat Inap Pr)
        $queryPelPr = DB::table('rawat_inap_pr as r')
            ->join('kamar_inap as a', 'a.no_rawat', '=', 'r.no_rawat')
            ->rightJoin('petugas as j', 'r.nip', '=', 'j.nip')
            ->whereBetween('r.tgl_perawatan', [$tgl1, $tgl2])
            ->when($kodepj, function ($q) use ($kodepj) {
                return $q->where('r.kd_pj', $kodepj);
            })
            ->groupBy('j.nama')
            ->select('j.nama', DB::raw('COUNT(j.nama) as total'))
            ->orderby('total', 'desc')
            ->limit(20)
            ->get();
        $dataPelPr = $this->formatChartData($queryPelPr, 'nama');

        // H. Pelayanan (Union Tindakan)
        $tableUnion = DB::raw('(
            SELECT no_rawat, kd_jenis_prw FROM rawat_inap_dr
            UNION ALL
            SELECT no_rawat, kd_jenis_prw FROM rawat_inap_drpr
            UNION ALL
            SELECT no_rawat, kd_jenis_prw FROM rawat_inap_pr 
        ) as r');
        
        $queryPelUnion = DB::table('kamar_inap as a')
            ->join('reg_periksa as b', 'b.no_rawat', '=', 'a.no_rawat')
            ->join($tableUnion, 'r.no_rawat', '=', 'b.no_rawat')
            ->whereBetween('a.tgl_masuk', [$tgl1, $tgl2])
            ->when($kodekamar, function ($q) use ($kodekamar) {
                return $q->where('a.kd_kamar', 'like', '%' . $kodekamar . '%');
            })
            ->when($kodepj, function ($q) use ($kodepj) {
                return $q->where('b.kd_pj', $kodepj);
            })
            ->rightJoin('jns_perawatan_inap as j', 'r.kd_jenis_prw', '=', 'j.kd_jenis_prw')
            ->groupBy('j.nm_perawatan')
            ->select('j.nm_perawatan', DB::raw('COUNT(j.nm_perawatan) as total'))
            ->orderby('total', 'desc')
            ->limit(20)
            ->get();
        $dataPelUnion = $this->formatChartData($queryPelUnion, 'nm_perawatan');

        // I. Status (Lama/Baru)
        $queryStatus = clone $baseQuery->where('b.status_lanjut', 'Ranap');
        $dataStatus = $this->getGenericStats(
            $queryStatus,
            'stts_daftar',
            'stts_daftar',
            'stts_daftar'
        );

        // J. Catatan Gizi (Adime)
        $dataAdime = DB::table('catatan_adime_gizi')
            ->whereNotNull('no_rawat')
            ->whereBetween('tanggal', [$tgl1, $tgl2])
            ->select(DB::raw('count(*) as total'))
            ->first();
        
        $totalAdime = $dataAdime->total ?? 0;
        $dataAdimeFormatted = [
            'data_adime' => [$totalAdime],
            'totalCatatanGizi' => $dataAdime,
            'labels_adime' => ["Total Catatan Gizi: $totalAdime ({$this->safePercent(0, $totalAdime)}%)"],
            'percentage_adime' => [$this->safePercent(0, $totalAdime)]
        ];

        // --- 5. MERGE RETURN DATA ---
        return [
            // Line Chart Insurance
            'umum' => $lineChartData['series']['PJ2'] ?? [],
            'bpjs' => $lineChartData['series']['BPJ'] ?? [],
            'inhealth' => $lineChartData['series']['PJ3'] ?? [],
            'jamkesda' => $lineChartData['series']['PJ4'] ?? [],
            'bkk' => $lineChartData['series']['PJ7'] ?? [],
            'pjkn' => $lineChartData['series']['PJ8'] ?? [],
            'labelstat' => $lineChartData['labels'],
            'judul_line' => 'Data Kunjungan Pasien',
            'subjudul_line' => $subjudul_line,

            // Line Chart Kelas
            'kelas3' => $lineChartDataKelas['series']['Kelas 3'] ?? [],
            'kelas2' => $lineChartDataKelas['series']['Kelas 2'] ?? [],
            'kelas1' => $lineChartDataKelas['series']['Kelas 1'] ?? [],
            'utama' => $lineChartDataKelas['series']['Kelas Utama'] ?? [],
            'vip' => $lineChartDataKelas['series']['Kelas VIP'] ?? [],
            'vvip' => $lineChartDataKelas['series']['Kelas VVIP'] ?? [],
            'labelstatkelas' => $lineChartDataKelas['labels'],
            'judul_linekelas' => 'Data Kunjungan Pasien Per Kelas',
            'subjudul_linekelas' => $subjudul_line,

            // Stats
            'datacara_bayar' => $dataCaraBayar['data'],
            'labelcara_bayar' => $dataCaraBayar['labels'],
            'judul_pie_cara_bayar' => 'Data Kunjungan Cara Bayar',
            'subjudul_pie_cara_bayar' => '',
            'warnabayar' => $this->getColors(),

            'datakelas' => $dataKelas['data'],
            'labelkelas' => $dataKelas['labels'],
            'judul_pie_kelas' => 'Data Kunjungan Pasien Per Kelas',
            'subjudul_pie_kelas' => '',
            'warnakelas' => $this->getColors(),

            'data_sql_kab' => $dataKab['data'],
            'labels_kab' => $dataKab['labels'],
            'judul_pie_sql_kab' => 'Data Kunjungan Per Kabupaten',
            'subjudul_pie_sql_kab' => $subjudul_line,
            'warna_sql_Kabupaten' => $this->getColors(),

            'data_kecamatan' => $dataKec['data'],
            'labels_kecamatan' => $dataKec['labels'],
            'judul_pie_kecamatan' => 'Data Kunjungan Per Kecamatan',
            'subjudul_pie_kecamatan' => $subjudul_line,
            'warnakec' => $this->getColors(),

            'data_sql_kel' => $dataKel['data'],
            'labels_kel' => $dataKel['labels'],
            'judul_pie_sql_kel' => 'Data Kunjungan Per Kelurahan',
            'subjudul_pie_sql_kel' => $subjudul_line,
            'warna_sql_kelurahan' => $this->getColors(),

            'data_sqlprosedur' => $dataProsedur['data'],
            'labelsprosedur' => $dataProsedur['labels'],
            'judul_pie_sqlprosedur' => 'Data Prosedur (ICD9)',
            'subjudul_pie_sqlprosedur' => $subjudul_line,
            'warna_sqlprosedur' => $this->getColors(),

            'data_sqldiagnosa' => $dataDiagnosa['data'],
            'labelsdiagnosa' => $dataDiagnosa['labels'],
            'judul_pie_sqldiagnosa' => 'Data Diagnosa (ICD10)',
            'subjudul_pie_sqldiagnosa' => $subjudul_line,
            'warna_sqldiagnosa' => $this->getColors(),

            'datapeldokter' => $dataPelDokter['data'],
            'labelspeldokter' => $dataPelDokter['labels'],
            'judul_pie_peldokter' => 'Data Trend Pelayanan Dokter Ranap',
            'subjudul_pie_peldokter' => $subjudul_line,
            'warnapeldokter' => $this->getColors(),

            'datapelprw' => $dataPelPr['data'],
            'labelspelprw' => $dataPelPr['labels'],
            'judul_pie_pelprw' => 'Data Trend Pelayanan Perawat Ranap',
            'subjudul_pie_pelprw' => $subjudul_line,
            'warnapelprw' => $this->getColors(),

            'datapel' => $dataPelUnion['data'],
            'labelspel' => $dataPelUnion['labels'],
            'judul_pie_pel' => 'Data Trend Pelayanan Ranap',
            'subjudul_pie_pel' => $subjudul_line,
            'warnapel' => $this->getColors(),

            'data_stts_daftar' => $dataStatus['data'],
            'labels_stts_daftar' => $dataStatus['labels'],
            'judul_bar_stts_daftar' => 'Data Kunjungan Pasien Lama dan Baru',
            'subjudul_bar_stts_daftar' => $subjudul_line,
            'warnastts_daftar' => ['#3cb371', '#ffa500'],

            // Adime
            'totalCatatanGizi' => $dataAdime,
            'judul_bar_adime' => 'Data Adime Gizi',
            'subjudul_bar_adime' => $subjudul_line,
            'labels_adime' => $dataAdimeFormatted['labels_adime'],
            'data_adime' => $dataAdimeFormatted['data_adime'],
            'warnastts_adime' => $this->getColors(),
        ];
    }

    /**
     * Helper untuk Merge dan Sort Line Chart Data
     */
    private function getMergedLineChartData($categories, $tgl1, $tgl2, $kodekamar, $type = 'insurance', $kodepj = null)
    {
        $seriesData = [];
        $labels = [];

        foreach ($categories as $cat) {
            $rawData = ($type === 'insurance') 
                ? $this->getChartData($cat, $tgl1, $tgl2, $kodekamar)
                : $this->getChartData2($cat, $tgl1, $tgl2, $kodekamar, $kodepj);

            $sortedData = $rawData->sortBy(['year', 'month'])->values();
            
            if (empty($labels)) {
                $labels = $sortedData->pluck('month_name')->toArray();
            }
            
            $seriesData[$cat] = $sortedData->pluck('total')->toArray();
        }

        return [
            'series' => $seriesData,
            'labels' => $labels
        ];
    }

    /**
     * Helper Get Data Cara Bayar (Line)
     */
    private function getChartData($kd_pj, $tgl1, $tgl2, $kodekamar)
    {
        return DB::table('reg_periksa as b')
            ->join('kamar_inap as a', 'a.no_rawat', '=', 'b.no_rawat')
            ->where('b.kd_pj', $kd_pj)
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_masuk', [$tgl1, $tgl2]);
            })
            ->when($kodekamar, function ($query) use ($kodekamar) {
                return $query->where('a.kd_kamar', 'like', '%' . $kodekamar . '%');
            })
            ->groupBy('b.kd_pj', DB::raw('YEAR(a.tgl_masuk)'), DB::raw('MONTH(a.tgl_masuk)'))
            ->select(
                'b.kd_pj',
                DB::raw('YEAR(a.tgl_masuk) as year'),
                DB::raw('MONTH(a.tgl_masuk) as month'),
                DB::raw('COUNT(DISTINCT b.no_rawat) as total')
            )
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }

    /**
     * Helper Get Data Kelas (Line)
     */
    private function getChartData2($kelas, $kodepj, $tgl1, $tgl2, $kodekamar)
    {
        return DB::table('reg_periksa as b')
            ->join('kamar_inap as a', 'a.no_rawat', '=', 'b.no_rawat')
            ->join('kamar as c', 'a.kd_kamar', '=', 'c.kd_kamar')
            ->where('c.kelas', $kelas)
            ->when($tgl1 && $tgl2, function ($query) use ($tgl1, $tgl2) {
                return $query->whereBetween('a.tgl_masuk', [$tgl1, $tgl2]);
            })
            ->when($kodekamar, function ($query) use ($kodekamar) {
                return $query->where('a.kd_kamar', 'like', '%' . $kodekamar . '%');
            })
            ->when($kodepj, function ($query) use ($kodepj) {
                return $query->where('b.kd_pj', $kodepj);
            })
            ->groupBy('c.kelas', DB::raw('YEAR(a.tgl_masuk)'), DB::raw('MONTH(a.tgl_masuk)'))
            ->select(
                'c.kelas',
                DB::raw('YEAR(a.tgl_masuk) as year'),
                DB::raw('MONTH(a.tgl_masuk) as month'),
                DB::raw('COUNT(b.no_rawat) as total')
            )
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });
    }

    /**
     * Fungsi Get Generic Stats (Pengganti kode repetitif)
     */
    private function getGenericStats($query, $keyField, $selectString, $groupByField1, $groupByField2 = null, $limit = null)
    {
        $dataQuery = clone $query;
        
        $dataQuery->groupBy($groupByField1, $groupByField2)
             ->select(DB::raw("$selectString"), DB::raw('count(*) as total'))
             ->orderBy('total', 'desc');

        if ($limit) {
            $dataQuery->limit($limit);
        }

        $results = $dataQuery->get();
        
        return $this->formatChartData($results, $keyField);
    }

    /**
     * Fungsi Format Data & Hitung Persen
     * PENTING: $keyField harus SAMA dengan alias SQL
     */
    private function formatChartData($collection, $keyField)
    {
        $data = $collection->pluck('total')->toArray();
        $totalSum = array_sum($data);

        $percentages = [];
        if ($totalSum > 0) {
            $percentages = array_map(function ($value) use ($totalSum) {
                return round(($value / $totalSum) * 100, 2);
            }, $data);
        }

        $labels = [];
        foreach ($collection as $item) {
            // Gunakan $keyField untuk mengambil nama properti dinamis sesuai Alias SQL
            $name = $item->$keyField ?? 'Unknown';
            $count = $item->total;
            
            // Cari persentase berdasarkan nilai count (karena key array numeric)
            $index = array_search($count, $data);
            $perc = isset($percentages[$index]) ? $percentages[$index] : 0;
            
            $labels[] = "$name: $count ($perc%)";
        }

        return [
            'data' => $data,
            'labels' => $labels
        ];
    }

    /**
     * Helper Hitung Persen Aman (khusus untuk Adime/Gizi)
     */
    private function safePercent($val, $total)
    {
        return $total > 0 ? round(($val / $total) * 100, 2) : 0;
    }

    /**
     * Helper Warna Chart
     */
    private function getColors()
    {
        return [
            '#008FFB', '#00E396', '#feb019', '#ff455f', '#775dd0',
            '#80effe', '#0077B5', '#ff6384', '#c9cbcf', '#0057ff',
            '#00a9f4', '#2ccdc9', '#5e72e4'
        ];
    }
}
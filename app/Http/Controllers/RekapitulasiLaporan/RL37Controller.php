<?php

namespace App\Http\Controllers\RekapitulasiLaporan;

use App\Http\Controllers\Controller;
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

class RL37Controller extends Controller{
    ///////////////////////////////////////////////////////////////
    // Laporan RL 3.7 - NEONATAL, BAYI, DAN BALITA
    public function laporanRL37(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $kategoriMap = $this->getKategoriRL37();

        // Get data neonatal dengan perawatan
        $dataNeonatal = DB::table('penilaian_awal_keperawatan_ranap_neonatus as pan')
            ->join('reg_periksa as rp', 'pan.no_rawat', '=', 'rp.no_rawat')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->select('pan.*', 'rp.status_lanjut', 'rp.no_rawat')
            ->get();

        // Get data bayi dengan perawatan
        $dataBayi = DB::table('penilaian_awal_keperawatan_ranap_bayi as pab')
            ->join('reg_periksa as rp', 'pab.no_rawat', '=', 'rp.no_rawat')
            ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->select('pab.*', 'rp.status_lanjut', 'p.tgl_lahir', 'pab.no_rawat')
            ->get();

        // Process neonatal
        foreach ($dataNeonatal as $data) {
            // Get perawatan untuk pasien ini
            $perawatan = $this->getPerawatanPasien($data->no_rawat);
            $this->processNeonatalData($data, $kategoriMap, $perawatan);
        }

        // Process bayi
        foreach ($dataBayi as $data) {
            $perawatan = $this->getPerawatanPasien($data->no_rawat);
            $this->processBayiData($data, $kategoriMap, $tanggalAwal, $perawatan);
        }

        $kategori = array_values($kategoriMap);
        $hospitalInfo = DB::table('setting')->first();

        if ($request->has('download_pdf')) {
            return $this->generateRL37PDF($tanggalAwal, $tanggalAkhir, $kategori, $hospitalInfo);
        }

        return view('rm.laporan_rm.rl37', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'kategori' => $kategori,
            'hospitalInfo' => $hospitalInfo
        ]);
    }

    public function laporanRL37Detail(Request $request)
    {
        $kategoriKode = $request->input('kategori'); // Kode kategori seperti '1.1.1', '4.1', dll
        $rujukanType = $request->input('rujukan_type'); // 'rujukan' atau 'non_rujukan'
        $sumberRujukan = $request->input('sumber'); // 'rs', 'bidan', 'puskes', 'faskes'
        $statusType = $request->input('status'); // 'hidup' atau 'mati'
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');
        
        // Get kategori info
        $kategoriMap = $this->getKategoriRL37();
        $kategoriInfo = $kategoriMap[$kategoriKode] ?? null;
        
        $data = collect();
        
        // Determine which table to query based on kategori
        $isNeonatal = (strpos($kategoriKode, '.') !== false && $kategoriKode[0] <= '7') || 
                    in_array($kategoriKode, ['1', '2', '3', '4', '5', '6', '7']);
        
        if ($isNeonatal) {
            // Query data neonatal
            $query = DB::table('penilaian_awal_keperawatan_ranap_neonatus as pan')
                ->join('reg_periksa as rp', 'pan.no_rawat', '=', 'rp.no_rawat')
                ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
                ->leftJoin('rujuk_masuk as rm', 'rp.no_rawat', '=', 'rm.no_rawat')
                ->leftJoin('kamar_inap as ki', function($join) {
                    $join->on('rp.no_rawat', '=', 'ki.no_rawat')
                        ->where('ki.stts_pulang', '=', 'Meninggal');
                })
                ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
                ->select(
                    'rp.no_rawat',
                    'rp.no_rkm_medis',
                    'p.nm_pasien',
                    'p.jk',
                    'rp.tgl_registrasi',
                    'pan.tanggal as tgl_lahir',
                    'pan.intranatal_bb',
                    'pan.prenatal_uk',
                    'pan.intranatal_kondisi_lahir',
                    'pan.keluhan_utama',
                    'pan.asal_pasien',
                    'rm.perujuk',
                    'rm.alamat as alamat_perujuk',
                    'ki.tgl_keluar as tgl_meninggal',
                    'rp.status_lanjut'
                );
                
            // Filter berdasarkan rujukan type
            if ($rujukanType === 'rujukan') {
                // Ada rujuk_masuk atau asal_pasien bukan internal
                $query->where(function($q) {
                    $q->whereNotNull('rm.no_rawat')
                    ->orWhereNotIn('pan.asal_pasien', ['IGD', 'POLIKLINIK', 'KAMAR BERSALIN', 'KAMAR OPERASI']);
                });
                
                // Filter sumber rujukan
                if ($sumberRujukan) {
                    if ($sumberRujukan === 'rs') {
                        $query->where(function($q) {
                            $q->where('rm.perujuk', 'LIKE', '%rs%')
                            ->orWhere('rm.perujuk', 'LIKE', '%rumah sakit%');
                            //->orWhere('pan.diperoleh_dari', 'LIKE', '%rs%');
                        });
                    } elseif ($sumberRujukan === 'bidan') {
                        $query->where(function($q) {
                            $q->where('rm.perujuk', 'LIKE', '%bidan%');
                            //->orWhere('pan.diperoleh_dari', 'LIKE', '%bidan%');
                        });
                    } elseif ($sumberRujukan === 'puskes') {
                        $query->where(function($q) {
                            $q->where('rm.perujuk', 'LIKE', '%puskesmas%')
                            ->orWhere('rm.perujuk', 'LIKE', '%puskes%');
                            //->orWhere('pan.diperoleh_dari', 'LIKE', '%puskesmas%');
                        });
                    } elseif ($sumberRujukan === 'faskes') {
                        $query->where(function($q) {
                            $q->whereNotNull('rm.perujuk');
                            //->orWhereNotNull('pan.diperoleh_dari');
                        });
                    }
                }
            } else {
                // Non rujukan - tidak ada rujuk_masuk dan asal_pasien internal
                $query->whereNull('rm.no_rawat')
                    ->whereIn('pan.asal_pasien', ['IGD', 'POLIKLINIK', 'KAMAR BERSALIN', 'KAMAR OPERASI']);
            }
            
            $rawData = $query->get();
            
            // Filter data berdasarkan kategori spesifik
            foreach ($rawData as $item) {
                $matchesKategori = $this->checkNeonatalMatchesKategori($item, $kategoriKode, $statusType);
                if ($matchesKategori) {
                    $data->push($item);
                }
            }
            
        } else {
            // Query data bayi dan balita
            $query = DB::table('penilaian_awal_keperawatan_ranap_bayi as pab')
                ->join('reg_periksa as rp', 'pab.no_rawat', '=', 'rp.no_rawat')
                ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
                ->leftJoin('rujuk_masuk as rm', 'rp.no_rawat', '=', 'rm.no_rawat')
                ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
                ->select(
                    'rp.no_rawat',
                    'rp.no_rkm_medis',
                    'p.nm_pasien',
                    'p.jk',
                    'p.tgl_lahir',
                    'rp.tgl_registrasi',
                    'pab.tiba_diruang_rawat',
                    //'pab.diperoleh_dari',
                    'pab.total_nilai',
                    'rm.perujuk',
                    'rm.alamat as alamat_perujuk'
                );
                
            // Filter berdasarkan rujukan type
            if ($rujukanType === 'rujukan') {
                $query->where(function($q) {
                    $q->whereNotNull('rm.no_rawat')
                    ->orWhereNotIn('pab.tiba_diruang_rawat', ['JALAN TANPA BANTUAN', 'KURSI RODA', 'BRANKAR']);
                });
                
                // Filter sumber rujukan
                if ($sumberRujukan) {
                    if ($sumberRujukan === 'rs') {
                        $query->where(function($q) {
                            $q->where('rm.perujuk', 'LIKE', '%rs%');
                            //->orWhere('pab.diperoleh_dari', 'LIKE', '%rs%');
                        });
                    } elseif ($sumberRujukan === 'bidan') {
                        $query->where(function($q) {
                            $q->where('rm.perujuk', 'LIKE', '%bidan%');
                            //->orWhere('pab.diperoleh_dari', 'LIKE', '%bidan%');
                        });
                    } elseif ($sumberRujukan === 'puskes') {
                        $query->where(function($q) {
                            $q->where('rm.perujuk', 'LIKE', '%puskesmas%');
                            //->orWhere('pab.diperoleh_dari', 'LIKE', '%puskesmas%');
                        });
                    }
                }
            } else {
                $query->whereNull('rm.no_rawat')
                    ->whereIn('pab.tiba_diruang_rawat', ['JALAN TANPA BANTUAN', 'KURSI RODA', 'BRANKAR']);
            }
            
            $rawData = $query->get();
            
            // Filter data berdasarkan kategori spesifik
            foreach ($rawData as $item) {
                $matchesKategori = $this->checkBayiMatchesKategori($item, $kategoriKode, $tanggalAwal);
                if ($matchesKategori) {
                    $data->push($item);
                }
            }
        }
        
        return view('rm.laporan_rm.rl37_detail', [
            'data' => $data,
            'kategoriKode' => $kategoriKode,
            'kategoriInfo' => $kategoriInfo,
            'rujukanType' => $rujukanType,
            'sumberRujukan' => $sumberRujukan,
            'statusType' => $statusType,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'isNeonatal' => $isNeonatal
        ]);
    }

    private function checkNeonatalMatchesKategori($data, $kategoriKode, $statusType)
    {
        $bb = floatval($data->intranatal_bb ?? 0);
        if ($bb > 0 && $bb < 100) $bb = $bb * 1000;
        
        $uk = $this->getUKFromString($data->prenatal_uk ?? '');
        $kondisiLahir = strtolower($data->intranatal_kondisi_lahir ?? '');
        
        // Filter status hidup/mati
        $isMati = false;
        if (stripos($kondisiLahir, 'mati') !== false || 
            stripos($kondisiLahir, 'meninggal') !== false ||
            $data->tgl_meninggal !== null) {
            $isMati = true;
        }
        
        if ($statusType === 'hidup' && $isMati) return false;
        if ($statusType === 'mati' && !$isMati) return false;
        
        // Check kategori spesifik
        switch ($kategoriKode) {
            case '1': // Bayi Lahir Hidup
                return !$isMati;
                
            case '1.1': // Lahir Prematur
                return $uk < 37 && !$isMati;
                
            case '1.1.1':
                return $uk < 37 && $bb >= 1500 && $bb < 2500 && !$isMati;
                
            case '1.1.2':
                return $uk < 37 && $bb >= 1000 && $bb < 1500 && !$isMati;
                
            case '1.1.3':
                return $uk < 37 && $bb < 1000 && !$isMati;
                
            case '1.2': // Lahir Non Prematur
                return $uk >= 37 && $uk <= 41 && !$isMati;
                
            case '1.2.1':
                return $uk >= 37 && $uk <= 41 && $bb >= 1500 && $bb < 2500 && !$isMati;
                
            case '1.2.2':
                return $uk >= 37 && $uk <= 41 && $bb >= 2500 && $bb < 4000 && !$isMati;
                
            case '1.2.3':
                return $uk >= 37 && $uk <= 41 && $bb >= 4000 && !$isMati;
                
            case '1.3': // Lahir Lebih dari 41 minggu
                return $uk > 41 && !$isMati;
                
            case '1.3.1':
                return $uk > 41 && $bb >= 1500 && $bb < 2500 && !$isMati;
                
            case '1.3.2':
                return $uk > 41 && $bb >= 2500 && $bb < 4000 && !$isMati;
                
            case '1.3.3':
                return $uk > 41 && $bb >= 4000 && !$isMati;
                
            case '2': // Lahir Mati
            case '2.1': // Antepartum
                return stripos($kondisiLahir, 'mati') !== false && 
                    stripos($kondisiLahir, 'antepartum') !== false;
                
            case '2.2': // Intrapartum
                return stripos($kondisiLahir, 'mati') !== false && 
                    stripos($kondisiLahir, 'intrapartum') !== false;
                
            case '3': // Kematian Neonatal
            case '3.1': // 0-7 hari
            case '3.2': // 8-28 hari
                if (!$data->tgl_meninggal) return false;
                $umurHari = Carbon::parse($data->tgl_lahir)->diffInDays(Carbon::parse($data->tgl_meninggal));
                if ($kategoriKode === '3.1') return $umurHari >= 0 && $umurHari <= 7;
                if ($kategoriKode === '3.2') return $umurHari >= 8 && $umurHari <= 28;
                return $umurHari >= 0 && $umurHari <= 28;
                
            case '4.1': // Asfiksia
                return stripos($kondisiLahir, 'asfiksia') !== false;
                
            case '4.2': // Trauma Kelahiran
                return stripos($data->keluhan_utama ?? '', 'trauma') !== false;
                
            case '4.3': // BBLR
                return $bb < 2500;
                
            case '5': // Metode Kanguru
                return $bb < 2500 && (
                    stripos($data->rencana ?? '', 'kanguru') !== false ||
                    stripos($data->rencana ?? '', 'kmc') !== false
                );
                
            case '6': // IMD
                return stripos($data->rencana ?? '', 'imd') !== false;
                
            default:
                return false;
        }
    }

    private function checkBayiMatchesKategori($data, $kategoriKode, $tanggalAwal)
    {
        $umurHari = Carbon::parse($tanggalAwal)->diffInDays(Carbon::parse($data->tgl_lahir));
        $umurBulan = Carbon::parse($tanggalAwal)->diffInMonths(Carbon::parse($data->tgl_lahir));
        
        switch ($kategoriKode) {
            case '8': // Bayi dan Anak Balita
                return $umurBulan >= 0 && $umurBulan <= 59;
                
            case '8.1': // Bayi Baru Lahir (0-28 hari)
                return $umurHari >= 0 && $umurHari <= 28;
                
            case '8.2': // Bayi (29 hari - 11 bulan)
                return $umurBulan >= 1 && $umurBulan <= 11;
                
            case '8.3': // Anak Balita (12-59 bulan)
                return $umurBulan >= 12 && $umurBulan <= 59;
                
            case '9': // Gizi Buruk
            case '9.1': // 0-5 bulan
            case '9.2': // 6-59 bulan
                if (!isset($data->total_nilai) || $data->total_nilai < 2) return false;
                if ($kategoriKode === '9.1') return $umurBulan >= 0 && $umurBulan <= 5;
                if ($kategoriKode === '9.2') return $umurBulan >= 6 && $umurBulan <= 59;
                return true;
                
            default:
                return false;
        }
    }

    private function getPerawatanPasien($noRawat)
    {
        $perawatanRanap = DB::table('rawat_inap_dr as ri')
            ->join('jns_perawatan_inap as jpi', 'ri.kd_jenis_prw', '=', 'jpi.kd_jenis_prw')
            ->where('ri.no_rawat', $noRawat)
            ->select('jpi.kd_jenis_prw', 'jpi.nm_perawatan')
            ->get();

        $perawatanRalan = DB::table('rawat_jl_dr as rj')
            ->join('jns_perawatan as jp', 'rj.kd_jenis_prw', '=', 'jp.kd_jenis_prw')
            ->where('rj.no_rawat', $noRawat)
            ->select('jp.kd_jenis_prw', 'jp.nm_perawatan')
            ->get();

        // Gabungkan dan convert ke array kode perawatan
        $allPerawatan = $perawatanRanap->concat($perawatanRalan);
        
        return [
            'kode' => $allPerawatan->pluck('kd_jenis_prw')->toArray(),
            'nama' => $allPerawatan->pluck('nm_perawatan')->map(function($nama) {
                return strtolower($nama);
            })->toArray()
        ];
    }

    private function getKategoriRL37()
    {
        $defaultData = ['rs' => 0, 'bidan' => 0, 'puskes' => 0, 'faskes' => 0, 'hidup_medis' => 0, 'mati_medis' => 0, 'total_medis' => 0, 'hidup_non_medis' => 0, 'mati_non_medis' => 0, 'total_non_medis' => 0, 'hidup_non_rujuk' => 0, 'mati_non_rujuk' => 0, 'total_non_rujuk' => 0, 'dirujuk' => 0];
        
        return [
            // A. NEONATAL
            'neonatal_header' => ['kode' => 'A', 'nama' => 'NEONATAL', 'is_header' => true, 'data' => $defaultData],
            '1' => ['kode' => '1', 'nama' => 'Bayi Lahir Hidup', 'is_header' => false, 'data' => $defaultData],
            '1.1' => ['kode' => '1.1', 'nama' => 'Lahir Prematur (< 37 minggu)', 'is_header' => false, 'data' => $defaultData],
            '1.1.1' => ['kode' => '1.1.1', 'nama' => '1500 - <2500 gram (BBLR)', 'is_header' => false, 'data' => $defaultData],
            '1.1.2' => ['kode' => '1.1.2', 'nama' => '1000 - <1500 gram (BBLSR)', 'is_header' => false, 'data' => $defaultData],
            '1.1.3' => ['kode' => '1.1.3', 'nama' => '<1000 gram (BBLER)', 'is_header' => false, 'data' => $defaultData],
            '1.2' => ['kode' => '1.2', 'nama' => 'Lahir Non Prematur (≥ 37 - 41 minggu)', 'is_header' => false, 'data' => $defaultData],
            '1.2.1' => ['kode' => '1.2.1', 'nama' => '1500 - <2500 gram (BBLR)', 'is_header' => false, 'data' => $defaultData],
            '1.2.2' => ['kode' => '1.2.2', 'nama' => '2500 - <4000 gram (BBLN)', 'is_header' => false, 'data' => $defaultData],
            '1.2.3' => ['kode' => '1.2.3', 'nama' => '≥4000 gram (BBLL)', 'is_header' => false, 'data' => $defaultData],
            '1.3' => ['kode' => '1.3', 'nama' => 'Lahir Lebih dari 41 minggu', 'is_header' => false, 'data' => $defaultData],
            '1.3.1' => ['kode' => '1.3.1', 'nama' => '1500 - <2500 gram (BBLR)', 'is_header' => false, 'data' => $defaultData],
            '1.3.2' => ['kode' => '1.3.2', 'nama' => '2500 - <4000 gram (BBLN)', 'is_header' => false, 'data' => $defaultData],
            '1.3.3' => ['kode' => '1.3.3', 'nama' => '≥4000 gram (BBLL)', 'is_header' => false, 'data' => $defaultData],
            '2' => ['kode' => '2', 'nama' => 'Lahir Mati', 'is_header' => false, 'data' => $defaultData],
            '2.1' => ['kode' => '2.1', 'nama' => 'Lahir Mati Antepartum', 'is_header' => false, 'data' => $defaultData],
            '2.2' => ['kode' => '2.2', 'nama' => 'Lahir Mati Intrapartum', 'is_header' => false, 'data' => $defaultData],
            '3' => ['kode' => '3', 'nama' => 'Kematian Neonatal dan Perinatal', 'is_header' => false, 'data' => $defaultData],
            '3.1' => ['kode' => '3.1', 'nama' => 'Kematian Neonatal Dini (0 - 7 hari)', 'is_header' => false, 'data' => $defaultData],
            '3.2' => ['kode' => '3.2', 'nama' => 'Kematian Neonatal Lanjut Perinatal (8 - 28 hari)', 'is_header' => false, 'data' => $defaultData],
            '4' => ['kode' => '4', 'nama' => 'Komplikasi Neonatal:', 'is_header' => false, 'data' => $defaultData],
            '4.1' => ['kode' => '4.1', 'nama' => 'Asfiksia', 'is_header' => false, 'data' => $defaultData],
            '4.2' => ['kode' => '4.2', 'nama' => 'Trauma Kelahiran', 'is_header' => false, 'data' => $defaultData],
            '4.3' => ['kode' => '4.3', 'nama' => 'BBLR', 'is_header' => false, 'data' => $defaultData],
            '4.4' => ['kode' => '4.4', 'nama' => 'Tetanus Neonatorum', 'is_header' => false, 'data' => $defaultData],
            '4.5' => ['kode' => '4.5', 'nama' => 'Kelainan Bawaan', 'is_header' => false, 'data' => $defaultData],
            '4.6' => ['kode' => '4.6', 'nama' => 'Covid-19', 'is_header' => false, 'data' => $defaultData],
            '4.7' => ['kode' => '4.7', 'nama' => 'Infeksi / Sepsis', 'is_header' => false, 'data' => $defaultData],
            '4.8' => ['kode' => '4.8', 'nama' => 'Komplikasi lainnya', 'is_header' => false, 'data' => $defaultData],
            '5' => ['kode' => '5', 'nama' => 'Bayi BBLR yang dilakukan perawatan metode kanguru', 'is_header' => false, 'data' => $defaultData],
            '6' => ['kode' => '6', 'nama' => 'Bayi baru lahir yang dilakukan IMD', 'is_header' => false, 'data' => $defaultData],
            '7' => ['kode' => '7', 'nama' => 'Bayi baru lahir yang dilakukan Skrining Hipertiroid Kongenital', 'is_header' => false, 'data' => $defaultData],
            
            // B. BAYI DAN BALITA
            'bayi_header' => ['kode' => 'B', 'nama' => 'BAYI DAN ANAK BALITA', 'is_header' => true, 'data' => $defaultData],
            '8' => ['kode' => '8', 'nama' => 'Bayi dan Anak Balita', 'is_header' => false, 'data' => $defaultData],
            '8.1' => ['kode' => '8.1', 'nama' => 'Bayi Baru Lahir (0 – 28 hari)', 'is_header' => false, 'data' => $defaultData],
            '8.2' => ['kode' => '8.2', 'nama' => 'Bayi (29 hari – 11 bulan)', 'is_header' => false, 'data' => $defaultData],
            '8.3' => ['kode' => '8.3', 'nama' => 'Anak Balita (12 - 59 bulan)', 'is_header' => false, 'data' => $defaultData],
            '9' => ['kode' => '9', 'nama' => 'Balita Gizi Buruk', 'is_header' => false, 'data' => $defaultData],
            '9.1' => ['kode' => '9.1', 'nama' => 'Balita Gizi Buruk usia 0-5 bulan', 'is_header' => false, 'data' => $defaultData],
            '9.2' => ['kode' => '9.2', 'nama' => 'Balita Gizi Buruk usia 6-59 bulan', 'is_header' => false, 'data' => $defaultData],
            '10' => ['kode' => '10', 'nama' => 'Balita menggunakan Buku KIA', 'is_header' => false, 'data' => $defaultData],
            '11' => ['kode' => '11', 'nama' => 'Balita dilakukan skrining pertumbuhan dan perkembangan', 'is_header' => false, 'data' => $defaultData],
            '11.1' => ['kode' => '11.1', 'nama' => 'Skrining Pertumbuhan sesuai umur', 'is_header' => false, 'data' => $defaultData],
            '11.2' => ['kode' => '11.2', 'nama' => 'Skrining perkembangan sesuai umur', 'is_header' => false, 'data' => $defaultData],
            '11.3' => ['kode' => '11.3', 'nama' => 'Skrining keterlambatan bicara dan bahasa', 'is_header' => false, 'data' => $defaultData],
            '11.4' => ['kode' => '11.4', 'nama' => 'Assessment kelainan motoric', 'is_header' => false, 'data' => $defaultData],
            '11.5' => ['kode' => '11.5', 'nama' => 'Skrining Kelainan Perilaku', 'is_header' => false, 'data' => $defaultData],
            '11.6' => ['kode' => '11.6', 'nama' => 'Skrining Gangguan Pendengaran', 'is_header' => false, 'data' => $defaultData],
            '10.7' => ['kode' => '10.7', 'nama' => 'Skrining Gangguan Penglihatan', 'is_header' => false, 'data' => $defaultData],
            '12' => ['kode' => '12', 'nama' => 'Bayi mendapatkan Imunisasi, Vitamin, dan Pengobatan Profilaksis:', 'is_header' => false, 'data' => $defaultData],
            '12.1' => ['kode' => '12.1', 'nama' => 'Hb 0', 'is_header' => false, 'data' => $defaultData],
            '12.2' => ['kode' => '12.2', 'nama' => 'BCG', 'is_header' => false, 'data' => $defaultData],
            '12.3' => ['kode' => '12.3', 'nama' => 'Polio 1,2,3', 'is_header' => false, 'data' => $defaultData],
            '12.4' => ['kode' => '12.4', 'nama' => 'DPT-HB-HIB 1, 2,3,4', 'is_header' => false, 'data' => $defaultData],
            '12.5' => ['kode' => '12.5', 'nama' => 'IPV', 'is_header' => false, 'data' => $defaultData],
            '12.6' => ['kode' => '12.6', 'nama' => 'Campak-Rubella', 'is_header' => false, 'data' => $defaultData],
            '12.7' => ['kode' => '12.7', 'nama' => 'Vitamin A 100.000 SI (1 kali dalam setahun)', 'is_header' => false, 'data' => $defaultData],
            '12.8' => ['kode' => '12.8', 'nama' => 'Pemberian Komunikasi, Informasi dan Edukasi (KIE)', 'is_header' => false, 'data' => $defaultData],
            '13' => ['kode' => '13', 'nama' => 'Bayi yang lahir dari Ibu HIV +', 'is_header' => false, 'data' => $defaultData],
            '13.1' => ['kode' => '13.1', 'nama' => 'Pemeriksaan Early Infant Diagnosis (EID)', 'is_header' => false, 'data' => $defaultData],
            '13.2' => ['kode' => '13.2', 'nama' => 'Pengobatan ARV bagi balita HIV+', 'is_header' => false, 'data' => $defaultData],
            '13.3' => ['kode' => '13.3', 'nama' => 'Pengobatan profilaksis kotrimoksazol', 'is_header' => false, 'data' => $defaultData],
            '14' => ['kode' => '14', 'nama' => 'Bayi yang lahir dari Ibu Sifilis +', 'is_header' => false, 'data' => $defaultData],
            '14.1' => ['kode' => '14.1', 'nama' => 'Pemeriksaan Titer RPR', 'is_header' => false, 'data' => $defaultData],
            '14.2' => ['kode' => '14.2', 'nama' => 'Pengobatan dosis tunggal Benzatin Penicilin G', 'is_header' => false, 'data' => $defaultData],
        ];
    }

    private function processNeonatalData($data, &$kategoriMap, $perawatan = [])
    {
        $bb = floatval($data->intranatal_bb ?? 0);
        
        // Normalisasi berat badan (jika < 100 berarti dalam kg, konversi ke gram)
        if ($bb > 0 && $bb < 100) {
            $bb = $bb * 1000;
        }
        
        $uk = $this->getUKFromString($data->prenatal_uk ?? '');
        $kondisiLahir = strtolower($data->intranatal_kondisi_lahir ?? '');
        
        // Cek rujukan dari tabel rujuk_masuk
        $rujukMasuk = DB::table('rujuk_masuk')
            ->where('no_rawat', $data->no_rawat)
            ->first();
        
        $isRujukan = false;
        $sumberRujukan = 'faskes';
        
        if ($rujukMasuk) {
            // Ada data rujuk_masuk, berarti pasien dirujuk
            $isRujukan = true;
            $sumberRujukan = $this->getSumberRujukanFromPerujuk($rujukMasuk->perujuk ?? '');
        } 
        //else {
        //    // Tidak ada rujuk_masuk, cek dari asal_pasien
        //    $asalPasien = strtoupper($data->asal_pasien ?? '');
        //    $isRujukan = !in_array($asalPasien, ['IGD', 'POLIKLINIK', 'KAMAR BERSALIN', 'KAMAR OPERASI']);
        //    
        //    if ($isRujukan) {
        //        // Jika rujukan tapi tidak ada di rujuk_masuk, cek dari diperoleh_dari
        //        $sumberRujukan = $this->getSumberRujukan($data->diperoleh_dari ?? '');
        //    }
        //}
        
        // 1. CEK LAHIR MATI dulu (prioritas tertinggi)
        // Lahir mati adalah kematian SEBELUM atau SAAT lahir (bukan rawat inap meninggal)
        if (stripos($kondisiLahir, 'mati') !== false || stripos($kondisiLahir, 'meninggal') !== false) {
            // 2.1 Lahir Mati Antepartum
            if (stripos($kondisiLahir, 'antepartum') !== false) {
                $this->incrementKategori($kategoriMap, '2.1', $isRujukan, $sumberRujukan, true);
                $this->incrementKategori($kategoriMap, '2', $isRujukan, $sumberRujukan, true);
            }
            // 2.2 Lahir Mati Intrapartum
            else if (stripos($kondisiLahir, 'intrapartum') !== false) {
                $this->incrementKategori($kategoriMap, '2.2', $isRujukan, $sumberRujukan, true);
                $this->incrementKategori($kategoriMap, '2', $isRujukan, $sumberRujukan, true);
            }
            return; // Jangan proses lebih lanjut jika lahir mati
        }
        
        // 2. BAYI LAHIR HIDUP - Tentukan kategori berdasarkan UK dan BB
        $kodeKategori = null;
        
        // Lahir Prematur (< 37 minggu)
        if ($uk < 37) {
            $this->incrementKategori($kategoriMap, '1.1', $isRujukan, $sumberRujukan);
            
            if ($bb >= 1500 && $bb < 2500) {
                $kodeKategori = '1.1.1';
            } elseif ($bb >= 1000 && $bb < 1500) {
                $kodeKategori = '1.1.2';
            } elseif ($bb < 1000) {
                $kodeKategori = '1.1.3';
            }
        }
        // Lahir Non Prematur (37-41 minggu)
        elseif ($uk >= 37 && $uk <= 41) {
            $this->incrementKategori($kategoriMap, '1.2', $isRujukan, $sumberRujukan);
            
            if ($bb >= 1500 && $bb < 2500) {
                $kodeKategori = '1.2.1';
            } elseif ($bb >= 2500 && $bb < 4000) {
                $kodeKategori = '1.2.2';
            } elseif ($bb >= 4000) {
                $kodeKategori = '1.2.3';
            }
        }
        // Lahir Lebih dari 41 minggu
        elseif ($uk > 41) {
            $this->incrementKategori($kategoriMap, '1.3', $isRujukan, $sumberRujukan);
            
            if ($bb >= 1500 && $bb < 2500) {
                $kodeKategori = '1.3.1';
            } elseif ($bb >= 2500 && $bb < 4000) {
                $kodeKategori = '1.3.2';
            } elseif ($bb >= 4000) {
                $kodeKategori = '1.3.3';
            }
        }
        
        // Increment kategori spesifik dan parent (1)
        if ($kodeKategori) {
            $this->incrementKategori($kategoriMap, $kodeKategori, $isRujukan, $sumberRujukan);
            $this->incrementKategori($kategoriMap, '1', $isRujukan, $sumberRujukan);
        }
        
        // 3. CEK KEMATIAN NEONATAL (bayi lahir hidup tapi meninggal dalam 0-28 hari)
        // Cek dari kamar_inap dengan stts_pulang = 'Meninggal'
        $kamarInapMeninggal = DB::table('kamar_inap')
            ->where('no_rawat', $data->no_rawat)
            ->where('stts_pulang', 'Meninggal')
            ->first();
        
        if ($kamarInapMeninggal) {
            $tglLahir = $data->tanggal ?? null;
            $tglMeninggal = $kamarInapMeninggal->tgl_keluar ?? null;
            
            if ($tglLahir && $tglMeninggal) {
                $umurHari = Carbon::parse($tglLahir)->diffInDays(Carbon::parse($tglMeninggal));
                
                // 3.1 Kematian Neonatal Dini (0-7 hari)
                if ($umurHari >= 0 && $umurHari <= 7) {
                    $this->incrementKategori($kategoriMap, '3.1', $isRujukan, $sumberRujukan, true);
                    $this->incrementKategori($kategoriMap, '3', $isRujukan, $sumberRujukan, true);
                }
                // 3.2 Kematian Neonatal Lanjut (8-28 hari)
                elseif ($umurHari >= 8 && $umurHari <= 28) {
                    $this->incrementKategori($kategoriMap, '3.2', $isRujukan, $sumberRujukan, true);
                    $this->incrementKategori($kategoriMap, '3', $isRujukan, $sumberRujukan, true);
                }
            }
        }
        
        // 4. KOMPLIKASI NEONATAL
        $this->processKomplikasiNeonatal($data, $kategoriMap, $isRujukan, $sumberRujukan);
        
        // 5. Bayi BBLR yang dilakukan perawatan metode kanguru
        // Cek dari rencana atau catatan perawatan
        if ($bb < 2500 && (
            stripos($data->rencana ?? '', 'kanguru') !== false ||
            stripos($data->rencana ?? '', 'kmc') !== false
        )) {
            $this->incrementKategori($kategoriMap, '5', $isRujukan, $sumberRujukan);
        }
        
        // 6. Bayi baru lahir yang dilakukan IMD
        // Cek dari field yang relevan di neonatus
        if (stripos($data->rencana ?? '', 'imd') !== false ||
            stripos($data->rencana ?? '', 'inisiasi menyusu dini') !== false) {
            $this->incrementKategori($kategoriMap, '6', $isRujukan, $sumberRujukan);
        }
        
        // 7. SKRINING HIPERTIROID KONGENITAL - ❌ TIDAK ADA di jns_perawatan
        // TODO: Tambahkan kode perawatan Skrining Hipertiroid Kongenital ke master jns_perawatan/jns_perawatan_inap
        // Sementara cek dari field rencana saja
        // if ($this->hasPerawatan($perawatan, ['SKRINING HIPERTIROID', 'SHK'])) {
        //     $this->incrementKategori($kategoriMap, '7', $isRujukan, $sumberRujukan);
        // } else 
        if (stripos($data->rencana ?? '', 'skrining') !== false &&
            (stripos($data->rencana ?? '', 'hipertiroid') !== false ||
            stripos($data->rencana ?? '', 'kongenital') !== false)) {
            $this->incrementKategori($kategoriMap, '7', $isRujukan, $sumberRujukan);
        }
    }

    private function hasPerawatan($perawatan, $keywords)
    {
        if (empty($perawatan)) return false;
        
        $kode = $perawatan['kode'] ?? [];
        $nama = $perawatan['nama'] ?? [];
        
        foreach ($keywords as $keyword) {
            // Cek di kode
            if (in_array($keyword, $kode)) return true;
            
            // Cek di nama (lowercase)
            foreach ($nama as $namaTindakan) {
                if (stripos($namaTindakan, strtolower($keyword)) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    private function processBayiData($data, &$kategoriMap, $tanggalAwal, $perawatan = [])
    {
        $tglLahir = $data->tgl_lahir ?? null;
        if (!$tglLahir) return;
        
        $umurHari = Carbon::parse($tanggalAwal)->diffInDays(Carbon::parse($tglLahir));
        $umurBulan = Carbon::parse($tanggalAwal)->diffInMonths(Carbon::parse($tglLahir));
        
        // Cek rujukan dari tabel rujuk_masuk
        $rujukMasuk = DB::table('rujuk_masuk')
            ->where('no_rawat', $data->no_rawat)
            ->first();
        
        $isRujukan = false;
        $sumberRujukan = 'faskes';
        
        if ($rujukMasuk) {
            // Ada data rujuk_masuk, berarti pasien dirujuk
            $isRujukan = true;
            $sumberRujukan = $this->getSumberRujukanFromPerujuk($rujukMasuk->perujuk ?? '');
        } 
        //else {
        //    // Tidak ada rujuk_masuk, cek dari tiba_diruang_rawat
        //    $tibaDiruang = strtoupper($data->tiba_diruang_rawat ?? '');
        //    $isRujukan = !in_array($tibaDiruang, ['JALAN TANPA BANTUAN', 'KURSI RODA', 'BRANKAR']);
        //    
        //    if ($isRujukan) {
        //        // Jika rujukan tapi tidak ada di rujuk_masuk, cek dari diperoleh_dari
        //        $sumberRujukan = $this->getSumberRujukan($data->diperoleh_dari ?? '');
        //    }
        //}
        
        // 8. Bayi dan Anak Balita
        $kodeKategori = null;
        if ($umurHari >= 0 && $umurHari <= 28) {
            $kodeKategori = '8.1';
        } elseif ($umurBulan >= 1 && $umurBulan <= 11) {
            $kodeKategori = '8.2';
        } elseif ($umurBulan >= 12 && $umurBulan <= 59) {
            $kodeKategori = '8.3';
        }
        
        if ($kodeKategori) {
            $this->incrementKategori($kategoriMap, $kodeKategori, $isRujukan, $sumberRujukan);
            $this->incrementKategori($kategoriMap, '8', $isRujukan, $sumberRujukan);
        }
        
        // 9. Gizi Buruk
        if (isset($data->total_nilai) && $data->total_nilai >= 2) {
            if ($umurBulan >= 0 && $umurBulan <= 5) {
                $this->incrementKategori($kategoriMap, '9.1', $isRujukan, $sumberRujukan);
            } elseif ($umurBulan >= 6 && $umurBulan <= 59) {
                $this->incrementKategori($kategoriMap, '9.2', $isRujukan, $sumberRujukan);
            }
            $this->incrementKategori($kategoriMap, '9', $isRujukan, $sumberRujukan);
        }
        
        // 10. Buku KIA
        if (stripos($data->psiko_edukasi_keterangan ?? '', 'kia') !== false ||
            stripos($data->rencana ?? '', 'buku kia') !== false) {
            $this->incrementKategori($kategoriMap, '10', $isRujukan, $sumberRujukan);
        }
        
        // 11. Skrining pertumbuhan dan perkembangan
        if (isset($data->penilaian_humptydumpty_totalnilai) && $data->penilaian_humptydumpty_totalnilai > 0) {
            $this->incrementKategori($kategoriMap, '11', $isRujukan, $sumberRujukan);
            $this->incrementKategori($kategoriMap, '11.1', $isRujukan, $sumberRujukan);
            // 11.2 - 11.7 bisa dikembangkan jika ada field spesifik
        }
        
        // ========================================================================
        // 12. Bayi mendapatkan Imunisasi, Vitamin, dan Pengobatan Profilaksis
        // ========================================================================
        $hasImunisasi = false;
        
        // 12.1 HB 0 - ✅ ADA: NICU.008, MED-457
        if ($this->hasPerawatan($perawatan, ['NICU.008', 'MED-457', 'hepatitis b', 'hb 0', 'vitamin k'])) {
            $this->incrementKategori($kategoriMap, '12.1', $isRujukan, $sumberRujukan);
            $hasImunisasi = true;
        }
        
        // 12.2 BCG - ❌ TIDAK ADA di jns_perawatan
        // TODO: Tambahkan kode perawatan BCG ke master jns_perawatan/jns_perawatan_inap
        // if ($this->hasPerawatan($perawatan, ['BCG'])) {
        //     $this->incrementKategori($kategoriMap, '12.2', $isRujukan, $sumberRujukan);
        //     $hasImunisasi = true;
        // }
        
        // 12.3 Polio 1,2,3 - ❌ TIDAK ADA di jns_perawatan
        // TODO: Tambahkan kode perawatan Polio ke master jns_perawatan/jns_perawatan_inap
        // if ($this->hasPerawatan($perawatan, ['POLIO'])) {
        //     $this->incrementKategori($kategoriMap, '12.3', $isRujukan, $sumberRujukan);
        //     $hasImunisasi = true;
        // }
        
        // 12.4 DPT-HB-HIB 1,2,3,4 - ❌ TIDAK ADA di jns_perawatan
        // TODO: Tambahkan kode perawatan DPT-HB-HIB ke master jns_perawatan/jns_perawatan_inap
        // if ($this->hasPerawatan($perawatan, ['DPT', 'HIB'])) {
        //     $this->incrementKategori($kategoriMap, '12.4', $isRujukan, $sumberRujukan);
        //     $hasImunisasi = true;
        // }
        
        // 12.5 IPV - ❌ TIDAK ADA di jns_perawatan
        // TODO: Tambahkan kode perawatan IPV ke master jns_perawatan/jns_perawatan_inap
        // if ($this->hasPerawatan($perawatan, ['IPV'])) {
        //     $this->incrementKategori($kategoriMap, '12.5', $isRujukan, $sumberRujukan);
        //     $hasImunisasi = true;
        // }
        
        // 12.6 Campak-Rubella - ❌ TIDAK ADA di jns_perawatan
        // TODO: Tambahkan kode perawatan Campak-Rubella ke master jns_perawatan/jns_perawatan_inap
        // if ($this->hasPerawatan($perawatan, ['CAMPAK', 'RUBELLA', 'MR'])) {
        //     $this->incrementKategori($kategoriMap, '12.6', $isRujukan, $sumberRujukan);
        //     $hasImunisasi = true;
        // }
        
        // 12.7 Vitamin A 100.000 SI - ❌ TIDAK ADA di jns_perawatan
        // TODO: Tambahkan kode perawatan Vitamin A ke master jns_perawatan/jns_perawatan_inap
        // if ($this->hasPerawatan($perawatan, ['VITAMIN A'])) {
        //     $this->incrementKategori($kategoriMap, '12.7', $isRujukan, $sumberRujukan);
        //     $hasImunisasi = true;
        // }
        
        // 12.8 KIE - ✅ ADA: KEP-08 (Manajemen laktasi)
        if ($this->hasPerawatan($perawatan, ['KEP-08', 'manajemen laktasi', 'kie', 'edukasi'])) {
            $this->incrementKategori($kategoriMap, '12.8', $isRujukan, $sumberRujukan);
            $hasImunisasi = true;
        }
        
        // Cek imunisasi generik - ✅ ADA: MED-259, MED-261
        if ($this->hasPerawatan($perawatan, ['MED-259', 'MED-261', 'imunisasi', 'vaksin'])) {
            $hasImunisasi = true;
        }
        
        if ($hasImunisasi) {
            $this->incrementKategori($kategoriMap, '12', $isRujukan, $sumberRujukan);
        }
        
        // ========================================================================
        // 13. Bayi yang lahir dari Ibu HIV+
        // ========================================================================
        // Cek dari prenatal_riwayat_penyakit_ibu (NEONATUS) atau dari anamnesis
        $isFromHIVMother = false;
        
        // Jika data dari bayi, cek apakah ada catatan ibu HIV
        if (stripos($data->rps ?? '', 'hiv') !== false ||
            stripos($data->rpk ?? '', 'hiv') !== false) {
            $isFromHIVMother = true;
        }
        
        if ($isFromHIVMother) {
            $this->incrementKategori($kategoriMap, '13', $isRujukan, $sumberRujukan);
            
            // 13.1 EID (Early Infant Diagnosis) - ❌ TIDAK ADA di jns_perawatan
            // TODO: Tambahkan kode perawatan EID ke master jns_perawatan/jns_perawatan_inap
            // if ($this->hasPerawatan($perawatan, ['EID'])) {
            //     $this->incrementKategori($kategoriMap, '13.1', $isRujukan, $sumberRujukan);
            // }
            
            // 13.2 Pengobatan ARV bagi balita HIV+ - ❌ TIDAK ADA di jns_perawatan
            // TODO: Tambahkan kode perawatan ARV ke master jns_perawatan/jns_perawatan_inap
            // if ($this->hasPerawatan($perawatan, ['ARV'])) {
            //     $this->incrementKategori($kategoriMap, '13.2', $isRujukan, $sumberRujukan);
            // }
            
            // 13.3 Pengobatan profilaksis kotrimoksazol - ❌ TIDAK ADA di jns_perawatan
            // TODO: Tambahkan kode perawatan Kotrimoksazol ke master jns_perawatan/jns_perawatan_inap
            // if ($this->hasPerawatan($perawatan, ['KOTRIMOKSAZOL', 'BACTRIM'])) {
            //     $this->incrementKategori($kategoriMap, '13.3', $isRujukan, $sumberRujukan);
            // }
        }
        
        // ========================================================================
        // 14. Bayi yang lahir dari Ibu Sifilis+
        // ========================================================================
        $isFromSifilisMother = false;
        
        if (stripos($data->rps ?? '', 'sifilis') !== false ||
            stripos($data->rpk ?? '', 'sifilis') !== false) {
            $isFromSifilisMother = true;
        }
        
        if ($isFromSifilisMother) {
            $this->incrementKategori($kategoriMap, '14', $isRujukan, $sumberRujukan);
            
            // 14.1 Pemeriksaan Titer RPR - ❌ TIDAK ADA di jns_perawatan
            // TODO: Tambahkan kode perawatan Titer RPR ke master jns_perawatan/jns_perawatan_inap
            // if ($this->hasPerawatan($perawatan, ['RPR', 'TITER RPR'])) {
            //     $this->incrementKategori($kategoriMap, '14.1', $isRujukan, $sumberRujukan);
            // }
            
            // 14.2 Pengobatan dosis tunggal Benzatin Penicilin G - ❌ TIDAK ADA di jns_perawatan
            // TODO: Tambahkan kode perawatan Benzatin Penicilin G ke master jns_perawatan/jns_perawatan_inap
            // if ($this->hasPerawatan($perawatan, ['BENZATIN', 'PENICILIN G'])) {
            //     $this->incrementKategori($kategoriMap, '14.2', $isRujukan, $sumberRujukan);
            // }
        }
    }

    private function getSumberRujukanFromPerujuk($perujuk)
    {
        $source = strtolower($perujuk);
        
        if (strpos($source, 'rs') !== false || strpos($source, 'rumah sakit') !== false) {
            return 'rs';
        } elseif (strpos($source, 'bidan') !== false) {
            return 'bidan';
        } elseif (strpos($source, 'puskesmas') !== false || strpos($source, 'puskes') !== false) {
            return 'puskes';
        } else {
            return 'faskes';
        }
    }

    private function processKomplikasiNeonatal($data, &$kategoriMap, $isRujukan, $sumberRujukan)
    {
        $bb = floatval($data->intranatal_bb ?? 0);
        if ($bb > 0 && $bb < 100) $bb = $bb * 1000;
        
        $kondisiLahir = strtolower($data->intranatal_kondisi_lahir ?? '');
        $komplikasiFound = false;
        
        // 4.1 Asfiksia
        if (stripos($kondisiLahir, 'asfiksia') !== false || 
            (isset($data->intranatal_apgar) && intval(explode('/', $data->intranatal_apgar)[0]) < 7)) {
            $this->incrementKategori($kategoriMap, '4.1', $isRujukan, $sumberRujukan);
            $komplikasiFound = true;
        }
        
        // 4.2 Trauma Kelahiran
        if (stripos($data->keluhan_utama ?? '', 'trauma') !== false ||
            stripos($kondisiLahir, 'trauma') !== false ||
            $data->saraf_pusat_kepala == 'Hematoma') {
            $this->incrementKategori($kategoriMap, '4.2', $isRujukan, $sumberRujukan);
            $komplikasiFound = true;
        }
        
        // 4.3 BBLR
        if ($bb > 0 && $bb < 2500) {
            $this->incrementKategori($kategoriMap, '4.3', $isRujukan, $sumberRujukan);
            $komplikasiFound = true;
        }
        
        // 4.4 Tetanus Neonatorum
        if (stripos($data->keluhan_utama ?? '', 'tetanus') !== false ||
            stripos($data->prenatal_riwayat_penyakit_ibu_keterangan ?? '', 'tetanus') !== false) {
            $this->incrementKategori($kategoriMap, '4.4', $isRujukan, $sumberRujukan);
            $komplikasiFound = true;
        }
        
        // 4.5 Kelainan Bawaan
        if ((isset($data->persalinan_kelainan_bawaan) && $data->persalinan_kelainan_bawaan == 'Ada') ||
            stripos($data->keluhan_utama ?? '', 'kelainan bawaan') !== false ||
            $data->reproduksi != 'Normal') {
            $this->incrementKategori($kategoriMap, '4.5', $isRujukan, $sumberRujukan);
            $komplikasiFound = true;
        }
        
        // 4.6 COVID-19
        if (stripos($data->prenatal_riwayat_penyakit_ibu_keterangan ?? '', 'covid') !== false ||
            stripos($data->keluhan_utama ?? '', 'covid') !== false) {
            $this->incrementKategori($kategoriMap, '4.6', $isRujukan, $sumberRujukan);
            $komplikasiFound = true;
        }
        
        // 4.7 Infeksi / Sepsis
        if (stripos($data->keluhan_utama ?? '', 'sepsis') !== false ||
            stripos($data->keluhan_utama ?? '', 'infeksi') !== false ||
            stripos($kondisiLahir, 'sepsis') !== false) {
            $this->incrementKategori($kategoriMap, '4.7', $isRujukan, $sumberRujukan);
            $komplikasiFound = true;
        }
        
        // 4.8 Komplikasi lainnya (jika ada keluhan tapi bukan kategori di atas)
        $keluhanUtama = strtolower($data->keluhan_utama ?? '');
        if (!empty($keluhanUtama) && 
            !$komplikasiFound &&
            (stripos($keluhanUtama, 'sesak') !== false ||
            stripos($keluhanUtama, 'merintih') !== false ||
            stripos($keluhanUtama, 'kuning') !== false ||
            stripos($keluhanUtama, 'kejang') !== false)) {
            $this->incrementKategori($kategoriMap, '4.8', $isRujukan, $sumberRujukan);
            $komplikasiFound = true;
        }
        
        // Increment parent kategori 4 jika ada komplikasi
        if ($komplikasiFound) {
            $this->incrementKategori($kategoriMap, '4', $isRujukan, $sumberRujukan);
        }
    }

    private function incrementKategori(&$kategoriMap, $kode, $isRujukan, $sumberRujukan, $isMati = false)
    {
        if (isset($kategoriMap[$kode])) {
            if ($isRujukan) {
                $kategoriMap[$kode]['data'][$sumberRujukan]++;
                
                if ($isMati) {
                    $kategoriMap[$kode]['data']['mati_medis']++;
                } else {
                    $kategoriMap[$kode]['data']['hidup_medis']++;
                }
                $kategoriMap[$kode]['data']['total_medis']++;
            } else {
                if ($isMati) {
                    $kategoriMap[$kode]['data']['mati_non_rujuk']++;
                } else {
                    $kategoriMap[$kode]['data']['hidup_non_rujuk']++;
                }
                $kategoriMap[$kode]['data']['total_non_rujuk']++;
            }
        }
    }

    private function getUKFromString($ukString)
    {
        if (preg_match('/(\d+)/', $ukString, $matches)) {
            return intval($matches[1]);
        }
        return 0;
    }

    private function getSumberRujukan($diperolehDari)
    {
        $source = strtolower($diperolehDari);
        
        if (strpos($source, 'rs') !== false || strpos($source, 'rumah sakit') !== false) {
            return 'rs';
        } elseif (strpos($source, 'bidan') !== false) {
            return 'bidan';
        } elseif (strpos($source, 'puskesmas') !== false || strpos($source, 'puskes') !== false) {
            return 'puskes';
        } else {
            return 'faskes';
        }
    }

    private function generateRL37PDF($tanggalAwal, $tanggalAkhir, $kategori, $hospitalInfo)
    {
        $pdf = PDF::loadView('rm.laporan_rm.rl37_pdf', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'kategori' => $kategori,
            'hospitalInfo' => $hospitalInfo
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('disable-smart-shrinking', true);
        
        $filename = 'RL37_Neonatal_Bayi_Balita_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir)) . '.pdf';
        
        return $pdf->download($filename);
    }

    // Akhir Laporan RL 3.7
    ///////////////////////////////////////////////////////////////
       
}
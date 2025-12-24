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

        // Ubah semua nilai 0 dalam array 'data' menjadi null
        $kategori = array_map(function($item) {
            if (isset($item['data']) && is_array($item['data'])) {
                $item['data'] = array_map(function($value) {
                    return $value === 0 ? null : $value;
                }, $item['data']);
            }
            return $item;
        }, $kategori);
        //throw new \Exception(json_encode($kategori));
        $hospitalInfo = DB::table('setting')->first();

        if ($request->has('download_pdf')) {
            return $this->generateRL37PDF($tanggalAwal, $tanggalAkhir, $kategori, $hospitalInfo);
        }

        if ($request->has('download_excel')) {
            return $this->generateRL37Excel($tanggalAwal, $tanggalAkhir, $kategori, $hospitalInfo);
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
                    'pan.intranatal_apgar',
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
                    $q->whereNotNull('rm.no_rawat');
                    //->orWhereNotIn('pan.asal_pasien', ['IGD', 'POLIKLINIK', 'KAMAR BERSALIN', 'KAMAR OPERASI']);
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
                        // Faskes = semua rujukan KECUALI RS, Bidan, Puskesmas
                        $query->where(function($q) {
                            $q->whereNotNull('rm.perujuk')
                            ->where('rm.perujuk', 'NOT LIKE', '%rs%')
                            ->where('rm.perujuk', 'NOT LIKE', '%rumah sakit%')
                            ->where('rm.perujuk', 'NOT LIKE', '%bidan%')
                            ->where('rm.perujuk', 'NOT LIKE', '%puskesmas%')
                            ->where('rm.perujuk', 'NOT LIKE', '%puskes%');
                        });
                    }
                }
            } else {
                // Non rujukan - tidak ada rujuk_masuk dan asal_pasien internal
                $query->whereNull('rm.no_rawat');
                    //->whereIn('pan.asal_pasien', ['IGD', 'POLIKLINIK', 'KAMAR BERSALIN', 'KAMAR OPERASI']);
            }
            
            $rawData = $query->get();
            
            //throw new \Exception(json_encode($rawData));

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

    private function getApgarMenitPertama($apgar)
    {
        if (empty($apgar)) return null;

        // Ambil angka pertama sebelum pemisah apapun
        if (preg_match('/(\d+)/', $apgar, $match)) {
            return intval($match[1]);
        }

        return null;
    }


    private function checkNeonatalMatchesKategori($data, $kategoriKode, $statusType)
    {
        $bb = floatval($data->intranatal_bb ?? 0);
        if ($bb > 0 && $bb < 100) $bb = $bb * 1000;
        
        $uk = $this->getUKFromString($data->prenatal_uk ?? '');
        $kondisiLahir = strtolower($data->intranatal_kondisi_lahir ?? '');
        
        // ===================================================================
        // PENTING: Pisahkan konsep "Lahir Mati" vs "Lahir Hidup tapi Meninggal"
        // ===================================================================
        
        // 1. LAHIR MATI = Bayi sudah mati SAAT/SEBELUM persalinan
        $isLahirMati = false;
        if (stripos($kondisiLahir, 'mati') !== false || 
            stripos($kondisiLahir, 'meninggal') !== false) {
            $isLahirMati = true;
        }
        
        // 2. KEMATIAN NEONATAL = Bayi lahir hidup, tapi meninggal dalam 0-28 hari
        $isKematianNeonatal = false;
        if (!$isLahirMati && $data->tgl_meninggal !== null) {
            // ⚠️ PERBAIKAN: Cek juga apakah tgl_meninggal valid (bukan 0000-00-00 atau string kosong)
            $tglMeninggal = trim($data->tgl_meninggal);
            if (!empty($tglMeninggal) && 
                $tglMeninggal !== '0000-00-00' && 
                $tglMeninggal !== '0000-00-00 00:00:00') {
                $isKematianNeonatal = true;
            }
        }
        
        // ===================================================================
        // Filter berdasarkan statusType yang diminta
        // ===================================================================
        
        // Untuk kategori yang memerlukan filter hidup/mati
        if ($statusType === 'hidup') {
            //if ($isLahirMati || $isKematianNeonatal) return false;
            if ($isLahirMati) return false;
        } 
        elseif ($statusType === 'mati') {
            // Untuk kategori 1 (Bayi Lahir Hidup), "mati" berarti lahir hidup tapi meninggal
            if (in_array($kategoriKode, ['1', '1.1', '1.1.1', '1.1.2', '1.1.3', '1.2', '1.2.1', '1.2.2', '1.2.3', '1.3', '1.3.1', '1.3.2', '1.3.3'])) {
                if (!$isKematianNeonatal) return false; // Harus lahir hidup tapi meninggal
            }
            // Untuk kategori 2 (Lahir Mati), "mati" berarti lahir dalam kondisi mati
            elseif (in_array($kategoriKode, ['2', '2.1', '2.2'])) {
                if (!$isLahirMati) return false;
            }
            // Untuk kategori 3 (Kematian Neonatal), sama dengan kategori 1
            elseif (in_array($kategoriKode, ['3', '3.1', '3.2'])) {
                if (!$isKematianNeonatal) return false;
            }
        }
        //throw new \Exception(1);
        // Check kategori spesifik
        switch ($kategoriKode) {
            case '1': // Bayi Lahir Hidup
                return !$isLahirMati;
                
            case '1.1': // Lahir Prematur
                return $uk < 37 && !$isLahirMati && $bb > 0;
                
            case '1.1.1':
                return $uk < 37 && $bb >= 1500 && $bb < 2500 && !$isLahirMati;
                
            case '1.1.2':
                return $uk < 37 && $bb >= 1000 && $bb < 1500 && !$isLahirMati;
                
            case '1.1.3':
                return $uk < 37 && $bb < 1000 && $bb > 0 && !$isLahirMati;
                
            case '1.2': // Lahir Non Prematur
                return $uk >= 37 && $uk <= 41 && !$isLahirMati && $bb > 0;
                
            case '1.2.1':
                return $uk >= 37 && $uk <= 41 && $bb >= 1500 && $bb < 2500 && !$isLahirMati;
                
            case '1.2.2':
                return $uk >= 37 && $uk <= 41 && $bb >= 2500 && $bb < 4000 && !$isLahirMati;
                
            case '1.2.3':
                return $uk >= 37 && $uk <= 41 && $bb >= 4000 && !$isLahirMati;
                
            case '1.3': // Lahir Lebih dari 41 minggu
                return $uk > 41 && !$isLahirMati;
                
            case '1.3.1':
                return $uk > 41 && $bb >= 1500 && $bb < 2500 && !$isLahirMati;
                
            case '1.3.2':
                return $uk > 41 && $bb >= 2500 && $bb < 4000 && !$isLahirMati;
                
            case '1.3.3':
                return $uk > 41 && $bb >= 4000 && !$isLahirMati;
                
            case '2': // Lahir Mati (semua)
                return stripos($kondisiLahir, 'mati') !== false;
                
            case '2.1': // Antepartum
                return stripos($kondisiLahir, 'mati') !== false && 
                    stripos($kondisiLahir, 'antepartum') !== false;
                
            case '2.2': // Intrapartum
                return stripos($kondisiLahir, 'mati') !== false && 
                    stripos($kondisiLahir, 'intrapartum') !== false;
                
            case '3': // Kematian Neonatal (semua)
                if (!$data->tgl_meninggal || stripos($kondisiLahir, 'mati') !== false) return false;
                $umurHari = Carbon::parse($data->tgl_lahir)->diffInDays(Carbon::parse($data->tgl_meninggal));
                return $umurHari >= 0 && $umurHari <= 28;
                
            case '3.1': // 0-7 hari
                if (!$data->tgl_meninggal || stripos($kondisiLahir, 'mati') !== false) return false;
                $umurHari = Carbon::parse($data->tgl_lahir)->diffInDays(Carbon::parse($data->tgl_meninggal));
                return $umurHari >= 0 && $umurHari <= 7;
                
            case '3.2': // 8-28 hari
                if (!$data->tgl_meninggal || stripos($kondisiLahir, 'mati') !== false) return false;
                $umurHari = Carbon::parse($data->tgl_lahir)->diffInDays(Carbon::parse($data->tgl_meninggal));
                return $umurHari >= 8 && $umurHari <= 28;
                
            case '4': // Komplikasi (any)
            case '4.1': // Asfiksia
                $apgar1 = $this->getApgarMenitPertama($data->intranatal_apgar ?? '');

                return
                    stripos($kondisiLahir, 'asfiksia') !== false ||
                    ($apgar1 !== null && $apgar1 < 7);

                
            case '4.2': // Trauma Kelahiran
                return stripos($data->keluhan_utama ?? '', 'trauma') !== false ||
                    stripos($kondisiLahir, 'trauma') !== false;
                
            case '4.3': // BBLR
                return $bb > 0 && $bb < 2500;
                
            case '4.4': // Tetanus
                return stripos($data->keluhan_utama ?? '', 'tetanus') !== false;
                
            case '4.5': // Kelainan Bawaan
                return stripos($data->keluhan_utama ?? '', 'kelainan bawaan') !== false ||
                    (isset($data->persalinan_kelainan_bawaan) && $data->persalinan_kelainan_bawaan == 'Ada');
                
            case '4.6': // COVID-19
                return stripos($data->keluhan_utama ?? '', 'covid') !== false;
                
            case '4.7': // Sepsis/Infeksi
                return stripos($data->keluhan_utama ?? '', 'sepsis') !== false ||
                    stripos($data->keluhan_utama ?? '', 'infeksi') !== false;
                
            case '4.8': // Komplikasi lain
                $keluhan = strtolower($data->keluhan_utama ?? '');
                return !empty($keluhan) && (
                    stripos($keluhan, 'sesak') !== false ||
                    stripos($keluhan, 'merintih') !== false ||
                    stripos($keluhan, 'kuning') !== false ||
                    stripos($keluhan, 'kejang') !== false
                );
                
            case '5': // Metode Kanguru
                return $bb < 2500 && $bb > 0 && (
                    stripos($data->rencana ?? '', 'kanguru') !== false ||
                    stripos($data->rencana ?? '', 'kmc') !== false
                );
                
            case '6': // IMD
                return stripos($data->rencana ?? '', 'imd') !== false ||
                    stripos($data->rencana ?? '', 'inisiasi menyusu dini') !== false;
                
            case '7': // Skrining Hipertiroid
                return stripos($data->rencana ?? '', 'skrining') !== false &&
                    (stripos($data->rencana ?? '', 'hipertiroid') !== false ||
                        stripos($data->rencana ?? '', 'kongenital') !== false);
                
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

            case '11': // Skrining pertumbuhan dan perkembangan
            case '11.1': // Skrining Pertumbuhan sesuai umur
            case '11.2':
            case '11.3':
            case '11.4':
            case '11.5':
            case '11.6':
            case '10.7':
                // ✅ FIX: Cek jika ada data penilaian_humptydumpty ATAU field terkait
                return (isset($data->penilaian_humptydumpty_totalnilai) && $data->penilaian_humptydumpty_totalnilai > 0) ||
                    (isset($data->hasil_skrining_penilaian_humptydumpty) && !empty($data->hasil_skrining_penilaian_humptydumpty));
                
            case '15': // Bayi dari Ibu Hepatitis+
            case '15.1':
            case '15.2':
            case '15.3':
                return stripos($data->rps ?? '', 'hepatitis') !== false ||
                    stripos($data->rpk ?? '', 'hepatitis') !== false;
                
            case '16': // Anak Balita Imunisasi
            case '16.1':
            case '16.2':
            case '16.3':
            case '16.4':
            case '16.5':
            case '16.6':
                return $umurBulan >= 12 && $umurBulan <= 59;
                
            case '17': // Balita Gizi Buruk mendapat perawatan
                return isset($data->total_nilai) && $data->total_nilai >= 2;
                
            case '17.1':
                return isset($data->total_nilai) && $data->total_nilai >= 2 &&
                    $umurBulan >= 0 && $umurBulan <= 5 && 
                    $data->status_lanjut == 'Ranap';
                
            case '17.2':
                return isset($data->total_nilai) && $data->total_nilai >= 2 &&
                    $umurBulan >= 6 && $umurBulan <= 59 && 
                    $data->status_lanjut == 'Ranap';
                
            case '17.3':
                return isset($data->total_nilai) && $data->total_nilai >= 2 &&
                    $umurBulan >= 6 && $umurBulan <= 59 && 
                    $data->status_lanjut == 'Ralan';
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

            // 15. Bayi yang lahir dari Ibu Hepatitis +
            '15' => ['kode' => '15', 'nama' => 'Bayi yang lahir dari Ibu Hepatitis +', 'is_header' => false, 'data' => $defaultData],
            '15.1' => ['kode' => '15.1', 'nama' => 'Pemeriksaan serologis HBs Ag', 'is_header' => false, 'data' => $defaultData],
            '15.2' => ['kode' => '15.2', 'nama' => 'Pemberian Hb 0', 'is_header' => false, 'data' => $defaultData],
            '15.3' => ['kode' => '15.3', 'nama' => 'Pemberian Hb Ig', 'is_header' => false, 'data' => $defaultData],

            // 16. Anak Balita (12-59 bulan) mendapatkan Imunisasi, Vitamin, dan Pengobatan profilaksis
            '16' => ['kode' => '16', 'nama' => 'Anak Balita (12-59 bulan) mendapatkan Imunisasi, Vitamin, dan Pengobatan profilaksis:', 'is_header' => false, 'data' => $defaultData],
            '16.1' => ['kode' => '16.1', 'nama' => 'Campak-Rubela', 'is_header' => false, 'data' => $defaultData],
            '16.2' => ['kode' => '16.2', 'nama' => 'Vitamin A 200.000 SI (2kali dalam setahun)', 'is_header' => false, 'data' => $defaultData],
            '16.3' => ['kode' => '16.3', 'nama' => 'Anak balita mendapat obat pencegahan kecacingan 1 kali setahun', 'is_header' => false, 'data' => $defaultData],
            '16.4' => ['kode' => '16.4', 'nama' => 'Balita (0-59 bulan) terduga TBC/ kontak erat mendapat TPT (Terapi Pencegahan TBC)', 'is_header' => false, 'data' => $defaultData],
            '16.5' => ['kode' => '16.5', 'nama' => 'Balita (0-59 bulan) TBC mendapatkan OAT', 'is_header' => false, 'data' => $defaultData],
            '16.6' => ['kode' => '16.6', 'nama' => 'Pemberian Komunikasi, Informasi dan Edukasi (KIE)', 'is_header' => false, 'data' => $defaultData],

            // 17. Balita Gizi Buruk mendapat perawatan
            '17' => ['kode' => '17', 'nama' => 'Balita Gizi Buruk mendapat perawatan', 'is_header' => false, 'data' => $defaultData],
            '17.1' => ['kode' => '17.1', 'nama' => 'Balita Gizi Buruk usia 0-5 bulan yang mendapat rawat inap', 'is_header' => false, 'data' => $defaultData],
            '17.2' => ['kode' => '17.2', 'nama' => 'Balita Gizi Buruk usia 6-59 bulan yang mendapat rawat inap', 'is_header' => false, 'data' => $defaultData],
            '17.3' => ['kode' => '17.3', 'nama' => 'Balita Gizi Buruk usia 6-59 bulan yang mendapat rawat jalan', 'is_header' => false, 'data' => $defaultData],
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
        
        // 1. CEK LAHIR MATI dulu (prioritas tertinggi)
        // Lahir mati adalah kematian SEBELUM atau SAAT lahir (bukan rawat inap meninggal)
        if (stripos($kondisiLahir, 'mati') !== false || stripos($kondisiLahir, 'meninggal') !== false) {
            // 2.1 Lahir Mati Antepartum
            if (stripos($kondisiLahir, 'antepartum') !== false) {
                $this->incrementKategori($kategoriMap, '2.1', $isRujukan, $sumberRujukan, true);
            }
            // 2.2 Lahir Mati Intrapartum
            else if (stripos($kondisiLahir, 'intrapartum') !== false) {
                $this->incrementKategori($kategoriMap, '2.2', $isRujukan, $sumberRujukan, true);
            }
            // 2. Lahir Mati (parent) - increment sekali saja
            $this->incrementKategori($kategoriMap, '2', $isRujukan, $sumberRujukan, true);
            return; // Jangan proses lebih lanjut jika lahir mati
        }
        
        // 2. BAYI LAHIR HIDUP - Tentukan kategori berdasarkan UK dan BB
        $kodeKategoriSpesifik = null;
        $kodeKategoriParent = null;
        
        // Lahir Prematur (< 37 minggu)
        if ($uk < 37) {
            $kodeKategoriParent = '1.1';
            
            if ($bb >= 1500 && $bb < 2500) {
                $kodeKategoriSpesifik = '1.1.1';
            } elseif ($bb >= 1000 && $bb < 1500) {
                $kodeKategoriSpesifik = '1.1.2';
            } elseif ($bb < 1000 && $bb > 0) {
                $kodeKategoriSpesifik = '1.1.3';
            }
        }
        // Lahir Non Prematur (37-41 minggu)
        elseif ($uk >= 37 && $uk <= 41) {
            $kodeKategoriParent = '1.2';
            
            if ($bb >= 1500 && $bb < 2500) {
                $kodeKategoriSpesifik = '1.2.1';
            } elseif ($bb >= 2500 && $bb < 4000) {
                $kodeKategoriSpesifik = '1.2.2';
            } elseif ($bb >= 4000) {
                $kodeKategoriSpesifik = '1.2.3';
            }
        }
        // Lahir Lebih dari 41 minggu
        elseif ($uk > 41) {
            $kodeKategoriParent = '1.3';
            
            if ($bb >= 1500 && $bb < 2500) {
                $kodeKategoriSpesifik = '1.3.1';
            } elseif ($bb >= 2500 && $bb < 4000) {
                $kodeKategoriSpesifik = '1.3.2';
            } elseif ($bb >= 4000) {
                $kodeKategoriSpesifik = '1.3.3';
            }
        }
        
        // Increment kategori spesifik saja (jika ada)
        if ($kodeKategoriSpesifik) {
            $this->incrementKategori($kategoriMap, $kodeKategoriSpesifik, $isRujukan, $sumberRujukan);
        }
        
        // Increment parent kategori (1.1, 1.2, atau 1.3)
        if ($kodeKategoriParent) {
            $this->incrementKategori($kategoriMap, $kodeKategoriParent, $isRujukan, $sumberRujukan);
        }
        
        // Increment kategori '1' (Bayi Lahir Hidup) - hanya sekali per bayi
        if ($kodeKategoriSpesifik || $kodeKategoriParent) {
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
                }
                // 3.2 Kematian Neonatal Lanjut (8-28 hari)
                elseif ($umurHari >= 8 && $umurHari <= 28) {
                    $this->incrementKategori($kategoriMap, '3.2', $isRujukan, $sumberRujukan, true);
                }
                
                // 3. Kematian Neonatal (parent) - increment sekali saja jika ada kematian
                if ($umurHari >= 0 && $umurHari <= 28) {
                    $this->incrementKategori($kategoriMap, '3', $isRujukan, $sumberRujukan, true);
                }
            }
        }
        
        // 4. KOMPLIKASI NEONATAL
        $this->processKomplikasiNeonatal($data, $kategoriMap, $isRujukan, $sumberRujukan);
        
        // 5. Bayi BBLR yang dilakukan perawatan metode kanguru
        if ($bb < 2500 && $bb > 0 && (
            stripos($data->rencana ?? '', 'kanguru') !== false ||
            stripos($data->rencana ?? '', 'kmc') !== false
        )) {
            $this->incrementKategori($kategoriMap, '5', $isRujukan, $sumberRujukan);
        }
        
        // 6. Bayi baru lahir yang dilakukan IMD
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
        
        // 8. Bayi dan Anak Balita - increment hanya kategori spesifik
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
            // Increment parent '8' hanya sekali
            $this->incrementKategori($kategoriMap, '8', $isRujukan, $sumberRujukan);
        }
        
        // 9. Gizi Buruk
        if (isset($data->total_nilai) && $data->total_nilai >= 2) {
            if ($umurBulan >= 0 && $umurBulan <= 5) {
                $this->incrementKategori($kategoriMap, '9.1', $isRujukan, $sumberRujukan);
            } elseif ($umurBulan >= 6 && $umurBulan <= 59) {
                $this->incrementKategori($kategoriMap, '9.2', $isRujukan, $sumberRujukan);
            }
            // Increment parent '9' hanya sekali
            $this->incrementKategori($kategoriMap, '9', $isRujukan, $sumberRujukan);
        }
        
        // 10. Buku KIA
        if (stripos($data->psiko_edukasi_keterangan ?? '', 'kia') !== false ||
            stripos($data->rencana ?? '', 'buku kia') !== false) {
            $this->incrementKategori($kategoriMap, '10', $isRujukan, $sumberRujukan);
        }
        
        // 11. Skrining pertumbuhan dan perkembangan
        if (isset($data->penilaian_humptydumpty_totalnilai) && $data->penilaian_humptydumpty_totalnilai > 0) {
            $this->incrementKategori($kategoriMap, '11.1', $isRujukan, $sumberRujukan);
            // Increment parent '11' hanya sekali
            $this->incrementKategori($kategoriMap, '11', $isRujukan, $sumberRujukan);
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
        
        // Increment parent '12' hanya sekali jika ada imunisasi
        if ($hasImunisasi) {
            $this->incrementKategori($kategoriMap, '12', $isRujukan, $sumberRujukan);
        }
        
        // ========================================================================
        // 13. Bayi yang lahir dari Ibu HIV+
        // ========================================================================
        $isFromHIVMother = false;
        
        if (stripos($data->rps ?? '', 'hiv') !== false ||
            stripos($data->rpk ?? '', 'hiv') !== false) {
            $isFromHIVMother = true;
        }
        
        if ($isFromHIVMother) {
            // Increment parent '13' hanya sekali
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
            // Increment parent '14' hanya sekali
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

        // ========================================================================
        // 15. Bayi yang lahir dari Ibu Hepatitis+
        // ========================================================================
        $isFromHepatitisMother = false;

        if (stripos($data->rps ?? '', 'hepatitis') !== false ||
            stripos($data->rpk ?? '', 'hepatitis') !== false ||
            stripos($data->rps ?? '', 'hbsag') !== false) {
            $isFromHepatitisMother = true;
        }

        if ($isFromHepatitisMother) {
            // Increment parent '15' hanya sekali
            $this->incrementKategori($kategoriMap, '15', $isRujukan, $sumberRujukan);
            
            // 15.1 Pemeriksaan serologis HBs Ag - ❌ TIDAK ADA di jns_perawatan
            // TODO: Tambahkan kode perawatan HBs Ag ke master jns_perawatan/jns_perawatan_inap
            // if ($this->hasPerawatan($perawatan, ['HBS AG', 'HEPATITIS B SURFACE'])) {
            //     $this->incrementKategori($kategoriMap, '15.1', $isRujukan, $sumberRujukan);
            // }
            
            // 15.2 Pemberian Hb 0 - ✅ Sama dengan 12.1
            if ($this->hasPerawatan($perawatan, ['NICU.008', 'MED-457', 'hepatitis b', 'hb 0'])) {
                $this->incrementKategori($kategoriMap, '15.2', $isRujukan, $sumberRujukan);
            }
            
            // 15.3 Pemberian Hb Ig - ❌ TIDAK ADA di jns_perawatan
            // TODO: Tambahkan kode perawatan Hepatitis B Immunoglobulin ke master jns_perawatan/jns_perawatan_inap
            // if ($this->hasPerawatan($perawatan, ['HB IG', 'HBIG', 'IMMUNOGLOBULIN'])) {
            //     $this->incrementKategori($kategoriMap, '15.3', $isRujukan, $sumberRujukan);
            // }
        }

        // ========================================================================
        // 16. Anak Balita (12-59 bulan) mendapatkan Imunisasi, Vitamin, dan Pengobatan profilaksis
        // ========================================================================
        if ($umurBulan >= 12 && $umurBulan <= 59) {
            $hasImunisasiBalita = false;
            
            // 16.1 Campak-Rubela - ❌ TIDAK ADA di jns_perawatan
            // TODO: Tambahkan kode perawatan Campak-Rubela ke master jns_perawatan/jns_perawatan_inap
            // if ($this->hasPerawatan($perawatan, ['CAMPAK', 'RUBELLA', 'MR'])) {
            //     $this->incrementKategori($kategoriMap, '16.1', $isRujukan, $sumberRujukan);
            //     $hasImunisasiBalita = true;
            // }
            
            // 16.2 Vitamin A 200.000 SI - ❌ TIDAK ADA di jns_perawatan
            // TODO: Tambahkan kode perawatan Vitamin A 200.000 SI ke master jns_perawatan/jns_perawatan_inap
            // if ($this->hasPerawatan($perawatan, ['VITAMIN A', 'VIT A 200'])) {
            //     $this->incrementKategori($kategoriMap, '16.2', $isRujukan, $sumberRujukan);
            //     $hasImunisasiBalita = true;
            // }
            
            // 16.3 Obat pencegahan kecacingan - ❌ TIDAK ADA di jns_perawatan
            // TODO: Tambahkan kode perawatan obat cacing ke master jns_perawatan/jns_perawatan_inap
            // if ($this->hasPerawatan($perawatan, ['ALBENDAZOLE', 'MEBENDAZOLE', 'OBAT CACING'])) {
            //     $this->incrementKategori($kategoriMap, '16.3', $isRujukan, $sumberRujukan);
            //     $hasImunisasiBalita = true;
            // }
            
            // 16.4 TPT (Terapi Pencegahan TBC) - ❌ TIDAK ADA di jns_perawatan
            // TODO: Tambahkan kode perawatan TPT ke master jns_perawatan/jns_perawatan_inap
            // if ($this->hasPerawatan($perawatan, ['TPT', 'ISONIAZID', 'INH'])) {
            //     $this->incrementKategori($kategoriMap, '16.4', $isRujukan, $sumberRujukan);
            //     $hasImunisasiBalita = true;
            // }
            
            // 16.5 OAT (Obat Anti Tuberkulosis) - ❌ TIDAK ADA di jns_perawatan
            // TODO: Tambahkan kode perawatan OAT ke master jns_perawatan/jns_perawatan_inap
            // if ($this->hasPerawatan($perawatan, ['OAT', 'RIFAMPICIN', 'RHZ'])) {
            //     $this->incrementKategori($kategoriMap, '16.5', $isRujukan, $sumberRujukan);
            //     $hasImunisasiBalita = true;
            // }
            
            // 16.6 KIE - ✅ ADA: KEP-08 (Manajemen laktasi)
            if ($this->hasPerawatan($perawatan, ['KEP-08', 'manajemen laktasi', 'kie', 'edukasi'])) {
                $this->incrementKategori($kategoriMap, '16.6', $isRujukan, $sumberRujukan);
                $hasImunisasiBalita = true;
            }
            
            // Increment parent '16' hanya sekali jika ada
            if ($hasImunisasiBalita) {
                $this->incrementKategori($kategoriMap, '16', $isRujukan, $sumberRujukan);
            }
        }

        // ========================================================================
        // 17. Balita Gizi Buruk mendapat perawatan
        // ========================================================================
        if (isset($data->total_nilai) && $data->total_nilai >= 2) {
            // 17.1 Balita Gizi Buruk 0-5 bulan rawat inap
            if ($umurBulan >= 0 && $umurBulan <= 5 && $data->status_lanjut == 'Ranap') {
                $this->incrementKategori($kategoriMap, '17.1', $isRujukan, $sumberRujukan);
            }
            
            // 17.2 Balita Gizi Buruk 6-59 bulan rawat inap
            if ($umurBulan >= 6 && $umurBulan <= 59 && $data->status_lanjut == 'Ranap') {
                $this->incrementKategori($kategoriMap, '17.2', $isRujukan, $sumberRujukan);
            }
            
            // 17.3 Balita Gizi Buruk 6-59 bulan rawat jalan
            if ($umurBulan >= 6 && $umurBulan <= 59 && $data->status_lanjut == 'Ralan') {
                $this->incrementKategori($kategoriMap, '17.3', $isRujukan, $sumberRujukan);
            }
            
            // Increment parent '17' hanya sekali
            if (($umurBulan >= 0 && $umurBulan <= 5 && $data->status_lanjut == 'Ranap') ||
                ($umurBulan >= 6 && $umurBulan <= 59)) {
                $this->incrementKategori($kategoriMap, '17', $isRujukan, $sumberRujukan);
            }
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
        $apgar1 = $this->getApgarMenitPertama($data->intranatal_apgar ?? '');
        if (stripos($kondisiLahir, 'asfiksia') !== false ||
                    ($apgar1 !== null && $apgar1 < 7)) {
            $this->incrementKategori($kategoriMap, '4.1', $isRujukan, $sumberRujukan);
            $komplikasiFound = true;
            //echo "<script>console.log(' \$kondisiLahir : $kondisiLahir ')</script>";
            //echo "<script>console.log(' \$data->intranatal_apgar : $data->intranatal_apgar ')</script>";
            //echo "<script>console.log(' ================== ')</script>";
        }
        
        // 4.2 Trauma Kelahiran
        if (stripos($data->keluhan_utama ?? '', 'trauma') !== false ||
            stripos($kondisiLahir, 'trauma') !== false ||
            ($data->saraf_pusat_kepala ?? '') == 'Hematoma') {
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
            ($data->reproduksi ?? 'Normal') != 'Normal') {
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
        
        // Increment parent kategori 4 HANYA SEKALI jika ada komplikasi
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

    private function generateRL37Excel($tanggalAwal, $tanggalAkhir, $kategori, $hospitalInfo)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('RL 3.7');
        
        // Header information with enhanced styling
        $sheet->setCellValue('A1', 'RL 3.7 - REKAPITULASI KEGIATAN PELAYANAN NEONATAL, BAYI, DAN BALITA');
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($tanggalAwal)) . ' - ' . date('d/m/Y', strtotime($tanggalAkhir)));
        
        // Merge cells for title
        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');
        
        // Enhanced title styling
        $titleStyle = [
            'font' => [
                'bold' => true, 
                'size' => 16,
                'color' => ['rgb' => '1F4E79']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID, 
                'startColor' => ['rgb' => 'D6EAF8']
            ],
        ];
        
        $periodStyle = [
            'font' => [
                'bold' => true, 
                'size' => 12,
                'color' => ['rgb' => '2E4053']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID, 
                'startColor' => ['rgb' => 'EBF5FB']
            ],
        ];
        
        $sheet->getStyle('A1:N1')->applyFromArray($titleStyle);
        $sheet->getStyle('A2:N2')->applyFromArray($periodStyle);
        
        // Set row heights for title
        $sheet->getRowDimension(1)->setRowHeight(35);
        $sheet->getRowDimension(2)->setRowHeight(25);
        
        // Add empty row for spacing
        $sheet->getRowDimension(3)->setRowHeight(10);
        
        // ========================================================================
        // TABLE HEADERS - STRUKTUR YANG BENAR
        // ========================================================================
        
        // Row 4 - Main headers
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Jenis Kegiatan');
        $sheet->setCellValue('C4', 'Rujukan');
        $sheet->mergeCells('C4:J4'); // Rujukan: C-J (8 kolom)
        $sheet->setCellValue('K4', 'Non Rujukan');
        $sheet->mergeCells('K4:M4'); // Non Rujukan: K-M (3 kolom)
        $sheet->setCellValue('N4', 'Dirujuk'); // Dirujuk: hanya 1 kolom N
        
        // Row 5 - Sub headers untuk Rujukan saja
        $sheet->setCellValue('C5', 'Medis');
        $sheet->mergeCells('C5:G5'); // Medis: C-G (5 kolom)
        $sheet->setCellValue('H5', 'Non Medis');
        $sheet->mergeCells('H5:J5'); // Non Medis: H-J (3 kolom)
        
        // Row 6 - Detail columns
        // Rujukan Medis (C-G)
        $sheet->setCellValue('C6', 'RS');
        $sheet->setCellValue('D6', 'Bidan');
        $sheet->setCellValue('E6', 'Puskes');
        $sheet->setCellValue('F6', 'Faskes Lain');
        $sheet->setCellValue('G6', 'Total Rujukan Medis');
        
        // Rujukan Non Medis (H-J)
        $sheet->setCellValue('H6', 'Hidup');
        $sheet->setCellValue('I6', 'Mati');
        $sheet->setCellValue('J6', 'Total Non Medis');
        
        // Non Rujukan (K-M)
        $sheet->setCellValue('K6', 'Hidup');
        $sheet->setCellValue('L6', 'Mati');
        $sheet->setCellValue('M6', 'Total Non Rujukan');
        
        // Merge cells untuk No, Jenis Kegiatan, dan Dirujuk (3 rows)
        $sheet->mergeCells('A4:A6');
        $sheet->mergeCells('B4:B6');
        $sheet->mergeCells('N4:N6'); // Dirujuk merge semua 3 rows
        
        // Enhanced header styling
        $mainHeaderStyle = [
            'font' => [
                'bold' => true, 
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER, 
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '2C3E50']
                ]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID, 
                'startColor' => ['rgb' => '2E86C1'] // Blue main header
            ]
        ];
        
        $subHeaderStyle = [
            'font' => [
                'bold' => true, 
                'size' => 10,
                'color' => ['rgb' => '2C3E50']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER, 
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '2C3E50']
                ]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID, 
                'startColor' => ['rgb' => 'AED6F1'] // Light blue for sub headers
            ]
        ];
        
        // Apply header styles
        $sheet->getStyle('A4:N6')->applyFromArray($mainHeaderStyle);
        
        // Set row heights for headers
        $sheet->getRowDimension(4)->setRowHeight(30);
        $sheet->getRowDimension(5)->setRowHeight(25);
        $sheet->getRowDimension(6)->setRowHeight(25);
        
        // ========================================================================
        // DATA ROWS
        // ========================================================================
        $row = 7;
        $no = 1;
        
        foreach ($kategori as $kat) {
            if ($kat['is_header']) {
                // Section header (NEONATAL, BAYI DAN ANAK BALITA)
                $sheet->mergeCells("A{$row}:N{$row}");
                $sheet->setCellValue("A{$row}", $kat['nama']);
                
                $sectionHeaderStyle = [
                    'font' => [
                        'bold' => true, 
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '2C3E50']
                        ]
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID, 
                        'startColor' => ['rgb' => '5DADE2'] // Medium blue for section headers
                    ]
                ];
                
                $sheet->getStyle("A{$row}:N{$row}")->applyFromArray($sectionHeaderStyle);
                $sheet->getRowDimension($row)->setRowHeight(25);
            } else {
                // Data row
                $d = $kat['data'];
                
                $sheet->setCellValue("A{$row}", $no++);
                $sheet->setCellValue("B{$row}", $kat['nama']);
                
                // Rujukan Medis (C-G)
                $sheet->setCellValue("C{$row}", $d['rs']);
                $sheet->setCellValue("D{$row}", $d['bidan']);
                $sheet->setCellValue("E{$row}", $d['puskes']);
                $sheet->setCellValue("F{$row}", $d['faskes']);
                $sheet->setCellValue("G{$row}", $d['total_medis']);
                
                // Rujukan Non Medis (H-J)
                $sheet->setCellValue("H{$row}", $d['hidup_non_medis']);
                $sheet->setCellValue("I{$row}", $d['mati_non_medis']);
                $sheet->setCellValue("J{$row}", $d['total_non_medis']);
                
                // Non Rujukan (K-M)
                $sheet->setCellValue("K{$row}", $d['hidup_non_rujuk']);
                $sheet->setCellValue("L{$row}", $d['mati_non_rujuk']);
                $sheet->setCellValue("M{$row}", $d['total_non_rujuk']);
                
                // Dirujuk (N) - hanya 1 kolom
                $sheet->setCellValue("N{$row}", $d['dirujuk']);
                
                // Enhanced data row styling with alternating colors
                $dataStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '85929E']
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'font' => ['size' => 11, 'color' => ['rgb' => '2C3E50']]
                ];
                
                // Apply alternating row colors
                if (($row - 7) % 2 == 0) {
                    // Even rows - white background
                    $evenRowStyle = array_merge($dataStyle, [
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID, 
                            'startColor' => ['rgb' => 'FFFFFF']
                        ]
                    ]);
                    $sheet->getStyle("A{$row}:N{$row}")->applyFromArray($evenRowStyle);
                } else {
                    // Odd rows - light gray background
                    $oddRowStyle = array_merge($dataStyle, [
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID, 
                            'startColor' => ['rgb' => 'F8F9FA']
                        ]
                    ]);
                    $sheet->getStyle("A{$row}:N{$row}")->applyFromArray($oddRowStyle);
                }
                
                // Special styling for Jenis Kegiatan column (B) - left alignment with wrap text
                $jenisKegiatanStyle = [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ]
                ];
                $sheet->getStyle("B{$row}")->applyFromArray($jenisKegiatanStyle);
                
                // Enhanced total column styling (G, J, M columns)
                $totalStyle = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID, 
                        'startColor' => ['rgb' => 'D5EDDB']
                    ],
                    'font' => ['bold' => true, 'color' => ['rgb' => '1B4F3C'], 'size' => 11]
                ];
                $sheet->getStyle("G{$row}")->applyFromArray($totalStyle);
                $sheet->getStyle("J{$row}")->applyFromArray($totalStyle);
                $sheet->getStyle("M{$row}")->applyFromArray($totalStyle);
                
                // Set row height
                $sheet->getRowDimension($row)->setRowHeight(20);
            }
            $row++;
        }
        
        // ========================================================================
        // COLUMN DIMENSIONS
        // ========================================================================
        $sheet->getColumnDimension('A')->setWidth(8);  // No
        $sheet->getColumnDimension('B')->setWidth(55); // Jenis Kegiatan
        $sheet->getColumnDimension('C')->setWidth(12); // RS
        $sheet->getColumnDimension('D')->setWidth(12); // Bidan
        $sheet->getColumnDimension('E')->setWidth(12); // Puskes
        $sheet->getColumnDimension('F')->setWidth(14); // Faskes Lain
        $sheet->getColumnDimension('G')->setWidth(18); // Total Rujukan Medis
        $sheet->getColumnDimension('H')->setWidth(12); // Hidup (Non Medis)
        $sheet->getColumnDimension('I')->setWidth(12); // Mati (Non Medis)
        $sheet->getColumnDimension('J')->setWidth(15); // Total Non Medis
        $sheet->getColumnDimension('K')->setWidth(12); // Hidup (Non Rujukan)
        $sheet->getColumnDimension('L')->setWidth(12); // Mati (Non Rujukan)
        $sheet->getColumnDimension('M')->setWidth(16); // Total Non Rujukan
        $sheet->getColumnDimension('N')->setWidth(14); // Dirujuk (1 kolom)
        
        // ========================================================================
        // SET AUTOFILTER (untuk sorting seperti RL 3.5)
        // ========================================================================
        $sheet->setAutoFilter('A6:N' . ($row-1));
        
        // ========================================================================
        // FREEZE PANES (freeze header dan kolom No + Jenis Kegiatan)
        // ========================================================================
        //$sheet->freezePane('C7'); // Freeze 2 kolom pertama dan 6 baris pertama
        
        // ========================================================================
        // OUTPUT
        // ========================================================================
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        
        $filename = 'RL37_Neonatal_Bayi_Balita_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir)) . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        
        $writer->save('php://output');
        exit();
    }

    // Akhir Laporan RL 3.7
    ///////////////////////////////////////////////////////////////
       
}
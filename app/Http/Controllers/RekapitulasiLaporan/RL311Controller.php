<?php

namespace App\Http\Controllers\RekapitulasiLaporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class RL311Controller extends Controller
{
    public function laporanRL311(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // Get data dari poliklinik gigi dan bedah mulut
        $dataGigiMulut = $this->getDataGigiMulut($tanggalAwal, $tanggalAkhir);
        
        $hospitalInfo = DB::table('setting')->first();

        if ($request->has('download_pdf')) {
            return $this->generateRL311PDF($tanggalAwal, $tanggalAkhir, $dataGigiMulut, $hospitalInfo);
        }

        if ($request->has('download_excel')) {
            return $this->generateRL311Excel($tanggalAwal, $tanggalAkhir, $dataGigiMulut, $hospitalInfo);
        }

        return view('rm.laporan_rm.rl311', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'data' => $dataGigiMulut,
            'hospitalInfo' => $hospitalInfo
        ]);
    }

    private function getDataGigiMulut($tanggalAwal, $tanggalAkhir)
    {
        // Inisialisasi struktur data sesuai RL 3.11
        $kategori = [
            1 => ['nama' => 'Tumpatan Gigi Tetap', 'jumlah' => 0],
            2 => ['nama' => 'Tumpatan Gigi Sulung', 'jumlah' => 0],
            3 => ['nama' => 'Pengobatan Pulpa', 'jumlah' => 0],
            4 => ['nama' => 'Pencabutan Gigi Tetap', 'jumlah' => 0],
            5 => ['nama' => 'Pencabutan Gigi Sulung', 'jumlah' => 0],
            6 => ['nama' => 'Pengobatan Periodontal', 'jumlah' => 0],
            7 => ['nama' => 'Pengobatan Abses', 'jumlah' => 0],
            8 => ['nama' => 'Pembersihan Karang Gigi', 'jumlah' => 0],
            9 => ['nama' => 'Prothese Lengkap', 'jumlah' => 0],
            10 => ['nama' => 'Prothese Sebagian', 'jumlah' => 0],
            11 => ['nama' => 'Prothese Cekat', 'jumlah' => 0],
            12 => ['nama' => 'Orthodonti', 'jumlah' => 0],
            13 => ['nama' => 'Jacket/Bridge', 'jumlah' => 0],
            14 => ['nama' => 'Bedah Mulut', 'jumlah' => 0],
            15 => ['nama' => 'Implan Gigi', 'jumlah' => 0],
            16 => ['nama' => 'Penyakit Mulut', 'jumlah' => 0],
            99 => ['nama' => 'TOTAL', 'jumlah' => 0]
        ];

        // Query data perawatan dari poliklinik gigi dan bedah mulut
        // Gabungkan data dari 3 tabel: rawat_jl_dr, rawat_jl_pr, rawat_jl_drpr
        
        // 1. Data dari rawat_jl_dr (dokter)
        $queryDr = DB::table('rawat_jl_dr as rj')
            ->join('reg_periksa as rp', 'rj.no_rawat', '=', 'rp.no_rawat')
            ->join('poliklinik as pol', 'rp.kd_poli', '=', 'pol.kd_poli')
            ->join('jns_perawatan as jp', 'rj.kd_jenis_prw', '=', 'jp.kd_jenis_prw')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where(function($query) {
                $query->where('pol.nm_poli', 'like', '%gigi%')
                      ->orWhere('pol.nm_poli', 'like', '%bedah mulut%');
            })
            ->select('jp.nm_perawatan', 'jp.kd_jenis_prw');

        // 2. Data dari rawat_jl_pr (perawat)
        $queryPr = DB::table('rawat_jl_pr as rj')
            ->join('reg_periksa as rp', 'rj.no_rawat', '=', 'rp.no_rawat')
            ->join('poliklinik as pol', 'rp.kd_poli', '=', 'pol.kd_poli')
            ->join('jns_perawatan as jp', 'rj.kd_jenis_prw', '=', 'jp.kd_jenis_prw')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where(function($query) {
                $query->where('pol.nm_poli', 'like', '%gigi%')
                      ->orWhere('pol.nm_poli', 'like', '%bedah mulut%');
            })
            ->select('jp.nm_perawatan', 'jp.kd_jenis_prw');

        // 3. Data dari rawat_jl_drpr (dokter & perawat)
        $queryDrPr = DB::table('rawat_jl_drpr as rj')
            ->join('reg_periksa as rp', 'rj.no_rawat', '=', 'rp.no_rawat')
            ->join('poliklinik as pol', 'rp.kd_poli', '=', 'pol.kd_poli')
            ->join('jns_perawatan as jp', 'rj.kd_jenis_prw', '=', 'jp.kd_jenis_prw')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where(function($query) {
                $query->where('pol.nm_poli', 'like', '%gigi%')
                      ->orWhere('pol.nm_poli', 'like', '%bedah mulut%');
            })
            ->select('jp.nm_perawatan', 'jp.kd_jenis_prw');

        // Gabungkan dengan UNION ALL dan hitung jumlahnya
        $perawatan = $queryDr
            ->unionAll($queryPr)
            ->unionAll($queryDrPr)
            ->get()
            ->groupBy(function($item) {
                return $item->kd_jenis_prw . '|' . $item->nm_perawatan;
            })
            ->map(function($group) {
                $first = $group->first();
                return (object)[
                    'kd_jenis_prw' => $first->kd_jenis_prw,
                    'nm_perawatan' => $first->nm_perawatan,
                    'jumlah' => $group->count()
                ];
            });

        // Mapping perawatan ke kategori
        foreach ($perawatan as $prw) {
            $nmPerawatan = strtolower($prw->nm_perawatan);
            $kdPerawatan = $prw->kd_jenis_prw;
            
            // 1. Tumpatan Gigi Tetap
            if (stripos($nmPerawatan, 'tambalan') !== false || 
                stripos($nmPerawatan, 'tumpatan') !== false) {
                if (stripos($nmPerawatan, 'sulung') === false && 
                    stripos($nmPerawatan, 'susu') === false) {
                    $kategori[1]['jumlah'] += $prw->jumlah;
                    continue;
                }
            }
            
            // 2. Tumpatan Gigi Sulung
            if ((stripos($nmPerawatan, 'tambalan') !== false || 
                 stripos($nmPerawatan, 'tumpatan') !== false) && 
                (stripos($nmPerawatan, 'sulung') !== false || 
                 stripos($nmPerawatan, 'susu') !== false)) {
                $kategori[2]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 3. Pengobatan Pulpa
            if (stripos($nmPerawatan, 'pulpa') !== false) {
                $kategori[3]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 4. Pencabutan Gigi Tetap
            if ((stripos($nmPerawatan, 'cabut gigi tetap') !== false || 
                 stripos($nmPerawatan, 'pencabutan gigi tetap') !== false) ||
                (in_array($kdPerawatan, ['BDM001', 'BDM002', 'GIG028', 'GIG029', 'MED-85', 'MED-86']))) {
                $kategori[4]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 5. Pencabutan Gigi Sulung
            if ((stripos($nmPerawatan, 'cabut gigi susu') !== false || 
                 stripos($nmPerawatan, 'cabut gigi sulung') !== false ||
                 stripos($nmPerawatan, 'pencabutan gigi sulung') !== false) ||
                (in_array($kdPerawatan, ['BDM003', 'BDM004', 'GIG030', 'GIG031', 'MED-87', 'MED-88']))) {
                $kategori[5]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 6. Pengobatan Periodontal
            if (stripos($nmPerawatan, 'periodontal') !== false ||
                stripos($nmPerawatan, 'gusi') !== false) {
                $kategori[6]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 7. Pengobatan Abses
            if (stripos($nmPerawatan, 'abses') !== false ||
                stripos($nmPerawatan, 'incici') !== false ||
                in_array($kdPerawatan, ['BDM009', 'BDM009a', 'BDM010', 'BDM010a', 'GIG025', 'MED-93', 'MED-94'])) {
                $kategori[7]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 8. Pembersihan Karang Gigi
            if (stripos($nmPerawatan, 'scaling') !== false ||
                stripos($nmPerawatan, 'ultrasonic') !== false ||
                stripos($nmPerawatan, 'karang gigi') !== false ||
                stripos($nmPerawatan, 'pembersihan karang') !== false ||
                stripos($nmPerawatan, 'manual') !== false ||
                in_array($kdPerawatan, ['GIG022', 'GIG023', 'MED-108', 'MED-109'])) {
                $kategori[8]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 9. Prothese Lengkap
            if ((stripos($nmPerawatan, 'protesa') !== false || 
                 stripos($nmPerawatan, 'prothese') !== false) && 
                (stripos($nmPerawatan, 'penuh') !== false ||
                 stripos($nmPerawatan, '2 rahang') !== false) ||
                in_array($kdPerawatan, ['GIG013', 'GIG014', 'MED-99', 'MED-100'])) {
                $kategori[9]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 10. Prothese Sebagian
            if ((stripos($nmPerawatan, 'protesa') !== false || 
                 stripos($nmPerawatan, 'prothese') !== false) && 
                (stripos($nmPerawatan, 'sebagian') !== false ||
                 stripos($nmPerawatan, 'plate') !== false ||
                 stripos($nmPerawatan, 'elemen') !== false) ||
                in_array($kdPerawatan, ['GIG011', 'GIG012', 'MED-97', 'MED-98'])) {
                $kategori[10]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 11. Prothese Cekat
            if ((stripos($nmPerawatan, 'protesa') !== false || 
                 stripos($nmPerawatan, 'prothese') !== false) && 
                stripos($nmPerawatan, 'cekat') !== false ||
                in_array($kdPerawatan, ['GIG019', 'MED-105'])) {
                $kategori[11]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 12. Orthodonti
            if (stripos($nmPerawatan, 'ortho') !== false ||
                stripos($nmPerawatan, 'kawat gigi') !== false ||
                stripos($nmPerawatan, 'behel') !== false ||
                stripos($nmPerawatan, 'aktivasi') !== false ||
                stripos($nmPerawatan, 'plate retra') !== false ||
                in_array($kdPerawatan, ['GIG020', 'GIG021', 'MED-106', 'MED-107'])) {
                $kategori[12]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 13. Jacket/Bridge
            if (stripos($nmPerawatan, 'jacket') !== false ||
                stripos($nmPerawatan, 'bridge') !== false ||
                stripos($nmPerawatan, 'jembatan') !== false) {
                $kategori[13]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 14. Bedah Mulut
            if (stripos($nmPerawatan, 'bedah') !== false ||
                stripos($nmPerawatan, 'odontectomy') !== false ||
                stripos($nmPerawatan, 'odontectomi') !== false ||
                stripos($nmPerawatan, 'uperculetomy') !== false ||
                stripos($nmPerawatan, 'uper culetomi') !== false ||
                stripos($nmPerawatan, 'alveolectomy') !== false ||
                stripos($nmPerawatan, 'alveolectomi') !== false ||
                stripos($nmPerawatan, 'extirpasi') !== false ||
                stripos($nmPerawatan, 'ekstirpasi') !== false ||
                stripos($nmPerawatan, 'fixasi') !== false ||
                stripos($nmPerawatan, 'operasi') !== false ||
                stripos($nmPerawatan, 'operatif') !== false ||
                in_array($kdPerawatan, ['BDM005', 'BDM006', 'BDM007', 'BDM008', 'BDM011', 'BDM012', 
                                        'BDM015', 'BDM062', 'BDM063', 'GIG024', 'GIG026', 'GIG027',
                                        'MED-89', 'MED-90', 'MED-91', 'MED-92', 'MED-95', 'MED-96',
                                        'MED-110', 'MED-17-IGD', 'MED-17_IGD'])) {
                $kategori[14]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 15. Implan Gigi
            if (stripos($nmPerawatan, 'implan') !== false ||
                stripos($nmPerawatan, 'implant') !== false) {
                $kategori[15]['jumlah'] += $prw->jumlah;
                continue;
            }
            
            // 16. Penyakit Mulut
            if (stripos($nmPerawatan, 'penyakit') !== false ||
                stripos($nmPerawatan, 'stomatitis') !== false ||
                stripos($nmPerawatan, 'sariawan') !== false ||
                stripos($nmPerawatan, 'soft tissue') !== false ||
                in_array($kdPerawatan, ['MED-66'])) {
                $kategori[16]['jumlah'] += $prw->jumlah;
                continue;
            }
        }

        // Hitung total
        $total = 0;
        for ($i = 1; $i <= 16; $i++) {
            $total += $kategori[$i]['jumlah'];
        }
        $kategori[99]['jumlah'] = $total;

        return $kategori;
    }

    public function laporanRL311Detail(Request $request)
    {
        $kategoriNo = $request->input('kategori');
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        // Base query untuk 3 tabel
        // 1. Query rawat_jl_dr (dokter)
        $queryDr = DB::table('rawat_jl_dr as rj')
            ->join('reg_periksa as rp', 'rj.no_rawat', '=', 'rp.no_rawat')
            ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
            ->join('poliklinik as pol', 'rp.kd_poli', '=', 'pol.kd_poli')
            ->join('jns_perawatan as jp', 'rj.kd_jenis_prw', '=', 'jp.kd_jenis_prw')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where(function($q) {
                $q->where('pol.nm_poli', 'like', '%gigi%')
                  ->orWhere('pol.nm_poli', 'like', '%bedah mulut%');
            })
            ->select(
                'rj.no_rawat',
                'rp.no_rkm_medis',
                'p.nm_pasien',
                'p.jk',
                'rp.tgl_registrasi',
                'jp.nm_perawatan',
                'jp.kd_jenis_prw',
                'pol.nm_poli',
                DB::raw("'Dokter' as jenis_pelayanan")
            );

        // 2. Query rawat_jl_pr (perawat)
        $queryPr = DB::table('rawat_jl_pr as rj')
            ->join('reg_periksa as rp', 'rj.no_rawat', '=', 'rp.no_rawat')
            ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
            ->join('poliklinik as pol', 'rp.kd_poli', '=', 'pol.kd_poli')
            ->join('jns_perawatan as jp', 'rj.kd_jenis_prw', '=', 'jp.kd_jenis_prw')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where(function($q) {
                $q->where('pol.nm_poli', 'like', '%gigi%')
                  ->orWhere('pol.nm_poli', 'like', '%bedah mulut%');
            })
            ->select(
                'rj.no_rawat',
                'rp.no_rkm_medis',
                'p.nm_pasien',
                'p.jk',
                'rp.tgl_registrasi',
                'jp.nm_perawatan',
                'jp.kd_jenis_prw',
                'pol.nm_poli',
                DB::raw("'Perawat' as jenis_pelayanan")
            );

        // 3. Query rawat_jl_drpr (dokter & perawat)
        $queryDrPr = DB::table('rawat_jl_drpr as rj')
            ->join('reg_periksa as rp', 'rj.no_rawat', '=', 'rp.no_rawat')
            ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
            ->join('poliklinik as pol', 'rp.kd_poli', '=', 'pol.kd_poli')
            ->join('jns_perawatan as jp', 'rj.kd_jenis_prw', '=', 'jp.kd_jenis_prw')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where(function($q) {
                $q->where('pol.nm_poli', 'like', '%gigi%')
                  ->orWhere('pol.nm_poli', 'like', '%bedah mulut%');
            })
            ->select(
                'rj.no_rawat',
                'rp.no_rkm_medis',
                'p.nm_pasien',
                'p.jk',
                'rp.tgl_registrasi',
                'jp.nm_perawatan',
                'jp.kd_jenis_prw',
                'pol.nm_poli',
                DB::raw("'Dokter & Perawat' as jenis_pelayanan")
            );

        // Apply filter kategori ke setiap query
        $queryDr = $this->applyKategoriFilter($queryDr, $kategoriNo);
        $queryPr = $this->applyKategoriFilter($queryPr, $kategoriNo);
        $queryDrPr = $this->applyKategoriFilter($queryDrPr, $kategoriNo);

        // Gabungkan dengan UNION ALL
        $data = $queryDr
            ->unionAll($queryPr)
            ->unionAll($queryDrPr)
            ->orderBy('tgl_registrasi', 'desc')
            ->get();

        $kategoriMap = [
            1 => 'Tumpatan Gigi Tetap',
            2 => 'Tumpatan Gigi Sulung',
            3 => 'Pengobatan Pulpa',
            4 => 'Pencabutan Gigi Tetap',
            5 => 'Pencabutan Gigi Sulung',
            6 => 'Pengobatan Periodontal',
            7 => 'Pengobatan Abses',
            8 => 'Pembersihan Karang Gigi',
            9 => 'Prothese Lengkap',
            10 => 'Prothese Sebagian',
            11 => 'Prothese Cekat',
            12 => 'Orthodonti',
            13 => 'Jacket/Bridge',
            14 => 'Bedah Mulut',
            15 => 'Implan Gigi',
            16 => 'Penyakit Mulut'
        ];

        return view('rm.laporan_rm.rl311_detail', [
            'data' => $data,
            'kategoriNo' => $kategoriNo,
            'kategoriNama' => $kategoriMap[$kategoriNo] ?? 'Unknown',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir
        ]);
    }

    private function applyKategoriFilter($query, $kategoriNo)
    {
        switch ($kategoriNo) {
            case 1: // Tumpatan Gigi Tetap
                $query->where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('jp.nm_perawatan', 'like', '%tambalan%')
                           ->orWhere('jp.nm_perawatan', 'like', '%tumpatan%');
                    })->where(function($q3) {
                        $q3->where('jp.nm_perawatan', 'not like', '%sulung%')
                           ->where('jp.nm_perawatan', 'not like', '%susu%');
                    });
                });
                break;
            case 2: // Tumpatan Gigi Sulung
                $query->where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('jp.nm_perawatan', 'like', '%tambalan%')
                           ->orWhere('jp.nm_perawatan', 'like', '%tumpatan%');
                    })->where(function($q3) {
                        $q3->where('jp.nm_perawatan', 'like', '%sulung%')
                           ->orWhere('jp.nm_perawatan', 'like', '%susu%');
                    });
                });
                break;
            case 3: // Pengobatan Pulpa
                $query->where('jp.nm_perawatan', 'like', '%pulpa%');
                break;
            case 4: // Pencabutan Gigi Tetap
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%cabut gigi tetap%')
                      ->orWhere('jp.nm_perawatan', 'like', '%pencabutan gigi tetap%')
                      ->orWhereIn('jp.kd_jenis_prw', ['BDM001', 'BDM002', 'GIG028', 'GIG029', 'MED-85', 'MED-86']);
                });
                break;
            case 5: // Pencabutan Gigi Sulung
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%cabut gigi susu%')
                      ->orWhere('jp.nm_perawatan', 'like', '%cabut gigi sulung%')
                      ->orWhere('jp.nm_perawatan', 'like', '%pencabutan gigi sulung%')
                      ->orWhereIn('jp.kd_jenis_prw', ['BDM003', 'BDM004', 'GIG030', 'GIG031', 'MED-87', 'MED-88']);
                });
                break;
            case 6: // Pengobatan Periodontal
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%periodontal%')
                      ->orWhere('jp.nm_perawatan', 'like', '%gusi%');
                });
                break;
            case 7: // Pengobatan Abses
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%abses%')
                      ->orWhere('jp.nm_perawatan', 'like', '%incici%')
                      ->orWhereIn('jp.kd_jenis_prw', ['BDM009', 'BDM009a', 'BDM010', 'BDM010a', 'GIG025', 'MED-93', 'MED-94']);
                });
                break;
            case 8: // Pembersihan Karang Gigi
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%scaling%')
                      ->orWhere('jp.nm_perawatan', 'like', '%ultrasonic%')
                      ->orWhere('jp.nm_perawatan', 'like', '%karang gigi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%pembersihan karang%')
                      ->orWhere('jp.nm_perawatan', 'like', '%manual%')
                      ->orWhereIn('jp.kd_jenis_prw', ['GIG022', 'GIG023', 'MED-108', 'MED-109']);
                });
                break;
            case 9: // Prothese Lengkap
                $query->where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('jp.nm_perawatan', 'like', '%protesa%')
                           ->orWhere('jp.nm_perawatan', 'like', '%prothese%');
                    })->where(function($q3) {
                        $q3->where('jp.nm_perawatan', 'like', '%penuh%')
                           ->orWhere('jp.nm_perawatan', 'like', '%2 rahang%');
                    })->orWhereIn('jp.kd_jenis_prw', ['GIG013', 'GIG014', 'MED-99', 'MED-100']);
                });
                break;
            case 10: // Prothese Sebagian
                $query->where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('jp.nm_perawatan', 'like', '%protesa%')
                           ->orWhere('jp.nm_perawatan', 'like', '%prothese%');
                    })->where(function($q3) {
                        $q3->where('jp.nm_perawatan', 'like', '%sebagian%')
                           ->orWhere('jp.nm_perawatan', 'like', '%plate%')
                           ->orWhere('jp.nm_perawatan', 'like', '%elemen%');
                    })->orWhereIn('jp.kd_jenis_prw', ['GIG011', 'GIG012', 'MED-97', 'MED-98']);
                });
                break;
            case 11: // Prothese Cekat
                $query->where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('jp.nm_perawatan', 'like', '%protesa%')
                           ->orWhere('jp.nm_perawatan', 'like', '%prothese%');
                    })->where('jp.nm_perawatan', 'like', '%cekat%')
                      ->orWhereIn('jp.kd_jenis_prw', ['GIG019', 'MED-105']);
                });
                break;
            case 12: // Orthodonti
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%ortho%')
                      ->orWhere('jp.nm_perawatan', 'like', '%kawat gigi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%behel%')
                      ->orWhere('jp.nm_perawatan', 'like', '%aktivasi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%plate retra%')
                      ->orWhereIn('jp.kd_jenis_prw', ['GIG020', 'GIG021', 'MED-106', 'MED-107']);
                });
                break;
            case 13: // Jacket/Bridge
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%jacket%')
                      ->orWhere('jp.nm_perawatan', 'like', '%bridge%')
                      ->orWhere('jp.nm_perawatan', 'like', '%jembatan%');
                });
                break;
            case 14: // Bedah Mulut
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%bedah%')
                      ->orWhere('jp.nm_perawatan', 'like', '%odontectomy%')
                      ->orWhere('jp.nm_perawatan', 'like', '%odontectomi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%uperculetomy%')
                      ->orWhere('jp.nm_perawatan', 'like', '%uper culetomi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%alveolectomy%')
                      ->orWhere('jp.nm_perawatan', 'like', '%alveolectomi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%extirpasi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%ekstirpasi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%fixasi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%operasi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%operatif%')
                      ->orWhereIn('jp.kd_jenis_prw', ['BDM005', 'BDM006', 'BDM007', 'BDM008', 'BDM011', 'BDM012', 
                                                       'BDM015', 'BDM062', 'BDM063', 'GIG024', 'GIG026', 'GIG027',
                                                       'MED-89', 'MED-90', 'MED-91', 'MED-92', 'MED-95', 'MED-96',
                                                       'MED-110', 'MED-17-IGD', 'MED-17_IGD']);
                });
                break;
            case 15: // Implan Gigi
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%implan%')
                      ->orWhere('jp.nm_perawatan', 'like', '%implant%');
                });
                break;
            case 16: // Penyakit Mulut
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%penyakit%')
                      ->orWhere('jp.nm_perawatan', 'like', '%stomatitis%')
                      ->orWhere('jp.nm_perawatan', 'like', '%sariawan%')
                      ->orWhere('jp.nm_perawatan', 'like', '%soft tissue%')
                      ->orWhereIn('jp.kd_jenis_prw', ['MED-66']);
                });
                break;
        }

        return $query;
    }

    private function generateRL311PDF($tanggalAwal, $tanggalAkhir, $data, $hospitalInfo)
    {
        $pdf = PDF::loadView('rm.laporan_rm.rl311_pdf', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'data' => $data,
            'hospitalInfo' => $hospitalInfo
        ]);

        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'RL311_Gigi_Mulut_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir)) . '.pdf';
        
        return $pdf->download($filename);
    }

    private function generateRL311Excel($tanggalAwal, $tanggalAkhir, $data, $hospitalInfo)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('RL 3.11');

        // Header
        $sheet->setCellValue('A1', 'RL 3.11 - REKAPITULASI KEGIATAN PELAYANAN GIGI DAN MULUT');
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($tanggalAwal)) . ' - ' . date('d/m/Y', strtotime($tanggalAkhir)));
        
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');

        // Styling header
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($titleStyle);
        $sheet->getStyle('A2:C2')->applyFromArray($titleStyle);

        // Table header
        $row = 4;
        $sheet->setCellValue("A{$row}", 'No.');
        $sheet->setCellValue("B{$row}", 'Jenis Kegiatan');
        $sheet->setCellValue("C{$row}", 'Jumlah');

        $headerStyle = [
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']]
        ];
        $sheet->getStyle("A{$row}:C{$row}")->applyFromArray($headerStyle);

        // Data
        $row = 5;
        foreach ($data as $no => $item) {
            if ($no == 99) continue; // Skip total, akan ditambahkan di akhir
            
            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $item['nama']);
            $sheet->setCellValue("C{$row}", $item['jumlah']);
            $row++;
        }

        // Total row
        $sheet->setCellValue("A{$row}", '99');
        $sheet->setCellValue("B{$row}", 'TOTAL');
        $sheet->setCellValue("C{$row}", $data[99]['jumlah']);
        
        $totalStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]
        ];
        $sheet->getStyle("A{$row}:C{$row}")->applyFromArray($totalStyle);

        // Border semua data
        $sheet->getStyle("A4:C{$row}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Column width
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(15);

        // Output
        $writer = new Xlsx($spreadsheet);
        $filename = 'RL311_Gigi_Mulut_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir)) . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit();
    }
}
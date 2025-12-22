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

class RL310Controller extends Controller
{
    ///////////////////////////////////////////////////////////////
    // Laporan RUJUKAN REKAP
    public function laporanRujukanRekap(Request $request){
        // Get input parameters
        $tanggalAwal = $request->input('tanggal_awal') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // Define specialization mapping with empty collections
        $spesialisasiMap = $this->getSpecializationMapping();

        // Initialize total data structure
        $totalData = [
            'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
            'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
            'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
        ];

        // Get rujukan masuk data
        $rujukanMasukData = DB::table('rujuk_masuk')
            ->join('reg_periksa', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('penyakit', 'rujuk_masuk.kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->select(
                'rujuk_masuk.no_rawat',
                'rujuk_masuk.perujuk',
                'rujuk_masuk.alamat',
                'rujuk_masuk.kategori_rujuk',
                'reg_periksa.kd_poli',
                'poliklinik.nm_poli',
                'penyakit.nm_penyakit',
                'rujuk_masuk.kd_penyakit'
            )
            ->get();

        // Process rujukan masuk data for "Diterima Dari"
        foreach ($rujukanMasukData as $data) {
            
            // Determine the category for diterima_dari
            $category = 'puskesmas'; // Default category

            if (preg_match('/klinik|poskes/i', $data->perujuk)) {
                $category = 'faskes_lain';

            } elseif (preg_match('/rsud/i', $data->perujuk) && !preg_match('/rsud.*kotabaru/i', $data->perujuk)) {
                $category = 'rs_lain';
            } elseif (preg_match('/poli/i', $data->perujuk) || preg_match('/rsud.*kotabaru/i', $data->perujuk)) {
                // Skip entries with "Poli" in the name
                continue;
            }
            // Determine specialization based on data
            $spesialisasi = $this->determineSpecialization($data, $spesialisasiMap);
            //if($data->no_rawat == '2025/07/07/000260') throw new \Exception(json_encode($spesialisasiMap));
            // Increment counter for diterima dari
            $spesialisasiMap[$spesialisasi]['data']['diterima_dari'][$category]['value']++;
            $totalData['diterima_dari'][$category]['value']++;
            $spesialisasiMap[$spesialisasi]['data']['diterima_dari']['all']['value']++;
            $totalData['diterima_dari']['all']['value']++;

            // Add kd_poli to array if not exists
            if (!in_array($data->kd_poli, $spesialisasiMap[$spesialisasi]['data']['diterima_dari'][$category]['kode_poli'])) {
                $spesialisasiMap[$spesialisasi]['data']['diterima_dari'][$category]['kode_poli'][] = $data->kd_poli;
            }

            // Add kd_poli to total data
            if (!in_array($data->kd_poli, $totalData['diterima_dari'][$category]['kode_poli'])) {
                $totalData['diterima_dari'][$category]['kode_poli'][] = $data->kd_poli;
            }
        }

        // Get rujukan keluar data dengan diagnosa dari diagnosa_pasien
        $rujukanKeluarData = DB::table('rujuk')
            ->join('reg_periksa', 'rujuk.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join(DB::raw('(SELECT no_rawat, MIN(prioritas) as min_prioritas FROM diagnosa_pasien GROUP BY no_rawat) as dp'), 'rujuk.no_rawat', '=', 'dp.no_rawat')
            ->join('diagnosa_pasien', function($join) {
                $join->on('rujuk.no_rawat', '=', 'diagnosa_pasien.no_rawat')
                     ->on('dp.min_prioritas', '=', 'diagnosa_pasien.prioritas');
            })
            ->leftJoin('penyakit', 'diagnosa_pasien.kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->select(
                'rujuk.no_rawat',
                'rujuk.rujuk_ke',
                'rujuk.kat_rujuk',
                'reg_periksa.kd_poli',
                'diagnosa_pasien.kd_penyakit',
                'penyakit.nm_penyakit'
            )
            ->get();

        // Match rujukan keluar with rujukan masuk to identify "dikembalikan ke"
        foreach ($rujukanKeluarData as $keluarData) {
            // Try to find matching rujukan masuk
            $matchingMasuk = $rujukanMasukData->where('no_rawat', $keluarData->no_rawat)->first();

            // Jika ada rujukan masuk sebagai perujuk awal
            if ($matchingMasuk) {
                // Determine if it is a return referral (dikembalikan ke)
                if ($keluarData->rujuk_ke == $matchingMasuk->perujuk) {
                    // This is a return referral (dikembalikan ke)
                    $returnCategory = 'faskes_asal'; // Default category
                    if (preg_match('/puskesmas/i', $keluarData->rujuk_ke)) {
                        $returnCategory = 'puskesmas';
                    } elseif (preg_match('/rs/i', $keluarData->rujuk_ke)) {
                        $returnCategory = 'rs_asal';
                    }

                    // Determine specialization based on the matching masuk data
                    $spesialisasi = $this->determineSpecialization($keluarData, $spesialisasiMap);

                    // Increment counter for dikembalikan ke
                    $spesialisasiMap[$spesialisasi]['data']['dikembalikan_ke'][$returnCategory]['value']++;
                    $totalData['dikembalikan_ke'][$returnCategory]['value']++;

                    // Add kd_poli to array if not exists
                    if (!in_array($keluarData->kd_poli, $spesialisasiMap[$spesialisasi]['data']['dikembalikan_ke'][$returnCategory]['kode_poli'])) {
                        $spesialisasiMap[$spesialisasi]['data']['dikembalikan_ke'][$returnCategory]['kode_poli'][] = $keluarData->kd_poli;
                    }

                    // Add kd_poli to total data
                    if (!in_array($keluarData->kd_poli, $totalData['dikembalikan_ke'][$returnCategory]['kode_poli'])) {
                        $totalData['dikembalikan_ke'][$returnCategory]['kode_poli'][] = $keluarData->kd_poli;
                    }
                } else {
                    // This is a referral to another place (dirujuk keluar)
                    
                    // Determine specialization based on the matching masuk data
                    $spesialisasi = $this->determineSpecialization($keluarData, $spesialisasiMap);
                    


                    // Increment counter for dirujuk keluar
                    $spesialisasiMap[$spesialisasi]['data']['dirujuk_keluar']['all']['value']++;
                    $totalData['dirujuk_keluar']['all']['value']++;

                    //if($keluarData->no_rawat == '2025/07/02/000246') throw new \Exception(json_encode($keluarData));

                    // Add kd_poli to array if not exists
                    if (!in_array($keluarData->kd_poli, $spesialisasiMap[$spesialisasi]['data']['dirujuk_keluar']['all']['kode_poli'])) {
                        $spesialisasiMap[$spesialisasi]['data']['dirujuk_keluar']['all']['kode_poli'][] = $keluarData->kd_poli;
                       
                    }

                    // Add kd_poli to total data
                    if (!in_array($keluarData->kd_poli, $totalData['dirujuk_keluar']['all']['kode_poli'])) {
                        $totalData['dirujuk_keluar']['all']['kode_poli'][] = $keluarData->kd_poli;
                    }
                }
            } else { 
              // Jika tidak ada , maka rujukan keluar biasa  
              // Determine specialization based on the matching 
                $spesialisasi = $this->determineSpecialization($keluarData, $spesialisasiMap);

                // Increment counter for dirujuk keluar
                $spesialisasiMap[$spesialisasi]['data']['dirujuk_keluar']['all']['value']++;
                $totalData['dirujuk_keluar']['all']['value']++;

                // Add kd_poli to array if not exists
                if (!in_array($keluarData->kd_poli, $spesialisasiMap[$spesialisasi]['data']['dirujuk_keluar']['all']['kode_poli'])) {
                    $spesialisasiMap[$spesialisasi]['data']['dirujuk_keluar']['all']['kode_poli'][] = $keluarData->kd_poli;
                }

                // Add kd_poli to total data
                if (!in_array($keluarData->kd_poli, $totalData['dirujuk_keluar']['all']['kode_poli'])) {
                    $totalData['dirujuk_keluar']['all']['kode_poli'][] = $keluarData->kd_poli;
                }
            }
        }

        // Konversi array kode_poli menjadi string
        foreach ($spesialisasiMap as &$spec) {
            foreach (['diterima_dari', 'dikembalikan_ke', 'dirujuk_keluar'] as $mainCategory) {
                foreach ($spec['data'][$mainCategory] as &$category) {
                    $category['kode_poli_str'] = implode(',', $category['kode_poli']);
                }
            }
        }

        // Lakukan hal yang sama untuk totalData
        foreach (['diterima_dari', 'dikembalikan_ke', 'dirujuk_keluar'] as $mainCategory) {
            foreach ($totalData[$mainCategory] as &$category) {
                $category['kode_poli_str'] = implode(',', $category['kode_poli']);
            }
        }

        // Convert the map to a sequential array for the view
        $spesialisasi = array_values($spesialisasiMap);
        $hospitalInfo = DB::table('setting')->first();
    
        // Add this at the end before return view
        if ($request->has('download_pdf')) {
            return $this->generateRujukanRekapPDF($tanggalAwal, $tanggalAkhir, $spesialisasi, $totalData, $hospitalInfo);
        }

        return view('rm.laporan_rm.rl310_rujukan_rekap', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'spesialisasi' => $spesialisasi,
            'totalData' => $totalData,
            'hospitalInfo' => $hospitalInfo
        ]);
    }

    private function generateRujukanRekapPDF($tanggalAwal, $tanggalAkhir, $spesialisasi, $totalData, $hospitalInfo)
    {
        $pdf = PDF::loadView('rm.laporan_rm.rl310_rujukan_rekap_pdf', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'spesialisasi' => $spesialisasi,
            'totalData' => $totalData,
            'hospitalInfo' => $hospitalInfo
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');

        // Options Snappy Laravel
        $pdf->setOption('disable-smart-shrinking', true);
        
        // Generate filename
        $filename = 'Laporan_Rujukan_Rekap_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir)) . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Show the detailed data for a specific rujukan category and source
     */
    public function laporanRujukanRekapDetail(Request $request){
        $category = $request->input('category'); // diterima_dari or dikembalikan_ke
        $source = $request->input('source');     // puskesmas, rs_lain, faskes_lain, etc.
        $kdPoli = $request->input('kd_poli');    // kode poli from the url parameter
        $specKey = $request->input('spec_key');  // Used to identify the specialization
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');
        
        // Get specialization info if spec_key is provided
        $spesialisasiInfo = null;
        $specName = null;
        if ($specKey) {
            $spesialisasiMap = $this->getSpecializationMapping();
            if (isset($spesialisasiMap[$specKey])) {
                $spesialisasiInfo = $spesialisasiMap[$specKey];
                $specName = $spesialisasiInfo['nama'];
            }
        }
        
        // Base query
        if ($category === 'diterima_dari') {
            // Query for "Diterima Dari" category
            $query = DB::table('rujuk_masuk')
                ->join('reg_periksa', 'rujuk_masuk.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                ->leftJoin('penyakit', 'rujuk_masuk.kd_penyakit', '=', 'penyakit.kd_penyakit')
                ->select(
                    'reg_periksa.no_rawat',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    'pasien.jk',
                    'reg_periksa.tgl_registrasi',
                    'rujuk_masuk.perujuk',
                    'rujuk_masuk.alamat',
                    'poliklinik.nm_poli',
                    'reg_periksa.kd_poli',
                    'rujuk_masuk.kd_penyakit',
                    'penyakit.nm_penyakit',
                    'rujuk_masuk.kategori_rujuk',
                    'rujuk_masuk.keterangan'
                )
                ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
                ->whereRaw("rujuk_masuk.perujuk NOT LIKE '%rsud%kotabaru%' and rujuk_masuk.perujuk NOT LIKE 'poli%'");

            // Filter based on source
            switch ($source) {
                case 'puskesmas':
                    $query->whereRaw("rujuk_masuk.perujuk NOT LIKE '%klinik%'")
                        ->whereRaw("rujuk_masuk.perujuk NOT LIKE '%poskes%'")
                        ->whereRaw("rujuk_masuk.perujuk NOT LIKE '%rsud%'")
                        ->whereRaw("rujuk_masuk.perujuk NOT LIKE '%poli%'");
                    //throw new \Exception($query->toSql);
                    break;
                case 'rs_lain':
                    $query->whereRaw("rujuk_masuk.perujuk LIKE '%rsud%'");
                    break;
                case 'faskes_lain':
                    $query->where(function($q) {
                        $q->whereRaw("rujuk_masuk.perujuk LIKE '%klinik%'")
                        ->orWhereRaw("rujuk_masuk.perujuk LIKE '%poskes%'");
                    });
                    //throw new \Exception($query->toSql());
                    break;
            }
            
            // If spec_key is provided, we need to filter by spesialisasi
            if ($specKey) {
                // Get all data first
                $allData = clone $query;
                $allData = $allData->get();
                
                // Filter the data based on the spec_key
                $spesialisasiMap = $this->getSpecializationMapping();
                $filteredData = collect();
                
                foreach ($allData as $item) {
                    
                    $itemSpesialisasi = $this->determineSpecialization($item, $spesialisasiMap);
                    //if($item->no_rawat == '2025/07/07/000260') throw new \Exception(json_encode($spesialisasiMap));
                    if ($itemSpesialisasi === $specKey) {
                        $filteredData->push($item);
                    }
                }
                 
                $data = $filteredData;
            } else {
                // If spec_key is not provided but kd_poli is, filter by kd_poli
                if (!empty($kdPoli)) {
                    $poliArray = explode(',', $kdPoli);
                    $query->whereIn('reg_periksa.kd_poli', $poliArray);
                }
                
                $data = $query->get();
            }
        } else {
            // Query for "Dikembalikan Ke" category
            $query = DB::table('rujuk')
                ->join('reg_periksa', 'rujuk.no_rawat', '=', 'reg_periksa.no_rawat')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                ->leftJoin('rujuk_masuk', 'rujuk.no_rawat', '=', 'rujuk_masuk.no_rawat')
                ->join(DB::raw('(SELECT no_rawat, MIN(prioritas) as min_prioritas FROM diagnosa_pasien GROUP BY no_rawat) as dp'), 'rujuk.no_rawat', '=', 'dp.no_rawat')
                ->join('diagnosa_pasien', function($join) {
                    $join->on('rujuk.no_rawat', '=', 'diagnosa_pasien.no_rawat')
                        ->on('dp.min_prioritas', '=', 'diagnosa_pasien.prioritas');
                })
                ->leftJoin('penyakit as penyakit_diagnosa', 'diagnosa_pasien.kd_penyakit', '=', 'penyakit_diagnosa.kd_penyakit')
                ->select(
                    'reg_periksa.no_rawat',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    'pasien.jk',
                    'reg_periksa.tgl_registrasi',
                    DB::raw('COALESCE(NULLIF(rujuk_masuk.perujuk, ""), "-") as perujuk_asal'),
                    'rujuk.rujuk_ke as tujuan_dirujuk',
                    'poliklinik.nm_poli',
                    'reg_periksa.kd_poli',
                    // Use COALESCE to prioritize rujuk_masuk diagnosis, fallback to diagnosa_pasien
                    'penyakit_diagnosa.nm_penyakit as nm_penyakit',
                    'diagnosa_pasien.kd_penyakit',
                    'rujuk_masuk.kategori_rujuk',
                    'rujuk.keterangan'
                )
                ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir]);    
                //->groupBy(
                //    'reg_periksa.no_rawat',
                //    'reg_periksa.no_rkm_medis',
                //    'pasien.nm_pasien',
                //    'pasien.jk',
                //    'reg_periksa.tgl_registrasi',
                //    'rujuk_masuk.perujuk',
                //    'rujuk.rujuk_ke',
                //    'poliklinik.nm_poli',
                //    'reg_periksa.kd_poli',
                //    'nm_penyakit',
                //    'kd_penyakit',
                //    'rujuk_masuk.kategori_rujuk',
                //    'rujuk.keterangan'
                //);
            
    
            // Filter based on source
            switch ($source) {
                case 'puskesmas':
                    $query->whereRaw("rujuk.rujuk_ke LIKE '%puskesmas%'");
                    break;
                case 'rs_asal':
                    $query->whereRaw("rujuk.rujuk_ke LIKE '%rs%'");
                    break;
                case 'faskes_asal':
                    $query->whereRaw("rujuk.rujuk_ke NOT LIKE '%puskesmas%'")
                        ->whereRaw("rujuk.rujuk_ke NOT LIKE '%rs%'");
                    break;
            }

            // If spec_key is provided, we need to filter by spesialisasi
            if ($specKey) {
                // Get all data first
                $allData = clone $query;
                $allData = $allData->get();
            
                // Filter the data based on the spec_key
                $spesialisasiMap = $this->getSpecializationMapping();
                $filteredData = collect();
            
                foreach ($allData as $item) {
                    // Create a masuk-like object for specialization determination
                    $masukLike = (object) [
                        'kategori_rujuk' => $item->kategori_rujuk,
                        'kd_poli' => $item->kd_poli,
                        'nm_penyakit' => $item->nm_penyakit,
                        'kd_penyakit' => $item->kd_penyakit,
                    ];
                
                    $itemSpesialisasi = $this->determineSpecialization($masukLike, $spesialisasiMap);
                    if ($itemSpesialisasi === $specKey) {
                        $filteredData->push($item);
                    }
                   // if($item->no_rawat == '2025/07/02/000246') throw new \Exception(json_encode($masukLike));
                }
                
                $data = $filteredData;
            } else {
                // If spec_key is not provided but kd_poli is, filter by kd_poli
                if (!empty($kdPoli)) {
                    $poliArray = explode(',', $kdPoli);
                    $query->whereIn('reg_periksa.kd_poli', $poliArray);
                }
            
                $data = $query->get();
            }
        }
        
        return view('rm.laporan_rm.rl310_rujukan_rekap_detail', [
            'data' => $data,
            'category' => $category, 
            'source' => $source,
            'kd_poli' => $kdPoli,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'spesialisasiInfo' => $spesialisasiInfo,
            'specName' => $specName
        ]);
    }

    /**
     * Determine specialization based on referring data
     */
    private function determineSpecialization($data, $spesialisasiMap) {
        if (isset($data->kd_poli) && $data->kd_poli == 'K11') { // K11 Poli Saraf
            $priorityKeys = ['saraf_stroke', 'saraf_non_stroke'];
            $spesialisasiMap = array_intersect_key($spesialisasiMap, array_flip($priorityKeys));
        } else if(isset($data->kd_poli) && $data->kd_poli == 'K4'){
            $priorityKeys = ['ginekologi', 'obstetri', 'keluarga_berencana'];
            $spesialisasiMap = array_intersect_key($spesialisasiMap, array_flip($priorityKeys));
        }

        // Define specializations that prioritize kd_poli over ICD blocks for incoming referrals
        $koliPoliPrioritySpecs = ['penyakit_dalam', 'bedah', 'kesehatan_anak', 'jiwa', 'tht', 'mata', 'gigi_mulut', 'paru'];
        
        // Define specializations that prioritize ICD blocks over kd_poli for incoming referrals  
        $icdPrioritySpecs = ['kesehatan_remaja', 'obstetri', 'ginekologi', 'keluarga_berencana', 'saraf_non_stroke', 'uronefrologi', 'saraf_stroke', 'kulit_kelamin', 'kardiologi', 'kanker'];

        // First try to match by category
        if (isset($data->kategori_rujuk) && $data->kategori_rujuk != '-' && $data->kategori_rujuk != '') {
            foreach ($spesialisasiMap as $key => $spec) {
                if (isset($spec['kategori']) && strtolower($spec['kategori']) == strtolower($data->kategori_rujuk)) {
                    return $key;
                }
            }
        }

        // For kd_poli priority specializations: check kd_poli first, then ICD blocks
        if (array_intersect(array_keys($spesialisasiMap), $koliPoliPrioritySpecs)) {
            // Try to match by poli first for these specializations
            if (isset($data->kd_poli) && $data->kd_poli) {
                foreach ($spesialisasiMap as $key => $spec) {
                    if (in_array($key, $koliPoliPrioritySpecs) && isset($spec['kd_poli']) && in_array($data->kd_poli, $spec['kd_poli'])) {
                        return $key;
                    }
                }
            }
        }

        // Check ICD blocks (for both priority types and general matching)
        if (isset($data->kd_penyakit) && $data->kd_penyakit) {
            $icdBase = substr($data->kd_penyakit, 0, 3);
            
            // Special case for G93.1 (Stroke)
            if ($data->kd_penyakit == 'G93.1') {
                return 'saraf_stroke';
            }
            
            foreach ($spesialisasiMap as $key => $spec) {
                if (isset($spec['icd_blocks'])) {
                    foreach ($spec['icd_blocks'] as $blockRange) {
                        if (strpos($blockRange, '-') !== false) {
                            list($start, $end) = explode('-', $blockRange);
                            
                            $startLetter = substr($start, 0, 1);
                            $endLetter = substr($end, 0, 1);
                            $startNum = intval(substr($start, 1));
                            $endNum = intval(substr($end, 1));
                            
                            $dataLetter = substr($icdBase, 0, 1);
                            $dataNum = intval(substr($icdBase, 1));
                            
                            if ($startLetter === $endLetter) {
                                if ($dataLetter === $startLetter && 
                                    $dataNum >= $startNum && $dataNum <= $endNum) {
                                    return $key;
                                }
                            } else {
                                if ($dataLetter > $startLetter && $dataLetter < $endLetter) {
                                    return $key;
                                } elseif ($dataLetter === $startLetter && $dataNum >= $startNum) {
                                    return $key;
                                } elseif ($dataLetter === $endLetter && $dataNum <= $endNum) {
                                    return $key;
                                }
                            }
                        } else {
                            if ($icdBase == $blockRange || $data->kd_penyakit == $blockRange) {
                                return $key;
                            }
                        }
                    }
                }
            }
        }

        // For ICD priority specializations: check kd_poli after ICD blocks
        if (isset($data->kd_poli) && $data->kd_poli) {
            foreach ($spesialisasiMap as $key => $spec) {
                if (!in_array($key, $koliPoliPrioritySpecs) && isset($spec['kd_poli']) && in_array($data->kd_poli, $spec['kd_poli'])) {
                    return $key;
                }
            }
        }

        // Try to match by diagnosis pattern
        if (isset($data->nm_penyakit) && $data->nm_penyakit) {
            $diagnosis = strtolower($data->nm_penyakit);
            if (stripos($diagnosis, 'stroke') !== false) {
                return 'saraf_stroke';
            }
            foreach ($spesialisasiMap as $key => $spec) {
                if (isset($spec['pattern'])) {
                    foreach ($spec['pattern'] as $pattern) {
                        if (!empty($pattern) && stripos($diagnosis, strtolower($pattern)) !== false) {
                            return $key;
                        }
                    }
                }
            }
        }
        
        // Default to other specialization if no match
        $defaultKey = array_key_first($spesialisasiMap);
        return isset($spesialisasiMap['spesialisasi_lain']) ? 'spesialisasi_lain' : $defaultKey;
    }

    /**
     * Get the complete specialization mapping with default structure
     */
    private function getSpecializationMapping(){
        return [
            'penyakit_dalam' => [
                'key' => 'penyakit_dalam',
                'nama' => 'Penyakit Dalam',
                'kd_poli' => ['INT', 'PDL', 'K9', 'K10'],
                'pattern' => [],
                'icd_blocks' => ['I00-I59', 'E00-E90', 'A00-B99', 'D50-D89'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'bedah' => [
                'key' => 'bedah',
                'nama' => 'Bedah',
                'kd_poli' => ['BED', 'BDH', 'K1', 'K18', 'K20'],
                'kategori' => 'Bedah',
                'icd_blocks' => ['S00-T98', 'C00-D48', 'M00-M99'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'kesehatan_anak' => [
                'key' => 'kesehatan_anak',
                'nama' => 'Kesehatan Anak',
                'kd_poli' => ['ANK', 'KSA', 'K0'],
                'kategori' => 'Anak',
                'icd_blocks' => ['Q00-Q99', 'P00-P96', 'Z00-Z13'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'kesehatan_remaja' => [
                'key' => 'kesehatan_remaja',
                'nama' => 'Kesehatan Remaja',
                'kd_poli' => ['REM', 'KSR'],
                'icd_blocks' => ['Z70-Z76'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'obstetri' => [
                'key' => 'obstetri',
                'nama' => 'Obstetri',
                'kd_poli' => ['OBS'],
                'kategori' => 'Kebidanan',
                'pattern' => [],
                'icd_blocks' => ['O00-O99', 'Z34-Z39'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'ginekologi' => [
                'key' => 'ginekologi',
                'nama' => 'Ginekologi',
                'kd_poli' => ['GYN', 'KDK'],
                'kategori' => 'Kandungan',
                'pattern' => [],
                'icd_blocks' => ['N80-N99', 'C51-C58', 'D25-D28'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'keluarga_berencana' => [
                'key' => 'keluarga_berencana',
                'nama' => 'Keluarga Berencana',
                'kd_poli' => ['KBR'],
                'kategori' => 'KB',
                'pattern' => [],
                'icd_blocks' => ['Z30-Z33'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'saraf_non_stroke' => [
                'key' => 'saraf_non_stroke',
                'nama' => 'Saraf (Non Stroke)',
                'kd_poli' => ['SAR', 'NFL'],
                'pattern' => [],
                'icd_blocks' => ['G00-G99'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'jiwa' => [
                'key' => 'jiwa',
                'nama' => 'Jiwa',
                'kd_poli' => ['JIW', 'PSK', 'K17'],
                'pattern' => [],
                'icd_blocks' => ['F00-F99'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'tht' => [
                'key' => 'tht',
                'nama' => 'THT',
                'kd_poli' => ['THT', 'K7'],
                'pattern' => [],
                'icd_blocks' => ['H60-H95', 'J30-J39'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'mata' => [
                'key' => 'mata',
                'nama' => 'Mata',
                'kd_poli' => ['MAT', 'K6'],
                'pattern' => [],
                'icd_blocks' => ['H00-H59'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'kulit_kelamin' => [
                'key' => 'kulit_kelamin',
                'nama' => 'Kulit dan Kelamin',
                'kd_poli' => ['KLT', 'KKL'],
                'pattern' => [],
                'icd_blocks' => ['L00-L99', 'A50-A64', 'N70-N77'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'gigi_mulut' => [
                'key' => 'gigi_mulut',
                'nama' => 'Gigi dan Mulut',
                'kd_poli' => ['GIG', 'GGM', 'K2', 'K3'],
                'pattern' => [],
                'icd_blocks' => ['K00-K14'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'radiologi' => [
                'key' => 'radiologi',
                'nama' => 'Radiologi',
                'kd_poli' => ['RAD'],
                'pattern' => [],
                'icd_blocks' => [],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'paru' => [
                'key' => 'paru',
                'nama' => 'Paru',
                'kd_poli' => ['PAR', 'PRM', 'K13', 'K8'],
                'pattern' => [],
                'icd_blocks' => ['J00-J99'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'kardiologi' => [
                'key' => 'kardiologi',
                'nama' => 'Kardiologi',
                'kd_poli' => ['KAR', 'JNT'],
                'pattern' => [],
                'icd_blocks' => ['I20-I25', 'I30-I52'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'kanker' => [
                'key' => 'kanker',
                'nama' => 'Kanker',
                'kd_poli' => ['KNK', 'ONK'],
                'pattern' => [],
                'icd_blocks' => ['C00-C97', 'D00-D09', 'D37-D48'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'uronefrologi' => [
                'key' => 'uronefrologi',
                'nama' => 'Uronefrologi',
                'kd_poli' => ['URO', 'GJL'],
                'pattern' => [],
                'icd_blocks' => ['N00-N39', 'N40-N51'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],         
            'saraf_stroke' => [
                'key' => 'saraf_stroke',
                'nama' => 'Saraf (Stroke)',
                'kd_poli' =>  ['STR'],
                'pattern' => [],
                'icd_blocks' => ['I60-I69', 'G93.1'],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
            'spesialisasi_lain' => [
                'key' => 'spesialisasi_lain',
                'nama' => 'Spesialisasi Lain',
                'kd_poli' => ['igd', 'IGDK'],
                'icd_blocks' => [],
                'data' => [
                    'diterima_dari' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_lain' => ['value' => 0, 'kode_poli' => []], 'faskes_lain' => ['value' => 0, 'kode_poli' => []], 'all' => ['value' => 0, 'kode_poli' => []]],
                    'dikembalikan_ke' => ['puskesmas' => ['value' => 0, 'kode_poli' => []], 'rs_asal' => ['value' => 0, 'kode_poli' => []], 'faskes_asal' => ['value' => 0, 'kode_poli' => []]],
                    'dirujuk_keluar' => ['all' => ['value' => 0, 'kode_poli' => []]]
                ]
            ],
        ];
    }

    // Akhir Laporan RUJUKAN REKAP
    ////////////////////////////////////////////////////////////////
}
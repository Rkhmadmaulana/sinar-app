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

class RL35Controller extends Controller
{
    ////////////////////////////////////////////////////////////////
    // Laporan Rekapitulasi Kunjungan
    /**
     * RL 3.5 - Rekapitulasi Kunjungan
     */
    public function rl35Kunjungan(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // Gunakan mapping spesialisasi yang sama dengan rujukan rekap
        $jenisKegiatan = $this->getJenisKegiatanMapping();
        
        $kotabaruKeywords = $this->getKotabaruKeywords();
        $kotabaruCodes = [413, 6550, 11261, 19996, 16551, 17451, 18410, 18542, 18674, 19537, 19611, 20470, 24789];

        // Query dengan data diagnosa
        $query = DB::table('reg_periksa as rp')
            ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
            ->join('poliklinik as pol', 'rp.kd_poli', '=', 'pol.kd_poli')
            ->leftJoin('rujuk_masuk as rm', 'rp.no_rawat', '=', 'rm.no_rawat')
            ->leftJoin(DB::raw('(
                SELECT 
                    no_rawat,
                    MIN(prioritas) AS min_prioritas,
                    MIN(kd_penyakit) AS min_kd_penyakit
                FROM diagnosa_pasien
                GROUP BY no_rawat
            ) AS dp'), 'rp.no_rawat', '=', 'dp.no_rawat')
            ->leftJoin('penyakit', 'dp.min_kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where('rp.status_lanjut', 'Ralan')
            ->select(
                'rp.no_rawat',
                'rp.kd_poli',
                'pol.nm_poli',
                'p.jk',
                'p.kd_kab',
                'rm.perujuk',
                'penyakit.kd_penyakit',
                'penyakit.nm_penyakit',
                'rm.kategori_rujuk'
            )
            ->get();

        // Process each record to determine location and specialization
        $processedData = $query->map(function($item) use ($jenisKegiatan, $kotabaruKeywords, $kotabaruCodes) {
            $item->lokasi_kab = $this->determineLocation($item->perujuk, $item->kd_kab, $kotabaruKeywords, $kotabaruCodes);
            // Tentukan spesialisasi menggunakan fungsi yang sama dengan rujukan rekap
            $item->spesialisasi = $this->determineSpecializationForKunjungan($item, $jenisKegiatan);
            return $item;
        });

        // Count data for each jenis kegiatan
        foreach ($jenisKegiatan as $key => &$jenis) {
            foreach (['Dalam', 'Luar'] as $lokasi) {
                foreach (['L', 'P'] as $gender) {
                    $count = $processedData->filter(function($item) use ($key, $lokasi, $gender) {
                        $matchSpec = $item->spesialisasi === $key;
                        $matchLokasi = $item->lokasi_kab === $lokasi;
                        $matchGender = $item->jk === $gender;
                        return $matchSpec && $matchLokasi && $matchGender;
                    })->count();
                    
                    $jenis['data'][$lokasi . '_' . $gender] = $count;
                }
            }
        }

        $hospitalInfo = DB::table('setting')->first();

        if ($request->has('download_pdf')) {
            return $this->generateRL35PDF($tanggalAwal, $tanggalAkhir, $jenisKegiatan, $hospitalInfo);
        }

        if ($request->has('download_excel')) {
            return $this->generateRL35Excel($tanggalAwal, $tanggalAkhir, $jenisKegiatan, $hospitalInfo);
        }
        
        return view('rm.laporan_rm.rl35_kunjungan', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'jenisKegiatan' => $jenisKegiatan, // Tidak perlu array_values()
            'hospitalInfo' => $hospitalInfo
        ]);
    }

    /**
     * Show the detailed data for a specific specialization in kunjungan rekap
     */
    public function rl35KunjunganDetail(Request $request)
    {
        $specKey = $request->input('spec_key');
        $lokasi = $request->input('lokasi'); // Dalam or Luar
        $gender = $request->input('gender'); // L or P
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');
        
        // Get specialization info if spec_key is provided
        $spesialisasiInfo = null;
        $specName = null;
        if ($specKey) {
            $spesialisasiMap = $this->getJenisKegiatanMapping();
            if (isset($spesialisasiMap[$specKey])) {
                $spesialisasiInfo = $spesialisasiMap[$specKey];
                $specName = $spesialisasiInfo['nama'];
            }
        }
        
        // Get Kotabaru keywords and codes
        $kotabaruKeywords = $this->getKotabaruKeywords();
        $kotabaruCodes = [413, 6550, 11261, 19996, 16551, 17451, 18410, 18542, 18674, 19537, 19611, 20470, 24789];
        
        // Base query with diagnosis data
        $query = DB::table('reg_periksa as rp')
            ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
            ->join('poliklinik as pol', 'rp.kd_poli', '=', 'pol.kd_poli')
            ->leftJoin('rujuk_masuk as rm', 'rp.no_rawat', '=', 'rm.no_rawat')
            ->leftJoin(DB::raw('(
                SELECT 
                    no_rawat,
                    MIN(prioritas) AS min_prioritas,
                    MIN(kd_penyakit) AS min_kd_penyakit
                FROM diagnosa_pasien
                GROUP BY no_rawat
            ) AS dp'), 'rp.no_rawat', '=', 'dp.no_rawat')
            ->leftJoin('penyakit', 'dp.min_kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where('rp.status_lanjut', 'Ralan')
            ->select(
                'rp.no_rawat',
                'rp.no_rkm_medis',
                'p.nm_pasien',
                'p.jk',
                'rp.tgl_registrasi',
                'rm.perujuk',
                'rm.alamat',
                'pol.nm_poli',
                'rp.kd_poli',
                'penyakit.kd_penyakit',
                'penyakit.nm_penyakit',
                'p.kd_kab'
            );
        
        // Get all data first
        $allData = $query->get();
        
        // Filter the data based on the spec_key
        $spesialisasiMap = $this->getJenisKegiatanMapping();
        $filteredData = collect();
        
        foreach ($allData as $item) {
            // Determine location
            $itemLokasi = $this->determineLocation($item->perujuk, $item->kd_kab, $kotabaruKeywords, $kotabaruCodes);
            
            // Determine specialization
            $itemSpesialisasi = $this->determineSpecializationForKunjungan($item, $spesialisasiMap);
            
            // Apply filters
            $matchSpec = ($specKey === null) || ($itemSpesialisasi === $specKey);
            $matchLokasi = ($lokasi === null) || ($itemLokasi === $lokasi);
            $matchGender = ($gender === null) || ($item->jk === $gender);
            
            if ($matchSpec && $matchLokasi && $matchGender) {
                $item->lokasi_kab = $itemLokasi;
                $item->spesialisasi = $itemSpesialisasi;
                $filteredData->push($item);
            }
        }
        
        return view('rm.laporan_rm.rl35_kunjungan_detail', [
            'data' => $filteredData,
            'specKey' => $specKey,
            'lokasi' => $lokasi,
            'gender' => $gender,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'spesialisasiInfo' => $spesialisasiInfo,
            'specName' => $specName
        ]);
    }

    /**
     * Get Kotabaru keywords for location detection
     */
    private function getKotabaruKeywords()
    {
        return [
            // Kabupaten name variations
            'kotabaru', 'kota baru', 'kotabarukab', 'kab kotabaru', 'kab. kotabaru',
            
            // 22 Kecamatan di Kabupaten Kotabaru
            'hampang', 'kelumpang barat', 'kelumpang hilir', 'kelumpang hulu', 
            'kelumpang selatan', 'kelumpang tengah', 'kelumpang utara',
            'pamukan barat', 'pamukan selatan', 'pamukan utara',
            'pulau laut barat', 'pulau laut kepulauan', 'pulau laut selatan', 
            'pulau laut sigam', 'pulau laut tanjung selayar', 'pulau laut tengah', 
            'pulau laut timur', 'pulau laut utara',
            'pulau sembilan', 'pulau sebuku', 'sampanahan', 'sungai durian',
            
            // Desa/Kelurahan terkenal
            'sebatung', 'cantung', 'bangkalaan', 'bangkalan', 'sampanahan',
            'manunggul', 'cengal', 'dirgahayu', 'stagen', 'batuah',
            'gunung ulin', 'lontar', 'semaras', 'betung', 'bepara',
            'kerayaan', 'buntar laut', 'magalau', 'serongga', 'tarjun',
            'pudi', 'senakin', 'sebuku', 'laut pulau', 'pulau laut'
        ];
    }

    /**
     * Determine if patient is from Dalam or Luar Kabupaten
     */
    private function determineLocation($perujuk, $kd_kab, $kotabaruKeywords, $kotabaruCodes)
    {
        // Priority 1: Check rujuk_masuk perujuk
        if ($perujuk && $perujuk != '-') {
            $perujukLower = strtolower($perujuk);
            
            // Check if perujuk contains any Kotabaru keyword
            foreach ($kotabaruKeywords as $keyword) {
                if (stripos($perujukLower, $keyword) !== false) {
                    return 'Dalam';
                }
            }
            
            // If perujuk exists but doesn't match Kotabaru keywords, it's Luar
            return 'Luar';
        }
        
        // Priority 2: Check from pasien.kd_kab
        if (in_array($kd_kab, $kotabaruCodes)) {
            return 'Dalam';
        }
        
        // Default: Luar Kabupaten
        return 'Luar';
    }

    private function getJenisKegiatanMapping()
    {
        return [
            'penyakit_dalam' => [
                'nama' => 'Penyakit Dalam',
                'kd_poli' => ['INT', 'PDL', 'K9', 'K10'],
                'icd_blocks' => ['I00-I59', 'E00-E89', 'A00-B99', 'D50-D89'],
                'data' => []
            ],
            'bedah' => [
                'nama' => 'Bedah',
                'kd_poli' => ['BED', 'BDH', 'K1', 'K18'], 
                'icd_blocks' => ['S00-T98', 'C00-D48'],
                'data' => []
            ],
            'kesehatan_anak_neonatal' => [
                'nama' => 'Kesehatan Anak (Neonatal)',
                'kd_poli' => ['ANK', 'KSA', 'K0'],
                'icd_blocks' => ['P00-P96'],
                'data' => []
            ],
            'kesehatan_anak_lainnya' => [
                'nama' => 'Kesehatan Anak (Lainnya)',
                'kd_poli' => ['ANK', 'KSA', 'K0'],
                'icd_blocks' => ['Q00-Q99'],
                'data' => []
            ],
            'obstetri_ibu_hamil' => [
                'nama' => 'Obstetri (Ibu Hamil)',
                'kd_poli' => ['OBS', 'K4'],
                'icd_blocks' => ['O00-O99', 'Z34-Z39'],
                'data' => []
            ],
            'ginekologi' => [
                'nama' => 'Ginekologi',
                'kd_poli' => ['GYN', 'KDK'],
                'icd_blocks' => ['N80-N99', 'C51-C58', 'D25-D28'],
                'data' => []
            ],
            'keluarga_berencana' => [
                'nama' => 'Keluarga Berencana',
                'kd_poli' => ['KBR'],
                'icd_blocks' => ['Z30-Z33'],
                'data' => []
            ],
            'jiwa' => [
                'nama' => 'Jiwa',
                'kd_poli' => ['JIW', 'PSK', 'K17'],
                'icd_blocks' => ['F00-F99'],
                'data' => []
            ],
            'napza' => [
                'nama' => 'Napza',
                'kd_poli' => ['NPZ'],
                'icd_blocks' => ['F10-F19'],
                'data' => []
            ],
            'psikologi' => [
                'nama' => 'Psikologi',
                'kd_poli' => ['PSI'],
                'icd_blocks' => ['F40-F48'],
                'data' => []
            ],
            'tht' => [
                'nama' => 'THT',
                'kd_poli' => ['THT', 'K7'],
                'icd_blocks' => ['H60-H95', 'J30-J39'],
                'data' => []
            ],
            'mata' => [
                'nama' => 'Mata',
                'kd_poli' => ['MAT', 'K6'],
                'icd_blocks' => ['H00-H59'],
                'data' => []
            ],
            'kulit_kelamin' => [
                'nama' => 'Kulit dan Kelamin',
                'kd_poli' => ['KLT', 'KKL'],
                'icd_blocks' => [], //['L00-L99', 'A50-A64', 'N70-N77'],
                'data' => []
            ],
            'gigi_mulut' => [
                'nama' => 'Gigi & Mulut',
                'kd_poli' => ['GIG', 'GGM', 'K2', 'K3'],
                'icd_blocks' => ['K00-K14'],
                'data' => []
            ],
            'geriatri' => [
                'nama' => 'Geriatri',
                'kd_poli' => ['GER'],
                'icd_blocks' => [],
                'data' => []
            ],
            'kardiologi' => [
                'nama' => 'Kardiologi',
                'kd_poli' => ['KAR', 'JNT'],
                'icd_blocks' => ['I20-I25', 'I30-I52'],
                'data' => []
            ],
            'radiologi' => [
                'nama' => 'Radiologi',
                'kd_poli' => ['RAD'],
                'icd_blocks' => [],
                'data' => []
            ],
            'bedah_orthopedi' => [
                'nama' => 'Bedah Orthopedi',
                'kd_poli' => ['K20'],
                'icd_blocks' => ['M00-M99'],
                'data' => []
            ],
            'paru' => [
                'nama' => 'Paru-Paru',
                'kd_poli' => ['PAR', 'PRM', 'K13', 'K8'],
                'icd_blocks' => ['J00-J29', 'J40-J99'],
                'data' => []
            ],
            'kanker' => [
                'nama' => 'Kanker',
                'kd_poli' => ['KNK', 'ONK'],
                'icd_blocks' => ['C00-C97', 'D00-D09', 'D37-D48'],
                'data' => []
            ],
            'uronefrologi' => [
                'nama' => 'Uronefrologi',
                'kd_poli' => ['URO', 'GJL'],
                'icd_blocks' => ['N00-N39', 'N40-N51'],
                'data' => []
            ],
            'kusta' => [
                'nama' => 'Kusta',
                'kd_poli' => ['KUS'],
                'icd_blocks' => ['A30-A49'],
                'data' => []
            ],
            'umum' => [
                'nama' => 'Umum',
                'kd_poli' => ['UMU', 'UMM'],
                'icd_blocks' => [],
                'data' => []
            ],
            'rawat_darurat' => [
                'nama' => 'Rawat Darurat',
                'kd_poli' => ['igd', 'IGDK'],
                'icd_blocks' => [],
                'data' => []
            ],
            'rehabilitasi_medik' => [
                'nama' => 'Rehabilitasi Medik',
                'kd_poli' => ['K14'],
                'icd_blocks' => ['Z40-Z53'],
                'data' => []
            ],
            'akupunktur_medik' => [
                'nama' => 'Akupunktur Medik',
                'kd_poli' => ['AKU'],
                'icd_blocks' => ['M79'],
                'data' => []
            ],
            'konsultasi_gizi' => [
                'nama' => 'Konsultasi Gizi',
                'kd_poli' => ['K21'],
                'icd_blocks' => ['E40-E46', 'E50-E64'],
                'data' => []
            ],
            'day_care' => [
                'nama' => 'Day Care',
                'kd_poli' => ['DAY'],
                'icd_blocks' => [],
                'data' => []
            ],
            'medical_checkup' => [
                'nama' => 'Medical Check Up',
                'kd_poli' => ['MCU'],
                'icd_blocks' => [],
                'data' => []
            ],
            'bedah_saraf_stroke' => [
                'nama' => 'Bedah Saraf (Stroke)',
                'kd_poli' => ['STR'],
                'icd_blocks' => [],
                'data' => []
            ],
            'bedah_saraf_lainnya' => [
                'nama' => 'Bedah Saraf (Lainnya)',
                'kd_poli' => ['SAR', 'NFL'],
                'icd_blocks' => [],
                'data' => []
            ],
            'saraf_stroke' => [
                'nama' => 'Saraf (Stroke)',
                'kd_poli' => ['STR', 'K11'],
                'icd_blocks' => ['I60-I69'],
                'data' => []
            ],
            'saraf_lainnya' => [
                'nama' => 'Saraf (Lainnya)',
                'kd_poli' => ['SAR', 'NFL', 'K11'],
                'icd_blocks' => ['G00-G99', 'R20-R29'],
                'data' => []
            ],
            'lain_lain' => [
                'nama' => 'Lain - Lain',
                'kd_poli' => ['-', '- - -', 'K22'],
                'icd_blocks' => ['R00-R19','R30-R99'],
                'data' => []
            ]
        ];
    }

    /**
     * Determine specialization based on patient data for kunjungan rekap
     * Uses HYBRID priority: kd_poli + ICD blocks combination
     */
    private function determineSpecializationForKunjungan($data, $spesialisasiMap) {
        
        if (isset($data->kd_poli) and $data->kd_poli == "K22"){ // K22 = VCT
            return 'lain_lain';
        }
        // ========================================
        // PHASE 1: SPECIAL CASES WITH SAME KD_POLI
        // ========================================
        // These require checking BOTH kd_poli AND ICD blocks together
        
        // 1. POLI ANAK (ANK/KSA/K0) - Neonatal vs Lainnya
        if (isset($data->kd_poli) && in_array($data->kd_poli, ['ANK', 'KSA', 'K0'])) {
            if (isset($data->kd_penyakit) && $data->kd_penyakit) {
                $icdBase = substr($data->kd_penyakit, 0, 3);
                
                // Check if P00-P96 (Neonatal)
                if (substr($icdBase, 0, 1) == 'P') {
                    $num = intval(substr($icdBase, 1));
                    if ($num >= 0 && $num <= 96) {
                        return 'kesehatan_anak_neonatal';
                    }
                }
                
                // Check if Q00-Q99 (Congenital)
                if (substr($icdBase, 0, 1) == 'Q') {
                    return 'kesehatan_anak_lainnya';
                }
            }
            
            // Default: if no ICD or unclear, use neonatal for very young, otherwise lainnya
            // You can add age checking here if available
            return 'kesehatan_anak_lainnya'; // Default to lainnya
        }
        
        // 2. POLI K4 (OB/GYN) - Obstetri vs Ginekologi
        if (isset($data->kd_poli) && $data->kd_poli == 'K4') {
            if (isset($data->kd_penyakit) && $data->kd_penyakit) {
                $icdBase = substr($data->kd_penyakit, 0, 3);
                
                // Check if O00-O99 or Z34-Z39 (Obstetri)
                if (substr($icdBase, 0, 1) == 'O') {
                    return 'obstetri_ibu_hamil';
                }
                
                if (substr($icdBase, 0, 1) == 'Z') {
                    $num = intval(substr($icdBase, 1));
                    if ($num >= 34 && $num <= 39) {
                        return 'obstetri_ibu_hamil';
                    }
                    if ($num >= 30 && $num <= 33) {
                        return 'keluarga_berencana';
                    }
                }
                
                // Check if N80-N99 or C51-C58 or D25-D28 (Ginekologi)
                if (substr($icdBase, 0, 1) == 'N') {
                    $num = intval(substr($icdBase, 1));
                    if ($num >= 80 && $num <= 99) {
                        return 'ginekologi';
                    }
                }
                
                if (substr($icdBase, 0, 1) == 'C') {
                    $num = intval(substr($icdBase, 1));
                    if ($num >= 51 && $num <= 58) {
                        return 'ginekologi';
                    }
                }
                
                if (substr($icdBase, 0, 1) == 'D') {
                    $num = intval(substr($icdBase, 1));
                    if ($num >= 25 && $num <= 28) {
                        return 'ginekologi';
                    }
                }
            }
            
            // Default to obstetri if unclear
            return 'obstetri_ibu_hamil';
        }
        
        // 3. POLI STR (Stroke) - Bedah Saraf vs Saraf
        if (isset($data->kd_poli) && in_array($data->kd_poli, ['STR', 'K11'])) {
            if (isset($data->kd_penyakit) && $data->kd_penyakit) {
                $icdBase = substr($data->kd_penyakit, 0, 3);
                
                // Check if I60-I69 or G93.1 (Stroke - bisa bedah atau non-bedah)
                if ($data->kd_penyakit == 'G93.1' || 
                    (substr($icdBase, 0, 1) == 'I' && intval(substr($icdBase, 1)) >= 60 && intval(substr($icdBase, 1)) <= 69)) {
                    
                    // TODO: Check if there's surgery procedure to differentiate
                    // For now, default to non-surgical (saraf_stroke)
                    // You can add logic here to check procedure codes if available
                    return 'saraf_stroke';
                }
            }
            //return 'saraf_stroke';
        }
        
        // 4. POLI SAR/NFL (Neuro) - Bedah Saraf vs Saraf
        if (isset($data->kd_poli) && in_array($data->kd_poli, ['SAR', 'NFL', 'K11'])) {
            if (isset($data->kd_penyakit) && $data->kd_penyakit) {
                $icdBase = substr($data->kd_penyakit, 0, 3);
                
                // G00-G99 codes - neurological diseases
                if (substr($icdBase, 0, 1) == 'G') {
                    // TODO: Check procedure codes to determine if surgery
                    // For now, default to non-surgical
                    return 'saraf_lainnya';
                }
                
                // M codes (musculoskeletal with neuro component)
                if (substr($icdBase, 0, 1) == 'M') {
                    return 'saraf_lainnya';
                }
                
                // H codes (vestibular)
                if (substr($icdBase, 0, 1) == 'H') {
                    return 'saraf_lainnya';
                }

                if (substr($icdBase, 0, 1) == 'R') {
                    $num = intval(substr($icdBase, 1));
                    if ($num >= 20 && $num <= 29) {
                        return 'saraf_lainnya';
                    }else{
                        return 'saraf_stroke';
                    }
                }

                // L codes - likely WRONG diagnosis
                if (substr($icdBase, 0, 1) == 'L') {
                    // Flag as potential data entry error
                    return 'saraf_lainnya';
                }
            }
           // return 'saraf_lainnya';
        }
        
        // ========================================
        // PHASE 2: STANDARD KD_POLI MATCHING
        // ========================================
        // For specializations with unique kd_poli codes
        
        if (isset($data->kd_poli) && $data->kd_poli) {
            // Define specializations with unique kd_poli (no conflicts)
            $uniquePoliSpecs = [
                'penyakit_dalam', 'bedah', 'jiwa', 'napza', 'psikologi', 
                'tht', 'mata', 'kulit_kelamin', 'gigi_mulut', 'geriatri',
                'kardiologi', 'radiologi', 'bedah_orthopedi', 'paru',
                'kanker', 'uronefrologi', 'kusta', 'umum', 'rawat_darurat',
                'rehabilitasi_medik', 'akupunktur_medik', 'konsultasi_gizi',
                'day_care', 'medical_checkup', 'ginekologi', 'keluarga_berencana'
            ];
            
            foreach ($spesialisasiMap as $key => $spec) {
                if (in_array($key, $uniquePoliSpecs) && 
                    isset($spec['kd_poli']) && 
                    in_array($data->kd_poli, $spec['kd_poli'])) {
                    return $key;
                }
            }
        }
        
        // ========================================
        // PHASE 3: ICD BLOCKS MATCHING (FALLBACK)
        // ========================================
        // If no kd_poli match, check ICD blocks
        
        if (isset($data->kd_penyakit) && $data->kd_penyakit) {
            $icdBase = substr($data->kd_penyakit, 0, 3);
            
            foreach ($spesialisasiMap as $key => $spec) {
                if (isset($spec['icd_blocks']) && !empty($spec['icd_blocks'])) {
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
                            // Exact match (like 'M79', 'G93.1')
                            if ($icdBase == $blockRange || $data->kd_penyakit == $blockRange) {
                                return $key;
                            }
                        }
                    }
                }
            }
        }
        
        // ========================================
        // PHASE 4: DIAGNOSIS NAME PATTERN MATCHING
        // ========================================
        
        if (isset($data->nm_penyakit) && $data->nm_penyakit) {
            $diagnosis = strtolower($data->nm_penyakit);
            
            // Check for stroke in diagnosis name
            if (stripos($diagnosis, 'stroke') !== false) {
                return 'saraf_stroke';
            }
            
            // Check for neonatal patterns
            if (stripos($diagnosis, 'neonatal') !== false || 
                stripos($diagnosis, 'newborn') !== false ||
                stripos($diagnosis, 'perinatal') !== false) {
                return 'kesehatan_anak_neonatal';
            }
            
            // Check for pregnancy/obstetric patterns
            if (stripos($diagnosis, 'pregnan') !== false || 
                stripos($diagnosis, 'gravid') !== false ||
                stripos($diagnosis, 'hamil') !== false ||
                stripos($diagnosis, 'antenatal') !== false) {
                return 'obstetri_ibu_hamil';
            }
            
            // Check other patterns from spesialisasiMap
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
        
        // ========================================
        // PHASE 5: DEFAULT FALLBACK
        // ========================================
        
        return 'lain_lain';
    }

    private function generateRL35PDF($tanggalAwal, $tanggalAkhir, $jenisKegiatan, $hospitalInfo)
    {
        $pdf = PDF::loadView('rm.laporan_rm.rl35_kunjungan_pdf', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'jenisKegiatan' => array_values($jenisKegiatan),
            'hospitalInfo' => $hospitalInfo
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('disable-smart-shrinking', true);
        
        $filename = 'rl35_Kunjungan_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir)) . '.pdf';
        
        return $pdf->download($filename);
    }

    private function generateRL35Excel($tanggalAwal, $tanggalAkhir, $jenisKegiatan, $hospitalInfo)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('RL 3.5');
        
        // Header information with enhanced styling
        $sheet->setCellValue('A1', 'RL 3.5 - REKAPITULASI KUNJUNGAN');
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($tanggalAwal)) . ' - ' . date('d/m/Y', strtotime($tanggalAkhir)));
        
        // Merge cells for title
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        
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
        
        $sheet->getStyle('A1:G1')->applyFromArray($titleStyle);
        $sheet->getStyle('A2:G2')->applyFromArray($periodStyle);
        
        // Set row heights for title
        $sheet->getRowDimension(1)->setRowHeight(35);
        $sheet->getRowDimension(2)->setRowHeight(25);
        
        // Add empty row for spacing
        $sheet->getRowDimension(3)->setRowHeight(10);
        
        // Table headers
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Jenis Kegiatan');
        $sheet->setCellValue('C4', 'Kunjungan Pasien Dalam Kab/Kota');
        $sheet->mergeCells('C4:D4');
        $sheet->setCellValue('E4', 'Kunjungan Pasien Luar Kab/Kota');
        $sheet->mergeCells('E4:F4');
        $sheet->setCellValue('G4', 'Total Kunjungan');
        
        $sheet->setCellValue('C5', 'Laki-laki');
        $sheet->setCellValue('D5', 'Perempuan');
        $sheet->setCellValue('E5', 'Laki-laki');
        $sheet->setCellValue('F5', 'Perempuan');
        
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
                'vertical' => Alignment::VERTICAL_CENTER
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
        $sheet->getStyle('A4:G4')->applyFromArray($mainHeaderStyle);
        $sheet->getStyle('A5:G5')->applyFromArray($subHeaderStyle);
        
        // Data rows
        $row = 6;
        $no = 1;
        foreach (array_values($jenisKegiatan) as $jenis) {
            $dalam_L = $jenis['data']['Dalam_L'] ?? 0;
            $dalam_P = $jenis['data']['Dalam_P'] ?? 0;
            $luar_L = $jenis['data']['Luar_L'] ?? 0;
            $luar_P = $jenis['data']['Luar_P'] ?? 0;
            $total = $dalam_L + $dalam_P + $luar_L + $luar_P;
            
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $jenis['nama']);
            $sheet->setCellValue('C' . $row, $dalam_L);
            $sheet->setCellValue('D' . $row, $dalam_P);
            $sheet->setCellValue('E' . $row, $luar_L);
            $sheet->setCellValue('F' . $row, $luar_P);
            $sheet->setCellValue('G' . $row, $total);
            $row++;
        }
        
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
        for ($i = 6; $i < $row; $i++) {
            if (($i - 6) % 2 == 0) {
                // Even rows - white background
                $evenRowStyle = array_merge($dataStyle, [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID, 
                        'startColor' => ['rgb' => 'FFFFFF']
                    ]
                ]);
                $sheet->getStyle('A' . $i . ':G' . $i)->applyFromArray($evenRowStyle);
            } else {
                // Odd rows - light gray background
                $oddRowStyle = array_merge($dataStyle, [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID, 
                        'startColor' => ['rgb' => 'F8F9FA']
                    ]
                ]);
                $sheet->getStyle('A' . $i . ':G' . $i)->applyFromArray($oddRowStyle);
            }
        }
        
        // Special styling for Jenis Kegiatan column (B) - left alignment with wrap text
        $jenisKegiatanStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('B6:B' . ($row-1))->applyFromArray($jenisKegiatanStyle);
        
        // Enhanced total column styling
        $totalStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID, 
                'startColor' => ['rgb' => 'D5EDDB']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '27AE60']
                ]
            ],
            'font' => ['bold' => true, 'color' => ['rgb' => '1B4F3C'], 'size' => 11]
        ];
        $sheet->getStyle('G6:G' . ($row-1))->applyFromArray($totalStyle);
        
        // Set column dimensions
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(18);
        
        // Set row heights
        $sheet->getRowDimension(4)->setRowHeight(30);
        $sheet->getRowDimension(5)->setRowHeight(25);
        
        // Set data row heights
        for ($i = 6; $i < $row; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(20);
        }
        
        // Add footer with hospital info
        //$footerRow = $row + 2;
        //$sheet->mergeCells('A' . $footerRow . ':G' . $footerRow);
        //$sheet->setCellValue('A' . $footerRow, $hospitalInfo['name'] ? $hospitalInfo['name'] : 'Rumah Sakit');
        //$sheet->setCellValue('A' . $footerRow,  'Rumah Sakit');
        
        //$footerStyle = [
        //    'font' => [
        //        'bold' => true, 
        //        'size' => 10,
        //        'color' => ['rgb' => '2E4053']
        //    ],
        //    'alignment' => [
        //        'horizontal' => Alignment::HORIZONTAL_CENTER,
        //        'vertical' => Alignment::VERTICAL_CENTER
        //    ],
        //    'fill' => [
        //        'fillType' => Fill::FILL_SOLID, 
        //        'startColor' => ['rgb' => 'EBF5FB']
        //    ],
        //];
        //
        //$sheet->getStyle('A' . $footerRow . ':G' . $footerRow)->applyFromArray($footerStyle);
        //$sheet->getRowDimension($footerRow)->setRowHeight(25);
        
        // Set autofilter
        $sheet->setAutoFilter('A5:G' . ($row-1));
        
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        
        $filename = 'rl35_Kunjungan_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir)) . '.xlsx';
        
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
    // Akhir Laporan Rekapitulasi Kunjungan
    ////////////////////////////////////////////////////////////////
}
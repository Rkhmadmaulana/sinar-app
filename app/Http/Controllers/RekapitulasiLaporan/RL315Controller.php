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

class RL315Controller extends Controller
{
    public function laporanRL315(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // Get data dari poliklinik jiwa
        $dataJiwa = $this->getDataKesehatanJiwa($tanggalAwal, $tanggalAkhir);
        
        $hospitalInfo = DB::table('setting')->first();

        if ($request->has('download_pdf')) {
            return $this->generateRL315PDF($tanggalAwal, $tanggalAkhir, $dataJiwa, $hospitalInfo);
        }

        if ($request->has('download_excel')) {
            return $this->generateRL315Excel($tanggalAwal, $tanggalAkhir, $dataJiwa, $hospitalInfo);
        }

        return view('rm.laporan_rm.rl315', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'data' => $dataJiwa,
            'hospitalInfo' => $hospitalInfo
        ]);
    }

    private function getDataKesehatanJiwa($tanggalAwal, $tanggalAkhir)
    {
        // Inisialisasi struktur data sesuai RL 3.15
        $kategori = [
            1 => ['nama' => 'Pemeriksaan Psikiatri', 'laki' => 0, 'perempuan' => 0, 'jumlah' => 0],
            2 => ['nama' => 'Penatalaksanaan Medikamentosa', 'laki' => 0, 'perempuan' => 0, 'jumlah' => 0],
            3 => ['nama' => 'Psikoterapi', 'laki' => 0, 'perempuan' => 0, 'jumlah' => 0],
            4 => ['nama' => 'Konseling', 'laki' => 0, 'perempuan' => 0, 'jumlah' => 0],
            5 => ['nama' => 'Elektro Medik', 'laki' => 0, 'perempuan' => 0, 'jumlah' => 0],
            6 => ['nama' => 'Terapi Perilaku', 'laki' => 0, 'perempuan' => 0, 'jumlah' => 0],
            7 => ['nama' => 'Rehabilitasi Medik Psikiatrik', 'laki' => 0, 'perempuan' => 0, 'jumlah' => 0],
            8 => ['nama' => 'Assessment', 'laki' => 0, 'perempuan' => 0, 'jumlah' => 0],
            99 => ['nama' => 'TOTAL', 'laki' => 0, 'perempuan' => 0, 'jumlah' => 0]
        ];

        // Query data perawatan dari poliklinik jiwa
        // Gabungkan data dari 3 tabel: rawat_jl_dr, rawat_jl_pr, rawat_jl_drpr
        
        // 1. Data dari rawat_jl_dr (dokter)
        $queryDr = DB::table('rawat_jl_dr as rj')
            ->join('reg_periksa as rp', 'rj.no_rawat', '=', 'rp.no_rawat')
            ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
            ->join('poliklinik as pol', 'rp.kd_poli', '=', 'pol.kd_poli')
            ->join('jns_perawatan as jp', 'rj.kd_jenis_prw', '=', 'jp.kd_jenis_prw')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where('pol.nm_poli', 'like', '%jiwa%')
            ->select('jp.nm_perawatan', 'jp.kd_jenis_prw', 'p.jk');

        // 2. Data dari rawat_jl_pr (perawat)
        $queryPr = DB::table('rawat_jl_pr as rj')
            ->join('reg_periksa as rp', 'rj.no_rawat', '=', 'rp.no_rawat')
            ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
            ->join('poliklinik as pol', 'rp.kd_poli', '=', 'pol.kd_poli')
            ->join('jns_perawatan as jp', 'rj.kd_jenis_prw', '=', 'jp.kd_jenis_prw')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where('pol.nm_poli', 'like', '%jiwa%')
            ->select('jp.nm_perawatan', 'jp.kd_jenis_prw', 'p.jk');

        // 3. Data dari rawat_jl_drpr (dokter & perawat)
        $queryDrPr = DB::table('rawat_jl_drpr as rj')
            ->join('reg_periksa as rp', 'rj.no_rawat', '=', 'rp.no_rawat')
            ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
            ->join('poliklinik as pol', 'rp.kd_poli', '=', 'pol.kd_poli')
            ->join('jns_perawatan as jp', 'rj.kd_jenis_prw', '=', 'jp.kd_jenis_prw')
            ->whereBetween('rp.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where('pol.nm_poli', 'like', '%jiwa%')
            ->select('jp.nm_perawatan', 'jp.kd_jenis_prw', 'p.jk');

        // Gabungkan dengan UNION ALL
        $perawatan = $queryDr
            ->unionAll($queryPr)
            ->unionAll($queryDrPr)
            ->get();

        // Mapping perawatan ke kategori
        foreach ($perawatan as $prw) {
            $nmPerawatan = strtolower($prw->nm_perawatan);
            $kdPerawatan = $prw->kd_jenis_prw;
            $jk = strtoupper($prw->jk);
            
            $kategoriIdx = null;
            
            // 1. Pemeriksaan Psikiatri
            if (stripos($nmPerawatan, 'pemeriksaan') !== false && 
                (stripos($nmPerawatan, 'psikiatri') !== false || 
                 stripos($nmPerawatan, 'jiwa') !== false ||
                 stripos($nmPerawatan, 'dokter spesialis') !== false) ||
                in_array($kdPerawatan, ['MED-2_JIWA'])) {
                $kategoriIdx = 1;
            }
            // 2. Penatalaksanaan Medikamentosa (injeksi, obat-obatan)
            elseif (stripos($nmPerawatan, 'injeksi') !== false ||
                    stripos($nmPerawatan, 'medikamentosa') !== false ||
                    stripos($nmPerawatan, 'obat') !== false ||
                    in_array($kdPerawatan, ['MED-306'])) {
                $kategoriIdx = 2;
            }
            // 3. Psikoterapi
            elseif (stripos($nmPerawatan, 'psikoterapi') !== false ||
                    stripos($nmPerawatan, 'psiko terapi') !== false ||
                    in_array($kdPerawatan, ['MED-307', 'MED-308', 'MED-309'])) {
                $kategoriIdx = 3;
            }
            // 4. Konseling
            elseif (stripos($nmPerawatan, 'konseling') !== false ||
                    stripos($nmPerawatan, 'counseling') !== false) {
                $kategoriIdx = 4;
            }
            // 5. Elektro Medik (ECT, dll)
            elseif (stripos($nmPerawatan, 'elektro') !== false ||
                    stripos($nmPerawatan, 'ect') !== false ||
                    stripos($nmPerawatan, 'electro') !== false) {
                $kategoriIdx = 5;
            }
            // 6. Terapi Perilaku
            elseif (stripos($nmPerawatan, 'terapi perilaku') !== false ||
                    stripos($nmPerawatan, 'behavior') !== false ||
                    stripos($nmPerawatan, 'behaviour') !== false) {
                $kategoriIdx = 6;
            }
            // 7. Rehabilitasi Medik Psikiatrik
            elseif (stripos($nmPerawatan, 'rehabilitasi') !== false ||
                    stripos($nmPerawatan, 'rehab') !== false) {
                $kategoriIdx = 7;
            }
            // 8. Assessment (pemeriksaan psikologi, tes, dll)
            elseif (stripos($nmPerawatan, 'assessment') !== false ||
                    stripos($nmPerawatan, 'asesmen') !== false ||
                    stripos($nmPerawatan, 'bebas narkoba') !== false ||
                    stripos($nmPerawatan, 'sehat jiwa') !== false ||
                    stripos($nmPerawatan, 'scl') !== false ||
                    stripos($nmPerawatan, 'mmpi') !== false ||
                    stripos($nmPerawatan, 'ver psychiatricium') !== false ||
                    stripos($nmPerawatan, 'tes') !== false ||
                    stripos($nmPerawatan, 'test') !== false ||
                    in_array($kdPerawatan, ['MED-310', 'MED-311', 'MED-312', 'MED-313'])) {
                $kategoriIdx = 8;
            }

            // Jika kategori ditemukan, tambahkan ke hitungan
            if ($kategoriIdx !== null) {
                if ($jk == 'L') {
                    $kategori[$kategoriIdx]['laki']++;
                } elseif ($jk == 'P') {
                    $kategori[$kategoriIdx]['perempuan']++;
                }
                $kategori[$kategoriIdx]['jumlah']++;
            }
        }

        // Hitung total
        $totalLaki = 0;
        $totalPerempuan = 0;
        $totalJumlah = 0;
        
        for ($i = 1; $i <= 8; $i++) {
            $totalLaki += $kategori[$i]['laki'];
            $totalPerempuan += $kategori[$i]['perempuan'];
            $totalJumlah += $kategori[$i]['jumlah'];
        }
        
        $kategori[99]['laki'] = $totalLaki;
        $kategori[99]['perempuan'] = $totalPerempuan;
        $kategori[99]['jumlah'] = $totalJumlah;

        return $kategori;
    }

    public function laporanRL315Detail(Request $request)
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
            ->where('pol.nm_poli', 'like', '%jiwa%')
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
            ->where('pol.nm_poli', 'like', '%jiwa%')
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
            ->where('pol.nm_poli', 'like', '%jiwa%')
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
            1 => 'Pemeriksaan Psikiatri',
            2 => 'Penatalaksanaan Medikamentosa',
            3 => 'Psikoterapi',
            4 => 'Konseling',
            5 => 'Elektro Medik',
            6 => 'Terapi Perilaku',
            7 => 'Rehabilitasi Medik Psikiatrik',
            8 => 'Assessment'
        ];

        return view('rm.laporan_rm.rl315_detail', [
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
            case 1: // Pemeriksaan Psikiatri
                $query->where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('jp.nm_perawatan', 'like', '%pemeriksaan%')
                           ->where(function($q3) {
                               $q3->where('jp.nm_perawatan', 'like', '%psikiatri%')
                                  ->orWhere('jp.nm_perawatan', 'like', '%jiwa%')
                                  ->orWhere('jp.nm_perawatan', 'like', '%dokter spesialis%');
                           });
                    })->orWhereIn('jp.kd_jenis_prw', ['MED-2_JIWA']);
                });
                break;
            case 2: // Penatalaksanaan Medikamentosa
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%injeksi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%medikamentosa%')
                      ->orWhere('jp.nm_perawatan', 'like', '%obat%')
                      ->orWhereIn('jp.kd_jenis_prw', ['MED-306']);
                });
                break;
            case 3: // Psikoterapi
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%psikoterapi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%psiko terapi%')
                      ->orWhereIn('jp.kd_jenis_prw', ['MED-307', 'MED-308', 'MED-309']);
                });
                break;
            case 4: // Konseling
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%konseling%')
                      ->orWhere('jp.nm_perawatan', 'like', '%counseling%');
                });
                break;
            case 5: // Elektro Medik
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%elektro%')
                      ->orWhere('jp.nm_perawatan', 'like', '%ect%')
                      ->orWhere('jp.nm_perawatan', 'like', '%electro%');
                });
                break;
            case 6: // Terapi Perilaku
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%terapi perilaku%')
                      ->orWhere('jp.nm_perawatan', 'like', '%behavior%')
                      ->orWhere('jp.nm_perawatan', 'like', '%behaviour%');
                });
                break;
            case 7: // Rehabilitasi Medik Psikiatrik
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%rehabilitasi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%rehab%');
                });
                break;
            case 8: // Assessment
                $query->where(function($q) {
                    $q->where('jp.nm_perawatan', 'like', '%assessment%')
                      ->orWhere('jp.nm_perawatan', 'like', '%asesmen%')
                      ->orWhere('jp.nm_perawatan', 'like', '%bebas narkoba%')
                      ->orWhere('jp.nm_perawatan', 'like', '%sehat jiwa%')
                      ->orWhere('jp.nm_perawatan', 'like', '%scl%')
                      ->orWhere('jp.nm_perawatan', 'like', '%mmpi%')
                      ->orWhere('jp.nm_perawatan', 'like', '%ver psychiatricium%')
                      ->orWhere('jp.nm_perawatan', 'like', '%tes%')
                      ->orWhere('jp.nm_perawatan', 'like', '%test%')
                      ->orWhereIn('jp.kd_jenis_prw', ['MED-310', 'MED-311', 'MED-312', 'MED-313']);
                });
                break;
        }

        return $query;
    }

    private function generateRL315PDF($tanggalAwal, $tanggalAkhir, $data, $hospitalInfo)
    {
        $pdf = PDF::loadView('rm.laporan_rm.rl315_pdf', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'data' => $data,
            'hospitalInfo' => $hospitalInfo
        ]);

        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'RL315_Kesehatan_Jiwa_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir)) . '.pdf';
        
        return $pdf->download($filename);
    }

    private function generateRL315Excel($tanggalAwal, $tanggalAkhir, $data, $hospitalInfo)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('RL 3.15');

        // Header
        $sheet->setCellValue('A1', 'RL 3.15 - REKAPITULASI KEGIATAN PELAYANAN KESEHATAN JIWA');
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($tanggalAwal)) . ' - ' . date('d/m/Y', strtotime($tanggalAkhir)));
        
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');

        // Styling header
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($titleStyle);
        $sheet->getStyle('A2:D2')->applyFromArray($titleStyle);

        // Table header
        $row = 4;
        $sheet->setCellValue("A{$row}", 'No.');
        $sheet->setCellValue("B{$row}", 'Jenis Kegiatan');
        $sheet->setCellValue("C{$row}", 'Laki-laki');
        $sheet->setCellValue("D{$row}", 'Perempuan');
        $sheet->setCellValue("E{$row}", 'Jumlah');

        $headerStyle = [
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']]
        ];
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($headerStyle);

        // Data
        $row = 5;
        foreach ($data as $no => $item) {
            if ($no == 99) continue; // Skip total, akan ditambahkan di akhir
            
            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $item['nama']);
            $sheet->setCellValue("C{$row}", $item['laki']);
            $sheet->setCellValue("D{$row}", $item['perempuan']);
            $sheet->setCellValue("E{$row}", $item['jumlah']);
            $row++;
        }

        // Total row
        $sheet->setCellValue("A{$row}", '99');
        $sheet->setCellValue("B{$row}", 'TOTAL');
        $sheet->setCellValue("C{$row}", $data[99]['laki']);
        $sheet->setCellValue("D{$row}", $data[99]['perempuan']);
        $sheet->setCellValue("E{$row}", $data[99]['jumlah']);
        
        $totalStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]
        ];
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($totalStyle);

        // Border semua data
        $sheet->getStyle("A4:E{$row}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Column width
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);

        // Output
        $writer = new Xlsx($spreadsheet);
        $filename = 'RL315_Kesehatan_Jiwa_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir)) . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit();
    }
}
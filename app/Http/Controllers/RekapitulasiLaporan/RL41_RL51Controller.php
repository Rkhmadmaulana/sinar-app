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

class RL41_RL51Controller extends Controller
{
    // Morbiditas Rawat Jalan
    public function morbiditasRawatJalan(Request $request){
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->format('Y-m-d'));
        $tanggalAkhir = $request->input('tanggal_akhir', now()->endOfMonth()->format('Y-m-d'));

        $data = $this->morbiditasRalanGetData($tanggalAwal, $tanggalAkhir);
            
        return view('rm.laporan_rm.rl51_morbiditas_rawat_jalan', compact('data', 'tanggalAwal', 'tanggalAkhir'));
    }

    // Morbiditas Rawat Inap
    public function morbiditasRawatInap(Request $request){
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->format('Y-m-d'));
        $tanggalAkhir = $request->input('tanggal_akhir', now()->endOfMonth()->format('Y-m-d'));

        $data = $this->morbiditasRanapGetData($tanggalAwal, $tanggalAkhir);
            
        return view('rm.laporan_rm.rl41_morbiditas_rawat_inap', compact('data', 'tanggalAwal', 'tanggalAkhir'));
    }

    public function exportMorbiditasRawatJalanExcel(Request $request)
    {
        try{
            $request->validate([
                'tanggal_awal' => 'required|date',
                'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            ]);

            $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->format('Y-m-d'));
            $tanggalAkhir = $request->input('tanggal_akhir', now()->endOfMonth()->format('Y-m-d'));
            
            $fileName = 'Morbiditas_Rawat_Jalan_' . $tanggalAwal . '_sampai_' . $tanggalAkhir . '.xlsx';
        
            $data = $this->morbiditasRalanGetData($tanggalAwal, $tanggalAkhir);

            if ($data->isEmpty()) {
                return back()->with('warning', 'Tidak ada data untuk periode yang dipilih');
            }
            if (ob_get_length()) {          // Kosongkan output buffer
                ob_end_clean();
            }
            return $this->generateMorbiditasRalanExcel($data, $tanggalAwal, $tanggalAkhir, $fileName);
        } catch (\Exception $e) {
            throw new \Exception('Error generating Excel: ' . $e->getMessage());
            //return back()->with('error', 'Terjadi kesalahan saat membuat file Excel');
        }
    }

     public function exportMorbiditasRawatInapExcel(Request $request)
    {
        try{
            $request->validate([
                'tanggal_awal' => 'required|date',
                'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            ]);

            $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->format('Y-m-d'));
            $tanggalAkhir = $request->input('tanggal_akhir', now()->endOfMonth()->format('Y-m-d'));
            
            $fileName = 'Morbiditas_Rawat_Inap_' . $tanggalAwal . '_sampai_' . $tanggalAkhir . '.xlsx';
        
            $data = $this->morbiditasRanapGetData($tanggalAwal, $tanggalAkhir);

            if ($data->isEmpty()) {
                return back()->with('warning', 'Tidak ada data untuk periode yang dipilih');
            }
            if (ob_get_length()) {          // Kosongkan output buffer
                ob_end_clean();
            }
            return $this->generateMorbiditasRanapExcel($data, $tanggalAwal, $tanggalAkhir, $fileName);
        } catch (\Exception $e) {
            throw new \Exception('Error generating Excel: ' . $e->getMessage());
            //return back()->with('error', 'Terjadi kesalahan saat membuat file Excel');
        }
    }

    private function generateMorbiditasExcel($data, $tanggalAwal, $tanggalAkhir, $fileName, $isRanap = false)
    {
        // Enable memory optimization for large datasets
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(9);
        
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set memory limit temporarily
        ini_set('memory_limit', '512M');
        
        // Set document title based on type
        if ($isRanap) {
            $sheet->setTitle('Morbiditas Rawat Inap');
            $headerTitle = 'LAPORAN MORBIDITAS RAWAT INAP';
        } else {
            $sheet->setTitle('Morbiditas Rawat Jalan');
            $headerTitle = 'LAPORAN MORBIDITAS RAWAT JALAN';
        }
        
        // Header information with enhanced styling
        $sheet->setCellValue('A1', $headerTitle);
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($tanggalAwal)) . ' - ' . date('d/m/Y', strtotime($tanggalAkhir)));
        
        // Merge cells for title
        $sheet->mergeCells('A1:BF1');
        $sheet->mergeCells('A2:BF2');
        
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
        
        $sheet->getStyle('A1:BF1')->applyFromArray($titleStyle);
        $sheet->getStyle('A2:BF2')->applyFromArray($periodStyle);
        
        // Set row heights for title
        $sheet->getRowDimension(1)->setRowHeight(35);
        $sheet->getRowDimension(2)->setRowHeight(25);
        
        // Create headers starting from row 4
        $headerRow = 4;
        
        // Main headers (row 1) - different based on type
        $sheet->setCellValue('A' . $headerRow, 'Kode ICD');
        $sheet->setCellValue('B' . $headerRow, 'Diagnosa Penyakit');
        
        if ($isRanap) {
            $sheet->setCellValue('C' . $headerRow, 'Jumlah Pasien Keluar Hidup dan Mati Menurut Kelompok Umur & Jenis Kelamin');
            $sheet->setCellValue('BA' . $headerRow, 'Jumlah Pasien Keluar Hidup dan Mati Menurut Jenis Kelamin');
            $sheet->setCellValue('BD' . $headerRow, 'Jumlah Pasien Keluar Mati');
        } else {
            $sheet->setCellValue('C' . $headerRow, 'Jumlah Kasus Baru Menurut Kelompok Umur & Jenis Kelamin');
            $sheet->setCellValue('BA' . $headerRow, 'Jumlah Kasus Baru Menurut Jenis Kelamin');
            $sheet->setCellValue('BD' . $headerRow, 'Jumlah Kunjungan');
        }
        
        // Merge main header cells
        $sheet->mergeCells('A' . $headerRow . ':A' . ($headerRow + 2));
        $sheet->mergeCells('B' . $headerRow . ':B' . ($headerRow + 2));
        $sheet->mergeCells('C' . $headerRow . ':AZ' . $headerRow);
        $sheet->mergeCells('BA' . $headerRow . ':BC' . ($headerRow + 1));
        $sheet->mergeCells('BD' . $headerRow . ':BF' . ($headerRow + 1));
        
        // Age group headers (row 2)
        $ageGroups = [
            'C' => '<1 jam', 'E' => '1-23 jam', 'G' => '1-7 hari', 'I' => '8-28 hari',
            'K' => '29 hari-<3 bln', 'M' => '3-<6 bln', 'O' => '6-<11 bln', 'Q' => '1-4 th',
            'S' => '5-9 th', 'U' => '10-14 th', 'W' => '15-19 th', 'Y' => '20-24 th',
            'AA' => '25-29 th', 'AC' => '30-34 th', 'AE' => '35-39 th', 'AG' => '40-44 th',
            'AI' => '45-49 th', 'AK' => '50-54 th', 'AM' => '55-59 th', 'AO' => '60-64 th',
            'AQ' => '65-69 th', 'AS' => '70-74 th', 'AU' => '75-79 th', 'AW' => '80-84 th',
            'AY' => '>=85 th'
        ];
        
        $setCellValueSafe = function($cell, $value) use ($sheet)
        {
            if ($value !== '-' && $value !== null && $value !== '') {
                $sheet->setCellValue($cell, $value);
            }
        };

        foreach ($ageGroups as $colName => $ageGroup) {
            // konversi nama kolom → nomor absolut
            $colNum = Coordinate::columnIndexFromString($colName);
            $nextCol = Coordinate::stringFromColumnIndex($colNum + 1);

            $sheet->setCellValue($colName . ($headerRow + 1), $ageGroup);
            $sheet->mergeCells($colName . ($headerRow + 1) . ':' . $nextCol . ($headerRow + 1));
        }
        
        // Gender headers (row 3)
        $colNo = 3;
        for ($i = 0; $i < 25; $i++) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colNo)   . ($headerRow + 2), 'L');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colNo+1) . ($headerRow + 2), 'P');
            $colNo += 2;
        }
        
        // Final summary headers
        $sheet->setCellValue('BA' . ($headerRow + 2), 'L');
        $sheet->setCellValue('BB' . ($headerRow + 2), 'P');
        $sheet->setCellValue('BC' . ($headerRow + 2), 'Total');
        $sheet->setCellValue('BD' . ($headerRow + 2), 'L');
        $sheet->setCellValue('BE' . ($headerRow + 2), 'P');
        $sheet->setCellValue('BF' . ($headerRow + 2), 'Total');
        
        // Enhanced header styling - different colors based on type
        if ($isRanap) {
            // Ranap - Purple theme
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
                    'startColor' => ['rgb' => '8E44AD'] // Purple main header
                ]
            ];
            
            $ageGroupHeaderStyle = [
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
                    'startColor' => ['rgb' => 'BB8FCE'] // Light purple for age groups
                ]
            ];
            
            $genderHeaderStyle = [
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
                    'startColor' => ['rgb' => 'D7BDE2'] // Very light purple for gender
                ]
            ];
        } else {
            // Ralan - Blue theme (original)
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
            
            $ageGroupHeaderStyle = [
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
                    'startColor' => ['rgb' => '5DADE2'] // Light blue for age groups
                ]
            ];
            
            $genderHeaderStyle = [
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
                    'startColor' => ['rgb' => 'AED6F1'] // Very light blue for gender
                ]
            ];
        }
        
        // Apply header styles
        $sheet->getStyle('A' . $headerRow . ':B' . ($headerRow + 2))->applyFromArray($mainHeaderStyle);
        $sheet->getStyle('C' . $headerRow . ':AZ' . $headerRow)->applyFromArray($mainHeaderStyle);
        $sheet->getStyle('BA' . $headerRow . ':BF' . ($headerRow + 1))->applyFromArray($mainHeaderStyle);
        
        // Age group headers
        $sheet->getStyle('C' . ($headerRow + 1) . ':AZ' . ($headerRow + 1))->applyFromArray($ageGroupHeaderStyle);
        
        // Gender headers
        $sheet->getStyle('C' . ($headerRow + 2) . ':BF' . ($headerRow + 2))->applyFromArray($genderHeaderStyle);
        
        // Data rows
        $dataStartRow = $headerRow + 3;
        $row = $dataStartRow;
        
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->kd_penyakit);
            $sheet->setCellValue('B' . $row, $item->nm_penyakit);
            
            // Age group data
            $cols = ['C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                    'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'];
            
            $fields = [
                $item->kurang_1hr_L, $item->kurang_1hr_P, $item->age_1_23hr_L, $item->age_1_23hr_P,
                $item->age_1_7day_L, $item->age_1_7day_P, $item->age_8_28day_L, $item->age_8_28day_P,
                $item->age_29day_3bln_L, $item->age_29day_3bln_P, $item->age_3_6bln_L, $item->age_3_6bln_P,
                $item->age_6_11bln_L, $item->age_6_11bln_P, $item->age_1_4th_L, $item->age_1_4th_P,
                $item->age_5_9_L, $item->age_5_9_P, $item->age_10_14_L, $item->age_10_14_P,
                $item->age_15_19_L, $item->age_15_19_P,
                $item->age_20_24_L, $item->age_20_24_P, $item->age_25_29_L, $item->age_25_29_P,
                $item->age_30_34_L, $item->age_30_34_P, $item->age_35_39_L, $item->age_35_39_P,
                $item->age_40_44_L, $item->age_40_44_P, $item->age_45_49_L, $item->age_45_49_P,
                $item->age_50_54_L, $item->age_50_54_P, $item->age_55_59_L, $item->age_55_59_P,
                $item->age_60_64_L, $item->age_60_64_P, $item->age_65_69_L, $item->age_65_69_P,
                $item->age_70_74_L, $item->age_70_74_P, $item->age_75_79_L, $item->age_75_79_P,
                $item->age_80_84_L, $item->age_80_84_P, $item->lebih_85_L, $item->lebih_85_P
            ];
            
            foreach ($fields as $index => $value) {
                $setCellValueSafe($cols[$index] . $row, $value === '-' ? '-' : $value);
            }
            
            // Summary data - different field names based on type
            $setCellValueSafe('BA' . $row, $item->total_L);
            $setCellValueSafe('BB' . $row, $item->total_P);
            
            if ($isRanap) {
                $setCellValueSafe('BC' . $row, $item->total_pasien_keluar);
                $setCellValueSafe('BD' . $row, $item->pasien_keluar_mati_L);
                $setCellValueSafe('BE' . $row, $item->pasien_keluar_mati_P);
                $setCellValueSafe('BF' . $row, $item->total_pasien_keluar_mati);
            } else {
                $setCellValueSafe('BC' . $row, $item->total_kasus_baru);
                $setCellValueSafe('BD' . $row, $item->kunjungan_L);
                $setCellValueSafe('BE' . $row, $item->kunjungan_P);
                $setCellValueSafe('BF' . $row, $item->total_kunjungan);
            }
            
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
        for ($i = $dataStartRow; $i < $row; $i++) {
            if (($i - $dataStartRow) % 2 == 0) {
                // Even rows - white background
                $evenRowStyle = array_merge($dataStyle, [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID, 
                        'startColor' => ['rgb' => 'FFFFFF']
                    ]
                ]);
                $sheet->getStyle('A' . $i . ':BF' . $i)->applyFromArray($evenRowStyle);
            } else {
                // Odd rows - light gray background
                $oddRowStyle = array_merge($dataStyle, [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID, 
                        'startColor' => ['rgb' => 'F8F9FA']
                    ]
                ]);
                $sheet->getStyle('A' . $i . ':BF' . $i)->applyFromArray($oddRowStyle);
            }
        }
        
        // Special styling for diagnosis column (B) - left alignment with wrap text
        $diagnosisStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('B' . $dataStartRow . ':B' . ($row - 1))->applyFromArray($diagnosisStyle);
        
        // Enhanced summary columns styling - different colors based on type
        if ($isRanap) {
            // Ranap - use purple theme
            $summaryTotalStyle = [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID, 
                    'startColor' => ['rgb' => 'E8DAEF']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '8E44AD']
                    ]
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => '512E5F'], 'size' => 11]
            ];
            
            $summaryMortalityStyle = [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID, 
                    'startColor' => ['rgb' => 'FADBD8']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => 'E74C3C']
                    ]
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => '922B21'], 'size' => 11]
            ];
        } else {
            // Ralan - use original green/yellow theme
            $summaryTotalStyle = [
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
            
            $summaryMortalityStyle = [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID, 
                    'startColor' => ['rgb' => 'FFF2CC']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => 'F39C12']
                    ]
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => '7D6608'], 'size' => 11]
            ];
        }
        
        $sheet->getStyle('BA' . $dataStartRow . ':BC' . ($row - 1))->applyFromArray($summaryTotalStyle);
        $sheet->getStyle('BD' . $dataStartRow . ':BF' . ($row - 1))->applyFromArray($summaryMortalityStyle);
        
        // Set autofilter
        $sheet->setAutoFilter('A' . ($headerRow + 2) . ':BF' . ($row - 1));
        
        // Auto size columns
        foreach (range('A', 'BF') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Summary columns - medium width
        foreach (['BA', 'BB', 'BC', 'BD', 'BE', 'BF'] as $column) {
            $sheet->getColumnDimension($column)->setWidth(10);
        }
        
        // Set row heights
        for ($i = $headerRow; $i <= $headerRow + 2; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(30);
        }
        
        // Set data row heights
        for ($i = $dataStartRow; $i < $row; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(20);
        }
        
        // Use streaming writer for better performance
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        
        $writer->save('php://output');
        exit();
    }

    /**
     * Generate Morbiditas Excel for Rawat Jalan
     */
    private function generateMorbiditasRalanExcel($data, $tanggalAwal, $tanggalAkhir, $fileName)
    {
        return $this->generateMorbiditasExcel($data, $tanggalAwal, $tanggalAkhir, $fileName, false);
    }

    /**
     * Generate Morbiditas Excel for Rawat Inap
     */
    private function generateMorbiditasRanapExcel($data, $tanggalAwal, $tanggalAkhir, $fileName)
    {
        return $this->generateMorbiditasExcel($data, $tanggalAwal, $tanggalAkhir, $fileName, true);
    }

    private function morbiditasGetData($tanggalAwal = null, $tanggalAkhir = null, $isRanap = false)
    {
        // Set default dates if not provided
        $tanggalAwal = $tanggalAwal ?: now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $tanggalAkhir ?: now()->endOfMonth()->format('Y-m-d');

        // Determine status for diagnosa_pasien and reg_periksa
        $statusRegistrasi = $isRanap ? 'Ranap' : 'Ralan';
        $statusDiagnosa = $isRanap ? 'Ranap' : 'Ralan';

        // Determine date field for age calculation
        $dateField = $isRanap ? 'ki.tgl_keluar' : 'rp.tgl_registrasi';

        // Subquery for Diagnosa Pasien (to get the highest priority diagnosis)
        $dpRawQuery = "(
            SELECT no_rawat, kd_penyakit, status_penyakit
            FROM (
                SELECT no_rawat, kd_penyakit, prioritas, status_penyakit,
                    ROW_NUMBER() OVER (
                        PARTITION BY no_rawat
                        ORDER BY -prioritas DESC, kd_penyakit ASC
                    ) as rn
                FROM diagnosa_pasien
                WHERE status = ?
            ) ranked
            WHERE rn = 1
        ) as dp";

        // PERBAIKAN: Subquery untuk kamar_inap yang sudah deduplicated
        $kiRawQuery = $isRanap ? "(
            SELECT no_rawat, tgl_masuk, tgl_keluar, stts_pulang
            FROM (
                SELECT no_rawat, tgl_masuk, tgl_keluar, stts_pulang,
                    ROW_NUMBER() OVER (
                        PARTITION BY no_rawat
                        ORDER BY tgl_keluar DESC, jam_keluar DESC
                    ) as rn
                FROM kamar_inap
                WHERE stts_pulang != 'Pindah Kamar' AND stts_pulang != '-'
            ) ranked_ki
            WHERE rn = 1
        ) as ki" : null;

        $query = DB::table('reg_periksa as rp')
            ->join('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis');

        // Add kamar_inap join only for Ranap - MENGGUNAKAN SUBQUERY YANG SUDAH DEDUPLICATED
        if ($isRanap) {
            $query->join(DB::raw($kiRawQuery), function ($join) {
                $join->on('rp.no_rawat', '=', 'ki.no_rawat');
            });
        }

        // Join with the derived table for diagnoses
        $query->join(DB::raw($dpRawQuery), function ($join) {
            $join->on('rp.no_rawat', '=', 'dp.no_rawat');
        })
        ->addBinding($statusDiagnosa, 'join')
        ->join('penyakit as py', 'dp.kd_penyakit', '=', 'py.kd_penyakit')
        ->where('rp.status_lanjut', '=', $statusRegistrasi)
        ->whereBetween(($isRanap ? 'ki.tgl_masuk' : 'rp.tgl_registrasi'), [$tanggalAwal, $tanggalAkhir]);

        // --- Select Clause with Aggregations ---
        $query->select(
            'py.kd_penyakit',
            'py.nm_penyakit',
            // Age-based counts by gender (simplified for readability)
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(HOUR, p.tgl_lahir, {$dateField}) < 1 AND p.jk = 'L' THEN 1 ELSE 0 END) as kurang_1hr_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(HOUR, p.tgl_lahir, {$dateField}) < 1 AND p.jk = 'P' THEN 1 ELSE 0 END) as kurang_1hr_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(HOUR, p.tgl_lahir, {$dateField}) BETWEEN 1 AND 23 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_1_23hr_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(HOUR, p.tgl_lahir, {$dateField}) BETWEEN 1 AND 23 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_1_23hr_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(DAY, p.tgl_lahir, {$dateField}) BETWEEN 1 AND 7 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_1_7day_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(DAY, p.tgl_lahir, {$dateField}) BETWEEN 1 AND 7 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_1_7day_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(DAY, p.tgl_lahir, {$dateField}) BETWEEN 8 AND 28 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_8_28day_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(DAY, p.tgl_lahir, {$dateField}) BETWEEN 8 AND 28 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_8_28day_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(DAY, p.tgl_lahir, {$dateField}) BETWEEN 29 AND 89 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_29day_3bln_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(DAY, p.tgl_lahir, {$dateField}) BETWEEN 29 AND 89 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_29day_3bln_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(MONTH, p.tgl_lahir, {$dateField}) BETWEEN 3 AND 6 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_3_6bln_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(MONTH, p.tgl_lahir, {$dateField}) BETWEEN 3 AND 6 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_3_6bln_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(MONTH, p.tgl_lahir, {$dateField}) BETWEEN 6 AND 11 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_6_11bln_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(MONTH, p.tgl_lahir, {$dateField}) BETWEEN 6 AND 11 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_6_11bln_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 1 AND 4 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_1_4th_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 1 AND 4 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_1_4th_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 5 AND 9 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_5_9_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 5 AND 9 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_5_9_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 10 AND 14 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_10_14_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 10 AND 14 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_10_14_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 15 AND 19 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_15_19_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 15 AND 19 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_15_19_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 20 AND 24 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_20_24_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 20 AND 24 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_20_24_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 25 AND 29 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_25_29_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 25 AND 29 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_25_29_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 30 AND 34 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_30_34_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 30 AND 34 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_30_34_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 35 AND 39 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_35_39_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 35 AND 39 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_35_39_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 40 AND 44 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_40_44_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 40 AND 44 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_40_44_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 45 AND 49 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_45_49_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 45 AND 49 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_45_49_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 50 AND 54 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_50_54_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 50 AND 54 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_50_54_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 55 AND 59 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_55_59_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 55 AND 59 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_55_59_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 60 AND 64 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_60_64_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 60 AND 64 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_60_64_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 65 AND 69 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_65_69_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 65 AND 69 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_65_69_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 70 AND 74 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_70_74_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 70 AND 74 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_70_74_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 75 AND 79 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_75_79_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 75 AND 79 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_75_79_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 80 AND 84 AND p.jk = 'L' THEN 1 ELSE 0 END) as age_80_84_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) BETWEEN 80 AND 84 AND p.jk = 'P' THEN 1 ELSE 0 END) as age_80_84_P"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) >= 85 AND p.jk = 'L' THEN 1 ELSE 0 END) as lebih_85_L"),
            DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(YEAR, p.tgl_lahir, {$dateField}) >= 85 AND p.jk = 'P' THEN 1 ELSE 0 END) as lebih_85_P"),

            // Total by gender
            DB::raw('COUNT(CASE WHEN p.jk = "L" THEN 1 END) as ' . ($isRanap ? 'total_L' : 'kunjungan_L')),
            DB::raw('COUNT(CASE WHEN p.jk = "P" THEN 1 END) as ' . ($isRanap ? 'total_P' : 'kunjungan_P')),
            DB::raw('COUNT(*) as ' . ($isRanap ? 'total_pasien_keluar' : 'total_kunjungan')),

            // Specific calculations for 'Kasus Baru' for Ralan and 'Pasien Keluar Mati' for Ranap
            DB::raw("SUM(CASE
                WHEN " . ($isRanap ? "ki.stts_pulang = 'Meninggal' AND p.jk = 'L'" : "dp.status_penyakit = 'Baru' AND p.jk = 'L'") . " THEN 1 ELSE 0 END
            ) as " . ($isRanap ? 'pasien_keluar_mati_L' : 'total_L')),

            DB::raw("SUM(CASE
                WHEN " . ($isRanap ? "ki.stts_pulang = 'Meninggal' AND p.jk = 'P'" : "dp.status_penyakit = 'Baru' AND p.jk = 'P'") . " THEN 1 ELSE 0 END
            ) as " . ($isRanap ? 'pasien_keluar_mati_P' : 'total_P')),

            DB::raw("SUM(CASE
                WHEN " . ($isRanap ? "ki.stts_pulang = 'Meninggal'" : "dp.status_penyakit = 'Baru'") . " THEN 1 ELSE 0 END
            ) as " . ($isRanap ? 'total_pasien_keluar_mati' : 'total_kasus_baru'))
        );

        $data = $query->groupBy('py.kd_penyakit', 'py.nm_penyakit')->get();
       //throw new \Exception($data->toSql());
        // --- Post-processing for formatting ---
        $formattedData = $data->map(function ($item) {
            foreach ($item as $key => $value) {
                if (is_numeric($value) && $value == 0) {
                    $item->$key = '-';
                }
            }
            return $item;
        });

        return $formattedData;
    }

    // Wrapper functions for backward compatibility and cleaner calls
    private function morbiditasRalanGetData($tanggalAwal = null, $tanggalAkhir = null){
        return $this->morbiditasGetData($tanggalAwal, $tanggalAkhir, false);
    }

    private function morbiditasRanapGetData($tanggalAwal = null, $tanggalAkhir = null){
        return $this->morbiditasGetData($tanggalAwal, $tanggalAkhir, true);
    }
}
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

class RL319Controller extends Controller
{
    public function laporanRL319(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal') ?? Carbon::now()->startOfYear()->format('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? Carbon::now()->endOfYear()->format('Y-m-d');

        // Get data rekapitulasi cara bayar
        $dataCaraBayar = $this->getDataCaraBayar($tanggalAwal, $tanggalAkhir);
        
        $hospitalInfo = DB::table('setting')->first();

        if ($request->has('download_pdf')) {
            return $this->generateRL319PDF($tanggalAwal, $tanggalAkhir, $dataCaraBayar, $hospitalInfo);
        }

        if ($request->has('download_excel')) {
            return $this->generateRL319Excel($tanggalAwal, $tanggalAkhir, $dataCaraBayar, $hospitalInfo);
        }

        return view('rm.laporan_rm.rl319', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'data' => $dataCaraBayar,
            'hospitalInfo' => $hospitalInfo
        ]);
    }

    private function getDataCaraBayar($tanggalAwal, $tanggalAkhir)
    {
        // Struktur data sesuai RL 3.19 yang benar
        $struktur = [
            1 => [
                'no' => '1',
                'nama' => 'Membayar Sendiri',
                'ranap_pasien' => 0,
                'ranap_lama' => 0,
                'rajal_total' => 0,
                'rajal_lab' => 0,
                'rajal_rad' => 0,
                'rajal_lain' => 0
            ],
            2 => [
                'no' => '2',
                'nama' => 'Asuransi',
                'ranap_pasien' => 0,
                'ranap_lama' => 0,
                'rajal_total' => 0,
                'rajal_lab' => 0,
                'rajal_rad' => 0,
                'rajal_lain' => 0
            ],
            21 => [
                'no' => '2.1',
                'nama' => 'Asuransi JKN (BPJS Kesehatan)',
                'ranap_pasien' => 0,
                'ranap_lama' => 0,
                'rajal_total' => 0,
                'rajal_lab' => 0,
                'rajal_rad' => 0,
                'rajal_lain' => 0
            ],
            22 => [
                'no' => '2.2',
                'nama' => 'Asuransi Pemerintah Daerah (Jamkesda)',
                'ranap_pasien' => 0,
                'ranap_lama' => 0,
                'rajal_total' => 0,
                'rajal_lab' => 0,
                'rajal_rad' => 0,
                'rajal_lain' => 0
            ],
            23 => [
                'no' => '2.3',
                'nama' => 'Asuransi Pemerintah Lainnya',
                'ranap_pasien' => 0,
                'ranap_lama' => 0,
                'rajal_total' => 0,
                'rajal_lab' => 0,
                'rajal_rad' => 0,
                'rajal_lain' => 0
            ],
            24 => [
                'no' => '2.4',
                'nama' => 'Asuransi Swasta',
                'ranap_pasien' => 0,
                'ranap_lama' => 0,
                'rajal_total' => 0,
                'rajal_lab' => 0,
                'rajal_rad' => 0,
                'rajal_lain' => 0
            ],
            3 => [
                'no' => '3',
                'nama' => 'Keringanan (Cost Sharing)',
                'ranap_pasien' => 0,
                'ranap_lama' => 0,
                'rajal_total' => 0,
                'rajal_lab' => 0,
                'rajal_rad' => 0,
                'rajal_lain' => 0
            ],
            4 => [
                'no' => '4',
                'nama' => 'Gratis',
                'ranap_pasien' => 0,
                'ranap_lama' => 0,
                'rajal_total' => 0,
                'rajal_lab' => 0,
                'rajal_rad' => 0,
                'rajal_lain' => 0
            ],
            41 => [
                'no' => '4.1',
                'nama' => 'Kartu Sehat',
                'ranap_pasien' => 0,
                'ranap_lama' => 0,
                'rajal_total' => 0,
                'rajal_lab' => 0,
                'rajal_rad' => 0,
                'rajal_lain' => 0
            ],
            42 => [
                'no' => '4.2',
                'nama' => 'Keterangan Tidak Mampu',
                'ranap_pasien' => 0,
                'ranap_lama' => 0,
                'rajal_total' => 0,
                'rajal_lab' => 0,
                'rajal_rad' => 0,
                'rajal_lain' => 0
            ],
            43 => [
                'no' => '4.3',
                'nama' => 'Lain-lain',
                'ranap_pasien' => 0,
                'ranap_lama' => 0,
                'rajal_total' => 0,
                'rajal_lab' => 0,
                'rajal_rad' => 0,
                'rajal_lain' => 0
            ],
            99 => [
                'no' => '99',
                'nama' => 'TOTAL',
                'ranap_pasien' => 0,
                'ranap_lama' => 0,
                'rajal_total' => 0,
                'rajal_lab' => 0,
                'rajal_rad' => 0,
                'rajal_lain' => 0
            ]
        ];

        // 1. Data Rawat Inap (Jumlah Pasien Keluar & Jumlah Lama Dirawat)
        $ranapData = DB::table('reg_periksa')
            ->join('kamar_inap', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where('reg_periksa.status_lanjut', 'Ranap')
            ->whereNotNull('kamar_inap.tgl_keluar')
            ->select(
                'reg_periksa.kd_pj',
                DB::raw('COUNT(DISTINCT reg_periksa.no_rawat) as jumlah_pasien'),
                DB::raw('SUM(kamar_inap.lama) as jumlah_lama')
            )
            ->groupBy('reg_periksa.kd_pj')
            ->get();

        foreach ($ranapData as $data) {
            $indexes = $this->getIndexesByKdPj($data->kd_pj);
            foreach ($indexes as $idx) {
                $struktur[$idx]['ranap_pasien'] += $data->jumlah_pasien;
                $struktur[$idx]['ranap_lama'] += $data->jumlah_lama;
            }
        }

        // 2. Data Rawat Jalan - Laboratorium
        $labData = DB::table('reg_periksa')
            ->join('permintaan_lab', 'reg_periksa.no_rawat', '=', 'permintaan_lab.no_rawat')
            ->whereBetween('permintaan_lab.tgl_permintaan', [$tanggalAwal, $tanggalAkhir])
            ->select(
                'reg_periksa.kd_pj',
                DB::raw('COUNT(DISTINCT permintaan_lab.noorder) as jumlah')
            )
            ->groupBy('reg_periksa.kd_pj')
            ->get();

        foreach ($labData as $data) {
            $indexes = $this->getIndexesByKdPj($data->kd_pj);
            foreach ($indexes as $idx) {
                $struktur[$idx]['rajal_lab'] += $data->jumlah;
            }
        }

        // 3. Data Rawat Jalan - Radiologi
        $radData = DB::table('reg_periksa')
            ->join('permintaan_radiologi', 'reg_periksa.no_rawat', '=', 'permintaan_radiologi.no_rawat')
            ->whereBetween('permintaan_radiologi.tgl_permintaan', [$tanggalAwal, $tanggalAkhir])
            ->select(
                'reg_periksa.kd_pj',
                DB::raw('COUNT(DISTINCT permintaan_radiologi.noorder) as jumlah')
            )
            ->groupBy('reg_periksa.kd_pj')
            ->get();

        foreach ($radData as $data) {
            $indexes = $this->getIndexesByKdPj($data->kd_pj);
            foreach ($indexes as $idx) {
                $struktur[$idx]['rajal_rad'] += $data->jumlah;
            }
        }

        // 4. Data Rawat Jalan - Lain-lain (Poliklinik)
        $rajalData = DB::table('reg_periksa')
            ->whereBetween('tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->where('status_lanjut', 'Ralan')
            ->select(
                'kd_pj',
                DB::raw('COUNT(DISTINCT no_rawat) as jumlah')
            )
            ->groupBy('kd_pj')
            ->get();

        foreach ($rajalData as $data) {
            $indexes = $this->getIndexesByKdPj($data->kd_pj);
            foreach ($indexes as $idx) {
                $struktur[$idx]['rajal_lain'] += $data->jumlah;
            }
        }

        // Hitung Jumlah Pasien Rawat Jalan untuk setiap kategori
        foreach ($struktur as $key => $item) {
            if ($key != 99) {
                $struktur[$key]['rajal_total'] = 
                    $struktur[$key]['rajal_lab'] + 
                    $struktur[$key]['rajal_rad'] + 
                    $struktur[$key]['rajal_lain'];
            }
        }

        // Hitung subtotal Asuransi (no 2)
        $struktur[2]['ranap_pasien'] = $struktur[21]['ranap_pasien'] + $struktur[22]['ranap_pasien'] + 
                                    $struktur[23]['ranap_pasien'] + $struktur[24]['ranap_pasien'];
        $struktur[2]['ranap_lama'] = $struktur[21]['ranap_lama'] + $struktur[22]['ranap_lama'] + 
                                    $struktur[23]['ranap_lama'] + $struktur[24]['ranap_lama'];
        $struktur[2]['rajal_lab'] = $struktur[21]['rajal_lab'] + $struktur[22]['rajal_lab'] + 
                                    $struktur[23]['rajal_lab'] + $struktur[24]['rajal_lab'];
        $struktur[2]['rajal_rad'] = $struktur[21]['rajal_rad'] + $struktur[22]['rajal_rad'] + 
                                    $struktur[23]['rajal_rad'] + $struktur[24]['rajal_rad'];
        $struktur[2]['rajal_lain'] = $struktur[21]['rajal_lain'] + $struktur[22]['rajal_lain'] + 
                                    $struktur[23]['rajal_lain'] + $struktur[24]['rajal_lain'];
        $struktur[2]['rajal_total'] = $struktur[21]['rajal_total'] + $struktur[22]['rajal_total'] + 
                                    $struktur[23]['rajal_total'] + $struktur[24]['rajal_total'];

        // Hitung subtotal Gratis (no 4)
        $struktur[4]['ranap_pasien'] = $struktur[41]['ranap_pasien'] + $struktur[42]['ranap_pasien'] + $struktur[43]['ranap_pasien'];
        $struktur[4]['ranap_lama'] = $struktur[41]['ranap_lama'] + $struktur[42]['ranap_lama'] + $struktur[43]['ranap_lama'];
        $struktur[4]['rajal_lab'] = $struktur[41]['rajal_lab'] + $struktur[42]['rajal_lab'] + $struktur[43]['rajal_lab'];
        $struktur[4]['rajal_rad'] = $struktur[41]['rajal_rad'] + $struktur[42]['rajal_rad'] + $struktur[43]['rajal_rad'];
        $struktur[4]['rajal_lain'] = $struktur[41]['rajal_lain'] + $struktur[42]['rajal_lain'] + $struktur[43]['rajal_lain'];
        $struktur[4]['rajal_total'] = $struktur[41]['rajal_total'] + $struktur[42]['rajal_total'] + $struktur[43]['rajal_total'];

        // Hitung TOTAL (no 99) - hanya dari kategori utama: 1, 2, 3, 4
        foreach ([1, 2, 3, 4] as $idx) {
            $struktur[99]['ranap_pasien'] += $struktur[$idx]['ranap_pasien'];
            $struktur[99]['ranap_lama'] += $struktur[$idx]['ranap_lama'];
            $struktur[99]['rajal_lab'] += $struktur[$idx]['rajal_lab'];
            $struktur[99]['rajal_rad'] += $struktur[$idx]['rajal_rad'];
            $struktur[99]['rajal_lain'] += $struktur[$idx]['rajal_lain'];
            $struktur[99]['rajal_total'] += $struktur[$idx]['rajal_total'];
        }

        return $struktur;
    }

    private function getIndexesByKdPj($kdPj)
    {
        // Return array of indexes yang akan diupdate berdasarkan kd_pj
        // Index untuk kategori detail dan parent-nya
        switch ($kdPj) {
            case 'PJ2': // Umum
                return [1]; // Membayar Sendiri
            case 'BPJ': // BPJS Kesehatan
                return [21]; // Asuransi JKN
            case 'PJ4': // JAMKESDA
                return [22]; // Asuransi Pemda
            case 'PJ8': // Pendamping JKN (Pemda)
                return [23]; // Asuransi Pemprov
            case 'PJ3': // INHEALTH
            case 'PJ7': // BPJS Ketenagakerjaan
            case 'JR': // Jasa Raharja
                return [24]; // Asuransi Swasta
            case 'PJ5': // Kontrak
                return [3]; // Keringanan
            case 'PJ6': // GRATIS - masuk ke 4.3 (Lain-lain)
                return [43]; // Gratis Lain-lain
            default:
                return [];
        }
    }

    private function mappingCaraBayar(&$struktur, $kdPj, $jumlah)
    {
        // Mapping berdasarkan kode penanggung jawab (kd_pj) dari tabel penjab
        switch ($kdPj) {
            case 'PJ2': // Umum
                $struktur['membayar_sendiri'] += $jumlah;
                break;
            case 'BPJ': // BPJS Kesehatan
                $struktur['asuransi_jkn'] += $jumlah;
                break;
            case 'PJ3': // INHEALTH
            case 'PJ7': // BPJS Ketenagakerjaan
            case 'JR': // Jasa Raharja
                $struktur['asuransi_perusahaan'] += $jumlah;
                break;
            case 'PJ4': // JAMKESDA
                $struktur['asuransi_pemda'] += $jumlah;
                break;
            case 'PJ8': // Pendamping JKN (Pemda)
                $struktur['asuransi_pemprov'] += $jumlah;
                break;
            case 'PJ5': // Kontrak (bisa dikategorikan sebagai keringanan)
                $struktur['keringanan'] += $jumlah;
                break;
            case 'PJ6': // GRATIS - masuk ke gratis_lainnya semuanya
                $struktur['gratis'] += $jumlah;
                $struktur['gratis_lainnya'] += $jumlah; // Semua gratis masuk ke 4.3
                break;
            default:
                // Jika tidak termasuk kategori di atas, masuk ke lain-lain
                break;
        }
    }

    public function laporanRL319Detail(Request $request)
{
    $kategoriNo = $request->input('kategori');
    $tipe = $request->input('tipe');
    $tanggalAwal = $request->input('tanggal_awal');
    $tanggalAkhir = $request->input('tanggal_akhir');

    $data = $this->getDetailData($kategoriNo, $tipe, $tanggalAwal, $tanggalAkhir);

    $kategoriMap = [
        1 => 'Membayar Sendiri',
        2 => 'Asuransi',
        21 => 'Asuransi JKN (BPJS Kesehatan)',
        22 => 'Asuransi Pemerintah Daerah (Jamkesda)',
        23 => 'Asuransi Pemerintah Lainnya',
        24 => 'Asuransi Swasta',
        3 => 'Keringanan (Cost Sharing)',
        4 => 'Gratis',
        41 => 'Kartu Sehat',
        42 => 'Keterangan Tidak Mampu',
        43 => 'Lain-lain'
    ];

    $tipeMap = [
        'ranap_pasien' => 'Pasien Rawat Inap',
        'rajal_lab' => 'Rawat Jalan - Laboratorium',
        'rajal_rad' => 'Rawat Jalan - Radiologi',
        'rajal_lain' => 'Rawat Jalan - Lain-lain (Poliklinik)'
    ];

    return view('rm.laporan_rm.rl319_detail', [
        'data' => $data,
        'kategoriNo' => $kategoriNo,
        'kategoriNama' => $kategoriMap[$kategoriNo] ?? 'Unknown',
        'tipe' => $tipe,
        'tipeNama' => $tipeMap[$tipe] ?? 'Unknown',
        'tanggalAwal' => $tanggalAwal,
        'tanggalAkhir' => $tanggalAkhir
    ]);
}

    private function getDetailData($kategoriNo, $tipe, $tanggalAwal, $tanggalAkhir)
    {
        $kdPjList = $this->getKdPjByKategori($kategoriNo);
        
        if (empty($kdPjList)) {
            return collect([]);
        }

        $query = null;

        switch ($tipe) {
            case 'ranap_pasien':
                // Data Rawat Inap
                $query = DB::table('reg_periksa')
                    ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                    ->join('kamar_inap', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
                    ->join('kamar', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
                    ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
                    ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
                    ->where('reg_periksa.status_lanjut', 'Ranap')
                    ->whereNotNull('kamar_inap.tgl_keluar')
                    ->whereIn('reg_periksa.kd_pj', $kdPjList)
                    ->select(
                        'reg_periksa.no_rawat',
                        'reg_periksa.no_rkm_medis',
                        'pasien.nm_pasien',
                        'pasien.jk',
                        'reg_periksa.tgl_registrasi',
                        'kamar_inap.tgl_masuk',
                        'kamar_inap.tgl_keluar',
                        'kamar_inap.lama',
                        'kamar.kd_kamar',
                        'penjab.png_jawab as cara_bayar'
                    )
                    ->orderBy('reg_periksa.tgl_registrasi', 'desc');
                break;

            case 'rajal_lab':
                // Data Laboratorium
                $query = DB::table('reg_periksa')
                    ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                    ->join('permintaan_lab', 'reg_periksa.no_rawat', '=', 'permintaan_lab.no_rawat')
                    ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
                    ->whereBetween('permintaan_lab.tgl_permintaan', [$tanggalAwal, $tanggalAkhir])
                    ->whereIn('reg_periksa.kd_pj', $kdPjList)
                    ->select(
                        'reg_periksa.no_rawat',
                        'permintaan_lab.noorder',
                        'reg_periksa.no_rkm_medis',
                        'pasien.nm_pasien',
                        'pasien.jk',
                        'permintaan_lab.tgl_permintaan',
                        'permintaan_lab.jam_permintaan',
                        'permintaan_lab.status',
                        'permintaan_lab.dokter_perujuk',
                        'penjab.png_jawab as cara_bayar'
                    )
                    ->orderBy('permintaan_lab.tgl_permintaan', 'desc');
                break;

            case 'rajal_rad':
                // Data Radiologi
                $query = DB::table('reg_periksa')
                    ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                    ->join('permintaan_radiologi', 'reg_periksa.no_rawat', '=', 'permintaan_radiologi.no_rawat')
                    ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
                    ->whereBetween('permintaan_radiologi.tgl_permintaan', [$tanggalAwal, $tanggalAkhir])
                    ->whereIn('reg_periksa.kd_pj', $kdPjList)
                    ->select(
                        'reg_periksa.no_rawat',
                        'permintaan_radiologi.noorder',
                        'reg_periksa.no_rkm_medis',
                        'pasien.nm_pasien',
                        'pasien.jk',
                        'permintaan_radiologi.tgl_permintaan',
                        'permintaan_radiologi.jam_permintaan',
                        'permintaan_radiologi.status',
                        'permintaan_radiologi.dokter_perujuk',
                        'penjab.png_jawab as cara_bayar'
                    )
                    ->orderBy('permintaan_radiologi.tgl_permintaan', 'desc');
                break;

            case 'rajal_lain':
                // Data Rawat Jalan (Poliklinik)
                $query = DB::table('reg_periksa')
                    ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                    ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                    ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
                    ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
                    ->where('reg_periksa.status_lanjut', 'Ralan')
                    ->whereIn('reg_periksa.kd_pj', $kdPjList)
                    ->select(
                        'reg_periksa.no_rawat',
                        'reg_periksa.no_rkm_medis',
                        'pasien.nm_pasien',
                        'pasien.jk',
                        'reg_periksa.tgl_registrasi',
                        'reg_periksa.jam_reg',
                        'poliklinik.nm_poli',
                        'penjab.png_jawab as cara_bayar'
                    )
                    ->orderBy('reg_periksa.tgl_registrasi', 'desc');
                break;
        }

        if ($query) {
            return $query->get();
        }

        return collect([]);
    }

    private function getKdPjByKategori($kategoriNo)
    {
        // Return array of kd_pj based on kategori
        switch ($kategoriNo) {
            case 1: // Membayar Sendiri
                return ['PJ2'];
            case 21: // Asuransi JKN
                return ['BPJ'];
            case 22: // Asuransi Pemda
                return ['PJ4'];
            case 23: // Asuransi Pemprov
                return ['PJ8'];
            case 24: // Asuransi Swasta
                return ['PJ3', 'PJ7', 'JR'];
            case 3: // Keringanan
                return ['PJ5'];
            case 41: // Kartu Sehat
                return []; // Kosong sesuai permintaan
            case 42: // Tidak Mampu
                return []; // Kosong sesuai permintaan
            case 43: // Gratis Lain-lain
                return ['PJ6'];
            default:
                return [];
        }
    }

    private function generateRL319PDF($tanggalAwal, $tanggalAkhir, $data, $hospitalInfo)
    {
        $pdf = PDF::loadView('rm.laporan_rm.rl319_pdf', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'data' => $data,
            'hospitalInfo' => $hospitalInfo
        ]);

        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'RL319_Cara_Bayar_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir)) . '.pdf';
        
        return $pdf->download($filename);
    }

    private function generateRL319Excel($tanggalAwal, $tanggalAkhir, $data, $hospitalInfo)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('RL 3.19');

        // Header
        $sheet->setCellValue('A1', 'RL 3.19 - REKAPITULASI CARA BAYAR');
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($tanggalAwal)) . ' - ' . date('d/m/Y', strtotime($tanggalAkhir)));
        
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');

        // Styling header
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($titleStyle);
        $sheet->getStyle('A2:H2')->applyFromArray($titleStyle);

        // Table header - Baris 1
        $row = 4;
        $sheet->setCellValue("A{$row}", 'No');
        $sheet->setCellValue("B{$row}", 'Cara Pembayaran');
        $sheet->setCellValue("C{$row}", 'Pasien Rawat Inap');
        $sheet->setCellValue("E{$row}", 'Jumlah Pasien Rawat Jalan');
        $sheet->setCellValue("F{$row}", 'Jumlah Pasien Rawat Jalan');
        
        $sheet->mergeCells("A{$row}:A" . ($row + 1));
        $sheet->mergeCells("B{$row}:B" . ($row + 1));
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->mergeCells("E{$row}:E" . ($row + 1));
        $sheet->mergeCells("F{$row}:H{$row}");

        // Table header - Baris 2
        $row = 5;
        $sheet->setCellValue("C{$row}", 'Jumlah Pasien Keluar');
        $sheet->setCellValue("D{$row}", 'Jumlah Lama Dirawat');
        $sheet->setCellValue("F{$row}", 'Laboratorium');
        $sheet->setCellValue("G{$row}", 'Radiologi');
        $sheet->setCellValue("H{$row}", 'Lain-lain');

        $headerStyle = [
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ];
        $sheet->getStyle("A4:H5")->applyFromArray($headerStyle);

        // Data
        $row = 6;
        foreach ($data as $no => $item) {
            if ($no == 99) {
                $totalStyle = [
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]
                ];
                $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($totalStyle);
            } elseif ($no == 2 || $no == 4) {
                $subtotalStyle = [
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']]
                ];
                $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($subtotalStyle);
            }
            
            $sheet->setCellValue("A{$row}", $item['no']);
            $sheet->setCellValue("B{$row}", $item['nama']);
            $sheet->setCellValue("C{$row}", $item['ranap_pasien']);
            $sheet->setCellValue("D{$row}", $item['ranap_lama']);
            $sheet->setCellValue("E{$row}", $item['rajal_total']);
            $sheet->setCellValue("F{$row}", $item['rajal_lab']);
            $sheet->setCellValue("G{$row}", $item['rajal_rad']);
            $sheet->setCellValue("H{$row}", $item['rajal_lain']);
            $row++;
        }

        // Border semua data
        $sheet->getStyle("A4:H" . ($row - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Column width
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);

        // Output
        $writer = new Xlsx($spreadsheet);
        $filename = 'RL319_Cara_Bayar_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir)) . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit();
    }
}
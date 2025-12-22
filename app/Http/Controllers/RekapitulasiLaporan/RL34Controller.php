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

class RL34Controller extends Controller
{
    ////////////////////////////////////////////////////////////////
    // RL 3.4 Pengunjung
     /**
     * RL 3.4 - Rekapitulasi Pengunjung
     */
    public function rl34Pengunjung(Request $request)
    {
        // Get date range
        $tanggalAwal = $request->input('tanggal_awal') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // Get pengunjung data
        $pengunjungBaru = DB::table('reg_periksa')
            ->where('stts_daftar', 'Baru')
            ->whereBetween('tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->count();

        $pengunjungLama = DB::table('reg_periksa')
            ->where('stts_daftar', 'Lama')
            ->whereBetween('tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->count();

        $total = $pengunjungBaru + $pengunjungLama;

        // Hospital info for PDF
        $hospitalInfo = DB::table('setting')->first();

        // Check if PDF download is requested
        if ($request->has('download_pdf')) {
            return $this->generateRL34PDF($tanggalAwal, $tanggalAkhir, $pengunjungBaru, $pengunjungLama, $total, $hospitalInfo);
        }

        return view('rm.laporan_rm.rl34_pengunjung', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'pengunjungBaru' => $pengunjungBaru,
            'pengunjungLama' => $pengunjungLama,
            'total' => $total,
            'hospitalInfo' => $hospitalInfo
        ]);
    }

    /**
     * Generate PDF for RL 3.4
     */
    private function generateRL34PDF($tanggalAwal, $tanggalAkhir, $pengunjungBaru, $pengunjungLama, $total, $hospitalInfo)
    {
        $pdf = PDF::loadView('rm.laporan_rm.rl34_pengunjung_pdf', [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'pengunjungBaru' => $pengunjungBaru,
            'pengunjungLama' => $pengunjungLama,
            'total' => $total,
            'hospitalInfo' => $hospitalInfo
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('disable-smart-shrinking', true);
        
        $filename = 'rl34_Pengunjung_' . date('d-m-Y', strtotime($tanggalAwal)) . '_sd_' . date('d-m-Y', strtotime($tanggalAkhir)) . '.pdf';
        
        return $pdf->download($filename);
    }

    // Akhir dari RL 3.4 Pengunjung 
    ////////////////////////////////////////////////////////////////

}
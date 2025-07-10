<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\PasienMeninggalDataSheet;
use App\Exports\Sheets\PasienMeninggalSummarySheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PasienMeninggalExport implements WithMultipleSheets
{
    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected $bangsal;

    public function __construct($tanggalAwal, $tanggalAkhir, $bangsal = null)
    {
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->bangsal = $bangsal;
    }

    public function sheets(): array
    {
        // Get data
        $data = $this->getPasienMeninggalData();
        $totalData = $this->hitungTotalDiagnosa($data);
        
        return [
            new PasienMeninggalDataSheet($data),
            new PasienMeninggalSummarySheet($totalData),
        ];
    }

    protected function getPasienMeninggalData()
    {
        $query = DB::select("
            SELECT tgl_registrasi, no_rawat, no_rkm_medis, jenis_pasien, nm_pasien, 
                alamat, jk, no_ktp, tgl_lahir, umurdaftar, png_jawab, nm_penyakit, 
                kd_penyakit, prioritas, kd_dokter, nm_dokter, kd_sps, nm_sps, 
                status_lanjut, kd_kamar, kelas, tgl_masuk, jam_masuk, tgl_keluar, 
                jam_keluar, stts_pulang, kd_bangsal, nm_bangsal, kd_dokter_dpjp, 
                nm_dokter_dpjp, json_dpjp
            FROM laporan_sensus_pasien_ranap t
            WHERE t.tgl_masuk >= ?
            AND t.tgl_masuk <= ?
            AND t.stts_pulang != '-'
            AND t.stts_pulang != 'Pindah Kamar'
            " . ($this->bangsal ? "AND t.kd_bangsal = ?" : "") . "
            GROUP BY t.no_rawat
            ORDER BY t.tgl_masuk ASC, t.no_rkm_medis ASC, -t.prioritas DESC
        ", $this->bangsal ? [$this->tanggalAwal, $this->tanggalAkhir, $this->bangsal] : [$this->tanggalAwal, $this->tanggalAkhir]);
        
        return collect($query)->map(function ($item, $index) {
            // Hitung selisih waktu untuk menentukan meninggal < 48 jam atau >= 48 jam
            $waktuMasuk = Carbon::parse($item->tgl_masuk . ' ' . $item->jam_masuk);
            $waktuKeluar = Carbon::parse($item->tgl_keluar . ' ' . $item->jam_keluar);
            $selisihJam = $waktuMasuk->diffInHours($waktuKeluar);
            $kurangDari48Jam = $selisihJam < 48;
            
            // Hitung umur dari tanggal lahir
            $umur = null;
            if (!empty($item->tgl_lahir)) {
                try {
                    $tglLahir = Carbon::parse($item->tgl_lahir);
                    $umur = $tglLahir->age;
                } catch (\Exception $e) {
                    $umur = null;
                }
            }
            
            return (object)[
                'no' => $index + 1,
                'tgl_masuk' => $item->tgl_masuk,
                'no_rkm_medis' => $item->no_rkm_medis,
                'nm_pasien' => $item->nm_pasien,
                'jk' => $item->jk,
                'tgl_lahir' => $item->tgl_lahir,
                'umur' => $umur,
                'png_jawab' => $item->png_jawab,
                'meninggal_kurang_48jam' => ($item->stts_pulang == 'Meninggal' && $kurangDari48Jam) ? 'Ya' : '-',
                'meninggal_lebih_48jam' => ($item->stts_pulang == 'Meninggal' && !$kurangDari48Jam) ? 'Ya' : '-',
                'kd_penyakit' => $item->kd_penyakit,
                'nm_penyakit' => $item->nm_penyakit,
                'item' => $item,
            ];
        });
    }

    protected function hitungTotalDiagnosa($data)
    {
        $diagnosaMap = [];
        
        foreach ($data as $item) {
            $diagnosaKey = $item->kd_penyakit;
            $diagnosaText = $item->nm_penyakit . ' (' . $item->kd_penyakit . ')';
            
            if (!isset($diagnosaMap[$diagnosaKey])) {
                $diagnosaMap[$diagnosaKey] = [
                    'diagnosa' => $diagnosaText,
                    'laki' => 0,
                    'perempuan' => 0,
                    'umur_lt_1' => 0,
                    'umur_lt_4' => 0,
                    'umur_lt_9' => 0,
                    'umur_lt_14' => 0,
                    'umur_lt_19' => 0,
                    'umur_lt_44' => 0,
                    'umur_lt_54' => 0,
                    'umur_lt_59' => 0,
                    'umur_lt_69' => 0,
                    'umur_lt_70' => 0,
                    'umur_null' => 0,
                    'meninggal_kurang_48jam' => 0,
                    'meninggal_lebih_48jam' => 0,
                ];
            }
            
            // Hitung jenis kelamin
            if ($item->jk == 'L') {
                $diagnosaMap[$diagnosaKey]['laki']++;
            } else if ($item->jk == 'P') {
                $diagnosaMap[$diagnosaKey]['perempuan']++;
            }
            
            // Hitung kelompok umur
            if ($item->umur === null) {
                $diagnosaMap[$diagnosaKey]['umur_null']++;
            } else if ($item->umur < 1) {
                $diagnosaMap[$diagnosaKey]['umur_lt_1']++;
            } else if ($item->umur < 4) {
                $diagnosaMap[$diagnosaKey]['umur_lt_4']++;
            } else if ($item->umur < 9) {
                $diagnosaMap[$diagnosaKey]['umur_lt_9']++;
            } else if ($item->umur < 14) {
                $diagnosaMap[$diagnosaKey]['umur_lt_14']++;
            } else if ($item->umur < 19) {
                $diagnosaMap[$diagnosaKey]['umur_lt_19']++;
            } else if ($item->umur < 44) {
                $diagnosaMap[$diagnosaKey]['umur_lt_44']++;
            } else if ($item->umur < 54) {
                $diagnosaMap[$diagnosaKey]['umur_lt_54']++;
            } else if ($item->umur < 59) {
                $diagnosaMap[$diagnosaKey]['umur_lt_59']++;
            } else if ($item->umur < 69) {
                $diagnosaMap[$diagnosaKey]['umur_lt_69']++;
            } else {
                $diagnosaMap[$diagnosaKey]['umur_lt_70']++;
            }
            
            // Hitung meninggal
            if ($item->meninggal_kurang_48jam == 'Ya') {
                $diagnosaMap[$diagnosaKey]['meninggal_kurang_48jam']++;
            }
            
            if ($item->meninggal_lebih_48jam == 'Ya') {
                $diagnosaMap[$diagnosaKey]['meninggal_lebih_48jam']++;
            }
        }
        
        // Convert to array with index
        $result = [];
        $index = 1;
        foreach ($diagnosaMap as $key => $value) {
            $value['no'] = $index++;
            $result[] = $value;
        }
        
        return $result;
    }
}
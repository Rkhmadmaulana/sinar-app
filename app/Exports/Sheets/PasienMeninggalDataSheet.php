<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PasienMeninggalDataSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }
    
    public function headings(): array
    {
        return [
            'No',
            'Tanggal Masuk',
            'Rekam Medis',
            'Nama Pasien',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Umur',
            'Penanggung Jawab',
            'Meninggal < 48 Jam',
            'Meninggal >= 48 Jam',
        ];
    }
    
    public function map($item): array
    {
        return [
            $item->no,
            $item->tgl_masuk,
            $item->no_rkm_medis,
            $item->nm_pasien,
            $item->jk == 'L' ? 'Laki-laki' : 'Perempuan',
            $item->tgl_lahir,
            $item->umur !== null ? $item->umur : '-',
            $item->png_jawab,
            $item->meninggal_kurang_48jam,
            $item->meninggal_lebih_48jam,
        ];
    }
    
    public function title(): string
    {
        return 'Data Pasien Meninggal';
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
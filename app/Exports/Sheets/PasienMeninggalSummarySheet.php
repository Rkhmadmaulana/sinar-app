<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class PasienMeninggalSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithEvents
{
    protected $totalData;

    public function __construct($totalData)
    {
        $this->totalData = collect($totalData);
    }

    public function collection()
    {
        return $this->totalData;
    }
    
    public function headings(): array
    {
        return [
            'No',
            'Diagnosa',
            'L',
            'P',
            '<1',
            '<4',
            '<9',
            '<14',
            '<19',
            '<44',
            '<54',
            '<59',
            '<69',
            '≥70',
            'Null',
            'Meninggal < 48 Jam',
            'Meninggal >= 48 Jam',
        ];
    }
    
    public function title(): string
    {
        return 'Ringkasan Diagnosa';
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRow = count($this->totalData) + 1; // +1 karena ada header
                $totalRow = $lastRow + 1;
                
                // Tambahkan baris total
                $event->sheet->setCellValue('A'.$totalRow, '');
                $event->sheet->setCellValue('B'.$totalRow, 'Total');
                
                // Hitung total untuk setiap kolom
                $columns = ['C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'];
                foreach ($columns as $column) {
                    $event->sheet->setCellValue(
                        $column.$totalRow, 
                        '=SUM('.$column.'2:'.$column.$lastRow.')'
                    );
                }
                
                // Format baris total
                $event->sheet->getStyle('A'.$totalRow.':Q'.$totalRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEEEEEE'],
                    ],
                ]);
            },
        ];
    }
}
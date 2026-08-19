<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SignalsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected Collection $units;

    public function __construct(Collection $units)
    {
        $this->units = $units;
    }

    public function collection()
    {
        return $this->units;
    }

    public function headings(): array
    {
        return [
            'كود الإشارة',
            'مضمون الإشارة',
            'نوع الإشارة',
            'موضوع الإشارة',
            'تاريخ الإشارة',
            'توقيت الإشارة',
            'متلقي الإشارة',
        ];
    }

    public function map($unit): array
    {
        return [
            $unit->MainSignalCode,
            $unit->UNIT_SIGNAL_CONTENT,
            $unit->UNIT_SIGNAL_TYPE,
            $unit->UNIT_SIGNAL_SUBJECT,
            $unit->UNIT_SIGNAL_DATE,
            $unit->UNIT_SIGNAL_TIME,
            $unit->receiver_name,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '001F3F'],
                ],
            ],
        ];
    }
}
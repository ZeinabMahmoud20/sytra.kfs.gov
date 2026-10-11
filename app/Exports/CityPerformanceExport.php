<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CityPerformanceExport implements FromCollection, WithHeadings, WithStyles
{
    protected Collection $cities;

    public function __construct(Collection $cities)
    {
        $this->cities = $cities;
    }

    public function headings(): array
    {
        return [
            'م',
            'المركز / المدينة',
            'إجمالي البلاغات',
            'تم استلام البلاغ',
            'قيد المعالجة',
            'تم التنفيذ',
            'تم الانتهاء',
            'نسبة الإنجاز',
        ];
    }

    public function collection()
    {
        return $this->cities->map(fn ($city, $index) => [
            $index + 1,
            $city->CITY_NAME,
            $city->total_count,
            $city->received_count,
            $city->processing_count,
            $city->executed_count,
            $city->finished_count,
            $city->completion_rate . '%',
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '001F3F'],
            ]],
        ];
    }
}

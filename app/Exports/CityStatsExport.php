<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CityStatsExport implements FromCollection, WithHeadings, WithStyles
{
    protected Collection $cities;

    protected array $totals;

    protected int $month;

    protected int $year;

    public function __construct(Collection $cities, array $totals, int $month, int $year)
    {
        $this->cities = $cities;
        $this->totals = $totals;
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * نفس ترتيب أعمدة جدول إحصائيات المراكز والمدن.
     */
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
            'نسبة التنفيذ',
        ];
    }

    public function collection()
    {
        $rows = $this->cities->map(fn ($city, $index) => [
            $index + 1,
            $city->CITY_NAME,
            $city->total_count,
            $city->received_count,
            $city->processing_count,
            $city->executed_count,
            $city->finished_count,
            $city->completion_rate . '%',
        ]);

        $rows->push([
            '',
            'الإجمالي',
            $this->totals['total'],
            $this->totals['received'],
            $this->totals['processing'],
            $this->totals['executed'],
            $this->totals['finished'],
            '',
        ]);

        return $rows;
    }

    /**
     * تنسيق بسيط: صف الهيدر Bold وبخلفية مميزة، وصف الإجمالي Bold.
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->cities->count() + 2;

        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '001F3F'],
            ]],
            $lastRow => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'],
            ]],
        ];
    }
}

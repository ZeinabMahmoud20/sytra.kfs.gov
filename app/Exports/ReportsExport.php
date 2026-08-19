<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected Collection $reports;

    public function __construct(Collection $reports)
    {
        $this->reports = $reports;
    }

    public function collection()
    {
        return $this->reports;
    }

    /**
     * نفس ترتيب الأعمدة الموجود في صفحة قائمة البلاغات بالظبط.
     */
    public function headings(): array
    {
        return [
            'الرقم القومي',
            'رقم قيد البلاغ',
            'متلقي البلاغ',
            'اسم المبلغ',
            'جهة البلاغ',
            'نوع البلاغ',
            'المركز',
            'مكان البلاغ',
            'تاريخ تقديم البلاغ',
            'وقت تقديم البلاغ',
            'عدد المصابين',
            'عدد الوفيات',
            'حالة البلاغ',
            'تاريخ انتهاء البلاغ',
            'وقت انتهاء البلاغ',
            'رقم تليفون',
        ];
    }

    public function map($report): array
    {
        return [
            $report->REPORTER_SSN,
            $report->REPORT_REGISTER_NUMBER,
            $report->user->name ?? 'online',
            $report->REPORTER_NAME,
            $report->REPORTING_Auth ?? optional($report->reportingType)->AUTHORITY,
            $report->reportingType->REPORT_SORT ?? '-',
            $report->city->CITY_NAME ?? '-',
            $report->village->VILLAGE_NAME ?? '-',
            $report->REPORT_START_DATE,
            $report->REPORT_START_TIME,
            $report->INFECTED_NUM ?? 0,
            $report->Deceased_Num ?? 0,
            $report->REQUEST_STATUS,
            $report->REPORT_END_DATE,
            $report->REPORT_END_TIME,
            $report->REPORT_FOLLOWUP_NUMBER,
        ];
    }

    /**
     * تنسيق بسيط: صف الهيدر Bold وبخلفية مميزة.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '001F3F'],
            ], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}
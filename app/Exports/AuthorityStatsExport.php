<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuthorityStatsExport implements FromCollection, WithHeadings, WithStyles
{
    protected Collection $authorities;

    public function __construct(Collection $authorities)
    {
        $this->authorities = $authorities;
    }

    public function headings(): array
    {
        return [
            'م',
            'جهة البلاغ',
            'عدد البلاغات',
            'عدد القرى',
            'أكثر قرية',
            'أكثر نوع بلاغ',
        ];
    }

    public function collection()
    {
        return $this->authorities->map(fn ($item, $index) => [
            $index + 1,
            $item['auth'],
            $item['total'],
            $item['village_count'],
            collect($item['top_villages'])->map(fn ($v) => $v['village'] . ' (' . $v['count'] . ')')->implode('، '),
            collect($item['top_types'])->map(fn ($t) => $t['type'] . ' (' . $t['count'] . ')')->implode('، '),
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

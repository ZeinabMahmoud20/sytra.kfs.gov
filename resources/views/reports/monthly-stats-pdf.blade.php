<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 8mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #ccc; padding: 4px 5px; text-align: right; word-wrap: break-word; }
        th { background-color: #001f3f; color: #fff; }
        h2 { text-align: center; color: #001f3f; font-size: 15px; margin-bottom: 4px; }
        .meta { text-align: center; color: #475569; margin-bottom: 6px; }
        .summary { margin-top: 10px; width: 100%; }
        .summary td { border: none; padding: 2px 4px; }
    </style>
</head>
<body>
    <h2>{{ $pageTitle }}</h2>
    <p class="meta">
        {{ $monthName }} {{ $year }} &nbsp;-&nbsp; {{ $printedAtLabel }} {{ now()->format('Y-m-d H:i') }}
        &nbsp;-&nbsp; {{ $totalLabel }} {{ $totals['total'] }}
    </p>

    <table>
        <thead>
            <tr>
                @foreach ($tableHeaders as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $index => $row)
                @php
                    $cells = [
                        $index + 1,
                        $row['name'],
                        $row['total_count'],
                        $row['received_count'],
                        $row['processing_count'],
                        $row['executed_count'],
                        $row['finished_count'],
                        $row['completion_rate'] . '%',
                    ];
                @endphp
                <tr>
                    @foreach (array_reverse($cells) as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $totalCells = [
                    '',
                    $totalRowLabel,
                    $totals['total'],
                    $totals['received'],
                    $totals['processing'],
                    $totals['executed'],
                    $totals['finished'],
                    '',
                ];
            @endphp
            <tr style="background-color:#e2e8f0; font-weight:bold;">
                @foreach (array_reverse($totalCells) as $cell)
                    <td>{{ $cell }}</td>
                @endforeach
            </tr>
        </tfoot>
    </table>
</body>
</html>

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
    </style>
</head>
<body>
    <h2>{{ $pageTitle }}</h2>
    <p class="meta">
        {{ $printedAtLabel }} {{ now()->format('Y-m-d H:i') }}
        &nbsp;-&nbsp; {{ $totalLabel }} {{ $rows->count() }}
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
                        $row['auth'],
                        $row['total'],
                        $row['village_count'],
                        $row['top_villages'],
                        $row['top_types'],
                    ];
                @endphp
                <tr>
                    @foreach (array_reverse($cells) as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

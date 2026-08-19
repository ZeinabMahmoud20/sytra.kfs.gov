<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 8mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #ccc; padding: 3px 4px; text-align: right; word-wrap: break-word; }
        th { background-color: #001f3f; color: #fff; }
        h2 { text-align: center; color: #001f3f; font-size: 14px; }
    </style>
</head>
<body>
    <h2>{{ $pageTitle }}</h2>
    <p>{{ $printedAtLabel }} {{ now()->format('Y-m-d H:i') }} - {{ $countLabel }} {{ $reports->count() }}</p>

    <table>
        <thead>
            <tr>
                @foreach ($tableHeaders as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($reports as $report)
                <tr>
                    <td>{{ $report->REPORT_FOLLOWUP_NUMBER }}</td>
                    <td>{{ $report->REPORT_END_TIME }}</td>
                    <td>{{ $report->REPORT_END_DATE }}</td>
                    <td>{{ $report->REQUEST_STATUS }}</td>
                    <td style="text-align:center">{{ $report->Deceased_Num ?? 0 }}</td>
                    <td style="text-align:center">{{ $report->INFECTED_NUM ?? 0 }}</td>
                    <td>{{ $report->REPORT_START_TIME }}</td>
                    <td>{{ $report->REPORT_START_DATE }}</td>
                    <td>{{ $report->village->VILLAGE_NAME ?? '-' }}</td>
                    <td>{{ $report->city->CITY_NAME ?? '-' }}</td>
                    <td>{{ $report->reportingType->REPORT_SORT ?? '-' }}</td>
                    <td>{{ $report->REPORTING_Auth ?: optional($report->reportingType)->AUTHORITY }}</td>
                    <td>{{ $report->REPORTER_NAME }}</td>
                    <td>{{ $report->user->name ?? 'online' }}</td>
                    <td>{{ $report->REPORT_REGISTER_NUMBER }}</td>
                    <td>{{ $report->REPORTER_SSN }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

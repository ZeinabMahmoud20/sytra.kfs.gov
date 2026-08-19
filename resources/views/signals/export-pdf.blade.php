<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: right; }
        th { background-color: #001f3f; color: #fff; }
        h2 { text-align: center; color: #001f3f; }
    </style>
</head>
<body>
    <h2>تقرير الإشارات - الشبكة الوطنية للطوارئ بكفر الشيخ</h2>
    <p>تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }} - عدد النتائج: {{ $units->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>كود الإشارة</th>
                <th>مضمون الإشارة</th>
                <th>نوع الإشارة</th>
                <th>موضوع الإشارة</th>
                <th>تاريخ الإشارة</th>
                <th>توقيت الإشارة</th>
                <th>متلقي الإشارة</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($units as $unit)
                <tr>
                    <td>{{ $unit->MainSignalCode }}</td>
                    <td>{{ $unit->UNIT_SIGNAL_CONTENT }}</td>
                    <td>{{ $unit->UNIT_SIGNAL_TYPE }}</td>
                    <td>{{ $unit->UNIT_SIGNAL_SUBJECT }}</td>
                    <td>{{ $unit->UNIT_SIGNAL_DATE }}</td>
                    <td>{{ $unit->UNIT_SIGNAL_TIME }}</td>
                    <td>{{ $unit->receiver_name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
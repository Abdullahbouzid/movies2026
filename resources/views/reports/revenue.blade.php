<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تقرير الإيرادات {{ $from }} — {{ $to }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: "Tajawal", "Segoe UI", Tahoma, sans-serif;
            margin: 0; padding: 32px; color: #111827; background: #fff;
        }
        h1 { font-size: 22px; margin: 0 0 4px; }
        .meta { color: #6b7280; font-size: 12px; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 13px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: right; }
        th { background: #f9fafb; font-weight: 600; }
        tfoot td { font-weight: 700; background: #f9fafb; }
        h2 { font-size: 16px; margin: 24px 0 8px; }
        .toolbar { margin-bottom: 20px; }
        button {
            font: inherit; padding: 8px 16px; border-radius: 8px; border: 1px solid #d1d5db;
            background: #dc2626; color: #fff; cursor: pointer;
        }
        @media print {
            body { padding: 0; }
            .toolbar { display: none; }
            h2 { page-break-after: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">طباعة / حفظ PDF</button>
    </div>

    <h1>سينما بلاس — تقرير الإيرادات</h1>
    <p class="meta">
        الفترة: {{ $from }} إلى {{ $to }} — تم الإنشاء في {{ $generatedAt->format('Y-m-d H:i') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>الخدمة</th>
                <th>حجوزات مدفوعة</th>
                <th>المحصّل</th>
                <th>حجوزات معلّقة</th>
                <th>المعلّق</th>
                <th>إجمالي الحجوزات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($summary['rows'] as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ $row['paid_count'] }}</td>
                    <td>{{ \App\Support\Money::format($row['paid_total']) }}</td>
                    <td>{{ $row['pending_count'] }}</td>
                    <td>{{ \App\Support\Money::format($row['pending_total']) }}</td>
                    <td>{{ $row['bookings'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>الإجمالي</td>
                <td>{{ $summary['totals']['paid_count'] }}</td>
                <td>{{ \App\Support\Money::format($summary['totals']['paid_total']) }}</td>
                <td>{{ $summary['totals']['pending_count'] }}</td>
                <td>{{ \App\Support\Money::format($summary['totals']['pending_total']) }}</td>
                <td>{{ $summary['totals']['bookings'] }}</td>
            </tr>
        </tfoot>
    </table>

    <h2>أفضل الأفلام</h2>
    @if ($topMovies->isEmpty())
        <p class="meta">لا توجد بيانات في هذه الفترة.</p>
    @else
        <table>
            <thead>
                <tr><th>الفيلم</th><th>عدد الحجوزات</th><th>الإيراد</th></tr>
            </thead>
            <tbody>
                @foreach ($topMovies as $row)
                    <tr>
                        <td>{{ $row->movie?->name ?? '—' }}</td>
                        <td>{{ $row->bookings }}</td>
                        <td>{{ \App\Support\Money::format((float) $row->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>أفضل الألعاب</h2>
    @if ($topGames->isEmpty())
        <p class="meta">لا توجد بيانات في هذه الفترة.</p>
    @else
        <table>
            <thead>
                <tr><th>اللعبة</th><th>عدد الحجوزات</th><th>الإيراد</th></tr>
            </thead>
            <tbody>
                @foreach ($topGames as $row)
                    <tr>
                        <td>{{ $row->game?->name ?? '—' }}</td>
                        <td>{{ $row->bookings }}</td>
                        <td>{{ \App\Support\Money::format((float) $row->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>التحصيل اليومي</h2>
    <table>
        <thead>
            <tr><th>التاريخ</th><th>المحصّل</th></tr>
        </thead>
        <tbody>
            @foreach ($dailySeries as $day => $total)
                <tr>
                    <td>{{ $day }}</td>
                    <td>{{ \App\Support\Money::format($total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

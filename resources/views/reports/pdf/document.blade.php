@use('App\Support\Settings')
@use('App\Support\Format')

@php
    $association = Settings::associationName();
    $phone = Settings::get('association_phone');
    $email = Settings::get('association_email');
    $footer = Settings::get('report_footer');
    $logo = 'images/logo.jpeg';
    $hasLogo = $logo !== null;
    $logo = $logo ? str_replace('\\', '/', public_path($logo)) : null;
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #292524;
            font-family: notonaskh, sans-serif;
            font-size: 8.5pt;
            line-height: 1.7;
            direction: rtl;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #facc15;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; border: 0; padding: 0; }
        .brand { text-align: right; }
        .brand h1 { margin: 0; font-size: 14pt; color: #1c1917; }
        .brand p { margin: 2px 0 0; font-size: 7.5pt; color: #78716c; }
        .logo { width: 46px; height: 46px; }
        .logo-fallback {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #facc15;
            color: #1c1917;
            text-align: center;
            line-height: 44px;
            font-size: 17pt;
            font-weight: bold;
        }
        .meta { text-align: left; font-size: 7.5pt; color: #78716c; line-height: 1.8; }
        .report-title {
            margin: 0 0 3px;
            font-size: 13pt;
            color: #1c1917;
        }
        .report-desc { margin: 0 0 8px; color: #57534e; font-size: 8pt; }
        .period {
            background: #fefce8;
            border: 1px solid #fde68a;
            border-radius: 5px;
            padding: 5px 8px;
            font-size: 7.5pt;
            color: #713f12;
            margin-bottom: 12px;
        }
        .summary { width: 100%; border-collapse: separate; border-spacing: 5px 0; margin-bottom: 12px; }
        .summary td {
            width: 25%;
            border: 1px solid #e7e5e4;
            border-radius: 6px;
            padding: 7px 8px;
            background: #fafaf9;
            vertical-align: top;
        }
        .summary .label { display: block; color: #78716c; font-size: 7pt; }
        .summary .value { display: block; margin-top: 2px; color: #1c1917; font-size: 10pt; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.data thead th {
            background: #1c1917;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            padding: 6px 5px;
            border: 1px solid #1c1917;
            text-align: right;
        }
        table.data tbody td {
            padding: 5px;
            border: 1px solid #e7e5e4;
            font-size: 7.5pt;
            vertical-align: top;
        }
        table.data tbody tr:nth-child(even) td { background: #fafaf9; }
        table.totals { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.totals td {
            border: 1px solid #e7e5e4;
            background: #fefce8;
            padding: 6px 8px;
            text-align: center;
        }
        table.totals .label { display: block; color: #78716c; font-size: 7pt; }
        table.totals .value { display: block; color: #1c1917; font-size: 9pt; font-weight: bold; }
        .footer {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #e7e5e4;
            color: #a8a29e;
            font-size: 7pt;
            text-align: center;
        }
        .muted { color: #78716c; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="brand" style="width: 70%;">
                    <table class="header-table">
                        <tr>
                            <td style="width: 52px;">
                                @if ($hasLogo)
                                    <img src="{{ $logo }}" class="logo" alt="شعار الجمعية">
                                @else
                                    <div class="logo-fallback">ت</div>
                                @endif
                            </td>
                            <td>
                                <h1>{{ $association }}</h1>
                                <p>
                                    @if ($phone) {{ $phone }} @endif
                                    @if ($phone && $email) &nbsp;·&nbsp; @endif
                                    @if ($email) {{ $email }} @endif
                                    @if (! $phone && ! $email) تقرير داخلي @endif
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="meta" style="width: 30%;">
                    تاريخ الإصدار: {{ Format::dateTime(now()) }}<br>
                    أُنشئ بواسطة: {{ $report['generated_by'] ?? '—' }}
                </td>
            </tr>
        </table>
    </div>

    <h2 class="report-title">{{ $report['title'] }}</h2>
    <p class="report-desc">{{ $report['description'] }}</p>
    <div class="period">الفترة: {{ $report['period'] }}</div>

    @if (count($report['summary']) > 0)
        <table class="summary">
            <tr>
                @foreach ($report['summary'] as $item)
                    <td>
                        <span class="label">{{ $item['label'] }}</span>
                        <span class="value">{{ $item['value'] }}</span>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    @if (count($report['rows']) > 0)
        <table class="data">
            <thead>
                <tr>
                    @foreach ($report['columns'] as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($report['rows'] as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                @foreach ($report['totals'] as $label => $value)
                    <td>
                        <span class="label">{{ $label }}</span>
                        <span class="value">{{ $value }}</span>
                    </td>
                @endforeach
            </tr>
        </table>
    @else
        <p class="muted">لا توجد بيانات مطابقة للفلاتر المحددة.</p>
    @endif

    <div class="footer">
        {{ $footer ?: $association }} · تم إنشاء هذا التقرير آليًا من نظام إدارة المستودع.
    </div>
</body>
</html>

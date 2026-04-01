<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Research Papers Report</title>
    <style>
        @page {
            header: html_Header;
            footer: html_Footer;
            margin-top: 90px;
            margin-bottom: 70px;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 12.5pt;
            line-height: 1.45;
            color: #222;
        }
        h1 {
            font-size: 20pt;
            margin: 0 0 4px 0;
        }
        .muted {
            color: #555;
        }
        .small {
            font-size: 11.5pt;
        }
        .section-title {
            background: #F8FAFC;
            color: #0F172A;
            padding: 6px 14px 6px 12px;
            margin: 18px 0 8px;
            font-weight: bold;
            border-radius: 8px;
            border: 1px solid #E2E8F0;
        }
        .summary-grid {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .summary-grid td {
            width: 25%;
            border: 1px solid #dde6f6;
            padding: 8px 10px;
            vertical-align: top;
        }
        .summary-label {
            font-size: 1em;
            color: #334155;
        }
        .summary-value {
            font-size: 1.15em;
            font-weight: bold;
            margin-top: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #dde6f6;
            padding: 8px 11px;
            vertical-align: top;
            font-size: 1em;
        }
        thead {
            background: #F1F5F9;
            color: #0F172A;
        }
        th {
            font-size: 1.02em;
            font-weight: bold;
        }
        .center {
            text-align: center;
        }
        .paper-title {
            font-weight: bold;
            color: #0f172a;
            line-height: 1.35;
            margin-bottom: 4px;
        }
        .paper-subline {
            margin-top: 4px;
            color: #334155;
            line-height: 1.35;
        }
        .paper-subline strong {
            color: #0f172a;
        }
        .author-group {
            margin-bottom: 4px;
            line-height: 1.35;
        }
        .meta-line {
            margin-top: 4px;
            line-height: 1.35;
        }
        .paper-list {
            margin-top: 10px;
        }
        .paper-card {
            border: 1px solid #dde6f6;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .paper-card-header {
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.4;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .paper-card-row {
            margin-top: 6px;
            line-height: 1.45;
        }
        .paper-card-row strong {
            color: #0f172a;
        }
    </style>
</head>
<body>
    @php
        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F') . ' ' . ($generatedAt->year + 543) . $generatedAt->translatedFormat(' เวลา H:i');
    @endphp

    <htmlpageheader name="Header">
        <div style="font-size: 16pt; color: black; padding-left: 10px;">
            Research Papers and Journal Articles
        </div>
    </htmlpageheader>

    <htmlpagefooter name="Footer">
        <div style="width: 100%; font-size: 12pt; border: none; padding: 4px 0;">
            <div style="text-align: right;">
                หน้า {PAGENO} จาก {nbpg} <br>
                โดย {{ $reporter }} : {{ $generatedLabel }}<br>
            </div>
        </div>
    </htmlpagefooter>

    <h1>รายงาน Research Papers and Journal Articles</h1>
    <div class="muted small">
        วันที่ออกรายงาน: {{ $generatedLabel }} | ผู้สร้างรายงาน: {{ $reporter }}
    </div>

    @if ($filters->isNotEmpty())
        <div class="section-title">เงื่อนไขการค้นหา</div>
        <div class="small muted" style="margin-bottom: 12px;">{{ $filters->implode(' | ') }}</div>
    @endif

    <div class="section-title">สรุปภาพรวม</div>
    <table class="summary-grid">
        <tr>
            <td>
                <div class="summary-label">จำนวน Papers</div>
                <div class="summary-value">{{ number_format($summary['total_papers']) }}</div>
            </td>
            <td>
                <div class="summary-label">Unique Authors</div>
                <div class="summary-value">{{ number_format($summary['unique_authors']) }}</div>
            </td>
            <td>
                <div class="summary-label">Top Author</div>
                <div class="summary-value" style="font-size: 13pt;">{{ $summary['top_author'] ?? '-' }}</div>
            </td>
            <td>
                <div class="summary-label">Top Journal</div>
                <div class="summary-value" style="font-size: 13pt;">{{ $summary['top_journal'] ?? '-' }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">รายการบทความ</div>
    <div class="paper-list">
        @forelse ($papers as $index => $paper)
            <div class="paper-card">
                <div class="paper-card-header">{{ $index + 1 }}. {{ $paper->title }}</div>

                @if ($paper->impact_factor)
                    <div class="paper-card-row"><strong>Impact Factor:</strong> {{ $paper->impact_factor }}</div>
                @endif

                <div class="paper-card-row">
                    <strong>Authors:</strong>
                    @forelse ($paper->authors_by_role as $role => $names)
                        <div class="author-group">
                            <strong>{{ ['first' => 'First Author', 'co' => 'Co Author', 'corresponding' => 'Corresponding Author'][$role] ?? ucfirst($role) }}:</strong>
                            {{ $names->implode(', ') }}
                        </div>
                    @empty
                        -
                    @endforelse
                </div>

                <div class="paper-card-row"><strong>Journal:</strong> {{ $paper->journal }}</div>
                <div class="paper-card-row"><strong>Quartile:</strong> {{ $paper->quartile }} | <strong>ปี:</strong> {{ $paper->year_be ?: '-' }}</div>
                <div class="paper-card-row">
                    <strong>Project:</strong> {{ $paper->project_label }}
                    @if ($paper->project_code)
                        <span class="muted">({{ $paper->project_code }})</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="small muted">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div>
        @endforelse
    </div>
</body>
</html>

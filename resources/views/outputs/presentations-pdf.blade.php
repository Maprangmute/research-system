<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Presentations Report</title>
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
        .muted { color: #555; }
        .small { font-size: 11.5pt; }
        .section-title {
            background: #F8FAFC;
            color: #0F172A;
            padding: 6px 14px 6px 12px;
            margin: 18px 0 8px;
            font-weight: bold;
            border-radius: 8px;
            border: 1px solid #E2E8F0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #dde6f6;
            padding: 7px 10px;
            vertical-align: top;
            font-size: 1em;
        }
        thead {
            background: #F1F5F9;
            color: #0F172A;
        }
        .summary-grid {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .summary-grid td { width: 25%; }
        .summary-label { font-size: 1em; color: #334155; }
        .summary-value { font-size: 1.15em; font-weight: bold; margin-top: 4px; }
        .center { text-align: center; }
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
            Presentations in Academic Conferences
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

    <h1>รายงาน Presentations in Academic Conferences</h1>
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
                <div class="summary-label">Presentations</div>
                <div class="summary-value">{{ number_format($summary['total_presentations']) }}</div>
            </td>
            <td>
                <div class="summary-label">Presenters</div>
                <div class="summary-value">{{ number_format($summary['unique_presenters']) }}</div>
            </td>
            <td>
                <div class="summary-label">Top Presenter</div>
                <div class="summary-value" style="font-size: 13pt;">{{ $summary['top_presenter'] ?? '-' }}</div>
            </td>
            <td>
                <div class="summary-label">Top Conference</div>
                <div class="summary-value" style="font-size: 13pt;">{{ $summary['top_conference'] ?? '-' }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">รายการการนำเสนอ</div>
    <div class="paper-list">
        @forelse ($presentations as $index => $row)
            <div class="paper-card">
                <div class="paper-card-header">{{ $index + 1 }}. {{ $row->title }}</div>

                <div class="paper-card-row"><strong>Presenter:</strong> {{ $row->presenter_name }}</div>
                <div class="paper-card-row"><strong>Activity:</strong> {{ $row->presentation_type }}</div>
                <div class="paper-card-row"><strong>Conference:</strong> {{ $row->conference_name }}</div>
                @if ($row->location !== '-')
                    <div class="paper-card-row"><strong>Location:</strong> {{ $row->location }}</div>
                @endif
                <div class="paper-card-row"><strong>ปี:</strong> {{ $row->year_be ?: '-' }}</div>
                @if (!empty($row->detail))
                    <div class="paper-card-row"><strong>รายละเอียด / ลิงก์:</strong> {{ $row->detail }}</div>
                @endif
                <div class="paper-card-row">
                    <strong>Project:</strong> {{ $row->project_label }}
                    @if ($row->project_code)
                        <span class="muted">({{ $row->project_code }})</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="small muted">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div>
        @endforelse
    </div>
</body>
</html>

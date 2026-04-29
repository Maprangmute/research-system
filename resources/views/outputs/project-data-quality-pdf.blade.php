<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Project Data Quality Report</title>
    <style>
        @page {
            header: html_Header;
            footer: html_Footer;
            margin-top: 86px;
            margin-bottom: 62px;
            margin-left: 24px;
            margin-right: 24px;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 12.6pt;
            line-height: 1.45;
            color: #1e293b;
        }
        .muted { color: #64748b; }
        .header-line {
            border-bottom: 1px solid #dbe7f5;
            padding-bottom: 6px;
        }
        .hero-shell {
            margin-top: 12px;
            border: 1px solid #dbe7f5;
            border-radius: 18px;
            background: linear-gradient(135deg, #fff7ed 0%, #ffffff 48%, #f8fbff 100%);
            padding: 16px 18px;
        }
        .report-kicker {
            font-size: 11pt;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #92400e;
        }
        .report-title {
            margin-top: 6px;
            font-size: 23pt;
            font-weight: bold;
            line-height: 1.16;
            color: #0f172a;
        }
        .report-subtitle {
            margin-top: 6px;
            font-size: 12.4pt;
            color: #475569;
            line-height: 1.45;
        }
        .hero-meta {
            margin-top: 10px;
            font-size: 11.6pt;
            color: #64748b;
            line-height: 1.55;
        }
        .chip {
            display: inline-block;
            margin-right: 6px;
            margin-bottom: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #dbe7f5;
            background: #f8fafc;
            font-size: 10.8pt;
            color: #334155;
        }
        .section-title {
            margin: 18px 0 8px;
            padding: 8px 14px 8px 12px;
            border-radius: 10px;
            border: 1px solid #dbe7f5;
            border-left: 5px solid #f59e0b;
            background: linear-gradient(90deg, #fff7ed 0%, #f8faff 100%);
            color: #0f172a;
            font-size: 15.2pt;
            font-weight: bold;
            line-height: 1.2;
        }
        .section-subtitle {
            margin: -2px 0 10px;
            color: #64748b;
            font-size: 12pt;
        }
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0 0 14px;
        }
        .summary-grid td {
            width: 20%;
            border: 1px solid #dbe7f5;
            padding: 11px 12px;
            vertical-align: top;
        }
        .summary-label {
            color: #64748b;
            font-size: 11.2pt;
            font-weight: bold;
        }
        .summary-value {
            margin-top: 8px;
            font-size: 18pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.18;
        }
        .project-card {
            border: 1px solid #dbe7f5;
            border-radius: 14px;
            margin-bottom: 12px;
            padding: 13px 15px;
            page-break-inside: avoid;
            background: #ffffff;
        }
        .project-card:nth-child(even) {
            background: #fcfdff;
        }
        .project-head {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0 0 8px;
        }
        .project-head td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }
        .project-index {
            width: 34px;
            color: #64748b;
            font-weight: bold;
            font-size: 13.6pt;
        }
        .project-title {
            font-size: 17.6pt;
            font-weight: bold;
            line-height: 1.28;
            color: #0f172a;
        }
        .meta-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 8px;
        }
        .meta-grid td {
            border: 1px solid #e2e8f0;
            padding: 8px 9px;
            vertical-align: top;
            background: #f8fafc;
        }
        .label {
            font-size: 10.6pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .value {
            margin-top: 3px;
            font-size: 12.2pt;
            font-weight: bold;
            color: #1e293b;
            line-height: 1.42;
        }
        .issue-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 10px;
        }
        .issue-table th {
            border: 1px solid #dbe7f5;
            background: #f8fafc;
            color: #334155;
            padding: 7px 8px;
            font-size: 12.6pt;
            text-align: left;
        }
        .issue-table td {
            border: 1px solid #dbe7f5;
            padding: 8px;
            vertical-align: top;
            font-size: 13pt;
            line-height: 1.5;
        }
        .toc-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 8px;
        }
        .toc-table th {
            border: 1px solid #dbe7f5;
            background: #f8fafc;
            color: #334155;
            padding: 8px 10px;
            font-size: 14pt;
            text-align: left;
        }
        .toc-table td {
            border: 1px solid #dbe7f5;
            padding: 8px 10px;
            vertical-align: top;
            font-size: 14.4pt;
            line-height: 1.5;
        }
        .toc-link {
            color: #0369a1;
            font-weight: bold;
            text-decoration: none;
        }
        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 999px;
            border: 1px solid #dbe7f5;
            font-size: 10.6pt;
            font-weight: bold;
        }
        .badge-duplicate { color:#be123c; background:#fff1f2; border-color:#fecdd3; }
        .badge-incomplete { color:#b45309; background:#fffbeb; border-color:#fde68a; }
        .badge-overdue { color:#b91c1c; background:#fef2f2; border-color:#fecaca; }
        .badge-no_output { color:#0369a1; background:#f0f9ff; border-color:#bae6fd; }
        .badge-other { color:#475569; background:#f8fafc; border-color:#e2e8f0; }
        .empty-state {
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            color: #64748b;
            background: #f8fafc;
        }
    </style>
</head>
<body>
    @php
        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F').' '.($generatedAt->year + 543).$generatedAt->translatedFormat(' เวลา H:i');
        $headerLogoPath = public_path('images/sihp-logo.png');
        $headerLogo = file_exists($headerLogoPath) ? 'file://'.$headerLogoPath : null;
        $severityLabel = fn ($score) => match ((int) $score) {
            4 => 'สูง',
            3 => 'กลาง',
            2 => 'ควรตรวจ',
            default => 'ติดตาม',
        };
    @endphp

    <htmlpageheader name="Header">
        <div class="header-line">
            <table style="width:100%; border-collapse:collapse; font-size:13.6pt; color:#0f172a;">
                <tr>
                    <td style="width:58%; text-align:left; vertical-align:middle;">
                        <table style="border-collapse:collapse;">
                            <tr>
                                @if ($headerLogo)
                                    <td style="vertical-align:middle; padding-right:7px;">
                                        <img src="{{ $headerLogo }}" alt="SiHP Logo" style="height:18px; width:auto; vertical-align:middle;">
                                    </td>
                                @endif
                                <td style="vertical-align:middle; font-size:13.2pt; color:#0f172a; font-weight:bold;">
                                    SiHP Research Database Management System
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:42%; text-align:right; vertical-align:middle; font-size:13.6pt; font-weight:bold; color:#0f172a;">
                        Project Data Quality
                    </td>
                </tr>
            </table>
        </div>
    </htmlpageheader>

    <htmlpagefooter name="Footer">
        <table style="width:100%; border-top:1px solid #dbe7f5; padding-top:6px; font-size:11.2pt; color:#64748b; border-collapse:collapse;">
            <tr>
                <td style="width:76%; text-align:left;">ผู้สร้างรายงาน {{ $reporter }} เวลา {{ $generatedLabel }}</td>
                <td style="width:24%; text-align:right; white-space:nowrap;">หน้า {PAGENO} / {nbpg}</td>
            </tr>
        </table>
    </htmlpagefooter>

    <div class="hero-shell">
        <div class="report-kicker">Project Data Quality</div>
        <div class="report-title">รายงานข้อมูลผิดพลาดของโปรเจค</div>
        <div class="report-subtitle">
            ตรวจข้อมูลซ้ำ ข้อมูลไม่ครบ วันที่ไม่ครบ Overdue โปรเจคที่ไม่มี output และสัญญาณอื่นที่กระทบ dashboard, FTE และรายงานผลงาน
        </div>
        <div class="hero-meta">
            วันที่ออกรายงาน {{ $generatedLabel }}<br>
            ผู้สร้างรายงาน {{ $reporter }}
        </div>
    </div>

    @if ($filters->isNotEmpty())
        <div class="section-title">เงื่อนไขการค้นหา</div>
        <div class="section-subtitle">รายการฟิลเตอร์ที่ถูกใช้ในการสร้างรายงานชุดนี้</div>
        @foreach ($filters as $filter)
            <span class="chip">{{ $filter }}</span>
        @endforeach
    @endif

    <div class="section-title">สรุปภาพรวม</div>
    <table class="summary-grid">
        <tr>
            <td style="background:#fff7ed;">
                <div class="summary-label">Projects</div>
                <div class="summary-value">{{ number_format($summary['projects']) }}</div>
            </td>
            <td style="background:#f8fafc;">
                <div class="summary-label">Issues</div>
                <div class="summary-value">{{ number_format($summary['issues']) }}</div>
            </td>
            <td style="background:#fef2f2;">
                <div class="summary-label">Overdue</div>
                <div class="summary-value">{{ number_format($summary['by_category']['overdue']['projects'] ?? 0) }}</div>
            </td>
            <td style="background:#f0f9ff;">
                <div class="summary-label">No Output</div>
                <div class="summary-value">{{ number_format($summary['by_category']['no_output']['projects'] ?? 0) }}</div>
            </td>
            <td style="background:#fffbeb;">
                <div class="summary-label">Incomplete</div>
                <div class="summary-value">{{ number_format($summary['by_category']['incomplete']['projects'] ?? 0) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">สารบัญรายการ</div>
    <div class="section-subtitle">คลิก Project ID เพื่อไปยังรายละเอียดของ record นั้นในรายงาน</div>
    <table class="toc-table">
        <thead>
            <tr>
                <th style="width:6%;">#</th>
                <th style="width:14%;">Project ID</th>
                <th style="width:62%;">ชื่อโครงการ</th>
                <th style="width:18%;">ระดับ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <a class="toc-link" href="#project-data-quality-{{ $index + 1 }}">
                            {{ $row->project_code }}
                        </a>
                    </td>
                    <td>{{ $row->project_name }}</td>
                    <td>{{ $severityLabel($row->severity_score) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">ไม่พบข้อมูลผิดพลาดตามเงื่อนไขที่เลือก</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">รายการโปรเจคที่ควรตรวจ</div>
    <div class="section-subtitle">แต่ละ record แสดงผู้สร้าง ผู้แก้ไขล่าสุด สถานะ และรายการปัญหาที่พบ</div>

    @forelse ($rows as $index => $row)
        <a name="project-data-quality-{{ $index + 1 }}"></a>
        <div id="project-data-quality-{{ $index + 1 }}" class="project-card">
            <table class="project-head">
                <tr>
                    <td class="project-index">{{ $index + 1 }}.</td>
                    <td>
                        <div class="muted" style="font-family:monospace; font-weight:bold;">{{ $row->project_code }}</div>
                        <div class="project-title">{{ $row->project_name }}</div>
                        <div style="margin-top:5px;">
                            <span class="chip">{{ $row->planning_status }}</span>
                            <span class="chip">ระดับ {{ $severityLabel($row->severity_score) }}</span>
                            <span class="chip">{{ count($row->issues) }} issues</span>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="meta-grid">
                <tr>
                    <td style="width:25%;">
                        <div class="label">PI</div>
                        <div class="value">{{ $row->pi_name }}</div>
                    </td>
                    <td style="width:15%;">
                        <div class="label">Output</div>
                        <div class="value">{{ (int) $row->outputs_count > 0 ? number_format($row->outputs_count).' รายการ' : 'ไม่มี output' }}</div>
                    </td>
                    <td style="width:30%;">
                        <div class="label">สร้าง record</div>
                        <div class="value">{{ $row->created_by_name }}</div>
                        <div class="muted">{{ $row->created_at_label }}</div>
                    </td>
                    <td style="width:30%;">
                        <div class="label">อัปเดทล่าสุด</div>
                        <div class="value">{{ $row->updated_by_name }}</div>
                        <div class="muted">{{ $row->updated_at_label }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <div class="label">Theme / ประเภท</div>
                        <div class="value">{{ $row->project_type }}</div>
                    </td>
                </tr>
            </table>

            <table class="issue-table">
                <thead>
                    <tr>
                        <th style="width:16%;">หมวด</th>
                        <th style="width:19%;">Field</th>
                        <th style="width:35%;">ปัญหา</th>
                        <th style="width:30%;">ควรทำ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($row->issues as $issue)
                        <tr>
                            <td><span class="badge badge-{{ $issue['category'] }}">{{ $issue['label'] }}</span></td>
                            <td>{{ collect($issue['fields'] ?? [])->implode(', ') ?: '-' }}</td>
                            <td>{{ $issue['reason'] }}</td>
                            <td>{{ $issue['suggestion'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="empty-state">ไม่พบข้อมูลผิดพลาดตามเงื่อนไขที่เลือก</div>
    @endforelse
</body>
</html>

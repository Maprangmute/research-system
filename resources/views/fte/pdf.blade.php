<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>FTE - {{ $person }}</title>
    <style>
        @page {
            margin: 66px 34px 88px 34px;
            header: html_Header;
            footer: html_Footer;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 13.5pt;
            line-height: 1.42;
            color: #1e293b;
        }
        h1, h2, h3, p {
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .muted { color: #64748b; }
        .report-shell { padding-top: 8px; }
        .hero-card {
            border: 1px solid #dbe7f5;
            border-radius: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 16px 18px;
            margin-bottom: 14px;
        }
        .hero-table td {
            border: none;
            vertical-align: top;
        }
        .avatar-wrap {
            width: 96px;
        }
        .avatar img {
            display: block;
            width: 80px;
            height: auto;
            max-height: 80px;
            border-radius: 18px;
            border: 1px solid #dbe7f5;
        }
        .hero-kicker {
            font-size: 10.5pt;
            font-weight: bold;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #64748b;
        }
        .hero-title {
            margin-top: 4px;
            font-size: 23pt;
            font-weight: bold;
            line-height: 1.08;
            color: #0f172a;
        }
        .hero-sub {
            margin-top: 3px;
            font-size: 13pt;
            color: #475569;
        }
        .hero-meta {
            margin-top: 7px;
            font-size: 11.2pt;
            color: #64748b;
        }
        .summary-box {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-bottom: 10px;
        }
        .summary-box td {
            width: 25%;
            border: 1px solid #dde6f6;
            border-radius: 15px;
            padding: 9px 11px;
            vertical-align: top;
        }
        .metric-label {
            font-size: 11pt;
            color: #475569;
        }
        .metric-value {
            margin-top: 3px;
            font-size: 21pt;
            font-weight: bold;
            line-height: 1.04;
            color: #0f172a;
        }
        .metric-note {
            margin-top: 2px;
            font-size: 10.4pt;
            color: #64748b;
        }
        .metric-project { background: #eff6ff; border-color: #bfdbfe; }
        .metric-fte { background: #f5f3ff; border-color: #ddd6fe; }
        .metric-hours { background: #ecfeff; border-color: #bae6fd; }
        .metric-month { background: #fff7ed; border-color: #fed7aa; }
        .section {
            margin-top: 14px;
        }
        .section-head {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 8px 12px;
            margin-bottom: 8px;
        }
        .section-head--summary { background: #eff6ff; border-color: #bfdbfe; }
        .section-head--monthly { background: #f5f3ff; border-color: #ddd6fe; }
        .section-head--worklist { background: #ecfeff; border-color: #bae6fd; }
        .section-head--detail { background: #fff7ed; border-color: #fed7aa; }
        .section-head--output { background: #fffbeb; border-color: #fde68a; }
        .section-kicker {
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
        }
        .section-title {
            margin-top: 1px;
            font-size: 17pt;
            font-weight: bold;
            color: #0f172a;
        }
        .section-subtitle {
            margin-top: 1px;
            font-size: 11pt;
            color: #64748b;
        }
        .data-table {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 12px;
            page-break-inside: auto;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #e2e8f0;
            padding: 7px 9px;
            vertical-align: top;
        }
        .data-table thead {
            display: table-header-group;
        }
        .data-table tbody tr:nth-child(even) td {
            background: #fbfdff;
        }
        .table-summary thead th { background: #eff6ff; }
        .table-worklist thead th { background: #ecfeff; }
        .table-detail thead th { background: #fff7ed; }
        .table-detail tr { page-break-inside: avoid; }
        .title-main {
            font-weight: bold;
            color: #0f172a;
            line-height: 1.24;
        }
        .title-sub {
            margin-top: 2px;
            font-size: 10.8pt;
            color: #334155;
            line-height: 1.28;
        }
        .manual-bullet-line {
            margin: 0 0 4px 0;
            line-height: 1.34;
        }
        .month-block {
            margin-top: 10px;
            page-break-inside: auto;
        }
        .month-badge {
            display: inline-block;
            border: 1px solid #fed7aa;
            background: #fff7ed;
            color: #9a3412;
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .work-chip {
            display: inline-block;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            padding: 2px 8px;
            font-size: 10.5pt;
            font-weight: bold;
        }
        .work-chip.active {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
        }
        .work-chip.complete {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #047857;
        }
        .work-chip.analysis {
            background: #fffbeb;
            border-color: #fde68a;
            color: #b45309;
        }
        .work-chip.default {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #475569;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .output-content {
            min-height: 420px;
            padding: 0;
            background: #ffffff;
            color: #111827;
            font-size: 12pt;
            line-height: 1.38;
            text-align: justify;
            text-justify: distribute;
            width: 100%;
        }
        .output-content p,
        .output-content div {
            margin: 0 0 5px 0;
            text-align: justify;
            text-justify: distribute;
        }
        .output-content ul,
        .output-content ol {
            margin-top: 0;
            margin-bottom: 10px;
            margin-left: 0;
            padding-left: 12px;
        }
        .output-content li {
            margin: 0 0 5px 0;
            padding-left: 2px;
            line-height: 1.38;
        }
        .wfa-output-list {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 8px 0;
            table-layout: fixed;
        }
        .wfa-output-list td {
            border: 0;
            padding: 0 0 7px 0;
            vertical-align: top;
            line-height: 1.38;
        }
        .wfa-output-list .wfa-output-bullet {
            width: 18px;
            padding-top: 0;
            text-align: left;
            font-weight: bold;
        }
        .wfa-output-list .wfa-output-item {
            width: auto;
            text-align: left;
            text-justify: none;
        }
        .wfa-certification {
            margin-top: 24pt;
            font-size: 12pt;
            line-height: 1.25;
            color: #111827;
        }
        .wfa-certification .sign-line {
            margin-top: 36pt;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    @php
        $allRows = $monthlyFTE->flatten(1);
        $projectCount = $allRows->pluck('project.id')->filter()->unique()->count();
        $monthCount = $monthlyFTE->count();
        $totalFte = $allRows->sum('fte_value');
        $totalHours = $allRows->sum('hours_in_month');
        $position = $profile->position ?? '-';
        $reporter = $user ? trim(($user->prefix ?? '') . ' ' . ($user->name ?? '')) : 'ผู้ใช้ระบบ';
        $reportNow = now()->setTimezone('Asia/Bangkok')->locale('th');
        $reportDate = $reportNow->translatedFormat('j F') . ' ' . ($reportNow->year + 543) . ' เวลา ' . $reportNow->format('H:i');
        $filterLabel = $pdfFilter['label'] ?? 'ทั้งหมด';
        $isWfaReport = !empty($wfaOutputHtml ?? null);
        $statusChipClass = static function (?string $status): string {
            $value = mb_strtolower(trim((string) $status), 'UTF-8');
            if (str_contains($value, 'เสร็จ')) return 'work-chip complete';
            if (str_contains($value, 'ดำเนิน') || str_contains($value, 'active')) return 'work-chip active';
            if (str_contains($value, 'วิเคราะห์') || str_contains($value, 'analysis')) return 'work-chip analysis';
            return 'work-chip default';
        };
        $formatManualBullets = static function ($value): string {
            $text = trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($text === '') {
                return '-';
            }

            if (!str_contains($text, '•')) {
                return nl2br(e($text));
            }

            $parts = preg_split('/\s*•\s*/u', $text);
            $prefix = trim((string) array_shift($parts));
            $items = collect($parts)->map(fn($part) => trim((string) $part))->filter()->values();
            $html = $prefix !== '' ? '<div class="manual-bullet-line">' . e($prefix) . '</div>' : '';

            foreach ($items as $item) {
                $html .= '<div class="manual-bullet-line">• ' . nl2br(e($item)) . '</div>';
            }

            return $html !== '' ? $html : nl2br(e($text));
        };
    @endphp

    <htmlpageheader name="Header">
        <table style="width:100%; border-bottom:1px solid #dbe7f5; padding-bottom:6px;">
            <tr>
                <td style="width:60%; font-size:16pt; font-weight:bold; color:#0f172a;">{{ $isWfaReport ? 'WFA / FTE Individual Report' : 'FTE Individual Report' }}</td>
                <td style="width:40%; text-align:right; font-size:11pt; color:#64748b;">
                    Siriraj Health Policy<br>
                    {{ $isWfaReport ? 'รายงานภาระงาน WFA และผลงานที่เกิดขึ้น' : 'รายงานภาระงานรายบุคคล' }}
                </td>
            </tr>
        </table>
    </htmlpageheader>

    <htmlpagefooter name="Footer">
        <table style="width:100%; border-top:1px solid #e2e8f0; padding-top:5px; font-size:10.5pt; color:#64748b;">
            <tr>
                <td style="width:60%;">สร้างโดย {{ $reporter }} · {{ $reportDate }}</td>
                <td style="width:40%; text-align:right;">หน้า {PAGENO} / {nbpg}</td>
            </tr>
        </table>
    </htmlpagefooter>

    <div class="report-shell">
        <div class="hero-card">
            <table class="hero-table">
                <tr>
                    <td class="avatar-wrap">
                        @if (!empty($profilePhotoDataUri))
                            <div class="avatar">
                                <img src="{{ $profilePhotoDataUri }}" alt="{{ $person }}" width="80">
                            </div>
                        @endif
                    </td>
                    <td>
                        @if (! $isWfaReport)
                            <div class="hero-kicker">FTE Overview</div>
                        @endif
                        <div class="hero-title">{{ $person }}</div>
                        <div class="hero-sub">ตำแหน่ง {{ $position }}</div>
                        <div class="hero-meta">ช่วงข้อมูล: {{ $filterLabel }} · รายงานออกเมื่อ {{ $reportDate }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <table class="summary-box">
            <tr>
                <td class="metric-project">
                    <div class="metric-label">จำนวนภาระงานทั้งหมด</div>
                    <div class="metric-value">{{ number_format($allRows->count()) }}</div>
                    <div class="metric-note">รายการงานที่ถูกคำนวณในรายงานนี้</div>
                </td>
                <td class="metric-project">
                    <div class="metric-label">จำนวนโครงการ / งาน</div>
                    <div class="metric-value">{{ number_format($projectCount) }}</div>
                    <div class="metric-note">นับโครงการที่มีภาระงานเชื่อมโยง</div>
                </td>
                <td class="metric-fte">
                    <div class="metric-label">FTE รวมทั้งหมด</div>
                    <div class="metric-value">{{ number_format($totalFte, 4) }}</div>
                    <div class="metric-note">สะสมจากทุกช่วงเวลาในขอบเขตรายงาน</div>
                </td>
                <td class="metric-hours">
                    <div class="metric-label">ชั่วโมงรวมทั้งหมด</div>
                    <div class="metric-value">{{ number_format($totalHours, 2) }}</div>
                    <div class="metric-note">รวมชั่วโมงทำงานทั้งหมด</div>
                </td>
            </tr>
        </table>
        <div class="section">
            <div class="section-head section-head--worklist">
                <div class="section-kicker">Monthly Snapshot</div>
                <div class="section-title">สถานะโครงการ / งานประจำเดือน {{ $monthProjectStats['label'] ?? '-' }}</div>
                <div class="section-subtitle">สรุปรายการงานที่เกี่ยวข้องในเดือนอ้างอิง พร้อมรายละเอียดภาระงานแต่ละกลุ่ม</div>
            </div>
            <table class="data-table table-worklist">
                <thead>
                    <tr>
                        <th style="width:28%">{{ $isWfaReport ? 'ภาระงาน' : 'ชื่อโครงการ / งาน' }}</th>
                        <th style="width:54%">รายละเอียดงาน</th>
                        <th style="width:18%">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($monthWorkList ?? collect()) as $work)
                        <tr>
                            <td>
                                <div class="title-main">{{ $work['name'] ?? '-' }}</div>
                            </td>
                            <td>
                                @php $detailGroups = collect($work['work_detail_groups'] ?? [])->filter(); @endphp
                                @if ($detailGroups->isEmpty())
                                    -
                                @else
                                    @foreach ($detailGroups as $group)
                                        <div class="title-main">{!! str_contains((string) ($group['job'] ?? ''), '•') ? $formatManualBullets($group['job'] ?? 'รายละเอียดงาน') : '• ' . e($group['job'] ?? 'รายละเอียดงาน') !!}</div>
                                        @php $tasks = collect($group['tasks'] ?? [])->filter(); @endphp
                                        @foreach ($tasks as $task)
                                            <div class="title-sub" style="margin-left: 14px;">{!! $formatManualBullets($task['task'] ?? '-') !!}</div>
                                            <div class="title-sub" style="margin-left: 28px;">({{ $task['range'] ?? '-' }})</div>
                                        @endforeach
                                    @endforeach
                                @endif
                            </td>
                            <td class="center">
                                <span class="{{ $statusChipClass($work['status_label'] ?? null) }}">{{ $work['status_label'] ?? '-' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="center muted">ไม่มีรายการโครงการ/งานในเดือนนี้</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-head section-head--detail">
                <div class="section-kicker">Monthly Detail</div>
                <div class="section-title">รายละเอียดการทำงานรายเดือน</div>
                <div class="section-subtitle">แยกรายเดือนเพื่อให้ติดตามภาระงาน ช่วงเวลา ชั่วโมง และค่า FTE ได้อย่างเป็นระบบ</div>
            </div>
            @forelse ($monthlyFTE as $monthKey => $rows)
                @php
                    $carbon = \Carbon\Carbon::createFromFormat('Y-m', $monthKey);
                    $monthName = $carbon->locale('th')->translatedFormat('F');
                    $year = $carbon->year + 543;
                @endphp
                <div class="month-block">
                    <div class="month-badge">{{ $monthName }} {{ $year }} · {{ $rows->count() }} รายการ</div>
                    <table class="data-table table-detail">
                        <thead>
                            <tr>
                                <th style="width:6%">#</th>
                                <th style="width:40%">{{ $isWfaReport ? 'ภาระงาน / รายละเอียดงาน' : 'โครงการ / รายละเอียดงาน' }}</th>
                                <th style="width:18%">ช่วงเวลา</th>
                                <th style="width:10%">ชั่วโมง</th>
                                <th style="width:10%">วันทำงาน</th>
                                <th style="width:16%">FTE เดือนนี้</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $index => $item)
                                @php
                                    $osCarbon = \Carbon\Carbon::parse($item->overlap_start);
                                    $oeCarbon = \Carbon\Carbon::parse($item->overlap_end);
                                    $isBackOffice = ! $item->project?->id;
                                    $backOfficeTitle = trim((string) ($item->job_name ?? '')) ?: trim((string) ($item->task_detail ?? '')) ?: '-';
                                    $backOfficeType = trim((string) ($item->task_detail ?? '')) ?: 'งานนอกโครงการ';
                                    $detailText = $isBackOffice
                                        ? (trim((string) ($item->job_detail ?? '')) ?: $backOfficeType)
                                        : (trim((string) ($item->task_detail ?? '')) ?: trim((string) ($item->job_detail ?? '')) ?: '-');
                                    $title = $item->project?->id ? ($item->project->project_name_th ?? '-') : $backOfficeTitle;
                                @endphp
                                <tr>
                                    <td class="center">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="title-main">{{ $title }}</div>
                                        @if (! $isWfaReport)
                                            <div class="title-sub">กลุ่ม: {{ $item->role_group ?? '-' }}</div>
                                        @endif
                                        <div class="title-sub">รายละเอียดงาน: {!! $formatManualBullets($detailText) !!}</div>
                                        @if ($isBackOffice)
                                            <div class="title-sub">{{ $backOfficeType }}</div>
                                        @endif
                                    </td>
                                    <td class="center">
                                        {{ $osCarbon->locale('th')->translatedFormat('j F') }} {{ $osCarbon->year + 543 }}<br>
                                        ถึง {{ $oeCarbon->locale('th')->translatedFormat('j F') }} {{ $oeCarbon->year + 543 }}
                                    </td>
                                    <td class="center">{{ number_format($item->hours_in_month, 2) }}</td>
                                    <td class="center">{{ intval($item->days_in_month) }}</td>
                                    <td class="center">{{ number_format($item->fte_value, 6) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="muted">ไม่มีข้อมูล</div>
            @endforelse
        </div>

        @if (!empty($wfaOutputHtml ?? null))
            <div style="page-break-before: always;"></div>
            <div style="height: 18px;"></div>

            <div class="section">
                <div class="section-title" style="margin-bottom: 14px;">{{ $wfaOutputTitle ?? 'Output ที่เกิดขึ้น' }}</div>
                <div class="output-content">{!! $wfaOutputPdfHtml ?? $wfaOutputHtml !!}</div>
                <div class="wfa-certification">
                    <div style="font-weight: bold;">ขอรับรองว่าปฏิบัติงานจริง</div>
                    <div class="sign-line">................................................</div>
                    <div style="font-weight: bold;">รศ. พญ.วราลักษณ์ ศรีนนท์ประเสริฐ</div>
                    <div>หัวหน้าหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</div>
                </div>
            </div>
        @endif
    </div>
</body>
</html>

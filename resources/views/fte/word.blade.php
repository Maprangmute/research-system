<!DOCTYPE html>
<html lang="th" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>FTE - {{ $person }}</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page Section1 {
            size: 595.3pt 841.9pt;
            margin: 36pt 36pt 48pt 36pt;
            mso-header-margin: 18pt;
            mso-footer-margin: 24pt;
            mso-paper-source: 0;
            mso-header: h1;
            mso-footer: f1;
        }
        body {
            font-family: "TH Sarabun New", "Angsana New", Tahoma, sans-serif;
            font-size: 14pt;
            line-height: 1.35;
            color: #1f2937;
            margin: 18px;
        }
        div.Section1 {
            page: Section1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        h1, h2, h3, p {
            margin: 0;
        }
        .report-shell {
            width: 100%;
        }
        .report-title {
            font-size: 22pt;
            font-weight: bold;
            color: #0f172a;
        }
        .report-subtitle {
            font-size: 11.5pt;
            color: #64748b;
        }
        .header-table {
            margin-bottom: 10px;
        }
        .hero-table {
            margin-top: 6px;
            margin-bottom: 14px;
            border: 1px solid #cbd5e1;
        }
        .hero-table td,
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .hero-table .hero-cell {
            border: 1px solid #cbd5e1;
            padding: 12px;
            vertical-align: top;
        }
        .hero-photo-cell {
            width: 110px;
            text-align: center;
            background: #f8fafc;
        }
        .hero-photo {
            display: block;
            width: 82px;
            max-width: 82px;
            height: 82px;
            max-height: 82px;
            border: 1px solid #cbd5e1;
            margin: 0 auto;
        }
        .hero-kicker {
            font-size: 10.5pt;
            font-weight: bold;
            letter-spacing: 0.08em;
            color: #64748b;
        }
        .hero-name {
            margin-top: 3px;
            font-size: 20pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.1;
        }
        .hero-meta {
            margin-top: 6px;
            font-size: 12pt;
            color: #475569;
        }
        .hero-note {
            margin-top: 4px;
            font-size: 11pt;
            color: #64748b;
        }
        .summary-table {
            margin-bottom: 14px;
            table-layout: fixed;
            border: 1px solid #cbd5e1;
        }
        .summary-table td {
            width: 25%;
            border: 1px solid #cbd5e1;
            padding: 9px 10px;
            vertical-align: top;
        }
        .summary-label {
            font-size: 11pt;
            color: #334155;
            font-weight: bold;
        }
        .summary-value {
            margin-top: 4px;
            font-size: 20pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.05;
        }
        .summary-note {
            margin-top: 2px;
            font-size: 10.5pt;
            color: #64748b;
        }
        .summary-project { background: #eff6ff; }
        .summary-fte { background: #f5f3ff; }
        .summary-hours { background: #ecfeff; }
        .section-title {
            margin-top: 14px;
            margin-bottom: 4px;
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
            font-size: 15pt;
            font-weight: bold;
            color: #0f172a;
        }
        .section-title.worklist { background: #dff7fb; }
        .section-title.detail { background: #fff1df; }
        .section-title.output { background: #fff7cf; }
        .section-subtitle {
            margin-bottom: 8px;
            font-size: 11pt;
            color: #64748b;
        }
        .data-table {
            margin-bottom: 12px;
            table-layout: fixed;
            border: 1px solid #cbd5e1;
            background: #ffffff;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            vertical-align: top;
            background: #ffffff;
        }
        .data-table th {
            background: #f8fafc;
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            color: #334155;
        }
        .worklist-table th {
            background: #dff7fb;
        }
        .detail-table th {
            background: #fff1df;
        }
        .month-head {
            margin-top: 10px;
            margin-bottom: 6px;
            padding: 6px 10px;
            border: 1px solid #fdba74;
            background: #fff1df;
            font-size: 13pt;
            font-weight: bold;
            color: #9a3412;
        }
        .item-title {
            font-weight: bold;
            color: #0f172a;
        }
        .item-sub {
            margin-top: 2px;
            color: #334155;
            font-size: 11pt;
        }
        .manual-bullet-line {
            margin: 0 0 4px 0;
            line-height: 1.34;
        }
        .status-active {
            font-weight: bold;
            color: #0f766e;
        }
        .status-complete {
            font-weight: bold;
            color: #166534;
        }
        .status-default {
            font-weight: bold;
            color: #334155;
        }
        .muted {
            color: #64748b;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .output-content {
            min-height: 360px;
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
        }
        .page-break {
            page-break-before: always;
        }
        div.h1 {
            mso-element: header;
            display: none;
        }
        div.f1 {
            mso-element: footer;
            display: none;
        }
        .footer-table {
            width: 100%;
        }
        .footer-table td {
            font-size: 10.5pt;
            color: #64748b;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
        }
        p.MsoFooter, li.MsoFooter, div.MsoFooter {
            margin: 0;
            mso-pagination: widow-orphan;
            tab-stops: center 234.0pt right 468.0pt;
        }
    </style>
</head>
<body>
    @php
        $allRows = $monthlyFTE->flatten(1);
        $projectCount = $allRows->pluck('project.id')->filter()->unique()->count();
        $totalFte = $allRows->sum('fte_value');
        $totalHours = $allRows->sum('hours_in_month');
        $position = $profile->position ?? '-';
        $reporter = $user ? trim(($user->prefix ?? '') . ' ' . ($user->name ?? '')) : 'ผู้ใช้ระบบ';
        $reportNow = now()->setTimezone('Asia/Bangkok')->locale('th');
        $reportDate = $reportNow->translatedFormat('j F') . ' ' . ($reportNow->year + 543) . ' เวลา ' . $reportNow->format('H:i');
        $filterLabel = $pdfFilter['label'] ?? 'ทั้งหมด';
        $isWfaReport = !empty($wfaOutputHtml ?? null);
        $formatFte = static function ($value, int $decimals = 7) use ($isWfaReport): string {
            $value = (float) $value;
            if (! $isWfaReport) {
                return number_format($value, $decimals);
            }

            $scale = 10 ** 6;
            $truncated = $value >= 0 ? floor($value * $scale) / $scale : ceil($value * $scale) / $scale;
            return number_format($truncated, 6);
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

    @php
        $statusTextClass = static function (?string $status): string {
            $value = mb_strtolower(trim((string) $status), 'UTF-8');
            if (str_contains($value, 'เสร็จ') || str_contains($value, 'complete')) {
                return 'status-complete';
            }
            if (str_contains($value, 'ดำเนิน') || str_contains($value, 'active')) {
                return 'status-active';
            }

            return 'status-default';
        };
    @endphp

    <div class="h1">
        <p class="MsoHeader">&nbsp;</p>
    </div>

    <div class="Section1">
    <div class="report-shell">
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="report-title">{{ $isWfaReport ? 'WFA / FTE Individual Report' : 'FTE Individual Report' }}</div>
            </td>
            <td style="width: 40%;" class="right">
                <div class="report-subtitle">Siriraj Health Policy</div>
                <div class="report-subtitle">{{ $isWfaReport ? 'รายงานภาระงาน WFA และผลงานที่เกิดขึ้น' : 'รายงานภาระงานรายบุคคล' }}</div>
            </td>
        </tr>
    </table>

    <table class="hero-table">
        <tr>
            <td class="hero-cell hero-photo-cell">
                @if (!empty($profilePhotoDataUri))
                    <img src="{{ $profilePhotoDataUri }}" alt="{{ $person }}" class="hero-photo" width="82" height="82">
                @else
                    <div class="hero-photo" style="background:#e2e8f0;"></div>
                @endif
            </td>
            <td class="hero-cell">
                @if (! $isWfaReport)
                    <div class="hero-kicker">FTE OVERVIEW</div>
                @endif
                <div class="hero-name">{{ $person }}</div>
                <div class="hero-meta">ตำแหน่ง {{ $position }}</div>
                <div class="hero-note">ช่วงข้อมูล: {{ $filterLabel }}</div>
                <div class="hero-note">รายงานออกเมื่อ {{ $reportDate }}</div>
            </td>
        </tr>
    </table>

    <table class="summary-table" border="1" cellspacing="0" cellpadding="0">
        <tr>
            <td class="summary-project">
                <div class="summary-label">จำนวนภาระงานทั้งหมด</div>
                <div class="summary-value">{{ number_format($allRows->count()) }}</div>
                <div class="summary-note">รายการงานที่ถูกคำนวณในรายงานนี้</div>
            </td>
            <td class="summary-project">
                <div class="summary-label">จำนวนโครงการ / งาน</div>
                <div class="summary-value">{{ number_format($projectCount) }}</div>
                <div class="summary-note">นับโครงการที่มีภาระงานเชื่อมโยง</div>
            </td>
            <td class="summary-fte">
                <div class="summary-label">FTE รวมทั้งหมด</div>
                <div class="summary-value">{{ $formatFte($totalFte, 4) }}</div>
                <div class="summary-note">สะสมจากทุกช่วงเวลาในขอบเขตรายงาน</div>
            </td>
            <td class="summary-hours">
                <div class="summary-label">ชั่วโมงรวมทั้งหมด</div>
                <div class="summary-value">{{ number_format($totalHours, 2) }}</div>
                <div class="summary-note">รวมชั่วโมงทำงานทั้งหมด</div>
            </td>
        </tr>
    </table>

    <div class="section-title worklist">สถานะโครงการ / งานประจำเดือน {{ $monthProjectStats['label'] ?? '-' }}</div>
    <div class="section-subtitle">สรุปรายการงานที่เกี่ยวข้องในเดือนอ้างอิง พร้อมรายละเอียดภาระงานแต่ละกลุ่ม</div>
    <table class="data-table worklist-table" border="1" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th style="width:28%; border:1px solid #cbd5e1;">{{ $isWfaReport ? 'ภาระงาน' : 'ชื่อโครงการ / งาน' }}</th>
                <th style="width:54%; border:1px solid #cbd5e1;">รายละเอียดงาน</th>
                <th style="width:18%; border:1px solid #cbd5e1;">สถานะ</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($monthWorkList ?? collect()) as $work)
                <tr>
                    <td style="border:1px solid #cbd5e1;"><div class="item-title">{{ $work['name'] ?? '-' }}</div></td>
                    <td style="border:1px solid #cbd5e1;">
                        @php $detailGroups = collect($work['work_detail_groups'] ?? [])->filter(); @endphp
                        @if ($detailGroups->isEmpty())
                            -
                    @else
                        @foreach ($detailGroups as $group)
                            <div class="item-title">{!! str_contains((string) ($group['job'] ?? ''), '•') ? $formatManualBullets($group['job'] ?? 'รายละเอียดงาน') : '• ' . e($group['job'] ?? 'รายละเอียดงาน') !!}</div>
                            @php $tasks = collect($group['tasks'] ?? [])->filter(); @endphp
                            @foreach ($tasks as $task)
                                    <div class="item-sub" style="margin-left: 14px;">{!! $formatManualBullets($task['task'] ?? '-') !!}</div>
                                    <div class="muted" style="margin-left: 28px; font-size: 10.5pt;">({{ $task['range'] ?? '-' }})</div>
                            @endforeach
                        @endforeach
                    @endif
                </td>
                    <td class="center" style="border:1px solid #cbd5e1;"><span class="{{ $statusTextClass($work['status_label'] ?? null) }}">{{ $work['status_label'] ?? '-' }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="center muted" style="border:1px solid #cbd5e1;">ไม่มีรายการโครงการ/งานในเดือนนี้</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title detail">รายละเอียดการทำงานรายเดือน</div>
    <div class="section-subtitle">แยกรายเดือนเพื่อให้ติดตามภาระงาน ช่วงเวลา ชั่วโมง และค่า FTE ได้อย่างเป็นระบบ</div>
    @forelse ($monthlyFTE as $monthKey => $rows)
        @php
            $carbon = \Carbon\Carbon::createFromFormat('Y-m', $monthKey);
            $monthName = $carbon->locale('th')->translatedFormat('F');
            $year = $carbon->year + 543;
        @endphp
        <div class="month-head">{{ $monthName }} {{ $year }} · {{ $rows->count() }} รายการ</div>
        <table class="data-table detail-table" border="1" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th style="width:6%; border:1px solid #cbd5e1;">#</th>
                    <th style="width:40%; border:1px solid #cbd5e1;">{{ $isWfaReport ? 'ภาระงาน / รายละเอียดงาน' : 'โครงการ / รายละเอียดงาน' }}</th>
                    <th style="width:18%; border:1px solid #cbd5e1;">ช่วงเวลา</th>
                    <th style="width:10%; border:1px solid #cbd5e1;">ชั่วโมง</th>
                    <th style="width:10%; border:1px solid #cbd5e1;">วันทำงาน</th>
                    <th style="width:16%; border:1px solid #cbd5e1;">FTE เดือนนี้</th>
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
                        <td class="center" style="border:1px solid #cbd5e1;">{{ $index + 1 }}</td>
                        <td style="border:1px solid #cbd5e1;">
                            <div class="item-title">{{ $title }}</div>
                            @if (! $isWfaReport)
                                <div class="item-sub">กลุ่ม: {{ $item->role_group ?? '-' }}</div>
                            @endif
                            <div class="item-sub">รายละเอียดงาน: {!! $formatManualBullets($detailText) !!}</div>
                            @if ($isBackOffice)
                                <div class="item-sub">{{ $backOfficeType }}</div>
                            @endif
                        </td>
                        <td class="center" style="border:1px solid #cbd5e1;">
                            {{ $osCarbon->locale('th')->translatedFormat('j F') }} {{ $osCarbon->year + 543 }}<br>
                            ถึง {{ $oeCarbon->locale('th')->translatedFormat('j F') }} {{ $oeCarbon->year + 543 }}
                        </td>
                        <td class="center" style="border:1px solid #cbd5e1;">{{ number_format($item->hours_in_month, 2) }}</td>
                        <td class="center" style="border:1px solid #cbd5e1;">{{ intval($item->days_in_month) }}</td>
                        <td class="center" style="border:1px solid #cbd5e1;">{{ $formatFte($item->fte_value) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <div class="muted">ไม่มีข้อมูล</div>
    @endforelse

    @if (!empty($wfaOutputHtml ?? null))
        <div class="page-break"></div>
        <div style="height: 18px;"></div>
        <div class="section-title output">{{ $wfaOutputTitle ?? 'Output ที่เกิดขึ้น' }}</div>
        <div class="output-content">{!! $wfaOutputWordHtml ?? $wfaOutputHtml !!}</div>
        <div class="wfa-certification">
            <div style="font-weight: bold;">ขอรับรองว่าปฏิบัติงานจริง</div>
            <div class="sign-line">................................................</div>
            <div style="font-weight: bold;">รศ. พญ.วราลักษณ์ ศรีนนท์ประเสริฐ</div>
            <div>หัวหน้าหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</div>
        </div>
    @endif
    </div>
    </div>

    <div class="f1">
        <p class="MsoFooter">
            <table class="footer-table">
                <tr>
                    <td style="width: 68%;">ผู้สร้างรายงาน {{ $reporter }} · {{ $reportDate }}</td>
                    <td style="width: 32%; text-align: right;">
                        หน้า
                        <!--[if supportFields]><span style='mso-element:field-begin'></span> PAGE \* MERGEFORMAT <span style='mso-element:field-separator'></span><![endif]-->
                        <span style="mso-no-proof:yes">1</span>
                        <!--[if supportFields]><span style='mso-element:field-end'></span><![endif]-->
                        /
                        <!--[if supportFields]><span style='mso-element:field-begin'></span> NUMPAGES \* MERGEFORMAT <span style='mso-element:field-separator'></span><![endif]-->
                        <span style="mso-no-proof:yes">1</span>
                        <!--[if supportFields]><span style='mso-element:field-end'></span><![endif]-->
                    </td>
                </tr>
            </table>
        </p>
    </div>
</body>
</html>

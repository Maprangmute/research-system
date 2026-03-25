<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>FTE - {{ $person }}</title>
    <style>
        @page {
            margin: 60px 40px 95px 40px;
            footer: html_Footer;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 14pt;
            line-height: 1.45;
            color: #222;
            padding-bottom: 10px;
        }
        h1 {
            font-size: 20pt;
            margin: 0 0 6px 0;
        }
        .muted { color: #555; }
        .section-title {
            background: #EAF2FF;
            padding: 6px 10px;
            border-radius: 8px;
            font-weight: bold;
            margin: 18px 0 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            page-break-inside: auto;
        }
        th, td {
            border: 1px solid #d9e2f1;
            padding: 7px 9px;
            vertical-align: top;
        }
        thead {
            background: #F2F6FF;
            display: table-header-group;
        }
        tr {
            page-break-inside: avoid;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .small { font-size: 12.5pt; }
        .month-block {
            margin-bottom: 18px;
            page-break-inside: auto;
        }
        .month-heading {
            margin: 12px 0 8px;
            font-size: 15pt;
            font-weight: bold;
            color: #0f172a;
            page-break-after: avoid;
            page-break-inside: avoid;
        }
        .fte-detail-table {
            table-layout: fixed;
        }
        .fte-detail-table th,
        .fte-detail-table td {
            word-break: break-word;
            white-space: normal;
            font-size: 14pt;
            line-height: 1.45;
        }
        .fte-detail-table th {
            font-size: 14pt;
            font-weight: 700;
            text-align: center;
        }
        .fte-col-index {
            width: 6%;
            text-align: center;
        }
        .fte-col-work {
            width: 43%;
        }
        .fte-col-period {
            width: 18%;
        }
        .fte-col-hours {
            width: 10%;
        }
        .fte-col-days {
            width: 10%;
        }
        .fte-col-fte-day {
            width: 14%;
        }
        .fte-col-fte-month {
            width: 12%;
        }
        .fte-work-title {
            font-weight: 600;
            color: #0f172a;
            line-height: 1.35;
            white-space: normal;
            word-break: break-word;
            font-size: 14pt;
        }
        .fte-work-meta {
            margin-top: 2px;
            color: #64748b;
            white-space: normal;
            word-break: break-word;
            font-size: 13.2pt;
            line-height: 1.35;
        }
        .fte-period {
            line-height: 1.35;
            font-size: 13.6pt;
        }
        .fte-period-date {
            display: block;
        }
        .fte-detail-extra td {
            background: #fafcff;
            border-top: none;
            padding-top: 4px;
            padding-bottom: 8px;
        }
        .fte-detail-extra td:first-child {
            background: #fff;
        }
        .fte-extra-line {
            font-size: 13.1pt;
            line-height: 1.35;
            color: #475569;
        }
        .fte-extra-line strong {
            color: #334155;
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
        $reportDate = now()->setTimezone('Asia/Bangkok')->locale('th')->translatedFormat('j F Y เวลา H:i');
        $filterLabel = $pdfFilter['label'] ?? 'ทั้งหมด';
        $wrapPdfText = static function ($value, $targetWidth = 34.0, $slack = 5.0) {
            $text = trim((string) ($value ?? ''));
            if ($text === '') {
                return '-';
            }

            $charWidth = static function ($char): float {
                if ($char === ' ') return 0.45;
                if (preg_match('/[A-Z]/u', $char)) return 0.72;
                if (preg_match('/[a-z0-9]/u', $char)) return 0.62;
                if (preg_match('/[\(\)\[\]\/\-\.,]/u', $char)) return 0.38;
                if (preg_match('/[่-๋็-๎ัิ-ืุ-ฺู]/u', $char)) return 0.05;
                if (preg_match('/[เแโใไ]/u', $char)) return 0.42;
                if (preg_match('/[ก-ฮ]/u', $char)) return 0.95;
                return 0.8;
            };

            $measureWidth = static function ($segment) use ($charWidth): float {
                $chars = preg_split('//u', (string) $segment, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                return array_reduce($chars, static fn ($sum, $char) => $sum + $charWidth($char), 0.0);
            };

            $totalWidth = $measureWidth($text);
            if ($totalWidth <= $targetWidth) {
                return e($text);
            }

            $estimatedLines = max(1, (int) ceil($totalWidth / $targetWidth));
            $balancedTarget = max(24.0, min(46.0, $totalWidth / $estimatedLines));

            $lines = [];
            $tokens = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];
            $current = '';

            foreach ($tokens as $token) {
                $candidate = $current . $token;
                $candidateWidth = $measureWidth($candidate);
                if ($current !== '' && $candidateWidth > ($balancedTarget + $slack)) {
                    $lines[] = trim($current);
                    $current = ltrim($token);
                } else {
                    $current = $candidate;
                }
            }

            if (trim($current) !== '') {
                $lines[] = trim($current);
            }

            $rebalance = [];
            foreach ($lines as $line) {
                if ($measureWidth($line) <= ($balancedTarget + $slack)) {
                    $rebalance[] = $line;
                    continue;
                }

                $chars = preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $buffer = '';
                foreach ($chars as $char) {
                    $candidate = $buffer . $char;
                    if ($buffer !== '' && $measureWidth($candidate) > ($balancedTarget + $slack)) {
                        $rebalance[] = trim($buffer);
                        $buffer = $char;
                    } else {
                        $buffer = $candidate;
                    }
                }
                if (trim($buffer) !== '') {
                    $rebalance[] = trim($buffer);
                }
            }

            return implode('<br>', array_map('e', $rebalance));
        };
    @endphp

    <h1>รายงาน FTE: {{ $person }} ตำแหน่ง {{ $position }}</h1>
    <div class="muted small">
        วันที่ออกรายงาน: {{ $reportDate }} • ผู้สร้างรายงาน: {{ $reporter }} • ช่วงข้อมูล: {{ $filterLabel }}
    </div>

    <htmlpagefooter name="Footer">
        <div style="width: 100%; font-size: 10pt; color: #333; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                รายงาน FTE: {{ $person }}
            </div>
            <div style="text-align: right;">
                ผู้สร้าง: {{ $reporter }} • {{ $reportDate }}<br>
                หน้า {PAGENO} จาก {nbpg}
            </div>
        </div>
    </htmlpagefooter>

    <div class="section-title">สรุปภาพรวม</div>
    <table>
        <tbody>
            <tr>
                <td>จำนวนเดือนที่มีงาน</td>
                <td class="right">{{ number_format($monthCount) }}</td>
            </tr>
            <tr>
                <td>จำนวนโครงการ</td>
                <td class="right">{{ number_format($projectCount) }}</td>
            </tr>
            <tr>
                <td>FTE รวมทั้งหมด</td>
                <td class="right">{{ number_format($totalFte, 4) }}</td>
            </tr>
            <tr>
                <td>ชั่วโมงรวมทั้งหมด</td>
                <td class="right">{{ number_format($totalHours, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">รายละเอียดการทำงานรายเดือน</div>
    @forelse ($monthlyFTE as $monthKey => $rows)
        @php
            $carbon = \Carbon\Carbon::createFromFormat('Y-m', $monthKey);
            $monthName = $carbon->locale('th')->translatedFormat('F');
            $year = $carbon->year + 543;
        @endphp

        <div class="month-block">
            <div class="month-heading">
                {{ $monthName }} {{ $year }} ({{ $rows->count() }} รายการ)
            </div>
            <table class="fte-detail-table">
                <thead>
                    <tr>
                        <th class="fte-col-index">#</th>
                        <th class="fte-col-work">โครงการ / รายละเอียดงาน</th>
                        <th class="fte-col-period center">ช่วงเวลา</th>
                        <th class="fte-col-hours center">ชั่วโมง</th>
                        <th class="fte-col-days center">วันทำงาน</th>
                        <th class="fte-col-fte-day center">ค่า FTE/วัน</th>
                        <th class="fte-col-fte-month center">FTE เดือนนี้</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $index => $item)
                        @php
                            $osCarbon = \Carbon\Carbon::parse($item->overlap_start);
                            $oeCarbon = \Carbon\Carbon::parse($item->overlap_end);
                            $os = $osCarbon->locale('th')->translatedFormat('j F');
                            $oe = $oeCarbon->locale('th')->translatedFormat('j F');
                            $osYear = $osCarbon->year + 543;
                            $oeYear = $oeCarbon->year + 543;
                            $title = $item->project?->id
                                ? ($item->project->project_name_th ?? '-')
                                : ($item->task_detail ?? '-');
                            $isBackOffice = !$item->project?->id;
                        @endphp
                        <tr>
                            <td class="fte-col-index">{{ $index + 1 }}</td>
                            <td class="fte-col-work">
                                <div class="fte-work-title">{!! $wrapPdfText($title, 37.0, 4.5) !!}</div>
                            </td>
                            <td class="fte-col-period center">
                                <div class="fte-period">
                                    <span class="fte-period-date">{{ $os }} {{ $osYear }}</span>
                                    <span class="fte-period-date">- {{ $oe }} {{ $oeYear }}</span>
                                </div>
                            </td>
                            <td class="fte-col-hours center">{{ number_format($item->hours_in_month, 2) }}</td>
                            <td class="fte-col-days center">{{ intval($item->days_in_month) }}</td>
                            <td class="fte-col-fte-day center">{{ number_format($item->fte_per_day, 8) }}</td>
                            <td class="fte-col-fte-month center">{{ number_format($item->fte_value, 6) }}</td>
                        </tr>
                        <tr class="fte-detail-extra">
                            <td class="fte-col-index"></td>
                            <td colspan="6">
                                <div class="fte-extra-line"><strong>กลุ่ม:</strong> {{ $item->role_group ?? '-' }}</div>
                                <div class="fte-extra-line"><strong>รายละเอียดงาน:</strong> {{ $item->task_detail ?? $item->job_detail ?? '-' }}</div>
                                @if ($isBackOffice)
                                    <div class="fte-extra-line"><strong>หมายเหตุ:</strong> งานนอกโครงการ</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="muted">ไม่มีข้อมูล</div>
    @endforelse
</body>
</html>

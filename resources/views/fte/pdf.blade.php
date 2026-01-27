<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>FTE - {{ $person }}</title>
    <style>
        @page {
            margin: 60px 40px 70px 40px;
            footer: html_Footer;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 13pt;
            color: #222;
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
        }
        th, td {
            border: 1px solid #d9e2f1;
            padding: 6px 8px;
            vertical-align: top;
        }
        thead { background: #F2F6FF; }
        .right { text-align: right; }
        .center { text-align: center; }
        .small { font-size: 11pt; }
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
    @endphp

    <h1>รายงาน FTE: {{ $person }} ตำแหน่ง {{ $position }}</h1>
    <div class="muted small">
        วันที่ออกรายงาน: {{ $reportDate }} • ผู้สร้างรายงาน: {{ $reporter }}
    </div>

    <htmlpagefooter name="Footer">
        <div style="width: 100%; font-size: 11pt; color: #333; display: flex; justify-content: space-between; align-items: flex-end;">
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

        <div style="margin: 8px 0; font-weight: bold;">
            {{ $monthName }} {{ $year }} ({{ $rows->count() }} รายการ)
        </div>
        <table class="small">
            <thead>
                <tr>
                    <th>ชื่อโปรเจค / งานนอกโครงการ</th>
                    <th>กลุ่ม</th>
                    <th>รายละเอียดงาน</th>
                    <th class="center">ช่วงเวลา</th>
                    <th class="center">ชั่วโมง</th>
                    <th class="center">วัน</th>
                    <th class="center">FTE/วัน</th>
                    <th class="center">FTE เดือนนี้</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $item)
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
                        <td>
                            {{ $title }}
                            @if ($isBackOffice)
                                <div class="muted small">งานนอกโครงการ</div>
                            @endif
                        </td>
                        <td>{{ $item->role_group ?? '-' }}</td>
                        <td>{{ $item->job_detail ?? '-' }}</td>
                        <td class="center">{{ $os }} {{ $osYear }} - {{ $oe }} {{ $oeYear }}</td>
                        <td class="center">{{ number_format($item->hours_in_month, 2) }}</td>
                        <td class="center">{{ intval($item->days_in_month) }}</td>
                        <td class="center">{{ number_format($item->fte_per_day, 10) }}</td>
                        <td class="center">{{ number_format($item->fte_value, 6) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <div class="muted">ไม่มีข้อมูล</div>
    @endforelse
</body>
</html>

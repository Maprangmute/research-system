<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>FTE - {{ $person }}</title>
    <style>
        body {
            font-family: "Angsana New", AngsanaUPC, "TH Sarabun New", Tahoma, sans-serif;
            font-size: 14pt;
            line-height: 1.45;
            color: #222;
            margin: 24px;
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
            padding: 7px 9px;
            vertical-align: top;
        }
        th {
            background: #F2F6FF;
            text-align: center;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .small { font-size: 12.5pt; }
        .month-block {
            margin-bottom: 18px;
        }
        .month-heading {
            margin: 12px 0 8px;
            font-size: 15pt;
            font-weight: bold;
            color: #0f172a;
        }
        .page-break {
            page-break-before: always;
        }
        .blank-output-space {
            min-height: 780px;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            background: #fff;
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
    @endphp

    <h1>รายงาน FTE: {{ $person }} ตำแหน่ง {{ $position }}</h1>
    <div class="muted small">
        วันที่ออกรายงาน: {{ $reportDate }} • ผู้สร้างรายงาน: {{ $reporter }} • ช่วงข้อมูล: {{ $filterLabel }}
    </div>

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

    <div class="section-title">สถานะโครงการประจำเดือน {{ $monthProjectStats['label'] ?? '-' }}</div>
    <table>
        <tbody>
            <tr>
                <td>มีโปรเจคทั้งหมด (เดือนนี้)</td>
                <td class="right">{{ number_format($monthProjectStats['total'] ?? 0) }}</td>
            </tr>
            <tr>
                <td>จำนวนโครงการที่ทำอยู่ในเดือนนี้</td>
                <td class="right">{{ number_format($monthProjectStats['active'] ?? 0) }}</td>
            </tr>
            <tr>
                <td>จำนวนจบโครงการในเดือนนี้</td>
                <td class="right">{{ number_format($monthProjectStats['completed'] ?? 0) }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width:30%">ชื่อโครงการ/งาน</th>
                <th style="width:50%">รายละเอียดงาน</th>
                <th style="width:20%">สถานะ</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($monthWorkList ?? collect()) as $work)
                <tr>
                    <td>{{ $work['name'] ?? '-' }}</td>
                    <td>
                        @php $detailGroups = collect($work['work_detail_groups'] ?? [])->filter(); @endphp
                        @if ($detailGroups->isEmpty())
                            -
                        @else
                            @foreach ($detailGroups as $group)
                                <div><strong>• {{ $group['job'] ?? 'รายละเอียดงาน' }}</strong></div>
                                @php $tasks = collect($group['tasks'] ?? [])->filter(); @endphp
                                @foreach ($tasks as $task)
                                    <div style="margin-left: 16px;">- {{ $task['task'] ?? '-' }}</div>
                                    <div style="margin-left: 32px; color:#64748b;">({{ $task['range'] ?? '-' }})</div>
                                @endforeach
                            @endforeach
                        @endif
                    </td>
                    <td class="center">{{ $work['status_label'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="center muted">ไม่มีรายการโครงการ/งานในเดือนนี้</td>
                </tr>
            @endforelse
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
            <div class="month-heading">{{ $monthName }} {{ $year }} ({{ $rows->count() }} รายการ)</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:6%">#</th>
                        <th style="width:42%">โครงการ / รายละเอียดงาน</th>
                        <th style="width:18%">ช่วงเวลา</th>
                        <th style="width:10%">ชั่วโมง</th>
                        <th style="width:10%">วันทำงาน</th>
                        <th style="width:14%">FTE เดือนนี้</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $index => $item)
                        @php
                            $osCarbon = \Carbon\Carbon::parse($item->overlap_start);
                            $oeCarbon = \Carbon\Carbon::parse($item->overlap_end);
                            $title = $item->project?->id ? ($item->project->project_name_th ?? '-') : ($item->task_detail ?? '-');
                            $isBackOffice = !$item->project?->id;
                        @endphp
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $title }}</strong><br>
                                กลุ่ม: {{ $item->role_group ?? '-' }}<br>
                                รายละเอียดงาน: {{ $item->task_detail ?? $item->job_detail ?? '-' }}
                                @if ($isBackOffice)
                                    <br>หมายเหตุ: งานนอกโครงการ
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

    <div class="page-break"></div>
    <div class="section-title">Output ที่เกิดขึ้น: {{ $pdfFilter['output_label'] ?? $filterLabel }}</div>
    <div class="blank-output-space"></div>
</body>
</html>

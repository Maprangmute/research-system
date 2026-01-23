<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Back Office - {{ $task->title }}</title>
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
        $reporter = $user ? trim(($user->prefix ?? '') . ' ' . ($user->name ?? '')) : 'ผู้ใช้ระบบ';
        $formatDateBE = function ($value) {
            if (!$value) {
                return '-';
            }
            try {
                $d = \Carbon\Carbon::createFromFormat('d-m-Y', $value)->locale('th');
                return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
            } catch (\Exception $e) {
                try {
                    $d = \Carbon\Carbon::parse($value)->locale('th');
                    return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
                } catch (\Exception $e) {
                    return $value ?: '-';
                }
            }
        };
        $now = now()->setTimezone('Asia/Bangkok')->locale('th');
        $reportDate = $now->translatedFormat('j F') . ' ' . ($now->year + 543) . $now->translatedFormat(' เวลา H:i');
    @endphp

    <h1>รายงานงานนอกโครงการ: {{ $task->title }}</h1>
    <div class="muted small">
        ช่วงเวลา {{ $formatDateBE($task->start_date) }} ถึง {{ $formatDateBE($task->end_date) }}
        • วันที่ออกรายงาน: {{ $reportDate }} • ผู้สร้างรายงาน: {{ $reporter }}
    </div>

    <htmlpagefooter name="Footer">
        <div style="width: 100%; font-size: 11pt; color: #333; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                งานนอกโครงการ: {{ $task->title }}
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
                <td>จุดประสงค์ของงาน</td>
                <td class="right">{{ $task->purpose ?? '-' }}</td>
            </tr>
            <tr>
                <td>หัวหน้างานผู้รับผิดชอบ</td>
                <td class="right">{{ !empty($task->leaders) ? implode(', ', $task->leaders) : '-' }}</td>
            </tr>
            <tr>
                <td>จำนวนภาระงาน</td>
                <td class="right">{{ number_format($summary['entries']) }}</td>
            </tr>
            <tr>
                <td>ชั่วโมงรวม</td>
                <td class="right">{{ number_format($summary['hours'], 2) }}</td>
            </tr>
            <tr>
                <td>FTE รวม</td>
                <td class="right">{{ number_format($summary['fte'], 6) }}</td>
            </tr>
            <tr>
                <td>ผู้บันทึก</td>
                <td class="right">{{ $task->creator?->name ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">รายละเอียดภาระงาน</div>
    <table class="small">
        <thead>
            <tr>
                <th>บุคคล</th>
                <th>ประเภท</th>
                <th>ภาระงาน</th>
                <th>ลิงก์เอกสาร</th>
                <th class="center">วันที่เริ่ม</th>
                <th class="center">วันที่สิ้นสุด</th>
                <th class="center">ชั่วโมง</th>
                <th class="center">FTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($task->entries as $entry)
                <tr>
                    <td>{{ $entry->person_name }}</td>
                    <td>{{ $entry->category }}</td>
                    <td>{{ $entry->job_detail }}</td>
                    <td>{{ $entry->document_link ? $entry->document_link : '-' }}</td>
                    <td class="center">{{ $formatDateBE($entry->start_date) }}</td>
                    <td class="center">{{ $formatDateBE($entry->end_date) }}</td>
                    <td class="center">{{ number_format($entry->hours, 2) }}</td>
                    <td class="center">{{ number_format($entry->fte_value, 8) }}</td>
                </tr>
                @if ($entry->outputs->count())
                    <tr>
                        <td colspan="8">
                            <div class="small" style="padding:6px 0;">
                                <strong>ผลงานของภาระงาน</strong>
                                <ul style="margin:6px 0 0 16px;">
                                    @foreach ($entry->outputs as $output)
                                        <li>
                                            {{ $output->title }}
                                            @if ($output->delivered_at) • ส่ง {{ $formatDateBE($output->delivered_at) }} @endif
                                            @if ($output->description) • {{ $output->description }} @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="section-title">ผลงานของงานนอกโครงการ</div>
    @if ($task->outputs->count())
        <table class="small">
            <thead>
                <tr>
                    <th>ชื่อผลงาน</th>
                    <th class="center">วันที่จัดส่ง</th>
                    <th>รายละเอียดเพิ่มเติม</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($task->outputs as $output)
                    <tr>
                        <td>{{ $output->title }}</td>
                        <td class="center">{{ $output->delivered_at ? $formatDateBE($output->delivered_at) : '-' }}</td>
                        <td>{{ $output->description ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="muted small">ไม่มีผลงาน</div>
    @endif
</body>
</html>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>{{ $meta['title'] ?? 'Dashboard Export' }}</title>
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            src: url('{{ public_path('fonts/THSarabunNew.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @page {
            margin: 20mm 16mm 18mm 16mm;
        }
        body {
            font-family: 'THSarabunNew', 'DejaVu Sans', Tahoma, sans-serif;
            color: #0f172a;
            font-size: 12pt;
        }
        h1 { margin: 0 0 6px 0; font-size: 22pt; }
        h2 { margin: 0 0 6px 0; font-size: 16pt; }
        .muted { color: #475569; font-size: 11pt; }
        .cover { text-align: center; padding-top: 10px; }
        .logo { margin: 0 auto 18px auto; display: block; height: 140px; width: auto; }
        .list { text-align: left; display: inline-block; }
        .two-col { display: table; width: 100%; table-layout: fixed; }
        .two-col .cell { display: table-cell; width: 50%; padding: 6px; vertical-align: top; }
        .chart-box { border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; background: #fff; height: 100%; }
        .chart-box img { width: 100%; height: auto; display: block; border-radius: 6px; }
        .chart-title { font-weight: bold; margin-bottom: 6px; font-size: 12pt; }
        .page-break { page-break-after: always; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; font-size: 11pt; }
        th { background: #f1f5f9; font-weight: bold; }
        .center { text-align: center; }
        /* .footer { position: fixed; bottom: 8px; left: 16mm; right: 16mm; font-size: 10pt; color: #374151; display: flex; justify-content: space-between; } */
    </style>
</head>
<body>
<!-- Header -->
<table width="100%" style="margin-bottom: 12px;">
    <tr>
        <td style="font-weight:bold; font-size:14pt;">{{ $meta['title'] ?? '' }}</td>
        <td style="text-align:right; font-size:10pt; color:#475569;">Dashboard Export: กราฟและตารางสถานะโครงการ • สร้างเมื่อ {{ $meta['generatedText'] ?? '' }}</td>
    </tr>
    <tr>
        <td style="font-size:10pt; color:#475569;">ผู้สร้าง: {{ $meta['generatedBy'] ?? '-' }}</td>
        <td></td>
    </tr>
</table>

<div class="cover page-break">
    @if(!empty($meta['logo']))
        <img src="{{ $meta['logo'] }}" class="logo" alt="logo">
    @endif
    <h1>{{ $meta['title'] ?? '' }}</h1>
    <h2>{{ $meta['subtitle'] ?? '' }}</h2>
    <div class="muted">สร้างเมื่อ {{ $meta['generatedText'] ?? '' }} โดย {{ $meta['generatedBy'] ?? '-' }} • กราฟทั้งหมด: {{ count($charts ?? []) }} รายการ</div>
    <div class="list" style="margin-top:18px;">
        <div style="font-weight:bold;">รายการที่รวม:</div>
        <ul>
            <li>กราฟกลยุทธ์ 2 กราฟต่อหน้า</li>
            <li>ตารางโครงการเกินกำหนด</li>
            <li>ตาราง IRB</li>
            <li>Co-PI / ผู้วิจัยร่วม</li>
            <li>รายการโครงการตามตัวกรอง</li>
        </ul>
    </div>
</div>

@php
    $pairs = collect($charts ?? [])->chunk(2);
@endphp
@foreach($pairs as $pair)
    <div class="page-break">
        <div class="two-col">
            @foreach($pair as $chart)
                <div class="cell">
                    <div class="chart-box">
                        <div class="chart-title">{{ $chart['title'] ?? ($chart['id'] ?? '') }}</div>
                        @if(!empty($chart['image']))
                            <img src="{{ $chart['image'] }}" alt="chart">
                        @else
                            <div class="muted">ไม่มีข้อมูลกราฟ</div>
                        @endif
                    </div>
                </div>
            @endforeach
            @if($pair->count() === 1)
                <div class="cell"></div>
            @endif
        </div>
    </div>
@endforeach

@if(!empty($tables['overdue']))
<div class="page-break">
    <h2>ตารางโครงการเกินกำหนด ({{ count($tables['overdue']) }} รายการ)</h2>
    <table>
        <thead>
            <tr>
                <th>โครงการ</th>
                <th class="center">วันสิ้นสุด</th>
                <th class="center">เกินกำหนด (วัน)</th>
                <th class="center">PI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tables['overdue'] as $row)
                <tr>
                    <td>{{ $row['project'] ?? '-' }}</td>
                    <td class="center">{{ $row['endDateStr'] ?? '-' }}</td>
                    <td class="center">{{ $row['overdueDays'] ?? '-' }}</td>
                    <td class="center">{{ $row['pi'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!empty($tables['irb']))
<div class="page-break">
    <h2>ตาราง IRB ({{ count($tables['irb']) }} รายการ)</h2>
    <table>
        <thead>
            <tr>
                <th>โครงการ</th>
                <th class="center">รหัส IRB</th>
                <th class="center">หน่วยงาน/แหล่ง</th>
                <th class="center">วันหมดอายุ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tables['irb'] as $row)
                <tr>
                    <td>{{ $row['project'] ?? '-' }}</td>
                    <td class="center">{{ $row['code'] ?? '-' }}</td>
                    <td class="center">{{ $row['source'] ?? '-' }}</td>
                    <td class="center">{{ $row['expDateStr'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!empty($tables['copi']))
<div class="page-break">
    <h2>Co-PI / ผู้วิจัยร่วม ({{ count($tables['copi']) }} รายการ)</h2>
    <table>
        <thead>
            <tr>
                <th>ชื่อ</th>
                <th>หน่วยงาน</th>
                <th class="center">จำนวนโครงการ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tables['copi'] as $row)
                <tr>
                    <td>{{ $row['name'] ?? '-' }}</td>
                    <td>{{ $row['affiliation'] ?? '-' }}</td>
                    <td class="center">{{ $row['project_count'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!empty($tables['projects']))
<div>
    <h2>รายการโครงการตามตัวกรอง ({{ count($tables['projects']) }} รายการ)</h2>
    <table>
        <thead>
            <tr>
                <th>ชื่อโครงการ</th>
                <th class="center">ปี</th>
                <th class="center">PI</th>
                <th class="center">Co-PI</th>
                <th class="center">Co-Research</th>
                <th class="center">FTE รวม</th>
                <th class="center">Output</th>
                <th class="center">IRB ล่าสุด</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tables['projects'] as $row)
                <tr>
                    <td>{{ $row['project'] ?? '-' }}</td>
                    <td class="center">{{ $row['year'] ?? '-' }}</td>
                    <td class="center">{{ $row['pi'] ?? '-' }}</td>
                    <td class="center">{{ $row['copis'] ?? '-' }}</td>
                    <td class="center">{{ $row['coResearch'] ?? '-' }}</td>
                    <td class="center">{{ $row['fte'] ?? '-' }}</td>
                    <td class="center">{{ $row['outputs'] ?? '-' }}</td>
                    <td class="center">{{ $row['irb'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
{{-- 
<div class="footer">
    <span>ออกรายงานจากแดชบอร์ด</span>
    <span>หน้า {PAGE_NUM} / {PAGE_COUNT}</span>
</div> --}}
</body>
</html>

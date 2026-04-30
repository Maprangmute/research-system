<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 18px 24px;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 15.5pt;
            line-height: 1.34;
            color: #0f172a;
        }
        h1 {
            margin: 0 0 4px;
            font-size: 24pt;
            font-weight: 700;
        }
        .meta {
            margin-bottom: 10px;
            color: #475569;
            font-size: 13.5pt;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            vertical-align: top;
            word-wrap: break-word;
            font-weight: 600;
        }
        th {
            background: #e2e8f0;
            font-weight: 800;
            font-size: 15pt;
        }
        .w-code { width: 9%; }
        .w-name { width: 26%; }
        .w-pi { width: 15%; }
        .w-year { width: 7%; }
        .w-budget { width: 12%; }
        .w-type { width: 10%; }
        .w-source { width: 13%; }
        .w-status { width: 8%; }
    </style>
</head>
<body>
    <h1>รายชื่อโครงการ {{ $periodLabel }}</h1>
    <div class="meta">
        สร้างเมื่อ {{ $generatedAt->format('d/m/Y H:i') }}
        โดย {{ $generatedBy }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="w-code">Project ID</th>
                <th class="w-name">ชื่อโครงการ</th>
                <th class="w-pi">PI</th>
                <th class="w-year">ปีโครงการ</th>
                <th class="w-budget">งบประมาณ (บาท)</th>
                <th class="w-type">ประเภทแหล่งทุน</th>
                <th class="w-source">แหล่งทุน</th>
                <th class="w-status">สถานะโครงการ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->project_code ?: '-' }}</td>
                    <td>{{ $row->project_name_th ?: '-' }}</td>
                    <td>{{ $row->pi_name ?: '-' }}</td>
                    <td>{{ $row->start_year ?: '-' }}</td>
                    <td>{{ number_format((float) ($row->budget ?? 0), 2) }}</td>
                    <td>{{ $row->funding_type ?: '-' }}</td>
                    <td>{{ $row->funding_source ?: '-' }}</td>
                    <td>{{ $row->planning_status ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

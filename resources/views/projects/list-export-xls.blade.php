<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Project Export</title>
    <style>
        body {
            font-family: "TH Sarabun New", "DejaVu Sans", sans-serif;
            font-size: 14px;
            color: #0f172a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: top;
        }
        th {
            background: #e2e8f0;
            font-weight: 700;
        }
        h1 {
            margin: 0 0 4px;
            font-size: 22px;
        }
        .meta {
            margin-bottom: 12px;
            color: #475569;
        }
    </style>
</head>
<body>
    <h1>รายชื่อโครงการ {{ $periodLabel }}</h1>
    <div class="meta">สร้างเมื่อ {{ $generatedAt->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>Project ID</th>
                <th>ชื่อโครงการ</th>
                <th>PI</th>
                <th>ปี</th>
                <th>งบประมาณ(บาท)</th>
                <th>ประเภททุน</th>
                <th>แหล่งทุน</th>
                <th>Project Type</th>
                <th>Project Status</th>
                <th>อัปเดตล่าสุด</th>
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
                    <td>{{ $row->project_type ?: '-' }}</td>
                    <td>{{ $row->planning_status ?: '-' }}</td>
                    <td>{{ optional($row->updated_at)->format('d/m/Y H:i') ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

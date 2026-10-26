<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Tahoma, sans-serif; color: #0f172a; }
        table { border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; vertical-align: top; }
        th { background: #d1fae5; }
        .text { mso-number-format: "\@"; }
    </style>
</head>
<body>
    <h1>รายชื่อโครงการ</h1>
    <p>ผู้ส่งออก: {{ $generatedBy }}</p>
    <p>วันที่ส่งออก: {{ $generatedAt->format('d/m/Y H:i:s') }} (Asia/Bangkok)</p>
    <p>ตัวกรองที่ใช้:
        @forelse ($filters as $label => $value)
            {{ $label }}: {{ $value }}{{ $loop->last ? '' : ' | ' }}
        @empty
            ทั้งหมด
        @endforelse
    </p>
    <p>จำนวนโครงการ: {{ $rows->count() }}</p>
    <table>
        <thead><tr>
            <th>ปี</th><th>รหัสโครงการ</th><th>ชื่อโครงการ</th><th>แหล่งทุน</th>
            <th>สถานะโครงการ</th><th>จำนวน outputs ที่เกิดขึ้น</th><th>Link ไปยังหน้า detail</th>
        </tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->start_year ?: '-' }}</td>
                    <td class="text">{{ $row->project_code ?: '-' }}</td>
                    <td class="text">{{ $row->project_name_th ?: '-' }}</td>
                    <td class="text">{{ $row->funding_source ?: '-' }}</td>
                    <td>{{ trim($row->planning_status ?? '') ?: 'ยังไม่ได้ระบุ' }}</td>
                    <td>{{ $row->outputs_count }}</td>
                    <td><a href="{{ route('projects.show', $row) }}">{{ route('projects.show', $row) }}</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

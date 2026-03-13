<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Project Logs Export</title>
    <style>
        body { font-family: "TH Sarabun New", "DejaVu Sans", sans-serif; font-size: 14px; color: #0f172a; }
        h1 { margin: 0 0 4px; font-size: 22px; }
        .meta { margin-bottom: 10px; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; vertical-align: top; }
        th { background: #e2e8f0; font-weight: 700; }
        .multiline { white-space: pre-line; }
    </style>
</head>
<body>
    <h1>สรุป Project Logs ({{ $periodLabel }})</h1>
    <div class="meta">สร้างเมื่อ {{ $generatedAt->format('d/m/Y H:i') }} โดย {{ $generatedBy }}</div>

    <table>
        <thead>
            <tr>
                <th>วันเวลา (พ.ศ.)</th>
                <th>ชื่อโครงการ</th>
                <th>ผู้อัปเดท</th>
                <th>จำนวนรายการที่ยุบ</th>
                <th>ประเภท</th>
                <th>เหตุการณ์</th>
                <th>action key</th>
                <th>รายละเอียดการเปลี่ยนแปลง</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['thai_date'] ?? '-' }}</td>
                    <td>{{ $row['project_name'] ?? '-' }}</td>
                    <td>{{ $row['user_name'] ?? '-' }}</td>
                    <td>{{ $row['items_count'] ?? 1 }}</td>
                    <td class="multiline">{{ $row['entities'] ?? '-' }}</td>
                    <td class="multiline">{{ $row['actions'] ?? '-' }}</td>
                    <td class="multiline">{{ $row['action_keys'] ?? '-' }}</td>
                    <td class="multiline">{{ $row['change_summary'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

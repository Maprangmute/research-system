<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 16px 18px; }
        body { font-family: "thsarabun", Tahoma, sans-serif; font-size: 14pt; color: #0f172a; }
        h1 { margin: 0 0 4px; font-size: 22pt; font-weight: 700; }
        .meta { margin-bottom: 10px; color: #475569; font-size: 12pt; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: top; word-wrap: break-word; }
        th { background: #e2e8f0; font-weight: 700; }
        .multiline { white-space: pre-line; }
        .w-time { width: 10%; }
        .w-project { width: 20%; }
        .w-user { width: 13%; }
        .w-count { width: 7%; }
        .w-entity { width: 8%; }
        .w-action { width: 8%; }
        .w-key { width: 10%; }
        .w-change { width: 24%; }
    </style>
</head>
<body>
    <h1>สรุป Project Logs ({{ $periodLabel }})</h1>
    <div class="meta">สร้างเมื่อ {{ $generatedAt->format('d/m/Y H:i') }} โดย {{ $generatedBy }}</div>

    <table>
        <thead>
            <tr>
                <th class="w-time">วันเวลา (พ.ศ.)</th>
                <th class="w-project">ชื่อโครงการ</th>
                <th class="w-user">ผู้อัปเดท</th>
                <th class="w-count">จำนวน</th>
                <th class="w-entity">ประเภท</th>
                <th class="w-action">เหตุการณ์</th>
                <th class="w-key">action key</th>
                <th class="w-change">รายละเอียดการเปลี่ยนแปลง</th>
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

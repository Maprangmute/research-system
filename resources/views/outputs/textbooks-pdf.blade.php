<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Textbooks Report</title>
    <style>
        @page { margin: 28px 24px 30px 24px; }
        body { font-family: "thsarabun", Tahoma, sans-serif; font-size: 13pt; color: #1f2937; }
        h1 { margin: 0; font-size: 22pt; }
        .subtitle { margin-top: 6px; font-size: 12pt; color: #4b5563; }
        .summary { margin-top: 14px; }
        .summary-item { margin-bottom: 4px; }
        .filters { margin-top: 8px; font-size: 11pt; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #dbe3f0; padding: 6px 8px; vertical-align: top; }
        thead { background: #f1f5f9; }
        th { font-size: 11pt; text-align: left; }
        td { font-size: 12pt; }
    </style>
</head>
<body>
    @php
        $generated = ($generatedAt ?? now())->setTimezone('Asia/Bangkok')->locale('th');
        $generatedLabel = $generated->translatedFormat('j F') . ' ' . ($generated->year + 543) . ' ' . $generated->format('H:i');
    @endphp

    <h1>รายงาน Textbook / Book chapter</h1>
    <div class="subtitle">ผู้สร้างรายงาน: {{ $reporter ?? 'ผู้ใช้ระบบ' }} | วันที่ออกรายงาน: {{ $generatedLabel }}</div>
    @if (!empty($filters) && count($filters))
        <div class="filters">ฟิลเตอร์: {{ collect($filters)->implode(' | ') }}</div>
    @endif

    <div class="summary">
        <div class="summary-item">ทั้งหมด: {{ number_format($summary['total_textbooks'] ?? 0) }} รายการ</div>
        <div class="summary-item">ผู้เขียนไม่ซ้ำ: {{ number_format($summary['unique_authors'] ?? 0) }}</div>
        <div class="summary-item">Top Author: {{ $summary['top_author'] ?? '-' }}</div>
        <div class="summary-item">Top Publisher: {{ $summary['top_publisher'] ?? '-' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 18%;">ชื่อผลงาน</th>
                <th style="width: 16%;">Book Title</th>
                <th style="width: 14%;">Publisher</th>
                <th style="width: 16%;">Authors</th>
                <th style="width: 8%;">ปี</th>
                <th style="width: 23%;">Project / รายละเอียด</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($textbooks as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $row->title }}
                        @if (!empty($row->output_code))
                            <div style="font-size:10pt;color:#475569;">{{ $row->output_code }}</div>
                        @endif
                    </td>
                    <td>
                        {{ $row->book_title }}
                        @if (($row->chapter_pages ?? '-') !== '-')
                            <div style="font-size:10pt;color:#475569;">{{ $row->chapter_pages }}</div>
                        @endif
                    </td>
                    <td>{{ $row->publisher_name }}</td>
                    <td>{{ collect($row->authors)->isEmpty() ? '-' : collect($row->authors)->implode(', ') }}</td>
                    <td>{{ $row->year_be ?: '-' }}</td>
                    <td>
                        <div>{{ $row->project_label }}</div>
                        @if (!empty($row->detail))
                            <div style="font-size:10pt;color:#475569;">{{ $row->detail }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:#64748b;">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th {
            background: #dbeafe;
            color: #0f172a;
            font-weight: bold;
            border: 1px solid #94a3b8;
            padding: 8px;
        }
        td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            vertical-align: top;
            mso-number-format: "\\@";
        }
        .meta { background: #f8fafc; font-weight: bold; }
        .source-project { background: #eff6ff; color: #1d4ed8; font-weight: bold; }
        .source-mission { background: #ecfdf5; color: #0f766e; font-weight: bold; }
        .source-extra { background: #fdf2f8; color: #9d174d; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F') . ' ' . ($generatedAt->year + 543) . $generatedAt->translatedFormat(' เวลา H:i');
        $sourceLabel = static fn($row) => trim((string) ($row->source_badge ?? '')) !== '' ? trim((string) $row->source_badge) : 'Project';
        $sourceClass = static function ($row): string {
            return match (mb_strtolower(trim((string) ($row->source_badge ?? '')))) {
                'mission' => 'source-mission',
                'extra' => 'source-extra',
                default => 'source-project',
            };
        };
        $specialField = static function ($row, string $label): string {
            $fields = collect(data_get($row, 'special.fields', []));

            return (string) ($fields->firstWhere('label', $label)['value'] ?? '');
        };
        $specialPeople = static function ($row): string {
            return collect(data_get($row, 'special.people', []))->filter()->implode(', ');
        };
    @endphp

    <table>
        <tr><td colspan="20" class="meta">รายงาน Output</td></tr>
        <tr><td colspan="20">เงื่อนไข: {{ $filterLabel }}</td></tr>
        <tr><td colspan="20">ออกรายงาน: {{ $generatedLabel }}</td></tr>
        <tr><td colspan="20">ผู้สร้างรายงาน: {{ $generatedBy }}</td></tr>
        <tr><td colspan="20">ทั้งหมด {{ number_format($summary['total']) }} รายการ | Project {{ number_format($summary['project']) }} | Mission {{ number_format($summary['mission']) }} | Extra {{ number_format($summary['extra']) }}</td></tr>
        <tr></tr>
        <tr>
            <th>#</th>
            <th>ชื่อผลงาน</th>
            <th>ประเภท</th>
            <th>ปี พ.ศ.</th>
            <th>ที่มา</th>
            <th>รายการอ้างอิง</th>
            <th>รายละเอียด</th>
            <th>ลิงก์ผลงาน</th>
            <th>Journal</th>
            <th>Ranking</th>
            <th>Impact Factor</th>
            <th>Authors</th>
            <th>Conference</th>
            <th>ประเภทการนำเสนอ</th>
            <th>สถานที่</th>
            <th>Presenter</th>
            <th>Awardee</th>
            <th>Book</th>
            <th>Publisher</th>
            <th>Pages</th>
            <th>ISBN</th>
            <th>วันที่ลงข้อมูล</th>
        </tr>
        @forelse ($rows as $index => $row)
            @php
                $created = $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null;
                $createdLabel = $created ? $created->locale('th')->translatedFormat('j F') . ' ' . ($created->year + 543) : '-';
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->title ?: '-' }}</td>
                <td>{{ $row->type ?: '-' }}</td>
                <td>{{ $row->year_be ?: '-' }}</td>
                <td class="{{ $sourceClass($row) }}">{{ $sourceLabel($row) }}</td>
                <td>{{ $row->project_label ?: '-' }}</td>
                <td>{{ $row->detail ?: '-' }}</td>
                <td>{{ $row->related_link ?: ($row->link ?: '-') }}</td>
                <td>{{ $specialField($row, 'Journal') ?: '-' }}</td>
                <td>{{ $specialField($row, 'Ranking') ?: '-' }}</td>
                <td>{{ $specialField($row, 'IF') ?: '-' }}</td>
                <td>{{ str_starts_with((string) ($row->type ?? ''), 'EA101') || str_contains((string) ($row->type ?? ''), 'Research Paper') ? ($specialPeople($row) ?: '-') : '-' }}</td>
                <td>{{ $specialField($row, 'Conference') ?: '-' }}</td>
                <td>{{ $specialField($row, 'Type') ?: '-' }}</td>
                <td>{{ $specialField($row, 'Place') ?: '-' }}</td>
                <td>{{ str_starts_with((string) ($row->type ?? ''), 'EA103') || str_contains((string) ($row->type ?? ''), 'Presentations') ? ($specialPeople($row) ?: '-') : '-' }}</td>
                <td>{{ str_starts_with((string) ($row->type ?? ''), 'EA118') || str_contains((string) ($row->type ?? ''), 'Award') ? ($specialPeople($row) ?: '-') : '-' }}</td>
                <td>{{ $specialField($row, 'Book') ?: '-' }}</td>
                <td>{{ $specialField($row, 'Publisher') ?: '-' }}</td>
                <td>{{ $specialField($row, 'Pages') ?: '-' }}</td>
                <td>{{ $specialField($row, 'ISBN') ?: '-' }}</td>
                <td>{{ $createdLabel }}</td>
            </tr>
        @empty
            <tr><td colspan="20">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td></tr>
        @endforelse
    </table>
</body>
</html>

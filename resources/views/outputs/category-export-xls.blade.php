<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th { background: #dbeafe; color: #0f172a; font-weight: bold; border: 1px solid #94a3b8; padding: 8px; }
        td { border: 1px solid #cbd5e1; padding: 8px; vertical-align: top; mso-number-format: "\\@"; }
        .meta { background: #f8fafc; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F') . ' ' . ($generatedAt->year + 543) . $generatedAt->translatedFormat(' เวลา H:i');
        $valueOf = static function ($row, string $key) {
            if ($key === 'source_badge') {
                return trim((string) ($row->source_badge ?? '')) !== '' ? $row->source_badge : 'Project';
            }
            if ($key === 'authors_text') {
                if (!empty($row->authors_text)) {
                    return $row->authors_text;
                }
                if (!empty($row->authors_by_role) && is_iterable($row->authors_by_role)) {
                    $roleLabels = [
                        'first' => 'First-author',
                        'co' => 'Co-author',
                        'corresponding' => 'Corresponding-author',
                    ];

                    return collect($row->authors_by_role)
                        ->flatMap(function ($names, $role) use ($roleLabels) {
                            $roleLabel = $roleLabels[$role] ?? ucfirst((string) $role);

                            return collect($names)
                                ->filter()
                                ->map(fn ($name) => "{$name} ({$roleLabel})");
                        })
                        ->values()
                        ->implode(', ');
                }
                if (!empty($row->authors) && is_iterable($row->authors)) {
                    return collect($row->authors)->filter()->implode(', ');
                }
            }
            return data_get($row, $key, '-');
        };
    @endphp
    <table>
        <tr><td colspan="{{ count($columns) + 1 }}" class="meta">{{ $title }}</td></tr>
        <tr><td colspan="{{ count($columns) + 1 }}">ออกรายงาน: {{ $generatedLabel }}</td></tr>
        <tr><td colspan="{{ count($columns) + 1 }}">เงื่อนไข: {{ $filters->isNotEmpty() ? $filters->implode(' | ') : 'ทั้งหมด' }}</td></tr>
        <tr><td colspan="{{ count($columns) + 1 }}">ทั้งหมด {{ number_format($rows->count()) }} รายการ</td></tr>
        <tr></tr>
        <tr>
            <th>#</th>
            @foreach ($columns as $label)
                <th>{{ $label }}</th>
            @endforeach
        </tr>
        @forelse ($rows as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                @foreach ($columns as $key => $label)
                    <td>{{ $valueOf($row, $key) ?: '-' }}</td>
                @endforeach
            </tr>
        @empty
            <tr><td colspan="{{ count($columns) + 1 }}">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td></tr>
        @endforelse
    </table>
</body>
</html>

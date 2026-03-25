<table class="gantt-export-table">
    <thead>
        <tr>
            <th class="col-no">#</th>
            <th class="col-task">งาน</th>
            <th class="col-owner">ผู้รับผิดชอบ</th>
            <th class="col-status">สถานะ</th>
            <th class="col-date">เริ่ม</th>
            <th class="col-date">สิ้นสุด</th>
            @foreach($timeline['periods'] as $period)
                <th class="col-period">{!! $period['label'] !!}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $index => $row)
            <tr class="{{ $row['is_summary'] ? 'row-summary' : '' }}">
                <td class="cell-no">{{ $index + 1 }}</td>
                <td class="cell-task">
                    <div class="task-title level-{{ $row['level'] }}">{{ $row['text'] }}</div>
                </td>
                <td>{{ $row['owner'] }}</td>
                <td>{{ $row['status_label'] }}</td>
                <td>{{ $row['start_label'] }}</td>
                <td>{{ $row['end_label'] }}</td>
                @foreach($row['cells'] as $cell)
                    @php
                        $bg = match($cell['class']) {
                            'is-mid' => '#99f6e4',
                            'is-start' => '#14b8a6',
                            'is-end' => '#0f766e',
                            'is-single' => '#0d9488',
                            default => '#ffffff',
                        };
                    @endphp
                    <td class="timeline-cell {{ $cell['class'] }}" style="background-color: {{ $bg }};">&nbsp;</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ 6 + ($timeline['column_count'] ?? 1) }}" class="empty-cell">ไม่มีข้อมูลงานตามเงื่อนไขที่เลือก</td>
            </tr>
        @endforelse
    </tbody>
</table>

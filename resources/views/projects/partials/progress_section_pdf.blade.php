@php
    $latestProgressReport = ($project->progressReports ?? collect())->first();
@endphp
<div class="section-title">I: ความก้าวหน้าของการดำเนินงาน</div>
@if ($latestProgressReport)
    @php
        $progressTitle = text_or_dash($latestProgressReport->progress_title ?: 'ความก้าวหน้าของการดำเนินงาน');
        $progressSubtitle = text_or_dash($latestProgressReport->progress_subtitle);
        $progressRows = collect($latestProgressReport->progress_rows ?? [])
            ->filter(function ($row) {
                return !empty(trim((string) ($row['plan'] ?? ''))) ||
                    !empty(trim((string) ($row['actual'] ?? ''))) ||
                    !empty(trim((string) ($row['alignment'] ?? '')));
            })
            ->values();
        $issueRows = collect($latestProgressReport->issue_rows ?? [])
            ->filter(function ($row) {
                return !empty(trim((string) ($row['problem'] ?? ''))) ||
                    !empty(trim((string) ($row['solution'] ?? ''))) ||
                    !empty(trim((string) ($row['consultation'] ?? '')));
            })
            ->values();
    @endphp

    <table>
        <tbody>
            <tr>
                <td style="width: 50%;">
                    <strong>ผู้รายงาน:</strong> {{ text_or_dash($latestProgressReport->submitter_name) }}
                </td>
                <td style="width: 50%;">
                    <strong>วันที่รายงาน:</strong> {{ th_date_or_dash($latestProgressReport->submit_date) }}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>หัวข้อ:</strong> {{ $progressTitle }}
                    @if ($progressSubtitle !== '-')
                        <br><strong>คำอธิบาย:</strong> {{ $progressSubtitle }}
                    @endif
                    @if (!empty($latestProgressReport->file_info))
                        <br><strong>ไฟล์/ลิงก์:</strong> {{ text_or_dash($latestProgressReport->file_info) }}
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>แผนการดำเนินงาน</th>
                <th>การดำเนินงานจริง</th>
                <th>ความสอดคล้องของการดำเนินงานจริงกับแผนการดำเนินงาน</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($progressRows as $row)
                <tr>
                    <td>{{ text_or_dash($row['plan'] ?? '') }}</td>
                    <td>{{ text_or_dash($row['actual'] ?? '') }}</td>
                    <td>{{ text_or_dash($row['alignment'] ?? '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="check">ไม่มีข้อมูลกิจกรรม</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>ปัญหาอุปสรรค</th>
                <th>แนวทางการแก้ปัญหา</th>
                <th>ประเด็นขอคำปรึกษาจากที่ประชุม</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($issueRows as $row)
                <tr>
                    <td>{{ text_or_dash($row['problem'] ?? '') }}</td>
                    <td>{{ text_or_dash($row['solution'] ?? '') }}</td>
                    <td>{{ text_or_dash($row['consultation'] ?? '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="check">ไม่มีข้อมูลปัญหาอุปสรรค</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@else
    <table>
        <tbody>
            <tr>
                <td class="check">ไม่มีข้อมูลความก้าวหน้าของการดำเนินงาน</td>
            </tr>
        </tbody>
    </table>
@endif

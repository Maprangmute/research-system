@php
    $latestProgressReport = ($project->progressReports ?? collect())->first();
@endphp
<div class="section-title section-title--progress">I: ความก้าวหน้าของการดำเนินงาน</div>
<div class="section-subtitle">สถานะความก้าวหน้าล่าสุดของโครงการ ทั้งกิจกรรมที่ดำเนินการจริงและประเด็นปัญหาอุปสรรค</div>
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

    <div class="progress-overview">
        <div class="progress-kicker">Progress Overview</div>
        <div class="progress-headline">{{ $progressTitle }}</div>
        @if ($progressSubtitle !== '-')
            <div style="margin-top: 8px; color: #475569; font-size: 11.1pt; line-height: 1.65;">
                {{ $progressSubtitle }}
            </div>
        @endif

        <div>
            <span class="progress-meta-chip">ผู้รายงาน {{ text_or_dash($latestProgressReport->submitter_name) }}</span>
            <span class="progress-meta-chip">วันที่รายงาน {{ th_date_or_dash($latestProgressReport->submit_date) }}</span>
        </div>

        @if (!empty($latestProgressReport->file_info))
            <div class="progress-file">
                <strong>ไฟล์/ลิงก์:</strong> {{ text_or_dash($latestProgressReport->file_info) }}
            </div>
        @endif
    </div>

    <div class="progress-group-title">กิจกรรมที่ดำเนินการ</div>
    @forelse ($progressRows as $index => $row)
        <div class="progress-card">
            <div class="progress-card-title">รายการที่ {{ $index + 1 }}</div>
            <table class="progress-card-grid progress-card-grid--activity">
                <tr>
                    <td>
                        <div class="progress-card-label">แผนการดำเนินงาน</div>
                        <div class="progress-card-body">{{ text_or_dash($row['plan'] ?? '') }}</div>
                    </td>
                    <td>
                        <div class="progress-card-label">การดำเนินงานจริง</div>
                        <div class="progress-card-body">{{ text_or_dash($row['actual'] ?? '') }}</div>
                    </td>
                    <td>
                        <div class="progress-card-label">สถานะเทียบแผน</div>
                        <div class="progress-card-body">{{ text_or_dash($row['alignment'] ?? '') }}</div>
                    </td>
                </tr>
            </table>
        </div>
    @empty
        <div class="progress-empty">ไม่มีข้อมูลกิจกรรม</div>
    @endforelse

    <div class="progress-group-title">ปัญหาอุปสรรคและแนวทางแก้ไข</div>
    @forelse ($issueRows as $index => $row)
        <div class="progress-card">
            <div class="progress-card-title">ประเด็นที่ {{ $index + 1 }}</div>
            <table class="progress-card-grid progress-card-grid--issues">
                <tr>
                    <td>
                        <div class="progress-card-label">ปัญหาอุปสรรค</div>
                        <div class="progress-card-body">{{ text_or_dash($row['problem'] ?? '') }}</div>
                    </td>
                    <td>
                        <div class="progress-card-label">แนวทางการแก้ปัญหา</div>
                        <div class="progress-card-body">{{ text_or_dash($row['solution'] ?? '') }}</div>
                    </td>
                    <td>
                        <div class="progress-card-label">ประเด็นขอคำปรึกษา</div>
                        <div class="progress-card-body">{{ text_or_dash($row['consultation'] ?? '') }}</div>
                    </td>
                </tr>
            </table>
        </div>
    @empty
        <div class="progress-empty">ไม่มีข้อมูลปัญหาอุปสรรค</div>
    @endforelse
@else
    <div class="progress-empty">ไม่มีข้อมูลความก้าวหน้าของการดำเนินงาน</div>
@endif

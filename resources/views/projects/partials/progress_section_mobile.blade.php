@php
    $latestProgressReport = ($project->progressReports ?? collect())->first();
    $alignmentBadgeClass = function ($text) {
        $normalized = mb_strtolower(trim((string) $text));
        if ($normalized === '') {
            return 'bg-slate-100 text-slate-700';
        }
        if (str_contains($normalized, 'ช้ากว่าแผน') && str_contains($normalized, 'ไม่กระทบ')) {
            return 'bg-amber-100 text-amber-800';
        }
        if (str_contains($normalized, 'ช้ากว่าแผน') || str_contains($normalized, 'ล่าช้า') || str_contains($normalized, 'ช้า')) {
            return 'bg-orange-100 text-orange-800';
        }
        if (str_contains($normalized, 'เป็นไปตามแผน')) {
            return 'bg-emerald-100 text-emerald-800';
        }
        return 'bg-slate-100 text-slate-700';
    };
@endphp
<div class="mobile-section space-y-3">
    <div class="flex items-center justify-between gap-2">
        <h3 class="font-semibold text-lg text-blue-700">I: ความก้าวหน้าของการดำเนินงาน</h3>
        <a href="{{ route('projects.progress_reports.index', $project) }}"
            class="text-xs px-3 py-1.5 rounded-lg bg-blue-600 text-white">ทั้งหมด</a>
    </div>

    @if ($latestProgressReport)
        @php
            $latestSubmitDate = $latestProgressReport->submit_date
                ? \Carbon\Carbon::parse($latestProgressReport->submit_date)->locale('th')->translatedFormat('j F') .
                    ' ' .
                    (\Carbon\Carbon::parse($latestProgressReport->submit_date)->year + 543)
                : '-';
            $progressTitle = trim((string) ($latestProgressReport->progress_title ?? '')) ?: 'ความก้าวหน้าของการดำเนินงาน';
            $progressSubtitle = trim((string) ($latestProgressReport->progress_subtitle ?? ''));
            $latestProgressRows = collect($latestProgressReport->progress_rows ?? [])
                ->filter(function ($row) {
                    return !empty(trim((string) ($row['plan'] ?? ''))) ||
                        !empty(trim((string) ($row['actual'] ?? ''))) ||
                        !empty(trim((string) ($row['alignment'] ?? '')));
                })
                ->values();
            $latestIssueRows = collect($latestProgressReport->issue_rows ?? [])
                ->filter(function ($row) {
                    return !empty(trim((string) ($row['problem'] ?? ''))) ||
                        !empty(trim((string) ($row['solution'] ?? ''))) ||
                        !empty(trim((string) ($row['consultation'] ?? '')));
                })
                ->values();
        @endphp

        <div class="mobile-card space-y-2">
            <div class="font-semibold text-slate-900">{{ $progressTitle }}</div>
            @if ($progressSubtitle !== '')
                <div class="text-sm text-slate-600">{{ $progressSubtitle }}</div>
            @endif
            <div class="text-xs text-slate-500">รายงานล่าสุด: {{ $latestSubmitDate }} โดย {{ $latestProgressReport->submitter_name ?: '-' }}</div>
            @if ($latestProgressReport->file_info)
                <div class="text-xs text-slate-600 break-words">ไฟล์/ลิงก์: {{ $latestProgressReport->file_info }}</div>
            @endif
        </div>

        <div class="mobile-card space-y-2">
            <div class="font-semibold text-slate-800 text-sm">กิจกรรมตามแผนการดำเนินงาน</div>
            @forelse ($latestProgressRows as $i => $row)
                @php $alignmentText = trim((string) ($row['alignment'] ?? '')); @endphp
                <div class="list-card">
                    <div class="list-index">{{ $i + 1 }}</div>
                    <div class="space-y-1">
                        <div class="text-xs text-slate-500">แผน</div>
                        <div class="text-sm text-slate-800">{{ trim((string) ($row['plan'] ?? '')) ?: '-' }}</div>
                        <div class="text-xs text-slate-500 mt-1">ดำเนินการจริง</div>
                        <div class="text-sm text-slate-800">{{ trim((string) ($row['actual'] ?? '')) ?: '-' }}</div>
                        <div class="mt-2">
                            <span class="tag {{ $alignmentBadgeClass($alignmentText) }}">{{ $alignmentText !== '' ? $alignmentText : '-' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-sm text-slate-500">ไม่มีข้อมูลกิจกรรม</div>
            @endforelse
        </div>

        <div class="mobile-card space-y-2">
            <div class="font-semibold text-slate-800 text-sm">ปัญหาอุปสรรค</div>
            @forelse ($latestIssueRows as $i => $row)
                <div class="list-card">
                    <div class="list-index">{{ $i + 1 }}</div>
                    <div class="space-y-1">
                        <div class="text-xs text-slate-500">ปัญหา</div>
                        <div class="text-sm text-slate-800">{{ trim((string) ($row['problem'] ?? '')) ?: '-' }}</div>
                        <div class="text-xs text-slate-500 mt-1">แนวทางแก้ไข</div>
                        <div class="text-sm text-slate-800">{{ trim((string) ($row['solution'] ?? '')) ?: '-' }}</div>
                        <div class="text-xs text-slate-500 mt-1">ประเด็นปรึกษา</div>
                        <div class="text-sm text-slate-800">{{ trim((string) ($row['consultation'] ?? '')) ?: '-' }}</div>
                    </div>
                </div>
            @empty
                <div class="text-sm text-slate-500">ไม่มีข้อมูลปัญหาอุปสรรค</div>
            @endforelse
        </div>
    @else
        <div class="mobile-card text-sm text-slate-500">
            ยังไม่มีรายงานความก้าวหน้า
            <a href="{{ route('projects.progress_reports.create', $project) }}" class="text-blue-600 underline ml-1">เพิ่มรายงาน</a>
        </div>
    @endif
</div>

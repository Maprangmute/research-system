@php
    $latestProgressReport = ($project->progressReports ?? collect())->first();
@endphp
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3 mb-3">
        <div>
            <h3 class="font-semibold text-lg text-blue-700">I: ความก้าวหน้าของการดำเนินงาน</h3>
            @if ($latestProgressReport)
                @php
                    $latestSubmitDate = $latestProgressReport->submit_date
                        ? \Carbon\Carbon::parse($latestProgressReport->submit_date)->locale('th')->translatedFormat('j F') .
                            ' ' .
                            (\Carbon\Carbon::parse($latestProgressReport->submit_date)->year + 543)
                        : '-';
                @endphp
                <p class="text-sm text-slate-600 mt-1">
                    อ้างอิงรายงานล่าสุดวันที่ {{ $latestSubmitDate }}
                    โดย {{ $latestProgressReport->submitter_name ?: '-' }}
                </p>
            @else
                <p class="text-sm text-slate-500 mt-1">ยังไม่มีรายงานความก้าวหน้า</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('projects.progress_reports.index', $project->id) }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                รายงานทั้งหมด
            </a>
            <a href="{{ route('projects.progress_reports.create', $project->id) }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm bg-white border border-blue-200 text-blue-700 rounded-lg hover:bg-blue-50">
                เพิ่มรายงาน
            </a>
        </div>
    </div>

    @if ($latestProgressReport)
        @php
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
            $alignmentClass = function ($text) {
                $normalized = mb_strtolower(trim((string) $text));
                if ($normalized === '') {
                    return 'text-slate-500';
                }
                if (str_contains($normalized, 'ช้ากว่าแผน') && str_contains($normalized, 'ไม่กระทบ')) {
                    return 'text-amber-700';
                }
                if (str_contains($normalized, 'ช้ากว่าแผน') || str_contains($normalized, 'ล่าช้า') || str_contains($normalized, 'ช้า')) {
                    return 'text-orange-700';
                }
                if (str_contains($normalized, 'เป็นไปตามแผน')) {
                    return 'text-emerald-700';
                }
                return 'text-slate-700';
            };
        @endphp

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 mb-4">
            <div class="font-semibold text-slate-900">{{ $progressTitle }}</div>
            @if ($progressSubtitle !== '')
                <div class="text-sm text-slate-600 mt-1">{{ $progressSubtitle }}</div>
            @endif
            @if ($latestProgressReport->file_info)
                <div class="text-sm text-slate-600 mt-1">
                    ไฟล์/ลิงก์: {{ $latestProgressReport->file_info }}
                </div>
            @endif
        </div>

        <h4 class="font-semibold text-slate-800 mb-2">กิจกรรมตามแผนการดำเนินงาน</h4>
        <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200 bg-white">
            <table class="min-w-full text-sm border-separate border-spacing-0">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="border px-3 py-2 text-left font-semibold">แผนการดำเนินงาน</th>
                        <th class="border px-3 py-2 text-left font-semibold">การดำเนินงานจริง</th>
                        <th class="border px-3 py-2 text-left font-semibold">ความสอดคล้องของการดำเนินงานจริงกับแผนการดำเนินงาน</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestProgressRows as $row)
                        @php $alignmentText = trim((string) ($row['alignment'] ?? '')); @endphp
                        <tr class="odd:bg-[#eef5fa]">
                            <td class="border px-3 py-2 align-top">{{ trim((string) ($row['plan'] ?? '')) ?: '-' }}</td>
                            <td class="border px-3 py-2 align-top">{{ trim((string) ($row['actual'] ?? '')) ?: '-' }}</td>
                            <td class="border px-3 py-2 align-top font-semibold {{ $alignmentClass($alignmentText) }}">
                                {{ $alignmentText !== '' ? $alignmentText : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="border px-3 py-5 text-center text-slate-500">ไม่มีข้อมูลกิจกรรม</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h4 class="font-semibold text-slate-800 mt-5 mb-2">ปัญหาอุปสรรค</h4>
        <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200 bg-white">
            <table class="min-w-full text-sm border-separate border-spacing-0">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="border px-3 py-2 text-left font-semibold">ปัญหาอุปสรรค</th>
                        <th class="border px-3 py-2 text-left font-semibold">แนวทางการแก้ปัญหา</th>
                        <th class="border px-3 py-2 text-left font-semibold">ประเด็นขอคำปรึกษาจากที่ประชุม</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestIssueRows as $row)
                        <tr class="odd:bg-[#eef5fa]">
                            <td class="border px-3 py-2 align-top">{{ trim((string) ($row['problem'] ?? '')) ?: '-' }}</td>
                            <td class="border px-3 py-2 align-top">{{ trim((string) ($row['solution'] ?? '')) ?: '-' }}</td>
                            <td class="border px-3 py-2 align-top">{{ trim((string) ($row['consultation'] ?? '')) ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="border px-3 py-5 text-center text-slate-500">ไม่มีข้อมูลปัญหาอุปสรรค</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>

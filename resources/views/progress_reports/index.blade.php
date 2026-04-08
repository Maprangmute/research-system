@extends('layouts.app')

@section('content')
    @php
        $isModalView = request()->boolean('modal');
        $modalQuery = $isModalView ? ['modal' => 1] : [];

        $formatDateThai = function ($value) {
            if (!$value) {
                return '-';
            }
            try {
                $d = \Carbon\Carbon::parse($value)->locale('th');
                return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
            } catch (\Throwable $e) {
                return (string) $value;
            }
        };
    @endphp

    <div class="max-w-7xl mx-auto mt-6 px-3 space-y-4">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 md:p-6">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                <div class="space-y-2 min-w-0">
                    @unless ($isModalView)
                        <a href="{{ route('projects.show', ['project' => $project] + $modalQuery) }}" class="text-blue-600 hover:underline text-sm">
                            ← กลับหน้าโครงการ
                        </a>
                    @endunless
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900">
                        รายงานความก้าวหน้าของการดำเนินงาน
                    </h1>
                    <p class="text-sm text-slate-600 break-words">โครงการ: {{ $project->project_name_th }}</p>
                </div>
                <a href="{{ route('projects.progress_reports.create', ['project' => $project] + $modalQuery) }}"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                    เพิ่มรายงาน
                </a>
            </div>
        </div>

        <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-3 py-3 text-center text-sm font-semibold text-slate-700 w-14">#</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold text-slate-700">ผู้จัดทำ</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold text-slate-700">วันที่รายงาน</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold text-slate-700">หัวข้อรายงาน</th>
                        <th class="px-3 py-3 text-center text-sm font-semibold text-slate-700">กิจกรรม</th>
                        <th class="px-3 py-3 text-center text-sm font-semibold text-slate-700">ปัญหา</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold text-slate-700">รายละเอียดไฟล์/ลิงก์</th>
                        <th class="px-3 py-3 text-center text-sm font-semibold text-slate-700 w-[270px]">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($progressReports as $i => $report)
                        @php
                            $progressCount = collect($report->progress_rows ?? [])->count();
                            $issueCount = collect($report->issue_rows ?? [])->count();
                            $reportTitle = trim((string) ($report->progress_title ?? '')) ?: 'ความก้าวหน้าของการดำเนินงาน';
                        @endphp
                        <tr class="border-t border-slate-200 odd:bg-white even:bg-slate-50">
                            <td class="px-3 py-3 text-center text-sm text-slate-700">{{ $i + 1 }}</td>
                            <td class="px-3 py-3 text-sm text-slate-800">{{ $report->submitter_name ?: '-' }}</td>
                            <td class="px-3 py-3 text-sm text-slate-700 whitespace-nowrap">{{ $formatDateThai($report->submit_date) }}</td>
                            <td class="px-3 py-3 text-sm font-semibold text-slate-900 max-w-[380px] break-words">{{ $reportTitle }}</td>
                            <td class="px-3 py-3 text-center text-sm font-semibold text-slate-700">{{ $progressCount }}</td>
                            <td class="px-3 py-3 text-center text-sm font-semibold text-slate-700">{{ $issueCount }}</td>
                            <td class="px-3 py-3 text-sm text-slate-700 break-words">{{ $report->file_info ?: '-' }}</td>
                            <td class="px-3 py-3 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <a href="{{ route('projects.progress_reports.show', ['project' => $project, 'progress_report' => $report->id] + $modalQuery) }}"
                                        class="px-3 py-1.5 rounded-lg border border-sky-200 bg-sky-50 text-sky-700 text-sm font-semibold hover:bg-sky-100">
                                        ดูรายละเอียด
                                    </a>
                                    <a href="{{ route('projects.progress_reports.edit', ['project' => $project, 'progress_report' => $report->id] + $modalQuery) }}"
                                        class="px-3 py-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 text-sm font-semibold hover:bg-amber-100">
                                        แก้ไข
                                    </a>
                                    <form action="{{ route('projects.progress_reports.destroy', ['project' => $project, 'progress_report' => $report->id] + $modalQuery) }}" method="POST"
                                        onsubmit="return confirm('ยืนยันการลบ?')">
                                        @csrf
                                        @method('DELETE')
                                        @if ($isModalView)
                                            <input type="hidden" name="modal" value="1">
                                        @endif
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 text-sm font-semibold hover:bg-rose-100">
                                            ลบ
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-10 text-center text-sm text-slate-500">
                                ยังไม่มีรายงานความก้าวหน้าในโครงการนี้
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-3">
            @forelse ($progressReports as $i => $report)
                @php
                    $progressCount = collect($report->progress_rows ?? [])->count();
                    $issueCount = collect($report->issue_rows ?? [])->count();
                    $reportTitle = trim((string) ($report->progress_title ?? '')) ?: 'ความก้าวหน้าของการดำเนินงาน';
                @endphp
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs text-slate-500">รายงานที่ {{ $i + 1 }}</div>
                            <div class="text-sm font-semibold text-slate-900 break-words">{{ $reportTitle }}</div>
                            <div class="text-base font-semibold text-slate-900">{{ $report->submitter_name ?: '-' }}</div>
                            <div class="text-sm text-slate-600">{{ $formatDateThai($report->submit_date) }}</div>
                        </div>
                        <div class="text-right text-xs text-slate-500">
                            <div>กิจกรรม {{ $progressCount }}</div>
                            <div>ปัญหา {{ $issueCount }}</div>
                        </div>
                    </div>

                    <div class="text-sm text-slate-700 break-words">
                        <span class="font-semibold">ไฟล์/ลิงก์:</span> {{ $report->file_info ?: '-' }}
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('projects.progress_reports.show', ['project' => $project, 'progress_report' => $report->id] + $modalQuery) }}"
                            class="px-3 py-2 rounded-lg border border-sky-200 bg-sky-50 text-sky-700 text-sm font-semibold">
                            ดูรายละเอียด
                        </a>
                        <a href="{{ route('projects.progress_reports.edit', ['project' => $project, 'progress_report' => $report->id] + $modalQuery) }}"
                            class="px-3 py-2 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 text-sm font-semibold">
                            แก้ไข
                        </a>
                        <form action="{{ route('projects.progress_reports.destroy', ['project' => $project, 'progress_report' => $report->id] + $modalQuery) }}" method="POST"
                            onsubmit="return confirm('ยืนยันการลบ?')">
                            @csrf
                            @method('DELETE')
                            @if ($isModalView)
                                <input type="hidden" name="modal" value="1">
                            @endif
                            <button type="submit"
                                class="px-3 py-2 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 text-sm font-semibold">
                                ลบ
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6 text-center text-sm text-slate-500">
                    ยังไม่มีรายงานความก้าวหน้าในโครงการนี้
                </div>
            @endforelse
        </div>
    </div>

    @if ($isModalView && session('success'))
        @php
            $latestReportForModal = $progressReports->first();
            $latestReportTitleForModal = $latestReportForModal
                ? (trim((string) ($latestReportForModal->progress_title ?? '')) ?: 'ความก้าวหน้าของการดำเนินงาน')
                : '';
            $latestReportDateForModal = $latestReportForModal ? $formatDateThai($latestReportForModal->submit_date) : '';
            $latestReportSubmitterForModal = $latestReportForModal ? (string) ($latestReportForModal->submitter_name ?? '') : '';
            $latestReportEditUrlForModal = $latestReportForModal
                ? route('projects.progress_reports.edit', [
                    'project' => $project,
                    'progress_report' => $latestReportForModal->id,
                    'modal' => 1,
                ])
                : '';
        @endphp
        <script>
            (() => {
                if (!window.parent || window.parent === window) return;
                window.parent.postMessage({
                    type: 'progress-report:updated',
                    projectId: @json($project->id),
                    summary: {
                        count: @json($progressReports->count()),
                        latestDate: @json($latestReportDateForModal),
                        submitter: @json($latestReportSubmitterForModal),
                        title: @json($latestReportTitleForModal),
                        editUrl: @json($latestReportEditUrlForModal),
                    },
                }, window.location.origin);
            })();
        </script>
    @endif
@endsection

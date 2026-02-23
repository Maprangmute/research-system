@extends('layouts.app')

@section('content')
    @php
        $isModalView = request()->boolean('modal');
        $modalQuery = $isModalView ? ['modal' => 1] : [];

        $progressTitle = trim((string) ($report->progress_title ?? '')) ?: 'ความก้าวหน้าของการดำเนินงาน';
        $progressSubtitle = trim((string) ($report->progress_subtitle ?? ''));

        $progressRows = collect($report->progress_rows ?? [])
            ->filter(function ($row) {
                return !empty(trim((string) ($row['plan'] ?? ''))) ||
                    !empty(trim((string) ($row['actual'] ?? ''))) ||
                    !empty(trim((string) ($row['alignment'] ?? '')));
            })
            ->values();

        $issueRows = collect($report->issue_rows ?? [])
            ->filter(function ($row) {
                return !empty(trim((string) ($row['problem'] ?? ''))) ||
                    !empty(trim((string) ($row['solution'] ?? ''))) ||
                    !empty(trim((string) ($row['consultation'] ?? '')));
            })
            ->values();

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

    <div class="max-w-7xl mx-auto mt-6 px-3 space-y-4">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 md:p-6">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex flex-wrap gap-3 text-sm">
                        <a href="{{ route('projects.show', ['project' => $project->id] + $modalQuery) }}" class="text-blue-600 hover:underline">← กลับหน้าโครงการ</a>
                        <a href="{{ route('projects.progress_reports.index', ['project' => $project->id] + $modalQuery) }}" class="text-blue-600 hover:underline">กลับหน้ารายงานความก้าวหน้า</a>
                    </div>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900">{{ $progressTitle }}</h1>
                    @if ($progressSubtitle !== '')
                        <p class="text-sm md:text-base text-slate-600">{{ $progressSubtitle }}</p>
                    @endif
                    <p class="text-sm text-slate-500">โครงการ: {{ $project->project_name_th }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('projects.progress_reports.edit', ['project' => $project->id, 'progress_report' => $report->id] + $modalQuery) }}"
                        class="px-3 py-2 rounded-lg bg-amber-100 text-amber-800 text-sm font-semibold hover:bg-amber-200">
                        แก้ไข
                    </a>
                    <form action="{{ route('projects.progress_reports.destroy', ['project' => $project->id, 'progress_report' => $report->id] + $modalQuery) }}" method="POST"
                        onsubmit="return confirm('ยืนยันการลบ?')" class="inline">
                        @csrf
                        @method('DELETE')
                        @if ($isModalView)
                            <input type="hidden" name="modal" value="1">
                        @endif
                        <button type="submit"
                            class="px-3 py-2 rounded-lg bg-rose-100 text-rose-800 text-sm font-semibold hover:bg-rose-200">
                            ลบ
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    <div class="text-slate-500">ผู้จัดทำ</div>
                    <div class="font-semibold text-slate-900">{{ $report->submitter_name ?: '-' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    <div class="text-slate-500">วันที่รายงาน</div>
                    <div class="font-semibold text-slate-900">{{ $formatDateThai($report->submit_date) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    <div class="text-slate-500">กิจกรรมทั้งหมด</div>
                    <div class="font-semibold text-slate-900">{{ $progressRows->count() }} รายการ</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    <div class="text-slate-500">ปัญหาอุปสรรค</div>
                    <div class="font-semibold text-slate-900">{{ $issueRows->count() }} รายการ</div>
                </div>
            </div>

            <div class="mt-3 text-sm text-slate-700">
                <span class="font-semibold">รายละเอียดไฟล์/ลิงก์:</span> {{ $report->file_info ?: '-' }}
            </div>
            <div class="mt-1 text-xs text-slate-500">
                สร้างโดย {{ $report->creator->name ?? '-' }}
                · อัปเดตโดย {{ $report->updater->name ?? '-' }}
                · ล่าสุด {{ $report->updated_at ? $report->updated_at->diffForHumans() : '-' }}
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-bold text-slate-900">{{ $progressTitle }}</h2>
            @if ($progressSubtitle !== '')
                <p class="text-sm text-slate-600 mt-1">{{ $progressSubtitle }}</p>
            @endif

            @if ($progressRows->isEmpty())
                <div class="mt-4 text-sm text-slate-500">ยังไม่มีข้อมูลความก้าวหน้า</div>
            @else
                <div class="hidden md:block mt-4 overflow-x-auto">
                    <table class="min-w-full border border-slate-300">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="border border-slate-300 px-3 py-2 text-left text-sm font-semibold w-[34%]">แผนการดำเนินงาน</th>
                                <th class="border border-slate-300 px-3 py-2 text-left text-sm font-semibold w-[33%]">การดำเนินงานจริง</th>
                                <th class="border border-slate-300 px-3 py-2 text-left text-sm font-semibold w-[33%]">ความสอดคล้องกับแผน</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($progressRows as $row)
                                @php $alignment = trim((string) ($row['alignment'] ?? '')); @endphp
                                <tr class="odd:bg-[#eef5fa]">
                                    <td class="border border-slate-300 px-3 py-2 text-sm align-top">{{ trim((string) ($row['plan'] ?? '')) ?: '-' }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-sm align-top">{{ trim((string) ($row['actual'] ?? '')) ?: '-' }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-sm align-top font-semibold {{ $alignmentClass($alignment) }}">
                                        {{ $alignment !== '' ? $alignment : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden mt-4 space-y-3">
                    @foreach ($progressRows as $i => $row)
                        @php $alignment = trim((string) ($row['alignment'] ?? '')); @endphp
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 space-y-2">
                            <div class="text-xs font-semibold text-slate-500">กิจกรรม {{ $i + 1 }}</div>
                            <div class="text-sm">
                                <div class="text-slate-500 text-xs">แผนการดำเนินงาน</div>
                                <div class="text-slate-800">{{ trim((string) ($row['plan'] ?? '')) ?: '-' }}</div>
                            </div>
                            <div class="text-sm">
                                <div class="text-slate-500 text-xs">การดำเนินงานจริง</div>
                                <div class="text-slate-800">{{ trim((string) ($row['actual'] ?? '')) ?: '-' }}</div>
                            </div>
                            <div class="text-sm">
                                <div class="text-slate-500 text-xs">ความสอดคล้องกับแผน</div>
                                <div class="font-semibold {{ $alignmentClass($alignment) }}">{{ $alignment !== '' ? $alignment : '-' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-bold text-slate-900">ปัญหาอุปสรรค</h2>

            @if ($issueRows->isEmpty())
                <div class="mt-4 text-sm text-slate-500">ยังไม่มีข้อมูลปัญหาอุปสรรค</div>
            @else
                <div class="hidden md:block mt-4 overflow-x-auto">
                    <table class="min-w-full border border-slate-300">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="border border-slate-300 px-3 py-2 text-left text-sm font-semibold w-[34%]">ปัญหาอุปสรรค</th>
                                <th class="border border-slate-300 px-3 py-2 text-left text-sm font-semibold w-[33%]">แนวทางการแก้ปัญหา</th>
                                <th class="border border-slate-300 px-3 py-2 text-left text-sm font-semibold w-[33%]">ประเด็นขอคำปรึกษาจากที่ประชุม</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($issueRows as $row)
                                <tr class="odd:bg-[#eef5fa]">
                                    <td class="border border-slate-300 px-3 py-2 text-sm align-top">{{ trim((string) ($row['problem'] ?? '')) ?: '-' }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-sm align-top">{{ trim((string) ($row['solution'] ?? '')) ?: '-' }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-sm align-top">{{ trim((string) ($row['consultation'] ?? '')) ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden mt-4 space-y-3">
                    @foreach ($issueRows as $i => $row)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 space-y-2">
                            <div class="text-xs font-semibold text-slate-500">ปัญหา {{ $i + 1 }}</div>
                            <div class="text-sm">
                                <div class="text-slate-500 text-xs">ปัญหาอุปสรรค</div>
                                <div class="text-slate-800">{{ trim((string) ($row['problem'] ?? '')) ?: '-' }}</div>
                            </div>
                            <div class="text-sm">
                                <div class="text-slate-500 text-xs">แนวทางการแก้ปัญหา</div>
                                <div class="text-slate-800">{{ trim((string) ($row['solution'] ?? '')) ?: '-' }}</div>
                            </div>
                            <div class="text-sm">
                                <div class="text-slate-500 text-xs">ประเด็นขอคำปรึกษาจากที่ประชุม</div>
                                <div class="text-slate-800">{{ trim((string) ($row['consultation'] ?? '')) ?: '-' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

<div id="presentations-results" class="space-y-6">
    <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Presentations</div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($summary['total_presentations']) }}</div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Presenters</div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($summary['unique_presenters']) }}</div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top Presenter</div>
            <div class="mt-2 text-base font-semibold text-slate-900">{{ $summary['top_presenter'] ?? '-' }}</div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top Conference</div>
            <div class="mt-2 text-base font-semibold text-slate-900">{{ $summary['top_conference'] ?? '-' }}</div>
        </article>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50 px-4 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="text-sm font-semibold text-slate-800">รายการการนำเสนอ</div>
                <div class="mt-1 text-sm text-slate-500">
                    แสดงผล {{ $outputs->firstItem() ?? 0 }}–{{ $outputs->lastItem() ?? 0 }} จากทั้งหมด {{ $outputs->total() }} รายการ
                </div>
            </div>
        </div>

        @if ($outputs->count() > 0)
            <div class="overflow-auto">
                <table class="min-w-full table-fixed">
                    <colgroup>
                        <col style="width: 22%;">
                        <col style="width: 16%;">
                        <col style="width: 14%;">
                        <col style="width: 20%;">
                        <col style="width: 10%;">
                        <col style="width: 18%;">
                    </colgroup>
                    <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold text-slate-600">
                        <tr>
                            <th class="px-4 py-3"><a href="{{ $sortUrl('title') }}">ชื่อผลงาน <span class="ml-1">{{ $sortIcon('title') }}</span></a></th>
                            <th class="px-4 py-3">ชื่อผู้นำเสนอ</th>
                            <th class="px-4 py-3"><a href="{{ $sortUrl('activity') }}">กิจกรรม <span class="ml-1">{{ $sortIcon('activity') }}</span></a></th>
                            <th class="px-4 py-3"><a href="{{ $sortUrl('conference') }}">Conference <span class="ml-1">{{ $sortIcon('conference') }}</span></a></th>
                            <th class="px-4 py-3 text-center"><a href="{{ $sortUrl('year') }}">ปี <span class="ml-1">{{ $sortIcon('year') }}</span></a></th>
                            <th class="px-4 py-3">Project Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($outputs as $row)
                            @php
                                $displayedAt = $row->displayed_at ? \Carbon\Carbon::parse($row->displayed_at)->setTimezone('Asia/Bangkok') : null;
                                $displayedAtLabel = $displayedAt
                                    ? $displayedAt->locale('th')->translatedFormat('j F') . ' ' . ($displayedAt->year + 543) . $displayedAt->translatedFormat(' H:i')
                                    : '-';
                            @endphp
                            <tr class="align-top hover:bg-indigo-50/40">
                                <td class="px-4 py-4 text-sm text-slate-900">
                                    @if (!empty($row->output_code))
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">{{ $row->output_code }}</div>
                                    @endif
                                    <div class="font-semibold leading-6">{{ $row->title }}</div>
                                    @if ($row->location !== '-')
                                        <div class="mt-1 text-xs text-slate-500">Location: {{ $row->location }}</div>
                                    @endif
                                    @if (!empty($row->detail))
                                        <div class="mt-2 text-xs leading-5 text-slate-600 break-words">
                                            รายละเอียด / ลิงก์:
                                            @if (filter_var($row->detail, FILTER_VALIDATE_URL))
                                                <a href="{{ $row->detail }}" target="_blank" rel="noopener"
                                                    class="ml-1 inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                                    ดูเอกสาร
                                                </a>
                                            @else
                                                {{ $row->detail }}
                                            @endif
                                        </div>
                                    @endif
                                    <div class="mt-2 text-xs text-slate-500">
                                        วันที่ลงข้อมูล: {{ $displayedAtLabel }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-700">{{ $row->presenter_name }}</td>
                                <td class="px-4 py-4 text-sm font-medium text-slate-700">{{ $row->presentation_type }}</td>
                                <td class="px-4 py-4 text-sm text-slate-700">{{ $row->conference_name }}</td>
                                <td class="px-4 py-4 text-center text-sm text-slate-700">{{ $row->year_be ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-700">
                                    <div class="font-medium">{{ $row->project_label }}</div>
                                    @if ($row->project_code)
                                        <div class="mt-1 text-xs text-slate-500">{{ $row->project_code }}</div>
                                    @endif
                                    @if ($row->project_link)
                                        <a href="{{ $row->project_link }}"
                                            class="mt-2 inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">
                                            {{ $row->project_code ? 'เปิด Project Detail' : 'เปิดรายละเอียด' }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 bg-slate-50 px-4 py-3">
                {{ $outputs->links('vendor.pagination.th') }}
            </div>
        @else
            <div class="px-4 py-10 text-center text-sm text-slate-500">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div>
        @endif
    </section>
</div>

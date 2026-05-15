<div id="papers-results" class="space-y-6">
    @php
        $matchedRoleBadgeClass = static function (string $roleLabel): string {
            return match ($roleLabel) {
                'First Author' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                'Co-Author' => 'border-amber-200 bg-amber-50 text-amber-800',
                'Corresponding Author' => 'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-800',
                default => 'border-slate-200 bg-slate-50 text-slate-700',
            };
        };
        $sourceBadgeClass = static function ($sourceBadge): string {
            return match (mb_strtolower(trim((string) $sourceBadge))) {
                'mission' => 'border-teal-200 bg-teal-50 text-teal-700',
                'extra' => 'border-pink-200 bg-pink-50 text-pink-700',
                default => 'border-slate-200 bg-slate-50 text-slate-700',
            };
        };
    @endphp
    <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Papers</div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($summary['total_papers']) }}</div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Unique Authors</div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($summary['unique_authors']) }}</div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top Author</div>
            <div class="mt-2 text-base font-semibold text-slate-900">{{ $summary['top_author'] ?? '-' }}</div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top Journal</div>
            <div class="mt-2 text-base font-semibold text-slate-900">{{ $summary['top_journal'] ?? '-' }}</div>
        </article>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50 px-4 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-sm font-semibold text-slate-800">รายการบทความ</div>
                <div class="mt-1 text-sm text-slate-500">
                    แสดงผล {{ $outputs->firstItem() ?? 0 }}–{{ $outputs->lastItem() ?? 0 }} จากทั้งหมด {{ $outputs->total() }} รายการ
                </div>
            </div>
        </div>

        @if ($outputs->count() > 0)
            <div class="divide-y divide-slate-100 lg:hidden">
                        @foreach ($outputs as $paper)
                            <article class="space-y-3 px-4 py-4">
                        <div>
                            @if (!empty($paper->output_code))
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">{{ $paper->output_code }}</div>
                            @endif
                            @if (!empty($paper->source_badge))
                                <div class="mt-1">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $sourceBadgeClass($paper->source_badge) }}">{{ $paper->source_badge }}</span>
                                </div>
                            @endif
                            <div class="font-semibold leading-6 text-slate-900">{{ $paper->title }}</div>
                            @if ($paper->impact_factor)
                                <div class="mt-1 text-xs text-slate-500">Impact Factor: {{ $paper->impact_factor }}</div>
                            @endif
                            @if (!empty($paper->detail))
                                <div class="mt-2 text-xs leading-5 text-slate-600 break-words">
                                    รายละเอียด / ลิงก์:
                                    @if (filter_var($paper->detail, FILTER_VALIDATE_URL))
                                        <a href="{{ $paper->detail }}" target="_blank" rel="noopener"
                                            class="ml-1 inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                            ดูเอกสาร
                                        </a>
                                    @else
                                        {{ $paper->detail }}
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="space-y-2 text-sm text-slate-700">
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Authors</div>
                                @if (request('author') && !empty($paper->matched_author_roles) && collect($paper->matched_author_roles)->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach (collect($paper->matched_author_roles) as $matchedRole)
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $matchedRoleBadgeClass((string) $matchedRole) }}">
                                                {{ $matchedRole }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                @forelse ($paper->authors_by_role as $role => $names)
                                    <div class="mt-1">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                            {{ ['first' => 'First Author', 'co' => 'Co Author', 'corresponding' => 'Corresponding Author'][$role] ?? ucfirst($role) }}
                                        </div>
                                        <div class="mt-1 leading-6">{{ $names->implode(', ') }}</div>
                                    </div>
                                @empty
                                    <div class="mt-1">-</div>
                                @endforelse
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Journal</div>
                                    <div class="mt-1">{{ $paper->journal }}</div>
                                </div>
                                <div>
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Ranking / ปี</div>
                                    <div class="mt-1">{{ $paper->quartile }} / {{ $paper->year_be ?: '-' }}</div>
                                </div>
                            </div>

                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Project Detail</div>
                                <div class="mt-1 font-medium">{{ $paper->project_label }}</div>
                                @if ($paper->project_code)
                                    <div class="mt-1 text-xs text-slate-500">{{ $paper->project_code }}</div>
                                @endif
                                @if ($paper->project_link)
                                    <a href="{{ $paper->project_link }}"
                                            class="mt-2 inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">
                                        {{ $paper->project_code ? 'เปิด Project Detail' : 'เปิดรายละเอียด' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="hidden overflow-auto lg:block">
                <table class="min-w-full table-fixed">
                    <colgroup>
                        <col style="width: 23%;">
                        <col style="width: 23%;">
                        <col style="width: 15%;">
                        <col style="width: 8%;">
                        <col style="width: 8%;">
                        <col style="width: 23%;">
                    </colgroup>
                    <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold text-slate-600">
                        <tr>
                            <th class="px-4 py-3"><a href="{{ $sortUrl('title') }}">ชื่อผลงาน <span class="ml-1">{{ $sortIcon('title') }}</span></a></th>
                            <th class="px-4 py-3">Authors</th>
                            <th class="px-4 py-3"><a href="{{ $sortUrl('journal') }}">Journal <span class="ml-1">{{ $sortIcon('journal') }}</span></a></th>
                            <th class="px-4 py-3 text-center"><a href="{{ $sortUrl('quartile') }}">Ranking <span class="ml-1">{{ $sortIcon('quartile') }}</span></a></th>
                            <th class="px-4 py-3 text-center"><a href="{{ $sortUrl('year') }}">ปี <span class="ml-1">{{ $sortIcon('year') }}</span></a></th>
                            <th class="px-4 py-3">Project Name</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($outputs as $paper)
                            @php
                                $displayedAt = $paper->displayed_at ? \Carbon\Carbon::parse($paper->displayed_at)->setTimezone('Asia/Bangkok') : null;
                                $displayedAtLabel = $displayedAt
                                    ? $displayedAt->locale('th')->translatedFormat('j F') . ' ' . ($displayedAt->year + 543) . $displayedAt->translatedFormat(' H:i')
                                    : '-';
                            @endphp
                            <tr class="align-top hover:bg-indigo-50/40">
                                <td class="px-4 py-4 text-sm text-slate-900">
                                    @if (!empty($paper->output_code))
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">{{ $paper->output_code }}</div>
                                    @endif
                                    @if (!empty($paper->source_badge))
                                        <div class="mt-1">
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $sourceBadgeClass($paper->source_badge) }}">{{ $paper->source_badge }}</span>
                                        </div>
                                    @endif
                                    <div class="font-semibold leading-6">{{ $paper->title }}</div>
                                    @if ($paper->impact_factor)
                                        <div class="mt-1 text-xs text-slate-500">Impact Factor: {{ $paper->impact_factor }}</div>
                                    @endif
                                    @if (!empty($paper->detail))
                                        <div class="mt-2 text-xs leading-5 text-slate-600 break-words">
                                            รายละเอียด / ลิงก์:
                                            @if (filter_var($paper->detail, FILTER_VALIDATE_URL))
                                                <a href="{{ $paper->detail }}" target="_blank" rel="noopener"
                                                    class="ml-1 inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                                    ดูเอกสาร
                                                </a>
                                            @else
                                                {{ $paper->detail }}
                                            @endif
                                        </div>
                                    @endif
                                    <div class="mt-2 text-xs text-slate-500">
                                        วันที่ลงข้อมูล: {{ $displayedAtLabel }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-700">
                                    @if (request('author') && !empty($paper->matched_author_roles) && collect($paper->matched_author_roles)->isNotEmpty())
                                        <div class="mb-3 flex flex-wrap gap-2">
                                            @foreach (collect($paper->matched_author_roles) as $matchedRole)
                                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $matchedRoleBadgeClass((string) $matchedRole) }}">
                                                    {{ $matchedRole }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @forelse ($paper->authors_by_role as $role => $names)
                                        <div class="mb-2">
                                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                                {{ ['first' => 'First Author', 'co' => 'Co Author', 'corresponding' => 'Corresponding Author'][$role] ?? ucfirst($role) }}
                                            </div>
                                            <div class="mt-1 leading-6">{{ $names->implode(', ') }}</div>
                                        </div>
                                    @empty
                                        -
                                    @endforelse
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-700">{{ $paper->journal }}</td>
                                <td class="px-4 py-4 text-center text-sm font-semibold text-slate-700">{{ $paper->quartile }}</td>
                                <td class="px-4 py-4 text-center text-sm text-slate-700">{{ $paper->year_be ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-700">
                                    <div class="font-medium">{{ $paper->project_label }}</div>
                                    @if ($paper->project_code)
                                        <div class="mt-1 text-xs text-slate-500">{{ $paper->project_code }}</div>
                                    @endif
                                    @if ($paper->project_link)
                                        <a href="{{ $paper->project_link }}"
                                            class="mt-2 inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">
                                            {{ $paper->project_code ? 'เปิด Project Detail' : 'เปิดรายละเอียด' }}
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

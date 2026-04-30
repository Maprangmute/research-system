@extends('layouts.app')

@section('content')
    @php
        $categoryTone = [
            'duplicate' => 'border-rose-200 bg-rose-50 text-rose-700',
            'incomplete' => 'border-amber-200 bg-amber-50 text-amber-700',
            'overdue' => 'border-red-200 bg-red-50 text-red-700',
            'no_output' => 'border-sky-200 bg-sky-50 text-sky-700',
            'other' => 'border-slate-200 bg-slate-50 text-slate-700',
        ];
        $severityLabel = fn ($score) => match ((int) $score) {
            4 => 'สูง',
            3 => 'กลาง',
            2 => 'ควรตรวจ',
            default => 'ติดตาม',
        };
        $severityClass = fn ($score) => match ((int) $score) {
            4 => 'border-red-200 bg-red-50 text-red-700',
            3 => 'border-amber-200 bg-amber-50 text-amber-700',
            2 => 'border-sky-200 bg-sky-50 text-sky-700',
            default => 'border-slate-200 bg-slate-50 text-slate-600',
        };
        $editTargetUrl = function ($project, ?string $target = null, array $highlightTargets = []) {
            $params = ['project' => $project, 'view' => 'desktop'];
            $highlightTargets = collect($highlightTargets)
                ->filter()
                ->unique()
                ->values()
                ->all();
            if ($highlightTargets !== []) {
                $params['dq_fields'] = implode(',', $highlightTargets);
            }

            $url = route('projects.edit', $params);
            if (!$target) {
                return $url;
            }

            $hashPrefix = str_starts_with($target, 'section-') ? '' : 'field-';
            return $url . '#' . $hashPrefix . rawurlencode($target);
        };
    @endphp
    <style>
        .output-export-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.75rem;
            background: #059669;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
            transition: background-color 0.2s ease;
        }
        .output-export-btn:hover {
            background: #047857;
        }
    </style>

    <div class="mx-auto w-full max-w-[1500px] space-y-5 px-4 py-6 md:px-6 xl:px-8" data-project-data-quality-content>
        @if (session('success'))
            <div class="border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error') || $errors->any())
            <div class="border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                {{ session('error') ?: $errors->first() }}
            </div>
        @endif

        <section class="border border-amber-200 bg-gradient-to-br from-amber-50 via-white to-sky-50 p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-amber-700">Project Data Quality</div>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">รายงานข้อมูลผิดพลาดของโครงการ</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
                        ตรวจข้อมูลซ้ำ ข้อมูลไม่ครบ วันที่ไม่ครบ Overdue โครงการที่ไม่มี output และสัญญาณอื่นที่กระทบ dashboard, FTE และรายงานผลงาน
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <div class="border border-slate-200 bg-white px-4 py-3">
                        <div class="text-xs font-semibold text-slate-500">Projects</div>
                        <div class="mt-1 text-2xl font-bold text-slate-950">{{ number_format($summary['projects']) }}</div>
                    </div>
                    <div class="border border-slate-200 bg-white px-4 py-3">
                        <div class="text-xs font-semibold text-slate-500">Issues</div>
                        <div class="mt-1 text-2xl font-bold text-slate-950">{{ number_format($summary['issues']) }}</div>
                    </div>
                    <div class="border border-red-200 bg-red-50 px-4 py-3">
                        <div class="text-xs font-semibold text-red-600">Overdue</div>
                        <div class="mt-1 text-2xl font-bold text-red-700">{{ number_format($summary['by_category']['overdue']['projects'] ?? 0) }}</div>
                    </div>
                    <div class="border border-sky-200 bg-sky-50 px-4 py-3">
                        <div class="text-xs font-semibold text-sky-600">No Output</div>
                        <div class="mt-1 text-2xl font-bold text-sky-700">{{ number_format($summary['by_category']['no_output']['projects'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="border border-slate-200 bg-white p-4 shadow-sm">
            <form id="project-data-quality-filter-form" method="GET" action="{{ route('outputs.project-data-quality') }}" class="space-y-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">ตัวกรองข้อมูลผิดพลาด</h2>
                        <p class="mt-1 text-sm text-slate-500">กรองตามหมวด ชนิดปัญหา FTE/Output/IRB ความรุนแรง และความเคลื่อนไหวล่าสุดของ record</p>
                    </div>
                    @include('outputs.partials.export-dialog', [
                        'id' => 'projectDataQualityExportModal',
                        'pdfUrl' => route('outputs.project-data-quality.pdf'),
                        'excelUrl' => route('outputs.project-data-quality.excel'),
                        'formSelector' => '#project-data-quality-filter-form',
                        'minDate' => $exportMinDate ?? null,
                    ])
                </div>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <label class="block md:col-span-2 xl:col-span-2">
                        <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                        <input type="text" name="search" value="{{ $search }}"
                            class="mt-1 w-full border border-slate-200 px-3 py-2 text-sm"
                            placeholder="Project ID, ชื่อโครงการ, PI, field หรือเหตุผล">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">หมวดปัญหา</span>
                        <select name="category" class="mt-1 w-full border border-slate-200 px-3 py-2 text-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach ($categoryOptions as $key => $label)
                                <option value="{{ $key }}" @selected($selectedCategory === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ชนิดปัญหา</span>
                        <select name="issue_label" class="mt-1 w-full border border-slate-200 px-3 py-2 text-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach (($issueLabelOptions ?? collect()) as $label)
                                <option value="{{ $label }}" @selected(($selectedIssueLabel ?? '') === $label)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ระดับ</span>
                        <select name="severity" class="mt-1 w-full border border-slate-200 px-3 py-2 text-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="4" @selected($selectedSeverity === '4')>สูง</option>
                            <option value="3" @selected($selectedSeverity === '3')>กลาง</option>
                            <option value="2" @selected($selectedSeverity === '2')>ควรตรวจ</option>
                            <option value="1" @selected($selectedSeverity === '1')>ติดตาม</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ผลงานวิจัย (Output)</span>
                        <select name="output_status" class="mt-1 w-full border border-slate-200 px-3 py-2 text-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="none" @selected($selectedOutputStatus === 'none')>ไม่มี output</option>
                            <option value="has" @selected($selectedOutputStatus === 'has')>มี output</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ความเคลื่อนไหว</span>
                        <select name="activity" class="mt-1 w-full border border-slate-200 px-3 py-2 text-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="updated_30" @selected($selectedActivity === 'updated_30')>อัปเดทใน 30 วัน</option>
                            <option value="updated_90" @selected($selectedActivity === 'updated_90')>อัปเดทใน 90 วัน</option>
                            <option value="stale_90" @selected($selectedActivity === 'stale_90')>ไม่อัปเดทเกิน 90 วัน</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                        <select name="sort" class="mt-1 w-full border border-slate-200 px-3 py-2 text-sm">
                            <option value="severity" @selected($sort === 'severity')>ความรุนแรง</option>
                            <option value="issues" @selected($sort === 'issues')>จำนวนปัญหา</option>
                            <option value="project_code" @selected($sort === 'project_code')>Project ID</option>
                            <option value="outputs" @selected($sort === 'outputs')>จำนวน output</option>
                            <option value="updated_at" @selected($sort === 'updated_at')>อัปเดทล่าสุด</option>
                        </select>
                    </label>
                </div>

                <div class="flex flex-col gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                    <button class="border border-slate-900 bg-slate-900 px-4 py-2 text-sm font-semibold text-white">ค้นหา</button>
                    <a href="{{ route('outputs.project-data-quality') }}" class="border border-slate-200 bg-white px-4 py-2 text-center text-sm font-semibold text-slate-700">ล้างฟิลเตอร์</a>
                </div>
            </form>
        </section>

        <section class="grid gap-3 md:grid-cols-5">
            @foreach ($summary['by_category'] as $key => $item)
                <a href="{{ route('outputs.project-data-quality', ['category' => $key]) }}"
                    class="border px-4 py-3 shadow-sm {{ $categoryTone[$key] ?? $categoryTone['other'] }}">
                    <div class="text-xs font-bold">{{ $item['label'] }}</div>
                    <div class="mt-2 text-2xl font-extrabold">{{ number_format($item['projects']) }}</div>
                    <div class="text-xs opacity-80">{{ number_format($item['issues']) }} issues</div>
                </a>
            @endforeach
        </section>

        <section class="border border-slate-200 bg-white shadow-sm" data-quality-list>
            <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
                <h2 class="text-lg font-bold text-slate-950">รายการโครงการที่ควรตรวจ</h2>
                <p class="mt-1 text-sm text-slate-500">แสดง {{ number_format($rows->count()) }} จาก {{ number_format($totalRows ?? $summary['projects']) }} โครงการที่ผ่านฟิลเตอร์</p>
            </div>
            <div class="space-y-5 bg-slate-100/70 p-4 md:p-5">
                @forelse ($rows as $row)
                    <article class="border border-slate-300 bg-white shadow-sm" data-quality-card-key="{{ $row->project->getKey() }}">
                        <div class="border-l-4 {{ $row->severity_score >= 4 ? 'border-l-red-500' : ($row->severity_score >= 3 ? 'border-l-amber-500' : 'border-l-sky-500') }}">
                            <header class="border-b border-slate-200 bg-white px-5 py-4">
                                <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex border border-slate-300 bg-slate-900 px-2.5 py-1 font-mono text-xs font-bold text-white">
                                                {{ $loop->iteration }}
                                            </span>
                                            <a href="{{ route('projects.show', $row->project) }}"
                                                class="font-mono text-sm font-extrabold tracking-wide text-sky-700 hover:underline">
                                                {{ $row->project_code }}
                                            </a>
                                            <span class="inline-flex border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                {{ $row->planning_status }}
                                            </span>
                                            <span class="inline-flex border px-2.5 py-1 text-xs font-semibold {{ $severityClass($row->severity_score) }}">
                                                {{ $severityLabel($row->severity_score) }}
                                            </span>
                                        </div>
                                        <h3 class="mt-3 max-w-5xl text-lg font-bold leading-snug text-slate-950">
                                            {{ $row->project_name }}
                                        </h3>
                                    </div>

                                    <div class="shrink-0 border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                                        <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Issues</div>
                                        <div class="mt-1 text-xl font-extrabold text-slate-950">{{ count($row->issues) }}</div>
                                        <div class="text-xs text-slate-500">จุดที่ควรตรวจ</div>
                                    </div>
                                </div>
                            </header>

                            @php
                                $rowQuickFixNames = collect($row->issues)
                                    ->flatMap(fn ($issue) => $issue['quick_fix_fields'] ?? [])
                                    ->filter(fn ($name) => isset($quickFixFields[$name]))
                                    ->unique()
                                    ->values();
                            @endphp
                            <div class="grid gap-0 lg:grid-cols-[minmax(320px,0.85fr)_minmax(0,1.65fr)]">
                                <aside class="border-b border-slate-200 bg-slate-50 p-5 lg:border-b-0 lg:border-r">
                                    <dl class="grid gap-3 text-sm">
                                        <div class="border border-slate-200 bg-white px-3 py-2">
                                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">PI</dt>
                                            <dd class="mt-1 font-semibold text-slate-800">{{ $row->pi_name }}</dd>
                                        </div>
                                        <div class="border border-slate-200 bg-white px-3 py-2">
                                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Output</dt>
                                            <dd class="mt-1">
                                                @if ($row->outputs_count > 0)
                                                    <span class="font-semibold text-slate-900">{{ number_format($row->outputs_count) }} รายการ</span>
                                                @else
                                                    <span class="inline-flex border border-sky-200 bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-700">ไม่มี output</span>
                                                @endif
                                            </dd>
                                        </div>
                                        <div class="border border-slate-200 bg-white px-3 py-2">
                                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">กลุ่มเนื้อหาหลักของงาน / ประเภทของโครงการ</dt>
                                            <dd class="mt-1 font-medium leading-relaxed text-slate-700">{{ $row->project_type }}</dd>
                                        </div>
                                        <div class="border border-slate-200 bg-white px-3 py-2">
                                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">สร้าง record</dt>
                                            <dd class="mt-1 font-semibold text-slate-800">{{ $row->created_by_name }}</dd>
                                            <dd class="mt-0.5 text-xs text-slate-500">{{ $row->created_at_label }}</dd>
                                        </div>
                                        <div class="border border-slate-200 bg-white px-3 py-2">
                                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">อัปเดทล่าสุด</dt>
                                            <dd class="mt-1 font-semibold text-slate-800">{{ $row->updated_by_name }}</dd>
                                            <dd class="mt-0.5 text-xs text-slate-500">
                                                {{ $row->updated_at_label }}
                                                @if (!empty($row->updated_action_label))
                                                    · {{ $row->updated_action_label }}
                                                @endif
                                            </dd>
                                        </div>
                                    </dl>
                                    <a href="{{ route('projects.show', $row->project) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="mt-4 inline-flex w-full justify-center border border-slate-900 bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700">
                                        เปิดโครงการนี้
                                    </a>
                                    @php
                                        $rowHighlightTargets = collect($row->issues)
                                            ->flatMap(fn ($issue) => collect($issue['field_targets'] ?? [])->values())
                                            ->filter()
                                            ->unique()
                                            ->values()
                                            ->all();
                                    @endphp
                                    <a href="{{ $editTargetUrl($row->project, null, $rowHighlightTargets) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="mt-2 inline-flex w-full justify-center border border-emerald-700 bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">
                                        แก้ไขโครงการนี้
                                    </a>
                                </aside>

                                <div class="bg-white p-5">
                                    @php
                                        $rowDuplicateIssue = collect($row->issues)->firstWhere('category', 'duplicate');
                                        $rowHasDuplicateIssue = (bool) $rowDuplicateIssue;
                                        $rowDuplicateMatches = $rowDuplicateIssue['duplicate_matches'] ?? [];
                                    @endphp
                                    <div class="mb-4 flex items-center gap-3 border-b border-slate-200 pb-3">
                                        <div class="h-8 w-1 bg-slate-900"></div>
                                        <div>
                                            <div class="text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">ตรวจพบปัญหา</div>
                                            <div class="text-sm font-semibold text-slate-900">รายการนี้เป็นของโครงการ {{ $row->project_code }}</div>
                                        </div>
                                    </div>

                                    <div class="grid gap-3 xl:grid-cols-2">
                                @foreach ($row->issues as $issue)
                                        @php
                                            $issueQuickFixNames = collect($issue['quick_fix_fields'] ?? [])
                                                ->filter(fn ($name) => isset($quickFixFields[$name]))
                                                ->unique()
                                                ->values();
                                            $issueSectionTarget = collect($issue['field_targets'] ?? [])
                                                ->first(fn ($target) => is_string($target) && str_starts_with($target, 'section-'));
                                            $issueModalId = 'qualityIssueModal-'.$row->project->getKey().'-'.$loop->iteration;
                                            $isDuplicateIssue = $issue['category'] === 'duplicate';
                                            $isDuplicateLocked = $rowHasDuplicateIssue;
                                            $canOpenIssueModal = $issueQuickFixNames->isNotEmpty() || $issueSectionTarget || $isDuplicateLocked;
                                        @endphp
                                        <div class="border border-slate-200 bg-slate-50 p-4 {{ $canOpenIssueModal ? 'cursor-pointer hover:border-amber-300 hover:bg-amber-50/40' : '' }}"
                                            @if ($canOpenIssueModal) data-issue-modal-trigger="{{ $issueModalId }}" role="button" tabindex="0" @endif>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex border border-slate-300 bg-white px-2 py-1 font-mono text-xs font-bold text-slate-700">
                                                    {{ $loop->iteration }}
                                                </span>
                                                <span class="inline-flex border px-2 py-1 text-xs font-bold {{ $categoryTone[$issue['category']] ?? $categoryTone['other'] }}">
                                                    {{ $issue['label'] }}
                                                </span>
                                            </div>
                                            @if (!empty($issue['fields']))
                                                <div class="mt-2 text-xs font-medium leading-relaxed text-slate-500">
                                                    <span class="font-bold text-slate-600">Field ที่ต้องแก้:</span>
                                                    @foreach ($issue['fields'] as $fieldIndex => $field)
                                                        @php
                                                            $fieldTarget = $issue['field_targets'][$field] ?? null;
                                                            $issueHighlightTargets = collect($issue['field_targets'] ?? [])->values()->filter()->all();
                                                        @endphp
                                                        @if ($fieldIndex > 0)
                                                            <span class="text-slate-400">,</span>
                                                        @endif
                                                        @if ($fieldTarget && ! $rowHasDuplicateIssue)
                                                            <a href="{{ $editTargetUrl($row->project, $fieldTarget, $issueHighlightTargets) }}"
                                                                class="font-semibold text-sky-700 underline-offset-2 hover:underline"
                                                                title="เปิดหน้าแก้ไขและเลื่อนไปที่ {{ $field }}">
                                                                {{ $field }}
                                                            </a>
                                                        @else
                                                            <span class="{{ $rowHasDuplicateIssue ? 'font-semibold text-slate-700' : 'text-slate-600' }}">{{ $field }}</span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div class="mt-3 text-sm font-bold leading-relaxed text-slate-900">
                                                {{ $issue['reason'] }}
                                            </div>
                                            @if (!empty($issue['duplicate_matches']))
                                                <div class="mt-3 space-y-1.5">
                                                    @foreach ($issue['duplicate_matches'] as $match)
                                                        <a href="{{ route('projects.show', $match['id']) }}"
                                                            target="_blank"
                                                            rel="noopener"
                                                            class="block border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 hover:border-rose-300 hover:bg-rose-50">
                                                            {{ $match['project_code'] ?: 'Project ID ไม่ระบุ' }}
                                                            <span class="text-slate-400">|</span>
                                                            {{ $match['project_name'] ?: 'ชื่อโครงการไม่ระบุ' }}
                                                            @if (!empty($match['start_year']))
                                                                <span class="text-slate-400">|</span>
                                                                ปี {{ $match['start_year'] }}
                                                            @endif
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div class="mt-3 border-l-2 border-slate-300 bg-white px-3 py-2 text-xs leading-relaxed text-slate-600">
                                                <span class="font-bold text-slate-700">ควรทำ:</span> {{ $issue['suggestion'] }}
                                            </div>
                                            @if (!empty($issue['impact']))
                                                <div class="mt-2 border-l-2 border-amber-300 bg-amber-50 px-3 py-2 text-xs leading-relaxed text-amber-900">
                                                    <span class="font-bold">ผลกระทบถ้าไม่แก้:</span> {{ $issue['impact'] }}
                                                </div>
                                            @endif
                                        </div>
                                        @if ($canOpenIssueModal)
                                            <div id="{{ $issueModalId }}" class="fixed inset-0 z-[80] hidden" data-issue-modal>
                                                <div class="absolute inset-0 bg-slate-900/45" data-issue-modal-close></div>
                                                <div class="absolute inset-x-4 top-8 mx-auto max-h-[calc(100vh-4rem)] max-w-5xl overflow-y-auto border border-slate-300 bg-white shadow-2xl md:top-12">
                                                    <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4">
                                                        <div>
                                                            <div class="text-xs font-extrabold uppercase tracking-[0.14em] text-slate-500">{{ $issue['label'] }}</div>
                                                            <div class="mt-1 text-base font-bold leading-snug text-slate-950">{{ $issue['reason'] }}</div>
                                                            <div class="mt-2 text-xs font-semibold leading-relaxed text-slate-600">
                                                                {{ $row->project_code }} | {{ $row->project_name }}
                                                            </div>
                                                            @if (!empty($issue['impact']))
                                                                <div class="mt-3 border-l-2 border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold leading-relaxed text-amber-900">
                                                                    <span class="font-bold">ผลกระทบถ้าไม่แก้:</span> {{ $issue['impact'] }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <button type="button" class="border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50" data-issue-modal-close>
                                                            ปิด
                                                        </button>
                                                    </div>
                                                    @if ($isDuplicateLocked)
                                                        <div class="px-5 py-5">
                                                            <div class="border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold leading-relaxed text-rose-800">
                                                                ข้อมูลซ้ำกับ
                                                                @if (!empty($rowDuplicateMatches))
                                                                    {{ collect($rowDuplicateMatches)->pluck('project_code')->filter()->implode(', ') }}
                                                                @else
                                                                    โครงการอื่นในระบบ
                                                                @endif
                                                                โปรดพิจารณาข้อมูลซ้ำก่อน
                                                            </div>
                                                            @if (!empty($rowDuplicateMatches))
                                                                <div class="mt-4 space-y-2">
                                                                    @foreach ($rowDuplicateMatches as $match)
                                                                        <a href="{{ route('projects.show', $match['id']) }}"
                                                                            target="_blank"
                                                                            rel="noopener"
                                                                            class="block border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-700 hover:border-rose-300 hover:bg-rose-50">
                                                                            {{ $match['project_code'] ?: 'Project ID ไม่ระบุ' }}
                                                                            <span class="text-slate-400">|</span>
                                                                            {{ $match['project_name'] ?: 'ชื่อโครงการไม่ระบุ' }}
                                                                            @if (!empty($match['start_year']))
                                                                                <span class="text-slate-400">|</span>
                                                                                ปี {{ $match['start_year'] }}
                                                                            @endif
                                                                        </a>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @elseif ($issueQuickFixNames->isNotEmpty())
                                                        <form method="POST" action="{{ route('outputs.project-data-quality.quick-fix', $row->project) }}"
                                                            class="px-5 py-5"
                                                            data-quick-fix-form>
                                                            @csrf
                                                            @method('PATCH')
                                                            <div class="grid gap-3 md:grid-cols-2">
                                                                @foreach ($issueQuickFixNames as $fieldName)
                                                                    @php
                                                                        $meta = $quickFixFields[$fieldName];
                                                                        $value = old($fieldName, data_get($row->project, $fieldName));
                                                                        $inputType = $meta['type'] ?? 'text';
                                                                        $isMoneyInput = $fieldName === 'budget';
                                                                        $displayValue = $value;
                                                                        if ($isMoneyInput && $value !== null && $value !== '') {
                                                                            $normalizedMoney = str_replace(',', '', (string) $value);
                                                                            $displayValue = is_numeric($normalizedMoney) ? number_format((float) $normalizedMoney, 0) : $value;
                                                                        }
                                                                    @endphp
                                                                    <label class="block">
                                                                        <span class="flex min-h-[2.5rem] items-end text-xs font-bold leading-snug text-slate-600">{{ $meta['label'] }}</span>
                                                                        @if ($inputType === 'select')
                                                                            <select name="{{ $fieldName }}" class="mt-1 w-full border border-slate-300 bg-white px-3 py-2 text-sm">
                                                                                <option value="">- ไม่ระบุ -</option>
                                                                                @foreach (($meta['options'] ?? []) as $option)
                                                                                    <option value="{{ $option }}" @selected((string) $value === (string) $option)>{{ $option }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        @elseif ($inputType === 'year')
                                                                            <select name="{{ $fieldName }}" class="mt-1 w-full border border-slate-300 bg-white px-3 py-2 text-sm">
                                                                                <option value="">- ไม่ระบุ -</option>
                                                                                @for ($year = now()->year + 2 + 543; $year >= 2557; $year--)
                                                                                    <option value="{{ $year }}" @selected((string) $value === (string) $year)>{{ $year }}</option>
                                                                                @endfor
                                                                            </select>
                                                                        @elseif ($inputType === 'user')
                                                                            <select name="{{ $fieldName }}" class="mt-1 w-full border border-slate-300 bg-white px-3 py-2 text-sm">
                                                                                <option value="">- ไม่ระบุ -</option>
                                                                                @foreach (($quickFixUsers ?? collect()) as $userOption)
                                                                                    <option value="{{ $userOption['id'] }}" @selected((string) data_get($row->project, 'pi_user_id') === (string) $userOption['id'])>{{ $userOption['name'] }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        @else
                                                                            <input name="{{ $fieldName }}"
                                                                                type="{{ $isMoneyInput ? 'text' : ($inputType === 'number' ? 'number' : 'text') }}"
                                                                                step="{{ $inputType === 'number' && ! $isMoneyInput ? '0.01' : null }}"
                                                                                inputmode="{{ $isMoneyInput ? 'decimal' : null }}"
                                                                                value="{{ $displayValue }}"
                                                                                placeholder="{{ $inputType === 'date' ? 'เลือกวันที่ (พ.ศ.)' : '' }}"
                                                                                class="mt-1 w-full border border-slate-300 bg-white px-3 py-2 text-sm {{ $inputType === 'date' ? 'thai-datepicker' : '' }}"
                                                                                @if ($isMoneyInput) data-money-input @endif>
                                                                        @endif
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                            <div class="mt-5 flex flex-col items-center justify-center gap-2">
                                                                <button class="border border-emerald-700 bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700" data-quick-fix-submit>
                                                                    บันทึกการแก้ไข
                                                                </button>
                                                                <span class="text-xs font-semibold text-slate-500" data-quick-fix-status></span>
                                                            </div>
                                                        </form>
                                                    @elseif ($issueSectionTarget)
                                                        <div class="px-5 py-5">
                                                            <div class="border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold leading-relaxed text-sky-800">
                                                                รายการนี้ต้องเพิ่มหรือแก้ไขข้อมูลใน section ของฟอร์มเต็ม
                                                            </div>
                                                            <div class="mt-5 flex justify-center">
                                                                <a href="{{ $editTargetUrl($row->project, $issueSectionTarget) }}"
                                                                    target="_blank"
                                                                    rel="noopener"
                                                                    class="border border-slate-900 bg-slate-900 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-slate-700">
                                                                    ไปแก้ไข / เพิ่มข้อมูล
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="border border-slate-200 bg-white px-4 py-12 text-center text-sm text-slate-500">ไม่พบข้อมูลผิดพลาดตามเงื่อนไขที่เลือก</div>
                @endforelse
            </div>
            @if (method_exists($rows, 'links') && $rows->hasPages())
                <div class="border-t border-slate-200 bg-white px-5 py-4">
                    {{ $rows->withQueryString()->links() }}
                </div>
            @endif
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const initThaiDatePickers = (scope = document) => {
                const formatDateBE = (date) => {
                    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
                        return '';
                    }

                    const day = date.getDate();
                    const month = date.toLocaleDateString('th-TH', { month: 'long' });
                    const year = date.getFullYear() + 543;
                    return `${day} ${month} ${year}`;
                };

                const forceAltInputBE = (input) => {
                    const picker = input._flatpickr;
                    if (!picker || !picker.altInput) {
                        return;
                    }

                    let picked = picker.selectedDates?.[0];
                    if (!(picked instanceof Date) || Number.isNaN(picked.getTime())) {
                        picked = typeof parseDateInputValue === 'function'
                            ? parseDateInputValue(picker.altInput.value || input.value || '')
                            : null;
                    }

                    const formatted = formatDateBE(picked);
                    if (formatted) {
                        picker.altInput.value = formatted;
                    }
                };

                if (typeof initDateBE === 'function') {
                    initDateBE(scope);
                }
                scope.querySelectorAll('.thai-datepicker').forEach((input) => {
                    const picker = input._flatpickr;
                    forceAltInputBE(input);
                    setTimeout(() => forceAltInputBE(input), 0);
                    setTimeout(() => forceAltInputBE(input), 50);

                    if (!picker || picker._projectQualityBeBound || typeof forceHeaderBE !== 'function') {
                        return;
                    }

                    picker._projectQualityBeBound = true;
                    const syncBE = (_selectedDates, _dateStr, instance) => {
                        forceHeaderBE(instance);
                        forceAltInputBE(input);
                        setTimeout(() => forceHeaderBE(instance), 0);
                        setTimeout(() => forceAltInputBE(input), 0);
                        setTimeout(() => forceHeaderBE(instance), 50);
                        setTimeout(() => forceAltInputBE(input), 50);
                    };

                    if (typeof appendFlatpickrHook === 'function') {
                        appendFlatpickrHook(picker, 'onOpen', syncBE);
                        appendFlatpickrHook(picker, 'onReady', syncBE);
                        appendFlatpickrHook(picker, 'onMonthChange', syncBE);
                        appendFlatpickrHook(picker, 'onYearChange', syncBE);
                    }
                });
            };

            const formatMoneyValue = (value) => {
                const cleaned = String(value || '')
                    .replace(/,/g, '')
                    .replace(/[^\d.]/g, '');

                if (cleaned === '') {
                    return '';
                }

                const parts = cleaned.split('.');
                const integer = parts.shift() || '0';
                const decimals = parts.join('').slice(0, 2);
                const formattedInteger = integer.replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                return decimals ? `${formattedInteger}.${decimals}` : formattedInteger;
            };

            const bindMoneyInputs = (root = document) => {
                root.querySelectorAll('[data-money-input]').forEach((input) => {
                    if (input.dataset.moneyBound === '1') {
                        return;
                    }

                    input.dataset.moneyBound = '1';
                    input.value = formatMoneyValue(input.value);
                    input.addEventListener('input', () => {
                        input.value = formatMoneyValue(input.value);
                    });
                    input.addEventListener('blur', () => {
                        input.value = formatMoneyValue(input.value);
                    });
                });
            };

            const rememberQuickFixInitialValues = (form) => {
                form.querySelectorAll('input, select, textarea').forEach((field) => {
                    if (field.dataset.initializedValue === '1') {
                        return;
                    }

                    field.dataset.initializedValue = '1';
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        field.dataset.initialChecked = field.checked ? '1' : '0';
                    } else {
                        field.dataset.initialValue = field.value || '';
                    }

                    if (field._flatpickr?.altInput) {
                        field.dataset.initialAltValue = field._flatpickr.altInput.value || '';
                    }
                });
            };

            const restoreQuickFixForm = (form) => {
                form.querySelectorAll('input, select, textarea').forEach((field) => {
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        field.checked = field.dataset.initialChecked === '1';
                        return;
                    }

                    const initialValue = field.dataset.initialValue || '';
                    const picker = field._flatpickr;
                    if (picker) {
                        if (initialValue) {
                            picker.setDate(initialValue, false);
                            field.value = initialValue;
                        } else {
                            picker.clear();
                            field.value = '';
                        }

                        if (picker.altInput) {
                            picker.altInput.value = field.dataset.initialAltValue || '';
                        }
                        return;
                    }

                    field.value = field.hasAttribute('data-money-input')
                        ? formatMoneyValue(initialValue)
                        : initialValue;
                });

                const status = form.querySelector('[data-quick-fix-status]');
                if (status) {
                    status.textContent = '';
                    status.className = 'text-xs font-semibold text-slate-500';
                }
            };

            const bindQuickFixForms = (root = document) => {
                bindMoneyInputs(root);

                root.querySelectorAll('[data-quick-fix-form]').forEach((form) => {
                    if (form.dataset.bound === '1') {
                        return;
                    }

                    form.dataset.bound = '1';
                    rememberQuickFixInitialValues(form);
                    form.addEventListener('submit', async (event) => {
                        event.preventDefault();

                        const submit = form.querySelector('[data-quick-fix-submit]');
                        const status = form.querySelector('[data-quick-fix-status]');
                        const originalText = submit ? submit.textContent : '';

                        if (submit) {
                            submit.disabled = true;
                            submit.textContent = 'กำลังบันทึก...';
                        }
                        if (status) {
                            status.textContent = '';
                            status.className = 'text-xs font-semibold text-slate-500';
                        }

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: new FormData(form),
                            });
                            const payload = await response.json().catch(() => ({}));

                            if (!response.ok) {
                                const firstError = payload.errors
                                    ? Object.values(payload.errors).flat().find(Boolean)
                                    : null;
                                throw new Error(firstError || payload.message || 'บันทึกไม่สำเร็จ');
                            }

                            if (status) {
                                status.textContent = payload.message || 'บันทึกแล้ว';
                                status.className = 'text-xs font-semibold text-emerald-700';
                            }

                            const card = form.closest('[data-quality-card-key]');
                            const cardKey = card?.dataset.qualityCardKey || payload.project_id || '';
                            if (card) {
                                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }

                            if (status) {
                                status.textContent = 'บันทึกแล้ว กำลังอัปเดตรายการ...';
                            }

                            const controller = new AbortController();
                            const timeout = window.setTimeout(() => controller.abort(), 15000);
                            const refreshUrl = new URL(window.location.href);
                            if (cardKey) {
                                refreshUrl.searchParams.set('focus_project', cardKey);
                            }
                            const listResponse = await fetch(refreshUrl.toString(), {
                                cache: 'no-store',
                                signal: controller.signal,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });
                            window.clearTimeout(timeout);

                            if (!listResponse.ok) {
                                throw new Error('บันทึกแล้ว แต่อัปเดตรายการไม่สำเร็จ');
                            }

                            const html = await listResponse.text();
                            const doc = new DOMParser().parseFromString(html, 'text/html');
                            const freshContent = doc.querySelector('[data-project-data-quality-content]');
                            const currentContent = document.querySelector('[data-project-data-quality-content]');

                            if (!freshContent || !currentContent) {
                                throw new Error('บันทึกแล้ว แต่อัปเดตรายการไม่สำเร็จ');
                            }

                            currentContent.replaceWith(freshContent);
                            document.body.classList.remove('overflow-hidden');
                            initThaiDatePickers(freshContent);
                            bindQuickFixForms(freshContent);
                            bindIssueModals(freshContent);

                            const cssEscape = window.CSS?.escape || ((value) => String(value).replace(/["\\]/g, '\\$&'));
                            const savedCard = cardKey
                                ? freshContent.querySelector(`[data-quality-card-key="${cssEscape(cardKey)}"]`)
                                : null;
                            const scrollTarget = savedCard || freshContent.querySelector('[data-quality-list]');
                            if (scrollTarget) {
                                window.setTimeout(() => {
                                    scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    if (savedCard) {
                                        savedCard.classList.add('project-data-quality-card-saved');
                                        window.setTimeout(() => {
                                            savedCard.classList.remove('project-data-quality-card-saved');
                                        }, 2400);
                                    }
                                }, 100);
                            }
                        } catch (error) {
                            if (status) {
                                status.textContent = error.name === 'AbortError'
                                    ? 'บันทึกแล้ว แต่อัปเดตรายการนานเกินไป'
                                    : (error.message || 'บันทึกไม่สำเร็จ');
                                status.className = 'text-xs font-semibold text-red-700';
                            }
                        } finally {
                            if (submit) {
                                submit.disabled = false;
                                submit.textContent = originalText;
                            }
                        }
                    });
                });
            };

            const openIssueModal = (modal) => {
                if (!modal) {
                    return;
                }

                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                initThaiDatePickers(modal);
                bindQuickFixForms(modal);
                modal.querySelector('input, select, textarea, button')?.focus();
            };

            const closeIssueModal = (modal) => {
                if (!modal) {
                    return;
                }

                modal.querySelectorAll('[data-quick-fix-form]').forEach((form) => restoreQuickFixForm(form));
                modal.classList.add('hidden');
                if (!document.querySelector('[data-issue-modal]:not(.hidden)')) {
                    document.body.classList.remove('overflow-hidden');
                }
            };

            const bindIssueModals = (root = document) => {
                root.querySelectorAll('[data-issue-modal-trigger]').forEach((trigger) => {
                    if (trigger.dataset.modalBound === '1') {
                        return;
                    }

                    trigger.dataset.modalBound = '1';
                    const openFromTrigger = (event) => {
                        if (event.target.closest('a, button, input, select, textarea, label')) {
                            return;
                        }

                        const modal = document.getElementById(trigger.dataset.issueModalTrigger || '');
                        openIssueModal(modal);
                    };

                    trigger.addEventListener('click', openFromTrigger);
                    trigger.addEventListener('keydown', (event) => {
                        if (!['Enter', ' '].includes(event.key)) {
                            return;
                        }
                        event.preventDefault();
                        const modal = document.getElementById(trigger.dataset.issueModalTrigger || '');
                        openIssueModal(modal);
                    });
                });

                root.querySelectorAll('[data-issue-modal-close]').forEach((control) => {
                    if (control.dataset.modalCloseBound === '1') {
                        return;
                    }

                    control.dataset.modalCloseBound = '1';
                    control.addEventListener('click', () => closeIssueModal(control.closest('[data-issue-modal]')));
                });
            };

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') {
                    return;
                }

                document.querySelectorAll('[data-issue-modal]:not(.hidden)').forEach((modal) => closeIssueModal(modal));
            });

            initThaiDatePickers();
            bindQuickFixForms();
            bindIssueModals();
        });
    </script>
    <style>
        .project-data-quality-card-saved {
            outline: 3px solid rgba(5, 150, 105, 0.6);
            outline-offset: 4px;
            transition: outline-color 0.2s ease;
        }
    </style>
@endsection

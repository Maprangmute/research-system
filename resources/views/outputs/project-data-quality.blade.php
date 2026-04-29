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

    <div class="mx-auto w-full max-w-[1500px] space-y-5 px-4 py-6 md:px-6 xl:px-8">
        <section class="border border-amber-200 bg-gradient-to-br from-amber-50 via-white to-sky-50 p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-amber-700">Project Data Quality</div>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">รายงานข้อมูลผิดพลาดของโปรเจค</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
                        ตรวจข้อมูลซ้ำ ข้อมูลไม่ครบ วันที่ไม่ครบ Overdue โปรเจคที่ไม่มี output และสัญญาณอื่นที่กระทบ dashboard, FTE และรายงานผลงาน
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
                        <p class="mt-1 text-sm text-slate-500">กรองตามชนิดปัญหา ความรุนแรง output และความเคลื่อนไหวล่าสุดของ record</p>
                    </div>
                    @include('outputs.partials.export-dialog', [
                        'id' => 'projectDataQualityExportModal',
                        'pdfUrl' => route('outputs.project-data-quality.pdf'),
                        'excelUrl' => route('outputs.project-data-quality.excel'),
                        'formSelector' => '#project-data-quality-filter-form',
                        'minDate' => $exportMinDate ?? null,
                    ])
                </div>

                <div class="grid gap-3 lg:grid-cols-[minmax(260px,1.5fr)_repeat(5,minmax(150px,1fr))]">
                    <label class="block">
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
                        <span class="text-xs font-semibold text-slate-600">Output</span>
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

        <section class="border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
                <h2 class="text-lg font-bold text-slate-950">รายการโปรเจคที่ควรตรวจ</h2>
                <p class="mt-1 text-sm text-slate-500">แสดง {{ number_format($rows->count()) }} จาก {{ number_format($totalRows ?? $summary['projects']) }} โปรเจคที่ผ่านฟิลเตอร์</p>
            </div>
            <div class="space-y-5 bg-slate-100/70 p-4 md:p-5">
                @forelse ($rows as $row)
                    <article class="border border-slate-300 bg-white shadow-sm">
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
                                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Theme / ประเภท</dt>
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
                                </aside>

                                <div class="bg-white p-5">
                                    <div class="mb-4 flex items-center gap-3 border-b border-slate-200 pb-3">
                                        <div class="h-8 w-1 bg-slate-900"></div>
                                        <div>
                                            <div class="text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">ตรวจพบปัญหา</div>
                                            <div class="text-sm font-semibold text-slate-900">รายการนี้เป็นของโปรเจค {{ $row->project_code }}</div>
                                        </div>
                                    </div>

                                    <div class="grid gap-3 xl:grid-cols-2">
                                @foreach ($row->issues as $issue)
                                        <div class="border border-slate-200 bg-slate-50 p-4">
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
                                                    <span class="font-bold text-slate-600">Field:</span> {{ implode(', ', $issue['fields']) }}
                                                </div>
                                            @endif
                                            <div class="mt-3 text-sm font-bold leading-relaxed text-slate-900">
                                                {{ $issue['reason'] }}
                                            </div>
                                            <div class="mt-3 border-l-2 border-slate-300 bg-white px-3 py-2 text-xs leading-relaxed text-slate-600">
                                                <span class="font-bold text-slate-700">ควรทำ:</span> {{ $issue['suggestion'] }}
                                            </div>
                                        </div>
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
@endsection

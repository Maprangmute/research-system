@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'error',
                    title: @json(session('error')),
                    showConfirmButton: false,
                    timer: 7000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif

    @php
        $currentSort = request('sort', 'created_at');
        $currentDir = request('dir', 'desc');
        $toggleDir = fn($field) => $currentSort === $field && $currentDir === 'asc' ? 'desc' : 'asc';
        $sortUrl = fn($field) => request()->fullUrlWithQuery(['sort' => $field, 'dir' => $toggleDir($field), 'page' => 1]);
        $sortIcon = function ($field) use ($currentSort, $currentDir) {
            if ($currentSort !== $field) {
                return '↕';
            }
            return $currentDir === 'asc' ? '↑' : '↓';
        };

        $activeFilterCount = collect(request()->query())
            ->reject(fn($value, $key) => in_array($key, ['page', 'sort', 'dir'], true))
            ->filter(fn($value) => filled($value))
            ->count();

        $results = $outputs->getCollection();
        $typeTotal = $results->pluck('type')->filter()->unique()->count();
        $linkedTotal = $results->pluck('project_label')->filter()->unique()->count();
        $sortLabel = [
            'created_at' => 'บันทึกล่าสุด',
            'year' => 'ปี',
            'type' => 'ประเภท',
            'title' => 'ชื่อผลงาน',
        ][$currentSort] ?? 'บันทึกล่าสุด';
        $currentWorkStatus = request('work_status', '');

        $typeUniverse = collect($typeOptions)
            ->filter(fn($type) => trim((string) $type) !== '')
            ->map(fn($type) => trim((string) $type))
            ->unique()
            ->sort(fn($a, $b) => strcasecmp($a, $b))
            ->values();
        $typePalette = [
            '#7aa2f7', '#7dcfb6', '#f7b267', '#f79d9d', '#b39ddb', '#7ec8e3', '#9ad29a', '#9fa8da',
            '#f6b26b', '#f4a6c1', '#9bb8ff', '#8fd3c8', '#c5d86d', '#c3a6e8', '#d9b08c', '#8ecae6',
            '#9ccc9c', '#a5b4fc', '#f4b183', '#e8a5a5', '#82c7b2', '#aab2bd', '#d7c97a', '#b0bec5',
        ];
        $withAlpha = static function (string $hex, string $alpha): string {
            $base = ltrim(trim($hex), '#');
            if (strlen($base) !== 6) {
                return '#cbd5e1';
            }
            return '#' . $base . $alpha;
        };
        $typeBaseColorMap = $typeUniverse
            ->values()
            ->mapWithKeys(fn($type, $index) => [(string) $type => $typePalette[$index % count($typePalette)]])
            ->all();
        $typeStyleMap = collect($typeBaseColorMap)->mapWithKeys(function ($baseColor, $type) use ($withAlpha) {
            return [
                (string) $type => sprintf(
                    'background-color:%s; border-color:%s; color:%s;',
                    $withAlpha($baseColor, '1A'),
                    $withAlpha($baseColor, '66'),
                    $baseColor
                ),
            ];
        })->all();

        $defaultTypeStyle = 'background-color:#f8fafc; border-color:#cbd5e1; color:#334155;';
        $outputTypeStyle = function ($type) use ($typeStyleMap, $defaultTypeStyle) {
            $label = trim((string) $type);
            return $typeStyleMap[$label] ?? $defaultTypeStyle;
        };
    @endphp

    <style>
        .output-shell {
            background: transparent;
        }

        .output-table {
            table-layout: fixed;
            min-width: 1040px;
        }

        .output-table th {
            white-space: nowrap;
            font-size: 12px;
            letter-spacing: 0.01em;
            color: #475569;
        }

        .output-table td {
            vertical-align: top;
        }

        .output-table tbody tr:nth-child(even) {
            background: #fbfdff;
        }

        .output-table tbody tr:hover {
            background: #eef6ff;
        }

        .output-open-link-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.38rem;
            border-radius: 999px;
            border: 1px solid #c7d2fe;
            background: #eef2ff;
            color: #4338ca;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            padding: 0.48rem 0.82rem;
            transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        }

        .output-open-link-btn:hover {
            background: #e0e7ff;
            border-color: #a5b4fc;
            color: #3730a3;
        }

        .cell-clamp-2,
        .cell-clamp-3 {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }

        .cell-clamp-2 {
            -webkit-line-clamp: 2;
        }

        .cell-clamp-3 {
            -webkit-line-clamp: 3;
        }

        .output-type-pill {
            display: inline-flex;
            max-width: 100%;
            white-space: normal;
            line-height: 1.35;
            word-break: break-word;
        }

        .output-source-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid #f9a8d4;
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
            color: #9d174d;
            font-size: 11px;
            font-weight: 700;
            padding: 0.18rem 0.55rem;
            margin-top: 0.35rem;
        }

        .outputs-filter-anchor {
            scroll-margin-top: 96px;
        }

        .outputs-filter-drawer-overlay {
            position: fixed;
            inset: 0;
            z-index: 48;
            background: rgba(15, 23, 42, 0.34);
            backdrop-filter: blur(6px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.18s ease;
        }

        .outputs-filter-drawer-overlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .outputs-filter-drawer {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 49;
            width: min(92vw, 380px);
            transform: translateX(-100%);
            transition: transform 0.22s ease;
        }

        .outputs-filter-drawer.is-open {
            transform: translateX(0);
        }

        .outputs-filter-fab {
            position: fixed;
            left: 16px;
            bottom: calc(16px + env(safe-area-inset-bottom, 0px));
            z-index: 35;
            width: 76px;
            min-height: 84px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
            padding: 0.75rem 0.55rem;
            border-radius: 22px;
            border: 1px solid #e2e8f0;
            background: rgba(255, 255, 255, 0.94);
            color: #0f172a;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.14);
            backdrop-filter: blur(12px);
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
            transition: opacity 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .outputs-filter-fab.is-visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .outputs-filter-fab.is-visible:hover {
            transform: translateY(-2px);
            border-color: #67e8f9;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.16);
        }

        .outputs-filter-fab:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.18), 0 20px 40px rgba(15, 23, 42, 0.16);
        }

        .outputs-filter-fab__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0f172a 0%, #155e75 100%);
            color: #ffffff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
        }

        .outputs-filter-fab__title {
            font-size: 0.74rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
        }

        .outputs-filter-fab__meta {
            font-size: 0.61rem;
            color: #64748b;
            line-height: 1.1;
            text-align: center;
        }

        .outputs-filter-fab__badge {
            position: absolute;
            top: -0.35rem;
            right: -0.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.45rem;
            height: 1.45rem;
            padding: 0 0.32rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
            border: 2px solid #fff;
            color: #ffffff;
            font-size: 0.68rem;
            font-weight: 800;
            line-height: 1;
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
        }

        .outputs-filter-fab__badge.hidden {
            display: none;
        }

        .outputs-responsive-cards {
            display: block;
        }

        .outputs-desktop-table {
            display: none;
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .outputs-responsive-cards {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }
        }

        @media (min-width: 1025px) {
            .outputs-responsive-cards {
                display: none;
            }

            .outputs-desktop-table {
                display: block;
            }
        }

        @media (max-width: 1024px) {
            .outputs-filter-fab {
                width: 68px;
                min-height: 76px;
                padding: 0.65rem 0.45rem;
                border-radius: 20px;
            }

            .outputs-filter-fab__icon {
                width: 36px;
                height: 36px;
                border-radius: 15px;
            }

            .outputs-filter-fab__title {
                font-size: 0.68rem;
            }

            .outputs-filter-fab__meta {
                font-size: 0.58rem;
            }

            .outputs-filter-fab__badge {
                top: -0.3rem;
                right: -0.3rem;
            }
        }

        @media (max-width: 640px) {
            .outputs-filter-fab {
                width: 58px;
                min-height: 58px;
                padding: 8px;
                border-radius: 18px;
                gap: 0;
            }

            .outputs-filter-fab__icon {
                width: 34px;
                height: 34px;
                border-radius: 14px;
            }

            .outputs-filter-fab__title,
            .outputs-filter-fab__meta {
                display: none;
            }

            .outputs-filter-fab__badge {
                min-width: 1.25rem;
                height: 1.25rem;
                font-size: 0.62rem;
            }
        }
    </style>

    <div class="output-shell min-h-screen">
        <div class="container mx-auto mt-11 space-y-6 px-4 pb-8">
            <section class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-sm">
                <div class="flex flex-col gap-5 px-5 py-6 md:px-7 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">📑 รายการ Output</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-600 md:text-[15px]">
                            รวมผลงานจากโครงการวิจัย งานนอกโครงการ และผลงานภาระงานไว้ในหน้าเดียว เพื่อค้นหาและกรองได้เร็วขึ้น.
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row">
                        <a href="{{ route('projects.index') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            ← กลับไปโครงการ
                        </a>
                        <a href="{{ route('projects.create') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                            ➕ เพิ่มโครงการ
                        </a>
                    </div>
                </div>
            </section>

            <section id="outputsFilterAnchor" class="outputs-filter-anchor rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">คัดกรองข้อมูล</h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">
                            <span>ฟิลเตอร์ที่ใช้อยู่</span>
                            <span id="outputsActiveFilterPill" class="font-semibold text-slate-900">{{ $activeFilterCount }}</span>
                        </span>
                        <a href="{{ route('outputs.index') }}" data-output-reset
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            ล้างฟิลเตอร์
                        </a>
                    </div>
                </div>

                <form id="outputsPrimaryFilterForm" method="GET" action="{{ route('outputs.index') }}" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                    <input type="hidden" name="dir" value="{{ $currentDir }}">

                    <label class="block xl:col-span-2">
                        <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                        <input id="outputsPrimarySearchInput" type="text" name="search" value="{{ request('search') }}"
                            data-output-live="true"
                            class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200"
                            placeholder="ชื่อผลงาน / ชื่อโครงการ / Project ID / งานอ้างอิง">
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ประเภทผลงาน</span>
                        <select name="type" data-output-live="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach ($typeOptions as $type)
                                <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                        <select name="year" data-output-live="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ลักษณะผลงาน</span>
                        <select name="work_status" data-output-live="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach ($workStatusOptions as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected($currentWorkStatus === $statusValue)>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                        <select name="sort" data-output-live="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                            <option value="created_at" @selected($currentSort === 'created_at')>บันทึกล่าสุด</option>
                            <option value="year" @selected($currentSort === 'year')>ปี</option>
                            <option value="type" @selected($currentSort === 'type')>ประเภท</option>
                            <option value="title" @selected($currentSort === 'title')>ชื่อผลงาน</option>
                        </select>
                    </label>
                </form>
            </section>

            <div id="outputsLoadingIndicator" class="hidden items-center gap-2 text-xs text-slate-500">
                <span class="inline-block h-3 w-3 rounded-full border-2 border-indigo-200 border-t-indigo-600 animate-spin"></span>
                กำลังอัปเดตข้อมูล...
            </div>

            <div id="outputsAjaxArea" class="space-y-6">
                <section id="outputsStatsGrid" class="grid gap-3 md:grid-cols-3">
                    <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ผลลัพธ์ทั้งหมด</div>
                        <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($outputs->total()) }}</div>
                        <div class="mt-1 text-xs text-slate-500">ในหน้าปัจจุบัน {{ $outputs->count() }} รายการ</div>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">กำลังใช้ฟิลเตอร์</div>
                        <div id="outputsActiveFilterStat" class="mt-2 text-2xl font-bold text-slate-900">{{ $activeFilterCount }}</div>
                        <div class="mt-1 text-xs text-slate-500">จัดเรียงตาม {{ $sortLabel }}</div>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">กลุ่มข้อมูลที่พบ</div>
                        <div class="mt-2 text-2xl font-bold text-slate-900">{{ $typeTotal }}</div>
                        <div class="mt-1 text-xs text-slate-500">เชื่อมกับรายการอ้างอิง {{ $linkedTotal }} งาน</div>
                    </article>
                </section>

                <section id="outputs-results-shell" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md transition-opacity duration-200">
                    <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50 px-4 py-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">รายการผลลัพธ์</div>
                            <div class="mt-1 text-sm text-slate-500">
                                แสดงผล {{ $outputs->firstItem() ?? 0 }}–{{ $outputs->lastItem() ?? 0 }} จากทั้งหมด {{ $outputs->total() }} รายการ
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">
                            จัดเรียงตาม <span class="font-semibold text-slate-700">{{ $sortLabel }}</span>
                            <span class="mx-1">•</span>
                            {{ $currentDir === 'asc' ? 'น้อย → มาก' : 'มาก → น้อย' }}
                        </div>
                    </div>

                    @if ($outputs->count() > 0)
                    <div class="outputs-responsive-cards gap-3 p-4">
                        @foreach ($outputs as $output)
                            @php
                                $created = $output->created_at ? \Carbon\Carbon::parse($output->created_at) : null;
                                $typeLabel = $output->type ?: '-';
                            @endphp
                            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="space-y-2">
                                    <h3 class="cell-clamp-3 text-sm font-semibold leading-6 text-slate-900">{{ $output->title ?: '-' }}</h3>
                                    @if (!empty($output->source_badge))
                                        <span class="output-source-badge">{{ $output->source_badge }}</span>
                                    @endif
                                    <span class="output-type-pill rounded-full border px-2.5 py-1 text-[11px] font-semibold" style="{{ $outputTypeStyle($typeLabel) }}">
                                        {{ $typeLabel }}
                                    </span>
                                </div>
                                <dl class="mt-4 space-y-2 text-sm text-slate-600">
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">ปี</dt>
                                        <dd class="font-medium text-slate-800">{{ $output->year_be ?: '-' }}</dd>
                                    </div>
                                    <div class="space-y-1">
                                        <dt class="text-slate-500">รายการอ้างอิง</dt>
                                        <dd class="text-slate-800">
                                            @if ($output->link && $output->project_label)
                                                <a href="{{ $output->link }}" class="font-medium text-indigo-700 hover:underline">
                                                    {{ $output->project_label }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </dd>
                                    </div>
                                    @if (!empty($output->detail) || !empty($output->related_link))
                                        <div class="space-y-1">
                                            <dt class="text-slate-500">ผลงานที่เกี่ยวข้อง</dt>
                                            <dd class="space-y-2">
                                                @if (!empty($output->detail))
                                                    <div class="cell-clamp-2 text-slate-700">{{ $output->detail }}</div>
                                                @endif
                                                @if (!empty($output->related_link))
                                                    <a href="{{ $output->related_link }}" target="_blank" rel="noopener noreferrer" class="output-open-link-btn">
                                                        เปิดผลงานที่เกี่ยวข้อง
                                                    </a>
                                                @endif
                                            </dd>
                                        </div>
                                    @endif
                                    <div class="flex justify-between gap-3 border-t border-slate-100 pt-2 text-xs">
                                        <dt class="text-slate-500">วันที่ลงข้อมูล</dt>
                                        <dd class="text-slate-700">
                                            {{ $created ? $created->locale('th')->translatedFormat('j F') . ' ' . ($created->year + 543) : '-' }}
                                        </dd>
                                    </div>
                                </dl>
                            </article>
                        @endforeach
                    </div>

                    <div class="outputs-desktop-table overflow-auto">
                        <table class="output-table min-w-full">
                            <colgroup>
                                <col style="width: 30%;">
                                <col style="width: 18%;">
                                <col style="width: 9%;">
                                <col style="width: 29%;">
                                <col style="width: 14%;">
                            </colgroup>
                            <thead class="border-b border-slate-200 bg-slate-50">
                                <tr class="text-left text-xs font-semibold text-slate-600">
                                    <th class="px-4 py-3">
                                        <a href="{{ $sortUrl('title') }}" class="hover:text-indigo-700">ชื่อผลงาน <span class="ml-1">{{ $sortIcon('title') }}</span></a>
                                    </th>
                                    <th class="px-4 py-3">
                                        <a href="{{ $sortUrl('type') }}" class="hover:text-indigo-700">ประเภท <span class="ml-1">{{ $sortIcon('type') }}</span></a>
                                    </th>
                                    <th class="px-4 py-3 text-center">
                                        <a href="{{ $sortUrl('year') }}" class="hover:text-indigo-700">ปี (พ.ศ.) <span class="ml-1">{{ $sortIcon('year') }}</span></a>
                                    </th>
                                    <th class="px-4 py-3">รายการอ้างอิง / ผลงานที่เกี่ยวข้อง</th>
                                    <th class="px-4 py-3 text-center">
                                        <a href="{{ $sortUrl('created_at') }}" class="hover:text-indigo-700">วันที่ลงข้อมูล <span class="ml-1">{{ $sortIcon('created_at') }}</span></a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($outputs as $output)
                                    @php
                                        $created = $output->created_at ? \Carbon\Carbon::parse($output->created_at) : null;
                                        $typeLabel = $output->type ?: '-';
                                    @endphp
                                    <tr class="transition hover:bg-indigo-50/40">
                                        <td class="px-4 py-4 text-slate-900">
                                            <div class="cell-clamp-3 font-semibold leading-6">{{ $output->title ?: '-' }}</div>
                                            @if (!empty($output->source_badge))
                                                <span class="output-source-badge">{{ $output->source_badge }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold" style="{{ $outputTypeStyle($typeLabel) }}">
                                                {{ $typeLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center text-slate-700">{{ $output->year_be ?: '-' }}</td>
                                        <td class="px-4 py-4 text-slate-700">
                                            <div class="space-y-2">
                                                <div>
                                                    @if ($output->link && $output->project_label)
                                                        <a href="{{ $output->link }}" class="font-medium leading-6 text-indigo-700 hover:underline">
                                                            {{ $output->project_label }}
                                                        </a>
                                                    @else
                                                        <span class="text-slate-400">-</span>
                                                    @endif
                                                </div>
                                                @if (!empty($output->detail))
                                                    <div class="text-xs leading-5 text-slate-500">{{ $output->detail }}</div>
                                                @endif
                                                @if (!empty($output->related_link))
                                                    <div>
                                                        <a href="{{ $output->related_link }}" target="_blank" rel="noopener noreferrer" class="output-open-link-btn">
                                                            เปิดผลงานที่เกี่ยวข้อง
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-center text-sm text-slate-500">
                                            {{ $created ? $created->locale('th')->translatedFormat('j F') . ' ' . ($created->year + 543) : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="px-4 py-8">
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-14 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-sm">
                                <svg class="h-7 w-7 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-slate-800">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</h3>
                            <p class="mt-2 text-sm text-slate-500">ลองปรับคำค้นหา ปี หรือประเภทผลงาน แล้วระบบจะอัปเดตผลลัพธ์ทันที</p>
                        </div>
                    </div>
                    @endif

                    @if ($outputs->count() > 0)
                        <div class="border-t border-slate-100 bg-slate-50 px-4 py-3">
                            {{ $outputs->links('vendor.pagination.th') }}
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>

    <div id="outputsFilterDrawerOverlay" class="outputs-filter-drawer-overlay"></div>
    <aside id="outputsFilterDrawer" class="outputs-filter-drawer" aria-hidden="true">
        <div class="flex h-full flex-col border-r border-slate-200 bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-4 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">คัดกรองข้อมูล</h3>
                    <p class="mt-1 text-xs text-slate-500">ปรับค่าแล้วข้อมูลในตารางจะอัปเดตทันที</p>
                </div>
                <button id="outputsFilterDrawerClose" type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                    <span class="sr-only">ปิดฟิลเตอร์</span>
                    <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                </button>
            </div>

            <form id="outputsDrawerFilterForm" method="GET" action="{{ route('outputs.index') }}" class="flex h-full flex-col">
                <div class="flex-1 space-y-4 overflow-y-auto px-4 py-4">
                    <input type="hidden" name="dir" value="{{ $currentDir }}">

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                        <input id="outputsDrawerSearchInput" type="text" name="search" value="{{ request('search') }}"
                            data-output-live="true"
                            class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200"
                            placeholder="ชื่อผลงาน / ชื่อโครงการ / Project ID / งานอ้างอิง">
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ประเภทผลงาน</span>
                        <select name="type" data-output-live="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach ($typeOptions as $type)
                                <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                        <select name="year" data-output-live="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ลักษณะผลงาน</span>
                        <select name="work_status" data-output-live="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach ($workStatusOptions as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected($currentWorkStatus === $statusValue)>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                        <select name="sort" data-output-live="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                            <option value="created_at" @selected($currentSort === 'created_at')>บันทึกล่าสุด</option>
                            <option value="year" @selected($currentSort === 'year')>ปี</option>
                            <option value="type" @selected($currentSort === 'type')>ประเภท</option>
                            <option value="title" @selected($currentSort === 'title')>ชื่อผลงาน</option>
                        </select>
                    </label>
                </div>

                <div class="border-t border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit"
                            class="flex-1 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                            ใช้ตัวกรอง
                        </button>
                        <a href="{{ route('outputs.index') }}" data-output-reset
                            class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            ล้าง
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </aside>

    <button id="outputsFilterFab" type="button" aria-label="เปิดฟิลเตอร์" class="outputs-filter-fab" data-output-open-drawer>
        <span class="outputs-filter-fab__icon" aria-hidden="true">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                <path d="M4 6h16M7 12h10m-7 6h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </span>
        <span class="outputs-filter-fab__title">ฟิลเตอร์</span>
        <span class="outputs-filter-fab__meta">เปิดแผง</span>
        <span id="outputsFilterFabBadge" class="outputs-filter-fab__badge @if ($activeFilterCount === 0) hidden @endif">{{ $activeFilterCount }}</span>
    </button>

    <script>
        (() => {
            'use strict';

            const selectors = {
                statsGrid: '#outputsStatsGrid',
                resultsShell: '#outputs-results-shell',
                loading: '#outputsLoadingIndicator',
                primaryForm: '#outputsPrimaryFilterForm',
                drawerForm: '#outputsDrawerFilterForm',
                drawerSearchInput: '#outputsDrawerSearchInput',
                filterAnchor: '#outputsFilterAnchor',
                drawer: '#outputsFilterDrawer',
                drawerOverlay: '#outputsFilterDrawerOverlay',
                drawerClose: '#outputsFilterDrawerClose',
                openDrawer: '[data-output-open-drawer]',
                fab: '#outputsFilterFab',
                fabBadge: '#outputsFilterFabBadge',
                activeFilterStat: '#outputsActiveFilterStat',
                activeFilterPill: '#outputsActiveFilterPill',
            };

            let controller = null;
            let requestToken = 0;
            let debounceTimer = null;
            let drawerOpen = false;
            let previousBodyOverflow = '';

            const query = (selector, root = document) => root.querySelector(selector);
            const queryAll = (selector, root = document) => Array.from(root.querySelectorAll(selector));
            const currentPath = window.location.pathname;
            const getPrimaryForm = () => query(selectors.primaryForm);
            const getDrawerForm = () => query(selectors.drawerForm);
            const getForms = () => [getPrimaryForm(), getDrawerForm()].filter(Boolean);

            const countActiveFilters = (form) => {
                if (!form) return 0;
                return queryAll('[name]', form).reduce((count, field) => {
                    if (!field.name || ['page', 'sort', 'dir'].includes(field.name)) return count;
                    if ((field.type === 'checkbox' || field.type === 'radio') && field.checked) return count + 1;
                    if (field.type === 'checkbox' || field.type === 'radio') return count;
                    return String(field.value ?? '').trim() !== '' ? count + 1 : count;
                }, 0);
            };

            const updateFilterBadges = () => {
                const form = getPrimaryForm() || getDrawerForm();
                const count = countActiveFilters(form);
                const stat = query(selectors.activeFilterStat);
                const pill = query(selectors.activeFilterPill);
                const fabBadge = query(selectors.fabBadge);

                if (stat) stat.textContent = String(count);
                if (pill) pill.textContent = String(count);
                if (fabBadge) {
                    fabBadge.textContent = String(count);
                    fabBadge.classList.toggle('hidden', count === 0);
                }
            };

            const syncFabVisibility = () => {
                const fab = query(selectors.fab);
                const anchor = query(selectors.filterAnchor);
                if (!fab || !anchor) return;

                const rect = anchor.getBoundingClientRect();
                fab.classList.toggle('is-visible', rect.bottom < 48);
            };

            const setFieldValue = (field, value) => {
                if (!field) return;
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = Array.isArray(value) ? value.includes(field.value || '1') : field.value === value;
                    return;
                }
                field.value = value ?? '';
            };

            const syncFormsFromSource = (sourceForm) => {
                if (!sourceForm) return;

                getForms().forEach((targetForm) => {
                    if (!targetForm || targetForm === sourceForm) return;

                    queryAll('[name]', sourceForm).forEach((sourceField) => {
                        if (!sourceField.name) return;
                        const targetField = targetForm.elements.namedItem(sourceField.name);
                        if (!targetField) return;

                        if (sourceField.type === 'checkbox' || sourceField.type === 'radio') {
                            const values = queryAll(`[name="${sourceField.name}"]`, sourceForm)
                                .filter((field) => field.checked)
                                .map((field) => field.value || '1');
                            const targetFields = queryAll(`[name="${sourceField.name}"]`, targetForm);
                            targetFields.forEach((field) => setFieldValue(field, values));
                            return;
                        }

                        setFieldValue(targetField, sourceField.value);
                    });
                });
            };

            const openDrawer = () => {
                const drawer = query(selectors.drawer);
                const overlay = query(selectors.drawerOverlay);
                if (!drawer || !overlay) return;

                syncFormsFromSource(getPrimaryForm() || getDrawerForm());
                drawer.classList.add('is-open');
                overlay.classList.add('is-open');
                drawer.setAttribute('aria-hidden', 'false');
                drawerOpen = true;
                previousBodyOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';

                window.setTimeout(() => {
                    query(selectors.drawerSearchInput)?.focus({
                        preventScroll: true
                    });
                }, 120);
            };

            const closeDrawer = () => {
                const drawer = query(selectors.drawer);
                const overlay = query(selectors.drawerOverlay);
                if (!drawer || !overlay) return;

                drawer.classList.remove('is-open');
                overlay.classList.remove('is-open');
                drawer.setAttribute('aria-hidden', 'true');
                drawerOpen = false;
                document.body.style.overflow = previousBodyOverflow;
            };

            const setLoading = (isLoading) => {
                const loadingEl = query(selectors.loading);
                const resultsShell = query(selectors.resultsShell);

                if (loadingEl) {
                    loadingEl.classList.toggle('hidden', !isLoading);
                    loadingEl.classList.toggle('flex', isLoading);
                }

                if (resultsShell) {
                    resultsShell.classList.toggle('opacity-60', isLoading);
                }
            };

            const buildUrlFromForm = (form) => {
                const url = new URL(form.action, window.location.origin);
                const formData = new FormData(form);

                for (const [key, value] of formData.entries()) {
                    if (String(value ?? '').trim() !== '') {
                        url.searchParams.append(key, String(value).trim());
                    }
                }

                url.searchParams.delete('page');
                return url;
            };

            const syncFormsWithUrl = (url) => {
                getForms().forEach((form) => {
                    queryAll('[name]', form).forEach((field) => {
                        if (!field.name) return;
                        if (field.type === 'checkbox' || field.type === 'radio') {
                            field.checked = url.searchParams.getAll(field.name).includes(field.value || '1');
                            return;
                        }
                        field.value = url.searchParams.get(field.name) ?? '';
                    });
                });
            };

            const replaceFromResponse = (html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextStats = query(selectors.statsGrid, doc);
                const nextResults = query(selectors.resultsShell, doc);
                const currentStats = query(selectors.statsGrid);
                const currentResults = query(selectors.resultsShell);

                if (!nextStats || !nextResults || !currentStats || !currentResults) return false;
                currentStats.replaceWith(nextStats);
                currentResults.replaceWith(nextResults);
                return true;
            };

            const fetchAndRender = async (url, options = {}) => {
                const token = ++requestToken;
                const { updateUrl = true } = options;

                if (controller) controller.abort();
                controller = new AbortController();
                setLoading(true);

                try {
                    const response = await fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: controller.signal,
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const html = await response.text();
                    if (token !== requestToken) return;
                    if (!replaceFromResponse(html)) {
                        throw new Error('Cannot replace outputs sections from response');
                    }

                    if (updateUrl) {
                        history.replaceState({ ajax: true }, '', `${url.pathname}${url.search}`);
                    }

                    syncFormsWithUrl(url);
                    updateFilterBadges();
                    syncFabVisibility();
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error(error);
                    }
                } finally {
                    if (token === requestToken) {
                        setLoading(false);
                    }
                }
            };

            const submitDebounced = (delay = 240) => {
                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(() => {
                    const form = getPrimaryForm() || getDrawerForm();
                    if (!form) return;
                    fetchAndRender(buildUrlFromForm(form), { updateUrl: true });
                }, delay);
            };

            const bindForms = () => {
                getForms().forEach((form) => {
                    if (!form || form.dataset.ajaxBound === 'true') return;
                    form.dataset.ajaxBound = 'true';

                    form.addEventListener('submit', (event) => {
                        event.preventDefault();
                        syncFormsFromSource(form);
                        const primaryForm = getPrimaryForm() || form;
                        fetchAndRender(buildUrlFromForm(primaryForm), { updateUrl: true });
                    });

                    queryAll('[data-output-live]', form).forEach((field) => {
                        if (field.matches('input[type="text"], input[type="search"]')) {
                            field.addEventListener('input', () => {
                                syncFormsFromSource(form);
                                updateFilterBadges();
                                submitDebounced(260);
                            });
                            field.addEventListener('keydown', (event) => {
                                if (event.key === 'Enter') {
                                    event.preventDefault();
                                    syncFormsFromSource(form);
                                    submitDebounced(0);
                                }
                            });
                            return;
                        }

                        field.addEventListener('change', () => {
                            syncFormsFromSource(form);
                            updateFilterBadges();
                            submitDebounced(120);
                        });
                    });
                });
            };

            const bindResetLinks = () => {
                if (window.__outputsResetLinksBound) return;
                window.__outputsResetLinksBound = true;

                document.addEventListener('click', (event) => {
                    const link = event.target.closest('[data-output-reset]');
                    if (!link) return;

                    event.preventDefault();
                    const url = new URL(link.href, window.location.origin);
                    syncFormsWithUrl(url);
                    updateFilterBadges();
                    fetchAndRender(url, { updateUrl: true });
                });
            };

            const bindDelegatedLinks = () => {
                if (window.__outputsDelegatedLinksBound) return;
                window.__outputsDelegatedLinksBound = true;

                document.addEventListener('click', (event) => {
                    const link = event.target.closest(`${selectors.resultsShell} a[href]`);
                    if (!link) return;

                    const href = link.getAttribute('href');
                    if (!href) return;

                    const url = new URL(href, window.location.origin);
                    const isSamePage = url.pathname === currentPath;
                    const isAjaxListLink = url.searchParams.has('page') || url.searchParams.has('sort') || url.searchParams.has('dir');

                    if (!isSamePage || !isAjaxListLink) return;

                    event.preventDefault();
                    syncFormsWithUrl(url);
                    updateFilterBadges();
                    fetchAndRender(url, { updateUrl: true });
                });
            };

            const bindDrawer = () => {
                if (window.__outputsDrawerBound) return;
                window.__outputsDrawerBound = true;

                queryAll(selectors.openDrawer).forEach((button) => {
                    button.addEventListener('click', openDrawer);
                });

                query(selectors.drawerClose)?.addEventListener('click', closeDrawer);
                query(selectors.drawerOverlay)?.addEventListener('click', closeDrawer);
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && drawerOpen) {
                        closeDrawer();
                    }
                });
            };

            const initOutputsPage = () => {
                bindForms();
                bindResetLinks();
                bindDelegatedLinks();
                bindDrawer();
                updateFilterBadges();
                syncFabVisibility();
                window.addEventListener('scroll', syncFabVisibility, { passive: true });
                window.addEventListener('resize', syncFabVisibility);
            };

            document.addEventListener('DOMContentLoaded', initOutputsPage);
        })();
    </script>
@endsection

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
        $outputSourceBadgeClass = function ($sourceBadge) {
            return match (mb_strtolower(trim((string) $sourceBadge))) {
                'mission' => 'output-source-badge output-source-badge--mission',
                'extra' => 'output-source-badge output-source-badge--extra',
                default => 'output-source-badge',
            };
        };
    @endphp

    <style>
        .output-shell {
            background: transparent;
        }

        .output-hero {
            position: relative;
            overflow: hidden;
        }

        .output-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 87% 18%, rgba(16, 185, 129, 0.16), transparent 42%);
        }

        .output-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.25rem 0.65rem;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            color: #065f46;
            background: #d1fae5;
            border: 1px solid #86efac;
        }

        .output-filter-panel {
            border-color: #86efac;
            background: linear-gradient(180deg, #f6fffb 0%, #ffffff 100%);
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
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            padding: 0.18rem 0.55rem;
            margin-top: 0.35rem;
        }

        .output-source-badge--mission {
            border-color: #5eead4;
            background: linear-gradient(135deg, #ecfdf5 0%, #ccfbf1 100%);
            color: #0f766e;
        }

        .output-source-badge--extra {
            border-color: #f9a8d4;
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
            color: #9d174d;
        }

        .output-title-edit-btn {
            display: block;
            width: 100%;
            text-align: left;
            color: #0f172a;
            transition: color 0.16s ease;
        }

        .output-title-edit-btn:hover {
            color: #047857;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .output-title-edit-btn:focus-visible {
            outline: 3px solid rgba(16, 185, 129, 0.28);
            outline-offset: 3px;
            border-radius: 0.5rem;
        }

        .outputs-edit-modal {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 0.75rem;
            background: rgba(15, 23, 42, 0.48);
            backdrop-filter: blur(6px);
        }

        .outputs-edit-modal.is-open {
            display: flex;
        }

        .outputs-edit-dialog {
            width: min(98vw, 1120px);
            max-height: min(92vh, 860px);
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid #dbe7f5;
            background: #fff;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.26);
        }

        .outputs-edit-field {
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid #cbd5e1;
            background: #fff;
            padding: 0.7rem 0.85rem;
            font-size: 0.875rem;
            color: #0f172a;
        }

        .outputs-edit-field:focus {
            border-color: #10b981;
            outline: none;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.16);
        }

        .outputs-edit-invalid {
            border-color: #ef4444 !important;
            background: #fef2f2 !important;
            box-shadow: 0 0 0 3px rgba(254, 202, 202, 0.82) !important;
        }

        .outputs-edit-person-row {
            grid-template-columns: 1fr;
        }

        @media (min-width: 768px) {
            .outputs-edit-person-row--author {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr) minmax(0, 1.4fr) auto;
            }

            .outputs-edit-person-row--plain {
                grid-template-columns: minmax(0, 1.05fr) minmax(0, 1.7fr) auto;
            }
        }

        .outputs-edit-section[hidden] {
            display: none;
        }

        .outputs-edit-section {
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            padding: 1rem;
        }

        .outputs-edit-section--ea101 {
            border-color: #bfdbfe;
            background: rgba(239, 246, 255, 0.72);
        }

        .outputs-edit-section--ea101 .outputs-edit-section-title {
            color: #1e40af;
        }

        .outputs-edit-section--ea103 {
            border-color: #fed7aa;
            background: rgba(255, 247, 237, 0.78);
        }

        .outputs-edit-section--ea103 .outputs-edit-section-title {
            color: #9a3412;
        }

        .outputs-edit-section--ea118 {
            border-color: #ddd6fe;
            background: rgba(245, 243, 255, 0.78);
        }

        .outputs-edit-section--ea118 .outputs-edit-section-title {
            color: #5b21b6;
        }

        .outputs-edit-section--ea119 {
            border-color: #a7f3d0;
            background: rgba(236, 253, 245, 0.78);
        }

        .outputs-edit-section--ea119 .outputs-edit-section-title {
            color: #047857;
        }

        .outputs-edit-add-btn {
            border-radius: 0.5rem;
            padding: 0.45rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #fff;
        }

        .outputs-edit-add-btn--ea101 { background: #2563eb; }
        .outputs-edit-add-btn--ea103 { background: #ea580c; }
        .outputs-edit-add-btn--ea118 { background: #7c3aed; }
        .outputs-edit-add-btn--ea119 { background: #059669; }

        @media (max-width: 767px) {
            .outputs-edit-modal {
                align-items: stretch;
                padding: 0;
            }

            .outputs-edit-dialog {
                width: 100vw;
                max-height: 100dvh;
                border-radius: 0;
                border-left: 0;
                border-right: 0;
            }

            .outputs-edit-dialog form {
                max-height: 100dvh;
            }

            .outputs-edit-section {
                padding: 0.8rem;
            }

            .outputs-edit-add-btn,
            [data-output-edit-remove] {
                width: 100%;
            }
        }

        .output-export-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.75rem;
            background: #059669;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
            transition: background-color 0.2s ease;
        }

        .output-export-btn:hover {
            background: #047857;
        }

        .output-export-btn--pdf {
            background: #dc2626;
        }

        .output-export-btn--pdf:hover {
            background: #b91c1c;
        }

        .outputs-export-modal {
            position: fixed;
            inset: 0;
            z-index: 70;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.42);
            backdrop-filter: blur(6px);
        }

        .outputs-export-modal.is-open {
            display: flex;
        }

        .outputs-export-dialog {
            width: min(100%, 560px);
            border-radius: 20px;
            border: 1px solid #dbe7f5;
            background: #fff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.24);
            overflow: hidden;
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

        @media (min-width: 641px) and (max-width: 767px) {
            .outputs-responsive-cards {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }
        }

        @media (min-width: 768px) {
            .outputs-responsive-cards {
                display: none;
            }

            .outputs-desktop-table {
                display: block;
            }
        }

        @media (min-width: 768px) and (max-width: 1366px) {
            .outputs-desktop-table {
                overflow-x: visible;
            }

            .output-table {
                width: 100%;
                min-width: 0;
                table-layout: fixed;
                font-size: 11.5px;
            }

            .output-table th,
            .output-table td {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            .output-table th {
                font-size: 10px;
                white-space: normal;
                line-height: 1.25;
            }

            .output-table col:nth-child(1) { width: 28% !important; }
            .output-table col:nth-child(2) { width: 17% !important; }
            .output-table col:nth-child(3) { width: 8% !important; }
            .output-table col:nth-child(4) { width: 32% !important; }
            .output-table col:nth-child(5) { width: 15% !important; }

            .output-table .cell-clamp-3 {
                -webkit-line-clamp: 4;
            }

            .output-table .output-open-link-btn {
                padding: 0.38rem 0.5rem;
                font-size: 10px;
            }
        }

        @media (max-width: 767px) {
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
            <section class="output-hero overflow-hidden rounded-[28px] border border-emerald-200/80 bg-white shadow-sm">
                <div class="flex flex-col gap-5 px-5 py-6 md:px-7">
                    <div class="max-w-3xl">
                        <span class="output-chip">All Outputs</span>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">รายการ Output</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-600 md:text-[15px]">
                            รวมผลงานจากโครงการวิจัย Mission และ Extra ไว้ในหน้าเดียว เพื่อค้นหาและกรองได้เร็วขึ้น.
                        </p>
                    </div>
                </div>
            </section>

            <form id="outputsPrimaryFilterForm" method="GET" action="{{ route('outputs.index') }}" class="outputs-filter-anchor output-filter-panel rounded-2xl border bg-white p-5 shadow-sm">
                <section id="outputsFilterAnchor" class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">คัดกรองข้อมูล</h2>
                        <p class="mt-1 text-sm text-slate-500">ค้นหาและ export ตามเงื่อนไขที่กำลังเลือกอยู่</p>
                    </div>
                    <button type="button"
                        class="output-export-btn"
                        data-output-export-trigger
                        data-output-export-pdf="{{ route('outputs.pdf') }}"
                        data-output-export-excel="{{ route('outputs.excel') }}">
                        Export ข้อมูล
                    </button>
                </section>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
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

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ทิศทาง</span>
                        <select name="dir" data-output-live="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                            <option value="desc" @selected($currentDir === 'desc')>มาก → น้อย</option>
                            <option value="asc" @selected($currentDir === 'asc')>น้อย → มาก</option>
                        </select>
                    </label>
                </div>

                <div class="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <span>เลือก dropdown เพื่ออัปเดตทันที หรือพิมพ์ค้นหาแล้วระบบจะค้นให้อัตโนมัติ</span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 font-medium text-slate-600">
                            <span>ฟิลเตอร์ที่ใช้อยู่</span>
                            <span id="outputsActiveFilterPill" class="font-semibold text-slate-900">{{ $activeFilterCount }}</span>
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700">
                            ค้นหา
                        </button>
                        <a href="{{ route('outputs.index') }}" data-output-reset
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            ล้างฟิลเตอร์
                        </a>
                    </div>
                </div>
            </form>

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
                                    <button type="button" class="output-title-edit-btn cell-clamp-3 text-sm font-semibold leading-6" data-output-edit-url="{{ $output->edit_url ?? '' }}">
                                        {{ $output->title ?: '-' }}
                                    </button>
                                    @if (!empty($output->source_badge))
                                        <span class="{{ $outputSourceBadgeClass($output->source_badge) }}">{{ $output->source_badge }}</span>
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
                                            <button type="button" class="output-title-edit-btn cell-clamp-3 font-semibold leading-6" data-output-edit-url="{{ $output->edit_url ?? '' }}">
                                                {{ $output->title ?: '-' }}
                                            </button>
                                            @if (!empty($output->source_badge))
                                                <span class="{{ $outputSourceBadgeClass($output->source_badge) }}">{{ $output->source_badge }}</span>
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

    <div id="outputsExportModal" class="outputs-export-modal" aria-hidden="true">
        <div class="outputs-export-dialog" role="dialog" aria-modal="true" aria-labelledby="outputsExportTitle">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                <div>
                    <div id="outputsExportTitle" class="text-base font-semibold text-slate-900">Export ข้อมูล</div>
                    <p class="mt-1 text-sm text-slate-500">เลือกชนิดไฟล์และขอบเขตข้อมูลก่อนโหลด</p>
                </div>
                <button type="button" class="rounded-lg px-2 py-1 text-sm font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-700" data-output-export-close>
                    ปิด
                </button>
            </div>
            <div class="space-y-4 px-5 py-5">
                <div>
                    <div class="mb-2 text-xs font-semibold text-slate-600">รูปแบบไฟล์</div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-emerald-200 hover:bg-emerald-50/40">
                            <input type="radio" name="output_export_format" value="pdf" checked class="mt-1">
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">PDF</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">รายงานสำหรับอ่านและพิมพ์</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-emerald-200 hover:bg-emerald-50/40">
                            <input type="radio" name="output_export_format" value="excel" class="mt-1">
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">Excel</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">ตารางข้อมูลสำหรับทำงานต่อ</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div>
                    <div class="mb-2 text-xs font-semibold text-slate-600">ช่วงข้อมูล</div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-emerald-200 hover:bg-emerald-50/40">
                        <input type="radio" name="output_export_scope" value="all" checked class="mt-1">
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">ทั้งหมด</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">โหลดตามฟิลเตอร์ปัจจุบันทั้งหมด</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-emerald-200 hover:bg-emerald-50/40">
                        <input type="radio" name="output_export_scope" value="range" class="mt-1">
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">ช่วงเวลา</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">เลือกจากวันที่ลงข้อมูล</span>
                        </span>
                    </label>
                </div>
                </div>

                <div id="outputsExportRangeFields" class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ตั้งแต่วันที่</span>
                        <input id="outputsExportStartDate" type="text" class="thai-datepicker mt-1 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm" placeholder="เลือกวันที่เริ่มต้น" data-min-date="{{ $exportMinDate ?? '' }}">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ถึงวันที่</span>
                        <input id="outputsExportEndDate" type="text" class="thai-datepicker mt-1 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm" placeholder="เลือกวันที่สิ้นสุด" data-min-date="{{ $exportMinDate ?? '' }}">
                    </label>
                </div>
            </div>
            <div class="flex flex-col gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" data-output-export-close>
                    ยกเลิก
                </button>
                <button type="button" id="outputsExportDownloadBtn" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    Export PDF
                </button>
            </div>
        </div>
    </div>

    <div id="outputsEditModal" class="outputs-edit-modal" aria-hidden="true">
        <datalist id="outputsEditResearchNames">
            @foreach (($userDirectory ?? collect()) as $userOption)
                <option value="{{ $userOption['research_display_name'] ?? '' }}"></option>
            @endforeach
        </datalist>
        <datalist id="outputsEditFullNames">
            @foreach (($userDirectory ?? collect()) as $userOption)
                <option value="{{ $userOption['full_name'] ?? '' }}"></option>
            @endforeach
        </datalist>
        <div class="outputs-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="outputsEditTitle">
            <form id="outputsEditForm" method="POST" class="flex max-h-[92vh] flex-col">
                @csrf
                @method('PUT')
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                    <div>
                        <div id="outputsEditTitle" class="text-base font-semibold text-slate-900">แก้ไขผลงาน</div>
                        <p id="outputsEditSubtitle" class="mt-1 text-sm text-slate-500">กำลังโหลดข้อมูล...</p>
                    </div>
                    <button type="button" class="rounded-lg px-2 py-1 text-sm font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-700" data-output-edit-close>
                        ปิด
                    </button>
                </div>

                <div class="flex-1 space-y-5 overflow-y-auto px-5 py-5">
                    <div id="outputsEditErrors" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block md:col-span-2">
                            <span class="text-xs font-semibold text-slate-600">ชื่อผลงาน</span>
                            <input name="title" class="outputs-edit-field mt-1.5" required>
                        </label>
                        <label class="block">
                            <span class="text-xs font-semibold text-slate-600">ประเภทผลงาน</span>
                            <select name="type" class="outputs-edit-field mt-1.5" required data-output-edit-type>
                                @foreach ($typeOptions as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                                @foreach (['EA101', 'EA103', 'EA118', 'EA119', 'EA120'] as $fallbackType)
                                    <option value="{{ $fallbackType }}">{{ $fallbackType }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block" data-output-edit-year-wrap>
                            <span class="text-xs font-semibold text-slate-600">ปี / วันที่ publish</span>
                            <div class="relative mt-1.5">
                                <input id="outputsEditYearDate" type="text" name="year" readonly
                                    class="js-date-be w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2 pr-10 text-sm focus:ring-2 focus:ring-blue-500"
                                    placeholder="กรุณาเลือกวันที่">
                                <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
                                    data-open-picker data-target="outputsEditYearDate">
                                    📅
                                </button>
                            </div>
                        </label>
                        <label class="hidden" data-output-edit-publish-wrap>
                            <span class="text-xs font-semibold text-slate-600">วันที่ publish</span>
                            <div class="relative mt-1.5">
                                <input id="outputsEditPublishDate" type="text" name="publish_date" readonly
                                    class="js-date-be w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2 pr-10 text-sm focus:ring-2 focus:ring-blue-500"
                                    placeholder="กรุณาเลือกวันที่">
                                <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
                                    data-open-picker data-target="outputsEditPublishDate">
                                    📅
                                </button>
                            </div>
                        </label>
                        <label class="hidden md:col-span-2" data-output-edit-job-wrap>
                            <span class="text-xs font-semibold text-slate-600">Theme / Job category</span>
                            <select name="job_category" class="outputs-edit-field mt-1.5">
                                <option value="">ไม่ระบุ</option>
                                @foreach (($jobCategoryOptions ?? []) as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block md:col-span-2">
                            <span class="text-xs font-semibold text-slate-600">รายละเอียด / หมายเหตุ / Link</span>
                            <textarea name="project_output" rows="3" class="outputs-edit-field mt-1.5"></textarea>
                            <textarea name="note" rows="3" class="outputs-edit-field mt-1.5 hidden"></textarea>
                        </label>
                    </div>

                    <section class="outputs-edit-section outputs-edit-section--ea101" data-output-edit-section="EA101" hidden>
                        <div class="outputs-edit-section-title text-sm font-semibold">EA101 Research paper / Journal article</div>
                        <div class="mt-3 grid gap-4 md:grid-cols-3">
                            <label class="block md:col-span-2"><span class="text-xs font-semibold text-slate-600">Journal</span><input name="journal" class="outputs-edit-field mt-1.5"></label>
                            <label class="block"><span class="text-xs font-semibold text-slate-600">Ranking</span><select name="quartile" class="outputs-edit-field mt-1.5"><option value="">ไม่ระบุ</option>@foreach (['Q1','Q2','Q3','Q4','TCI 1','TCI 2'] as $q)<option value="{{ $q }}">{{ $q }}</option>@endforeach</select></label>
                            <label class="block"><span class="text-xs font-semibold text-slate-600">Impact factor</span><input type="number" step="0.001" name="impact_factor" class="outputs-edit-field mt-1.5"></label>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-3">
                            <div class="outputs-edit-section-title text-xs font-semibold">Authors</div>
                            <button type="button" class="outputs-edit-add-btn outputs-edit-add-btn--ea101" data-output-edit-add="authors">+ เพิ่มผู้เขียน</button>
                        </div>
                        <div class="mt-3 space-y-2" data-output-edit-list="authors"></div>
                        <div class="mt-3 flex justify-center">
                            <button type="button" class="outputs-edit-add-btn outputs-edit-add-btn--ea101" data-output-edit-add="authors">+ เพิ่มผู้เขียน</button>
                        </div>
                    </section>

                    <section class="outputs-edit-section outputs-edit-section--ea103" data-output-edit-section="EA103" hidden>
                        <div class="outputs-edit-section-title text-sm font-semibold">EA103 Presentation in academic conference</div>
                        <div class="mt-3 grid gap-4 md:grid-cols-3">
                            <label class="block"><span class="text-xs font-semibold text-slate-600">Conference</span><input name="conference_name" class="outputs-edit-field mt-1.5"></label>
                            <label class="block">
                                <span class="text-xs font-semibold text-slate-600">Presentation type</span>
                                <select name="conference_presentation_type" class="outputs-edit-field mt-1.5">
                                    <option value="">ไม่ระบุ</option>
                                    @foreach (['Oral Presentation', 'Poster Presentation', 'Abstract Presentation', 'Panel Discussion', 'Keynote Speech', 'World café', 'Moderator'] as $presentationType)
                                        <option value="{{ $presentationType }}">{{ $presentationType }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block"><span class="text-xs font-semibold text-slate-600">Location</span><input name="conference_location" class="outputs-edit-field mt-1.5"></label>
                            <input type="hidden" name="conference_id">
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-3">
                            <div class="outputs-edit-section-title text-xs font-semibold">Presenters</div>
                            <button type="button" class="outputs-edit-add-btn outputs-edit-add-btn--ea103" data-output-edit-add="conference_presenters">+ เพิ่มผู้นำเสนอ</button>
                        </div>
                        <div class="mt-3 space-y-2" data-output-edit-list="conference_presenters"></div>
                        <div class="mt-3 flex justify-center">
                            <button type="button" class="outputs-edit-add-btn outputs-edit-add-btn--ea103" data-output-edit-add="conference_presenters">+ เพิ่มผู้นำเสนอ</button>
                        </div>
                    </section>

                    <section class="outputs-edit-section outputs-edit-section--ea118" data-output-edit-section="EA118" hidden>
                        <div class="flex items-center justify-between gap-3">
                            <div class="outputs-edit-section-title text-sm font-semibold">EA118 Award and recognition</div>
                            <button type="button" class="outputs-edit-add-btn outputs-edit-add-btn--ea118" data-output-edit-add="award_recipients">+ เพิ่มผู้ได้รับรางวัล</button>
                        </div>
                        <div class="mt-3 space-y-2" data-output-edit-list="award_recipients"></div>
                        <div class="mt-3 flex justify-center">
                            <button type="button" class="outputs-edit-add-btn outputs-edit-add-btn--ea118" data-output-edit-add="award_recipients">+ เพิ่มผู้ได้รับรางวัล</button>
                        </div>
                    </section>

                    <section class="outputs-edit-section outputs-edit-section--ea119" data-output-edit-section="EA119" hidden>
                        <div class="outputs-edit-section-title text-sm font-semibold">EA119 Textbook / Book chapter</div>
                        <div class="mt-3 grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold text-slate-600">Book title</span><input name="book_title" class="outputs-edit-field mt-1.5"></label>
                            <label class="block"><span class="text-xs font-semibold text-slate-600">Publisher</span><input name="publisher_name" class="outputs-edit-field mt-1.5"></label>
                            <label class="block"><span class="text-xs font-semibold text-slate-600">Chapter / Pages</span><input name="chapter_pages" class="outputs-edit-field mt-1.5"></label>
                            <label class="block"><span class="text-xs font-semibold text-slate-600">ISBN</span><input name="isbn" class="outputs-edit-field mt-1.5"></label>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-3">
                            <div class="outputs-edit-section-title text-xs font-semibold">Book authors</div>
                            <button type="button" class="outputs-edit-add-btn outputs-edit-add-btn--ea119" data-output-edit-add="book_authors">+ เพิ่มผู้เขียน</button>
                        </div>
                        <div class="mt-3 space-y-2" data-output-edit-list="book_authors"></div>
                        <div class="mt-3 flex justify-center">
                            <button type="button" class="outputs-edit-add-btn outputs-edit-add-btn--ea119" data-output-edit-add="book_authors">+ เพิ่มผู้เขียน</button>
                        </div>
                    </section>
                </div>

                <div class="flex flex-col gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end">
                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" data-output-edit-close>
                        ยกเลิก
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700" data-output-edit-submit>
                        บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                exportModal: '#outputsExportModal',
                exportTitle: '#outputsExportTitle',
                exportDownload: '#outputsExportDownloadBtn',
                exportTrigger: '[data-output-export-trigger]',
                exportClose: '[data-output-export-close]',
                exportRangeFields: '#outputsExportRangeFields',
                editModal: '#outputsEditModal',
                editForm: '#outputsEditForm',
                editSubtitle: '#outputsEditSubtitle',
                editErrors: '#outputsEditErrors',
                editClose: '[data-output-edit-close]',
                editTrigger: '[data-output-edit-url]',
                editSubmit: '[data-output-edit-submit]',
            };

            let controller = null;
            let requestToken = 0;
            let debounceTimer = null;
            let isSearchComposing = false;
            let drawerOpen = false;
            let previousBodyOverflow = '';
            let exportUrls = { pdf: '', excel: '' };

            const query = (selector, root = document) => root.querySelector(selector);
            const queryAll = (selector, root = document) => Array.from(root.querySelectorAll(selector));
            const outputUserDirectory = @json(($userDirectory ?? collect())->values());
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

            const syncSearchFieldsFromInput = (sourceField) => {
                if (!sourceField || sourceField.name !== 'search') return;
                getForms().forEach((targetForm) => {
                    queryAll('input[name="search"]', targetForm).forEach((targetField) => {
                        if (targetField !== sourceField) {
                            targetField.value = sourceField.value || '';
                        }
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

            const buildExportUrl = () => {
                const form = getPrimaryForm() || getDrawerForm();
                const format = document.querySelector('input[name="output_export_format"]:checked')?.value || 'pdf';
                const exportUrl = new URL(exportUrls[format] || exportUrls.pdf, window.location.origin);
                if (form) {
                    const currentUrl = buildUrlFromForm(form);
                    currentUrl.searchParams.forEach((value, key) => {
                        if (key !== 'page') exportUrl.searchParams.append(key, value);
                    });
                }

                const scope = document.querySelector('input[name="output_export_scope"]:checked')?.value || 'all';
                exportUrl.searchParams.set('export_scope', scope);
                if (scope === 'range') {
                    const startDate = query('#outputsExportStartDate')?.value?.trim() || '';
                    const endDate = query('#outputsExportEndDate')?.value?.trim() || '';
                    if (startDate) exportUrl.searchParams.set('export_start_date', startDate);
                    if (endDate) exportUrl.searchParams.set('export_end_date', endDate);
                }

                return exportUrl;
            };

            const updateExportRangeState = () => {
                const scope = document.querySelector('input[name="output_export_scope"]:checked')?.value || 'all';
                const format = document.querySelector('input[name="output_export_format"]:checked')?.value || 'pdf';
                const rangeFields = query(selectors.exportRangeFields);
                if (rangeFields) {
                    rangeFields.classList.toggle('opacity-45', scope !== 'range');
                    rangeFields.querySelectorAll('input').forEach((input) => {
                        input.disabled = scope !== 'range';
                    });
                }
                const downloadButton = query(selectors.exportDownload);
                if (downloadButton) {
                    downloadButton.textContent = format === 'excel' ? 'Export Excel' : 'Export PDF';
                }
            };

            const openExportModal = (button) => {
                exportUrls = {
                    pdf: button.dataset.outputExportPdf || '',
                    excel: button.dataset.outputExportExcel || '',
                };
                const modal = query(selectors.exportModal);
                const title = query(selectors.exportTitle);
                if (!modal || !exportUrls.pdf) return;

                if (title) title.textContent = 'Export ข้อมูล';
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                updateExportRangeState();
                modal.querySelectorAll('[data-min-date]').forEach((input) => {
                    const minDate = input.dataset.minDate;
                    if (minDate && input._flatpickr) input._flatpickr.set('minDate', minDate);
                });
            };

            const closeExportModal = () => {
                const modal = query(selectors.exportModal);
                if (!modal) return;
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            };

            const outputTypeCode = (value) => {
                const match = String(value || '').match(/EA\d{3}/i);
                return match ? match[0].toUpperCase() : '';
            };

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[char]));

            const setNamedValue = (form, name, value) => {
                const escapedName = window.CSS?.escape ? CSS.escape(name) : name.replace(/"/g, '\\"');
                const field = form?.elements?.namedItem(name) || query(`[name="${escapedName}"]`, form);
                if (!field) return;
                field.value = value ?? '';
            };

            const setEditErrors = (message, errors = null) => {
                const box = query(selectors.editErrors);
                if (!box) return;
                const lines = [];
                if (message) lines.push(message);
                if (errors) {
                    Object.values(errors).flat().forEach((line) => lines.push(line));
                }
                box.innerHTML = lines.map((line) => `<div>${String(line)}</div>`).join('');
                box.classList.toggle('hidden', lines.length === 0);
            };

            const visibleEditTarget = (field) => {
                if (!field) return null;
                const row = field.closest('[data-output-edit-row]');
                if (field.matches('[data-output-edit-name-input]') && row) {
                    const userSelect = query('[data-output-edit-user-select]', row);
                    const userWrap = query('[data-output-edit-user-wrap]', row);
                    if (userSelect && userWrap && !userWrap.classList.contains('hidden')) {
                        return userSelect.tomselect?.control || userSelect;
                    }
                }
                if (field._flatpickr?.altInput) {
                    return field._flatpickr.altInput;
                }
                return field;
            };

            const markEditInvalid = (field) => {
                const target = visibleEditTarget(field);
                target?.classList?.add('outputs-edit-invalid');
            };

            const clearEditInvalids = (form) => {
                queryAll('.outputs-edit-invalid', form).forEach((field) => {
                    field.classList.remove('outputs-edit-invalid');
                });
            };

            const validateEditPeopleRows = (form, kind, label, options = {}) => {
                const list = query(`[data-output-edit-list="${kind}"]`, form);
                const rows = queryAll('[data-output-edit-row]', list);
                const errors = [];
                let firstInvalid = null;
                let hasAnyRowData = false;

                rows.forEach((row) => {
                    const personType = query('[data-output-edit-person-type]', row);
                    const nameInput = query('[data-output-edit-name-input]', row);
                    const userSelect = query('[data-output-edit-user-select]', row);
                    const roleSelect = query(`[name$="[role]"]`, row);
                    const isInternal = personType?.value === 'internal';
                    const nameValue = String(nameInput?.value || '').trim();
                    const userValue = String(userSelect?.value || '').trim();
                    const roleValue = String(roleSelect?.value || '').trim();
                    const rowHasData = String(personType?.value || '').trim() !== '' || nameValue !== '' || userValue !== '' || roleValue !== '';

                    if (!rowHasData) return;
                    hasAnyRowData = true;

                    if (!personType?.value) {
                        errors.push(`กรุณาเลือกประเภท${label}`);
                        markEditInvalid(personType);
                        firstInvalid ||= personType;
                    }
                    if (options.requireRole && !roleValue) {
                        errors.push(`กรุณาเลือกบทบาท${label}`);
                        markEditInvalid(roleSelect);
                        firstInvalid ||= roleSelect;
                    }
                    if ((isInternal && !userValue) || (!isInternal && nameValue === '')) {
                        errors.push(`กรุณาระบุชื่อ${label}`);
                        markEditInvalid(nameInput);
                        firstInvalid ||= visibleEditTarget(nameInput) || nameInput;
                    }
                });

                if (!hasAnyRowData) {
                    errors.push(`กรุณาเพิ่ม${label}อย่างน้อย 1 รายชื่อ`);
                    firstInvalid ||= query(`[data-output-edit-add="${kind}"]`, form);
                }

                return { errors, firstInvalid };
            };

            const validateEditForm = (form) => {
                clearEditInvalids(form);
                const errors = [];
                let firstInvalid = null;
                const source = form.dataset.source || '';
                const code = outputTypeCode(form.elements.namedItem('type')?.value);
                const requireField = (name, message) => {
                    const field = form.elements.namedItem(name);
                    if (!field || String(field.value || '').trim() !== '') return;
                    errors.push(message);
                    markEditInvalid(field);
                    firstInvalid ||= visibleEditTarget(field) || field;
                };

                requireField('title', 'กรุณากรอกชื่อผลงาน');
                requireField('type', 'กรุณาเลือกประเภทผลงาน');
                if (source === 'extra') {
                    requireField('publish_date', 'กรุณาเลือกวันที่ publish');
                    requireField('job_category', 'กรุณาเลือก Theme / Job category');
                } else {
                    requireField('year', 'กรุณาเลือกวันที่ publish');
                }

                if (code === 'EA101') {
                    requireField('journal', 'กรุณากรอก Journal');
                    const result = validateEditPeopleRows(form, 'authors', 'ผู้เขียน', { requireRole: true });
                    errors.push(...result.errors);
                    firstInvalid ||= result.firstInvalid;
                }
                if (code === 'EA103') {
                    requireField('conference_name', 'กรุณากรอกชื่อ Conference');
                    const result = validateEditPeopleRows(form, 'conference_presenters', 'ผู้นำเสนอ');
                    errors.push(...result.errors);
                    firstInvalid ||= result.firstInvalid;
                }
                if (code === 'EA118') {
                    const result = validateEditPeopleRows(form, 'award_recipients', 'ผู้ได้รับรางวัล');
                    errors.push(...result.errors);
                    firstInvalid ||= result.firstInvalid;
                }
                if (code === 'EA119') {
                    requireField('book_title', 'กรุณากรอกชื่อหนังสือ');
                    requireField('publisher_name', 'กรุณากรอกชื่อสำนักพิมพ์');
                    requireField('chapter_pages', 'กรุณากรอกชื่อบท / เลขหน้า');
                    const result = validateEditPeopleRows(form, 'book_authors', 'ผู้เขียน');
                    errors.push(...result.errors);
                    firstInvalid ||= result.firstInvalid;
                }

                if (errors.length > 0) {
                    setEditErrors('กรุณาตรวจสอบข้อมูลที่จำเป็น', [...new Set(errors)]);
                    firstInvalid?.scrollIntoView?.({ behavior: 'smooth', block: 'center' });
                    window.setTimeout(() => firstInvalid?.focus?.({ preventScroll: true }), 250);
                    return false;
                }

                return true;
            };

            const setEditLoading = (isLoading) => {
                const form = query(selectors.editForm);
                const submit = query(selectors.editSubmit);
                if (submit) submit.textContent = isLoading ? 'กำลังบันทึก...' : 'บันทึก';
                form?.querySelectorAll('input, select, textarea, button').forEach((field) => {
                    if (field.matches(selectors.editClose)) return;
                    field.disabled = isLoading;
                });
            };

            const clearEditLists = (form) => {
                queryAll('[data-output-edit-list]', form).forEach((list) => {
                    list.innerHTML = '';
                });
            };

            const personTypeOptions = (kind) => {
                const hasUnit = kind === 'award_recipients';
                return `
                    <option value="">กรุณาเลือก</option>
                    ${hasUnit ? '<option value="unit">หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>' : ''}
                    <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                    <option value="external">คนนอก</option>
                `;
            };

            const syncEditPersonRow = (row, kind) => {
                if (!row) return;
                const personType = query('[data-output-edit-person-type]', row)?.value || '';
                const nameInput = query('[data-output-edit-name-input]', row);
                const userIdInput = query('[data-output-edit-user-id]', row);
                const userSelect = query('[data-output-edit-user-select]', row);
                const manualWrap = query('[data-output-edit-manual-wrap]', row);
                const userWrap = query('[data-output-edit-user-wrap]', row);
                if (!nameInput || !userIdInput || !userSelect) return;

                const isInternal = personType === 'internal';
                const isAwardUnit = personType === 'unit' && kind === 'award_recipients';
                const useResearchName = kind === 'authors';
                manualWrap?.classList.toggle('hidden', isInternal);
                userWrap?.classList.toggle('hidden', !isInternal);
                nameInput.removeAttribute('list');
                nameInput.readOnly = isAwardUnit;
                if (isAwardUnit) {
                    nameInput.value = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
                    userIdInput.value = '';
                    if (userSelect) userSelect.value = '';
                } else if (isInternal) {
                    const selected = outputUserDirectory.find((user) => String(user.id) === String(userSelect.value || userIdInput.value || ''));
                    if (selected) {
                        userSelect.value = String(selected.id);
                        userIdInput.value = String(selected.id);
                        nameInput.value = useResearchName ? (selected.research_display_name || '') : (selected.full_name || '');
                    } else {
                        userIdInput.value = '';
                        nameInput.value = '';
                    }
                } else if (!isInternal) {
                    userIdInput.value = '';
                    if (userSelect) userSelect.value = '';
                }
            };

            const outputUserOptions = (kind, selectedId = '') => {
                const useResearchName = kind === 'authors';
                return [
                    '<option value="">พิมพ์เพื่อค้นหาชื่อ</option>',
                    ...outputUserDirectory.map((user) => {
                        const label = useResearchName ? (user.research_display_name || '') : (user.full_name || '');
                        const id = String(user.id || '');
                        if (!id || !label) return '';
                        const selected = String(selectedId || '') === id ? ' selected' : '';
                        return `<option value="${escapeHtml(id)}"${selected}>${escapeHtml(label)}</option>`;
                    }),
                ].join('');
            };

            const syncEditDatepickers = (form) => {
                const normalizePickerValue = (value) => {
                    const raw = String(value || '').trim();
                    let match = raw.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
                    if (match) {
                        return `${match[3].padStart(2, '0')}-${match[2].padStart(2, '0')}-${match[1]}`;
                    }
                    match = raw.match(/^(\d{1,2})-(\d{1,2})-(\d{4})$/);
                    if (match) {
                        return `${match[1].padStart(2, '0')}-${match[2].padStart(2, '0')}-${match[3]}`;
                    }
                    return raw;
                };

                const syncThaiAlt = (dateInput) => {
                    const fp = dateInput?._flatpickr;
                    const selected = fp?.selectedDates?.[0];
                    if (!fp?.altInput || !(selected instanceof Date) || Number.isNaN(selected.getTime())) return;
                    const day = selected.getDate();
                    const month = selected.toLocaleDateString('th-TH', { month: 'long' });
                    const year = selected.getFullYear() + 543;
                    fp.altInput.value = `${day} ${month} ${year}`;
                };

                const dateInputs = ['year', 'publish_date']
                    .map((name) => form?.elements?.namedItem(name))
                    .filter((input) => input instanceof HTMLInputElement);

                dateInputs.forEach((input) => {
                    input.value = normalizePickerValue(input.value);
                });

                if (typeof window.initDateBE === 'function') {
                    window.initDateBE(form);
                }

                dateInputs.forEach((input) => {
                    if (input._flatpickr && input.value) {
                        input._flatpickr.setDate(input.value, false, 'd-m-Y');
                        syncThaiAlt(input);
                    }
                });
            };

            const addPeopleRow = (kind, row = {}, options = {}) => {
                const { focus = false } = options;
                const form = query(selectors.editForm);
                const list = query(`[data-output-edit-list="${kind}"]`, form);
                if (!list) return;
                const index = list.children.length;
                const isAuthor = kind === 'authors';
                const isPresenter = kind === 'conference_presenters';
                const nameField = isPresenter ? 'note' : 'name';
                const label = isPresenter ? 'ชื่อผู้นำเสนอ' : (kind === 'award_recipients' ? 'ชื่อผู้ได้รับรางวัล' : 'ชื่อผู้เขียน');
                const roleSelect = isAuthor
                    ? `<label class="block"><span class="text-xs font-semibold text-slate-600">บทบาทผู้เขียน (Role)</span><select name="${kind}[${index}][role]" class="outputs-edit-field mt-1.5"><option value="">เลือกบทบาท</option><option value="first">First-author</option><option value="co">Co-author</option><option value="corresponding">Corresponding author</option></select></label>`
                    : '';
                const rowClass = isAuthor ? 'outputs-edit-person-row--author' : 'outputs-edit-person-row--plain';
                const inferredPersonType = row.person_type || (row.user_id ? 'internal' : (row.name || row.note ? 'external' : ''));

                list.insertAdjacentHTML('beforeend', `
                    <div class="outputs-edit-person-row ${rowClass} grid gap-2 rounded-xl border border-slate-200 bg-white p-3" data-output-edit-row data-output-edit-kind="${kind}">
                        <input type="hidden" name="${kind}[${index}][id]" value="">
                        <input type="hidden" name="${kind}[${index}][user_id]" value="" data-output-edit-user-id>
                        ${roleSelect}
                        <label class="block">
                            <span class="text-xs font-semibold text-slate-600">คนในหน่วย / คนนอก</span>
                            <select name="${kind}[${index}][person_type]" class="outputs-edit-field mt-1.5" data-output-edit-person-type>
                                ${personTypeOptions(kind)}
                            </select>
                        </label>
                        <div class="block">
                            <span class="text-xs font-semibold text-slate-600">${label}</span>
                            <div data-output-edit-manual-wrap>
                                <input name="${kind}[${index}][${nameField}]" class="outputs-edit-field mt-1.5" data-output-edit-name-input placeholder="${isPresenter ? 'ชื่อผู้นำเสนอ' : 'เลือกประเภทก่อน'}">
                            </div>
                            <div class="hidden" data-output-edit-user-wrap>
                                <select class="outputs-edit-field mt-1.5" data-output-edit-user-select>
                                    ${outputUserOptions(kind, row.user_id || '')}
                                </select>
                            </div>
                        </div>
                        <div class="flex items-end justify-end">
                            <button type="button" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100" data-output-edit-remove>ลบ</button>
                        </div>
                    </div>
                `);
                const created = list.lastElementChild;
                setNamedValue(created, `${kind}[${index}][id]`, row.id || '');
                setNamedValue(created, `${kind}[${index}][user_id]`, row.user_id || '');
                setNamedValue(created, `${kind}[${index}][person_type]`, inferredPersonType);
                if (isAuthor) setNamedValue(created, `${kind}[${index}][role]`, row.role || 'co');
                setNamedValue(created, `${kind}[${index}][${nameField}]`, row[nameField] || row.name || '');
                const userSelect = query('[data-output-edit-user-select]', created);
                if (userSelect) {
                    userSelect.value = row.user_id ? String(row.user_id) : '';
                    if (typeof window.ensureProjectSearchDropdown === 'function') {
                        window.ensureProjectSearchDropdown(userSelect, {
                            placeholder: 'พิมพ์เพื่อค้นหาชื่อ',
                            searchPlaceholder: 'พิมพ์เพื่อค้นหาชื่อ',
                        });
                    }
                }
                syncEditPersonRow(created, kind);
                if (focus) {
                    window.requestAnimationFrame(() => {
                        created.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        const focusTarget = query('[data-output-edit-person-type]', created)
                            || query('[data-output-edit-name-input]', created)
                            || query('input, select, textarea, button', created);
                        focusTarget?.focus({ preventScroll: true });
                    });
                }
            };

            const updateEditSpecialSections = () => {
                const form = query(selectors.editForm);
                if (!form) return;
                const code = outputTypeCode(form.elements.namedItem('type')?.value);
                queryAll('[data-output-edit-section]', form).forEach((section) => {
                    section.hidden = section.dataset.outputEditSection !== code;
                });
            };

            const fillEditForm = (payload) => {
                const form = query(selectors.editForm);
                const output = payload.output || {};
                if (!form) return;

                form.reset();
                clearEditLists(form);
                form.action = payload.update_url;
                form.dataset.source = payload.source || '';
                setEditErrors('');

                query(selectors.editSubtitle).textContent = payload.source === 'extra'
                    ? 'แก้รายการ Extra โดยตรงจากหน้า overview'
                    : (payload.source === 'mission' ? 'แก้รายการ Mission โดยตรงจากหน้า overview' : 'แก้รายการ Project output โดยตรงจากหน้า overview');

                const isExtra = payload.source === 'extra';
                query('[data-output-edit-year-wrap]', form)?.classList.toggle('hidden', isExtra);
                query('[data-output-edit-publish-wrap]', form)?.classList.toggle('hidden', !isExtra);
                query('[data-output-edit-job-wrap]', form)?.classList.toggle('hidden', !isExtra);
                form.elements.namedItem('project_output')?.classList.toggle('hidden', isExtra);
                form.elements.namedItem('note')?.classList.toggle('hidden', !isExtra);

                const typeSelect = form.elements.namedItem('type');
                if (typeSelect && output.type && !Array.from(typeSelect.options).some((option) => option.value === output.type)) {
                    typeSelect.insertAdjacentHTML('afterbegin', `<option value="${escapeHtml(output.type)}">${escapeHtml(output.type)}</option>`);
                }

                ['title', 'type', 'year', 'publish_date', 'job_category', 'project_output', 'note', 'journal', 'quartile', 'impact_factor', 'book_title', 'publisher_name', 'chapter_pages', 'isbn'].forEach((name) => {
                    setNamedValue(form, name, output[name] || '');
                });

                if (isExtra && !output.note && output.project_output) setNamedValue(form, 'note', output.project_output);
                if (!isExtra && !output.project_output && output.note) setNamedValue(form, 'project_output', output.note);

                (output.authors || []).forEach((row) => addPeopleRow('authors', row));
                (output.award_recipients || []).forEach((row) => addPeopleRow('award_recipients', row));
                (output.book_authors || []).forEach((row) => addPeopleRow('book_authors', row));
                const conference = output.conference || {};
                setNamedValue(form, 'conference_id', conference.id || '');
                setNamedValue(form, 'conference_name', conference.name || '');
                setNamedValue(form, 'conference_presentation_type', conference.presentation_type || '');
                setNamedValue(form, 'conference_location', conference.location || '');
                (conference.presenters || []).forEach((row) => addPeopleRow('conference_presenters', row));

                updateEditSpecialSections();
                syncEditDatepickers(form);
            };

            const openEditModal = async (url) => {
                const modal = query(selectors.editModal);
                const subtitle = query(selectors.editSubtitle);
                if (!modal || !url) return;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                if (subtitle) subtitle.textContent = 'กำลังโหลดข้อมูล...';
                setEditErrors('');

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    });
                    const payload = await response.json();
                    if (!response.ok || !payload.ok) throw new Error(payload.message || `HTTP ${response.status}`);
                    fillEditForm(payload);
                } catch (error) {
                    setEditErrors(error.message || 'โหลดข้อมูลไม่สำเร็จ');
                }
            };

            const closeEditModal = () => {
                const modal = query(selectors.editModal);
                if (!modal) return;
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                setEditLoading(false);
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
                                if (isSearchComposing) return;
                                if (controller) controller.abort();
                                syncSearchFieldsFromInput(field);
                                updateFilterBadges();
                                submitDebounced(300);
                            });
                            field.addEventListener('keydown', (event) => {
                                if (event.key === 'Enter') {
                                    event.preventDefault();
                                    window.clearTimeout(debounceTimer);
                                    syncFormsFromSource(form);
                                    const primaryForm = getPrimaryForm() || form;
                                    fetchAndRender(buildUrlFromForm(primaryForm), { updateUrl: true });
                                }
                            });
                            field.addEventListener('compositionstart', () => {
                                isSearchComposing = true;
                            });
                            field.addEventListener('compositionend', () => {
                                isSearchComposing = false;
                                if (controller) controller.abort();
                                syncSearchFieldsFromInput(field);
                                updateFilterBadges();
                                submitDebounced(300);
                            });
                            field.addEventListener('change', () => {
                                syncSearchFieldsFromInput(field);
                                updateFilterBadges();
                                submitDebounced(180);
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

            const bindExportModal = () => {
                if (window.__outputsExportModalBound) return;
                window.__outputsExportModalBound = true;

                document.addEventListener('click', (event) => {
                    const trigger = event.target.closest(selectors.exportTrigger);
                    if (trigger) {
                        event.preventDefault();
                        openExportModal(trigger);
                        return;
                    }

                    if (event.target.closest(selectors.exportClose)) {
                        event.preventDefault();
                        closeExportModal();
                    }
                });

                query(selectors.exportModal)?.addEventListener('click', (event) => {
                    if (event.target === event.currentTarget) {
                        closeExportModal();
                    }
                });

                document.querySelectorAll('input[name="output_export_scope"]').forEach((input) => {
                    input.addEventListener('change', updateExportRangeState);
                });
                document.querySelectorAll('input[name="output_export_format"]').forEach((input) => {
                    input.addEventListener('change', updateExportRangeState);
                });

                query(selectors.exportDownload)?.addEventListener('click', () => {
                    window.open(buildExportUrl().toString(), '_blank', 'noopener');
                    closeExportModal();
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeExportModal();
                    }
                });
            };

            const bindEditModal = () => {
                if (window.__outputsEditModalBound) return;
                window.__outputsEditModalBound = true;

                document.addEventListener('click', (event) => {
                    const trigger = event.target.closest(selectors.editTrigger);
                    if (trigger) {
                        const url = trigger.dataset.outputEditUrl || '';
                        if (!url) return;
                        event.preventDefault();
                        openEditModal(url);
                        return;
                    }

                    if (event.target.closest(selectors.editClose)) {
                        event.preventDefault();
                        closeEditModal();
                        return;
                    }

                    const addButton = event.target.closest('[data-output-edit-add]');
                    if (addButton) {
                        event.preventDefault();
                        addPeopleRow(addButton.dataset.outputEditAdd, {}, { focus: true });
                        return;
                    }

                    const removeButton = event.target.closest('[data-output-edit-remove]');
                    if (removeButton) {
                        event.preventDefault();
                        removeButton.closest('[data-output-edit-row]')?.remove();
                        return;
                    }

                    const personType = event.target.closest('[data-output-edit-person-type]');
                    if (personType) {
                        const row = personType.closest('[data-output-edit-row]');
                        syncEditPersonRow(row, row?.dataset.outputEditKind || '');
                    }
                });

                query(selectors.editModal)?.addEventListener('click', (event) => {
                    if (event.target === event.currentTarget) {
                        closeEditModal();
                    }
                });

                query(selectors.editForm)?.addEventListener('change', (event) => {
                    if (event.target.matches('[data-output-edit-type]')) {
                        updateEditSpecialSections();
                    }
                    if (event.target.matches('[data-output-edit-person-type], [data-output-edit-user-select]')) {
                        const row = event.target.closest('[data-output-edit-row]');
                        syncEditPersonRow(row, row?.dataset.outputEditKind || '');
                    }
                });

                query(selectors.editForm)?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    if (!form.action) return;
                    setEditErrors('');
                    if (!validateEditForm(form)) return;
                    if (typeof window.syncDateInputsBeforeSubmit === 'function') {
                        window.syncDateInputsBeforeSubmit(form);
                    }
                    setEditLoading(true);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                            body: new FormData(form),
                        });
                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok || payload.ok === false) {
                            setEditErrors(payload.message || 'บันทึกไม่สำเร็จ', payload.errors || null);
                            return;
                        }

                        closeEditModal();
                        const currentUrl = new URL(window.location.href);
                        fetchAndRender(currentUrl, { updateUrl: false });
                    } catch (error) {
                        setEditErrors(error.message || 'บันทึกไม่สำเร็จ');
                    } finally {
                        setEditLoading(false);
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && query(selectors.editModal)?.classList.contains('is-open')) {
                        closeEditModal();
                    }
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
                bindExportModal();
                bindEditModal();
                updateFilterBadges();
                updateExportRangeState();
                syncFabVisibility();
                window.addEventListener('scroll', syncFabVisibility, { passive: true });
                window.addEventListener('resize', syncFabVisibility);
            };

            document.addEventListener('DOMContentLoaded', initOutputsPage);
        })();
    </script>
@endsection

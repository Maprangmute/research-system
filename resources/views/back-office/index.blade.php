@extends('layouts.app')

@section('content')
    @php
        $activeFilterCount = collect(request()->query())
            ->reject(fn($value, $key) => in_array($key, ['page', 'sort'], true))
            ->filter(fn($value) => filled($value))
            ->count();

        $activeFilterLabels = [];

        if ($searchValue = trim((string) request('search'))) {
            $activeFilterLabels[] = 'ค้นหา: ' . $searchValue;
        }

        if ($creatorId = request('creator')) {
            $creator = $creatorOptions->firstWhere('id', (int) $creatorId);
            if ($creator) {
                $activeFilterLabels[] = 'ผู้บันทึก: ' . trim(($creator->prefix ? $creator->prefix . ' ' : '') . $creator->name);
            }
        }

        if ($person = trim((string) request('person'))) {
            $activeFilterLabels[] = 'ผู้ปฏิบัติงาน: ' . $person;
        }

        if ($category = trim((string) request('category'))) {
            $activeFilterLabels[] = 'ประเภท: ' . $category;
        }

        if ($withOutput = request('with_output')) {
            $activeFilterLabels[] = $withOutput === 'yes' ? 'สถานะ: มีผลงาน' : 'สถานะ: ยังไม่มีผลงาน';
        }

        if ($start = trim((string) request('start'))) {
            $activeFilterLabels[] = 'เริ่ม: ' . $start;
        }

        if ($end = trim((string) request('end'))) {
            $activeFilterLabels[] = 'สิ้นสุด: ' . $end;
        }
    @endphp

    <style>
        .back-office-shell {
            background: transparent;
        }

        .back-office-panel {
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.07);
        }

        .back-office-hero {
            overflow: hidden;
            position: relative;
        }

        .back-office-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 30%),
                radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.12), transparent 24%);
            pointer-events: none;
        }

        .back-office-filter-panel {
            position: relative;
            overflow: hidden;
        }

        .back-office-filter-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(135deg, rgba(14, 165, 233, 0.06), transparent 30%),
                radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.08), transparent 28%);
            pointer-events: none;
        }

        .back-office-input,
        .back-office-select {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            padding: 0.64rem 0.78rem;
            font-size: 0.9rem;
            color: #0f172a;
            background: #ffffff;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .back-office-input:focus,
        .back-office-select:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 4px rgba(125, 211, 252, 0.25);
        }

        .back-office-stat {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }

        .back-office-task-card {
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
        }

        .back-office-task-meta {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
        }

        .back-office-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
        }

        .back-office-table {
            width: 100%;
            min-width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #ffffff;
        }

        .back-office-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc;
            color: #475569;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.64rem 0.74rem;
            white-space: nowrap;
        }

        .back-office-table tbody td {
            border-bottom: 1px solid #e2e8f0;
            padding: 0.66rem 0.74rem;
            vertical-align: top;
        }

        .back-office-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .back-office-table tbody tr:nth-child(even) td {
            background: #fcfdff;
        }

        .back-office-table tbody tr:hover td {
            background: #f0f9ff;
        }

        @media (min-width: 1280px) {
            .back-office-table {
                min-width: 1320px;
            }
        }

        .back-office-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 0.45rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
            line-height: 1;
        }

        .back-office-loading-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 999px;
            background: #ecfeff;
            padding: 0.5rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #0f766e;
        }

        .back-office-loading-indicator__dot {
            height: 0.55rem;
            width: 0.55rem;
            border-radius: 999px;
            background: currentColor;
            animation: backOfficePulse 1s ease-in-out infinite;
        }

        .back-office-search-shell {
            position: relative;
        }

        .back-office-search-icon {
            position: absolute;
            left: 0.95rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            pointer-events: none;
        }

        .back-office-search-input {
            padding-left: 2.85rem;
        }

        .back-office-filter-note {
            font-size: 0.78rem;
            color: #64748b;
        }

        .back-office-filter-details > summary {
            list-style: none;
        }

        .back-office-filter-details > summary::-webkit-details-marker {
            display: none;
        }

        .back-office-filter-chevron {
            transition: transform 0.18s ease;
        }

        .back-office-filter-details[open] .back-office-filter-chevron {
            transform: rotate(180deg);
        }

        @keyframes backOfficePulse {
            0%, 100% {
                opacity: 0.35;
                transform: scale(0.92);
            }

            50% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .back-office-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            padding: 0.56rem 0.84rem;
            font-size: 0.84rem;
            font-weight: 600;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .back-office-action:hover {
            transform: translateY(-1px);
        }

        .back-office-action-cluster {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            width: 100%;
        }

        .back-office-mobile-list {
            display: grid;
            gap: 0.75rem;
        }

        .back-office-mobile-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
            padding: 0.8rem;
        }

        .back-office-mobile-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.45rem;
            margin-top: 0.6rem;
        }

        .back-office-mobile-meta {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            padding: 0.45rem 0.55rem;
        }

        .back-office-mobile-meta__label {
            font-size: 0.66rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }

        .back-office-mobile-meta__value {
            margin-top: 0.2rem;
            font-size: 0.86rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .back-office-action-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 33px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            padding: 0.38rem 0.7rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: #1e3a5f;
            transition: background-color 0.16s ease, border-color 0.16s ease, transform 0.16s ease;
        }

        .back-office-action-primary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            transform: translateY(-1px);
        }

        .back-office-action-danger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 33px;
            border-radius: 10px;
            border: 1px solid #fecdd3;
            background: #fff1f2;
            padding: 0.38rem 0.7rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: #be123c;
            transition: background-color 0.16s ease, border-color 0.16s ease, transform 0.16s ease;
        }

        .back-office-action-danger:hover {
            background: #ffe4e6;
            border-color: #fda4af;
            transform: translateY(-1px);
        }

        .back-office-action-menu {
            position: relative;
        }

        .back-office-action-menu > summary {
            list-style: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 33px;
            height: 33px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            cursor: pointer;
            font-size: 1rem;
            line-height: 1;
            transition: background-color 0.16s ease, border-color 0.16s ease;
        }

        .back-office-action-menu > summary::-webkit-details-marker {
            display: none;
        }

        .back-office-action-menu > summary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        .back-office-action-menu[open] > summary {
            border-color: #64748b;
            background: #f1f5f9;
        }

        .back-office-action-menu__panel {
            position: absolute;
            right: 0;
            top: calc(100% + 0.35rem);
            z-index: 12;
            min-width: 142px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.16);
            padding: 0.3rem;
        }

        .back-office-action-menu__item {
            width: 100%;
            display: inline-flex;
            align-items: center;
            border-radius: 8px;
            padding: 0.42rem 0.6rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: #334155;
            text-align: left;
            border: 0;
            background: transparent;
        }

        .back-office-action-menu__item:hover {
            background: #f8fafc;
        }

        .back-office-action-menu__item--danger {
            color: #be123c;
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

        .back-office-link-button {
            appearance: none;
            border: 0;
            background: transparent;
            padding: 0;
            text-align: left;
            cursor: pointer;
        }

        .back-office-drawer-overlay {
            position: fixed;
            inset: 0;
            z-index: 58;
            background: rgba(15, 23, 42, 0.16);
            backdrop-filter: blur(3px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.22s ease;
        }

        .back-office-drawer-overlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .back-office-drawer {
            position: fixed;
            inset: 0 0 0 auto;
            z-index: 59;
            width: min(96vw, 980px);
            height: 100dvh;
            transform: translateX(104%);
            transition: transform 0.24s ease;
        }

        .back-office-drawer.is-open {
            transform: translateX(0);
        }

        .back-office-drawer-shell {
            display: flex;
            height: 100%;
            flex-direction: column;
            border-left: 1px solid #e2e8f0;
            background: #f8fbff;
            box-shadow: -18px 0 48px rgba(15, 23, 42, 0.16);
        }

        .back-office-drawer-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1px solid #e2e8f0;
            background: rgba(255, 255, 255, 0.94);
            padding: 1rem 1.1rem;
            backdrop-filter: blur(12px);
        }

        .back-office-drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .back-office-drawer-loading {
            display: flex;
            min-height: 220px;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 0.95rem;
        }

        .back-office-drawer-close {
            display: inline-flex;
            height: 2.75rem;
            width: 2.75rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #475569;
            transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
        }

        .back-office-drawer-close:hover {
            transform: rotate(90deg);
            background: #f8fafc;
            color: #0f172a;
        }

        .back-office-filter-drawer-overlay {
            position: fixed;
            inset: 0;
            z-index: 46;
            background: rgba(15, 23, 42, 0.28);
            backdrop-filter: blur(6px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.18s ease;
        }

        .back-office-filter-drawer-overlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .back-office-filter-drawer {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 47;
            width: min(92vw, 400px);
            transform: translateX(-100%);
            transition: transform 0.22s ease;
        }

        .back-office-filter-drawer.is-open {
            transform: translateX(0);
        }

        .back-office-filter-fab {
            position: fixed;
            left: 16px;
            bottom: calc(16px + env(safe-area-inset-bottom, 0px));
            z-index: 35;
            width: 72px;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            padding: 0.72rem 0.5rem;
            border-radius: 22px;
            border: 1px solid #dbeafe;
            background: rgba(255, 255, 255, 0.94);
            color: #0f172a;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.14);
            backdrop-filter: blur(12px);
            opacity: 0;
            transform: translateY(18px);
            pointer-events: none;
            transition: opacity 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .back-office-filter-fab.is-visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .back-office-filter-fab.is-visible:hover {
            transform: translateY(-2px);
            border-color: #7dd3fc;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.16);
        }

        .back-office-filter-fab:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.18), 0 20px 40px rgba(15, 23, 42, 0.16);
        }

        .back-office-filter-fab__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0f172a 0%, #075985 100%);
            color: #ffffff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
        }

        .back-office-filter-fab__title {
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
        }

        .back-office-filter-fab__meta {
            font-size: 0.6rem;
            color: #64748b;
            line-height: 1.1;
            text-align: center;
        }

        .back-office-filter-fab__badge {
            position: absolute;
            top: -0.32rem;
            right: -0.32rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.4rem;
            height: 1.4rem;
            padding: 0 0.3rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
            border: 2px solid #fff;
            color: #ffffff;
            font-size: 0.66rem;
            font-weight: 800;
            line-height: 1;
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
        }

        .back-office-filter-fab__badge.hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .back-office-panel,
            .back-office-task-card,
            .back-office-stat {
                border-radius: 16px;
            }

            .back-office-input,
            .back-office-select {
                border-radius: 12px;
                padding: 0.56rem 0.68rem;
                font-size: 0.85rem;
            }

            .back-office-action {
                border-radius: 10px;
                padding: 0.5rem 0.7rem;
                font-size: 0.78rem;
            }

            .back-office-action-cluster {
                justify-content: flex-start;
                flex-wrap: wrap;
                gap: 0.38rem;
            }

            .back-office-action-primary {
                min-height: 31px;
                padding: 0.34rem 0.58rem;
                font-size: 0.74rem;
            }

            .back-office-action-menu > summary {
                width: 31px;
                height: 31px;
                border-radius: 9px;
            }

            .back-office-action-menu__panel {
                left: 0;
                right: auto;
            }

            .back-office-drawer {
                width: 100vw;
            }

            .back-office-drawer-body {
                padding: 0.85rem;
            }

            .back-office-filter-fab {
                width: 58px;
                min-height: 58px;
                padding: 8px;
                border-radius: 18px;
                gap: 0;
            }

            .back-office-filter-fab__icon {
                width: 34px;
                height: 34px;
                border-radius: 14px;
            }

            .back-office-filter-fab__title,
            .back-office-filter-fab__meta {
                display: none;
            }

            .back-office-filter-fab__badge {
                min-width: 1.2rem;
                height: 1.2rem;
                font-size: 0.62rem;
            }
        }
    </style>

    <div class="back-office-shell min-h-screen">
        <div class="container mx-auto mt-7 space-y-5 px-4 pb-6 md:mt-9 md:space-y-6 md:pb-8">
            <section class="back-office-panel back-office-hero">
                <div class="relative flex flex-col gap-4 px-4 py-4 md:px-6 md:py-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sky-700">Back Office Workspace</p>
                        <h1 class="mt-1.5 text-xl font-bold tracking-tight text-slate-900 md:text-2xl">🧾 งานนอกโครงการ</h1>
                        <p class="mt-2 text-sm leading-5 text-slate-600 md:text-sm">
                            จัดการงานภายในที่ไม่ผูกกับโครงการวิจัย
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row">
                        <a href="{{ route('fte.index') }}"
                            class="back-office-action border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                            📈 ดูสรุป FTE
                        </a>
                        <a href="{{ route('back_office.create') }}"
                            class="back-office-action bg-emerald-600 text-white shadow-sm hover:bg-emerald-700">
                            ➕ เพิ่มงาน
                        </a>
                    </div>
                </div>
            </section>

            <section id="backOfficeFilterAnchor" class="back-office-panel back-office-filter-panel p-4 md:p-5">
                <div class="relative">
                    <details id="backOfficeFilterDetails" class="back-office-filter-details">
                        <summary class="flex cursor-pointer flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div class="max-w-3xl">
                                <h2 class="text-base font-semibold text-slate-900">ตัวกรอง</h2>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 md:justify-end">
                                <div id="backOfficeLoadingIndicator" class="hidden"></div>
                                <span class="back-office-chip bg-sky-50 text-sky-800">
                                    ฟิลเตอร์ที่ใช้อยู่
                                    <span id="backOfficeActiveFilterCount" class="font-extrabold text-slate-900">{{ $activeFilterCount }}</span>
                                </span>
                                <span class="back-office-filter-chevron inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                        </summary>

                        <div class="mt-3 border-t border-slate-100 pt-3">
                            <div class="mb-3 flex flex-wrap gap-1.5">
                                @forelse ($activeFilterLabels as $label)
                                    <span class="back-office-chip">{{ $label }}</span>
                                @empty
                                    <span class="back-office-chip bg-slate-50 text-slate-500">ยังไม่ได้ใช้ฟิลเตอร์เพิ่มเติม</span>
                                @endforelse
                            </div>

                            <form id="backOfficeFilterForm" method="GET" action="{{ route('back_office.index') }}" class="space-y-3">
                                <div class="grid grid-cols-1 gap-2.5 xl:grid-cols-12">
                                    <label class="block xl:col-span-5">
                                        <span class="text-xs font-semibold text-slate-600">ค้นหางาน</span>
                                        <div class="back-office-search-shell mt-1">
                                            <span class="back-office-search-icon" aria-hidden="true">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                    <path d="M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Zm10 2-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                            <input type="text" name="search" value="{{ request('search') }}"
                                                placeholder="ชื่องาน / รายละเอียด / จุดประสงค์ / ชื่อคน / ผลงาน"
                                                class="back-office-input back-office-search-input" data-back-office-live="text">
                                        </div>
                                    </label>

                                    <label class="block sm:col-span-1 xl:col-span-2">
                                        <span class="text-xs font-semibold text-slate-600">ผู้บันทึก</span>
                                        <select name="creator" class="back-office-select mt-1" data-back-office-live="change">
                                            <option value="">ทั้งหมด</option>
                                            @foreach ($creatorOptions as $creator)
                                                <option value="{{ $creator->id }}" @selected((string) request('creator') === (string) $creator->id)>
                                                    {{ trim(($creator->prefix ? $creator->prefix . ' ' : '') . $creator->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label class="block sm:col-span-1 xl:col-span-2">
                                        <span class="text-xs font-semibold text-slate-600">ผู้ปฏิบัติงาน</span>
                                        <select name="person" class="back-office-select mt-1" data-back-office-live="change">
                                            <option value="">ทั้งหมด</option>
                                            @foreach ($personList as $person)
                                                <option value="{{ $person }}" @selected(request('person') === $person)>{{ $person }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label class="block sm:col-span-2 xl:col-span-3">
                                        <span class="text-xs font-semibold text-slate-600">ประเภทงาน</span>
                                        <select name="category" class="back-office-select mt-1" data-back-office-live="change">
                                            <option value="">ทั้งหมด</option>
                                            @foreach ($categoryOptions as $option)
                                                <option value="{{ $option }}" @selected(request('category') === $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label class="block sm:col-span-1 xl:col-span-2">
                                        <span class="text-xs font-semibold text-slate-600">สถานะผลงาน</span>
                                        <select name="with_output" class="back-office-select mt-1" data-back-office-live="change">
                                            <option value="">ทั้งหมด</option>
                                            <option value="yes" @selected(request('with_output') === 'yes')>มีผลงาน</option>
                                            <option value="no" @selected(request('with_output') === 'no')>ยังไม่มีผลงาน</option>
                                        </select>
                                    </label>

                                    <label class="block sm:col-span-1 xl:col-span-2">
                                        <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                                        <select name="sort" class="back-office-select mt-1" data-back-office-live="change">
                                            <option value="latest" @selected($currentSort === 'latest')>บันทึกล่าสุด</option>
                                            <option value="oldest" @selected($currentSort === 'oldest')>เก่าสุด</option>
                                            <option value="hours" @selected($currentSort === 'hours')>ชั่วโมงมากที่สุด</option>
                                            <option value="fte" @selected($currentSort === 'fte')>FTE มากที่สุด</option>
                                            <option value="outputs" @selected($currentSort === 'outputs')>ผลงานมากที่สุด</option>
                                            <option value="title" @selected($currentSort === 'title')>ชื่องาน A-Z</option>
                                        </select>
                                    </label>

                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:col-span-2 xl:col-span-5">
                                        <label class="block">
                                            <span class="text-xs font-semibold text-slate-600">ช่วงวันที่เริ่ม</span>
                                            <input type="text" name="start" value="{{ request('start') }}"
                                                class="back-office-input js-date-be mt-1" data-back-office-live="change"
                                                placeholder="วันเริ่มต้น" autocomplete="off">
                                        </label>

                                        <label class="block">
                                            <span class="text-xs font-semibold text-slate-600">ช่วงวันที่สิ้นสุด</span>
                                            <input type="text" name="end" value="{{ request('end') }}"
                                                class="back-office-input js-date-be mt-1" data-back-office-live="change"
                                                placeholder="วันสิ้นสุด" autocomplete="off">
                                        </label>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-2.5">
                                    <p class="back-office-filter-note">พิมพ์หรือเลือกค่าแล้วระบบจะอัปเดตรายการ สรุปตัวเลข และ pagination โดยไม่ reload ทั้งหน้า</p>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('back_office.index') }}" data-back-office-reset
                                            class="back-office-action border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                                            ล้างฟิลเตอร์
                                        </a>
                                        <button type="submit"
                                            class="back-office-action border border-sky-200 bg-sky-50 px-4 py-2.5 text-sm text-sky-700 hover:bg-sky-100">
                                            อัปเดตทันที
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </details>
                </div>
            </section>

            @include('back-office.partials.index-content', [
                'summary' => $summary,
                'tasks' => $tasks,
                'currentSortLabel' => $currentSortLabel,
            ])
        </div>
    </div>

    <div id="backOfficeFilterDrawerOverlay" class="back-office-filter-drawer-overlay"></div>
    <aside id="backOfficeFilterDrawer" class="back-office-filter-drawer" aria-hidden="true">
        <div class="flex h-full flex-col border-r border-slate-200 bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-4 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">คัดกรองข้อมูล</h3>
                    <p class="mt-1 text-xs text-slate-500">ค่าที่เลือกจะถูกใช้เป็นฟิลเตอร์หลักของหน้าและอัปเดตผลลัพธ์ทันที</p>
                </div>
                <button id="backOfficeFilterDrawerClose" type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                    <span class="sr-only">ปิดฟิลเตอร์</span>
                    <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                </button>
            </div>

            <form id="backOfficeDrawerFilterForm" method="GET" action="{{ route('back_office.index') }}" class="flex h-full flex-col">
                <div class="flex-1 space-y-4 overflow-y-auto px-4 py-4">
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ค้นหางาน</span>
                        <div class="back-office-search-shell mt-1">
                            <span class="back-office-search-icon" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path d="M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Zm10 2-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <input id="backOfficeDrawerSearchInput" type="text" name="search" value="{{ request('search') }}"
                                class="back-office-input back-office-search-input" data-back-office-live="text"
                                placeholder="ชื่องาน / รายละเอียด / จุดประสงค์ / ชื่อคน / ผลงาน">
                        </div>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ผู้บันทึก</span>
                        <select name="creator" class="back-office-select mt-1" data-back-office-live="change">
                            <option value="">ทั้งหมด</option>
                            @foreach ($creatorOptions as $creator)
                                <option value="{{ $creator->id }}" @selected((string) request('creator') === (string) $creator->id)>
                                    {{ trim(($creator->prefix ? $creator->prefix . ' ' : '') . $creator->name) }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ผู้ปฏิบัติงาน</span>
                        <select name="person" class="back-office-select mt-1" data-back-office-live="change">
                            <option value="">ทั้งหมด</option>
                            @foreach ($personList as $person)
                                <option value="{{ $person }}" @selected(request('person') === $person)>{{ $person }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ประเภทงาน</span>
                        <select name="category" class="back-office-select mt-1" data-back-office-live="change">
                            <option value="">ทั้งหมด</option>
                            @foreach ($categoryOptions as $option)
                                <option value="{{ $option }}" @selected(request('category') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">สถานะผลงาน</span>
                        <select name="with_output" class="back-office-select mt-1" data-back-office-live="change">
                            <option value="">ทั้งหมด</option>
                            <option value="yes" @selected(request('with_output') === 'yes')>มีผลงาน</option>
                            <option value="no" @selected(request('with_output') === 'no')>ยังไม่มีผลงาน</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                        <select name="sort" class="back-office-select mt-1" data-back-office-live="change">
                            <option value="latest" @selected($currentSort === 'latest')>บันทึกล่าสุด</option>
                            <option value="oldest" @selected($currentSort === 'oldest')>เก่าสุด</option>
                            <option value="hours" @selected($currentSort === 'hours')>ชั่วโมงมากที่สุด</option>
                            <option value="fte" @selected($currentSort === 'fte')>FTE มากที่สุด</option>
                            <option value="outputs" @selected($currentSort === 'outputs')>ผลงานมากที่สุด</option>
                            <option value="title" @selected($currentSort === 'title')>ชื่องาน A-Z</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ช่วงวันที่เริ่ม</span>
                        <input type="text" name="start" value="{{ request('start') }}"
                            class="back-office-input js-date-be mt-1" data-back-office-live="change"
                            placeholder="วันเริ่มต้น" autocomplete="off">
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ช่วงวันที่สิ้นสุด</span>
                        <input type="text" name="end" value="{{ request('end') }}"
                            class="back-office-input js-date-be mt-1" data-back-office-live="change"
                            placeholder="วันสิ้นสุด" autocomplete="off">
                    </label>

                    <p class="back-office-filter-note">
                        พิมพ์หรือเลือกค่าแล้วระบบจะอัปเดตรายการ สรุปตัวเลข และ pagination โดยไม่ reload ทั้งหน้า
                    </p>
                </div>

                <div class="border-t border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit"
                            class="flex-1 rounded-xl bg-sky-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-sky-700">
                            ใช้ตัวกรอง
                        </button>
                        <a href="{{ route('back_office.index') }}" data-back-office-reset
                            class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            ล้าง
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </aside>

    <button id="backOfficeFilterFab" type="button" aria-label="เปิดฟิลเตอร์" class="back-office-filter-fab" data-back-office-open-filter>
        <span class="back-office-filter-fab__icon" aria-hidden="true">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                <path d="M4 6h16M7 12h10m-7 6h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </span>
        <span class="back-office-filter-fab__title">ฟิลเตอร์</span>
        <span class="back-office-filter-fab__meta">เปิดแผง</span>
        <span id="backOfficeFilterFabBadge" class="back-office-filter-fab__badge @if ($activeFilterCount === 0) hidden @endif">{{ $activeFilterCount }}</span>
    </button>

    <div id="backOfficeDrawerOverlay" class="back-office-drawer-overlay"></div>
    <aside id="backOfficeDrawer" class="back-office-drawer" aria-hidden="true">
        <div class="back-office-drawer-shell">
            <div class="back-office-drawer-head">
                <div>
                    <div class="text-sm font-semibold text-slate-900">รายละเอียดงาน</div>
                    <div class="mt-1 text-xs text-slate-500">กดพื้นหลังหรือปุ่มปิดเพื่อออกจากลิ้นชัก</div>
                </div>
                <button id="backOfficeDrawerClose" type="button" class="back-office-drawer-close">
                    <span class="sr-only">ปิดรายละเอียด</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 6l12 12M18 6l-12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
            <div id="backOfficeDrawerBody" class="back-office-drawer-body">
                <div class="back-office-drawer-loading">กำลังโหลดข้อมูล...</div>
            </div>
        </div>
    </aside>

    <script>
        (() => {
            'use strict';

            const selectors = {
                content: '#backOfficeIndexContent',
                statsGrid: '#backOfficeStatsGrid',
                resultsShell: '#backOfficeResultsShell',
                loading: '#backOfficeLoadingIndicator',
                activeFilterCount: '#backOfficeActiveFilterCount',
                primaryForm: '#backOfficeFilterForm',
                drawerForm: '#backOfficeDrawerFilterForm',
                drawerSearchInput: '#backOfficeDrawerSearchInput',
                filterAnchor: '#backOfficeFilterAnchor',
                filterDrawer: '#backOfficeFilterDrawer',
                filterDrawerOverlay: '#backOfficeFilterDrawerOverlay',
                filterDrawerClose: '#backOfficeFilterDrawerClose',
                openFilterDrawer: '[data-back-office-open-filter]',
                filterFab: '#backOfficeFilterFab',
                filterFabBadge: '#backOfficeFilterFabBadge',
                detailOverlay: '#backOfficeDrawerOverlay',
                detailDrawer: '#backOfficeDrawer',
                detailDrawerClose: '#backOfficeDrawerClose',
                detailDrawerBody: '#backOfficeDrawerBody',
            };

            const query = (selector, root = document) => root.querySelector(selector);
            const queryAll = (selector, root = document) => Array.from(root.querySelectorAll(selector));
            const currentPath = window.location.pathname;
            const getPrimaryForm = () => query(selectors.primaryForm);
            const getFilterDrawerForm = () => query(selectors.drawerForm);
            const getForms = () => [getPrimaryForm(), getFilterDrawerForm()].filter(Boolean);
            const loadingIndicator = query(selectors.loading);

            let resultController = null;
            let detailController = null;
            let requestToken = 0;
            let debounceTimer = null;
            let isSearchComposing = false;
            let isSyncingForms = false;
            let filterDrawerOpen = false;
            let detailDrawerOpen = false;
            let bodyLockCount = 0;
            let bodyOverflowValue = '';
            let baseResultsCountText = '';
            let baseResultsMetaHtml = '';

            const lockBody = () => {
                if (bodyLockCount === 0) {
                    bodyOverflowValue = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                }
                bodyLockCount += 1;
            };

            const unlockBody = () => {
                bodyLockCount = Math.max(0, bodyLockCount - 1);
                if (bodyLockCount === 0) {
                    document.body.style.overflow = bodyOverflowValue;
                }
            };

            const countActiveFilters = (form) => {
                if (!form) return 0;
                return queryAll('[name]', form).reduce((count, field) => {
                    if (!field.name || ['page', 'sort'].includes(field.name)) return count;
                    return String(field.value ?? '').trim() !== '' ? count + 1 : count;
                }, 0);
            };

            const updateFilterBadges = () => {
                const form = getPrimaryForm() || getFilterDrawerForm();
                const count = countActiveFilters(form);
                const stat = query(selectors.activeFilterCount);
                const fabBadge = query(selectors.filterFabBadge);

                if (stat) stat.textContent = String(count);
                if (fabBadge) {
                    fabBadge.textContent = String(count);
                    fabBadge.classList.toggle('hidden', count === 0);
                }
            };

            const syncFabVisibility = () => {
                const fab = query(selectors.filterFab);
                const anchor = query(selectors.filterAnchor);
                if (!fab || !anchor) return;

                const rect = anchor.getBoundingClientRect();
                fab.classList.toggle('is-visible', rect.bottom < 48);
            };

            const normalizeSearchTerm = (value) => String(value || '')
                .toLowerCase()
                .normalize('NFKC')
                .replace(/\s+/g, ' ')
                .trim();

            const captureResultsMeta = () => {
                const countEl = query('#backOfficeResultsCount');
                const metaEl = query('#backOfficeResultsMeta');
                baseResultsCountText = countEl ? countEl.textContent.trim() : '';
                baseResultsMetaHtml = metaEl ? metaEl.innerHTML : '';
            };

            const applyLocalSearchPreview = (rawValue) => {
                const list = query('#backOfficeResultsList');
                if (!list) return;

                const cards = queryAll('[data-back-office-search-text]', list);
                if (!cards.length) return;

                const term = normalizeSearchTerm(rawValue);
                const emptyState = query('#backOfficeEmptySearchState');
                const paginationWrap = query('#backOfficePaginationWrap');
                const countEl = query('#backOfficeResultsCount');
                const metaEl = query('#backOfficeResultsMeta');
                let visibleCount = 0;

                cards.forEach((card) => {
                    const haystack = normalizeSearchTerm(card.dataset.backOfficeSearchText || card.textContent);
                    const matched = term === '' || haystack.includes(term);
                    card.classList.toggle('hidden', !matched);
                    if (matched && card.offsetParent !== null) visibleCount += 1;
                });

                if (emptyState) {
                    emptyState.classList.toggle('hidden', !(term !== '' && visibleCount === 0));
                }

                if (paginationWrap) {
                    paginationWrap.classList.toggle('hidden', term !== '');
                }

                if (countEl) {
                    countEl.textContent = term === ''
                        ? baseResultsCountText
                        : `พบ ${visibleCount} รายการในหน้านี้`;
                }

                if (metaEl) {
                    metaEl.innerHTML = term === ''
                        ? baseResultsMetaHtml
                        : 'กรองทันทีโดยไม่ reload';
                }
            };

            const setFieldValue = (field, value) => {
                if (!field) return;

                if (field._flatpickr) {
                    if (value) {
                        field._flatpickr.setDate(value, true, 'd-m-Y');
                    } else {
                        field._flatpickr.clear();
                    }
                    return;
                }

                field.value = value ?? '';
            };

            const syncFormsFromSource = (sourceForm) => {
                if (!sourceForm) return;

                isSyncingForms = true;
                try {
                    getForms().forEach((targetForm) => {
                        if (!targetForm || targetForm === sourceForm) return;

                        queryAll('[name]', sourceForm).forEach((sourceField) => {
                            if (!sourceField.name) return;
                            const targetField = targetForm.elements.namedItem(sourceField.name);
                            if (!targetField) return;
                            setFieldValue(targetField, sourceField.value);
                        });
                    });
                } finally {
                    isSyncingForms = false;
                }
            };

            const syncFormsWithUrl = (url) => {
                isSyncingForms = true;
                try {
                    getForms().forEach((form) => {
                        queryAll('[name]', form).forEach((field) => {
                            if (!field.name) return;
                            const value = field.name === 'sort'
                                ? (url.searchParams.get(field.name) ?? 'latest')
                                : (url.searchParams.get(field.name) ?? '');
                            setFieldValue(field, value);
                        });
                    });
                } finally {
                    isSyncingForms = false;
                }

                updateFilterBadges();
            };

            const setLoading = (isLoading) => {
                if (loadingIndicator) {
                    loadingIndicator.classList.toggle('hidden', !isLoading);
                    loadingIndicator.classList.toggle('flex', isLoading);
                }

                query(selectors.resultsShell)?.classList.toggle('opacity-60', isLoading);
                query(selectors.statsGrid)?.classList.toggle('opacity-60', isLoading);
            };

            const buildUrlFromForm = (form) => {
                const url = new URL(form.action, window.location.origin);
                const formData = new FormData(form);

                for (const [key, rawValue] of formData.entries()) {
                    const value = String(rawValue ?? '').trim();
                    if (!value) continue;
                    if (key === 'sort' && value === 'latest') continue;
                    url.searchParams.append(key, value);
                }

                url.searchParams.delete('page');
                return url;
            };

            const replaceIndexContent = (html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextContent = query(selectors.content, doc);
                const currentContent = query(selectors.content);

                if (!nextContent || !currentContent) return false;
                currentContent.replaceWith(nextContent);
                return true;
            };

            const closeFilterDrawer = () => {
                const drawer = query(selectors.filterDrawer);
                const overlay = query(selectors.filterDrawerOverlay);
                if (!drawer || !overlay || !filterDrawerOpen) return;

                drawer.classList.remove('is-open');
                overlay.classList.remove('is-open');
                drawer.setAttribute('aria-hidden', 'true');
                filterDrawerOpen = false;
                unlockBody();
            };

            const openFilterDrawer = () => {
                const drawer = query(selectors.filterDrawer);
                const overlay = query(selectors.filterDrawerOverlay);
                if (!drawer || !overlay) return;
                if (filterDrawerOpen) return;

                if (detailDrawerOpen) {
                    closeDetailDrawer();
                }

                syncFormsFromSource(getPrimaryForm() || getFilterDrawerForm());
                drawer.classList.add('is-open');
                overlay.classList.add('is-open');
                drawer.setAttribute('aria-hidden', 'false');
                filterDrawerOpen = true;
                lockBody();

                window.setTimeout(() => {
                    query(selectors.drawerSearchInput)?.focus({ preventScroll: true });
                }, 120);
            };

            const closeDetailDrawer = () => {
                const drawer = query(selectors.detailDrawer);
                const overlay = query(selectors.detailOverlay);
                if (!drawer || !overlay || !detailDrawerOpen) return;

                drawer.classList.remove('is-open');
                overlay.classList.remove('is-open');
                drawer.setAttribute('aria-hidden', 'true');
                detailDrawerOpen = false;
                unlockBody();
            };

            const openDetailDrawer = async (url) => {
                const drawer = query(selectors.detailDrawer);
                const overlay = query(selectors.detailOverlay);
                const drawerBody = query(selectors.detailDrawerBody);
                if (!drawer || !overlay || !drawerBody || !url) return;

                if (filterDrawerOpen) {
                    closeFilterDrawer();
                }

                if (detailController) detailController.abort();
                detailController = new AbortController();

                drawerBody.innerHTML = '<div class="back-office-drawer-loading">กำลังโหลดข้อมูล...</div>';
                drawer.classList.add('is-open');
                overlay.classList.add('is-open');
                drawer.setAttribute('aria-hidden', 'false');
                if (!detailDrawerOpen) {
                    detailDrawerOpen = true;
                    lockBody();
                }

                try {
                    const fetchUrl = new URL(url, window.location.origin);
                    fetchUrl.searchParams.set('drawer', '1');

                    const response = await fetch(fetchUrl.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: detailController.signal,
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    drawerBody.innerHTML = await response.text();
                } catch (error) {
                    if (error.name === 'AbortError') return;
                    drawerBody.innerHTML = `
                        <div class="rounded-[24px] border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                            <div class="text-base font-semibold text-slate-800">ไม่สามารถโหลดรายละเอียดได้</div>
                            <p class="mt-2 text-sm text-slate-500">ลองเปิดใหม่อีกครั้ง หรือเข้าหน้าเต็มแทน</p>
                        </div>
                    `;
                }
            };

            const fetchAndRender = async (url, options = {}) => {
                const { updateUrl = true } = options;
                const token = ++requestToken;

                if (resultController) resultController.abort();
                resultController = new AbortController();
                setLoading(true);

                try {
                    const response = await fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: resultController.signal,
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const html = await response.text();
                    if (token !== requestToken) return;
                    if (!replaceIndexContent(html)) {
                        throw new Error('Cannot replace back office index content');
                    }

                    if (updateUrl) {
                        history.replaceState({ ajax: true }, '', `${url.pathname}${url.search}`);
                    }

                    syncFormsWithUrl(url);
                    captureResultsMeta();
                    applyLocalSearchPreview(query('input[name="search"]', getPrimaryForm() || getFilterDrawerForm())?.value || '');
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
                    const form = getPrimaryForm() || getFilterDrawerForm();
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

                    queryAll('[data-back-office-live]', form).forEach((field) => {
                        if (field.dataset.ajaxBound === 'true') return;
                        field.dataset.ajaxBound = 'true';

                        if (field.matches('input[type="text"], input[type="search"]')) {
                            field.addEventListener('input', () => {
                                if (isSyncingForms) return;
                                if (isSearchComposing) return;
                                syncFormsFromSource(form);
                                updateFilterBadges();
                                const sourceForm = getPrimaryForm() || form;
                                const searchField = query('input[name="search"]', sourceForm);
                                applyLocalSearchPreview(searchField ? searchField.value : '');
                                fetchAndRender(buildUrlFromForm(sourceForm), { updateUrl: true });
                            });
                            field.addEventListener('keydown', (event) => {
                                if (isSyncingForms) return;
                                if (event.key === 'Enter') {
                                    event.preventDefault();
                                    syncFormsFromSource(form);
                                    const sourceForm = getPrimaryForm() || form;
                                    const searchField = query('input[name="search"]', sourceForm);
                                    applyLocalSearchPreview(searchField ? searchField.value : '');
                                    fetchAndRender(buildUrlFromForm(sourceForm), { updateUrl: true });
                                }
                            });
                            field.addEventListener('compositionstart', () => {
                                if (isSyncingForms) return;
                                isSearchComposing = true;
                            });
                            field.addEventListener('compositionend', () => {
                                if (isSyncingForms) return;
                                isSearchComposing = false;
                                syncFormsFromSource(form);
                                updateFilterBadges();
                                const sourceForm = getPrimaryForm() || form;
                                const searchField = query('input[name="search"]', sourceForm);
                                applyLocalSearchPreview(searchField ? searchField.value : '');
                                fetchAndRender(buildUrlFromForm(sourceForm), { updateUrl: true });
                            });
                            field.addEventListener('change', () => {
                                if (isSyncingForms) return;
                                syncFormsFromSource(form);
                                updateFilterBadges();
                                const sourceForm = getPrimaryForm() || form;
                                const searchField = query('input[name="search"]', sourceForm);
                                applyLocalSearchPreview(searchField ? searchField.value : '');
                                fetchAndRender(buildUrlFromForm(sourceForm), { updateUrl: true });
                            });
                            return;
                        }

                        field.addEventListener('change', () => {
                            if (isSyncingForms) return;
                            syncFormsFromSource(form);
                            updateFilterBadges();
                            submitDebounced(120);
                        });
                    });
                });
            };

            const bindResetLinks = () => {
                if (window.__backOfficeResetBound) return;
                window.__backOfficeResetBound = true;

                document.addEventListener('click', (event) => {
                    const link = event.target.closest('[data-back-office-reset]');
                    if (!link) return;

                    event.preventDefault();
                    const url = new URL(link.href, window.location.origin);
                    syncFormsWithUrl(url);
                    fetchAndRender(url, { updateUrl: true });
                });
            };

            const bindDelegatedLinks = () => {
                if (window.__backOfficeLinksBound) return;
                window.__backOfficeLinksBound = true;

                document.addEventListener('click', (event) => {
                    const openFilter = event.target.closest(selectors.openFilterDrawer);
                    if (openFilter) {
                        event.preventDefault();
                        openFilterDrawer();
                        return;
                    }

                    const ajaxLink = event.target.closest(`${selectors.content} a[href]`);
                    if (ajaxLink) {
                        const url = new URL(ajaxLink.href, window.location.origin);
                        const isPagination = url.pathname === currentPath && url.searchParams.has('page');
                        if (isPagination) {
                            event.preventDefault();
                            syncFormsWithUrl(url);
                            fetchAndRender(url, { updateUrl: true });
                            return;
                        }
                    }

                    const drawerTrigger = event.target.closest('[data-back-office-open]');
                    if (drawerTrigger) {
                        event.preventDefault();
                        openDetailDrawer(drawerTrigger.href);
                    }
                });
            };

            const bindDeleteConfirm = () => {
                if (window.__backOfficeDeleteConfirmBound) return;
                window.__backOfficeDeleteConfirmBound = true;

                const escapeHtml = (value) => String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');

                document.addEventListener('submit', (event) => {
                    const form = event.target?.closest('form[data-back-office-delete-form]');
                    if (!form) return;

                    event.preventDefault();

                    const recordId = form.dataset.recordId || '-';
                    const title = form.dataset.recordTitle || '-';
                    const creator = form.dataset.recordCreator || '-';
                    const fallbackText = `ยืนยันลบ Record #${recordId}\nงาน: ${title}\nผู้บันทึก: ${creator}`;

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'ยืนยันการลบงาน Back Office',
                            html: `Record #${escapeHtml(recordId)}<br>งาน: ${escapeHtml(title)}<br>ผู้บันทึก: ${escapeHtml(creator)}`,
                            showCancelButton: true,
                            confirmButtonText: 'ลบรายการนี้',
                            cancelButtonText: 'ยกเลิก',
                            confirmButtonColor: '#dc2626'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                        return;
                    }

                    if (window.confirm(fallbackText)) {
                        form.submit();
                    }
                });
            };

            const bindDrawers = () => {
                if (window.__backOfficeDrawersBound) return;
                window.__backOfficeDrawersBound = true;

                query(selectors.filterDrawerClose)?.addEventListener('click', closeFilterDrawer);
                query(selectors.filterDrawerOverlay)?.addEventListener('click', closeFilterDrawer);
                query(selectors.detailDrawerClose)?.addEventListener('click', closeDetailDrawer);
                query(selectors.detailOverlay)?.addEventListener('click', closeDetailDrawer);

                document.addEventListener('keydown', (event) => {
                    if (event.key !== 'Escape') return;
                    if (detailDrawerOpen) {
                        closeDetailDrawer();
                        return;
                    }
                    if (filterDrawerOpen) {
                        closeFilterDrawer();
                    }
                });
            };

            window.addEventListener('popstate', () => {
                const url = new URL(window.location.href);
                if (url.pathname !== currentPath) return;
                syncFormsWithUrl(url);
                fetchAndRender(url, { updateUrl: false });
            });

            window.addEventListener('scroll', syncFabVisibility, { passive: true });
            window.addEventListener('resize', syncFabVisibility);

            bindForms();
            bindResetLinks();
            bindDelegatedLinks();
            bindDeleteConfirm();
            bindDrawers();
            updateFilterBadges();
            syncFabVisibility();
            captureResultsMeta();
            applyLocalSearchPreview(query('input[name="search"]', getPrimaryForm() || getFilterDrawerForm())?.value || '');
        })();
    </script>
@endsection

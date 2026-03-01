@extends('layouts.app')

@section('content')
    <style>
        .mode-option {
            transition: all .16s ease;
            border: 1px solid #bbf7d0;
            background: #fff;
            color: #166534;
        }

        .mode-option:hover {
            border-color: #86efac;
            background: #f0fdf4;
        }

        .mode-option.is-active {
            background: #6ee7b7;
            border-color: #34d399;
            color: #065f46;
            box-shadow: 0 6px 16px rgba(52, 211, 153, .22);
        }

        .fte-table-wrap,
        .fte-detail-table-wrap {
            width: 100%;
        }

        @media (max-width: 1024px) {
            .fte-table-wrap,
            .fte-detail-table-wrap {
                overflow-x: visible;
            }

            .fte-index-table thead {
                display: none;
            }

            .fte-index-table,
            .fte-index-table tbody,
            .fte-index-table tr,
            .fte-index-table td {
                display: block;
                width: 100%;
            }

            .fte-index-table tr {
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 10px 12px;
                margin-bottom: 10px;
                background: #ffffff;
            }

            .fte-index-table td {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 12px;
                padding: 6px 0;
                text-align: left;
            }

            .fte-index-table td::before {
                content: attr(data-label);
                font-size: 11px;
                text-transform: uppercase;
                color: #94a3b8;
                flex: 0 0 120px;
            }

            .fte-index-table tr.fte-project-detail-row td {
                display: block;
            }

            .fte-index-table tr.fte-project-detail-row td::before {
                content: none;
            }

            .fte-detail-table thead {
                display: none;
            }

            .fte-detail-table,
            .fte-detail-table tbody,
            .fte-detail-table tr,
            .fte-detail-table td {
                display: block;
                width: 100%;
            }

            .fte-detail-table tr {
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 10px 12px;
                margin-bottom: 10px;
                background: #ffffff;
            }

            .fte-detail-table td {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 12px;
                padding: 6px 0;
                text-align: left;
            }

            .fte-detail-table td::before {
                content: attr(data-label);
                font-size: 11px;
                text-transform: uppercase;
                color: #94a3b8;
                flex: 0 0 120px;
            }

            .fte-index-table,
            .fte-detail-table {
                min-width: 0 !important;
                width: 100% !important;
                table-layout: fixed;
            }

            .fte-index-table td>*,
            .fte-detail-table td>* {
                max-width: 100%;
            }

            .fte-index-table td[data-label="รายละเอียด"] button {
                width: 100%;
            }

            .fte-index-table tr.fte-project-detail-row,
            .fte-detail-table tr {
                box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
            }

            .fte-index-table tr.fte-project-detail-row {
                border: none;
                background: transparent;
                padding: 0;
                box-shadow: none;
            }

            .fte-index-table tr.fte-project-detail-row td {
                padding: 0;
            }

            .fte-index-table tr.fte-project-detail-row.is-open {
                display: block;
            }
        }

        .fte-index-table tr.fte-project-detail-row {
            display: none;
        }

        .fte-index-table tr.fte-project-detail-row.is-open {
            display: table-row;
        }

        @media (max-width: 1024px) {
            .fte-index-table tr.fte-project-detail-row.is-open {
                display: block;
            }
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.35s ease;
        }

        .fte-filter-drawer-overlay {
            position: fixed;
            inset: 0;
            z-index: 48;
            background: rgba(15, 23, 42, .08);
            backdrop-filter: blur(2px);
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s ease;
        }

        .fte-filter-drawer-overlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .fte-filter-drawer {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 49;
            width: min(92vw, 380px);
            height: 100dvh;
            transform: translateX(-105%);
            transition: transform .25s ease;
        }

        .fte-filter-drawer.is-open {
            transform: translateX(0);
        }

        .fte-filter-fab {
            position: fixed;
            left: 16px;
            bottom: calc(16px + env(safe-area-inset-bottom, 0px));
            z-index: 35;
            width: 76px;
            min-height: 84px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .35rem;
            padding: .75rem .55rem;
            border-radius: 22px;
            border: 1px solid #e2e8f0;
            background: rgba(255, 255, 255, .95);
            color: #0f172a;
            box-shadow: 0 18px 36px rgba(15, 23, 42, .12);
            backdrop-filter: blur(14px);
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
            transition: opacity .2s ease, transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .fte-filter-fab.is-visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .fte-filter-fab.is-visible:hover {
            transform: translateY(-2px);
            border-color: #86efac;
            box-shadow: 0 20px 40px rgba(15, 23, 42, .16);
        }

        .fte-filter-fab:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px rgba(134, 239, 172, .22), 0 20px 40px rgba(15, 23, 42, .16);
        }

        .fte-filter-fab__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 16px;
            background: linear-gradient(135deg, #064e3b 0%, #059669 100%);
            color: #f8fafc;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .18);
        }

        .fte-filter-fab__title {
            font-size: .74rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
        }

        .fte-filter-fab__meta {
            font-size: .61rem;
            color: #64748b;
            line-height: 1.1;
            text-align: center;
        }

        .fte-filter-fab__badge {
            position: absolute;
            top: -0.35rem;
            right: -0.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.45rem;
            height: 1.45rem;
            padding: 0 .32rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
            border: 2px solid #fff;
            color: #fff;
            font-size: .68rem;
            font-weight: 800;
            line-height: 1;
            box-shadow: 0 10px 20px rgba(239, 68, 68, .3);
        }

        .fte-filter-fab__badge.hidden {
            display: none;
        }

        .fte-filter-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
            padding: .4rem .8rem;
            font-size: .75rem;
            font-weight: 700;
            line-height: 1;
        }

        @media (max-width: 1024px) {
            .fte-filter-fab {
                width: 68px;
                min-height: 76px;
                padding: .65rem .45rem;
                border-radius: 20px;
            }

            .fte-filter-fab__icon {
                width: 36px;
                height: 36px;
                border-radius: 15px;
            }

            .fte-filter-fab__title {
                font-size: .68rem;
            }

            .fte-filter-fab__meta {
                font-size: .58rem;
            }

            .fte-filter-fab__badge {
                top: -0.3rem;
                right: -0.3rem;
            }
        }

        @media (max-width: 640px) {
            .fte-filter-fab {
                width: 58px;
                min-height: 58px;
                padding: 8px;
                border-radius: 18px;
                gap: 0;
            }

            .fte-filter-fab__icon {
                width: 34px;
                height: 34px;
                border-radius: 14px;
            }

            .fte-filter-fab__title,
            .fte-filter-fab__meta {
                display: none;
            }

            .fte-filter-fab__badge {
                min-width: 1.25rem;
                height: 1.25rem;
                font-size: .62rem;
            }
        }
    </style>

    <div class="max-w-[92rem] mx-auto p-4">
        <div class="mb-5 flex items-center gap-4">
            <a href="javascript:history.back()" class="text-emerald-600 hover:underline flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                ย้อนกลับ
            </a>
        </div>

        <h2 class="text-xl mb-2 md:text-2xl font-bold">📊 สรุป FTE รายโปรเจค</h2>

        <div id="fte-filter-card" class="bg-gradient-to-r from-green-50 via-white to-emerald-50 border border-green-100 rounded-2xl shadow-sm mb-6 px-4 py-6">
            <form method="GET" id="filter-form" autocomplete="off" class="space-y-4">
                <div class="flex flex-col">
                    <label class="text-slate-700 font-semibold mb-2">ดูข้อมูล</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <button type="button" data-mode-value="year"
                            class="mode-option w-full rounded-xl px-4 py-2.5 text-sm font-semibold">
                            รายปี
                        </button>
                        <button type="button" data-mode-value="month"
                            class="mode-option w-full rounded-xl px-4 py-2.5 text-sm font-semibold">
                            เดือนนี้
                        </button>
                        <button type="button" data-mode-value="custom"
                            class="mode-option w-full rounded-xl px-4 py-2.5 text-sm font-semibold">
                            เลือกช่วงวันที่
                        </button>
                    </div>
                    <select name="mode" id="mode" class="sr-only">
                        <option value="year" {{ $mode == 'year' ? 'selected' : '' }}>รายปี</option>
                        <option value="month" {{ $mode == 'month' ? 'selected' : '' }}>เดือนนี้</option>
                        <option value="custom" {{ $mode == 'custom' ? 'selected' : '' }}>เลือกช่วงวันที่</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-5 items-end">
                    <div class="lg:col-span-9">
                        <div id="filter-year" class="flex flex-col {{ $mode === 'year' ? '' : 'hidden' }}">
                            <label class="text-slate-700 mb-1">ปี (พ.ศ.)</label>
                            <select name="year" id="yearSelect" class="rounded-xl border-slate-200 bg-white p-2.5 focus:ring-2 focus:ring-green-200 w-full">
                                @foreach ($years as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="filter-month" class="flex flex-col {{ $mode === 'month' ? '' : 'hidden' }}">
                            <label class="text-slate-700 mb-1">เดือน</label>
                            <div class="rounded-xl border border-slate-200 bg-white p-2.5 px-3 text-base font-semibold text-slate-800 w-full">
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $monthLabel = $now->locale('th')->translatedFormat('F');
                                    $yearLabel = $now->year + 543;
                                @endphp
                                {{ $monthLabel }} {{ $yearLabel }}
                            </div>
                        </div>

                        <div id="filter-custom" class="flex flex-col {{ $mode === 'custom' ? '' : 'hidden' }}">
                            <label class="text-slate-700 mb-1">ช่วงวันที่</label>
                            <div class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] gap-2 items-end w-full">
                                <div class="relative min-w-0">
                                    <input name="start" id="dateStart"
                                        class="js-date-be rounded-xl border-slate-200 bg-white p-2.5 w-full pl-9 focus:ring-2 focus:ring-green-200"
                                        placeholder="วันเริ่มต้น" value="{{ $start ?? '' }}" autocomplete="off">
                                    <span class="absolute left-2 top-2.5 text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                    </span>
                                </div>
                                <span class="text-gray-500 pb-2">ถึง</span>
                                <div class="relative min-w-0">
                                    <input name="end" id="dateEnd"
                                        class="js-date-be rounded-xl border-slate-200 bg-white p-2.5 w-full pl-9 focus:ring-2 focus:ring-green-200"
                                        placeholder="วันสิ้นสุด" value="{{ $end ?? '' }}" autocomplete="off">
                                    <span class="absolute left-2 top-2.5 text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-slate-700 mb-1">เรียงลำดับ</label>
                        <select id="projectSortSelect" class="rounded-xl border-slate-200 bg-white p-2.5 w-full focus:ring-2 focus:ring-green-200">
                            <option value="filtered_desc">FTE ตามตัวกรอง: มาก → น้อย</option>
                            <option value="filtered_asc">FTE ตามตัวกรอง: น้อย → มาก</option>
                            <option value="total_desc">FTE ทั้งหมด: มาก → น้อย</option>
                            <option value="total_asc">FTE ทั้งหมด: น้อย → มาก</option>
                            <option value="month_desc">FTE เดือนนี้: มาก → น้อย</option>
                            <option value="month_asc">FTE เดือนนี้: น้อย → มาก</option>
                            <option value="name_asc">ชื่อโปรเจค: A → Z</option>
                            <option value="name_desc">ชื่อโปรเจค: Z → A</option>
                        </select>
                    </div>

                    <div class="flex flex-col lg:col-span-10">
                        <label class="text-slate-700 mb-1">ค้นหาโปรเจคในตาราง</label>
                        <input type="search" id="projectSearchInput"
                            class="rounded-xl border-slate-200 bg-white p-2.5 w-full focus:ring-2 focus:ring-green-200"
                            placeholder="พิมพ์ชื่อโปรเจคเพื่อค้นหา">
                    </div>

                    <div class="flex flex-col lg:col-span-2">
                        <label class="invisible mb-1">ล้างตัวกรอง</label>
                        <button type="button" id="clearFiltersBtn"
                            class="w-full px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 font-semibold hover:bg-emerald-100 text-center">
                            ล้างตัวกรอง
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div id="fte-project-results">
            @include('fte.partials.project-index-table', [
                'fteSummary' => $fteSummary,
                'monthlyFTE' => $monthlyFTE,
                'summaryTotalFte' => $summaryTotalFte,
                'summaryFilteredFte' => $summaryFilteredFte,
                'summaryMonthFte' => $summaryMonthFte,
            ])
        </div>
    </div>

    <div id="fte-project-filter-drawer-overlay" class="fte-filter-drawer-overlay"></div>
    <aside id="fte-project-filter-drawer" class="fte-filter-drawer" aria-hidden="true">
        <div class="flex h-full flex-col border-r border-slate-200 bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-4 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">คัดกรองข้อมูล</h3>
                    <p class="mt-1 text-xs text-slate-500">ค่าฟิลเตอร์ชุดนี้ sync กับฟิลเตอร์ด้านบนตาราง</p>
                </div>
                <button id="fte-project-filter-drawer-close" type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                    <span class="sr-only">ปิดฟิลเตอร์</span>
                    <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                </button>
            </div>

            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-4">
                <div class="flex items-center justify-between gap-2">
                    <span class="fte-filter-pill">
                        ปรับฟิลเตอร์แล้วผลจะอัปเดตทันที
                    </span>
                    <button type="button" id="drawerClearFiltersBtn"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        ล้างฟิลเตอร์
                    </button>
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-700 font-semibold mb-2">ดูข้อมูล</label>
                    <div class="grid grid-cols-1 gap-2">
                        <button type="button" data-drawer-mode-value="year"
                            class="mode-option w-full rounded-xl px-4 py-2.5 text-sm font-semibold">
                            รายปี
                        </button>
                        <button type="button" data-drawer-mode-value="month"
                            class="mode-option w-full rounded-xl px-4 py-2.5 text-sm font-semibold">
                            เดือนนี้
                        </button>
                        <button type="button" data-drawer-mode-value="custom"
                            class="mode-option w-full rounded-xl px-4 py-2.5 text-sm font-semibold">
                            เลือกช่วงวันที่
                        </button>
                    </div>
                </div>

                <div id="drawer-filter-year" class="flex flex-col {{ $mode === 'year' ? '' : 'hidden' }}">
                    <label class="text-slate-700 mb-1">ปี (พ.ศ.)</label>
                    <select id="drawerYearSelect" class="rounded-xl border-slate-200 bg-white p-2.5 focus:ring-2 focus:ring-green-200 w-full">
                        @foreach ($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="drawer-filter-month" class="flex flex-col {{ $mode === 'month' ? '' : 'hidden' }}">
                    <label class="text-slate-700 mb-1">เดือน</label>
                    <div class="rounded-xl border border-slate-200 bg-white p-2.5 px-3 text-base font-semibold text-slate-800 w-full">
                        @php
                            $now = \Carbon\Carbon::now();
                            $monthLabel = $now->locale('th')->translatedFormat('F');
                            $yearLabel = $now->year + 543;
                        @endphp
                        {{ $monthLabel }} {{ $yearLabel }}
                    </div>
                </div>

                <div id="drawer-filter-custom" class="flex flex-col {{ $mode === 'custom' ? '' : 'hidden' }}">
                    <label class="text-slate-700 mb-1">ช่วงวันที่</label>
                    <div class="grid grid-cols-1 gap-2 items-end w-full">
                        <div class="relative min-w-0">
                            <input id="drawerDateStart"
                                class="js-date-be rounded-xl border-slate-200 bg-white p-2.5 w-full pl-9 focus:ring-2 focus:ring-green-200"
                                placeholder="วันเริ่มต้น" value="{{ $start ?? '' }}" autocomplete="off">
                            <span class="absolute left-2 top-2.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </span>
                        </div>
                        <div class="relative min-w-0">
                            <input id="drawerDateEnd"
                                class="js-date-be rounded-xl border-slate-200 bg-white p-2.5 w-full pl-9 focus:ring-2 focus:ring-green-200"
                                placeholder="วันสิ้นสุด" value="{{ $end ?? '' }}" autocomplete="off">
                            <span class="absolute left-2 top-2.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-700 mb-1">เรียงลำดับ</label>
                    <select id="drawerProjectSortSelect" class="rounded-xl border-slate-200 bg-white p-2.5 w-full focus:ring-2 focus:ring-green-200">
                        <option value="filtered_desc">FTE ตามตัวกรอง: มาก → น้อย</option>
                        <option value="filtered_asc">FTE ตามตัวกรอง: น้อย → มาก</option>
                        <option value="total_desc">FTE ทั้งหมด: มาก → น้อย</option>
                        <option value="total_asc">FTE ทั้งหมด: น้อย → มาก</option>
                        <option value="month_desc">FTE เดือนนี้: มาก → น้อย</option>
                        <option value="month_asc">FTE เดือนนี้: น้อย → มาก</option>
                        <option value="name_asc">ชื่อโปรเจค: A → Z</option>
                        <option value="name_desc">ชื่อโปรเจค: Z → A</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-slate-700 mb-1">ค้นหาโปรเจคในตาราง</label>
                    <input type="search" id="drawerProjectSearchInput"
                        class="rounded-xl border-slate-200 bg-white p-2.5 w-full focus:ring-2 focus:ring-green-200"
                        placeholder="พิมพ์ชื่อโปรเจคเพื่อค้นหา">
                </div>
            </div>
        </div>
    </aside>

    <button id="fte-project-filter-fab" type="button" class="fte-filter-fab" aria-label="เปิดฟิลเตอร์">
        <span class="fte-filter-fab__icon" aria-hidden="true">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                <path d="M4 6h16M7 12h10m-7 6h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </span>
        <span class="fte-filter-fab__title">ฟิลเตอร์</span>
        <span class="fte-filter-fab__meta">เปิดแผง</span>
        <span id="fte-project-filter-fab-badge" class="fte-filter-fab__badge hidden">0</span>
    </button>

    <script>
        function toggleProjectDetail(idx) {
            const all = document.querySelectorAll('.fte-project-detail-row');
            const row = document.getElementById('project-detail-row-' + idx);
            const buttons = document.querySelectorAll('button[data-project-detail]');
            if (!row) return;

            const willShow = !row.classList.contains('is-open');
            all.forEach((el) => el.classList.remove('is-open'));
            buttons.forEach((btn) => {
                btn.textContent = 'ดูรายละเอียด';
                btn.setAttribute('aria-expanded', 'false');
            });

            if (willShow) {
                row.classList.add('is-open');
                const btn = document.querySelector(`button[data-project-detail="${idx}"]`);
                if (btn) {
                    btn.textContent = 'ซ่อนรายละเอียด';
                    btn.setAttribute('aria-expanded', 'true');
                }
            }
        }

        document.addEventListener('click', function(e) {
            const rows = document.querySelectorAll('[id^="project-detail-row-"]');
            rows.forEach((row) => {
                if (!row.classList.contains('is-open')) return;
                const idx = row.id.replace('project-detail-row-', '');
                const btn = document.querySelector('button[data-project-detail="' + idx + '"]');
                if (!row.contains(e.target) && (!btn || !btn.contains(e.target))) {
                    row.classList.remove('is-open');
                    if (btn) {
                        btn.textContent = 'ดูรายละเอียด';
                        btn.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const routeProjectIndex = @json(route('fte.project.index'));
            const filterForm = document.getElementById('filter-form');
            const modeSelect = document.getElementById('mode');
            const yearSelect = document.getElementById('yearSelect');
            const startInput = document.getElementById('dateStart');
            const endInput = document.getElementById('dateEnd');
            const projectSearchInput = document.getElementById('projectSearchInput');
            const projectSortSelect = document.getElementById('projectSortSelect');
            const clearFiltersBtn = document.getElementById('clearFiltersBtn');
            const resultsEl = document.getElementById('fte-project-results');
            const filterCard = document.getElementById('fte-filter-card');
            const drawer = document.getElementById('fte-project-filter-drawer');
            const drawerOverlay = document.getElementById('fte-project-filter-drawer-overlay');
            const drawerCloseBtn = document.getElementById('fte-project-filter-drawer-close');
            const drawerYearSelect = document.getElementById('drawerYearSelect');
            const drawerDateStart = document.getElementById('drawerDateStart');
            const drawerDateEnd = document.getElementById('drawerDateEnd');
            const drawerProjectSearchInput = document.getElementById('drawerProjectSearchInput');
            const drawerProjectSortSelect = document.getElementById('drawerProjectSortSelect');
            const drawerClearFiltersBtn = document.getElementById('drawerClearFiltersBtn');
            const filterFab = document.getElementById('fte-project-filter-fab');
            const filterFabBadge = document.getElementById('fte-project-filter-fab-badge');

            const filterYear = document.getElementById('filter-year');
            const filterMonth = document.getElementById('filter-month');
            const filterCustom = document.getElementById('filter-custom');
            const modeButtons = Array.from(filterForm.querySelectorAll('[data-mode-value]'));
            const drawerFilterYear = document.getElementById('drawer-filter-year');
            const drawerFilterMonth = document.getElementById('drawer-filter-month');
            const drawerFilterCustom = document.getElementById('drawer-filter-custom');
            const drawerModeButtons = Array.from(document.querySelectorAll('[data-drawer-mode-value]'));

            let pendingController = null;
            let debounceTimer = null;
            let tableDebounceTimer = null;
            let drawerOpen = false;
            let previousBodyOverflow = '';

            const normalizeText = (value) => String(value || '')
                .toLowerCase()
                .normalize('NFKD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/\s+/g, '');

            const fuzzyScore = (query, target) => {
                const q = normalizeText(query);
                const t = normalizeText(target);

                if (!q) return 0;
                if (!t) return -1;

                const includeIdx = t.indexOf(q);
                if (includeIdx !== -1) {
                    return 2000 - includeIdx * 2 - (t.length - q.length);
                }

                let qi = 0;
                let score = 0;
                let streak = 0;

                for (let i = 0; i < t.length && qi < q.length; i++) {
                    if (t[i] === q[qi]) {
                        qi++;
                        streak++;
                        score += 20 + streak * 3;
                    } else {
                        streak = 0;
                    }
                }

                if (qi < q.length) return -1;
                return score - t.length;
            };

            function updateModeButtonGroup(buttons, mode, key = 'modeValue') {
                buttons.forEach((button) => {
                    const buttonMode = key === 'drawerModeValue' ? button.dataset.drawerModeValue : button.dataset.modeValue;
                    const active = button.dataset.modeValue === mode;
                    if (key === 'drawerModeValue') {
                        button.setAttribute('aria-pressed', buttonMode === mode ? 'true' : 'false');
                        button.classList.toggle('is-active', buttonMode === mode);
                        return;
                    }
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                    button.classList.toggle('is-active', active);
                });
            }

            function updateModeButtons() {
                const mode = modeSelect.value;
                updateModeButtonGroup(modeButtons, mode, 'modeValue');
                updateModeButtonGroup(drawerModeButtons, mode, 'drawerModeValue');
            }

            function updateFilterVisibility() {
                const mode = modeSelect.value;
                filterYear.classList.toggle('hidden', mode !== 'year');
                filterMonth.classList.toggle('hidden', mode !== 'month');
                filterCustom.classList.toggle('hidden', mode !== 'custom');
                if (drawerFilterYear) drawerFilterYear.classList.toggle('hidden', mode !== 'year');
                if (drawerFilterMonth) drawerFilterMonth.classList.toggle('hidden', mode !== 'month');
                if (drawerFilterCustom) drawerFilterCustom.classList.toggle('hidden', mode !== 'custom');
                updateModeButtons();
            }

            function getActiveFilterCount() {
                let count = 0;
                if (modeSelect.value !== 'month') count++;
                if (modeSelect.value === 'year' && yearSelect?.value) count++;
                if (modeSelect.value === 'custom' && startInput.value.trim()) count++;
                if (modeSelect.value === 'custom' && endInput.value.trim()) count++;
                if ((projectSearchInput?.value || '').trim()) count++;
                if ((projectSortSelect?.value || 'filtered_desc') !== 'filtered_desc') count++;
                return count;
            }

            function updateFilterFab() {
                if (!filterFab || !filterCard) return;
                const rect = filterCard.getBoundingClientRect();
                filterFab.classList.toggle('is-visible', rect.bottom < 36);

                const count = getActiveFilterCount();
                if (filterFabBadge) {
                    filterFabBadge.textContent = String(count);
                    filterFabBadge.classList.toggle('hidden', count === 0);
                }
            }

            function syncDrawerFromPrimary() {
                if (drawerYearSelect && yearSelect) drawerYearSelect.value = yearSelect.value;
                if (drawerDateStart) drawerDateStart.value = startInput.value;
                if (drawerDateEnd) drawerDateEnd.value = endInput.value;
                if (drawerProjectSearchInput && projectSearchInput) drawerProjectSearchInput.value = projectSearchInput.value;
                if (drawerProjectSortSelect && projectSortSelect) drawerProjectSortSelect.value = projectSortSelect.value;
                updateFilterVisibility();
                updateFilterFab();
            }

            function lockBodyScroll(lock) {
                if (lock) {
                    previousBodyOverflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                    return;
                }
                document.body.style.overflow = previousBodyOverflow;
            }

            function openFilterDrawer() {
                if (!drawer || !drawerOverlay) return;
                syncDrawerFromPrimary();
                drawer.classList.add('is-open');
                drawerOverlay.classList.add('is-open');
                drawer.setAttribute('aria-hidden', 'false');
                drawerOpen = true;
                lockBodyScroll(true);
            }

            function closeFilterDrawer() {
                if (!drawer || !drawerOverlay) return;
                drawer.classList.remove('is-open');
                drawerOverlay.classList.remove('is-open');
                drawer.setAttribute('aria-hidden', 'true');
                drawerOpen = false;
                lockBodyScroll(false);
            }

            function resetIrrelevantInputsForMode() {
                const mode = modeSelect.value;

                if (mode === 'year' || mode === 'month') {
                    startInput.value = '';
                    endInput.value = '';
                }

                if (mode === 'month' || mode === 'custom') {
                    if (yearSelect) yearSelect.value = '';
                }

                if (mode === 'year' && yearSelect && !yearSelect.value && yearSelect.options.length > 0) {
                    yearSelect.value = yearSelect.options[0].value;
                }
            }

            function buildQueryParams() {
                const mode = modeSelect.value || 'month';
                const params = new URLSearchParams();
                params.set('mode', mode);

                if (mode === 'year' && yearSelect && yearSelect.value) {
                    params.set('year', yearSelect.value);
                }

                if (mode === 'custom') {
                    if (startInput.value.trim()) params.set('start', startInput.value.trim());
                    if (endInput.value.trim()) params.set('end', endInput.value.trim());
                }

                return params;
            }

            function applyProjectTableSearchAndSort() {
                const tbody = resultsEl.querySelector('table.fte-index-table tbody');
                if (!tbody) return;

                const query = (projectSearchInput?.value || '').trim();
                const sortMode = projectSortSelect?.value || 'filtered_desc';
                const emptyRowId = 'client-project-empty-row';

                const oldEmptyRow = document.getElementById(emptyRowId);
                if (oldEmptyRow) oldEmptyRow.remove();

                const mainRows = Array.from(tbody.querySelectorAll('tr.fte-project-main-row'));
                const pairs = mainRows.map((main) => {
                    const detail = main.nextElementSibling && main.nextElementSibling.classList.contains('fte-project-detail-row')
                        ? main.nextElementSibling
                        : null;

                    const name = main.dataset.projectName || '';
                    const total = Number(main.dataset.totalFte || 0);
                    const filtered = Number(main.dataset.filteredFte || 0);
                    const month = Number(main.dataset.monthFte || 0);
                    const year = Number(main.dataset.projectYear || 0);
                    const score = query ? fuzzyScore(query, name) : 0;
                    const hasFte = filtered > 0;
                    const matched = query ? score >= 0 : hasFte;

                    return {
                        main,
                        detail,
                        name,
                        total,
                        filtered,
                        month,
                        year,
                        score,
                        matched,
                    };
                });

                const nameAsc = (a, b) => a.name.localeCompare(b.name, 'th');
                const sortByMode = (a, b) => {
                    switch (sortMode) {
                        case 'total_asc':
                            return a.total - b.total;
                        case 'filtered_desc':
                            return b.filtered - a.filtered;
                        case 'filtered_asc':
                            return a.filtered - b.filtered;
                        case 'month_desc':
                            return b.month - a.month;
                        case 'month_asc':
                            return a.month - b.month;
                        case 'name_desc':
                            return nameAsc(b, a);
                        case 'name_asc':
                            return nameAsc(a, b);
                        case 'total_desc':
                        default:
                            return b.total - a.total;
                    }
                };

                pairs.sort((a, b) => {
                    if (a.matched !== b.matched) return a.matched ? -1 : 1;
                    const byMode = sortByMode(a, b);
                    if (byMode !== 0) return byMode;
                    if (query) {
                        const byScore = b.score - a.score;
                        if (byScore !== 0) return byScore;
                    }
                    return nameAsc(a, b);
                });

                pairs.forEach((pair) => {
                    tbody.appendChild(pair.main);
                    if (pair.detail) tbody.appendChild(pair.detail);

                    pair.main.style.display = pair.matched ? '' : 'none';
                    if (pair.detail) {
                        if (!pair.matched) {
                            pair.detail.classList.remove('is-open');
                            pair.detail.style.display = 'none';
                        } else {
                            pair.detail.style.display = '';
                        }
                    }
                });

                const visibleCount = pairs.filter((pair) => pair.matched).length;
                const countEl = resultsEl.querySelector('#fte-project-visible-count');
                if (countEl) {
                    countEl.textContent = String(visibleCount);
                }

                if (visibleCount === 0) {
                    const tr = document.createElement('tr');
                    tr.id = emptyRowId;
                    const emptyText = query ? 'ไม่พบข้อมูลตามคำค้นหา' : 'ไม่พบข้อมูลที่มีค่า FTE';
                    tr.innerHTML = `<td colspan="6" class="p-8 text-center text-gray-400">${emptyText}</td>`;
                    tbody.appendChild(tr);
                }

                pairs.forEach((pair) => {
                    const btn = pair.main.querySelector('button[data-project-detail]');
                    if (!btn || !pair.detail) return;
                    if (pair.detail.classList.contains('is-open')) {
                        btn.textContent = 'ซ่อนรายละเอียด';
                        btn.setAttribute('aria-expanded', 'true');
                    } else {
                        btn.textContent = 'ดูรายละเอียด';
                        btn.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            async function loadResults() {
                const params = buildQueryParams();
                const fetchParams = new URLSearchParams(params);
                fetchParams.set('partial', '1');

                if (pendingController) {
                    pendingController.abort();
                }

                pendingController = new AbortController();

                try {
                    const response = await fetch(`${routeProjectIndex}?${fetchParams.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        signal: pendingController.signal,
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load data');
                    }

                    const data = await response.json();
                    resultsEl.innerHTML = data.html || '';
                    applyProjectTableSearchAndSort();

                    const query = params.toString();
                    const nextUrl = query ? `${routeProjectIndex}?${query}` : routeProjectIndex;
                    history.replaceState(null, '', nextUrl);
                } catch (error) {
                    if (error.name === 'AbortError') return;
                    resultsEl.innerHTML =
                        '<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">โหลดข้อมูลไม่สำเร็จ กรุณาลองใหม่อีกครั้ง</div>';
                }
            }

            function scheduleLoad(delay = 250) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(loadResults, delay);
            }

            filterForm.addEventListener('submit', (event) => {
                event.preventDefault();
                loadResults();
            });

            modeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const nextMode = button.dataset.modeValue;
                    if (!nextMode || nextMode === modeSelect.value) return;
                    modeSelect.value = nextMode;
                    modeSelect.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                });
            });

            drawerModeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const nextMode = button.dataset.drawerModeValue;
                    if (!nextMode || nextMode === modeSelect.value) return;
                    modeSelect.value = nextMode;
                    modeSelect.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                });
            });

            modeSelect.addEventListener('change', () => {
                resetIrrelevantInputsForMode();
                updateFilterVisibility();
                syncDrawerFromPrimary();
                loadResults();
            });

            if (yearSelect) {
                yearSelect.addEventListener('change', () => {
                    syncDrawerFromPrimary();
                    loadResults();
                });
            }

            if (drawerYearSelect) {
                drawerYearSelect.addEventListener('change', () => {
                    if (yearSelect) yearSelect.value = drawerYearSelect.value;
                    syncDrawerFromPrimary();
                    loadResults();
                });
            }

            [startInput, endInput].forEach((input) => {
                input.addEventListener('input', () => {
                    syncDrawerFromPrimary();
                    scheduleLoad(350);
                });
                input.addEventListener('change', () => {
                    syncDrawerFromPrimary();
                    scheduleLoad(120);
                });
            });

            [
                [drawerDateStart, startInput],
                [drawerDateEnd, endInput],
            ].forEach(([drawerInput, primaryInput]) => {
                if (!drawerInput || !primaryInput) return;
                drawerInput.addEventListener('input', () => {
                    primaryInput.value = drawerInput.value;
                    syncDrawerFromPrimary();
                    scheduleLoad(350);
                });
                drawerInput.addEventListener('change', () => {
                    primaryInput.value = drawerInput.value;
                    syncDrawerFromPrimary();
                    scheduleLoad(120);
                });
            });

            if (projectSearchInput) {
                projectSearchInput.addEventListener('input', () => {
                    syncDrawerFromPrimary();
                    clearTimeout(tableDebounceTimer);
                    tableDebounceTimer = setTimeout(applyProjectTableSearchAndSort, 120);
                });
            }

            if (drawerProjectSearchInput) {
                drawerProjectSearchInput.addEventListener('input', () => {
                    if (projectSearchInput) projectSearchInput.value = drawerProjectSearchInput.value;
                    syncDrawerFromPrimary();
                    clearTimeout(tableDebounceTimer);
                    tableDebounceTimer = setTimeout(applyProjectTableSearchAndSort, 120);
                });
            }

            if (projectSortSelect) {
                projectSortSelect.addEventListener('change', () => {
                    syncDrawerFromPrimary();
                    applyProjectTableSearchAndSort();
                });
            }

            if (drawerProjectSortSelect) {
                drawerProjectSortSelect.addEventListener('change', () => {
                    if (projectSortSelect) projectSortSelect.value = drawerProjectSortSelect.value;
                    syncDrawerFromPrimary();
                    applyProjectTableSearchAndSort();
                });
            }

            function resetAllFilters() {
                modeSelect.value = 'month';
                if (yearSelect) yearSelect.value = '';
                startInput.value = '';
                endInput.value = '';
                if (projectSearchInput) projectSearchInput.value = '';
                if (projectSortSelect) projectSortSelect.value = 'filtered_desc';
                if (drawerProjectSearchInput) drawerProjectSearchInput.value = '';
                if (drawerProjectSortSelect) drawerProjectSortSelect.value = 'filtered_desc';
                updateFilterVisibility();
                syncDrawerFromPrimary();
                loadResults();
            }

            clearFiltersBtn.addEventListener('click', resetAllFilters);
            if (drawerClearFiltersBtn) {
                drawerClearFiltersBtn.addEventListener('click', resetAllFilters);
            }

            if (filterFab) {
                filterFab.addEventListener('click', openFilterDrawer);
            }

            if (drawerCloseBtn) {
                drawerCloseBtn.addEventListener('click', closeFilterDrawer);
            }

            if (drawerOverlay) {
                drawerOverlay.addEventListener('click', closeFilterDrawer);
            }

            window.addEventListener('scroll', updateFilterFab, {
                passive: true
            });
            window.addEventListener('resize', updateFilterFab);

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && drawerOpen) {
                    closeFilterDrawer();
                }
            });

            updateFilterVisibility();
            syncDrawerFromPrimary();
            applyProjectTableSearchAndSort();
        });
    </script>
@endsection

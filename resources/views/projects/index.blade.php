@extends('layouts.app')

@section('content')
    @php
        $activeFilterCount = collect(request()->query())
            ->reject(fn($value, $key) => in_array($key, ['page', 'sort', 'dir'], true))
            ->filter(fn($value) => filled($value))
            ->count();
    @endphp

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
    <style>
        .budget-range {
            position: relative;
        }

        .budget-range input[type='range'] {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 2rem;
            background: transparent;
            pointer-events: auto;
        }

        .budget-range input[type='range']::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            background: #10b981;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.2);
            pointer-events: auto;
            cursor: pointer;
        }

        .budget-range input[type='range']::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            background: #10b981;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.2);
            pointer-events: auto;
            cursor: pointer;
        }

        .budget-range input[type='range']::-webkit-slider-runnable-track {
            background: transparent;
        }

        .budget-range input[type='range']::-moz-range-track {
            background: transparent;
        }

        .budget-range-label {
            position: absolute;
            top: 28px;
            transform: translateX(-50%);
            background: #ffffff;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 12px;
            color: #0f172a;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.12);
            white-space: nowrap;
            z-index: 5;
            pointer-events: none;
        }

        .project-table {
            table-layout: fixed;
            min-width: 1040px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .project-table th {
            white-space: nowrap;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.02em;
            color: #475569;
            background: #f8fafc;
            border-bottom: 1px solid #dbe4ef;
        }

        .project-table td {
            vertical-align: top;
            border-bottom: 1px solid #e8eef6;
        }

        .project-table tbody tr:nth-child(even) {
            background: #fbfdff;
        }

        .project-table tbody tr:hover {
            background: #eef2ff;
        }

        .project-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .project-id-cell {
            position: relative;
            padding-bottom: 3.45rem;
        }

        .project-id-meta {
            position: absolute;
            right: 1rem;
            bottom: 0.75rem;
            left: 1rem;
            color: #64748b;
            font-size: 0.72rem;
            line-height: 1.25;
        }

        .project-id-year {
            color: #334155;
            font-size: 0.82rem;
            font-weight: 800;
        }

        .project-type-subline {
            margin-top: 0.25rem;
        }

        .cell-clamp-2,
        .cell-clamp-3,
        .cell-clamp-4,
        .cell-clamp-5 {
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

        .cell-clamp-4 {
            -webkit-line-clamp: 4;
        }

        .cell-clamp-5 {
            -webkit-line-clamp: 5;
        }

        .status-box {
            display: block;
            width: 100%;
            padding: 6px 8px;
            border-radius: 6px;
            line-height: 1.3;
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        .project-filter-anchor {
            scroll-margin-top: 96px;
        }

        #project-filter-form {
            position: relative;
            overflow: visible;
            border: 1px solid #dbe4ef;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 18px 46px rgba(15, 23, 42, 0.08);
        }

        #project-filter-form span.text-xs.font-semibold {
            color: #475569;
            font-weight: 800;
        }

        #project-filter-form input[type='text'],
        #project-filter-form select,
        #project-filter-form button[data-project-dropdown-toggle] {
            min-height: 38px;
            border-color: #dbe4ef;
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.03);
            transition: border-color 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease, transform 0.16s ease;
        }

        #project-filter-form input[type='text']:focus,
        #project-filter-form select:focus,
        #project-filter-form button[data-project-dropdown-toggle]:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.14);
            outline: none;
        }

        #project-filter-form input[type='checkbox'] {
            width: 0.9rem;
            height: 0.9rem;
            border-radius: 4px;
            accent-color: #4f46e5;
        }

        .project-filter-issue-group {
            min-height: 38px;
            align-content: center;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            background: rgba(248, 250, 252, 0.72);
            padding: 0.5rem;
        }

        .project-filter-actions {
            align-items: stretch;
            justify-content: flex-end;
        }

        .project-filter-actions > span {
            justify-content: center;
            border-color: #dbe4ef;
            background: #f8fafc;
        }

        #project-filter-form button[type='submit'] {
            min-height: 38px;
            border-radius: 10px;
            background: #4f46e5;
            font-weight: 800;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.18);
        }

        #project-filter-form button[type='submit']:hover {
            background: #4338ca;
        }

        #project-filter-reset {
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        #project-table-shell {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
        }

        #project-table-shell > .border-b {
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .project-table a {
            text-decoration-thickness: 1px;
            text-underline-offset: 3px;
        }

        .project-filter-anchor grammarly-extension,
        .project-filter-anchor grammarly-desktop-integration,
        .project-filter-anchor [data-grammarly-part],
        .project-filter-anchor .grammarly-desktop-integration,
        .project-filter-anchor .lt-toolbar,
        .project-filter-anchor .lt-toolbar__wrapper,
        .project-filter-anchor .languagetool-tooltip,
        .project-filter-anchor .languagetool-popup,
        .project-filter-anchor [data-lt-active],
        .project-filter-anchor [data-lt-installed] {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        .project-filter-drawer-overlay {
            position: fixed;
            inset: 0;
            z-index: 48;
            background: rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(6px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.18s ease;
        }

        .project-filter-drawer-overlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .project-filter-drawer {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 49;
            width: min(92vw, 400px);
            transform: translateX(-100%);
            transition: transform 0.22s ease;
        }

        .project-filter-drawer.is-open {
            transform: translateX(0);
        }

        .project-filter-fab {
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

        .project-filter-fab.is-visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .project-filter-fab.is-visible:hover {
            transform: translateY(-2px);
            border-color: #67e8f9;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.16);
        }

        .project-filter-fab:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.18), 0 20px 40px rgba(15, 23, 42, 0.16);
        }

        .project-filter-fab__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0f172a 0%, #4338ca 100%);
            color: #ffffff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
        }

        .project-filter-fab__title {
            font-size: 0.74rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
        }

        .project-filter-fab__meta {
            font-size: 0.61rem;
            color: #64748b;
            line-height: 1.1;
            text-align: center;
        }

        .project-filter-fab__badge {
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

        .project-filter-fab__badge.hidden {
            display: none;
        }

        .project-filter-dropdown {
            position: relative;
        }

        .project-filter-dropdown__menu {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 65;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
            padding: 0.5rem;
        }

        .project-filter-dropdown__menu.hidden {
            display: none;
        }

        .project-filter-dropdown__options {
            max-height: 14rem;
            overflow-y: scroll;
            scrollbar-gutter: stable;
        }

        .project-filter-dropdown__options::-webkit-scrollbar {
            width: 10px;
        }

        .project-filter-dropdown__options::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 999px;
        }

        .project-filter-dropdown__options::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 999px;
            border: 2px solid #f1f5f9;
        }

        .project-role-hit {
            display: inline-flex;
            align-items: center;
            margin-top: 0.35rem;
            padding: 0.18rem 0.48rem;
            border-radius: 999px;
            border: 1px solid #fca5a5;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.25;
        }

        @media (max-width: 1024px) {
            .project-filter-fab {
                width: 68px;
                min-height: 76px;
                padding: 0.65rem 0.45rem;
                border-radius: 20px;
            }

            .project-filter-fab__icon {
                width: 36px;
                height: 36px;
                border-radius: 15px;
            }

            .project-filter-fab__title {
                font-size: 0.68rem;
            }

            .project-filter-fab__meta {
                font-size: 0.58rem;
            }
        }

        @media (max-width: 640px) {
            .project-filter-fab {
                width: 58px;
                min-height: 58px;
                padding: 8px;
                border-radius: 18px;
                gap: 0;
            }

            .project-filter-fab__icon {
                width: 34px;
                height: 34px;
                border-radius: 14px;
            }

            .project-filter-fab__title,
            .project-filter-fab__meta {
                display: none;
            }

            .project-filter-fab__badge {
                min-width: 1.25rem;
                height: 1.25rem;
                font-size: 0.62rem;
            }
        }
    </style>

    <div class="container mx-auto px-4 mt-11 space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">📊 Research Project</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-amber-300 bg-amber-200 px-4 py-2 text-sm font-semibold text-slate-950 shadow-sm transition hover:border-amber-400 hover:bg-amber-300 hover:shadow">
                    ➕ เพิ่มโครงการ
                </a>
            </div>
        </div>

        {{-- Filters --}}

        <form id="project-filter-form" method="GET" action="{{ route('projects.index') }}"
            class="project-filter-anchor bg-white p-4 rounded-xl shadow-sm ring-1 ring-slate-100 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ค้นหาชื่อโครงการ / Project ID</span>
                    <input id="project-search-input" type="text" name="search"
                        placeholder="ชื่อโครงการ หรือ Project ID (รองรับคำใกล้เคียง)" value="{{ request('search') }}"
                        data-project-live="true"
                        class="w-full border border-slate-200 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2 p-2 rounded-lg">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ประเภทโครงการ</span>
                    <select name="type" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        @foreach ($typeOptions as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                    <select name="year" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">PI</span>
                    <div class="project-filter-dropdown" data-project-filter-dropdown>
                        <input type="hidden" name="pi_name" value="{{ request('pi_name') }}" data-project-live="true" data-project-dropdown-value>
                        <button type="button" data-project-dropdown-toggle
                            class="w-full border border-slate-200 bg-white text-left p-2 rounded-lg flex items-center justify-between gap-2 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2">
                            <span class="truncate" data-project-dropdown-label>ทั้งหมด</span>
                            <span class="text-slate-400">▾</span>
                        </button>
                        <div class="project-filter-dropdown__menu hidden" data-project-dropdown-menu>
                            <input type="text" data-project-dropdown-search placeholder="พิมพ์ค้นหา..."
                                class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                            <div class="project-filter-dropdown__options mt-2 space-y-1" data-project-dropdown-options>
                                <button type="button" data-project-dropdown-option data-value=""
                                    class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">ทั้งหมด</button>
                                @foreach ($piOptions as $pi)
                                    <button type="button" data-project-dropdown-option data-value="{{ $pi }}"
                                        class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">{{ $pi }}</button>
                                @endforeach
                                <div class="hidden px-2 py-1.5 text-xs text-slate-500" data-project-dropdown-empty>ไม่พบข้อมูล</div>
                            </div>
                        </div>
                    </div>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">บุคคลในโครงการ</span>
                    <div class="project-filter-dropdown" data-project-filter-dropdown>
                        <input type="hidden" name="person_name" value="{{ $selectedPersonName ?? '' }}" data-project-live="true" data-project-dropdown-value>
                        <button type="button" data-project-dropdown-toggle
                            class="w-full border border-slate-200 bg-white text-left p-2 rounded-lg flex items-center justify-between gap-2 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2">
                            <span class="truncate" data-project-dropdown-label>ทั้งหมด</span>
                            <span class="text-slate-400">▾</span>
                        </button>
                        <div class="project-filter-dropdown__menu hidden" data-project-dropdown-menu>
                            <input type="text" data-project-dropdown-search placeholder="พิมพ์ค้นหา..."
                                class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                            <div class="project-filter-dropdown__options mt-2 space-y-1" data-project-dropdown-options>
                                <button type="button" data-project-dropdown-option data-value=""
                                    class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">ทั้งหมด</button>
                                @foreach (($personOptions ?? []) as $person)
                                    <button type="button" data-project-dropdown-option data-value="{{ $person['value'] }}"
                                        class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">{{ $person['label'] }}</button>
                                @endforeach
                                <div class="hidden px-2 py-1.5 text-xs text-slate-500" data-project-dropdown-empty>ไม่พบข้อมูล</div>
                            </div>
                        </div>
                    </div>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">แหล่งทุน</span>
                    <div class="project-filter-dropdown" data-project-filter-dropdown>
                        <input type="hidden" name="funding_source" value="{{ request('funding_source') }}" data-project-live="true" data-project-dropdown-value>
                        <button type="button" data-project-dropdown-toggle
                            class="w-full border border-slate-200 bg-white text-left p-2 rounded-lg flex items-center justify-between gap-2 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2">
                            <span class="truncate" data-project-dropdown-label>ทั้งหมด</span>
                            <span class="text-slate-400">▾</span>
                        </button>
                        <div class="project-filter-dropdown__menu hidden" data-project-dropdown-menu>
                            <input type="text" data-project-dropdown-search placeholder="พิมพ์ค้นหา..."
                                class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                            <div class="project-filter-dropdown__options mt-2 space-y-1" data-project-dropdown-options>
                                <button type="button" data-project-dropdown-option data-value=""
                                    class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">ทั้งหมด</button>
                                @foreach ($fundingSources as $src)
                                    <button type="button" data-project-dropdown-option data-value="{{ $src }}"
                                        class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">{{ $src }}</button>
                                @endforeach
                                <div class="hidden px-2 py-1.5 text-xs text-slate-500" data-project-dropdown-empty>ไม่พบข้อมูล</div>
                            </div>
                        </div>
                    </div>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ช่วงงบประมาณ (preset)</span>
                    <select name="budget_range" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        <option value="0-500000" @selected(request('budget_range') === '0-500000')>0 - 500,000</option>
                        <option value="500000-1000000" @selected(request('budget_range') === '500000-1000000')>500,000 - 1,000,000</option>
                        <option value="1000000-3000000" @selected(request('budget_range') === '1000000-3000000')>1,000,000 - 3,000,000</option>
                        <option value="3000000-5000000" @selected(request('budget_range') === '3000000-5000000')>3,000,000 - 5,000,000</option>
                        <option value="5000000-999999999" @selected(request('budget_range') === '5000000-999999999')>มากกว่า 5,000,000</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">สถานะโครงการ (Project Status)</span>
                    <select name="project_status" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        <option value="__unspecified__" @selected(request('project_status') === '__unspecified__')>ยังไม่ได้ระบุ</option>
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}" @selected(request('project_status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">สถานะ FTE</span>
                    <select name="fte_status" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        <option value="missing" @selected(request('fte_status') === 'missing')>ยังกรอกไม่ครบ</option>
                        <option value="complete" @selected(request('fte_status') === 'complete')>กรอกครบแล้ว</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ไม่อัปเดท</span>
                    <select name="last_update" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        <option value="1m" @selected(request('last_update') === '1m')>1 เดือน</option>
                        <option value="2m" @selected(request('last_update') === '2m')>2 เดือน</option>
                        <option value="3m" @selected(request('last_update') === '3m')>3 เดือน</option>
                        <option value="gt3" @selected(request('last_update') === 'gt3')>มากกว่า 3 เดือน</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ปัญหา</span>
                    <div class="flex flex-wrap gap-2 text-xs text-slate-600">
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" name="issue_overdue" value="1" data-project-live="true" @checked(request('issue_overdue'))>
                            Overdue
                        </label>
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" name="issue_missing_dates" value="1" data-project-live="true" @checked(request('issue_missing_dates'))>
                            วันที่ไม่ครบ
                        </label>
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" name="issue_duplicates" value="1" data-project-live="true" @checked(request('issue_duplicates'))>
                            โครงการซ้ำ
                        </label>
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" name="issue_no_output" value="1" data-project-live="true" @checked(request('issue_no_output'))>
                            ไม่มี output
                        </label>
                    </div>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                    <select name="sort" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="updated_at" @selected(request('sort') === 'updated_at')>อัปเดทล่าสุด → เก่าสุด</option>
                        <option value="created_at" @selected(request('sort') === 'created_at')>สร้างล่าสุด → เก่าสุด</option>
                        <option value="budget" @selected(request('sort') === 'budget')>งบประมาณ</option>
                        <option value="start_year" @selected(request('sort') === 'start_year')>ปีโครงการ</option>
                        <option value="planning_status" @selected(request('sort') === 'planning_status')>สถานะโครงการ</option>
                        <option value="project_name_th" @selected(request('sort') === 'project_name_th')>ชื่อโครงการ</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ทิศทาง</span>
                    <select name="dir" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="desc" @selected(request('dir', 'desc') === 'desc')>มาก → น้อย</option>
                        <option value="asc" @selected(request('dir') === 'asc')>น้อย → มาก</option>
                    </select>
                </label>
                <div class="flex gap-2 items-center">
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600">
                        ใช้ฟิลเตอร์
                        <span id="project-filter-count" class="rounded-full bg-white px-2 py-0.5 text-slate-900 shadow-sm">{{ $activeFilterCount }}</span>
                    </span>
                    <button type="submit"
                        class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 shadow-sm">ใช้ตัวกรอง</button>
                    <a id="project-filter-reset" href="{{ route('projects.index') }}"
                        class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">ล้าง</a>
                </div>
            </div>
        </form>
        <div id="table-loading-indicator" class="hidden items-center gap-2 text-xs text-slate-500">
            <span class="inline-block h-3 w-3 rounded-full border-2 border-indigo-200 border-t-indigo-600 animate-spin"></span>
            กำลังอัปเดตตาราง...
        </div>

        {{-- Helpers: sort + color badge --}}
        @php
            $currentSort = request('sort', 'updated_at');
            $currentDir = request('dir', 'desc');
            $toggleDir = fn($field) => $currentSort === $field && $currentDir === 'asc' ? 'desc' : 'asc';
            $sortUrl = function ($field) use ($toggleDir) {
                return request()->fullUrlWithQuery(['sort' => $field, 'dir' => $toggleDir($field), 'page' => 1]);
            };
            $sortIcon = function ($field) use ($currentSort, $currentDir) {
                if ($currentSort !== $field) {
                    return '↕';
                }
                return $currentDir === 'asc' ? '↑' : '↓';
            };

            // แผนที่สี badge ตามประเภท (ถ้าไม่พบ ใช้สีกลาง)
            function type_badge_classes($type)
            {
                $label = trim((string) $type);
                if ($label === '') {
                    return 'bg-slate-200 text-slate-800 border border-slate-300';
                }

                $palette = [
                    ['bg' => 'bg-rose-100', 'text' => 'text-rose-800', 'border' => 'border-rose-200'],
                    ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-200'],
                    ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'border' => 'border-amber-200'],
                    ['bg' => 'bg-lime-100', 'text' => 'text-lime-800', 'border' => 'border-lime-200'],
                    ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'border' => 'border-emerald-200'],
                    ['bg' => 'bg-teal-100', 'text' => 'text-teal-800', 'border' => 'border-teal-200'],
                    ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-800', 'border' => 'border-cyan-200'],
                    ['bg' => 'bg-sky-100', 'text' => 'text-sky-800', 'border' => 'border-sky-200'],
                    ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-200'],
                    ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'border' => 'border-indigo-200'],
                    ['bg' => 'bg-violet-100', 'text' => 'text-violet-800', 'border' => 'border-violet-200'],
                    ['bg' => 'bg-fuchsia-100', 'text' => 'text-fuchsia-800', 'border' => 'border-fuchsia-200'],
                    ['bg' => 'bg-pink-100', 'text' => 'text-pink-800', 'border' => 'border-pink-200'],
                ];

                $normalized = mb_strtolower($label, 'UTF-8');
                $hash = hexdec(substr(md5($normalized), 0, 8));
                $tone = $palette[$hash % count($palette)];

                return "{$tone['bg']} {$tone['text']} border {$tone['border']}";
            }

            function funding_badge_classes($fundingType)
            {
                $t = trim(mb_strtolower((string) $fundingType, 'UTF-8'));
                if ($t === '') {
                    return 'bg-slate-200 text-slate-800';
                }
                if (str_contains($t, 'ภายใน')) {
                    return 'bg-emerald-200 text-emerald-900';
                }
                if (str_contains($t, 'ภายนอก')) {
                    return 'bg-sky-200 text-sky-900';
                }
                if (str_contains($t, 'ร่วม') || str_contains($t, 'co-') || str_contains($t, 'collab')) {
                    return 'bg-amber-200 text-amber-900';
                }
                return 'bg-slate-200 text-slate-800';
            }

            function project_status_badge_classes($status)
            {
                $s = trim(mb_strtolower((string) $status, 'UTF-8'));
                if ($s === '') {
                    return 'bg-slate-200 text-slate-800';
                }
                if (str_contains($s, 'ยกเลิก')) {
                    return 'bg-rose-200 text-rose-900';
                }
                if (str_contains($s, 'ปิดโปรเจค') || str_contains($s, 'เสร็จ')) {
                    return 'bg-emerald-200 text-emerald-900';
                }
                if (str_contains($s, 'เขียนรายงาน') || str_contains($s, 'publication')) {
                    return 'bg-indigo-200 text-indigo-900';
                }
                if (str_contains($s, 'วิเคราะห์')) {
                    return 'bg-violet-200 text-violet-900';
                }
                if (str_contains($s, 'เก็บข้อมูล')) {
                    return 'bg-sky-200 text-sky-900';
                }
                if (str_contains($s, 'plan') || str_contains($s, 'เริ่มต้น') || str_contains($s, 'submit irb')) {
                    return 'bg-amber-200 text-amber-900';
                }
                return 'bg-slate-200 text-slate-800';
            }
        @endphp


        @if ($missingFTE->count() > 0)
            <div id="fte-alert-box"
                class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl shadow-sm">

                <h3 class="font-semibold text-lg mb-2">⚠️ แจ้งเตือน: กรุณากรอกค่า FTE ของคุณ</h3>

                <p class="text-sm mb-3 font-medium">
                    คุณมีรายชื่อเป็นผู้ร่วมงานในโครงการด้านล่างนี้ แต่ยังไม่ได้กรอกค่า FTE โปรดดำเนินการโดยเร็วที่สุด
                    <span class=""><br>กดที่ชื่อโครงการเพื่อดูรายละเอียดได้ทันที</span>

                </p>

                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach ($missingFTE as $fte)
                        <li>
                            <a href="{{ route('projects.edit', $fte->project ?? $fte->project_id) }}"
                                class="text-indigo-700 font-medium hover:underline">
                                {{ $fte->project->project_name_th ?? 'ไม่พบชื่อโครงการ' }}
                            </a>

                            @php
                                $year = $fte->project->start_year ?? '-';
                                $code = $fte->project->project_code ?? 'null';
                                $id = $fte->project->id ?? '-';
                            @endphp

                            <span class="text-slate-500">
                                ({{ $year }})
                                – {{ $code }} (#{{ $id }})
                            </span>
                        </li>
                    @endforeach

                </ul>

                <div class="mt-3 text-right">
                    <button onclick="document.getElementById('fte-alert-box').classList.add('hidden');"
                        class="inline-block px-3 py-1.5 bg-slate-600 text-white text-xs rounded-md hover:bg-slate-700">
                        ปิดการแจ้งเตือน
                    </button>
                </div>
            </div>
        @endif

        @if(collect($personRoleSummary ?? [])->isNotEmpty())
            <div class="rounded-xl border border-sky-100 bg-sky-50 px-3 py-2">
                <div class="text-xs font-semibold text-sky-700">
                    บทบาทของ <span class="text-sky-900">{{ $selectedPersonName }}</span> ในผลลัพธ์หน้านี้
                </div>
                <div class="mt-1 flex flex-wrap gap-2">
                    @forelse(($personRoleSummary ?? []) as $item)
                        <span class="inline-flex items-center rounded-full bg-white border border-sky-200 px-2 py-0.5 text-xs text-sky-800">
                            {{ $item['role'] }}: {{ number_format((int) $item['count']) }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-500">ไม่พบบทบาทที่ตรงเงื่อนไข</span>
                    @endforelse
                </div>
            </div>
        @endif


        {{-- Table --}}
        <div id="project-table-shell" class="bg-white rounded-2xl shadow-md ring-1 ring-slate-100 overflow-hidden transition-opacity duration-200">
            <div class="px-4 py-3 border-b bg-slate-50 flex items-center justify-between">
                <div id="project-table-count" class="text-sm text-slate-600">
                    แสดงผล {{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }} จากทั้งหมด
                    {{ $projects->total() }} รายการ
                    <span id="project-table-count-meta" class="ml-2 text-xs text-slate-500">เรียงลำดับจากข้อมูลที่แก้ไขล่าสุด</span>
                </div>
                <div class="text-xs text-slate-400">ดับเบิลคลิก = ดูรายละเอียด • คลิกขวา/กดค้าง = เมนูด่วน</div>
            </div>

            <div class="overflow-auto">
                <table class="project-table min-w-full">
                    <colgroup>
                        <col style="width: 13%;"> {{-- Project ID + ปี + อัปเดท --}}
                        <col style="width: 58%;"> {{-- ชื่อโครงการ + Theme + ประเภท --}}
                        <col style="width: 11%;"> {{-- ทุน --}}
                        <col style="width: 12%;"> {{-- Status --}}
                        <col style="width: 6%;"> {{-- Outputs --}}
                    </colgroup>
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr class="text-left text-xs font-semibold text-slate-600">
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('project_code') }}" class="hover:text-indigo-700">Project ID <span
                                        class="ml-1">{{ $sortIcon('project_code') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('project_name_th') }}" class="hover:text-indigo-700">ชื่อโครงการ <span
                                        class="ml-1">{{ $sortIcon('project_name_th') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('funding_type') }}" class="hover:text-indigo-700">ทุน <span
                                        class="ml-1">{{ $sortIcon('funding_type') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('planning_status') }}" class="hover:text-indigo-700">สถานะโครงการ <span
                                        class="ml-1">{{ $sortIcon('planning_status') }}</span></a>
                            </th>
                            <th class="px-4 py-3 text-right">
                                Outputs
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($projects as $project)
                            @php
                                $relatedPersonNames = collect()
                                    ->merge(($project->fteRoles ?? collect())->pluck('name'))
                                    ->merge(($project->copis ?? collect())->pluck('name'))
                                    ->merge(($project->coResearches ?? collect())->pluck('name'))
                                    ->filter()
                                    ->map(fn($name) => trim((string) $name))
                                    ->filter()
                                    ->unique()
                                    ->implode(' ');
                                $selectedPersonRoles = collect();
                                $selectedPersonRaw = trim((string) ($selectedPersonName ?? ''));
                                if ($selectedPersonRaw !== '') {
                                    $selectedPersonLower = mb_strtolower($selectedPersonRaw, 'UTF-8');
                                    $selectedPersonCompact = preg_replace('/\s+/u', '', $selectedPersonLower) ?: $selectedPersonLower;
                                    $matchesSelectedPerson = function ($candidate) use ($selectedPersonLower, $selectedPersonCompact) {
                                        $candidateLower = mb_strtolower(trim((string) $candidate), 'UTF-8');
                                        if ($candidateLower === '') {
                                            return false;
                                        }
                                        if ($candidateLower === $selectedPersonLower) {
                                            return true;
                                        }
                                        $candidateCompact = preg_replace('/\s+/u', '', $candidateLower) ?: $candidateLower;
                                        return $candidateCompact === $selectedPersonCompact;
                                    };

                                    if ($matchesSelectedPerson($project->pi_name ?? '')) {
                                        $selectedPersonRoles->push('PI');
                                    }
                                    if (($project->copis ?? collect())->contains(fn ($item) => $matchesSelectedPerson($item->name ?? ''))) {
                                        $selectedPersonRoles->push('Co-PI');
                                    }
                                    if (($project->coResearches ?? collect())->contains(fn ($item) => $matchesSelectedPerson($item->name ?? ''))) {
                                        $selectedPersonRoles->push('Co-Research');
                                    }
                                    if (($project->fteRoles ?? collect())->contains(fn ($item) => $matchesSelectedPerson($item->name ?? ''))) {
                                        $selectedPersonRoles->push('FTE');
                                    }
                                    $selectedPersonRoles = $selectedPersonRoles->unique()->values();
                                }
                                $showUrl = route('projects.show', $project);
                                $editUrl = route('projects.edit', $project);
                                $pdfUrl = route('projects.pdf', $project);
                                $progressUrl = route('projects.progress_reports.index', $project);
                                $fteProjectUrl = route('fte.project.detail', $project);
                                $deleteFormId = "delete-form-{$project->id}";
                            @endphp
                            <tr class="hover:bg-indigo-50/40 transition cursor-default" data-show-url="{{ $showUrl }}"
                                data-edit-url="{{ $editUrl }}" data-pdf-url="{{ $pdfUrl }}"
                                data-progress-url="{{ $progressUrl }}" data-fte-project-url="{{ $fteProjectUrl }}"
                                data-delete-form-id="{{ $deleteFormId }}"
                                data-project-name="{{ $project->project_name_th }}"
                                data-project-search-text="{{ trim(implode(' ', array_filter([$project->project_code, $project->project_name_th, $project->theme, $project->project_type, $project->funding_type, $project->planning_status, $project->start_year, $relatedPersonNames]))) }}"
                                oncontextmenu="return openRowMenu(event, this)" ondblclick="goToDetail(this)"
                                ontouchstart="rowLongPressStart(event, this)" ontouchend="rowLongPressEnd(event)">
                                <td class="project-id-cell px-4 py-3 text-sm font-mono text-slate-700">
                                    @php
                                        $updated = $project->updated_at ? \Carbon\Carbon::parse($project->updated_at) : null;
                                    @endphp
                                    <a href="{{ $showUrl }}" class="hover:underline">{{ $project->project_code }}</a>
                                    <div class="project-id-meta">
                                        <div class="project-id-year">ปีโปรเจค : {{ $project->start_year ?: '-' }}</div>
                                        <div>อัพเดท : {{ $updated ? $updated->locale('th')->translatedFormat('j M') . ' ' . ($updated->year + 543) : '-' }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ $showUrl }}"
                                        class="text-slate-900 hover:text-indigo-700 font-medium cell-clamp-5"
                                        title="{{ $project->project_name_th }}">
                                        {{ $project->project_name_th }}
                                    </a>
                                    @if(!empty($selectedPersonName) && $selectedPersonRoles->isNotEmpty())
                                        <div class="project-role-hit">
                                            (มีรายชื่อใน {{ $selectedPersonRoles->implode(', ') }})
                                        </div>
                                    @endif
                                    <div class="mt-1 text-xs text-slate-500 cell-clamp-2" title="{{ $project->theme ?: 'ไม่ระบุ Theme' }}">
                                        Theme: {{ $project->theme ?: 'ไม่ระบุ' }}
                                    </div>
                                    <span
                                        class="project-type-subline inline-flex items-center px-2 py-0.5 rounded-none text-xs font-medium {{ type_badge_classes($project->project_type) }}">
                                        <span class="cell-clamp-2" title="{{ $project->project_type ?: '-' }}">
                                            {{ $project->project_type ?: '-' }}
                                        </span>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ funding_badge_classes($project->funding_type) }}">
                                        {{ $project->funding_type ?: 'ยังไม่ได้ระบุ' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-normal text-slate-700">
                                    @php
                                        $projectStatus = trim((string) ($project->planning_status ?? ''));
                                    @endphp
                                    <span
                                        class="status-box text-xs font-semibold {{ project_status_badge_classes($projectStatus) }}">
                                        {{ $projectStatus !== '' ? $projectStatus : 'ยังไม่ได้ระบุ' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-700">
                                    @if (($project->outputs_count ?? 0) > 0)
                                        {{ $project->outputs_count }}
                                    @else
                                        <span class="inline-flex items-center justify-end rounded-none border border-amber-200 bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-700">
                                            ไม่มี output
                                        </span>
                                    @endif
                                </td>

                                {{-- hidden form for delete --}}
                                <td class="hidden">
                                    <form id="{{ $deleteFormId }}"
                                        action="{{ route('projects.destroy', $project) }}" method="POST">
                                        @csrf @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-slate-500">ไม่มีข้อมูล</td>
                            </tr>
                        @endforelse
                        <tr id="project-table-empty-search" class="hidden">
                            <td colspan="5" class="text-center py-10 text-slate-500">ไม่มีข้อมูลตรงกับคำค้นหาในหน้านี้</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="project-table-pagination-wrap" class="px-4 py-3 border-t bg-slate-50">
                {{ $projects->links('vendor.pagination.th') }}
            </div>
        </div>
    </div>

    <div id="projectFilterDrawerOverlay" class="project-filter-drawer-overlay"></div>
    <aside id="projectFilterDrawer" class="project-filter-drawer" aria-hidden="true">
        <div class="flex h-full flex-col border-r border-slate-200 bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-4 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">คัดกรองโครงการ</h3>
                    <p class="mt-1 text-xs text-slate-500">ค่าที่เลือกจะ sync กับฟิลเตอร์ด้านบนและอัปเดตตารางทันที</p>
                </div>
                <button id="projectFilterDrawerClose" type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                    <span class="sr-only">ปิดฟิลเตอร์</span>
                    <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                </button>
            </div>

            <form id="project-filter-drawer-form" method="GET" action="{{ route('projects.index') }}" class="flex h-full flex-col">
                <div class="flex-1 space-y-4 overflow-y-auto px-4 py-4">
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ค้นหาชื่อโครงการ / Project ID</span>
                        <input id="project-drawer-search-input" type="text" name="search"
                            placeholder="ชื่อโครงการ หรือ Project ID (รองรับคำใกล้เคียง)" value="{{ request('search') }}"
                            data-project-live="true"
                            class="w-full border border-slate-200 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2 p-2 rounded-lg">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ประเภทโครงการ</span>
                        <select name="type" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="">ทั้งหมด</option>
                            @foreach ($typeOptions as $type)
                                <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                        <select name="year" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="">ทั้งหมด</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">PI</span>
                        <div class="project-filter-dropdown" data-project-filter-dropdown>
                            <input type="hidden" name="pi_name" value="{{ request('pi_name') }}" data-project-live="true" data-project-dropdown-value>
                            <button type="button" data-project-dropdown-toggle
                                class="w-full border border-slate-200 bg-white text-left p-2 rounded-lg flex items-center justify-between gap-2 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2">
                                <span class="truncate" data-project-dropdown-label>ทั้งหมด</span>
                                <span class="text-slate-400">▾</span>
                            </button>
                            <div class="project-filter-dropdown__menu hidden" data-project-dropdown-menu>
                                <input type="text" data-project-dropdown-search placeholder="พิมพ์ค้นหา..."
                                    class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                                <div class="project-filter-dropdown__options mt-2 space-y-1" data-project-dropdown-options>
                                    <button type="button" data-project-dropdown-option data-value=""
                                        class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">ทั้งหมด</button>
                                    @foreach ($piOptions as $pi)
                                        <button type="button" data-project-dropdown-option data-value="{{ $pi }}"
                                            class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">{{ $pi }}</button>
                                    @endforeach
                                    <div class="hidden px-2 py-1.5 text-xs text-slate-500" data-project-dropdown-empty>ไม่พบข้อมูล</div>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">บุคคลในโครงการ</span>
                        <div class="project-filter-dropdown" data-project-filter-dropdown>
                            <input type="hidden" name="person_name" value="{{ $selectedPersonName ?? '' }}" data-project-live="true" data-project-dropdown-value>
                            <button type="button" data-project-dropdown-toggle
                                class="w-full border border-slate-200 bg-white text-left p-2 rounded-lg flex items-center justify-between gap-2 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2">
                                <span class="truncate" data-project-dropdown-label>ทั้งหมด</span>
                                <span class="text-slate-400">▾</span>
                            </button>
                            <div class="project-filter-dropdown__menu hidden" data-project-dropdown-menu>
                                <input type="text" data-project-dropdown-search placeholder="พิมพ์ค้นหา..."
                                    class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                                <div class="project-filter-dropdown__options mt-2 space-y-1" data-project-dropdown-options>
                                    <button type="button" data-project-dropdown-option data-value=""
                                        class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">ทั้งหมด</button>
                                    @foreach (($personOptions ?? []) as $person)
                                        <button type="button" data-project-dropdown-option data-value="{{ $person['value'] }}"
                                            class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">{{ $person['label'] }}</button>
                                    @endforeach
                                    <div class="hidden px-2 py-1.5 text-xs text-slate-500" data-project-dropdown-empty>ไม่พบข้อมูล</div>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">แหล่งทุน</span>
                        <div class="project-filter-dropdown" data-project-filter-dropdown>
                            <input type="hidden" name="funding_source" value="{{ request('funding_source') }}" data-project-live="true" data-project-dropdown-value>
                            <button type="button" data-project-dropdown-toggle
                                class="w-full border border-slate-200 bg-white text-left p-2 rounded-lg flex items-center justify-between gap-2 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2">
                                <span class="truncate" data-project-dropdown-label>ทั้งหมด</span>
                                <span class="text-slate-400">▾</span>
                            </button>
                            <div class="project-filter-dropdown__menu hidden" data-project-dropdown-menu>
                                <input type="text" data-project-dropdown-search placeholder="พิมพ์ค้นหา..."
                                    class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                                <div class="project-filter-dropdown__options mt-2 space-y-1" data-project-dropdown-options>
                                    <button type="button" data-project-dropdown-option data-value=""
                                        class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">ทั้งหมด</button>
                                    @foreach ($fundingSources as $src)
                                        <button type="button" data-project-dropdown-option data-value="{{ $src }}"
                                            class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">{{ $src }}</button>
                                    @endforeach
                                    <div class="hidden px-2 py-1.5 text-xs text-slate-500" data-project-dropdown-empty>ไม่พบข้อมูล</div>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ช่วงงบประมาณ (preset)</span>
                        <select name="budget_range" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="">ทั้งหมด</option>
                            <option value="0-500000" @selected(request('budget_range') === '0-500000')>0 - 500,000</option>
                            <option value="500000-1000000" @selected(request('budget_range') === '500000-1000000')>500,000 - 1,000,000</option>
                            <option value="1000000-3000000" @selected(request('budget_range') === '1000000-3000000')>1,000,000 - 3,000,000</option>
                            <option value="3000000-5000000" @selected(request('budget_range') === '3000000-5000000')>3,000,000 - 5,000,000</option>
                            <option value="5000000-999999999" @selected(request('budget_range') === '5000000-999999999')>มากกว่า 5,000,000</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">สถานะโครงการ (Project Status)</span>
                        <select name="project_status" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="">ทั้งหมด</option>
                            <option value="__unspecified__" @selected(request('project_status') === '__unspecified__')>ยังไม่ได้ระบุ</option>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}" @selected(request('project_status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">สถานะ FTE</span>
                        <select name="fte_status" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="">ทั้งหมด</option>
                            <option value="missing" @selected(request('fte_status') === 'missing')>ยังกรอกไม่ครบ</option>
                            <option value="complete" @selected(request('fte_status') === 'complete')>กรอกครบแล้ว</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ไม่อัปเดท</span>
                        <select name="last_update" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="">ทั้งหมด</option>
                            <option value="1m" @selected(request('last_update') === '1m')>1 เดือน</option>
                            <option value="2m" @selected(request('last_update') === '2m')>2 เดือน</option>
                            <option value="3m" @selected(request('last_update') === '3m')>3 เดือน</option>
                            <option value="gt3" @selected(request('last_update') === 'gt3')>มากกว่า 3 เดือน</option>
                        </select>
                    </label>
                    <div class="block">
                        <span class="text-xs font-semibold text-slate-600">ปัญหา</span>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                            <label class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1.5">
                                <input type="checkbox" name="issue_overdue" value="1" data-project-live="true" @checked(request('issue_overdue'))>
                                Overdue
                            </label>
                            <label class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1.5">
                                <input type="checkbox" name="issue_missing_dates" value="1" data-project-live="true" @checked(request('issue_missing_dates'))>
                                วันที่ไม่ครบ
                            </label>
                            <label class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1.5">
                                <input type="checkbox" name="issue_duplicates" value="1" data-project-live="true" @checked(request('issue_duplicates'))>
                                โครงการซ้ำ
                            </label>
                            <label class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1.5">
                                <input type="checkbox" name="issue_no_output" value="1" data-project-live="true" @checked(request('issue_no_output'))>
                                ไม่มี output
                            </label>
                        </div>
                    </div>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                        <select name="sort" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="updated_at" @selected(request('sort') === 'updated_at')>อัปเดทล่าสุด → เก่าสุด</option>
                            <option value="created_at" @selected(request('sort') === 'created_at')>สร้างล่าสุด → เก่าสุด</option>
                            <option value="budget" @selected(request('sort') === 'budget')>งบประมาณ</option>
                            <option value="start_year" @selected(request('sort') === 'start_year')>ปีโครงการ</option>
                            <option value="planning_status" @selected(request('sort') === 'planning_status')>สถานะโครงการ</option>
                            <option value="project_name_th" @selected(request('sort') === 'project_name_th')>ชื่อโครงการ</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ทิศทาง</span>
                        <select name="dir" data-project-live="true" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="desc" @selected(request('dir', 'desc') === 'desc')>มาก → น้อย</option>
                            <option value="asc" @selected(request('dir') === 'asc')>น้อย → มาก</option>
                        </select>
                    </label>
                </div>
                <div class="border-t border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit"
                            class="flex-1 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                            ใช้ตัวกรอง
                        </button>
                        <a id="project-filter-drawer-reset" href="{{ route('projects.index') }}"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            ล้าง
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </aside>

    <button id="projectFilterFab" type="button" aria-label="เปิดฟิลเตอร์" class="project-filter-fab" data-project-open-drawer>
        <span class="project-filter-fab__icon" aria-hidden="true">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                <path d="M4 6h16M7 12h10m-7 6h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </span>
        <span class="project-filter-fab__title">ฟิลเตอร์</span>
        <span class="project-filter-fab__meta">เปิดแผง</span>
        <span id="projectFilterFabBadge" class="project-filter-fab__badge @if ($activeFilterCount === 0) hidden @endif">{{ $activeFilterCount }}</span>
    </button>

    {{-- Context Menu --}}
    <div id="row-menu"
        class="hidden fixed z-50 w-[188px] max-w-[88vw] max-h-[70vh] overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg ring-1 ring-black/5">
        <div class="px-3 pt-2 pb-1 border-b border-slate-100 bg-slate-50/80">
            <div class="text-[10px] font-normal text-slate-400">โครงการ</div>
            <div id="row-menu-project-name" class="text-[11px] font-normal text-slate-600 leading-snug cell-clamp-2">-</div>
        </div>
        <div class="py-1 text-sm">
            <button type="button" data-action="show" class="w-full text-left px-3 py-2 hover:bg-indigo-50">🔎
                ดูรายละเอียด</button>
            <button type="button" data-action="show-new-tab" class="w-full text-left px-3 py-2 hover:bg-indigo-50">🗂️
                เปิดข้อมูลในแท็บใหม่</button>
            <button type="button" data-action="copy-project-name" class="w-full text-left px-3 py-2 hover:bg-indigo-50">📋
                คัดลอกชื่อโครงการ</button>
            <button type="button" data-action="edit" class="w-full text-left px-3 py-2 hover:bg-indigo-50">✏️
                แก้ไข</button>
            <button type="button" data-action="pdf"
                class="w-full text-left px-3 py-2 hover:bg-green-50 text-green-700">🖨️ พิมพ์ PDF</button>
            <button type="button" data-action="progress"
                class="w-full text-left px-3 py-2 hover:bg-sky-50 text-sky-700">📑 Progress Report</button>
            <button type="button" data-action="fte-project"
                class="w-full text-left px-3 py-2 hover:bg-emerald-50 text-emerald-700">📊 FTE โครงการ</button>
            <div class="my-1 border-t border-slate-100"></div>
            <button type="button" data-action="delete"
                class="w-full text-left px-3 py-2 hover:bg-red-50 text-red-600">🗑️ ลบข้อมูล</button>
        </div>
    </div>

    <script>
        function goToDetail(row) {
            const url = row.getAttribute('data-show-url');
            if (url) location.href = url;
        }

        function openRowMenu(e, row) {
            e.preventDefault();
            const menu = document.getElementById('row-menu');
            if (!menu) return;

            if (menu.parentElement !== document.body) {
                document.body.appendChild(menu);
            }

            menu.dataset.showUrl = row.getAttribute('data-show-url') || '';
            menu.dataset.editUrl = row.getAttribute('data-edit-url') || '';
            menu.dataset.pdfUrl = row.getAttribute('data-pdf-url') || '';
            menu.dataset.progressUrl = row.getAttribute('data-progress-url') || '';
            menu.dataset.fteProjectUrl = row.getAttribute('data-fte-project-url') || '';
            menu.dataset.deleteFormId = row.getAttribute('data-delete-form-id') || '';
            menu.dataset.projectName = row.getAttribute('data-project-name') || '';

            const nameLabel = document.getElementById('row-menu-project-name');
            if (nameLabel) {
                nameLabel.textContent = menu.dataset.projectName || '-';
                nameLabel.title = menu.dataset.projectName || '';
            }

            const margin = 8;
            menu.style.left = '0px';
            menu.style.top = '0px';
            menu.style.visibility = 'hidden';
            menu.classList.remove('hidden');

            const measured = menu.getBoundingClientRect();
            let x = e.clientX;
            let y = e.clientY;
            if (x + measured.width + margin > innerWidth) {
                x = innerWidth - measured.width - margin;
            }
            if (y + measured.height + margin > innerHeight) {
                y = innerHeight - measured.height - margin;
            }

            x = Math.max(margin, x);
            y = Math.max(margin, y);
            menu.style.left = `${x}px`;
            menu.style.top = `${y}px`;
            menu.style.visibility = 'visible';

            setTimeout(() => {
                document.addEventListener('click', clickAwayClose, {
                    once: true
                });
                addEventListener('scroll', hideMenu, {
                    once: true
                });
                addEventListener('resize', hideMenu, {
                    once: true
                });
                document.addEventListener('keydown', escClose, {
                    once: true
                });
            }, 0);
            return false;
        }

        function hideMenu() {
            const m = document.getElementById('row-menu');
            if (m) {
                m.classList.add('hidden');
                m.style.visibility = '';
            }
        }

        function clickAwayClose(ev) {
            const m = document.getElementById('row-menu');
            if (m && !m.contains(ev.target)) hideMenu();
        }

        function escClose(ev) {
            if (ev.key === 'Escape') hideMenu();
        }

        async function copyTextToClipboard(text) {
            if (!text) return false;

            if (navigator.clipboard && window.isSecureContext) {
                try {
                    await navigator.clipboard.writeText(text);
                    return true;
                } catch (error) {
                    // fallback below
                }
            }

            const temp = document.createElement('textarea');
            temp.value = text;
            temp.setAttribute('readonly', '');
            temp.style.position = 'fixed';
            temp.style.top = '-9999px';
            temp.style.left = '-9999px';
            document.body.appendChild(temp);
            temp.select();
            temp.setSelectionRange(0, text.length);

            let copied = false;
            try {
                copied = document.execCommand('copy');
            } catch (error) {
                copied = false;
            }
            document.body.removeChild(temp);
            return copied;
        }

        (function bindMenu() {
            const m = document.getElementById('row-menu');
            if (!m) return;
            m.addEventListener('click', async (ev) => {
                const btn = ev.target.closest('button[data-action]');
                if (!btn) return;
                const act = btn.dataset.action,
                    showUrl = m.dataset.showUrl,
                    editUrl = m.dataset.editUrl,
                    pdfUrl = m.dataset.pdfUrl,
                    progressUrl = m.dataset.progressUrl,
                    fteProjectUrl = m.dataset.fteProjectUrl,
                    delId = m.dataset.deleteFormId,
                    projectName = m.dataset.projectName || '';
                hideMenu();
                switch (act) {
                    case 'show':
                        if (showUrl) location.href = showUrl;
                        break;
                    case 'show-new-tab':
                        if (showUrl) window.open(showUrl, '_blank', 'noopener,noreferrer');
                        break;
                    case 'copy-project-name':
                        if (!projectName) return;
                        {
                            const copied = await copyTextToClipboard(projectName);
                            Swal.fire({
                                icon: copied ? 'success' : 'error',
                                title: copied ? 'คัดลอกชื่อโครงการแล้ว' : 'คัดลอกไม่สำเร็จ',
                                text: copied ? projectName : 'เบราว์เซอร์ไม่อนุญาตให้คัดลอกอัตโนมัติ',
                                timer: copied ? 1400 : 2200,
                                showConfirmButton: false
                            });
                        }
                        break;
                    case 'edit':
                        if (editUrl) location.href = editUrl;
                        break;
                    case 'pdf':
                        if (pdfUrl) window.open(pdfUrl, '_blank');
                        break;
                    case 'progress':
                        if (progressUrl) location.href = progressUrl;
                        break;
                    case 'fte-project':
                        if (fteProjectUrl) window.open(fteProjectUrl, '_blank');
                        break;
                    case 'delete':
                        const form = document.getElementById(delId);
                        if (!form) return;
                        Swal.fire({
                            title: 'คุณแน่ใจหรือไม่?',
                            text: 'การลบไม่สามารถย้อนกลับได้',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#e3342f',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'ใช่, ลบเลย',
                            cancelButtonText: 'ยกเลิก'
                        }).then(r => {
                            if (r.isConfirmed) form.submit();
                        });
                        break;
                }
            });
        })();

        // mobile long press
        let longPressTimer = null;

        function rowLongPressStart(e, row) {
            if (e.touches && e.touches.length > 1) return;
            longPressTimer = setTimeout(() => {
                const t = e.touches ? e.touches[0] : null;
                openRowMenu({
                    preventDefault: () => {},
                    clientX: t ? t.clientX : 20,
                    clientY: t ? t.clientY : 20
                }, row);
            }, 500);
        }

        function rowLongPressEnd() {
            if (longPressTimer) {
                clearTimeout(longPressTimer);
                longPressTimer = null;
            }
        }

        // Budget range sync
        (function() {
            const minRange = document.getElementById('budgetMinRange');
            const maxRange = document.getElementById('budgetMaxRange');
            const minInput = document.getElementById('budgetMinInput');
            const maxInput = document.getElementById('budgetMaxInput');
            const rangeFill = document.getElementById('budgetRangeFill');
            const minLabel = document.getElementById('budgetMinLabel');
            const maxLabel = document.getElementById('budgetMaxLabel');
            if (!minRange || !maxRange || !minInput || !maxInput || !rangeFill || !minLabel || !maxLabel) return;

            const shortNumber = (value) => {
                const abs = Math.abs(value);
                if (abs >= 1000000) {
                    const v = (value / 1000000).toFixed(1).replace(/\.0$/, '');
                    return `${v}m`;
                }
                if (abs >= 1000) {
                    const v = (value / 1000).toFixed(1).replace(/\.0$/, '');
                    return `${v}k`;
                }
                return `${value}`;
            };
            const minBound = Number(minRange.min);
            const maxBound = Number(maxRange.max);

            const updateUI = () => {
                const minVal = Number(minRange.value);
                const maxVal = Number(maxRange.value);
                const total = maxBound - minBound || 1;
                const minPct = ((minVal - minBound) / total) * 100;
                const maxPct = ((maxVal - minBound) / total) * 100;
                rangeFill.style.left = `${minPct}%`;
                rangeFill.style.right = `${100 - maxPct}%`;
                minLabel.textContent = shortNumber(minVal);
                maxLabel.textContent = shortNumber(maxVal);
                minLabel.style.left = `${minPct}%`;
                maxLabel.style.left = `${maxPct}%`;
                minLabel.style.display = minVal === minBound ? 'none' : 'block';
            };

            const clamp = () => {
                let minVal = Number(minRange.value);
                let maxVal = Number(maxRange.value);
                if (minVal > maxVal) {
                    [minVal, maxVal] = [maxVal, minVal];
                    minRange.value = minVal;
                    maxRange.value = maxVal;
                }
                minInput.value = minVal;
                maxInput.value = maxVal;
                updateUI();
            };

            const clampInputs = () => {
                let minVal = Number(minInput.value);
                let maxVal = Number(maxInput.value);
                if (minVal > maxVal) {
                    [minVal, maxVal] = [maxVal, minVal];
                }
                minRange.value = minVal;
                maxRange.value = maxVal;
                updateUI();
            };

            minRange.addEventListener('input', clamp);
            maxRange.addEventListener('input', clamp);
            minInput.addEventListener('change', clampInputs);
            maxInput.addEventListener('change', clampInputs);
            updateUI();
        })();

        // AJAX filter/search/sort/pagination (no page refresh)
        (function() {
            const selectors = {
                primaryForm: '#project-filter-form',
                drawerForm: '#project-filter-drawer-form',
                shell: '#project-table-shell',
                loading: '#table-loading-indicator',
                filterAnchor: '.project-filter-anchor',
                drawer: '#projectFilterDrawer',
                drawerOverlay: '#projectFilterDrawerOverlay',
                drawerClose: '#projectFilterDrawerClose',
                drawerSearchInput: '#project-drawer-search-input',
                openDrawer: '[data-project-open-drawer]',
                fab: '#projectFilterFab',
                fabBadge: '#projectFilterFabBadge',
                count: '#project-filter-count',
            };

            const query = (selector, root = document) => root.querySelector(selector);
            const queryAll = (selector, root = document) => Array.from(root.querySelectorAll(selector));
            const closeAllFilterDropdowns = (except = null) => {
                queryAll('[data-project-dropdown-menu]').forEach((menu) => {
                    if (except && menu === except) return;
                    menu.classList.add('hidden');
                });
            };
            const syncFilterDropdownLabels = (root = document) => {
                queryAll('[data-project-filter-dropdown]', root).forEach((dropdown) => {
                    const valueField = query('[data-project-dropdown-value]', dropdown);
                    const labelEl = query('[data-project-dropdown-label]', dropdown);
                    if (!valueField || !labelEl) return;
                    const selected = queryAll('[data-project-dropdown-option]', dropdown)
                        .find((option) => (option.dataset.value ?? '') === (valueField.value ?? ''));
                    labelEl.textContent = selected ? selected.textContent.trim() : (valueField.value || 'ทั้งหมด');
                });
            };
            const initFilterDropdowns = (root = document) => {
                queryAll('[data-project-filter-dropdown]', root).forEach((dropdown) => {
                    if (dropdown.dataset.dropdownBound === 'true') return;
                    dropdown.dataset.dropdownBound = 'true';

                    const valueField = query('[data-project-dropdown-value]', dropdown);
                    const toggle = query('[data-project-dropdown-toggle]', dropdown);
                    const menu = query('[data-project-dropdown-menu]', dropdown);
                    const searchInput = query('[data-project-dropdown-search]', dropdown);
                    const emptyEl = query('[data-project-dropdown-empty]', dropdown);
                    const options = queryAll('[data-project-dropdown-option]', dropdown);
                    if (!valueField || !toggle || !menu || !searchInput || !options.length) return;

                    const filterOptions = () => {
                        const term = (searchInput.value || '').toLowerCase().trim();
                        let visible = 0;
                        options.forEach((option) => {
                            const text = (option.textContent || '').toLowerCase();
                            const show = term === '' || text.includes(term);
                            option.classList.toggle('hidden', !show);
                            if (show) visible += 1;
                        });
                        if (emptyEl) {
                            emptyEl.classList.toggle('hidden', visible > 0);
                        }
                    };

                    toggle.addEventListener('click', () => {
                        const willOpen = menu.classList.contains('hidden');
                        closeAllFilterDropdowns(willOpen ? menu : null);
                        menu.classList.toggle('hidden', !willOpen);
                        if (willOpen) {
                            searchInput.value = '';
                            filterOptions();
                            searchInput.focus();
                        }
                    });

                    searchInput.addEventListener('input', filterOptions);

                    options.forEach((option) => {
                        option.addEventListener('click', () => {
                            valueField.value = option.dataset.value ?? '';
                            syncFilterDropdownLabels(dropdown);
                            menu.classList.add('hidden');
                            valueField.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                    });
                });

                syncFilterDropdownLabels(root);
            };
            const loadingEl = query(selectors.loading);
            const currentPath = window.location.pathname;
            const getPrimaryForm = () => query(selectors.primaryForm);
            const getDrawerForm = () => query(selectors.drawerForm);
            const getForms = () => [getPrimaryForm(), getDrawerForm()].filter(Boolean);
            const tableCountEl = query('#project-table-count');
            const tableCountMetaEl = query('#project-table-count-meta');
            const tablePaginationWrapEl = query('#project-table-pagination-wrap');
            const emptySearchRowEl = query('#project-table-empty-search');
            const form = getPrimaryForm();
            if (!form) return;

            let controller = null;
            let requestToken = 0;
            let timer = null;
            let isSearchComposing = false;
            let drawerOpen = false;
            let previousBodyOverflow = '';
            const baseTableCountText = tableCountEl ? (tableCountEl.childNodes[0]?.textContent || '').trim() : '';

            const normalizeSearchTerm = (value) => String(value || '')
                .toLowerCase()
                .normalize('NFKC')
                .replace(/\s+/g, ' ')
                .trim();

            const applyLocalSearchPreview = (rawValue) => {
                const shell = query(selectors.shell);
                if (!shell) return;

                const rows = queryAll('tbody tr[data-project-search-text]', shell);
                if (!rows.length) return;

                const term = normalizeSearchTerm(rawValue);
                let visibleCount = 0;

                rows.forEach((row) => {
                    const haystack = normalizeSearchTerm(row.dataset.projectSearchText || row.textContent);
                    const matched = term === '' || haystack.includes(term);
                    row.classList.toggle('hidden', !matched);
                    if (matched) visibleCount += 1;
                });

                if (emptySearchRowEl) {
                    emptySearchRowEl.classList.toggle('hidden', !(term !== '' && visibleCount === 0));
                }

                if (tablePaginationWrapEl) {
                    tablePaginationWrapEl.classList.toggle('hidden', term !== '');
                }

                if (tableCountEl) {
                    const message = term === '' ? baseTableCountText : `พบ ${visibleCount} รายการในหน้านี้`;
                    const textNode = tableCountEl.childNodes[0];
                    if (textNode) {
                        textNode.textContent = `${message} `;
                    } else {
                        tableCountEl.textContent = message;
                    }
                }

                if (tableCountMetaEl) {
                    tableCountMetaEl.textContent = term === '' ? 'เรียงลำดับจากข้อมูลที่แก้ไขล่าสุด' : 'กรองทันทีโดยไม่ reload';
                }
            };

            const countActiveFilters = (sourceForm) => {
                if (!sourceForm) return 0;
                return queryAll('[name]', sourceForm).reduce((count, field) => {
                    if (!field.name || ['page', 'sort', 'dir'].includes(field.name)) return count;
                    if (field.type === 'checkbox') return count + (field.checked ? 1 : 0);
                    return String(field.value ?? '').trim() !== '' ? count + 1 : count;
                }, 0);
            };

            const updateFilterBadges = () => {
                const activeCount = countActiveFilters(getPrimaryForm() || getDrawerForm());
                const countEl = query(selectors.count);
                const badgeEl = query(selectors.fabBadge);
                if (countEl) countEl.textContent = String(activeCount);
                if (badgeEl) {
                    badgeEl.textContent = String(activeCount);
                    badgeEl.classList.toggle('hidden', activeCount === 0);
                }
            };

            const syncFabVisibility = () => {
                const fab = query(selectors.fab);
                const anchor = query(selectors.filterAnchor);
                if (!fab || !anchor) return;
                const rect = anchor.getBoundingClientRect();
                fab.classList.toggle('is-visible', rect.bottom < 48);
            };

            const setLoading = (isLoading) => {
                if (loadingEl) {
                    loadingEl.classList.toggle('hidden', !isLoading);
                    loadingEl.classList.toggle('flex', isLoading);
                }
                query(selectors.shell)?.classList.toggle('opacity-60', isLoading);
            };

            const scrollToTableTop = () => {
                const shell = query(selectors.shell);
                if (!shell) return;

                window.requestAnimationFrame(() => {
                    const top = shell.getBoundingClientRect().top + window.scrollY - 96;
                    window.scrollTo({
                        top: Math.max(top, 0),
                        behavior: 'smooth',
                    });
                });
            };

            const setFieldValue = (field, value) => {
                if (!field) return;
                if (field.type === 'checkbox') {
                    const values = Array.isArray(value) ? value : [value];
                    field.checked = values.includes(field.value || '1');
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
                        const targetFields = queryAll(`[name="${sourceField.name}"]`, targetForm);
                        if (!targetFields.length) return;

                        if (sourceField.type === 'checkbox') {
                            const checkedValues = queryAll(`[name="${sourceField.name}"]`, sourceForm)
                                .filter((field) => field.checked)
                                .map((field) => field.value || '1');
                            targetFields.forEach((field) => setFieldValue(field, checkedValues));
                            return;
                        }

                        targetFields.forEach((field) => setFieldValue(field, sourceField.value));
                    });
                });
                syncFilterDropdownLabels();
            };

            const syncFormWithUrl = (url) => {
                getForms().forEach((targetForm) => {
                    queryAll('[name]', targetForm).forEach((field) => {
                        const values = url.searchParams.getAll(field.name);
                        if (field.type === 'checkbox') {
                            setFieldValue(field, values);
                            return;
                        }

                        if (field.name === 'sort') {
                            setFieldValue(field, url.searchParams.get(field.name) ?? 'updated_at');
                            return;
                        }
                        if (field.name === 'dir') {
                            setFieldValue(field, url.searchParams.get(field.name) ?? 'desc');
                            return;
                        }
                        setFieldValue(field, url.searchParams.get(field.name) ?? '');
                    });
                });
                updateFilterBadges();
                syncFilterDropdownLabels();
            };

            const buildUrlFromForm = (sourceForm) => {
                const url = new URL(sourceForm.action, window.location.origin);
                const formData = new FormData(sourceForm);
                for (const [key, value] of formData.entries()) {
                    if (value !== null && String(value).trim() !== '') {
                        url.searchParams.append(key, String(value));
                    }
                }
                url.searchParams.delete('page');
                return url;
            };

            const parseAndReplaceShell = (html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextShell = doc.querySelector(selectors.shell);
                const currentShell = query(selectors.shell);
                if (!nextShell || !currentShell) return false;
                currentShell.replaceWith(nextShell);
                return true;
            };

            const closeDrawer = () => {
                const drawer = query(selectors.drawer);
                const overlay = query(selectors.drawerOverlay);
                if (!drawer || !overlay || !drawerOpen) return;
                drawer.classList.remove('is-open');
                overlay.classList.remove('is-open');
                drawer.setAttribute('aria-hidden', 'true');
                drawerOpen = false;
                document.body.style.overflow = previousBodyOverflow;
            };

            const openDrawer = () => {
                const drawer = query(selectors.drawer);
                const overlay = query(selectors.drawerOverlay);
                if (!drawer || !overlay || drawerOpen) return;

                syncFormsFromSource(getPrimaryForm() || getDrawerForm());
                drawer.classList.add('is-open');
                overlay.classList.add('is-open');
                drawer.setAttribute('aria-hidden', 'false');
                drawerOpen = true;
                previousBodyOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';

                window.setTimeout(() => {
                    query(selectors.drawerSearchInput)?.focus({ preventScroll: true });
                }, 120);
            };

            const fetchAndRender = async (url, options = {}) => {
                const { updateUrl = true, scrollToTop = false } = options;
                const token = ++requestToken;

                if (controller) controller.abort();
                controller = new AbortController();
                setLoading(true);

                try {
                    const res = await fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: controller.signal
                    });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);

                    const html = await res.text();
                    if (token !== requestToken) return;
                    if (!parseAndReplaceShell(html)) {
                        throw new Error('Cannot find table shell in response');
                    }

                    if (updateUrl) {
                        history.replaceState({ ajax: true }, '', `${url.pathname}${url.search}`);
                    }

                    syncFormWithUrl(url);
                    syncFabVisibility();
                    if (scrollToTop) {
                        scrollToTableTop();
                    }
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

            const submitDebounced = (delay = 220) => {
                if (timer) clearTimeout(timer);
                timer = setTimeout(() => {
                    const sourceForm = getPrimaryForm() || getDrawerForm();
                    if (!sourceForm) return;
                    fetchAndRender(buildUrlFromForm(sourceForm), { updateUrl: true });
                }, delay);
            };

            const bindForms = () => {
                getForms().forEach((targetForm) => {
                    if (!targetForm || targetForm.dataset.ajaxBound === 'true') return;
                    targetForm.dataset.ajaxBound = 'true';

                    targetForm.addEventListener('submit', (event) => {
                        event.preventDefault();
                        syncFormsFromSource(targetForm);
                        const sourceForm = getPrimaryForm() || targetForm;
                        fetchAndRender(buildUrlFromForm(sourceForm), { updateUrl: true });
                    });

                    queryAll('[data-project-live]', targetForm).forEach((field) => {
                        if (field.dataset.projectAjaxBound === 'true') return;
                        field.dataset.projectAjaxBound = 'true';

                        if (field.matches('input[type="text"], input[type="search"]')) {
                            field.addEventListener('input', () => {
                                if (isSearchComposing) return;
                                syncFormsFromSource(targetForm);
                                updateFilterBadges();
                                const sourceForm = getPrimaryForm() || targetForm;
                                const searchField = sourceForm.querySelector('input[name="search"]');
                                applyLocalSearchPreview(searchField ? searchField.value : '');
                                submitDebounced(280);
                            });
                            field.addEventListener('keydown', (event) => {
                                if (event.key === 'Enter') {
                                    event.preventDefault();
                                    if (timer) clearTimeout(timer);
                                    syncFormsFromSource(targetForm);
                                    const sourceForm = getPrimaryForm() || targetForm;
                                    const searchField = sourceForm.querySelector('input[name="search"]');
                                    applyLocalSearchPreview(searchField ? searchField.value : '');
                                    fetchAndRender(buildUrlFromForm(sourceForm), { updateUrl: true });
                                }
                            });
                            field.addEventListener('compositionstart', () => {
                                isSearchComposing = true;
                            });
                            field.addEventListener('compositionend', () => {
                                isSearchComposing = false;
                                syncFormsFromSource(targetForm);
                                updateFilterBadges();
                                const sourceForm = getPrimaryForm() || targetForm;
                                const searchField = sourceForm.querySelector('input[name="search"]');
                                applyLocalSearchPreview(searchField ? searchField.value : '');
                                submitDebounced(180);
                            });
                            field.addEventListener('change', () => {
                                syncFormsFromSource(targetForm);
                                updateFilterBadges();
                                const sourceForm = getPrimaryForm() || targetForm;
                                const searchField = sourceForm.querySelector('input[name="search"]');
                                applyLocalSearchPreview(searchField ? searchField.value : '');
                                submitDebounced(180);
                            });
                            return;
                        }

                        field.addEventListener('change', () => {
                            syncFormsFromSource(targetForm);
                            updateFilterBadges();
                            submitDebounced(120);
                        });
                    });
                });
            };

            document.addEventListener('click', (event) => {
                if (!event.target.closest('[data-project-filter-dropdown]')) {
                    closeAllFilterDropdowns();
                }

                const openButton = event.target.closest(selectors.openDrawer);
                if (openButton) {
                    event.preventDefault();
                    openDrawer();
                    return;
                }

                const resetLink = event.target.closest('#project-filter-reset, #project-filter-drawer-reset');
                if (resetLink) {
                    event.preventDefault();
                    const resetUrl = new URL(resetLink.href, window.location.origin);
                    syncFormWithUrl(resetUrl);
                    fetchAndRender(resetUrl, { updateUrl: true });
                    return;
                }

                const link = event.target.closest(`${selectors.shell} a[href]`);
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href) return;

                const url = new URL(href, window.location.origin);
                const isSamePage = url.pathname === currentPath;
                const isListInteraction = url.searchParams.has('page') || url.searchParams.has('sort') || url.searchParams.has('dir');

                if (!isSamePage || !isListInteraction) {
                    if (timer) clearTimeout(timer);
                    if (controller) controller.abort();
                    return;
                }

                event.preventDefault();
                syncFormWithUrl(url);
                fetchAndRender(url, {
                    updateUrl: true,
                    scrollToTop: url.searchParams.has('page'),
                });
            });

            query(selectors.drawerClose)?.addEventListener('click', closeDrawer);
            query(selectors.drawerOverlay)?.addEventListener('click', closeDrawer);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && drawerOpen) {
                    closeDrawer();
                }
                if (event.key === 'Escape') {
                    closeAllFilterDropdowns();
                }
            });

            window.addEventListener('popstate', () => {
                const url = new URL(window.location.href);
                if (url.pathname !== currentPath) return;
                syncFormWithUrl(url);
                fetchAndRender(url, { updateUrl: false });
            });

            window.addEventListener('scroll', syncFabVisibility, { passive: true });
            window.addEventListener('resize', syncFabVisibility);

            bindForms();
            initFilterDropdowns();
            updateFilterBadges();
            syncFabVisibility();
            applyLocalSearchPreview(query('#project-search-input')?.value || '');
        })();
    </script>
@endsection

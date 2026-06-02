@extends('layouts.app')

@section('content')
    @php
        $wfaMonthLabel = static function ($report): string {
            $yearBE = (int) ($report->filter_year ?: now()->year + 543);
            $yearAD = $yearBE > 2500 ? $yearBE - 543 : $yearBE;
            $month = (int) ($report->filter_month ?: now()->month);
            $date = \Carbon\Carbon::createFromDate($yearAD, max(1, min(12, $month)), 1)->locale('th');
            return $date->translatedFormat('F') . ' ' . ($date->year + 543);
        };
        $wfaCreatedLabel = static function ($report): string {
            if (! $report->created_at) {
                return '-';
            }
            $date = $report->created_at->copy()->setTimezone('Asia/Bangkok')->locale('th');
            return $date->translatedFormat('j F') . ' ' . ($date->year + 543) . ' เวลา ' . $date->format('H:i');
        };
        $wfaMonthKey = static function ($report): string {
            $yearBE = (int) ($report->filter_year ?: now()->year + 543);
            $yearAD = $yearBE > 2500 ? $yearBE - 543 : $yearBE;
            $month = (int) ($report->filter_month ?: now()->month);
            return sprintf('%04d-%02d', $yearAD, max(1, min(12, $month)));
        };
        $currentUser = auth()->user();
        $canManageWfaReports = $currentUser && in_array($currentUser->role, [
            \App\Models\User::ROLE_ADMIN,
            \App\Models\User::ROLE_SU_ADMIN,
        ], true);
        $canViewAnyWfaReports = $canManageWfaReports || $currentUser?->role === \App\Models\User::ROLE_UNIT_EXECUTIVE;
        $canViewWfaReport = static fn ($report): bool => $currentUser
            && ((int) $report->user_id === (int) $currentUser->id || $canViewAnyWfaReports);
        $canEditWfaReport = static fn ($report): bool => $currentUser
            && ((int) $report->user_id === (int) $currentUser->id || $canManageWfaReports);
        $wfaDownloadName = static function ($report, string $extension): string {
            $title = trim((string) $report->title) ?: ($report->wfa_code ?: \App\Models\WfaReport::formatCode((int) $report->id));
            $title = preg_replace('/[\\\\\/:*?"<>|]+/u', ' ', $title);
            $title = trim((string) preg_replace('/\s+/u', ' ', (string) $title));
            return ($title ?: 'WFA') . '.' . $extension;
        };
        $wfaPdfUrl = static fn ($report): string => route('wfa.pdf', [
            'wfaReport' => $report,
            'filename' => $wfaDownloadName($report, 'pdf'),
        ]);
        $reportPayloads = $reports->getCollection()->mapWithKeys(function ($report) use ($wfaMonthLabel, $wfaCreatedLabel, $wfaMonthKey, $wfaDownloadName, $canViewWfaReport, $canEditWfaReport) {
            return [
                $report->id => [
                    'id' => $report->id,
                    'code' => $report->wfa_code ?: \App\Models\WfaReport::formatCode((int) $report->id),
                    'person_name' => $report->person_name,
                    'title' => $report->title,
                    'created_at_label' => $wfaCreatedLabel($report),
                    'month_label' => $wfaMonthLabel($report),
                    'filter_month_key' => $wfaMonthKey($report),
                    'output_html' => $report->output_html,
                    'output_text' => $report->output_text ?: '-',
                    'can_view' => $canViewWfaReport($report),
                    'can_edit' => $canEditWfaReport($report),
                    'update_url' => route('wfa.update', $report),
                    'delete_url' => route('wfa.destroy', $report),
                    'edit_url' => route('wfa.edit', $report),
                    'pdf_url' => route('wfa.pdf', ['wfaReport' => $report, 'filename' => $wfaDownloadName($report, 'pdf')]),
                    'word_url' => route('wfa.word', $report),
                    'pdf_filename' => $wfaDownloadName($report, 'pdf'),
                    'word_filename' => $wfaDownloadName($report, 'docx'),
                ],
            ];
        });
        $previousOptionYear = null;
    @endphp

    <style>
        .wfa-shell { width: 100%; margin: 0 auto; }
        .wfa-panel { border: 1px solid #e2e8f0; border-radius: 18px; background: #fff; box-shadow: 0 10px 22px rgba(15, 23, 42, .06); }
        .wfa-chip { display: inline-flex; align-items: center; border-radius: 999px; border: 1px solid #dbeafe; background: #eff6ff; padding: .38rem .7rem; font-size: .75rem; font-weight: 800; color: #1d4ed8; line-height: 1; white-space: nowrap; }
        .wfa-month-chip { display: inline-flex; align-items: center; border-radius: 999px; border: 1px solid transparent; padding: .38rem .7rem; font-size: .75rem; font-weight: 900; line-height: 1; white-space: nowrap; }
        .wfa-month-chip--01 { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
        .wfa-month-chip--02 { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
        .wfa-month-chip--03 { background: #fefce8; border-color: #fde68a; color: #a16207; }
        .wfa-month-chip--04 { background: #fff7ed; border-color: #fed7aa; color: #c2410c; }
        .wfa-month-chip--05 { background: #fdf2f8; border-color: #fbcfe8; color: #be185d; }
        .wfa-month-chip--06 { background: #f5f3ff; border-color: #ddd6fe; color: #6d28d9; }
        .wfa-month-chip--07 { background: #ecfeff; border-color: #a5f3fc; color: #0e7490; }
        .wfa-month-chip--08 { background: #f7fee7; border-color: #d9f99d; color: #4d7c0f; }
        .wfa-month-chip--09 { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .wfa-month-chip--10 { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
        .wfa-month-chip--11 { background: #faf5ff; border-color: #e9d5ff; color: #7e22ce; }
        .wfa-month-chip--12 { background: #f1f5f9; border-color: #cbd5e1; color: #334155; }
        .wfa-action { display: inline-flex; align-items: center; justify-content: center; min-height: 34px; border-radius: 10px; padding: .42rem .72rem; font-size: .78rem; font-weight: 800; line-height: 1; }
        .wfa-action--icon { width: 40px; min-width: 40px; height: 36px; min-height: 36px; padding: 0; }
        .wfa-action-logo { width: 22px; height: 22px; object-fit: contain; }
        .wfa-action--delete svg { width: 18px; height: 18px; stroke-width: 2.25; }
        .wfa-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; border: 1px solid #e2e8f0; border-radius: 14px; }
        .wfa-table { width: 100%; min-width: 840px; table-layout: fixed; border-collapse: separate; border-spacing: 0; background: #fff; }
        .wfa-table thead th { background: #f8fafc; color: #475569; font-size: .74rem; font-weight: 900; border-bottom: 1px solid #e2e8f0; padding: .72rem .85rem; white-space: nowrap; }
        .wfa-table tbody td { border-bottom: 1px solid #e2e8f0; padding: .82rem .85rem; vertical-align: top; }
        .wfa-table tbody tr:last-child td { border-bottom: 0; }
        .wfa-table tbody tr:hover td { background: #f8fbff; }
        .wfa-live-input { min-height: 40px; border: 1px solid #cbd5e1; border-radius: 12px; padding: 0 .8rem; font-size: .88rem; outline: none; }
        .wfa-live-input:focus { border-color: #38bdf8; box-shadow: 0 0 0 4px rgba(125, 211, 252, .22); }
        .wfa-select { min-height: 40px; border: 1px solid #cbd5e1; border-radius: 12px; background: #fff; padding: 0 .8rem; font-size: .88rem; outline: none; }
        .wfa-modal { position: fixed; inset: 0; z-index: 70; display: none; align-items: center; justify-content: center; overflow-x: hidden; padding: 24px; background: rgba(15, 23, 42, .48); }
        .wfa-modal.is-open { display: flex; }
        .wfa-modal-card { width: min(1120px, 100%); max-width: calc(100vw - 48px); max-height: min(88vh, 900px); overflow: hidden; border-radius: 18px; background: #fff; box-shadow: 0 24px 60px rgba(15, 23, 42, .25); }
        .wfa-modal-card form,
        .wfa-modal-card label,
        .wfa-modal-card input,
        .wfa-modal-card select,
        .wfa-modal-card .rounded-2xl,
        .wfa-modal-card .wfa-editor-wrap,
        .wfa-modal-card .wfa-editor-page { min-width: 0; max-width: 100%; }
        .wfa-modal-card .overflow-y-auto { overflow-x: hidden; }
        .wfa-guide-modal { position: fixed; inset: 0; z-index: 80; display: none; align-items: center; justify-content: center; overflow-x: hidden; padding: 24px; background: rgba(15, 23, 42, .52); }
        .wfa-guide-modal.is-open { display: flex; }
        .wfa-guide-card { width: min(980px, 100%); max-width: calc(100vw - 48px); max-height: min(88vh, 900px); overflow: hidden; border-radius: 18px; background: #fff; box-shadow: 0 24px 60px rgba(15, 23, 42, .25); }
        .wfa-guide-body { max-height: calc(min(88vh, 900px) - 92px); overflow-y: auto; overflow-x: hidden; }
        .wfa-guide-table { width: 100%; border-collapse: collapse; }
        .wfa-guide-table th,
        .wfa-guide-table td { border: 1px solid #e2e8f0; padding: .75rem; text-align: left; vertical-align: top; }
        .wfa-guide-table th { background: #f8fafc; color: #334155; font-size: .78rem; font-weight: 900; }
        .wfa-guide-table td { color: #475569; font-size: .86rem; line-height: 1.6; }
        .wfa-editor-wrap { position: relative; }
        .wfa-editor-page { min-height: 360px; max-height: 48vh; overflow: auto; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; padding: 24px 28px; color: #0f172a; font-size: 16px; line-height: 1.7; outline: none; }
        .wfa-editor-placeholder { position: absolute; top: 25px; left: 29px; right: 29px; color: #94a3b8; font-size: 16px; line-height: 1.7; pointer-events: none; }
        .wfa-editor-placeholder.is-hidden { display: none; }
        .wfa-editor-page ul, .wfa-editor-page ol { margin-left: 1.3rem; padding-left: 1.1rem; }
        .wfa-editor-page ol { list-style: decimal; }
        .wfa-editor-page ul { list-style: disc; }
        .wfa-tool { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 32px; border-radius: 9px; border: 1px solid #cbd5e1; background: #fff; color: #334155; padding: 0 .6rem; font-size: .76rem; font-weight: 900; line-height: 1; }
        .wfa-tool svg { width: 17px; height: 17px; stroke-width: 2.25; }
        .wfa-tool:hover { background: #f1f5f9; }
        .wfa-month-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
        .wfa-month-choice { min-height: 40px; border-radius: 12px; border: 1px solid #cbd5e1; background: #fff; padding: 0 10px; color: #334155; font-size: .86rem; font-weight: 800; text-align: center; }
        .wfa-month-choice.is-selected { border-color: #60a5fa; background: #eff6ff; color: #1d4ed8; box-shadow: inset 0 0 0 1px #93c5fd; }
        .wfa-month-readonly { display: none; min-height: 40px; align-items: center; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 0 12px; color: #475569; font-size: .9rem; font-weight: 800; }
        .wfa-toast { position: fixed; right: 20px; bottom: 20px; z-index: 90; display: none; max-width: 360px; border: 1px solid #bbf7d0; border-radius: 14px; background: #f0fdf4; padding: 12px 14px; color: #166534; font-size: .88rem; font-weight: 800; box-shadow: 0 16px 36px rgba(15, 23, 42, .14); }
        .wfa-toast.is-show { display: block; }
        .wfa-export-choice { display: none; border-top: 1px solid #e2e8f0; background: #f8fafc; padding: 14px 18px; }
        .wfa-export-choice.is-open { display: block; }
        @media (max-width: 767px) {
            .wfa-table { min-width: 760px; }
            .wfa-modal { display: none !important; }
            .wfa-guide-modal { padding: 12px; }
            .wfa-guide-card { max-width: calc(100vw - 24px); }
        }
    </style>

    <div class="wfa-shell space-y-5">
        <section class="wfa-panel p-5 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="wfa-chip">Work From Anywhere</div>
                    <h1 class="mt-3 text-2xl font-extrabold text-slate-950 sm:text-3xl">รายงานภาระงาน (WFA)</h1>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                    <button type="button" id="wfa-guide-open" class="inline-flex min-h-[42px] items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-extrabold text-slate-700 shadow-sm hover:bg-slate-100">
                        คู่มือการใช้งาน WFA
                    </button>
                    <button type="button" id="wfa-create-desktop" class="hidden min-h-[42px] items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-extrabold text-white shadow-sm hover:bg-blue-700 md:inline-flex">
                        เพิ่มผลลัพธ์ของงาน (Output) รายเดือน
                    </button>
                    <a href="{{ route('wfa.create') }}" class="inline-flex min-h-[42px] items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-extrabold text-white shadow-sm hover:bg-blue-700 md:hidden">
                        เพิ่มผลลัพธ์ของงาน (Output) รายเดือน
                    </a>
                </div>
            </div>
        </section>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
        @endif

        <section id="wfa-list-table" class="wfa-panel scroll-mt-24 p-4 sm:p-5">
            <div class="mb-4 grid gap-3 lg:grid-cols-[1fr_auto] lg:items-end">
                <label class="block">
                    <span class="mb-1 block text-xs font-extrabold text-slate-500">ค้นหา</span>
                    <input id="wfa-live-search" type="search" value="{{ $search ?? '' }}" autocomplete="off" placeholder="ค้นหารหัส รายงานภาระงาน (WFA) เดือน หรือ output" class="wfa-live-input w-full">
                </label>
                <label class="block lg:w-64">
                    <span class="mb-1 block text-xs font-extrabold text-slate-500">เรียงรายการ</span>
                    <select id="wfa-sort" class="wfa-select w-full">
                        <option value="created_desc">วันที่บันทึกล่าสุด</option>
                        <option value="month_desc">เดือนล่าสุด</option>
                        <option value="month_asc">เดือนเก่าสุด</option>
                        <option value="title_asc">รายงานภาระงาน (WFA) A-Z</option>
                    </select>
                </label>
            </div>

            <div class="wfa-table-wrap">
                <table class="wfa-table text-left text-sm">
                    <colgroup>
                        <col style="width: 150px;">
                        <col>
                        <col style="width: 220px;">
                        <col style="width: 220px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>รหัส</th>
                            <th>รายงานภาระงาน (WFA)</th>
                            <th>ประจำเดือน</th>
                            <th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody id="wfa-table-body">
                        @forelse ($reports as $report)
                            @php
                                $code = $report->wfa_code ?: \App\Models\WfaReport::formatCode((int) $report->id);
                                $monthLabel = $wfaMonthLabel($report);
                                $createdLabel = $wfaCreatedLabel($report);
                                $searchText = mb_strtolower(trim($code . ' ' . $report->title . ' ' . $monthLabel . ' ' . $createdLabel . ' ' . $report->output_text));
                                $canViewThisReport = $canViewWfaReport($report);
                                $canEditThisReport = $canEditWfaReport($report);
                            @endphp
                            <tr data-wfa-row data-report-id="{{ $report->id }}" data-month="{{ $wfaMonthKey($report) }}" data-created="{{ optional($report->created_at)->timestamp ?? 0 }}" data-title="{{ e($report->title) }}" data-search-text="{{ e($searchText) }}">
                                <td class="whitespace-nowrap font-semibold text-slate-600">{{ $code }}</td>
                                <td>
                                    @if ($canEditThisReport)
                                        <button type="button" class="hidden text-left font-extrabold leading-6 text-blue-700 hover:underline md:inline" data-open-wfa="{{ $report->id }}">{{ $report->title }}</button>
                                        <a href="{{ route('wfa.edit', $report) }}" class="font-extrabold leading-6 text-blue-700 hover:underline md:hidden">{{ $report->title }}</a>
                                    @elseif ($canViewThisReport)
                                        <button type="button" class="hidden text-left font-extrabold leading-6 text-blue-700 hover:underline md:inline" data-open-wfa="{{ $report->id }}">{{ $report->title }}</button>
                                        <span class="font-extrabold leading-6 text-slate-800 md:hidden">{{ $report->title }}</span>
                                    @else
                                        <span class="font-extrabold leading-6 text-slate-800">{{ $report->title }}</span>
                                    @endif
                                    <div class="mt-1 text-xs font-semibold text-slate-500">วันที่บันทึก {{ $createdLabel }}</div>
                                    <div class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500" data-row-output="{{ $report->id }}">{{ $report->output_text ?: '-' }}</div>
                                </td>
                                <td><span class="wfa-month-chip wfa-month-chip--{{ substr($wfaMonthKey($report), 5, 2) }}" data-row-month="{{ $report->id }}">{{ $monthLabel }}</span></td>
                                <td>
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ $wfaPdfUrl($report) }}" target="_blank" rel="noopener" class="wfa-action wfa-action--icon border border-red-200 bg-white text-red-600 hover:bg-red-50" title="PDF" aria-label="PDF">
                                            <img src="https://thesvg.org/icons/pdf/default.svg" alt="" aria-hidden="true" class="wfa-action-logo">
                                            <span class="sr-only">PDF</span>
                                        </a>
                                        <a href="{{ route('wfa.word', $report) }}" download="{{ $wfaDownloadName($report, 'docx') }}" class="wfa-action wfa-action--icon border border-blue-200 bg-white text-blue-600 hover:bg-blue-50" title="Word" aria-label="Word">
                                            <img src="https://thesvg.org/icons/microsoft-word/default.svg" alt="" aria-hidden="true" class="wfa-action-logo">
                                            <span class="sr-only">Word</span>
                                        </a>
                                        @if ($canEditThisReport)
                                            <button type="button" class="wfa-action wfa-action--icon wfa-action--delete border border-red-200 bg-white text-red-600 hover:bg-red-50" data-delete-wfa="{{ $report->id }}" title="ลบ" aria-label="ลบ">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                    <path d="M3 6h18"></path>
                                                    <path d="M8 6V4h8v2"></path>
                                                    <path d="M19 6l-1 14H6L5 6"></path>
                                                    <path d="M10 11v5"></path>
                                                    <path d="M14 11v5"></path>
                                                </svg>
                                                <span class="sr-only">ลบ</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-wfa-static-empty>
                                <td colspan="4" class="px-4 py-12 text-center">
                                    <div class="text-base font-extrabold text-slate-800">ยังไม่มีรายงานภาระงาน (WFA)</div>
                                    <div class="mt-1 text-sm text-slate-500">เริ่มจากปุ่มเพิ่มผลลัพธ์ของงาน (Output) รายเดือนด้านบน</div>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="wfa-empty-search" class="hidden">
                            <td colspan="4" class="px-4 py-12 text-center text-sm font-semibold text-slate-500">ไม่พบรายการที่ตรงกับคำค้นหา</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{ $reports->links() }}
    </div>

    <div id="wfa-modal" class="wfa-modal" aria-hidden="true">
        <div class="wfa-modal-card">
            <form id="wfa-modal-form" method="POST" class="flex max-h-[88vh] flex-col">
                @csrf
                @method('PUT')
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 p-5">
                    <div>
                        <div id="wfa-modal-code" class="wfa-chip"></div>
                        <h2 id="wfa-modal-heading" class="mt-3 text-xl font-extrabold text-slate-950">แก้ไข Output ใน WFA</h2>
                    </div>
                    <button type="button" id="wfa-modal-close" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 hover:bg-slate-100">ปิด</button>
                </div>

                <div class="grid gap-4 overflow-y-auto p-5">
                    <label class="block">
                        <span class="mb-1 block text-xs font-extrabold text-slate-500">รายงานภาระงาน (WFA)</span>
                        <input id="wfa-modal-title" type="text" readonly class="wfa-live-input w-full bg-slate-50 text-slate-600">
                    </label>
                    <div>
                        <span class="mb-1 block text-xs font-extrabold text-slate-500">WFA ประจำเดือน</span>
                        <input type="hidden" id="wfa-modal-month" name="filter_month_key" value="">
                        <div id="wfa-modal-month-readonly" class="wfa-month-readonly"></div>
                        <div id="wfa-modal-month-grid" class="wfa-month-grid hidden">
                            @foreach ($monthOptions as $option)
                                <button type="button" class="wfa-month-choice" data-wfa-month-choice="{{ $option['value'] }}" data-title="WFA {{ auth()->user()->full_name }} ประจำเดือน {{ $option['label'] }} และ ผลงานรายเดือน {{ $option['output_label'] }}">
                                    {{ $option['label'] }}{{ $option['is_current'] ? '  เดือนนี้' : '' }}
                                </button>
                            @endforeach
                        </div>
                        <select id="wfa-modal-month-select" class="wfa-select w-full">
                            @foreach ($monthOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}{{ $option['is_current'] ? '  เดือนนี้' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rounded-2xl border border-slate-200">
                        <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-slate-50 px-3 py-2">
                            <button type="button" data-wfa-command="bold" class="wfa-tool" title="ตัวหนา">B</button>
                            <button type="button" data-wfa-command="italic" class="wfa-tool italic" title="ตัวเอียง">I</button>
                            <button type="button" data-wfa-command="underline" class="wfa-tool underline" title="ขีดเส้นใต้">U</button>
                            <button type="button" data-wfa-command="insertOrderedList" class="wfa-tool" title="ลำดับเลข">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" d="M10 6h10M10 12h10M10 18h10M4 6h1.5M4 12h1.5M4 18h1.5" />
                                </svg>
                            </button>
                            <button type="button" data-wfa-command="insertUnorderedList" class="wfa-tool" title="หัวข้อย่อย">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" d="M9 6h11M9 12h11M9 18h11" />
                                    <path fill="currentColor" stroke="none" d="M4.75 7.25a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5ZM4.75 13.25a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5ZM4.75 19.25a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5Z" />
                                </svg>
                            </button>
                            <button type="button" data-wfa-command="justifyLeft" class="wfa-tool" title="ชิดซ้าย">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" d="M4 6h14M4 10h10M4 14h14M4 18h8" />
                                </svg>
                            </button>
                            <button type="button" data-wfa-command="justifyCenter" class="wfa-tool" title="กึ่งกลาง">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" d="M5 6h14M7 10h10M5 14h14M8 18h8" />
                                </svg>
                            </button>
                            <button type="button" data-wfa-command="justifyRight" class="wfa-tool" title="ชิดขวา">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" d="M6 6h14M10 10h10M6 14h14M12 18h8" />
                                </svg>
                            </button>
                            <button type="button" data-wfa-command="removeFormat" class="wfa-tool" title="ล้างรูปแบบ">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 19h14M8 5h8M12 5v10M9 15h6M17 9l3 3M20 9l-3 3" />
                                </svg>
                            </button>
                        </div>
                        <div class="wfa-editor-wrap">
                            <div id="wfa-modal-placeholder" class="wfa-editor-placeholder"></div>
                            <div id="wfa-modal-editor" contenteditable="true" class="wfa-editor-page"></div>
                        </div>
                    </div>
                    <input type="hidden" id="wfa-modal-output" name="output_html" value="">
                </div>

                <div id="wfa-export-choice" class="wfa-export-choice">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm font-extrabold text-slate-800">ต้องการ export เป็น PDF / Word หรือไม่</div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" id="wfa-export-no" class="wfa-action border border-slate-300 bg-white text-slate-700 hover:bg-slate-100">ไม่</button>
                            <button type="button" id="wfa-export-pick" class="wfa-action bg-blue-600 text-white hover:bg-blue-700">ใช้</button>
                            <a id="wfa-export-pdf" href="#" class="wfa-action hidden border border-slate-300 bg-white text-slate-700 hover:bg-slate-100">PDF</a>
                            <a id="wfa-export-word" href="#" class="wfa-action hidden border border-slate-300 bg-white text-slate-700 hover:bg-slate-100">Word</a>
                        </div>
                    </div>
                </div>

                <div class="relative flex flex-col items-center justify-center gap-3 border-t border-slate-200 p-5 sm:flex-row">
                    <button type="button" id="wfa-modal-delete" class="hidden inline-flex min-h-[44px] items-center justify-center rounded-xl border border-red-200 bg-white px-5 text-sm font-extrabold text-red-600 hover:bg-red-50 sm:absolute sm:left-5 sm:top-1/2 sm:-translate-y-1/2">
                        ลบรายการนี้
                    </button>
                    <button type="submit" id="wfa-modal-save" class="inline-flex min-h-[52px] min-w-[180px] items-center justify-center rounded-2xl bg-blue-600 px-8 text-base font-extrabold text-white shadow-sm hover:bg-blue-700">
                        บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="wfa-guide-modal" class="wfa-guide-modal" aria-hidden="true">
        <div class="wfa-guide-card">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 p-5">
                <div>
                    <div class="wfa-chip">คู่มือ</div>
                    <h2 class="mt-3 text-xl font-extrabold text-slate-950">คู่มือการใช้งานรายงานภาระงาน (WFA)</h2>
                </div>
                <button type="button" id="wfa-guide-close" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 hover:bg-slate-100">ปิด</button>
            </div>
            <div class="wfa-guide-body space-y-5 p-5 text-sm text-slate-700">
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-900">
                    <div class="font-extrabold">สิ่งสำคัญเรื่องเดือน</div>
                    <p class="mt-1 leading-6">
                        ช่อง “WFA ประจำเดือน” คือเดือนของรอบรายงาน ส่วนข้อความในช่อง Output จะอ้างอิงเดือนก่อนหน้า 1 เดือนโดยอัตโนมัติ
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="font-extrabold text-slate-900">ขั้นตอนเพิ่มรายการใหม่</div>
                        <ol class="mt-2 list-decimal space-y-1 pl-5 leading-6">
                            <li>กด “เพิ่มผลลัพธ์ของงาน (Output) รายเดือน”</li>
                            <li>เลือก “WFA ประจำเดือน”</li>
                            <li>กรอกข้อมูลในช่องรายละเอียด Output</li>
                            <li>กด “บันทึก”</li>
                            <li>เลือกดาวน์โหลด PDF หรือ Word เมื่อจำเป็นต้องส่งรายงาน</li>
                        </ol>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="font-extrabold text-slate-900">สิทธิ์การใช้งาน</div>
                        <ul class="mt-2 list-disc space-y-1 pl-5 leading-6">
                            <li>ผู้ใช้ทั่วไปจัดการรายการของตนเองได้</li>
                            <li>ผู้บริหารหน่วยดูรายงานตามสิทธิ์ที่ได้รับ</li>
                            <li>Admin/SU Admin จัดการรายงานได้ทุกคน</li>
                            <li>ถ้าไม่มีสิทธิ์แก้ไข ระบบจะซ่อนปุ่มบันทึก/ลบ</li>
                        </ul>
                    </div>
                </div>

                <div>
                    <div class="mb-2 font-extrabold text-slate-900">ช่องและตัวเลือกในหน้าต่างเพิ่ม/แก้ไข</div>
                    <div class="overflow-x-auto">
                        <table class="wfa-guide-table">
                            <thead>
                                <tr>
                                    <th>ส่วนที่เลือก/ปุ่ม</th>
                                    <th>ใช้ทำอะไร</th>
                                    <th>ควรรู้อะไร</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="font-bold">รายงานภาระงาน (WFA)</td>
                                    <td>แสดงชื่อรายงานที่ระบบสร้างให้อัตโนมัติ</td>
                                    <td>ระบบตั้งชื่อจากชื่อผู้ใช้ เดือน WFA และเดือนผลงานรายเดือน</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">WFA ประจำเดือน</td>
                                    <td>เลือกเดือนรอบรายงานที่ต้องการส่ง</td>
                                    <td>ช่อง Output จะอ้างอิงเดือนก่อนหน้า 1 เดือน</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">ช่องรายละเอียด Output</td>
                                    <td>กรอกผลลัพธ์ของงานรายเดือน</td>
                                    <td>placeholder เป็นคำแนะนำเท่านั้น และจะไม่ถูกบันทึกเป็นข้อมูล</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">แถบเครื่องมือ</td>
                                    <td>จัดรูปแบบข้อความ</td>
                                    <td>ใช้ตัวหนา ตัวเอียง ขีดเส้นใต้ รายการ จัดแนว หรือล้างรูปแบบ</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">บันทึก</td>
                                    <td>บันทึกรายการใหม่หรือการแก้ไข</td>
                                    <td>หลังบันทึก ระบบจะถามว่าต้องการ export PDF/Word หรือไม่</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">ลบรายการนี้</td>
                                    <td>ลบ WFA ที่เปิดอยู่</td>
                                    <td>ระบบถามยืนยันก่อนลบ และแสดงเฉพาะรายการที่มีสิทธิ์ลบ</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <div class="mb-2 font-extrabold text-slate-900">ตัวเลือกในตารางรายการ WFA</div>
                    <div class="overflow-x-auto">
                        <table class="wfa-guide-table">
                            <thead>
                                <tr>
                                    <th>ส่วนที่เลือก/ปุ่ม</th>
                                    <th>ใช้ทำอะไร</th>
                                    <th>ควรรู้อะไร</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="font-bold">ค้นหา</td>
                                    <td>ค้นหาจากรหัส ชื่อรายงาน เดือน หรือข้อความใน Output</td>
                                    <td>ใช้หารายการเก่าหรือรายการของเดือนที่ต้องการอย่างรวดเร็ว</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">เรียงรายการ</td>
                                    <td>เปลี่ยนลำดับการแสดงผลในตาราง</td>
                                    <td>เลือกได้ เช่น วันที่บันทึกล่าสุด เดือนล่าสุด เดือนเก่าสุด หรือชื่อรายงาน</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">ชื่อรายงาน</td>
                                    <td>เปิดดูหรือแก้ไขรายละเอียด</td>
                                    <td>บนหน้าจอใหญ่จะเปิด modal ส่วนบางหน้าจออาจเปิดหน้าแก้ไขโดยตรง</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">PDF</td>
                                    <td>ดาวน์โหลด/เปิดรายงาน PDF</td>
                                    <td>เหมาะสำหรับส่งอ่านหรือตรวจสอบแบบคงรูปแบบ</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">Word</td>
                                    <td>ดาวน์โหลดรายงาน Word</td>
                                    <td>เหมาะเมื่อต้องนำไฟล์ไปแก้ไขหรือจัดหน้าเพิ่มเติม</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">ลบ</td>
                                    <td>ลบรายการจากตาราง</td>
                                    <td>ระบบถามยืนยันก่อนลบ และแสดงเฉพาะรายการที่มีสิทธิ์ลบ</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="font-extrabold text-slate-900">ก่อนบันทึกควรตรวจ</div>
                        <ul class="mt-2 list-disc space-y-1 pl-5 leading-6">
                            <li>เลือกเดือน WFA ถูกต้อง</li>
                            <li>ชื่อรายงานตรงกับรอบรายงาน</li>
                            <li>ช่องรายละเอียดมีข้อมูลจริง ไม่ใช่ placeholder</li>
                            <li>ไม่มีข้อความทดลองหรือข้อมูลที่ไม่ต้องการส่ง</li>
                        </ul>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="font-extrabold text-slate-900">หลังดาวน์โหลดไฟล์ควรตรวจ</div>
                        <ul class="mt-2 list-disc space-y-1 pl-5 leading-6">
                            <li>ชื่อรายงานและเดือนถูกต้อง</li>
                            <li>ภาระงาน/FTE แสดงครบตามข้อมูลในระบบ</li>
                            <li>ไฟล์ PDF/Word เป็นข้อมูลล่าสุดหลังบันทึก</li>
                            <li>ถ้าแก้ไขข้อมูล ต้องดาวน์โหลดไฟล์ใหม่อีกครั้ง</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="wfa-toast" class="wfa-toast">อัพเดทข้อมูลแล้ว</div>

    <script>
        (() => {
            const reports = @js($reportPayloads);
            const monthOptions = @js($monthOptions->values());
            const createUrl = @js(route('wfa.store'));
            const currentUserName = @js(auth()->user()->full_name);
            const csrf = @js(csrf_token());
            let rows = Array.from(document.querySelectorAll('[data-wfa-row]'));
            const tableBody = document.getElementById('wfa-table-body');
            const emptySearch = document.getElementById('wfa-empty-search');
            const searchInput = document.getElementById('wfa-live-search');
            const sortSelect = document.getElementById('wfa-sort');
            const modal = document.getElementById('wfa-modal');
            const guideModal = document.getElementById('wfa-guide-modal');
            const guideOpen = document.getElementById('wfa-guide-open');
            const guideClose = document.getElementById('wfa-guide-close');
            const form = document.getElementById('wfa-modal-form');
            const editor = document.getElementById('wfa-modal-editor');
            const editorPlaceholder = document.getElementById('wfa-modal-placeholder');
            const output = document.getElementById('wfa-modal-output');
            const methodInput = form.querySelector('input[name="_method"]');
            const monthInput = document.getElementById('wfa-modal-month');
            const monthGrid = document.getElementById('wfa-modal-month-grid');
            const monthSelect = document.getElementById('wfa-modal-month-select');
            const monthReadonly = document.getElementById('wfa-modal-month-readonly');
            const titleInput = document.getElementById('wfa-modal-title');
            const codeBadge = document.getElementById('wfa-modal-code');
            const modalHeading = document.getElementById('wfa-modal-heading');
            const exportChoice = document.getElementById('wfa-export-choice');
            const exportPdf = document.getElementById('wfa-export-pdf');
            const exportWord = document.getElementById('wfa-export-word');
            const exportPick = document.getElementById('wfa-export-pick');
            const exportNo = document.getElementById('wfa-export-no');
            const toast = document.getElementById('wfa-toast');
            const saveButton = document.getElementById('wfa-modal-save');
            const deleteButton = document.getElementById('wfa-modal-delete');
            let activeReportId = null;
            let isSaving = false;

            const normalize = (value) => String(value || '').toLocaleLowerCase('th').replace(/\s+/g, ' ').trim();
            const escapeHtml = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const wfaPlaceholderHtml = (monthValue) => {
                const option = monthOptions.find((item) => item.value === monthValue);
                const outputLabel = option?.output_label || option?.label || '';
                return outputLabel
                    ? `พิมพ์ output ที่เกิดขึ้น จาก WFA ในเดือน <strong>${escapeHtml(outputLabel)}</strong>`
                    : 'พิมพ์ output ที่เกิดขึ้น จาก WFA';
            };
            const wfaReportTitle = (ownerName, option) => {
                if (!option?.label) return '';
                return `WFA ${ownerName} ประจำเดือน ${option.label} และ ผลงานรายเดือน ${option.output_label || ''}`.trim();
            };
            const actionIconSources = {
                PDF: 'https://thesvg.org/icons/pdf/default.svg',
                Word: 'https://thesvg.org/icons/microsoft-word/default.svg',
            };

            const editorIsEmpty = () => editor.innerHTML.trim() === '' || editor.innerText.trim() === '';
            const refreshEditorPlaceholder = () => {
                editorPlaceholder?.classList.toggle('is-hidden', !editorIsEmpty());
            };

            const setEditorPlaceholder = (monthValue) => {
                if (!editorPlaceholder) return;
                editorPlaceholder.innerHTML = wfaPlaceholderHtml(monthValue);
                refreshEditorPlaceholder();
            };

            const applySearch = () => {
                const keyword = normalize(searchInput.value);
                let visible = 0;
                rows.forEach((row) => {
                    const matched = keyword === '' || normalize(row.dataset.searchText).includes(keyword);
                    row.classList.toggle('hidden', !matched);
                    if (matched) visible += 1;
                });
                emptySearch.classList.toggle('hidden', visible !== 0 || rows.length === 0);
            };

            const applySort = () => {
                const value = sortSelect.value;
                const sorted = rows.slice().sort((a, b) => {
                    if (value === 'month_desc') return String(b.dataset.month).localeCompare(String(a.dataset.month));
                    if (value === 'month_asc') return String(a.dataset.month).localeCompare(String(b.dataset.month));
                    if (value === 'title_asc') return String(a.dataset.title).localeCompare(String(b.dataset.title), 'th');
                    return Number(b.dataset.created || 0) - Number(a.dataset.created || 0);
                });
                sorted.forEach((row) => tableBody.insertBefore(row, emptySearch));
                applySearch();
            };

            const showToast = (message) => {
                toast.textContent = message || 'อัพเดทข้อมูลแล้ว';
                toast.classList.add('is-show');
                window.setTimeout(() => toast.classList.remove('is-show'), 2600);
            };

            const setSavingState = (saving) => {
                isSaving = saving;
                if (saveButton) {
                    saveButton.disabled = saving;
                    saveButton.textContent = saving ? 'กำลังบันทึก...' : 'บันทึก';
                    saveButton.classList.toggle('opacity-70', saving);
                    saveButton.classList.toggle('cursor-not-allowed', saving);
                }
                if (deleteButton && !deleteButton.classList.contains('hidden')) {
                    deleteButton.disabled = saving;
                    deleteButton.classList.toggle('opacity-70', saving);
                    deleteButton.classList.toggle('cursor-not-allowed', saving);
                }
            };

            const setDeleteButtonState = (canDelete) => {
                if (!deleteButton) return;
                deleteButton.classList.toggle('hidden', !canDelete);
                deleteButton.disabled = !canDelete || isSaving;
                deleteButton.classList.toggle('opacity-70', isSaving && canDelete);
                deleteButton.classList.toggle('cursor-not-allowed', (!canDelete || isSaving) && canDelete);
            };

            const showSavingDialog = () => {
                if (!window.Swal) {
                    showToast('กำลังบันทึก...');
                    return;
                }

                Swal.fire({
                    title: 'กำลังบันทึก',
                    text: 'กรุณารอสักครู่',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'rounded-3xl',
                        title: 'text-slate-950',
                        htmlContainer: 'text-slate-500',
                    },
                    didOpen: () => Swal.showLoading(),
                });
            };

            const closeSavingDialog = async () => {
                if (!window.Swal) return;
                Swal.close();
                await new Promise((resolve) => window.setTimeout(resolve, 80));
            };

            const triggerDownload = (url, filename) => {
                const link = document.createElement('a');
                link.href = url;
                if (filename) {
                    link.download = filename;
                }
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                link.remove();
            };

            const handleExportChoice = (result, report) => {
                if (result?.isConfirmed) {
                    window.open(report.pdf_url, '_blank', 'noopener');
                    return;
                }

                if (result?.isDenied) {
                    triggerDownload(report.word_url, report.word_filename);
                    return;
                }

                closeModal();
                showToast('บันทึกข้อมูลแล้ว');
            };

            const askExportAfterSave = (report) => {
                exportPdf.href = report.pdf_url;
                exportWord.href = report.word_url;
                exportPdf.removeAttribute('download');
                exportPdf.target = '_blank';
                exportPdf.rel = 'noopener';
                exportWord.download = report.word_filename || '';
                exportPdf.classList.add('hidden');
                exportWord.classList.add('hidden');
                exportChoice.classList.add('is-open');

                if (!window.Swal) {
                    showToast('บันทึกแล้ว เลือก PDF / Word ได้จากแถบด้านล่าง');
                    return;
                }

                window.setTimeout(() => {
                    Swal.fire({
                        title: 'บันทึกข้อมูลแล้ว',
                        text: 'ต้องการ export เอกสารหรือไม่',
                        icon: 'success',
                        showCancelButton: true,
                        showDenyButton: true,
                        confirmButtonText: 'PDF',
                        denyButtonText: 'Word',
                        cancelButtonText: 'ไม่',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'rounded-3xl',
                            title: 'text-slate-950',
                            htmlContainer: 'text-slate-500',
                            actions: 'gap-2',
                            confirmButton: 'inline-flex min-h-[42px] min-w-[86px] items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-extrabold text-white hover:bg-blue-700',
                            denyButton: 'inline-flex min-h-[42px] min-w-[86px] items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-extrabold text-slate-700 hover:bg-slate-100',
                            cancelButton: 'inline-flex min-h-[42px] min-w-[86px] items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-extrabold text-slate-500 hover:bg-slate-100',
                        },
                    })
                        .then((result) => handleExportChoice(result, report))
                        .catch((swalError) => {
                            console.warn('WFA export prompt failed', swalError);
                            showToast('บันทึกแล้ว เลือก PDF / Word ได้จากแถบด้านล่าง');
                        });
                }, 160);
            };

            const updateMonthChoices = (value) => {
                monthInput.value = value || '';
                monthSelect.value = monthInput.value;
                monthGrid.querySelectorAll('[data-wfa-month-choice]').forEach((button) => {
                    button.classList.toggle('is-selected', button.dataset.wfaMonthChoice === monthInput.value);
                });
                const selected = Array.from(monthGrid.querySelectorAll('[data-wfa-month-choice]'))
                    .find((button) => button.dataset.wfaMonthChoice === monthInput.value);
                if (selected) {
                    const option = monthOptions.find((item) => item.value === monthInput.value);
                    const ownerName = activeReportId && reports[activeReportId]?.person_name
                        ? reports[activeReportId].person_name
                        : currentUserName;
                    titleInput.value = wfaReportTitle(ownerName, option) || selected.dataset.title;
                }
                setEditorPlaceholder(monthInput.value);
            };

            const ensureMonthChoice = (value, label, title) => {
                if (!value || Array.from(monthGrid.querySelectorAll('[data-wfa-month-choice]')).some((button) => button.dataset.wfaMonthChoice === value)) return;
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'wfa-month-choice';
                button.dataset.wfaMonthChoice = value;
                button.dataset.title = title || label || value;
                button.textContent = label || value;
                monthGrid.appendChild(button);
            };

            const bindRowOpen = (row) => {
                row.querySelector('[data-open-wfa]')?.addEventListener('click', (event) => {
                    openModal(event.currentTarget.dataset.openWfa);
                });
                row.querySelector('[data-delete-wfa]')?.addEventListener('click', (event) => {
                    deleteReportById(event.currentTarget.dataset.deleteWfa);
                });
            };

            const createRow = (report) => {
                report.can_view = report.can_view ?? true;
                report.can_edit = report.can_edit ?? true;
                const row = document.createElement('tr');
                row.dataset.wfaRow = '1';
                row.dataset.reportId = report.id;
                row.dataset.month = report.filter_month_key;
                row.dataset.created = report.created_timestamp || 0;
                row.dataset.title = report.title;
                row.dataset.searchText = normalize([report.code, report.title, report.month_label, report.created_at_label, report.output_text].join(' '));

                const codeCell = row.insertCell();
                codeCell.className = 'whitespace-nowrap';
                codeCell.className = 'whitespace-nowrap font-semibold text-slate-600';
                codeCell.textContent = report.code;

                const titleCell = row.insertCell();
                const titleButton = document.createElement('button');
                titleButton.type = 'button';
                titleButton.className = 'hidden text-left font-extrabold leading-6 text-blue-700 hover:underline md:inline';
                titleButton.dataset.openWfa = report.id;
                titleButton.textContent = report.title;
                titleCell.appendChild(titleButton);
                const mobileLink = document.createElement('a');
                mobileLink.href = report.edit_url;
                mobileLink.className = 'font-extrabold leading-6 text-blue-700 hover:underline md:hidden';
                mobileLink.textContent = report.title;
                titleCell.appendChild(mobileLink);
                const created = document.createElement('div');
                created.className = 'mt-1 text-xs font-semibold text-slate-500';
                created.textContent = 'วันที่บันทึก ' + report.created_at_label;
                titleCell.appendChild(created);
                const outputText = document.createElement('div');
                outputText.className = 'mt-1 line-clamp-2 text-xs leading-5 text-slate-500';
                outputText.dataset.rowOutput = report.id;
                outputText.textContent = report.output_text || '-';
                titleCell.appendChild(outputText);

                const monthCell = row.insertCell();
                const monthChip = document.createElement('span');
                const monthNumber = String(report.filter_month_key || '').slice(5, 7) || '12';
                monthChip.className = `wfa-month-chip wfa-month-chip--${monthNumber}`;
                monthChip.dataset.rowMonth = report.id;
                monthChip.textContent = report.month_label;
                monthCell.appendChild(monthChip);

                const actionCell = row.insertCell();
                const actionWrap = document.createElement('div');
                actionWrap.className = 'flex flex-wrap items-center justify-end gap-2';
                [['PDF', report.pdf_url], ['Word', report.word_url]].forEach(([label, href]) => {
                    const link = document.createElement('a');
                    link.href = href;
                    link.className = label === 'PDF'
                        ? 'wfa-action wfa-action--icon border border-red-200 bg-white text-red-600 hover:bg-red-50'
                        : 'wfa-action wfa-action--icon border border-blue-200 bg-white text-blue-600 hover:bg-blue-50';
                    link.title = label;
                    link.setAttribute('aria-label', label);
                    if (label === 'PDF') {
                        link.target = '_blank';
                        link.rel = 'noopener';
                        link.removeAttribute('download');
                    } else {
                        link.download = report.word_filename || '';
                    }
                    const icon = document.createElement('img');
                    icon.src = actionIconSources[label];
                    icon.alt = '';
                    icon.setAttribute('aria-hidden', 'true');
                    icon.className = 'wfa-action-logo';
                    link.appendChild(icon);
                    const labelText = document.createElement('span');
                    labelText.className = 'sr-only';
                    labelText.textContent = label;
                    link.appendChild(labelText);
                    actionWrap.appendChild(link);
                });
                if (report.can_edit && report.delete_url) {
                    const deleteRowButton = document.createElement('button');
                    deleteRowButton.type = 'button';
                    deleteRowButton.className = 'wfa-action wfa-action--icon wfa-action--delete border border-red-200 bg-white text-red-600 hover:bg-red-50';
                    deleteRowButton.dataset.deleteWfa = report.id;
                    deleteRowButton.title = 'ลบ';
                    deleteRowButton.setAttribute('aria-label', 'ลบ');
                    deleteRowButton.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path d="M3 6h18"></path>
                            <path d="M8 6V4h8v2"></path>
                            <path d="M19 6l-1 14H6L5 6"></path>
                            <path d="M10 11v5"></path>
                            <path d="M14 11v5"></path>
                        </svg>
                        <span class="sr-only">ลบ</span>
                    `;
                    actionWrap.appendChild(deleteRowButton);
                }
                actionCell.appendChild(actionWrap);

                document.querySelector('[data-wfa-static-empty]')?.remove();
                tableBody.insertBefore(row, emptySearch);
                bindRowOpen(row);
                rows.push(row);
                return row;
            };

            const closeModal = () => {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                activeReportId = null;
                editor.contentEditable = 'true';
                editor.classList.remove('bg-slate-50');
                document.querySelectorAll('[data-wfa-command]').forEach((button) => {
                    button.disabled = false;
                    button.classList.remove('opacity-50', 'cursor-not-allowed');
                });
                saveButton?.classList.remove('hidden');
                setDeleteButtonState(false);
            };

            const openGuideModal = () => {
                guideModal?.classList.add('is-open');
                guideModal?.setAttribute('aria-hidden', 'false');
            };

            const closeGuideModal = () => {
                guideModal?.classList.remove('is-open');
                guideModal?.setAttribute('aria-hidden', 'true');
            };

            const openModal = (id) => {
                const report = reports[id];
                if (!report || !report.can_view) return;
                const canEdit = Boolean(report.can_edit);
                activeReportId = String(id);
                methodInput.disabled = false;
                form.action = report.update_url;
                modalHeading.textContent = canEdit ? 'แก้ไข Output ใน WFA' : 'ดู Output ใน WFA';
                codeBadge.textContent = report.code;
                titleInput.value = report.title;
                ensureMonthChoice(report.filter_month_key, report.month_label, report.title);
                updateMonthChoices(report.filter_month_key);
                monthReadonly.textContent = 'ประจำเดือน ' + report.month_label;
                monthReadonly.style.display = 'flex';
                monthGrid.style.display = 'none';
                monthSelect.style.display = 'none';
                editor.innerHTML = report.output_html || '';
                refreshEditorPlaceholder();
                editor.contentEditable = canEdit ? 'true' : 'false';
                editor.classList.toggle('bg-slate-50', !canEdit);
                output.value = report.output_html || '';
                document.querySelectorAll('[data-wfa-command]').forEach((button) => {
                    button.disabled = !canEdit;
                    button.classList.toggle('opacity-50', !canEdit);
                    button.classList.toggle('cursor-not-allowed', !canEdit);
                });
                saveButton?.classList.toggle('hidden', !canEdit);
                setDeleteButtonState(canEdit && Boolean(report.delete_url));
                exportChoice.classList.remove('is-open');
                exportPdf.classList.add('hidden');
                exportWord.classList.add('hidden');
                exportPdf.href = report.pdf_url;
                exportWord.href = report.word_url;
                exportPdf.removeAttribute('download');
                exportPdf.target = '_blank';
                exportPdf.rel = 'noopener';
                exportWord.download = report.word_filename || '';
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                if (canEdit) {
                    window.setTimeout(() => editor.focus(), 40);
                }
            };

            const openCreateModal = () => {
                activeReportId = null;
                methodInput.disabled = true;
                form.action = createUrl;
                modalHeading.textContent = 'เพิ่มผลลัพธ์ของงาน (Output) รายเดือน';
                codeBadge.textContent = 'Output รายเดือนใหม่';
                editor.innerHTML = '';
                editor.contentEditable = 'true';
                editor.classList.remove('bg-slate-50');
                document.querySelectorAll('[data-wfa-command]').forEach((button) => {
                    button.disabled = false;
                    button.classList.remove('opacity-50', 'cursor-not-allowed');
                });
                saveButton?.classList.remove('hidden');
                setDeleteButtonState(false);
                output.value = '';
                const currentOption = monthOptions.find((option) => option.is_current) || monthOptions[0];
                updateMonthChoices(currentOption?.value || '');
                setEditorPlaceholder(currentOption?.value || '');
                monthReadonly.textContent = '';
                monthReadonly.style.display = 'none';
                monthGrid.style.display = 'none';
                monthSelect.style.display = 'block';
                exportChoice.classList.remove('is-open');
                exportPdf.classList.add('hidden');
                exportWord.classList.add('hidden');
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                window.setTimeout(() => editor.focus(), 40);
            };

            const ensureStaticEmptyRow = () => {
                if (rows.length > 0 || document.querySelector('[data-wfa-static-empty]')) return;
                const row = document.createElement('tr');
                row.dataset.wfaStaticEmpty = '1';
                const cell = row.insertCell();
                cell.colSpan = 4;
                cell.className = 'px-4 py-12 text-center';
                cell.innerHTML = `
                    <div class="text-base font-extrabold text-slate-800">ยังไม่มีรายงานภาระงาน (WFA)</div>
                    <div class="mt-1 text-sm text-slate-500">เริ่มจากปุ่มเพิ่มผลลัพธ์ของงาน (Output) รายเดือนด้านบน</div>
                `;
                tableBody.insertBefore(row, emptySearch);
            };

            const confirmDeleteReport = async (report) => {
                const label = report?.title || 'รายการนี้';
                if (!window.Swal) {
                    return window.confirm(`ยืนยันลบ ${label} ?`);
                }

                const result = await Swal.fire({
                    title: 'ลบรายการนี้?',
                    text: label,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ลบ',
                    cancelButtonText: 'ยกเลิก',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'rounded-3xl',
                        title: 'text-slate-950',
                        htmlContainer: 'text-slate-500',
                        actions: 'gap-2',
                        confirmButton: 'inline-flex min-h-[42px] min-w-[86px] items-center justify-center rounded-xl bg-red-600 px-4 text-sm font-extrabold text-white hover:bg-red-700',
                        cancelButton: 'inline-flex min-h-[42px] min-w-[86px] items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-extrabold text-slate-600 hover:bg-slate-100',
                    },
                });

                return result.isConfirmed;
            };

            const deleteReportById = async (reportId) => {
                if (isSaving || !reportId) return;
                const reportKey = String(reportId);
                const report = reports[reportKey];
                if (!report?.can_edit || !report.delete_url) return;
                const confirmed = await confirmDeleteReport(report);
                if (!confirmed) return;

                setSavingState(true);
                try {
                    const body = new URLSearchParams();
                    body.set('_method', 'DELETE');
                    const response = await fetch(report.delete_url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body,
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || data.ok === false) {
                        throw new Error(data.message || 'ลบไม่สำเร็จ');
                    }

                    const row = document.querySelector(`[data-wfa-row][data-report-id="${report.id}"]`);
                    if (row) {
                        row.remove();
                        rows = rows.filter((candidate) => candidate !== row);
                    }
                    delete reports[reportKey];
                    setSavingState(false);
                    if (activeReportId === reportKey) {
                        closeModal();
                    }
                    ensureStaticEmptyRow();
                    applySort();
                    showToast(data.message || 'ลบรายงานภาระงาน (WFA) แล้ว');
                } catch (error) {
                    setSavingState(false);
                    showToast(error.message || 'ลบไม่สำเร็จ');
                }
            };

            const deleteActiveReport = async () => {
                await deleteReportById(activeReportId);
            };

            rows.forEach(bindRowOpen);
            document.getElementById('wfa-create-desktop')?.addEventListener('click', openCreateModal);
            deleteButton?.addEventListener('click', deleteActiveReport);
            guideOpen?.addEventListener('click', openGuideModal);
            guideClose?.addEventListener('click', closeGuideModal);
            guideModal?.addEventListener('click', (event) => {
                if (event.target === guideModal) closeGuideModal();
            });

            document.getElementById('wfa-modal-close')?.addEventListener('click', closeModal);
            modal.addEventListener('click', (event) => {
                if (event.target === modal) closeModal();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && guideModal?.classList.contains('is-open')) {
                    closeGuideModal();
                }
            });

            document.querySelectorAll('[data-wfa-command]').forEach((button) => {
                button.addEventListener('click', () => {
                    editor.focus();
                    document.execCommand(button.dataset.wfaCommand, false, null);
                    refreshEditorPlaceholder();
                });
            });

            monthGrid.addEventListener('click', (event) => {
                const button = event.target.closest('[data-wfa-month-choice]');
                if (!button) return;
                updateMonthChoices(button.dataset.wfaMonthChoice);
            });
            monthSelect.addEventListener('change', () => updateMonthChoices(monthSelect.value));
            editor.addEventListener('input', () => {
                refreshEditorPlaceholder();
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (isSaving) return;
                if (activeReportId && !reports[activeReportId]?.can_edit) return;
                output.value = editor.innerHTML.trim();
                const formData = new FormData(form);
                setSavingState(true);
                showSavingDialog();
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: formData,
                    });
                    const data = await response.json();
                    if (!response.ok || !data.ok) {
                        throw new Error(data.message || 'บันทึกไม่สำเร็จ');
                    }
                    setSavingState(false);
                    const report = data.report;
                    const reportKey = String(report.id);
                    reports[reportKey] = { ...(reports[reportKey] || {}), ...report };
                    activeReportId = reportKey;
                    methodInput.disabled = false;
                    form.action = report.update_url;
                    modalHeading.textContent = 'แก้ไข Output ใน WFA';
                    codeBadge.textContent = report.code;
                    setDeleteButtonState(Boolean(report.can_edit && report.delete_url));
                    try {
                        const row = document.querySelector(`[data-wfa-row][data-report-id="${report.id}"]`) || createRow(report);
                        if (row) {
                            row.dataset.month = report.filter_month_key;
                            row.dataset.title = report.title;
                            row.dataset.created = report.created_timestamp || row.dataset.created || 0;
                            row.dataset.searchText = normalize([report.code, report.title, report.month_label, report.created_at_label, report.output_text].join(' '));
                            row.querySelector('[data-open-wfa]') && (row.querySelector('[data-open-wfa]').textContent = report.title);
                            row.querySelector('[data-open-wfa]') && (row.querySelector('[data-open-wfa]').dataset.openWfa = report.id);
                            const mobileLink = row.querySelector('a.md\\:hidden');
                            if (mobileLink) {
                                mobileLink.textContent = report.title;
                                mobileLink.href = report.edit_url;
                            }
                            if (row.querySelector(`[data-row-month="${report.id}"]`)) {
                                const monthChip = row.querySelector(`[data-row-month="${report.id}"]`);
                                const monthNumber = String(report.filter_month_key || '').slice(5, 7) || '12';
                                monthChip.className = `wfa-month-chip wfa-month-chip--${monthNumber}`;
                                monthChip.textContent = report.month_label;
                            }
                            row.querySelector(`[data-row-output="${report.id}"]`) && (row.querySelector(`[data-row-output="${report.id}"]`).textContent = report.output_text || '-');
                        }
                        applySort();
                    } catch (rowError) {
                        console.warn('WFA row update failed after save', rowError);
                    }
                    titleInput.value = report.title;
                    exportPdf.href = report.pdf_url;
                    exportWord.href = report.word_url;
                    exportPdf.removeAttribute('download');
                    exportPdf.target = '_blank';
                    exportPdf.rel = 'noopener';
                    exportWord.download = report.word_filename || '';
                    exportChoice.classList.remove('is-open');
                    await closeSavingDialog();
                    askExportAfterSave(report);
                } catch (error) {
                    setSavingState(false);
                    await closeSavingDialog();
                    showToast(error.message || 'บันทึกไม่สำเร็จ');
                }
            });

            exportNo.addEventListener('click', () => {
                exportChoice.classList.remove('is-open');
                showToast('อัพเดทข้อมูลแล้ว');
            });
            exportPick.addEventListener('click', () => {
                exportPdf.classList.remove('hidden');
                exportWord.classList.remove('hidden');
            });

            searchInput.addEventListener('input', applySearch);
            sortSelect.addEventListener('change', applySort);
            applySort();
            applySearch();
        })();
    </script>
@endsection

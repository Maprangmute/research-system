@php
    $editing = isset($report) && $report;
    $isModalView = request()->boolean('modal');
    $modalQuery = $isModalView ? ['modal' => 1] : [];
    $authUser = auth()->user();
    $autoSubmitterName = trim((string) ($authUser->prefix ?? '') . ' ' . (string) ($authUser->name ?? ''));
    if ($autoSubmitterName === '' && $editing) {
        $autoSubmitterName = trim((string) ($report->submitter_name ?? ''));
    }

    $submitDate = old('submit_date');
    if ($submitDate === null) {
        if ($editing && !empty($report->submit_date)) {
            try {
                $submitDate = \Carbon\Carbon::parse($report->submit_date)->format('d-m-Y');
            } catch (\Throwable $e) {
                $submitDate = '';
            }
        } else {
            $submitDate = now()->format('d-m-Y');
        }
    }

    $progressRows = old('progress_rows');
    if (!is_array($progressRows)) {
        $progressRows = $editing ? (is_array($report->progress_rows ?? null) ? $report->progress_rows : []) : [];
    }
    if (!$progressRows || count($progressRows) === 0) {
        $progressRows = [['plan' => '', 'actual' => '', 'alignment' => '']];
    }

    $issueRows = old('issue_rows');
    if (!is_array($issueRows)) {
        $issueRows = $editing ? (is_array($report->issue_rows ?? null) ? $report->issue_rows : []) : [];
    }
    if (!$issueRows || count($issueRows) === 0) {
        $issueRows = [['problem' => '', 'solution' => '', 'consultation' => '']];
    }

    $fieldBaseClass = 'mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:outline-none';
    $fieldNormalClass = 'border-slate-300 focus:ring-blue-200';
    $fieldErrorClass = 'border-red-500 bg-red-50 focus:ring-red-100';
    $rowFieldClass = 'mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm leading-6 text-slate-800 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none';
    $rowErrorClass = 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-100';
    $progressSectionHasError = collect($errors->keys())->contains(fn($key) => str_starts_with($key, 'progress_rows.'));
    $issueSectionHasError = collect($errors->keys())->contains(fn($key) => str_starts_with($key, 'issue_rows.'));
    $submitDateInputId = 'progress_submit_date_' . ($editing ? (string) ($report->id ?? 'edit') : 'new');
@endphp

<style>
    @keyframes addButtonBlink {
        0%, 100% { transform: scale(1); filter: brightness(1); }
        50% { transform: scale(1.06); filter: brightness(1.1); }
    }

    @keyframes newRowBlink {
        0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.35); background-color: #eff6ff; }
        50% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); background-color: #f8fafc; }
        100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); background-color: inherit; }
    }

    .blink-add-btn {
        animation: addButtonBlink 0.32s ease 2;
    }

    .blink-new-row {
        animation: newRowBlink 1.2s ease;
    }
</style>

<div class="max-w-6xl mx-auto mt-6 px-3">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 md:p-6 space-y-6">
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 md:p-6">
            <div class="absolute -right-14 -top-14 h-40 w-40 rounded-full bg-cyan-100/60 blur-2xl"></div>
            <div class="absolute -left-12 -bottom-16 h-44 w-44 rounded-full bg-indigo-100/50 blur-2xl"></div>

            <div class="relative">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                        รายงานความก้าวหน้า
                    </span>
                    @if (!empty($project->project_code))
                        <span class="inline-flex items-center rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                            {{ $project->project_code }}
                        </span>
                    @endif
                </div>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50/90 px-4 py-3 md:py-4 h-full">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Project</p>
                        <p class="mt-1 text-xl md:text-2xl font-extrabold leading-snug text-slate-900 break-words">
                            {{ $project->project_name_th }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 md:py-4 h-full">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Report</p>
                        <h1 class="mt-1 text-sm md:text-base font-semibold leading-snug text-slate-800">
                            {{ $pageTitle ?? ($editing ? 'แก้ไขรายงานความก้าวหน้า' : 'เพิ่มรายงานความก้าวหน้า') }}
                        </h1>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ $action }}" class="space-y-6" id="progressReportForm" novalidate>
            @csrf
            @if ($isModalView)
                <input type="hidden" name="modal" value="1">
            @endif
            @if (strtoupper($httpMethod ?? 'POST') !== 'POST')
                @method($httpMethod)
            @endif

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    กรุณาตรวจสอบข้อมูลที่ทำเครื่องหมายสีแดงก่อนบันทึก
                </div>
            @endif

            <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                    <p class="text-xs text-emerald-700">ผู้จัดทำ (ระบบใส่อัตโนมัติจากผู้ใช้ที่ล็อกอิน)</p>
                    <p class="mt-1 text-sm font-semibold text-emerald-900">{{ $autoSubmitterName ?: '-' }}</p>
                </div>

                <label class="block rounded-xl border border-slate-200 bg-slate-50 p-3 md:p-4">
                    <span class="text-sm text-slate-700">วันที่รายงาน <span class="text-red-500">*</span></span>
                    <div class="relative">
                        <input
                            id="{{ $submitDateInputId }}"
                            type="text"
                            name="submit_date"
                            value="{{ $submitDate }}"
                            placeholder="กรุณาเลือกวันที่"
                            readonly
                            required
                            data-required-label="วันที่รายงาน"
                            class="{{ $fieldBaseClass }} {{ $errors->has('submit_date') ? $fieldErrorClass : $fieldNormalClass }} js-date-be pr-10 bg-white cursor-pointer"
                        >
                        <button
                            type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
                            data-open-picker
                            data-target="{{ $submitDateInputId }}"
                            aria-label="เปิดปฏิทิน"
                        >
                            📅
                        </button>
                    </div>
                    @error('submit_date')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </label>

                <label class="block md:col-span-2">
                    <span class="text-sm text-slate-700">หัวข้อรายงาน <span class="text-red-500">*</span></span>
                    <input
                        type="text"
                        name="progress_title"
                        value="{{ old('progress_title', $editing ? ($report->progress_title ?? '') : '') }}"
                        required
                        data-required-label="หัวข้อรายงาน"
                        class="{{ $fieldBaseClass }} {{ $errors->has('progress_title') ? $fieldErrorClass : $fieldNormalClass }}"
                        placeholder="เช่น ความก้าวหน้าของการดำเนินงาน (VBHC)"
                    >
                    @error('progress_title')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </label>

                <label class="block md:col-span-2">
                    <span class="text-sm text-slate-700">รายละเอียดไฟล์ / ลิงก์ (ถ้ามี)</span>
                    <input
                        type="text"
                        name="file_info"
                        value="{{ old('file_info', $editing ? ($report->file_info ?? '') : '') }}"
                        class="{{ $fieldBaseClass }} {{ $errors->has('file_info') ? $fieldErrorClass : $fieldNormalClass }}"
                        placeholder="ลิงก์ไฟล์รายงาน หรือหมายเหตุเพิ่มเติม"
                    >
                    @error('file_info')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </label>
            </section>

            <section class="space-y-4 rounded-2xl border border-blue-100 bg-gradient-to-b from-blue-50/80 to-white p-4 md:p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-lg md:text-xl font-bold text-slate-900">ความก้าวหน้าของการดำเนินงาน</h2>
                        <p class="text-sm text-slate-600 mt-1">พิมพ์หรือแก้ไขข้อมูลได้ทันทีในแต่ละช่อง</p>
                    </div>
                </div>

                <div class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs text-blue-700">
                    แนะนำ: 1 แถว = 1 กิจกรรมหลักของเดือนนั้น
                </div>

                @if ($progressSectionHasError)
                    <p class="text-xs text-red-600">มีข้อมูลในตารางความก้าวหน้าที่ไม่ผ่าน validation กรุณาตรวจสอบแต่ละรายการ</p>
                @endif

                <div id="progressRows" data-next-index="{{ count($progressRows) }}" class="space-y-4">
                    @foreach ($progressRows as $i => $row)
                        @php
                            $planKey = "progress_rows.$i.plan";
                            $actualKey = "progress_rows.$i.actual";
                            $alignmentKey = "progress_rows.$i.alignment";
                            $rowHasError = $errors->has($planKey) || $errors->has($actualKey) || $errors->has($alignmentKey);
                        @endphp
                        <div class="rounded-2xl border p-4 md:p-5 shadow-sm {{ $rowHasError ? 'border-red-300 bg-red-50/50' : 'border-slate-200 bg-white' }}" data-row-item data-row-type="progress" data-row-collapsed="0">
                            <div class="flex items-center justify-between mb-3">
                                <div class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5">
                                    <span class="text-xs text-slate-500">กิจกรรมที่</span>
                                    <span data-row-no class="text-sm font-bold text-slate-900">{{ $loop->iteration }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" data-toggle-row class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
                                        <span data-toggle-label>ย่อ</span>
                                    </button>
                                    <button type="button" data-remove-row class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">ลบแถวนี้</button>
                                </div>
                            </div>
                            <p data-row-summary class="hidden mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600"></p>
                            <div data-row-body class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">แผนการดำเนินงาน <span class="text-red-500">*</span></span>
                                    <textarea
                                        name="progress_rows[{{ $i }}][plan]"
                                        rows="4"
                                        required
                                        data-required-label="แผนการดำเนินงาน"
                                        class="{{ $rowFieldClass }} {{ $errors->has($planKey) ? $rowErrorClass : '' }}"
                                    >{{ old($planKey, (string) ($row['plan'] ?? '')) }}</textarea>
                                    @error($planKey)
                                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                    @enderror
                                </label>

                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">การดำเนินงานจริง <span class="text-red-500">*</span></span>
                                    <textarea
                                        name="progress_rows[{{ $i }}][actual]"
                                        rows="4"
                                        required
                                        data-required-label="การดำเนินงานจริง"
                                        class="{{ $rowFieldClass }} {{ $errors->has($actualKey) ? $rowErrorClass : '' }}"
                                    >{{ old($actualKey, (string) ($row['actual'] ?? '')) }}</textarea>
                                    @error($actualKey)
                                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                    @enderror
                                </label>

                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">ความสอดคล้องกับแผน <span class="text-red-500">*</span></span>
                                    <input
                                        type="text"
                                        list="alignmentOptions"
                                        name="progress_rows[{{ $i }}][alignment]"
                                        value="{{ old($alignmentKey, (string) ($row['alignment'] ?? '')) }}"
                                        required
                                        data-required-label="ความสอดคล้องกับแผน"
                                        class="{{ $rowFieldClass }} {{ $errors->has($alignmentKey) ? $rowErrorClass : '' }}"
                                        placeholder="เช่น เป็นไปตามแผน / ช้ากว่าแผน"
                                    >
                                    @error($alignmentKey)
                                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                    @enderror
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-2 flex justify-center">
                    <button
                        type="button"
                        data-add-row="progress"
                        class="inline-flex items-center justify-center min-w-[220px] px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm md:text-base font-semibold shadow-md hover:bg-blue-700"
                    >
                        + เพิ่มกิจกรรม
                    </button>
                </div>
            </section>

            <section class="space-y-4 rounded-2xl border border-indigo-100 bg-gradient-to-b from-indigo-50/80 to-white p-4 md:p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-lg md:text-xl font-bold text-slate-900">ปัญหาอุปสรรค</h2>
                        <p class="text-sm text-slate-600 mt-1">พิมพ์หรือแก้ไขข้อมูลได้ทันทีในแต่ละช่อง</p>
                    </div>
                </div>

                <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs text-indigo-700">
                    แนะนำ: ระบุปัญหาให้เฉพาะเจาะจง และใส่แนวทางแก้ไขที่ทำได้จริง
                </div>

                @if ($issueSectionHasError)
                    <p class="text-xs text-red-600">มีข้อมูลในตารางปัญหาอุปสรรคที่ไม่ผ่าน validation กรุณาตรวจสอบแต่ละรายการ</p>
                @endif

                <div id="issueRows" data-next-index="{{ count($issueRows) }}" class="space-y-4">
                    @foreach ($issueRows as $i => $row)
                        @php
                            $problemKey = "issue_rows.$i.problem";
                            $solutionKey = "issue_rows.$i.solution";
                            $consultationKey = "issue_rows.$i.consultation";
                            $rowHasError = $errors->has($problemKey) || $errors->has($solutionKey) || $errors->has($consultationKey);
                        @endphp
                        <div class="rounded-2xl border p-4 md:p-5 shadow-sm {{ $rowHasError ? 'border-red-300 bg-red-50/50' : 'border-slate-200 bg-white' }}" data-row-item data-row-type="issue" data-row-collapsed="0">
                            <div class="flex items-center justify-between mb-3">
                                <div class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5">
                                    <span class="text-xs text-slate-500">ปัญหาที่</span>
                                    <span data-row-no class="text-sm font-bold text-slate-900">{{ $loop->iteration }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" data-toggle-row class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
                                        <span data-toggle-label>ย่อ</span>
                                    </button>
                                    <button type="button" data-remove-row class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">ลบแถวนี้</button>
                                </div>
                            </div>
                            <p data-row-summary class="hidden mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600"></p>
                            <div data-row-body class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">ปัญหาอุปสรรค <span class="text-red-500">*</span></span>
                                    <textarea
                                        name="issue_rows[{{ $i }}][problem]"
                                        rows="4"
                                        required
                                        data-required-label="ปัญหาอุปสรรค"
                                        class="{{ $rowFieldClass }} {{ $errors->has($problemKey) ? $rowErrorClass : '' }}"
                                    >{{ old($problemKey, (string) ($row['problem'] ?? '')) }}</textarea>
                                    @error($problemKey)
                                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                    @enderror
                                </label>

                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">แนวทางการแก้ปัญหา <span class="text-red-500">*</span></span>
                                    <textarea
                                        name="issue_rows[{{ $i }}][solution]"
                                        rows="4"
                                        required
                                        data-required-label="แนวทางการแก้ปัญหา"
                                        class="{{ $rowFieldClass }} {{ $errors->has($solutionKey) ? $rowErrorClass : '' }}"
                                    >{{ old($solutionKey, (string) ($row['solution'] ?? '')) }}</textarea>
                                    @error($solutionKey)
                                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                    @enderror
                                </label>

                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">ประเด็นขอคำปรึกษาจากที่ประชุม <span class="text-red-500">*</span></span>
                                    <textarea
                                        name="issue_rows[{{ $i }}][consultation]"
                                        rows="4"
                                        required
                                        data-required-label="ประเด็นขอคำปรึกษาจากที่ประชุม"
                                        class="{{ $rowFieldClass }} {{ $errors->has($consultationKey) ? $rowErrorClass : '' }}"
                                    >{{ old($consultationKey, (string) ($row['consultation'] ?? '')) }}</textarea>
                                    @error($consultationKey)
                                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                    @enderror
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-2 flex justify-center">
                    <button
                        type="button"
                        data-add-row="issue"
                        class="inline-flex items-center justify-center min-w-[220px] px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm md:text-base font-semibold shadow-md hover:bg-indigo-700"
                    >
                        + เพิ่มปัญหา
                    </button>
                </div>
            </section>

            <div class="sticky bottom-0 z-20 -mx-4 md:-mx-6 border-t border-slate-200 bg-white/95 px-4 py-3 md:px-6 backdrop-blur">
                <div class="flex flex-col sm:flex-row justify-end gap-2">
                    @if (!$isModalView)
                        <a href="{{ route('projects.progress_reports.index', ['project' => $project->id] + $modalQuery) }}"
                            class="w-full sm:w-auto px-4 py-3 rounded-xl border border-slate-300 text-slate-700 text-center font-semibold hover:bg-slate-50">
                            ยกเลิก
                        </a>
                    @endif
                    <button type="submit"
                        class="w-full sm:w-auto sm:min-w-[260px] px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm md:text-base font-bold shadow-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-blue-200 transition">
                        {{ $submitLabel ?? ($editing ? 'บันทึกการแก้ไข' : 'บันทึกรายงาน') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<datalist id="alignmentOptions">
    <option value="เป็นไปตามแผน"></option>
    <option value="ช้ากว่าแผน"></option>
    <option value="ช้ากว่าแผนแต่ไม่กระทบต่อการดำเนินงานในภาพรวม"></option>
</datalist>

<template id="progressRowTemplate">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 md:p-5 shadow-sm" data-row-item data-row-type="progress" data-row-collapsed="0">
        <div class="flex items-center justify-between mb-3">
            <div class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5">
                <span class="text-xs text-slate-500">กิจกรรมที่</span>
                <span data-row-no class="text-sm font-bold text-slate-900"></span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" data-toggle-row class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
                    <span data-toggle-label>ย่อ</span>
                </button>
                <button type="button" data-remove-row class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">ลบแถวนี้</button>
            </div>
        </div>
        <p data-row-summary class="hidden mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600"></p>
        <div data-row-body class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <label class="block">
                <span class="text-xs font-semibold text-slate-600">แผนการดำเนินงาน <span class="text-red-500">*</span></span>
                <textarea
                    name="progress_rows[__INDEX__][plan]"
                    rows="4"
                    required
                    data-required-label="แผนการดำเนินงาน"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm leading-6 text-slate-800 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                ></textarea>
            </label>

            <label class="block">
                <span class="text-xs font-semibold text-slate-600">การดำเนินงานจริง <span class="text-red-500">*</span></span>
                <textarea
                    name="progress_rows[__INDEX__][actual]"
                    rows="4"
                    required
                    data-required-label="การดำเนินงานจริง"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm leading-6 text-slate-800 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                ></textarea>
            </label>

            <label class="block">
                <span class="text-xs font-semibold text-slate-600">ความสอดคล้องกับแผน <span class="text-red-500">*</span></span>
                <input
                    type="text"
                    list="alignmentOptions"
                    name="progress_rows[__INDEX__][alignment]"
                    required
                    data-required-label="ความสอดคล้องกับแผน"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm leading-6 text-slate-800 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                    placeholder="เช่น เป็นไปตามแผน / ช้ากว่าแผน"
                >
            </label>
        </div>
    </div>
</template>

<template id="issueRowTemplate">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 md:p-5 shadow-sm" data-row-item data-row-type="issue" data-row-collapsed="0">
        <div class="flex items-center justify-between mb-3">
            <div class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5">
                <span class="text-xs text-slate-500">ปัญหาที่</span>
                <span data-row-no class="text-sm font-bold text-slate-900"></span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" data-toggle-row class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
                    <span data-toggle-label>ย่อ</span>
                </button>
                <button type="button" data-remove-row class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">ลบแถวนี้</button>
            </div>
        </div>
        <p data-row-summary class="hidden mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600"></p>
        <div data-row-body class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <label class="block">
                <span class="text-xs font-semibold text-slate-600">ปัญหาอุปสรรค <span class="text-red-500">*</span></span>
                <textarea
                    name="issue_rows[__INDEX__][problem]"
                    rows="4"
                    required
                    data-required-label="ปัญหาอุปสรรค"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm leading-6 text-slate-800 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                ></textarea>
            </label>

            <label class="block">
                <span class="text-xs font-semibold text-slate-600">แนวทางการแก้ปัญหา <span class="text-red-500">*</span></span>
                <textarea
                    name="issue_rows[__INDEX__][solution]"
                    rows="4"
                    required
                    data-required-label="แนวทางการแก้ปัญหา"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm leading-6 text-slate-800 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                ></textarea>
            </label>

            <label class="block">
                <span class="text-xs font-semibold text-slate-600">ประเด็นขอคำปรึกษาจากที่ประชุม <span class="text-red-500">*</span></span>
                <textarea
                    name="issue_rows[__INDEX__][consultation]"
                    rows="4"
                    required
                    data-required-label="ประเด็นขอคำปรึกษาจากที่ประชุม"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm leading-6 text-slate-800 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                ></textarea>
            </label>
        </div>
    </div>
</template>

<script>
    (function() {
        const configs = {
            progress: {
                containerId: 'progressRows',
                templateId: 'progressRowTemplate',
                prefix: 'progress_rows',
            },
            issue: {
                containerId: 'issueRows',
                templateId: 'issueRowTemplate',
                prefix: 'issue_rows',
            },
        };

        const byId = (id) => document.getElementById(id);
        const form = byId('progressReportForm');

        if (!form) return;

        const trimValue = (field) => ((field?.value || '') + '').trim();
        const requiredErrorClasses = ['border-red-500', 'bg-red-50', 'focus:border-red-500', 'focus:ring-red-100'];
        const rowErrorClasses = ['border-red-300', 'bg-red-50/50'];
        const rowNormalClasses = ['border-slate-200', 'bg-white'];

        const getProgressSummary = (row) => {
            const plan = (row.querySelector('textarea[name$="[plan]"]')?.value || '').trim();
            const actual = (row.querySelector('textarea[name$="[actual]"]')?.value || '').trim();
            const alignment = (row.querySelector('input[name$="[alignment]"]')?.value || '').trim();
            const raw = plan || actual || alignment || 'ยังไม่มีรายละเอียดกิจกรรม';
            return raw.length > 120 ? `${raw.slice(0, 120)}...` : raw;
        };

        const getIssueSummary = (row) => {
            const problem = (row.querySelector('textarea[name$="[problem]"]')?.value || '').trim();
            const solution = (row.querySelector('textarea[name$="[solution]"]')?.value || '').trim();
            const consultation = (row.querySelector('textarea[name$="[consultation]"]')?.value || '').trim();
            const raw = problem || solution || consultation || 'ยังไม่มีรายละเอียดปัญหาอุปสรรค';
            return raw.length > 120 ? `${raw.slice(0, 120)}...` : raw;
        };

        const updateProgressSummary = (row) => {
            const summaryEl = row?.querySelector('[data-row-summary]');
            if (!summaryEl) return;
            summaryEl.textContent = getProgressSummary(row);
        };

        const updateIssueSummary = (row) => {
            const summaryEl = row?.querySelector('[data-row-summary]');
            if (!summaryEl) return;
            summaryEl.textContent = getIssueSummary(row);
        };

        const setRowCollapsed = (row, collapsed) => {
            if (!row) return;
            const rowType = row.dataset.rowType;
            if (rowType !== 'progress' && rowType !== 'issue') return;
            row.dataset.rowCollapsed = collapsed ? '1' : '0';

            const body = row.querySelector('[data-row-body]');
            const summaryEl = row.querySelector('[data-row-summary]');
            const label = row.querySelector('[data-toggle-label]');

            if (body) {
                body.classList.toggle('hidden', collapsed);
            }

            if (summaryEl) {
                if (rowType === 'progress') {
                    updateProgressSummary(row);
                } else {
                    updateIssueSummary(row);
                }
                summaryEl.classList.toggle('hidden', !collapsed);
            }

            if (label) {
                label.textContent = collapsed ? 'ขยาย' : 'ย่อ';
            }
        };

        const triggerBlink = (el, className, durationMs = 1200) => {
            if (!el) return;
            el.classList.remove(className);
            void el.offsetWidth;
            el.classList.add(className);
            window.setTimeout(() => el.classList.remove(className), durationMs);
        };

        const markFieldError = (field) => {
            if (!field) return;
            field.classList.add(...requiredErrorClasses);
            field.setAttribute('aria-invalid', 'true');
        };

        const clearFieldError = (field) => {
            if (!field) return;
            field.classList.remove(...requiredErrorClasses);
            field.removeAttribute('aria-invalid');
            if (typeof field.setCustomValidity === 'function') {
                field.setCustomValidity('');
            }
        };

        const markRowError = (row) => {
            if (!row) return;
            row.classList.remove(...rowNormalClasses);
            row.classList.add(...rowErrorClasses);
        };

        const clearRowError = (row) => {
            if (!row) return;
            row.classList.remove(...rowErrorClasses);
            row.classList.add(...rowNormalClasses);
        };

        const focusAndScrollToField = (field) => {
            if (!field) return;
            const row = field.closest?.('[data-row-item]');
            if (row) {
                setRowCollapsed(row, false);
            }
            field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            window.setTimeout(() => {
                if (typeof field.focus === 'function') {
                    field.focus();
                }
            }, 120);
        };

        const showFieldValidity = (field, message) => {
            if (!field) return;
            if (typeof field.setCustomValidity === 'function') {
                field.setCustomValidity(message || 'กรุณากรอกข้อมูลนี้');
            }
            if (typeof field.reportValidity === 'function') {
                field.reportValidity();
            }
        };

        const getRowRequiredFields = (row, type) => {
            if (type === 'progress') {
                return [
                    row.querySelector('textarea[name$="[plan]"]'),
                    row.querySelector('textarea[name$="[actual]"]'),
                    row.querySelector('input[name$="[alignment]"]'),
                ].filter(Boolean);
            }

            return [
                row.querySelector('textarea[name$="[problem]"]'),
                row.querySelector('textarea[name$="[solution]"]'),
                row.querySelector('textarea[name$="[consultation]"]'),
            ].filter(Boolean);
        };

        const isRowCompletelyEmpty = (row, type) => getRowRequiredFields(row, type).every((field) => trimValue(field) === '');

        const removeEmptyRows = (type) => {
            const cfg = configs[type];
            const container = byId(cfg.containerId);
            if (!container) return;

            container.querySelectorAll('[data-row-item]').forEach((row) => {
                if (isRowCompletelyEmpty(row, type)) {
                    row.remove();
                }
            });

            reindexRows(type);
        };

        const clearAllValidationHighlights = () => {
            form.querySelectorAll('[data-required-label]').forEach((field) => clearFieldError(field));
            Object.keys(configs).forEach((type) => {
                const cfg = configs[type];
                const container = byId(cfg.containerId);
                if (!container) return;
                container.querySelectorAll('[data-row-item]').forEach((row) => clearRowError(row));
            });
        };

        const validateRequiredFields = () => {
            const requiredFields = Array.from(form.querySelectorAll('[data-required-label]')).filter((field) => !field.disabled);
            for (const field of requiredFields) {
                if (trimValue(field) !== '') continue;

                markFieldError(field);

                const row = field.closest('[data-row-item]');
                if (row) {
                    markRowError(row);
                }

                const label = field.dataset.requiredLabel || 'ข้อมูลที่จำเป็น';
                const rowNo = row?.querySelector('[data-row-no]')?.textContent?.trim();
                const rowType = row?.dataset?.rowType === 'issue' ? 'ปัญหาที่' : 'กิจกรรมที่';
                const suffix = rowNo ? ` (${rowType} ${rowNo})` : '';

                return {
                    message: `กรุณากรอก ${label}${suffix}`,
                    target: field,
                };
            }

            return null;
        };

        const reindexRows = (type) => {
            const cfg = configs[type];
            const container = byId(cfg.containerId);
            if (!container) return;

            const rows = container.querySelectorAll('[data-row-item]');
            rows.forEach((row, idx) => {
                const no = row.querySelector('[data-row-no]');
                if (no) {
                    no.textContent = String(idx + 1);
                }

                row.querySelectorAll('input[name], textarea[name], select[name]').forEach((field) => {
                    const name = field.getAttribute('name');
                    if (!name) return;
                    const pattern = new RegExp(`^${cfg.prefix}\\[\\d+\\]`);
                    field.setAttribute('name', name.replace(pattern, `${cfg.prefix}[${idx}]`));
                });

                if (type === 'progress') updateProgressSummary(row);
                if (type === 'issue') updateIssueSummary(row);
            });

            container.dataset.nextIndex = String(rows.length);
        };

        const bindRow = (type, row) => {
            if (!row || row.dataset.bound === '1') return;
            row.dataset.bound = '1';

            const toggleBtn = row.querySelector('[data-toggle-row]');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    const isCollapsed = row.dataset.rowCollapsed === '1';
                    setRowCollapsed(row, !isCollapsed);
                });
            }

            row.querySelectorAll('textarea[name], input[name]').forEach((field) => {
                field.addEventListener('input', () => {
                    clearFieldError(field);
                    if (type === 'progress') updateProgressSummary(row);
                    if (type === 'issue') updateIssueSummary(row);
                    const stillInvalidInRow = row.querySelector('[aria-invalid="true"]');
                    if (!stillInvalidInRow) {
                        clearRowError(row);
                    }
                });
                field.addEventListener('change', () => {
                    clearFieldError(field);
                    const stillInvalidInRow = row.querySelector('[aria-invalid="true"]');
                    if (!stillInvalidInRow) {
                        clearRowError(row);
                    }
                });
            });

            if (row.hasAttribute('data-row-collapsed')) {
                setRowCollapsed(row, row.dataset.rowCollapsed === '1');
            }

            const removeBtn = row.querySelector('[data-remove-row]');
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    row.remove();
                    reindexRows(type);
                });
            }
        };

        const addRow = (type, addButton = null) => {
            const cfg = configs[type];
            const container = byId(cfg.containerId);
            const template = byId(cfg.templateId);
            if (!container || !template) return;

            const nextIndex = Number(container.dataset.nextIndex || container.querySelectorAll('[data-row-item]').length);
            const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
            container.insertAdjacentHTML('beforeend', html);

            const row = container.querySelector('[data-row-item]:last-child');
            bindRow(type, row);
            reindexRows(type);
            triggerBlink(addButton, 'blink-add-btn', 700);
            triggerBlink(row, 'blink-new-row', 1300);

            if ((type === 'progress' || type === 'issue') && row) {
                container.querySelectorAll('[data-row-item]').forEach((item) => {
                    if (item !== row) {
                        setRowCollapsed(item, true);
                    }
                });
                setRowCollapsed(row, false);
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        };

        Object.keys(configs).forEach((type) => {
            const cfg = configs[type];
            const container = byId(cfg.containerId);
            if (!container) return;

            container.querySelectorAll('[data-row-item]').forEach((row) => bindRow(type, row));
            reindexRows(type);

            const addBtn = document.querySelector(`[data-add-row="${type}"]`);
            if (addBtn) {
                addBtn.addEventListener('click', () => addRow(type, addBtn));
            }
        });

        form.querySelectorAll('[data-required-label]').forEach((field) => {
            field.addEventListener('input', () => clearFieldError(field));
            field.addEventListener('change', () => clearFieldError(field));
        });

        form.addEventListener('submit', (event) => {
            clearAllValidationHighlights();
            removeEmptyRows('progress');
            removeEmptyRows('issue');

            const validationError = validateRequiredFields();
            if (!validationError) return;

            event.preventDefault();
            const target = validationError.target;

            markFieldError(target);
            focusAndScrollToField(target);
            showFieldValidity(target, validationError.message);
        });

        const hasServerErrors = @json($errors->any());
        if (hasServerErrors) {
            const firstInvalidField = form.querySelector('input.border-red-500, textarea.border-red-500, select.border-red-500');
            if (firstInvalidField) {
                markFieldError(firstInvalidField);
                focusAndScrollToField(firstInvalidField);
                const label = firstInvalidField.dataset.requiredLabel || 'ข้อมูลที่จำเป็น';
                window.setTimeout(() => {
                    showFieldValidity(firstInvalidField, `กรุณาตรวจสอบ ${label}`);
                }, 120);
            }
        }
    })();
</script>

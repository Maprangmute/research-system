@extends('layouts.app')

<script>
    window.DISABLE_FTE_TABLE_MODE = true;
</script>

@if ($errors->any())
    <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $allUsers = \App\Models\User::orderBy('name')->get();
    $projectUserDirectory = $allUsers->map(fn($user) => [
        'id' => $user->id,
        'full_name' => $user->full_name,
        'research_display_name' => $user->research_display_name,
    ])->values();
    $piInternalAffiliation = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ คณะแพทยศาสตร์ศิริราชพยาบาล มหาวิทยาลัยมหิดล';
    $internalCoresearchUnit = 'ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
    $internalCoresearchAffiliation = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ คณะแพทยศาสตร์ศิริราชพยาบาล มหาวิทยาลัยมหิดล';
    $bookAuthorNameOptions = $allUsers
        ->map(fn($user) => trim(($user->prefix ?? '') . ' ' . ($user->name ?? '')))
        ->filter()
        ->unique()
        ->values();
    $roleGroups = ['Scientific', 'Management', 'IT + Communication', 'Logistics + Admin'];
    $jobDetails = [
        'Researcher',
        'Researcher Assistant',
        'Project Manager',
        'Research Coordinator',
        'นักวิชาการสารสนเทศ',
        'นักประชาสัมพันธ์',
        'เจ้าหน้าที่บริหารงานทั่วไป',
    ];
    $taskDetails = [
        'ติดต่อประสานงาน',
        'ประชุมผู้มีส่วนได้ส่วนเสีย (Stakeholder meeting)',
        'review literature',
        'เขียนproposal',
        'ดำเนินการเอกสารขอทุน/submit ทุน',
        'พัฒนาเครื่องมือและแนวทางในการเก็บข้อมูล',
        'ดำเนินการเอกสารขอirb/submit irb',
        'อบรมพัฒนาศักยภาพ',
        'เก็บข้อมูล',
        'วิเคราะห์ข้อมูล',
        'จัดทำรายงานความก้าวหน้า',
        'ยืนขอขยายระยะเวลาโครงการ',
        'เขียนเล่มรายงาน',
        'ส่งเล่มรายงาน เเละ แก้ไขตามข้อเสนอเเนะจากผู้ให้ทุน',
        'ส่งเล่มรายงานฉบับสมบูรณ์',
        'สรุปงบประมาณที่ใช้จริง เเละ ปิดโครงการ',
        'เขียนmanuscript',
        'submit journal',
        'เบิกเงินรางวัลตีพิมพ์/ค่าตอบแทนตีพิมพ์',
        'นำเสนอผลงานวิจัย Oral/Poster presentation',
        'ประชาสัมพันธ์',
        'จัดนิทรรศการแลกเปลี่ยนเรียนรู้ (Knowledge Sharing)',
        'จัดทำสื่อ / Communication Assets',
        'สร้างโปรเเกรม ฐานข้อมูล เเละ โปรเเกรมที่เกี่ยวข้องกับงานวิจัย',
        'IT and Technical support',
        'ที่ปรึกษาโครงการ',
        'เบิกงบประมาณทุน',
        'พัฒนา Scientific Concept note',
    ];
    $piAffiliationList = \App\Models\Project::whereNotNull('pi_affiliation')
        ->whereRaw("TRIM(pi_affiliation) <> ''")
        ->selectRaw('TRIM(pi_affiliation) as pi_affiliation')
        ->distinct()
        ->orderBy('pi_affiliation')
        ->pluck('pi_affiliation');
    $fundingNameList = \App\Models\Project::whereNotNull('funding_name')
        ->whereRaw("TRIM(funding_name) <> ''")
        ->selectRaw('TRIM(funding_name) as funding_name')
        ->distinct()
        ->orderBy('funding_name')
        ->pluck('funding_name');
    $coresearchNameList = \App\Models\CoResearch::whereNotNull('name')
        ->whereRaw("TRIM(name) <> ''")
        ->where(function ($query) use ($internalCoresearchUnit) {
            $query->whereNull('unit_status')
                ->orWhereRaw("TRIM(unit_status) <> ?", [$internalCoresearchUnit]);
        })
        ->selectRaw('TRIM(name) as name')
        ->distinct()
        ->orderBy('name')
        ->pluck('name');
    $coresearchAffiliationList = \App\Models\CoResearch::whereNotNull('affiliation')
        ->whereRaw("TRIM(affiliation) <> ''")
        ->whereRaw("TRIM(affiliation) <> ?", [$internalCoresearchAffiliation])
        ->selectRaw('TRIM(affiliation) as affiliation')
        ->distinct()
        ->orderBy('affiliation')
        ->pluck('affiliation');
    $copiAffiliationList = \App\Models\Copi::whereNotNull('affiliation')
        ->whereRaw("TRIM(affiliation) <> ''")
        ->whereRaw("TRIM(affiliation) <> ?", [$piInternalAffiliation])
        ->selectRaw('TRIM(affiliation) as affiliation')
        ->distinct()
        ->orderBy('affiliation')
        ->pluck('affiliation');
@endphp

{{-- datalist ใช้ร่วมทุกแถว --}}
<datalist id="userNames">
    @foreach ($allUsers as $user)
        @php $fullName = $user->full_name; @endphp
        <option value="{{ $fullName }}"></option>
    @endforeach
</datalist>
<datalist id="outputAuthorNames">
    @foreach (\App\Models\OutputAuthor::query()->whereNotNull('name')->whereRaw("TRIM(name) <> ''")->selectRaw('TRIM(name) as name')->distinct()->orderBy('name')->pluck('name') as $authorName)
        <option value="{{ $authorName }}"></option>
    @endforeach
</datalist>
<datalist id="awardRecipientNames">
    @foreach (\App\Models\OutputAwardRecipient::query()->whereNotNull('name')->whereRaw("TRIM(name) <> ''")->selectRaw('TRIM(name) as name')->distinct()->orderBy('name')->pluck('name') as $awardeeName)
        <option value="{{ $awardeeName }}"></option>
    @endforeach
</datalist>
<datalist id="bookAuthorNames">
    @foreach (\App\Models\OutputBookAuthor::query()->whereNotNull('name')->whereRaw("TRIM(name) <> ''")->selectRaw('TRIM(name) as name')->distinct()->orderBy('name')->pluck('name') as $bookAuthorName)
        <option value="{{ $bookAuthorName }}"></option>
    @endforeach
</datalist>
{{-- ✅ DATAlIST กลาง (ใช้ร่วมกันทุก row) --}}
<datalist id="fteUsers">
    @foreach ($allUsers as $user)
        @php $fullName = $user->full_name; @endphp
        <option value="{{ $fullName }}"></option>
    @endforeach
</datalist>
<datalist id="coresearchUsers">
    @foreach ($coresearchNameList as $coresearchName)
        <option value="{{ $coresearchName }}"></option>
    @endforeach
</datalist>

<script>
    window.AUTH_USER_ID = {{ auth()->id() ?? 'null' }};
    window.enableRowCollapseSummaryOnMobile = true;
    window.PROJECT_CARD_DATE_MODE = 'edit';
    window.PROJECT_MOBILE_WHEEL_DATE = true;
    window.projectUserDirectory = @json($projectUserDirectory);
    document.documentElement.classList.add('page-loading-lock');
</script>

<style>
    [x-cloak] {
        display: none !important;
    }

    .required-mark {
        color: #ef4444;
        font-weight: 600;
    }

    .page-loading-lock,
    .page-loading-lock body {
        overflow: hidden;
    }

    .page-loading-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(2px);
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }

    .page-loading-overlay.is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .page-loading-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.9rem 1.1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.9rem;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        color: #334155;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .page-loading-spinner {
        width: 1.15rem;
        height: 1.15rem;
        border: 2px solid #cbd5e1;
        border-top-color: #2563eb;
        border-radius: 9999px;
        animation: page-loading-spin 0.7s linear infinite;
    }

    @keyframes page-loading-spin {
        to {
            transform: rotate(360deg);
        }
    }

    .fte-mobile-row {
        content-visibility: auto;
        contain-intrinsic-size: 120px;
        position: relative;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .fte-mobile-row.row-collapsed {
        contain-intrinsic-size: 88px;
    }

    #fte-container .fte-mobile-row.row-collapsed {
        padding-bottom: 1rem !important;
    }

    #fte-container .fte-mobile-row.row-collapsed > * {
        display: block !important;
    }

    #fte-container .fte-mobile-row .row-collapse-toggle,
    #fte-container .fte-mobile-row .row-collapse-summary,
    #fte-container .fte-mobile-row .fte-summary-shell {
        display: none !important;
    }

    #fte-container .fte-mobile-row.fte-mobile-collapsed .fte-mobile-body {
        display: none;
    }

    #fte-container .fte-mobile-toggle {
        width: 100%;
    }

    #fte-container .fte-mobile-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    #fte-container .fte-mobile-remove {
        flex-shrink: 0;
        border: 1px solid #fecaca;
        border-radius: 9999px;
        padding: 0.25rem 0.75rem;
        color: #dc2626;
        font-size: 0.85rem;
        font-weight: 600;
        line-height: 1.2;
        background: #fff;
    }

    #fte-container .fte-mobile-summary {
        display: none;
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.35;
    }

    #fte-container .fte-mobile-row.fte-mobile-collapsed .fte-mobile-summary {
        display: block;
    }

    #fte-container .fte-metrics-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    #fte-container .fte-metric-card {
        padding: 0.75rem;
    }

    #fte-container .fte-metric-card span {
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
    }

    #fte-container .fte-metric-card input {
        padding: 0.75rem;
    }

    #fte-container .fte-metric-card--research {
        border-color: #bfdbfe;
        background: #eff6ff;
    }

    #fte-container .fte-metric-card--manager {
        border-color: #a7f3d0;
        background: #ecfdf5;
    }

    #fte-container .fte-field-label {
        display: inline-flex;
        align-items: baseline;
        gap: 0.25rem;
        flex-wrap: wrap;
    }

    .fte-sync-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 9999px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        padding: 0.3rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .fte-sync-status::before {
        content: '';
        width: 0.45rem;
        height: 0.45rem;
        border-radius: 9999px;
        background: currentColor;
        opacity: 0.75;
    }

    .fte-mobile-row.fte-row-remote-new,
    .fte-mobile-row.fte-row-remote-updated {
        animation: fte-row-remote-flash 3.2s ease;
    }

    .fte-mobile-row.fte-row-remote-new {
        border-color: #22c55e;
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.16);
        background: #f0fdf4;
    }

    .fte-mobile-row.fte-row-remote-updated {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.14);
        background: #eff6ff;
    }

    .fte-mobile-row.fte-row-remote-new::after,
    .fte-mobile-row.fte-row-remote-updated::after {
        content: attr(data-remote-label);
        position: absolute;
        top: 0.7rem;
        right: 0.85rem;
        z-index: 3;
        border-radius: 9999px;
        padding: 0.22rem 0.6rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .fte-mobile-row.fte-row-remote-new::after {
        background: #dcfce7;
        color: #166534;
    }

    .fte-mobile-row.fte-row-remote-updated::after {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .fte-mobile-row.fte-row-remote-new .fte-mobile-header,
    .fte-mobile-row.fte-row-remote-new .fte-mobile-body {
        background: rgba(240, 253, 244, 0.82);
        border-radius: 0.85rem;
    }

    .fte-mobile-row.fte-row-remote-updated .fte-mobile-header,
    .fte-mobile-row.fte-row-remote-updated .fte-mobile-body {
        background: rgba(239, 246, 255, 0.88);
        border-radius: 0.85rem;
    }

    .fte-mobile-row.fte-row-deleted {
        position: relative;
        border-color: #fda4af;
        background: #fff1f2;
        box-shadow: 0 0 0 1px rgba(244, 63, 94, 0.1);
    }

    .fte-mobile-row.fte-row-deleted::after {
        content: '';
        position: absolute;
        left: 0.9rem;
        right: 0.9rem;
        top: 50%;
        height: 2px;
        background: rgba(190, 24, 93, 0.45);
        transform: rotate(-1.6deg);
        pointer-events: none;
    }

    .fte-mobile-row.fte-row-deleted .fte-mobile-header,
    .fte-mobile-row.fte-row-deleted .fte-mobile-body {
        background: #fff1f2;
        border-radius: 0.85rem;
    }

    .fte-mobile-row.fte-row-deleted .fte-mobile-summary,
    .fte-mobile-row.fte-row-deleted .fte-mobile-body {
        opacity: 0.72;
    }

    .fte-mobile-row.fte-row-deleted .fte-mobile-summary,
    .fte-mobile-row.fte-row-deleted .fte-mobile-body {
        text-decoration: line-through;
        text-decoration-color: rgba(190, 24, 93, 0.55);
        text-decoration-thickness: 2px;
    }

    .fte-mobile-row .fte-mobile-delete-note {
        display: none;
        margin-top: 0.85rem;
        border: 1px solid #fda4af;
        background: #ffe4e6;
        color: #be123c;
        border-radius: 0.85rem;
        padding: 0.7rem 0.85rem;
        font-size: 0.85rem;
        font-weight: 600;
        line-height: 1.45;
    }

    .fte-mobile-row.fte-row-deleted .fte-mobile-delete-note {
        display: block;
    }

    @keyframes fte-row-remote-flash {
        0% {
            background: #dbeafe;
        }
        35% {
            background: #eff6ff;
        }
        100% {
            background: #ffffff;
        }
    }
</style>


@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        html,
        body {
            max-width: 100%;
            overflow-x: hidden;
        }

        .lux-mobile-form {
            width: calc(100vw - 1.5rem);
            max-width: 100%;
            margin: 0.75rem;
            padding: 1rem;
            border-radius: 16px;
            box-sizing: border-box;
            overflow-x: clip;
        }

        .lux-mobile-form .grid {
            grid-template-columns: 1fr !important;
        }

        .lux-mobile-form input,
        .lux-mobile-form select,
        .lux-mobile-form textarea {
            font-size: 1rem;
        }

        .lux-mobile-form .text-sm {
            font-size: 0.95rem;
        }

        .lux-mobile-form .flatpickr-hidden {
            display: none;
        }

    .lux-mobile-form .flatpickr-mobile {
        display: none !important;
    }

    .lux-mobile-form #fte-tools,
    .lux-mobile-form #fte-table-wrap {
        display: none !important;
    }


        .lux-mobile-form h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 10px 12px;
            margin-top: 16px;
        }

        .lux-mobile-form h3 + div,
        .lux-mobile-form h3 + section,
        .lux-mobile-form h3 + .grid {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px;
            margin-top: 8px;
        }

        .lux-mobile-form label {
            display: block;
        }

        .lux-mobile-form label span {
            display: block;
            margin-bottom: 6px;
        }

        .lux-mobile-form input,
        .lux-mobile-form select,
        .lux-mobile-form textarea {
            width: 100%;
            box-sizing: border-box;
            border-radius: 10px;
            max-width: 100%;
        }

        .lux-mobile-form .btn,
        .lux-mobile-form button {
            border-radius: 10px;
        }

        .lux-mobile-form .lux-mobile-add-row-btn {
            display: block;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- 1. Include Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>


    <!-- ✅ เพิ่ม Plugin สำหรับเลือกปี -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">

    <div id="projectEditLoadingOverlay" class="page-loading-overlay" aria-live="polite" aria-busy="true">
        <div class="page-loading-card">
            <span class="page-loading-spinner" aria-hidden="true"></span>
            <span>กำลังเตรียมหน้าแก้ไขโครงการ...</span>
        </div>
    </div>

    <div class="project-form lux-mobile-form max-w-6xl mx-auto mt-6 bg-white border border-slate-200 shadow-sm rounded-2xl p-6">

        <div class="mb-4">
            {{-- <a href="javascript:history.back()" class="text-blue-600 hover:underline text-base font-semibold">
                <<< ย้อนกลับ </a> --}}
            <a href="{{ route('projects.index') }}" class="text-blue-600 hover:underline">
                ← กลับไปยังรายการโครงการ
            </a>
        </div>
        <h2 class="text-xl font-bold mb-6 text-gray-800">✏️ แก้ไขข้อมูลโครงการวิจัย</h2>
        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm text-slate-600">
            <span class="font-medium text-slate-500">รหัสโครงการ</span>
            <span class="font-semibold text-slate-700">{{ $project->project_code ?: '-' }}</span>
        </div>



        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form id="projectForm" class="pb-10 md:pb-0" action="{{ route('projects.update', $project) }}" method="POST"
            novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="_fte_expected_rows" id="fteExpectedRows" value="0">
            <input type="hidden" name="_form_expected_vars" id="formExpectedVars" value="0">
            <input type="hidden" name="fte_roles_payload" id="fteRolesPayload" value="">

            <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ชื่อโครงการ (ภาษาไทย)</span>
                    <input name="project_name_th" value="{{ old('project_name_th', $project->project_name_th) }}"
                        class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ชื่อโครงการ (English)</span>
                    <input name="project_name_en" value="{{ old('project_name_en', $project->project_name_en) }}"
                        class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">กลุ่มเนื้อหาหลักของงาน(Content/Theme) <span class="text-red-500">*</span></span>
                    <select name="project_type" id="project_type" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกกลุ่มเนื้อหาหลักของงาน')" oninput="this.setCustomValidity('')">
                        <option value="" {{ old('project_type', $project->project_type) ? '' : 'selected' }}>
                            กรุณาเลือกกลุ่มเนื้อหาโครงการ
                        </option>
                        <option value="01 Aging and Frailty"
                            {{ old('project_type', $project->project_type) == '01 Aging and Frailty' ? 'selected' : '' }}>
                            01 Aging and Frailty
                        </option>
                        <option value="02 Health Systems and HTA"
                            {{ old('project_type', $project->project_type) == '02 Health Systems and HTA' ? 'selected' : '' }}>
                            02 Health Systems and HTA
                        </option>
                        <option value="03 Rational Drug Use (RDU)"
                            {{ old('project_type', $project->project_type) == '03 Rational Drug Use (RDU)' ? 'selected' : '' }}>
                            03 Rational Drug Use (RDU)
                        </option>
                        <option value="04 Real-World Evidence (RWE) and Clinical–Technical Cohorts"
                            {{ old('project_type', $project->project_type) == '04 Real-World Evidence (RWE) and Clinical–Technical Cohorts' ? 'selected' : '' }}>
                            04 Real-World Evidence (RWE) and Clinical–Technical Cohorts
                        </option>
                        <option value="05 Global Health"
                            {{ old('project_type', $project->project_type) == '05 Global Health' ? 'selected' : '' }}>
                            05 Global Health
                        </option>
                        <option value="06 Other Areas"
                            {{ old('project_type', $project->project_type) == '06 Other Areas' ? 'selected' : '' }}>
                            06 Other Areas
                        </option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ประเภทของโครงการ/กิจกรรม <span class="text-red-500">*</span></span>
                    <select name="theme" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกประเภทของโครงการ/กิจกรรม')" oninput="this.setCustomValidity('')">
                        <option value="" {{ old('theme', $project->theme) ? '' : 'selected' }}>
                            กรุณาเลือกประเภทของโครงการ/กิจกรรม</option>
                        <option value="T101_Research_วิจัยที่ Health Policy เป็น ผู้วิจัยหลัก"
                            {{ old('theme', $project->theme) == 'T101_Research_วิจัยที่ Health Policy เป็น ผู้วิจัยหลัก' ? 'selected' : '' }}>
                            T101_Research_วิจัยที่ Health Policy เป็น ผู้วิจัยหลัก
                        </option>
                        <option value="T102_Research: โครงการวิจัยที่ Team Health Policy เป็น ผู้ร่วมวิจัย"
                            {{ old('theme', $project->theme) == 'T102_Research: โครงการวิจัยที่ Team Health Policy เป็น ผู้ร่วมวิจัย' ? 'selected' : '' }}>
                            T102_Research: โครงการวิจัยที่ Team Health Policy เป็น ผู้ร่วมวิจัย
                        </option>
                        <option
                            value="T103_Research: โครงการวิจัยที่เป็นไปในลักษณะ Individual Expertise contribution (Skill รายบุคคล) เช่น การเข้าร่วมเขียน paper"
                            {{ old('theme', $project->theme) == 'T103_Research: โครงการวิจัยที่เป็นไปในลักษณะ Individual Expertise contribution (Skill รายบุคคล) เช่น การเข้าร่วมเขียน paper' ? 'selected' : '' }}>
                            T103_Research: โครงการวิจัยที่เป็นไปในลักษณะ Individual Expertise contribution (Skill รายบุคคล)
                        </option>
                        <option value="T301_Model of Care development (Clinic)"
                            {{ old('theme', $project->theme) == 'T301_Model of Care development (Clinic)' ? 'selected' : '' }}>
                            T301_Model of Care development (Clinic)
                        </option>
                        <option value="T401_Cohort Study"
                            {{ old('theme', $project->theme) == 'T401_Cohort Study' ? 'selected' : '' }}>
                            T401_Cohort Study
                        </option>
                        <option value="T501_Curriculum Development (Education)"
                            {{ old('theme', $project->theme) == 'T501_Curriculum Development (Education)' ? 'selected' : '' }}>
                            T501_Curriculum Development (Education)
                        </option>
                        <option value="T601_Consult"
                            {{ old('theme', $project->theme) == 'T601_Consult' ? 'selected' : '' }}>
                            T601_Consult
                        </option>
                        <option value="T701_Process Improvement (Siriraj Health Policy)"
                            {{ old('theme', $project->theme) == 'T701_Process Improvement (Siriraj Health Policy)' ? 'selected' : '' }}>
                            T701_Process Improvement (Siriraj Health Policy)
                        </option>
                        <option value="T801_Conference Session / Knowledge Sharing"
                            {{ old('theme', $project->theme) == 'T801_Conference Session / Knowledge Sharing' ? 'selected' : '' }}>
                            T801_Conference Session / Knowledge Sharing
                        </option>
                        <option value="T901_Capacity Building / Skill Development"
                            {{ old('theme', $project->theme) == 'T901_Capacity Building / Skill Development' ? 'selected' : '' }}>
                            T901_Capacity Building / Skill Development
                        </option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">เอกสารโครงการ (proposal , IRB)</span>
                    <input name="shortcut_link" value="{{ old('shortcut_link', $project->shortcut_link) }}"
                        class="w-full border p-2 rounded">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ลักษณะโครงการ <span class="text-red-500">*</span></span>
                    <select name="project_list" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกลักษณะโครงการ')"
                        oninput="this.setCustomValidity('')">
                        <option value="">-- เลือกประเภทลักษณะ --</option>
                        <option value="โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ"
                            {{ in_array(old('project_list', $project->project_list), ['โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ', 'โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภา'], true) ? 'selected' : '' }}>
                            โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ
                        </option>
                        <option value="โครงการนอกหน่วย แต่ยังอยู่ในคณะฯ"
                            {{ old('project_list', $project->project_list) == 'โครงการนอกหน่วย แต่ยังอยู่ในคณะฯ' ? 'selected' : '' }}>
                            โครงการนอกหน่วย แต่ยังอยู่ในคณะฯ
                        </option>
                        <option value="โครงการวิจัยนอกคณะ"
                            {{ old('project_list', $project->project_list) == 'โครงการวิจัยนอกคณะ' ? 'selected' : '' }}>
                            โครงการวิจัยนอกคณะ
                        </option>
                    </select>
                </label>



                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ขนาดโครงการ (S/M/L) </span>
                    <select name="size" class="w-full border p-2 rounded">
                        <option value="" disabled {{ old('size', $project->size) ? '' : 'selected' }}>
                            กรุณาเลือกขนาดของโปรเจค
                        </option>
                        <option value="S" {{ old('size', $project->size) == 'S' ? 'selected' : '' }}>
                            S (ภายนอก ต่ำกว่า 1 ล้าน / ภายใน)
                        </option>
                        <option value="M" {{ old('size', $project->size) == 'M' ? 'selected' : '' }}>
                            M (ภายนอก 1-3 ล้าน / ภายใน / Cohort Study (Perioperative, SEAPOD))
                        </option>
                        <option value="L" {{ old('size', $project->size) == 'L' ? 'selected' : '' }}>
                            L (ภายนอก 3-5 ล้าน / ภายใน / Cohort Study (GSC, PCU, DM))
                        </option>
                        <option value="XL" {{ old('size', $project->size) == 'XL' ? 'selected' : '' }}>
                            XL (ภายนอก มากกว่า 5 ล้าน)
                        </option>
                        <option value="FTE Hours" {{ old('size', $project->size) == 'FTE Hours' ? 'selected' : '' }}>FTE
                            คิดแบบรายชั่วโมง</option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">รหัสโครงการ R</span>
                    <input name="r_code" value="{{ old('r_code', $project->r_code) }}" class="w-full border p-2 rounded">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ปีโปรเจค <span class="text-red-500">*</span></span>
                    <select name="start_year" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกปีโปรเจค')" oninput="this.setCustomValidity('')">
                        <option value="" disabled
                            {{ old('start_year', $project->start_year ?? '') == '' ? 'selected' : '' }}>เลือกปีโปรเจค
                        </option>
                        @for ($year = now()->year + 2 + 543; $year >= 2557; $year--)
                            <option value="{{ $year }}"
                                {{ old('start_year', $project->start_year ?? '') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </label>



                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA102 - ประเภทแหล่งทุน</span>
                    <select name="funding_type" class="w-full border p-2 rounded">
                        <option value="" disabled
                            {{ old('funding_type', $project->funding_type) ? '' : 'selected' }}>
                            กรุณาเลือกประเภทแหล่งทุน
                        </option>
                        <option value="ภายใน"
                            {{ old('funding_type', $project->funding_type) == 'ภายใน' ? 'selected' : '' }}>
                            ภายใน
                        </option>
                        <option value="ไม่มีแหล่งทุน"
                            {{ old('funding_type', $project->funding_type) == 'ไม่มีแหล่งทุน' ? 'selected' : '' }}>
                            ไม่มีแหล่งทุน
                        </option>
                        <option value="ภายนอก"
                            {{ old('funding_type', $project->funding_type) == 'ภายนอก' ? 'selected' : '' }}>
                            ภายนอก
                        </option>
                    </select>
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA103 - ชื่อแหล่งทุน</span>
                    <input name="funding_source" type="text"
                        value="{{ old('funding_source', $project->funding_source) }}"
                        class="w-full border p-2 rounded"
                        placeholder="ชื่อหน่วยงานหรือองค์กรที่ให้ทุน">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA104 - รัฐบาล / เอกชน / มูลนิธิ</span>
                    <select name="funding_org_type" class="w-full border p-2 rounded">
                        <option value="" disabled
                            {{ old('funding_org_type', $project->funding_org_type) ? '' : 'selected' }}>
                            กรุณาเลือกประเภทองค์กร
                        </option>
                        <option value="รัฐบาล"
                            {{ old('funding_org_type', $project->funding_org_type) == 'รัฐบาล' ? 'selected' : '' }}>
                            รัฐบาล
                        </option>
                        <option value="เอกชน"
                            {{ old('funding_org_type', $project->funding_org_type) == 'เอกชน' ? 'selected' : '' }}>
                            เอกชน
                        </option>
                        <option value="มูลนิธิ"
                            {{ old('funding_org_type', $project->funding_org_type) == 'มูลนิธิ' ? 'selected' : '' }}>
                            มูลนิธิ
                        </option>
                    </select>
                </label>



                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA105 - ชื่อทุน</span>

                    <input name="funding_name" list="funding_list"
                        value="{{ old('funding_name', $project->funding_name) }}" class="w-full border p-2 rounded"
                        placeholder="พิมพ์ชื่อทุนหรือเลือกจากรายการ">
                </label>

                <datalist id="funding_list">
                    @foreach ($fundingNameList as $fundingName)
                        <option value="{{ $fundingName }}"></option>
                    @endforeach
                </datalist>

                <label class="w-full">
                    <label class="w-full">
                        <span class="block text-sm text-gray-700 mb-1">DA106 - งบประมาณ (บาท) <span class="text-red-600">*ใส่ตัวเลขเท่านั้น หากไม่มี ให้เว้นว่างไว้*</span></span>
                        @php
                            $budgetRaw = $project->budget ?? '';
                            $budgetFormatted = is_numeric($budgetRaw) ? number_format($budgetRaw, 2) : $budgetRaw;
                        @endphp

                        <input id="budgetInput" name="budget" type="text" inputmode="decimal"
                            value="{{ old('budget', $budgetFormatted) }}" class="w-full border p-2 rounded"
                            pattern="[\d,\.]*" autocomplete="off">
                        <span id="budgetInputNotice" class="mt-1 hidden text-sm text-red-600">กรุณากรอกตัวเลขเท่านั้น</span>
                    </label>
                </label>
            </div>

            <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">B: ระยะเวลาดำเนินโครงการ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <x-date-be name="team_start_date" label="วันที่โครงการเริ่ม (ทีม)"
                    value="{{ old('team_start_date', $project->team_start_date) }}"
                    placeholder="กรุณาเลือกวันที่เริ่มงานของทีม" />

                <label class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">วันที่สิ้นสุดทำ (ทีม)</span>
                    <div class="relative">
                        <input id="date_team_end_date_edit_mobile" name="team_end_date" type="text"
                            value="{{ old('team_end_date', $project->team_end_date) }}"
                            class="thai-datepicker w-full border rounded-lg px-3 py-2 pr-10 bg-white cursor-pointer focus:ring-2 focus:ring-blue-500"
                            data-force-be-date="1"
                            placeholder="กรุณาเลือกวันที่สิ้นสุดการทำงานของทีม">
                        <button type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
                            data-open-picker
                            data-target="date_team_end_date_edit_mobile">
                            📅
                        </button>
                    </div>
                    @error('team_end_date')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </label>

                <x-date-be name="proposal_date" label="BA001 - วันที่เริ่มเขียน Proposal (เดือน/ปี)"
                    value="{{ old('proposal_date', $project->proposal_date) }}"
                    placeholder="กรุณาเลือกวันที่เริ่มเขียน Proposal" />

                <x-date-be name="contract_date" label="BC101 - วันที่สัญญาอนุมัติ"
                    value="{{ old('contract_date', $project->contract_date) }}"
                    placeholder="กรุณาเลือกวันที่อนุมัติสัญญา" />

                <x-date-be name="start_date" label="BC102 - วันที่โครงการเริ่ม (ตามสัญญา)"
                    value="{{ old('start_date', $project->start_date) }}"
                    placeholder="กรุณาเลือกวันที่เริ่มตามสัญญา" />

                <label class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">BC103 - วันที่สิ้นสุด <strong>(ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ)</strong></span>
                    <div class="relative">
                        <input id="date_end_date_edit_mobile" name="end_date" type="text"
                            value="{{ old('end_date', $project->end_date) }}"
                            class="thai-datepicker w-full border rounded-lg px-3 py-2 pr-10 bg-white cursor-pointer focus:ring-2 focus:ring-blue-500"
                            data-force-be-date="1"
                            placeholder="กรุณาเลือกวันที่สิ้นสุดตามสัญญา">
                        <button type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
                            data-open-picker
                            data-target="date_end_date_edit_mobile">
                            📅
                        </button>
                    </div>
                    @error('end_date')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">Project Status <span class="text-red-500">*</span></span>
                    <select name="planning_status" id="planning_status" class="w-full border p-2 rounded font-semibold"
                        onchange="updateStatusColor(this)" required
                        oninvalid="this.setCustomValidity('กรุณาเลือก Project Status')"
                        oninput="this.setCustomValidity('')">
                        <option value="" disabled
                            {{ old('planning_status', $project->planning_status) ? '' : 'selected' }}
                            style="background-color: #fff; color: #222;">
                            ระบุสถานะของโปรเจค
                        </option>
                        <option value="Project Planing (เริ่มต้น - submit IRB)" data-color="#FFE599"
                            style="background-color:#FFE599; color:#222;"
                            {{ old('planning_status', $project->planning_status) == 'Project Planing (เริ่มต้น - submit IRB)' ? 'selected' : '' }}>
                            Project Planing (เริ่มต้น - submit IRB)
                        </option>
                        <option value="เก็บข้อมูล" data-color="#B6E2D3" style="background-color:#B6E2D3; color:#222;"
                            {{ old('planning_status', $project->planning_status) == 'เก็บข้อมูล' ? 'selected' : '' }}>
                            เก็บข้อมูล
                        </option>
                        <option value="วิเคราะห์ข้อมูล" data-color="#B3D8F7"
                            style="background-color:#B3D8F7; color:#222;"
                            {{ old('planning_status', $project->planning_status) == 'วิเคราะห์ข้อมูล' ? 'selected' : '' }}>
                            วิเคราะห์ข้อมูล
                        </option>
                        <option value="เขียนรายงานและpublication" data-color="#D6C1E6"
                            style="background-color:#D6C1E6; color:#222;"
                            {{ old('planning_status', $project->planning_status) == 'เขียนรายงานและpublication' ? 'selected' : '' }}>
                            เขียนรายงาน และ publication
                        </option>
                        <option value="ปิดโปรเจค" data-color="#C6F1E7" style="background-color:#C6F1E7; color:#222;"
                            {{ old('planning_status', $project->planning_status) == 'ปิดโปรเจค' ? 'selected' : '' }}>
                            ปิดโปรเจค
                        </option>
                        <option value="ยกเลิกโปรเจค" data-color="#F7A9A8" style="background-color:#F7A9A8; color:#222;"
                            {{ old('planning_status', $project->planning_status) == 'ยกเลิกโปรเจค' ? 'selected' : '' }}>
                            ยกเลิกโปรเจค
                        </option>
                    </select>
                    @error('planning_status')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </label>


            </div>




            <hr class="my-6 border-t-2 border-gray-300">
            <h3 class="font-semibold text-lg text-blue-700 mb-2">IRB</h3>
            <div id="irb-code-container" class="space-y-2 mb-4">
                @foreach ($project->irbs ?? [] as $i => $irb)
                    <div id="irb-{{ $i }}" class="irb-row space-y-3 p-4 border rounded-lg bg-gray-50">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-semibold text-slate-700">IRB ที่ <span class="irb-index">{{ $i + 1 }}</span></span>
                            <button type="button" onclick="removeRow('irb-{{ $i }}', 'irb')"
                                class="text-red-600">✕</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <div class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">รหัส IRB <span class="text-red-500">*</span></span>
                                <input name="irbs[{{ $i }}][code]" value="{{ $irb['code'] }}"
                                class="w-full border p-2 rounded" placeholder="เช่น SIHP-IRB-2025-001" required
                                oninvalid="this.setCustomValidity('กรุณาระบุรหัส IRB')"
                                oninput="this.setCustomValidity('')">
                            </div>
                            <div class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">หน่วยงาน <span class="text-red-500">*</span></span>
                                <input name="irbs[{{ $i }}][source]" value="{{ $irb['source'] }}"
                                class="w-full border p-2 rounded" placeholder="ชื่อหน่วยงานที่ออก IRB" required
                                oninvalid="this.setCustomValidity('กรุณาระบุหน่วยงาน')"
                                oninput="this.setCustomValidity('')">
                            </div>
                            <div class="w-full relative">
                                <span class="block text-sm text-gray-700 mb-1">วันหมดอายุ IRB <span class="text-red-500">*</span></span>
                                <input name="irbs[{{ $i }}][exp_date]" value="{{ $irb['exp_date'] ?? '' }}"
                                    type="text" placeholder="เลือกวันหมดอายุ IRB"
                                    class="thai-datepicker w-full border p-2 rounded pl-10" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกวันหมดอายุ IRB')"
                                    oninput="this.setCustomValidity('')">
                                <!-- ไอคอนปฏิทิน -->
                                <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
            <button type="button" onclick="addIRBCode()" class="lux-mobile-add-row-btn mb-6 px-3 py-1 bg-red-600 text-white rounded">+
                เพิ่มรหัส IRB</button>


            <hr class="my-6 border-t-2 border-gray-300">
            <h3 class="font-semibold text-lg text-blue-700 mb-2">C: Roles and Responsibilities</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">CA101 - ประเภท PI (ผู้วิจัยหลัก) <span class="text-red-500">*</span></span>
                    <select name="pi_person_type" data-pi-person-type class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกประเภทผู้วิจัยหลัก')" oninput="this.setCustomValidity('')">
                        <option value="">กรุณาเลือก</option>
                        <option value="internal" {{ old('pi_person_type', $project->pi_user_id ? 'internal' : 'external') === 'internal' ? 'selected' : '' }}>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                        <option value="external" {{ old('pi_person_type', $project->pi_user_id ? 'internal' : 'external') === 'external' ? 'selected' : '' }}>คนนอก</option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">CA103 - สังกัดผู้วิจัยหลัก <span class="text-red-500">*</span></span>

                    <input name="pi_affiliation" value="{{ old('pi_affiliation', $project->pi_affiliation ?? '') }}" data-pi-affiliation
                        class="w-full border p-2 rounded" placeholder="เลือกประเภทผู้วิจัยหลักก่อน" list="pi_affiliation_list" required
                        oninvalid="this.setCustomValidity('กรุณาระบุสังกัดผู้วิจัยหลัก')"
                        oninput="this.setCustomValidity('')">

                    <datalist id="pi_affiliation_list">
                        @foreach ($piAffiliationList as $affiliation)
                            <option value="{{ $affiliation }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="coresearchAffiliationList">
                        @foreach ($coresearchAffiliationList as $affiliation)
                            <option value="{{ $affiliation }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="copiAffiliationList">
                        @foreach ($copiAffiliationList as $affiliation)
                            <option value="{{ $affiliation }}"></option>
                        @endforeach
                    </datalist>
                </label>

                <label class="w-full md:col-span-2" data-pi-manual-wrap>
                    <span class="block text-sm text-gray-700 mb-1">CA102 - ชื่อ PI (ผู้วิจัยหลัก) <span class="text-red-500">*</span></span>
                    <input type="hidden" name="pi_user_id" value="{{ old('pi_user_id', $project->pi_user_id) }}" data-pi-user-id>
                    <input type="hidden" name="pi_name" value="{{ old('pi_name', $project->pi_name) }}" data-pi-name-hidden>
                    <input data-pi-manual-name value="{{ old('pi_name', $project->pi_name) }}"
                        class="w-full border p-2 rounded" placeholder="เลือกประเภทผู้วิจัยหลักก่อน"
                        oninvalid="this.setCustomValidity('กรุณาระบุชื่อผู้วิจัยหลัก')" oninput="this.setCustomValidity('')">
                </label>

                <label class="w-full md:col-span-2 hidden" data-pi-user-wrap>
                    <span class="block text-sm text-gray-700 mb-1">CA102 - เลือก PI จากรายชื่อบุคลากร <span class="text-red-500">*</span></span>
                    <select data-pi-user-select class="w-full border p-2 rounded"
                        oninvalid="this.setCustomValidity('กรุณาเลือกผู้วิจัยหลักจากรายการ')" oninput="this.setCustomValidity('')">
                        <option value="">พิมพ์เพื่อค้นหาชื่อบุคลากร</option>
                        @foreach ($allUsers as $user)
                            <option value="{{ $user->id }}" {{ (string) old('pi_user_id', $project->pi_user_id) === (string) $user->id ? 'selected' : '' }}>
                                {{ trim(($user->prefix ?? '') . ' ' . ($user->name ?? '')) }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>





            <h3 class="text-balance text-blue-700 mb-2">รายชื่อผู้ร่วมวิจัย (COPI)</h3>
            <div id="copi-container" class="space-y-2 mb-4">
                @foreach ($project->copis ?? [] as $i => $copi)
                    <div id="copi-{{ $i }}" class="copi-row space-y-2 p-4 border rounded-lg bg-gray-50">
                        <input type="hidden" name="copis[{{ $i }}][id]" value="{{ $copi['id'] ?? $copi->id ?? '' }}">
                        <input type="hidden" name="copis[{{ $i }}][name]" value="{{ old('copis.' . $i . '.name', $copi['name']) }}" data-copi-name>
                        <input type="hidden" name="copis[{{ $i }}][user_id]" value="{{ old('copis.' . $i . '.user_id', $copi['user_id'] ?? '') }}" data-copi-user-id>

                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-600">ลำดับ</span>
                            <span class="inline-flex px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold copi-index">{{ $i + 1 }}</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm text-gray-700 mb-1">คนใน / คนนอก <span class="text-red-500">*</span></span>
                                <select name="copis[{{ $i }}][person_type]" data-copi-person-type class="w-full border p-2 rounded" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกประเภท COPI')"
                                    oninput="this.setCustomValidity('')">
                                    <option value="">กรุณาเลือก</option>
                                    <option value="internal" {{ old('copis.' . $i . '.person_type', !empty($copi['user_id'] ?? null) ? 'internal' : 'external') === 'internal' ? 'selected' : '' }}>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                    <option value="external" {{ old('copis.' . $i . '.person_type', !empty($copi['user_id'] ?? null) ? 'internal' : 'external') === 'external' ? 'selected' : '' }}>คนนอก</option>
                                </select>
                            </label>

                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm text-gray-700 mb-1">สังกัด COPI <span class="text-red-500">*</span></span>
                                <input name="copis[{{ $i }}][affiliation]" value="{{ old('copis.' . $i . '.affiliation', $copi['affiliation']) }}"
                                    data-copi-affiliation class="w-full border p-2 rounded" placeholder="เลือกประเภท COPI ก่อน" list="copiAffiliationList"
                                    oninvalid="this.setCustomValidity('กรุณาระบุสังกัด COPI')"
                                    oninput="this.setCustomValidity('')">
                            </label>

                            <div class="col-span-12 md:col-span-2 flex justify-end md:pt-6">
                                <button type="button" onclick="removeRow('copi-{{ $i }}', 'copi')"
                                    class="text-red-600 text-xl">✕</button>
                            </div>

                            <label class="col-span-12" data-copi-manual-wrap>
                                <span class="block text-sm text-gray-700 mb-1">ชื่อ COPI <span class="text-red-500">*</span></span>
                                <input value="{{ old('copis.' . $i . '.name', $copi['name']) }}"
                                    list="userNames" data-copi-manual-name class="w-full border p-2 rounded"
                                    placeholder="เลือกประเภท COPI ก่อน"
                                    oninvalid="this.setCustomValidity('กรุณาระบุชื่อ COPI')"
                                    oninput="this.setCustomValidity('')">
                            </label>

                            <label class="col-span-12 hidden" data-copi-user-wrap>
                                <span class="block text-sm text-gray-700 mb-1">เลือก COPI จากรายชื่อบุคลากร <span class="text-red-500">*</span></span>
                                <select data-copi-user-select class="w-full border p-2 rounded"
                                    oninvalid="this.setCustomValidity('กรุณาเลือก COPI จากรายการ')"
                                    oninput="this.setCustomValidity('')">
                                    <option value="">พิมพ์เพื่อค้นหาชื่อบุคลากร</option>
                                    @foreach ($allUsers as $user)
                                        <option value="{{ $user->id }}" {{ (string) old('copis.' . $i . '.user_id', $copi['user_id'] ?? '') === (string) $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" onclick="addCopiRow()" class="lux-mobile-add-row-btn mb-6 px-3 py-1 bg-yellow-600 text-white rounded">+ เพิ่ม
                COPI</button>


            <h3 class="text-balance text-blue-700 mb-2">รายชื่อผู้ร่วมวิจัย (Co-Researcher)</h3>
            <div id="coresearch-container" class="space-y-4 mb-4">
                @foreach ($project->coResearches ?? [] as $i => $co)
                    <div id="coresearch-{{ $i }}" class="coresearch-row space-y-2 p-4 border rounded-lg bg-gray-50">
                        <input type="hidden" name="coresearch[{{ $i }}][id]" value="{{ $co->id }}">
                        <input type="hidden" name="coresearch[{{ $i }}][name]" value="{{ old('coresearch.' . $i . '.name', $co->name) }}" data-coresearch-name>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-600">ลำดับ</span>
                            <span class="inline-flex px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold coresearch-index">{{ $i + 1 }}</span>
                        </div>
                        <!-- สังกัด + สถานะ + ปุ่มลบ -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm text-gray-700 mb-1">คนภายนอก / ภายใน <span class="text-red-500">*</span></span>
                                <select name="coresearch[{{ $i }}][unit_status]"
                                    data-coresearch-unit-status
                                    class="w-full border p-2 rounded" required
                                    oninvalid="this.setCustomValidity('กรุณเลือกสถานะของ Co-Researcher')"
                                    oninput="this.setCustomValidity('')">
                                    <option value="" disabled {{ $co->unit_status == '' ? 'selected' : '' }}>
                                        กรุณาเลือก</option>
                                    <option value="ภายในคณะแพทยศาสตร์ศิริราชพยาบาล"
                                        {{ $co->unit_status == 'ภายในคณะแพทยศาสตร์ศิริราชพยาบาล' ? 'selected' : '' }}>
                                        ภายในคณะแพทยศาสตร์ศิริราชพยาบาล
                                    </option>
                                    <option value="ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล"
                                        {{ $co->unit_status == 'ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล' ? 'selected' : '' }}>
                                        ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล
                                    </option>
                                    <option value="{{ $internalCoresearchUnit }}"
                                        {{ in_array(old('coresearch.' . $i . '.unit_status', $co->unit_status), [$internalCoresearchUnit, 'ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ'], true) ? 'selected' : '' }}>
                                        {{ $internalCoresearchUnit }}
                                    </option>
                                </select>
                            </label>
                            <label class="col-span-12 md:col-span-5" data-coresearch-affiliation-wrap>
                                <span class="block text-sm text-gray-700 mb-1">สังกัด <span class="text-red-500">*</span></span>
                                <input name="coresearch[{{ $i }}][affiliation]"
                                    value="{{ old('coresearch.' . $i . '.affiliation', $co->affiliation) }}" data-coresearch-affiliation class="w-full border p-2 rounded"
                                    placeholder="เลือกคนภายนอก / ภายในก่อน" list="coresearchAffiliationList"
                                    oninvalid="this.setCustomValidity('กรุณาระบุสังกัด')"
                                    oninput="this.setCustomValidity('')">
                            </label>
                            <div class="col-span-12 md:col-span-2 flex justify-end md:pt-6">
                                <button type="button"
                                    onclick="removeRow('coresearch-{{ $i }}', 'coresearch')"
                                    class="text-red-600 text-xl">✕</button>
                            </div>
                            <label class="col-span-12" data-coresearch-manual-wrap>
                                <span class="block text-sm text-gray-700 mb-1">ชื่อ Co-Researcher <span class="text-red-500">*</span></span>
                                <input
                                    value="{{ old('coresearch.' . $i . '.name', $co->name) }}"
                                    list="coresearchUsers" data-coresearch-manual-name class="w-full border p-2 rounded"
                                    placeholder="เลือกคนภายนอก / ภายในก่อน"
                                    oninvalid="this.setCustomValidity('กรุณาระบุชื่อ Co-Researcher')"
                                    oninput="this.setCustomValidity('')">
                            </label>
                            <label class="col-span-12 hidden" data-coresearch-user-wrap>
                                <span class="block text-sm text-gray-700 mb-1">เลือกบุคลากรภายในหน่วยวิจัย <span class="text-red-500">*</span></span>
                                <select name="coresearch[{{ $i }}][user_id]" data-coresearch-user-select class="w-full border p-2 rounded"
                                    oninvalid="this.setCustomValidity('กรุณาเลือกบุคลากรภายในหน่วยวิจัยจากรายการ')"
                                    oninput="this.setCustomValidity('')">
                                    <option value="">พิมพ์เพื่อค้นหาชื่อบุคลากร</option>
                                    @foreach ($allUsers as $user)
                                        <option value="{{ $user->id }}" {{ (string) old('coresearch.' . $i . '.user_id', $co->user_id) === (string) $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- ปุ่มเพิ่ม -->
            <button type="button" onclick="addCoResearchRow()" class="lux-mobile-add-row-btn mb-6 px-3 py-1 bg-purple-600 text-white rounded">
                + เพิ่ม Co-Researcher
            </button>




            <!-- ========== SECTION D : รายชื่อผู้มีส่วนร่วม (FTE) ========== -->
            <hr class="my-6 border-t-2 border-gray-300">

            <div>

                <div class="mb-2">
                    <h3 class="font-semibold text-lg text-blue-700">D: รายชื่อผู้มีส่วนร่วมในการทำงาน</h3>
                </div>
                <div class="mb-3">
                    <span id="fteSyncStatus" class="fte-sync-status">ซิงก์ FTE อัตโนมัติทุก 7 วินาที</span>
                </div>

                <!-- ========== LIST OF FTE ROWS ========== -->
                <div id="fte-container" class="space-y-4 mb-4">

                    @php
                        $fteRows = old('fte_roles');
                        if (!is_array($fteRows)) {
                            $fteRows = $project->fteRoles ?? collect();
                        }
                    @endphp
                    @foreach ($fteRows as $i => $fte)
                        @php
                            $fteId = data_get($fte, 'id');
                            $fteName = data_get($fte, 'name');
                            $selectedRoleGroup = data_get($fte, 'role_group');
                            $selectedJobDetail = data_get($fte, 'job_detail');
                            $fteStartDate = data_get($fte, 'start_date');
                            $fteEndDate = data_get($fte, 'end_date');
                            $fteHours = data_get($fte, 'hours');
                            $selectedTaskDetail = data_get($fte, 'task_detail');
                            $hasKnownTaskDetail = in_array($selectedTaskDetail, $taskDetails, true);
                            $fteValue = data_get($fte, 'fte_value');
                            $fteValueResearch = data_get($fte, 'fte_value_research');
                        @endphp
                        <div id="fte-{{ $i }}" class="fte-mobile-row p-4 border rounded-lg bg-white shadow-sm" data-deleted="0">
                            <input type="hidden" name="fte_roles[{{ $i }}][id]" value="{{ $fteId }}">
                            <div class="fte-mobile-header">
                                <button type="button" class="fte-mobile-toggle text-left">
                                    <span class="text-sm font-semibold text-slate-700">ผู้มีส่วนร่วมคนที่ <span class="fte-index">{{ $i + 1 }}</span></span>
                                    <div class="fte-mobile-summary mt-2"></div>
                                </button>
                                <button type="button" onclick="removeFteRow('fte-{{ $i }}')"
                                    class="fte-mobile-remove">ลบ</button>
                            </div>

                            <div class="fte-mobile-body grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

                                <!-- ===== Left Column ===== -->
                                <div class="md:col-span-9 space-y-4">

                                    <!-- ชื่อ + กลุ่ม -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                        <label class="w-full">
                                            <span class="fte-field-label text-sm font-semibold">
                                                ชื่อ-นามสกุล
                                                <span class="text-red-500">*</span>
                                            </span>
                                            <input list="fteUsers" name="fte_roles[{{ $i }}][name]"
                                                value="{{ $fteName }}" class="w-full border p-2 rounded" placeholder="พิมพ์หรือเลือกชื่อผู้มีส่วนร่วม" required
                                                oninvalid="this.setCustomValidity('กรุณาเลือกหรือพิมพ์ชื่อผู้ใช้จากรายการ')"
                                                oninput="this.setCustomValidity('')">
                                        </label>

                                        <label class="w-full">
                                            <span class="block text-sm font-semibold">กลุ่ม</span>
                                            <select name="fte_roles[{{ $i }}][role_group]"
                                                class="w-full border p-2 rounded" required
                                                oninvalid="this.setCustomValidity('กรุณาเลือกกลุ่ม')"
                                                oninput="this.setCustomValidity('')">
                                                <option value="" disabled>เลือกกลุ่ม</option>

                                                @foreach ($roleGroups as $group)
                                                    <option value="{{ $group }}" @selected($selectedRoleGroup == $group)>
                                                        {{ $group }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </label>

                                    </div>

                                    <!-- ภาระงาน + วันที่ -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                                        <label class="w-full">
                                            <span class="fte-field-label text-sm font-semibold">
                                                ภาระงาน
                                                <span class="text-red-500">*</span>
                                            </span>
                                            <select name="fte_roles[{{ $i }}][job_detail]"
                                                class="w-full border p-2 rounded" required
                                                oninvalid="this.setCustomValidity('กรุณาเลือกภาระงาน')"
                                                oninput="this.setCustomValidity('')">
                                                <option value="" disabled>เลือกภาระงาน</option>

                                                @foreach ($jobDetails as $job)
                                                    <option value="{{ $job }}" @selected($selectedJobDetail == $job)>
                                                        {{ $job }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label class="w-full relative">
                                            <span class="fte-field-label text-sm font-semibold text-gray-700 mb-1">
                                                วันเริ่มต้นภาระงาน
                                                <span class="text-red-500">*</span>
                                            </span>
                                            <input name="fte_roles[{{ $i }}][start_date]" type="text"
                                                value="{{ $fteStartDate }}"
                                                class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="เลือกวันเริ่มต้นภาระงาน" required
                                                oninvalid="this.setCustomValidity('กรุณาเลือกวันเริ่มต้นภาระงาน')"
                                                oninput="this.setCustomValidity('')">
                                            <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                                                fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </label>

                                        <label class="w-full relative">
                                            <span class="fte-field-label text-sm font-semibold text-gray-700 mb-1">
                                                วันสิ้นสุดภาระงาน
                                                <span class="text-red-500">*</span>
                                            </span>
                                            <input name="fte_roles[{{ $i }}][end_date]" type="text"
                                                value="{{ $fteEndDate }}"
                                                class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="เลือกวันสิ้นสุดภาระงาน" required
                                                oninvalid="this.setCustomValidity('กรุณาเลือกวันสิ้นสุดภาระงาน')"
                                                oninput="this.setCustomValidity('')">
                                            <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                                                fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </label>

                                    </div>

                                    <!-- ชั่วโมง -->
                                    <label class="w-full">
                                        <span class="fte-field-label text-sm font-semibold">
                                            ชั่วโมงการทำงาน (Hour)
                                            <span class="text-red-500">*</span>
                                        </span>
                                        <input type="number" step="0.1" min="0"
                                            name="fte_roles[{{ $i }}][hours]" value="{{ $fteHours }}"
                                            class="fte-hours w-full border p-2 rounded text-right" placeholder="0.0" required
                                            oninvalid="this.setCustomValidity('กรุณาระบุชั่วโมงการทำงาน')"
                                            oninput="this.setCustomValidity('')">
                                    </label>

                                    <!-- รายละเอียดภารกิจ -->
                                    <label class="w-full">
                                        <span class="fte-field-label text-sm font-semibold">
                                            รายละเอียดภารกิจ
                                            <span class="text-red-500">*</span>
                                        </span>
                                        <select name="fte_roles[{{ $i }}][task_detail]"
                                            class="w-full border p-2 rounded" required
                                            oninvalid="this.setCustomValidity('กรุณาเลือกรายละเอียดภารกิจ')"
                                            oninput="this.setCustomValidity('')">
                                            <option value="" disabled @selected(blank($selectedTaskDetail))>เลือกภารกิจ</option>
                                            @if (filled($selectedTaskDetail) && ! $hasKnownTaskDetail)
                                                <option value="{{ $selectedTaskDetail }}" selected>{{ $selectedTaskDetail }}</option>
                                            @endif
                                            @foreach ($taskDetails as $taskDetail)
                                                <option value="{{ $taskDetail }}" @selected($selectedTaskDetail === $taskDetail)>
                                                    {{ $taskDetail }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>

                                </div>

                                <!-- ===== Right Column ===== -->
                                <div class="md:col-span-3">
                                    <div class="fte-metrics-grid">

                                    <!-- FTE (ผู้วิจัย) -->
                                    <label class="fte-metric-card fte-metric-card--research block border rounded-xl">
                                        <span class="block text-sm font-bold text-blue-800 mb-2">FTE (ผู้วิจัย)</span>
                                        <input type="number" step="0.00001" min="0" max="1"
                                            name="fte_roles[{{ $i }}][fte_value]"
                                            value="{{ $fteValue }}"
                                            class="fte-value w-full border p-3 rounded bg-gray-100 text-right" readonly>
                                    </label>

                                    <!-- FTE (ผู้บริหาร) -->
                                    <label class="fte-metric-card fte-metric-card--manager block border rounded-xl">
                                        <span class="block text-sm font-bold text-emerald-800 mb-2">FTE (ผู้บริหาร)</span>
                                        <input type="number" step="0.01" min="0" max="1"
                                            name="fte_roles[{{ $i }}][fte_value_research]"
                                            value="{{ $fteValueResearch }}"
                                            class="w-full border p-3 rounded text-right" placeholder="0.00">
                                    </label>
                                    </div>

                                </div>
                            </div>
                            <div class="fte-mobile-delete-note">
                                รายการนี้ถูกทำเครื่องหมายเพื่อลบแล้ว ระบบจะไม่บันทึกการแก้ไขของแถวนี้เมื่อกดบันทึก
                            </div>
                        </div>
                    @endforeach

                </div>
                <div id="fte-deleted-ids"></div>

                <button type="button" onclick="event.stopPropagation(); addFTERow()" data-fte-add-row class="lux-mobile-add-row-btn mb-6 px-3 py-1 bg-blue-600 text-white rounded">
                    + เพิ่มผู้มีส่วนร่วม
                </button>

            </div>


            {{-- E: ผลงานวิจัย (Output) ========================= --}}

            <h3 class="font-semibold text-lg text-blue-700 mb-2">E: ผลงานวิจัย (Output)</h3>

            <div id="output-container" class="space-y-4 mb-4">
                @php
                    $outs = $project->outputs ?? collect();
                @endphp

                @forelse($outs as $i => $out)
                    <div id="output-{{ $i }}" class="output-row space-y-2 border p-4 rounded-lg bg-gray-50"
                        data-index="{{ $i }}">
                        {{-- hidden id ของ output เดิม --}}
                        <input type="hidden" name="outputs[{{ $i }}][id]" value="{{ $out->id }}">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-semibold text-blue-700">ผลงานที่ <span class="output-index">{{ $i + 1 }}</span></span>
                            <button type="button" onclick="removeRow('output-{{ $i }}','output')"
                                class="text-red-600">✕</button>
                        </div>

                        {{-- ชื่อผลงาน --}}
                        <label class="block w-full">
                            <span class="block text-sm text-gray-700 mb-1">
                                ชื่อผลงาน <span class="text-red-500">*</span>
                            </span>
                            <input name="outputs[{{ $i }}][title]" type="text"
                                class="w-full border p-2 rounded" value="{{ old("outputs.$i.title", $out->title) }}"
                                required oninvalid="this.setCustomValidity('กรุณากรอกชื่อผลงาน')"
                                oninput="this.setCustomValidity('')" placeholder="ระบุชื่อผลงาน">
                        </label>

                        {{-- บรรทัด: ประเภท, หมายเหตุ, วันที่ --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">ประเภทของผลงาน <span class="text-red-500">*</span></span>
                                <select name="outputs[{{ $i }}][type]"
                                    class="w-full border p-2 rounded output-type" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกประเภทของผลงาน')"
                                    oninput="this.setCustomValidity('')" onchange="handleTypeChange(this)">
                                    <option value="" {{ !$out->type ? 'selected' : '' }}>
                                        เลือกประเภทของผลงาน</option>

                                    @php $t = $out->type ?? ''; @endphp
                                    <option value="EA101 Research Paper or Journal Article"
                                        {{ str_starts_with($t, 'EA101') ? 'selected' : '' }}>
                                        EA101 Research Paper or Journal Article
                                    </option>
                                    <option value="EA102 Reports and White papers"
                                        {{ $t === 'EA102 Reports and White papers' ? 'selected' : '' }}>
                                        EA102 Reports and White papers
                                    </option>
                                    <option value="EA103 Presentations in Academic Conferences"
                                        {{ str_starts_with($t, 'EA103') ? 'selected' : '' }}>
                                        EA103 Presentations in Academic Conferences
                                    </option>
                                    <option value="EA104 Data Sets and Databases"
                                        {{ $t === 'EA104 Data Sets and Databases' ? 'selected' : '' }}>
                                        EA104 Data Sets and Databases
                                    </option>
                                    <option value="EA105 Inventions or Innovations (Application /Website)"
                                        {{ $t === 'EA105 Inventions or Innovations (Application /Website)' ? 'selected' : '' }}>
                                        EA105 Inventions or Innovations (Application /Website)
                                    </option>
                                    <option value="EA106 Policy Recommendations"
                                        {{ $t === 'EA106 Policy Recommendations' ? 'selected' : '' }}>
                                        EA106 Policy Recommendations
                                    </option>
                                    <option value="EA107 Educational Materials"
                                        {{ $t === 'EA107 Educational Materials' ? 'selected' : '' }}>
                                        EA107 Educational Materials
                                    </option>
                                    <option value="EA108 Communication Materials (Creations)"
                                        {{ $t === 'EA108 Communication Materials (Creations)' ? 'selected' : '' }}>
                                        EA108 Communication Materials (Creations)
                                    </option>
                                    <option value="EA109 Change in Practices ,Procedures, Policy"
                                        {{ $t === 'EA109 Change in Practices ,Procedures, Policy' ? 'selected' : '' }}>
                                        EA109 Change in Practices ,Procedures, Policy
                                    </option>
                                    <option value="EA110 Number of participants (persons)"
                                        {{ $t === 'EA110 Number of participants (persons)' ? 'selected' : '' }}>
                                        EA110 Number of participants (persons)
                                    </option>
                                    <option value="EA111 Satisfaction Rating (1-5)"
                                        {{ $t === 'EA111 Satisfaction Rating (1-5)' ? 'selected' : '' }}>
                                        EA111 Satisfaction Rating (1-5)
                                    </option>
                                    <option value="EA112 Present to Committee Meeting / Policy Maker / Board"
                                        {{ $t === 'EA112 Present to Committee Meeting / Policy Maker / Board' ? 'selected' : '' }}>
                                        EA112 Present to Committee Meeting / Policy Maker / Board
                                    </option>
                                    <option value="EA113 คำสั่งแต่งตั้ง Committees เน้นที่ SIHP เป็นกรรมการและหรือเลขา"
                                        {{ $t === 'EA113 คำสั่งแต่งตั้ง Committees เน้นที่ SIHP เป็นกรรมการและหรือเลขา' ? 'selected' : '' }}>
                                        EA113 คำสั่งแต่งตั้ง Committees เน้นที่ SIHP เป็นกรรมการและหรือเลขา
                                    </option>
                                    <option value="EA114 Events / Forum"
                                        {{ $t === 'EA114 Events / Forum' ? 'selected' : '' }}>
                                        EA114 Events / Forum
                                    </option>
                                    <option value="EA115 Capacity Building / Knowledge Sharing"
                                        {{ $t === 'EA115 Capacity Building / Knowledge Sharing' ? 'selected' : '' }}>
                                        EA115 Capacity Building / Knowledge Sharing
                                    </option>
                                    <option value="EA116 Research Project"
                                        {{ $t === 'EA116 Research Project' ? 'selected' : '' }}>
                                        EA116 Research Project
                                    </option>
                                    <option value="EA117 MOU" {{ $t === 'EA117 MOU' ? 'selected' : '' }}>
                                        EA117 MOU
                                    </option>
                                    <option value="EA118 Award and Recognition"
                                        {{ $t === 'EA118 Award and Recognition' ? 'selected' : '' }}>
                                        EA118 Award and Recognition
                                    </option>
                                    <option value="EA119 Textbook / Book chapter"
                                        {{ $t === 'EA119 Textbook / Book chapter' ? 'selected' : '' }}>
                                        EA119 Textbook / Book chapter
                                    </option>
                                    <option value="EA120 Manual / Instruction / Guideline"
                                        {{ $t === 'EA120 Manual / Instruction / Guideline' ? 'selected' : '' }}>
                                        EA120 Manual / Instruction / Guideline
                                    </option>
                                </select>
                            </label>

                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">รายละเอียด / หมายเหตุ / LINK</span>
                                <input name="outputs[{{ $i }}][project_output]" type="text"
                                    class="w-full border p-2 rounded"
                                    value="{{ old("outputs.$i.project_output", $out->project_output) }}"
                                    placeholder="เช่น ตีพิมพ์แล้ว / ลิงก์ / รายละเอียดเพิ่มเติม">
                            </label>

                            <label class="w-full relative">
                                <span class="block text-sm text-gray-700 mb-1">วันที่ publish</span>
                                <input name="outputs[{{ $i }}][year]" type="text"
                                    class="thai-datepicker w-full border p-2 rounded pl-10"
                                    value="{{ old("outputs.$i.year", $out->year) }}" placeholder="เลือกวันที่ publish">
                                <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </label>
                        </div>

                        {{-- จุดติดตั้งส่วนพิเศษ (EA101/EA103) --}}
                        <div class="special-mount">
                            @php $t = $out->type ?? ''; @endphp

                            {{-- EA101 --}}
                            @if (str_starts_with($t, 'EA101'))
                                <div class="ea101-section space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <label>
                                            <span class="block text-sm text-gray-700 mb-1">Journal</span>
                                            <input name="outputs[{{ $i }}][journal]" type="text"
                                                class="w-full border p-2 rounded"
                                                value="{{ old("outputs.$i.journal", $out->journal) }}"
                                                placeholder="เช่น IEEE Access">
                                        </label>
                                        <label>
                                            <span class="block text-sm text-gray-700 mb-1">Quartile (Q)</span>
                                            @php $q = old("outputs.$i.quartile", $out->quartile); @endphp
                                            <select name="outputs[{{ $i }}][quartile]"
                                                class="w-full border p-2 rounded">
                                                <option value="" {{ !$q ? 'selected' : '' }}>เลือก
                                                    Quartile</option>
                                                <option value="Q1" {{ $q === 'Q1' ? 'selected' : '' }}>Q1
                                                </option>
                                                <option value="Q2" {{ $q === 'Q2' ? 'selected' : '' }}>Q2
                                                </option>
                                                <option value="Q3" {{ $q === 'Q3' ? 'selected' : '' }}>Q3
                                                </option>
                                                <option value="Q4" {{ $q === 'Q4' ? 'selected' : '' }}>Q4
                                                </option>
                                            </select>
                                        </label>
                                        <label>
                                            <span class="block text-sm text-gray-700 mb-1">Impact Factor (IF)</span>
                                            <input name="outputs[{{ $i }}][impact_factor]" type="number"
                                                step="0.001" class="w-full border p-2 rounded"
                                                value="{{ old("outputs.$i.impact_factor", $out->impact_factor) }}"
                                                placeholder="เช่น 3.215">
                                        </label>
                                    </div>

                                    {{-- Authors --}}
                                    @php $authors = $out->authors ?? collect(); @endphp
                                    <div class="space-y-3">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="font-semibold text-slate-800">รายชื่อผู้เขียน (Authors)</span>
                                        </div>

                                        <div class="authors-container space-y-3" id="authors-{{ $i }}">
                                            @forelse($authors as $j => $auth)
                                                <div class="author-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3">
                                                    <input type="hidden"
                                                        name="outputs[{{ $i }}][authors][{{ $j }}][id]"
                                                        value="{{ $auth->id }}">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span class="author-index inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-blue-100 px-2 text-xs font-bold text-blue-700">{{ $j + 1 }}</span>
                                                            <span class="text-sm font-semibold text-slate-700">ผู้เขียน</span>
                                                        </div>
                                                        <button type="button"
                                                            class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600"
                                                            onclick="removeAuthorRow(this)">ลบ</button>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <label class="w-full">
                                                            <span class="block text-sm text-gray-700 mb-1">บทบาทผู้เขียน
                                                                (Role) <span class="required-mark">*</span>
                                                            </span>
                                                            <select
                                                                name="outputs[{{ $i }}][authors][{{ $j }}][role]"
                                                                class="w-full border p-2 rounded" required
                                                                oninvalid="this.setCustomValidity('กรุณาเลือกบทบาทผู้เขียน')"
                                                                oninput="this.setCustomValidity('')">
                                                                <option value="" 
                                                                    {{ !$auth->role ? 'selected' : '' }}>เลือกบทบาท
                                                                </option>
                                                                <option value="first"
                                                                    {{ $auth->role === 'first' ? 'selected' : '' }}>
                                                                    First-author</option>
                                                                <option value="co"
                                                                    {{ $auth->role === 'co' ? 'selected' : '' }}>Co-author
                                                                </option>
                                                                <option
                                                                    value="corresponding"{{ $auth->role === 'corresponding' ? 'selected' : '' }}>
                                                                    Corresponding author</option>
                                                            </select>
                                                        </label>

                                                        @php $authorPersonType = old("outputs.$i.authors.$j.person_type", $auth->user_id ? 'internal' : 'external'); @endphp
                                                        <label class="w-full">
                                                            <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก
                                                                <span class="required-mark">*</span></span>
                                                            <select
                                                                name="outputs[{{ $i }}][authors][{{ $j }}][person_type]"
                                                                data-output-author-person-type class="w-full border p-2 rounded" required
                                                                oninvalid="this.setCustomValidity('กรุณาเลือกประเภทผู้เขียน')"
                                                                oninput="this.setCustomValidity('')">
                                                                <option value="" {{ !$authorPersonType ? 'selected' : '' }}>กรุณาเลือก</option>
                                                                <option value="internal" {{ $authorPersonType === 'internal' ? 'selected' : '' }}>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                                <option value="external" {{ $authorPersonType === 'external' ? 'selected' : '' }}>คนนอก</option>
                                                            </select>
                                                        </label>
                                                    </div>
                                                    <div class="space-y-2">
                                                        <span class="block text-sm text-gray-700 mb-1">ชื่อ - นามสกุล
                                                            ผู้เขียน <span class="required-mark">*</span></span>
                                                        <input type="hidden" name="outputs[{{ $i }}][authors][{{ $j }}][user_id]" value="{{ old("outputs.$i.authors.$j.user_id", $auth->user_id) }}" data-user-id-hidden>
                                                        <input type="hidden" name="outputs[{{ $i }}][authors][{{ $j }}][name]" value="{{ old("outputs.$i.authors.$j.name", $auth->name) }}" data-output-author-name-hidden>
                                                        <div data-output-author-manual-wrap>
                                                            <input
                                                                type="text" list="outputAuthorNames" data-output-author-manual-name class="w-full border p-2 rounded"
                                                                value="{{ old("outputs.$i.authors.$j.name", $auth->name) }}"
                                                                placeholder="ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)"
                                                                oninvalid="this.setCustomValidity('กรุณากรอกชื่อ-นามสกุล ผู้เขียน')"
                                                                oninput="this.setCustomValidity('')">
                                                        </div>
                                                        <div data-output-author-user-wrap class="hidden" style="display:none;">
                                                            <select data-output-author-user-select class="w-full border p-2 rounded">
                                                                <option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>
                                                                @foreach ($allUsers as $user)
                                                                    <option value="{{ $user->id }}" {{ (string) old("outputs.$i.authors.$j.user_id", $auth->user_id) === (string) $user->id ? 'selected' : '' }}>
                                                                        {{ $user->research_display_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="author-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span class="author-index inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-blue-100 px-2 text-xs font-bold text-blue-700">1</span>
                                                            <span class="text-sm font-semibold text-slate-700">ผู้เขียน</span>
                                                        </div>
                                                        <button type="button"
                                                            class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600"
                                                            onclick="removeAuthorRow(this)">ลบ</button>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <label class="w-full">
                                                            <span class="block text-sm text-gray-700 mb-1">บทบาทผู้เขียน
                                                                (Role) <span class="required-mark">*</span></span>
                                                            <select name="outputs[{{ $i }}][authors][0][role]"
                                                                class="w-full border p-2 rounded" required
                                                                oninvalid="this.setCustomValidity('กรุณาเลือกบทบาทผู้เขียน')"
                                                                oninput="this.setCustomValidity('')">
                                                                <option value="">เลือกบทบาท
                                                                </option>
                                                                <option value="first">First-author</option>
                                                                <option value="co">Co-author</option>
                                                                <option value="corresponding">Corresponding author</option>
                                                            </select>
                                                        </label>
                                                        <label class="w-full">
                                                            <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก
                                                                <span class="required-mark">*</span></span>
                                                            <select name="outputs[{{ $i }}][authors][0][person_type]"
                                                                data-output-author-person-type class="w-full border p-2 rounded" required
                                                                oninvalid="this.setCustomValidity('กรุณาเลือกประเภทผู้เขียน')"
                                                                oninput="this.setCustomValidity('')">
                                                                <option value="">กรุณาเลือก</option>
                                                                <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                                <option value="external">คนนอก</option>
                                                            </select>
                                                        </label>
                                                    </div>
                                                    <div class="space-y-2">
                                                        <span class="block text-sm text-gray-700 mb-1">ชื่อ - นามสกุล
                                                            ผู้เขียน <span class="required-mark">*</span></span>
                                                        <input type="hidden" name="outputs[{{ $i }}][authors][0][user_id]" data-user-id-hidden>
                                                        <input type="hidden" name="outputs[{{ $i }}][authors][0][name]" data-output-author-name-hidden>
                                                        <div data-output-author-manual-wrap>
                                                            <input type="text" list="outputAuthorNames" data-output-author-manual-name class="w-full border p-2 rounded"
                                                                placeholder="เลือกประเภทผู้เขียนก่อน"
                                                                oninvalid="this.setCustomValidity('กรุณากรอกชื่อ-นามสกุล ผู้เขียน')"
                                                                oninput="this.setCustomValidity('')">
                                                        </div>
                                                        <div data-output-author-user-wrap class="hidden" style="display:none;">
                                                            <select data-output-author-user-select class="w-full border p-2 rounded">
                                                                <option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>
                                                                @foreach ($allUsers as $user)
                                                                    <option value="{{ $user->id }}">{{ $user->research_display_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                        <div class="flex justify-end">
                                            <button type="button"
                                                class="lux-mobile-add-row-btn add-author-btn whitespace-nowrap px-3 py-2 rounded-lg bg-blue-600 text-sm font-semibold text-white"
                                                onclick="addAuthorRow(this)">+ เพิ่มผู้เขียน</button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (str_starts_with($t, 'EA118'))
                                @php $awardees = $out->awardRecipients ?? collect(); @endphp
                                <div class="ea118-section space-y-3 rounded-xl border border-slate-200 bg-violet-50/60 p-4">
                                    <div class="space-y-3">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="font-semibold text-violet-800">ผู้ได้รับรางวัล (Award Recipients)</span>
                                        </div>

                                        <div class="award-recipients-container space-y-3" id="award-recipients-{{ $i }}">
                                            @forelse($awardees as $j => $awardee)
                                                @php
                                                    $awardeePersonType = old("outputs.$i.award_recipients.$j.person_type");
                                                    if ($awardeePersonType === null) {
                                                        $awardeePersonType = $awardee->user_id
                                                            ? 'internal'
                                                            : (trim((string) ($awardee->name ?? '')) === 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ' ? 'unit' : 'external');
                                                    }
                                                @endphp
                                                <div class="award-recipient-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3">
                                                    <input type="hidden" name="outputs[{{ $i }}][award_recipients][{{ $j }}][id]" value="{{ $awardee->id }}">
                                                    <input type="hidden" name="outputs[{{ $i }}][award_recipients][{{ $j }}][user_id]" value="{{ old("outputs.$i.award_recipients.$j.user_id", $awardee->user_id) }}" data-user-id-hidden>
                                                    <div class="space-y-3">
                                                        <label class="w-full">
                                                            <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก / หน่วยงาน</span>
                                                            <select name="outputs[{{ $i }}][award_recipients][{{ $j }}][person_type]" data-award-recipient-person-type class="w-full border p-2 rounded" required>
                                                                <option value="">กรุณาเลือก</option>
                                                                <option value="unit" {{ $awardeePersonType === 'unit' ? 'selected' : '' }}>หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                                <option value="internal" {{ $awardeePersonType === 'internal' ? 'selected' : '' }}>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                                <option value="external" {{ $awardeePersonType === 'external' ? 'selected' : '' }}>คนนอก</option>
                                                            </select>
                                                        </label>
                                                        <label class="w-full">
                                                            <span class="block text-sm text-gray-700 mb-1">ชื่อผู้ได้รับรางวัล</span>
                                                            <input type="hidden" name="outputs[{{ $i }}][award_recipients][{{ $j }}][name]" value="{{ old("outputs.$i.award_recipients.$j.name", $awardee->name) }}" data-award-recipient-name-hidden>
                                                            <div data-award-recipient-manual-wrap>
                                                                <input type="text" list="awardRecipientNames" data-award-recipient-manual-name class="w-full border p-2 rounded"
                                                                    value="{{ old("outputs.$i.award_recipients.$j.name", $awardee->name) }}"
                                                                    placeholder="เลือกประเภทผู้ได้รับรางวัลก่อน">
                                                            </div>
                                                            <div data-award-recipient-user-wrap class="hidden" style="display:none;">
                                                                <select data-award-recipient-user-select class="w-full border p-2 rounded">
                                                                    <option value="">พิมพ์เพื่อค้นหาชื่อผู้ได้รับรางวัล</option>
                                                                    @foreach ($allUsers as $user)
                                                                        <option value="{{ $user->id }}" {{ (string) old("outputs.$i.award_recipients.$j.user_id", $awardee->user_id) === (string) $user->id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </label>
                                                        <div class="flex items-end">
                                                            <button type="button" class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600" onclick="removeAwardRecipientRow(this)">ลบ</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="award-recipient-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3">
                                                    <input type="hidden" name="outputs[{{ $i }}][award_recipients][0][id]" value="">
                                                    <input type="hidden" name="outputs[{{ $i }}][award_recipients][0][user_id]" data-user-id-hidden>
                                                    <div class="space-y-3">
                                                        <label class="w-full">
                                                            <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก / หน่วยงาน</span>
                                                            <select name="outputs[{{ $i }}][award_recipients][0][person_type]" data-award-recipient-person-type class="w-full border p-2 rounded" required>
                                                                <option value="">กรุณาเลือก</option>
                                                                <option value="unit">หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                                <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                                <option value="external">คนนอก</option>
                                                            </select>
                                                        </label>
                                                        <label class="w-full">
                                                            <span class="block text-sm text-gray-700 mb-1">ชื่อผู้ได้รับรางวัล</span>
                                                            <input type="hidden" name="outputs[{{ $i }}][award_recipients][0][name]" data-award-recipient-name-hidden>
                                                            <div data-award-recipient-manual-wrap>
                                                                <input type="text" list="awardRecipientNames" data-award-recipient-manual-name class="w-full border p-2 rounded"
                                                                    placeholder="เลือกประเภทผู้ได้รับรางวัลก่อน">
                                                            </div>
                                                            <div data-award-recipient-user-wrap class="hidden" style="display:none;">
                                                                <select data-award-recipient-user-select class="w-full border p-2 rounded">
                                                                    <option value="">พิมพ์เพื่อค้นหาชื่อผู้ได้รับรางวัล</option>
                                                                    @foreach ($allUsers as $user)
                                                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </label>
                                                        <div class="flex items-end">
                                                            <button type="button" class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600" onclick="removeAwardRecipientRow(this)">ลบ</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                        <div class="flex justify-center">
                                            <button type="button" class="lux-mobile-add-row-btn add-award-recipient-btn whitespace-nowrap px-3 py-2 rounded-lg bg-violet-600 text-sm font-semibold text-white" onclick="addAwardRecipientRow(this)">+ เพิ่มผู้ได้รับรางวัล</button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- EA103 --}}
                            @if (str_starts_with($t, 'EA103'))
                                <div class="ea103-section space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    @php
                                        $confs = $out->conferences ?? collect();
                                        $primaryConf = $confs->first();
                                        $presenters = collect($primaryConf?->presenters ?? [])->values();
                                    @endphp
                                    <input type="hidden" name="outputs[{{ $i }}][conference_id]" value="{{ old("outputs.$i.conference_id", $primaryConf?->id) }}">
                                    <div class="grid grid-cols-1 gap-3">
                                        <label>
                                            <span class="block text-sm text-gray-700 mb-1">ชื่อเวที/Conference</span>
                                            <input name="outputs[{{ $i }}][conference_name]" class="w-full border p-2 rounded"
                                                value="{{ old("outputs.$i.conference_name", $primaryConf?->name) }}"
                                                placeholder="เช่น PMAC 2025">
                                        </label>
                                        <label>
                                            <span class="block text-sm text-gray-700 mb-1">ประเภทนำเสนอ</span>
                                            @php $pt = old("outputs.$i.conference_presentation_type", $primaryConf?->presentation_type); @endphp
                                            <select name="outputs[{{ $i }}][conference_presentation_type]" class="w-full border p-2 rounded">
                                                <option value="">เลือก</option>
                                                <option value="Oral Presentation" {{ $pt === 'Oral Presentation' ? 'selected' : '' }}>Oral Presentation</option>
                                                <option value="Poster Presentation" {{ $pt === 'Poster Presentation' ? 'selected' : '' }}>Poster Presentation</option>
                                                <option value="Abstract Presentation" {{ $pt === 'Abstract Presentation' ? 'selected' : '' }}>Abstract Presentation</option>
                                                <option value="Panel Discussion" {{ $pt === 'Panel Discussion' ? 'selected' : '' }}>Panel Discussion</option>
                                                <option value="Keynote Speech" {{ $pt === 'Keynote Speech' ? 'selected' : '' }}>Keynote Speech</option>
                                                <option value="World café" {{ $pt === 'World café' ? 'selected' : '' }}>World café</option>
                                            </select>
                                        </label>
                                        <label>
                                            <span class="block text-sm text-gray-700 mb-1">สถานที่</span>
                                            <input name="outputs[{{ $i }}][conference_location]" class="w-full border p-2 rounded"
                                                value="{{ old("outputs.$i.conference_location", $primaryConf?->location) }}"
                                                placeholder="เมือง/ประเทศ">
                                        </label>
                                    </div>

                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-slate-800">รายชื่อผู้นำเสนอ</span>
                                        </div>
                                        <div class="conference-presenters-container space-y-3" id="confs-{{ $i }}" data-conf-presenter-index="{{ max($presenters->count() - 1, 0) }}">
                                            @forelse($presenters as $k => $c)
                                                @php
                                                    $personType = old("outputs.$i.conference_presenters.$k.person_type");
                                                    if ($personType === null) {
                                                        $personType = $c->user_id ? 'internal' : 'external';
                                                    }
                                                @endphp
                                                <div class="conference-presenter-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3">
                                                    <input type="hidden" name="outputs[{{ $i }}][conference_presenters][{{ $k }}][id]" value="{{ $c->id }}">
                                                    <input type="hidden" name="outputs[{{ $i }}][conference_presenters][{{ $k }}][user_id]" value="{{ old("outputs.$i.conference_presenters.$k.user_id", $c->user_id) }}" data-conference-presenter-user-id>
                                                    <input type="hidden" name="outputs[{{ $i }}][conference_presenters][{{ $k }}][note]" value="{{ old("outputs.$i.conference_presenters.$k.note", $c->name) }}" data-conference-presenter-note-hidden>
                                                    <label>
                                                        <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก</span>
                                                        <select name="outputs[{{ $i }}][conference_presenters][{{ $k }}][person_type]" data-conference-presenter-person-type class="w-full border p-2 rounded" required>
                                                            <option value="">กรุณาเลือก</option>
                                                            <option value="internal" {{ $personType === 'internal' ? 'selected' : '' }}>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                            <option value="external" {{ $personType === 'external' ? 'selected' : '' }}>คนนอก</option>
                                                        </select>
                                                    </label>
                                                    <label data-conference-presenter-manual-wrap>
                                                        <span class="block text-sm text-gray-700 mb-1">ชื่อผู้นำเสนอ</span>
                                                        <input type="text" data-conference-presenter-manual-name class="w-full border p-2 rounded"
                                                            value="{{ old("outputs.$i.conference_presenters.$k.note", $c->name) }}"
                                                            placeholder="ยศ ชื่อ นามสกุล">
                                                    </label>
                                                    <label class="hidden" data-conference-presenter-user-wrap>
                                                        <span class="block text-sm text-gray-700 mb-1">เลือกผู้นำเสนอจากรายชื่อบุคลากร</span>
                                                        <select data-conference-presenter-user-select class="w-full border p-2 rounded">
                                                            <option value="">พิมพ์เพื่อค้นหาชื่อผู้นำเสนอ</option>
                                                            @foreach ($allUsers as $user)
                                                                <option value="{{ $user->id }}" {{ (string) old("outputs.$i.conference_presenters.$k.user_id", $c->user_id) === (string) $user->id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <button type="button" class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600" onclick="removeConferencePresenterRow(this)">ลบ</button>
                                                </div>
                                            @empty
                                                <div class="conference-presenter-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3">
                                                    <input type="hidden" name="outputs[{{ $i }}][conference_presenters][0][id]" value="">
                                                    <input type="hidden" name="outputs[{{ $i }}][conference_presenters][0][user_id]" value="" data-conference-presenter-user-id>
                                                    <input type="hidden" name="outputs[{{ $i }}][conference_presenters][0][note]" value="" data-conference-presenter-note-hidden>
                                                    <label>
                                                        <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก</span>
                                                        <select name="outputs[{{ $i }}][conference_presenters][0][person_type]" data-conference-presenter-person-type class="w-full border p-2 rounded" required>
                                                            <option value="">กรุณาเลือก</option>
                                                            <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                            <option value="external">คนนอก</option>
                                                        </select>
                                                    </label>
                                                    <label data-conference-presenter-manual-wrap>
                                                        <span class="block text-sm text-gray-700 mb-1">ชื่อผู้นำเสนอ</span>
                                                        <input type="text" data-conference-presenter-manual-name class="w-full border p-2 rounded" placeholder="ยศ ชื่อ นามสกุล">
                                                    </label>
                                                    <label class="hidden" data-conference-presenter-user-wrap>
                                                        <span class="block text-sm text-gray-700 mb-1">เลือกผู้นำเสนอจากรายชื่อบุคลากร</span>
                                                        <select data-conference-presenter-user-select class="w-full border p-2 rounded">
                                                            <option value="">พิมพ์เพื่อค้นหาชื่อผู้นำเสนอ</option>
                                                            @foreach ($allUsers as $user)
                                                                <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <button type="button" class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600" onclick="removeConferencePresenterRow(this)">ลบ</button>
                                                </div>
                                            @endforelse
                                        </div>
                                        <button type="button" class="lux-mobile-add-row-btn whitespace-nowrap px-3 py-2 rounded-lg bg-amber-600 text-sm font-semibold text-white" onclick="addConferencePresenterRow(this)">+ เพิ่มผู้นำเสนอ</button>
                                    </div>
                                </div>
                            @endif

                            {{-- EA119 --}}
                            @if (str_starts_with($t, 'EA119'))
                                <div class="ea119-section space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <label>
                                            <span class="block text-sm text-gray-700 mb-1">ชื่อหนังสือ</span>
                                            <input name="outputs[{{ $i }}][book_title]" type="text"
                                                class="w-full border p-2 rounded"
                                                value="{{ old("outputs.$i.book_title", $out->book_title) }}"
                                                placeholder="เช่น Textbook of Public Health" required
                                                oninvalid="this.setCustomValidity('กรุณากรอกชื่อหนังสือ')"
                                                oninput="this.setCustomValidity('')">
                                        </label>
                                        <label>
                                            <span class="block text-sm text-gray-700 mb-1">ชื่อสำนักพิมพ์</span>
                                            <input name="outputs[{{ $i }}][publisher_name]" type="text"
                                                class="w-full border p-2 rounded"
                                                value="{{ old("outputs.$i.publisher_name", $out->publisher_name) }}"
                                                placeholder="เช่น Elsevier" required
                                                oninvalid="this.setCustomValidity('กรุณากรอกชื่อสำนักพิมพ์')"
                                                oninput="this.setCustomValidity('')">
                                        </label>
                                        <label>
                                            <span class="block text-sm text-gray-700 mb-1">ชื่อบท / เลขหน้า</span>
                                            <input name="outputs[{{ $i }}][chapter_pages]" type="text"
                                                class="w-full border p-2 rounded"
                                                value="{{ old("outputs.$i.chapter_pages", $out->chapter_pages) }}"
                                                placeholder="เช่น Chapter 2, pp. 45-66" required
                                                oninvalid="this.setCustomValidity('กรุณากรอกชื่อบท / เลขหน้า')"
                                                oninput="this.setCustomValidity('')">
                                        </label>
                                        <label>
                                            <span class="block text-sm text-gray-700 mb-1">เลข ISBN</span>
                                            <input name="outputs[{{ $i }}][isbn]" type="text"
                                                class="w-full border p-2 rounded"
                                                value="{{ old("outputs.$i.isbn", $out->isbn) }}"
                                                placeholder="เช่น 978-3-16-148410-0">
                                        </label>
                                    </div>

                                    @php $bookAuthors = $out->bookAuthors ?? collect(); @endphp
                                    <div class="space-y-3">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="font-semibold text-slate-800">รายชื่อผู้เขียน</span>
                                        </div>
                                        <div class="book-authors-container space-y-3" id="book-authors-{{ $i }}">
                                            @forelse($bookAuthors as $j => $author)
                                                @php
                                                    $bookAuthorPersonType = old("outputs.$i.book_authors.$j.person_type");
                                                    if ($bookAuthorPersonType === null) {
                                                        $bookAuthorPersonType = $author->user_id ? 'internal' : 'external';
                                                    }
                                                @endphp
                                                <div class="book-author-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3">
                                                    <input type="hidden" name="outputs[{{ $i }}][book_authors][{{ $j }}][id]" value="{{ $author->id }}">
                                                    <input type="hidden" name="outputs[{{ $i }}][book_authors][{{ $j }}][user_id]" value="{{ old("outputs.$i.book_authors.$j.user_id", $author->user_id) }}" data-user-id-hidden>
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span class="book-author-index inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-emerald-100 px-2 text-xs font-bold text-emerald-700">{{ $j + 1 }}</span>
                                                            <span class="text-sm font-semibold text-slate-700">ผู้เขียน</span>
                                                        </div>
                                                        <button type="button"
                                                            class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600"
                                                            onclick="removeBookAuthorRow(this)">ลบ</button>
                                                    </div>
                                                    <label>
                                                        <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก</span>
                                                        <select name="outputs[{{ $i }}][book_authors][{{ $j }}][person_type]" data-book-author-person-type class="w-full border p-2 rounded" required>
                                                            <option value="">กรุณาเลือก</option>
                                                            <option value="internal" {{ $bookAuthorPersonType === 'internal' ? 'selected' : '' }}>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                            <option value="external" {{ $bookAuthorPersonType === 'external' ? 'selected' : '' }}>คนนอก</option>
                                                        </select>
                                                    </label>
                                                    <label>
                                                        <span class="block text-sm text-gray-700 mb-1">ชื่อผู้เขียน</span>
                                                        <input type="hidden" name="outputs[{{ $i }}][book_authors][{{ $j }}][name]" value="{{ old("outputs.$i.book_authors.$j.name", $author->name) }}" data-book-author-name-hidden>
                                                        <div data-book-author-manual-wrap>
                                                            <input type="text" data-book-author-input data-book-author-manual-name list="book-author-name-list-{{ $i }}" class="w-full border p-2 rounded"
                                                                value="{{ old("outputs.$i.book_authors.$j.name", $author->name) }}"
                                                                placeholder="เลือกประเภทผู้เขียนก่อน"
                                                                onfocus="refreshBookAuthorDatalistInOutput(this)"
                                                                oninput="refreshBookAuthorDatalistInOutput(this)">
                                                        </div>
                                                        <div data-book-author-user-wrap class="hidden" style="display:none;">
                                                            <select data-book-author-user-select class="w-full border p-2 rounded">
                                                                <option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>
                                                                @foreach ($allUsers as $user)
                                                                    <option value="{{ $user->id }}" {{ (string) old("outputs.$i.book_authors.$j.user_id", $author->user_id) === (string) $user->id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </label>
                                                </div>
                                            @empty
                                                <div class="book-author-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3">
                                                    <input type="hidden" name="outputs[{{ $i }}][book_authors][0][user_id]" data-user-id-hidden>
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span class="book-author-index inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-emerald-100 px-2 text-xs font-bold text-emerald-700">1</span>
                                                            <span class="text-sm font-semibold text-slate-700">ผู้เขียน</span>
                                                        </div>
                                                        <button type="button"
                                                            class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600"
                                                            onclick="removeBookAuthorRow(this)">ลบ</button>
                                                    </div>
                                                    <label>
                                                        <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก</span>
                                                        <select name="outputs[{{ $i }}][book_authors][0][person_type]" data-book-author-person-type class="w-full border p-2 rounded" required>
                                                            <option value="">กรุณาเลือก</option>
                                                            <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                            <option value="external">คนนอก</option>
                                                        </select>
                                                    </label>
                                                    <label>
                                                        <span class="block text-sm text-gray-700 mb-1">ชื่อผู้เขียน</span>
                                                        <input type="hidden" name="outputs[{{ $i }}][book_authors][0][name]" data-book-author-name-hidden>
                                                        <div data-book-author-manual-wrap>
                                                            <input type="text" data-book-author-input data-book-author-manual-name list="book-author-name-list-{{ $i }}" class="w-full border p-2 rounded"
                                                                placeholder="เลือกประเภทผู้เขียนก่อน"
                                                                onfocus="refreshBookAuthorDatalistInOutput(this)"
                                                                oninput="refreshBookAuthorDatalistInOutput(this)">
                                                        </div>
                                                        <div data-book-author-user-wrap class="hidden" style="display:none;">
                                                            <select data-book-author-user-select class="w-full border p-2 rounded">
                                                                <option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>
                                                                @foreach ($allUsers as $user)
                                                                    <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforelse
                                        </div>
                                        <datalist class="book-author-name-list" id="book-author-name-list-{{ $i }}"></datalist>
                                        <div class="flex justify-center">
                                            <button type="button"
                                                class="lux-mobile-add-row-btn add-book-author-btn whitespace-nowrap px-3 py-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white"
                                                onclick="addBookAuthorRow(this)">+ เพิ่มผู้เขียน</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                
                @endforelse
            </div>

            <button type="button" onclick="addOutputRow()" class="lux-mobile-add-row-btn mb-6 px-3 py-1 bg-green-600 text-white rounded">
                + เพิ่ม Output
            </button>


            @php
                $latestProgressReport = ($project->progressReports ?? collect())->first();
                $progressCount = ($project->progressReports ?? collect())->count();
                $latestProgressDate = '-';
                $latestProgressTitle = 'ความก้าวหน้าของการดำเนินงาน';
                if ($latestProgressReport && $latestProgressReport->submit_date) {
                    try {
                        $d = \Carbon\Carbon::parse($latestProgressReport->submit_date)->locale('th');
                        $latestProgressDate = $d->translatedFormat('j F') . ' ' . ($d->year + 543);
                    } catch (\Throwable $e) {
                        $latestProgressDate = (string) $latestProgressReport->submit_date;
                    }
                }
                if ($latestProgressReport) {
                    $latestProgressTitle = trim((string) ($latestProgressReport->progress_title ?? '')) ?: 'ความก้าวหน้าของการดำเนินงาน';
                }
            @endphp
            <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">I: ความก้าวหน้าของการดำเนินงาน</h3>
            <div class="mb-5 rounded-xl border border-blue-200 bg-blue-50 p-3">
                <p id="progressReportSummaryText" class="text-sm text-blue-700 mt-1">
                    มีรายงาน {{ $progressCount }} รายการ
                    @if ($latestProgressReport)
                        · ล่าสุด {{ $latestProgressDate }}
                    @endif
                </p>
                <p id="progressReportLatestTitle"
                    class="text-sm text-blue-900 font-semibold mt-1 {{ $latestProgressReport ? '' : 'hidden' }}">
                    หัวข้อล่าสุด: {{ $latestProgressTitle }}
                </p>
                <div class="mt-3 grid grid-cols-1 gap-2">
                    <button type="button"
                        data-progress-modal-open
                        data-url="{{ route('projects.progress_reports.create', ['project' => $project, 'modal' => 1]) }}"
                        data-title="เพิ่มความก้าวหน้าของการดำเนินงาน"
                        class="px-3 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold text-center">
                        + เพิ่มความก้าวหน้า
                    </button>
                    <button type="button"
                        data-progress-modal-open
                        data-url="{{ route('projects.progress_reports.index', ['project' => $project, 'modal' => 1]) }}"
                        data-title="จัดการรายการความก้าวหน้า"
                        class="px-3 py-2 rounded-lg border border-blue-300 bg-white text-blue-700 text-sm font-semibold text-center">
                        จัดการรายการ
                    </button>
                    <button type="button"
                        id="progressEditLatestBtn"
                        data-progress-modal-open
                        data-url="{{ $latestProgressReport ? route('projects.progress_reports.edit', ['project' => $project, 'progress_report' => $latestProgressReport->id, 'modal' => 1]) : '' }}"
                        data-title="แก้ไขรายงาน: {{ $latestProgressTitle }}"
                        class="px-3 py-2 rounded-lg border border-blue-300 bg-white text-blue-700 text-sm font-semibold text-center {{ $latestProgressReport ? '' : 'hidden' }}">
                        แก้ไขรายงานล่าสุด
                    </button>
                </div>
            </div>

            <div id="progressReportModal" class="fixed inset-0 z-[90] hidden">
                <div class="absolute inset-0 bg-slate-900/50" data-progress-modal-close></div>
                <div class="relative mx-auto h-[100dvh] w-full rounded-none bg-white border border-slate-200 shadow-xl md:mt-3 md:h-[94vh] md:w-[98vw] md:max-w-[1700px] md:rounded-2xl">
                    <div class="flex items-center justify-between gap-2 px-3 py-2 border-b border-slate-200">
                        <h4 id="progressReportModalTitle" class="text-sm font-semibold text-slate-900">ความก้าวหน้าของการดำเนินงาน</h4>
                        <div class="flex items-center gap-2">
                            <button type="button" data-progress-modal-close
                                class="px-2.5 py-1 rounded-lg bg-slate-800 text-white text-xs hover:bg-slate-900">
                                ปิด
                            </button>
                        </div>
                    </div>
                    <iframe id="progressReportModalFrame" src="about:blank" class="w-full h-[calc(100dvh-49px)] md:h-[calc(94vh-49px)] rounded-none md:rounded-b-2xl"></iframe>
                </div>
            </div>


            <hr class="my-6 border-t-2 border-gray-300">
            <!-- J: ยุทธศาสตร์ Siriraj Health Policy -->
            <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">J: ยุทธศาสตร์ Siriraj Health Policy</h3>
            <table class="w-full table-auto text-sm mb-6">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-3 py-2 text-left">หมวด</th>
                        <th class="px-3 py-2 text-left">รายการ</th>
                        <th class="px-3 py-2 text-center">มี</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $strategies = [
                            '1' => [
                                'label' => 'AA501: Capacity Building',
                                'items' => [
                                    '1' => '1.1 การสรรหานักวิจัยด้านนโยบายสุขภาพที่มีความสามารถ',
                                    '2' => '1.2 การพัฒนาศักยภาพฯ ผ่านการเรียนรู้จากการปฏิบัติงานจริง',
                                    '3' => '1.3 การพัฒนาศักยภาพฯ ผ่านการศึกษาต่อเนื่อง',
                                    '4' => '1.4 การบูรณาการกับการเรียนการสอนนักศึกษาแพทย์',
                                    '5' => '1.5 การพัฒนาศักยภาพฯ ให้แก่บุคลากรศิริราช',
                                ],
                            ],
                            '2' => [
                                'label' => 'AA502: Excellence Research',
                                'items' => [
                                    '1' => '2.1 การเพิ่มบทบาทของหน่วยวิจัยฯ',
                                    '2' => '2.2 การสร้างความสัมพันธ์กับผู้กำหนดนโยบาย',
                                    '3' => '2.3 การสรรหาและกำหนดหัวข้อวิจัย',
                                ],
                            ],
                            '3' => [
                                'label' => 'AA503: Health Policy Linkage',
                                'items' => [
                                    '1' => '3.1 การสังเคราะห์องค์ความรู้ฯ จากงานวิจัยต้นแบบ',
                                    '2' => '3.2 การดำเนินงานวิจัยที่มีคุณภาพ',
                                    '3' => '3.3 ผลักดันให้งานวิจัยใช้ตัดสินใจนโยบาย',
                                    '4' => '3.4 เชื่อมโยงนวัตกรรมกับการพัฒนานโยบาย',
                                    '5' => '3.5 การสื่อสารงานวิจัยต่อสาธารณะ',
                                ],
                            ],
                            '4' => [
                                'label' => 'AA504: Partnership Engagement',
                                'items' => [
                                    '1' => '4.1 การสร้างเครือข่ายงานวิจัย',
                                    '2' => '4.2 การสร้างความร่วมมือฯ',
                                ],
                            ],
                            '5' => [
                                'label' => 'AA505: System Support',
                                'items' => [
                                    '1' => '5.1 การพัฒนาระบบสนับสนุนการดำเนินงานวิจัย',
                                    '2' => '5.2 การพัฒนาระบบสนับสนุนเส้นทางอาชีพในหน่วยวิจัย',
                                ],
                            ],
                        ];
                    @endphp

                    @foreach ($strategies as $sKey => $strategy)
                        <tr class="border-t">
                            <td class="px-3 py-2 font-bold" rowspan="{{ count($strategy['items']) + 1 }}">
                                {{ $strategy['label'] }}</td>
                        </tr>
                        @foreach ($strategy['items'] as $iKey => $desc)
                            @php $field = "strategy_{$sKey}_{$iKey}"; @endphp
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $desc }}</td>
                                <td class="px-3 py-2 text-center">
                                    <input type="checkbox" name="{{ $field }}" class="mr-1"
                                        {{ $project->$field ? 'checked' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>



            <div class="mt-8 flex flex-col sm:flex-row sm:justify-between items-center gap-4">
                <a href="{{ route('projects.index') }}"
                    class="inline-flex items-center text-blue-700 font-semibold hover:underline hover:text-blue-900 transition-colors">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    กลับไปยังรายการโครงการ
                </a>

                <button type="submit"
                    class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2 rounded shadow transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    บันทึกการเปลี่ยนแปลง
                </button>
            </div>

    </div>
    </form>


    {{-- Floating Save Bar (อยู่นอกฟอร์ม) --}}
    <div x-data="{
        dirty: false,
        scrolled: false,
        nearBottom: false,
        navOpen: false,
        get show() { return (this.dirty || this.scrolled) && !this.nearBottom && !this.navOpen }
    }" x-init="const THRESHOLD = 160; // ระยะห่างจากขอบล่างที่ต้องการให้ซ่อน (px)
    
    const onScrollOrResize = () => {
        scrolled = window.scrollY > 200;
        const doc = document.documentElement;
        const viewportBottom = window.scrollY + window.innerHeight;
        const docHeight = Math.max(doc.scrollHeight, doc.offsetHeight, doc.clientHeight);
        nearBottom = (docHeight - viewportBottom) <= THRESHOLD;
    };
    
    window.addEventListener('scroll', onScrollOrResize, { passive: true });
    window.addEventListener('resize', onScrollOrResize);
    onScrollOrResize();
    
    const form = document.getElementById('projectForm');
    if (form) {
        form.addEventListener('input', () => { dirty = true }, { passive: true });
        form.addEventListener('change', () => { dirty = true }, { passive: true });
    }

    window.addEventListener('app-mobile-nav', (e) => {
        navOpen = !!(e && e.detail && e.detail.open);
    });" x-show="show" x-transition.opacity
        class="fixed inset-x-0 bottom-0 md:inset-auto md:right-6 md:bottom-6 md:left-auto z-50" style="display:none"
        role="region" aria-live="polite">
        <div class="mx-3 mb-3 md:mx-0 md:mb-0">
            <div class="w-full md:w-auto md:min-w-[360px] bg-white/92 backdrop-blur border border-slate-200 shadow-lg rounded-2xl px-3 py-3">
                <div class="flex flex-col gap-2">
                    <div class="w-full flex items-center gap-2">
                        <a href="{{ route('projects.index') }}"
                            class="md:hidden inline-flex w-10 h-9 shrink-0 justify-center items-center text-slate-700 bg-slate-100 hover:bg-slate-200 font-bold text-base rounded-lg shadow">
                            &lt;
                        </a>

                        <button type="button" @click="document.getElementById('projectForm')?.requestSubmit()"
                            class="inline-flex flex-1 justify-center items-center bg-amber-200 hover:bg-amber-300 text-amber-900 font-semibold text-sm px-3 py-2 rounded-lg shadow">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            บันทึกการเปลี่ยนแปลง
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>




    </div>

    <script>
        // รายชื่อผู้ใช้จาก backend (ครั้งเดียวพอ)
        const userOptions = @json(
            \App\Models\User::orderBy('name')->get()->map(
                fn($u) => [
                    'fullName' => trim(($u->prefix ?? '') . ' ' . ($u->name ?? '')),
                ]));

        const FTE_DATALIST_ID = 'fteUsers';

        function escapeHtml(s = '') {
            return String(s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // สร้าง <datalist id="fteUsers"> กลางครั้งเดียว
        function ensureFteDatalist() {
            if (document.getElementById(FTE_DATALIST_ID)) return;
            const dl = document.createElement('datalist');
            dl.id = FTE_DATALIST_ID;
            dl.innerHTML = userOptions.map(u => `<option value="${escapeHtml(u.fullName)}">`).join('');
            document.body.appendChild(dl);
        }

        // เรียกครั้งเดียวหลังโหลดหน้า
        document.addEventListener('DOMContentLoaded', ensureFteDatalist);
    </script>



    <script>
        function keepAddedRowOpen(rowOrId) {
            const row = typeof rowOrId === 'string' ? document.getElementById(rowOrId) : rowOrId;
            if (!row) return;
            row.dataset.forceOpen = '1';
            row.dataset.newlyAdded = '1';
            row.dataset.collapsed = '0';
            row.classList.remove('row-collapsed');
            setTimeout(() => {
                if (!row.isConnected) return;
                if (row.dataset.newlyAdded === '1') {
                    delete row.dataset.forceOpen;
                    delete row.dataset.newlyAdded;
                }
            }, 400);
        }

        function confirmProjectMobileRemoval(callback) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ยืนยันการลบ',
                    text: 'ต้องการลบรายการนี้ใช่หรือไม่?',
                    showCancelButton: true,
                    confirmButtonText: 'ลบ',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#dc2626'
                }).then((result) => {
                    if (result.isConfirmed) callback();
                });
                return;
            }
            callback();
        }

        function reindexAuthorRows(container) {
            container?.querySelectorAll('.author-row').forEach((row, index) => {
                row.querySelectorAll('.author-index').forEach((el) => {
                    el.textContent = index + 1;
                });
            });
        }

        function reindexConferenceRows(container) {
            container?.querySelectorAll('.conference-presenter-row').forEach((row, index) => {
                row.querySelectorAll('.conference-index').forEach((el) => {
                    el.textContent = index + 1;
                });
            });
        }

        function reindexBookAuthorRows(container) {
            container?.querySelectorAll('.book-author-row').forEach((row, index) => {
                row.querySelectorAll('.book-author-index').forEach((el) => {
                    el.textContent = index + 1;
                });
            });
        }

        // let irbCodeIndex = {{ count($project->irbs ?? []) }};
        let irbCodeIndex = document.querySelectorAll('.irb-row').length;

        function addIRBCode() {
            const container = document.getElementById('irb-code-container');
            const id = `irb-${irbCodeIndex}`;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="irb-row space-y-3 p-4 border rounded-lg bg-white">
            <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-semibold text-slate-700">IRB ที่ <span class="irb-index">${irbCodeIndex + 1}</span></span>
                <button type="button" onclick="removeRow('${id}', 'irb')" class="text-red-600">✕</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="w-full">
                <span class="block text-sm text-gray-700 mb-1">รหัส IRB <span class="text-red-500">*</span></span>
                <input name="irbs[${irbCodeIndex}][code]" type="text" placeholder="เช่น SIHP-IRB-2025-001" class="w-full border p-2 rounded" required
                    oninvalid="this.setCustomValidity('กรุณาระบุรหัส IRB')"
                    oninput="this.setCustomValidity('')">
            </div>
            <div class="w-full">
                <span class="block text-sm text-gray-700 mb-1">หน่วยงาน <span class="text-red-500">*</span></span>
                <input name="irbs[${irbCodeIndex}][source]" type="text" placeholder="ชื่อหน่วยงานที่ออก IRB" class="w-full border p-2 rounded" required
                    oninvalid="this.setCustomValidity('กรุณาระบุหน่วยงาน')"
                    oninput="this.setCustomValidity('')">
            </div>
            <div class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">วันหมดอายุ IRB <span class="text-red-500">*</span></span>
                    <input name="irbs[${irbCodeIndex}][exp_date]" type="text" class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="เลือกวันหมดอายุ IRB" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกวันหมดอายุ IRB')"
                        oninput="this.setCustomValidity('')">
                    <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            </div>
        </div>
    `);

            irbCodeIndex++;
            reindexIRB();
            keepAddedRowOpen(id);

            const dateInput = document.querySelector(`#${id} .thai-datepicker`);
            if (MOBILE_WHEEL_MODE) {
                initNativeThaiDateInput(dateInput);
                bindProjectDatePairsThaiPicker();
            } else {
                flatpickr(`#${id} .thai-datepicker`, {
                    locale: "th",
                    dateFormat: "d-m-Y",
                    altInput: true,
                    altFormat: "j F Y",
                    disableMobile: true,
                    onReady: (selectedDates, dateStr, instance) => {
                        const input = instance.input;
                        const altInput = instance.altInput;
                        if (altInput) {
                            altInput.readOnly = true;
                            altInput.className = input.className;
                            altInput.classList.remove('js-date-be', 'thai-datepicker', 'native-date-raw', 'flatpickr-hidden');
                            altInput.placeholder = input.getAttribute('placeholder') || input.dataset.placeholder || '';
                        }
                        if (input) input.classList.add('flatpickr-hidden');
                        toBuddhist(selectedDates, dateStr, instance);
                        requestAnimationFrame(() => toBuddhist(instance.selectedDates || selectedDates, dateStr, instance));
                    },
                    onValueUpdate: toBuddhist,
                    onChange: toBuddhist,
                    onMonthChange: toBuddhist,
                    onYearChange: toBuddhist,
                });
                bindDatepickerSummarySync(dateInput);
            }
        }


        function reindexIRB() {
            const rows = document.querySelectorAll('#irb-code-container .irb-row');
            rows.forEach((row, index) => {
                const spans = row.querySelectorAll('.irb-index');
                spans.forEach(span => span.textContent = index + 1);
            });
        }

        let copiIndex = {{ count($project->copis ?? []) }};

        function addCopiRow() {
            const container = document.getElementById('copi-container');
            const id = `copi-${copiIndex}`;
            const labelNum = copiIndex + 1;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="copi-row space-y-2 p-4 border rounded-lg bg-white">
            <input type="hidden" name="copis[${copiIndex}][name]" data-copi-name>
            <input type="hidden" name="copis[${copiIndex}][user_id]" data-copi-user-id>
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-slate-600">ลำดับ</span>
                <span class="inline-flex px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold copi-index">${labelNum}</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <label class="col-span-12 md:col-span-5">
                    <span class="block text-sm text-gray-700 mb-1">คนใน / คนนอก <span class="text-red-500">*</span></span>
                    <select name="copis[${copiIndex}][person_type]" data-copi-person-type class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกประเภท COPI')"
                        oninput="this.setCustomValidity('')">
                        <option value="">กรุณาเลือก</option>
                        <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                        <option value="external">คนนอก</option>
                    </select>
                </label>
                <label class="col-span-12 md:col-span-5">
                    <span class="block text-sm text-gray-700 mb-1">สังกัด COPI <span class="text-red-500">*</span></span>
                    <input name="copis[${copiIndex}][affiliation]" type="text" list="copiAffiliationList" data-copi-affiliation
                        class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed" placeholder="เลือกประเภท COPI ก่อน" disabled
                        oninvalid="this.setCustomValidity('กรุณาระบุสังกัด COPI')"
                        oninput="this.setCustomValidity('')">
                </label>
                <div class="col-span-12 md:col-span-2 flex justify-end md:pt-6">
                    <button type="button" onclick="removeRow('${id}', 'copi')" class="text-red-600 text-xl">✕</button>
                </div>
                <label class="col-span-12" data-copi-manual-wrap>
                    <span class="block text-sm text-gray-700 mb-1">ชื่อ COPI <span class="text-red-500">*</span></span>
                    <input list="userNames" data-copi-manual-name class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed"
                        placeholder="เลือกประเภท COPI ก่อน" disabled
                        oninvalid="this.setCustomValidity('กรุณาระบุชื่อ COPI')"
                        oninput="this.setCustomValidity('')">
                </label>
                <label class="col-span-12 hidden" data-copi-user-wrap>
                    <span class="block text-sm text-gray-700 mb-1">เลือก COPI จากรายชื่อบุคลากร <span class="text-red-500">*</span></span>
                    <select data-copi-user-select class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed" disabled
                        oninvalid="this.setCustomValidity('กรุณาเลือก COPI จากรายการ')"
                        oninput="this.setCustomValidity('')">
                        <option value="">พิมพ์เพื่อค้นหาชื่อบุคลากร</option>
                        @foreach ($allUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>
            `);

            copiIndex++;
            keepAddedRowOpen(id);
        }



        function reindexCopi() {
            const rows = document.querySelectorAll('#copi-container .copi-row');
            rows.forEach((row, index) => {
                const labels = row.querySelectorAll('.copi-index');
                labels.forEach(label => label.textContent = index + 1);
            });
        }





        function getNextFteIndex() {
            const container = document.getElementById('fte-container');
            if (!container) return 0;
            let maxIndex = -1;
            container.querySelectorAll('[name^="fte_roles["]').forEach((el) => {
                const m = String(el.name || '').match(/^fte_roles\[(\d+)\]/);
                if (!m) return;
                const idx = Number(m[1]);
                if (!Number.isNaN(idx)) maxIndex = Math.max(maxIndex, idx);
            });
            return maxIndex + 1;
        }

        function buildFteSelectOptions(options, selectedValue, placeholder) {
            const normalizedSelected = String(selectedValue ?? '');
            const selectedExists = options.some((option) => String(option) === normalizedSelected);
            const items = [`<option value="" disabled ${normalizedSelected ? '' : 'selected'}>${escapeHtml(placeholder)}</option>`];
            if (normalizedSelected && !selectedExists) {
                items.push(`<option value="${escapeHtml(normalizedSelected)}" selected>${escapeHtml(normalizedSelected)}</option>`);
            }
            options.forEach((option) => {
                const selected = String(option) === normalizedSelected ? 'selected' : '';
                items.push(`<option value="${escapeHtml(option)}" ${selected}>${escapeHtml(option)}</option>`);
            });
            return items.join('');
        }

        function addFTERow(options = {}) {
            const {
                skipValidation = false,
                initialData = null
            } = options;

            if (!skipValidation && typeof window.validateFteBeforeAdd === 'function' && !window.validateFteBeforeAdd({
                    showAlert: true
                })) {
                return;
            }

            const container = document.getElementById('fte-container');
            const index = getNextFteIndex();
            const id = `fte-${index}`;
            const rowData = initialData || {};

            ensureFteDatalist();

            const roleGroups = ['Scientific', 'Management', 'IT + Communication', 'Logistics + Admin'];
            const jobDetails = [
                'Researcher', 'Researcher Assistant', 'Project Manager',
                'Research Coordinator', 'นักวิชาการสารสนเทศ',
                'นักประชาสัมพันธ์', 'เจ้าหน้าที่บริหารงานทั่วไป'
            ];
            const taskDetails = @json($taskDetails);

            const groupDropdown = buildFteSelectOptions(roleGroups, rowData.role_group, 'เลือกกลุ่ม');
            const jobDropdown = buildFteSelectOptions(jobDetails, rowData.job_detail, 'เลือกภาระงาน');
            const taskDropdown = buildFteSelectOptions(taskDetails, rowData.task_detail, 'เลือกภารกิจ');

            container.insertAdjacentHTML('beforeend', `
<div id="${id}" class="fte-mobile-row p-4 border rounded-lg bg-white shadow-sm" data-deleted="0">
    <input type="hidden" name="fte_roles[${index}][id]" value="${escapeHtml(rowData.id || '')}">
    <div class="fte-mobile-header">
        <button type="button" class="fte-mobile-toggle text-left">
            <span class="text-sm font-semibold text-slate-700">ผู้มีส่วนร่วมคนที่ <span class="fte-index">${index + 1}</span></span>
            <div class="fte-mobile-summary mt-2"></div>
        </button>
        <button type="button" onclick="removeFteRow('${id}')" class="fte-mobile-remove">ลบ</button>
    </div>
    <div class="fte-mobile-body grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

        <div class="md:col-span-9 space-y-4">

            <!-- ชื่อ + กลุ่ม -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="w-full">
                    <span class="fte-field-label text-sm font-semibold text-gray-700 mb-1">
                        ชื่อ-นามสกุล
                        <span class="text-red-500">*</span>
                    </span>

                    <input list="${FTE_DATALIST_ID}"
                        name="fte_roles[${index}][name]"
                        value="${escapeHtml(rowData.name || '')}"
                        class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500"
                        placeholder="พิมพ์หรือเลือกชื่อผู้มีส่วนร่วม"
                        required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกหรือพิมพ์ชื่อผู้ใช้จากรายการ')"
                                    oninput="this.setCustomValidity('')">
                </label>

                <label class="w-full">
                    <span class="block text-sm font-semibold text-gray-700 mb-1">กลุ่ม</span>
                    <select name="fte_roles[${index}][role_group]" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกกลุ่ม')"
                        oninput="this.setCustomValidity('')">
                        ${groupDropdown}
                    </select>
                </label>
            </div>

            <!-- ภาระงาน + วันที่ -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <label class="w-full">
                    <span class="fte-field-label text-sm font-semibold text-gray-700 mb-1">
                        ภาระงาน
                        <span class="text-red-500">*</span>
                    </span>
                    <select name="fte_roles[${index}][job_detail]" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกภาระงาน')"
                        oninput="this.setCustomValidity('')">
                        ${jobDropdown}
                    </select>
                </label>

                <label class="w-full relative">
                    <span class="fte-field-label text-sm font-semibold text-gray-700 mb-1">
                        วันเริ่มต้นภาระงาน
                        <span class="text-red-500">*</span>
                    </span>
                    <input name="fte_roles[${index}][start_date]"
                        type="text"
                        value="${escapeHtml(rowData.start_date || '')}"
                        class="thai-datepicker w-full border p-2 rounded pl-10"
                        placeholder="เลือกวันเริ่มต้นภาระงาน"
                        required
                        oninvalid="this.setCustomValidity('กรุณาเลือกวันเริ่มต้นภาระงาน')"
                        oninput="this.setCustomValidity('')">

                    <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </label>

                <label class="w-full relative">
                    <span class="fte-field-label text-sm font-semibold text-gray-700 mb-1">
                        วันสิ้นสุดภาระงาน
                        <span class="text-red-500">*</span>
                    </span>
                    <input name="fte_roles[${index}][end_date]"
                        type="text"
                        value="${escapeHtml(rowData.end_date || '')}"
                        class="thai-datepicker w-full border p-2 rounded pl-10"
                        placeholder="เลือกวันสิ้นสุดภาระงาน"
                        required
                        oninvalid="this.setCustomValidity('กรุณาเลือกวันสิ้นสุดภาระงาน')"
                        oninput="this.setCustomValidity('')">

                    <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </label>

            </div>

            <!-- ชั่วโมง (NEW) -->
            <label class="w-full">
                <span class="fte-field-label text-sm font-semibold text-gray-700 mb-1">
                    ชั่วโมง (Hour)
                    <span class="text-red-500">*</span>
                </span>
                <input type="number"
                    step="0.1"
                    min="0"
                    name="fte_roles[${index}][hours]"
                    value="${escapeHtml(rowData.hours || '')}"
                    class="fte-hours w-full border p-2 rounded text-right"
                    placeholder="0.0"
                    required
                    oninvalid="this.setCustomValidity('กรุณาระบุชั่วโมงการทำงาน')"
                    oninput="this.setCustomValidity('')">
            </label>

            <!-- รายละเอียดภารกิจ -->
            <label class="w-full">
                <span class="fte-field-label text-sm font-semibold text-gray-700 mb-1">
                    รายละเอียดภารกิจ
                    <span class="text-red-500">*</span>
                </span>
                <select name="fte_roles[${index}][task_detail]"
                    class="w-full border p-2 rounded"
                    required
                    oninvalid="this.setCustomValidity('กรุณาเลือกรายละเอียดภารกิจ')"
                    oninput="this.setCustomValidity('')">
                    ${taskDropdown}
                </select>
            </label>

        </div>

        <div class="md:col-span-3">
            <div class="fte-metrics-grid">

            <!-- FTE ผู้วิจัย คำนวณอัตโนมัติ -->
            <label class="fte-metric-card block border rounded-xl bg-blue-50">
                <span class="block text-sm font-bold text-blue-800 mb-2">FTE จากชั่วโมง</span>
                <input name="fte_roles[${index}][fte_value]"
                    type="number"
                    step="0.00001"
                    min="0" max="1"
                    value="${escapeHtml(rowData.fte_value || '')}"
                    placeholder="0.00000"
                    class="fte-value w-full border p-3 rounded text-right bg-gray-100"
                    readonly>
            </label>

            <!-- FTE ผู้บริหาร -->
            <label class="fte-metric-card block border rounded-xl bg-emerald-50">
                <span class="block text-sm font-bold text-emerald-800 mb-2">FTE (ผู้บริหาร)</span>
                <input name="fte_roles[${index}][fte_value_research]"
                    type="number"
                    step="0.01"
                    min="0" max="1"
                    value="${escapeHtml(rowData.fte_value_research || '')}"
                    placeholder="0.00"
                    class="w-full border p-3 rounded text-right">
            </label>
            </div>
        </div>

    </div>
</div>
`);

            const row = document.getElementById(id);
            const rowNumber = container.querySelectorAll('.fte-mobile-row').length;
            setFteRowIndex(row, rowNumber);
            keepAddedRowOpen(id);
            initializeMobileFteRow(row);
            collapseAllMobileFteCards(row);
            setMobileFteCollapsed(row, false);
            return row;
        }

        document.addEventListener("DOMContentLoaded", function() {
            reindexFTE();
            initMobileFteCards();
        });

        function setFteRowIndex(row, index) {
            row?.querySelectorAll('.fte-index').forEach((span) => {
                span.textContent = index;
            });
        }

        function reindexFTE(startIndex = 0) {
            const rows = document.querySelectorAll('#fte-container .fte-mobile-row');
            rows.forEach((row, index) => {
                if (index < startIndex) return;
                setFteRowIndex(row, index + 1);
            });
        }

        function getFteContainer() {
            return document.getElementById('fte-container');
        }

        function getFteFieldValue(row, field) {
            return row.querySelector(`[name^="fte_roles["][name$="[${field}]"]`)?.value?.trim() || '';
        }

        function getFteSerializedValue(row, field, {
            preferIso = false
        } = {}) {
            const input = row.querySelector(`[name^="fte_roles["][name$="[${field}]"]`);
            if (!input) return '';

            const rawValue = input.value?.trim() || '';
            const isoValue = input.dataset?.isoValue?.trim() || '';

            if (preferIso) {
                const parsedRaw = parseLocalDateInput(rawValue);
                if (parsedRaw) {
                    return toDmyCE(parsedRaw);
                }

                const parsedIso = parseLocalDateInput(isoValue);
                if (parsedIso) {
                    return toDmyCE(parsedIso);
                }

                return rawValue || isoValue;
            }

            return rawValue || isoValue;
        }

        function serializeFteRows() {
            const container = getFteContainer();
            if (!container) return [];

            return Array.from(container.querySelectorAll('.fte-mobile-row'))
                .filter((row) => row.dataset.deleted !== '1' && row.dataset.remoteDeleted !== '1')
                .map((row) => {
                return {
                    id: getFteSerializedValue(row, 'id'),
                    name: getFteSerializedValue(row, 'name'),
                    role_group: getFteSerializedValue(row, 'role_group'),
                    job_detail: getFteSerializedValue(row, 'job_detail'),
                    start_date: getFteSerializedValue(row, 'start_date', {
                        preferIso: true
                    }),
                    end_date: getFteSerializedValue(row, 'end_date', {
                        preferIso: true
                    }),
                    hours: getFteSerializedValue(row, 'hours'),
                    fte_value: getFteSerializedValue(row, 'fte_value'),
                    fte_value_research: getFteSerializedValue(row, 'fte_value_research'),
                    task_detail: getFteSerializedValue(row, 'task_detail'),
                };
            });
        }

        function getFteSummaryParts(row) {
            const name = getFteFieldValue(row, 'name') || 'ยังไม่ได้ระบุชื่อ';
            const roleGroup = getFteFieldValue(row, 'role_group');
            const jobDetail = getFteFieldValue(row, 'job_detail');
            const hours = getFteFieldValue(row, 'hours') || '-';
            const startDate = getFteFieldValue(row, 'start_date') || '-';
            const endDate = getFteFieldValue(row, 'end_date') || '-';
            const taskDetail = getFteFieldValue(row, 'task_detail');
            const fteValue = getFteFieldValue(row, 'fte_value');

            const meta = [roleGroup, jobDetail].filter(Boolean).join(' / ') || 'ยังไม่ได้เลือกกลุ่มหรือภาระงาน';
            const dateRange = `${startDate} ถึง ${endDate}`;
            const hoursText = `${hours} ชั่วโมง`;
            const task = taskDetail || 'ยังไม่ได้เลือกรายละเอียดภารกิจ';
            const metrics = [
                hours !== '-' ? `${hours} hr` : '0 hr',
                fteValue ? `FTE ${fteValue}` : 'FTE 0.00000000'
            ].join(' | ');

            return {
                name,
                meta,
                dateRange,
                hoursText,
                task,
                metrics
            };
        }

        function getMobileFteCollapsedState(row) {
            return row?.dataset?.fteMobileCollapsed === '1';
        }

        function restoreMobileFteState(row, collapsed) {
            if (!(row instanceof HTMLElement)) return;
            row.classList.remove('row-collapsed', 'row-collapsible');
            row.dataset.collapsed = '0';
            delete row.dataset.forceOpen;
            setMobileFteCollapsed(row, collapsed);
        }

        function confirmMobileFteRemoval(row, callback) {
            const collapsed = getMobileFteCollapsedState(row);
            const parts = getFteSummaryParts(row);
            const text = `ต้องการลบ ผู้มีส่วนร่วม ชื่อ ${parts.name} หรือไม่?`;

            const onCancel = () => restoreMobileFteState(row, collapsed);

            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ยืนยันการลบ',
                    text,
                    showCancelButton: true,
                    confirmButtonText: 'ลบ',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#dc2626'
                }).then((result) => {
                    if (result.isConfirmed) {
                        callback();
                    } else {
                        onCancel();
                    }
                });
                return;
            }

            if (confirm(text)) {
                callback();
            } else {
                onCancel();
            }
        }

        function setMobileFteCollapsed(row, collapsed) {
            if (!(row instanceof HTMLElement)) return;
            row.classList.toggle('fte-mobile-collapsed', collapsed);
            row.dataset.fteMobileCollapsed = collapsed ? '1' : '0';
            const body = row.querySelector('.fte-mobile-body');
            if (body) body.hidden = collapsed;
            const summary = row.querySelector('.fte-mobile-summary');
            if (summary) {
                if (row.dataset.deleted === '1') {
                    summary.innerHTML = `รายการนี้ถูกทำเครื่องหมายเพื่อลบ<br>ระบบจะข้ามการบันทึกแถวนี้`;
                    return;
                }
                if (row.dataset.remoteDeleted === '1') {
                    summary.innerHTML = `รายการนี้ถูกลบจากผู้ใช้อื่นแล้ว<br>ระบบคงแถวนี้ไว้เพื่อแสดงสถานะลบ`;
                    return;
                }
                const parts = getFteSummaryParts(row);
                summary.innerHTML = collapsed ? `${parts.name}<br>${parts.meta}<br>${parts.dateRange}<br>${parts.hoursText}<br>${parts.task}` : '';
            }
        }

        function collapseAllMobileFteCards(exceptRow = null) {
            document.querySelectorAll('#fte-container .fte-mobile-row').forEach((row) => {
                if (row === exceptRow) return;
                setMobileFteCollapsed(row, true);
            });
        }

        function ensureMobileFteDisclosure(row) {
            if (!(row instanceof HTMLElement)) return;
            const toggle = row.querySelector('.fte-mobile-toggle');
            const body = row.querySelector('.fte-mobile-body');
            const removeBtn = row.querySelector('.fte-mobile-remove');
            if (!toggle || !body) return;

            if (row.dataset.mobileFteDisclosureReady !== '1') {
                row.dataset.mobileFteDisclosureReady = '1';
                toggle.addEventListener('click', () => {
                    const nextCollapsed = row.dataset.fteMobileCollapsed !== '1';
                    collapseAllMobileFteCards(nextCollapsed ? null : row);
                    setMobileFteCollapsed(row, nextCollapsed);
                });
                ['pointerdown', 'mousedown', 'touchstart'].forEach((eventName) => {
                    removeBtn?.addEventListener(eventName, (event) => {
                        event.stopPropagation();
                    });
                });
                removeBtn?.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                });
                row.addEventListener('input', () => {
                    if (row.dataset.fteMobileCollapsed === '1') {
                        setMobileFteCollapsed(row, true);
                    }
                });
                row.addEventListener('change', () => {
                    if (row.dataset.fteMobileCollapsed === '1') {
                        setMobileFteCollapsed(row, true);
                    }
                });
            }

            const shouldOpen = row.dataset.forceOpen === '1';
            setMobileFteCollapsed(row, !shouldOpen);
        }

        function normalizeMobileFteCard(row) {
            if (!(row instanceof HTMLElement)) return;

            const bodyHost = row._fteBodyHost;
            if (bodyHost instanceof HTMLElement) {
                while (bodyHost.firstChild) {
                    row.appendChild(bodyHost.firstChild);
                }
                bodyHost.remove();
            }

            row._fteBodyHost = null;
            row._fteBodyFragment = null;
            delete row.dataset.fteHydrated;
            delete row.dataset.fteBodyMounted;
            row.classList.remove('row-collapsed', 'row-collapsible');
            row.dataset.collapsed = '0';

            row.querySelectorAll('.row-collapse-toggle, .row-collapse-summary, .fte-summary-shell').forEach((el) => el.remove());
            ensureMobileFteDisclosure(row);
        }

        function initializeMobileFteRow(row) {
            normalizeMobileFteCard(row);
            if (typeof primeLazyThaiDatepickers === 'function') {
                primeLazyThaiDatepickers(row);
            }
        }

        function initMobileFteCards() {
            const container = getFteContainer();
            if (!container) return;

            const normalizeAll = () => {
                container.querySelectorAll('.fte-mobile-row').forEach((row, index) => {
                    normalizeMobileFteCard(row);
                    setFteRowIndex(row, index + 1);
                });
            };

            normalizeAll();
            window.requestAnimationFrame(normalizeAll);
            window.setTimeout(normalizeAll, 60);

            if (container.dataset.mobileFteCardsReady === '1') return;
            container.dataset.mobileFteCardsReady = '1';

            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (!(node instanceof HTMLElement)) return;
                        if (node.classList.contains('fte-mobile-row')) {
                            initializeMobileFteRow(node);
                            reindexFTE();
                            return;
                        }
                        node.querySelectorAll?.('.fte-mobile-row').forEach((row) => initializeMobileFteRow(row));
                    });
                });
            });
            observer.observe(container, {
                childList: true
            });

            document.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLElement)) return;
                if (target.closest('#fte-container .fte-mobile-row')) return;
                if (target.closest('[data-fte-add-row]')) return;
                if (
                    target.closest('.swal2-container') ||
                    target.closest('.flatpickr-calendar') ||
                    target.closest('.thai-wheel-overlay') ||
                    target.closest('.thai-wheel-sheet')
                ) {
                    return;
                }
                collapseAllMobileFteCards();
            });
        }

        function readFteRowData(row) {
            return {
                id: getFteSerializedValue(row, 'id'),
                name: getFteSerializedValue(row, 'name'),
                role_group: getFteSerializedValue(row, 'role_group'),
                job_detail: getFteSerializedValue(row, 'job_detail'),
                start_date: getFteSerializedValue(row, 'start_date', {
                    preferIso: true
                }),
                end_date: getFteSerializedValue(row, 'end_date', {
                    preferIso: true
                }),
                hours: getFteSerializedValue(row, 'hours'),
                fte_value: getFteSerializedValue(row, 'fte_value'),
                fte_value_research: getFteSerializedValue(row, 'fte_value_research'),
                task_detail: getFteSerializedValue(row, 'task_detail'),
            };
        }

        function buildFteRowSignature(data) {
            return JSON.stringify([
                String(data.id ?? ''),
                String(data.name ?? ''),
                String(data.role_group ?? ''),
                String(data.job_detail ?? ''),
                String(data.task_detail ?? ''),
                String(data.start_date ?? ''),
                String(data.end_date ?? ''),
                String(data.hours ?? ''),
                String(data.fte_value ?? ''),
                String(data.fte_value_research ?? ''),
            ]);
        }

        function getFteExistingIdSet() {
            return new Set(
                Array.from(document.querySelectorAll('#fte-container input[name^="fte_roles["][name$="[id]"]'))
                    .map((input) => String(input.value || '').trim())
                    .filter(Boolean)
            );
        }

        function setFteFieldValue(row, field, value) {
            const input = row.querySelector(`[name^="fte_roles["][name$="[${field}]"]`);
            if (!input) return;

            const normalizedValue = value == null ? '' : String(value);

            if (field === 'start_date' || field === 'end_date') {
                if (MOBILE_WHEEL_MODE) {
                    const parsed = parseLocalDateInput(normalizedValue);
                    input.value = parsed ? toYmdCE(parsed) : normalizedValue;
                } else if (input._flatpickr) {
                    if (normalizedValue) {
                        input._flatpickr.setDate(normalizedValue, false, 'd-m-Y');
                    } else {
                        input._flatpickr.clear(false);
                    }
                    input.value = normalizedValue;
                } else {
                    input.value = normalizedValue;
                }
                input.dataset.isoValue = normalizedValue;
                return;
            }

            input.value = normalizedValue;
        }

        function applyRemoteFteRowData(row, data) {
            if (row?.dataset?.deleted === '1' || row?.dataset?.remoteDeleted === '1') return;
            setFteFieldValue(row, 'id', data.id);
            setFteFieldValue(row, 'name', data.name);
            setFteFieldValue(row, 'role_group', data.role_group);
            setFteFieldValue(row, 'job_detail', data.job_detail);
            setFteFieldValue(row, 'task_detail', data.task_detail);
            setFteFieldValue(row, 'start_date', data.start_date);
            setFteFieldValue(row, 'end_date', data.end_date);
            setFteFieldValue(row, 'hours', data.hours);
            setFteFieldValue(row, 'fte_value', data.fte_value);
            setFteFieldValue(row, 'fte_value_research', data.fte_value_research);
            setMobileFteCollapsed(row, row.dataset.fteMobileCollapsed === '1');
            row.dataset.remoteActorName = String(data.updated_by_name || '');
            row.dataset.remoteOccurredAt = String(data.updated_logged_at || data.updated_at || '');
            row.dataset.remoteSignature = buildFteRowSignature(data);
            row.dataset.remoteDirty = '0';
        }

        function flashRemoteFteRow(row, kind) {
            if (!(row instanceof HTMLElement)) return;
            const className = kind === 'new'
                ? 'fte-row-remote-new'
                : (kind === 'deleted' ? '' : 'fte-row-remote-updated');
            row.classList.remove('fte-row-remote-new', 'fte-row-remote-updated');
            if (className) {
                void row.offsetWidth;
                row.classList.add(className);
            }
            document.dispatchEvent(new CustomEvent('fte:remote-change', {
                detail: {
                    rowId: row.id,
                    kind,
                    actorName: row.dataset.remoteActorName || 'ผู้ใช้อื่น',
                    occurredAt: row.dataset.remoteOccurredAt || '',
                }
            }));
            window.syncSingleFteTableRowState?.(row);
        }

        function isFteRowLockedForRemote(row) {
            return row?.dataset?.deleted === '1' || row?.dataset?.remoteDeleted === '1' || row?.dataset?.remoteDirty === '1' || row?.contains(document.activeElement);
        }

        function setMobileFteRowDisabledState(row, disabled) {
            if (!(row instanceof HTMLElement)) return;
            row.querySelectorAll('input, select, textarea, button').forEach((field) => {
                if (field.classList.contains('fte-mobile-remove')) return;
                if (field.classList.contains('fte-mobile-toggle')) return;
                field.disabled = disabled;
            });
        }

        function markMobileFteRowDeleted(row) {
            if (!(row instanceof HTMLElement) || row.dataset.deleted === '1') return;

            const idInput = row.querySelector('input[name^="fte_roles["][name$="[id]"]');
            const existingId = Number(idInput?.value || 0);
            if (existingId > 0) {
                const deletedContainer = document.getElementById('fte-deleted-ids');
                if (deletedContainer && !deletedContainer.querySelector(`input[value="${existingId}"]`)) {
                    deletedContainer.insertAdjacentHTML(
                        'beforeend',
                        `<input type="hidden" name="fte_deleted_ids[]" value="${existingId}">`
                    );
                }
            }

            row.dataset.deleted = '1';
            row.classList.add('fte-row-deleted');
            setMobileFteRowDisabledState(row, true);
            setMobileFteCollapsed(row, true);
            row.style.background = '#fff1f2';
            row.style.borderColor = '#fda4af';

            const removeBtn = row.querySelector('.fte-mobile-remove');
            if (removeBtn) {
                removeBtn.textContent = 'จะถูกลบ';
                removeBtn.disabled = true;
                removeBtn.classList.add('bg-rose-100', 'text-rose-800', 'border', 'border-rose-300');
            }

            const note = row.querySelector('.fte-mobile-delete-note');
            if (note) {
                note.style.display = 'block';
                note.textContent = 'รายการนี้ถูกทำเครื่องหมายเพื่อลบแล้ว ระบบจะไม่บันทึกการแก้ไขของแถวนี้เมื่อกดบันทึก';
            }
        }

        function markMobileFteRowRemoteDeleted(row, meta = {}) {
            if (!(row instanceof HTMLElement) || row.dataset.remoteDeleted === '1' || row.dataset.deleted === '1') return;

            row.dataset.remoteDeleted = '1';
            row.dataset.remoteActorName = String(meta.actorName || row.dataset.remoteActorName || '');
            row.dataset.remoteOccurredAt = String(meta.occurredAt || row.dataset.remoteOccurredAt || '');
            row.classList.add('fte-row-deleted');
            setMobileFteRowDisabledState(row, true);
            setMobileFteCollapsed(row, true);
            row.style.background = '#fff1f2';
            row.style.borderColor = '#fda4af';

            const removeBtn = row.querySelector('.fte-mobile-remove');
            if (removeBtn) {
                removeBtn.textContent = 'ถูกลบแล้ว';
                removeBtn.disabled = true;
                removeBtn.classList.add('bg-rose-100', 'text-rose-800', 'border', 'border-rose-300');
            }

            const note = row.querySelector('.fte-mobile-delete-note');
            if (note) {
                note.style.display = 'block';
                note.textContent = 'รายการนี้ถูกลบจากผู้ใช้อื่นแล้ว ระบบคงแถวนี้ไว้เพื่อแสดงสถานะลบ';
            }

            flashRemoteFteRow(row, 'deleted');
        }

        function updateFteSyncStatus(message) {
            const el = document.getElementById('fteSyncStatus');
            if (el) el.textContent = message;
        }

        function reorderFteRowsByServerIds(container, orderedIds) {
            if (!(container instanceof HTMLElement) || !Array.isArray(orderedIds) || !orderedIds.length) return;

            const idToRow = new Map();
            const currentRows = Array.from(container.querySelectorAll('.fte-mobile-row'));

            currentRows.forEach((row) => {
                const idValue = getFteSerializedValue(row, 'id');
                if (idValue) {
                    idToRow.set(idValue, row);
                }
            });

            const orderedRows = orderedIds
                .map((idValue) => idToRow.get(String(idValue)))
                .filter(Boolean);

            const unsavedRows = Array.from(container.querySelectorAll('.fte-mobile-row'))
                .filter((row) =>
                    !getFteSerializedValue(row, 'id') &&
                    row.dataset.deleted !== '1' &&
                    row.dataset.remoteDeleted !== '1'
                );

            const activeQueue = [...orderedRows, ...unsavedRows];
            const nextRows = [];

            currentRows.forEach((row) => {
                if (row.dataset.deleted === '1' || row.dataset.remoteDeleted === '1') {
                    nextRows.push(row);
                    return;
                }

                const replacement = activeQueue.shift();
                if (replacement) {
                    nextRows.push(replacement);
                }
            });

            activeQueue.forEach((row) => nextRows.push(row));
            nextRows.forEach((row) => container.appendChild(row));
        }

        function setupFteRealtimeSync() {
            const container = getFteContainer();
            const form = document.getElementById('projectForm');
            const endpoint = @json(route('projects.fte.snapshot', $project));
            if (!container || !form || !endpoint) return;

            let pollInFlight = false;
            let pollTimer = null;
            let stopped = false;
            let hasCompletedInitialSync = false;
            const pollIntervalMs = 7000;

            const markDirty = (target) => {
                if (window.__fteRemoteApplying) return;
                const row = target?.closest?.('.fte-mobile-row');
                if (!row) return;
                row.dataset.remoteDirty = '1';
            };

            container.addEventListener('input', (event) => markDirty(event.target), true);
            container.addEventListener('change', (event) => markDirty(event.target), true);
            container.addEventListener('focusin', (event) => markDirty(event.target), true);

            container.querySelectorAll('.fte-mobile-row').forEach((row) => {
                row.dataset.remoteSignature = buildFteRowSignature(readFteRowData(row));
                row.dataset.remoteDirty = '0';
            });

            const schedulePoll = (delay = pollIntervalMs) => {
                if (stopped) return;
                window.clearTimeout(pollTimer);
                pollTimer = window.setTimeout(runPoll, delay);
            };

            const runPoll = async () => {
                if (stopped || pollInFlight) return;
                if (document.hidden) {
                    schedulePoll();
                    return;
                }

                pollInFlight = true;
                try {
                    const response = await fetch(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });

                    if (!response.ok) throw new Error(`FTE sync failed: ${response.status}`);
                    const payload = await response.json();
                    const rows = Array.isArray(payload.rows) ? payload.rows : [];
                    const projectActorName = String(payload.project_updated_by_name || '').trim();
                    const projectOccurredAt = String(payload.project_updated_at || '').trim();
                    const serverRows = rows.map((row) => ({
                        id: String(row.id ?? ''),
                        name: String(row.name ?? ''),
                        role_group: String(row.role_group ?? ''),
                        job_detail: String(row.job_detail ?? ''),
                        task_detail: String(row.task_detail ?? ''),
                        start_date: String(row.start_date ?? ''),
                        end_date: String(row.end_date ?? ''),
                        hours: String(row.hours ?? ''),
                        fte_value: String(row.fte_value ?? ''),
                        fte_value_research: String(row.fte_value_research ?? ''),
                        updated_by_name: String(row.updated_by_name ?? ''),
                        updated_logged_at: String(row.updated_logged_at ?? row.updated_at ?? ''),
                        updated_at: String(row.updated_at ?? ''),
                    })).filter((row) => row.id !== '');

                    const deletedIds = new Set(
                        Array.from(document.querySelectorAll('#fte-deleted-ids input[name="fte_deleted_ids[]"]'))
                            .map((input) => String(input.value || '').trim())
                            .filter(Boolean)
                    );
                    const existingById = new Map();
                    container.querySelectorAll('.fte-mobile-row').forEach((row) => {
                        const idValue = getFteSerializedValue(row, 'id');
                        if (idValue) existingById.set(idValue, row);
                    });

                    let hasChange = false;
                    const seenIds = new Set();
                    for (const rowData of serverRows) {
                        seenIds.add(rowData.id);
                        const remoteSignature = buildFteRowSignature(rowData);
                        const localRow = existingById.get(rowData.id);

                        if (localRow) {
                            if (isFteRowLockedForRemote(localRow)) continue;
                            if (localRow.dataset.remoteSignature === remoteSignature) {
                                localRow.dataset.remoteDirty = '0';
                                continue;
                            }

                            window.__fteRemoteApplying = true;
                            applyRemoteFteRowData(localRow, rowData);
                            window.__fteRemoteApplying = false;
                            if (hasCompletedInitialSync) {
                                flashRemoteFteRow(localRow, 'updated');
                            }
                            hasChange = true;
                            continue;
                        }

                        if (deletedIds.has(rowData.id) || getFteExistingIdSet().has(rowData.id)) {
                            continue;
                        }

                        window.__fteRemoteApplying = true;
                        const newRow = addFTERow({
                            skipValidation: true,
                            initialData: rowData,
                        });
                        window.__fteRemoteApplying = false;
                        if (newRow) {
                            newRow.dataset.remoteSignature = remoteSignature;
                            newRow.dataset.remoteDirty = '0';
                            if (hasCompletedInitialSync) {
                                flashRemoteFteRow(newRow, 'new');
                            }
                            hasChange = true;
                        }
                    }

                    Array.from(existingById.entries()).forEach(([idValue, row]) => {
                        if (seenIds.has(idValue) || deletedIds.has(idValue) || row.dataset.deleted === '1') {
                            return;
                        }
                        if (row.dataset.remoteDeleted === '1') {
                            return;
                        }
                        markMobileFteRowRemoteDeleted(row, {
                            actorName: projectActorName,
                            occurredAt: projectOccurredAt,
                        });
                        hasChange = true;
                    });

                    if (hasChange) {
                        reorderFteRowsByServerIds(container, serverRows.map((row) => row.id));
                        reindexFTE();
                        window.syncFteTableOrderFromSource?.();
                    }

                    updateFteSyncStatus(`ซิงก์ FTE ล่าสุด ${new Date().toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' })}`);
                    hasCompletedInitialSync = true;
                } catch (error) {
                    updateFteSyncStatus('ซิงก์ FTE อัตโนมัติไม่สำเร็จ กำลังลองใหม่');
                } finally {
                    window.__fteRemoteApplying = false;
                    pollInFlight = false;
                    schedulePoll();
                }
            };

            form.addEventListener('submit', () => {
                stopped = true;
                window.clearTimeout(pollTimer);
                updateFteSyncStatus('หยุดซิงก์ FTE ชั่วคราวระหว่างบันทึก');
            }, true);

            schedulePoll(pollIntervalMs);
        }

        document.addEventListener('DOMContentLoaded', setupFteRealtimeSync);


        /* =========================================================
           JS: Output + EA101 + Authors (index-safe, non-overwrite)
           ========================================================= */


        let outputIndex = document.querySelectorAll('#output-container .output-row').length || 0;

        // เก็บตัวนับ nested ต่อ output แยกกัน
        const authorIndex = {}; // authors ต่อ output
        const confIndex = {}; // presenters ต่อ output
        const awardRecipientIndex = {}; // award recipients ต่อ output
        const bookAuthorIndex = {}; // book_authors ต่อ output

        // ---------- Bootstrap counter จาก DOM ที่ render มาแล้ว ----------
        document.querySelectorAll('#output-container .output-row').forEach(row => {
            const idx = row.getAttribute('data-index');

            // authors
            const aWrap = row.querySelector('.authors-container');
            authorIndex[idx] = aWrap ? aWrap.querySelectorAll('.author-row').length : 0;

            // conferences
            const cWrap = row.querySelector('.conference-presenters-container');
            confIndex[idx] = cWrap ? cWrap.querySelectorAll('.conference-presenter-row').length : 0;
            // award recipients
            const rWrap = row.querySelector('.award-recipients-container');
            awardRecipientIndex[idx] = rWrap ? rWrap.querySelectorAll('.award-recipient-row').length : 0;
            // book authors
            const bWrap = row.querySelector('.book-authors-container');
            bookAuthorIndex[idx] = bWrap ? bWrap.querySelectorAll('.book-author-row').length : 0;
        });

        // ---------- Add Output ----------
        function addOutputRow() {
            const idx = outputIndex;
            const id = `output-${idx}`;

            document.getElementById('output-container').insertAdjacentHTML('beforeend', `
    <div id="${id}" class="output-row space-y-2 border p-4 rounded-lg bg-gray-50" data-index="${idx}">
      <input type="hidden" name="outputs[${idx}][id]" value="">
      <div class="flex items-center justify-between gap-3">
        <span class="font-semibold text-blue-700">ผลงานที่ <span class="output-index">${idx + 1}</span></span>
        <button type="button" onclick="removeRow('${id}','output')" class="text-red-600">✕</button>
      </div>
      <label class="block w-full">
        <span class="block text-sm text-gray-700 mb-1">
          ชื่อผลงาน <span class="text-red-500">*</span>
        </span>
        <input name="outputs[${idx}][title]" type="text" class="w-full border p-2 rounded" 
        required
        oninvalid="this.setCustomValidity('กรุณากรอกชื่อผลงาน')"
        oninput="this.setCustomValidity('')"
         placeholder="ระบุชื่อผลงาน">
      </label>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <label class="w-full">
          <span class="block text-sm text-gray-700 mb-1">ประเภทของผลงาน <span class="text-red-500">*</span></span>
          <select name="outputs[${idx}][type]" class="w-full border p-2 rounded output-type" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกประเภทของผลงาน')"
                                    oninput="this.setCustomValidity('')" onchange="handleTypeChange(this)">
            <option value="" disabled selected>เลือกประเภทของผลงาน</option>
            <option value="EA101 Research Paper or Journal Article">EA101 Research Paper or Journal Article</option>
            <option value="EA102 Reports and White papers">EA102 Reports and White papers</option>
            <option value="EA103 Presentations in Academic Conferences">EA103 Presentations in Academic Conferences</option>
            <option value="EA104 Data Sets and Databases">EA104 Data Sets and Databases</option>
            <option value="EA105 Inventions or Innovations (Application /Website)">EA105 Inventions or Innovations (Application /Website)</option>
            <option value="EA106 Policy Recommendations">EA106 Policy Recommendations</option>
            <option value="EA107 Educational Materials">EA107 Educational Materials</option>
            <option value="EA108 Communication Materials (Creations)">EA108 Communication Materials (Creations)</option>
            <option value="EA109 Change in Practices ,Procedures, Policy">EA109 Change in Practices ,Procedures, Policy</option>
            <option value="EA110 Number of participants (persons)">EA110 Number of participants (persons)</option>
            <option value="EA111 Satisfaction Rating (1-5)">EA111 Satisfaction Rating (1-5)</option>
            <option value="EA112 Present to Committee Meeting / Policy Maker / Board">EA112 Present to Committee Meeting / Policy Maker / Board</option>
            <option value="EA113 คำสั่งแต่งตั้ง Committees เน้นที่ SIHP เป็นกรรมการและหรือเลขา">EA113 คำสั่งแต่งตั้ง Committees เน้นที่ SIHP เป็นกรรมการและหรือเลขา</option>
            <option value="EA114 Events / Forum">EA114 Events / Forum</option>
            <option value="EA115 Capacity Building / Knowledge Sharing">EA115 Capacity Building / Knowledge Sharing</option>
            <option value="EA116 Research Project">EA116 Research Project</option>
            <option value="EA117 MOU">EA117 MOU</option>
            <option value="EA118 Award and Recognition">EA118 Award and Recognition</option>
            <option value="EA119 Textbook / Book chapter">EA119 Textbook / Book chapter</option>
            <option value="EA120 Manual / Instruction / Guideline">EA120 Manual / Instruction / Guideline</option>
        </select>
        </label>

        <label class="w-full">
          <span class="block text-sm text-gray-700 mb-1">รายละเอียด / หมายเหตุ / LINK</span>
          <input name="outputs[${idx}][project_output]" type="text" class="w-full border p-2 rounded" placeholder="เช่น ตีพิมพ์แล้ว / ลิงก์ / รายละเอียดเพิ่มเติม">
        </label>

        <label class="w-full relative">
          <span class="block text-sm text-gray-700 mb-1">วันที่ publish</span>
          <input name="outputs[${idx}][year]" type="text" class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="เลือกวันที่ publish">
          <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </label>
      </div>

      <!-- จุด mount สำหรับ section พิเศษ -->
      <div class="special-mount"></div>
    </div>
  `);

            // เริ่มนับ nested ใหม่เป็น 0
            authorIndex[idx] = 0;
            confIndex[idx] = 0;
            awardRecipientIndex[idx] = 0;

            outputIndex++;
            reindexOutput();
            keepAddedRowOpen(id);
            if (typeof initThaiDatepickers === 'function') initThaiDatepickers();
        }

        // ---------- เมื่อเปลี่ยนประเภท (ใช้ทั้ง onchange และ delegated change) ----------
        function handleTypeChange(selectEl) {
            const row = selectEl.closest('.output-row');
            const mount = row?.querySelector('.special-mount');
            if (!row || !mount) return;

            const idx = parseInt(row.getAttribute('data-index') || '0', 10);
            const val = selectEl.value || '';

            // ถ้ามี section ที่ render มาแล้ว ไม่ต้องล้าง
            if (val.startsWith('EA101')) {
                if (!mount.querySelector('.ea101-section')) {
                    mount.innerHTML = ''; // ล้างแค่ถ้าเราจะ render ใหม่
                    const section = renderEA101Section(idx);
                    mount.appendChild(section);

                    // เพิ่มแถวแรกเฉพาะตอน create ใหม่เท่านั้น
                    if (!row.querySelector('.author-row')) {
                        const addBtn = section.querySelector('.add-author-btn');
                        if (addBtn) addAuthorRow(addBtn);
                    }
                }
                authorIndex[idx] = row.querySelectorAll('.author-row').length;

            } else if (val.startsWith('EA103')) {
                if (!mount.querySelector('.ea103-section')) {
                    mount.innerHTML = '';
                    const section = renderEA103Section(idx);
                    mount.appendChild(section);

                    if (!row.querySelector('.conference-presenter-row')) {
                        const addBtn = section.querySelector('button[onclick*="addConferencePresenterRow"]');
                        if (addBtn) addConferencePresenterRow(addBtn);
                    }
                }
                confIndex[idx] = row.querySelectorAll('.conference-presenter-row').length;

            } else if (val.startsWith('EA118')) {
                if (!mount.querySelector('.ea118-section')) {
                    mount.innerHTML = '';
                    const section = renderEA118Section(idx);
                    mount.appendChild(section);
                }
                awardRecipientIndex[idx] = row.querySelectorAll('.award-recipient-row').length;

            } else if (val.startsWith('EA119')) {
                if (!mount.querySelector('.ea119-section')) {
                    mount.innerHTML = '';
                    const section = renderEA119Section(idx);
                    mount.appendChild(section);

                    if (!row.querySelector('.book-author-row')) {
                        const addBtn = section.querySelector('.add-book-author-btn');
                        if (addBtn) addBookAuthorRow(addBtn);
                    }
                }
                bookAuthorIndex[idx] = row.querySelectorAll('.book-author-row').length;

            } else {
                // ไม่ใช่ EA101/EA103/EA118/EA119 -> ล้างทิ้ง
                mount.innerHTML = '';
            }
        }


        /* ===== EA101: Authors ===== */
        function renderEA101Section(idx) {
            const wrap = document.createElement('div');
            wrap.className = 'ea101-section space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4';
            wrap.innerHTML = `
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <label>
        <span class="block text-sm text-gray-700 mb-1">Journal</span>
        <input name="outputs[${idx}][journal]" class="w-full border p-2 rounded" placeholder="เช่น IEEE Access">
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">Quartile (Q)</span>
        <select name="outputs[${idx}][quartile]" class="w-full border p-2 rounded">
          <option value="" selected>เลือก Quartile</option>
          <option value="Q1">Q1</option>
          <option value="Q2">Q2</option>
          <option value="Q3">Q3</option>
          <option value="Q4">Q4</option>
        </select>
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">Impact Factor (IF)</span>
        <input name="outputs[${idx}][impact_factor]" type="number" step="0.001" class="w-full border p-2 rounded" placeholder="เช่น 3.215">
      </label>
    </div>

    <div class="space-y-3">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <span class="font-semibold text-slate-800">รายชื่อผู้เขียน (Authors)</span>
      </div>
      <div class="authors-container space-y-3" id="authors-${idx}"></div>
      <div class="flex justify-end">
        <button type="button" class="lux-mobile-add-row-btn add-author-btn whitespace-nowrap px-3 py-2 rounded-lg bg-blue-600 text-sm font-semibold text-white" onclick="addAuthorRow(this)">+ เพิ่มผู้เขียน</button>
      </div>
    </div>
  `;
            return wrap;
        }

        function addAuthorRow(btnEl) {
            const outputRow = btnEl.closest('.output-row');
            const idx = outputRow.getAttribute('data-index');
            const authorsWrap = outputRow.querySelector(`#authors-${idx}`);
            if (!authorsWrap) return;

            const aIdx = authorIndex[idx] || 0;
            const userOptions = (Array.isArray(window.projectUserDirectory) ? window.projectUserDirectory : [])
                .map((user) => `<option value="${String(user.id)}">${String(user.research_display_name || '').replace(/"/g, '&quot;')}</option>`)
                .join('');
            const row = document.createElement('div');
            row.className = 'author-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3';
            row.innerHTML = `
    <div class="flex items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="author-index inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-blue-100 px-2 text-xs font-bold text-blue-700"></span>
        <span class="text-sm font-semibold text-slate-700">ผู้เขียน</span>
      </div>
      <button type="button" onclick="removeAuthorRow(this)" class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600">ลบ</button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      <label>
        <span class="block text-sm text-gray-700 mb-1">บทบาทผู้เขียน <span class="required-mark">*</span></span>
        <select name="outputs[${idx}][authors][${aIdx}][role]" class="w-full border p-2 rounded" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกบทบาทผู้เขียน')"
                                    oninput="this.setCustomValidity('')">
          <option value="" selected>เลือกบทบาท</option>
          <option value="first">First-author</option>
          <option value="co">Co-author</option>
          <option value="corresponding">Corresponding author</option>
        </select>
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก <span class="required-mark">*</span></span>
        <select name="outputs[${idx}][authors][${aIdx}][person_type]" data-output-author-person-type class="w-full border p-2 rounded" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกประเภทผู้เขียน')"
                                    oninput="this.setCustomValidity('')">
          <option value="">กรุณาเลือก</option>
          <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
          <option value="external">คนนอก</option>
        </select>
      </label>
    </div>
    <div class="space-y-2">
      <span class="block text-sm text-gray-700 mb-1">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ) <span class="required-mark">*</span></span>
      <input type="hidden" name="outputs[${idx}][authors][${aIdx}][user_id]" data-user-id-hidden>
      <input type="hidden" name="outputs[${idx}][authors][${aIdx}][name]" data-output-author-name-hidden>
      <div data-output-author-manual-wrap>
        <input type="text" list="outputAuthorNames" data-output-author-manual-name class="w-full border p-2 rounded" placeholder="เลือกประเภทผู้เขียนก่อน"
                                    oninvalid="this.setCustomValidity('กรุณากรอกชื่อผู้เขียน')"
                                    oninput="this.setCustomValidity('')">
      </div>
      <div data-output-author-user-wrap class="hidden" style="display:none;">
        <select data-output-author-user-select class="w-full border p-2 rounded">
          <option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>
          ${userOptions}
        </select>
      </div>
    </div>
  `;
            authorsWrap.appendChild(row);

            authorIndex[idx] = aIdx + 1;
            bindOutputAuthorRow(row);
            reindexAuthorRows(authorsWrap);
        }

        function removeAuthorRow(btnEl) {
            const row = btnEl.closest('.author-row');
            const container = row?.parentElement;
            confirmProjectMobileRemoval(() => {
                row?.remove();
                reindexAuthorRows(container);
            });
        }

        function renderEA118Section(idx) {
            const wrap = document.createElement('div');
            wrap.className = 'ea118-section space-y-3 rounded-xl border border-slate-200 bg-violet-50/60 p-4';
            wrap.innerHTML = `
    <div class="space-y-3">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <span class="font-semibold text-violet-800">ผู้ได้รับรางวัล (Award Recipients)</span>
      </div>
      <div class="award-recipients-container space-y-3" id="award-recipients-${idx}"></div>
      <div class="flex justify-center">
        <button type="button" class="lux-mobile-add-row-btn add-award-recipient-btn whitespace-nowrap px-3 py-2 rounded-lg bg-violet-600 text-sm font-semibold text-white" onclick="addAwardRecipientRow(this)">+ เพิ่มผู้ได้รับรางวัล</button>
      </div>
    </div>
  `;
            return wrap;
        }

        function addAwardRecipientRow(btnEl) {
            const outputRow = btnEl.closest('.output-row');
            const idx = outputRow.getAttribute('data-index');
            const recipientsWrap = outputRow.querySelector(`#award-recipients-${idx}`);
            if (!recipientsWrap) return;

            const aIdx = awardRecipientIndex[idx] || 0;
            const row = buildAwardRecipientRow(idx, aIdx);
            recipientsWrap.appendChild(row);
            awardRecipientIndex[idx] = aIdx + 1;
            bindAwardRecipientRow(row);
        }

        function removeAwardRecipientRow(btnEl) {
            const row = btnEl.closest('.award-recipient-row');
            confirmProjectMobileRemoval(() => {
                row?.remove();
            });
        }

        function buildAwardRecipientRow(outputIdx, authorIdx) {
            const row = document.createElement('div');
            row.className = 'award-recipient-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3';
            const userOptions = (Array.isArray(window.projectUserDirectory) ? window.projectUserDirectory : [])
                .map((user) => `<option value="${String(user.id)}">${String(user.full_name || '').replace(/"/g, '&quot;')}</option>`)
                .join('');
            row.innerHTML = `
    <input type="hidden" name="outputs[${outputIdx}][award_recipients][${authorIdx}][id]" value="">
    <label class="w-full">
      <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก / หน่วยงาน</span>
      <select name="outputs[${outputIdx}][award_recipients][${authorIdx}][person_type]" data-award-recipient-person-type class="w-full border p-2 rounded" required>
        <option value="">กรุณาเลือก</option>
        <option value="unit">หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
        <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
        <option value="external">คนนอก</option>
      </select>
    </label>
    <div>
      <span class="block text-sm text-gray-700 mb-1">ชื่อผู้ได้รับรางวัล</span>
      <input type="hidden" name="outputs[${outputIdx}][award_recipients][${authorIdx}][user_id]" data-user-id-hidden>
      <input type="hidden" name="outputs[${outputIdx}][award_recipients][${authorIdx}][name]" data-award-recipient-name-hidden>
      <div data-award-recipient-manual-wrap>
        <input type="text" list="awardRecipientNames" data-award-recipient-manual-name class="w-full border p-2 rounded" placeholder="เลือกประเภทผู้ได้รับรางวัลก่อน">
      </div>
      <div data-award-recipient-user-wrap class="hidden" style="display:none;">
        <select data-award-recipient-user-select class="w-full border p-2 rounded">
          <option value="">พิมพ์เพื่อค้นหาชื่อผู้ได้รับรางวัล</option>
          ${userOptions}
        </select>
      </div>
    </div>
    <div class="flex items-end">
      <button type="button" onclick="removeAwardRecipientRow(this)" class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600">ลบ</button>
    </div>
  `;
            return row;
        }

        /* ===== EA119: Textbook / Book chapter ===== */
        function renderEA119Section(idx) {
            const wrap = document.createElement('div');
            wrap.className = 'ea119-section space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4';
            wrap.innerHTML = `
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <label>
        <span class="block text-sm text-gray-700 mb-1">ชื่อหนังสือ</span>
        <input name="outputs[${idx}][book_title]" class="w-full border p-2 rounded" placeholder="เช่น Textbook of Public Health" required
          oninvalid="this.setCustomValidity('กรุณากรอกชื่อหนังสือ')" oninput="this.setCustomValidity('')">
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">ชื่อสำนักพิมพ์</span>
        <input name="outputs[${idx}][publisher_name]" class="w-full border p-2 rounded" placeholder="เช่น Elsevier" required
          oninvalid="this.setCustomValidity('กรุณากรอกชื่อสำนักพิมพ์')" oninput="this.setCustomValidity('')">
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">ชื่อบท / เลขหน้า</span>
        <input name="outputs[${idx}][chapter_pages]" class="w-full border p-2 rounded" placeholder="เช่น Chapter 2, pp. 45-66" required
          oninvalid="this.setCustomValidity('กรุณากรอกชื่อบท / เลขหน้า')" oninput="this.setCustomValidity('')">
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">เลข ISBN</span>
        <input name="outputs[${idx}][isbn]" class="w-full border p-2 rounded" placeholder="เช่น 978-3-16-148410-0">
      </label>
    </div>

    <div class="space-y-3">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <span class="font-semibold text-slate-800">รายชื่อผู้เขียน</span>
      </div>
      <div class="book-authors-container space-y-3" id="book-authors-${idx}"></div>
      <datalist class="book-author-name-list" id="book-author-name-list-${idx}"></datalist>
      <div class="flex justify-center">
        <button type="button" class="lux-mobile-add-row-btn add-book-author-btn whitespace-nowrap px-3 py-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white" onclick="addBookAuthorRow(this)">+ เพิ่มผู้เขียน</button>
      </div>
    </div>
  `;
            return wrap;
        }

        function addBookAuthorRow(btnEl) {
            const outputRow = btnEl.closest('.output-row');
            const idx = outputRow.getAttribute('data-index');
            const authorsWrap = outputRow.querySelector(`#book-authors-${idx}`);
            if (!authorsWrap) return;

            const aIdx = bookAuthorIndex[idx] || 0;
            const row = buildBookAuthorRow(idx, aIdx);
            authorsWrap.appendChild(row);
            bookAuthorIndex[idx] = aIdx + 1;
            bindBookAuthorRow(row);
            reindexBookAuthorRows(authorsWrap);
            refreshBookAuthorDatalistInOutput(outputRow);
        }

        function removeBookAuthorRow(btnEl) {
            const row = btnEl.closest('.book-author-row');
            const container = row?.parentElement;
            confirmProjectMobileRemoval(() => {
                row?.remove();
                reindexBookAuthorRows(container);
                refreshBookAuthorDatalistInOutput(container);
            });
        }

        function buildBookAuthorRow(outputIdx, authorIdx) {
            const row = document.createElement('div');
            row.className = 'book-author-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3';
            const userOptions = (Array.isArray(window.projectUserDirectory) ? window.projectUserDirectory : [])
                .map((user) => `<option value="${String(user.id)}">${String(user.full_name || '').replace(/"/g, '&quot;')}</option>`)
                .join('');
            row.innerHTML = `
    <div class="flex items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="book-author-index inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-emerald-100 px-2 text-xs font-bold text-emerald-700"></span>
        <span class="text-sm font-semibold text-slate-700">ผู้เขียน</span>
      </div>
      <button type="button" onclick="removeBookAuthorRow(this)" class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600">ลบ</button>
    </div>
    <label>
      <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก</span>
      <select name="outputs[${outputIdx}][book_authors][${authorIdx}][person_type]" data-book-author-person-type class="w-full border p-2 rounded" required>
        <option value="">กรุณาเลือก</option>
        <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
        <option value="external">คนนอก</option>
      </select>
    </label>
    <label>
      <span class="block text-sm text-gray-700 mb-1">ชื่อผู้เขียน</span>
      <input type="hidden" name="outputs[${outputIdx}][book_authors][${authorIdx}][user_id]" data-user-id-hidden>
      <input type="hidden" name="outputs[${outputIdx}][book_authors][${authorIdx}][name]" data-book-author-name-hidden>
      <div data-book-author-manual-wrap>
        <input type="text" data-book-author-input data-book-author-manual-name list="book-author-name-list-${outputIdx}" class="w-full border p-2 rounded" placeholder="เลือกประเภทผู้เขียนก่อน" onfocus="refreshBookAuthorDatalistInOutput(this)" oninput="refreshBookAuthorDatalistInOutput(this)">
      </div>
      <div data-book-author-user-wrap class="hidden" style="display:none;">
        <select data-book-author-user-select class="w-full border p-2 rounded">
          <option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>
          ${userOptions}
        </select>
      </div>
    </label>
  `;
            return row;
        }

        function getBookAuthorBaseOptions() {
            if (Array.isArray(window.__bookAuthorBaseOptions) && window.__bookAuthorBaseOptions.length) {
                return window.__bookAuthorBaseOptions;
            }
            const base = Array.from(document.querySelectorAll('#bookAuthorNames option'))
                .map((option) => String(option.value || '').trim())
                .filter(Boolean);
            window.__bookAuthorBaseOptions = Array.from(new Set(base));
            return window.__bookAuthorBaseOptions;
        }

        function findProjectMatchedUser(input) {
            const value = String(input?.value || '').trim();
            const mode = input?.dataset?.userLookup || 'full';
            const users = Array.isArray(window.projectUserDirectory) ? window.projectUserDirectory : [];
            if (!value) return null;
            return users.find((user) => mode === 'research'
                ? String(user.research_display_name || '').trim() === value
                : String(user.full_name || '').trim() === value) || null;
        }

        function syncProjectUserLookupInput(input) {
            const hidden = input?.closest('label, .author-row, .award-recipient-row')?.querySelector('[data-user-id-hidden]');
            if (!hidden) return;
            const matched = findProjectMatchedUser(input);
            hidden.value = matched ? String(matched.id) : '';
        }

        function bindProjectUserLookup(scope = document) {
            scope.querySelectorAll('input[data-user-lookup]').forEach((input) => {
                if (!input.dataset.userLookup) input.dataset.userLookup = 'full';
                if (input.dataset.userLookupBound === '1') {
                    syncProjectUserLookupInput(input);
                    return;
                }
                input.dataset.userLookupBound = '1';
                ['input', 'change', 'blur'].forEach((eventName) => {
                    input.addEventListener(eventName, () => syncProjectUserLookupInput(input));
                });
                syncProjectUserLookupInput(input);
            });
        }

        function ensureOutputFullNameTomSelect(select, placeholder) {
            if (!(select instanceof HTMLSelectElement)) return;
            const tom = window.ensureProjectSearchDropdown?.(select, {
                placeholder,
                searchPlaceholder: placeholder,
            });
            applySearchTomSelectStyles(tom);
        }

        function syncAwardRecipientRow(row) {
            if (!(row instanceof HTMLElement)) return;
            const UNIT_AWARD_RECIPIENT_NAME = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
            const typeSelect = row.querySelector('[data-award-recipient-person-type]');
            const manualWrap = row.querySelector('[data-award-recipient-manual-wrap]');
            const manualInput = row.querySelector('[data-award-recipient-manual-name]');
            const userWrap = row.querySelector('[data-award-recipient-user-wrap]');
            const userSelect = row.querySelector('[data-award-recipient-user-select]');
            const userIdInput = row.querySelector('[data-user-id-hidden]');
            const nameHidden = row.querySelector('[data-award-recipient-name-hidden]');
            if (!(typeSelect instanceof HTMLSelectElement) || !(manualWrap instanceof HTMLElement) || !(manualInput instanceof HTMLInputElement) || !(userWrap instanceof HTMLElement) || !(userSelect instanceof HTMLSelectElement) || !(userIdInput instanceof HTMLInputElement) || !(nameHidden instanceof HTMLInputElement)) return;
            ensureOutputFullNameTomSelect(userSelect, 'พิมพ์เพื่อค้นหาชื่อผู้ได้รับรางวัล');
            const mode = String(typeSelect.value || '').trim();
            const previousMode = row.dataset.awardRecipientMode || '';
            const modeChanged = previousMode !== '' && previousMode !== mode;
            const tom = userSelect.tomselect || null;
            const matchedUser = Array.isArray(window.projectUserDirectory) ? window.projectUserDirectory.find((user) => String(user.full_name || '').trim() === String(nameHidden.value || manualInput.value || '').trim()) : null;
            if (mode === 'internal' && userIdInput.value && !String(userSelect.value || '').trim()) {
                userSelect.value = String(userIdInput.value).trim();
                if (tom) tom.setValue(String(userIdInput.value).trim(), true);
            }
            if (mode === 'internal' && !userSelect.value && matchedUser) {
                userSelect.value = String(matchedUser.id);
                if (tom) tom.setValue(String(matchedUser.id), true);
            }
            if (mode === 'unit') {
                if (modeChanged) {
                    if (tom) tom.clear(true);
                    else userSelect.value = '';
                    userIdInput.value = '';
                    manualInput.value = UNIT_AWARD_RECIPIENT_NAME;
                }
                setOutputAuthorWrapVisible(manualWrap, true);
                setOutputAuthorWrapVisible(userWrap, false);
                toggleOutputAuthorFieldState(manualInput, true, 'ชื่อหน่วยงานผู้ได้รับรางวัล');
                manualInput.required = true;
                userSelect.required = false;
                userSelect.disabled = true;
                userIdInput.value = '';
                manualInput.value = UNIT_AWARD_RECIPIENT_NAME;
                nameHidden.value = UNIT_AWARD_RECIPIENT_NAME;
                manualInput.disabled = true;
                manualInput.readOnly = true;
                if (tom) tom.disable();
                setTomSelectWrapperState(userSelect, false, true);
            } else if (mode === 'internal') {
                if (modeChanged) {
                    manualInput.value = '';
                    userIdInput.value = '';
                    nameHidden.value = '';
                    if (tom) tom.clear(true);
                    else userSelect.value = '';
                }
                const selectedOption = userSelect.options[userSelect.selectedIndex] || null;
                userIdInput.value = String(userSelect.value || '').trim();
                nameHidden.value = selectedOption && userIdInput.value ? String(selectedOption.textContent || '').trim() : '';
                setOutputAuthorWrapVisible(manualWrap, false);
                setOutputAuthorWrapVisible(userWrap, true);
                toggleOutputAuthorFieldState(manualInput, true, 'เลือกชื่อผู้ได้รับรางวัลจากรายชื่อบุคลากร');
                manualInput.disabled = true;
                manualInput.readOnly = true;
                manualInput.required = false;
                userSelect.required = true;
                userSelect.disabled = false;
                if (tom) tom.enable();
                setTomSelectWrapperState(userSelect, true, false);
            } else if (mode === 'external') {
                if (modeChanged) {
                    if (tom) tom.clear(true);
                    else userSelect.value = '';
                    userIdInput.value = '';
                    nameHidden.value = '';
                    manualInput.value = '';
                }
                setOutputAuthorWrapVisible(manualWrap, true);
                setOutputAuthorWrapVisible(userWrap, false);
                toggleOutputAuthorFieldState(manualInput, false, 'ชื่อผู้ได้รับรางวัล');
                manualInput.disabled = false;
                manualInput.readOnly = false;
                manualInput.required = true;
                userSelect.required = false;
                userSelect.disabled = true;
                userIdInput.value = '';
                nameHidden.value = String(manualInput.value || '').trim();
                if (tom) tom.disable();
                setTomSelectWrapperState(userSelect, false, true);
            } else {
                if (tom) {
                    tom.clear(true);
                    tom.disable();
                } else {
                    userSelect.value = '';
                }
                setOutputAuthorWrapVisible(manualWrap, true);
                setOutputAuthorWrapVisible(userWrap, false);
                toggleOutputAuthorFieldState(manualInput, true, 'เลือกประเภทผู้ได้รับรางวัลก่อน');
                manualInput.disabled = true;
                manualInput.readOnly = true;
                manualInput.required = false;
                userSelect.required = false;
                userSelect.disabled = true;
                userIdInput.value = '';
                nameHidden.value = '';
                manualInput.value = '';
                setTomSelectWrapperState(userSelect, false, true);
            }
            row.dataset.awardRecipientMode = mode;
        }

        function bindAwardRecipientRow(row) {
            if (!(row instanceof HTMLElement)) return;
            const typeSelect = row.querySelector('[data-award-recipient-person-type]');
            const manualInput = row.querySelector('[data-award-recipient-manual-name]');
            const userSelect = row.querySelector('[data-award-recipient-user-select]');
            if (!(typeSelect instanceof HTMLSelectElement) || !(manualInput instanceof HTMLInputElement) || !(userSelect instanceof HTMLSelectElement)) return;
            if (typeSelect.dataset.awardRecipientBound !== '1') {
                typeSelect.dataset.awardRecipientBound = '1';
                typeSelect.addEventListener('change', () => syncAwardRecipientRow(row));
            }
            if (manualInput.dataset.awardRecipientBound !== '1') {
                manualInput.dataset.awardRecipientBound = '1';
                ['input', 'change', 'blur'].forEach((eventName) => manualInput.addEventListener(eventName, () => syncAwardRecipientRow(row)));
            }
            if (userSelect.dataset.awardRecipientBound !== '1') {
                userSelect.dataset.awardRecipientBound = '1';
                ['change', 'blur'].forEach((eventName) => userSelect.addEventListener(eventName, () => syncAwardRecipientRow(row)));
            }
            syncAwardRecipientRow(row);
        }

        function initAwardRecipientRows(scope = document) {
            scope.querySelectorAll('.award-recipient-row').forEach((row) => bindAwardRecipientRow(row));
        }

        function syncBookAuthorRow(row) {
            if (!(row instanceof HTMLElement)) return;
            const typeSelect = row.querySelector('[data-book-author-person-type]');
            const manualWrap = row.querySelector('[data-book-author-manual-wrap]');
            const manualInput = row.querySelector('[data-book-author-manual-name]');
            const userWrap = row.querySelector('[data-book-author-user-wrap]');
            const userSelect = row.querySelector('[data-book-author-user-select]');
            const userIdInput = row.querySelector('[data-user-id-hidden]');
            const nameHidden = row.querySelector('[data-book-author-name-hidden]');
            if (!(typeSelect instanceof HTMLSelectElement) || !(manualWrap instanceof HTMLElement) || !(manualInput instanceof HTMLInputElement) || !(userWrap instanceof HTMLElement) || !(userSelect instanceof HTMLSelectElement) || !(userIdInput instanceof HTMLInputElement) || !(nameHidden instanceof HTMLInputElement)) return;
            ensureOutputFullNameTomSelect(userSelect, 'พิมพ์เพื่อค้นหาชื่อผู้เขียน');
            const mode = String(typeSelect.value || '').trim();
            const previousMode = row.dataset.bookAuthorMode || '';
            const modeChanged = previousMode !== '' && previousMode !== mode;
            const tom = userSelect.tomselect || null;
            const matchedUser = Array.isArray(window.projectUserDirectory) ? window.projectUserDirectory.find((user) => String(user.full_name || '').trim() === String(nameHidden.value || manualInput.value || '').trim()) : null;
            if (mode === 'internal' && userIdInput.value && !String(userSelect.value || '').trim()) {
                userSelect.value = String(userIdInput.value).trim();
                if (tom) tom.setValue(String(userIdInput.value).trim(), true);
            }
            if (mode === 'internal' && !userSelect.value && matchedUser) {
                userSelect.value = String(matchedUser.id);
                if (tom) tom.setValue(String(matchedUser.id), true);
            }
            if (mode === 'internal') {
                if (modeChanged) {
                    manualInput.value = '';
                    userIdInput.value = '';
                    nameHidden.value = '';
                    if (tom) tom.clear(true);
                    else userSelect.value = '';
                }
                const selectedOption = userSelect.options[userSelect.selectedIndex] || null;
                userIdInput.value = String(userSelect.value || '').trim();
                nameHidden.value = selectedOption && userIdInput.value ? String(selectedOption.textContent || '').trim() : '';
                setOutputAuthorWrapVisible(manualWrap, false);
                setOutputAuthorWrapVisible(userWrap, true);
                toggleOutputAuthorFieldState(manualInput, true, 'เลือกชื่อผู้เขียนจากรายชื่อบุคลากร');
                manualInput.required = false;
                userSelect.required = true;
                userSelect.disabled = false;
                if (tom) tom.enable();
                setTomSelectWrapperState(userSelect, true, false);
            } else if (mode === 'external') {
                if (modeChanged) {
                    if (tom) tom.clear(true);
                    else userSelect.value = '';
                    userIdInput.value = '';
                    nameHidden.value = '';
                    manualInput.value = '';
                }
                setOutputAuthorWrapVisible(manualWrap, true);
                setOutputAuthorWrapVisible(userWrap, false);
                toggleOutputAuthorFieldState(manualInput, false, 'ชื่อผู้เขียน');
                manualInput.required = true;
                userSelect.required = false;
                userSelect.disabled = true;
                userIdInput.value = '';
                nameHidden.value = String(manualInput.value || '').trim();
                if (tom) tom.disable();
                setTomSelectWrapperState(userSelect, false, true);
            } else {
                if (tom) {
                    tom.clear(true);
                    tom.disable();
                } else {
                    userSelect.value = '';
                }
                setOutputAuthorWrapVisible(manualWrap, true);
                setOutputAuthorWrapVisible(userWrap, false);
                toggleOutputAuthorFieldState(manualInput, true, 'เลือกประเภทผู้เขียนก่อน');
                manualInput.required = false;
                userSelect.required = false;
                userSelect.disabled = true;
                userIdInput.value = '';
                nameHidden.value = '';
                manualInput.value = '';
                setTomSelectWrapperState(userSelect, false, true);
            }
            refreshBookAuthorDatalistInOutput(row);
            row.dataset.bookAuthorMode = mode;
        }

        function bindBookAuthorRow(row) {
            if (!(row instanceof HTMLElement)) return;
            const typeSelect = row.querySelector('[data-book-author-person-type]');
            const manualInput = row.querySelector('[data-book-author-manual-name]');
            const userSelect = row.querySelector('[data-book-author-user-select]');
            if (!(typeSelect instanceof HTMLSelectElement) || !(manualInput instanceof HTMLInputElement) || !(userSelect instanceof HTMLSelectElement)) return;
            if (typeSelect.dataset.bookAuthorBound !== '1') {
                typeSelect.dataset.bookAuthorBound = '1';
                typeSelect.addEventListener('change', () => syncBookAuthorRow(row));
            }
            if (manualInput.dataset.bookAuthorBound !== '1') {
                manualInput.dataset.bookAuthorBound = '1';
                ['input', 'change', 'blur'].forEach((eventName) => manualInput.addEventListener(eventName, () => syncBookAuthorRow(row)));
            }
            if (userSelect.dataset.bookAuthorBound !== '1') {
                userSelect.dataset.bookAuthorBound = '1';
                ['change', 'blur'].forEach((eventName) => userSelect.addEventListener(eventName, () => syncBookAuthorRow(row)));
            }
            syncBookAuthorRow(row);
        }

        function initBookAuthorRows(scope = document) {
            scope.querySelectorAll('.book-author-row').forEach((row) => bindBookAuthorRow(row));
        }

        function bindProjectSearchSelectGuards(scope = document) {
            scope.querySelectorAll('.project-search-select').forEach((element) => {
                if (!(element instanceof HTMLElement) || element.dataset.guardBound === '1') return;
                element.dataset.guardBound = '1';
                ['mousedown', 'click'].forEach((eventName) => {
                    element.addEventListener(eventName, (event) => {
                        event.stopPropagation();
                    });
                });
            });
        }

        function applySearchTomSelectStyles(tom) {
            return tom;
        }

        function ensureOutputAuthorTomSelect(select) {
            if (!(select instanceof HTMLSelectElement)) return;
            const tom = window.ensureProjectSearchDropdown?.(select, {
                placeholder: 'พิมพ์เพื่อค้นหาชื่อผู้เขียน',
                searchPlaceholder: 'พิมพ์เพื่อค้นหาชื่อผู้เขียน',
            });
            applySearchTomSelectStyles(tom);
        }

        function setOutputAuthorWrapVisible(element, visible) {
            if (!(element instanceof HTMLElement)) return;
            element.classList.toggle('hidden', !visible);
            element.hidden = !visible;
            element.style.display = visible ? '' : 'none';
        }

        function toggleOutputAuthorFieldState(input, disabled, placeholder) {
            if (!(input instanceof HTMLElement)) return;
            if ('disabled' in input) input.disabled = disabled;
            input.classList.toggle('bg-slate-100', disabled);
            input.classList.toggle('text-slate-400', disabled);
            input.classList.toggle('cursor-not-allowed', disabled);
            if (typeof placeholder === 'string' && 'placeholder' in input) {
                input.placeholder = placeholder;
            }
        }

        function syncOutputAuthorRow(row) {
            if (!(row instanceof HTMLElement)) return;

            const typeSelect = row.querySelector('[data-output-author-person-type]');
            const manualWrap = row.querySelector('[data-output-author-manual-wrap]');
            const manualInput = row.querySelector('[data-output-author-manual-name]');
            const userWrap = row.querySelector('[data-output-author-user-wrap]');
            const userSelect = row.querySelector('[data-output-author-user-select]');
            const userIdInput = row.querySelector('[data-user-id-hidden]');
            const nameHidden = row.querySelector('[data-output-author-name-hidden]');

            if (!(typeSelect instanceof HTMLSelectElement) ||
                !(manualWrap instanceof HTMLElement) ||
                !(manualInput instanceof HTMLInputElement) ||
                !(userWrap instanceof HTMLElement) ||
                !(userSelect instanceof HTMLSelectElement) ||
                !(userIdInput instanceof HTMLInputElement) ||
                !(nameHidden instanceof HTMLInputElement)) {
                return;
            }

            ensureOutputAuthorTomSelect(userSelect);

            const mode = String(typeSelect.value || '').trim();
            const previousMode = row.dataset.outputAuthorMode || '';
            const tom = userSelect.tomselect || null;
            const modeChanged = previousMode !== '' && previousMode !== mode;
            const matchedUser = Array.isArray(window.projectUserDirectory)
                ? window.projectUserDirectory.find((user) => String(user.research_display_name || '').trim() === String(nameHidden.value || manualInput.value || '').trim())
                : null;

            if (mode === 'internal' && userIdInput.value && !String(userSelect.value || '').trim()) {
                userSelect.value = String(userIdInput.value).trim();
                if (tom) tom.setValue(String(userIdInput.value).trim(), true);
            }

            if (mode === 'internal' && !userSelect.value && matchedUser) {
                userSelect.value = String(matchedUser.id);
                if (tom) tom.setValue(String(matchedUser.id), true);
            }

            if (mode === 'internal') {
                if (modeChanged) {
                    manualInput.value = '';
                    userIdInput.value = '';
                    nameHidden.value = '';
                    if (tom) {
                        tom.clear(true);
                    } else {
                        userSelect.value = '';
                    }
                }

                const selectedOption = userSelect.options[userSelect.selectedIndex] || null;
                userIdInput.value = String(userSelect.value || '').trim();
                nameHidden.value = selectedOption && userIdInput.value
                    ? String(selectedOption.textContent || '').trim()
                    : '';

                setOutputAuthorWrapVisible(manualWrap, false);
                setOutputAuthorWrapVisible(userWrap, true);
                toggleOutputAuthorFieldState(manualInput, true, 'เลือกชื่อผู้เขียนจากรายชื่อบุคลากร');

                manualInput.required = false;
                userSelect.required = true;
                userSelect.disabled = false;

                if (tom) tom.enable();
                setTomSelectWrapperState(userSelect, true, false);
            } else if (mode === 'external') {
                if (modeChanged) {
                    if (tom) {
                        tom.clear(true);
                    } else {
                        userSelect.value = '';
                    }
                    userIdInput.value = '';
                    nameHidden.value = '';
                    manualInput.value = '';
                }

                setOutputAuthorWrapVisible(manualWrap, true);
                setOutputAuthorWrapVisible(userWrap, false);
                toggleOutputAuthorFieldState(manualInput, false, 'ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)');

                manualInput.required = true;
                userSelect.required = false;
                userSelect.disabled = true;
                userIdInput.value = '';
                nameHidden.value = String(manualInput.value || '').trim();

                if (tom) tom.disable();
                setTomSelectWrapperState(userSelect, false, true);
            } else {
                if (tom) {
                    tom.clear(true);
                    tom.disable();
                } else {
                    userSelect.value = '';
                }

                setOutputAuthorWrapVisible(manualWrap, true);
                setOutputAuthorWrapVisible(userWrap, false);
                toggleOutputAuthorFieldState(manualInput, true, 'เลือกประเภทผู้เขียนก่อน');

                manualInput.required = false;
                userSelect.required = false;
                userSelect.disabled = true;
                userIdInput.value = '';
                nameHidden.value = '';
                manualInput.value = '';

                setTomSelectWrapperState(userSelect, false, true);
            }

            row.dataset.outputAuthorMode = mode;
        }

        function bindOutputAuthorRow(row) {
            if (!(row instanceof HTMLElement)) return;

            const typeSelect = row.querySelector('[data-output-author-person-type]');
            const manualInput = row.querySelector('[data-output-author-manual-name]');
            const userSelect = row.querySelector('[data-output-author-user-select]');

            if (!(typeSelect instanceof HTMLSelectElement) ||
                !(manualInput instanceof HTMLInputElement) ||
                !(userSelect instanceof HTMLSelectElement)) {
                return;
            }

            if (typeSelect.dataset.outputAuthorBound !== '1') {
                typeSelect.dataset.outputAuthorBound = '1';
                typeSelect.addEventListener('change', () => syncOutputAuthorRow(row));
            }

            if (manualInput.dataset.outputAuthorBound !== '1') {
                manualInput.dataset.outputAuthorBound = '1';
                ['input', 'change', 'blur'].forEach((eventName) => {
                    manualInput.addEventListener(eventName, () => syncOutputAuthorRow(row));
                });
            }

            if (userSelect.dataset.outputAuthorBound !== '1') {
                userSelect.dataset.outputAuthorBound = '1';
                ['change', 'blur'].forEach((eventName) => {
                    userSelect.addEventListener(eventName, () => syncOutputAuthorRow(row));
                });
            }

            syncOutputAuthorRow(row);
        }

        function initOutputAuthorRows(scope = document) {
            scope.querySelectorAll('.author-row').forEach((row) => bindOutputAuthorRow(row));
        }

        const PI_INTERNAL_AFFILIATION = @json($piInternalAffiliation);
        const CORESEARCH_INTERNAL_UNIT = @json($internalCoresearchUnit);
        const CORESEARCH_INTERNAL_AFFILIATION = @json($internalCoresearchAffiliation);

        function ensurePiTomSelect(select) {
            if (!(select instanceof HTMLSelectElement)) return;
            const tom = window.ensureProjectSearchDropdown?.(select, {
                placeholder: 'พิมพ์เพื่อค้นหาชื่อบุคลากร',
                searchPlaceholder: 'พิมพ์เพื่อค้นหาชื่อบุคลากร',
            });
            applySearchTomSelectStyles(tom);
        }

        function togglePiFieldState(input, disabled, placeholder) {
            if (!(input instanceof HTMLElement)) return;
            if ('disabled' in input) input.disabled = disabled;
            input.classList.toggle('bg-gray-100', disabled);
            input.classList.toggle('text-gray-400', disabled);
            input.classList.toggle('cursor-not-allowed', disabled);
            if (typeof placeholder === 'string' && 'placeholder' in input) {
                input.placeholder = placeholder;
            }
        }

        function setPiWrapVisible(element, visible) {
            if (!(element instanceof HTMLElement)) return;
            element.classList.toggle('hidden', !visible);
            element.hidden = !visible;
            element.style.display = visible ? '' : 'none';
        }

        function setTomSelectWrapperState(select, visible, disabled) {
            const wrapper = select?.tomselect?.wrapper;
            if (!(wrapper instanceof HTMLElement)) return;
            wrapper.classList.toggle('hidden', !visible);
            wrapper.hidden = !visible;
            wrapper.style.display = visible ? '' : 'none';
            wrapper.classList.toggle('opacity-60', !!disabled);
            wrapper.classList.toggle('pointer-events-none', !!disabled);
        }

        function syncPiPersonFields(scope = document) {
            const typeSelect = scope.querySelector('[data-pi-person-type]');
            const affiliationInput = scope.querySelector('[data-pi-affiliation]');
            const manualWrap = scope.querySelector('[data-pi-manual-wrap]');
            const manualInput = scope.querySelector('[data-pi-manual-name]');
            const userWrap = scope.querySelector('[data-pi-user-wrap]');
            const userSelect = scope.querySelector('[data-pi-user-select]');
            const userIdInput = scope.querySelector('[data-pi-user-id]');
            const nameHidden = scope.querySelector('[data-pi-name-hidden]');

            if (!(typeSelect instanceof HTMLSelectElement) ||
                !(affiliationInput instanceof HTMLInputElement) ||
                !(manualWrap instanceof HTMLElement) ||
                !(manualInput instanceof HTMLInputElement) ||
                !(userWrap instanceof HTMLElement) ||
                !(userSelect instanceof HTMLSelectElement) ||
                !(userIdInput instanceof HTMLInputElement) ||
                !(nameHidden instanceof HTMLInputElement)) {
                return;
            }

            ensurePiTomSelect(userSelect);

            const mode = String(typeSelect.value || '').trim();
            const previousMode = typeSelect.dataset.piMode || '';
            const tom = userSelect.tomselect || null;
            const modeChanged = previousMode !== '' && previousMode !== mode;

            if (mode === 'internal' && userIdInput.value && !String(userSelect.value || '').trim()) {
                userSelect.value = String(userIdInput.value).trim();
                if (tom) tom.setValue(String(userIdInput.value).trim(), true);
            }

            if (mode === 'internal') {
                if (modeChanged) {
                    manualInput.value = '';
                    userIdInput.value = '';
                    nameHidden.value = '';
                    if (tom) {
                        tom.clear(true);
                    } else {
                        userSelect.value = '';
                    }
                }

                const selectedOption = userSelect.options[userSelect.selectedIndex] || null;
                userIdInput.value = String(userSelect.value || '').trim();
                nameHidden.value = selectedOption && userIdInput.value
                    ? String(selectedOption.textContent || '').trim()
                    : '';

                setPiWrapVisible(manualWrap, false);
                setPiWrapVisible(userWrap, true);
                togglePiFieldState(manualInput, true, 'เลือก PI จากรายชื่อบุคลากร');
                togglePiFieldState(affiliationInput, false, PI_INTERNAL_AFFILIATION);

                manualInput.required = false;
                userSelect.required = true;
                userSelect.disabled = false;
                affiliationInput.required = false;
                affiliationInput.readOnly = true;
                affiliationInput.value = PI_INTERNAL_AFFILIATION;
                affiliationInput.removeAttribute('list');

                if (tom) tom.enable();
                setTomSelectWrapperState(userSelect, true, false);
            } else if (mode === 'external') {
                if (modeChanged) {
                    if (tom) {
                        tom.clear(true);
                    } else {
                        userSelect.value = '';
                    }
                    userIdInput.value = '';
                    nameHidden.value = '';
                    manualInput.value = '';
                    affiliationInput.value = '';
                }

                setPiWrapVisible(manualWrap, true);
                setPiWrapVisible(userWrap, false);
                togglePiFieldState(manualInput, false, 'ยศ ชื่อ นามสกุล');
                togglePiFieldState(affiliationInput, false, 'สังกัดผู้วิจัยหลัก');

                manualInput.required = true;
                userSelect.required = false;
                userSelect.disabled = true;
                affiliationInput.required = true;
                affiliationInput.readOnly = false;
                affiliationInput.setAttribute('list', 'pi_affiliation_list');

                userIdInput.value = '';
                nameHidden.value = String(manualInput.value || '').trim();

                if (tom) tom.disable();
                setTomSelectWrapperState(userSelect, false, true);
            } else {
                if (tom) {
                    tom.clear(true);
                    tom.disable();
                } else {
                    userSelect.value = '';
                }

                setPiWrapVisible(manualWrap, true);
                setPiWrapVisible(userWrap, false);
                togglePiFieldState(manualInput, true, 'เลือกประเภทผู้วิจัยหลักก่อน');
                togglePiFieldState(affiliationInput, true, 'เลือกประเภทผู้วิจัยหลักก่อน');

                manualInput.required = false;
                userSelect.required = false;
                userSelect.disabled = true;
                affiliationInput.required = false;
                affiliationInput.readOnly = false;
                affiliationInput.setAttribute('list', 'pi_affiliation_list');

                userIdInput.value = '';
                nameHidden.value = '';
                manualInput.value = '';
                affiliationInput.value = '';

                setTomSelectWrapperState(userSelect, false, true);
            }

            typeSelect.dataset.piMode = mode;
        }

        function bindPiPersonControl(scope = document) {
            const typeSelect = scope.querySelector('[data-pi-person-type]');
            const manualInput = scope.querySelector('[data-pi-manual-name]');
            const userSelect = scope.querySelector('[data-pi-user-select]');

            if (!(typeSelect instanceof HTMLSelectElement) ||
                !(manualInput instanceof HTMLInputElement) ||
                !(userSelect instanceof HTMLSelectElement)) {
                return;
            }

            if (typeSelect.dataset.piBound !== '1') {
                typeSelect.dataset.piBound = '1';
                typeSelect.addEventListener('change', () => syncPiPersonFields(scope));
            }

            if (manualInput.dataset.piBound !== '1') {
                manualInput.dataset.piBound = '1';
                ['input', 'change', 'blur'].forEach((eventName) => {
                    manualInput.addEventListener(eventName, () => syncPiPersonFields(scope));
                });
            }

            if (userSelect.dataset.piBound !== '1') {
                userSelect.dataset.piBound = '1';
                ['change', 'blur'].forEach((eventName) => {
                    userSelect.addEventListener(eventName, () => syncPiPersonFields(scope));
                });
            }

            syncPiPersonFields(scope);
        }

        function ensureCopiTomSelect(select) {
            if (!(select instanceof HTMLSelectElement)) return;
            const tom = window.ensureProjectSearchDropdown?.(select, {
                placeholder: 'พิมพ์เพื่อค้นหาชื่อบุคลากร',
                searchPlaceholder: 'พิมพ์เพื่อค้นหาชื่อบุคลากร',
            });
            applySearchTomSelectStyles(tom);
        }

        function setCopiWrapVisible(element, visible) {
            if (!(element instanceof HTMLElement)) return;
            element.classList.toggle('hidden', !visible);
            element.hidden = !visible;
            element.style.display = visible ? '' : 'none';
        }

        function toggleCopiFieldState(input, disabled, placeholder) {
            if (!(input instanceof HTMLElement)) return;
            if ('disabled' in input) input.disabled = disabled;
            input.classList.toggle('bg-slate-100', disabled);
            input.classList.toggle('text-slate-400', disabled);
            input.classList.toggle('cursor-not-allowed', disabled);
            if (typeof placeholder === 'string' && 'placeholder' in input) {
                input.placeholder = placeholder;
            }
        }

        function syncCopiRow(row) {
            if (!(row instanceof HTMLElement)) return;

            const typeSelect = row.querySelector('[data-copi-person-type]');
            const affiliationInput = row.querySelector('[data-copi-affiliation]');
            const manualWrap = row.querySelector('[data-copi-manual-wrap]');
            const manualInput = row.querySelector('[data-copi-manual-name]');
            const userWrap = row.querySelector('[data-copi-user-wrap]');
            const userSelect = row.querySelector('[data-copi-user-select]');
            const userIdInput = row.querySelector('[data-copi-user-id]');
            const nameHidden = row.querySelector('[data-copi-name]');

            if (!(typeSelect instanceof HTMLSelectElement) ||
                !(affiliationInput instanceof HTMLInputElement) ||
                !(manualWrap instanceof HTMLElement) ||
                !(manualInput instanceof HTMLInputElement) ||
                !(userWrap instanceof HTMLElement) ||
                !(userSelect instanceof HTMLSelectElement) ||
                !(userIdInput instanceof HTMLInputElement) ||
                !(nameHidden instanceof HTMLInputElement)) {
                return;
            }

            ensureCopiTomSelect(userSelect);

            const mode = String(typeSelect.value || '').trim();
            const previousMode = row.dataset.copiMode || '';
            const tom = userSelect.tomselect || null;
            const modeChanged = previousMode !== '' && previousMode !== mode;
            const matchedUser = Array.isArray(window.projectUserDirectory)
                ? window.projectUserDirectory.find((user) => String(user.full_name || '').trim() === String(nameHidden.value || manualInput.value || '').trim())
                : null;

            if (mode === 'internal' && userIdInput.value && String(userSelect.value || '').trim() !== String(userIdInput.value).trim()) {
                userSelect.value = String(userIdInput.value).trim();
                if (tom) tom.setValue(String(userIdInput.value).trim(), true);
            }

            if (mode === 'internal' && !userSelect.value && matchedUser) {
                userSelect.value = String(matchedUser.id);
                if (tom) tom.setValue(String(matchedUser.id), true);
            }

            if (mode === 'internal') {
                if (modeChanged) {
                    manualInput.value = '';
                    userIdInput.value = '';
                    nameHidden.value = '';
                    if (tom) {
                        tom.clear(true);
                    } else {
                        userSelect.value = '';
                    }
                }

                const selectedOption = userSelect.options[userSelect.selectedIndex] || null;
                userIdInput.value = String(userSelect.value || '').trim();
                nameHidden.value = selectedOption && userIdInput.value
                    ? String(selectedOption.textContent || '').trim()
                    : '';

                setCopiWrapVisible(manualWrap, false);
                setCopiWrapVisible(userWrap, true);
                toggleCopiFieldState(manualInput, true, 'เลือก COPI จากรายชื่อบุคลากร');
                toggleCopiFieldState(affiliationInput, false, PI_INTERNAL_AFFILIATION);

                manualInput.required = false;
                userSelect.required = true;
                userSelect.disabled = false;
                affiliationInput.required = false;
                affiliationInput.readOnly = true;
                affiliationInput.value = PI_INTERNAL_AFFILIATION;
                affiliationInput.removeAttribute('list');

                if (tom) tom.enable();
                setTomSelectWrapperState(userSelect, true, false);
            } else if (mode === 'external') {
                if (modeChanged) {
                    if (tom) {
                        tom.clear(true);
                    } else {
                        userSelect.value = '';
                    }
                    userIdInput.value = '';
                    nameHidden.value = '';
                    manualInput.value = '';
                    affiliationInput.value = '';
                }

                setCopiWrapVisible(manualWrap, true);
                setCopiWrapVisible(userWrap, false);
                toggleCopiFieldState(manualInput, false, 'ยศ ชื่อ นามสกุล');
                toggleCopiFieldState(affiliationInput, false, 'สังกัด COPI');

                manualInput.required = true;
                userSelect.required = false;
                userSelect.disabled = true;
                affiliationInput.required = true;
                affiliationInput.readOnly = false;
                affiliationInput.setAttribute('list', 'copiAffiliationList');

                userIdInput.value = '';
                nameHidden.value = String(manualInput.value || '').trim();

                if (tom) tom.disable();
                setTomSelectWrapperState(userSelect, false, true);
            } else {
                if (tom) {
                    tom.clear(true);
                    tom.disable();
                } else {
                    userSelect.value = '';
                }

                setCopiWrapVisible(manualWrap, true);
                setCopiWrapVisible(userWrap, false);
                toggleCopiFieldState(manualInput, true, 'เลือกประเภท COPI ก่อน');
                toggleCopiFieldState(affiliationInput, true, 'เลือกประเภท COPI ก่อน');

                manualInput.required = false;
                userSelect.required = false;
                userSelect.disabled = true;
                affiliationInput.required = false;
                affiliationInput.readOnly = false;
                affiliationInput.setAttribute('list', 'copiAffiliationList');

                userIdInput.value = '';
                nameHidden.value = '';
                manualInput.value = '';
                affiliationInput.value = '';

                setTomSelectWrapperState(userSelect, false, true);
            }

            row.dataset.copiMode = mode;
        }

        function bindCopiRow(row) {
            if (!(row instanceof HTMLElement)) return;

            const typeSelect = row.querySelector('[data-copi-person-type]');
            const manualInput = row.querySelector('[data-copi-manual-name]');
            const userSelect = row.querySelector('[data-copi-user-select]');

            if (!(typeSelect instanceof HTMLSelectElement) ||
                !(manualInput instanceof HTMLInputElement) ||
                !(userSelect instanceof HTMLSelectElement)) {
                return;
            }

            if (typeSelect.dataset.copiBound !== '1') {
                typeSelect.dataset.copiBound = '1';
                typeSelect.addEventListener('change', () => syncCopiRow(row));
            }

            if (manualInput.dataset.copiBound !== '1') {
                manualInput.dataset.copiBound = '1';
                ['input', 'change', 'blur'].forEach((eventName) => {
                    manualInput.addEventListener(eventName, () => syncCopiRow(row));
                });
            }

            if (userSelect.dataset.copiBound !== '1') {
                userSelect.dataset.copiBound = '1';
                ['change', 'blur'].forEach((eventName) => {
                    userSelect.addEventListener(eventName, () => syncCopiRow(row));
                });
            }

            syncCopiRow(row);
        }

        function initCopiRows(scope = document) {
            scope.querySelectorAll('.copi-row').forEach((row) => bindCopiRow(row));

            const container = scope.querySelector('#copi-container');
            if (!(container instanceof HTMLElement) || container.dataset.copiObserverBound === '1') {
                return;
            }

            container.dataset.copiObserverBound = '1';
            new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node instanceof HTMLElement && node.matches('.copi-row')) {
                            bindCopiRow(node);
                        }
                    });
                });
            }).observe(container, { childList: true });
        }

        document.addEventListener('DOMContentLoaded', () => {
            bindProjectUserLookup(document);
            initOutputAuthorRows(document);
            initAwardRecipientRows(document);
            initBookAuthorRows(document);
            initAwardRecipientRows(document);
            initBookAuthorRows(document);
            bindPiPersonControl(document);
            initCopiRows(document);
            initCoresearchRows(document);
        });

        function ensureCoresearchTomSelect(select) {
            if (!(select instanceof HTMLSelectElement)) return;
            const tom = window.ensureProjectSearchDropdown?.(select, {
                placeholder: 'พิมพ์เพื่อค้นหาชื่อบุคลากร',
                searchPlaceholder: 'พิมพ์เพื่อค้นหาชื่อบุคลากร',
            });
            applySearchTomSelectStyles(tom);
        }

        function syncCoresearchRow(row) {
            if (!(row instanceof HTMLElement)) return;

            const statusSelect = row.querySelector('[data-coresearch-unit-status]');
            const nameHidden = row.querySelector('[data-coresearch-name]');
            const manualWrap = row.querySelector('[data-coresearch-manual-wrap]');
            const manualInput = row.querySelector('[data-coresearch-manual-name]');
            const userWrap = row.querySelector('[data-coresearch-user-wrap]');
            const userSelect = row.querySelector('[data-coresearch-user-select]');
            const affiliationInput = row.querySelector('[data-coresearch-affiliation]');

            if (!(statusSelect instanceof HTMLSelectElement) || !(nameHidden instanceof HTMLInputElement) || !(manualInput instanceof HTMLInputElement) || !(userSelect instanceof HTMLSelectElement) || !(affiliationInput instanceof HTMLInputElement)) {
                return;
            }

            const isInternal = String(statusSelect.value || '').trim() === CORESEARCH_INTERNAL_UNIT;
            const matchedUser = Array.isArray(window.projectUserDirectory)
                ? window.projectUserDirectory.find((user) => String(user.full_name || '').trim() === String(nameHidden.value || manualInput.value || '').trim())
                : null;

            if (isInternal && !String(userSelect.value || '').trim() && matchedUser) {
                userSelect.value = String(matchedUser.id);
            }

            const hasStatus = !!String(statusSelect.value || '').trim();
            const wasInternal = row.dataset.coresearchMode === 'internal';

            const setWrapVisibility = (wrap, visible) => {
                if (!(wrap instanceof HTMLElement)) return;
                wrap.classList.toggle('hidden', !visible);
                wrap.hidden = !visible;
                wrap.style.display = visible ? '' : 'none';
            };

            setWrapVisibility(manualWrap, !isInternal);
            setWrapVisibility(userWrap, isInternal);
            manualInput.required = !isInternal && hasStatus;
            manualInput.disabled = isInternal || !hasStatus;
            userSelect.disabled = !isInternal;
            userSelect.required = isInternal;
            affiliationInput.required = hasStatus && !isInternal;
            affiliationInput.disabled = !hasStatus;

            const setDisabledStyle = (field, disabled, placeholderWhenDisabled = 'เลือกคนภายนอก / ภายในก่อน', placeholderWhenEnabled = null) => {
                if (!field) return;
                field.classList.toggle('bg-slate-100', disabled);
                field.classList.toggle('text-slate-400', disabled);
                field.classList.toggle('cursor-not-allowed', disabled);
                if ('placeholder' in field) {
                    field.placeholder = disabled ? placeholderWhenDisabled : (placeholderWhenEnabled ?? field.placeholder);
                }
            };

            if (isInternal) {
                ensureCoresearchTomSelect(userSelect);
                const selectedOption = userSelect.options[userSelect.selectedIndex] ?? null;
                nameHidden.value = selectedOption && userSelect.value ? String(selectedOption.text || '').trim() : '';
                affiliationInput.value = CORESEARCH_INTERNAL_AFFILIATION;
                affiliationInput.readOnly = true;
                affiliationInput.required = false;
                setDisabledStyle(affiliationInput, false, 'เลือกคนภายนอก / ภายในก่อน', CORESEARCH_INTERNAL_AFFILIATION);
                setDisabledStyle(manualInput, true);
                if (userSelect.tomselect?.wrapper) {
                    userSelect.tomselect.wrapper.classList.remove('opacity-60', 'pointer-events-none');
                    userSelect.tomselect.wrapper.style.display = '';
                }
            } else {
                if (wasInternal) {
                    nameHidden.value = '';
                    manualInput.value = '';
                    if (affiliationInput.value === CORESEARCH_INTERNAL_AFFILIATION) {
                        affiliationInput.value = '';
                    }
                }
                userSelect.required = false;
                userSelect.disabled = true;
                if (userSelect.tomselect) {
                    userSelect.tomselect.clear(true);
                } else {
                    userSelect.value = '';
                }
                nameHidden.value = manualInput.value;
                affiliationInput.readOnly = false;
                setDisabledStyle(manualInput, !hasStatus, 'เลือกคนภายนอก / ภายในก่อน', 'ยศ ชื่อ-นามสกุล');
                setDisabledStyle(affiliationInput, !hasStatus, 'เลือกคนภายนอก / ภายในก่อน', 'สังกัด');
                if (userSelect.tomselect?.wrapper) {
                    userSelect.tomselect.wrapper.classList.add('opacity-60', 'pointer-events-none');
                    userSelect.tomselect.wrapper.style.display = 'none';
                }
                if (!hasStatus) {
                    nameHidden.value = '';
                    manualInput.value = '';
                    affiliationInput.value = '';
                }
            }

            row.dataset.coresearchMode = isInternal ? 'internal' : 'manual';
        }

        function bindCoresearchRow(row) {
            if (!(row instanceof HTMLElement) || row.dataset.coresearchBound === '1') return;

            row.dataset.coresearchBound = '1';
            row.querySelector('[data-coresearch-unit-status]')?.addEventListener('change', () => syncCoresearchRow(row));
            row.querySelector('[data-coresearch-manual-name]')?.addEventListener('input', () => syncCoresearchRow(row));
            row.querySelector('[data-coresearch-user-select]')?.addEventListener('change', () => syncCoresearchRow(row));

            if (row.querySelector('[data-coresearch-user-select]')?.tomselect?.wrapper) {
                row.querySelector('[data-coresearch-user-select]').tomselect.wrapper.style.display = 'none';
            }
            syncCoresearchRow(row);
        }

        function initCoresearchRows(scope = document) {
            scope.querySelectorAll('.coresearch-row').forEach((row) => bindCoresearchRow(row));
        }

        function refreshBookAuthorDatalistInOutput(sourceEl) {
            if (!(sourceEl instanceof Element)) return;
            const section = sourceEl.classList.contains('ea119-section') ? sourceEl : sourceEl.closest('.ea119-section');
            if (!section) return;

            const list = section.querySelector('.book-author-name-list');
            if (!(list instanceof HTMLDataListElement)) return;

            const inputs = Array.from(section.querySelectorAll('input[data-book-author-manual-name]'));
            const activeInput = sourceEl.matches('input[data-book-author-manual-name]') ? sourceEl : null;
            const selected = new Set(
                inputs
                    .map((input) => String(input.value || '').trim())
                    .filter(Boolean)
                    .filter((value) => value !== String(activeInput?.value || '').trim())
            );

            const options = getBookAuthorBaseOptions()
                .filter((name) => !selected.has(name))
                .map((name) => `<option value="${name.replace(/"/g, '&quot;')}"></option>`)
                .join('');

            list.innerHTML = options;
        }

        function normalizeAuthorNameForDuplicateCheck(name) {
            return String(name || '')
                .replace(/\s+/g, ' ')
                .trim()
                .replace(/^(คุณ|นาย|นางสาว|นาง|น\.ส\.|ดร\.?|ผศ\.?|รศ\.?|ศ\.?)\s+/u, '')
                .toLowerCase();
        }

        function validateEA119DuplicateAuthors(form) {
            const result = {
                valid: true,
                field: null,
                message: '',
                rows: []
            };

            form.querySelectorAll('.ea119-section input[data-book-author-manual-name], .ea119-section select[data-book-author-user-select]').forEach((el) => el.classList.remove('border-red-500', 'bg-red-50'));

            form.querySelectorAll('.output-row').forEach((row) => {
                const typeField = row.querySelector('select[name^="outputs["][name$="[type]"]') || row.querySelector('input[name^="outputs["][name$="[type]"]');
                const typeValue = String(typeField?.value || '').trim();
                if (!typeValue.startsWith('EA119')) return;

                const seen = new Map();
                const rows = Array.from(row.querySelectorAll('.ea119-section .book-author-row'));
                rows.forEach((bookAuthorRow) => {
                    const hiddenInput = bookAuthorRow.querySelector('[data-book-author-name-hidden]');
                    const manualInput = bookAuthorRow.querySelector('[data-book-author-manual-name]');
                    const userSelect = bookAuthorRow.querySelector('[data-book-author-user-select]');
                    const displayTarget = window.getProjectValidationDisplayTarget?.(userSelect?.disabled ? manualInput : userSelect) || (userSelect?.disabled ? manualInput : userSelect);
                    const raw = String(hiddenInput?.value || manualInput?.value || '').trim();
                    if (!raw) return;

                    const key = normalizeAuthorNameForDuplicateCheck(raw);
                    if (!key) return;

                    if (seen.has(key)) {
                        const firstInput = seen.get(key);
                        firstInput?.classList.add('border-red-500', 'bg-red-50');
                        displayTarget?.classList.add('border-red-500', 'bg-red-50');
                        if (result.valid) {
                            result.valid = false;
                            result.field = displayTarget;
                            result.message = 'พบชื่อผู้เขียนซ้ำ กรุณาเลือกชื่อที่ไม่ซ้ำ';
                            result.rows.push(row);
                        }
                        return;
                    }

                    seen.set(key, displayTarget);
                });
            });

            return result;
        }

        function ensureConferencePresenterTomSelect(select) {
            if (!(select instanceof HTMLSelectElement)) return;
            const tom = window.ensureProjectSearchDropdown?.(select, {
                placeholder: 'พิมพ์เพื่อค้นหาชื่อผู้นำเสนอ',
                searchPlaceholder: 'พิมพ์เพื่อค้นหาชื่อผู้นำเสนอ',
            });
            applySearchTomSelectStyles(tom);
        }

        function setConferencePresenterWrapVisible(element, visible) {
            if (!(element instanceof HTMLElement)) return;
            element.classList.toggle('hidden', !visible);
            element.hidden = !visible;
            element.style.display = visible ? '' : 'none';
        }

        function toggleConferencePresenterFieldState(input, disabled, placeholder) {
            if (!(input instanceof HTMLElement)) return;
            if ('disabled' in input) input.disabled = disabled;
            input.classList.toggle('bg-slate-100', disabled);
            input.classList.toggle('text-slate-400', disabled);
            input.classList.toggle('cursor-not-allowed', disabled);
            if (typeof placeholder === 'string' && 'placeholder' in input) input.placeholder = placeholder;
        }

        function syncConferencePresenterRow(row) {
            if (!(row instanceof HTMLElement)) return;
            const typeSelect = row.querySelector('[data-conference-presenter-person-type]');
            const manualWrap = row.querySelector('[data-conference-presenter-manual-wrap]');
            const manualInput = row.querySelector('[data-conference-presenter-manual-name]');
            const userWrap = row.querySelector('[data-conference-presenter-user-wrap]');
            const userSelect = row.querySelector('[data-conference-presenter-user-select]');
            const userIdInput = row.querySelector('[data-conference-presenter-user-id]');
            const noteHidden = row.querySelector('[data-conference-presenter-note-hidden]');
            if (!(typeSelect instanceof HTMLSelectElement) || !(manualWrap instanceof HTMLElement) || !(manualInput instanceof HTMLInputElement) || !(userWrap instanceof HTMLElement) || !(userSelect instanceof HTMLSelectElement) || !(userIdInput instanceof HTMLInputElement) || !(noteHidden instanceof HTMLInputElement)) return;

            ensureConferencePresenterTomSelect(userSelect);
            const mode = String(typeSelect.value || '').trim();
            const previousMode = row.dataset.conferencePresenterMode || '';
            const modeChanged = previousMode !== '' && previousMode !== mode;
            const tom = userSelect.tomselect || null;
            const matchedUser = Array.isArray(window.projectUserDirectory)
                ? window.projectUserDirectory.find((user) => String(user.full_name || '').trim() === String(noteHidden.value || manualInput.value || '').trim())
                : null;

            if (mode === 'internal' && userIdInput.value && !String(userSelect.value || '').trim()) {
                userSelect.value = String(userIdInput.value).trim();
                if (tom) tom.setValue(String(userIdInput.value).trim(), true);
            }
            if (mode === 'internal' && !String(userSelect.value || '').trim() && matchedUser) {
                userSelect.value = String(matchedUser.id);
                if (tom) tom.setValue(String(matchedUser.id), true);
            }

            if (mode === 'internal') {
                if (modeChanged) {
                    manualInput.value = '';
                    userIdInput.value = '';
                    noteHidden.value = '';
                    if (tom) tom.clear(true); else userSelect.value = '';
                }
                const selectedOption = userSelect.options[userSelect.selectedIndex] || null;
                userIdInput.value = String(userSelect.value || '').trim();
                noteHidden.value = selectedOption && userIdInput.value ? String(selectedOption.textContent || '').trim() : '';
                setConferencePresenterWrapVisible(manualWrap, false);
                setConferencePresenterWrapVisible(userWrap, true);
                toggleConferencePresenterFieldState(manualInput, true, 'เลือกผู้นำเสนอจากรายชื่อบุคลากร');
                manualInput.required = false;
                userSelect.required = true;
                userSelect.disabled = false;
                if (tom) tom.enable();
                setTomSelectWrapperState(userSelect, true, false);
            } else if (mode === 'external') {
                if (modeChanged) {
                    if (tom) tom.clear(true); else userSelect.value = '';
                    userIdInput.value = '';
                    noteHidden.value = '';
                    manualInput.value = '';
                }
                setConferencePresenterWrapVisible(manualWrap, true);
                setConferencePresenterWrapVisible(userWrap, false);
                toggleConferencePresenterFieldState(manualInput, false, 'ยศ ชื่อ นามสกุล');
                manualInput.required = true;
                userSelect.required = false;
                userSelect.disabled = true;
                userIdInput.value = '';
                noteHidden.value = String(manualInput.value || '').trim();
                if (tom) tom.disable();
                setTomSelectWrapperState(userSelect, false, true);
            } else {
                if (tom) {
                    tom.clear(true);
                    tom.disable();
                } else {
                    userSelect.value = '';
                }
                setConferencePresenterWrapVisible(manualWrap, true);
                setConferencePresenterWrapVisible(userWrap, false);
                toggleConferencePresenterFieldState(manualInput, true, 'เลือกประเภทผู้นำเสนอก่อน');
                manualInput.required = false;
                userSelect.required = false;
                userSelect.disabled = true;
                userIdInput.value = '';
                noteHidden.value = '';
                manualInput.value = '';
                setTomSelectWrapperState(userSelect, false, true);
            }

            row.dataset.conferencePresenterMode = mode;
        }

        function bindConferencePresenterRow(row) {
            if (!(row instanceof HTMLElement)) return;
            const typeSelect = row.querySelector('[data-conference-presenter-person-type]');
            const manualInput = row.querySelector('[data-conference-presenter-manual-name]');
            const userSelect = row.querySelector('[data-conference-presenter-user-select]');
            if (!(typeSelect instanceof HTMLSelectElement) || !(manualInput instanceof HTMLInputElement) || !(userSelect instanceof HTMLSelectElement)) return;
            if (typeSelect.dataset.conferencePresenterBound !== '1') {
                typeSelect.dataset.conferencePresenterBound = '1';
                typeSelect.addEventListener('change', () => syncConferencePresenterRow(row));
            }
            if (manualInput.dataset.conferencePresenterBound !== '1') {
                manualInput.dataset.conferencePresenterBound = '1';
                ['input', 'change', 'blur'].forEach((eventName) => manualInput.addEventListener(eventName, () => syncConferencePresenterRow(row)));
            }
            if (userSelect.dataset.conferencePresenterBound !== '1') {
                userSelect.dataset.conferencePresenterBound = '1';
                ['change', 'blur'].forEach((eventName) => userSelect.addEventListener(eventName, () => syncConferencePresenterRow(row)));
            }
            syncConferencePresenterRow(row);
        }

        function initConferencePresenterRows(scope = document) {
            scope.querySelectorAll('.conference-presenter-row').forEach((row) => bindConferencePresenterRow(row));
        }

        window.syncConferencePresenterRow = syncConferencePresenterRow;

        /* ===== EA103: Conference + Presenters ===== */
        function renderEA103Section(idx) {
            const wrap = document.createElement('div');
            wrap.className = 'ea103-section space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4';
            wrap.innerHTML = `
    <input type="hidden" name="outputs[${idx}][conference_id]" value="">
    <div class="grid grid-cols-1 gap-3">
      <label>
        <span class="block text-sm text-gray-700 mb-1">ชื่อเวที/Conference</span>
        <input name="outputs[${idx}][conference_name]" class="w-full border p-2 rounded" placeholder="เช่น PMAC 2025">
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">ประเภทนำเสนอ</span>
        <select name="outputs[${idx}][conference_presentation_type]" class="w-full border p-2 rounded">
          <option value="">เลือก</option>
          <option value="Oral Presentation">Oral Presentation</option>
          <option value="Poster Presentation">Poster Presentation</option>
          <option value="Abstract Presentation">Abstract Presentation</option>
          <option value="Panel Discussion">Panel Discussion</option>
          <option value="Keynote Speech">Keynote Speech</option>
          <option value="World café">World café</option>
        </select>
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">สถานที่</span>
        <input name="outputs[${idx}][conference_location]" class="w-full border p-2 rounded" placeholder="เมือง/ประเทศ">
      </label>
    </div>
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <span class="font-semibold text-slate-800">รายชื่อผู้นำเสนอ</span>
      </div>
      <div class="conference-presenters-container space-y-3" id="confs-${idx}" data-conf-presenter-index="0"></div>
      <button type="button" class="lux-mobile-add-row-btn whitespace-nowrap px-3 py-2 rounded-lg bg-amber-600 text-sm font-semibold text-white" onclick="addConferencePresenterRow(this)">+ เพิ่มผู้นำเสนอ</button>
    </div>
  `;
            return wrap;
        }

        function addConferencePresenterRow(btnEl) {
            const outputRow = btnEl.closest('.output-row');
            const idx = outputRow.getAttribute('data-index');
            const presentersWrap = outputRow.querySelector(`#confs-${idx}`);
            if (!presentersWrap) return;
            const pIdx = confIndex[idx] || 0;
            const userOptions = (Array.isArray(window.projectUserDirectory) ? window.projectUserDirectory : [])
                .map((user) => `<option value="${String(user.id)}">${String(user.full_name || '').replace(/"/g, '&quot;')}</option>`)
                .join('');
            presentersWrap.insertAdjacentHTML('beforeend', `
    <div class="conference-presenter-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3">
      <input type="hidden" name="outputs[${idx}][conference_presenters][${pIdx}][id]" value="">
      <input type="hidden" name="outputs[${idx}][conference_presenters][${pIdx}][user_id]" value="" data-conference-presenter-user-id>
      <input type="hidden" name="outputs[${idx}][conference_presenters][${pIdx}][note]" value="" data-conference-presenter-note-hidden>
      <label>
        <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก</span>
        <select name="outputs[${idx}][conference_presenters][${pIdx}][person_type]" data-conference-presenter-person-type class="w-full border p-2 rounded" required>
          <option value="">กรุณาเลือก</option>
          <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
          <option value="external">คนนอก</option>
        </select>
      </label>
      <label data-conference-presenter-manual-wrap>
        <span class="block text-sm text-gray-700 mb-1">ชื่อผู้นำเสนอ</span>
        <input type="text" data-conference-presenter-manual-name class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed" placeholder="เลือกประเภทผู้นำเสนอก่อน" disabled>
      </label>
      <label class="hidden" data-conference-presenter-user-wrap>
        <span class="block text-sm text-gray-700 mb-1">เลือกผู้นำเสนอจากรายชื่อบุคลากร</span>
        <select data-conference-presenter-user-select class="w-full border p-2 rounded" disabled>
          <option value="">พิมพ์เพื่อค้นหาชื่อผู้นำเสนอ</option>
          ${userOptions}
        </select>
      </label>
      <button type="button" class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600" onclick="removeConferencePresenterRow(this)">ลบ</button>
    </div>
  `);

            confIndex[idx] = pIdx + 1;
            const row = presentersWrap.lastElementChild;
            if (row instanceof HTMLElement) bindConferencePresenterRow(row);
            bindProjectSearchSelectGuards(document);
        }

        function removeConferencePresenterRow(btn) {
            btn.closest('.conference-presenter-row')?.remove();
        }

        // ---------- ลบ Output + reindex ----------
        function removeRow(id, prefix) {
            const row = document.getElementById(id);
            const finalizeRemoval = () => {
                row?.remove();
                reindexOutput();
            };

            confirmProjectMobileRemoval(() => {
                if (row && typeof window.removeProjectRowWithScroll === 'function') {
                    window.removeProjectRowWithScroll(row, finalizeRemoval);
                    return;
                }

                finalizeRemoval();
            });
        }

        function reindexOutput() {
            const rows = document.querySelectorAll('#output-container .output-row');

            rows.forEach((row, i) => {
                row.id = `output-${i}`;
                row.setAttribute('data-index', i);

                const idxEl = row.querySelector('.output-index');
                if (idxEl) idxEl.textContent = i + 1;

                // เปลี่ยน prefix ชื่อฟิลด์ทั้งหมด outputs[old] → outputs[i]
                row.querySelectorAll(
                        'input[name^="outputs["], select[name^="outputs["], textarea[name^="outputs["]')
                    .forEach(el => {
                        const old = el.getAttribute('name');
                        if (!old) return;
                        el.setAttribute('name', old.replace(/^outputs\[\d+\]/, `outputs[${i}]`));
                    });

                // sync id ของ containers ให้ตรง index ใหม่
                const aWrap = row.querySelector('.authors-container');
                if (aWrap) aWrap.id = `authors-${i}`;
                const cWrap = row.querySelector('.conference-presenters-container');
                if (cWrap) cWrap.id = `confs-${i}`;
                const bWrap = row.querySelector('.book-authors-container');
                if (bWrap) bWrap.id = `book-authors-${i}`;

                // นับใหม่ให้ตัวนับ
                authorIndex[i] = aWrap ? aWrap.querySelectorAll('.author-row').length : 0;
                confIndex[i] = cWrap ? cWrap.querySelectorAll('.conference-presenter-row').length : 0;
                bookAuthorIndex[i] = bWrap ? bWrap.querySelectorAll('.book-author-row').length : 0;
            });

            outputIndex = rows.length;
        }

        // ---------- Init: hook section ให้แสดงถูกต้องตอนโหลด + delegated change ----------
        document.addEventListener('DOMContentLoaded', () => {
            const oc = document.getElementById('output-container');

            // แสดง section ให้ตรงกับค่าที่โหลดมาเดิม
            document.querySelectorAll('.output-row select.output-type').forEach(selectEl => {
                handleTypeChange(selectEl);
            });

            // ผูก event แบบ delegated สำหรับการเปลี่ยนค่าในภายหลัง
            if (oc) {
                oc.addEventListener('change', (e) => {
                    if (e.target && e.target.matches('select.output-type')) {
                        handleTypeChange(e.target);
                    }
                });
            }

            // ถ้ามี datepicker ไทยให้เรียก init ที่นี่ (ถ้าไม่มี ฟังก์ชันนี้จะถูกข้าม)
            if (typeof initThaiDatepickers === 'function') initThaiDatepickers();
            bindProjectUserLookup(document);
            bindProjectSearchSelectGuards(document);
            initOutputAuthorRows(document);
            initAwardRecipientRows(document);
            initBookAuthorRows(document);
            initAwardRecipientRows(document);
            initBookAuthorRows(document);
            initConferencePresenterRows(document);
            bindPiPersonControl(document);
            initCopiRows(document);
            initCoresearchRows(document);
        });







        let coResearchIndex = {{ count($project->coResearches ?? []) }};

        function addCoResearchRow() {
            const container = document.getElementById('coresearch-container');
            const id = `coresearch-${coResearchIndex}`;
            const labelNum = coResearchIndex + 1;
            const userOptions = (Array.isArray(window.projectUserDirectory) ? window.projectUserDirectory : [])
                .map((user) => `<option value="${String(user.id)}">${String(user.full_name || '').replace(/"/g, '&quot;')}</option>`)
                .join('');

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="coresearch-row space-y-2 p-4 border rounded-lg bg-white">
            <input type="hidden" name="coresearch[${coResearchIndex}][name]" data-coresearch-name>
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-slate-600">ลำดับ</span>
                <span class="inline-flex px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold coresearch-index">${labelNum}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <label class="col-span-12 md:col-span-5">
                    <span class="block text-sm text-gray-700 mb-1">คนภายนอก / ภายใน <span class="text-red-500">*</span></span>
                    <select name="coresearch[${coResearchIndex}][unit_status]" data-coresearch-unit-status class="w-full border p-2 rounded" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกสถานะหน่วยงาน')"
                                    oninput="this.setCustomValidity('')">
                        <option value="" disabled selected>กรุณาเลือก</option>
                        <option value="ภายในคณะแพทยศาสตร์ศิริราชพยาบาล">ภายในคณะแพทยศาสตร์ศิริราชพยาบาล</option>
                        <option value="ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล">ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล</option>
                        <option value="${CORESEARCH_INTERNAL_UNIT}">${CORESEARCH_INTERNAL_UNIT}</option>
                    </select>
                </label>
                <label class="col-span-12 md:col-span-5" data-coresearch-affiliation-wrap>
                    <span class="block text-sm text-gray-700 mb-1">สังกัด <span class="text-red-500">*</span></span>
                    <input name="coresearch[${coResearchIndex}][affiliation]" type="text" list="coresearchAffiliationList" data-coresearch-affiliation
                        class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed" placeholder="เลือกคนภายนอก / ภายในก่อน" disabled
                        oninvalid="this.setCustomValidity('กรุณาระบุสังกัด')"
                        oninput="this.setCustomValidity('')">
                </label>
                <div class="col-span-12 md:col-span-2 flex justify-end md:pt-6">
                    <button type="button" onclick="removeRow('${id}', 'coresearch')" class="text-red-600 text-xl">✕</button>
                </div>
                <label class="col-span-12" data-coresearch-manual-wrap>
                    <span class="block text-sm text-gray-700 mb-1">ชื่อ Co-Researcher <span class="text-red-500">*</span></span>
                    <input type="text" list="coresearchUsers" data-coresearch-manual-name
                        class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed" placeholder="เลือกคนภายนอก / ภายในก่อน" disabled
                        oninvalid="this.setCustomValidity('กรุณาระบุชื่อ Co-Researcher')"
                        oninput="this.setCustomValidity('')">
                </label>
                <label class="col-span-12 hidden" data-coresearch-user-wrap>
                    <span class="block text-sm text-gray-700 mb-1">เลือกบุคลากรภายในหน่วยวิจัย <span class="text-red-500">*</span></span>
                    <select name="coresearch[${coResearchIndex}][user_id]" data-coresearch-user-select class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed" disabled
                        oninvalid="this.setCustomValidity('กรุณาเลือกบุคลากรภายในหน่วยวิจัยจากรายการ')"
                        oninput="this.setCustomValidity('')">
                        <option value="">พิมพ์เพื่อค้นหาชื่อบุคลากร</option>
                        ${userOptions}
                    </select>
                </label>
        </div>
    </div>
    <div class="fte-mobile-delete-note">
        รายการนี้ถูกทำเครื่องหมายเพื่อลบแล้ว ระบบจะไม่บันทึกการแก้ไขของแถวนี้เมื่อกดบันทึก
    </div>
</div>
            `);

            coResearchIndex++;
            reindexCoResearch();
            keepAddedRowOpen(id);
            initCoresearchRows(container);
        }



        function reindexCoResearch() {
            const rows = document.querySelectorAll('#coresearch-container .coresearch-row');
            rows.forEach((row, index) => {
                row.querySelectorAll('.coresearch-index').forEach(span => {
                    span.textContent = index + 1;
                });
            });
        }

        function reindexOutput() {
            const rows = document.querySelectorAll('#output-container .output-row');
            rows.forEach((row, index) => {
                const spans = row.querySelectorAll('.output-index');
                spans.forEach(span => span.textContent = index + 1);
            });
        }

        function removeFteRow(id) {
            const row = document.getElementById(id);
            if (!row) return;
            markMobileFteRowDeleted(row);
        }

        function removeRow(id, type) {
            const el = document.getElementById(id);
            const removedIndex = type === 'fte' && el?.parentElement ?
                Array.from(el.parentElement.children).indexOf(el) :
                -1;
            const finalizeRemoval = () => {
                el?.remove();
                if (type === 'output') reindexOutput();
                if (type === 'copi') reindexCopi?.();
                if (type === 'irb') reindexIRB?.();
                if (type === 'fte') reindexFTE?.(Math.max(0, removedIndex));
                if (type === 'coresearch') reindexCoResearch?.();
            };

            const requestRemoval = (runner) => {
                if (type === 'fte' && el) {
                    confirmMobileFteRemoval(el, runner);
                    return;
                }
                confirmProjectMobileRemoval(runner);
            };

            requestRemoval(() => {
                if (el && typeof window.removeProjectRowWithScroll === 'function') {
                    window.removeProjectRowWithScroll(el, finalizeRemoval);
                    return;
                }

                finalizeRemoval();
            });
        }
    </script>

    <script>
        const MOBILE_WHEEL_MODE = window.PROJECT_MOBILE_WHEEL_DATE === true;

        function getClientTodayLocal() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return today;
        }

        function normalizeDateOnly(date) {
            const d = new Date(date);
            d.setHours(0, 0, 0, 0);
            return d;
        }

        function parseLocalDateInput(raw) {
            const normalizeThaiDigits = (input) => String(input || '').replace(/[๐-๙]/g, (ch) => String('๐๑๒๓๔๕๖๗๘๙'.indexOf(ch)));
            const value = normalizeThaiDigits(String(raw || '').trim()).replace(/\s+/g, ' ');
            if (!value) return null;

            let match = value.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{4})$/);
            if (match) {
                const [, dd, mm, yyyy] = match;
                let year = Number(yyyy);
                if (year > 2400) year -= 543;
                return new Date(year, Number(mm) - 1, Number(dd));
            }

            match = value.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})$/);
            if (match) {
                const [, yyyy, mm, dd] = match;
                let year = Number(yyyy);
                if (year > 2400) year -= 543;
                return new Date(year, Number(mm) - 1, Number(dd));
            }

            match = value.match(/^(\d{1,2})\s+([^\s]+)\s+(\d{4})$/);
            if (match) {
                const [, dd, monthRaw, yyyyRaw] = match;
                const monthMap = {
                    'มกราคม': 0, 'ม.ค.': 0, 'ม.ค': 0,
                    'กุมภาพันธ์': 1, 'ก.พ.': 1, 'ก.พ': 1,
                    'มีนาคม': 2, 'มี.ค.': 2, 'มี.ค': 2,
                    'เมษายน': 3, 'เม.ย.': 3, 'เม.ย': 3,
                    'พฤษภาคม': 4, 'พ.ค.': 4, 'พ.ค': 4,
                    'มิถุนายน': 5, 'มิ.ย.': 5, 'มิ.ย': 5,
                    'กรกฎาคม': 6, 'ก.ค.': 6, 'ก.ค': 6,
                    'สิงหาคม': 7, 'ส.ค.': 7, 'ส.ค': 7,
                    'กันยายน': 8, 'ก.ย.': 8, 'ก.ย': 8,
                    'ตุลาคม': 9, 'ต.ค.': 9, 'ต.ค': 9,
                    'พฤศจิกายน': 10, 'พ.ย.': 10, 'พ.ย': 10,
                    'ธันวาคม': 11, 'ธ.ค.': 11, 'ธ.ค': 11
                };
                const monthIdx = monthMap[monthRaw];
                if (monthIdx !== undefined) {
                    let yyyy = Number(yyyyRaw);
                    if (yyyy > 2400) yyyy -= 543;
                    return new Date(yyyy, monthIdx, Number(dd));
                }
            }

            const fallback = new Date(value);
            if (!Number.isNaN(fallback.getTime())) return fallback;
            return null;
        }

        function toDmyCE(date) {
            if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
            const dd = String(date.getDate()).padStart(2, '0');
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const yyyy = String(date.getFullYear());
            return `${dd}-${mm}-${yyyy}`;
        }

        function toYmdCE(date) {
            if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
            const yyyy = String(date.getFullYear());
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function toBeLabel(date) {
            if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
            const day = date.getDate();
            const month = date.toLocaleString('th-TH', {
                month: 'long'
            });
            const year = date.getFullYear() + 543;
            return `${day} ${month} ${year}`;
        }

        function ensureNativeHint(input) {
            const holder = input.closest('.relative') || input.parentElement;
            if (!holder) return null;
            let hint = holder.querySelector(`.native-date-be-hint[data-date-name="${CSS.escape(input.name || '')}"]`);
            if (!hint) {
                hint = document.createElement('div');
                hint.className = 'native-date-be-hint text-xs text-slate-500 mt-1';
                hint.dataset.dateName = input.name || '';
                holder.appendChild(hint);
            }
            return hint;
        }

        function initNativeThaiDateInput(input) {
            if (!(input instanceof HTMLInputElement)) return;
            if (input.hasAttribute('data-native-date-display-for')) return;
            if (typeof window.initNativeMobileDateInput === 'function') {
                window.initNativeMobileDateInput(input);
                return;
            }
            if (input.dataset.nativeDateReady === '1') return;

            const parsed = parseLocalDateInput(input.value);
            const ymdRaw = /^\d{4}-\d{2}-\d{2}$/.test(input.value || '') ? input.value : '';

            input.type = 'date';
            input.readOnly = false;
            input.classList.remove('flatpickr-hidden');
            input.removeAttribute('aria-hidden');
            input.style.display = 'block';
            input.value = parsed ? toYmdCE(parsed) : ymdRaw;
            if (input.dataset.noPast === '1') {
                input.min = toYmdCE(getClientTodayLocal());
            }

            const hint = ensureNativeHint(input);
            const syncHint = () => {
                const date = parseLocalDateInput(input.value);
                if (hint) hint.textContent = date ? `พ.ศ. ${toBeLabel(date)}` : '';
            };
            syncHint();
            input.addEventListener('change', syncHint);
            input.addEventListener('input', syncHint);
            input.dataset.nativeDateReady = '1';
        }

        function bindEndDateMinNative(startInput, endInput) {
            if (!(startInput instanceof HTMLInputElement) || !(endInput instanceof HTMLInputElement)) return;
            const update = () => {
                const startDate = parseLocalDateInput(startInput.value);
                if (startDate) {
                    endInput.min = toYmdCE(normalizeDateOnly(startDate));
                } else if (endInput.dataset.noPast === '1') {
                    endInput.min = toYmdCE(getClientTodayLocal());
                } else {
                    endInput.removeAttribute('min');
                }

                const endDate = parseLocalDateInput(endInput.value);
                if (startDate && endDate && normalizeDateOnly(endDate) < normalizeDateOnly(startDate)) {
                    endInput.value = '';
                    endInput.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }
            };

            startInput.addEventListener('change', update);
            startInput.addEventListener('input', update);
            endInput.addEventListener('change', update);
            endInput.addEventListener('input', update);
            update();
        }

        function appendFlatpickrHook(instance, hook, callback) {
            if (!instance || typeof callback !== 'function') return;
            const current = instance.config?.[hook];
            if (Array.isArray(current)) {
                if (!current.includes(callback)) current.push(callback);
                return;
            }
            if (typeof current === 'function') {
                instance.config[hook] = [current, callback];
                return;
            }
            instance.config[hook] = [callback];
        }

        function bindEndDateMinThaiPicker(startInput, endInput, key) {
            if (!(startInput instanceof HTMLElement) || !(endInput instanceof HTMLElement)) {
                return;
            }

            const retryKey = `__thaiDateRangeRetry_${key}`;
            const startReady = !!startInput._flatpickr;
            const endReady = !!endInput._flatpickr;
            if (!startReady || !endReady) {
                const retries = Number(endInput[retryKey] || 0);
                if (retries >= 60) return;
                endInput[retryKey] = String(retries + 1);
                setTimeout(() => bindEndDateMinThaiPicker(startInput, endInput, key), 80);
                return;
            }
            delete endInput[retryKey];

            const updaterKey = `__thaiDateRangeUpdater_${key}`;
            if (!endInput[updaterKey]) {
                endInput[updaterKey] = () => {
                    const fpStart = startInput._flatpickr;
                    const fpEnd = endInput._flatpickr;
                    if (!fpStart || !fpEnd) return;

                    const startDate = fpStart.selectedDates?.[0] ||
                        parseLocalDateInput(startInput.value) ||
                        parseLocalDateInput(fpStart.altInput?.value);
                    const minDate = startDate
                        ? normalizeDateOnly(startDate)
                        : (endInput.dataset.noPast === '1' ? getClientTodayLocal() : null);

                    fpEnd.set('minDate', minDate || null);
                    fpEnd.set('disable', minDate ? [date => normalizeDateOnly(date) < minDate] : []);

                    const endDate = fpEnd.selectedDates?.[0] ||
                        parseLocalDateInput(endInput.value) ||
                        parseLocalDateInput(fpEnd.altInput?.value);
                    const maxDate = endDate ? normalizeDateOnly(endDate) : null;
                    fpStart.set('maxDate', maxDate || null);
                    fpStart.set('disable', maxDate ? [date => normalizeDateOnly(date) > maxDate] : []);

                    if (endDate && minDate && normalizeDateOnly(endDate) < minDate) {
                        fpEnd.clear();
                        endInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                };

                startInput.addEventListener('change', endInput[updaterKey]);
                startInput.addEventListener('input', endInput[updaterKey]);
                endInput.addEventListener('change', endInput[updaterKey]);
                endInput.addEventListener('input', endInput[updaterKey]);
            }

            const hookKey = `${updaterKey}_hooked`;
            if (!endInput[hookKey]) {
                const fpStart = startInput._flatpickr;
                if (fpStart) {
                    appendFlatpickrHook(fpStart, 'onChange', endInput[updaterKey]);
                    appendFlatpickrHook(fpStart, 'onValueUpdate', endInput[updaterKey]);
                }

                const fpEnd = endInput._flatpickr;
                if (fpEnd) {
                    appendFlatpickrHook(fpEnd, 'onOpen', endInput[updaterKey]);
                    appendFlatpickrHook(fpEnd, 'onChange', endInput[updaterKey]);
                    appendFlatpickrHook(fpEnd, 'onValueUpdate', endInput[updaterKey]);
                    fpEnd.altInput?.addEventListener('focus', endInput[updaterKey]);
                    fpEnd.altInput?.addEventListener('click', endInput[updaterKey]);
                }

                endInput[hookKey] = '1';
            }

            endInput[updaterKey]();
        }

        function bindProjectDatePairsThaiPicker(scope = document) {
            if (MOBILE_WHEEL_MODE) {
                scope.querySelectorAll('input[name^="fte_roles["][name$="[start_date]"]').forEach((startInput) => {
                    const endName = startInput.name.replace('[start_date]', '[end_date]');
                    const endInput = document.querySelector(`input[name="${endName}"]`);
                    bindEndDateMinNative(startInput, endInput);
                });
                return;
            }

            scope.querySelectorAll('input[name^="fte_roles["][name$="[start_date]"]').forEach((startInput) => {
                const endName = startInput.name.replace('[start_date]', '[end_date]');
                const endInput = document.querySelector(`input[name="${endName}"]`);
                bindEndDateMinThaiPicker(startInput, endInput, `fte_${startInput.name}`);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('budgetInput');
            const notice = document.getElementById('budgetInputNotice');
            if (!input) return;

            input.addEventListener('input', function() {
                const rawValue = input.value.replace(/,/g, '');
                const hasInvalidChars = /[^\d.]/.test(rawValue);
                let value = rawValue.replace(/[^\d.]/g, '');
                const parts = value.split('.');

                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }

                let [intPart, decimalPart] = value.split('.');
                intPart = (intPart || '').replace(/^0+(?=\d)/, '');
                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                input.value = decimalPart !== undefined ? `${intPart}.${decimalPart}` : intPart;
                notice?.classList.toggle('hidden', !hasInvalidChars);
            });

            // ก่อน submit form ให้ลบ comma ออก
            input.form?.addEventListener('submit', () => {
                input.value = input.value.replace(/,/g, '');
            });
        });

        function initThaiDatepickers(scope = document, {
            bindPairs = true
        } = {}) {
            if (MOBILE_WHEEL_MODE) {
                scope.querySelectorAll('.thai-datepicker').forEach((input) => {
                    if (input.hasAttribute('data-native-date-display-for')) return;
                    initNativeThaiDateInput(input);
                });
                if (bindPairs) {
                    bindProjectDatePairsThaiPicker(scope);
                }
                return;
            }

            scope.querySelectorAll('.thai-datepicker').forEach((input) => {
                if (input.hasAttribute('data-native-date-display-for')) return;
                if (input._flatpickr) return;

                // รองรับข้อมูลเก่าที่อาจเก็บปี พ.ศ. / รูปแบบหลากหลาย
                // โดย normalize เป็น d-m-Y (ค.ศ.) ก่อนส่งให้ flatpickr
                const normalized = toDmyCE(parseLocalDateInput(input.value));
                if (normalized) {
                    input.value = normalized;
                }

                const options = {
                    locale: "th",
                    dateFormat: "d-m-Y",
                    altInput: true,
                    altFormat: "j F Y",
                    prevArrow: "", // 🔒 ซ่อนปุ่มย้อนเดือน
                    nextArrow: "", // 🔒 ซ่อนปุ่มถัดไปเดือน
                    disableMobile: true,
                    onReady: (selectedDates, dateStr, instance) => {
                        const altInput = instance.altInput;
                        if (altInput) {
                            altInput.readOnly = true;
                            altInput.className = input.className;
                            altInput.classList.remove('js-date-be', 'thai-datepicker', 'native-date-raw', 'flatpickr-hidden');
                            altInput.placeholder = input.getAttribute('placeholder') || input.dataset.placeholder || '';
                        }
                        input.classList.add('flatpickr-hidden');
                        toBuddhist(selectedDates, dateStr, instance);
                        requestAnimationFrame(() => toBuddhist(instance.selectedDates || selectedDates, dateStr, instance));
                    },
                    onValueUpdate: toBuddhist,
                    onChange: toBuddhist,
                    onMonthChange: toBuddhist,
                    onYearChange: toBuddhist,
                };

                if (input.dataset.noPast === '1') {
                    options.minDate = getClientTodayLocal();
                }

                flatpickr(input, options);
                bindDatepickerSummarySync(input);
                bindThaiDatepickerAltOpen(input);
            });

            if (bindPairs) {
                bindProjectDatePairsThaiPicker(scope);
            }
        }

        function shouldLazyInitThaiDatepicker(input) {
            return Boolean(input?.closest?.('#fte-container .fte-mobile-row'));
        }

        function primeLazyThaiDatepickers(scope = document) {
            scope.querySelectorAll('.thai-datepicker').forEach((input) => {
                if (input._flatpickr || input.dataset.lazyDatepickerBound === '1' || !shouldLazyInitThaiDatepicker(input)) {
                    return;
                }

                input.dataset.lazyDatepickerBound = '1';

                const boot = () => {
                    if (input._flatpickr) {
                        requestAnimationFrame(() => input._flatpickr?.open());
                        return;
                    }
                    const row = input.closest('.fte-mobile-row');
                    initThaiDatepickers(row || input.closest('label') || input.parentElement || input, {
                        bindPairs: true
                    });
                    requestAnimationFrame(() => input._flatpickr?.open());
                };

                ['focus', 'click'].forEach((eventName) => {
                    input.addEventListener(eventName, boot, {
                        passive: true
                    });
                });
            });
        }

        function bindDatepickerSummarySync(input) {
            if (!input) return;

            const notify = () => input.dispatchEvent(new Event('change', {
                bubbles: true
            }));

            setTimeout(notify, 0);

            const altInput = input._flatpickr?.altInput;
            if (!altInput || altInput.dataset.summarySyncBound === '1') return;

            altInput.dataset.summarySyncBound = '1';
            ['input', 'change', 'blur'].forEach((evt) => {
                altInput.addEventListener(evt, notify);
            });
        }

        function bindThaiDatepickerAltOpen(input) {
            const instance = input?._flatpickr;
            const altInput = instance?.altInput;
            if (!altInput || altInput.dataset.altOpenBound === '1') return;

            altInput.dataset.altOpenBound = '1';
            altInput.style.cursor = 'pointer';

            const openPicker = (event) => {
                event?.preventDefault?.();
                instance.open();
                requestAnimationFrame(() => instance.open());
            };

            ['mousedown', 'click', 'focus'].forEach((evt) => {
                altInput.addEventListener(evt, openPicker);
            });

            const extraTargets = [
                input.closest('label'),
                input.closest('.relative')
            ].filter(Boolean);

            extraTargets.forEach((target) => {
                if (target.dataset.altOpenBound === '1') return;
                target.dataset.altOpenBound = '1';
                target.addEventListener('mousedown', (event) => {
                    if (event.target?.closest?.('.flatpickr-calendar')) return;
                    if (event.target === altInput) return;
                    openPicker(event);
                });
            });
        }

        function normalizeFlatpickrMobile() {
            document.querySelectorAll('.thai-datepicker').forEach((input) => {
                const instance = input._flatpickr;
                if (!instance?.altInput) return;
                instance.altInput.readOnly = true;
                instance.altInput.style.display = 'block';
                input.style.display = 'none';
            });
        }

        function toBuddhist(selectedDates, dateStr, instance) {
            const altInput = instance.altInput;
            if (selectedDates.length > 0) {
                const date = selectedDates[0];
                const day = date.getDate();
                const month = date.toLocaleString('th-TH', {
                    month: 'long'
                });
                const year = date.getFullYear() + 543;
                altInput.value = `${day} ${month} ${year}`;
            }

            const years = instance.calendarContainer.querySelectorAll('.flatpickr-current-month input.cur-year');
            years.forEach(el => {
                const year = parseInt(el.value, 10);
                if (!isNaN(year) && year < 2400) el.value = year + 543;

                if (el.dataset.beBound === '1') return;
                el.dataset.beBound = '1';
                el.addEventListener('blur', () => {
                    const rawYear = parseInt(el.value, 10);
                    if (Number.isNaN(rawYear)) return;

                    const ceYear = rawYear > 2400 ? rawYear - 543 : rawYear;
                    const selectedDate = instance.selectedDates?.[0];
                    if (selectedDate) {
                        const nextDate = new Date(selectedDate);
                        nextDate.setFullYear(ceYear);
                        instance.setDate(nextDate, true);
                    } else {
                        instance.changeYear(ceYear);
                    }

                    setTimeout(() => {
                        const currentYearEl = instance.calendarContainer?.querySelector(
                            '.flatpickr-current-month input.cur-year'
                        );
                        if (!currentYearEl) return;
                        const currentYear = parseInt(currentYearEl.value, 10);
                        if (!Number.isNaN(currentYear) && currentYear < 2400) {
                            currentYearEl.value = currentYear + 543;
                        }
                    }, 0);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const eagerScopes = Array.from(document.querySelectorAll('.thai-datepicker'))
                .filter((input) => !shouldLazyInitThaiDatepicker(input));

            eagerScopes.forEach((input) => {
                initThaiDatepickers(input.closest('label') || input.parentElement || input, {
                    bindPairs: true
                });
            });

            primeLazyThaiDatepickers();
            setTimeout(normalizeFlatpickrMobile, 0);
        });



        function updateStatusColor(selectEl) {
            const selected = selectEl.options[selectEl.selectedIndex];
            const bgColor = selected.getAttribute("data-color") || "#ffffff";
            const isPlaceholder = selected.value === "";

            selectEl.style.backgroundColor = bgColor;

            // ถ้าเป็น placeholder → ใช้สีดำ
            selectEl.style.color = isPlaceholder ? "#9ca3af" : "#222";

        }

        document.addEventListener('DOMContentLoaded', () => {
            const selects = document.querySelectorAll("select[id$='_status']");
            selects.forEach(selectEl => updateStatusColor(selectEl));
        });
    </script>




    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('projectForm');
            if (!form) return;

            const updateFormMeta = ({
                includeFtePayload = false
            } = {}) => {
                const namedFields = Array.from(form.querySelectorAll('input[name], select[name], textarea[name]'))
                    .filter((el) => !el.disabled && !el.closest('template')).length;

                const fteExpected = form.querySelector('#fteExpectedRows');
                const varsExpected = form.querySelector('#formExpectedVars');
                const ftePayload = form.querySelector('#fteRolesPayload');
                const rowCount = form.querySelectorAll('#fte-container .fte-mobile-row').length;

                if (fteExpected) fteExpected.value = String(rowCount);
                if (varsExpected) varsExpected.value = String(namedFields);
                if (ftePayload) {
                    ftePayload.value = includeFtePayload ? JSON.stringify(serializeFteRows()) : '';
                }
            };

            updateFormMeta();
            form.addEventListener('submit', () => updateFormMeta({
                includeFtePayload: true
            }), true);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const projectForm = document.getElementById('projectForm');
            document.querySelectorAll('.ea119-section').forEach((section) => refreshBookAuthorDatalistInOutput(section));
            projectForm?.addEventListener('focusin', (event) => {
                const target = event.target;
                if (target instanceof HTMLInputElement && target.matches('input[data-book-author-input]')) {
                    refreshBookAuthorDatalistInOutput(target);
                }
            });

            function revealFieldIfCollapsed(field) {
                if (!field || field.closest('template')) return false;

                const displayTarget = window.getProjectValidationDisplayTarget?.(field) || field;

                let node = field.parentElement;
                let changed = false;

                while (node && node !== document.body) {
                    if (node.classList?.contains('row-collapsed')) {
                        node.classList.remove('row-collapsed');
                        node.dataset.collapsed = '0';
                        changed = true;
                    }

                    if (node.tagName === 'DETAILS' && !node.open) {
                        node.open = true;
                        changed = true;
                    }

                    if (node.classList?.contains('collapse') && !node.classList.contains('show')) {
                        node.classList.add('show');
                        changed = true;
                    }

                    if (node.hasAttribute('hidden')) {
                        node.removeAttribute('hidden');
                        changed = true;
                    }

                    if (node.id) {
                        const trigger = document.querySelector(`[aria-controls="${node.id}"]`);
                        if (trigger && trigger.getAttribute('aria-expanded') === 'false') {
                            trigger.click();
                            changed = true;
                        }
                    }

                    node = node.parentElement;
                }

                return changed || field.offsetParent !== null || displayTarget.offsetParent !== null;
            }

            if (projectForm) {
                projectForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    let firstInvalidField = null;
                    let firstInvalidMessage = '';
                    const invalidRows = new Set();
                    const invalidSections = new Set();
                    const sectionNameById = {
                        irb: 'IRB',
                        copi: 'COPI',
                        coresearch: 'Co-Researcher',
                        fte: 'Roles and Responsibilities',
                        output: 'ผลงานวิจัย (Output)',
                    };

                    window.validateProjectFteDateRanges?.(projectForm);

                    const duplicateAuthorCheck = validateEA119DuplicateAuthors(projectForm);
                    if (!duplicateAuthorCheck.valid) {
                        isValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = duplicateAuthorCheck.field;
                            firstInvalidMessage = duplicateAuthorCheck.message;
                        }
                        duplicateAuthorCheck.rows.forEach((row) => invalidRows.add(row));
                        invalidSections.add('ผลงานวิจัย (Output)');
                    }

                    const requiredFields = projectForm.querySelectorAll('[required]');
                    projectForm.querySelectorAll('[data-repeatable-row], .output-row, .fte-mobile-row, .copi-row, .coresearch-row, .irb-row')
                        .forEach(row => {
                            delete row.dataset.forceOpen;
                        });

                    requiredFields.forEach(field => {
                        if (field.disabled || field.type === 'hidden' || field.closest('template')) return;
                        const displayTarget = window.getProjectValidationDisplayTarget?.(field) || field;
                        const fieldVisible = field.offsetParent !== null || field.getClientRects().length > 0;
                        const displayVisible = displayTarget.offsetParent !== null || displayTarget.getClientRects().length > 0;
                        if (!fieldVisible && !displayVisible) {
                            const visibleNow = revealFieldIfCollapsed(field);
                            if (!visibleNow) return;
                        }

                        const val = String(field.value ?? '').trim();
                        const invalid = !val || !field.checkValidity();

                        if (invalid) {
                            isValid = false;
                            window.syncProjectValidationClasses?.(field, true);
                            if (field.name === 'planning_status') {
                                displayTarget.style.backgroundColor = '#fef2f2';
                                displayTarget.style.color = '#991b1b';
                            }
                            if (!firstInvalidField) {
                                firstInvalidField = displayTarget;
                                firstInvalidMessage = field.validationMessage || '';
                            }
                            const row = field.closest('[data-repeatable-row], .output-row, .fte-mobile-row, .copi-row, .coresearch-row, .irb-row') ||
                                displayTarget.closest('[data-repeatable-row], .output-row, .fte-mobile-row, .copi-row, .coresearch-row, .irb-row');
                            if (row) invalidRows.add(row);
                            const group = field.closest('[data-repeatable]') || displayTarget.closest('[data-repeatable]');
                            if (group) {
                                const id = group.dataset.id || '';
                                if (sectionNameById[id]) invalidSections.add(sectionNameById[id]);
                            }
                        } else {
                            window.syncProjectValidationClasses?.(field, false);
                            if (field.name === 'planning_status') {
                                updateStatusColor(displayTarget);
                            }
                        }
                    });

                    if (!isValid) {
                        window.syncFteTableValidationUi?.({
                            ensureRender: true
                        });
                        e.preventDefault();
                        e.stopPropagation();

                        invalidRows.forEach(row => {
                            row.classList.remove('row-collapsed');
                            row.dataset.collapsed = '0';
                            row.dataset.forceOpen = '1';
                        });

                        const focusInvalidField = () => {
                            if (!firstInvalidField) return;
                            revealFieldIfCollapsed(firstInvalidField);
                            firstInvalidField.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            setTimeout(() => {
                                firstInvalidField.focus({
                                    preventScroll: true
                                });
                            }, 250);
                        };
                        focusInvalidField();

                        const fieldLabel = firstInvalidField?.closest('label')?.querySelector('span')?.textContent
                            ?.trim() || 'ช่องข้อมูลที่จำเป็น';
                        const sectionText = invalidSections.size
                            ? ` (ส่วน: ${Array.from(invalidSections).join(', ')})`
                            : '';
                        const errorText = firstInvalidMessage
                            ? `${firstInvalidMessage}${sectionText}`
                            : `กรุณากรอก: ${fieldLabel}${sectionText}`;

                        Swal.fire({
                            icon: 'error',
                            title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                            text: errorText,
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#4f46e5',
                            returnFocus: false,
                            didClose: focusInvalidField
                        });
                    }
                });
            }
        });

        const budgetInput = document.getElementById('budgetInput');
        budgetInput?.addEventListener('blur', () => {
            const val = Number(budgetInput.value.replace(/,/g, ''));
            if (val > 1_000_000_000_000) {
                Swal.fire({
                    icon: 'warning',
                    title: 'งบประมาณสูงเกินไป',
                    text: 'กรุณาตรวจสอบจำนวนเงินอีกครั้ง'
                });
                budgetInput.focus();
            }
        });
    </script>

    <script>
        // ใช้ Blade render รายชื่อเป็น HTML <option> พร้อม escape
        window.copiOptionsHtml = `{!! collect($allUsers)->map(function ($u) {
                $name = $u->full_name;
                return "<option value=\"{$name}\"></option>";
            })->implode('') !!}`;

        window.coresearchOptionsHtml = `{!! collect($allUsers)->map(function ($u) {
                $name = $u->full_name;
                return "<option value=\"{$name}\"></option>";
            })->implode('') !!}`;
    </script>

    <script>
        (function() {
            const modal = document.getElementById('progressReportModal');
            if (!modal) return;

            const frame = document.getElementById('progressReportModalFrame');
            const title = document.getElementById('progressReportModalTitle');
            const summaryTextEl = document.getElementById('progressReportSummaryText');
            const latestTitleEl = document.getElementById('progressReportLatestTitle');
            const latestEditBtn = document.getElementById('progressEditLatestBtn');
            const currentProjectId = Number(@json($project->id));

            const closeModal = () => {
                modal.classList.add('hidden');
                if (frame) frame.src = 'about:blank';
                document.body.classList.remove('overflow-hidden');
            };

            const openModal = (url, label) => {
                if (!url || !frame) return;
                frame.src = url;
                if (title) title.textContent = label || 'ความก้าวหน้าของการดำเนินงาน';
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            const applyProgressSummary = (summary) => {
                if (!summary || typeof summary !== 'object') return;

                const count = Number(summary.count ?? 0);
                const latestDate = (summary.latestDate || '').toString().trim();
                const reportTitle = (summary.title || '').toString().trim();
                const editUrl = (summary.editUrl || '').toString().trim();

                if (summaryTextEl) {
                    let text = `มีรายงาน ${count} รายการ`;
                    if (latestDate) {
                        text += ` · ล่าสุด ${latestDate}`;
                    }
                    summaryTextEl.textContent = text;
                }

                if (latestTitleEl) {
                    if (reportTitle) {
                        latestTitleEl.textContent = `หัวข้อล่าสุด: ${reportTitle}`;
                        latestTitleEl.classList.remove('hidden');
                    } else {
                        latestTitleEl.classList.add('hidden');
                        latestTitleEl.textContent = '';
                    }
                }

                if (latestEditBtn) {
                    if (editUrl) {
                        latestEditBtn.dataset.url = editUrl;
                        latestEditBtn.dataset.title = `แก้ไขรายงาน: ${reportTitle || 'ความก้าวหน้าของการดำเนินงาน'}`;
                        latestEditBtn.classList.remove('hidden');
                    } else {
                        latestEditBtn.dataset.url = '';
                        latestEditBtn.classList.add('hidden');
                    }
                }
            };

            document.querySelectorAll('[data-progress-modal-open]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    openModal(btn.dataset.url || '', btn.dataset.title || '');
                });
            });

            modal.querySelectorAll('[data-progress-modal-close]').forEach((btn) => {
                btn.addEventListener('click', closeModal);
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            window.addEventListener('message', (event) => {
                if (event.origin !== window.location.origin) return;

                const payload = event.data;
                if (!payload || Number(payload.projectId) !== currentProjectId) return;

                if (payload.type === 'progress-report:close') {
                    closeModal();
                    return;
                }

                if (payload.type !== 'progress-report:updated') return;

                applyProgressSummary(payload.summary || {});
                closeModal();
            });
        })();
    </script>

    <script>
        (() => {
            const overlayId = 'projectEditLoadingOverlay';
            let released = false;

            const releaseLoadingOverlay = () => {
                if (released) return;
                released = true;

                const overlay = document.getElementById(overlayId);
                overlay?.classList.add('is-hidden');
                window.setTimeout(() => overlay?.remove(), 240);
                document.documentElement.classList.remove('page-loading-lock');
            };

            const resetMobileBackNavigationState = () => {
                document.documentElement.classList.remove('page-loading-lock');
                document.body.classList.remove('overflow-hidden');
                window.dispatchEvent(new CustomEvent('app-mobile-nav', {
                    detail: {
                        open: false
                    }
                }));

                const openNavToggle = document.querySelector('#main-nav .mobile-nav-toggle.is-open');
                if (openNavToggle instanceof HTMLElement) {
                    openNavToggle.click();
                }
            };

            window.releaseProjectEditLoadingOverlay = releaseLoadingOverlay;

            window.addEventListener('load', () => {
                window.setTimeout(() => {
                    window.requestAnimationFrame(() => {
                        window.requestAnimationFrame(releaseLoadingOverlay);
                    });
                }, 250);
            }, {
                once: true
            });

            window.addEventListener('pageshow', () => {
                resetMobileBackNavigationState();
                releaseLoadingOverlay();
            });

            window.setTimeout(releaseLoadingOverlay, 4000);
        })();
    </script>

    <script>
        (() => {
            const formatBe = (date) => {
                if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
                const day = date.getDate();
                const month = date.toLocaleString('th-TH', { month: 'long' });
                const year = date.getFullYear() + 543;
                return `${day} ${month} ${year}`;
            };

            const syncForcedBeDatepicker = (input) => {
                if (!(input instanceof HTMLInputElement)) return false;
                const fp = input._flatpickr;
                if (!fp) return false;

                const picked = fp.selectedDates?.[0] ||
                    (typeof parseLocalDateInput === 'function' ? parseLocalDateInput(input.value) : null) ||
                    (typeof parseLocalDateInput === 'function' ? parseLocalDateInput(fp.altInput?.value) : null);

                if (picked && fp.altInput) {
                    fp.altInput.value = formatBe(picked);
                }

                const yearInput = fp.calendarContainer?.querySelector('.flatpickr-current-month input.cur-year');
                if (yearInput) {
                    const year = parseInt(yearInput.value, 10);
                    if (!Number.isNaN(year) && year < 2400) {
                        yearInput.value = String(year + 543);
                    }
                }

                return true;
            };

            const bindForcedBeDatepicker = (input) => {
                if (!(input instanceof HTMLInputElement) || input.dataset.forceBeBound === '1') return;

                const attach = () => {
                    if (!syncForcedBeDatepicker(input)) {
                        requestAnimationFrame(attach);
                        return;
                    }

                    const fp = input._flatpickr;
                    if (!fp || input.dataset.forceBeBound === '1') return;
                    input.dataset.forceBeBound = '1';

                    ['onReady', 'onOpen', 'onValueUpdate', 'onChange', 'onMonthChange', 'onYearChange'].forEach((hook) => {
                        if (typeof appendFlatpickrHook === 'function') {
                            appendFlatpickrHook(fp, hook, () => syncForcedBeDatepicker(input));
                        }
                    });

                    fp.altInput?.addEventListener('focus', () => syncForcedBeDatepicker(input));
                    fp.altInput?.addEventListener('click', () => syncForcedBeDatepicker(input));
                };

                attach();
            };

            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('input[data-force-be-date="1"]').forEach(bindForcedBeDatepicker);
            });
        })();
    </script>

@endsection

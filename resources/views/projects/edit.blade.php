@extends('layouts.app')

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
        'จัดทำสื่อ / Communication Assets',
        'สร้างโปรเเกรม ฐานข้อมูล เเละ โปรเเกรมที่เกี่ยวข้องกับงานวิจัย',
        'IT and Technical support',
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
    $coresearchAffiliationList = \App\Models\CoResearch::whereNotNull('affiliation')
        ->whereRaw("TRIM(affiliation) <> ''")
        ->selectRaw('TRIM(affiliation) as affiliation')
        ->distinct()
        ->orderBy('affiliation')
        ->pluck('affiliation');
    $copiAffiliationList = \App\Models\Copi::whereNotNull('affiliation')
        ->whereRaw("TRIM(affiliation) <> ''")
        ->selectRaw('TRIM(affiliation) as affiliation')
        ->orderBy('affiliation')
        ->pluck('affiliation');
@endphp

{{-- datalist ใช้ร่วมทุกแถว --}}
<datalist id="userNames">
    @foreach ($allUsers as $user)
        @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
        <option value="{{ $fullName }}"></option>
    @endforeach
</datalist>
{{-- ✅ DATAlIST กลาง (ใช้ร่วมกันทุก row) --}}
<datalist id="fteUsers">
    @foreach ($allUsers as $user)
        @php
            $fullName = trim(($user->prefix ?? '') . ' ' . ($user->name ?? ''));
        @endphp
        <option value="{{ $fullName }}"></option>
    @endforeach
</datalist>

<script>
    window.AUTH_USER_ID = {{ auth()->id() ?? 'null' }};
    window.PROJECT_CARD_DATE_MODE = 'edit';
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
        background: rgba(255, 255, 255, 0.86);
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

    .fte-row {
        content-visibility: auto;
        contain-intrinsic-size: 120px;
    }

    .fte-row.row-collapsed {
        contain-intrinsic-size: 88px;
    }
</style>


@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    <div class="project-form max-w-6xl mx-auto mt-6 bg-white border border-slate-200 shadow-sm rounded-2xl p-6">

        <div class="mb-4">
            {{-- <a href="javascript:history.back()" class="text-blue-600 hover:underline text-base font-semibold">
                <<< ย้อนกลับ </a> --}}
            <a href="{{ route('projects.index') }}" class="text-blue-600 hover:underline">
                ← กลับไปยังรายการโครงการ
            </a>
        </div>
        <h2 class="text-xl font-bold mb-6 text-gray-800">✏️ แก้ไขข้อมูลโครงการวิจัย</h2>



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
                    <span class="block text-sm text-gray-700 mb-1">ประเภทของโครงการ<span class="text-red-500">*</span></span>
                    <select name="project_type" id="project_type" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกประเภทโครงการ')" oninput="this.setCustomValidity('')">
                        <option value="" {{ old('project_type', $project->project_type) ? '' : 'selected' }}>
                            เลือกประเภทของโครงการ
                        </option>
                        <option value="01 ผู้สูงอายุ ตามยุทธศาสตร์ศาสตร์สุขอายุของคณะแพทยศาสตร์ศิริราชพยาบาล"
                            {{ old('project_type', $project->project_type) == '01 ผู้สูงอายุ ตามยุทธศาสตร์ศาสตร์สุขอายุของคณะแพทยศาสตร์ศิริราชพยาบาล' ? 'selected' : '' }}>
                            01 ผู้สูงอายุ ตามยุทธศาสตร์ศาสตร์สุขอายุของคณะแพทยศาสตร์ศิริราชพยาบาล
                        </option>
                        <option
                            value="02 โครงการพัฒนาระบบเพื่อการสร้างเสริมสุขภาพสำหรับพระสงฆ์ โดยทีมสหวิทยาการ และการมีส่วนร่วมของประชาชนอย่างยั่งยืน : บางกอกน้อยโมเดล"
                            {{ old('project_type', $project->project_type) == '02 โครงการพัฒนาระบบเพื่อการสร้างเสริมสุขภาพสำหรับพระสงฆ์ โดยทีมสหวิทยาการ และการมีส่วนร่วมของประชาชนอย่างยั่งยืน : บางกอกน้อยโมเดล' ? 'selected' : '' }}>
                            02 โครงการพัฒนาระบบเพื่อการสร้างเสริมสุขภาพสำหรับพระสงฆ์...
                        </option>
                        <option value="03 Health System research and HTA"
                            {{ old('project_type', $project->project_type) == '03 Health System research and HTA' ? 'selected' : '' }}>
                            03 Health System research and HTA
                        </option>
                        <option value="04 การใช้ยาอย่างสมเหตุผล (RDU)"
                            {{ old('project_type', $project->project_type) == '04 การใช้ยาอย่างสมเหตุผล (RDU)' ? 'selected' : '' }}>
                            04 การใช้ยาอย่างสมเหตุผล (RDU)
                        </option>
                        <option
                            value="05 Health Policy Platform: Prince Mahidol Award Conference (PMAC), Global Health, UHC"
                            {{ old('project_type', $project->project_type) == '05 Health Policy Platform: Prince Mahidol Award Conference (PMAC), Global Health, UHC' ? 'selected' : '' }}>
                            05 Health Policy Platform: Prince Mahidol Award Conference (PMAC), Global Health, UHC
                        </option>
                        <option value="06 Real-world Evidence: E-Health, Health Information system, Digital Health"
                            {{ old('project_type', $project->project_type) == '06 Real-world Evidence: E-Health, Health Information system, Digital Health' ? 'selected' : '' }}>
                            06 Real-world Evidence: E-Health, Health Information system, Digital Health
                        </option>
                        <option value="07 Fall prevention + Hip facture preject (VBHC & Registry)"
                            {{ old('project_type', $project->project_type) == '07 Fall prevention + Hip facture preject (VBHC & Registry)' ? 'selected' : '' }}>
                            07 Fall prevention + Hip facture preject (VBHC & Registry)
                        </option>
                        <option value="08 การพัฒนาศักยภาพบุคลากร และการจัดเก็บองค์ความรู้เพื่อส่งเสริมการเรียนรู้"
                            {{ old('project_type', $project->project_type) == '08 การพัฒนาศักยภาพบุคลากร และการจัดเก็บองค์ความรู้เพื่อส่งเสริมการเรียนรู้' ? 'selected' : '' }}>
                            08 การพัฒนาศักยภาพบุคลากร และการจัดเก็บองค์ความรู้เพื่อส่งเสริมการเรียนรู้
                        </option>
                        <option value="09 SIHP Process Improvement (System Support)"
                            {{ old('project_type', $project->project_type) == '09 SIHP Process Improvement (System Support)' ? 'selected' : '' }}>
                            09 SIHP Process Improvement (System Support)
                        </option>
                        <option value="10 อื่นๆ ที่ได้รับมอบหมาย ไม่มีค่า FTE"
                            {{ old('project_type', $project->project_type) == '10 อื่นๆ ที่ได้รับมอบหมาย ไม่มีค่า FTE' ? 'selected' : '' }}>
                            10 อื่นๆ ที่ได้รับมอบหมาย ไม่มีค่า FTE
                        </option>
                        <option value="11 SIPG"
                            {{ old('project_type', $project->project_type) == '11 SIPG' ? 'selected' : '' }}>
                            11 SIPG
                        </option>
                        <option value="12 RDMU"
                            {{ old('project_type', $project->project_type) == '12 RDMU' ? 'selected' : '' }}>
                            12 RDMU
                        </option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">กลุ่มเนื้อหา (Theme) <span class="text-red-500">*</span></span>
                    <select name="theme" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกกลุ่มเนื้อหา')" oninput="this.setCustomValidity('')">
                        <option value="" {{ old('theme', $project->theme) ? '' : 'selected' }}>
                            กรุณาเลือกกลุ่มเนื้อหา</option>
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
                    <span class="block text-sm text-gray-700 mb-1">ลิงก์ Google Drive / Shortcut</span>
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
                    <input name="funding_type" value="{{ old('funding_type', $project->funding_type) }}"
                        class="w-full border p-2 rounded">
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
                        <span class="block text-sm text-gray-700 mb-1">DA106 - งบประมาณ (บาท)</span>
                        @php
                            $budgetRaw = $project->budget ?? '';
                            $budgetFormatted = is_numeric($budgetRaw) ? number_format($budgetRaw, 2) : $budgetRaw;
                        @endphp

                        <input id="budgetInput" name="budget" type="text" inputmode="decimal"
                            value="{{ old('budget', $budgetFormatted) }}" class="w-full border p-2 rounded">

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
                    <input name="team_end_date" type="text"
                        value="{{ old('team_end_date', $project->team_end_date) }}"
                        class="thai-datepicker w-full border p-2 rounded pl-10 bg-white cursor-pointer"
                        data-force-be-date="1"
                        placeholder="กรุณาเลือกวันที่สิ้นสุดการทำงานของทีม">
                    <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
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
                    <span class="block text-sm text-gray-700 mb-1">วันที่สิ้นสุด (ตามสัญญา)</span>
                    <input name="end_date" type="text"
                        value="{{ old('end_date', $project->end_date) }}"
                        class="thai-datepicker w-full border p-2 rounded pl-10 bg-white cursor-pointer"
                        data-force-be-date="1"
                        placeholder="กรุณาเลือกวันที่สิ้นสุดตามสัญญา">
                    <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
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
                    <div id="irb-{{ $i }}" class="irb-row grid grid-cols-1 md:grid-cols-3 gap-2 items-center p-3 border rounded-lg bg-gray-50">
                        <div class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">รหัส IRB ที่ <span
                                    class="irb-index">{{ $i + 1 }}</span> <span class="text-red-500">*</span></span>
                            <input name="irbs[{{ $i }}][code]" value="{{ $irb['code'] }}"
                                class="w-full border p-2 rounded" placeholder="Eg. 2025xxx" required
                                oninvalid="this.setCustomValidity('กรุณาระบุรหัส IRB')"
                                oninput="this.setCustomValidity('')">
                        </div>
                        <div class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">จาก (ชื่อหน่วยงาน) ของรหัส IRB ที่ <span
                                    class="irb-index">{{ $i + 1 }}</span> <span class="text-red-500">*</span></span>
                            <input name="irbs[{{ $i }}][source]" value="{{ $irb['source'] }}"
                                class="w-full border p-2 rounded" placeholder="ชื่อหน่วยงาน" required
                                oninvalid="this.setCustomValidity('กรุณาระบุหน่วยงาน')"
                                oninput="this.setCustomValidity('')">
                        </div>
                        <div class="w-full flex flex-col sm:flex-row gap-2 items-end">
                            <div class="w-full relative">
                                <span class="block text-sm text-gray-700 mb-1">วันที่หมดอายุ <span class="text-red-500">*</span></span>
                                <input name="irbs[{{ $i }}][exp_date]" value="{{ $irb['exp_date'] ?? '' }}"
                                    type="text" placeholder="เลือกวันที่"
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
                            <button type="button" onclick="removeRow('irb-{{ $i }}', 'irb')"
                                class="text-red-600 mb-2">✕</button>
                        </div>

                    </div>
                @endforeach
            </div>
            <button type="button" onclick="addIRBCode()" class="mb-6 px-3 py-1 bg-red-600 text-white rounded">+
                เพิ่มรหัส IRB</button>


            <hr class="my-6 border-t-2 border-gray-300">
            <h3 class="font-semibold text-lg text-blue-700 mb-2">C: Roles and Responsibilities</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">CA101 - ชื่อ PI (ผู้วิจัยหลัก) <span class="text-red-500">*</span></span>
                    <input name="pi_name" list="userNames" value="{{ old('pi_name', $project->pi_name) }}"
                        class="w-full border p-2 rounded" placeholder="ยศ-ชื่อ PI" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกกลุ่มเนื้อหา')" oninput="this.setCustomValidity('')">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">CA103 - สังกัดผู้วิจัยหลัก <span class="text-red-500">*</span></span>

                    <input name="pi_affiliation" value="{{ old('pi_affiliation', $project->pi_affiliation ?? '') }}"
                        class="w-full border p-2 rounded" placeholder="สังกัด PI" list="pi_affiliation_list" required
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
            </div>





            <h3 class="text-balance text-blue-700 mb-2">รายชื่อผู้ร่วมวิจัย (COPI)</h3>
            <div id="copi-container" class="space-y-2 mb-4">
                @foreach ($project->copis ?? [] as $i => $copi)
                    <div id="copi-{{ $i }}" class="copi-row grid grid-cols-1 md:grid-cols-2 gap-4 items-center p-3 border rounded-lg bg-gray-50">
                        <label class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">ชื่อ COPI คนที่ {{ $i + 1 }} <span class="text-red-500">*</span></span>
                            <input name="copis[{{ $i }}][name]"
                                value="{{ old('copis.' . $i . '.name', $copi['name']) }}"
                                list="userNames" class="w-full border p-2 rounded"
                                placeholder="ยศ ชื่อ-นามสกุล COPI" required
                                oninvalid="this.setCustomValidity('กรุณาระบุชื่อ COPI')"
                                oninput="this.setCustomValidity('')">
                        </label>

                        <div class="flex flex-col sm:flex-row gap-2 w-full">
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">สังกัด COPI คนที่
                                    {{ $i + 1 }} <span class="text-red-500">*</span></span>
                                <input name="copis[{{ $i }}][affiliation]" value="{{ $copi['affiliation'] }}"
                                    class="w-full border p-2 rounded" placeholder="สังกัด COPI" list="copiAffiliationList" required
                                    oninvalid="this.setCustomValidity('กรุณาระบุสังกัด COPI')"
                                    oninput="this.setCustomValidity('')">
                            </label>
                            <button type="button" onclick="removeRow('copi-{{ $i }}', 'copi')"
                                class="text-red-600 mt-6">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" onclick="addCopiRow()" class="mb-6 px-3 py-1 bg-yellow-600 text-white rounded">+ เพิ่ม
                COPI</button>


            <h3 class="text-balance text-blue-700 mb-2">รายชื่อผู้ร่วมวิจัย (Co-Researcher)</h3>
            <div id="coresearch-container" class="space-y-4 mb-4">
                @foreach ($project->coResearches ?? [] as $i => $co)
                    <div id="coresearch-{{ $i }}" class="coresearch-row space-y-2 p-4 border rounded-lg bg-gray-50">
                        <!-- ชื่อ -->
                        <label class="block w-full">
                            <span class="block text-sm text-gray-700 mb-1">
                                ชื่อ Co-Researcher คนที่ <span class="coresearch-index">{{ $i + 1 }}</span> <span class="text-red-500">*</span>
                            </span>
                            <input name="coresearch[{{ $i }}][name]"
                                value="{{ old('coresearch.' . $i . '.name', $co->name) }}"
                                list="userNames" class="w-full border p-2 rounded"
                                placeholder="ยศ ชื่อ-นามสกุล" required
                                oninvalid="this.setCustomValidity('กรุณาระบุชื่อ Co-Researcher')"
                                oninput="this.setCustomValidity('')">
                        </label>
                        <!-- สังกัด + สถานะ + ปุ่มลบ -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm text-gray-700 mb-1">สังกัด <span class="text-red-500">*</span></span>
                                <input name="coresearch[{{ $i }}][affiliation]"
                                    value="{{ $co->affiliation }}" class="w-full border p-2 rounded"
                                    placeholder="สังกัด" list="coresearchAffiliationList" required
                                    oninvalid="this.setCustomValidity('กรุณาระบุสังกัด')"
                                    oninput="this.setCustomValidity('')">
                            </label>
                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm text-gray-700 mb-1">คนภายนอก / ภายใน <span class="text-red-500">*</span></span>
                                <select name="coresearch[{{ $i }}][unit_status]"
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
                                    <option value="ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ"
                                        {{ $co->unit_status == 'ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ' ? 'selected' : '' }}>
                                        ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ
                                    </option>
                                </select>
                            </label>
                            <div class="col-span-12 md:col-span-2 flex justify-end md:pt-6">
                                <button type="button"
                                    onclick="removeRow('coresearch-{{ $i }}', 'coresearch')"
                                    class="text-red-600 text-xl">✕</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- ปุ่มเพิ่ม -->
            <button type="button" onclick="addCoResearchRow()" class="mb-6 px-3 py-1 bg-purple-600 text-white rounded">
                + เพิ่ม Co-Researcher
            </button>




            <!-- ========== SECTION D : รายชื่อผู้มีส่วนร่วม (FTE) ========== -->
            <hr class="my-6 border-t-2 border-gray-300">

            <div>

                <div class="mb-2">
                    <h3 class="font-semibold text-lg text-blue-700">D: รายชื่อผู้มีส่วนร่วมในการทำงาน</h3>
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
                        <div id="fte-{{ $i }}" class="fte-row p-4 border rounded-lg bg-white shadow-sm">
                            <input type="hidden" name="fte_roles[{{ $i }}][id]" value="{{ $fteId }}">

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

                                <!-- ===== Left Column ===== -->
                                <div class="md:col-span-9 space-y-4">

                                    <!-- ชื่อ + กลุ่ม -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                        <label class="w-full">
                                            <span class="block text-sm font-semibold"><span class="fte-index text-xl font-extrabold text-blue-700">{{ $i + 1 }}</span>. ชื่อ-นามสกุล <span class="text-red-500">*</span></span>
                                            <input list="fteUsers" name="fte_roles[{{ $i }}][name]"
                                                value="{{ $fteName }}" class="w-full border p-2 rounded" required
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
                                            <span class="block text-sm font-semibold">ภาระงาน <span class="text-red-500">*</span></span>
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
                                            <span class="block text-sm font-semibold">วันเริ่มต้นภาระงาน <span class="text-red-500">*</span></span>
                                            <input name="fte_roles[{{ $i }}][start_date]" type="text"
                                                value="{{ $fteStartDate }}"
                                                class="fte-shared-datepicker w-full border p-2 rounded pl-10 bg-white cursor-pointer" required readonly
                                                data-date-role="start"
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
                                            <span class="block text-sm font-semibold">วันสิ้นสุดภาระงาน <span class="text-red-500">*</span></span>
                                            <input name="fte_roles[{{ $i }}][end_date]" type="text"
                                                value="{{ $fteEndDate }}"
                                                class="fte-shared-datepicker w-full border p-2 rounded pl-10 bg-white cursor-pointer" required readonly
                                                data-date-role="end"
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
                                        <span class="block text-sm font-semibold">ชั่วโมงการทำงาน (Hour) <span class="text-red-500">*</span></span>
                                        <input type="number" step="0.1" min="0"
                                            name="fte_roles[{{ $i }}][hours]" value="{{ $fteHours }}"
                                            class="fte-hours w-full border p-2 rounded text-right" placeholder="0.0" required
                                            oninvalid="this.setCustomValidity('กรุณาระบุชั่วโมงการทำงาน')"
                                            oninput="this.setCustomValidity('')">
                                    </label>

                                    <!-- รายละเอียดภารกิจ -->
                                    <label class="w-full">
                                        <span class="block text-sm font-semibold">รายละเอียดภารกิจ <span class="text-red-500">*</span></span>
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
                                <div class="md:col-span-3 space-y-4">

                                    <!-- FTE (ผู้วิจัย) -->
                                    <label class="block p-4 border rounded-xl bg-blue-50">
                                        <span class="block text-sm font-bold text-blue-800 mb-2">FTE (ผู้วิจัย)</span>
                                        <input type="number" step="0.00001" min="0" max="1"
                                            name="fte_roles[{{ $i }}][fte_value]"
                                            value="{{ $fteValue }}"
                                            class="fte-value w-full border p-3 rounded bg-gray-100 text-right" readonly>
                                    </label>

                                    <!-- FTE (ผู้บริหาร) -->
                                    <label class="block p-4 border rounded-xl bg-emerald-50">
                                        <span class="block text-sm font-bold text-emerald-800 mb-2">FTE (ผู้บริหาร)</span>
                                        <input type="number" step="0.01" min="0" max="1"
                                            name="fte_roles[{{ $i }}][fte_value_research]"
                                            value="{{ $fteValueResearch }}"
                                            class="w-full border p-3 rounded text-right">
                                    </label>

                                    <button type="button" onclick="removeRow('fte-{{ $i }}','fte')"
                                        class="w-full py-2 border rounded-lg text-red-600 hover:bg-red-50">
                                        ✕ ลบผู้มีส่วนร่วมนี้
                                    </button>

                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
                <div id="fte-deleted-ids"></div>
                <input type="text" id="fteSharedDatepicker" class="hidden" aria-hidden="true" tabindex="-1">

                <button type="button" onclick="addFTERow()" class="mb-6 px-3 py-1 bg-blue-600 text-white rounded">
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

                        {{-- ชื่อผลงาน --}}
                        <label class="block w-full">
                            <span class="block text-sm text-gray-700 mb-1">
                                ชื่อผลงานที่ <span class="output-index">{{ $i + 1 }}</span> <span class="text-red-500">*</span>
                            </span>
                            <input name="outputs[{{ $i }}][title]" type="text"
                                class="w-full border p-2 rounded" value="{{ old("outputs.$i.title", $out->title) }}"
                                required oninvalid="this.setCustomValidity('กรุณากรอกชื่อผลงาน')"
                                oninput="this.setCustomValidity('')" placeholder="ชื่อผลงาน">
                        </label>

                        {{-- บรรทัด: ประเภท, หมายเหตุ, วันที่ --}}
                        <div class="grid grid-cols-3 gap-4 items-end">
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
                                    <option value="EA115 Capacity Building"
                                        {{ $t === 'EA115 Capacity Building' ? 'selected' : '' }}>
                                        EA115 Capacity Building
                                    </option>
                                    <option value="EA116 Research Project"
                                        {{ $t === 'EA116 Research Project' ? 'selected' : '' }}>
                                        EA116 Research Project
                                    </option>
                                    <option value="EA117 MOU" {{ $t === 'EA117 MOU' ? 'selected' : '' }}>
                                        EA117 MOU
                                    </option>
                                </select>
                            </label>

                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">รายละเอียด / หมายเหตุ / LINK</span>
                                <input name="outputs[{{ $i }}][project_output]" type="text"
                                    class="w-full border p-2 rounded"
                                    value="{{ old("outputs.$i.project_output", $out->project_output) }}"
                                    placeholder="เพิ่มเติม">
                            </label>

                            <div class="flex gap-2 w-full">
                                <label class="w-full relative">
                                    <span class="block text-sm text-gray-700 mb-1">วันที่ publish</span>
                                    <input name="outputs[{{ $i }}][year]" type="text"
                                        class="thai-datepicker w-full border p-2 rounded pl-10"
                                        value="{{ old("outputs.$i.year", $out->year) }}" placeholder="โปรดใส่วันที่">
                                    <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </label>
                                <button type="button" onclick="removeRow('output-{{ $i }}','output')"
                                    class="text-red-600 mb-2">✕</button>
                            </div>
                        </div>

                        {{-- จุดติดตั้งส่วนพิเศษ (EA101/EA103) --}}
                        <div class="special-mount">
                            @php $t = $out->type ?? ''; @endphp

                            {{-- EA101 --}}
                            @if (str_starts_with($t, 'EA101'))
                                <div class="ea101-section space-y-3 border rounded-xl p-4 bg-blue-50/50">
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
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
                                                <option value="" {{ !$q ? 'selected' : '' }} disabled>เลือก
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
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-blue-800">รายชื่อผู้เขียน (Authors)</span>
                                            <button type="button"
                                                class="add-author-btn px-3 py-1 rounded bg-blue-600 text-white"
                                                onclick="addAuthorRow(this)">+ เพิ่มผู้เขียน</button>
                                        </div>

                                        <div class="authors-container space-y-2" id="authors-{{ $i }}">
                                            @forelse($authors as $j => $auth)
                                                <div class="author-row grid grid-cols-1 md:grid-cols-3 gap-3">
                                                    <input type="hidden"
                                                        name="outputs[{{ $i }}][authors][{{ $j }}][id]"
                                                        value="{{ $auth->id }}">

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

                                                    <label class="w-full">
                                                        <span class="block text-sm text-gray-700 mb-1">ชื่อ-นามสกุล
                                                            ผู้เขียน <span class="required-mark">*</span></span>
                                                        <input
                                                            name="outputs[{{ $i }}][authors][{{ $j }}][name]"
                                                            type="text" list="userNames" class="w-full border p-2 rounded"
                                                            value="{{ old("outputs.$i.authors.$j.name", $auth->name) }}"
                                                            placeholder="คำนำหน้า ชื่อ-นามสกุล" required
                                                            oninvalid="this.setCustomValidity('กรุณากรอกชื่อ-นามสกุล ผู้เขียน')"
                                                            oninput="this.setCustomValidity('')">
                                                    </label>

                                                    <div class="flex items-end">
                                                        <button type="button"
                                                            class="px-3 py-2 rounded border text-red-600 border-red-300"
                                                            onclick="removeAuthorRow(this)">ลบผู้เขียน</button>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="author-row grid grid-cols-1 md:grid-cols-3 gap-3">
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
                                                        <span class="block text-sm text-gray-700 mb-1">คำนำหน้า ชื่อ-สกุล
                                                            ผู้เขียน <span class="required-mark">*</span></span>
                                                        <input name="outputs[{{ $i }}][authors][0][name]"
                                                            type="text" list="userNames" class="w-full border p-2 rounded"
                                                            placeholder="คำนำหน้า ชื่อ-นามสกุล" required
                                                            oninvalid="this.setCustomValidity('กรุณากรอกชื่อ-นามสกุล ผู้เขียน')"
                                                            oninput="this.setCustomValidity('')">
                                                    </label>
                                                    <div class="flex items-end">
                                                        <button type="button"
                                                            class="px-3 py-2 rounded border text-red-600 border-red-300"
                                                            onclick="removeAuthorRow(this)">ลบผู้เขียน</button>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- EA103 --}}
                            @if (str_starts_with($t, 'EA103'))
                                <div class="ea103-section space-y-3 border rounded-xl p-4 bg-amber-50/60">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-amber-800">Conferences</span>
                                        <button type="button"
                                            class="add-conf-btn px-3 py-1 rounded bg-amber-600 text-white"
                                            onclick="addConferenceRow(this)">+ เพิ่ม Conference</button>
                                    </div>

                                    @php $confs = $out->conferences ?? collect(); @endphp
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm border">
                                            <thead class="bg-amber-100">
                                                <tr>
                                                    <th class="px-2 py-2 text-left">ชื่อเวที/Conference</th>
                                                    <th class="px-2 py-2 text-left">ประเภทนำเสนอ</th>
                                                    <th class="px-2 py-2 text-left">สถานที่</th>
                                                    <th class="px-2 py-2 text-left">หมายเหตุ</th>
                                                    <th class="px-2 py-2 text-center">ลบ</th>
                                                </tr>
                                            </thead>
                                            <tbody class="conference-tbody" id="confs-{{ $i }}"
                                                data-conf-index="{{ max($confs->count() - 1, 0) }}">
                                                @forelse($confs as $k => $c)
                                                    <tr class="border-t conference-row">
                                                        <input type="hidden"
                                                            name="outputs[{{ $i }}][conferences][{{ $k }}][id]"
                                                            value="{{ $c->id }}">
                                                        <td class="px-2 py-2">
                                                            <input
                                                                name="outputs[{{ $i }}][conferences][{{ $k }}][name]"
                                                                class="w-56 border p-2 rounded"
                                                                value="{{ old("outputs.$i.conferences.$k.name", $c->name) }}"
                                                                placeholder="เช่น PMAC 2025">
                                                        </td>
                                                        <td class="px-2 py-2">
                                                            @php $pt = old("outputs.$i.conferences.$k.presentation_type", $c->presentation_type); @endphp
                                                            <select
                                                                name="outputs[{{ $i }}][conferences][{{ $k }}][presentation_type]"
                                                                class="w-44 border p-2 rounded">
                                                                <option value="">เลือก</option>
                                                                <option value="Oral"
                                                                    {{ $pt === 'Oral' ? 'selected' : '' }}>Oral
                                                                </option>
                                                                <option
                                                                    value="Poster"{{ $pt === 'Poster' ? 'selected' : '' }}>
                                                                    Poster</option>
                                                                <option value="Panel"
                                                                    {{ $pt === 'Panel' ? 'selected' : '' }}>Panel
                                                                </option>
                                                                <option value="Invited Talk"
                                                                    {{ $pt === 'Invited Talk' ? 'selected' : '' }}>
                                                                    Invited Talk</option>
                                                            </select>
                                                        </td>
                                                        <td class="px-2 py-2">
                                                            <input
                                                                name="outputs[{{ $i }}][conferences][{{ $k }}][location]"
                                                                class="w-44 border p-2 rounded"
                                                                value="{{ old("outputs.$i.conferences.$k.location", $c->location) }}"
                                                                placeholder="เมือง/ประเทศ">
                                                        </td>
                                                        <td class="px-2 py-2">
                                                            <input
                                                                name="outputs[{{ $i }}][conferences][{{ $k }}][note]"
                                                                class="w-56 border p-2 rounded"
                                                                value="{{ old("outputs.$i.conferences.$k.note", $c->note) }}"
                                                                placeholder="หมายเหตุ">
                                                        </td>
                                                        <td class="px-2 py-2 text-center">
                                                            <button type="button"
                                                                class="px-2 py-1 text-red-600 border border-red-300 rounded"
                                                                onclick="removeConferenceRow(this)">ลบ</button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr class="border-t conference-row">
                                                        <td class="px-2 py-2">
                                                            <input
                                                                name="outputs[{{ $i }}][conferences][0][name]"
                                                                class="w-56 border p-2 rounded"
                                                                placeholder="เช่น PMAC 2025">
                                                        </td>
                                                        <td class="px-2 py-2">
                                                            <select
                                                                name="outputs[{{ $i }}][conferences][0][presentation_type]"
                                                                class="w-44 border p-2 rounded">
                                                                <option value="">เลือก</option>
                                                                <option value="Oral">Oral</option>
                                                                <option value="Poster">Poster</option>
                                                                <option value="Panel">Panel</option>
                                                                <option value="Invited Talk">Invited Talk</option>
                                                            </select>
                                                        </td>
                                                        <td class="px-2 py-2">
                                                            <input
                                                                name="outputs[{{ $i }}][conferences][0][location]"
                                                                class="w-44 border p-2 rounded"
                                                                placeholder="เมือง/ประเทศ">
                                                        </td>
                                                        <td class="px-2 py-2">
                                                            <input
                                                                name="outputs[{{ $i }}][conferences][0][note]"
                                                                class="w-56 border p-2 rounded" placeholder="หมายเหตุ">
                                                        </td>
                                                        <td class="px-2 py-2 text-center">
                                                            <button type="button"
                                                                class="px-2 py-1 text-red-600 border border-red-300 rounded"
                                                                onclick="removeConferenceRow(this)">ลบ</button>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                
                @endforelse
            </div>

            <button type="button" onclick="addOutputRow()" class="mb-6 px-3 py-1 bg-green-600 text-white rounded">
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
            <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <p id="progressReportSummaryText" class="text-sm text-blue-700">
                            มีรายงานทั้งหมด {{ $progressCount }} รายการ
                            @if ($latestProgressReport)
                                · ล่าสุดวันที่ {{ $latestProgressDate }} โดย {{ $latestProgressReport->submitter_name ?: '-' }}
                            @endif
                        </p>
                        <p id="progressReportLatestTitle"
                            class="text-sm text-blue-900 font-semibold mt-1 {{ $latestProgressReport ? '' : 'hidden' }}">
                            หัวข้อรายงานล่าสุด: {{ $latestProgressTitle }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button"
                            data-progress-modal-open
                            data-url="{{ route('projects.progress_reports.create', ['project' => $project, 'modal' => 1]) }}"
                            data-title="เพิ่มความก้าวหน้าของการดำเนินงาน"
                            class="px-3 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                            + เพิ่มความก้าวหน้า
                        </button>
                        <button type="button"
                            data-progress-modal-open
                            data-url="{{ route('projects.progress_reports.index', ['project' => $project, 'modal' => 1]) }}"
                            data-title="จัดการรายการความก้าวหน้า"
                            class="px-3 py-2 rounded-lg border border-blue-300 bg-white text-blue-700 text-sm font-semibold hover:bg-blue-100">
                            จัดการรายการ
                        </button>
                        <button type="button"
                            id="progressEditLatestBtn"
                            data-progress-modal-open
                            data-url="{{ $latestProgressReport ? route('projects.progress_reports.edit', ['project' => $project, 'progress_report' => $latestProgressReport->id, 'modal' => 1]) : '' }}"
                            data-title="แก้ไขรายงาน: {{ $latestProgressTitle }}"
                            class="px-3 py-2 rounded-lg border border-blue-300 bg-white text-blue-700 text-sm font-semibold hover:bg-blue-100 {{ $latestProgressReport ? '' : 'hidden' }}">
                            แก้ไขรายงานล่าสุด
                        </button>
                    </div>
                </div>
            </div>

            <div id="progressReportModal" class="fixed inset-0 z-[90] hidden">
                <div class="absolute inset-0 bg-slate-900/50" data-progress-modal-close></div>
                <div class="relative mx-auto h-[100dvh] w-full rounded-none bg-white border border-slate-200 shadow-xl md:mt-3 md:h-[94vh] md:w-[98vw] md:max-w-[1700px] md:rounded-2xl">
                    <div class="flex items-center justify-between gap-2 px-4 py-3 border-b border-slate-200">
                        <h4 id="progressReportModalTitle" class="text-sm md:text-base font-semibold text-slate-900">ความก้าวหน้าของการดำเนินงาน</h4>
                        <div class="flex items-center gap-2">
                            <button type="button" data-progress-modal-close
                                class="px-3 py-1.5 rounded-lg bg-slate-800 text-white text-sm hover:bg-slate-900">
                                ปิด
                            </button>
                        </div>
                    </div>
                    <iframe id="progressReportModalFrame" src="about:blank" class="w-full h-[calc(100dvh-57px)] md:h-[calc(94vh-57px)] rounded-none md:rounded-b-2xl"></iframe>
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

        // let irbCodeIndex = {{ count($project->irbs ?? []) }};
        let irbCodeIndex = document.querySelectorAll('.irb-row').length;

        function addIRBCode() {
            const container = document.getElementById('irb-code-container');
            const id = `irb-${irbCodeIndex}`;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="irb-row grid grid-cols-1 md:grid-cols-3 gap-2 items-center p-3 border rounded-lg bg-white">
            <div class="w-full">
                <span class="block text-sm text-gray-700 mb-1">รหัส IRB ที่ <span class="irb-index">${irbCodeIndex + 1}</span> <span class="text-red-500">*</span></span>
                <input name="irbs[${irbCodeIndex}][code]" type="text" placeholder="ECxxxx" class="w-full border p-2 rounded" required
                    oninvalid="this.setCustomValidity('กรุณาระบุรหัส IRB')"
                    oninput="this.setCustomValidity('')">
            </div>
            <div class="w-full">
                <span class="block text-sm text-gray-700 mb-1">จาก (ชื่อหน่วยงาน) ของรหัส IRB ที่ <span class="irb-index">${irbCodeIndex + 1}</span> <span class="text-red-500">*</span></span>
                <input name="irbs[${irbCodeIndex}][source]" type="text" placeholder="ชื่อหน่วยงาน" class="w-full border p-2 rounded" required
                    oninvalid="this.setCustomValidity('กรุณาระบุหน่วยงาน')"
                    oninput="this.setCustomValidity('')">
            </div>
            <div class="w-full flex flex-col sm:flex-row gap-2 items-end">
                <div class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">วันที่หมดอายุ <span class="text-red-500">*</span></span>
                    <input name="irbs[${irbCodeIndex}][exp_date]" type="text" class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="เลือกวันที่" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกวันหมดอายุ IRB')"
                        oninput="this.setCustomValidity('')">
                    <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <button type="button" onclick="removeRow('${id}', 'irb')" class="text-red-600 mb-2">✕</button>
            </div>
        </div>
    `);

            irbCodeIndex++;
            reindexIRB();
            keepAddedRowOpen(id);

            flatpickr(`#${id} .thai-datepicker`, {
                locale: "th",
                dateFormat: "d-m-Y",
                altInput: true,
                altFormat: "j F Y",
                onReady: toBuddhist,
                onValueUpdate: toBuddhist,
                onChange: toBuddhist,
                onMonthChange: toBuddhist,
                onYearChange: toBuddhist,
            });
            const dateInput = document.querySelector(`#${id} .thai-datepicker`);
            bindDatepickerSummarySync(dateInput);
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
        <div id="${id}" class="copi-row grid grid-cols-1 md:grid-cols-2 gap-4 items-center p-3 border rounded-lg bg-white">
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">ชื่อ COPI คนที่ <span class="copi-index">${labelNum}</span> <span class="text-red-500">*</span></span>
                <input name="copis[${copiIndex}][name]" list="userNames" type="text"
                    class="w-full border p-2 rounded" placeholder="คำนำหน้า ชื่อ-นามสกุล" required
                    oninvalid="this.setCustomValidity('กรุณาระบุชื่อ COPI')"
                    oninput="this.setCustomValidity('')">
            </label>
            <div class="flex flex-col sm:flex-row gap-2 w-full">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">สังกัด COPI คนที่ <span class="copi-index">${labelNum}</span> <span class="text-red-500">*</span></span>
                    <input name="copis[${copiIndex}][affiliation]" type="text" list="copiAffiliationList"
                        class="w-full border p-2 rounded" placeholder="สังกัด COPI" required
                        oninvalid="this.setCustomValidity('กรุณาระบุสังกัด COPI')"
                        oninput="this.setCustomValidity('')">
                </label>
                <button type="button" onclick="removeRow('${id}', 'copi')" class="text-red-600 mt-6">✕</button>
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


        const FTE_ROLE_GROUPS = ['Scientific', 'Management', 'IT + Communication', 'Logistics + Admin'];
        const FTE_JOB_DETAILS = [
            'Researcher', 'Researcher Assistant', 'Project Manager',
            'Research Coordinator', 'นักวิชาการสารสนเทศ',
            'นักประชาสัมพันธ์', 'เจ้าหน้าที่บริหารงานทั่วไป'
        ];
        const FTE_TASK_DETAILS = @json($taskDetails);
        const FTE_GROUP_DROPDOWN = [`<option value="" disabled selected>เลือกกลุ่ม</option>`]
            .concat(FTE_ROLE_GROUPS.map((group) => `<option value="${group}">${group}</option>`))
            .join('');
        const FTE_JOB_DROPDOWN = [`<option value="" disabled selected>เลือกภาระงาน</option>`]
            .concat(FTE_JOB_DETAILS.map((job) => `<option value="${job}">${job}</option>`))
            .join('');
        const FTE_TASK_DROPDOWN = [`<option value="" disabled selected>เลือกภารกิจ</option>`]
            .concat(FTE_TASK_DETAILS.map((task) => `<option value="${task}">${task}</option>`))
            .join('');
        let fteNextIndex = document.querySelectorAll('#fte-container .fte-row').length;

        function addFTERow() {
            if (typeof window.validateFteBeforeAdd === 'function' && !window.validateFteBeforeAdd({
                    showAlert: true
                })) {
                return;
            }

            const container = document.getElementById('fte-container');
            const index = fteNextIndex++;
            const id = `fte-${index}`;

            ensureFteDatalist();

            container.insertAdjacentHTML('beforeend', `
<div id="${id}" class="fte-row p-4 border rounded-lg bg-white shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

        <div class="md:col-span-9 space-y-4">

            <!-- ชื่อ + กลุ่ม -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="w-full">
                    <span class="block text-sm font-semibold text-gray-700 mb-1">
                        <span class="fte-index text-xl font-extrabold text-blue-700">${index + 1}</span>. ชื่อ-นามสกุล <span class="text-red-500">*</span>
                    </span>

                    <input list="${FTE_DATALIST_ID}"
                        name="fte_roles[${index}][name]"
                        class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500"
                        placeholder="พิมพ์หรือเลือกชื่อ"
                        required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกหรือพิมพ์ชื่อผู้ใช้จากรายการ')"
                                    oninput="this.setCustomValidity('')">
                </label>

                <label class="w-full">
                    <span class="block text-sm font-semibold text-gray-700 mb-1">กลุ่ม</span>
                    <select name="fte_roles[${index}][role_group]" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกกลุ่ม')"
                        oninput="this.setCustomValidity('')">
                        ${FTE_GROUP_DROPDOWN}
                    </select>
                </label>
            </div>

            <!-- ภาระงาน + วันที่ -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <label class="w-full">
                    <span class="block text-sm font-semibold text-gray-700 mb-1">ภาระงาน <span class="text-red-500">*</span></span>
                    <select name="fte_roles[${index}][job_detail]" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกภาระงาน')"
                        oninput="this.setCustomValidity('')">
                        ${FTE_JOB_DROPDOWN}
                    </select>
                </label>

                <label class="w-full relative">
                    <span class="block text-sm font-semibold text-gray-700 mb-1">วันเริ่มต้นภาระงาน <span class="text-red-500">*</span></span>
                    <input name="fte_roles[${index}][start_date]"
                        type="text"
                        class="fte-shared-datepicker w-full border p-2 rounded pl-10 bg-white cursor-pointer"
                        data-date-role="start"
                        placeholder="โปรดใส่วันที่"
                        readonly
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
                    <span class="block text-sm font-semibold text-gray-700 mb-1">วันสิ้นสุดภาระงาน <span class="text-red-500">*</span></span>
                    <input name="fte_roles[${index}][end_date]"
                        type="text"
                        class="fte-shared-datepicker w-full border p-2 rounded pl-10 bg-white cursor-pointer"
                        data-date-role="end"
                        placeholder="โปรดใส่วันที่"
                        readonly
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
                <span class="block text-sm font-semibold text-gray-700 mb-1">ชั่วโมง (Hour) <span class="text-red-500">*</span></span>
                <input type="number"
                    step="0.1"
                    min="0"
                    name="fte_roles[${index}][hours]"
                    class="fte-hours w-full border p-2 rounded text-right"
                    placeholder="0.0"
                    required
                    oninvalid="this.setCustomValidity('กรุณาระบุชั่วโมงการทำงาน')"
                    oninput="this.setCustomValidity('')">
            </label>

            <!-- รายละเอียดภารกิจ -->
            <label class="w-full">
                <span class="block text-sm font-semibold text-gray-700 mb-1">รายละเอียดภารกิจ <span class="text-red-500">*</span></span>
                <select name="fte_roles[${index}][task_detail]"
                    class="w-full border p-2 rounded"
                    required
                    oninvalid="this.setCustomValidity('กรุณาเลือกรายละเอียดภารกิจ')"
                    oninput="this.setCustomValidity('')">
                    ${FTE_TASK_DROPDOWN}
                </select>
            </label>

        </div>

        <div class="md:col-span-3 space-y-4">

            <!-- FTE ผู้วิจัย คำนวณอัตโนมัติ -->
            <label class="block p-4 border rounded-xl bg-blue-50">
                <span class="block text-sm font-bold text-blue-800 mb-2">FTE จากชั่วโมง</span>
                <input name="fte_roles[${index}][fte_value]"
                    type="number"
                    step="0.00001"
                    min="0" max="1"
                    placeholder="0.00000"
                    class="fte-value w-full border p-3 rounded text-right bg-gray-100"
                    readonly>
            </label>

            <!-- FTE ผู้บริหาร -->
            <label class="block p-4 border rounded-xl bg-emerald-50">
                <span class="block text-sm font-bold text-emerald-800 mb-2">FTE (ผู้บริหาร)</span>
                <input name="fte_roles[${index}][fte_value_research]"
                    type="number"
                    step="0.01"
                    min="0" max="1"
                    placeholder="0.00"
                    class="w-full border p-3 rounded text-right">
            </label>

            <button type="button"
                onclick="removeRow('${id}', 'fte')"
                class="w-full py-2 border rounded-lg text-red-600 hover:bg-red-50">
                ✕ ลบผู้มีส่วนร่วมนี้
            </button>
        </div>

    </div>
</div>
`);

            const row = document.getElementById(id);
            const rowNumber = container.querySelectorAll('.fte-row').length;
            setFteRowIndex(row, rowNumber);
            keepAddedRowOpen(id);
            window.handleFteRowAdded?.(id);
        }

        document.addEventListener("DOMContentLoaded", function() {
            reindexFTE();
        });

        function setFteRowIndex(row, index) {
            const span = row?.querySelector('.fte-index');
            if (span) span.textContent = index;
            const summaryIndex = row?.querySelector('.fte-summary-index');
            if (summaryIndex) summaryIndex.textContent = index;
        }

        function reindexFTE(startIndex = 0) {
            const rows = document.querySelectorAll('#fte-container .fte-row');
            rows.forEach((row, index) => {
                if (index < startIndex) return;
                setFteRowIndex(row, index + 1);
            });
        }

        function getFteContainer() {
            return document.getElementById('fte-container');
        }

        function getFteFieldElement(row, field) {
            if (!row) return null;

            const selector = `[name^="fte_roles["][name$="[${field}]"]`;
            const direct = row.querySelector(selector);
            if (direct) return direct;

            const host = row._fteBodyHost;
            const hostField = host?.querySelector?.(selector);
            if (hostField) return hostField;

            const fragment = row._fteBodyFragment;
            const fragmentField = fragment?.querySelector?.(selector);
            if (fragmentField) return fragmentField;

            return null;
        }

        function getFteFieldValue(row, field) {
            return getFteFieldElement(row, field)?.value?.trim() || '';
        }

        function getFteSerializedValue(row, field, {
            preferIso = false
        } = {}) {
            const input = getFteFieldElement(row, field);
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

            return Array.from(container.querySelectorAll('.fte-row')).map((row) => {
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
            const taskDetail = getFteFieldValue(row, 'task_detail');
            const hours = getFteFieldValue(row, 'hours');
            const fteValue = getFteFieldValue(row, 'fte_value');

            const meta = [roleGroup, jobDetail].filter(Boolean).join(' / ') || 'ยังไม่ได้เลือกกลุ่มหรือภาระงาน';
            const task = taskDetail || 'ยังไม่ได้เลือกรายละเอียดภารกิจ';
            const metrics = [
                hours ? `${hours} hr` : '0 hr',
                fteValue ? `FTE ${fteValue}` : 'FTE 0.00000000'
            ].join(' | ');

            return {
                name,
                meta,
                task,
                metrics
            };
        }

        function updateFteRowSummary(row) {
            if (!row) return;
            const summary = getFteSummaryParts(row);
            const nameEl = row.querySelector('.fte-summary-name');
            const metaEl = row.querySelector('.fte-summary-meta');
            const taskEl = row.querySelector('.fte-summary-task');
            const metricsEl = row.querySelector('.fte-summary-metrics');

            if (nameEl) nameEl.textContent = summary.name;
            if (metaEl) metaEl.textContent = summary.meta;
            if (taskEl) taskEl.textContent = summary.task;
            if (metricsEl) metricsEl.textContent = summary.metrics;
        }

        function mountFteRowBody(row) {
            if (!row || row.dataset.fteBodyMounted === '1') return;
            const host = row._fteBodyHost;
            const fragment = row._fteBodyFragment;
            if (!host || !fragment) return;

            host.hidden = false;
            host.appendChild(fragment);
            row._fteBodyFragment = document.createDocumentFragment();
            row.dataset.fteBodyMounted = '1';
            primeLazyThaiDatepickers(host);
        }

        function unmountFteRowBody(row) {
            if (!row || row.dataset.fteBodyMounted !== '1') return;
            const host = row._fteBodyHost;
            if (!host) return;

            const fragment = document.createDocumentFragment();
            while (host.firstChild) {
                fragment.appendChild(host.firstChild);
            }

            row._fteBodyFragment = fragment;
            row.dataset.fteBodyMounted = '0';
            host.hidden = true;
        }

        function setFteRowOpen(row, open) {
            if (!row) return;

            if (open) {
                const container = getFteContainer();
                container?.querySelectorAll('.fte-row[data-collapsed="0"]').forEach((otherRow) => {
                    if (otherRow !== row) setFteRowOpen(otherRow, false);
                });
                mountFteRowBody(row);
                row.dataset.collapsed = '0';
                row.classList.remove('row-collapsed');
                row.querySelector('.fte-summary-toggle')?.setAttribute('aria-expanded', 'true');
            } else {
                updateFteRowSummary(row);
                unmountFteRowBody(row);
                row.dataset.collapsed = '1';
                row.classList.add('row-collapsed');
                row.querySelector('.fte-summary-toggle')?.setAttribute('aria-expanded', 'false');
            }
        }

        function hydrateFteRow(row, {
            open = false
        } = {}) {
            if (!row || row.dataset.fteHydrated === '1') {
                if (open) setFteRowOpen(row, true);
                return;
            }

            const bodyNodes = Array.from(row.childNodes);
            row.textContent = '';

            const summary = document.createElement('div');
            summary.className = 'fte-summary-shell flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3';
            summary.innerHTML = `
                <button type="button" class="fte-summary-toggle flex-1 text-left" aria-expanded="false">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-700">ผู้มีส่วนร่วมคนที่ <span class="fte-summary-index">1</span></div>
                            <div class="fte-summary-name truncate text-base font-bold text-slate-900"></div>
                            <div class="fte-summary-meta mt-1 text-sm text-slate-600"></div>
                            <div class="fte-summary-task mt-1 text-sm text-slate-500"></div>
                            <div class="fte-summary-metrics mt-2 text-xs font-medium text-slate-500"></div>
                        </div>
                        <div class="ml-2 shrink-0 text-xs font-semibold text-blue-700">แก้ไข</div>
                    </div>
                </button>
                <button type="button" class="fte-summary-delete shrink-0 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                    ลบ
                </button>
            `;

            const bodyHost = document.createElement('div');
            bodyHost.className = 'fte-editor-body mt-4';
            bodyNodes.forEach((node) => bodyHost.appendChild(node));

            row.append(summary, bodyHost);
            row._fteBodyHost = bodyHost;
            row._fteBodyFragment = document.createDocumentFragment();
            row.dataset.fteHydrated = '1';

            summary.querySelector('.fte-summary-toggle')?.addEventListener('click', () => {
                const nextOpen = row.dataset.collapsed !== '0';
                setFteRowOpen(row, nextOpen);
            });

            summary.querySelector('.fte-summary-delete')?.addEventListener('click', (event) => {
                event.stopPropagation();
                removeRow(row.id, 'fte');
            });

            updateFteRowSummary(row);
            setFteRowOpen(row, open);
        }

        function initFteEditorWindowing() {
            const container = getFteContainer();
            if (!container || container.dataset.fteWindowingReady === '1') return;
            container.dataset.fteWindowingReady = '1';

            const rows = Array.from(container.querySelectorAll('.fte-row'));
            const firstRow = rows.shift();
            if (firstRow) {
                hydrateFteRow(firstRow, {
                    open: true
                });
            }

            const chunkSize = 12;
            const hydrateChunk = () => {
                const slice = rows.splice(0, chunkSize);
                slice.forEach((row) => hydrateFteRow(row, {
                    open: false
                }));
                if (rows.length > 0) {
                    if (typeof window.requestIdleCallback === 'function') {
                        window.requestIdleCallback(hydrateChunk, {
                            timeout: 120
                        });
                    } else {
                        window.setTimeout(hydrateChunk, 16);
                    }
                }
            };

            if (rows.length > 0) {
                if (typeof window.requestIdleCallback === 'function') {
                    window.requestIdleCallback(hydrateChunk, {
                        timeout: 120
                    });
                } else {
                    window.setTimeout(hydrateChunk, 16);
                }
            }

            container.addEventListener('input', (event) => {
                const row = event.target.closest('.fte-row');
                if (!row) return;
                updateFteRowSummary(row);
            });

            container.addEventListener('change', (event) => {
                const row = event.target.closest('.fte-row');
                if (!row) return;
                updateFteRowSummary(row);
            });

            const form = document.getElementById('projectForm');
            form?.addEventListener('submit', () => {
                if (document.getElementById('fteRolesPayload')) return;
                container.querySelectorAll('.fte-row').forEach((row) => mountFteRowBody(row));
            }, true);
        }


        /* =========================================================
           JS: Output + EA101 + Authors (index-safe, non-overwrite)
           ========================================================= */


        let outputIndex = document.querySelectorAll('#output-container .output-row').length || 0;

        // เก็บตัวนับ nested ต่อ output แยกกัน
        const authorIndex = {}; // authors ต่อ output
        const confIndex = {}; // conferences ต่อ output

        // ---------- Bootstrap counter จาก DOM ที่ render มาแล้ว ----------
        document.querySelectorAll('#output-container .output-row').forEach(row => {
            const idx = row.getAttribute('data-index');

            // authors
            const aWrap = row.querySelector('.authors-container');
            authorIndex[idx] = aWrap ? aWrap.querySelectorAll('.author-row').length : 0;

            // conferences
            const cTbody = row.querySelector('.conference-tbody');
            confIndex[idx] = cTbody ? cTbody.querySelectorAll('.conference-row').length : 0;
        });

        // ---------- Add Output ----------
        function addOutputRow() {
            const idx = outputIndex;
            const id = `output-${idx}`;

            document.getElementById('output-container').insertAdjacentHTML('beforeend', `
    <div id="${id}" class="output-row space-y-2 border p-4 rounded-lg bg-gray-50" data-index="${idx}">
      <input type="hidden" name="outputs[${idx}][id]" value="">
      <label class="block w-full">
        <span class="block text-sm text-gray-700 mb-1">
          ชื่อผลงานที่ <span class="output-index">${idx + 1}</span> <span class="text-red-500">*</span>
        </span>
        <input name="outputs[${idx}][title]" type="text" class="w-full border p-2 rounded" 
        required
        oninvalid="this.setCustomValidity('กรุณากรอกชื่อผลงาน')"
        oninput="this.setCustomValidity('')"
         placeholder="ชื่อผลงาน">
      </label>

      <div class="grid grid-cols-3 gap-4 items-end">
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
            <option value="EA115 Capacity Building">EA115 Capacity Building</option>
            <option value="EA116 Research Project">EA116 Research Project</option>
            <option value="EA117 MOU">EA117 MOU</option>
          </select>
        </label>

        <label class="w-full">
          <span class="block text-sm text-gray-700 mb-1">รายละเอียด / หมายเหตุ / LINK</span>
          <input name="outputs[${idx}][project_output]" type="text" class="w-full border p-2 rounded" placeholder="เช่น ตีพิมพ์แล้ว / ลิงก์">
        </label>

        <div class="flex gap-2 w-full">
          <label class="w-full relative">
            <span class="block text-sm text-gray-700 mb-1">วันที่ publish</span>
            <input name="outputs[${idx}][year]" type="text" class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="โปรดใส่วันที่">
            <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </label>
          <button type="button" onclick="removeRow('${id}','output')" class="text-red-600 mb-2">✕</button>
        </div>
      </div>

      <!-- จุด mount สำหรับ section พิเศษ -->
      <div class="special-mount"></div>
    </div>
  `);

            // เริ่มนับ nested ใหม่เป็น 0
            authorIndex[idx] = 0;
            confIndex[idx] = 0;

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

                    if (!row.querySelector('.conference-row')) {
                        const addBtn = section.querySelector('.add-conf-btn');
                        if (addBtn) addConferenceRow(addBtn);
                    }
                }
                confIndex[idx] = row.querySelectorAll('.conference-row').length;

            } else {
                // ไม่ใช่ EA101/EA103 -> ล้างทิ้ง
                mount.innerHTML = '';
            }
        }


        /* ===== EA101: Authors ===== */
        function renderEA101Section(idx) {
            const wrap = document.createElement('div');
            wrap.className = 'ea101-section space-y-3 border rounded-xl p-4 bg-blue-50/50';
            wrap.innerHTML = `
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <label>
        <span class="block text-sm text-gray-700 mb-1">Journal</span>
        <input name="outputs[${idx}][journal]" class="w-full border p-2 rounded" placeholder="เช่น IEEE Access">
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">Quartile (Q)</span>
        <select name="outputs[${idx}][quartile]" class="w-full border p-2 rounded">
          <option value="" disabled selected>เลือก Quartile</option>
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

    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <span class="font-semibold text-blue-800">รายชื่อผู้เขียน (Authors)</span>
        <button type="button" class="add-author-btn px-3 py-1 rounded bg-blue-600 text-white" onclick="addAuthorRow(this)">+ เพิ่มผู้เขียน</button>
      </div>
      <div class="authors-container space-y-2" id="authors-${idx}"></div>
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

            authorsWrap.insertAdjacentHTML('beforeend', `
    <div class="author-row grid grid-cols-1 md:grid-cols-3 gap-3">
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
        <span class="block text-sm text-gray-700 mb-1">ชื่อผู้เขียน <span class="required-mark">*</span></span>
        <input type="text" name="outputs[${idx}][authors][${aIdx}][name]" list="userNames" class="w-full border p-2 rounded" placeholder="คำนำหน้า ชื่อ-สกุล" required
                                    oninvalid="this.setCustomValidity('กรุณากรอกชื่อผู้เขียน')"
                                    oninput="this.setCustomValidity('')">
      </label>
      <div class="flex items-end">
        <button type="button" onclick="removeAuthorRow(this)" class="px-3 py-2 rounded border text-red-600 border-red-300">ลบ</button>
      </div>
    </div>
  `);

            authorIndex[idx] = aIdx + 1;
        }

        function removeAuthorRow(btnEl) {
            btnEl.closest('.author-row')?.remove();
        }

        /* ===== EA103: Conferences ===== */
        function renderEA103Section(idx) {
            const wrap = document.createElement('div');
            wrap.className = 'ea103-section space-y-3 border rounded-xl p-4 bg-amber-50/60';
            wrap.innerHTML = `
    <div class="flex items-center justify-between">
      <span class="font-semibold text-amber-800">Conferences</span>
      <button type="button" class="add-conf-btn px-3 py-1 rounded bg-amber-600 text-white" onclick="addConferenceRow(this)">+ เพิ่ม Conference</button>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border">
        <thead class="bg-amber-100">
          <tr>
            <th class="px-2 py-2 text-left">ชื่อเวที/Conference</th>
            <th class="px-2 py-2 text-left">ประเภทนำเสนอ</th>
            <th class="px-2 py-2 text-left">สถานที่</th>
            <th class="px-2 py-2 text-left">หมายเหตุ</th>
            <th class="px-2 py-2 text-center">ลบ</th>
          </tr>
        </thead>
        <tbody class="conference-tbody" id="confs-${idx}" data-conf-index="0"></tbody>
      </table>
    </div>
  `;
            return wrap;
        }

        function addConferenceRow(btnEl) {
            const outputRow = btnEl.closest('.output-row');
            const idx = outputRow.getAttribute('data-index');
            const tbody = outputRow.querySelector(`#confs-${idx}`);
            if (!tbody) return;

            const cIdx = confIndex[idx] || 0;

            tbody.insertAdjacentHTML('beforeend', `
    <tr class="border-t conference-row">
      <td class="px-2 py-2">
        <input name="outputs[${idx}][conferences][${cIdx}][name]" class="w-56 border p-2 rounded" placeholder="เช่น PMAC 2025">
      </td>
      <td class="px-2 py-2">
        <select name="outputs[${idx}][conferences][${cIdx}][presentation_type]" class="w-44 border p-2 rounded">
          <option value="">เลือก</option>
          <option value="Oral">Oral</option>
          <option value="Poster">Poster</option>
          <option value="Panel">Panel</option>
          <option value="Invited Talk">Invited Talk</option>
        </select>
      </td>
      <td class="px-2 py-2">
        <input name="outputs[${idx}][conferences][${cIdx}][location]" class="w-44 border p-2 rounded" placeholder="เมือง/ประเทศ">
      </td>
      <td class="px-2 py-2">
        <input name="outputs[${idx}][conferences][${cIdx}][note]" class="w-56 border p-2 rounded" placeholder="หมายเหตุ">
      </td>
      <td class="px-2 py-2 text-center">
        <button type="button" class="px-2 py-1 text-red-600 border border-red-300 rounded" onclick="removeConferenceRow(this)">ลบ</button>
      </td>
    </tr>
  `);

            confIndex[idx] = cIdx + 1;
        }

        function removeConferenceRow(btn) {
            btn.closest('tr.conference-row')?.remove();
        }

        // ---------- ลบ Output + reindex ----------
        function removeRow(id, prefix) {
            document.getElementById(id)?.remove();
            reindexOutput();
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
                const cTbody = row.querySelector('.conference-tbody');
                if (cTbody) cTbody.id = `confs-${i}`;

                // นับใหม่ให้ตัวนับ
                authorIndex[i] = aWrap ? aWrap.querySelectorAll('.author-row').length : 0;
                confIndex[i] = cTbody ? cTbody.querySelectorAll('.conference-row').length : 0;
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
        });







        let coResearchIndex = {{ count($project->coResearches ?? []) }};

        function addCoResearchRow() {
            const container = document.getElementById('coresearch-container');
            const id = `coresearch-${coResearchIndex}`;
            const labelNum = coResearchIndex + 1;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="coresearch-row space-y-2 p-4 border rounded-lg bg-white">
            <label class="block w-full">
                <span class="block text-sm text-gray-700 mb-1">
                    ชื่อ Co-Researcher คนที่ <span class="coresearch-index">${labelNum}</span> <span class="text-red-500">*</span>
                </span>
                <input name="coresearch[${coResearchIndex}][name]" list="userNames" type="text"
                    class="w-full border p-2 rounded" placeholder="ยศ ชื่อ-นามสกุล" required
                    oninvalid="this.setCustomValidity('กรุณาระบุชื่อ Co-Researcher')"
                    oninput="this.setCustomValidity('')">
            </label>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <label class="col-span-12 md:col-span-5">
                    <span class="block text-sm text-gray-700 mb-1">สังกัด <span class="text-red-500">*</span></span>
                    <input name="coresearch[${coResearchIndex}][affiliation]" type="text" list="coresearchAffiliationList"
                        class="w-full border p-2 rounded" placeholder="ป สังกัด" required
                        oninvalid="this.setCustomValidity('กรุณาระบุสังกัด')"
                        oninput="this.setCustomValidity('')">
                </label>
                <label class="col-span-12 md:col-span-5">
                    <span class="block text-sm text-gray-700 mb-1">คนภายนอก / ภายใน <span class="text-red-500">*</span></span>
                    <select name="coresearch[${coResearchIndex}][unit_status]" class="w-full border p-2 rounded" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกสถานะหน่วยงาน')"
                                    oninput="this.setCustomValidity('')">
                        <option value="" disabled selected>กรุณาเลือก</option>
                        <option value="ภายในคณะแพทยศาสตร์ศิริราชพยาบาล">ภายในคณะแพทยศาสตร์ศิริราชพยาบาล</option>
                        <option value="ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล">ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล</option>
                        <option value="ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ">ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ</option>
                    </select>
                </label>
                <div class="col-span-12 md:col-span-2 flex justify-end md:pt-6">
                    <button type="button" onclick="removeRow('${id}', 'coresearch')" class="text-red-600 text-xl">✕</button>
                </div>
            </div>
        </div>
    `);

            coResearchIndex++;
            reindexCoResearch();
            keepAddedRowOpen(id);
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

        function removeRow(id, type) {
            const el = document.getElementById(id);
            const removedIndex = type === 'fte' && el?.parentElement ?
                Array.from(el.parentElement.children).indexOf(el) :
                -1;
            if (el && type === 'fte') {
                const idInput = el.querySelector('input[name^="fte_roles["][name$="[id]"]');
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
            }
            if (el) el.remove();
            if (type === 'output') reindexOutput();
            if (type === 'copi') reindexCopi?.();
            if (type === 'irb') reindexIRB?.();
            if (type === 'fte') reindexFTE?.(Math.max(0, removedIndex));
            if (type === 'coresearch') reindexCoResearch?.();
        }
    </script>

    <script>
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
            scope.querySelectorAll('input.thai-datepicker[name^="fte_roles["][name$="[start_date]"]').forEach((startInput) => {
                const endName = startInput.name.replace('[start_date]', '[end_date]');
                const endInput = document.querySelector(`input[name="${endName}"]`);
                bindEndDateMinThaiPicker(startInput, endInput, `fte_${startInput.name}`);
            });
        }

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('budgetInput');

            input.addEventListener('input', function() {
                let value = input.value.replace(/,/g, '');
                const parts = value.split('.');

                if (parts.length > 2) {
                    value = parts[0] + '.' + parts[1];
                }

                let [intPart, decimalPart] = value.split('.');
                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                input.value = decimalPart !== undefined ? `${intPart}.${decimalPart}` : intPart;
            });

            // ก่อน submit form ให้ลบ comma ออก
            input.form?.addEventListener('submit', () => {
                input.value = input.value.replace(/,/g, '');
            });
        });

        function initThaiDatepickers(scope = document, {
            bindPairs = true
        } = {}) {
            scope.querySelectorAll('.thai-datepicker').forEach((input) => {
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
                        input.setAttribute('aria-hidden', 'true');
                        input.tabIndex = -1;
                        input.style.display = 'none';
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

        function formatThaiBuddhistDate(date) {
            if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
            const day = date.getDate();
            const month = date.toLocaleString('th-TH', {
                month: 'long'
            });
            const year = date.getFullYear() + 543;
            return `${day} ${month} ${year}`;
        }

        function syncFteSharedDateInput(input) {
            if (!(input instanceof HTMLInputElement)) return null;
            const parsed = parseLocalDateInput(input.value || '') || parseLocalDateInput(input.dataset.isoValue || '');
            if (!parsed) {
                input.dataset.isoValue = '';
                return null;
            }

            const isoValue = toDmyCE(parsed);
            input.dataset.isoValue = isoValue;
            input.value = formatThaiBuddhistDate(parsed);
            input.setCustomValidity('');
            return parsed;
        }

        function initSharedFteDatepicker() {
            const sharedInput = document.getElementById('fteSharedDatepicker');
            if (!(sharedInput instanceof HTMLInputElement) || sharedInput._flatpickr) return;

            let activeTarget = null;
            const resolveSharedDateValue = (input) => {
                return syncFteSharedDateInput(input);
            };

            const picker = flatpickr(sharedInput, {
                locale: 'th',
                dateFormat: 'd-m-Y',
                prevArrow: '',
                nextArrow: '',
                disableMobile: true,
                clickOpens: false,
                allowInput: false,
                static: false,
                onReady(selectedDates, dateStr, instance) {
                    toBuddhist(selectedDates, dateStr, instance);
                },
                onValueUpdate: toBuddhist,
                onMonthChange: toBuddhist,
                onYearChange: toBuddhist,
                onChange(selectedDates) {
                    if (!(activeTarget instanceof HTMLInputElement)) return;
                    const selected = selectedDates?.[0] || null;
                    activeTarget.value = selected ? formatThaiBuddhistDate(selected) : '';
                    activeTarget.dataset.isoValue = selected ? toDmyCE(selected) : '';
                    activeTarget.setCustomValidity('');
                    activeTarget.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    activeTarget.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));

                    const row = activeTarget.closest('.fte-row');
                    const startInput = row?.querySelector('.fte-shared-datepicker[data-date-role="start"]');
                    const endInput = row?.querySelector('.fte-shared-datepicker[data-date-role="end"]');
                    if (activeTarget.dataset.dateRole === 'start' && startInput && endInput) {
                        const startDate = resolveSharedDateValue(startInput);
                        const endDate = resolveSharedDateValue(endInput);
                        if (startDate && endDate && normalizeDateOnly(endDate) < normalizeDateOnly(startDate)) {
                            endInput.value = '';
                            endInput.dataset.isoValue = '';
                            endInput.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                            endInput.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                        }
                    }
                }
            });

            const openSharedPicker = (target) => {
                if (!(target instanceof HTMLInputElement)) return;
                activeTarget = target;
                const row = target.closest('.fte-row');
                const startInput = row?.querySelector('.fte-shared-datepicker[data-date-role="start"]');
                const endInput = row?.querySelector('.fte-shared-datepicker[data-date-role="end"]');

                syncFteSharedDateInput(startInput);
                syncFteSharedDateInput(endInput);

                const currentDate = resolveSharedDateValue(target);
                const minDate = target.dataset.dateRole === 'end' ? resolveSharedDateValue(startInput) : null;
                const maxDate = target.dataset.dateRole === 'start' ? resolveSharedDateValue(endInput) : null;

                picker.set('positionElement', target);
                picker._positionElement = target;
                picker.set('minDate', minDate ? normalizeDateOnly(minDate) : null);
                picker.set('maxDate', maxDate ? normalizeDateOnly(maxDate) : null);
                picker.set('disable', [
                    ...(minDate ? [date => normalizeDateOnly(date) < normalizeDateOnly(minDate)] : []),
                    ...(maxDate ? [date => normalizeDateOnly(date) > normalizeDateOnly(maxDate)] : []),
                ]);
                picker.setDate(currentDate || null, false);
                picker.open();
                requestAnimationFrame(() => {
                    picker._positionCalendar?.();
                    toBuddhist(picker.selectedDates || [], picker.input?.value || '', picker);
                });
            };

            document.addEventListener('click', (event) => {
                const target = event.target.closest('.fte-shared-datepicker') ||
                    event.target.closest('.relative')?.querySelector('.fte-shared-datepicker');
                if (!(target instanceof HTMLInputElement)) return;
                openSharedPicker(target);
            });

            document.addEventListener('focusin', (event) => {
                const target = event.target.closest('.fte-shared-datepicker');
                if (!target) return;
                openSharedPicker(target);
            });

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || form.id !== 'projectForm') return;
                form.querySelectorAll('.fte-shared-datepicker').forEach((input) => {
                    if (!(input instanceof HTMLInputElement)) return;
                    syncFteSharedDateInput(input);
                    const isoValue = input.dataset.isoValue || '';
                    if (isoValue) {
                        input.value = isoValue;
                    }
                });
            }, true);

            window.addEventListener('pageshow', () => {
                document.querySelectorAll('.fte-shared-datepicker').forEach((input) => {
                    syncFteSharedDateInput(input);
                });
            });
        }

        function shouldLazyInitThaiDatepicker(input) {
            return Boolean(input?.closest?.('#fte-container .fte-row'));
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
                    const row = input.closest('.fte-row');
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

        function toBuddhist(selectedDates, dateStr, instance) {
            const altInput = instance.altInput;
            if (altInput && selectedDates.length > 0) {
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
            initSharedFteDatepicker();
            document.querySelectorAll('.fte-shared-datepicker').forEach((input) => {
                syncFteSharedDateInput(input);
            });
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
                const rowCount = form.querySelectorAll('#fte-container .fte-row').length;

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

            function revealFieldIfCollapsed(field) {
                if (!field || field.closest('template')) return false;

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

                return changed || field.offsetParent !== null;
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

                    const requiredFields = projectForm.querySelectorAll('[required]');
                    projectForm.querySelectorAll('[data-repeatable-row], .output-row, .fte-row, .copi-row, .coresearch-row, .irb-row')
                        .forEach(row => {
                            delete row.dataset.forceOpen;
                        });

                    requiredFields.forEach(field => {
                        if (field.disabled || field.type === 'hidden' || field.closest('template')) return;
                        if (field.offsetParent === null) {
                            const visibleNow = revealFieldIfCollapsed(field);
                            if (!visibleNow) return;
                        }

                        const val = field.value.trim();
                        const invalid = !val || !field.checkValidity();
                        const displayTarget = window.getProjectValidationDisplayTarget?.(field) || field;

                        if (invalid) {
                            isValid = false;
                            displayTarget.classList.add('border-red-500', 'bg-red-50');
                            if (field !== displayTarget) {
                                field.classList.add('border-red-500', 'bg-red-50');
                            }
                            if (field.name === 'planning_status') {
                                displayTarget.style.backgroundColor = '#fef2f2';
                                displayTarget.style.color = '#991b1b';
                            }
                            if (!firstInvalidField) {
                                firstInvalidField = displayTarget;
                                firstInvalidMessage = field.validationMessage || '';
                            }
                            const row = field.closest('[data-repeatable-row], .output-row, .fte-row, .copi-row, .coresearch-row, .irb-row') ||
                                displayTarget.closest('[data-repeatable-row], .output-row, .fte-row, .copi-row, .coresearch-row, .irb-row');
                            if (row) invalidRows.add(row);
                            const group = field.closest('[data-repeatable]') || displayTarget.closest('[data-repeatable]');
                            if (group) {
                                const id = group.dataset.id || '';
                                if (sectionNameById[id]) invalidSections.add(sectionNameById[id]);
                            }
                        } else {
                            displayTarget.classList.remove('border-red-500', 'bg-red-50');
                            if (field !== displayTarget) {
                                field.classList.remove('border-red-500', 'bg-red-50');
                            }
                            if (field.name === 'planning_status') {
                                updateStatusColor(displayTarget);
                            }
                        }
                    });

                    if (!isValid) {
                        window.syncFteTableValidationUi?.({
                            ensureRender: false
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
                $name = trim($u->prefix . ' ' . $u->name);
                return "<option value=\"{$name}\"></option>";
            })->implode('') !!}`;

        window.coresearchOptionsHtml = `{!! collect($allUsers)->map(function ($u) {
                $name = trim($u->prefix . ' ' . $u->name);
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
                const submitter = (summary.submitter || '-').toString().trim() || '-';
                const reportTitle = (summary.title || '').toString().trim();
                const editUrl = (summary.editUrl || '').toString().trim();

                if (summaryTextEl) {
                    let text = `มีรายงานทั้งหมด ${count} รายการ`;
                    if (latestDate) {
                        text += ` · ล่าสุดวันที่ ${latestDate} โดย ${submitter}`;
                    }
                    summaryTextEl.textContent = text;
                }

                if (latestTitleEl) {
                    if (reportTitle) {
                        latestTitleEl.textContent = `หัวข้อรายงานล่าสุด: ${reportTitle}`;
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
                if (!payload || payload.type !== 'progress-report:updated') return;
                if (Number(payload.projectId) !== currentProjectId) return;

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

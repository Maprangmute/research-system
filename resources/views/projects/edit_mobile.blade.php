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
    window.enableRowCollapseSummaryOnMobile = true;
</script>

<style>
    [x-cloak] {
        display: none !important;
    }

    .required-mark {
        color: #ef4444;
        font-weight: 600;
    }
</style>


@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .lux-mobile-form {
            max-width: 100%;
            margin: 0.75rem;
            padding: 1rem;
            border-radius: 16px;
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
        }

        .lux-mobile-form .btn,
        .lux-mobile-form button {
            border-radius: 10px;
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

    <div class="project-form lux-mobile-form max-w-6xl mx-auto mt-6 bg-white border border-slate-200 shadow-sm rounded-2xl p-6">

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
                            {{ old('project_list', $project->project_list) == 'โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ' ? 'selected' : '' }}>
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
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">วันที่โครงการเริ่ม (ทีม)</span>

                    <div class="relative">
                        <!-- ไอคอนปฏิทิน -->
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>

                        <!-- Input -->
                        <input name="team_start_date" value="{{ old('team_start_date', $project->team_start_date) }}"
                            type="text" class="thai-datepicker w-full border p-2 rounded pl-10"
                            placeholder="กรุณาเลือกวันที่เริ่มงานของทีม">
                    </div>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">วันที่สิ้นสุดทำ (ทีม)</span>

                    <div class="relative">
                        <!-- ไอคอนปฏิทิน -->
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>

                        <!-- ช่องกรอกวันที่ -->
                        <input name="team_end_date" value="{{ old('team_end_date', $project->team_end_date) }}"
                            type="text" class="thai-datepicker w-full border p-2 rounded pl-10"
                            placeholder="กรุณาเลือกวันที่สิ้นสุดการทำงานของทีม">
                    </div>
                </label>


                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BA001 - วันที่เริ่มเขียน Proposal (เดือน/ปี)</span>

                    <div class="relative">
                        <!-- ไอคอนปฏิทิน -->
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>

                        <!-- ช่องกรอก -->
                        <input name="proposal_date" value="{{ old('proposal_date', $project->proposal_date) }}"
                            type="text" class="thai-datepicker w-full border p-2 rounded pl-10"
                            placeholder="กรุณาเลือกวันที่เริ่มเขียน Proposal">
                    </div>
                </label>


                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BC101 - วันที่สัญญาอนุมัติ</span>

                    <div class="relative">
                        <!-- ไอคอนปฏิทิน -->
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>

                        <!-- ช่องกรอก -->
                        <input name="contract_date" value="{{ old('contract_date', $project->contract_date) }}"
                            type="text" class="thai-datepicker w-full border p-2 rounded pl-10"
                            placeholder="กรุณาเลือกวันที่อนุมัติสัญญา">
                    </div>
                </label>


                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BC102 - วันที่โครงการเริ่ม (ตามสัญญา)</span>

                    <div class="relative">
                        <!-- ไอคอนปฏิทิน -->
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>

                        <!-- ช่องกรอก -->
                        <input name="start_date" value="{{ old('start_date', $project->start_date) }}" type="text"
                            class="thai-datepicker w-full border p-2 rounded pl-10"
                            placeholder="กรุณาเลือกวันที่เริ่มตามสัญญา">
                    </div>
                </label>


                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">วันที่สิ้นสุด (ตามสัญญา)</span>

                    <div class="relative">
                        <!-- ไอคอนปฏิทิน -->
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>

                        <!-- ช่องกรอกวันที่ -->
                        <input name="end_date" value="{{ old('end_date', $project->end_date) }}" type="text"
                            class="thai-datepicker w-full border p-2 rounded pl-10"
                            placeholder="กรุณาเลือกวันที่สิ้นสุดตามสัญญา">
                    </div>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">Project Status</span>
                    <select name="planning_status" id="planning_status" class="w-full border p-2 rounded font-semibold"
                        onchange="updateStatusColor(this)">
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
                                <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
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

            <div x-data="{ showFTE: false, currentTab: 'external', setTab(tab) { this.currentTab = tab } }" x-effect="document.body.classList.toggle('overflow-hidden', showFTE)">

                <!-- Header -->
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-semibold text-lg text-blue-700">D: รายชื่อผู้มีส่วนร่วม</h3>
                    <a href="#" @click.prevent="showFTE = true" class="text-sm text-blue-600 hover:underline">
                        รายละเอียดค่า FTE
                    </a>
                </div>

                <!-- Modal -->
                <div x-show="showFTE" x-cloak x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm"
                    @click.self="showFTE = false" x-trap.noscroll="showFTE" x-init="...">


                    <!-- Modal box -->
                    <div x-ref="fteModal"
                        class="relative w-full max-w-6xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-xl ring-1 ring-gray-200"
                        @click.stop>
                        <!-- Modal Header -->
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-white to-blue-50 sticky top-0 z-10">
                            <div class="flex items-center space-x-2">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h6m6-6v12m6-6h-6" />
                                </svg>
                                <h2 class="text-lg md:text-xl font-bold text-gray-800">ข้อมูลผู้มีส่วนร่วม (FTE)</h2>
                            </div>
                            <button @click="showFTE = false"
                                class="text-gray-500 hover:text-red-500 hover:bg-red-100 rounded-full p-2 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Tabs -->
                        <div
                            class="flex justify-start px-6 pt-3 space-x-4 border-b text-sm font-medium bg-white sticky top-[60px] z-10">
                            <button @click.prevent="setTab('external')"
                                :class="currentTab === 'external'
                                    ?
                                    'text-blue-600 border-b-2 border-blue-600 font-semibold' :
                                    'text-gray-500 hover:text-blue-500 border-b-2 border-transparent'">
                                ทุนภายนอก
                            </button>
                            <button @click.prevent="setTab('internal')"
                                :class="currentTab === 'internal'
                                    ?
                                    'text-blue-600 border-b-2 border-blue-600 font-semibold' :
                                    'text-gray-500 hover:text-blue-500 border-b-2 border-transparent'">
                                ทุนภายใน
                            </button>
                            <button @click.prevent="setTab('cohort')"
                                :class="currentTab === 'cohort'
                                    ?
                                    'text-blue-600 border-b-2 border-blue-600 font-semibold' :
                                    'text-gray-500 hover:text-blue-500 border-b-2 border-transparent'">
                                COHORT
                            </button>
                        </div>

                        <!-- Tab Content -->
                        <div class="p-6 space-y-4 text-sm bg-white">
                            <div x-show="currentTab === 'external'" x-transition>
                                @include('projects.table.external')
                            </div>
                            <div x-show="currentTab === 'internal'" x-transition>
                                @include('projects.table.internal')
                            </div>
                            <div x-show="currentTab === 'cohort'" x-transition>
                                @include('projects.table.cohort')
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 text-right border-t">
                            <button @click="showFTE = false"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                ปิดหน้าต่าง
                            </button>
                        </div>

                    </div>
                </div>

                <!-- ========== LIST OF FTE ROWS ========== -->
                <div id="fte-container" class="space-y-4 mb-4">

                    @foreach ($project->fteRoles ?? [] as $i => $fte)
                        <div id="fte-{{ $i }}" class="fte-row p-4 border rounded-lg bg-white shadow-sm">

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

                                <!-- ===== Left Column ===== -->
                                <div class="md:col-span-9 space-y-4">

                                    <!-- ชื่อ + กลุ่ม -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                        <label class="w-full">
                                            <span class="block text-sm font-semibold">ชื่อ-นามสกุล <span class="text-red-500">*</span></span>
                                            <input list="fteUsers" name="fte_roles[{{ $i }}][name]"
                                                value="{{ $fte->name }}" class="w-full border p-2 rounded" required
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
                                                    <option value="{{ $group }}" @selected($fte->role_group == $group)>
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
                                                    <option value="{{ $job }}" @selected($fte->job_detail == $job)>
                                                        {{ $job }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label class="w-full relative">
                                            <span class="block text-sm font-semibold">วันเริ่มต้นภาระงาน <span class="text-red-500">*</span></span>
                                            <input name="fte_roles[{{ $i }}][start_date]" type="text"
                                                value="{{ $fte->start_date }}"
                                                class="thai-datepicker w-full border p-2 rounded pl-10" required
                                                oninvalid="this.setCustomValidity('กรุณาเลือกวันเริ่มต้นภาระงาน')"
                                                oninput="this.setCustomValidity('')">
                                            <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
                                                fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </label>

                                        <label class="w-full relative">
                                            <span class="block text-sm font-semibold">วันสิ้นสุดภาระงาน <span class="text-red-500">*</span></span>
                                            <input name="fte_roles[{{ $i }}][end_date]" type="text"
                                                value="{{ $fte->end_date }}"
                                                class="thai-datepicker w-full border p-2 rounded pl-10" required
                                                oninvalid="this.setCustomValidity('กรุณาเลือกวันสิ้นสุดภาระงาน')"
                                                oninput="this.setCustomValidity('')">
                                            <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
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
                                            name="fte_roles[{{ $i }}][hours]" value="{{ $fte->hours }}"
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

                                            <option value="" disabled>เลือกภารกิจ</option>

                                            <!-- ⬇ Dropdown ไม่ย่อ รายการอยู่ครบ -->
                                            <option value="ติดต่อประสานงาน">ติดต่อประสานงาน</option>
                                            <option value="ที่ปรึกษาโครงการ">ที่ปรึกษาโครงการ</option>
                                            <option value="review literature">review literature</option>
                                            <option value="เขียนproposal">เขียนproposal</option>
                                            <option value="ดำเนินการเอกสารขอirb/submit irb">ดำเนินการเอกสารขอirb/submit irb
                                            </option>
                                            <option value="ดำเนินการเอกสารขอทุน/submit ทุน">ดำเนินการเอกสารขอทุน/submit ทุน
                                            </option>
                                            <option value="เก็บข้อมูล">เก็บข้อมูล</option>
                                            <option value="วิเคราะห์ข้อมูล">วิเคราะห์ข้อมูล</option>
                                            <option value="เขียนเล่มรายงาน">เขียนเล่มรายงาน</option>
                                            <option value="เขียนmanuscript">เขียนmanuscript</option>
                                            <option value="submit journal">submit journal</option>
                                            <option value="เบิกเงินรางวัลตีพิมพ์/ค่าตอบแทนตีพิมพ์">เบิกเงินรางวัลตีพิมพ์ /
                                                ค่าตอบแทนตีพิมพ์</option>
                                            <option value="นำเสนอผลงานวิจัย Oral/Poster presentation">นำเสนอผลงานวิจัย
                                                Oral/Poster presentation</option>
                                            <option value="ประชาสัมพันธ์-เบิกงบประมาณทุน">ประชาสัมพันธ์ – เบิกงบประมาณทุน
                                            </option>

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
                                            value="{{ $fte->fte_value }}"
                                            class="fte-value w-full border p-3 rounded bg-gray-100 text-right" readonly>
                                    </label>

                                    <!-- FTE (ผู้บริหาร) -->
                                    <label class="block p-4 border rounded-xl bg-emerald-50">
                                        <span class="block text-sm font-bold text-emerald-800 mb-2">FTE (ผู้บริหาร)</span>
                                        <input type="number" step="0.01" min="0" max="1"
                                            name="fte_roles[{{ $i }}][fte_value_research]"
                                            value="{{ $fte->fte_value_research }}"
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
                                    <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
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
        get show() { return (this.dirty || this.scrolled) && !this.nearBottom }
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
    }" x-show="show" x-transition.opacity
        class="fixed inset-x-0 bottom-0 md:inset-auto md:right-6 md:bottom-6 md:left-auto z-50" style="display:none"
        role="region" aria-live="polite">
        <div class="mx-3 mb-3 md:mx-0 md:mb-0">
            <div class="max-w-[520px] md:min-w-[420px] bg-white/90 backdrop-blur border shadow-xl rounded-2xl px-4 py-3">
                <div class="flex items-center gap-3">
                    <a href="{{ route('projects.index') }}"
                        class="hidden md:inline-flex items-center text-blue-700 font-semibold hover:text-blue-900 hover:underline">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        กลับไปยังรายการโครงการ
                    </a>

                    <div class="ml-auto flex items-center gap-2">
                        <span x-show="dirty" x-transition.opacity
                            class="text-xs text-amber-700 bg-amber-100 px-2 py-1 rounded-full">
                            มีการแก้ไขที่ยังไม่บันทึก
                        </span>

                        <button type="button" @click="document.getElementById('projectForm')?.requestSubmit()"
                            class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-full shadow">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2"
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
                    <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
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
                disableMobile: true,
                onReady: (selectedDates, dateStr, instance) => {
                    const input = instance.input;
                    const altInput = instance.altInput;
                    if (altInput) {
                        altInput.readOnly = true;
                        altInput.className = input.className;
                    }
                    if (input) input.classList.add('flatpickr-hidden');
                    toBuddhist(selectedDates, dateStr, instance);
                },
                onChange: toBuddhist,
                onMonthChange: toBuddhist,
                onYearChange: toBuddhist,
            });
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





        function addFTERow() {
            const container = document.getElementById('fte-container');
            const index = container.querySelectorAll('.fte-row').length;
            const id = `fte-${index}`;

            ensureFteDatalist();

            const roleGroups = ['Scientific', 'Management', 'IT + Communication', 'Logistics + Admin'];
            const jobDetails = [
                'Researcher', 'Researcher Assistant', 'Project Manager',
                'Research Coordinator', 'นักวิชาการสารสนเทศ',
                'นักประชาสัมพันธ์', 'เจ้าหน้าที่บริหารงานทั่วไป'
            ];

            const groupDropdown = [`<option value="" disabled selected>เลือกกลุ่ม</option>`]
                .concat(roleGroups.map(g => `<option value="${g}">${g}</option>`))
                .join('');

            const jobDropdown = [`<option value="" disabled selected>เลือกภาระงาน</option>`]
                .concat(jobDetails.map(j => `<option value="${j}">${j}</option>`))
                .join('');

            container.insertAdjacentHTML('beforeend', `
<div id="${id}" class="fte-row p-4 border rounded-lg bg-white shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

        <div class="md:col-span-9 space-y-4">

            <!-- ชื่อ + กลุ่ม -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="w-full">
                    <span class="block text-sm font-semibold text-gray-700 mb-1">
                        <span class="fte-index">${index + 1}</span>. ชื่อ-นามสกุล <span class="text-red-500">*</span>
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
                        ${groupDropdown}
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
                        ${jobDropdown}
                    </select>
                </label>

                <label class="w-full relative">
                    <span class="block text-sm font-semibold text-gray-700 mb-1">วันเริ่มต้นภาระงาน <span class="text-red-500">*</span></span>
                    <input name="fte_roles[${index}][start_date]"
                        type="text"
                        class="thai-datepicker w-full border p-2 rounded pl-10"
                        placeholder="โปรดใส่วันที่"
                        required
                        oninvalid="this.setCustomValidity('กรุณาเลือกวันเริ่มต้นภาระงาน')"
                        oninput="this.setCustomValidity('')">

                    <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </label>

                <label class="w-full relative">
                    <span class="block text-sm font-semibold text-gray-700 mb-1">วันสิ้นสุดภาระงาน <span class="text-red-500">*</span></span>
                    <input name="fte_roles[${index}][end_date]"
                        type="text"
                        class="thai-datepicker w-full border p-2 rounded pl-10"
                        placeholder="โปรดใส่วันที่"
                        required
                        oninvalid="this.setCustomValidity('กรุณาเลือกวันสิ้นสุดภาระงาน')"
                        oninput="this.setCustomValidity('')">

                    <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
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
                    <option value="" disabled selected>เลือกภารกิจ</option>
                    <option>ติดต่อประสานงาน</option>
                    <option>ที่ปรึกษาโครงการ</option>
                    <option>review literature</option>
                    <option>เขียนproposal</option>
                    <option>ดำเนินการเอกสารขอirb/submit irb</option>
                    <option>ดำเนินการเอกสารขอทุน/submit ทุน</option>
                    <option>เก็บข้อมูล</option>
                    <option>วิเคราะห์ข้อมูล</option>
                    <option>เขียนเล่มรายงาน</option>
                    <option>เขียนmanuscript</option>
                    <option>submit journal</option>
                    <option>เบิกเงินรางวัลตีพิมพ์/ค่าตอบแทนตีพิมพ์</option>
                    <option>นำเสนอผลงานวิจัย Oral/Poster presentation</option>
                    <option>ประชาสัมพันธ์-เบิกงบประมาณทุน</option>
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

            reindexFTE();
            keepAddedRowOpen(id);
            initThaiDatepickers();
            attachFTEAuto(); // สำคัญ!
        }

        document.addEventListener("DOMContentLoaded", function() {
            attachFTEAuto(); // ให้แถวเดิมทำงาน auto
        });


        function attachFTEAuto() {
            document.querySelectorAll('.fte-hours').forEach(input => {
                input.oninput = function() {

                    const row = this.closest('.fte-row');
                    const fteField = row.querySelector('.fte-value');

                    const hours = parseFloat(this.value);

                    const oldFTE = fteField.value; // ค่าเดิมในช่อง FTE

                    // ========== RULE 1 ==========
                    // ชั่วโมง = 0 และ fte เดิม > 0 → ห้ามแก้ไข
                    if ((!hours || hours === 0) && oldFTE !== "" && parseFloat(oldFTE) > 0) {
                        return; // ไม่แตะต้องค่าเดิม
                    }

                    // ========== RULE 2 ==========
                    // ชั่วโมง = 0 และ FTE เดิมว่างหรือ 0 → set = 0.00000
                    if (!hours || hours === 0) {
                        if (oldFTE === "" || parseFloat(oldFTE) === 0) {
                            fteField.value = "0.00000";
                        }
                        return;
                    }

                    // ========== RULE 3 ==========
                    // ชั่วโมงมากกว่า 0 → คำนวณปกติ
                    const fte = (hours * 0.000621).toFixed(8);
                    // const fte = (hours * 0.00745).toFixed(6);
                    


                    console.log('[FTE AUTO] hours=', hours, 'calc fte=', fte, 'before=', fteField.value);
fteField.value = fte;
console.log('[FTE AUTO] after=', fteField.value);
                };
            });
        }


        function reindexFTE() {
            const rows = document.querySelectorAll('#fte-container .fte-row');
            rows.forEach((row, index) => {
                const span = row.querySelector('.fte-index');
                if (span) span.textContent = index + 1;
            });
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
            <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            if (el) el.remove();
            if (type === 'output') reindexOutput();
            if (type === 'copi') reindexCopi?.();
            if (type === 'irb') reindexIRB?.();
            if (type === 'fte') reindexFTE?.();
            if (type === 'coresearch') reindexCoResearch?.();
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

        function initThaiDatepickers() {
            document.querySelectorAll('.thai-datepicker').forEach((input) => {
                if (input._flatpickr) return;
                flatpickr(input, {
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
                        }
                        input.classList.add('flatpickr-hidden');
                        toBuddhist(selectedDates, dateStr, instance);
                    },
                    onChange: toBuddhist,
                    onMonthChange: toBuddhist,
                    onYearChange: toBuddhist,
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
                const year = parseInt(el.value);
                if (!isNaN(year)) el.value = year + 543;

                el.addEventListener('blur', () => {
                    let y = parseInt(el.value);
                    if (y > 2400) {
                        el.value = y - 543;
                        instance.setDate(new Date(instance.selectedDates[0]));
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            initThaiDatepickers();
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
                    const invalidRows = new Set();
                    const invalidSections = new Set();
                    const sectionNameById = {
                        irb: 'IRB',
                        copi: 'COPI',
                        coresearch: 'Co-Researcher',
                        fte: 'Roles and Responsibilities',
                        output: 'ผลงานวิจัย (Output)',
                    };

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
                        const displayTarget = field;

                        if (!val) {
                            isValid = false;
                            displayTarget.classList.add('border-red-500', 'bg-red-50');
                            if (!firstInvalidField) firstInvalidField = displayTarget;
                            const row = displayTarget.closest('[data-repeatable-row], .output-row, .fte-row, .copi-row, .coresearch-row, .irb-row');
                            if (row) invalidRows.add(row);
                            const group = displayTarget.closest('[data-repeatable]');
                            if (group) {
                                const id = group.dataset.id || '';
                                if (sectionNameById[id]) invalidSections.add(sectionNameById[id]);
                            }
                        } else {
                            displayTarget.classList.remove('border-red-500', 'bg-red-50');
                        }
                    });

                    if (!isValid) {
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

                        Swal.fire({
                            icon: 'error',
                            title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                            text: `กรุณากรอก: ${fieldLabel}${sectionText}`,
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
                if (!payload || payload.type !== 'progress-report:updated') return;
                if (Number(payload.projectId) !== currentProjectId) return;

                applyProgressSummary(payload.summary || {});
                closeModal();
            });
        })();
    </script>

@endsection

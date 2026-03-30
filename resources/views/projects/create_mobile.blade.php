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
        'จัดทำสื่อ / Communication Assets',
        'สร้างโปรเเกรม ฐานข้อมูล เเละ โปรเเกรมที่เกี่ยวข้องกับงานวิจัย',
        'IT and Technical support',
        'ที่ปรึกษาโครงการ',
        'เบิกงบประมาณทุน'
    ];
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
    window.DISABLE_FTE_TABLE_MODE = true;
    window.enableRowCollapseSummaryOnMobile = true;
    window.PROJECT_CARD_DATE_MODE = 'create';
    window.PROJECT_MOBILE_WHEEL_DATE = true;
</script>

@php
    $allUsers = \App\Models\User::orderBy('name')->get();
@endphp


@php
    $piAffiliationList = \App\Models\Project::whereNotNull('pi_affiliation')
        ->whereRaw("TRIM(pi_affiliation) <> ''")
        ->selectRaw('TRIM(pi_affiliation) as pi_affiliation')
        ->distinct()
        ->orderBy('pi_affiliation')
        ->pluck('pi_affiliation');

    $coresearchAffiliationList = \App\Models\CoResearch::whereNotNull('affiliation')
        ->whereRaw("TRIM(affiliation) <> ''")
        ->selectRaw('TRIM(affiliation) as affiliation')
        ->distinct()
        ->orderBy('affiliation')
        ->pluck('affiliation');

    $copiAffiliationList = \App\Models\Copi::whereNotNull('affiliation')
        ->whereRaw("TRIM(affiliation) <> ''")
        ->selectRaw('TRIM(affiliation) as affiliation')
        ->distinct()
        ->orderBy('affiliation')
        ->pluck('affiliation');

    $irbSourceList = \App\Models\Irb::whereNotNull('source')
        ->whereRaw("TRIM(source) <> ''")
        ->selectRaw('TRIM(source) as source')
        ->distinct()
        ->orderBy('source')
        ->pluck('source');
@endphp



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

        .lux-mobile-form .flatpickr-input:not([readonly]) {
            display: none;
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

        .lux-mobile-form #fte-tools,
        .lux-mobile-form #fte-table-wrap {
            display: none !important;
        }

        .fte-mobile-row {
            content-visibility: auto;
            contain-intrinsic-size: 120px;
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

        .lux-mobile-add-row-btn {
            display: block;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
    <!-- Flatpickr -->
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script> --}}
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- 1. Include Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    {{-- 
    <!-- ✅ เพิ่ม Plugin สำหรับเลือกปี -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
 --}}



    <div class="project-form lux-mobile-form max-w-6xl mx-auto mt-6 bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
        <h2 class="text-xl font-bold mb-6 text-gray-800">📋 เพิ่มข้อมูลโครงการวิจัย</h2>

        <div id="autosave-banner" class="hidden mb-4 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm">
                    <span class="font-semibold">พบข้อมูลที่ยังไม่ได้บันทึก</span>
                    <span class="block text-xs text-amber-700" id="autosave-banner-meta"></span>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="px-3 py-1.5 rounded bg-amber-600 text-white text-sm" id="autosave-banner-restore">
                        กู้คืน
                    </button>
                    <button type="button" class="px-3 py-1.5 rounded bg-amber-100 text-amber-900 text-sm" id="autosave-banner-discard">
                        ทิ้งข้อมูล
                    </button>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @php
            $allUsers = \App\Models\User::orderBy('name')->get();
        @endphp

        <form id="projectForm" action="{{ route('projects.store') }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="_fte_expected_rows" id="fteExpectedRows" value="0">
            <input type="hidden" name="_form_expected_vars" id="formExpectedVars" value="0">
            <input type="hidden" name="fte_roles_payload" id="fteRolesPayload" value="">

            <hr class="my-6 border-t-2 border-gray-300">
            <!-- A: Project Overview -->
            <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ชื่อโครงการ (ภาษาไทย)</span>
                    <input name="project_name_th" type="text" class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ชื่อโครงการ (English)</span>
                    <input name="project_name_en" type="text" class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ประเภทของโครงการ<span class="text-red-500">*</span></span>
                    <select name="project_type" id="project_type" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกประเภทของโครงการ')"
                        oninput="this.setCustomValidity('')">
                        <option value="" disabled selected>เลือกประเภทของโครงการ</option> <!-- สำหรับ placeholder -->
                        <option value="01 ผู้สูงอายุ ตามยุทธศาสตร์ศาสตร์สูงอายุของคณะแพทยศาสตร์ศิริราชพยาบาล">01 ผู้สูงอายุ
                            ตามยุทธศาสตร์ศาสตร์สูงอายุของคณะแพทยศาสตร์ศิริราชพยาบาล</option>
                        <option
                            value="02 โครงการพัฒนาระบบเพื่อการสร้างเสริมสุขภาพสำหรับพระสงฆ์ โดยทีมสหวิทยาการ และการมีส่วนร่วมของประชาชนอย่างยั่งยืน : บางกอกน้อยโมเดล">
                            02 โครงการพัฒนาระบบเพื่อการสร้างเสริมสุขภาพสำหรับพระสงฆ์ โดยทีมสหวิทยาการ
                            และการมีส่วนร่วมของประชาชนอย่างยั่งยืน : บางกอกน้อยโมเดล</option>
                        <option value="03 Health System research and HTA">03 Health System research and HTA</option>
                        <option value="04 การใช้ยาอย่างสมเหตุผล (RDU)">04 การใช้ยาอย่างสมเหตุผล (RDU)</option>
                        <option
                            value="05 Health Policy Platform: Prince Mahidol Award Conference (PMAC), Global Health, UHC">05
                            Health Policy Platform: Prince Mahidol Award Conference (PMAC), Global Health, UHC</option>
                        <option value="06 Real-world Evidence: E-Health, Health Information system, Digital Health">06
                            Real-world Evidence: E-Health, Health Information system, Digital Health</option>
                        <option value="07 Fall prevention + Hip facture preject (VBHC & Registry)">07 Fall prevention + Hip
                            facture preject (VBHC & Registry)</option>
                        <option value="08 การพัฒนาศักยภาพบุคลากร และการจัดเก็บองค์ความรู้เพื่อส่งเสริมการเรียนรู้">08
                            การพัฒนาศักยภาพบุคลากร และการจัดเก็บองค์ความรู้เพื่อส่งเสริมการเรียนรู้</option>
                        <option value="09 SIHP Process Improvement (System Support)">09 SIHP Process Improvement (System
                            Support)</option>
                        <option value="10 อื่นๆ ที่ได้รับมอบหมาย ไม่มีค่า FTE">10 อื่นๆ ที่ได้รับมอบหมาย ไม่มีค่า FTE
                        </option>
                        <option value="11 SIPG">11 SIPG</option>
                        <option value="12 RDMU">12 RDMU</option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">กลุ่มเนื้อหา (Theme) <span class="text-red-500">*</span></span>
                    <select name="theme" required oninvalid="this.setCustomValidity('กรุณาเลือกกลุ่มเนื้อหา')"
                        oninput="this.setCustomValidity('')" class="w-full border p-2 rounded">
                        <option value="">กรุณาเลือกกลุ่มเนื้อหา</option>
                        <option value="T101_Research_วิจัยที่ Health Policy เป็น ผู้วิจัยหลัก">T101_Research_วิจัยที่ Health
                            Policy เป็น ผู้วิจัยหลัก</option>
                        <option value="T102_Research: โครงการวิจัยที่ Team Health Policy เป็น ผู้ร่วมวิจัย">T102_Research:
                            โครงการวิจัยที่ Team Health Policy เป็น ผู้ร่วมวิจัย</option>
                        <option
                            value="T103_Research: โครงการวิจัยที่เป็นไปในลักษณะ Individual Expertise contribution (Skill รายบุคคล) เช่น การเข้าร่วมเขียน paper">
                            T103_Research: โครงการวิจัยที่เป็นไปในลักษณะ Individual Expertise contribution (Skill รายบุคคล)
                            เช่น การเข้าร่วมเขียน paper</option>
                        <option value="T301_Model of Care development (Clinic)">T301_Model of Care development (Clinic)
                        </option>
                        <option value="T401_Cohort Study">T401_Cohort Study</option>
                        <option value="T501_Curriculum Development (Education)">T501_Curriculum Development (Education)
                        </option>
                        <option value="T601_Consult">T601_Consult</option>
                        <option value="T701_Process Improvement (Siriraj Health Policy)">T701_Process Improvement (Siriraj
                            Health Policy)</option>
                        <option value="T801_Conference Session / Knowledge Sharing">T801_Conference Session / Knowledge
                            Sharing</option>
                        <option value="T901_Capacity Building / Skill Development">T901_Capacity Building / Skill
                            Development</option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">เอกสารโครงการ (proposal , IRB)</span>
                    <input name="shortcut_link" type="text" class="w-full border p-2 rounded">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ลักษณะโครงการ <span class="text-red-500">*</span></span>
                    <select name="project_list" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกลักษณะโครงการ')"
                        oninput="this.setCustomValidity('')">
                        <option value="">-- เลือกประเภทลักษณะ --</option>
                        <option value="โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ">
                            โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                        <option value="โครงการนอกหน่วย แต่ยังอยู่ในคณะฯ">โครงการนอกหน่วย แต่ยังอยู่ในคณะฯ</option>
                        <option value="โครงการวิจัยนอกคณะ">โครงการวิจัยนอกคณะ</option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ขนาดโครงการ (S/M/L)</span>
                    <select name="size" class="w-full border p-2 rounded">
                        <option value="" disabled selected>กรุณาเลือกขนาดของโปรเจค</option>
                        <option value="S">S (ภายนอก ต่ำกว่า 1 ล้าน / ภายใน)</option>
                        <option value="M">M (ภายนอก 1-3 ล้าน / ภายใน / Cohort Study (Perioperative, SEAPOD))</option>
                        <option value="L">L (ภายนอก 3-5 ล้าน / ภายใน / Cohort Study (GSC, PCU, DM))</option>
                        <option value="XL">XL (ภายนอก มากกว่า 5 ล้าน) </option>
                        <option value="FTE Hours">FTE คิดแบบรายชั่วโมง </option>
                    </select>
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1"> รหัสโครงการ R</span>
                    <input name="r_code" type="text" placeholder="R:001" class="w-full border p-2 rounded">
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





                <!-- Funding Source Section -->
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA102 - ประเภทแหล่งทุน</span>
                    <select name="funding_type" class="w-full border p-2 rounded">
                        <option value="" disabled selected>กรุณาเลือกประเภทแหล่งทุน</option>
                        <option value="ภายใน">ภายใน</option>
                        <option value="ภายนอก">ภายนอก</option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA103 - ชื่อแหล่งทุน</span>
                    <input name="funding_source" type="text" class="w-full border p-2 rounded"
                        placeholder="ชื่อหน่วยงานหรือองค์กรที่ให้ทุน">
                </label>


                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA104 - รัฐบาล / เอกชน / มูลนิธิ</span>
                    <select name="funding_org_type" class="w-full border p-2 rounded">
                        <option value="" disabled selected>กรุณาเลือกประเภทองค์กร</option>
                        <option value="รัฐบาล">รัฐบาล</option>
                        <option value="เอกชน">เอกชน</option>
                        <option value="มูลนิธิ">มูลนิธิ</option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA105 - ชื่อทุน</span>

                    <input name="funding_name" list="funding_list" class="w-full border p-2 rounded"
                        placeholder="พิมพ์ชื่อทุนหรือเลือกจากรายการ">

                    <datalist id="funding_list">
                        <option value="National University Hospital (Singapore) PTE LTD"></option>
                        <option value="Roland Berger"></option>
                        <option value="ทุนพัฒนาการวิจัย"></option>
                        <option value="บัญชียาหลักแห่งชาติ"></option>
                        <option value="มูลนิธิโรคมะเร็ง โรงพยาบาลศิริราช"></option>
                        <option value="ศูนย์ความเป็นเลิศด้านชีววิทยาศาสตร์ (องค์การมหาชน)"></option>
                        <option value="ศูนย์บริการวิชาการแห่งจุฬาลงกรณ์มหาวิทยาลัย"></option>
                        <option value="สถาบันวิจัยระบบสาธารณสุข (สวรส.)"></option>
                        <option
                            value="สมาคมพฤฒาวิทยาและเวชศาสตร์ผู้สูงอายุไทย (ทุนจากบริษัท เอไซ (ประเทศไทย) มาร์เก็ตติ้ง จำกัด)">
                        </option>
                        <option value="สำนักงานกองทุนสนับสนุนการสร้างเสริมสุขภาพ (สสส.)"></option>
                        <option value="สำนักงานคณะกรรมการอาหารและยา (อย.)"></option>
                    </datalist>
                </label>


                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA106 - งบประมาณ (บาท)</span>
                    <input id="budgetInput" name="budget" type="text" class="w-full border p-2 rounded"
                        placeholder="ระบุจำนวนเงินที่ได้รับ" inputmode="decimal" pattern="[\d,\.]*"
                        data-max="1000000000000">

                </label>

            </div>

            <!-- B: ระยะเวลาดำเนินโครงการ -->
            <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">B: ระยะเวลาดำเนินโครงการ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

                <x-date-be name="team_start_date" label="วันที่โครงการเริ่มทำงานของทีมวิจัย"
                    placeholder="กรุณาเลือกวันที่เริ่มทำงานของทีมวิจัย" />

                <label class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">วันที่สิ้นสุดการทำงานของทีมวิจัย</span>
                    <input name="team_end_date" type="text"
                        value="{{ old('team_end_date') }}"
                        class="thai-datepicker w-full border p-2 rounded pl-10 bg-white cursor-pointer"
                        data-force-be-date="1"
                        placeholder="กรุณาเลือกวันที่สิ้นสุดการทำงานของทีมวิจัย">
                    <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2z" />
                    </svg>
                    @error('team_end_date')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </label>

                <x-date-be name="proposal_date" label="BA001 - วันที่เริ่มเขียน Proposal"
                    placeholder="กรุณาเลือกวันที่เริ่มเขียน Proposal" />

                <x-date-be name="contract_date" label="BC101 - วันที่สัญญาอนุมัติ"
                    placeholder="กรุณาเลือกวันที่อนุมัติสัญญา" />

                <x-date-be name="start_date" label="BC102 - วันที่โครงการเริ่ม (ตามสัญญา)"
                    placeholder="กรุณาเลือกวันที่เริ่มตามสัญญา" />

                <label class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">BC103 - วันที่สิ้นสุด (ตามสัญญา)</span>
                    <input name="end_date" type="text"
                        value="{{ old('end_date') }}"
                        class="thai-datepicker w-full border p-2 rounded pl-10 bg-white cursor-pointer"
                        data-force-be-date="1"
                        placeholder="กรุณาเลือกวันที่สิ้นสุดตามสัญญา">
                    <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2z" />
                    </svg>
                    @error('end_date')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </label>

                <label class="w-full">
                    {{-- BA000 - Project Planning (Status) --}}
                    <span class="block text-sm text-gray-700 mb-1">Project Status <span class="text-red-500">*</span></span>
                    <select name="planning_status" id="planning_status" class="w-full border p-2 rounded font-semibold"
                        onchange="updateStatusColor(this)" required
                        oninvalid="this.setCustomValidity('กรุณาเลือก Project Status')"
                        oninput="this.setCustomValidity('')">
                        <option value="" disabled selected style="background-color: #fff; color: #222;">
                            ระบุสถานะของโปรเจค</option>
                        <option value="Project Planing (เริ่มต้น - submit IRB)" data-color="#FFE599"
                            style="background-color:#FFE599; color:#222;">Project Planing (เริ่มต้น - submit IRB)</option>
                        <option value="เก็บข้อมูล" data-color="#B6E2D3" style="background-color:#B6E2D3; color:#222;">
                            เก็บข้อมูล</option>
                        <option value="วิเคราะห์ข้อมูล" data-color="#B3D8F7"
                            style="background-color:#B3D8F7; color:#222;">วิเคราะห์ข้อมูล</option>
                        <option value="เขียนรายงานและpublication" data-color="#D6C1E6"
                            style="background-color:#D6C1E6; color:#222;">เขียนรายงาน และ publication</option>
                        <option value="ปิดโปรเจค" data-color="#C6F1E7" style="background-color:#C6F1E7; color:#222;">
                            ปิดโปรเจค</option>
                        <option value="ยกเลิกโปรเจค" data-color="#F7A9A8" style="background-color:#F7A9A8; color:#222;">
                            ยกเลิกโปรเจค</option>
                    </select>
                    @error('planning_status')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </label>
            </div>
            <!-- รหัส IRB -->
            <hr class="my-6 border-t-2 border-gray-300">
            <h3 class="font-semibold text-lg text-blue-700 mb-2">IRB</h3>
            <x-repeatable-row id="irb" addText="+ เพิ่ม IRB" startIndex="0" addClass="lux-mobile-add-row-btn mb-6 px-3 py-1 bg-red-600 text-white rounded">

                {{-- ✅ ต้องมี container --}}
                <div data-repeatable-container class="space-y-2">

                </div>

                {{-- template --}}
                <x-slot:template>
                    <div class="space-y-3 p-4 border rounded-lg bg-white" data-repeatable-row>

                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span data-repeatable-index
                                    class="inline-flex w-8 h-8 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-sm font-semibold">
                                    #
                                </span>
                                <span class="text-sm font-semibold text-slate-700">IRB</span>
                            </div>
                            <button type="button" class="text-red-500" data-repeatable-remove>✕</button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <div class="col-span-12 md:col-span-4">
                                <label class="text-sm">รหัส IRB <span class="text-red-500">*</span></label>
                                <input name="irbs[__i__][code]" class="w-full border p-2 rounded" placeholder="เช่น SIHP-IRB-2025-001" required
                                oninvalid="this.setCustomValidity('กรุณาระบุรหัส IRB')"
                                oninput="this.setCustomValidity('')">
                            </div>

                            <div class="col-span-12 md:col-span-4">
                                <label class="text-sm">หน่วยงาน <span class="text-red-500">*</span></label>
                                <input name="irbs[__i__][source]" list="irbSourceList" class="w-full border p-2 rounded" placeholder="ชื่อหน่วยงานที่ออก IRB" required
                                oninvalid="this.setCustomValidity('กรุณาระบุหน่วยงาน')"
                                oninput="this.setCustomValidity('')">
                            </div>

                            <div class="col-span-12 md:col-span-4">
                                <x-date-be name="irbs[__i__][exp_date]" label="วันหมดอายุ IRB" placeholder="เลือกวันหมดอายุ IRB" required />
                            </div>
                        </div>
                    </div>
                </x-slot:template>

            </x-repeatable-row>

            <hr class="my-6 border-t-2 border-gray-300">
            <!-- C: Roles and Responsibilities -->
            <h3 class="font-semibold text-lg text-blue-700 mb-2">C: Roles and Responsibilities</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <label class="block w-full">
                    <span class="block text-sm font-medium text-gray-700 mb-1">
                        CA101 - ชื่อ PI (ผู้วิจัยหลัก) <span class="text-red-500">*</span>
                    </span>
                    <div class="relative">
                        <input name="pi_name" list="userNames" type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                            placeholder="คำนำหน้า ชื่อ-นามสกุล" required
                            oninvalid="this.setCustomValidity('กรุณาระบุชื่อผู้วิจัยหลัก')"
                            oninput="this.setCustomValidity('')">
                        <datalist id="userNames">
                            @foreach ($allUsers as $user)
                                @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
                                <option value="{{ $fullName }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    @error('pi_name')
                        <span class="text-sm text-red-500 mt-1">{{ $message }}</span>
                    @enderror
                </label>


                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">CA103 - สังกัดผู้วิจัยหลัก</span>
                    <input name="pi_affiliation" type="text" list="piaffiliationList"
                        class="w-full border p-2 rounded" value="" placeholder="สังกัดผู้วิจัยหลัก" required
                        oninvalid="this.setCustomValidity('กรุณาระบุสังกัดผู้วิจัยหลัก')"
                        oninput="this.setCustomValidity('')">
                    @error('pi_affiliation')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <datalist id="piaffiliationList">

                    @foreach ($piAffiliationList as $affiliation)
                        <option value="{{ $affiliation }}"></option>
                    @endforeach

                </datalist>
                <datalist id="coresearchAffiliationList">
                    @foreach ($coresearchAffiliationList as $affiliation)
                        <option value="{{ $affiliation }}"></option>
                    @endforeach
                </datalist>
                <datalist id="irbSourceList">
                    @foreach ($irbSourceList as $source)
                        <option value="{{ $source }}"></option>
                    @endforeach
                </datalist>
                <datalist id="copiAffiliationList">
                    @foreach ($copiAffiliationList as $affiliation)
                        <option value="{{ $affiliation }}"></option>
                    @endforeach
                </datalist>
            </div>


            <h3 class="text-balance text-blue-700 mb-2">
                รายชื่อผู้ร่วมวิจัย (COPI)
            </h3>
            <x-repeatable-row id="copi" addText="+ เพิ่ม COPI" startIndex="0" addClass="lux-mobile-add-row-btn mb-6 px-3 py-1 bg-yellow-600 text-white rounded">

                {{-- ✅ container --}}
                <div data-repeatable-container class="space-y-3 mb-4">

                </div>

                {{-- ================== template ================== --}}
                <x-slot:template>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end p-3 border rounded-lg bg-white" data-repeatable-row>

                        <div class="col-span-12 md:col-span-1 text-center">
                            <span data-repeatable-index
                                class="inline-flex w-8 h-8 items-center justify-center
                             rounded-full bg-yellow-100 text-yellow-700
                             text-sm font-semibold">
                                #
                            </span>
                        </div>

                        <div class="col-span-12 md:col-span-5">
                            <label class="block text-sm text-gray-700 mb-1">
                                ชื่อ COPI <span class="text-red-500">*</span>
                            </label>
                            <input name="copis[__i__][name]" list="userNames" type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg
                           focus:ring-2 focus:ring-yellow-500"
                                placeholder="คำนำหน้า ชื่อ-นามสกุล" required
                                oninvalid="this.setCustomValidity('กรุณาระบุชื่อ COPI')"
                                oninput="this.setCustomValidity('')">
                        </div>

                        <div class="col-span-12 md:col-span-5">
                            <label class="block text-sm text-gray-700 mb-1">
                                สังกัด COPI <span class="text-red-500">*</span>
                            </label>
                            <input name="copis[__i__][affiliation]" type="text" list="copiAffiliationList" class="w-full border p-2 rounded"
                                placeholder="สังกัด COPI" required
                                oninvalid="this.setCustomValidity('กรุณาระบุสังกัด COPI')"
                                oninput="this.setCustomValidity('')">
                        </div>

                        <div class="col-span-12 md:col-span-1 flex justify-end md:justify-center">
                            <button type="button" class="text-red-600" data-repeatable-remove>
                                ✕
                            </button>
                        </div>
                    </div>
                </x-slot:template>

            </x-repeatable-row>


            <h3 class="text-blue-700 font-semibold mt-2"> Co-Researcher</h3>
            <x-repeatable-row id="coresearch" addText="+ เพิ่ม Co-Researcher" startIndex="0" addClass="lux-mobile-add-row-btn mb-6 px-3 py-1 bg-purple-600 text-white rounded">

                {{-- ================== container ================== --}}
                <div data-repeatable-container class="space-y-4 mb-4">

                </div>

                {{-- ================== template ================== --}}
                <x-slot:template>
                    <div class="space-y-2 p-4 border rounded-lg bg-white" data-repeatable-row>

                        <label class="block w-full">
                            <span class="block text-sm mb-1">
                                ชื่อ Co-Researcher คนที่ <span class="text-red-500">*</span>
                                <span data-repeatable-index
                                    class="inline-flex ml-1 px-2 py-0.5 rounded-full
                                 bg-purple-100 text-purple-700 text-xs font-semibold">
                                    #
                                </span>
                            </span>

                            <input name="coresearch[__i__][name]" list="coresearchUsers" type="text"
                                class="w-full border p-2 rounded" placeholder="ยศ ชื่อ นามสกุล" required
                                oninvalid="this.setCustomValidity('กรุณาระบุชื่อ Co-Researcher')"
                                oninput="this.setCustomValidity('')">
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">

                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm mb-1">สังกัด <span class="text-red-500">*</span></span>
                                <input name="coresearch[__i__][affiliation]" type="text" list="coresearchAffiliationList"
                                    class="w-full border p-2 rounded" placeholder="สังกัด" required
                                    oninvalid="this.setCustomValidity('กรุณาระบุสังกัด')"
                                    oninput="this.setCustomValidity('')">
                            </label>

                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm mb-1">
                                    คนภายนอก / ภายใน <span class="text-red-500">*</span>
                                </span>
                                <select name="coresearch[__i__][unit_status]" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกคนภายนอก/ภายใน')"
                                    oninput="this.setCustomValidity('')" class="w-full border p-2 rounded">
                                    <option value="">กรุณาเลือก</option>
                                    <option value="ภายในคณะแพทยศาสตร์ศิริราชพยาบาล">
                                        ภายในคณะแพทยศาสตร์ศิริราชพยาบาล
                                    </option>
                                    <option value="ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล">
                                        ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล
                                    </option>
                                    <option value="ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ">
                                        ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ
                                    </option>
                                </select>
                            </label>

                            <div class="col-span-12 md:col-span-2 flex items-center justify-end md:pt-6">
                                <button type="button" class="text-red-600 text-xl" data-repeatable-remove>
                                    ✕
                                </button>
                            </div>

                        </div>
                    </div>
                </x-slot:template>

            </x-repeatable-row>

            {{-- datalist ใช้ร่วมทุกแถว --}}
            <datalist id="coresearchUsers">
                @foreach ($allUsers as $user)
                    @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
                    <option value="{{ $fullName }}"></option>
                @endforeach
            </datalist>

            <hr class="my-6 border-t-2 border-gray-300">
            <div class="flex justify-between items-center mb-2">
                <h3 class="font-semibold text-lg text-blue-700">D: รายชื่อผู้มีส่วนร่วมในการทำงาน</h3>
            </div>

            <div id="fte-container" class="space-y-4 mb-4">
                @php
                    $fteRows = old('fte_roles', []);
                @endphp
                @foreach ($fteRows as $i => $fte)
                    @php
                        $fteName = data_get($fte, 'name');
                        $selectedRoleGroup = data_get($fte, 'role_group');
                        $selectedJobDetail = data_get($fte, 'job_detail');
                        $fteStartDate = data_get($fte, 'start_date');
                        $fteEndDate = data_get($fte, 'end_date');
                        $fteHours = data_get($fte, 'hours');
                        $selectedTaskDetail = data_get($fte, 'task_detail');
                        $fteValue = data_get($fte, 'fte_value');
                        $fteValueResearch = data_get($fte, 'fte_value_research');
                    @endphp
                    <div id="fte-{{ $i }}" class="fte-mobile-row p-4 border rounded-lg bg-white shadow-sm">
                        <div class="fte-mobile-header">
                            <button type="button" class="fte-mobile-toggle text-left">
                                <span class="text-sm font-semibold text-slate-700">ผู้มีส่วนร่วมคนที่ <span class="fte-index">{{ $i + 1 }}</span></span>
                                <div class="fte-mobile-summary mt-2"></div>
                            </button>
                            <button type="button" onclick="removeRow('fte-{{ $i }}','fte')" class="fte-mobile-remove">ลบ</button>
                        </div>

                        <div class="fte-mobile-body grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                            <div class="md:col-span-9 space-y-4">
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
                                        <select name="fte_roles[{{ $i }}][role_group]" class="w-full border p-2 rounded" required
                                            oninvalid="this.setCustomValidity('กรุณาเลือกกลุ่ม')"
                                            oninput="this.setCustomValidity('')">
                                            <option value="" disabled>เลือกกลุ่ม</option>
                                            @foreach ($roleGroups as $group)
                                                <option value="{{ $group }}" @selected($selectedRoleGroup == $group)>{{ $group }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <label class="w-full">
                                        <span class="fte-field-label text-sm font-semibold">
                                            ภาระงาน
                                            <span class="text-red-500">*</span>
                                        </span>
                                        <select name="fte_roles[{{ $i }}][job_detail]" class="w-full border p-2 rounded" required
                                            oninvalid="this.setCustomValidity('กรุณาเลือกภาระงาน')"
                                            oninput="this.setCustomValidity('')">
                                            <option value="" disabled>เลือกภาระงาน</option>
                                            @foreach ($jobDetails as $job)
                                                <option value="{{ $job }}" @selected($selectedJobDetail == $job)>{{ $job }}</option>
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
                                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
                                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </label>
                                </div>

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

                                <label class="w-full">
                                    <span class="fte-field-label text-sm font-semibold">
                                        รายละเอียดภารกิจ
                                        <span class="text-red-500">*</span>
                                    </span>
                                    <select name="fte_roles[{{ $i }}][task_detail]" class="w-full border p-2 rounded" required
                                        oninvalid="this.setCustomValidity('กรุณาเลือกรายละเอียดภารกิจ')"
                                        oninput="this.setCustomValidity('')">
                                        <option value="" disabled @selected(blank($selectedTaskDetail))>เลือกภารกิจ</option>
                                        @foreach ($taskDetails as $taskDetail)
                                            <option value="{{ $taskDetail }}" @selected($selectedTaskDetail === $taskDetail)>{{ $taskDetail }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>

                            <div class="md:col-span-3">
                                <div class="fte-metrics-grid">
                                    <label class="fte-metric-card fte-metric-card--research block border rounded-xl">
                                        <span class="block text-sm font-bold text-blue-800 mb-2">FTE (ผู้วิจัย)</span>
                                        <input name="fte_roles[{{ $i }}][fte_value]" type="number" step="0.00001"
                                            value="{{ $fteValue }}"
                                            class="fte-value w-full border p-3 rounded text-right bg-gray-100" readonly>
                                    </label>

                                    <label class="fte-metric-card fte-metric-card--manager block border rounded-xl">
                                        <span class="block text-sm font-bold text-emerald-800 mb-2">FTE (ผู้บริหาร)</span>
                                        <input name="fte_roles[{{ $i }}][fte_value_research]" type="number" step="0.01"
                                            min="0" max="1" value="{{ $fteValueResearch }}"
                                            class="w-full border p-3 rounded text-right" placeholder="0.00">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" onclick="event.stopPropagation(); addFTERow()" data-fte-add-row class="lux-mobile-add-row-btn mb-6 px-3 py-1 bg-blue-600 text-white rounded">
                + เพิ่มผู้มีส่วนร่วม
            </button>


            <hr class="my-6 border-t-2 border-gray-300">
            <!-- E: ผลงานวิจัย (Output) -->
            <h3 class="font-semibold text-lg text-blue-700 mb-2">E: ผลงานวิจัย (Output)</h3>

            <x-repeatable-row id="output" addText="+ เพิ่ม Output" startIndex="0" addClass="lux-mobile-add-row-btn mb-6 px-3 py-1 bg-green-600 text-white rounded">

                <div data-repeatable-container class="space-y-4 mb-4">

                </div>

                {{-- TEMPLATE --}}
                <x-slot:template>
                    <div class="output-row p-4 border rounded-lg bg-white space-y-3" data-repeatable-row>

                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-blue-700">
                                ผลงานที่ <span data-repeatable-index>#</span>
                            </span>
                            <button type="button" class="text-red-600" data-repeatable-remove>✕</button>
                        </div>

                        <label>
                            <span class="text-sm">ชื่อผลงาน <span class="text-red-500">*</span></span>
                            <input name="outputs[__i__][title]" class="w-full border p-2 rounded" placeholder="ระบุชื่อผลงาน" required>
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label>
                                <span class="text-sm">ประเภท <span class="text-red-500">*</span></span>
                                <select name="outputs[__i__][type]" class="output-type w-full border p-2 rounded"
                                    onchange="handleOutputType(this)" required>
                                    <option value="">เลือกประเภทของผลงาน</option>

                                    <option>EA101 Research Paper or Journal Article</option>
                                    <option>EA102 Reports and White Papers</option>
                                    <option>EA103 Presentations in Academic Conferences</option>
                                    <option>EA104 Data Sets and Databases</option>
                                    <option>EA105 Inventions or Innovations (Application / Website)</option>
                                    <option>EA106 Policy Recommendations</option>
                                    <option>EA107 Educational Materials</option>
                                    <option>EA108 Communication Materials (Creations)</option>
                                    <option>EA109 Change in Practices / Procedures / Policy</option>
                                    <option>EA110 Number of Participants (Persons)</option>
                                    <option>EA111 Satisfaction Rating (1–5)</option>
                                    <option>EA112 Presentation to Committee / Policy Maker / Board</option>
                                    <option>EA113 Appointment Orders (SIHP as Committee / Secretary)</option>
                                    <option>EA114 Events / Forum</option>
                                    <option>EA115 Capacity Building</option>
                                    <option>EA116 Research Project</option>
                                    <option>EA117 MOU</option>
                                </select>
                            </label>

                            <label>
                                <span class="text-sm">หมายเหตุ</span>
                                <input name="outputs[__i__][project_output]" class="w-full border p-2 rounded" placeholder="เช่น ตีพิมพ์แล้ว / ลิงก์ / รายละเอียดเพิ่มเติม">
                            </label>

                            <x-date-be name="outputs[__i__][year]" label="วันที่ publish" placeholder="เลือกวันที่ publish" required />
                        </div>

                        <div class="special-mount mt-3"></div>
                    </div>
                </x-slot:template>

            </x-repeatable-row>





            <hr class="my-6 border-t-2 border-gray-300">
            <!-- ยุทธศาสตร์ -->
            <h3 class="font-semibold text-lg text-blue-700 mb-2">ยุทธศาสตร์ Siriraj Health Policy</h3>
            <table class="w-full table-auto text-sm mb-6">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-3 py-2 text-left">หมวด</th>
                        <th class="px-3 py-2 text-left">รายการ</th>
                        <th class="px-3 py-2">มี</th>
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
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $desc }}</td>
                                <td class="px-3 py-2 text-center">
                                    <input type="checkbox" name="strategy_{{ $sKey }}_{{ $iKey }}"
                                        class="mr-1">
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <!-- Submit -->
            <div class="mt-6 flex justify-center">
                <button type="submit" class="w-full max-w-2xl bg-indigo-600 text-white px-6 py-3 rounded hover:bg-indigo-700">💾
                    บันทึกข้อมูล</button>
            </div>
        </form>



        <div id="project-name-modal"
            class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg w-full max-w-lg shadow-lg">
                <h3 class="text-xl font-bold text-red-600 mb-2">⚠️ พบชื่อโครงการใกล้เคียง</h3>
                <p class="mb-4 text-gray-700">มีโครงการที่ชื่อใกล้เคียงกันในระบบ:</p>
                <ul id="similar-projects-list" class="mb-4 space-y-2 text-sm text-gray-800"></ul>
                <div class="flex justify-end gap-2">
                    <button onclick="continueAnyway()"
                        class="bg-blue-600 text-white px-4 py-2 rounded">เพิ่มต่อไป</button>
                    <button onclick="resetForm()" class="bg-red-600 text-white px-4 py-2 rounded">กรอกใหม่</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        function getMobileFteSummary(row) {
            const getValue = (field) => row.querySelector(`[name^="fte_roles["][name$="[${field}]"]`)?.value?.trim() || '';
            const name = getValue('name') || 'ยังไม่ได้ระบุชื่อ';
            const meta = [getValue('role_group'), getValue('job_detail')].filter(Boolean).join(' / ') || 'ยังไม่ได้เลือกกลุ่มหรือภาระงาน';
            const hours = getValue('hours') || '-';
            const startDate = getValue('start_date') || '-';
            const endDate = getValue('end_date') || '-';
            const task = getValue('task_detail') || 'ยังไม่ได้เลือกรายละเอียดภารกิจ';
            return `${name}<br>${meta}<br>${startDate} ถึง ${endDate}<br>${hours} ชั่วโมง<br>${task}`;
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

        function reindexMobileCreateFteCards() {
            document.querySelectorAll('#fte-container .fte-mobile-row').forEach((row, index) => {
                row.querySelectorAll('.fte-index').forEach((el) => {
                    el.textContent = index + 1;
                });
            });
        }

        function reindexFTE() {
            reindexMobileCreateFteCards();
        }

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

        function addFTERow() {
            if (typeof window.validateFteBeforeAdd === 'function' && !window.validateFteBeforeAdd({
                    showAlert: true
                })) {
                return;
            }

            const container = document.getElementById('fte-container');
            const index = getNextFteIndex();
            const id = `fte-${index}`;
            const roleGroups = ['Scientific', 'Management', 'IT + Communication', 'Logistics + Admin'];
            const jobDetails = [
                'Researcher', 'Researcher Assistant', 'Project Manager',
                'Research Coordinator', 'นักวิชาการสารสนเทศ',
                'นักประชาสัมพันธ์', 'เจ้าหน้าที่บริหารงานทั่วไป'
            ];
            const taskDetails = @json($taskDetails);

            const groupDropdown = [`<option value="" disabled selected>เลือกกลุ่ม</option>`]
                .concat(roleGroups.map((g) => `<option value="${g}">${g}</option>`))
                .join('');

            const jobDropdown = [`<option value="" disabled selected>เลือกภาระงาน</option>`]
                .concat(jobDetails.map((j) => `<option value="${j}">${j}</option>`))
                .join('');

            const taskDropdown = [`<option value="" disabled selected>เลือกภารกิจ</option>`]
                .concat(taskDetails.map((t) => `<option value="${t}">${t}</option>`))
                .join('');

            container.insertAdjacentHTML('beforeend', `
<div id="${id}" class="fte-mobile-row p-4 border rounded-lg bg-white shadow-sm">
    <div class="fte-mobile-header">
        <button type="button" class="fte-mobile-toggle text-left">
            <span class="text-sm font-semibold text-slate-700">ผู้มีส่วนร่วมคนที่ <span class="fte-index">${index + 1}</span></span>
            <div class="fte-mobile-summary mt-2"></div>
        </button>
        <button type="button" onclick="removeRow('${id}','fte')" class="fte-mobile-remove">ลบ</button>
    </div>

    <div class="fte-mobile-body grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
        <div class="md:col-span-9 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="w-full">
                    <span class="fte-field-label text-sm font-semibold">
                        ชื่อ-นามสกุล
                        <span class="text-red-500">*</span>
                    </span>
                    <input list="fteUsers" name="fte_roles[${index}][name]"
                        class="w-full border p-2 rounded" placeholder="พิมพ์หรือเลือกชื่อผู้มีส่วนร่วม" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกหรือพิมพ์ชื่อผู้ใช้จากรายการ')"
                        oninput="this.setCustomValidity('')">
                </label>

                <label class="w-full">
                    <span class="block text-sm font-semibold">กลุ่ม</span>
                    <select name="fte_roles[${index}][role_group]" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกกลุ่ม')"
                        oninput="this.setCustomValidity('')">
                        ${groupDropdown}
                    </select>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <label class="w-full">
                    <span class="fte-field-label text-sm font-semibold">
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
                    <input name="fte_roles[${index}][start_date]" type="text"
                        class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="เลือกวันเริ่มต้นภาระงาน" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกวันเริ่มต้นภาระงาน')"
                        oninput="this.setCustomValidity('')">
                    <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </label>

                <label class="w-full relative">
                    <span class="fte-field-label text-sm font-semibold text-gray-700 mb-1">
                        วันสิ้นสุดภาระงาน
                        <span class="text-red-500">*</span>
                    </span>
                    <input name="fte_roles[${index}][end_date]" type="text"
                        class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="เลือกวันสิ้นสุดภาระงาน" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกวันสิ้นสุดภาระงาน')"
                        oninput="this.setCustomValidity('')">
                    <svg class="w-5 h-5 absolute bottom-2.5 left-3 text-gray-500 pointer-events-none"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </label>
            </div>

            <label class="w-full">
                <span class="fte-field-label text-sm font-semibold">
                    ชั่วโมงการทำงาน (Hour)
                    <span class="text-red-500">*</span>
                </span>
                <input type="number" step="0.1" min="0"
                    name="fte_roles[${index}][hours]"
                    class="fte-hours w-full border p-2 rounded text-right" placeholder="0.0" required
                    oninvalid="this.setCustomValidity('กรุณาระบุชั่วโมงการทำงาน')"
                    oninput="this.setCustomValidity('')">
            </label>

            <label class="w-full">
                <span class="fte-field-label text-sm font-semibold">
                    รายละเอียดภารกิจ
                    <span class="text-red-500">*</span>
                </span>
                <select name="fte_roles[${index}][task_detail]" class="w-full border p-2 rounded" required
                    oninvalid="this.setCustomValidity('กรุณาเลือกรายละเอียดภารกิจ')"
                    oninput="this.setCustomValidity('')">
                    ${taskDropdown}
                </select>
            </label>
        </div>

        <div class="md:col-span-3">
            <div class="fte-metrics-grid">
                <label class="fte-metric-card fte-metric-card--research block border rounded-xl">
                    <span class="block text-sm font-bold text-blue-800 mb-2">FTE (ผู้วิจัย)</span>
                    <input name="fte_roles[${index}][fte_value]" type="number" step="0.00001"
                        class="fte-value w-full border p-3 rounded text-right bg-gray-100" readonly>
                </label>

                <label class="fte-metric-card fte-metric-card--manager block border rounded-xl">
                    <span class="block text-sm font-bold text-emerald-800 mb-2">FTE (ผู้บริหาร)</span>
                    <input name="fte_roles[${index}][fte_value_research]" type="number" step="0.01"
                        min="0" max="1" class="w-full border p-3 rounded text-right" placeholder="0.00">
                </label>
            </div>
        </div>
    </div>
</div>
`);

            const row = document.getElementById(id);
            keepAddedRowOpen(row);
            initializeMobileFteRow(row);
            collapseAllMobileFteCards(row);
            setMobileFteCollapsed(row, false);
            reindexFTE();
        }

        function confirmMobileFteRemoval(row, callback) {
            const collapsed = getMobileFteCollapsedState(row);
            const name = row.querySelector(`[name^="fte_roles["][name$="[name]"]`)?.value?.trim() || 'ยังไม่ได้ระบุชื่อ';
            const text = `ต้องการลบ ผู้มีส่วนร่วม ชื่อ ${name} หรือไม่?`;

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
            if (summary) summary.innerHTML = collapsed ? getMobileFteSummary(row) : '';
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
                if (removeBtn) {
                    removeBtn.onclick = (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        const container = document.getElementById('fte-container');
                        confirmMobileFteRemoval(row, () => {
                            const finalizeRemoval = () => {
                                row.remove();
                                reindexMobileCreateFteCards();
                            };

                            if (typeof window.removeProjectRowWithScroll === 'function') {
                                window.removeProjectRowWithScroll(row, finalizeRemoval);
                                return;
                            }

                            finalizeRemoval();
                        });
                    };
                }
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
            row.classList.remove('row-collapsed', 'row-collapsible');
            row.dataset.collapsed = '0';
            row.querySelectorAll('.row-collapse-toggle, .row-collapse-summary').forEach((el) => el.remove());
            ensureMobileFteDisclosure(row);
        }

        function initializeMobileFteRow(row) {
            normalizeMobileFteCard(row);
            if (typeof primeLazyThaiDatepickers === 'function') {
                primeLazyThaiDatepickers(row);
            }
        }

        function initMobileFteCards() {
            const container = document.getElementById('fte-container');
            if (!container) return;

            const normalizeAll = () => {
                container.querySelectorAll('.fte-mobile-row').forEach(normalizeMobileFteCard);
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
                            normalizeMobileFteCard(node);
                            return;
                        }
                        node.querySelectorAll?.('.fte-mobile-row').forEach(normalizeMobileFteCard);
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

        document.addEventListener('DOMContentLoaded', initMobileFteCards);
    </script>

    <script>
        // ✅ STATUS COLOR
        function updateStatusColor(selectEl) {
            const selected = selectEl.options[selectEl.selectedIndex];
            const bgColor = selected.getAttribute("data-color") || "#ffffff";
            const isPlaceholder = selected.value === "";
            selectEl.style.backgroundColor = bgColor;
            selectEl.style.color = isPlaceholder ? "#9ca3af" : "#222";
        }

        // ✅ FORMAT BUDGET
        function setupBudgetInput() {
            const input = document.getElementById('budgetInput');
            if (!input) return;

            input.addEventListener('input', () => {
                let value = input.value.replace(/,/g, '');
                const parts = value.split('.');
                if (parts.length > 2) value = parts[0] + '.' + parts[1];
                let [intPart, decimalPart] = value.split('.');
                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                input.value = decimalPart !== undefined ? `${intPart}.${decimalPart}` : intPart;
            });

            input.form?.addEventListener('submit', () => {
                input.value = input.value.replace(/,/g, '');
            });
        }
        function confirmProjectMobileRemoval(callback) {
            if (typeof window.confirmProjectRowRemoval === 'function') {
                window.confirmProjectRowRemoval(callback);
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
            container?.querySelectorAll('.conference-row').forEach((row, index) => {
                row.querySelectorAll('.conference-index').forEach((el) => {
                    el.textContent = index + 1;
                });
            });
        }

        // ✅ REMOVE & REINDEX
        function removeRow(id, type) {
            const el = document.getElementById(id);
            const map = {
                fte: reindexFTE,
                copi: reindexCopi,
                irb: reindexIRB,
                output: reindexOutput,
                coresearch: reindexCoResearch
            };
            const finalizeRemoval = () => {
                el?.remove();
                map[type]?.();
            };

            confirmProjectMobileRemoval(() => {
                if (el && typeof window.removeProjectRowWithScroll === 'function') {
                    window.removeProjectRowWithScroll(el, finalizeRemoval);
                    return;
                }

                finalizeRemoval();
            });
        }


        function reindexOutput() {
            document.querySelectorAll('#output-container .output-row').forEach((row, i) => {
                row.querySelector('.output-index').textContent = i + 1;
            });
        }


        // ============== Output (Create) ==============
        let outputIndex = 1;

        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('output-container');
            if (!container) return;

            container.addEventListener('change', (e) => {
                if (e.target && e.target.classList.contains('output-type')) {
                    handleTypeChange(e.target);
                }
            });
        });



        function handleTypeChange(selectEl) {
            const row = selectEl.closest('.output-row');
            const mount = row.querySelector('.special-mount');
            if (!row || !mount) return;

            // เคลียร์ของเดิม (EA101/EA103)
            mount.innerHTML = '';

            const idx = parseInt(row.getAttribute('data-index') || '0', 10);
            const val = selectEl.value || '';

            if (val.startsWith('EA101')) {
                mount.appendChild(renderEA101Section(idx));
            } else if (val.startsWith('EA103')) {
                mount.appendChild(renderEA103Section(idx));
            }
        }

        /** ===== EA101 Section (Authors) ===== */
        function renderEA101Section(idx) {
            const wrap = document.createElement('div');
            wrap.className = 'ea101-section space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4';
            wrap.innerHTML = `
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">Journal</span>
        <input name="outputs[${idx}][journal]" type="text" class="w-full border p-2 rounded" placeholder="เช่น IEEE Access">
      </label>
      <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">Quartile (Q)</span>
        <select name="outputs[${idx}][quartile]" class="w-full border p-2 rounded" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือก Quartile')"
                                    oninput="this.setCustomValidity('')">
          <option value=""  >เลือก Quartile</option>
          <option value="Q1">Q1</option>
          <option value="Q2">Q2</option>
          <option value="Q3">Q3</option>
          <option value="Q4">Q4</option>
        </select>
      </label>
      <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">Impact Factor (IF)</span>
        <input name="outputs[${idx}][impact_factor]" type="number" step="0.001" class="w-full border p-2 rounded" placeholder="เช่น 3.215">
      </label>
    </div>

    <div class="space-y-3">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <span class="font-semibold text-slate-800">รายชื่อผู้เขียน (Authors)</span>
        <button type="button" class="lux-mobile-add-row-btn add-author-btn whitespace-nowrap px-3 py-2 rounded-lg bg-blue-600 text-sm font-semibold text-white" onclick="addAuthorRow(this)">+ เพิ่มผู้เขียน</button>
      </div>
      <div class="authors-container space-y-3" data-auth-index="0"></div>
    </div>
  `;

            const authorsWrap = wrap.querySelector('.authors-container');
            authorsWrap.appendChild(buildAuthorRow(idx, 0));
            reindexAuthorRows(authorsWrap);

            return wrap;
        }

        function addAuthorRow(btnEl) {
            const section = btnEl.closest('.ea101-section');
            const outputRow = btnEl.closest('.output-row');
            const idx = parseInt(outputRow.getAttribute('data-index') || '0', 10);

            const authorsWrap = section.querySelector('.authors-container');
            let aIdx = parseInt(authorsWrap.getAttribute('data-auth-index') || '0', 10);
            aIdx += 1;
            authorsWrap.setAttribute('data-auth-index', aIdx);

            authorsWrap.appendChild(buildAuthorRow(idx, aIdx));
            reindexAuthorRows(authorsWrap);
        }

        function buildAuthorRow(outputIdx, authorIdx) {
            const row = document.createElement('div');
            row.className = 'author-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3';
            row.innerHTML = `
    <div class="flex items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="author-index inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-blue-100 px-2 text-xs font-bold text-blue-700"></span>
        <span class="text-sm font-semibold text-slate-700">ผู้เขียน</span>
      </div>
      <button type="button" class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600" onclick="removeAuthorRow(this)">ลบ</button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">บทบาทผู้เขียน (Role)</span>
        <select name="outputs[${outputIdx}][authors][${authorIdx}][role]" class="w-full border p-2 rounded" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกบทบาทผู้เขียน')"
                                    oninput="this.setCustomValidity('')">
          <option value=""  >เลือกบทบาท</option>
          <option value="first">First-author</option>
          <option value="co">Co-author</option>
          <option value="corresponding">Corresponding author</option>
        </select>
      </label>

      <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">ชื่อ-สกุล ผู้เขียน</span>
        <input name="outputs[${outputIdx}][authors][${authorIdx}][name]" list="userNames" type="text"
             class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
             placeholder="คำนำหน้า ชื่อ-นามสกุล" required
                                    oninvalid="this.setCustomValidity('กรุณาระบุชื่อผู้เขียน')"
                                    oninput="this.setCustomValidity('')">
      </label>
    </div>
  `;
            return row;
        }

        function removeAuthorRow(btnEl) {
            const row = btnEl.closest('.author-row');
            const authorsWrap = row?.parentElement;
            confirmProjectMobileRemoval(() => {
                row?.remove();
                reindexAuthorRows(authorsWrap);
            });
        }

        /** ===== EA103 Section (Conferences) ===== */
        function renderEA103Section(idx) {
            const wrap = document.createElement('div');
            wrap.className = 'ea103-section space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4';
            wrap.innerHTML = `
    <div class="flex flex-wrap items-center justify-between gap-2">
      <span class="font-semibold text-slate-800">Conferences</span>
      <button type="button" class="lux-mobile-add-row-btn add-conf-btn whitespace-nowrap px-3 py-2 rounded-lg bg-amber-600 text-sm font-semibold text-white" onclick="addConferenceRow(this)">+ เพิ่ม Conference</button>
    </div>
    <div class="conference-tbody space-y-3" data-conf-index="0"></div>
  `;

            const tbody = wrap.querySelector('.conference-tbody');
            tbody.appendChild(buildConferenceRow(idx, 0));
            reindexConferenceRows(tbody);

            return wrap;
        }

        function addConferenceRow(btnEl) {
            const section = btnEl.closest('.ea103-section');
            const outputRow = btnEl.closest('.output-row');
            const idx = parseInt(outputRow.getAttribute('data-index') || '0', 10);

            const tbody = section.querySelector('.conference-tbody');
            let cIdx = parseInt(tbody.getAttribute('data-conf-index') || '0', 10);
            cIdx += 1;
            tbody.setAttribute('data-conf-index', cIdx);

            tbody.appendChild(buildConferenceRow(idx, cIdx));
            reindexConferenceRows(tbody);
        }

        function buildConferenceRow(outputIdx, cIdx) {
            const row = document.createElement('div');
            row.className = 'conference-row rounded-lg border border-slate-200 bg-white p-3 shadow-sm space-y-3';
            row.innerHTML = `
    <div class="flex items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="conference-index inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-amber-100 px-2 text-xs font-bold text-amber-700"></span>
        <span class="text-sm font-semibold text-slate-700">Conference</span>
      </div>
      <button type="button" class="shrink-0 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-semibold text-red-600" onclick="removeConferenceRow(this)">ลบ</button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      <label>
        <span class="block text-sm text-gray-700 mb-1">ชื่อเวที/Conference</span>
        <input name="outputs[${outputIdx}][conferences][${cIdx}][name]" class="w-full border p-2 rounded" placeholder="เช่น PMAC 2025">
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">ประเภทนำเสนอ</span>
        <select name="outputs[${outputIdx}][conferences][${cIdx}][presentation_type]" class="w-full border p-2 rounded">
          <option value="">เลือก</option>
          <option value="Oral">Oral</option>
          <option value="Poster">Poster</option>
          <option value="Panel">Panel</option>
          <option value="Invited Talk">Invited Talk</option>
        </select>
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">สถานที่</span>
        <input name="outputs[${outputIdx}][conferences][${cIdx}][location]" class="w-full border p-2 rounded" placeholder="เมือง/ประเทศ">
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">หมายเหตุ</span>
        <input name="outputs[${outputIdx}][conferences][${cIdx}][note]" class="w-full border p-2 rounded" placeholder="หมายเหตุเพิ่มเติม">
      </label>
    </div>
  `;
            return row;
        }

        function removeConferenceRow(btn) {
            const row = btn.closest('.conference-row');
            const container = row?.parentElement;
            confirmProjectMobileRemoval(() => {
                row?.remove();
                reindexConferenceRows(container);
            });
        }

        /** ===== util reindex ===== */
        function reindexOutput() {
            const rows = document.querySelectorAll('#output-container .output-row');
            rows.forEach((row, i) => {
                row.id = `output-${i}`;
                row.setAttribute('data-index', i);
                const idxEl = row.querySelector('.output-index');
                if (idxEl) idxEl.textContent = i + 1;

                row.querySelectorAll(
                        'input[name^="outputs["], select[name^="outputs["], textarea[name^="outputs["]')
                    .forEach(el => {
                        const old = el.getAttribute('name');
                        if (!old) return;
                        el.setAttribute('name', old.replace(/^outputs\[\d+\]/, `outputs[${i}]`));
                    });
            });
        }




        // ✅ INITIALIZE
        document.addEventListener('DOMContentLoaded', () => {
            // initThaiDatepickers();
            setupBudgetInput();

            document.querySelectorAll("select[id$='_status']").forEach(updateStatusColor);
        });

        document.querySelector('[name="project_name_th"]').addEventListener('blur', function() {
            const name = this.value.trim();
            if (name.length < 5) return;

            fetch('{{ route('projects.checkName') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        project_name_th: name
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exact) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'ชื่อโครงการซ้ำ',
                            text: '⚠️ มีชื่อโครงการนี้อยู่ในระบบแล้ว กรุณาตรวจสอบ!',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: '🔁 กรอกใหม่',
                            cancelButtonText: '📋 กลับไปยังรายการ'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // 🗑️ ลบ autosave key ที่ใช้งานจริง
                                const FORM_KEY =
                                    `autosave:project:${window.AUTH_USER_ID}:${location.pathname}`;
                                localStorage.removeItem(FORM_KEY);

                                console.log('🗑️ autosave cleared:', FORM_KEY);

                                localStorage.removeItem(FORM_KEY);
                                location.reload(); // กรอกใหม่
                            } else if (result.dismiss === Swal.DismissReason.cancel) {
                                window.location.href =
                                    '{{ route('projects.index') }}'; // กลับไปยังรายการ
                            }
                        });
                    } else if (data.similar.length > 0) {
                        showSimilarModal(data.similar);
                    }
                });

        });

        function showSimilarModal(similarProjects) {
            const modal = document.getElementById('project-name-modal');
            const list = document.getElementById('similar-projects-list');
            list.innerHTML = '';
            similarProjects.forEach(p => {
                const li = document.createElement('li');
                li.innerHTML =
                    `<strong>ชื่อ:</strong> ${p.project_name_th}<br><strong>PI:</strong> ${p.pi_name}<br><strong>ปี:</strong> ${p.start_year}`;
                list.appendChild(li);
            });
            modal.classList.remove('hidden');
        }

        function continueAnyway() {
            document.getElementById('project-name-modal').classList.add('hidden');
        }

        function resetForm() {
            document.getElementById('project-name-modal').classList.add('hidden');
            location.reload(); // 🔄 รีโหลดหน้าเว็บใหม่
        }
    </script>

    {{-- <script>
        flatpickr(".flatpickr-year", {
            plugins: [
                new flatpickr.plugins.monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y", // หรือ "F Y" หากต้องการเดือนด้วย
                    altFormat: "Y"
                })
            ],
            locale: "th"
        });
    </script> --}}
    <script>
        new TomSelect('#pi_name', {
            create: true,
            maxItems: 1,
            persist: false,
            plugins: ['remove_button']
        });
    </script>

    <script>
        (() => {
            if (!window.AUTH_USER_ID) return;

            const FORM = document.getElementById('projectForm');
            if (!FORM) return;

            const FORM_KEY = `autosave:project:${window.AUTH_USER_ID}:${location.pathname}`;
            const TTL_HOURS = 48;
            const HAS_SERVER_ERRORS = @json($errors->any());

            /* ---------- helpers ---------- */
            const now = () => Date.now();
            const expired = ts => (now() - ts) > TTL_HOURS * 3600 * 1000;

            const collectForm = () => {
                const data = {};
                FORM.querySelectorAll('input, select, textarea').forEach(el => {
                    if (!el.name) return;
                    if (el.type === 'checkbox') data[el.name] = el.checked;
                    else if (el.type === 'radio') {
                        if (el.checked) data[el.name] = el.value;
                    } else data[el.name] = el.value;
                });
                return data;
            };

            const save = () => {
                localStorage.setItem(FORM_KEY, JSON.stringify({
                    ts: now(),
                    data: collectForm()
                }));
            };

            let timer = null;
            const debounceSave = () => {
                clearTimeout(timer);
                timer = setTimeout(save, 400);
            };

            FORM.addEventListener('input', debounceSave);
            FORM.addEventListener('change', debounceSave);
            FORM.addEventListener('submit', () => {
                clearTimeout(timer);
                save();
            });

            /* ---------- dynamic rows ---------- */
            const maxIndex = (data, prefix) => {
                let max = -1;
                Object.keys(data).forEach(k => {
                    const m = k.match(new RegExp(`^${prefix}\\[(\\d+)\\]`));
                    if (m) max = Math.max(max, parseInt(m[1], 10));
                });
                return max;
            };

            const addRepeatableRows = (id, maxIdx) => {
                if (maxIdx < 1) return;
                const btn = document.querySelector(`[data-repeatable-add="${id}"]`);
                if (!btn) return;
                for (let i = 0; i < maxIdx; i++) btn.click();
            };

            const syncOutputIndexes = () => {
                document.querySelectorAll('[data-repeatable][data-id="output"] [data-repeatable-row]')
                    .forEach((row, i) => row.setAttribute('data-index', i));
            };

            const collectOutputMeta = (data) => {
                const typeMap = {};
                const authorMax = {};
                const confMax = {};

                Object.keys(data).forEach(k => {
                    let m = k.match(/^outputs\[(\d+)\]\[type\]/);
                    if (m) typeMap[parseInt(m[1], 10)] = data[k];

                    m = k.match(/^outputs\[(\d+)\]\[authors\]\[(\d+)\]/);
                    if (m) {
                        const oIdx = parseInt(m[1], 10);
                        const aIdx = parseInt(m[2], 10);
                        authorMax[oIdx] = Math.max(authorMax[oIdx] ?? -1, aIdx);
                    }

                    m = k.match(/^outputs\[(\d+)\]\[conferences\]\[(\d+)\]/);
                    if (m) {
                        const oIdx = parseInt(m[1], 10);
                        const cIdx = parseInt(m[2], 10);
                        confMax[oIdx] = Math.max(confMax[oIdx] ?? -1, cIdx);
                    }
                });

                return { typeMap, authorMax, confMax };
            };

            const ensureOutputSections = (data) => {
                const meta = collectOutputMeta(data);
                syncOutputIndexes();

                Object.entries(meta.typeMap).forEach(([idxStr, typeVal]) => {
                    const idx = parseInt(idxStr, 10);
                    const select = FORM.querySelector(`[name="outputs[${idx}][type]"]`);
                    if (!select) return;
                    select.value = typeVal;
                    handleTypeChange(select);
                });

                Object.entries(meta.authorMax).forEach(([idxStr, maxIdx]) => {
                    if (maxIdx < 1) return;
                    const idx = parseInt(idxStr, 10);
                    const row = FORM.querySelector(`[name="outputs[${idx}][title]"]`)?.closest('.output-row');
                    const btn = row?.querySelector('.ea101-section button[onclick*="addAuthorRow"]');
                    if (!btn || typeof addAuthorRow !== 'function') return;
                    for (let i = 0; i < maxIdx; i++) addAuthorRow(btn);
                });

                Object.entries(meta.confMax).forEach(([idxStr, maxIdx]) => {
                    if (maxIdx < 1) return;
                    const idx = parseInt(idxStr, 10);
                    const row = FORM.querySelector(`[name="outputs[${idx}][title]"]`)?.closest('.output-row');
                    const btn = row?.querySelector('.ea103-section button[onclick*="addConferenceRow"]');
                    if (!btn || typeof addConferenceRow !== 'function') return;
                    for (let i = 0; i < maxIdx; i++) addConferenceRow(btn);
                });
            };

            const parseLocalDateValue = (raw) => {
                const val = String(raw ?? '').trim();
                if (!val) return null;

                let match = val.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{4})$/);
                if (match) {
                    const [, dd, mm, yyyy] = match;
                    return new Date(Number(yyyy), Number(mm) - 1, Number(dd));
                }

                match = val.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})$/);
                if (match) {
                    const [, yyyy, mm, dd] = match;
                    return new Date(Number(yyyy), Number(mm) - 1, Number(dd));
                }

                return null;
            };

            const NO_PAST_CLIENT_TODAY = (() => {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                return today;
            })();

            const applyForm = (data) => {
                Object.entries(data).forEach(([name, value]) => {
                    const el = FORM.querySelector(`[name="${CSS.escape(name)}"]`);
                    if (!el) return;

                    if (el.type === 'checkbox') el.checked = !!value;
                    else if (el.type === 'radio') {
                        const r = FORM.querySelector(
                            `[name="${CSS.escape(name)}"][value="${CSS.escape(value)}"]`
                        );
                        if (r) r.checked = true;
                    } else if (el._flatpickr) {
                        const val = String(value ?? '').trim();
                        if (!val) {
                            el._flatpickr.clear();
                        } else {
                            const parsed = parseLocalDateValue(val);
                            const noPast = el.dataset.noPast === '1';
                            const isPastDate = parsed instanceof Date && !Number.isNaN(parsed.getTime()) &&
                                parsed < NO_PAST_CLIENT_TODAY;

                            if (noPast && isPastDate) {
                                el._flatpickr.clear();
                            } else {
                                el._flatpickr.setDate(val, false);
                            }
                        }
                    } else {
                        el.value = value;
                    }

                    el.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                });
            };

            const formatThaiDateTime = (timestamp) => {
                const d = new Date(timestamp);

                const day = d.getDate();
                const month = d.toLocaleString('th-TH', {
                    month: 'long'
                });
                const year = d.getFullYear() + 543;

                const hh = String(d.getHours()).padStart(2, '0');
                const mm = String(d.getMinutes()).padStart(2, '0');

                return `${day} ${month} ${year} เวลา ${hh}:${mm}`;
            };


            const askRestore = (payload, yes, no) => {
                const projectName =
                    payload?.data?.project_name_th?.trim() || '— ยังไม่ได้ระบุชื่อโครงการ —';

                const savedAt = formatThaiDateTime(payload.ts);

                if (window.Swal) {
                    Swal.fire({
                        icon: 'question',
                        title: 'พบข้อมูลที่บันทึกไว้',
                        html: `
                <div class="text-left space-y-2">
                    <div>
                        <strong>ชื่อโครงการ (ภาษาไทย)</strong><br>
                        <span class="text-gray-800">${projectName}</span>
                    </div>
                    <div>
                        <strong>บันทึกล่าสุดเมื่อ</strong><br>
                        <span class="text-gray-700">${savedAt}</span>
                    </div>
                </div>
            `,
                        showCancelButton: true,
                        confirmButtonText: '📂 กู้ข้อมูล',
                        cancelButtonText: '❌ ไม่ใช้ข้อมูลนี้',
                        focusCancel: true
                    }).then(r => r.isConfirmed ? yes() : no());
                } else {
                    const msg =
                        `ชื่อโครงการ: ${projectName}\n` +
                        `บันทึกล่าสุด: ${savedAt}\n\n` +
                        `ต้องการกู้ข้อมูลนี้หรือไม่?`;

                    confirm(msg) ? yes() : no();
                }
            };

            const banner = document.getElementById('autosave-banner');
            const bannerMeta = document.getElementById('autosave-banner-meta');
            const bannerRestore = document.getElementById('autosave-banner-restore');
            const bannerDiscard = document.getElementById('autosave-banner-discard');
            const restoreDraft = (payload) => {
                const data = payload.data;
                addRepeatableRows('irb', maxIndex(data, 'irbs'));
                addRepeatableRows('copi', maxIndex(data, 'copis'));
                addRepeatableRows('coresearch', maxIndex(data, 'coresearch'));
                addRepeatableRows('fte', maxIndex(data, 'fte_roles'));
                addRepeatableRows('output', maxIndex(data, 'outputs'));
                ensureOutputSections(data);
                applyForm(data);
            };


            document.addEventListener('DOMContentLoaded', () => {
                const raw = localStorage.getItem(FORM_KEY);
                if (!raw) return;

                try {
                    const payload = JSON.parse(raw);
                    if (!payload?.data || expired(payload.ts)) {
                        localStorage.removeItem(FORM_KEY);
                        return;
                    }

                    if (HAS_SERVER_ERRORS) {
                        restoreDraft(payload);
                        banner?.classList.add('hidden');
                        return;
                    }

                    if (banner && bannerMeta && bannerRestore && bannerDiscard) {
                        const projectName =
                            payload?.data?.project_name_th?.trim() || '— ยังไม่ได้ระบุชื่อโครงการ —';
                        bannerMeta.textContent = `ชื่อโครงการ: ${projectName} · บันทึกล่าสุด: ${formatThaiDateTime(payload.ts)}`;
                        banner.classList.remove('hidden');

                        bannerRestore.onclick = () => {
                            banner.classList.add('hidden');
                            askRestore(payload, () => {
                                restoreDraft(payload);
                            }, () => localStorage.removeItem(FORM_KEY));
                        };

                        bannerDiscard.onclick = () => {
                            localStorage.removeItem(FORM_KEY);
                            banner.classList.add('hidden');
                        };
                    }

                    askRestore(
                        payload,
                        () => {
                            restoreDraft(payload);
                        },
                        () => localStorage.removeItem(FORM_KEY)
                    );

                } catch {
                    localStorage.removeItem(FORM_KEY);
                }
            });
        })();
    </script>

    @if (session('success'))
        <script>
            (() => {
                const FORM_KEY = `autosave:project:{{ auth()->id() }}:${location.pathname}`;
                localStorage.removeItem(FORM_KEY);
                console.log('✅ Draft cleared after successful save');
            })();
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('projectForm');
            if (!form) return;

            const updateFormMeta = () => {
                const fteIndexes = new Set();
                form.querySelectorAll('[name^="fte_roles["]').forEach((el) => {
                    if (el.disabled || el.closest('template')) return;
                    const m = String(el.name || '').match(/^fte_roles\[(\d+)\]/);
                    if (m) fteIndexes.add(m[1]);
                });

                const namedFields = Array.from(form.querySelectorAll('input[name], select[name], textarea[name]'))
                    .filter((el) => !el.disabled && !el.closest('template')).length;

                const fteExpected = form.querySelector('#fteExpectedRows');
                const varsExpected = form.querySelector('#formExpectedVars');
                if (fteExpected) fteExpected.value = String(fteIndexes.size);
                if (varsExpected) varsExpected.value = String(namedFields);
            };

            form.addEventListener('submit', updateFormMeta, true);
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

                    // 1. ค้นหาทุกช่องที่มีคำว่า required
                    const requiredFields = projectForm.querySelectorAll('[required]');
                    projectForm.querySelectorAll('[data-repeatable-row], .output-row, .fte-mobile-row, .copi-row, .coresearch-row, .irb-row')
                        .forEach(row => {
                            delete row.dataset.forceOpen;
                        });

                    requiredFields.forEach(field => {
                        if (field.disabled || field.type === 'hidden' || field.closest('template')) {
                            return;
                        }

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
                            const row = field.closest('[data-repeatable-row], .output-row, .fte-mobile-row, .copi-row, .coresearch-row, .irb-row') ||
                                displayTarget.closest('[data-repeatable-row], .output-row, .fte-mobile-row, .copi-row, .coresearch-row, .irb-row');
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

                    // 2. ถ้าไม่ผ่าน ให้หยุดส่งข้อมูลและแจ้งเตือน
                    if (!isValid) {
                        window.syncFteTableValidationUi?.({
                            ensureRender: true
                        });
                        e.preventDefault(); // 🛑 หยุดการส่งฟอร์มทันที
                        e.stopPropagation();

                        // เปิดทุกลิ้นชักที่มีช่องบังคับยังไม่ถูกกรอก
                        invalidRows.forEach(row => {
                            row.classList.remove('row-collapsed');
                            row.dataset.collapsed = '0';
                            row.dataset.forceOpen = '1';
                        });

                        // เลื่อนหน้าจอ + โฟกัสไปที่ช่องแรกที่ยังไม่กรอก
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

        function handleOutputType(el) {
            handleTypeChange(el);
        }

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

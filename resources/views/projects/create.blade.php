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



    <div class="project-form max-w-6xl mx-auto mt-6 bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
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
                        <option value="01 ผู้สูงอายุ ตามยุทธศาสตร์ศาสตร์สุขอายุของคณะแพทยศาสตร์ศิริราชพยาบาล">01 ผู้สูงอายุ
                            ตามยุทธศาสตร์ศาสตร์สุขอายุของคณะแพทยศาสตร์ศิริราชพยาบาล</option>
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
                    <span class="block text-sm text-gray-700 mb-1">ลิงก์ Google Drive / Shortcut</span>
                    <input name="shortcut_link" type="text" class="w-full border p-2 rounded">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ลักษณะโครงการ <span class="text-red-500">*</span></span>
                    <select name="project_list" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกลักษณะโครงการ')"
                        oninput="this.setCustomValidity('')">
                        <option value="">-- เลือกประเภทลักษณะ --</option>
                        <option value="โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภา">
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
                        <option value="" disabled selected>กรุณาเลือกขนาดของโปรเจค</option>
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

                <x-date-be name="team_end_date" label="วันที่สิ้นสุดการทำงานของทีมวิจัย"
                    placeholder="กรุณาเลือกวันที่สิ้นสุดการทำงานของทีมวิจัย" />

                <x-date-be name="proposal_date" label="BA001 - วันที่เริ่มเขียน Proposal"
                    placeholder="กรุณาเลือกวันที่เริ่มเขียน Proposal" />

                <x-date-be name="contract_date" label="BC101 - วันที่สัญญาอนุมัติ"
                    placeholder="กรุณาเลือกวันที่อนุมัติสัญญา" />

                <x-date-be name="start_date" label="BC102 - วันที่โครงการเริ่ม (ตามสัญญา)"
                    placeholder="กรุณาเลือกวันที่เริ่มตามสัญญา" />

                <x-date-be name="end_date" label="BC103 - วันที่สิ้นสุด (ตามสัญญา)"
                    placeholder="กรุณาเลือกวันที่สิ้นสุดตามสัญญา" />

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
            <x-repeatable-row id="irb" addText="+ เพิ่ม IRB" addClass="mb-6 px-3 py-1 bg-red-600 text-white rounded">

                {{-- ✅ ต้องมี container --}}
                <div data-repeatable-container class="space-y-2">

                    {{-- แถวแรก --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end p-3 border rounded-lg bg-gray-50" data-repeatable-row>

                        <div class="col-span-12 md:col-span-1 text-center">
                            <span data-repeatable-index
                                class="inline-flex w-8 h-8 items-center justify-center
                           rounded-full bg-blue-100 text-blue-700 text-sm font-semibold">
                                1
                            </span>
                        </div>

                        <div class="col-span-12 md:col-span-3">
                            <label class="text-sm">รหัส IRB <span class="text-red-500">*</span></label>
                            <input name="irbs[0][code]" class="w-full border p-2 rounded" required
                                oninvalid="this.setCustomValidity('กรุณาระบุรหัส IRB')"
                                oninput="this.setCustomValidity('')">
                        </div>

                        <div class="col-span-12 md:col-span-3">
                            <label class="text-sm">หน่วยงาน <span class="text-red-500">*</span></label>
                            <input name="irbs[0][source]" list="irbSourceList" class="w-full border p-2 rounded" required
                                oninvalid="this.setCustomValidity('กรุณาระบุหน่วยงาน')"
                                oninput="this.setCustomValidity('')">
                        </div>

                        <div class="col-span-12 md:col-span-5 flex flex-col sm:flex-row gap-2 items-end">
                            <x-date-be name="irbs[0][exp_date]" label="วันหมดอายุ IRB" required />
                            <button type="button" class="text-red-500" data-repeatable-remove>✕</button>
                        </div>
                    </div>

                </div>

                {{-- template --}}
                <x-slot:template>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end p-3 border rounded-lg bg-white" data-repeatable-row>

                        <div class="col-span-12 md:col-span-1 text-center">
                            <span data-repeatable-index
                                class="inline-flex w-8 h-8 items-center justify-center
                           rounded-full bg-blue-100 text-blue-700 text-sm font-semibold">
                                #
                            </span>
                        </div>

                        <div class="col-span-12 md:col-span-3">
                            <label class="text-sm">รหัส IRB <span class="text-red-500">*</span></label>
                            <input name="irbs[__i__][code]" class="w-full border p-2 rounded" required
                                oninvalid="this.setCustomValidity('กรุณาระบุรหัส IRB')"
                                oninput="this.setCustomValidity('')">
                        </div>

                        <div class="col-span-12 md:col-span-3">
                            <label class="text-sm">หน่วยงาน <span class="text-red-500">*</span></label>
                            <input name="irbs[__i__][source]" list="irbSourceList" class="w-full border p-2 rounded" required
                                oninvalid="this.setCustomValidity('กรุณาระบุหน่วยงาน')"
                                oninput="this.setCustomValidity('')">
                        </div>

                        <div class="col-span-12 md:col-span-5 flex flex-col sm:flex-row gap-2 items-end">
                            <x-date-be name="irbs[__i__][exp_date]" label="วันหมดอายุ IRB" required />
                            <button type="button" class="text-red-500" data-repeatable-remove>✕</button>
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
            <x-repeatable-row id="copi" addText="+ เพิ่ม COPI" addClass="mb-6 px-3 py-1 bg-yellow-600 text-white rounded">

                {{-- ✅ container --}}
                <div data-repeatable-container class="space-y-3 mb-4">

                    {{-- ================== แถวแรก ================== --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end p-3 border rounded-lg bg-gray-50" data-repeatable-row>

                        {{-- เลขลำดับ --}}
                        <div class="col-span-12 md:col-span-1 text-center">
                            <span data-repeatable-index
                                class="inline-flex w-8 h-8 items-center justify-center
                             rounded-full bg-yellow-100 text-yellow-700
                             text-sm font-semibold">
                                1
                            </span>
                        </div>

                        {{-- ชื่อ COPI --}}
                        <div class="col-span-12 md:col-span-5">
                            <label class="block text-sm text-gray-700 mb-1">
                                ชื่อ COPI <span class="text-red-500">*</span>
                            </label>
                            <input name="copis[0][name]" list="userNames" type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg
                           focus:ring-2 focus:ring-yellow-500"
                                placeholder="คำนำหน้า ชื่อ-นามสกุล" required
                                oninvalid="this.setCustomValidity('กรุณาระบุชื่อ COPI')"
                                oninput="this.setCustomValidity('')">
                        </div>

                        {{-- สังกัด --}}
                        <div class="col-span-12 md:col-span-5">
                            <label class="block text-sm text-gray-700 mb-1">
                                สังกัด COPI <span class="text-red-500">*</span>
                            </label>
                            <input name="copis[0][affiliation]" type="text" list="copiAffiliationList" class="w-full border p-2 rounded"
                                placeholder="สังกัด COPI" required
                                oninvalid="this.setCustomValidity('กรุณาระบุสังกัด COPI')"
                                oninput="this.setCustomValidity('')">
                        </div>

                        {{-- ลบ --}}
                        <div class="col-span-12 md:col-span-1 flex justify-end md:justify-center">
                            <button type="button" class="text-red-600" data-repeatable-remove>
                                ✕
                            </button>
                        </div>
                    </div>

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
            <x-repeatable-row id="coresearch" addText="+ เพิ่ม Co-Researcher" addClass="mb-6 px-3 py-1 bg-purple-600 text-white rounded">

                {{-- ================== container ================== --}}
                <div data-repeatable-container class="space-y-4 mb-4">

                    {{-- ================== แถวแรก ================== --}}
                    <div class="space-y-2 p-4 border rounded-lg bg-gray-50" data-repeatable-row>

                        {{-- แถวชื่อ --}}
                        <label class="block w-full">
                            <span class="block text-sm mb-1">
                                ชื่อ Co-Researcher คนที่ <span class="text-red-500">*</span>
                                <span data-repeatable-index
                                    class="inline-flex ml-1 px-2 py-0.5 rounded-full
                                 bg-purple-100 text-purple-700 text-xs font-semibold">
                                    1
                                </span>
                            </span>

                            <input name="coresearch[0][name]" list="coresearchUsers" type="text"
                                class="w-full border p-2 rounded" placeholder="ยศ ชื่อ นามสกุล" required
                                oninvalid="this.setCustomValidity('กรุณาระบุชื่อ Co-Researcher')"
                                oninput="this.setCustomValidity('')">
                        </label>

                        {{-- แถวสังกัด + สถานะ + ลบ --}}
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">

                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm mb-1">สังกัด <span class="text-red-500">*</span></span>
                                <input name="coresearch[0][affiliation]" type="text" list="coresearchAffiliationList" class="w-full border p-2 rounded"
                                    placeholder="สังกัด" required
                                    oninvalid="this.setCustomValidity('กรุณาระบุสังกัด')"
                                    oninput="this.setCustomValidity('')">
                            </label>

                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm mb-1">
                                    คนภายนอก / ภายใน <span class="text-red-500">*</span>
                                </span>
                                <select name="coresearch[0][unit_status]" required
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
                <h3 class="font-semibold text-lg text-blue-700">D: รายชื่อผู้มีส่วนร่วม</h3>
            </div>

            <x-repeatable-row id="fte" addText="+ เพิ่มผู้มีส่วนร่วม" addClass="mb-6 px-3 py-1 bg-blue-600 text-white rounded">

                {{-- ================== CONTAINER ================== --}}
                <div data-repeatable-container class="space-y-6 mb-6">

                    {{-- ================== ROW 0 ================== --}}
                    <div class="fte-row p-4 border rounded-lg bg-white shadow-sm" data-repeatable-row>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

                            {{-- INDEX --}}
                            <div class="md:col-span-1 flex justify-center pt-2">
                                <span data-repeatable-index
                                    class="inline-flex w-8 h-8 items-center justify-center
                        rounded-full bg-blue-100 text-blue-700
                        text-sm font-semibold">
                                    1
                                </span>
                            </div>

                            {{-- ================= LEFT ================= --}}
                            <div class="md:col-span-8 space-y-4">

                                {{-- NAME + GROUP --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <label>
                                        <span class="block text-sm font-semibold mb-1">
                                            ชื่อ-นามสกุล <span class="text-red-500">*</span>
                                        </span>
                                        <input list="fteUsers" name="fte_roles[0][name]"
                                            class="w-full border p-2 rounded" placeholder="พิมพ์หรือเลือกชื่อ" required>
                                    </label>

                                    <label>
                                        <span class="block text-sm font-semibold mb-1">
                                            กลุ่ม
                                        </span>
                                        <select name="fte_roles[0][role_group]" class="w-full border p-2 rounded" required
                                            oninvalid="this.setCustomValidity('กรุณาเลือกกลุ่ม')"
                                            oninput="this.setCustomValidity('')">
                                            <option value="" disabled selected>เลือกกลุ่ม</option>
                                            @foreach ($roleGroups as $group)
                                                <option value="{{ $group }}">{{ $group }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>

                                {{-- JOB + DATES --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                                    <label>
                                        <span class="block text-sm font-semibold mb-1">
                                            ภาระงาน <span class="text-red-500">*</span>
                                        </span>
                                        <select name="fte_roles[0][job_detail]" class="w-full border p-2 rounded"
                                            required>
                                            <option value="">เลือกภาระงาน</option>
                                            @foreach ($jobDetails as $job)
                                                <option value="{{ $job }}">{{ $job }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <x-date-be name="fte_roles[0][start_date]" label="วันเริ่มต้นภาระงาน"
                                        placeholder="เลือกวันเริ่มต้น" required />

                                    <x-date-be name="fte_roles[0][end_date]" label="วันสิ้นสุดภาระงาน"
                                        placeholder="เลือกวันสิ้นสุด" required />
                                </div>

                                {{-- HOURS --}}
                                <label>
                                    <span class="block text-sm font-semibold mb-1">
                                        ชั่วโมงการทำงาน (Hour)
                                    </span>
                                    <input name="fte_roles[0][hours]" type="number" step="0.1" min="0"
                                        class="fte-hours w-full border p-2 rounded text-right" placeholder="0.0" required
                                        oninvalid="this.setCustomValidity('กรุณาระบุชั่วโมงการทำงาน')"
                                        oninput="this.setCustomValidity('')">
                                </label>

                                {{-- TASK DETAIL --}}
                                <label>
                                    <span class="block text-sm font-semibold mb-1">
                                        รายละเอียดภารกิจ <span class="text-red-500">*</span>
                                    </span>
                                    <select name="fte_roles[0][task_detail]" class="w-full border p-2 rounded" required>
                                        <option value="">เลือกภารกิจ</option>
                                        <option>ติดต่อประสานงาน</option>
                                        <option>ที่ปรึกษาโครงการ</option>
                                        <option>review literature</option>
                                        <option>เขียน proposal</option>
                                        <option>เก็บข้อมูล</option>
                                        <option>วิเคราะห์ข้อมูล</option>
                                        <option>เขียน manuscript</option>
                                        <option>submit journal</option>
                                        <option>นำเสนอผลงาน Oral / Poster</option>
                                    </select>
                                </label>
                            </div>

                            {{-- ================= RIGHT ================= --}}
                            <div class="md:col-span-3 space-y-4">

                                {{-- AUTO FTE --}}
                                <label class="block p-4 border rounded-xl bg-blue-50">
                                    <span class="block text-sm font-bold text-blue-800 mb-2">
                                        FTE (คำนวณอัตโนมัติ)
                                    </span>
                                    <input name="fte_roles[0][fte_value]" type="number" step="0.00001"
                                        class="fte-value w-full border p-3 rounded text-right bg-gray-100"
                                        placeholder="0.00000" readonly>
                                </label>

                                {{-- MANUAL FTE --}}
                                <label class="block p-4 border rounded-xl bg-emerald-50">
                                    <span class="block text-sm font-bold text-emerald-800 mb-2">
                                        FTE (ให้โดยหัวหน้าโครงการ)
                                    </span>
                                    <input name="fte_roles[0][fte_value_research]" type="number" step="0.01"
                                        min="0" max="1" class="w-full border p-3 rounded text-right"
                                        placeholder="0.00">
                                </label>

                                <button type="button" class="w-full py-2 border rounded-lg text-red-600 hover:bg-red-50"
                                    data-repeatable-remove>
                                    ✕ ลบผู้มีส่วนร่วมนี้
                                </button>
                            </div>

                        </div>
                    </div>

                </div>

                {{-- ================== TEMPLATE ================== --}}
                <x-slot:template>
                    <div class="fte-row p-4 border rounded-lg bg-white shadow-sm" data-repeatable-row>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

                            <div class="md:col-span-1 flex justify-center pt-2">
                                <span data-repeatable-index
                                    class="inline-flex w-8 h-8 items-center justify-center
                        rounded-full bg-blue-100 text-blue-700
                        text-sm font-semibold">
                                    #
                                </span>
                            </div>

                            <div class="md:col-span-8 space-y-4">

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <label>
                                        <span class="block text-sm font-semibold mb-1">
                                            ชื่อ-นามสกุล <span class="text-red-500">*</span>
                                        </span>
                                        <input list="fteUsers" name="fte_roles[__i__][name]"
                                            class="w-full border p-2 rounded" required>
                                    </label>

                                    <label>
                                        <span class="block text-sm font-semibold mb-1">
                                            กลุ่ม
                                        </span>
                                        <select name="fte_roles[__i__][role_group]" class="w-full border p-2 rounded" required
                                            oninvalid="this.setCustomValidity('กรุณาเลือกกลุ่ม')"
                                            oninput="this.setCustomValidity('')">
                                            <option value="" disabled selected>เลือกกลุ่ม</option>
                                            @foreach ($roleGroups as $group)
                                                <option value="{{ $group }}">{{ $group }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <label>
                                        <span class="block text-sm font-semibold mb-1">
                                            ภาระงาน <span class="text-red-500">*</span>
                                        </span>
                                        <select name="fte_roles[__i__][job_detail]" class="w-full border p-2 rounded"
                                            required>
                                            <option value="">เลือกภาระงาน</option>
                                            @foreach ($jobDetails as $job)
                                                <option value="{{ $job }}">{{ $job }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <x-date-be name="fte_roles[__i__][start_date]" label="วันเริ่มต้นภาระงาน" required />
                                    <x-date-be name="fte_roles[__i__][end_date]" label="วันสิ้นสุดภาระงาน" required />
                                </div>

                                <label>
                                    <span class="block text-sm font-semibold mb-1">
                                        ชั่วโมงการทำงาน (Hour)
                                    </span>
                                    <input name="fte_roles[__i__][hours]" type="number" step="0.1" min="0"
                                        class="fte-hours w-full border p-2 rounded text-right" required
                                        oninvalid="this.setCustomValidity('กรุณาระบุชั่วโมงการทำงาน')"
                                        oninput="this.setCustomValidity('')">
                                </label>

                                <label>
                                    <span class="block text-sm font-semibold mb-1">
                                        รายละเอียดภารกิจ <span class="text-red-500">*</span>
                                    </span>
                                    <select name="fte_roles[__i__][task_detail]" class="w-full border p-2 rounded"
                                        required>
                                        <option value="">เลือกภารกิจ</option>
                                        <option>ติดต่อประสานงาน</option>
                                        <option>ที่ปรึกษาโครงการ</option>
                                        <option>review literature</option>
                                        <option>เขียน proposal</option>
                                        <option>เก็บข้อมูล</option>
                                        <option>วิเคราะห์ข้อมูล</option>
                                        <option>เขียน manuscript</option>
                                        <option>submit journal</option>
                                        <option>นำเสนอผลงาน Oral / Poster</option>
                                    </select>
                                </label>
                            </div>

                            <div class="md:col-span-3 space-y-4">
                                <label class="block p-4 border rounded-xl bg-blue-50">
                                    <span class="block text-sm font-bold text-blue-800 mb-2">
                                        FTE (คำนวณอัตโนมัติ)
                                    </span>
                                    <input name="fte_roles[__i__][fte_value]" type="number" step="0.00001" placeholder="0.000000"
                                        class="fte-value w-full border p-3 rounded text-right bg-gray-100" readonly>
                                </label>

                                <label class="block p-4 border rounded-xl bg-emerald-50">
                                    <span class="block text-sm font-bold text-emerald-800 mb-2">
                                        FTE (ให้โดยหัวหน้าโครงการ)
                                    </span>
                                    <input name="fte_roles[__i__][fte_value_research]" type="number" step="0.01" placeholder="0.00"
                                        min="0" max="1" class="w-full border p-3 rounded text-right">
                                </label>

                                <button type="button" class="w-full py-2 border rounded-lg text-red-600 hover:bg-red-50"
                                    data-repeatable-remove>
                                    ✕ ลบผู้มีส่วนร่วมนี้
                                </button>
                            </div>

                        </div>
                    </div>
                </x-slot:template>

            </x-repeatable-row>


            <hr class="my-6 border-t-2 border-gray-300">
            <!-- E: ผลงานวิจัย (Output) -->
            <h3 class="font-semibold text-lg text-blue-700 mb-2">E: ผลงานวิจัย (Output)</h3>

            <x-repeatable-row id="output" addText="+ เพิ่ม Output" addClass="mb-6 px-3 py-1 bg-green-600 text-white rounded">

                <div data-repeatable-container class="space-y-4 mb-4">

                    {{-- ROW แรก --}}
                    <div class="output-row p-4 border rounded-lg bg-white space-y-3" data-repeatable-row>

                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-blue-700">
                                ผลงานที่ <span data-repeatable-index>1</span>
                            </span>
                            <button type="button" class="text-red-600" data-repeatable-remove>✕</button>
                        </div>

                        <label>
                            <span class="text-sm">ชื่อผลงาน <span class="text-red-500">*</span></span>
                            <input name="outputs[0][title]" class="w-full border p-2 rounded" required>
                        </label>

                        <div class="grid grid-cols-3 gap-4">
                            <label>
                                <span class="text-sm">ประเภท <span class="text-red-500">*</span></span>
                                <select name="outputs[0][type]" class="output-type w-full border p-2 rounded"
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
                                <input name="outputs[0][project_output]" class="w-full border p-2 rounded">
                            </label>

                            <x-date-be name="outputs[0][year]" label="วันที่ publish" required />
                        </div>

                        {{-- ตรงนี้สำคัญ --}}
                        <div class="special-mount mt-3"></div>
                    </div>

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
                            <input name="outputs[__i__][title]" class="w-full border p-2 rounded" required>
                        </label>

                        <div class="grid grid-cols-3 gap-4">
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
                                <input name="outputs[__i__][project_output]" class="w-full border p-2 rounded">
                            </label>

                            <x-date-be name="outputs[__i__][year]" label="วันที่ publish" required />
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
        // ✅ REMOVE & REINDEX
        function removeRow(id, type) {
            const el = document.getElementById(id);
            if (el) el.remove();

            const map = {
                fte: reindexFTE,
                copi: reindexCopi,
                irb: reindexIRB,
                output: reindexOutput,
                coresearch: reindexCoResearch
            };
            map[type]?.();
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
            wrap.className = 'ea101-section space-y-3 border rounded-xl p-4 bg-blue-50/50';
            wrap.innerHTML = `
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
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

    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <span class="font-semibold text-blue-800">รายชื่อผู้เขียน (Authors)</span>
        <button type="button" class="px-3 py-1 rounded bg-blue-600 text-white" onclick="addAuthorRow(this)">+ เพิ่มผู้เขียน</button>
      </div>
      <div class="authors-container space-y-2" data-auth-index="0"></div>
    </div>
  `;

            const authorsWrap = wrap.querySelector('.authors-container');
            authorsWrap.appendChild(buildAuthorRow(idx, 0));

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
        }

        function buildAuthorRow(outputIdx, authorIdx) {
            const row = document.createElement('div');
            row.className = 'author-row grid grid-cols-1 md:grid-cols-3 gap-3';
            row.innerHTML = `
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

    <div class="flex items-end">
      <button type="button" class="px-3 py-2 rounded border text-red-600 border-red-300" onclick="removeAuthorRow(this)">ลบผู้เขียน</button>
    </div>
  `;
            return row;
        }

        function removeAuthorRow(btnEl) {
            const row = btnEl.closest('.author-row');
            if (row) row.remove();
        }

        /** ===== EA103 Section (Conferences) ===== */
        function renderEA103Section(idx) {
            const wrap = document.createElement('div');
            wrap.className = 'ea103-section space-y-3 border rounded-xl p-4 bg-amber-50/60';
            wrap.innerHTML = `
    <div class="flex items-center justify-between">
      <span class="font-semibold text-amber-800">Conferences</span>
      <button type="button" class="px-3 py-1 rounded bg-amber-600 text-white" onclick="addConferenceRow(this)">+ เพิ่ม Conference</button>
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
        <tbody class="conference-tbody" data-conf-index="0"></tbody>
      </table>
    </div>
  `;

            const tbody = wrap.querySelector('.conference-tbody');
            tbody.appendChild(buildConferenceRow(idx, 0));

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
        }

        function buildConferenceRow(outputIdx, cIdx) {
            const tr = document.createElement('tr');
            tr.className = 'border-t conference-row';
            tr.innerHTML = `
    <td class="px-2 py-2">
      <input name="outputs[${outputIdx}][conferences][${cIdx}][name]" class="w-56 border p-2 rounded" placeholder="เช่น PMAC 2025">
    </td>
    <td class="px-2 py-2">
      <select name="outputs[${outputIdx}][conferences][${cIdx}][presentation_type]" class="w-44 border p-2 rounded">
        <option value="">เลือก</option>
        <option value="Oral">Oral</option>
        <option value="Poster">Poster</option>
        <option value="Panel">Panel</option>
        <option value="Invited Talk">Invited Talk</option>
      </select>
    </td>
    <td class="px-2 py-2">
      <input name="outputs[${outputIdx}][conferences][${cIdx}][location]" class="w-44 border p-2 rounded" placeholder="เมือง/ประเทศ">
    </td>
    <td class="px-2 py-2">
      <input name="outputs[${outputIdx}][conferences][${cIdx}][note]" class="w-56 border p-2 rounded" placeholder="หมายเหตุ">
    </td>
    <td class="px-2 py-2 text-center">
      <button type="button" class="px-2 py-1 text-red-600 border border-red-300 rounded" onclick="removeConferenceRow(this)">ลบ</button>
    </td>
  `;
            return tr;
        }

        function removeConferenceRow(btn) {
            const tr = btn.closest('tr.conference-row');
            if (tr) tr.remove();
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
            FORM.addEventListener('submit', () => localStorage.removeItem(FORM_KEY));

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


            document.addEventListener('DOMContentLoaded', () => {
                const raw = localStorage.getItem(FORM_KEY);
                if (!raw) return;

                try {
                    const payload = JSON.parse(raw);
                    if (!payload?.data || expired(payload.ts)) {
                        localStorage.removeItem(FORM_KEY);
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
                                const data = payload.data;
                                addRepeatableRows('irb', maxIndex(data, 'irbs'));
                                addRepeatableRows('copi', maxIndex(data, 'copis'));
                                addRepeatableRows('coresearch', maxIndex(data, 'coresearch'));
                                addRepeatableRows('fte', maxIndex(data, 'fte_roles'));
                                addRepeatableRows('output', maxIndex(data, 'outputs'));
                                ensureOutputSections(data);
                                applyForm(data);
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
                            const data = payload.data;
                            addRepeatableRows('irb', maxIndex(data, 'irbs'));
                            addRepeatableRows('copi', maxIndex(data, 'copis'));
                            addRepeatableRows('coresearch', maxIndex(data, 'coresearch'));
                            addRepeatableRows('fte', maxIndex(data, 'fte_roles'));
                            addRepeatableRows('output', maxIndex(data, 'outputs'));
                            ensureOutputSections(data);
                            applyForm(data);
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

                    // 1. ค้นหาทุกช่องที่มีคำว่า required
                    const requiredFields = projectForm.querySelectorAll('[required]');
                    projectForm.querySelectorAll('[data-repeatable-row], .output-row, .fte-row, .copi-row, .coresearch-row, .irb-row')
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
                        let displayTarget = field;

                        // // ถ้าเป็น Flatpickr ต้องไปจัดการตัวที่ User เห็น (altInput)
                        // if (field._flatpickr) {
                        //     displayTarget = field._flatpickr.altInput;
                        // }
                        displayTarget = field;


                        if (!val) {
                            isValid = false;
                            displayTarget.classList.add('border-red-500', 'bg-red-50');
                            if (displayTarget.name === 'planning_status') {
                                displayTarget.style.backgroundColor = '#fef2f2';
                                displayTarget.style.color = '#991b1b';
                            }
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
                            if (displayTarget.name === 'planning_status') {
                                updateStatusColor(displayTarget);
                            }
                        }
                    });

                    // 2. ถ้าไม่ผ่าน ให้หยุดส่งข้อมูลและแจ้งเตือน
                    if (!isValid) {
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
@endsection

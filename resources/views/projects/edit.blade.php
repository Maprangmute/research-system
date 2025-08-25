@extends('layouts.app')

@section('content')
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


    <!-- ✅ เพิ่ม Plugin สำหรับเลือกปี -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">

    @php
        $allUsers = \App\Models\User::orderBy('name')->get();
    @endphp



    <div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">

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

        @if ($errors->any())
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="projectForm" class="pb-10 md:pb-0" action="{{ route('projects.update', $project->id) }}" method="POST">
            @csrf
            @method('PUT')

            <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ชื่อโครงการ (ภาษาไทย)*</span>
                    <input name="project_name_th" value="{{ old('project_name_th', $project->project_name_th) }}" required
                        class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ชื่อโครงการ (English)</span>
                    <input name="project_name_en" value="{{ old('project_name_en', $project->project_name_en) }}"
                        class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ประเภทของโครงการ*</span>
                    <select name="project_type" id="project_type" class="w-full border p-2 rounded" >
                        <option value="" disabled {{ old('project_type', $project->project_type) ? '' : 'selected' }}>
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
                    <span class="block text-sm text-gray-700 mb-1">กลุ่มเนื้อหา (Theme)*</span>
                    <select name="theme"  class="w-full border p-2 rounded">
                        <option value="" disabled {{ old('theme', $project->theme) ? '' : 'selected' }}>
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
                    <span class="block text-sm text-gray-700 mb-1">ลักษณะโครงการ</span>
                    <select name="project_list" class="w-full border p-2 rounded">
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
                    <span class="block text-sm text-gray-700 mb-1">ขนาดโครงการ (S/M/L)</span>
                    {{-- <select name="size" required class="w-full border p-2 rounded"> --}}
                    <select name="size" class="w-full border p-2 rounded">
                        <option value="" disabled {{ old('size', $project->size) ? '' : 'selected' }}>
                            กรุณาเลือกขนาดของโปรเจค
                        </option>
                        <option value="S" {{ old('size', $project->size) == 'S' ? 'selected' : '' }}>
                            S (ภายนอก / ภายใน / Cohort Study)
                        </option>
                        <option value="M" {{ old('size', $project->size) == 'M' ? 'selected' : '' }}>
                            M (ภายนอก / ภายใน / Cohort Study)
                        </option>
                        <option value="L" {{ old('size', $project->size) == 'L' ? 'selected' : '' }}>
                            L (ภายนอก / ภายใน / Cohort Study)
                        </option>
                        <option value="XL" {{ old('size', $project->size) == 'XL' ? 'selected' : '' }}>
                            XL (ภายนอก)
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
                    <span class="block text-sm text-gray-700 mb-1">ปีโปรเจค</span>
                    <select name="start_year" class="w-full border p-2 rounded" required>
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
                    <select name="funding_org_type"  class="w-full border p-2 rounded">
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
                    <input name="funding_name" value="{{ old('funding_name', $project->funding_name) }}"
                        class="w-full border p-2 rounded">
                </label>
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
                        onchange="updateStatusColor(this)" >
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
            <h3 class="font-semibold text-lg text-blue-700 mb-2">รหัส IRB</h3>
            <div id="irb-code-container" class="space-y-2 mb-4">
                @foreach ($project->irbs ?? [] as $i => $irb)
                    <div id="irb-{{ $i }}" class="irb-row grid grid-cols-3 gap-2 items-center">
                        <div class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">รหัส IRB ที่ <span
                                    class="irb-index">{{ $i + 1 }}</span></span>
                            <input name="irbs[{{ $i }}][code]" value="{{ $irb['code'] }}"
                                class="w-full border p-2 rounded" placeholder="Eg. 2025xxx">
                        </div>
                        <div class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">จาก (ชื่อหน่วยงาน) ของรหัส IRB ที่ <span
                                    class="irb-index">{{ $i + 1 }}</span></span>
                            <input name="irbs[{{ $i }}][source]" value="{{ $irb['source'] }}"
                                class="w-full border p-2 rounded" placeholder="ชื่อหน่วยงาน">
                        </div>
                        <div class="w-full flex gap-2 items-end">
                            <div class="w-full relative">
                                <span class="block text-sm text-gray-700 mb-1">วันที่หมดอายุ</span>
                                <input name="irbs[{{ $i }}][exp_date]" value="{{ $irb['exp_date'] ?? '' }}"
                                    type="text" placeholder="เลือกวันที่"
                                    class="thai-datepicker w-full border p-2 rounded pl-10">
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
                    <span class="block text-sm text-gray-700 mb-1">CA101 - ชื่อ PI</span>
                    <input name="pi_name" list="piNameList" value="{{ old('pi_name', $project->pi_name) }}"
                        class="w-full border p-2 rounded" placeholder="ยศ-ชื่อ PI">

                    <datalist id="piNameList">
                        @foreach ($allUsers as $user)
                            @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
                            <option value="{{ $fullName }}"></option>
                        @endforeach
                    </datalist>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">CA103 - สังกัด PI</span>
                    <input name="pi_affiliation" value="{{ old('pi_affiliation', $project->pi_affiliation) }}"
                        class="w-full border p-2 rounded" placeholder="สังกัด PI">
                </label>
            </div>





            <h3 class="text-balance text-blue-700 mb-2">รายชื่อผู้ร่วมวิจัย (COPI)</h3>
            <div id="copi-container" class="space-y-2 mb-4">
                @foreach ($project->copis ?? [] as $i => $copi)
                    <div id="copi-{{ $i }}" class="copi-row grid grid-cols-2 gap-4 items-center">
                        <label class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">ชื่อ COPI คนที่ {{ $i + 1 }}</span>
                            <input name="copis[{{ $i }}][name]"
                                value="{{ old('copis.' . $i . '.name', $copi['name']) }}"
                                list="copiList-{{ $i }}" class="w-full border p-2 rounded"
                                placeholder="ยศ ชื่อ-นามสกุล COPI">
                            <datalist id="copiList-{{ $i }}">
                                @foreach ($allUsers as $user)
                                    @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
                                    <option value="{{ $fullName }}"></option>
                                @endforeach
                            </datalist>
                        </label>

                        <div class="flex gap-2 w-full">
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">สังกัด COPI คนที่
                                    {{ $i + 1 }}</span>
                                <input name="copis[{{ $i }}][affiliation]" value="{{ $copi['affiliation'] }}"
                                    class="w-full border p-2 rounded" placeholder="สังกัด COPI">
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
                    <div id="coresearch-{{ $i }}" class="coresearch-row space-y-2">
                        <!-- ชื่อ -->
                        <label class="block w-full">
                            <span class="block text-sm text-gray-700 mb-1">
                                ชื่อ Co-Researcher คนที่ <span class="coresearch-index">{{ $i + 1 }}</span>
                            </span>
                            <input name="coresearch[{{ $i }}][name]"
                                value="{{ old('coresearch.' . $i . '.name', $co->name) }}"
                                list="coresearchList-{{ $i }}" class="w-full border p-2 rounded"
                                placeholder="ยศ ชื่อ-นามสกุล">
                            <datalist id="coresearchList-{{ $i }}">
                                @foreach ($allUsers as $user)
                                    @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
                                    <option value="{{ $fullName }}"></option>
                                @endforeach
                            </datalist>
                        </label>
                        <!-- สังกัด + สถานะ + ปุ่มลบ -->
                        <div class="grid grid-cols-12 gap-4 items-end">
                            <label class="col-span-5">
                                <span class="block text-sm text-gray-700 mb-1">สังกัด</span>
                                <input name="coresearch[{{ $i }}][affiliation]"
                                    value="{{ $co->affiliation }}" class="w-full border p-2 rounded"
                                    placeholder="สังกัด">
                            </label>
                            <label class="col-span-5">
                                <span class="block text-sm text-gray-700 mb-1">คนภายนอก/ภายใน</span>
                                <select name="coresearch[{{ $i }}][unit_status]"
                                    class="w-full border p-2 rounded" required>
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
                            <div class="col-span-2 flex justify-end pt-6">
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




            @php
                $allUsers = \App\Models\User::orderBy('name')->get(); // ดึงผู้ใช้งานทั้งหมด
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

            <hr class="my-6 border-t-2 border-gray-300">
            <!-- Modal Wrapper -->
            <div x-data="{ showFTE: false, currentTab: 'external', setTab(tab) { this.currentTab = tab } }" x-effect="document.body.classList.toggle('overflow-hidden', showFTE)">

                <!-- Header -->
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-semibold text-lg text-blue-700">D: รายชื่อผู้มีส่วนร่วม</h3>
                    <a href="#" @click.prevent="showFTE = true"
                        class="text-sm text-blue-600 hover:underline">รายละเอียดค่า FTE</a>
                </div>

                <!-- Modal -->
                <!-- Overlay + Modal Wrapper -->
                <div x-show="showFTE" x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm"
                    @click.self="showFTE = false" x-trap.noscroll="showFTE" x-init="$watch('showFTE', value => {
                        if (value) { setTimeout(() => $refs.fteModal?.scrollIntoView({ behavior: 'smooth', block: 'center' }), 50) }
                    })">

                    <!-- Modal Container -->
                    <div x-ref="fteModal"
                        class="relative w-full max-w-6xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-xl ring-1 ring-gray-200"
                        @click.stop>

                        <!-- Header -->
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
                                :class="currentTab === 'external' ? 'text-blue-600 border-b-2 border-blue-600 font-semibold' :
                                    'text-gray-500 hover:text-blue-500 border-b-2 border-transparent hover:border-blue-300 transition'">
                                ทุนภายนอก
                            </button>
                            <button @click.prevent="setTab('internal')"
                                :class="currentTab === 'internal' ? 'text-blue-600 border-b-2 border-blue-600 font-semibold' :
                                    'text-gray-500 hover:text-blue-500 border-b-2 border-transparent hover:border-blue-300 transition'">
                                ทุนภายใน
                            </button>
                            <button @click.prevent="setTab('cohort')"
                                :class="currentTab === 'cohort' ? 'text-blue-600 border-b-2 border-blue-600 font-semibold' :
                                    'text-gray-500 hover:text-blue-500 border-b-2 border-transparent hover:border-blue-300 transition'">
                                COHORT
                            </button>
                        </div>

                        <!-- Content Area -->
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

                        <!-- Footer (optional) -->
                        <div class="px-6 py-4 bg-gray-50 text-right border-t">
                            <button @click="showFTE = false"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                ปิดหน้าต่าง
                            </button>
                        </div>

                    </div>
                </div>
                <div id="fte-container" class="space-y-4 mb-4">
                    @foreach ($project->fteRoles ?? [] as $i => $fte)
                        <div id="fte-{{ $i }}" class="fte-row p-4 border rounded-lg bg-white shadow-sm">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

                                {{-- ซ้าย: รายละเอียดทั้งหมด --}}
                                <div class="md:col-span-9 space-y-4">
                                    {{-- บรรทัด 1: ชื่อ-นามสกุล + กลุ่ม --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <label class="w-full">
                                            <span class="block text-sm font-semibold text-gray-700 mb-1">
                                                <span class="fte-index">{{ $loop->iteration }}</span>. ชื่อ-นามสกุล
                                            </span>
                                            <select name="fte_roles[{{ $i }}][name]"
                                                class="w-full border p-2 rounded">
                                                <option value="" disabled
                                                    {{ empty($fte->name) ? 'selected' : '' }}>เลือกผู้รับผิดชอบ</option>
                                                @foreach ($allUsers as $user)
                                                    @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
                                                    <option value="{{ $fullName }}" @selected($fte->name == $fullName)>
                                                        {{ $fullName }}</option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label class="w-full">
                                            <span class="block text-sm font-semibold text-gray-700 mb-1">กลุ่ม</span>
                                            <select name="fte_roles[{{ $i }}][role_group]"
                                                class="w-full border p-2 rounded">
                                                <option value="" disabled
                                                    {{ empty($fte->role_group) ? 'selected' : '' }}>เลือกกลุ่ม</option>
                                                @foreach ($roleGroups as $group)
                                                    <option value="{{ $group }}" @selected($fte->role_group == $group)>
                                                        {{ $group }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    </div>

                                    {{-- บรรทัด 2: ภาระงาน + วันที่เริ่ม/สิ้นสุด --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <label class="w-full">
                                            <span class="block text-sm font-semibold text-gray-700 mb-1">ภาระงาน</span>
                                            <select name="fte_roles[{{ $i }}][job_detail]"
                                                class="w-full border p-2 rounded">
                                                <option value="" disabled
                                                    {{ empty($fte->job_detail) ? 'selected' : '' }}>เลือกภาระงาน</option>
                                                @foreach ($jobDetails as $job)
                                                    <option value="{{ $job }}" @selected($fte->job_detail == $job)>
                                                        {{ $job }}</option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label class="w-full relative">
                                            <span
                                                class="block text-sm font-semibold text-gray-700 mb-1">วันเริ่มต้นภาระงาน</span>
                                            <input name="fte_roles[{{ $i }}][start_date]" type="text"
                                                value="{{ $fte->start_date ?? '' }}" placeholder="โปรดใส่วันที่"
                                                class="thai-datepicker w-full border p-2 rounded pl-10">
                                            <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </label>

                                        <label class="w-full relative">
                                            <span
                                                class="block text-sm font-semibold text-gray-700 mb-1">วันสิ้นสุดภาระงาน</span>
                                            <input name="fte_roles[{{ $i }}][end_date]" type="text"
                                                value="{{ $fte->end_date ?? '' }}" placeholder="โปรดใส่วันที่"
                                                class="thai-datepicker w-full border p-2 rounded pl-10">
                                            <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </label>
                                    </div>

                                    {{-- บรรทัด 3: รายละเอียดภารกิจ --}}
                                    <label class="w-full">
                                        <span
                                            class="block text-sm font-semibold text-gray-700 mb-1">รายละเอียดภารกิจ</span>
                                        <select name="fte_roles[{{ $i }}][task_detail]"
                                            class="w-full border p-2 rounded" required>
                                            <option value="" disabled
                                                {{ empty($fte->task_detail) ? 'selected' : '' }}>เลือกภารกิจ</option>
                                            @php
                                                $taskOptions = [
                                                    'ติดต่อประสานงาน',
                                                    'review literature',
                                                    'เขียนproposal',
                                                    'ดำเนินการเอกสารขอirb/submit irb',
                                                    'ดำเนินการเอกสารขอทุน/submit ทุน',
                                                    'เก็บข้อมูล',
                                                    'วิเคราะห์ข้อมูล',
                                                    'เขียนเล่มรายงาน',
                                                    'เขียนmanuscript',
                                                    'submit journal',
                                                    'เบิกเงินรางวัลตีพิมพ์/ค่าตอบแทนตีพิมพ์',
                                                    'นำเสนอผลงานวิจัย Oral/Poster presentation',
                                                    'ประชาสัมพันธ์-เบิกงบประมาณทุน',
                                                ];
                                            @endphp
                                            @foreach ($taskOptions as $task)
                                                <option value="{{ $task }}" @selected($fte->task_detail === $task)>
                                                    {{ $task }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>

                                {{-- ขวา: FTE (ผู้วิจัย) + FTE (ผู้บริหาร) ซ้อนกัน --}}
                                <div class="md:col-span-3 space-y-4">
                                    <label class="block p-4 border rounded-xl bg-blue-50">
                                        <span class="block text-sm font-bold text-blue-800 mb-2">FTE (ผู้วิจัย)</span>
                                        <input name="fte_roles[{{ $i }}][fte_value]" type="number"
                                            step="0.01" min="0" max="1"
                                            value="{{ $fte->fte_value ?? '' }}"
                                            class="w-full border p-3 rounded font-semibold text-right" placeholder="0.00"
                                            required>
                                    </label>

                                    <label class="block p-4 border rounded-xl bg-emerald-50">
                                        <span class="block text-sm font-bold text-emerald-800 mb-2">FTE (ผู้บริหาร)</span>
                                        <input name="fte_roles[{{ $i }}][fte_value_research]" type="number"
                                            step="0.01" min="0" max="1"
                                            value="{{ $fte->fte_value_research ?? '' }}"
                                            class="w-full border p-3 rounded font-semibold text-right" placeholder="0.00">
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
                {{-- E: ผลงานวิจัย (Output) ========================= --}}
                <h3 class="font-semibold text-lg text-blue-700 mb-2">E: ผลงานวิจัย (Output)</h3>

                <div id="output-container" class="space-y-4 mb-6">
                    @foreach ($project->outputs ?? [] as $i => $out)
                        <div id="output-{{ $i }}"
                            class="output-row space-y-2 border p-4 rounded-lg bg-gray-50"
                            data-index="{{ $i }}">

                            {{-- Output ID --}}
                            <input type="hidden" name="outputs[{{ $i }}][id]"
                                value="{{ $out->id }}">

                            {{-- Title --}}
                            <label class="block w-full">
                                <span class="block text-sm text-gray-700 mb-1">
                                    ชื่อผลงานที่ <span class="output-index">{{ $loop->iteration }}</span>
                                </span>
                                <input name="outputs[{{ $i }}][title]" value="{{ $out->title }}"
                                    class="w-full border p-2 rounded" placeholder="ชื่อผลงาน">
                            </label>

                            {{-- Type + Remark + Year --}}
                            <div class="grid grid-cols-3 gap-4 items-end">
                                <label class="w-full">
                                    <span class="block text-sm text-gray-700 mb-1">ประเภทของผลงาน</span>
                                    <select name="outputs[{{ $i }}][type]"
                                        class="w-full border p-2 rounded output-type" onchange="handleTypeChange(this)"
                                        >
                                        <option value="" disabled {{ empty($out->type) ? 'selected' : '' }}>
                                            เลือกประเภทของผลงาน</option>
                                        {{-- เก็บ “ชื่อเต็ม” เป็นค่า --}}
                                        @php
                                            $typeFull = $out->type; // สมมติใน DB เก็บชื่อเต็ม (เช่น "EA101 Research Paper or Journal Article")
                                        @endphp
                                        <option value="EA101 Research Paper or Journal Article"
                                            @selected(str_contains($typeFull, 'EA101'))>
                                            EA101 Research Paper or Journal Article
                                        </option>
                                        <option value="EA102 Reports and White papers" @selected($typeFull === 'EA102 Reports and White papers')>
                                            EA102 Reports and White papers
                                        </option>
                                        <option value="EA103 Presentations in Academic Conferences"
                                            @selected($typeFull === 'EA103 Presentations in Academic Conferences')>
                                            EA103 Presentations in Academic Conferences
                                        </option>
                                        <option value="EA104 Data Sets and Databases" @selected($typeFull === 'EA104 Data Sets and Databases')>
                                            EA104 Data Sets and Databases
                                        </option>
                                        <option value="EA105 Inventions or Innovations (Application /Website)"
                                            @selected($typeFull === 'EA105 Inventions or Innovations (Application /Website)')>
                                            EA105 Inventions or Innovations (Application /Website)
                                        </option>
                                        <option value="EA106 Policy Recommendations" @selected($typeFull === 'EA106 Policy Recommendations')>
                                            EA106 Policy Recommendations
                                        </option>
                                        <option value="EA107 Educational Materials" @selected($typeFull === 'EA107 Educational Materials')>
                                            EA107 Educational Materials
                                        </option>
                                        <option value="EA108 Communication Materials (Creations)"
                                            @selected($typeFull === 'EA108 Communication Materials (Creations)')>
                                            EA108 Communication Materials (Creations)
                                        </option>
                                        <option value="EA109 Change in Practices ,Procedures, Policy"
                                            @selected($typeFull === 'EA109 Change in Practices ,Procedures, Policy')>
                                            EA109 Change in Practices ,Procedures, Policy
                                        </option>
                                        <option value="EA110 Number of participants (persons)"
                                            @selected($typeFull === 'EA110 Number of participants (persons)')>
                                            EA110 Number of participants (persons)
                                        </option>
                                        <option value="EA111 Satisfaction Rating (1-5)" @selected($typeFull === 'EA111 Satisfaction Rating (1-5)')>
                                            EA111 Satisfaction Rating (1-5)
                                        </option>
                                        <option value="EA112 Present to Committee Meeting / Policy Maker / Board"
                                            @selected($typeFull === 'EA112 Present to Committee Meeting / Policy Maker / Board')>
                                            EA112 Present to Committee Meeting / Policy Maker / Board
                                        </option>
                                        <option value="EA113 คำสั่งแต่งตั้ง Committees เน้นที่ SIHP เป็นกรรมการและหรือเลขา"
                                            @selected($typeFull === 'EA113 คำสั่งแต่งตั้ง Committees เน้นที่ SIHP เป็นกรรมการและหรือเลขา')>
                                            EA113 คำสั่งแต่งตั้ง Committees เน้นที่ SIHP เป็นกรรมการและหรือเลขา
                                        </option>
                                        <option value="EA114 Events / Forum" @selected($typeFull === 'EA114 Events / Forum')>
                                            EA114 Events / Forum
                                        </option>
                                        <option value="EA115 Capacity Building" @selected($typeFull === 'EA115 Capacity Building')>
                                            EA115 Capacity Building
                                        </option>
                                        <option value="EA116 Research Project" @selected($typeFull === 'EA116 Research Project')>
                                            EA116 Research Project
                                        </option>
                                        <option value="EA117 MOU" @selected($typeFull === 'EA117 MOU')>
                                            EA117 MOU
                                        </option>
                                    </select>
                                </label>

                                <label class="w-full">
                                    <span class="block text-sm text-gray-700 mb-1">รายละเอียด / หมายเหตุ / link</span>
                                    <input name="outputs[{{ $i }}][project_output]"
                                        value="{{ $out->project_output }}" class="w-full border p-2 rounded"
                                        placeholder="เช่น ตีพิมพ์แล้ว / ลิงก์">
                                </label>

                                <div class="flex gap-2 w-full">
                                    <label class="w-full relative">
                                        <span class="block text-sm text-gray-700 mb-1">วันที่เผยแพร่ผลงาน</span>
                                        <input name="outputs[{{ $i }}][year]" type="text"
                                            value="{{ $out->year }}"
                                            class="thai-datepicker w-full border p-2 rounded pl-10"
                                            placeholder="โปรดใส่วันที่">
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

                            {{-- EA101 extra fields mount --}}
                            <div class="ea101-mount">
                                @if (str_contains($typeFull, 'EA101'))
                                    <div class="ea101-section space-y-3 border rounded-xl p-4 bg-blue-50/50">
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                            <label>
                                                <span class="block text-sm text-gray-700 mb-1">Journal</span>
                                                <input name="outputs[{{ $i }}][journal]"
                                                    value="{{ $out->journal }}" class="w-full border p-2 rounded"
                                                    placeholder="ชื่อ Journal">
                                            </label>
                                            <label>
                                                <span class="block text-sm text-gray-700 mb-1">Quartile (Q)</span>
                                                <select name="outputs[{{ $i }}][quartile]"
                                                    class="w-full border p-2 rounded">
                                                    <option value="" disabled>เลือก Quartile</option>
                                                    <option value="Q1" @selected($out->quartile == 'Q1')>Q1</option>
                                                    <option value="Q2" @selected($out->quartile == 'Q2')>Q2</option>
                                                    <option value="Q3" @selected($out->quartile == 'Q3')>Q3</option>
                                                    <option value="Q4" @selected($out->quartile == 'Q4')>Q4</option>
                                                </select>
                                            </label>
                                            <label>
                                                <span class="block text-sm text-gray-700 mb-1">Impact Factor</span>
                                                <input name="outputs[{{ $i }}][impact_factor]" type="number"
                                                    step="0.001" value="{{ $out->impact_factor }}"
                                                    class="w-full border p-2 rounded" placeholder="เช่น 3.215">
                                            </label>
                                        </div>

                                        {{-- Authors --}}
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="font-semibold text-blue-800">รายชื่อผู้เขียน (Authors)</span>
                                                <button type="button" class="px-3 py-1 rounded bg-blue-600 text-white"
                                                    onclick="addAuthorRow(this)">+ เพิ่มผู้เขียน</button>
                                            </div>

                                            {{-- ใช้ id ไม่ซ้ำต่อ output index --}}
                                            <div class="authors-container space-y-2" id="authors-{{ $i }}">
                                                @foreach ($out->authors as $j => $auth)
                                                    <div class="author-row grid grid-cols-1 md:grid-cols-3 gap-3">
                                                        <label>
                                                            <span
                                                                class="block text-sm text-gray-700 mb-1">บทบาทผู้เขียน</span>
                                                            <select
                                                                name="outputs[{{ $i }}][authors][{{ $j }}][role]"
                                                                class="w-full border p-2 rounded">
                                                                <option value="">เลือกบทบาท</option>
                                                                <option value="first" @selected($auth->role === 'first')>
                                                                    First-author</option>
                                                                <option value="co" @selected($auth->role === 'co')>
                                                                    Co-author</option>
                                                                <option value="corresponding"
                                                                    @selected($auth->role === 'corresponding')>Corresponding</option>
                                                            </select>
                                                        </label>
                                                        <label>
                                                            <span
                                                                class="block text-sm text-gray-700 mb-1">ชื่อผู้เขียน</span>
                                                            {{-- สำคัญ: hidden id เพื่อไม่ให้ overwrite กัน --}}
                                                            <input type="hidden"
                                                                name="outputs[{{ $i }}][authors][{{ $j }}][id]"
                                                                value="{{ $auth->id }}">
                                                            <input type="text"
                                                                name="outputs[{{ $i }}][authors][{{ $j }}][name]"
                                                                value="{{ $auth->name }}"
                                                                class="w-full border p-2 rounded" placeholder="ชื่อ-สกุล">
                                                        </label>
                                                        <div class="flex items-end">
                                                            <button type="button" onclick="removeAuthorRow(this)"
                                                                class="px-3 py-2 rounded border text-red-600 border-red-300">ลบ</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" onclick="addOutputRow()" class="mb-6 px-3 py-1 bg-green-600 text-white rounded">
                    + เพิ่ม Output
                </button>




                <hr class="my-6 border-t-2 border-gray-300">
                <!-- ยุทธศาสตร์ Siriraj Health Policy -->
                <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">ยุทธศาสตร์ Siriraj Health Policy</h3>
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
                <div
                    class="max-w-[520px] md:min-w-[420px] bg-white/90 backdrop-blur border shadow-xl rounded-2xl px-4 py-3">
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
        // let irbCodeIndex = {{ count($project->irbs ?? []) }};
        let irbCodeIndex = document.querySelectorAll('.irb-row').length;

        function addIRBCode() {
            const container = document.getElementById('irb-code-container');
            const id = `irb-${irbCodeIndex}`;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="irb-row grid grid-cols-3 gap-2 items-center">
            <div class="w-full">
                <span class="block text-sm text-gray-700 mb-1">รหัส IRB ที่ <span class="irb-index">${irbCodeIndex + 1}</span></span>
                <input name="irbs[${irbCodeIndex}][code]" type="text" placeholder="ECxxxx" class="w-full border p-2 rounded">
            </div>
            <div class="w-full">
                <span class="block text-sm text-gray-700 mb-1">จาก (ชื่อหน่วยงาน) ของรหัส IRB ที่ <span class="irb-index">${irbCodeIndex + 1}</span></span>
                <input name="irbs[${irbCodeIndex}][source]" type="text" placeholder="ชื่อหน่วยงาน" class="w-full border p-2 rounded">
            </div>
            <div class="w-full flex gap-2 items-end">
                <div class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">วันที่หมดอายุ</span>
                    <input name="irbs[${irbCodeIndex}][exp_date]" type="text" class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="เลือกวันที่">
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

            flatpickr(`#${id} .thai-datepicker`, {
                locale: "th",
                dateFormat: "d-m-Y",
                altInput: true,
                altFormat: "j F Y",
                onReady: toBuddhist,
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
            const listId = `copiList-${copiIndex}`;
            const labelNum = copiIndex + 1;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="copi-row grid grid-cols-2 gap-4 items-center">
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">ชื่อ COPI คนที่ <span class="copi-index">${labelNum}</span></span>
                <input name="copis[${copiIndex}][name]" list="${listId}" type="text"
                    class="w-full border p-2 rounded" placeholder="คำนำหน้า ชื่อ-นามสกุล">
                <datalist id="${listId}">
                    ${window.copiOptionsHtml}
                </datalist>
            </label>
            <div class="flex gap-2 w-full">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">สังกัด COPI คนที่ <span class="copi-index">${labelNum}</span></span>
                    <input name="copis[${copiIndex}][affiliation]" type="text"
                        class="w-full border p-2 rounded" placeholder="สังกัด COPI">
                </label>
                <button type="button" onclick="removeRow('${id}', 'copi')" class="text-red-600 mt-6">✕</button>
            </div>
        </div>
    `);

            copiIndex++;
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

            const userOptions = @json(\App\Models\User::orderBy('name')->get()->map(fn($u) => ['fullName' => trim($u->prefix . ' ' . $u->name)]));
            const roleGroups = ['Scientific', 'Management', 'IT + Communication', 'Logistics + Admin'];
            const jobDetails = [
                'Researcher', 'Researcher Assistant', 'Project Manager',
                'Research Coordinator', 'นักวิชาการสารสนเทศ',
                'นักประชาสัมพันธ์', 'เจ้าหน้าที่บริหารงานทั่วไป'
            ];

            const userDropdown = [`<option value="" disabled selected>เลือกผู้รับผิดชอบ</option>`]
                .concat(userOptions.map(u => `<option value="${u.fullName}">${u.fullName}</option>`))
                .join('');

            const groupDropdown = [`<option value="" disabled selected>เลือกกลุ่ม</option>`]
                .concat(roleGroups.map(g => `<option value="${g}">${g}</option>`))
                .join('');

            const jobDropdown = [`<option value="" disabled selected>เลือกภาระงาน</option>`]
                .concat(jobDetails.map(j => `<option value="${j}">${j}</option>`))
                .join('');

            container.insertAdjacentHTML('beforeend', `
  <div id="${id}" class="fte-row p-4 border rounded-lg bg-white shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
      
      <!-- ซ้าย: รายละเอียด -->
      <div class="md:col-span-9 space-y-4">
        <!-- บรรทัด 1: ชื่อ-นามสกุล + กลุ่ม -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <label class="w-full">
            <span class="block text-sm font-semibold text-gray-700 mb-1">
              <span class="fte-index">${index + 1}</span>. ชื่อ-นามสกุล
            </span>
            <select name="fte_roles[${index}][name]" class="w-full border p-2 rounded">
              ${userDropdown}
            </select>
          </label>
          <label class="w-full">
            <span class="block text-sm font-semibold text-gray-700 mb-1">กลุ่ม</span>
            <select name="fte_roles[${index}][role_group]" class="w-full border p-2 rounded">
              ${groupDropdown}
            </select>
          </label>
        </div>

        <!-- บรรทัด 2: ภาระงาน + วันที่ -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <label class="w-full">
            <span class="block text-sm font-semibold text-gray-700 mb-1">ภาระงาน</span>
            <select name="fte_roles[${index}][job_detail]" class="w-full border p-2 rounded">
              ${jobDropdown}
            </select>
          </label>
          <label class="w-full relative">
            <span class="block text-sm font-semibold text-gray-700 mb-1">วันเริ่มต้นภาระงาน</span>
            <input name="fte_roles[${index}][start_date]" type="text" placeholder="โปรดใส่วันที่"
                   class="thai-datepicker w-full border p-2 rounded pl-10">
            <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </label>
          <label class="w-full relative">
            <span class="block text-sm font-semibold text-gray-700 mb-1">วันสิ้นสุดภาระงาน</span>
            <input name="fte_roles[${index}][end_date]" type="text" placeholder="โปรดใส่วันที่"
                   class="thai-datepicker w-full border p-2 rounded pl-10">
            <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </label>
        </div>

        <!-- รายละเอียดภารกิจ -->
        <label class="w-full">
          <span class="block text-sm font-semibold text-gray-700 mb-1">รายละเอียดภารกิจ</span>
          <select name="fte_roles[${index}][task_detail]" class="w-full border p-2 rounded" >
            <option value="" disabled selected>เลือกภารกิจ</option>
            <option>ติดต่อประสานงาน</option>
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

      <!-- ขวา: FTE ทั้งสอง -->
      <div class="md:col-span-3 space-y-4">
        <label class="block p-4 border rounded-xl bg-blue-50">
          <span class="block text-sm font-bold text-blue-800 mb-2">FTE (ผู้วิจัย)</span>
          <input name="fte_roles[${index}][fte_value]" type="number" step="0.01" min="0" max="1"
                 placeholder="0.00" class="w-full border p-3 rounded font-semibold text-right" required>
        </label>
        <label class="block p-4 border rounded-xl bg-emerald-50">
          <span class="block text-sm font-bold text-emerald-800 mb-2">FTE (ผู้บริหาร)</span>
          <input name="fte_roles[${index}][fte_value_research]" type="number" step="0.01" min="0" max="1"
                 placeholder="0.00" class="w-full border p-3 rounded font-semibold text-right">
        </label>
        <button type="button" onclick="removeRow('${id}', 'fte')"
                class="w-full py-2 border rounded-lg text-red-600 hover:bg-red-50">
          ✕ ลบผู้มีส่วนร่วมนี้
        </button>
      </div>

    </div>
  </div>
`);


            reindexFTE();
            initThaiDatepickers(); // สำคัญ! ต้องเรียกอีกครั้งหลังแทรก HTML ใหม่
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

        // เก็บจำนวน author ต่อ output index
        const authorIndex = {};

        // init authorIndex จากของที่ render มาแล้ว
        document.querySelectorAll('#output-container .output-row').forEach(row => {
            const idx = row.getAttribute('data-index');
            const count = row.querySelectorAll('.authors-container .author-row').length;
            authorIndex[idx] = count; // เริ่มจากจำนวนแถวที่มีอยู่
        });

        // ===== Add Output =====
        function addOutputRow() {
            const idx = outputIndex;
            const id = `output-${idx}`;

            document.getElementById('output-container').insertAdjacentHTML('beforeend', `
    <div id="${id}" class="output-row space-y-2 border p-4 rounded-lg bg-gray-50" data-index="${idx}">
      <input type="hidden" name="outputs[${idx}][id]" value="">
      <label class="block w-full">
        <span class="block text-sm text-gray-700 mb-1">
          ชื่อผลงานที่ <span class="output-index">${idx + 1}</span>
        </span>
        <input name="outputs[${idx}][title]" type="text" class="w-full border p-2 rounded" placeholder="ชื่อผลงาน">
      </label>

      <div class="grid grid-cols-3 gap-4 items-end">
        <label class="w-full">
          <span class="block text-sm text-gray-700 mb-1">ประเภทของผลงาน</span>
          <select name="outputs[${idx}][type]" class="w-full border p-2 rounded output-type" required onchange="handleTypeChange(this)">
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
            <span class="block text-sm text-gray-700 mb-1">วันที่เผยแพร่ผลงาน</span>
            <input name="outputs[${idx}][year]" type="text" class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="โปรดใส่วันที่">
            <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </label>
          <button type="button" onclick="removeRow('${id}','output')" class="text-red-600 mb-2">✕</button>
        </div>
      </div>

      <!-- จุด mount สำหรับ EA101 -->
      <div class="ea101-mount"></div>
    </div>
  `);

            // เริ่มนับ authors ของ output แถวใหม่ = 0
            authorIndex[idx] = 0;

            outputIndex++;
            reindexOutput();

            if (typeof initThaiDatepickers === 'function') {
                initThaiDatepickers();
            }
        }

        // ===== เมื่อเปลี่ยนประเภทผลงาน =====
        function handleTypeChange(selectEl) {
            const row = selectEl.closest('.output-row');
            if (!row) return;
            const idx = parseInt(row.getAttribute('data-index') || '0', 10);
            const mount = row.querySelector('.ea101-mount');
            if (!mount) return;

            if (selectEl.value && selectEl.value.includes('EA101')) {
                if (!row.querySelector('.ea101-section')) {
                    mount.appendChild(renderEA101Section(idx));
                    // init authorIndex สำหรับแถวที่ไม่มี authors มาก่อน
                    if (authorIndex[idx] == null) authorIndex[idx] = 0;
                }
            } else {
                const sec = row.querySelector('.ea101-section');
                if (sec) sec.remove();
            }
        }

        // ===== Template EA101 =====
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
        <span class="block text-sm text-gray-700 mb-1">Impact Factor</span>
        <input name="outputs[${idx}][impact_factor]" type="number" step="0.001" class="w-full border p-2 rounded" placeholder="เช่น 3.215">
      </label>
    </div>

    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <span class="font-semibold text-blue-800">รายชื่อผู้เขียน (Authors)</span>
        <button type="button" class="px-3 py-1 rounded bg-blue-600 text-white" onclick="addAuthorRow(this)">+ เพิ่มผู้เขียน</button>
      </div>
      <div class="authors-container space-y-2" id="authors-${idx}"></div>
    </div>
  `;
            return wrap;
        }

        // ===== เพิ่มผู้เขียน (append แถวใหม่เสมอ) =====
        function addAuthorRow(btnEl) {
            const outputRow = btnEl.closest('.output-row');
            const idx = outputRow.getAttribute('data-index');

            const authorsWrap = outputRow.querySelector(`#authors-${idx}`);
            if (!authorsWrap) return;

            const aIdx = authorIndex[idx] || 0;

            authorsWrap.insertAdjacentHTML('beforeend', `
    <div class="author-row grid grid-cols-1 md:grid-cols-3 gap-3">
      <label>
        <span class="block text-sm text-gray-700 mb-1">บทบาทผู้เขียน</span>
        <select name="outputs[${idx}][authors][${aIdx}][role]" class="w-full border p-2 rounded">
          <option value="">เลือกบทบาท</option>
          <option value="first">First-author</option>
          <option value="co">Co-author</option>
          <option value="corresponding">Corresponding</option>
        </select>
      </label>
      <label>
        <span class="block text-sm text-gray-700 mb-1">ชื่อผู้เขียน</span>
        <!-- ไม่มี hidden id = บันทึกเป็น record ใหม่ -->
        <input type="text" name="outputs[${idx}][authors][${aIdx}][name]" class="w-full border p-2 rounded" placeholder="ชื่อ-สกุล">
      </label>
      <div class="flex items-end">
        <button type="button" onclick="removeAuthorRow(this)" class="px-3 py-2 rounded border text-red-600 border-red-300">ลบ</button>
      </div>
    </div>
  `);

            // อัปเดตตัวชี้วัด index ต่อ output
            authorIndex[idx] = aIdx + 1;
        }

        // ===== ลบผู้เขียน (แค่เอาแถวออกจาก DOM; controller จะจัดการตาม ids ที่ส่งมา) =====
        function removeAuthorRow(btnEl) {
            const row = btnEl.closest('.author-row');
            if (row) row.remove();
        }

        // ===== Reindex ทั้ง output หลังลบแถว =====
        function reindexOutput() {
            const rows = document.querySelectorAll('#output-container .output-row');
            rows.forEach((row, i) => {
                row.id = `output-${i}`;
                row.setAttribute('data-index', i);

                const idxEl = row.querySelector('.output-index');
                if (idxEl) idxEl.textContent = i + 1;

                // อัปเดต name="outputs[oldIndex]..." → outputs[i]...
                row.querySelectorAll('input[name^="outputs["], select[name^="outputs["]')
                    .forEach(el => {
                        const old = el.getAttribute('name');
                        if (old) el.setAttribute('name', old.replace(/^outputs\[\d+\]/, `outputs[${i}]`));
                    });

                // อัปเดต id ของ authors-container ให้ตรง index ใหม่ และ sync authorIndex key
                const authorsWrap = row.querySelector('.authors-container');
                if (authorsWrap) {
                    authorsWrap.id = `authors-${i}`;
                }

                // นับจำนวน author-row ใหม่ให้ authorIndex[i] = current count
                const count = row.querySelectorAll('.authors-container .author-row').length;
                authorIndex[i] = count;
            });

            outputIndex = rows.length;
        }

        // ===== init ตอนหน้าโหลด: hook EA101 ที่มีอยู่ให้ mount ถูกต้อง =====
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.output-row select.output-type').forEach(selectEl => {
                handleTypeChange(selectEl);
            });
        });








        let coResearchIndex = {{ count($project->coResearches ?? []) }};

        function addCoResearchRow() {
            const container = document.getElementById('coresearch-container');
            const id = `coresearch-${coResearchIndex}`;
            const listId = `coresearchList-${coResearchIndex}`;
            const labelNum = coResearchIndex + 1;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="coresearch-row space-y-2 py-4">
            <label class="block w-full">
                <span class="block text-sm text-gray-700 mb-1">
                    ชื่อ Co-Researcher คนที่ <span class="coresearch-index">${labelNum}</span>
                </span>
                <input name="coresearch[${coResearchIndex}][name]" list="${listId}" type="text"
                    class="w-full border p-2 rounded" placeholder="ยศ ชื่อ-นามสกุล">
                <datalist id="${listId}">
                    ${window.coresearchOptionsHtml}
                </datalist>
            </label>

            <div class="grid grid-cols-12 gap-4 items-end">
                <label class="col-span-5">
                    <span class="block text-sm text-gray-700 mb-1">สังกัด</span>
                    <input name="coresearch[${coResearchIndex}][affiliation]" type="text"
                        class="w-full border p-2 rounded" placeholder="ป สังกัด">
                </label>
                <label class="col-span-5">
                    <span class="block text-sm text-gray-700 mb-1">คนภายนอก/ภายใน</span>
                    <select name="coresearch[${coResearchIndex}][unit_status]" class="w-full border p-2 rounded" required>
                        <option value="" disabled selected>กรุณาเลือก</option>
                        <option value="ภายในคณะแพทยศาสตร์ศิริราชพยาบาล">ภายในคณะแพทยศาสตร์ศิริราชพยาบาล</option>
                        <option value="ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล">ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล</option>
                        <option value="ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ">ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ</option>
                    </select>
                </label>
                <div class="col-span-2 flex justify-end pt-6">
                    <button type="button" onclick="removeRow('${id}', 'coresearch')" class="text-red-600 text-xl">✕</button>
                </div>
            </div>
        </div>
    `);

            coResearchIndex++;
            reindexCoResearch();
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
            flatpickr(".thai-datepicker", {
                locale: "th",
                dateFormat: "d-m-Y", // สำหรับส่งไป controller
                altInput: true,
                altFormat: "j F Y", // <-- เราจะเปลี่ยนตรงนี้ด้านล่าง
                prevArrow: "", // 🔒 ซ่อนปุ่มย้อนเดือน
                nextArrow: "", // 🔒 ซ่อนปุ่มถัดไปเดือน
                onReady: toBuddhist,
                onChange: toBuddhist,
                onMonthChange: toBuddhist,
                onYearChange: toBuddhist,
            });

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

                const years = instance.calendarContainer.querySelectorAll(
                    '.flatpickr-current-month input.cur-year');
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
        });
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
            flatpickr(".thai-datepicker", {
                locale: "th",
                dateFormat: "d-m-Y",
                altInput: true,
                altFormat: "j F Y",
                prevArrow: "", // 🔒 ซ่อนปุ่มย้อนเดือน
                nextArrow: "", // 🔒 ซ่อนปุ่มถัดไปเดือน
                onReady: toBuddhist,
                onChange: toBuddhist,
                onMonthChange: toBuddhist,
                onYearChange: toBuddhist,
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

        document.addEventListener('DOMContentLoaded', initThaiDatepickers);



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

@endsection

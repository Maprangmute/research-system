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


@section('content')
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

    <div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
        <h2 class="text-xl font-bold mb-6 text-gray-800">📋 เพิ่มข้อมูลโครงการวิจัย</h2>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @php
            $allUsers = \App\Models\User::orderBy('name')->get();
        @endphp

        <form action="{{ route('projects.store') }}" method="POST">
            @csrf

            <hr class="my-6 border-t-2 border-gray-300">
            <!-- A: Project Overview -->
            <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ชื่อโครงการ (ภาษาไทย)*</span>
                    <input name="project_name_th" type="text" required class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ชื่อโครงการ (English)</span>
                    <input name="project_name_en" type="text" class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ประเภทของโครงการ*</span>
                    <select name="project_type" id="project_type" class="w-full border p-2 rounded" required>
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
                    <span class="block text-sm text-gray-700 mb-1">กลุ่มเนื้อหา (Theme)*</span>
                    <select name="theme" required class="w-full border p-2 rounded">
                        <option value="" disabled selected>กรุณาเลือกกลุ่มเนื้อหา</option>
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
                    <span class="block text-sm text-gray-700 mb-1">ลักษณะโครงการ</span>
                    <select name="project_list" class="w-full border p-2 rounded">
                        <option value="">-- เลือกประเภทลักษณะ --</option>
                        <option value="โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภา">โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                        <option value="โครงการนอกหน่วย แต่ยังอยู่ในคณะฯ">โครงการนอกหน่วย แต่ยังอยู่ในคณะฯ</option>
                        <option value="โครงการวิจัยนอกคณะ">โครงการวิจัยนอกคณะ</option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ขนาดโครงการ (S/M/L)</span>
                    {{-- <select name="size" required class="w-full border p-2 rounded"> --}}
                    <select name="size"  class="w-full border p-2 rounded">    
                        <option value="" disabled selected>กรุณาเลือกขนาดของโปรเจค</option>
                        <option value="S">S (ภายนอก / ภายใน / Cohort Study)</option>
                        <option value="M">M (ภายนอก / ภายใน / Cohort Study)</option>
                        <option value="L">L (ภายนอก / ภายใน / Cohort Study)</option>
                        <option value="XL">XL (ภายนอก) </option>
                        <option value="FTE Hours">FTE คิดแบบรายชั่วโมง </option>
                    </select>
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1"> รหัสโครงการ R</span>
                    <input name="r_code" type="text" placeholder="R:001" class="w-full border p-2 rounded">
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





                <!-- Funding Source Section -->
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA102 - ประเภทแหล่งทุน</span>
                    <select name="funding_type" required class="w-full border p-2 rounded">
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
                    <select name="funding_org_type" required class="w-full border p-2 rounded">
                        <option value="" disabled selected>กรุณาเลือกประเภทองค์กร</option>
                        <option value="รัฐบาล">รัฐบาล</option>
                        <option value="เอกชน">เอกชน</option>
                        <option value="มูลนิธิ">มูลนิธิ</option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA105 - ชื่อทุน</span>
                    <input name="funding_name" type="text" class="w-full border p-2 rounded"
                        placeholder="ชื่อทุนที่ได้รับ">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA106 - งบประมาณ (บาท)</span>
                    <input id="budgetInput" name="budget" type="text" class="w-full border p-2 rounded"
                        placeholder="ระบุจำนวนเงินที่ได้รับ" inputmode="decimal" pattern="[\d,\.]*">

                </label>

            </div>

            <!-- B: ระยะเวลาดำเนินโครงการ -->
            <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">B: ระยะเวลาดำเนินโครงการ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

                <label class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">วันที่โครงการเริ่มทำงานของทีมวิจัย</span>
                    <input name="team_start_date" type="text"
                        class="thai-datepicker w-full border p-2 rounded pl-10 @error('start_date') border-red-500 @enderror"
                        value="{{ old('team_start_date') }}" placeholder="กรุณาเลือกวันที่เริ่มงานของทีม">
                    <!-- ปุ่มไอคอนปฏิทิน (absolute ซ้ายใน input) -->
                    <svg class="absolute left-3 top-9 w-5 h-5 text-gray-500 pointer-events-none" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </label>

                <label class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">วันที่สิ้นสุดการทำงานของทีมวิจัย</span>
                    <input name="team_end_date" type="text" class="thai-datepicker w-full border p-2 rounded pl-10"
                        value="{{ old('team_end_date') }}" placeholder="กรุณาเลือกวันที่สิ้นสุดการทำงานของทีม">
                    <!-- ปฏิทินไอคอน -->
                    <svg class="absolute left-3 top-9 w-5 h-5 text-gray-500 pointer-events-none" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </label>


                <label class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">BA001 - วันที่เริ่มเขียน Proposal</span>
                    <input name="proposal_date" type="text"
                        class="thai-datepicker w-full border p-2 rounded pl-10 @error('proposal_date') border-red-500 @enderror"
                        value="{{ old('proposal_date') }}" placeholder="กรุณาเลือกวันที่เริ่มเขียน Proposal">
                    <!-- ไอคอนปฏิทิน -->
                    <svg class="absolute left-3 top-9 w-5 h-5 text-gray-500 pointer-events-none" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </label>


                <label class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">BC101 - วันที่สัญญาอนุมัติ</span>
                    <input name="contract_date" type="text"
                        class="thai-datepicker w-full border p-2 rounded pl-10 @error('contract_date') border-red-500 @enderror"
                        value="{{ old('contract_date') }}" placeholder="กรุณาเลือกวันที่อนุมัติสัญญา">
                    <svg class="absolute left-3 top-9 w-5 h-5 text-gray-500 pointer-events-none" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    @error('contract_date')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">BC102 - วันที่โครงการเริ่ม (ตามสัญญา)</span>
                    <input name="start_date" type="text"
                        class="thai-datepicker w-full border p-2 rounded pl-10 @error('start_date') border-red-500 @enderror"
                        value="{{ old('start_date') }}" placeholder="กรุณาเลือกวันที่เริ่มตามสัญญา">
                    <svg class="absolute left-3 top-9 w-5 h-5 text-gray-500 pointer-events-none" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    @error('start_date')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="w-full relative ">
                    <span class="block text-sm text-gray-700 mb-1">วันที่สิ้นสุด (ตามสัญญา)</span>
                    <input name="end_date" type="text" class="thai-datepicker w-full border p-2 rounded pl-10"
                        value="{{ old('end_date') }}" placeholder="กรุณาเลือกวันที่สิ้นสุดตามสัญญา">
                    <svg class="absolute left-3 top-9 w-5 h-5 text-gray-500 pointer-events-none" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </label>

                <label class="w-full">
                    {{-- BA000 - Project Planning (Status) --}}
                    <span class="block text-sm text-gray-700 mb-1">Project Status</span>
                    <select name="planning_status" id="planning_status" class="w-full border p-2 rounded font-semibold"
                        onchange="updateStatusColor(this)" required>
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
                </label>




            </div>




            <hr class="my-6 border-t-2 border-gray-300">
            <!-- รหัส IRB -->
            <h3 class="font-semibold text-lg text-blue-700 mb-2">รหัส IRB</h3>
            <div id="irb-code-container" class="space-y-2 mb-4">
                <div id="irb-0" class="irb-row grid grid-cols-3 gap-2 items-center">
                    <div class="w-full">
                        <span class="block text-sm text-gray-700 mb-1">รหัส IRB ที่ <span
                                class="irb-index">1</span></span>
                        <input name="irbs[0][code]" type="text" placeholder="EC2025"
                            class="w-full border p-2 rounded">
                    </div>
                    <div class="w-full">
                        <span class="block text-sm text-gray-700 mb-1">จาก (ชื่อหน่วยงาน)</span>
                        <input name="irbs[0][source]" type="text" placeholder="ชื่อหน่วยงาน"
                            class="w-full border p-2 rounded">
                    </div>
                    <div class="w-full flex gap-2 items-end">
                        <div class="w-full relative">
                            <span class="block text-sm text-gray-700 mb-1">วันที่หมดอายุ</span>
                            <input name="irbs[0][exp_date]" type="text" placeholder="เลือกวันที่"
                                class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="โปรดเลือกวันที่">

                            <!-- ไอคอนปฏิทิน -->
                            <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <button type="button" onclick="removeRow('irb-0', 'irb')" class="text-red-600 mb-2">✕</button>
                    </div>
                </div>
            </div>
            <button type="button" onclick="addIRBCode()" class="mb-6 px-3 py-1 bg-red-600 text-white rounded">+
                เพิ่มรหัส IRB</button>

            @php
                $allUsers = \App\Models\User::orderBy('name')->get();
            @endphp


            <hr class="my-6 border-t-2 border-gray-300">
            <!-- C: Roles and Responsibilities -->
            <h3 class="font-semibold text-lg text-blue-700 mb-2">C: Roles and Responsibilities</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <label class="block w-full">
                    <span class="block text-sm font-medium text-gray-700 mb-1">
                        CA101 - ชื่อ PI (ผู้วิจัยหลัก)
                    </span>
                    <div class="relative">
                        <input name="pi_name" list="userNames" type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                            placeholder="คำนำหน้า ชื่อ-นามสกุล" required>
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
                    <input name="pi_affiliation" type="text" class="w-full border p-2 rounded" value=""
                        placeholder="สังกัดผู้วิจัยหลัก">
                    @error('pi_affiliation')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </label>
            </div>








            <h3 class="text-balance text-blue-700 mb-2">รายชื่อผู้ร่วมวิจัย (COPI)</h3>
            <div id="copi-container" class="space-y-2 mb-4">
                <div id="copi-0" class="copi-row grid grid-cols-2 gap-4 items-center">
                    <label class="w-full">
                        <span class="block text-sm text-gray-700 mb-1">
                            ชื่อ COPI คนที่ <span class="copi-index">1</span>
                        </span>
                        <div class="relative">
                            <input name="copis[0][name]" list="userNames" type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                placeholder="คำนำหน้า ชื่อ-นามสกุล">
                            <datalist id="userNames">
                                @foreach ($allUsers as $user)
                                    @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
                                    <option value="{{ $fullName }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                    </label>

                    <div class="flex gap-2 w-full">
                        <label class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">สังกัด COPI คนที่ <span
                                    class="copi-index">1</span></span>
                            <input name="copis[0][affiliation]" type="text" class="w-full border p-2 rounded"
                                placeholder="สังกัด COPI">
                        </label>
                        <button type="button" onclick="removeRow('copi-0', 'copi')" class="text-red-600 mt-6">✕</button>
                    </div>
                </div>
            </div>
            <button type="button" onclick="addCopiRow()" class="mb-6 px-3 py-1 bg-yellow-600 text-white rounded">+ เพิ่ม
                COPI</button>
            <h3 class="text-blue-700 font-semibold mb-2">Co-Researcher</h3>
            <div id="coresearch-container" class="space-y-4 mb-4">
                <div id="coresearch-0" class="coresearch-row space-y-2">
                    <!-- บรรทัดที่ 1: ชื่อ -->
                    <label class="block w-full">
                        <span class="block text-sm mb-1">
                            ชื่อ Co-Researcher ที่ <span class="coresearch-index">1</span>
                        </span>
                        <input name="coresearch[0][name]" list="coresearchList0" type="text"
                            class="w-full border p-2 rounded" placeholder="ยศ ชื่อ นามสกุล">

                        <datalist id="coresearchList0">
                            @foreach ($allUsers as $user)
                                @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
                                <option value="{{ $fullName }}"></option>
                            @endforeach
                        </datalist>
                    </label>


                    <!-- บรรทัดที่ 2: สังกัด + ภายใน/ภายนอก + ลบ -->
                    <div class="grid grid-cols-12 gap-4 items-end">
                        <label class="col-span-5">
                            <span class="block text-sm mb-1">สังกัด</span>
                            <input name="coresearch[0][affiliation]" type="text" class="w-full border p-2 rounded"
                                placeholder="สังกัด">
                        </label>

                        <label class="col-span-5">
                            <span class="block text-sm mb-1">คนภายนอก/ภายใน</span>
                            <select name="coresearch[0][unit_status]" required class="w-full border p-2 rounded">
                                <option value="" disabled selected>กรุณาเลือก</option>
                                <option value="ภายในคณะแพทยศาสตร์ศิริราชพยาบาล">ภายในคณะแพทยศาสตร์ศิริราชพยาบาล</option>
                                <option value="ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล">ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล</option>
                                <option value="ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ">
                                    ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ</option>
                            </select>
                        </label>

                        <div class="col-span-2 flex items-center justify-end pt-6">
                            <button type="button" onclick="removeRow('coresearch-0', 'coresearch')"
                                class="text-red-600 text-xl">✕</button>
                        </div>
                    </div>
                </div>
            </div>


            <button type="button" onclick="addCoResearchRow()" class="mb-6 px-3 py-1 bg-purple-600 text-white rounded">+
                เพิ่ม Co-Researcher</button>




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
                <div id="fte-container" class="space-y-6 mb-6">
                    <div id="fte-0" class="fte-row p-4 border rounded-lg bg-white shadow-sm">
                        <!-- โครงร่างหลัก: ซ้ายรายละเอียด / ขวา FTE -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

                            <!-- ซ้าย: รายละเอียด -->
                            <div class="md:col-span-9 space-y-4">
                                <!-- ชื่อ-นามสกุล + กลุ่ม -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <label class="w-full">
                                        <span class="block text-sm font-semibold text-gray-700 mb-1">1. ชื่อ-นามสกุล</span>
                                        <select name="fte_roles[0][name]"
                                            class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" required>
                                            <option value="" disabled selected>เลือกผู้รับผิดชอบ</option>
                                            @foreach ($allUsers as $user)
                                                @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
                                                <option value="{{ $fullName }}">{{ $fullName }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="w-full">
                                        <span class="block text-sm font-semibold text-gray-700 mb-1">กลุ่ม</span>
                                        <select name="fte_roles[0][role_group]"
                                            class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                                            <option value="" disabled selected>เลือกกลุ่ม</option>
                                            @foreach ($roleGroups as $group)
                                                <option value="{{ $group }}">{{ $group }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>

                                <!-- ภาระงาน + วันที่ -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <label class="w-full">
                                        <span class="block text-sm font-semibold text-gray-700 mb-1">ภาระงาน</span>
                                        <select name="fte_roles[0][job_detail]"
                                            class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
                                            <option value="" disabled selected>เลือกภาระงาน</option>
                                            @foreach ($jobDetails as $job)
                                                <option value="{{ $job }}">{{ $job }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="w-full relative">
                                        <span
                                            class="block text-sm font-semibold text-gray-700 mb-1">วันเริ่มต้นภาระงาน</span>
                                        <input name="fte_roles[0][start_date]" type="text" placeholder="โปรดใส่วันที่"
                                            class="thai-datepicker w-full border p-2 rounded pl-10 focus:ring-2 focus:ring-blue-500"
                                            required>
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
                                        <input name="fte_roles[0][end_date]" type="text" placeholder="โปรดใส่วันที่"
                                            class="thai-datepicker w-full border p-2 rounded pl-10 focus:ring-2 focus:ring-blue-500"
                                            required>
                                        <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2 2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </label>
                                </div>

                                <!-- รายละเอียดภารกิจ -->
                                <label class="w-full">
                                    <span class="block text-sm font-semibold text-gray-700 mb-1">รายละเอียดภารกิจ</span>
                                    <select name="fte_roles[0][task_detail]"
                                        class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" required>
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

                            <!-- ขวา: FTE (สองกล่องซ้อน) -->
                            <div class="md:col-span-3">
                                <div class="space-y-4">
                                    <!-- FTE ผู้วิจัย -->
                                    <label class="block p-4 border rounded-xl bg-blue-50">
                                        <span class="block text-sm font-bold text-blue-800 mb-2">FTE (ผู้วิจัย)</span>
                                        <input name="fte_roles[0][fte_value]" type="number" step="0.01"
                                            min="0" max="1"
                                            class="w-full border p-3 rounded font-semibold text-right focus:ring-2 focus:ring-blue-500"
                                            placeholder="0.00" required>
                                    </label>

                                    <!-- FTE ผู้บริหาร -->
                                    <label class="block p-4 border rounded-xl bg-emerald-50">
                                        <span class="block text-sm font-bold text-emerald-800 mb-2">FTE (ผู้บริหาร)</span>
                                        <input name="fte_roles[0][fte_value_research]" type="number" step="0.01"
                                            min="0" max="1"
                                            class="w-full border p-3 rounded font-semibold text-right focus:ring-2 focus:ring-emerald-500"
                                            placeholder="0.00">
                                    </label>

                                    <!-- ปุ่มลบแถว -->
                                    <button type="button" onclick="removeRow('fte-0','fte')"
                                        class="w-full py-2 border rounded-lg text-red-600 hover:bg-red-50">
                                        ✕ ลบผู้มีส่วนร่วมนี้
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="addFTERow()"
                    class="mb-6 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow">
                    + เพิ่มผู้มีส่วนร่วม
                </button>





                <hr class="my-6 border-t-2 border-gray-300">
                <!-- E: ผลงานวิจัย (Output) -->
                <h3 class="font-semibold text-lg text-blue-700 mb-2">E: ผลงานวิจัย (Output)</h3>
                <div id="output-container" class="space-y-4 mb-4">
                    <div id="output-0" class="output-row space-y-2">
                        <!-- บรรทัดแรก: ชื่อผลงาน -->
                        <label class="block w-full">
                            <span class="block text-sm text-gray-700 mb-1">ชื่อผลงานที่ <span
                                    class="output-index">1</span></span>
                            <input name="outputs[0][title]" type="text" class="w-full border p-2 rounded"
                                placeholder="ชื่อผลงาน">
                        </label>

                        <!-- บรรทัดที่สอง: ประเภท, หมายเหตุ, ปี -->
                        <div class="grid grid-cols-3 gap-4 items-end">
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">ประเภทของผลงาน</span>
                                <select name="outputs[0][type]" class="w-full border p-2 rounded" required>
                                    <option value="" disabled selected>เลือกประเภทของผลงาน</option>
                                    <option>EA101 Research Paper or Journal Article</option>
                                    <option>EA102 Reports and White papers</option>
                                    <option>EA103 Presentations in Academic Conferences</option>
                                    <option>EA104 Data Sets and Databases</option>
                                    <option>EA105 Inventions or Innovations (Application /Website)</option>
                                    <option>EA106 Policy Recommendations</option>
                                    <option>EA107 Educational Materials</option>
                                    <option>EA108 Communication Materials (Creations)</option>
                                    <option>EA109 Change in Practices ,Procedures, Policy</option>
                                    <option>EA110 Number of participants (persons)</option>
                                    <option>EA111 Satisfaction Rating (1-5)</option>
                                    <option>EA112 Present to Committee Meeting / Policy Maker / Board</option>
                                    <option>EA113 คำสั่งแต่งตั้ง Committees เน้นที่ SIHP เป็นกรรมการและหรือเลขา</option>
                                    <option>EA114 Events / Forum</option>
                                    <option>EA115 Capacity Building</option>
                                    <option>EA116 Research Project</option>
                                    <option>EA117 MOU</option>
                                </select>
                            </label>
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">รายละเอียด / หมายเหตุ / LINK</span>
                                <input name="outputs[0][project_output]" type="text" class="w-full border p-2 rounded"
                                    placeholder="เพิ่มเติม">
                            </label>
                            <div class="flex gap-2 w-full">

                                <label class="w-full relative">
                                    <span class="block text-sm text-gray-700 mb-1">วันที่ publish</span>
                                    <input name="outputs[0][year]" type="text"
                                        class="thai-datepicker w-full border p-2 rounded pl-10"
                                        placeholder="โปรดใส่วันที่" required>
                                    <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </label>
                                {{-- <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">ปี</span>
                                <input name="outputs[0][year]" type="text" class="w-full border p-2 rounded"
                                    placeholder="2025">
                            </label> --}}
                                <button type="button" onclick="removeRow('output-0', 'output')"
                                    class="text-red-600 mb-2">✕</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addOutputRow()" class="mb-6 px-3 py-1 bg-green-600 text-white rounded">+
                    เพิ่ม Output</button>

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
                <div class="mt-6">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">💾
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

    <script>
        // ✅ TO BUDDHIST
        function toBuddhist(selectedDates, dateStr, instance) {
            const altInput = instance.altInput;

            if (selectedDates.length > 0 && altInput) {
                const date = selectedDates[0];
                const day = date.getDate();
                const month = date.toLocaleString('th-TH', {
                    month: 'long'
                });
                const year = date.getFullYear() + 543;
                altInput.value = `${day} ${month} ${year}`;
            }

            setTimeout(() => {
                const yearInputs = instance?.calendarContainer?.querySelectorAll(
                    '.flatpickr-current-month input.cur-year') || [];
                yearInputs.forEach(el => {
                    let y = parseInt(el.value);
                    if (y < 2500) el.value = y + 543;

                    el.addEventListener('blur', () => {
                        let y2 = parseInt(el.value);
                        if (y2 > 2400) {
                            el.value = y2 - 543;
                            instance.setDate(new Date(y2 - 543, instance.currentMonth, 1));
                            setTimeout(() => toBuddhist(selectedDates, dateStr, instance), 5);
                        }
                    });
                });
            }, 5);
        }

        function initThaiDatepickers() {
            flatpickr(".thai-datepicker", {
                locale: "th",
                dateFormat: "d-m-Y",
                altInput: true,
                altFormat: "j F Y",
                prevArrow: "",
                nextArrow: "",
                onReady: toBuddhist,
                onClose: toBuddhist,
                onChange: toBuddhist,
                onMonthChange: toBuddhist,
                onYearChange: toBuddhist,
                disableMobile: true
            });
        }

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

        function reindexIRB() {
            document.querySelectorAll('#irb-code-container .irb-row').forEach((row, i) => {
                row.querySelector('.irb-index').textContent = i + 1;
            });
        }

        function reindexFTE() {
            const rows = document.querySelectorAll('.fte-row');
            rows.forEach((row, index) => {
                const label = row.querySelector('.fte-index');
                if (label !== null) {
                    label.textContent = `${index + 1}. ชื่อ-นามสกุล`;
                }
            });
        }

        function reindexOutput() {
            document.querySelectorAll('#output-container .output-row').forEach((row, i) => {
                row.querySelector('.output-index').textContent = i + 1;
            });
        }

        function reindexCopi() {
            document.querySelectorAll('#copi-container .copi-row').forEach((row, i) => {
                row.querySelectorAll('.copi-index').forEach(span => span.textContent = i + 1);
            });
        }

        function reindexCoResearch() {
            document.querySelectorAll('#coresearch-container .coresearch-row').forEach((row, i) => {
                row.querySelector('.coresearch-index').textContent = i + 1;
            });
        }

        // ✅ ADD ROWS
        let irbIndex = 1;

        function addIRBCode() {
            const id = `irb-${irbIndex}`;
            document.getElementById('irb-code-container').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="irb-row grid grid-cols-3 gap-2 items-center">
            <div class="w-full">
                <span class="block text-sm text-gray-700 mb-1">รหัส IRB ที่ <span class="irb-index">${irbIndex + 1}</span></span>
                <input name="irbs[${irbIndex}][code]" type="text" class="w-full border p-2 rounded" placeholder="EC2025">
            </div>
            <div class="w-full">
                <span class="block text-sm text-gray-700 mb-1">จาก (ชื่อหน่วยงาน)</span>
                <input name="irbs[${irbIndex}][source]" type="text" class="w-full border p-2 rounded" placeholder="ชื่อหน่วยงาน">
            </div>
            <div class="w-full flex gap-2 items-end">
                <div class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">วันที่หมดอายุ</span>
                    <input name="irbs[${irbIndex}][exp_date]" type="text" class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="เลือกวันที่">
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
            irbIndex++;
            reindexIRB();
            initThaiDatepickers(); // สำหรับรีเปิด datepicker ใหม่ที่เพิ่มมา
        }

        let fteIndex = 1;

        const userOptions = @json(
            \App\Models\User::orderBy('name')->get()->map(function ($u) {
                    return [
                        'fullName' => trim($u->prefix . ' ' . $u->name),
                    ];
                }));

        const roleGroups = ['Scientific', 'Management', 'IT + Communication', 'Logistics + Admin'];
        const jobDetails = [
            'Researcher',
            'Researcher Assistant',
            'Project Manager',
            'Research Coordinator',
            'นักวิชาการสารสนเทศ',
            'นักประชาสัมพันธ์',
            'เจ้าหน้าที่บริหารงานทั่วไป',
        ];

        function addFTERow() {
            const id = `fte-${fteIndex}`;

            const userDropdown = userOptions.map(u =>
                `<option value="${u.fullName}">${u.fullName}</option>`
            ).join('');

            const groupDropdown = roleGroups.map(g =>
                `<option value="${g}">${g}</option>`
            ).join('');

            const jobDropdown = jobDetails.map(j =>
                `<option value="${j}">${j}</option>`
            ).join('');

            document.getElementById('fte-container').insertAdjacentHTML('beforeend', `
  <div id="${id}" class="fte-row p-4 border rounded-lg bg-white shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
      
      <!-- ซ้าย: รายละเอียด -->
      <div class="md:col-span-9 space-y-4">
        <!-- ชื่อ-นามสกุล + กลุ่ม -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <label class="w-full">
            <span class="fte-index block text-sm font-semibold text-gray-700 mb-1">${fteIndex + 1}. ชื่อ-นามสกุล</span>
            <select name="fte_roles[${fteIndex}][name]" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" required>
              <option value="" disabled selected>เลือกผู้รับผิดชอบ</option>
              ${userDropdown}
            </select>
          </label>
          <label class="w-full">
            <span class="block text-sm font-semibold text-gray-700 mb-1">กลุ่ม</span>
            <select name="fte_roles[${fteIndex}][role_group]" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
              <option value="" disabled selected>โปรดเลือกกลุ่ม</option>
              ${groupDropdown}
            </select>
          </label>
        </div>

        <!-- ภาระงาน + วันที่ -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <label class="w-full">
            <span class="block text-sm font-semibold text-gray-700 mb-1">ภาระงาน</span>
            <select name="fte_roles[${fteIndex}][job_detail]" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
              <option value="" disabled selected>เลือกภาระงาน</option>
              ${jobDropdown}
            </select>
          </label>
          <label class="w-full relative">
            <span class="block text-sm font-semibold text-gray-700 mb-1">วันเริ่ม</span>
            <input name="fte_roles[${fteIndex}][start_date]" type="text" class="thai-datepicker w-full border p-2 rounded pl-10 focus:ring-2 focus:ring-blue-500" placeholder="โปรดใส่วันที่" required>
            <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
              xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </label>
          <label class="w-full relative">
            <span class="block text-sm font-semibold text-gray-700 mb-1">วันสิ้นสุด</span>
            <input name="fte_roles[${fteIndex}][end_date]" type="text" class="thai-datepicker w-full border p-2 rounded pl-10 focus:ring-2 focus:ring-blue-500" placeholder="โปรดใส่วันที่" required>
            <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
              xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </label>
        </div>

        <!-- รายละเอียดภารกิจ -->
        <label class="w-full">
          <span class="block text-sm font-semibold text-gray-700 mb-1">รายละเอียดภารกิจ</span>
          <select name="fte_roles[${fteIndex}][task_detail]" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500" required>
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
        <!-- FTE ผู้วิจัย -->
        <label class="block p-4 border rounded-xl bg-blue-50">
          <span class="block text-sm font-bold text-blue-800 mb-2">FTE (ผู้วิจัย)</span>
          <input name="fte_roles[${fteIndex}][fte_value]" type="number" step="0.01" min="0" max="1"
            class="w-full border p-3 rounded font-semibold text-right focus:ring-2 focus:ring-blue-500"
            placeholder="0.00" required>
        </label>

        <!-- FTE ผู้บริหาร -->
        <label class="block p-4 border rounded-xl bg-emerald-50">
          <span class="block text-sm font-bold text-emerald-800 mb-2">FTE (ผู้บริหาร)</span>
          <input name="fte_roles[${fteIndex}][fte_value_research]" type="number" step="0.01" min="0" max="1"
            class="w-full border p-3 rounded font-semibold text-right focus:ring-2 focus:ring-emerald-500"
            placeholder="0.00">
        </label>

        <!-- ปุ่มลบ -->
        <button type="button" onclick="removeRow('${id}', 'fte')"
          class="w-full py-2 border rounded-lg text-red-600 hover:bg-red-50">
          ✕ ลบผู้มีส่วนร่วมนี้
        </button>
      </div>
    </div>
  </div>
`);


            fteIndex++;
            reindexFTE();
            initThaiDatepickers();
        }

        let outputIndex = 1;

        function addOutputRow() {
            const id = `output-${outputIndex}`;
            document.getElementById('output-container').insertAdjacentHTML('beforeend', `
            <div id="${id}" class="output-row space-y-2">
                <label class="block w-full">
                    <span class="block text-sm text-gray-700 mb-1">ชื่อผลงานที่ <span class="output-index">${outputIndex + 1}</span></span>
                    <input name="outputs[${outputIndex}][title]" type="text" class="w-full border p-2 rounded">
                </label>
                <div class="grid grid-cols-3 gap-4 items-end">
                    <label class="w-full">
                        <span class="block text-sm text-gray-700 mb-1">ประเภทของผลงาน</span>
                            <select name="outputs[${outputIndex}][type]" class="w-full border p-2 rounded" required>
                                <option value="" disabled selected>เลือกประเภทของผลงาน</option>
                                <option>EA101 Research Paper or Journal Article</option>
                                <option>EA102 Reports and White papers</option>
                                <option>EA103 Presentations in Academic Conferences</option>
                                <option>EA104 Data Sets and Databases</option>
                                <option>EA105 Inventions or Innovations (Application /Website)</option>
                                <option>EA106 Policy Recommendations</option>
                                <option>EA107 Educational Materials</option>
                                <option>EA108 Communication Materials (Creations)</option>
                                <option>EA109 Change in Practices ,Procedures, Policy</option>
                                <option>EA110 Number of participants (persons)</option>
                                <option>EA111 Satisfaction Rating (1-5)</option>
                                <option>EA112 Present to Committee Meeting / Policy Maker / Board</option>
                                <option>EA113 คำสั่งแต่งตั้ง Committees เน้นที่ SIHP เป็นกรรมการและหรือเลขา</option>
                                <option>EA114 Events / Forum</option>
                                <option>EA115 Capacity Building</option>
                                <option>EA116 Research Project</option>
                                <option>EA117 MOU</option>
                            </select>
                    </label>
                    <label class="w-full">
                        <span class="block text-sm text-gray-700 mb-1">รายละเอียด / หมายเหตุ / LINK</span>
                        <input name="outputs[${outputIndex}][output]" type="text" class="w-full border p-2 rounded">
                    </label>
                    <div class="flex gap-2 w-full">
                               <div class="flex gap-2 w-full">

                            <label class="w-full relative">
                            <span class="block text-sm text-gray-700 mb-1">วันที่ publish</span>
                            <input name="outputs[${outputIndex}][year]" type="text"
                                class="thai-datepicker w-full border p-2 rounded pl-10" placeholder="โปรดใส่วันที่"
                                required>
                            <svg class="w-5 h-5 absolute top-9 left-3 text-gray-500 pointer-events-none"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </label>
                        
                        <button type="button" onclick="removeRow('${id}', 'output')" class="text-red-600 mb-2">✕</button>
                    </div>
                </div>
            </div>
        `);
            outputIndex++;
            reindexOutput();
            initThaiDatepickers(); // ✅ Apply flatpickr to new row
        }

        let copiIndex = 1;

        function addCopiRow() {
            const id = `copi-${copiIndex}`;
            const listId = `copiList-${copiIndex}`;

            // Blade จะ render datalistOptions เป็น <option> ทั้งหมด
            const datalistOptions = `@foreach ($allUsers as $user)
        @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
        <option value="{{ $fullName }}"></option>
    @endforeach`.replace(/\n\s*/g, ''); // clean newlines for JS string

            document.getElementById('copi-container').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="copi-row grid grid-cols-2 gap-4 items-center">
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">ชื่อ COPI คนที่ <span class="copi-index">${copiIndex + 1}</span></span>
                <input name="copis[${copiIndex}][name]" list="${listId}" type="text" 
                    class="w-full border p-2 rounded" placeholder="คำนำหน้า ชื่อ-นามสกุล">
                <datalist id="${listId}">
                    ${datalistOptions}
                </datalist>
            </label>
            <div class="flex gap-2 w-full">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">สังกัด COPI คนที่ <span class="copi-index">${copiIndex + 1}</span></span>
                    <input name="copis[${copiIndex}][affiliation]" type="text" class="w-full border p-2 rounded" placeholder="สังกัด COPI">
                </label>
                <button type="button" onclick="removeRow('${id}', 'copi')" class="text-red-600 mt-6">✕</button>
            </div>
        </div>
    `);

            copiIndex++;
            reindexCopi();
        }

        let coResearchIndex = 1;

        function addCoResearchRow() {
            const id = `coresearch-${coResearchIndex}`;
            const listId = `coresearchList-${coResearchIndex}`;

            const datalistOptions = `@foreach ($allUsers as $user)
        @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
        <option value="{{ $fullName }}"></option>
    @endforeach`.replace(/\n\s*/g, '');

            document.getElementById('coresearch-container').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="coresearch-row space-y-2 py-4">
            <label class="block w-full">
                <span class="block text-sm mb-1">ชื่อ Co-Researcher ที่ <span class="coresearch-index">${coResearchIndex + 1}</span></span>
                <input name="coresearch[${coResearchIndex}][name]" list="${listId}" type="text" class="w-full border p-2 rounded" placeholder="ยศ ชื่อ นามสกุล">
                <datalist id="${listId}">
                    ${datalistOptions}
                </datalist>
            </label>
            <div class="grid grid-cols-12 gap-4 items-end">
                <label class="col-span-5">
                    <span class="block text-sm mb-1">สังกัด</span>
                    <input name="coresearch[${coResearchIndex}][affiliation]" type="text" class="w-full border p-2 rounded" placeholder="สังกัด">
                </label>
                <label class="col-span-5">
                    <span class="block text-sm mb-1">คนภายนอก/ภายใน</span>
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


        // ✅ INITIALIZE
        document.addEventListener('DOMContentLoaded', () => {
            initThaiDatepickers();
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

    <script>
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
    </script>
    <script>
        new TomSelect('#pi_name', {
            create: true,
            maxItems: 1,
            persist: false,
            plugins: ['remove_button']
        });
    </script>
@endsection

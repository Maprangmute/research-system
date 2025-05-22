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
                    <span class="block text-sm text-gray-700 mb-1">ขนาดโครงการ (S/M/L)</span>
                    <select name="size" required class="w-full border p-2 rounded">
                        <option value="" disabled selected>กรุณาเลือกขนาดของโปรเจค</option>
                        <option value="S">S (ภายนอก / ภายใน / Cohort Study)</option>
                        <option value="M">M (ภายนอก / ภายใน / Cohort Study)</option>
                        <option value="L">L (ภายนอก / ภายใน / Cohort Study)</option>
                        <option value="XL">XL (ภายนอก) </option>
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
                    <input
                      name="proposal_date"
                      type="text"
                      class="thai-datepicker w-full border p-2 rounded pl-10 @error('proposal_date') border-red-500 @enderror"
                      value="{{ old('proposal_date') }}"
                      placeholder="กรุณาเลือกวันที่เริ่มเขียน Proposal"
                    >
                    <!-- ไอคอนปฏิทิน -->
                    <svg class="absolute left-3 top-9 w-5 h-5 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                  </label>
                  

                  <label class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">BC101 - วันที่สัญญาอนุมัติ</span>
                    <input
                      name="contract_date"
                      type="text"
                      class="thai-datepicker w-full border p-2 rounded pl-10 @error('contract_date') border-red-500 @enderror"
                      value="{{ old('contract_date') }}"
                      placeholder="กรุณาเลือกวันที่อนุมัติสัญญา"
                    >
                    <svg class="absolute left-3 top-9 w-5 h-5 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    @error('contract_date')
                      <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                  </label>
                  
                  <label class="w-full relative">
                    <span class="block text-sm text-gray-700 mb-1">BC102 - วันที่โครงการเริ่ม (ตามสัญญา)</span>
                    <input
                      name="start_date"
                      type="text"
                      class="thai-datepicker w-full border p-2 rounded pl-10 @error('start_date') border-red-500 @enderror"
                      value="{{ old('start_date') }}"
                      placeholder="กรุณาเลือกวันที่เริ่มตามสัญญา"
                    >
                    <svg class="absolute left-3 top-9 w-5 h-5 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    @error('start_date')
                      <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                  </label>
                  
                  <label class="w-full relative ">
                    <span class="block text-sm text-gray-700 mb-1">วันที่สิ้นสุด (ตามสัญญา)</span>
                    <input
                      name="end_date"
                      type="text"
                      class="thai-datepicker w-full border p-2 rounded pl-10"
                      value="{{ old('end_date') }}"
                      placeholder="กรุณาเลือกวันที่สิ้นสุดตามสัญญา"
                    >
                    <svg class="absolute left-3 top-9 w-5 h-5 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                  </label>
                  



                  <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BA000 - Project Planning (Status)</span>
                    <select name="planning_status" id="planning_status" class="w-full border p-2 rounded text-white font-semibold" onchange="updateStatusColor(this)">
                      <option value="" disabled selected>ระบุคำตอบ</option>
                      <option value="25" data-color="#dc2626">อยู่ระหว่างการรอ (On Hold) 25%</option>
                      <option value="50" data-color="#f59e0b">กำลังดำเนินการ (In Progress) 50%</option>
                      <option value="75" data-color="#3b82f6">อยู่ในระหว่างการตรวจสอบ/พิจารณา (In Review) 75%</option>
                      <option value="100" data-color="#16a34a">เสร็จสิ้น (Complete) 100%</option>
                      <option value="0" data-color="#991b1b">ยกเลิก (Canceled) 0%</option>
                    </select>
                  </label>
                  
                  <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BC000 - Project Execution (Status)</span>
                    <select
                      name="execution_status"
                      id="execution_status"
                      class="w-full border p-2 rounded text-white font-semibold"
                      onchange="updateStatusColor(this)"
                    >
                      <option value="" disabled selected>ระบุคำตอบ</option>
                      <option value="25" data-color="#dc2626" {{ old('execution_status') == '25' ? 'selected' : '' }}>อยู่ระหว่างการรอ (On Hold) 25%</option>
                      <option value="50" data-color="#f59e0b" {{ old('execution_status') == '50' ? 'selected' : '' }}>กำลังดำเนินการ (In Progress) 50%</option>
                      <option value="75" data-color="#3b82f6" {{ old('execution_status') == '75' ? 'selected' : '' }}>อยู่ในระหว่างการตรวจสอบ/พิจารณา (In Review) 75%</option>
                      <option value="100" data-color="#16a34a" {{ old('execution_status') == '100' ? 'selected' : '' }}>เสร็จสิ้น (Complete) 100%</option>
                      <option value="0" data-color="#991b1b" {{ old('execution_status') == '0' ? 'selected' : '' }}>ยกเลิก (Canceled) 0%</option>
                    </select>
                  
                    @error('execution_status')
                      <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                  </label>
                  
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BD000 - Review Status (Status)</span>
                    <input name="review_status" value="{{ old('review_status') }}" class="w-full border p-2 rounded">
                    {{-- @error('review_status') <span class="text-sm text-red-500">{{ $message }}</span> @enderror --}}
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BE000 - Closure Status (status) </span>
                    <input name="closure_status" value="{{ old('closure_status') }}" class="w-full border p-2 rounded">
                    {{-- @error('closure_status') <span class="text-sm text-red-500">{{ $message }}</span> @enderror --}}
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
                        <div class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">วันที่หมดอายุ</span>
                            <input name="irbs[0][exp_date]" type="text"
                                class="thai-datepicker w-full border p-2 rounded">
                        </div>
                        <button type="button" onclick="removeRow('irb-0', 'irb')" class="text-red-600 mb-2">✕</button>
                    </div>
                </div>
            </div>
            <button type="button" onclick="addIRBCode()" class="mb-6 px-3 py-1 bg-red-600 text-white rounded">+
                เพิ่มรหัส IRB</button>



            <hr class="my-6 border-t-2 border-gray-300">
            <!-- C: Roles and Responsibilities -->
            <h3 class="font-semibold text-lg text-blue-700 mb-2">C: Roles and Responsibilities</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">CA101 - ชื่อ PI (ผู้วิจัยหลัก)</span>
                    <input name="pi_name" type="text" class="w-full border p-2 rounded" value="">
                    @error('pi_name')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">CA103 - สังกัดผู้วิจัยหลัก</span>
                    <input name="pi_affiliation" type="text" class="w-full border p-2 rounded" value="">
                    @error('pi_affiliation')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </label>
            </div>








            <h3 class="text-balance text-blue-700 mb-2">รายชื่อผู้ร่วมวิจัย (COPI)</h3>
            <div id="copi-container" class="space-y-2 mb-4">
                <div id="copi-0" class="copi-row grid grid-cols-2 gap-4 items-center">
                    <label class="w-full">
                        <span class="block text-sm text-gray-700 mb-1">ชื่อ COPI คนที่ <span
                                class="copi-index">1</span></span>
                        <input name="copis[0][name]" type="text" class="w-full border p-2 rounded">
                    </label>
                    <div class="flex gap-2 w-full">
                        <label class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">สังกัด COPI คนที่ <span
                                    class="copi-index">1</span></span>
                            <input name="copis[0][affiliation]" type="text" class="w-full border p-2 rounded">
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
                        <span class="block text-sm mb-1">ชื่อ Co-Researcher ที่ <span
                                class="coresearch-index">1</span></span>
                        <input name="coresearch[0][name]" type="text" class="w-full border p-2 rounded">
                    </label>

                    <!-- บรรทัดที่ 2: สังกัด + ภายใน/ภายนอก + ลบ -->
                    <div class="grid grid-cols-3 gap-4 items-end">
                        <label class="w-full">
                            <span class="block text-sm mb-1">สังกัด</span>
                            <input name="coresearch[0][affiliation]" type="text" class="w-full border p-2 rounded">
                        </label>
                        <label class="w-full">
                            <span class="block text-sm mb-1">คนภายนอก/ภายใน</span>
                            <input name="coresearch[0][unit_status]" type="text" class="w-full border p-2 rounded">
                        </label>
                        <div class="flex justify-end items-end">
                            <button type="button" onclick="removeRow('coresearch-0', 'coresearch')"
                                class="text-red-600 mb-2">✕</button>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" onclick="addCoResearchRow()" class="mb-6 px-3 py-1 bg-purple-600 text-white rounded">+
                เพิ่ม Co-Researcher</button>




            <hr class="my-6 border-t-2 border-gray-300">
            <h3 class="font-semibold text-lg text-blue-700 mb-2">D: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
            <div id="fte-container" class="space-y-4 mb-4">
                <div id="fte-0" class="fte-row space-y-2">
                    <!-- บรรทัดที่ 1: ชื่อและกลุ่ม -->
                    <div class="grid grid-cols-2 gap-4">
                        <label class="w-full">
                            <span class="block text-sm text-gray-700 mb-1"><span class="fte-index">1</span>.
                                ชื่อ-นามสกุล</span>
                            <input name="fte_roles[0][name]" type="text" class="w-full border p-2 rounded">
                        </label>
                        <label class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">กลุ่ม (เช่น Scientific)</span>
                            <input name="fte_roles[0][role_group]" type="text" class="w-full border p-2 rounded">
                        </label>
                    </div>

                    <!-- บรรทัดที่ 2: ภาระงาน + วันที่ -->
                    <div class="grid grid-cols-3 gap-4">
                        <label class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">ภาระงาน</span>
                            <input name="fte_roles[0][job_detail]" type="text" class="w-full border p-2 rounded">
                        </label>
                        <label class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">วันเริ่มต้นภาระงาน</span>
                            <input name="fte_roles[0][start_date]" type="text"
                                class="thai-datepicker w-full border p-2 rounded">
                        </label>
                        <label class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">วันสิ้นสุดภาระงาน</span>
                            <input name="fte_roles[0][end_date]" type="text"
                                class="thai-datepicker w-full border p-2 rounded">
                        </label>
                    </div>

                    <!-- บรรทัดที่ 3: FTE + ปุ่มลบ -->
                    <div class="flex gap-4 items-end">
                        <label class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">FTE</span>
                            <input name="fte_roles[0][fte_value]" type="number" step="0.01"
                                class="w-full border p-2 rounded">
                        </label>
                        <button type="button" onclick="removeRow('fte-0', 'fte')" class="text-red-600 mb-2">✕</button>
                    </div>
                </div>
            </div>

            <button type="button" onclick="addFTERow()" class="mb-6 px-3 py-1 bg-blue-600 text-white rounded">+
                เพิ่มผู้มีส่วนร่วม</button>



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
                            <input name="outputs[0][type]" type="text" class="w-full border p-2 rounded"
                                placeholder="เช่น รายงาน">
                        </label>
                        <label class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">รายละเอียด / หมายเหตุ / LINK</span>
                            <input name="outputs[0][project_output]" type="text" class="w-full border p-2 rounded"
                                placeholder="เพิ่มเติม">
                        </label>
                        <div class="flex gap-2 w-full">
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">ปี</span>
                                <input name="outputs[0][year]" type="text" class="w-full border p-2 rounded"
                                    placeholder="2025">
                            </label>
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
    </div>



    <script>
        let irbIndex = 1;

        function addIRBCode() {
            const container = document.getElementById('irb-code-container');
            const id = `irb-${irbIndex}`;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="irb-row grid grid-cols-3 gap-2 items-center">
            <div class="w-full">
                <span class="block text-sm text-gray-700 mb-1">รหัส IRB ที่ <span class="irb-index">${irbIndex + 1}</span></span>
                <input name="irbs[${irbIndex}][code]" type="text" placeholder="ECxxxx" class="w-full border p-2 rounded">
            </div>
            <div class="w-full">
                <span class="block text-sm text-gray-700 mb-1">จาก (ชื่อหน่วยงาน)</span>
                <input name="irbs[${irbIndex}][source]" type="text" placeholder="ชื่อหน่วยงาน" class="w-full border p-2 rounded">
            </div>
            <div class="w-full flex gap-2 items-end">
                <div class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">วันที่หมดอายุ</span>
                    <input name="irbs[${irbIndex}][exp_date]" type="text" class="thai-datepicker w-full border p-2 rounded">
                </div>
                <button type="button" onclick="removeRow('${id}', 'irb')" class="text-red-600 mb-2">✕</button>
            </div>
        </div>
    `);

            irbIndex++;
            reindexIRB();

            // 📅 Re-initialize flatpickr สำหรับ input ใหม่
            initThaiDatepickers();
        }

        function reindexIRB() {
            const rows = document.querySelectorAll('#irb-code-container .irb-row');
            rows.forEach((row, index) => {
                const span = row.querySelector('.irb-index');
                if (span) span.textContent = index + 1;
            });
        }


        let fteIndex = 1; // เริ่มจาก 1 เพราะมี index 0 แล้ว

        function addFTERow() {
            const container = document.getElementById('fte-container');
            const id = `fte-${fteIndex}`;
            const indexLabel = fteIndex + 1;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="fte-row space-y-2">
            <div class="grid grid-cols-2 gap-4">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1"><span class="fte-index">${indexLabel}</span>. ชื่อ-นามสกุล</span>
                    <input name="fte_roles[${fteIndex}][name]" type="text" class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">กลุ่ม (เช่น Scientific)</span>
                    <input name="fte_roles[${fteIndex}][role_group]" type="text" class="w-full border p-2 rounded">
                </label>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ภาระงาน</span>
                    <input name="fte_roles[${fteIndex}][job_detail]" type="text" class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">วันเริ่มต้นภาระงาน</span>
                    <input name="fte_roles[${fteIndex}][start_date]" type="text" class="thai-datepicker w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">วันสิ้นสุดภาระงาน</span>
                    <input name="fte_roles[${fteIndex}][end_date]" type="text" class="thai-datepicker w-full border p-2 rounded">
                </label>
            </div>

            <div class="flex gap-4 items-end">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">FTE</span>
                    <input name="fte_roles[${fteIndex}][fte_value]" type="number" step="0.01" class="w-full border p-2 rounded">
                </label>
                <button type="button" onclick="removeRow('${id}', 'fte')" class="text-red-600 mb-2">✕</button>
            </div>
        </div>
    `);

            fteIndex++;
            reindexFTE();
            initThaiDatepickers?.();
        }

        function reindexFTE() {
            const rows = document.querySelectorAll('#fte-container .fte-row');
            rows.forEach((row, index) => {
                row.querySelectorAll('.fte-index').forEach(span => {
                    span.textContent = index + 1;
                });
            });
        }


        let outputIndex = 1; // เพราะเรามี index 0 แล้ว
        function addOutputRow() {
            const container = document.getElementById('output-container');
            const id = `output-${outputIndex}`;
            const labelNum = outputIndex + 1;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="output-row space-y-2">
            <label class="block w-full">
                <span class="block text-sm text-gray-700 mb-1">ชื่อผลงานที่ <span class="output-index">${labelNum}</span></span>
                <input name="outputs[${outputIndex}][title]" type="text" class="w-full border p-2 rounded" placeholder="ชื่อผลงาน">
            </label>
            <div class="grid grid-cols-3 gap-4 items-end">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ประเภทของผลงาน</span>
                    <input name="outputs[${outputIndex}][type]" type="text" class="w-full border p-2 rounded" placeholder="ประเภท">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">รายละเอียด / หมายเหตุ / LINK</span>
                    <input name="outputs[${outputIndex}][output]" type="text" class="w-full border p-2 rounded" placeholder="เพิ่มเติม">
                </label>
                <div class="flex gap-2 w-full">
                    <label class="w-full">
                        <span class="block text-sm text-gray-700 mb-1">ปี</span>
                        <input name="outputs[${outputIndex}][year]" type="text" class="w-full border p-2 rounded" placeholder="2025">
                    </label>
                    <button type="button" onclick="removeRow('${id}', 'output')" class="text-red-600 mb-2">✕</button>
                </div>
            </div>
        </div>
    `);

            outputIndex++;
            reindexOutput();
        }

        function reindexOutput() {
            const rows = document.querySelectorAll('#output-container .output-row');
            rows.forEach((row, index) => {
                const span = row.querySelector('.output-index');
                if (span) span.textContent = index + 1;
            });
        }

        function removeRow(id, type) {
            const el = document.getElementById(id);
            if (el) el.remove();
            if (type === 'fte') reindexFTE();
            if (type === 'copi') reindexCopi?.();
            if (type === 'irb') reindexIRB?.();
            if (type === 'output') reindexOutput?.();
        }


        let coResearchIndex = 1;

        function addCoResearchRow() {
            const container = document.getElementById('coresearch-container');
            const id = `coresearch-${coResearchIndex}`;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="coresearch-row space-y-2">
            <!-- บรรทัดที่ 1: ชื่อ -->
            <label class="block w-full">
                <span class="block text-sm mb-1">ชื่อ Co-Researcher ที่ <span class="coresearch-index">${coResearchIndex + 1}</span></span>
                <input name="coresearch[${coResearchIndex}][name]" type="text" class="w-full border p-2 rounded">
            </label>

            <!-- บรรทัดที่ 2: สังกัด + ภายใน/ภายนอก + ลบ -->
            <div class="grid grid-cols-3 gap-4 items-end">
                <label class="w-full">
                    <span class="block text-sm mb-1">สังกัด</span>
                    <input name="coresearch[${coResearchIndex}][affiliation]" type="text" class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm mb-1">คนภายนอก/ภายใน</span>
                    <input name="coresearch[${coResearchIndex}][unit_status]" type="text" class="w-full border p-2 rounded">
                </label>
                <div class="flex justify-end items-end">
                    <button type="button" onclick="removeRow('${id}', 'coresearch')" class="text-red-600 mb-2">✕</button>
                </div>
            </div>
        </div>
    `);

            coResearchIndex++;
            reindexCoResearch();
        }

        function removeRow(id, type) {
            const el = document.getElementById(id);
            if (el) el.remove();

            if (type === 'coresearch') reindexCoResearch();
        }

        function reindexCoResearch() {
            const rows = document.querySelectorAll('#coresearch-container .coresearch-row');
            rows.forEach((row, index) => {
                const indexLabel = row.querySelector('.coresearch-index');
                if (indexLabel) indexLabel.textContent = index + 1;
            });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr(".thai-datepicker", {
                locale: "th",
                dateFormat: "d-m-Y", // ค่าที่ส่งไป Controller (ค.ศ.)
                altInput: true,
                altFormat: "j F Y", // ค่าที่โชว์ (พ.ศ.)
                prevArrow: "", // 🔒 ซ่อนปุ่มย้อนเดือน
                nextArrow: "", // 🔒 ซ่อนปุ่มถัดไปเดือน
                onReady: toBuddhist,
                onChange: toBuddhist,
                onMonthChange: toBuddhist,
                onYearChange: toBuddhist,
                disableMobile: true
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

                // แก้ปัญหากดลูกศรเปลี่ยนปีแล้วกลายเป็น ค.ศ.
                setTimeout(() => {
                    const yearInputs = instance.calendarContainer.querySelectorAll(
                        '.flatpickr-current-month input.cur-year');
                    yearInputs.forEach(el => {
                        let currentYear = parseInt(el.value);
                        if (currentYear < 2500) {
                            el.value = currentYear + 543;
                        }

                        el.addEventListener('blur', () => {
                            let inputYear = parseInt(el.value);
                            if (inputYear > 2400) {
                                el.value = inputYear - 543;
                                instance.setDate(new Date(inputYear - 543, instance
                                    .currentMonth, 1));
                                setTimeout(() => toBuddhist(selectedDates, dateStr,
                                    instance), 5);
                            }
                        });
                    });
                }, 5); // ปล่อยให้ DOM render เสร็จก่อน
            }
        });





        let copiIndex = 1;

        function addCopiRow() {
            const container = document.getElementById('copi-container');
            const id = `copi-${copiIndex}`;
            const labelNum = copiIndex + 1;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="copi-row grid grid-cols-2 gap-4 items-center">
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">ชื่อ COPI คนที่ <span class="copi-index">${labelNum}</span></span>
                <input name="copis[${copiIndex}][name]" type="text" class="w-full border p-2 rounded">
            </label>
            <div class="flex gap-2 w-full">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">สังกัด COPI คนที่ <span class="copi-index">${labelNum}</span></span>
                    <input name="copis[${copiIndex}][affiliation]" type="text" class="w-full border p-2 rounded">
                </label>
                <button type="button" onclick="removeRow('${id}', 'copi')" class="text-red-600 mt-6">✕</button>
            </div>
        </div>
    `);

            copiIndex++;
            reindexCopi();
        }

        function reindexCopi() {
            const rows = document.querySelectorAll('#copi-container .copi-row');
            rows.forEach((row, index) => {
                const spans = row.querySelectorAll('.copi-index');
                spans.forEach(span => span.textContent = index + 1);
            });
        }


        document.addEventListener('DOMContentLoaded', function() {
            flatpickr(".year-th-picker", {
                locale: "th",
                plugins: [
                    new monthSelectPlugin({
                        shorthand: false,
                        dateFormat: "Y", // ส่งค่าไปยัง backend
                        altFormat: "Y", // แสดง พ.ศ.
                        theme: "light"
                    })
                ],
                prevArrow: "", // 🔒 ซ่อนปุ่มย้อนเดือน
                nextArrow: "", // 🔒 ซ่อนปุ่มถัดไปเดือน
                onReady: toBuddhistYear,
                onChange: toBuddhistYear,
                onYearChange: toBuddhistYear,
                disableMobile: true
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

                // แก้ปัญหากดลูกศรเปลี่ยนปีแล้วกลายเป็น ค.ศ.
                setTimeout(() => {
                    const yearInputs = instance.calendarContainer.querySelectorAll(
                        '.flatpickr-current-month input.cur-year');
                    yearInputs.forEach(el => {
                        let currentYear = parseInt(el.value);
                        if (currentYear < 2500) {
                            el.value = currentYear + 543;
                        }

                        el.addEventListener('blur', () => {
                            let inputYear = parseInt(el.value);
                            if (inputYear > 2400) {
                                el.value = inputYear - 543;
                                instance.setDate(new Date(inputYear - 543, instance
                                    .currentMonth, 1));
                                setTimeout(() => toBuddhist(selectedDates, dateStr,
                                    instance), 5);
                            }
                        });
                    });
                }, 5); // ปล่อยให้ DOM render เสร็จก่อน
            }
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('budgetInput');

            input.addEventListener('input', function(e) {
                let value = input.value.replace(/,/g, '');

                // ถ้ามีมากกว่า 1 จุดทศนิยม (.) ให้ลบส่วนที่เกิน
                const parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts[1];
                }

                // format ใหม่ให้มี , ทุกหลักพัน
                let [intPart, decimalPart] = value.split('.');
                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                input.value = decimalPart !== undefined ? `${intPart}.${decimalPart}` : intPart;
            });

            // ก่อน submit form ให้เอา , ออก
            input.form?.addEventListener('submit', function() {
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
                onClose: toBuddhist,
                onChange: toBuddhist,
                onMonthChange: toBuddhist,
                onYearChange: toBuddhist,
            });
        }

        function toBuddhist(selectedDates, dateStr, instance) {
            const altInput = instance.altInput;

            // 🔸 Format พ.ศ. สำหรับช่อง alt input
            if (selectedDates.length > 0 && altInput) {
                const date = selectedDates[0];
                const day = date.getDate();
                const month = date.toLocaleString('th-TH', {
                    month: 'long'
                });
                const year = date.getFullYear() + 543;
                altInput.value = `${day} ${month} ${year}`;
            }

            // 🔸 ปรับปีใน dropdown ให้แสดง พ.ศ. (เฉพาะถ้ายังไม่บวก)
            const years = instance.calendarContainer.querySelectorAll('.flatpickr-current-month input.cur-year');
            years.forEach(el => {
                let year = parseInt(el.value);
                if (!isNaN(year) && year < 2500) {
                    el.value = year + 543;
                }

                // 🔸 ปรับกลับเป็น ค.ศ. ถ้าผู้ใช้พิมพ์ พ.ศ.
                el.addEventListener('blur', () => {
                    let y = parseInt(el.value);
                    if (y > 2400) {
                        el.value = y - 543;
                        instance.setDate(new Date(instance.selectedDates[0]), false);
                        setTimeout(() => toBuddhist(selectedDates, dateStr, instance), 5); // 🌀 refresh UI
                    }
                });
            });
        }


        // เรียกตอนเริ่มหน้า
        document.addEventListener('DOMContentLoaded', initThaiDatepickers);


        function updateStatusColor(selectEl) {
    const selected = selectEl.options[selectEl.selectedIndex];
    const bgColor = selected.getAttribute("data-color") || "#ffffff";
    selectEl.style.backgroundColor = bgColor;
  }

  // trigger once on page load if value is pre-filled
  document.addEventListener('DOMContentLoaded', () => {
    const selectEl = document.getElementById('planning_status');
    if (selectEl) updateStatusColor(selectEl);
  });
    </script>
@endsection

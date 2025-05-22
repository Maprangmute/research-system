@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

    <div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
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

        <form action="{{ route('projects.update', $project->id) }}" method="POST">
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
                    <select name="project_type" id="project_type" class="w-full border p-2 rounded" required>
                      <option value="" disabled {{ old('project_type', $project->project_type) ? '' : 'selected' }}>
                        เลือกประเภทของโครงการ
                      </option>
                      <option value="01 ผู้สูงอายุ ตามยุทธศาสตร์ศาสตร์สุขอายุของคณะแพทยศาสตร์ศิริราชพยาบาล"
                        {{ old('project_type', $project->project_type) == '01 ผู้สูงอายุ ตามยุทธศาสตร์ศาสตร์สุขอายุของคณะแพทยศาสตร์ศิริราชพยาบาล' ? 'selected' : '' }}>
                        01 ผู้สูงอายุ ตามยุทธศาสตร์ศาสตร์สุขอายุของคณะแพทยศาสตร์ศิริราชพยาบาล
                      </option>
                      <option value="02 โครงการพัฒนาระบบเพื่อการสร้างเสริมสุขภาพสำหรับพระสงฆ์ โดยทีมสหวิทยาการ และการมีส่วนร่วมของประชาชนอย่างยั่งยืน : บางกอกน้อยโมเดล"
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
                      <option value="05 Health Policy Platform: Prince Mahidol Award Conference (PMAC), Global Health, UHC"
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
                    <select name="theme" required class="w-full border p-2 rounded">
                      <option value="" disabled {{ old('theme', $project->theme) ? '' : 'selected' }}>กรุณาเลือกกลุ่มเนื้อหา</option>
                      <option value="T101_Research_วิจัยที่ Health Policy เป็น ผู้วิจัยหลัก"
                        {{ old('theme', $project->theme) == "T101_Research_วิจัยที่ Health Policy เป็น ผู้วิจัยหลัก" ? 'selected' : '' }}>
                        T101_Research_วิจัยที่ Health Policy เป็น ผู้วิจัยหลัก
                      </option>
                      <option value="T102_Research: โครงการวิจัยที่ Team Health Policy เป็น ผู้ร่วมวิจัย"
                        {{ old('theme', $project->theme) == "T102_Research: โครงการวิจัยที่ Team Health Policy เป็น ผู้ร่วมวิจัย" ? 'selected' : '' }}>
                        T102_Research: โครงการวิจัยที่ Team Health Policy เป็น ผู้ร่วมวิจัย
                      </option>
                      <option value="T103_Research: โครงการวิจัยที่เป็นไปในลักษณะ Individual Expertise contribution (Skill รายบุคคล) เช่น การเข้าร่วมเขียน paper"
                        {{ old('theme', $project->theme) == "T103_Research: โครงการวิจัยที่เป็นไปในลักษณะ Individual Expertise contribution (Skill รายบุคคล) เช่น การเข้าร่วมเขียน paper" ? 'selected' : '' }}>
                        T103_Research: โครงการวิจัยที่เป็นไปในลักษณะ Individual Expertise contribution (Skill รายบุคคล)
                      </option>
                      <option value="T301_Model of Care development (Clinic)"
                        {{ old('theme', $project->theme) == "T301_Model of Care development (Clinic)" ? 'selected' : '' }}>
                        T301_Model of Care development (Clinic)
                      </option>
                      <option value="T401_Cohort Study"
                        {{ old('theme', $project->theme) == "T401_Cohort Study" ? 'selected' : '' }}>
                        T401_Cohort Study
                      </option>
                      <option value="T501_Curriculum Development (Education)"
                        {{ old('theme', $project->theme) == "T501_Curriculum Development (Education)" ? 'selected' : '' }}>
                        T501_Curriculum Development (Education)
                      </option>
                      <option value="T601_Consult"
                        {{ old('theme', $project->theme) == "T601_Consult" ? 'selected' : '' }}>
                        T601_Consult
                      </option>
                      <option value="T701_Process Improvement (Siriraj Health Policy)"
                        {{ old('theme', $project->theme) == "T701_Process Improvement (Siriraj Health Policy)" ? 'selected' : '' }}>
                        T701_Process Improvement (Siriraj Health Policy)
                      </option>
                      <option value="T801_Conference Session / Knowledge Sharing"
                        {{ old('theme', $project->theme) == "T801_Conference Session / Knowledge Sharing" ? 'selected' : '' }}>
                        T801_Conference Session / Knowledge Sharing
                      </option>
                      <option value="T901_Capacity Building / Skill Development"
                        {{ old('theme', $project->theme) == "T901_Capacity Building / Skill Development" ? 'selected' : '' }}>
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
                    <span class="block text-sm text-gray-700 mb-1">ขนาดโครงการ (S/M/L)</span>
                    <input name="size" value="{{ old('size', $project->size) }}" class="w-full border p-2 rounded">
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
                    <span class="block text-sm text-gray-700 mb-1">DA103 - ชื่อแหล่งทุน</span>
                    <input name="funding_source" value="{{ old('funding_source', $project->funding_source) }}"
                        class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA104 - รัฐบาล / เอกชน / มูลนิธิ</span>
                    <input name="funding_org_type" value="{{ old('funding_org_type', $project->funding_org_type) }}"
                        class="w-full border p-2 rounded">
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
                    <input name="team_start_date" value="{{ old('team_start_date', $project->team_start_date) }}"
                        type="text" class="thai-datepicker w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">วันที่สิ้นสุดทำ (ทีม)</span>
                    <input name="team_end_date" value="{{ old('team_end_date', $project->team_end_date) }}" type="text"
                        class="thai-datepicker w-full border p-2 rounded">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BA001 - วันที่เริ่มเขียน Proposal (เดือน/ปี)</span>
                    <input name="proposal_date" value="{{ old('proposal_date', $project->proposal_date) }}" type="text"
                        class="thai-datepicker w-full border p-2 rounded">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BC101 - วันที่สัญญาอนุมัติ</span>
                    <input name="contract_date" value="{{ old('contract_date', $project->contract_date) }}"
                        type="text" class="thai-datepicker w-full border p-2 rounded">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BC102 - วันที่โครงการเริ่ม (ตามสัญญา)</span>
                    <input name="start_date" value="{{ old('start_date', $project->start_date) }}" type="text"
                        class="thai-datepicker w-full border p-2 rounded">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">วันที่สิ้นสุด (ตามสัญญา)</span>
                    <input name="end_date" value="{{ old('end_date', $project->end_date) }}" type="text"
                        class="thai-datepicker w-full border p-2 rounded">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BA000 - Project Planning (Status)</span>
                    <input name="planning_status" value="{{ old('planning_status', $project->planning_status) }}"
                        class="w-full border p-2 rounded">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BC000 - Project Execution (Status)</span>
                    <input name="execution_status" value="{{ old('execution_status', $project->execution_status) }}"
                        class="w-full border p-2 rounded">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BD000 - Review Status</span>
                    <input name="review_status" value="{{ old('review_status', $project->review_status) }}"
                        class="w-full border p-2 rounded">
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">BE000 - Closure Status</span>
                    <input name="closure_status" value="{{ old('closure_status', $project->closure_status) }}"
                        class="w-full border p-2 rounded">
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
                                class="w-full border p-2 rounded">
                        </div>
                        <div class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">จาก (ชื่อหน่วยงาน) ของรหัส IRB ที่ <span
                                    class="irb-index">{{ $i + 1 }}</span></span>
                            <input name="irbs[{{ $i }}][source]" value="{{ $irb['source'] }}"
                                class="w-full border p-2 rounded">
                        </div>
                        <div class="w-full flex gap-2 items-end">
                            <div class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">วันที่หมดอายุ</span>
                                <input name="irbs[{{ $i }}][exp_date]" value="{{ $irb['exp_date'] ?? '' }}"
                                    type="text" class="thai-datepicker w-full border p-2 rounded">
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
                    <input name="pi_name" value="{{ old('pi_name', $project->pi_name) }}"
                        class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">CA103 - สังกัด PI</span>
                    <input name="pi_affiliation" value="{{ old('pi_affiliation', $project->pi_affiliation) }}"
                        class="w-full border p-2 rounded">
                </label>
            </div>





            <h3 class="text-balance text-blue-700 mb-2">รายชื่อผู้ร่วมวิจัย (COPI)</h3>
            <div id="copi-container" class="space-y-2 mb-4">
                @foreach ($project->copis ?? [] as $i => $copi)
                    <div id="copi-{{ $i }}" class="copi-row grid grid-cols-2 gap-4 items-center">
                        <label class="w-full">
                            <span class="block text-sm text-gray-700 mb-1">ชื่อ COPI คนที่ {{ $i + 1 }}</span>
                            <input name="copis[{{ $i }}][name]" value="{{ $copi['name'] }}"
                                class="w-full border p-2 rounded">
                        </label>
                        <div class="flex gap-2 w-full">
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">สังกัด COPI คนที่
                                    {{ $i + 1 }}</span>
                                <input name="copis[{{ $i }}][affiliation]" value="{{ $copi['affiliation'] }}"
                                    class="w-full border p-2 rounded">
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
                                <span class="block text-sm text-gray-700 mb-1">ชื่อ Co-Researcher คนที่ <span class="coresearch-index">{{ $i + 1 }}</span></span>
                                <input name="coresearch[{{ $i }}][name]" value="{{ $co->name }}" class="w-full border p-2 rounded">
                            </label>
                
                            <!-- สังกัด + สถานะ + ปุ่มลบ -->
                            <div class="grid grid-cols-12 gap-4 items-end">
                                <label class="col-span-5">
                                    <span class="block text-sm text-gray-700 mb-1">สังกัด</span>
                                    <input name="coresearch[{{ $i }}][affiliation]" value="{{ $co->affiliation }}" class="w-full border p-2 rounded">
                                </label>
                                <label class="col-span-5">
                                    <span class="block text-sm text-gray-700 mb-1">คนภายนอก/ภายใน</span>
                                    <select name="coresearch[{{ $i }}][unit_status]" class="w-full border p-2 rounded" required>
                                        <option value="" disabled {{ $co->unit_status == '' ? 'selected' : '' }}>กรุณาเลือก</option>
                                        <option value="ภายใน" {{ $co->unit_status == 'ภายใน' ? 'selected' : '' }}>ภายใน</option>
                                        <option value="ภายนอก" {{ $co->unit_status == 'ภายนอก' ? 'selected' : '' }}>ภายนอก</option>
                                    </select>
                                </label>
                                <div class="col-span-2 flex justify-end pt-6">
                                    <button type="button" onclick="removeRow('coresearch-{{ $i }}', 'coresearch')" class="text-red-600 text-xl">✕</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- ปุ่มเพิ่ม -->
                <button type="button" onclick="addCoResearchRow()" class="mb-6 px-3 py-1 bg-purple-600 text-white rounded">
                    + เพิ่ม Co-Researcher
                </button>
                



            <hr class="my-6 border-t-2 border-gray-300">
            <h3 class="font-semibold text-lg text-blue-700 mb-2">D: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
            <div id="fte-container" class="space-y-4 mb-4">
                @foreach ($project->fteRoles ?? [] as $i => $fte)
                    <div id="fte-{{ $i }}" class="fte-row space-y-2">
                        <!-- บรรทัด 1 -->
                        <div class="grid grid-cols-2 gap-4">
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1"><span
                                        class="fte-index">{{ $loop->iteration }}</span>. ชื่อ-นามสกุล</span>
                                <input name="fte_roles[{{ $i }}][name]" type="text"
                                    value="{{ $fte->name }}" class="w-full border p-2 rounded">
                            </label>
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">กลุ่ม (เช่น Scientific)</span>
                                <input name="fte_roles[{{ $i }}][role_group]" type="text"
                                    value="{{ $fte->role_group }}" class="w-full border p-2 rounded">
                            </label>
                        </div>

                        <!-- บรรทัด 2 -->
                        <div class="grid grid-cols-3 gap-4">
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">ภาระงาน</span>
                                <input name="fte_roles[{{ $i }}][job_detail]" type="text"
                                    value="{{ $fte->job_detail ?? '' }}" class="w-full border p-2 rounded">
                            </label>
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">วันเริ่มต้นภาระงาน</span>
                                <input name="fte_roles[{{ $i }}][start_date]" type="text"
                                    value="{{ $fte->start_date ?? '' }}"
                                    class="thai-datepicker w-full border p-2 rounded">
                            </label>
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">วันสิ้นสุดภาระงาน</span>
                                <input name="fte_roles[{{ $i }}][end_date]" type="text"
                                    value="{{ $fte->end_date ?? '' }}" class="thai-datepicker w-full border p-2 rounded">
                            </label>
                        </div>

                        <!-- บรรทัด 3 -->
                        <div class="flex gap-4 items-end">
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">FTE</span>
                                <input name="fte_roles[{{ $i }}][fte_value]" type="number" step="0.01"
                                    value="{{ $fte->fte_value }}" class="w-full border p-2 rounded">
                            </label>
                            <button type="button" onclick="removeRow('fte-{{ $i }}', 'fte')"
                                class="text-red-600 mb-2">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" onclick="addFTERow()" class="mb-6 px-3 py-1 bg-blue-600 text-white rounded">+
                เพิ่มผู้มีส่วนร่วม</button>



            <h3 class="font-semibold text-lg text-blue-700 mb-2">E: ผลงานวิจัย (Output)</h3>
            <div id="output-container" class="space-y-4 mb-6">
                @foreach ($project->outputs ?? [] as $i => $out)
                    <div id="output-{{ $i }}" class="output-row space-y-2">
                        <!-- บรรทัดที่ 1: ชื่อผลงาน -->
                        <label class="block w-full">
                            <span class="block text-sm text-gray-700 mb-1">ชื่อผลงานที่ <span
                                    class="output-index">{{ $loop->iteration }}</span></span>
                            <input name="outputs[{{ $i }}][title]" value="{{ $out['title'] }}"
                                class="w-full border p-2 rounded" placeholder="ชื่อผลงาน">
                        </label>

                        <!-- บรรทัดที่ 2: ประเภท + หมายเหตุ + ปี -->
                        <div class="grid grid-cols-3 gap-4 items-end">
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">ประเภทของผลงาน</span>
                                <input name="outputs[{{ $i }}][type]" value="{{ $out['type'] }}"
                                    class="w-full border p-2 rounded" placeholder="เช่น บทความ">
                            </label>
                            <label class="w-full">
                                <span class="block text-sm text-gray-700 mb-1">รายละเอียด / หมายเหตุ / link</span>
                                <input name="outputs[{{ $i }}][project_output]"
                                    value="{{ $out['project_output'] ?? '' }}" class="w-full border p-2 rounded"
                                    placeholder="เช่น ตีพิมพ์แล้ว">
                            </label>
                            <div class="flex gap-2 w-full">
                                <label class="w-full">
                                    <span class="block text-sm text-gray-700 mb-1">ปี</span>
                                    <input name="outputs[{{ $i }}][year]" value="{{ $out['year'] }}"
                                        class="w-full border p-2 rounded" placeholder="2025">
                                </label>
                                <button type="button" onclick="removeRow('output-{{ $i }}', 'output')"
                                    class="text-red-600 mb-2">✕</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" onclick="addOutputRow()" class="mb-6 px-3 py-1 bg-green-600 text-white rounded">+
                เพิ่ม Output</button>



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



            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">💾
                    บันทึกการเปลี่ยนแปลง</button>
            </div>
        </form>
    </div>



    <script>
        let irbCodeIndex = {{ count($project->irbs ?? []) }};

        function addIRBCode() {
            const container = document.getElementById('irb-code-container');
            const id = `irb-${irbCodeIndex}`;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="irb-row grid grid-cols-3 gap-2 items-center">
            <div class="w-full">
                <span class="block text-sm text-gray-700 mb-1">รหัส IRB ที่ <span class="irb-index"></span></span>
                <input name="irbs[${irbCodeIndex}][code]" type="text" placeholder="ECxxxx" class="w-full border p-2 rounded">
            </div>
            <div class="w-full">
                <span class="block text-sm text-gray-700 mb-1">จาก (ชื่อหน่วยงาน) ของรหัส IRB ที่ <span class="irb-index"></span></span>
                <input name="irbs[${irbCodeIndex}][source]" type="text" placeholder="ชื่อหน่วยงาน" class="w-full border p-2 rounded">
            </div>
            <div class="w-full flex gap-2 items-end">
                <div class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">วันที่หมดอายุ</span>
                    <input name="irbs[${irbCodeIndex}][exp_date]" type="text" class="thai-datepicker w-full border p-2 rounded">
                </div>
                <button type="button" onclick="removeRow('${id}', 'irb')" class="text-red-600 mb-2">✕</button>
            </div>
        </div>
    `);

            irbCodeIndex++;
            reindexIRB();

            // 📅 Re-initialize flatpickr สำหรับ input ใหม่
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
                const labels = row.querySelectorAll('.copi-index');
                labels.forEach(label => label.textContent = index + 1);
            });
        }





        function addFTERow() {
            const container = document.getElementById('fte-container');
            const index = container.querySelectorAll('.fte-row').length;
            const id = `fte-${index}`;

            container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="fte-row space-y-2">
            <!-- บรรทัดที่ 1 -->
            <div class="grid grid-cols-2 gap-4">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1 py-4">
                        <span class="fte-index">${index + 1}</span>. ชื่อ-นามสกุล
                    </span>
                    <input name="fte_roles[${index}][name]" type="text" class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">กลุ่ม (เช่น Scientific)</span>
                    <input name="fte_roles[${index}][role_group]" type="text" class="w-full border p-2 rounded">
                </label>
            </div>

            <!-- บรรทัดที่ 2 -->
            <div class="grid grid-cols-3 gap-4">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ภาระงาน</span>
                    <input name="fte_roles[${index}][job_detail]" type="text" class="w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">วันเริ่มต้นภาระงาน</span>
                    <input name="fte_roles[${index}][start_date]" type="text" class="thai-datepicker w-full border p-2 rounded">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">วันสิ้นสุดภาระงาน</span>
                    <input name="fte_roles[${index}][end_date]" type="text" class="thai-datepicker w-full border p-2 rounded">
                </label>
            </div>

            <!-- บรรทัดที่ 3 -->
            <div class="flex gap-4 items-end">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">FTE</span>
                    <input name="fte_roles[${index}][fte_value]" type="number" step="0.01" class="w-full border p-2 rounded">
                </label>
                <button type="button" onclick="removeRow('${id}', 'fte')" class="text-red-600 mb-2">✕</button>
            </div>
        </div>
    `);

            reindexFTE();
            initThaiDatepickers(); // 🟢 อย่าลืมเรียกเพื่อ enable datepicker ใหม่
        }




        function reindexFTE() {
            const rows = document.querySelectorAll('#fte-container .fte-row');
            rows.forEach((row, index) => {
                const span = row.querySelector('.fte-index');
                if (span) span.textContent = index + 1;
            });
        }


        let outputIndex = {{ count($project->outputs ?? []) }};

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
                    <input name="outputs[${outputIndex}][type]" type="text" class="w-full border p-2 rounded" placeholder="เช่น รายงาน">
                </label>
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">รายละเอียด / หมายเหตุ / link </span>
                    <input name="outputs[${outputIndex}][project_output]" type="text" class="w-full border p-2 rounded" placeholder="เพิ่มเติม">
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

        let coResearchIndex = {{ count($project->coResearches ?? []) }};

        function addCoResearchRow() {
            const container = document.getElementById('coresearch-container');
            const id = `coresearch-${coResearchIndex}`;
            const labelNum = coResearchIndex + 1;

            container.insertAdjacentHTML('beforeend', `
    <div id="${id}" class="coresearch-row space-y-2">
      <!-- ชื่อ Co-Researcher -->
      <label class="block w-full">
        <span class="block text-sm text-gray-700 mb-1">ชื่อ Co-Researcher คนที่ <span class="coresearch-index">${labelNum}</span></span>
        <input name="coresearch[${coResearchIndex}][name]" type="text" class="w-full border p-2 rounded">
      </label>

      <!-- สังกัด / คนภายนอก-ภายใน / ปุ่มลบ -->
      <div class="grid grid-cols-12 gap-4 items-end">
        <label class="col-span-5">
          <span class="block text-sm text-gray-700 mb-1">สังกัด</span>
          <input name="coresearch[${coResearchIndex}][affiliation]" type="text" class="w-full border p-2 rounded">
        </label>
        <label class="col-span-5">
          <span class="block text-sm text-gray-700 mb-1">คนภายนอก/ภายใน</span>
          <select name="coresearch[${coResearchIndex}][unit_status]" class="w-full border p-2 rounded" required>
            <option value="" disabled selected>กรุณาเลือก</option>
            <option value="ภายใน">ภายใน</option>
            <option value="ภายนอก">ภายนอก</option>
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
    </script>



@endsection

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
    $projectUserDirectory = $allUsers->map(fn($user) => [
        'id' => $user->id,
        'full_name' => $user->full_name,
        'research_display_name' => $user->research_display_name,
    ])->values();
    $piInternalAffiliation = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ คณะแพทยศาสตร์ศิริราชพยาบาล มหาวิทยาลัยมหิดล';
    $internalCoresearchUnit = 'ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
    $internalCoresearchAffiliation = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ คณะแพทยศาสตร์ศิริราชพยาบาล มหาวิทยาลัยมหิดล';
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
@endphp

{{-- datalist ใช้ร่วมทุกแถว --}}
<datalist id="userNames">
    @foreach ($allUsers as $user)
        @php $fullName = trim($user->prefix . ' ' . $user->name); @endphp
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
        @php
            $fullName = trim(($user->prefix ?? '') . ' ' . ($user->name ?? ''));
        @endphp
        <option value="{{ $fullName }}"></option>
    @endforeach
</datalist>


<script>
    window.AUTH_USER_ID = {{ auth()->id() ?? 'null' }};
    window.PROJECT_CARD_DATE_MODE = 'create';
    window.projectUserDirectory = @json($projectUserDirectory);
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
        <aside class="edit-section-nav" aria-label="เมนูลัดหัวข้อ">
            <button type="button" class="edit-section-dot" data-nav-target="section-a" aria-label="A: ข้อมูลโครงการ"><span class="edit-section-dot__short">A</span><span class="edit-section-dot__full">A: ข้อมูลโครงการ</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-b" aria-label="B: ระยะเวลาดำเนินโครงการ"><span class="edit-section-dot__short">B</span><span class="edit-section-dot__full">B: ระยะเวลาดำเนินโครงการ</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-c" aria-label="C: ข้อมูลแหล่งทุน"><span class="edit-section-dot__short">C</span><span class="edit-section-dot__full">C: ข้อมูลแหล่งทุน</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-d" aria-label="D: Roles and Responsibilities"><span class="edit-section-dot__short">D</span><span class="edit-section-dot__full">D: Roles and Responsibilities</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-e" aria-label="E: รหัส IRB"><span class="edit-section-dot__short">E</span><span class="edit-section-dot__full">E: รหัส IRB</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-f" aria-label="F: รายชื่อผู้มีส่วนร่วมในการทำงาน"><span class="edit-section-dot__short">F</span><span class="edit-section-dot__full">F: รายชื่อผู้มีส่วนร่วมในการทำงาน</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-g" aria-label="G: ผลงานวิจัย (Outputs)"><span class="edit-section-dot__short">G</span><span class="edit-section-dot__full">G: ผลงานวิจัย (Outputs)</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-j" aria-label="I: ยุทธศาสตร์ Siriraj Health Policy"><span class="edit-section-dot__short">I</span><span class="edit-section-dot__full">I: ยุทธศาสตร์ Siriraj Health Policy</span></button>
        </aside>
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
            <h3 id="section-a" class="edit-section-target font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
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
                    <span class="block text-sm text-gray-700 mb-1">กลุ่มเนื้อหาหลักของงาน(Content/Theme) <span class="text-red-500">*</span></span>
                    <select name="project_type" id="project_type" class="w-full border p-2 rounded" required
                        oninvalid="this.setCustomValidity('กรุณาเลือกกลุ่มเนื้อหาหลักของงาน')"
                        oninput="this.setCustomValidity('')">
                        <option value="" disabled selected>กรุณาเลือกกลุ่มเนื้อหาหลักของงาน</option>
                        <option value="01 Aging and Frailty">01 Aging and Frailty</option>
                        <option value="02 Health Systems and HTA">02 Health Systems and HTA</option>
                        <option value="03 Rational Drug Use (RDU)">03 Rational Drug Use (RDU)</option>
                        <option value="04 Real-World Evidence (RWE) and Clinical–Technical Cohorts">04 Real-World Evidence
                            (RWE) and Clinical–Technical Cohorts</option>
                        <option value="05 Global Health">05 Global Health</option>
                        <option value="06 Other Areas">06 Other Areas</option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">ประเภทของโครงการ/กิจกรรม (TYPE) <span class="text-red-500">*</span></span>
                    <select name="theme" required oninvalid="this.setCustomValidity('กรุณาเลือกประเภทของโครงการ/กิจกรรม')"
                        oninput="this.setCustomValidity('')" class="w-full border p-2 rounded">
                        <option value="">กรุณาเลือกประเภทของโครงการ / กิจกรรม</option>
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

            </div>

            <!-- B: ระยะเวลาดำเนินโครงการ -->
            <h3 id="section-b" class="edit-section-target font-semibold text-lg text-blue-700 mt-6 mb-2">B: ระยะเวลาดำเนินโครงการ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

                <x-date-be name="team_start_date" label="วันที่โครงการเริ่มทำงานของทีมวิจัย"
                    placeholder="กรุณาเลือกวันที่เริ่มทำงานของทีมวิจัย" />

                <div class="w-full">
                    <label class="block text-sm text-gray-700 mb-1" for="date_team_end_date">
                        วันที่สิ้นสุดการทำงานของทีมวิจัย
                    </label>
                    <div class="relative">
                        <input
                            id="date_team_end_date"
                            type="text"
                            name="team_end_date"
                            value="{{ old('team_end_date') }}"
                            placeholder="กรุณาเลือกวันที่สิ้นสุดการทำงานของทีมวิจัย"
                            readonly
                            data-force-be-date="1"
                            class="thai-datepicker w-full border rounded-lg px-3 py-2 pr-10 bg-white cursor-pointer focus:ring-2 focus:ring-blue-500 @error('team_end_date') border-red-500 bg-red-50 @enderror"
                        >
                        <button
                            type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
                            data-open-picker
                            data-target="date_team_end_date"
                        >
                            📅
                        </button>
                    </div>
                    @error('team_end_date')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <x-date-be name="proposal_date" label="BA001 - วันที่เริ่มเขียน Proposal"
                    placeholder="กรุณาเลือกวันที่เริ่มเขียน Proposal" />

                <x-date-be name="contract_date" label="BC101 - วันที่สัญญาอนุมัติ"
                    placeholder="กรุณาเลือกวันที่อนุมัติสัญญา" />

                <x-date-be name="start_date" label="BC102 - วันที่โครงการเริ่ม (ตามสัญญา)"
                    placeholder="กรุณาเลือกวันที่เริ่มตามสัญญา" />

                <div class="w-full">
                    <label class="block text-sm text-gray-700 mb-1" for="date_end_date">
                        BC103 - วันที่สิ้นสุด <strong>(ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ)</strong>
                    </label>
                    <div class="relative">
                        <input
                            id="date_end_date"
                            type="text"
                            name="end_date"
                            value="{{ old('end_date') }}"
                            placeholder="กรุณาเลือกวันที่สิ้นสุดตามสัญญา"
                            readonly
                            data-force-be-date="1"
                            class="thai-datepicker w-full border rounded-lg px-3 py-2 pr-10 bg-white cursor-pointer focus:ring-2 focus:ring-blue-500 @error('end_date') border-red-500 bg-red-50 @enderror"
                        >
                        <button
                            type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
                            data-open-picker
                            data-target="date_end_date"
                        >
                            📅
                        </button>
                    </div>
                    @error('end_date')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

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

            <h3 id="section-c" class="edit-section-target font-semibold text-lg text-blue-700 mt-6 mb-2">C: ข้อมูลแหล่งทุน</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">DA102 - ประเภทแหล่งทุน</span>
                    <select name="funding_type" class="w-full border p-2 rounded">
                        <option value="" disabled selected>กรุณาเลือกประเภทแหล่งทุน</option>
                        <option value="ไม่มีแหล่งทุน">ไม่มีแหล่งทุน</option>
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
                    <span class="block text-sm text-gray-700 mb-1">DA106 - งบประมาณ (บาท) <span class="text-red-600">*ใส่ตัวเลขเท่านั้น หากไม่มี ให้เว้นว่างไว้*</span></span>
                    <input id="budgetInput" name="budget" type="text" class="w-full border p-2 rounded"
                        placeholder="ระบุจำนวนเงินที่ได้รับ" inputmode="decimal" pattern="[\d,\.]*"
                        data-max="1000000000000" autocomplete="off">
                    <span id="budgetInputNotice" class="mt-1 hidden text-sm text-red-600">กรุณากรอกตัวเลขเท่านั้น</span>
                </label>
            </div>

            <hr class="my-6 border-t-2 border-gray-300">
            <!-- C: Roles and Responsibilities -->
            <h3 id="section-d" class="edit-section-target font-semibold text-lg text-blue-700 mb-2">D: Roles and Responsibilities</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <label class="block w-full">
                    <span class="block text-sm font-medium text-gray-700 mb-1">
                        CA101 - ประเภท PI (ผู้วิจัยหลัก) <span class="text-red-500">*</span>
                    </span>
                    <select name="pi_person_type" data-pi-person-type
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                        required
                        oninvalid="this.setCustomValidity('กรุณาเลือกประเภทผู้วิจัยหลัก')"
                        oninput="this.setCustomValidity('')">
                        <option value="">กรุณาเลือก</option>
                        <option value="internal" {{ old('pi_person_type') === 'internal' ? 'selected' : '' }}>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                        <option value="external" {{ old('pi_person_type', 'external') === 'external' ? 'selected' : '' }}>คนนอก</option>
                    </select>
                </label>

                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">CA103 - สังกัดผู้วิจัยหลัก <span class="text-red-500">*</span></span>
                    <div class="relative">
                        <input name="pi_affiliation" type="text" list="piaffiliationList" data-pi-affiliation
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                            value="{{ old('pi_affiliation', '') }}"
                            placeholder="เลือกประเภทผู้วิจัยหลักก่อน" required disabled
                            oninvalid="this.setCustomValidity('กรุณาระบุสังกัดผู้วิจัยหลัก')"
                            oninput="this.setCustomValidity('')">
                    </div>
                    @error('pi_affiliation')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block w-full md:col-span-2" data-pi-manual-wrap>
                    <span class="block text-sm font-medium text-gray-700 mb-1">
                        CA102 - ชื่อ PI (ผู้วิจัยหลัก) <span class="text-red-500">*</span>
                    </span>
                    <input type="hidden" name="pi_user_id" value="{{ old('pi_user_id') }}" data-pi-user-id>
                    <input type="hidden" name="pi_name" value="{{ old('pi_name') }}" data-pi-name-hidden>
                    <input type="text" list="userNames" data-pi-manual-name
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                        value="{{ old('pi_name', '') }}"
                        placeholder="เลือกประเภทผู้วิจัยหลักก่อน" disabled
                        oninvalid="this.setCustomValidity('กรุณาระบุชื่อผู้วิจัยหลัก')"
                        oninput="this.setCustomValidity('')">
                    @error('pi_name')
                        <span class="text-sm text-red-500 mt-1">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block w-full md:col-span-2 hidden" data-pi-user-wrap>
                    <span class="block text-sm font-medium text-gray-700 mb-1">
                        CA102 - เลือก PI จากรายชื่อบุคลากร <span class="text-red-500">*</span>
                    </span>
                    <select data-pi-user-select
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                        disabled
                        oninvalid="this.setCustomValidity('กรุณาเลือกผู้วิจัยหลักจากรายการ')"
                        oninput="this.setCustomValidity('')">
                        <option value="">พิมพ์เพื่อค้นหาชื่อบุคลากร</option>
                        @foreach ($allUsers as $user)
                            <option value="{{ $user->id }}">{{ trim(($user->prefix ?? '') . ' ' . ($user->name ?? '')) }}</option>
                        @endforeach
                    </select>
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
                    <div class="copi-row space-y-2 p-4 border rounded-lg bg-gray-50" data-repeatable-row>
                        <input type="hidden" name="copis[0][name]" data-copi-name>
                        <input type="hidden" name="copis[0][user_id]" data-copi-user-id>

                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-600">ลำดับ</span>
                            <span data-repeatable-index
                                class="inline-flex ml-1 px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold">
                                1
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm text-gray-700 mb-1">
                                    คนใน / คนนอก <span class="text-red-500">*</span>
                                </span>
                                <select name="copis[0][person_type]" data-copi-person-type
                                    class="w-full border p-2 rounded" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกประเภท COPI')"
                                    oninput="this.setCustomValidity('')">
                                    <option value="">กรุณาเลือก</option>
                                    <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                    <option value="external">คนนอก</option>
                                </select>
                            </label>

                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm text-gray-700 mb-1">
                                    สังกัด COPI <span class="text-red-500">*</span>
                                </span>
                                <input name="copis[0][affiliation]" type="text" list="copiAffiliationList" data-copi-affiliation
                                    class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed"
                                    placeholder="เลือกประเภท COPI ก่อน" disabled
                                    oninvalid="this.setCustomValidity('กรุณาระบุสังกัด COPI')"
                                    oninput="this.setCustomValidity('')">
                            </label>

                            <div class="col-span-12 md:col-span-2 flex justify-end md:pt-6">
                                <button type="button" class="text-red-600 text-xl" data-repeatable-remove>
                                    ✕
                                </button>
                            </div>

                            <label class="col-span-12" data-copi-manual-wrap>
                                <span class="block text-sm text-gray-700 mb-1">
                                    ชื่อ COPI <span class="text-red-500">*</span>
                                </span>
                                <input type="text" list="userNames" data-copi-manual-name
                                    class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed"
                                    placeholder="เลือกประเภท COPI ก่อน" disabled
                                    oninvalid="this.setCustomValidity('กรุณาระบุชื่อ COPI')"
                                    oninput="this.setCustomValidity('')">
                            </label>

                            <label class="col-span-12 hidden" data-copi-user-wrap>
                                <span class="block text-sm text-gray-700 mb-1">
                                    เลือก COPI จากรายชื่อบุคลากร <span class="text-red-500">*</span>
                                </span>
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

                </div>

                {{-- ================== template ================== --}}
                <x-slot:template>
                    <div class="copi-row space-y-2 p-4 border rounded-lg bg-white" data-repeatable-row>
                        <input type="hidden" name="copis[__i__][name]" data-copi-name>
                        <input type="hidden" name="copis[__i__][user_id]" data-copi-user-id>

                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-600">ลำดับ</span>
                            <span data-repeatable-index
                                class="inline-flex ml-1 px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold">
                                #
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm text-gray-700 mb-1">
                                    คนใน / คนนอก <span class="text-red-500">*</span>
                                </span>
                                <select name="copis[__i__][person_type]" data-copi-person-type class="w-full border p-2 rounded" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกประเภท COPI')"
                                    oninput="this.setCustomValidity('')">
                                    <option value="">กรุณาเลือก</option>
                                    <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                    <option value="external">คนนอก</option>
                                </select>
                            </label>

                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm text-gray-700 mb-1">
                                    สังกัด COPI <span class="text-red-500">*</span>
                                </span>
                                <input name="copis[__i__][affiliation]" type="text" list="copiAffiliationList" data-copi-affiliation
                                    class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed"
                                    placeholder="เลือกประเภท COPI ก่อน" disabled
                                    oninvalid="this.setCustomValidity('กรุณาระบุสังกัด COPI')"
                                    oninput="this.setCustomValidity('')">
                            </label>

                            <div class="col-span-12 md:col-span-2 flex justify-end md:pt-6">
                                <button type="button" class="text-red-600 text-xl" data-repeatable-remove>
                                    ✕
                                </button>
                            </div>

                            <label class="col-span-12" data-copi-manual-wrap>
                                <span class="block text-sm text-gray-700 mb-1">
                                    ชื่อ COPI <span class="text-red-500">*</span>
                                </span>
                                <input type="text" list="userNames" data-copi-manual-name
                                    class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed"
                                    placeholder="เลือกประเภท COPI ก่อน" disabled
                                    oninvalid="this.setCustomValidity('กรุณาระบุชื่อ COPI')"
                                    oninput="this.setCustomValidity('')">
                            </label>

                            <label class="col-span-12 hidden" data-copi-user-wrap>
                                <span class="block text-sm text-gray-700 mb-1">
                                    เลือก COPI จากรายชื่อบุคลากร <span class="text-red-500">*</span>
                                </span>
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
                </x-slot:template>

            </x-repeatable-row>


            <h3 class="text-blue-700 font-semibold mt-2"> Co-Researcher</h3>
            <x-repeatable-row id="coresearch" addText="+ เพิ่ม Co-Researcher" addClass="mb-6 px-3 py-1 bg-purple-600 text-white rounded">

                {{-- ================== container ================== --}}
                <div data-repeatable-container class="space-y-4 mb-4">

                    {{-- ================== แถวแรก ================== --}}
                    <div class="space-y-2 p-4 border rounded-lg bg-gray-50 coresearch-row" data-repeatable-row>

                        <input type="hidden" name="coresearch[0][name]" data-coresearch-name>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-600">ลำดับ</span>
                            <span data-repeatable-index
                                class="inline-flex ml-1 px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold">
                                1
                            </span>
                        </div>

                        {{-- แถวสังกัด + สถานะ + ลบ --}}
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm mb-1">
                                    คนภายนอก / ภายใน <span class="text-red-500">*</span>
                                </span>
                                <select name="coresearch[0][unit_status]" data-coresearch-unit-status required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกคนภายนอก/ภายใน')"
                                    oninput="this.setCustomValidity('')" class="w-full border p-2 rounded">
                                    <option value="">กรุณาเลือก</option>
                                    <option value="ภายในคณะแพทยศาสตร์ศิริราชพยาบาล">
                                        ภายในคณะแพทยศาสตร์ศิริราชพยาบาล
                                    </option>
                                    <option value="ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล">
                                        ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล
                                    </option>
                                    <option value="{{ $internalCoresearchUnit }}">
                                        {{ $internalCoresearchUnit }}
                                    </option>
                                </select>
                            </label>

                            <label class="col-span-12 md:col-span-5" data-coresearch-affiliation-wrap>
                                <span class="block text-sm mb-1">สังกัด <span class="text-red-500">*</span></span>
                                <input name="coresearch[0][affiliation]" type="text" list="coresearchAffiliationList" data-coresearch-affiliation
                                    class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed"
                                    placeholder="เลือกคนภายนอก / ภายในก่อน" disabled
                                    oninvalid="this.setCustomValidity('กรุณาระบุสังกัด')"
                                    oninput="this.setCustomValidity('')">
                            </label>

                            <div class="col-span-12 md:col-span-2 flex items-center justify-end md:pt-6">
                                <button type="button" class="text-red-600 text-xl" data-repeatable-remove>
                                    ✕
                                </button>
                            </div>

                            <label class="col-span-12" data-coresearch-manual-wrap>
                                <span class="block text-sm mb-1">ชื่อ Co-Researcher <span class="text-red-500">*</span></span>
                                <input type="text" list="coresearchUsers" data-coresearch-manual-name
                                    class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed"
                                    placeholder="เลือกคนภายนอก / ภายในก่อน" disabled
                                    oninvalid="this.setCustomValidity('กรุณาระบุชื่อ Co-Researcher')"
                                    oninput="this.setCustomValidity('')">
                            </label>

                            <label class="col-span-12 hidden" data-coresearch-user-wrap>
                                <span class="block text-sm mb-1">เลือกบุคลากรภายในหน่วยวิจัย <span class="text-red-500">*</span></span>
                                <select name="coresearch[0][user_id]" data-coresearch-user-select class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed" disabled
                                    oninvalid="this.setCustomValidity('กรุณาเลือกบุคลากรภายในหน่วยวิจัยจากรายการ')"
                                    oninput="this.setCustomValidity('')">
                                    <option value="">พิมพ์เพื่อค้นหาชื่อบุคลากร</option>
                                    @foreach ($allUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                    @endforeach
                                </select>
                            </label>

                        </div>
                    </div>

                </div>

                {{-- ================== template ================== --}}
                <x-slot:template>
                    <div class="space-y-2 p-4 border rounded-lg bg-white coresearch-row" data-repeatable-row>

                        <input type="hidden" name="coresearch[__i__][name]" data-coresearch-name>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-600">ลำดับ</span>
                            <span data-repeatable-index
                                class="inline-flex ml-1 px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold">
                                #
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <label class="col-span-12 md:col-span-5">
                                <span class="block text-sm mb-1">
                                    คนภายนอก / ภายใน <span class="text-red-500">*</span>
                                </span>
                                <select name="coresearch[__i__][unit_status]" data-coresearch-unit-status required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกคนภายนอก/ภายใน')"
                                    oninput="this.setCustomValidity('')" class="w-full border p-2 rounded">
                                    <option value="">กรุณาเลือก</option>
                                    <option value="ภายในคณะแพทยศาสตร์ศิริราชพยาบาล">
                                        ภายในคณะแพทยศาสตร์ศิริราชพยาบาล
                                    </option>
                                    <option value="ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล">
                                        ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล
                                    </option>
                                    <option value="{{ $internalCoresearchUnit }}">
                                        {{ $internalCoresearchUnit }}
                                    </option>
                                </select>
                            </label>

                            <label class="col-span-12 md:col-span-5" data-coresearch-affiliation-wrap>
                                <span class="block text-sm mb-1">สังกัด <span class="text-red-500">*</span></span>
                                <input name="coresearch[__i__][affiliation]" type="text" list="coresearchAffiliationList" data-coresearch-affiliation
                                    class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed"
                                    placeholder="เลือกคนภายนอก / ภายในก่อน" disabled
                                    oninvalid="this.setCustomValidity('กรุณาระบุสังกัด')"
                                    oninput="this.setCustomValidity('')">
                            </label>

                            <div class="col-span-12 md:col-span-2 flex items-center justify-end md:pt-6">
                                <button type="button" class="text-red-600 text-xl" data-repeatable-remove>
                                    ✕
                                </button>
                            </div>

                            <label class="col-span-12" data-coresearch-manual-wrap>
                                <span class="block text-sm mb-1">ชื่อ Co-Researcher <span class="text-red-500">*</span></span>
                                <input type="text" list="coresearchUsers" data-coresearch-manual-name
                                    class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed"
                                    placeholder="เลือกคนภายนอก / ภายในก่อน" disabled
                                    oninvalid="this.setCustomValidity('กรุณาระบุชื่อ Co-Researcher')"
                                    oninput="this.setCustomValidity('')">
                            </label>

                            <label class="col-span-12 hidden" data-coresearch-user-wrap>
                                <span class="block text-sm mb-1">เลือกบุคลากรภายในหน่วยวิจัย <span class="text-red-500">*</span></span>
                                <select name="coresearch[__i__][user_id]" data-coresearch-user-select class="w-full border p-2 rounded bg-slate-100 text-slate-400 cursor-not-allowed" disabled
                                    oninvalid="this.setCustomValidity('กรุณาเลือกบุคลากรภายในหน่วยวิจัยจากรายการ')"
                                    oninput="this.setCustomValidity('')">
                                    <option value="">พิมพ์เพื่อค้นหาชื่อบุคลากร</option>
                                    @foreach ($allUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                    @endforeach
                                </select>
                            </label>

                        </div>
                    </div>
                </x-slot:template>

            </x-repeatable-row>

            {{-- datalist ใช้ร่วมทุกแถว --}}
            <datalist id="coresearchUsers">
                @foreach ($coresearchNameList as $coresearchName)
                    <option value="{{ $coresearchName }}"></option>
                @endforeach
            </datalist>

            <!-- รหัส IRB -->
            <hr class="my-6 border-t-2 border-gray-300">
            <h3 id="section-e" class="edit-section-target font-semibold text-lg text-blue-700 mb-2">E: รหัส IRB</h3>
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
            <div class="flex justify-between items-center mb-2">
                <h3 id="section-f" class="edit-section-target font-semibold text-lg text-blue-700 mb-2">F: รายชื่อผู้มีส่วนร่วมในการทำงาน</h3>
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

                                {{-- JOB + TASK + DATES --}}
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">

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

                                    <label>
                                        <span class="block text-sm font-semibold mb-1">
                                            รายละเอียดงาน <span class="text-red-500">*</span>
                                        </span>
                                        <select name="fte_roles[0][task_detail]" class="w-full border p-2 rounded" required>
                                            <option value="">เลือกรายละเอียดงาน</option>
                                            @foreach ($taskDetails as $taskDetail)
                                                <option value="{{ $taskDetail }}">{{ $taskDetail }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <div class="w-full">
                                        <label class="block text-sm text-gray-700 mb-1">
                                            วันเริ่มต้นภาระงาน <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                type="text"
                                                name="fte_roles[0][start_date]"
                                                placeholder="เลือกวันเริ่มต้น"
                                                readonly
                                                required
                                                data-date-role="start"
                                                class="fte-shared-datepicker w-full border rounded-lg px-3 py-2 pr-10 bg-white cursor-pointer"
                                            >
                                            <button
                                                type="button"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
                                                aria-label="เปิดปฏิทินวันเริ่มต้น"
                                            >
                                                📅
                                            </button>
                                        </div>
                                    </div>

                                    <div class="w-full">
                                        <label class="block text-sm text-gray-700 mb-1">
                                            วันสิ้นสุดภาระงาน <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                type="text"
                                                name="fte_roles[0][end_date]"
                                                placeholder="เลือกวันสิ้นสุด"
                                                readonly
                                                required
                                                data-date-role="end"
                                                class="fte-shared-datepicker w-full border rounded-lg px-3 py-2 pr-10 bg-white cursor-pointer"
                                            >
                                            <button
                                                type="button"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
                                                aria-label="เปิดปฏิทินวันสิ้นสุด"
                                            >
                                                📅
                                            </button>
                                        </div>
                                    </div>
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

                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
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

                                    <label>
                                        <span class="block text-sm font-semibold mb-1">
                                            รายละเอียดงาน <span class="text-red-500">*</span>
                                        </span>
                                        <select name="fte_roles[__i__][task_detail]" class="w-full border p-2 rounded"
                                            required>
                                            <option value="">เลือกรายละเอียดงาน</option>
                                            @foreach ($taskDetails as $taskDetail)
                                                <option value="{{ $taskDetail }}">{{ $taskDetail }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <div class="w-full">
                                        <label class="block text-sm text-gray-700 mb-1">
                                            วันเริ่มต้นภาระงาน <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                type="text"
                                                name="fte_roles[__i__][start_date]"
                                                placeholder="เลือกวันเริ่มต้น"
                                                readonly
                                                required
                                                data-date-role="start"
                                                class="fte-shared-datepicker w-full border rounded-lg px-3 py-2 pr-10 bg-white cursor-pointer"
                                            >
                                            <button
                                                type="button"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
                                                aria-label="เปิดปฏิทินวันเริ่มต้น"
                                            >
                                                📅
                                            </button>
                                        </div>
                                    </div>

                                    <div class="w-full">
                                        <label class="block text-sm text-gray-700 mb-1">
                                            วันสิ้นสุดภาระงาน <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                type="text"
                                                name="fte_roles[__i__][end_date]"
                                                placeholder="เลือกวันสิ้นสุด"
                                                readonly
                                                required
                                                data-date-role="end"
                                                class="fte-shared-datepicker w-full border rounded-lg px-3 py-2 pr-10 bg-white cursor-pointer"
                                            >
                                            <button
                                                type="button"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
                                                aria-label="เปิดปฏิทินวันสิ้นสุด"
                                            >
                                                📅
                                            </button>
                                        </div>
                                    </div>
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

            <input type="text" id="fteSharedDatepicker" class="hidden" aria-hidden="true" tabindex="-1">


            <hr class="my-6 border-t-2 border-gray-300">
            <!-- E: ผลงานวิจัย (Output) -->
            <h3 id="section-g" class="edit-section-target font-semibold text-lg text-blue-700 mb-2">G: ผลงานวิจัย (Outputs)</h3>

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

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label>
                                <span class="text-sm">ประเภทของผลงาน <span class="text-red-500">*</span></span>
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
                                    <option>EA115 Capacity Building / Knowledge Sharing</option>
                                    <option>EA116 Research Project</option>
                                    <option>EA117 MOU</option>
                                    <option>EA118 Award and Recognition</option>
                                    <option>EA119 Textbook / Book chapter</option>
                                    <option>EA120 Manual / Instruction / Guideline</option>
                                </select>
                            </label>

                            <label>
                                <span class="text-sm">รายละเอียด / หมายเหตุ / LINK</span>
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

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label>
                                <span class="text-sm">ประเภทของผลงาน <span class="text-red-500">*</span></span>
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
                                    <option>EA115 Capacity Building / Knowledge Sharing</option>
                                    <option>EA116 Research Project</option>
                                    <option>EA117 MOU</option>
                                    <option>EA118 Award and Recognition</option>
                                    <option>EA119 Textbook / Book chapter</option>
                                    <option>EA120 Manual / Instruction / Guideline</option>
                                </select>
                            </label>

                            <label>
                                <span class="text-sm">รายละเอียด / หมายเหตุ / LINK</span>
                                <input name="outputs[__i__][project_output]" class="w-full border p-2 rounded">
                            </label>

                            <x-date-be name="outputs[__i__][year]" label="วันที่ publish" required />
                        </div>

                        <div class="special-mount mt-3"></div>
                    </div>
                </x-slot:template>

            </x-repeatable-row>





            <hr class="my-6 border-t-2 border-gray-300">
            <!-- I: ยุทธศาสตร์ Siriraj Health Policy -->
            <h3 id="section-j" class="edit-section-target font-semibold text-lg text-blue-700 mt-6 mb-2">I: ยุทธศาสตร์ Siriraj Health Policy</h3>
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

        .edit-section-nav {
            position: fixed;
            left: 4px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 55;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.45rem;
            padding: 0.55rem;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
            backdrop-filter: blur(8px);
        }

        .edit-section-nav::before {
            content: '';
            position: absolute;
            top: 0.85rem;
            bottom: 0.85rem;
            left: 1.3rem;
            width: 2px;
            background: linear-gradient(180deg, #bfdbfe 0%, #cbd5e1 100%);
            border-radius: 999px;
            z-index: 0;
        }

        .edit-section-dot {
            position: relative;
            z-index: 1;
            width: auto;
            min-width: 38px;
            height: 38px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #0f172a;
            font-weight: 800;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            padding: 0 0.46rem;
            overflow: hidden;
            white-space: nowrap;
            max-width: 38px;
            transition: max-width 0.2s ease, padding 0.2s ease, transform 0.16s ease, border-color 0.16s ease, background-color 0.16s ease, box-shadow 0.16s ease, color 0.16s ease;
        }

        .edit-section-dot:hover,
        .edit-section-dot:focus-visible {
            outline: none;
            transform: translateY(-1px);
            border-color: #60a5fa;
            background: #eff6ff;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.18);
        }

        .edit-section-dot.is-active {
            border-color: #2563eb;
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.28);
        }

        .edit-section-dot__short {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            border-radius: 9px;
            background: #ffffff;
            border: 1px solid rgba(148, 163, 184, 0.65);
            font-size: 0.78rem;
            font-weight: 700;
            color: #0f172a;
        }

        .edit-section-dot__full {
            margin-left: 0.5rem;
            font-size: 0.78rem;
            font-weight: 700;
            opacity: 0;
            transform: translateX(-6px);
            transition: opacity 0.14s ease, transform 0.14s ease;
        }

        .edit-section-nav.is-expanded .edit-section-dot {
            width: 270px;
            max-width: 270px;
            padding: 0 0.62rem;
        }

        .edit-section-nav.is-expanded .edit-section-dot__full {
            opacity: 1;
            transform: translateX(0);
        }

        .edit-section-dot.is-active .edit-section-dot__short {
            border-color: rgba(191, 219, 254, 0.9);
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
        }

        .edit-section-target {
            scroll-margin-top: 88px;
        }

        #projectForm {
            display: block;
        }

        #projectForm .project-zone-card {
            position: relative;
            margin: 1rem 0 1.15rem;
            border: 1px solid #dbe5f0;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 1rem 1rem 1.1rem;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        #projectForm .project-zone-card.project-zone-card--alt {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        #projectForm .project-zone-title {
            margin: 0 0 0.9rem !important;
            padding: 0.55rem 0.75rem;
            border: 1px solid #dbeafe;
            border-radius: 0.8rem;
            background: #eff6ff;
            color: #1d4ed8 !important;
            font-weight: 800;
            line-height: 1.25;
        }

        #projectForm .project-zone-card > :last-child {
            margin-bottom: 0 !important;
        }

        @media (max-width: 1280px) {
            .edit-section-nav {
                display: none;
            }
        }
    </style>

    <script>
        (() => {
            const form = document.getElementById('projectForm');
            if (!form || form.dataset.zoneReady === '1') return;

            const topLevelHeadings = Array.from(form.children).filter((el) =>
                el instanceof HTMLElement && el.matches('h3.edit-section-target')
            );

            if (!topLevelHeadings.length) return;

            topLevelHeadings.forEach((heading, index) => {
                const zone = document.createElement('section');
                zone.className = `project-zone-card${index % 2 === 1 ? ' project-zone-card--alt' : ''}`;
                zone.dataset.zoneId = heading.id || `zone-${index + 1}`;
                form.insertBefore(zone, heading);
                zone.appendChild(heading);
                heading.classList.add('project-zone-title');

                let cursor = zone.nextSibling;
                while (cursor) {
                    const next = cursor.nextSibling;
                    if (cursor instanceof HTMLElement && cursor.matches('h3.edit-section-target')) {
                        break;
                    }
                    zone.appendChild(cursor);
                    cursor = next;
                }
            });

            form.querySelectorAll('h3.edit-section-target').forEach((heading) => {
                heading.classList.add('project-zone-title');
            });

            form.dataset.zoneReady = '1';
        })();
    </script>

    <script>
        (() => {
            const navDots = Array.from(document.querySelectorAll('.edit-section-dot[data-nav-target]'));
            if (!navDots.length) return;

            const quickNav = document.querySelector('.edit-section-nav');
            const sectionById = new Map();

            const expandQuickNav = () => quickNav?.classList.add('is-expanded');
            const collapseQuickNav = () => quickNav?.classList.remove('is-expanded');

            quickNav?.addEventListener('mouseenter', expandQuickNav);
            quickNav?.addEventListener('mouseleave', collapseQuickNav);
            quickNav?.addEventListener('focusin', expandQuickNav);
            quickNav?.addEventListener('focusout', (event) => {
                if (!quickNav.contains(event.relatedTarget)) collapseQuickNav();
            });

            navDots.forEach((dot) => {
                const targetId = dot.dataset.navTarget || '';
                const targetEl = document.getElementById(targetId);
                if (targetEl) {
                    sectionById.set(targetId, targetEl);
                }

                dot.addEventListener('click', () => {
                    if (!targetEl) return;
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    dot.blur();
                    collapseQuickNav();
                });
            });

            const updateActiveDot = () => {
                let activeId = '';
                let bestTop = Number.POSITIVE_INFINITY;

                sectionById.forEach((el, id) => {
                    const rect = el.getBoundingClientRect();
                    const delta = Math.abs(rect.top - 120);
                    if (rect.top <= 180 && delta < bestTop) {
                        bestTop = delta;
                        activeId = id;
                    }
                });

                if (!activeId) {
                    const firstVisible = Array.from(sectionById.entries())
                        .find(([, el]) => el.getBoundingClientRect().top > 0);
                    activeId = firstVisible ? firstVisible[0] : Array.from(sectionById.keys())[0] || '';
                }

                navDots.forEach((dot) => {
                    dot.classList.toggle('is-active', dot.dataset.navTarget === activeId);
                });
            };

            window.addEventListener('scroll', updateActiveDot, { passive: true });
            window.addEventListener('resize', updateActiveDot);
            window.setTimeout(updateActiveDot, 60);
            updateActiveDot();
        })();

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
            const notice = document.getElementById('budgetInputNotice');
            if (!input) return;

            input.addEventListener('input', () => {
                const rawValue = input.value.replace(/,/g, '');
                const hasInvalidChars = /[^\d.]/.test(rawValue);
                let value = rawValue.replace(/[^\d.]/g, '');
                const parts = value.split('.');
                if (parts.length > 2) value = parts[0] + '.' + parts.slice(1).join('');
                let [intPart, decimalPart] = value.split('.');
                intPart = (intPart || '').replace(/^0+(?=\d)/, '');
                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                input.value = decimalPart !== undefined ? `${intPart}.${decimalPart}` : intPart;
                notice?.classList.toggle('hidden', !hasInvalidChars);
            });

            input.form?.addEventListener('submit', () => {
                input.value = input.value.replace(/,/g, '');
            });
        }
        // ✅ REMOVE & REINDEX
        function confirmProjectDesktopRemoval(callback) {
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
            if (window.confirm('ยืนยันการลบรายการนี้หรือไม่?')) callback();
        }

        function removeRow(id, type) {
            confirmProjectDesktopRemoval(() => {
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
            reindexOutput();
            const container = document.getElementById('output-container');
            if (!container) return;

            container.addEventListener('change', (e) => {
                if (e.target && e.target.classList.contains('output-type')) {
                    handleTypeChange(e.target);
                }
            });

            bindProjectUserLookup(document);
            initOutputAuthorRows(document);
            initAwardRecipientRows(document);
            initBookAuthorRows(document);
            bindPiPersonControl(document);
            initCopiRows(document);
            initCoresearchRows(document);

            const coresearchContainer = document.getElementById('coresearch-container');
            if (coresearchContainer) {
                new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        mutation.addedNodes.forEach((node) => {
                            if (node instanceof HTMLElement && node.matches('.coresearch-row')) {
                                bindCoresearchRow(node);
                            }
                        });
                    });
                }).observe(coresearchContainer, {
                    childList: true,
                });
            }
        });



        function handleTypeChange(selectEl) {
            const row = selectEl.closest('.output-row');
            const mount = row.querySelector('.special-mount');
            if (!row || !mount) return;

            // เคลียร์ของเดิม (EA101/EA103/EA118/EA119)
            mount.innerHTML = '';

            const idx = parseInt(row.getAttribute('data-index') || '0', 10);
            const val = selectEl.value || '';

            if (val.startsWith('EA101')) {
                mount.appendChild(renderEA101Section(idx));
            } else if (val.startsWith('EA103')) {
                mount.appendChild(renderEA103Section(idx));
            } else if (val.startsWith('EA118')) {
                mount.appendChild(renderEA118Section(idx));
            } else if (val.startsWith('EA119')) {
                mount.appendChild(renderEA119Section(idx));
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
        <select name="outputs[${idx}][quartile]" class="w-full border p-2 rounded">
          <option value="">เลือก Quartile</option>
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
      </div>
      <div class="authors-container space-y-2" data-auth-index="0"></div>
      <div class="flex justify-center">
        <button type="button" class="px-3 py-1 rounded bg-blue-600 text-white" onclick="addAuthorRow(this)">+ เพิ่มผู้เขียน</button>
      </div>
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
            row.className = 'author-row grid grid-cols-1 md:grid-cols-12 gap-3 items-end';
            const userOptions = (Array.isArray(window.projectUserDirectory) ? window.projectUserDirectory : [])
                .map((user) => `<option value="${String(user.id)}">${String(user.research_display_name || '').replace(/"/g, '&quot;')}</option>`)
                .join('');
            row.innerHTML = `
    <label class="w-full md:col-span-3">
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

    <label class="w-full md:col-span-3">
      <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก</span>
      <select name="outputs[${outputIdx}][authors][${authorIdx}][person_type]" data-output-author-person-type class="w-full border p-2 rounded" required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกประเภทผู้เขียน')"
                                    oninput="this.setCustomValidity('')">
        <option value="">กรุณาเลือก</option>
        <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
        <option value="external">คนนอก</option>
      </select>
    </label>

    <div class="w-full md:col-span-5">
      <span class="block text-sm text-gray-700 mb-1">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)</span>
      <input type="hidden" name="outputs[${outputIdx}][authors][${authorIdx}][user_id]" data-user-id-hidden>
      <input type="hidden" name="outputs[${outputIdx}][authors][${authorIdx}][name]" data-output-author-name-hidden>
      <div data-output-author-manual-wrap>
        <input list="outputAuthorNames" type="text" data-output-author-manual-name
             class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
             placeholder="เลือกประเภทผู้เขียนก่อน"
                                    oninvalid="this.setCustomValidity('กรุณาระบุชื่อผู้เขียน')"
                                    oninput="this.setCustomValidity('')">
      </div>
      <div data-output-author-user-wrap class="hidden" style="display:none;">
        <select data-output-author-user-select class="w-full border p-2 rounded">
          <option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>
          ${userOptions}
        </select>
      </div>
    </div>

    <div class="flex items-end md:col-span-1">
      <button type="button" class="w-full md:w-auto px-3 py-2 rounded border text-red-600 border-red-300 whitespace-nowrap" onclick="removeAuthorRow(this)">ลบผู้เขียน</button>
    </div>
  `;
            bindOutputAuthorRow(row);
            return row;
        }

        function removeAuthorRow(btnEl) {
            confirmProjectDesktopRemoval(() => {
                const row = btnEl.closest('.author-row');
                if (row) row.remove();
            });
        }

        function renderEA118Section(idx) {
            const wrap = document.createElement('div');
            wrap.className = 'ea118-section space-y-3 border rounded-xl p-4 bg-violet-50/60';
            wrap.innerHTML = `
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <span class="font-semibold text-violet-800">ผู้ได้รับรางวัล (Award Recipients)</span>
      </div>
      <div class="award-recipients-container space-y-2" data-award-index="0"></div>
      <div class="flex justify-center">
        <button type="button" class="px-3 py-1 rounded bg-violet-600 text-white" onclick="addAwardRecipientRow(this)">+ เพิ่มผู้ได้รับรางวัล</button>
      </div>
    </div>
  `;

            const recipientsWrap = wrap.querySelector('.award-recipients-container');
            recipientsWrap.appendChild(buildAwardRecipientRow(idx, 0));

            return wrap;
        }

        function addAwardRecipientRow(btnEl) {
            const section = btnEl.closest('.ea118-section');
            const outputRow = btnEl.closest('.output-row');
            const idx = parseInt(outputRow.getAttribute('data-index') || '0', 10);

            const recipientsWrap = section.querySelector('.award-recipients-container');
            let aIdx = parseInt(recipientsWrap.getAttribute('data-award-index') || '0', 10);
            aIdx += 1;
            recipientsWrap.setAttribute('data-award-index', aIdx);

            recipientsWrap.appendChild(buildAwardRecipientRow(idx, aIdx));
        }

        function buildAwardRecipientRow(outputIdx, authorIdx) {
            const row = document.createElement('div');
            row.className = 'award-recipient-row grid grid-cols-1 md:grid-cols-12 gap-3 items-end';
            const userOptions = (Array.isArray(window.projectUserDirectory) ? window.projectUserDirectory : [])
                .map((user) => `<option value="${String(user.id)}">${String(user.full_name || '').replace(/"/g, '&quot;')}</option>`)
                .join('');
            row.innerHTML = `
    <input type="hidden" name="outputs[${outputIdx}][award_recipients][${authorIdx}][id]" value="">
    <label class="w-full md:col-span-3">
      <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก</span>
      <select name="outputs[${outputIdx}][award_recipients][${authorIdx}][person_type]" data-award-recipient-person-type class="w-full border p-2 rounded" required>
        <option value="">กรุณาเลือก</option>
        <option value="unit">หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
        <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
        <option value="external">คนนอก</option>
      </select>
    </label>
    <div class="w-full md:col-span-8">
      <span class="block text-sm text-gray-700 mb-1">ชื่อผู้ได้รับรางวัล</span>
      <input type="hidden" name="outputs[${outputIdx}][award_recipients][${authorIdx}][user_id]" data-user-id-hidden>
      <input type="hidden" name="outputs[${outputIdx}][award_recipients][${authorIdx}][name]" data-award-recipient-name-hidden>
      <div data-award-recipient-manual-wrap>
        <input list="awardRecipientNames" type="text" data-award-recipient-manual-name
             class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
             placeholder="เลือกประเภทผู้ได้รับรางวัลก่อน">
      </div>
      <div data-award-recipient-user-wrap class="hidden" style="display:none;">
        <select data-award-recipient-user-select class="w-full border p-2 rounded">
          <option value="">พิมพ์เพื่อค้นหาชื่อผู้ได้รับรางวัล</option>
          ${userOptions}
        </select>
      </div>
    </div>
    <div class="flex items-end md:col-span-1">
      <button type="button" class="px-3 py-2 rounded border text-red-600 border-red-300" onclick="removeAwardRecipientRow(this)">ลบรายชื่อ</button>
    </div>
  `;
            bindAwardRecipientRow(row);
            return row;
        }

        function removeAwardRecipientRow(btnEl) {
            confirmProjectDesktopRemoval(() => {
                const row = btnEl.closest('.award-recipient-row');
                if (row) row.remove();
            });
        }

        /** ===== EA119 Section (Textbook / Book chapter) ===== */
        function renderEA119Section(idx) {
            const wrap = document.createElement('div');
            wrap.className = 'ea119-section space-y-3 border rounded-xl p-4 bg-emerald-50/60';
            wrap.innerHTML = `
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">ชื่อหนังสือ</span>
        <input name="outputs[${idx}][book_title]" type="text" class="w-full border p-2 rounded" placeholder="เช่น Textbook of Public Health" required
               oninvalid="this.setCustomValidity('กรุณากรอกชื่อหนังสือ')" oninput="this.setCustomValidity('')">
      </label>
      <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">ชื่อสำนักพิมพ์</span>
        <input name="outputs[${idx}][publisher_name]" type="text" class="w-full border p-2 rounded" placeholder="เช่น Elsevier" required
               oninvalid="this.setCustomValidity('กรุณากรอกชื่อสำนักพิมพ์')" oninput="this.setCustomValidity('')">
      </label>
      <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">ชื่อบท / เลขหน้า</span>
        <input name="outputs[${idx}][chapter_pages]" type="text" class="w-full border p-2 rounded" placeholder="เช่น Chapter 2, pp. 45-66" required
               oninvalid="this.setCustomValidity('กรุณากรอกชื่อบท / เลขหน้า')" oninput="this.setCustomValidity('')">
      </label>
      <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">เลข ISBN</span>
        <input name="outputs[${idx}][isbn]" type="text" class="w-full border p-2 rounded" placeholder="เช่น 978-3-16-148410-0">
      </label>
    </div>

    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <span class="font-semibold text-emerald-800">รายชื่อผู้เขียน</span>
      </div>
      <div class="book-authors-container space-y-2" data-book-auth-index="0"></div>
      <datalist class="book-author-name-list" id="book-author-name-list-${idx}"></datalist>
      <div class="flex justify-center">
        <button type="button" class="px-3 py-1 rounded bg-emerald-600 text-white" onclick="addBookAuthorRow(this)">+ เพิ่มผู้เขียน</button>
      </div>
    </div>
  `;

            const authorsWrap = wrap.querySelector('.book-authors-container');
            authorsWrap.appendChild(buildBookAuthorRow(idx, 0));
            refreshBookAuthorDatalistInOutput(wrap);
            return wrap;
        }

        function addBookAuthorRow(btnEl) {
            const section = btnEl.closest('.ea119-section');
            const outputRow = btnEl.closest('.output-row');
            const idx = parseInt(outputRow.getAttribute('data-index') || '0', 10);

            const authorsWrap = section.querySelector('.book-authors-container');
            let aIdx = parseInt(authorsWrap.getAttribute('data-book-auth-index') || '0', 10);
            aIdx += 1;
            authorsWrap.setAttribute('data-book-auth-index', aIdx);
            authorsWrap.appendChild(buildBookAuthorRow(idx, aIdx));
            refreshBookAuthorDatalistInOutput(section);
        }

        function buildBookAuthorRow(outputIdx, authorIdx) {
            const row = document.createElement('div');
            row.className = 'book-author-row grid grid-cols-1 md:grid-cols-12 gap-3 items-end';
            const userOptions = (Array.isArray(window.projectUserDirectory) ? window.projectUserDirectory : [])
                .map((user) => `<option value="${String(user.id)}">${String(user.full_name || '').replace(/"/g, '&quot;')}</option>`)
                .join('');
            row.innerHTML = `
    <label class="w-full md:col-span-3">
      <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก</span>
      <select name="outputs[${outputIdx}][book_authors][${authorIdx}][person_type]" data-book-author-person-type class="w-full border p-2 rounded" required>
        <option value="">กรุณาเลือก</option>
        <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
        <option value="external">คนนอก</option>
      </select>
    </label>
    <div class="w-full md:col-span-8">
      <span class="block text-sm text-gray-700 mb-1">ชื่อผู้เขียน</span>
      <input type="hidden" name="outputs[${outputIdx}][book_authors][${authorIdx}][user_id]" data-user-id-hidden>
      <input type="hidden" name="outputs[${outputIdx}][book_authors][${authorIdx}][name]" data-book-author-name-hidden>
      <div data-book-author-manual-wrap>
        <input type="text" data-book-author-manual-name data-book-author-input
             list="book-author-name-list-${outputIdx}"
             class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
             placeholder="เลือกประเภทผู้เขียนก่อน"
             oninput="refreshBookAuthorDatalistInOutput(this)">
      </div>
      <div data-book-author-user-wrap class="hidden" style="display:none;">
        <select data-book-author-user-select class="w-full border p-2 rounded">
          <option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>
          ${userOptions}
        </select>
      </div>
    </div>
    <div class="flex items-end md:col-span-1">
      <button type="button" class="px-3 py-2 rounded border text-red-600 border-red-300" onclick="removeBookAuthorRow(this)">ลบผู้เขียน</button>
    </div>
  `;
            bindBookAuthorRow(row);
            return row;
        }

        function removeBookAuthorRow(btnEl) {
            confirmProjectDesktopRemoval(() => {
                const row = btnEl.closest('.book-author-row');
                const section = btnEl.closest('.ea119-section');
                if (row) row.remove();
                refreshBookAuthorDatalistInOutput(section);
            });
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

            return users.find((user) => {
                if (mode === 'research') {
                    return String(user.research_display_name || '').trim() === value;
                }
                return String(user.full_name || '').trim() === value;
            }) || null;
        }

        function syncProjectUserLookupInput(input) {
            const hidden = input?.closest('label, .author-row, .award-recipient-row')?.querySelector('[data-user-id-hidden]');
            if (!hidden) return;
            const matched = findProjectMatchedUser(input);
            hidden.value = matched ? String(matched.id) : '';
        }

        function bindProjectUserLookup(scope = document) {
            scope.querySelectorAll('input[data-user-lookup]').forEach((input) => {
                if (!input.dataset.userLookup) {
                    input.dataset.userLookup = 'full';
                }
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

            if (!(typeSelect instanceof HTMLSelectElement) ||
                !(manualWrap instanceof HTMLElement) ||
                !(manualInput instanceof HTMLInputElement) ||
                !(userWrap instanceof HTMLElement) ||
                !(userSelect instanceof HTMLSelectElement) ||
                !(userIdInput instanceof HTMLInputElement) ||
                !(nameHidden instanceof HTMLInputElement)) {
                return;
            }

            ensureOutputFullNameTomSelect(userSelect, 'พิมพ์เพื่อค้นหาชื่อผู้ได้รับรางวัล');

            const mode = String(typeSelect.value || '').trim();
            const previousMode = row.dataset.awardRecipientMode || '';
            const modeChanged = previousMode !== '' && previousMode !== mode;
            const tom = userSelect.tomselect || null;
            const matchedUser = Array.isArray(window.projectUserDirectory)
                ? window.projectUserDirectory.find((user) => String(user.full_name || '').trim() === String(nameHidden.value || manualInput.value || '').trim())
                : null;

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
                nameHidden.value = selectedOption && userIdInput.value
                    ? String(selectedOption.textContent || '').trim()
                    : '';

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

            if (!(typeSelect instanceof HTMLSelectElement) ||
                !(manualInput instanceof HTMLInputElement) ||
                !(userSelect instanceof HTMLSelectElement)) {
                return;
            }

            if (typeSelect.dataset.awardRecipientBound !== '1') {
                typeSelect.dataset.awardRecipientBound = '1';
                typeSelect.addEventListener('change', () => syncAwardRecipientRow(row));
            }

            if (manualInput.dataset.awardRecipientBound !== '1') {
                manualInput.dataset.awardRecipientBound = '1';
                ['input', 'change', 'blur'].forEach((eventName) => {
                    manualInput.addEventListener(eventName, () => syncAwardRecipientRow(row));
                });
            }

            if (userSelect.dataset.awardRecipientBound !== '1') {
                userSelect.dataset.awardRecipientBound = '1';
                ['change', 'blur'].forEach((eventName) => {
                    userSelect.addEventListener(eventName, () => syncAwardRecipientRow(row));
                });
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

            if (!(typeSelect instanceof HTMLSelectElement) ||
                !(manualWrap instanceof HTMLElement) ||
                !(manualInput instanceof HTMLInputElement) ||
                !(userWrap instanceof HTMLElement) ||
                !(userSelect instanceof HTMLSelectElement) ||
                !(userIdInput instanceof HTMLInputElement) ||
                !(nameHidden instanceof HTMLInputElement)) {
                return;
            }

            ensureOutputFullNameTomSelect(userSelect, 'พิมพ์เพื่อค้นหาชื่อผู้เขียน');

            const mode = String(typeSelect.value || '').trim();
            const previousMode = row.dataset.bookAuthorMode || '';
            const modeChanged = previousMode !== '' && previousMode !== mode;
            const tom = userSelect.tomselect || null;
            const matchedUser = Array.isArray(window.projectUserDirectory)
                ? window.projectUserDirectory.find((user) => String(user.full_name || '').trim() === String(nameHidden.value || manualInput.value || '').trim())
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
                    if (tom) tom.clear(true);
                    else userSelect.value = '';
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

            if (!(typeSelect instanceof HTMLSelectElement) ||
                !(manualInput instanceof HTMLInputElement) ||
                !(userSelect instanceof HTMLSelectElement)) {
                return;
            }

            if (typeSelect.dataset.bookAuthorBound !== '1') {
                typeSelect.dataset.bookAuthorBound = '1';
                typeSelect.addEventListener('change', () => syncBookAuthorRow(row));
            }

            if (manualInput.dataset.bookAuthorBound !== '1') {
                manualInput.dataset.bookAuthorBound = '1';
                ['input', 'change', 'blur'].forEach((eventName) => {
                    manualInput.addEventListener(eventName, () => syncBookAuthorRow(row));
                });
            }

            if (userSelect.dataset.bookAuthorBound !== '1') {
                userSelect.dataset.bookAuthorBound = '1';
                ['change', 'blur'].forEach((eventName) => {
                    userSelect.addEventListener(eventName, () => syncBookAuthorRow(row));
                });
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
            input.classList.toggle('bg-gray-100', disabled);
            input.classList.toggle('text-gray-400', disabled);
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
                affiliationInput.setAttribute('list', 'piaffiliationList');

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
                affiliationInput.setAttribute('list', 'piaffiliationList');

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
            bindProjectSearchSelectGuards(document);
            initOutputAuthorRows(document);
            initAwardRecipientRows(document);
            initBookAuthorRows(document);
            initConferencePresenterRows(document);
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

            manualWrap?.classList.toggle('hidden', isInternal);
            userWrap?.classList.toggle('hidden', !isInternal);
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
                setDisabledStyle(manualInput, !hasStatus, 'เลือกคนภายนอก / ภายในก่อน', 'ยศ ชื่อ นามสกุล');
                setDisabledStyle(affiliationInput, !hasStatus, 'เลือกคนภายนอก / ภายในก่อน', 'สังกัด');
                if (userSelect.tomselect?.wrapper) {
                    userSelect.tomselect.wrapper.classList.add('opacity-60', 'pointer-events-none');
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
            const statusSelect = row.querySelector('[data-coresearch-unit-status]');
            const manualInput = row.querySelector('[data-coresearch-manual-name]');
            const userSelect = row.querySelector('[data-coresearch-user-select]');

            statusSelect?.addEventListener('change', () => syncCoresearchRow(row));
            manualInput?.addEventListener('input', () => syncCoresearchRow(row));
            userSelect?.addEventListener('change', () => syncCoresearchRow(row));

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
            input.classList.toggle('bg-gray-100', disabled);
            input.classList.toggle('text-gray-400', disabled);
            input.classList.toggle('cursor-not-allowed', disabled);
            if (typeof placeholder === 'string' && 'placeholder' in input) {
                input.placeholder = placeholder;
            }
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

            if (!(typeSelect instanceof HTMLSelectElement) ||
                !(manualWrap instanceof HTMLElement) ||
                !(manualInput instanceof HTMLInputElement) ||
                !(userWrap instanceof HTMLElement) ||
                !(userSelect instanceof HTMLSelectElement) ||
                !(userIdInput instanceof HTMLInputElement) ||
                !(noteHidden instanceof HTMLInputElement)) {
                return;
            }

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
                    if (tom) {
                        tom.clear(true);
                    } else {
                        userSelect.value = '';
                    }
                }

                const selectedOption = userSelect.options[userSelect.selectedIndex] || null;
                userIdInput.value = String(userSelect.value || '').trim();
                noteHidden.value = selectedOption && userIdInput.value
                    ? String(selectedOption.textContent || '').trim()
                    : '';

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
                    if (tom) {
                        tom.clear(true);
                    } else {
                        userSelect.value = '';
                    }
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

            if (!(typeSelect instanceof HTMLSelectElement) ||
                !(manualInput instanceof HTMLInputElement) ||
                !(userSelect instanceof HTMLSelectElement)) {
                return;
            }

            if (typeSelect.dataset.conferencePresenterBound !== '1') {
                typeSelect.dataset.conferencePresenterBound = '1';
                typeSelect.addEventListener('change', () => syncConferencePresenterRow(row));
            }

            if (manualInput.dataset.conferencePresenterBound !== '1') {
                manualInput.dataset.conferencePresenterBound = '1';
                ['input', 'change', 'blur'].forEach((eventName) => {
                    manualInput.addEventListener(eventName, () => syncConferencePresenterRow(row));
                });
            }

            if (userSelect.dataset.conferencePresenterBound !== '1') {
                userSelect.dataset.conferencePresenterBound = '1';
                ['change', 'blur'].forEach((eventName) => {
                    userSelect.addEventListener(eventName, () => syncConferencePresenterRow(row));
                });
            }

            syncConferencePresenterRow(row);
        }

        function initConferencePresenterRows(scope = document) {
            scope.querySelectorAll('.conference-presenter-row').forEach((row) => bindConferencePresenterRow(row));
        }

        window.syncConferencePresenterRow = syncConferencePresenterRow;

        /** ===== EA103 Section (Conference + Presenters) ===== */
        function renderEA103Section(idx) {
            const wrap = document.createElement('div');
            wrap.className = 'ea103-section space-y-3 border rounded-xl p-4 bg-amber-50/60';
            wrap.innerHTML = `
    <input type="hidden" name="outputs[${idx}][conference_id]" value="">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">ชื่อเวที/Conference</span>
        <input name="outputs[${idx}][conference_name]" class="w-full border p-2 rounded" placeholder="เช่น PMAC 2025">
      </label>
      <label class="w-full">
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
      <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">สถานที่</span>
        <input name="outputs[${idx}][conference_location]" class="w-full border p-2 rounded" placeholder="เมือง/ประเทศ">
      </label>
    </div>

    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <span class="font-semibold text-amber-800">รายชื่อผู้นำเสนอ</span>
      </div>
      <div class="conference-presenters-container space-y-2" data-conf-presenter-index="0"></div>
      <div class="flex justify-center">
        <button type="button" class="px-3 py-1 rounded bg-amber-600 text-white" onclick="addConferencePresenterRow(this)">+ เพิ่มผู้นำเสนอ</button>
      </div>
    </div>
  `;

            const presentersWrap = wrap.querySelector('.conference-presenters-container');
            presentersWrap.appendChild(buildConferencePresenterRow(idx, 0));

            return wrap;
        }

        function addConferencePresenterRow(btnEl) {
            const section = btnEl.closest('.ea103-section');
            const outputRow = btnEl.closest('.output-row');
            const idx = parseInt(outputRow.getAttribute('data-index') || '0', 10);

            const presentersWrap = section.querySelector('.conference-presenters-container');
            let pIdx = parseInt(presentersWrap.getAttribute('data-conf-presenter-index') || '0', 10);
            pIdx += 1;
            presentersWrap.setAttribute('data-conf-presenter-index', pIdx);

            const row = buildConferencePresenterRow(idx, pIdx);
            presentersWrap.appendChild(row);
            bindProjectSearchSelectGuards(section);
        }

        function buildConferencePresenterRow(outputIdx, presenterIdx) {
            const row = document.createElement('div');
            row.className = 'conference-presenter-row grid grid-cols-1 md:grid-cols-12 gap-3 items-end';
            const userOptions = (Array.isArray(window.projectUserDirectory) ? window.projectUserDirectory : [])
                .map((user) => `<option value="${String(user.id)}">${String(user.full_name || '').replace(/"/g, '&quot;')}</option>`)
                .join('');

            row.innerHTML = `
    <input type="hidden" name="outputs[${outputIdx}][conference_presenters][${presenterIdx}][id]" value="">
    <input type="hidden" name="outputs[${outputIdx}][conference_presenters][${presenterIdx}][user_id]" value="" data-conference-presenter-user-id>
    <input type="hidden" name="outputs[${outputIdx}][conference_presenters][${presenterIdx}][note]" value="" data-conference-presenter-note-hidden>

    <label class="w-full md:col-span-3">
      <span class="block text-sm text-gray-700 mb-1">คนในหน่วย / คนนอก</span>
      <select name="outputs[${outputIdx}][conference_presenters][${presenterIdx}][person_type]" data-conference-presenter-person-type class="w-full border p-2 rounded" required
        oninvalid="this.setCustomValidity('กรุณาเลือกประเภทผู้นำเสนอ')"
        oninput="this.setCustomValidity('')">
        <option value="">กรุณาเลือก</option>
        <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
        <option value="external">คนนอก</option>
      </select>
    </label>

    <label class="w-full md:col-span-8" data-conference-presenter-manual-wrap>
      <span class="block text-sm text-gray-700 mb-1">ชื่อผู้นำเสนอ</span>
      <input type="text" data-conference-presenter-manual-name class="w-full border p-2 rounded bg-gray-100 text-gray-400 cursor-not-allowed" placeholder="เลือกประเภทผู้นำเสนอก่อน"
        disabled oninvalid="this.setCustomValidity('กรุณาระบุชื่อผู้นำเสนอ')" oninput="this.setCustomValidity('')">
    </label>

    <label class="w-full md:col-span-8 hidden" data-conference-presenter-user-wrap>
      <span class="block text-sm text-gray-700 mb-1">เลือกผู้นำเสนอจากรายชื่อบุคลากร</span>
      <select data-conference-presenter-user-select class="w-full border p-2 rounded" disabled
        oninvalid="this.setCustomValidity('กรุณาเลือกผู้นำเสนอจากรายการ')" oninput="this.setCustomValidity('')">
        <option value="">พิมพ์เพื่อค้นหาชื่อผู้นำเสนอ</option>
        ${userOptions}
      </select>
    </label>

    <div class="w-full md:col-span-1 flex justify-end">
      <button type="button" class="w-full md:w-auto px-3 py-2 rounded border text-red-600 border-red-300 whitespace-nowrap" onclick="removeConferencePresenterRow(this)">ลบ</button>
    </div>
  `;

            bindConferencePresenterRow(row);
            return row;
        }

        function removeConferencePresenterRow(btn) {
            confirmProjectDesktopRemoval(() => {
                btn.closest('.conference-presenter-row')?.remove();
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
                        el.setAttribute('name', old.replace(/^outputs\[[^\]]+\]/, `outputs[${i}]`));
                    });
            });
        }




        // ✅ INITIALIZE
        document.addEventListener('DOMContentLoaded', () => {
            // initThaiDatepickers();
            setupBudgetInput();
            initSharedFteDatepicker();
            hydrateSharedFteDateInputs();

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

        function parseLocalDateInput(raw) {
            const value = String(raw ?? '').trim();
            if (!value) return null;

            let match = value.match(/^(\d{1,2})-(\d{1,2})-(\d{4})$/);
            if (match) {
                const [, day, month, year] = match;
                const date = new Date(Number(year), Number(month) - 1, Number(day));
                return Number.isNaN(date.getTime()) ? null : date;
            }

            match = value.match(/^(\d{1,2})\s+([^\s]+)\s+(\d{4})$/);
            if (match) {
                const [, day, monthText, yearRaw] = match;
                const thaiMonths = {
                    มกราคม: 0,
                    กุมภาพันธ์: 1,
                    มีนาคม: 2,
                    เมษายน: 3,
                    พฤษภาคม: 4,
                    มิถุนายน: 5,
                    กรกฎาคม: 6,
                    สิงหาคม: 7,
                    กันยายน: 8,
                    ตุลาคม: 9,
                    พฤศจิกายน: 10,
                    ธันวาคม: 11,
                };
                const monthIndex = thaiMonths[monthText];
                const rawYear = Number(yearRaw);
                const year = rawYear > 2400 ? rawYear - 543 : rawYear;

                if (Number.isInteger(monthIndex) && Number.isFinite(year)) {
                    const date = new Date(year, monthIndex, Number(day));
                    return Number.isNaN(date.getTime()) ? null : date;
                }

                const parsed = new Date(value);
                return Number.isNaN(parsed.getTime()) ? null : parsed;
            }

            return null;
        }

        function toDmyCE(date) {
            if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
            const dd = String(date.getDate()).padStart(2, '0');
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const yyyy = String(date.getFullYear());
            return `${dd}-${mm}-${yyyy}`;
        }

        function normalizeDateOnly(date) {
            const d = new Date(date);
            d.setHours(0, 0, 0, 0);
            return d;
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

        function hydrateSharedFteDateInputs(scope = document) {
            scope.querySelectorAll('.fte-shared-datepicker').forEach((input) => {
                if (!(input instanceof HTMLInputElement)) return;
                const parsed = parseLocalDateInput(input.dataset.isoValue || input.value);
                if (!parsed) return;
                input.dataset.isoValue = toDmyCE(parsed);
                input.value = formatThaiBuddhistDate(parsed);
            });
        }

        function initSharedFteDatepicker() {
            const sharedInput = document.getElementById('fteSharedDatepicker');
            if (!(sharedInput instanceof HTMLInputElement) || sharedInput._flatpickr) return;

            let activeTarget = null;
            const resolveSharedDateValue = (input) => {
                if (!(input instanceof HTMLInputElement)) return null;
                return parseLocalDateInput(input.dataset.isoValue || input.value || '');
            };

            const picker = flatpickr(sharedInput, {
                locale: 'th',
                dateFormat: 'd-m-Y',
                prevArrow: '',
                nextArrow: '',
                disableMobile: true,
                clickOpens: false,
                allowInput: false,
                onReady(selectedDates, dateStr, instance) {
                    if (typeof toBuddhist === 'function') toBuddhist(selectedDates, dateStr, instance);
                },
                onValueUpdate: typeof toBuddhist === 'function' ? toBuddhist : undefined,
                onMonthChange: typeof toBuddhist === 'function' ? toBuddhist : undefined,
                onYearChange: typeof toBuddhist === 'function' ? toBuddhist : undefined,
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
                const currentDate = resolveSharedDateValue(target);
                const minDate = target.dataset.dateRole === 'end' ? resolveSharedDateValue(startInput) : null;
                const maxDate = target.dataset.dateRole === 'start' ? resolveSharedDateValue(endInput) : null;

                picker.set('minDate', minDate ? normalizeDateOnly(minDate) : null);
                picker.set('maxDate', maxDate ? normalizeDateOnly(maxDate) : null);
                picker.set('disable', [
                    ...(minDate ? [date => normalizeDateOnly(date) < normalizeDateOnly(minDate)] : []),
                    ...(maxDate ? [date => normalizeDateOnly(date) > normalizeDateOnly(maxDate)] : []),
                ]);
                picker.setDate(currentDate || null, false);
                picker.open();
            };

            document.addEventListener('click', (event) => {
                const pickerTarget = event.target.closest('.fte-shared-datepicker');
                if (pickerTarget instanceof HTMLInputElement) {
                    openSharedPicker(pickerTarget);
                    return;
                }

                const button = event.target.closest('.fte-row button[aria-label^="เปิดปฏิทิน"]');
                const target = button?.closest('.relative')?.querySelector('.fte-shared-datepicker');
                if (target instanceof HTMLInputElement) {
                    openSharedPicker(target);
                }
            });

            document.addEventListener('focusin', (event) => {
                const target = event.target.closest('.fte-shared-datepicker');
                if (target instanceof HTMLInputElement) {
                    openSharedPicker(target);
                }
            });

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || form.id !== 'projectForm') return;
                form.querySelectorAll('.fte-shared-datepicker').forEach((input) => {
                    if (!(input instanceof HTMLInputElement)) return;
                    const isoValue = input.dataset.isoValue || '';
                    if (isoValue) input.value = isoValue;
                });
            }, true);

            window.addEventListener('pageshow', () => {
                hydrateSharedFteDateInputs();
            });
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
        const piNameSelect = document.querySelector('select#pi_name');
        if (piNameSelect) {
            window.ensureProjectSearchDropdown?.(piNameSelect, {
                placeholder: 'เลือก PI',
                searchPlaceholder: 'พิมพ์เพื่อค้นหา PI',
            });
        }

        initCoresearchRows(document);
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

            const syncProjectRoleDraftFields = () => {
                window.reindexOutput?.();
                document.querySelectorAll('.author-row').forEach((row) => window.syncOutputAuthorRow?.(row));
                document.querySelectorAll('.award-recipient-row').forEach((row) => window.syncAwardRecipientRow?.(row));
                document.querySelectorAll('.book-author-row').forEach((row) => window.syncBookAuthorRow?.(row));
                window.syncPiPersonFields?.(document);
                document.querySelectorAll('.copi-row').forEach((row) => window.syncCopiRow?.(row));
                document.querySelectorAll('.coresearch-row').forEach((row) => window.syncCoresearchRow?.(row));
                document.querySelectorAll('.conference-presenter-row').forEach((row) => window.syncConferencePresenterRow?.(row));
            };

            const assignCollectedValue = (data, el, nameOverride = null) => {
                const name = nameOverride || el.name;
                if (!name) return;

                if (el.type === 'checkbox') data[name] = el.checked;
                else if (el.type === 'radio') {
                    if (el.checked) data[name] = el.value;
                } else if (el.classList?.contains('fte-shared-datepicker')) {
                    const parsed = parseLocalDateInput(el.dataset.isoValue || el.value);
                    data[name] = parsed ? toDmyCE(parsed) : '';
                } else {
                    data[name] = el.value;
                }
            };

            const collectNormalizedOutputData = (data) => {
                window.reindexOutput?.();
                FORM.querySelectorAll('#output-container .output-row').forEach((row, rowIndex) => {
                    row.querySelectorAll('input[name^="outputs["], select[name^="outputs["], textarea[name^="outputs["]').forEach((el) => {
                        if (!el.name) return;
                        const normalizedName = el.name.replace(/^outputs\[[^\]]+\]/, `outputs[${rowIndex}]`);
                        assignCollectedValue(data, el, normalizedName);
                    });
                });
            };

            const getOutputRelativeName = (name) => {
                const match = String(name || '').match(/^outputs\[[^\]]+\]\[(.+)\]$/);
                return match ? match[1] : '';
            };

            const collectOutputRows = () => {
                window.reindexOutput?.();
                return Array.from(FORM.querySelectorAll('#output-container .output-row')).map((row) => {
                    const rowData = {};
                    row.querySelectorAll('input[name^="outputs["], select[name^="outputs["], textarea[name^="outputs["]').forEach((el) => {
                        if (!el.name) return;
                        const relativeName = getOutputRelativeName(el.name);
                        if (!relativeName) return;
                        assignCollectedValue(rowData, el, relativeName);
                    });
                    return rowData;
                });
            };

            const flattenOutputRows = (rows) => {
                const flat = {};
                (Array.isArray(rows) ? rows : []).forEach((rowData, rowIndex) => {
                    Object.entries(rowData || {}).forEach(([relativeName, value]) => {
                        flat[`outputs[${rowIndex}][${relativeName}]`] = value;
                    });
                });
                return flat;
            };

            const collectForm = () => {
                syncProjectRoleDraftFields();
                const data = {};
                FORM.querySelectorAll('input, select, textarea').forEach(el => {
                    if (!el.name) return;
                    if (el.name.startsWith('outputs[')) return;
                    assignCollectedValue(data, el);
                });
                collectNormalizedOutputData(data);
                return data;
            };

            const save = () => {
                const data = collectForm();
                localStorage.setItem(FORM_KEY, JSON.stringify({
                    ts: now(),
                    data,
                    outputRows: collectOutputRows()
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
            window.addEventListener('pagehide', save);
            window.addEventListener('beforeunload', save);

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
                if (maxIdx < 0) return;
                const btn = document.querySelector(`[data-repeatable-add="${id}"]`);
                const wrap = document.querySelector(`[data-repeatable][data-id="${id}"]`);
                const containerId = wrap?.dataset?.container || '';
                const container = containerId ? document.getElementById(containerId) : null;
                const existingCount = container?.querySelectorAll('[data-repeatable-row]').length ?? 0;
                const targetCount = maxIdx + 1;
                const rowsToAdd = Math.max(0, targetCount - existingCount);
                if (!btn || rowsToAdd < 1) return;
                for (let i = 0; i < rowsToAdd; i++) btn.click();
            };

            const syncOutputIndexes = () => {
                document.querySelectorAll('[data-repeatable][data-id="output"] [data-repeatable-row]')
                    .forEach((row, i) => row.setAttribute('data-index', i));
            };

            const collectOutputMeta = (data) => {
                const typeMap = {};
                const authorMax = {};
                const awardRecipientMax = {};
                const bookAuthorMax = {};
                const confPresenterMax = {};

                Object.keys(data).forEach(k => {
                    let m = k.match(/^outputs\[(\d+)\]\[type\]/);
                    if (m) typeMap[parseInt(m[1], 10)] = data[k];

                    m = k.match(/^outputs\[(\d+)\]\[authors\]\[(\d+)\]/);
                    if (m) {
                        const oIdx = parseInt(m[1], 10);
                        const aIdx = parseInt(m[2], 10);
                        authorMax[oIdx] = Math.max(authorMax[oIdx] ?? -1, aIdx);
                    }

                    m = k.match(/^outputs\[(\d+)\]\[award_recipients\]\[(\d+)\]/);
                    if (m) {
                        const oIdx = parseInt(m[1], 10);
                        const aIdx = parseInt(m[2], 10);
                        awardRecipientMax[oIdx] = Math.max(awardRecipientMax[oIdx] ?? -1, aIdx);
                    }

                    m = k.match(/^outputs\[(\d+)\]\[book_authors\]\[(\d+)\]/);
                    if (m) {
                        const oIdx = parseInt(m[1], 10);
                        const bIdx = parseInt(m[2], 10);
                        bookAuthorMax[oIdx] = Math.max(bookAuthorMax[oIdx] ?? -1, bIdx);
                    }

                    m = k.match(/^outputs\[(\d+)\]\[conference_presenters\]\[(\d+)\]/);
                    if (m) {
                        const oIdx = parseInt(m[1], 10);
                        const pIdx = parseInt(m[2], 10);
                        confPresenterMax[oIdx] = Math.max(confPresenterMax[oIdx] ?? -1, pIdx);
                    }
                });

                return { typeMap, authorMax, awardRecipientMax, bookAuthorMax, confPresenterMax };
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

                Object.entries(meta.awardRecipientMax).forEach(([idxStr, maxIdx]) => {
                    if (maxIdx < 1) return;
                    const idx = parseInt(idxStr, 10);
                    const row = FORM.querySelector(`[name="outputs[${idx}][title]"]`)?.closest('.output-row');
                    const btn = row?.querySelector('.ea118-section button[onclick*="addAwardRecipientRow"]');
                    if (!btn || typeof addAwardRecipientRow !== 'function') return;
                    for (let i = 0; i < maxIdx; i++) addAwardRecipientRow(btn);
                });

                Object.entries(meta.bookAuthorMax).forEach(([idxStr, maxIdx]) => {
                    if (maxIdx < 1) return;
                    const idx = parseInt(idxStr, 10);
                    const row = FORM.querySelector(`[name="outputs[${idx}][title]"]`)?.closest('.output-row');
                    const btn = row?.querySelector('.ea119-section button[onclick*="addBookAuthorRow"]');
                    if (!btn || typeof addBookAuthorRow !== 'function') return;
                    for (let i = 0; i < maxIdx; i++) addBookAuthorRow(btn);
                });

                Object.entries(meta.confPresenterMax).forEach(([idxStr, maxIdx]) => {
                    if (maxIdx < 1) return;
                    const idx = parseInt(idxStr, 10);
                    const row = FORM.querySelector(`[name="outputs[${idx}][title]"]`)?.closest('.output-row');
                    const btn = row?.querySelector('.ea103-section button[onclick*="addConferencePresenterRow"]');
                    if (!btn || typeof addConferencePresenterRow !== 'function') return;
                    for (let i = 0; i < maxIdx; i++) addConferencePresenterRow(btn);
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

            const applyFieldValue = (el, name, value, options = {}) => {
                if (!el) return;
                const skipChangeEvent = options.skipChangeEvent === true;

                if (el.type === 'checkbox') {
                    el.checked = !!value;
                } else if (el.type === 'radio') {
                    const r = FORM.querySelector(
                        `[name="${CSS.escape(name)}"][value="${CSS.escape(value)}"]`
                    );
                    if (r) r.checked = true;
                    return;
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
                } else if (el.classList?.contains('fte-shared-datepicker')) {
                    const parsed = parseLocalDateValue(value);
                    const normalized = parsed ? `${String(parsed.getDate()).padStart(2, '0')}-${String(parsed.getMonth() + 1).padStart(2, '0')}-${parsed.getFullYear()}` : String(value ?? '').trim();
                    el.dataset.isoValue = normalized;
                    el.value = normalized;
                } else {
                    el.value = value;
                }

                if (!skipChangeEvent) {
                    el.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }
            };

            const applyForm = (data) => {
                Object.entries(data).forEach(([name, value]) => {
                    const el = FORM.querySelector(`[name="${CSS.escape(name)}"]`);
                    if (!el) return;
                    applyFieldValue(el, name, value);
                });
            };

            const applyOutputRows = (rows) => {
                const outputEls = Array.from(FORM.querySelectorAll('#output-container .output-row'));

                rows.forEach((rowData, rowIndex) => {
                    const row = outputEls[rowIndex];
                    if (!row || !rowData || typeof rowData !== 'object') return;

                    const typeName = `outputs[${rowIndex}][type]`;
                    const typeSelect = row.querySelector(`[name="${CSS.escape(typeName)}"]`);
                    if (typeSelect && Object.prototype.hasOwnProperty.call(rowData, 'type')) {
                        applyFieldValue(typeSelect, typeName, rowData.type, {
                            skipChangeEvent: true
                        });
                    }

                    Object.entries(rowData).forEach(([relativeName, value]) => {
                        if (relativeName === 'type') return;
                        const fullName = `outputs[${rowIndex}][${relativeName}]`;
                        const field = row.querySelector(`[name="${CSS.escape(fullName)}"]`) ||
                            FORM.querySelector(`[name="${CSS.escape(fullName)}"]`);
                        if (!field) return;
                        applyFieldValue(field, fullName, value);
                    });
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
                const outputRows = Array.isArray(payload.outputRows) ? payload.outputRows : [];
                const normalizedOutputData = outputRows.length ? flattenOutputRows(outputRows) : data;
                window.reindexOutput?.();
                addRepeatableRows('irb', maxIndex(data, 'irbs'));
                addRepeatableRows('copi', maxIndex(data, 'copis'));
                addRepeatableRows('coresearch', maxIndex(data, 'coresearch'));
                addRepeatableRows('fte', maxIndex(data, 'fte_roles'));
                addRepeatableRows('output', outputRows.length ? outputRows.length - 1 : maxIndex(data, 'outputs'));
                window.reindexOutput?.();
                if (outputRows.length) {
                    ensureOutputSections(normalizedOutputData);
                    applyOutputRows(outputRows);
                    applyForm(Object.fromEntries(
                        Object.entries(data).filter(([name]) => !name.startsWith('outputs['))
                    ));
                } else {
                    ensureOutputSections(normalizedOutputData);
                    applyForm({
                        ...data,
                        ...normalizedOutputData
                    });
                }
                window.reindexOutput?.();
                hydrateSharedFteDateInputs();
                initOutputAuthorRows(document);
                initAwardRecipientRows(document);
                initBookAuthorRows(document);
                initConferencePresenterRows(document);
                bindProjectUserLookup(document);
                bindPiPersonControl(document);
                initCopiRows(document);
                initCoresearchRows(document);
                document.querySelectorAll('.ea119-section').forEach((section) => refreshBookAuthorDatalistInOutput(section));
                syncProjectRoleDraftFields();
                window.reindexFTE?.();
                window.refreshFteUx?.();
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
                const namedFields = Array.from(form.querySelectorAll('input[name], select[name], textarea[name]'))
                    .filter((el) => !el.disabled && !el.closest('template')).length;

                const fteExpected = form.querySelector('#fteExpectedRows');
                const varsExpected = form.querySelector('#formExpectedVars');
                if (fteExpected) fteExpected.value = String(form.querySelectorAll('.fte-row[data-repeatable-row]').length);
                if (varsExpected) varsExpected.value = String(namedFields);
            };

            updateFormMeta();
            form.addEventListener('submit', updateFormMeta, true);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const projectForm = document.getElementById('projectForm');
            document.querySelectorAll('.ea119-section').forEach((section) => refreshBookAuthorDatalistInOutput(section));

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
                            const row = field.closest('[data-repeatable-row], .output-row, .fte-row, .copi-row, .coresearch-row, .irb-row') ||
                                displayTarget.closest('[data-repeatable-row], .output-row, .fte-row, .copi-row, .coresearch-row, .irb-row');
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

                    // 2. ถ้าไม่ผ่าน ให้หยุดส่งข้อมูลและแจ้งเตือน
                    if (!isValid) {
                        window.syncFteTableValidationUi?.({
                            ensureRender: false
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

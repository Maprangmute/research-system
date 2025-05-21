@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-bold mb-6 text-gray-800">✏️ แก้ไขข้อมูลโครงการวิจัย</h2>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
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
                <input name="project_name_th" value="{{ old('project_name_th', $project->project_name_th) }}" required class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">ชื่อโครงการ (English)</span>
                <input name="project_name_en" value="{{ old('project_name_en', $project->project_name_en) }}" class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">ประเภทของโครงการ*</span>
                <input name="project_type" value="{{ old('project_type', $project->project_type) }}" required class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">กลุ่มเนื้อหา (Theme)*</span>
                <input name="theme" value="{{ old('theme', $project->theme) }}" required class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">ลิงก์ Google Drive / Shortcut</span>
                <input name="shortcut_link" value="{{ old('shortcut_link', $project->shortcut_link) }}" class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">ขนาดโครงการ (S/M/L)</span>
                <input name="size" value="{{ old('size', $project->size) }}" class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">DA102 - ประเภทแหล่งทุน</span>
                <input name="funding_type" value="{{ old('funding_type', $project->funding_type) }}" class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">DA103 - ชื่อแหล่งทุน</span>
                <input name="funding_source" value="{{ old('funding_source', $project->funding_source) }}" class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">DA104 - รัฐบาล / เอกชน / มูลนิธิ</span>
                <input name="funding_org_type" value="{{ old('funding_org_type', $project->funding_org_type) }}" class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">DA105 - ชื่อทุน</span>
                <input name="funding_name" value="{{ old('funding_name', $project->funding_name) }}" class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">DA106 - งบประมาณ (บาท)</span>
                <input name="budget" type="number" step="0.01" value="{{ old('budget', $project->budget) }}" class="w-full border p-2 rounded">
            </label>
        </div>

        <h3 class="font-semibold text-lg text-blue-700 mb-2 mt-6">B: ระยะเวลาดำเนินโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">BA000 - Project Planning (Status)</span>
                <input name="planning_status" value="{{ old('planning_status', $project->planning_status) }}" class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">BA001 - วันที่เริ่มเขียน Proposal</span>
                <input name="proposal_date" value="{{ old('proposal_date', $project->proposal_date) }}" type="text" class="thai-datepicker w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">BC101 - วันที่สัญญาอนุมัติ</span>
                <input name="contract_date" value="{{ old('contract_date', $project->contract_date) }}" type="text" class="thai-datepicker w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">BC102 - วันที่โครงการเริ่ม</span>
                <input name="start_date" value="{{ old('start_date', $project->start_date) }}" type="text" class="thai-datepicker w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">วันที่สิ้นสุด</span>
                <input name="end_date" value="{{ old('end_date', $project->end_date) }}" type="text" class="thai-datepicker w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">เดือนเริ่มต้น</span>
                <input name="start_month" value="{{ old('start_month', $project->start_month) }}" type="text" class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">ปีเริ่มต้น</span>
                <input name="start_year" value="{{ old('start_year', $project->start_year) }}" type="text" class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">BC000 - Project Execution (Status)</span>
                <input name="execution_status" value="{{ old('execution_status', $project->execution_status) }}" type="text" class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">BD000 - Review Status</span>
                <input name="review_status" value="{{ old('review_status', $project->review_status) }}" type="text" class="w-full border p-2 rounded">
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">BE000 - Closure Status</span>
                <input name="closure_status" value="{{ old('closure_status', $project->closure_status) }}" type="text" class="w-full border p-2 rounded">
            </label>
        </div>

        {{-- เพิ่ม Sections: R Code, IRB, COPI, FTE, OUTPUT, Strategy ได้ในรูปแบบเดิมที่คุณมี --}}
        {{-- สำหรับแต่ละ array เช่น $project->r_codes, $project->copis, $project->fte_roles ให้ใช้ @foreach loop แสดงค่าที่มีอยู่ --}}
        {{-- ถ้าต้องการให้ผมเติมให้ครบทุก section พร้อม foreach loop → แจ้งได้เลย ผมจะต่อให้ทั้งหมด --}}

        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">💾 บันทึกการเปลี่ยนแปลง</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    flatpickr(".thai-datepicker", {
        locale: "th",
        dateFormat: "d-m-Y",
        altInput: true,
        altFormat: "j F Y",
        onReady: toBuddhist,
        onChange: toBuddhist,
        onMonthChange: toBuddhist,
        onYearChange: toBuddhist,
    });

    function toBuddhist(selectedDates, dateStr, instance) {
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
});
</script>
@endsection

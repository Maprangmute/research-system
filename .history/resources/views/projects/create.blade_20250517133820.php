@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-bold mb-6 text-gray-800">📋 เพิ่มข้อมูลโครงการวิจัย</h2>

    @if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('projects.store') }}" method="POST">
        @csrf

        {{-- A: ข้อมูลโครงการ --}}
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            @foreach ([
                ['ชื่อโครงการ (ภาษาไทย)*', 'project_name_th', true],
                ['ชื่อโครงการ (English)', 'project_name_en', false],
                ['ประเภทของโครงการ*', 'project_type', true],
                ['กลุ่มเนื้อหา (Theme)*', 'theme', true],
                ['ลิงก์ Google Drive / Shortcut', 'shortcut_link', false],
                ['ขนาดโครงการ (S/M/L)', 'size', false],
            ] as [$label, $name, $required])
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">{{ $label }}</span>
                    <input name="{{ $name }}" type="text" class="w-full border p-2 rounded" @if($required) required @endif>
                </label>
            @endforeach
        </div>

        {{-- B: ระยะเวลาดำเนินโครงการ --}}
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">B: ระยะเวลาดำเนินโครงการ</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            @foreach ([
                ['เดือนเริ่มต้น', 'start_month'],
                ['ปีเริ่มต้น (พ.ศ.)', 'start_year'],
                ['เดือนสิ้นสุด', 'end_month'],
                ['ปีสิ้นสุด (พ.ศ.)', 'end_year'],
            ] as [$label, $name])
                <label>
                    <span class="block text-sm text-gray-700 mb-1">{{ $label }}</span>
                    <input name="{{ $name }}" type="text" class="w-full border p-2 rounded">
                </label>
            @endforeach
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <label>สถานะการวางแผน (BA000)
                <input name="planning_status" type="text" class="w-full border p-2 rounded">
            </label>
            <label>วันที่เริ่มเขียน Proposal (BA001)
                <input name="proposal_date" type="text" class="w-full border p-2 rounded datepicker-th">
            </label>
            <label>สถานะการดำเนินการ (BC000)
                <input name="execution_status" type="text" class="w-full border p-2 rounded">
            </label>
            <label>วันที่สัญญาอนุมัติ (BC101)
                <input name="contract_date" type="text" class="w-full border p-2 rounded datepicker-th">
            </label>
            <label>วันที่เริ่มโครงการ (BC102)
                <input name="start_date" type="text" class="w-full border p-2 rounded datepicker-th">
            </label>
            <label>สถานะการส่งข้อเสนอ (BD000)
                <input name="review_status" type="text" class="w-full border p-2 rounded">
            </label>
            <label>สถานะการปิดโครงการ (BE000)
                <input name="closure_status" type="text" class="w-full border p-2 rounded">
            </label>
        </div>

        {{-- C: ข้อมูลทุน --}}
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">C: ข้อมูลทุน</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            @foreach ([
                ['ประเภทแหล่งทุน', 'funding_type'],
                ['ชื่อแหล่งทุน', 'funding_source'],
                ['ประเภทองค์กร (รัฐบาล/เอกชน/มูลนิธิ)', 'funding_org_type'],
                ['ชื่อทุน', 'funding_name'],
                ['งบประมาณ (บาท)', 'budget'],
            ] as [$label, $name])
                <label>
                    <span class="block text-sm text-gray-700 mb-1">{{ $label }}</span>
                    <input name="{{ $name }}" type="{{ $name === 'budget' ? 'number' : 'text' }}" class="w-full border p-2 rounded">
                </label>
            @endforeach
        </div>

        {{-- D: รหัสโครงการ R / IRB --}}
        <hr class="my-6 border-t-2 border-gray-300">
        @include('projects.partials.r_irb')

        {{-- E: FTE --}}
        <hr class="my-6 border-t-2 border-gray-300">
        @include('projects.partials.fte')

        {{-- F: Output --}}
        <hr class="my-6 border-t-2 border-gray-300">
        @include('projects.partials.output')

        {{-- G: ยุทธศาสตร์ --}}
        <hr class="my-6 border-t-2 border-gray-300">
        @include('projects.partials.strategy')

        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">💾 บันทึกข้อมูล</button>
        </div>
    </form>
</div>

<script>
flatpickr(".datepicker-th", {
    locale: 'th',
    dateFormat: "F Y",
});
</script>
@endsection

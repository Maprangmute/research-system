@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vanillajs-datepicker@1.3.4/dist/css/datepicker.min.css">
<script src="https://cdn.jsdelivr.net/npm/vanillajs-datepicker@1.3.4/dist/js/datepicker-full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanillajs-datepicker@1.3.4/dist/js/i18n/datepicker.th.js"></script>

@if(session('success'))
<script>
Swal.fire({
    toast: true,
    position: 'bottom-start',
    icon: 'success',
    title: '{{ session('success') }}',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    showClass: { popup: 'animate__animated animate__fadeInLeft' },
    hideClass: { popup: 'animate__animated animate__fadeOutRight' }
});
</script>
@endif

@if(session('error'))
<script>
Swal.fire({
    toast: true,
    position: 'bottom-end',
    icon: 'error',
    title: 'เกิดข้อผิดพลาด',
    text: '{{ session('error') }}',
    showConfirmButton: false,
    timer: 7000,
    timerProgressBar: true,
    showClass: { popup: 'animate__animated animate__fadeInRightBig' },
    hideClass: { popup: 'animate__animated animate__fadeOutRightBig' }
});
</script>
@endif

<div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-bold mb-6 text-gray-800">📋 เพิ่มข้อมูลโครงการวิจัย</h2>
    <form action="{{ route('projects.store') }}" method="POST">
        @csrf

        <hr class="my-6 border-t-2 border-gray-300">
        <!-- A: ข้อมูลโครงการ -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-input label="ชื่อโครงการ (ภาษาไทย)*" name="project_name_th" required />
            <x-input label="ชื่อโครงการ (English)" name="project_name_en" />
            <x-input label="ประเภทของโครงการ*" name="project_type" required />
            <x-input label="กลุ่มเนื้อหา (Theme)*" name="theme" required />
            <x-input label="ลิงก์ Google Drive / Shortcut" name="shortcut_link" />
            <x-input label="ขนาดโครงการ (S/M/L)" name="size" />
        </div>

        <hr class="my-6 border-t-2 border-gray-300">
        <!-- B: ข้อมูลระยะเวลาดำเนินโครงการ -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2">B: ข้อมูลระยะเวลาดำเนินโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-input label="BA000: สถานะการวางแผน" name="planning_status" />
            <x-input label="BA001: วันที่เริ่มเขียน Proposal" name="proposal_date" class="datepicker-th" />
            <x-input label="BC000: สถานะการดำเนินโครงการ" name="execution_status" />
            <x-input label="BC101: วันที่สัญญาอนุมัติ" name="contract_date" class="datepicker-th" />
            <x-input label="BC102: วันที่โครงการเริ่ม" name="start_date" class="datepicker-th" />
            <x-input label="BD000: สถานะ Review" name="review_status" />
            <x-input label="BE000: สถานะปิดโครงการ" name="closure_status" />
        </div>

        <hr class="my-6 border-t-2 border-gray-300">
        <!-- C: ข้อมูลทุน -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2">C: ข้อมูลทุน</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-input label="DA102: ประเภทแหล่งทุน" name="funding_type" />
            <x-input label="DA103: ชื่อแหล่งทุน" name="funding_source" />
            <x-input label="DA104: ประเภทองค์กร (รัฐบาล / เอกชน / มูลนิธิ)" name="funding_org_type" />
            <x-input label="DA105: ชื่อทุน" name="funding_name" />
            <x-input label="DA106: งบประมาณ (บาท)" name="budget" type="number" step="0.01" />
        </div>

        <!-- รหัสเพิ่มเติม -->
        <x-input label="AA603.1: รหัสโครงการ IO (AA)" name="io_code" />
        <x-input label="AA605.1: รหัสรายวิชา" name="course_code" />

        <!-- ✨ ต่อ section อื่นๆ เช่น รหัส R, IRB, FTE, Output, ยุทธศาสตร์ -->
        <!-- คุณสามารถ paste ต่อส่วนล่างได้เลยตามที่เราเคยสร้างไว้ -->

        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                💾 บันทึกข้อมูล
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateInputs = document.querySelectorAll('.datepicker-th');
    dateInputs.forEach(input => {
        new Datepicker(input, {
            language: 'th',
            format: 'mmmm yyyy',
            autohide: true
        });
    });
});
</script>
@endsection

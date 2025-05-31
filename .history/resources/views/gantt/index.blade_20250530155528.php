@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<!-- DHTMLX Gantt -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<style>
    .gantt_marker.today { background-color: red; }
</style>

<div class="max-w-full mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📊 Gantt Chart: {{ $project->project_name_th }}</h2>

    <div class="bg-white p-4 rounded shadow">
        <div class="flex justify-end mb-4">
            <button onclick="showModal()" class="px-4 py-2 bg-blue-500 text-white rounded">➕ เพิ่มงาน</button>
            <button onclick="saveAllTasks()" class="px-4 py-2 bg-green-600 text-white rounded">📂 บันทึกทั้งหมด</button>
        </div>
        <div id="gantt_here" class="w-full min-h-[500px] max-h-[90vh] overflow-auto"></div>
    </div>
</div>

<!-- Task Modal -->
<div id="task-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded w-full max-w-md space-y-4">
        <h3 id="task-modal-title" class="text-lg font-semibold">📝 งาน</h3>
        <input id="task-id" type="hidden" data-parent="">

        <label class="block">
            <span class="text-sm">ชื่งาน</span>
            <input id="task-title" class="w-full border p-2 rounded">
        </label>

        <label class="block">
            <span class="text-sm">วันที่เริ่มต้น</span>
            <input id="task-start" class="w-full border p-2 rounded flatpickr-th">
        </label>

        <label class="block">
            <span class="text-sm">วันที่สิ้นสุด</span>
            <input id="task-end" class="w-full border p-2 rounded flatpickr-th">
        </label>

        <label class="block">
            <span class="text-sm">สถานะ</span>
            <select id="task-status" class="w-full border p-2 rounded">
                <option value="doing">🕒 กำลังทำ</option>
                <option value="done">✅ เสร็จแล้ว</option>
            </select>
        </label>

        <label class="block">
            <span class="text-sm">ผู้รับผิดชอบ</span>
            <input id="task-owner" class="w-full border p-2 rounded">
        </label>

        <label class="block">
            <span class="text-sm">ความคืบหน้า (%)</span>
            <input id="task-progress" type="number" min="0" max="100" step="5" class="w-full border p-2 rounded">
        </label>

        <div class="flex justify-between pt-2">
            <button id="delete-task" class="text-red-600 hover:underline hidden">🗑 ลบงาน</button>
            <div class="flex gap-2">
                <button onclick="hideModal()" class="text-gray-600 px-4 py-2 rounded hover:bg-gray-100">ยกเลิก</button>
                <button id="save-task" class="bg-blue-600 text-white px-4 py-2 rounded">📂 บันทึก</button>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript ฟังก์ชันทั้งหมดจะถูกใส่ในภายหลังโดยแยก script หรือใส่ตรงนี้ต่อ
</script>
@endsection
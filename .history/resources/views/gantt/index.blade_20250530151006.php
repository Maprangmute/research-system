@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- ✅ Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<!-- ✅ DHTMLX Gantt -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<style>
    .gantt_marker.today { background-color: red; }
</style>

<div class="max-w-full mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📊 Gantt Chart: {{ $project->project_name_th }}</h2>
    <div class="bg-white p-4 rounded shadow">
        <!-- View Selector -->
        <div class="flex justify-end mb-4">
            <button id="scale-button" onclick="toggleScaleDropdown()"
                class="px-4 py-2 bg-white border rounded shadow">🗂 เปลี่ยนมุมมอง</button>
            <div id="scale-dropdown" class="hidden absolute bg-white border mt-2 rounded shadow z-50">
                <button onclick="setScale('week')" class="block px-4 py-2 hover:bg-gray-100">🗓 รายสัปดาห์</button>
                <button onclick="setScale('month')" class="block px-4 py-2 hover:bg-gray-100">📆 รายเดือน</button>
                <button onclick="setScale('year')" class="block px-4 py-2 hover:bg-gray-100">🗂 รายปี</button>
            </div>
        </div>

        <div id="gantt_here" class="w-full min-h-[400px] max-h-[90vh] overflow-y-auto"></div>
        <div class="text-right mt-4">
            <button onclick="showModal()" class="px-4 py-2 bg-blue-600 text-white rounded">➕ เพิ่มงานรอง</button>
        </div>
    </div>
</div>

<!-- ✅ Modal -->
<div id="subtask-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded w-full max-w-md space-y-4">
        <h3 class="text-lg font-semibold">➕ เพิ่มงานรอง</h3>
        <input id="subtask-title" class="w-full border p-2 rounded" placeholder="ชื่องานรอง">
        <label><span class="text-sm">วันที่เริ่มต้น</span><input id="subtask-start" class="w-full border p-2 rounded flatpickr-th"></label>
        <label><span class="text-sm">วันที่สิ้นสุด</span><input id="subtask-end" class="w-full border p-2 rounded flatpickr-th"></label>
        <div class="flex justify-end gap-2">
            <button id="confirm-add-subtask" class="bg-green-600 text-white px-4 py-2 rounded">บันทึก</button>
            <button onclick="hideModal()" class="text-gray-600 px-4 py-2">ยกเลิก</button>
        </div>
    </div>
</div>

<script>
    function toggleScaleDropdown() {
        const menu = document.getElementById("scale-dropdown");
        menu.classList.toggle("hidden");
    }

    function setScale(scale) {
        const daysThai = ["อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัส", "ศุกร์", "เสาร์"];
        const monthsThai = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
        if (scale === "week") {
            gantt.config.scale_unit = "week";
            gantt.config.date_scale = "สัปดาห์ที่ #%W";
            gantt.config.subscales = [{ unit: "day", step: 1, date: d => `${daysThai[d.getDay()]} ${d.getDate()}` }];
        } else if (scale === "month") {
            gantt.config.scale_unit = "month";
            gantt.config.date_scale = "%F";
            gantt.config.subscales = [{ unit: "week", step: 1, date: d => `${d.getDate()} ${monthsThai[d.getMonth()]}` }];
        } else if (scale === "year") {
            gantt.config.scale_unit = "year";
            gantt.config.date_scale = "%Y";
            gantt.config.subscales = [{ unit: "month", step: 1, date: d => monthsThai[d.getMonth()] }];
        }
        gantt.render();
        document.getElementById("scale-dropdown").classList.add("hidden");
    }

    function showModal() {
        document.getElementById("subtask-modal").classList.remove("hidden");
    }

    function hideModal() {
        document.getElementById("subtask-modal").classList.add("hidden");
    }

    function formatDateThai(date) {
        const months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
    }

    flatpickr.localize(flatpickr.l10ns.th);
    flatpickr(".flatpickr-th", {
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        allowInput: true,
        onChange: function(selectedDates, dateStr, instance) {
            const buddhistYear = selectedDates[0].getFullYear() + 543;
            instance.altInput.value = instance.altInput.value.replace(/\d{4}/, buddhistYear);
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        gantt.config.xml_date = "%Y-%m-%d";
        gantt.config.columns = [
            { name: "text", label: "งาน", width: 220, tree: true },
            { name: "start_date", label: "เริ่ม", align: "center", width: 120, template: task => formatDateThai(task.start_date) },
            { name: "end_date", label: "สิ้นสุด", align: "center", width: 120, template: task => formatDateThai(task.end_date) },
            { name: "status", label: "สถานะ", align: "center", width: 100, template: task => task.status === 'done' ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ" },
            { name: "add", label: "", width: 44 }
        ];

        gantt.templates.scale_date = function(date) {
            if (gantt.config.scale_unit === "year") return `${date.getFullYear() + 543}`;
            return `${date.getDate()} ${monthsThai[date.getMonth()]} ${date.getFullYear() + 543}`;
        };

        setScale("month");
        gantt.init("gantt_here");
        gantt.load("/projects/{{ $project->id }}/tasks");
    });
</script>
@endsection

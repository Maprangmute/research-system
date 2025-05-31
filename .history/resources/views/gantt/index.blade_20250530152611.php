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
        <!-- Dropdown -->
        <div class="flex justify-end mb-4">
            <div class="relative inline-block text-left">
                <button id="scale-button" onclick="toggleScaleDropdown()"
                    class="inline-flex justify-center w-full rounded-md border px-4 py-2 bg-white text-sm text-gray-700 shadow-sm hover:bg-gray-50">
                    🗂 เปลี่ยนมุมมอง
                    <svg class="-mr-1 ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="scale-dropdown" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden z-50">
                    <div class="py-1">
                        <button onclick="setScale('week')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🗓 รายสัปดาห์</button>
                        <button onclick="setScale('month')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📆 รายเดือน</button>
                        <button onclick="setScale('year')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🗂 รายปี</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="gantt_here" class="w-full min-h-[500px] max-h-[90vh] overflow-auto"></div>

        <div class="mt-4 text-right">
            <button onclick="showModal()" class="px-4 py-2 bg-blue-500 text-white rounded">➕ เพิ่มงานรอง</button>
            <button onclick="saveAllTasks()" class="px-4 py-2 bg-green-600 text-white rounded">💾 บันทึกทั้งหมด</button>
        </div>
    </div>
</div>
<!-- ปุ่มกดเปิด modal -->

<!-- ✅ Modal เพิ่มงานรอง -->
<div id="subtask-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded w-full max-w-md space-y-4">
        <h3 class="text-lg font-semibold">➕ เพิ่มงานรอง</h3>
        <input id="subtask-title" class="w-full border p-2 rounded" placeholder="ชื่องานรอง">
        <input id="subtask-start" class="w-full border p-2 rounded flatpickr-th" placeholder="วันที่เริ่มต้น">
        <input id="subtask-end" class="w-full border p-2 rounded flatpickr-th" placeholder="วันที่สิ้นสุด">
        <div class="flex justify-end gap-2 pt-2">
            <button id="confirm-add-subtask" class="bg-blue-600 text-white px-4 py-2 rounded">บันทึก</button>
            <button onclick="hideModal()" class="text-gray-600 px-4 py-2 rounded hover:bg-gray-100">ยกเลิก</button>
        </div>
    </div>
</div>



<script>
    // Dropdown
    function toggleScaleDropdown() {
        const menu = document.getElementById("scale-dropdown");
        menu.classList.toggle("hidden");
        document.addEventListener("click", function handler(e) {
            if (!menu.contains(e.target) && !document.getElementById("scale-button").contains(e.target)) {
                menu.classList.add("hidden");
                document.removeEventListener("click", handler);
            }
        });
    }

    // Modal
    function showModal() {
        document.getElementById('subtask-modal').classList.remove('hidden');
    }
    function hideModal() {
        document.getElementById('subtask-modal').classList.add('hidden');
    }

    // พ.ศ. → ค.ศ.
    function convertToAD(dateStr) {
        const parts = dateStr.split("-");
        if (parseInt(parts[0]) > 2400) parts[0] = parseInt(parts[0]) - 543;
        return parts.join("-");
    }

    // Flatpickr Thai
    flatpickr.localize(flatpickr.l10ns.th);
    flatpickr(".flatpickr-th", {
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        allowInput: true,
        locale: "th",
        onReady: (dates, str, inst) => {
            const y = inst.selectedDates?.[0]?.getFullYear();
            if (y && y < 2500) inst.altInput.value = inst.altInput.value.replace(/\d{4}/, y + 543);
        },
        onChange: (dates, str, inst) => {
            const y = dates[0].getFullYear();
            inst.altInput.value = inst.altInput.value.replace(/\d{4}/, y + 543);
        }
    });

    // เพิ่มงานรอง (console เท่านั้นในตอนนี้)
    document.getElementById("confirm-add-subtask").addEventListener("click", () => {
        const title = document.getElementById('subtask-title').value;
        const start = document.getElementById('subtask-start').value;
        const end = document.getElementById('subtask-end').value;
        console.log("📌 Subtask:", title, start, end);
        hideModal();
    });

    // Gantt Scale
    function setScale(scale) {
        const daysThai = ["อาทิตย์","จันทร์","อังคาร","พุธ","พฤหัส","ศุกร์","เสาร์"];
        const monthsThai = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
        switch (scale) {
            case "week":
                gantt.config.scale_unit = "week";
                gantt.config.date_scale = "สัปดาห์ #%W";
                gantt.config.subscales = [{ unit: "day", step: 1, date: d => `${daysThai[d.getDay()]} ${("0"+d.getDate()).slice(-2)}` }];
                break;
            case "month":
                gantt.config.scale_unit = "month";
                gantt.config.date_scale = "%F";
                gantt.config.subscales = [{ unit: "week", step: 1, date: d => `${("0"+d.getDate()).slice(-2)} ${monthsThai[d.getMonth()]}` }];
                break;
            case "year":
                gantt.config.scale_unit = "year";
                gantt.config.date_scale = "%Y";
                gantt.config.subscales = [{ unit: "month", step: 1, date: d => monthsThai[d.getMonth()] }];
                break;
        }
        gantt.render();
        document.getElementById("scale-dropdown").classList.add("hidden");
    }

    // Format พ.ศ.
    function formatDateThai(date) {
        const months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
    }

    // Save tasks
    function saveAllTasks() {
        const data = [];
        gantt.eachTask(task => {
            data.push({
                id: task.id,
                text: task.text,
                start_date: task.start_date,
                end_date: task.end_date,
                parent: task.parent,
                progress: task.progress,
                status: task.status,
                owner: task.owner
            });
        });
        fetch("/tasks/bulk-save", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ project_id: {{ $project->id }}, tasks: data })
        }).then(r => r.json()).then(() => {
            alert("✅ บันทึกสำเร็จ");
            gantt.clearAll();
            gantt.load("/projects/{{ $project->id }}/tasks");
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        gantt.config.xml_date = "%Y-%m-%d";
        gantt.config.row_height = 36;
        gantt.config.grid_resize = true;
        gantt.config.start_on_monday = true;

        gantt.config.columns = [
            { name: "text", label: "งาน", width: 220, tree: true },
            { name: "start_date", label: "เริ่ม", align: "center", width: 120, template: t => formatDateThai(t.start_date) },
            { name: "end_date", label: "สิ้นสุด", align: "center", width: 120, template: t => formatDateThai(t.end_date) },
            { name: "status", label: "สถานะ", align: "center", width: 100, template: t => t.status === 'done' ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ" },
            { name: "add", label: "", width: 44 }
        ];

        gantt.templates.scale_date = date => {
            const y = date.getFullYear() + 543;
            return gantt.config.scale_unit === "year" ? `${y}` : `${date.getDate()} ${date.getMonth() + 1} ${y}`;
        };

        setScale("month");
        gantt.init("gantt_here");
        gantt.load("/projects/{{ $project->id }}/tasks");
    });
</script>
@endsection

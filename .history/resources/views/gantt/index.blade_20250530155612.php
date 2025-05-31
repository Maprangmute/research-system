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
            <button onclick="showModal()" class="px-4 py-2 bg-blue-500 text-white rounded">➕ เพิ่มงาน</button>
            <button onclick="saveAllTasks()" class="px-4 py-2 bg-green-600 text-white rounded">💾 บันทึกทั้งหมด</button>
        </div>
    </div>
</div>

@include('partials.gantt-modal')

<script>
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

function convertToAD(dateStr) {
    const parts = dateStr.split("-");
    if (parseInt(parts[0]) > 2400) parts[0] -= 543;
    return parts.join("-");
}

function formatDateThai(date) {
    const months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
}

function rebindAddButtons() {
    setTimeout(() => {
        document.querySelectorAll(".gantt_add").forEach(btn => {
            btn.onclick = e => {
                const row = btn.closest(".gantt_row");
                const id = row?.getAttribute("data-task-id");
                if (id) {
                    const task = gantt.getTask(id);
                    showModal({ parent: task.id });
                    e.stopPropagation();
                }
            };
        });
    }, 300);
}

function saveAllTasks() {
    const data = [];
    gantt.eachTask(task => {
        data.push({
            id: task.id,
            text: task.text,
            start_date: gantt.templates.xml_format(task.start_date),
            end_date: gantt.templates.xml_format(task.end_date),
            parent: task.parent,
            status: task.status ?? 'ongoing',
            progress: task.progress ?? 0,
            owner: task.owner ?? null,
        });
    });

    fetch("/tasks/bulk-save", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            project_id: {{ $project->id }},
            tasks: data
        })
    }).then(res => res.json()).then(() => {
        alert("✅ บันทึกสำเร็จ");
        gantt.clearAll();
        gantt.load("/projects/{{ $project->id }}/tasks", null, rebindAddButtons);
    });
}

function setScale(scale) {
    const days = ["อา", "จ", "อ", "พ", "พฤ", "ศ", "ส"];
    const months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    if (scale === "week") {
        gantt.config.scale_unit = "week";
        gantt.config.date_scale = "สัปดาห์ #%W";
        gantt.config.subscales = [{ unit: "day", step: 1, date: d => `${days[d.getDay()]} ${d.getDate()}` }];
    } else if (scale === "month") {
        gantt.config.scale_unit = "month";
        gantt.config.date_scale = "%F";
        gantt.config.subscales = [{ unit: "week", step: 1, date: d => `${d.getDate()} ${months[d.getMonth()]}` }];
    } else if (scale === "year") {
        gantt.config.scale_unit = "year";
        gantt.config.date_scale = "%Y";
        gantt.config.subscales = [{ unit: "month", step: 1, date: d => months[d.getMonth()] }];
    }
    gantt.render();
    document.getElementById("scale-dropdown").classList.add("hidden");
}

document.addEventListener("DOMContentLoaded", () => {
    gantt.config.xml_date = "%Y-%m-%d";
    gantt.config.row_height = 36;

    gantt.config.columns = [
        { name: "text", label: "งาน", width: 220, tree: true },
        { name: "start_date", label: "เริ่ม", align: "center", width: 120, template: t => formatDateThai(t.start_date) },
        { name: "end_date", label: "สิ้นสุด", align: "center", width: 120, template: t => formatDateThai(t.end_date) },
        { name: "status", label: "สถานะ", align: "center", width: 100, template: t => t.status === 'done' ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ" },
        { name: "add", label: "", width: 44 }
    ];

    gantt.attachEvent("onTaskDblClick", (id) => {
        const task = gantt.getTask(id);
        showModal(task);
        return false;
    });

    gantt.load("/projects/{{ $project->id }}/tasks", null, rebindAddButtons);
    setScale("month");
    gantt.init("gantt_here");

    flatpickr.localize(flatpickr.l10ns.th);
    flatpickr(".flatpickr-th", {
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        locale: "th",
        onChange: (dates, str, inst) => {
            const y = dates[0].getFullYear();
            inst.altInput.value = inst.altInput.value.replace(/\d{4}/, y + 543);
        }
    });
});
</script>
@endsection

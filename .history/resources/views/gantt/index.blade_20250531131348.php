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
@media (max-width: 768px) {
    .status-ongoing { color: #eab308; font-weight: 600; }
    .status-done { color: #16a34a; font-weight: 600; }

    #gantt-container, #gantt_here {
        width: 100vw !important;
        min-width: 100vw !important;
        overflow-x: auto;
    }

    #task-panel {
        width: 100vw;
        height: 100vh;
        border-radius: 0;
        padding-bottom: 6rem;
    }
}

.flatpickr-calendar {
    z-index: 999999 !important;
}

#task-panel {
    transition: transform 0.3s ease;
    max-width: 480px;
    margin: 0 auto;
    bottom: 0;
    left: 0;
    right: 0;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 999998;
    pointer-events: auto;
}

#task-panel.hidden {
    transform: translateY(100%);
}
</style>

<div class="w-full px-2 py-4 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📊 Gantt Chart: {{ $project->project_name_th }}</h2>

    <div class="bg-white p-4 rounded shadow">
        <div class="flex justify-end mb-4">
            <div class="relative">
                <button onclick="toggleScaleDropdown()" id="scale-button" class="px-4 py-2 border rounded bg-white text-sm">
                    🗂 เปลี่ยนมุมมอง
                </button>
                <div id="scale-dropdown" class="absolute right-0 mt-2 w-40 bg-white border rounded shadow hidden z-50">
                    <button onclick="setScale('day')" class="w-full text-left px-4 py-2 hover:bg-gray-100">📅 รายวัน</button>
                    <button onclick="setScale('week')" class="w-full text-left px-4 py-2 hover:bg-gray-100">🗓 รายสัปดาห์</button>
                    <button onclick="setScale('month')" class="w-full text-left px-4 py-2 hover:bg-gray-100">📆 รายเดือน</button>
                </div>
            </div>
        </div>

        <div id="gantt-container" class="w-full overflow-x-auto">
            <div id="gantt_here" style="min-width: 1000px; height: 500px;"></div>
        </div>

        <div class="mt-4 text-right space-x-2">
            <button onclick="showPanel()" class="px-4 py-2 bg-blue-600 text-white rounded">➕ เพิ่มงาน</button>
            <button onclick="saveAllTasks()" class="px-4 py-2 bg-green-600 text-white rounded">💾 บันทึกทั้งหมด</button>
        </div>
    </div>
</div>

<!-- ✅ Slide Panel Modal -->
@include('components.task-panel') {{-- หรือวางโค้ด modal panel ที่คุณมีไว้ตรงนี้ได้เลย --}}

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

function formatDateThai(date) {
    const months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                    "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
}

function setScale(scale) {
    const shortMonths = ["ม.ค", "ก.พ", "มี.ค", "เม.ย", "พ.ค", "มิ.ย",
                         "ก.ค", "ส.ค", "ก.ย", "ต.ค", "พ.ย", "ธ.ค"];
    const fullMonths = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
                        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];

    if (scale === "day") {
        gantt.config.scales = [
            { unit: "day", step: 1, format: d => `${d.getDate()} ${shortMonths[d.getMonth()]}` }
        ];
    } else if (scale === "week") {
        gantt.config.scales = [
            { unit: "week", step: 1, format: d => "สัปดาห์ที่ " + gantt.date.date_to_str("%W")(d) },
            { unit: "day", step: 1, format: d => `${d.getDate()} ${shortMonths[d.getMonth()]}` }
        ];
    } else {
        gantt.config.scales = [
            { unit: "month", step: 1, format: d => `${fullMonths[d.getMonth()]}` },
            { unit: "week", step: 1, format: d => `${d.getDate()} ${fullMonths[d.getMonth()]}` }
        ];
    }

    gantt.render();
    document.getElementById("scale-dropdown").classList.add("hidden");
}

function updateColumnsForScreenSize() {
    const isMobile = window.innerWidth <= 768;
    gantt.config.columns = isMobile
        ? [
            {
                name: "text", label: "งาน", tree: true, width: "*",
                template: t => `<span class="${t.status === 'done' ? 'status-done' : 'status-ongoing'}">${t.text}</span>`
            },
            { name: "add", label: "", width: 44 }
        ]
        : [
            { name: "text", label: "งาน", tree: true, width: 240 },
            { name: "start_date", label: "เริ่ม", align: "center", width: 120, template: t => formatDateThai(new Date(t.start_date)) },
            { name: "end_date", label: "สิ้นสุด", align: "center", width: 120, template: t => formatDateThai(new Date(t.end_date)) },
            {
                name: "status", label: "สถานะ", align: "center", width: 100,
                template: t => t.status === "done" ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ"
            },
            { name: "add", label: "", width: 44 }
        ];
}

// ✅ เรียกใช้เมื่อโหลด
document.addEventListener("DOMContentLoaded", () => {
    updateColumnsForScreenSize();
    setScale("month");

    gantt.config.xml_date = "%Y-%m-%d";
    gantt.config.lightbox = false;
    gantt.config.row_height = 36;

    gantt.attachEvent("onBeforeLightbox", () => false);
    gantt.attachEvent("onTaskCreated", task => {
        setTimeout(() => {
            if (gantt.isTaskExists(task.id)) gantt.deleteTask(task.id);
            showPanel({ parent: task.parent });
        }, 0);
        return false;
    });

    gantt.attachEvent("onTaskDblClick", id => {
        showPanel(gantt.getTask(id));
        return false;
    });

    gantt.init("gantt_here");
    gantt.load(`/projects/{{ $project->id }}/tasks`);

    // ✅ ลิงก์งาน (task dependencies)
    gantt.config.api_links = {
        create: "/task-links",
        update: "/task-links",
        delete: "/task-links"
    };
    const linkProcessor = new gantt.dataProcessor("/task-links");
    linkProcessor.init(gantt);
    linkProcessor.setTransactionMode("REST");

    window.addEventListener("resize", () => {
        updateColumnsForScreenSize();
        gantt.render();
    });
});
</script>
@endsection

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
        .status-ongoing { color: #eab308; font-weight: 600; } /* เหลือง */
        .status-done { color: #16a34a; font-weight: 600; }     /* เขียว */

        #gantt-container, #gantt_here {
            width: 100vw !important;
            min-width: 100vw !important;
            overflow-x: auto;
        }
    }

    .flatpickr-calendar {
        z-index: 999999 !important;
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

// ✅ เดือนภาษาไทย + ปี พ.ศ.
function formatDateThai(date) {
    const months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                    "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
}

// ✅ ใช้รูปแบบใหม่สำหรับ scale
function setScale(scale) {
    if (scale === "day") {
        gantt.config.scales = [
            { unit: "day", step: 1, format: date => formatDateThai(date) }
        ];
    } else if (scale === "week") {
        gantt.config.scales = [
            { unit: "week", step: 1, format: date => "สัปดาห์ที่ " + gantt.date.date_to_str("%W")(date) },
            { unit: "day", step: 1, format: date => formatDateThai(date) }
        ];
    } else if (scale === "month") {
        gantt.config.scales = [
            { unit: "month", step: 1, format: date => {
                const months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                                "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
                return `${months[date.getMonth()]} ${date.getFullYear() + 543}`;
            }},
            { unit: "week", step: 1, format: date => formatDateThai(date) }
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
                name: "text",
                label: "งาน",
                tree: true,
                width: "*",
                template: t => {
                    const cls = t.status === "done" ? "status-done" : "status-ongoing";
                    return `<span class="${cls}">${t.text}</span>`;
                }
            },
            { name: "add", label: "", width: 44 }
        ]
        : [
            { name: "text", label: "งาน", tree: true, width: 240 },
            { name: "start_date", label: "เริ่ม", align: "center", width: 120,
              template: t => formatDateThai(new Date(t.start_date)) },
            { name: "end_date", label: "สิ้นสุด", align: "center", width: 120,
              template: t => formatDateThai(new Date(t.end_date)) },
            {
                name: "status", label: "สถานะ", align: "center", width: 100,
                template: t => t.status === "done" ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ"
            },
            { name: "add", label: "", width: 44 }
        ];
}

function saveAllTasks() {
    const tasks = [];
    gantt.eachTask(t => tasks.push(t));
    const formData = new FormData();
    formData.append("project_id", {{ $project->id }});
    formData.append("tasks", JSON.stringify(tasks));

    fetch("/tasks/bulk-save", {
        method: "POST",
        headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
        body: formData
    }).then(res => res.json())
      .then(json => {
          if (json.status === 'success') {
              alert("✅ บันทึกสำเร็จ");
              gantt.clearAll();
              gantt.load(`/projects/{{ $project->id }}/tasks`);
          }
      }).catch(() => alert("❌ เกิดข้อผิดพลาด"));
}

document.addEventListener("DOMContentLoaded", () => {
    gantt.config.xml_date = "%Y-%m-%d";
    gantt.config.lightbox = false;
    gantt.attachEvent("onBeforeLightbox", () => false);
    gantt.config.row_height = 36;

    gantt.attachEvent("onTaskCreated", task => {
        setTimeout(() => {
            if (gantt.isTaskExists(task.id)) gantt.deleteTask(task.id);
            showPanel({ parent: task.parent });
        }, 0);
        return false;
    });

    gantt.attachEvent("onTaskDblClick", id => {
        const task = gantt.getTask(id);
        showPanel(task);
        return false;
    });

    updateColumnsForScreenSize();

    if (window.innerWidth < 768) {
        setScale("day");
    } else {
        setScale("month");
    }

    gantt.init("gantt_here");
    gantt.load(`/projects/{{ $project->id }}/tasks`);

    window.addEventListener("resize", () => {
        updateColumnsForScreenSize();
        gantt.render();
    });
});
</script>
@endsection

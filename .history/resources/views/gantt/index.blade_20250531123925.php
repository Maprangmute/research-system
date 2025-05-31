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
        .status-bg-ongoing {
            background-color: #fef08a !important;
        }
        .status-bg-done {
            background-color: #bbf7d0 !important;
        }

        #gantt-container {
            width: 100vw !important;
            overflow-x: auto;
        }

        #gantt_here,
        .gantt_grid,
        .gantt_task,
        .dhx_cal_data {
            width: 100vw !important;
            min-width: 100vw !important;
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
<div id="task-panel" class="fixed bg-white shadow-xl p-4 z-50 hidden transform translate-y-full">
    <div id="parent-info" class="text-sm text-gray-600 mb-2 hidden">
        📎 เป็นงานย่อยของ: <span id="parent-task-name" class="font-semibold"></span>
    </div>
    <input type="hidden" id="task-id" data-parent="">
    <h3 class="text-lg font-bold mb-4">📝 งาน</h3>

    <div class="space-y-3">
        <label class="block">
            <span class="text-sm">ชื่อเรื่อง</span>
            <input id="task-title" class="w-full border p-2 rounded mt-1" type="text">
        </label>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="block">
                <span class="text-sm">วันที่เริ่มต้น</span>
                <input id="task-start" class="flatpickr-th w-full border p-2 rounded mt-1" type="text">
            </label>
            <label class="block">
                <span class="text-sm">วันที่สิ้นสุด</span>
                <input id="task-end" class="flatpickr-th w-full border p-2 rounded mt-1" type="text">
            </label>
        </div>

        <label class="block">
            <span class="text-sm">สถานะ</span>
            <select id="task-status" class="w-full border p-2 rounded mt-1">
                <option value="ongoing">🕒 กำลังทำ</option>
                <option value="done">✅ เสร็จแล้ว</option>
            </select>
        </label>

        <label class="block">
            <span class="text-sm">ผู้รับผิดชอบ</span>
            <input id="task-owner" class="w-full border p-2 rounded mt-1" type="text">
        </label>

        <label class="block">
            <span class="text-sm">ความคืบหน้า (%)</span>
            <input id="task-progress" type="number" min="0" max="100" step="5" class="w-full border p-2 rounded mt-1">
        </label>
    </div>

    <div class="flex justify-between mt-4">
        <button id="delete-task" class="text-red-600 hover:underline hidden">🗑 ลบ</button>
        <div class="space-x-2">
            <button onclick="hidePanel()" class="px-4 py-2 bg-gray-300 rounded">ยกเลิก</button>
            <button id="save-task" class="px-4 py-2 bg-blue-600 text-white rounded">💾 บันทึก</button>
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

function formatDateThai(date) {
    const months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
}

function updateColumnsForScreenSize() {
    const isMobile = window.innerWidth <= 768;
    gantt.config.columns = isMobile
        ? [{
              name: "text",
              label: "งาน",
              tree: true,
              width: 240,
              template: t => {
                  const cls = t.status === "done" ? "status-bg-done" : "status-bg-ongoing";
                  return `<div class="${cls} p-1 rounded">${t.text}</div>`;
              }
          }, { name: "add", label: "", width: 44 }]
        : [
            { name: "text", label: "งาน", tree: true, width: 240 },
            { name: "start_date", label: "เริ่ม", align: "center", width: 120, template: t => formatDateThai(new Date(t.start_date)) },
            { name: "end_date", label: "สิ้นสุด", align: "center", width: 120, template: t => formatDateThai(new Date(t.end_date)) },
            { name: "status", label: "สถานะ", align: "center", width: 100, template: t => t.status === "done" ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ" },
            { name: "add", label: "", width: 44 }
        ];
}

function setScale(scale) {
    gantt.config.scale_unit = scale;
    if (scale === "day") {
        gantt.config.date_scale = "%j %M";
        gantt.config.subscales = [];
    } else if (scale === "week") {
        gantt.config.date_scale = "สัปดาห์ที่ #%W";
        gantt.config.subscales = [{ unit: "day", step: 1, date: "%j %D" }];
    } else if (scale === "month") {
        gantt.config.date_scale = "%F";
        gantt.config.subscales = [{ unit: "week", step: 1, date: "%j %M" }];
    }
    gantt.render();
    document.getElementById("scale-dropdown").classList.add("hidden");
}

function showPanel(task = {}) {
    const panel = document.getElementById("task-panel");
    panel.classList.remove("hidden");
    setTimeout(() => panel.classList.remove("translate-y-full"), 10);

    document.getElementById("task-id").value = task.id || "";
    document.getElementById("task-id").dataset.parent = task.parent || "";
    document.getElementById("task-title").value = task.text || "";
    document.getElementById("task-status").value = task.status || "ongoing";
    document.getElementById("task-owner").value = task.owner || "";
    document.getElementById("task-progress").value = task.progress != null ? task.progress * 100 : 0;

    const parentInfo = document.getElementById("parent-info");
    const parentNameSpan = document.getElementById("parent-task-name");
    if (task.parent && gantt.isTaskExists(task.parent)) {
        const parentTask = gantt.getTask(task.parent);
        parentInfo.classList.remove("hidden");
        parentNameSpan.textContent = parentTask.text;
    } else {
        parentInfo.classList.add("hidden");
    }

    setTimeout(() => {
        flatpickr.localize(flatpickr.l10ns.th);
        document.querySelectorAll(".flatpickr-th").forEach(input => {
            if (input._flatpickr) input._flatpickr.destroy();
            flatpickr(input, {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "j F Y",
                locale: "th",
                appendTo: document.body,
                disableMobile: true
            });
        });

        if (task.start_date) document.getElementById("task-start")._flatpickr.setDate(task.start_date, true);
        if (task.end_date) document.getElementById("task-end")._flatpickr.setDate(task.end_date, true);
    }, 300);

    document.getElementById("delete-task").classList.toggle("hidden", !task.id);
}

function hidePanel() {
    const panel = document.getElementById("task-panel");
    panel.classList.add("translate-y-full");
    setTimeout(() => panel.classList.add("hidden"), 300);
}

document.getElementById("save-task").addEventListener("click", () => {
    const id = document.getElementById("task-id").value;
    const parent = document.getElementById("task-id").dataset.parent || 0;
    const text = document.getElementById("task-title").value;
    const start = document.getElementById("task-start").value;
    const end = document.getElementById("task-end").value;
    const status = document.getElementById("task-status").value;
    const owner = document.getElementById("task-owner").value;
    const progress = parseInt(document.getElementById("task-progress").value || 0) / 100;

    if (!text.trim() || !start || !end) return alert("กรุณากรอกข้อมูลให้ครบ");

    const startDate = new Date(start);
    const endDate = new Date(end);

    if (id) {
        Object.assign(gantt.getTask(id), { text, start_date: startDate, end_date: endDate, status, owner, progress });
        gantt.updateTask(id);
    } else {
        gantt.addTask({ text, start_date: startDate, end_date: endDate, status, owner, progress, parent });
    }

    hidePanel();
});

document.getElementById("delete-task").addEventListener("click", () => {
    const id = document.getElementById("task-id").value;
    if (id && confirm("ลบใช่หรือไม่?")) {
        gantt.deleteTask(id);
        hidePanel();
    }
});

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
    updateColumnsForScreenSize();
    if (window.innerWidth < 768) setScale("day");
    else setScale("month");

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

    gantt.init("gantt_here");
    gantt.load(`/projects/{{ $project->id }}/tasks`);

    window.addEventListener("resize", () => {
        updateColumnsForScreenSize();
        gantt.render();
    });
});
</script>
@endsection

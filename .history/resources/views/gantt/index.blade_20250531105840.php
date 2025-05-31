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
        <div class="flex justify-end mb-4">
            <div class="relative">
                <button onclick="toggleScaleDropdown()" id="scale-button" class="px-4 py-2 border rounded bg-white text-sm">
                    🗂 เปลี่ยนมุมมอง
                </button>
                <div id="scale-dropdown" class="absolute right-0 mt-2 w-40 bg-white border rounded shadow hidden z-50">
                    <button onclick="setScale('week')" class="w-full text-left px-4 py-2 hover:bg-gray-100">🗓 รายสัปดาห์</button>
                    <button onclick="setScale('month')" class="w-full text-left px-4 py-2 hover:bg-gray-100">📆 รายเดือน</button>
                    <button onclick="setScale('year')" class="w-full text-left px-4 py-2 hover:bg-gray-100">🗂 รายปี</button>
                </div>
            </div>
        </div>

        <div id="gantt_here" class="w-full min-h-[500px] max-h-[90vh] overflow-auto"></div>

        <div class="mt-4 text-right">
            <button onclick="showModal()" class="px-4 py-2 bg-blue-600 text-white rounded">➕ เพิ่มงาน</button>
            <button onclick="saveAllTasks()" class="px-4 py-2 bg-green-600 text-white rounded">💾 บันทึกทั้งหมด</button>
        </div>
    </div>
</div>

<!-- ✅ Modal -->
<div id="task-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-lg space-y-4">
        <h3 class="text-xl font-bold text-gray-800">📝 งาน</h3>
        <input type="hidden" id="task-id" data-parent="">

        <div class="space-y-3">
            <label class="block">
                <span class="text-sm font-medium">ชื่อเรื่อง</span>
                <input id="task-title" class="w-full border p-2 rounded mt-1" type="text">
            </label>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-sm font-medium">วันที่เริ่มต้น</span>
                    <input id="task-start" class="w-full border p-2 rounded mt-1 flatpickr-th" type="text">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">วันที่สิ้นสุด</span>
                    <input id="task-end" class="w-full border p-2 rounded mt-1 flatpickr-th" type="text">
                </label>
            </div>

            <label class="block">
                <span class="text-sm font-medium">สถานะ</span>
                <select id="task-status" class="w-full border p-2 rounded mt-1">
                    <option value="ongoing">🕒 กำลังทำ</option>
                    <option value="done">✅ เสร็จแล้ว</option>
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-medium">ผู้รับผิดชอบ</span>
                <input id="task-owner" class="w-full border p-2 rounded mt-1" type="text">
            </label>

            <label class="block">
                <span class="text-sm font-medium">ความคืบหน้า (%)</span>
                <input id="task-progress" type="number" min="0" max="100" step="5" class="w-full border p-2 rounded mt-1">
            </label>
        </div>

        <div class="flex justify-between items-center pt-4">
            <button id="delete-task" class="text-red-600 hover:underline hidden">🗑 ลบ</button>
            <div class="space-x-2">
                <button onclick="hideModal()" class="px-4 py-2 bg-gray-300 rounded">ยกเลิก</button>
                <button id="save-task" class="px-4 py-2 bg-blue-600 text-white rounded">💾 บันทึก</button>
            </div>
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

function formatThaiBuddhistYear(instance) {
    const input = instance.altInput;
    if (input && instance.selectedDates.length > 0) {
        const date = instance.selectedDates[0];
        const buddhistYear = date.getFullYear() + 543;
        const formatted = input.value.replace(/\d{4}/, buddhistYear);
        input.value = formatted;
    }
}

function showModal(task = {}) {
    document.getElementById("task-modal").classList.remove("hidden");

    document.getElementById("task-id").value = task.id || "";
    document.getElementById("task-id").dataset.parent = task.parent || "";
    document.getElementById("task-title").value = task.text || "";
    document.getElementById("task-status").value = task.status || "ongoing";
    document.getElementById("task-owner").value = task.owner || "";
    document.getElementById("task-progress").value = task.progress != null ? task.progress * 100 : 0;

    setTimeout(() => {
        flatpickr.localize(flatpickr.l10ns.th);
        document.querySelectorAll(".flatpickr-th").forEach(input => {
            if (input._flatpickr) input._flatpickr.destroy();
            flatpickr(input, {
                altInput: true,
                altFormat: "j F Y",
                dateFormat: "Y-m-d",
                locale: "th",
                onReady: (_, __, instance) => formatThaiBuddhistYear(instance),
                onChange: (_, __, instance) => formatThaiBuddhistYear(instance),
            });
        });

        if (task.start_date) document.getElementById("task-start")._flatpickr.setDate(task.start_date);
        if (task.end_date) document.getElementById("task-end")._flatpickr.setDate(task.end_date);
    }, 100);

    document.getElementById("delete-task").classList.toggle("hidden", !task.id);
}

function hideModal() {
    document.getElementById("task-modal").classList.add("hidden");
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

    if (!text.trim() || !start || !end) {
        alert("กรุณากรอกข้อมูลให้ครบถ้วน");
        return;
    }

    const startDate = new Date(start);
    const endDate = new Date(end);

    if (id) {
        const task = gantt.getTask(id);
        Object.assign(task, { text, start_date: startDate, end_date: endDate, status, owner, progress });
        gantt.updateTask(id);
    } else {
        gantt.addTask({ text, start_date: startDate, end_date: endDate, status, owner, progress, parent });
    }

    hideModal();
    rebindAddButtons();
});

document.getElementById("delete-task").addEventListener("click", () => {
    const id = document.getElementById("task-id").value;
    if (id && confirm("ลบใช่หรือไม่?")) {
        gantt.deleteTask(id);
        hideModal();
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
              gantt.load(`/projects/{{ $project->id }}/tasks`, null, rebindAddButtons);
          }
      }).catch(err => alert("❌ เกิดข้อผิดพลาด"));
}

function rebindAddButtons() {
    setTimeout(() => {
        document.querySelectorAll(".gantt_add").forEach(btn => {
            btn.onclick = e => {
                const row = btn.closest(".gantt_row");
                const id = row?.getAttribute("data-task-id");
                if (id && gantt.isTaskExists(id)) {
                    const task = gantt.getTask(id);
                    showModal({ parent: task.id });
                    e.stopPropagation();
                }
            };
        });
    }, 300);
}

function setScale(scale) {
    gantt.config.scale_unit = scale;
    if (scale === "week") {
        gantt.config.date_scale = "สัปดาห์ที่ #%W";
        gantt.config.subscales = [{ unit: "day", step: 1, date: "%D %j" }];
    } else if (scale === "month") {
        gantt.config.date_scale = "%F";
        gantt.config.subscales = [{ unit: "week", step: 1, date: "%j %M" }];
    } else if (scale === "year") {
        gantt.config.date_scale = "%Y";
        gantt.config.subscales = [{ unit: "month", step: 1, date: "%F" }];
    }
    gantt.render();
    document.getElementById("scale-dropdown").classList.add("hidden");
}

function formatDateThai(date) {
    const months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
}

document.addEventListener("DOMContentLoaded", () => {
    flatpickr.localize(flatpickr.l10ns.th);

    gantt.config.xml_date = "%Y-%m-%d";
    gantt.config.row_height = 36;

    gantt.config.columns = [
        { name: "text", label: "งาน", tree: true, width: 240 },
        { name: "start_date", label: "เริ่ม", align: "center", width: 120, template: t => formatDateThai(new Date(t.start_date)) },
        { name: "end_date", label: "สิ้นสุด", align: "center", width: 120, template: t => formatDateThai(new Date(t.end_date)) },
        { name: "status", label: "สถานะ", align: "center", width: 100, template: t => t.status === "done" ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ" },
        { name: "add", label: "", width: 44 }
    ];

    gantt.attachEvent("onTaskDblClick", id => {
        const task = gantt.getTask(id);
        showModal(task);
        return false;
    });

    setScale("month");
    gantt.init("gantt_here");
    gantt.load(`/projects/{{ $project->id }}/tasks`, null, rebindAddButtons);
});
</script>
@endsection

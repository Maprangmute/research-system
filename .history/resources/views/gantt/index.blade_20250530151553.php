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
        <div class="mb-4 text-right">
            <button onclick="openTaskModal()" class="px-4 py-2 bg-blue-600 text-white rounded">➕ เพิ่มงาน</button>
            <button onclick="saveAllTasks()" class="px-4 py-2 bg-green-600 text-white rounded">💾 บันทึกทั้งหมด</button>
        </div>

        <div id="gantt_here" class="w-full min-h-[500px] max-h-[90vh] overflow-auto"></div>
    </div>
</div>

<!-- Modal สร้าง/แก้ไขงาน -->
<div id="task-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center">
  <div class="bg-white rounded-lg p-6 w-full max-w-md space-y-4">
    <h2 id="task-modal-title" class="text-xl font-bold">➕ เพิ่มงาน</h2>

    <input type="hidden" id="task-id">

    <input type="text" id="task-title" class="w-full border p-2 rounded" placeholder="ชื่องาน">

    <input type="text" id="task-start" class="w-full border p-2 rounded flatpickr-th" placeholder="วันที่เริ่มต้น">
    <input type="text" id="task-end" class="w-full border p-2 rounded flatpickr-th" placeholder="วันที่สิ้นสุด">

    <div class="flex justify-end space-x-2 pt-2">
      <button onclick="closeTaskModal()" class="px-4 py-2 bg-gray-200 rounded">ยกเลิก</button>
      <button onclick="saveTaskFromModal()" class="px-4 py-2 bg-blue-600 text-white rounded">💾 บันทึก</button>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Init Gantt
    gantt.config.xml_date = "%Y-%m-%d";
    gantt.config.row_height = 36;
    gantt.config.grid_resize = true;
    gantt.config.start_on_monday = true;

    gantt.config.columns = [
        { name: "text", label: "งาน", width: 220, tree: true },
        { name: "start_date", label: "เริ่ม", align: "center", width: 120, template: t => formatDateThai(t.start_date) },
        { name: "end_date", label: "สิ้นสุด", align: "center", width: 120, template: t => formatDateThai(t.end_date) },
        { name: "edit", label: "✏️", width: 44, template: t => `<button onclick='openTaskModal(${JSON.stringify(t)})'>✏️</button>` }
    ];

    gantt.templates.scale_date = date => {
        const y = date.getFullYear() + 543;
        return gantt.config.scale_unit === "year" ? `${y}` : `${date.getDate()} ${date.getMonth() + 1} ${y}`;
    };

    gantt.config.scale_unit = "month";
    gantt.config.date_scale = "%F";
    gantt.config.subscales = [{ unit: "week", step: 1 }];

    gantt.init("gantt_here");
    gantt.load("/projects/{{ $project->id }}/tasks");

    // Init Flatpickr
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
});

// Format date to Thai
function formatDateThai(date) {
    const months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
}

// Modal controls
function openTaskModal(task = null) {
    document.getElementById("task-modal").classList.remove("hidden");

    if (task) {
        document.getElementById("task-modal-title").textContent = "✏️ แก้ไขงาน";
        document.getElementById("task-id").value = task.id;
        document.getElementById("task-title").value = task.text;
        document.getElementById("task-start")._flatpickr.setDate(task.start_date, true);
        document.getElementById("task-end")._flatpickr.setDate(task.end_date, true);
    } else {
        document.getElementById("task-modal-title").textContent = "➕ เพิ่มงาน";
        document.getElementById("task-id").value = "";
        document.getElementById("task-title").value = "";
        document.getElementById("task-start").value = "";
        document.getElementById("task-end").value = "";
    }
}

function closeTaskModal() {
    document.getElementById("task-modal").classList.add("hidden");
}

function saveTaskFromModal() {
    const id = document.getElementById("task-id").value;
    const title = document.getElementById("task-title").value;
    const start = document.getElementById("task-start").value;
    const end = document.getElementById("task-end").value;

    if (!title || !start || !end) return alert("กรุณากรอกข้อมูลให้ครบ");

    const taskData = {
        id: id || gantt.uid(),
        text: title,
        start_date: start,
        end_date: end,
        progress: 0,
        status: "doing"
    };

    if (id && gantt.isTaskExists(id)) {
        const task = gantt.getTask(id);
        Object.assign(task, taskData);
        gantt.updateTask(id);
    } else {
        gantt.addTask(taskData);
    }

    closeTaskModal();
}

// Save all tasks
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
            status: task.status
        });
    });

    fetch("/tasks/bulk-save", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ project_id: {{ $project->id }}, tasks: data })
    })
    .then(res => res.json())
    .then(() => {
        alert("✅ บันทึกเรียบร้อย");
        gantt.clearAll();
        gantt.load("/projects/{{ $project->id }}/tasks");
    });
}
</script>
@endsection

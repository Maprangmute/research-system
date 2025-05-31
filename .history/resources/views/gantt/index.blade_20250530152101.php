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

<!-- ✅ Modal เพิ่มและแก้ไขงาน -->
<div id="task-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4">
        <div class="flex justify-between items-center bg-gray-100 px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800" id="modal-task-title">✏️ จัดการงาน</h3>
            <button onclick="hideTaskModal()" class="text-gray-500 hover:text-red-600 text-2xl">&times;</button>
        </div>
        <div class="px-6 py-4 space-y-4">
            <input type="hidden" id="task-id">
            <div>
                <label class="block text-sm font-medium text-gray-700">ชื่อกิจกรรม</label>
                <input id="task-title" type="text" class="w-full border p-2 rounded mt-1" placeholder="ชื่อกิจกรรม">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">วันที่เริ่มต้น</label>
                    <input id="task-start" type="text" class="flatpickr-th w-full border p-2 rounded mt-1" placeholder="เลือกวันที่เริ่มต้น">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">วันที่สิ้นสุด</label>
                    <input id="task-end" type="text" class="flatpickr-th w-full border p-2 rounded mt-1" placeholder="เลือกวันที่สิ้นสุด">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">สถานะ</label>
                    <select id="task-status" class="w-full border p-2 rounded mt-1">
                        <option value="ongoing">🕒 กำลังทำ</option>
                        <option value="done">✅ เสร็จแล้ว</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">ผู้รับผิดชอบ</label>
                <input id="task-owner" type="text" class="w-full border p-2 rounded mt-1" placeholder="ชื่อผู้รับผิดชอบ">
            </div>
        </div>
        <div class="flex justify-end gap-2 p-4 border-t bg-gray-50">
            <button onclick="hideTaskModal()" class="px-4 py-2 rounded border">ยกเลิก</button>
            <button id="save-task-btn" onclick="saveTask()" class="px-4 py-2 rounded bg-blue-600 text-white">บันทึก</button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Flatpickr Thai config
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

    gantt.config.xml_date = "%Y-%m-%d";
    gantt.config.row_height = 36;
    gantt.config.grid_resize = true;

    gantt.config.columns = [
        { name: "text", label: "งาน", width: 220, tree: true },
        { name: "start_date", label: "เริ่ม", align: "center", width: 120, template: t => formatDateThai(t.start_date) },
        { name: "end_date", label: "สิ้นสุด", align: "center", width: 120, template: t => formatDateThai(t.end_date) },
        { name: "edit", label: "✏️", width: 50, template: t => `<button onclick='openTaskModal(${JSON.stringify(t)})'>✏️</button>` }
    ];

    gantt.config.scale_unit = "month";
    gantt.config.date_scale = "%F";
    gantt.config.subscales = [{ unit: "week", step: 1 }];
    gantt.templates.scale_date = date => `${date.getDate()}/${date.getMonth() + 1}/${date.getFullYear() + 543}`;

    gantt.init("gantt_here");
    gantt.load("/projects/{{ $project->id }}/tasks");
});

function formatDateThai(date) {
    const months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
}

function openTaskModal(task = null) {
    document.getElementById("task-modal").classList.remove("hidden");
    document.getElementById("modal-task-title").textContent = task ? "✏️ แก้ไขงาน" : "➕ เพิ่มงาน";

    document.getElementById("task-id").value = task?.id || "";
    document.getElementById("task-title").value = task?.text || "";
    document.getElementById("task-start")._flatpickr.setDate(task?.start_date || "", true);
    document.getElementById("task-end")._flatpickr.setDate(task?.end_date || "", true);
    document.getElementById("task-status").value = task?.status || "ongoing";
    document.getElementById("task-owner").value = task?.owner || "";
}

function hideTaskModal() {
    document.getElementById("task-modal").classList.add("hidden");
}

function saveTask() {
    const id = document.getElementById("task-id").value;
    const title = document.getElementById("task-title").value;
    const start = document.getElementById("task-start").value;
    const end = document.getElementById("task-end").value;
    const status = document.getElementById("task-status").value;
    const owner = document.getElementById("task-owner").value;

    if (!title || !start || !end) return alert("กรุณากรอกข้อมูลให้ครบ");

    const taskData = {
        id: id || gantt.uid(),
        text: title,
        start_date: start,
        end_date: end,
        progress: 0,
        status: status,
        owner: owner
    };

    if (id && gantt.isTaskExists(id)) {
        Object.assign(gantt.getTask(id), taskData);
        gantt.updateTask(id);
    } else {
        gantt.addTask(taskData);
    }

    hideTaskModal();
}

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

@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- ✅ Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<div class="max-w-full mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">🗕️ Gantt Chart: {{ $project->project_name_th }}</h2>

    <div class="bg-white p-4 rounded shadow">
        <div id="gantt_here" class="w-full h-[800px] overflow-x-auto"></div>
        <div class="mt-4 text-right">
            <button onclick="saveAllTasks()" class="px-4 py-2 bg-green-600 text-white rounded">💾 บันทึกทั้งหมด</button>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="task-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4">
        <div class="flex justify-between items-center bg-gray-100 px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800" id="modal-task-title">✏️ จัดการงาน</h3>
            <button onclick="hideTaskModal()" class="text-gray-500 hover:text-red-600 text-2xl">&times;</button>
        </div>
        <div class="px-6 py-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">ชื่อกิจกรรม</label>
                <input id="task-title" type="text" class="w-full border p-2 rounded mt-1">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">วันที่เริ่มต้น</label>
                    <input id="task-start" type="text" class="datepicker-th w-full border p-2 rounded mt-1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">วันที่สิ้นสุด</label>
                    <input id="task-end" type="text" class="datepicker-th w-full border p-2 rounded mt-1">
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
                <input id="task-owner" type="text" class="w-full border p-2 rounded mt-1">
            </div>
        </div>
        <div class="flex justify-end gap-2 p-4 border-t bg-gray-50">
            <button onclick="hideTaskModal()" class="px-4 py-2 rounded border">ยกเลิก</button>
            <button id="save-task-btn" class="px-4 py-2 rounded bg-blue-600 text-white">บันทึก</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
<script>
let selectedParentId = 0;
let currentTaskId = null;

function formatDateThai(date) {
    const monthsThai = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    const d = new Date(date);
    return `${d.getDate()} ${monthsThai[d.getMonth()]} ${d.getFullYear() + 543}`;
}

function showTaskModal() {
    document.getElementById("task-modal").classList.remove("hidden");
}

function hideTaskModal() {
    document.getElementById("task-modal").classList.add("hidden");
    selectedParentId = 0;
    currentTaskId = null;
}

document.addEventListener("DOMContentLoaded", function () {
    flatpickr(".datepicker-th", {
        dateFormat: "Y-m-d",
        locale: "th",
        allowInput: true
    });

    gantt.config.xml_date = "%Y-%m-%d";
    gantt.config.row_height = 36;
    gantt.config.grid_resize = true;
    gantt.config.order_branch = true;
    gantt.config.order_branch_free = true;

    gantt.config.columns = [
        { name: "text", label: "Task name", width: 220, tree: true },
        {
            name: "start_date", label: "Start", align: "center", width: 120,
            template: task => formatDateThai(task.start_date)
        },
        {
            name: "end_date", label: "End", align: "center", width: 120,
            template: task => formatDateThai(task.end_date)
        },
        {
            name: "status", label: "Status", align: "center", width: 100,
            template: task => task.status === 'done' ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ"
        },
        { name: "add", label: "", width: 44 }
    ];

    gantt.templates.date_grid = date => formatDateThai(date);
    gantt.templates.tooltip_date_format = date => formatDateThai(date);
    gantt.templates.task_date = (start, end) => `${formatDateThai(start)} - ${formatDateThai(end)}`;

    gantt.attachEvent("onTaskCreated", function(task){
        selectedParentId = task.parent || 0;
        currentTaskId = null;
        showTaskModal();
        return false;
    });

    gantt.attachEvent("onTaskDblClick", function(id, e){
        const task = gantt.getTask(id);
        currentTaskId = id;
        console.log("[EDIT]", task);
        document.getElementById("task-title").value = task.text;
        document.getElementById("task-start")._flatpickr.setDate(task.start_date, true);
        document.getElementById("task-end")._flatpickr.setDate(task.end_date, true);
        document.getElementById("task-status").value = task.status || "ongoing";
        document.getElementById("task-owner").value = task.owner || "";
        showTaskModal();
        return false;
    });

    document.getElementById("save-task-btn").addEventListener("click", () => {
        const title = document.getElementById("task-title").value;
        const start = document.getElementById("task-start").value;
        const end = document.getElementById("task-end").value;
        const status = document.getElementById("task-status").value;
        const owner = document.getElementById("task-owner").value;

        if (!title || !start || !end) {
            alert("กรุณากรอกข้อมูลให้ครบ");
            return;
        }

        if (currentTaskId) {
            const task = gantt.getTask(currentTaskId);
            task.text = title;
            task.start_date = start;
            task.end_date = end;
            task.status = status;
            task.owner = owner;
            gantt.updateTask(currentTaskId);
        } else {
            const id = gantt.uid();
            gantt.addTask({
                id,
                text: title,
                start_date: start,
                end_date: end,
                parent: selectedParentId,
                progress: 0,
                status,
                owner
            });
            gantt.showTask(id);
            gantt.selectTask(id);
        }

        hideTaskModal();
    });

    gantt.init("gantt_here");
    gantt.load("/projects/{{ $project->id }}/tasks");
});

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
    .then(response => {
        alert("บันทึกสำเร็จ");
        gantt.clearAll();
        gantt.load("/projects/{{ $project->id }}/tasks");
    });
}
</script>
@endsection

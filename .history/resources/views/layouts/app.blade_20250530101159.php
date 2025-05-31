@extends('layouts.app')

@section('content')
<div class="max-w-full mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">🗕️ Gantt Chart: {{ $project->project_name_th }}</h2>

    <div class="bg-white p-4 rounded shadow">
        <div id="gantt_here" style="width: 100%; height: 800px;"></div>
    </div>
</div>

<!-- ✅ Modal เพิ่มงานแบบละเอียด -->
<div id="task-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl">
        <div class="flex justify-between items-center bg-gray-100 px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800" id="modal-task-title">➕ เพิ่มงานใหม่</h3>
            <button onclick="hideTaskModal()" class="text-gray-500 hover:text-red-600 text-2xl">&times;</button>
        </div>
        <div class="px-6 py-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">ชื่อกิจกรรม</label>
                <input id="task-title" type="text" class="w-full border p-2 rounded mt-1" placeholder="ชื่อกิจกรรม">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">วันที่เริ่มต้น</label>
                    <input id="task-start" type="date" class="w-full border p-2 rounded mt-1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">วันที่สิ้นสุด</label>
                    <input id="task-end" type="date" class="w-full border p-2 rounded mt-1">
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
            <button onclick="addTaskFromModal()" class="px-4 py-2 rounded bg-blue-600 text-white">บันทึก</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<script>
let selectedParentId = 0;

document.addEventListener("DOMContentLoaded", function () {
    gantt.config.xml_date = "%Y-%m-%d";
    gantt.config.row_height = 36;
    gantt.config.grid_resize = true;

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

    function formatDateThai(date) {
        const monthsThai = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
        const d = new Date(date);
        const day = d.getDate();
        const month = monthsThai[d.getMonth()];
        const year = d.getFullYear() + 543;
        return `${day} ${month} ${year}`;
    }

    gantt.templates.date_grid = date => formatDateThai(date);
    gantt.templates.tooltip_date_format = date => formatDateThai(date);
    gantt.templates.task_date = (start, end) => `${formatDateThai(start)} - ${formatDateThai(end)}`;

    gantt.attachEvent("onTaskCreated", function(task){
        selectedParentId = task.parent || 0;
        showTaskModal();
        return false;
    });

    gantt.init("gantt_here");
    gantt.load("/projects/{{ $project->id }}/tasks");

    const dp = new gantt.dataProcessor("/tasks");
    dp.init(gantt);
    dp.setTransactionMode("REST");
});

function showTaskModal() {
    document.getElementById("task-modal").classList.remove("hidden");
}

function hideTaskModal() {
    document.getElementById("task-modal").classList.add("hidden");
    selectedParentId = 0;
}

function addTaskFromModal() {
    const title = document.getElementById("task-title").value;
    const start = document.getElementById("task-start").value;
    const end = document.getElementById("task-end").value;
    const status = document.getElementById("task-status").value;
    const owner = document.getElementById("task-owner").value;

    if (!title || !start || !end) {
        alert("กรุณากรอกข้อมูลให้ครบ");
        return;
    }

    const id = gantt.uid();

    gantt.addTask({
        id,
        text: title,
        start_date: start,
        end_date: end,
        parent: selectedParentId,
        progress: 0,
        status: status,
        owner: owner
    });

    gantt.showTask(id);
    gantt.selectTask(id);
    hideTaskModal();
}
</script>
@endsection

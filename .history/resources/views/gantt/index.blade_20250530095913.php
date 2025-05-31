@extends('layouts.app')

@section('content')
<div class="max-w-full mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📅 Gantt Chart: {{ $project->project_name_th }}</h2>

    <div class="flex justify-end">
        <button onclick="showTaskModal()" class="px-4 py-2 mb-2 bg-green-600 text-white rounded hover:bg-green-700">
            ➕ เพิ่มงาน
        </button>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <div id="gantt_here" style="width: 100%; height: 800px;"></div>
    </div>
</div>

<!-- ✅ Modal -->
<div id="task-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md space-y-4">
        <h3 class="text-lg font-semibold text-gray-800">➕ เพิ่มงานใหม่</h3>
        <div class="space-y-2">
            <label class="block">ชื่อกิจกรรม</label>
            <input id="task-title" type="text" class="w-full border p-2 rounded" placeholder="ชื่องาน">
            
            <label class="block">วันที่เริ่ม</label>
            <input id="task-start" type="date" class="w-full border p-2 rounded">
            
            <label class="block">วันที่สิ้นสุด</label>
            <input id="task-end" type="date" class="w-full border p-2 rounded">
            
            <label class="block">สถานะ</label>
            <select id="task-status" class="w-full border p-2 rounded">
                <option value="ongoing">🕒 กำลังทำ</option>
                <option value="done">✅ เสร็จแล้ว</option>
            </select>
            
            <label class="block">ผู้รับผิดชอบ</label>
            <input id="task-owner" type="text" class="w-full border p-2 rounded" placeholder="ชื่อผู้รับผิดชอบ">
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <button onclick="hideTaskModal()" class="px-4 py-2 rounded border">ยกเลิก</button>
            <button onclick="addTaskFromModal()" class="px-4 py-2 rounded bg-blue-600 text-white">บันทึก</button>
        </div>
    </div>
</div>

<!-- ✅ DHTMLX Gantt -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    gantt.config.xml_date = "%Y-%m-%d";

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
        }
    ];

    function formatDateThai(date) {
        const monthsThai = [
            "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
            "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
        ];
        const d = new Date(date);
        const day = d.getDate();
        const month = monthsThai[d.getMonth()];
        const year = d.getFullYear() + 543;
        return `${day} ${month} ${year}`;
    }

    gantt.templates.date_grid = date => formatDateThai(date);
    gantt.templates.tooltip_date_format = date => formatDateThai(date);
    gantt.templates.task_date = (start, end) => `${formatDateThai(start)} - ${formatDateThai(end)}`;

    gantt.init("gantt_here");
    gantt.load("/projects/{{ $project->id }}/tasks");

    const dp = new gantt.dataProcessor("/tasks");
    dp.init(gantt);
    dp.setTransactionMode("REST");
});

// ✅ Modal handlers
function showTaskModal() {
    document.getElementById("task-modal").classList.remove("hidden");
}

function hideTaskModal() {
    document.getElementById("task-modal").classList.add("hidden");
}

// ✅ เพิ่ม task จาก modal
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
        parent: 0,
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

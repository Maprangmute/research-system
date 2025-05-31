@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<div class="max-w-full mx-auto py-6 space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">🔕️ Gantt Chart: {{ $project->project_name_th }}</h2>
        <button onclick="showTaskModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">➕ เพิ่มงานใหม่</button>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <div id="gantt_here" class="w-full h-[800px] overflow-x-auto"></div>
    </div>
    <div class="text-right">
        <button onclick="saveAllTasks()" class="mt-4 px-6 py-2 bg-green-600 text-white rounded">💾 บันทึกทั้งหมด</button>
    </div>
</div>

<!-- ✅ Modal -->
<div id="task-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4">
        <div class="flex justify-between items-center bg-gray-100 px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800" id="modal-task-title">✏️ จัดการงาน</h3>
            <button onclick="hideTaskModal()" class="text-gray-500 hover:text-red-600 text-2xl">&times;</button>
        </div>
        <div class="px-6 py-4 space-y-4">
            <label class="block text-sm">ชื่อกิจกรรม</label>
            <input id="task-title" class="w-full border p-2 rounded" type="text">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm">วันที่เริ่ม</label>
                    <input id="task-start" type="text" class="flatpickr-th w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block text-sm">วันที่สิ้นสุด</label>
                    <input id="task-end" type="text" class="flatpickr-th w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block text-sm">สถานะ</label>
                    <select id="task-status" class="w-full border p-2 rounded">
                        <option value="ongoing">🕒 กำลังทำ</option>
                        <option value="done">✅ เสร็จแล้ว</option>
                    </select>
                </div>
            </div>

            <label class="block text-sm">ผู้รับผิดชอบ</label>
            <input id="task-owner" class="w-full border p-2 rounded" type="text">
        </div>
        <div class="flex justify-end gap-2 p-4 border-t bg-gray-50">
            <button onclick="hideTaskModal()" class="px-4 py-2 border rounded">ยกเลิก</button>
            <button id="save-task-btn" class="px-4 py-2 bg-blue-600 text-white rounded">บันทึก</button>
        </div>
    </div>
</div>

<script>
let currentTaskId = null;

function showTaskModal(task = null) {
    currentTaskId = task?.id || null;
    document.getElementById('task-title').value = task?.text || '';
    document.getElementById('task-status').value = task?.status || 'ongoing';
    document.getElementById('task-owner').value = task?.owner || '';
    if (task) {
        document.getElementById('task-start')._flatpickr.setDate(task.start_date, true);
        document.getElementById('task-end')._flatpickr.setDate(task.end_date, true);
    } else {
        document.getElementById('task-start').value = '';
        document.getElementById('task-end').value = '';
    }
    document.getElementById('task-modal').classList.remove('hidden');
}

function hideTaskModal() {
    document.getElementById('task-modal').classList.add('hidden');
}

function formatDateThai(date) {
    const m = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    return `${date.getDate()} ${m[date.getMonth()]} ${date.getFullYear() + 543}`;
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
            status: task.status,
            progress: task.progress,
            owner: task.owner,
        });
    });

    fetch("/tasks/bulk-save", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ project_id: {{ $project->id }}, tasks: data })
    }).then(r => r.json())
      .then(() => alert("✅ บันทึกสำเร็จ"));
}

document.addEventListener("DOMContentLoaded", function () {
    flatpickr.localize(flatpickr.l10ns.th);
    flatpickr(".flatpickr-th", {
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        locale: "th"
    });

    gantt.config.xml_date = "%Y-%m-%d";
    gantt.config.columns = [
        { name: "text", label: "งาน", tree: true, width: 220 },
        { name: "start_date", label: "เริ่ม", align: "center", width: 120, template: t => formatDateThai(new Date(t.start_date)) },
        { name: "end_date", label: "สิ้นสุด", align: "center", width: 120, template: t => formatDateThai(new Date(t.end_date)) },
        { name: "status", label: "สถานะ", align: "center", width: 100, template: t => t.status === 'done' ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ" },
        { name: "add", label: "", width: 44 }
    ];

    gantt.templates.scale_date = d => `${d.getFullYear() + 543}`;
    gantt.config.show_lightbox = false;

    gantt.attachEvent("onTaskCreated", function () {
        showTaskModal();
        return false;
    });

    gantt.attachEvent("onTaskDblClick", function (id) {
        showTaskModal(gantt.getTask(id));
        return false;
    });

    document.getElementById("save-task-btn").addEventListener("click", function () {
        const title = document.getElementById("task-title").value;
        const start = document.getElementById("task-start").value;
        const end = document.getElementById("task-end").value;
        const status = document.getElementById("task-status").value;
        const owner = document.getElementById("task-owner").value;

        if (!title || !start || !end) {
            alert("❌ กรุณากรอกข้อมูลให้ครบ");
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
            gantt.addTask({
                id: gantt.uid(),
                text: title,
                start_date: start,
                end_date: end,
                status,
                owner,
                progress: 0
            });
        }

        hideTaskModal();
    });

    gantt.init("gantt_here");
    gantt.load("/projects/{{ $project->id }}/tasks");
});
</script>
@endsection

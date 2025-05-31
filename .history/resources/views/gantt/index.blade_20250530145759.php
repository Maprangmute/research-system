@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<div class="max-w-full mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">🔕️ Gantt Chart: {{ $project->project_name_th }}</h2>
    <div class="bg-white p-4 rounded shadow">
        <div id="gantt_here" class="w-full h-[800px] overflow-x-auto"></div>
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
            <button id="save-task-btn" class="px-4 py-2 rounded bg-blue-600 text-white">บันทึก</button>
        </div>
    </div>
</div>

<script>
let currentTaskId = null;

function showTaskModal(task = null) {
    if (task) {
        currentTaskId = task.id;
        document.getElementById('task-title').value = task.text || '';
        document.getElementById('task-start')._flatpickr.setDate(task.start_date, true);
        document.getElementById('task-end')._flatpickr.setDate(task.end_date, true);
        document.getElementById('task-status').value = task.status || 'ongoing';
        document.getElementById('task-owner').value = task.owner || '';
    } else {
        currentTaskId = null;
        document.getElementById('task-title').value = '';
        document.getElementById('task-start').value = '';
        document.getElementById('task-end').value = '';
        document.getElementById('task-status').value = 'ongoing';
        document.getElementById('task-owner').value = '';
    }
    document.getElementById('task-modal').classList.remove('hidden');
}

function hideTaskModal() {
    document.getElementById('task-modal').classList.add('hidden');
}

function formatDateThai(date) {
    const months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
}

function setScale(scale) {
    const daysThai = ["อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัส", "ศุกร์", "เสาร์"];
    const monthsThai = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    if (scale === "week") {
        gantt.config.scale_unit = "week";
        gantt.config.date_scale = "สัปดาห์ที่ #%W";
        gantt.config.subscales = [{ unit: "day", step: 1, date: d => `${daysThai[d.getDay()]} ${d.getDate()}` }];
    } else if (scale === "month") {
        gantt.config.scale_unit = "month";
        gantt.config.date_scale = "%F";
        gantt.config.subscales = [{ unit: "week", step: 1, date: d => `${d.getDate()} ${monthsThai[d.getMonth()]}` }];
    } else if (scale === "year") {
        gantt.config.scale_unit = "year";
        gantt.config.date_scale = "%Y";
        gantt.config.subscales = [{ unit: "month", step: 1, date: d => monthsThai[d.getMonth()] }];
    }
    gantt.render();
}

document.addEventListener("DOMContentLoaded", function () {
    flatpickr.localize(flatpickr.l10ns.th);
    flatpickr(".flatpickr-th", {
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        allowInput: true,
        locale: "th",
        onReady: function(selectedDates, dateStr, instance) {
            const y = instance.selectedDates?.[0]?.getFullYear();
            if (y && y < 2500) {
                const buddhistYear = y + 543;
                instance.altInput.value = instance.altInput.value.replace(/\d{4}/, buddhistYear);
            }
        },
        onChange: function(selectedDates, dateStr, instance) {
            const buddhistYear = selectedDates[0].getFullYear() + 543;
            instance.altInput.value = instance.altInput.value.replace(/\d{4}/, buddhistYear);
        }
    });

    gantt.config.show_lightbox = false;
    gantt.config.xml_date = "%Y-%m-%d";
    gantt.config.columns = [
        { name: "text", label: "งาน", width: 220, tree: true },
        { name: "start_date", label: "เริ่ม", align: "center", width: 120, template: task => formatDateThai(new Date(task.start_date)) },
        { name: "end_date", label: "สิ้นสุด", align: "center", width: 120, template: task => formatDateThai(new Date(task.end_date)) },
        { name: "status", label: "สถานะ", align: "center", width: 100, template: task => task.status === 'done' ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ" },
        { name: "add", label: "", width: 44 }
    ];

    gantt.templates.scale_date = date => `${date.getFullYear() + 543}`;

    gantt.attachEvent("onTaskCreated", function(task){
        showTaskModal();
        return false;
    });

    gantt.attachEvent("onTaskDblClick", function(id, e){
        const task = gantt.getTask(id);
        showTaskModal(task);
        return false;
    });

    document.getElementById("save-task-btn").addEventListener("click", function () {
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
                progress: 0,
                status,
                owner
            });
        }

        hideTaskModal();
    });

    setScale("month");
    gantt.init("gantt_here");
    gantt.load("/projects/{{ $project->id }}/tasks");
});
</script>
@endsection

@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto py-6 space-y-6">
        <h2 class="text-2xl font-bold text-gray-800">🗕️ Gantt Chart: {{ $project->project_name_th }}</h2>

        <div class="bg-white p-4 rounded shadow">
            <div id="gantt_here" class="w-full h-[800px] overflow-x-auto"></div>
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

        document.addEventListener("DOMContentLoaded", function() {
            gantt.config.xml_date = "%Y-%m-%d";
            gantt.config.row_height = 36;
            gantt.config.grid_resize = true;
            gantt.config.order_branch = true;
            gantt.config.order_branch_free = true;

            gantt.attachEvent("onTaskLoading", function(task) {
                if (!task.text && task.title) task.text = task.title;
                if (!task.start_date && task.start) task.start_date = task.start;
                if (!task.end_date && task.end) task.end_date = task.end;
                return true;
            });

            gantt.config.data_property = function(data) {
                return {
                    ...data,
                    title: data.text,
                    start: data.start_date,
                    end: data.end_date
                };
            };

            gantt.config.columns = [{
                    name: "text",
                    label: "Task name",
                    width: 220,
                    tree: true
                },
                {
                    name: "start_date",
                    label: "Start",
                    align: "center",
                    width: 120,
                    template: task => formatDateThai(task.start_date)
                },
                {
                    name: "end_date",
                    label: "End",
                    align: "center",
                    width: 120,
                    template: task => formatDateThai(task.end_date)
                },
                {
                    name: "status",
                    label: "Status",
                    align: "center",
                    width: 100,
                    template: task => task.status === 'done' ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ"
                },
                {
                    name: "add",
                    label: "",
                    width: 44
                }
            ];

            function formatDateThai(date) {
                const monthsThai = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม",
                    "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
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

            gantt.attachEvent("onTaskCreated", function(task) {
                selectedParentId = task.parent || 0;
                currentTaskId = null;
                showTaskModal();
                return false;
            });

            gantt.attachEvent("onTaskDblClick", function(id, e) {
                const task = gantt.getTask(id);
                currentTaskId = id;
                document.getElementById("task-title").value = task.text;
                document.getElementById("task-start").value = task.start_date;
                document.getElementById("task-end").value = task.end_date;
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
            currentTaskId = null;
        }
    </script>
@endsection

@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto py-6 space-y-6">
        <h2 class="text-2xl font-bold text-gray-800">🗕️ Gantt Chart: {{ $project->project_name_th }}</h2>

        <div class="bg-white p-4 rounded shadow">
            <div id="gantt_here" class="w-full h-[800px] overflow-x-auto"></div>
        </div>
    </div>

    <!-- ✅ Modal เพิ่มและแก้ไขงาน -->
    <div id="task-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4">
            <div class="flex justify-between items-center bg-gray-100 px-6 py-4 border-b">
                <div class="space-y-1">
                    <h3 class="text-lg font-semibold text-gray-800" id="modal-task-title">✏️ จัดการงาน</h3>
                    <p id="task-type-label" class="text-sm text-gray-500"></p>
                </div>
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
                    <input id="task-owner" type="text" class="w-full border p-2 rounded mt-1"
                        placeholder="ชื่อผู้รับผิดชอบ">
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
        document.addEventListener("DOMContentLoaded", function() {
            gantt.config.xml_date = "%Y-%m-%d";
            gantt.config.autoscroll = true;
            gantt.config.drag_links = true;
            gantt.config.drag_move = true;
            gantt.config.order_branch = true;
            gantt.config.order_branch_free = true;

            gantt.init("gantt_here");
            gantt.load("/projects/{{ $project->id }}/tasks");

            const dp = new gantt.dataProcessor("/tasks");
            dp.init(gantt);
            dp.setTransactionMode("REST");

            gantt.attachEvent("onAfterLinkAdd", function(id, link) {
                fetch("/task-links", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            project_id: {{ $project->id }},
                            source: link.source,
                            target: link.target,
                            type: link.type
                        })
                    })
                    .then(res => res.json())
                    .then(data => gantt.changeLinkId(id, data.tid));
            });

            gantt.attachEvent("onAfterLinkDelete", function(id) {
                fetch("/task-links/" + id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": '{{ csrf_token() }}'
                    }
                });
            });
        });
    </script>
@endsection

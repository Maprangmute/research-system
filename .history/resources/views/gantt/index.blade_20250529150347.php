@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📅 Gantt Chart: {{ $project->project_name_th }}</h2>
    <div class="bg-white p-4 rounded shadow overflow-x-auto" id="gantt_here"></div>
</div>

<!-- Modal สำหรับเพิ่มงานรอง -->
<div id="subtask-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded p-6 w-full max-w-md shadow-lg space-y-4">
        <h3 class="text-lg font-semibold text-gray-800">➕ เพิ่มงานรอง</h3>
        <input id="subtask-title" type="text" class="w-full border p-2 rounded" placeholder="ชื่องานรอง">
        <input id="subtask-start" type="date" class="w-full border p-2 rounded">
        <input id="subtask-end" type="date" class="w-full border p-2 rounded">
        <div class="flex justify-end gap-2 pt-2">
            <button onclick="hideModal()" class="px-4 py-2 rounded border">ยกเลิก</button>
            <button id="confirm-add-subtask" class="px-4 py-2 rounded bg-blue-600 text-white">บันทึก</button>
        </div>
    </div>
</div>

<!-- ✅ ใช้ Frappe Gantt ที่คัดลอกมาแบบ UMD -->
<link rel="stylesheet" href="{{ asset('vendor/frappe-gantt/frappe-gantt.css') }}">
<script src="{{ asset('vendor/frappe-gantt/frappe-gantt.min.js') }}"></script>

<script>
    let gantt;
    let tasks = [];
    let selectedParentId = null;

    function showModal(parentId) {
        selectedParentId = parentId;
        document.getElementById('subtask-modal').classList.remove('hidden');
        document.getElementById('subtask-title').value = '';
        document.getElementById('subtask-start').value = '';
        document.getElementById('subtask-end').value = '';
    }

    function hideModal() {
        document.getElementById('subtask-modal').classList.add('hidden');
    }

    function loadTasks() {
    fetch("/projects/{{ $project->id }}/tasks")
        .then(res => res.json())
        .then(data => {
            console.log("✅ Frappe Gantt Data:", data);
            gantt = new Gantt("#gantt_here", data, {
                on_click: task => showModal(task.id),
                on_date_change: (task, start, end) => {
                    fetch(`/api/tasks/${task.id}`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            start: start.toISOString().slice(0, 10),
                            end: end.toISOString().slice(0, 10),
                        }),
                    });
                },
            });
        });
}


    document.getElementById("confirm-add-subtask").addEventListener("click", () => {
        const title = document.getElementById("subtask-title").value;
        const start = document.getElementById("subtask-start").value;
        const end = document.getElementById("subtask-end").value;

        if (!title || !start || !end) {
            alert("กรุณากรอกข้อมูลให้ครบ");
            return;
        }

        fetch("/api/tasks", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                title,
                start,
                end,
                project_id: {{ $project->id }},
                parent_id: selectedParentId,
            }),
        }).then(() => {
            hideModal();
            loadTasks();
        });
    });

    // โหลดงานเมื่อเปิดหน้า
    loadTasks();
</script>
@endsection

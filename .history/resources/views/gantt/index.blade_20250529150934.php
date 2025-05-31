@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📅 Gantt Chart: {{ $project->project_name_th }}</h2>
    <div id="gantt_here" class="overflow-x-auto border rounded bg-white shadow" style="min-height: 500px;"></div>

    <!-- Debug Output -->
    <div class="mt-6 p-4 bg-gray-100 text-xs rounded border">
        <strong>🔍 Debug Output:</strong>
        <pre id="debug-output" class="mt-2 text-gray-700 whitespace-pre-wrap"></pre>
    </div>
</div>

<!-- Modal เพิ่มงานรอง -->
<div id="subtask-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 items-center justify-center">
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

<!-- Frappe Gantt -->
<link rel="stylesheet" href="{{ asset('vendor/frappe-gantt/frappe-gantt.css') }}">
<script src="{{ asset('vendor/frappe-gantt/frappe-gantt.min.js') }}"></script>

<script>
let gantt;
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

function renderGantt(data) {
    try {
        if (typeof Gantt === 'undefined') {
            throw new Error("Frappe Gantt is not loaded.");
        }

        // ล้างก่อน render ใหม่
        document.getElementById('gantt_here').innerHTML = '';

        gantt = new Gantt("#gantt_here", data, {
            on_click: task => {
                console.log("🟢 คลิก Task:", task);
                showModal(task.id);
            },
            on_date_change: (task, start, end) => {
                console.log(`📅 เปลี่ยนวัน: ${task.id} → ${start.toISOString()} → ${end.toISOString()}`);
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
                }).then(() => console.log("✅ บันทึกการเปลี่ยนวันสำเร็จ"));
            },
            custom_popup_html: task => `
                <div class="bg-white p-2 rounded shadow text-sm">
                    <strong>${task.name}</strong><br>
                    📅 ${new Date(task.start).toDateString()} → ${new Date(task.end).toDateString()}
                </div>
            `
        });

        setTimeout(() => {
            const svg = document.querySelector('#gantt_here svg');
            if (svg) {
                console.log("✅ Gantt SVG Rendered:", svg);
            } else {
                console.warn("⚠️ Gantt SVG not rendered!");
            }
        }, 300);

    } catch (err) {
        console.error("❌ Gantt Error:", err);
        document.getElementById('debug-output').textContent += `❌ Gantt Error: ${err.message}\n`;
    }
}

function loadTasks() {
    fetch("/projects/{{ $project->id }}/tasks")
        .then(res => res.json())
        .then(data => {
            console.log("✅ Frappe Gantt Data:", data);
            document.getElementById('debug-output').textContent = JSON.stringify(data, null, 2);
            renderGantt(data);
        })
        .catch(err => {
            console.error("❌ โหลดข้อมูล Gantt ผิดพลาด:", err);
            document.getElementById('debug-output').textContent = `❌ โหลดข้อมูล Gantt ผิดพลาด: ${err.message}`;
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

// เริ่มโหลดเมื่อเปิดหน้า
loadTasks();
</script>

<style>
#gantt_here {
    min-height: 500px;
    height: 500px;
}

/* ซ่อน label ซ้ายและแสดงด้านขวาแทน */
.gantt .bar-label {
    display: none;
}
.gantt .bar-wrapper {
    position: relative;
}
.task-label-right {
    position: absolute;
    left: 105%;
    top: 50%;
    transform: translateY(-50%);
    white-space: nowrap;
    font-size: 12px;
    color: #333;
}
</style>
@endsection

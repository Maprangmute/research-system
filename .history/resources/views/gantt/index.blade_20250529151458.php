@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📅 Gantt Chart: Mock Test</h2>

    <div id="gantt_here" class="overflow-x-auto border rounded bg-white shadow" style="min-height: 500px;"></div>

    <!-- Debug Output -->
    <div class="mt-6 p-4 bg-gray-100 text-xs rounded border">
        <strong>🔍 Debug Output:</strong>
        <pre id="debug-output" class="mt-2 text-gray-700 whitespace-pre-wrap"></pre>
    </div>
</div>

<!-- Frappe Gantt (local) -->
<link rel="stylesheet" href="{{ asset('vendor/frappe-gantt/frappe-gantt.css') }}">
<script src="{{ asset('vendor/frappe-gantt/frappe-gantt.min.js') }}"></script>

<script>
function renderMockGantt() {
    const testTasks = [
        {
            id: 1,
            name: "Mock Task 1",
            start: new Date().toISOString().slice(0, 10),
            end: new Date(Date.now() + 3 * 86400000).toISOString().slice(0, 10), // +3 days
            progress: 50,
            dependencies: [],
            parent: null
        },
        {
            id: 2,
            name: "Mock Subtask",
            start: new Date(Date.now() + 1 * 86400000).toISOString().slice(0, 10),
            end: new Date(Date.now() + 2 * 86400000).toISOString().slice(0, 10),
            progress: 20,
            dependencies: [],
            parent: 1
        }
    ];

    console.log("🧪 Mock Data:", testTasks);
    document.getElementById('debug-output').textContent = JSON.stringify(testTasks, null, 2);

    // ล้าง DOM ก่อนวาดใหม่
    document.getElementById('gantt_here').innerHTML = '';

    // วาด Gantt
    const gantt = new Gantt("#gantt_here", testTasks, {
        on_click: task => console.log("🟢 คลิก:", task),
        on_date_change: (task, start, end) => {
            console.log(`📅 เปลี่ยนวัน ${task.id}: ${start} → ${end}`);
        },
        custom_popup_html: task => `
            <div class="bg-white p-2 rounded shadow text-sm">
                <strong>${task.name}</strong><br>
                📅 ${new Date(task.start).toDateString()} → ${new Date(task.end).toDateString()}
            </div>
        `
    });

    // ตรวจว่า SVG วาดแล้วหรือยัง
    setTimeout(() => {
        const svg = document.querySelector('#gantt_here svg');
        if (svg) {
            console.log("✅ Gantt SVG rendered");
        } else {
            console.warn("⚠️ Gantt SVG NOT rendered");
        }
    }, 300);
}

// เรียกเมื่อโหลดหน้า
document.addEventListener("DOMContentLoaded", renderMockGantt);
</script>

<style>
#gantt_here {
    min-height: 500px;
    height: 500px;
}

/* แสดงชื่อ task ด้านขวาแทน */
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

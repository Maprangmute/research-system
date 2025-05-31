@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📅 Gantt Chart (CDN Version)</h2>

    <div id="gantt_here" class="overflow-x-auto border rounded bg-white shadow" style="min-height: 500px;"></div>

    <div class="mt-6 p-4 bg-gray-100 text-xs rounded border">
        <strong>🔍 Debug Output:</strong>
        <pre id="debug-output" class="mt-2 text-gray-700 whitespace-pre-wrap"></pre>
    </div>
</div>

<!-- ✅ Frappe Gantt from CDN -->

<!-- Frappe Gantt CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@1.0.3/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@1.0.3/dist/frappe-gantt.umd.min.js"></script>


<script>
function renderGantt() {
    const testTasks = [
        {
            id: 1,
            name: "รวบรวมความต้องการ",
            start: "2025-06-01",
            end: "2025-06-07",
            progress: 30,
            dependencies: [],
            parent: null
        },
        {
            id: 2,
            name: "สัมภาษณ์ผู้ใช้",
            start: "2025-06-02",
            end: "2025-06-04",
            progress: 60,
            dependencies: [],
            parent: 1
        },
        {
            id: 3,
            name: "เขียนเอกสาร BRD",
            start: "2025-06-05",
            end: "2025-06-07",
            progress: 10,
            dependencies: [],
            parent: 1
        }
    ];

    document.getElementById('debug-output').textContent = JSON.stringify(testTasks, null, 2);

    document.getElementById('gantt_here').innerHTML = '';

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
}

document.addEventListener("DOMContentLoaded", renderGantt);
</script>
@endsection

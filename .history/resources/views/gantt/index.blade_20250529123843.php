@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6">
    <h2 class="text-2xl font-bold mb-4">📅 แผนงานโครงการ: {{ $project->project_name_th ?? '-' }}</h2>
    <button id="add-task" class="bg-green-600 text-white px-4 py-2 rounded mb-4">➕ เพิ่มงาน</button>

    <div id="gantt" class="border border-gray-300 rounded p-4 min-h-[300px] bg-white">
        <p id="loading-msg" class="text-gray-400">⏳ กำลังโหลดข้อมูล...</p>
    </div>
</div>

<!-- Frappe Gantt -->
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.css"/>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ganttContainer = document.getElementById("gantt");
        const loadingMsg = document.getElementById("loading-msg");

        fetch(`/projects/{{ $project->id }}/tasks`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(json => {
                console.log("📦 TASK DATA", json); // 🐛 DEBUG: console output

                const tasks = json.data || [];

                if (tasks.length === 0) {
                    loadingMsg.textContent = 'ยังไม่มีงานในโครงการนี้';
                    return;
                }

                loadingMsg.remove(); // ลบข้อความ loading

                const gantt = new Gantt("#gantt", tasks, {
                    on_click: task => {
                        console.log("🖱️ คลิกงาน:", task);
                        alert(`คุณคลิกงาน: ${task.name}`);
                    },
                    on_date_change: (task, start, end) => {
                        console.log("📅 เปลี่ยนวันที่:", task, start, end);

                        fetch(`/tasks/${task.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                start: start.toISOString().slice(0, 10),
                                end: end.toISOString().slice(0, 10)
                            })
                        }).then(res => {
                            console.log('📝 อัปเดตสำเร็จ:', res.status);
                        }).catch(err => {
                            console.error("❌ PUT error", err);
                        });
                    }
                });
            })
            .catch(err => {
                console.error("❗ FETCH error:", err);
                ganttContainer.innerHTML = '<p class="text-red-600">❌ โหลดข้อมูลไม่สำเร็จ</p>';
            });
    });
</script>
@endsection

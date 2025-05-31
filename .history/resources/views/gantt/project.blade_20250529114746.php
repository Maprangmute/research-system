// resources/views/gantt/project.blade.php
@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">📆 แผนงานโครงการ: {{ $project->project_name_th }}</h2>
    <div class="mb-4">
        <button onclick="openTaskForm()" class="bg-green-600 text-white px-4 py-2 rounded">➕ เพิ่มงาน</button>
    </div>
    <div id="gantt" class="border border-gray-300 rounded" style="height: 600px; min-height: 300px;"></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css"/>

<script>
    let projectId = {{ $project->id }};

    function renderGantt(tasks) {
        const gantt = new Gantt("#gantt", tasks, {
            view_mode: 'Day',
            date_format: 'YYYY-MM-DD',
            on_click: task => console.log("Clicked", task),
            on_date_change: (task, start, end) => {
                if (task.id === 'placeholder') return;
                fetch(`/api/tasks/${task.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ start, end })
                });
            },
            custom_popup_html: task => `
                <div class="p-2">
                    <strong>${task.name}</strong><br>
                    ${task.start} → ${task.end}
                </div>`
        });

        window.gantt = gantt;
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetch(`/api/projects/${projectId}/tasks`)
            .then(res => res.json())
            .then(data => {
                let tasks = data.data;
                if (!tasks || !tasks.length) {
                    const today = new Date().toISOString().slice(0, 10);
                    tasks = [{
                        id: 'placeholder',
                        name: 'ยังไม่มีงานในโครงการนี้',
                        start: today,
                        end: today,
                        progress: 0,
                        custom_class: 'opacity-30 pointer-events-none'
                    }];
                }
                renderGantt(tasks);
            });
    });

    function openTaskForm() {
        const title = prompt("ชื่อภารกิจใหม่:");
        if (!title) return;
        const start = prompt("วันที่เริ่ม (YYYY-MM-DD):", new Date().toISOString().slice(0, 10));
        const end = prompt("วันที่สิ้นสุด (YYYY-MM-DD):", new Date().toISOString().slice(0, 10));

        fetch('/api/tasks', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, start, end, project_id: projectId })
        })
        .then(res => res.json())
        .then(response => {
            const task = response.data;
            const ganttContainer = document.getElementById('gantt');
            if (window.gantt.get_task && window.gantt.get_task('placeholder')) {
                ganttContainer.innerHTML = '';
                renderGantt([task]);
            } else {
                window.gantt.add_task(task);
            }
        });
    }
</script>
@endpush
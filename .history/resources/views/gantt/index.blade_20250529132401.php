@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6">
    <h2 class="text-2xl font-bold mb-4">📅 แผนงานโครงการ: {{ $project->project_name_th ?? '-' }}</h2>
    <button id="add-task" class="bg-green-600 text-white px-4 py-2 rounded mb-4">➕ เพิ่มงาน</button>

    <div id="gantt" class="bg-white border rounded shadow p-4"></div>
</div>



<!-- Gantt.js -->
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.css"/>

<style>
    #gantt svg {
        width: 100%;
        min-height: 400px;
    }
    .bar-done rect {
        fill: #4ade80 !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ganttContainer = document.getElementById("gantt");
    let allTasks = [];
    let visibleTasks = [];

    const renderGantt = () => {
        ganttContainer.innerHTML = '';
        if (visibleTasks.length === 0) {
            ganttContainer.innerHTML = '<p class="text-gray-500">ยังไม่มีงานในโครงการนี้</p>';
            return;
        }

        console.log("[DEBUG] Rendering visible tasks:", visibleTasks);

        new Gantt("#gantt", visibleTasks, {
            view_mode: 'Day',
            on_click: task => toggleCollapse(task.id),
            on_date_change: (task, start, end) => {
                console.log("[DEBUG] Task updated:", task);
                fetch(`/tasks/${task.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        start: start.toISOString().slice(0,10),
                        end: end.toISOString().slice(0,10)
                    })
                });
            }
        });
    };

    const toggleCollapse = (taskId) => {
        const parentTask = allTasks.find(t => t.id === taskId);
        if (!parentTask) return;
        parentTask.collapsed = !parentTask.collapsed;
        filterVisibleTasks();
        renderGantt();
    };

    const filterVisibleTasks = () => {
        visibleTasks = [];
        allTasks.forEach(task => {
            if (!task.parent_id) {
                visibleTasks.push(task);
                if (!task.collapsed) {
                    visibleTasks.push(...allTasks.filter(sub => sub.parent_id === task.id));
                }
            }
        });
    };

    fetch(`/projects/{{ $project->id }}/tasks`)
        .then(res => res.json())
        .then(json => {
            console.log("[DEBUG] Loaded task data:", json);
            allTasks = (json.data || []).map(t => ({
                id: t.id,
                name: t.name || t.title,
                start: t.start,
                end: t.end,
                progress: t.progress ?? 0,
                dependencies: (t.dependencies || []).join(','),
                custom_class: '',
                parent_id: t.parent_id ?? null,
                collapsed: false,
            }));
            filterVisibleTasks();
            renderGantt();
        })
        .catch(err => {
            console.error("[ERROR] Cannot load Gantt data:", err);
            ganttContainer.innerHTML = '<p class="text-red-600">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>';
        });

    document.getElementById('add-task').addEventListener('click', () => {
        const title = prompt('กรอกชื่องาน');
        if (!title) return;
        const parentId = prompt('ใส่ ID งานหลัก (ถ้ามี)');
        fetch('/tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                title: title,
                project_id: {{ $project->id }},
                start: new Date().toISOString().slice(0,10),
                end: new Date().toISOString().slice(0,10),
                parent_id: parentId || null
            })
        }).then(() => location.reload());
    });
});
</script>
@endsection

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
                console.log("[DEBUG] No visible tasks to render");
                return;
            }

            console.log("[DEBUG] Rendering visible tasks:", visibleTasks);
            new Gantt("#gantt", visibleTasks, {
                view_mode: 'Day',
                on_click: task => {
                    toggleCollapse(task.id);
                },
                on_date_change: (task, start, end) => {
                    console.log("[DEBUG] Date changed for task", task);
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
                    });
                }
            });
        };

        const toggleCollapse = (taskId) => {
            const parentTask = allTasks.find(t => t.id === taskId);
            if (parentTask) {
                parentTask.collapsed = !parentTask.collapsed;
                console.log("[DEBUG] Toggled collapse for:", parentTask);
                filterVisibleTasks();
                renderGantt();
            }
        };

        const filterVisibleTasks = () => {
            visibleTasks = [];
            allTasks.forEach(task => {
                if (!task.parent_id) {
                    visibleTasks.push(task);
                    if (!task.collapsed) {
                        allTasks.filter(t => t.parent_id === task.id).forEach(sub => {
                            visibleTasks.push(sub);
                        });
                    }
                }
            });
            console.log("[DEBUG] Filtered visible tasks:", visibleTasks);
        };

        fetch(`/projects/{{ $project->id }}/tasks`)
            .then(res => res.json())
            .then(json => {
                console.log("[DEBUG] Loaded task data:", json);
                allTasks = (json.data || []).map(t => ({ ...t, collapsed: false }));
                filterVisibleTasks();
                renderGantt();
            })
            .catch(err => {
                console.error("[ERROR] Failed to load task data:", err);
                ganttContainer.innerHTML = '<p class="text-red-600">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>';
            });

        document.getElementById('add-task').addEventListener('click', () => {
            const title = prompt('กรอกชื่องาน');
            const parentId = prompt('หากเป็นงานย่อย ให้ใส่ ID งานหลัก (หรือเว้นว่าง)');
            if (!title) return;

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

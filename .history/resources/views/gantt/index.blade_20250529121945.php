@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">📅 แผนงานโครงการ: {{ $project->project_name_th ?? '-' }}</h2>

    <button id="addTaskBtn" class="mb-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
        + เพิ่มงาน
    </button>

    <div id="gantt"></div>
</div>

<!-- 👇 ติดตั้ง Frappe Gantt -->
<link rel="stylesheet" href="https://unpkg.com/frappe-gantt/dist/frappe-gantt.css">
<script src="https://unpkg.com/frappe-gantt/dist/frappe-gantt.min.js"></script>

<script>
    const projectId = @json($project->id);
    let tasks = [];

    fetch(`/projects/${projectId}/tasks`)
        .then(res => res.json())
        .then(data => {
            tasks = data.data.length ? data.data : [{
                id: 'placeholder',
                name: 'ยังไม่มีงาน',
                start: new Date().toISOString().split('T')[0],
                end: new Date().toISOString().split('T')[0],
                progress: 0,
            }];
            renderGantt();
        });

    function renderGantt() {
        const gantt = new Gantt("#gantt", tasks, {
            on_date_change: (task, start, end) => {
                if (task.id === 'placeholder') return;
                fetch(`/tasks/${task.id}`, {
                    method: "PUT",
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ start, end })
                });
            }
        });
    }

    document.getElementById('addTaskBtn').addEventListener('click', () => {
        const today = new Date().toISOString().split('T')[0];
        fetch("/tasks", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            },
            body: JSON.stringify({
                title: "งานใหม่",
                start: today,
                end: today,
                project_id: projectId
            })
        }).then(() => location.reload());
    });
</script>
@endsection

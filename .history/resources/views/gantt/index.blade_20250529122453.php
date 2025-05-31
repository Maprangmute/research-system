@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-6">
    <h2 class="text-xl font-bold mb-4">📅 แผนงานโครงการ: {{ $project->project_name_th }}</h2>
    <button id="addTask" class="mb-2 px-4 py-2 bg-green-600 text-white rounded">➕ เพิ่มงาน</button>
    <div id="gantt" class="border rounded" style="height: 600px;"></div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/frappe-gantt/dist/frappe-gantt.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const projectId = {{ $project->id }};
    const ganttContainer = document.getElementById("gantt");

    fetch(`/projects/${projectId}/tasks`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(res => {
        const tasks = res.data.length ? res.data : [{
            id: 'placeholder',
            name: 'เริ่มต้น',
            start: new Date().toISOString().substring(0,10),
            end: new Date().toISOString().substring(0,10),
            progress: 0,
            dependencies: [],
        }];

        const gantt = new Gantt("#gantt", tasks, {
            on_date_change(task, start, end) {
                fetch(`/tasks/${task.id}`, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ start, end })
                });
            },
            on_click(task) {
                console.log("Task clicked:", task);
            }
        });

        document.getElementById("addTask").addEventListener("click", () => {
            const today = new Date().toISOString().substring(0,10);
            fetch("/tasks", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    title: "งานใหม่",
                    start: today,
                    end: today,
                    project_id: projectId,
                    progress: 0
                })
            }).then(() => location.reload());
        });
    });
});
</script>
@endsection

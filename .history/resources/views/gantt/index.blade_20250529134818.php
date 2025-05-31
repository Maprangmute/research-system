@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6">
    <h2 class="text-2xl font-bold mb-4">📅 แผนงานโครงการ: {{ $project->project_name_th }}</h2>
    <button id="add-task" class="bg-green-600 text-white px-4 py-2 rounded mb-4">➕ เพิ่มงาน</button>

    <div id="gantt" class="bg-white border rounded shadow p-4" style="height: 500px;"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.css"/>

<script>
document.addEventListener('DOMContentLoaded', function () {
    fetch(`/projects/{{ $project->id }}/tasks`)
        .then(res => res.json())
        .then(json => {
            const tasks = json.data || [];

            if (tasks.length === 0) {
                document.getElementById("gantt").innerHTML = "<p>ยังไม่มีงาน</p>";
                return;
            }

            new Gantt("#gantt", tasks, {
                view_mode: 'Day',
                on_click: task => alert(task.name),
                on_date_change: (task, start, end) => {
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
        });
});
</script>

@endsection

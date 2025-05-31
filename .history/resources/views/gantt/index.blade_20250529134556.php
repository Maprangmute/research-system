@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6">
    <h2 class="text-2xl font-bold mb-4">📅 แผนงานโครงการ: {{ $project->project_name_th }}</h2>
    <button id="add-task" class="bg-green-600 text-white px-4 py-2 rounded mb-4">➕ เพิ่มงาน</button>

    <div id="gantt" class="bg-white border rounded shadow p-4"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.css"/>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ganttContainer = document.getElementById("gantt");

    fetch(`/projects/{{ $project->id }}/tasks`)
        .then(res => res.json())
        .then(json => {
            const tasks = json.data || [];

            if (tasks.length === 0) {
                ganttContainer.innerHTML = '<p class="text-gray-500">ยังไม่มีงานในโครงการนี้</p>';
                return;
            }

            const gantt = new Gantt("#gantt", tasks, {
                view_mode: 'Day',
                on_click: task => {
                    alert(`📝 งาน: ${task.name}`);
                },
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
                    }).then(() => console.log('Updated!'));
                }
            });
        });
});
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">📆 แผนงานโครงการ: {{ $project->project_name_th }}</h2>
    <div class="mb-4">
        <button onclick="openTaskForm()" class="bg-green-600 text-white px-4 py-2 rounded">➕ เพิ่มงาน</button>
    </div>
    <div id="gantt"></div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/frappe-gantt"></script>
<script>
    let projectId = {{ $project->id }};
    fetch(`/api/projects/${projectId}/tasks`)
        .then(res => res.json())
        .then(data => {
            window.gantt = new Gantt("#gantt", data.data, {
                on_click: task => console.log("Clicked", task),
                on_date_change: (task, start, end) => {
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
        });

    function openTaskForm() {
        const title = prompt("ชื่อภารกิจใหม่:");
        if (!title) return;
        const start = prompt("วันที่เริ่ม (YYYY-MM-DD):");
        const end = prompt("วันที่สิ้นสุด (YYYY-MM-DD):");

        fetch('/api/tasks', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, start, end, project_id: projectId })
        })
        .then(res => res.json())
        .then(response => {
            const task = response.data;
            window.gantt.add_task(task);
        });
    }
</script>
@endpush
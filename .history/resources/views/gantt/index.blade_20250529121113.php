@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">📅 Gantt Chart: {{ $project->project_name_th }}</h1>

    <div id="gantt" class="border rounded shadow" style="height: 600px;"></div>
</div>
@endsection

@section('scripts')
<!-- Frappe Gantt (CDN) -->
<script src="https://unpkg.com/frappe-gantt/dist/frappe-gantt.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/frappe-gantt/dist/frappe-gantt.css" />

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const response = await fetch(`/api/projects/{{ $project->id }}/tasks`);
    const { data } = await response.json();

    const gantt = new Gantt("#gantt", data, {
        on_date_change: async (task, start, end) => {
            await fetch(`/api/tasks/${task.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    start: start.toISOString().split('T')[0],
                    end: end.toISOString().split('T')[0]
                }),
            });
        },
        on_click: task => alert(`คลิก: ${task.name}`)
    });
});
</script>
@endsection

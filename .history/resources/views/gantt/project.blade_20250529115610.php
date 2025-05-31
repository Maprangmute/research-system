@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">📅 แผนงานโครงการ: {{ $project->project_name_th }}</h2>

    <button id="add-task" class="bg-green-600 text-white px-4 py-2 rounded shadow mb-4">+ เพิ่มงาน</button>

    <div id="gantt"></div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/frappe-gantt/dist/frappe-gantt.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/frappe-gantt/dist/frappe-gantt.css" />

<script>
    let projectId = @json($project->id);

    const ganttTarget = document.getElementById('gantt');
    const gantt = new Gantt(ganttTarget, [], {
        on_date_change(task, start, end) {
            axios.put(`/api/tasks/${task.id}`, {
                start: start.toISOString().split('T')[0],
                end: end.toISOString().split('T')[0],
            });
        },
        on_click(task) {
            console.log('Task clicked', task);
        },
        on_progress_change(task, progress) {
            axios.put(`/api/tasks/${task.id}`, {
                progress: Math.round(progress)
            });
        },
        on_view_change(view_mode) {
            console.log('View mode changed to', view_mode);
        }
    });

    function loadTasks() {
        axios.get(`/api/projects/${projectId}/tasks`)
            .then(res => {
                gantt.refresh(res.data.data);
            });
    }

    document.getElementById('add-task').addEventListener('click', () => {
        const today = new Date().toISOString().split('T')[0];
        const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];

        axios.post('/api/tasks', {
            title: 'New Task',
            start: today,
            end: tomorrow,
            project_id: projectId
        }).then(() => loadTasks());
    });

    loadTasks();
</script>
@endpush

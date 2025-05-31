@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6 space-y-4">
    <h2 class="text-2xl font-bold">📅 Gantt Chart: {{ $project->project_name_th }}</h2>

    <div class="bg-white p-4 rounded shadow" id="dpGantt" style="height: 500px;"></div>
</div>

<link rel="stylesheet" href="https://cdn.daypilot.org/daypilot-lite.min.css">
<script src="https://cdn.daypilot.org/daypilot-lite.min.js"></script>

<script>
    const dp = new DayPilot.Gantt("dpGantt");

    dp.startDate = DayPilot.Date.today().firstDayOfMonth();
    dp.days = 31;
    dp.scale = "Day";
    dp.treeEnabled = true;
    dp.rowMoveHandling = "Update";
    dp.onTimeRangeSelected = function (args) {
        const name = prompt("ชื่องาน:");
        if (!name) return;

        const newTask = {
            start: args.start,
            end: args.end,
            id: DayPilot.guid(),
            text: name
        };

        dp.tasks.add(newTask);

        fetch('/tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                title: name,
                start: args.start.toString(),
                end: args.end.toString(),
                project_id: {{ $project->id }},
            })
        });
    };

    dp.onTaskMoved = function(args) {
        fetch(`/tasks/${args.task.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                start: args.newStart.toString(),
                end: args.newEnd.toString(),
            })
        });
    };

    dp.init();

    fetch("/projects/{{ $project->id }}/tasks")
        .then(res => res.json())
        .then(data => {
            dp.tasks.list = data.map(t => ({
                id: t.id,
                text: t.name,
                start: t.start,
                end: t.end,
                parent: t.parent_id ?? null
            }));
            dp.update();
        });
</script>
@endsection

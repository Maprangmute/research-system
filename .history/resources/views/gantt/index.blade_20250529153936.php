@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-4">
    <h2 class="text-xl font-bold mb-4">📅 Gantt: {{ $project->project_name_th }}</h2>
    <div id="dp"></div>
</div>

<!-- ✅ DayPilot Assets -->
<!-- ลิงก์ CSS (ถ้ามี) -->
<link rel="stylesheet" href="{{ asset('vendor/daypilot/daypilot-lite.min.css') }}">

<!-- ลิงก์ JavaScript -->
<script src="{{ asset('vendor/daypilot/daypilot-all.min.js') }}"></script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const dp = new DayPilot.Gantt("dp", {
        startDate: DayPilot.Date.today(),
        days: 30,
        showResources: false,
        rowHeaderColumns: [
            { title: "กิจกรรม", display: "text", width: 220 }
        ],
        onTaskMoved: args => updateTask(args),
        onTaskResized: args => updateTask(args),
    });

    dp.init();

    // โหลดข้อมูล
    fetch("/projects/{{ $project->id }}/tasks")
        .then(res => res.json())
        .then(data => {
            dp.tasks.list = data.map(task => ({
                id: task.id,
                text: task.title,
                start: task.start,
                end: task.end,
                parent: task.parent_id,
            }));
            dp.update();
        });

    // บันทึกเมื่อมีการย้าย/ปรับเวลา
    function updateTask(args) {
        fetch(`/tasks/${args.task.id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                start: args.task.start.value,
                end: args.task.end.value,
                parent_id: args.task.parent || null,
            }),
        }).then(() => console.log("✅ Updated:", args.task.id));
    }
});
</script>
@endsection

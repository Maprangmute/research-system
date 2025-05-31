@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-4">
    <h2 class="text-xl font-bold mb-4">📊 DHTMLX Gantt: {{ $project->project_name_th }}</h2>
    <div id="gantt_here" style="width: 100%; height: 500px;"></div>
</div>

<!-- DHTMLX Gantt -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    gantt.config.xml_date = "%Y-%m-%d";
    gantt.init("gantt_here");

    gantt.load("/projects/{{ $project->id }}/tasks");

    // Save changes (drag, resize, edit)
    const dp = new gantt.dataProcessor("/tasks");
    dp.init(gantt);
    dp.setTransactionMode("REST");
});
</script>
@endsection

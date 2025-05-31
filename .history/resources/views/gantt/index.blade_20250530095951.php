@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📅 Gantt Chart: {{ $project->project_name_th }}</h2>
    <div class="bg-white p-4 rounded shadow" id="gantt_here" style="height: 600px;"></div>
</div>

<!-- ✅ DHTMLX Gantt from local (you already installed) -->
<link rel="stylesheet" href="{{ asset('vendor/dhtmlxgantt/dhtmlxgantt.css') }}">
<script src="{{ asset('vendor/dhtmlxgantt/dhtmlxgantt.js') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    gantt.config.xml_date = "%Y-%m-%d";
    gantt.config.date_grid = "%d %M %Y";

    // ✅ Format date in Thai
    function formatDateThai(date) {
        const monthsThai = [
            "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
            "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
        ];
        const d = new Date(date);
        const day = d.getDate();
        const month = monthsThai[d.getMonth()];
        const year = d.getFullYear() + 543;
        return `${day} ${month} ${year}`;
    }

    // ✅ Override Gantt text display
    gantt.templates.date_grid = date => formatDateThai(date);
    gantt.templates.tooltip_date_format = date => formatDateThai(date);
    gantt.templates.task_date = (start, end) => `${formatDateThai(start)} - ${formatDateThai(end)}`;

    // ✅ Initialize
    gantt.init("gantt_here");

    // ✅ Load from Laravel API
    gantt.load("/projects/{{ $project->id }}/tasks");

    // ✅ Enable dataProcessor (PUT/POST)
    const dp = new gantt.dataProcessor("/tasks");
    dp.init(gantt);
    dp.setTransactionMode("REST");
});
</script>
@endsection

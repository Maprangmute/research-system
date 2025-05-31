@extends('layouts.app')

@section('content')
<div class="max-w-full mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📅 Gantt Chart: {{ $project->project_name_th }}</h2>
    <div class="bg-white p-4 rounded shadow">
        <div id="gantt_here" style="width: 100%; height: 800px;"></div>
    </div>
</div>

<!-- ✅ DHTMLX Gantt -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    gantt.config.xml_date = "%Y-%m-%d";

    // ✅ ขยายขนาดคอลัมน์ที่แสดงข้อมูล
    gantt.config.columns = [
        { name: "text", label: "Task name", width: 220, tree: true },
        {
            name: "start_date", label: "Start", align: "center", width: 120,
            template: function (task) {
                return formatDateThai(task.start_date);
            }
        },
        {
            name: "end_date", label: "End", align: "center", width: 120,
            template: function (task) {
                return formatDateThai(task.end_date);
            }
        },
        {
            name: "status", label: "Status", align: "center", width: 100,
            template: function (task) {
                return task.status === 'done' ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ";
            }
        }
    ];

    // ✅ Format ภาษาไทย
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

    gantt.templates.date_grid = date => formatDateThai(date);
    gantt.templates.tooltip_date_format = date => formatDateThai(date);
    gantt.templates.task_date = (start, end) => `${formatDateThai(start)} - ${formatDateThai(end)}`;

    // ✅ เริ่มการโหลด
    gantt.init("gantt_here");
    gantt.load("/projects/{{ $project->id }}/tasks");

    const dp = new gantt.dataProcessor("/tasks");
    dp.init(gantt);
    dp.setTransactionMode("REST");
});
</script>
@endsection

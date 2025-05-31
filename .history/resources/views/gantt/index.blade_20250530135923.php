@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<!-- ✅ DHTMLX Gantt (Stable v7.1 with marker support) -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/7.1/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/7.1/dhtmlxgantt.js"></script>

<div class="max-w-full mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📊 Gantt Chart: {{ $project->project_name_th }}</h2>

    <div class="bg-white p-4 rounded shadow">
        <!-- View Switcher -->
        <div class="flex justify-end mb-4">
            <div class="relative inline-block text-left">
                <button id="scale-button" onclick="toggleScaleDropdown()"
                    class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                    🗂 เปลี่ยนมุมมอง
                    <svg class="-mr-1 ml-2 h-5 w-5" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="scale-dropdown"
                    class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden z-50">
                    <div class="py-1">
                        <button onclick="setScale('week')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            🗓 รายสัปดาห์
                        </button>
                        <button onclick="setScale('month')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            📆 รายเดือน
                        </button>
                        <button onclick="setScale('year')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            🗂 รายปี
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="gantt_here" class="w-full h-[800px] overflow-x-auto"></div>

        <div class="mt-4 text-right">
            <button onclick="saveAllTasks()" class="px-4 py-2 bg-green-600 text-white rounded">
                💾 บันทึกทั้งหมด
            </button>
        </div>
    </div>
</div>

<script>
    function toggleScaleDropdown() {
        const menu = document.getElementById("scale-dropdown");
        menu.classList.toggle("hidden");
        document.addEventListener("click", function handler(e) {
            if (!menu.contains(e.target) && !document.getElementById("scale-button").contains(e.target)) {
                menu.classList.add("hidden");
                document.removeEventListener("click", handler);
            }
        });
    }

    function setScale(scale) {
        const daysThai = ["อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัส", "ศุกร์", "เสาร์"];
        const monthsThai = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
            "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];

        switch (scale) {
            case "week":
                gantt.config.scale_unit = "week";
                gantt.config.date_scale = "สัปดาห์ที่ #%W";
                gantt.config.subscales = [
                    {
                        unit: "day", step: 1,
                        date: date => `${daysThai[date.getDay()]} ${date.getDate()}`
                    }
                ];
                break;
            case "month":
                gantt.config.scale_unit = "month";
                gantt.config.date_scale = date => monthsThai[date.getMonth()];
                gantt.config.subscales = [
                    {
                        unit: "week", step: 1,
                        date: date => `${date.getDate()} ${monthsThai[date.getMonth()]}`
                    }
                ];
                break;
            case "year":
                gantt.config.scale_unit = "year";
                gantt.config.date_scale = date => `${date.getFullYear() + 543}`;
                gantt.config.subscales = [
                    {
                        unit: "month", step: 1,
                        date: date => monthsThai[date.getMonth()]
                    }
                ];
                break;
        }
        gantt.render();
        document.getElementById("scale-dropdown").classList.add("hidden");
    }

    function formatDateThai(date) {
        const monthsThai = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
            "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
        const d = new Date(date);
        return `${d.getDate()} ${monthsThai[d.getMonth()]} ${d.getFullYear() + 543}`;
    }

    function saveAllTasks() {
        const data = [];
        gantt.eachTask(task => {
            data.push({
                id: task.id,
                text: task.text,
                start_date: task.start_date,
                end_date: task.end_date,
                parent: task.parent,
                progress: task.progress,
                status: task.status,
                owner: task.owner
            });
        });

        fetch("/tasks/bulk-save", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ project_id: {{ $project->id }}, tasks: data })
        })
        .then(res => res.json())
        .then(response => {
            alert("✅ บันทึกสำเร็จ");
            gantt.clearAll();
            gantt.load("/projects/{{ $project->id }}/tasks");
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        flatpickr.localize(flatpickr.l10ns.th);

        gantt.config.xml_date = "%Y-%m-%d";
        gantt.config.row_height = 36;
        gantt.config.grid_resize = true;
        gantt.config.order_branch = true;
        gantt.config.order_branch_free = true;
        gantt.config.start_on_monday = true;

        gantt.config.columns = [
            { name: "text", label: "งาน", width: 220, tree: true },
            {
                name: "start_date", label: "เริ่ม", align: "center", width: 120,
                template: task => formatDateThai(task.start_date)
            },
            {
                name: "end_date", label: "สิ้นสุด", align: "center", width: 120,
                template: task => formatDateThai(task.end_date)
            },
            {
                name: "status", label: "สถานะ", align: "center", width: 100,
                template: task => task.status === 'done' ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ"
            },
            { name: "add", label: "", width: 44 }
        ];

        // ✅ Marker: วันนี้
        gantt.addMarker({
            start_date: new Date(),
            css: "today",
            text: "วันนี้",
            title: formatDateThai(new Date())
        });

        setScale("month");
        gantt.init("gantt_here");
        gantt.load("/projects/{{ $project->id }}/tasks");
    });

    
</script>
@endsection

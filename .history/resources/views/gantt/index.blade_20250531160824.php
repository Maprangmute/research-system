@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- ✅ Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<!-- ✅ DHTMLX Gantt -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<style>
    .gantt_marker.today { background-color: red; }
    .flatpickr-calendar { z-index: 999999 !important; position: absolute !important; }
    #task-panel {
        transition: transform 0.3s ease;
        max-width: 480px;
        margin: 0 auto;
        bottom: 0; left: 0; right: 0;
        border-top-left-radius: 1rem; border-top-right-radius: 1rem;
        max-height: 90vh; overflow-y: auto; overscroll-behavior: contain;
        z-index: 999998; pointer-events: auto;
    }
    #task-panel.hidden { transform: translateY(100%); }
</style>

<div class="max-w-full mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📊 Gantt Chart: {{ $project->project_name_th }}</h2>

    <div class="bg-white p-4 rounded shadow">
        <div class="flex flex-wrap justify-between mb-4 gap-2">
            <div class="flex items-center gap-2">
                <label for="scale-select" class="text-sm font-medium">มุมมอง:</label>
                <select id="scale-select" onchange="setScale(this.value)"
                    class="border border-gray-300 rounded px-3 py-1 text-sm">
                    <option value="day">📅 รายวัน</option>
                    <option value="week">🗓 สัปดาห์</option>
                    <option value="month" selected>📆 รายเดือน</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button onclick="saveAllTasks()" class="px-4 py-2 bg-green-600 text-white rounded">💾 บันทึกทั้งหมด</button>
            </div>
        </div>

        <div id="gantt_here" class="w-full min-h-[500px] max-h-[90vh] overflow-auto"></div>
    </div>
</div>

<!-- ✅ Slide Panel -->
<div id="task-panel" class="fixed bg-white shadow-xl p-4 z-50 hidden transform translate-y-full">
    <input type="hidden" id="task-id" data-parent="">
    <h3 class="text-lg font-bold mb-4">📝 งาน</h3>
    <p id="task-parent-label" class="text-sm text-gray-600 italic mb-2 hidden">
    📌 งานย่อยของ: <span id="parent-task-name" class="font-medium text-gray-800"></span>
</p>


    <div class="space-y-3">
        <label class="block">
            
            <span class="text-sm">ชื่อเรื่อง</span>
            <input id="task-title" class="w-full border p-2 rounded mt-1" type="text">
        </label>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="block">
                <span class="text-sm">วันที่เริ่มต้น</span>
                <input id="task-start" class="flatpickr-th w-full border p-2 rounded mt-1" type="text">
            </label>
            <label class="block">
                <span class="text-sm">วันที่สิ้นสุด</span>
                <input id="task-end" class="flatpickr-th w-full border p-2 rounded mt-1" type="text">
            </label>
        </div>

        <label class="block">
            <span class="text-sm">สถานะ</span>
            <select id="task-status" class="w-full border p-2 rounded mt-1">
                <option value="ongoing">🕒 กำลังทำ</option>
                <option value="done">✅ เสร็จแล้ว</option>
            </select>
        </label>

        <label class="block">
            <span class="text-sm">ผู้รับผิดชอบ</span>
            <input id="task-owner" class="w-full border p-2 rounded mt-1" type="text">
        </label>

        <label class="block">
            <span class="text-sm">ความคืบหน้า (%)</span>
            <input id="task-progress" type="number" min="0" max="100" step="5" class="w-full border p-2 rounded mt-1">
        </label>
    </div>

    <div class="mt-6 space-y-3">
    <div class="flex justify-between items-center">
        <!-- ปุ่มลบ -->
        <button id="delete-task" class="text-red-600 hover:underline hidden">🗑 ลบ</button>

        <!-- ปุ่มยกเลิกและบันทึก -->
        <div class="flex items-center gap-2">
            <button onclick="hidePanel()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
                ยกเลิก
            </button>
            <button id="save-task" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                💾 ลงข้อมูลในตาราง
            </button>
        </div>
    </div>

    <!-- หมายเหตุ -->
    <p class="text-xs text-gray-500 italic text-right">
        * ข้อมูลนี้จะไม่ถูกบันทึกลงฐานข้อมูลโดยตรง
    </p>
</div>

</div>

<script>
// ✅ ฟังก์ชันแสดง พ.ศ. ใน altInput และแก้ dropdown ปี
function toBuddhistWithDropdownFix(selectedDates, dateStr, instance) {
    const input = instance.altInput;
    if (input && selectedDates.length > 0) {
        const date = selectedDates[0];
        const day = date.getDate();
        const monthName = instance.l10n.months.longhand[date.getMonth()];
        const buddhistYear = date.getFullYear() + 543;
        input.value = `${day} ${monthName} ${buddhistYear}`;
    }

    // ✅ แก้ปีใน dropdown เป็น พ.ศ. ทุกครั้งที่ calendar เปิด
    const fixYearDropdown = () => {
        const yearInput = instance.calendarContainer.querySelector(".flatpickr-year");
        if (yearInput) {
            const realYear = parseInt(yearInput.value);
            if (!isNaN(realYear) && realYear < 3000) {
                yearInput.value = realYear + 543;
            }
        }
    };

    // ✅ แก้ปีซ้ำ ๆ ขณะเปิด calendar
    if (!instance._buddhistYearInterval) {
        instance._buddhistYearInterval = setInterval(() => {
            if (instance.isOpen) {
                fixYearDropdown();
            } else {
                clearInterval(instance._buddhistYearInterval);
                instance._buddhistYearInterval = null;
            }
        }, 100);
    }
}



// ✅ เรียกใช้ Flatpickr กับทุก .flatpickr-th
function initThaiDatepickers() {
    flatpickr(".flatpickr-th", {
        locale: "th",
        dateFormat: "Y-m-d",        // ค่า backend: ค.ศ.
        altInput: true,
        altFormat: "j F Y",         // แสดง: พ.ศ.
        appendTo: document.body,
        position: "auto center",
        disableMobile: true,
        prevArrow: "",
        nextArrow: "",
        onReady: toBuddhistWithDropdownFix,
        onOpen: toBuddhistWithDropdownFix,
        onClose: toBuddhistWithDropdownFix,
        onChange: toBuddhistWithDropdownFix,
        onMonthChange: toBuddhistWithDropdownFix,
        onYearChange: toBuddhistWithDropdownFix,
    });
}



function showPanel(task = {}) {
    const panel = document.getElementById("task-panel");
    panel.classList.remove("hidden");
    setTimeout(() => panel.classList.remove("translate-y-full"), 10);

    // Set ID and parent
    const taskIdEl = document.getElementById("task-id");
    taskIdEl.value = task.id || "";
    taskIdEl.dataset.parent = task.parent || "";

    // Show parent task name if exists
    const parentLabel = document.getElementById("task-parent-label");
    const parentNameSpan = document.getElementById("parent-task-name");
    if (
        task.parent &&
        task.parent !== gantt.config.root_id &&
        gantt.isTaskExists(task.parent) &&
        gantt.getTask(task.parent).text
    ) {
        const parentTask = gantt.getTask(task.parent);
        parentNameSpan.textContent = parentTask.text;
        parentLabel.classList.remove("hidden");
    } else {
        parentLabel.classList.add("hidden");
        parentNameSpan.textContent = "";
    }

    // Fill other fields
    document.getElementById("task-title").value = task.text || "";
    document.getElementById("task-status").value = task.status || "ongoing";
    document.getElementById("task-owner").value = task.owner || "";
    document.getElementById("task-progress").value = task.progress != null ? task.progress * 100 : 0;

    // Init Thai datepickers with Buddhist year dropdown
   setTimeout(() => {
    flatpickr.localize(flatpickr.l10ns.th);

    document.querySelectorAll(".flatpickr-th").forEach(input => {
        if (input._flatpickr) input._flatpickr.destroy();
    });

    initThaiDatepickers();

    // กำหนดค่าถ้ามี
    if (task.start_date) document.getElementById("task-start")._flatpickr.setDate(task.start_date, true);
    if (task.end_date) document.getElementById("task-end")._flatpickr.setDate(task.end_date, true);
}, 300);


    // Show or hide delete button
    document.getElementById("delete-task").classList.toggle("hidden", !task.id);
}


function hidePanel() {
    const panel = document.getElementById("task-panel");
    panel.classList.add("translate-y-full");
    setTimeout(() => panel.classList.add("hidden"), 300);
}

document.getElementById("save-task").addEventListener("click", () => {
    const id = document.getElementById("task-id").value;
    const parent = document.getElementById("task-id").dataset.parent || 0;
    const text = document.getElementById("task-title").value;
    const start = document.getElementById("task-start").value;
    const end = document.getElementById("task-end").value;
    const status = document.getElementById("task-status").value;
    const owner = document.getElementById("task-owner").value;
    const progress = parseInt(document.getElementById("task-progress").value || 0) / 100;

    if (!text.trim() || !start || !end) {
        alert("กรุณากรอกข้อมูลให้ครบถ้วน");
        return;
    }

    const startDate = new Date(start);
    const endDate = new Date(end);

    if (id) {
        const task = gantt.getTask(id);
        Object.assign(task, { text, start_date: startDate, end_date: endDate, status, owner, progress });
        gantt.updateTask(id);
    } else {
        gantt.addTask({ text, start_date: startDate, end_date: endDate, status, owner, progress, parent });
    }

    hidePanel();
});

document.getElementById("delete-task").addEventListener("click", () => {
    const id = document.getElementById("task-id").value;
    if (id && confirm("ลบใช่หรือไม่?")) {
        gantt.deleteTask(id);
        hidePanel();
    }
});

function reloadGanttData() {
    gantt.clearAll();
    Promise.all([
        fetch(`/projects/{{ $project->id }}/tasks`).then(res => res.json()),
        fetch(`/projects/{{ $project->id }}/task-links`).then(res => res.json())
    ]).then(([taskRes, linkRes]) => {
        gantt.parse({ data: taskRes.data || [], links: linkRes.data || [] });
    });
}

function saveAllTasks() {
    const tasks = [];

    // ฟังก์ชันช่วยแปลงวันที่ให้อยู่ในรูปแบบ ISO (Laravel จะเข้าใจ)
    const formatDate = d => {
        const date = new Date(d);
        const pad = n => (n < 10 ? '0' + n : n);
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
    };

    gantt.eachTask(t => {
        tasks.push({
            id: t.id,
            text: t.text,
            start_date: formatDate(t.start_date),
            end_date: formatDate(t.end_date),
            parent: t.parent || 0,
            progress: t.progress || 0,
            status: t.status || 'ongoing',
            owner: t.owner || '',
            order: t.order || 0
        });
    });

    const links = gantt.getLinks();

    const formData = new FormData();
    formData.append("project_id", {{ $project->id }});
    formData.append("tasks", JSON.stringify(tasks));
    formData.append("links", JSON.stringify(links));

    fetch("/tasks/bulk-save", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(res => res.json())
    .then(json => {
        if (json.status === 'success') {
            alert("✅ บันทึกสำเร็จ");
            reloadGanttData();
        } else {
            alert("❌ บันทึกไม่สำเร็จ");
        }
    })
    .catch(err => {
        console.error(err);
        alert("❌ เกิดข้อผิดพลาดในการบันทึก");
    });
}


function formatDateThai(date) {
    const months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
}

function setScale(scale) {
    if (scale === "day") {
        gantt.config.scale_unit = "day";
        gantt.config.date_scale = "%d %M";
        gantt.config.subscales = [];
    } else if (scale === "week") {
        gantt.config.scale_unit = "week";
        gantt.config.date_scale = "สัปดาห์ที่ #%W";
        gantt.config.subscales = [{ unit: "day", step: 1, date: "%D" }];
    } else if (scale === "month") {
        gantt.config.scale_unit = "month";
        gantt.config.date_scale = "%F %Y";
        gantt.config.subscales = [{ unit: "week", step: 1 }];
    }
    gantt.render();
}

document.addEventListener("DOMContentLoaded", () => {
    flatpickr.localize(flatpickr.l10ns.th);

    gantt.config.lightbox = false;
    gantt.attachEvent("onBeforeLightbox", () => false);
    gantt.config.xml_date = "%Y-%m-%d";
    gantt.config.row_height = 36;
    gantt.config.show_links = true;
    gantt.config.drag_links = true;

    gantt.attachEvent("onTaskCreated", task => {
        setTimeout(() => {
            if (gantt.isTaskExists(task.id)) gantt.deleteTask(task.id);
            // showPanel({ parent: task.parent });
            const parentId = task.parent || 0;
showPanel({ parent: parentId });

        }, 0);
        return false;
    });

    gantt.attachEvent("onTaskDblClick", id => {
        showPanel(gantt.getTask(id));
        return false;
    });

    gantt.config.columns = [
        { name: "text", label: "งาน", tree: true, width: 240 },
        { name: "start_date", label: "เริ่ม", align: "center", width: 120, template: t => formatDateThai(new Date(t.start_date)) },
        { name: "end_date", label: "สิ้นสุด", align: "center", width: 120, template: t => formatDateThai(new Date(t.end_date)) },
        { name: "status", label: "สถานะ", align: "center", width: 100, template: t => t.status === "done" ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ" },
        { name: "add", label: "", width: 44 }
    ];

    setScale("month"); // default view
    gantt.init("gantt_here");
    reloadGanttData();
});
</script>
@endsection

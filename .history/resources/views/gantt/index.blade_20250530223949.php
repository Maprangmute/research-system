@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- ✅ Flatpickr (ภาษาไทย + พ.ศ.) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<!-- ✅ DHTMLX Gantt -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<style>
    .gantt_marker.today { background-color: red; }
</style>

<div class="max-w-full mx-auto py-6 space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">📊 Gantt Chart: {{ $project->project_name_th }}</h2>

    <div class="bg-white p-4 rounded shadow">
        <div class="flex justify-end mb-4">
            <div class="relative inline-block text-left">
                <button id="scale-button" onclick="toggleScaleDropdown()"
                    class="inline-flex justify-center w-full rounded-md border px-4 py-2 bg-white text-sm text-gray-700 shadow-sm hover:bg-gray-50">
                    🗂 เปลี่ยนมุมมอง
                    <svg class="-mr-1 ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="scale-dropdown" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden z-50">
                    <div class="py-1">
                        <button onclick="setScale('week')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🗓 รายสัปดาห์</button>
                        <button onclick="setScale('month')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📆 รายเดือน</button>
                        <button onclick="setScale('year')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🗂 รายปี</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="gantt_here" class="w-full min-h-[500px] max-h-[90vh] overflow-auto"></div>

        <div class="mt-4 text-right">
            <button onclick="showModal()" class="px-4 py-2 bg-blue-500 text-white rounded">➕ เพิ่มงาน</button>
            <button onclick="saveAllTasks()" class="px-4 py-2 bg-green-600 text-white rounded">💾 บันทึกทั้งหมด</button>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="task-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded w-full max-w-md space-y-4">
        <h3 id="task-modal-title" class="text-lg font-semibold">📝 งาน</h3>
        <p id="task-parent-label" class="text-sm text-gray-500 hidden">
            เพิ่มงานรองของ: <span id="parent-task-name" class="font-semibold text-gray-700"></span>
        </p>

        <input id="task-id" type="hidden" data-parent="">

        <label class="block"><span class="text-sm">ชื่องาน</span><input id="task-title" class="w-full border p-2 rounded"></label>
        <label class="block"><span class="text-sm">วันที่เริ่มต้น</span><input id="task-start" class="w-full border p-2 rounded flatpickr-th"></label>
        <label class="block"><span class="text-sm">วันที่สิ้นสุด</span><input id="task-end" class="w-full border p-2 rounded flatpickr-th"></label>
        <label class="block"><span class="text-sm">สถานะ</span>
            <select id="task-status" class="w-full border p-2 rounded">
                <option value="ongoing">🕒 กำลังทำ</option>
                <option value="done">✅ เสร็จแล้ว</option>
            </select>
        </label>
        <label class="block"><span class="text-sm">ผู้รับผิดชอบ</span><input id="task-owner" class="w-full border p-2 rounded"></label>
        <label class="block"><span class="text-sm">ความคืบหน้า (%)</span><input id="task-progress" type="number" min="0" max="100" step="5" class="w-full border p-2 rounded"></label>

        <div class="flex justify-between pt-2">
            <button id="delete-task" class="text-red-600 hover:underline hidden">🗑 ลบงาน</button>
            <div class="flex gap-2">
                <button onclick="hideModal()" class="text-gray-600 px-4 py-2 rounded hover:bg-gray-100">ยกเลิก</button>
                <button id="save-task" class="bg-blue-600 text-white px-4 py-2 rounded">💾 บันทึก</button>
            </div>
        </div>
    </div>
</div>

<script>
    function formatDateThai(date) {
        const months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
    }

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

    function showModal(task = {}) {
        document.getElementById("task-modal").classList.remove("hidden");
        document.getElementById("task-id").value = task.id ?? "";
        document.getElementById("task-id").dataset.parent = task.parent ?? "";
        document.getElementById("task-title").value = task.text ?? "";
        document.getElementById("task-start")._flatpickr.setDate(task.start_date ?? '');
        document.getElementById("task-end")._flatpickr.setDate(task.end_date ?? '');
        document.getElementById("task-status").value = task.status ?? "ongoing";
        document.getElementById("task-owner").value = task.owner ?? "";
        document.getElementById("task-progress").value = task.progress != null ? task.progress * 100 : 0;

        const titleEl = document.getElementById("task-modal-title");
        const parentLabel = document.getElementById("task-parent-label");
        const parentNameEl = document.getElementById("parent-task-name");

        if (task.id) {
            titleEl.textContent = "✏️ แก้ไขงาน";
            parentLabel.classList.add("hidden");
        } else if (task.parent && gantt.isTaskExists(task.parent)) {
            const parentTask = gantt.getTask(task.parent);
            titleEl.textContent = "📝 เพิ่มงานรอง";
            parentNameEl.textContent = parentTask.text;
            parentLabel.classList.remove("hidden");
        } else {
            titleEl.textContent = "📝 เพิ่มงาน";
            parentLabel.classList.add("hidden");
        }

        document.getElementById("delete-task").classList.toggle("hidden", !task.id);
    }

    function hideModal() {
        document.getElementById("task-modal").classList.add("hidden");
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

        if (!start || !end) {
            alert("กรุณาระบุวันที่ให้ครบ");
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

        hideModal();
        rebindAddButtons();
    });

    document.getElementById("delete-task").addEventListener("click", () => {
        const id = document.getElementById("task-id").value;
        if (id && confirm("ลบงานนี้ใช่หรือไม่?")) {
            gantt.deleteTask(id);
            hideModal();
        }
    });

    function saveAllTasks() {
        const data = [];
        gantt.eachTask(task => data.push(task));

        const formData = new FormData();
        formData.append("project_id", {{ $project->id }});
        formData.append("tasks", JSON.stringify(data));

        fetch("/tasks/bulk-save", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(async res => {
            const text = await res.text();
            try {
                const json = JSON.parse(text);
                if (json.status === 'success') {
                    alert("✅ บันทึกสำเร็จ");
                    gantt.clearAll();
                    gantt.load("/projects/{{ $project->id }}/tasks", null, rebindAddButtons);
                }
            } catch (e) {
                console.error("❌ ไม่ใช่ JSON:", text);
                alert("❌ เกิดข้อผิดพลาด: ดู console");
            }
        });
    }

    function rebindAddButtons() {
        setTimeout(() => {
            document.querySelectorAll(".gantt_add").forEach(btn => {
                btn.onclick = e => {
                    const row = btn.closest(".gantt_row");
                    const id = row?.getAttribute("data-task-id");
                    if (id && gantt.isTaskExists(id)) {
                        const task = gantt.getTask(id);
                        showModal({ parent: task.id });
                        e.stopPropagation();
                    }
                };
            });
        }, 300);
    }

    function setScale(scale) {
        gantt.config.scale_unit = scale;
        if (scale === "week") {
            gantt.config.date_scale = "สัปดาห์ที่ #%W";
            gantt.config.subscales = [{ unit: "day", step: 1, date: gantt.date.date_to_str("%D %j") }];
        } else if (scale === "month") {
            gantt.config.date_scale = "%F";
            gantt.config.subscales = [{ unit: "week", step: 1, date: gantt.date.date_to_str("%j %M") }];
        } else if (scale === "year") {
            gantt.config.date_scale = "%Y";
            gantt.config.subscales = [{ unit: "month", step: 1, date: gantt.date.date_to_str("%F") }];
        }
        gantt.render();
        document.getElementById("scale-dropdown").classList.add("hidden");
    }

    document.addEventListener("DOMContentLoaded", function () {
        flatpickr.localize(flatpickr.l10ns.th);

        flatpickr(".flatpickr-th", {
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "Y-m-d",
            locale: "th",
            onReady: function (selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const buddhistYear = selectedDates[0].getFullYear() + 543;
                    instance.altInput.value = instance.altInput.value.replace(/\d{4}/, buddhistYear);
                }
            },
            onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const buddhistYear = selectedDates[0].getFullYear() + 543;
                    instance.altInput.value = instance.altInput.value.replace(/\d{4}/, buddhistYear);
                }
            }
        });

        gantt.config.xml_date = "%Y-%m-%d";
        gantt.config.row_height = 36;

        gantt.config.columns = [
            { name: "text", label: "งาน", width: 220, tree: true },
            { name: "start_date", label: "เริ่ม", align: "center", width: 120, template: t => formatDateThai(new Date(t.start_date)) },
            { name: "end_date", label: "สิ้นสุด", align: "center", width: 120, template: t => formatDateThai(new Date(t.end_date)) },
            { name: "status", label: "สถานะ", align: "center", width: 100, template: t => t.status === 'done' ? "✅ เสร็จแล้ว" : "🕒 กำลังทำ" },
            { name: "add", label: "", width: 44 }
        ];

        gantt.attachEvent("onTaskDblClick", id => {
            const task = gantt.getTask(id);
            showModal(task);
            return false;
        });

        gantt.attachEvent("onGridHeaderClick", name => {
            if (name === "add") {
                showModal();
                return false;
            }
        });

        setScale("month");
        gantt.init("gantt_here");
        gantt.load("/projects/{{ $project->id }}/tasks", null, rebindAddButtons);
    });
</script>
@endsection

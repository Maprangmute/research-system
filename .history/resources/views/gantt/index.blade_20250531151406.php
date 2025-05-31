@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- ✅ Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<style>
    .flatpickr-calendar {
        z-index: 999999 !important;
    }
</style>

<div class="max-w-2xl mx-auto py-10">
    <button onclick="showPanel()" class="px-4 py-2 bg-blue-600 text-white rounded">➕ เพิ่มงาน</button>
</div>

<div id="task-panel" class="fixed inset-x-0 bottom-0 bg-white shadow-xl p-4 z-50 hidden transform translate-y-full transition-all duration-300 rounded-t-xl max-h-[90vh] overflow-y-auto">
    <h3 class="text-lg font-bold mb-4">📅 งานใหม่</h3>

    <div class="grid grid-cols-1 gap-4">
        <label class="block">
            <span class="text-sm">วันที่เริ่มต้น</span>
            <input id="task-start" class="flatpickr-th w-full border p-2 rounded mt-1" type="text">
        </label>

        <label class="block">
            <span class="text-sm">วันที่สิ้นสุด</span>
            <input id="task-end" class="flatpickr-th w-full border p-2 rounded mt-1" type="text">
        </label>
    </div>

    <div class="mt-6 text-right">
        <button onclick="hidePanel()" class="px-4 py-2 bg-gray-300 rounded">ปิด</button>
    </div>
</div>

<script>
function formatThaiBuddhistYear(instance) {
    const input = instance.altInput;
    if (input && instance.selectedDates.length > 0) {
        const date = instance.selectedDates[0];
        const day = date.getDate();
        const month = instance.l10n.months.longhand[date.getMonth()];
        const year = date.getFullYear() + 543;
        input.value = `${day} ${month} ${year}`;
    }
}

function showPanel() {
    const panel = document.getElementById("task-panel");
    panel.classList.remove("hidden");
    setTimeout(() => panel.classList.remove("translate-y-full"), 10);

    setTimeout(() => {
        flatpickr.localize(flatpickr.l10ns.th);

        document.querySelectorAll(".flatpickr-th").forEach(input => {
            if (input._flatpickr) input._flatpickr.destroy();

            const picker = flatpickr(input, {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "j F Y",
                locale: "th",
                appendTo: document.body,
                disableMobile: true,
                position: "auto center",
                onReady: (_, __, instance) => {
                    formatThaiBuddhistYear(instance);
                    if (instance.altInput) {
                        instance.altInput.addEventListener("click", () => instance.open());
                        instance.altInput.classList.add('w-full', 'border', 'p-2', 'rounded', 'mt-1');
                    }
                },
                onChange: (_, __, instance) => formatThaiBuddhistYear(instance),
            });
        });
    }, 300);
}

function hidePanel() {
    const panel = document.getElementById("task-panel");
    panel.classList.add("translate-y-full");
    setTimeout(() => panel.classList.add("hidden"), 300);
}
</script>
@endsection

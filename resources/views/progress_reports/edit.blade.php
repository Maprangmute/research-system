@extends('layouts.app')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

@section('content')
<div class="max-w-2xl mx-auto mt-10 bg-white rounded-2xl shadow-lg p-8">
    <h2 class="text-2xl font-bold mb-2 text-blue-700 flex items-center gap-2">
        ✏️ แก้ไข Progress Report
    </h2>
    <div class="mb-7 text-xl text-black-500 break-words whitespace-pre-line leading-snug">
        {{ $project->project_name_th }}
    </div>

    <form method="POST" action="{{ route('projects.progress_reports.update', [$project->id, $report->id]) }}">
        @csrf
        @method('PUT')

        <div class="mb-5">
            <label class="block font-semibold mb-1 text-gray-700">ชื่อ progress <span class="text-red-500">*</span></label>
            <input type="text" name="submitter_name" value="{{ old('submitter_name', $report->submitter_name) }}"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 @error('submitter_name') border-red-500 @enderror transition"
                required>
            @error('submitter_name')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>

        {{-- Date Picker พ.ศ. --}}
        <div class="mb-5">
            <label class="block font-semibold mb-1 text-gray-700">วันที่ส่ง <span class="text-red-500">*</span></label>
            <div class="relative">
                <input type="text" id="show_buddhist_date"
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 datepicker-th bg-gray-50 cursor-pointer transition @error('submit_date') border-red-500 @enderror"
                    readonly placeholder="เลือกวันที่ เช่น 6 มิถุนายน 2565" autocomplete="off">
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 h-5 w-5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M3 11h18M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <input type="hidden" name="submit_date" id="submit_date" value="{{ old('submit_date', $report->submit_date) }}">
            @error('submit_date')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
            <small class="text-gray-500">* คลิกเลือกวัน/เดือน/ปี จะเห็นปี พ.ศ. ในช่องนี้</small>
        </div>

        <div class="mb-7">
            <label class="block font-semibold mb-1 text-gray-700">รายละเอียดไฟล์ / ลิงก์</label>
            <input type="text" name="file_info" value="{{ old('file_info', $report->file_info) }}"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 @error('file_info') border-red-500 @enderror transition"
                placeholder="ใส่ลิงก์ หรืออธิบายไฟล์ (ถ้ามี)">
            @error('file_info')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                อัปเดต
            </button>
            <a href="{{ route('projects.progress_reports.index', $project->id) }}"
               class="text-gray-500 hover:underline px-6 py-2 rounded-lg text-center">ยกเลิก</a>
        </div>
    </form>
</div>

<script>
flatpickr("#show_buddhist_date", {
    dateFormat: "Y-m-d",
    locale: "th",
    allowInput: false,
    clickOpens: true,
    onChange: function(selectedDates, dateStr, instance) {
        if(selectedDates.length) {
            const d = selectedDates[0];
            const day = d.getDate();
            const monthNames = [
                "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
                "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
            ];
            const month = monthNames[d.getMonth()];
            const year = d.getFullYear() + 543;
            document.getElementById('show_buddhist_date').value = `${day} ${month} ${year}`;
            // format ค.ศ. สำหรับ backend
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(day).padStart(2, '0');
            document.getElementById('submit_date').value = `${d.getFullYear()}-${mm}-${dd}`;
        }
    },
    onReady: function(selectedDates, dateStr, instance) {
        // preload ถ้ามีค่าเดิม
        let value = document.getElementById('submit_date').value;
        if(value) {
            let d = new Date(value);
            if (!isNaN(d)) {
                const day = d.getDate();
                const monthNames = [
                    "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
                    "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
                ];
                const month = monthNames[d.getMonth()];
                const year = d.getFullYear() + 543;
                document.getElementById('show_buddhist_date').value = `${day} ${month} ${year}`;
            }
        }
    }
});
</script>
@endsection

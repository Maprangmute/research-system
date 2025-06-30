@extends('layouts.app')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

@section('content')
<div class="max-w-lg mx-auto mt-10 bg-white rounded shadow p-6">
    <div class="text-xl font-bold mb-4 text-gray-800">+ เพิ่ม Progress Report</div>

    <form method="POST" action="{{ route('projects.progress_reports.store', $project->id) }}">
        @csrf

        <div class="mb-4">
            <label class="block font-medium mb-1">ชื่อ Progress <span class="text-red-500">*</span></label>
            <input type="text" name="submitter_name" value="{{ old('submitter_name') }}"
                class="w-full border rounded p-2 focus:ring focus:ring-blue-200 @error('submitter_name') border-red-500 @enderror" required>
            @error('submitter_name')
                <div class="text-red-500 text-xs">{{ $message }}</div>
            @enderror
        </div>

        <!-- ช่องโชว์ พ.ศ. -->
        <div class="mb-4">
            <label class="block font-medium mb-1">วันที่ส่ง <span class="text-red-500">*</span></label>
            <input type="text" id="show_buddhist_date"
                class="w-full border rounded p-2 focus:ring focus:ring-blue-200 datepicker-th @error('submit_date') border-red-500 @enderror"
                readonly placeholder="เลือกวันที่ เช่น 6 มิถุนายน 2565" style="background: #fafafa; cursor: pointer;">
            <!-- ช่อง hidden สำหรับ submit (ค.ศ.) -->
            <input type="hidden" name="submit_date" id="submit_date" value="{{ old('submit_date') }}">
            @error('submit_date')
                <div class="text-red-500 text-xs">{{ $message }}</div>
            @enderror
            <small class="text-gray-500">* กดปฏิทิน เลือกวัน จะเห็นปี พ.ศ.</small>
        </div>

        <div class="mb-6">
            <label class="block font-medium mb-1">รายละเอียดไฟล์ / ลิงก์</label>
            <input type="text" name="file_info" value="{{ old('file_info') }}"
                class="w-full border rounded p-2 focus:ring focus:ring-blue-200 @error('file_info') border-red-500 @enderror"
                placeholder="ใส่ลิงก์ หรืออธิบายไฟล์ (ถ้ามี)">
            @error('file_info')
                <div class="text-red-500 text-xs">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex justify-end gap-2">
            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">บันทึก</button>
            <a href="{{ route('projects.progress_reports.index', $project->id) }}"
               class="text-gray-500 hover:underline px-4 py-2">ยกเลิก</a>
        </div>
    </form>
</div>

<script>
flatpickr("#show_buddhist_date", {
    dateFormat: "Y-m-d",
    locale: "th",
    allowInput: false,
    onChange: function(selectedDates, dateStr, instance) {
        if(selectedDates.length) {
            const d = selectedDates[0];
            // สร้าง string พ.ศ.
            const day = d.getDate();
            // เดือนเต็ม (ภาษาไทย)
            const monthNames = [
                "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
                "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
            ];
            const month = monthNames[d.getMonth()];
            const year = d.getFullYear() + 543;
            document.getElementById('show_buddhist_date').value = `${day} ${month} ${year}`;
            // เซต hidden ให้เป็น yyyy-mm-dd (ค.ศ.)
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(day).padStart(2, '0');
            document.getElementById('submit_date').value = `${d.getFullYear()}-${mm}-${dd}`;
        }
    },
});
</script>
@endsection

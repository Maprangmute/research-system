@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-6">

    <div class="mb-4">
    <a href="javascript:history.back()" class="text-blue-600 hover:underline text-base font-semibold">
        ⟵ ย้อนกลับ
    </a>
</div>

    <h2 class="text-2xl font-bold text-gray-800 mb-6">📊 สรุป FTE รายบุคคล</h2>

    <!-- 🎚 Filter ปี -->
    <form method="GET" action="{{ route('fte.index') }}" class="mb-6 flex gap-4 items-center">
        <label for="year" class="text-gray-700">ปีงบประมาณ:</label>
        <select name="year" id="year" class="p-2 border rounded">
            @foreach($years as $year)
                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">🔍 ค้นหา</button>
    </form>

    <!-- 📊 FTE Summary Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
        <table class="w-full table-auto text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">รายการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($fteSummary as $row)
                    @php
                        $rowId = 'row-' . md5($row->name . $loop->index);
                    @endphp
                    <tr class="border-t hover:bg-gray-50 cursor-pointer" onclick="toggleDetails('{{ $rowId }}')">
                        <td class="px-4 py-2 font-medium text-blue-700">
                            {{ $row->name }} 
                        </td>
                    </tr>
                    <tr id="detail-{{ $rowId }}" class="hidden bg-gray-50 border-t">
                        <td class="px-4 py-3">
                            @if(isset($monthlyFTE[$row->name]))
                                @foreach($monthlyFTE[$row->name] as $month => $items)
                                    @php
                                        $carbon = \Carbon\Carbon::parse($month . '-01');
                                        $thaiYear = $carbon->year + 543;
                                        $thaiMonth = $carbon->locale('th')->translatedFormat('F');
                                    @endphp
                                    <div class="mb-4">
                                        <h4 class="text-gray-700 font-semibold mb-1">📅 เดือน {{ $thaiMonth }} {{ $thaiYear }}</h4>
                                        <table class="w-full text-sm border border-gray-200 mb-2 table-fixed">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="p-2 text-left w-1/3">ชื่อโปรเจค</th>
                                                    <th class="p-2 text-left w-1/4">กลุ่ม</th>
                                                    <th class="p-2 text-left w-1/3">ภาระงาน</th>
                                                    <th class="p-2 text-right w-1/6">FTE</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $sum = 0; @endphp
                                                @foreach($items as $item)
                                                    <tr>
                                                        <td class="p-2 text-left">
                                                            <a href="{{ route('projects.show', $item->project->id) }}"
                                                               class="text-blue-600 hover:underline">
                                                                {{ $item->project->project_name_th ?? '-' }}
                                                            </a>
                                                        </td>
                                                        <td class="p-2 text-left">{{ $item->role_group ?? '-' }}</td>
                                                        <td class="p-2 text-left">{{ $item->job_detail ?? '-' }}</td>
                                                        <td class="p-2 text-right">{{ number_format($item->fte_value, 2) }}</td>
                                                    </tr>
                                                    @php $sum += $item->fte_value; @endphp
                                                @endforeach
                                                <tr class="bg-gray-200 font-bold">
                                                    <td colspan="3" class="p-2 text-right">รวม</td>
                                                    <td class="p-2 text-right">{{ number_format($sum, 2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-500">ไม่มีข้อมูล FTE สำหรับบุคคลนี้</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-6 text-center text-gray-400">ไม่พบข้อมูล</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- 🔽 Toggle ลิ้นชัก -->
<script>
function toggleDetails(id) {
    const row = document.getElementById('detail-' + id);
    row.classList.toggle('hidden');
}
</script>
@endsection

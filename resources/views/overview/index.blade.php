@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-4">
    <h2 class="text-3xl font-bold mb-6 text-gray-800">📈 ภาพรวมโครงการวิจัย</h2>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('overview') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700">ปีที่เริ่มต้น</label>
                <select name="year" class="w-full border border-gray-300 p-2 rounded">
                    <option value="">-- ทุกปี --</option>
                    @foreach ($allYears as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>ปี {{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">ประเภทโครงการ</label>
                <select name="type" class="w-full border border-gray-300 p-2 rounded">
                    <option value="">-- ทุกประเภท --</option>
                    @foreach ($allTypes as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">🔍 กรองข้อมูล</button>
            </div>
        </form>
    </div>

    <!-- กราฟจำนวนโครงการตามปี -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <h3 class="text-lg font-semibold mb-3 text-gray-700">📊 จำนวนโครงการตามปี</h3>
        <canvas id="yearChart" height="100"></canvas>
    </div>

    <!-- กราฟประเภทของโครงการ -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <h3 class="text-lg font-semibold mb-3 text-gray-700">📂 ประเภทของโครงการ</h3>
        <canvas id="typeChart" height="100"></canvas>
    </div>

    <!-- Timeline -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <h3 class="text-lg font-semibold mb-3 text-gray-700">🗓️ ช่วงเวลาโครงการ</h3>
        <ul class="space-y-3">
            @foreach ($timeline as $item)
            <li class="border-l-4 border-indigo-500 pl-3">
                <p class="font-semibold text-gray-800">{{ $item['name'] }}</p>
                <p class="text-sm text-gray-600">{{ $item['start'] }} - {{ $item['end'] }}</p>
            </li>
            @endforeach
        </ul>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const yearChart = new Chart(document.getElementById('yearChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($projectsByYear->keys()) !!},
        datasets: [{
            label: 'จำนวนโครงการ',
            data: {!! json_encode($projectsByYear->values()) !!},
            backgroundColor: 'rgba(79, 70, 229, 0.6)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});

const typeChart = new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($projectsByType->keys()) !!},
        datasets: [{
            label: 'จำนวน',
            data: {!! json_encode($projectsByType->values()) !!},
            backgroundColor: ['#60a5fa', '#34d399', '#f87171', '#fbbf24', '#a78bfa']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endsection

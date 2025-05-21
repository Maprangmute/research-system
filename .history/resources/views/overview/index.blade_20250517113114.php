@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">📈 ภาพรวมโครงการวิจัย</h2>

    <!-- กราฟจำนวนโครงการตามปี -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-2">จำนวนโครงการตามปี</h3>
        <canvas id="yearChart" height="100"></canvas>
    </div>

    <!-- กราฟประเภทโครงการ -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-2">ประเภทของโครงการ</h3>
        <canvas id="typeChart" height="100"></canvas>
    </div>

    <!-- Timeline -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-2">ช่วงเวลาโครงการ</h3>
        <ul class="space-y-2">
            @foreach ($timeline as $item)
                <li class="border p-3 rounded shadow-sm bg-white">
                    <strong>{{ $item['name'] }}</strong><br>
                    {{ $item['start'] }} - {{ $item['end'] }}
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
            backgroundColor: 'rgba(54, 162, 235, 0.6)'
        }]
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
    }
});
</script>
@endsection

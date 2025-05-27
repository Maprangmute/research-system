@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-6 p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">📊 รายงานภาพรวมโครงการ</h2>

    <!-- Filters -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="w-full md:w-1/3">
            <label class="block text-sm text-gray-700 mb-1">กรองตามปีเริ่มต้น</label>
            <select id="yearFilter" class="w-full border rounded p-2">
                <option value="">ทั้งหมด</option>
                @foreach ($projects->pluck('start_year')->unique()->sortDesc() as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-full md:w-2/3">
            <label class="block text-sm text-gray-700 mb-1">ค้นหาชื่อโครงการ</label>
            <input type="text" id="searchInput" class="w-full border rounded p-2" placeholder="พิมพ์ชื่อโครงการ">
        </div>
    </div>

    <!-- Chart -->
    <canvas id="projectChart" height="100"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const allProjects = @json($projects);

    const ctx = document.getElementById('projectChart').getContext('2d');
    let chartInstance;

    function renderChart(data) {
        const labels = data.map(p => p.project_name_th);
        const budgets = data.map(p => parseFloat(p.budget));

        if (chartInstance) chartInstance.destroy();

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'งบประมาณ (บาท)',
                    data: budgets,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    function filterAndRender() {
        const year = document.getElementById('yearFilter').value;
        const search = document.getElementById('searchInput').value.toLowerCase();

        const filtered = allProjects.filter(p => {
            return (!year || p.start_year == year) &&
                   (!search || p.project_name_th.toLowerCase().includes(search));
        });

        renderChart(filtered);
    }

    document.getElementById('yearFilter').addEventListener('change', filterAndRender);
    document.getElementById('searchInput').addEventListener('input', filterAndRender);

    // Render chart on page load
    renderChart(allProjects);
</script>
@endsection

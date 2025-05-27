@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto mt-6 p-6 bg-white rounded shadow">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">📊 รายงานภาพรวมโครงการ</h2>

        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="w-full md:w-1/4">
                <label class="block text-sm text-gray-700 mb-1">ปีเริ่มต้น</label>
                <select id="startYear" class="w-full border rounded p-2">
                    <option value="">ทั้งหมด</option>
                    @for ($year = now()->year + 543; $year >= 2557; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>

            <div class="w-full md:w-1/4">
                <label class="block text-sm text-gray-700 mb-1">ปีสิ้นสุด</label>
                <select id="endYear" class="w-full border rounded p-2">
                    <option value="">ทั้งหมด</option>
                    @for ($year = now()->year + 543; $year >= 2557; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>

            <div class="w-full md:w-2/4">
                <label class="block text-sm text-gray-700 mb-1">ค้นหาชื่อโครงการ</label>
                <input type="text" id="searchInput" class="w-full border rounded p-2" placeholder="พิมพ์ชื่อโครงการ">
            </div>
        </div>


        <!-- Chart -->
        <canvas id="projectChart" height="100"></canvas>


        <div class="flex justify-between items-center mt-8 mb-4">
            <h3 class="font-semibold text-gray-700 text-lg">📄 รายชื่อโครงการ</h3>
            <button id="exportBtn" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">
                📤 Export CSV
            </button>
        </div>

        <ul id="projectList" class="list-disc pl-5 text-sm text-gray-800"></ul>

        <hr class="my-8 border-t">

        <h3 class="font-semibold text-gray-700 text-lg mb-4">📊 จำนวน Outputs จำแนกตามประเภท</h3>
        <canvas id="outputChart" height="100"></canvas>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <canvas id="projectChart" height="100"></canvas>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const rawProjects = @json($projects);
        let filteredProjects = rawProjects;

        function renderChart(data) {
            const ctx = document.getElementById('projectChart').getContext('2d');
            if (window.myChart) window.myChart.destroy();

            const labels = data.map(p => p.project_name_th);
            const budgets = data.map(p => p.budget);

            window.myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'งบประมาณ (บาท)',
                        data: budgets,
                        backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function applyFilter() {
            const start = parseInt(document.getElementById('startYear').value);
            const end = parseInt(document.getElementById('endYear').value);
            const keyword = document.getElementById('searchInput').value.toLowerCase();

            filteredProjects = rawProjects.filter(p => {
                const year = parseInt(p.start_year);
                const nameMatch = p.project_name_th.toLowerCase().includes(keyword);
                const inRange = (!start || year >= start) && (!end || year <= end);
                return nameMatch && inRange;
            });

            renderChart(filteredProjects);
        }

         

        document.getElementById('startYear').addEventListener('change', applyFilter);
        document.getElementById('endYear').addEventListener('change', applyFilter);
        document.getElementById('searchInput').addEventListener('input', applyFilter);

        renderChart(rawProjects);
    </script>
@endsection

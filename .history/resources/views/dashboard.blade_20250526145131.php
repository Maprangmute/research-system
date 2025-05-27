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

            <div class="w-full md:w-1/4">
                <label class="block text-sm text-gray-700 mb-1">ค้นหาชื่อโครงการ</label>
                <input type="text" id="searchInput" class="w-full border rounded p-2" placeholder="พิมพ์ชื่อโครงการ">
            </div>

            <div class="w-full md:w-1/4">
                <label class="block text-sm text-gray-700 mb-1">ค้นหาชื่อทุน</label>
                <input type="text" id="fundingSearch" class="w-full border rounded p-2" placeholder="ชื่อทุน">
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
            const fundKey = document.getElementById('fundingSearch').value.toLowerCase();

            filteredProjects = rawProjects.filter(p => {
                const year = parseInt(p.start_year);
                const nameMatch = p.project_name_th?.toLowerCase().includes(keyword) ?? false;
                const fundMatch = p.funding_name?.toLowerCase().includes(fundKey) ?? false;
                const inRange = (!start || year >= start) && (!end || year <= end);
                return nameMatch && fundMatch && inRange;
            });

            renderChart(filteredProjects);
            renderFundingChart(filteredProjects);
            renderOutputChart(filteredProjects);
            updateProjectList(filteredProjects);
        }

        function groupByFunding(projects) {
            const count = {};
            projects.forEach(p => {
                const name = p.funding_name || 'ไม่ระบุ';
                count[name] = (count[name] || 0) + 1;
            });
            return count;
        }

        function groupByOutputType(projects) {
            const typeCount = {};
            projects.forEach(p => {
                (p.outputs || []).forEach(out => {
                    const type = out.type || 'ไม่ระบุ';
                    typeCount[type] = (typeCount[type] || 0) + 1;
                });
            });
            return typeCount;
        }

        function renderFundingChart(data) {
            const counts = groupByFunding(data);

            if (!document.getElementById('fundingChart')) {
                const canvas = document.createElement('canvas');
                canvas.id = 'fundingChart';
                canvas.height = 100;
                document.querySelector('.max-w-6xl').appendChild(canvas);
            }

            const ctx = document.getElementById('fundingChart').getContext('2d');

            if (window.fundingChart && typeof window.fundingChart.destroy === 'function') {
                window.fundingChart.destroy();
            }

            const labels = Object.keys(counts);
            const values = Object.values(counts);

            window.fundingChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'จำนวนโครงการ',
                        data: values,
                        backgroundColor: 'rgba(96, 165, 250, 0.8)'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    onClick: (e, elements) => {
                        if (elements.length) {
                            const index = elements[0].index;
                            const label = labels[index];
                            const filtered = filteredProjects.filter(p => (p.funding_name || 'ไม่ระบุ') ===
                                label);
                            updateProjectList(filtered);
                        }
                    }
                }
            });
        }


        function renderOutputChart(data) {
            const counts = groupByOutputType(data);
            const ctx = document.getElementById('outputChart').getContext('2d');
            if (window.outputChart) window.outputChart.destroy();

            const labels = Object.keys(counts);
            const values = Object.values(counts);

            window.outputChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'จำนวนผลงาน',
                        data: values,
                        backgroundColor: ['#60a5fa', '#10b981', '#f59e0b', '#ef4444', '#a78bfa']
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
        }

        function updateProjectList(data) {
            const ul = document.getElementById('projectList');
            ul.innerHTML = data.length ?
                data.map(p => `<li>${p.project_name_th} (${p.funding_name || 'ไม่ระบุ'})</li>`).join('') :
                '<li class="text-red-600">ไม่พบโครงการ</li>';
        }

        document.getElementById('startYear').addEventListener('change', applyFilter);
        document.getElementById('endYear').addEventListener('change', applyFilter);
        document.getElementById('searchInput').addEventListener('input', applyFilter);
        document.getElementById('fundingSearch').addEventListener('input', applyFilter);

        document.getElementById('exportBtn').addEventListener('click', () => {
            const csv = "ชื่อโครงการ,ชื่อทุน,งบประมาณ,ปี\n" +
                filteredProjects.map(p =>
                    `"${p.project_name_th}","${p.funding_name || ''}","${p.budget || ''}","${p.start_year}"`
                ).join("\n");

            const blob = new Blob(["\uFEFF" + csv], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "projects_export.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        renderChart(rawProjects);
        renderFundingChart(rawProjects);
        renderOutputChart(rawProjects);
        updateProjectList(rawProjects);
    </script>
@endsection

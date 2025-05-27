<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            รายงานเดชบองโครงการวิจัย</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="mb-4 flex flex-wrap gap-4">
                    <div>
                        <label class="text-sm block mb-1 text-gray-700">ปีโครงการ</label>
                        <select id="yearFilter" class="border p-2 rounded">
                            <option value="">-- ทั้งหมด --</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm block mb-1 text-gray-700">ชื่อโครงการ</label>
                        <input id="searchFilter" type="text" class="border p-2 rounded" placeholder="ฎียวโครงการ">
                    </div>
                </div>

                <canvas id="projectChart" class="w-full h-64"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const projects = @json($projects);

        function renderChart(data) {
            const ctx = document.getElementById('projectChart').getContext('2d');
            if (window.myChart) window.myChart.destroy();

            const labels = data.map(p => p.project_name_th);
            const values = data.map(p => p.budget);

            window.myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'งบประมาณโครงการ (บาท)',
                        data: values,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => new Intl.NumberFormat().format(ctx.raw) + ' บาท'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function applyFilters() {
            const year = document.getElementById('yearFilter').value;
            const keyword = document.getElementById('searchFilter').value.toLowerCase();

            const filtered = projects.filter(p => {
                const matchYear = !year || p.start_year == year;
                const matchSearch = !keyword || p.project_name_th.toLowerCase().includes(keyword);
                return matchYear && matchSearch;
            });

            renderChart(filtered);
        }

        document.getElementById('yearFilter').addEventListener('change', applyFilters);
        document.getElementById('searchFilter').addEventListener('input', applyFilters);

        // fill year options
        const yearSelect = document.getElementById('yearFilter');
        const years = [...new Set(projects.map(p => p.start_year))].sort((a, b) => b - a);
        years.forEach(y => {
            const opt = document.createElement('option');
            opt.value = y;
            opt.textContent = y;
            yearSelect.appendChild(opt);
        });

        renderChart(projects);
    </script>
</x-app-layout>

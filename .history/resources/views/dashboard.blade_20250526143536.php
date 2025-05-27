@extends('layouts.app')

@section('content')
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
        <label class="block text-sm text-gray-700 mb-1">ค้นหาชื่อทุน</label>
        <input type="text" id="fundingSearch" class="w-full border rounded p-2" placeholder="ชื่อทุน...">
    </div>

    <div class="w-full md:w-1/4">
        <label class="block text-sm text-gray-700 mb-1">ค้นหาชื่อโครงการ</label>
        <input type="text" id="projectSearch" class="w-full border rounded p-2" placeholder="ชื่อโครงการ...">
    </div>
</div>

<canvas id="fundingChart" height="120"></canvas>

<div class="mt-6">
    <h3 class="font-semibold text-gray-700 mb-2">📋 รายชื่อโครงการที่พบ</h3>
    <ul id="projectList" class="list-disc pl-5 text-sm text-gray-800"></ul>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const rawProjects = @json($projects);
    let filteredProjects = rawProjects;

    function renderFundingChart(data) {
        const ctx = document.getElementById('fundingChart').getContext('2d');
        if (window.fundingChart) window.fundingChart.destroy();

        // สรุปจำนวนโครงการต่อชื่อทุน
        const counts = {};
        data.forEach(p => {
            const name = p.funding_name || 'ไม่ระบุ';
            counts[name] = (counts[name] || 0) + 1;
        });

        const labels = Object.keys(counts);
        const values = Object.values(counts);

        window.fundingChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'จำนวนโครงการ',
                    data: values,
                    backgroundColor: 'rgba(96, 165, 250, 0.7)'
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { beginAtZero: true }
                }
            }
        });
    }

    function updateProjectList(data) {
        const list = document.getElementById('projectList');
        list.innerHTML = '';
        if (data.length === 0) {
            list.innerHTML = `<li class="text-red-600">ไม่พบโครงการ</li>`;
            return;
        }
        data.forEach(p => {
            list.innerHTML += `<li>${p.project_name_th} (${p.funding_name || 'ไม่ระบุ'})</li>`;
        });
    }

    function applyFilter() {
        const start = parseInt(document.getElementById('startYear').value);
        const end = parseInt(document.getElementById('endYear').value);
        const fundingKeyword = document.getElementById('fundingSearch').value.toLowerCase();
        const projectKeyword = document.getElementById('projectSearch').value.toLowerCase();

        filteredProjects = rawProjects.filter(p => {
            const year = parseInt(p.start_year);
            const inYearRange = (!start || year >= start) && (!end || year <= end);
            const matchFunding = p.funding_name?.toLowerCase().includes(fundingKeyword) ?? false;
            const matchProject = p.project_name_th?.toLowerCase().includes(projectKeyword) ?? false;
            return inYearRange && matchFunding && matchProject;
        });

        renderFundingChart(filteredProjects);
        updateProjectList(filteredProjects);
    }

    // 🎯 Event listeners
    document.getElementById('startYear').addEventListener('change', applyFilter);
    document.getElementById('endYear').addEventListener('change', applyFilter);
    document.getElementById('fundingSearch').addEventListener('input', applyFilter);
    document.getElementById('projectSearch').addEventListener('input', applyFilter);

    // ⏯️ เริ่มต้น
    renderFundingChart(rawProjects);
    updateProjectList(rawProjects);
</script>

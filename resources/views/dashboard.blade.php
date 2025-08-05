@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-4 space-y-6">

    <h2 class="text-2xl font-bold text-gray-800 mb-2">📊 ภาพรวมโครงการวิจัย (Research Dashboard)</h2>


<!-- 2. Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    @php
        $summaryItems = [
            ['count' => $projects->count(), 'label' => '📁 โครงการ'],
            ['count' => $projects->pluck('pi_name')->unique()->count(), 'label' => '👨‍🔬 PI'],
            ['count' => $copis->count(), 'label' => '👥 Co-PI'],
            ['count' => $coResearches->count(), 'label' => '🤝 Co-Research'],
            ['count' => number_format($fteRoles->sum('fte_value'), 2), 'label' => '📈 FTE รวม'],
            ['count' => $outputs->count(), 'label' => '📝 Outputs'],
        ];
    @endphp

    @foreach ($summaryItems as $item)
        <div class="bg-white shadow-sm border rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-emerald-600">{{ $item['count'] }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $item['label'] }}</div>
        </div>
    @endforeach
</div>

<!-- 3. Visualization Charts -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

    <div class="bg-white shadow-sm border rounded-xl p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">📊 โครงการแยกตาม PI และปี</h3>
        <canvas id="chartPiYear" height="250"></canvas>
    </div>

    <div class="bg-white shadow-sm border rounded-xl p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">📋 สถานะโครงการในแต่ละปี</h3>
        <canvas id="chartStatusYear" height="250"></canvas>
    </div>

    <div class="bg-white shadow-sm border rounded-xl p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">📝 Timeline ของ Output (แยกปี)</h3>
        <canvas id="chartOutputTimeline" height="250"></canvas>
    </div>

    <div class="bg-white shadow-sm border rounded-xl p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">📅 IRB ที่จะหมดอายุ (6 เดือนถัดไป)</h3>
        <canvas id="chartIRBExpiry" height="250"></canvas>
    </div>

    <div class="bg-white shadow-sm border rounded-xl p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">📈 การกระจาย FTE</h3>
        <canvas id="chartFteSummary" height="300"></canvas>
    </div>
</div>

<!-- 4.1: Co-Research Summary -->
<div class="bg-white shadow-sm border rounded-xl p-4 mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">🤝 รายชื่อ Co-Research ทั้งหมด</h3>
    <table class="w-full text-sm border">
        <thead class="bg-gray-50 text-xs text-gray-700">
            <tr>
                <th class="p-2 text-left">ชื่อ</th>
                <th class="p-2 text-left">หน่วยงาน</th>
                <th class="p-2 text-center">จำนวนโครงการ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($coResearches->groupBy('name') as $name => $group)
                <tr class="border-t">
                    <td class="p-2">{{ $name }}</td>
                    <td class="p-2">{{ $group->first()->affiliation }}</td>
                    <td class="p-2 text-center">{{ $group->count() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- 4.2: Project Table -->
<div class="bg-white shadow-sm border rounded-xl p-4">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">📋 รายการโครงการทั้งหมด</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border" id="projectTable">
            <thead class="bg-gray-100 text-xs text-gray-700">
                <tr>
                    <th class="p-2 text-left">ชื่อโครงการ</th>
                    <th class="p-2">ปี</th>
                    <th class="p-2">PI</th>
                    <th class="p-2">Co-PI</th>
                    <th class="p-2">CoResearch</th>
                    <th class="p-2">FTE รวม</th>
                    <th class="p-2">Output</th>
                    <th class="p-2">IRB ล่าสุด</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($projects as $p)
                    <tr class="border-t">
                        <td class="p-2 text-blue-700 hover:underline"><a href="/projects/{{ $p->id }}">{{ $p->project_name_th }}</a></td>
                        <td class="p-2 text-center">{{ $p->start_year }}</td>
                        <td class="p-2 text-center">{{ $p->pi_name }}</td>
                        <td class="p-2 text-center">{{ $p->copis->count() }}</td>
                        <td class="p-2 text-center">{{ $p->coResearches->count() }}</td>
                        <td class="p-2 text-center">{{ number_format($p->fteRoles->sum('fte_value'), 2) }}</td>
                        <td class="p-2 text-center">{{ $p->outputs->count() }}</td>
                        <td class="p-2 text-center">
                            @if ($p->irbs->isNotEmpty())
                                {{ \Carbon\Carbon::parse($p->irbs->sortByDesc('exp_date')->first()->exp_date)->format('d/m/Y') }}
                            @else
                                ❌
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


    <!-- 1. Filter Panel -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div>
                <label class="text-xs font-semibold text-gray-700 block">ปีเริ่มต้น</label>
                <select id="filterYear" class="w-full border rounded px-2 py-1 text-sm">
                    <option value="">ทั้งหมด</option>
                    @for ($y = now()->year + 543; $y >= 2557; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-700 block">Theme</label>
                <select id="filterTheme" class="w-full border rounded px-2 py-1 text-sm">
                    <option value="">ทั้งหมด</option>
                    @foreach ($themes as $theme)
                        <option value="{{ $theme }}">{{ $theme }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-700 block">ประเภททุน</label>
                <select id="filterFunding" class="w-full border rounded px-2 py-1 text-sm">
                    <option value="">ทั้งหมด</option>
                    <option value="ภายใน">ภายใน</option>
                    <option value="ภายนอก">ภายนอก</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-700 block">ค้นหาโครงการ</label>
                <input type="text" id="filterName" class="w-full border rounded px-2 py-1 text-sm" placeholder="ชื่อโครงการ">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-700 block">ค้นหา PI</label>
                <input type="text" id="filterPI" class="w-full border rounded px-2 py-1 text-sm" placeholder="ชื่อ PI">
            </div>
            <div class="flex items-end">
                <button id="exportCSV" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 text-sm rounded shadow">
                    ⬇ Export CSV
                </button>
            </div>
        </div>
    </div>


</div> <!-- end container -->

<!-- Scripts -->


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const projects = @json($projects);
const outputs = @json($outputs);
const ftes = @json($fteRoles);
const irbs = @json($irbs);

// Chart 1: Project by PI / Year
(function() {
    const ctx = document.getElementById('chartPiYear').getContext('2d');
    const group = {};
    projects.forEach(p => {
        const pi = p.pi_name || 'ไม่ระบุ';
        const y = p.start_year || 'ไม่ระบุ';
        group[pi] ??= {};
        group[pi][y] = (group[pi][y] || 0) + 1;
    });
    const years = [...new Set(projects.map(p => p.start_year))].sort();
    const datasets = Object.entries(group).map(([pi, counts]) => ({
        label: pi,
        data: years.map(y => counts[y] || 0),
        backgroundColor: `hsl(${Math.random()*360}, 70%, 60%)`
    }));
    new Chart(ctx, {
        type: 'bar',
        data: { labels: years, datasets },
        options: { plugins: { legend: { position: 'bottom' } }, responsive: true, scales: { x: { stacked: true }, y: { stacked: true } } }
    });
})();

// Chart 2: Project Status by Year
// Chart: Project Status per Year
(function() {
    const ctx = document.getElementById('chartStatusYear').getContext('2d');

    const statuses = [
        'Project Planing (เริ่มต้น - submit IRB)',
        'เก็บข้อมูล',
        'วิเคราะห์ข้อมูล',
        'เขียนรายงาน และ publication',
        'ปิดโปรเจค',
        'ยกเลิกโปรเจค'
    ];

    const colors = ['#60a5fa', '#34d399', '#fbbf24', '#f472b6', '#a78bfa', '#f87171'];

    const statusData = {}; // year → status → count
    projects.forEach(p => {
        const year = p.start_year || 'ไม่ระบุ';
        const status = p.planning_status || 'ไม่ระบุ';
        if (!statusData[year]) {
            statusData[year] = {};
            statuses.forEach(s => statusData[year][s] = 0);
        }
        if (statuses.includes(status)) {
            statusData[year][status]++;
        }
    });

    const years = Object.keys(statusData).sort();
    const datasets = statuses.map((status, i) => ({
        label: status,
        backgroundColor: colors[i],
        data: years.map(y => statusData[y][status])
    }));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: years,
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { stacked: true },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    title: { display: true, text: 'จำนวนโครงการ' }
                }
            }
        }
    });
})();

// Chart 3: Output Timeline
(function() {
    const ctx = document.getElementById('chartOutputTimeline').getContext('2d');
    const yearGroup = {};
    outputs.forEach(o => {
        const y = o.year || 'ไม่ระบุ';
        yearGroup[y] = (yearGroup[y] || 0) + 1;
    });
    const years = Object.keys(yearGroup).sort();
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: years,
            datasets: [{
                label: 'Output',
                data: years.map(y => yearGroup[y]),
                fill: true,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.2)'
            }]
        },
        options: { responsive: true }
    });
})();

// Chart 4: IRB Expiry
(function() {
    const ctx = document.getElementById('chartIRBExpiry').getContext('2d');
    const now = new Date();
    const expGroup = {};
    irbs.forEach(irb => {
        const exp = new Date(irb.exp_date);
        const key = `${exp.getMonth()+1}/${exp.getFullYear()}`;
        const diff = (exp - now) / (1000 * 60 * 60 * 24 * 30);
        if (diff >= 0 && diff <= 6) {
            expGroup[key] = (expGroup[key] || 0) + 1;
        }
    });
    const labels = Object.keys(expGroup).sort((a, b) => new Date(a) - new Date(b));
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'IRB ใกล้หมดอายุ',
                data: labels.map(l => expGroup[l]),
                backgroundColor: '#EF4444'
            }]
        },
        options: { responsive: true }
    });
})();

// Chart 5: FTE Summary as Bubble
(function() {
    const ctx = document.getElementById('chartFteSummary').getContext('2d');
    const fteData = {};

    ftes.forEach(f => {
        const name = f.name || 'ไม่ระบุ';
        if (!fteData[name]) {
            fteData[name] = { fte: 0, projectSet: new Set() };
        }
        fteData[name].fte += parseFloat(f.fte_value || 0);
        fteData[name].projectSet.add(f.project_id);
    });

    const datasets = Object.entries(fteData).map(([name, data]) => {
        return {
            label: name,
            data: [{
                x: data.projectSet.size,
                y: data.fte,
                r: Math.max(5, data.fte * 10)
            }]
        };
    });

    new Chart(ctx, {
        type: 'bubble',
        data: {
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const d = ctx.raw;
                            return `${ctx.dataset.label}: โครงการ ${d.x} / FTE ${d.y.toFixed(2)}`;
                        }
                    }
                },
                legend: { display: false }
            },
            scales: {
                x: {
                    title: { display: true, text: 'จำนวนโครงการ' }
                },
                y: {
                    title: { display: true, text: 'FTE รวม' },
                    beginAtZero: true
                }
            }
        }
    });
})();

// Export CSV
document.getElementById('exportCSV').addEventListener('click', () => {
    const csv = [["ชื่อโครงการ", "PI", "ปี", "Co-PI", "CoResearch", "FTE", "Output", "IRB ล่าสุด"]];
    projects.forEach(p => {
        const latestIRB = (p.irbs?.length ?? 0) > 0
            ? new Date(p.irbs.sort((a, b) => new Date(b.exp_date) - new Date(a.exp_date))[0].exp_date).toLocaleDateString("th-TH")
            : "ไม่มี";
        csv.push([
            p.project_name_th,
            p.pi_name,
            p.start_year,
            p.copis?.length ?? 0,
            p.co_researches?.length ?? 0,
            (p.fte_roles ?? []).reduce((sum, r) => sum + parseFloat(r.fte_value || 0), 0).toFixed(2),
            p.outputs?.length ?? 0,
            latestIRB
        ]);
    });
    const content = "\uFEFF" + csv.map(r => r.join(",")).join("\n");
    const blob = new Blob([content], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "dashboard_export.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});
</script>


<script>
const projects = @json($projects);
const outputs = @json($outputs);
const ftes = @json($fteRoles);
const irbs = @json($irbs);
const progressReports = @json($progressReports);

// 📌 Helper: แสดง Modal
function showModal(title, htmlBody) {
    document.getElementById('chartModalTitle').innerText = title;
    document.getElementById('chartModalBody').innerHTML = htmlBody;
    document.getElementById('chartModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('chartModal').classList.add('hidden');
}

// ✅ 1. FTE Drill-down (Pie)
document.getElementById('chartFteSummary').onclick = function (evt) {
    const chart = Chart.getChart('chartFteSummary');
    const point = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true)[0];
    if (!point) return;

    const label = chart.data.labels[point.index];
    const fteFiltered = ftes.filter(f => f.job_detail === label);

    let html = `<table class="table-auto w-full border text-sm">
        <thead><tr><th class="p-2">ชื่อ</th><th class="p-2">ตำแหน่ง</th><th class="p-2">โครงการ</th><th class="p-2">FTE</th></tr></thead><tbody>`;
    fteFiltered.forEach(f => {
        html += `<tr class="border-t">
            <td class="p-2">${f.name}</td>
            <td class="p-2">${f.role_group}</td>
            <td class="p-2 text-blue-600"><a href="/projects/${f.project_id}" target="_blank">${f.project_name}</a></td>
            <td class="p-2 text-center">${f.fte}</td>
        </tr>`;
    });
    html += `</tbody></table>`;
    showModal(`📈 รายชื่อผู้ร่วมในตำแหน่ง "${label}"`, html);
};

// ✅ 2. Output Timeline Drill-down
document.getElementById('chartOutputTimeline').onclick = function (evt) {
    const chart = Chart.getChart('chartOutputTimeline');
    const point = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true)[0];
    if (!point) return;

    const label = chart.data.labels[point.index];
    const year = label.split('/').pop();
    const outputFiltered = outputs.filter(o => o.export_year == year);

    let html = `<table class="table-auto w-full border text-sm">
        <thead><tr><th class="p-2">ชื่อผลงาน</th><th class="p-2">ประเภท</th><th class="p-2">โครงการ</th></tr></thead><tbody>`;
    outputFiltered.forEach(o => {
        html += `<tr class="border-t">
            <td class="p-2">${o.title}</td>
            <td class="p-2">${o.type}</td>
            <td class="p-2 text-blue-600"><a href="/projects/${o.project_id}" target="_blank">${o.project_name}</a></td>
        </tr>`;
    });
    html += `</tbody></table>`;
    showModal(`📝 Output ในปี พ.ศ. ${year}`, html);
};

// ✅ 3. IRB Expiry Drill-down (ทำแล้ว)
document.getElementById('chartIRBExpiry').onclick = function(evt) {
    const chart = Chart.getChart('chartIRBExpiry');
    const point = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true)[0];
    if (!point) return;

    const label = chart.data.labels[point.index];
    const filteredProjects = projects.filter(p =>
        p.irbs?.some(irb => {
            const exp = new Date(irb.exp_date);
            return `${exp.getMonth()+1}/${exp.getFullYear()}` === label;
        })
    );

    let html = `<table class="table-auto w-full text-sm border"><thead><tr>
        <th class="p-2">ชื่อโครงการ</th>
        <th class="p-2">PI</th>
        <th class="p-2">วันหมดอายุ IRB</th></tr></thead><tbody>`;

    filteredProjects.forEach(p => {
        const latestIRB = p.irbs?.sort((a,b) => new Date(b.exp_date) - new Date(a.exp_date))[0];
        const exp = latestIRB ? new Date(latestIRB.exp_date).toLocaleDateString('th-TH') : '❌';
        html += `<tr class="border-t"><td class="p-2 text-blue-600">
                    <a href="/projects/${p.id}" target="_blank">${p.project_name_th}</a></td>
                    <td class="p-2">${p.pi_name}</td>
                    <td class="p-2">${exp}</td></tr>`;
    });
    html += `</tbody></table>`;
    showModal('📅 โครงการที่ IRB ใกล้หมดอายุ', html);
};

// ✅ 4. Project Status per Year Drill-down
document.getElementById('chartStatusPerYear').onclick = function (evt) {
    const chart = Chart.getChart('chartStatusPerYear');
    const point = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true)[0];
    if (!point) return;

    const year = chart.data.labels[point.index];
    const status = chart.data.datasets[point.datasetIndex].label;
    const filtered = projects.filter(p => p.start_year == year && p.planning_status == status);

    let html = `<table class="table-auto w-full border text-sm">
        <thead><tr><th class="p-2">ชื่อโครงการ</th><th class="p-2">PI</th><th class="p-2">สถานะ</th></tr></thead><tbody>`;
    filtered.forEach(p => {
        html += `<tr class="border-t">
            <td class="p-2 text-blue-600"><a href="/projects/${p.id}" target="_blank">${p.project_name_th}</a></td>
            <td class="p-2">${p.pi_name}</td>
            <td class="p-2">${p.planning_status}</td>
        </tr>`;
    });
    html += `</tbody></table>`;
    showModal(`📋 โครงการปี ${year} ที่มีสถานะ "${status}"`, html);
};
</script>

@endsection

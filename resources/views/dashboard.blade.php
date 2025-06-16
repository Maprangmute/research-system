@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-3 md:p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">📊 ภาพรวมโครงการวิจัย (Dashboard)</h2>

<!-- 1. Filter Panel (Redesigned) -->
<div class="dashboard-filter bg-white rounded-xl shadow-sm p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
        <!-- ปีเริ่มต้น -->
        <div>
            <label class="flex items-center text-xs font-semibold text-gray-700 mb-1">
                <svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3M16 7V3M4 11H20M5 19H19M3 11V19A2 2 0 0 0 5 21H19A2 2 0 0 0 21 19V11"></path></svg>
                ปีเริ่มต้น
            </label>
            <select id="filterStartYear" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">ทั้งหมด</option>
                @for ($year = now()->year + 543; $year >= 2557; $year--)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endfor
            </select>
        </div>
        <!-- Theme -->
        <div>
            <label class="flex items-center text-xs font-semibold text-gray-700 mb-1">
                <svg class="w-4 h-4 mr-1 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"></path></svg>
                Theme
            </label>
            <select id="filterTheme" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">ทั้งหมด</option>
                @foreach ($themes as $theme)
                    <option value="{{ $theme }}">{{ $theme }}</option>
                @endforeach
            </select>
        </div>
        <!-- ทุน -->
        <div>
            <label class="flex items-center text-xs font-semibold text-gray-700 mb-1">
                <svg class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8V4m0 4c-4.418 0-8 2.686-8 6s3.582 6 8 6 8-2.686 8-6-3.582-6-8-6z"/></svg>
                ประเภททุน
            </label>
            <select id="filterFundingType" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">ทั้งหมด</option>
                <option value="ภายใน">ภายใน</option>
                <option value="ภายนอก">ภายนอก</option>
            </select>
        </div>
        <!-- ค้นหาชื่อโครงการ -->
        <div>
            <label class="flex items-center text-xs font-semibold text-gray-700 mb-1">
                <svg class="w-4 h-4 mr-1 text-pink-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                ค้นหาชื่อโครงการ
            </label>
            <input type="text" id="filterName" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="ชื่อโครงการ">
        </div>
        <!-- ค้นหา PI -->
        <div>
            <label class="flex items-center text-xs font-semibold text-gray-700 mb-1">
                <svg class="w-4 h-4 mr-1 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h0a4 4 0 0 1 4 4v2"></path></svg>
                ค้นหา PI
            </label>
            <input type="text" id="filterPI" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="ชื่อ PI">
        </div>
        <!-- Export -->
        <div class="flex items-end">
            <button id="exportCSV" class="w-full bg-emerald-600 hover:bg-emerald-700 transition text-white px-3 py-2 rounded-lg shadow">
                <svg class="w-4 h-4 inline-block mr-1 -mt-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 16v-8m0 8l-4-4m4 4l4-4M4 20h16"/></svg>
                Export CSV
            </button>
        </div>
    </div>
</div>


    <!-- 2. Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-lg font-bold" id="totalProjects">-</div>
            <div class="text-xs text-gray-600">จำนวนโครงการ</div>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-lg font-bold" id="totalBudget">-</div>
            <div class="text-xs text-gray-600">งบประมาณรวม (บาท)</div>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-lg font-bold" id="totalPIs">-</div>
            <div class="text-xs text-gray-600">จำนวน PI</div>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <div class="text-lg font-bold" id="totalOutputs">-</div>
            <div class="text-xs text-gray-600">จำนวน Outputs</div>
        </div>
    </div>

    <!-- 3. Chart Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- 3.1 Projects by Year & Status (Stacked Bar) -->
        <div class="bg-white rounded shadow p-3">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-bold">จำนวนโครงการแต่ละปี (แยกตามสถานะ)</h3>
                <select id="limitStatus" class="border rounded p-1 text-xs">
                    <option value="25">แสดง 25 ปีล่าสุด</option>
                    <option value="50">แสดง 50 ปีล่าสุด</option>
                    <option value="75">แสดง 75 ปีล่าสุด</option>
                    <option value="100">แสดง 100 ปีล่าสุด</option>
                    <option value="all" selected>ทั้งหมด</option>
                </select>
            </div>
            <div style="min-height:260px;">
                <canvas id="projectByYearChart"></canvas>
            </div>
        </div>
        <!-- 3.2 Pie/Donut: Theme -->
        <div class="bg-white rounded shadow p-3">
            <h3 class="text-sm font-bold mb-2">สัดส่วน Theme โครงการ</h3>
            <canvas id="themePieChart" height="220"></canvas>
        </div>
        <!-- 3.3 Funding Type/Size (Bar) -->
        <div class="bg-white rounded shadow p-3 md:col-span-2">
            <h3 class="text-sm font-bold mb-2">จำนวนโครงการตามประเภททุน/ขนาด</h3>
            <canvas id="fundingBarChart" height="200"></canvas>
        </div>
        <!-- 3.4 Outputs by Type (Pie) -->
        <div class="bg-white rounded shadow p-3">
            <h3 class="text-sm font-bold mb-2">Outputs แยกตามประเภท</h3>
            <canvas id="outputPieChart" height="220"></canvas>
        </div>
        <!-- 3.5 Budget by Year (Line) -->
        <div class="bg-white rounded shadow p-3">
            <h3 class="text-sm font-bold mb-2">งบประมาณรวมแต่ละปี</h3>
            <canvas id="budgetLineChart" height="220"></canvas>
        </div>
    </div>

    <!-- 4. Table Section -->
    <div class="bg-white rounded shadow p-3 mb-10 overflow-x-auto">
        <h3 class="text-sm font-bold mb-2">📋 รายชื่อโครงการ</h3>
        <table class="w-full text-sm border">
            <thead>
                <tr class="bg-gray-100 text-xs">
                    <th class="p-2">ชื่อโครงการ</th>
                    <th class="p-2">Theme</th>
                    <th class="p-2">ปี</th>
                    <th class="p-2">ทุน</th>
                    <th class="p-2">PI</th>
                    <th class="p-2">ขนาด</th>
                    <th class="p-2">สถานะ</th>
                    <th class="p-2">งบประมาณ</th>
                    <th class="p-2">Output</th>
                </tr>
            </thead>
            <tbody id="projectTableBody">
                <!-- JS FILL -->
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const projects = @json($projects);

// สถานะ (ภาษาไทย/รหัส/สี)
const statusDefs = [
    {key: 'planning_status',   label: 'วางแผน',     color: '#3B82F6', code: 1},
    {key: 'execution_status',  label: 'ดำเนินการ',   color: '#22C55E', code: 2},
    {key: 'review_status',     label: 'ตรวจสอบ',    color: '#F59E42', code: 3},
    {key: 'closure_status',    label: 'ปิดโครงการ',  color: '#EF4444', code: 4},
];

// Filter state
let filter = { year: "", theme: "", funding: "", name: "", pi: "" };
let statusLimit = 'all';

// Utils
function numFormat(x) {
    if (x === null || x === undefined) return "-";
    return x.toLocaleString('th-TH');
}
function filterProjects() {
    return projects.filter(p => {
        let pass = true;
        if (filter.year && String(p.start_year) !== filter.year) pass = false;
        if (filter.theme && p.theme !== filter.theme) pass = false;
        if (filter.funding && p.funding_type !== filter.funding) pass = false;
        if (filter.name && !(p.project_name_th || '').toLowerCase().includes(filter.name)) pass = false;
        if (filter.pi && !(p.pi_name || '').toLowerCase().includes(filter.pi)) pass = false;
        return pass;
    });
}

// Summary Card
function updateSummary(filtered) {
    document.getElementById('totalProjects').textContent = filtered.length;
    document.getElementById('totalBudget').textContent = numFormat(filtered.reduce((sum, p) => sum + (Number(p.budget) || 0), 0));
    document.getElementById('totalPIs').textContent = new Set(filtered.map(p => p.pi_name)).size;
    let outputCount = 0;
    filtered.forEach(p => { if(p.outputs) outputCount += p.outputs.length; });
    document.getElementById('totalOutputs').textContent = outputCount;
}

// Chart.js holders
let charts = {};

// Chart 1: Stacked bar - โครงการแต่ละปี/สถานะ
document.getElementById('limitStatus').addEventListener('change', (e) => {
    statusLimit = e.target.value;
    renderProjectByYear(filterProjects());
});
function renderProjectByYear(filtered) {
    const ctx = document.getElementById('projectByYearChart').getContext('2d');
    if (charts.projectByYear) charts.projectByYear.destroy();

    let allYears = [...new Set(filtered.map(p => p.start_year))].sort((a,b) => b-a);
    let years = statusLimit === 'all' ? allYears : allYears.slice(0, +statusLimit);
    years = years.sort();

    // แต่ละสถานะ: วางแผน, ดำเนินการ, ตรวจสอบ, ปิดโครงการ
    let datasets = statusDefs.map(def => ({
        label: def.label,
        backgroundColor: def.color,
        data: years.map(y =>
            filtered.filter(p =>
                String(p.start_year) === String(y)
                && p[def.key] && Number(p[def.key]) === def.code
            ).length
        ),
        maxBarThickness: 36,
        borderWidth: 1,
        borderRadius: 4
    }));

    charts.projectByYear = new Chart(ctx, {
        type: 'bar',
        data: { labels: years, datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            aspectRatio: 2.5,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 14 } } },
                tooltip: { callbacks: { label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}` } }
            },
            scales: {
                x: {
                    stacked: true,
                    title: { display: true, text: "ปี" },
                    ticks: { font: { size: 13 } }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    precision: 0,
                    ticks: {
                        precision: 0,
                        callback: v => Number(v).toLocaleString('th-TH')
                    },
                    title: { display: true, text: "จำนวนโครงการ" }
                }
            },
            elements: { bar: { maxBarThickness: 36 } }
        }
    });
}

// Chart 2: Pie - Theme
function renderThemePie(filtered) {
    const ctx = document.getElementById('themePieChart').getContext('2d');
    if (charts.themePie) charts.themePie.destroy();
    let group = {};
    filtered.forEach(p => {
        group[p.theme || 'ไม่ระบุ'] = (group[p.theme || 'ไม่ระบุ'] || 0) + 1;
    });
    charts.themePie = new Chart(ctx, {
        type: 'pie',
        data: { labels: Object.keys(group), datasets: [{ data: Object.values(group) }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}

// Chart 3: Funding Bar
function renderFundingBar(filtered) {
    const ctx = document.getElementById('fundingBarChart').getContext('2d');
    if (charts.fundingBar) charts.fundingBar.destroy();
    let groups = {};
    filtered.forEach(p => {
        const key = `${p.funding_type || 'ไม่ระบุ'} | ${p.size || 'ไม่ระบุ'}`;
        groups[key] = (groups[key] || 0) + 1;
    });
    charts.fundingBar = new Chart(ctx, {
        type: 'bar',
        data: { labels: Object.keys(groups), datasets: [{ label: 'จำนวนโครงการ', data: Object.values(groups) }] },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            indexAxis: 'y'
        }
    });
}

// Chart 4: Output Pie
function renderOutputPie(filtered) {
    const ctx = document.getElementById('outputPieChart').getContext('2d');
    if (charts.outputPie) charts.outputPie.destroy();
    let typeGroup = {};
    filtered.forEach(p => {
        (p.outputs || []).forEach(o => {
            const type = o.type || 'ไม่ระบุ';
            typeGroup[type] = (typeGroup[type] || 0) + 1;
        });
    });
    charts.outputPie = new Chart(ctx, {
        type: 'doughnut',
        data: { labels: Object.keys(typeGroup), datasets: [{ data: Object.values(typeGroup) }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}

// Chart 5: Line - Budget per year
function renderBudgetLine(filtered) {
    const ctx = document.getElementById('budgetLineChart').getContext('2d');
    if (charts.budgetLine) charts.budgetLine.destroy();
    let yearGroup = {};
    filtered.forEach(p => {
        let y = p.start_year;
        yearGroup[y] = (yearGroup[y] || 0) + (Number(p.budget) || 0);
    });
    let labels = Object.keys(yearGroup).sort();
    let values = labels.map(y => yearGroup[y]);
    charts.budgetLine = new Chart(ctx, {
        type: 'line',
        data: { labels: labels, datasets: [{ label: 'งบประมาณ (บาท)', data: values, fill: true }] },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });
}

// Table Renderer
function renderTable(filtered) {
    const tb = document.getElementById('projectTableBody');
    tb.innerHTML = filtered.map(p => {
        // หาสถานะล่าสุด (ตามลำดับ priority)
        let status = statusDefs.map(def => Number(p[def.key]) === def.code ? def : null).filter(Boolean)[0];
        return `<tr>
            <td class="p-2">${p.project_name_th || "-"}</td>
            <td class="p-2">${p.theme || "-"}</td>
            <td class="p-2">${p.start_year || "-"}</td>
            <td class="p-2">${p.funding_type || "-"}</td>
            <td class="p-2">${p.pi_name || "-"}</td>
            <td class="p-2">${p.size || "-"}</td>
            <td class="p-2">
                ${status
                    ? `<span class="inline-block rounded px-2 py-1 text-white text-xs" style="background:${status.color}">${status.label}</span>`
                    : '-'}
            </td>
            <td class="p-2 text-right">${numFormat(Number(p.budget) || 0)}</td>
            <td class="p-2">${(p.outputs||[]).length}</td>
        </tr>`;
    }).join('');
}

// Master render
function renderAll() {
    const filtered = filterProjects();
    updateSummary(filtered);
    renderProjectByYear(filtered);
    renderThemePie(filtered);
    renderFundingBar(filtered);
    renderOutputPie(filtered);
    renderBudgetLine(filtered);
    renderTable(filtered);
}

// Event Bindings (filter usability: Enter/change ทุกช่อง)
document.querySelectorAll('.dashboard-filter input, .dashboard-filter select').forEach(el => {
    el.addEventListener('keyup', e => { if(e.key==='Enter') renderAll(); });
    el.addEventListener('change', renderAll);
});

// Export CSV
document.getElementById('exportCSV').addEventListener('click', () => {
    const filtered = filterProjects();
    let csv = "ชื่อโครงการ,Theme,ปี,ประเภททุน,PI,ขนาด,สถานะ,งบประมาณ,Outputs\n" +
        filtered.map(p =>
            `"${p.project_name_th}","${p.theme}","${p.start_year}","${p.funding_type}","${p.pi_name}","${p.size}","${
                statusDefs.map(def => Number(p[def.key]) === def.code ? def.label : '').filter(Boolean)[0] || ''
            }","${p.budget || ''}","${(p.outputs||[]).length}"`
        ).join("\n");
    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "projects_export.csv";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});

// Init
renderAll();
window.addEventListener('resize', () => renderAll());
</script>
@endsection

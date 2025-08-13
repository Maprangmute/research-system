@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-4 space-y-6">

    <h2 class="text-2xl font-bold text-gray-800 mb-2">📊 ภาพรวมโครงการวิจัย (Research Dashboard)</h2>

    <!-- 1) Summary Cards -->
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

    <!-- 2) กรอง + Export -->
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

    <!-- 3) Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        <div class="bg-white shadow-sm border rounded-xl p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">📈 จำนวนโครงการรายปี (รวม)</h3>
            <canvas id="chartProjectsPerYear" height="250"></canvas>
        </div>

        <div class="bg-white shadow-sm border rounded-xl p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">🏷️ สัดส่วนทุน ภายใน/ภายนอก (รายปี)</h3>
            <canvas id="chartFundingSplit" height="250"></canvas>
        </div>

        <div class="bg-white shadow-sm border rounded-xl p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">💰 งบประมาณที่ได้รับ (รวมรายปี)</h3>
            <canvas id="chartBudgetPerYear" height="250"></canvas>
        </div>

        <div class="bg-white shadow-sm border rounded-xl p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">📝 Output (Stacked by Type / Year)</h3>
            <canvas id="chartOutputStacked" height="250"></canvas>
        </div>

        <div class="bg-white shadow-sm border rounded-xl p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">📚 Publication รายปี</h3>
            <canvas id="chartPublicationPerYear" height="250"></canvas>
        </div>

        <div class="bg-white shadow-sm border rounded-xl p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">🧩 โครงการตามสถานะ (Stacked)</h3>
            <canvas id="chartStatusYear" height="250"></canvas>
        </div>

     

        <div class="bg-white shadow-sm border rounded-xl p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">🤝 ความร่วมมือ (นอกคณะ/ข้ามหน่วย/นอกมหาวิทยาลัย)</h3>
            <canvas id="chartCollabBreakdown" height="250"></canvas>
        </div>

        <div class="bg-white shadow-sm border rounded-xl p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">👨‍🔬 จำนวน PI ต่อหน่วยงาน</h3>
            <canvas id="chartPIsPerUnit" height="250"></canvas>
        </div>

        <div class="bg-white shadow-sm border rounded-xl p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">🏦 ทุนภายนอก แยกตามแหล่งทุน</h3>
            <canvas id="chartExternalBySource" height="250"></canvas>
        </div>

        <div class="bg-white shadow-sm border rounded-xl p-4 md:col-span-2">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">📅 โครงการทั้งหมด vs โครงการใหม่ (ต่อปี)</h3>
            <canvas id="chartYearlyNewVsTotal" height="280"></canvas>
        </div>

        <div class="bg-white shadow-sm border rounded-xl p-4 md:col-span-2">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">📈 การกระจาย FTE (Bubble)</h3>
            <canvas id="chartFteSummary" height="300"></canvas>
        </div>
    </div>

    <!-- 4) Co-Research Summary -->
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

    <!-- 5) Project Table -->
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

</div> <!-- end container -->

<!-- Modal สำหรับ Drill-down -->
<div id="chartModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" onclick="closeModal()"></div>
  <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-xl w-[92vw] max-w-5xl max-h-[80vh] overflow-auto">
    <div class="flex items-center justify-between border-b px-4 py-3">
      <h3 id="chartModalTitle" class="font-semibold text-gray-800">รายละเอียด</h3>
      <button class="text-gray-500 hover:text-red-500 text-xl" onclick="closeModal()">&times;</button>
    </div>
    <div id="chartModalBody" class="p-4 text-sm"></div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* ---------- Data from backend ---------- */
const projects         = @json($projects);
const outputs          = @json($outputs);
const ftes             = @json($fteRoles);
const irbs             = @json($irbs);
const progressReports  = @json($progressReports);

/* ---------- Helpers ---------- */
const toNumber = v => isNaN(+v) ? 0 : +v;
const thaiYear = y => y; // ถ้า start_year เก็บเป็น พ.ศ. อยู่แล้วก็คืนตามเดิม
const getYear = (p) => p.start_year || 'ไม่ระบุ';
const getEndYear = (p) => p.end_year || p.finish_year || null; // ปรับตามฟิลด์จริง
const getBudget = (p) => toNumber(p.total_budget ?? p.budget ?? 0);
const getFundingType = (p) => p.funding_type || 'ไม่ระบุ';
const getFundingSource = (p) => p.funding_source || 'ไม่ระบุ';
const getUnit = (p) => p.unit_name || p.department || 'ไม่ระบุ';

const outputYear = (o) => o.year ?? o.export_year ?? 'ไม่ระบุ';
const outputType = (o) => (o.type || 'อื่นๆ').trim();


function categorizeCollab(p){
  // ถ้ามี p.collab_type ให้ใช้เลย เช่น: 'outside_faculty' | 'cross_dept_in_med' | 'outside_university'
  if (p.collab_type) {
    const map = {
      outside_faculty: 'นอกคณะ',
      cross_dept_in_med: 'ข้ามหน่วย (ในคณะแพทย์)',
      outside_university: 'นอกมหาวิทยาลัย'
    };
    return map[p.collab_type] || 'ไม่ระบุ';
  }
  // ถ้าไม่มีฟิลด์ ให้คืน 'ไม่ระบุ' (หรือเพิ่ม logic จาก affiliation ได้)
  return 'ไม่ระบุ';
}

function showModal(title, htmlBody) {
  document.getElementById('chartModalTitle').innerText = title;
  document.getElementById('chartModalBody').innerHTML = htmlBody;
  document.getElementById('chartModal').classList.remove('hidden');
}
function closeModal(){ document.getElementById('chartModal').classList.add('hidden'); }

/* ---------- 1) จำนวนโครงการรายปี (Bar + ตัวเลขบนแท่ง) ---------- */
(function () {
  // รวมจำนวนโครงการต่อปี
  const counts = new Map();
  projects.forEach(p => {
    const y = (getYear(p) ?? '').toString().trim() || 'ไม่ระบุ';
    counts.set(y, (counts.get(y) || 0) + 1);
  });

  // ปีตัวเลขก่อน แล้วค่อยหมวดอื่น
  const isNumeric = (s) => /^\d+$/.test(s);
  const numericYears = [...counts.keys()].filter(isNumeric).map(Number).sort((a,b)=>a-b).map(String);
  const otherYears   = [...counts.keys()].filter(k => !isNumeric(k)).sort();
  const labels = numericYears.concat(otherYears);

  const data   = labels.map(y => counts.get(y) ?? 0);
  const colors = labels.map((_, i) => `hsl(${(i * 37) % 360} 70% 60%)`);

  // ทำลายกราฟเดิมถ้ามี
  const el = document.getElementById('chartProjectsPerYear');
  const prev = Chart.getChart(el);
  if (prev) prev.destroy();

  // ปลั๊กอินวาดตัวเลขบนแท่ง (ไม่ง้อ chartjs-plugin-datalabels)
  const barValuePlugin = {
    id: 'barValuePlugin',
    afterDatasetsDraw(chart, args, pluginOptions) {
      const { ctx } = chart;
      ctx.save();
      ctx.font = 'bold 11px sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'bottom';
      ctx.fillStyle = '#374151'; // เทาเข้มอ่านง่าย

      const meta = chart.getDatasetMeta(0);
      const ds   = chart.data.datasets[0];

      meta.data.forEach((bar, i) => {
        const v = ds.data[i];
        if (v == null) return;
        const x = bar.x;
        const y = bar.y - 6;  // ระยะห่างเหนือแท่ง
        ctx.fillText(String(v), x, y);
      });

      ctx.restore();
    }
  };

  new Chart(el, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'จำนวนโครงการ',
        data,
        backgroundColor: colors,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      layout: { padding: { top: 18 } }, // เผื่อพื้นที่ให้ตัวเลขด้านบน
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: { label: (ctx) => ` ${ctx.raw} โครงการ` }
        }
      },
      scales: {
        y: { beginAtZero: true, title: { display: true, text: 'จำนวนโครงการ' } },
        x: { title: { display: true, text: 'ปี' } }
      }
    },
    plugins: [barValuePlugin]
  });
})();


(function(){
  const years = [...new Set(projects.map(getYear))].sort();
  const types = ['ภายใน','ภายนอก'];
  const matrix = {};
  years.forEach(y=> (matrix[y] = { 'ภายใน':0, 'ภายนอก':0 }));
  projects.forEach(p => {
    const y = getYear(p);
    const t = getFundingType(p);
    if (matrix[y] && (t==='ภายใน' || t==='ภายนอก')) matrix[y][t] += 1;
  });
  const datasets = types.map(t => ({
    label: t,
    data: years.map(y => matrix[y][t]),
    backgroundColor: t==='ภายใน' ? '#60a5fa' : '#34d399',
    stack: 'fund'
  }));

  // ปลั๊กอินโชว์ตัวเลขบนแท่ง
  const barValuePlugin = {
    id: 'barValue',
    afterDatasetsDraw(chart) {
      const { ctx } = chart;
      ctx.save();
      ctx.font = 'bold 11px sans-serif';
      ctx.fillStyle = '#111827';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'bottom';

      chart.data.datasets.forEach((dataset, datasetIndex) => {
        chart.getDatasetMeta(datasetIndex).data.forEach((bar, index) => {
          const value = dataset.data[index];
          if (value > 0) {
            ctx.fillText(value, bar.x, bar.y - 4);
          }
        });
      });

      ctx.restore();
    }
  };

  new Chart(document.getElementById('chartFundingSplit'), {
    type:'bar',
    data:{ labels: years, datasets },
    options:{
      responsive:true,
      plugins:{ legend:{ position:'bottom' } },
      scales:{ x:{ stacked:true }, y:{ stacked:true, beginAtZero:true } }
    },
    plugins: [barValuePlugin] // เรียกใช้ปลั๊กอิน
  });
})();


/* ---------- 3) งบประมาณรวมรายปี (Bar + Data Label หน่วย พันบาท/ล้านบาท) ---------- */
(function(){
  const byYear = {};
  projects.forEach(p=>{
    const y = getYear(p);
    byYear[y] = (byYear[y]||0) + getBudget(p);
  });
  const years = Object.keys(byYear).sort();

  // ฟอร์แมตหน่วยเงินไทย
  function formatMoneyTHB(v){
    if (v >= 1_000_000) return (v/1_000_000).toFixed(2) + ' ล้านบาท';
    if (v >= 1_000)     return (v/1_000).toFixed(2)     + ' พันบาท';
    return v.toLocaleString('th-TH') + ' บาท';
  }

  // ปลั๊กอินวาดเลขบนแท่ง (ไม่พึ่ง chartjs-plugin-datalabels)
  const budgetValueLabelPlugin = {
    id: 'budgetValueLabel',
    afterDatasetsDraw(chart){
      const { ctx, chartArea: { top } } = chart;
      const meta = chart.getDatasetMeta(0);
      const ds   = chart.data.datasets[0];
      if (!meta || !ds) return;

      ctx.save();
      ctx.font = 'bold 11px sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'bottom';
      ctx.fillStyle = '#111827';

      meta.data.forEach((bar, i) => {
        const v = Number(ds.data[i] ?? 0);
        if (!isFinite(v) || v <= 0) return;

        const label = formatMoneyTHB(v);
        let y = bar.y - 6;              // วางเหนือแท่งเล็กน้อย
        if (y < top + 6) y = top + 6;   // กันเลขตกขอบบน
        ctx.fillText(label, bar.x, y);
      });

      ctx.restore();
    }
  };

  const el = document.getElementById('chartBudgetPerYear');
  const prev = Chart.getChart(el); if (prev) prev.destroy();

  new Chart(el, {
    type:'bar',
    data:{
      labels: years,
      datasets:[{
        label:'งบประมาณ (รวม)',
        data: years.map(y=>byYear[y]),
        backgroundColor: '#fbbf24',
        borderRadius: 6
      }]
    },
    options:{
      responsive:true,
      layout:{ padding:{ top: 22 } }, // เผื่อที่ให้ label ด้านบน
      plugins:{
        legend:{ position:'bottom' },
        tooltip:{
          callbacks:{
            label: (ctx)=> formatMoneyTHB(ctx.raw)
          }
        }
      },
      scales:{
        y:{
          beginAtZero:true,
          ticks:{
            callback: (value)=> formatMoneyTHB(value)
          },
          title:{ display:true, text:'งบประมาณ' }
        },
        x:{ title:{ display:true, text:'ปี' } }
      }
    },
    plugins: [budgetValueLabelPlugin]
  });
})();


/* ---------- 4) Output Stacked by Type / Year ---------- */
(function(){
  function safeYear(val) {
    if (!val) return 'ไม่ระบุ';
    const str = String(val).trim();
    if (/^\d{4}$/.test(str)) return str;
    const match = str.match(/(25\d{2}|20\d{2}|19\d{2})/);
    if (match) return match[1];
    const d = new Date(str);
    if (!isNaN(d)) return String(d.getFullYear());
    return 'ไม่ระบุ';
  }

  const years = [...new Set(outputs.map(o => safeYear(o.year ?? o.date ?? o.created_at)))];
  const isNum = s => /^\d+$/.test(s);
  const sortedYears = years.filter(isNum).map(Number).sort((a,b)=>a-b).map(String)
                     .concat(years.filter(y => !isNum(y)));

  const types = [...new Set(outputs.map(outputType))].sort();
  const grid = {};
  sortedYears.forEach(y => grid[y] = Object.fromEntries(types.map(t => [t, 0])));

  outputs.forEach(o => {
    const y = safeYear(o.year ?? o.date ?? o.created_at);
    const t = outputType(o);
    if (!grid[y]) grid[y] = Object.fromEntries(types.map(tt => [tt, 0]));
    grid[y][t] += 1;
  });

  const datasets = types.map((t,i) => ({
    label: t,
    data: sortedYears.map(y => grid[y][t]),
    backgroundColor: `hsl(${(i*53)%360} 70% 60%)`,
    stack:'out'
  }));

  // ปลั๊กอินแสดงตัวเลขบนแท่ง
  const dataLabelPlugin = {
    id: 'dataLabelPlugin',
    afterDatasetsDraw(chart) {
      const { ctx } = chart;
      chart.data.datasets.forEach((dataset, datasetIndex) => {
        const meta = chart.getDatasetMeta(datasetIndex);
        meta.data.forEach((bar, index) => {
          const value = dataset.data[index];
          if (value > 0) {
            ctx.fillStyle = '#111';
            ctx.font = 'bold 11px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';
            ctx.fillText(value, bar.x, bar.y - 3);
          }
        });
      });
    }
  };

  const el = document.getElementById('chartOutputStacked');
  const prev = Chart.getChart(el); if (prev) prev.destroy();

  new Chart(el,{
    type:'bar',
    data:{ labels: sortedYears, datasets },
    options:{
      responsive:true,
      plugins:{ legend:{position:'bottom'} },
      scales:{ 
        x:{ stacked:true, title:{display:true, text:'ปี'} }, 
        y:{ stacked:true, beginAtZero:true, title:{display:true, text:'จำนวนผลงาน'} } 
      }
    },
    plugins: [dataLabelPlugin]
  });
})();


/* ---------- 5) Publication per Year ---------- */
(function(){
  const pubs = outputs.filter(o => (o.type||'').toLowerCase().includes('pub'));
  const byYear = {};
  pubs.forEach(o => {
    const y = outputYear(o);
    byYear[y] = (byYear[y]||0)+1;
  });
  const years = Object.keys(byYear).sort();
  new Chart(document.getElementById('chartPublicationPerYear'), {
    type:'line',
    data:{ labels: years, datasets:[{ label:'Publication', data: years.map(y=>byYear[y]), fill:false }]},
    options:{ responsive:true, plugins:{ legend:{position:'bottom'} } }
  });
})();

/* ---------- 6) สถานะโครงการ (Stacked) ---------- */
(function() {
  const ctx = document.getElementById('chartStatusYear').getContext('2d');
  const statuses = [
    'Project Planing (เริ่มต้น - submit IRB)',
    'เก็บข้อมูล',
    'วิเคราะห์ข้อมูล',
    'เขียนรายงานและpublication',
    'ปิดโปรเจค',
    'ยกเลิกโปรเจค'
  ];

  const statusData = {}; // year → status → count
  projects.forEach(p => {
    const year = getYear(p);
    const status = p.planning_status || 'ไม่ระบุ';
    if (!statusData[year]) {
      statusData[year] = Object.fromEntries(statuses.map(s => [s, 0]));
    }
    if (statuses.includes(status)) statusData[year][status]++;
  });

  const years = Object.keys(statusData).sort();
  const datasets = statuses.map((s,i)=>({
    label: s,
    data: years.map(y => statusData[y][s]),
    backgroundColor: `hsl(${(i*46)%360} 70% 60%)`,
    stack: 'st'
  }));

  // ปลั๊กอินแสดงตัวเลขบนแท่ง
  const dataLabelPlugin = {
    id: 'dataLabelPlugin',
    afterDatasetsDraw(chart) {
      const { ctx } = chart;
      ctx.save();
      chart.data.datasets.forEach((dataset, datasetIndex) => {
        const meta = chart.getDatasetMeta(datasetIndex);
        meta.data.forEach((bar, index) => {
          const value = dataset.data[index];
          if (value > 0) {
            ctx.fillStyle = '#111';
            ctx.font = 'bold 11px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';
            ctx.fillText(value, bar.x, bar.y - 3);
          }
        });
      });
      ctx.restore();
    }
  };

  new Chart(ctx, {
    type: 'bar',
    data: { labels: years, datasets },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' }, tooltip: { mode: 'index', intersect: false } },
      scales: { 
        x: { stacked: true, title: { display: true, text: 'ปี' } }, 
        y: { stacked: true, beginAtZero: true, title: { display: true, text: 'จำนวนโครงการ' } } 
      }
    },
    plugins: [dataLabelPlugin] // ใส่เฉพาะกราฟนี้
  });
})();

/* ---------- 7) IRB Expiry (6 เดือนข้างหน้า) ---------- */
(function(){
  const now = new Date();
  const group = {};
  irbs.forEach(irb=>{
    const exp = new Date(irb.exp_date);
    const monthsAhead = (exp - now)/(1000*60*60*24*30);
    if (monthsAhead >= 0 && monthsAhead <= 6) {
      const key = `${exp.getMonth()+1}/${exp.getFullYear()}`;
      group[key] = (group[key]||0) + 1;
    }
  });
  const labels = Object.keys(group).sort((a,b)=> new Date(a)-new Date(b));
  new Chart(document.getElementById('chartIRBExpiry'),{
    type:'bar',
    data:{ labels, datasets:[{ label:'IRB ใกล้หมดอายุ', data: labels.map(l=>group[l]), backgroundColor:'#ef4444' }]},
    options:{ responsive:true, plugins:{ legend:{position:'bottom'} } }
  });
})();

/* ---------- 8) ความร่วมมือ ---------- */
(function(){
  const cats = ['ภายในคณะแพทยศาสตร์ศิริราชพยาบาล','ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล','ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ','ไม่ระบุ'];
  const byYearCat = {};
  const years = [...new Set(projects.map(getYear))].sort();
  years.forEach(y => byYearCat[y] = Object.fromEntries(cats.map(c=>[c,0])));
  projects.forEach(p=>{
    const y = getYear(p);
    const c = categorizeCollab(p);
    if (!byYearCat[y]) byYearCat[y] = Object.fromEntries(cats.map(c=>[c,0]));
    byYearCat[y][c] += 1;
  });
  const datasets = cats.map((c,i)=>({
    label:c, data: years.map(y=>byYearCat[y][c]),
    backgroundColor:`hsl(${(i*60)%360} 70% 60%)`, stack:'collab'
  }));
  new Chart(document.getElementById('chartCollabBreakdown'),{
    type:'bar',
    data:{ labels: years, datasets },
    options:{ responsive:true, plugins:{legend:{position:'bottom'}}, scales:{ x:{stacked:true}, y:{stacked:true, beginAtZero:true} } }
  });
})();

/* ---------- 9) จำนวน PI ต่อหน่วยงาน ---------- */
(function(){
  // นับจำนวน PI (unique) ต่อ unit
  const map = {};
  projects.forEach(p=>{
    const unit = getUnit(p);
    const pi = p.pi_name || 'ไม่ระบุ';
    map[unit] = map[unit] || new Set();
    map[unit].add(pi);
  });
  const units = Object.keys(map);
  const counts = units.map(u => map[u].size);
  new Chart(document.getElementById('chartPIsPerUnit'),{
    type:'bar',
    data:{ labels: units, datasets:[{ label:'PI ต่อหน่วย', data: counts }]},
    options:{ responsive:true, indexAxis: 'y', plugins:{legend:{display:false}} }
  });
})();

/* ---------- 10) ทุนภายนอกตามแหล่งทุน ---------- */
(function(){
  const map = {};
  projects.forEach(p=>{
    if (getFundingType(p) === 'ภายนอก') {
      map[getFundingSource(p)] = (map[getFundingSource(p)]||0) + 1;
    }
  });
  const labels = Object.keys(map);
  const data = labels.map(l => map[l]);
  new Chart(document.getElementById('chartExternalBySource'),{
    type:'doughnut',
    data:{ labels, datasets:[{ label:'จำนวนโครงการ', data }]},
    options:{ responsive:true, plugins:{ legend:{position:'bottom'} } }
  });
})();

/* ---------- 11) โครงการทั้งหมด vs โครงการใหม่ (ต่อปี) + เก่า/ใหม่ ---------- */
(function(){
  // กำหนดปีที่มีในระบบ
  const years = [...new Set(projects.map(getYear))].sort();

  // helper: โครงการ active ในปี y ถ้า start_year <= y <= end_year (หรือไม่มี end_year)
  function isActiveInYear(p, y){
    const s = toNumber(p.start_year);
    const e = toNumber(getEndYear(p)) || Infinity;
    return toNumber(y) >= s && toNumber(y) <= e;
  }

  const totalPerYear = {};
  const newPerYear = {};
  years.forEach(y=>{
    totalPerYear[y] = projects.filter(p => isActiveInYear(p,y)).length;
    newPerYear[y]   = projects.filter(p => getYear(p) == y).length;
  });

  new Chart(document.getElementById('chartYearlyNewVsTotal'),{
    type:'bar',
    data:{
      labels: years,
      datasets:[
        { label:'ทั้งหมด', data: years.map(y=>totalPerYear[y]), backgroundColor:'#93c5fd' },
        { label:'ใหม่',   data: years.map(y=>newPerYear[y]),   backgroundColor:'#34d399' }
      ]
    },
    options:{ responsive:true, plugins:{legend:{position:'bottom'}}, scales:{ y:{beginAtZero:true} } }
  });
})();

/* ---------- 12) FTE Bubble (ชื่อ → โครงการ/รวม FTE) ---------- */
(function(){
  const fteData = {};
  ftes.forEach(f=>{
    const name = f.name || 'ไม่ระบุ';
    if (!fteData[name]) fteData[name] = { fte:0, projectSet:new Set() };
    fteData[name].fte += toNumber(f.fte_value || f.fte || 0);
    fteData[name].projectSet.add(f.project_id);
  });
  const datasets = Object.entries(fteData).map(([name, data])=>({
    label:name,
    data:[{ x:data.projectSet.size, y:data.fte, r: Math.max(5, data.fte*10) }]
  }));
  new Chart(document.getElementById('chartFteSummary'),{
    type:'bubble',
    data:{ datasets },
    options:{
      responsive:true,
      plugins:{ tooltip:{ callbacks:{ label:(ctx)=> `${ctx.dataset.label}: โครงการ ${ctx.raw.x} / FTE ${ctx.raw.y.toFixed(2)}` }}, legend:{ display:false }},
      scales:{ x:{ title:{display:true, text:'จำนวนโครงการ'}}, y:{ beginAtZero:true, title:{display:true, text:'FTE รวม'}} }
    }
  });
})();

/* ---------- Drill-down ตัวอย่าง (คลิกบางกราฟ) ---------- */
// ตัวอย่าง: คลิก Publication รายปี -> รายการผลงาน
document.getElementById('chartPublicationPerYear').onclick = function(evt){
  const chart = Chart.getChart('chartPublicationPerYear');
  const point = chart.getElementsAtEventForMode(evt, 'nearest', {intersect:true}, true)[0];
  if (!point) return;
  const y = chart.data.labels[point.index];
  const rows = outputs.filter(o => (o.type||'').toLowerCase().includes('pub') && (''+outputYear(o)) === (''+y));
  let html = `<table class="table-auto w-full border text-sm"><thead><tr><th class="p-2">ชื่อผลงาน</th><th class="p-2">ประเภท</th><th class="p-2">โครงการ</th></tr></thead><tbody>`;
  rows.forEach(o=>{
    html += `<tr class="border-t"><td class="p-2">${o.title||'-'}</td><td class="p-2">${o.type||'-'}</td><td class="p-2 text-blue-600"><a href="/projects/${o.project_id}" target="_blank">${o.project_name||'-'}</a></td></tr>`;
  });
  html += `</tbody></table>`;
  showModal(`📚 Publication ปี ${y}`, html);
};

/* ---------- Export CSV (ตามตาราง) ---------- */
document.getElementById('exportCSV').addEventListener('click', () => {
  const csv = [["ชื่อโครงการ","PI","ปี","Co-PI","CoResearch","FTE","Output","IRB ล่าสุด"]];
  projects.forEach(p => {
    const latestIRB = (p.irbs?.length ?? 0) > 0
      ? new Date(p.irbs.sort((a,b)=> new Date(b.exp_date)-new Date(a.exp_date))[0].exp_date).toLocaleDateString("th-TH")
      : "ไม่มี";
    const fteSum = (p.fte_roles ?? p.fteRoles ?? []).reduce((sum,r)=> sum + toNumber(r.fte_value||r.fte||0), 0).toFixed(2);
    csv.push([ p.project_name_th, p.pi_name, p.start_year, p.copis?.length ?? 0, p.co_researches?.length ?? p.coResearches?.length ?? 0, fteSum, p.outputs?.length ?? 0, latestIRB ]);
  });
  const content = "\uFEFF" + csv.map(r => r.join(",")).join("\n");
  const blob = new Blob([content], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const link = Object.assign(document.createElement("a"), { href:url, download:"dashboard_export.csv" });
  document.body.appendChild(link); link.click(); document.body.removeChild(link);
});
</script>
@endsection

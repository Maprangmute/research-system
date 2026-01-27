@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto px-4 py-6 space-y-4">
    <h1 class="text-2xl font-bold text-gray-900">🤖 AI Dashboard</h1>

    {{-- Prompt box --}}
    <div class="bg-white p-4 rounded-xl shadow border space-y-3">
        <textarea id="prompt"
            class="w-full border rounded-lg p-3"
            rows="3"
            placeholder="พิมพ์สิ่งที่ต้องการ เช่น: สรุปจำนวนโครงการรายปี, งบรวมแยกตาม funding_type, FTE รวมต่อคน ปี 2568"></textarea>

        <div class="flex gap-2">
            <button id="btnAsk"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                ถาม AI
            </button>

            <button id="btnExport"
                disabled
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg disabled:opacity-40">
                Download Excel
            </button>

            <span id="status" class="text-sm text-gray-500"></span>
        </div>
    </div>

    {{-- Result --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Chart --}}
        <div class="bg-white p-4 rounded-xl shadow border">
            <h2 class="font-semibold mb-2">📊 กราฟ</h2>
            <canvas id="chart"></canvas>
            <div id="sqlHint" class="text-xs text-gray-400 mt-2"></div>
        </div>

        {{-- Table --}}
        <div class="bg-white p-4 rounded-xl shadow border overflow-auto">
            <h2 class="font-semibold mb-2">📋 ตารางข้อมูล</h2>
            <table id="table" class="min-w-full text-sm"></table>
        </div>
    </div>
</div>

<script>
let chartInstance = null;
let lastSql = null;

const statusEl = document.getElementById('status');

document.getElementById('btnAsk').onclick = async () => {
    const prompt = document.getElementById('prompt').value.trim();
    if (!prompt) return;

    statusEl.textContent = 'กำลังประมวลผล...';
    document.getElementById('btnExport').disabled = true;

    const res = await fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prompt })
    });

    const data = await res.json();
    statusEl.textContent = '';

    if (!data.ok) {
        statusEl.textContent = 'เกิดข้อผิดพลาด';
        console.error(data);
        return;
    }

    lastSql = data.sql;
    renderTable(data.columns, data.rows);
    renderChart(data.chart, data.rows);

    document.getElementById('sqlHint').textContent = data.sql;
    document.getElementById('btnExport').disabled = false;
};

document.getElementById('btnExport').onclick = async () => {
    const res = await fetch('/rm/ai/export', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ sql: lastSql })
    });

    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'ai_export.csv';
    a.click();
    URL.revokeObjectURL(url);
};

function renderTable(columns, rows) {
    const table = document.getElementById('table');
    if (!columns || columns.length === 0) {
        table.innerHTML = '<tr><td>ไม่มีข้อมูล</td></tr>';
        return;
    }

    const thead = `<thead><tr>` +
        columns.map(c => `<th class="border-b p-2 text-left">${c}</th>`).join('') +
        `</tr></thead>`;

    const tbody = `<tbody>` +
        rows.map(r => `<tr>` +
            columns.map(c => `<td class="border-b p-2">${r[c] ?? ''}</td>`).join('') +
        `</tr>`).join('') +
        `</tbody>`;

    table.innerHTML = thead + tbody;
}

function renderChart(spec, rows) {
    if (chartInstance) chartInstance.destroy();

    if (!spec || !rows.length) return;

    const labels = rows.map(r => r[spec.labelColumn]);
    const datasets = spec.valueColumns.map(v => ({
        label: v.label,
        data: rows.map(r => Number(r[v.key]))
    }));

    chartInstance = new Chart(document.getElementById('chart'), {
        type: spec.type,
        data: { labels, datasets },
        options: { responsive: true }
    });
}
</script>
@endsection

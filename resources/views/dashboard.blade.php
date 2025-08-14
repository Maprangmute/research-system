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
                    <input type="text" id="filterName" class="w-full border rounded px-2 py-1 text-sm"
                        placeholder="ชื่อโครงการ">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-700 block">ค้นหา PI</label>
                    <input type="text" id="filterPI" class="w-full border rounded px-2 py-1 text-sm"
                        placeholder="ชื่อ PI">
                </div>
                <div class="flex items-end">
                    <button id="exportCSV"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 text-sm rounded shadow">
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
            <div class="bg-white shadow-sm border rounded-xl p-4 ">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📅 โครงการทั้งหมด โครงการใหม่ เเละ
                    โครงการกำลังดำเนินการ (ต่อปี)</h3>
                <canvas id="chartYearlyNewVsTotal" height="250"></canvas>
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
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🏦 ทุนภายนอก แยกตามแหล่งทุน</h3>
                <canvas id="chartExternalBySource" height="250"></canvas>
            </div>

            <div class="bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🏦 ทุนภายใน แยกตามแหล่งทุน</h3>
                <canvas id="chartInternalBySource" height="250"></canvas>
            </div>

            <div class="bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📝 Output (รายปี)</h3>
                <canvas id="chartOutputStacked" height="250"></canvas>
            </div>

            <div class="bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📚 Publication รายปี</h3>
                <canvas id="chartPublicationPerYear" height="250"></canvas>
            </div>

            <div class="bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🧩 สถานะของโครงการ รายปี</h3>
                <canvas id="chartStatusYear" height="250"></canvas>
            </div>

            <div class="bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🤝 จำนวนความร่วมมือ (ภายนอก/ในคณะ และ
                    หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ)</h3>
                <canvas id="chartCollabBreakdown" height="250"></canvas>
            </div>

            <div class="bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">👨‍🔬 จำนวนงานของ PI ในหน่วยงาน</h3>
                <canvas id="chartPIsPerUnit" height="250"></canvas>
            </div>

            <div class="bg-white shadow-sm border rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📈 การกระจาย FTE</h3>
                <canvas id="chartFteSummary" height="250"></canvas>
            </div>
        </div>

        <!-- 4) Co-Research (จาก COPIs) Summary -->
        <div class="bg-white shadow-sm border rounded-xl p-4 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">🤝 รายชื่อผู้ร่วมวิจัย</h3>

            @php
                // รวมตาม "ชื่อ|หน่วยงาน" เพื่อกันกรณีชื่อซ้ำคนละสังกัด
                $grouped = $copis->groupBy(function ($c) {
                    $name = trim($c->name ?? '');
                    $aff = trim($c->affiliation ?? ($c->unit ?? ($c->department ?? ''))); // fallback
                    return $name . '|' . $aff;
                });
            @endphp

            <table class="w-full text-sm border">
                <thead class="bg-gray-50 text-xs text-gray-700">
                    <tr>
                        <th class="p-2 text-left">ชื่อ</th>
                        <th class="p-2 text-left">หน่วยงาน</th>
                        <th class="p-2 text-center">จำนวนโครงการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grouped as $key => $rows)
                        @php
                            [$name, $aff] = explode('|', $key);
                            $projectCount = $rows->pluck('project_id')->filter()->unique()->count();
                        @endphp
                        <tr class="border-t">
                            <td class="p-2">{{ $name ?: 'ไม่ระบุ' }}</td>
                            <td class="p-2">{{ $aff ?: 'ไม่ระบุ' }}</td>
                            <td class="p-2 text-center">{{ $projectCount }}</td>
                        </tr>
                    @empty
                        <tr class="border-t">
                            <td colspan="3" class="p-3 text-center text-gray-500">ไม่มีข้อมูลผู้ร่วมวิจัย</td>
                        </tr>
                    @endforelse
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
                                <td class="p-2 text-blue-700 hover:underline"><a
                                        href="/projects/{{ $p->id }}">{{ $p->project_name_th }}</a></td>
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
        <div
            class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-xl w-[92vw] max-w-5xl max-h-[80vh] overflow-auto">
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
        const projects = @json($projects);
        const outputs = @json($outputs);
        const ftes = @json($fteRoles);
        const irbs = @json($irbs);
        const progressReports = @json($progressReports);

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


        function categorizeCollab(p) {
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

        /* ---------- ฟังก์ชันย่อเงินแบบไทย (พัน/หมื่น/แสน/ล้าน) ---------- */
        function formatThaiMoney(num) {
            if (num >= 1000000) return (num / 1000000).toFixed(2).replace(/\.00$/, '') + ' ล้านบ.';
            if (num >= 100000) return (num / 100000).toFixed(2).replace(/\.00$/, '') + ' แสนบ.';
            if (num >= 10000) return (num / 10000).toFixed(2).replace(/\.00$/, '') + ' หมื่นบ.';
            if (num >= 1000) return (num / 1000).toFixed(2).replace(/\.00$/, '') + ' พันบ.';
            return num.toLocaleString('th-TH') + ' บ.';
        }


        function showModal(title, htmlBody) {
            document.getElementById('chartModalTitle').innerText = title;
            document.getElementById('chartModalBody').innerHTML = htmlBody;
            document.getElementById('chartModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('chartModal').classList.add('hidden');
        }

        /* ---------- 1) จำนวนโครงการรายปี (Bar + ตัวเลขบนแท่ง | กันเลขตกขอบ) ---------- */
        (function() {
            // รวมจำนวนโครงการต่อปี
            const counts = new Map();
            projects.forEach(p => {
                const y = (getYear(p) ?? '').toString().trim() || 'ไม่ระบุ';
                counts.set(y, (counts.get(y) || 0) + 1);
            });

            // ปีตัวเลขก่อน แล้วค่อยหมวดอื่น
            const isNumeric = (s) => /^\d+$/.test(s);
            const numericYears = [...counts.keys()].filter(isNumeric).map(Number).sort((a, b) => a - b).map(String);
            const otherYears = [...counts.keys()].filter(k => !isNumeric(k)).sort();
            const labels = numericYears.concat(otherYears);

            const data = labels.map(y => counts.get(y) ?? 0);
            const colors = labels.map((_, i) => `hsl(${(i * 37) % 360} 70% 60%)`);

            // ทำลายกราฟเดิมถ้ามี
            const el = document.getElementById('chartProjectsPerYear');
            const prev = Chart.getChart(el);
            if (prev) prev.destroy();

            // ปลั๊กอินวาดตัวเลขบนแท่ง + กันตกขอบบน
            const barValuePlugin = {
                id: 'barValuePlugin',
                afterDatasetsDraw(chart) {
                    const {
                        ctx,
                        chartArea: {
                            top,
                            right
                        },
                        scales: {
                            x,
                            y
                        }
                    } = chart;
                    ctx.save();
                    ctx.font = 'bold 11px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillStyle = '#374151';

                    const meta = chart.getDatasetMeta(0);
                    const ds = chart.data.datasets[0];

                    meta.data.forEach((bar, i) => {
                        const v = ds.data[i];
                        if (v == null || !isFinite(v) || v <= 0) return;

                        let drawX = bar.x;
                        let drawY = bar.y - 6; // วางเหนือแท่ง

                        // กันตกขอบบน
                        const minY = top + 8;
                        if (drawY < minY) {
                            // ถ้าแนบขอบจริง ๆ ให้เลื่อนลงมาให้อยู่ในกราฟ
                            drawY = minY;
                        }
                        // กันชิดขอบขวา
                        if (drawX > right - 2) drawX = right - 2;

                        ctx.fillText(String(v), drawX, drawY);
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
                    layout: {
                        padding: {
                            top: 14
                        }
                    }, // เผื่อพื้นที่ด้านบนเพิ่ม
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ` ${ctx.raw} โครงการ`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grace: '12%', // headroom กันเลขชนขอบ
                            ticks: {
                                precision: 0
                            },
                            title: {
                                display: true,
                                text: 'จำนวนโครงการ'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'ปี'
                            }
                        }
                    }
                },
                plugins: [barValuePlugin]
            });
        })();

        // 2) สัดส่วนทุน ภายใน/ภายนอก (รายปี) + Data Label + กันเลขตกขอบ + เช็ค toggle
        (function() {
            const years = [...new Set(projects.map(getYear))].sort();
            const types = ['ภายใน', 'ภายนอก'];

            const matrix = {};
            years.forEach(y => (matrix[y] = {
                'ภายใน': 0,
                'ภายนอก': 0
            }));
            projects.forEach(p => {
                const y = getYear(p);
                const t = getFundingType(p);
                if (matrix[y] && (t === 'ภายใน' || t === 'ภายนอก')) matrix[y][t] += 1;
            });

            const datasets = types.map(t => ({
                label: t,
                data: years.map(y => matrix[y][t]),
                backgroundColor: t === 'ภายใน' ? '#60a5fa' : '#34d399',
                stack: 'fund'
            }));

            // ปลั๊กอินโชว์ตัวเลขบนแท่ง + เช็คซ่อน/แสดง + กันตกขอบบน/ขวา
            const barValuePlugin = {
                id: 'barValueFunding',
                afterDatasetsDraw(chart) {
                    const {
                        ctx,
                        chartArea: {
                            top,
                            right
                        }
                    } = chart;
                    ctx.save();
                    ctx.font = 'bold 11px sans-serif';
                    ctx.fillStyle = '#111827';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';

                    chart.data.datasets.forEach((dataset, dsIndex) => {
                        // ถ้าชุดถูกซ่อนจาก legend ให้ข้าม
                        if (!chart.isDatasetVisible(dsIndex)) return;

                        const meta = chart.getDatasetMeta(dsIndex);
                        meta.data.forEach((bar, index) => {
                            if (!bar || bar.hidden) return;

                            const value = dataset.data[index];
                            if (!isFinite(value) || value <= 0) return;

                            let drawX = bar.x;
                            let drawY = bar.y - 4; // วางเหนือแท่งเล็กน้อย

                            // clamp กันตกขอบบน
                            const minY = top + 8;
                            if (drawY < minY) drawY = minY;

                            // กันชิดขอบขวา
                            if (drawX > right - 2) drawX = right - 2;

                            ctx.fillText(String(value), drawX, drawY);
                        });
                    });

                    ctx.restore();
                }
            };

            const el = document.getElementById('chartFundingSplit');
            const prev = Chart.getChart(el);
            if (prev) prev.destroy();

            new Chart(el, {
                type: 'bar',
                data: {
                    labels: years,
                    datasets
                },
                options: {
                    responsive: true,
                    layout: {
                        padding: {
                            top: 14
                        }
                    }, // เผื่อพื้นที่ด้านบนให้ label
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'ปี'
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grace: '12%', // headroom กันเลขชนขอบ
                            ticks: {
                                precision: 0
                            },
                            title: {
                                display: true,
                                text: 'จำนวนโครงการ'
                            }
                        }
                    }
                },
                plugins: [barValuePlugin]
            });
        })();

        /* ---------- 3) งบประมาณรวมรายปี (Bar + Data Label หน่วย พัน/หมื่น/แสน/ล้าน | กันเลขตกขอบ) ---------- */
        (function() {
            // รวมงบประมาณต่อปี (บังคับเลขเสมอ)
            const sumByYear = new Map();
            projects.forEach(p => {
                const y = (getYear(p) ?? '').toString().trim() || 'ไม่ระบุ';
                // บังคับเป็น number (กันงบประมาณเป็นสตริง/ค่าว่าง)
                const v = Number(getBudget ? getBudget(p) : (p.total_budget ?? p.budget ?? 0)) || 0;
                sumByYear.set(y, (sumByYear.get(y) || 0) + v);
            });

            // เรียงปี: ตัวเลขก่อน คำทีหลัง
            const isNum = s => /^\d+$/.test(s);
            const allYears = [...sumByYear.keys()];
            const years = allYears
                .filter(isNum).map(Number).sort((a, b) => a - b).map(String)
                .concat(allYears.filter(y => !isNum(y)).sort());

            const data = years.map(y => sumByYear.get(y) || 0);

            // ฟอร์แมตหน่วยเงินไทย (พัน/หมื่น/แสน/ล้าน) + ตัด .00
            function formatMoneyTHB(v) {
                const n = Number(v) || 0;
                const trim = (x) => Number(x).toFixed(2).replace(/\.00$/, '');
                if (n >= 1_000_000) return `${trim(n / 1_000_000)} ล้านบาท`;
                if (n >= 100_000) return `${trim(n /   100_000)} แสนบาท`;
                if (n >= 10_000) return `${trim(n /    10_000)} หมื่นบาท`;
                if (n >= 1_000) return `${trim(n /     1_000)} พันบาท`;
                return `${n.toLocaleString('th-TH')} บาท`;
            }

            // ปลั๊กอินวาดเลขบนแท่ง + เช็คซ่อน + กันตกขอบบน/ขวา
            const budgetValueLabelPlugin = {
                id: 'budgetValueLabel',
                afterDatasetsDraw(chart) {
                    const {
                        ctx,
                        chartArea: {
                            top,
                            right
                        }
                    } = chart;
                    // ถ้าชุดข้อมูลถูกซ่อนจาก legend → ไม่วาด
                    if (!chart.isDatasetVisible(0)) return;

                    const meta = chart.getDatasetMeta(0);
                    const ds = chart.data.datasets[0];
                    if (!meta || !ds) return;

                    ctx.save();
                    ctx.font = 'bold 11px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillStyle = '#111827';

                    meta.data.forEach((bar, i) => {
                        if (!bar || bar.hidden) return;
                        const v = Number(ds.data[i] ?? 0);
                        if (!(isFinite(v) && v > 0)) return;

                        const label = formatMoneyTHB(v);
                        let drawX = bar.x;
                        let drawY = bar.y - 6;

                        // clamp กันตกขอบบน/ขวา
                        const minY = top + 8;
                        if (drawY < minY) drawY = minY;
                        if (drawX > right - 2) drawX = right - 2;

                        ctx.fillText(label, drawX, drawY);
                    });

                    ctx.restore();
                }
            };

            const el = document.getElementById('chartBudgetPerYear');
            if (!el) return;
            const prev = Chart.getChart(el);
            if (prev) prev.destroy();

            new Chart(el, {
                type: 'bar',
                data: {
                    labels: years,
                    datasets: [{
                        label: 'งบประมาณ (รวม)',
                        data,
                        backgroundColor: '#fbbf24',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    layout: {
                        padding: {
                            top: 20
                        }
                    }, // เผื่อพื้นที่ label ด้านบน
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => formatMoneyTHB(ctx.raw)
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'ปี'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grace: '12%', // headroom กันเลขชนขอบ
                            ticks: {
                                // กัน error ถ้าค่า tick แปลก
                                callback: (value) => {
                                    try {
                                        return formatMoneyTHB(value);
                                    } catch {
                                        return value;
                                    }
                                }
                            },
                            title: {
                                display: true,
                                text: 'งบประมาณ'
                            }
                        }
                    }
                },
                plugins: [budgetValueLabelPlugin]
            });
        })();



        /* ---------- 4) Output Stacked by Type / Year (กันเลขตกขอบ + เช็ค toggle legend) ---------- */
        (function() {
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
            const sortedYears = years.filter(isNum).map(Number).sort((a, b) => a - b).map(String)
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

            const datasets = types.map((t, i) => ({
                label: t,
                data: sortedYears.map(y => grid[y][t]),
                backgroundColor: `hsl(${(i*53)%360} 70% 60%)`,
                stack: 'out'
            }));

            // ปลั๊กอินแสดงตัวเลขบนแท่ง + กันตกขอบบน/ขวา + เช็คซ่อนชุดข้อมูล
            const dataLabelPlugin = {
                id: 'dataLabelPluginOutputs',
                afterDatasetsDraw(chart) {
                    const {
                        ctx,
                        chartArea: {
                            top,
                            right
                        }
                    } = chart;
                    ctx.save();
                    ctx.font = 'bold 11px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillStyle = '#111';

                    chart.data.datasets.forEach((ds, dsIndex) => {
                        // ถ้าชุดนี้ถูกซ่อนจาก legend -> ข้าม
                        if (!chart.isDatasetVisible(dsIndex)) return;

                        const meta = chart.getDatasetMeta(dsIndex);
                        meta.data.forEach((bar, i) => {
                            if (!bar || bar.hidden) return;

                            const v = Number(ds.data[i] || 0);
                            if (!isFinite(v) || v <= 0) return;

                            let drawX = bar.x;
                            let drawY = bar.y - 3; // วางเหนือแท่งเล็กน้อย

                            // clamp กันตกขอบบน
                            const minY = top + 8;
                            if (drawY < minY) drawY = minY;

                            // กันชิดขอบขวา (เผื่อข้อความยาว)
                            if (drawX > right - 2) drawX = right - 2;

                            ctx.fillText(String(v), drawX, drawY);
                        });
                    });

                    ctx.restore();
                }
            };

            const el = document.getElementById('chartOutputStacked');
            const prev = Chart.getChart(el);
            if (prev) prev.destroy();

            new Chart(el, {
                type: 'bar',
                data: {
                    labels: sortedYears,
                    datasets
                },
                options: {
                    responsive: true,
                    layout: {
                        padding: {
                            top: 16
                        }
                    }, // เผื่อพื้นที่ด้านบน
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'ปี'
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grace: '12%', // headroom กันเลขชนขอบ
                            ticks: {
                                precision: 0
                            },
                            title: {
                                display: true,
                                text: 'จำนวนผลงาน'
                            }
                        }
                    }
                },
                plugins: [dataLabelPlugin]
            });
        })();



        /* ---------- 5) Publication per Year ---------- */
        (function() {
            const pubs = outputs.filter(o => (o.type || '').toLowerCase().includes('pub'));
            const byYear = {};
            pubs.forEach(o => {
                const y = outputYear(o);
                byYear[y] = (byYear[y] || 0) + 1;
            });
            const years = Object.keys(byYear).sort();
            new Chart(document.getElementById('chartPublicationPerYear'), {
                type: 'line',
                data: {
                    labels: years,
                    datasets: [{
                        label: 'Publication',
                        data: years.map(y => byYear[y]),
                        fill: false
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
        })();

        /* ---------- 6) สถานะโครงการ (Stacked) + กันเลขตกขอบ ---------- */
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
                if (!statusData[year]) statusData[year] = Object.fromEntries(statuses.map(s => [s, 0]));
                if (statuses.includes(status)) statusData[year][status]++;
            });

            // เรียงปีให้ถูก: ตัวเลขก่อน คำทีหลัง
            const isNum = s => /^\d+$/.test(String(s));
            const years = Object.keys(statusData);
            const sortedYears = years.filter(isNum).map(Number).sort((a, b) => a - b).map(String)
                .concat(years.filter(y => !isNum(y)).sort());

            const datasets = statuses.map((s, i) => ({
                label: s,
                data: sortedYears.map(y => statusData[y]?.[s] ?? 0),
                backgroundColor: `hsl(${(i*46)%360} 70% 60%)`,
                stack: 'st'
            }));

            // ปลั๊กอินแสดงตัวเลขบนแท่ง + กันตกขอบบน/ขวา
            const dataLabelPlugin = {
                id: 'dataLabelPluginStatus',
                afterDatasetsDraw(chart) {
                    const {
                        ctx,
                        chartArea: {
                            top,
                            right
                        }
                    } = chart;
                    ctx.save();
                    ctx.font = 'bold 11px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillStyle = '#111';

                    chart.data.datasets.forEach((dataset, dsIndex) => {
                        const meta = chart.getDatasetMeta(dsIndex);
                        meta.data.forEach((bar, index) => {
                            const value = Number(dataset.data[index] || 0);
                            if (!isFinite(value) || value <= 0) return;

                            let drawX = bar.x;
                            let drawY = bar.y - 3; // วางเหนือแท่ง

                            // กันตกขอบบน
                            const minY = top + 8;
                            if (drawY < minY) drawY = minY;

                            // กันชิดขอบขวา
                            if (drawX > right - 2) drawX = right - 2;

                            ctx.fillText(String(value), drawX, drawY);
                        });
                    });

                    ctx.restore();
                }
            };

            const prev = Chart.getChart(ctx.canvas);
            if (prev) prev.destroy();

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: sortedYears,
                    datasets
                },
                options: {
                    responsive: true,
                    layout: {
                        padding: {
                            top: 16
                        }
                    }, // เผื่อพื้นที่ด้านบนสำหรับ label
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'ปี'
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grace: '12%', // headroom กันเลขชนขอบ
                            ticks: {
                                precision: 0
                            },
                            title: {
                                display: true,
                                text: 'จำนวนโครงการ'
                            }
                        }
                    }
                },
                plugins: [dataLabelPlugin] // ใส่เฉพาะกราฟนี้
            });
        })();


        /* ---------- 7) IRB Expiry (6 เดือนข้างหน้า) ---------- */
        (function() {
            const now = new Date();
            const group = {};
            irbs.forEach(irb => {
                const exp = new Date(irb.exp_date);
                const monthsAhead = (exp - now) / (1000 * 60 * 60 * 24 * 30);
                if (monthsAhead >= 0 && monthsAhead <= 6) {
                    const key = `${exp.getMonth()+1}/${exp.getFullYear()}`;
                    group[key] = (group[key] || 0) + 1;
                }
            });
            const labels = Object.keys(group).sort((a, b) => new Date(a) - new Date(b));
            new Chart(document.getElementById('chartIRBExpiry'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'IRB ใกล้หมดอายุ',
                        data: labels.map(l => group[l]),
                        backgroundColor: '#ef4444'
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
        })();


        
        // 8) การร่วมมือ (Collaboration) แบ่งตามหน่วยงาน — robust + data labels + เช็ค toggle legend
(function() {
  const CATS = [
    'ภายในคณะแพทยศาสตร์ศิริราชพยาบาล',
    'ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล',
    'ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ'
  ];

  // normalize unit_status ให้เข้าหมวดมาตรฐาน
  function normalizeUnitStatus(raw) {
    const s = (raw ?? '').toString().trim().replace(/\s+/g, '');
    if (!s) return null;
    if (s.includes('ภายในคณะแพทยศาสตร์ศิริราชพยาบาล')) return CATS[0];
    if (s.includes('ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล')) return CATS[1];
    // รองรับทั้ง "นโยบายสุขภาพ" และ "นโยบาลสุขภาพ"
    if (s.includes('ภายในหน่วยวิจัย') && (s.includes('นโยบายสุขภาพ') || s.includes('นโยบาลสุขภาพ'))) {
      return CATS[2];
    }
    return null;
  }

  // ปี: ตัวเลขก่อน คำทีหลัง
  const rawYears = [...new Set(projects.map(getYear))].map(y => (y ?? '').toString().trim() || 'ไม่ระบุ');
  const isNum = v => /^\d+$/.test(v);
  const years = rawYears.filter(isNum).map(Number).sort((a,b)=>a-b).map(String)
                 .concat(rawYears.filter(y => !isNum(y)).sort());

  const byYearCat = {};
  years.forEach(y => byYearCat[y] = Object.fromEntries(CATS.map(c => [c, 0])));

  // นับแบบ "โปรเจกต์×หมวด×ปี" (unique ต่อหมวด)
  projects.forEach(p => {
    const y = (getYear(p) ?? '').toString().trim() || 'ไม่ระบุ';
    const list = p.co_researches || p.coResearches || [];
    if (!byYearCat[y]) byYearCat[y] = Object.fromEntries(CATS.map(c => [c, 0]));
    if (!Array.isArray(list) || list.length === 0) return;

    const catsInProject = new Set();
    list.forEach(cr => {
      const cat = normalizeUnitStatus(cr?.unit_status);
      if (cat) catsInProject.add(cat);
    });

    catsInProject.forEach(cat => byYearCat[y][cat] += 1);
  });

  const datasets = CATS.map((c, i) => ({
    label: c,
    data: years.map(y => byYearCat[y]?.[c] ?? 0),
    backgroundColor: `hsl(${(i*60)%360} 70% 60%)`,
    stack: 'collab'
  }));

  // ปลั๊กอิน data label + กันตกขอบ + เช็คซ่อนชุดข้อมูล
  const dataLabelPlugin = {
    id: 'dataLabelPluginCollab',
    afterDatasetsDraw(chart) {
      const { ctx, chartArea:{ top, right } } = chart;
      ctx.save();
      ctx.font = 'bold 11px sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'bottom';
      ctx.fillStyle = '#111827';

      chart.data.datasets.forEach((ds, dsIndex) => {
        // ถ้าชุดนี้ถูกซ่อนจาก legend -> ข้าม
        if (!chart.isDatasetVisible(dsIndex)) return;

        const meta = chart.getDatasetMeta(dsIndex);
        meta.data.forEach((bar, i) => {
          if (!bar || bar.hidden) return;

          const v = Number(ds.data[i] || 0);
          if (!isFinite(v) || v <= 0) return;

          let x = bar.x;
          let y = bar.y - 3;           // วางเหนือแท่งเล็กน้อย

          // clamp กันตกขอบบน/ขวา
          const minY = top + 8;
          if (y < minY) y = minY;
          if (x > right - 2) x = right - 2;

          ctx.fillText(String(v), x, y);
        });
      });

      ctx.restore();
    }
  };

  const el = document.getElementById('chartCollabBreakdown');
  const prev = Chart.getChart(el); if (prev) prev.destroy();

  new Chart(el, {
    type: 'bar',
    data: { labels: years, datasets },
    options: {
      responsive: true,
      layout: { padding: { top: 16 } }, // เผื่อพื้นที่ด้านบน
      plugins: {
        legend: { position: 'bottom' },
        tooltip: {
          mode: 'index',
          intersect: false,
          callbacks: {
            footer: (items) => {
              try {
                const i = items[0].dataIndex;
                const sum = CATS.reduce((s, c) => s + (byYearCat[years[i]]?.[c] ?? 0), 0);
                return `รวมปีนี้: ${sum}`;
              } catch { return ''; }
            }
          }
        }
      },
      scales: {
        x: { stacked: true, title: { display: true, text: 'ปี' } },
        y: {
          stacked: true,
          beginAtZero: true,
          grace: '12%',               // headroom กันเลขชนขอบ
          ticks: { precision: 0 },
          title: { display: true, text: 'จำนวนโครงการ (นับโปรเจกต์ต่อหมวด)' }
        }
      }
    },
    plugins: [dataLabelPlugin]
  });
})();


        /* ---------- 9) จำนวน PI ต่อหน่วยงาน ---------- */
        (function() {
            // นับจำนวนโครงการต่อ PI (ตัดชื่อว่าง/ไม่ระบุทิ้ง)
            const countByPI = {};
            projects.forEach(p => {
                const name = (p.pi_name || '').toString().trim();
                if (!name || name === 'ไม่ระบุ') return;
                countByPI[name] = (countByPI[name] || 0) + 1;
            });

            // เรียงมาก→น้อย และเลือก Top N (ตั้ง Infinity ถ้าอยากเอาทั้งหมด)
            const TOP_N = 20;
            const sorted = Object.entries(countByPI).sort((a, b) => b[1] - a[1]);
            const top = sorted.slice(0, TOP_N);

            const labels = top.map(([pi]) => pi);
            const data = top.map(([, count]) => count);

            // 👇 เปลี่ยนให้ตรงกับ canvas ที่มีอยู่
            const el = document.getElementById('chartPIsPerUnit');
            if (!el) return;

            // ปรับความสูงกันชื่อทับกัน
            el.height = Math.max(250, labels.length * 28);

            // ปลั๊กอินโชว์ตัวเลขท้ายแท่ง (ไม่ใช้ปลั๊กอินภายนอก)
            const valueLabelRight = {
                id: 'valueLabelRight',
                afterDatasetsDraw(chart) {
                    const {
                        ctx,
                        chartArea: {
                            right
                        }
                    } = chart;
                    const meta = chart.getDatasetMeta(0);
                    const scaleX = chart.scales.x;
                    ctx.save();
                    ctx.font = 'bold 11px sans-serif';
                    ctx.fillStyle = '#111827';
                    ctx.textBaseline = 'middle';
                    ctx.textAlign = 'right';
                    chart.data.datasets[0].data.forEach((v, i) => {
                        if (!isFinite(v) || v <= 0) return;
                        const y = meta.data[i].y;
                        let x = scaleX.getPixelForValue(v) + 8; // วางขวาของแท่ง
                        if (x > right - 2) x = right - 2; // กันตกขอบ
                        ctx.fillText(String(v), x, y);
                    });
                    ctx.restore();
                }
            };

            const prev = Chart.getChart(el);
            if (prev) prev.destroy();

            new Chart(el, {
                type: 'bar',
                data: {
                    labels, // แกน Y = รายชื่อ PI
                    datasets: [{
                        label: 'จำนวนโครงการ',
                        data,
                        backgroundColor: labels.map((_, i) => `hsl(${(i*33)%360} 70% 60%)`),
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ` ${ctx.raw} โครงการ`
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            },
                            title: {
                                display: true,
                                text: 'จำนวนโครงการ'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'PI'
                            }
                        }
                    }
                },
                plugins: [valueLabelRight]
            });
        })();

        /* ---------- 10) ทุนภายนอกตามแหล่งทุน (จำนวน + ยอดเงินย่อ) ---------- */
        (function() {
            // ย่อเงินไทย: พัน/หมื่น/แสน/ล้าน บาท
            function formatThaiMoney(num) {
                if (num >= 1_000_000) return (num / 1_000_000).toFixed(2).replace(/\.00$/, '') + ' ล้านบ.';
                if (num >= 100_000) return (num / 100_000).toFixed(2).replace(/\.00$/, '') + ' แสนบ.';
                if (num >= 10_000) return (num / 10_000).toFixed(2).replace(/\.00$/, '') + ' หมื่นบ.';
                if (num >= 1_000) return (num / 1_000).toFixed(2).replace(/\.00$/, '') + ' พันบ.';
                return (Number(num) || 0).toLocaleString('th-TH') + ' บ.';
            }

            // สะสม "จำนวนโครงการ" และ "ยอดเงินรวม" ต่อแหล่งทุน (เฉพาะทุนภายนอก)
            const countBySrc = {};
            const budgetBySrc = {};
            projects.forEach(p => {
                if ((getFundingType(p) || '').trim() === 'ภายนอก') {
                    const src = (getFundingSource(p) || 'ไม่ระบุ').toString().trim();
                    const amt = Number(p.budget) || 0;
                    countBySrc[src] = (countBySrc[src] || 0) + 1;
                    budgetBySrc[src] = (budgetBySrc[src] || 0) + amt;
                }
            });

            const labels = Object.keys(countBySrc);
            const counts = labels.map(l => countBySrc[l]);
            const budgets = labels.map(l => budgetBySrc[l]);
            const totalBudget = budgets.reduce((s, v) => s + v, 0) || 1;

            // data label บนชิ้นโดนัท: "จำนวน (ยอดเงินย่อ)"
            const valueLabelCenter = {
                id: 'valueLabelCenterExt',
                afterDatasetsDraw(chart) {
                    const {
                        ctx
                    } = chart;
                    const meta = chart.getDatasetMeta(0);
                    ctx.save();
                    ctx.font = 'bold 11px sans-serif';
                    ctx.fillStyle = '#111827';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    meta.data.forEach((el, i) => {
                        const c = counts[i];
                        const m = budgets[i];
                        if (!c) return;
                        const {
                            x,
                            y
                        } = el.tooltipPosition();
                        ctx.fillText(`${c} (${formatThaiMoney(m)})`, x, y);
                    });
                    ctx.restore();
                }
            };

            const el = document.getElementById('chartExternalBySource');
            if (!el) return;
            const prev = Chart.getChart(el);
            if (prev) prev.destroy();

            new Chart(el, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        label: 'จำนวนโครงการ',
                        data: counts,
                        backgroundColor: labels.map((_, i) => `hsl(${(i*37)%360} 70% 60%)`)
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const i = ctx.dataIndex;
                                    const c = counts[i];
                                    const m = budgets[i];
                                    const pct = ((m / totalBudget) * 100).toFixed(1);
                                    return ` ${ctx.label}: ${c} โครงการ, ${formatThaiMoney(m)} (${pct}%)`;
                                }
                            }
                        }
                    }
                },
                plugins: [valueLabelCenter]
            });
        })();



        /* ---------- ทุนภายในตามแหล่งทุน (จำนวน + ยอดเงิน) ---------- */
        (function() {
            const mapCount = {};
            const mapBudget = {};

            projects.forEach(p => {
                if ((getFundingType(p) || '').trim() === 'ภายใน') {
                    const src = (getFundingSource(p) || 'ไม่ระบุ').toString().trim();
                    const amt = Number(p.budget) || 0;
                    mapCount[src] = (mapCount[src] || 0) + 1;
                    mapBudget[src] = (mapBudget[src] || 0) + amt;
                }
            });

            const labels = Object.keys(mapCount);
            const counts = labels.map(l => mapCount[l]);
            const budgets = labels.map(l => mapBudget[l]);
            const totalBudget = budgets.reduce((s, v) => s + v, 0) || 1;

            const dataLabelPlugin = {
                id: 'dataLabelPluginInternal',
                afterDatasetsDraw(chart) {
                    const {
                        ctx
                    } = chart;
                    ctx.save();
                    ctx.font = 'bold 11px sans-serif';
                    ctx.fillStyle = '#111827';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';

                    const meta = chart.getDatasetMeta(0);
                    meta.data.forEach((el, i) => {
                        const count = counts[i];
                        const money = budgets[i];
                        if (!count || !money) return;
                        const {
                            x,
                            y
                        } = el.tooltipPosition();
                        ctx.fillText(`${count} (${formatThaiMoney(money)})`, x, y);
                    });
                    ctx.restore();
                }
            };

            const el = document.getElementById('chartInternalBySource');
            if (!el) return;
            const prev = Chart.getChart(el);
            if (prev) prev.destroy();

            new Chart(el, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        label: 'โครงการ',
                        data: counts,
                        backgroundColor: labels.map((_, i) => `hsl(${(i*37)%360} 70% 60%)`)
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const idx = ctx.dataIndex;
                                    const c = counts[idx];
                                    const m = budgets[idx];
                                    const pct = ((m / totalBudget) * 100).toFixed(1);
                                    return ` ${ctx.label}: ${c} โครงการ, ${formatThaiMoney(m)} (${pct}%)`;
                                }
                            }
                        }
                    }
                },
                plugins: [dataLabelPlugin]
            });
        })();

        /* ---------- 11) โครงการทั้งหมด vs ใหม่ vs ค้างจากปีก่อน (ต่อปี) + Data Label + กันเลขตกขอบ ---------- */
        (function() {
            const uniqYears = [...new Set(projects.map(getYear))]
                .filter(y => y != null && String(y).trim() !== '');
            const years = uniqYears.map(Number).sort((a, b) => a - b).map(String);

            function isActiveInYear(p, y) {
                const s = toNumber(p.start_year);
                const e = toNumber(getEndYear(p)) || Infinity;
                const Y = toNumber(y);
                return s <= Y && Y <= e;
            }

            const totalPerYear = {};
            const newPerYear = {};
            const carryoverPerYear = {};

            years.forEach(y => {
                totalPerYear[y] = projects.filter(p => isActiveInYear(p, y)).length;
                newPerYear[y] = projects.filter(p => String(getYear(p)) === String(y)).length;
                carryoverPerYear[y] = projects.filter(p => {
                    const s = toNumber(p.start_year);
                    const e = toNumber(getEndYear(p)) || Infinity;
                    const Y = toNumber(y);
                    return s < Y && Y <= e; // เริ่มก่อนปีนี้และยัง active
                }).length;
            });

            // ปลั๊กอินโชว์เลข + เช็คการซ่อน + กันตกขอบบน/ขวา
            const valueLabelBar = {
                id: 'valueLabelBar',
                afterDatasetsDraw(chart) {
                    const {
                        ctx,
                        chartArea: {
                            top,
                            right
                        }
                    } = chart;
                    ctx.save();
                    ctx.font = 'bold 11px sans-serif';
                    ctx.fillStyle = '#111827';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';

                    chart.data.datasets.forEach((dataset, dsIndex) => {
                        // ถ้าชุดข้อมูลถูกซ่อนจาก legend ให้ข้าม
                        if (!chart.isDatasetVisible(dsIndex)) return;

                        const meta = chart.getDatasetMeta(dsIndex);
                        meta.data.forEach((bar, index) => {
                            if (!bar || bar.hidden) return;
                            const val = dataset.data[index];
                            if (!isFinite(val) || val <= 0) return;

                            let drawX = bar.x;
                            let drawY = bar.y - 2;

                            // clamp กันตกขอบบน
                            const minY = top + 8;
                            if (drawY < minY) drawY = minY;

                            // กันชิดขอบขวา (เผื่อข้อความ)
                            if (drawX > right - 2) drawX = right - 2;

                            ctx.fillText(val, drawX, drawY);
                        });
                    });

                    ctx.restore();
                }
            };

            const el = document.getElementById('chartYearlyNewVsTotal');
            if (!el) return;
            const prev = Chart.getChart(el);
            if (prev) prev.destroy();

            new Chart(el, {
                type: 'bar',
                data: {
                    labels: years,
                    datasets: [{
                            label: 'ทั้งหมด',
                            data: years.map(y => totalPerYear[y]),
                            backgroundColor: '#93c5fd'
                        },
                        {
                            label: 'ใหม่',
                            data: years.map(y => newPerYear[y]),
                            backgroundColor: '#34d399'
                        },
                        {
                            label: 'ค้างจากปีก่อน',
                            data: years.map(y => carryoverPerYear[y]),
                            backgroundColor: '#fbbf24'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    layout: {
                        padding: {
                            top: 16
                        }
                    }, // ← เผื่อพื้นที่ด้านบนให้ label
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grace: '12%', // ← headroom กันเลขชนขอบบน
                            ticks: {
                                precision: 0
                            },
                            title: {
                                display: true,
                                text: 'จำนวนโครงการ'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'ปี'
                            }
                        }
                    }
                },
                plugins: [valueLabelBar]
            });
        })();


        /* ---------- 12) FTE Bubble (ชื่อ → โครงการ/รวม FTE) ---------- */
        (function() {
            const fteData = {};
            ftes.forEach(f => {
                const name = f.name || 'ไม่ระบุ';
                if (!fteData[name]) fteData[name] = {
                    fte: 0,
                    projectSet: new Set()
                };
                fteData[name].fte += toNumber(f.fte_value || f.fte || 0);
                fteData[name].projectSet.add(f.project_id);
            });
            const datasets = Object.entries(fteData).map(([name, data]) => ({
                label: name,
                data: [{
                    x: data.projectSet.size,
                    y: data.fte,
                    r: Math.max(5, data.fte * 10)
                }]
            }));
            new Chart(document.getElementById('chartFteSummary'), {
                type: 'bubble',
                data: {
                    datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: (ctx) =>
                                    `${ctx.dataset.label}: โครงการ ${ctx.raw.x} / FTE ${ctx.raw.y.toFixed(2)}`
                            }
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'จำนวนโครงการ'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'FTE รวม'
                            }
                        }
                    }
                }
            });
        })();

        /* ---------- Drill-down ตัวอย่าง (คลิกบางกราฟ) ---------- */
        // ตัวอย่าง: คลิก Publication รายปี -> รายการผลงาน
        document.getElementById('chartPublicationPerYear').onclick = function(evt) {
            const chart = Chart.getChart('chartPublicationPerYear');
            const point = chart.getElementsAtEventForMode(evt, 'nearest', {
                intersect: true
            }, true)[0];
            if (!point) return;
            const y = chart.data.labels[point.index];
            const rows = outputs.filter(o => (o.type || '').toLowerCase().includes('pub') && ('' + outputYear(o)) === (
                '' + y));
            let html =
                `<table class="table-auto w-full border text-sm"><thead><tr><th class="p-2">ชื่อผลงาน</th><th class="p-2">ประเภท</th><th class="p-2">โครงการ</th></tr></thead><tbody>`;
            rows.forEach(o => {
                html +=
                    `<tr class="border-t"><td class="p-2">${o.title||'-'}</td><td class="p-2">${o.type||'-'}</td><td class="p-2 text-blue-600"><a href="/projects/${o.project_id}" target="_blank">${o.project_name||'-'}</a></td></tr>`;
            });
            html += `</tbody></table>`;
            showModal(`📚 Publication ปี ${y}`, html);
        };

        /* ---------- Export CSV (ตามตาราง) ---------- */
        document.getElementById('exportCSV').addEventListener('click', () => {
            const csv = [
                ["ชื่อโครงการ", "PI", "ปี", "Co-PI", "CoResearch", "FTE", "Output", "IRB ล่าสุด"]
            ];
            projects.forEach(p => {
                const latestIRB = (p.irbs?.length ?? 0) > 0 ?
                    new Date(p.irbs.sort((a, b) => new Date(b.exp_date) - new Date(a.exp_date))[0].exp_date)
                    .toLocaleDateString("th-TH") :
                    "ไม่มี";
                const fteSum = (p.fte_roles ?? p.fteRoles ?? []).reduce((sum, r) => sum + toNumber(r
                    .fte_value || r.fte || 0), 0).toFixed(2);
                csv.push([p.project_name_th, p.pi_name, p.start_year, p.copis?.length ?? 0, p.co_researches
                    ?.length ?? p.coResearches?.length ?? 0, fteSum, p.outputs?.length ?? 0,
                    latestIRB
                ]);
            });
            const content = "\uFEFF" + csv.map(r => r.join(",")).join("\n");
            const blob = new Blob([content], {
                type: "text/csv;charset=utf-8;"
            });
            const url = URL.createObjectURL(blob);
            const link = Object.assign(document.createElement("a"), {
                href: url,
                download: "dashboard_export.csv"
            });
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>

    <script>
        console.log('sample', (@json($projects))[0]);
    </script>
@endsection

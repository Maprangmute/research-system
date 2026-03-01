@extends('layouts.app')

@section('content')
<div class="overview-wrap space-y-6">
    <style>
        .overview-wrap {
            --ov-bg: #f1f5f9;
            --ov-border: #e2e8f0;
            --ov-text: #0f172a;
            --ov-muted: #64748b;
            --ov-card-shadow: 0 14px 24px rgba(15, 23, 42, 0.08);
        }

        .overview-hero {
            background: linear-gradient(130deg, #0f172a 0%, #0c4a6e 48%, #0891b2 100%);
            border-radius: 20px;
            padding: 1.5rem;
            color: #e2e8f0;
            box-shadow: 0 18px 34px rgba(2, 132, 199, 0.25);
        }

        .overview-panel {
            border: 1px solid var(--ov-border);
            border-radius: 16px;
            background: #ffffff;
            box-shadow: var(--ov-card-shadow);
        }

        .overview-stat {
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .overview-chart {
            min-height: 320px;
        }

        .overview-chart canvas {
            cursor: pointer;
            touch-action: manipulation;
        }

        #overviewChartModalBody .overview-modal-table {
            width: 100%;
            border-collapse: collapse;
        }

        #overviewChartModalBody .overview-modal-table th,
        #overviewChartModalBody .overview-modal-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        #overviewChartModalBody .overview-modal-table thead {
            background: #f8fafc;
        }

        @media (max-width: 768px) {
            #overviewChartModalBody .overview-modal-table thead {
                display: none;
            }

            #overviewChartModalBody .overview-modal-table,
            #overviewChartModalBody .overview-modal-table tbody,
            #overviewChartModalBody .overview-modal-table tr,
            #overviewChartModalBody .overview-modal-table td {
                display: block;
                width: 100%;
            }

            #overviewChartModalBody .overview-modal-table tr {
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                margin-bottom: 12px;
                overflow: hidden;
                background: #ffffff;
            }

            #overviewChartModalBody .overview-modal-table td {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                text-align: left;
                padding: 0.8rem 0.9rem;
            }

            #overviewChartModalBody .overview-modal-table td::before {
                content: attr(data-label);
                flex: 0 0 104px;
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: #64748b;
            }
        }

        .overview-label {
            color: var(--ov-muted);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .overview-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 0.6rem 0.75rem;
            font-size: 0.95rem;
            color: var(--ov-text);
            background: #ffffff;
        }

        .overview-input:focus {
            outline: none;
            border-color: #0891b2;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2);
        }

        @media (max-width: 768px) {
            .overview-hero {
                border-radius: 14px;
            }

            .overview-panel,
            .overview-stat {
                border-radius: 14px;
            }
        }
    </style>

    <section class="overview-hero">
        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-cyan-100/90">Realtime Overview</p>
        <h1 class="text-3xl md:text-4xl font-black mt-2 text-white">ภาพรวมข้อมูลโครงการวิจัย</h1>
        <p class="mt-3 text-sm md:text-base text-cyan-50/90">
            ฟิลเตอร์ข้อมูล, กราฟ, และตารางทั้งหมดทำงานแบบ realtime โดยไม่ต้อง refresh หน้า
        </p>
        <p class="mt-2 text-xs text-cyan-100/80">อัปเดตข้อมูลล่าสุด {{ $generatedAt }} น.</p>
    </section>

    <section class="overview-panel p-5 md:p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Filter ข้อมูล</h2>
                <p class="text-sm text-slate-500">เลือกเงื่อนไขเพื่ออัปเดตสรุปและกราฟแบบทันที</p>
            </div>
            <div class="flex items-center gap-2">
                <span id="liveFilterCount" class="text-sm font-semibold text-slate-700 bg-slate-100 rounded-full px-3 py-1">0 โครงการ</span>
                <button
                    id="resetFilters"
                    type="button"
                    class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200"
                >
                    ล้างฟิลเตอร์
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
            <div>
                <label for="filterSearch" class="overview-label">ค้นหาโครงการ / รหัส / PI</label>
                <input id="filterSearch" type="text" class="overview-input" placeholder="พิมพ์เพื่อค้นหาแบบทันที">
            </div>

            <div>
                <label for="filterYear" class="overview-label">ปีที่เริ่มต้น</label>
                <select id="filterYear" class="overview-input">
                    <option value="">ทุกปี</option>
                    @foreach ($allYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filterType" class="overview-label">ประเภทโครงการ</label>
                <select id="filterType" class="overview-input">
                    <option value="">ทุกประเภท</option>
                    @foreach ($allTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filterFundingType" class="overview-label">ประเภททุน</label>
                <select id="filterFundingType" class="overview-input">
                    <option value="">ทุกประเภททุน</option>
                    @foreach ($allFundingTypes as $fundingType)
                        <option value="{{ $fundingType }}">{{ $fundingType }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filterStatus" class="overview-label">สถานะดำเนินการ</label>
                <select id="filterStatus" class="overview-input">
                    <option value="">ทุกสถานะ</option>
                    @foreach ($allStatuses as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <article class="overview-stat p-4">
            <p class="overview-label">จำนวนโครงการ</p>
            <p id="totalProjects" class="mt-2 text-3xl font-extrabold text-slate-900">0</p>
        </article>
        <article class="overview-stat p-4">
            <p class="overview-label">งบประมาณรวม</p>
            <p id="totalBudget" class="mt-2 text-2xl font-extrabold text-slate-900">0.00 บาท</p>
        </article>
        <article class="overview-stat p-4">
            <p class="overview-label">งบเฉลี่ยต่อโครงการ</p>
            <p id="avgBudget" class="mt-2 text-2xl font-extrabold text-slate-900">0.00 บาท</p>
        </article>
        <article class="overview-stat p-4">
            <p class="overview-label">กำลังดำเนินการ</p>
            <p id="activeProjects" class="mt-2 text-3xl font-extrabold text-slate-900">0</p>
        </article>
    </section>

    <section class="overview-panel p-5 md:p-6 space-y-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">เอกสารสรุปข้อมูลทั้งหมด</h2>
            <p id="reportCaption" class="text-sm text-slate-500 mt-1">สรุปภาพรวมตามฟิลเตอร์ที่เลือก</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <article class="rounded-xl bg-cyan-50 border border-cyan-100 p-4">
                <p class="overview-label">ช่วงปีข้อมูล</p>
                <p id="reportYearRange" class="mt-2 text-lg font-bold text-cyan-900">-</p>
            </article>
            <article class="rounded-xl bg-amber-50 border border-amber-100 p-4">
                <p class="overview-label">ประเภทโครงการเด่น</p>
                <p id="reportTopType" class="mt-2 text-lg font-bold text-amber-900">-</p>
            </article>
            <article class="rounded-xl bg-emerald-50 border border-emerald-100 p-4">
                <p class="overview-label">ประเภททุนเด่น</p>
                <p id="reportTopFunding" class="mt-2 text-lg font-bold text-emerald-900">-</p>
            </article>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="min-w-full text-left">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600">#</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600">ประเภทโครงการ</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600">จำนวนโครงการ</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600">สัดส่วน / งบรวม</th>
                    </tr>
                </thead>
                <tbody id="reportTypeRows" class="divide-y divide-slate-100 bg-white"></tbody>
            </table>
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <article class="overview-panel p-4">
            <div class="flex items-start justify-between gap-3 mb-2">
                <h3 class="text-base font-bold text-slate-800">จำนวนโครงการตามปี</h3>
                <span class="text-xs font-semibold text-sky-700 bg-sky-50 border border-sky-100 rounded-full px-2.5 py-1">แตะหรือคลิกกราฟเพื่อดูข้อมูล</span>
            </div>
            <div class="overview-chart">
                <canvas id="yearChart"></canvas>
            </div>
        </article>

        <article class="overview-panel p-4">
            <div class="flex items-start justify-between gap-3 mb-2">
                <h3 class="text-base font-bold text-slate-800">สัดส่วนประเภทโครงการ</h3>
                <span class="text-xs font-semibold text-sky-700 bg-sky-50 border border-sky-100 rounded-full px-2.5 py-1">แตะหรือคลิกกราฟเพื่อดูข้อมูล</span>
            </div>
            <div class="overview-chart">
                <canvas id="typeChart"></canvas>
            </div>
        </article>

        <article class="overview-panel p-4">
            <div class="flex items-start justify-between gap-3 mb-2">
                <h3 class="text-base font-bold text-slate-800">งบประมาณรวมตามปี</h3>
                <span class="text-xs font-semibold text-sky-700 bg-sky-50 border border-sky-100 rounded-full px-2.5 py-1">แตะหรือคลิกกราฟเพื่อดูข้อมูล</span>
            </div>
            <div class="overview-chart">
                <canvas id="budgetChart"></canvas>
            </div>
        </article>
    </section>

    <section class="overview-panel p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h3 class="text-lg font-bold text-slate-900">รายการโครงการที่ผ่านฟิลเตอร์</h3>
            <span id="filteredCount" class="text-sm font-semibold text-slate-600">0 รายการ</span>
        </div>

        <div class="overflow-auto border border-slate-200 rounded-xl">
            <table class="min-w-full text-left whitespace-nowrap">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600">#</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600">รหัส</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600">ชื่อโครงการ</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600">ปีเริ่ม</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600">ประเภท</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600">ประเภททุน</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600">สถานะ</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600">งบประมาณ</th>
                    </tr>
                </thead>
                <tbody id="projectRows" class="divide-y divide-slate-100 bg-white"></tbody>
            </table>
        </div>
    </section>

    <div id="overviewChartModal" class="fixed inset-0 z-[70] hidden">
        <div id="overviewChartModalOverlay" class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm"></div>
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6">
            <div class="w-full max-w-6xl max-h-[86vh] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-5">
                    <div>
                        <h3 id="overviewChartModalTitle" class="text-lg font-bold text-slate-900">รายละเอียดจากกราฟ</h3>
                        <p id="overviewChartModalMeta" class="mt-1 text-sm text-slate-500">0 รายการ</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-slate-500">Sort:
                            <select id="overviewChartModalSort" class="ml-1 rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                <option value="name_asc">ชื่อ (ก-ฮ)</option>
                                <option value="name_desc">ชื่อ (ฮ-ก)</option>
                                <option value="pi_asc">PI (ก-ฮ)</option>
                                <option value="pi_desc">PI (ฮ-ก)</option>
                                <option value="year_desc">ปี (ใหม่→เก่า)</option>
                                <option value="year_asc">ปี (เก่า→ใหม่)</option>
                                <option value="budget_desc">งบ (มาก→น้อย)</option>
                                <option value="budget_asc">งบ (น้อย→มาก)</option>
                            </select>
                        </label>
                        <button id="overviewChartModalClose" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                            <span class="sr-only">ปิด</span>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6l-12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="overviewChartModalBody" class="max-h-[calc(86vh-84px)] overflow-auto p-4 sm:p-5 text-sm"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    'use strict';

    const sourceRows = @json($projectsData, JSON_UNESCAPED_UNICODE);
    const projectShowBaseUrl = @json(url('/rm/projects'));

    const palette = [
        '#0ea5e9', '#22c55e', '#f59e0b', '#f97316', '#3b82f6',
        '#ec4899', '#14b8a6', '#a855f7', '#ef4444', '#06b6d4',
    ];

    const elements = {
        search: document.getElementById('filterSearch'),
        year: document.getElementById('filterYear'),
        type: document.getElementById('filterType'),
        fundingType: document.getElementById('filterFundingType'),
        status: document.getElementById('filterStatus'),
        reset: document.getElementById('resetFilters'),
        liveCount: document.getElementById('liveFilterCount'),
        totalProjects: document.getElementById('totalProjects'),
        totalBudget: document.getElementById('totalBudget'),
        avgBudget: document.getElementById('avgBudget'),
        activeProjects: document.getElementById('activeProjects'),
        reportCaption: document.getElementById('reportCaption'),
        reportYearRange: document.getElementById('reportYearRange'),
        reportTopType: document.getElementById('reportTopType'),
        reportTopFunding: document.getElementById('reportTopFunding'),
        reportRows: document.getElementById('reportTypeRows'),
        filteredCount: document.getElementById('filteredCount'),
        projectRows: document.getElementById('projectRows'),
        modal: document.getElementById('overviewChartModal'),
        modalOverlay: document.getElementById('overviewChartModalOverlay'),
        modalClose: document.getElementById('overviewChartModalClose'),
        modalTitle: document.getElementById('overviewChartModalTitle'),
        modalMeta: document.getElementById('overviewChartModalMeta'),
        modalBody: document.getElementById('overviewChartModalBody'),
        modalSort: document.getElementById('overviewChartModalSort'),
    };

    const formatNumber = new Intl.NumberFormat('th-TH');
    const formatMoney = new Intl.NumberFormat('th-TH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const normalizeText = (value, fallback) => {
        const clean = String(value ?? '').trim();
        return clean === '' ? fallback : clean;
    };

    const parseBudget = (value) => {
        const numeric = Number(value);
        return Number.isFinite(numeric) ? numeric : 0;
    };

    const escapeHtml = (value) => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const projects = sourceRows.map((row) => ({
        id: Number(row.id ?? 0),
        code: normalizeText(row.project_code, '-'),
        name: normalizeText(row.project_name_th || row.project_name_en, 'ไม่ระบุชื่อโครงการ'),
        type: normalizeText(row.project_type, 'ไม่ระบุประเภท'),
        fundingType: normalizeText(row.funding_type, 'ไม่ระบุประเภททุน'),
        status: normalizeText(row.execution_status, 'ไม่ระบุสถานะ'),
        year: normalizeText(row.start_year, 'ไม่ระบุปี'),
        piName: normalizeText(row.pi_name, 'ไม่ระบุ PI'),
        budget: parseBudget(row.budget),
    }));

    const charts = {
        year: null,
        type: null,
        budget: null,
    };

    const getHitPoint = (event, chart) => {
        if (!chart) return null;
        const points = chart.getElementsAtEventForMode(event, 'nearest', {
            intersect: true,
        }, true);
        return points.length > 0 ? points[0] : null;
    };

    const applyOverviewProjectSort = (rows, mode = 'name_asc') => {
        const list = [...rows];
        const compareText = (a, b) => String(a || '').localeCompare(String(b || ''), 'th');

        list.sort((a, b) => {
            switch (mode) {
                case 'name_desc':
                    return compareText(b.name, a.name);
                case 'pi_asc':
                    return compareText(a.piName, b.piName);
                case 'pi_desc':
                    return compareText(b.piName, a.piName);
                case 'year_desc':
                    return compareText(b.year, a.year);
                case 'year_asc':
                    return compareText(a.year, b.year);
                case 'budget_desc':
                    return (b.budget || 0) - (a.budget || 0);
                case 'budget_asc':
                    return (a.budget || 0) - (b.budget || 0);
                case 'name_asc':
                default:
                    return compareText(a.name, b.name);
            }
        });

        return list;
    };

    const openOverviewChartModal = (title, rows) => {
        const sortMode = elements.modalSort?.value || 'name_asc';
        const projectsForModal = applyOverviewProjectSort(rows, sortMode);
        window.overviewChartModalState = {
            title,
            rows: [...rows],
        };

        elements.modalTitle.textContent = title;
        elements.modalMeta.textContent = `${formatNumber.format(projectsForModal.length)} รายการ`;

        if (!projectsForModal.length) {
            elements.modalBody.innerHTML = '<div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-8 text-center text-slate-500">ไม่พบข้อมูลที่ตรงกับจุดที่เลือก</div>';
            elements.modal.classList.remove('hidden');
            return;
        }

        const rowsHtml = projectsForModal.map((project, index) => `
            <tr>
                <td data-label="#"><span class="font-semibold text-slate-500">${index + 1}</span></td>
                <td data-label="รหัส">${escapeHtml(project.code)}</td>
                <td data-label="ชื่อโครงการ">
                    <a href="${projectShowBaseUrl}/${project.id}" class="font-semibold text-sky-700 underline underline-offset-2">
                        ${escapeHtml(project.name)}
                    </a>
                </td>
                <td data-label="ปีเริ่ม">${escapeHtml(project.year)}</td>
                <td data-label="ประเภท">${escapeHtml(project.type)}</td>
                <td data-label="ประเภททุน">${escapeHtml(project.fundingType)}</td>
                <td data-label="สถานะ">${escapeHtml(project.status)}</td>
                <td data-label="PI">${escapeHtml(project.piName)}</td>
                <td data-label="งบประมาณ"><span class="font-semibold text-slate-800">${formatBudget(project.budget)}</span></td>
            </tr>
        `).join('');

        elements.modalBody.innerHTML = `
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="overview-modal-table min-w-full text-left">
                    <thead>
                        <tr>
                            <th class="text-xs font-bold uppercase tracking-wide text-slate-600">#</th>
                            <th class="text-xs font-bold uppercase tracking-wide text-slate-600">รหัส</th>
                            <th class="text-xs font-bold uppercase tracking-wide text-slate-600">ชื่อโครงการ</th>
                            <th class="text-xs font-bold uppercase tracking-wide text-slate-600">ปีเริ่ม</th>
                            <th class="text-xs font-bold uppercase tracking-wide text-slate-600">ประเภท</th>
                            <th class="text-xs font-bold uppercase tracking-wide text-slate-600">ประเภททุน</th>
                            <th class="text-xs font-bold uppercase tracking-wide text-slate-600">สถานะ</th>
                            <th class="text-xs font-bold uppercase tracking-wide text-slate-600">PI</th>
                            <th class="text-xs font-bold uppercase tracking-wide text-slate-600">งบประมาณ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">${rowsHtml}</tbody>
                </table>
            </div>
        `;

        elements.modal.classList.remove('hidden');
    };

    const closeOverviewChartModal = () => {
        window.overviewChartModalState = null;
        elements.modal.classList.add('hidden');
    };

    const bindChartHover = (chart) => {
        if (!chart) return;

        chart.options.onHover = (event, activeElements) => {
            const canvas = event?.native?.target || chart.canvas;
            if (!canvas) return;
            canvas.style.cursor = activeElements?.length ? 'pointer' : 'default';
        };
    };

    const bindOverviewChartNativeEvents = (chart) => {
        if (!chart?.canvas || chart.canvas.dataset.overviewBound === 'true') return;
        chart.canvas.dataset.overviewBound = 'true';

        let lastPointerUpAt = 0;
        chart.canvas.addEventListener('pointerup', (event) => {
            lastPointerUpAt = Date.now();
            handleOverviewChartClick(event, chart);
        }, {
            passive: true,
        });

        chart.canvas.addEventListener('click', (event) => {
            if (Date.now() - lastPointerUpAt < 220) return;
            handleOverviewChartClick(event, chart);
        });
    };

    const resolveOverviewChartSelection = (chart, point) => {
        const currentRows = getFilteredProjects();
        const chartId = chart?.canvas?.id;
        const label = chart?.data?.labels?.[point.index];

        if (!chartId || !label || label === 'ไม่มีข้อมูล') return null;

        if (chartId === 'yearChart') {
            return {
                title: `โครงการปี ${label}`,
                projects: currentRows.filter((project) => project.year === label),
            };
        }

        if (chartId === 'typeChart') {
            return {
                title: `โครงการประเภท ${label}`,
                projects: currentRows.filter((project) => project.type === label),
            };
        }

        if (chartId === 'budgetChart') {
            return {
                title: `งบประมาณโครงการปี ${label}`,
                projects: currentRows.filter((project) => project.year === label),
            };
        }

        return null;
    };

    const handleOverviewChartClick = (event, chart) => {
        let hit = getHitPoint(event, chart);
        if (!hit && chart?.canvas?.id === 'budgetChart') {
            const points = chart.getElementsAtEventForMode(event, 'index', {
                intersect: false,
                axis: 'x',
            }, true);
            hit = points?.[0] || null;
        }
        if (!hit && chart?.canvas?.id === 'yearChart') {
            const points = chart.getElementsAtEventForMode(event, 'index', {
                intersect: false,
                axis: 'x',
            }, true);
            hit = points?.[0] || null;
        }
        if (!hit) return;

        const result = resolveOverviewChartSelection(chart, hit);
        if (!result) return;

        openOverviewChartModal(result.title, result.projects || []);
    };

    const sortYearLabel = (a, b) => {
        const extract = (label) => {
            const numeric = parseInt(String(label).replace(/[^0-9]/g, ''), 10);
            return Number.isNaN(numeric) ? -1 : numeric;
        };

        const ay = extract(a);
        const by = extract(b);

        if (ay === by) {
            return String(a).localeCompare(String(b), 'th');
        }

        return ay - by;
    };

    const groupCount = (items, selector) => {
        const map = new Map();
        items.forEach((item) => {
            const key = selector(item);
            map.set(key, (map.get(key) ?? 0) + 1);
        });
        return map;
    };

    const groupBudgetByYear = (items) => {
        const map = new Map();
        items.forEach((item) => {
            map.set(item.year, (map.get(item.year) ?? 0) + item.budget);
        });
        return map;
    };

    const toEntries = (map, sortMode = 'count') => {
        const entries = [...map.entries()];
        if (sortMode === 'year') {
            return entries.sort((a, b) => sortYearLabel(a[0], b[0]));
        }
        return entries.sort((a, b) => b[1] - a[1]);
    };

    const makeGradient = (canvasId, from, to) => {
        const canvas = document.getElementById(canvasId);
        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, from);
        gradient.addColorStop(1, to);
        return gradient;
    };

    const ensureCharts = () => {
        if (!charts.year) {
            charts.year = new Chart(document.getElementById('yearChart'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'จำนวนโครงการ',
                        data: [],
                        backgroundColor: makeGradient('yearChart', 'rgba(14,165,233,0.9)', 'rgba(6,182,212,0.25)'),
                        borderColor: '#0284c7',
                        borderWidth: 1.5,
                        borderRadius: 10,
                        maxBarThickness: 42,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    animation: { duration: 380, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => ` ${formatNumber.format(context.parsed.y ?? 0)} โครงการ`,
                            },
                        },
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(148,163,184,0.25)' },
                        },
                    },
                },
            });

            bindChartHover(charts.year);
            bindOverviewChartNativeEvents(charts.year);
        }

        if (!charts.type) {
            charts.type = new Chart(document.getElementById('typeChart'), {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'สัดส่วนโครงการ',
                        data: [],
                        backgroundColor: [],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    cutout: '58%',
                    animation: { duration: 380, easing: 'easeOutQuart' },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, boxWidth: 10, padding: 14 },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const value = context.parsed ?? 0;
                                    const total = context.dataset.data.reduce((sum, number) => sum + number, 0);
                                    const percent = total ? (value / total) * 100 : 0;
                                    return ` ${context.label}: ${formatNumber.format(value)} โครงการ (${percent.toFixed(1)}%)`;
                                },
                            },
                        },
                    },
                },
            });

            bindChartHover(charts.type);
            bindOverviewChartNativeEvents(charts.type);
        }

        if (!charts.budget) {
            charts.budget = new Chart(document.getElementById('budgetChart'), {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'งบประมาณรวม',
                        data: [],
                        tension: 0.35,
                        fill: true,
                        borderWidth: 2.5,
                        borderColor: '#f97316',
                        pointRadius: 4,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#f97316',
                        backgroundColor: makeGradient('budgetChart', 'rgba(249,115,22,0.35)', 'rgba(249,115,22,0.04)'),
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    animation: { duration: 380, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => ` ${formatMoney.format(context.parsed.y ?? 0)} บาท`,
                            },
                        },
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(148,163,184,0.25)' },
                            ticks: {
                                callback: (value) => `${formatNumber.format(value)} บ.`,
                            },
                        },
                    },
                },
            });

            bindChartHover(charts.budget);
            bindOverviewChartNativeEvents(charts.budget);
        }
    };

    const isActiveStatus = (status) => {
        const text = String(status).toLowerCase();
        return text.includes('ดำเนินการ') || text.includes('active') || text.includes('ongoing') || text.includes('progress');
    };

    const getFilteredProjects = () => {
        const searchValue = elements.search.value.trim().toLowerCase();
        const year = elements.year.value;
        const type = elements.type.value;
        const fundingType = elements.fundingType.value;
        const status = elements.status.value;

        return projects.filter((project) => {
            if (year && project.year !== year) return false;
            if (type && project.type !== type) return false;
            if (fundingType && project.fundingType !== fundingType) return false;
            if (status && project.status !== status) return false;

            if (searchValue) {
                const haystack = `${project.code} ${project.name} ${project.piName}`.toLowerCase();
                if (!haystack.includes(searchValue)) {
                    return false;
                }
            }

            return true;
        });
    };

    const formatBudget = (budget) => `${formatMoney.format(budget)} บาท`;

    const updateSummaryCards = (rows) => {
        const total = rows.length;
        const budget = rows.reduce((sum, row) => sum + row.budget, 0);
        const average = total > 0 ? budget / total : 0;
        const active = rows.filter((row) => isActiveStatus(row.status)).length;

        elements.totalProjects.textContent = formatNumber.format(total);
        elements.totalBudget.textContent = formatBudget(budget);
        elements.avgBudget.textContent = formatBudget(average);
        elements.activeProjects.textContent = formatNumber.format(active);
    };

    const updateReport = (rows) => {
        const total = rows.length;
        const years = rows
            .map((row) => row.year)
            .filter((year) => year !== 'ไม่ระบุปี')
            .sort(sortYearLabel);

        const yearRange = years.length > 0
            ? `${years[0]} - ${years[years.length - 1]}`
            : 'ไม่มีช่วงปีที่ระบุ';

        const typeEntries = toEntries(groupCount(rows, (row) => row.type), 'count');
        const fundingEntries = toEntries(groupCount(rows, (row) => row.fundingType), 'count');
        const topType = typeEntries[0];
        const topFunding = fundingEntries[0];

        elements.reportCaption.textContent = `ข้อมูลที่กรองแล้ว ${formatNumber.format(total)} โครงการ จากทั้งหมด ${formatNumber.format(projects.length)} โครงการ`;
        elements.reportYearRange.textContent = yearRange;
        elements.reportTopType.textContent = topType
            ? `${topType[0]} (${formatNumber.format(topType[1])} โครงการ)`
            : 'ไม่มีข้อมูล';
        elements.reportTopFunding.textContent = topFunding
            ? `${topFunding[0]} (${formatNumber.format(topFunding[1])} โครงการ)`
            : 'ไม่มีข้อมูล';

        elements.reportRows.innerHTML = '';

        if (typeEntries.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="4" class="px-4 py-4 text-center text-sm text-slate-500">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>';
            elements.reportRows.appendChild(row);
            return;
        }

        const budgetByType = new Map();
        rows.forEach((entry) => {
            budgetByType.set(entry.type, (budgetByType.get(entry.type) ?? 0) + entry.budget);
        });

        typeEntries.slice(0, 8).forEach(([type, count], index) => {
            const share = total > 0 ? ((count / total) * 100).toFixed(1) : '0.0';
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-3 text-sm text-slate-700">${index + 1}</td>
                <td class="px-4 py-3 text-sm font-medium text-slate-800">${escapeHtml(type)}</td>
                <td class="px-4 py-3 text-sm text-slate-700">${formatNumber.format(count)} โครงการ</td>
                <td class="px-4 py-3 text-sm text-slate-700">${share}% | ${formatBudget(budgetByType.get(type) ?? 0)}</td>
            `;
            elements.reportRows.appendChild(row);
        });
    };

    const updateCharts = (rows) => {
        ensureCharts();

        const yearEntries = toEntries(groupCount(rows, (row) => row.year), 'year');
        const yearLabels = yearEntries.map((entry) => entry[0]);
        const yearValues = yearEntries.map((entry) => entry[1]);
        charts.year.data.labels = yearLabels.length > 0 ? yearLabels : ['ไม่มีข้อมูล'];
        charts.year.data.datasets[0].data = yearValues.length > 0 ? yearValues : [0];
        charts.year.update();

        const typeEntries = toEntries(groupCount(rows, (row) => row.type), 'count').slice(0, 8);
        charts.type.data.labels = typeEntries.length > 0 ? typeEntries.map((entry) => entry[0]) : ['ไม่มีข้อมูล'];
        charts.type.data.datasets[0].data = typeEntries.length > 0 ? typeEntries.map((entry) => entry[1]) : [1];
        charts.type.data.datasets[0].backgroundColor = typeEntries.length > 0
            ? typeEntries.map((entry, index) => palette[index % palette.length])
            : ['#cbd5e1'];
        charts.type.update();

        const budgetEntries = toEntries(groupBudgetByYear(rows), 'year');
        charts.budget.data.labels = budgetEntries.length > 0 ? budgetEntries.map((entry) => entry[0]) : ['ไม่มีข้อมูล'];
        charts.budget.data.datasets[0].data = budgetEntries.length > 0
            ? budgetEntries.map((entry) => Number(entry[1].toFixed(2)))
            : [0];
        charts.budget.update();
    };

    const updateProjectTable = (rows) => {
        elements.filteredCount.textContent = `${formatNumber.format(rows.length)} รายการ`;
        elements.liveCount.textContent = `${formatNumber.format(rows.length)} โครงการ`;
        elements.projectRows.innerHTML = '';

        if (rows.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="8" class="px-4 py-5 text-center text-sm text-slate-500">ไม่พบข้อมูลที่ตรงกับฟิลเตอร์</td>';
            elements.projectRows.appendChild(row);
            return;
        }

        rows.forEach((project, index) => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-slate-50 transition-colors';
            row.innerHTML = `
                <td class="px-4 py-3 text-sm text-slate-600">${index + 1}</td>
                <td class="px-4 py-3 text-sm text-slate-700">${escapeHtml(project.code)}</td>
                <td class="px-4 py-3 text-sm font-medium text-slate-800">${escapeHtml(project.name)}</td>
                <td class="px-4 py-3 text-sm text-slate-700">${escapeHtml(project.year)}</td>
                <td class="px-4 py-3 text-sm text-slate-700">${escapeHtml(project.type)}</td>
                <td class="px-4 py-3 text-sm text-slate-700">${escapeHtml(project.fundingType)}</td>
                <td class="px-4 py-3 text-sm text-slate-700">${escapeHtml(project.status)}</td>
                <td class="px-4 py-3 text-sm text-slate-700">${formatBudget(project.budget)}</td>
            `;
            elements.projectRows.appendChild(row);
        });
    };

    const render = () => {
        const filteredRows = getFilteredProjects();
        updateSummaryCards(filteredRows);
        updateReport(filteredRows);
        updateCharts(filteredRows);
        updateProjectTable(filteredRows);
    };

    let searchTimer = null;
    elements.search.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(render, 120);
    });

    [elements.year, elements.type, elements.fundingType, elements.status].forEach((element) => {
        element.addEventListener('change', render);
    });

    elements.reset.addEventListener('click', () => {
        elements.search.value = '';
        elements.year.value = '';
        elements.type.value = '';
        elements.fundingType.value = '';
        elements.status.value = '';
        render();
    });

    elements.modalClose?.addEventListener('click', closeOverviewChartModal);
    elements.modalOverlay?.addEventListener('click', closeOverviewChartModal);
    elements.modalSort?.addEventListener('change', () => {
        if (!window.overviewChartModalState) return;
        openOverviewChartModal(window.overviewChartModalState.title, window.overviewChartModalState.rows);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !elements.modal.classList.contains('hidden')) {
            closeOverviewChartModal();
        }
    });

    render();
})();
</script>
@endsection

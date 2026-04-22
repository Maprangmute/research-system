@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <style>
            .person-panel {
                border: 1px solid #e2e8f0;
                background: rgba(255, 255, 255, 0.94);
                border-radius: 20px;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
            }

            .overview-card {
                min-height: 100%;
            }

            .summary-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.9rem;
            }

            .summary-card {
                border-radius: 16px;
                border: 1px solid #e2e8f0;
                background: #f8fafc;
                padding: 1rem;
            }

            .summary-stack {
                display: grid;
                gap: 0.75rem;
            }

            .summary-mini-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
            }

            .section-list {
                display: grid;
                gap: 0.85rem;
            }

            .record-card {
                border-radius: 20px;
                border: 1px solid #e2e8f0;
                background: #fff;
                padding: 1rem;
            }

            .list-row {
                display: grid;
                gap: 0.9rem;
                border-radius: 18px;
                border: 1px solid #e2e8f0;
                background: #fff;
                padding: 1rem 1.05rem;
            }

            .section-search {
                width: 100%;
                border-radius: 12px;
                border: 1px solid #cbd5e1;
                background: #fff;
                padding: 0.7rem 0.9rem;
                font-size: 0.92rem;
                color: #334155;
            }

            .section-search:focus {
                outline: none;
                border-color: #94a3b8;
                box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.12);
            }

            .section-toolbar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
            }

            .section-heading {
                display: inline-flex;
                align-items: center;
                gap: 0.7rem;
            }

            .section-heading::before {
                content: '';
                width: 10px;
                height: 28px;
                border-radius: 999px;
                background: #cbd5e1;
                flex: 0 0 auto;
            }

            .section-heading.projects::before {
                background: linear-gradient(180deg, #2563eb 0%, #60a5fa 100%);
            }

            .section-heading.research::before {
                background: linear-gradient(180deg, #7c3aed 0%, #a78bfa 100%);
            }

            .section-heading.presentation::before {
                background: linear-gradient(180deg, #0f766e 0%, #2dd4bf 100%);
            }

            .section-heading.textbook::before {
                background: linear-gradient(180deg, #c2410c 0%, #fb923c 100%);
            }

            .section-heading.award::before {
                background: linear-gradient(180deg, #b45309 0%, #facc15 100%);
            }

            .section-search-row {
                display: flex;
                width: 100%;
                max-width: 380px;
            }

            .section-filter-row {
                display: flex;
                flex-wrap: wrap;
                gap: 0.65rem;
                margin-top: 0.55rem;
                margin-bottom: 0.55rem;
            }

            .filter-select {
                min-width: 150px;
                border-radius: 12px;
                border: 1px solid #cbd5e1;
                background: #fff;
                padding: 0.72rem 0.85rem;
                font-size: 0.9rem;
                color: #334155;
            }

            .filter-select:focus {
                outline: none;
                border-color: #94a3b8;
                box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.12);
            }

            .section-table {
                width: 100%;
                min-width: 920px;
                border-collapse: collapse;
            }

            .section-table th {
                white-space: nowrap;
                font-size: 12px;
                color: #475569;
                letter-spacing: 0.02em;
            }

            .section-table td {
                vertical-align: top;
            }

            .section-table tbody tr:nth-child(even) {
                background: #fbfdff;
            }

            .section-table tbody tr:hover {
                background: #f8fafc;
            }

            .role-badge {
                display: inline-flex;
                align-items: center;
                border-radius: 999px;
                border: 1px solid #e2e8f0;
                padding: 0.3rem 0.7rem;
                font-size: 12px;
                font-weight: 700;
            }

            .role-badge.pi {
                border-color: #bfdbfe;
                background: #eff6ff;
                color: #1d4ed8;
            }

            .role-badge.co-pi {
                border-color: #fde68a;
                background: #fffbeb;
                color: #b45309;
            }

            .role-badge.co-researcher {
                border-color: #bbf7d0;
                background: #f0fdf4;
                color: #15803d;
            }

            .role-badge.first-author {
                border-color: #c7d2fe;
                background: #eef2ff;
                color: #4338ca;
            }

            .role-badge.co-author {
                border-color: #fed7aa;
                background: #fff7ed;
                color: #c2410c;
            }

            .role-badge.corresponding-author {
                border-color: #a7f3d0;
                background: #ecfdf5;
                color: #047857;
            }

            .role-badge.source-project {
                border-color: #cbd5e1;
                background: #f8fafc;
                color: #475569;
            }

            .role-badge.source-extra {
                border-color: #fbcfe8;
                background: #fdf2f8;
                color: #be185d;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                border-radius: 999px;
                border: 1px solid #cbd5e1;
                background: #f8fafc;
                color: #475569;
                padding: 0.28rem 0.72rem;
                font-size: 12px;
                font-weight: 700;
            }

            .status-badge.is-active {
                border-color: #bfdbfe;
                background: #eff6ff;
                color: #1d4ed8;
            }

            .status-badge.is-analysis {
                border-color: #fde68a;
                background: #fffbeb;
                color: #b45309;
            }

            .status-badge.is-complete {
                border-color: #bbf7d0;
                background: #f0fdf4;
                color: #15803d;
            }

            .live-search {
                width: 100%;
                border-radius: 14px;
                border: 1px solid #cbd5e1;
                background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
                padding: 0.85rem 1rem;
                font-size: 0.94rem;
                color: #334155;
                transition: border-color 0.18s ease, box-shadow 0.18s ease;
            }

            .live-search:focus {
                outline: none;
                border-color: #94a3b8;
                box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.12);
            }

            .pager {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                justify-content: flex-end;
            }

            .pager button {
                min-width: 2.15rem;
                border-radius: 12px;
                border: 1px solid #cbd5e1;
                background: #fff;
                color: #475569;
                padding: 0.42rem 0.72rem;
                font-size: 0.84rem;
                font-weight: 700;
                transition: all 0.18s ease;
            }

            .pager button:hover {
                border-color: #94a3b8;
                color: #0f172a;
            }

            .pager button.is-active {
                border-color: #0f172a;
                background: #0f172a;
                color: #fff;
            }

            .pager button:disabled {
                opacity: 0.45;
                cursor: not-allowed;
            }

            .pager-ellipsis {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 2rem;
                color: #94a3b8;
                font-weight: 700;
            }

            @media (max-width: 900px) {
                .summary-grid,
                .summary-mini-grid {
                    grid-template-columns: 1fr;
                }

                .section-table,
                .section-table thead,
                .section-table tbody,
                .section-table tr,
                .section-table td {
                    display: block;
                    width: 100%;
                }

                .section-table {
                    min-width: 0;
                }

                .section-table thead {
                    display: none;
                }

                .section-table tbody tr {
                    margin: 0.75rem;
                    border: 1px solid #e2e8f0;
                    border-radius: 16px;
                    background: #fff;
                    overflow: hidden;
                }

                .section-table tbody td {
                    padding: 0.8rem 0.95rem;
                    border-top: 1px solid #e2e8f0;
                }

                .section-table tbody td:first-child {
                    border-top: 0;
                }

                .section-table tbody td[data-label]::before {
                    content: attr(data-label);
                    display: block;
                    margin-bottom: 0.3rem;
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 0.04em;
                    text-transform: uppercase;
                    color: #64748b;
                }

                .pager {
                    justify-content: flex-start;
                }
            }
        </style>

        <section class="person-panel overflow-hidden">
            <div class="grid gap-6 p-5 md:grid-cols-[280px_minmax(0,1fr)] md:p-7">
                <div class="overview-card flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 p-7 text-center">
                    @if ($subject->profile_photo_path)
                        <img
                            src="{{ asset('storage/' . $subject->profile_photo_path) }}?v={{ time() }}"
                            alt="{{ $subject->full_name }}"
                            onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($subject->full_name) }}&background=0F172A&color=fff&size=128'"
                            class="h-36 w-36 rounded-[28px] border border-slate-200 object-cover shadow-sm"
                        >
                    @else
                        <img
                            src="https://ui-avatars.com/api/?name={{ urlencode($subject->full_name) }}&background=0F172A&color=fff&size=128"
                            alt="{{ $subject->full_name }}"
                            class="h-36 w-36 rounded-[28px] border border-slate-200 object-cover shadow-sm"
                        >
                    @endif
                    <div class="mt-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Personal Overview</p>
                        <h1 class="mt-3 text-3xl font-bold leading-tight text-slate-900">{{ $subject->full_name }}</h1>
                        @if ($subject->research_display_name !== $subject->full_name)
                            <p class="mt-3 text-base text-slate-500">{{ $subject->research_display_name }}</p>
                        @endif
                        @if (filled($subject->position))
                            <p class="mt-3 text-base text-slate-600">{{ $subject->position }}</p>
                        @endif
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div></div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('outputs.people') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-900">
                                กลับหน้ารายชื่อ
                            </a>
                            <a href="{{ route('outputs.people.pdf', $subjectRouteKey) }}" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                                Export PDF
                            </a>
                        </div>
                    </div>

                    <div class="summary-grid">
                        <article class="summary-card">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Project Roles</p>
                            <div class="summary-mini-grid mt-4">
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <p class="text-xs font-semibold text-slate-500">PI</p>
                                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['pi_projects']) }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Projects</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <p class="text-xs font-semibold text-slate-500">Co-PI</p>
                                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['co_pi_projects']) }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Projects</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <p class="text-xs font-semibold text-slate-500">Co-Researcher</p>
                                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['co_research_projects']) }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Projects</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <p class="text-xs font-semibold text-slate-500">Total Projects</p>
                                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['total_projects']) }}</p>
                                    <p class="mt-1 text-sm text-slate-500">All Roles</p>
                                </div>
                            </div>
                        </article>
                        <article class="summary-card">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Output Summary</p>
                            <div class="summary-mini-grid mt-4">
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <p class="text-xs font-semibold text-slate-500">Research</p>
                                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['research_outputs']) }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Papers</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <p class="text-xs font-semibold text-slate-500">Presentation</p>
                                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['presentation_outputs']) }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Outputs</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <p class="text-xs font-semibold text-slate-500">Textbook</p>
                                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['textbook_outputs']) }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Outputs</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <p class="text-xs font-semibold text-slate-500">Award</p>
                                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['award_outputs']) }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Outputs</p>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="person-panel p-5 md:p-6">
            <div class="section-toolbar mb-4">
                <div>
                    <h2 class="section-heading projects text-xl font-bold text-slate-900">Projects</h2>
                    <p class="text-sm text-slate-500">รายการโครงการที่เกี่ยวข้องทั้งหมด</p>
                </div>
                <div class="section-search-row">
                    <input id="projectSearchInput" type="text" placeholder="ค้นหาโครงการ / รหัส / บทบาท / สถานะ" class="live-search">
                </div>
            </div>
            <div class="section-filter-row">
                <select id="projectYearFilter" class="filter-select">
                    <option value="">ทุกปี</option>
                </select>
                <select id="projectStatusFilter" class="filter-select">
                    <option value="">ทุกสถานะ</option>
                </select>
                <select id="projectRoleFilter" class="filter-select">
                    <option value="">ทุกบทบาท</option>
                </select>
                <select id="projectSortSelect" class="filter-select">
                    <option value="year_desc">เรียงปีล่าสุด</option>
                    <option value="year_asc">เรียงปีเก่าสุด</option>
                    <option value="name_asc">ชื่อ A-Z</option>
                    <option value="name_desc">ชื่อ Z-A</option>
                </select>
            </div>

            @if ($projectRows->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-slate-500">
                    ไม่พบโครงการที่ผูกกับผู้ใช้นี้
                </div>
            @else
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="section-table text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">โครงการ</th>
                                <th class="px-4 py-3 text-left font-semibold">ปี</th>
                                <th class="px-4 py-3 text-left font-semibold">สถานะ</th>
                                <th class="px-4 py-3 text-left font-semibold">บทบาท</th>
                            </tr>
                        </thead>
                        <tbody id="projectTableBody"></tbody>
                    </table>
                </div>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <p id="projectResultMeta" class="text-sm text-slate-500"></p>
                    <div id="projectPager" class="pager"></div>
                </div>
            @endif
        </section>

        <section class="person-panel p-5 md:p-6">
            <div class="section-toolbar mb-4">
                <div>
                    <h2 class="section-heading research text-xl font-bold text-slate-900">Research Papers</h2>
                    <p class="text-sm text-slate-500">รวมทั้ง Project outputs และ Extra outputs แล้ว</p>
                </div>
                <div class="section-search-row">
                    <input id="researchSearchInput" type="text" placeholder="ค้นหาชื่อ paper / journal / ผู้เขียน / แหล่งอ้างอิง" class="live-search">
                </div>
            </div>
            <div class="section-filter-row">
                <select id="researchYearFilter" class="filter-select">
                    <option value="">ทุกปี</option>
                </select>
                <select id="researchJournalFilter" class="filter-select">
                    <option value="">ทุก Journal</option>
                </select>
                <select id="researchQuartileFilter" class="filter-select">
                    <option value="">ทุก Quartile</option>
                </select>
                <select id="researchRoleFilter" class="filter-select">
                    <option value="">ทุกบทบาท</option>
                </select>
                <select id="researchOriginFilter" class="filter-select">
                    <option value="">ทุกที่มา</option>
                </select>
                <select id="researchSortSelect" class="filter-select">
                    <option value="year_desc">เรียงปีล่าสุด</option>
                    <option value="year_asc">เรียงปีเก่าสุด</option>
                    <option value="title_asc">ชื่อ A-Z</option>
                    <option value="if_desc">IF มากไปน้อย</option>
                </select>
            </div>

            @if ($researchRows->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-slate-500">
                    ไม่พบ research paper ของผู้ใช้นี้
                </div>
            @else
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="section-table text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">ชื่อผลงาน</th>
                                <th class="px-4 py-3 text-left font-semibold">Journal</th>
                                <th class="px-4 py-3 text-left font-semibold">IF / Quartile</th>
                                <th class="px-4 py-3 text-left font-semibold">ปี</th>
                                <th class="px-4 py-3 text-left font-semibold" style="min-width: 190px;">บทบาท</th>
                                <th class="px-4 py-3 text-left font-semibold">โครงการ / skill ส่วนบุคคล</th>
                            </tr>
                        </thead>
                        <tbody id="researchTableBody"></tbody>
                    </table>
                </div>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <p id="researchResultMeta" class="text-sm text-slate-500"></p>
                    <div id="researchPager" class="pager"></div>
                </div>
            @endif
        </section>

        <section class="space-y-6">
            <article class="person-panel p-5 md:p-6">
                <div class="section-toolbar mb-4">
                    <div>
                        <h2 class="section-heading presentation text-xl font-bold text-slate-900">Presentation</h2>
                        <p class="text-sm text-slate-500">รายการนำเสนอ</p>
                    </div>
                    <div class="section-search-row">
                        <input id="presentationSearchInput" type="text" placeholder="ค้นหาชื่อผลงาน / conference / ประเภท / ที่มา" class="live-search">
                    </div>
                </div>
                <div class="section-filter-row">
                    <select id="presentationYearFilter" class="filter-select">
                        <option value="">ทุกปี</option>
                    </select>
                    <select id="presentationTypeFilter" class="filter-select">
                        <option value="">ทุกประเภท</option>
                    </select>
                    <select id="presentationOriginFilter" class="filter-select">
                        <option value="">ทุกที่มา</option>
                    </select>
                    <select id="presentationSortSelect" class="filter-select">
                        <option value="year_desc">เรียงปีล่าสุด</option>
                        <option value="year_asc">เรียงปีเก่าสุด</option>
                        <option value="title_asc">ชื่อ A-Z</option>
                        <option value="conference_asc">Conference A-Z</option>
                    </select>
                </div>

                @if ($presentationRows->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-slate-500">
                        ไม่มี presentation
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="section-table text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">ชื่อผลงาน</th>
                                    <th class="px-4 py-3 text-left font-semibold">Conference</th>
                                    <th class="px-4 py-3 text-left font-semibold">ประเภท</th>
                                    <th class="px-4 py-3 text-left font-semibold">ปี</th>
                                    <th class="px-4 py-3 text-left font-semibold">โครงการ / skill ส่วนบุคคล</th>
                                </tr>
                            </thead>
                            <tbody id="presentationTableBody"></tbody>
                        </table>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <p id="presentationResultMeta" class="text-sm text-slate-500"></p>
                        <div id="presentationPager" class="pager"></div>
                    </div>
                @endif
            </article>

            <article class="person-panel p-5 md:p-6">
                <div class="section-toolbar mb-4">
                    <div>
                        <h2 class="section-heading textbook text-xl font-bold text-slate-900">Textbook</h2>
                        <p class="text-sm text-slate-500">หนังสือและบทในหนังสือ</p>
                    </div>
                    <div class="section-search-row">
                        <input id="textbookSearchInput" type="text" placeholder="ค้นหาหนังสือ / บท / ผู้จัดพิมพ์ / ที่มา" class="live-search">
                    </div>
                </div>
                <div class="section-filter-row">
                    <select id="textbookYearFilter" class="filter-select">
                        <option value="">ทุกปี</option>
                    </select>
                    <select id="textbookOriginFilter" class="filter-select">
                        <option value="">ทุกที่มา</option>
                    </select>
                    <select id="textbookSortSelect" class="filter-select">
                        <option value="year_desc">เรียงปีล่าสุด</option>
                        <option value="year_asc">เรียงปีเก่าสุด</option>
                        <option value="title_asc">ชื่อ A-Z</option>
                        <option value="publisher_asc">ผู้จัดพิมพ์ A-Z</option>
                    </select>
                </div>

                @if ($textbookRows->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-slate-500">
                        ไม่มี textbook
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="section-table text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">หนังสือ / บทในหนังสือ</th>
                                    <th class="px-4 py-3 text-left font-semibold">ผู้จัดพิมพ์</th>
                                    <th class="px-4 py-3 text-left font-semibold">ปี</th>
                                    <th class="px-4 py-3 text-left font-semibold">โครงการ / skill ส่วนบุคคล</th>
                                </tr>
                            </thead>
                            <tbody id="textbookTableBody"></tbody>
                        </table>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <p id="textbookResultMeta" class="text-sm text-slate-500"></p>
                        <div id="textbookPager" class="pager"></div>
                    </div>
                @endif
            </article>

            <article class="person-panel p-5 md:p-6">
                <div class="section-toolbar mb-4">
                    <div>
                        <h2 class="section-heading award text-xl font-bold text-slate-900">Award</h2>
                        <p class="text-sm text-slate-500">รางวัลและการยกย่อง</p>
                    </div>
                    <div class="section-search-row">
                        <input id="awardSearchInput" type="text" placeholder="ค้นหารางวัล / ผู้ได้รับ / รายละเอียด / ที่มา" class="live-search">
                    </div>
                </div>
                <div class="section-filter-row">
                    <select id="awardYearFilter" class="filter-select">
                        <option value="">ทุกปี</option>
                    </select>
                    <select id="awardOriginFilter" class="filter-select">
                        <option value="">ทุกที่มา</option>
                    </select>
                    <select id="awardSortSelect" class="filter-select">
                        <option value="year_desc">เรียงปีล่าสุด</option>
                        <option value="year_asc">เรียงปีเก่าสุด</option>
                        <option value="title_asc">ชื่อ A-Z</option>
                    </select>
                </div>

                @if ($awardRows->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-slate-500">
                        ไม่มี award
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="section-table text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">รางวัล</th>
                                    <th class="px-4 py-3 text-left font-semibold">ผู้ได้รับ</th>
                                    <th class="px-4 py-3 text-left font-semibold">ปี</th>
                                    <th class="px-4 py-3 text-left font-semibold">โครงการ / skill ส่วนบุคคล</th>
                                </tr>
                            </thead>
                            <tbody id="awardTableBody"></tbody>
                        </table>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <p id="awardResultMeta" class="text-sm text-slate-500"></p>
                        <div id="awardPager" class="pager"></div>
                    </div>
                @endif
            </article>
        </section>

        @php
            $projectRowsJson = $projectRows->map(fn ($row) => [
                'project_name' => $row->project_name,
                'project_code' => $row->project_code,
                'year_be' => $row->year_be,
                'execution_status' => $row->execution_status,
                'roles' => array_values($row->roles ?? []),
                'link' => $row->link,
            ])->values();

            $researchRowsJson = $researchRows->map(fn ($row) => [
                'title' => $row->title,
                'author_line' => $row->author_line,
                'journal' => $row->journal,
                'impact_factor' => $row->impact_factor,
                'quartile' => $row->quartile,
                'year_be' => $row->year_be,
                'source_label' => $row->source_label,
                'roles' => $row->roles instanceof \Illuminate\Support\Collection ? $row->roles->values()->all() : array_values($row->roles ?? []),
                'reference_label' => $row->reference_label,
                'link' => $row->link,
            ])->values();

            $presentationRowsJson = $presentationRows->map(fn ($row) => [
                'title' => $row->title,
                'conference_name' => $row->conference_name,
                'location' => $row->location,
                'presentation_type' => $row->presentation_type,
                'year_be' => $row->year_be,
                'source_label' => $row->source_label,
                'reference_label' => $row->reference_label,
                'link' => $row->link,
            ])->values();

            $textbookRowsJson = $textbookRows->map(fn ($row) => [
                'book_title' => $row->book_title,
                'title' => $row->title,
                'publisher_name' => $row->publisher_name,
                'chapter_pages' => $row->chapter_pages,
                'year_be' => $row->year_be,
                'source_label' => $row->source_label,
                'reference_label' => $row->reference_label,
                'link' => $row->link,
            ])->values();

            $awardRowsJson = $awardRows->map(fn ($row) => [
                'title' => $row->title,
                'note' => $row->note,
                'recipient_name' => $row->recipient_name,
                'year_be' => $row->year_be,
                'source_label' => $row->source_label,
                'reference_label' => $row->reference_label,
                'link' => $row->link,
            ])->values();
        @endphp

        <script>
            (() => {
                const projectRows = @json($projectRowsJson);
                const researchRows = @json($researchRowsJson);
                const presentationRows = @json($presentationRowsJson);
                const textbookRows = @json($textbookRowsJson);
                const awardRows = @json($awardRowsJson);
                const perPage = 8;

                const normalize = (value) => String(value || '').toLowerCase().trim();
                const roleClass = (role) => {
                    const key = normalize(role);
                    if (key === 'pi') return 'pi';
                    if (key === 'co-pi') return 'co-pi';
                    if (key === 'co-researcher') return 'co-researcher';
                    if (key === 'first author') return 'first-author';
                    if (key === 'co-author') return 'co-author';
                    if (key === 'corresponding author') return 'corresponding-author';
                    return '';
                };
                const sourceClass = (label) => normalize(label) === 'extra' ? 'source-extra' : 'source-project';
                const originLabel = (label) => normalize(label) === 'extra' ? 'skill ส่วนบุคคล' : 'โครงการ';
                const statusClass = (status) => {
                    const value = normalize(status);
                    if (value.includes('ปิด') || value.includes('เสร็จ') || value.includes('เสร็จสิ้น')) return 'is-complete';
                    if (value.includes('วิเคราะห์') || value.includes('รายงาน') || value.includes('publication')) return 'is-analysis';
                    if (value.includes('ดำเนิน') || value.includes('เก็บข้อมูล') || value.includes('ติดตาม')) return 'is-active';
                    return '';
                };
                const uniqueSorted = (items, direction = 'asc') => {
                    const values = [...new Set(items.filter(Boolean))];
                    values.sort((a, b) => String(a).localeCompare(String(b), 'th', { numeric: true }));
                    return direction === 'desc' ? values.reverse() : values;
                };
                const fillSelect = (select, values, placeholder) => {
                    if (!select) return;
                    select.innerHTML = `<option value="">${placeholder}</option>` + values.map((value) => {
                        return `<option value="${String(value).replace(/"/g, '&quot;')}">${value}</option>`;
                    }).join('');
                };

                function renderPager(container, page, totalPages, onChange) {
                    container.innerHTML = '';
                    if (totalPages <= 1) return;

                    const makeButton = (label, targetPage, active = false, disabled = false) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.textContent = label;
                        if (active) button.classList.add('is-active');
                        if (disabled) button.disabled = true;
                        button.addEventListener('click', () => onChange(targetPage));
                        container.appendChild(button);
                    };

                    const makeEllipsis = () => {
                        const span = document.createElement('span');
                        span.className = 'pager-ellipsis';
                        span.textContent = '...';
                        container.appendChild(span);
                    };

                    makeButton('‹', Math.max(1, page - 1), false, page === 1);

                    const pages = new Set([1, totalPages, page - 1, page, page + 1]);
                    const visiblePages = [...pages]
                        .filter((p) => p >= 1 && p <= totalPages)
                        .sort((a, b) => a - b);

                    let previousPage = null;
                    for (const p of visiblePages) {
                        if (previousPage !== null && p - previousPage > 1) {
                            makeEllipsis();
                        }
                        makeButton(String(p), p, p === page);
                        previousPage = p;
                    }

                    makeButton('›', Math.min(totalPages, page + 1), false, page === totalPages);
                }

                function setupProjectsTable() {
                    const input = document.getElementById('projectSearchInput');
                    const body = document.getElementById('projectTableBody');
                    const meta = document.getElementById('projectResultMeta');
                    const pager = document.getElementById('projectPager');
                    const section = input.closest('.person-panel');
                    const yearFilter = document.getElementById('projectYearFilter');
                    const statusFilter = document.getElementById('projectStatusFilter');
                    const roleFilter = document.getElementById('projectRoleFilter');
                    const sortSelect = document.getElementById('projectSortSelect');
                    if (!input || !body || !meta || !pager || !yearFilter || !statusFilter || !roleFilter || !sortSelect) return;

                    let page = 1;
                    fillSelect(yearFilter, uniqueSorted(projectRows.map((row) => row.year_be).filter((value) => value !== null && value !== ''), 'desc'), 'ทุกปี');
                    fillSelect(statusFilter, uniqueSorted(projectRows.map((row) => row.execution_status)), 'ทุกสถานะ');
                    fillSelect(roleFilter, uniqueSorted(projectRows.flatMap((row) => row.roles || [])), 'ทุกบทบาท');

                    const render = () => {
                        const query = normalize(input.value);
                        const filtered = projectRows.filter((row) => {
                            if (!query) return true;
                            return normalize([
                                row.project_name,
                                row.project_code,
                                row.year_be,
                                row.execution_status,
                                ...(row.roles || []),
                            ].join(' ')).includes(query);
                        }).filter((row) => !yearFilter.value || String(row.year_be || '') === yearFilter.value)
                            .filter((row) => !statusFilter.value || String(row.execution_status || '') === statusFilter.value)
                            .filter((row) => !roleFilter.value || (row.roles || []).includes(roleFilter.value));

                        const sorted = [...filtered].sort((a, b) => {
                            switch (sortSelect.value) {
                                case 'year_asc':
                                    return (Number(a.year_be) || 0) - (Number(b.year_be) || 0);
                                case 'name_asc':
                                    return String(a.project_name || '').localeCompare(String(b.project_name || ''), 'th');
                                case 'name_desc':
                                    return String(b.project_name || '').localeCompare(String(a.project_name || ''), 'th');
                                case 'year_desc':
                                default:
                                    return (Number(b.year_be) || 0) - (Number(a.year_be) || 0);
                            }
                        });

                        const totalPages = Math.max(1, Math.ceil(sorted.length / perPage));
                        if (page > totalPages) page = totalPages;
                        const start = (page - 1) * perPage;
                        const current = sorted.slice(start, start + perPage);

                        body.innerHTML = current.map((row) => `
                            <tr class="border-t border-slate-200">
                                <td class="px-4 py-4" data-label="โครงการ">
                                    <div class="font-semibold text-slate-900">
                                        ${row.link
                                            ? `<a href="${row.link}" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-900 no-underline hover:text-slate-900">${row.project_name || '-'}</a>`
                                            : (row.project_name || '-')}
                                    </div>
                                    ${row.project_code ? `<div class="mt-1 text-xs font-normal text-slate-400">${row.project_code}</div>` : ''}
                                </td>
                                <td class="px-4 py-4 text-slate-600" data-label="ปี">${row.year_be || '-'}</td>
                                <td class="px-4 py-4 text-slate-600" data-label="สถานะ">
                                    <span class="status-badge ${statusClass(row.execution_status)}">${row.execution_status || '-'}</span>
                                </td>
                                <td class="px-4 py-4" data-label="บทบาท">
                                    <div class="flex flex-wrap gap-2">
                                        ${(row.roles || []).map((role) => `<span class="role-badge ${roleClass(role)}">${role}</span>`).join('')}
                                    </div>
                                </td>
                            </tr>
                        `).join('');

                        if (current.length === 0) {
                            body.innerHTML = `<tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">ไม่พบโครงการที่ตรงกับคำค้น</td></tr>`;
                        }

                        meta.textContent = `แสดง ${sorted.length === 0 ? 0 : start + 1}-${Math.min(start + current.length, sorted.length)} จาก ${sorted.length} รายการ`;
                        renderPager(pager, page, totalPages, (nextPage) => {
                            page = nextPage;
                            render();
                            section?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    };

                    input.addEventListener('input', () => {
                        page = 1;
                        render();
                    });
                    [yearFilter, statusFilter, roleFilter, sortSelect].forEach((element) => {
                        element.addEventListener('change', () => {
                            page = 1;
                            render();
                        });
                    });

                    render();
                }

                function setupResearchTable() {
                    const input = document.getElementById('researchSearchInput');
                    const body = document.getElementById('researchTableBody');
                    const meta = document.getElementById('researchResultMeta');
                    const pager = document.getElementById('researchPager');
                    const section = input.closest('.person-panel');
                    const yearFilter = document.getElementById('researchYearFilter');
                    const journalFilter = document.getElementById('researchJournalFilter');
                    const quartileFilter = document.getElementById('researchQuartileFilter');
                    const roleFilter = document.getElementById('researchRoleFilter');
                    const originFilter = document.getElementById('researchOriginFilter');
                    const sortSelect = document.getElementById('researchSortSelect');
                    if (!input || !body || !meta || !pager || !yearFilter || !journalFilter || !quartileFilter || !roleFilter || !originFilter || !sortSelect) return;

                    let page = 1;
                    fillSelect(yearFilter, uniqueSorted(researchRows.map((row) => row.year_be).filter((value) => value !== null && value !== ''), 'desc'), 'ทุกปี');
                    fillSelect(journalFilter, uniqueSorted(researchRows.map((row) => row.journal).filter((value) => value && value !== '-')), 'ทุก Journal');
                    fillSelect(quartileFilter, uniqueSorted(researchRows.map((row) => row.quartile).filter((value) => value && value !== '-')), 'ทุก Quartile');
                    fillSelect(roleFilter, uniqueSorted(researchRows.flatMap((row) => row.roles || [])), 'ทุกบทบาท');
                    fillSelect(originFilter, ['โครงการ', 'skill ส่วนบุคคล'], 'ทุกที่มา');

                    const render = () => {
                        const query = normalize(input.value);
                        const filtered = researchRows.filter((row) => {
                            if (!query) return true;
                            return normalize([
                                row.title,
                                row.author_line,
                                row.journal,
                                row.impact_factor,
                                row.quartile,
                                row.year_be,
                                row.source_label,
                                row.reference_label,
                                ...(row.roles || []),
                            ].join(' ')).includes(query);
                        }).filter((row) => !yearFilter.value || String(row.year_be || '') === yearFilter.value)
                            .filter((row) => !journalFilter.value || String(row.journal || '') === journalFilter.value)
                            .filter((row) => !quartileFilter.value || String(row.quartile || '') === quartileFilter.value)
                            .filter((row) => !roleFilter.value || (row.roles || []).includes(roleFilter.value))
                            .filter((row) => !originFilter.value || originLabel(row.source_label) === originFilter.value);

                        const sorted = [...filtered].sort((a, b) => {
                            switch (sortSelect.value) {
                                case 'year_asc':
                                    return (Number(a.year_be) || 0) - (Number(b.year_be) || 0);
                                case 'title_asc':
                                    return String(a.title || '').localeCompare(String(b.title || ''), 'th');
                                case 'if_desc':
                                    return (Number(b.impact_factor) || -1) - (Number(a.impact_factor) || -1);
                                case 'year_desc':
                                default:
                                    return (Number(b.year_be) || 0) - (Number(a.year_be) || 0);
                            }
                        });

                        const totalPages = Math.max(1, Math.ceil(sorted.length / perPage));
                        if (page > totalPages) page = totalPages;
                        const start = (page - 1) * perPage;
                        const current = sorted.slice(start, start + perPage);

                        body.innerHTML = current.map((row) => `
                            <tr class="border-t border-slate-200">
                                <td class="px-4 py-4" data-label="ชื่อผลงาน">
                                    ${row.link
                                        ? `<a href="${row.link}" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-900 no-underline hover:text-slate-900">${row.title || '-'}</a>`
                                        : `<p class="font-semibold text-slate-900">${row.title || '-'}</p>`}
                                    ${row.author_line ? `<p class="mt-1 text-xs leading-5 text-slate-500">${row.author_line}</p>` : ''}
                                </td>
                                <td class="px-4 py-4 text-slate-600" data-label="Journal">${row.journal || '-'}</td>
                                <td class="px-4 py-4 text-slate-600" data-label="IF / Quartile">
                                    IF ${row.impact_factor ?? '-'}
                                    <div class="mt-1 text-xs text-slate-500">${row.quartile || '-'}</div>
                                </td>
                                <td class="px-4 py-4 text-slate-600" data-label="ปี">${row.year_be || '-'}</td>
                                <td class="px-4 py-4" data-label="บทบาท">
                                    <div class="flex flex-wrap gap-2">
                                        ${(row.roles || []).map((role) => `<span class="role-badge ${roleClass(role)}">${role}</span>`).join('')}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-slate-600" data-label="โครงการ / skill ส่วนบุคคล">
                                    <div class="flex flex-wrap items-start gap-2">
                                        <span class="role-badge ${sourceClass(row.source_label)}">${originLabel(row.source_label)}</span>
                                        <span class="leading-6">${row.reference_label || '-'}</span>
                                    </div>
                                </td>
                            </tr>
                        `).join('');

                        if (current.length === 0) {
                            body.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">ไม่พบ research paper ที่ตรงกับคำค้น</td></tr>`;
                        }

                        meta.textContent = `แสดง ${sorted.length === 0 ? 0 : start + 1}-${Math.min(start + current.length, sorted.length)} จาก ${sorted.length} รายการ`;
                        renderPager(pager, page, totalPages, (nextPage) => {
                            page = nextPage;
                            render();
                            section?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    };

                    input.addEventListener('input', () => {
                        page = 1;
                        render();
                    });
                    [yearFilter, journalFilter, quartileFilter, roleFilter, originFilter, sortSelect].forEach((element) => {
                        element.addEventListener('change', () => {
                            page = 1;
                            render();
                        });
                    });

                    render();
                }

                function setupPresentationTable() {
                    const input = document.getElementById('presentationSearchInput');
                    const body = document.getElementById('presentationTableBody');
                    const meta = document.getElementById('presentationResultMeta');
                    const pager = document.getElementById('presentationPager');
                    const section = input?.closest('.person-panel');
                    const yearFilter = document.getElementById('presentationYearFilter');
                    const typeFilter = document.getElementById('presentationTypeFilter');
                    const originFilter = document.getElementById('presentationOriginFilter');
                    const sortSelect = document.getElementById('presentationSortSelect');
                    if (!input || !body || !meta || !pager || !yearFilter || !typeFilter || !originFilter || !sortSelect) return;

                    let page = 1;
                    fillSelect(yearFilter, uniqueSorted(presentationRows.map((row) => row.year_be).filter((value) => value !== null && value !== ''), 'desc'), 'ทุกปี');
                    fillSelect(typeFilter, uniqueSorted(presentationRows.map((row) => row.presentation_type).filter((value) => value && value !== '-')), 'ทุกประเภท');
                    fillSelect(originFilter, ['โครงการ', 'skill ส่วนบุคคล'], 'ทุกที่มา');

                    const render = () => {
                        const query = normalize(input.value);
                        const filtered = presentationRows.filter((row) => {
                            if (!query) return true;
                            return normalize([
                                row.title,
                                row.conference_name,
                                row.location,
                                row.presentation_type,
                                row.year_be,
                                row.source_label,
                                row.reference_label,
                            ].join(' ')).includes(query);
                        }).filter((row) => !yearFilter.value || String(row.year_be || '') === yearFilter.value)
                            .filter((row) => !typeFilter.value || String(row.presentation_type || '') === typeFilter.value)
                            .filter((row) => !originFilter.value || originLabel(row.source_label) === originFilter.value);

                        const sorted = [...filtered].sort((a, b) => {
                            switch (sortSelect.value) {
                                case 'year_asc':
                                    return (Number(a.year_be) || 0) - (Number(b.year_be) || 0);
                                case 'title_asc':
                                    return String(a.title || '').localeCompare(String(b.title || ''), 'th');
                                case 'conference_asc':
                                    return String(a.conference_name || '').localeCompare(String(b.conference_name || ''), 'th');
                                case 'year_desc':
                                default:
                                    return (Number(b.year_be) || 0) - (Number(a.year_be) || 0);
                            }
                        });

                        const totalPages = Math.max(1, Math.ceil(sorted.length / perPage));
                        if (page > totalPages) page = totalPages;
                        const start = (page - 1) * perPage;
                        const current = sorted.slice(start, start + perPage);

                        body.innerHTML = current.map((row) => `
                            <tr class="border-t border-slate-200">
                                <td class="px-4 py-4" data-label="ชื่อผลงาน">
                                    ${row.link
                                        ? `<a href="${row.link}" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-900 no-underline hover:text-slate-900">${row.title || '-'}</a>`
                                        : `<p class="font-semibold text-slate-900">${row.title || '-'}</p>`}
                                </td>
                                <td class="px-4 py-4 text-slate-600" data-label="Conference">
                                    <div>${row.conference_name || '-'}</div>
                                    ${row.location ? `<div class="mt-1 text-xs text-slate-500">${row.location}</div>` : ''}
                                </td>
                                <td class="px-4 py-4 text-slate-600" data-label="ประเภท">${row.presentation_type || '-'}</td>
                                <td class="px-4 py-4 text-slate-600" data-label="ปี">${row.year_be || '-'}</td>
                                <td class="px-4 py-4 text-slate-600" data-label="โครงการ / skill ส่วนบุคคล">
                                    <div class="flex flex-wrap items-start gap-2">
                                        <span class="role-badge ${sourceClass(row.source_label)}">${originLabel(row.source_label)}</span>
                                        <span class="leading-6">${row.reference_label || '-'}</span>
                                    </div>
                                </td>
                            </tr>
                        `).join('');

                        if (current.length === 0) {
                            body.innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">ไม่พบ presentation ที่ตรงกับคำค้น</td></tr>`;
                        }

                        meta.textContent = `แสดง ${sorted.length === 0 ? 0 : start + 1}-${Math.min(start + current.length, sorted.length)} จาก ${sorted.length} รายการ`;
                        renderPager(pager, page, totalPages, (nextPage) => {
                            page = nextPage;
                            render();
                            section?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    };

                    input.addEventListener('input', () => {
                        page = 1;
                        render();
                    });
                    [yearFilter, typeFilter, originFilter, sortSelect].forEach((element) => {
                        element.addEventListener('change', () => {
                            page = 1;
                            render();
                        });
                    });

                    render();
                }

                function setupTextbookTable() {
                    const input = document.getElementById('textbookSearchInput');
                    const body = document.getElementById('textbookTableBody');
                    const meta = document.getElementById('textbookResultMeta');
                    const pager = document.getElementById('textbookPager');
                    const section = input?.closest('.person-panel');
                    const yearFilter = document.getElementById('textbookYearFilter');
                    const originFilter = document.getElementById('textbookOriginFilter');
                    const sortSelect = document.getElementById('textbookSortSelect');
                    if (!input || !body || !meta || !pager || !yearFilter || !originFilter || !sortSelect) return;

                    let page = 1;
                    fillSelect(yearFilter, uniqueSorted(textbookRows.map((row) => row.year_be).filter((value) => value !== null && value !== ''), 'desc'), 'ทุกปี');
                    fillSelect(originFilter, ['โครงการ', 'skill ส่วนบุคคล'], 'ทุกที่มา');

                    const render = () => {
                        const query = normalize(input.value);
                        const filtered = textbookRows.filter((row) => {
                            if (!query) return true;
                            return normalize([
                                row.book_title,
                                row.title,
                                row.publisher_name,
                                row.chapter_pages,
                                row.year_be,
                                row.source_label,
                                row.reference_label,
                            ].join(' ')).includes(query);
                        }).filter((row) => !yearFilter.value || String(row.year_be || '') === yearFilter.value)
                            .filter((row) => !originFilter.value || originLabel(row.source_label) === originFilter.value);

                        const sorted = [...filtered].sort((a, b) => {
                            switch (sortSelect.value) {
                                case 'year_asc':
                                    return (Number(a.year_be) || 0) - (Number(b.year_be) || 0);
                                case 'title_asc':
                                    return String(a.book_title || '').localeCompare(String(b.book_title || ''), 'th');
                                case 'publisher_asc':
                                    return String(a.publisher_name || '').localeCompare(String(b.publisher_name || ''), 'th');
                                case 'year_desc':
                                default:
                                    return (Number(b.year_be) || 0) - (Number(a.year_be) || 0);
                            }
                        });

                        const totalPages = Math.max(1, Math.ceil(sorted.length / perPage));
                        if (page > totalPages) page = totalPages;
                        const start = (page - 1) * perPage;
                        const current = sorted.slice(start, start + perPage);

                        body.innerHTML = current.map((row) => `
                            <tr class="border-t border-slate-200">
                                <td class="px-4 py-4" data-label="หนังสือ / บทในหนังสือ">
                                    ${row.link
                                        ? `<a href="${row.link}" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-900 no-underline hover:text-slate-900">${row.book_title || '-'}</a>`
                                        : `<p class="font-semibold text-slate-900">${row.book_title || '-'}</p>`}
                                    ${row.title ? `<div class="mt-1 text-sm text-slate-600">${row.title}</div>` : ''}
                                    ${row.chapter_pages ? `<div class="mt-1 text-xs text-slate-500">หน้า ${row.chapter_pages}</div>` : ''}
                                </td>
                                <td class="px-4 py-4 text-slate-600" data-label="ผู้จัดพิมพ์">
                                    <div>${row.publisher_name || '-'}</div>
                                    ${row.reference_label ? `<div class="mt-1 text-xs leading-5 text-slate-500">${row.reference_label}</div>` : ''}
                                </td>
                                <td class="px-4 py-4 text-slate-600" data-label="ปี">${row.year_be || '-'}</td>
                                <td class="px-4 py-4 text-slate-600" data-label="โครงการ / skill ส่วนบุคคล">
                                    <span class="role-badge ${sourceClass(row.source_label)}">${originLabel(row.source_label)}</span>
                                </td>
                            </tr>
                        `).join('');

                        if (current.length === 0) {
                            body.innerHTML = `<tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">ไม่พบ textbook ที่ตรงกับคำค้น</td></tr>`;
                        }

                        meta.textContent = `แสดง ${sorted.length === 0 ? 0 : start + 1}-${Math.min(start + current.length, sorted.length)} จาก ${sorted.length} รายการ`;
                        renderPager(pager, page, totalPages, (nextPage) => {
                            page = nextPage;
                            render();
                            section?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    };

                    input.addEventListener('input', () => {
                        page = 1;
                        render();
                    });
                    [yearFilter, originFilter, sortSelect].forEach((element) => {
                        element.addEventListener('change', () => {
                            page = 1;
                            render();
                        });
                    });

                    render();
                }

                function setupAwardTable() {
                    const input = document.getElementById('awardSearchInput');
                    const body = document.getElementById('awardTableBody');
                    const meta = document.getElementById('awardResultMeta');
                    const pager = document.getElementById('awardPager');
                    const section = input?.closest('.person-panel');
                    const yearFilter = document.getElementById('awardYearFilter');
                    const originFilter = document.getElementById('awardOriginFilter');
                    const sortSelect = document.getElementById('awardSortSelect');
                    if (!input || !body || !meta || !pager || !yearFilter || !originFilter || !sortSelect) return;

                    let page = 1;
                    fillSelect(yearFilter, uniqueSorted(awardRows.map((row) => row.year_be).filter((value) => value !== null && value !== ''), 'desc'), 'ทุกปี');
                    fillSelect(originFilter, ['โครงการ', 'skill ส่วนบุคคล'], 'ทุกที่มา');

                    const render = () => {
                        const query = normalize(input.value);
                        const filtered = awardRows.filter((row) => {
                            if (!query) return true;
                            return normalize([
                                row.title,
                                row.note,
                                row.recipient_name,
                                row.year_be,
                                row.source_label,
                                row.reference_label,
                            ].join(' ')).includes(query);
                        }).filter((row) => !yearFilter.value || String(row.year_be || '') === yearFilter.value)
                            .filter((row) => !originFilter.value || originLabel(row.source_label) === originFilter.value);

                        const sorted = [...filtered].sort((a, b) => {
                            switch (sortSelect.value) {
                                case 'year_asc':
                                    return (Number(a.year_be) || 0) - (Number(b.year_be) || 0);
                                case 'title_asc':
                                    return String(a.title || '').localeCompare(String(b.title || ''), 'th');
                                case 'year_desc':
                                default:
                                    return (Number(b.year_be) || 0) - (Number(a.year_be) || 0);
                            }
                        });

                        const totalPages = Math.max(1, Math.ceil(sorted.length / perPage));
                        if (page > totalPages) page = totalPages;
                        const start = (page - 1) * perPage;
                        const current = sorted.slice(start, start + perPage);

                        body.innerHTML = current.map((row) => `
                            <tr class="border-t border-slate-200">
                                <td class="px-4 py-4" data-label="รางวัล">
                                    ${row.link
                                        ? `<a href="${row.link}" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-900 no-underline hover:text-slate-900">${row.title || '-'}</a>`
                                        : `<p class="font-semibold text-slate-900">${row.title || '-'}</p>`}
                                    ${row.note ? `<div class="mt-1 text-sm leading-6 text-slate-500">${row.note}</div>` : ''}
                                </td>
                                <td class="px-4 py-4 text-slate-600" data-label="ผู้ได้รับ">
                                    <div>${row.recipient_name || '-'}</div>
                                    ${row.reference_label ? `<div class="mt-1 text-xs leading-5 text-slate-500">${row.reference_label}</div>` : ''}
                                </td>
                                <td class="px-4 py-4 text-slate-600" data-label="ปี">${row.year_be || '-'}</td>
                                <td class="px-4 py-4 text-slate-600" data-label="โครงการ / skill ส่วนบุคคล">
                                    <span class="role-badge ${sourceClass(row.source_label)}">${originLabel(row.source_label)}</span>
                                </td>
                            </tr>
                        `).join('');

                        if (current.length === 0) {
                            body.innerHTML = `<tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">ไม่พบ award ที่ตรงกับคำค้น</td></tr>`;
                        }

                        meta.textContent = `แสดง ${sorted.length === 0 ? 0 : start + 1}-${Math.min(start + current.length, sorted.length)} จาก ${sorted.length} รายการ`;
                        renderPager(pager, page, totalPages, (nextPage) => {
                            page = nextPage;
                            render();
                            section?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    };

                    input.addEventListener('input', () => {
                        page = 1;
                        render();
                    });
                    [yearFilter, originFilter, sortSelect].forEach((element) => {
                        element.addEventListener('change', () => {
                            page = 1;
                            render();
                        });
                    });

                    render();
                }

                setupProjectsTable();
                setupResearchTable();
                setupPresentationTable();
                setupTextbookTable();
                setupAwardTable();
            })();
        </script>
    </div>
@endsection

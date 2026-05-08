<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Outputs Visualization Report</title>
    <style>
        @page {
            header: html_Header;
            footer: html_Footer;
            margin-top: 86px;
            margin-bottom: 62px;
            margin-left: 24px;
            margin-right: 24px;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 12.8pt;
            line-height: 1.45;
            color: #1e293b;
        }
        .header-line { border-bottom: 1px solid #dbe7f5; padding-bottom: 6px; }
        .report-kicker { font-size: 11pt; letter-spacing: .12em; text-transform: uppercase; color: #64748b; }
        .report-title { margin-top: 6px; font-size: 23pt; font-weight: bold; line-height: 1.16; color: #0f172a; }
        .report-subtitle { margin-top: 6px; font-size: 12.4pt; color: #475569; line-height: 1.45; }
        .hero-shell {
            margin-top: 12px;
            border: 1px solid #dbe7f5;
            border-radius: 18px;
            background: #f8fbff;
            padding: 16px 18px;
        }
        .chip {
            display: inline-block;
            margin-right: 6px;
            margin-bottom: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #dbe7f5;
            background: #f8fafc;
            font-size: 10.8pt;
            color: #334155;
        }
        .section-title {
            margin: 18px 0 8px;
            padding: 8px 14px 8px 12px;
            border-radius: 10px;
            border: 1px solid #dbe7f5;
            border-left: 5px solid #0f766e;
            background: #ecfeff;
            color: #0f172a;
            font-size: 15.2pt;
            font-weight: bold;
            line-height: 1.2;
        }
        .section-subtitle { margin: -2px 0 10px; color: #64748b; font-size: 12pt; }
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0 0 14px;
        }
        .summary-grid td {
            border: 1px solid #dbe7f5;
            padding: 10px 12px;
            vertical-align: top;
        }
        .summary-label { color: #64748b; font-size: 11.2pt; font-weight: bold; }
        .summary-value { margin-top: 8px; font-size: 18pt; font-weight: bold; color: #0f172a; line-height: 1.18; }
        .summary-note { margin-top: 6px; color: #64748b; font-size: 11.2pt; line-height: 1.35; }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0 0 12px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #dbe7f5;
            padding: 7px 8px;
            vertical-align: top;
            line-height: 1.35;
        }
        .data-table th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
        }
        .muted { color: #64748b; }
        .small { font-size: 11.2pt; }
        .callout {
            border: 1px solid #dbe7f5;
            border-radius: 12px;
            background: #fbfdff;
            padding: 10px 12px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .callout-title { font-weight: bold; color: #0f172a; }
        .two-col {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-left: -10px;
            margin-right: -10px;
        }
        .two-col td {
            width: 50%;
            vertical-align: top;
            border: 0;
            padding: 0;
        }
        .bar-card {
            border: 1px solid #dbe7f5;
            border-radius: 12px;
            background: #ffffff;
            padding: 10px 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .bar-title {
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .bar-row {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .bar-row td {
            border: 0;
            padding: 2px 0;
            vertical-align: middle;
        }
        .bar-label {
            width: 33%;
            padding-right: 8px !important;
            color: #334155;
            font-size: 11.6pt;
            line-height: 1.25;
        }
        .bar-track {
            height: 10px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }
        .bar-fill {
            height: 10px;
            border-radius: 999px;
            background: #0f766e;
        }
        .bar-fill.blue { background: #2563eb; }
        .bar-fill.violet { background: #7c3aed; }
        .bar-fill.orange { background: #ea580c; }
        .bar-fill.rose { background: #e11d48; }
        .bar-value {
            width: 13%;
            text-align: right;
            font-weight: bold;
            color: #0f172a;
            font-size: 11.6pt;
        }
    </style>
</head>
<body>
    @php
        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F') . ' ' . ($generatedAt->year + 543) . $generatedAt->translatedFormat(' เวลา H:i');
        $headerLogoPath = public_path('images/sihp-logo.png');
        $headerLogo = file_exists($headerLogoPath) ? 'file://' . $headerLogoPath : null;
        $rows = collect($rows ?? []);
        $sourceCounts = collect($sourceCounts ?? []);
        $typeCounts = collect($typeCounts ?? []);
        $yearTotals = collect($yearTotals ?? []);
        $projectTypeSummary = collect($projectTypeSummary ?? []);
        $policyImpact = $policyImpact ?? [];
        $impactOutputs = collect($policyImpact['impact_outputs'] ?? []);
        $impactTrendYears = collect(data_get($policyImpact, 'impact_trend.years', []));
        $impactTrendSeries = collect(data_get($policyImpact, 'impact_trend.series', []));
        $dataSourceCounts = collect($policyImpact['data_source_counts'] ?? []);
        $dataSourceTrendYears = collect(data_get($policyImpact, 'data_source_trend.years', []));
        $dataSourceTrendSeries = collect(data_get($policyImpact, 'data_source_trend.series', []));
        $themeAnalysis = collect($policyImpact['impact_theme_analysis'] ?? []);
        $unitRoleTables = collect($policyImpact['unit_contribution_role_theme_tables'] ?? []);
        $paperCount = collect(data_get($paperSummary ?? [], 'all', []))->count();
        $presentationCount = collect(data_get($presentationSummary ?? [], 'all', []))->count();
        $paperRankingLabels = collect($paperRankingLabels ?? []);
        $paperRankingValues = collect($paperRankingValues ?? []);
        $presentationActivityLabels = collect($presentationActivityLabels ?? []);
        $presentationActivityValues = collect($presentationActivityValues ?? []);
        $textbookYearLabels = collect($textbookYearLabels ?? []);
        $textbookYearValues = collect($textbookYearValues ?? []);
        $networkingYearLabels = collect($networkingYearLabels ?? []);
        $networkingYearSeries = collect($networkingYearSeries ?? []);
        $yearTotalMax = max(1, (int) $yearTotals->max());
        $sourceMax = max(1, (int) $sourceCounts->max());
        $typeMax = max(1, (int) $typeCounts->max());
        $projectTypeMax = max(1, (int) $projectTypeSummary->max(fn ($row) => (int) ($row->count ?? 0)));
        $impactMax = max(1, (int) $impactOutputs->max());
        $dataSourceMax = max(1, (int) $dataSourceCounts->max());
        $paperRankingMax = max(1, (int) $paperRankingValues->max());
        $presentationActivityMax = max(1, (int) $presentationActivityValues->max());
        $textbookYearMax = max(1, (int) $textbookYearValues->max());
        $formatPct = fn ($value) => number_format((float) $value, 1) . '%';
        $barWidth = fn ($value, $max) => max(2, min(100, ((float) $value / max(1, (float) $max)) * 100));
        $sumSeriesAt = fn ($series, $index) => collect($series)->sum(fn ($values) => (int) collect($values)->values()->get($index, 0));
        $typeLabel = function ($code) {
            return match ($code) {
                'EA101' => 'Research Paper and Journal Article',
                'EA109' => 'Change in Practices / Procedures / Policy',
                'EA102' => 'Reports and White Papers',
                'EA106' => 'Policy Recommendations',
                default => (string) $code,
            };
        };
    @endphp

    <htmlpageheader name="Header">
        <div class="header-line">
            <table style="width:100%; border-collapse:collapse; font-size:13.6pt; color:#0f172a;">
                <tr>
                    <td style="width:58%; text-align:left; vertical-align:middle;">
                        <table style="border-collapse:collapse;">
                            <tr>
                                @if ($headerLogo)
                                    <td style="vertical-align:middle; padding-right:7px;">
                                        <img src="{{ $headerLogo }}" alt="SiHP Logo" style="height:18px; width:auto; vertical-align:middle;">
                                    </td>
                                @endif
                                <td style="vertical-align:middle; font-size:13.2pt; color:#0f172a; font-weight:bold;">
                                    SiHP Research Database Management System
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:42%; text-align:right; vertical-align:middle; font-size:13.6pt; font-weight:bold; color:#0f172a;">
                        Outputs Visualization
                    </td>
                </tr>
            </table>
        </div>
    </htmlpageheader>

    <htmlpagefooter name="Footer">
        <table style="width:100%; border-top:1px solid #dbe7f5; padding-top:6px; font-size:11.2pt; color:#64748b; border-collapse:collapse;">
            <tr>
                <td style="width:76%; text-align:left;">ผู้สร้างรายงาน {{ $reporter }} เวลา {{ $generatedLabel }}</td>
                <td style="width:24%; text-align:right; white-space:nowrap;">หน้า {PAGENO} / {nbpg}</td>
            </tr>
        </table>
    </htmlpagefooter>

    <div class="hero-shell">
        <div class="report-kicker">Research Outputs Overview</div>
        <div class="report-title">ข้อมูลผลงาน สำหรับสัมมนา</div>
        <div class="report-subtitle">สรุปข้อมูลจากหน้า visualization ตามตัวกรองปัจจุบัน โดยแปลงกราฟเป็นตารางและคำบรรยายสำหรับเอกสาร PDF</div>
        <div style="margin-top:10px;">
            @forelse (($filters ?? collect()) as $filter)
                <span class="chip">{{ $filter }}</span>
            @empty
                <span class="chip">ตัวกรอง: ทั้งหมด</span>
            @endforelse
        </div>
    </div>

    <div class="section-title">ภาพรวมผลงาน</div>
    <table class="summary-grid">
        <tr>
            <td>
                <div class="summary-label">ผลงานทั้งหมด</div>
                <div class="summary-value">{{ number_format($rows->count()) }}</div>
                <div class="summary-note">รายการตามตัวกรองปัจจุบัน</div>
            </td>
            <td>
                <div class="summary-label">ปีที่ครอบคลุม</div>
                <div class="summary-value">{{ data_get($forwardSummary ?? [], 'first_year', '-') }} - {{ data_get($forwardSummary ?? [], 'latest_year', '-') }}</div>
                <div class="summary-note">Peak {{ data_get($forwardSummary ?? [], 'peak_year', '-') }} = {{ number_format((int) data_get($forwardSummary ?? [], 'peak_count', 0)) }} รายการ</div>
            </td>
            <td>
                <div class="summary-label">Research Papers</div>
                <div class="summary-value">{{ number_format($paperCount) }}</div>
                <div class="summary-note">Avg IF {{ number_format((float) data_get($paperSummary ?? [], 'avg_if', 0), 2) }}</div>
            </td>
            <td>
                <div class="summary-label">Presentations</div>
                <div class="summary-value">{{ number_format($presentationCount) }}</div>
                <div class="summary-note">Top conference: {{ data_get($presentationSummary ?? [], 'top_conference', '-') ?: '-' }}</div>
            </td>
        </tr>
    </table>

    <table class="two-col">
        <tr>
            <td>
                <div class="bar-card">
                    <div class="bar-title">กราฟแท่ง: Trend ผลงานตามปี</div>
                    @forelse ($yearTotals as $year => $count)
                        <table class="bar-row">
                            <tr>
                                <td class="bar-label">{{ $year ?: 'ไม่ระบุปี' }}</td>
                                <td><div class="bar-track"><div class="bar-fill blue" style="width: {{ $barWidth($count, $yearTotalMax) }}%;"></div></div></td>
                                <td class="bar-value">{{ number_format((int) $count) }}</td>
                            </tr>
                        </table>
                    @empty
                        <div class="muted">ไม่มีข้อมูล</div>
                    @endforelse
                </div>
            </td>
            <td>
                <div class="bar-card">
                    <div class="bar-title">กราฟแท่ง: ประเภทผลงาน</div>
                    @forelse ($typeCounts->sortDesc()->take(8) as $type => $count)
                        <table class="bar-row">
                            <tr>
                                <td class="bar-label">{{ $type ?: 'ไม่ระบุประเภท' }}</td>
                                <td><div class="bar-track"><div class="bar-fill" style="width: {{ $barWidth($count, $typeMax) }}%;"></div></div></td>
                                <td class="bar-value">{{ number_format((int) $count) }}</td>
                            </tr>
                        </table>
                    @empty
                        <div class="muted">ไม่มีข้อมูล</div>
                    @endforelse
                </div>
            </td>
        </tr>
    </table>

    <table class="two-col">
        <tr>
            <td>
                <div class="section-title">แหล่งข้อมูล</div>
                <table class="data-table">
                    <tr><th>แหล่งข้อมูล</th><th style="width:28%;">จำนวน</th></tr>
                    @forelse ($sourceCounts as $key => $count)
                        <tr>
                            <td>{{ data_get($sourceLabelMap ?? [], $key, $key) }}</td>
                            <td style="text-align:right;">{{ number_format((int) $count) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="muted">ไม่มีข้อมูล</td></tr>
                    @endforelse
                </table>
            </td>
            <td>
                <div class="section-title">Trend ตามปี</div>
                <table class="data-table">
                    <tr><th>ปี</th><th style="width:28%;">จำนวนผลงาน</th></tr>
                    @forelse ($yearTotals as $year => $count)
                        <tr>
                            <td>{{ $year ?: 'ไม่ระบุปี' }}</td>
                            <td style="text-align:right;">{{ number_format((int) $count) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="muted">ไม่มีข้อมูล</td></tr>
                    @endforelse
                </table>
            </td>
        </tr>
    </table>

    <div class="section-title">ผลงานตามกลุ่มเนื้อหา</div>
    <div class="section-subtitle">สรุปเฉพาะผลงานในโครงการวิจัยที่นำไปจัดกลุ่มเนื้อหา</div>
    <table class="data-table">
        <tr>
            <th>กลุ่มเนื้อหา</th>
            <th style="width:14%;">ผลงาน</th>
            <th style="width:14%;">โครงการ</th>
            <th style="width:14%;">สัดส่วน</th>
        </tr>
        @forelse ($projectTypeSummary->take(12) as $row)
            <tr>
                <td>{{ $row->label ?? '-' }}</td>
                <td style="text-align:right;">{{ number_format((int) ($row->count ?? 0)) }}</td>
                <td style="text-align:right;">{{ number_format((int) ($row->project_count ?? 0)) }}</td>
                <td style="text-align:right;">{{ $formatPct($row->pct ?? 0) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">ไม่มีข้อมูล</td></tr>
        @endforelse
    </table>
    <div class="bar-card">
        <div class="bar-title">กราฟแท่ง: ผลงานตามกลุ่มเนื้อหา</div>
        @forelse ($projectTypeSummary->take(10) as $row)
            <table class="bar-row">
                <tr>
                    <td class="bar-label">{{ $row->label ?? '-' }}</td>
                    <td><div class="bar-track"><div class="bar-fill violet" style="width: {{ $barWidth((int) ($row->count ?? 0), $projectTypeMax) }}%;"></div></div></td>
                    <td class="bar-value">{{ number_format((int) ($row->count ?? 0)) }}</td>
                </tr>
            </table>
        @empty
            <div class="muted">ไม่มีข้อมูล</div>
        @endforelse
    </div>

    <div class="section-title">Outputs contributing to Policy-Impact or Practice Change</div>
    <table class="summary-grid">
        <tr>
            @foreach (['EA101', 'EA109', 'EA102', 'EA106'] as $code)
                <td>
                    <div class="summary-label">{{ $typeLabel($code) }}</div>
                    <div class="summary-value">{{ number_format((int) ($impactOutputs[$code] ?? 0)) }}</div>
                    <div class="summary-note">{{ $code }}</div>
                </td>
            @endforeach
        </tr>
    </table>
    <table class="data-table">
        <tr>
            <th style="width:13%;">ปี</th>
            <th>Research Paper</th>
            <th>Change in Practices</th>
            <th>Reports and White Papers</th>
            <th>Policy Recommendations</th>
            <th style="width:12%;">รวม</th>
        </tr>
        @forelse ($impactTrendYears as $index => $year)
            <tr>
                <td>{{ $year }}</td>
                <td style="text-align:right;">{{ number_format((int) collect($impactTrendSeries['EA101'] ?? [])->values()->get($index, 0)) }}</td>
                <td style="text-align:right;">{{ number_format((int) collect($impactTrendSeries['EA109'] ?? [])->values()->get($index, 0)) }}</td>
                <td style="text-align:right;">{{ number_format((int) collect($impactTrendSeries['EA102'] ?? [])->values()->get($index, 0)) }}</td>
                <td style="text-align:right;">{{ number_format((int) collect($impactTrendSeries['EA106'] ?? [])->values()->get($index, 0)) }}</td>
                <td style="text-align:right;">{{ number_format((int) $sumSeriesAt($impactTrendSeries, $index)) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">ไม่มีข้อมูล</td></tr>
        @endforelse
    </table>
    <div class="bar-card">
        <div class="bar-title">กราฟแท่ง: Policy-Impact output รวมตามประเภท</div>
        @foreach (['EA101', 'EA102', 'EA106', 'EA109'] as $code)
            @php $count = (int) ($impactOutputs[$code] ?? 0); @endphp
            <table class="bar-row">
                <tr>
                    <td class="bar-label">{{ $typeLabel($code) }}</td>
                    <td><div class="bar-track"><div class="bar-fill orange" style="width: {{ $barWidth($count, $impactMax) }}%;"></div></div></td>
                    <td class="bar-value">{{ number_format($count) }}</td>
                </tr>
            </table>
        @endforeach
    </div>
    @if (trim((string) data_get($policyImpact, 'impact_theme_narrative_paragraph', '')) !== '')
        <div class="callout">
            <div class="callout-title">Theme output gap analysis</div>
            <div>{{ data_get($policyImpact, 'impact_theme_narrative_paragraph') }}</div>
        </div>
    @endif

    <div class="section-title">Data Literacy Skill</div>
    <div class="section-subtitle">ทักษะและประสบการณ์การใช้ข้อมูลประเภท Real-World Data</div>
    <table class="summary-grid">
        <tr>
            @foreach (['Real-World Data', 'Primary Data', 'Real-World Data + Primary Data'] as $label)
                <td>
                    <div class="summary-label">{{ $label }}</div>
                    <div class="summary-value">{{ number_format((int) ($dataSourceCounts[$label] ?? 0)) }}</div>
                    <div class="summary-note">โครงการ</div>
                </td>
            @endforeach
            <td>
                <div class="summary-label">ยังไม่ระบุ</div>
                <div class="summary-value">{{ number_format((int) data_get($policyImpact, 'data_source_missing_count', 0)) }}</div>
                <div class="summary-note">โครงการ</div>
            </td>
        </tr>
    </table>
    <table class="data-table">
        <tr>
            <th style="width:14%;">ปี</th>
            <th>Real-World Data</th>
            <th>Primary Data</th>
            <th>Real-World Data + Primary Data</th>
            <th style="width:14%;">รวม</th>
        </tr>
        @forelse ($dataSourceTrendYears as $index => $year)
            <tr>
                <td>{{ $year }}</td>
                <td style="text-align:right;">{{ number_format((int) collect($dataSourceTrendSeries['Real-World Data'] ?? [])->values()->get($index, 0)) }}</td>
                <td style="text-align:right;">{{ number_format((int) collect($dataSourceTrendSeries['Primary Data'] ?? [])->values()->get($index, 0)) }}</td>
                <td style="text-align:right;">{{ number_format((int) collect($dataSourceTrendSeries['Real-World Data + Primary Data'] ?? [])->values()->get($index, 0)) }}</td>
                <td style="text-align:right;">{{ number_format((int) $sumSeriesAt($dataSourceTrendSeries, $index)) }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">ไม่มีข้อมูล</td></tr>
        @endforelse
    </table>
    <div class="bar-card">
        <div class="bar-title">กราฟแท่ง: Data Source / Method</div>
        @foreach (['Real-World Data', 'Primary Data', 'Real-World Data + Primary Data'] as $label)
            @php $count = (int) ($dataSourceCounts[$label] ?? 0); @endphp
            <table class="bar-row">
                <tr>
                    <td class="bar-label">{{ $label }}</td>
                    <td><div class="bar-track"><div class="bar-fill blue" style="width: {{ $barWidth($count, $dataSourceMax) }}%;"></div></div></td>
                    <td class="bar-value">{{ number_format($count) }}</td>
                </tr>
            </table>
        @endforeach
    </div>

    <div class="section-title">Publications และ Presentations</div>
    <table class="two-col">
        <tr>
            <td>
                <div class="callout">
                    <div class="callout-title">ผลงานการตีพิมพ์ต่างๆ</div>
                    <div>ทั้งหมด {{ number_format($paperCount) }} รายการ</div>
                    <div class="muted small">Top Journal: {{ data_get($paperSummary ?? [], 'top_journal', '-') ?: '-' }} · Avg IF {{ number_format((float) data_get($paperSummary ?? [], 'avg_if', 0), 2) }}</div>
                </div>
            </td>
            <td>
                <div class="callout">
                    <div class="callout-title">ผลงานการนำเสนอต่างๆ</div>
                    <div>ทั้งหมด {{ number_format($presentationCount) }} รายการ</div>
                    <div class="muted small">Top Presenter: {{ data_get($presentationSummary ?? [], 'top_presenter', '-') ?: '-' }}</div>
                </div>
            </td>
        </tr>
    </table>
    <table class="two-col">
        <tr>
            <td>
                <div class="bar-card">
                    <div class="bar-title">กราฟแท่ง: Publication ranking</div>
                    @forelse ($paperRankingLabels as $index => $label)
                        @php $count = (int) $paperRankingValues->get($index, 0); @endphp
                        <table class="bar-row">
                            <tr>
                                <td class="bar-label">{{ $label ?: 'ไม่ระบุ' }}</td>
                                <td><div class="bar-track"><div class="bar-fill violet" style="width: {{ $barWidth($count, $paperRankingMax) }}%;"></div></div></td>
                                <td class="bar-value">{{ number_format($count) }}</td>
                            </tr>
                        </table>
                    @empty
                        <div class="muted">ไม่มีข้อมูล</div>
                    @endforelse
                </div>
            </td>
            <td>
                <div class="bar-card">
                    <div class="bar-title">กราฟแท่ง: Presentation activity</div>
                    @forelse ($presentationActivityLabels as $index => $label)
                        @php $count = (int) $presentationActivityValues->get($index, 0); @endphp
                        <table class="bar-row">
                            <tr>
                                <td class="bar-label">{{ $label ?: 'ไม่ระบุ' }}</td>
                                <td><div class="bar-track"><div class="bar-fill rose" style="width: {{ $barWidth($count, $presentationActivityMax) }}%;"></div></div></td>
                                <td class="bar-value">{{ number_format($count) }}</td>
                            </tr>
                        </table>
                    @empty
                        <div class="muted">ไม่มีข้อมูล</div>
                    @endforelse
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Textbooks และ Networking</div>
    <table class="two-col">
        <tr>
            <td>
                <div class="bar-card">
                    <div class="bar-title">กราฟแท่ง: Textbooks ตามปี</div>
                    @forelse ($textbookYearLabels as $index => $year)
                        @php $count = (int) $textbookYearValues->get($index, 0); @endphp
                        <table class="bar-row">
                            <tr>
                                <td class="bar-label">{{ $year ?: '-' }}</td>
                                <td><div class="bar-track"><div class="bar-fill" style="width: {{ $barWidth($count, $textbookYearMax) }}%;"></div></div></td>
                                <td class="bar-value">{{ number_format($count) }}</td>
                            </tr>
                        </table>
                    @empty
                        <div class="muted">ไม่มีข้อมูล</div>
                    @endforelse
                </div>
            </td>
            <td>
                <table class="data-table">
                    <tr>
                        <th>ปี</th>
                        <th>EA115</th>
                        <th>EA117</th>
                        <th>รวม</th>
                    </tr>
                    @forelse ($networkingYearLabels as $index => $year)
                        @php
                            $ea115 = (int) collect($networkingYearSeries['EA115'] ?? [])->values()->get($index, 0);
                            $ea117 = (int) collect($networkingYearSeries['EA117'] ?? [])->values()->get($index, 0);
                        @endphp
                        <tr>
                            <td>{{ $year ?: '-' }}</td>
                            <td style="text-align:right;">{{ number_format($ea115) }}</td>
                            <td style="text-align:right;">{{ number_format($ea117) }}</td>
                            <td style="text-align:right;">{{ number_format($ea115 + $ea117) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">ไม่มีข้อมูล</td></tr>
                    @endforelse
                </table>
            </td>
        </tr>
    </table>

    <div class="section-title">บทบาทของคนในหน่วยวิจัยฯ ตามกลุ่มเนื้อหา</div>
    <table class="data-table">
        <tr>
            <th>บทบาท</th>
            <th style="width:20%;">จำนวนคน</th>
            <th style="width:20%;">จำนวนโครงการ</th>
        </tr>
        @foreach (['pi' => 'PI', 'co_pi' => 'Co-PI', 'co_research' => 'Co-Research'] as $key => $label)
            @php $role = $unitRoleTables->get($key); @endphp
            <tr>
                <td>{{ $label }}</td>
                <td style="text-align:right;">{{ number_format((int) data_get($role, 'people_count', 0)) }}</td>
                <td style="text-align:right;">{{ number_format((int) data_get($role, 'project_count', 0)) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="section-title">รายการผลงานตัวอย่างจากข้อมูลชุดนี้</div>
    <div class="section-subtitle">แสดงรายการล่าสุดบางส่วนเพื่อให้ PDF มี trace กลับไปยังข้อมูลจริง</div>
    <table class="data-table">
        <tr>
            <th style="width:8%;">#</th>
            <th style="width:12%;">ปี</th>
            <th>ผลงาน</th>
            <th style="width:22%;">ประเภท</th>
            <th style="width:16%;">แหล่งข้อมูล</th>
        </tr>
        @forelse ($rows->sortByDesc(fn ($row) => (int) ($row->year_be ?? $row->year ?? 0))->take(20)->values() as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->year_be ?? $row->year ?? '-' }}</td>
                <td>{{ $row->title ?? '-' }}</td>
                <td>{{ $row->type_label ?? $row->type ?? $row->type_code ?? '-' }}</td>
                <td>{{ $row->source_label ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">ไม่มีข้อมูล</td></tr>
        @endforelse
    </table>

    <div class="callout">
        <div class="callout-title">หมายเหตุ</div>
        <div>รายงานนี้เป็นการสรุปข้อมูลเบื้องต้นจากข้อมูลที่บันทึกในระบบ และผลการวิเคราะห์อาจเปลี่ยนแปลงได้เมื่อมีข้อมูลเพิ่มเติมหรือมีการปรับตัวกรอง</div>
    </div>
</body>
</html>

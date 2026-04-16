@extends('layouts.app')

@section('content')
    @php
        $totalCount = (int) $rows->count();
        $yearLabels = $yearAxisLabels->values();
        $sourceSeries = $yearBySource->map(fn($values) => collect($values)->map(fn($value) => (int) $value)->values());
        $typeLabels = $typeCounts->keys()->values();
        $typeValues = $typeCounts->values()->map(fn($value) => (int) $value)->values();

        $paperCount = (int) ($paperSummary['count'] ?? 0);
        $presentationCount = (int) ($presentationSummary['count'] ?? 0);
        $textbookCount = (int) ($textbookSummary['count'] ?? 0);
        $typeSectionTotal = (int) $typeCounts->sum();
        $paperLatestCount = (int) (($paperSummary['latest'] ?? collect())->count());
        $presentationLatestCount = (int) (($presentationSummary['latest'] ?? collect())->count());
        $textbookLatestCount = (int) (($textbookSummary['latest'] ?? collect())->count());

        $baseTitle = 'ผลงานของหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
        $titleParts = collect();
        if (filled($selectedYearFrom) || filled($selectedYearTo)) {
            if (filled($selectedYearFrom) && filled($selectedYearTo)) {
                $titleParts->push((int) $selectedYearFrom === (int) $selectedYearTo
                    ? 'ปี ' . $selectedYearFrom
                    : 'ปี ' . $selectedYearFrom . ' - ' . $selectedYearTo);
            } elseif (filled($selectedYearFrom)) {
                $titleParts->push('ตั้งแต่ปี ' . $selectedYearFrom);
            } else {
                $titleParts->push('ถึงปี ' . $selectedYearTo);
            }
        }
        if (filled($selectedType)) {
            $titleParts->push('ประเภท ' . $selectedType);
        }
        if (filled($selectedSource)) {
            $titleParts->push('แหล่งข้อมูล ' . ($sourceLabelMap[$selectedSource] ?? $selectedSource));
        }
        $pageTitle = $baseTitle . ($titleParts->isNotEmpty() ? ' ' . $titleParts->implode(' ') : '');

        $forwardFirstYear = $forwardSummary['first_year'] ?? null;
        $forwardFirstCount = (int) ($forwardSummary['first_count'] ?? 0);
        $forwardLatestYear = $forwardSummary['latest_year'] ?? null;
        $forwardLatestCount = (int) ($forwardSummary['latest_count'] ?? 0);
        $forwardCumulativeGrowthCount = (int) ($forwardSummary['cumulative_growth_count'] ?? 0);
        $forwardCumulativeGrowthPct = $forwardSummary['cumulative_growth_pct'] ?? null;
        $forwardAvgPerYear = (float) ($forwardSummary['avg_per_year'] ?? 0);
        $forwardPeakYear = $forwardSummary['peak_year'] ?? null;
        $forwardPeakCount = (int) ($forwardSummary['peak_count'] ?? 0);
        $forwardKnownYearCount = (int) ($forwardSummary['known_year_count'] ?? 0);
        $currentYearBe = (int) now()->setTimezone('Asia/Bangkok')->format('Y') + 543;
        $rangeFromYear = $selectedYearFrom ?? $forwardFirstYear;
        $rangeToYear = $selectedYearTo ?? $forwardLatestYear;
        $rangeFromCount = ($rangeFromYear !== null && isset($yearTotals)) ? (int) $yearTotals->get((int) $rangeFromYear, 0) : $forwardFirstCount;
        $rangeToCount = ($rangeToYear !== null && isset($yearTotals)) ? (int) $yearTotals->get((int) $rangeToYear, 0) : $forwardLatestCount;
        $rangeDelta = $rangeToCount - $rangeFromCount;
        $rangeGrowthPct = $rangeFromCount > 0 ? round(($rangeDelta / $rangeFromCount) * 100, 1) : null;
        $isRollingThreeYears = ($rangeFromYear !== null && $rangeToYear !== null && ((int) $rangeToYear - (int) $rangeFromYear) === 2);
        $rangeToYearLabel = ((int) ($rangeToYear ?? 0) === $currentYearBe) ? 'ปีนี้' : 'ปีล่าสุด';

        $sourceShareRows = collect($sourceShare ?? [])->take(5);
        $eaFullNameMap = [
            'EA101' => 'Research Paper or Journal Article',
            'EA102' => 'Reports and White Papers',
            'EA103' => 'Presentations in Academic Conferences',
            'EA104' => 'Data Sets and Databases',
            'EA105' => 'Inventions or Innovations (Application / Website)',
            'EA106' => 'Policy Recommendations',
            'EA107' => 'Educational Materials',
            'EA108' => 'Communication Materials (Creations)',
            'EA109' => 'Change in Practices / Procedures / Policy',
            'EA110' => 'Number of Participants (Persons)',
            'EA111' => 'Satisfaction Rating (1-5)',
            'EA112' => 'Present to Committee Meeting / Policy Maker / Board',
            'EA113' => 'Appointment Orders (SIHP as Committee / Secretary)',
            'EA114' => 'Events / Forum',
            'EA115' => 'Capacity Building / Knowledge Sharing',
            'EA116' => 'Research Project',
            'EA117' => 'MOU',
            'EA118' => 'Award and Recognition',
            'EA119' => 'Textbook / Book Chapter',
            'EA120' => 'Manual / Instruction / Guideline',
            'EA999' => 'Other Outputs',
            'OTHER' => 'Other Outputs',
        ];
        $typeBaseColorMap = [
            'EA101' => '#7aa2f7',
            'EA102' => '#7dcfb6',
            'EA103' => '#f7b267',
            'EA104' => '#f79d9d',
            'EA105' => '#b39ddb',
            'EA106' => '#7ec8e3',
            'EA107' => '#9ad29a',
            'EA108' => '#9fa8da',
            'EA109' => '#f6b26b',
            'EA110' => '#f4a6c1',
            'EA111' => '#9bb8ff',
            'EA112' => '#8fd3c8',
            'EA113' => '#c5d86d',
            'EA114' => '#c3a6e8',
            'EA115' => '#d9b08c',
            'EA116' => '#8ecae6',
            'EA117' => '#9ccc9c',
            'EA118' => '#a5b4fc',
            'EA119' => '#f4b183',
            'EA120' => '#e8a5a5',
            'EA999' => '#82c7b2',
            'OTHER' => '#aab2bd',
        ];
        $withAlpha = static function (string $hex, string $alpha): string {
            $base = ltrim(trim($hex), '#');
            if (strlen($base) !== 6) {
                return '#cbd5e1';
            }
            return '#' . $base . $alpha;
        };
        $extractTypeCode = static function ($type): string {
            $text = trim((string) $type);
            if ($text === '') {
                return 'OTHER';
            }
            if (preg_match('/^(EA\d{3})/i', $text, $match) === 1) {
                return strtoupper($match[1]);
            }
            return 'OTHER';
        };

        $typeStyleMap = collect($typeBaseColorMap)->mapWithKeys(function ($baseColor, $type) use ($withAlpha) {
            return [
                (string) $type => sprintf(
                    'background-color:%s; border-color:%s; color:%s;',
                    $withAlpha($baseColor, '1A'),
                    $withAlpha($baseColor, '66'),
                    $baseColor
                ),
            ];
        })->all();
        $typeCardBgColorByCode = collect($typeBaseColorMap)->mapWithKeys(fn($baseColor, $type) => [
            (string) $type => $withAlpha($baseColor, '14')
        ])->all();
        $typeCardBorderColorByCode = collect($typeBaseColorMap)->mapWithKeys(fn($baseColor, $type) => [
            (string) $type => $withAlpha($baseColor, '66')
        ])->all();
        $typeChartFillColorByCode = collect($typeBaseColorMap)->mapWithKeys(fn($baseColor, $type) => [
            (string) $type => $baseColor
        ])->all();
        $typeCardStyleMap = collect($typeBaseColorMap)->mapWithKeys(function ($baseColor, $type) use ($withAlpha) {
            return [
                (string) $type => sprintf(
                    'background-color:%s; border-color:%s;',
                    $withAlpha($baseColor, '14'),
                    $withAlpha($baseColor, '66')
                ),
            ];
        })->all();
        $typeChartColorByCode = collect($typeLabels)->mapWithKeys(function ($code) use ($typeChartFillColorByCode, $typeCardBgColorByCode, $extractTypeCode) {
            $normalizedCode = strtoupper((string) $code);
            $resolvedCode = $extractTypeCode($normalizedCode);
            return [$normalizedCode => $typeChartFillColorByCode[$resolvedCode] ?? $typeCardBgColorByCode[$resolvedCode] ?? '#aab2bd'];
        });

        $typeChartLabels = collect($typeLabels)->map(function ($code) use ($eaFullNameMap) {
            $code = strtoupper((string) $code);
            return $code . ' - ' . ($eaFullNameMap[$code] ?? ('ประเภท ' . $code));
        })->values();
        $typeChartColors = collect($typeLabels)->map(function ($code) use ($typeChartColorByCode) {
            return $typeChartColorByCode->get(strtoupper((string) $code), '#f8fafc');
        })->values();
        $typeChartBorderColors = collect($typeLabels)->map(function ($code) use ($typeCardBorderColorByCode) {
            return $typeCardBorderColorByCode[strtoupper((string) $code)] ?? '#cbd5e1';
        })->values();
        $typeBreakdownRows = collect($typeCounts ?? collect())
            ->map(function ($count, $code) use ($typeValues, $eaFullNameMap, $typeChartColorByCode) {
                $total = (int) collect($typeValues)->sum();
                $countInt = (int) $count;
                $normalizedCode = strtoupper((string) $code);
                return (object) [
                    'code' => $normalizedCode,
                    'label' => $eaFullNameMap[$normalizedCode] ?? ('ประเภท ' . $normalizedCode),
                    'count' => $countInt,
                    'pct' => $total > 0 ? round(($countInt / $total) * 100, 1) : 0.0,
                    'color' => $typeChartColorByCode->get($normalizedCode, '#f8fafc'),
                ];
            })
            ->sortByDesc('count')
            ->values();
        $topSource = $sourceShareRows->first();
        $topType = $typeBreakdownRows->first();
        $defaultTypeStyle = 'background-color:#f8fafc; border-color:#cbd5e1; color:#334155;';
        $outputTypeStyle = function ($type) use ($typeStyleMap, $defaultTypeStyle, $extractTypeCode) {
            $code = $extractTypeCode($type);
            return $typeStyleMap[$code] ?? $defaultTypeStyle;
        };
        $defaultTypeCardStyle = 'background-color:#ffffff; border-color:#e2e8f0;';
        $outputTypeCardStyle = function ($type) use ($typeCardStyleMap, $defaultTypeCardStyle, $extractTypeCode) {
            $code = $extractTypeCode($type);
            return $typeCardStyleMap[$code] ?? $defaultTypeCardStyle;
        };
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <style>
        .viz-page {
            font-family: "Sarabun", "Noto Sans Thai", "Segoe UI", sans-serif;
            background:
                radial-gradient(1200px 420px at 8% -5%, rgba(14, 116, 144, .08), transparent 70%),
                radial-gradient(900px 360px at 100% 0%, rgba(30, 64, 175, .07), transparent 68%),
                #f3f6fb;
        }
        .panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
        }
        .metric {
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.02em;
        }
        .scene {
            min-height: 78vh;
            padding-top: 8px;
            padding-bottom: 8px;
        }
        .scene--compact {
            min-height: auto;
            padding-top: 0;
            padding-bottom: 0;
            align-items: start;
        }
        .scene--compact .chart-sticky {
            position: relative;
            top: auto;
        }
        .scene--balanced {
            min-height: auto;
            padding-top: 0;
            padding-bottom: 0;
            align-items: stretch;
        }
        .scene--balanced .chart-sticky {
            position: relative;
            top: auto;
        }
        .chart-sticky {
            position: sticky;
            top: 92px;
        }
        .fade-up {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity .55s ease, transform .55s ease;
        }
        .fade-up.in {
            opacity: 1;
            transform: translateY(0);
        }
        .focus-kpi {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }
        .growth-panel {
            background:
                radial-gradient(420px 120px at 0% 0%, rgba(37, 99, 235, .08), transparent 75%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }
        .growth-summary {
            border: 1px solid #bfdbfe;
            border-radius: 14px;
            background: linear-gradient(180deg, #f0f7ff 0%, #ffffff 100%);
        }
        .scroll-card {
            max-height: 360px;
            overflow-y: auto;
            padding-right: 6px;
        }
        .type-chart-wrap {
            height: 540px;
        }
        .type-right-panel {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .type-right-panel .scroll-card {
            flex: 1;
            max-height: 440px;
        }
        .type-table-head {
            display: grid;
            grid-template-columns: minmax(280px, 2fr) 120px 110px 130px;
            gap: 12px;
        }
        .type-table-row {
            display: grid;
            grid-template-columns: minmax(280px, 2fr) 120px 110px 130px;
            gap: 12px;
            align-items: start;
        }
        .type-drawer-row {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
        }
        .type-drawer-row > summary {
            list-style: none;
            cursor: pointer;
            padding: 12px;
        }
        .type-drawer-row > summary::-webkit-details-marker {
            display: none;
        }
        .type-drawer-row[open] > summary {
            background: #f8fafc;
        }
        .type-title-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .type-expand-hint {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            letter-spacing: .01em;
        }
        .type-drawer-row[open] .type-expand-hint::after {
            content: " (ซ่อน)";
            color: #64748b;
            font-weight: 500;
        }
        .type-drawer-body {
            border-top: 1px solid #e2e8f0;
            padding: 10px 12px 12px;
            background: #f8fafc;
        }
        .output-item-button {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            padding: 9px 10px;
            text-align: left;
            transition: background-color .18s ease, border-color .18s ease;
        }
        .output-item-button:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }
        .output-type-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid transparent;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
        }
        .type-color-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 999px;
            margin-right: 8px;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .35);
        }
        .viz-modal {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, .52);
            padding: 16px;
        }
        .viz-modal.open {
            display: flex;
        }
        .viz-modal-card {
            width: min(1100px, 100%);
            max-height: 86vh;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            background: #fff;
            box-shadow: 0 28px 70px rgba(15, 23, 42, .28);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .viz-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 14px;
            background: #f8fafc;
        }
        .viz-modal-body {
            padding: 12px 14px 14px;
            overflow: auto;
        }
        .viz-detail-box {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            padding: 12px;
            color: #334155;
            white-space: pre-wrap;
            line-height: 1.6;
        }
        .viz-link-card {
            border: 1px solid #dbeafe;
            border-radius: 12px;
            background: #f8fbff;
            padding: 12px;
        }
        .type-bar {
            height: 8px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }
        .type-bar > span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #0f766e, #1d4ed8);
        }
        .output-list-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
        }
        .paper-record-scroll {
            max-height: 520px;
            overflow-y: auto;
            padding-right: 6px;
        }
        .drawer {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
        }
        .drawer > summary {
            list-style: none;
            cursor: pointer;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .drawer > summary::-webkit-details-marker {
            display: none;
        }
        .drawer > summary::after {
            content: '+';
            font-size: 20px;
            line-height: 1;
            color: #334155;
        }
        .drawer[open] > summary::after {
            content: '-';
        }
        .drawer-body {
            border-top: 1px solid #e2e8f0;
            padding: 10px 14px 12px;
            background: #f8fafc;
        }
        @media (max-width: 1023px) {
            .scene {
                min-height: auto;
            }
            .chart-sticky {
                position: relative;
                top: auto;
            }
            .type-chart-wrap {
                height: 360px;
            }
            .type-right-panel .scroll-card {
                max-height: 360px;
            }
            .type-table-head,
            .type-table-row {
                grid-template-columns: 1fr;
                gap: 6px;
            }
        }
    </style>

    <div class="viz-page min-h-screen pb-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 md:py-8">
            <section class="fade-up panel p-6 md:p-8">
                <h1 class="mt-2 text-2xl font-semibold leading-tight text-slate-900 md:text-2xl">{{ $pageTitle }}</h1>

                <div class="mt-4 flex flex-wrap gap-2 text-xs text-slate-600">
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1">ทั้งหมด {{ number_format($totalCount) }} ผลงาน</span>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1">แหล่งหลัก: {{ $topSource?->label ?? '-' }}</span>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1">ประเภทหลัก: {{ $topType?->label ?? '-' }}</span>
                </div>
            </section>

            <section class="fade-up panel p-4 md:p-5">
                <form id="vizFilters" method="GET" action="{{ route('outputs.visualization') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">แหล่งข้อมูล</span>
                        <select name="source" class="js-auto-filter mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach ($sourceLabelMap as $sourceKey => $sourceLabel)
                                <option value="{{ $sourceKey }}" @selected($selectedSource === $sourceKey)>{{ $sourceLabel }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ประเภท (EA)</span>
                        <select name="type" class="js-auto-filter mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach ($typeOptions as $typeCode)
                                <option value="{{ $typeCode }}" @selected($selectedType === $typeCode)>{{ $typeCode }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ปีเริ่ม (พ.ศ.)</span>
                        <select name="year_from" class="js-auto-filter mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected((int) $selectedYearFrom === (int) $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ปีสิ้นสุด (พ.ศ.)</span>
                        <select name="year_to" class="js-auto-filter mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected((int) $selectedYearTo === (int) $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="flex items-end justify-between gap-2">
                        <div id="filterLoading" class="hidden text-xs text-slate-500">กำลังอัปเดต...</div>
                        <a href="{{ route('outputs.visualization') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">ล้าง</a>
                    </div>
                </form>
            </section>

            <section class="fade-up panel p-5 md:p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">ประเภทโครงการ (6 กลุ่ม) จาก Output</h2>
                        <p class="mt-1 text-sm text-slate-500">สรุปจำนวนผลงาน แยกตามประเภทโครงการ พร้อมรายการผลงานในแต่ละกลุ่ม</p>
                    </div>
                    <div class="focus-kpi px-4 py-2">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">Total Outputs</div>
                        <div class="metric text-xl font-semibold text-slate-900">{{ number_format((int) ($projectTypeTotalOutputs ?? 0)) }}</div>
                    </div>
                </div>

                <div class="mt-4 type-table-head rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <div>ประเภทโครงการ</div>
                    <div class="text-right">ผลงาน</div>
                    <div class="text-right">สัดส่วน</div>
                    <div class="text-right">โครงการ</div>
                </div>

                <div class="mt-2 space-y-2">
                    @foreach (($projectTypeSummary ?? collect()) as $group)
                        <details class="type-drawer-row">
                            <summary>
                                <div class="type-table-row">
                                    <div>
                                        <div class="type-title-line">
                                            <div class="text-sm font-semibold text-slate-800">{{ $group->label }}</div>
                                            <div class="type-expand-hint">คลิกเพื่อดูรายการผลงาน</div>
                                        </div>
                                        <div class="mt-1 type-bar">
                                            <span style="width: {{ min(100, max(0, (float) $group->pct)) }}%"></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="metric text-lg font-semibold text-slate-900">{{ number_format((int) $group->output_count) }}</div>
                                        <div class="text-[11px] text-slate-500">ผลงาน</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="metric text-lg font-semibold text-slate-900">{{ number_format((float) $group->pct, 1) }}%</div>
                                        <div class="text-[11px] text-slate-500">ของผลงานทั้งหมด</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="metric text-lg font-semibold text-slate-900">{{ number_format((int) $group->project_count) }}</div>
                                        <div class="text-[11px] text-slate-500">โครงการ</div>
                                    </div>
                                </div>
                            </summary>
                            <div class="type-drawer-body">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">รายการผลงานในกลุ่มนี้</div>
                                <div class="mt-2">
                                    <div class="max-h-64 space-y-1 overflow-y-auto pr-1">
                                        @forelse (($group->items ?? collect()) as $idx => $item)
                                            <button
                                                type="button"
                                                class="output-item-button js-output-item"
                                                style="{{ $outputTypeCardStyle($item->type_code) }}"
                                                data-title="{{ $item->title }}"
                                                data-link="{{ $item->link ?? '' }}"
                                                data-detail="{{ $item->detail ?? '' }}"
                                                data-type="{{ $item->type_code }} - {{ $item->type_label }}"
                                            >
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="text-xs text-slate-700">
                                                            <span class="mr-1 font-semibold text-slate-500">{{ (int) $idx + 1 }}.</span>{{ $item->title }}
                                                        </div>
                                                        <div class="mt-1">
                                                            <span class="output-type-chip" style="{{ $outputTypeStyle($item->type_code) }}">{{ $item->type_code }} - {{ $item->type_label }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="shrink-0 text-[11px] font-semibold text-slate-500">ดูรายละเอียด</div>
                                                </div>
                                            </button>
                                        @empty
                                            <div class="rounded-md border border-dashed border-slate-200 px-3 py-4 text-xs text-slate-400">ไม่มีรายการผลงานในช่วงปีที่เลือก</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>

            <section class="scene scene--compact grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                <article class="fade-up panel chart-sticky h-full p-4 md:p-5">
                    <h2 class="text-base font-semibold text-slate-900">ผลงานที่เกิดขึ้น <span class="ml-1 text-sm font-medium text-slate-500">({{ number_format($totalCount) }} รายการ)</span></h2>
                    <div class="mt-4 h-[360px]"><canvas id="chartOutputsByYear"></canvas></div>
                </article>
                <article class="fade-up panel growth-panel flex h-full flex-col justify-center p-5 md:p-6">
                    <h3 class="text-xl font-semibold text-slate-900">ขับเคลื่อนผลงาน</h3>
                    <div class="growth-summary mt-3 px-4 py-3">
                        <div class="metric mt-1 text-2xl font-semibold text-slate-900">
                            @if ($rangeGrowthPct !== null)
                                {{ $rangeDelta >= 0 ? 'เพิ่มขึ้น' : 'ลดลง' }} {{ number_format(abs($rangeDelta)) }} รายการ
                                ({{ number_format(abs((float) $rangeGrowthPct), 1) }}%)
                            @else
                                ยังคำนวณการเติบโตในช่วงนำเสนอไม่ได้
                            @endif
                        </div>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        @if ($rangeFromYear && $rangeToYear)
                            @if ($isRollingThreeYears)
                                ช่วงย้อนหลัง 3 ปี:
                            @endif
                            จากปี {{ $rangeFromYear }} ({{ number_format($rangeFromCount) }} รายการ)
                            ถึง{{ $rangeToYearLabel }} {{ $rangeToYear }} ({{ number_format($rangeToCount) }} รายการ)
                            {{ $rangeDelta >= 0 ? 'เพิ่มขึ้น' : 'ลดลง' }} {{ number_format(abs($rangeDelta)) }} รายการ
                        @else
                            ข้อมูลปีไม่พอสำหรับคำนวณในช่วงนำเสนอ
                        @endif
                    </p>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-center">
                            <div class="metric text-xl font-semibold text-slate-900">{{ $rangeFromYear ?: '-' }}</div>
                            <div class="mt-1 text-sm font-semibold text-slate-600">{{ number_format($rangeFromCount) }} รายการ</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-center">
                            <div class="metric text-xl font-semibold text-slate-900">{{ $rangeToYear ?: '-' }}</div>
                            <div class="mt-1 text-sm font-semibold text-slate-600">{{ number_format($rangeToCount) }} รายการ</div>
                        </div>
                    </div>
                </article>
            </section>

            <section class="scene scene--balanced grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                <article class="fade-up panel chart-sticky h-full p-4 md:p-5">
                    <h2 class="text-base font-semibold text-slate-900">ประเภทผลงานที่เกิดขึ้นจากหลายมิติ <span class="ml-1 text-sm font-medium text-slate-500">({{ number_format($typeSectionTotal) }} รายการ)</span></h2>
                    <div class="type-chart-wrap mt-4"><canvas id="chartOutputTypes"></canvas></div>
                </article>
                <article class="fade-up panel type-right-panel p-5 md:p-6">
                    <h3 class="text-xl font-semibold text-slate-900">โดยผลงาน</h3>
                    <div class="focus-kpi mt-3 px-4 py-3">
                        <div class="metric mt-1 text-2xl font-semibold text-slate-900">
                            {{ $topType?->label ?? '-' }}
                            @if ($topType)
                                {{ number_format((float) $topType->pct, 1) }}%
                            @endif
                        </div>
                    </div>
                    <div class="scroll-card mt-4 space-y-3">
                        @forelse ($typeBreakdownRows as $type)
                            <div class="rounded-xl border px-4 py-3" style="{{ $outputTypeCardStyle($type->code) }}">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-slate-800">
                                        <span class="type-color-dot" style="background-color: {{ $type->color }}"></span>{{ $type->code }} - {{ $type->label }}
                                    </span>
                                    <span class="metric font-semibold text-slate-900">{{ number_format((float) $type->pct, 1) }}%</span>
                                </div>
                                <div class="mt-1 text-xs text-slate-500">{{ number_format((int) $type->count) }} ผลงาน</div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 px-4 py-5 text-sm text-slate-500">ไม่มีข้อมูล</div>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="scene scene--balanced grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                <article class="fade-up panel chart-sticky flex h-full flex-col p-4 md:p-5">
                    <h2 class="text-base font-semibold text-slate-900">ผลงานการตีพิมพ์ต่างๆ <span class="ml-1 text-sm font-medium text-slate-500">({{ number_format($paperCount) }} รายการ)</span></h2>
                    <div class="mt-4 flex flex-1 items-center justify-center">
                        <div class="h-[360px] w-full max-w-[880px]"><canvas id="chartPaperQuartile"></canvas></div>
                    </div>
                </article>
                <article class="fade-up panel flex h-full flex-col p-5 md:p-6">
                    <h3 class="text-xl font-semibold text-slate-900">ตีพิมพ์ไป</h3>
                    <div class="focus-kpi mt-3 px-4 py-3">
                        <div class="metric mt-1 text-2xl font-semibold text-slate-900">{{ number_format($paperCount) }} ผลงาน</div>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">Top Journal: {{ $paperSummary['top_journal'] ?: '-' }} · Avg IF: {{ number_format((float) ($paperSummary['avg_if'] ?? 0), 2) }}</p>
                    <p class="mt-1 text-xs text-slate-500">แสดงทั้งหมด {{ number_format((int) (($paperSummary['all'] ?? collect())->count())) }} รายการ</p>
                    <div class="paper-record-scroll mt-4 flex-1 space-y-2">
                        @forelse (($paperSummary['all'] ?? collect()) as $row)
                            <a href="{{ $row->link ?: '#' }}" class="block rounded-lg border border-slate-200 px-3 py-2.5 text-slate-700 hover:bg-slate-50">
                                <div class="font-medium line-clamp-2">{{ $row->title }}</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $row->journal ?: 'ไม่ระบุวารสาร' }} • {{ $row->quartile ?: 'ไม่ระบุ Quartile' }} • IF {{ is_numeric($row->impact_factor) ? number_format((float) $row->impact_factor, 2) : '-' }} • {{ $row->year_be ?: '-' }}
                                </div>
                            </a>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-200 px-3 py-4 text-xs text-slate-500">ไม่มีข้อมูล</div>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="scene scene--balanced grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                <article class="fade-up panel chart-sticky flex h-full flex-col p-4 md:p-5">
                    <h2 class="text-base font-semibold text-slate-900">ผลงานการนำเสนอต่างๆ <span class="ml-1 text-sm font-medium text-slate-500">({{ number_format($presentationCount) }} รายการ)</span></h2>
                    <div class="mt-4 flex flex-1 items-center justify-center">
                        <div class="h-[360px] w-full max-w-[760px]"><canvas id="chartPresentationActivity"></canvas></div>
                    </div>
                </article>
                <article class="fade-up panel flex h-full flex-col p-5 md:p-6">
                    <h3 class="text-xl font-semibold text-slate-900">นำเสนอไป</h3>
                    <div class="focus-kpi mt-3 px-4 py-3">
                        <div class="metric mt-1 text-2xl font-semibold text-slate-900">{{ number_format($presentationCount) }} ผลงาน</div>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">Top Conference: {{ $presentationSummary['top_conference'] ?: '-' }} · Top Presenter: {{ $presentationSummary['top_presenter'] ?: '-' }}</p>
                    <p class="mt-1 text-xs text-slate-500">แสดงทั้งหมด {{ number_format((int) (($presentationSummary['all'] ?? collect())->count())) }} รายการ</p>
                    <div class="paper-record-scroll mt-4 flex-1 space-y-2">
                        @forelse (($presentationSummary['all'] ?? collect()) as $row)
                            <a href="{{ $row->link ?: '#' }}" class="block rounded-lg border border-slate-200 px-3 py-2.5 text-slate-700 hover:bg-slate-50">
                                <div class="font-medium line-clamp-2">{{ $row->title }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $row->conference_name ?: 'ไม่ระบุเวที' }} • {{ $row->presentation_type ?: 'ไม่ระบุประเภท' }} • {{ $row->presenter_name ?: 'ไม่ระบุผู้นำเสนอ' }}</div>
                            </a>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-200 px-3 py-4 text-xs text-slate-500">ไม่มีข้อมูล</div>
                        @endforelse
                    </div>
                </article>
            </section>

            @if ($textbookCount > 0)
                <section class="scene grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                    <article class="fade-up panel chart-sticky p-4 md:p-5">
                        <h2 class="text-base font-semibold text-slate-900">ผลงาน Textbooks <span class="ml-1 text-sm font-medium text-slate-500">({{ number_format($textbookCount) }} รายการ)</span></h2>
                        <div class="mt-4 h-[340px]"><canvas id="chartTextbookPublisher"></canvas></div>
                    </article>
                    <article class="fade-up panel p-5 md:p-6">
                        <h3 class="text-xl font-semibold text-slate-900">มีผลงาน Textbooks ไป</h3>
                        <div class="focus-kpi mt-3 px-4 py-3">
                            <div class="metric mt-1 text-2xl font-semibold text-slate-900">{{ number_format($textbookCount) }} Textbooks</div>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">Top Publisher: {{ $textbookSummary['top_publisher'] ?: '-' }} · ผู้เขียนไม่ซ้ำ: {{ number_format((int) ($textbookSummary['unique_authors'] ?? 0)) }}</p>
                        <p class="mt-1 text-xs text-slate-500">รายการที่แสดงล่าสุด {{ number_format($textbookLatestCount) }} รายการ</p>
                        <div class="mt-4 space-y-2">
                            @forelse (($textbookSummary['latest'] ?? collect()) as $row)
                                <a href="{{ $row->link ?: '#' }}" class="block rounded-lg border border-slate-200 px-3 py-2.5 text-slate-700 hover:bg-slate-50">
                                    <div class="font-medium line-clamp-2">{{ $row->title }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $row->publisher_name ?: 'ไม่ระบุสำนักพิมพ์' }} • {{ $row->book_title ?: 'ไม่ระบุชื่อหนังสือ' }} • {{ $row->year_be ?: '-' }}</div>
                                </a>
                            @empty
                                <div class="rounded-lg border border-dashed border-slate-200 px-3 py-4 text-xs text-slate-500">ไม่มีข้อมูล</div>
                            @endforelse
                        </div>
                    </article>
                </section>
            @endif
        </div>
    </div>

    <div id="outputItemModal" class="viz-modal" aria-hidden="true">
        <div class="viz-modal-card">
            <div class="viz-modal-head">
                <div id="outputItemModalTitle" class="text-sm font-semibold text-slate-900">รายละเอียดผลงาน</div>
                <button type="button" id="outputItemModalClose" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    ปิด
                </button>
            </div>
            <div id="outputItemModalBody" class="viz-modal-body"></div>
        </div>
    </div>

    <script>
        (() => {
            const OutlabelsWithLinesPlugin = {
                id: 'outlabelsWithLines',
                afterDatasetsDraw(chart, args, pluginOptions) {
                    const opts = pluginOptions || {};
                    if (!opts.enabled) return;
                    if (chart.config.type !== 'doughnut') return;

                    const dataset = chart.data.datasets?.[0];
                    const meta = chart.getDatasetMeta(0);
                    if (!dataset || !meta?.data?.length) return;

                    const values = (dataset.data || []).map((v) => Number(v || 0));
                    const total = values.reduce((sum, v) => sum + v, 0);
                    if (total <= 0) return;

                    const labels = chart.data.labels || [];
                    const ctx = chart.ctx;
                    const { left, right, top, bottom } = chart.chartArea;
                    const lineColor = opts.lineColor || '#94a3b8';
                    const textColor = opts.textColor || '#0f172a';
                    const minAngleDeg = Number(opts.minAngleDeg || 12);
                    const minPct = Number(opts.minPct || 0);
                    const maxLabels = Number(opts.maxLabels || 0);
                    const minGap = Number(opts.minGap || 14);

                    const candidates = [];
                    const pointsLeft = [];
                    const pointsRight = [];

                    meta.data.forEach((arc, i) => {
                        const value = values[i];
                        if (value <= 0) return;
                        const pct = (value / total) * 100;
                        if (pct < minPct) return;

                        const arcAngleDeg = (((arc.endAngle - arc.startAngle) * 180) / Math.PI);
                        if (arcAngleDeg >= minAngleDeg && values.length <= 8) return;

                        const mid = (arc.startAngle + arc.endAngle) / 2;
                        const cx = arc.x;
                        const cy = arc.y;
                        const r = arc.outerRadius;
                        const sx = cx + Math.cos(mid) * (r + 2);
                        const sy = cy + Math.sin(mid) * (r + 2);
                        const mx = cx + Math.cos(mid) * (r + 16);
                        const my = cy + Math.sin(mid) * (r + 16);
                        const isRight = Math.cos(mid) >= 0;
                        const ex = isRight ? Math.min(right - 8, mx + 22) : Math.max(left + 8, mx - 22);
                        const pctText = ((value / total) * 100).toFixed(1);
                        const text = `${value} รายการ (${pctText}%)`;
                        candidates.push({ sx, sy, mx, my, ex, y: my, text, isRight, value, pct });
                    });

                    let selected = candidates;
                    if (maxLabels > 0 && candidates.length > maxLabels) {
                        selected = [...candidates]
                            .sort((a, b) => b.value - a.value)
                            .slice(0, maxLabels);
                    }
                    selected.forEach((point) => {
                        (point.isRight ? pointsRight : pointsLeft).push(point);
                    });

                    const resolveOverlap = (items, minY, maxY) => {
                        items.sort((a, b) => a.y - b.y);
                        for (let i = 1; i < items.length; i++) {
                            if (items[i].y - items[i - 1].y < minGap) {
                                items[i].y = items[i - 1].y + minGap;
                            }
                        }
                        for (let i = items.length - 2; i >= 0; i--) {
                            if (items[i + 1].y > maxY && items[i + 1].y - items[i].y < minGap) {
                                items[i].y = items[i + 1].y - minGap;
                            }
                        }
                        items.forEach((item) => {
                            item.y = Math.max(minY, Math.min(maxY, item.y));
                        });
                    };

                    resolveOverlap(pointsRight, top + 8, bottom - 8);
                    resolveOverlap(pointsLeft, top + 8, bottom - 8);

                    ctx.save();
                    ctx.font = '700 11px Sarabun, Noto Sans Thai, Segoe UI, sans-serif';
                    ctx.textBaseline = 'middle';
                    ctx.lineWidth = 1.5;
                    ctx.strokeStyle = lineColor;
                    ctx.fillStyle = textColor;

                    [...pointsRight, ...pointsLeft].forEach((p) => {
                        ctx.beginPath();
                        ctx.moveTo(p.sx, p.sy);
                        ctx.lineTo(p.mx, p.y);
                        ctx.lineTo(p.ex, p.y);
                        ctx.stroke();

                        ctx.textAlign = p.isRight ? 'left' : 'right';
                        const tx = p.isRight ? p.ex + 5 : p.ex - 5;
                        ctx.fillText(p.text, tx, p.y);
                    });
                    ctx.restore();
                }
            };

            Chart.register(ChartDataLabels, OutlabelsWithLinesPlugin);

            const yearLabels = @json($yearLabels);
            const sourceSeries = @json($sourceSeries);
            const typeLabels = @json($typeChartLabels);
            const typeValues = @json($typeValues);
            const typeColors = @json($typeChartColors);
            const typeBorderColors = @json($typeChartBorderColors);
            const sourceLabelMap = @json($sourceLabelMap);
            const paperQuartileLabels = @json($paperQuartileLabels);
            const paperQuartileValues = @json($paperQuartileValues);
            const presentationActivityLabels = @json($presentationActivityLabels);
            const presentationActivityValues = @json($presentationActivityValues);
            const textbookPublisherLabels = @json($textbookPublisherLabels);
            const textbookPublisherValues = @json($textbookPublisherValues);

            const gridColor = 'rgba(148, 163, 184, 0.2)';
            const textColor = '#334155';
            const chartAnim = { duration: 760, easing: 'easeOutCubic' };
            const outputInProjectColor = '#4f46e5';
            const outputInProjectBorder = '#3730a3';
            const outputOutsideProjectColor = '#0f766e';
            const outputOutsideProjectBorder = '#115e59';

            const safeLabels = (labels) => (labels && labels.length ? labels : ['ไม่มีข้อมูล']);
            const safeValues = (values, fallback = 0) => (values && values.length ? values : [fallback]);
            const safeColors = (colors) => (colors && colors.length ? colors : ['#64748b']);
            const suggestedAxisMax = (values, min = 5) => {
                const maxVal = Math.max(...(values || [0]), 0);
                if (maxVal <= 0) return min;
                return Math.ceil(maxVal * 1.18);
            };
            const labelsForYearChart = safeLabels(yearLabels);
            const projectSeries = labelsForYearChart.map((_, index) => Number(sourceSeries?.project?.[index] || 0));
            const nonProjectSeries = labelsForYearChart.map((_, index) =>
                Number(sourceSeries?.back_office?.[index] || 0) + Number(sourceSeries?.external?.[index] || 0)
            );
            const totalsByYear = labelsForYearChart.map((_, i) => projectSeries[i] + nonProjectSeries[i]);

            new Chart(document.getElementById('chartOutputsByYear'), {
                type: 'bar',
                data: {
                    labels: labelsForYearChart,
                    datasets: [
                        {
                            label: 'ผลงานในโครงการวิจัย',
                            data: projectSeries,
                            backgroundColor: outputInProjectColor,
                            borderColor: outputInProjectBorder,
                            borderWidth: 1,
                            hoverBackgroundColor: outputInProjectBorder,
                            borderRadius: 8
                        },
                        {
                            label: 'ผลงานนอกโครงการวิจัย',
                            data: nonProjectSeries,
                            backgroundColor: outputOutsideProjectColor,
                            borderColor: outputOutsideProjectBorder,
                            borderWidth: 1,
                            hoverBackgroundColor: outputOutsideProjectBorder,
                            borderRadius: 8
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: chartAnim,
                    scales: {
                        x: { stacked: true, ticks: { color: textColor }, grid: { color: gridColor } },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            suggestedMax: suggestedAxisMax(totalsByYear, 10),
                            ticks: { color: textColor, precision: 0 },
                            grid: { color: gridColor }
                        },
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                boxWidth: 18,
                                boxHeight: 10,
                                padding: 14
                            }
                        },
                        datalabels: {
                            color: '#0f172a',
                            anchor: 'end',
                            align: 'end',
                            offset: 2,
                            clamp: true,
                            font: { weight: '700', size: 11 },
                            formatter: (value, ctx) => {
                                const index = ctx.dataIndex;
                                const isTopStack = ctx.datasetIndex === 1;
                                if (!isTopStack) return '';
                                const total = Number(projectSeries[index] || 0) + Number(nonProjectSeries[index] || 0);
                                return total > 0 ? total : '';
                            }
                        },
                    }
                }
            });

            new Chart(document.getElementById('chartOutputTypes'), {
                type: 'doughnut',
                data: {
                    labels: safeLabels(typeLabels),
                    datasets: [{
                        data: safeValues(typeValues, 1),
                        backgroundColor: safeColors(typeColors),
                        hoverBackgroundColor: safeColors(typeColors),
                        borderColor: safeColors(typeBorderColors),
                        hoverBorderColor: safeColors(typeBorderColors),
                        borderWidth: 1.25
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: chartAnim,
                    layout: {
                        padding: { top: 56, right: 30, left: 30, bottom: 10 }
                    },
                    radius: '88%',
                    cutout: '46%',
                    elements: { arc: { hoverOffset: 0 } },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                padding: 10,
                                boxWidth: 12,
                                generateLabels: (chart) => {
                                    const labels = chart.data.labels || [];
                                    const data = chart.data.datasets?.[0]?.data || [];
                                    const total = data.reduce((sum, v) => sum + Number(v || 0), 0);
                                    return labels.map((label, i) => {
                                        const value = Number(data[i] || 0);
                                        const pct = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                                        const color = chart.data.datasets[0].backgroundColor[i];
                                        const stroke = chart.data.datasets[0].borderColor[i] || color;
                                        return {
                                            text: `${label} (${value}, ${pct}%)`,
                                            fillStyle: color,
                                            strokeStyle: stroke,
                                            lineWidth: 1,
                                            hidden: !chart.getDataVisibility(i),
                                            index: i,
                                        };
                                    });
                                }
                            }
                        },
                        datalabels: {
                            display: false
                        },
                        outlabelsWithLines: {
                            enabled: true,
                            minAngleDeg: 16,
                            minPct: 1.4,
                            maxLabels: 10,
                            minGap: 16,
                            lineColor: '#94a3b8',
                            textColor: '#0f172a'
                        },
                    }
                }
            });

            new Chart(document.getElementById('chartPaperQuartile'), {
                type: 'bar',
                data: {
                    labels: safeLabels(paperQuartileLabels),
                    datasets: [{
                        label: 'จำนวน Paper',
                        data: safeValues(paperQuartileValues),
                        backgroundColor: '#4f46e5',
                        borderColor: '#3730a3',
                        borderWidth: 1,
                        hoverBackgroundColor: '#3730a3',
                        borderRadius: 10,
                        barPercentage: 0.78,
                        categoryPercentage: 0.72,
                        maxBarThickness: 72
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: chartAnim,
                    layout: {
                        padding: { left: 8, right: 8, top: 10, bottom: 0 }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: suggestedAxisMax(safeValues(paperQuartileValues), 5),
                            ticks: { precision: 0, color: textColor },
                            grid: { color: gridColor }
                        },
                        x: { ticks: { color: textColor } }
                    },
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            color: '#0f172a',
                            anchor: 'end',
                            align: 'end',
                            offset: 2,
                            clamp: true,
                            font: { weight: '700', size: 11 },
                            formatter: (value) => Number(value || 0) > 0 ? Number(value) : ''
                        }
                    }
                }
            });

            new Chart(document.getElementById('chartPresentationActivity'), {
                type: 'doughnut',
                data: {
                    labels: safeLabels(presentationActivityLabels),
                    datasets: [{ data: safeValues(presentationActivityValues, 1), backgroundColor: ['#ea580c', '#fb7185', '#16a34a', '#0891b2', '#7c3aed', '#ca8a04'] }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: chartAnim,
                    layout: {
                        padding: { top: 38, right: 20, left: 20, bottom: 8 }
                    },
                    radius: '86%',
                    cutout: '50%',
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: {
                            display: false
                        },
                        outlabelsWithLines: {
                            enabled: true,
                            minAngleDeg: 16,
                            minPct: 2.2,
                            maxLabels: 8,
                            minGap: 14,
                            lineColor: '#94a3b8',
                            textColor: '#0f172a'
                        },
                    }
                }
            });

            const textbookCanvas = document.getElementById('chartTextbookPublisher');
            if (textbookCanvas) {
                new Chart(textbookCanvas, {
                    type: 'bar',
                    data: {
                        labels: safeLabels(textbookPublisherLabels),
                        datasets: [{ label: 'จำนวน Textbook', data: safeValues(textbookPublisherValues), backgroundColor: '#0f766e', borderRadius: 8 }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: chartAnim,
                        scales: {
                            x: {
                                beginAtZero: true,
                                suggestedMax: suggestedAxisMax(safeValues(textbookPublisherValues), 5),
                                ticks: { precision: 0, color: textColor },
                                grid: { color: gridColor }
                            },
                            y: { ticks: { color: textColor } }
                        },
                        plugins: {
                            legend: { display: false },
                            datalabels: {
                                color: '#0f172a',
                                anchor: 'end',
                                align: 'right',
                                offset: 4,
                                clamp: true,
                                font: { weight: '700', size: 11 },
                                formatter: (value) => Number(value || 0) > 0 ? Number(value) : ''
                            }
                        }
                    }
                });
            }

            const form = document.getElementById('vizFilters');
            const loading = document.getElementById('filterLoading');
            if (form) {
                let timer = null;
                form.querySelectorAll('.js-auto-filter').forEach((el) => {
                    el.addEventListener('change', () => {
                        window.clearTimeout(timer);
                        if (loading) loading.classList.remove('hidden');
                        timer = window.setTimeout(() => form.requestSubmit(), 160);
                    });
                });
            }

            const fades = document.querySelectorAll('.fade-up');
            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('in');
                            io.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });
                fades.forEach((el) => io.observe(el));
            } else {
                fades.forEach((el) => el.classList.add('in'));
            }

            const modal = document.getElementById('outputItemModal');
            const modalTitle = document.getElementById('outputItemModalTitle');
            const modalBody = document.getElementById('outputItemModalBody');
            const modalClose = document.getElementById('outputItemModalClose');

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const openModal = (title, bodyHtml) => {
                if (!modal || !modalBody || !modalTitle) return;
                modalTitle.textContent = title || 'รายละเอียดผลงาน';
                modalBody.innerHTML = bodyHtml;
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            };

            const closeModal = () => {
                if (!modal || !modalBody) return;
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                modalBody.innerHTML = '';
                document.body.style.overflow = '';
            };

            if (modalClose) {
                modalClose.addEventListener('click', closeModal);
            }
            if (modal) {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) closeModal();
                });
            }
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeModal();
            });

            document.querySelectorAll('.js-output-item').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const title = btn.getAttribute('data-title') || 'รายละเอียดผลงาน';
                    const link = (btn.getAttribute('data-link') || '').trim();
                    const detail = (btn.getAttribute('data-detail') || '').trim();
                    const type = (btn.getAttribute('data-type') || '').trim();
                    const normalizeText = (value) => String(value || '').trim().replace(/\/+$/, '').toLowerCase();
                    const stripUrls = (value) => String(value || '')
                        .replace(/https?:\/\/[^\s<>"'\]\)]+/giu, '')
                        .trim();

                    if (link !== '') {
                        const safeLink = escapeHtml(link);
                        const normalizedLink = normalizeText(link);
                        const normalizedDetail = normalizeText(detail);
                        const detailWithoutUrl = stripUrls(detail);
                        const shouldShowDetail = detail !== ''
                            && normalizedDetail !== normalizedLink
                            && detailWithoutUrl !== '';
                        const detailHtml = shouldShowDetail
                            ? `<div class="viz-detail-box mt-3">${escapeHtml(detail)}</div>`
                            : '';
                        const typeHtml = type !== ''
                            ? `<div class="mt-2 text-xs text-slate-500">ประเภท Output: <span class="font-semibold text-slate-700">${escapeHtml(type)}</span></div>`
                            : '';
                        openModal(
                            title,
                            `<div class="viz-link-card">
                                <div class="text-xs font-semibold uppercase tracking-wide text-blue-800">Link</div>
                                <a href="${safeLink}" target="_blank" rel="noopener" class="mt-1 block break-all text-sm font-medium text-blue-700 underline">${safeLink}</a>
                                <div class="mt-3 flex items-center justify-end">
                                    <a href="${safeLink}" target="_blank" rel="noopener" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">เปิดลิงก์ใหม่</a>
                                </div>
                            </div>
                            ${typeHtml}
                            ${detailHtml}`
                        );
                        return;
                    }

                    if (detail !== '') {
                        const typeHtml = type !== ''
                            ? `<div class="mb-2 text-xs text-slate-500">ประเภท Output: <span class="font-semibold text-slate-700">${escapeHtml(type)}</span></div>`
                            : '';
                        openModal(
                            title,
                            `${typeHtml}<div class="viz-detail-box">${escapeHtml(detail)}</div>`
                        );
                        return;
                    }

                    const typeHtml = type !== ''
                        ? `<div class="mb-2 text-xs text-slate-500">ประเภท Output: <span class="font-semibold text-slate-700">${escapeHtml(type)}</span></div>`
                        : '';
                    openModal(
                        title,
                        `${typeHtml}<div class="viz-detail-box text-slate-500">ไม่มีรายละเอียด</div>`
                    );
                });
            });
        })();
    </script>
@endsection

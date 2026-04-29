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

        $currentYearBe = (int) now()->setTimezone('Asia/Bangkok')->format('Y') + 543;
        $trendPreviousYear = $trendSummary['previous_year'] ?? null;
        $trendLatestYear = $trendSummary['latest_year'] ?? null;
        $trendPreviousCount = (int) ($trendSummary['previous_count'] ?? 0);
        $trendLatestCount = (int) ($trendSummary['latest_count'] ?? 0);
        $trendDelta = (int) ($trendSummary['delta'] ?? 0);
        $trendGrowthPct = $trendSummary['growth_pct'] ?? null;
        $trendLatestYearLabel = ((int) ($trendLatestYear ?? 0) === $currentYearBe) ? 'ปีนี้' : 'ปีล่าสุด';

        $projectOutputCount = (int) ($sourceCounts['project'] ?? 0);
        $nonProjectOutputCount = (int) (($sourceCounts['back_office'] ?? 0) + ($sourceCounts['external'] ?? 0));
        $graphShareRows = collect([
            (object) [
                'label' => 'ผลงานในโครงการวิจัย',
                'count' => $projectOutputCount,
                'pct' => $totalCount > 0 ? round(($projectOutputCount / $totalCount) * 100, 1) : 0.0,
            ],
            (object) [
                'label' => 'Mission',
                'count' => $nonProjectOutputCount,
                'pct' => $totalCount > 0 ? round(($nonProjectOutputCount / $totalCount) * 100, 1) : 0.0,
            ],
        ])->values();
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
        $textbookYearPalette = [
            '#2563eb',
            '#14b8a6',
            '#f97316',
            '#8b5cf6',
            '#ec4899',
            '#0f766e',
            '#d97706',
            '#4f46e5',
            '#059669',
            '#dc2626',
        ];
        $textbookYearColorMap = collect($textbookYearLabels ?? collect())
            ->values()
            ->map(fn ($year) => (string) $year)
            ->values()
            ->mapWithKeys(function ($year, $index) use ($textbookYearPalette) {
                return [$year => $textbookYearPalette[$index % count($textbookYearPalette)]];
            });
        $textbookYearStyleMap = $textbookYearColorMap->mapWithKeys(fn ($color, $year) => [
            $year => sprintf(
                'background-color:%s; border-color:%s; color:%s;',
                $withAlpha($color, '16'),
                $withAlpha($color, '55'),
                $color
            ),
        ]);
        $textbookCoverageStartYear = collect($textbookYearLabels ?? collect())->first();
        $textbookCoverageEndYear = collect($textbookYearLabels ?? collect())->last();
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
        $topSource = $graphShareRows->sortByDesc('count')->first();
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
        $presentationActivityColorMap = [
            'Panel Discussion' => '#ea580c',
            'Poster Presentation' => '#fb7185',
            'Oral Presentation' => '#16a34a',
            'ไม่ระบุ' => '#0891b2',
            'Keynote Speech' => '#7c3aed',
            'World café' => '#ca8a04',
            'Abstract Presentation' => '#0f766e',
            'Other' => '#475569',
        ];
        $presentationCardStyle = function ($presentationType) use ($presentationActivityColorMap, $withAlpha) {
            $type = trim((string) $presentationType);
            $baseColor = $presentationActivityColorMap[$type]
                ?? ($type === '' ? $presentationActivityColorMap['ไม่ระบุ'] : $presentationActivityColorMap['Other']);

            return sprintf(
                'background-color:%s; border-color:%s;',
                $withAlpha($baseColor, '12'),
                $withAlpha($baseColor, '55')
            );
        };
        $presentationBadgeStyle = function ($presentationType) use ($presentationActivityColorMap, $withAlpha) {
            $type = trim((string) $presentationType);
            $baseColor = $presentationActivityColorMap[$type]
                ?? ($type === '' ? $presentationActivityColorMap['ไม่ระบุ'] : $presentationActivityColorMap['Other']);

            return sprintf(
                'background-color:%s; border-color:%s; color:%s;',
                $withAlpha($baseColor, '1A'),
                $withAlpha($baseColor, '55'),
                $baseColor
            );
        };
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <style>
        .viz-page {
            font-family: "Sarabun", "Noto Sans Thai", "Segoe UI", sans-serif;
            background: #ffffff;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
        }
        .panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
        }
        .hero-panel {
            border-radius: 22px;
            border: 1px solid #ebeef3;
            background: #ffffff;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .04);
        }
        .hero-title {
            max-width: 980px;
            font-size: clamp(22px, 2.4vw, 34px);
            line-height: 1.2;
            letter-spacing: -0.03em;
        }
        .hero-kicker {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #64748b;
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
            background: #ffffff;
        }
        .growth-panel {
            background: #ffffff;
        }
        .growth-summary {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #ffffff;
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
        .filter-shell {
            background: #ffffff;
        }
        .filter-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        .filter-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .filter-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #475569;
        }
        .filter-select {
            width: 100%;
            min-height: 50px;
            border: 1px solid #dbe4f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 12px 14px;
            font-size: 14px;
            color: #0f172a;
            transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
        }
        .filter-select:hover {
            border-color: #bfd2ea;
            background: #fbfdff;
        }
        .filter-select:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 4px rgba(96, 165, 250, .14);
        }
        .filter-hint {
            font-size: 12px;
            color: #64748b;
        }
        .filter-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 34px;
            border-radius: 999px;
            background: #f3f4f6;
            padding: 0 14px;
            font-size: 12px;
            color: #475569;
        }
        .filter-chip strong {
            font-weight: 700;
            color: #0f172a;
        }
        .filter-summary-card {
            border: 1px solid #eceff3;
            border-radius: 16px;
            background: #ffffff;
            padding: 14px;
        }
        .section-panel {
            position: relative;
            overflow: hidden;
        }
        .section-panel::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #cbd5e1;
        }
        .section-panel--slate::before { background: linear-gradient(90deg, #64748b, #cbd5e1); }
        .section-panel--teal::before { background: linear-gradient(90deg, #0f766e, #99f6e4); }
        .section-panel--indigo::before { background: linear-gradient(90deg, #4338ca, #c7d2fe); }
        .section-panel--amber::before { background: linear-gradient(90deg, #d97706, #fde68a); }
        .section-panel--rose::before { background: linear-gradient(90deg, #be123c, #fda4af); }
        .section-panel--emerald::before { background: linear-gradient(90deg, #047857, #a7f3d0); }
        .section-tag {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            border-radius: 999px;
            padding: 0 10px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .section-tag--slate {
            background: #f8fafc;
            color: #334155;
        }
        .section-tag--teal {
            background: #f0fdfa;
            color: #0f766e;
        }
        .section-tag--amber {
            background: #fffbeb;
            color: #c2410c;
        }
        .section-tag--indigo {
            background: #eef2ff;
            color: #4338ca;
        }
        .section-tag--rose {
            background: #fff1f2;
            color: #be123c;
        }
        .section-tag--emerald {
            background: #ecfdf5;
            color: #047857;
        }
        .filter-summary-metric {
            font-variant-numeric: tabular-nums;
            font-size: 24px;
            font-weight: 700;
            line-height: 1.1;
            color: #0f172a;
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
        @media (min-width: 768px) {
            .filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1280px) {
            .filter-grid {
                grid-template-columns: 1.1fr 1.4fr 1fr 1fr auto;
            }
        }
    </style>

    <div class="viz-page min-h-screen pb-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 md:py-8">
            <section class="fade-up hero-panel section-panel section-panel--slate p-7 md:p-10">
                <div class="hero-kicker">Research Outputs Overview</div>
                <h1 class="hero-title mt-3 font-semibold text-slate-900">{{ $pageTitle }}</h1>

            </section>

            <section class="fade-up panel filter-shell section-panel section-panel--slate p-5 md:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <div class="section-tag section-tag--slate">Visualization Filters</div>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">เลือกช่วงข้อมูลที่อยากดู</h2>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600">ตัวกรองนี้จะเปลี่ยนทั้งกราฟ ตัวเลขสรุป และรายการด้านล่างพร้อมกัน โดยประเภท EA จะแสดงชื่อเต็มเพื่ออ่านได้ทันที</p>
                    </div>
                    <div class="filter-summary-card lg:w-[280px]">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Active Scope</div>
                        <div class="filter-summary-metric mt-2">{{ number_format($totalCount) }}</div>
                        <div class="mt-1 text-sm font-medium text-slate-700">ผลงานในชุดข้อมูลที่เลือก</div>
                        <div class="mt-2 text-xs text-slate-500">แหล่งหลัก: {{ $topSource?->label ?? '-' }} · ประเภทหลัก: {{ $topType?->label ?? '-' }}</div>
                    </div>
                </div>

                {{-- <div class="filter-chip-row mt-4">
                    <span class="filter-chip"><strong>แหล่งข้อมูล</strong> {{ $selectedSource !== '' ? ($sourceLabelMap[$selectedSource] ?? $selectedSource) : 'ทั้งหมด' }}</span>
                    <span class="filter-chip"><strong>ประเภท</strong> {{ $selectedType !== '' ? ($selectedType . ' - ' . ($eaFullNameMap[$selectedType] ?? ('ประเภท ' . $selectedType))) : 'ทั้งหมด' }}</span>
                    <span class="filter-chip"><strong>ปี</strong>
                        @if (filled($selectedYearFrom) || filled($selectedYearTo))
                            {{ $selectedYearFrom ?: '...' }} - {{ $selectedYearTo ?: '...' }}
                        @else
                            ทั้งหมด
                        @endif
                    </span>
                </div> --}}

                <form id="vizFilters" method="GET" action="{{ route('outputs.visualization') }}" class="filter-grid mt-5">
                    <label class="filter-field">
                        <span class="filter-label">แหล่งข้อมูล</span>
                        <select name="source" class="js-auto-filter filter-select">
                            <option value="">ทั้งหมด</option>
                            @foreach ($sourceLabelMap as $sourceKey => $sourceLabel)
                                <option value="{{ $sourceKey }}" @selected($selectedSource === $sourceKey)>{{ $sourceLabel }}</option>
                            @endforeach
                        </select>
                        <span class="filter-hint">เลือกว่าต้องการดูเฉพาะในโครงการ Mission หรือ Extra</span>
                    </label>
                    <label class="filter-field">
                        <span class="filter-label">ประเภทผลงาน (EA)</span>
                        <select name="type" class="js-auto-filter filter-select">
                            <option value="">ทั้งหมด</option>
                            @foreach ($typeOptions as $typeCode)
                                <option value="{{ $typeCode }}" @selected($selectedType === $typeCode)>{{ $typeCode }} - {{ $eaFullNameMap[$typeCode] ?? ('ประเภท ' . $typeCode) }}</option>
                            @endforeach
                        </select>
                        <span class="filter-hint">แสดงชื่อเต็มของ EA เพื่อแยกประเภทได้เร็วขึ้นโดยไม่ต้องจำรหัส</span>
                    </label>
                    <label class="filter-field">
                        <span class="filter-label">ปีเริ่ม (พ.ศ.)</span>
                        <select name="year_from" class="js-auto-filter filter-select">
                            <option value="">ทั้งหมด</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected((int) $selectedYearFrom === (int) $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                        <span class="filter-hint">ใช้กำหนดขอบซ้ายของช่วงเวลา</span>
                    </label>
                    <label class="filter-field">
                        <span class="filter-label">ปีสิ้นสุด (พ.ศ.)</span>
                        <select name="year_to" class="js-auto-filter filter-select">
                            <option value="">ทั้งหมด</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected((int) $selectedYearTo === (int) $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                        <span class="filter-hint">ใช้กำหนดขอบขวาของช่วงเวลา</span>
                    </label>
                    <div class="flex flex-col justify-end gap-3">
                        <div id="filterLoading" class="hidden rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-medium text-blue-700">กำลังอัปเดตข้อมูล...</div>
                        <a href="{{ route('outputs.visualization') }}" class="inline-flex min-h-[50px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">ล้างตัวกรอง</a>
                    </div>
                </form>
            </section>

            <section class="fade-up panel section-panel section-panel--teal p-5 md:p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <div class="section-tag section-tag--teal">Theme Output</div>
                        <h2 class="text-lg font-semibold text-slate-900">กลุ่มเนื้อหา  (6 กลุ่ม) จาก Output</h2>
                        <p class="mt-1 text-sm text-slate-500">สรุปจำนวนผลงาน แยกตามกลุ่มเนื้อหา พร้อมรายการผลงานในแต่ละกลุ่ม</p>
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
                                                        <div class="mt-1 flex flex-wrap items-center gap-2">
                                                            <span class="output-type-chip" style="{{ $outputTypeStyle($item->type_code) }}">{{ $item->type_code }} - {{ $item->type_label }}</span>
                                                            <span class="text-[11px] font-medium text-slate-500">ปี {{ $item->year_be ?? '-' }}</span>
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
                <article class="fade-up panel section-panel section-panel--indigo chart-sticky h-full p-4 md:p-5">
                    <div class="section-tag section-tag--indigo">Output Trend</div>
                    <h2 class="text-base font-semibold text-slate-900">ผลงานที่เกิดขึ้น <span class="ml-1 text-sm font-medium text-slate-500">({{ number_format($totalCount) }} รายการ)</span></h2>
                    <div class="mt-4 h-[360px]"><canvas id="chartOutputsByYear"></canvas></div>
                </article>
                <article class="fade-up panel section-panel section-panel--slate growth-panel flex h-full flex-col justify-center p-5 md:p-6">
                    <div class="section-tag section-tag--slate">Summary</div>
                    <div class="growth-summary mt-3 px-4 py-3">
                        <div class="metric mt-1 text-2xl font-semibold text-slate-900">
                            มีผลงานทั้งหมด {{ number_format($totalCount) }} รายการ
                        </div>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        @if ($topSource)
                            แหล่งข้อมูลหลักคือ {{ $topSource->label }} {{ number_format((int) $topSource->count) }} รายการ
                            ({{ number_format((float) $topSource->pct, 1) }}%)
                        @else
                            ไม่มีข้อมูลในช่วงที่เลือก
                        @endif
                    </p>
                    <div class="mt-4 space-y-2">
                        @forelse ($graphShareRows as $source)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-slate-900">{{ $source->label }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ number_format((float) $source->pct, 1) }}% ของผลรวม</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="metric text-xl font-semibold text-slate-900">{{ number_format((int) $source->count) }}</div>
                                        <div class="text-xs text-slate-500">รายการ</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 px-4 py-5 text-sm text-slate-500">ไม่มีข้อมูลในช่วงที่เลือก</div>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="scene scene--balanced grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                <article class="fade-up panel section-panel section-panel--amber chart-sticky h-full p-4 md:p-5">
                    <div class="section-tag section-tag--amber">Type Of Output</div>
                    <h2 class="text-base font-semibold text-slate-900">ประเภทผลงานที่เกิดขึ้นจากหลายมิติ <span class="ml-1 text-sm font-medium text-slate-500">({{ number_format($typeSectionTotal) }} รายการ)</span></h2>
                    <div class="type-chart-wrap mt-4"><canvas id="chartOutputTypes"></canvas></div>
                </article>
                <article class="fade-up panel section-panel section-panel--amber type-right-panel p-5 md:p-6">
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
                <article class="fade-up panel section-panel section-panel--rose chart-sticky flex h-full flex-col p-4 md:p-5">
                    <div class="section-tag section-tag--rose">Publications</div>
                    <h2 class="text-base font-semibold text-slate-900">ผลงานการตีพิมพ์ต่างๆ <span class="ml-1 text-sm font-medium text-slate-500">({{ number_format($paperCount) }} รายการ)</span></h2>
                    <div class="mt-4 flex flex-1 items-center justify-center">
                        <div class="h-[360px] w-full max-w-[880px]"><canvas id="chartPaperRanking"></canvas></div>
                    </div>
                </article>
                <article class="fade-up panel section-panel section-panel--rose flex h-full flex-col p-5 md:p-6">
                    <h3 class="text-xl font-semibold text-slate-900">ตีพิมพ์ไป</h3>
                    <div class="focus-kpi mt-3 px-4 py-3">
                        <div class="metric mt-1 text-2xl font-semibold text-slate-900">{{ number_format($paperCount) }} ผลงาน</div>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">Top Journal: {{ $paperSummary['top_journal'] ?: '-' }} · Avg IF: {{ number_format((float) ($paperSummary['avg_if'] ?? 0), 2) }}</p>
                    <p class="mt-1 text-xs text-slate-500">แสดงทั้งหมด {{ number_format((int) (($paperSummary['all'] ?? collect())->count())) }} รายการ</p>
                    <div class="paper-record-scroll mt-4 flex-1 space-y-2">
                        @forelse (($paperSummary['all'] ?? collect()) as $row)
                            <button
                                type="button"
                                class="js-output-item block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-left text-slate-700 hover:bg-slate-50"
                                data-title="{{ $row->title }}"
                                data-link="{{ $row->modal_link ?? '' }}"
                                data-detail="{{ $row->modal_detail ?? '' }}"
                                data-type="EA101 - Research Paper or Journal Article"
                            >
                                <div class="font-medium line-clamp-2">{{ $row->title }}</div>
                                <div class="mt-1 text-xs text-slate-600">
                                    ผู้ทำ: {{ collect($row->authors ?? [])->filter()->implode(', ') ?: 'ไม่ระบุผู้ทำ' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $row->journal ?: 'ไม่ระบุวารสาร' }} • {{ $row->quartile ?: 'ไม่ระบุ Ranking' }} • IF {{ is_numeric($row->impact_factor) ? number_format((float) $row->impact_factor, 2) : '-' }} • {{ $row->year_be ?: '-' }}
                                </div>
                            </button>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-200 px-3 py-4 text-xs text-slate-500">ไม่มีข้อมูล</div>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="scene scene--balanced grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                <article class="fade-up panel section-panel section-panel--emerald chart-sticky flex h-full flex-col p-4 md:p-5">
                    <div class="section-tag section-tag--emerald">Presentations</div>
                    <h2 class="text-base font-semibold text-slate-900">ผลงานการนำเสนอต่างๆ <span class="ml-1 text-sm font-medium text-slate-500">({{ number_format($presentationCount) }} รายการ)</span></h2>
                    <div class="mt-4 flex flex-1 items-center justify-center">
                        <div class="h-[360px] w-full max-w-[760px]"><canvas id="chartPresentationActivity"></canvas></div>
                    </div>
                </article>
                <article class="fade-up panel section-panel section-panel--emerald flex h-full flex-col p-5 md:p-6">
                    <h3 class="text-xl font-semibold text-slate-900">นำเสนอไป</h3>
                    <div class="focus-kpi mt-3 px-4 py-3">
                        <div class="metric mt-1 text-2xl font-semibold text-slate-900">{{ number_format($presentationCount) }} ผลงาน</div>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">Top Conference: {{ $presentationSummary['top_conference'] ?: '-' }} · Top Presenter: {{ $presentationSummary['top_presenter'] ?: '-' }}</p>
                    <p class="mt-1 text-xs text-slate-500">แสดงทั้งหมด {{ number_format((int) (($presentationSummary['all'] ?? collect())->count())) }} รายการ</p>
                    <div class="paper-record-scroll mt-4 flex-1 space-y-2">
                        @forelse (($presentationSummary['all'] ?? collect()) as $row)
                            <button
                                type="button"
                                class="js-output-item block w-full rounded-xl border px-3 py-3 text-left text-slate-700 transition hover:-translate-y-[1px]"
                                style="{{ $presentationCardStyle($row->presentation_type) }}"
                                data-title="{{ $row->title }}"
                                data-link="{{ $row->modal_link ?? '' }}"
                                data-detail="{{ $row->modal_detail ?? '' }}"
                                data-type="EA103 - Presentations in Academic Conferences"
                            >
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold" style="{{ $presentationBadgeStyle($row->presentation_type) }}">
                                        {{ $row->presentation_type ?: 'ไม่ระบุประเภท' }}
                                    </span>
                                    <span class="text-[11px] font-medium text-slate-500">ปี {{ $row->year_be ?: '-' }}</span>
                                </div>
                                <div class="font-medium line-clamp-2">{{ $row->title }}</div>
                                <div class="mt-2 grid gap-1 text-xs">
                                    <div class="text-slate-500">เวที: <span class="font-medium text-slate-700">{{ $row->conference_name ?: 'ไม่ระบุเวที' }}</span></div>
                                    <div class="text-slate-500">ปี: <span class="font-medium text-slate-700">{{ $row->year_be ?: '-' }}</span></div>
                                    <div class="text-slate-500">ผู้นำเสนอ: <span class="font-medium text-slate-700">{{ $row->presenter_name ?: 'ไม่ระบุผู้นำเสนอ' }}</span></div>
                                </div>
                            </button>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-200 px-3 py-4 text-xs text-slate-500">ไม่มีข้อมูล</div>
                        @endforelse
                    </div>
                </article>
            </section>

            @if ($textbookCount > 0)
                <section class="scene scene--balanced grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                    <article class="fade-up panel section-panel section-panel--teal chart-sticky flex h-full flex-col p-4 md:p-5">
                        <div class="section-tag section-tag--teal">Textbooks</div>
                        <h2 class="text-base font-semibold text-slate-900">ผลงาน Textbooks <span class="ml-1 text-sm font-medium text-slate-500">({{ number_format($textbookCount) }} รายการ)</span></h2>
                        <div class="mt-4 flex flex-1 items-center">
                            <div class="h-[340px] w-full"><canvas id="chartTextbookYear"></canvas></div>
                        </div>
                    </article>
                    <article class="fade-up panel section-panel section-panel--teal flex h-full flex-col p-5 md:p-6">
                        <h3 class="text-xl font-semibold text-slate-900">มีผลงาน</h3>
                        <div class="focus-kpi mt-3 px-4 py-3">
                            <div class="metric mt-1 text-2xl font-semibold text-slate-900">{{ number_format($textbookCount) }} Textbooks</div>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">ครอบคลุมปี {{ $textbookCoverageStartYear ?: '-' }} ถึง {{ $textbookCoverageEndYear ?: '-' }} · ปีที่มีจำนวนสูงสุด {{ $textbookSummary['peak_year'] ?: '-' }} ({{ number_format((int) ($textbookSummary['peak_year_count'] ?? 0)) }} รายการ)</p>
                        <p class="mt-1 text-xs text-slate-500">รายการที่แสดงล่าสุด {{ number_format($textbookLatestCount) }} รายการ</p>
                        <div class="mt-4 flex-1 space-y-2">
                            @forelse (($textbookSummary['latest'] ?? collect()) as $row)
                                <button
                                    type="button"
                                    class="js-output-item block w-full rounded-xl border px-3 py-3 text-left text-slate-700 transition hover:-translate-y-[1px]"
                                    style="{{ isset($textbookYearStyleMap[(string) ($row->year_be ?? '')]) ? $textbookYearStyleMap[(string) ($row->year_be ?? '')] . ' background-image:linear-gradient(180deg, rgba(255,255,255,0.78), rgba(255,255,255,0.94));' : 'border-color:#e2e8f0; background:#ffffff;' }}"
                                    data-title="{{ $row->title }}"
                                    data-link="{{ $row->modal_link ?? '' }}"
                                    data-detail="{{ $row->modal_detail ?? '' }}"
                                    data-type="EA119 - Textbook / Book Chapter"
                                >
                                    <div class="font-medium line-clamp-2">{{ $row->title }}</div>
                                    <div class="mt-2 grid gap-1 text-xs">
                                        <div class="text-slate-500">สำนักพิมพ์: <span class="font-medium text-slate-700">{{ $row->publisher_name ?: 'ไม่ระบุสำนักพิมพ์' }}</span></div>
                                        <div class="text-slate-500">หนังสือ: <span class="font-medium text-slate-700">{{ $row->book_title ?: 'ไม่ระบุชื่อหนังสือ' }}</span></div>
                                        <div class="text-slate-500">ผู้แต่ง: <span class="font-medium text-slate-700">{{ filled($row->authors ?? []) ? implode(', ', array_filter((array) ($row->authors ?? []))) : 'ไม่ระบุผู้แต่ง' }}</span></div>
                                    </div>
                                </button>
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
            const paperRankingLabels = @json($paperRankingLabels);
            const paperRankingValues = @json($paperRankingValues);
            const presentationActivityLabels = @json($presentationActivityLabels);
            const presentationActivityValues = @json($presentationActivityValues);
            const textbookYearLabels = @json($textbookYearLabels);
            const textbookYearValues = @json($textbookYearValues);
            const presentationChartColors = ['#ea580c', '#fb7185', '#16a34a', '#0891b2', '#7c3aed', '#ca8a04', '#0f766e', '#475569'];
            const presentationChartBorderColors = ['#c2410c', '#e11d48', '#15803d', '#0e7490', '#6d28d9', '#a16207', '#115e59', '#334155'];
            const textbookYearColors = @json(collect($textbookYearLabels ?? collect())->map(fn ($year) => $textbookYearColorMap[(string) $year] ?? '#0f766e')->values());

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
                            label: 'Mission',
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

            new Chart(document.getElementById('chartPaperRanking'), {
                type: 'bar',
                data: {
                    labels: safeLabels(paperRankingLabels),
                    datasets: [{
                        label: 'จำนวน Paper',
                        data: safeValues(paperRankingValues),
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
                            suggestedMax: suggestedAxisMax(safeValues(paperRankingValues), 5),
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
                    datasets: [{
                        data: safeValues(presentationActivityValues, 1),
                        backgroundColor: safeColors(presentationChartColors),
                        hoverBackgroundColor: safeColors(presentationChartColors),
                        borderColor: safeColors(presentationChartBorderColors),
                        hoverBorderColor: safeColors(presentationChartBorderColors),
                        borderWidth: 1.25
                    }]
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
                            color: '#ffffff',
                            font: { weight: '700', size: 11 },
                            formatter: (value) => Number(value || 0) > 0 ? Number(value) : ''
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

            const textbookCanvas = document.getElementById('chartTextbookYear');
            if (textbookCanvas) {
                new Chart(textbookCanvas, {
                    type: 'bar',
                    data: {
                        labels: safeLabels(textbookYearLabels),
                        datasets: [{
                            label: 'จำนวน Textbook',
                            data: safeValues(textbookYearValues),
                            backgroundColor: safeColors(textbookYearColors),
                            borderColor: safeColors(textbookYearColors),
                            borderWidth: 1,
                            borderRadius: 8,
                            hoverBackgroundColor: safeColors(textbookYearColors),
                            maxBarThickness: 68
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: chartAnim,
                        scales: {
                            y: {
                                beginAtZero: true,
                                suggestedMax: suggestedAxisMax(safeValues(textbookYearValues), 5),
                                ticks: { precision: 0, color: textColor },
                                grid: { color: gridColor }
                            },
                            x: {
                                ticks: { color: textColor },
                                grid: { color: gridColor }
                            }
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

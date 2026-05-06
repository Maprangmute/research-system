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
        $networkingCount = (int) ($networkingSummary['count'] ?? 0);
        $typeSectionTotal = (int) $typeCounts->sum();
        $paperLatestCount = (int) (($paperSummary['latest'] ?? collect())->count());
        $presentationLatestCount = (int) (($presentationSummary['latest'] ?? collect())->count());
        $textbookLatestCount = (int) (($textbookSummary['latest'] ?? collect())->count());
        $networkingLatestCount = (int) (($networkingSummary['latest'] ?? collect())->count());
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
        $graphShareRows = collect([
            (object) [
                'label' => 'ผลงานในโครงการวิจัย',
                'count' => $projectOutputCount,
                'pct' => $totalCount > 0 ? round(($projectOutputCount / $totalCount) * 100, 1) : 0.0,
            ],
            (object) [
                'label' => 'Mission',
                'count' => (int) ($sourceCounts['back_office'] ?? 0),
                'pct' => $totalCount > 0 ? round((((int) ($sourceCounts['back_office'] ?? 0)) / $totalCount) * 100, 1) : 0.0,
            ],
            (object) [
                'label' => 'Extra',
                'count' => (int) ($sourceCounts['external'] ?? 0),
                'pct' => $totalCount > 0 ? round((((int) ($sourceCounts['external'] ?? 0)) / $totalCount) * 100, 1) : 0.0,
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
        $networkingCoverageStartYear = collect($networkingYearLabels ?? collect())->first();
        $networkingCoverageEndYear = collect($networkingYearLabels ?? collect())->last();
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
            'Moderator' => '#2563eb',
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
        $policyImpact = $policyImpact ?? [];
        $policyDataSourceCounts = collect($policyImpact['data_source_counts'] ?? []);
        $policyDataSourceTotal = max(1, (int) $policyDataSourceCounts->sum());
        $policyDataSourceTrend = $policyImpact['data_source_trend'] ?? ['years' => collect(), 'series' => collect()];
        $policyImpactOutputs = collect($policyImpact['impact_outputs'] ?? []);
        $policyHta = $policyImpact['hta'] ?? [];
        $policyClinicalLed = $policyImpact['clinical_led'] ?? [];
        $policyWeakness = $policyImpact['weakness'] ?? [];
        $policyImpactOutputItems = collect($policyImpact['impact_output_items'] ?? []);
        $policyImpactTrend = $policyImpact['impact_trend'] ?? ['years' => collect(), 'series' => collect()];
        $policyFrameworkTrend = $policyImpact['framework_trend'] ?? ['years' => collect(), 'series' => collect(), 'interpretation' => collect()];
        $policyFrameworkInterpretation = collect($policyFrameworkTrend['interpretation'] ?? []);
        $policyFrameworkSummary = $policyFrameworkTrend['summary'] ?? null;
        $policyPeopleHighlights = collect($policyImpact['people_highlights'] ?? []);
        $contributionNetwork = $policyImpact['contribution_network'] ?? [];
        $peopleTypeMix = collect($contributionNetwork['people_type_mix'] ?? []);
        $expertTrend = $contributionNetwork['expert_trend'] ?? ['years' => collect(), 'people' => collect(), 'new_people' => collect(), 'projects' => collect()];
        $newExpertsByYear = collect($contributionNetwork['new_experts_by_year'] ?? []);
        $clinicalLedIntegration = $contributionNetwork['clinical_led_integration'] ?? [];
        $clinicalLedIntegrationTrend = $clinicalLedIntegration['trend'] ?? ['years' => collect(), 'people' => collect(), 'projects' => collect(), 'ageing_projects' => collect(), 'rwe_projects' => collect()];
        $clinicalLedPeopleByYear = collect($clinicalLedIntegration['people_by_year'] ?? []);
        $clinicalLedIntegrationThemes = collect($clinicalLedIntegration['themes'] ?? []);
        $clinicalLedIntegrationRoleStats = collect($clinicalLedIntegration['role_stats'] ?? []);
        $clinicalLedRoleSummaryText = $clinicalLedIntegrationRoleStats
            ->map(fn ($role) => ($role->label ?? '-') . ' ' . number_format((int) ($role->people ?? 0)) . ' คน')
            ->implode(' · ');
        $professionMix = collect($contributionNetwork['profession_mix'] ?? []);
        $professionTypeMatrix = collect($contributionNetwork['profession_type_matrix'] ?? []);
        $expertTrendRows = collect($expertTrend['years'] ?? [])
            ->values()
            ->map(function ($year, $index) use ($expertTrend) {
                return (object) [
                    'year' => (int) $year,
                    'people' => (int) (collect($expertTrend['people'] ?? [])->values()->get($index) ?? 0),
                    'new_people' => (int) (collect($expertTrend['new_people'] ?? [])->values()->get($index) ?? 0),
                    'projects' => (int) (collect($expertTrend['projects'] ?? [])->values()->get($index) ?? 0),
                ];
            })
            ->values();
        $expertTrendMax = max(1, (int) $expertTrendRows->max('people'));
        $expertTrendLatest = $expertTrendRows->last();
        $expertTrendTotalPeople = (int) ($expertTrend['total_people'] ?? 0);
        $expertTrendTotalProjects = (int) ($expertTrend['total_projects'] ?? 0);
        $policyDataLabels = [
            'Real-World data' => ['short' => 'Real-World data', 'tag' => 'Existing / routine data', 'color' => '#2563eb'],
            'Primary data collection' => ['short' => 'Primary data collection', 'tag' => 'New data collection', 'color' => '#0f766e'],
        ];
        $policyDataSourcePeak = $policyDataSourceCounts->sortDesc()->keys()->first();
        $policyDataSourcePeakCount = (int) ($policyDataSourceCounts[$policyDataSourcePeak] ?? 0);
        $policyImpactCodeLabels = [
            'EA109' => 'Change in Practices / Procedures / Policy',
            'EA102' => 'Reports and White Papers',
            'EA106' => 'Policy Recommendations',
        ];
        $policyImpactTrendRows = collect($policyImpactTrend['years'] ?? [])
            ->values()
            ->map(function ($year, $index) use ($policyImpactTrend, $policyImpactCodeLabels) {
                $series = collect($policyImpactTrend['series'] ?? []);
                $byCode = collect($policyImpactCodeLabels)->mapWithKeys(fn ($label, $code) => [
                    $code => (int) (collect($series[$code] ?? [])->values()->get($index) ?? 0),
                ]);

                return (object) [
                    'year' => (int) $year,
                    'total' => (int) $byCode->sum(),
                    'by_code' => $byCode,
                ];
            })
            ->values();
        $policyImpactTrendSummaryRows = $policyImpactTrendRows
            ->filter(fn ($row) => (int) ($row->year ?? 0) < (int) $currentYearBe)
            ->values();
        if ($policyImpactTrendSummaryRows->isEmpty()) {
            $policyImpactTrendSummaryRows = $policyImpactTrendRows;
        }
        $policyImpactTrendFirst = $policyImpactTrendSummaryRows->first();
        $policyImpactTrendLast = $policyImpactTrendSummaryRows->last();
        $policyImpactTrendPeak = $policyImpactTrendSummaryRows
            ->sortByDesc(fn ($row) => (int) ($row->total ?? 0))
            ->first();
        $policyImpactTopCode = collect($policyImpactOutputs ?? [])
            ->sortDesc()
            ->keys()
            ->first();
        $policyImpactTopLabel = $policyImpactCodeLabels[$policyImpactTopCode] ?? null;
        $policyImpactTrendDirection = (int) ($policyImpactTrendLast?->total ?? 0) > (int) ($policyImpactTrendFirst?->total ?? 0)
            ? 'เพิ่มขึ้น'
            : ((int) ($policyImpactTrendLast?->total ?? 0) < (int) ($policyImpactTrendFirst?->total ?? 0) ? 'ลดลง' : 'ทรงตัว');
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
        .policy-shell {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }
        .equation-box {
            border: 1px solid #dbe4f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 16px;
        }
        .equation-text {
            font-size: clamp(18px, 2vw, 28px);
            line-height: 1.25;
            letter-spacing: 0;
        }
        .policy-pill {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            border-radius: 999px;
            border: 1px solid #dbe4f0;
            background: #fff;
            padding: 0 10px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
        }
        .policy-axis-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 14px;
        }
        .policy-axis-card mark {
            border-radius: 6px;
            background: #fef3c7;
            color: #92400e;
            padding: 0 5px;
        }
        .policy-kpi {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 14px;
        }
        .policy-mini-bar {
            height: 8px;
            overflow: hidden;
            border-radius: 999px;
            background: #e2e8f0;
        }
        .policy-mini-bar > span {
            display: block;
            height: 100%;
            border-radius: inherit;
        }
        .policy-project-link {
            display: block;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            padding: 10px 12px;
            transition: border-color .18s ease, background-color .18s ease;
        }
        .policy-project-link:hover {
            border-color: #bfdbfe;
            background: #f8fbff;
        }
        .weakness-card {
            border: 1px solid #fecdd3;
            border-radius: 14px;
            background: #fff7f8;
            padding: 14px;
        }
        .policy-brief {
            border: 1px solid #dbe4f0;
            border-radius: 16px;
            background: #ffffff;
            padding: 18px;
        }
        .policy-flow {
            display: grid;
            gap: 10px;
        }
        .policy-flow-step {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            padding: 10px 12px;
        }
        .policy-flow-step__label {
            font-size: 11px;
            font-weight: 700;
            color: #4f46e5;
        }
        .policy-section-title {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .policy-list-compact {
            max-height: 520px;
            overflow-y: auto;
            padding-right: 4px;
        }
        .policy-stat-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        .policy-mini-list {
            max-height: 220px;
            overflow-y: auto;
            padding-right: 4px;
        }
        .policy-mini-link {
            display: block;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            padding: 9px 10px;
        }
        .policy-people-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        .policy-person-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 12px;
        }
        .policy-trend-panel {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 12px;
        }
        .contribution-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 14px;
        }
        .framework-card {
            border: 1px solid var(--framework-border, #e2e8f0);
            border-radius: 14px;
            background: var(--framework-bg, #f8fafc);
            padding: 16px;
            text-align: left;
            transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
            cursor: pointer;
            min-height: 148px;
        }
        .framework-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(15, 23, 42, .10);
            border-color: var(--framework-accent, #94a3b8);
        }
        .framework-card:focus-visible {
            outline: 3px solid color-mix(in srgb, var(--framework-accent, #334155) 28%, transparent);
            outline-offset: 2px;
        }
        .framework-card__accent {
            color: var(--framework-accent, #334155);
        }
        .framework-card__eyebrow {
            color: var(--framework-accent, #334155);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
        }
        .contribution-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(1, minmax(0, 1fr));
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
        @media (max-width: 640px) {
            .viz-page {
                width: 100%;
                margin-left: 0;
                margin-right: 0;
                overflow-x: hidden;
                overflow-wrap: anywhere;
            }
            .viz-page > div {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
            .viz-page *,
            .viz-page *::before,
            .viz-page *::after {
                min-width: 0;
            }
            .fade-up {
                opacity: 1;
                transform: none;
            }
            .panel,
            .hero-panel,
            .policy-axis-card,
            .policy-brief,
            .policy-trend-panel,
            .contribution-card,
            .framework-card {
                border-radius: 12px;
            }
            .hero-panel,
            .panel {
                padding: 16px !important;
            }
            .hero-title {
                font-size: clamp(20px, 6vw, 26px);
                letter-spacing: 0;
            }
            .hero-kicker {
                letter-spacing: .12em;
                line-height: 1.35;
            }
            .policy-brief,
            .policy-axis-card,
            .policy-trend-panel,
            .contribution-card,
            .framework-card {
                padding: 12px;
            }
            .section-tag,
            .policy-pill {
                max-width: 100%;
                white-space: normal;
                line-height: 1.3;
            }
            .policy-pill {
                min-height: 0;
                padding: 6px 10px;
            }
            .policy-section-title {
                align-items: flex-start;
            }
            .framework-card {
                min-height: auto;
            }
            .viz-page [class*="h-[340px]"],
            .viz-page [class*="h-[360px]"],
            .viz-page [class*="h-[380px]"] {
                height: 300px !important;
            }
            .type-chart-wrap {
                height: 300px;
            }
            .scroll-card,
            .paper-record-scroll,
            .policy-list-compact,
            .policy-mini-list {
                max-height: 340px;
            }
            .viz-modal {
                align-items: stretch;
                padding: 8px;
            }
            .viz-modal-card {
                width: 100%;
                max-height: calc(100vh - 16px);
                border-radius: 12px;
            }
            .viz-modal-head {
                align-items: flex-start;
                padding: 10px 12px;
            }
            .viz-modal-body {
                padding: 12px;
            }
            #frameworkTrendModal {
                align-items: stretch !important;
                padding: 8px !important;
            }
            #frameworkTrendModalCard {
                width: 100% !important;
                max-height: calc(100vh - 16px) !important;
                border-radius: 12px !important;
            }
            #frameworkTrendModalHeader {
                padding: 12px !important;
            }
            #frameworkTrendModalTitle {
                font-size: 18px;
                line-height: 1.25;
            }
            #frameworkTrendModalSubtitle {
                font-size: 14px;
            }
            #frameworkTrendModalSubtitle .text-2xl {
                font-size: 20px !important;
                line-height: 1.2 !important;
            }
            #frameworkTrendModalSubtitle .text-base {
                font-size: 14px !important;
                line-height: 1.35 !important;
            }
            #frameworkTrendModalBody {
                max-height: calc(100vh - 112px) !important;
                padding: 12px !important;
            }
        }
        @media (min-width: 768px) {
            .filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .policy-stat-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .policy-people-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .contribution-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (min-width: 1280px) {
            .filter-grid {
                grid-template-columns: 1.1fr 1.4fr 1fr 1fr auto;
            }
        }
    </style>

    <div class="viz-page min-h-screen pb-10">
        <div class="mx-auto w-full max-w-[1600px] space-y-6 px-4 py-6 md:px-8 md:py-8 2xl:px-10">
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

            <section class="fade-up panel section-panel section-panel--indigo policy-shell p-5 md:p-6">
                <div class="policy-brief">
                    <div class="section-tag section-tag--indigo">การเชื่อมโยงงานวิจัยสู่การตัดสินใจเชิงนโยบาย</div>
                    <div class="max-w-5xl">
                        <h2 class="mt-4 text-3xl font-semibold leading-tight text-slate-900 md:text-4xl">
                            จากโจทย์บริการจริง ไปสู่ผลต่อระบบบริการและนโยบาย
                        </h2>
                        <p class="mt-4 max-w-4xl text-base leading-relaxed text-slate-600">
                            ดูว่าโครงการและผลงานของหน่วยสร้าง evidence จากบริบทจริงของศิริราช
                            และเชื่อมต่อไปสู่การตัดสินใจเชิงนโยบายได้มากเพียงใด
                        </p>
                    </div>
                </div>

                <div class="policy-axis-card mt-4">
                    <div class="policy-section-title">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Policy-Relevant Decision-Making Trend</h3>
                            <p class="mt-1 text-sm text-slate-600">ดูแนวโน้มว่ากลุ่มโครงการและผลงานเชื่อมจาก clinical insight และ evidence ไปถึง communication และ policy impact แค่ไหน</p>
                        </div>
                        <span class="policy-pill">แสดงเป็นจำนวนต่อปี</span>
                    </div>
                    <div class="grid gap-4 xl:grid-cols-[1.35fr_0.65fr]">
                        <div class="policy-trend-panel flex h-full flex-col">
                            <div class="h-[360px]">
                                <canvas id="chartPolicyFrameworkTrend"></canvas>
                            </div>
                            @if ($policyFrameworkSummary)
                                <div class="mt-4 flex-1 rounded-2xl border border-sky-300 bg-sky-50 px-4 py-4">
                                    <div class="text-sm font-semibold text-sky-950">สรุปจากแนวโน้ม</div>
                                    <div class="mt-8 grid gap-2">
                                        <div class="rounded-xl bg-white/80 px-3 py-2">
                                            <div class="text-[11px] font-semibold text-sky-700">เกิดอะไรขึ้น</div>
                                            <div class="mt-1 text-sm font-semibold leading-relaxed text-slate-900">{{ $policyFrameworkSummary->headline }}</div>
                                        </div>
                                        <div class="rounded-xl bg-white/80 px-3 py-2">
                                            <div class="text-[11px] font-semibold text-sky-700">แปลว่าอะไร</div>
                                            <div class="mt-1 text-sm leading-relaxed text-slate-700">{{ $policyFrameworkSummary->body }}</div>
                                        </div>
                                        <div class="rounded-xl bg-white/80 px-3 py-2">
                                            <div class="text-[11px] font-semibold text-sky-700">ช่องว่างหลัก</div>
                                            <div class="mt-1 text-sm leading-relaxed text-slate-700">{{ $policyFrameworkSummary->focus }}</div>
                                        </div>
                                    </div>
                                    @if (!empty($policyFrameworkSummary->note))
                                        <div class="mt-3 rounded-xl bg-white/65 px-3 py-2 text-xs leading-relaxed text-slate-500">{{ $policyFrameworkSummary->note }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="grid gap-2">
                            @foreach ($policyFrameworkInterpretation as $item)
                                @php
                                    $frameworkStyles = [
                                        'trust' => ['bg' => '#eef2ff', 'border' => '#c7d2fe', 'accent' => '#4f46e5'],
                                        'truth' => ['bg' => '#ecfdf5', 'border' => '#99f6e4', 'accent' => '#0f766e'],
                                        'policy_impact' => ['bg' => '#fff7ed', 'border' => '#fed7aa', 'accent' => '#c2410c'],
                                    ];
                                    $style = $frameworkStyles[$item->key] ?? ['bg' => '#f8fafc', 'border' => '#e2e8f0', 'accent' => '#334155'];
                                @endphp
                                <button type="button" class="framework-card" data-framework-key="{{ $item->key }}" data-framework-label="{{ $item->label }}" data-framework-unit="{{ $item->unit ?? 'รายการ' }}" style="--framework-bg: {{ $style['bg'] }}; --framework-border: {{ $style['border'] }}; --framework-accent: {{ $style['accent'] }};">
                                    <div class="flex h-full flex-col gap-3">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <div class="framework-card__eyebrow">{{ $item->label }}</div>
                                                <div class="mt-1 text-sm font-semibold leading-snug text-slate-900">{{ $item->title }}</div>
                                                @if (!empty($item->subtitle))
                                                    <div class="mt-1 text-xs font-medium leading-snug text-slate-600">{{ $item->subtitle }}</div>
                                                @endif
                                            </div>
                                            <div class="shrink-0 rounded-2xl bg-white/80 px-3 py-2 text-right shadow-sm shadow-slate-200/40">
                                                <div class="metric framework-card__accent text-3xl font-semibold leading-none">{{ number_format((int) $item->total) }}</div>
                                                <div class="mt-1 text-[11px] text-slate-500">{{ $item->unit ?? 'รายการ' }}</div>
                                            </div>
                                        </div>
                                        <div class="text-xs leading-relaxed text-slate-600">{{ $item->detail }}</div>
                                        @if (!empty($item->role_stats))
                                            <div class="grid gap-2 sm:grid-cols-3">
                                                @foreach (collect($item->role_stats) as $roleStat)
                                                    <div class="rounded-xl bg-white/80 px-3 py-2 shadow-sm shadow-slate-200/30">
                                                        <div class="text-[11px] font-semibold framework-card__accent">{{ $roleStat->label }}</div>
                                                        <div class="mt-1 text-sm font-semibold text-slate-900">{{ number_format((int) $roleStat->projects) }} โครงการ</div>
                                                        <div class="mt-0.5 text-[11px] text-slate-500">{{ number_format((int) $roleStat->people) }} คน</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="mt-auto rounded-lg bg-white/75 px-2.5 py-2 text-[11px] text-slate-500">
                                            ปีล่าสุด {{ $item->latest_year ?: '-' }} = {{ number_format((int) $item->latest) }} {{ $item->unit ?? 'รายการ' }}
                                            · จุดสูงสุดปี {{ $item->peak_year ?: '-' }} = {{ number_format((int) $item->peak) }} {{ $item->unit ?? 'รายการ' }}
                                        </div>
                                        <div class="text-[11px] font-semibold framework-card__accent">กดเพื่อดูรายการ{{ ($item->unit ?? 'รายการ') === 'โครงการ' ? 'โครงการ' : '' }}</div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div id="frameworkTrendModal" class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-950/40 px-4 py-6" aria-hidden="true">
                    <div id="frameworkTrendModalCard" class="max-h-[86vh] w-full max-w-none overflow-hidden rounded-2xl bg-white shadow-2xl">
                        <div id="frameworkTrendModalHeader" class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                            <div>
                                <div id="frameworkTrendModalTitle" class="text-xl font-semibold text-slate-900">รายการ</div>
                                <div id="frameworkTrendModalSubtitle" class="mt-1 text-base font-semibold text-slate-600"></div>
                            </div>
                            <button type="button" id="frameworkTrendModalClose" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">ปิด</button>
                        </div>
                        <div id="frameworkTrendModalBody" class="max-h-[68vh] overflow-y-auto px-5 py-4"></div>
                    </div>
                </div>

                <div class="policy-axis-card mt-4 hidden">
                    <div class="policy-section-title rounded-xl border border-indigo-100 px-3 py-3" style="background-color: #eef2ff;">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">Contribute to Siriraj</h3>
                            <p class="mt-1 max-w-4xl text-sm leading-relaxed text-indigo-950/80">สรุปว่าโครงการที่มีคนศิริราชร่วมเชื่อมกับคนกลุ่มใดบ้าง ทั้งคนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ คนภายในคณะแพทยศาสตร์ศิริราชพยาบาล และคนนอกคณะฯ เพื่อเห็นฐาน evidence ที่เกิดจากบริบทจริงของศิริราชชัดขึ้น</p>
                        </div>
                        <span class="policy-pill">People / Projects</span>
                    </div>

                    <div class="hidden grid gap-3 md:grid-cols-4">
                        <div class="contribution-card border-indigo-200 bg-indigo-50">
                            <div class="text-sm font-semibold text-indigo-700">โครงการทั้งหมดที่เกิดขึ้น</div>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-center">
                                <div>
                                    <div class="metric text-lg font-semibold text-slate-900">{{ number_format((int) ($policyImpact['project_count'] ?? 0)) }}</div>
                                    <div class="text-[11px] text-slate-500">โครงการ</div>
                                </div>
                                <div>
                                    <div class="metric text-lg font-semibold text-slate-900">{{ number_format((int) ($contributionNetwork['total_researchers'] ?? 0)) }}</div>
                                    <div class="text-[11px] text-slate-500">นักวิจัย</div>
                                </div>
                            </div>
                        </div>
                        @foreach ($peopleTypeMix as $mix)
                            <div class="contribution-card">
                                <div class="text-sm font-semibold text-slate-900">{{ $mix->label }}</div>
                                <div class="mt-3 grid grid-cols-2 gap-2 text-center">
                                    <div>
                                        <div class="metric text-lg font-semibold text-slate-900">{{ number_format((int) $mix->projects) }}</div>
                                        <div class="text-[11px] text-slate-500">โครงการ</div>
                                    </div>
                                    <div>
                                        <div class="metric text-lg font-semibold text-slate-900">{{ number_format((int) $mix->people) }}</div>
                                        <div class="text-[11px] text-slate-500">นักวิจัย</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 space-y-4">
                        <div class="hidden contribution-card">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">แพทย์ศิริราชที่ร่วมในกลุ่มโครงการ</div>
                                    <div class="mt-1 max-w-3xl text-xs leading-relaxed text-slate-500">ตัวเลขการ์ดเป็นยอดรวมแบบไม่ซ้ำทั้งช่วงที่เลือก ส่วนกราฟด้านล่างแสดงแนวโน้มรายปีโดยนับไม่ซ้ำแยกปีต่อปี</div>
                                </div>
                                <span class="policy-pill">{{ $expertTrendRows->first()?->year ?: '-' }} - {{ $expertTrendRows->last()?->year ?: '-' }}</span>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-3">
                                    <div class="text-xs font-semibold text-indigo-700">แพทย์</div>
                                    <div class="metric mt-1 text-2xl font-semibold text-slate-900">{{ number_format($expertTrendTotalPeople) }} คน</div>
                                    <div class="mt-1 text-[11px] text-slate-500">รวมทั้งช่วงที่เลือกแบบไม่ซ้ำ</div>
                                </div>
                                <div class="rounded-xl border border-amber-100 bg-amber-50 px-3 py-3">
                                    <div class="text-xs font-semibold text-amber-700">โครงการที่มีแพทย์ศิริราชร่วม</div>
                                    <div class="metric mt-1 text-2xl font-semibold text-slate-900">{{ number_format($expertTrendTotalProjects) }} โครงการ</div>
                                    <div class="mt-1 text-[11px] text-slate-500">รวมทั้งช่วงที่เลือกแบบไม่ซ้ำ</div>
                                </div>
                            </div>
                            @php
                                $expertTrendSummaryRows = $expertTrendRows
                                    ->filter(fn ($row) => (int) ($row->year ?? 0) < (int) $currentYearBe)
                                    ->values();
                                if ($expertTrendSummaryRows->isEmpty()) {
                                    $expertTrendSummaryRows = $expertTrendRows;
                                }
                                $expertTrendFirst = $expertTrendSummaryRows->first();
                                $expertTrendLast = $expertTrendSummaryRows->last();
                                $expertPeopleGrowth = max(0, (int) ($expertTrendLast?->people ?? 0) - (int) ($expertTrendFirst?->people ?? 0));
                                $expertProjectGrowth = max(0, (int) ($expertTrendLast?->projects ?? 0) - (int) ($expertTrendFirst?->projects ?? 0));
                            @endphp
                            @if ($expertTrendRows->isNotEmpty())
                                <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,3fr)_minmax(360px,2fr)]">
                                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <div class="text-sm font-semibold text-slate-900">Trend ตามปี</div>
                                                <div class="mt-1 text-xs text-slate-500">แพทย์ศิริราชและโครงการที่มีแพทย์ศิริราชร่วม นับไม่ซ้ำแยกปีต่อปี</div>
                                            </div>
                                            <span class="policy-pill">{{ $expertTrendRows->first()?->year }} - {{ $expertTrendRows->last()?->year }}</span>
                                        </div>
                                        <div class="mt-3 h-[360px]">
                                            <canvas id="chartSirirajClinicalTrend" class="block h-full w-full"></canvas>
                                        </div>
                                    </div>
                                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-4">
                                        <div class="text-sm font-semibold text-indigo-950">สรุปจากแนวโน้ม</div>
                                        <div class="mt-3 grid gap-3">
                                            <div class="rounded-xl bg-white/85 px-3 py-3">
                                                <div class="text-[11px] font-semibold text-indigo-700">เกิดอะไรขึ้น</div>
                                                <div class="mt-1 text-sm font-semibold leading-relaxed text-slate-900">
                                                    ปี {{ $expertTrendFirst?->year }} - {{ $expertTrendLast?->year }} แพทย์ศิริราชที่ร่วมในกลุ่มโครงการเพิ่มจาก {{ number_format((int) ($expertTrendFirst?->people ?? 0)) }} คน เป็น {{ number_format((int) ($expertTrendLast?->people ?? 0)) }} คน
                                                </div>
                                            </div>
                                            <div class="rounded-xl bg-white/85 px-3 py-3">
                                                <div class="text-[11px] font-semibold text-indigo-700">แปลว่าอะไร</div>
                                                <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                    งานวิจัยเริ่มมีแพทย์ศิริราชเข้ามามีส่วนร่วมกับโจทย์จากบริการจริงและ real-world / practice-based evidence มากขึ้น โดยจำนวนโครงการที่มีแพทย์ศิริราชร่วมเพิ่มขึ้น {{ number_format($expertProjectGrowth) }} โครงการในช่วงเดียวกัน
                                                </div>
                                            </div>
                                            <div class="rounded-xl bg-white/85 px-3 py-3">
                                                <div class="text-[11px] font-semibold text-indigo-700">จุดที่ควรอ่าน</div>
                                                <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                    การเพิ่มขึ้นนี้สะท้อนว่า evidence ของกลุ่มโครงการไม่ได้มาจากข้อมูลอย่างเดียว แต่เริ่มเชื่อมกับ clinical insight และความเชี่ยวชาญของแพทย์ศิริราชมากขึ้น
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="mt-4 rounded-lg border border-dashed border-slate-200 px-3 py-4 text-xs text-slate-500">ยังไม่มีข้อมูลแพทย์ในช่วงปีที่เลือก</div>
                            @endif
                        </div>

                        <div class="hidden grid gap-4 content-start">
                            <div class="contribution-card">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">กลุ่มวิชาชีพของผู้ร่วมโครงการ</div>
                                    </div>
                                    <span class="policy-pill">กดที่ row เพื่อเปิดลิ้นชักรายชื่อ</span>
                                </div>
                                <div class="mt-3 space-y-2">
                                    <div class="hidden grid-cols-[1.5fr_repeat(3,minmax(0,1fr))] gap-3 px-3 text-xs font-semibold text-slate-500 lg:grid">
                                        <div>อาชีพ / กลุ่มวิชาชีพ</div>
                                        <div class="text-right">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</div>
                                        <div class="text-right">คนภายในคณะแพทยศาสตร์ศิริราชพยาบาล</div>
                                        <div class="text-right">รวมทั้งช่วงที่เลือก</div>
                                    </div>

                                    @forelse ($professionTypeMatrix as $row)
                                        <details class="rounded-xl border border-slate-200 bg-white">
                                            <summary class="cursor-pointer list-none px-3 py-3">
                                                <div class="grid gap-3 lg:grid-cols-[1.5fr_repeat(3,minmax(0,1fr))] lg:items-center">
                                                    <div>
                                                        <div class="font-semibold text-slate-900">{{ $row->label }}</div>
                                                        <div class="mt-1 text-[11px] text-blue-700">กดเพื่อเปิดลิ้นชักรายชื่อ</div>
                                                    </div>
                                                    @foreach (['unit', 'siriraj'] as $cellKey)
                                                        @php $cell = $row->cells[$cellKey] ?? null; @endphp
                                                        <div class="rounded-lg bg-slate-50 px-3 py-2 text-right">
                                                            <div class="text-sm font-semibold text-slate-900">{{ number_format((int) ($cell->people ?? 0)) }} นักวิจัย</div>
                                                            <div class="text-[11px] text-slate-500">{{ number_format((int) ($cell->projects ?? 0)) }} โครงการ</div>
                                                        </div>
                                                    @endforeach
                                                    <div class="rounded-lg bg-indigo-50 px-3 py-2 text-right">
                                                        <div class="text-sm font-semibold text-slate-900">{{ number_format((int) $row->total_people) }} นักวิจัย</div>
                                                        <div class="text-[11px] text-slate-500">{{ number_format((int) $row->total_projects) }} โครงการ</div>
                                                    </div>
                                                </div>
                                            </summary>

                                            <div class="border-t border-slate-200 bg-slate-50 px-3 py-4">
                                                <div class="grid gap-3 xl:grid-cols-2">
                                                    @foreach (['unit', 'siriraj'] as $cellKey)
                                                        @php
                                                            $cell = $row->cells[$cellKey] ?? null;
                                                            $peopleList = collect($cell->people_list ?? []);
                                                        @endphp
                                                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                                                            <div class="flex items-start justify-between gap-3">
                                                                <div>
                                                                    <div class="text-sm font-semibold text-slate-900">{{ $cell->label ?? '-' }}</div>
                                                                    <div class="mt-1 text-xs text-slate-500">{{ number_format((int) ($cell->people ?? 0)) }} นักวิจัย / {{ number_format((int) ($cell->projects ?? 0)) }} โครงการ</div>
                                                                </div>
                                                            </div>
                                                            <div class="mt-3 max-h-80 space-y-1.5 overflow-y-auto pr-1">
                                                                @forelse ($peopleList as $person)
                                                                    @php
                                                                        $roles = collect($person->roles ?? [])->implode(', ');
                                                                        $years = collect($person->years ?? [])->implode(', ');
                                                                        $themes = collect($person->themes ?? [])->implode(' / ');
                                                                    @endphp
                                                                    <div class="rounded-lg bg-slate-50 px-2.5 py-2">
                                                                        <div class="text-xs font-semibold leading-snug text-slate-900">{{ $person->name }}</div>
                                                                        <div class="mt-0.5 text-[11px] leading-snug text-slate-500">{{ $roles ?: '-' }} · {{ number_format((int) ($person->projects ?? 0)) }} โครงการ@if ($years) · ปี {{ $years }}@endif</div>
                                                                        @if ($themes)
                                                                            <div class="mt-0.5 text-[11px] leading-snug text-slate-400">{{ $themes }}</div>
                                                                        @endif
                                                                    </div>
                                                                @empty
                                                                    <div class="rounded-lg border border-dashed border-slate-200 px-3 py-4 text-center text-xs text-slate-400">ไม่มีรายชื่อในกลุ่มนี้</div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </details>
                                    @empty
                                        <div class="rounded-lg border border-dashed border-slate-200 px-3 py-4 text-center text-slate-500">ยังไม่มีข้อมูลอาชีพ</div>
                                    @endforelse
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="policy-axis-card">
                            <div class="policy-section-title rounded-xl border border-orange-200 px-3 py-3" style="background-color: #fff7ed;">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-orange-700">Policy / Process / People</div>
                                    <h3 class="mt-1 text-xl font-semibold text-slate-900">Contribution</h3>
                                    <p class="mt-2 max-w-4xl text-sm leading-relaxed text-slate-700">
                                        สรุปผลงานที่แปลงงานวิจัยไปสู่รายงาน ข้อเสนอเชิงนโยบาย และการเปลี่ยนแปลงในระบบบริการ เพื่อดูว่าหลักฐานจากโครงการถูกนำไปสนับสนุนระบบสุขภาพระดับประเทศมากแค่ไหน
                                    </p>
                                </div>
                                <span class="policy-pill">{{ number_format((int) ($policyImpact['project_count'] ?? 0)) }} โครงการในช่วงที่เลือก</span>
                            </div>
                            <div class="mt-4 grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
                                <div class="policy-trend-panel">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900">Trend ตามปี</div>
                                            <div class="text-xs text-slate-500">จำนวน output ที่เกี่ยวกับ policy / process / people</div>
                                        </div>
                                        <span class="policy-pill">{{ number_format((int) $policyImpactOutputs->sum()) }} รายการ</span>
                                    </div>
                                    <div class="mt-3 h-[360px]">
                                        <canvas id="chartPolicyImpactTrend"></canvas>
                                    </div>
                                    @if ($policyImpactTrendSummaryRows->isNotEmpty())
                                        <div class="mt-4 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-4">
                                            <div class="text-sm font-semibold text-indigo-950">สรุปจากแนวโน้ม</div>
                                            <div class="mt-3 grid gap-3">
                                                <div class="rounded-xl bg-white/85 px-3 py-3">
                                                    <div class="text-[11px] font-semibold text-indigo-700">แนวโน้มรวม</div>
                                                    <div class="mt-1 text-sm font-semibold leading-relaxed text-slate-900">
                                                        ปี {{ $policyImpactTrendFirst?->year ?: '-' }} - {{ $policyImpactTrendLast?->year ?: '-' }} ผลงานกลุ่ม policy / process / people {{ $policyImpactTrendDirection }} จาก {{ number_format((int) ($policyImpactTrendFirst?->total ?? 0)) }} เป็น {{ number_format((int) ($policyImpactTrendLast?->total ?? 0)) }} รายการ โดยปี {{ $policyImpactTrendPeak?->year ?: '-' }} เป็นจุดสูงสุดที่ {{ number_format((int) ($policyImpactTrendPeak?->total ?? 0)) }} รายการ
                                                    </div>
                                                </div>
                                                <div class="rounded-xl bg-white/85 px-3 py-3">
                                                    <div class="text-[11px] font-semibold text-indigo-700">อ่านเส้นกราฟอย่างไร</div>
                                                    <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                        Reports and White Papers คือหลักฐานเชิงรายงานหรือ white paper, Policy Recommendations คือข้อเสนอเชิงนโยบาย และ Change in Practices / Procedures / Policy คือ output ที่ใกล้การเปลี่ยนระบบหรือแนวปฏิบัติมากที่สุด ดังนั้นการอ่าน trend ควรดูทั้งจำนวนรวมและสัดส่วนของแต่ละเส้น ไม่ใช่อ่านเป็นจำนวนโครงการ
                                                    </div>
                                                </div>
                                                <div class="rounded-xl bg-white/85 px-3 py-3">
                                                    <div class="text-[11px] font-semibold text-indigo-700">ภาพรวมที่เห็น</div>
                                                    <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                        จากข้อมูลในช่วงที่เลือก ผลงานที่พบมากที่สุดคือ {{ $policyImpactTopLabel ?: '-' }} {{ number_format((int) ($policyImpactOutputs[$policyImpactTopCode] ?? 0)) }} รายการ หมายความว่างานจำนวนมากถูกถ่ายทอดออกมาเป็นรายงานหรือ white paper แล้ว แต่การต่อยอดให้กลายเป็นข้อเสนอเชิงนโยบาย หรือการเปลี่ยนแนวปฏิบัติจริง ยังเกิดขึ้นน้อยกว่า
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="space-y-3">
                                    @foreach ($policyImpactCodeLabels as $code => $label)
                                        <div class="rounded-xl border p-3" style="{{ $outputTypeCardStyle($code) }}">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-sm font-semibold text-slate-900">{{ $label }}</div>
                                                    <div class="mt-1 text-xs text-slate-500">รายการ output ที่บันทึกไว้ในระบบ</div>
                                                </div>
                                                <div class="metric rounded-full border px-3 py-1 text-sm font-semibold" style="{{ $outputTypeStyle($code) }}">{{ number_format((int) ($policyImpactOutputs[$code] ?? 0)) }} รายการ</div>
                                            </div>
                                            <div class="policy-mini-list mt-3 space-y-2">
                                                @forelse (collect($policyImpactOutputItems[$code] ?? []) as $item)
                                                    <a href="{{ $item->link ?: '#' }}" class="policy-mini-link">
                                                        <div class="text-xs font-semibold leading-snug text-slate-900">{{ $item->title }}</div>
                                                        <div class="mt-1 text-[11px] text-slate-500">{{ $item->reference }} · {{ $item->source_label ?: '-' }} · ปี {{ $item->year ?: '-' }}</div>
                                                    </a>
                                                @empty
                                                    <div class="rounded-lg border border-dashed border-slate-200 px-3 py-3 text-xs text-slate-500">ยังไม่มี output ในช่วงที่เลือก</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                    </div>
                </div>

                <div class="policy-axis-card mt-4">
                    <div class="contribution-card">
                                <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4">
                                    <div class="flex flex-wrap items-start justify-between gap-4">
                                        <div>
                                            <div class="text-xs font-semibold uppercase tracking-wide text-sky-700">Clinical-Led Integration</div>
                                            <div class="mt-1 text-xl font-semibold text-slate-900">กลุ่มเนื้อหา Ageing และ Real-World Evidence (RWE)</div>
                                            <div class="mt-2 max-w-5xl text-sm leading-relaxed text-slate-700">ดูแนวโน้มรายปีว่าในกลุ่ม Ageing และ Real-World Evidence (RWE) มีคนศิริราชเข้ามามีส่วนร่วมกี่คน พร้อมดูสัดส่วนบทบาท PI, Co-PI และ Co-Research ในสองกลุ่มเนื้อหานี้</div>
                                        </div>
                                    </div>
                                </div>
                                @php
                                    $clinicalLedTrendRowsForSummary = $clinicalLedPeopleByYear->values();
                                    $clinicalLedFirstYearRow = $clinicalLedTrendRowsForSummary->first();
                                    $clinicalLedLastYearRow = $clinicalLedTrendRowsForSummary->last();
                                    $clinicalLedPeakYearRow = $clinicalLedTrendRowsForSummary->sortByDesc(fn ($row) => (int) ($row->people_count ?? 0))->first();
                                    $clinicalLedTopTheme = $clinicalLedIntegrationThemes->sortByDesc(fn ($row) => (int) ($row->people ?? 0))->first();
                                @endphp

                                <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1fr)_430px]">
                                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <div class="text-sm font-semibold text-slate-900">Trend กลุ่มเนื้อหา Ageing / RWE ตามปี</div>
                                                <div class="mt-1 text-xs text-slate-500">นับจำนวนคนศิริราชไม่ซ้ำในแต่ละกลุ่มเนื้อหาของแต่ละปี</div>
                                            </div>
                                            <span class="policy-pill">Ageing / RWE</span>
                                        </div>
                                        <div class="mt-3 h-[380px]">
                                            <canvas id="chartClinicalLedIntegrationTrend" class="block h-full w-full"></canvas>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-4">
                                        <div class="text-sm font-semibold text-indigo-950">สรุปจากแนวโน้ม</div>
                                        <div class="mt-3 grid gap-3">
                                            <div class="rounded-xl bg-white/90 px-3 py-3">
                                                <div class="text-[11px] font-semibold text-indigo-700">ทิศทางหลัก</div>
                                                <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                    แนวโน้มสะท้อนว่าการมีส่วนร่วมของคนศิริราชในกลุ่ม Ageing ขยายตัวก่อนกลุ่ม RWE โดย Ageing มี {{ number_format((int) ($clinicalLedFirstYearRow?->ageing_people_count ?? 0)) }} คนในปี {{ $clinicalLedFirstYearRow?->year ?: '-' }} และไปสู่จุดสูงสุด {{ number_format((int) ($clinicalLedPeakYearRow?->ageing_people_count ?? 0)) }} คนในปี {{ $clinicalLedPeakYearRow?->year ?: '-' }}
                                                </div>
                                            </div>
                                            <div class="rounded-xl bg-white/90 px-3 py-3">
                                                <div class="text-[11px] font-semibold text-indigo-700">บทบาทที่เข้ามาร่วม</div>
                                                <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                    ในสองกลุ่มเนื้อหานี้มีคนศิริราชร่วมสะสมตามบทบาท: {{ $clinicalLedRoleSummaryText ?: '-' }} ทำให้เห็นว่าไม่ได้มีเฉพาะหัวหน้าโครงการ แต่มีการกระจายบทบาททั้ง PI, Co-PI และ Co-Research
                                                </div>
                                            </div>
                                            <div class="rounded-xl bg-white/90 px-3 py-3">
                                                <div class="text-[11px] font-semibold text-indigo-700">ข้อควรตีความ</div>
                                                <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                    ปี {{ $clinicalLedPeakYearRow?->year ?: '-' }} เป็นจุดสูงสุดของช่วงข้อมูล มีคนศิริราชรวม {{ number_format((int) ($clinicalLedPeakYearRow?->people_count ?? 0)) }} คนในสองกลุ่มนี้ ขณะที่ RWE เริ่มเห็นการเพิ่มขึ้นต่อเนื่องกว่าเดิม จึงควรอ่านเป็นสัญญาณของการขยับจาก HTA/system research ไปสู่งานที่เชื่อมโจทย์บริการกับ real-world evidence มากขึ้น
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                </div>

                <div class="policy-axis-card mt-4">
                    <div class="contribution-card">
                        <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-4">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-cyan-700">Data Literacy Skill</div>
                                    <div class="mt-1 text-xl font-semibold text-slate-900">ทักษะการใช้ข้อมูลของกลุ่มโครงการ</div>
                                    <div class="mt-2 max-w-5xl text-sm leading-relaxed text-slate-700">ดูว่ากลุ่มโครงการใช้ข้อมูลจากโลกจริงหรือเก็บข้อมูลใหม่มากน้อยแค่ไหน เพื่ออ่านทักษะการแปล ใช้ และสร้างข้อมูลในการตอบโจทย์วิจัย</div>
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-3 text-right shadow-sm">
                                    <div class="metric text-2xl font-semibold text-slate-900">{{ number_format((int) $policyDataSourceCounts->sum()) }}</div>
                                    <div class="text-xs text-slate-500">โครงการที่ระบุข้อมูล</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1fr)_430px]">
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">Trend Data Source / Method ตามปี</div>
                                        <div class="mt-1 text-xs text-slate-500">นับจำนวนโครงการในแต่ละปีตามแหล่งที่มาและวิธีใช้ข้อมูล</div>
                                    </div>
                                    <span class="policy-pill">Real-world / Primary</span>
                                </div>
                                <div class="mt-3 h-[360px]">
                                    <canvas id="chartDataLiteracyTrend" class="block h-full w-full"></canvas>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-4">
                                <div class="text-sm font-semibold text-cyan-950">สรุป Data Literacy Skill</div>
                                <div class="mt-3 grid gap-3">
                                    @foreach ($policyDataLabels as $label => $meta)
                                        @php
                                            $count = (int) ($policyDataSourceCounts[$label] ?? 0);
                                            $pct = $policyDataSourceTotal > 0 ? round(($count / $policyDataSourceTotal) * 100, 1) : 0;
                                        @endphp
                                        <div class="rounded-xl bg-white/90 px-3 py-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-sm font-semibold text-slate-900">{{ $meta['short'] }}</div>
                                                    <div class="mt-1 text-[11px] font-semibold" style="color: {{ $meta['color'] }}">{{ $meta['tag'] }}</div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="metric text-xl font-semibold text-slate-900">{{ number_format($count) }}</div>
                                                    <div class="text-[11px] text-slate-500">โครงการ</div>
                                                </div>
                                            </div>
                                            <div class="mt-3 h-2 rounded-full bg-slate-100">
                                                <div class="h-2 rounded-full" style="width: {{ min(100, max(0, $pct)) }}%; background-color: {{ $meta['color'] }}"></div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="rounded-xl bg-white/90 px-3 py-3">
                                        <div class="text-[11px] font-semibold text-cyan-700">จุดที่ควรอ่าน</div>
                                        <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                            @if ((int) $policyDataSourceCounts->sum() > 0)
                                                หมวดที่พบมากที่สุดคือ {{ $policyDataSourcePeak ?: '-' }} จำนวน {{ number_format($policyDataSourcePeakCount) }} โครงการ จึงช่วยบอกว่างานของหน่วยกำลังใช้ฐานข้อมูลเดิมจากระบบบริการ หรือสร้างข้อมูลใหม่เพื่อตอบคำถามวิจัยมากกว่ากัน
                                            @else
                                                ยังไม่มีโครงการที่ระบุ Data source / Method ในช่วงที่เลือก จึงยังไม่ควรสรุปว่างานของหน่วยใช้ข้อมูลเดิมจากระบบบริการ หรือสร้างข้อมูลใหม่มากกว่ากัน
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="policy-axis-card mt-4">
                    <div class="policy-section-title">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">People Contribution</h3>
                            <p class="mt-1 text-sm text-slate-600">สรุปบุคลากรในกลุ่มโครงการ: PI, Co-PI, Co-Research และด้านที่มีส่วนร่วมบ่อย</p>
                        </div>
                        <span class="policy-pill">แสดง {{ number_format($policyPeopleHighlights->count()) }} คน</span>
                    </div>
                    <div class="policy-people-grid">
                        @forelse ($policyPeopleHighlights as $person)
                            <a href="{{ $person->link }}" class="policy-person-card">
                                <div class="flex items-start gap-3">
                                    <img src="{{ $person->profile_photo_url }}" alt="{{ $person->name }}" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'" class="h-12 w-12 rounded-2xl border border-slate-200 object-cover">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold text-slate-900">{{ $person->name }}</div>
                                        @if ($person->position)
                                            <div class="mt-1 text-xs text-slate-500">{{ $person->position }}</div>
                                        @endif
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <span class="rounded-full bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-700">PI {{ number_format((int) ($person->role_counts['pi'] ?? 0)) }}</span>
                                            <span class="rounded-full bg-teal-50 px-2 py-1 text-[11px] font-semibold text-teal-700">Co-PI {{ number_format((int) ($person->role_counts['co_pi'] ?? 0)) }}</span>
                                            <span class="rounded-full bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-700">Co-Research {{ number_format((int) ($person->role_counts['co_research'] ?? 0)) }}</span>
                                        </div>
                                        <div class="mt-2 text-xs leading-relaxed text-slate-600">เด่นด้าน: {{ $person->top_theme }}</div>
                                        <div class="mt-1 text-xs leading-relaxed text-slate-500">{{ $person->trend_text }}</div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="metric text-sm font-semibold text-slate-900">{{ number_format((int) $person->total_projects) }}</div>
                                        <div class="text-[11px] text-slate-500">projects</div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 px-4 py-5 text-sm text-slate-500">ยังไม่มีข้อมูลบุคลากรในช่วงที่เลือก</div>
                        @endforelse
                    </div>
                </div>

            </section>

            <section class="fade-up panel section-panel section-panel--teal p-5 md:p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <div class="section-tag section-tag--teal">Theme Output</div>
                        <h2 class="text-lg font-semibold text-slate-900">กลุ่มเนื้อหา  (6 กลุ่ม) จาก Output</h2>
                        <p class="mt-1 text-sm text-slate-500">สรุปเฉพาะผลงานในโครงการวิจัยที่นำไปจัดกลุ่มเนื้อหา พร้อมรายการผลงานในแต่ละกลุ่ม</p>
                    </div>
                    <div class="focus-kpi px-4 py-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Project Outputs</div>
                        <div class="metric text-xl font-semibold text-slate-900">{{ number_format((int) ($projectTypeTotalOutputs ?? 0)) }} รายการ</div>
                        <div class="text-[11px] leading-tight text-slate-500">เฉพาะในโครงการวิจัย</div>
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

            @if ($networkingCount > 0)
                <section class="scene scene--balanced grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                    <article class="fade-up panel section-panel section-panel--emerald chart-sticky flex h-full flex-col p-4 md:p-5">
                        <div class="section-tag section-tag--emerald">Networking</div>
                        <h2 class="text-base font-semibold text-slate-900">Networking: EA115 Capacity Building / Knowledge Sharing และ EA117 MOU <span class="ml-1 text-sm font-medium text-slate-500">({{ number_format($networkingCount) }} รายการ)</span></h2>
                        <div class="mt-4 flex flex-1 items-center">
                            <div class="h-[340px] w-full"><canvas id="chartNetworkingYear"></canvas></div>
                        </div>
                    </article>
                    <article class="fade-up panel section-panel section-panel--emerald flex h-full flex-col p-5 md:p-6">
                        <h3 class="text-xl font-semibold text-slate-900">เครือข่ายและการเสริมศักยภาพ</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div class="focus-kpi px-4 py-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">รวม</div>
                                <div class="metric mt-1 text-2xl font-semibold text-slate-900">{{ number_format($networkingCount) }}</div>
                                <div class="text-xs text-slate-500">รายการ</div>
                            </div>
                            <div class="focus-kpi px-4 py-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">EA115</div>
                                <div class="metric mt-1 text-2xl font-semibold text-slate-900">{{ number_format((int) ($networkingSummary['ea115_count'] ?? 0)) }}</div>
                                <div class="text-xs text-slate-500">Capacity Building</div>
                            </div>
                            <div class="focus-kpi px-4 py-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">EA117</div>
                                <div class="metric mt-1 text-2xl font-semibold text-slate-900">{{ number_format((int) ($networkingSummary['ea117_count'] ?? 0)) }}</div>
                                <div class="text-xs text-slate-500">MOU</div>
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">ครอบคลุมปี {{ $networkingCoverageStartYear ?: '-' }} ถึง {{ $networkingCoverageEndYear ?: '-' }} · ปีที่มีจำนวนสูงสุด {{ $networkingSummary['peak_year'] ?: '-' }} ({{ number_format((int) ($networkingSummary['peak_year_count'] ?? 0)) }} รายการ)</p>
                        <p class="mt-1 text-xs text-slate-500">รายการที่แสดงล่าสุด {{ number_format($networkingLatestCount) }} รายการ</p>
                        <div class="mt-4 flex-1 space-y-2">
                            @forelse (($networkingSummary['latest'] ?? collect()) as $row)
                                <button
                                    type="button"
                                    class="js-output-item block w-full rounded-xl border px-3 py-3 text-left text-slate-700 transition hover:-translate-y-[1px]"
                                    style="{{ $outputTypeCardStyle($row->type_code ?? 'EA117') }}"
                                    data-title="{{ $row->title }}"
                                    data-link="{{ $row->modal_link ?? '' }}"
                                    data-detail="{{ $row->modal_detail ?? '' }}"
                                    data-type="{{ $row->type_code ?? 'EA117' }} - {{ $row->type_label ?? 'Networking' }}"
                                >
                                    <div class="mb-2 inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold" style="{{ $outputTypeCardStyle($row->type_code ?? 'EA117') }}">
                                        {{ $row->type_code ?? '-' }} {{ $row->type_label ?? '' }}
                                    </div>
                                    <div class="font-medium line-clamp-2">{{ $row->title }}</div>
                                    <div class="mt-2 grid gap-1 text-xs">
                                        <div class="text-slate-500">ปี: <span class="font-medium text-slate-700">{{ $row->year_be ?: '-' }}</span></div>
                                        <div class="text-slate-500">แหล่งข้อมูล: <span class="font-medium text-slate-700">{{ $sourceLabelMap[$row->source ?? ''] ?? ($row->source ?? '-') }}</span></div>
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
            const networkingYearLabels = @json($networkingYearLabels);
            const networkingYearSeries = @json($networkingYearSeries);
            const policyFrameworkTrendYears = @json(collect($policyFrameworkTrend['years'] ?? [])->values());
            const policyFrameworkTrendSeries = @json(collect($policyFrameworkTrend['series'] ?? []));
            const policyFrameworkTrendItems = @json(collect($policyFrameworkTrend['items'] ?? []));
            const policyFrameworkAllItems = @json(collect($policyFrameworkTrend['all_items'] ?? []));
            const dataLiteracyTrendYears = @json(collect($policyDataSourceTrend['years'] ?? [])->values());
            const dataLiteracyTrendSeries = @json(collect($policyDataSourceTrend['series'] ?? []));
            const policyImpactTrendYears = @json(collect($policyImpactTrend['years'] ?? [])->values());
            const policyImpactTrendSeries = @json(collect($policyImpactTrend['series'] ?? []));
            const policyImpactTrendItems = @json(collect($policyImpactTrend['items'] ?? []));
            const policyImpactTrendItemsByYear = @json(collect($policyImpactTrend['items_by_year'] ?? []));
            const sirirajClinicalTrendRows = @json($expertTrendRows);
            const clinicalLedIntegrationTrend = @json($clinicalLedIntegrationTrend);
            const clinicalLedIntegrationItemsByThemeYear = @json(collect($clinicalLedIntegration['items_by_theme_year'] ?? []));
            const presentationChartColors = ['#ea580c', '#fb7185', '#16a34a', '#0891b2', '#7c3aed', '#ca8a04', '#0f766e', '#2563eb', '#475569'];
            const presentationChartBorderColors = ['#c2410c', '#e11d48', '#15803d', '#0e7490', '#6d28d9', '#a16207', '#115e59', '#1d4ed8', '#334155'];
            const textbookYearColors = @json(collect($textbookYearLabels ?? collect())->map(fn ($year) => $textbookYearColorMap[(string) $year] ?? '#0f766e')->values());
            const networkingEa115 = (networkingYearSeries?.EA115 || []);
            const networkingEa117 = (networkingYearSeries?.EA117 || []);

            const gridColor = 'rgba(148, 163, 184, 0.2)';
            const textColor = '#334155';
            const chartAnim = { duration: 760, easing: 'easeOutCubic' };
            const outputInProjectColor = '#4f46e5';
            const outputInProjectBorder = '#3730a3';
            const missionColor = '#0f766e';
            const missionBorder = '#115e59';
            const extraColor = '#ea580c';
            const extraBorder = '#c2410c';

            const safeLabels = (labels) => (labels && labels.length ? labels : ['ไม่มีข้อมูล']);
            const safeValues = (values, fallback = 0) => (values && values.length ? values : [fallback]);
            const safeColors = (colors) => (colors && colors.length ? colors : ['#64748b']);
            const renderChart = (canvasId, config) => {
                const canvas = document.getElementById(canvasId);
                if (!canvas || typeof Chart === 'undefined') return null;

                try {
                    Chart.getChart?.(canvas)?.destroy();
                    return new Chart(canvas, config);
                } catch (error) {
                    console.error(`Unable to render ${canvasId}`, error);
                    return null;
                }
            };
            const suggestedAxisMax = (values, min = 5) => {
                const maxVal = Math.max(...(values || [0]), 0);
                if (maxVal <= 0) return min;
                return Math.ceil(maxVal * 1.18);
            };
            const labelsForYearChart = safeLabels(yearLabels);
            const projectSeries = labelsForYearChart.map((_, index) => Number(sourceSeries?.project?.[index] || 0));
            const missionSeries = labelsForYearChart.map((_, index) => Number(sourceSeries?.back_office?.[index] || 0));
            const extraSeries = labelsForYearChart.map((_, index) => Number(sourceSeries?.external?.[index] || 0));
            const totalsByYear = labelsForYearChart.map((_, i) => projectSeries[i] + missionSeries[i] + extraSeries[i]);

            renderChart('chartOutputsByYear', {
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
                            data: missionSeries,
                            backgroundColor: missionColor,
                            borderColor: missionBorder,
                            borderWidth: 1,
                            hoverBackgroundColor: missionBorder,
                            borderRadius: 8
                        },
                        {
                            label: 'Extra',
                            data: extraSeries,
                            backgroundColor: extraColor,
                            borderColor: extraBorder,
                            borderWidth: 1,
                            hoverBackgroundColor: extraBorder,
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
                                const isTopStack = ctx.datasetIndex === 2;
                                if (!isTopStack) return '';
                                const total = Number(projectSeries[index] || 0) + Number(missionSeries[index] || 0) + Number(extraSeries[index] || 0);
                                return total > 0 ? total : '';
                            }
                        },
                    }
                }
            });

            const frameworkTrendLabels = safeLabels(policyFrameworkTrendYears);
            const frameworkTrendMeta = {
                trust: 'Contribute to Siriraj',
                truth: 'Integrate Clinical Skill',
                policy_impact: 'Contribution',
                clinical_led: 'กลุ่มเนื้อหา Ageing / RWE'
            };
            const frameworkOutputTypeStyles = @json($typeStyleMap);
            const frameworkTrendUnits = {
                trust: 'โครงการ',
                truth: 'โครงการ',
                policy_impact: 'รายการ',
                clinical_led: 'โครงการ'
            };
            const frameworkToneStyles = {
                trust: { bg: '#eef2ff', border: '#c7d2fe', accent: '#4f46e5', header: '#eef2ff' },
                truth: { bg: '#ecfdf5', border: '#99f6e4', accent: '#0f766e', header: '#ecfdf5' },
                policy_impact: { bg: '#fff7ed', border: '#fed7aa', accent: '#c2410c', header: '#fff7ed' },
                clinical_led: { bg: '#f0f9ff', border: '#bae6fd', accent: '#0369a1', header: '#f0f9ff' }
            };
            let frameworkModalTone = frameworkToneStyles.trust;
            const frameworkTrendDatasets = [
                {
                    key: 'trust',
                    label: frameworkTrendMeta.trust,
                    data: frameworkTrendLabels.map((_, index) => Number(policyFrameworkTrendSeries?.trust?.[index] || 0)),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, .12)',
                    pointBackgroundColor: '#4f46e5',
                    tension: .35,
                    fill: true
                },
                {
                    key: 'truth',
                    label: frameworkTrendMeta.truth,
                    data: frameworkTrendLabels.map((_, index) => Number(policyFrameworkTrendSeries?.truth?.[index] || 0)),
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15, 118, 110, .10)',
                    pointBackgroundColor: '#0f766e',
                    tension: .35,
                    fill: true
                },
                {
                    key: 'policy_impact',
                    label: frameworkTrendMeta.policy_impact,
                    data: frameworkTrendLabels.map((_, index) => Number(policyFrameworkTrendSeries?.policy_impact?.[index] || 0)),
                    borderColor: '#c2410c',
                    backgroundColor: 'rgba(194, 65, 12, .12)',
                    pointBackgroundColor: '#c2410c',
                    tension: .35,
                    fill: true
                }
            ];
            const frameworkTrendValues = frameworkTrendDatasets.flatMap((dataset) => dataset.data.map((value) => Number(value || 0)));
            const frameworkModal = document.getElementById('frameworkTrendModal');
            const frameworkModalCard = document.getElementById('frameworkTrendModalCard');
            const frameworkModalHeader = document.getElementById('frameworkTrendModalHeader');
            const frameworkModalTitle = document.getElementById('frameworkTrendModalTitle');
            const frameworkModalSubtitle = document.getElementById('frameworkTrendModalSubtitle');
            const frameworkModalBody = document.getElementById('frameworkTrendModalBody');
            const frameworkModalClose = document.getElementById('frameworkTrendModalClose');
            const frameworkPageSize = 10;
            let frameworkModalState = { items: [], page: 1, unit: 'รายการ', filters: { q: '', year: '', role: '', theme: '' } };
            const escapeFrameworkHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
            const normalizeFrameworkItems = (value) => {
                if (Array.isArray(value)) return value;
                if (value && typeof value === 'object') return Object.values(value);
                return [];
            };
            const frameworkThemePalette = [
                { bg: '#fff1f2', border: '#fecdd3', color: '#9f1239' },
                { bg: '#fff7ed', border: '#fed7aa', color: '#9a3412' },
                { bg: '#fffbeb', border: '#fde68a', color: '#92400e' },
                { bg: '#f7fee7', border: '#d9f99d', color: '#3f6212' },
                { bg: '#ecfdf5', border: '#a7f3d0', color: '#065f46' },
                { bg: '#f0fdfa', border: '#99f6e4', color: '#115e59' },
                { bg: '#ecfeff', border: '#a5f3fc', color: '#155e75' },
                { bg: '#f0f9ff', border: '#bae6fd', color: '#075985' },
                { bg: '#eff6ff', border: '#bfdbfe', color: '#1d4ed8' },
                { bg: '#eef2ff', border: '#c7d2fe', color: '#3730a3' },
                { bg: '#f5f3ff', border: '#ddd6fe', color: '#6d28d9' },
                { bg: '#fdf4ff', border: '#f5d0fe', color: '#a21caf' },
                { bg: '#fdf2f8', border: '#fbcfe8', color: '#be185d' },
                { bg: '#f8fafc', border: '#cbd5e1', color: '#475569' }
            ];
            const frameworkThemeStyle = (label) => {
                const value = String(label || '');
                const outputTypeMatch = value.match(/^(EA\d{3})/i);
                if (outputTypeMatch) {
                    const outputTypeStyle = frameworkOutputTypeStyles?.[outputTypeMatch[1].toUpperCase()];
                    if (outputTypeStyle) return outputTypeStyle;
                }
                const codeMatch = value.match(/^(\d{1,2})/);
                if (codeMatch) {
                    const codeIndex = Math.max(0, Number(codeMatch[1]) - 1);
                    const codeStyle = frameworkThemePalette[codeIndex % frameworkThemePalette.length];
                    return `background:${codeStyle.bg}; border-color:${codeStyle.border}; color:${codeStyle.color};`;
                }
                let hash = 0;
                for (let index = 0; index < value.length; index += 1) {
                    hash = ((hash << 5) - hash) + value.charCodeAt(index);
                    hash |= 0;
                }
                const style = frameworkThemePalette[Math.abs(hash) % frameworkThemePalette.length];
                return `background:${style.bg}; border-color:${style.border}; color:${style.color};`;
            };
            const sortedFrameworkItems = (items) => normalizeFrameworkItems(items)
                .slice()
                .sort((a, b) => {
                    const yearDiff = Number(b?.year || 0) - Number(a?.year || 0);
                    if (yearDiff !== 0) return yearDiff;
                    return String(a?.title || '').localeCompare(String(b?.title || ''), 'th');
                });
            const frameworkItemSearchText = (item) => [
                item?.title,
                item?.meta,
                item?.type,
                item?.year,
                ...normalizeFrameworkItems(item?.people).flatMap((person) => [person?.name, person?.role, person?.affiliation])
            ].filter(Boolean).join(' ').toLowerCase();
            const filteredFrameworkItems = () => {
                const q = String(frameworkModalState.filters?.q || '').trim().toLowerCase();
                const year = String(frameworkModalState.filters?.year || '').trim();
                const role = String(frameworkModalState.filters?.role || '').trim();
                const theme = String(frameworkModalState.filters?.theme || '').trim();

                return frameworkModalState.items.filter((item) => {
                    if (year && String(item?.year || '') !== year) return false;
                    if (role && !normalizeFrameworkItems(item?.people).some((person) => String(person?.role || '') === role)) return false;
                    if (theme && String(item?.type || '') !== theme) return false;
                    if (q && !frameworkItemSearchText(item).includes(q)) return false;
                    return true;
                });
            };
            const renderFrameworkFilters = (items, filteredTotal) => {
                const years = items
                    .map((item) => Number(item?.year || 0))
                    .filter(Boolean)
                    .filter((year, index, list) => list.indexOf(year) === index)
                    .sort((a, b) => b - a);
                const selectedYear = String(frameworkModalState.filters?.year || '');
                const selectedRole = String(frameworkModalState.filters?.role || '');
                const selectedTheme = String(frameworkModalState.filters?.theme || '');
                const query = escapeFrameworkHtml(frameworkModalState.filters?.q || '');
                const themes = items
                    .map((item) => String(item?.type || '').trim())
                    .filter(Boolean)
                    .filter((theme, index, list) => list.indexOf(theme) === index)
                    .sort((a, b) => a.localeCompare(b, 'th'));

                return `
                    <div class="mb-4 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <div class="grid gap-3 lg:grid-cols-[minmax(280px,1fr)_220px_180px_180px_auto] lg:items-end">
                            <label class="block">
                                <span class="text-[11px] font-semibold text-slate-500">ค้นหา</span>
                                <input type="search" class="js-framework-filter-q mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" value="${query}" placeholder="ค้นหาชื่อโปรเจค ชื่อคน หรือประเภท">
                            </label>
                            <label class="block">
                                <span class="text-[11px] font-semibold text-slate-500">Theme</span>
                                <select class="js-framework-filter-theme mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <option value="">ทั้งหมด</option>
                                    ${themes.map((theme) => `<option value="${escapeFrameworkHtml(theme)}" ${selectedTheme === theme ? 'selected' : ''}>${escapeFrameworkHtml(theme)}</option>`).join('')}
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-[11px] font-semibold text-slate-500">บทบาทในโปรเจค</span>
                                <select class="js-framework-filter-role mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <option value="">ทั้งหมด</option>
                                    ${['PI', 'Co-PI', 'Co-Research'].map((role) => `<option value="${role}" ${selectedRole === role ? 'selected' : ''}>${role}</option>`).join('')}
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-[11px] font-semibold text-slate-500">ปี</span>
                                <select class="js-framework-filter-year mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <option value="">ทุกปี</option>
                                    ${years.map((year) => `<option value="${year}" ${selectedYear === String(year) ? 'selected' : ''}>${year}</option>`).join('')}
                                </select>
                            </label>
                            <button type="button" class="js-framework-filter-clear rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">ล้าง filter</button>
                        </div>
                        <div class="mt-2 text-xs text-slate-500">พบ ${filteredTotal.toLocaleString()} จาก ${items.length.toLocaleString()} ${escapeFrameworkHtml(frameworkModalState.unit || 'รายการ')}</div>
                    </div>
                `;
            };
            const renderFrameworkItems = () => {
                const allItems = frameworkModalState.items;
                const items = filteredFrameworkItems();
                const total = items.length;
                const totalPages = Math.max(1, Math.ceil(total / frameworkPageSize));
                const page = Math.min(Math.max(1, frameworkModalState.page), totalPages);
                frameworkModalState.page = page;
                const start = (page - 1) * frameworkPageSize;
                const pageItems = items.slice(start, start + frameworkPageSize);
                const pager = total > frameworkPageSize
                    ? `<div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-3">
                        <div class="text-xs text-slate-500">หน้า ${page.toLocaleString()} / ${totalPages.toLocaleString()} · แสดง ${Math.min(start + 1, total).toLocaleString()}-${Math.min(start + frameworkPageSize, total).toLocaleString()} จาก ${total.toLocaleString()} ${escapeFrameworkHtml(frameworkModalState.unit || 'รายการ')}</div>
                        <div class="flex gap-2">
                            <button type="button" class="js-framework-page rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40" data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''}>ก่อนหน้า</button>
                            <button type="button" class="js-framework-page rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40" data-page="${page + 1}" ${page >= totalPages ? 'disabled' : ''}>ถัดไป</button>
                        </div>
                    </div>`
                    : '';

                const listHtml = pageItems.length
                    ? `<div class="space-y-2">${pageItems.map((item, index) => {
                        const order = start + index + 1;
                        const title = escapeFrameworkHtml(item.title || '-');
                        const meta = escapeFrameworkHtml(item.meta || '-');
                        const type = escapeFrameworkHtml(item.type || '-');
                        const typeBadge = item.type
                            ? `<span class="inline-flex max-w-full items-center rounded-full border px-3 py-1 text-sm font-semibold leading-snug" style="${frameworkThemeStyle(item.type)}">${type}</span>`
                            : '';
                        const year = item.year ? escapeFrameworkHtml(item.year) : '-';
                        const link = item.link ? String(item.link) : '';
                        const people = normalizeFrameworkItems(item.people);
                        const peopleHtml = people.length
                            ? `<div class="mt-3 flex flex-wrap gap-2">${people.map((person) => {
                                const personName = escapeFrameworkHtml(person.name || '-');
                                const personRole = escapeFrameworkHtml(person.role || '');
                                const personAffiliation = escapeFrameworkHtml(person.affiliation || '');
                                const personPhoto = escapeFrameworkHtml(person.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(person.name || '-')}&background=0F172A&color=fff&size=128`);
                                const roleClass = person.role === 'PI'
                                    ? 'bg-indigo-50 text-indigo-700 border-indigo-100'
                                    : (person.role === 'Co-PI'
                                        ? 'bg-teal-50 text-teal-700 border-teal-100'
                                        : 'bg-amber-50 text-amber-700 border-amber-100');

                                return `
                                    <span class="inline-flex max-w-full items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2">
                                        <img src="${personPhoto}" alt="${personName}" class="h-11 w-11 shrink-0 rounded-xl border border-slate-200 object-cover" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(person.name || '-')}&background=0F172A&color=fff&size=128';">
                                        <span class="min-w-0 text-left">
                                            <span class="flex min-w-0 flex-wrap items-center gap-1.5">
                                                <span class="truncate text-sm font-semibold text-slate-900">${personName}</span>
                                                ${personRole ? `<span class="rounded-full border px-2 py-0.5 text-[11px] font-semibold ${roleClass}">${personRole}</span>` : ''}
                                            </span>
                                            ${personAffiliation ? `<span class="mt-1 block max-w-[520px] whitespace-normal text-sm font-medium leading-snug text-slate-600">${personAffiliation}</span>` : ''}
                                        </span>
                                    </span>
                                `;
                            }).join('')}</div>`
                            : '';
                        const inner = `
                            <div class="grid gap-3 lg:grid-cols-[54px_86px_1fr]">
                                    <div class="text-sm font-bold text-slate-400">#${order.toLocaleString()}</div>
                                <div>
                                    <div class="text-xs font-semibold text-slate-500">ปี</div>
                                    <div class="metric text-2xl font-semibold text-slate-900">${year}</div>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">โปรเจค / รายการ</div>
                                    <div class="mt-1 text-base font-semibold leading-snug text-slate-900">${title}</div>
                                    <div class="mt-1 text-sm font-medium text-slate-500">${meta}</div>
                                    ${typeBadge ? `<div class="mt-2">${typeBadge}</div>` : ''}
                                    ${peopleHtml}
                                </div>
                            </div>
                        `;
                        return link
                            ? `<a href="${escapeFrameworkHtml(link)}" class="block rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 hover:border-blue-200 hover:bg-blue-50">${inner}</a>`
                            : `<div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">${inner}</div>`;
                    }).join('')}</div>${pager}`
                    : '<div class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-500">ไม่มีรายการ</div>';

                return `${renderFrameworkFilters(allItems, total)}${listHtml}`;
            };
            const renderFrameworkModalPage = (focusTarget = null) => {
                frameworkModalBody.innerHTML = renderFrameworkItems();
                const filterInput = frameworkModalBody.querySelector('.js-framework-filter-q');
                const themeSelect = frameworkModalBody.querySelector('.js-framework-filter-theme');
                const roleSelect = frameworkModalBody.querySelector('.js-framework-filter-role');
                const yearSelect = frameworkModalBody.querySelector('.js-framework-filter-year');
                const clearButton = frameworkModalBody.querySelector('.js-framework-filter-clear');
                filterInput?.addEventListener('input', () => {
                    frameworkModalState.filters.q = filterInput.value;
                    frameworkModalState.page = 1;
                    renderFrameworkModalPage('q');
                });
                themeSelect?.addEventListener('change', () => {
                    frameworkModalState.filters.theme = themeSelect.value;
                    frameworkModalState.page = 1;
                    renderFrameworkModalPage('theme');
                });
                roleSelect?.addEventListener('change', () => {
                    frameworkModalState.filters.role = roleSelect.value;
                    frameworkModalState.page = 1;
                    renderFrameworkModalPage('role');
                });
                yearSelect?.addEventListener('change', () => {
                    frameworkModalState.filters.year = yearSelect.value;
                    frameworkModalState.page = 1;
                    renderFrameworkModalPage('year');
                });
                clearButton?.addEventListener('click', () => {
                    frameworkModalState.filters = { q: '', year: '', role: '', theme: '' };
                    frameworkModalState.page = 1;
                    renderFrameworkModalPage();
                });
                frameworkModalBody.querySelectorAll('.js-framework-page').forEach((button) => {
                    button.addEventListener('click', () => {
                        frameworkModalState.page = Number(button.dataset.page || 1);
                        renderFrameworkModalPage();
                        frameworkModalBody.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                });
                if (focusTarget === 'q') {
                    const nextInput = frameworkModalBody.querySelector('.js-framework-filter-q');
                    nextInput?.focus();
                    nextInput?.setSelectionRange?.(nextInput.value.length, nextInput.value.length);
                }
                if (focusTarget === 'year') {
                    frameworkModalBody.querySelector('.js-framework-filter-year')?.focus();
                }
                if (focusTarget === 'theme') {
                    frameworkModalBody.querySelector('.js-framework-filter-theme')?.focus();
                }
                if (focusTarget === 'role') {
                    frameworkModalBody.querySelector('.js-framework-filter-role')?.focus();
                }
            };
            const setFrameworkModalItems = (items, unit, initialFilters = {}) => {
                frameworkModalState = {
                    items: sortedFrameworkItems(items),
                    page: 1,
                    unit: unit || 'รายการ',
                    filters: {
                        q: initialFilters.q || '',
                        year: initialFilters.year || '',
                        role: initialFilters.role || '',
                        theme: initialFilters.theme || ''
                    }
                };
                renderFrameworkModalPage();
            };
            const showFrameworkModal = () => {
                if (!frameworkModal) return;
                if (frameworkModal.parentElement !== document.body) {
                    document.body.appendChild(frameworkModal);
                }
                const compactModal = window.matchMedia?.('(max-width: 640px)')?.matches;
                Object.assign(frameworkModal.style, {
                    position: 'fixed',
                    inset: '0',
                    zIndex: '9999',
                    display: 'flex',
                    alignItems: compactModal ? 'stretch' : 'center',
                    justifyContent: 'center',
                    background: 'rgba(15, 23, 42, .52)',
                    padding: compactModal ? '8px' : '24px'
                });
                if (frameworkModalCard) {
                    Object.assign(frameworkModalCard.style, {
                        width: compactModal ? '100%' : 'min(1720px, calc(100vw - 20px))',
                        maxWidth: 'none',
                        maxHeight: compactModal ? 'calc(100vh - 16px)' : '92vh',
                        overflow: 'hidden',
                        background: '#ffffff',
                        border: `1px solid ${frameworkModalTone.border}`,
                        borderRadius: compactModal ? '12px' : '16px',
                        boxShadow: '0 28px 70px rgba(15, 23, 42, .28)'
                    });
                }
                if (frameworkModalHeader) {
                    Object.assign(frameworkModalHeader.style, {
                        background: frameworkModalTone.header,
                        borderBottomColor: frameworkModalTone.border
                    });
                }
                document.body.style.overflow = 'hidden';
                frameworkModal.classList.remove('hidden');
                frameworkModal.classList.add('flex');
                frameworkModal.setAttribute('aria-hidden', 'false');
            };
            const openFrameworkModal = (datasetKey, year, count) => {
                const items = normalizeFrameworkItems(policyFrameworkTrendItems?.[datasetKey]?.[String(year)]);
                if (!frameworkModal || !frameworkModalBody) return;
                frameworkModalTone = frameworkToneStyles[datasetKey] || frameworkToneStyles.trust;
                frameworkModalTitle.textContent = frameworkTrendMeta[datasetKey] || 'รายการ';
                frameworkModalSubtitle.innerHTML = `<span class="inline-flex items-baseline gap-2"><span class="text-2xl font-semibold" style="color:${frameworkModalTone.accent}">ปี ${escapeFrameworkHtml(year)}</span><span class="text-base font-semibold text-slate-700">${items.length.toLocaleString()} ${escapeFrameworkHtml(frameworkTrendUnits[datasetKey] || 'รายการ')}</span></span>`;
                setFrameworkModalItems(items, frameworkTrendUnits[datasetKey] || 'รายการ', { year: String(year || '') });
                showFrameworkModal();
            };
            const openFrameworkAllModal = (datasetKey, label, unit) => {
                const items = normalizeFrameworkItems(policyFrameworkAllItems?.[datasetKey]);
                if (!frameworkModal || !frameworkModalBody) return;
                frameworkModalTone = frameworkToneStyles[datasetKey] || frameworkToneStyles.trust;
                frameworkModalTitle.textContent = label || frameworkTrendMeta[datasetKey] || 'รายการ';
                frameworkModalSubtitle.innerHTML = `<span class="text-base font-semibold text-slate-700">รวม ${items.length.toLocaleString()} ${escapeFrameworkHtml(unit || frameworkTrendUnits[datasetKey] || 'รายการ')}</span>`;
                setFrameworkModalItems(items, unit || frameworkTrendUnits[datasetKey] || 'รายการ');
                showFrameworkModal();
            };
            const closeFrameworkModal = () => {
                if (frameworkModal) {
                    frameworkModal.style.display = 'none';
                    frameworkModal.setAttribute('aria-hidden', 'true');
                }
                document.body.style.overflow = '';
                frameworkModal?.classList.add('hidden');
                frameworkModal?.classList.remove('flex');
            };
            frameworkModalClose?.addEventListener('click', closeFrameworkModal);
            frameworkModal?.addEventListener('click', (event) => {
                if (event.target === frameworkModal) closeFrameworkModal();
            });
            document.querySelectorAll('[data-framework-key]').forEach((card) => {
                card.addEventListener('click', () => {
                    openFrameworkAllModal(card.dataset.frameworkKey, card.dataset.frameworkLabel, card.dataset.frameworkUnit);
                });
            });

            try {
                renderChart('chartPolicyFrameworkTrend', {
                    type: 'line',
                    data: {
                        labels: frameworkTrendLabels,
                        datasets: frameworkTrendDatasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: chartAnim,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            x: { ticks: { color: textColor }, grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                suggestedMax: suggestedAxisMax(frameworkTrendValues, 10),
                                ticks: { color: textColor, precision: 0 },
                                grid: { color: gridColor }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: textColor, boxWidth: 14, boxHeight: 8, padding: 10 }
                            },
                            datalabels: {
                                display: false
                            }
                        },
                        onHover: (event, elements) => {
                            const target = event?.native?.target || event?.chart?.canvas || document.getElementById('chartPolicyFrameworkTrend');
                            if (target) target.style.cursor = elements.length ? 'pointer' : 'default';
                        },
                        onClick: (event, elements, chart) => {
                            if (!elements.length) return;
                            const point = elements[0];
                            const dataset = chart.data.datasets[point.datasetIndex];
                            const year = chart.data.labels[point.index];
                            const count = dataset.data[point.index];
                            openFrameworkModal(dataset.key, year, count);
                        }
                    }
                });
            } catch (error) {
                console.error('Unable to initialize policy framework trend', error);
            }

            const sirirajClinicalRows = Array.isArray(sirirajClinicalTrendRows)
                ? sirirajClinicalTrendRows
                : (sirirajClinicalTrendRows && typeof sirirajClinicalTrendRows === 'object' ? Object.values(sirirajClinicalTrendRows) : []);
            const sirirajClinicalTrendLabels = safeLabels(sirirajClinicalRows.map((row) => row.year));
            const sirirajClinicalPeople = sirirajClinicalTrendLabels.map((_, index) => Number(sirirajClinicalRows?.[index]?.people || 0));
            const sirirajClinicalProjects = sirirajClinicalTrendLabels.map((_, index) => Number(sirirajClinicalRows?.[index]?.projects || 0));
            renderChart('chartSirirajClinicalTrend', {
                type: 'line',
                data: {
                    labels: sirirajClinicalTrendLabels,
                    datasets: [
                        {
                            label: 'แพทย์',
                            data: sirirajClinicalPeople,
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, .12)',
                            pointBackgroundColor: '#4f46e5',
                            tension: .35,
                            fill: true
                        },
                        {
                            label: 'โครงการที่มีแพทย์ศิริราชร่วม',
                            data: sirirajClinicalProjects,
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217, 119, 6, .10)',
                            pointBackgroundColor: '#d97706',
                            tension: .35,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: chartAnim,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            suggestedMax: suggestedAxisMax([...sirirajClinicalPeople, ...sirirajClinicalProjects], 5),
                            ticks: { color: textColor, precision: 0 },
                            grid: { color: gridColor }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor, boxWidth: 14, boxHeight: 8, padding: 10 }
                        },
                        datalabels: {
                            align: (context) => context.datasetIndex === 0 ? 'top' : 'bottom',
                            anchor: (context) => context.datasetIndex === 0 ? 'end' : 'start',
                            offset: (context) => context.datasetIndex === 0 ? 8 : 10,
                            color: '#334155',
                            font: { weight: '700', size: 10 },
                            formatter: (value) => Number(value || 0) > 0 ? Number(value) : ''
                        }
                    }
                }
            });

            const clinicalLedYears = safeLabels(clinicalLedIntegrationTrend?.years || []);
            const clinicalLedAgeingPeople = clinicalLedYears.map((_, index) => Number(clinicalLedIntegrationTrend?.ageing_people?.[index] || 0));
            const clinicalLedRwePeople = clinicalLedYears.map((_, index) => Number(clinicalLedIntegrationTrend?.rwe_people?.[index] || 0));
            const openClinicalLedIntegrationModal = (themeKey, year) => {
                const items = normalizeFrameworkItems(clinicalLedIntegrationItemsByThemeYear?.[themeKey]?.[String(year)]);
                if (!frameworkModal || !frameworkModalBody) return;
                const themeLabel = themeKey === 'rwe' ? 'Real-World Evidence (RWE)' : 'Ageing and Frailty';
                frameworkModalTone = frameworkToneStyles.clinical_led;
                frameworkModalTitle.textContent = themeLabel;
                frameworkModalSubtitle.innerHTML = `<span class="inline-flex items-baseline gap-2"><span class="text-2xl font-semibold" style="color:${frameworkModalTone.accent}">ปี ${escapeFrameworkHtml(year)}</span><span class="text-base font-semibold text-slate-700">${items.length.toLocaleString()} โครงการ</span></span>`;
                setFrameworkModalItems(items, 'โครงการ', { year: String(year || ''), theme: themeLabel });
                showFrameworkModal();
            };
            renderChart('chartClinicalLedIntegrationTrend', {
                type: 'line',
                data: {
                    labels: clinicalLedYears,
                    datasets: [
                        {
                            key: 'ageing',
                            label: 'Ageing',
                            data: clinicalLedAgeingPeople,
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, .12)',
                            pointBackgroundColor: '#4f46e5',
                            tension: .35,
                            fill: true
                        },
                        {
                            key: 'rwe',
                            label: 'Real-World Evidence (RWE)',
                            data: clinicalLedRwePeople,
                            borderColor: '#0f766e',
                            backgroundColor: 'rgba(15, 118, 110, .10)',
                            pointBackgroundColor: '#0f766e',
                            tension: .35,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: chartAnim,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            suggestedMax: suggestedAxisMax([...clinicalLedAgeingPeople, ...clinicalLedRwePeople], 5),
                            ticks: { color: textColor, precision: 0 },
                            grid: { color: gridColor }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor, boxWidth: 14, boxHeight: 8, padding: 10 }
                        },
                        datalabels: {
                            color: '#334155',
                            anchor: 'end',
                            align: 'top',
                            font: { weight: '700', size: 10 },
                            formatter: (value) => Number(value || 0) > 0 ? Number(value) : ''
                        }
                    },
                    onHover: (event, elements) => {
                        const target = event?.native?.target || event?.chart?.canvas || document.getElementById('chartClinicalLedIntegrationTrend');
                        if (target) target.style.cursor = elements.length ? 'pointer' : 'default';
                    },
                    onClick: (event, elements, chart) => {
                        if (!elements.length) return;
                        const point = elements[0];
                        const dataset = chart.data.datasets?.[point.datasetIndex] || {};
                        const year = chart.data.labels?.[point.index];
                        openClinicalLedIntegrationModal(dataset.key || 'ageing', year);
                    }
                }
            });

            const dataLiteracyYears = safeLabels(dataLiteracyTrendYears || []);
            const dataLiteracyRealWorld = dataLiteracyYears.map((_, index) => Number(dataLiteracyTrendSeries?.['Real-World data']?.[index] || 0));
            const dataLiteracyPrimary = dataLiteracyYears.map((_, index) => Number(dataLiteracyTrendSeries?.['Primary data collection']?.[index] || 0));
            renderChart('chartDataLiteracyTrend', {
                type: 'line',
                data: {
                    labels: dataLiteracyYears,
                    datasets: [
                        {
                            label: 'Real-World data',
                            data: dataLiteracyRealWorld,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, .12)',
                            pointBackgroundColor: '#2563eb',
                            tension: .35,
                            fill: true
                        },
                        {
                            label: 'Primary data collection',
                            data: dataLiteracyPrimary,
                            borderColor: '#0f766e',
                            backgroundColor: 'rgba(15, 118, 110, .10)',
                            pointBackgroundColor: '#0f766e',
                            tension: .35,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: chartAnim,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            suggestedMax: suggestedAxisMax([...dataLiteracyRealWorld, ...dataLiteracyPrimary], 5),
                            ticks: { color: textColor, precision: 0 },
                            grid: { color: gridColor }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor, boxWidth: 14, boxHeight: 8, padding: 10 }
                        },
                        datalabels: {
                            color: '#334155',
                            anchor: 'end',
                            align: 'top',
                            font: { weight: '700', size: 10 },
                            formatter: (value) => Number(value || 0) > 0 ? Number(value) : ''
                        }
                    }
                }
            });

            const policyTrendLabels = safeLabels(policyImpactTrendYears);
            const policyTrendDatasetMeta = {
                EA109: { label: 'Change in Practices / Procedures / Policy', unit: 'รายการ' },
                EA102: { label: 'Reports and White Papers', unit: 'รายการ' },
                EA106: { label: 'Policy Recommendations', unit: 'รายการ' }
            };
            const openPolicyImpactTrendModal = (year) => {
                const items = normalizeFrameworkItems(policyImpactTrendItemsByYear?.[String(year)]);
                if (!frameworkModal || !frameworkModalBody) return;
                frameworkModalTone = frameworkToneStyles.policy_impact;
                frameworkModalTitle.textContent = 'Contribution';
                frameworkModalSubtitle.innerHTML = `<span class="inline-flex items-baseline gap-2"><span class="text-2xl font-semibold" style="color:${frameworkModalTone.accent}">ปี ${escapeFrameworkHtml(year)}</span><span class="text-base font-semibold text-slate-700">${items.length.toLocaleString()} รายการ</span></span>`;
                setFrameworkModalItems(items, 'รายการ', { year: String(year || '') });
                showFrameworkModal();
            };
            const policyTrendDatasets = [
                {
                    key: 'EA109',
                    label: 'Change in Practices / Procedures / Policy',
                    data: policyTrendLabels.map((_, index) => Number(policyImpactTrendSeries?.EA109?.[index] || 0)),
                    borderColor: '#d97706',
                    backgroundColor: 'rgba(217, 119, 6, .14)',
                    pointBackgroundColor: '#d97706',
                    tension: .35,
                    fill: true
                },
                {
                    key: 'EA102',
                    label: 'Reports and White Papers',
                    data: policyTrendLabels.map((_, index) => Number(policyImpactTrendSeries?.EA102?.[index] || 0)),
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15, 118, 110, .12)',
                    pointBackgroundColor: '#0f766e',
                    tension: .35,
                    fill: true
                },
                {
                    key: 'EA106',
                    label: 'Policy Recommendations',
                    data: policyTrendLabels.map((_, index) => Number(policyImpactTrendSeries?.EA106?.[index] || 0)),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, .12)',
                    pointBackgroundColor: '#2563eb',
                    tension: .35,
                    fill: true
                }
            ];
            const policyTrendTotals = policyTrendLabels.map((_, index) => policyTrendDatasets.reduce((sum, dataset) => sum + Number(dataset.data[index] || 0), 0));

            renderChart('chartPolicyImpactTrend', {
                type: 'line',
                data: {
                    labels: policyTrendLabels,
                    datasets: policyTrendDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: chartAnim,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            suggestedMax: suggestedAxisMax(policyTrendTotals, 5),
                            ticks: { color: textColor, precision: 0 },
                            grid: { color: gridColor }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor, boxWidth: 14, boxHeight: 8, padding: 10 }
                        },
                        datalabels: {
                            align: 'top',
                            anchor: 'end',
                            color: '#334155',
                            font: { weight: '700', size: 10 },
                            formatter: (value) => Number(value || 0) > 0 ? Number(value) : ''
                        }
                    },
                    onHover: (event, elements) => {
                        const target = event?.native?.target || event?.chart?.canvas || document.getElementById('chartPolicyImpactTrend');
                        if (target) target.style.cursor = elements.length ? 'pointer' : 'default';
                    },
                    onClick: (event, elements, chart) => {
                        if (!elements.length) return;
                        const point = elements[0];
                        const year = chart.data.labels[point.index];
                        openPolicyImpactTrendModal(year);
                    }
                }
            });

            renderChart('chartOutputTypes', {
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

            renderChart('chartPaperRanking', {
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

            renderChart('chartPresentationActivity', {
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

            renderChart('chartTextbookYear', {
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

            renderChart('chartNetworkingYear', {
                type: 'bar',
                data: {
                    labels: safeLabels(networkingYearLabels),
                    datasets: [
                        {
                            label: 'EA115 Capacity Building / Knowledge Sharing',
                            data: safeValues(networkingEa115),
                            backgroundColor: '#d97706',
                            borderColor: '#b45309',
                            borderWidth: 1,
                            borderRadius: 8,
                            maxBarThickness: 68
                        },
                        {
                            label: 'EA117 MOU',
                            data: safeValues(networkingEa117),
                            backgroundColor: '#059669',
                            borderColor: '#047857',
                            borderWidth: 1,
                            borderRadius: 8,
                            maxBarThickness: 68
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: chartAnim,
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: suggestedAxisMax([...safeValues(networkingEa115), ...safeValues(networkingEa117)], 5),
                            ticks: { precision: 0, color: textColor },
                            grid: { color: gridColor }
                        },
                        x: {
                            ticks: { color: textColor },
                            grid: { color: gridColor }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor, boxWidth: 18, boxHeight: 10, padding: 14 }
                        },
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

            const form = document.getElementById('vizFilters');
            const loading = document.getElementById('filterLoading');
            if (form) {
                const ajaxState = window.__outputVizAjaxState || (window.__outputVizAjaxState = {
                    controller: null,
                    popstateBound: false,
                });
                const getFilterUrl = () => {
                    const url = new URL(form.action, window.location.origin);
                    const params = new URLSearchParams(new FormData(form));
                    params.forEach((value, key) => {
                        const cleanValue = String(value || '').trim();
                        if (cleanValue !== '') {
                            url.searchParams.set(key, cleanValue);
                        }
                    });
                    return url;
                };
                const executeVisualizationScript = (doc) => {
                    const script = Array.from(doc.scripts).find((item) =>
                        item.textContent.includes('OutlabelsWithLinesPlugin')
                    );
                    if (!script) return;

                    const nextScript = document.createElement('script');
                    nextScript.textContent = script.textContent;
                    document.body.appendChild(nextScript);
                    nextScript.remove();
                };
                const loadVisualization = async (url, options = {}) => {
                    const push = options.push !== false;
                    const scrollY = window.scrollY;

                    ajaxState.controller?.abort();
                    ajaxState.controller = new AbortController();
                    if (loading) loading.classList.remove('hidden');

                    try {
                        const response = await fetch(url.toString(), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            signal: ajaxState.controller.signal,
                        });
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);

                        const html = await response.text();
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const currentPage = document.querySelector('.viz-page');
                        const nextPage = doc.querySelector('.viz-page');
                        if (!currentPage || !nextPage) throw new Error('Visualization content not found');

                        nextPage.querySelectorAll('.fade-up').forEach((el) => el.classList.add('in'));
                        currentPage.replaceWith(nextPage);
                        if (doc.title) document.title = doc.title;
                        if (push) window.history.pushState({}, '', url);
                        window.scrollTo(0, scrollY);
                        executeVisualizationScript(doc);
                    } catch (error) {
                        if (error.name === 'AbortError') return;
                        window.location.href = url.toString();
                    }
                };

                let timer = null;
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    loadVisualization(getFilterUrl());
                });
                form.querySelectorAll('.js-auto-filter').forEach((el) => {
                    el.addEventListener('change', () => {
                        window.clearTimeout(timer);
                        timer = window.setTimeout(() => loadVisualization(getFilterUrl()), 160);
                    });
                });
                form.querySelector('a[href]')?.addEventListener('click', (event) => {
                    event.preventDefault();
                    loadVisualization(new URL(event.currentTarget.href), { push: true });
                });
                if (!ajaxState.popstateBound) {
                    ajaxState.popstateBound = true;
                    window.addEventListener('popstate', () => {
                        loadVisualization(new URL(window.location.href), { push: false });
                    });
                }
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

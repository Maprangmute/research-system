@extends(($isPublicSeminar ?? false) ? 'layouts.public' : 'layouts.app')

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
                'label' => 'พันธกิจ',
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
            'EA117' => 'Memorandum of Understanding (MOU)',
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
        $projectTypeTonePalette = [
            ['bg' => '#fffdfd', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
            ['bg' => '#fffefd', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
            ['bg' => '#fffefd', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
            ['bg' => '#fefffd', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
            ['bg' => '#fdfffe', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
            ['bg' => '#fdfffe', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
            ['bg' => '#fdffff', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
            ['bg' => '#fdfeff', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
            ['bg' => '#fdfeff', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
            ['bg' => '#fdfdff', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
            ['bg' => '#fefdff', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
            ['bg' => '#fffdff', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
            ['bg' => '#fffdfd', 'cell' => '#ffffff', 'text' => '#0f172a', 'border' => '#e8edf3'],
        ];
        $projectTypeToneStyle = static function ($label) use ($projectTypeTonePalette): string {
            $normalized = mb_strtolower(trim((string) $label), 'UTF-8');
            if ($normalized === '') {
                return '--doctor-row-bg:#f8fafc; --doctor-row-cell-bg:#ffffff; --doctor-row-border:#e2e8f0; --doctor-row-accent:#475569;';
            }

            $hash = hexdec(substr(md5($normalized), 0, 8));
            $tone = $projectTypeTonePalette[$hash % count($projectTypeTonePalette)];

            return sprintf(
                '--doctor-row-bg:%s; --doctor-row-cell-bg:%s; --doctor-row-border:%s; --doctor-row-accent:%s;',
                $tone['bg'],
                $tone['cell'],
                $tone['border'],
                $tone['text']
            );
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
        $analysisInsightRemark = 'หมายเหตุ: การวิเคราะห์นี้เป็นการใช้ AI เพื่อการวิเคราะห์เบื้องต้น โดยอาศัยข้อมูลที่หน่วยงานนำเข้า ทั้งนี้ ยังจำเป็นต้องอาศัยการพิจารณาร่วมกันของทีมเพื่อให้ได้ข้อสรุปที่ชัดเจน และผลการวิเคราะห์อาจเปลี่ยนแปลงได้เมื่อมีข้อมูลเพิ่มเติม';
        $policyImpact = $policyImpact ?? [];
        $policyDataSourceCounts = collect($policyImpact['data_source_counts'] ?? []);
        $policyDataSourceTotal = max(1, (int) $policyDataSourceCounts->sum());
        $policyDataSourceTrend = $policyImpact['data_source_trend'] ?? ['years' => collect(), 'series' => collect()];
        $policyImpactOutputs = collect($policyImpact['impact_outputs'] ?? []);
        $policyHta = $policyImpact['hta'] ?? [];
        $policyClinicalLed = $policyImpact['clinical_led'] ?? [];
        $policyWeakness = $policyImpact['weakness'] ?? [];
        $policyImpactOutputItems = collect($policyImpact['impact_output_items'] ?? []);
        $policyImpactThemeAnalysis = collect($policyImpact['impact_theme_analysis'] ?? []);
        $policyImpactThemeNarrative = (object) ($policyImpact['impact_theme_narrative'] ?? [
            'overview' => 'ยังไม่มีข้อมูล output ในระดับธีมสำหรับช่วงที่เลือก.',
            'gap' => 'ยังไม่มีข้อมูลเพียงพอสำหรับอ่านช่องว่างของ output ตามธีม.',
            'effect' => 'ควรติดตามข้อมูลเพิ่มเติมก่อนสรุปผลต่อ policy impact.',
        ]);
        $policyImpactThemeNarrativeParagraph = trim((string) ($policyImpact['impact_theme_narrative_paragraph'] ?? 'ยังไม่มีข้อมูล output ในระดับธีมสำหรับช่วงที่เลือก.'));
        $researchInterestCategories = collect([
            ['category' => 'Aging / Frailty', 'interest' => 'ผู้สูงอายุ ภาวะเปราะบาง การฟื้นฟู การดูแลระยะยาว และ care pathway หลังเจ็บป่วย'],
            ['category' => 'Health Economics / HTA', 'interest' => 'ความคุ้มค่า ต้นทุน ผลกระทบงบประมาณ การประเมินนโยบายและเทคโนโลยีสุขภาพ'],
            ['category' => 'Medical Devices & Innovation', 'interest' => 'รากฟันเทียม กระดูกเทียม เท้าเทียม insulin pump และการประเมินนวัตกรรมก่อนใช้จริง'],
            ['category' => 'AI / Digital Health / Data Platform', 'interest' => 'AI อ่านภาพรังสี telemedicine data warehouse EMR linkage และระบบข้อมูลเพื่อการตัดสินใจ'],
            ['category' => 'Clinical Cohort / Real-world Data', 'interest' => 'registry, cohort, longitudinal data และฐานข้อมูลโรคเรื้อรังเพื่ออ่าน outcome ในโลกจริง'],
            ['category' => 'Precision / Early Diagnosis', 'interest' => 'BRCA screening, early detection, genetic access และการคัดกรองเชิงประชากร'],
            ['category' => 'Rational Drug Use', 'interest' => 'ความเหมาะสมในการใช้ยา prescribing quality, adverse drug events และ medication governance'],
            ['category' => 'Health System & Access', 'interest' => 'การเข้าถึงบริการ สิทธิประโยชน์ universal coverage reimbursement และ equity'],
            ['category' => 'Population / Public Health', 'interest' => 'PM2.5, urban health, global health, prevention และ health promotion'],
        ]);
        $researchInterestByType = collect([
            [
                'type' => '01 Aging and Frailty',
                'main' => 'สนใจระบบดูแลผู้สูงอายุแบบองค์รวม มากกว่าการรักษาโรคเฉพาะราย',
                'themes' => [
                    ['name' => 'Frailty & Healthy Aging', 'signals' => ['ภาวะเปราะบาง', 'functional decline', 'คุณภาพชีวิต', 'long-term care'], 'meaning' => 'เป็น core identity ของกลุ่ม และเชื่อมกับการเตรียมระบบสุขภาพสำหรับสังคมสูงวัย'],
                    ['name' => 'Hip Fracture / Orthogeriatrics', 'signals' => ['กระดูกสะโพกหัก', 'delirium', 'mortality', 'rehabilitation'], 'meaning' => 'สะท้อนความสนใจต่อ geriatric trauma pathway และ continuum of care'],
                    ['name' => 'Care Model Innovation', 'signals' => ['ทีมสหสาขา', 'integrated care', 'transitional care', 'intermediate care'], 'meaning' => 'เน้น redesign รูปแบบบริการ ไม่ใช่แค่ intervention รายโรค'],
                    ['name' => 'Data-driven Geriatrics', 'signals' => ['prediction model', 'risk stratification', 'outcome prediction'], 'meaning' => 'เริ่มใช้ข้อมูลเพื่อคาดการณ์ความเสี่ยงและ optimize outcome'],
                ],
                'reading' => 'หน่วยนี้กำลังสร้าง expertise ด้าน aging society, frailty system, geriatric service redesign และ outcome optimization',
            ],
            [
                'type' => '02 Health Systems and HTA',
                'main' => 'เน้น policy-facing research ที่เชื่อม health economics, innovation adoption และ reimbursement decision',
                'themes' => [
                    ['name' => 'Economic Evaluation', 'signals' => ['cost utility', 'economic evaluation', 'budget impact', 'value assessment'], 'meaning' => 'ตอบคำถามว่าเทคโนโลยีหรือบริการใดควรเข้าสู่ระบบสุขภาพไทย'],
                    ['name' => 'Medical Device Assessment', 'signals' => ['รากฟันเทียม', 'กระดูกเทียม', 'เท้าเทียม', 'insulin pump'], 'meaning' => 'โดดเด่นด้าน device evaluation มากกว่างานยาอย่างเดียว'],
                    ['name' => 'Innovation & Horizon Scanning', 'signals' => ['horizon scanning', 'innovation monitoring', 'preparedness'], 'meaning' => 'มีบทบาท policy foresight มองเทคโนโลยีที่กำลังจะเข้าระบบ'],
                    ['name' => 'Access & Equity', 'signals' => ['service access', 'reimbursement', 'transgender health'], 'meaning' => 'แสดงมิติ social policy และความเป็นธรรมในการเข้าถึงบริการ'],
                ],
                'reading' => 'นี่ไม่ใช่ HTA แบบ cost-effectiveness เท่านั้น แต่เป็น technology governance และ national program evaluation',
            ],
            [
                'type' => '03 Rational Drug Use (RDU)',
                'main' => 'สนใจคุณภาพการใช้ยา ความปลอดภัย และ governance ของ prescribing ในระบบบริการ',
                'themes' => [
                    ['name' => 'Appropriate Prescribing', 'signals' => ['metformin use', 'guideline adherence', 'prescribing appropriateness'], 'meaning' => 'เน้นลด variation และยกระดับคุณภาพการสั่งใช้ยา'],
                    ['name' => 'Medication Safety', 'signals' => ['adverse drug events', 'drug-related harm', 'hospitalization'], 'meaning' => 'เชื่อมการใช้ยากับผลลัพธ์และความปลอดภัยของผู้ป่วย'],
                    ['name' => 'Drug Governance', 'signals' => ['ใช้ยาอย่างสมเหตุผล', 'quality improvement', 'hospital policy'], 'meaning' => 'เป็นหน่วย practical ที่เน้น operational policy มากกว่า innovation frontier'],
                ],
                'reading' => 'เหมาะกับบทบาท medication policy, quality improvement และ hospital prescribing governance',
            ],
            [
                'type' => '04 Real-World Evidence (RWE) and Clinical-Technical Cohorts',
                'main' => 'เป็นกลุ่ม data-heavy ที่เน้น registry, cohort, real-world evidence และ health informatics',
                'themes' => [
                    ['name' => 'Clinical Data Infrastructure', 'signals' => ['electronic database', 'data warehouse', 'linkage', 'EMR integration'], 'meaning' => 'สร้าง infrastructure สำหรับ evidence generation จาก routine data'],
                    ['name' => 'Chronic Disease Cohorts', 'signals' => ['เบาหวาน', 'ความดัน', 'fatty liver', 'cirrhosis'], 'meaning' => 'ติดตาม disease progression และ complication ในระยะยาว'],
                    ['name' => 'AI-enabled Healthcare', 'signals' => ['AI chest X-ray', 'telemedicine', 'digital literacy'], 'meaning' => 'ใช้ digital transformation เป็นเครื่องมือเชื่อมข้อมูลและบริการจริง'],
                    ['name' => 'Precision / Genomic Access', 'signals' => ['BRCA1/2', 'genetic screening', 'early detection'], 'meaning' => 'เริ่มเชื่อม precision medicine กับการเข้าถึงและระบบบริการ'],
                ],
                'reading' => 'ใกล้เคียง clinical informatics, data science for healthcare และ observational epidemiology มากที่สุด',
            ],
            [
                'type' => '05 Global Health',
                'main' => 'ไม่ได้เน้น disease-specific research แต่เน้น policy dialogue, global collaboration และ agenda setting',
                'themes' => [
                    ['name' => 'Universal Health Coverage', 'signals' => ['financing', 'coverage', 'sustainability'], 'meaning' => 'เชื่อมหลักประกันสุขภาพกับ governance และความยั่งยืน'],
                    ['name' => 'Environmental / Urban Health', 'signals' => ['PM2.5', 'urban health', 'public health'], 'meaning' => 'เปิดพื้นที่ต่อโจทย์สุขภาพประชากรและปัจจัยแวดล้อม'],
                    ['name' => 'International Academic Platform', 'signals' => ['PMAC', 'global conference', 'knowledge diplomacy'], 'meaning' => 'ทำหน้าที่เชื่อมเครือข่ายและกำหนดวาระเชิงนโยบายระหว่างประเทศ'],
                ],
                'reading' => 'เป็นกลุ่ม public policy, international health และ strategic health agenda มากกว่างาน clinical research',
            ],
            [
                'type' => '06 Other Areas',
                'main' => 'เป็น innovation sandbox สำหรับโจทย์ที่ยังไม่ fit กับ cluster หลัก แต่มีสัญญาณเชิงระบบที่น่าสนใจ',
                'themes' => [
                    ['name' => 'AI Translation to Public Hospitals', 'signals' => ['AI chest X-ray', 'knowledge transfer', 'deployment'], 'meaning' => 'เน้นการนำเทคโนโลยีไปใช้จริง ไม่ใช่แค่ proof of concept'],
                    ['name' => 'Community / Religious Health', 'signals' => ['พระสงฆ์', 'community participation', 'health promotion'], 'meaning' => 'สะท้อนการออกแบบสุขภาพเชิงบริบทและกลุ่มประชากรเฉพาะ'],
                    ['name' => 'Supportive Oncology / Nutrition', 'signals' => ['body composition', 'chemotherapy outcomes'], 'meaning' => 'เชื่อมผลลัพธ์ทางคลินิกกับคุณภาพชีวิตและการดูแลสนับสนุน'],
                ],
                'reading' => 'เป็นพื้นที่ทดลอง cross-cutting innovation และ evaluation framework',
            ],
        ]);
        $researchInterestMetaPatterns = collect([
            ['title' => 'System-centric problems', 'text' => 'ภาพรวมไม่ได้ขับเคลื่อนด้วยโรคเป็นหลัก แต่ถามว่าระบบดูแลทำงานดีไหม เข้าถึงได้ไหม คุ้มค่าไหม และ outcome ดีขึ้นหรือไม่'],
            ['title' => 'Health system engineering', 'text' => 'หลายโครงการมุ่ง redesign workflow, care pathway, service model และการ scale มากกว่างานวิชาการเพื่อ publication อย่างเดียว'],
            ['title' => 'Technology as infrastructure', 'text' => 'AI, telemedicine, registry และ database ถูกมองเป็น infrastructure สำหรับระบบจริง ไม่ใช่ gadget หรือ showcase'],
            ['title' => 'Real-world evidence mindset', 'text' => 'ให้คุณค่ากับ routine data, cohort, EMR, implementation outcome และหลักฐานจากบริบทบริการจริง'],
            ['title' => 'Technology governance', 'text' => 'HTA ถูกใช้เพื่อตอบว่าประเทศพร้อมไหม scale ได้ไหม equity เป็นอย่างไร และควรออกแบบ reimbursement อย่างไร'],
            ['title' => 'Evidence-to-policy organization', 'text' => 'ปลายทางของงานจำนวนมากคือ policy, reimbursement, implementation, service redesign และ national adoption'],
        ]);
        $researchInterestCapabilities = collect([
            ['capability' => 'Aging & Integrated Care', 'types' => '01'],
            ['capability' => 'Health Economics & HTA', 'types' => '02'],
            ['capability' => 'Drug Governance & Medication Safety', 'types' => '03'],
            ['capability' => 'Data Platform / AI / RWE', 'types' => '04'],
            ['capability' => 'Public Policy & Global Health', 'types' => '05'],
            ['capability' => 'Emerging / Cross-cutting Innovation', 'types' => '06'],
        ]);
        $researchInterestTakeaways = collect([
            'ระบบดูแลผู้สูงอายุและ frailty เป็นแกนที่เด่นมากของ portfolio',
            'HTA ของหน่วยมีบทบาทกว้างถึง technology governance และ reimbursement design',
            'AI / digital health / data platform ถูกใช้เพื่อฝังในระบบบริการจริง',
            'RWE และ cohort เป็นฐานสำคัญของ learning health system',
            'ภาพรวมสะท้อนองค์กรแบบ evidence-to-policy และ healthcare transformation platform',
        ]);
        $policyImpactThemeAiFacts = $policyImpactThemeAnalysis
            ->map(fn ($theme) => [
                'theme' => (string) ($theme->theme ?? ''),
                'project_count' => (int) ($theme->project_count ?? 0),
                'output_count' => (int) ($theme->output_count ?? 0),
                'dominant_code' => (string) ($theme->dominant_code ?? ''),
                'dominant_label' => (string) ($theme->dominant_label ?? ''),
                'dominant_count' => (int) ($theme->dominant_count ?? 0),
                'main_weakness_code' => (string) ($theme->main_weakness_code ?? ''),
                'main_weakness_label' => (string) ($theme->main_weakness_label ?? ''),
                'main_weakness_with' => (int) ($theme->main_weakness_with ?? 0),
                'main_weakness_missing' => (int) ($theme->main_weakness_missing ?? 0),
            ])
            ->values();
        $policyImpactTrend = $policyImpact['impact_trend'] ?? ['years' => collect(), 'series' => collect()];
        $policyFrameworkTrend = $policyImpact['framework_trend'] ?? ['years' => collect(), 'series' => collect(), 'interpretation' => collect()];
        $policyFrameworkInterpretation = collect($policyFrameworkTrend['interpretation'] ?? []);
        $policyFrameworkSummary = $policyFrameworkTrend['summary'] ?? null;
        $policyPeopleHighlights = collect($policyImpact['people_highlights'] ?? []);
        $doctorContributionStarts = collect($policyImpact['doctor_contribution_starts'] ?? []);
        $doctorContributionTimeline = collect($policyImpact['doctor_contribution_timeline'] ?? []);
        $contributionNetwork = $policyImpact['contribution_network'] ?? [];
        $peopleTypeMix = collect($contributionNetwork['people_type_mix'] ?? []);
        $doctorContributionRoleTables = collect($policyImpact['doctor_contribution_role_tables'] ?? []);
        $unitContributionRoleThemeTables = collect($policyImpact['unit_contribution_role_theme_tables'] ?? []);
        $expertTrend = $contributionNetwork['expert_trend'] ?? ['years' => collect(), 'people' => collect(), 'new_people' => collect(), 'projects' => collect()];
        $newExpertsByYear = collect($contributionNetwork['new_experts_by_year'] ?? []);
        $clinicalLedIntegration = $contributionNetwork['clinical_led_integration'] ?? [];
        $clinicalLedIntegrationTrend = $clinicalLedIntegration['trend'] ?? ['years' => collect(), 'people' => collect(), 'projects' => collect(), 'ageing_projects' => collect(), 'hta_projects' => collect(), 'rwe_projects' => collect()];
        $clinicalLedPeopleByYear = collect($clinicalLedIntegration['people_by_year'] ?? []);
        $clinicalLedIntegrationThemes = collect($clinicalLedIntegration['themes'] ?? []);
        $clinicalLedIntegrationRoleStats = collect($clinicalLedIntegration['role_stats'] ?? []);
        $clinicalLedIntegrationRoleTrend = collect($clinicalLedIntegration['role_trend'] ?? []);
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
            'Real-World Data' => ['short' => 'Real-World Data', 'tag' => 'Existing data', 'color' => '#2563eb'],
            'Primary Data' => ['short' => 'Primary Data', 'tag' => 'Newly collected data', 'color' => '#0f766e'],
            'Real-World Data + Primary Data' => ['short' => 'RWD + Primary', 'tag' => 'Mixed data source', 'color' => '#7c3aed'],
        ];
        $policyDataSourcePeak = $policyDataSourceCounts->sortDesc()->keys()->first();
        $policyDataSourcePeakCount = (int) ($policyDataSourceCounts[$policyDataSourcePeak] ?? 0);
        $policyImpactCodeLabels = [
            'EA101' => 'Research Paper and Journal Article',
            'EA102' => 'Reports and White Papers',
            'EA106' => 'Policy Recommendations',
            'EA109' => 'Change in Practices / Procedures / Policy',
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
        $policyImpactTopCount = (int) ($policyImpactOutputs[$policyImpactTopCode] ?? 0);
        $policyImpactTrendDirection = (int) ($policyImpactTrendLast?->total ?? 0) > (int) ($policyImpactTrendFirst?->total ?? 0)
            ? 'เพิ่มขึ้น'
            : ((int) ($policyImpactTrendLast?->total ?? 0) < (int) ($policyImpactTrendFirst?->total ?? 0) ? 'ลดลง' : 'ทรงตัว');
        $trendShape = static function ($values): string {
            $numbers = collect($values)->map(fn ($value) => (int) $value)->values();
            if ($numbers->count() < 2) {
                return 'ข้อมูลยังมีไม่พอสำหรับอ่านแนวโน้ม';
            }

            $first = (int) $numbers->first();
            $last = (int) $numbers->last();
            $nonDecreasing = true;
            $nonIncreasing = true;

            for ($index = 1; $index < $numbers->count(); $index += 1) {
                if ((int) $numbers[$index] < (int) $numbers[$index - 1]) {
                    $nonDecreasing = false;
                }
                if ((int) $numbers[$index] > (int) $numbers[$index - 1]) {
                    $nonIncreasing = false;
                }
            }

            if ($nonDecreasing && $last > $first) {
                return 'เพิ่มขึ้นต่อเนื่อง';
            }
            if ($nonIncreasing && $last < $first) {
                return 'ลดลงต่อเนื่อง';
            }
            if ($last > $first) {
                return 'เพิ่มขึ้นเมื่อเทียบกับปีแรก แต่มีความผันผวนระหว่างทาง';
            }
            if ($last < $first) {
                return 'ลดลงเมื่อเทียบกับปีแรก และมีความผันผวนระหว่างทาง';
            }

            return 'ทรงตัวเมื่อเทียบปีแรกกับปีล่าสุด แต่มีความผันผวนระหว่างทาง';
        };
        $policyImpactLineSummaries = collect($policyImpactCodeLabels)
            ->map(function ($label, $code) use ($policyImpactTrendSummaryRows, $trendShape) {
                $rows = $policyImpactTrendSummaryRows->map(fn ($row) => (object) [
                    'year' => (int) ($row->year ?? 0),
                    'value' => (int) (collect($row->by_code ?? [])->get($code) ?? 0),
                ])->values();
                $first = $rows->first();
                $last = $rows->last();
                $peak = $rows->sortByDesc(fn ($row) => (int) ($row->value ?? 0))->first();
                $largestIncrease = null;
                for ($index = 1; $index < $rows->count(); $index += 1) {
                    $previous = $rows[$index - 1];
                    $current = $rows[$index];
                    $change = (int) ($current->value ?? 0) - (int) ($previous->value ?? 0);
                    if ($largestIncrease === null || $change > (int) ($largestIncrease['change'] ?? 0)) {
                        $largestIncrease = [
                            'from_year' => (int) ($previous->year ?? 0),
                            'to_year' => (int) ($current->year ?? 0),
                            'change' => $change,
                        ];
                    }
                }

                return (object) [
                    'code' => $code,
                    'label' => $label,
                    'rows' => $rows,
                    'first' => (int) ($first?->value ?? 0),
                    'last' => (int) ($last?->value ?? 0),
                    'peak' => (int) ($peak?->value ?? 0),
                    'peak_year' => (int) ($peak?->year ?? 0),
                    'total' => (int) $rows->sum('value'),
                    'shape' => $trendShape($rows->pluck('value')),
                    'largest_increase' => $largestIncrease,
                ];
            })
            ->values();
        $policyImpactTopLine = $policyImpactLineSummaries
            ->sortByDesc(fn ($row) => (int) ($row->total ?? 0))
            ->first();
        $policyImpactRecommendationLine = $policyImpactLineSummaries->firstWhere('code', 'EA106');
        $policyImpactPracticeLine = $policyImpactLineSummaries->firstWhere('code', 'EA109');
        $policyImpactReportLine = $policyImpactLineSummaries->firstWhere('code', 'EA102');
        $policyImpactPaperLine = $policyImpactLineSummaries->firstWhere('code', 'EA101');
        $policyImpactGraphFirst = $policyImpactTrendSummaryRows->first();
        $policyImpactGraphLast = $policyImpactTrendSummaryRows->last();
        $policyRecommendationIncrease = $policyImpactRecommendationLine?->largest_increase ?? null;
        $formatPolicyYearRange = static function ($firstYear, $lastYear): string {
            $firstYear = (int) $firstYear;
            $lastYear = (int) $lastYear;

            if ($firstYear <= 0 && $lastYear <= 0) {
                return 'ช่วงข้อมูลที่เลือก';
            }
            if ($firstYear === $lastYear) {
                return 'ปี ' . $firstYear;
            }

            return 'ช่วงปี ' . $firstYear . '-' . $lastYear;
        };
        $policyInsightYearRange = $formatPolicyYearRange($policyImpactGraphFirst?->year, $policyImpactGraphLast?->year);
        $hasCurrentYearInInsightData = collect()
            ->merge(collect($policyImpactTrend['years'] ?? []))
            ->merge(collect($clinicalLedIntegrationTrend['years'] ?? []))
            ->merge(collect($policyDataSourceTrend['years'] ?? []))
            ->merge($expertTrendRows->pluck('year'))
            ->contains(fn ($year) => (int) $year === (int) $currentYearBe);
        $incompleteCurrentYearNote = $hasCurrentYearInInsightData
            ? 'ปี ' . $currentYearBe . ' ยังเป็นปีที่ข้อมูลกำลังเข้า ไม่ควรสรุปว่า trend ลดลงถาวร'
            : null;
        $formatPolicyList = static function ($items): string {
            $items = collect($items)->filter()->values();

            if ($items->isEmpty()) {
                return 'ยังไม่มีข้อมูล';
            }
            if ($items->count() === 1) {
                return (string) $items->first();
            }

            return $items->slice(0, -1)->implode(', ') . ' และ ' . $items->last();
        };
        $lineHasEveryYear = static fn ($line): bool => collect($line?->rows ?? [])->isNotEmpty()
            && collect($line?->rows ?? [])->every(fn ($row) => (int) ($row->value ?? 0) > 0);
        $continuousMainLines = $policyImpactLineSummaries
            ->filter(fn ($line) => (int) ($line->total ?? 0) > 0 && $lineHasEveryYear($line))
            ->sortByDesc(fn ($line) => (int) ($line->total ?? 0))
            ->take(2)
            ->values();
        $fallbackMainLines = $policyImpactLineSummaries
            ->filter(fn ($line) => (int) ($line->total ?? 0) > 0)
            ->sortByDesc(fn ($line) => (int) ($line->total ?? 0))
            ->take(2)
            ->values();
        $mainLines = $continuousMainLines->isNotEmpty() ? $continuousMainLines : $fallbackMainLines;
        $mainLineNames = $formatPolicyList($mainLines->pluck('label'));
        $mainLineContinuityText = $continuousMainLines->isNotEmpty()
            ? 'ซึ่งมีจำนวนต่อเนื่องในทุกปี'
            : 'ซึ่งเป็นกลุ่มที่มีจำนวนสะสมสูงสุดในช่วงข้อมูลนี้';
        $policyRecommendationIncreaseText = ((int) ($policyRecommendationIncrease['change'] ?? 0) > 0)
            ? 'เริ่มเพิ่มขึ้นอย่างชัดเจนในช่วงปี ' . ($policyRecommendationIncrease['from_year'] ?? '-') . '-' . ($policyRecommendationIncrease['to_year'] ?? '-')
            : ((int) ($policyImpactRecommendationLine?->total ?? 0) > 0
                ? 'มีรายการเกิดขึ้นแล้ว แต่ยังไม่เห็นช่วงที่เพิ่มขึ้นชัดเจน'
                : 'ยังไม่มีรายการในช่วงข้อมูลนี้');
        $practiceRows = collect($policyImpactPracticeLine?->rows ?? []);
        $practiceActiveYears = $practiceRows->filter(fn ($row) => (int) ($row->value ?? 0) > 0)->count();
        $practiceYearCount = max(1, $practiceRows->count());
        $policyPracticePatternText = (int) ($policyImpactPracticeLine?->total ?? 0) <= 0
            ? 'ยังไม่มีรายการในช่วงข้อมูลนี้'
            : (((int) ($policyImpactPracticeLine?->peak ?? 0) <= 2 || $practiceActiveYears < $practiceYearCount)
                ? 'ยังมีจำนวนจำกัดและเกิดขึ้นไม่ต่อเนื่อง'
                : 'มีจำนวนต่อเนื่องมากขึ้นในหลายปี');
        $policyRecommendationIsGrowing = str_contains((string) ($policyImpactRecommendationLine?->shape ?? ''), 'เพิ่ม');
        $policyPracticeIsGrowing = str_contains((string) ($policyImpactPracticeLine?->shape ?? ''), 'เพิ่ม');
        $paperLineTotal = (int) ($policyImpactPaperLine?->total ?? 0);
        $publicationSupportMeaning = $paperLineTotal > 0
            ? ' โดย Research Paper and Journal Article ช่วยทำให้ evidence base มีความน่าเชื่อถือและอ้างอิงได้มากขึ้น เพราะผ่านการเผยแพร่และตรวจสอบในระบบวารสาร'
            : '';
        $publicationSupportAction = $paperLineTotal > 0
            ? ' ในภาพรวม publication จึงเป็นฐานต้นทางที่ช่วยให้รายงาน, policy brief, recommendation หรือ guideline มีหลักฐานรองรับ และเพิ่มโอกาสต่อยอดไปสู่ practice หรือ policy implementation ในระยะถัดไป'
            : '';
        $policyImpactTrendReading = $policyInsightYearRange . ' output หลักยังคงอยู่ในรูปแบบ ' . $mainLineNames . ' ' . $mainLineContinuityText . ' ขณะที่ Policy Recommendations ' . $policyRecommendationIncreaseText . ' ส่วน Change in Practices / Procedures / Policy ' . $policyPracticePatternText;
        $policyImpactMeaning = $policyRecommendationIsGrowing
            ? 'แนวโน้มดังกล่าวสะท้อนการพัฒนาจากการสร้างองค์ความรู้เชิงวิชาการ ไปสู่การสังเคราะห์และสื่อสารเชิงระบบมากขึ้น และเริ่มมีการต่อยอดสู่ข้อเสนอเชิงนโยบาย โดยเฉพาะในช่วงปี ' . ($policyRecommendationIncrease['from_year'] ?? ($policyImpactRecommendationLine?->peak_year ?: '-')) . '-' . ($policyRecommendationIncrease['to_year'] ?? ($policyImpactRecommendationLine?->peak_year ?: '-')) . ' อย่างไรก็ตาม การเปลี่ยนแปลงเชิงปฏิบัติหรือเชิงนโยบายยังปรากฏในระดับจำกัด' . $publicationSupportMeaning
            : 'แนวโน้มดังกล่าวสะท้อนว่าระบบยังอยู่ในช่วงสร้างและสะสมองค์ความรู้ผ่าน ' . $mainLineNames . ' เป็นหลัก ขณะที่การต่อยอดสู่ข้อเสนอเชิงนโยบายหรือการเปลี่ยนแปลงเชิงปฏิบัติยังไม่เพิ่มขึ้นอย่างชัดเจน' . $publicationSupportMeaning;
        $policyImpactWhatHappened = $policyRecommendationIsGrowing
            ? 'ภาพรวมสะท้อนให้เห็นว่า output ของระบบเริ่มมีเส้นทางการใช้ประโยชน์ (knowledge translation pathway) ที่ชัดขึ้น จากงานวิชาการและรายงาน ไปสู่ข้อเสนอเชิงนโยบายมากขึ้น อย่างไรก็ตาม การผลักดันให้เกิดการเปลี่ยนแปลงในระดับ practice หรือ policy implementation ยังอาจต้องอาศัยกลไกสนับสนุนเพิ่มเติมและระยะเวลาในการต่อยอด' . $publicationSupportAction
            : 'ภาพรวมสะท้อนให้เห็นว่า output ของระบบยังอยู่ในช่วงต้นของ knowledge translation pathway โดยมีงานวิชาการและรายงานเป็นฐานสำคัญ จึงควรติดตามต่อว่าหลักฐานเหล่านี้ถูกนำไปสังเคราะห์เป็นข้อเสนอเชิงนโยบาย หรือผลักดันสู่ practice และ policy implementation ได้มากขึ้นหรือไม่' . $publicationSupportAction;
        $policyImpactInsightAiSeries = collect($policyImpactTrendSummaryRows)
            ->map(fn ($row) => [
                'year' => (int) ($row->year ?? 0),
                'research_paper' => (int) (collect($row->by_code ?? [])->get('EA101') ?? 0),
                'change_in_practices' => (int) (collect($row->by_code ?? [])->get('EA109') ?? 0),
                'reports_white_papers' => (int) (collect($row->by_code ?? [])->get('EA102') ?? 0),
                'policy_recommendations' => (int) (collect($row->by_code ?? [])->get('EA106') ?? 0),
            ])
            ->values();
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
            overflow-x: visible;
        }
        .viz-page *,
        .viz-page *::before,
        .viz-page *::after {
            min-width: 0;
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
        @keyframes viz-rise-in {
            from {
                opacity: .01;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .fade-up {
            opacity: 1;
            transform: translateY(0);
            animation: viz-rise-in .42s ease both;
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
        .viz-pdf-popover {
            position: fixed;
            left: 14px;
            bottom: 18px;
            z-index: 75;
        }
        .viz-pdf-popover summary {
            list-style: none;
            cursor: pointer;
            width: 46px;
            height: 46px;
            border-radius: 999px;
            border: 1px solid #fecaca;
            background: #dc2626;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 900;
            box-shadow: 0 16px 34px rgba(127, 29, 29, .22);
        }
        .viz-pdf-popover summary::-webkit-details-marker {
            display: none;
        }
        .viz-pdf-popover[open] summary {
            background: #991b1b;
        }
        .viz-pdf-popover__panel {
            position: absolute;
            left: 54px;
            top: 0;
            width: 220px;
            border: 1px solid #fecaca;
            border-radius: 14px;
            background: #ffffff;
            padding: 10px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .18);
        }
        .viz-pdf-popover__link {
            display: flex;
            align-items: center;
            gap: 8px;
            border-radius: 10px;
            background: #fef2f2;
            padding: 9px 10px;
            color: #991b1b;
            font-size: 13px;
            font-weight: 800;
        }
        .viz-pdf-popover__link:hover {
            background: #fee2e2;
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
        .section-panel.nature-section {
            overflow: visible;
            transform: none;
            animation: none;
        }
        .section-panel.fte-skill-section {
            overflow: visible;
            transform: none;
        }
        .nature-shell,
        .nature-layout,
        .nature-rail {
            overflow: visible;
        }
        @media (min-width: 1280px) {
            .nature-sticky {
                position: sticky;
                top: 96px;
                align-self: flex-start;
            }
        }
        .nature-map {
            --nature-active-x: 50;
            --nature-active-y: 50;
        }
        .nature-bubble {
            filter: grayscale(1) saturate(.08) brightness(1.08);
            opacity: .5;
        }
        .nature-bubble[data-nature-bubble="aging"],
        .nature-bubble[data-nature-bubble="hta"] {
            z-index: 4;
        }
        .nature-bubble[data-nature-bubble="access_population"] {
            z-index: 8;
        }
        .nature-bubble[data-nature-bubble="device_innovation"],
        .nature-bubble[data-nature-bubble="data_rwe"],
        .nature-bubble[data-nature-bubble="ai_digital"],
        .nature-bubble[data-nature-bubble="drug_rdu"] {
            z-index: 14;
        }
        .nature-bubble.is-complete {
            filter: none;
            opacity: .78;
        }
        .nature-bubble.is-active {
            filter: none;
            opacity: 1;
            z-index: 35 !important;
            box-shadow: 0 24px 52px rgba(15, 23, 42, .28) !important;
            outline: 3px solid rgba(255, 255, 255, .8);
        }
        .nature-bubble.is-active::after {
            content: "";
            position: absolute;
            inset: -8px;
            border-radius: 999px;
            border: 2px solid rgba(15, 23, 42, .55);
            box-shadow: 0 0 0 7px rgba(255, 255, 255, .62);
            pointer-events: none;
        }
        .nature-bubble__callout {
            display: none;
        }
        .nature-bubble__callout-title,
        .nature-bubble__callout-count {
            display: block;
        }
        .nature-callout-layer,
        .nature-map-callout {
            display: none;
        }
        .nature-card {
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .nature-card.is-active {
            border-color: #14b8a6;
            box-shadow: 0 18px 38px rgba(15, 118, 110, .12);
            background: linear-gradient(180deg, #ffffff, #f8fffd);
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
            display: block;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 12px;
            min-height: 100%;
            transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }
        .policy-person-card:hover {
            transform: translateY(-1px);
            border-color: #cbd5e1;
            box-shadow: 0 10px 22px rgba(15, 23, 42, .07);
        }
        .policy-person-card:focus-visible {
            outline: 3px solid rgba(79, 70, 229, .24);
            outline-offset: 2px;
        }
        .policy-role-chip {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            border-radius: 999px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
        }
        .doctor-lane-board {
            overflow-x: auto;
            overflow-y: visible;
            padding-bottom: 6px;
        }
        .doctor-lane-grid {
            min-width: 760px;
            display: grid;
            gap: 0;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: visible;
            background: #ffffff;
        }
        .doctor-lane-row,
        .doctor-lane-head {
            display: grid;
            grid-template-columns: minmax(210px, 1.25fr) repeat(var(--doctor-year-count, 1), minmax(118px, 1fr));
            gap: 0;
            align-items: stretch;
        }
        .doctor-lane-row {
            position: relative;
            background: var(--doctor-row-bg, #ffffff);
        }
        .doctor-lane-row:hover {
            z-index: 3;
        }
        .doctor-lane-head {
            position: sticky;
            top: 0;
            z-index: 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .doctor-lane-theme,
        .doctor-lane-year,
        .doctor-lane-cell {
            background: #ffffff;
            padding: 12px;
            border-right: 1px solid #e2e8f0;
        }
        .doctor-lane-theme:last-child,
        .doctor-lane-year:last-child,
        .doctor-lane-cell:last-child {
            border-right: 0;
        }
        .doctor-lane-theme {
            background: #f8fafc;
        }
        .doctor-lane-row .doctor-lane-theme {
            background: var(--doctor-row-bg, #f8fafc);
            color: #0f172a;
        }
        .doctor-lane-row .doctor-lane-theme div {
            color: #0f172a;
        }
        .doctor-lane-year {
            text-align: center;
            font-size: 12px;
            font-weight: 800;
            color: #4338ca;
            background: #f8fafc;
        }
        .doctor-lane-cell {
            min-height: 78px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--doctor-row-cell-bg, #ffffff);
            border-color: var(--doctor-row-border, #e2e8f0);
            position: relative;
            overflow: visible;
            z-index: 1;
        }
        .doctor-lane-cell:hover {
            z-index: 80;
        }
        .doctor-lane-cell.is-empty {
            background: var(--doctor-row-cell-bg, #ffffff);
            color: #cbd5e1;
        }
        .doctor-lane-row + .doctor-lane-row {
            border-top: 1px solid var(--doctor-row-border, #e2e8f0);
        }
        .unit-contribution-matrix .doctor-lane-cell {
            min-height: 96px;
            max-height: 120px;
            align-items: flex-start;
            padding: 6px;
            overflow: visible;
            position: relative;
            z-index: 1;
        }
        .unit-contribution-matrix .doctor-lane-cell:hover {
            z-index: 80;
        }
        .unit-contribution-matrix .doctor-lane-row:hover {
            z-index: 70;
        }
        .unit-contribution-matrix .doctor-lane-row .doctor-lane-theme {
            min-height: 96px;
        }
        .unit-contribution-matrix .doctor-lane-head .doctor-lane-theme,
        .unit-contribution-matrix .doctor-lane-head .doctor-lane-year {
            min-height: 0;
            padding-top: 10px;
            padding-bottom: 10px;
        }
        .unit-cell-content {
            width: 100%;
            display: grid;
            gap: 4px;
        }
        .unit-cell-count {
            justify-self: center;
            border-radius: 999px;
            background: #ecfdf5;
            padding: 1px 7px;
            font-size: 10px;
            font-weight: 800;
            line-height: 1.2;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .doctor-avatar-stack {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            isolation: isolate;
            position: relative;
            z-index: 1;
            overflow: visible;
        }
        .doctor-avatar-stack:hover {
            z-index: 90;
        }
        .unit-avatar-stack {
            width: 100%;
            max-height: 142px;
            overflow-y: auto;
            overflow-x: visible;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(46px, 46px));
            justify-content: center;
            align-content: start;
            gap: 8px;
            padding: 3px 4px 6px;
            scrollbar-width: thin;
            scrollbar-color: #a7f3d0 transparent;
        }
        .unit-avatar-stack.doctor-avatar-stack {
            isolation: auto;
        }
        .unit-avatar-stack:hover {
            overflow: visible;
            z-index: 90;
        }
        .unit-avatar-stack::-webkit-scrollbar {
            width: 5px;
        }
        .unit-avatar-stack::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: #a7f3d0;
        }
        .doctor-avatar-stack a,
        .doctor-avatar-stack img {
            position: relative;
            display: inline-flex;
            flex: none;
            border-radius: 999px;
            transition: transform .18s ease, box-shadow .18s ease, z-index .18s ease;
            transform-origin: center center;
        }
        .doctor-avatar-stack img {
            width: 58px;
            height: 58px;
            border-radius: 999px;
            border: 3px solid #ffffff;
            object-fit: cover;
            box-shadow: 0 0 0 1px #cbd5e1, 0 8px 16px rgba(15, 23, 42, .10);
        }
        .unit-avatar-stack img {
            width: 46px;
            height: 46px;
            border-width: 3px;
            box-shadow: 0 0 0 1px #a7f3d0, 0 8px 16px rgba(15, 23, 42, .12);
        }
        @media (hover: hover) and (pointer: fine) {
            .doctor-avatar-stack a:hover,
            .doctor-avatar-stack a:focus-visible,
            .doctor-avatar-stack img:hover {
                z-index: 120;
                transform: scale(2.55);
            }
            .doctor-avatar-stack a:hover img,
            .doctor-avatar-stack a:focus-visible img,
            .doctor-avatar-stack img:hover {
                box-shadow: 0 0 0 2px #ffffff, 0 0 0 5px #6366f1, 0 28px 58px rgba(15, 23, 42, .36);
            }
            .unit-avatar-stack img:hover {
                z-index: 120;
                transform: scale(3.15);
                box-shadow: 0 0 0 2px #ffffff, 0 0 0 5px #10b981, 0 28px 58px rgba(15, 23, 42, .38);
            }
        }
        .doctor-mobile-timeline {
            display: none;
        }
        .unit-mobile-role-timeline {
            display: none;
        }
        .doctor-role-floating {
            display: none;
        }
        .doctor-role-tabs {
            border-color: #99f6e4;
            background: linear-gradient(180deg, #ccfbf1 0%, #ecfeff 100%);
            box-shadow: inset 0 0 0 1px rgba(20, 184, 166, .16);
        }
        .doctor-role-tab {
            position: relative;
            min-height: 38px;
            border: 1px solid transparent;
            background: transparent;
            color: #0f766e;
            transition: background-color .16s ease, border-color .16s ease, color .16s ease, box-shadow .16s ease;
        }
        .doctor-role-tab:hover {
            background: rgba(255, 255, 255, .58);
            color: #115e59;
        }
        .doctor-role-tab.is-active {
            border-color: #14b8a6;
            background: #ffffff !important;
            color: #0f172a !important;
            box-shadow: 0 10px 24px rgba(15, 118, 110, .18);
        }
        .doctor-role-tab.is-active span {
            background: #0f766e;
            color: #ffffff !important;
        }
        .doctor-role-tab span {
            display: inline-flex;
            min-width: 1.35rem;
            height: 1.35rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(15, 118, 110, .1);
            color: #0f766e;
            padding: 0 0.35rem;
        }
        .doctor-role-tab:focus-visible {
            outline: 3px solid rgba(20, 184, 166, .32);
            outline-offset: 2px;
        }
        .doctor-mobile-theme {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            overflow: visible;
        }
        .doctor-mobile-theme__head {
            border-bottom: 1px solid #e2e8f0;
            background: var(--doctor-row-bg, #f8fafc);
            padding: 12px;
        }
        .doctor-mobile-year {
            display: grid;
            gap: 8px;
            padding: 12px;
        }
        .doctor-mobile-year + .doctor-mobile-year {
            border-top: 1px solid #e2e8f0;
        }
        .doctor-mobile-year__label {
            font-size: 12px;
            font-weight: 800;
            color: #4338ca;
        }
        .doctor-mobile-empty {
            color: #cbd5e1;
            font-size: 13px;
        }
        .unit-mobile-role-timeline .doctor-mobile-theme {
            border-color: #a7f3d0;
        }
        .unit-mobile-role-timeline .doctor-mobile-theme__head {
            border-color: #d1fae5;
        }
        .unit-mobile-year-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .unit-mobile-avatar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(48px, 48px));
            gap: 8px;
            align-items: start;
        }
        .unit-mobile-avatar-grid img {
            width: 48px;
            height: 48px;
            border-radius: 999px;
            border: 3px solid #ffffff;
            object-fit: cover;
            box-shadow: 0 0 0 1px #a7f3d0, 0 8px 16px rgba(15, 23, 42, .12);
        }
        .clinical-led-chart-scroll {
            margin-top: 12px;
            height: 390px;
            overflow-x: auto;
            overflow-y: visible;
            padding-bottom: 4px;
        }
        .clinical-led-chart-inner {
            height: 100%;
            min-width: 920px;
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
        .fte-taxonomy {
            overflow: hidden;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
        }
        .fte-taxonomy__head,
        .fte-taxonomy__row {
            display: grid;
            grid-template-columns: minmax(150px, .34fr) minmax(0, 1fr);
            align-items: stretch;
        }
        .fte-taxonomy__head {
            background: #1f4e79;
            color: #ffffff;
            font-size: 13px;
            font-weight: 800;
        }
        .fte-taxonomy__head > div {
            padding: 8px 10px;
        }
        .fte-taxonomy__group {
            display: flex;
            align-items: flex-start;
            border-top: 1px solid #d6dee8;
            background: #f8fafc;
            padding: 8px 10px;
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
        }
        .fte-taxonomy__group--scientific {
            border-left: 4px solid #2563eb;
        }
        .fte-taxonomy__group--management {
            border-left: 4px solid #0f766e;
        }
        .fte-taxonomy__group--it {
            border-left: 4px solid #7c3aed;
        }
        .fte-taxonomy__group--logistics {
            border-left: 4px solid #d97706;
        }
        .fte-taxonomy__cell {
            border-top: 1px solid #d6dee8;
            border-left: 1px solid #e2e8f0;
            padding: 8px 10px;
            font-size: 13px;
            line-height: 1.45;
            color: #0f172a;
        }
        .fte-taxonomy__item {
            font-weight: 650;
        }
        .fte-taxonomy__count {
            display: inline-flex;
            margin-left: 8px;
            border-radius: 999px;
            background: #f1f5f9;
            padding: 1px 7px;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .fte-taxonomy__evidence {
            margin-top: 4px;
            color: #64748b;
            font-size: 12px;
            font-weight: 400;
        }
        .fte-taxonomy__empty {
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 16px;
            color: #64748b;
            font-size: 14px;
        }
        .fte-capability-board {
            display: grid;
            gap: 14px;
        }
        .fte-capability-group {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            overflow: hidden;
        }
        .fte-capability-group__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 14px 16px;
        }
        .fte-capability-group__title {
            font-size: 18px;
            font-weight: 800;
            line-height: 1.2;
            color: #0f172a;
        }
        .fte-capability-group__title-note {
            margin-left: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
        }
        .fte-capability-group__summary {
            margin-top: 4px;
            max-width: 720px;
            font-size: 13px;
            line-height: 1.55;
            color: #64748b;
        }
        .fte-capability-group__metric {
            display: inline-flex;
            flex-shrink: 0;
            align-items: center;
            border-radius: 999px;
            background: #ffffff;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 800;
            color: #334155;
            box-shadow: inset 0 0 0 1px #e2e8f0;
            white-space: nowrap;
        }
        .fte-capability-group__body {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: stretch;
            background: #fbfdff;
            padding: 14px;
        }
        .fte-capability-item {
            display: grid;
            flex: 1 1 250px;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 10px 12px;
            box-shadow: 0 6px 16px rgba(15, 23, 42, .035);
        }
        .fte-capability-item__name {
            min-width: 0;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.35;
            color: #0f172a;
        }
        .fte-capability-item__stats {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            justify-content: flex-end;
        }
        .fte-capability-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 58px;
            border-radius: 999px;
            background: #f8fafc;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 800;
            color: #1f2937;
        }
        .fte-capability-chip--primary {
            background: #0f172a;
            color: #ffffff;
        }
        .fte-capability-chip--secondary {
            box-shadow: inset 0 0 0 1px #e2e8f0;
            color: #475569;
        }
        .fte-capability-group--scientific .fte-capability-chip--primary {
            background: #2563eb;
        }
        .fte-capability-group--management .fte-capability-chip--primary {
            background: #0f766e;
        }
        .fte-capability-group--it .fte-capability-chip--primary {
            background: #7c3aed;
        }
        .fte-capability-group--logistics .fte-capability-chip--primary {
            background: #d97706;
        }
        .fte-capability-group--scientific .fte-capability-group__head {
            border-left: 5px solid #2563eb;
        }
        .fte-capability-group--management .fte-capability-group__head {
            border-left: 5px solid #0f766e;
        }
        .fte-capability-group--it .fte-capability-group__head {
            border-left: 5px solid #7c3aed;
        }
        .fte-capability-group--logistics .fte-capability-group__head {
            border-left: 5px solid #d97706;
        }
        .fte-radar-shell {
            display: grid;
            gap: 14px;
            grid-template-columns: minmax(0, .9fr) minmax(260px, .42fr);
        }
        .fte-radar-card,
        .fte-time-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 14px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
        }
        .fte-radar-card__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }
        .fte-radar-card__title {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
            color: #0f172a;
        }
        .fte-radar-card__note {
            margin-top: 3px;
            font-size: 12px;
            line-height: 1.45;
            color: #64748b;
        }
        .fte-radar-tabs {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 6px;
        }
        .fte-radar-tab {
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #ffffff;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 800;
            color: #475569;
            transition: background .16s ease, border-color .16s ease, color .16s ease, box-shadow .16s ease;
        }
        .fte-radar-tab.is-active,
        .fte-radar-tab:hover,
        .fte-radar-tab:focus-visible {
            border-color: #0f172a;
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .14);
        }
        .fte-radar-chart-wrap {
            margin-top: 18px;
            height: 430px;
        }
        .fte-radar-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px 16px;
            max-width: 430px;
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
        }
        .fte-radar-legend span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
        }
        .fte-radar-legend i {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 2px;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .08);
        }
        .fte-insight-card {
            background: #fbfdff;
        }
        .fte-chart-insight {
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
        }
        .fte-chart-insight__body {
            border-left: 4px solid #2563eb;
            border-radius: 10px;
            background: #f8fbff;
            padding: 10px 12px;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.625;
            color: #334155;
        }
        .fte-insight-item {
            border-left: 4px solid #14b8a6;
            border-radius: 10px;
            background: #ffffff;
            padding: 10px 12px;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.625;
            color: #334155;
            box-shadow: 0 6px 16px rgba(15, 23, 42, .035);
        }
        .fte-capacity-tables {
            display: grid;
            gap: 18px;
        }
        @media (min-width: 1280px) {
            .fte-capacity-tables {
                grid-template-columns: minmax(0, 1.15fr) minmax(380px, .85fr);
                align-items: start;
            }
            .fte-capacity-sticky-col {
                position: relative;
                align-self: start;
            }
            .fte-capacity-card--sticky {
                transform: translate3d(0, var(--fte-capacity-stick-y, 0px), 0);
                will-change: transform;
            }
        }
        .fte-capacity-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            padding: 16px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
        }
        .fte-capacity-card__head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }
        .fte-capacity-card__title {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
            color: #0f172a;
        }
        .fte-capacity-card__note {
            margin-top: 2px;
            font-size: 12px;
            line-height: 1.5;
            color: #64748b;
        }
        .fte-capacity-card__kicker {
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #64748b;
        }
        .fte-capacity-metrics {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .fte-capacity-metric {
            min-width: 92px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            padding: 7px 10px;
        }
        .fte-capacity-metric__label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
        }
        .fte-capacity-metric__value {
            margin-top: 2px;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }
        .fte-capacity-table-wrap {
            overflow-x: auto;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
        }
        .fte-capacity-position-cards {
            display: none;
        }
        .fte-capacity-position-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 12px;
        }
        .fte-capacity-position-card__head {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 10px;
            align-items: start;
        }
        .fte-capacity-position-card__metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin-top: 10px;
        }
        .fte-capacity-position-card__metric {
            border-radius: 10px;
            background: #f8fafc;
            padding: 8px;
            text-align: center;
        }
        .fte-capacity-position-card__metric-label {
            font-size: 10px;
            font-weight: 800;
            line-height: 1.2;
            color: #64748b;
        }
        .fte-capacity-position-card__metric-value {
            margin-top: 3px;
            font-size: 14px;
            font-weight: 900;
            line-height: 1;
            color: #0f172a;
        }
        .fte-capacity-table {
            width: 100%;
            min-width: 560px;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
            color: #334155;
        }
        .fte-capacity-table th,
        .fte-capacity-table td {
            padding: 10px 12px;
            vertical-align: middle;
        }
        .fte-capacity-table th {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            text-align: left;
            text-transform: uppercase;
        }
        .fte-capacity-table th.fte-capacity-table__num {
            text-align: center;
        }
        .fte-capacity-table tbody td {
            border-bottom: 1px solid #e5e7eb;
        }
        .fte-capacity-table tbody tr:nth-child(odd) td {
            background: #ffffff;
        }
        .fte-capacity-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        .fte-capacity-table__num {
            text-align: center;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }
        .fte-capacity-table tbody tr:hover td {
            background: #eef6ff;
        }
        .fte-capacity-table tfoot td {
            border-top: 1px solid #cbd5e1;
            background: #f1f5f9;
            font-weight: 700;
            color: #0f172a;
        }
        .fte-capacity-table-note {
            margin-top: 9px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.55;
        }
        .fte-capacity-rank {
            display: inline-flex;
            width: 28px;
            height: 28px;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: #eef2ff;
            color: #334155;
            font-size: 12px;
            font-weight: 800;
        }
        .fte-capacity-position {
            display: grid;
            gap: 3px;
        }
        .fte-capacity-position__name {
            color: #0f172a;
            font-weight: 800;
        }
        .fte-capacity-position__skill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
        }
        .fte-capacity-position__dot {
            width: .78em;
            height: .78em;
            flex: 0 0 auto;
            border-radius: 999px;
            background: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        }
        .fte-capacity-table tbody tr:nth-child(2n) .fte-capacity-position__dot {
            background: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, .10);
        }
        .fte-capacity-table tbody tr:nth-child(3n) .fte-capacity-position__dot {
            background: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, .10);
        }
        .fte-capacity-count {
            color: #0f172a;
            font-size: 15px;
            font-weight: 800;
        }
        .fte-capacity-skill-name {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #0f172a;
            font-weight: 800;
        }
        .fte-capacity-skill-dot {
            width: 10px;
            height: 10px;
            flex: 0 0 auto;
            border-radius: 999px;
            background: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }
        .fte-capacity-table tbody tr:nth-child(2n) .fte-capacity-skill-dot {
            background: #14b8a6;
            box-shadow: 0 0 0 4px rgba(20, 184, 166, .10);
        }
        .fte-capacity-table tbody tr:nth-child(3n) .fte-capacity-skill-dot {
            background: #f97316;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, .10);
        }
        .fte-skill-badge {
            display: inline-flex;
            max-width: 100%;
            align-items: center;
            border-radius: 999px;
            background: #eef2ff;
            padding: 4px 9px;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.3;
        }
        .fte-borrowed-badge {
            display: inline-flex;
            min-width: 28px;
            justify-content: center;
            border-radius: 999px;
            background: #fff7ed;
            padding: 3px 8px;
            color: #c2410c;
            font-size: 12px;
            font-weight: 700;
        }
        .fte-borrowed-empty {
            color: #cbd5e1;
        }
        .fte-radar-breakdown {
            display: grid;
            gap: 8px;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
        }
        .fte-radar-breakdown__row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            padding: 9px 10px;
        }
        .fte-radar-breakdown__label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 12px;
            font-weight: 900;
            color: #0f172a;
        }
        .fte-radar-breakdown__sub {
            margin-top: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
        }
        .fte-radar-breakdown__metrics {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 5px;
            max-width: 150px;
        }
        .fte-radar-breakdown__metrics span {
            border-radius: 999px;
            background: #f1f5f9;
            padding: 3px 7px;
            font-size: 10px;
            font-weight: 900;
            color: #475569;
            white-space: nowrap;
        }
        .fte-time-card__title {
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
        }
        .fte-time-year {
            margin-top: 10px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .fte-time-year:first-of-type {
            border-top: 0;
            padding-top: 0;
        }
        .fte-time-year__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-size: 12px;
            font-weight: 900;
            color: #0f172a;
        }
        .fte-time-year__total {
            border-radius: 999px;
            background: #f1f5f9;
            padding: 3px 8px;
            color: #475569;
            white-space: nowrap;
        }
        .fte-time-strip {
            display: grid;
            gap: 5px;
            margin-top: 8px;
        }
        .fte-time-strip__row {
            display: grid;
            grid-template-columns: 86px minmax(0, 1fr) 50px;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 750;
            color: #475569;
        }
        .fte-time-strip__bar {
            height: 8px;
            overflow: hidden;
            border-radius: 999px;
            background: #e2e8f0;
        }
        .fte-time-strip__fill {
            display: block;
            height: 100%;
            width: var(--w, 0%);
            border-radius: inherit;
            background: var(--bar, #2563eb);
        }
        .fte-time-strip__value {
            text-align: right;
            color: #0f172a;
        }
        .fte-tone-scientific { --bar: #2563eb; }
        .fte-tone-management { --bar: #0f766e; }
        .fte-tone-it { --bar: #7c3aed; }
        .fte-tone-logistics { --bar: #d97706; }
        .network-feature-grid {
            display: grid;
            gap: 18px;
        }
        .network-year-block + .network-year-block {
            margin-top: 58px;
        }
        .network-year-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border-top: 1px solid #cbd5e1;
            padding: 22px 0 4px;
        }
        .network-kpi-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        .network-kpi-card {
            position: relative;
            overflow: hidden;
            border: 1px solid #d6e0ec;
            border-radius: 14px;
            background: #ffffff;
            padding: 16px 18px;
            min-height: 128px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .035);
        }
        .network-kpi-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: #10b981;
            opacity: .85;
        }
        .network-kpi-card__label {
            max-width: 420px;
            font-size: 12px;
            font-weight: 850;
            line-height: 1.35;
            color: #64748b;
            letter-spacing: .02em;
        }
        .network-kpi-card__value {
            margin-top: 10px;
            font-size: 34px;
            font-weight: 950;
            line-height: .95;
            color: #0f172a;
        }
        .network-kpi-card__sub {
            margin-top: 8px;
            font-size: 13px;
            font-weight: 750;
            line-height: 1.35;
            color: #64748b;
        }
        @media (min-width: 1280px) {
            .network-kpi-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .network-year-head__year {
            position: relative;
            padding-left: 18px;
            font-size: clamp(28px, 3vw, 40px);
            font-weight: 950;
            color: #0f172a;
            line-height: .95;
            letter-spacing: 0;
        }
        .network-year-head__year::before {
            content: "";
            position: absolute;
            left: 0;
            top: 3px;
            bottom: 1px;
            width: 6px;
            border-radius: 999px;
            background: linear-gradient(180deg, #047857, #34d399);
            box-shadow: 0 10px 20px rgba(4, 120, 87, .24);
        }
        .network-year-head__count {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: #0f172a;
            padding: 9px 13px;
            font-size: 13px;
            font-weight: 850;
            color: #ffffff;
            white-space: nowrap;
            box-shadow: 0 12px 24px rgba(15, 23, 42, .16);
        }
        .network-feature-card {
            position: relative;
            min-height: 620px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #0f172a;
            box-shadow: 0 16px 38px rgba(15, 23, 42, .10);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .network-feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 24px 52px rgba(15, 23, 42, .18);
        }
        .network-feature-card__media,
        .network-feature-card__fallback,
        .network-feature-card__shade {
            position: absolute;
            inset: 0;
        }
        .network-feature-card__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .network-feature-card__fallback {
            display: grid;
            place-items: center;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, .92), rgba(37, 99, 235, .72)),
                radial-gradient(circle at 20% 15%, rgba(125, 211, 252, .45), transparent 34%),
                radial-gradient(circle at 80% 20%, rgba(167, 139, 250, .36), transparent 32%);
            color: #ffffff;
            text-align: center;
        }
        .network-feature-card--no-link .network-feature-card__fallback {
            background:
                linear-gradient(135deg, rgba(15, 23, 42, .94), rgba(15, 118, 110, .76)),
                radial-gradient(circle at 18% 18%, rgba(110, 231, 183, .38), transparent 34%),
                radial-gradient(circle at 82% 12%, rgba(251, 191, 36, .24), transparent 32%);
        }
        .network-feature-card__fallback-kind {
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .76);
        }
        .network-feature-card__fallback-code {
            margin-top: 10px;
            font-size: clamp(30px, 7vw, 42px);
            font-weight: 950;
            line-height: 1;
        }
        .network-feature-card__shade {
            background:
                linear-gradient(180deg, rgba(15, 23, 42, .02) 0%, rgba(15, 23, 42, .10) 48%, rgba(15, 23, 42, .82) 100%),
                linear-gradient(90deg, rgba(15, 23, 42, .36) 0%, rgba(15, 23, 42, .08) 52%, rgba(15, 23, 42, .22) 100%);
        }
        .network-feature-card__body {
            position: relative;
            z-index: 1;
            display: flex;
            min-height: 620px;
            flex-direction: column;
            justify-content: flex-end;
            padding: 22px;
            color: #ffffff;
        }
        .network-feature-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            margin-bottom: 10px;
        }
        .network-feature-card__pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, .92);
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 900;
            color: #0f172a;
        }
        .network-feature-card__host {
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 11px;
            font-weight: 800;
            color: rgba(255, 255, 255, .78);
        }
        .network-feature-card__title {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            font-size: 23px;
            font-weight: 850;
            line-height: 1.22;
            text-shadow: 0 1px 2px rgba(15, 23, 42, .32);
        }
        .network-feature-card__desc {
            display: -webkit-box;
            overflow: hidden;
            margin-top: 8px;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            font-size: 13px;
            line-height: 1.45;
            color: rgba(255, 255, 255, .82);
        }
        @media (min-width: 1280px) {
            .fte-capability-board {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                align-items: start;
            }
            .network-feature-grid {
                grid-template-columns: repeat(12, minmax(0, 1fr));
                grid-auto-rows: 430px;
            }
            .network-feature-card {
                grid-column: span 4;
                min-height: auto;
            }
            .network-feature-card__body {
                min-height: 100%;
            }
            .network-feature-card:nth-child(1) {
                grid-column: span 7;
                grid-row: span 2;
            }
            .network-feature-card:nth-child(2),
            .network-feature-card:nth-child(3) {
                grid-column: span 5;
            }
            .network-feature-card:first-child .network-feature-card__title {
                max-width: 760px;
                font-size: 34px;
            }
            .network-feature-card:nth-child(n+4) .network-feature-card__title {
                font-size: 21px;
            }
        }
        @media (max-width: 1023px) {
            .scene {
                min-height: auto;
            }
            .fte-radar-shell {
                grid-template-columns: 1fr;
            }
            .fte-radar-card__head {
                flex-direction: column;
                gap: 10px;
            }
            .fte-radar-legend {
                justify-content: flex-start;
                max-width: none;
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
        @media (min-width: 641px) and (max-width: 1279px) and (orientation: portrait) {
            .viz-page > div {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
            .viz-page {
                overflow-x: clip;
                overflow-wrap: anywhere;
            }
            .hero-panel,
            .panel {
                padding: 20px !important;
            }
            .policy-brief,
            .policy-axis-card,
            .policy-trend-panel,
            .contribution-card {
                padding: 14px;
            }
            .chart-sticky {
                position: relative;
                top: auto;
            }
            .scene {
                min-height: auto;
            }
            .viz-page [class*="h-[340px]"],
            .viz-page [class*="h-[360px]"],
            .viz-page [class*="h-[380px]"],
            .viz-page [class*="h-[390px]"] {
                height: 340px !important;
            }
            .type-chart-wrap {
                height: 420px;
            }
            .scroll-card,
            .paper-record-scroll,
            .policy-list-compact {
                max-height: 420px;
            }
            .policy-mini-list {
                max-height: 280px;
            }
            .doctor-lane-board {
                display: none;
            }
            .doctor-mobile-timeline,
            .unit-mobile-role-timeline {
                display: grid;
                gap: 12px;
            }
            .doctor-mobile-theme__head,
            .doctor-mobile-year {
                padding: 12px;
            }
            .doctor-mobile-year .doctor-avatar-stack {
                justify-content: flex-start;
            }
            .doctor-role-tabs {
                display: grid !important;
                width: 100%;
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .doctor-role-tab {
                min-width: 0;
                min-height: 46px;
                padding: 8px 6px !important;
                text-align: center;
                line-height: 1.15;
                white-space: normal;
            }
            .doctor-role-tab span {
                display: block;
                margin-left: 0;
                margin-top: 4px;
                font-size: 12px;
            }
            .clinical-led-chart-scroll {
                height: 340px;
                margin-left: 0;
                margin-right: 0;
                padding-bottom: 0;
                overflow-x: clip;
            }
            .clinical-led-chart-inner {
                min-width: 0;
                width: 100%;
            }
            .fte-capacity-table-wrap {
                overflow-x: visible;
                border: 0;
                border-radius: 0;
                background: transparent;
            }
            .fte-capacity-table,
            .fte-capacity-table thead,
            .fte-capacity-table tbody,
            .fte-capacity-table tfoot,
            .fte-capacity-table tr,
            .fte-capacity-table th,
            .fte-capacity-table td {
                display: block;
                min-width: 0 !important;
                width: 100%;
            }
            .fte-capacity-table {
                font-size: 13px;
            }
            .fte-capacity-table thead {
                display: none;
            }
            .fte-capacity-table tbody {
                display: grid;
                gap: 8px;
            }
            .fte-capacity-table tbody tr {
                display: grid;
                grid-template-columns: 32px minmax(0, 1fr);
                gap: 6px 10px;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                background: #ffffff;
                padding: 10px;
                align-items: start;
            }
            .fte-capacity-table tbody tr:nth-child(even) {
                background: #f8fafc;
            }
            .fte-capacity-table tbody td,
            .fte-capacity-table tbody tr:nth-child(odd) td,
            .fte-capacity-table tbody tr:nth-child(even) td,
            .fte-capacity-table tbody tr:hover td {
                border-bottom: 0;
                background: transparent;
                padding: 0;
            }
            .fte-capacity-table tbody td:first-child {
                grid-row: 1 / span 8;
                width: 32px;
            }
            .fte-capacity-table tbody td:nth-child(2) {
                grid-column: 2;
            }
            .fte-capacity-table tbody td.fte-capacity-table__num {
                grid-column: 2;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                border-top: 1px dashed #e2e8f0;
                padding-top: 6px;
                text-align: right;
            }
            .fte-capacity-table tbody td.fte-capacity-table__num::before,
            .fte-capacity-table tfoot td.fte-capacity-table__num::before {
                content: attr(data-label);
                max-width: 60%;
                color: #64748b;
                font-size: 12px;
                font-weight: 700;
                text-align: left;
            }
            .fte-capacity-table tfoot {
                display: grid;
                gap: 8px;
                margin-top: 8px;
            }
            .fte-capacity-table tfoot tr {
                display: grid;
                gap: 6px;
                border: 1px solid #cbd5e1;
                border-radius: 12px;
                background: #f8fafc;
                padding: 10px;
            }
            .fte-capacity-table tfoot td {
                border-bottom: 0;
                background: transparent;
                padding: 0;
            }
            .fte-capacity-table tfoot td:first-child {
                font-size: 12px;
                color: #475569;
            }
            .fte-capacity-table tfoot td.fte-capacity-table__num {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                border-top: 1px dashed #cbd5e1;
                padding-top: 6px;
            }
            .type-table-head,
            .type-table-row {
                grid-template-columns: minmax(220px, 2fr) 96px 88px 104px;
                gap: 10px;
            }
            .section-tag,
            .policy-pill {
                max-width: 100%;
                white-space: normal;
                line-height: 1.3;
            }
            #frameworkTrendModal {
                padding: 12px !important;
            }
            #frameworkTrendModalCard {
                max-height: calc(100vh - 24px) !important;
            }
            #frameworkTrendModalBody {
                max-height: calc(100vh - 128px) !important;
            }
        }
        @media (min-width: 768px) and (max-width: 1023px) and (orientation: portrait) {
            .viz-page > div {
                padding-left: 18px !important;
                padding-right: 18px !important;
            }
            .type-table-head,
            .type-table-row {
                grid-template-columns: 1fr;
                gap: 6px;
            }
            .policy-people-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
            .policy-mini-list {
                max-height: 320px;
            }
            .nature-bubble,
            .nature-bubble.is-complete {
                filter: none;
                opacity: 1;
            }
        }
        @media (min-width: 1024px) and (max-width: 1279px) and (orientation: portrait) {
            .viz-page [class*="lg:grid-cols"] {
                grid-template-columns: minmax(0, 1fr) !important;
            }
            .viz-page [class*="lg:flex-row"] {
                flex-direction: column !important;
            }
            .viz-page [class*="lg:items-start"],
            .viz-page [class*="lg:items-end"],
            .viz-page [class*="lg:items-center"] {
                align-items: stretch !important;
            }
            .viz-page [class*="lg:justify-between"] {
                justify-content: flex-start !important;
            }
            .viz-page [class*="lg:col-span"] {
                grid-column: auto !important;
            }
        }
        @media (min-width: 768px) and (max-width: 1279px) and (orientation: portrait) {
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table-wrap {
                display: none;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-position-cards {
                display: grid;
                gap: 10px;
            }
            .fte-capacity-position-card__metrics {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-metrics {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100%;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-metric {
                min-width: 0;
            }
            .network-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }
            .network-kpi-card {
                min-height: 0;
                padding: 12px 14px;
                border-radius: 12px;
            }
            .network-kpi-card__label {
                max-width: none;
                font-size: 11px;
                line-height: 1.35;
            }
            .network-kpi-card__value {
                margin-top: 8px;
                font-size: 24px;
                line-height: 1;
            }
            .network-kpi-card__sub,
            .network-kpi-card .mt-2 {
                font-size: 11px;
                line-height: 1.35;
            }
            .network-year-block + .network-year-block {
                margin-top: 30px;
            }
            .network-year-head {
                padding: 16px 0 2px;
            }
            .network-year-head__year {
                font-size: 28px;
            }
            .network-year-head__count {
                padding: 7px 10px;
                font-size: 11px;
            }
            .network-feature-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
            }
            .network-feature-card {
                min-height: 430px;
                border-radius: 14px;
            }
            .network-feature-card__body {
                min-height: 430px;
                padding: 16px;
            }
            .network-feature-card__meta {
                gap: 6px;
                margin-bottom: 8px;
            }
            .network-feature-card__pill {
                padding: 4px 7px;
                font-size: 10px;
            }
            .network-feature-card__title {
                font-size: 20px;
                line-height: 1.22;
                -webkit-line-clamp: 3;
            }
            .network-feature-card__desc {
                font-size: 12px;
                line-height: 1.35;
                -webkit-line-clamp: 2;
            }
        }
        @media (min-width: 1024px) and (max-width: 1279px) and (orientation: landscape) {
            .viz-page > div {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }
            .viz-page [class*="h-[340px]"],
            .viz-page [class*="h-[360px]"],
            .viz-page [class*="h-[380px]"],
            .viz-page [class*="h-[390px]"] {
                height: 360px !important;
            }
            .type-chart-wrap {
                height: 460px;
            }
            .policy-mini-list {
                max-height: 300px;
            }
            .doctor-mobile-timeline,
            .unit-mobile-role-timeline,
            .fte-capacity-table tbody {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .doctor-role-tabs {
                max-width: 680px;
            }
            .doctor-lane-board {
                display: block;
            }
            .doctor-mobile-timeline,
            .unit-mobile-role-timeline {
                display: none;
            }
            .doctor-lane-grid {
                min-width: 900px;
            }
            .doctor-lane-row,
            .doctor-lane-head {
                grid-template-columns: minmax(170px, 1.1fr) repeat(var(--doctor-year-count, 1), minmax(92px, 1fr));
            }
            .doctor-lane-theme,
            .doctor-lane-year,
            .doctor-lane-cell {
                padding: 8px;
            }
            .doctor-lane-theme .text-sm {
                font-size: 12px;
                line-height: 1.35;
            }
            .doctor-lane-cell {
                min-height: 62px;
            }
            .unit-contribution-matrix .doctor-lane-cell,
            .unit-contribution-matrix .doctor-lane-row .doctor-lane-theme {
                min-height: 78px;
                max-height: 96px;
            }
            .doctor-avatar-stack {
                gap: 6px;
            }
            .doctor-avatar-stack img {
                width: 42px;
                height: 42px;
                border-width: 2px;
            }
            .unit-avatar-stack {
                grid-template-columns: repeat(auto-fit, minmax(34px, 34px));
                gap: 5px;
                max-height: 88px;
            }
            .unit-avatar-stack img {
                width: 34px;
                height: 34px;
                border-width: 2px;
            }
            .clinical-led-chart-scroll {
                height: 330px;
                overflow-x: auto;
                padding-bottom: 6px;
            }
            .clinical-led-chart-inner {
                min-width: 760px;
            }
            .fte-capacity-table-wrap {
                overflow-x: auto;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                background: #ffffff;
            }
            .fte-capacity-table {
                display: table;
                width: 100%;
                min-width: 560px !important;
                border-collapse: separate;
                border-spacing: 0;
                font-size: 12px;
            }
            .fte-capacity-table thead {
                display: table-header-group;
            }
            .fte-capacity-table tbody {
                display: table-row-group;
            }
            .fte-capacity-table tfoot {
                display: table-footer-group;
            }
            .fte-capacity-table tr {
                display: table-row;
            }
            .fte-capacity-table th,
            .fte-capacity-table td {
                display: table-cell;
                width: auto;
                padding: 8px 9px;
            }
            .fte-capacity-table tbody td.fte-capacity-table__num,
            .fte-capacity-table tfoot td.fte-capacity-table__num {
                display: table-cell;
                border-top: 0;
                padding-top: 8px;
            }
            .fte-capacity-table tbody td.fte-capacity-table__num::before,
            .fte-capacity-table tfoot td.fte-capacity-table__num::before {
                content: none;
            }
        }
        @media (min-width: 641px) and (max-width: 1279px) {
            .fte-capacity-table-wrap {
                overflow-x: auto;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                background: #ffffff;
                -webkit-overflow-scrolling: touch;
            }
            .fte-capacity-table {
                display: table;
                width: 100%;
                min-width: 680px !important;
                border-collapse: separate;
                border-spacing: 0;
                table-layout: auto;
                font-size: 12px;
            }
            .fte-capacity-table thead {
                display: table-header-group;
            }
            .fte-capacity-table tbody {
                display: table-row-group;
            }
            .fte-capacity-table tfoot {
                display: table-footer-group;
                margin-top: 0;
            }
            .fte-capacity-table tr,
            .fte-capacity-table tbody tr,
            .fte-capacity-table tfoot tr {
                display: table-row;
                grid-template-columns: none;
                gap: 0;
                border: 0;
                border-radius: 0;
                background: transparent;
                padding: 0;
            }
            .fte-capacity-table th,
            .fte-capacity-table td {
                display: table-cell;
                width: auto;
                min-width: 0 !important;
                padding: 8px 9px;
                vertical-align: middle;
            }
            .fte-capacity-table tbody td,
            .fte-capacity-table tbody tr:nth-child(odd) td,
            .fte-capacity-table tbody tr:nth-child(even) td,
            .fte-capacity-table tbody tr:hover td,
            .fte-capacity-table tfoot td {
                display: table-cell;
                width: auto;
                min-width: 0 !important;
                border-bottom: 1px solid #e2e8f0;
                background: transparent;
                padding: 8px 9px;
                vertical-align: middle;
            }
            .fte-capacity-table tbody td:first-child {
                width: 52px;
                grid-row: auto;
                grid-column: auto;
            }
            .fte-capacity-table tbody td:nth-child(2) {
                min-width: 260px !important;
                grid-row: auto;
                grid-column: auto;
            }
            .fte-capacity-table tbody td.fte-capacity-table__num,
            .fte-capacity-table tfoot td.fte-capacity-table__num {
                display: table-cell;
                width: auto;
                grid-row: auto;
                grid-column: auto;
                align-items: initial;
                justify-content: initial;
                gap: 0;
                border-top: 0;
                padding-top: 8px;
                text-align: right;
            }
            .fte-capacity-table tbody td.fte-capacity-table__num::before,
            .fte-capacity-table tfoot td.fte-capacity-table__num::before {
                content: none;
            }
            .fte-capacity-position__name,
            .fte-capacity-skill-name {
                overflow-wrap: normal;
                word-break: normal;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table-wrap {
                overflow-x: visible;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table {
                min-width: 0 !important;
                table-layout: fixed;
                font-size: 11px;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table th,
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table td,
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table tbody td,
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table tbody tr:nth-child(odd) td,
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table tbody tr:nth-child(even) td,
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table tbody tr:hover td,
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table tfoot td {
                padding: 8px 6px;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table th:first-child,
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table td:first-child {
                width: 42px !important;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table th:nth-child(2),
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table td:nth-child(2) {
                width: auto;
                min-width: 0 !important;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table th:nth-child(3),
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table td:nth-child(3) {
                width: 13%;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table th:nth-child(4),
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table td:nth-child(4),
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table th:nth-child(5),
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table td:nth-child(5),
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table th:nth-child(6),
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table td:nth-child(6) {
                width: 12%;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table th {
                font-size: 9px;
                letter-spacing: 0;
                line-height: 1.2;
                white-space: normal;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-position__name {
                font-size: 12px;
                line-height: 1.25;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-position__skill {
                font-size: 11px;
                line-height: 1.25;
                white-space: normal;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-rank {
                width: 28px;
                height: 28px;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-count,
            .fte-capacity-card[data-fte-capacity-left] .fte-borrowed-badge,
            .fte-capacity-card[data-fte-capacity-left] .fte-borrowed-empty {
                font-size: 13px;
            }
        }
        @media (min-width: 1024px) and (max-width: 1199px) and (orientation: landscape) {
            .nature-section {
                padding: 16px !important;
            }
            .nature-shell {
                padding: 18px !important;
                border-radius: 20px !important;
            }
            .nature-shell h2 {
                font-size: 24px !important;
                line-height: 1.25 !important;
            }
            .nature-shell .mt-5.grid.gap-3 {
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 10px;
            }
            .nature-shell .mt-5.grid.gap-3 > div {
                padding: 12px;
                border-radius: 14px;
            }
            .nature-shell .mt-5.grid.gap-3 .text-3xl {
                font-size: 26px !important;
            }
            .nature-layout {
                grid-template-columns: minmax(0, 0.56fr) minmax(0, 0.44fr) !important;
                gap: 18px !important;
                align-items: start !important;
            }
            .nature-rail,
            .nature-sticky {
                min-width: 0;
            }
            .nature-sticky {
                position: sticky;
                top: 86px;
                align-self: flex-start;
                padding: 14px !important;
                border-radius: 18px !important;
            }
            .nature-map {
                width: min(100%, 380px);
                min-height: 0 !important;
                height: clamp(320px, 36vw, 380px) !important;
                max-width: 380px !important;
                margin-left: auto;
                margin-right: auto;
                border-radius: 24px !important;
                padding: 10px !important;
            }
            .nature-map .absolute.inset-6 {
                inset: 16px !important;
            }
            .nature-bubble {
                min-width: 36px;
                min-height: 36px;
            }
            .nature-bubble > div {
                padding-left: 5px !important;
                padding-right: 5px !important;
            }
            .nature-bubble [class*="text-[15px]"],
            .nature-bubble [class*="text-[12px]"],
            .nature-bubble [class*="text-[10px]"] {
                font-size: clamp(8px, 1.05vw, 12px) !important;
                line-height: 1.08 !important;
            }
            .nature-bubble .mt-1 {
                margin-top: 1px !important;
                font-size: clamp(7px, .9vw, 10px) !important;
                line-height: 1 !important;
            }
            .nature-card {
                padding: 14px !important;
                border-radius: 16px !important;
            }
            .nature-card .text-lg {
                font-size: 15px !important;
                line-height: 1.35 !important;
            }
            .nature-card .mt-5.grid {
                grid-template-columns: minmax(0, 1fr) !important;
                gap: 12px;
            }
            .nature-card .lg\:col-span-2 {
                grid-column: auto !important;
            }
            .clinical-led-layout {
                grid-template-columns: minmax(0, .58fr) minmax(0, .42fr) !important;
                gap: 14px !important;
                align-items: start;
            }
            .clinical-led-layout > div {
                min-width: 0;
            }
            .clinical-led-chart-scroll {
                height: 300px !important;
                overflow-x: visible !important;
                overflow-y: visible;
                padding-bottom: 0;
            }
            .clinical-led-chart-inner {
                width: 100%;
                min-width: 0 !important;
                height: 100%;
            }
            .clinical-led-layout .policy-pill {
                font-size: 10px;
                line-height: 1.25;
                padding: 5px 8px;
            }
            .clinical-led-layout .text-xl {
                font-size: 17px !important;
                line-height: 1.3 !important;
            }
            .fte-capacity-tables {
                grid-template-columns: minmax(0, .58fr) minmax(0, .42fr);
                align-items: start;
                gap: 14px;
            }
            .fte-capacity-card {
                padding: 12px;
                border-radius: 14px;
            }
            .fte-capacity-card__head {
                gap: 8px;
                margin-bottom: 10px;
            }
            .fte-capacity-card__title {
                font-size: 13px;
                line-height: 1.35;
            }
            .fte-capacity-card__note {
                font-size: 11px;
                line-height: 1.45;
            }
            .fte-capacity-metrics {
                gap: 6px;
            }
            .fte-capacity-metric {
                min-width: 74px;
                padding: 6px 8px;
            }
            .fte-capacity-metric__value {
                font-size: 16px;
            }
            .fte-capacity-sticky-col {
                position: relative;
                align-self: start;
                min-width: 0;
            }
            .fte-capacity-card--sticky {
                transform: translate3d(0, var(--fte-capacity-stick-y, 0px), 0);
                will-change: transform;
            }
            .fte-capacity-table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .fte-capacity-table {
                font-size: 11px;
                min-width: 520px !important;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table {
                min-width: 600px !important;
            }
            .fte-capacity-sticky-col .fte-capacity-table {
                min-width: 420px !important;
            }
            .fte-capacity-table th,
            .fte-capacity-table td,
            .fte-capacity-table tbody td,
            .fte-capacity-table tbody tr:nth-child(odd) td,
            .fte-capacity-table tbody tr:nth-child(even) td,
            .fte-capacity-table tbody tr:hover td,
            .fte-capacity-table tfoot td {
                padding: 7px 7px;
            }
            .fte-capacity-table th {
                font-size: 9px;
                letter-spacing: 0;
            }
            .fte-capacity-rank {
                width: 24px;
                height: 24px;
                border-radius: 8px;
                font-size: 10px;
            }
            .fte-capacity-count {
                font-size: 13px;
            }
            .fte-capacity-position__name,
            .fte-capacity-skill-name {
                font-size: 11px;
                line-height: 1.3;
            }
            .fte-capacity-position__skill {
                font-size: 10px;
                line-height: 1.25;
            }
            .fte-capacity-table-note {
                font-size: 10px;
                line-height: 1.45;
            }
            .network-kpi-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 8px;
            }
            .network-kpi-card {
                min-height: 104px;
                padding: 12px 12px;
                border-radius: 12px;
            }
            .network-kpi-card__label {
                max-width: none;
                font-size: 10px;
                line-height: 1.25;
            }
            .network-kpi-card__value {
                margin-top: 8px;
                font-size: 24px;
                line-height: 1;
            }
            .network-kpi-card__sub,
            .network-kpi-card .mt-2 {
                font-size: 10px;
                line-height: 1.3;
            }
            .network-year-block + .network-year-block {
                margin-top: 34px;
            }
            .network-year-head {
                gap: 10px;
                padding: 16px 0 2px;
            }
            .network-year-head__year {
                font-size: 28px;
            }
            .network-year-head__count {
                padding: 7px 10px;
                font-size: 11px;
            }
            .network-feature-grid {
                grid-template-columns: repeat(12, minmax(0, 1fr));
                grid-auto-rows: 260px;
                gap: 10px;
            }
            .network-feature-card {
                grid-column: span 4;
                min-height: auto;
                border-radius: 14px;
            }
            .network-feature-card__body {
                min-height: 100%;
                padding: 14px;
            }
            .network-feature-card:nth-child(1) {
                grid-column: span 7;
                grid-row: span 2;
            }
            .network-feature-card:nth-child(2),
            .network-feature-card:nth-child(3) {
                grid-column: span 5;
            }
            .network-feature-card__meta {
                gap: 5px;
                margin-bottom: 7px;
            }
            .network-feature-card__pill {
                padding: 4px 7px;
                font-size: 9px;
            }
            .network-feature-card__title {
                font-size: 17px;
                line-height: 1.2;
                -webkit-line-clamp: 3;
            }
            .network-feature-card:first-child .network-feature-card__title {
                max-width: 620px;
                font-size: 25px;
            }
            .network-feature-card:nth-child(n+4) .network-feature-card__title {
                font-size: 15px;
            }
            .network-feature-card__desc {
                font-size: 11px;
                line-height: 1.35;
            }
        }
        @media (min-width: 768px) and (max-width: 1366px) {
            .fte-capacity-table-wrap {
                overflow-x: visible !important;
                max-width: 100%;
            }
            .fte-capacity-table,
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table,
            .fte-capacity-sticky-col .fte-capacity-table {
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
                table-layout: fixed !important;
                font-size: 10px;
            }
            .fte-capacity-table th,
            .fte-capacity-table td,
            .fte-capacity-table tbody td,
            .fte-capacity-table tbody tr:nth-child(odd) td,
            .fte-capacity-table tbody tr:nth-child(even) td,
            .fte-capacity-table tbody tr:hover td,
            .fte-capacity-table tfoot td {
                padding: 6px 5px !important;
            }
            .fte-capacity-table th {
                font-size: 8.5px !important;
                letter-spacing: 0 !important;
                line-height: 1.15;
                white-space: normal;
            }
            .fte-capacity-table__num {
                white-space: normal;
                text-align: center !important;
            }
            .fte-capacity-table th:first-child,
            .fte-capacity-table td:first-child {
                width: 36px !important;
            }
            .fte-capacity-table th:nth-child(2),
            .fte-capacity-table td:nth-child(2) {
                width: auto !important;
                min-width: 0 !important;
            }
            .fte-capacity-table th:nth-child(3),
            .fte-capacity-table td:nth-child(3),
            .fte-capacity-table th:nth-child(4),
            .fte-capacity-table td:nth-child(4),
            .fte-capacity-table th:nth-child(5),
            .fte-capacity-table td:nth-child(5) {
                width: 14% !important;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table th:nth-child(6),
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table td:nth-child(6) {
                width: 11% !important;
            }
            .fte-capacity-sticky-col .fte-capacity-table th:nth-child(3),
            .fte-capacity-sticky-col .fte-capacity-table td:nth-child(3),
            .fte-capacity-sticky-col .fte-capacity-table th:nth-child(4),
            .fte-capacity-sticky-col .fte-capacity-table td:nth-child(4),
            .fte-capacity-sticky-col .fte-capacity-table th:nth-child(5),
            .fte-capacity-sticky-col .fte-capacity-table td:nth-child(5) {
                width: 16% !important;
            }
            .fte-capacity-rank {
                width: 24px !important;
                height: 24px !important;
                border-radius: 8px;
                font-size: 10px !important;
            }
            .fte-capacity-position__name,
            .fte-capacity-skill-name {
                overflow-wrap: anywhere !important;
                word-break: normal;
                font-size: 10.5px !important;
                line-height: 1.25;
            }
            .fte-capacity-position__skill {
                display: flex;
                min-width: 0;
                align-items: flex-start;
                font-size: 9.5px !important;
                line-height: 1.2;
                white-space: normal;
            }
            .fte-capacity-count,
            .fte-borrowed-badge,
            .fte-borrowed-empty {
                font-size: 12px !important;
            }
        }
        @media (min-width: 641px) and (max-width: 1366px) and (orientation: portrait) {
            .nature-layout {
                grid-template-columns: minmax(0, 1fr) !important;
            }
            .nature-sticky {
                position: static !important;
                top: auto !important;
            }
            .nature-map {
                display: block !important;
                width: 100% !important;
                max-width: 620px !important;
                min-height: 520px !important;
                height: auto !important;
                aspect-ratio: 1 !important;
                overflow: visible !important;
                border-radius: 32px !important;
                background: linear-gradient(135deg, #ffffff, #f8fafc) !important;
                padding: 16px !important;
            }
            .nature-callout-layer {
                display: block;
                position: absolute;
                inset: 0;
                z-index: 120;
                pointer-events: none;
            }
            .nature-map-callout {
                display: block;
                position: absolute;
                z-index: 121;
                width: 124px;
                border-radius: 12px;
                background: rgba(255, 255, 255, .96);
                border: 1px solid #e2e8f0;
                padding: 7px 9px;
                color: #0f172a;
                text-align: left;
                box-shadow: 0 10px 22px rgba(15, 23, 42, .16);
            }
            .nature-map-callout::before {
                content: "";
                position: absolute;
                height: 2px;
                border-radius: 999px;
                background: #64748b;
                opacity: .28;
            }
            .nature-map-callout[data-nature-callout="aging"] {
                left: 43%;
                top: 49%;
                background: #bae6fd;
                border-color: #38bdf8;
                color: #0c4a6e;
            }
            .nature-map-callout[data-nature-callout="aging"]::before {
                right: 100%;
                top: 50%;
                width: 28px;
                background: #38bdf8;
            }
            .nature-map-callout[data-nature-callout="hta"] {
                right: 5%;
                top: 48%;
                background: #fed7aa;
                border-color: #fb923c;
                color: #7c2d12;
            }
            .nature-map-callout[data-nature-callout="hta"]::before {
                right: 100%;
                top: 50%;
                width: 22px;
                background: #fb923c;
            }
            .nature-map-callout[data-nature-callout="access_population"] {
                left: 43%;
                bottom: 7%;
                background: #bbf7d0;
                border-color: #4ade80;
                color: #14532d;
            }
            .nature-map-callout[data-nature-callout="access_population"]::before {
                left: 36%;
                bottom: 100%;
                width: 1px;
                height: 18px;
                background: #4ade80;
            }
            .nature-map-callout[data-nature-callout="device_innovation"] {
                left: 25%;
                top: 18%;
                background: #fef08a;
                border-color: #eab308;
                color: #713f12;
            }
            .nature-map-callout[data-nature-callout="device_innovation"]::before {
                left: 100%;
                top: 50%;
                width: 18px;
                background: #eab308;
            }
            .nature-map-callout[data-nature-callout="data_rwe"] {
                left: 3%;
                top: 30%;
                background: #cbd5e1;
                border-color: #94a3b8;
                color: #334155;
            }
            .nature-map-callout[data-nature-callout="data_rwe"]::before {
                left: 100%;
                top: 50%;
                width: 18px;
                background: #94a3b8;
            }
            .nature-map-callout[data-nature-callout="ai_digital"] {
                right: 3%;
                top: 29%;
                background: #ddd6fe;
                border-color: #a78bfa;
                color: #4c1d95;
            }
            .nature-map-callout[data-nature-callout="ai_digital"]::before {
                right: 100%;
                top: 50%;
                width: 18px;
                background: #a78bfa;
            }
            .nature-map-callout[data-nature-callout="drug_rdu"] {
                left: 15%;
                bottom: 22%;
                background: #fecdd3;
                border-color: #fb7185;
                color: #881337;
            }
            .nature-map-callout[data-nature-callout="drug_rdu"]::before {
                left: 100%;
                top: 50%;
                width: 18px;
                background: #fb7185;
            }
            .nature-bubble,
            .nature-bubble.is-complete,
            .nature-bubble.is-active {
                position: absolute !important;
                display: grid !important;
                place-items: center !important;
                min-width: 0 !important;
                min-height: 0 !important;
                border-radius: 999px !important;
                filter: none !important;
                opacity: 1 !important;
                text-align: center;
                box-shadow: 0 10px 24px rgba(15, 23, 42, .10) !important;
            }
            .nature-bubble__inner {
                display: none !important;
            }
            .nature-map .nature-bubble[data-nature-bubble="aging"],
            .nature-map .nature-bubble[data-nature-bubble="aging"].is-active,
            .nature-map .nature-bubble[data-nature-bubble="hta"],
            .nature-map .nature-bubble[data-nature-bubble="hta"].is-active {
                z-index: 10 !important;
            }
            .nature-map .nature-bubble[data-nature-bubble="access_population"],
            .nature-map .nature-bubble[data-nature-bubble="access_population"].is-active {
                z-index: 55 !important;
            }
            .nature-map .nature-bubble[data-nature-bubble="device_innovation"],
            .nature-map .nature-bubble[data-nature-bubble="device_innovation"].is-active,
            .nature-map .nature-bubble[data-nature-bubble="data_rwe"],
            .nature-map .nature-bubble[data-nature-bubble="data_rwe"].is-active,
            .nature-map .nature-bubble[data-nature-bubble="ai_digital"],
            .nature-map .nature-bubble[data-nature-bubble="ai_digital"].is-active,
            .nature-map .nature-bubble[data-nature-bubble="drug_rdu"],
            .nature-map .nature-bubble[data-nature-bubble="drug_rdu"].is-active {
                z-index: 90 !important;
            }
            .nature-bubble.is-active::after {
                display: none !important;
            }
            .nature-bubble__callout {
                display: none !important;
            }
            .nature-bubble__callout-title {
                font-size: 12px;
                font-weight: 900;
                line-height: 1.15;
            }
            .nature-bubble__callout-count {
                margin-top: 2px;
                font-size: 11px !important;
                line-height: 1.15 !important;
                font-weight: 800;
                color: #64748b;
            }
            .nature-bubble[data-nature-bubble="aging"] {
                --callout-line: 54px;
                --callout-angle: 0deg;
            }
            .nature-bubble[data-nature-bubble="aging"] .nature-bubble__callout {
                left: calc(100% + 44px);
                top: 50%;
                transform: translateY(-50%);
            }
            .nature-bubble[data-nature-bubble="hta"] {
                --callout-line: 64px;
                --callout-angle: 180deg;
            }
            .nature-bubble[data-nature-bubble="hta"]::before {
                right: 100%;
                left: auto;
                transform-origin: right center;
            }
            .nature-bubble[data-nature-bubble="hta"] .nature-bubble__callout {
                right: calc(100% + 54px);
                top: 50%;
                transform: translateY(-50%);
            }
            .nature-bubble[data-nature-bubble="access_population"] {
                --callout-line: 42px;
                --callout-angle: -92deg;
            }
            .nature-bubble[data-nature-bubble="access_population"] .nature-bubble__callout {
                left: 50%;
                bottom: calc(100% + 36px);
                transform: translateX(-50%);
            }
            .nature-bubble[data-nature-bubble="device_innovation"],
            .nature-bubble[data-nature-bubble="data_rwe"] {
                --callout-line: 38px;
                --callout-angle: 180deg;
            }
            .nature-bubble[data-nature-bubble="device_innovation"]::before,
            .nature-bubble[data-nature-bubble="data_rwe"]::before {
                right: 100%;
                left: auto;
                transform-origin: right center;
            }
            .nature-bubble[data-nature-bubble="device_innovation"] .nature-bubble__callout,
            .nature-bubble[data-nature-bubble="data_rwe"] .nature-bubble__callout {
                right: calc(100% + 32px);
                top: 50%;
                transform: translateY(-50%);
            }
            .nature-bubble[data-nature-bubble="ai_digital"],
            .nature-bubble[data-nature-bubble="drug_rdu"] {
                --callout-line: 36px;
                --callout-angle: 0deg;
            }
            .nature-bubble[data-nature-bubble="ai_digital"] .nature-bubble__callout,
            .nature-bubble[data-nature-bubble="drug_rdu"] .nature-bubble__callout {
                left: calc(100% + 30px);
                top: 50%;
                transform: translateY(-50%);
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table-wrap {
                display: block !important;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-position-cards {
                display: none !important;
            }
            .fte-capacity-card[data-fte-capacity-left] .fte-capacity-table-note {
                margin-bottom: 0;
            }
        }
        @media (max-width: 640px) {
            .nature-layout {
                grid-template-columns: minmax(0, 1fr) !important;
            }
            .nature-sticky {
                position: static !important;
                top: auto !important;
            }
            .nature-map {
                display: block !important;
                width: 100% !important;
                max-width: min(100%, 390px) !important;
                min-height: 390px !important;
                height: auto !important;
                aspect-ratio: 1 !important;
                overflow: visible !important;
                border-radius: 20px !important;
                background: linear-gradient(135deg, #ffffff, #f8fafc) !important;
                padding: 10px !important;
            }
            .nature-bubble,
            .nature-bubble.is-complete,
            .nature-bubble.is-active {
                position: absolute !important;
                display: grid !important;
                place-items: center !important;
                min-width: 0 !important;
                min-height: 0 !important;
                border-radius: 999px !important;
                filter: none !important;
                opacity: 1 !important;
                text-align: center;
                box-shadow: 0 8px 18px rgba(15, 23, 42, .10) !important;
            }
            .nature-bubble__inner {
                display: none !important;
            }
            .nature-map .nature-bubble[data-nature-bubble="aging"],
            .nature-map .nature-bubble[data-nature-bubble="aging"].is-active,
            .nature-map .nature-bubble[data-nature-bubble="hta"],
            .nature-map .nature-bubble[data-nature-bubble="hta"].is-active {
                z-index: 10 !important;
            }
            .nature-map .nature-bubble[data-nature-bubble="access_population"],
            .nature-map .nature-bubble[data-nature-bubble="access_population"].is-active {
                z-index: 55 !important;
            }
            .nature-map .nature-bubble[data-nature-bubble="device_innovation"],
            .nature-map .nature-bubble[data-nature-bubble="device_innovation"].is-active,
            .nature-map .nature-bubble[data-nature-bubble="data_rwe"],
            .nature-map .nature-bubble[data-nature-bubble="data_rwe"].is-active,
            .nature-map .nature-bubble[data-nature-bubble="ai_digital"],
            .nature-map .nature-bubble[data-nature-bubble="ai_digital"].is-active,
            .nature-map .nature-bubble[data-nature-bubble="drug_rdu"],
            .nature-map .nature-bubble[data-nature-bubble="drug_rdu"].is-active {
                z-index: 90 !important;
            }
            .nature-bubble.is-active::after {
                display: none !important;
            }
            .nature-bubble::before {
                content: "";
                position: absolute;
                height: 2px;
                border-radius: 999px;
                width: var(--callout-line, 28px);
                background: color-mix(in srgb, currentColor 54%, transparent);
                opacity: .28;
                transform-origin: left center;
                transform: rotate(var(--callout-angle, 0deg));
                pointer-events: none;
            }
            .nature-bubble__callout {
                display: block;
                position: absolute;
                z-index: 50;
                width: var(--callout-width, 96px);
                border-radius: 10px;
                background: rgba(255, 255, 255, .94);
                border: 1px solid #e2e8f0;
                padding: 5px 7px;
                color: #0f172a;
                text-align: left;
                box-shadow: 0 8px 18px rgba(15, 23, 42, .14);
                pointer-events: none;
            }
            .nature-bubble[data-nature-bubble="aging"] .nature-bubble__callout {
                background: #bae6fd;
                border-color: #38bdf8;
                color: #0c4a6e;
            }
            .nature-bubble[data-nature-bubble="hta"] .nature-bubble__callout {
                background: #fed7aa;
                border-color: #fb923c;
                color: #7c2d12;
            }
            .nature-bubble[data-nature-bubble="access_population"] .nature-bubble__callout {
                background: #bbf7d0;
                border-color: #4ade80;
                color: #14532d;
            }
            .nature-bubble[data-nature-bubble="device_innovation"] .nature-bubble__callout {
                background: #fef08a;
                border-color: #eab308;
                color: #713f12;
            }
            .nature-bubble[data-nature-bubble="data_rwe"] .nature-bubble__callout {
                background: #cbd5e1;
                border-color: #94a3b8;
                color: #334155;
            }
            .nature-bubble[data-nature-bubble="ai_digital"] .nature-bubble__callout {
                background: #ddd6fe;
                border-color: #a78bfa;
                color: #4c1d95;
            }
            .nature-bubble[data-nature-bubble="drug_rdu"] .nature-bubble__callout {
                background: #fecdd3;
                border-color: #fb7185;
                color: #881337;
            }
            .nature-bubble__callout-title {
                font-size: 10px;
                font-weight: 900;
                line-height: 1.1;
            }
            .nature-bubble__callout-count {
                margin-top: 2px;
                font-size: 9px !important;
                line-height: 1.1 !important;
                font-weight: 800;
                color: #64748b;
            }
            .nature-bubble[data-nature-bubble="aging"] {
                --callout-line: 28px;
                --callout-angle: 0deg;
            }
            .nature-bubble[data-nature-bubble="aging"] .nature-bubble__callout {
                left: calc(100% + 22px);
                top: 50%;
                transform: translateY(-50%);
            }
            .nature-bubble[data-nature-bubble="hta"] {
                --callout-line: 32px;
                --callout-angle: 180deg;
            }
            .nature-bubble[data-nature-bubble="hta"]::before {
                right: 100%;
                left: auto;
                transform-origin: right center;
            }
            .nature-bubble[data-nature-bubble="hta"] .nature-bubble__callout {
                right: calc(100% + 28px);
                top: 50%;
                transform: translateY(-50%);
            }
            .nature-bubble[data-nature-bubble="access_population"] {
                --callout-line: 24px;
                --callout-angle: -92deg;
            }
            .nature-bubble[data-nature-bubble="access_population"] .nature-bubble__callout {
                left: 50%;
                bottom: calc(100% + 20px);
                transform: translateX(-50%);
            }
            .nature-bubble[data-nature-bubble="device_innovation"],
            .nature-bubble[data-nature-bubble="data_rwe"] {
                --callout-line: 22px;
                --callout-angle: 180deg;
            }
            .nature-bubble[data-nature-bubble="device_innovation"]::before,
            .nature-bubble[data-nature-bubble="data_rwe"]::before {
                right: 100%;
                left: auto;
                transform-origin: right center;
            }
            .nature-bubble[data-nature-bubble="device_innovation"] .nature-bubble__callout,
            .nature-bubble[data-nature-bubble="data_rwe"] .nature-bubble__callout {
                right: calc(100% + 18px);
                top: 50%;
                transform: translateY(-50%);
            }
            .nature-bubble[data-nature-bubble="ai_digital"],
            .nature-bubble[data-nature-bubble="drug_rdu"] {
                --callout-line: 20px;
                --callout-angle: 0deg;
            }
            .nature-bubble[data-nature-bubble="ai_digital"] .nature-bubble__callout,
            .nature-bubble[data-nature-bubble="drug_rdu"] .nature-bubble__callout {
                left: calc(100% + 16px);
                top: 50%;
                transform: translateY(-50%);
            }
            .viz-page {
                width: 100%;
                margin-left: 0;
                margin-right: 0;
                overflow-x: clip;
                overflow-wrap: anywhere;
            }
            .viz-page > div {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
            .fade-up {
                opacity: 1;
                transform: none;
                animation: none;
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
            .fte-radar-chart-wrap {
                height: 360px;
            }
            .fte-radar-card,
            .fte-time-card {
                padding: 12px;
            }
            .fte-radar-card__title {
                font-size: 13px;
            }
            .fte-radar-legend {
                gap: 8px 12px;
                font-size: 11px;
            }
            .fte-radar-legend i {
                width: 12px;
                height: 12px;
            }
            .fte-chart-insight__body,
            .fte-insight-item {
                border-left-width: 3px;
                padding: 9px 10px;
                font-size: 13px;
                line-height: 1.6;
            }
            .fte-capacity-table {
                min-width: 560px;
                font-size: 12px;
            }
            .fte-capacity-card {
                padding: 12px;
            }
            .fte-capacity-card__head {
                gap: 10px;
                margin-bottom: 10px;
            }
            .fte-capacity-card__title {
                font-size: 13px;
                line-height: 1.45;
            }
            .fte-capacity-card__note {
                font-size: 11px;
                line-height: 1.55;
            }
            .fte-capacity-metrics {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100%;
            }
            .fte-capacity-metric {
                min-width: 0;
                padding: 6px 8px;
            }
            .fte-capacity-metric__value {
                font-size: 16px;
            }
            .fte-capacity-tables {
                gap: 14px;
            }
            .fte-capacity-table-wrap {
                overflow-x: visible;
                border: 0;
                border-radius: 0;
                background: transparent;
            }
            .fte-capacity-table,
            .fte-capacity-table thead,
            .fte-capacity-table tbody,
            .fte-capacity-table tfoot,
            .fte-capacity-table tr,
            .fte-capacity-table th,
            .fte-capacity-table td {
                display: block;
                min-width: 0 !important;
                width: 100%;
            }
            .fte-capacity-table {
                font-size: 13px;
            }
            .fte-capacity-table thead {
                display: none;
            }
            .fte-capacity-table tbody {
                display: grid;
                gap: 8px;
            }
            .fte-capacity-table tbody tr {
                display: grid;
                grid-template-columns: 32px minmax(0, 1fr);
                gap: 6px 10px;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                background: #ffffff;
                padding: 10px;
                align-items: start;
            }
            .fte-capacity-table tbody tr:nth-child(even) {
                background: #f8fafc;
            }
            .fte-capacity-table tbody td,
            .fte-capacity-table tbody tr:nth-child(odd) td,
            .fte-capacity-table tbody tr:nth-child(even) td,
            .fte-capacity-table tbody tr:hover td {
                border-bottom: 0;
                background: transparent;
                padding: 0;
            }
            .fte-capacity-table tbody td:first-child {
                grid-row: 1 / span 8;
                width: 32px;
            }
            .fte-capacity-table tbody td:nth-child(2) {
                grid-column: 2;
            }
            .fte-capacity-table tbody td.fte-capacity-table__num {
                grid-column: 2;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                border-top: 1px dashed #e2e8f0;
                padding-top: 6px;
                text-align: right;
            }
            .fte-capacity-table tbody td.fte-capacity-table__num::before,
            .fte-capacity-table tfoot td.fte-capacity-table__num::before {
                content: attr(data-label);
                color: #64748b;
                font-size: 12px;
                font-weight: 700;
                text-align: left;
                max-width: 60%;
                padding-right: 8px;
            }
            .fte-capacity-rank {
                width: 28px;
                height: 28px;
                border-radius: 10px;
                font-size: 11px;
            }
            .fte-capacity-position__name,
            .fte-capacity-skill-name {
                overflow-wrap: anywhere;
                line-height: 1.35;
            }
            .fte-capacity-position__skill,
            .fte-capacity-skill-name {
                align-items: flex-start;
            }
            .fte-capacity-count,
            .fte-borrowed-badge,
            .fte-borrowed-empty {
                flex: 0 0 auto;
            }
            .fte-capacity-table tfoot {
                margin-top: 8px;
            }
            .fte-capacity-table tfoot tr {
                display: grid;
                gap: 6px;
                border: 1px solid #cbd5e1;
                border-radius: 12px;
                background: #f1f5f9;
                padding: 10px;
            }
            .fte-capacity-table tfoot td {
                border: 0;
                background: transparent;
                padding: 0;
            }
            .fte-capacity-table tfoot td:first-child {
                color: #0f172a;
                font-weight: 800;
            }
            .fte-capacity-table tfoot td.fte-capacity-table__num {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                text-align: right;
            }
            .fte-capacity-table-note {
                font-size: 11px;
            }
            .fte-radar-breakdown__row {
                grid-template-columns: 1fr;
            }
            .fte-radar-breakdown__metrics {
                justify-content: flex-start;
                max-width: none;
            }
            .policy-pill {
                min-height: 0;
                padding: 6px 10px;
            }
            .policy-section-title {
                align-items: flex-start;
            }
            .policy-section-title h3 {
                font-size: 18px;
                line-height: 1.35;
            }
            .framework-card {
                min-height: auto;
            }
            .nature-section {
                padding: 12px !important;
            }
            .nature-shell {
                border-radius: 14px !important;
                padding: 12px !important;
            }
            .nature-shell h2 {
                font-size: 21px !important;
                line-height: 1.25 !important;
            }
            .nature-shell .mt-5.grid.gap-3 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }
            .nature-shell .mt-5.grid.gap-3 > div {
                border-radius: 12px;
                padding: 12px;
            }
            .nature-shell .mt-5.grid.gap-3 .text-3xl {
                font-size: 24px !important;
            }
            .nature-layout {
                gap: 14px;
            }
            .nature-sticky {
                border-radius: 14px !important;
                padding: 12px !important;
            }
            .nature-sticky > .flex {
                align-items: flex-start;
            }
            .nature-sticky > .flex .rounded-full {
                border-radius: 12px;
                white-space: nowrap;
            }
            .nature-map {
                width: 100%;
                min-height: 0 !important;
                max-width: min(100%, calc(100vw - 72px));
                border-radius: 20px !important;
                padding: 8px !important;
            }
            .nature-map .absolute.inset-6 {
                inset: 14px !important;
            }
            .nature-bubble {
                min-width: 42px;
                min-height: 42px;
                max-width: 46%;
                overflow: hidden;
                filter: none;
                opacity: .9;
            }
            .nature-bubble > div {
                padding-left: 5px !important;
                padding-right: 5px !important;
            }
            .nature-bubble [class*="text-[15px]"],
            .nature-bubble [class*="text-[12px]"],
            .nature-bubble [class*="text-[10px]"] {
                font-size: clamp(8px, 2.35vw, 11px) !important;
                line-height: 1.08 !important;
            }
            .nature-bubble .mt-1 {
                margin-top: 1px !important;
                font-size: clamp(7px, 2vw, 9px) !important;
                line-height: 1 !important;
            }
            .nature-bubble.is-active {
                box-shadow: 0 14px 28px rgba(15, 23, 42, .22) !important;
                outline-width: 2px;
            }
            .nature-bubble.is-active::after {
                inset: -4px;
                border-width: 1px;
                box-shadow: 0 0 0 4px rgba(255, 255, 255, .62);
            }
            .nature-card {
                border-radius: 14px !important;
                padding: 14px !important;
                scroll-margin-top: 12px;
            }
            .nature-card .text-lg {
                font-size: 16px !important;
                line-height: 1.35 !important;
            }
            .nature-card .mt-5.grid {
                gap: 12px;
            }
            .nature-card .border-t {
                padding-top: 12px;
            }
            .nature-card .bg-amber-50\/50 {
                border-radius: 12px;
                padding: 12px !important;
            }
            .viz-page [class*="h-[340px]"],
            .viz-page [class*="h-[360px]"],
            .viz-page [class*="h-[380px]"] {
                height: 320px !important;
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
            .viz-pdf-popover {
                left: 10px;
                top: auto;
                bottom: 14px;
            }
            .viz-pdf-popover summary {
                width: 42px;
                height: 42px;
                font-size: 10px;
            }
            .viz-pdf-popover__panel {
                left: 48px;
                top: auto;
                bottom: 0;
                width: min(218px, calc(100vw - 68px));
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
            .doctor-lane-board {
                display: none;
            }
            .doctor-mobile-timeline {
                display: grid;
                gap: 12px;
            }
            .unit-mobile-role-timeline {
                display: grid;
                gap: 12px;
            }
            .doctor-mobile-theme__head {
                padding: 12px;
            }
            .doctor-mobile-year {
                padding: 12px;
            }
            .doctor-mobile-year .doctor-avatar-stack {
                justify-content: flex-start;
            }
            .doctor-avatar-stack {
                gap: 10px;
            }
            .doctor-avatar-stack img {
                width: 56px;
                height: 56px;
                border-width: 3px;
            }
            .doctor-role-tabs {
                display: grid !important;
                width: 100%;
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .doctor-role-tab {
                min-width: 0;
                min-height: 48px;
                padding: 8px 6px !important;
                text-align: center;
                line-height: 1.15;
                white-space: normal;
            }
            .doctor-role-tab span {
                display: block;
                margin-left: 0;
                margin-top: 4px;
                font-size: 12px;
            }
            .doctor-role-floating {
                position: fixed;
                left: 50%;
                bottom: 14px;
                z-index: 70;
                display: flex;
                max-width: calc(100vw - 24px);
                transform: translate(-50%, 14px);
                align-items: center;
                gap: 4px;
                border: 1px solid rgba(15, 23, 42, .18);
                border-radius: 999px;
                background: rgba(255, 255, 255, .94);
                padding: 5px;
                box-shadow: 0 18px 40px rgba(15, 23, 42, .18);
                opacity: 0;
                pointer-events: none;
                transition: opacity .18s ease, transform .18s ease;
                backdrop-filter: blur(10px);
            }
            .doctor-role-floating.is-visible {
                transform: translate(-50%, 0);
                opacity: 1;
                pointer-events: auto;
            }
            .doctor-role-floating button {
                min-width: 56px;
                border: 1px solid transparent;
                background: transparent;
                border-radius: 999px;
                padding: 7px 9px;
                font-size: 11px;
                font-weight: 800;
                line-height: 1;
                color: #0f766e;
                white-space: nowrap;
            }
            .doctor-role-floating button.is-active {
                border-color: #14b8a6;
                background: #ffffff;
                color: #0f172a;
                box-shadow: 0 8px 18px rgba(15, 118, 110, .2);
            }
            .unit-mobile-avatar-grid img {
                width: 44px;
                height: 44px;
            }
            #frameworkTrendModalBody .rounded-xl {
                overflow-wrap: anywhere;
            }
            .doctor-avatar-stack a:hover,
            .doctor-avatar-stack a:focus-visible,
            .doctor-avatar-stack img:hover {
                transform: none;
            }
            .doctor-avatar-stack a:hover img,
            .doctor-avatar-stack a:focus-visible img,
            .doctor-avatar-stack img:hover {
                box-shadow: 0 0 0 1px #cbd5e1, 0 8px 16px rgba(15, 23, 42, .10);
            }
            .clinical-led-chart-scroll {
                height: 320px;
                margin-left: 0;
                margin-right: 0;
                padding-bottom: 8px;
                overflow-x: clip;
            }
            .clinical-led-chart-inner {
                min-width: 0;
                width: 100%;
            }
            .fte-taxonomy {
                border-radius: 8px;
            }
            .fte-taxonomy__head {
                display: none;
            }
            .fte-taxonomy__row {
                display: block;
                border-top: 1px solid #d6dee8;
                padding: 10px 0;
            }
            .fte-taxonomy__row:first-of-type {
                border-top: 0;
            }
            .fte-taxonomy__group {
                border-top: 0;
                border-bottom: 1px solid #e2e8f0;
                padding: 8px 10px;
            }
            .fte-taxonomy__cell {
                border-top: 0;
                border-left: 0;
                padding: 4px 10px;
            }
            .fte-taxonomy__count {
                margin-left: 0;
                margin-top: 4px;
            }
            .fte-capability-group__head {
                flex-direction: column;
                gap: 8px;
                padding: 12px;
            }
            .fte-capability-group__title {
                font-size: 16px;
            }
            .fte-capability-group__metric {
                white-space: normal;
            }
            .fte-capability-item {
                grid-template-columns: 1fr;
                gap: 8px;
                padding: 12px;
                flex-basis: 100%;
            }
            .fte-capability-item__stats {
                justify-content: flex-start;
            }
        }
        @media (min-width: 600px) and (max-width: 767px) and (orientation: portrait) {
            .viz-page > div {
                padding-left: 18px !important;
                padding-right: 18px !important;
            }
            .viz-page .md\:hidden {
                display: none !important;
            }
            .viz-page .hidden.md\:block,
            .viz-page .md\:block {
                display: block !important;
            }
            .viz-page .md\:flex {
                display: flex !important;
            }
            .viz-page .md\:grid {
                display: grid !important;
            }
            .viz-page .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
            .viz-page .md\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
            .viz-page .md\:grid-cols-4 {
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            }
            .viz-page .md\:grid-cols-5 {
                grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
            }
            .filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
            .policy-stat-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
            .policy-people-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
            .contribution-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
            .viz-page .scene.scene--compact.grid,
            .viz-page .scene.scene--balanced.grid {
                display: grid !important;
                grid-template-columns: minmax(0, 1.12fr) minmax(0, 0.88fr) !important;
                align-items: stretch !important;
                gap: 14px !important;
            }
            .viz-page .scene.scene--compact > article,
            .viz-page .scene.scene--balanced > article {
                min-width: 0 !important;
                height: 100% !important;
            }
            .viz-page .scene.scene--compact .chart-sticky,
            .viz-page .scene.scene--balanced .chart-sticky {
                position: relative !important;
                top: auto !important;
            }
            .viz-page .scene.scene--compact h2,
            .viz-page .scene.scene--balanced h2 {
                font-size: 0.95rem !important;
                line-height: 1.35 !important;
            }
            .viz-page .scene.scene--compact h3,
            .viz-page .scene.scene--balanced h3 {
                font-size: 1rem !important;
                line-height: 1.35 !important;
            }
            .viz-page .scene.scene--compact .section-panel,
            .viz-page .scene.scene--balanced .section-panel {
                padding: 14px !important;
            }
            .viz-page .scene.scene--compact [class*="h-[360px]"],
            .viz-page .scene.scene--balanced [class*="h-[360px]"],
            .viz-page .scene.scene--balanced [class*="h-[340px]"] {
                height: 300px !important;
            }
            .viz-page .scene.scene--balanced .type-chart-wrap {
                height: 300px !important;
            }
            .viz-page .scene.scene--compact .scroll-card,
            .viz-page .scene.scene--balanced .scroll-card,
            .viz-page .scene.scene--balanced .paper-record-scroll {
                max-height: 300px !important;
            }
            .viz-page .scene.scene--balanced .js-output-item {
                padding: 9px 10px !important;
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

        @media (min-width: 768px) and (max-width: 1279px) and (orientation: portrait) {
            .viz-page .scene.scene--compact.grid,
            .viz-page .scene.scene--balanced.grid {
                display: grid !important;
                grid-template-columns: minmax(0, 1.12fr) minmax(0, 0.88fr) !important;
                align-items: stretch !important;
                gap: 14px !important;
            }

            .viz-page .scene.scene--compact > article,
            .viz-page .scene.scene--balanced > article {
                min-width: 0 !important;
                height: 100% !important;
            }

            .viz-page .scene.scene--compact .chart-sticky,
            .viz-page .scene.scene--balanced .chart-sticky {
                position: relative !important;
                top: auto !important;
            }

            .viz-page .scene.scene--compact h2,
            .viz-page .scene.scene--balanced h2 {
                font-size: 0.95rem !important;
                line-height: 1.35 !important;
            }

            .viz-page .scene.scene--compact h3,
            .viz-page .scene.scene--balanced h3 {
                font-size: 1.05rem !important;
                line-height: 1.35 !important;
            }

            .viz-page .scene.scene--compact .section-panel,
            .viz-page .scene.scene--balanced .section-panel {
                padding: 16px !important;
            }

            .viz-page .scene.scene--compact [class*="h-[360px]"],
            .viz-page .scene.scene--balanced [class*="h-[360px]"],
            .viz-page .scene.scene--balanced [class*="h-[340px]"] {
                height: 300px !important;
            }

            .viz-page .scene.scene--balanced .type-chart-wrap {
                height: 330px !important;
            }

            .viz-page .scene.scene--compact .metric,
            .viz-page .scene.scene--balanced .metric {
                font-size: 1.25rem !important;
                line-height: 1.15 !important;
            }

            .viz-page .scene.scene--compact p,
            .viz-page .scene.scene--balanced p,
            .viz-page .scene.scene--compact .text-sm,
            .viz-page .scene.scene--balanced .text-sm {
                font-size: 0.78rem !important;
                line-height: 1.45 !important;
            }

            .viz-page .scene.scene--compact .scroll-card,
            .viz-page .scene.scene--balanced .scroll-card,
            .viz-page .scene.scene--balanced .paper-record-scroll {
                max-height: 330px !important;
            }

            .viz-page .scene.scene--balanced .js-output-item {
                padding: 0.65rem 0.75rem !important;
            }

            .viz-page .type-table-head,
            .viz-page .type-table-row {
                grid-template-columns: minmax(0, 2fr) 86px 76px 86px !important;
                gap: 8px !important;
            }

            .viz-page .doctor-lane-board {
                display: block !important;
                overflow-x: visible !important;
                padding-bottom: 0 !important;
            }

            .viz-page .doctor-mobile-timeline,
            .viz-page .unit-mobile-role-timeline {
                display: none !important;
            }

            .viz-page .doctor-lane-grid {
                min-width: 0 !important;
                width: 100% !important;
            }

            .viz-page .doctor-lane-row,
            .viz-page .doctor-lane-head {
                grid-template-columns: minmax(150px, 0.95fr) repeat(var(--doctor-year-count, 1), minmax(46px, 1fr)) !important;
            }

            .viz-page .doctor-lane-theme,
            .viz-page .doctor-lane-year,
            .viz-page .doctor-lane-cell {
                padding: 8px !important;
            }

            .viz-page .doctor-lane-theme .text-sm,
            .viz-page .doctor-lane-cell,
            .viz-page .doctor-lane-year {
                font-size: 0.72rem !important;
                line-height: 1.25 !important;
            }

            .viz-page .doctor-lane-cell {
                min-height: 58px !important;
            }

            .viz-page .doctor-avatar-stack {
                gap: 5px !important;
            }

            .viz-page .doctor-avatar-stack img {
                width: 34px !important;
                height: 34px !important;
                border-width: 2px !important;
            }

            .viz-page .unit-contribution-matrix .doctor-lane-cell {
                min-height: 72px !important;
                max-height: none !important;
                padding: 5px !important;
            }

            .viz-page .unit-contribution-matrix .doctor-lane-row .doctor-lane-theme {
                min-height: 72px !important;
            }

            .viz-page .unit-avatar-stack {
                grid-template-columns: repeat(auto-fit, minmax(30px, 30px)) !important;
                gap: 4px !important;
                max-height: 84px !important;
                padding: 2px !important;
            }

            .viz-page .unit-avatar-stack img {
                width: 30px !important;
                height: 30px !important;
            }

            .viz-page .unit-cell-count {
                font-size: 0.58rem !important;
                padding: 1px 5px !important;
            }

            .viz-page .doctor-role-tabs {
                display: inline-flex !important;
                width: auto !important;
                grid-template-columns: none !important;
            }

            .viz-page .doctor-role-tab {
                min-height: 34px !important;
                padding: 6px 9px !important;
                white-space: nowrap !important;
            }

            .viz-page .doctor-role-tab span {
                display: inline !important;
                margin-left: 4px !important;
                margin-top: 0 !important;
                font-size: 0.68rem !important;
            }

            .viz-page .doctor-role-floating {
                display: none !important;
            }
        }
    </style>

    <div class="viz-page min-h-screen pb-10">
        {{-- PDF export is hidden for now. Keep the route/export code available for later use. --}}
        <div class="mx-auto w-full max-w-[1600px] space-y-6 px-4 py-6 md:px-8 md:py-8 2xl:px-10">
            <section class="fade-up hero-panel section-panel section-panel--slate p-7 md:p-10">
                <div class="hero-kicker">Research Outputs Overview</div>
                <h1 class="hero-title mt-3 font-semibold text-slate-900">{{ $pageTitle }}</h1>
                {{-- <p class="mt-3 max-w-5xl text-sm font-normal leading-relaxed text-slate-500 md:text-base">
                    เป็นการใช้ AI ช่วยวิเคราะห์เบื้องต้น อันเกิดจาก data ของหน่วย และยังต้องอาศัยการร่วมกันคิดของทีมเพื่อให้ได้ข้อสรุปที่ชัดเจน
                </p> --}}

            </section>

            <section class="fade-up panel filter-shell section-panel section-panel--slate p-5 md:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <div class="section-tag section-tag--slate">Visualization Filters</div>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">เลือกช่วงข้อมูลที่อยากดู</h2>
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

                <form id="vizFilters" method="GET" action="{{ route($visualizationRouteName ?? 'outputs.visualization') }}" class="filter-grid mt-5">
                    <label class="filter-field">
                        <span class="filter-label">แหล่งข้อมูล</span>
                        <select name="source" class="js-auto-filter filter-select">
                            <option value="">ทั้งหมด</option>
                            @foreach ($sourceLabelMap as $sourceKey => $sourceLabel)
                                <option value="{{ $sourceKey }}" @selected($selectedSource === $sourceKey)>{{ $sourceLabel }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="filter-field">
                        <span class="filter-label">ประเภทผลงาน (EA)</span>
                        <select name="type" class="js-auto-filter filter-select">
                            <option value="">ทั้งหมด</option>
                            @foreach ($typeOptions as $typeCode)
                                <option value="{{ $typeCode }}" @selected($selectedType === $typeCode)>{{ $typeCode }} - {{ $eaFullNameMap[$typeCode] ?? ('ประเภท ' . $typeCode) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="filter-field">
                        <span class="filter-label">ปีเริ่ม (พ.ศ.)</span>
                        <select name="year_from" class="js-auto-filter filter-select">
                            <option value="">ทั้งหมด</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected((int) $selectedYearFrom === (int) $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="filter-field">
                        <span class="filter-label">ปีสิ้นสุด (พ.ศ.)</span>
                        <select name="year_to" class="js-auto-filter filter-select">
                            <option value="">ทั้งหมด</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected((int) $selectedYearTo === (int) $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="flex flex-col justify-end gap-3">
                        <div id="filterLoading" class="hidden rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-medium text-blue-700">กำลังอัปเดตข้อมูล...</div>
                        <a href="{{ route($visualizationRouteName ?? 'outputs.visualization') }}" class="inline-flex min-h-[50px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">ล้างตัวกรอง</a>
                    </div>
                </form>
            </section>

            @php
                $policyProjectNature = $projectNatureAnalysis ?? [];
                $policyProjectTypeRows = collect(data_get($policyProjectNature, 'type_rows', []));
                $policyProjectTotal = (int) data_get($policyProjectNature, 'project_total', 0);
            @endphp
            <section class="fade-up panel section-panel section-panel--slate fte-skill-section p-5 md:p-6">
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Project Scope</div>
                        <div class="mt-1 text-lg font-semibold text-slate-950">{{ number_format($policyProjectTotal) }} โครงการในระบบ</div>
                    </div>
                    <div class="mt-4 grid gap-2 md:grid-cols-2 lg:grid-cols-3">
                        @forelse ($policyProjectTypeRows as $typeRow)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3">
                                <div class="text-sm font-semibold leading-snug text-slate-900">{{ data_get($typeRow, 'type') }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ number_format((int) data_get($typeRow, 'count', 0)) }} โครงการ</div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 px-3 py-3 text-sm text-slate-500">ยังไม่มีข้อมูลประเภทโครงการ</div>
                        @endforelse
                    </div>
                </div>
            </section>


            <section class="fade-up panel section-panel section-panel--indigo policy-shell p-5 md:p-6">
                <div class="policy-brief">
                    <div class="section-tag section-tag--indigo">การเชื่อมโยงงานวิจัยสู่การตัดสินใจเชิงนโยบาย</div>
                    <div class="w-full">
                        <h2 class="max-w-[1500px] font-semibold text-slate-900" style="font-size: clamp(24px, 1.9vw, 32px); line-height: 1.42; letter-spacing: 0;">
                            โครงการและผลงาน Siriraj Health Policy สามารถสร้างและใช้ประโยชน์จากบริบทศิริราช เพื่อขับเคลื่อนการตัดสินใจเชิงนโยบายได้มากเพียงใด
                        </h2>
                        <p class="mt-4 max-w-none text-base leading-relaxed text-slate-600 md:text-lg">
                            Policy-Relevant Decision-Making = (Clinical Insight + Epidemiological Evidence + Economic Value) × Communication = Policy Impact
                        </p>
                        <div class="mt-4 grid gap-3 md:grid-cols-3">
                            <div class="rounded-2xl border border-indigo-100 bg-white/80 px-4 py-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Trust (Credibility)</div>
                                <div class="mt-1 text-sm leading-relaxed text-slate-700">Clinical Relevance (Siriraj, Real-world)</div>
                            </div>
                            <div class="rounded-2xl border border-emerald-100 bg-white/80 px-4 py-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Truth (Validation)</div>
                                <div class="mt-1 text-sm leading-relaxed text-slate-700">System Analysis / Epidemiology + Health Economics (HTA)</div>
                            </div>
                            <div class="rounded-2xl border border-orange-100 bg-white/80 px-4 py-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-orange-700">Translation (Impact)</div>
                                <div class="mt-1 text-sm leading-relaxed text-slate-700">Strategic Communication (to policy & practice)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="policy-axis-card mt-4">
                        <div class="policy-section-title">
                            <div>
                            <h3 class="text-base font-semibold text-slate-900">Trend of our work toward Policy-Relevant Decision-Making</h3>
                            <p class="mt-1 text-sm text-slate-600">แนวโน้มของโครงการต่างๆ ว่าสามารถเชื่อม Clinical Insight ผ่านกระบวนการสร้าง Evidence และไปถึง communication และ policy impact แค่ไหน</p>
                        </div>
                        <span class="policy-pill">แสดงเป็นจำนวนต่อปี</span>
                    </div>
                    <div class="grid gap-4 lg:grid-cols-[1.35fr_0.65fr]">
                        <div class="policy-trend-panel flex h-full flex-col">
                            <div class="h-[360px]">
                                <canvas id="chartPolicyFrameworkTrend"></canvas>
                            </div>
                            @if ($policyFrameworkSummary)
                                <div class="mt-4 flex-1 rounded-2xl border border-sky-300 bg-sky-50 px-4 py-4">
                                    <div class="text-xl font-semibold text-sky-950">Analysis Insight</div>
                                    <div class="mt-3 grid gap-2">
                                        <div class="rounded-xl bg-white/80 px-3 py-2">
                                            <div class="text-[11px] font-semibold text-sky-700">เกิดอะไรขึ้น</div>
                                            <div class="mt-1 text-sm font-semibold leading-relaxed text-slate-900">{{ $policyFrameworkSummary->headline }}</div>
                                        </div>
                                        <div class="rounded-xl bg-white/80 px-3 py-2">
                                            <div class="text-[11px] font-semibold text-sky-700">แปลว่าอะไร</div>
                                            <div class="mt-1 text-sm leading-relaxed text-slate-700">{{ $policyFrameworkSummary->body }}</div>
                                        </div>
                                        <div class="rounded-xl bg-white/80 px-3 py-2">
                                            <div class="text-[11px] font-semibold text-sky-700">สิ่งที่เกิดขึ้น</div>
                                            <div class="mt-1 text-sm leading-relaxed text-slate-700">{{ $policyFrameworkSummary->focus }}</div>
                                        </div>
                                    </div>
                                    @if (!empty($policyFrameworkSummary->note))
                                        <div class="mt-3 rounded-xl bg-white/65 px-3 py-2 text-xs leading-relaxed text-slate-500">{{ $policyFrameworkSummary->note }}</div>
                                    @endif
                                    <div class="mt-3 rounded-xl bg-white/65 px-3 py-2 text-xs leading-relaxed text-slate-500">
                                        {{ $analysisInsightRemark }}
                                    </div>
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
                                        <div class="whitespace-pre-line text-xs leading-relaxed text-slate-600">{{ $item->detail }}</div>
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
                            <h3 class="text-xl font-semibold text-slate-900">Credibility</h3>
                            <p class="mt-1 max-w-4xl text-sm leading-relaxed text-indigo-950/80">สรุปเฉพาะโครงการที่มีทั้งคนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ และคนภายในคณะแพทยศาสตร์ศิริราชพยาบาลอยู่ในโครงการเดียวกัน เพื่ออ่านฐาน evidence ที่เกิดจากบริบทจริงของศิริราชให้ชัดขึ้น</p>
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
                                $expertPeopleTrendShape = $trendShape($expertTrendSummaryRows->pluck('people'));
                                $expertProjectTrendShape = $trendShape($expertTrendSummaryRows->pluck('projects'));
                                $expertClinicalInterpretation = str_contains($expertPeopleTrendShape, 'เพิ่ม')
                                    ? 'ข้อมูลสะท้อนว่ากลุ่มโครงการมีฐานผู้ร่วมจากฝั่ง clinical มากขึ้น ทำให้การอ่าน evidence เชื่อมกับโจทย์บริการจริงได้ชัดขึ้น'
                                    : (str_contains($expertPeopleTrendShape, 'ลด')
                                        ? 'ข้อมูลสะท้อนว่าฐานผู้ร่วมจากฝั่ง clinical ลดลงเมื่อเทียบกับช่วงต้นข้อมูล จึงควรดูต่อว่าการลดลงมาจากข้อมูลยังไม่ครบหรือจากจำนวนโครงการที่ลดลงจริง'
                                        : 'ข้อมูลยังไม่ชี้การเปลี่ยนแปลงของฐานผู้ร่วมจากฝั่ง clinical อย่างชัดเจน จึงควรอ่านประกอบกับจำนวนโครงการและประเภท output');
                            @endphp
                            @if ($expertTrendRows->isNotEmpty())
                                <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,3fr)_minmax(360px,2fr)]">
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
                                        <div class="text-sm font-semibold text-indigo-950">Analysis Insight</div>
                                        <div class="mt-3 grid gap-3">
                                            <div class="rounded-xl bg-white/85 px-3 py-3">
                                                <div class="text-[11px] font-semibold text-indigo-700">เกิดอะไรขึ้น</div>
                                                <div class="mt-1 text-sm font-semibold leading-relaxed text-slate-900">
                                                    ปี {{ $expertTrendFirst?->year }} - {{ $expertTrendLast?->year }} แพทย์ศิริราชที่ร่วมในกลุ่มโครงการ {{ $expertPeopleTrendShape }} จาก {{ number_format((int) ($expertTrendFirst?->people ?? 0)) }} เป็น {{ number_format((int) ($expertTrendLast?->people ?? 0)) }} คน
                                                </div>
                                            </div>
                                            <div class="rounded-xl bg-white/85 px-3 py-3">
                                                <div class="text-[11px] font-semibold text-indigo-700">แปลว่าอะไร</div>
                                                <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                    จำนวนโครงการที่มีแพทย์ศิริราชร่วม {{ $expertProjectTrendShape }} ในช่วงเดียวกัน จึงควรอ่านจำนวนคนและจำนวนโครงการควบคู่กัน ไม่ใช่อ่านจากจำนวนคนเพียงอย่างเดียว
                                                </div>
                                            </div>
                                            <div class="rounded-xl bg-white/85 px-3 py-3">
                                                <div class="text-[11px] font-semibold text-indigo-700">จุดที่ควรอ่าน</div>
                                                <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                    {{ $expertClinicalInterpretation }}
                                                </div>
                                            </div>
                                            <div class="rounded-xl bg-white/70 px-3 py-3 text-xs leading-relaxed text-slate-500">
                                                {{ $analysisInsightRemark }}
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
                                                <div class="grid gap-3 lg:grid-cols-2">
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
                                    <div class="text-xs font-semibold uppercase tracking-wide text-orange-700">IMPACT</div>
                                    <h3 class="mt-1 text-xl font-semibold text-slate-900">Outputs contributing to Policy-Impact or Practice Change (No)</h3>
                                    <p class="mt-2 max-w-4xl text-sm leading-relaxed text-slate-700">
                                        จำนวนผลงานและรายละเอียดของผลงานที่มีส่วนในการผลักดันการเปลี่ยนแปลงในเชิงนโยบายหรือการปฏิบัติในระบบสุขภาพ โดยแบ่งตามประเภทผลงาน Research Paper and Journal Article / Change in Practices, Procedure, Policy / Reports and White Paper / Policy Recommendation
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                                <div class="policy-trend-panel">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900">Trend ตามปี</div>
                                            <div class="text-xs text-slate-500">จำนวน output ของ Research Paper and Journal Article / Change in Practices, Procedure, Policy / Reports and White Paper / Policy Recommendation</div>
                                        </div>
                                        <span class="policy-pill">{{ number_format((int) $policyImpactOutputs->sum()) }} รายการ</span>
                                    </div>
                                    <div class="mt-3 h-[360px]">
                                        <canvas id="chartPolicyImpactTrend"></canvas>
                                    </div>
                                    @if ($policyImpactTrendSummaryRows->isNotEmpty())
                                        <div class="mt-4 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-4">
                                            <div class="text-xl font-semibold text-indigo-950">Analysis Insight</div>
                                            <div class="mt-3 grid gap-3">
                                                <div class="rounded-xl bg-white/85 px-3 py-3">
                                                    <div class="text-[13px] font-semibold text-indigo-700">เกิดอะไรขึ้น</div>
                                                    <div class="mt-1 text-sm font-semibold leading-relaxed text-slate-900">
                                                        {{ $policyImpactTrendReading }}
                                                    </div>
                                                </div>
                                                <div class="rounded-xl bg-white/85 px-3 py-3">
                                                    <div class="text-[13px] font-semibold text-indigo-700">แปลว่าอะไร</div>
                                                    <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                        {{ $policyImpactMeaning }}
                                                    </div>
                                                </div>
                                                <div class="rounded-xl bg-white/85 px-3 py-3">
                                                    <div class="text-[13px] font-semibold text-indigo-700">สิ่งที่เกิดขึ้น</div>
                                                    <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                        {{ $policyImpactWhatHappened }}
                                                    </div>
                                                </div>
                                                @if ($incompleteCurrentYearNote)
                                                    <div class="rounded-xl bg-white/70 px-3 py-3 text-sm leading-relaxed text-slate-500">
                                                        {{ $incompleteCurrentYearNote }}
                                                    </div>
                                                @endif
                                                <div class="rounded-xl bg-white/70 px-3 py-3 text-xs leading-relaxed text-slate-500">
                                                    {{ $analysisInsightRemark }}
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
                                                </div>
                                                <div class="metric rounded-full border px-3 py-1 text-sm font-semibold" style="{{ $outputTypeStyle($code) }}">{{ number_format((int) ($policyImpactOutputs[$code] ?? 0)) }} รายการ</div>
                                            </div>
                                            <div class="policy-mini-list mt-3 space-y-2">
                                                @forelse (collect($policyImpactOutputItems[$code] ?? []) as $item)
                                                    @if (($isPublicSeminar ?? false) || ! $item->link)
                                                        <div class="policy-mini-link">
                                                            <div class="text-xs font-semibold leading-snug text-slate-900">{{ $item->title }}</div>
                                                            <div class="mt-1 text-[11px] text-slate-500">{{ $item->reference }} · {{ $item->source_label ?: '-' }} · ปี {{ $item->year ?: '-' }}</div>
                                                        </div>
                                                    @else
                                                        <a href="{{ $item->link }}" class="policy-mini-link">
                                                            <div class="text-xs font-semibold leading-snug text-slate-900">{{ $item->title }}</div>
                                                            <div class="mt-1 text-[11px] text-slate-500">{{ $item->reference }} · {{ $item->source_label ?: '-' }} · ปี {{ $item->year ?: '-' }}</div>
                                                        </a>
                                                    @endif
                                                @empty
                                                    <div class="rounded-lg border border-dashed border-slate-200 px-3 py-3 text-xs text-slate-500">ยังไม่มี output ในช่วงที่เลือก</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="mt-4 rounded-2xl border border-indigo-200 bg-white px-4 py-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">Theme output gap analysis</div>
                                        <div class="mt-1 text-xs text-slate-500">บรรยายตามหมวดว่าเด่นอะไร ขาดอะไร และส่งผลต่อ policy impact อย่างไร</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center rounded-full border border-slate-200 bg-white px-2 py-1" title="สีเขียว = Gemini rewrite สำเร็จ, สีแดง = ใช้ข้อความจากระบบ">
                                            <span class="h-2 w-2 rounded-full bg-red-500" data-theme-gap-ai-field="status-dot"></span>
                                        </div>
                                        <div class="policy-pill">{{ number_format($policyImpactThemeAnalysis->count()) }} ธีม</div>
                                    </div>
                                </div>
                                <div class="mt-3 rounded-xl bg-slate-50 px-4 py-4">
                                    <div class="whitespace-pre-line text-sm leading-relaxed text-slate-700" data-theme-gap-ai-field="paragraph">{{ $policyImpactThemeNarrativeParagraph }}</div>
                                </div>
                                <div class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-xs leading-relaxed text-slate-500">
                                    {{ $analysisInsightRemark }}
                                </div>
                            </div>
                    </div>
                </div>

                <div class="policy-axis-card mt-4">
                    <div class="contribution-card">
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                                    <div class="flex w-full flex-wrap items-start justify-between gap-4">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Validation</div>
                                            <div class="mt-1 text-xl font-semibold text-slate-900">Trends in Siriraj health professionals’ interest in research-to-policy work</div>
                                            <div class="mt-2 w-full whitespace-pre-line text-sm leading-relaxed text-slate-700">แนวโน้มคนภายในคณะแพทยศาสตร์ศิริราชพยาบาล (แพทย์, บุคลากรทางการแพทย์) ที่ให้ความสนใจทำงานด้านการขับเคลื่อนนโยบาย และระบบสุขภาพ (Health Policy, Health Sytem)

จำนวนโครงการวิจัยที่นำไปสู่การขับเคลื่อนนโยบาย ที่คนภายในคณะแพทยศาสตร์ศิริราชพยาบาล (แพทย์, บุคลากรทางการแพทย์)  เข้ามาเป็นส่วนร่วม โดยแบ่งตามประเภท Theme/เนื้อหาของงานวิจัย
Ageing and Frailty, Health System and HTA, Real-World Evidence and Clinical-Technical Cohorts</div>
                                            <div class="mt-4 w-full text-sm italic leading-relaxed text-slate-500">*Theme/เนื้อหา Rational Drug Use (RDU) และ Global Health อาจยังถูกมองเป็น Operational project ภายในคณะแพทยศาสตร์ศิริราชพยาบาล  มากกว่า Research model จึงยังไม่ถูกรวมในการวิเคราะห์</div>
                                        </div>
                                    </div>
                                </div>
                                @php
                                    $clinicalLedSubjectName = 'กลุ่ม Ageing and Frailty, Health Systems and HTA และ Real-World Evidence (RWE)';
                                    $clinicalLedTrendRowsForSummary = $clinicalLedPeopleByYear
                                        ->filter(fn ($row) => (int) ($row->year ?? 0) < (int) $currentYearBe)
                                        ->values();
                                    if ($clinicalLedTrendRowsForSummary->isEmpty()) {
                                        $clinicalLedTrendRowsForSummary = $clinicalLedPeopleByYear->values();
                                    }
                                    $clinicalLedFirstYearRow = $clinicalLedTrendRowsForSummary->first();
                                    $clinicalLedLastYearRow = $clinicalLedTrendRowsForSummary->last();
                                    $clinicalLedPeakYearRow = $clinicalLedTrendRowsForSummary->sortByDesc(fn ($row) => (int) ($row->people_count ?? 0))->first();
                                    $clinicalLedTopTheme = $clinicalLedIntegrationThemes->sortByDesc(fn ($row) => (int) ($row->people ?? 0))->first();
                                    $clinicalLedFirstCount = (int) ($clinicalLedFirstYearRow?->people_count ?? 0);
                                    $clinicalLedLastCount = (int) ($clinicalLedLastYearRow?->people_count ?? 0);
                                    $clinicalLedDelta = $clinicalLedLastCount - $clinicalLedFirstCount;
                                    $clinicalLedDeltaLabel = $clinicalLedDelta > 0 ? '+' . number_format($clinicalLedDelta) : number_format($clinicalLedDelta);
                                    $clinicalLedTotalTrendShape = $trendShape($clinicalLedTrendRowsForSummary->pluck('people_count'));
                                    $clinicalLedThemeTrendRows = collect($clinicalLedIntegrationTrend['years'] ?? [])
                                        ->values()
                                        ->map(fn ($year, $index) => (object) [
                                            'year' => (int) $year,
                                            'ageing' => (int) (collect($clinicalLedIntegrationTrend['ageing_people'] ?? [])->values()->get($index) ?? 0),
                                            'hta' => (int) (collect($clinicalLedIntegrationTrend['hta_people'] ?? [])->values()->get($index) ?? 0),
                                            'rwe' => (int) (collect($clinicalLedIntegrationTrend['rwe_people'] ?? [])->values()->get($index) ?? 0),
                                        ])
                                        ->filter(fn ($row) => (int) ($row->year ?? 0) < (int) $currentYearBe)
                                        ->values();
                                    if ($clinicalLedThemeTrendRows->isEmpty()) {
                                        $clinicalLedThemeTrendRows = collect($clinicalLedIntegrationTrend['years'] ?? [])
                                            ->values()
                                            ->map(fn ($year, $index) => (object) [
                                                'year' => (int) $year,
                                                'ageing' => (int) (collect($clinicalLedIntegrationTrend['ageing_people'] ?? [])->values()->get($index) ?? 0),
                                                'hta' => (int) (collect($clinicalLedIntegrationTrend['hta_people'] ?? [])->values()->get($index) ?? 0),
                                                'rwe' => (int) (collect($clinicalLedIntegrationTrend['rwe_people'] ?? [])->values()->get($index) ?? 0),
                                            ])
                                            ->values();
                                    }
                                    $clinicalLedThemeFirst = $clinicalLedThemeTrendRows->first();
                                    $clinicalLedThemeLast = $clinicalLedThemeTrendRows->last();
                                    $clinicalLedHtaTrendShape = $trendShape($clinicalLedThemeTrendRows->pluck('hta'));
                                    $clinicalLedRweTrendShape = $trendShape($clinicalLedThemeTrendRows->pluck('rwe'));
                                    $clinicalLedHtaInterpretation = str_contains($clinicalLedHtaTrendShape, 'เพิ่ม')
                                        ? 'ควบคู่กับการเติบโตของกลุ่ม HTA ในช่วงเวลาเดียวกัน'
                                        : (str_contains($clinicalLedHtaTrendShape, 'ลด')
                                            ? 'ในขณะที่กลุ่ม HTA ลดลงเมื่อเทียบกับช่วงต้นข้อมูล'
                                            : 'ในขณะที่กลุ่ม HTA ยังทรงตัวในภาพรวม');
                                    $clinicalLedRweInterpretation = str_contains($clinicalLedRweTrendShape, 'เพิ่ม')
                                        ? 'ขณะที่ RWE มีแนวโน้ม' . $clinicalLedRweTrendShape . 'ในช่วงหลัง สะท้อนการขยายตัวของงานที่เชื่อมโจทย์บริการสุขภาพเข้ากับ real-world evidence มากขึ้น ' . $clinicalLedHtaInterpretation
                                        : (str_contains($clinicalLedRweTrendShape, 'ลด')
                                            ? 'ขณะที่ RWE มีแนวโน้มลดลงเมื่อเทียบกับช่วงต้นข้อมูล จึงยังไม่ควรสรุปว่าเกิดการขยายตัวของงานที่เชื่อมโจทย์บริการสุขภาพเข้ากับ real-world evidence ในช่วงข้อมูลนี้'
                                            : 'ขณะที่ RWE ยังไม่เปลี่ยนแปลงชัดเจน จึงยังไม่เห็นการขยายตัวของงานที่เชื่อมโจทย์บริการสุขภาพเข้ากับ real-world evidence ในช่วงข้อมูลนี้');
                                    $clinicalLedRoleYears = collect($clinicalLedIntegrationTrend['years'] ?? [])->values();
                                    $clinicalLedRoleTrendSummaries = collect([
                                        'pi' => 'PI',
                                        'co_pi' => 'Co-PI',
                                        'co_research' => 'Co-Research',
                                    ])->map(function ($label, $key) use ($clinicalLedIntegrationRoleTrend, $clinicalLedIntegrationRoleStats, $clinicalLedRoleYears, $currentYearBe) {
                                        $roleStat = $clinicalLedIntegrationRoleStats->firstWhere('key', $key);
                                        $topTheme = $roleStat?->top_theme ?? null;
                                        $topThemeText = $topTheme
                                            ? ' เด่นที่ ' . ($topTheme->label ?? '-') . ' (' . number_format((int) ($topTheme->people ?? 0)) . ' คน)'
                                            : '';
                                        $rows = $clinicalLedRoleYears
                                            ->map(fn ($year, $index) => (object) [
                                                'year' => (int) $year,
                                                'value' => (int) (collect($clinicalLedIntegrationRoleTrend[$key] ?? [])->values()->get($index) ?? 0),
                                            ])
                                            ->filter(fn ($row) => (int) ($row->year ?? 0) < (int) $currentYearBe)
                                            ->values();
                                        if ($rows->isEmpty()) {
                                            $rows = $clinicalLedRoleYears
                                                ->map(fn ($year, $index) => (object) [
                                                    'year' => (int) $year,
                                                    'value' => (int) (collect($clinicalLedIntegrationRoleTrend[$key] ?? [])->values()->get($index) ?? 0),
                                                ])
                                                ->values();
                                        }

                                        $first = $rows->first();
                                        $last = $rows->last();
                                        return (object) [
                                            'label' => $label,
                                            'summary' => $label . ' จาก ' . number_format((int) ($first?->value ?? 0)) . ' เป็น ' . number_format((int) ($last?->value ?? 0)) . ' คน' . $topThemeText,
                                            'delta' => (int) ($last?->value ?? 0) - (int) ($first?->value ?? 0),
                                        ];
                                    })->values();
                                    $clinicalLedGrowingRoles = $clinicalLedRoleTrendSummaries
                                        ->filter(fn ($row) => (int) ($row->delta ?? 0) > 0)
                                        ->pluck('label')
                                        ->values();
                                    $clinicalLedRoleExpansionSignal = $clinicalLedGrowingRoles->count() >= 2
                                        ? 'ภาพรวมแสดงว่าการมีส่วนร่วมขยายในหลายบทบาท ได้แก่ ' . $formatPolicyList($clinicalLedGrowingRoles)
                                        : ($clinicalLedGrowingRoles->count() === 1
                                            ? 'การเปลี่ยนแปลงเด่นอยู่ที่บทบาท ' . $clinicalLedGrowingRoles->first()
                                            : 'ยังไม่เห็นการขยายตัวของบทบาทผู้ร่วมโครงการอย่างชัดเจนในช่วงข้อมูลนี้');
                                @endphp

                                <div class="clinical-led-layout mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_430px]">
                                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <div class="text-sm font-semibold text-slate-900">Trend กลุ่มเนื้อหา Ageing and Frailty, Health Systems and HTA และ Real-World Evidence (RWE) ตามปี</div>
                                                <div class="mt-1 text-xs text-slate-500">แนวโน้มคนภายในคณะแพทยศาสตร์ศิริราชพยาบาล (แพทย์, บุคลากรทางการแพทย์) ที่ให้ความสนใจทำงานด้านการขับเคลื่อนนโยบาย และระบบสุขภาพ (Health Policy, Health Sytem)</div>
                                            </div>
                                            <span class="policy-pill">Ageing and Frailty / Health Systems and HTA / Real-World Evidence (RWE)</span>
                                        </div>
                                        <div class="clinical-led-chart-scroll">
                                            <div class="clinical-led-chart-inner">
                                                <canvas id="chartClinicalLedIntegrationTrend" class="block h-full w-full"></canvas>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-4">
                                        <div class="text-xl font-semibold text-indigo-950">Analysis Insight</div>
                                        <div class="mt-3 grid gap-3">
                                            <div class="rounded-xl bg-white/90 px-3 py-3">
                                        <div class="text-[13px] font-semibold text-indigo-700">ทิศทางหลัก</div>
                                        <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                    {{ $formatPolicyYearRange($clinicalLedFirstYearRow?->year, $clinicalLedLastYearRow?->year) }} โครงการที่มีทั้งคนในหน่วยวิจัยฯ และคนภายในคณะฯ ร่วมกันใน{{ $clinicalLedSubjectName }} {{ $clinicalLedTotalTrendShape }} จาก {{ number_format($clinicalLedFirstCount) }} เป็น {{ number_format($clinicalLedLastCount) }} คน จุดสูงสุดปี {{ $clinicalLedPeakYearRow?->year ?: '-' }} ที่ {{ number_format((int) ($clinicalLedPeakYearRow?->people_count ?? 0)) }} คน โดยกลุ่มสะสมสูงสุดคือ {{ $clinicalLedTopTheme?->label ?? '-' }} {{ number_format((int) ($clinicalLedTopTheme?->people ?? 0)) }} คน
                                        </div>
                                    </div>
                                            <div class="rounded-xl bg-white/90 px-3 py-3">
                                                <div class="text-[13px] font-semibold text-indigo-700">บทบาทที่เข้ามาร่วม</div>
                                                <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                    {{ $clinicalLedRoleTrendSummaries->pluck('summary')->implode(', ') }}. {{ $clinicalLedRoleExpansionSignal }}
                                                </div>
                                            </div>
                                            <div class="rounded-xl bg-white/90 px-3 py-3">
                                        <div class="text-[13px] font-semibold text-indigo-700">สิ่งที่เกิดขึ้น</div>
                                        <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                                    ปี {{ $clinicalLedPeakYearRow?->year ?: '-' }} เป็นจุดสูงสุดของช่วงข้อมูล เฉพาะโครงการที่มีทั้งคนในหน่วยวิจัยฯ และคนภายในคณะแพทยศาสตร์ศิริราชพยาบาลร่วมกัน โดยมีผู้เกี่ยวข้อง {{ number_format((int) ($clinicalLedPeakYearRow?->people_count ?? 0)) }} คนใน{{ $clinicalLedSubjectName }} {{ $clinicalLedRweInterpretation }}
                                        </div>
                                    </div>
                                            @if ($incompleteCurrentYearNote)
                                                <div class="rounded-xl bg-white/70 px-3 py-3 text-sm leading-relaxed text-slate-500">
                                                    {{ $incompleteCurrentYearNote }}
                                                </div>
                                            @endif
                                            <div class="rounded-xl bg-white/70 px-3 py-3 text-xs leading-relaxed text-slate-500">
                                                {{ $analysisInsightRemark }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                </div>


            </section>

            <section class="fade-up panel section-panel section-panel--slate nature-section p-5 md:p-6">
                @php
                    $nature = $projectNatureAnalysis ?? [];
                    $natureThemes = collect(data_get($nature, 'theme_rows', []));
                    $natureTypes = collect(data_get($nature, 'type_rows', []));
                    $natureTotal = (int) data_get($nature, 'project_total', 0);
                    $topNatureThemes = $natureThemes->take(3);
                    $maxNatureCount = max(1, (int) $natureThemes->max('count'));
                    $aiNaturePayload = is_array($aiProjectNatureInsight?->payload ?? null) ? $aiProjectNatureInsight->payload : [];
                    $aiInterestClusters = collect(data_get($aiNaturePayload, 'interest_clusters', []))
                        ->filter(fn ($cluster) => trim((string) data_get($cluster, 'name')) !== '')
                        ->values();
                    $aiOutputAlignment = collect(data_get($aiNaturePayload, 'output_alignment', []))
                        ->filter(fn ($row) => trim((string) data_get($row, 'theme')) !== '')
                        ->values();
                    $aiSectionCards = collect(data_get($aiNaturePayload, 'section_cards', []))
                        ->filter(fn ($card) => trim((string) data_get($card, 'theme_key')) !== '' || trim((string) data_get($card, 'label')) !== '')
                        ->values();
                    $aiLowQualityPhrases = [
                        'หน่วยงานได้ดำเนินการศึกษาและพัฒนา',
                        'งานวิจัยนี้เน้นที่การสร้างหลักฐานทางวิชาการ',
                        'ผลงานวิชาการเชิงระบบและการนำเสนอผลงานทางวิชาการเป็นหลัก พร้อมกับรายงานวิชาการที่มีความหลากหลาย',
                        'ควรพัฒนา output ประเภทข้อเสนอเชิงนโยบายเพื่อสนับสนุนการเปลี่ยนแปลงจริง',
                        'แต่ยังขาดข้อเสนอเชิงนโยบายเพื่อสนับสนุนการเปลี่ยนแปลงจริง',
                    ];
                    $aiSectionCardTexts = $aiSectionCards
                        ->map(fn ($card) => collect(['work_done', 'reading', 'output_direction', 'next_step', 'evidence_note'])
                            ->map(fn ($field) => trim((string) data_get($card, $field, '')))
                            ->filter()
                            ->implode(' '))
                        ->filter()
                        ->values();
                    $aiSectionCardsAreLowQuality = collect($aiLowQualityPhrases)
                        ->contains(fn ($phrase) => $aiSectionCardTexts->filter(fn ($text) => Str::contains($text, $phrase))->count() >= 3);
                    if ($aiSectionCardsAreLowQuality) {
                        $aiSectionCards = collect();
                    }
                    $hasAiProjectOutputInsight = $aiSectionCards->isNotEmpty() || $aiInterestClusters->isNotEmpty() || $aiOutputAlignment->isNotEmpty();
                    $aiNatureSummary = trim((string) data_get($aiNaturePayload, 'summary', ''));
                    $hasCjkText = static fn ($text): bool => preg_match('/[\x{3040}-\x{30ff}\x{3400}-\x{9fff}\x{f900}-\x{faff}]/u', (string) $text) === 1;
                    if ($hasCjkText($aiNatureSummary)) {
                        $aiNatureSummary = '';
                    }
                    $aiNatureGeneratedAt = $aiProjectNatureInsight?->generated_at?->timezone('Asia/Bangkok')->format('d/m/Y H:i');
                    $normalizeNatureText = static fn ($text): string => Str::of((string) $text)->lower()->replace(['-', '/', '&', '–', '—'], ' ')->squish()->toString();
                    $natureAiKeyFor = static function ($name) use ($normalizeNatureText): ?string {
                        $text = $normalizeNatureText($name);
                        return match (true) {
                            Str::contains($text, ['aging', 'frailty', 'ผู้สูงอายุ', 'เปราะบาง', 'geriatric']) => 'aging',
                            Str::contains($text, ['hta', 'economics', 'economic', 'คุ้มค่า', 'ต้นทุน', 'reimbursement', 'สิทธิประโยชน์']) => 'hta',
                            Str::contains($text, ['access', 'population', 'equity', 'uhc', 'pmac', 'เข้าถึง', 'ประชากร', 'ความเป็นธรรม']) => 'access_population',
                            Str::contains($text, ['device', 'innovation', 'implant', 'รากฟัน', 'กระดูกเทียม', 'เท้าเทียม', 'นวัตกรรม']) => 'device_innovation',
                            Str::contains($text, ['data', 'rwe', 'real world', 'cohort', 'registry', 'ฐานข้อมูล']) => 'data_rwe',
                            Str::contains($text, ['ai', 'digital', 'telemedicine', 'ปัญญาประดิษฐ์', 'ดิจิทัล']) => 'ai_digital',
                            Str::contains($text, ['drug', 'rdu', 'medication', 'prescribing', 'ใช้ยา']) => 'drug_rdu',
                            default => null,
                        };
                    };
                    $aiNatureClusterByKey = $aiInterestClusters
                        ->mapWithKeys(function ($cluster) use ($natureAiKeyFor) {
                            $key = $natureAiKeyFor(trim((string) data_get($cluster, 'name', '')));
                            if (!$key) {
                                $key = collect(data_get($cluster, 'keywords', []))
                                    ->map(fn ($keyword) => $natureAiKeyFor($keyword))
                                    ->filter()
                                    ->first();
                            }

                            return $key ? [$key => $cluster] : [];
                        });
                    $aiNatureAlignmentByKey = $aiOutputAlignment
                        ->mapWithKeys(function ($row) use ($natureAiKeyFor) {
                            $key = $natureAiKeyFor(trim((string) data_get($row, 'theme', '')));
                            if (!$key) {
                                $key = $natureAiKeyFor(trim((string) data_get($row, 'project_signal', '')));
                            }

                            return $key ? [$key => $row] : [];
                        });
                    $aiNatureSectionCardByKey = $aiSectionCards
                        ->mapWithKeys(function ($card) use ($natureAiKeyFor) {
                            $key = trim((string) data_get($card, 'theme_key', ''));
                            if ($key === '') {
                                $key = $natureAiKeyFor(trim((string) data_get($card, 'label', ''))) ?? '';
                            }

                            return $key !== '' ? [$key => $card] : [];
                        });
                    $aiNatureText = static function ($value) use ($hasCjkText): string {
                        $text = trim((string) $value);
                        return $text !== '' && !$hasCjkText($text) ? $text : '';
                    };
                    $aiNatureList = static function ($value) use ($aiNatureText): array {
                        return collect(is_array($value) ? $value : [])
                            ->map(fn ($item) => $aiNatureText($item))
                            ->filter()
                            ->take(7)
                            ->values()
                            ->all();
                    };
                    $natureBubbleLayout = [
                        'aging' => ['x' => 31, 'y' => 50, 'r' => 25, 'bg' => '#bae6fd', 'border' => '#38bdf8', 'text' => '#0c4a6e'],
                        'hta' => ['x' => 67, 'y' => 50, 'r' => 25, 'bg' => '#fed7aa', 'border' => '#fb923c', 'text' => '#7c2d12'],
                        'access_population' => ['x' => 49, 'y' => 73, 'r' => 17, 'bg' => '#bbf7d0', 'border' => '#4ade80', 'text' => '#14532d'],
                        'device_innovation' => ['x' => 49, 'y' => 28, 'r' => 11, 'bg' => '#fef08a', 'border' => '#eab308', 'text' => '#713f12'],
                        'data_rwe' => ['x' => 35, 'y' => 30, 'r' => 8.6, 'bg' => '#cbd5e1', 'border' => '#94a3b8', 'text' => '#334155'],
                        'ai_digital' => ['x' => 63, 'y' => 30, 'r' => 8, 'bg' => '#ddd6fe', 'border' => '#a78bfa', 'text' => '#4c1d95'],
                        'drug_rdu' => ['x' => 30, 'y' => 73, 'r' => 7.2, 'bg' => '#fecdd3', 'border' => '#fb7185', 'text' => '#881337'],
                    ];
                @endphp

                <div class="nature-shell rounded-[28px] border border-slate-200 bg-white p-4 shadow-[0_14px_35px_rgba(15,23,42,.05)] md:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="section-tag section-tag--teal">Research Nature Analysis</div>
                            <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-950 md:text-3xl">ความสนใจเชิงเนื้อหาของหน่วย</h2>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 md:grid-cols-4">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <div class="text-3xl font-semibold leading-none text-slate-950">{{ number_format($natureTotal) }}</div>
                            <div class="mt-1 text-xs font-semibold text-slate-500">โครงการทั้งหมด</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <div class="text-3xl font-semibold leading-none text-slate-950">{{ number_format($natureThemes->count()) }}</div>
                            <div class="mt-1 text-xs font-semibold text-slate-500">กลุ่มความสนใจ</div>
                        </div>
                        @foreach ($topNatureThemes->take(2) as $theme)
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-3xl font-semibold leading-none text-slate-950">{{ number_format((int) data_get($theme, 'count', 0)) }}</div>
                                <div class="mt-1 text-xs font-semibold text-slate-500">{{ data_get($theme, 'short_label') }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="nature-layout mt-5 grid gap-6 lg:grid-cols-[minmax(520px,620px)_minmax(0,1fr)] lg:items-stretch">
                        <div class="nature-rail relative lg:self-stretch">
                            <div class="nature-sticky rounded-[26px] border border-slate-200 bg-slate-50 p-4 shadow-sm md:p-5">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-base font-semibold text-slate-950">ภาพรวม</div>
                                        <div class="mt-1 text-xs text-slate-500">ภาพรวมความสนใจจากชื่อโครงการทั้งหมด</div>
                                    </div>
                                    <div class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm">{{ number_format($natureTotal) }} projects</div>
                                </div>
                                <div class="nature-map relative mx-auto mt-5 aspect-square min-h-[340px] max-w-[620px] overflow-hidden rounded-[40px] bg-gradient-to-br from-white via-slate-50 to-slate-100 p-4 sm:min-h-[460px] lg:min-h-[520px]" data-nature-map>
                                    <div class="absolute inset-6 rounded-full border border-slate-200/80 bg-white/60 shadow-inner"></div>
                                    @foreach ($natureThemes as $theme)
                                        @php
                                            $count = (int) data_get($theme, 'count', 0);
                                            $layout = $natureBubbleLayout[data_get($theme, 'key')] ?? ['x' => 50, 'y' => 50, 'r' => 10, 'bg' => '#cbd5e1', 'border' => '#64748b', 'text' => '#0f172a'];
                                            $fontSize = $layout['r'] >= 20 ? 'text-[15px]' : ($layout['r'] >= 11 ? 'text-[12px]' : 'text-[10px]');
                                        @endphp
                                        <a href="#nature-card-{{ data_get($theme, 'key') }}" class="nature-bubble group absolute grid place-items-center rounded-full text-center shadow-[0_14px_30px_rgba(15,23,42,.10)] transition duration-300 hover:z-20 hover:scale-[1.035] hover:shadow-[0_18px_42px_rgba(15,23,42,.18)]" data-nature-bubble="{{ data_get($theme, 'key') }}" data-nature-x="{{ $layout['x'] }}" data-nature-y="{{ $layout['y'] }}" style="left: {{ $layout['x'] }}%; top: {{ $layout['y'] }}%; width: {{ $layout['r'] * 2 }}%; aspect-ratio: 1; transform: translate(-50%, -50%); background: {{ $layout['bg'] }}; border: 1px solid {{ $layout['border'] }}; color: {{ $layout['text'] }};" title="{{ data_get($theme, 'label') }} · {{ number_format($count) }} โครงการ">
                                            <div class="nature-bubble__inner px-2">
                                                <div class="{{ $fontSize }} font-bold leading-tight">{{ data_get($theme, 'short_label') }}</div>
                                                <div class="mt-1 text-[11px] font-bold opacity-85">{{ number_format($count) }} projects</div>
                                            </div>
                                            <span class="nature-bubble__callout">
                                                <span class="nature-bubble__callout-title">{{ data_get($theme, 'short_label') }}</span>
                                                <span class="nature-bubble__callout-count">{{ number_format($count) }} projects</span>
                                            </span>
                                        </a>
                                    @endforeach
                                    <div class="nature-callout-layer" aria-hidden="true">
                                        @foreach ($natureThemes as $theme)
                                            @php
                                                $count = (int) data_get($theme, 'count', 0);
                                            @endphp
                                            <div class="nature-map-callout" data-nature-callout="{{ data_get($theme, 'key') }}">
                                                <div class="nature-bubble__callout-title">{{ data_get($theme, 'short_label') }}</div>
                                                <div class="nature-bubble__callout-count">{{ number_format($count) }} projects</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4">
                            <div class="rounded-[24px] bg-slate-50 p-5">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ $hasAiProjectOutputInsight ? 'AI policy translation reading' : 'Portfolio reading' }}</div>
                                <div class="mt-2 text-xl font-semibold leading-snug text-slate-950">{{ $aiNatureSummary !== '' ? $aiNatureSummary : data_get($nature, 'headline') }}</div>
                            </div>

                            @foreach ($natureThemes as $theme)
                                @php
                                    $themeKey = (string) data_get($theme, 'key');
                                    $aiCard = $aiNatureSectionCardByKey->get($themeKey, []);
                                    $displayLabel = $aiNatureText(data_get($aiCard, 'label')) ?: data_get($theme, 'label');
                                    $displayKeywords = $aiNatureList(data_get($aiCard, 'keywords'));
                                    if (empty($displayKeywords)) {
                                        $displayKeywords = collect(data_get($theme, 'keywords', []))->take(5)->values()->all();
                                    }
                                    $displayWorkDone = $aiNatureText(data_get($aiCard, 'work_done')) ?: data_get($theme, 'work_done');
                                    $displayReading = $aiNatureText(data_get($aiCard, 'reading')) ?: data_get($theme, 'reading');
                                    $displayOutputDirection = $aiNatureText(data_get($aiCard, 'output_direction')) ?: data_get($theme, 'output_direction');
                                    $displayNextStep = $aiNatureText(data_get($aiCard, 'next_step')) ?: data_get($theme, 'next_step');
                                    $displayEvidenceNote = $aiNatureText(data_get($aiCard, 'evidence_note'));
                                @endphp
                                <div id="nature-card-{{ data_get($theme, 'key') }}" class="nature-card scroll-mt-24 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm" data-nature-card="{{ data_get($theme, 'key') }}">
                                    <div>
                                        <div class="min-w-0">
                                            <div class="text-lg font-semibold leading-snug text-slate-950">{{ $displayLabel }}</div>
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                @foreach ($displayKeywords as $keyword)
                                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">{{ $keyword }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 grid gap-5 lg:grid-cols-[0.85fr_1.15fr]">
                                        <div class="border-t border-slate-200 pt-4">
                                            <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">สิ่งที่เกิดขึ้น</div>
                                            <div class="mt-2 text-sm leading-relaxed text-slate-700">{{ $displayWorkDone }}</div>
                                            <div class="mt-3 text-sm leading-relaxed text-slate-500">{{ $displayReading }}</div>
                                            @if ($displayEvidenceNote !== '')
                                                <div class="mt-3 rounded-2xl bg-slate-50 px-3 py-2 text-xs leading-relaxed text-slate-500">{{ $displayEvidenceNote }}</div>
                                            @endif
                                        </div>
                                        <div class="border-t border-teal-200 pt-4">
                                            <div class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">ทิศทางของ output</div>
                                            <div class="mt-2 text-sm leading-relaxed text-slate-700">{{ $displayOutputDirection }}</div>
                                        </div>
                                        <div class="border-t border-amber-200 bg-amber-50/50 px-4 py-3 lg:col-span-2">
                                            <div class="text-xs font-semibold uppercase tracking-[0.14em] text-amber-700">การต่อยอด</div>
                                            <div class="mt-2 text-sm leading-relaxed text-slate-700">{{ $displayNextStep }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="fade-up panel section-panel section-panel--slate p-5 md:p-6">
                @php
                    $fteSkills = $fteSkillAnalysis ?? [];
                    $fteSkillGroups = collect(data_get($fteSkills, 'grouped_task_rows', []));
                    $fteRadarRows = collect(data_get($fteSkills, 'radar_rows', []));
                    $fteRadarChartInsight = (string) data_get($fteSkills, 'radar_chart_insight', '');
                    $fteRadarInsights = collect(data_get($fteSkills, 'radar_insights', []));
                    $fteCapacityPositionRows = collect(data_get($fteSkills, 'capacity_position_rows', []));
                    $fteCapacitySkillRows = collect(data_get($fteSkills, 'capacity_skill_rows', []));
                    $fteCapacityYear = data_get($fteSkills, 'capacity_year');
                    $fteCapacityYearLabel = filled($fteCapacityYear) ? (string) $fteCapacityYear : 'ปีท้าย';
                    $fteCapacityYearSkillRows = collect(data_get($fteSkills, 'capacity_year_skill_rows', []));
                    $fteCapacityYearTotal = (int) data_get($fteSkills, 'capacity_year_total', 0);
                    $fteCapacityYearBorrowedTotal = (int) data_get($fteSkills, 'capacity_year_borrowed_total', 0);
                    $fteCapacityTotal = (int) data_get($fteSkills, 'capacity_total', 0);
                    $fteCapacitySkillBorrowedTotal = (int) data_get($fteSkills, 'capacity_skill_borrowed_total', 0);
                    $fteCapacityRegularTotal = (int) data_get($fteSkills, 'capacity_regular_total', 0);
                    $fteCapacityBorrowedTotal = (int) data_get($fteSkills, 'capacity_borrowed_total', 0);
                    $fteGroupClass = static function ($tone): string {
                        return match ((string) $tone) {
                            'scientific' => 'fte-capability-group--scientific',
                            'management' => 'fte-capability-group--management',
                            'it' => 'fte-capability-group--it',
                            'logistics' => 'fte-capability-group--logistics',
                            default => '',
                        };
                    };
                    $fteGroupSummary = static function ($key): string {
                        return match ((string) $key) {
                            'scientific' => 'ทักษะแกนวิชาการตั้งแต่ตั้งโจทย์ ทบทวนหลักฐาน เก็บและวิเคราะห์ข้อมูล ไปจนถึงเขียนและเผยแพร่ผลงาน',
                            'management' => 'ทักษะจัดการโครงการ เอกสารกำกับ ทุน IRB การประสาน stakeholder และการปิดงานให้เดินต่อได้',
                            'it_communication' => 'ทักษะสื่อสาร สร้างสื่อ ระบบข้อมูล โปรแกรม และ technical support ที่ช่วยให้งานวิจัยใช้งานได้จริง',
                            'logistics_admin' => 'งานสนับสนุนด้านระบบ เอกสาร บุคลากร การจัดประชุม และงานปฏิบัติการที่ทำให้ระบบทำงานต่อเนื่อง',
                            default => 'กลุ่มทักษะจากภาระงาน FTE',
                        };
                    };
                    $fteGroupNote = static function ($key): string {
                        return match ((string) $key) {
                            'scientific' => 'Scientific',
                            'management' => 'Management',
                            'it_communication' => 'IT + Communication',
                            'logistics_admin' => 'IT and Admin',
                            default => '',
                        };
                    };
                @endphp
                <div>
                    <div>
                        <div>
                            <div class="section-tag section-tag--slate">FTE Skill Map</div>
                            <h2 class="mt-2 text-xl font-semibold leading-tight text-slate-950 md:text-2xl">Skill ที่เกิดขึ้นในหน่วยงานจากภาระงาน FTE</h2>
                        </div>
                    </div>

                    @if ($fteRadarRows->isNotEmpty())
                        <div class="fte-radar-shell mt-5">
                            <div class="fte-radar-card">
                                <div class="fte-radar-card__head">
                                    <div>
                                        <div class="fte-radar-card__title">Skill profile จากภาระงาน FTE</div>
                                    </div>
                                    <div class="fte-radar-legend" aria-label="FTE skill radar legend">
                                        <span><i style="background:#2563eb;"></i>เวลาทำงาน (ชั่วโมง)</span>
                                        <span><i style="background:#f97316;"></i>ภาระงาน</span>
                                        <span><i style="background:#7c3aed;"></i>กำลังคน</span>
                                    </div>
                                </div>
                                <div class="fte-radar-chart-wrap">
                                    <canvas id="chartFteSkillRadar" class="block h-full w-full"></canvas>
                                </div>
                                <div class="mt-2 text-xs leading-relaxed text-slate-500">
                                    หมายเหตุ: กราฟนี้รวมจำนวนกำลังคนทั้งหมดที่เคยร่วมงานในช่วงเวลาที่แสดง รวมถึงบุคลากรที่ได้ลาออกแล้วด้วย
                                </div>
                                @if (filled($fteRadarChartInsight))
                                    <div class="fte-chart-insight">
                                        <div class="fte-radar-card__title">Analysis insight</div>
                                        <div class="fte-chart-insight__body mt-3">{{ $fteRadarChartInsight }}</div>
                                    </div>
                                @endif
                            </div>

                            <aside class="fte-radar-card fte-insight-card">
                                <div class="fte-radar-card__title">สิ่งที่เกิดขึ้น</div>
                                <div class="mt-3 grid gap-3">
                                    @foreach ($fteRadarInsights as $insight)
                                        <div class="fte-insight-item">{{ $insight }}</div>
                                    @endforeach
                                </div>
                            </aside>
                        </div>
                        @if ($fteCapacityPositionRows->isNotEmpty() || $fteCapacitySkillRows->isNotEmpty())
                            <div class="fte-capacity-tables mt-5" data-fte-capacity-tables>
                                @if ($fteCapacityPositionRows->isNotEmpty())
                                    <div class="fte-capacity-card" data-fte-capacity-left>
                                        <div class="fte-capacity-card__head">
                                            <div>
                                                <div class="fte-capacity-card__kicker">Position capacity</div>
                                                <div class="fte-capacity-card__title">อัตรากำลังจริง แบ่งตามชื่อตำแหน่ง</div>
                                            </div>
                                            <div class="fte-capacity-metrics">
                                                <div class="fte-capacity-metric">
                                                    <div class="fte-capacity-metric__label">ทั้งหมด</div>
                                                    <div class="fte-capacity-metric__value">{{ number_format($fteCapacityRegularTotal + $fteCapacityBorrowedTotal) }}</div>
                                                </div>
                                                <div class="fte-capacity-metric">
                                                    <div class="fte-capacity-metric__label">ยืมตัว</div>
                                                    <div class="fte-capacity-metric__value">{{ number_format($fteCapacityBorrowedTotal) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="fte-capacity-table-wrap">
                                            <table class="fte-capacity-table" style="min-width: 760px;">
                                            <thead>
                                                <tr>
                                                    <th class="fte-capacity-table__num" style="width:52px;"></th>
                                                    <th>ตำแหน่ง / สายงาน</th>
                                                    <th class="fte-capacity-table__num" style="width:96px;">ทั้งหมด</th>
                                                    <th class="fte-capacity-table__num" style="width:96px;">ยืมตัวมา</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($fteCapacityPositionRows as $row)
                                                    @php
                                                        $regularPeople = (int) ($row->people ?? 0);
                                                        $borrowedPeople = (int) ($row->borrowed_people ?? 0);
                                                        $totalPeople = $regularPeople + $borrowedPeople;
                                                    @endphp
                                                    <tr>
                                                        <td class="fte-capacity-table__num"><span class="fte-capacity-rank">{{ number_format($loop->iteration) }}</span></td>
                                                        <td data-label="ตำแหน่ง / สายงาน"><span class="fte-capacity-position__name">{{ $row->position_group }}</span></td>
                                                        <td class="fte-capacity-table__num" data-label="ทั้งหมด"><span class="fte-capacity-count">{{ number_format($totalPeople) }}</span></td>
                                                        <td class="fte-capacity-table__num" data-label="ยืมตัวมา">
                                                            @if ($borrowedPeople > 0)
                                                                <span class="fte-borrowed-badge">{{ number_format($borrowedPeople) }}</span>
                                                            @else
                                                                <span class="fte-borrowed-empty">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2">รวม</td>
                                                    <td class="fte-capacity-table__num" data-label="ทั้งหมด">{{ number_format($fteCapacityRegularTotal + $fteCapacityBorrowedTotal) }}</td>
                                                    <td class="fte-capacity-table__num" data-label="ยืมตัวมา">{{ $fteCapacityBorrowedTotal > 0 ? number_format($fteCapacityBorrowedTotal) : '-' }}</td>
                                                </tr>
                                            </tfoot>
                                            </table>
                                        </div>
                                        <div class="fte-capacity-position-cards">
                                            @foreach ($fteCapacityPositionRows as $row)
                                                @php
                                                    $regularPeople = (int) ($row->people ?? 0);
                                                    $borrowedPeople = (int) ($row->borrowed_people ?? 0);
                                                    $totalPeople = $regularPeople + $borrowedPeople;
                                                @endphp
                                                <article class="fte-capacity-position-card">
                                                    <div class="fte-capacity-position-card__head">
                                                        <span class="fte-capacity-rank">{{ number_format($loop->iteration) }}</span>
                                                        <div class="min-w-0">
                                                            <div class="fte-capacity-position__name">{{ $row->position_group }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="fte-capacity-position-card__metrics">
                                                        <div class="fte-capacity-position-card__metric">
                                                            <div class="fte-capacity-position-card__metric-label">ทั้งหมด</div>
                                                            <div class="fte-capacity-position-card__metric-value">{{ number_format($totalPeople) }}</div>
                                                        </div>
                                                        <div class="fte-capacity-position-card__metric">
                                                            <div class="fte-capacity-position-card__metric-label">ยืมตัว</div>
                                                            <div class="fte-capacity-position-card__metric-value">{{ $borrowedPeople > 0 ? number_format($borrowedPeople) : '-' }}</div>
                                                        </div>
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($fteCapacitySkillRows->isNotEmpty())
                                    <div class="fte-capacity-sticky-col" data-fte-capacity-right>
                                        <div class="fte-capacity-card fte-capacity-card--sticky" data-fte-capacity-follow>
                                            <div class="fte-capacity-card__head">
                                                <div>
                                                    <div class="fte-capacity-card__kicker">Skill set capacity</div>
                                                    <div class="fte-capacity-card__title">อัตรากำลังจริง แบ่งตาม Skill set</div>
                                                </div>
                                            </div>
                                            <div class="fte-capacity-table-wrap">
                                                <table class="fte-capacity-table" style="min-width: 520px;">
                                                <thead>
                                                    <tr>
                                                        <th style="width:52px;"></th>
                                                        <th>สายงาน</th>
                                                        <th class="fte-capacity-table__num" style="width:86px;">ทั้งหมด</th>
                                                        <th class="fte-capacity-table__num" style="width:74px;">%</th>
                                                        <th class="fte-capacity-table__num" style="width:86px;">ยืมตัวมา</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($fteCapacitySkillRows as $row)
                                                        <tr>
                                                            <td><span class="fte-capacity-rank">{{ $loop->iteration }}</span></td>
                                                            <td>
                                                                <div class="fte-capacity-skill-name">
                                                                    <span>{{ $row->skill_role }}</span>
                                                                </div>
                                                            </td>
                                                            <td class="fte-capacity-table__num" data-label="ทั้งหมด"><span class="fte-capacity-count">{{ number_format((int) ($row->people ?? 0)) }}</span></td>
                                                            <td class="fte-capacity-table__num" data-label="%">{{ number_format((float) ($row->pct ?? 0), 1) }}</td>
                                                            <td class="fte-capacity-table__num" data-label="ยืมตัวมา">
                                                                @if ((int) ($row->borrowed_people ?? 0) > 0)
                                                                    <span class="fte-borrowed-badge">{{ number_format((int) $row->borrowed_people) }}</span>
                                                                @else
                                                                    <span class="fte-borrowed-empty">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="2">รวม</td>
                                                        <td class="fte-capacity-table__num" data-label="ทั้งหมด">{{ number_format($fteCapacityTotal) }}</td>
                                                        <td class="fte-capacity-table__num" data-label="%">{{ number_format($fteCapacityTotal > 0 ? 100 : 0, 1) }}</td>
                                                        <td class="fte-capacity-table__num" data-label="ยืมตัวมา">{{ $fteCapacitySkillBorrowedTotal > 0 ? number_format($fteCapacitySkillBorrowedTotal) : '-' }}</td>
                                                    </tr>
                                                </tfoot>
                                                </table>
                                            </div>
                                            <div class="fte-capacity-table-note">
                                                หมายเหตุ: จำนวนทั้งหมดรวมคนยืมตัวไว้แล้ว และแสดงจำนวนยืมตัวแยกอีกคอลัมน์เพื่อให้เห็นสัดส่วนภายในแต่ละสายงาน
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="fte-taxonomy__empty mt-4">ยังไม่มีข้อมูล FTE ในช่วงที่เลือก</div>
                    @endif
                </div>
            </section>

            <section class="fade-up panel section-panel section-panel--indigo policy-shell p-5 md:p-6">
                <div class="policy-axis-card mt-4">
                    <div class="contribution-card">
                        <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-4">
                            <div class="flex w-full flex-wrap items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-cyan-700">Data Literacy Skill</div>
                                    <div class="mt-1 text-xl font-semibold text-slate-900">ทักษะและประสบการณ์การใช้ข้อมูลประเภท Real-World Data</div>
                                    <div class="mt-2 w-full text-sm leading-relaxed text-slate-700">จำนวนโครงการวิจัยที่มีประสบการณ์ใช้ Real-World Data, Primary Data หรือใช้ทั้งสองรูปแบบร่วมกัน เช่น NHSO, National Health Examination Survey, Si-Data plus, survey, interview, cohort และ intervention study</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_430px]">
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">Trend Data Source / Method ตามปี</div>
                                        <div class="mt-1 text-xs text-slate-500">นับจำนวนโครงการในแต่ละปีตามแหล่งที่มาและวิธีใช้ข้อมูล กดที่จุดบนกราฟเพื่อดูรายการโปรเจค</div>
                                    </div>
                                    <span class="policy-pill">RWD / Primary / Mixed</span>
                                </div>
                                <div class="mt-3 h-[360px]">
                                    <canvas id="chartDataLiteracyTrend" class="block h-full w-full"></canvas>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-4">
                                @php
                                    $dataLiteracyTrendRows = collect($policyDataSourceTrend['years'] ?? [])
                                        ->values()
                                        ->map(function ($year, $index) use ($policyDataSourceTrend) {
                                            $realWorld = (int) (collect($policyDataSourceTrend['series']['Real-World Data'] ?? [])->values()->get($index) ?? 0);
                                            $primary = (int) (collect($policyDataSourceTrend['series']['Primary Data'] ?? [])->values()->get($index) ?? 0);
                                            $combined = (int) (collect($policyDataSourceTrend['series']['Real-World Data + Primary Data'] ?? [])->values()->get($index) ?? 0);

                                            return (object) [
                                                'year' => (int) $year,
                                                'real_world' => $realWorld,
                                                'primary' => $primary,
                                                'combined' => $combined,
                                                'total' => $realWorld + $primary + $combined,
                                            ];
                                        })
                                        ->filter(fn ($row) => (int) ($row->year ?? 0) < (int) $currentYearBe)
                                        ->values();
                                    if ($dataLiteracyTrendRows->isEmpty()) {
                                        $dataLiteracyTrendRows = collect($policyDataSourceTrend['years'] ?? [])
                                            ->values()
                                            ->map(function ($year, $index) use ($policyDataSourceTrend) {
                                                $realWorld = (int) (collect($policyDataSourceTrend['series']['Real-World Data'] ?? [])->values()->get($index) ?? 0);
                                                $primary = (int) (collect($policyDataSourceTrend['series']['Primary Data'] ?? [])->values()->get($index) ?? 0);
                                                $combined = (int) (collect($policyDataSourceTrend['series']['Real-World Data + Primary Data'] ?? [])->values()->get($index) ?? 0);

                                                return (object) [
                                                    'year' => (int) $year,
                                                    'real_world' => $realWorld,
                                                    'primary' => $primary,
                                                    'combined' => $combined,
                                                    'total' => $realWorld + $primary + $combined,
                                                ];
                                            })
                                            ->values();
                                    }
                                    $dataLiteracyFirst = $dataLiteracyTrendRows->first();
                                    $dataLiteracyLast = $dataLiteracyTrendRows->last();
                                    $dataLiteracyPeak = $dataLiteracyTrendRows->sortByDesc(fn ($row) => (int) ($row->total ?? 0))->first();
                                    $dataLiteracyTotalTrendShape = $trendShape($dataLiteracyTrendRows->pluck('total'));
                                    $dataLiteracyRealWorldTrendShape = $trendShape($dataLiteracyTrendRows->pluck('real_world'));
                                    $dataLiteracyPrimaryTrendShape = $trendShape($dataLiteracyTrendRows->pluck('primary'));
                                    $dataLiteracyCombinedTrendShape = $trendShape($dataLiteracyTrendRows->pluck('combined'));
                                    $dataLiteracySourceInterpretation = $policyDataSourcePeak === 'Real-World Data'
                                        ? 'น้ำหนักหลักอยู่ที่การใช้ข้อมูลจากระบบบริการหรือข้อมูลที่เกิดขึ้นจริง'
                                        : ($policyDataSourcePeak === 'Primary Data'
                                            ? 'น้ำหนักหลักอยู่ที่การเก็บข้อมูลใหม่เพื่อให้ตอบโจทย์โครงการ'
                                            : ($policyDataSourcePeak === 'Real-World Data + Primary Data'
                                                ? 'น้ำหนักหลักอยู่ที่โครงการแบบผสมที่ใช้ทั้ง existing data และ newly collected data'
                                                : 'ยังไม่มีหมวด Data Source / Method ที่เด่นชัด'));
                                @endphp
                                <div class="text-xl font-semibold text-cyan-950">Analysis Insight</div>
                                <div class="mt-3 grid gap-3">
                                    <div class="rounded-xl bg-white/90 px-3 py-3">
                                        <div class="text-[13px] font-semibold text-cyan-700">เกิดอะไรขึ้น</div>
                                        <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                            ปี {{ $dataLiteracyFirst?->year ?: '-' }} - {{ $dataLiteracyLast?->year ?: '-' }} โครงการที่ระบุ Data Source / Method {{ $dataLiteracyTotalTrendShape }} จาก {{ number_format((int) ($dataLiteracyFirst?->total ?? 0)) }} เป็น {{ number_format((int) ($dataLiteracyLast?->total ?? 0)) }} โครงการ โดยปี {{ $dataLiteracyPeak?->year ?: '-' }} เป็นจุดสูงสุดที่ {{ number_format((int) ($dataLiteracyPeak?->total ?? 0)) }} โครงการ
                                        </div>
                                    </div>
                                    <div class="rounded-xl bg-white/90 px-3 py-3">
                                        <div class="text-[13px] font-semibold text-cyan-700">แปลว่าอะไร</div>
                                        <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                            Real-World Data คือข้อมูลผู้ป่วยที่มีอยู่แล้วจากระบบบริการสุขภาพ ส่วน Primary Data คือข้อมูลที่เก็บใหม่เพื่อการวิจัยโดยเฉพาะ และ Real-World Data + Primary Data คือโครงการที่ใช้ข้อมูลทั้งสองรูปแบบร่วมกัน ดังนั้น trend นี้ควรอ่านเป็นแนวทางการใช้ข้อมูลของกลุ่มโครงการ ไม่ใช่จำนวน output
                                        </div>
                                    </div>
                                    <div class="rounded-xl bg-white/90 px-3 py-3">
                                        <div class="text-[13px] font-semibold text-cyan-700">สิ่งที่เกิดขึ้น</div>
                                        <div class="mt-1 text-sm leading-relaxed text-slate-700">
                                            หมวดที่พบมากที่สุดคือ {{ $policyDataSourcePeak ?: '-' }} จำนวน {{ number_format($policyDataSourcePeakCount) }} โครงการ แปลว่า{{ $dataLiteracySourceInterpretation }} โดย Real-World Data {{ $dataLiteracyRealWorldTrendShape }}, Primary Data {{ $dataLiteracyPrimaryTrendShape }} และ Real-World Data + Primary Data {{ $dataLiteracyCombinedTrendShape }} ในช่วงเดียวกัน ขณะที่โครงการที่ยังไม่ระบุ Data Source / Method มี {{ number_format((int) ($policyImpact['data_source_missing_count'] ?? 0)) }} โครงการ
                                        </div>
                                    </div>
                                    @if ($incompleteCurrentYearNote)
                                        <div class="rounded-xl bg-white/70 px-3 py-3 text-sm leading-relaxed text-slate-500">
                                            {{ $incompleteCurrentYearNote }}
                                        </div>
                                    @endif
                                    <div class="rounded-xl bg-white/70 px-3 py-3 text-xs leading-relaxed text-slate-500">
                                        {{ $analysisInsightRemark }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="fade-up panel section-panel section-panel--indigo policy-shell p-5 md:p-6">
                <div class="policy-axis-card mt-4">
                    <div class="policy-section-title">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">People Contribution</h3>
                            <p class="mt-1 text-sm leading-relaxed text-slate-600">สรุปบุคลากรในกลุ่มโครงการ: PI, Co-PI, Co-Research และด้านที่มีส่วนร่วมบ่อย</p>
                        </div>
                        <span class="policy-pill">แสดง {{ number_format($policyPeopleHighlights->count()) }} คน</span>
                    </div>
                    <div class="policy-people-grid">
                        @forelse ($policyPeopleHighlights as $person)
                            <a href="{{ $person->link }}" class="policy-person-card">
                                <div class="flex items-start gap-3">
                                    <img src="{{ $person->profile_photo_url }}" alt="{{ $person->name }}" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'" class="h-12 w-12 rounded-2xl border border-slate-200 object-cover">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold leading-snug text-slate-950">{{ $person->name }}</div>
                                        @if ($person->position)
                                            <div class="mt-1 text-xs font-medium leading-snug text-slate-600">{{ $person->position }}</div>
                                        @endif
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <span class="policy-role-chip bg-indigo-50 text-indigo-700">PI {{ number_format((int) ($person->role_counts['pi'] ?? 0)) }}</span>
                                            <span class="policy-role-chip bg-teal-50 text-teal-700">Co-PI {{ number_format((int) ($person->role_counts['co_pi'] ?? 0)) }}</span>
                                            <span class="policy-role-chip bg-amber-50 text-amber-700">Co-Research {{ number_format((int) ($person->role_counts['co_research'] ?? 0)) }}</span>
                                        </div>
                                        <div class="mt-3 text-xs font-semibold leading-relaxed text-slate-700">เด่นด้าน: <span class="font-medium text-slate-600">{{ $person->top_theme }}</span></div>
                                        <div class="mt-1 text-xs leading-relaxed text-slate-500">{{ $person->trend_text }}</div>
                                    </div>
                                    <div class="shrink-0 min-w-[48px] text-right">
                                        <div class="metric text-base font-semibold leading-none text-slate-950">{{ number_format((int) $person->total_projects) }}</div>
                                        <div class="mt-1 text-[11px] font-medium leading-none text-slate-500">projects</div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 px-4 py-5 text-sm text-slate-500">ยังไม่มีข้อมูลบุคลากรในช่วงที่เลือก</div>
                        @endforelse
                    </div>
                </div>

                <div class="policy-axis-card mt-4">
                    <div class="policy-section-title">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">การผลักด้นของเเพทย์ในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพในด้านต่างๆ</h3>
                        </div>
                    </div>
                    @php
                        $doctorTimelineNodes = $doctorContributionTimeline->values();
                        $doctorTimelineNodeYears = $doctorTimelineNodes
                            ->pluck('year')
                            ->filter()
                            ->map(fn ($year) => (int) $year)
                            ->values();
                        $doctorTimelineStartYear = filled($selectedYearFrom)
                            ? (int) $selectedYearFrom
                            : (int) ($doctorTimelineNodeYears->min() ?: 0);
                        $doctorTimelineEndYear = filled($selectedYearTo)
                            ? (int) $selectedYearTo
                            : (int) ($doctorTimelineNodeYears->max() ?: 0);
                        $doctorTimelineYears = ($doctorTimelineStartYear > 0 && $doctorTimelineEndYear >= $doctorTimelineStartYear)
                            ? collect(range($doctorTimelineStartYear, $doctorTimelineEndYear))
                            : $doctorTimelineNodeYears->unique()->sort()->values();
                        $doctorTimelineThemes = $doctorTimelineNodes
                            ->pluck('theme')
                            ->filter()
                            ->unique()
                            ->sort()
                            ->values();
                        $doctorTimelineMap = $doctorTimelineNodes
                            ->mapWithKeys(fn ($node) => [((int) ($node->year ?? 0)) . '|' . trim((string) ($node->theme ?? '')) => $node]);
                    @endphp
                    <div class="doctor-lane-board">
                        <div class="doctor-lane-grid" style="--doctor-year-count: {{ max(1, $doctorTimelineYears->count()) }};">
                            @if ($doctorTimelineYears->isNotEmpty() && $doctorTimelineThemes->isNotEmpty())
                                <div class="doctor-lane-head">
                                    <div class="doctor-lane-theme">
                                        <div class="text-xs font-semibold text-slate-500">กลุ่มงาน</div>
                                    </div>
                                    @foreach ($doctorTimelineYears as $year)
                                        <div class="doctor-lane-year">{{ $year }}</div>
                                    @endforeach
                                </div>
                                @foreach ($doctorTimelineThemes as $theme)
                                    <div class="doctor-lane-row" style="{{ $projectTypeToneStyle($theme) }}">
                                        <div class="doctor-lane-theme">
                                            <div class="text-sm font-semibold leading-snug text-slate-900">{{ $theme }}</div>
                                        </div>
                                        @foreach ($doctorTimelineYears as $year)
                                            @php
                                                $node = $doctorTimelineMap->get(((int) $year) . '|' . trim((string) $theme));
                                                $doctors = collect($node->doctors ?? [])->values();
                                            @endphp
                                            <div class="doctor-lane-cell {{ $doctors->isEmpty() ? 'is-empty' : '' }}">
                                                @if ($doctors->isNotEmpty())
                                                    <div class="doctor-avatar-stack" title="{{ $doctors->pluck('name')->implode(', ') }}">
                                                        @foreach ($doctors as $doctor)
                                                            <img src="{{ $doctor->profile_photo_url }}" alt="{{ $doctor->name }}" title="{{ $doctor->name }}" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'">
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-sm text-slate-300">-</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @else
                                <div class="rounded-xl border border-dashed border-slate-200 px-4 py-5 text-sm text-slate-500">ยังไม่มีข้อมูลแพทย์ในช่วงที่เลือก</div>
                            @endif
                        </div>
                    </div>
                    @if ($doctorTimelineYears->isNotEmpty() && $doctorTimelineThemes->isNotEmpty())
                        <div class="doctor-mobile-timeline">
                            @foreach ($doctorTimelineThemes as $theme)
                                <div class="doctor-mobile-theme" style="{{ $projectTypeToneStyle($theme) }}">
                                    <div class="doctor-mobile-theme__head">
                                        <div class="text-sm font-semibold leading-snug text-slate-950">{{ $theme }}</div>
                                    </div>
                                    @foreach ($doctorTimelineYears as $year)
                                        @php
                                            $node = $doctorTimelineMap->get(((int) $year) . '|' . trim((string) $theme));
                                            $doctors = collect($node->doctors ?? [])->values();
                                        @endphp
                                        <div class="doctor-mobile-year">
                                            <div class="doctor-mobile-year__label">{{ $year }}</div>
                                            @if ($doctors->isNotEmpty())
                                                <div class="doctor-avatar-stack" title="{{ $doctors->pluck('name')->implode(', ') }}">
                                                    @foreach ($doctors as $doctor)
                                                        <img src="{{ $doctor->profile_photo_url }}" alt="{{ $doctor->name }}" title="{{ $doctor->name }}" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'">
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="doctor-mobile-empty">-</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4" data-doctor-role-panel>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-emerald-950">บทบาทของคนในหน่วยวิจัยฯ ตามกลุ่มเนื้อหา</div>
                                <div class="mt-1 text-xs text-emerald-700/80">สรุป PI, Co-PI และ Co-Research ของคนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ แยกตามหมวด 01-06</div>
                            </div>
                            <div class="doctor-role-tabs inline-flex rounded-xl border p-1" role="tablist" aria-label="เลือกบทบาท">
                                @foreach (['pi' => 'PI', 'co_pi' => 'Co-PI', 'co_research' => 'Co-Research'] as $roleKey => $roleLabel)
                                    @php
                                        $roleTable = $unitContributionRoleThemeTables->get($roleKey);
                                    @endphp
                                    <button type="button" class="doctor-role-tab rounded-lg px-3 py-1.5 text-xs font-semibold {{ $loop->first ? 'is-active' : '' }}" data-doctor-role-tab="{{ $roleKey }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                                        {{ $roleLabel }}
                                        <span class="ml-1 text-[11px] font-black">{{ number_format((int) ($roleTable?->people_count ?? 0)) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="doctor-role-floating" data-doctor-role-floating aria-label="เลือกบทบาทของคนในหน่วยวิจัย">
                            @foreach (['pi' => 'PI', 'co_pi' => 'Co-PI', 'co_research' => 'Co-Research'] as $roleKey => $roleLabel)
                                <button type="button" class="{{ $loop->first ? 'is-active' : '' }}" data-doctor-role-floating-tab="{{ $roleKey }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $roleLabel }}
                                </button>
                            @endforeach
                        </div>

                        @foreach (['pi' => 'PI', 'co_pi' => 'Co-PI', 'co_research' => 'Co-Research'] as $roleKey => $roleLabel)
                            @php
                                $roleTable = $unitContributionRoleThemeTables->get($roleKey);
                                $roleRows = collect($roleTable?->rows ?? []);
                                $roleYears = collect($roleTable?->years ?? []);
                            @endphp
                            <div class="doctor-role-table mt-3 {{ $loop->first ? '' : 'hidden' }}" data-doctor-role-table="{{ $roleKey }}">
                                <div class="doctor-lane-board unit-contribution-matrix rounded-xl border border-emerald-200 bg-white">
                                    <div class="doctor-lane-grid border-0" style="--doctor-year-count: {{ max(1, $roleYears->count()) }};">
                                        @if ($roleYears->isNotEmpty() && $roleRows->isNotEmpty())
                                            <div class="doctor-lane-head">
                                                <div class="doctor-lane-theme">
                                                    <div class="text-xs font-semibold text-emerald-700">กลุ่มงาน</div>
                                                </div>
                                                @foreach ($roleYears as $year)
                                                    <div class="doctor-lane-year text-emerald-700">{{ $year }}</div>
                                                @endforeach
                                            </div>
                                            @foreach ($roleRows as $row)
                                                <div class="doctor-lane-row" style="{{ $projectTypeToneStyle($row->theme) }}">
                                                    <div class="doctor-lane-theme">
                                                        <div class="text-sm font-semibold leading-snug text-slate-900">{{ $row->theme }}</div>
                                                        <div class="mt-1 text-[11px] font-medium text-slate-500">{{ number_format((int) $row->people_count) }} คน · {{ number_format((int) $row->project_count) }} โครงการ</div>
                                                    </div>
                                                    @foreach ($roleYears as $year)
                                                        @php
                                                            $cell = collect($row->by_year ?? [])->get((string) ((int) $year));
                                                            $people = collect($cell->people ?? [])->values();
                                                        @endphp
                                                        <div class="doctor-lane-cell {{ $people->isEmpty() ? 'is-empty' : '' }}">
                                                            @if ($people->isNotEmpty())
                                                                <div class="unit-cell-content">
                                                                    <div class="unit-cell-count">{{ number_format($people->count()) }} คน</div>
                                                                    <div class="doctor-avatar-stack unit-avatar-stack" title="{{ $people->pluck('name')->implode(', ') }}">
                                                                        @foreach ($people as $person)
                                                                            <img src="{{ $person->profile_photo_url ?: asset('images/default-avatar.png') }}" alt="{{ $person->name }}" title="{{ $person->name }}{{ (int) ($person->project_count ?? 0) > 0 ? ' · ' . number_format((int) $person->project_count) . ' โครงการ' : '' }}" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'">
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <span class="text-sm text-slate-300">-</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="rounded-xl border border-dashed border-emerald-200 px-4 py-5 text-sm text-slate-500">ยังไม่มีข้อมูลในบทบาท {{ $roleLabel }} สำหรับช่วงที่เลือก</div>
                                        @endif
                                    </div>
                                </div>
                                @if ($roleYears->isNotEmpty() && $roleRows->isNotEmpty())
                                    <div class="unit-mobile-role-timeline">
                                        @foreach ($roleRows as $row)
                                            <div class="doctor-mobile-theme" style="{{ $projectTypeToneStyle($row->theme) }}">
                                                <div class="doctor-mobile-theme__head">
                                                    <div class="text-sm font-semibold leading-snug text-slate-950">{{ $row->theme }}</div>
                                                    <div class="mt-1 text-[11px] font-medium text-slate-500">{{ number_format((int) $row->people_count) }} คน · {{ number_format((int) $row->project_count) }} โครงการ</div>
                                                </div>
                                                @foreach ($roleYears as $year)
                                                    @php
                                                        $cell = collect($row->by_year ?? [])->get((string) ((int) $year));
                                                        $people = collect($cell->people ?? [])->values();
                                                    @endphp
                                                    <div class="doctor-mobile-year">
                                                        <div class="unit-mobile-year-meta">
                                                            <div class="doctor-mobile-year__label text-emerald-700">{{ $year }}</div>
                                                            @if ($people->isNotEmpty())
                                                                <div class="unit-cell-count">{{ number_format($people->count()) }} คน</div>
                                                            @endif
                                                        </div>
                                                        @if ($people->isNotEmpty())
                                                            <div class="unit-mobile-avatar-grid" title="{{ $people->pluck('name')->implode(', ') }}">
                                                                @foreach ($people as $person)
                                                                    <img src="{{ $person->profile_photo_url ?: asset('images/default-avatar.png') }}" alt="{{ $person->name }}" title="{{ $person->name }}{{ (int) ($person->project_count ?? 0) > 0 ? ' · ' . number_format((int) $person->project_count) . ' โครงการ' : '' }}" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'">
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="doctor-mobile-empty">-</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            </section>

            <section class="fade-up panel section-panel section-panel--teal mt-8 p-5 md:p-6">
                <div class="section-tag section-tag--teal">Output Intelligence</div>
                <h2 class="max-w-[1500px] font-semibold text-slate-900" style="font-size: clamp(24px, 1.9vw, 32px); line-height: 1.42; letter-spacing: 0;">
                    ผลงานที่เกิดขึ้นในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ
                </h2>
            </section>

            <section class="fade-up panel section-panel section-panel--teal mt-4 p-5 md:p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <div class="section-tag section-tag--teal">Theme Output</div>
                        <h2 class="text-lg font-semibold text-slate-900">กลุ่มเนื้อหา  (6 กลุ่ม) จาก Output</h2>
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
                                                data-link="{{ ($isPublicSeminar ?? false) ? '' : ($item->link ?? '') }}"
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
                    <div class="mt-4 flex flex-1 items-start justify-center">
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
                                data-link="{{ ($isPublicSeminar ?? false) ? '' : ($row->modal_link ?? '') }}"
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
                    <div class="mt-4 flex flex-1 items-start justify-center">
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
                                data-link="{{ ($isPublicSeminar ?? false) ? '' : ($row->modal_link ?? '') }}"
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
                                    data-link="{{ ($isPublicSeminar ?? false) ? '' : ($row->modal_link ?? '') }}"
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
                @php
                    $networkingCommunicationFeatures = collect($networkingSummary['communication_features'] ?? []);
                    $networkingFeaturesByYear = $networkingCommunicationFeatures
                        ->groupBy(fn ($feature) => (string) ($feature->year_be ?: 'ไม่ระบุปี'))
                        ->sortKeysDesc();
                @endphp
                <section class="scene">
                    <article class="fade-up panel section-panel section-panel--emerald p-5 md:p-7">
                        <div>
                            <div class="section-tag section-tag--emerald">Networking</div>
                            <h2 class="max-w-6xl text-2xl font-semibold leading-tight text-slate-900 md:text-3xl">เครือข่ายและการเสริมศักยภาพ <span class="ml-1 text-lg font-medium text-slate-500">({{ number_format($networkingCount) }} รายการ)</span></h2>
                        </div>
                        <div class="network-kpi-grid mt-5">
                            <div class="network-kpi-card">
                                <div class="network-kpi-card__label">จำนวนคนภายนอกคณะแพทยศาสตร์ศิริราชพยาบาลที่เข้ามาเป็นส่วนร่วมในโครงการวิจัย</div>
                                <div class="network-kpi-card__value">{{ number_format((int) ($networkingSummary['external_people_count'] ?? 0)) }} คน</div>
                                <div class="network-kpi-card__sub">จาก {{ number_format((int) ($networkingSummary['external_project_count'] ?? 0)) }} โครงการ</div>
                                <div class="mt-2 text-xs font-semibold leading-relaxed text-slate-500">
                                    PI {{ number_format((int) (collect($networkingSummary['external_role_counts'] ?? [])->get('PI') ?? 0)) }}
                                    · Co-PI {{ number_format((int) (collect($networkingSummary['external_role_counts'] ?? [])->get('Co-PI') ?? 0)) }}
                                    · Co-Research {{ number_format((int) (collect($networkingSummary['external_role_counts'] ?? [])->get('Co-Research') ?? 0)) }}
                                </div>
                            </div>
                            <div class="network-kpi-card">
                                <div class="network-kpi-card__label">News / Article</div>
                                <div class="network-kpi-card__value">{{ number_format((int) ($networkingSummary['ea108_count'] ?? 0)) }}</div>
                                <div class="network-kpi-card__sub">Communication Materials</div>
                            </div>
                            <div class="network-kpi-card">
                                <div class="network-kpi-card__label">{{ $eaFullNameMap['EA114'] }}</div>
                                <div class="network-kpi-card__value">{{ number_format((int) ($networkingSummary['ea114_count'] ?? 0)) }}</div>
                                <div class="network-kpi-card__sub">{{ $eaFullNameMap['EA114'] }}</div>
                            </div>
                            <div class="network-kpi-card">
                                <div class="network-kpi-card__label">{{ $eaFullNameMap['EA115'] }}</div>
                                <div class="network-kpi-card__value">{{ number_format((int) ($networkingSummary['ea115_count'] ?? 0)) }}</div>
                                <div class="network-kpi-card__sub">{{ $eaFullNameMap['EA115'] }}</div>
                            </div>
                            <div class="network-kpi-card">
                                <div class="network-kpi-card__label">{{ $eaFullNameMap['EA117'] }}</div>
                                <div class="network-kpi-card__value">{{ number_format((int) ($networkingSummary['ea117_count'] ?? 0)) }}</div>
                                <div class="network-kpi-card__sub">{{ $eaFullNameMap['EA117'] }}</div>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                            <span class="rounded-full bg-slate-100 px-3 py-1.5">ปี {{ $networkingCoverageStartYear ?: '-' }}-{{ $networkingCoverageEndYear ?: '-' }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1.5">ปีสูงสุด {{ $networkingSummary['peak_year'] ?: '-' }} · {{ number_format((int) ($networkingSummary['peak_year_count'] ?? 0)) }} รายการ</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1.5">แสดง {{ number_format($networkingCommunicationFeatures->count()) }} ภาพ</span>
                        </div>
                        @if ($networkingFeaturesByYear->isNotEmpty())
                            <div class="mt-6">
                                @foreach ($networkingFeaturesByYear as $networkingYear => $yearFeatures)
                                    <div class="network-year-block">
                                        <div class="network-year-head">
                                            <div class="network-year-head__year">{{ $networkingYear }}</div>
                                            <div class="network-year-head__count">{{ number_format($yearFeatures->count()) }} รายการ</div>
                                        </div>
                                        <div class="network-feature-grid mt-4">
                                            @foreach ($yearFeatures as $feature)
                                                @php
                                                    $featureLink = trim((string) ($feature->external_link ?? ''));
                                                    $featureHost = $featureLink !== '' ? parse_url($featureLink, PHP_URL_HOST) : null;
                                                    $featureKind = trim((string) ($feature->content_kind ?? 'News'));
                                                    $featureTypeCode = trim((string) ($feature->feature_type_code ?? 'EA108')) ?: 'EA108';
                                                    $featureKindLabel = match ($featureTypeCode) {
                                                        'EA114' => $eaFullNameMap['EA114'] ?? 'Events / Forum',
                                                        'EA115' => $eaFullNameMap['EA115'] ?? 'Capacity Building / Knowledge Sharing',
                                                        'EA117' => $eaFullNameMap['EA117'] ?? 'Memorandum of Understanding (MOU)',
                                                        default => $featureKind,
                                                    };
                                                    $featurePreview = (array) ($feature->link_preview ?? []);
                                                    $featurePreviewId = (int) ($featurePreview['id'] ?? 0);
                                                    $featureProvider = trim((string) ($featurePreview['provider_name'] ?? ''));
                                                    $featurePreviewPath = trim((string) ($featurePreview['preview_image_path'] ?? ''));
                                                    $featurePreviewImage = trim((string) ($featurePreview['preview_image_url'] ?? ''));
                                                    $featureThumbnailRaw = trim((string) ($featurePreview['thumbnail_url'] ?? ''));
                                                    $featureThumbnailCandidate = $featurePreviewImage ?: $featureThumbnailRaw;
                                                    $featureThumbnailLower = Str::lower($featureThumbnailCandidate);
                                                    $shouldProxyPreviewImage = $featurePreviewId > 0 && (
                                                        Str::contains($featureThumbnailLower, 'googleusercontent.com/sitesv/')
                                                        || (Str::contains($featureThumbnailLower, 'si.mahidol.ac.th') && Str::contains($featureThumbnailLower, 'admin/news_images/'))
                                                    );
                                                    $featureThumbnail = $featurePreviewPath !== ''
                                                        ? '/storage/' . ltrim($featurePreviewPath, '/')
                                                        : ($shouldProxyPreviewImage ? route('outputs.link-previews.image', $featurePreviewId, false) : $featureThumbnailCandidate);
                                                    $featureTitle = trim((string) ($feature->clean_title ?? '')) ?: trim((string) ($feature->title ?? '')) ?: trim((string) ($featurePreview['title'] ?? ''));
                                                    $featureDescription = trim((string) ($featurePreview['description'] ?? ''));
                                                    if ($featureDescription === '') {
                                                        $featureDescription = trim((string) ($feature->modal_detail ?? ''));
                                                        $featureDescription = trim((string) preg_replace('/https?:\/\/[^\s<>"\'\]\)]+/iu', '', $featureDescription));
                                                        $featureDescription = trim((string) preg_replace('/\s+/u', ' ', $featureDescription));
                                                        $featureDescription = trim((string) preg_replace('/^(news|article)\s*:\s*/iu', '', $featureDescription));
                                                        if ($featureTitle !== '' && Str::startsWith(Str::lower($featureDescription), Str::lower($featureTitle))) {
                                                            $featureDescription = trim(Str::substr($featureDescription, Str::length($featureTitle)));
                                                        }
                                                        $featureDescription = Str::limit($featureDescription, 180);
                                                    }
                                                    $isYoutubeFeature = Str::contains(Str::lower((string) $featureHost), ['youtube.com', 'youtu.be']);
                                                    if ($isYoutubeFeature) {
                                                        $featureDescription = '';
                                                    }
                                                    $featureHasLink = $featureLink !== '';
                                                    $featureFallbackCode = $eaFullNameMap[$featureTypeCode] ?? $featureKindLabel;
                                                @endphp
                                                <a
                                                    href="{{ $featureHasLink ? $featureLink : '#' }}"
                                                    target="{{ $featureHasLink ? '_blank' : '_self' }}"
                                                    rel="{{ $featureHasLink ? 'noopener' : '' }}"
                                                    class="network-feature-card group {{ $featureHasLink ? '' : 'network-feature-card--no-link' }}"
                                                    @if (! $featureHasLink) aria-disabled="true" onclick="return false" @endif
                                                >
                                                    @if ($featureThumbnail !== '')
                                                        <div class="network-feature-card__media">
                                                            <img src="{{ $featureThumbnail }}" alt="" loading="lazy" decoding="async">
                                                        </div>
                                                    @else
                                                        <div class="network-feature-card__fallback">
                                                            <div>
                                                                <div class="network-feature-card__fallback-kind">{{ $featureKind }}</div>
                                                                <div class="network-feature-card__fallback-code">{{ $featureFallbackCode }}</div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="network-feature-card__shade"></div>
                                                    <div class="network-feature-card__body">
                                                        <div class="network-feature-card__meta">
                                                            <span class="network-feature-card__pill">{{ $featureKindLabel }}</span>
                                                            <span class="network-feature-card__pill">{{ $feature->year_be ?: '-' }}</span>
                                                            @if (! $featureHasLink)
                                                                <span class="network-feature-card__pill">ไม่มีลิงก์</span>
                                                            @endif
                                                        </div>
                                                        <div class="network-feature-card__title">{{ $featureTitle }}</div>
                                                        @if ($featureDescription !== '')
                                                            <div class="network-feature-card__desc">{{ $featureDescription }}</div>
                                                        @endif
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-6 rounded-2xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500">ยังไม่มีรูปสำหรับแสดงผล</div>
                        @endif
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

            const StackedTotalLabelsPlugin = {
                id: 'stackedTotalLabels',
                afterDatasetsDraw(chart, args, pluginOptions) {
                    const opts = pluginOptions || {};
                    if (!opts.enabled || chart.config.type !== 'bar') return;

                    const labels = chart.data.labels || [];
                    if (!labels.length) return;

                    const ctx = chart.ctx;
                    const chartTop = chart.chartArea?.top ?? 0;
                    const color = opts.color || '#0f172a';
                    const backgroundColor = opts.backgroundColor || '#ffffff';
                    const borderColor = opts.borderColor || '#cbd5e1';

                    ctx.save();
                    ctx.font = opts.font || '700 12px Sarabun, Noto Sans Thai, Segoe UI, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';

                    labels.forEach((_, dataIndex) => {
                        let total = 0;
                        let x = null;
                        let topY = null;

                        chart.data.datasets.forEach((dataset, datasetIndex) => {
                            if (!chart.isDatasetVisible(datasetIndex)) return;
                            const value = Number(dataset?.data?.[dataIndex] || 0);
                            if (value <= 0) return;

                            const element = chart.getDatasetMeta(datasetIndex)?.data?.[dataIndex];
                            if (!element) return;

                            total += value;
                            x = element.x;
                            topY = topY === null ? element.y : Math.min(topY, element.y);
                        });

                        if (total <= 0 || x === null || topY === null) return;

                        const text = String(total);
                        const metrics = ctx.measureText(text);
                        const paddingX = 6;
                        const boxW = metrics.width + paddingX * 2;
                        const boxH = 20;
                        const boxX = x - boxW / 2;
                        const boxY = Math.max(chartTop + 2, topY - boxH - 6);
                        const radius = 10;

                        ctx.beginPath();
                        ctx.moveTo(boxX + radius, boxY);
                        ctx.lineTo(boxX + boxW - radius, boxY);
                        ctx.quadraticCurveTo(boxX + boxW, boxY, boxX + boxW, boxY + radius);
                        ctx.lineTo(boxX + boxW, boxY + boxH - radius);
                        ctx.quadraticCurveTo(boxX + boxW, boxY + boxH, boxX + boxW - radius, boxY + boxH);
                        ctx.lineTo(boxX + radius, boxY + boxH);
                        ctx.quadraticCurveTo(boxX, boxY + boxH, boxX, boxY + boxH - radius);
                        ctx.lineTo(boxX, boxY + radius);
                        ctx.quadraticCurveTo(boxX, boxY, boxX + radius, boxY);
                        ctx.closePath();
                        ctx.fillStyle = backgroundColor;
                        ctx.fill();
                        ctx.strokeStyle = borderColor;
                        ctx.lineWidth = 1;
                        ctx.stroke();

                        ctx.fillStyle = color;
                        ctx.fillText(text, x, boxY + boxH / 2);
                    });

                    ctx.restore();
                }
            };

            Chart.register(ChartDataLabels, OutlabelsWithLinesPlugin, StackedTotalLabelsPlugin);

            const yearLabels = @json($yearLabels);
            const sourceSeries = @json($sourceSeries);
            const typeLabels = @json($typeChartLabels);
            const typeValues = @json($typeValues);
            const typeColors = @json($typeChartColors);
            const typeBorderColors = @json($typeChartBorderColors);
            const sourceLabelMap = @json($sourceLabelMap);
            const outputYearSourceItems = @json(collect($outputYearSourceItems ?? []));
            const paperRankingLabels = @json($paperRankingLabels);
            const paperRankingValues = @json($paperRankingValues);
            const paperRankingItems = @json(collect($paperRankingItems ?? []));
            const presentationActivityLabels = @json($presentationActivityLabels);
            const presentationActivityValues = @json($presentationActivityValues);
            const presentationActivityItems = @json(collect($presentationActivityItems ?? []));
            const paperYearLabels = @json($paperYearLabels);
            const paperRankingYearSeries = @json(collect($paperRankingYearSeries ?? []));
            const paperRankingYearItems = @json(collect($paperRankingYearItems ?? []));
            const presentationYearLabels = @json($presentationYearLabels);
            const presentationActivityYearSeries = @json(collect($presentationActivityYearSeries ?? []));
            const presentationActivityYearItems = @json(collect($presentationActivityYearItems ?? []));
            const textbookYearLabels = @json($textbookYearLabels);
            const textbookYearValues = @json($textbookYearValues);
            const textbookYearItems = @json(collect($textbookYearItems ?? []));
            const networkingYearLabels = @json($networkingYearLabels);
            const networkingYearSeries = @json($networkingYearSeries);
            const networkingYearItems = @json(collect($networkingYearItems ?? []));
            const outputTypeChartItems = @json(collect($outputTypeChartItems ?? []));
            const policyFrameworkTrendYears = @json(collect($policyFrameworkTrend['years'] ?? [])->values());
            const policyFrameworkTrendSeries = @json(collect($policyFrameworkTrend['series'] ?? []));
            const policyFrameworkTrendItems = @json(collect($policyFrameworkTrend['items'] ?? []));
            const policyFrameworkAllItems = @json(collect($policyFrameworkTrend['all_items'] ?? []));
            const dataLiteracyTrendYears = @json(collect($policyDataSourceTrend['years'] ?? [])->values());
            const dataLiteracyTrendSeries = @json(collect($policyDataSourceTrend['series'] ?? []));
            const dataLiteracyTrendItems = @json(collect($policyDataSourceTrend['items'] ?? []));
            const policyImpactTrendYears = @json(collect($policyImpactTrend['years'] ?? [])->values());
            const policyImpactTrendSeries = @json(collect($policyImpactTrend['series'] ?? []));
            const policyImpactTrendItems = @json(collect($policyImpactTrend['items'] ?? []));
            const policyImpactTrendItemsByYear = @json(collect($policyImpactTrend['items_by_year'] ?? []));
            const fteSkillRadarRows = @json(collect(data_get($fteSkillAnalysis ?? [], 'radar_rows', []))->values());
            const isPublicSeminar = @json($isPublicSeminar ?? false);
            const themeGapAi = {
                enabled: @json(!($isPublicSeminar ?? false) && $policyImpactThemeAiFacts->isNotEmpty()),
                endpoint: @json(($isPublicSeminar ?? false) ? null : route('outputs.visualization.theme-gap-ai', [], false)),
                csrf: @json(csrf_token()),
                facts: {
                    themes: @json($policyImpactThemeAiFacts),
                },
                fallback: @json($policyImpactThemeNarrativeParagraph)
            };
            const sirirajClinicalTrendRows = @json($expertTrendRows);
            const sirirajClinicalTrendItems = @json(collect($expertTrend['items_by_year'] ?? []));
            const clinicalLedIntegrationTrend = @json($clinicalLedIntegrationTrend);
            const clinicalLedIntegrationRoleTrend = @json(collect($clinicalLedIntegration['role_trend'] ?? []));
            const clinicalLedIntegrationItemsByThemeYear = @json(collect($clinicalLedIntegration['items_by_theme_year'] ?? []));
            const presentationChartColors = ['#ea580c', '#fb7185', '#16a34a', '#0891b2', '#7c3aed', '#ca8a04', '#0f766e', '#2563eb', '#475569'];
            const presentationChartBorderColors = ['#c2410c', '#e11d48', '#15803d', '#0e7490', '#6d28d9', '#a16207', '#115e59', '#1d4ed8', '#334155'];
            const textbookYearColors = @json(collect($textbookYearLabels ?? collect())->map(fn ($year) => $textbookYearColorMap[(string) $year] ?? '#0f766e')->values());
            const networkingEa108 = (networkingYearSeries?.EA108 || []);
            const networkingEa114 = (networkingYearSeries?.EA114 || []);
            const networkingEa115 = (networkingYearSeries?.EA115 || []);
            const networkingEa117 = (networkingYearSeries?.EA117 || []);

            const gridColor = 'rgba(148, 163, 184, 0.2)';
            const textColor = '#334155';
            const chartAnim = { duration: 760, easing: 'easeOutCubic' };
            const chartFontFamily = '"Sarabun", "Noto Sans Thai", "Segoe UI", sans-serif';
            const outputInProjectColor = '#4f46e5';
            const outputInProjectBorder = '#3730a3';
            const missionColor = '#0f766e';
            const missionBorder = '#115e59';
            const extraColor = '#ea580c';
            const extraBorder = '#c2410c';
            function isCompactViewport() {
                return window.matchMedia('(max-width: 640px)').matches;
            }
            function isTabletLandscapeCompact() {
                return window.matchMedia('(min-width: 1024px) and (max-width: 1199px) and (orientation: landscape)').matches;
            }
            function isClinicalLedCompactViewport() {
                return isCompactViewport() || isTabletLandscapeCompact();
            }

            const safeLabels = (labels) => (labels && labels.length ? labels : ['ไม่มีข้อมูล']);
            const safeValues = (values, fallback = 0) => (values && values.length ? values : [fallback]);
            const safeColors = (colors) => (colors && colors.length ? colors : ['#64748b']);
            const enhanceThemeGapWithAi = async () => {
                const target = document.querySelector('[data-theme-gap-ai-field="paragraph"]');
                const statusDot = document.querySelector('[data-theme-gap-ai-field="status-dot"]');
                if (!target) return;

                const markFallback = () => {
                    statusDot?.classList.remove('bg-emerald-500');
                    statusDot?.classList.add('bg-red-500');
                };
                const markAi = () => {
                    statusDot?.classList.remove('bg-red-500');
                    statusDot?.classList.add('bg-emerald-500');
                };

                markFallback();
                if (!themeGapAi?.enabled || !themeGapAi?.endpoint) return;

                try {
                    const response = await fetch(themeGapAi.endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': themeGapAi.csrf || '',
                        },
                        body: JSON.stringify({
                            facts: themeGapAi.facts || {},
                            fallback: themeGapAi.fallback || '',
                        }),
                    });

                    if (!response.ok) return;
                    const payload = await response.json();
                    if (!payload?.ok || !payload.paragraph) {
                        markFallback();
                        return;
                    }

                    target.textContent = payload.paragraph;
                    markAi();
                } catch (error) {
                    markFallback();
                    // Keep the rule-based paragraph when Gemini is unavailable, rate-limited, or out of tokens.
                }
            };
            const setupDoctorRoleTables = () => {
                document.querySelectorAll('[data-doctor-role-panel]').forEach((panel) => {
                    const tabs = Array.from(panel.querySelectorAll('[data-doctor-role-tab]'));
                    const floating = panel.querySelector('[data-doctor-role-floating]');
                    const floatingTabs = Array.from(panel.querySelectorAll('[data-doctor-role-floating-tab]'));
                    const triggers = [...tabs, ...floatingTabs];
                    const tables = Array.from(panel.querySelectorAll('[data-doctor-role-table]'));
                    const mobileQuery = window.matchMedia('(max-width: 640px)');
                    let panelInView = false;
                    if (floating && floating.parentElement !== document.body) {
                        document.body.appendChild(floating);
                    }
                    const syncFloatingVisibility = () => {
                        if (!floating) return;
                        floating.classList.toggle('is-visible', mobileQuery.matches && panelInView);
                    };
                    const activateRole = (key, options = {}) => {
                        tabs.forEach((item) => {
                            const active = item.dataset.doctorRoleTab === key;
                            item.classList.toggle('is-active', active);
                            item.setAttribute('aria-pressed', active ? 'true' : 'false');
                        });
                        floatingTabs.forEach((item) => {
                            const active = item.dataset.doctorRoleFloatingTab === key;
                            item.classList.toggle('is-active', active);
                            item.setAttribute('aria-pressed', active ? 'true' : 'false');
                        });
                        tables.forEach((table) => {
                            table.classList.toggle('hidden', table.dataset.doctorRoleTable !== key);
                        });
                        if (options.scroll && mobileQuery.matches) {
                            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    };

                    triggers.forEach((tab) => {
                        tab.addEventListener('click', () => {
                            const key = tab.dataset.doctorRoleTab || tab.dataset.doctorRoleFloatingTab || '';
                            activateRole(key, { scroll: Boolean(tab.dataset.doctorRoleFloatingTab) });
                        });
                    });

                    if (floating) {
                        if ('IntersectionObserver' in window) {
                            const observer = new IntersectionObserver((entries) => {
                                panelInView = entries.some((entry) => entry.isIntersecting);
                                syncFloatingVisibility();
                            }, {
                                threshold: 0,
                                rootMargin: '-18% 0px -22% 0px',
                            });
                            observer.observe(panel);
                        } else {
                            const updateFloating = () => {
                                const rect = panel.getBoundingClientRect();
                                panelInView = rect.top < window.innerHeight * .82 && rect.bottom > window.innerHeight * .18;
                                syncFloatingVisibility();
                            };
                            window.addEventListener('scroll', updateFloating, { passive: true });
                            window.addEventListener('resize', updateFloating);
                            updateFloating();
                        }
                        if (typeof mobileQuery.addEventListener === 'function') {
                            mobileQuery.addEventListener('change', syncFloatingVisibility);
                        } else if (typeof mobileQuery.addListener === 'function') {
                            mobileQuery.addListener(syncFloatingVisibility);
                        }
                    }

                    const initialKey = tabs.find((item) => item.classList.contains('is-active'))?.dataset.doctorRoleTab
                        || tabs[0]?.dataset.doctorRoleTab
                        || floatingTabs[0]?.dataset.doctorRoleFloatingTab
                        || '';
                    if (initialKey) {
                        activateRole(initialKey);
                    }
                });
            };
            const setupFteCapacityFollow = () => {
                const mq = window.matchMedia('(min-width: 1280px), (min-width: 1024px) and (max-width: 1199px) and (orientation: landscape)');
                const tables = Array.from(document.querySelectorAll('[data-fte-capacity-tables]'));
                if (!tables.length) return;

                const update = () => {
                    tables.forEach((tableGroup) => {
                        const left = tableGroup.querySelector('[data-fte-capacity-left]');
                        const card = tableGroup.querySelector('[data-fte-capacity-follow]');
                        if (!left || !card || !mq.matches) {
                            if (card) card.style.setProperty('--fte-capacity-stick-y', '0px');
                            return;
                        }

                        const topOffset = 96;
                        const groupTop = tableGroup.getBoundingClientRect().top + window.scrollY;
                        const leftHeight = left.offsetHeight || 0;
                        const cardHeight = card.offsetHeight || 0;
                        const maxMove = Math.max(0, leftHeight - cardHeight);
                        const rawMove = window.scrollY - groupTop + topOffset;
                        const move = Math.max(0, Math.min(maxMove, rawMove));

                        card.style.setProperty('--fte-capacity-stick-y', `${Math.round(move)}px`);
                    });
                };

                update();
                window.addEventListener('scroll', update, { passive: true });
                window.addEventListener('resize', update, { passive: true });
                window.visualViewport?.addEventListener('resize', update, { passive: true });
                if (typeof ResizeObserver !== 'undefined') {
                    tables.forEach((tableGroup) => {
                        const observer = new ResizeObserver(update);
                        observer.observe(tableGroup);
                    });
                }
            };
            const renderedCharts = new Map();
            const pendingChartConfigs = new Map();
            const createChart = (canvasId, config) => {
                const canvas = document.getElementById(canvasId);
                if (!canvas || typeof Chart === 'undefined') return null;

                try {
                    Chart.getChart?.(canvas)?.destroy();
                    const chart = new Chart(canvas, config);
                    renderedCharts.set(canvasId, chart);
                    return chart;
                } catch (error) {
                    console.error(`Unable to render ${canvasId}`, error);
                    return null;
                }
            };
            const chartObserver = 'IntersectionObserver' in window
                ? new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        const canvas = entry.target;
                        const canvasId = canvas.id;
                        const config = pendingChartConfigs.get(canvasId);
                        if (!config) return;
                        pendingChartConfigs.delete(canvasId);
                        chartObserver.unobserve(canvas);
                        createChart(canvasId, config);
                    });
                }, { rootMargin: '520px 0px', threshold: 0.01 })
                : null;
            const renderChart = (canvasId, config) => {
                const canvas = document.getElementById(canvasId);
                if (!canvas || typeof Chart === 'undefined') return null;

                const rect = canvas.getBoundingClientRect();
                const shouldDefer = chartObserver && rect.top > window.innerHeight * 1.15;
                if (shouldDefer) {
                    pendingChartConfigs.set(canvasId, config);
                    chartObserver.observe(canvas);
                    return null;
                }

                return createChart(canvasId, config);
            };
            let chartResizeTimer = null;
            const resizeRenderedCharts = () => {
                window.clearTimeout(chartResizeTimer);
                chartResizeTimer = window.setTimeout(() => {
                    renderedCharts.forEach((chart, canvasId) => {
                        try {
                            const canvas = document.getElementById(canvasId);
                            if (!canvas || !chart) return;
                            if (canvasId === 'chartClinicalLedIntegrationTrend') {
                                chart.options.layout = chart.options.layout || {};
                                chart.options.layout.padding = {
                                    ...(chart.options.layout.padding || {}),
                                    top: isClinicalLedCompactViewport() ? 14 : 46,
                                    right: isClinicalLedCompactViewport() ? 8 : 12,
                                    left: isClinicalLedCompactViewport() ? 8 : 12
                                };
                                chart.options.plugins = chart.options.plugins || {};
                                chart.options.plugins.legend = chart.options.plugins.legend || {};
                                chart.options.plugins.legend.labels = {
                                    ...(chart.options.plugins.legend.labels || {}),
                                    boxWidth: isClinicalLedCompactViewport() ? 9 : 14,
                                    padding: isClinicalLedCompactViewport() ? 4 : 6,
                                    font: { size: isClinicalLedCompactViewport() ? 10 : 12 }
                                };
                            }
                            if (canvasId === 'chartFteSkillRadar') {
                                chart.options.layout = chart.options.layout || {};
                                chart.options.layout.padding = {
                                    top: isCompactViewport() ? 18 : 34,
                                    right: isCompactViewport() ? 10 : 24,
                                    bottom: isCompactViewport() ? 8 : 12,
                                    left: isCompactViewport() ? 10 : 24
                                };
                                chart.options.scales = chart.options.scales || {};
                                chart.options.scales.r = chart.options.scales.r || {};
                                chart.options.scales.r.pointLabels = {
                                    ...(chart.options.scales.r.pointLabels || {}),
                                    padding: isCompactViewport() ? 10 : 18,
                                    font: { family: chartFontFamily, size: isCompactViewport() ? 10 : 13, weight: '600' }
                                };
                            }
                            chart.resize();
                            chart.update('none');
                        } catch (error) {
                            console.error(`Unable to resize ${canvasId}`, error);
                        }
                    });
                }, 80);
            };
            const suggestedAxisMax = (values, min = 5) => {
                const maxVal = Math.max(...(values || [0]), 0);
                if (maxVal <= 0) return min;
                return Math.ceil(maxVal * 1.18);
            };
            const fteRadarColors = {
                hours: {
                    fill: 'rgba(37, 99, 235, 0.24)',
                    hoverFill: 'rgba(37, 99, 235, 0.24)',
                    border: '#2563eb',
                    point: '#2563eb',
                },
                tasks: {
                    fill: 'rgba(249, 115, 22, 0.22)',
                    hoverFill: 'rgba(249, 115, 22, 0.22)',
                    border: '#f97316',
                    point: '#f97316',
                },
                people: {
                    fill: 'rgba(124, 58, 237, 0.22)',
                    hoverFill: 'rgba(124, 58, 237, 0.22)',
                    border: '#7c3aed',
                    point: '#7c3aed',
                },
            };
            const fteRadarLabels = (fteSkillRadarRows || []).map((row) => row.label);
            if (fteRadarLabels.length) {
                renderChart('chartFteSkillRadar', {
                    type: 'radar',
                    data: {
                        labels: fteRadarLabels,
                        datasets: [
                            {
                                label: 'เวลาทำงาน (ชั่วโมง)',
                                data: fteSkillRadarRows.map((row) => Number(row.hours_pct || 0)),
                                rawMetric: 'hours',
                                rawLabel: 'ชั่วโมง',
                                backgroundColor: fteRadarColors.hours.fill,
                                hoverBackgroundColor: fteRadarColors.hours.hoverFill,
                                borderColor: fteRadarColors.hours.border,
                                pointBackgroundColor: fteRadarColors.hours.point,
                                pointBorderColor: '#ffffff',
                                pointRadius: 3,
                                pointHoverRadius: 3,
                                pointHoverBorderWidth: 1,
                                pointHitRadius: 8,
                                borderWidth: 2,
                                hoverBorderWidth: 2
                            },
                            {
                                label: 'ภาระงาน',
                                data: fteSkillRadarRows.map((row) => Number(row.tasks_pct || 0)),
                                rawMetric: 'tasks',
                                rawLabel: 'งาน',
                                backgroundColor: fteRadarColors.tasks.fill,
                                hoverBackgroundColor: fteRadarColors.tasks.hoverFill,
                                borderColor: fteRadarColors.tasks.border,
                                pointBackgroundColor: fteRadarColors.tasks.point,
                                pointBorderColor: '#ffffff',
                                pointRadius: 3,
                                pointHoverRadius: 3,
                                pointHoverBorderWidth: 1,
                                pointHitRadius: 8,
                                borderWidth: 2,
                                hoverBorderWidth: 2
                            },
                            {
                                label: 'กำลังคน',
                                data: fteSkillRadarRows.map((row) => Number(row.people_pct || 0)),
                                rawMetric: 'people',
                                rawLabel: 'คน',
                                backgroundColor: fteRadarColors.people.fill,
                                hoverBackgroundColor: fteRadarColors.people.hoverFill,
                                borderColor: fteRadarColors.people.border,
                                pointBackgroundColor: fteRadarColors.people.point,
                                pointBorderColor: '#ffffff',
                                pointRadius: 3,
                                pointHoverRadius: 3,
                                pointHoverBorderWidth: 1,
                                pointHitRadius: 8,
                                borderWidth: 2,
                                hoverBorderWidth: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: chartAnim,
                        layout: {
                            padding: {
                                top: isCompactViewport() ? 18 : 34,
                                right: isCompactViewport() ? 10 : 24,
                                bottom: isCompactViewport() ? 8 : 12,
                                left: isCompactViewport() ? 10 : 24
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            intersect: true
                        },
                        hover: {
                            mode: 'nearest',
                            intersect: true
                        },
                        onHover: (event, activeElements) => {
                            if (event?.native?.target) {
                                event.native.target.style.cursor = activeElements?.length ? 'pointer' : 'default';
                            }
                        },
                        scales: {
                            r: {
                                beginAtZero: true,
                                min: 0,
                                max: 90,
                                ticks: {
                                    stepSize: 22.5,
                                    color: '#0f172a',
                                    backdropColor: 'rgba(255, 255, 255, 0.72)',
                                    font: { family: chartFontFamily, weight: '400' },
                                    callback: (value) => `${Number(value).toFixed(2)}%`
                                },
                                pointLabels: {
                                    color: '#0f172a',
                                    padding: isCompactViewport() ? 10 : 18,
                                    font: { family: chartFontFamily, size: isCompactViewport() ? 10 : 13, weight: '600' }
                                },
                                angleLines: { color: 'rgba(148, 163, 184, 0.36)' },
                                grid: { color: 'rgba(148, 163, 184, 0.42)' }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            datalabels: { display: false },
                            tooltip: {
                                enabled: true,
                                displayColors: true,
                                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                                padding: 12,
                                titleFont: { weight: '800' },
                                bodyFont: { weight: '700' },
                                callbacks: {
                                    title: (items) => {
                                        const row = fteSkillRadarRows?.[items?.[0]?.dataIndex || 0];
                                        return row ? [row.label, row.thai] : '';
                                    },
                                    label: (context) => {
                                        const row = fteSkillRadarRows?.[context.dataIndex] || {};
                                        const metric = context.dataset.rawMetric;
                                        const unit = context.dataset.rawLabel;
                                        const raw = Number(row?.[metric] || 0);
                                        const value = metric === 'hours' ? raw.toLocaleString(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1 }) : raw.toLocaleString();
                                        return `${context.dataset.label}: ${Number(context.parsed.r || 0).toFixed(1)}% (${value} ${unit})`;
                                    },
                                    afterBody: (items) => {
                                        const row = fteSkillRadarRows?.[items?.[0]?.dataIndex || 0];
                                        if (!row) return [];
                                        return [
                                            `โครงการวิจัย: ${Number(row.project_count || 0).toLocaleString()} งาน`,
                                            `พันธกิจ: ${Number(row.backoffice_count || 0).toLocaleString()} งาน`,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                });
            }
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
                            key: 'project',
                            label: 'ผลงานในโครงการวิจัย',
                            data: projectSeries,
                            backgroundColor: outputInProjectColor,
                            borderColor: outputInProjectBorder,
                            borderWidth: 1,
                            hoverBackgroundColor: outputInProjectBorder,
                            borderRadius: 8
                        },
                        {
                            key: 'back_office',
                            label: 'พันธกิจ',
                            data: missionSeries,
                            backgroundColor: missionColor,
                            borderColor: missionBorder,
                            borderWidth: 1,
                            hoverBackgroundColor: missionBorder,
                            borderRadius: 8
                        },
                        {
                            key: 'external',
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
                    interaction: { mode: 'nearest', intersect: true },
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
                    },
                    onHover: (event, elements, chart) => {
                        const target = event?.native?.target || event?.chart?.canvas || document.getElementById('chartOutputsByYear');
                        const nearest = chart.getElementsAtEventForMode(event, 'nearest', { intersect: true }, true);
                        if (target) target.style.cursor = nearest.length ? 'pointer' : 'default';
                    },
                    onClick: (event, elements, chart) => {
                        const nearest = chart.getElementsAtEventForMode(event, 'nearest', { intersect: true }, true);
                        if (!nearest.length) return;
                        const point = nearest[0];
                        const dataset = chart.data.datasets?.[point.datasetIndex] || {};
                        const year = chart.data.labels?.[point.index];
                        const yearKey = year === 'ไม่ระบุปี' ? '__unknown__' : String(year);
                        const items = normalizeFrameworkItems(outputYearSourceItems?.[dataset.key]?.[yearKey]);
                        openOutputItemsModal(items, dataset.label || 'รายการผลงาน', 'รายการ', { year: yearKey === '__unknown__' ? '' : yearKey });
                    }
                }
            });

            const frameworkTrendLabels = safeLabels(policyFrameworkTrendYears);
            const frameworkTrendMeta = {
                trust: 'Credibility',
                truth: 'Validation',
                policy_impact: 'Impact',
                clinical_led: 'กลุ่มเนื้อหา Ageing and Frailty, Health Systems and HTA และ Real-World Evidence (RWE)'
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
                clinical_led: { bg: '#f0f9ff', border: '#bae6fd', accent: '#0369a1', header: '#f0f9ff' },
                clinical_ageing: { bg: '#eef2ff', border: '#c7d2fe', accent: '#4f46e5', header: '#eef2ff' },
                clinical_hta: { bg: '#fff7ed', border: '#fed7aa', accent: '#d97706', header: '#fff7ed' },
                clinical_rwe: { bg: '#ecfdf5', border: '#99f6e4', accent: '#0f766e', header: '#ecfdf5' }
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
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">ผลงาน</div>
                                    <div class="mt-1 text-base font-semibold leading-snug text-slate-900">${title}</div>
                                    <div class="mt-1 text-sm font-medium text-slate-500">${meta}</div>
                                    ${typeBadge ? `<div class="mt-2">${typeBadge}</div>` : ''}
                                    ${peopleHtml}
                                </div>
                            </div>
                        `;
                        return link && !isPublicSeminar
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
            const openOutputItemsModal = (items, title, unit = 'รายการ', initialFilters = {}, tone = null) => {
                const normalizedItems = normalizeFrameworkItems(items);
                if (!frameworkModal || !frameworkModalBody) return;
                frameworkModalTone = tone || { bg: '#f8fafc', border: '#cbd5e1', accent: '#334155', header: '#f8fafc' };
                frameworkModalTitle.textContent = title || 'รายการ';
                frameworkModalSubtitle.innerHTML = `<span class="text-base font-semibold text-slate-700">รวม ${normalizedItems.length.toLocaleString()} ${escapeFrameworkHtml(unit || 'รายการ')}</span>`;
                setFrameworkModalItems(normalizedItems, unit || 'รายการ', initialFilters || {});
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
                    layout: {
                        padding: { top: 24, right: 96, bottom: 0, left: 72 }
                    },
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: { offset: false, ticks: { color: textColor }, grid: { display: false } },
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
                    },
                    onHover: (event, elements) => {
                        const target = event?.native?.target || event?.chart?.canvas || document.getElementById('chartSirirajClinicalTrend');
                        if (target) target.style.cursor = elements.length ? 'pointer' : 'default';
                    },
                    onClick: (event, elements, chart) => {
                        if (!elements.length) return;
                        const point = elements[0];
                        const year = chart.data.labels?.[point.index];
                        const items = normalizeFrameworkItems(sirirajClinicalTrendItems?.[String(year)]);
                        openOutputItemsModal(items, 'แพทย์ศิริราชที่ร่วมในกลุ่มโครงการ', 'โครงการ', { year: String(year || '') }, frameworkToneStyles.truth);
                    }
                }
            });

            const clinicalLedYears = safeLabels(clinicalLedIntegrationTrend?.years || []);
            const clinicalLedAgeingPeople = clinicalLedYears.map((_, index) => Number(clinicalLedIntegrationTrend?.ageing_people?.[index] || 0));
            const clinicalLedHtaPeople = clinicalLedYears.map((_, index) => Number(clinicalLedIntegrationTrend?.hta_people?.[index] || 0));
            const clinicalLedRwePeople = clinicalLedYears.map((_, index) => Number(clinicalLedIntegrationTrend?.rwe_people?.[index] || 0));
            const clinicalLedPiPeople = clinicalLedYears.map((_, index) => Number(clinicalLedIntegrationRoleTrend?.pi?.[index] || 0));
            const clinicalLedCoPiPeople = clinicalLedYears.map((_, index) => Number(clinicalLedIntegrationRoleTrend?.co_pi?.[index] || 0));
            const clinicalLedCoResearchPeople = clinicalLedYears.map((_, index) => Number(clinicalLedIntegrationRoleTrend?.co_research?.[index] || 0));
            const clinicalLedYearNumbers = clinicalLedYears.map((year) => Number(year)).filter((year) => Number.isFinite(year));
            const clinicalLedYearMin = clinicalLedYearNumbers.length ? Math.min(...clinicalLedYearNumbers) - 0.25 : undefined;
            const clinicalLedYearMax = clinicalLedYearNumbers.length ? Math.max(...clinicalLedYearNumbers) + 0.25 : undefined;
            const clinicalLedYearPoint = (value, index) => ({ x: Number(clinicalLedYears[index]), y: Number(value || 0) });
            const openClinicalLedIntegrationModal = (themeKey, year) => {
                const items = normalizeFrameworkItems(clinicalLedIntegrationItemsByThemeYear?.[themeKey]?.[String(year)]);
                if (!frameworkModal || !frameworkModalBody) return;
                const themeLabel = themeKey === 'rwe'
                    ? 'Real-World Evidence (RWE)'
                    : (themeKey === 'hta' ? 'Health Systems and HTA' : 'Ageing and Frailty');
                frameworkModalTone = themeKey === 'rwe'
                    ? frameworkToneStyles.clinical_rwe
                    : (themeKey === 'hta' ? frameworkToneStyles.clinical_hta : frameworkToneStyles.clinical_ageing);
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
                            data: clinicalLedAgeingPeople.map(clinicalLedYearPoint),
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, .12)',
                            pointBackgroundColor: '#4f46e5',
                            tension: .35,
                            fill: true
                        },
                        {
                            key: 'hta',
                            label: 'Health Systems and HTA',
                            data: clinicalLedHtaPeople.map(clinicalLedYearPoint),
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217, 119, 6, .12)',
                            pointBackgroundColor: '#d97706',
                            tension: .35,
                            fill: true
                        },
                        {
                            key: 'rwe',
                            label: 'Real-World Evidence (RWE)',
                            data: clinicalLedRwePeople.map(clinicalLedYearPoint),
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
                    layout: {
                        padding: {
                            top: isClinicalLedCompactViewport() ? 14 : 46,
                            right: isClinicalLedCompactViewport() ? 8 : 12,
                            bottom: 8,
                            left: isClinicalLedCompactViewport() ? 8 : 12
                        }
                    },
                    interaction: { mode: 'index', intersect: false },
                    elements: {
                        point: {
                            radius: () => isClinicalLedCompactViewport() ? 3 : 4,
                            hoverRadius: () => isClinicalLedCompactViewport() ? 5 : 6
                        },
                        line: {
                            borderWidth: () => isClinicalLedCompactViewport() ? 2 : 3
                        }
                    },
                    scales: {
                        x: {
                            type: 'linear',
                            min: clinicalLedYearMin,
                            max: clinicalLedYearMax,
                            afterBuildTicks: (scale) => {
                                scale.ticks = clinicalLedYearNumbers.map((year) => ({ value: year }));
                            },
                            ticks: {
                                color: textColor,
                                maxRotation: 0,
                                minRotation: 0,
                                font: {
                                    size: () => isClinicalLedCompactViewport() ? 10 : 12,
                                    weight: '700'
                                },
                                callback: (value) => String(Math.trunc(Number(value)))
                            },
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            suggestedMax: suggestedAxisMax([...clinicalLedAgeingPeople, ...clinicalLedHtaPeople, ...clinicalLedRwePeople], 5),
                            ticks: {
                                color: textColor,
                                precision: 0,
                                font: { size: () => isClinicalLedCompactViewport() ? 10 : 12 }
                            },
                            grid: { color: gridColor }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                boxWidth: isClinicalLedCompactViewport() ? 9 : 14,
                                boxHeight: 8,
                                padding: isClinicalLedCompactViewport() ? 4 : 6,
                                font: { size: isClinicalLedCompactViewport() ? 10 : 12 }
                            }
                        },
                        datalabels: {
                            labels: {
                                value: {
                                    display: () => !isClinicalLedCompactViewport(),
                                    color: '#334155',
                                    anchor: 'end',
                                    align: (context) => context.datasetIndex === 1 ? 'bottom' : 'end',
                                    offset: (context) => context.datasetIndex === 1 ? 8 : 6,
                                    clamp: true,
                                    clip: false,
                                    font: { weight: '700', size: 10 },
                                    formatter: (value) => Number(value?.y || 0) > 0 ? Number(value.y) : ''
                                },
                                roles: {
                                    display: (context) => context.datasetIndex === 0 && !isClinicalLedCompactViewport(),
                                    color: '#334155',
                                    anchor: 'end',
                                    align: 'top',
                                    offset: 30,
                                    clamp: true,
                                    clip: false,
                                    backgroundColor: 'rgba(255, 255, 255, .92)',
                                    borderColor: 'rgba(148, 163, 184, .45)',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                    padding: { top: 4, right: 8, bottom: 4, left: 8 },
                                    textAlign: 'center',
                                    font: { weight: '700', size: 9 },
                                    formatter: (_value, context) => {
                                        const index = context.dataIndex;
                                        return [
                                            `PI ${clinicalLedPiPeople[index] || 0}`,
                                            `Co-PI ${clinicalLedCoPiPeople[index] || 0}`,
                                            `Co-Research ${clinicalLedCoResearchPeople[index] || 0}`
                                        ];
                                    }
                                }
                            }
                        }
                    },
                    onHover: (event, elements) => {
                        const target = event?.native?.target || event?.chart?.canvas || document.getElementById('chartClinicalLedIntegrationTrend');
                        const nearest = event?.chart?.getElementsAtEventForMode(event, 'nearest', { intersect: false }, true) || [];
                        if (target) target.style.cursor = nearest.length ? 'pointer' : 'default';
                    },
                    onClick: (event, elements, chart) => {
                        const nearest = chart.getElementsAtEventForMode(event, 'nearest', { intersect: false }, true);
                        const point = nearest[0] || elements[0];
                        if (!point) return;
                        const dataset = chart.data.datasets?.[point.datasetIndex] || {};
                        const year = dataset.data?.[point.index]?.x;
                        openClinicalLedIntegrationModal(dataset.key || 'ageing', year);
                    }
                }
            });

            const dataLiteracyYears = safeLabels(dataLiteracyTrendYears || []);
            const dataLiteracyRealWorld = dataLiteracyYears.map((_, index) => Number(dataLiteracyTrendSeries?.['Real-World Data']?.[index] || 0));
            const dataLiteracyPrimary = dataLiteracyYears.map((_, index) => Number(dataLiteracyTrendSeries?.['Primary Data']?.[index] || 0));
            const dataLiteracyCombined = dataLiteracyYears.map((_, index) => Number(dataLiteracyTrendSeries?.['Real-World Data + Primary Data']?.[index] || 0));
            const dataLiteracyTrendMeta = {
                'Real-World Data': 'Real-World Data',
                'Primary Data': 'Primary Data',
                'Real-World Data + Primary Data': 'Real-World Data + Primary Data'
            };
            const openDataLiteracyTrendModal = (datasetKey, year) => {
                const items = normalizeFrameworkItems(dataLiteracyTrendItems?.[datasetKey]?.[String(year)]);
                if (!frameworkModal || !frameworkModalBody) return;
                frameworkModalTone = datasetKey === 'Primary Data'
                    ? { bg: '#ecfdf5', border: '#99f6e4', accent: '#0f766e', header: '#ecfdf5' }
                    : (datasetKey === 'Real-World Data + Primary Data'
                        ? { bg: '#f5f3ff', border: '#ddd6fe', accent: '#7c3aed', header: '#f5f3ff' }
                        : { bg: '#eff6ff', border: '#bfdbfe', accent: '#2563eb', header: '#eff6ff' });
                frameworkModalTitle.textContent = dataLiteracyTrendMeta[datasetKey] || 'Data Source / Method';
                frameworkModalSubtitle.innerHTML = `<span class="inline-flex items-baseline gap-2"><span class="text-2xl font-semibold" style="color:${frameworkModalTone.accent}">ปี ${escapeFrameworkHtml(year)}</span><span class="text-base font-semibold text-slate-700">${items.length.toLocaleString()} โครงการ</span></span>`;
                setFrameworkModalItems(items, 'โครงการ', { year: String(year || '') });
                showFrameworkModal();
            };
            renderChart('chartDataLiteracyTrend', {
                type: 'line',
                data: {
                    labels: dataLiteracyYears,
                    datasets: [
                        {
                            key: 'Real-World Data',
                            label: 'Real-World Data',
                            data: dataLiteracyRealWorld,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, .12)',
                            pointBackgroundColor: '#2563eb',
                            tension: .35,
                            fill: true
                        },
                        {
                            key: 'Primary Data',
                            label: 'Primary Data',
                            data: dataLiteracyPrimary,
                            borderColor: '#0f766e',
                            backgroundColor: 'rgba(15, 118, 110, .10)',
                            pointBackgroundColor: '#0f766e',
                            tension: .35,
                            fill: true
                        },
                        {
                            key: 'Real-World Data + Primary Data',
                            label: 'Real-World Data + Primary Data',
                            data: dataLiteracyCombined,
                            borderColor: '#7c3aed',
                            backgroundColor: 'rgba(124, 58, 237, .10)',
                            pointBackgroundColor: '#7c3aed',
                            tension: .35,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: chartAnim,
                    interaction: { mode: 'nearest', intersect: true },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            suggestedMax: suggestedAxisMax([...dataLiteracyRealWorld, ...dataLiteracyPrimary, ...dataLiteracyCombined], 5),
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
                    onHover: (event, elements, chart) => {
                        const target = event?.native?.target || event?.chart?.canvas || document.getElementById('chartDataLiteracyTrend');
                        const nearest = chart.getElementsAtEventForMode(event, 'nearest', { intersect: true }, true);
                        if (target) target.style.cursor = nearest.length ? 'pointer' : 'default';
                    },
                    onClick: (event, elements, chart) => {
                        const nearest = chart.getElementsAtEventForMode(event, 'nearest', { intersect: true }, true);
                        if (!nearest.length) return;
                        const point = nearest[0];
                        const dataset = chart.data.datasets?.[point.datasetIndex] || {};
                        const year = chart.data.labels?.[point.index];
                        openDataLiteracyTrendModal(dataset.key || dataset.label, year);
                    }
                }
            });

            const policyTrendLabels = safeLabels(policyImpactTrendYears);
            const policyTrendDatasetMeta = {
                EA101: { label: 'Research Paper and Journal Article', unit: 'รายการ' },
                EA109: { label: 'Change in Practices / Procedures / Policy', unit: 'รายการ' },
                EA102: { label: 'Reports and White Papers', unit: 'รายการ' },
                EA106: { label: 'Policy Recommendations', unit: 'รายการ' }
            };
            const openPolicyImpactTrendModal = (year) => {
                const items = normalizeFrameworkItems(policyImpactTrendItemsByYear?.[String(year)]);
                if (!frameworkModal || !frameworkModalBody) return;
                frameworkModalTone = frameworkToneStyles.policy_impact;
                frameworkModalTitle.textContent = 'Impact';
                frameworkModalSubtitle.innerHTML = `<span class="inline-flex items-baseline gap-2"><span class="text-2xl font-semibold" style="color:${frameworkModalTone.accent}">ปี ${escapeFrameworkHtml(year)}</span><span class="text-base font-semibold text-slate-700">${items.length.toLocaleString()} รายการ</span></span>`;
                setFrameworkModalItems(items, 'รายการ', { year: String(year || '') });
                showFrameworkModal();
            };
            const policyTrendDatasets = [
                {
                    key: 'EA101',
                    label: 'Research Paper and Journal Article',
                    data: policyTrendLabels.map((_, index) => Number(policyImpactTrendSeries?.EA101?.[index] || 0)),
                    borderColor: '#7aa2f7',
                    backgroundColor: 'rgba(122, 162, 247, .14)',
                    pointBackgroundColor: '#7aa2f7',
                    tension: .35,
                    fill: true
                },
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
                    },
                    onHover: (event, elements) => {
                        const target = event?.native?.target || event?.chart?.canvas || document.getElementById('chartOutputTypes');
                        if (target) target.style.cursor = elements.length ? 'pointer' : 'default';
                    },
                    onClick: (event, elements, chart) => {
                        if (!elements.length) return;
                        const point = elements[0];
                        const typeLabel = String(chart.data.labels?.[point.index] || '');
                        const typeKey = (typeLabel.match(/EA\d{3}|OTHER/i)?.[0] || typeLabel).toUpperCase();
                        const items = normalizeFrameworkItems(outputTypeChartItems?.[typeKey]);
                        openOutputItemsModal(items, typeLabel || 'ประเภทผลงาน', 'รายการ', { theme: '' });
                    }
                }
            });

            renderChart('chartPaperRanking', {
                type: 'bar',
                data: {
                    labels: safeLabels(paperYearLabels),
                    datasets: safeLabels(paperRankingLabels).map((label, index) => {
                        const colors = ['#4f46e5', '#7c3aed', '#2563eb', '#0891b2', '#0f766e', '#ca8a04', '#64748b'];
                        return {
                            label,
                            data: safeLabels(paperYearLabels).map((_, yearIndex) => Number(paperRankingYearSeries?.[label]?.[yearIndex] || 0)),
                            backgroundColor: colors[index % colors.length],
                            borderColor: colors[index % colors.length],
                            borderWidth: 1,
                            borderRadius: 6,
                            maxBarThickness: 64
                        };
                    })
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: chartAnim,
                    layout: {
                        padding: { left: 8, right: 8, top: 28, bottom: 0 }
                    },
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: { stacked: true, ticks: { color: textColor }, grid: { display: false } },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            suggestedMax: suggestedAxisMax(safeValues(paperYearLabels.map((_, index) => safeLabels(paperRankingLabels).reduce((sum, label) => sum + Number(paperRankingYearSeries?.[label]?.[index] || 0), 0))), 5),
                            ticks: { precision: 0, color: textColor },
                            grid: { color: gridColor }
                        }
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { color: textColor, boxWidth: 12, padding: 10 } },
                        stackedTotalLabels: {
                            enabled: true,
                            color: '#1e1b4b',
                            backgroundColor: '#eef2ff',
                            borderColor: '#c7d2fe'
                        },
                        datalabels: {
                            display: false,
                            color: '#0f172a',
                            anchor: 'end',
                            align: 'end',
                            offset: 2,
                            clamp: true,
                            font: { weight: '700', size: 11 },
                            formatter: (value) => Number(value || 0) > 0 ? Number(value) : ''
                        }
                    },
                    onHover: (event, elements) => {
                        const target = event?.native?.target || event?.chart?.canvas || document.getElementById('chartPaperRanking');
                        if (target) target.style.cursor = elements.length ? 'pointer' : 'default';
                    },
                    onClick: (event, elements, chart) => {
                        if (!elements.length) return;
                        const point = elements[0];
                        const year = chart.data.labels?.[point.index];
                        const ranking = chart.data.datasets?.[point.datasetIndex]?.label;
                        const items = normalizeFrameworkItems(paperRankingYearItems?.[String(ranking)]?.[String(year)]);
                        openOutputItemsModal(items, `Publication: ${year || '-'} / ${ranking || '-'}`, 'รายการ');
                    }
                }
            });

            renderChart('chartPresentationActivity', {
                type: 'bar',
                data: {
                    labels: safeLabels(presentationYearLabels),
                    datasets: safeLabels(presentationActivityLabels).map((label, index) => ({
                        label,
                        data: safeLabels(presentationYearLabels).map((_, yearIndex) => Number(presentationActivityYearSeries?.[label]?.[yearIndex] || 0)),
                        backgroundColor: safeColors(presentationChartColors)[index % safeColors(presentationChartColors).length],
                        borderColor: safeColors(presentationChartBorderColors)[index % safeColors(presentationChartBorderColors).length],
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 64
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: chartAnim,
                    layout: {
                        padding: { left: 8, right: 8, top: 28, bottom: 0 }
                    },
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: { stacked: true, ticks: { color: textColor }, grid: { display: false } },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            suggestedMax: suggestedAxisMax(safeValues(presentationYearLabels.map((_, index) => safeLabels(presentationActivityLabels).reduce((sum, label) => sum + Number(presentationActivityYearSeries?.[label]?.[index] || 0), 0))), 5),
                            ticks: { precision: 0, color: textColor },
                            grid: { color: gridColor }
                        }
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { color: textColor, boxWidth: 12, padding: 10 } },
                        stackedTotalLabels: {
                            enabled: true,
                            color: '#134e4a',
                            backgroundColor: '#ecfdf5',
                            borderColor: '#99f6e4'
                        },
                        datalabels: {
                            display: false,
                            color: '#0f172a',
                            anchor: 'end',
                            align: 'end',
                            offset: 2,
                            clamp: true,
                            font: { weight: '700', size: 11 },
                            formatter: (value) => Number(value || 0) > 0 ? Number(value) : ''
                        }
                    },
                    onHover: (event, elements) => {
                        const target = event?.native?.target || event?.chart?.canvas || document.getElementById('chartPresentationActivity');
                        if (target) target.style.cursor = elements.length ? 'pointer' : 'default';
                    },
                    onClick: (event, elements, chart) => {
                        if (!elements.length) return;
                        const point = elements[0];
                        const year = chart.data.labels?.[point.index];
                        const activity = chart.data.datasets?.[point.datasetIndex]?.label;
                        const items = normalizeFrameworkItems(presentationActivityYearItems?.[String(activity)]?.[String(year)]);
                        openOutputItemsModal(items, `Presentation: ${year || '-'} / ${activity || '-'}`, 'รายการ');
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
                    },
                    onHover: (event, elements) => {
                        const target = event?.native?.target || event?.chart?.canvas || document.getElementById('chartTextbookYear');
                        if (target) target.style.cursor = elements.length ? 'pointer' : 'default';
                    },
                    onClick: (event, elements, chart) => {
                        if (!elements.length) return;
                        const point = elements[0];
                        const year = chart.data.labels?.[point.index];
                        const items = normalizeFrameworkItems(textbookYearItems?.[String(year)]);
                        openOutputItemsModal(items, `Textbook ปี ${year || '-'}`, 'รายการ', { year: String(year || '') });
                    }
                }
            });

            renderChart('chartNetworkingYear', {
                type: 'bar',
                data: {
                    labels: safeLabels(networkingYearLabels),
                    datasets: [
                        {
                            key: 'EA108',
                            label: 'News / Article',
                            data: safeValues(networkingEa108),
                            backgroundColor: '#6366f1',
                            borderColor: '#4f46e5',
                            borderWidth: 1,
                            borderRadius: 8,
                            maxBarThickness: 68
                        },
                        {
                            key: 'EA114',
                            label: 'Events / Forum',
                            data: safeValues(networkingEa114),
                            backgroundColor: '#7c3aed',
                            borderColor: '#6d28d9',
                            borderWidth: 1,
                            borderRadius: 8,
                            maxBarThickness: 68
                        },
                        {
                            key: 'EA115',
                            label: 'Capacity Building / Knowledge Sharing',
                            data: safeValues(networkingEa115),
                            backgroundColor: '#d97706',
                            borderColor: '#b45309',
                            borderWidth: 1,
                            borderRadius: 8,
                            maxBarThickness: 68
                        },
                        {
                            key: 'EA117',
                            label: 'Memorandum of Understanding (MOU)',
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
                            suggestedMax: suggestedAxisMax([...safeValues(networkingEa108), ...safeValues(networkingEa115), ...safeValues(networkingEa117)], 5),
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
                    },
                    onHover: (event, elements) => {
                        const target = event?.native?.target || event?.chart?.canvas || document.getElementById('chartNetworkingYear');
                        if (target) target.style.cursor = elements.length ? 'pointer' : 'default';
                    },
                    onClick: (event, elements, chart) => {
                        if (!elements.length) return;
                        const point = elements[0];
                        const dataset = chart.data.datasets?.[point.datasetIndex] || {};
                        const year = chart.data.labels?.[point.index];
                        const typeCode = dataset.key || (String(dataset.label || '').match(/EA\d{3}/)?.[0]) || '';
                        const items = normalizeFrameworkItems(networkingYearItems?.[typeCode]?.[String(year)]);
                        openOutputItemsModal(items, `${typeCode || 'Networking'} ปี ${year || '-'}`, 'รายการ', { year: String(year || '') });
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
            requestAnimationFrame(() => {
                fades.forEach((el) => el.classList.add('in'));
            });
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

            window.__natureScrollSpyCleanup?.();
            const setupNatureScrollSpy = () => {
                const map = document.querySelector('[data-nature-map]');
                const bubbles = Array.from(document.querySelectorAll('[data-nature-bubble]'));
                const cards = Array.from(document.querySelectorAll('[data-nature-card]'));
                if (!map || bubbles.length === 0 || cards.length === 0) return;

                const bubbleByKey = new Map(bubbles.map((bubble) => [bubble.dataset.natureBubble, bubble]));
                let ticking = false;

                const setActive = (activeIndex) => {
                    cards.forEach((card, index) => {
                        const key = card.dataset.natureCard;
                        const bubble = bubbleByKey.get(key);
                        const isActive = index === activeIndex;
                        const isComplete = index <= activeIndex;

                        card.classList.toggle('is-active', isActive);
                        if (!bubble) return;
                        bubble.classList.toggle('is-active', isActive);
                        bubble.classList.toggle('is-complete', isComplete);

                        if (isActive) {
                            map.classList.add('has-active');
                        }
                    });
                };

                const update = () => {
                    ticking = false;
                    const readLine = Math.round(window.innerHeight * 0.52);
                    let activeIndex = 0;

                    cards.forEach((card, index) => {
                        const rect = card.getBoundingClientRect();
                        if (rect.top <= readLine) {
                            activeIndex = index;
                        }
                    });

                    const firstRect = cards[0].getBoundingClientRect();
                    if (firstRect.top > readLine) {
                        activeIndex = 0;
                    }

                    setActive(activeIndex);
                };

                const requestUpdate = () => {
                    if (ticking) return;
                    ticking = true;
                    window.requestAnimationFrame(update);
                };

                window.addEventListener('scroll', requestUpdate, { passive: true });
                window.addEventListener('resize', requestUpdate);
                update();

                window.__natureScrollSpyCleanup = () => {
                    window.removeEventListener('scroll', requestUpdate);
                    window.removeEventListener('resize', requestUpdate);
                };
            };
            setupNatureScrollSpy();

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
            window.addEventListener('resize', resizeRenderedCharts, { passive: true });
            window.addEventListener('orientationchange', resizeRenderedCharts, { passive: true });
            window.visualViewport?.addEventListener('resize', resizeRenderedCharts, { passive: true });
            if (typeof ResizeObserver !== 'undefined') {
                const pageShell = document.querySelector('.viz-page');
                if (pageShell) {
                    window.__visualizationResizeObserver = new ResizeObserver(resizeRenderedCharts);
                    window.__visualizationResizeObserver.observe(pageShell);
                }
            }
            resizeRenderedCharts();
            enhanceThemeGapWithAi();
            setupDoctorRoleTables();
            setupFteCapacityFollow();

        })();
    </script>
@endsection

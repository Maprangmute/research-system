<?php

namespace App\Console\Commands;

use App\Models\AiVisualizationInsight;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GenerateVisualizationAiInsights extends Command
{
    protected $signature = 'outputs:generate-visualization-ai-insights
        {--provider= : AI provider: rule, ollama, gemini, or openai. Defaults to AI_PROVIDER env}
        {--model= : Override model name for this run}
        {--max-projects= : Maximum project titles to send to local/Ollama model}
        {--refresh : Regenerate even when the current input hash already exists}
        {--preview : Call AI and print parsed/raw output without saving to database}
        {--dry-run : Show the input snapshot without calling AI}';

    protected $description = 'Generate and cache AI insights for the outputs visualization dashboard';

    public function handle(): int
    {
        $startedAt = now();
        $startedTs = microtime(true);
        $this->logStep('started', 'Started at: ' . $startedAt->timezone('Asia/Bangkok')->format('Y-m-d H:i:s'));

        if (! Schema::hasTable('ai_visualization_insights')) {
            $this->error('Missing ai_visualization_insights table. Run: php artisan migrate');

            return self::FAILURE;
        }

        $provider = $this->resolveProvider();
        $model = $this->resolveModel($provider);
        $snapshot = $this->projectNatureSnapshot();
        if ($snapshot['projects']->isEmpty()) {
            $this->info('No projects found for AI visualization insight.');

            return self::SUCCESS;
        }

        $maxProjects = $this->resolveMaxProjects($provider, $snapshot['projects']->count());
        $inputHash = $this->hashSnapshot($snapshot, $provider, $maxProjects);
        $this->logStep('snapshot_ready', 'Snapshot ready: ' . $snapshot['projects']->count() . ' projects, AI reads ' . min($maxProjects, $snapshot['projects']->count()));
        $existing = AiVisualizationInsight::query()
            ->where('insight_type', 'project_nature')
            ->where('scope_key', 'global')
            ->where('input_hash', $inputHash)
            ->latest('generated_at')
            ->first();

        if ($existing && ! $this->option('refresh') && ! $this->option('preview')) {
            $this->info('Current project_nature insight already exists. Use --refresh to regenerate.');
            $this->finishConsoleRun($startedTs);

            return self::SUCCESS;
        }

        $this->table(
            ['Provider', 'Model', 'Projects total', 'Projects AI reads', 'Mode', 'Hash', 'Latest update'],
            [[
                $provider,
                $model,
                number_format($snapshot['projects']->count()),
                number_format(min($maxProjects, $snapshot['projects']->count())),
                $this->option('preview') ? 'preview/no-save' : 'save',
                $inputHash,
                $snapshot['latest_updated_at'] ?: '-',
            ]]
        );

        if ($this->option('dry-run')) {
            $this->line(json_encode($this->projectNaturePortfolioFacts($snapshot, $maxProjects), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $this->finishConsoleRun($startedTs);

            return self::SUCCESS;
        }

        $apiKey = in_array($provider, ['ollama', 'rule'], true) ? '' : trim((string) config("services.{$provider}.key"));
        if (! in_array($provider, ['ollama', 'rule'], true) && $apiKey === '') {
            $envName = $provider === 'gemini' ? 'GEMINI_API_KEY' : 'OPENAI_API_KEY';
            $this->error("{$envName} is not configured.");

            return self::FAILURE;
        }

        $this->logStep('ai_request_started', 'AI request started at: ' . now()->timezone('Asia/Bangkok')->format('Y-m-d H:i:s'));
        $result = $this->generateProjectNatureInsight($provider, $apiKey, $snapshot, $maxProjects, $model);
        $this->logStep('ai_request_finished', 'AI request finished at: ' . now()->timezone('Asia/Bangkok')->format('Y-m-d H:i:s'));
        if (! $this->isValidProjectNatureInsight($result)) {
            $this->error('AI returned no usable insight.');
            $visibleKeys = collect(array_keys($result))->reject(fn ($key) => str_starts_with((string) $key, '_'))->values()->all();
            $this->warn('Decoded keys: ' . ($visibleKeys ? implode(', ', $visibleKeys) : '(none)'));
            if (isset($result['_raw_text'])) {
                $this->warn('Raw preview: ' . Str::limit((string) $result['_raw_text'], 1000));
            }
            $this->finishConsoleRun($startedTs);

            return self::FAILURE;
        }

        $this->printResultSummary($result);

        if ($this->option('preview')) {
            $this->warn('Preview mode: AI output was not saved to database.');
            $this->finishConsoleRun($startedTs);

            return self::SUCCESS;
        }

        $this->logStep('database_save_started', 'Saving AI insight to database.');
        AiVisualizationInsight::query()->updateOrCreate(
            [
                'insight_type' => 'project_nature',
                'scope_key' => 'global',
                'input_hash' => $inputHash,
            ],
            [
                'provider' => $provider,
                'model' => $result['model'] ?? $model,
                'summary' => trim((string) ($result['summary'] ?? '')),
                'payload' => $result,
                'input_snapshot' => [
                    'project_count' => $snapshot['projects']->count(),
                    'analyzed_project_count' => min($maxProjects, $snapshot['projects']->count()),
                    'output_count' => (int) ($snapshot['output_count'] ?? 0),
                    'provider' => $provider,
                    'latest_updated_at' => $snapshot['latest_updated_at'],
                    'projects' => $snapshot['projects']->values()->all(),
                ],
                'generated_at' => now(),
            ]
        );

        $this->logStep('database_save_finished', 'Cached project_nature AI insight.');
        $this->finishConsoleRun($startedTs);

        return self::SUCCESS;
    }

    private function projectNatureSnapshot(): array
    {
        $projects = Project::query()
            ->with(['outputs' => fn ($query) => $query
                ->select(['id', 'project_id', 'type', 'title', 'year', 'project_output'])
                ->orderByDesc('year')
                ->orderByDesc('id')])
            ->select(['id', 'project_code', 'project_name_th', 'project_name_en', 'project_type', 'start_year', 'updated_at'])
            ->where(function ($query) {
                $query->whereNotNull('project_name_th')
                    ->orWhereNotNull('project_name_en');
            })
            ->orderByDesc('start_year')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'code' => trim((string) $project->project_code),
                'title_th' => trim((string) $project->project_name_th),
                'title_en' => trim((string) $project->project_name_en),
                'project_type' => trim((string) $project->project_type),
                'year' => $project->start_year,
                'updated_at' => optional($project->updated_at)->toDateTimeString(),
                'outputs' => $project->outputs
                    ->map(fn ($output) => [
                        'type_code' => $this->extractOutputTypeCode($output->type) ?? 'OTHER',
                        'type_label' => $this->outputTypeLabel($this->extractOutputTypeCode($output->type) ?? 'OTHER'),
                        'title' => trim((string) ($output->title ?? '')),
                        'year' => $output->year,
                        'detail' => trim((string) ($output->project_output ?? '')),
                    ])
                    ->filter(fn ($output) => $output['title'] !== '' || $output['detail'] !== '')
                    ->take(12)
                    ->values()
                    ->all(),
            ])
            ->filter(fn ($project) => $project['title_th'] !== '' || $project['title_en'] !== '')
            ->values();

        return [
            'projects' => $projects,
            'latest_updated_at' => (string) $projects->max('updated_at'),
            'output_count' => (int) $projects->sum(fn ($project) => count($project['outputs'] ?? [])),
        ];
    }

    private function hashSnapshot(array $snapshot, string $provider, int $maxProjects): string
    {
        $analyzedProjects = $snapshot['projects']->take($maxProjects)->values();

        return hash('sha256', json_encode([
                'version' => 6,
            'provider' => $provider,
            'analysis_project_limit' => $maxProjects,
            'total_project_count' => $snapshot['projects']->count(),
            'total_output_count_in_snapshot' => (int) ($snapshot['output_count'] ?? 0),
            'latest_updated_at' => $snapshot['latest_updated_at'],
            'projects' => $analyzedProjects->map(fn ($project) => [
                'id' => $project['id'],
                'title_th' => $project['title_th'],
                'title_en' => $project['title_en'],
                'project_type' => $project['project_type'],
                'updated_at' => $project['updated_at'],
                'outputs' => collect($project['outputs'] ?? [])
                    ->map(fn ($output) => [
                        'type_code' => $output['type_code'] ?? '',
                        'title' => $output['title'] ?? '',
                        'year' => $output['year'] ?? '',
                    ])
                    ->values()
                    ->all(),
            ])->values()->all(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function extractOutputTypeCode($rawType): ?string
    {
        $type = trim((string) ($rawType ?? ''));
        if ($type === '') {
            return null;
        }
        if (preg_match('/EA\d{3}/i', $type, $matches)) {
            return strtoupper($matches[0]);
        }

        return null;
    }

    private function outputTypeLabel(string $code): string
    {
        return match (strtoupper($code)) {
            'EA101' => 'บทความวิจัยและบทความวารสาร',
            'EA102' => 'รายงานหรือเอกสารวิชาการ',
            'EA103' => 'การนำเสนอผลงานทางวิชาการ',
            'EA104' => 'ชุดข้อมูลและฐานข้อมูล',
            'EA105' => 'เครื่องมือหรือระบบสนับสนุนการทำงาน',
            'EA106' => 'ข้อเสนอเชิงนโยบาย',
            'EA107' => 'สื่อหรือวัสดุเพื่อการเรียนรู้',
            'EA108' => 'ผลงานวิชาการที่เชื่อมกับการตัดสินใจเชิงระบบ',
            'EA109' => 'การเปลี่ยนแปลงแนวปฏิบัติ กระบวนการ หรือนโยบาย',
            'EA110' => 'จำนวนผู้เข้าร่วมกิจกรรม',
            'EA111' => 'ผลประเมินความพึงพอใจ',
            'EA112' => 'การสื่อสารหรือเผยแพร่สู่ผู้เกี่ยวข้อง',
            'EA113' => 'คำสั่งแต่งตั้งหรือบทบาทในคณะทำงาน',
            'EA114' => 'ผลงานสนับสนุนการใช้ประโยชน์เชิงนโยบาย',
            'EA115', 'EA117' => 'เครือข่ายหรือเวทีความร่วมมือ',
            'EA116' => 'โครงการวิจัยต่อเนื่อง',
            'EA118' => 'รางวัลหรือการยอมรับ',
            'EA119' => 'ตำรา คู่มือ หรือสื่อความรู้',
            'EA120' => 'คู่มือ แนวทาง หรือ instruction',
            'EA999' => 'ผลงานประเภทอื่น',
            default => strtoupper($code) ?: 'Other output',
        };
    }

    private function resolveProvider(): string
    {
        $provider = mb_strtolower(trim((string) ($this->option('provider') ?: env('AI_PROVIDER', 'rule'))), 'UTF-8');

        return in_array($provider, ['rule', 'ollama', 'gemini', 'openai'], true) ? $provider : 'rule';
    }

    private function resolveModel(string $provider): string
    {
        $override = trim((string) ($this->option('model') ?? ''));
        if ($override !== '') {
            return $override;
        }

        return match ($provider) {
            'openai' => trim((string) config('services.openai.model', 'gpt-5-mini')) ?: 'gpt-5-mini',
            'gemini' => trim((string) config('services.gemini.model', 'gemini-2.5-flash')) ?: 'gemini-2.5-flash',
            'rule' => 'rule-based-portfolio-v1',
            default => trim((string) config('services.ollama.model', 'qwen2.5:7b')) ?: 'qwen2.5:7b',
        };
    }

    private function resolveMaxProjects(string $provider, int $totalProjects): int
    {
        $maxProjectsOption = $this->option('max-projects');
        if ($maxProjectsOption !== null && $maxProjectsOption !== '') {
            return max(1, min($totalProjects, (int) $maxProjectsOption));
        }

        if ($provider === 'ollama') {
            return max(1, min($totalProjects, (int) config('services.ollama.max_projects', 220)));
        }

        return $totalProjects;
    }

    private function generateProjectNatureInsight(string $provider, string $apiKey, array $snapshot, int $maxProjects, string $model): array
    {
        return match ($provider) {
            'rule' => $this->generateWithRules($snapshot, $maxProjects, $model),
            'openai' => $this->generateWithOpenAi($apiKey, $snapshot, $maxProjects, $model),
            'gemini' => $this->generateWithGemini($apiKey, $snapshot, $maxProjects, $model),
            default => $this->generateWithOllama($snapshot, $maxProjects, $model),
        };
    }

    private function projectNaturePrompt(array $snapshot, int $maxProjects): string
    {
        $facts = $this->projectNaturePortfolioFacts($snapshot, $maxProjects);
        $factsJson = json_encode($facts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return implode("\n", [
            'You analyze aggregated portfolio facts for a Thai health policy research unit. The facts were computed from ALL included project titles and their recorded outputs.',
            'Write in the voice of a researcher and policy translation practitioner: evidence-aware, cautious, useful for strategy, and grounded in what the data shows.',
            'LANGUAGE REQUIREMENT: Write Thai only. Do not use Chinese characters. Do not mix Chinese, Japanese, or Korean text. English technical terms are allowed only when commonly used in Thai research/policy writing.',
            'Use the aggregated facts below. Do not extract or return a single project.',
            'Infer the actual nature of work from project title signals first. Then cross-check whether recorded outputs support, partially support, or leave gaps in that interpretation.',
            'Use project_type only as a lens. Do not use project_type as the source of themes.',
            'Do not overclaim. If output evidence is weak, say it needs follow-up instead of making a firm recommendation.',
            'Return one valid JSON object only. Do not copy the input list. Do not wrap in markdown.',
            'Required JSON keys: summary, section_cards, category_overview, interest_clusters, by_project_type, meta_patterns, capability_clusters, output_alignment, concise_summary, emerging_keywords, underrepresented_areas, method_signals, policy_relevance, confidence.',
            'section_cards is the most important key for the website section "ความสนใจเชิงเนื้อหาของหน่วย". Do NOT simply mirror project_type or the predefined helper themes. Infer 5-9 dominant content clusters from project titles, keywords, project_type context, and recorded outputs. Return the most prominent clusters first.',
            'Each section_cards item must have: theme_key, label, keywords, headline, work_done, reading, output_direction, next_step, evidence_note, project_count, output_count. theme_key must be a stable lowercase snake_case key you create from the inferred cluster label, e.g. "frailty_care_transition".',
            'section_cards.label should be concise and human-readable. section_cards.keywords should be 4-7 short keywords from actual project/output signals.',
            'section_cards.work_done: 2-4 Thai sentences explaining what has actually happened in this interest area, grounded in project title signals. Be specific and different for each theme.',
            'section_cards.reading: 2-3 Thai sentences interpreting the nature of the work as a researcher/policy translation practitioner. Do not use generic phrases repeatedly.',
            'section_cards.output_direction: 3-5 Thai sentences explaining the direction of recorded outputs in this theme. Use full Thai output type names from output_types_seen; do not display EA codes. Explain whether outputs look like academic evidence, policy-facing evidence, data infrastructure, implementation tool, communication platform, or practice/policy change support.',
            'section_cards.next_step: 2-4 Thai sentences about what output would help translate this interest area further. Phrase politely and strategically, e.g. "ระยะถัดไปอาจต่อยอดเป็น...", "หลักฐานชุดนี้จะมีน้ำหนักมากขึ้นหาก...". Avoid repetitive "ควรตรวจสอบว่า" in every card.',
            'section_cards.evidence_note: 1-2 cautious Thai sentences describing what the current project+output evidence can and cannot conclude. Do not claim real-world policy impact unless recorded outputs clearly support it.',
            'Bad answer patterns that are NOT acceptable: repeating "หน่วยงานได้ดำเนินการศึกษาและพัฒนา..." across cards, repeating "งานวิจัยนี้เน้นที่การสร้างหลักฐาน..." across cards, repeating the same output_direction for many cards, or saying every theme simply lacks policy recommendations.',
            'If a theme has weak evidence, explain the specific missing link for that theme instead of using a generic gap sentence.',
            'category_overview: array of 6-10 items, each has category, interest.',
            'interest_clusters: array of 5-8 clusters, each has name, keywords, project_count, output_count, output_types_seen, evidence_titles, output_evidence, interpretation, output_gap, suggested_output_followup.',
            'by_project_type: array of project type sections. Each has project_type, main_interest, dominant_themes, strategic_reading. dominant_themes is an array; each theme has title, signals, meaning.',
            'meta_patterns: array of 6-10 strategic patterns. Each has title and interpretation.',
            'capability_clusters: array. Each has capability and related_project_types.',
            'output_alignment: array. Each item has theme, project_signal, output_signal, alignment_level, what_exists, what_to_verify_next. alignment_level must be one of strong, partial, weak, unclear.',
            'concise_summary: array of 5 short Thai bullet sentences.',
            'Keep the full JSON compact and under 8500 words. Use clear Thai. Each section_cards field should be detailed enough for a dashboard card, but not essay-length.',
            'For suggested_output_followup and what_to_verify_next, phrase as policy/research translation actions, e.g. "ควรตรวจสอบว่า...", "ควรติดตามว่า...", "ควรพัฒนา output ประเภท..." instead of claiming direct impact.',
            'Keep evidence_titles short and based only on given titles. Keep output_evidence short and based only on given outputs.',
            'Do not repeat the same sentence structure across section_cards. The themes must read clearly different from one another.',
            'Do not invent output counts, output types, policy impact, or implementation impact beyond recorded outputs.',
            'Identify system-centric problems, health system engineering, technology governance, learning health system, evidence-to-policy, continuum/community care only if supported by titles and/or outputs.',
            'Example shape: {"summary":"...","section_cards":[{"theme_key":"frailty_care_transition","label":"Frailty และการดูแลต่อเนื่อง","keywords":["ผู้สูงอายุ"],"headline":"...","project_count":12,"output_count":8,"work_done":"...","reading":"...","output_direction":"...","next_step":"...","evidence_note":"..."}],"category_overview":[{"category":"Aging / Frailty","interest":"..."}],"interest_clusters":[{"name":"...","keywords":["..."],"project_count":3,"output_count":2,"output_types_seen":["บทความวิจัยและบทความวารสาร"],"evidence_titles":["..."],"output_evidence":["..."],"interpretation":"...","output_gap":"...","suggested_output_followup":"..."}],"by_project_type":[{"project_type":"01 Aging and Frailty","main_interest":"...","dominant_themes":[{"title":"Frailty & Healthy Aging","signals":["..."],"meaning":"..."}],"strategic_reading":"..."}],"meta_patterns":[{"title":"...","interpretation":"..."}],"capability_clusters":[{"capability":"Aging & Integrated Care","related_project_types":["01"]}],"output_alignment":[{"theme":"...","project_signal":"...","output_signal":"...","alignment_level":"partial","what_exists":"...","what_to_verify_next":"..."}],"concise_summary":["..."],"emerging_keywords":["..."],"underrepresented_areas":["..."],"method_signals":["..."],"policy_relevance":"...","confidence":0.7}',
            '',
            'PORTFOLIO_FACTS_JSON:',
            $factsJson ?: '{}',
        ]);
    }

    private function projectNaturePortfolioFacts(array $snapshot, int $maxProjects): array
    {
        $projects = collect($snapshot['projects'])->take($maxProjects)->values();
        $themeDefinitions = collect([
            ['key' => 'aging', 'name' => 'Aging / Frailty', 'keywords' => ['ผู้สูงอายุ', 'สูงอายุ', 'frailty', 'เปราะบาง', 'aging', 'geriatric', 'สะโพก', 'hip', 'delirium', 'ฟื้นฟู', 'transitional', 'intermediate']],
            ['key' => 'hta', 'name' => 'HTA / Economics', 'keywords' => ['คุ้มค่า', 'ต้นทุน', 'เศรษฐศาสตร์', 'cost', 'economic', 'budget', 'สิทธิประโยชน์', 'reimbursement', 'ความพร้อม']],
            ['key' => 'access_population', 'name' => 'Access / Population', 'keywords' => ['เข้าถึง', 'หลักประกัน', 'ข้ามเพศ', 'pmac', 'pm 2.5', 'พระสงฆ์', 'uhc', 'global', 'urban', 'สร้างเสริมสุขภาพ', 'ป้องกันโรค']],
            ['key' => 'device_innovation', 'name' => 'Device / Innovation', 'keywords' => ['รากฟัน', 'กระดูกเทียม', 'เท้าเทียม', 'insulin pump', 'อุปกรณ์', 'implant', '3 มิติ', '3d', 'นวัตกรรม']],
            ['key' => 'data_rwe', 'name' => 'Data / RWE', 'keywords' => ['real-world', 'real world', 'rwe', 'cohort', 'registry', 'ฐานข้อมูล', 'คลังเก็บข้อมูล', 'อิเล็กทรอนิกส์', 'เวชระเบียน', 'longitudinal', 'ติดตามผล']],
            ['key' => 'ai_digital', 'name' => 'AI / Digital', 'keywords' => ['ปัญญาประดิษฐ์', ' ai', '(ai)', 'telemedicine', 'แพทย์ทางไกล', 'digital', 'ระบบรายงาน', 'แสดงข้อมูล']],
            ['key' => 'drug_rdu', 'name' => 'Drug / RDU', 'keywords' => ['ใช้ยาอย่างสมเหตุผล', 'metformin', 'สั่งใช้ยา', 'ยาที่มีโอกาส', 'ยา metformin', 'medication', 'prescribing', 'rdu']],
        ]);
        $matchesKeywords = static function (array $project, array $keywords): bool {
            $text = mb_strtolower(trim(($project['title_th'] ?? '') . ' ' . ($project['title_en'] ?? '')), 'UTF-8');

            return $text !== '' && Str::contains($text, $keywords);
        };
        $outputTypes = $projects
            ->flatMap(fn ($project) => collect($project['outputs'] ?? []))
            ->groupBy(fn ($output) => (string) ($output['type_code'] ?? 'OTHER'))
            ->map(fn ($rows, $code) => [
                'code' => $code,
                'label' => $this->outputTypeLabel((string) $code),
                'count' => $rows->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();

        return [
            'project_count_total' => $snapshot['projects']->count(),
            'project_count_analyzed' => $projects->count(),
            'output_count_analyzed' => (int) $projects->sum(fn ($project) => count($project['outputs'] ?? [])),
            'latest_updated_at' => $snapshot['latest_updated_at'] ?? null,
            'overall_output_types' => $outputTypes,
            'project_records' => $projects
                ->map(fn ($project) => [
                    'code' => $project['code'] ?? '',
                    'year' => $project['year'] ?? '',
                    'project_type' => $project['project_type'] ?? '',
                    'title_th' => Str::limit((string) ($project['title_th'] ?? ''), 180, '...'),
                    'title_en' => Str::limit((string) ($project['title_en'] ?? ''), 180, '...'),
                    'output_count' => count($project['outputs'] ?? []),
                    'output_types_seen' => collect($project['outputs'] ?? [])
                        ->groupBy(fn ($output) => (string) ($output['type_code'] ?? 'OTHER'))
                        ->map(fn ($rows, $code) => [
                            'code' => $code,
                            'label' => $this->outputTypeLabel((string) $code),
                            'count' => $rows->count(),
                        ])
                        ->sortByDesc('count')
                        ->values()
                        ->all(),
                    'output_examples' => collect($project['outputs'] ?? [])
                        ->take(4)
                        ->map(fn ($output) => [
                            'type_code' => $output['type_code'] ?? '',
                            'type_label' => $output['type_label'] ?? $this->outputTypeLabel((string) ($output['type_code'] ?? 'OTHER')),
                            'year' => $output['year'] ?? '',
                            'title' => Str::limit((string) (($output['title'] ?? '') ?: ($output['detail'] ?? '')), 150, '...'),
                        ])
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all(),
            'themes' => $themeDefinitions->map(function (array $theme) use ($projects, $matchesKeywords) {
                $themeProjects = $projects
                    ->filter(fn ($project) => $matchesKeywords((array) $project, $theme['keywords']))
                    ->values();
                $themeOutputs = $themeProjects
                    ->flatMap(fn ($project) => collect($project['outputs'] ?? [])->map(fn ($output) => array_merge($output, [
                        'project_code' => $project['code'] ?? '',
                        'project_title' => $project['title_th'] ?: ($project['title_en'] ?? ''),
                    ])))
                    ->values();

                return [
                    'theme_key' => $theme['key'],
                    'theme' => $theme['name'],
                    'keywords' => $theme['keywords'],
                    'project_count' => $themeProjects->count(),
                    'output_count' => $themeOutputs->count(),
                    'output_types_seen' => $themeOutputs
                        ->groupBy(fn ($output) => (string) ($output['type_code'] ?? 'OTHER'))
                        ->map(fn ($rows, $code) => [
                            'code' => $code,
                            'label' => $this->outputTypeLabel((string) $code),
                            'count' => $rows->count(),
                        ])
                        ->sortByDesc('count')
                        ->values()
                        ->all(),
                    'project_examples' => $themeProjects
                        ->take(6)
                        ->map(fn ($project) => [
                            'code' => $project['code'] ?? '',
                            'year' => $project['year'] ?? '',
                            'project_type' => $project['project_type'] ?? '',
                            'title' => Str::limit((string) (($project['title_th'] ?? '') ?: ($project['title_en'] ?? '')), 180, '...'),
                        ])
                        ->values()
                        ->all(),
                    'output_examples' => $themeOutputs
                        ->take(6)
                        ->map(fn ($output) => [
                            'project_code' => $output['project_code'] ?? '',
                            'type_code' => $output['type_code'] ?? '',
                            'type_label' => $output['type_label'] ?? $this->outputTypeLabel((string) ($output['type_code'] ?? 'OTHER')),
                            'year' => $output['year'] ?? '',
                            'title' => Str::limit((string) ($output['title'] ?? ''), 180, '...'),
                        ])
                        ->values()
                        ->all(),
                ];
            })
                ->sortByDesc('project_count')
                ->values()
                ->all(),
            'project_type_counts' => $projects
                ->groupBy(fn ($project) => trim((string) ($project['project_type'] ?? '')) ?: 'ไม่ระบุ')
                ->map(fn ($rows, $type) => ['project_type' => $type, 'count' => $rows->count()])
                ->sortByDesc('count')
                ->values()
                ->all(),
        ];
    }

    private function generateWithGemini(string $apiKey, array $snapshot, int $maxProjects, string $model): array
    {
        $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
            ->acceptJson()
            ->timeout(120)
            ->post(rtrim((string) config('services.gemini.base_url'), '/') . '/models/' . $model . ':generateContent', [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [['text' => $this->projectNaturePrompt($snapshot, $maxProjects)]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.15,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            $this->error('Gemini request failed: ' . $response->status() . ' ' . Str::limit($response->body(), 300));

            return [];
        }

        $text = collect($response->json('candidates.0.content.parts', []))
            ->map(fn ($part) => $part['text'] ?? '')
            ->filter()
            ->implode("\n");

        return array_merge($this->decodeJsonObject($text), ['model' => $model]);
    }

    private function generateWithOpenAi(string $apiKey, array $snapshot, int $maxProjects, string $model): array
    {
        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(120)
            ->post(rtrim((string) config('services.openai.base_url'), '/') . '/responses', [
                'model' => $model,
                'instructions' => 'Return only valid JSON.',
                'input' => $this->projectNaturePrompt($snapshot, $maxProjects),
            ]);

        if ($response->failed()) {
            $this->error('OpenAI request failed: ' . $response->status() . ' ' . Str::limit($response->body(), 300));

            return [];
        }

        return array_merge($this->decodeJsonObject($this->extractOutputText($response->json())), ['model' => $model]);
    }

    private function generateWithOllama(array $snapshot, int $maxProjects, string $model): array
    {
        $baseUrl = rtrim((string) config('services.ollama.base_url', 'http://127.0.0.1:11434'), '/');
        $payload = [
            'model' => $model,
            'prompt' => $this->projectNaturePrompt($snapshot, $maxProjects),
            'stream' => true,
            'format' => 'json',
            'options' => [
                'temperature' => 0.1,
                'num_ctx' => 24576,
                'num_predict' => 6000,
            ],
        ];

        $text = $this->streamOllamaGenerate($baseUrl . '/api/generate', $payload, $model, $snapshot, $maxProjects);
        if ($text === '') {
            return [];
        }

        return array_merge($this->decodeJsonObject($text), [
            'model' => $model,
            '_raw_text' => $text,
        ]);
    }

    private function generateWithRules(array $snapshot, int $maxProjects, string $model): array
    {
        $facts = $this->projectNaturePortfolioFacts($snapshot, $maxProjects);
        $projects = collect($facts['project_records'] ?? []);
        $clusters = collect($this->ruleClusterDefinitions())
            ->map(function (array $cluster) use ($projects) {
                $matchedProjects = $projects
                    ->map(function (array $project) use ($cluster) {
                        $score = $this->scoreRuleProjectCluster($project, $cluster);

                        return array_merge($project, ['_rule_score' => $score]);
                    })
                    ->filter(fn (array $project) => (float) ($project['_rule_score'] ?? 0) >= 4.0)
                    ->sortByDesc('_rule_score')
                    ->values();

                $outputs = $matchedProjects
                    ->flatMap(fn (array $project) => collect($project['output_examples'] ?? [])
                        ->map(fn (array $output) => array_merge($output, [
                            'project_code' => $project['code'] ?? '',
                            'project_title' => ($project['title_th'] ?? '') ?: ($project['title_en'] ?? ''),
                        ])))
                    ->values();

                $outputTypes = $outputs
                    ->groupBy(fn (array $output) => (string) ($output['type_code'] ?? 'OTHER'))
                    ->map(fn (Collection $rows, string $code) => [
                        'code' => $code,
                        'label' => $this->outputTypeLabel($code),
                        'count' => $rows->count(),
                    ])
                    ->sortByDesc('count')
                    ->values()
                    ->all();

                $score = (float) $matchedProjects->sum('_rule_score')
                    + ($matchedProjects->count() * 3)
                    + ($outputs->count() * 0.7)
                    + (collect($outputTypes)->whereIn('code', ['EA106', 'EA108', 'EA109', 'EA112', 'EA114', 'EA115'])->sum('count') * 0.8);

                return array_merge($cluster, [
                    'project_count' => $matchedProjects->count(),
                    'output_count' => $outputs->count(),
                    'score' => round($score, 2),
                    'output_types_seen' => $outputTypes,
                    'evidence_titles' => $matchedProjects
                        ->take(5)
                        ->map(fn (array $project) => Str::limit((string) (($project['title_th'] ?? '') ?: ($project['title_en'] ?? '')), 150, '...'))
                        ->values()
                        ->all(),
                    'output_evidence' => $outputs
                        ->take(5)
                        ->map(fn (array $output) => trim(($output['type_label'] ?? $this->outputTypeLabel((string) ($output['type_code'] ?? 'OTHER'))) . ': ' . Str::limit((string) ($output['title'] ?? ''), 120, '...')))
                        ->filter()
                        ->values()
                        ->all(),
                    'projects' => $matchedProjects->take(8)->values()->all(),
                ]);
            })
            ->filter(fn (array $cluster) => (int) ($cluster['project_count'] ?? 0) > 0)
            ->sortByDesc('score')
            ->take(9)
            ->values();

        $totalProjects = max(1, (int) ($facts['project_count_analyzed'] ?? $projects->count()));
        $sectionCards = $clusters
            ->map(fn (array $cluster) => $this->buildRuleSectionCard($cluster, $totalProjects))
            ->values()
            ->all();

        $interestClusters = $clusters
            ->map(fn (array $cluster) => [
                'name' => $cluster['label'],
                'keywords' => collect($cluster['keywords'])->take(7)->values()->all(),
                'project_count' => (int) $cluster['project_count'],
                'output_count' => (int) $cluster['output_count'],
                'output_types_seen' => collect($cluster['output_types_seen'])->pluck('label')->values()->all(),
                'evidence_titles' => $cluster['evidence_titles'],
                'output_evidence' => $cluster['output_evidence'],
                'interpretation' => $cluster['reading'],
                'output_gap' => $this->ruleOutputGap($cluster),
                'suggested_output_followup' => $this->ruleNextStep($cluster),
            ])
            ->values()
            ->all();

        $byProjectType = $projects
            ->groupBy(fn (array $project) => trim((string) ($project['project_type'] ?? '')) ?: 'ไม่ระบุกลุ่ม')
            ->map(function (Collection $rows, string $type) use ($clusters) {
                $dominant = $clusters
                    ->map(function (array $cluster) use ($rows) {
                        $count = $rows
                            ->filter(fn (array $project) => $this->scoreRuleProjectCluster($project, $cluster) > 0)
                            ->count();

                        return [
                            'title' => $cluster['label'],
                            'count' => $count,
                            'signals' => collect($cluster['keywords'])->take(4)->values()->all(),
                            'meaning' => $cluster['reading'],
                        ];
                    })
                    ->filter(fn (array $row) => (int) $row['count'] > 0)
                    ->sortByDesc('count')
                    ->take(3)
                    ->values()
                    ->all();

                return [
                    'project_type' => $type,
                    'main_interest' => collect($dominant)->pluck('title')->take(2)->implode(' และ ') ?: 'อ่านจากชื่อโครงการและ output รายรายการ',
                    'dominant_themes' => $dominant,
                    'strategic_reading' => 'กลุ่มนี้มี ' . number_format($rows->count()) . ' โครงการ จึงควรอ่านร่วมกับ cluster เด่นและชนิด output ที่เกิดขึ้นจริง ไม่สรุปจาก project_type เพียงอย่างเดียว',
                ];
            })
            ->values()
            ->all();

        $topLabels = $clusters->take(3)->pluck('label')->values();
        $summary = $this->ruleEditorialSummary($topLabels);

        return [
            'summary' => $summary,
            'section_cards' => $sectionCards,
            'category_overview' => $clusters
                ->map(fn (array $cluster) => [
                    'category' => $cluster['label'],
                    'interest' => $cluster['reading'],
                ])
                ->values()
                ->all(),
            'interest_clusters' => $interestClusters,
            'by_project_type' => $byProjectType,
            'meta_patterns' => $this->ruleMetaPatterns($clusters, $facts),
            'capability_clusters' => $clusters
                ->map(fn (array $cluster) => [
                    'capability' => $cluster['label'],
                    'related_project_types' => collect($cluster['projects'] ?? [])->pluck('project_type')->filter()->unique()->take(4)->values()->all(),
                ])
                ->values()
                ->all(),
            'output_alignment' => $clusters
                ->map(fn (array $cluster) => [
                    'theme' => $cluster['label'],
                    'project_signal' => number_format((int) $cluster['project_count']) . ' โครงการ / keyword: ' . collect($cluster['keywords'])->take(4)->implode(', '),
                    'output_signal' => number_format((int) $cluster['output_count']) . ' output; เด่นที่ ' . collect($cluster['output_types_seen'])->take(3)->pluck('label')->implode(', '),
                    'alignment_level' => $this->ruleAlignmentLevel($cluster),
                    'what_exists' => $this->ruleOutputDirection($cluster),
                    'what_to_verify_next' => $this->ruleNextStep($cluster),
                ])
                ->values()
                ->all(),
            'concise_summary' => $clusters
                ->take(5)
                ->map(fn (array $cluster) => $cluster['label'] . ' มี ' . number_format((int) $cluster['project_count']) . ' โครงการ และ ' . number_format((int) $cluster['output_count']) . ' output')
                ->values()
                ->all(),
            'emerging_keywords' => $clusters->flatMap(fn (array $cluster) => $cluster['keywords'])->unique()->take(14)->values()->all(),
            'underrepresented_areas' => $clusters
                ->filter(fn (array $cluster) => (int) ($cluster['output_count'] ?? 0) < max(2, (int) floor(((int) ($cluster['project_count'] ?? 0)) / 2)))
                ->pluck('label')
                ->values()
                ->all(),
            'method_signals' => ['keyword weighting', 'project-output alignment', 'output-type evidence', 'portfolio clustering'],
            'policy_relevance' => 'เป็นการอ่านภาพรวม portfolio เพื่อช่วยชี้ประเด็นเด่นและช่องว่างของ output ก่อนให้ทีมตรวจทานเชิงเนื้อหา',
            'confidence' => 0.72,
            'model' => $model,
            'provider_note' => 'rule-based fallback; no external AI call',
        ];
    }

    private function ruleClusterDefinitions(): array
    {
        return [
            [
                'key' => 'frailty_care_transition',
                'label' => 'Aging / Frailty and Care Continuum',
                'keywords' => ['ผู้สูงอายุ', 'สูงอายุ', 'frailty', 'เปราะบาง', 'geriatric', 'hip', 'สะโพก', 'delirium', 'ฟื้นฟู', 'intermediate', 'transitional', 'caregiver'],
                'boost_project_types' => ['aging', 'frailty'],
                'reading' => 'เป็นแกนงานที่มองผู้สูงอายุเป็นระบบการดูแลต่อเนื่อง ตั้งแต่การคัดกรองความเปราะบาง การฟื้นฟู ไปจนถึงภาระผู้ดูแลและการกลับสู่ชุมชน',
            ],
            [
                'key' => 'economic_evaluation_reimbursement',
                'label' => 'HTA / Health Economics and Reimbursement',
                'keywords' => ['คุ้มค่า', 'ต้นทุน', 'เศรษฐศาสตร์', 'cost', 'economic', 'budget', 'budget impact', 'utility', 'reimbursement', 'สิทธิประโยชน์', 'benefit package', 'hta'],
                'boost_project_types' => ['hta', 'health systems'],
                'reading' => 'เป็นงานที่แปลงหลักฐานทางคลินิกและระบบบริการให้เป็นข้อมูลประกอบการตัดสินใจเชิงงบประมาณ สิทธิประโยชน์ และความพร้อมของระบบ',
            ],
            [
                'key' => 'technology_device_adoption',
                'label' => 'Device / Innovation and Technology Adoption',
                'keywords' => ['นวัตกรรม', 'เทคโนโลยี', 'device', 'implant', 'รากฟัน', 'กระดูกเทียม', 'เท้าเทียม', 'insulin pump', '3d', 'ดิจิทัลแพลตฟอร์ม', 'horizon'],
                'boost_project_types' => ['hta', 'other'],
                'reading' => 'กลุ่มนี้ไม่ได้ถามเพียงว่าเทคโนโลยีใหม่ดีหรือไม่ แต่ถามต่อว่าระบบบริการพร้อมรับ ใช้ ติดตาม และจ่ายอย่างไร',
            ],
            [
                'key' => 'data_rwe_learning_system',
                'label' => 'Data / RWE and Learning Health System',
                'keywords' => ['real-world', 'real world', 'rwe', 'cohort', 'registry', 'ฐานข้อมูล', 'คลังเก็บข้อมูล', 'เวชระเบียน', 'emr', 'longitudinal', 'ติดตามผล', 'dashboard'],
                'boost_project_types' => ['real-world', 'rwe', 'cohort'],
                'reading' => 'เป็นชั้นโครงสร้างพื้นฐานของหลักฐาน ใช้ข้อมูลบริการจริงเพื่อติดตาม outcome ความแปรปรวนของบริการ และสร้าง feedback loop กลับสู่ระบบสุขภาพ',
            ],
            [
                'key' => 'ai_digital_workflow',
                'label' => 'AI / Digital Health Workflow',
                'keywords' => ['ปัญญาประดิษฐ์', ' ai', '(ai)', 'artificial intelligence', 'digital', 'telemedicine', 'แพทย์ทางไกล', 'ระบบรายงาน', 'แสดงข้อมูล', 'cxr', 'mammogram'],
                'boost_project_types' => ['real-world', 'hta', 'other'],
                'reading' => 'AI และ digital health ใน portfolio นี้ควรถูกอ่านในฐานะเทคโนโลยีที่ต้องฝังใน workflow จริง ทั้งด้านข้อมูล ความปลอดภัย การยอมรับของผู้ใช้ และการบำรุงรักษา',
            ],
            [
                'key' => 'drug_governance_safety',
                'label' => 'Drug / RDU and Medication Safety',
                'keywords' => ['ใช้ยาอย่างสมเหตุผล', 'rdu', 'metformin', 'medication', 'prescribing', 'nsaids', 'สั่งใช้ยา', 'ลดการใช้ยา', 'ยาที่ไม่เหมาะสม'],
                'boost_project_types' => ['rdu'],
                'reading' => 'เป็นงาน policy-in-practice ที่เชื่อมข้อมูลการใช้ยากับพฤติกรรมการสั่งใช้ ความปลอดภัยของผู้ป่วย และกลไกกำกับคุณภาพในหน่วยบริการ',
            ],
            [
                'key' => 'access_equity_population_health',
                'label' => 'Access / Equity and Population Health',
                'keywords' => ['เข้าถึง', 'หลักประกัน', 'uhc', 'equity', 'ความเป็นธรรม', 'ประชากร', 'transgender', 'พระสงฆ์', 'pm 2.5', 'pm2.5', 'urban', 'prevention', 'ป้องกันโรค'],
                'boost_project_types' => ['global', 'health systems'],
                'reading' => 'กลุ่มนี้ขยายจากผลลัพธ์ทางคลินิกไปสู่สิทธิ ความเป็นธรรม ประชากรเฉพาะ และวาระสุขภาพระดับประเทศหรือนานาชาติ',
            ],
            [
                'key' => 'global_policy_dialogue',
                'label' => 'Global Health / Policy Dialogue and Agenda Setting',
                'keywords' => ['pmac', 'global health', 'conference', 'ประชุมวิชาการนานาชาติ', 'uhc conference', 'policy dialogue', 'field trip', 'side meeting'],
                'boost_project_types' => ['global'],
                'reading' => 'เป็นพื้นที่สร้างวาระและเครือข่ายเชิงนโยบาย มากกว่างานวิจัยเดี่ยว จึงควรอ่าน output ด้านเวที การสื่อสาร และการเชื่อมผู้มีส่วนได้ส่วนเสียเป็นหลัก',
            ],
            [
                'key' => 'implementation_capacity_building',
                'label' => 'Implementation / Capacity Building and Practice Change',
                'keywords' => ['implementation', 'capacity building', 'อบรม', 'ถ่ายทอด', 'คู่มือ', 'แนวทาง', 'มาตรการ', 'service model', 'readiness', 'workflow', 'เปลี่ยนแปลงแนวปฏิบัติ'],
                'boost_project_types' => ['aging', 'hta', 'rdu', 'other'],
                'reading' => 'เป็นสัญญาณว่างานไม่ได้หยุดที่การผลิตหลักฐาน แต่พยายามทำให้หลักฐานถูกใช้ผ่านคู่มือ การอบรม มาตรการ และการปรับกระบวนการทำงาน',
            ],
            [
                'key' => 'clinical_outcomes_chronic_disease',
                'label' => 'Clinical Outcomes / Chronic Disease and Service Outcomes',
                'keywords' => ['clinical outcomes', 'ผลลัพธ์ทางคลินิก', 'เบาหวาน', 'diabetes', 'ความดัน', 'kidney', 'ckd', 'มะเร็ง', 'cancer', 'gastric', 'mortality', 'safety', 'effectiveness'],
                'boost_project_types' => ['real-world', 'aging'],
                'reading' => 'กลุ่มนี้ใช้โจทย์ทางคลินิกเป็นประตูเข้าสู่คำถามเชิงระบบ เช่น outcome จริง ความปลอดภัย ต้นทุน และการปรับบริการในบริบทไทย',
            ],
        ];
    }

    private function scoreRuleProjectCluster(array $project, array $cluster): float
    {
        $title = $this->normalizeRuleText(($project['title_th'] ?? '') . ' ' . ($project['title_en'] ?? ''));
        $projectType = $this->normalizeRuleText((string) ($project['project_type'] ?? ''));
        $outputs = collect($project['output_examples'] ?? []);
        $outputText = $this->normalizeRuleText($outputs
            ->map(fn (array $output) => (string) ($output['title'] ?? ''))
            ->implode(' '));

        $score = 0.0;
        $contentScore = 0.0;
        foreach (($cluster['keywords'] ?? []) as $keyword) {
            $needle = $this->normalizeRuleText((string) $keyword);
            if ($needle === '') {
                continue;
            }
            if ($this->ruleTextContains($title, $needle)) {
                $contentScore += 4.0;
            }
            if ($this->ruleTextContains($outputText, $needle)) {
                $contentScore += 1.8;
            }
        }

        $score += $contentScore;
        foreach (($cluster['boost_project_types'] ?? []) as $keyword) {
            $needle = $this->normalizeRuleText((string) $keyword);
            if ($contentScore > 0 && $needle !== '' && $this->ruleTextContains($projectType, $needle)) {
                $score += 2.2;
            }
        }

        if ($outputs->isNotEmpty()) {
            $score += min(3.0, $outputs->count() * 0.25);
        }

        return $score;
    }

    private function ruleTextContains(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return false;
        }

        if (preg_match('/^[a-z0-9][a-z0-9\s.+-]*$/u', $needle) === 1) {
            $parts = preg_split('/\s+/u', trim($needle)) ?: [];
            if (count($parts) === 1 && mb_strlen($needle, 'UTF-8') <= 3) {
                return preg_match('/(?<![a-z0-9])' . preg_quote($needle, '/') . '(?![a-z0-9])/u', $haystack) === 1;
            }
        }

        if (mb_strlen($needle, 'UTF-8') <= 2 && preg_match('/[ก-๙]/u', $needle) === 1) {
            return false;
        }

        return Str::contains($haystack, $needle);
    }

    private function normalizeRuleText(string $text): string
    {
        return Str::of($text)
            ->lower()
            ->replace(['–', '—', '-', '/', '(', ')', '[', ']', ':', ';', ',', '.', "\n", "\r", "\t"], ' ')
            ->squish()
            ->toString();
    }

    private function buildRuleSectionCard(array $cluster, int $totalProjects): array
    {
        $projectCount = (int) ($cluster['project_count'] ?? 0);
        $outputCount = (int) ($cluster['output_count'] ?? 0);
        $share = $totalProjects > 0 ? round(($projectCount / $totalProjects) * 100, 1) : 0.0;
        $keywords = collect($cluster['keywords'] ?? [])->take(7)->values()->all();

        return [
            'theme_key' => $cluster['key'],
            'label' => $cluster['label'],
            'keywords' => $keywords,
            'headline' => $this->ruleHeadline($cluster, $projectCount, $share),
            'project_count' => $projectCount,
            'output_count' => $outputCount,
            'work_done' => $this->ruleWorkDone($cluster, $projectCount),
            'reading' => $this->ruleStrategicReading($cluster, $share),
            'output_direction' => $this->ruleOutputDirection($cluster),
            'next_step' => $this->ruleNextStep($cluster),
            'evidence_note' => '',
        ];
    }

    private function ruleHeadline(array $cluster, int $projectCount, float $share): string
    {
        return match ((string) ($cluster['key'] ?? '')) {
            'frailty_care_transition' => 'งานผู้สูงอายุของหน่วยกำลังขยับจากการมองโรคเดี่ยว ไปสู่การออกแบบระบบดูแลต่อเนื่อง ครอบคลุม ' . number_format($projectCount) . ' โครงการ หรือประมาณ ' . $share . '% ของ portfolio ที่อ่านได้',
            'economic_evaluation_reimbursement' => 'แกน HTA / economics ทำหน้าที่เป็นสะพานระหว่างหลักฐานทางคลินิก งบประมาณ และการตัดสินใจเรื่องสิทธิประโยชน์ ครอบคลุม ' . number_format($projectCount) . ' โครงการ',
            'clinical_outcomes_chronic_disease' => 'โจทย์ clinical outcomes ช่วยให้หน่วยอ่านภาระโรคเรื้อรังผ่านผลลัพธ์จริงของผู้ป่วยและบริการ ไม่ใช่ผ่านจำนวนผู้ป่วยอย่างเดียว ครอบคลุม ' . number_format($projectCount) . ' โครงการ',
            'access_equity_population_health' => 'กลุ่ม Access / Equity ทำให้เห็นว่างานของหน่วยไม่ได้อยู่เฉพาะในโรงพยาบาล แต่แตะคำถามเรื่องสิทธิ ความเป็นธรรม และประชากรเฉพาะ ครอบคลุม ' . number_format($projectCount) . ' โครงการ',
            'technology_device_adoption' => 'งานนวัตกรรมและอุปกรณ์แพทย์ถูกวางในมุมการนำเข้า ใช้จริง และติดตามผลในระบบบริการ มากกว่าการมองเทคโนโลยีเป็นวัตถุใหม่เพียงอย่างเดียว',
            'implementation_capacity_building' => 'กลุ่ม implementation ชี้ให้เห็นความพยายามเปลี่ยนหลักฐานให้กลายเป็นทักษะ เครื่องมือ และแนวทางทำงานที่ทีมบริการใช้ต่อได้',
            'data_rwe_learning_system' => 'Data / RWE เป็นฐานให้หน่วยมองระบบสุขภาพแบบเรียนรู้จากข้อมูลจริง ทั้งผลลัพธ์บริการ ความต่อเนื่อง และช่องว่างที่ข้อมูล routine อาจตอบได้',
            'global_policy_dialogue' => 'Global Health / policy dialogue เป็นบทบาทด้านการตั้งวาระและเชื่อมเครือข่าย มากกว่าการผลิตงานวิจัยรายชิ้นเพียงอย่างเดียว',
            'ai_digital_workflow' => 'AI / Digital Health ถูกอ่านในฐานะ workflow ใหม่ของบริการสุขภาพ ต้องดูทั้งข้อมูล ผู้ใช้ ความปลอดภัย และการดูแลหลังนำไปใช้',
            'drug_governance_safety' => 'Drug / RDU สะท้อนงานกำกับคุณภาพการใช้ยาในชีวิตจริง ตั้งแต่ prescribing behavior ไปจนถึง feedback loop ของระบบบริการ',
            default => $cluster['label'] . ' เป็นทิศทางงานที่เห็นชัดใน portfolio ของหน่วย ครอบคลุม ' . number_format($projectCount) . ' โครงการ หรือประมาณ ' . $share . '% ของโครงการที่อ่านได้',
        };
    }

    private function ruleEditorialSummary(Collection $topLabels): string
    {
        $labels = $topLabels->filter()->values();
        if ($labels->isEmpty()) {
            return 'Portfolio นี้กำลังบอกใบ้ว่าหน่วยมีหลายโจทย์ที่พร้อมต่อยอด แต่ยังต้องจัดกลุ่มหลักฐานให้เห็นทิศทางเชิงนโยบายชัดขึ้น';
        }

        $lead = (string) $labels->get(0);
        $second = (string) $labels->get(1, '');
        $third = (string) $labels->get(2, '');

        if (Str::contains($lead, 'Aging / Frailty')) {
            return 'จากภาวะเปราะบางของผู้สูงอายุ สู่โจทย์เชิงระบบบริการสุขภาพ: ชุดโครงการและผลงานที่บันทึกไว้ของหน่วยสะท้อนว่างานด้าน Aging / Frailty ไม่ควรถูกมองเป็นเพียงงานเฉพาะโรคหรือเฉพาะกลุ่มประชากร แต่เป็นฐานหลักฐานสำคัญสำหรับการออกแบบระบบดูแลผู้สูงอายุแบบต่อเนื่องและบูรณาการ เมื่อนำมาอ่านร่วมกับงาน HTA เศรษฐศาสตร์สุขภาพ และผลลัพธ์บริการ จะช่วยยกระดับคำถามจาก “ผู้สูงอายุมีภาวะเปราะบางอย่างไร” ไปสู่คำถามเชิงนโยบายว่า ระบบสุขภาพควรจัดลำดับความสำคัญ จัดสรรทรัพยากร และออกแบบรูปแบบบริการสำหรับผู้สูงอายุอย่างไรให้ตอบโจทย์ทั้งประสิทธิผล ความคุ้มค่า และความเป็นธรรม';
        }

        if (Str::contains($lead, 'HTA')) {
            return 'เมื่อหลักฐานต้องตอบคำถามเรื่องงบประมาณและสิทธิประโยชน์: จุดเด่นของ portfolio อยู่ที่การเชื่อม HTA / Health Economics กับโจทย์บริการจริง ทำให้งานวิจัยมีน้ำหนักต่อการตัดสินใจมากกว่าการสรุปผลทางวิชาการเพียงอย่างเดียว';
        }

        if (Str::contains($lead, 'Clinical Outcomes')) {
            return 'ภาระโรคเรื้อรังกำลังถูกอ่านผ่านผลลัพธ์จริงของผู้ป่วย: งานกลุ่ม Clinical Outcomes เปิดทางให้หน่วยเชื่อมข้อมูลคลินิกกับคำถามเชิงนโยบาย ทั้งคุณภาพบริการ ต้นทุน และช่องว่างของการดูแล';
        }

        return 'ภาพรวม portfolio กำลังเผยแกนงานที่น่าจับตา: '
            . $labels->take(3)->implode(', ')
            . ' ไม่ได้เป็นเพียงหมวดงานวิจัย แต่เป็นชุดประเด็นที่สามารถเล่าต่อเป็นทิศทางนโยบายและการใช้ประโยชน์ของหน่วยได้';
    }

    private function ruleWorkDone(array $cluster, int $projectCount): string
    {
        $examples = collect($cluster['evidence_titles'] ?? [])->take(2)->filter()->implode(' / ');
        $examplesText = $examples !== '' ? ' ตัวอย่างโจทย์ที่เห็นได้คือ ' . $examples : '';

        return match ((string) ($cluster['key'] ?? '')) {
            'frailty_care_transition' => 'ใน portfolio มี ' . number_format($projectCount) . ' โครงการที่วนอยู่กับภาวะเปราะบาง การฟื้นฟู การดูแลหลังเจ็บป่วย และภาระของผู้ดูแล' . $examplesText . ' เมื่อนำมามองรวมกัน งานกลุ่มนี้กำลังเล่าเรื่องระบบดูแลผู้สูงอายุทั้งเส้นทาง ไม่ใช่เฉพาะ intervention รายจุด',
            'economic_evaluation_reimbursement' => 'โครงการในกลุ่มนี้วางคำถามเรื่องความคุ้มค่า ต้นทุน ผลกระทบงบประมาณ และความเป็นไปได้ของการนำเทคโนโลยีหรือบริการเข้าสู่ระบบ' . $examplesText . ' สิ่งที่เกิดขึ้นจึงเป็นฐานข้อมูลสำหรับการตัดสินใจ ไม่ใช่เพียงการประเมินเชิงวิชาการ',
            'clinical_outcomes_chronic_disease' => 'งานกลุ่มนี้จับผลลัพธ์ของโรคเรื้อรังและบริการจริง เช่น เบาหวาน โรคไต มะเร็ง ความปลอดภัย และประสิทธิผลของการดูแล' . $examplesText . ' จุดเด่นคือใช้โจทย์คลินิกเป็นทางเข้าไปสู่คำถามเชิงระบบ',
            'access_equity_population_health' => 'กลุ่มนี้รวมโครงการที่ถามว่าใครเข้าถึงบริการ ใครยังตกหล่น และระบบสุขภาพควรตอบประชากรเฉพาะอย่างไร' . $examplesText . ' จึงเป็นพื้นที่ที่เชื่อมหลักฐานกับความเป็นธรรมและสิทธิในระบบสุขภาพ',
            'technology_device_adoption' => 'งานที่พบเกี่ยวกับอุปกรณ์ เทคโนโลยี และนวัตกรรมถูกผูกกับคำถามเรื่อง adoption readiness และการใช้จริงในบริการ' . $examplesText . ' ภาพรวมจึงไม่ใช่แค่การพิสูจน์ว่าเทคโนโลยีทำงานได้ แต่ถามว่าระบบควรรับมืออย่างไร',
            'implementation_capacity_building' => 'โครงการในกลุ่มนี้เน้นการแปลงความรู้ไปเป็นมาตรการ การอบรม คู่มือ หรือกระบวนการทำงาน' . $examplesText . ' เป็นสัญญาณว่างานของหน่วยเริ่มเดินจาก evidence generation ไปสู่การเปลี่ยนวิธีทำงานในพื้นที่จริง',
            'data_rwe_learning_system' => 'กลุ่ม Data / RWE รวมงานฐานข้อมูล cohort registry dashboard และการติดตามผลจากบริการจริง' . $examplesText . ' สิ่งเหล่านี้เป็นทุนสำคัญของระบบสุขภาพที่ต้องการเรียนรู้และปรับตัวจากข้อมูลของตัวเอง',
            'global_policy_dialogue' => 'งานกลุ่มนี้เด่นที่เวที การประชุม เครือข่าย และการวางวาระสุขภาพระดับประเทศหรือนานาชาติ' . $examplesText . ' คุณค่าของงานจึงอยู่ที่การพาคนและประเด็นมาเจอกัน เพื่อเปิดทางให้การตัดสินใจเชิงนโยบายเกิดขึ้นได้',
            'ai_digital_workflow' => 'โครงการที่เกี่ยวกับ AI และ digital health ไม่ได้หยุดที่ตัว algorithm แต่โยงกับ workflow การรายงานข้อมูล การอ่านผล และการใช้งานในหน่วยบริการ' . $examplesText . ' นี่คือโจทย์ที่ต้องอ่านพร้อมกันทั้งด้านเทคโนโลยี ข้อมูล และคนทำงาน',
            'drug_governance_safety' => 'งาน Drug / RDU มองการใช้ยาในฐานะพฤติกรรมของระบบบริการ ไม่ใช่แค่การเลือกยารายตัว' . $examplesText . ' จุดสนใจจึงอยู่ที่คุณภาพการสั่งใช้ยา ความปลอดภัย และกลไกสะท้อนข้อมูลกลับไปยังผู้ปฏิบัติ',
            default => 'ในกลุ่มนี้มี ' . number_format($projectCount) . ' โครงการที่ชี้ไปยังโจทย์ใกล้เคียงกัน' . $examplesText . ' ภาพรวมจึงสะท้อนความสนใจที่เกิดซ้ำในงานของหน่วย มากกว่าการจัดหมวดจากชื่อประเภทโครงการเพียงอย่างเดียว',
        };
    }

    private function ruleStrategicReading(array $cluster, float $share): string
    {
        return match ((string) ($cluster['key'] ?? '')) {
            'frailty_care_transition' => 'มุมยุทธศาสตร์ของ Aging / Frailty คือการถามว่า หน่วยจะช่วยระบบบริการดูแลผู้สูงอายุให้ต่อเนื่องขึ้นตรงจุดใด ระหว่างการคัดกรองความเสี่ยง การฟื้นฟู การส่งต่อ และการดูแลในชุมชน หากเล่าให้ชัด จะช่วยให้ผู้บริหารเห็นว่า output แต่ละชิ้นควรต่อเป็น care pathway ไม่ใช่กระจายเป็นโครงการแยกกัน',
            'economic_evaluation_reimbursement' => 'มุมยุทธศาสตร์ของ HTA คือการทำให้ผู้ตัดสินใจเห็นทางเลือก ไม่ใช่เห็นแค่ผลคุ้มค่าหรือไม่คุ้มค่า ควรอ่านกลุ่มนี้เพื่อสรุปว่าเทคโนโลยีหรือบริการใดพร้อมเข้าสู่ระบบ เงื่อนไขงบประมาณคืออะไร และต้องมีมาตรการรองรับอะไรจึงจะใช้ได้จริง',
            'clinical_outcomes_chronic_disease' => 'มุมยุทธศาสตร์ของ Clinical Outcomes คือการใช้ผลลัพธ์ผู้ป่วยเป็นสัญญาณเตือนของระบบบริการ หาก outcome ไม่ดี ควรถามต่อว่าเกิดจากโรคร่วม ความต่อเนื่องของการดูแล คุณภาพบริการ หรือข้อจำกัดของข้อมูล จุดนี้ช่วยแปลงโจทย์คลินิกให้เป็นโจทย์นโยบายได้',
            'access_equity_population_health' => 'มุมยุทธศาสตร์ของ Access / Equity คือการชี้ให้เห็นว่าใครยังอยู่ขอบระบบ งานกลุ่มนี้ควรช่วยผู้บริหารตอบให้ได้ว่าประชากรกลุ่มใดเข้าถึงบริการยาก เพราะอะไร และควรปรับบริการหรือการสื่อสารแบบใดให้เป็นธรรมขึ้น',
            'technology_device_adoption' => 'มุมยุทธศาสตร์ของ Device / Innovation คือการไม่หยุดที่คำถามว่าเทคโนโลยีดีหรือไม่ดี แต่ต้องถามว่าระบบพร้อมรับหรือยัง ใครเป็นผู้ใช้ ต้นทุนซ่อนอยู่ตรงไหน และหลังนำไปใช้จะติดตามผลอย่างไร',
            'implementation_capacity_building' => 'มุมยุทธศาสตร์ของ Implementation คือการดูว่าความรู้ถูกแปลงเป็นความสามารถของทีมบริการแล้วหรือยัง งานกลุ่มนี้ควรช่วยบอกว่าต้องอบรมใคร ใช้เครื่องมืออะไร และมีหลักฐานใดบ้างว่าการทำงานเปลี่ยนไปจริง',
            'data_rwe_learning_system' => 'มุมยุทธศาสตร์ของ Data / RWE คือการทำให้ข้อมูลจริงกลับมาช่วยตัดสินใจซ้ำได้ ไม่ใช่เก็บข้อมูลครั้งเดียวแล้วจบ ควรอ่านกลุ่มนี้เพื่อดูว่าฐานข้อมูลหรือ dashboard ใดตอบคำถามบริการได้จริง และข้อมูลใดยังต้องปรับคุณภาพก่อนใช้เชิงนโยบาย',
            'global_policy_dialogue' => 'มุมยุทธศาสตร์ของ Global Health / Policy Dialogue คือการดูว่าเวทีช่วยขยับวาระอะไร ไม่ใช่นับเพียงจำนวนการประชุม ควรอ่านกลุ่มนี้เพื่อสรุปว่าประเด็นใดถูกยกขึ้นมา ใครควรเป็นพันธมิตร และบทสนทนาใดควรถูกต่อยอดเป็นข้อเสนอหรือความร่วมมือ',
            'ai_digital_workflow' => 'มุมยุทธศาสตร์ของ AI / Digital Health คือการพิสูจน์ว่าเครื่องมือเข้ากับงานจริงได้หรือไม่ ควรอ่านกลุ่มนี้ผ่านคำถามเรื่องผู้ใช้หลัก ความพร้อมของข้อมูล ความปลอดภัย ภาระงาน และการดูแลระบบหลังใช้งาน มากกว่าดูแค่ว่าเป็น AI หรือไม่',
            'drug_governance_safety' => 'มุมยุทธศาสตร์ของ Drug / RDU คือการทำให้ข้อมูลการใช้ยากลับไปเปลี่ยนพฤติกรรมการสั่งใช้และความปลอดภัยของผู้ป่วย งานกลุ่มนี้ควรช่วยบอกว่าจุดเสี่ยงอยู่ตรงไหน และ feedback แบบใดที่ทีมรักษานำไปปรับได้จริง',
            default => $cluster['reading'] . ' เมื่อคิดเป็นสัดส่วน กลุ่มนี้ครอบคลุมประมาณ ' . $share . '% ของโครงการที่อ่านได้ จึงเป็นประเด็นที่ควรหยิบขึ้นมาเล่าเป็นภาพยุทธศาสตร์ของหน่วย',
        };
    }

    private function ruleOutputDirection(array $cluster): string
    {
        $typeCounts = collect($cluster['output_types_seen'] ?? [])->mapWithKeys(fn (array $row) => [(string) ($row['code'] ?? 'OTHER') => (int) ($row['count'] ?? 0)]);
        $labels = collect($cluster['output_types_seen'] ?? [])->take(4)->pluck('label')->filter()->implode(', ');
        $labelText = $labels ?: 'ผลงานหลายประเภท';
        $key = (string) ($cluster['key'] ?? '');

        $specific = match ($key) {
            'frailty_care_transition' => 'ผลงานของกลุ่ม Aging / Frailty เริ่มเล่าได้มากกว่า “มีงานวิจัยผู้สูงอายุหลายชิ้น” เพราะมีทั้ง ' . $labelText . ' สิ่งที่ควรจับตาคือ output เหล่านี้ช่วยประกอบภาพ care pathway ได้แค่ไหน ตั้งแต่การป้องกันภาวะเปราะบาง การฟื้นฟู ไปจนถึงการดูแลต่อเนื่องหลังออกจากโรงพยาบาล',
            'economic_evaluation_reimbursement' => 'ทิศทาง output ของกลุ่ม HTA ควรถูกอ่านเป็นวัตถุดิบสำหรับการตัดสินใจ ไม่ใช่แค่ผลการประเมินทางเศรษฐศาสตร์ รายการที่พบ เช่น ' . $labelText . ' ช่วยบอกว่าหน่วยมีฐานสำหรับคุยเรื่องความคุ้มค่า งบประมาณ ความพร้อมของบริการ และเงื่อนไขของการนำเข้า benefit package',
            'clinical_outcomes_chronic_disease' => 'output ในกลุ่ม Clinical Outcomes ทำหน้าที่เชื่อมข้อมูลคลินิกกับคำถามเชิงระบบ รายการที่พบ เช่น ' . $labelText . ' จึงควรถูกใช้เพื่อเล่าว่าผลลัพธ์ผู้ป่วย โรคร่วม และคุณภาพบริการกำลังชี้ช่องว่างใดของระบบดูแล ไม่ใช่เล่าแค่ว่ามีโรคใดถูกศึกษา',
            'access_equity_population_health' => 'กลุ่ม Access / Equity มี output ที่ควรอ่านผ่านคำถามว่า “ใครได้ประโยชน์ และใครยังเข้าไม่ถึง” มากกว่าการนับจำนวนผลงาน รายการอย่าง ' . $labelText . ' จึงมีน้ำหนักเมื่อถูกต่อยอดเป็นข้อเสนอเรื่องสิทธิ ความเป็นธรรม หรือการออกแบบบริการสำหรับประชากรเฉพาะ',
            'technology_device_adoption' => 'สำหรับ Device / Innovation ทิศทาง output ควรตอบให้ได้ว่าเทคโนโลยีจะเดินทางเข้าสู่ระบบบริการอย่างไร รายการที่พบ เช่น ' . $labelText . ' จึงควรถูกอ่านร่วมกับคำถามเรื่องความพร้อมของหน่วยบริการ การติดตามหลังใช้งาน และเงื่อนไขการจ่ายหรือการกระจายเทคโนโลยี',
            'implementation_capacity_building' => 'output ของกลุ่ม Implementation มีคุณค่าเมื่อทำให้คนทำงานเปลี่ยนวิธีทำงานได้จริง รายการที่พบ เช่น ' . $labelText . ' จึงควรถูกต่อยอดเป็นชุดเครื่องมือ คู่มือ การอบรม หรือ monitoring ที่บอกได้ว่าความรู้ถูกนำไปใช้ในพื้นที่อย่างไร',
            'data_rwe_learning_system' => 'สำหรับ Data / RWE ผลงานที่สำคัญไม่ใช่แค่ฐานข้อมูลหรือ dashboard แต่คือความสามารถในการทำให้ระบบเรียนรู้จากข้อมูลจริง รายการที่พบ เช่น ' . $labelText . ' ควรถูกใช้ต่อเพื่อสร้าง feedback loop ให้ทีมบริการและผู้กำหนดนโยบายเห็น outcome, variation และช่องว่างของข้อมูล',
            'global_policy_dialogue' => 'ทิศทาง output ของ Global Health อยู่ที่การสร้างเวทีและจังหวะให้หลักฐานเดินเข้าสู่บทสนทนาเชิงนโยบาย รายการที่พบ เช่น ' . $labelText . ' จึงควรถูกเล่าในฐานะการตั้งวาระ เชื่อมเครือข่าย และเปิดพื้นที่ให้ผู้มีส่วนเกี่ยวข้องเห็นโจทย์ร่วมกัน',
            'ai_digital_workflow' => 'output ในกลุ่ม AI / Digital Health ควรตอบคำถามเรื่องการใช้งานจริงใน workflow มากกว่าความใหม่ของเทคโนโลยี รายการที่พบ เช่น ' . $labelText . ' จึงควรถูกตามต่อในมิติ data readiness, user adoption, safety monitoring และการดูแลระบบหลัง deployment',
            'drug_governance_safety' => 'สำหรับ Drug / RDU ทิศทาง output ควรชี้ให้เห็น feedback loop ของการใช้ยาในระบบบริการ รายการที่พบ เช่น ' . $labelText . ' จึงมีความหมายเมื่อช่วยให้ทีมรักษาเห็น prescribing behavior, medication safety และจุดที่ต้องปรับแนวปฏิบัติ',
            default => null,
        };

        if ($specific !== null) {
            return $specific;
        }

        if ($typeCounts->only(['EA106', 'EA108', 'EA109', 'EA112', 'EA114'])->sum() >= 3) {
            return 'ผลงานในกลุ่มนี้ไม่ได้หยุดอยู่ที่บทความหรือรายงานวิชาการเท่านั้น แต่เริ่มมีงานที่ใช้สื่อสารกับผู้เกี่ยวข้องและสนับสนุนการตัดสินใจเชิงระบบ เช่น ' . ($labels ?: 'ผลงานหลายประเภท') . ' ภาพรวมจึงบอกว่าองค์ความรู้ชุดนี้มีโอกาสถูกต่อยอดไปสู่ข้อเสนอ แนวทางทำงาน หรือการใช้ประโยชน์ในระดับหน่วยงานมากขึ้น';
        }

        if ($typeCounts->only(['EA104', 'EA105'])->sum() >= 2) {
            return 'ผลงานที่พบเอนมาทางฐานข้อมูล เครื่องมือ หรือระบบสนับสนุนการทำงาน เช่น ' . ($labels ?: 'ผลงานด้านระบบ') . ' จุดที่ควรติดตามต่อคือเครื่องมือเหล่านี้ถูกนำกลับไปใช้ปรับบริการจริงเพียงใด และมีข้อมูลสะท้อนผลการใช้งานหรือยัง';
        }

        if ($typeCounts->only(['EA101', 'EA102', 'EA103'])->sum() >= 2) {
            return 'ผลงานที่พบยังอยู่ในฝั่งการผลิตและเผยแพร่หลักฐานเป็นหลัก เช่น ' . ($labels ?: 'บทความ รายงาน หรือการนำเสนอ') . ' ระยะถัดไปควรเชื่อมผลเหล่านี้กับคำถามเชิงนโยบาย แนวทางปฏิบัติ หรือเครื่องมือที่ผู้เกี่ยวข้องนำไปใช้ต่อได้ชัดขึ้น';
        }

        if ((int) ($cluster['output_count'] ?? 0) === 0) {
            return 'ยังไม่พบผลงานที่บันทึกไว้สำหรับกลุ่มนี้ จึงควรอ่านว่าเป็นโจทย์วิจัยที่เริ่มเห็นทิศทางแล้ว แต่หลักฐานปลายทางยังต้องรอติดตาม';
        }

        return 'ผลงานที่พบมีหลายลักษณะ เช่น ' . ($labels ?: 'หลายประเภท') . ' การอ่านกลุ่มนี้จึงควรดูทั้งชื่อโครงการและรายละเอียดผลงานประกอบกัน เพื่อแยกว่าอะไรเป็นหลักฐานวิชาการ อะไรเป็นงานสื่อสาร และอะไรเริ่มขยับไปสู่การใช้จริง';
    }

    private function ruleNextStep(array $cluster): string
    {
        $key = (string) ($cluster['key'] ?? '');
        $specific = match ($key) {
            'frailty_care_transition' => 'ระยะถัดไปควรร้อยงานผู้สูงอายุหลายชิ้นให้เห็นเป็น care pathway เดียวกัน ตั้งแต่การคัดกรองภาวะเปราะบาง การฟื้นฟู การส่งต่อ ไปจนถึงการดูแลในชุมชน แล้วระบุว่า output ใดควรกลายเป็นแนวทางบริการ ตัวชี้วัด outcome หรือข้อเสนอสำหรับผู้บริหารระบบดูแลผู้สูงอายุ',
            'economic_evaluation_reimbursement' => 'ควรต่อยอดผล HTA ให้เป็นชุดข้อมูลสำหรับการตัดสินใจที่อ่านง่ายขึ้น เช่น สรุป value for money, budget impact, เงื่อนไขความพร้อมของบริการ และทางเลือกเชิงนโยบาย เพื่อให้ผู้ตัดสินใจเห็นว่าควรนำเทคโนโลยีหรือบริการเข้าสู่ระบบภายใต้เงื่อนไขใด',
            'clinical_outcomes_chronic_disease' => 'ระยะถัดไปควรสรุปผลลัพธ์ทางคลินิกให้เชื่อมกับคำถามเชิงระบบ เช่น กลุ่มผู้ป่วยใดมีช่องว่างการดูแลมากที่สุด จุดใดของบริการกระทบ outcome และข้อมูลใดควรถูกติดตามซ้ำ เพื่อให้ผลวิจัยไม่หยุดอยู่ที่ disease outcome แต่ช่วยออกแบบบริการได้',
            'access_equity_population_health' => 'ควรต่อยอดเป็น equity brief ที่ชี้ให้เห็นว่ากลุ่มประชากรใดเข้าถึงบริการน้อยหรือยังมีความเสี่ยงถูกมองข้าม พร้อมเสนอทางเลือกของบริการ การสื่อสาร หรือมาตรการเชิงระบบที่ช่วยลดช่องว่างการเข้าถึง',
            'technology_device_adoption' => 'ระยะถัดไปควรทำ adoption note สำหรับเทคโนโลยีหรืออุปกรณ์เด่น ๆ โดยตอบว่าใครควรใช้ ใช้ใน setting ใด ต้องมีต้นทุนหรือบุคลากรแบบไหน และควรติดตามผลหลังนำไปใช้ด้วยตัวชี้วัดใด เพื่อไม่ให้ข้อเสนอจบที่ความคุ้มค่าอย่างเดียว',
            'implementation_capacity_building' => 'ควรจัด output ให้เห็นเส้นทางจากความรู้ไปสู่การปฏิบัติจริง เช่น training package, checklist, คู่มือปฏิบัติงาน หรือ dashboard ติดตามการนำไปใช้ พร้อมระบุว่าหน่วยบริการใดเริ่มใช้แล้วและยังติดขัดตรงไหน',
            'data_rwe_learning_system' => 'ควรต่อยอดฐานข้อมูลและ dashboard ให้เป็นวงจรเรียนรู้ของระบบบริการ โดยกำหนดคำถามหลักที่ข้อมูลตอบได้ ชุดตัวชี้วัดที่ควรติดตามซ้ำ และกลไกส่งข้อมูลกลับไปยังทีมบริการหรือผู้กำหนดนโยบาย',
            'global_policy_dialogue' => 'ระยะถัดไปควรสรุปผลจากเวทีหรือเครือข่ายให้เห็นว่าเกิด agenda ใด ผู้เล่นหลักคือใคร และประเด็นใดควรถูกพัฒนาต่อเป็น policy note หรือความร่วมมือระยะถัดไป เพื่อให้ output ของเวทีไม่หายไปหลังจบกิจกรรม',
            'ai_digital_workflow' => 'ระยะถัดไปควรสรุปให้เห็นว่า AI หรือระบบดิจิทัลถูกใช้ในขั้นตอนใดของบริการ ใครเป็นผู้ใช้หลัก และมีข้อมูลติดตามเรื่องความแม่นยำ ความปลอดภัย ภาระงาน และการยอมรับของทีมบริการหรือไม่ เพื่อให้การต่อยอดไม่หยุดอยู่ที่ตัวระบบ แต่เชื่อมไปถึงการใช้งานจริงในโรงพยาบาล',
            'drug_governance_safety' => 'ควรต่อยอดงาน RDU ให้เป็น feedback package สำหรับทีมรักษาและผู้บริหารคุณภาพ เช่น dashboard การสั่งใช้ยา ตัวชี้วัด medication safety หรือ brief เรื่องพฤติกรรม prescribing ที่ควรปรับ เพื่อให้ข้อมูลกลับไปเปลี่ยนการปฏิบัติได้จริง',
            default => null,
        };

        if ($specific !== null) {
            return $specific;
        }

        $typeCodes = collect($cluster['output_types_seen'] ?? [])->pluck('code')->map(fn ($code) => strtoupper((string) $code));
        if (! $typeCodes->contains('EA106')) {
            return 'ระยะถัดไปควรต่อยอดเป็นข้อเสนอเชิงนโยบายหรือ brief สำหรับผู้ตัดสินใจ โดยสรุปให้ชัดว่าหลักฐานในกลุ่มนี้ตอบคำถามของระบบสุขภาพอย่างไร และยังต้องเติมข้อมูลส่วนใดก่อนนำไปใช้จริง';
        }
        if (! $typeCodes->contains('EA109') && ! $typeCodes->contains('EA105')) {
            return 'ควรติดตามต่อว่าองค์ความรู้ในกลุ่มนี้ถูกแปลงเป็นเครื่องมือ กระบวนการทำงาน หรือแนวปฏิบัติจริงแล้วหรือยัง เพื่อให้เห็นเส้นทางจากงานวิจัยไปสู่การใช้งานในระบบบริการ';
        }

        return 'ควรจัดทำบทสรุปสังเคราะห์ที่เชื่อมโครงการหลายชิ้นในกลุ่มนี้เข้าด้วยกัน พร้อมตัวชี้วัดหรือบันทึกติดตามผล เพื่อดูว่าผลงานถูกนำไปใช้ต่อในปีถัดไปอย่างไร';
    }

    private function ruleOutputGap(array $cluster): string
    {
        $outputCount = (int) ($cluster['output_count'] ?? 0);
        $projectCount = max(1, (int) ($cluster['project_count'] ?? 0));
        $typeCodes = collect($cluster['output_types_seen'] ?? [])->pluck('code')->map(fn ($code) => strtoupper((string) $code));

        if ($outputCount === 0) {
            return 'มีสัญญาณโจทย์วิจัย แต่ยังไม่มี output ที่บันทึกไว้รองรับ';
        }
        if (($outputCount / $projectCount) < 0.7) {
            return 'จำนวน output ยังน้อยเมื่อเทียบกับจำนวนโครงการใน cluster';
        }
        if (! $typeCodes->intersect(['EA106', 'EA109', 'EA114'])->count()) {
            return 'ยังไม่เห็น output เชิงข้อเสนอ นโยบาย หรือการใช้ประโยชน์ปลายทางชัดเจน';
        }

        return 'มี output รองรับพอสมควร ช่องว่างหลักคือการตรวจทานคุณภาพและผลการใช้ประโยชน์จริงของ output รายชิ้น';
    }

    private function ruleAlignmentLevel(array $cluster): string
    {
        $projectCount = (int) ($cluster['project_count'] ?? 0);
        $outputCount = (int) ($cluster['output_count'] ?? 0);
        $policyFacing = collect($cluster['output_types_seen'] ?? [])
            ->whereIn('code', ['EA106', 'EA108', 'EA109', 'EA112', 'EA114', 'EA115'])
            ->sum('count');

        if ($projectCount >= 5 && $outputCount >= 5 && $policyFacing >= 2) {
            return 'strong';
        }
        if ($projectCount >= 3 && $outputCount > 0) {
            return 'partial';
        }
        if ($projectCount > 0 && $outputCount === 0) {
            return 'weak';
        }

        return 'unclear';
    }

    private function ruleMetaPatterns(Collection $clusters, array $facts): array
    {
        $overallOutputTypes = collect($facts['overall_output_types'] ?? []);
        $topOutput = $overallOutputTypes->first();

        return [
            [
                'title' => 'อ่านจากเนื้อหา ไม่ใช่ project_type ล้วน',
                'interpretation' => 'การจัดกลุ่มนี้ให้น้ำหนักชื่อโครงการ keyword และ output ที่เกิดขึ้นจริง เพื่อให้เห็น cluster ข้ามหมวดฐานข้อมูล',
            ],
            [
                'title' => 'output เด่นสะท้อนทิศทางการใช้ประโยชน์',
                'interpretation' => $topOutput
                    ? 'ชนิด output ที่พบมากคือ ' . ($topOutput['label'] ?? '-') . ' จำนวน ' . number_format((int) ($topOutput['count'] ?? 0)) . ' รายการ จึงควรอ่านควบคู่กับความเด่นเชิงเนื้อหา'
                    : 'ยังไม่มีข้อมูล output เพียงพอสำหรับอ่านทิศทางการใช้ประโยชน์',
            ],
            [
                'title' => 'cluster ใหญ่ควรถูกแปลงเป็น synthesis',
                'interpretation' => 'cluster ที่มีหลายโครงการควรถูกสรุปรวมเป็น policy/research synthesis เพื่อเชื่อมงานรายชิ้นให้กลายเป็นภาพยุทธศาสตร์ของหน่วย',
            ],
            [
                'title' => 'ช่องว่างสำคัญคือ evidence-to-implementation',
                'interpretation' => 'หาก cluster มีบทความหรือรายงานมากแต่ยังไม่มีข้อเสนอ เครื่องมือ หรือการเปลี่ยนแนวปฏิบัติ ควรถูกติดตามเป็นช่องว่างการแปลหลักฐานสู่การใช้จริง',
            ],
        ];
    }

    private function streamOllamaGenerate(string $url, array $payload, string $model, array $snapshot, int $maxProjects): string
    {
        if (! function_exists('curl_init')) {
            $this->error('PHP cURL extension is required for Ollama streaming progress.');

            return '';
        }

        $estimatedSeconds = $this->estimateOllamaSeconds($model, $snapshot, $maxProjects);
        $started = microtime(true);
        $lastUiUpdate = 0.0;
        $lineBuffer = '';
        $text = '';
        $chunks = 0;
        $finalStats = [];
        $errorText = '';
        $promptChars = mb_strlen((string) ($payload['prompt'] ?? ''), 'UTF-8');

        $bar = $this->output->createProgressBar(100);
        $bar->setFormat(' %current:3s%% [%bar%] %message%');
        $bar->setMessage('preparing Ollama request');
        $bar->start();

        $updateProgress = function (string $stage = 'waiting for model') use (&$bar, &$started, &$lastUiUpdate, &$chunks, &$text, $estimatedSeconds, $promptChars): void {
            $now = microtime(true);
            if ($now - $lastUiUpdate < 1.0) {
                return;
            }

            $elapsed = max(0, (int) round($now - $started));
            $progress = min(95, max(2, (int) floor(($elapsed / max(1, $estimatedSeconds)) * 95)));
            $remaining = max(0, $estimatedSeconds - $elapsed);
            $message = $stage
                . ' | elapsed ' . $this->formatDuration($elapsed)
                . ' | est left ' . $this->formatDuration($remaining)
                . ' | chunks ' . number_format($chunks)
                . ' | chars ' . number_format(mb_strlen($text, 'UTF-8'))
                . ' | prompt ' . number_format($promptChars) . ' chars';

            $bar->setProgress($progress);
            $bar->setMessage($message);
            $bar->display();
            $lastUiUpdate = $now;
        };

        $handleLine = function (string $line) use (&$text, &$chunks, &$finalStats, &$errorText, $updateProgress): void {
            $line = trim($line);
            if ($line === '') {
                return;
            }

            $json = json_decode($line, true);
            if (! is_array($json)) {
                return;
            }

            if (isset($json['error'])) {
                $errorText = (string) $json['error'];
                return;
            }

            $piece = (string) ($json['response'] ?? '');
            if ($piece !== '') {
                $text .= $piece;
                $chunks++;
                $updateProgress('generating JSON');
            }

            if (! empty($json['done'])) {
                $finalStats = $json;
            }
        };

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/x-ndjson',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_BUFFERSIZE => 1024,
            CURLOPT_NOPROGRESS => false,
            CURLOPT_XFERINFOFUNCTION => function () use ($updateProgress) {
                $updateProgress('model loading / prompt evaluation');

                return 0;
            },
            CURLOPT_WRITEFUNCTION => function ($curl, string $chunk) use (&$lineBuffer, $handleLine) {
                $lineBuffer .= $chunk;
                while (($pos = strpos($lineBuffer, "\n")) !== false) {
                    $line = substr($lineBuffer, 0, $pos);
                    $lineBuffer = substr($lineBuffer, $pos + 1);
                    $handleLine($line);
                }

                return strlen($chunk);
            },
        ]);

        $ok = curl_exec($ch);
        $curlError = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (trim($lineBuffer) !== '') {
            $handleLine($lineBuffer);
        }

        if ($ok === false || $curlError !== '') {
            $bar->clear();
            $this->error('Ollama stream failed: ' . ($curlError ?: 'unknown cURL error'));

            return '';
        }

        if ($status >= 400) {
            $bar->clear();
            $this->error('Ollama stream failed with HTTP ' . $status);

            return '';
        }

        if ($errorText !== '') {
            $bar->clear();
            $this->error('Ollama error: ' . Str::limit($errorText, 500));

            return '';
        }

        $bar->setProgress(100);
        $bar->setMessage(
            'done | elapsed ' . $this->formatDuration((int) round(microtime(true) - $started))
            . ' | output tokens ' . number_format((int) ($finalStats['eval_count'] ?? 0))
            . ' | prompt tokens ' . number_format((int) ($finalStats['prompt_eval_count'] ?? 0))
        );
        $bar->finish();
        $this->line('');

        return trim($text);
    }

    private function estimateOllamaSeconds(string $model, array $snapshot, int $maxProjects): int
    {
        $projects = min($maxProjects, (int) collect($snapshot['projects'] ?? [])->count());
        $outputs = (int) collect($snapshot['projects'] ?? [])
            ->take($maxProjects)
            ->sum(fn ($project) => count($project['outputs'] ?? []));
        $model = mb_strtolower($model, 'UTF-8');
        $base = str_contains($model, '14b') ? 900 : (str_contains($model, '7b') ? 420 : 180);
        $estimate = $base + ($projects * 2) + ($outputs * 1);

        return max(180, min(7200, $estimate));
    }

    private function decodeJsonObject(string $text): array
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/u', '', $text) ?? $text;
        $text = preg_replace('/\s*```$/u', '', $text) ?? $text;
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (preg_match_all('/\{(?:[^{}]|(?R))*\}/su', $text, $matches)) {
            foreach (array_reverse($matches[0]) as $candidate) {
                $decoded = json_decode($candidate, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return [];
    }

    private function isValidProjectNatureInsight(array $result): bool
    {
        $summary = trim((string) ($result['summary'] ?? ''));
        $clusters = $result['interest_clusters'] ?? null;
        $sectionCards = $result['section_cards'] ?? null;
        $isRuleBased = trim((string) ($result['provider_note'] ?? '')) === 'rule-based fallback; no external AI call';

        return $summary !== ''
            && ($isRuleBased || ! $this->hasLowQualitySectionCards(is_array($sectionCards) ? $sectionCards : []))
            && (
                (is_array($sectionCards) && count($sectionCards) > 0)
                || (is_array($clusters) && count($clusters) > 0)
            );
    }

    private function hasLowQualitySectionCards(array $sectionCards): bool
    {
        if (count($sectionCards) === 0) {
            return false;
        }

        $badPhrases = [
            'หน่วยงานได้ดำเนินการศึกษาและพัฒนา',
            'งานวิจัยนี้เน้นที่การสร้างหลักฐานทางวิชาการ',
            'ผลงานวิชาการเชิงระบบและการนำเสนอผลงานทางวิชาการเป็นหลัก พร้อมกับรายงานวิชาการที่มีความหลากหลาย',
            'ควรพัฒนา output ประเภทข้อเสนอเชิงนโยบายเพื่อสนับสนุนการเปลี่ยนแปลงจริง',
            'แต่ยังขาดข้อเสนอเชิงนโยบายเพื่อสนับสนุนการเปลี่ยนแปลงจริง',
        ];

        $combinedByCard = collect($sectionCards)
            ->map(fn ($card) => collect(['work_done', 'reading', 'output_direction', 'next_step', 'evidence_note'])
                ->map(fn ($field) => trim((string) data_get($card, $field, '')))
                ->filter()
                ->implode(' '))
            ->filter()
            ->values();

        foreach ($badPhrases as $phrase) {
            if ($combinedByCard->filter(fn ($text) => Str::contains($text, $phrase))->count() >= 3) {
                return true;
            }
        }

        $directions = collect($sectionCards)
            ->map(fn ($card) => Str::of((string) data_get($card, 'output_direction', ''))->lower()->squish()->toString())
            ->filter()
            ->values();

        return $directions->duplicates()->count() >= 2;
    }

    private function extractOutputText(array $json): string
    {
        return collect($json['output'] ?? [])
            ->flatMap(fn ($item) => $item['content'] ?? [])
            ->map(fn ($content) => $content['text'] ?? '')
            ->filter()
            ->implode("\n");
    }

    private function logStep(string $step, string $message): void
    {
        $this->line('[' . now()->timezone('Asia/Bangkok')->format('H:i:s') . '] ' . $step . ' - ' . $message);
    }

    private function printResultSummary(array $result): void
    {
        $visible = collect($result)
            ->reject(fn ($value, $key) => str_starts_with((string) $key, '_'))
            ->all();

        $this->info('AI output parsed successfully.');
        $this->line('Decoded keys: ' . implode(', ', array_keys($visible)));

        if (trim((string) ($result['summary'] ?? '')) !== '') {
            $this->line('');
            $this->line('Summary sample:');
            $this->line(Str::limit(trim((string) $result['summary']), 1200));
        }

        $clusters = collect($result['interest_clusters'] ?? []);
        $sectionCards = collect($result['section_cards'] ?? []);
        if ($sectionCards->isNotEmpty()) {
            $this->line('');
            $this->line('Website section cards:');
            $this->table(
                ['Key', 'Label', 'Headline'],
                $sectionCards->take(8)->map(function ($card) {
                    $card = (array) $card;
                    return [
                        (string) ($card['theme_key'] ?? '-'),
                        Str::limit((string) ($card['label'] ?? '-'), 40),
                        Str::limit((string) ($card['headline'] ?? '-'), 80),
                    ];
                })->all()
            );
        }

        if ($clusters->isNotEmpty()) {
            $this->line('');
            $this->line('Interest clusters:');
            $this->table(
                ['Cluster', 'Projects', 'Keywords'],
                $clusters->take(8)->map(function ($cluster) {
                    $cluster = (array) $cluster;
                    return [
                        (string) ($cluster['name'] ?? '-'),
                        (string) ($cluster['project_count'] ?? '-'),
                        collect($cluster['keywords'] ?? [])->take(5)->implode(', '),
                    ];
                })->all()
            );
        }

        if (isset($result['_raw_text'])) {
            $this->line('');
            $this->line('Raw output preview:');
            $this->line(Str::limit((string) $result['_raw_text'], 2000));
        }
    }

    private function finishConsoleRun(float $startedTs): void
    {
        $this->info('Finished at: ' . now()->timezone('Asia/Bangkok')->format('Y-m-d H:i:s'));
        $this->info('Total duration: ' . $this->formatDuration((int) round(microtime(true) - $startedTs)));
    }

    private function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return collect([
            $hours > 0 ? $hours . 'h' : null,
            $minutes > 0 ? $minutes . 'm' : null,
            $remainingSeconds . 's',
        ])->filter()->implode(' ');
    }
}

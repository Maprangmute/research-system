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
        {--provider= : AI provider: ollama, gemini, or openai. Defaults to AI_PROVIDER env}
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

        $apiKey = $provider === 'ollama' ? '' : trim((string) config("services.{$provider}.key"));
        if ($provider !== 'ollama' && $apiKey === '') {
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
                'version' => 5,
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
        $provider = mb_strtolower(trim((string) ($this->option('provider') ?: env('AI_PROVIDER', 'gemini'))), 'UTF-8');

        return in_array($provider, ['ollama', 'gemini', 'openai'], true) ? $provider : 'ollama';
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
            'section_cards is the most important key for the website section "ความสนใจเชิงเนื้อหาของหน่วย". Return one card for EACH theme in PORTFOLIO_FACTS_JSON.themes. Use the exact theme_key from the input. Each card must have: theme_key, label, keywords, headline, work_done, reading, output_direction, next_step, evidence_note.',
            'section_cards.label should be concise and can improve the display name, but must still map to the same theme. section_cards.keywords should be 4-7 short keywords from the data.',
            'section_cards.work_done: 2-4 Thai sentences explaining what has actually happened in this interest area, grounded in project title signals. Be specific and different for each theme.',
            'section_cards.reading: 2-3 Thai sentences interpreting the nature of the work as a researcher/policy translation practitioner. Do not use generic phrases repeatedly.',
            'section_cards.output_direction: 3-5 Thai sentences explaining the direction of recorded outputs in this theme. Use full Thai output type names from output_types_seen; do not display EA codes. Explain whether outputs look like academic evidence, policy-facing evidence, data infrastructure, implementation tool, communication platform, or practice/policy change support.',
            'section_cards.next_step: 2-4 Thai sentences about what output would help translate this interest area further. Phrase politely and strategically, e.g. "ระยะถัดไปอาจต่อยอดเป็น...", "หลักฐานชุดนี้จะมีน้ำหนักมากขึ้นหาก...". Avoid repetitive "ควรตรวจสอบว่า" in every card.',
            'section_cards.evidence_note: 1-2 cautious Thai sentences describing what the current project+output evidence can and cannot conclude. Do not claim real-world policy impact unless recorded outputs clearly support it.',
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
            'Example shape: {"summary":"...","section_cards":[{"theme_key":"aging","label":"Aging, Frailty และ care continuum","keywords":["ผู้สูงอายุ"],"headline":"...","work_done":"...","reading":"...","output_direction":"...","next_step":"...","evidence_note":"..."}],"category_overview":[{"category":"Aging / Frailty","interest":"..."}],"interest_clusters":[{"name":"...","keywords":["..."],"project_count":3,"output_count":2,"output_types_seen":["บทความวิจัยและบทความวารสาร"],"evidence_titles":["..."],"output_evidence":["..."],"interpretation":"...","output_gap":"...","suggested_output_followup":"..."}],"by_project_type":[{"project_type":"01 Aging and Frailty","main_interest":"...","dominant_themes":[{"title":"Frailty & Healthy Aging","signals":["..."],"meaning":"..."}],"strategic_reading":"..."}],"meta_patterns":[{"title":"...","interpretation":"..."}],"capability_clusters":[{"capability":"Aging & Integrated Care","related_project_types":["01"]}],"output_alignment":[{"theme":"...","project_signal":"...","output_signal":"...","alignment_level":"partial","what_exists":"...","what_to_verify_next":"..."}],"concise_summary":["..."],"emerging_keywords":["..."],"underrepresented_areas":["..."],"method_signals":["..."],"policy_relevance":"...","confidence":0.7}',
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

        return $summary !== ''
            && (
                (is_array($sectionCards) && count($sectionCards) > 0)
                || (is_array($clusters) && count($clusters) > 0)
            );
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

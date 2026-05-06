<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ClassifyParticipantProfessionsWithAi extends Command
{
    protected $signature = 'outputs:classify-professions-ai
        {--limit=100 : Maximum unique names to classify}
        {--batch=20 : Names per API request}
        {--provider= : AI provider: gemini or openai. Defaults to AI_PROVIDER env}
        {--name=* : Classify only matching display names}
        {--all : Include names that local rules can already classify}
        {--refresh : Re-classify names that already have cached AI results}
        {--dry-run : Show candidates without writing AI results}';

    protected $description = 'Classify project participant profession groups with OpenAI and cache the results for visualization';

    private const ALLOWED_GROUPS = [
        'แพทย์ / Clinical expert',
        'เภสัชกร',
        'พยาบาล',
        'Data / Analyst',
        'นักวิจัย / เจ้าหน้าที่วิจัย',
    ];

    public function handle(): int
    {
        if (! Schema::hasTable('ai_profession_classifications')) {
            $this->error('Missing ai_profession_classifications table. Run: php artisan migrate');

            return self::FAILURE;
        }

        $candidates = $this->collectCandidates();
        $nameFilters = collect($this->option('name'))->map(fn ($name) => mb_strtolower(trim((string) $name), 'UTF-8'))->filter()->values();
        if ($nameFilters->isNotEmpty()) {
            $candidates = $candidates
                ->filter(fn ($row) => $nameFilters->contains(fn ($needle) => Str::contains(mb_strtolower($row['display_name'], 'UTF-8'), $needle)))
                ->values();
        }

        if (! $this->option('all')) {
            $candidates = $candidates
                ->filter(fn ($row) => ! $this->hasLocalProfessionSignal($row['display_name'], $row['position']))
                ->values();
        }

        if (! $this->option('refresh')) {
            $existing = DB::table('ai_profession_classifications')->pluck('name_key')->all();
            $candidates = $candidates->reject(fn ($row) => in_array($row['name_key'], $existing, true))->values();
        }

        $limit = max(1, (int) $this->option('limit'));
        $candidates = $candidates->take($limit)->values();

        if ($candidates->isEmpty()) {
            $this->info('No profession classification candidates found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Position', 'Affiliations', 'Projects'],
            $candidates->take(20)->map(fn ($row) => [
                $row['display_name'],
                $row['position'] ?: '-',
                Str::limit(collect($row['affiliations'])->filter()->implode(' | '), 80),
                $row['projects'],
            ])->all()
        );

        if ($this->option('dry-run')) {
            $this->info('Dry run only. Rerun without --dry-run to write classifications.');

            return self::SUCCESS;
        }

        $provider = $this->resolveProvider();
        $apiKey = trim((string) config("services.{$provider}.key"));
        if ($apiKey === '') {
            $envName = $provider === 'gemini' ? 'GEMINI_API_KEY' : 'OPENAI_API_KEY';
            $this->error("{$envName} is not configured.");

            return self::FAILURE;
        }

        $batchSize = max(1, min(50, (int) $this->option('batch')));
        $written = 0;

        foreach ($candidates->chunk($batchSize) as $chunk) {
            $results = $this->classifyBatch($provider, $apiKey, $chunk->values());
            foreach ($results as $result) {
                $nameKey = trim((string) ($result['name_key'] ?? ''));
                if ($nameKey === '') {
                    continue;
                }

                $group = trim((string) ($result['profession_group'] ?? ''));
                if ($group === 'Research / Policy / Other') {
                    $group = 'นักวิจัย / เจ้าหน้าที่วิจัย';
                }
                if ($group === 'ไม่ระบุวิชาชีพ') {
                    $group = 'นักวิจัย / เจ้าหน้าที่วิจัย';
                }
                if (! in_array($group, self::ALLOWED_GROUPS, true)) {
                    $group = 'นักวิจัย / เจ้าหน้าที่วิจัย';
                }

                DB::table('ai_profession_classifications')->updateOrInsert(
                    ['name_key' => $nameKey],
                    [
                        'display_name' => trim((string) ($result['display_name'] ?? $nameKey)),
                        'profession_group' => $group,
                        'confidence' => max(0, min(1, (float) ($result['confidence'] ?? 0))),
                        'evidence' => trim((string) ($result['evidence'] ?? '')) ?: null,
                        'source_context' => 'project_participant',
                        'raw_response' => json_encode(array_merge($result, ['provider' => $provider]), JSON_UNESCAPED_UNICODE),
                        'classified_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
                $written++;
            }
        }

        $this->info("Cached {$written} AI profession classifications.");

        return self::SUCCESS;
    }

    private function resolveProvider(): string
    {
        $provider = mb_strtolower(trim((string) ($this->option('provider') ?: env('AI_PROVIDER', 'gemini'))), 'UTF-8');

        return in_array($provider, ['gemini', 'openai'], true) ? $provider : 'gemini';
    }

    private function classifyBatch(string $provider, string $apiKey, Collection $candidates): array
    {
        return $provider === 'openai'
            ? $this->classifyBatchWithOpenAi($apiKey, $candidates)
            : $this->classifyBatchWithGemini($apiKey, $candidates);
    }

    private function classificationPrompt(Collection $candidates): string
    {
        return implode("\n", [
            'You classify research project participant professions for a Thai research portfolio dashboard.',
            'Return only valid JSON: {"items":[...]}',
            'Allowed profession_group values: ' . implode(' | ', self::ALLOWED_GROUPS),
            'Use position first when available. If position is missing, infer from name, public professional context, affiliation, project themes, and role context.',
            'If evidence is weak, choose "นักวิจัย / เจ้าหน้าที่วิจัย" and set confidence below 0.55.',
            'For physicians, dentists, clinical faculty, or medical doctors, use "แพทย์ / Clinical expert".',
            'Each item must include name_key, display_name, profession_group, confidence, evidence.',
            '',
            json_encode([
                'items' => $candidates->map(fn ($row) => [
                    'name_key' => $row['name_key'],
                    'display_name' => $row['display_name'],
                    'position' => $row['position'],
                    'affiliations' => $row['affiliations'],
                    'roles' => $row['roles'],
                    'people_types' => $row['people_types'],
                    'themes' => $row['themes'],
                    'project_names' => $row['project_names'],
                ])->values()->all(),
            ], JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function classifyBatchWithGemini(string $apiKey, Collection $candidates): array
    {
        $model = trim((string) config('services.gemini.model', 'gemini-2.5-flash'));
        $url = rtrim((string) config('services.gemini.base_url'), '/') . '/models/' . $model . ':generateContent';
        $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
            ->acceptJson()
            ->timeout(90)
            ->post($url, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $this->classificationPrompt($candidates)],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
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
        $json = $this->decodeJsonObject($text);

        return is_array($json['items'] ?? null) ? $json['items'] : [];
    }

    private function classifyBatchWithOpenAi(string $apiKey, Collection $candidates): array
    {
        $payload = [
            'model' => config('services.openai.model', 'gpt-5-mini'),
            'instructions' => 'Return only valid JSON.',
            'input' => $this->classificationPrompt($candidates),
        ];

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(90)
            ->post(rtrim((string) config('services.openai.base_url'), '/') . '/responses', $payload);

        if ($response->failed()) {
            $this->error('OpenAI request failed: ' . $response->status() . ' ' . Str::limit($response->body(), 300));

            return [];
        }

        $text = $this->extractOutputText($response->json());
        $json = $this->decodeJsonObject($text);

        return is_array($json['items'] ?? null) ? $json['items'] : [];
    }

    private function collectCandidates(): Collection
    {
        $usersById = User::query()->select(['id', 'position'])->get()->keyBy('id');
        $rows = collect();

        $projects = Project::query()
            ->with(['copis:id,project_id,user_id,name,affiliation,person_type', 'coResearches:id,project_id,user_id,name,affiliation,unit_status'])
            ->select(['id', 'project_name_th', 'project_type', 'theme', 'pi_name', 'pi_user_id', 'pi_affiliation', 'pi_person_type'])
            ->get();

        foreach ($projects as $project) {
            $push = function (?string $name, ?int $userId, string $role, ?string $affiliation, ?string $peopleType) use ($rows, $project, $usersById) {
                $name = trim((string) $name);
                if ($name === '') {
                    return;
                }

                $position = '';
                if ($userId && $usersById->has($userId)) {
                    $position = trim((string) $usersById->get($userId)?->position);
                }

                $rows->push([
                    'name_key' => $this->normalizePersonNameForMatch($name),
                    'display_name' => $name,
                    'position' => $position,
                    'role' => $role,
                    'affiliation' => trim((string) $affiliation),
                    'people_type' => trim((string) $peopleType),
                    'theme' => trim((string) ($project->project_type ?: $project->theme)),
                    'project_name' => trim((string) $project->project_name_th),
                ]);
            };

            $push($project->pi_name, (int) ($project->pi_user_id ?? 0) ?: null, 'PI', $project->pi_affiliation, $project->pi_person_type);
            foreach ($project->copis as $copi) {
                $push($copi->name, (int) ($copi->user_id ?? 0) ?: null, 'Co-PI', $copi->affiliation, $copi->person_type);
            }
            foreach ($project->coResearches as $coResearch) {
                $push($coResearch->name, (int) ($coResearch->user_id ?? 0) ?: null, 'Co-Research', $coResearch->affiliation, $coResearch->unit_status);
            }
        }

        return $rows
            ->filter(fn ($row) => $row['name_key'] !== '')
            ->groupBy('name_key')
            ->map(function (Collection $group, string $nameKey) {
                $first = $group->first();

                return [
                    'name_key' => $nameKey,
                    'display_name' => $first['display_name'],
                    'position' => $group->pluck('position')->filter()->first() ?: '',
                    'affiliations' => $group->pluck('affiliation')->filter()->unique()->take(5)->values()->all(),
                    'roles' => $group->pluck('role')->filter()->unique()->values()->all(),
                    'people_types' => $group->pluck('people_type')->filter()->unique()->values()->all(),
                    'themes' => $group->pluck('theme')->filter()->unique()->take(5)->values()->all(),
                    'project_names' => $group->pluck('project_name')->filter()->unique()->take(5)->values()->all(),
                    'projects' => $group->pluck('project_name')->filter()->unique()->count(),
                ];
            })
            ->sortBy('display_name', SORT_NATURAL)
            ->values();
    }

    private function hasLocalProfessionSignal(string $name, string $position): bool
    {
        $text = mb_strtolower(trim($position . ' ' . $name), 'UTF-8');

        return Str::contains($text, [
            'นพ.',
            'พญ.',
            'แพทย์',
            'doctor',
            'physician',
            'clinical',
            'เภสัช',
            'pharmac',
            'พยาบาล',
            'nurse',
            'data',
            'engineer',
            'developer',
            'analyst',
            'สถิติ',
            'วิเคราะห์',
        ]);
    }

    private function normalizePersonNameForMatch(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value), 'UTF-8');
        $value = preg_replace('/[\s.\x{200B}\x{200C}\x{200D}\x{FEFF}]+/u', '', $value) ?? '';
        $titlePattern = '/^(?:ศาสตราจารย์|รองศาสตราจารย์|ผู้ช่วยศาสตราจารย์|อาจารย์|พันเอกหญิง|นายแพทย์|แพทย์หญิง|ทันตแพทย์หญิง|ทันตแพทย์|ดร|รศ|ผศ|ศ|อ|นพ|พญ|ทพญ|ทพ|ภกญ|ภก|คุณ|นาย|นางสาว|นาง|professor|associateprofessor|assistantprofessor|assocprof|asstprof|prof|doctor|dr|mr|mrs|ms)+/u';

        return preg_replace($titlePattern, '', $value) ?? $value;
    }

    private function extractOutputText(array $response): string
    {
        if (isset($response['output_text']) && is_string($response['output_text'])) {
            return $response['output_text'];
        }

        return collect($response['output'] ?? [])
            ->flatMap(fn ($item) => $item['content'] ?? [])
            ->map(fn ($content) => $content['text'] ?? '')
            ->filter()
            ->implode("\n");
    }

    private function decodeJsonObject(string $text): array
    {
        $clean = trim($text);
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\s*```$/', '', $clean) ?? $clean;
        $decoded = json_decode($clean, true);

        return is_array($decoded) ? $decoded : [];
    }
}

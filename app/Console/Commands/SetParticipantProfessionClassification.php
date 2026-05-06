<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SetParticipantProfessionClassification extends Command
{
    protected $signature = 'outputs:set-profession-classification
        {name : Participant name, or part of the name}
        {group : Profession group}
        {--evidence= : Short reason shown in visualization}
        {--dry-run : Show matched names without writing}';

    protected $description = 'Manually cache a profession classification for project participants without using an API key';

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

        $group = trim((string) $this->argument('group'));
        if ($group === 'Research / Policy / Other') {
            $group = 'นักวิจัย / เจ้าหน้าที่วิจัย';
        }
        if ($group === 'ไม่ระบุวิชาชีพ') {
            $group = 'นักวิจัย / เจ้าหน้าที่วิจัย';
        }
        if (! in_array($group, self::ALLOWED_GROUPS, true)) {
            $this->error('Invalid group. Allowed values:');
            foreach (self::ALLOWED_GROUPS as $allowedGroup) {
                $this->line('- ' . $allowedGroup);
            }

            return self::FAILURE;
        }

        $needle = mb_strtolower(trim((string) $this->argument('name')), 'UTF-8');
        $matches = $this->collectParticipantNames()
            ->filter(fn ($row) => Str::contains(mb_strtolower($row['display_name'], 'UTF-8'), $needle))
            ->values();

        if ($matches->isEmpty()) {
            $this->error('No participant names matched.');

            return self::FAILURE;
        }

        $this->table(
            ['Name', 'Affiliations', 'Projects'],
            $matches->map(fn ($row) => [
                $row['display_name'],
                Str::limit(collect($row['affiliations'])->filter()->implode(' | '), 90),
                $row['projects'],
            ])->all()
        );

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        $evidence = trim((string) ($this->option('evidence') ?: 'manual override'));
        foreach ($matches as $row) {
            DB::table('ai_profession_classifications')->updateOrInsert(
                ['name_key' => $row['name_key']],
                [
                    'display_name' => $row['display_name'],
                    'profession_group' => $group,
                    'confidence' => 1,
                    'evidence' => $evidence,
                    'source_context' => 'project_participant',
                    'raw_response' => json_encode(['source' => 'manual'], JSON_UNESCAPED_UNICODE),
                    'classified_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->info('Saved manual profession classification for ' . $matches->count() . ' participant name(s).');

        return self::SUCCESS;
    }

    private function collectParticipantNames(): Collection
    {
        $rows = collect();
        $projects = Project::query()
            ->with(['copis:id,project_id,name,affiliation', 'coResearches:id,project_id,name,affiliation'])
            ->select(['id', 'project_name_th', 'pi_name', 'pi_affiliation'])
            ->get();

        foreach ($projects as $project) {
            $push = function (?string $name, ?string $affiliation) use ($rows, $project) {
                $name = trim((string) $name);
                if ($name === '') {
                    return;
                }

                $rows->push([
                    'name_key' => $this->normalizePersonNameForMatch($name),
                    'display_name' => $name,
                    'affiliation' => trim((string) $affiliation),
                    'project_name' => trim((string) $project->project_name_th),
                ]);
            };

            $push($project->pi_name, $project->pi_affiliation);
            foreach ($project->copis as $copi) {
                $push($copi->name, $copi->affiliation);
            }
            foreach ($project->coResearches as $coResearch) {
                $push($coResearch->name, $coResearch->affiliation);
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
                    'affiliations' => $group->pluck('affiliation')->filter()->unique()->values()->all(),
                    'projects' => $group->pluck('project_name')->filter()->unique()->count(),
                ];
            })
            ->sortBy('display_name', SORT_NATURAL)
            ->values();
    }

    private function normalizePersonNameForMatch(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value), 'UTF-8');
        $value = preg_replace('/[\s.\x{200B}\x{200C}\x{200D}\x{FEFF}]+/u', '', $value) ?? '';
        $titlePattern = '/^(?:ศาสตราจารย์|รองศาสตราจารย์|ผู้ช่วยศาสตราจารย์|อาจารย์|พันเอกหญิง|นายแพทย์|แพทย์หญิง|ทันตแพทย์หญิง|ทันตแพทย์|ดร|รศ|ผศ|ศ|อ|นพ|พญ|ทพญ|ทพ|ภกญ|ภก|คุณ|นาย|นางสาว|นาง|professor|associateprofessor|assistantprofessor|assocprof|asstprof|prof|doctor|dr|mr|mrs|ms)+/u';

        return preg_replace($titlePattern, '', $value) ?? $value;
    }
}

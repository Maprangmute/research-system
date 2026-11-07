<?php

// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Models\BackOfficeTask;
use App\Models\ExternalOutput;
use App\Models\FteRole;
use App\Models\Output;
use App\Models\ProgressReport;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\ResponsiveView;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use PDF;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $projectColumns = [
            'id',
            'project_name_th',
            'project_code',
            'project_type',
            'theme',
            'funding_type',
            'funding_source',
            'funding_year',
            'planning_status',
            'start_year',
            'start_date',
            'team_start_date',
            'end_year',
            'end_date',
            'finish_year',
            'finish_date',
            'team_end_date',
            'total_budget',
            'budget',
            'pi_name',
            'pi_affiliation',
            'pi_person_type',
            'project_list',
            'collab_type',
            'unit_name',
            'department',
            'created_at',
            'updated_at',
        ];

        $existingColumns = static function (string $table, array $desired): array {
            static $cache = [];

            if (!array_key_exists($table, $cache)) {
                $cache[$table] = Schema::getColumnListing($table);
            }

            $selected = array_values(array_intersect($desired, $cache[$table]));

            return $selected ?: ['*'];
        };

        $authUser = $request->user();
        $limitFteToSelf = (bool) ($authUser?->mustLimitFteToSelf() ?? false);
        $authFullName = trim(($authUser->prefix ?? '').' '.($authUser->name ?? ''));

        $projects = Project::query()
            ->select($existingColumns('projects', $projectColumns))
            ->with([
                'coResearches' => fn($query) => $query->select($existingColumns('co_research', [
                    'id', 'project_id', 'user_id', 'name', 'unit_status', 'affiliation',
                ])),
                'copis' => fn($query) => $query->select($existingColumns('copis', [
                    'id', 'project_id', 'user_id', 'name', 'affiliation', 'person_type',
                ])),
                'fteRoles' => function ($query) use ($existingColumns, $limitFteToSelf, $authFullName) {
                    $query->select($existingColumns('fte_roles', [
                        'id', 'project_id', 'name', 'role_group', 'fte_value', 'fte', 'hours',
                        'job_detail', 'task_detail', 'start_date', 'end_date',
                    ]));

                    if ($limitFteToSelf) {
                        $query->where('name', $authFullName);
                    }
                },
                'irbs' => fn($query) => $query->select($existingColumns('irbs', [
                    'id', 'project_id', 'code', 'source', 'exp_date',
                ])),
                'progressReports',
                'outputs' => fn($query) => $query
                    ->select($existingColumns('outputs', [
                        'id', 'project_id', 'output_code', 'type', 'title', 'year',
                        'journal', 'quartile', 'impact_factor', 'created_at', 'updated_at',
                    ]))
                    ->with([
                        'authors' => fn($authorQuery) => $authorQuery->select($existingColumns('output_authors', [
                            'id', 'output_id', 'name', 'role',
                        ])),
                    ]),
            ])
            ->get();

        // collections เดิม (ยังเก็บไว้ใช้กับ Blade ส่วนอื่น ๆ ได้)
        $copis           = $projects->flatMap->copis;
        $coResearches    = $projects->flatMap->coResearches;
        $fteRoles        = $projects->flatMap->fteRoles;
        $outputs         = $projects->flatMap->outputs;
        $irbs            = $projects->flatMap->irbs;
        $progressReports = $projects->flatMap->progressReports;
        $canDownloadDashboardData = in_array(optional($request->user())->role, [
            User::ROLE_UNIT_EXECUTIVE,
            User::ROLE_HR,
            User::ROLE_ADMIN,
            User::ROLE_SU_ADMIN,
        ], true);

        $externalOutputs = $canDownloadDashboardData ? ExternalOutput::query()
            ->select($existingColumns('external_outputs', [
                'id',
                'output_code',
                'type',
                'job_category',
                'title',
                'note',
                'publish_date',
                'journal',
                'quartile',
                'impact_factor',
                'created_at',
                'updated_at',
            ]))
            ->with([
                'authors' => fn($authorQuery) => $authorQuery->select($existingColumns('external_output_authors', [
                    'id', 'external_output_id', 'name', 'role',
                ])),
            ])
            ->latest()
            ->get() : collect();

        $backOfficeTasks = $canDownloadDashboardData ? BackOfficeTask::query()
            ->with([
                'creator:id,prefix,name',
                'entries:id,task_id,hours,fte_value',
                'outputs' => fn($query) => $query
                    ->select($existingColumns('back_office_task_outputs', [
                        'id', 'task_id', 'owner_user_id', 'owner_name', 'title', 'type',
                        'year', 'journal', 'publisher_name', 'created_at', 'updated_at',
                    ]))
                    ->with([
                        'authorLinks.user',
                    ]),
            ])
            ->select($existingColumns('back_office_tasks', [
                'id',
                'backoffice_id',
                'title',
                'description',
                'purpose',
                'start_date',
                'end_date',
                'created_by',
                'updated_at',
                'created_at',
            ]))
            ->withCount(['entries', 'outputs'])
            ->latest()
            ->get() : collect();
        $normalizeName = static function (?string $value, bool $squash = false): string {
            $text = mb_strtolower(trim((string) $value), 'UTF-8');
            $text = preg_replace('/\s+/u', ' ', $text) ?: '';
            if ($squash) {
                $text = preg_replace('/\s+/u', '', $text) ?: '';
            }
            return $text;
        };
        $stripHonorifics = static function (?string $value): string {
            $text = trim((string) $value);
            if ($text === '') {
                return '';
            }

            $pattern = '/^(?:นายแพทย์หญิง|นายแพทย์|แพทย์หญิง|แพทย์|ทันตแพทย์หญิง|ทันตแพทย์|เภสัชกรหญิง|เภสัชกร|'
                .'ศ\.คลินิก|ศ\.นพ\.|ศ\.พญ\.|รศ\.นพ\.|รศ\.พญ\.|ผศ\.นพ\.|ผศ\.พญ\.|'
                .'ศ\.|รศ\.|ผศ\.|อ\.|ดร\.?|ภกญ\.|ภญ\.|ภก\.|พญ\.|นพ\.|ทพ\.ญ\.|ทพ\.|นาย|นางสาว|นาง)\s*/u';

            do {
                $next = preg_replace($pattern, '', $text);
                if ($next === null || $next === $text) {
                    break;
                }
                $text = trim($next);
            } while ($text !== '');

            return preg_replace('/\s+/u', ' ', trim($text)) ?: '';
        };
        $nameCandidates = static function (?string $prefix, ?string $name, ?string $prefixEn = '', ?string $nameEn = '') use ($stripHonorifics): array {
            $fullName = trim(implode(' ', array_filter([
                trim((string) $prefix),
                trim((string) $name),
            ])));
            $englishFullName = trim(implode(' ', array_filter([
                trim((string) $prefixEn),
                trim((string) $nameEn),
            ])));
            $bareName = trim((string) $name);
            $bareEnglishName = trim((string) $nameEn);

            $strippedFullName = $stripHonorifics($fullName);
            $strippedBareName = $stripHonorifics($bareName);
            $parts = preg_split('/\s+/u', $strippedBareName) ?: [];
            $firstName = trim((string) ($parts[0] ?? ''));
            $firstLastName = count($parts) > 1
                ? trim($parts[0].' '.($parts[count($parts) - 1] ?? ''))
                : '';
            $firstLastNoSpace = count($parts) > 1
                ? trim($parts[0].($parts[count($parts) - 1] ?? ''))
                : '';

            return array_unique(array_filter([
                $fullName,
                $bareName,
                $strippedFullName,
                $strippedBareName,
                preg_replace('/\s+/u', '', $fullName),
                preg_replace('/\s+/u', '', $bareName),
                preg_replace('/\s+/u', '', $strippedFullName),
                preg_replace('/\s+/u', '', $strippedBareName),
                $englishFullName,
                $bareEnglishName,
                preg_replace('/\s+/u', '', $englishFullName),
                preg_replace('/\s+/u', '', $bareEnglishName),
                $firstName,
                $firstLastName,
                $firstLastNoSpace,
            ]));
        };
        $fteProfileUrls = [];

        User::query()
            ->select(['prefix', 'name', 'prefix_en', 'name_en', 'profile_photo_path'])
            ->whereNotNull('name')
            ->whereNotNull('profile_photo_path')
            ->get()
            ->each(function (User $user) use (&$fteProfileUrls, $normalizeName, $nameCandidates) {
                $photoPath = trim((string) $user->profile_photo_path);
                if ($photoPath === '') {
                    return;
                }

                if (preg_match('~^(https?:)?//~i', $photoPath)) {
                    $photoUrl = $photoPath;
                } else {
                    $publicPath = ltrim((string) preg_replace('~^/?storage/~', '', $photoPath), '/');
                    if ($publicPath === '') {
                        return;
                    }
                    $photoUrl = asset('storage/'.$publicPath).'?v='.rawurlencode(md5($publicPath));
                }

                foreach ($nameCandidates($user->prefix, $user->name, $user->prefix_en ?? '', $user->name_en ?? '') as $candidate) {
                    foreach (array_unique(array_filter([
                        $normalizeName($candidate),
                        $normalizeName($candidate, true),
                    ])) as $key) {
                        if ($key !== '' && !array_key_exists($key, $fteProfileUrls)) {
                            $fteProfileUrls[$key] = $photoUrl;
                        }
                    }
                }
            });

        $fteUserMetaByName = [];
        $isConsultantText = static function (?string $value): bool {
            $text = mb_strtolower(trim((string) $value), 'UTF-8');

            return $text !== '' && (
                str_contains($text, 'research consultant')
                || str_contains($text, 'consultant')
                || str_contains($text, 'ที่ปรึกษา')
            );
        };

        User::query()
            ->select($existingColumns('users', [
                'id', 'prefix', 'name', 'prefix_en', 'name_en', 'position', 'skill_role', 'role', 'is_borrowed', 'borrowed_department',
            ]))
            ->whereNotNull('name')
            ->get()
            ->each(function (User $user) use (&$fteUserMetaByName, $normalizeName, $nameCandidates, $isConsultantText) {
                $position = trim((string) ($user->position ?? ''));
                $skillRole = trim((string) ($user->skill_role ?? ''));
                $role = trim((string) ($user->role ?? ''));
                $isBorrowed = (bool) ($user->is_borrowed ?? false);
                $isResigned = $role === User::ROLE_RESIGNED
                    || str_contains(mb_strtolower($position.' '.$skillRole.' '.(string) $user->name, 'UTF-8'), 'ลาออก');
                $isConsultant = $isConsultantText($position)
                    || $isConsultantText($skillRole)
                    || $isConsultantText($user->name);

                $meta = [
                    'id' => $user->id,
                    'name' => trim((string) $user->name),
                    'position' => $position,
                    'skill_role' => $skillRole,
                    'role' => $role,
                    'is_borrowed' => $isBorrowed,
                    'is_resigned' => $isResigned,
                    'is_consultant' => $isConsultant,
                    'is_unit_user' => ! $isBorrowed && ! $isResigned && ! $isConsultant,
                ];

                foreach ($nameCandidates($user->prefix ?? '', $user->name, $user->prefix_en ?? '', $user->name_en ?? '') as $candidate) {
                    foreach (array_unique(array_filter([
                        $normalizeName($candidate),
                        $normalizeName($candidate, true),
                    ])) as $key) {
                        if ($key !== '' && ! array_key_exists($key, $fteUserMetaByName)) {
                            $fteUserMetaByName[$key] = $meta;
                        }
                    }
                }
            });

        $resolveFteUserMeta = static function (?string $name) use ($fteUserMetaByName, $normalizeName, $stripHonorifics): ?array {
            foreach (array_unique(array_filter([
                $name,
                $stripHonorifics($name),
                preg_replace('/\s+/u', '', (string) $name),
                preg_replace('/\s+/u', '', $stripHonorifics($name)),
            ])) as $candidate) {
                foreach (array_unique(array_filter([
                    $normalizeName($candidate),
                    $normalizeName($candidate, true),
                ])) as $key) {
                    if ($key !== '' && array_key_exists($key, $fteUserMetaByName)) {
                        return $fteUserMetaByName[$key];
                    }
                }
            }

            return null;
        };

        $mapDashboardAuthor = static function ($author) use ($resolveFteUserMeta): array {
            $userMeta = $resolveFteUserMeta($author->name);
            $isUnitAuthor = $userMeta !== null
                && ! (bool) ($userMeta['is_resigned'] ?? false)
                && ! (bool) ($userMeta['is_consultant'] ?? false);

            return [
                'name' => $author->name,
                'role' => $author->role,
                'user_id' => $userMeta['id'] ?? null,
                'is_unit_user' => $isUnitAuthor,
                'is_resigned' => (bool) ($userMeta['is_resigned'] ?? false),
                'is_consultant' => (bool) ($userMeta['is_consultant'] ?? false),
                'is_borrowed' => (bool) ($userMeta['is_borrowed'] ?? false),
            ];
        };

        $dashboardProjectsJs = $projects->map(function ($project) use ($resolveFteUserMeta, $mapDashboardAuthor) {
            return [
                'id' => $project->id,
                'project_name_th' => $project->project_name_th,
                'project_code' => $project->project_code,
                'project_type' => $project->project_type,
                'theme' => $project->theme,
                'funding_type' => $project->funding_type,
                'funding_source' => $project->funding_source,
                'funding_year' => $project->funding_year,
                'planning_status' => $project->planning_status,
                'start_year' => $project->start_year,
                'start_date' => $project->start_date,
                'team_start_date' => $project->team_start_date,
                'end_year' => $project->end_year,
                'end_date' => $project->end_date,
                'finish_year' => $project->finish_year,
                'finish_date' => $project->finish_date,
                'team_end_date' => $project->team_end_date,
                'total_budget' => $project->total_budget,
                'budget' => $project->budget,
                'pi_name' => $project->pi_name,
                'pi_affiliation' => $project->pi_affiliation,
                'pi_person_type' => $project->pi_person_type,
                'project_list' => $project->project_list,
                'collab_type' => $project->collab_type,
                'unit_name' => $project->unit_name,
                'department' => $project->department,
                'created_at' => optional($project->created_at)->toDateTimeString(),
                'updated_at' => optional($project->updated_at)->toDateTimeString(),
                'copis' => $project->copis->map(fn($copi) => [
                    'id' => $copi->id,
                    'project_id' => $copi->project_id,
                    'name' => $copi->name,
                    'user_id' => $copi->user_id,
                    'person_type' => $copi->person_type,
                    'affiliation' => $copi->affiliation,
                ])->values(),
                'co_researches' => $project->coResearches->map(fn($coResearch) => [
                    'id' => $coResearch->id,
                    'project_id' => $coResearch->project_id,
                    'name' => $coResearch->name,
                    'user_id' => $coResearch->user_id,
                    'unit_status' => $coResearch->unit_status,
                    'affiliation' => $coResearch->affiliation,
                ])->values(),
                'fte_roles' => $project->fteRoles->map(function ($fteRole) use ($resolveFteUserMeta) {
                    $userMeta = $resolveFteUserMeta($fteRole->name);

                    return [
                        'id' => $fteRole->id,
                        'project_id' => $fteRole->project_id,
                        'name' => $fteRole->name,
                        'role_group' => $fteRole->role_group,
                        'fte_value' => $fteRole->fte_value,
                        'fte' => $fteRole->fte,
                        'hours' => $fteRole->hours,
                        'job_detail' => $fteRole->job_detail,
                        'task_detail' => $fteRole->task_detail,
                        'start_date' => $fteRole->start_date,
                        'end_date' => $fteRole->end_date,
                        'user' => $userMeta,
                        'user_id' => $userMeta['id'] ?? null,
                        'skill_role' => $userMeta['skill_role'] ?? null,
                        'position' => $userMeta['position'] ?? null,
                        'is_unit_user' => (bool) ($userMeta['is_unit_user'] ?? false),
                        'is_resigned' => (bool) ($userMeta['is_resigned'] ?? false),
                        'is_consultant' => (bool) ($userMeta['is_consultant'] ?? false),
                        'is_borrowed' => (bool) ($userMeta['is_borrowed'] ?? false),
                    ];
                })->values(),
                'irbs' => $project->irbs->map(fn($irb) => [
                    'id' => $irb->id,
                    'project_id' => $irb->project_id,
                    'code' => $irb->code,
                    'source' => $irb->source,
                    'irb_source' => $irb->source,
                    'exp_date' => $irb->exp_date,
                ])->values(),
                'outputs' => $project->outputs->map(fn($output) => [
                    'id' => $output->id,
                    'project_id' => $output->project_id,
                    'code' => trim((string) ($output->output_code ?? '')) ?: Output::formatOutputCode((int) $output->id),
                    'output_code' => trim((string) ($output->output_code ?? '')) ?: Output::formatOutputCode((int) $output->id),
                    'project_name' => $project->project_name_th,
                    'type' => $output->type,
                    'title' => $output->title,
                    'year' => $output->year,
                    'journal' => $output->journal,
                    'quartile' => $output->quartile,
                    'impact_factor' => $output->impact_factor,
                    'created_at' => optional($output->created_at)->toDateTimeString(),
                    'updated_at' => optional($output->updated_at)->toDateTimeString(),
                    'authors' => $output->authors->map($mapDashboardAuthor)->values(),
                ])->values(),
            ];
        })->values();
        $dashboardFteSkillAnalysis = app(OutputController::class)->buildFteSkillAnalysis(null, null);

        $externalOutputsData = $externalOutputs->map(function (ExternalOutput $output) use ($mapDashboardAuthor) {
            $publishDate = $this->toCarbonOrNull($output->publish_date);

            return [
                'id' => $output->id,
                'code' => trim((string) ($output->output_code ?? '')) ?: ExternalOutput::formatOutputCode((int) $output->id),
                'output_code' => trim((string) ($output->output_code ?? '')) ?: ExternalOutput::formatOutputCode((int) $output->id),
                'type' => trim((string) ($output->type ?? '')) ?: 'ไม่ระบุประเภท',
                'job_category' => trim((string) ($output->job_category ?? '')) ?: '-',
                'title' => trim((string) ($output->title ?? '')) ?: 'ไม่ระบุชื่อผลงาน',
                'note' => trim((string) ($output->note ?? '')),
                'publish_date' => $publishDate?->format('Y-m-d'),
                'publish_year' => $publishDate?->year ?? $this->extractYear($output->created_at),
                'journal' => trim((string) ($output->journal ?? '')) ?: '-',
                'quartile' => trim((string) ($output->quartile ?? '')) ?: '-',
                'impact_factor' => $this->toFloat($output->impact_factor),
                'created_at' => optional($output->created_at)->toDateTimeString(),
                'updated_at' => optional($output->updated_at)->toDateTimeString(),
                'url' => route('outputs.external-academic.show', $output),
                'authors' => $output->authors->map($mapDashboardAuthor)->values(),
            ];
        })->values();

        $backOfficeData = $backOfficeTasks->map(function (BackOfficeTask $task) use ($mapDashboardAuthor) {
            $hours = (float) $task->entries->sum(fn ($entry) => $this->toFloat($entry->hours));
            $fte = (float) $task->entries->sum(fn ($entry) => $this->toFloat($entry->fte_value));
            $creatorName = trim(implode(' ', array_filter([
                trim((string) ($task->creator?->prefix ?? '')),
                trim((string) ($task->creator?->name ?? '')),
            ])));

            return [
                'id' => $task->id,
                'code' => trim((string) ($task->backoffice_id ?? '')) ?: BackOfficeTask::formatBackOfficeId((int) $task->id),
                'backoffice_id' => trim((string) ($task->backoffice_id ?? '')) ?: BackOfficeTask::formatBackOfficeId((int) $task->id),
                'title' => trim((string) ($task->title ?? '')) ?: 'ไม่ระบุชื่องาน',
                'description' => trim((string) ($task->description ?? '')),
                'purpose' => trim((string) ($task->purpose ?? '')),
                'start_date' => $this->toCarbonOrNull($task->start_date)?->format('Y-m-d'),
                'end_date' => $this->toCarbonOrNull($task->end_date)?->format('Y-m-d'),
                'year' => $this->extractYear($task->start_date ?: $task->created_at),
                'entries_count' => (int) ($task->entries_count ?? 0),
                'outputs_count' => (int) ($task->outputs_count ?? 0),
                'hours' => round($hours, 2),
                'fte' => round($fte, 4),
                'creator_name' => $creatorName !== '' ? $creatorName : 'ไม่ระบุผู้บันทึก',
                'created_at' => optional($task->created_at)->toDateTimeString(),
                'updated_at' => optional($task->updated_at)->toDateTimeString(),
                'url' => route('back_office.show', $task),
                'outputs' => $task->outputs->map(function ($output) use ($task, $mapDashboardAuthor) {
                    $year = $this->extractYear($output->year ?: $output->created_at);
                    return [
                        'id' => $output->id,
                        'code' => 'BO-OUT-'.$output->id,
                        'type' => trim((string) ($output->type ?? '')) ?: 'ไม่ระบุประเภท',
                        'title' => trim((string) ($output->title ?? '')) ?: 'ไม่ระบุชื่อผลงาน',
                        'year' => $year,
                        'journal' => trim((string) ($output->journal ?? $output->publisher_name ?? '')) ?: '-',
                        'created_at' => optional($output->created_at)->toDateTimeString(),
                        'updated_at' => optional($output->updated_at)->toDateTimeString(),
                        'url' => route('back_office.show', $task),
                        'authors' => collect($output->resolvedAuthors())
                            ->map(fn (array $author) => $mapDashboardAuthor((object) $author))
                            ->values(),
                    ];
                })->values(),
            ];
        })->values();

        // Keep backward-compatible payloads for the legacy mobile dashboard view.
        $outputsForJs = $outputs->map(function ($output) use ($projects, $mapDashboardAuthor) {
            $projectName = $projects->firstWhere('id', $output->project_id)?->project_name_th;

            return [
                'id' => $output->id,
                'project_id' => $output->project_id,
                'project_name' => $projectName,
                'type' => $output->type,
                'title' => $output->title,
                'year' => $output->year,
                'created_at' => optional($output->created_at)->toDateTimeString(),
                'authors' => $output->authors->map($mapDashboardAuthor)->values(),
            ];
        })->values();

        $themes = Project::query()
            ->selectRaw('LOWER(TRIM(theme)) as theme_key, MIN(TRIM(theme)) as theme')
            ->whereNotNull('theme')
            ->whereRaw("TRIM(theme) <> ''")
            ->groupBy('theme_key')
            ->orderBy('theme')
            ->pluck('theme');

        $typeOptions = Project::query()
            ->selectRaw('LOWER(TRIM(project_type)) as type_key, MIN(TRIM(project_type)) as project_type')
            ->whereNotNull('project_type')
            ->whereRaw("TRIM(project_type) <> ''")
            ->groupBy('type_key')
            ->orderBy('project_type')
            ->pluck('project_type');

        $statusOptions = Project::query()
            ->selectRaw('LOWER(TRIM(planning_status)) as status_key, MIN(TRIM(planning_status)) as planning_status')
            ->whereNotNull('planning_status')
            ->whereRaw("TRIM(planning_status) <> ''")
            ->groupBy('status_key')
            ->orderBy('planning_status')
            ->pluck('planning_status');

        $fundingSources = Project::query()
            ->selectRaw('LOWER(TRIM(funding_source)) as source_key, MIN(TRIM(funding_source)) as funding_source')
            ->whereNotNull('funding_source')
            ->whereRaw("TRIM(funding_source) <> ''")
            ->groupBy('source_key')
            ->orderBy('funding_source')
            ->pluck('funding_source');

        $view = $request->query('view') === 'legacy'
            ? ResponsiveView::resolve($request, 'dashboard')
            : 'dashboard-insight';

        return view($view, compact(
            'projects',
            'dashboardProjectsJs',
            'dashboardFteSkillAnalysis',
            'copis',
            'coResearches',
            'fteRoles',
            'outputs',
            'outputsForJs',
            'irbs',
            'progressReports',
            'fteProfileUrls',
            'externalOutputsData',
            'backOfficeData',
            'canDownloadDashboardData',
            'themes',
            'typeOptions',
            'statusOptions',
            'fundingSources'
        ));
    }

    public function commandCenter(Request $request)
    {
        $today = now()->startOfDay();
        $existingColumns = static function (string $table, array $desired): array {
            static $cache = [];

            if (!array_key_exists($table, $cache)) {
                $cache[$table] = Schema::getColumnListing($table);
            }

            $selected = array_values(array_intersect($desired, $cache[$table]));

            return $selected ?: ['*'];
        };

        $authUser = $request->user();
        $limitFteToSelf = (bool) ($authUser?->mustLimitFteToSelf() ?? false);
        $authFullName = trim(($authUser->prefix ?? '').' '.($authUser->name ?? ''));

        $projects = Project::query()
            ->withCount(['outputs', 'progressReports', 'irbs', 'tasks'])
            ->with([
                'irbs:id,project_id,code,source,exp_date',
                'progressReports:id,project_id,submit_date,progress_title,created_at',
                'tasks:id,project_id,title,end,status,workflow_status,progress,created_at',
                'fteRoles' => function ($query) use ($existingColumns, $limitFteToSelf, $authFullName) {
                    $query->select($existingColumns('fte_roles', [
                        'id',
                        'project_id',
                        'name',
                        'role_group',
                        'fte_value',
                        'fte_value_research',
                        'hours',
                        'job_detail',
                        'task_detail',
                        'start_date',
                        'end_date',
                        'created_at',
                    ]));

                    if ($limitFteToSelf) {
                        $query->where('name', $authFullName);
                    }
                },
            ])
            ->select($existingColumns('projects', [
                'id',
                'project_code',
                'project_name_th',
                'project_type',
                'funding_type',
                'funding_source',
                'planning_status',
                'execution_status',
                'start_year',
                'start_date',
                'end_date',
                'total_budget',
                'budget',
                'pi_name',
                'department',
                'unit_name',
                'created_at',
                'updated_at',
            ]))
            ->orderByDesc('updated_at')
            ->get();

        $internalOutputs = Output::query()
            ->with(['project:id,project_code,project_name_th,project_type,funding_type'])
            ->select($existingColumns('outputs', [
                'id',
                'project_id',
                'output_code',
                'type',
                'title',
                'year',
                'journal',
                'quartile',
                'impact_factor',
                'created_at',
                'updated_at',
            ]))
            ->latest()
            ->get();

        $externalOutputs = ExternalOutput::query()
            ->select($existingColumns('external_outputs', [
                'id',
                'output_code',
                'type',
                'job_category',
                'title',
                'note',
                'publish_date',
                'journal',
                'quartile',
                'impact_factor',
                'created_at',
                'updated_at',
            ]))
            ->latest()
            ->get();

        $backOfficeTasks = BackOfficeTask::query()
            ->withCount(['entries', 'outputs'])
            ->with([
                'creator:id,prefix,name',
                'entries:id,task_id,hours,fte_value',
            ])
            ->select($existingColumns('back_office_tasks', [
                'id',
                'backoffice_id',
                'title',
                'description',
                'purpose',
                'start_date',
                'end_date',
                'created_by',
                'updated_at',
                'created_at',
            ]))
            ->latest()
            ->get();

        $progressReports = ProgressReport::query()
            ->with(['project:id,project_code,project_name_th'])
            ->select($existingColumns('progress_reports', [
                'id',
                'project_id',
                'submit_date',
                'progress_title',
                'created_at',
            ]))
            ->latest()
            ->get();

        $projectsPayload = $projects->map(function (Project $project) use ($today) {
            $latestIrb = $project->irbs
                ->map(function ($irb) {
                    $exp = $this->toCarbonOrNull($irb->exp_date);
                    return [
                        'code' => trim((string) ($irb->code ?? '')) ?: '-',
                        'source' => trim((string) ($irb->source ?? '')) ?: '-',
                        'exp_date' => $exp?->format('Y-m-d'),
                    ];
                })
                ->sortBy('exp_date')
                ->last();

            $latestProgress = $project->progressReports
                ->sortByDesc(fn ($report) => $this->toCarbonOrNull($report->submit_date)?->timestamp ?? 0)
                ->first();

            $taskRiskCount = $project->tasks->filter(function ($task) use ($today) {
                $endDate = $this->toCarbonOrNull($task->end);
                return $endDate && $endDate->lt($today) && !$this->isTaskCompleted($task->status, $task->workflow_status, $task->progress);
            })->count();

            $budgetValue = $this->toFloat($project->total_budget);
            if ($budgetValue <= 0) {
                $budgetValue = $this->toFloat($project->budget);
            }

            return [
                'id' => $project->id,
                'code' => trim((string) ($project->project_code ?? '')) ?: 'PRJ-' . $project->id,
                'name' => trim((string) ($project->project_name_th ?? '')) ?: 'ไม่ระบุชื่อโครงการ',
                'project_type' => trim((string) ($project->project_type ?? '')) ?: 'ไม่ระบุประเภท',
                'funding_type' => trim((string) ($project->funding_type ?? '')) ?: 'ไม่ระบุประเภททุน',
                'funding_source' => trim((string) ($project->funding_source ?? '')) ?: 'ไม่ระบุแหล่งทุน',
                'planning_status' => trim((string) ($project->planning_status ?? '')) ?: '-',
                'execution_status' => trim((string) ($project->execution_status ?? '')) ?: '-',
                'derived_status' => $this->deriveProjectStatus($project, $today),
                'start_year' => $this->extractYear($project->start_year ?: $project->start_date ?: $project->created_at),
                'start_date' => $this->toCarbonOrNull($project->start_date)?->format('Y-m-d'),
                'end_date' => $this->toCarbonOrNull($project->end_date)?->format('Y-m-d'),
                'budget' => $budgetValue,
                'pi_name' => trim((string) ($project->pi_name ?? '')) ?: 'ไม่ระบุ PI',
                'department' => trim((string) ($project->department ?? '')) ?: 'ไม่ระบุหน่วยงาน',
                'unit_name' => trim((string) ($project->unit_name ?? '')) ?: 'ไม่ระบุหน่วย',
                'outputs_count' => (int) ($project->outputs_count ?? 0),
                'progress_reports_count' => (int) ($project->progress_reports_count ?? 0),
                'irbs_count' => (int) ($project->irbs_count ?? 0),
                'tasks_count' => (int) ($project->tasks_count ?? 0),
                'task_risk_count' => $taskRiskCount,
                'latest_irb' => $latestIrb,
                'latest_progress' => $latestProgress ? [
                    'title' => trim((string) ($latestProgress->progress_title ?? '')) ?: 'รายงานความก้าวหน้า',
                    'submit_date' => $this->toCarbonOrNull($latestProgress->submit_date)?->format('Y-m-d'),
                ] : null,
                'created_at' => optional($project->created_at)->toDateString(),
                'updated_at' => optional($project->updated_at)->toDateString(),
                'url' => route('projects.show', $project),
            ];
        })->values();

        $ftePayload = $projects->flatMap(function (Project $project) {
            return $project->fteRoles->map(function (FteRole $fteRole) use ($project) {
                return [
                    'id' => $fteRole->id,
                    'project_id' => $project->id,
                    'project_code' => trim((string) ($project->project_code ?? '')) ?: 'PRJ-' . $project->id,
                    'project_name' => trim((string) ($project->project_name_th ?? '')) ?: 'ไม่ระบุโครงการ',
                    'project_type' => trim((string) ($project->project_type ?? '')) ?: 'ไม่ระบุประเภท',
                    'funding_type' => trim((string) ($project->funding_type ?? '')) ?: 'ไม่ระบุประเภททุน',
                    'project_status' => $this->deriveProjectStatus($project, now()->startOfDay()),
                    'name' => trim((string) ($fteRole->name ?? '')) ?: 'ไม่ระบุชื่อ',
                    'role_group' => trim((string) ($fteRole->role_group ?? '')) ?: 'ไม่ระบุกลุ่ม',
                    'fte_value' => round($this->toFloat($fteRole->fte_value), 4),
                    'fte_research' => round($this->toFloat($fteRole->fte_value_research), 4),
                    'hours' => round($this->toFloat($fteRole->hours), 2),
                    'job_detail' => trim((string) ($fteRole->job_detail ?? '')),
                    'task_detail' => trim((string) ($fteRole->task_detail ?? '')),
                    'start_date' => $this->toCarbonOrNull($fteRole->start_date)?->format('Y-m-d'),
                    'end_date' => $this->toCarbonOrNull($fteRole->end_date)?->format('Y-m-d'),
                    'year' => $this->extractYear($fteRole->start_date ?: $fteRole->created_at ?: $project->start_date),
                    'url' => route('projects.show', $project),
                ];
            });
        })->values();

        $internalOutputsPayload = $internalOutputs->map(function (Output $output) {
            $project = $output->project;
            $year = $this->extractYear($output->year ?: $output->created_at);
            $categoryUrl = $this->resolveInternalOutputUrlByType($output->type);

            return [
                'id' => $output->id,
                'code' => trim((string) ($output->output_code ?? '')) ?: Output::formatOutputCode((int) $output->id),
                'title' => trim((string) ($output->title ?? '')) ?: 'ไม่ระบุชื่อผลงาน',
                'type' => trim((string) ($output->type ?? '')) ?: 'ไม่ระบุประเภท',
                'year' => $year,
                'journal' => trim((string) ($output->journal ?? '')) ?: '-',
                'quartile' => trim((string) ($output->quartile ?? '')) ?: '-',
                'impact_factor' => $this->toFloat($output->impact_factor),
                'project_id' => $project?->id,
                'project_code' => trim((string) ($project?->project_code ?? '')) ?: '-',
                'project_name' => trim((string) ($project?->project_name_th ?? '')) ?: 'ไม่ผูกโครงการ',
                'project_type' => trim((string) ($project?->project_type ?? '')) ?: 'ไม่ระบุประเภท',
                'funding_type' => trim((string) ($project?->funding_type ?? '')) ?: 'ไม่ระบุประเภททุน',
                'created_at' => optional($output->created_at)->toDateString(),
                'url' => $project ? route('projects.show', $project) : $categoryUrl,
                'source_url' => $categoryUrl,
            ];
        })->values();

        $externalOutputsPayload = $externalOutputs->map(function (ExternalOutput $output) {
            $publishDate = $this->toCarbonOrNull($output->publish_date);

            return [
                'id' => $output->id,
                'code' => trim((string) ($output->output_code ?? '')) ?: ExternalOutput::formatOutputCode((int) $output->id),
                'title' => trim((string) ($output->title ?? '')) ?: 'ไม่ระบุชื่อผลงาน',
                'type' => trim((string) ($output->type ?? '')) ?: 'ไม่ระบุประเภท',
                'job_category' => trim((string) ($output->job_category ?? '')) ?: '-',
                'publish_date' => $publishDate?->format('Y-m-d'),
                'publish_year' => $publishDate?->year ?? $this->extractYear($output->created_at),
                'journal' => trim((string) ($output->journal ?? '')) ?: '-',
                'quartile' => trim((string) ($output->quartile ?? '')) ?: '-',
                'impact_factor' => $this->toFloat($output->impact_factor),
                'note' => trim((string) ($output->note ?? '')),
                'created_at' => optional($output->created_at)->toDateString(),
                'url' => route('outputs.external-academic.show', $output),
            ];
        })->values();

        $backOfficePayload = $backOfficeTasks->map(function (BackOfficeTask $task) {
            $hours = (float) $task->entries->sum(fn ($entry) => $this->toFloat($entry->hours));
            $fte = (float) $task->entries->sum(fn ($entry) => $this->toFloat($entry->fte_value));
            $creatorName = trim(implode(' ', array_filter([
                trim((string) ($task->creator?->prefix ?? '')),
                trim((string) ($task->creator?->name ?? '')),
            ])));

            return [
                'id' => $task->id,
                'code' => trim((string) ($task->backoffice_id ?? '')) ?: BackOfficeTask::formatBackOfficeId((int) $task->id),
                'title' => trim((string) ($task->title ?? '')) ?: 'ไม่ระบุชื่องาน',
                'description' => trim((string) ($task->description ?? '')),
                'purpose' => trim((string) ($task->purpose ?? '')),
                'start_date' => $this->toCarbonOrNull($task->start_date)?->format('Y-m-d'),
                'end_date' => $this->toCarbonOrNull($task->end_date)?->format('Y-m-d'),
                'year' => $this->extractYear($task->start_date ?: $task->created_at),
                'entries_count' => (int) ($task->entries_count ?? 0),
                'outputs_count' => (int) ($task->outputs_count ?? 0),
                'hours' => round($hours, 2),
                'fte' => round($fte, 4),
                'creator_name' => $creatorName !== '' ? $creatorName : 'ไม่ระบุผู้บันทึก',
                'created_at' => optional($task->created_at)->toDateString(),
                'url' => route('back_office.show', $task),
            ];
        })->values();

        $progressReportsPayload = $progressReports->map(function (ProgressReport $report) {
            $submitDate = $this->toCarbonOrNull($report->submit_date);
            return [
                'id' => $report->id,
                'project_id' => $report->project_id,
                'project_code' => trim((string) ($report->project?->project_code ?? '')) ?: '-',
                'project_name' => trim((string) ($report->project?->project_name_th ?? '')) ?: 'ไม่ระบุโครงการ',
                'title' => trim((string) ($report->progress_title ?? '')) ?: 'รายงานความก้าวหน้า',
                'submit_date' => $submitDate?->format('Y-m-d'),
                'year' => $submitDate?->year ?? $this->extractYear($report->created_at),
                'created_at' => optional($report->created_at)->toDateString(),
                'url' => $report->project ? route('projects.show', $report->project) : route('overview'),
            ];
        })->values();

        $alerts = $this->buildCommandCenterAlerts(
            $projectsPayload,
            $backOfficePayload,
            $externalOutputsPayload,
            $today
        );

        $yearOptions = collect()
            ->merge($projectsPayload->pluck('start_year'))
            ->merge($internalOutputsPayload->pluck('year'))
            ->merge($externalOutputsPayload->pluck('publish_year'))
            ->merge($backOfficePayload->pluck('year'))
            ->merge($progressReportsPayload->pluck('year'))
            ->filter(fn ($year) => is_numeric($year) && (int) $year > 2000)
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sort()
            ->values();

        $projectTypeOptions = $projectsPayload->pluck('project_type')->filter()->unique()->sort()->values();
        $fundingTypeOptions = $projectsPayload->pluck('funding_type')->filter()->unique()->sort()->values();
        $projectStatusOptions = $projectsPayload->pluck('derived_status')->filter()->unique()->sort()->values();

        $summary = [
            'projects_total' => $projectsPayload->count(),
            'projects_active' => $projectsPayload->where('derived_status', 'กำลังดำเนินการ')->count(),
            'budget_total' => round((float) $projectsPayload->sum('budget'), 2),
            'internal_outputs_total' => $internalOutputsPayload->count(),
            'external_outputs_total' => $externalOutputsPayload->count(),
            'fte_total' => round((float) $ftePayload->sum('fte_value'), 4),
            'fte_hours_total' => round((float) $ftePayload->sum('hours'), 2),
            'fte_people_total' => $ftePayload->pluck('name')->filter()->unique()->count(),
            'backoffice_total' => $backOfficePayload->count(),
            'backoffice_hours_total' => round((float) $backOfficePayload->sum('hours'), 2),
            'progress_reports_total' => $progressReportsPayload->count(),
            'alert_total' => collect($alerts)->sum(fn ($group) => count($group['rows'] ?? [])),
        ];

        return view('dashboard-command-center', [
            'generatedAt' => now()->timezone('Asia/Bangkok')->format('d/m/Y H:i'),
            'summary' => $summary,
            'projectsData' => $projectsPayload,
            'internalOutputsData' => $internalOutputsPayload,
            'externalOutputsData' => $externalOutputsPayload,
            'fteData' => $ftePayload,
            'backOfficeData' => $backOfficePayload,
            'progressReportsData' => $progressReportsPayload,
            'alertsData' => array_values($alerts),
            'yearOptions' => $yearOptions,
            'projectTypeOptions' => $projectTypeOptions,
            'fundingTypeOptions' => $fundingTypeOptions,
            'projectStatusOptions' => $projectStatusOptions,
        ]);
    }

    private function buildCommandCenterAlerts(
        Collection $projects,
        Collection $backOfficeTasks,
        Collection $externalOutputs,
        Carbon $today
    ): array {
        $alerts = [];

        $alerts[] = [
            'key' => 'irb_expired',
            'title' => 'IRB หมดอายุแล้ว',
            'severity' => 'critical',
            'rows' => $projects
                ->filter(function ($project) use ($today) {
                    $expDate = $this->toCarbonOrNull(data_get($project, 'latest_irb.exp_date'));
                    return $expDate && $expDate->lt($today);
                })
                ->sortBy('latest_irb.exp_date')
                ->take(12)
                ->map(fn ($project) => [
                    'title' => $project['name'],
                    'meta' => trim(($project['code'] ?? '-') . ' | IRB ' . data_get($project, 'latest_irb.code', '-')),
                    'detail' => 'หมดอายุ ' . data_get($project, 'latest_irb.exp_date', '-'),
                    'url' => $project['url'],
                ])
                ->values()
                ->all(),
        ];

        $alerts[] = [
            'key' => 'project_ending_soon',
            'title' => 'โครงการใกล้ครบกำหนดใน 45 วัน',
            'severity' => 'high',
            'rows' => $projects
                ->filter(function ($project) use ($today) {
                    $endDate = $this->toCarbonOrNull($project['end_date'] ?? null);
                    if (!$endDate || ($project['derived_status'] ?? '') === 'เสร็จสิ้น') {
                        return false;
                    }
                    return $endDate->betweenIncluded($today, $today->copy()->addDays(45));
                })
                ->sortBy('end_date')
                ->take(12)
                ->map(fn ($project) => [
                    'title' => $project['name'],
                    'meta' => trim(($project['code'] ?? '-') . ' | ' . ($project['pi_name'] ?? '-')),
                    'detail' => 'ครบกำหนด ' . ($project['end_date'] ?? '-'),
                    'url' => $project['url'],
                ])
                ->values()
                ->all(),
        ];

        $alerts[] = [
            'key' => 'project_no_progress',
            'title' => 'โครงการที่ยังไม่มี progress report',
            'severity' => 'medium',
            'rows' => $projects
                ->filter(function ($project) use ($today) {
                    $startDate = $this->toCarbonOrNull($project['start_date'] ?? null);
                    if (!$startDate || ($project['derived_status'] ?? '') !== 'กำลังดำเนินการ') {
                        return false;
                    }
                    return $startDate->lt($today->copy()->subDays(90)) && (int) ($project['progress_reports_count'] ?? 0) === 0;
                })
                ->sortBy('start_date')
                ->take(12)
                ->map(fn ($project) => [
                    'title' => $project['name'],
                    'meta' => trim(($project['code'] ?? '-') . ' | PI ' . ($project['pi_name'] ?? '-')),
                    'detail' => 'เริ่มแล้วแต่ยังไม่มีรายงานความก้าวหน้า',
                    'url' => $project['url'],
                ])
                ->values()
                ->all(),
        ];

        $alerts[] = [
            'key' => 'project_no_output',
            'title' => 'โครงการ active แต่ยังไม่มี output',
            'severity' => 'medium',
            'rows' => $projects
                ->filter(function ($project) use ($today) {
                    $startDate = $this->toCarbonOrNull($project['start_date'] ?? null);
                    if (!$startDate || ($project['derived_status'] ?? '') !== 'กำลังดำเนินการ') {
                        return false;
                    }
                    return $startDate->lt($today->copy()->subDays(180)) && (int) ($project['outputs_count'] ?? 0) === 0;
                })
                ->sortByDesc('start_date')
                ->take(12)
                ->map(fn ($project) => [
                    'title' => $project['name'],
                    'meta' => trim(($project['code'] ?? '-') . ' | ' . ($project['project_type'] ?? '-')),
                    'detail' => 'ดำเนินการมาระยะหนึ่งแล้วแต่ยังไม่มีผลงานเชื่อมกับโครงการ',
                    'url' => $project['url'],
                ])
                ->values()
                ->all(),
        ];

        $alerts[] = [
            'key' => 'backoffice_no_output',
            'title' => 'Back Office ปิดงานแล้วแต่ยังไม่ผูก output',
            'severity' => 'medium',
            'rows' => $backOfficeTasks
                ->filter(function ($task) use ($today) {
                    $endDate = $this->toCarbonOrNull($task['end_date'] ?? null);
                    return $endDate && $endDate->lt($today) && (int) ($task['outputs_count'] ?? 0) === 0;
                })
                ->sortBy('end_date')
                ->take(12)
                ->map(fn ($task) => [
                    'title' => $task['title'],
                    'meta' => trim(($task['code'] ?? '-') . ' | ผู้บันทึก ' . ($task['creator_name'] ?? '-')),
                    'detail' => 'สิ้นสุด ' . ($task['end_date'] ?? '-') . ' | ยังไม่มี output',
                    'url' => $task['url'],
                ])
                ->values()
                ->all(),
        ];

        $alerts[] = [
            'key' => 'external_output_missing_metadata',
            'title' => 'External Output ที่ metadata ยังไม่ครบ',
            'severity' => 'low',
            'rows' => $externalOutputs
                ->filter(fn ($output) => empty($output['publish_date']) || trim((string) ($output['note'] ?? '')) === '')
                ->take(12)
                ->map(fn ($output) => [
                    'title' => $output['title'],
                    'meta' => trim(($output['code'] ?? '-') . ' | ' . ($output['type'] ?? '-')),
                    'detail' => empty($output['publish_date']) ? 'ยังไม่มีวันที่เผยแพร่' : 'ยังไม่มี note / รายละเอียด',
                    'url' => $output['url'],
                ])
                ->values()
                ->all(),
        ];

        return $alerts;
    }

    private function resolveInternalOutputUrlByType(?string $type): string
    {
        $normalized = strtoupper(trim((string) $type));

        return match (true) {
            str_starts_with($normalized, 'EA101') => route('outputs.papers'),
            str_starts_with($normalized, 'EA103') => route('outputs.presentations'),
            str_starts_with($normalized, 'EA119') => route('outputs.textbooks'),
            default => route('outputs.visualization'),
        };
    }

    private function deriveProjectStatus(Project $project, Carbon $today): string
    {
        $execution = mb_strtolower(trim((string) ($project->execution_status ?? '')), 'UTF-8');
        $planning = mb_strtolower(trim((string) ($project->planning_status ?? '')), 'UTF-8');
        $startDate = $this->toCarbonOrNull($project->start_date);
        $endDate = $this->toCarbonOrNull($project->end_date);

        if ($this->containsAny($execution, ['เสร็จ', 'สิ้นสุด', 'ปิด', 'completed', 'closed', 'finish'])) {
            return 'เสร็จสิ้น';
        }

        if ($endDate && $endDate->lt($today)) {
            return 'เกินกำหนด';
        }

        if ($this->containsAny($planning, ['รอ', 'วางแผน', 'proposal', 'plan']) || ($startDate && $startDate->gt($today))) {
            return 'วางแผน';
        }

        return 'กำลังดำเนินการ';
    }

    private function isTaskCompleted($status, $workflowStatus, $progress): bool
    {
        $statusText = mb_strtolower(trim(implode(' ', array_filter([
            (string) $status,
            (string) $workflowStatus,
        ]))), 'UTF-8');

        if ($this->containsAny($statusText, ['done', 'complete', 'completed', 'closed', 'finished', 'เสร็จ', 'ปิด'])) {
            return true;
        }

        return $this->toFloat($progress) >= 100;
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, mb_strtolower($needle, 'UTF-8'))) {
                return true;
            }
        }

        return false;
    }

    private function extractYear($value): ?int
    {
        if ($value instanceof Carbon) {
            return $value->year;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        if (preg_match('/(20\d{2}|25\d{2})/', $text, $matches)) {
            $year = (int) $matches[1];
            return $year > 2400 ? $year - 543 : $year;
        }

        try {
            return Carbon::parse($text)->year;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function toCarbonOrNull($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        try {
            return Carbon::parse($text);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function toFloat($value): float
    {
        $normalized = preg_replace('/[^0-9.\-]/', '', (string) $value);
        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }

    public function exportPdf(Request $request)
    {
        @set_time_limit(120);
        @ini_set('memory_limit', '512M');

        $user = $request->user();
        $generatedBy = $user?->name ?? 'ไม่ระบุ';

        $data = $request->validate([
            'charts' => 'array',
            'charts.*.id' => 'required|string',
            'charts.*.title' => 'required|string',
            'charts.*.image' => 'required|string',
            'overdueRows' => 'array',
            'irbRows' => 'array',
            'copiRows' => 'array',
            'projectRows' => 'array',
            'meta.generatedText' => 'required|string',
            'meta.generatedAt' => 'nullable|string',
            'meta.logo' => 'nullable|string',
        ]);

        $charts = $data['charts'] ?? [];
        $tables = [
            'overdue' => $data['overdueRows'] ?? [],
            'irb' => $data['irbRows'] ?? [],
            'copi' => $data['copiRows'] ?? [],
            'projects' => $data['projectRows'] ?? [],
        ];

        $meta = [
            'generatedBy' => $generatedBy,
            'generatedText' => $data['meta']['generatedText'] ?? '',
            'generatedAt' => $data['meta']['generatedAt'] ?? '',
            'logo' => $data['meta']['logo'] ?? null,
            'title' => 'Siriraj Health Policy Database Research',
            'subtitle' => 'Dashboard Export: กราฟและตารางสถานะโครงการ',
        ];

        $html = view('dashboard_pdf', compact('charts', 'tables', 'meta'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'THSarabunNew');

        // ✅ ปิดการรัน <script type="text/php"> (ตัด footer ที่ถูก inject แบบ dompdf)
        $options->set('isPhpEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        $stamp = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
        $userSlug = preg_replace('/\\s+/', '', $generatedBy);
        $filename = "SiHealthpolicy_overview_{$userSlug}_{$stamp}.pdf";

        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'dashboard_pdf',
            'subject_label' => 'Dashboard Export',
        ], $request);

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\""
        ]);
    }
}

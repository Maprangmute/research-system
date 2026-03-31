<?php

// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
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

        $projects = Project::query()
            ->select($existingColumns('projects', $projectColumns))
            ->with([
                'coResearches' => fn($query) => $query->select($existingColumns('co_research', [
                    'id', 'project_id', 'name', 'unit_status', 'affiliation',
                ])),
                'copis' => fn($query) => $query->select($existingColumns('copis', [
                    'id', 'project_id', 'name', 'affiliation',
                ])),
                'fteRoles' => fn($query) => $query->select($existingColumns('fte_roles', [
                    'id', 'project_id', 'name', 'role_group', 'fte_value', 'fte', 'hours',
                    'job_detail', 'task_detail', 'start_date', 'end_date',
                ])),
                'irbs' => fn($query) => $query->select($existingColumns('irbs', [
                    'id', 'project_id', 'code', 'source', 'exp_date',
                ])),
                'progressReports',
                'outputs' => fn($query) => $query
                    ->select($existingColumns('outputs', [
                        'id', 'project_id', 'type', 'title', 'year', 'created_at',
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
        $fteProfileUrls = [];

        User::query()
            ->select(['prefix', 'name', 'profile_photo_path'])
            ->whereNotNull('name')
            ->whereNotNull('profile_photo_path')
            ->get()
            ->each(function (User $user) use (&$fteProfileUrls, $normalizeName, $stripHonorifics) {
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

                $fullName = trim(implode(' ', array_filter([
                    trim((string) $user->prefix),
                    trim((string) $user->name),
                ])));
                $bareName = trim((string) $user->name);

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

                foreach (array_unique(array_filter([
                    $fullName,
                    $bareName,
                    $strippedFullName,
                    $strippedBareName,
                    preg_replace('/\s+/u', '', $fullName),
                    preg_replace('/\s+/u', '', $bareName),
                    preg_replace('/\s+/u', '', $strippedFullName),
                    preg_replace('/\s+/u', '', $strippedBareName),
                    $firstName,
                    $firstLastName,
                    $firstLastNoSpace,
                ])) as $candidate) {
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

        $dashboardProjectsJs = $projects->map(function ($project) {
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
                    'affiliation' => $copi->affiliation,
                ])->values(),
                'co_researches' => $project->coResearches->map(fn($coResearch) => [
                    'id' => $coResearch->id,
                    'project_id' => $coResearch->project_id,
                    'name' => $coResearch->name,
                    'unit_status' => $coResearch->unit_status,
                    'affiliation' => $coResearch->affiliation,
                ])->values(),
                'fte_roles' => $project->fteRoles->map(fn($fteRole) => [
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
                ])->values(),
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
                    'project_name' => $project->project_name_th,
                    'type' => $output->type,
                    'title' => $output->title,
                    'year' => $output->year,
                    'created_at' => optional($output->created_at)->toDateTimeString(),
                    'authors' => $output->authors->map(fn($author) => [
                        'name' => $author->name,
                        'role' => $author->role,
                    ])->values(),
                ])->values(),
            ];
        })->values();

        // Keep backward-compatible payloads for the legacy mobile dashboard view.
        $outputsForJs = $outputs->map(function ($output) use ($projects) {
            $projectName = $projects->firstWhere('id', $output->project_id)?->project_name_th;

            return [
                'id' => $output->id,
                'project_id' => $output->project_id,
                'project_name' => $projectName,
                'type' => $output->type,
                'title' => $output->title,
                'year' => $output->year,
                'created_at' => optional($output->created_at)->toDateTimeString(),
                'authors' => $output->authors->map(fn($author) => [
                    'name' => $author->name,
                    'role' => $author->role,
                ])->values(),
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

        $view = 'dashboard';
        $forceView = $request->query('view');
        if ($forceView === 'mobile') {
            $view = 'dashboard_mobile';
        } elseif ($forceView !== 'desktop') {
            $ua = (string) $request->header('User-Agent', '');
            $isMobileOrTablet = (bool) preg_match(
                '/Android|webOS|iPhone|iPod|iPad|Tablet|PlayBook|Silk|Kindle|BlackBerry|IEMobile|Opera Mini|Macintosh.*Mobile/i',
                $ua
            );
            if ($isMobileOrTablet) {
                $view = 'dashboard_mobile';
            }
        }

        return view($view, compact(
            'projects',
            'dashboardProjectsJs',
            'copis',
            'coResearches',
            'fteRoles',
            'outputs',
            'outputsForJs',
            'irbs',
            'progressReports',
            'fteProfileUrls',
            'themes',
            'typeOptions',
            'statusOptions',
            'fundingSources'
        ));
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

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\""
        ]);
    }
}

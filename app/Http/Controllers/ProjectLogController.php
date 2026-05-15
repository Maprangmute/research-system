<?php

namespace App\Http\Controllers;

use App\Models\ProjectLog;
use App\Models\Project;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\ResponsiveView;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PDF;

class ProjectLogController extends Controller
{
    private const TIMEZONE_BANGKOK = 'Asia/Bangkok';

    private function resolveView(Request $request, string $base): string
    {
        return ResponsiveView::resolve($request, $base);
    }

    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user || !$user->isAdminEquivalent()) {
            abort(403);
        }
    }

    /**
     * แสดง log ทั้งหมด
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();
        [
            'entity' => $entity,
            'verb' => $verb,
            'search' => $search,
            'timeMode' => $timeMode,
            'month' => $month,
            'yearBeFromRequest' => $yearBeFromRequest,
            'yearCe' => $yearCe,
            'filterMonth' => $filterMonth,
            'filterYear' => $filterYear,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'thaiMonths' => $thaiMonths,
        ] = $this->resolveFilterInputs($request);
        $fuzzyProjectIds = $this->findFuzzyProjectIds($search);
        $fuzzyUserIds = $this->findFuzzyUserIds($search);
        $relatedPersonProjectIds = $this->findRelatedPersonProjectIds($search);

        $query = ProjectLog::query()->with([
            'user',
            'project' => fn ($q) => $q->withTrashed(),
        ]);
        $this->applyLogFilters($query, $entity, $verb, $search, $timeMode, $filterMonth, $filterYear, $startDate, $endDate, $fuzzyProjectIds, $fuzzyUserIds, $relatedPersonProjectIds);

        $sort = $request->input('sort', 'latest');
        $sortMap = [
            'latest' => ['project_logs.created_at', 'desc'],
            'oldest' => ['project_logs.created_at', 'asc'],
            'project_asc' => ['projects.project_name_th', 'asc'],
            'project_desc' => ['projects.project_name_th', 'desc'],
            'user_asc' => ['users.name', 'asc'],
            'user_desc' => ['users.name', 'desc'],
            'action_asc' => ['project_logs.action', 'asc'],
            'action_desc' => ['project_logs.action', 'desc'],
        ];

        if (array_key_exists($sort, $sortMap)) {
            $query->leftJoin('projects', 'project_logs.project_id', '=', 'projects.id')
                ->leftJoin('users', 'project_logs.user_id', '=', 'users.id')
                ->select('project_logs.*')
                ->orderBy($sortMap[$sort][0], $sortMap[$sort][1]);
        } else {
            $query->latest();
        }

        $statsQuery = ProjectLog::query();
        $this->applyLogFilters($statsQuery, $entity, $verb, $search, $timeMode, $filterMonth, $filterYear, $startDate, $endDate, $fuzzyProjectIds, $fuzzyUserIds, $relatedPersonProjectIds);

        $logs = $query->paginate(50)->appends($request->query());
        $groupedLogs = $logs->getCollection()->groupBy(function ($log) {
            $bangkokTime = optional($log->created_at)->copy()->setTimezone('Asia/Bangkok');
            // รวมแบบอ่านง่าย: เวลาเดียวกัน + project + user + กริยาเดียวกัน (create/update/delete/restore)
            return optional($bangkokTime)->format('Y-m-d H:i:s')
                . '|' . ($log->project_id ?? '-')
                . '|' . ($log->user_id ?? '-')
                . '|' . ($log->action_verb ?? '-');
        });
        $projectDistinctExpr = "COALESCE(CAST(project_logs.project_id AS CHAR), CONCAT('name:', COALESCE(project_logs.project_name_th, 'ไม่ระบุโครงการ')))";
        $topUpdaters = (clone $statsQuery)
            ->leftJoin('users', 'project_logs.user_id', '=', 'users.id')
            ->selectRaw("COALESCE(NULLIF(TRIM(CONCAT(COALESCE(users.prefix, ''), ' ', COALESCE(users.name, ''))), ''), 'ไม่ทราบผู้ใช้') AS user_label")
            ->selectRaw("COUNT(DISTINCT {$projectDistinctExpr}) AS total_projects")
            ->selectRaw('COUNT(*) AS total_logs')
            ->groupBy('project_logs.user_id', 'users.prefix', 'users.name')
            ->orderByDesc('total_projects')
            ->orderByDesc('total_logs')
            ->limit(10)
            ->get();
        $topUpdaterLabels = $topUpdaters->pluck('user_label')->values();
        $topUpdaterCounts = $topUpdaters->pluck('total_projects')->map(fn ($v) => (int) $v)->values();
        $totalProjectsInRange = (int) ((clone $statsQuery)->selectRaw("COUNT(DISTINCT {$projectDistinctExpr}) AS total_projects")->value('total_projects') ?? 0);
        $periodLabel = $this->buildPeriodLabel($timeMode, $filterMonth, $filterYear, $startDate, $endDate);

        $entityOptions = [
            'all' => 'ทั้งหมด',
            'project' => 'โครงการ',
            'output' => 'ผลผลิต',
            'output_author' => 'ผู้เขียนผลงาน',
            'back_office_task' => 'Back Office',
            'back_office_entry' => 'ภาระงาน Back Office',
            'back_office_output' => 'ผลงาน Back Office',
            'external_output' => 'Extra',
            'external_output_author' => 'ผู้เขียน Extra',
            'external_output_conference' => 'การนำเสนอ Extra',
            'fte' => 'FTE',
            'task' => 'งาน',
            'tasklink' => 'ลิงก์งาน',
            'copi' => 'ผู้ร่วมวิจัย',
            'coresearch' => 'นักวิจัยร่วม',
            'irb' => 'IRB',
        ];

        $verbOptions = [
            'all' => 'ทั้งหมด',
            'create' => 'สร้าง',
            'update' => 'แก้ไข',
            'delete' => 'ลบ',
            'restore' => 'กู้คืน',
        ];

        $view = $this->resolveView($request, 'project_logs.index');
        return view($view, compact(
            'logs',
            'groupedLogs',
            'entityOptions',
            'verbOptions',
            'entity',
            'verb',
            'search',
            'sort',
            'timeMode',
            'filterMonth',
            'filterYear',
            'thaiMonths',
            'month',
            'yearBeFromRequest',
            'startDate',
            'endDate',
            'topUpdaters',
            'topUpdaterLabels',
            'topUpdaterCounts',
            'totalProjectsInRange',
            'periodLabel'
        ));
    }

    public function projectSuggestions(Request $request)
    {
        $this->authorizeAdmin();
        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $normalized = $this->normalizeText($q);
        $tokens = array_values(array_filter(explode(' ', preg_replace('/\s+/u', ' ', $normalized))));

        $projectCandidates = Project::query()
            ->withTrashed()
            ->select(['id', 'project_name_th', 'project_name_en', 'project_code'])
            ->where(function ($query) use ($q, $tokens) {
                $query->where('project_name_th', 'like', '%' . $q . '%')
                    ->orWhere('project_name_en', 'like', '%' . $q . '%')
                    ->orWhere('project_code', 'like', '%' . $q . '%');
                foreach ($tokens as $token) {
                    if ($token === '') {
                        continue;
                    }
                    $query->orWhere('project_name_th', 'like', '%' . $token . '%')
                        ->orWhere('project_name_en', 'like', '%' . $token . '%')
                        ->orWhere('project_code', 'like', '%' . $token . '%');
                }
            })
            ->limit(200)
            ->get();

        $projectRanked = $projectCandidates->map(function ($project) use ($normalized, $tokens) {
            $target = trim(implode(' ', array_filter([
                (string) ($project->project_name_th ?? ''),
                (string) ($project->project_name_en ?? ''),
                (string) ($project->project_code ?? ''),
            ])));
            $score = $this->calculateFuzzyScore($normalized, $tokens, $target);
            return [
                'id' => (int) $project->id,
                'label' => trim((string) ($project->project_name_th ?: $project->project_name_en ?: ('โครงการ #' . $project->id))),
                'project_code' => (string) ($project->project_code ?? ''),
                'type' => 'project',
                'score' => $score,
            ];
        });

        $userCandidates = User::query()
            ->select(['id', 'prefix', 'name'])
            ->where(function ($query) use ($q, $tokens) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('prefix', 'like', '%' . $q . '%');
                foreach ($tokens as $token) {
                    if ($token === '') {
                        continue;
                    }
                    $query->orWhere('name', 'like', '%' . $token . '%')
                        ->orWhere('prefix', 'like', '%' . $token . '%');
                }
            })
            ->limit(120)
            ->get();

        $userRanked = $userCandidates->map(function ($user) use ($normalized, $tokens) {
            $fullName = trim((string) (($user->prefix ?? '') . ' ' . ($user->name ?? '')));
            $score = $this->calculateFuzzyScore($normalized, $tokens, $fullName);
            return [
                'id' => (int) $user->id,
                'label' => $fullName !== '' ? $fullName : 'ไม่ทราบผู้ใช้',
                'project_code' => '',
                'type' => 'user',
                'score' => $score,
            ];
        });

        $ranked = $projectRanked
            ->concat($userRanked)
            ->filter(fn ($item) => $item['score'] > 0)
            ->sortByDesc('score')
            ->values()
            ->take(10)
            ->map(fn ($item) => [
                'id' => $item['id'],
                'label' => $item['label'],
                'project_code' => $item['project_code'],
                'type' => $item['type'],
            ])
            ->values();

        return response()->json(['data' => $ranked]);
    }

    public function export(Request $request)
    {
        $this->authorizeAdmin();
        $format = strtolower(trim((string) $request->query('format', 'csv')));
        if (!in_array($format, ['csv', 'xls', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        [
            'entity' => $entity,
            'verb' => $verb,
            'search' => $search,
            'timeMode' => $timeMode,
            'filterMonth' => $filterMonth,
            'filterYear' => $filterYear,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ] = $this->resolveFilterInputs($request);
        $fuzzyProjectIds = $this->findFuzzyProjectIds($search);
        $fuzzyUserIds = $this->findFuzzyUserIds($search);
        $relatedPersonProjectIds = $this->findRelatedPersonProjectIds($search);

        $query = ProjectLog::query()->with([
            'user',
            'project' => fn ($q) => $q->withTrashed(),
        ]);
        $this->applyLogFilters($query, $entity, $verb, $search, $timeMode, $filterMonth, $filterYear, $startDate, $endDate, $fuzzyProjectIds, $fuzzyUserIds, $relatedPersonProjectIds);
        $rows = $query->latest('project_logs.created_at')->limit(15000)->get()->map(function ($log) {
            $log->change_summary = $this->summarizeLogChanges(is_array($log->changes) ? $log->changes : []);
            return $log;
        });
        $groupedExportRows = $this->groupRowsForExport($rows);

        $periodLabel = $this->buildPeriodLabel($timeMode, $filterMonth, $filterYear, $startDate, $endDate);
        $generatedAt = now(self::TIMEZONE_BANGKOK);
        $generatedBy = trim(((string) auth()->user()?->prefix) . ' ' . ((string) auth()->user()?->name));
        if ($generatedBy === '') {
            $generatedBy = 'ไม่ระบุ';
        }

        $stamp = $generatedAt->format('Ymd_His');
        $baseName = "project_logs_{$stamp}";

        if ($format === 'csv') {
            AuditLogger::logFileDownload("{$baseName}.csv", [
                'subject_type' => 'project_log_export',
                'subject_label' => 'Project Log',
            ], $request);
            return response()->streamDownload(function () use ($groupedExportRows) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($handle, ['วันเวลา(พ.ศ.)', 'ชื่อโครงการ', 'ผู้อัปเดท', 'จำนวนรายการที่ยุบ', 'ประเภท', 'เหตุการณ์', 'action_key', 'รายละเอียดการเปลี่ยนแปลง']);
                foreach ($groupedExportRows as $row) {
                    fputcsv($handle, [
                        $row['thai_date'],
                        $row['project_name'],
                        $row['user_name'],
                        $row['items_count'],
                        $row['entities'],
                        $row['actions'],
                        $row['action_keys'],
                        $row['change_summary'],
                    ]);
                }
                fclose($handle);
            }, "{$baseName}.csv", [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        if ($format === 'pdf') {
            $pdf = PDF::loadView('project_logs.export-pdf', [
                'rows' => $groupedExportRows,
                'periodLabel' => $periodLabel,
                'generatedAt' => $generatedAt,
                'generatedBy' => $generatedBy,
            ], [], [
                'format' => 'A4-L',
            ]);
            AuditLogger::logFileDownload("{$baseName}.pdf", [
                'subject_type' => 'project_log_export',
                'subject_label' => 'Project Log',
            ], $request);
            return $pdf->stream("{$baseName}.pdf");
        }

        $html = view('project_logs.export-xls', [
            'rows' => $groupedExportRows,
            'periodLabel' => $periodLabel,
            'generatedAt' => $generatedAt,
            'generatedBy' => $generatedBy,
        ])->render();
        AuditLogger::logFileDownload("{$baseName}.xls", [
            'subject_type' => 'project_log_export',
            'subject_label' => 'Project Log',
        ], $request);
        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$baseName}.xls\"",
        ]);
    }

    /**
     * แสดง log ทั้งหมดของ project เดียว (แสดงใน project detail)
     */
    public function projectLogs(Request $request, $projectId)
    {
        $this->authorizeAdmin();
        $project = Project::withTrashed()->find($projectId);
        $logs = ProjectLog::with('user')
            ->where('project_id', $projectId)
            ->latest()
            ->get();
        if (!$project) {
            $latest = $logs->first();
            $project = (object) [
                'id' => $projectId,
                'project_name_th' => $latest->project_name_th ?? 'โครงการที่ถูกลบ',
            ];
        }
        $groupedLogs = $logs->groupBy(function ($log) {
            $bangkokTime = optional($log->created_at)->copy()->setTimezone('Asia/Bangkok');
            return optional($bangkokTime)->format('Y-m-d H:i:s')
                . '|' . ($log->user_id ?? '-')
                . '|' . ($log->action_verb ?? '-');
        });
        $view = $this->resolveView($request, 'project_logs.project');
        return view($view, compact('project', 'logs', 'groupedLogs'));
    }

    /**
     * แสดง log รายการเดียว
     */
    public function show(Request $request, $id)
    {
        $this->authorizeAdmin();
        $log = ProjectLog::with([
            'user',
            'project' => fn ($q) => $q->withTrashed(),
        ])->findOrFail($id);
        $view = $this->resolveView($request, 'project_logs.show');
        return view($view, compact('log'));
    }

    /**
     * ลบ log (option สำหรับ admin)
     */
    public function destroy($id)
    {
        $this->authorizeAdmin();
        $log = ProjectLog::findOrFail($id);
        // สามารถเพิ่ม permission เช็คว่าเป็น admin เท่านั้น
        $log->delete();
        return redirect()->back()->with('success', 'ลบ log สำเร็จ');
    }

    /**
     * ลบ log ที่เก่ากว่า 3 เดือน
     */
    public function purgeOld(Request $request)
    {
        $this->authorizeAdmin();
        $cutoff = now()->subMonths(3);
        $deleted = ProjectLog::where('created_at', '<', $cutoff)->delete();

        return redirect()->back()->with('success', "ลบ log เก่ากว่า 3 เดือนแล้ว {$deleted} รายการ");
    }

    private function applyLogFilters(
        Builder $query,
        ?string $entity,
        ?string $verb,
        string $search,
        string $timeMode,
        string $filterMonth,
        string $filterYear,
        string $startDate,
        string $endDate,
        array $fuzzyProjectIds = [],
        array $fuzzyUserIds = [],
        array $relatedPersonProjectIds = []
    ): void {
        if (!empty($entity) && $entity !== 'all') {
            if ($entity === 'project') {
                $query->whereIn('action', ['create', 'update', 'delete', 'restore']);
            } else {
                $query->where('action', 'like', $entity . '_%');
            }
        }

        if (!empty($verb) && $verb !== 'all') {
            $query->where(function ($q) use ($verb) {
                $q->where('action', $verb)
                    ->orWhere('action', 'like', '%_' . $verb);
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search, $fuzzyProjectIds, $fuzzyUserIds) {
                $q->whereHas('project', function ($project) use ($search) {
                    $project->where('project_name_th', 'like', "%{$search}%")
                        ->orWhere('project_name_en', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%");
                })->orWhereHas('user', function ($user) use ($search) {
                    $user->where('name', 'like', "%{$search}%")
                        ->orWhere('prefix', 'like', "%{$search}%");
                });

                if ($fuzzyProjectIds !== []) {
                    $q->orWhereIn('project_logs.project_id', $fuzzyProjectIds);
                }
                if ($fuzzyUserIds !== []) {
                    $q->orWhereIn('project_logs.user_id', $fuzzyUserIds);
                }
                if ($relatedPersonProjectIds !== []) {
                    $q->orWhereIn('project_logs.project_id', $relatedPersonProjectIds);
                }
            });
        }

        $this->applyTimeFilter($query, $timeMode, $filterMonth, $filterYear, $startDate, $endDate);
    }

    private function applyTimeFilter(
        Builder $query,
        string $timeMode,
        string $filterMonth,
        string $filterYear,
        string $startDate,
        string $endDate
    ): void {
        if ($timeMode === 'month' && preg_match('/^\d{4}-\d{2}$/', $filterMonth)) {
            $start = Carbon::createFromFormat('Y-m', $filterMonth, self::TIMEZONE_BANGKOK)->startOfMonth()->utc();
            $end = Carbon::createFromFormat('Y-m', $filterMonth, self::TIMEZONE_BANGKOK)->endOfMonth()->utc();
            $query->whereBetween('project_logs.created_at', [$start, $end]);
            return;
        }

        if ($timeMode === 'year' && preg_match('/^\d{4}$/', $filterYear)) {
            $start = Carbon::createFromFormat('Y', $filterYear, self::TIMEZONE_BANGKOK)->startOfYear()->utc();
            $end = Carbon::createFromFormat('Y', $filterYear, self::TIMEZONE_BANGKOK)->endOfYear()->utc();
            $query->whereBetween('project_logs.created_at', [$start, $end]);
            return;
        }

        if ($timeMode === 'range') {
            $startParsed = $this->parseFilterDateInput($startDate);
            $endParsed = $this->parseFilterDateInput($endDate);

            if ($startParsed instanceof Carbon) {
                $start = $startParsed->copy()->startOfDay()->utc();
                $query->where('project_logs.created_at', '>=', $start);
            }
            if ($endParsed instanceof Carbon) {
                $end = $endParsed->copy()->endOfDay()->utc();
                $query->where('project_logs.created_at', '<=', $end);
            }
        }
    }

    private function buildPeriodLabel(
        string $timeMode,
        string $filterMonth,
        string $filterYear,
        string $startDate,
        string $endDate
    ): string {
        if ($timeMode === 'month' && preg_match('/^\d{4}-\d{2}$/', $filterMonth)) {
            $month = Carbon::createFromFormat('Y-m', $filterMonth, self::TIMEZONE_BANGKOK);
            return 'เดือน ' . $month->locale('th')->translatedFormat('F') . ' ' . ((int) $month->format('Y') + 543);
        }

        if ($timeMode === 'year' && preg_match('/^\d{4}$/', $filterYear)) {
            return 'ปี ' . ((int) $filterYear + 543);
        }

        if ($timeMode === 'range') {
            $startParsed = $this->parseFilterDateInput($startDate);
            $endParsed = $this->parseFilterDateInput($endDate);
            $displayStart = $startParsed instanceof Carbon
                ? $startParsed->copy()->locale('th')->translatedFormat('d M') . ' ' . ($startParsed->year + 543)
                : '-';
            $displayEnd = $endParsed instanceof Carbon
                ? $endParsed->copy()->locale('th')->translatedFormat('d M') . ' ' . ($endParsed->year + 543)
                : '-';
            return "ช่วง {$displayStart} ถึง {$displayEnd}";
        }

        return 'ทุกช่วงเวลา';
    }

    private function findFuzzyProjectIds(string $search): array
    {
        $search = trim($search);
        if (mb_strlen($search) < 2) {
            return [];
        }

        $normalized = $this->normalizeText($search);
        $tokens = array_values(array_filter(explode(' ', preg_replace('/\s+/u', ' ', $normalized))));

        $candidates = Project::query()
            ->withTrashed()
            ->select(['id', 'project_name_th', 'project_name_en', 'project_code'])
            ->where(function ($query) use ($search, $tokens) {
                $query->where('project_name_th', 'like', '%' . $search . '%')
                    ->orWhere('project_name_en', 'like', '%' . $search . '%')
                    ->orWhere('project_code', 'like', '%' . $search . '%');
                foreach ($tokens as $token) {
                    if ($token === '') {
                        continue;
                    }
                    $query->orWhere('project_name_th', 'like', '%' . $token . '%')
                        ->orWhere('project_name_en', 'like', '%' . $token . '%')
                        ->orWhere('project_code', 'like', '%' . $token . '%');
                }
            })
            ->limit(400)
            ->get();

        return $candidates
            ->map(function ($project) use ($normalized, $tokens) {
                $target = trim(implode(' ', array_filter([
                    (string) ($project->project_name_th ?? ''),
                    (string) ($project->project_name_en ?? ''),
                    (string) ($project->project_code ?? ''),
                ])));
                $score = $this->calculateFuzzyScore($normalized, $tokens, $target);
                return ['id' => (int) $project->id, 'score' => $score];
            })
            ->filter(fn ($row) => $row['score'] >= 45)
            ->sortByDesc('score')
            ->take(50)
            ->pluck('id')
            ->values()
            ->all();
    }

    private function findRelatedPersonProjectIds(string $search): array
    {
        $search = trim($search);
        if (mb_strlen($search, 'UTF-8') < 2) {
            return [];
        }

        $searchLower = mb_strtolower($search, 'UTF-8');
        $normalized = $this->normalizeText($searchLower);
        $tokens = array_values(array_filter(explode(' ', preg_replace('/\s+/u', ' ', $normalized))));

        return Project::query()
            ->withTrashed()
            ->select('id')
            ->where(function (Builder $query) use ($search, $searchLower, $normalized, $tokens) {
                $nameMatcher = function (Builder $rel) use ($search, $searchLower, $normalized, $tokens) {
                    $rel->where('name', 'like', '%'.$search.'%')
                        ->orWhereRaw('LOWER(name) like ?', ['%'.$searchLower.'%']);

                    if ($normalized !== '') {
                        $rel->orWhereRaw("REGEXP_REPLACE(LOWER(TRIM(COALESCE(name, ''))), '[[:space:][:punct:]]+', '') like ?", ['%'.$normalized.'%']);
                    }

                    foreach ($tokens as $token) {
                        $rel->orWhereRaw('LOWER(name) like ?', ['%'.$token.'%']);
                    }
                };

                $query->whereHas('fteRoles', $nameMatcher)
                    ->orWhereHas('copis', $nameMatcher)
                    ->orWhereHas('coResearches', $nameMatcher);
            })
            ->limit(300)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
    }

    private function findFuzzyUserIds(string $search): array
    {
        $search = trim($search);
        if (mb_strlen($search) < 2) {
            return [];
        }

        $normalized = $this->normalizeText($search);
        $tokens = array_values(array_filter(explode(' ', preg_replace('/\s+/u', ' ', $normalized))));

        return User::query()
            ->select(['id', 'prefix', 'name'])
            ->where(function ($query) use ($search, $tokens) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('prefix', 'like', '%' . $search . '%');
                foreach ($tokens as $token) {
                    if ($token === '') {
                        continue;
                    }
                    $query->orWhere('name', 'like', '%' . $token . '%')
                        ->orWhere('prefix', 'like', '%' . $token . '%');
                }
            })
            ->limit(400)
            ->get()
            ->map(function ($user) use ($normalized, $tokens) {
                $fullName = trim(((string) ($user->prefix ?? '')) . ' ' . ((string) ($user->name ?? '')));
                return [
                    'id' => (int) $user->id,
                    'score' => $this->calculateFuzzyScore($normalized, $tokens, $fullName),
                ];
            })
            ->filter(fn ($row) => $row['score'] >= 45)
            ->sortByDesc('score')
            ->take(50)
            ->pluck('id')
            ->values()
            ->all();
    }

    private function calculateFuzzyScore(string $normalizedNeedle, array $tokens, string $candidateText): int
    {
        $normalizedHaystack = $this->normalizeText($candidateText);
        if ($normalizedHaystack === '' || $normalizedNeedle === '') {
            return 0;
        }

        $score = 0;
        if (Str::contains($normalizedHaystack, $normalizedNeedle)) {
            $score += 65;
        }

        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }
            if (Str::contains($normalizedHaystack, $token)) {
                $score += 8;
            }
        }

        similar_text($normalizedNeedle, $normalizedHaystack, $percent);
        $score += (int) round($percent * 0.5);

        return $score;
    }

    private function normalizeText(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = preg_replace('/[^\pL\pN\s]+/u', ' ', $text);
        $text = preg_replace('/\s+/u', ' ', (string) $text);
        return trim((string) $text);
    }

    private function summarizeLogChanges(array $changes): string
    {
        if ($changes === []) {
            return '';
        }

        $parts = [];
        foreach ($changes as $field => $value) {
            if (in_array((string) $field, ['id', 'project_id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                continue;
            }

            $from = null;
            $to = null;
            if (is_array($value) && array_key_exists('from', $value) && array_key_exists('to', $value)) {
                $from = $value['from'];
                $to = $value['to'];
            } elseif (is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value)) {
                $from = $value['old'];
                $to = $value['new'];
            } elseif (is_array($value)) {
                $from = null;
                $to = $value;
            } else {
                $from = null;
                $to = $value;
            }

            $fromText = $this->stringifyChangeValue($from);
            $toText = $this->stringifyChangeValue($to);

            // เอาเฉพาะฟิลด์ที่เปลี่ยนจริง
            if ($fromText === $toText) {
                continue;
            }

            // ไม่แสดง JSON ให้เป็นข้อความอ่านง่าย
            if ($this->isComplexValue($from) || $this->isComplexValue($to)) {
                $parts[] = $this->fieldLabel((string) $field) . ': มีการปรับปรุงข้อมูล';
                continue;
            }

            $parts[] = $this->fieldLabel((string) $field) . ': ' . $fromText . ' -> ' . $toText;
        }

        return implode(' | ', $parts);
    }

    private function stringifyChangeValue($value): string
    {
        if ($value === null) {
            return '(ว่าง)';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_scalar($value)) {
            $text = trim((string) $value);
            return $text !== '' ? $text : '(ว่าง)';
        }
        return '(มีข้อมูล)';
    }

    private function isComplexValue($value): bool
    {
        return is_array($value) || is_object($value);
    }

    private function fieldLabel(string $field): string
    {
        $map = [
            'project_name_th' => 'ชื่อโครงการ (TH)',
            'project_name_en' => 'ชื่อโครงการ (EN)',
            'project_code' => 'รหัสโครงการ',
            'pi_name' => 'ชื่อ PI',
            'planning_status' => 'สถานะโครงการ',
            'execution_status' => 'สถานะดำเนินงาน',
            'closure_status' => 'สถานะปิดโครงการ',
            'review_status' => 'สถานะ review',
            'budget' => 'งบประมาณ',
            'funding_type' => 'ประเภททุน',
            'funding_source' => 'แหล่งทุน',
            'start_year' => 'ปีเริ่มต้น',
            'start_date' => 'วันที่เริ่ม',
            'end_date' => 'วันที่สิ้นสุด',
            'name' => 'ชื่อ',
            'position' => 'ภาระงาน/ตำแหน่ง',
            'hours' => 'ชั่วโมง',
            'task_detail' => 'รายละเอียดภารกิจ',
        ];
        return $map[$field] ?? str_replace('_', ' ', $field);
    }

    private function resolveFilterInputs(Request $request): array
    {
        $entity = $request->input('entity');
        $verb = $request->input('verb');
        $search = trim((string) $request->input('q'));
        $timeMode = (string) $request->input('time_mode', 'month');
        $now = now(self::TIMEZONE_BANGKOK);
        $monthFromRequest = (int) $request->input('filter_month_th', (int) $now->format('n'));
        $yearBeFromRequest = (int) $request->input('filter_year_be', (int) $now->format('Y') + 543);
        $month = max(1, min(12, $monthFromRequest));
        $yearCe = $yearBeFromRequest > 2400 ? $yearBeFromRequest - 543 : ($yearBeFromRequest > 1900 ? $yearBeFromRequest : (int) $now->format('Y'));
        $filterMonth = sprintf('%04d-%02d', $yearCe, $month);
        $filterYear = (string) $yearCe;
        $startDate = (string) $request->input('start_date', '');
        $endDate = (string) $request->input('end_date', '');
        $thaiMonths = [
            1 => 'มกราคม',
            2 => 'กุมภาพันธ์',
            3 => 'มีนาคม',
            4 => 'เมษายน',
            5 => 'พฤษภาคม',
            6 => 'มิถุนายน',
            7 => 'กรกฎาคม',
            8 => 'สิงหาคม',
            9 => 'กันยายน',
            10 => 'ตุลาคม',
            11 => 'พฤศจิกายน',
            12 => 'ธันวาคม',
        ];

        return compact(
            'entity',
            'verb',
            'search',
            'timeMode',
            'month',
            'yearBeFromRequest',
            'yearCe',
            'filterMonth',
            'filterYear',
            'startDate',
            'endDate',
            'thaiMonths'
        );
    }

    private function parseFilterDateInput(?string $value): ?Carbon
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $normalized = str_replace('/', '-', $raw);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalized)) {
            return Carbon::createFromFormat('Y-m-d', $normalized, self::TIMEZONE_BANGKOK);
        }

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $normalized)) {
            $candidate = Carbon::createFromFormat('d-m-Y', $normalized, self::TIMEZONE_BANGKOK);
            if ((int) $candidate->format('Y') > 2400) {
                $candidate->subYears(543);
            }
            return $candidate;
        }

        try {
            return Carbon::parse($normalized, self::TIMEZONE_BANGKOK);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function groupRowsForExport(Collection $logs): Collection
    {
        $rows = $logs->map(function ($log) {
            $time = optional($log->created_at)->copy()?->setTimezone(self::TIMEZONE_BANGKOK);
            $thaiDate = $time?->copy()?->addYears(543)?->format('d/m/Y H:i:s') ?: '-';
            $projectName = $log->project?->project_name_th ?? $log->project_name_th ?? '-';
            $userName = trim(((string) ($log->user?->prefix ?? '')) . ' ' . ((string) ($log->user?->name ?? ''))) ?: 'ไม่ทราบผู้ใช้';
            return [
                'time_key' => $time?->format('Y-m-d H:i:s') ?: '-',
                'thai_date' => $thaiDate,
                'project_name' => $projectName,
                'user_name' => $userName,
                'entity' => (string) ($log->entity_label ?? '-'),
                'action' => (string) ($log->action_label ?? '-'),
                'action_key' => (string) ($log->action ?? '-'),
                'change_summary' => trim((string) ($log->change_summary ?? '')) ?: '-',
            ];
        });

        return $rows->groupBy(function ($row) {
            return $row['time_key'] . '|' . $row['project_name'] . '|' . $row['user_name'];
        })->map(function ($group) {
            $first = $group->first();
            $changes = $group->pluck('change_summary')
                ->filter(fn ($text) => trim((string) $text) !== '' && trim((string) $text) !== '-')
                ->unique()
                ->values();

            return [
                'thai_date' => $first['thai_date'],
                'project_name' => $first['project_name'],
                'user_name' => $first['user_name'],
                'items_count' => (int) $group->count(),
                'entities' => $group->pluck('entity')->filter()->unique()->values()->implode("\n"),
                'actions' => $group->pluck('action')->filter()->unique()->values()->implode("\n"),
                'action_keys' => $group->pluck('action_key')->filter()->unique()->values()->implode("\n"),
                'change_summary' => $changes->isNotEmpty() ? $changes->implode("\n") : '-',
            ];
        })->values();
    }
}

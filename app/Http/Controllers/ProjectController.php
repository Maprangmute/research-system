<?php

namespace App\Http\Controllers;

use App\Models\Output;
use App\Models\Project;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\ResponsiveView;
// ⬅️ เพิ่ม
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use PDF;

class ProjectController extends Controller
{
    private const CO_RESEARCH_INTERNAL_UNIT = 'ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
    private const CO_RESEARCH_INTERNAL_AFFILIATION = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ คณะแพทยศาสตร์ศิริราชพยาบาล มหาวิทยาลัยมหิดล';
    private const PI_INTERNAL_AFFILIATION = self::CO_RESEARCH_INTERNAL_AFFILIATION;
    private const PERSON_TYPE_INTERNAL_FACULTY = 'ภายในคณะแพทยศาสตร์ศิริราชพยาบาล';
    private const PERSON_TYPE_EXTERNAL_FACULTY = 'ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล';
    private const PERSON_TYPE_INTERNAL_UNIT = self::CO_RESEARCH_INTERNAL_UNIT;
    private const LEGACY_PERSON_TYPE_MAP = [
        'internal' => self::PERSON_TYPE_INTERNAL_UNIT,
        'external' => self::PERSON_TYPE_EXTERNAL_FACULTY,
    ];
    private const ROLE_PERSON_TYPES = [
        self::PERSON_TYPE_INTERNAL_FACULTY,
        self::PERSON_TYPE_EXTERNAL_FACULTY,
        self::PERSON_TYPE_INTERNAL_UNIT,
    ];
    private const CO_RESEARCH_ALLOWED_UNIT_STATUSES = [
        self::PERSON_TYPE_INTERNAL_FACULTY,
        self::PERSON_TYPE_EXTERNAL_FACULTY,
        self::CO_RESEARCH_INTERNAL_UNIT,
    ];

    public function index(Request $request)
    {
        $view = ResponsiveView::resolve($request, 'projects.index');

        $user = auth()->user();
        $applyPermission = function ($q) use ($user) {
            if ($user->isAdminLike()) {
                return;
            }
            $fullName = trim(($user->prefix ?? '').' '.$user->name);
            $q->where(function ($inner) use ($fullName, $user) {
                $inner->where('user_id', $user->id)
                    ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
        };

        $query = Project::query()
            ->withCount('outputs')
            ->with([
                'fteRoles:id,project_id,name',
                'copis:id,project_id,user_id,name,affiliation',
                'coResearches:id,project_id,name',
            ]);
        $applyPermission($query);

        $baseQuery = Project::query();
        $applyPermission($baseQuery);

        // filter
        if ($request->filled('year')) {
            $query->where('start_year', $request->year);
        }
        if ($request->filled('type')) {
            $query->where('project_type', 'like', '%'.$request->type.'%');
        }
        if ($request->filled('pi_name')) {
            $query->whereRaw('TRIM(pi_name) = ?', [$request->pi_name]);
        }
        $selectedPersonName = trim((string) $request->input('person_name', ''));
        if ($selectedPersonName !== '') {
            $this->applyPersonRoleFilter($query, $selectedPersonName);
        }
        if ($request->filled('funding_source')) {
            $query->whereRaw('TRIM(funding_source) = ?', [$request->funding_source]);
        }
        if ($request->filled('project_status')) {
            $status = trim((string) $request->project_status);
            if ($status === '__unspecified__') {
                $query->where(function ($q) {
                    $q->whereNull('planning_status')
                        ->orWhereRaw("TRIM(planning_status) = ''");
                });
            } else {
                $query->whereRaw('TRIM(planning_status) = ?', [$status]);
            }
        }
        if ($request->filled('budget_range') && !$request->filled('budget_min') && !$request->filled('budget_max')) {
            [$minRange, $maxRange] = array_pad(explode('-', $request->budget_range), 2, null);
            if (is_numeric($minRange)) {
                $query->where('budget', '>=', $minRange);
            }
            if (is_numeric($maxRange)) {
                $query->where('budget', '<=', $maxRange);
            }
        }
        if ($request->filled('budget_min')) {
            $query->where('budget', '>=', $request->budget_min);
        }
        if ($request->filled('budget_max')) {
            $query->where('budget', '<=', $request->budget_max);
        }
        $issueOverdue = $request->boolean('issue_overdue');
        $issueMissingDates = $request->boolean('issue_missing_dates');
        $issueDuplicates = $request->boolean('issue_duplicates');
        $issueNoOutput = $request->boolean('issue_no_output');

        if ($request->filled('last_update')) {
            $now = now();
            if ($request->last_update === '1m') {
                $query->where('updated_at', '<', $now->copy()->subMonth())
                    ->where('updated_at', '>=', $now->copy()->subMonths(2));
            }
            if ($request->last_update === '2m') {
                $query->where('updated_at', '<', $now->copy()->subMonths(2))
                    ->where('updated_at', '>=', $now->copy()->subMonths(3));
            }
            if ($request->last_update === '3m') {
                $query->where('updated_at', '<', $now->copy()->subMonths(3))
                    ->where('updated_at', '>=', $now->copy()->subMonths(4));
            }
            if ($request->last_update === 'gt3') {
                $query->where('updated_at', '<', $now->copy()->subMonths(4));
            }
        }
        if ($request->filled('fte_status')) {
            if ($request->fte_status === 'missing') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('fteRoles')
                        ->orWhereHas('fteRoles', function ($subQ) {
                            $subQ->whereNull('fte_value')->orWhere('fte_value', 0);
                        });
                });
            }
            if ($request->fte_status === 'complete') {
                $query->whereHas('fteRoles')
                    ->whereDoesntHave('fteRoles', function ($subQ) {
                        $subQ->whereNull('fte_value')->orWhere('fte_value', 0);
                });
            }
        }
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $searchScope = clone $query;
            $this->applyProjectNameSearch($query, $searchScope, $search);
        }

        if ($issueOverdue || $issueMissingDates || $issueDuplicates || $issueNoOutput) {
            $startDateExpr = $this->projectNormalizedDateSql('start_date');
            $endDateExpr = $this->projectNormalizedDateSql('end_date');
            $duplicateProjectIds = $issueDuplicates ? $this->findDuplicateProjectIds(clone $query) : [];

            $query->where(function ($issueQuery) use (
                $issueOverdue,
                $issueMissingDates,
                $issueDuplicates,
                $issueNoOutput,
                $startDateExpr,
                $endDateExpr,
                $duplicateProjectIds
            ) {
                if ($issueOverdue) {
                    $issueQuery->orWhereRaw("{$endDateExpr} IS NOT NULL AND {$endDateExpr} < CURDATE()");
                }

                if ($issueMissingDates) {
                    $issueQuery->orWhereRaw("{$startDateExpr} IS NULL OR {$endDateExpr} IS NULL");
                }

                if ($issueDuplicates) {
                    if ($duplicateProjectIds === []) {
                        $issueQuery->orWhereRaw('0 = 1');
                    } else {
                        $issueQuery->orWhereIn('id', $duplicateProjectIds);
                    }
                }

                if ($issueNoOutput) {
                    $issueQuery->orWhereDoesntHave('outputs');
                }
            });
        }

        // sort
        $sortable = ['id', 'project_code', 'project_name_th', 'project_type', 'funding_type', 'theme', 'planning_status', 'start_year', 'updated_at', 'created_at', 'budget'];
        $sort = in_array($request->get('sort'), $sortable, true) ? $request->get('sort') : 'updated_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        if ($issueDuplicates) {
            $nameSortExpr = $this->projectNormalizedTextSql('project_name_th');
            $query->orderByRaw("{$nameSortExpr} asc")
                ->orderByRaw("CHAR_LENGTH(TRIM(COALESCE(project_name_th, ''))) asc")
                ->orderBy('project_name_th', 'asc');
        }

        $projects = $query->orderBy($sort, $dir)
            ->paginate(10)
            ->appends($request->query());

        $fullName = auth()->user()->prefix.' '.auth()->user()->name;

        $missingFTE = \App\Models\FteRole::where('name', $fullName)
            ->where(function ($q) {
                $q->whereNull('fte_value')
                    ->orWhere('fte_value', 0);
            })
            ->with('project:id,project_code,project_name_th,start_year')
            ->get();

        $piOptions = (clone $baseQuery)
            ->selectRaw('LOWER(TRIM(pi_name)) as name_key, MIN(TRIM(pi_name)) as pi_name')
            ->whereNotNull('pi_name')
            ->whereRaw("TRIM(pi_name) <> ''")
            ->groupBy('name_key')
            ->orderBy('pi_name')
            ->pluck('pi_name');
        $personOptions = $this->buildPersonFilterOptions(clone $baseQuery);
        $personRoleSummary = $selectedPersonName !== ''
            ? $this->buildPersonRoleSummary($selectedPersonName, $projects->getCollection()->all())
            : [];
        $fundingSources = (clone $baseQuery)
            ->selectRaw('LOWER(TRIM(funding_source)) as source_key, MIN(TRIM(funding_source)) as funding_source')
            ->whereNotNull('funding_source')
            ->whereRaw("TRIM(funding_source) <> ''")
            ->groupBy('source_key')
            ->orderBy('funding_source')
            ->pluck('funding_source');
        $typeOptions = (clone $baseQuery)
            ->selectRaw('LOWER(TRIM(project_type)) as type_key, MIN(TRIM(project_type)) as project_type')
            ->whereNotNull('project_type')
            ->whereRaw("TRIM(project_type) <> ''")
            ->groupBy('type_key')
            ->orderBy('project_type')
            ->pluck('project_type');
        $statusOptions = (clone $baseQuery)
            ->selectRaw('LOWER(TRIM(planning_status)) as status_key, MIN(TRIM(planning_status)) as planning_status')
            ->whereNotNull('planning_status')
            ->whereRaw("TRIM(planning_status) <> ''")
            ->groupBy('status_key')
            ->orderBy('planning_status')
            ->pluck('planning_status');
        $yearOptions = (clone $baseQuery)
            ->whereNotNull('start_year')
            ->distinct()
            ->orderBy('start_year', 'desc')
            ->pluck('start_year');
        $budgetMin = (clone $baseQuery)->min('budget') ?? 0;
        $budgetMax = (clone $baseQuery)->max('budget') ?? 0;

        return view($view, [
            'projects' => $projects,
            'missingFTE' => $missingFTE,
            'piOptions' => $piOptions,
            'personOptions' => $personOptions,
            'selectedPersonName' => $selectedPersonName,
            'personRoleSummary' => $personRoleSummary,
            'fundingSources' => $fundingSources,
            'typeOptions' => $typeOptions,
            'statusOptions' => $statusOptions,
            'yearOptions' => $yearOptions,
            'budgetMin' => $budgetMin,
            'budgetMax' => $budgetMax,
        ]);
    }

    public function dataQualityReport(Request $request)
    {
        $payload = $this->buildProjectDataQualityReportPayload($request);
        $rows = $payload['rows'];

        return view('outputs.project-data-quality', array_merge($payload, [
            'rows' => $this->paginateProjectDataQualityRows($rows, $request, 12),
            'totalRows' => $rows->count(),
            'exportMinDate' => $this->projectDataQualityExportMinDate($rows),
            'quickFixFields' => $this->projectDataQualityQuickFixFields(),
            'quickFixUsers' => User::query()
                ->orderBy('name')
                ->get(['id', 'prefix', 'name'])
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                ])
                ->values(),
        ]));
    }

    public function dataQualityReportPdf(Request $request)
    {
        set_time_limit(120);
        ini_set('max_execution_time', '120');
        ini_set('pcre.backtrack_limit', '5000000');

        $payload = $this->buildProjectDataQualityReportPayload($request);
        $rows = $this->filterProjectDataQualityRowsByExportDateRange($payload['rows'], $request);
        $summary = $this->summarizeProjectDataQualityRows($rows);
        $generatedAt = now('Asia/Bangkok');
        $reporter = trim((string) (auth()->user()?->full_name ?? '')) ?: 'ผู้ใช้ระบบ';
        $filters = $this->buildProjectDataQualityExportFilters($request);
        $filename = $this->buildProjectDataQualityExportFilename('PROJECT_DATA_QUALITY', 'pdf');

        $pdf = PDF::loadView('outputs.project-data-quality-pdf', [
            'rows' => $rows,
            'summary' => $summary,
            'categoryOptions' => collect($this->projectDataQualityCategories()),
            'reporter' => $reporter,
            'generatedAt' => $generatedAt,
            'filters' => $filters,
        ], [], [
            'format' => 'A4-L',
        ]);

        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'project_data_quality_pdf',
            'subject_label' => 'Project Data Quality Report',
        ], $request);

        return $pdf->stream($filename);
    }

    public function dataQualityReportExcel(Request $request)
    {
        $payload = $this->buildProjectDataQualityReportPayload($request);
        $rows = $this->filterProjectDataQualityRowsByExportDateRange($payload['rows'], $request);
        $summary = $this->summarizeProjectDataQualityRows($rows);
        $filename = $this->buildProjectDataQualityExportFilename('PROJECT_DATA_QUALITY', 'xlsx');
        $columns = [
            '#',
            'Project ID',
            'ชื่อโครงการ',
            'PI',
            'สถานะโครงการ',
            'กลุ่มเนื้อหาหลักของงาน / ประเภทของโครงการ',
            'จำนวน Output',
            'ระดับ',
            'จำนวน Issue',
            'หมวดปัญหา',
            'รายละเอียดปัญหา',
            'Field ที่ต้องแก้',
            'ควรทำ',
            'ผลกระทบถ้าไม่แก้',
            'สร้างโดย',
            'สร้างเมื่อ',
            'อัปเดทล่าสุดโดย',
            'อัปเดทล่าสุดเมื่อ',
            'ลิงก์เปิด Record',
            'ลิงก์แก้ไข Record',
            'Quick Fix Fields',
            'Field Target',
            'ข้อมูลซ้ำที่พบ',
        ];

        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'project_data_quality_excel',
            'subject_label' => 'Project Data Quality Report',
        ], $request);

        return $this->projectDataQualityXlsxResponse(
            $filename,
            'Data Quality',
            $columns,
            $this->projectDataQualityExcelRows($rows),
            [
                ['รายงานข้อมูลผิดพลาดของโครงการ'],
                ['เงื่อนไข', $this->buildProjectDataQualityExportFilters($request)->implode(' | ') ?: 'ทั้งหมด'],
                ['ออกรายงาน', $this->formatProjectDateTimeThai(now('Asia/Bangkok'))],
                ['ผู้สร้างรายงาน', trim((string) (auth()->user()?->full_name ?? '')) ?: 'ผู้ใช้ระบบ'],
                ['สรุป', 'Projects ' . number_format($summary['projects']) . ' | Issues ' . number_format($summary['issues'])],
            ]
        );
    }

    public function dataQualityQuickFix(Request $request, Project $project)
    {
        $fullName = trim((Auth::user()?->prefix ?? '').' '.(Auth::user()?->name ?? ''));
        $hasPermission = Auth::user()?->isAdminLike()
            || Auth::id() === $project->user_id
            || $project->fteRoles()->where('name', $fullName)->exists();

        abort_unless($hasPermission, 403);

        $allowedFields = $this->projectDataQualityQuickFixFields();
        $incoming = collect($request->only(array_keys($allowedFields)))
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->all();

        if ($incoming === []) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'ไม่มีข้อมูลที่เลือกแก้ไข'], 422);
            }

            return back()->with('error', 'ไม่มีข้อมูลที่เลือกแก้ไข');
        }

        if (array_key_exists('budget', $incoming)) {
            $request->merge(['budget' => str_replace(',', '', (string) $incoming['budget'])]);
            $incoming['budget'] = $request->input('budget');
        }

        $rules = [];
        foreach (array_keys($incoming) as $field) {
            $rules[$field] = $allowedFields[$field]['rule'];
        }

        $validated = $request->validate($rules);
        $dateFields = ['team_start_date', 'team_end_date', 'proposal_date', 'contract_date', 'start_date', 'end_date'];
        foreach ($dateFields as $field) {
            if (array_key_exists($field, $validated)) {
                $rawDate = trim((string) ($validated[$field] ?? ''));
                if ($rawDate === '') {
                    $validated[$field] = null;
                    continue;
                }

                $date = $this->parseProjectDateInput($rawDate);
                if (! $date) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'รูปแบบวันที่ไม่ถูกต้อง',
                            'errors' => [$field => ['รูปแบบวันที่ไม่ถูกต้อง']],
                        ], 422);
                    }

                    return back()
                        ->withErrors([$field => 'รูปแบบวันที่ไม่ถูกต้อง'])
                        ->withInput();
                }

                $validated[$field] = $date->format('d-m-Y');
            }
        }

        if (array_key_exists('budget', $validated)) {
            $budget = (string) $validated['budget'];
            $validated['budget'] = $budget === '' ? null : (float) $budget;
        }

        if (array_key_exists('pi_user_id', $validated) && filled($validated['pi_user_id'])) {
            $user = User::query()->find((int) $validated['pi_user_id']);
            if ($user) {
                $validated['pi_name'] = $user->full_name;
                $validated['pi_affiliation'] = self::PI_INTERNAL_AFFILIATION;
            }
        }
        unset($validated['pi_user_id']);

        $project->fill($validated);
        $project->updated_by = Auth::id();
        $redirectFields = collect(array_keys($incoming))
            ->reject(fn ($field) => $field === 'pi_user_id')
            ->when(
                array_key_exists('pi_user_id', $incoming),
                fn ($fields) => $fields->prepend('pi_name')->push('pi_affiliation')
            )
            ->filter()
            ->unique()
            ->values()
            ->all();
        $firstRedirectField = $redirectFields[0] ?? null;
        $redirectUrl = route('projects.edit', array_filter([
            'project' => $project,
            'view' => 'desktop',
            'dq_fields' => $redirectFields !== [] ? implode(',', $redirectFields) : null,
        ], fn ($value) => $value !== null));

        if ($firstRedirectField) {
            $redirectUrl .= '#field-' . rawurlencode($firstRedirectField);
        }

        if ($project->isDirty()) {
            $project->save();
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'บันทึก Quick Fix เรียบร้อยแล้ว',
                    'project_id' => $project->getKey(),
                ]);
            }

            return redirect($redirectUrl)->with('success', 'บันทึก Quick Fix เรียบร้อยแล้ว');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'ไม่มีข้อมูลเปลี่ยนแปลง',
                'project_id' => $project->getKey(),
            ]);
        }

        return redirect($redirectUrl)->with('success', 'ไม่มีข้อมูลเปลี่ยนแปลง');
    }

    private function buildProjectDataQualityReportPayload(Request $request): array
    {
        $user = auth()->user();
        $applyPermission = function ($q) use ($user) {
            if ($user->isAdminLike()) {
                return;
            }

            $fullName = trim(($user->prefix ?? '').' '.$user->name);
            $q->where(function ($inner) use ($fullName, $user) {
                $inner->where('user_id', $user->id)
                    ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
        };

        $baseQuery = Project::query();
        $applyPermission($baseQuery);
        $duplicateProjectIds = $this->findDuplicateProjectIds(clone $baseQuery);

        $projectsQuery = Project::query()
            ->withCount(['outputs', 'fteRoles', 'progressReports', 'irbs'])
            ->with([
                'createdBy:id,prefix,name',
                'updatedBy:id,prefix,name',
                'latestLog' => fn ($query) => $query->select([
                    'project_logs.id',
                    'project_logs.project_id',
                    'project_logs.user_id',
                    'project_logs.action',
                    'project_logs.created_at',
                ]),
                'latestLog.user:id,prefix,name',
                'fteRoles:id,project_id,name,fte_value',
                'irbs:id,project_id,code,source,exp_date',
                'progressReports:id,project_id,submit_date,created_at',
            ]);
        $applyPermission($projectsQuery);

        $rows = $this->buildProjectDataQualityRows($projectsQuery->get(), $duplicateProjectIds);
        $issueLabelOptions = $rows
            ->flatMap(fn ($row) => collect($row->issues)->pluck('label'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $category = trim((string) $request->input('category', ''));
        if ($category !== '') {
            $rows = $rows
                ->filter(fn ($row) => collect($row->issues)->contains(fn ($issue) => $issue['category'] === $category))
                ->values();
        }

        $issueLabel = trim((string) $request->input('issue_label', ''));
        if ($issueLabel !== '') {
            $rows = $rows
                ->filter(fn ($row) => collect($row->issues)->contains(fn ($issue) => (string) ($issue['label'] ?? '') === $issueLabel))
                ->values();
        }

        $severity = trim((string) $request->input('severity', ''));
        if ($severity !== '') {
            $rows = $rows
                ->filter(fn ($row) => (int) $row->severity_score === (int) $severity)
                ->values();
        }

        $outputStatus = trim((string) $request->input('output_status', ''));
        if ($outputStatus === 'none') {
            $rows = $rows->filter(fn ($row) => (int) $row->outputs_count === 0)->values();
        } elseif ($outputStatus === 'has') {
            $rows = $rows->filter(fn ($row) => (int) $row->outputs_count > 0)->values();
        }

        $activity = trim((string) $request->input('activity', ''));
        if ($activity !== '') {
            $now = now('Asia/Bangkok');
            $rows = $rows->filter(function ($row) use ($activity, $now) {
                $updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->setTimezone('Asia/Bangkok') : null;
                return match ($activity) {
                    'updated_30' => $updatedAt && $updatedAt->gte($now->copy()->subDays(30)),
                    'updated_90' => $updatedAt && $updatedAt->gte($now->copy()->subDays(90)),
                    'stale_90' => ! $updatedAt || $updatedAt->lt($now->copy()->subDays(90)),
                    default => true,
                };
            })->values();
        }

        $search = mb_strtolower(trim((string) $request->input('search', '')), 'UTF-8');
        if ($search !== '') {
            $rows = $rows
                ->filter(function ($row) use ($search) {
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        $row->project_code,
                        $row->project_name,
                        $row->pi_name,
                        $row->project_type,
                        $row->planning_status,
                        collect($row->issues)->pluck('reason')->implode(' '),
                        collect($row->issues)->pluck('fields')->flatten()->implode(' '),
                    ])), 'UTF-8');

                    return str_contains($haystack, $search);
                })
                ->values();
        }

        $sort = trim((string) $request->input('sort', 'severity'));
        $rows = (match ($sort) {
            'project_code' => $rows->sortBy(fn ($row) => $row->project_code ?: ''),
            'outputs' => $rows->sortBy(fn ($row) => (int) $row->outputs_count),
            'issues' => $rows->sortByDesc(fn ($row) => count($row->issues)),
            'updated_at' => $rows->sortByDesc(fn ($row) => $row->updated_at?->timestamp ?? 0),
            default => $rows->sortByDesc(fn ($row) => ((int) $row->severity_score * 100) + count($row->issues)),
        })->values();

        $summary = $this->summarizeProjectDataQualityRows($rows);
        $categoryOptions = collect($this->projectDataQualityCategories());

        return [
            'rows' => $rows,
            'summary' => $summary,
            'categoryOptions' => $categoryOptions,
            'issueLabelOptions' => $issueLabelOptions,
            'selectedCategory' => $category,
            'selectedIssueLabel' => $issueLabel,
            'selectedSeverity' => $severity,
            'selectedOutputStatus' => $outputStatus,
            'selectedActivity' => $activity,
            'search' => $request->input('search', ''),
            'sort' => $sort,
        ];
    }

    private function projectDataQualityCategories(): array
    {
        return [
            'duplicate' => 'ข้อมูลซ้ำ',
            'incomplete' => 'ข้อมูลไม่ครบ / วันที่ไม่ครบ',
            'overdue' => 'Overdue',
            'no_output' => 'ไม่มี output',
            'other' => 'อื่นๆ',
        ];
    }

    private function buildProjectDataQualityRows(Collection $projects, array $duplicateProjectIds): Collection
    {
        $duplicateLookup = array_fill_keys(array_map('intval', $duplicateProjectIds), true);
        $duplicateMatches = $this->projectDataQualityDuplicateMatches($projects, $duplicateProjectIds);
        $today = now('Asia/Bangkok')->startOfDay();

        return $projects
            ->map(function (Project $project) use ($duplicateLookup, $duplicateMatches, $today) {
                $issues = [];
                $startDate = $this->parseProjectDateInput((string) $project->start_date);
                $endDate = $this->parseProjectDateInput((string) $project->end_date);
                $teamStartDate = $this->parseProjectDateInput((string) $project->team_start_date);
                $teamEndDate = $this->parseProjectDateInput((string) $project->team_end_date);

                if (isset($duplicateLookup[(int) $project->id])) {
                    $matches = collect($duplicateMatches[(int) $project->id] ?? [])->take(4)->values();
                    $matchLabels = $matches
                        ->map(fn ($match) => trim(implode(' | ', array_filter([
                            $match['project_code'] ?? null,
                            $match['project_name'] ?? null,
                            isset($match['start_year']) && $match['start_year'] !== '' ? 'ปี '.$match['start_year'] : null,
                        ]))))
                        ->filter()
                        ->values();
                    $duplicateIssue = $this->projectDataQualityIssue(
                        'duplicate',
                        'ข้อมูลซ้ำ',
                        $matchLabels->isNotEmpty()
                            ? 'ชื่อโครงการซ้ำหรือใกล้เคียงกับโครงการด้านล่าง'
                            : 'ชื่อโครงการซ้ำหรือใกล้เคียงกับโครงการอื่นในระบบ',
                        [],
                        'ตรวจว่าเป็นโครงการเดียวกันหรือคนละโครงการ โดยดู Project ID, ปีโครงการ และเจ้าของ record ก่อน merge หรือปิด record ซ้ำ',
                        4
                    );
                    $duplicateIssue['duplicate_matches'] = $matches
                        ->take(4)
                        ->map(fn ($match) => [
                            'id' => (int) ($match['id'] ?? 0),
                            'project_code' => $match['project_code'] ?? '',
                            'project_name' => $match['project_name'] ?? '',
                            'start_year' => $match['start_year'] ?? '',
                        ])
                        ->filter(fn ($match) => $match['id'] > 0)
                        ->values()
                        ->all();
                    $issues[] = $duplicateIssue;
                }

                $missingCore = collect([
                    'Project ID' => $project->project_code,
                    'ชื่อโครงการ (ภาษาไทย)' => $project->project_name_th,
                    'PI' => $project->pi_name,
                    'กลุ่มเนื้อหาหลักของงาน (Content/Theme)' => $project->project_type,
                    'ลักษณะโครงการ' => $project->project_list,
                    'ประเภทของโครงการ/กิจกรรม (TYPE)' => $project->theme,
                    'เอกสารโครงการ (Proposal / IRB)' => $project->shortcut_link,
                    'ขนาด' => $project->size,
                    'รหัสโครงการ R' => $project->r_code,
                    'ปีโครงการ' => $project->start_year,
                    'สถานะโครงการ (Project Status)' => $project->planning_status,
                ])->filter(fn ($value) => $this->isProjectDataQualityBlank($value))->keys()->values();

                $missingDates = collect([
                    'BA001 - วันที่เริ่มเขียน Proposal' => $project->proposal_date,
                    'BC101 - วันที่สัญญาอนุมัติ' => $project->contract_date,
                    'BC102 - วันที่โครงการเริ่ม (ตามสัญญา)' => $project->start_date,
                    'BC103 - วันที่สิ้นสุด (ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ)' => $project->end_date,
                    'วันที่โครงการเริ่มทำงานของทีมวิจัย' => $project->team_start_date,
                    'วันที่สิ้นสุดการทำงานของทีมวิจัย' => $project->team_end_date,
                ])->filter(fn ($value) => $this->isProjectDataQualityBlank($value))->keys()->values();

                if ($missingCore->isNotEmpty() || $missingDates->isNotEmpty()) {
                    $reasonParts = [];
                    if ($missingCore->isNotEmpty()) {
                        $reasonParts[] = 'ข้อมูลหลักไม่ครบ: '.$missingCore->implode(', ');
                    }
                    if ($missingDates->isNotEmpty()) {
                        $reasonParts[] = 'วันที่ไม่ครบ: '.$missingDates->implode(', ');
                    }

                    $issues[] = $this->projectDataQualityIssue(
                        'incomplete',
                        'ข้อมูลไม่ครบ / วันที่ไม่ครบ',
                        implode(' | ', $reasonParts),
                        $missingCore->merge($missingDates)->all(),
                        'เติมข้อมูลในหน้าแก้ไขโครงการให้ครบก่อนใช้รายงานหรือ dashboard',
                        3
                    );
                }

                $hasNoFunding = $this->projectHasNoFunding($project);
                if ($this->isProjectDataQualityBlank($project->funding_type)) {
                    $issues[] = $this->projectDataQualityIssue(
                        'incomplete',
                        'ข้อมูลแหล่งทุนไม่ครบ',
                        'ยังไม่ได้ระบุ DA102 - ประเภทแหล่งทุน',
                        ['DA102 - ประเภทแหล่งทุน'],
                        'เลือกประเภทแหล่งทุนเป็น ไม่มีแหล่งทุน / ภายใน / ภายนอก ให้ชัดเจน',
                        3
                    );
                } elseif (! $hasNoFunding) {
                    $missingFunding = collect([
                        'DA103 - ชื่อแหล่งทุน' => $project->funding_source,
                        'DA104 - รัฐบาล / เอกชน / มูลนิธิ' => $project->funding_org_type,
                        'DA105 - ชื่อทุน' => $project->funding_name,
                    ])->filter(fn ($value) => $this->isProjectDataQualityBlank($value))->keys()->values();

                    $budgetMissing = $project->budget === null || (float) $project->budget <= 0;
                    if ($budgetMissing) {
                        $missingFunding->push('DA106 - งบประมาณ (บาท)');
                    }

                    if ($missingFunding->isNotEmpty()) {
                        $issues[] = $this->projectDataQualityIssue(
                            'incomplete',
                            'ข้อมูลแหล่งทุนไม่ครบ',
                            'DA102 ระบุว่า '.$project->funding_type.' แต่ข้อมูลแหล่งทุนยังไม่ครบ: '.$missingFunding->implode(', '),
                            $missingFunding->all(),
                            'ถ้ามีแหล่งทุน ให้กรอก DA103 - ชื่อแหล่งทุน, DA104 - รัฐบาล / เอกชน / มูลนิธิ, DA105 - ชื่อทุน และ DA106 - งบประมาณ (บาท) ให้ครบ; ถ้าไม่มีทุนให้เลือก DA102 - ประเภทแหล่งทุน เป็น ไม่มีแหล่งทุน',
                            3
                        );
                    }
                }

                if ($startDate && $endDate && $endDate->lt($startDate)) {
                    $issues[] = $this->projectDataQualityIssue(
                        'incomplete',
                        'วันที่ผิดลำดับ',
                        'วันที่สิ้นสุดตามสัญญาน้อยกว่าวันที่เริ่มตามสัญญา',
                        ['BC102 - วันที่โครงการเริ่ม (ตามสัญญา)', 'BC103 - วันที่สิ้นสุด (ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ)'],
                        'ตรวจ BC102 และ BC103 อีกครั้ง',
                        4
                    );
                }

                if ($teamStartDate && $teamEndDate && $teamEndDate->lt($teamStartDate)) {
                    $issues[] = $this->projectDataQualityIssue(
                        'incomplete',
                        'วันที่ทีมผิดลำดับ',
                        'วันที่สิ้นสุดการทำงานของทีมวิจัยน้อยกว่าวันที่เริ่มทำงาน',
                        ['วันที่โครงการเริ่มทำงานของทีมวิจัย', 'วันที่สิ้นสุดการทำงานของทีมวิจัย'],
                        'ตรวจช่วงเวลาทำงานของทีมวิจัย',
                        3
                    );
                }

                $irbs = collect($project->irbs ?? []);
                if ((int) ($project->irbs_count ?? 0) === 0) {
                    $issues[] = $this->projectDataQualityIssue(
                        'incomplete',
                        'ไม่มีรหัส IRB',
                        'ยังไม่มีรหัส IRB ที่ผูกกับโครงการ',
                        ['รหัส IRB'],
                        'เพิ่มรหัส IRB ใน section E หากโครงการนี้มีการขอ IRB',
                        2
                    );
                } else {
                    $incompleteIrbs = $irbs->filter(fn ($irb) =>
                        $this->isProjectDataQualityBlank($irb->code ?? null)
                        || $this->isProjectDataQualityBlank($irb->source ?? null)
                        || $this->isProjectDataQualityBlank($irb->exp_date ?? null)
                    );

                    if ($incompleteIrbs->isNotEmpty()) {
                        $issues[] = $this->projectDataQualityIssue(
                            'incomplete',
                            'ข้อมูล IRB ไม่ครบ',
                            'มีรหัส IRB บางรายการที่ยังขาดรหัส หน่วยงาน หรือวันหมดอายุ',
                            ['รหัส IRB'],
                            'ตรวจรหัส IRB, หน่วยงาน และวันหมดอายุใน section E ให้ครบ',
                            2
                        );
                    }

                    if (! $this->projectLooksClosed($project)) {
                        $latestIrb = $irbs->sortByDesc(fn ($irb) => (int) ($irb->id ?? 0))->first();
                        $latestIrbDate = $latestIrb
                            ? $this->parseProjectDateInput((string) ($latestIrb->exp_date ?? ''))
                            : null;

                        if ($latestIrbDate && $latestIrbDate->lt($today)) {
                            $code = trim((string) ($latestIrb->code ?? ''));
                            $issues[] = $this->projectDataQualityIssue(
                                'overdue',
                                'IRB หมดอายุ',
                                'IRB record ล่าสุดหมดอายุแล้ว'.($code !== '' ? ': '.$code : ''),
                                ['รหัส IRB'],
                                'ตรวจ IRB record ล่าสุดด้านล่างสุดใน section E และเพิ่ม record ต่ออายุหากมีการต่อ IRB แล้ว',
                                3
                            );
                        }
                    }
                }

                if ($endDate && $endDate->lt($today) && ! $this->projectLooksClosed($project)) {
                    $issues[] = $this->projectDataQualityIssue(
                        'overdue',
                        'Overdue',
                        'สิ้นสุดตามสัญญา '.$this->formatProjectDateThai($endDate).' แล้ว แต่สถานะยังไม่ปิด/เสร็จสิ้น',
                        ['BC103 - วันที่สิ้นสุด (ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ)', 'สถานะโครงการ (Project Status)'],
                        'อัปเดตสถานะโครงการ (Project Status) หรือปรับ BC103 - วันที่สิ้นสุด (ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ)',
                        4
                    );
                }

                if ((int) ($project->outputs_count ?? 0) === 0) {
                    $issues[] = $this->projectDataQualityIssue(
                        'no_output',
                        'ไม่มี output',
                        'โครงการนี้ยังไม่มี output ที่ผูกกับโครงการ',
                        ['ผลงานวิจัย (Outputs)'],
                        'ตรวจว่ามีผลงานที่ควรเพิ่ม หรือยืนยันว่าโครงการยังไม่เกิด output',
                        3
                    );
                }

                if ((int) ($project->fte_roles_count ?? 0) === 0) {
                    $issues[] = $this->projectDataQualityIssue(
                        'other',
                        'ไม่มี FTE',
                        'ยังไม่มีรายชื่อหรือภาระงาน FTE ในโครงการ',
                        ['รายชื่อผู้มีส่วนร่วมในการทำงาน'],
                        'เพิ่มผู้รับผิดชอบ/FTE เพื่อให้ workload และ dashboard ถูกต้อง',
                        2
                    );
                } elseif (($project->fteRoles ?? collect())->contains(fn ($role) => $role->fte_value === null || (float) $role->fte_value <= 0)) {
                    $issues[] = $this->projectDataQualityIssue(
                        'other',
                        'FTE ไม่ครบ',
                        'มี FTE บางรายการที่ยังไม่ได้กรอกค่า หรือค่าเป็น 0',
                        ['FTE (ผู้วิจัย)'],
                        'ตรวจค่า FTE ของผู้เกี่ยวข้องในโครงการ',
                        2
                    );
                }

                if ((int) ($project->progress_reports_count ?? 0) === 0 && ! $this->projectLooksClosed($project)) {
                    $issues[] = $this->projectDataQualityIssue(
                        'other',
                        'ไม่มี progress report',
                        'ยังไม่มี progress report สำหรับโครงการที่ยังไม่ปิด',
                        ['ความก้าวหน้าของการดำเนินงาน'],
                        'เพิ่ม progress report หรือปิดสถานะหากโครงการเสร็จแล้ว',
                        1
                    );
                }

                $updatedAt = $project->updated_at ? Carbon::parse($project->updated_at)->setTimezone('Asia/Bangkok') : null;
                if ($updatedAt && $updatedAt->lt(now('Asia/Bangkok')->subMonths(3)) && ! $this->projectLooksClosed($project)) {
                    $issues[] = $this->projectDataQualityIssue(
                        'other',
                        'ไม่อัปเดตเกิน 3 เดือน',
                        'ข้อมูลโครงการไม่ได้อัปเดตตั้งแต่ '.$this->formatProjectDateThai($updatedAt),
                        ['อัปเดตล่าสุด'],
                        'ตรวจสถานะล่าสุด หรือเพิ่ม progress report',
                        1
                    );
                }

                if ($issues === []) {
                    return null;
                }

                $createdAt = $project->created_at ? Carbon::parse($project->created_at)->setTimezone('Asia/Bangkok') : null;
                $updatedAtForDisplay = $project->latestLog?->created_at
                    ? Carbon::parse($project->latestLog->created_at)->setTimezone('Asia/Bangkok')
                    : ($project->updated_at ? Carbon::parse($project->updated_at)->setTimezone('Asia/Bangkok') : null);
                $updatedUser = $project->latestLog?->user ?: $project->updatedBy ?: $project->createdBy;

                return (object) [
                    'project' => $project,
                    'project_code' => trim((string) ($project->project_code ?? '')) ?: '-',
                    'project_name' => trim((string) ($project->project_name_th ?? '')) ?: '-',
                    'pi_name' => trim((string) ($project->pi_name ?? '')) ?: '-',
                    'project_type' => trim(implode(' / ', array_filter([
                        trim((string) ($project->project_type ?? '')),
                        trim((string) ($project->theme ?? '')),
                    ]))) ?: '-',
                    'planning_status' => trim((string) ($project->planning_status ?? '')) ?: 'ยังไม่ได้ระบุ',
                    'outputs_count' => (int) ($project->outputs_count ?? 0),
                    'updated_at' => $project->updated_at,
                    'created_by_name' => $this->projectDataQualityUserName($project->createdBy),
                    'created_at_label' => $createdAt ? $this->formatProjectDateTimeThai($createdAt) : '-',
                    'updated_by_name' => $this->projectDataQualityUserName($updatedUser),
                    'updated_at_label' => $updatedAtForDisplay ? $this->formatProjectDateTimeThai($updatedAtForDisplay) : '-',
                    'updated_action_label' => $project->latestLog?->action_label ?? null,
                    'issues' => $issues,
                    'severity_score' => collect($issues)->max('severity') ?? 0,
                ];
            })
            ->filter()
            ->values();
    }

    private function projectDataQualityIssue(string $category, string $label, string $reason, array $fields, string $suggestion, int $severity, ?array $quickFixFields = null, ?string $impact = null): array
    {
        $fields = array_values(array_filter($fields));
        $derivedQuickFixFields = collect($fields)
            ->map(fn ($field) => $this->projectDataQualityFieldTarget((string) $field))
            ->filter(fn ($target) => $target && !str_starts_with($target, 'section-'))
            ->unique()
            ->values()
            ->all();

        return [
            'category' => $category,
            'label' => $label,
            'reason' => $reason,
            'fields' => $fields,
            'field_targets' => collect($fields)
                ->mapWithKeys(fn ($field) => [$field => $this->projectDataQualityFieldTarget((string) $field)])
                ->filter()
                ->all(),
            'quick_fix_fields' => $quickFixFields ?? $derivedQuickFixFields,
            'suggestion' => $suggestion,
            'impact' => $impact ?: $this->projectDataQualityImpact($category, $label, $fields),
            'severity' => $severity,
        ];
    }

    private function projectDataQualityImpact(string $category, string $label, array $fields): string
    {
        $fieldText = mb_strtolower(implode(' ', $fields), 'UTF-8');

        if ($label === 'ข้อมูลซ้ำ') {
            return 'Dashboard และรายงานสรุปจำนวนโครงการอาจนับซ้ำ โดยเฉพาะ KPI จำนวนโครงการ, กราฟสัดส่วนสถานะโครงการ และรายงาน Excel/PDF ที่ export จากระบบ';
        }

        if ($label === 'ข้อมูลแหล่งทุนไม่ครบ' || str_contains($fieldText, 'da102') || str_contains($fieldText, 'da106') || str_contains($fieldText, 'ทุน')) {
            return 'กราฟ Dashboard ชื่อ Funding Stack, งบประมาณรวม, Funding Split และกราฟงบประมาณรายปีจะคลาดเคลื่อน เพราะระบบจัดกลุ่มทุนหรือรวมงบประมาณไม่ได้ถูกต้อง';
        }

        if ($label === 'Overdue') {
            return 'Dashboard จะจัดโครงการนี้เป็นงานเกินกำหนด ทำให้ Alert Center, กราฟสถานะพอร์ต, กราฟโครงการเกินกำหนด และสรุปเปิดประชุมแสดงความเสี่ยงสูงกว่าความจริง';
        }

        if ($label === 'ไม่มี output') {
            return 'Dashboard ส่วนภาพรวมผลงาน, Output coverage, รายการโครงการ active แต่ยังไม่มี output และรายงาน Outputs จะนับผลงานของโครงการนี้ไม่ครบ';
        }

        if ($label === 'ไม่มี FTE' || $label === 'FTE เป็น 0') {
            return 'Dashboard ส่วน Capacity + Funding, FTE by Role Group, แผนที่โครงการและภาระงาน และรายงาน FTE จะไม่สะท้อน workload จริงของทีม';
        }

        if ($label === 'ไม่มี progress report' || $label === 'ไม่อัปเดตเกิน 3 เดือน') {
            return 'Dashboard ส่วน Alert Center, โครงการที่ควรหยิบมาดูก่อน และสรุปความคืบหน้าจะประเมินสถานะติดตามงานคลาดเคลื่อน';
        }

        if ($label === 'ไม่มีรหัส IRB' || $label === 'ข้อมูล IRB ไม่ครบ' || $label === 'IRB หมดอายุ') {
            return 'Dashboard ส่วน IRB หมดอายุแล้ว, กราฟ IRB ใกล้หมดอายุ และ Alert Center จะติดตามความเสี่ยงด้าน IRB ไม่ถูกต้อง';
        }

        if (str_contains($fieldText, 'สถานะโครงการ')) {
            return 'Dashboard ชื่อสัดส่วนสถานะโครงการ, สถานะพอร์ต และกราฟสถานะรายปีจะจัดกลุ่มโครงการผิด';
        }

        if (str_contains($fieldText, 'วันที่') || str_contains($fieldText, 'bc') || str_contains($fieldText, 'ba')) {
            return 'Dashboard ที่ใช้ timeline เช่น การเคลื่อนไหวรายเดือน, โครงการใกล้ครบกำหนด, โครงการเกินกำหนด และกราฟรายปีจะวางช่วงเวลาไม่ถูกต้อง';
        }

        return 'Dashboard, รายงาน Excel/PDF และตัวกรองที่อ้างอิงข้อมูลนี้อาจแสดงผลไม่ตรงกับสถานะจริงของโครงการ';
    }

    private function projectDataQualityDuplicateMatches(Collection $projects, array $duplicateProjectIds): array
    {
        $duplicateLookup = array_fill_keys(array_map('intval', $duplicateProjectIds), true);
        if ($duplicateLookup === []) {
            return [];
        }

        $prepared = [];
        $exactGroups = [];
        foreach ($projects as $project) {
            if (!isset($duplicateLookup[(int) $project->id])) {
                continue;
            }

            $rawName = trim((string) $project->project_name_th);
            $normalized = $this->normalizeSearchText($rawName);
            if ($normalized === '') {
                continue;
            }

            $years = $this->collectComparableProjectYears($project->start_year, $rawName);
            $primaryYear = $years[0] ?? '__unknown__';
            $item = [
                'id' => (int) $project->id,
                'project_code' => trim((string) ($project->project_code ?? '')),
                'project_name' => $rawName,
                'normalized' => $normalized,
                'tokens' => $this->tokenizeSearchTerms($rawName),
                'years' => $years,
                'start_year' => $this->normalizeComparableYearValue($project->start_year),
            ];

            $prepared[] = $item;
            $exactGroups[$normalized.'|'.$primaryYear][] = $item;
        }

        $matches = [];
        $addMatch = static function (array $left, array $right) use (&$matches): void {
            $matches[$left['id']][$right['id']] = [
                'id' => $right['id'],
                'project_code' => $right['project_code'],
                'project_name' => $right['project_name'],
                'start_year' => $right['start_year'],
            ];
            $matches[$right['id']][$left['id']] = [
                'id' => $left['id'],
                'project_code' => $left['project_code'],
                'project_name' => $left['project_name'],
                'start_year' => $left['start_year'],
            ];
        };

        foreach ($exactGroups as $group) {
            $count = count($group);
            if ($count < 2) {
                continue;
            }

            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $addMatch($group[$i], $group[$j]);
                }
            }
        }

        $count = count($prepared);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if (($prepared[$i]['normalized'] ?? '') === ($prepared[$j]['normalized'] ?? '')) {
                    continue;
                }

                if ($this->areProjectNamesNearDuplicates($prepared[$i], $prepared[$j])) {
                    $addMatch($prepared[$i], $prepared[$j]);
                }
            }
        }

        return array_map(static fn ($items) => array_values($items), $matches);
    }

    private function projectDataQualityFieldTarget(string $field): ?string
    {
        $normalized = mb_strtolower(trim($field), 'UTF-8');
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?: $normalized;

        $map = [
            'project id' => 'section-a',
            'ชื่อโครงการ (ภาษาไทย)' => 'project_name_th',
            'pi' => 'pi_name',
            'กลุ่มเนื้อหาหลักของงาน (content/theme)' => 'project_type',
            'ลักษณะโครงการ' => 'project_list',
            'ประเภทของโครงการ/กิจกรรม (type)' => 'theme',
            'เอกสารโครงการ (proposal / irb)' => 'shortcut_link',
            'ขนาด' => 'size',
            'รหัสโครงการ r' => 'r_code',
            'ปีโครงการ' => 'start_year',
            'สถานะโครงการ (project status)' => 'planning_status',
            'วันที่โครงการเริ่มทำงานของทีมวิจัย' => 'team_start_date',
            'วันที่สิ้นสุดการทำงานของทีมวิจัย' => 'team_end_date',
            'ba001 - วันที่เริ่มเขียน proposal' => 'proposal_date',
            'bc101 - วันที่สัญญาอนุมัติ' => 'contract_date',
            'bc102 - วันที่โครงการเริ่ม (ตามสัญญา)' => 'start_date',
            'bc103 - วันที่สิ้นสุด (ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ)' => 'end_date',
            'da102 - ประเภทแหล่งทุน' => 'funding_type',
            'da103 - ชื่อแหล่งทุน' => 'funding_source',
            'da104 - รัฐบาล / เอกชน / มูลนิธิ' => 'funding_org_type',
            'da105 - ชื่อทุน' => 'funding_name',
            'da106 - งบประมาณ (บาท)' => 'budget',
            'รหัส irb' => 'section-e',
            'ผลงานวิจัย (outputs)' => 'section-g',
            'รายชื่อผู้มีส่วนร่วมในการทำงาน' => 'section-f',
            'fte (ผู้วิจัย)' => 'section-f',
            'ความก้าวหน้าของการดำเนินงาน' => 'section-i',
            'อัปเดตล่าสุด' => 'section-b',
        ];

        return $map[$normalized] ?? null;
    }

    private function projectDataQualityQuickFixFields(): array
    {
        return [
            'project_name_th' => ['label' => 'ชื่อโครงการ (ภาษาไทย)', 'type' => 'text', 'rule' => 'nullable|string|max:5000'],
            'project_name_en' => ['label' => 'ชื่อโครงการ (ภาษาอังกฤษ)', 'type' => 'text', 'rule' => 'nullable|string|max:5000'],
            'project_type' => [
                'label' => 'กลุ่มเนื้อหาหลักของงาน (Content/Theme)',
                'type' => 'select',
                'rule' => 'nullable|string|max:255',
                'options' => [
                    '01 Aging and Frailty',
                    '02 Health Systems and HTA',
                    '03 Rational Drug Use (RDU)',
                    '04 Real-World Evidence (RWE) and Clinical–Technical Cohorts',
                    '05 Global Health',
                    '06 Other Areas',
                ],
            ],
            'theme' => [
                'label' => 'ประเภทของโครงการ/กิจกรรม (TYPE)',
                'type' => 'select',
                'rule' => 'nullable|string|max:255',
                'options' => [
                    'T101_Research_วิจัยที่ Health Policy เป็น ผู้วิจัยหลัก',
                    'T102_Research: โครงการวิจัยที่ Team Health Policy เป็น ผู้ร่วมวิจัย',
                    'T103_Research: โครงการวิจัยที่เป็นไปในลักษณะ Individual Expertise contribution (Skill รายบุคคล) เช่น การเข้าร่วมเขียน paper',
                    'T301_Model of Care development (Clinic)',
                    'T401_Cohort Study',
                    'T501_Curriculum Development (Education)',
                    'T601_Consult',
                    'T701_Process Improvement (Siriraj Health Policy)',
                    'T801_Conference Session / Knowledge Sharing',
                    'T901_Capacity Building / Skill Development',
                ],
            ],
            'project_list' => [
                'label' => 'ลักษณะโครงการ',
                'type' => 'select',
                'rule' => 'nullable|string|max:255',
                'options' => [
                    'โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ',
                    'โครงการนอกหน่วย แต่ยังอยู่ในคณะฯ',
                    'โครงการวิจัยนอกคณะ',
                ],
            ],
            'shortcut_link' => ['label' => 'เอกสารโครงการ (Proposal / IRB)', 'type' => 'text', 'rule' => 'nullable|string'],
            'size' => [
                'label' => 'ขนาดโครงการ (S/M/L)',
                'type' => 'select',
                'rule' => 'nullable|string|max:10',
                'options' => ['S', 'M', 'L', 'XL', 'FTE Hours'],
            ],
            'r_code' => ['label' => 'รหัสโครงการ R', 'type' => 'text', 'rule' => 'nullable|string'],
            'start_year' => [
                'label' => 'ปีโครงการ',
                'type' => 'year',
                'rule' => 'nullable|string',
            ],
            'planning_status' => [
                'label' => 'สถานะโครงการ (Project Status)',
                'type' => 'select',
                'rule' => 'nullable|string',
                'options' => [
                    'Project Planing (เริ่มต้น - submit IRB)',
                    'เก็บข้อมูล',
                    'วิเคราะห์ข้อมูล',
                    'เขียนรายงานและpublication',
                    'ปิดโปรเจค',
                    'ยกเลิกโปรเจค',
                ],
            ],
            'team_start_date' => ['label' => 'วันที่โครงการเริ่มทำงานของทีมวิจัย', 'type' => 'date', 'rule' => 'nullable|string'],
            'team_end_date' => ['label' => 'วันที่สิ้นสุดการทำงานของทีมวิจัย', 'type' => 'date', 'rule' => 'nullable|string'],
            'proposal_date' => ['label' => 'BA001 - วันที่เริ่มเขียน Proposal', 'type' => 'date', 'rule' => 'nullable|string'],
            'contract_date' => ['label' => 'BC101 - วันที่สัญญาอนุมัติ', 'type' => 'date', 'rule' => 'nullable|string'],
            'start_date' => ['label' => 'BC102 - วันที่โครงการเริ่ม (ตามสัญญา)', 'type' => 'date', 'rule' => 'nullable|string'],
            'end_date' => ['label' => 'BC103 - วันที่สิ้นสุด (ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ)', 'type' => 'date', 'rule' => 'nullable|string'],
            'funding_type' => [
                'label' => 'DA102 - ประเภทแหล่งทุน',
                'type' => 'select',
                'rule' => 'nullable|string',
                'options' => ['ไม่มีแหล่งทุน', 'ภายใน', 'ภายนอก'],
            ],
            'funding_source' => ['label' => 'DA103 - ชื่อแหล่งทุน', 'type' => 'text', 'rule' => 'nullable|string'],
            'funding_org_type' => [
                'label' => 'DA104 - รัฐบาล / เอกชน / มูลนิธิ',
                'type' => 'select',
                'rule' => 'nullable|string',
                'options' => ['รัฐบาล', 'เอกชน', 'มูลนิธิ'],
            ],
            'funding_name' => ['label' => 'DA105 - ชื่อทุน', 'type' => 'text', 'rule' => 'nullable|string'],
            'budget' => ['label' => 'DA106 - งบประมาณ (บาท)', 'type' => 'number', 'rule' => 'nullable|numeric|min:0'],
            'pi_name' => ['label' => 'CA102 - ชื่อ PI (ผู้วิจัยหลัก)', 'type' => 'text', 'rule' => 'nullable|string'],
            'pi_affiliation' => ['label' => 'CA103 - สังกัดผู้วิจัยหลัก', 'type' => 'text', 'rule' => 'nullable|string'],
            'pi_user_id' => ['label' => 'CA102 - เลือก PI จากรายชื่อบุคลากร', 'type' => 'user', 'rule' => 'nullable|integer|exists:users,id'],
        ];
    }

    private function isProjectDataQualityBlank($value): bool
    {
        $text = trim((string) ($value ?? ''));
        $normalized = mb_strtolower($text, 'UTF-8');

        return $text === ''
            || in_array($normalized, ['-', '–', '—', 'n/a', 'na', 'null', 'ไม่มี', 'ไม่ระบุ'], true);
    }

    private function projectHasNoFunding(Project $project): bool
    {
        $fundingType = mb_strtolower(trim((string) ($project->funding_type ?? '')), 'UTF-8');

        return $fundingType !== ''
            && (str_contains($fundingType, 'ไม่มีแหล่งทุน')
                || str_contains($fundingType, 'ไม่มีทุน')
                || $fundingType === 'no funding');
    }

    private function summarizeProjectDataQualityRows(Collection $rows): array
    {
        $issues = $rows->flatMap(fn ($row) => $row->issues);

        return [
            'projects' => $rows->count(),
            'issues' => $issues->count(),
            'by_category' => collect($this->projectDataQualityCategories())
                ->mapWithKeys(fn ($label, $key) => [$key => [
                    'label' => $label,
                    'projects' => $rows->filter(fn ($row) => collect($row->issues)->contains(fn ($issue) => $issue['category'] === $key))->count(),
                    'issues' => $issues->where('category', $key)->count(),
                ]])
                ->all(),
        ];
    }

    private function projectLooksClosed(Project $project): bool
    {
        $text = mb_strtolower(implode(' ', array_filter([
            $project->planning_status,
            $project->execution_status,
            $project->closure_status,
        ])), 'UTF-8');

        return str_contains($text, 'completed')
            || str_contains($text, 'complete')
            || str_contains($text, 'closed')
            || str_contains($text, 'เสร็จ')
            || str_contains($text, 'ปิด')
            || str_contains($text, 'สิ้นสุด');
    }

    private function formatProjectDateThai(Carbon $date): string
    {
        $date = $date->copy()->locale('th')->setTimezone('Asia/Bangkok');

        return $date->translatedFormat('j F').' '.($date->year + 543);
    }

    private function formatProjectDateTimeThai(Carbon $date): string
    {
        $date = $date->copy()->locale('th')->setTimezone('Asia/Bangkok');

        return $date->translatedFormat('j F').' '.($date->year + 543).' '.$date->format('H:i').' น.';
    }

    private function projectDataQualityUserName($user): string
    {
        $name = trim((string) ($user?->full_name ?? ''));

        return $name !== '' ? $name : 'ไม่ทราบผู้ใช้';
    }

    private function projectDataQualityExportMinDate(Collection $rows): ?string
    {
        $min = $rows
            ->pluck('updated_at')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->setTimezone('Asia/Bangkok'))
            ->sortBy(fn (Carbon $date) => $date->timestamp)
            ->first();

        return $min ? $min->format('Y-m-d') : null;
    }

    private function paginateProjectDataQualityRows(Collection $rows, Request $request, int $perPage)
    {
        $page = max(1, (int) $request->input('page', 1));
        $focusProjectId = (int) $request->input('focus_project', 0);
        if ($focusProjectId > 0) {
            $focusIndex = $rows->search(fn ($row) => (int) $row->project->getKey() === $focusProjectId);
            if ($focusIndex !== false) {
                $page = (int) floor($focusIndex / $perPage) + 1;
            }
        }

        $items = $rows->forPage($page, $perPage)->values();
        $query = $request->query();
        unset($query['focus_project']);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $query,
            ]
        );
    }

    private function filterProjectDataQualityRowsByExportDateRange(Collection $rows, Request $request): Collection
    {
        if ($request->input('export_scope') !== 'range') {
            return $rows->values();
        }

        $start = $this->parseProjectDateInput($request->input('export_start_date'));
        $end = $this->parseProjectDateInput($request->input('export_end_date'));

        if ($start) {
            $rows = $rows->filter(fn ($row) => $row->updated_at && Carbon::parse($row->updated_at)->setTimezone('Asia/Bangkok')->gte($start->copy()->startOfDay()));
        }

        if ($end) {
            $rows = $rows->filter(fn ($row) => $row->updated_at && Carbon::parse($row->updated_at)->setTimezone('Asia/Bangkok')->lte($end->copy()->endOfDay()));
        }

        return $rows->values();
    }

    private function buildProjectDataQualityExportFilters(Request $request): Collection
    {
        $categoryLabels = $this->projectDataQualityCategories();
        $severityLabels = ['4' => 'สูง', '3' => 'กลาง', '2' => 'ควรตรวจ', '1' => 'ติดตาม'];
        $outputLabels = ['none' => 'ไม่มี output', 'has' => 'มี output'];
        $activityLabels = [
            'updated_30' => 'อัปเดทใน 30 วัน',
            'updated_90' => 'อัปเดทใน 90 วัน',
            'stale_90' => 'ไม่อัปเดทเกิน 90 วัน',
        ];

        return collect([
            $request->filled('search') ? 'ค้นหา: '.trim((string) $request->input('search')) : null,
            $request->filled('category') ? 'หมวดปัญหา: '.($categoryLabels[$request->input('category')] ?? $request->input('category')) : null,
            $request->filled('issue_label') ? 'ชนิดปัญหา: '.trim((string) $request->input('issue_label')) : null,
            $request->filled('severity') ? 'ระดับ: '.($severityLabels[(string) $request->input('severity')] ?? $request->input('severity')) : null,
            $request->filled('output_status') ? 'Output: '.($outputLabels[$request->input('output_status')] ?? $request->input('output_status')) : null,
            $request->filled('activity') ? 'ความเคลื่อนไหว: '.($activityLabels[$request->input('activity')] ?? $request->input('activity')) : null,
            $request->filled('sort') ? 'เรียงลำดับ: '.trim((string) $request->input('sort')) : null,
            $this->projectDataQualityExportDateRangeLabel($request),
        ])->filter()->values();
    }

    private function projectDataQualityExportDateRangeLabel(Request $request): ?string
    {
        if ($request->input('export_scope') !== 'range') {
            return null;
        }

        $format = fn (?Carbon $date) => $date ? $date->format('d/m/').($date->year + 543) : null;
        $start = $format($this->parseProjectDateInput($request->input('export_start_date')));
        $end = $format($this->parseProjectDateInput($request->input('export_end_date')));

        return 'วันที่อัปเดท record: '.($start ?: 'เริ่มต้น').' - '.($end ?: 'ปัจจุบัน');
    }

    private function buildProjectDataQualityExportFilename(string $prefix, string $extension): string
    {
        return strtoupper($prefix).'_'.now('Asia/Bangkok')->format('Ymd_Hi').'.'.ltrim($extension, '.');
    }

    private function projectDataQualityExcelRows(Collection $rows): Collection
    {
        $severityLabel = fn ($score) => match ((int) $score) {
            4 => 'สูง',
            3 => 'กลาง',
            2 => 'ควรตรวจ',
            default => 'ติดตาม',
        };

        return $rows->map(function ($row, $index) use ($severityLabel) {
            $issues = collect($row->issues);
            $fieldTargets = $issues
                ->flatMap(fn ($issue) => collect($issue['field_targets'] ?? [])->values())
                ->filter()
                ->unique()
                ->values();
            $quickFixFields = $issues
                ->flatMap(fn ($issue) => $issue['quick_fix_fields'] ?? [])
                ->filter()
                ->unique()
                ->values();
            $duplicateMatches = $issues
                ->flatMap(fn ($issue) => collect($issue['duplicate_matches'] ?? []))
                ->map(fn ($match) => trim(implode(' | ', array_filter([
                    $match['project_code'] ?? null,
                    $match['project_name'] ?? null,
                    isset($match['start_year']) && $match['start_year'] !== '' ? 'ปี '.$match['start_year'] : null,
                ]))))
                ->filter()
                ->unique()
                ->values();
            $editUrl = route('projects.edit', array_filter([
                'project' => $row->project,
                'view' => 'desktop',
                'dq_fields' => $fieldTargets->isNotEmpty() ? $fieldTargets->implode(',') : null,
            ], fn ($value) => $value !== null));
            $firstTarget = $fieldTargets->first();
            if ($firstTarget) {
                $editUrl .= '#' . (str_starts_with((string) $firstTarget, 'section-') ? rawurlencode($firstTarget) : 'field-' . rawurlencode($firstTarget));
            }

            return [
                (int) $index + 1,
                $row->project_code,
                $row->project_name,
                $row->pi_name,
                $row->planning_status,
                $row->project_type,
                $row->outputs_count,
                $severityLabel($row->severity_score),
                $issues->count(),
                $issues->pluck('label')->unique()->implode(' | '),
                $issues->pluck('reason')->implode("\n"),
                $issues->pluck('fields')->flatten()->unique()->implode(', '),
                $issues->pluck('suggestion')->unique()->implode("\n"),
                $issues->pluck('impact')->filter()->unique()->implode("\n"),
                $row->created_by_name,
                $row->created_at_label,
                $row->updated_by_name,
                $row->updated_at_label,
                route('projects.show', $row->project),
                $editUrl,
                $quickFixFields->implode(', '),
                $fieldTargets->implode(', '),
                $duplicateMatches->implode("\n"),
            ];
        })->values();
    }

    private function projectDataQualityXlsxResponse(string $filename, string $sheetName, array $columns, Collection $rows, array $metaRows = [])
    {
        $content = $this->buildProjectDataQualityXlsxWorkbook($sheetName, $columns, $rows, $metaRows);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => (string) strlen($content),
        ]);
    }

    private function buildProjectDataQualityXlsxWorkbook(string $sheetName, array $headers, Collection $rows, array $metaRows = []): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'project_data_quality_xlsx_');
        $zip = new \ZipArchive();
        $zip->open($tmpPath, \ZipArchive::OVERWRITE);

        $dataSheetName = mb_substr($sheetName, 0, 31);

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="list" sheetId="1" r:id="rId1"/><sheet name="'.$this->projectDataQualityXlsxEscapeAttribute($dataSheetName).'" sheetId="2" r:id="rId2"/></sheets><calcPr calcMode="auto" fullCalcOnLoad="1"/></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFEAF2FF"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom><diagonal/></border></borders><cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment wrapText="1" vertical="top"/></xf></cellXfs></styleSheet>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildProjectDataQualityXlsxListWorksheet($rows, $metaRows, $dataSheetName));
        $zip->addFromString('xl/worksheets/sheet2.xml', $this->buildProjectDataQualityXlsxWorksheet($headers, $rows));
        $zip->close();

        $content = (string) file_get_contents($tmpPath);
        @unlink($tmpPath);

        return $content;
    }

    private function buildProjectDataQualityXlsxListWorksheet(Collection $rows, array $metaRows, string $detailSheetName): string
    {
        $rowIndex = 1;
        $xmlRows = [];
        foreach ($metaRows as $metaRow) {
            $xmlRows[] = $this->projectDataQualityXlsxRow($rowIndex++, collect($metaRow)->values()->all(), 2);
        }
        if (!empty($metaRows)) {
            $xmlRows[] = '<row r="'.$rowIndex++.'"/>';
        }

        $xmlRows[] = $this->projectDataQualityXlsxRow($rowIndex++, ['สารบัญรายการ: คลิก "เปิดรายละเอียด" เพื่อไปยังแถวรายละเอียดของ record นั้น'], 1);
        $indexHeaderRowIndex = $rowIndex;
        $xmlRows[] = $this->projectDataQualityXlsxRow($rowIndex++, ['#', 'Project ID', 'ชื่อโครงการ', 'ระดับ', 'Issues', 'Output', 'เปิดรายละเอียด'], 1);
        $detailStartRowIndex = 2;

        foreach ($rows->values() as $index => $row) {
            $rowValues = array_values((array) $row);
            $detailRowIndex = $detailStartRowIndex + $index;
            $xmlRows[] = $this->projectDataQualityXlsxRow($rowIndex++, [
                (int) $index + 1,
                $rowValues[1] ?? '',
                $rowValues[2] ?? '',
                $rowValues[7] ?? '',
                $rowValues[8] ?? '',
                $rowValues[6] ?? '',
                $this->projectDataQualityXlsxFormula('HYPERLINK("#'.$this->projectDataQualityXlsxSheetCellReference($detailSheetName, 'A'.$detailRowIndex).'","เปิดรายละเอียด")'),
            ], 2);
        }

        $lastRow = max($indexHeaderRowIndex, $rowIndex - 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheetViews><sheetView workbookViewId="0"><pane ySplit="'.$indexHeaderRowIndex.'" topLeftCell="A'.($indexHeaderRowIndex + 1).'" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols><col min="1" max="1" width="8" customWidth="1"/><col min="2" max="2" width="22" customWidth="1"/><col min="3" max="3" width="60" customWidth="1"/><col min="4" max="4" width="16" customWidth="1"/><col min="5" max="5" width="12" customWidth="1"/><col min="6" max="6" width="12" customWidth="1"/><col min="7" max="7" width="18" customWidth="1"/></cols><sheetData>'.implode('', $xmlRows).'</sheetData><autoFilter ref="A'.$indexHeaderRowIndex.':G'.$lastRow.'"/></worksheet>';
    }

    private function buildProjectDataQualityXlsxWorksheet(array $headers, Collection $rows): string
    {
        $rowIndex = 1;
        $xmlRows = [];
        $headerRowIndex = $rowIndex;
        $xmlRows[] = $this->projectDataQualityXlsxRow($rowIndex++, $headers, 1);
        foreach ($rows as $row) {
            $xmlRows[] = $this->projectDataQualityXlsxRow($rowIndex++, array_values((array) $row), 2);
        }
        $columnCount = max(count($headers), 1);
        $lastColumn = $this->projectDataQualityXlsxColumnName($columnCount);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols>'.collect(range(1, $columnCount))->map(fn ($i) => '<col min="'.$i.'" max="'.$i.'" width="'.($i === 1 ? 8 : ($i >= 10 ? 42 : 24)).'" customWidth="1"/>')->implode('').'</cols><sheetData>'.implode('', $xmlRows).'</sheetData><autoFilter ref="A'.$headerRowIndex.':'.$lastColumn.max($headerRowIndex, $rowIndex - 1).'"/></worksheet>';
    }

    private function projectDataQualityXlsxRow(int $rowIndex, array $values, int $style = 0): string
    {
        $cells = [];
        foreach (array_values($values) as $columnIndex => $value) {
            $cell = $this->projectDataQualityXlsxColumnName($columnIndex + 1).$rowIndex;
            $styleAttribute = $style > 0 ? ' s="'.$style.'"' : '';
            if (is_array($value) && array_key_exists('formula', $value)) {
                $formula = ltrim((string) ($value['formula'] ?? ''), '=');
                $cells[] = '<c r="'.$cell.'"'.$styleAttribute.'><f>'.$this->projectDataQualityXlsxEscapeFormula($formula).'</f><v></v></c>';
                continue;
            }

            $cells[] = '<c r="'.$cell.'" t="inlineStr"'.$styleAttribute.'><is><t xml:space="preserve">'.$this->projectDataQualityXlsxEscapeText($value).'</t></is></c>';
        }

        return '<row r="'.$rowIndex.'">'.implode('', $cells).'</row>';
    }

    private function projectDataQualityXlsxFormula(string $formula): array
    {
        return ['formula' => $formula];
    }

    private function projectDataQualityXlsxSheetCellReference(string $sheetName, string $cell): string
    {
        return "'".str_replace("'", "''", $sheetName)."'!".$cell;
    }

    private function projectDataQualityXlsxColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)).$name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function projectDataQualityXlsxEscapeText($value): string
    {
        $text = trim((string) ($value ?? ''));
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $text) ?? '';

        return htmlspecialchars($text, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function projectDataQualityXlsxEscapeFormula(string $formula): string
    {
        return htmlspecialchars($formula, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function projectDataQualityXlsxEscapeAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function normalizePersonName(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');
        return preg_replace('/\s+/u', '', $lower) ?: $lower;
    }

    private function applyPersonRoleFilter(Builder $query, string $personName): void
    {
        $variants = $this->buildPersonNameVariants($personName);
        if ($variants === []) {
            return;
        }

        $query->where(function (Builder $inner) use ($variants) {
            $this->applyNormalizedNameMatchAny($inner, 'pi_name', $variants);

            $inner->orWhereHas('copis', function (Builder $q) use ($variants) {
                $this->applyNormalizedNameMatchAny($q, 'name', $variants);
                })
                ->orWhereHas('coResearches', function (Builder $q) use ($variants) {
                    $this->applyNormalizedNameMatchAny($q, 'name', $variants);
                })
                ->orWhereHas('fteRoles', function (Builder $q) use ($variants) {
                    $this->applyNormalizedNameMatchAny($q, 'name', $variants);
                });
        });
    }

    private function applyNormalizedNameMatchAny(Builder $query, string $column, array $variants): void
    {
        if ($variants === []) {
            return;
        }

        $placeholders = implode(', ', array_fill(0, count($variants), '?'));
        $query->whereRaw(
            "REPLACE(LOWER(TRIM(COALESCE({$column}, ''))), ' ', '') IN ({$placeholders})",
            $variants
        );
    }

    private function buildPersonNameVariants(string $personName): array
    {
        $raw = trim($personName);
        if ($raw === '') {
            return [];
        }

        $variants = [ $this->normalizePersonName($raw) ];
        $parts = preg_split('/\s+/u', $raw) ?: [];
        if (count($parts) > 1) {
            $withoutPrefix = trim(implode(' ', array_slice($parts, 1)));
            if ($withoutPrefix !== '') {
                $variants[] = $this->normalizePersonName($withoutPrefix);
            }
        }

        return collect($variants)->filter()->unique()->values()->all();
    }

    private function buildPersonFilterOptions(Builder $baseQuery): array
    {
        return \App\Models\User::query()
            ->selectRaw("TRIM(COALESCE(prefix, '')) as person_prefix")
            ->selectRaw("TRIM(name) as person_name")
            ->whereNotNull('name')
            ->whereRaw("TRIM(name) <> ''")
            ->groupBy('person_prefix', 'person_name')
            ->orderBy('person_prefix')
            ->orderBy('person_name')
            ->get()
            ->map(function ($row) {
                $name = trim((string) $row->person_name);
                $prefix = trim((string) ($row->person_prefix ?? ''));
                if ($name === '') {
                    return null;
                }
                $fullName = trim($prefix.' '.$name);
                return [
                    'value' => $fullName,
                    'label' => $fullName,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function buildPersonRoleSummary(string $personName, $projects): array
    {
        $variants = $this->buildPersonNameVariants($personName);
        if ($variants === []) {
            return [];
        }

        $matchesAny = fn ($value) => in_array($this->normalizePersonName((string) $value), $variants, true);
        $rows = collect($projects);

        $countRole = function (callable $predicate) use ($rows) {
            return $rows->filter($predicate)->count();
        };

        $summary = [];
        $piCount = $countRole(fn ($p) => $matchesAny($p->pi_name ?? ''));
        if ($piCount > 0) $summary[] = ['role' => 'PI', 'count' => $piCount];
        $copiCount = $countRole(fn ($p) => collect($p->copis ?? [])->contains(fn ($r) => $matchesAny($r->name ?? '')));
        if ($copiCount > 0) $summary[] = ['role' => 'Co-PI', 'count' => $copiCount];
        $coCount = $countRole(fn ($p) => collect($p->coResearches ?? [])->contains(fn ($r) => $matchesAny($r->name ?? '')));
        if ($coCount > 0) $summary[] = ['role' => 'Co-Research', 'count' => $coCount];
        $fteCount = $countRole(fn ($p) => collect($p->fteRoles ?? [])->contains(fn ($r) => $matchesAny($r->name ?? '')));
        if ($fteCount > 0) $summary[] = ['role' => 'FTE', 'count' => $fteCount];

        return $summary;
    }

    public function create(Request $request)
    {
        $view = ResponsiveView::resolve($request, 'projects.create');

        return view($view);
    }

public function store(Request $request)
{
    $this->hydratePackedFteRequest($request);
    $validated = $this->validateProject($request);
    $project = null;

    // ✅ กรอง IRB ที่ไม่มี code ออกก่อน
    $irbs = collect($request->input('irbs', []))
        ->filter(fn ($irb) => !empty($irb['code']))
        ->values()
        ->toArray();

    $request->merge(['irbs' => $irbs]);

    // ✅ validate เฉพาะ IRB ที่มี code จริง
    $request->validate([
        'irbs.*.exp_date' => 'required',
    ]);

    DB::transaction(function () use ($validated, $request, &$project) {
        $validated['project_code'] = $this->nextProjectCode();
        $validated['user_id'] = Auth::id();

        $project = new Project($validated);
        $this->fillStrategies($request, $project);

        $project->updated_by = Auth::id();
        $project->save();

        // sync relations
        $this->validateOutputs($request);
        $this->syncFTERoles($request, $project);
        $this->syncOutputs($request, $project);
        $this->syncCopis($request, $project);
        $this->syncIrbs($request, $project);
        $this->syncCoResearch($request, $project);
    });

    return redirect()
        ->route('projects.show', $project)
        ->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว')
        ->with('clear_project_create_autosave', true);
}

    private function nextProjectCode(): string
    {
        if (Schema::hasTable('project_sequences')) {
            $sequence = DB::table('project_sequences')
                ->where('prefix', 'SP')
                ->lockForUpdate()
                ->first();

            if ($sequence) {
                $nextNumber = (int) $sequence->current_number + 1;

                DB::table('project_sequences')
                    ->where('id', $sequence->id)
                    ->update([
                        'current_number' => $nextNumber,
                        'updated_at' => now(),
                    ]);

                return 'SP_'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            }
        }

        // fallback: ใช้รหัสล่าสุดจาก projects หากยังไม่มี sequence table/row
        $lastCode = Project::query()
            ->where('project_code', 'like', 'SP_%')
            ->orderByRaw("CAST(SUBSTRING(project_code, 4) AS UNSIGNED) DESC")
            ->value('project_code');

        $nextNumber = 1;
        if (is_string($lastCode) && preg_match('/^SP_(\d+)$/', $lastCode, $m)) {
            $nextNumber = ((int) $m[1]) + 1;
        }

        return 'SP_'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function edit(Request $request, Project $project)
    {
        $routeProjectParam = trim((string) ($request->route()?->originalParameter('project') ?? ''));
        $canonicalProjectKey = (string) $project->getRouteKey();

        $fullName = trim(Auth::user()->prefix.' '.Auth::user()->name);
        $hasPermission = Auth::user()->isAdminLike()
            || Auth::id() === $project->user_id
            || $project->fteRoles->contains('name', $fullName);

        if (! $hasPermission) {
            abort(403);
        }

        if ($routeProjectParam !== '' && $canonicalProjectKey !== '' && $routeProjectParam !== $canonicalProjectKey) {
            return redirect()->route('projects.edit', array_merge(['project' => $project], $request->query()));
        }

        $project->load([
            'outputs.authors',
            'outputs.awardRecipients',
            'outputs.bookAuthors',
            'outputs.conferences.presenters',
            'progressReports' => function ($query) {
                $query->orderByDesc('submit_date')->orderByDesc('id');
            },
        ]);

        $view = ResponsiveView::resolve($request, 'projects.edit');

        return view($view, compact('project'));
    }

    public function fteSnapshot(Project $project)
    {
        $fullName = trim(Auth::user()->prefix.' '.Auth::user()->name);
        $hasPermission = Auth::user()->isAdminEquivalent()
            || Auth::id() === $project->user_id
            || $project->fteRoles->contains('name', $fullName);

        if (! $hasPermission) {
            abort(403);
        }

        $project->loadMissing('updatedBy:id,prefix,name');
        $activityLogs = $this->fetchFteActivityLogs($project);
        $projectUpdatedByName = trim(((string) optional($project->updatedBy)->prefix).' '.((string) optional($project->updatedBy)->name));

        $rows = $project->fteRoles()
            ->orderBy('id')
            ->get()
            ->map(function ($fte) use ($activityLogs, $projectUpdatedByName) {
                $activityMeta = $this->resolveFteActivityMeta(optional($fte->updated_at), $activityLogs);
                $actorName = $activityMeta['actor_name'] ?? ($projectUpdatedByName !== '' ? $projectUpdatedByName : null);

                return [
                    'id' => $fte->id,
                    'name' => trim((string) $fte->name),
                    'role_group' => trim((string) $fte->role_group),
                    'job_detail' => trim((string) $fte->job_detail),
                    'task_detail' => trim((string) $fte->task_detail),
                    'start_date' => trim((string) $fte->start_date),
                    'end_date' => trim((string) $fte->end_date),
                    'hours' => $fte->hours === null ? '' : rtrim(rtrim(number_format((float) $fte->hours, 8, '.', ''), '0'), '.'),
                    'fte_value' => $fte->fte_value === null ? '' : number_format((float) $fte->fte_value, 8, '.', ''),
                    'fte_value_research' => $fte->fte_value_research === null ? '' : rtrim(rtrim(number_format((float) $fte->fte_value_research, 2, '.', ''), '0'), '.'),
                    'updated_at' => optional($fte->updated_at)->toIso8601String(),
                    'updated_by_name' => $actorName,
                    'updated_logged_at' => $activityMeta['logged_at'] ?? optional($fte->updated_at)->toIso8601String(),
                ];
            })
            ->values();

        return response()->json([
            'project_id' => $project->id,
            'project_updated_at' => optional($project->updated_at)->toIso8601String(),
            'project_updated_by_name' => $projectUpdatedByName !== '' ? $projectUpdatedByName : null,
            'rows' => $rows,
        ]);
    }

    private function buildFteActivityMetaMap(Project $project): array
    {
        $logs = $this->fetchFteActivityLogs($project);
        $metaBySecond = [];

        foreach ($logs as $log) {
            $secondKey = optional($log->created_at)?->copy()->floorSecond()?->toIso8601String();
            if (! $secondKey || isset($metaBySecond[$secondKey])) {
                continue;
            }

            $metaBySecond[$secondKey] = $this->formatFteActivityMeta($log);
        }

        return $metaBySecond;
    }

    private function fetchFteActivityLogs(Project $project)
    {
        return \App\Models\ProjectLog::query()
            ->with('user:id,prefix,name')
            ->where('project_id', $project->id)
            ->whereIn('action', ['fte_create', 'fte_update'])
            ->latest('created_at')
            ->limit(200)
            ->get();
    }

    private function resolveFteActivityMeta($updatedAt, $logs): ?array
    {
        if (! $updatedAt || ! $logs || $logs->isEmpty()) {
            return null;
        }

        $updatedAtTs = $updatedAt->copy()->floorSecond()->getTimestamp();
        $bestLog = null;
        $bestDelta = null;
        $bestNamedLog = null;
        $bestNamedDelta = null;

        foreach ($logs as $log) {
            $logAt = optional($log->created_at)?->copy()->floorSecond();
            if (! $logAt) {
                continue;
            }

            $delta = abs($logAt->getTimestamp() - $updatedAtTs);
            if ($delta > 15) {
                continue;
            }

            if ($bestDelta === null || $delta < $bestDelta) {
                $bestDelta = $delta;
                $bestLog = $log;
            }

            $actorName = trim(((string) optional($log->user)->prefix).' '.((string) optional($log->user)->name));
            if ($actorName !== '' && ($bestNamedDelta === null || $delta < $bestNamedDelta)) {
                $bestNamedDelta = $delta;
                $bestNamedLog = $log;
            }
        }

        if ($bestNamedLog) {
            return $this->formatFteActivityMeta($bestNamedLog);
        }

        if (! $bestLog) {
            $bestLog = $logs->first(function ($log) {
                $actorName = trim(((string) optional($log->user)->prefix).' '.((string) optional($log->user)->name));
                return $actorName !== '';
            }) ?? $logs->first();
        }

        return $bestLog ? $this->formatFteActivityMeta($bestLog) : null;
    }

    private function formatFteActivityMeta($log): array
    {
        $actorName = trim(((string) optional($log->user)->prefix).' '.((string) optional($log->user)->name));

        return [
            'actor_name' => $actorName !== '' ? $actorName : null,
            'logged_at' => optional($log->created_at)->toIso8601String(),
        ];
    }

    public function show(Request $request, Project $project)
    {
        $routeProjectParam = trim((string) ($request->route()?->originalParameter('project') ?? ''));
        $canonicalProjectKey = (string) $project->getRouteKey();

        $view = ResponsiveView::resolve($request, 'projects.detail');

        $fullName = trim(Auth::user()->prefix.' '.Auth::user()->name);
        $hasPermission = Auth::user()->isAdminLike()
            || Auth::id() === $project->user_id
            || $project->fteRoles->contains('name', $fullName);

        if (! $hasPermission) {
            abort(403);
        }

        if ($routeProjectParam !== '' && $canonicalProjectKey !== '' && $routeProjectParam !== $canonicalProjectKey) {
            return redirect()->route('projects.show', array_merge(['project' => $project], $request->query()));
        }

        $project->load([
            'copis',
            'irbs',
            'fteRoles',
            'progressReports' => function ($query) {
                $query->orderByDesc('submit_date')->orderByDesc('id');
            },
            'outputs.authors',
            'outputs.awardRecipients',
            'outputs.bookAuthors',
            'outputs.conferences.presenters',
            'updatedBy',
            'createdBy',   // ✅ เพิ่มตรงนี้
        ]);

        $fteChartData = $this->buildProjectFteChartData($project);
        $outputTypeOptions = Output::query()
            ->whereNotNull('type')
            ->whereRaw("TRIM(type) <> ''")
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view($view, array_merge(compact('project', 'outputTypeOptions'), $fteChartData));
    }

    public function update(Request $request, Project $project)
    {
        $fullName = trim(Auth::user()->prefix.' '.Auth::user()->name);
        $hasPermission = Auth::user()->isAdminLike()
            || Auth::id() === $project->user_id
            || $project->fteRoles->contains('name', $fullName);

        if (! $hasPermission) {
            abort(403);
        }

        $this->hydratePackedFteRequest($request);
        $validated = $this->validateProject($request);
        $this->validateOutputs($request);

        $project->fill($validated);
        $this->fillStrategies($request, $project);

        // บันทึก parent project เมื่อมี field ระดับโครงการเปลี่ยน
        $project->updated_by = Auth::id();
        if ($project->isDirty()) {
            $project->save();
        }

        $this->syncFTERoles($request, $project);
        $this->syncOutputs($request, $project);
        $this->syncCopis($request, $project);
        $this->syncIrbs($request, $project);
        $this->syncCoResearch($request, $project);
        $this->touchProjectTimestamp($project);

        return redirect()->route('projects.show', $project)
            ->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy(Project $project)
    {
        $fullName = trim(Auth::user()->prefix.' '.Auth::user()->name);

        $hasPermission = Auth::user()->isAdminLike()
            || Auth::id() === $project->user_id
            || $project->fteRoles->contains('name', $fullName);

        if (! $hasPermission) {
            abort(403);
        }

        // ลบความสัมพันธ์ทีละรายการเพื่อให้ model events ยิงและ ProjectLog ถูกบันทึกครบ
        $this->deleteModelsWithEvents($project->fteRoles());
        $this->deleteModelsWithEvents($project->outputs());
        $this->deleteModelsWithEvents($project->copis());
        $this->deleteModelsWithEvents($project->irbs());
        $this->deleteModelsWithEvents($project->coResearches());

        // แล้วค่อยลบตัวโปรเจค
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'ลบโครงการเรียบร้อยแล้ว');
    }

    public function exportPdf(Project $project)
    {
        $project->load([
            'copis',
            'irbs',
            'fteRoles',
            'progressReports' => function ($query) {
                $query->orderByDesc('submit_date')->orderByDesc('id');
            },
            'outputs.authors',
            'outputs.awardRecipients',
            'outputs.bookAuthors',
            'outputs.conferences.presenters',
            'updatedBy',
            'createdBy',
        ]); // ⬅️ authors

        $user = Auth::user();
        $author = $project->createdBy ? ($project->createdBy->prefix.$project->createdBy->name) : '';
        $creater = $user ? ($user->prefix.$user->name) : '';
        $dateStr = \Carbon\Carbon::now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
        $filename = "{$dateStr}_{$creater}_{$project->project_name_th}.pdf";

        $fteChartData = $this->buildProjectFteChartData($project);
        $pdf = PDF::loadView('projects.pdf', array_merge(compact('project', 'user'), $fteChartData));

        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'project_pdf',
            'subject_label' => (string) ($project->project_name_th ?: $project->project_code ?: $project->id),
        ], request());

        return $pdf->stream($filename);
    }

    public function exportList(Request $request)
    {
        $format = strtolower(trim((string) $request->query('format', 'xls')));
        if (!in_array($format, ['xls', 'excel', 'csv', 'pdf'], true)) {
            $format = 'xls';
        }

        $user = auth()->user();
        $query = Project::query();

        if (! $user->isAdminLike()) {
            $fullName = trim(($user->prefix ?? '').' '.$user->name);
            $query->where(function ($inner) use ($fullName, $user) {
                $inner->where('user_id', $user->id)
                    ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
        }

        if ($request->filled('year')) {
            $query->where('start_year', $request->query('year'));
        }
        if ($request->filled('type')) {
            $query->where('project_type', 'like', '%'.$request->query('type').'%');
        }
        if ($request->filled('pi_name')) {
            $query->whereRaw('TRIM(pi_name) = ?', [$request->query('pi_name')]);
        }
        if ($request->filled('funding_source')) {
            $query->whereRaw('TRIM(funding_source) = ?', [$request->query('funding_source')]);
        }
        if ($request->filled('project_status')) {
            $status = trim((string) $request->query('project_status'));
            if ($status === '__unspecified__') {
                $query->where(function ($q) {
                    $q->whereNull('planning_status')
                        ->orWhereRaw("TRIM(planning_status) = ''");
                });
            } else {
                $query->whereRaw('TRIM(planning_status) = ?', [$status]);
            }
        }
        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $searchScope = clone $query;
            $this->applyProjectNameSearch($query, $searchScope, $search);
        }

        $rows = $query
            ->orderByDesc('updated_at')
            ->limit(5000)
            ->get([
                'project_code',
                'project_name_th',
                'pi_name',
                'start_year',
                'budget',
                'funding_type',
                'funding_source',
                'project_type',
                'planning_status',
                'updated_at',
            ]);

        $year = trim((string) $request->query('year', ''));
        $periodLabel = $year !== '' ? "ปี {$year}" : 'ทั้งหมด';
        $stamp = now()->setTimezone('Asia/Bangkok')->format('Ymd_His');
        $suffix = $year !== '' ? $year : 'all';
        $baseName = "projects_{$suffix}_{$stamp}";

        if ($format === 'csv') {
            AuditLogger::logFileDownload("{$baseName}.csv", [
                'subject_type' => 'project_list_export',
                'subject_label' => 'Project List',
            ], $request);
            return response()->streamDownload(function () use ($rows) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, ['Project ID', 'ชื่อโครงการ', 'PI', 'ปีโครงการ', 'งบประมาณ (บาท)', 'ประเภทแหล่งทุน', 'แหล่งทุน', 'กลุ่มเนื้อหาหลักของงาน (Content/Theme)', 'สถานะโครงการ', 'อัปเดตล่าสุด']);

                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->project_code ?: '-',
                        $row->project_name_th ?: '-',
                        $row->pi_name ?: '-',
                        $row->start_year ?: '-',
                        is_numeric($row->budget) ? number_format((float) $row->budget, 2, '.', '') : '0.00',
                        $row->funding_type ?: '-',
                        $row->funding_source ?: '-',
                        $row->project_type ?: '-',
                        $row->planning_status ?: '-',
                        optional($row->updated_at)->format('Y-m-d H:i:s') ?: '-',
                    ]);
                }

                fclose($handle);
            }, "{$baseName}.csv", [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        if ($format === 'pdf') {
            $pdf = PDF::loadView('projects.list-pdf', [
                'rows' => $rows,
                'periodLabel' => $periodLabel,
                'generatedAt' => now()->setTimezone('Asia/Bangkok'),
                'generatedBy' => trim(($user->prefix ?? '').' '.($user->name ?? '')) ?: 'ไม่ระบุ',
            ], [], [
                'format' => 'A4-L',
            ]);

            AuditLogger::logFileDownload("{$baseName}.pdf", [
                'subject_type' => 'project_list_export',
                'subject_label' => 'Project List',
            ], $request);

            return $pdf->stream("{$baseName}.pdf");
        }

        $html = view('projects.list-export-xls', [
            'rows' => $rows,
            'periodLabel' => $periodLabel,
            'generatedAt' => now()->setTimezone('Asia/Bangkok'),
        ])->render();

        AuditLogger::logFileDownload("{$baseName}.xls", [
            'subject_type' => 'project_list_export',
            'subject_label' => 'Project List',
        ], $request);

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$baseName}.xls\"",
        ]);
    }

    // ====== SUB-FUNCTIONS ======

    private function validateProject(Request $request): array
    {
        $validated = $request->validate([
            'project_name_th' => 'required|string|max:5000',
            'project_name_en' => 'nullable|string|max:5000',
            'project_type' => 'required|string|max:255',
            'theme' => 'required|string|max:255',
            'data_source' => 'nullable|string|max:255',
            'shortcut_link' => 'nullable|string',
            'r_code' => 'nullable|string',
            'size' => 'nullable|string|max:10',
            'funding_type' => 'required|string',
            'funding_source' => 'nullable|string',
            'funding_org_type' => 'nullable|string',
            'funding_name' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'start_year' => 'nullable|string',
            'team_start_date' => 'nullable|string',
            'team_end_date' => 'nullable|string',
            'pi_name' => 'nullable|string',
            'pi_user_id' => 'nullable|integer|exists:users,id',
            'pi_person_type' => 'nullable|string|max:255',
            'pi_affiliation' => 'nullable|string',
            'planning_status' => 'required|string',
            'proposal_date' => 'nullable|string',
            'execution_status' => 'nullable|string',
            'contract_date' => 'nullable|string',
            'start_date' => 'nullable|string',
            'review_status' => 'nullable|string',
            'closure_status' => 'nullable|string',
            'io_code' => 'nullable|string',
            'course_code' => 'nullable|string',
            'end_date' => 'nullable|string',
            'updated_by' => 'nullable|integer',
            'project_list' => 'nullable|string|max:255',
            '_fte_expected_rows' => 'nullable|integer|min:0',
            '_form_expected_vars' => 'nullable|integer|min:0',
            'fte_roles' => 'nullable|array',
            'fte_roles.*.id' => 'nullable|integer',
            'fte_roles.*.fte_value' => 'nullable|numeric|min:0',
            'fte_roles.*.fte_value_research' => 'nullable|numeric|min:0|max:1',
            'fte_roles.*.hours' => 'nullable|numeric|min:0',
            'copis' => 'nullable|array',
            'copis.*.id' => 'nullable|integer',
            'copis.*.name' => 'nullable|string|max:255',
            'copis.*.affiliation' => 'nullable|string|max:255',
            'copis.*.person_type' => 'nullable|string|max:255',
            'copis.*.user_id' => 'nullable|integer|exists:users,id',
            'coresearch' => 'nullable|array',
            'coresearch.*.id' => 'nullable|integer',
            'coresearch.*.name' => 'nullable|string|max:255',
            'coresearch.*.affiliation' => 'nullable|string|max:255',
            'coresearch.*.unit_status' => 'nullable|string|max:255',
            'coresearch.*.user_id' => 'nullable|integer|exists:users,id',
        ], [
            'planning_status.required' => 'กรุณาเลือกสถานะโครงการ',
            'fte_roles.*.fte_value.numeric' => 'ค่า FTE (ผู้วิจัย) ต้องเป็นตัวเลข',
            'fte_roles.*.fte_value.min' => 'ค่า FTE (ผู้วิจัย) ต้องไม่ติดลบ',
            'fte_roles.*.fte_value_research.numeric' => 'ค่า FTE (ผู้บริหาร) ต้องเป็นตัวเลข',
            'fte_roles.*.fte_value_research.min' => 'ค่า FTE (ผู้บริหาร) ต้องไม่ติดลบ',
            'fte_roles.*.fte_value_research.max' => 'ค่า FTE (ผู้บริหาร) ต้องไม่เกิน 1.00',
            'fte_roles.*.hours.numeric' => 'ชั่วโมงการทำงานต้องเป็นตัวเลข',
            'fte_roles.*.hours.min' => 'ชั่วโมงการทำงานต้องไม่ติดลบ',
        ]);

        $this->validateInputCapacity($request);
        $this->validateRequiredFteFields($request);
        $this->validateNoPastProjectDates($request);

        $validated = $this->normalizeValidatedProjectDates($validated, $request);

        return $this->normalizeValidatedPi($validated, $request);
    }

    private function normalizeValidatedPi(array $validated, Request $request): array
    {
        $piPersonType = $this->normalizeRolePersonType($request->input('pi_person_type'));
        $piUserId = $request->input('pi_user_id');
        $piUserId = $piUserId !== null && $piUserId !== '' ? (int) $piUserId : null;

        $validated['pi_person_type'] = $piPersonType !== '' ? $piPersonType : null;

        if ($piPersonType === '') {
            throw ValidationException::withMessages([
                'pi_person_type' => 'กรุณาเลือกประเภท PI',
            ]);
        }

        if (! in_array($piPersonType, self::ROLE_PERSON_TYPES, true)) {
            throw ValidationException::withMessages([
                'pi_person_type' => 'ประเภท PI ไม่ถูกต้อง',
            ]);
        }

        if ($piPersonType !== self::PERSON_TYPE_INTERNAL_UNIT) {
            $validated['pi_user_id'] = null;
            $validated['pi_name'] = trim((string) ($validated['pi_name'] ?? ''));
            $validated['pi_affiliation'] = trim((string) ($validated['pi_affiliation'] ?? ''));

            $errors = [];
            if ($validated['pi_name'] === '') {
                $errors['pi_name'] = 'กรุณาระบุชื่อ PI';
            }
            if ($validated['pi_affiliation'] === '') {
                $errors['pi_affiliation'] = 'กรุณาระบุสังกัด PI';
            }
            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }

            return $validated;
        }

        if (! $piUserId) {
            $piUserId = $this->resolveInternalUnitUserByName($request->input('pi_name'))?->id;
        }

        if (! $piUserId) {
            throw ValidationException::withMessages([
                'pi_user_id' => 'กรุณาเลือก PI จากรายชื่อบุคลากร',
            ]);
        }

        $user = User::query()->find($piUserId);
        if (! $user) {
            throw ValidationException::withMessages([
                'pi_user_id' => 'ไม่พบข้อมูลบุคลากรที่เลือก',
            ]);
        }

        $validated['pi_user_id'] = $user->id;
        $validated['pi_name'] = $user->full_name;
        $validated['pi_affiliation'] = self::PI_INTERNAL_AFFILIATION;

        return $validated;
    }

    private function normalizeRolePersonType(?string $value): string
    {
        $normalized = trim((string) $value);

        return self::LEGACY_PERSON_TYPE_MAP[$normalized] ?? $normalized;
    }

    private function normalizePersonNameForMatch(?string $name): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $name)) ?? '';
    }

    private function resolveInternalUnitUserByName(?string $name): ?User
    {
        $normalizedName = $this->normalizePersonNameForMatch($name);
        if ($normalizedName === '') {
            return null;
        }

        return User::query()
            ->select(['id', 'prefix', 'name', 'prefix_en', 'name_en'])
            ->get()
            ->first(function (User $user) use ($normalizedName) {
                $candidateNames = [
                    $user->full_name,
                    $user->research_display_name,
                    $user->name,
                    trim(((string) ($user->prefix_en ?? '')) . ' ' . ((string) ($user->name_en ?? ''))),
                ];

                foreach ($candidateNames as $candidateName) {
                    if ($this->normalizePersonNameForMatch($candidateName) === $normalizedName) {
                        return true;
                    }
                }

                return false;
            });
    }

    private function normalizeValidatedProjectDates(array $validated, Request $request): array
    {
        $dateFields = [
            'team_start_date' => 'วันที่โครงการเริ่มทำงานของทีมวิจัย',
            'team_end_date' => 'วันที่สิ้นสุดการทำงานของทีมวิจัย',
            'proposal_date' => 'BA001 - วันที่เริ่มเขียน Proposal',
            'contract_date' => 'BC101 - วันที่สัญญาอนุมัติ',
            'start_date' => 'BC102 - วันที่โครงการเริ่ม (ตามสัญญา)',
            'end_date' => 'BC103 - วันที่สิ้นสุด (ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ)',
        ];

        $errors = [];

        foreach ($dateFields as $field => $label) {
            $raw = trim((string) $request->input($field, ''));

            if ($raw === '') {
                $validated[$field] = null;
                continue;
            }

            $parsed = $this->parseProjectDateInput($raw);
            if (! $parsed) {
                $errors[$field] = "{$label} รูปแบบวันที่ไม่ถูกต้อง";
                continue;
            }

            $validated[$field] = $parsed->format('d-m-Y');
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $validated;
    }

    private function validateRequiredFteFields(Request $request): void
    {
        $errors = [];
        $rows = (array) $request->input('fte_roles', []);

        foreach ($rows as $idx => $fteRow) {
            $name = trim((string) data_get($fteRow, 'name', ''));
            $roleGroup = trim((string) data_get($fteRow, 'role_group', ''));
            $jobDetail = trim((string) data_get($fteRow, 'job_detail', ''));
            $taskDetail = trim((string) data_get($fteRow, 'task_detail', ''));
            $startDate = trim((string) data_get($fteRow, 'start_date', ''));
            $endDate = trim((string) data_get($fteRow, 'end_date', ''));
            $hoursRaw = data_get($fteRow, 'hours', null);
            $hasHours = ! is_null($hoursRaw) && trim((string) $hoursRaw) !== '';
            $fteResearchRaw = data_get($fteRow, 'fte_value_research', null);
            $hasFteResearch = ! is_null($fteResearchRaw) && trim((string) $fteResearchRaw) !== '';

            $hasAnyValue = $name !== ''
                || $roleGroup !== ''
                || $jobDetail !== ''
                || $taskDetail !== ''
                || $startDate !== ''
                || $endDate !== ''
                || $hasHours
                || $hasFteResearch;

            if (! $hasAnyValue) {
                continue;
            }

            if ($name === '') {
                $errors["fte_roles.$idx.name"] = 'กรุณากรอกชื่อ-นามสกุล ผู้มีส่วนร่วม';
            }
            if ($roleGroup === '') {
                $errors["fte_roles.$idx.role_group"] = 'กรุณาเลือกกลุ่มของผู้มีส่วนร่วม';
            }
            if ($jobDetail === '') {
                $errors["fte_roles.$idx.job_detail"] = 'กรุณาเลือกภาระงานของผู้มีส่วนร่วม';
            }
            if ($taskDetail === '') {
                $errors["fte_roles.$idx.task_detail"] = 'กรุณาเลือกรายละเอียดภารกิจ';
            }
            if ($startDate === '') {
                $errors["fte_roles.$idx.start_date"] = 'กรุณาเลือกวันเริ่มต้นภาระงาน';
            }
            if ($endDate === '') {
                $errors["fte_roles.$idx.end_date"] = 'กรุณาเลือกวันสิ้นสุดภาระงาน';
            }
            if (! $hasHours) {
                $errors["fte_roles.$idx.hours"] = 'กรุณาระบุชั่วโมงการทำงาน';
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function validateInputCapacity(Request $request): void
    {
        $hasPackedFtePayload = trim((string) $request->input('fte_roles_payload', '')) !== '';
        $expectedFteRows = (int) $request->input('_fte_expected_rows', 0);
        $expectedVars = (int) $request->input('_form_expected_vars', 0);
        $receivedFteRows = count((array) $request->input('fte_roles', []));
        $maxInputVars = (int) ini_get('max_input_vars');
        if ($maxInputVars <= 0) {
            $maxInputVars = 1000;
        }

        $errors = [];

        if ($expectedFteRows > 0 && $receivedFteRows < $expectedFteRows) {
            $errors['fte_roles'] = "ข้อมูลผู้มีส่วนร่วม (FTE) ถูกส่งมาไม่ครบ ({$receivedFteRows}/{$expectedFteRows} รายการ) กรุณาลดจำนวนรายการต่อครั้ง หรือตั้งค่า max_input_vars ให้สูงกว่า {$maxInputVars}";
        }

        if (! $hasPackedFtePayload && $expectedVars > $maxInputVars) {
            $errors['project_name_th'] = "ข้อมูลฟอร์มมีจำนวนช่องมากเกินลิมิตระบบ (ประมาณ {$expectedVars} ช่อง > max_input_vars {$maxInputVars}) กรุณาเพิ่มค่า max_input_vars (แนะนำอย่างน้อย 10000)";
        }

        if (! empty($errors)) {
            Log::warning('project.fte_input_capacity_failed', [
                'route' => optional($request->route())->getName(),
                'has_packed_fte_payload' => $hasPackedFtePayload,
                'expected_fte_rows' => $expectedFteRows,
                'received_fte_rows' => $receivedFteRows,
                'expected_vars' => $expectedVars,
                'max_input_vars' => $maxInputVars,
            ]);
            throw ValidationException::withMessages($errors);
        }
    }

    private function hydratePackedFteRequest(Request $request): void
    {
        $rawPayload = trim((string) $request->input('fte_roles_payload', ''));
        if ($rawPayload === '') {
            return;
        }

        try {
            $decoded = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::error('project.fte_payload_decode_failed', [
                'route' => optional($request->route())->getName(),
                'payload_length' => strlen($rawPayload),
                'payload_preview' => mb_substr($rawPayload, 0, 500),
                'error' => $e->getMessage(),
            ]);
            throw ValidationException::withMessages([
                'fte_roles' => 'ข้อมูลผู้มีส่วนร่วม (FTE) ที่ส่งมาไม่ถูกต้อง กรุณาลองบันทึกใหม่อีกครั้ง',
            ]);
        }

        if (! is_array($decoded)) {
            Log::error('project.fte_payload_invalid_shape', [
                'route' => optional($request->route())->getName(),
                'payload_length' => strlen($rawPayload),
                'decoded_type' => gettype($decoded),
            ]);
            throw ValidationException::withMessages([
                'fte_roles' => 'ข้อมูลผู้มีส่วนร่วม (FTE) ที่ส่งมาไม่ถูกต้อง กรุณาลองบันทึกใหม่อีกครั้ง',
            ]);
        }

        $rows = collect($decoded)
            ->filter(fn ($row) => is_array($row))
            ->values()
            ->map(function (array $row) {
                return [
                    'id' => $this->normalizePackedFteScalar($row['id'] ?? null),
                    'name' => $this->normalizePackedFteScalar($row['name'] ?? null),
                    'role_group' => $this->normalizePackedFteScalar($row['role_group'] ?? null),
                    'job_detail' => $this->normalizePackedFteScalar($row['job_detail'] ?? null),
                    'start_date' => $this->normalizePackedFteScalar($row['start_date'] ?? null),
                    'end_date' => $this->normalizePackedFteScalar($row['end_date'] ?? null),
                    'hours' => $this->normalizePackedFteScalar($row['hours'] ?? null),
                    'fte_value' => $this->normalizePackedFteScalar($row['fte_value'] ?? null),
                    'fte_value_research' => $this->normalizePackedFteScalar($row['fte_value_research'] ?? null),
                    'task_detail' => $this->normalizePackedFteScalar($row['task_detail'] ?? null),
                ];
            })
            ->all();

        $request->merge([
            'fte_roles' => $rows,
            '_fte_expected_rows' => count($rows),
            '_form_expected_vars' => 0,
        ]);

        Log::info('project.fte_payload_hydrated', [
            'route' => optional($request->route())->getName(),
            'payload_length' => strlen($rawPayload),
            'rows' => count($rows),
            'deleted_ids' => count((array) $request->input('fte_deleted_ids', [])),
        ]);
    }

    private function normalizePackedFteScalar($value): ?string
    {
        if (is_array($value) || is_object($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function validateNoPastProjectDates(Request $request): void
    {
        $errors = [];

        $teamStart = $this->parseProjectDateInput((string) $request->input('team_start_date', ''));
        $teamEnd = $this->parseProjectDateInput((string) $request->input('team_end_date', ''));
        if ($teamStart && $teamEnd && $teamEnd->lt($teamStart)) {
            $errors['team_end_date'] = 'วันที่สิ้นสุดการทำงานของทีมวิจัย ต้องไม่น้อยกว่าวันที่เริ่มทำงาน';
        }

        $contractStart = $this->parseProjectDateInput((string) $request->input('start_date', ''));
        $contractEnd = $this->parseProjectDateInput((string) $request->input('end_date', ''));
        if ($contractStart && $contractEnd && $contractEnd->lt($contractStart)) {
            $errors['end_date'] = 'BC103 - วันที่สิ้นสุด (ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ) ต้องไม่น้อยกว่า BC102 - วันที่โครงการเริ่ม (ตามสัญญา)';
        }

        foreach ((array) $request->input('fte_roles', []) as $idx => $fteRow) {
            $startRaw = trim((string) data_get($fteRow, 'start_date', ''));
            $endRaw = trim((string) data_get($fteRow, 'end_date', ''));

            $start = $this->parseProjectDateInput($startRaw);
            $end = $this->parseProjectDateInput($endRaw);

            if ($startRaw !== '' && ! $start) {
                $errors["fte_roles.$idx.start_date"] = 'รูปแบบวันเริ่มต้นภาระงานไม่ถูกต้อง';
            }
            if ($endRaw !== '' && ! $end) {
                $errors["fte_roles.$idx.end_date"] = 'รูปแบบวันสิ้นสุดภาระงานไม่ถูกต้อง';
            }

            if ($start && $end && $end->lt($start)) {
                $errors["fte_roles.$idx.end_date"] = 'วันสิ้นสุดภาระงาน ต้องไม่น้อยกว่าวันเริ่มต้นภาระงาน';
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function parseProjectDateInput(?string $value): ?Carbon
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $raw = preg_replace_callback('/[๐-๙]/u', static fn ($m) => (string) mb_strpos('๐๑๒๓๔๕๖๗๘๙', $m[0]), $raw);
        $raw = preg_replace('/\s+/u', ' ', trim((string) $raw));

        if (preg_match('/^(\d{1,2})([-\/])(\d{1,2})\2(\d{4})$/', $raw, $m)) {
            $day = (int) $m[1];
            $sep = $m[2];
            $month = (int) $m[3];
            $year = (int) $m[4];
            if ($year > 2400) {
                $year -= 543;
            }
            $raw = sprintf('%02d%s%02d%s%04d', $day, $sep, $month, $sep, $year);
        } elseif (preg_match('/^(\d{4})([-\/])(\d{1,2})\2(\d{1,2})$/', $raw, $m)) {
            $year = (int) $m[1];
            $sep = $m[2];
            $month = (int) $m[3];
            $day = (int) $m[4];
            if ($year > 2400) {
                $year -= 543;
            }
            $raw = sprintf('%04d%s%02d%s%02d', $year, $sep, $month, $sep, $day);
        }

        $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'Y/m/d'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $raw)->startOfDay();
            } catch (\Throwable $e) {
            }
        }

        if (preg_match('/^(\d{1,2})\s+([^\s]+)\s+(\d{4})$/u', $raw, $m)) {
            $day = (int) $m[1];
            $monthRaw = trim($m[2]);
            $year = (int) $m[3];

            $monthMap = [
                'มกราคม' => 1, 'ม.ค.' => 1, 'ม.ค' => 1,
                'กุมภาพันธ์' => 2, 'ก.พ.' => 2, 'ก.พ' => 2,
                'มีนาคม' => 3, 'มี.ค.' => 3, 'มี.ค' => 3,
                'เมษายน' => 4, 'เม.ย.' => 4, 'เม.ย' => 4,
                'พฤษภาคม' => 5, 'พ.ค.' => 5, 'พ.ค' => 5,
                'มิถุนายน' => 6, 'มิ.ย.' => 6, 'มิ.ย' => 6,
                'กรกฎาคม' => 7, 'ก.ค.' => 7, 'ก.ค' => 7,
                'สิงหาคม' => 8, 'ส.ค.' => 8, 'ส.ค' => 8,
                'กันยายน' => 9, 'ก.ย.' => 9, 'ก.ย' => 9,
                'ตุลาคม' => 10, 'ต.ค.' => 10, 'ต.ค' => 10,
                'พฤศจิกายน' => 11, 'พ.ย.' => 11, 'พ.ย' => 11,
                'ธันวาคม' => 12, 'ธ.ค.' => 12, 'ธ.ค' => 12,
            ];

            if (array_key_exists($monthRaw, $monthMap)) {
                if ($year > 2400) {
                    $year -= 543;
                }
                try {
                    return Carbon::create($year, $monthMap[$monthRaw], $day, 0, 0, 0)->startOfDay();
                } catch (\Throwable $e) {
                }
            }
        }

        try {
            return Carbon::parse($raw)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function projectNormalizedDateSql(string $column): string
    {
        $field = "`{$column}`";
        $trimmed = "NULLIF(TRIM({$field}), '')";
        $clean = "REPLACE({$trimmed}, '/', '-')";
        $dmyYear = "CAST(SUBSTRING_INDEX({$clean}, '-', -1) AS UNSIGNED)";
        $ymdYear = "CAST(SUBSTRING_INDEX({$clean}, '-', 1) AS UNSIGNED)";

        $normalizedDmy = "CASE
            WHEN {$clean} REGEXP '^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$'
            THEN CONCAT(
                SUBSTRING_INDEX({$clean}, '-', 2),
                '-',
                LPAD(CASE WHEN {$dmyYear} > 2400 THEN {$dmyYear} - 543 ELSE {$dmyYear} END, 4, '0')
            )
            ELSE NULL
        END";

        $normalizedYmd = "CASE
            WHEN {$clean} REGEXP '^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$'
            THEN CONCAT(
                LPAD(CASE WHEN {$ymdYear} > 2400 THEN {$ymdYear} - 543 ELSE {$ymdYear} END, 4, '0'),
                '-',
                SUBSTRING_INDEX(SUBSTRING_INDEX({$clean}, '-', 2), '-', -1),
                '-',
                SUBSTRING_INDEX({$clean}, '-', -1)
            )
            ELSE NULL
        END";

        return "COALESCE(
            STR_TO_DATE({$normalizedYmd}, '%Y-%m-%d'),
            STR_TO_DATE({$normalizedDmy}, '%d-%m-%Y'),
            STR_TO_DATE({$clean}, '%Y-%m-%d'),
            STR_TO_DATE({$clean}, '%d-%m-%Y')
        )";
    }

    private function projectNormalizedTextSql(string $column): string
    {
        $field = "COALESCE(`{$column}`, '')";
        return "REGEXP_REPLACE(LOWER(TRIM({$field})), '[[:space:][:punct:]]+', '')";
    }

    private function buildProjectFteChartData(Project $project): array
    {
        $rows = $project->fteRoles ?? collect();
        $expanded = collect();

        foreach ($rows as $item) {
            if (empty($item->start_date) || empty($item->end_date)) {
                continue;
            }
            try {
                $formatStart = strpos($item->start_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';
                $formatEnd = strpos($item->end_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';

                $start = Carbon::createFromFormat($formatStart, $item->start_date)->startOfDay();
                $end = Carbon::createFromFormat($formatEnd, $item->end_date)->endOfDay();

                if ($start->gt($end)) {
                    continue;
                }

                $totalDays = $start->diffInDays($end) + 1;
                if ($totalDays <= 0) {
                    continue;
                }

                $ftePerDay = $item->fte_value / $totalDays;

                $cursor = $start->copy()->startOfMonth();
                while ($cursor <= $end) {
                    $mStart = $cursor->copy()->startOfMonth();
                    $mEnd = $cursor->copy()->endOfMonth();

                    $overlapStart = $start->copy()->max($mStart);
                    $overlapEnd = $end->copy()->min($mEnd);

                    if ($overlapStart->lte($overlapEnd)) {
                        $daysInMonth = $overlapStart->diffInDays($overlapEnd) + 1;
                        $fteMonth = $daysInMonth * $ftePerDay;

                        $expanded->push((object)[
                            'month' => $cursor->format('Y-m'),
                            'name' => $item->name,
                            'fte_value' => $fteMonth,
                        ]);
                    }

                    $cursor->addMonth();
                }
            } catch (\Exception $e) {}
        }

        $monthlyFTE = $expanded->groupBy('month')->sortKeys();
        $fteChartLabels = $monthlyFTE->keys()->values();
        $personNames = $expanded->pluck('name')->filter()->unique()->values();
        $fteChartDatasets = $personNames->map(function ($name) use ($monthlyFTE) {
            $data = $monthlyFTE->map(function ($rows) use ($name) {
                return $rows->where('name', $name)->sum('fte_value');
            })->values();
            return [
                'label' => $name,
                'data' => $data,
            ];
        })->values();

        $yearTotals = [];
        foreach ($monthlyFTE as $month => $items) {
            $year = (int) substr($month, 0, 4);
            $yearTotals[$year] = ($yearTotals[$year] ?? 0) + $items->sum('fte_value');
        }
        $currentYear = (int) now()->year;
        $years = [$currentYear - 2, $currentYear - 1, $currentYear];
        $fteYearLabels = array_map(fn($y) => $y + 543, $years);
        $fteYearData = array_map(fn($y) => $yearTotals[$y] ?? 0, $years);

        $fteMonthlySummary = $monthlyFTE->map(function ($rows, $month) {
            return [
                'month' => $month,
                'total' => $rows->sum('fte_value'),
            ];
        })->values();
        $fteMonthlyMax = $fteMonthlySummary->max('total') ?: 0;

        return [
            'fteChartLabels' => $fteChartLabels,
            'fteChartDatasets' => $fteChartDatasets,
            'fteYearLabels' => $fteYearLabels,
            'fteYearData' => $fteYearData,
            'fteMonthlySummary' => $fteMonthlySummary,
            'fteMonthlyMax' => $fteMonthlyMax,
        ];
    }

    private function validateOutputs(Request $request): void
    {
        $request->validate([
            'outputs' => ['nullable', 'array'],
            'outputs.*.id' => ['nullable', 'integer', 'exists:outputs,id'],
            'outputs.*.type' => ['nullable', 'string'],
            'outputs.*.title' => ['nullable', 'string', 'max:5000'],
            'outputs.*.project_output' => ['nullable', 'string'],
            'outputs.*.year' => ['nullable', 'string'],

            'outputs.*.journal' => ['nullable', 'string'],
            'outputs.*.quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4,TCI 1,TCI 2'],
            'outputs.*.impact_factor' => ['nullable', 'numeric'],

            'outputs.*.authors' => ['nullable', 'array'],
            'outputs.*.authors.*.id' => ['nullable', 'integer', 'exists:output_authors,id'],
            'outputs.*.authors.*.person_type' => ['nullable', 'in:internal,external'],
            'outputs.*.authors.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.authors.*.role' => ['nullable', 'string'],
            'outputs.*.authors.*.name' => ['nullable', 'string'],
            'outputs.*.award_recipients' => ['nullable', 'array'],
            'outputs.*.award_recipients.*.id' => ['nullable', 'integer', 'exists:output_award_recipients,id'],
            'outputs.*.award_recipients.*.person_type' => ['nullable', 'in:unit,internal,external'],
            'outputs.*.award_recipients.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.award_recipients.*.name' => ['nullable', 'string'],

            // EA103 (Conference + Presenters)
            'outputs.*.conference_id' => ['nullable', 'integer', 'exists:output_conferences,id'],
            'outputs.*.conference_name' => ['nullable', 'string'],
            'outputs.*.conference_presentation_type' => ['nullable', 'string'],
            'outputs.*.conference_location' => ['nullable', 'string'],
            'outputs.*.conference_presenters' => ['nullable', 'array'],
            'outputs.*.conference_presenters.*.id' => ['nullable', 'integer', 'exists:output_conference_presenters,id'],
            'outputs.*.conference_presenters.*.person_type' => ['nullable', 'in:internal,external'],
            'outputs.*.conference_presenters.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.conference_presenters.*.note' => ['nullable', 'string'],

            // EA119 (Textbook / Book chapter)
            'outputs.*.book_title' => ['nullable', 'string', 'max:1000'],
            'outputs.*.publisher_name' => ['nullable', 'string', 'max:1000'],
            'outputs.*.chapter_pages' => ['nullable', 'string', 'max:1000'],
            'outputs.*.isbn' => ['nullable', 'string', 'max:1000'],
            'outputs.*.book_authors' => ['nullable', 'array'],
            'outputs.*.book_authors.*.id' => ['nullable', 'integer', 'exists:output_book_authors,id'],
            'outputs.*.book_authors.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.book_authors.*.name' => ['nullable', 'string', 'max:255'],
        ]);

        $errors = [];
        foreach ((array) $request->input('outputs', []) as $index => $row) {
            $type = trim((string) ($row['type'] ?? ''));
            if (! str_starts_with($type, 'EA119')) {
                continue;
            }

            $requiredEa119Fields = [
                'book_title' => 'ชื่อหนังสือ',
                'publisher_name' => 'ชื่อสำนักพิมพ์',
                'chapter_pages' => 'ชื่อบท / เลขหน้า',
            ];

            foreach ($requiredEa119Fields as $field => $label) {
                if (trim((string) ($row[$field] ?? '')) === '') {
                    $errors["outputs.$index.$field"][] = "กรุณากรอก{$label}";
                }
            }

            // Duplicate textbook authors are ignored during sync instead of blocking the whole save.
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function fillStrategies(Request $request, Project $project): void
    {
        $valid_strategies = [
            '1' => [1, 2, 3, 4, 5],
            '2' => [1, 2, 3],
            '3' => [1, 2, 3, 4, 5],
            '4' => [1, 2],
            '5' => [1, 2],
        ];

        foreach ($valid_strategies as $main => $subs) {
            $mainChecked = false;
            foreach ($subs as $sub) {
                $field = "strategy_{$main}_{$sub}";
                $has = $request->has($field);
                if ($has != $project->$field) {
                    $project->$field = $has;
                }
                if ($has) {
                    $mainChecked = true;
                }
            }
            $mainField = "strategy_$main";
            if ($mainChecked != $project->$mainField) {
                $project->$mainField = $mainChecked;
            }
        }
    }

    private function syncFTERoles(Request $request, Project $project): void
    {
        $existingById = $project->fteRoles()->get()->keyBy('id');
        $deleteIds = collect((array) $request->input('fte_deleted_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();
        $deleteLookup = array_fill_keys($deleteIds->all(), true);

        foreach (($request->fte_roles ?? []) as $fte) {
            $start = $this->parseProjectDateInput((string) ($fte['start_date'] ?? ''));
            $end = $this->parseProjectDateInput((string) ($fte['end_date'] ?? ''));
            $hoursRaw = $fte['hours'] ?? null;
            $hours = ($hoursRaw === null || $hoursRaw === '') ? null : round((float) $hoursRaw, 8);
            $computedFteValue = $hours !== null ? $this->computeProjectFteValueFromHours($hours) : 0.0;

            $payload = [
                'role_group' => trim((string) ($fte['role_group'] ?? '')),
                'name' => trim((string) ($fte['name'] ?? '')) ?: null,
                'job_detail' => trim((string) ($fte['job_detail'] ?? '')) ?: null,
                'start_date' => $start?->format('d-m-Y'),
                'hours' => $hours,
                'end_date' => $end?->format('d-m-Y'),
                'fte_value' => $computedFteValue,
                'fte_value_research' => $fte['fte_value_research'] ?? 0,
                'task_detail' => trim((string) ($fte['task_detail'] ?? '')) ?: null,
            ];

            if (
                empty($payload['name']) &&
                empty($payload['job_detail']) &&
                empty($payload['task_detail']) &&
                empty($payload['start_date']) &&
                empty($payload['end_date']) &&
                empty($payload['hours']) &&
                (float) $payload['fte_value'] === 0.0 &&
                (float) $payload['fte_value_research'] === 0.0
            ) {
                continue;
            }

            $rowId = (int) ($fte['id'] ?? 0);
            if ($rowId > 0 && isset($existingById[$rowId]) && ! isset($deleteLookup[$rowId])) {
                $existingById[$rowId]->update($payload);
                continue;
            }

            if (! empty($payload['name'])) {
                $project->fteRoles()->create($payload);
            }
        }

        if ($deleteIds->isNotEmpty()) {
            $this->deleteModelsWithEvents($project->fteRoles()->whereIn('id', $deleteIds));
        }
    }

    private function computeProjectFteValueFromHours(float $hours): float
    {
        return round($hours * 0.000621, 8);
    }

    private function syncOutputs(Request $request, Project $project): void
    {
        $rows = $request->input('outputs', []);
        $keepIds = [];
        $unmatchedExisting = $project->outputs()->orderBy('id')->get()->keyBy('id');

        foreach ($rows as $row) {
            if (! $this->hasAnyOutputData($row)) {
                continue;
            }

            $payload = [
                'project_id' => $project->id,
                'type' => $row['type'] ?? null,
                'title' => $row['title'] ?? null,
                'project_output' => $row['project_output'] ?? ($row['output'] ?? null),
                'year' => $row['year'] ?? null,
                'journal' => null,
                'quartile' => null,
                'impact_factor' => null,
                'book_title' => null,
                'publisher_name' => null,
                'chapter_pages' => null,
                'isbn' => null,
            ];

            $type = (string) ($payload['type'] ?? '');
            $isEA101 = str_starts_with($type, 'EA101');
            $isEA103 = str_starts_with($type, 'EA103');
            $isEA118 = str_starts_with($type, 'EA118');
            $isEA119 = str_starts_with($type, 'EA119');

            if ($isEA101) {
                $payload['journal'] = $row['journal'] ?? null;
                $payload['quartile'] = $this->normalizeQuartile($row['quartile'] ?? null);
                $payload['impact_factor'] = $row['impact_factor'] ?? null;
            }

            if ($isEA119) {
                $payload['book_title'] = $row['book_title'] ?? null;
                $payload['publisher_name'] = $row['publisher_name'] ?? null;
                $payload['chapter_pages'] = $row['chapter_pages'] ?? null;
                $payload['isbn'] = $row['isbn'] ?? null;
            }

            if (! empty($row['id'])) {
                $model = $project->outputs()->whereKey($row['id'])->first();
                if ($model) {
                    $model->update($payload);
                    $unmatchedExisting->forget($model->id);

                    // relations
                    $this->syncOutputAuthors($model, $row['authors'] ?? null, $isEA101);
                    $this->syncOutputAwardRecipients($model, $row['award_recipients'] ?? null, $isEA118);
                    $this->syncOutputBookAuthors($model, $row['book_authors'] ?? null, $isEA119);
                    $this->syncOutputConferences($model, $row, $isEA103);

                    $keepIds[] = $model->id;
                }
            } else {
                $fallback = $unmatchedExisting->shift();
                if ($fallback) {
                    $fallback->update($payload);

                    // relations
                    $this->syncOutputAuthors($fallback, $row['authors'] ?? null, $isEA101);
                    $this->syncOutputAwardRecipients($fallback, $row['award_recipients'] ?? null, $isEA118);
                    $this->syncOutputBookAuthors($fallback, $row['book_authors'] ?? null, $isEA119);
                    $this->syncOutputConferences($fallback, $row, $isEA103);

                    $keepIds[] = $fallback->id;
                    continue;
                }

                $new = $project->outputs()->create($payload);

                // relations
                $this->syncOutputAuthors($new, $row['authors'] ?? null, $isEA101);
                $this->syncOutputAwardRecipients($new, $row['award_recipients'] ?? null, $isEA118);
                $this->syncOutputBookAuthors($new, $row['book_authors'] ?? null, $isEA119);
                $this->syncOutputConferences($new, $row, $isEA103);

                $keepIds[] = $new->id;
            }
        }

        $staleOutputs = $project->outputs();
        if (!empty($keepIds)) {
            $staleOutputs->whereNotIn('id', $keepIds);
        }
        $this->deleteModelsWithEvents($staleOutputs);
    }

    private function syncOutputAuthors(Output $output, ?array $authors, bool $supportsAuthors): void
    {
        if (! $supportsAuthors) {
            $this->deleteModelsWithEvents($output->authors());

            return;
        }

        $authors = $authors ?? []; // ป้องกัน null

        if (! $authors) {
            $this->deleteModelsWithEvents($output->authors());

            return;
        }

        $ids = [];
        foreach ($authors as $author) {
            $userId = (($author['user_id'] ?? null) !== null && (int) $author['user_id'] > 0)
                ? (int) $author['user_id']
                : null;
            $role = strtolower(trim((string) ($author['role'] ?? '')));
            $linkedUser = $userId ? User::find($userId) : null;
            $name = $linkedUser
                ? trim((string) $linkedUser->research_display_name)
                : trim((string) ($author['name'] ?? ''));

            if ($name === '' || $role === '') {
                continue;
            }

            if (! in_array($role, ['first', 'co', 'corresponding'], true)) {
                continue;
            }

            $payload = [
                'role' => $role,
                'name' => $name,
                'user_id' => $userId,
            ];

            if (! empty($author['id'])) {
                $model = $output->authors()->find($author['id']);
                if ($model) {
                    $model->update($payload);
                    $ids[] = $model->id;
                }
            } else {
                $new = $output->authors()->create($payload);
                $ids[] = $new->id;
            }
        }

        if ($ids) {
            $this->deleteModelsWithEvents($output->authors()->whereNotIn('id', $ids));
            return;
        }

        $this->deleteModelsWithEvents($output->authors());
    }

    private function syncOutputAwardRecipients(Output $output, ?array $recipients, bool $isEA118): void
    {
        if (! $isEA118) {
            $this->deleteModelsWithEvents($output->awardRecipients());

            return;
        }

        $recipients = $recipients ?? [];
        if (! $recipients) {
            $this->deleteModelsWithEvents($output->awardRecipients());

            return;
        }

        $ids = [];
        foreach ($recipients as $recipient) {
            $userId = (($recipient['user_id'] ?? null) !== null && (int) $recipient['user_id'] > 0)
                ? (int) $recipient['user_id']
                : null;
            $linkedUser = $userId ? User::find($userId) : null;
            $name = $linkedUser
                ? trim((string) $linkedUser->full_name)
                : trim((string) ($recipient['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $payload = [
                'name' => $name,
                'user_id' => $userId,
            ];

            if (! empty($recipient['id'])) {
                $model = $output->awardRecipients()->find($recipient['id']);
                if ($model) {
                    $model->update($payload);
                    $ids[] = $model->id;
                }
            } else {
                $new = $output->awardRecipients()->create($payload);
                $ids[] = $new->id;
            }
        }

        if ($ids) {
            $this->deleteModelsWithEvents($output->awardRecipients()->whereNotIn('id', $ids));
            return;
        }

        $this->deleteModelsWithEvents($output->awardRecipients());
    }

    private function syncOutputBookAuthors(Output $output, ?array $authors, bool $isEA119): void
    {
        if (! $isEA119) {
            $this->deleteModelsWithEvents($output->bookAuthors());

            return;
        }

        if ($authors === null) {
            $this->deleteModelsWithEvents($output->bookAuthors());

            return;
        }

        $existing = $output->bookAuthors()->get();
        $existingById = $existing->keyBy('id');
        $existingByNameKey = [];
        foreach ($existing as $existingAuthor) {
            $nameKey = $this->normalizeAuthorNameForDuplicateCheck((string) $existingAuthor->name);
            if ($nameKey === '' || isset($existingByNameKey[$nameKey])) {
                continue;
            }
            $existingByNameKey[$nameKey] = $existingAuthor;
        }

        $ids = [];
        $seen = [];
        foreach ($authors as $author) {
            $userId = (($author['user_id'] ?? null) !== null && (int) $author['user_id'] > 0)
                ? (int) $author['user_id']
                : null;
            $linkedUser = $userId ? User::find($userId) : null;
            $name = $linkedUser
                ? trim((string) $linkedUser->full_name)
                : trim((string) ($author['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $nameKey = $this->normalizeAuthorNameForDuplicateCheck($name);
            if ($nameKey === '') {
                continue;
            }
            if (isset($seen[$nameKey])) {
                continue;
            }
            $seen[$nameKey] = true;

            $payload = [
                'name' => $name,
                'user_id' => $userId,
            ];
            $model = null;
            $authorId = (int) ($author['id'] ?? 0);
            if ($authorId > 0) {
                $model = $existingById->get($authorId);
            }
            if (! $model) {
                $model = $existingByNameKey[$nameKey] ?? null;
            }

            if ($model) {
                if ((string) $model->name !== $name) {
                    $model->update($payload);
                }
                $ids[] = $model->id;
            } else {
                $new = $output->bookAuthors()->create($payload);
                $ids[] = $new->id;
                $existingByNameKey[$nameKey] = $new;
            }
        }

        if (! empty($ids)) {
            $this->deleteModelsWithEvents($output->bookAuthors()->whereNotIn('id', $ids));
        } else {
            $this->deleteModelsWithEvents($output->bookAuthors());
        }
    }

    private function normalizeAuthorNameForDuplicateCheck(string $name): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($name)) ?? '';
        if ($normalized === '') {
            return '';
        }

        $normalized = preg_replace('/^(คุณ|นาย|นางสาว|นาง|น\.ส\.|ดร\.?|ผศ\.?|รศ\.?|ศ\.?)\s+/u', '', $normalized) ?? $normalized;

        return mb_strtolower(trim($normalized));
    }

    private function syncOutputConferences(Output $output, array $row, bool $isEA103): void
    {
        if (! $isEA103) {
            $this->deleteModelsWithEvents($output->conferences());

            return;
        }

        $conferenceId = (($row['conference_id'] ?? null) !== null && (int) $row['conference_id'] > 0)
            ? (int) $row['conference_id']
            : null;
        $conferenceName = trim((string) ($row['conference_name'] ?? ''));
        $presentationType = trim((string) ($row['conference_presentation_type'] ?? ''));
        $location = trim((string) ($row['conference_location'] ?? ''));
        $presenters = is_array($row['conference_presenters'] ?? null) ? $row['conference_presenters'] : [];

        $hasConferencePayload = $conferenceName !== '' || $presentationType !== '' || $location !== '';
        $hasPresenterPayload = collect($presenters)->contains(function ($presenter) {
            return trim((string) ($presenter['note'] ?? '')) !== ''
                || (int) ($presenter['user_id'] ?? 0) > 0
                || trim((string) ($presenter['person_type'] ?? '')) !== '';
        });

        if (! $hasConferencePayload && ! $hasPresenterPayload) {
            $this->deleteModelsWithEvents($output->conferences());

            return;
        }

        $conference = null;
        if ($conferenceId) {
            $conference = $output->conferences()->whereKey($conferenceId)->first();
        }
        if (! $conference) {
            $conference = $output->conferences()->orderBy('id')->first();
        }
        if (! $conference) {
            $conference = $output->conferences()->make();
        }

        $conference->fill([
            'name' => $conferenceName !== '' ? $conferenceName : null,
            'presentation_type' => $presentationType !== '' ? $presentationType : null,
            'location' => $location !== '' ? $location : null,
        ]);
        $conference->output()->associate($output);
        $conference->save();

        $presenterIds = [];
        foreach ($presenters as $index => $presenter) {
            $personType = trim((string) ($presenter['person_type'] ?? ''));
            $userId = (($presenter['user_id'] ?? null) !== null && (int) $presenter['user_id'] > 0)
                ? (int) $presenter['user_id']
                : null;
            $presenterName = trim((string) ($presenter['note'] ?? ''));

            if ($personType === 'internal') {
                $user = $this->resolveOutputConferenceUser($userId, $presenterName);
                if (! $user) {
                    throw ValidationException::withMessages([
                        "conference_presenters.$index.user_id" => 'กรุณาเลือกผู้นำเสนอจากรายชื่อบุคลากร',
                    ]);
                }

                $userId = $user->id;
                $presenterName = $user->full_name;
            } elseif ($personType === 'external') {
                $userId = null;
            }

            if ($presenterName === '' && ! $userId) {
                continue;
            }

            $payload = [
                'user_id' => $userId,
                'name' => $presenterName !== '' ? $presenterName : null,
            ];

            if (! empty($presenter['id'])) {
                $m = $conference->presenters()->whereKey($presenter['id'])->first();
                if ($m) {
                    $m->update($payload);
                    $presenterIds[] = $m->id;
                }
            } else {
                $new = $conference->presenters()->create($payload);
                $presenterIds[] = $new->id;
            }
        }

        if (! empty($presenterIds)) {
            $this->deleteModelsWithEvents($conference->presenters()->whereNotIn('id', $presenterIds));
        } else {
            $this->deleteModelsWithEvents($conference->presenters());
        }

        $extraConferenceIds = $output->conferences()
            ->where('id', '!=', $conference->id)
            ->pluck('id');
        if ($extraConferenceIds->isNotEmpty()) {
            $this->deleteModelsWithEvents($output->conferences()->whereIn('id', $extraConferenceIds->all()));
        }
    }

    private function resolveOutputConferenceUser(?int $userId, string $name): ?User
    {
        if ($userId) {
            $user = User::query()->find($userId);
            if ($user) {
                return $user;
            }
        }

        $name = trim($name);
        if ($name === '') {
            return null;
        }

        return User::query()
            ->whereRaw("TRIM(CONCAT(COALESCE(prefix, ''), ' ', COALESCE(name, ''))) = ?", [$name])
            ->orWhereRaw('TRIM(name) = ?', [$name])
            ->first();
    }

    private function hasAnyOutputData(array $row): bool
    {
        return (bool) (
            ($row['type'] ?? null) ||
            ($row['title'] ?? null) ||
            ($row['project_output'] ?? ($row['output'] ?? null)) ||
            ($row['year'] ?? null) ||
            ($row['journal'] ?? null) ||
            ($row['quartile'] ?? null) ||
            ($row['impact_factor'] ?? null) ||
            ($row['book_title'] ?? null) ||
            ($row['publisher_name'] ?? null) ||
            ($row['chapter_pages'] ?? null) ||
            ($row['isbn'] ?? null) ||
            (! empty($row['authors'])) ||
            (! empty($row['award_recipients'])) ||
            (! empty($row['book_authors']))
        );
    }

    private function normalizeQuartile(?string $q): ?string
    {
        if (! $q) {
            return null;
        }
        $q = strtoupper(trim($q));
        if (in_array($q, ['Q1', 'Q2', 'Q3', 'Q4', 'TCI 1', 'TCI 2'], true)) {
            return $q;
        }
        if (in_array($q, ['1', '2', '3', '4'], true)) {
            return 'Q'.$q;
        }

        return null;
    }

    private function syncCopis(Request $request, Project $project): void
    {
        $ids = [];
        $unmatchedExisting = $project->copis()->orderBy('id')->get()->keyBy('id');
        $errors = [];

        foreach ($request->copis ?? [] as $idx => $copi) {
            $payload = $this->buildCopiPayload($copi, (int) $idx, $errors);
            if ($payload === null) {
                continue;
            }

            if (! empty($copi['id'])) {
                $copiModel = $project->copis()->whereKey($copi['id'])->first();
                if ($copiModel) {
                    $copiModel->update($payload);
                    $unmatchedExisting->forget($copiModel->id);
                    $ids[] = $copiModel->id;
                }
            } else {
                $fallback = $unmatchedExisting->shift();
                if ($fallback) {
                    $fallback->update($payload);
                    $ids[] = $fallback->id;
                    continue;
                }

                $new = \App\Models\Copi::create(array_merge($payload, [
                    'project_id' => $project->id,
                ]));
                $ids[] = $new->id;
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $staleCopis = $project->copis();
        if (!empty($ids)) {
            $staleCopis->whereNotIn('id', $ids);
        }
        $this->deleteModelsWithEvents($staleCopis);
    }

    private function buildCopiPayload(array $copi, int $idx, array &$errors): ?array
    {
        $rowErrors = [];
        $personType = $this->normalizeRolePersonType($copi['person_type'] ?? null);
        $name = trim((string) ($copi['name'] ?? ''));
        $affiliation = trim((string) ($copi['affiliation'] ?? ''));
        $userId = isset($copi['user_id']) && $copi['user_id'] !== '' ? (int) $copi['user_id'] : null;

        $hasAnyValue = $personType !== '' || $name !== '' || $affiliation !== '' || $userId !== null;
        if (! $hasAnyValue) {
            return null;
        }

        if ($personType === '') {
            $rowErrors["copis.$idx.person_type"] = 'กรุณาเลือกประเภท COPI';
            $errors = array_merge($errors, $rowErrors);
            return null;
        }

        if (! in_array($personType, self::ROLE_PERSON_TYPES, true)) {
            $rowErrors["copis.$idx.person_type"] = 'ประเภท COPI ไม่ถูกต้อง';
            $errors = array_merge($errors, $rowErrors);
            return null;
        }

        if ($personType === self::PERSON_TYPE_INTERNAL_UNIT) {
            if (! $userId) {
                $userId = $this->resolveInternalUnitUserByName($name)?->id;
            }

            if (! $userId) {
                $rowErrors["copis.$idx.user_id"] = 'กรุณาเลือก COPI จากรายชื่อบุคลากร';
                $errors = array_merge($errors, $rowErrors);
                return null;
            }

            $user = User::query()->find($userId);
            if (! $user) {
                $rowErrors["copis.$idx.user_id"] = 'ไม่พบข้อมูลบุคลากรที่เลือก';
                $errors = array_merge($errors, $rowErrors);
                return null;
            }

            return [
                'user_id' => $user->id,
                'name' => $user->full_name,
                'affiliation' => self::PI_INTERNAL_AFFILIATION,
                'person_type' => $personType,
            ];
        }

        if ($name === '') {
            $rowErrors["copis.$idx.name"] = 'กรุณาระบุชื่อ COPI';
        }

        if ($affiliation === '') {
            $rowErrors["copis.$idx.affiliation"] = 'กรุณาระบุสังกัด COPI';
        }

        if ($rowErrors !== []) {
            $errors = array_merge($errors, $rowErrors);
            return null;
        }

        return [
            'user_id' => null,
            'name' => $name,
            'affiliation' => $affiliation,
            'person_type' => $personType,
        ];
    }

    private function syncIrbs(Request $request, Project $project): void
    {
        $ids = [];
        $unmatchedExisting = $project->irbs()->orderBy('id')->get()->keyBy('id');
        foreach ($request->irbs ?? [] as $irb) {
            if (! empty($irb['id'])) {
                $irbModel = $project->irbs()->whereKey($irb['id'])->first();
                if ($irbModel) {
                    $irbModel->update([
                        'code' => $irb['code'],
                        'exp_date' => $irb['exp_date'],
                        'source' => $irb['source'] ?? '',
                    ]);
                    $unmatchedExisting->forget($irbModel->id);
                    $ids[] = $irbModel->id;
                }
            } elseif (! empty($irb['code'])) {
                $fallback = $unmatchedExisting->shift();
                if ($fallback) {
                    $fallback->update([
                        'code' => $irb['code'],
                        'exp_date' => $irb['exp_date'],
                        'source' => $irb['source'] ?? '',
                    ]);
                    $ids[] = $fallback->id;
                    continue;
                }

                $new = \App\Models\Irb::create([
                    'project_id' => $project->id,
                    'code' => $irb['code'],
                    'exp_date' => $irb['exp_date'],
                    'source' => $irb['source'] ?? '',
                ]);
                $ids[] = $new->id;
            }
        }
        $staleIrbs = $project->irbs();
        if (!empty($ids)) {
            $staleIrbs->whereNotIn('id', $ids);
        }
        $this->deleteModelsWithEvents($staleIrbs);
    }

    private function syncCoResearch(Request $request, Project $project): void
    {
        $ids = [];
        $unmatchedExisting = $project->coResearches()->orderBy('id')->get()->keyBy('id');
        foreach ($request->coresearch ?? [] as $idx => $core) {
            $payload = $this->buildCoResearchPayload($core, $idx);
            if ($payload === null) {
                continue;
            }

            if (! empty($core['id'])) {
                $coreModel = $project->coResearches()->whereKey($core['id'])->first();
                if ($coreModel) {
                    $coreModel->update($payload);
                    $unmatchedExisting->forget($coreModel->id);
                    $ids[] = $coreModel->id;
                }
            } else {
                $fallback = $unmatchedExisting->shift();
                if ($fallback) {
                    $fallback->update($payload);
                    $ids[] = $fallback->id;
                    continue;
                }

                $new = \App\Models\CoResearch::create([
                    'project_id' => $project->id,
                    ...$payload,
                ]);
                $ids[] = $new->id;
            }
        }
        $staleCoResearches = $project->coResearches();
        if (!empty($ids)) {
            $staleCoResearches->whereNotIn('id', $ids);
        }
        $this->deleteModelsWithEvents($staleCoResearches);
    }

    private function buildCoResearchPayload(array $core, int $idx): ?array
    {
        $name = trim((string) ($core['name'] ?? ''));
        $affiliation = trim((string) ($core['affiliation'] ?? ''));
        $unitStatus = $this->normalizeCoResearchUnitStatus($core['unit_status'] ?? null);
        $userId = isset($core['user_id']) && $core['user_id'] !== '' ? (int) $core['user_id'] : null;

        $hasAnyValue = $name !== '' || $affiliation !== '' || $unitStatus !== '' || $userId !== null;
        if (! $hasAnyValue) {
            return null;
        }

        $errors = [];

        if ($unitStatus === '') {
            $errors["coresearch.$idx.unit_status"] = 'กรุณาเลือกสถานะ Co-Researcher';
        } elseif (! in_array($unitStatus, self::CO_RESEARCH_ALLOWED_UNIT_STATUSES, true)) {
            $errors["coresearch.$idx.unit_status"] = 'สถานะ Co-Researcher ไม่ถูกต้อง';
        }

        if ($unitStatus === self::CO_RESEARCH_INTERNAL_UNIT) {
            $user = $this->resolveCoResearchUser($userId, $name);

            if (! $user) {
                $errors["coresearch.$idx.user_id"] = 'กรุณาเลือกบุคลากรภายในหน่วยวิจัยจากรายการ';
            }

            if (! empty($errors)) {
                throw ValidationException::withMessages($errors);
            }

            return [
                'user_id' => $user->id,
                'name' => $user->full_name,
                'unit_status' => self::CO_RESEARCH_INTERNAL_UNIT,
                'affiliation' => self::CO_RESEARCH_INTERNAL_AFFILIATION,
            ];
        }

        if ($name === '') {
            $errors["coresearch.$idx.name"] = 'กรุณาระบุชื่อ Co-Researcher';
        }

        if ($affiliation === '') {
            $errors["coresearch.$idx.affiliation"] = 'กรุณาระบุสังกัด Co-Researcher';
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return [
            'user_id' => null,
            'name' => $name,
            'unit_status' => $unitStatus,
            'affiliation' => $affiliation,
        ];
    }

    private function normalizeCoResearchUnitStatus(?string $value): string
    {
        $normalized = trim((string) $value);

        if ($normalized === 'ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบาลสุขภาพ') {
            return self::CO_RESEARCH_INTERNAL_UNIT;
        }

        return $normalized;
    }

    private function resolveCoResearchUser(?int $userId, string $name): ?User
    {
        if ($userId) {
            $user = User::query()->find($userId);
            if ($user) {
                return $user;
            }
        }

        return $this->resolveInternalUnitUserByName($name);
    }

    private function deleteModelsWithEvents($query): void
    {
        $query->chunkById(200, function ($models) {
            $models->each->delete();
        });
    }

    private function touchProjectTimestamp(Project $project): void
    {
        $project->updated_by = Auth::id();
        if ($project->isDirty('updated_by')) {
            $project->save();
            return;
        }

        $project->touch();
    }

    private function applyProjectNameSearch(Builder $query, Builder $searchScope, string $rawSearch): void
    {
        $search = trim($rawSearch);
        if ($search === '') {
            return;
        }

        $searchLower = mb_strtolower($search, 'UTF-8');
        $searchNormalized = $this->normalizeSearchText($searchLower);
        $normalizedNeedle = $searchNormalized !== '' ? $searchNormalized : $searchLower;
        $searchNumericId = ctype_digit($search) ? (int) $search : null;
        $tokens = $this->tokenizeSearchTerms($searchLower);
        $fuzzyIds = $this->findFuzzyProjectIds($searchScope, $searchNormalized, $tokens);

        $nameExpr = "REGEXP_REPLACE(LOWER(TRIM(project_name_th)), '[[:space:][:punct:]]+', '')";
        $nameEnExpr = "REGEXP_REPLACE(LOWER(TRIM(COALESCE(project_name_en, ''))), '[[:space:][:punct:]]+', '')";
        $codeExpr = "REGEXP_REPLACE(LOWER(TRIM(project_code)), '[[:space:][:punct:]]+', '')";
        $personNameExpr = "REGEXP_REPLACE(LOWER(TRIM(COALESCE(name, ''))), '[[:space:][:punct:]]+', '')";
        $normalizedLike = '%'.$normalizedNeedle.'%';

        $query->where(function (Builder $inner) use ($search, $searchLower, $searchNormalized, $searchNumericId, $tokens, $fuzzyIds, $nameExpr, $nameEnExpr, $codeExpr, $personNameExpr) {
            $inner->where('project_name_th', 'like', '%'.$search.'%')
                ->orWhereRaw('LOWER(project_name_th) like ?', ['%'.$searchLower.'%'])
                ->orWhereRaw("LOWER(COALESCE(project_name_en, '')) like ?", ['%'.$searchLower.'%'])
                ->orWhereRaw('LOWER(project_code) like ?', ['%'.$searchLower.'%']);

            if ($searchNormalized !== '') {
                $inner->orWhereRaw("$nameExpr like ?", ['%'.$searchNormalized.'%'])
                    ->orWhereRaw("$nameEnExpr like ?", ['%'.$searchNormalized.'%'])
                    ->orWhereRaw("$codeExpr like ?", ['%'.$searchNormalized.'%']);
            }

            if ($searchNumericId !== null) {
                $inner->orWhere('id', $searchNumericId);
            }

            foreach ($tokens as $token) {
                $inner->orWhereRaw('LOWER(project_name_th) like ?', ['%'.$token.'%'])
                    ->orWhereRaw("LOWER(COALESCE(project_name_en, '')) like ?", ['%'.$token.'%']);
            }

            // ค้นหาชื่อบุคคลที่เกี่ยวข้องในโปรเจกต์
            $inner->orWhereHas('fteRoles', function (Builder $rel) use ($search, $searchLower, $searchNormalized, $tokens, $personNameExpr) {
                $rel->where('name', 'like', '%'.$search.'%')
                    ->orWhereRaw('LOWER(name) like ?', ['%'.$searchLower.'%']);

                if ($searchNormalized !== '') {
                    $rel->orWhereRaw("$personNameExpr like ?", ['%'.$searchNormalized.'%']);
                }

                foreach ($tokens as $token) {
                    $rel->orWhereRaw('LOWER(name) like ?', ['%'.$token.'%']);
                }
            });

            $inner->orWhereHas('copis', function (Builder $rel) use ($search, $searchLower, $searchNormalized, $tokens, $personNameExpr) {
                $rel->where('name', 'like', '%'.$search.'%')
                    ->orWhereRaw('LOWER(name) like ?', ['%'.$searchLower.'%']);

                if ($searchNormalized !== '') {
                    $rel->orWhereRaw("$personNameExpr like ?", ['%'.$searchNormalized.'%']);
                }

                foreach ($tokens as $token) {
                    $rel->orWhereRaw('LOWER(name) like ?', ['%'.$token.'%']);
                }
            });

            $inner->orWhereHas('coResearches', function (Builder $rel) use ($search, $searchLower, $searchNormalized, $tokens, $personNameExpr) {
                $rel->where('name', 'like', '%'.$search.'%')
                    ->orWhereRaw('LOWER(name) like ?', ['%'.$searchLower.'%']);

                if ($searchNormalized !== '') {
                    $rel->orWhereRaw("$personNameExpr like ?", ['%'.$searchNormalized.'%']);
                }

                foreach ($tokens as $token) {
                    $rel->orWhereRaw('LOWER(name) like ?', ['%'.$token.'%']);
                }
            });

            if ($fuzzyIds !== []) {
                $inner->orWhereIn('id', $fuzzyIds);
            }
        });

        $query->orderByRaw(
            "CASE
                WHEN LOWER(TRIM(project_code)) = ? THEN 0
                WHEN $codeExpr = ? THEN 1
                WHEN ? IS NOT NULL AND id = ? THEN 2
                WHEN LOWER(TRIM(project_name_th)) = ? THEN 3
                WHEN LOWER(TRIM(COALESCE(project_name_en, ''))) = ? THEN 4
                WHEN LOWER(project_code) LIKE ? THEN 5
                WHEN LOWER(project_name_th) LIKE ? THEN 6
                WHEN LOWER(COALESCE(project_name_en, '')) LIKE ? THEN 7
                WHEN $nameExpr LIKE ? THEN 8
                WHEN $nameEnExpr LIKE ? THEN 9
                WHEN project_name_th LIKE ? THEN 10
                ELSE 11
            END",
            [
                $searchLower,
                $normalizedNeedle,
                $searchNumericId,
                $searchNumericId,
                $searchLower,
                $searchLower,
                '%'.$searchLower.'%',
                '%'.$searchLower.'%',
                '%'.$searchLower.'%',
                $normalizedLike,
                $normalizedLike,
                '%'.$search.'%',
            ]
        );
    }

    private function findFuzzyProjectIds(Builder $scope, string $searchNormalized, array $tokens): array
    {
        if ($searchNormalized === '' && $tokens === []) {
            return [];
        }

        if ($searchNormalized !== '' && mb_strlen($searchNormalized, 'UTF-8') < 2) {
            return [];
        }

        $threshold = $searchNormalized !== '' && mb_strlen($searchNormalized, 'UTF-8') <= 4 ? 0.55 : 0.42;

        $candidates = (clone $scope)
            ->select(['id', 'project_name_th', 'project_name_en'])
            ->where(function (Builder $query) {
                $query->whereRaw("TRIM(COALESCE(project_name_th, '')) <> ''")
                    ->orWhereRaw("TRIM(COALESCE(project_name_en, '')) <> ''");
            })
            ->limit(1200)
            ->get();

        $scored = [];
        foreach ($candidates as $candidate) {
            $score = max(
                $this->calculateFuzzyScore($searchNormalized, $tokens, (string) $candidate->project_name_th),
                $this->calculateFuzzyScore($searchNormalized, $tokens, (string) $candidate->project_name_en)
            );
            if ($score >= $threshold) {
                $scored[] = [
                    'id' => (int) $candidate->id,
                    'score' => $score,
                ];
            }
        }

        usort($scored, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_values(array_unique(array_slice(array_column($scored, 'id'), 0, 80)));
    }

    private function findDuplicateProjectIds(Builder $scope): array
    {
        $candidates = (clone $scope)
            ->reorder()
            ->select(['id', 'project_name_th', 'project_code', 'start_year'])
            ->whereNotNull('project_name_th')
            ->whereRaw("TRIM(project_name_th) <> ''")
            ->get();

        if ($candidates->count() < 2) {
            return [];
        }

        $prepared = [];
        $exactGroups = [];

        foreach ($candidates as $candidate) {
            $rawName = trim((string) $candidate->project_name_th);
            $normalized = $this->normalizeSearchText($rawName);
            if ($normalized === '') {
                continue;
            }
            $years = $this->collectComparableProjectYears($candidate->start_year, $rawName);
            $primaryYear = $years[0] ?? '__unknown__';

            $prepared[] = [
                'id' => (int) $candidate->id,
                'raw' => $rawName,
                'normalized' => $normalized,
                'tokens' => $this->tokenizeSearchTerms($rawName),
                'years' => $years,
                'start_year' => $this->normalizeComparableYearValue($candidate->start_year),
            ];
            $exactGroups[$normalized.'|'.$primaryYear][] = (int) $candidate->id;
        }

        $duplicateIds = [];
        foreach ($exactGroups as $ids) {
            if (count($ids) < 2) {
                continue;
            }

            foreach ($ids as $id) {
                $duplicateIds[$id] = true;
            }
        }

        $remaining = array_values(array_filter($prepared, static function (array $item) use ($duplicateIds): bool {
            return !isset($duplicateIds[$item['id']]);
        }));

        $count = count($remaining);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if (!$this->areProjectNamesNearDuplicates($remaining[$i], $remaining[$j])) {
                    continue;
                }

                $duplicateIds[$remaining[$i]['id']] = true;
                $duplicateIds[$remaining[$j]['id']] = true;
            }
        }

        return array_map('intval', array_keys($duplicateIds));
    }

    private function areProjectNamesNearDuplicates(array $left, array $right): bool
    {
        $leftName = $left['normalized'] ?? '';
        $rightName = $right['normalized'] ?? '';
        $leftYears = array_values(array_filter((array) ($left['years'] ?? [])));
        $rightYears = array_values(array_filter((array) ($right['years'] ?? [])));

        if ($leftName === '' || $rightName === '') {
            return false;
        }

        // รายการชื่อใกล้เคียงแต่คนละปีไม่ถือว่าเป็นซ้ำ
        if ($this->yearsConflict($leftYears, $rightYears)) {
            return false;
        }

        if ($leftName === $rightName) {
            return true;
        }

        $leftLength = mb_strlen($leftName, 'UTF-8');
        $rightLength = mb_strlen($rightName, 'UTF-8');
        $minLength = min($leftLength, $rightLength);
        $maxLength = max($leftLength, $rightLength);

        if ($minLength < 12) {
            return false;
        }

        if (abs($leftLength - $rightLength) > max(6, (int) floor($maxLength * 0.18))) {
            return false;
        }

        $prefixLength = min(10, $minLength);
        $samePrefix = $prefixLength > 0
            && mb_substr($leftName, 0, $prefixLength, 'UTF-8') === mb_substr($rightName, 0, $prefixLength, 'UTF-8');

        $shorter = $leftLength <= $rightLength ? $leftName : $rightName;
        $longer = $leftLength <= $rightLength ? $rightName : $leftName;
        $containmentRatio = str_contains($longer, $shorter) ? ($minLength / max($maxLength, 1)) : 0.0;

        if ($containmentRatio >= 0.94) {
            return true;
        }

        $bigramScore = $this->ngramJaccardScore($leftName, $rightName, 2);
        $trigramScore = $this->ngramJaccardScore($leftName, $rightName, 3);

        if ($samePrefix && $bigramScore >= 0.92 && $trigramScore >= 0.84) {
            return true;
        }

        $leftTokens = $left['tokens'] ?? [];
        $rightTokens = $right['tokens'] ?? [];
        if ($leftTokens === [] || $rightTokens === []) {
            return false;
        }

        $tokenHits = count(array_intersect($leftTokens, $rightTokens));
        $tokenScore = $tokenHits / max(min(count($leftTokens), count($rightTokens)), 1);

        return $samePrefix
            && $tokenScore >= 0.8
            && $bigramScore >= 0.86
            && $trigramScore >= 0.72;
    }

    private function normalizeComparableYearValue($value): ?int
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '' || !preg_match('/(\d{4})/', $text, $match)) {
            return null;
        }

        $year = (int) $match[1];
        if ($year > 2400) {
            $year -= 543;
        }

        if ($year < 1900 || $year > 2600) {
            return null;
        }

        return $year;
    }

    private function extractComparableYearsFromText(string $text): array
    {
        preg_match_all('/\d{4}/', $text, $matches);
        $years = [];
        foreach (($matches[0] ?? []) as $token) {
            $year = $this->normalizeComparableYearValue($token);
            if ($year !== null) {
                $years[] = $year;
            }
        }

        return array_values(array_unique($years));
    }

    private function collectComparableProjectYears($startYear, string $name): array
    {
        $years = [];

        $normalizedStartYear = $this->normalizeComparableYearValue($startYear);
        if ($normalizedStartYear !== null) {
            $years[] = $normalizedStartYear;
        }

        $years = array_merge($years, $this->extractComparableYearsFromText($name));

        return array_values(array_unique(array_filter($years, static fn($year) => is_int($year))));
    }

    private function yearsConflict(array $leftYears, array $rightYears): bool
    {
        if ($leftYears === [] || $rightYears === []) {
            return false;
        }

        return count(array_intersect($leftYears, $rightYears)) === 0;
    }

    private function calculateFuzzyScore(string $needleNormalized, array $tokens, string $candidateName): float
    {
        $candidateLower = mb_strtolower(trim($candidateName), 'UTF-8');
        $candidateNormalized = $this->normalizeSearchText($candidateLower);

        if ($needleNormalized === '' || $candidateNormalized === '') {
            return 0.0;
        }

        if ($candidateNormalized === $needleNormalized) {
            return 1.0;
        }

        if (str_contains($candidateNormalized, $needleNormalized)) {
            return 0.92;
        }

        if (str_contains($needleNormalized, $candidateNormalized)) {
            return 0.75;
        }

        $tokenScore = 0.0;
        if ($tokens !== []) {
            $candidateTokens = $this->tokenizeSearchTerms($candidateLower);
            if ($candidateTokens !== []) {
                $tokenHits = count(array_intersect($tokens, $candidateTokens));
                $tokenScore = $tokenHits / max(count($tokens), 1);
            }
        }

        $bigramScore = $this->ngramJaccardScore($needleNormalized, $candidateNormalized, 2);
        $trigramScore = $this->ngramJaccardScore($needleNormalized, $candidateNormalized, 3);
        $prefix = mb_substr($needleNormalized, 0, min(3, mb_strlen($needleNormalized, 'UTF-8')), 'UTF-8');
        $prefixBonus = $prefix !== '' && str_starts_with($candidateNormalized, $prefix) ? 0.08 : 0.0;

        return min(1.0, ($bigramScore * 0.45) + ($trigramScore * 0.25) + ($tokenScore * 0.22) + $prefixBonus);
    }

    private function normalizeSearchText(string $value): string
    {
        $normalized = mb_strtolower(trim($value), 'UTF-8');
        $normalized = preg_replace('/\s+/u', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', '', $normalized) ?? $normalized;

        return $normalized;
    }

    private function tokenizeSearchTerms(string $value): array
    {
        $normalized = mb_strtolower(trim($value), 'UTF-8');
        if ($normalized === '') {
            return [];
        }

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_filter($tokens, static fn(string $token): bool => mb_strlen($token, 'UTF-8') >= 2);

        return array_values(array_unique($tokens));
    }

    private function ngramJaccardScore(string $left, string $right, int $size): float
    {
        $leftNgrams = $this->makeNgrams($left, $size);
        $rightNgrams = $this->makeNgrams($right, $size);

        if ($leftNgrams === [] || $rightNgrams === []) {
            return 0.0;
        }

        $leftSet = array_values(array_unique($leftNgrams));
        $rightSet = array_values(array_unique($rightNgrams));

        $intersection = count(array_intersect($leftSet, $rightSet));
        $union = count(array_unique(array_merge($leftSet, $rightSet)));

        return $union > 0 ? $intersection / $union : 0.0;
    }

    private function makeNgrams(string $value, int $size): array
    {
        $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $length = count($chars);

        if ($length === 0) {
            return [];
        }

        if ($length <= $size) {
            return [$value];
        }

        $ngrams = [];
        for ($i = 0; $i <= $length - $size; $i++) {
            $ngrams[] = implode('', array_slice($chars, $i, $size));
        }

        return $ngrams;
    }

    public function checkProjectName(Request $request)
    {
        $name = $request->input('project_name_th');

        $exact = Project::where('project_name_th', $name)->first();

        $similar = Project::where('project_name_th', 'LIKE', '%'.$name.'%')
            ->take(5)
            ->get(['project_name_th', 'pi_name', 'start_year'])
            ->map(function ($project) {
                return [
                    'project_name_th' => $project->project_name_th,
                    'pi_name' => $project->pi_name ?? 'ไม่ได้ลงข้อมูล',
                    'start_year' => $project->start_year ?? 'ไม่ได้ลงข้อมูล',
                ];
            });

        return response()->json([
            'exact' => $exact ? true : false,
            'similar' => $similar,
        ]);
    }
}

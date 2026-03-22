<?php

namespace App\Http\Controllers;

use App\Models\Output;
use App\Models\Project;
// ⬅️ เพิ่ม
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use PDF;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $view = 'projects.index';
        $forceView = $request->query('view');
        if ($forceView === 'mobile') {
            $view = 'projects.index_mobile';
        } elseif ($forceView !== 'desktop') {
            $ua = (string) $request->header('User-Agent', '');
            $isMobile = (bool) preg_match(
                '/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i',
                $ua
            );
            if ($isMobile) {
                $view = 'projects.index_mobile';
            }
        }

        $user = auth()->user();
        $applyPermission = function ($q) use ($user) {
            if ($user->role === 'admin' || $user->role === 'user_all') {
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

        $query = Project::query()->withCount('outputs');
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

        if ($issueOverdue || $issueMissingDates || $issueDuplicates) {
            $startDateExpr = $this->projectNormalizedDateSql('start_date');
            $endDateExpr = $this->projectNormalizedDateSql('end_date');
            $duplicateProjectIds = $issueDuplicates ? $this->findDuplicateProjectIds(clone $query) : [];

            $query->where(function ($issueQuery) use (
                $issueOverdue,
                $issueMissingDates,
                $issueDuplicates,
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
            'fundingSources' => $fundingSources,
            'typeOptions' => $typeOptions,
            'statusOptions' => $statusOptions,
            'yearOptions' => $yearOptions,
            'budgetMin' => $budgetMin,
            'budgetMax' => $budgetMax,
        ]);
    }

    public function create(Request $request)
    {
        $view = 'projects.create';
        $forceView = $request->query('view');
        if ($forceView === 'mobile') {
            $view = 'projects.create_mobile';
        } elseif ($forceView !== 'desktop') {
            $ua = (string) $request->header('User-Agent', '');
            $isMobile = (bool) preg_match(
                '/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i',
                $ua
            );
            if ($isMobile) {
                $view = 'projects.create_mobile';
            }
        }

        return view($view);
    }

public function store(Request $request)
{
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
        ->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
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
        $hasPermission = Auth::user()->role === 'admin'
            || Auth::id() === $project->user_id
            || Auth::user()->role === 'user_all'
            || $project->fteRoles->contains('name', $fullName);

        if (! $hasPermission) {
            abort(403);
        }

        if ($routeProjectParam !== '' && $canonicalProjectKey !== '' && $routeProjectParam !== $canonicalProjectKey) {
            return redirect()->route('projects.edit', array_merge(['project' => $project], $request->query()));
        }

        $project->load([
            'outputs.authors',
            'progressReports' => function ($query) {
                $query->orderByDesc('submit_date')->orderByDesc('id');
            },
        ]);

        $view = 'projects.edit';
        $forceView = $request->query('view');
        if ($forceView === 'mobile') {
            $view = 'projects.edit_mobile';
        } elseif ($forceView !== 'desktop') {
            $ua = (string) $request->header('User-Agent', '');
            $isMobile = (bool) preg_match(
                '/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i',
                $ua
            );
            if ($isMobile) {
                $view = 'projects.edit_mobile';
            }
        }

        return view($view, compact('project'));
    }

    public function show(Request $request, Project $project)
    {
        $routeProjectParam = trim((string) ($request->route()?->originalParameter('project') ?? ''));
        $canonicalProjectKey = (string) $project->getRouteKey();

        $view = 'projects.detail';
        $forceView = $request->query('view');
        if ($forceView === 'mobile') {
            $view = 'projects.detail_mobile';
        } elseif ($forceView !== 'desktop') {
            $ua = (string) $request->header('User-Agent', '');
            $isMobile = (bool) preg_match(
                '/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i',
                $ua
            );
            if ($isMobile) {
                $view = 'projects.detail_mobile';
            }
        }

        $fullName = trim(Auth::user()->prefix.' '.Auth::user()->name);
        $hasPermission = Auth::user()->role === 'admin'
            || Auth::id() === $project->user_id
            || $project->fteRoles->contains('name', $fullName)
            || Auth::user()->role === 'user_all';   // all user check

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
            'outputs.conferences',
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
        $hasPermission = Auth::user()->role === 'admin'
            || Auth::id() === $project->user_id
            || Auth::user()->role === 'user_all' // all user check
            || $project->fteRoles->contains('name', $fullName);

        if (! $hasPermission) {
            abort(403);
        }

        $validated = $this->validateProject($request);
        $this->validateOutputs($request);

        $project->fill($validated);
        $this->fillStrategies($request, $project);

        // ✅ กำกับตรงทุกครั้งที่มีการแก้ไข
        $project->updated_by = Auth::id();
        $project->save();

        $this->syncFTERoles($request, $project);
        $this->syncOutputs($request, $project);
        $this->syncCopis($request, $project);
        $this->syncIrbs($request, $project);
        $this->syncCoResearch($request, $project);

        return redirect()->route('projects.show', $project)
            ->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy(Project $project)
    {
        $fullName = trim(Auth::user()->prefix.' '.Auth::user()->name);

        $hasPermission = Auth::user()->role === 'admin'
            || Auth::id() === $project->user_id
            || $project->fteRoles->contains('name', $fullName);

        if (! $hasPermission) {
            abort(403);
        }

        // ลบความสัมพันธ์ที่เกี่ยวข้องก่อน (ถ้ามี foreign key)
        $project->fteRoles()->delete();
        $project->outputs()->delete();
        $project->copis()->delete();
        $project->irbs()->delete();
        $project->coResearches()->delete();

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
            'outputs.conferences',
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

        if ($user->role !== 'admin' && $user->role !== 'user_all') {
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
            return response()->streamDownload(function () use ($rows) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, ['Project ID', 'ชื่อโครงการ', 'PI', 'ปี', 'งบประมาณ(บาท)', 'ประเภททุน', 'แหล่งทุน', 'Project Type', 'Project Status', 'อัปเดตล่าสุด']);

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

            return $pdf->stream("{$baseName}.pdf");
        }

        $html = view('projects.list-export-xls', [
            'rows' => $rows,
            'periodLabel' => $periodLabel,
            'generatedAt' => now()->setTimezone('Asia/Bangkok'),
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$baseName}.xls\"",
        ]);
    }

    // ====== SUB-FUNCTIONS ======

    private function validateProject(Request $request): array
    {
        $validated = $request->validate([
            'project_name_th' => 'required|string',
            'project_name_en' => 'nullable|string',
            'project_type' => 'required|string|max:255',
            'theme' => 'required|string|max:255',
            'shortcut_link' => 'nullable|string',
            'r_code' => 'nullable|string',
            'size' => 'nullable|string|max:10',
            'funding_type' => 'nullable|string',
            'funding_source' => 'nullable|string',
            'funding_org_type' => 'nullable|string',
            'funding_name' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'start_year' => 'nullable|string',
            'team_start_date' => 'nullable|date',
            'team_end_date' => 'nullable|date',
            'pi_name' => 'nullable|string',
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
        ], [
            'planning_status.required' => 'กรุณาเลือก Project Status',
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

        if ($expectedVars > $maxInputVars) {
            $errors['project_name_th'] = "ข้อมูลฟอร์มมีจำนวนช่องมากเกินลิมิตระบบ (ประมาณ {$expectedVars} ช่อง > max_input_vars {$maxInputVars}) กรุณาเพิ่มค่า max_input_vars (แนะนำอย่างน้อย 10000)";
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
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
            $errors['end_date'] = 'BC103 - วันที่สิ้นสุด (ตามสัญญา) ต้องไม่น้อยกว่า BC102 - วันที่โครงการเริ่ม (ตามสัญญา)';
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
            'outputs.*.title' => ['nullable', 'string'],
            'outputs.*.project_output' => ['nullable', 'string'],
            'outputs.*.year' => ['nullable', 'string'],

            'outputs.*.journal' => ['nullable', 'string'],
            'outputs.*.quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'outputs.*.impact_factor' => ['nullable', 'numeric'],

            'outputs.*.authors' => ['nullable', 'array'],
            'outputs.*.authors.*.id' => ['nullable', 'integer', 'exists:output_authors,id'],
            'outputs.*.authors.*.role' => ['nullable', 'string'],
            'outputs.*.authors.*.name' => ['nullable', 'string'],

            // EA103 (Conferences)
            'outputs.*.conferences' => ['nullable', 'array'],
            'outputs.*.conferences.*.id' => ['nullable', 'integer', 'exists:output_conferences,id'],
            'outputs.*.conferences.*.name' => ['nullable', 'string'],
            'outputs.*.conferences.*.presentation_type' => ['nullable', 'string'],
            'outputs.*.conferences.*.location' => ['nullable', 'string'],
            'outputs.*.conferences.*.note' => ['nullable', 'string'],
        ]);
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
        $existingRows = $project->fteRoles()->orderBy('id')->get()->values();
        $existingById = $existingRows->keyBy('id');
        $deleteIds = collect((array) $request->input('fte_deleted_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();
        $deleteLookup = array_fill_keys($deleteIds->all(), true);

        $signatureBuckets = [];
        foreach ($existingRows as $candidate) {
            if (isset($deleteLookup[$candidate->id])) {
                continue;
            }
            $sig = $this->buildFteSignatureFromModel($candidate);
            if (! isset($signatureBuckets[$sig])) {
                $signatureBuckets[$sig] = [];
            }
            $signatureBuckets[$sig][] = $candidate->id;
        }

        $usedExistingIds = [];
        $explicitSubmittedIds = [];

        foreach (($request->fte_roles ?? []) as $fte) {
            $start = $this->parseProjectDateInput((string) ($fte['start_date'] ?? ''));
            $end = $this->parseProjectDateInput((string) ($fte['end_date'] ?? ''));

            $payload = [
                'role_group' => trim((string) ($fte['role_group'] ?? '')),
                'name' => trim((string) ($fte['name'] ?? '')) ?: null,
                'job_detail' => trim((string) ($fte['job_detail'] ?? '')) ?: null,
                'start_date' => $start?->format('d-m-Y'),
                'hours' => $fte['hours'] ?? null,
                'end_date' => $end?->format('d-m-Y'),
                'fte_value' => $fte['fte_value'] ?? 0,
                'fte_value_research' => $fte['fte_value_research'] ?? 0,
                'task_detail' => trim((string) ($fte['task_detail'] ?? '')) ?: null,
            ];

            // Skip empty rows.
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
                $fteRole = $existingById[$rowId];
                $fteRole->update($payload);
                $usedExistingIds[$fteRole->id] = true;
                $explicitSubmittedIds[$fteRole->id] = true;
                continue;
            }

            // Legacy support: old clients may submit without row id.
            $matched = null;
            $sig = $this->buildFteSignatureFromPayload($payload);
            $bucket = $signatureBuckets[$sig] ?? [];

            while (! empty($bucket)) {
                $candidateId = array_shift($bucket);
                if (isset($usedExistingIds[$candidateId])) {
                    continue;
                }
                if (isset($explicitSubmittedIds[$candidateId])) {
                    continue;
                }
                $matched = $existingById[$candidateId] ?? null;
                break;
            }
            $signatureBuckets[$sig] = $bucket;

            if (! $matched) {
                foreach ($existingRows as $candidate) {
                    if (isset($usedExistingIds[$candidate->id])) {
                        continue;
                    }
                    if (isset($explicitSubmittedIds[$candidate->id])) {
                        continue;
                    }
                    $samePerson = trim((string) $candidate->name) === (string) $payload['name'];
                    $sameRole = trim((string) $candidate->role_group) === (string) $payload['role_group'];
                    $sameJob = trim((string) $candidate->job_detail) === (string) $payload['job_detail'];
                    $sameTask = trim((string) $candidate->task_detail) === (string) ($payload['task_detail'] ?? '');
                    if ($samePerson && $sameRole && $sameJob && $sameTask) {
                        $matched = $candidate;
                        break;
                    }
                }
            }

            if ($matched) {
                $matched->update($payload);
                $usedExistingIds[$matched->id] = true;
            } elseif (! empty($payload['name'])) {
                $project->fteRoles()->create($payload);
            }
        }

        // Delete only rows explicitly marked as removed by the form.
        if ($deleteIds->isNotEmpty()) {
            $project->fteRoles()->whereIn('id', $deleteIds)->delete();
        }
    }

    private function buildFteSignatureFromModel($candidate): string
    {
        return implode('|', [
            $this->normalizeFteText($candidate->name),
            $this->normalizeFteText($candidate->role_group),
            $this->normalizeFteText($candidate->job_detail),
            $this->normalizeFteText($candidate->task_detail),
            (string) ($this->normalizeFteDate($candidate->start_date) ?? ''),
            (string) ($this->normalizeFteDate($candidate->end_date) ?? ''),
            number_format($this->normalizeFteNumber($candidate->hours), 8, '.', ''),
            number_format($this->normalizeFteNumber($candidate->fte_value), 8, '.', ''),
            number_format($this->normalizeFteNumber($candidate->fte_value_research), 8, '.', ''),
        ]);
    }

    private function buildFteSignatureFromPayload(array $payload): string
    {
        return implode('|', [
            $this->normalizeFteText($payload['name'] ?? null),
            $this->normalizeFteText($payload['role_group'] ?? null),
            $this->normalizeFteText($payload['job_detail'] ?? null),
            $this->normalizeFteText($payload['task_detail'] ?? null),
            (string) ($this->normalizeFteDate($payload['start_date'] ?? null) ?? ''),
            (string) ($this->normalizeFteDate($payload['end_date'] ?? null) ?? ''),
            number_format($this->normalizeFteNumber($payload['hours'] ?? null), 8, '.', ''),
            number_format($this->normalizeFteNumber($payload['fte_value'] ?? null), 8, '.', ''),
            number_format($this->normalizeFteNumber($payload['fte_value_research'] ?? null), 8, '.', ''),
        ]);
    }

    private function normalizeFteText($value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function normalizeFteDate($value): ?string
    {
        $parsed = $this->parseProjectDateInput((string) ($value ?? ''));
        return $parsed ? $parsed->format('d-m-Y') : null;
    }

    private function normalizeFteNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        return round((float) $value, 8);
    }

    private function syncOutputs(Request $request, Project $project): void
    {
        $rows = $request->input('outputs', []);
        $keepIds = [];

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
            ];

            $type = (string) ($payload['type'] ?? '');
            $isEA101 = str_starts_with($type, 'EA101');
            $isEA103 = str_starts_with($type, 'EA103');

            if ($isEA101) {
                $payload['journal'] = $row['journal'] ?? null;
                $payload['quartile'] = $this->normalizeQuartile($row['quartile'] ?? null);
                $payload['impact_factor'] = $row['impact_factor'] ?? null;
            }

            if (! empty($row['id'])) {
                $model = $project->outputs()->whereKey($row['id'])->first();
                if ($model) {
                    $model->update($payload);

                    // relations
                    $this->syncOutputAuthors($model, $row['authors'] ?? null, $isEA101);
                    $this->syncOutputConferences($model, $row['conferences'] ?? null, $isEA103);

                    $keepIds[] = $model->id;
                }
            } else {
                $new = $project->outputs()->create($payload);

                // relations
                $this->syncOutputAuthors($new, $row['authors'] ?? null, $isEA101);
                $this->syncOutputConferences($new, $row['conferences'] ?? null, $isEA103);

                $keepIds[] = $new->id;
            }
        }

        $project->outputs()
            ->when($keepIds, fn ($q) => $q->whereNotIn('id', $keepIds))
            ->delete();
    }

    private function syncOutputAuthors(Output $output, ?array $authors, bool $isEA101): void
    {
        if (! $isEA101) {
            $output->authors()->delete();

            return;
        }

        $authors = $authors ?? []; // ป้องกัน null

        if (! $authors) {
            $output->authors()->delete();

            return;
        }

        $ids = [];
        foreach ($authors as $author) {
            if (empty($author['name']) || empty($author['role'])) {
                continue;
            }

            $payload = [
                'role' => strtolower(trim($author['role'])),
                'name' => trim($author['name']),
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
            $output->authors()->whereNotIn('id', $ids)->delete();
        }
    }

    private function syncOutputConferences(Output $output, ?array $confs, bool $isEA103): void
    {
        if (! $isEA103) {
            $output->conferences()->delete();

            return;
        }
        if ($confs === null) {
            // ไม่ได้แตะ conferences -> ไม่ทำอะไร
            return;
        }

        $ids = [];
        foreach ($confs as $conf) {
            $payload = [
                'name' => $conf['name'] ?? null,
                'presentation_type' => $conf['presentation_type'] ?? null,
                'location' => $conf['location'] ?? null,
                'note' => $conf['note'] ?? null,
            ];

            // ว่างหมดก็ข้าม
            if (
                empty($payload['name']) &&
                empty($payload['presentation_type']) &&
                empty($payload['location']) &&
                empty($payload['note'])
            ) {
                continue;
            }

            if (! empty($conf['id'])) {
                $m = $output->conferences()->whereKey($conf['id'])->first();
                if ($m) {
                    $m->update($payload);
                    $ids[] = $m->id;
                }
            } else {
                $new = $output->conferences()->create($payload);
                $ids[] = $new->id;
            }
        }

        if (! empty($ids)) {
            $output->conferences()->whereNotIn('id', $ids)->delete();
        }
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
            (! empty($row['authors']))
        );
    }

    private function normalizeQuartile(?string $q): ?string
    {
        if (! $q) {
            return null;
        }
        $q = strtoupper(trim($q));
        if (in_array($q, ['Q1', 'Q2', 'Q3', 'Q4'], true)) {
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
        foreach ($request->copis ?? [] as $copi) {
            if (! empty($copi['id'])) {
                $copiModel = \App\Models\Copi::find($copi['id']);
                if ($copiModel) {
                    $copiModel->update([
                        'name' => $copi['name'],
                        'affiliation' => $copi['affiliation'] ?? '',
                    ]);
                    $ids[] = $copiModel->id;
                }
            } elseif (! empty($copi['name'])) {
                $new = \App\Models\Copi::create([
                    'project_id' => $project->id,
                    'name' => $copi['name'],
                    'affiliation' => $copi['affiliation'] ?? '',
                ]);
                $ids[] = $new->id;
            }
        }
        $project->copis()->whereNotIn('id', $ids)->delete();
    }

    private function syncIrbs(Request $request, Project $project): void
    {
        $ids = [];
        foreach ($request->irbs ?? [] as $irb) {
            if (! empty($irb['id'])) {
                $irbModel = \App\Models\Irb::find($irb['id']);
                if ($irbModel) {
                    $irbModel->update([
                        'code' => $irb['code'],
                        'exp_date' => $irb['exp_date'],
                        'source' => $irb['source'] ?? '',
                    ]);
                    $ids[] = $irbModel->id;
                }
            } elseif (! empty($irb['code'])) {
                $new = \App\Models\Irb::create([
                    'project_id' => $project->id,
                    'code' => $irb['code'],
                    'exp_date' => $irb['exp_date'],
                    'source' => $irb['source'] ?? '',
                ]);
                $ids[] = $new->id;
            }
        }
        $project->irbs()->whereNotIn('id', $ids)->delete();
    }

    private function syncCoResearch(Request $request, Project $project): void
    {
        $ids = [];
        foreach ($request->coresearch ?? [] as $core) {
            if (! empty($core['id'])) {
                $coreModel = \App\Models\CoResearch::find($core['id']);
                if ($coreModel) {
                    $coreModel->update([
                        'name' => $core['name'],
                        'unit_status' => $core['unit_status'],
                        'affiliation' => $core['affiliation'] ?? '',
                    ]);
                    $ids[] = $coreModel->id;
                }
            } elseif (! empty($core['name'])) {
                $new = \App\Models\CoResearch::create([
                    'project_id' => $project->id,
                    'name' => $core['name'],
                    'unit_status' => $core['unit_status'],
                    'affiliation' => $core['affiliation'] ?? '',
                ]);
                $ids[] = $new->id;
            }
        }
        $project->coResearches()->whereNotIn('id', $ids)->delete();
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
        $codeExpr = "REGEXP_REPLACE(LOWER(TRIM(project_code)), '[[:space:][:punct:]]+', '')";
        $normalizedLike = '%'.$normalizedNeedle.'%';

        $query->where(function (Builder $inner) use ($search, $searchLower, $searchNormalized, $searchNumericId, $tokens, $fuzzyIds, $nameExpr, $codeExpr) {
            $inner->where('project_name_th', 'like', '%'.$search.'%')
                ->orWhereRaw('LOWER(project_name_th) like ?', ['%'.$searchLower.'%'])
                ->orWhereRaw('LOWER(project_code) like ?', ['%'.$searchLower.'%']);

            if ($searchNormalized !== '') {
                $inner->orWhereRaw("$nameExpr like ?", ['%'.$searchNormalized.'%'])
                    ->orWhereRaw("$codeExpr like ?", ['%'.$searchNormalized.'%']);
            }

            if ($searchNumericId !== null) {
                $inner->orWhere('id', $searchNumericId);
            }

            foreach ($tokens as $token) {
                $inner->orWhereRaw('LOWER(project_name_th) like ?', ['%'.$token.'%']);
            }

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
                WHEN LOWER(project_code) LIKE ? THEN 4
                WHEN LOWER(project_name_th) LIKE ? THEN 5
                WHEN $nameExpr LIKE ? THEN 6
                WHEN project_name_th LIKE ? THEN 7
                ELSE 8
            END",
            [
                $searchLower,
                $normalizedNeedle,
                $searchNumericId,
                $searchNumericId,
                $searchLower,
                '%'.$searchLower.'%',
                '%'.$searchLower.'%',
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
            ->select(['id', 'project_name_th'])
            ->whereNotNull('project_name_th')
            ->whereRaw("TRIM(project_name_th) <> ''")
            ->limit(1200)
            ->get();

        $scored = [];
        foreach ($candidates as $candidate) {
            $score = $this->calculateFuzzyScore($searchNormalized, $tokens, (string) $candidate->project_name_th);
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
            ->select(['id', 'project_name_th'])
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

            $prepared[] = [
                'id' => (int) $candidate->id,
                'raw' => $rawName,
                'normalized' => $normalized,
                'tokens' => $this->tokenizeSearchTerms($rawName),
            ];
            $exactGroups[$normalized][] = (int) $candidate->id;
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

        if ($leftName === '' || $rightName === '') {
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

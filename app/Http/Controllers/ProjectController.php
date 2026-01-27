<?php

namespace App\Http\Controllers;

use App\Models\Output;
use App\Models\Project;
// ⬅️ เพิ่ม
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('project_name_th', 'like', '%'.$search.'%');
            $query->orderByRaw(
                "case when project_name_th = ? then 0 when project_name_th like ? then 1 else 2 end",
                [$search, '%'.$search.'%']
            );
        }
        if ($request->filled('pi_name')) {
            $query->whereRaw('TRIM(pi_name) = ?', [$request->pi_name]);
        }
        if ($request->filled('funding_source')) {
            $query->whereRaw('TRIM(funding_source) = ?', [$request->funding_source]);
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
        if ($request->boolean('issue_overdue')) {
            $query->whereNotNull('end_date')->whereDate('end_date', '<', now()->toDateString());
        }
        if ($request->boolean('issue_missing_dates')) {
            $query->where(function ($q) {
                $q->whereNull('start_date')->orWhereNull('end_date');
            });
        }
        if ($request->boolean('issue_duplicates')) {
            $nameExpr = "REGEXP_REPLACE(LOWER(TRIM(project_name_th)),'\\\\s+','')";
            $duplicateNames = (clone $baseQuery)
                ->select(DB::raw("$nameExpr as name_norm"))
                ->whereNotNull('project_name_th')
                ->whereRaw("TRIM(project_name_th) <> ''")
                ->groupBy('name_norm')
                ->havingRaw('count(*) > 1');
            $query->whereIn(DB::raw($nameExpr), $duplicateNames);
        }
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

        // sort
        $sortable = ['id', 'project_code', 'project_name_th', 'project_type', 'funding_type', 'theme', 'start_year', 'updated_at', 'created_at', 'budget'];
        $sort = in_array($request->get('sort'), $sortable, true) ? $request->get('sort') : 'updated_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

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

        // 🔐 lock sequence row (atomic 100%)
        $sequence = DB::table('project_sequences')
            ->where('prefix', 'SP')
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            throw new \Exception('Project sequence SP not found');
        }

        $nextNumber = $sequence->current_number + 1;

        DB::table('project_sequences')
            ->where('id', $sequence->id)
            ->update([
                'current_number' => $nextNumber,
                'updated_at' => now(),
            ]);

        $validated['project_code'] = 'SP_'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
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
        ->route('projects.show', $project->id)
        ->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
}

    public function edit(Request $request, $id)
    {
        $project = Project::with('outputs.authors')->find($id);

        if (! $project) {
            return redirect('/rm')->with('error', 'ไม่พบข้อมูลโครงการ');
        }

        $fullName = trim(Auth::user()->prefix.' '.Auth::user()->name);
        $hasPermission = Auth::user()->role === 'admin'
            || Auth::id() === $project->user_id
            || Auth::user()->role === 'user_all'
            || $project->fteRoles->contains('name', $fullName);

        if (! $hasPermission) {
            abort(403);
        }

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

        $project->load([
            'copis',
            'irbs',
            'fteRoles',
            'outputs.authors',
            'outputs.conferences',
            'updatedBy',
            'createdBy',   // ✅ เพิ่มตรงนี้
        ]);

        $fteChartData = $this->buildProjectFteChartData($project);

        return view($view, array_merge(compact('project'), $fteChartData));
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
        $project->load(['copis', 'irbs', 'fteRoles', 'outputs.authors', 'updatedBy', 'createdBy']); // ⬅️ authors

        $user = Auth::user();
        $author = $project->createdBy ? ($project->createdBy->prefix.$project->createdBy->name) : '';
        $creater = $user ? ($user->prefix.$user->name) : '';
        $dateStr = \Carbon\Carbon::now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
        $filename = "{$dateStr}_{$creater}_{$project->project_name_th}.pdf";

        $fteChartData = $this->buildProjectFteChartData($project);
        $pdf = PDF::loadView('projects.pdf', array_merge(compact('project', 'user'), $fteChartData));

        return $pdf->stream($filename);
    }

    // ====== SUB-FUNCTIONS ======

    private function validateProject(Request $request): array
    {
        return $request->validate([
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
            'planning_status' => 'nullable|string',
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
        ]);
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
        $ids = [];

        foreach ($request->fte_roles ?? [] as $fte) {
            $payload = [
                'role_group' => $fte['role_group'] ?? '',
                'name' => $fte['name'] ?? null,
                'job_detail' => $fte['job_detail'] ?? null,
                'start_date' => $fte['start_date'] ?? null,
                'hours' => $fte['hours'] ?? null,
                'end_date' => $fte['end_date'] ?? null,
                'fte_value' => $fte['fte_value'] ?? 0,
                'fte_value_research' => $fte['fte_value_research'] ?? 0,
                'task_detail' => $fte['task_detail'] ?? null,
            ];

            if (! empty($fte['id'])) {
                $fteRole = $project->fteRoles()->whereKey($fte['id'])->first();
                if ($fteRole) {
                    $fteRole->update($payload);
                    $ids[] = $fteRole->id;
                }
            } elseif (! empty($fte['name'])) {
                $new = $project->fteRoles()->create($payload);
                $ids[] = $new->id;
            }
        }

        $project->fteRoles()->whereNotIn('id', $ids)->delete();
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FteRole;
use App\Models\Project;
use App\Models\BackOfficeEntry;
use App\Models\BackOfficeTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use PDF;


class FteController extends Controller
{
private function isFteAdmin(): bool
{
    return Auth::user()?->role === 'admin';
}

private function currentUserFullName(): string
{
    $user = Auth::user();
    return trim(($user?->prefix ?? '') . ' ' . ($user?->name ?? ''));
}

private function ensureCanViewFtePerson(string $person): void
{
    if ($this->isFteAdmin()) {
        return;
    }

    abort_unless(trim($person) === $this->currentUserFullName(), 403);
}

private function ensureCanViewFteProject(Project $project): void
{
    if ($this->isFteAdmin()) {
        return;
    }

    $fullName = $this->currentUserFullName();
    $allowed = $project->fteRoles()->where('name', $fullName)->exists();

    abort_unless($allowed, 403);
}

public function index(Request $request)
{
    $user = Auth::user();
    $fullName = $this->currentUserFullName();

    $modeInput = trim((string) $request->input('mode', ''));
    $yearInput = $request->input('year');
    $person = $request->input('person');
    $start  = $request->input('start');
    $end    = $request->input('end');

    // Project สิทธิ์
    $projectQuery = Project::query();
    if (! $this->isFteAdmin()) {
        $projectQuery->whereHas('fteRoles', function ($subQ) use ($fullName) {
            $subQ->where('name', $fullName);
        });
    }
    $projectIds = $projectQuery->pluck('id');

    // FTE Query base
    $fteQuery = FteRole::query()
        ->select([
            'id',
            'project_id',
            'name',
            'fte_value',
            'hours',
            'start_date',
            'end_date',
            'role_group',
            'job_detail',
            'task_detail'
        ])
        ->whereIn('project_id', $projectIds);

    if (! $this->isFteAdmin()) {
        $fteQuery->where('name', $fullName);
    }

    $backOfficeQuery = BackOfficeEntry::query()
        ->select([
            'id',
            'task_id',
            'person_name',
            'category',
            'job_detail',
            'hours',
            'fte_value',
        ])
        ->with(['task:id,title,start_date,end_date,description,created_by']);

    if (! $this->isFteAdmin()) {
        $backOfficeQuery->where(function ($q) use ($fullName, $user) {
            $q->where('person_name', $fullName)
                ->orWhereHas('task', function ($taskQ) use ($user) {
                    $taskQ->where('created_by', $user->id);
                });
        });
    }

    $fteStartYearExpr = 'YEAR(' . $this->parsedDateExpression('start_date') . ')';
    $backOfficeTaskStartYearExpr = 'YEAR(' . $this->parsedDateExpression('start_date') . ')';

    $fteYears = FteRole::query()
        ->whereIn('project_id', $projectIds)
        ->selectRaw("DISTINCT {$fteStartYearExpr} AS year")
        ->pluck('year');

    $backOfficeYearsQuery = BackOfficeTask::query();
    if (! $this->isFteAdmin()) {
        $backOfficeYearsQuery->where(function ($q) use ($fullName, $user) {
            $q->where('created_by', $user->id)
                ->orWhereHas('entries', function ($entryQ) use ($fullName) {
                    $entryQ->where('person_name', $fullName);
                });
        });
    }
    $backOfficeYears = $backOfficeYearsQuery
        ->selectRaw("DISTINCT {$backOfficeTaskStartYearExpr} AS year")
        ->pluck('year');

    $availableYears = $fteYears
        ->merge($backOfficeYears)
        ->filter()
        ->map(fn($y) => (int) $y)
        ->filter()
        ->unique()
        ->sortDesc()
        ->values();

    $defaultYearBE = $availableYears->isNotEmpty() ? $availableYears->first() + 543 : now()->year + 543;
    $mode = $modeInput !== '' ? $modeInput : ($availableYears->isNotEmpty() ? 'year' : 'month');
    $year = filled($yearInput) ? $yearInput : ($mode === 'year' ? $defaultYearBE : null);

    // ========== FILTER ==========
    $filterFrom = null;
    $filterTo = null;

    // รายปี
    if ($mode === 'year' && $year) {
        $yearAD = $year > 2500 ? $year - 543 : $year;
        $filterFrom = Carbon::createFromDate($yearAD, 1, 1)->startOfDay();
        $filterTo = Carbon::createFromDate($yearAD, 12, 31)->endOfDay();
    }

    // รายเดือน (เดือนนี้)
    if ($mode === 'month') {
        $now = now();
        $filterFrom = $now->copy()->startOfMonth()->startOfDay();
        $filterTo = $now->copy()->endOfMonth()->endOfDay();
    }

    // custom range
    if ($mode === 'custom' && $start && $end) {
        [$customFrom, $customTo] = $this->parseDateRange($start, $end);
        if ($customFrom && $customTo) {
            $filterFrom = $customFrom;
            $filterTo = $customTo;
            $start = $customFrom->format('d-m-Y');
            $end = $customTo->format('d-m-Y');
        }
    }

    if ($filterFrom && $filterTo) {
        $this->applyDateOverlapFilter($fteQuery, 'start_date', 'end_date', $filterFrom, $filterTo);

        // ใช้วันที่จากรายการหรือวันที่ของงานหลัก (task) เพื่อไม่ตกหล่นข้อมูลที่ไม่ได้กรอกวันที่ใน entry
        $backOfficeQuery->where(function ($q) use ($filterFrom, $filterTo) {
            $this->applyDateOverlapFilter($q, 'start_date', 'end_date', $filterFrom, $filterTo);
            $q->orWhereHas('task', function ($taskQ) use ($filterFrom, $filterTo) {
                $this->applyDateOverlapFilter($taskQ, 'start_date', 'end_date', $filterFrom, $filterTo);
            });
        });
    }

    if ($person) {
        $fteQuery->where('name', $person);
        $backOfficeQuery->where('person_name', $person);
    }

    $fteRows = $fteQuery->with(['project:id,project_name_th'])->get();

    $backOfficeRows = $backOfficeQuery->get()->map(function ($entry) {
        $task = $entry->task;
        $startDate = $entry->start_date ?: ($task?->start_date);
        $endDate = $entry->end_date ?: ($task?->end_date);
        $category = $this->backOfficeCategoryLabel($entry->category);
        return (object) [
            'id' => 'bo-' . $entry->id,
            'task_id' => $task?->id,
            'project_id' => null,
            'name' => $entry->person_name,
            'fte_value' => $entry->fte_value,
            'hours' => $entry->hours,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'role_group' => $category,
            'job_detail' => $entry->job_detail,
            'task_detail' => $task?->title,
            'project' => (object) [
                'id' => null,
                'project_name_th' => 'งานนอกโครงการ',
            ],
        ];
    });

    $fteRows = $fteRows->concat($backOfficeRows);

    // ==== Profile Map ====
    $users = \App\Models\User::select('prefix', 'name', 'profile_photo_path')->get();
    $profiles = $users->mapWithKeys(function ($user) {
        $full = trim($user->prefix . ' ' . $user->name);
        return [$full => $user->profile_photo_path];
    });

    // ============================
    // Group by: name → month
    // ============================

    $customFilterFrom = $mode === 'custom' ? $filterFrom : null;
    $customFilterTo = $mode === 'custom' ? $filterTo : null;

    $monthlyFTE = $fteRows->groupBy('name')->map(function ($rows) use ($mode, $year, $customFilterFrom, $customFilterTo) {

        $expanded = collect();

        foreach ($rows as $item) {
            if (empty($item->start_date) || empty($item->end_date)) continue;

            try {
                [$startDate, $endDate] = $this->parseStoredDateBounds($item->start_date, $item->end_date);
                if (! $startDate || ! $endDate) continue;

                if ($startDate->gt($endDate)) continue;

                $totalDays = $this->inclusiveDateOnlyDays($startDate, $endDate);
                if ($totalDays <= 0) continue;

                $ftePerDay = $item->fte_value / $totalDays;
                $hoursPerDay = $item->hours ? ((float) $item->hours / $totalDays) : 0;

                $effectiveStart = $startDate->copy();
                $effectiveEnd   = $endDate->copy();

                // custom
                if ($mode === 'custom' && $customFilterFrom && $customFilterTo) {
                    if ($endDate < $customFilterFrom || $startDate > $customFilterTo) continue;
                    if ($effectiveStart < $customFilterFrom) $effectiveStart = $customFilterFrom->copy();
                    if ($effectiveEnd > $customFilterTo)     $effectiveEnd = $customFilterTo->copy();
                }

                // year filter
                if ($mode === 'year' && $year) {
                    $yearAD = $year > 2500 ? $year - 543 : $year;
                    $from = Carbon::createFromDate($yearAD, 1, 1)->startOfDay();
                    $to   = Carbon::createFromDate($yearAD, 12, 31)->endOfDay();

                    if ($endDate < $from || $startDate > $to) continue;
                    if ($effectiveStart < $from) $effectiveStart = $from;
                    if ($effectiveEnd > $to)     $effectiveEnd = $to;
                }

                // month filter
                if ($mode === 'month') {
                    $now = now();
                    $mStart = $now->copy()->startOfMonth()->startOfDay();
                    $mEnd   = $now->copy()->endOfMonth()->endOfDay();

                    if ($endDate < $mStart || $startDate > $mEnd) continue;
                    if ($effectiveStart < $mStart) $effectiveStart = $mStart;
                    if ($effectiveEnd > $mEnd)     $effectiveEnd = $mEnd;
                }

                if ($effectiveStart->gt($effectiveEnd)) continue;

                // === split by month ===
                $cursor = $effectiveStart->copy()->startOfMonth();
                while ($cursor <= $effectiveEnd) {

                    $monthStart = $cursor->copy()->startOfMonth();
                    $monthEnd   = $cursor->copy()->endOfMonth();

                    // FIX: ใช้ date-only และ startOfDay เฉพาะ
                    $overlapStart = $effectiveStart->copy()->startOfDay()->max($monthStart->copy()->startOfDay());
                    $overlapEnd   = $effectiveEnd->copy()->startOfDay()->min($monthEnd->copy()->startOfDay());

                    if ($overlapStart->lte($overlapEnd)) {
                        $daysInMonth = $this->inclusiveDateOnlyDays($overlapStart, $overlapEnd);
                        $fteMonth = $daysInMonth * $ftePerDay;
                        $hoursInMonth = $hoursPerDay * $daysInMonth;

                        $expanded->push((object)[
                            'rounding_key'  => 'person-index:' . $item->id,
                            'month'         => $cursor->format('Y-m'),
                            'project'       => $item->project,
                            'role_group'    => $item->role_group,
                            'job_detail'    => $item->job_detail,
                            'fte_month'     => $fteMonth,
                            'fte_value'     => $item->fte_value,
                            'hours_in_month'=> $hoursInMonth,
                            'days_in_month' => $daysInMonth,
                            'total_days'    => $totalDays,
                            'fte_per_day'   => $ftePerDay,
                            'overlap_start' => $overlapStart,
                            'overlap_end'   => $overlapEnd,
                            'start_date'    => $item->start_date,
                            'end_date'      => $item->end_date,
                            'task_detail'   => $item->task_detail,
                            'task_id'       => $item->task_id ?? ($item->task?->id ?? null),
                        ]);
                    }

                    $cursor->addMonth();
                }

            } catch (\Exception $e) {}
        }

        $expanded = $this->rebalancePersonIndexDisplayValues($expanded);
        return $expanded->groupBy('month')->sortKeys(); // IMPORTANT
    });

    // ===== Summary =====
    $fteSummary = $monthlyFTE->map(function ($months, $name) {
        $flatten = $months->flatten();
        $total = $flatten->sum('fte_month');

        $thisMonthKey = now()->format('Y-m');
        $thisMonth = $months[$thisMonthKey] ?? collect();
        $fteThisMonth = $thisMonth->sum('fte_month');

        return (object)[
            'name'           => $name,
            'total_fte'      => $total,
            'fte_this_month' => $fteThisMonth,
        ];
    })->sortByDesc('total_fte')->values();

    $years = $availableYears->map(fn($y) => $y + 543);

    $viewData = [
        'fteSummary' => $fteSummary,
        'years'      => $years,
        'monthlyFTE' => $monthlyFTE,
        'profiles'   => $profiles,
        'mode'       => $mode,
        'year'       => $year,
        'person'     => $person,
        'start'      => $start,
        'end'        => $end,
    ];

    if ($request->boolean('partial') || $request->ajax()) {
        return response()->json([
            'html' => view('fte.partials.index-table', $viewData)->render(),
            'count' => $fteSummary->count(),
        ]);
    }

    return view('fte.index', $viewData);
}

public function projectIndex(Request $request)
{
    $user = Auth::user();
    $fullName = $this->currentUserFullName();

    $modeInput = trim((string) $request->input('mode', ''));
    $yearInput = $request->input('year');
    $projectId = $request->input('project_id');
    $start = $request->input('start');
    $end = $request->input('end');

    $projectQuery = Project::query();
    if (! $this->isFteAdmin()) {
        $projectQuery->whereHas('fteRoles', function ($subQ) use ($fullName) {
            $subQ->where('name', $fullName);
        });
    }
    $projectList = $projectQuery
        ->orderBy('project_name_th')
        ->get(['id', 'project_name_th', 'start_year']);
    $projectIds = $projectList->pluck('id');
    $projectOptions = $projectList;
    $yearSourceProjectIds = filled($projectId) ? collect([(int) $projectId]) : $projectIds;
    $fteStartYearExpr = 'YEAR(' . $this->parsedDateExpression('start_date') . ')';
    $availableYears = FteRole::query()
        ->whereIn('project_id', $yearSourceProjectIds)
        ->selectRaw("DISTINCT {$fteStartYearExpr} AS year")
        ->pluck('year')
        ->filter()
        ->map(fn($y) => (int) $y)
        ->filter()
        ->unique()
        ->sortDesc()
        ->values();

    $defaultYearBE = $availableYears->isNotEmpty() ? $availableYears->first() + 543 : now()->year + 543;
    $mode = $modeInput !== '' ? $modeInput : ($availableYears->isNotEmpty() ? 'year' : 'month');
    $year = filled($yearInput) ? $yearInput : ($mode === 'year' ? $defaultYearBE : null);

    $totalFteMap = [];
    $allRows = FteRole::whereIn('project_id', $projectIds);
    if (! $this->isFteAdmin()) {
        $allRows->where('name', $fullName);
    }
    $allRows = $allRows->get(['project_id', 'fte_value', 'start_date', 'end_date', 'hours']);
    foreach ($allRows as $row) {
        if (empty($row->start_date) || empty($row->end_date)) {
            continue;
        }
        try {
            [$startDate, $endDate] = $this->parseStoredDateBounds($row->start_date, $row->end_date);
            if (! $startDate || ! $endDate) {
                continue;
            }
            if ($startDate->gt($endDate)) {
                continue;
            }
            $totalDays = $this->inclusiveDateOnlyDays($startDate, $endDate);
            if ($totalDays <= 0) {
                continue;
            }
            $ftePerDay = $row->fte_value / $totalDays;
            $cursor = $startDate->copy()->startOfMonth();
            while ($cursor <= $endDate) {
                $mStart = $cursor->copy()->startOfMonth();
                $mEnd = $cursor->copy()->endOfMonth();
                $overlapStart = $startDate->copy()->max($mStart);
                $overlapEnd = $endDate->copy()->min($mEnd);
                if ($overlapStart->lte($overlapEnd)) {
                    $daysInMonth = $this->inclusiveDateOnlyDays($overlapStart, $overlapEnd);
                    $fteMonth = $daysInMonth * $ftePerDay;
                    $totalFteMap[$row->project_id] = ($totalFteMap[$row->project_id] ?? 0) + $fteMonth;
                }
                $cursor->addMonth();
            }
        } catch (\Exception $e) {}
    }

    $fteQuery = FteRole::query()
        ->select([
            'id',
            'project_id',
            'name',
            'fte_value',
            'start_date',
            'end_date',
            'role_group',
            'job_detail',
            'task_detail',
            'hours'
        ])
        ->whereIn('project_id', $projectIds);

    if (! $this->isFteAdmin()) {
        $fteQuery->where('name', $fullName);
    }

    if ($projectId) {
        $fteQuery->where('project_id', $projectId);
    }

    $filterFrom = null;
    $filterTo = null;

    if ($mode === 'year' && $year) {
        $yearAD = $year > 2500 ? $year - 543 : $year;
        $filterFrom = Carbon::createFromDate($yearAD, 1, 1)->startOfDay();
        $filterTo = Carbon::createFromDate($yearAD, 12, 31)->endOfDay();
    }

    if ($mode === 'month') {
        $now = now();
        $filterFrom = $now->copy()->startOfMonth()->startOfDay();
        $filterTo = $now->copy()->endOfMonth()->endOfDay();
    }

    if ($mode === 'custom' && $start && $end) {
        [$customFrom, $customTo] = $this->parseDateRange($start, $end);
        if ($customFrom && $customTo) {
            $filterFrom = $customFrom;
            $filterTo = $customTo;
            $start = $customFrom->format('d-m-Y');
            $end = $customTo->format('d-m-Y');
        }
    }

    if ($filterFrom && $filterTo) {
        $this->applyDateOverlapFilter($fteQuery, 'start_date', 'end_date', $filterFrom, $filterTo);
    }

    $fteRows = $fteQuery->with(['project:id,project_name_th,start_year'])->get();
    $projectMap = $projectList->keyBy('id');

    $customFilterFrom = $mode === 'custom' ? $filterFrom : null;
    $customFilterTo = $mode === 'custom' ? $filterTo : null;

    $monthlyFTE = $fteRows->groupBy('project_id')->map(function ($rows) use ($mode, $year, $customFilterFrom, $customFilterTo) {
        $expanded = collect();

        foreach ($rows as $item) {
            if (empty($item->start_date) || empty($item->end_date)) continue;

            try {
                [$startDate, $endDate] = $this->parseStoredDateBounds($item->start_date, $item->end_date);
                if (! $startDate || ! $endDate) continue;

                if ($startDate->gt($endDate)) continue;

                $totalDays = $this->inclusiveDateOnlyDays($startDate, $endDate);
                if ($totalDays <= 0) continue;

                $ftePerDay = $item->fte_value / $totalDays;
                $hoursPerDay = $item->hours ? ((float) $item->hours / $totalDays) : 0;

                $effectiveStart = $startDate->copy();
                $effectiveEnd = $endDate->copy();

                if ($mode === 'custom' && $customFilterFrom && $customFilterTo) {
                    if ($endDate < $customFilterFrom || $startDate > $customFilterTo) continue;
                    if ($effectiveStart < $customFilterFrom) $effectiveStart = $customFilterFrom->copy();
                    if ($effectiveEnd > $customFilterTo) $effectiveEnd = $customFilterTo->copy();
                }

                if ($mode === 'year' && $year) {
                    $yearAD = $year > 2500 ? $year - 543 : $year;
                    $from = Carbon::createFromDate($yearAD, 1, 1)->startOfDay();
                    $to = Carbon::createFromDate($yearAD, 12, 31)->endOfDay();

                    if ($endDate < $from || $startDate > $to) continue;
                    if ($effectiveStart < $from) $effectiveStart = $from;
                    if ($effectiveEnd > $to) $effectiveEnd = $to;
                }

                if ($mode === 'month') {
                    $now = now();
                    $mStart = $now->copy()->startOfMonth()->startOfDay();
                    $mEnd = $now->copy()->endOfMonth()->endOfDay();

                    if ($endDate < $mStart || $startDate > $mEnd) continue;
                    if ($effectiveStart < $mStart) $effectiveStart = $mStart;
                    if ($effectiveEnd > $mEnd) $effectiveEnd = $mEnd;
                }

                if ($effectiveStart->gt($effectiveEnd)) continue;

                $cursor = $effectiveStart->copy()->startOfMonth();
                while ($cursor <= $effectiveEnd) {
                    $monthStart = $cursor->copy()->startOfMonth();
                    $monthEnd = $cursor->copy()->endOfMonth();

                    $overlapStart = $effectiveStart->copy()->startOfDay()->max($monthStart->copy()->startOfDay());
                    $overlapEnd = $effectiveEnd->copy()->startOfDay()->min($monthEnd->copy()->startOfDay());

                    if ($overlapStart->lte($overlapEnd)) {
                        $daysInMonth = $this->inclusiveDateOnlyDays($overlapStart, $overlapEnd);
                        $fteMonth = $daysInMonth * $ftePerDay;
                        $hoursInMonth = $hoursPerDay * $daysInMonth;

                        $expanded->push((object)[
                            'rounding_key'  => 'fte-role:' . $item->id,
                            'month'         => $cursor->format('Y-m'),
                            'project'       => $item->project,
                            'name'          => $item->name,
                            'role_group'    => $item->role_group,
                            'job_detail'    => $item->job_detail,
                            'task_detail'   => $item->task_detail,
                            'fte_month'     => $fteMonth,
                            'fte_value'     => $item->fte_value,
                            'days_in_month' => $daysInMonth,
                            'fte_per_day'   => $ftePerDay,
                            'hours_in_month'=> $hoursInMonth,
                            'overlap_start' => $overlapStart,
                            'overlap_end'   => $overlapEnd,
                            'start_date'    => $item->start_date,
                            'end_date'      => $item->end_date,
                        ]);
                    }

                    $cursor->addMonth();
                }
            } catch (\Exception $e) {}
        }

        $expanded = $this->rebalanceProjectIndexDisplayValues($expanded);
        return $expanded->groupBy('month')->sortKeys();
    });

    $fteSummary = $projectList->map(function ($project) use ($monthlyFTE, $totalFteMap) {
        $months = $monthlyFTE[$project->id] ?? collect();
        $flatten = $months->flatten();
        $filteredTotal = $flatten->sum('fte_month');
        $total = (float) ($totalFteMap[$project->id] ?? 0);
        $thisMonthKey = now()->format('Y-m');
        $thisMonth = $months[$thisMonthKey] ?? collect();
        $fteThisMonth = $thisMonth->sum('fte_month');

        return (object)[
            'project_id' => $project->id,
            'project_name' => $project->project_name_th ?? 'ไม่พบชื่อโครงการ',
            'project_year' => $project->start_year,
            'total_fte' => $total,
            'filtered_fte' => $filteredTotal,
            'fte_this_month' => $fteThisMonth,
        ];
    })->sortByDesc('total_fte')->values();

    $summaryTotalFte = $fteSummary->sum('total_fte');
    $summaryFilteredFte = $fteSummary->sum('filtered_fte');
    $summaryMonthFte = $fteSummary->sum('fte_this_month');

    $years = $availableYears->map(fn($y) => $y + 543);

    $viewData = [
        'fteSummary' => $fteSummary,
        'monthlyFTE' => $monthlyFTE,
        'projects' => $projectOptions,
        'summaryTotalFte' => $summaryTotalFte,
        'summaryFilteredFte' => $summaryFilteredFte,
        'summaryMonthFte' => $summaryMonthFte,
        'years' => $years,
        'mode' => $mode,
        'year' => $year,
        'projectId' => $projectId,
        'start' => $start,
        'end' => $end,
    ];

    if ($request->boolean('partial') || $request->ajax()) {
        return response()->json([
            'html' => view('fte.partials.project-index-table', $viewData)->render(),
            'count' => $fteSummary->count(),
        ]);
    }

    return view('fte.project-index', $viewData);
}


public function detail(Request $request, $person)
{
    $this->ensureCanViewFtePerson((string) $person);
    $data = $this->buildFteDetailData($person);

    // แบ่งหน้าเดือนละ 10 เดือน (เรียงใหม่ล่าสุดก่อน)
    $months = $data['monthlyFTE']->sortKeysDesc();
    $perPage = 10;
    $page = LengthAwarePaginator::resolveCurrentPage();
    $sliced = $months->slice(($page - 1) * $perPage, $perPage, true);

    $monthlyFTEPage = new LengthAwarePaginator(
        $sliced,
        $months->count(),
        $perPage,
        $page,
        [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]
    );
    $monthlyFTEPage->setCollection($sliced);
    // กลับขึ้นไปบริเวณตัวกรองเมื่อเปลี่ยนหน้า
    $monthlyFTEPage = $monthlyFTEPage->withQueryString()->fragment('fte-filters');

    $data['monthlyFTEPage'] = $monthlyFTEPage;

    return view('fte.detail', $data);
}

    public function projectDetail(Project $project)
    {
        $this->ensureCanViewFteProject($project);
        $data = $this->buildFteProjectData($project);

        // แบ่งหน้าเดือนละ 10 เดือน (ล่าสุดก่อน)
        $months = $data['monthlyFTE']->sortKeysDesc();
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $sliced = $months->slice(($page - 1) * $perPage, $perPage, true);

        $monthlyFTEPage = new LengthAwarePaginator(
            $sliced,
            $months->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
        $monthlyFTEPage->setCollection($sliced);

        $data['monthlyFTEPage'] = $monthlyFTEPage;

        return view('fte.project-detail', $data);
    }

public function projectPdf(Project $project)
{
    $this->ensureCanViewFteProject($project);
    $data = $this->buildFteProjectData($project);
    $user = auth()->user();
    $dateStr = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
    $filename = "FTE_PROJECT_{$dateStr}_{$project->id}.pdf";

    $pdf = PDF::loadView('fte.project-pdf', array_merge($data, [
        'user' => $user,
    ]));

    return $pdf->stream($filename);
}

public function exportPdf(Request $request, $person)
{
    $this->ensureCanViewFtePerson((string) $person);
    $mode = $request->input('mode', 'all');
    $year = $request->input('year');
    $start = $request->input('start');
    $end = $request->input('end');

    $data = $this->buildFteDetailData($person, [
        'mode' => $mode,
        'year' => $year,
        'start' => $start,
        'end' => $end,
    ]);

    $user = auth()->user();
    $dateStr = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
    $filename = "FTE_{$dateStr}_{$data['person']}.pdf";

    $pdf = PDF::loadView('fte.pdf', array_merge($data, [
        'user' => $user,
    ]));

    return $pdf->stream($filename);
}

public function exportWord(Request $request, $person)
{
    $this->ensureCanViewFtePerson((string) $person);
    $mode = $request->input('mode', 'all');
    $year = $request->input('year');
    $start = $request->input('start');
    $end = $request->input('end');

    $data = $this->buildFteDetailData($person, [
        'mode' => $mode,
        'year' => $year,
        'start' => $start,
        'end' => $end,
    ]);

    $user = auth()->user();
    $dateStr = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
    $safePerson = preg_replace('/[^\pL\pN\-_\s]+/u', '', (string) $data['person']);
    $filename = "FTE_{$dateStr}_" . trim((string) $safePerson) . ".doc";

    return response()
        ->view('fte.word', array_merge($data, [
            'user' => $user,
        ]))
        ->header('Content-Type', 'application/msword; charset=UTF-8')
        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
}

private function buildFteDetailData(string $person, array $filter = []): array
{
    $mode = strtolower((string) ($filter['mode'] ?? 'all'));
    if (!in_array($mode, ['all', 'year', 'month', 'custom'], true)) {
        $mode = 'all';
    }

    $year = isset($filter['year']) && $filter['year'] !== '' ? (int) $filter['year'] : null;
    $startInput = isset($filter['start']) ? trim((string) $filter['start']) : null;
    $endInput = isset($filter['end']) ? trim((string) $filter['end']) : null;

    $filterFrom = null;
    $filterTo = null;
    $filterLabel = 'ทั้งหมด';
    $outputLabel = 'ทั้งหมด';

    if ($mode === 'year' && $year) {
        $yearAD = $year > 2500 ? $year - 543 : $year;
        if ($yearAD > 1900 && $yearAD < 2600) {
            $filterFrom = Carbon::createFromDate($yearAD, 1, 1)->startOfDay();
            $filterTo = Carbon::createFromDate($yearAD, 12, 31)->endOfDay();
            $filterLabel = 'ปี ' . ($yearAD + 543);
            $outputLabel = 'ปี ' . ($yearAD + 543);
        } else {
            $mode = 'all';
        }
    } elseif ($mode === 'month') {
        $filterFrom = now()->startOfMonth()->startOfDay();
        $filterTo = now()->endOfMonth()->endOfDay();
        $monthYearLabel = $filterFrom->copy()->locale('th')->translatedFormat('F') . ' ' . ($filterFrom->year + 543);
        $filterLabel = $monthYearLabel;
        $outputLabel = $monthYearLabel;
    } elseif ($mode === 'custom') {
        [$customFrom, $customTo] = $this->parseDateRange($startInput, $endInput);
        if ($customFrom && $customTo) {
            $filterFrom = $customFrom;
            $filterTo = $customTo;
            $filterLabel = 'ช่วงวันที่ '
                . $customFrom->copy()->locale('th')->translatedFormat('j F') . ' ' . ($customFrom->year + 543)
                . ' ถึง '
                . $customTo->copy()->locale('th')->translatedFormat('j F') . ' ' . ($customTo->year + 543);
            if ($customFrom->format('Y-m') === $customTo->format('Y-m')) {
                $outputLabel = $customFrom->copy()->locale('th')->translatedFormat('F') . ' ' . ($customFrom->year + 543);
            } else {
                $outputLabel = $customFrom->copy()->locale('th')->translatedFormat('j F') . ' ' . ($customFrom->year + 543)
                    . ' ถึง ' .
                    $customTo->copy()->locale('th')->translatedFormat('j F') . ' ' . ($customTo->year + 543);
            }
        } else {
            $mode = 'all';
            $startInput = null;
            $endInput = null;
        }
    }

    $pdfFilter = [
        'mode' => $mode,
        'year' => $year ? ($year > 2500 ? $year : $year + 543) : null,
        'start' => $filterFrom?->format('d-m-Y'),
        'end' => $filterTo?->format('d-m-Y'),
        'label' => $filterLabel,
        'output_label' => $outputLabel,
    ];

    $profile = \App\Models\User::whereRaw("CONCAT(TRIM(prefix),' ',TRIM(name)) = ?", [$person])->first();

    $raw = FteRole::with('project')
        ->where('name', $person)
        ->get();

    $backOffice = BackOfficeEntry::with('task')
        ->where('person_name', $person)
        ->get();

    $expanded = collect();

    foreach ($raw as $item) {
        try {
            [$entryStart, $entryEnd] = $this->parseStoredDateBounds($item->start_date, $item->end_date);
            if (! $entryStart || ! $entryEnd) continue;

            if ($entryStart->gt($entryEnd)) continue;

            $effectiveStart = $entryStart->copy();
            $effectiveEnd = $entryEnd->copy();

            if ($filterFrom && $filterTo) {
                if ($entryEnd < $filterFrom || $entryStart > $filterTo) continue;
                if ($effectiveStart < $filterFrom) $effectiveStart = $filterFrom->copy();
                if ($effectiveEnd > $filterTo) $effectiveEnd = $filterTo->copy();
            }

            if ($effectiveStart->gt($effectiveEnd)) continue;

            $totalDays  = $this->inclusiveDateOnlyDays($entryStart, $entryEnd);
            if ($totalDays <= 0) continue;

            $ftePerDay = $item->fte_value / $totalDays;
            $hoursPerDay = $item->hours
                ? ((float) $item->hours / $totalDays)
                : 0;

            // แตกช่วงรายเดือน
            $cursor = $effectiveStart->copy()->startOfMonth();
            while ($cursor <= $effectiveEnd) {

                $mStart = $cursor->copy()->startOfMonth();
                $mEnd   = $cursor->copy()->endOfMonth();

                $overlapStart = $effectiveStart->copy()->max($mStart);
                $overlapEnd   = $effectiveEnd->copy()->min($mEnd);

                if ($overlapStart->lte($overlapEnd)) {

                    $daysInMonth = $this->inclusiveDateOnlyDays($overlapStart, $overlapEnd);
                    $hoursInMonth = $hoursPerDay * $daysInMonth;
                    $fteMonth = $daysInMonth * $ftePerDay;

                    // ⭐ โครงสร้างเดิม + hours_in_month ที่เพิ่มเข้ามา
                    $expanded->push((object)[
                        'rounding_key'  => 'fte-role:' . $item->id,
                        'month'         => $cursor->format('Y-m'),
                        'project'       => $item->project,
                        'role_group'    => $item->role_group,
                        'job_detail'    => $item->job_detail,
                        'task_detail'   => $item->task_detail,
                        'days_in_month' => $daysInMonth,
                        'fte_per_day'   => $ftePerDay,
                        'fte_value'     => $fteMonth,

                        // ⭐ ฟิลด์ใหม่ที่เพิ่มเข้ามา
                        'hours_in_month' => $hoursInMonth,

                        'overlap_start' => $overlapStart->format('d M Y'),
                        'overlap_end'   => $overlapEnd->format('d M Y'),
                        'start_date'    => $item->start_date,
                        'end_date'      => $item->end_date,
                    ]);
                }

                $cursor->addMonth();
            }

        } catch (\Exception $e) {}
    }

    foreach ($backOffice as $entry) {
        try {
            $startRaw = $entry->start_date ?: ($entry->task?->start_date);
            $endRaw = $entry->end_date ?: ($entry->task?->end_date);
            if (!$startRaw || !$endRaw) {
                continue;
            }

            [$entryStart, $entryEnd] = $this->parseStoredDateBounds($startRaw, $endRaw);
            if (! $entryStart || ! $entryEnd) continue;

            if ($entryStart->gt($entryEnd)) continue;

            $effectiveStart = $entryStart->copy();
            $effectiveEnd = $entryEnd->copy();

            if ($filterFrom && $filterTo) {
                if ($entryEnd < $filterFrom || $entryStart > $filterTo) continue;
                if ($effectiveStart < $filterFrom) $effectiveStart = $filterFrom->copy();
                if ($effectiveEnd > $filterTo) $effectiveEnd = $filterTo->copy();
            }

            if ($effectiveStart->gt($effectiveEnd)) continue;

            $totalDays  = $this->inclusiveDateOnlyDays($entryStart, $entryEnd);
            if ($totalDays <= 0) continue;

            $ftePerDay = $entry->fte_value / $totalDays;
            $hoursPerDay = $entry->hours
                ? ((float) $entry->hours / $totalDays)
                : 0;

            $cursor = $effectiveStart->copy()->startOfMonth();
            while ($cursor <= $effectiveEnd) {

                $mStart = $cursor->copy()->startOfMonth();
                $mEnd   = $cursor->copy()->endOfMonth();

                $overlapStart = $effectiveStart->copy()->max($mStart);
                $overlapEnd   = $effectiveEnd->copy()->min($mEnd);

                if ($overlapStart->lte($overlapEnd)) {

                    $daysInMonth = $this->inclusiveDateOnlyDays($overlapStart, $overlapEnd);
                    $hoursInMonth = $hoursPerDay * $daysInMonth;
                    $fteMonth = $daysInMonth * $ftePerDay;

                    $expanded->push((object)[
                        'rounding_key'  => 'back-office:' . $entry->id,
                        'month'         => $cursor->format('Y-m'),
                        'project'       => (object) [
                            'id' => null,
                            'project_name_th' => 'งานนอกโครงการ',
                        ],
                        'task_id'       => $entry->task?->id,
                        'role_group'    => $this->backOfficeCategoryLabel($entry->category),
                        'job_detail'    => $entry->job_detail,
                        'task_detail'   => $entry->task?->title,
                        'days_in_month' => $daysInMonth,
                        'fte_per_day'   => $ftePerDay,
                        'fte_value'     => $fteMonth,
                        'hours_in_month' => $hoursInMonth,
                        'overlap_start' => $overlapStart->format('d M Y'),
                        'overlap_end'   => $overlapEnd->format('d M Y'),
                        'start_date'    => $startRaw,
                        'end_date'      => $endRaw,
                    ]);
                }

                $cursor->addMonth();
            }

        } catch (\Exception $e) {}
    }

    $expanded = $this->rebalanceSplitDisplayValues($expanded);
    $monthlyFTE = $expanded->groupBy('month')->sortKeys();

    $chartLabels = $monthlyFTE->keys()->values();
    $chartData   = $monthlyFTE->map(fn($rows) => $rows->sum('fte_value'))->values();

    return compact(
        'person',
        'profile',
        'monthlyFTE',
        'chartLabels',
        'chartData',
        'pdfFilter'
    );
}

private function buildFteProjectData(Project $project): array
{
    $fteRoles = FteRole::with('project')
        ->where('project_id', $project->id);

    if (! $this->isFteAdmin()) {
        $fteRoles->where('name', $this->currentUserFullName());
    }

    $fteRoles = $fteRoles->get();

    $expanded = collect();

    foreach ($fteRoles as $item) {
        try {
            [$start, $end] = $this->parseStoredDateBounds($item->start_date, $item->end_date);
            if (! $start || ! $end) continue;

            if ($start->gt($end)) continue;

            $totalDays  = $this->inclusiveDateOnlyDays($start, $end);
            if ($totalDays <= 0) continue;

            $ftePerDay  = $item->fte_value / $totalDays;
            $hoursPerDay = $item->hours
                ? ((float) $item->hours / $totalDays)
                : 0;

            $cursor = $start->copy()->startOfMonth();
            while ($cursor <= $end) {

                $mStart = $cursor->copy()->startOfMonth();
                $mEnd   = $cursor->copy()->endOfMonth();

                $overlapStart = $start->copy()->max($mStart);
                $overlapEnd   = $end->copy()->min($mEnd);

                if ($overlapStart->lte($overlapEnd)) {
                    $daysInMonth = $this->inclusiveDateOnlyDays($overlapStart, $overlapEnd);
                    $fteMonth = $daysInMonth * $ftePerDay;
                    $hoursInMonth = $hoursPerDay * $daysInMonth;

                    $expanded->push((object)[
                        'rounding_key'   => 'fte-role:' . $item->id,
                        'month'          => $cursor->format('Y-m'),
                        'project'        => $item->project,
                        'name'           => $item->name,
                        'role_group'     => $item->role_group,
                        'job_detail'     => $item->job_detail,
                        'task_detail'    => $item->task_detail,
                        'days_in_month'  => $daysInMonth,
                        'fte_per_day'    => $ftePerDay,
                        'fte_value'      => $fteMonth,
                        'hours_in_month' => $hoursInMonth,
                        'overlap_start'  => $overlapStart->format('d M Y'),
                        'overlap_end'    => $overlapEnd->format('d M Y'),
                    ]);
                }

                $cursor->addMonth();
            }

        } catch (\Exception $e) {}
    }

    $expanded = $this->rebalanceSplitDisplayValues($expanded);
    $monthlyFTE = $expanded->groupBy('month')->sortKeys();
    $chartLabels = $monthlyFTE->keys()->values();
    $chartData = $monthlyFTE->map(fn($rows) => $rows->sum('fte_value'))->values();

    $yearTotals = [];
    foreach ($monthlyFTE as $month => $rows) {
        $year = (int) substr($month, 0, 4);
        $yearTotals[$year] = ($yearTotals[$year] ?? 0) + $rows->sum('fte_value');
    }
    $currentYear = (int) now()->year;
    $years = [$currentYear - 2, $currentYear - 1, $currentYear];
    $yearChartLabels = array_map(fn($y) => $y + 543, $years);
    $yearChartData = array_map(fn($y) => $yearTotals[$y] ?? 0, $years);

    $personOptions = $expanded->pluck('name')->filter()->unique()->sort()->values();
    $roleOptions = $expanded->pluck('role_group')->filter()->unique()->sort()->values();
    $yearOptions = $monthlyFTE->keys()->map(fn($m) => substr($m, 0, 4))->unique()->sort()->values();
    $monthOptions = $monthlyFTE->keys()->values();

    return compact(
        'project',
        'monthlyFTE',
        'chartLabels',
        'chartData',
        'yearChartLabels',
        'yearChartData',
        'personOptions',
        'roleOptions',
        'yearOptions',
        'monthOptions',
    );
}




    public function compareData(Request $request)
    {
        $name  = $request->query('name');
        $mode  = $request->query('mode', 'year');
        $year  = $request->query('year', now()->year + 543);
        $start = $request->query('start');
        $end   = $request->query('end');

        if (!$name) {
            return response()->json(['error' => 'Name required'], 400);
        }

        $this->ensureCanViewFtePerson((string) $name);

        $query = FteRole::with('project')->where('name', $name);
        $backOfficeQuery = BackOfficeEntry::with('task')->where('person_name', $name);

        if ($mode === 'year' && $year) {
            $yearAD = ($year > 2500) ? $year - 543 : $year;
            $query->whereRaw('RIGHT(start_date, 4) = ?', [$yearAD]);
            $backOfficeQuery->whereRaw('RIGHT(start_date, 4) = ?', [$yearAD]);
        } elseif ($mode === 'month') {
            $now    = now();
            $month  = str_pad($now->month, 2, '0', STR_PAD_LEFT);
            $yearAD = $now->year;
            $query->whereRaw('RIGHT(start_date, 7) = ?', ["{$month}-{$yearAD}"]);
            $backOfficeQuery->whereRaw('RIGHT(start_date, 7) = ?', ["{$month}-{$yearAD}"]);
        } elseif ($mode === 'custom' && $start && $end) {
            try {
                $from = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->format('d-m-Y');
                $to   = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->format('d-m-Y');
                $query->whereRaw('STR_TO_DATE(start_date, "%d-%m-%Y") >= ?', [$from])
                      ->whereRaw('STR_TO_DATE(end_date, "%d-%m-%Y") <= ?', [$to]);
                $backOfficeQuery->whereRaw('STR_TO_DATE(start_date, "%d-%m-%Y") >= ?', [$from])
                                ->whereRaw('STR_TO_DATE(end_date, "%d-%m-%Y") <= ?', [$to]);
            } catch (\Exception $e) {}
        }

        $backOfficeRows = $backOfficeQuery->get()
            ->filter(fn($entry) => !empty($entry->start_date))
            ->map(function ($entry) {
                return (object) [
                    'start_date' => $entry->start_date,
                    'fte_value' => $entry->fte_value,
                ];
            });

        $rows = $query->get()->concat($backOfficeRows)->groupBy(function ($item) {
            try {
                if (!is_string($item->start_date)) {
                    return 'invalid';
                }
                $format = strpos($item->start_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';
                return \Carbon\Carbon::createFromFormat($format, $item->start_date)->format('Y-m');
            } catch (\Exception $e) {
                return 'invalid';
            }
        });

        $labels = $rows->keys()->map(function ($key) {
            if (preg_match('/^\d{4}-\d{2}$/', $key)) {
                return \Carbon\Carbon::createFromFormat('Y-m', $key)->locale('th')->isoFormat('MMM YY');
            }
            return $key;
        });

        $chartData = $rows->map(fn($r) => $r->sum('fte_value'))->values();

        return response()->json([
            'name'   => $name,
            'labels' => $labels->values(),
            'data'   => $chartData,
        ]);
    }

    private function parseDateRange(?string $start, ?string $end): array
    {
        $from = $this->parseInputDate($start);
        $to = $this->parseInputDate($end);

        if (!$from || !$to) {
            return [null, null];
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
    }

    private function parseInputDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'j-n-Y', 'j/n/Y'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if (!$date) {
                    continue;
                }
                if ((int) $date->format('Y') > 2400) {
                    $date->subYears(543);
                }
                return $date;
            } catch (\Exception $e) {
            }
        }

        if (preg_match('/^(\d{1,2})\s+([^\s]+)\s+(\d{4})$/u', $value, $matches)) {
            $monthToken = str_replace('.', '', trim($matches[2]));
            $monthMap = [
                'มกราคม' => 1,
                'มค' => 1,
                'กุมภาพันธ์' => 2,
                'กพ' => 2,
                'มีนาคม' => 3,
                'มีค' => 3,
                'เมษายน' => 4,
                'เมย' => 4,
                'พฤษภาคม' => 5,
                'พค' => 5,
                'มิถุนายน' => 6,
                'มิย' => 6,
                'กรกฎาคม' => 7,
                'กค' => 7,
                'สิงหาคม' => 8,
                'สค' => 8,
                'กันยายน' => 9,
                'กย' => 9,
                'ตุลาคม' => 10,
                'ตค' => 10,
                'พฤศจิกายน' => 11,
                'พย' => 11,
                'ธันวาคม' => 12,
                'ธค' => 12,
            ];

            if (isset($monthMap[$monthToken])) {
                $year = (int) $matches[3];
                if ($year > 2400) {
                    $year -= 543;
                }
                return Carbon::createFromDate($year, $monthMap[$monthToken], (int) $matches[1]);
            }
        }

        return null;
    }

    private function applyDateOverlapFilter($query, string $startColumn, string $endColumn, Carbon $from, Carbon $to): void
    {
        $startExpr = $this->parsedDateExpression($startColumn);
        $endExpr = $this->parsedDateExpression($endColumn);

        $query->whereRaw("{$startExpr} IS NOT NULL")
            ->whereRaw("{$endExpr} IS NOT NULL")
            ->whereRaw("{$startExpr} <= ?", [$to->format('Y-m-d H:i:s')])
            ->whereRaw("{$endExpr} >= ?", [$from->format('Y-m-d H:i:s')]);
    }

    private function parsedDateExpression(string $column): string
    {
        if (!preg_match('/^[A-Za-z0-9_.]+$/', $column)) {
            throw new \InvalidArgumentException('Invalid date column');
        }

        return "CASE
            WHEN REPLACE({$column}, '/', '-') REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
                THEN STR_TO_DATE(REPLACE({$column}, '/', '-'), '%Y-%m-%d')
            WHEN REPLACE({$column}, '/', '-') REGEXP '^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$'
                THEN STR_TO_DATE(REPLACE({$column}, '/', '-'), '%d-%m-%Y')
            ELSE NULL
        END";
    }

    private function parseStoredDateBounds(?string $start, ?string $end): array
    {
        $parsedStart = $this->parseInputDate($start)?->startOfDay();
        $parsedEnd = $this->parseInputDate($end)?->endOfDay();

        if (! $parsedStart || ! $parsedEnd) {
            return [null, null];
        }

        return [$parsedStart, $parsedEnd];
    }

    private function inclusiveDateOnlyDays(Carbon $start, Carbon $end): int
    {
        $startDate = $start->copy()->startOfDay();
        $endDate = $end->copy()->startOfDay();

        if ($startDate->gt($endDate)) {
            return 0;
        }

        return (int) $startDate->diffInDays($endDate) + 1;
    }

    private function rebalanceSplitDisplayValues(Collection $rows): Collection
    {
        $rows->each(function ($row) {
            $row->hours_in_month_exact = (float) ($row->hours_in_month ?? 0);
            $row->fte_value_exact = (float) ($row->fte_value ?? 0);
        });

        foreach ($rows->groupBy('rounding_key') as $group) {
            $this->rebalanceRoundedField($group, 'hours_in_month', 2);
            $this->rebalanceRoundedField($group, 'fte_value', 7);
        }

        return $rows;
    }

    private function rebalanceProjectIndexDisplayValues(Collection $rows): Collection
    {
        foreach ($rows->groupBy('rounding_key') as $group) {
            $this->rebalanceRoundedField($group, 'fte_month', 7);
            $this->rebalanceRoundedField($group, 'hours_in_month', 2);
        }

        return $rows;
    }

    private function rebalancePersonIndexDisplayValues(Collection $rows): Collection
    {
        foreach ($rows->groupBy('rounding_key') as $group) {
            $this->rebalanceRoundedField($group, 'fte_month', 7);
            $this->rebalanceRoundedField($group, 'hours_in_month', 2);
        }

        return $rows;
    }

    private function rebalanceRoundedField(Collection $rows, string $field, int $precision): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $scale = 10 ** $precision;
        $prepared = [];
        $targetUnits = (int) round(
            $rows->sum(fn($row) => (float) ($row->{$field} ?? 0)) * $scale
        );

        foreach ($rows->values() as $index => $row) {
            $exactUnits = ((float) ($row->{$field} ?? 0)) * $scale;
            $baseUnits = (int) floor($exactUnits + 1e-9);

            $prepared[] = [
                'row' => $row,
                'index' => $index,
                'units' => $baseUnits,
                'remainder' => $exactUnits - $baseUnits,
            ];
        }

        $currentUnits = array_sum(array_column($prepared, 'units'));
        $remainderUnits = max(0, $targetUnits - $currentUnits);

        usort($prepared, function (array $left, array $right) {
            if (abs($left['remainder'] - $right['remainder']) > 1e-12) {
                return $left['remainder'] < $right['remainder'] ? 1 : -1;
            }

            return $left['index'] <=> $right['index'];
        });

        for ($i = 0; $i < $remainderUnits; $i++) {
            $prepared[$i % count($prepared)]['units']++;
        }

        usort($prepared, fn(array $left, array $right) => $left['index'] <=> $right['index']);

        foreach ($prepared as $item) {
            $item['row']->{$field} = $item['units'] / $scale;
        }
    }

    private function backOfficeCategoryLabel(?string $category): string
    {
        $map = [
            'งานบริการด้านเอกสารงานวิจัย' => 'งานบริการด้านเอกสารงานวิจัย',
            'งานบริการด้านการขอคำปรึกษางานวิจัย' => 'งานบริการด้านการขอคำปรึกษางานวิจัย',
            'งานบริการหน่วยงาน' => 'งานบริการหน่วยงาน',
            'งานบริการด้านทรัพยากรบุคคล' => 'งานบริการด้านทรัพยากรบุคคล',
            'งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย' => 'งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย',
            'งานบริการขนส่งสินค้าเเละเอกสาร' => 'งานบริการขนส่งสินค้าเเละเอกสาร',
            'งานด้านการออกแบบ เเละ สื่อสิ่งพิมพ์' => 'งานด้านการออกแบบ เเละ สื่อสิ่งพิมพ์',
            'งานด้านการสื่อสาร' => 'งานด้านการสื่อสาร',
            'งานด้านการพัฒนาเทคโนโลยีเเละสารสนเทศ' => 'งานด้านการพัฒนาเทคโนโลยีเเละสารสนเทศ',
            'งานด้านการออกแบบเเละผลิตสื่อวิดิทัศน์' => 'งานด้านการออกแบบเเละผลิตสื่อวิดิทัศน์',
            'การจัดงานหรือเข้าร่วมงานนิทรรศการ' => 'การจัดงานหรือเข้าร่วมงานนิทรรศการ',
            'การออกแบบหลักสูตร เเละ การเรียนการสอน' => 'การออกแบบหลักสูตร เเละ การเรียนการสอน',
            'event' => 'event',
            'work' => 'work',
            'consult' => 'consult',
            'other' => 'อื่นๆ',
        ];

        return $map[$category] ?? 'อื่นๆ';
    }

}

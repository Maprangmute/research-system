<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FteRole;
use App\Models\Project;
use App\Models\BackOfficeEntry;
use App\Models\BackOfficeTask;
use App\Models\User;
use App\Models\WfaReport;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use PDF;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\SimpleType\VerticalJc;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Html;


class FteController extends Controller
{
private function isFteAdmin(): bool
{
    return Auth::user()?->isAdminEquivalent() ?? false;
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

    if ($user && in_array($user->role, [
        User::ROLE_USER,
        User::ROLE_USER_ALL,
        User::ROLE_RESEARCH_EXECUTIVE,
    ], true)) {
        return redirect()->route('fte.detail', ['person' => $fullName]);
    }

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
            'job_name',
            'job_detail',
            'start_date',
            'end_date',
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
        $category = $this->backOfficeCategoryLabel($entry->category);
        return (object) [
            'id' => 'bo-' . $entry->id,
            'task_id' => $task?->id,
            'project_id' => null,
            'name' => $entry->person_name,
            'fte_value' => $entry->fte_value,
            'hours' => $entry->hours,
            'start_date' => $entry->start_date,
            'end_date' => $entry->end_date,
            'role_group' => $category,
            'job_name' => $entry->job_name,
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
                            'job_name'      => $item->job_name ?? null,
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

    $selectedFteLabel = match ($mode) {
        'year' => 'FTE รายปี',
        'month' => 'FTE รายเดือน',
        'custom' => 'FTE ตามช่วงเวลาที่เลือก',
        default => 'FTE ตามช่วงที่เลือก',
    };

    // ===== Summary =====
    $fteSummary = $monthlyFTE->map(function ($months, $name) {
        $flatten = $months->flatten();
        $total = $flatten->sum('fte_month');

        return (object)[
            'name'           => $name,
            'total_fte'      => $total,
            'fte_selected'   => $total,
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
        'selectedFteLabel' => $selectedFteLabel,
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

    // แบ่งหน้าเดือนละ 10 เดือน โดยเรียงเดือนจากเก่าไปใหม่
    $months = $data['monthlyFTE']->sortKeys();
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

        // แบ่งหน้าเดือนละ 10 เดือน โดยเรียงเดือนจากเก่าไปใหม่
        $months = $data['monthlyFTE']->sortKeys();
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

    AuditLogger::logFileDownload($filename, [
        'subject_type' => 'fte_project_pdf',
        'subject_label' => (string) ($project->project_name_th ?: $project->project_code ?: $project->id),
    ], request());

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
        'profilePhotoDataUri' => $this->resolveUserProfilePhotoDataUriForExport($data['profile'] ?? null),
    ]));

    AuditLogger::logFileDownload($filename, [
        'subject_type' => 'fte_person_pdf',
        'subject_label' => (string) $data['person'],
    ], $request);

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
    $filename = "FTE_{$dateStr}_" . trim((string) $safePerson) . ".docx";
    $tempFile = tempnam(sys_get_temp_dir(), 'fte_word_');
    $docxPath = $tempFile . '.docx';
    @unlink($tempFile);

    $this->buildFteWordDocument(
        array_merge($data, [
            'user' => $user,
            'profilePhotoPath' => $this->resolveUserProfilePhotoPathForWordExport($data['profile'] ?? null),
        ]),
        $docxPath
    );

    AuditLogger::logFileDownload($filename, [
        'subject_type' => 'fte_person_word',
        'subject_label' => (string) $data['person'],
    ], $request);

    return response()
        ->download($docxPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])
        ->deleteFileAfterSend(true);
}

public function wfaIndex(Request $request)
{
    $user = $request->user();
    $search = trim((string) $request->query('q', ''));
    $query = WfaReport::query()
        ->with('user:id,prefix,name,position,role,profile_photo_path')
        ->latest();

    if (! $this->canViewAnyWfaReport($user)) {
        $query->where('user_id', $user->id);
    }

    if ($search !== '') {
        $query->where(function ($subQuery) use ($search): void {
            $subQuery
                ->where('wfa_code', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhere('person_name', 'like', "%{$search}%")
                ->orWhere('output_text', 'like', "%{$search}%");
        });
    }

    $reports = $query->paginate(10)->withQueryString()->fragment('wfa-list-table');

    return view('wfa.index', [
        'reports' => $reports,
        'canViewAll' => $this->canViewAnyWfaReport($user),
        'search' => $search,
        'monthOptions' => $this->wfaMonthOptions(),
    ]);
}

public function wfaCreate(Request $request)
{
    $user = $request->user();
    $month = now()->copy()->startOfMonth();

    return view('wfa.form', [
        'report' => new WfaReport([
            'title' => $this->wfaDefaultTitle($user, $month),
            'filter_mode' => 'month',
            'filter_year' => $month->year + 543,
            'filter_month' => $month->month,
        ]),
        'users' => $this->wfaAssignableUsers($request->user()),
        'monthOptions' => $this->wfaMonthOptions(),
        'isEdit' => false,
    ]);
}

public function wfaStore(Request $request)
{
    $payload = $this->validatedWfaPayload($request);
    $report = WfaReport::create($payload);

    if ($request->expectsJson()) {
        $report->refresh();

        return response()->json([
            'ok' => true,
            'message' => 'บันทึก WFA แล้ว',
            'report' => $this->wfaJsonReport($report),
        ]);
    }

    return redirect()
        ->route('wfa.edit', $report)
        ->with('status', 'บันทึก WFA แล้ว');
}

public function wfaEdit(Request $request, WfaReport $wfaReport)
{
    $this->ensureCanEditWfaReport($wfaReport);

    return view('wfa.form', [
        'report' => $wfaReport,
        'users' => $this->wfaAssignableUsers($request->user()),
        'monthOptions' => $this->wfaMonthOptions(),
        'isEdit' => true,
    ]);
}

public function wfaUpdate(Request $request, WfaReport $wfaReport)
{
    $this->ensureCanEditWfaReport($wfaReport);
    $wfaReport->update($this->validatedWfaPayload($request, $wfaReport));

    if ($request->expectsJson()) {
        $wfaReport->refresh();

        return response()->json([
            'ok' => true,
            'message' => 'อัพเดทข้อมูลแล้ว',
            'report' => $this->wfaJsonReport($wfaReport),
        ]);
    }

    return redirect()
        ->route('wfa.edit', $wfaReport)
        ->with('status', 'บันทึก WFA แล้ว');
}

private function wfaJsonReport(WfaReport $report): array
{
    $createdAt = $report->created_at?->copy()->setTimezone('Asia/Bangkok');
    $createdLabel = $createdAt
        ? $createdAt->copy()->locale('th')->translatedFormat('j F') . ' ' . ($createdAt->year + 543) . ' เวลา ' . $createdAt->format('H:i')
        : '-';

    return [
        'id' => $report->id,
        'code' => $report->wfa_code ?: WfaReport::formatCode((int) $report->id),
        'person_name' => $report->person_name,
        'title' => $report->title,
        'month_label' => $this->wfaMonthLabel($this->wfaReportMonth($report)),
        'created_at_label' => $createdLabel,
        'created_timestamp' => $report->created_at?->timestamp ?? 0,
        'output_text' => $report->output_text ?: '-',
        'output_html' => $report->output_html,
        'filter_month_key' => $this->wfaReportMonth($report)->format('Y-m'),
        'can_edit' => $this->canEditWfaReport($report),
        'edit_url' => route('wfa.edit', $report),
        'update_url' => route('wfa.update', $report),
        'pdf_url' => route('wfa.pdf', $report),
        'word_url' => route('wfa.word', $report),
    ];
}

public function wfaDestroy(WfaReport $wfaReport)
{
    $this->ensureCanEditWfaReport($wfaReport);
    $wfaReport->delete();

    return redirect()
        ->route('wfa.index')
        ->with('status', 'ลบ WFA แล้ว');
}

public function wfaPdf(Request $request, WfaReport $wfaReport)
{
    $this->ensureCanViewWfaReport($wfaReport);
    $wfaReport->refresh();
    $data = $this->buildWfaExportData($wfaReport);
    $dateStr = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
    $code = $wfaReport->wfa_code ?: WfaReport::formatCode((int) $wfaReport->id);
    $filename = "{$code}_{$dateStr}_{$data['person']}.pdf";

    $pdf = PDF::loadView('fte.pdf', array_merge($data, [
        'user' => $request->user(),
        'profilePhotoDataUri' => $this->resolveUserProfilePhotoDataUriForExport($data['profile'] ?? null),
    ]));

    AuditLogger::logFileDownload($filename, [
        'subject_type' => 'wfa_pdf',
        'subject_label' => (string) $wfaReport->title,
    ], $request);

    return $pdf->stream($filename);
}

public function wfaWord(Request $request, WfaReport $wfaReport)
{
    $this->ensureCanViewWfaReport($wfaReport);
    $wfaReport->refresh();
    $data = $this->buildWfaExportData($wfaReport);
    $dateStr = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
    $safePerson = preg_replace('/[^\pL\pN\-_\s]+/u', '', (string) $data['person']);
    $code = $wfaReport->wfa_code ?: WfaReport::formatCode((int) $wfaReport->id);
    $filename = "{$code}_{$dateStr}_" . trim((string) $safePerson) . ".docx";
    $tempFile = tempnam(sys_get_temp_dir(), 'wfa_word_');
    $docxPath = $tempFile . '.docx';
    @unlink($tempFile);

    $this->buildFteWordDocument(
        array_merge($data, [
            'user' => $request->user(),
            'profilePhotoPath' => $this->resolveUserProfilePhotoPathForWordExport($data['profile'] ?? null),
        ]),
        $docxPath
    );

    AuditLogger::logFileDownload($filename, [
        'subject_type' => 'wfa_word',
        'subject_label' => (string) $wfaReport->title,
    ], $request);

    return response()
        ->download($docxPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])
        ->deleteFileAfterSend(true);
}

private function canManageAnyWfaReport(?User $user): bool
{
    return $user && in_array($user->role, [
        User::ROLE_ADMIN,
        User::ROLE_SU_ADMIN,
    ], true);
}

private function canViewAnyWfaReport(?User $user): bool
{
    return $user && (
        $this->canManageAnyWfaReport($user)
        || $user->role === User::ROLE_UNIT_EXECUTIVE
    );
}

private function canEditWfaReport(WfaReport $report, ?User $user = null): bool
{
    $user ??= Auth::user();

    return $user
        && ((int) $report->user_id === (int) $user->id || $this->canManageAnyWfaReport($user));
}

private function ensureCanViewWfaReport(WfaReport $report): void
{
    $user = Auth::user();
    abort_unless($user && ($this->canViewAnyWfaReport($user) || (int) $report->user_id === (int) $user->id), 403);
}

private function ensureCanEditWfaReport(WfaReport $report): void
{
    abort_unless($this->canEditWfaReport($report), 403);
}

private function wfaAssignableUsers(?User $user): Collection
{
    if (! $user?->isAdminLike()) {
        return collect([$user])->filter();
    }

    return User::query()
        ->where('role', '!=', User::ROLE_RESIGNED)
        ->orderBy('name')
        ->get(['id', 'prefix', 'name', 'position', 'role']);
}

private function validatedWfaPayload(Request $request, ?WfaReport $report = null): array
{
    $user = $request->user();
    $data = $request->validate([
        'filter_month_key' => ['required', 'date_format:Y-m'],
        'output_html' => ['required', 'string'],
    ]);

    $ownerId = (int) ($report?->user_id ?? $user->id);
    $owner = User::findOrFail($ownerId);
    $selectedMonth = Carbon::createFromFormat('Y-m', $data['filter_month_key'])->startOfMonth();
    $outputHtml = $this->sanitizeWfaOutputHtml((string) $data['output_html']);
    $outputText = trim(html_entity_decode(strip_tags(str_replace(['</p>', '</div>', '</li>', '<br>', '<br/>', '<br />'], "\n", $outputHtml))));

    return [
        'user_id' => $owner->id,
        'person_name' => $owner->full_name,
        'title' => $this->wfaDefaultTitle($owner, $selectedMonth),
        'work_date' => null,
        'filter_mode' => 'month',
        'filter_year' => $selectedMonth->year + 543,
        'filter_month' => $selectedMonth->month,
        'filter_start' => null,
        'filter_end' => null,
        'output_html' => $outputHtml,
        'output_text' => $outputText,
    ];
}

private function buildWfaExportData(WfaReport $report): array
{
    $mode = $report->filter_mode ?: 'month';
    $filter = [
        'mode' => $mode,
        'year' => $report->filter_year,
        'start' => $report->filter_start?->format('d-m-Y'),
        'end' => $report->filter_end?->format('d-m-Y'),
    ];

    if ($mode === 'month') {
        $selectedMonth = $this->wfaReportMonth($report);
        $from = $selectedMonth->copy()->subMonthNoOverflow()->startOfMonth();
        $to = $selectedMonth->copy()->addMonthNoOverflow()->endOfMonth();
        $filter = [
            'mode' => 'custom',
            'year' => null,
            'start' => $from->format('d-m-Y'),
            'end' => $to->format('d-m-Y'),
        ];
    }

    $data = $this->buildFteDetailData($report->person_name, $filter);

    if ($mode === 'month') {
        $selectedMonth = $this->wfaReportMonth($report);
        $previousMonth = $this->wfaMonthLabel($selectedMonth->copy()->subMonthNoOverflow());
        $currentMonth = $this->wfaMonthLabel($selectedMonth);
        $nextMonth = $this->wfaMonthLabel($selectedMonth->copy()->addMonthNoOverflow());
        $data['pdfFilter']['mode'] = 'month';
        $data['pdfFilter']['label'] = "{$previousMonth} - {$currentMonth} - {$nextMonth}";
        $data['pdfFilter']['output_label'] = $data['pdfFilter']['label'];
    }

    $outputHtml = $this->sanitizeWfaOutputHtml((string) $report->output_html);

    return array_merge($data, [
        'wfaReport' => $report,
        'wfaOutputHtml' => $outputHtml,
        'wfaOutputPdfHtml' => $this->normalizeWfaOutputHtmlForPdf($outputHtml),
        'wfaOutputWordHtml' => $this->normalizeWfaOutputHtmlForPdf($outputHtml),
        'wfaOutputTitle' => 'Output ที่เกิดขึ้นจากการทำงานของเดือน ' . $this->wfaMonthLabel($this->wfaReportMonth($report)->subMonthNoOverflow()),
    ]);
}

private function wfaReportMonth(WfaReport $report): Carbon
{
    $yearBE = (int) ($report->filter_year ?: now()->year + 543);
    $yearAD = $yearBE > 2500 ? $yearBE - 543 : $yearBE;
    $month = (int) ($report->filter_month ?: now()->month);

    return Carbon::createFromDate($yearAD, max(1, min(12, $month)), 1)->startOfMonth();
}

private function wfaDefaultTitle(User $user, Carbon $month): string
{
    return 'WFA ' . $user->full_name . ' ประจำเดือน ' . $this->wfaMonthLabel($month);
}

private function wfaMonthLabel(Carbon $month): string
{
    $date = $month->copy()->locale('th');
    return $date->translatedFormat('F') . ' ' . ($date->year + 543);
}

private function wfaMonthOptions(): Collection
{
    $current = now()->copy()->startOfMonth();

    $months = collect([
        $current->copy()->subMonthNoOverflow(),
        $current,
        $current->copy()->addMonthNoOverflow(),
    ]);

    return $months
        ->unique(fn(Carbon $month) => $month->format('Y-m'))
        ->sortBy(fn(Carbon $month) => $month->format('Y-m'))
        ->values()
        ->map(fn(Carbon $month) => [
            'value' => $month->format('Y-m'),
            'label' => $this->wfaMonthLabel($month),
            'output_label' => $this->wfaMonthLabel($month->copy()->subMonthNoOverflow()),
            'year' => $month->year + 543,
            'is_current' => $month->isSameMonth($current),
        ]);
}

private function sanitizeWfaOutputHtml(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $html = $this->normalizeWfaManualBullets($html);

    libxml_use_internal_errors(true);
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $allowedTags = ['div', 'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'ul', 'ol', 'li', 'span'];
    $allowedStyles = ['font-weight', 'font-style', 'text-decoration', 'text-align', 'margin-left'];

    $cleanNode = function (\DOMNode $node) use (&$cleanNode, $allowedTags, $allowedStyles): void {
        if ($node instanceof \DOMElement) {
            $tag = strtolower($node->nodeName);
            if (! in_array($tag, $allowedTags, true)) {
                $node->parentNode?->removeChild($node);
                return;
            }

            foreach (iterator_to_array($node->attributes) as $attribute) {
                $name = strtolower($attribute->nodeName);
                if ($name !== 'style') {
                    $node->removeAttribute($attribute->nodeName);
                    continue;
                }

                $style = collect(explode(';', (string) $attribute->nodeValue))
                    ->map(fn($rule) => trim($rule))
                    ->filter()
                    ->map(function ($rule) {
                        [$property, $value] = array_pad(explode(':', $rule, 2), 2, '');
                        return [strtolower(trim($property)), trim($value)];
                    })
                    ->filter(fn($pair) => in_array($pair[0], $allowedStyles, true))
                    ->map(fn($pair) => $pair[0] . ':' . preg_replace('/[^a-zA-Z0-9ก-๙#.,()%\s-]/u', '', $pair[1]))
                    ->implode('; ');

                if ($style === '') {
                    $node->removeAttribute('style');
                } else {
                    $node->setAttribute('style', $style);
                }
            }
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $cleanNode($child);
        }
    };

    $cleanNode($dom);

    $body = '';
    foreach ($dom->documentElement?->childNodes ?? [] as $child) {
        $body .= $dom->saveHTML($child);
    }

    return trim($body);
}

private function normalizeWfaOutputHtmlForPdf(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    libxml_use_internal_errors(true);
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $collapseTextNodes = function (\DOMNode $node) use (&$collapseTextNodes): void {
        if ($node instanceof \DOMText) {
            $node->nodeValue = preg_replace('/[ \t\r\n]+/u', ' ', (string) $node->nodeValue);
            return;
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $collapseTextNodes($child);
        }
    };

    $collapseTextNodes($dom);

    foreach (iterator_to_array($dom->getElementsByTagName('ul')) as $list) {
        $table = $dom->createElement('table');
        $table->setAttribute('class', 'wfa-output-list');
        $table->setAttribute('style', 'width:100%; border-collapse:collapse; table-layout:fixed; margin:4px 0 8px 0;');

        foreach (iterator_to_array($list->childNodes) as $child) {
            if (! $child instanceof \DOMElement || strtolower($child->nodeName) !== 'li') {
                continue;
            }

            $row = $dom->createElement('tr');
            $bulletCell = $dom->createElement('td', '•');
            $bulletCell->setAttribute('class', 'wfa-output-bullet');
            $bulletCell->setAttribute('style', 'width:18px; border:0; padding:0 0 7px 0; vertical-align:top; text-align:left; font-weight:bold;');
            $textCell = $dom->createElement('td');
            $textCell->setAttribute('class', 'wfa-output-item');
            $textCell->setAttribute('style', 'border:0; padding:0 0 7px 0; vertical-align:top; text-align:left;');

            foreach (iterator_to_array($child->childNodes) as $itemChild) {
                $textCell->appendChild($itemChild->cloneNode(true));
            }

            $row->appendChild($bulletCell);
            $row->appendChild($textCell);
            $table->appendChild($row);
        }

        $list->parentNode?->replaceChild($table, $list);
    }

    $body = '';
    foreach ($dom->documentElement?->childNodes ?? [] as $child) {
        $body .= $dom->saveHTML($child);
    }

    return trim((string) $body);
}

private function normalizeWfaManualBullets(string $html): string
{
    if (!str_contains($html, '•') || preg_match('/<\/?(ul|ol|li)\b/i', $html)) {
        return $html;
    }

    $text = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
    $text = preg_replace('/<\/\s*(p|div)\s*>/i', "\n", (string) $text);
    $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace("/\r\n|\r/u", "\n", (string) $text);
    $text = preg_replace("/[ \t]+/u", ' ', (string) $text);
    $text = trim((string) $text);

    if ($text === '' || !str_contains($text, '•')) {
        return $html;
    }

    $parts = preg_split('/\s*•\s*/u', $text);
    if (!is_array($parts) || count($parts) < 2) {
        return $html;
    }

    $prefix = trim((string) array_shift($parts));
    $items = collect($parts)
        ->map(fn($part) => trim((string) $part))
        ->filter()
        ->values();

    if ($items->isEmpty()) {
        return $html;
    }

    $result = '';
    if ($prefix !== '') {
        $result .= '<p>' . e($prefix) . '</p>';
    }

    $result .= '<ul>';
    foreach ($items as $item) {
        $lines = collect(explode("\n", (string) $item))
            ->map(fn($line) => trim($line))
            ->filter()
            ->map(fn($line) => e($line));
        $result .= '<li>' . $lines->implode('<br>') . '</li>';
    }
    $result .= '</ul>';

    return $result;
}

private function buildFteWordDocument(array $data, string $targetPath): void
{
    $phpWord = new PhpWord();
    $phpWord->setDefaultFontName('TH Sarabun New');
    $phpWord->setDefaultFontSize(16);
    $phpWord->setDefaultParagraphStyle([
        'spaceAfter' => 0,
        'spacing' => 0,
        'lineHeight' => 1.1,
    ]);

    $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 24, 'color' => '0F172A']);
    $section = $phpWord->addSection([
        'marginTop' => 900,
        'marginBottom' => 900,
        'marginLeft' => 900,
        'marginRight' => 900,
        'footerHeight' => 500,
    ]);

    $user = $data['user'] ?? null;
    $person = (string) ($data['person'] ?? '-');
    $profile = $data['profile'] ?? null;
    $profilePhotoPath = $data['profilePhotoPath'] ?? null;
    $monthlyFTE = $data['monthlyFTE'] ?? collect();
    $monthWorkList = collect($data['monthWorkList'] ?? []);
    $monthProjectStats = $data['monthProjectStats'] ?? [];
    $pdfFilter = $data['pdfFilter'] ?? [];
    $wfaOutputHtml = trim((string) ($data['wfaOutputWordHtml'] ?? $data['wfaOutputHtml'] ?? ''));
    $wfaOutputTitle = trim((string) ($data['wfaOutputTitle'] ?? '')) ?: 'Output ที่เกิดขึ้น';
    $isWfaReport = $wfaOutputHtml !== '';
    $reportTitle = $isWfaReport ? 'WFA / FTE Individual Report' : 'FTE Individual Report';
    $allRows = $monthlyFTE instanceof Collection ? $monthlyFTE->flatten(1) : collect();
    $projectCount = $allRows->pluck('project.id')->filter()->unique()->count();
    $totalFte = (float) $allRows->sum('fte_value');
    $totalHours = (float) $allRows->sum('hours_in_month');
    $position = $profile->position ?? '-';
    $reporter = $user ? trim(($user->prefix ?? '') . ' ' . ($user->name ?? '')) : 'ผู้ใช้ระบบ';
    $reportNow = now()->setTimezone('Asia/Bangkok')->locale('th');
    $reportDate = $reportNow->translatedFormat('j F') . ' ' . ($reportNow->year + 543) . ' เวลา ' . $reportNow->format('H:i');
    $filterLabel = $pdfFilter['label'] ?? 'ทั้งหมด';
    $fullWidth = 10000;
    $cellBorder = ['borderSize' => 8, 'borderColor' => 'CBD5E1'];
    $tableBase = [
        'unit' => TblWidth::TWIP,
        'width' => 100 * 50,
        'alignment' => JcTable::CENTER,
        'cellMarginTop' => 80,
        'cellMarginBottom' => 80,
        'cellMarginLeft' => 120,
        'cellMarginRight' => 120,
    ];
    $labelStyle = ['bold' => true, 'size' => 14, 'color' => '334155'];
    $bodyStyle = ['size' => 12, 'color' => '334155'];
    $mutedStyle = ['size' => 12, 'color' => '64748B'];

    $footer = $section->addFooter();
    $footerTable = $footer->addTable(array_merge($tableBase, [
        'borderTopSize' => 6,
        'borderTopColor' => 'CBD5E1',
    ]));
    $footerTable->addRow();
    $footerTable->addCell(7000, ['valign' => VerticalJc::CENTER])->addText(
        'ผู้สร้างรายงาน ' . $reporter . ' · ' . $reportDate,
        ['size' => 10, 'color' => '64748B']
    );
    $footerTable->addCell(2500, ['valign' => VerticalJc::CENTER])->addPreserveText(
        'หน้า {PAGE} / {NUMPAGES}',
        ['size' => 10, 'color' => '64748B'],
        ['alignment' => Jc::END]
    );

    $headerTable = $section->addTable($tableBase);
    $headerTable->addRow();
    $headerTable->addCell(6200, ['valign' => VerticalJc::CENTER])->addText($reportTitle, ['bold' => true, 'size' => 24, 'color' => '0F172A']);
    $headerRight = $headerTable->addCell(3800, ['valign' => VerticalJc::CENTER]);
    $headerRight->addText('Siriraj Health Policy', ['size' => 13, 'color' => '64748B'], ['alignment' => Jc::END, 'spaceAfter' => 40]);
    $headerRight->addText($isWfaReport ? 'รายงานภาระงาน WFA และผลงานที่เกิดขึ้น' : 'รายงานภาระงานรายบุคคล', ['size' => 13, 'color' => '64748B'], ['alignment' => Jc::END]);

    $section->addTextBreak(1);

    $heroTable = $section->addTable(array_merge($tableBase, $cellBorder));
    $heroTable->addRow(1900);
    $photoCell = $heroTable->addCell(1900, ['bgColor' => 'F8FAFC', 'valign' => VerticalJc::CENTER]);
    if ($profilePhotoPath && is_file($profilePhotoPath)) {
        $photoCell->addImage($profilePhotoPath, [
            'width' => 82,
            'height' => 82,
            'alignment' => Jc::CENTER,
        ]);
    }
    $heroCell = $heroTable->addCell(8100, ['valign' => VerticalJc::CENTER]);
    if (! $isWfaReport) {
        $heroCell->addText('FTE OVERVIEW', ['bold' => true, 'size' => 14, 'color' => '64748B'], ['spaceAfter' => 60]);
    }
    $heroCell->addText($person, ['bold' => true, 'size' => 24, 'color' => '0F172A'], ['spaceAfter' => 120]);
    $heroCell->addText('ตำแหน่ง ' . $position, ['size' => 15, 'color' => '475569'], ['spaceAfter' => 70]);
    $heroCell->addText('ช่วงข้อมูล: ' . $filterLabel, ['size' => 14, 'color' => '64748B'], ['spaceAfter' => 40]);
    $heroCell->addText('รายงานออกเมื่อ ' . $reportDate, ['size' => 14, 'color' => '64748B']);

    $section->addTextBreak(1);

    $summaryTable = $section->addTable(array_merge($tableBase, $cellBorder));
    $summaryTable->addRow();
    $summaryItems = [
        ['label' => 'จำนวนภาระงานทั้งหมด', 'value' => number_format($allRows->count()), 'note' => 'รายการงานที่ถูกคำนวณในรายงานนี้', 'bg' => 'EFF6FF'],
        ['label' => 'จำนวนโครงการ / งาน', 'value' => number_format($projectCount), 'note' => 'นับโครงการที่มีภาระงานเชื่อมโยง', 'bg' => 'EFF6FF'],
        ['label' => 'FTE รวมทั้งหมด', 'value' => number_format($totalFte, 4), 'note' => 'สะสมจากทุกช่วงเวลาในขอบเขตรายงาน', 'bg' => 'F5F3FF'],
        ['label' => 'ชั่วโมงรวมทั้งหมด', 'value' => number_format($totalHours, 2), 'note' => 'รวมชั่วโมงทำงานทั้งหมด', 'bg' => 'ECFEFF'],
    ];
    foreach ($summaryItems as $item) {
        $cell = $summaryTable->addCell(2500, ['bgColor' => $item['bg'], 'valign' => VerticalJc::CENTER]);
        $cell->addText($item['label'], $labelStyle, ['alignment' => Jc::CENTER, 'spaceAfter' => 50]);
        $cell->addText($item['value'], ['bold' => true, 'size' => 22, 'color' => '0F172A'], ['alignment' => Jc::CENTER, 'spaceAfter' => 40]);
        $cell->addText($item['note'], $mutedStyle, ['alignment' => Jc::CENTER]);
    }

    $section->addTextBreak(1);
    $section->addText('สถานะโครงการ / งานประจำเดือน ' . ($monthProjectStats['label'] ?? '-'), ['bold' => true, 'size' => 18, 'color' => '0F172A'], ['spaceAfter' => 40]);
    $section->addText('สรุปรายการงานที่เกี่ยวข้องในเดือนอ้างอิง พร้อมรายละเอียดภาระงานแต่ละกลุ่ม', ['size' => 13, 'color' => '64748B'], ['spaceAfter' => 80]);

    $workListTable = $section->addTable(array_merge($tableBase, $cellBorder));
    $workListTable->addRow();
    foreach ([
        [$isWfaReport ? 'ภาระงาน' : 'ชื่อโครงการ / งาน', 2800],
        ['รายละเอียดงาน', 5400],
        ['สถานะ', 1800],
    ] as [$text, $width]) {
        $workListTable->addCell($width, ['bgColor' => 'DFF7FB', 'valign' => VerticalJc::CENTER])->addText($text, ['bold' => true, 'size' => 13, 'color' => '334155'], ['alignment' => Jc::CENTER]);
    }
    if ($monthWorkList->isEmpty()) {
        $workListTable->addRow();
        $workListTable->addCell($fullWidth, ['gridSpan' => 3, 'valign' => VerticalJc::CENTER])->addText('ไม่มีรายการโครงการ/งานในเดือนนี้', ['size' => 13, 'color' => '64748B'], ['alignment' => Jc::CENTER]);
    } else {
        foreach ($monthWorkList as $work) {
            $workListTable->addRow();
            $workTitle = (string) ($work['name'] ?? '-');
            $workListTable->addCell(2800, ['valign' => VerticalJc::CENTER])->addText($workTitle, ['bold' => true, 'size' => 14, 'color' => '0F172A']);
            $detailCell = $workListTable->addCell(5400, ['valign' => VerticalJc::CENTER]);
            $detailGroups = collect($work['work_detail_groups'] ?? [])->filter();
            if ($detailGroups->isEmpty()) {
                $detailCell->addText('-', ['size' => 13, 'color' => '64748B']);
            } else {
                foreach ($detailGroups as $group) {
                    $groupLabel = (string) ($group['job'] ?? 'รายละเอียดงาน');
                    if (str_contains($groupLabel, '•')) {
                        $this->addManualBulletTextToWordCell($detailCell, $groupLabel, ['bold' => true, 'size' => 13, 'color' => '0F172A']);
                    } else {
                        $detailCell->addText('• ' . $groupLabel, ['bold' => true, 'size' => 13, 'color' => '0F172A'], ['spaceAfter' => 20]);
                    }
                    foreach (collect($group['tasks'] ?? [])->filter() as $task) {
                        $this->addManualBulletTextToWordCell($detailCell, (string) ($task['task'] ?? '-'), $bodyStyle);
                    }
                }
            }
            $workListTable->addCell(1800, ['valign' => VerticalJc::CENTER])->addText((string) ($work['status_label'] ?? '-'), ['bold' => true, 'size' => 13, 'color' => '334155'], ['alignment' => Jc::CENTER]);
        }
    }

    $section->addTextBreak(1);
    $section->addText('รายละเอียดการทำงานรายเดือน', ['bold' => true, 'size' => 18, 'color' => '0F172A'], ['spaceAfter' => 40]);
    $section->addText('แยกรายเดือนเพื่อให้ติดตามภาระงาน ช่วงเวลา ชั่วโมง และค่า FTE ได้อย่างเป็นระบบ', ['size' => 13, 'color' => '64748B'], ['spaceAfter' => 80]);

    foreach ($monthlyFTE as $monthKey => $rows) {
        $carbon = Carbon::createFromFormat('Y-m', $monthKey);
        $monthName = $carbon->locale('th')->translatedFormat('F');
        $year = $carbon->year + 543;

        $section->addText($monthName . ' ' . $year . ' · ' . $rows->count() . ' รายการ', ['bold' => true, 'size' => 16, 'color' => '9A3412'], ['spaceAfter' => 40]);
        $monthTable = $section->addTable(array_merge($tableBase, $cellBorder));
        $monthTable->addRow();
        foreach ([
            ['#', 600],
            [$isWfaReport ? 'ภาระงาน / รายละเอียดงาน' : 'โครงการ / รายละเอียดงาน', 4000],
            ['ช่วงเวลา', 2200],
            ['ชั่วโมง', 1000],
            ['วันทำงาน', 1000],
            ['FTE เดือนนี้', 1200],
        ] as [$text, $width]) {
            $monthTable->addCell($width, ['bgColor' => 'FFF1DF', 'valign' => VerticalJc::CENTER])->addText($text, ['bold' => true, 'size' => 13, 'color' => '334155'], ['alignment' => Jc::CENTER]);
        }

        foreach ($rows as $index => $item) {
            $monthTable->addRow();
            $monthTable->addCell(600, ['valign' => VerticalJc::CENTER])->addText((string) ($index + 1), ['size' => 13], ['alignment' => Jc::CENTER]);

            $isBackOffice = ! $item->project?->id;
            $backOfficeTitle = trim((string) ($item->job_name ?? '')) ?: trim((string) ($item->task_detail ?? '')) ?: '-';
            $backOfficeType = trim((string) ($item->task_detail ?? '')) ?: 'งานนอกโครงการ';
            $detailText = $isBackOffice
                ? (trim((string) ($item->job_detail ?? '')) ?: $backOfficeType)
                : (trim((string) ($item->task_detail ?? '')) ?: trim((string) ($item->job_detail ?? '')) ?: '-');
            $title = $item->project?->id ? ($item->project->project_name_th ?? '-') : $backOfficeTitle;
            $detailCell = $monthTable->addCell(4000, ['valign' => VerticalJc::CENTER]);
            $detailCell->addText($title, ['bold' => true, 'size' => 14, 'color' => '0F172A'], ['spaceAfter' => 20]);
            if (! $isWfaReport) {
                $detailCell->addText('กลุ่ม: ' . ($item->role_group ?? '-'), $bodyStyle, ['spaceAfter' => 10]);
            }
            $detailCell->addText('รายละเอียดงาน:', $bodyStyle, ['spaceAfter' => 10]);
            $this->addManualBulletTextToWordCell($detailCell, $detailText, $bodyStyle);
            if ($isBackOffice) {
                $detailCell->addText($backOfficeType, $bodyStyle);
            }

            $osCarbon = Carbon::parse($item->overlap_start);
            $oeCarbon = Carbon::parse($item->overlap_end);
            $range = $osCarbon->locale('th')->translatedFormat('j F') . ' ' . ($osCarbon->year + 543)
                . "\nถึง " . $oeCarbon->locale('th')->translatedFormat('j F') . ' ' . ($oeCarbon->year + 543);
            $monthTable->addCell(2200, ['valign' => VerticalJc::CENTER])->addText($range, $bodyStyle, ['alignment' => Jc::CENTER]);
            $monthTable->addCell(1000, ['valign' => VerticalJc::CENTER])->addText(number_format((float) $item->hours_in_month, 2), $bodyStyle, ['alignment' => Jc::CENTER]);
            $monthTable->addCell(1000, ['valign' => VerticalJc::CENTER])->addText((string) intval($item->days_in_month), $bodyStyle, ['alignment' => Jc::CENTER]);
            $monthTable->addCell(1200, ['valign' => VerticalJc::CENTER])->addText(number_format((float) $item->fte_value, 6), $bodyStyle, ['alignment' => Jc::CENTER]);
        }

        $section->addTextBreak(1);
    }

    if ($wfaOutputHtml !== '') {
        $section->addPageBreak();
        $section->addTextBreak(1);
        $section->addText($wfaOutputTitle, ['bold' => true, 'size' => 18, 'color' => '0F172A']);
        $section->addTextBreak(1);
        Html::addHtml($section, '<div style="font-family: TH Sarabun New; font-size: 12pt; line-height: 1.38; text-align: left;">' . $wfaOutputHtml . '</div>', false, false);
        $section->addTextBreak(2);
        $section->addText('ขอรับรองว่าปฏิบัติงานจริง', ['bold' => true, 'size' => 12, 'color' => '111827']);
        $section->addTextBreak(3);
        $section->addText('................................................', ['size' => 12, 'color' => '111827']);
        $section->addText('รศ. พญ.วราลักษณ์ ศรีนนท์ประเสริฐ', ['bold' => true, 'size' => 12, 'color' => '111827']);
        $section->addText('หัวหน้าหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ', ['size' => 12, 'color' => '111827']);
    }

    $previousEscaping = Settings::isOutputEscapingEnabled();
    Settings::setOutputEscapingEnabled(true);

    try {
        IOFactory::createWriter($phpWord, 'Word2007')->save($targetPath);
    } finally {
        Settings::setOutputEscapingEnabled($previousEscaping);
    }
}

private function addManualBulletTextToWordCell($cell, string $text, array $style, array $paragraphStyle = []): void
{
    $text = trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($text === '') {
        $cell->addText('-', $style, $paragraphStyle);
        return;
    }

    if (!str_contains($text, '•')) {
        foreach (preg_split("/\r\n|\r|\n/u", $text) ?: [$text] as $line) {
            $line = trim((string) $line);
            if ($line !== '') {
                $cell->addText($line, $style, array_merge(['spaceAfter' => 35], $paragraphStyle));
            }
        }
        return;
    }

    $parts = preg_split('/\s*•\s*/u', $text);
    $prefix = trim((string) array_shift($parts));
    if ($prefix !== '') {
        $cell->addText($prefix, $style, array_merge(['spaceAfter' => 35], $paragraphStyle));
    }

    foreach (collect($parts)->map(fn($part) => trim((string) $part))->filter() as $item) {
        $lines = collect(preg_split("/\r\n|\r|\n/u", (string) $item) ?: [])
            ->map(fn($line) => trim((string) $line))
            ->filter()
            ->values();

        $textLine = $lines->isNotEmpty() ? $lines->implode("\n") : (string) $item;
        $cell->addText('• ' . $textLine, $style, array_merge(['spaceAfter' => 60], $paragraphStyle));
    }
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
    $currentMonthStart = now()->copy()->startOfMonth()->startOfDay();
    $currentMonthEnd = now()->copy()->endOfMonth()->endOfDay();
    $currentMonthLabel = $currentMonthStart->copy()->locale('th')->translatedFormat('F') . ' ' . ($currentMonthStart->year + 543);
    $monthWorkMap = collect();
    $upsertMonthWork = function (string $key, array $payload) use ($monthWorkMap): void {
        $existing = $monthWorkMap->get($key);
        $incomingJob = trim((string) ($payload['job_detail'] ?? ''));
        $incomingTask = trim((string) ($payload['task_detail'] ?? ''));
        $incomingPair = ['job' => $incomingJob, 'task' => $incomingTask];

        if (!$existing) {
            $payload['detail_pairs'] = (($incomingJob !== '') || ($incomingTask !== '')) ? [$incomingPair] : [];
            $payload['work_detail_lines'] = [];
            $payload['work_detail'] = '-';
            $monthWorkMap->put($key, $payload);
            $existing = $payload;
        } else {
            $detailPairs = collect($existing['detail_pairs'] ?? []);
            if (($incomingJob !== '') || ($incomingTask !== '')) {
                $detailPairs->push($incomingPair);
            }
            $detailPairs = $detailPairs
                ->map(function ($pair) {
                    return [
                        'job' => trim((string) ($pair['job'] ?? '')),
                        'task' => trim((string) ($pair['task'] ?? '')),
                    ];
                })
                ->filter(fn($pair) => $pair['job'] !== '' || $pair['task'] !== '')
                ->unique(fn($pair) => mb_strtolower($pair['job'] . '|' . $pair['task']))
                ->values();
            $existing['detail_pairs'] = $detailPairs->all();
        }

        $isCompleted = (bool) (($existing['is_completed'] ?? false) || ($payload['is_completed'] ?? false));
        $detailPairs = collect($existing['detail_pairs'] ?? []);
        $grouped = $detailPairs->groupBy(fn($pair) => $pair['job'] !== '' ? $pair['job'] : '-');
        $detailLines = $grouped->map(function ($pairs, $job) {
            $tasks = collect($pairs)
                ->pluck('task')
                ->map(fn($task) => trim((string) $task))
                ->filter()
                ->unique()
                ->values();

            if ($job === '-') {
                return $tasks->isNotEmpty() ? $tasks->implode(', ') : '-';
            }

            return $tasks->isNotEmpty()
                ? "{$job}: " . $tasks->implode(', ')
                : $job;
        })->filter()->values();

        $existing['is_completed'] = $isCompleted;
        $existing['status_label'] = $isCompleted ? 'จบงานในเดือนนี้' : 'กำลังดำเนินงาน';
        $existing['status_monthly'] = $existing['status_label'];
        $existing['status_project'] = $existing['status_label'];
        $existing['work_detail_lines'] = $detailLines->all();
        $existing['work_detail'] = $detailLines->isNotEmpty() ? $detailLines->implode(' | ') : '-';

        $monthWorkMap->put($key, $existing);
    };

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

            if ($item->project?->id && $entryStart->lte($currentMonthEnd) && $entryEnd->gte($currentMonthStart)) {
                $project = $item->project;
                $projectId = (int) $project->id;
                $entryEndDate = $this->parseInputDate((string) ($item->end_date ?? ''))?->endOfDay();
                $isCompletedThisMonth = $entryEndDate
                    && $entryEndDate->gte($currentMonthStart)
                    && $entryEndDate->lte($currentMonthEnd);

                $workKey = 'fte-role:' . (int) $item->id;
                $upsertMonthWork($workKey, [
                    'key' => $workKey,
                    'type' => 'project',
                    'project_id' => $projectId,
                    'task_id' => null,
                    'name' => trim((string) ($project->project_name_th ?? '')) ?: ('โครงการ #' . $projectId),
                    'role_group' => trim((string) ($item->role_group ?? '')),
                    'status_label' => $isCompletedThisMonth ? 'จบงานในเดือนนี้' : 'กำลังดำเนินงาน',
                    'status_monthly' => $isCompletedThisMonth ? 'จบงานในเดือนนี้' : 'กำลังดำเนินงาน',
                    'status_project' => $isCompletedThisMonth ? 'จบงานในเดือนนี้' : 'กำลังดำเนินงาน',
                    'is_completed' => (bool) $isCompletedThisMonth,
                    'job_detail' => trim((string) ($item->job_detail ?? '')),
                    'task_detail' => trim((string) ($item->task_detail ?? '')),
                ]);
            }

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
                        'job_name'      => null,
                        'job_detail'    => $item->job_detail,
                        'task_detail'   => $item->task_detail,
                        'days_in_month' => $daysInMonth,
                        'fte_per_day'   => $ftePerDay,
                        'fte_value'     => $fteMonth,

                        // ⭐ ฟิลด์ใหม่ที่เพิ่มเข้ามา
                        'hours_in_month' => $hoursInMonth,
                        'hours_total'   => (float) ($item->hours ?? 0),

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
            $startRaw = $entry->start_date;
            $endRaw = $entry->end_date;
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

            if ($entryStart->lte($currentMonthEnd) && $entryEnd->gte($currentMonthStart)) {
                $taskId = $entry->task?->id;
                $key = 'back-office:' . (int) $entry->id;
                $categoryName = $this->backOfficeCategoryLabel($entry->category);
                $taskName = trim((string) ($entry->task?->title ?? ''));
                if ($taskName === '') {
                    $taskName = trim((string) ($entry->job_detail ?? '')) ?: 'งานนอกโครงการ';
                }
                $workName = trim((string) ($entry->job_name ?? '')) ?: $taskName;

                $entryEndDate = $this->parseInputDate((string) $endRaw)?->endOfDay();
                $isCompletedThisMonth = $entryEndDate
                    && $entryEndDate->gte($currentMonthStart)
                    && $entryEndDate->lte($currentMonthEnd);

                $upsertMonthWork($key, [
                    'key' => $key,
                    'type' => 'back_office',
                    'project_id' => null,
                    'task_id' => $taskId,
                    'name' => $workName,
                    'role_group' => $categoryName,
                    'status_label' => $isCompletedThisMonth ? 'จบงานในเดือนนี้' : 'กำลังดำเนินงาน',
                    'status_monthly' => $isCompletedThisMonth ? 'จบงานในเดือนนี้' : 'กำลังดำเนินงาน',
                    'status_project' => $isCompletedThisMonth ? 'จบงานในเดือนนี้' : 'กำลังดำเนินงาน',
                    'is_completed' => (bool) $isCompletedThisMonth,
                    'job_detail' => trim((string) ($entry->job_detail ?? '')),
                    'task_detail' => trim((string) ($entry->task?->title ?? '')),
                ]);
            }

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
                        'job_name'      => $entry->job_name,
                        'job_detail'    => $entry->job_detail,
                        'task_detail'   => $entry->task?->title,
                        'days_in_month' => $daysInMonth,
                        'fte_per_day'   => $ftePerDay,
                        'fte_value'     => $fteMonth,
                        'hours_in_month' => $hoursInMonth,
                        'hours_total'   => (float) ($entry->hours ?? 0),
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
    $currentMonthKey = $currentMonthStart->format('Y-m');
    $currentMonthRows = $monthlyFTE->get($currentMonthKey, collect());
    $formatShortBeDate = static function (?Carbon $date): string {
        if (!$date) {
            return '-';
        }
        $beYear2 = str_pad((string) (($date->year + 543) % 100), 2, '0', STR_PAD_LEFT);
        return $date->format('d-m-') . $beYear2;
    };
    $formatHours = static function (float $hours): string {
        $formatted = number_format($hours, 2, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');
        return $formatted === '' ? '0' : $formatted;
    };

    $monthDetailMap = collect();
    foreach ($currentMonthRows as $row) {
        $detailKey = (string) ($row->rounding_key ?? '');

        if ($detailKey === '') {
            continue;
        }

        $job = trim((string) ($row->job_detail ?? ''));
        $task = trim((string) ($row->task_detail ?? ''));
        if ($job === '' && $task === '') {
            continue;
        }

        $rowStart = $this->parseInputDate((string) ($row->start_date ?? ''))?->startOfDay();
        $rowEnd = $this->parseInputDate((string) ($row->end_date ?? ''))?->endOfDay();
        if (!$rowStart || !$rowEnd) {
            continue;
        }
        if ($rowStart->gt($rowEnd)) {
            [$rowStart, $rowEnd] = [$rowEnd, $rowStart];
        }

        $rangeLabel = $formatShortBeDate($rowStart) . ' ถึง ' . $formatShortBeDate($rowEnd);
        $hoursValue = (float) ($row->hours_total ?? $row->hours_in_month ?? 0);
        $monthDaysValue = (int) ($row->days_in_month ?? 0);

        $pairs = collect($monthDetailMap->get($detailKey, []));
        $pairs->push([
            'job' => $job,
            'task' => $task,
            'range' => $rangeLabel,
            'hours' => $hoursValue,
            'month_days' => $monthDaysValue,
        ]);
        $monthDetailMap->put($detailKey, $pairs->all());
    }
    $monthWorkList = $monthWorkMap
        ->values()
        ->map(function ($work) use ($monthDetailMap, $formatHours) {
            $pairs = collect($monthDetailMap->get((string) ($work['key'] ?? ''), []))
                ->map(function ($pair) {
                    return [
                        'job' => trim((string) ($pair['job'] ?? '')),
                        'task' => trim((string) ($pair['task'] ?? '')),
                        'range' => trim((string) ($pair['range'] ?? '')),
                        'hours' => (float) ($pair['hours'] ?? 0),
                        'month_days' => (int) ($pair['month_days'] ?? 0),
                    ];
                })
                ->filter(fn($pair) => $pair['job'] !== '' || $pair['task'] !== '')
                ->values();

            if ($pairs->isNotEmpty()) {
                $groups = $pairs
                    ->groupBy(fn($pair) => $pair['job'] !== '' ? $pair['job'] : 'รายละเอียดงาน')
                    ->map(function ($group, $job) use ($formatHours) {
                        $taskMap = [];
                        foreach ($group as $item) {
                            $taskText = trim((string) ($item['task'] ?? ''));
                            $rangeText = trim((string) ($item['range'] ?? ''));
                            $hoursVal = (float) ($item['hours'] ?? 0);

                            $taskLabel = $taskText !== '' ? $taskText : 'งาน';
                            $rangeLabel = $rangeText !== '' ? $rangeText : '-';
                            $aggregateKey = mb_strtolower($taskLabel . '|' . $rangeLabel);

                            if (!isset($taskMap[$aggregateKey])) {
                                $taskMap[$aggregateKey] = [
                                    'task' => $taskLabel,
                                    'range' => $rangeLabel,
                                    'hours' => 0.0,
                                    'month_days' => 0,
                                ];
                            }
                            $taskMap[$aggregateKey]['hours'] += $hoursVal;
                            $taskMap[$aggregateKey]['month_days'] += (int) ($item['month_days'] ?? 0);
                        }

                        $tasks = collect(array_values($taskMap))
                            ->map(function ($item) use ($formatHours) {
                                $hoursText = $formatHours((float) ($item['hours'] ?? 0));
                                $monthDaysText = (int) ($item['month_days'] ?? 0);
                                return [
                                    'task' => $item['task'] ?? 'งาน',
                                    'range' => ($item['range'] ?? '-') . ' | เดือนนี้ทำ ' . $monthDaysText . ' วัน  รวม ' . $hoursText . ' ชั่วโมง',
                                ];
                            })
                            ->values();

                        return [
                            'job' => $job,
                            'tasks' => $tasks->all(),
                        ];
                    })
                    ->values();

                $work['work_detail_groups'] = $groups->all();
                $lines = $groups->map(function ($group) {
                    $taskLines = collect($group['tasks'] ?? [])->map(function ($task) {
                        return trim((string) ($task['task'] ?? '')) . ' (' . trim((string) ($task['range'] ?? '-')) . ')';
                    })->implode(', ');
                    return trim((string) ($group['job'] ?? '')) . ': ' . $taskLines;
                })->values();

                $work['work_detail_lines'] = $lines->all();
                $work['work_detail'] = $lines->isNotEmpty() ? $lines->implode(' | ') : '-';
            }

            if (!isset($work['work_detail']) || trim((string) $work['work_detail']) === '') {
                $work['work_detail'] = '-';
            }
            if (!isset($work['work_detail_lines']) || !is_array($work['work_detail_lines'])) {
                $work['work_detail_lines'] = $work['work_detail'] !== '-' ? [(string) $work['work_detail']] : [];
            }
            if (!isset($work['work_detail_groups']) || !is_array($work['work_detail_groups'])) {
                $work['work_detail_groups'] = [];
            }
            return $work;
        })
        ->sortBy([
            ['is_completed', 'asc'],
            ['type', 'asc'],
            ['name', 'asc'],
        ], SORT_NATURAL | SORT_FLAG_CASE)
        ->values();
    $activeCount = $monthWorkList->where('is_completed', false)->count();
    $completedCount = $monthWorkList->where('is_completed', true)->count();

    $monthProjectStats = [
        'label' => $currentMonthLabel,
        'total' => $monthWorkList->count(),
        'active' => $activeCount,
        'completed' => $completedCount,
    ];

    return compact(
        'person',
        'profile',
        'monthlyFTE',
        'chartLabels',
        'chartData',
        'pdfFilter',
        'monthProjectStats',
        'monthWorkList'
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
            'งานบริการ สนับสนุน และประสานงานภายในหน่วย' => 'งานบริการ สนับสนุน และประสานงานภายในหน่วย',
            'งานด้านติดต่อประสานงาน' => 'งานด้านติดต่อประสานงาน',
            'งานด้านการลงตารางนัดหมายประชุม' => 'งานด้านการลงตารางนัดหมายประชุม',
            'งานบริการส่งสินค้าและเอกสาร' => 'งานบริการส่งสินค้าและเอกสาร',
            'งานบริการด้านเอกสาร' => 'งานบริการด้านเอกสาร',
            'งานบริการด้านการขอคำปรึกษางานวิจัย' => 'งานบริการด้านการขอคำปรึกษางานวิจัย',
            'งานบริการด้านการให้คำปรึกษางานวิจัย' => 'งานบริการด้านการให้คำปรึกษางานวิจัย',
            'งานบริการหน่วยงาน' => 'งานบริการหน่วยงาน',
            'งานบริการด้านทรัพยากรบุคคล' => 'งานบริการด้านทรัพยากรบุคคล',
            'งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย' => 'งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย',
            'งานบริการขนส่งสินค้าเเละเอกสาร' => 'งานบริการขนส่งสินค้าเเละเอกสาร',
            'งานออกแบบหลักสูตรและการเรียนการสอน' => 'งานออกแบบหลักสูตรและการเรียนการสอน',
            'งานจัดการเรียนการสอน' => 'งานจัดการเรียนการสอน',
            'งานพัฒนาศักยภาพและให้คำปรึกษานักศึกษา (Mentorship)' => 'งานพัฒนาศักยภาพและให้คำปรึกษานักศึกษา (Mentorship)',
            'งานด้านการสื่อสาร และประชาสัมพันธ์' => 'งานด้านการสื่อสาร และประชาสัมพันธ์',
            'งานด้านออกแบบ เเละผลิตสื่อ' => 'งานด้านออกแบบ เเละผลิตสื่อ',
            'งานด้านออกแบบ และผลิตสื่อ' => 'งานด้านออกแบบ และผลิตสื่อ',
            'งานด้านการออกแบบ เเละ สื่อสิ่งพิมพ์' => 'งานด้านออกแบบ เเละผลิตสื่อ',
            'งานด้านการสื่อสาร' => 'งานด้านการสื่อสาร',
            'งานด้านการพัฒนาเทคโนโลยีเเละสารสนเทศ' => 'งานด้านการพัฒนาเทคโนโลยีเเละสารสนเทศ',
            'งานด้านการพัฒนาเทคโนโลยีและสารสนเทศ' => 'งานด้านการพัฒนาเทคโนโลยีและสารสนเทศ',
            'งานจัดงานหรือเข้าร่วมงานนิทรรศการ' => 'งานจัดงานหรือเข้าร่วมงานนิทรรศการ',
            'การจัดงานหรือเข้าร่วมงานนิทรรศการ' => 'การจัดงานหรือเข้าร่วมงานนิทรรศการ',
            'การออกแบบหลักสูตร เเละ การเรียนการสอน' => 'การออกแบบหลักสูตร เเละ การเรียนการสอน',
            'event' => 'event',
            'work' => 'work',
            'consult' => 'consult',
            'other' => 'อื่นๆ',
        ];

        return $map[$category] ?? 'อื่นๆ';
    }

    private function resolveUserProfilePhotoDataUriForExport(?User $user): ?string
    {
        $fullPath = $this->resolveUserProfilePhotoPathForExport($user);
        if (! $fullPath) {
            return null;
        }

        $contents = @file_get_contents($fullPath);
        if ($contents === false || $contents === '') {
            return null;
        }

        $mime = mime_content_type($fullPath) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private function resolveUserProfilePhotoPathForExport(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $relativePath = trim((string) ($user->profile_photo_path ?? ''));
        if ($relativePath === '') {
            return null;
        }

        $candidates = [
            storage_path('app/public/' . ltrim($relativePath, '/')),
            public_path('storage/' . ltrim($relativePath, '/')),
        ];

        return collect($candidates)->first(fn ($path) => is_file($path) && is_readable($path));
    }

    private function resolveUserProfilePhotoPathForWordExport(?User $user): ?string
    {
        $fullPath = $this->resolveUserProfilePhotoPathForExport($user);
        if (! $fullPath) {
            return null;
        }

        $imageInfo = @getimagesize($fullPath);
        if (! is_array($imageInfo)) {
            return null;
        }

        $mime = strtolower((string) ($imageInfo['mime'] ?? mime_content_type($fullPath) ?: ''));
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)) {
            return null;
        }

        return $fullPath;
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FteRole;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use PDF;


class FteController extends Controller
{
public function index(Request $request)
{
    $user = Auth::user();
    $fullName = trim($user->prefix . ' ' . $user->name);

    $mode   = $request->input('mode', 'month');
    $year   = $request->input('year');
    $person = $request->input('person');
    $start  = $request->input('start');
    $end    = $request->input('end');

    // Project สิทธิ์
    $projectQuery = Project::query();
    if ($user->role !== 'admin') {
        $projectQuery->where(function ($q) use ($fullName, $user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                    $subQ->where('name', $fullName);
                });
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
            'start_date',
            'end_date',
            'role_group',
            'job_detail',
            'task_detail'
        ])
        ->whereIn('project_id', $projectIds);

    // ========== FILTER ==========

    // รายปี
    if ($mode === 'year' && $year) {
        $yearAD = $year > 2500 ? $year - 543 : $year;
        $startOfYear = Carbon::createFromDate($yearAD, 1, 1)->startOfDay();
        $endOfYear   = Carbon::createFromDate($yearAD, 12, 31)->endOfDay();

        $fteQuery->whereRaw("STR_TO_DATE(start_date, '%d-%m-%Y') <= ?", [$endOfYear->format('Y-m-d H:i:s')])
                 ->whereRaw("STR_TO_DATE(end_date, '%d-%m-%Y') >= ?",  [$startOfYear->format('Y-m-d H:i:s')]);
    }

    // รายเดือน (เดือนนี้)
    if ($mode === 'month') {
        $now = now();
        $mStart = $now->copy()->startOfMonth()->startOfDay();
        $mEnd   = $now->copy()->endOfMonth()->endOfDay();

        $fteQuery->whereRaw("STR_TO_DATE(start_date, '%d-%m-%Y') <= ?", [$mEnd->format('Y-m-d H:i:s')])
                 ->whereRaw("STR_TO_DATE(end_date, '%d-%m-%Y') >= ?",  [$mStart->format('Y-m-d H:i:s')]);
    }

    // custom range
    if ($mode === 'custom' && $start && $end) {
        try {
            $from = Carbon::createFromFormat('d/m/Y', $start)->startOfDay();
            $to   = Carbon::createFromFormat('d/m/Y', $end)->endOfDay();

            $fteQuery->whereRaw("STR_TO_DATE(start_date, '%d-%m-%Y') <= ?", [$to->format('Y-m-d H:i:s')])
                     ->whereRaw("STR_TO_DATE(end_date, '%d-%m-%Y') >= ?",  [$from->format('Y-m-d H:i:s')]);
        } catch (\Exception $e) {}
    }

    if ($person) {
        $fteQuery->where('name', $person);
    }

    $fteRows = $fteQuery->with(['project:id,project_name_th'])->get();

    // ==== Profile Map ====
    $users = \App\Models\User::select('prefix', 'name', 'profile_photo_path')->get();
    $profiles = $users->mapWithKeys(function ($user) {
        $full = trim($user->prefix . ' ' . $user->name);
        return [$full => $user->profile_photo_path];
    });

    // ============================
    // Group by: name → month
    // ============================

    $monthlyFTE = $fteRows->groupBy('name')->map(function ($rows) use ($mode, $year, $start, $end) {

        $expanded = collect();

        foreach ($rows as $item) {
            if (empty($item->start_date) || empty($item->end_date)) continue;

            try {
                // detect format
                $formatStart = strpos($item->start_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';
                $formatEnd   = strpos($item->end_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';

                $startDate = Carbon::createFromFormat($formatStart, $item->start_date)->startOfDay();
                $endDate   = Carbon::createFromFormat($formatEnd,   $item->end_date)->endOfDay();

                if ($startDate->gt($endDate)) continue;

                $totalDays = $startDate->diffInDays($endDate) + 1;
                if ($totalDays <= 0) continue;

                $ftePerDay = $item->fte_value / $totalDays;

                $effectiveStart = $startDate->copy();
                $effectiveEnd   = $endDate->copy();

                // custom
                if ($mode === 'custom' && $start && $end) {
                    try {
                        $from = Carbon::createFromFormat('d/m/Y', $start)->startOfDay();
                        $to   = Carbon::createFromFormat('d/m/Y', $end)->endOfDay();

                        if ($endDate < $from || $startDate > $to) continue;
                        if ($effectiveStart < $from) $effectiveStart = $from;
                        if ($effectiveEnd > $to)     $effectiveEnd = $to;
                    } catch (\Exception $e) {}
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
                        $daysInMonth = $overlapStart->diffInDays($overlapEnd) + 1;
                        $fteMonth = $daysInMonth * $ftePerDay;

                        $expanded->push((object)[
                            'month'         => $cursor->format('Y-m'),
                            'project'       => $item->project,
                            'role_group'    => $item->role_group,
                            'job_detail'    => $item->job_detail,
                            'fte_month'     => $fteMonth,
                            'fte_value'     => $item->fte_value,
                            'days_in_month' => $daysInMonth,
                            'total_days'    => $totalDays,
                            'fte_per_day'   => $ftePerDay,
                            'overlap_start' => $overlapStart,
                            'overlap_end'   => $overlapEnd,
                            'start_date'    => $item->start_date,
                            'end_date'      => $item->end_date,
                            'task_detail'  => $item->task_detail,
                        ]);
                    }

                    $cursor->addMonth();
                }

            } catch (\Exception $e) {}
        }

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

    // years
    $years = FteRole::selectRaw('DISTINCT RIGHT(start_date,4) AS year')
        ->orderByDesc('year')
        ->pluck('year')
        ->map(fn($y) => (int)$y + 543);

    $personList = FteRole::whereIn('project_id', $projectIds)
        ->distinct()->orderBy('name')->pluck('name');

    return view('fte.index', [
        'fteSummary' => $fteSummary,
        'years'      => $years,
        'monthlyFTE' => $monthlyFTE,
        'personList' => $personList,
        'profiles'   => $profiles,
        'mode'       => $mode,
        'year'       => $request->input('year'),
        'person'     => $person,
        'start'      => $start,
        'end'        => $end,
    ]);
}

public function projectIndex(Request $request)
{
    $user = Auth::user();
    $fullName = trim($user->prefix . ' ' . $user->name);

    $mode = $request->input('mode', 'month');
    $year = $request->input('year');
    $projectId = $request->input('project_id');
    $start = $request->input('start');
    $end = $request->input('end');

    $projectQuery = Project::query();
    if ($user->role !== 'admin') {
        $projectQuery->where(function ($q) use ($fullName, $user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                    $subQ->where('name', $fullName);
                });
        });
    }
    $projectList = $projectQuery
        ->orderBy('project_name_th')
        ->get(['id', 'project_name_th', 'start_year']);
    $projectIds = $projectList->pluck('id');
    $projectOptions = $projectList;

    $totalFteMap = [];
    $allRows = FteRole::whereIn('project_id', $projectIds)
        ->get(['project_id', 'fte_value', 'start_date', 'end_date', 'hours']);
    foreach ($allRows as $row) {
        if (empty($row->start_date) || empty($row->end_date)) {
            continue;
        }
        try {
            $formatStart = strpos($row->start_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';
            $formatEnd = strpos($row->end_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';
            $startDate = Carbon::createFromFormat($formatStart, $row->start_date)->startOfDay();
            $endDate = Carbon::createFromFormat($formatEnd, $row->end_date)->endOfDay();
            if ($startDate->gt($endDate)) {
                continue;
            }
            $totalDays = $startDate->diffInDays($endDate) + 1;
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
                    $daysInMonth = $overlapStart->diffInDays($overlapEnd) + 1;
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

    if ($projectId) {
        $fteQuery->where('project_id', $projectId);
    }

    if ($mode === 'year' && $year) {
        $yearAD = $year > 2500 ? $year - 543 : $year;
        $startOfYear = Carbon::createFromDate($yearAD, 1, 1)->startOfDay();
        $endOfYear = Carbon::createFromDate($yearAD, 12, 31)->endOfDay();

        $fteQuery->whereRaw("STR_TO_DATE(start_date, '%d-%m-%Y') <= ?", [$endOfYear->format('Y-m-d H:i:s')])
            ->whereRaw("STR_TO_DATE(end_date, '%d-%m-%Y') >= ?", [$startOfYear->format('Y-m-d H:i:s')]);
    }

    if ($mode === 'month') {
        $now = now();
        $mStart = $now->copy()->startOfMonth()->startOfDay();
        $mEnd = $now->copy()->endOfMonth()->endOfDay();

        $fteQuery->whereRaw("STR_TO_DATE(start_date, '%d-%m-%Y') <= ?", [$mEnd->format('Y-m-d H:i:s')])
            ->whereRaw("STR_TO_DATE(end_date, '%d-%m-%Y') >= ?", [$mStart->format('Y-m-d H:i:s')]);
    }

    if ($mode === 'custom' && $start && $end) {
        try {
            $from = Carbon::createFromFormat('d/m/Y', $start)->startOfDay();
            $to = Carbon::createFromFormat('d/m/Y', $end)->endOfDay();

            $fteQuery->whereRaw("STR_TO_DATE(start_date, '%d-%m-%Y') <= ?", [$to->format('Y-m-d H:i:s')])
                ->whereRaw("STR_TO_DATE(end_date, '%d-%m-%Y') >= ?", [$from->format('Y-m-d H:i:s')]);
        } catch (\Exception $e) {}
    }

    $fteRows = $fteQuery->with(['project:id,project_name_th,start_year'])->get();
    $projectMap = $projectList->keyBy('id');

    $monthlyFTE = $fteRows->groupBy('project_id')->map(function ($rows) use ($mode, $year, $start, $end) {
        $expanded = collect();

        foreach ($rows as $item) {
            if (empty($item->start_date) || empty($item->end_date)) continue;

            try {
                $formatStart = strpos($item->start_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';
                $formatEnd = strpos($item->end_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';

                $startDate = Carbon::createFromFormat($formatStart, $item->start_date)->startOfDay();
                $endDate = Carbon::createFromFormat($formatEnd, $item->end_date)->endOfDay();

                if ($startDate->gt($endDate)) continue;

                $totalDays = $startDate->diffInDays($endDate) + 1;
                if ($totalDays <= 0) continue;

                $ftePerDay = $item->fte_value / $totalDays;
                $hoursPerDay = $item->hours ? ((float) $item->hours / $totalDays) : 0;

                $effectiveStart = $startDate->copy();
                $effectiveEnd = $endDate->copy();

                if ($mode === 'custom' && $start && $end) {
                    try {
                        $from = Carbon::createFromFormat('d/m/Y', $start)->startOfDay();
                        $to = Carbon::createFromFormat('d/m/Y', $end)->endOfDay();

                        if ($endDate < $from || $startDate > $to) continue;
                        if ($effectiveStart < $from) $effectiveStart = $from;
                        if ($effectiveEnd > $to) $effectiveEnd = $to;
                    } catch (\Exception $e) {}
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
                        $daysInMonth = $overlapStart->diffInDays($overlapEnd) + 1;
                        $fteMonth = $daysInMonth * $ftePerDay;
                        $hoursInMonth = $hoursPerDay * $daysInMonth;

                        $expanded->push((object)[
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

    $summaryTotalFte = array_sum($totalFteMap);
    $summaryMonthFte = $fteSummary->sum('fte_this_month');

    $perPage = 10;
    $page = LengthAwarePaginator::resolveCurrentPage();
    $fteSummaryPage = new LengthAwarePaginator(
        $fteSummary->forPage($page, $perPage)->values(),
        $fteSummary->count(),
        $perPage,
        $page,
        [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]
    );

    $years = FteRole::selectRaw('DISTINCT RIGHT(start_date,4) AS year')
        ->orderByDesc('year')
        ->pluck('year')
        ->map(fn($y) => (int)$y + 543);

    return view('fte.project-index', [
        'fteSummary' => $fteSummaryPage,
        'monthlyFTE' => $monthlyFTE,
        'projects' => $projectOptions,
        'summaryTotalFte' => $summaryTotalFte,
        'summaryMonthFte' => $summaryMonthFte,
        'years' => $years,
        'mode' => $mode,
        'year' => $request->input('year'),
        'projectId' => $projectId,
        'start' => $start,
        'end' => $end,
    ]);
}


public function detail(Request $request, $person)
{
    $data = $this->buildFteDetailData($person);

    return view('fte.detail', $data);
}

public function projectDetail(Project $project)
{
    $data = $this->buildFteProjectData($project);

    return view('fte.project-detail', $data);
}

public function projectPdf(Project $project)
{
    $data = $this->buildFteProjectData($project);
    $user = auth()->user();
    $dateStr = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
    $filename = "FTE_PROJECT_{$dateStr}_{$project->id}.pdf";

    $pdf = PDF::loadView('fte.project-pdf', array_merge($data, [
        'user' => $user,
    ]));

    return $pdf->stream($filename);
}

public function exportPdf($person)
{
    $data = $this->buildFteDetailData($person);
    $user = auth()->user();
    $dateStr = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
    $filename = "FTE_{$dateStr}_{$data['person']}.pdf";

    $pdf = PDF::loadView('fte.pdf', array_merge($data, [
        'user' => $user,
    ]));

    return $pdf->stream($filename);
}

private function buildFteDetailData(string $person): array
{
    $profile = \App\Models\User::whereRaw("CONCAT(TRIM(prefix),' ',TRIM(name)) = ?", [$person])->first();

    $raw = FteRole::with('project')
        ->where('name', $person)
        ->get();

    $expanded = collect();

    foreach ($raw as $item) {
        try {
            $formatStart = strpos($item->start_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';
            $formatEnd   = strpos($item->end_date,   '/') !== false ? 'd/m/Y' : 'd-m-Y';

            $start = Carbon::createFromFormat($formatStart, $item->start_date)->startOfDay();
            $end   = Carbon::createFromFormat($formatEnd,   $item->end_date)->endOfDay();

            if ($start->gt($end)) continue;

            $totalDays  = $start->diffInDays($end) + 1;
            if ($totalDays <= 0) continue;

            $ftePerDay = $item->fte_value / $totalDays;
            $hoursPerDay = $item->hours
                ? ((float) $item->hours / $totalDays)
                : 0;

            // แตกช่วงรายเดือน
            $cursor = $start->copy()->startOfMonth();
            while ($cursor <= $end) {

                $mStart = $cursor->copy()->startOfMonth();
                $mEnd   = $cursor->copy()->endOfMonth();

                $overlapStart = $start->copy()->max($mStart);
                $overlapEnd   = $end->copy()->min($mEnd);

                if ($overlapStart->lte($overlapEnd)) {

                    $daysInMonth = $overlapStart->diffInDays($overlapEnd) + 1;
                    $hoursInMonth = $hoursPerDay * $daysInMonth;
                    $fteMonth = $daysInMonth * $ftePerDay;

                    // ⭐ โครงสร้างเดิม + hours_in_month ที่เพิ่มเข้ามา
                    $expanded->push((object)[
                        'month'         => $cursor->format('Y-m'),
                        'project'       => $item->project,
                        'role_group'    => $item->role_group,
                        'job_detail'    => $item->job_detail,
                        'days_in_month' => $daysInMonth,
                        'fte_per_day'   => $ftePerDay,
                        'fte_value'     => $fteMonth,

                        // ⭐ ฟิลด์ใหม่ที่เพิ่มเข้ามา
                        'hours_in_month' => $hoursInMonth,

                        'overlap_start' => $overlapStart->format('d M Y'),
                        'overlap_end'   => $overlapEnd->format('d M Y'),
                    ]);
                }

                $cursor->addMonth();
            }

        } catch (\Exception $e) {}
    }

    $monthlyFTE = $expanded->groupBy('month')->sortKeys();

    $chartLabels = $monthlyFTE->keys()->values();
    $chartData   = $monthlyFTE->map(fn($rows) => $rows->sum('fte_value'))->values();

    return compact(
        'person',
        'profile',
        'monthlyFTE',
        'chartLabels',
        'chartData',
    );
}

private function buildFteProjectData(Project $project): array
{
    $fteRoles = FteRole::with('project')
        ->where('project_id', $project->id)
        ->get();

    $expanded = collect();

    foreach ($fteRoles as $item) {
        try {
            $formatStart = strpos($item->start_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';
            $formatEnd   = strpos($item->end_date,   '/') !== false ? 'd/m/Y' : 'd-m-Y';

            $start = Carbon::createFromFormat($formatStart, $item->start_date)->startOfDay();
            $end   = Carbon::createFromFormat($formatEnd,   $item->end_date)->endOfDay();

            if ($start->gt($end)) continue;

            $totalDays  = $start->diffInDays($end) + 1;
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
                    $daysInMonth = $overlapStart->diffInDays($overlapEnd) + 1;
                    $fteMonth = $daysInMonth * $ftePerDay;
                    $hoursInMonth = $hoursPerDay * $daysInMonth;

                    $expanded->push((object)[
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

        $query = FteRole::with('project')->where('name', $name);

        if ($mode === 'year' && $year) {
            $yearAD = ($year > 2500) ? $year - 543 : $year;
            $query->whereRaw('RIGHT(start_date, 4) = ?', [$yearAD]);
        } elseif ($mode === 'month') {
            $now    = now();
            $month  = str_pad($now->month, 2, '0', STR_PAD_LEFT);
            $yearAD = $now->year;
            $query->whereRaw('RIGHT(start_date, 7) = ?', ["{$month}-{$yearAD}"]);
        } elseif ($mode === 'custom' && $start && $end) {
            try {
                $from = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->format('d-m-Y');
                $to   = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->format('d-m-Y');
                $query->whereRaw('STR_TO_DATE(start_date, "%d-%m-%Y") >= ?', [$from])
                      ->whereRaw('STR_TO_DATE(end_date, "%d-%m-%Y") <= ?', [$to]);
            } catch (\Exception $e) {}
        }

        $rows = $query->get()->groupBy(function ($item) {
            try {
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

}

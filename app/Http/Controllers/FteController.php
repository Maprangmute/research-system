<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FteRole;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class FteController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $fullName = trim($user->prefix . ' ' . $user->name);

        $mode   = $request->input('mode', 'month');
        $year   = $request->input('year');
        $person = $request->input('person');
        $start  = $request->input('start');
        $end    = $request->input('end');

        // Project ที่ user มีสิทธิ์
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

        // FTE Query
        $fteQuery = FteRole::query()
            ->select([
                'id',
                'project_id',
                'name',
                'fte_value',
                'start_date',
                'end_date',
                'role_group',
                'job_detail'
            ])
            ->whereIn('project_id', $projectIds);

        // รายปี
        if ($mode === 'year' && $year) {
            if ($year > 2500) $year -= 543;
            $fteQuery->whereRaw("STR_TO_DATE(start_date, '%d-%m-%Y') <= ?", [$year . "-12-31"])
                ->whereRaw("STR_TO_DATE(end_date, '%d-%m-%Y') >= ?", [$year . "-01-01"]);
        }

        // รายเดือน (เดือนนี้)
        if ($mode === 'month') {
            $now = now()->startOfMonth();
            $fteQuery->whereRaw("STR_TO_DATE(start_date, '%d-%m-%Y') <= ?", [$now->endOfMonth()->toDateString()])
                ->whereRaw("STR_TO_DATE(end_date, '%d-%m-%Y') >= ?", [$now->toDateString()]);
        }

        // custom range → overlap
        if ($mode === 'custom' && $start && $end) {
            try {
                $from = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->format('d-m-Y');
                $to   = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->format('d-m-Y');

                $fromDate = \Carbon\Carbon::createFromFormat('d-m-Y', $from)->toDateString();
                $toDate   = \Carbon\Carbon::createFromFormat('d-m-Y', $to)->toDateString();

                $fteQuery->whereRaw("STR_TO_DATE(start_date, '%d-%m-%Y') <= ?", [$toDate])
                    ->whereRaw("STR_TO_DATE(end_date, '%d-%m-%Y') >= ?", [$fromDate]);
            } catch (\Exception $e) {
                // ข้ามถ้าวันไม่ถูก format
            }
        }

        if ($person) $fteQuery->where('name', $person);

        $fteRows = $fteQuery->with(['project:id,project_name_th'])->get();

        // ==== Profile Map ====
        $users = \App\Models\User::select('prefix', 'name', 'profile_photo_path')->get();
        $profiles = $users->mapWithKeys(function ($user) {
            $full = trim($user->prefix . ' ' . $user->name);
            return [$full => $user->profile_photo_path];
        });

        // Group by name + month (expand across start→end, respect filter)
        $monthlyFTE = $fteRows->groupBy('name')->map(function ($rows) use ($mode, $year, $start, $end) {
            $expanded = collect();

            foreach ($rows as $item) {
                if (empty($item->start_date) || empty($item->end_date)) continue;

                try {
                    $formatStart = strpos($item->start_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';
                    $formatEnd   = strpos($item->end_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';

                    $startDate = \Carbon\Carbon::createFromFormat($formatStart, $item->start_date)->startOfMonth();
                    $endDate   = \Carbon\Carbon::createFromFormat($formatEnd, $item->end_date)->startOfMonth();

                    // filter: custom
                    if ($mode === 'custom' && $start && $end) {
                        try {
                            $from = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->startOfMonth();
                            $to   = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->startOfMonth();
                            if ($endDate < $from || $startDate > $to) continue;
                            if ($startDate < $from) $startDate = $from;
                            if ($endDate > $to) $endDate = $to;
                        } catch (\Exception $e) {
                        }
                    }

                    // filter: year
                    if ($mode === 'year' && $year) {
                        $yearAD = $year > 2500 ? $year - 543 : $year;
                        $from = \Carbon\Carbon::createFromDate($yearAD, 1, 1)->startOfMonth();
                        $to   = \Carbon\Carbon::createFromDate($yearAD, 12, 31)->startOfMonth();
                        if ($endDate < $from || $startDate > $to) continue;
                        if ($startDate < $from) $startDate = $from;
                        if ($endDate > $to) $endDate = $to;
                    }

                    // filter: month
                    if ($mode === 'month') {
                        $now = now()->startOfMonth();
                        if ($endDate < $now || $startDate > $now) continue;
                        $startDate = $now;
                        $endDate   = $now;
                    }

                    for ($date = $startDate->copy(); $date <= $endDate; $date->addMonth()) {
                        $expanded->push((object)[
                            'month'      => $date->format('Y-m'),
                            'project'    => $item->project,
                            'role_group' => $item->role_group,
                            'job_detail' => $item->job_detail,
                            'fte_value'  => $item->fte_value,
                            'start_date' => $item->start_date,
                            'end_date'   => $item->end_date,
                        ]);
                    }
                } catch (\Exception $e) {
                    // skip invalid date
                }
            }

            return $expanded->groupBy('month');
        });

        // Summary (ตาม monthlyFTE)
        $fteSummary = $monthlyFTE->map(function ($months, $name) {
            $total = $months->flatten()->sum('fte_value'); // รวมทุกเดือน (respect filter)
            $thisMonth = $months[now()->format('Y-m')] ?? collect();
            $fteThisMonth = $thisMonth->sum('fte_value');

            return (object)[
                'name'           => $name,
                'total_fte'      => $total,
                'fte_this_month' => $fteThisMonth,
            ];
        })->sortByDesc('total_fte')->values();

        // Years (พ.ศ.)
        $years = FteRole::selectRaw('DISTINCT RIGHT(start_date, 4) as year')
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->map(function ($year) {
                return (int)$year + 543;
            });

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


    public function detail(Request $request, $person)
    {
        $mode   = $request->input('mode', 'year');
        $year   = $request->input('year', now()->year + 543);
        $start  = $request->input('start');
        $end    = $request->input('end');

        // profile
        $profile = \App\Models\User::whereRaw("CONCAT(TRIM(prefix),' ',TRIM(name)) = ?", [$person])->first();

        // base query
        $fteQuery = FteRole::with('project')->where('name', $person);

        // filter
        if ($mode === 'year' && $year) {
            $yearAD = ($year > 2500) ? $year - 543 : $year;
            $fteQuery->whereRaw('RIGHT(start_date, 4) = ?', [$yearAD]);
        } elseif ($mode === 'month') {
            $now    = now();
            $month  = str_pad($now->month, 2, '0', STR_PAD_LEFT);
            $yearAD = $now->year;
            $fteQuery->whereRaw('RIGHT(start_date, 7) = ?', ["{$month}-{$yearAD}"]);
        } elseif ($mode === 'custom' && $start && $end) {
            try {
                $from = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->format('d-m-Y');
                $to   = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->format('d-m-Y');
                $fteQuery->whereRaw('STR_TO_DATE(start_date, "%d-%m-%Y") >= ?', [$from])
                         ->whereRaw('STR_TO_DATE(end_date, "%d-%m-%Y") <= ?', [$to]);
            } catch (\Exception $e) {}
        }

        $fteRows = $fteQuery->get();

        // group by month
        $fteMonthly = $fteRows->groupBy(function ($item) {
            try {
                $format = strpos($item->start_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';
                return \Carbon\Carbon::createFromFormat($format, $item->start_date)->format('Y-m');
            } catch (\Exception $e) {
                return 'invalid';
            }
        });

        // calendar events
        $calendarEvents = $fteRows->map(function ($row) {
            try {
                $format = strpos($row->start_date, '/') !== false ? 'd/m/Y' : 'd-m-Y';
                $start  = \Carbon\Carbon::createFromFormat($format, $row->start_date)->toDateString();
                $end    = \Carbon\Carbon::createFromFormat($format, $row->end_date)->addDay()->toDateString();
            } catch (\Exception $e) {
                $start = $end = null;
            }
            return [
                'title' => $row->project->project_name_th ?? '',
                'start' => $start,
                'end'   => $end,
            ];
        });

        // chart data (main person)
        $chartLabels = $fteMonthly->keys()->map(function ($key) {
            if (preg_match('/^\d{4}-\d{2}$/', $key)) {
                return \Carbon\Carbon::createFromFormat('Y-m', $key)->locale('th')->isoFormat('MMM YY');
            }
            return $key;
        });
        $chartData = $fteMonthly->map(fn($rows) => $rows->sum('fte_value'))->values();

        // all years
        $allYears = FteRole::where('name', $person)
            ->get()
            ->map(fn($f) => (int)substr($f->start_date, -4) + 543)
            ->unique()->sortDesc()->values();

        // all people for compare dropdown
        $allPeople = FteRole::distinct()->pluck('name')->sort()->values();

        return view('fte.detail', compact(
            'person',
            'fteRows',
            'fteMonthly',
            'calendarEvents',
            'chartLabels',
            'chartData',
            'profile',
            'hours',
            'mode',
            'year',
            'start',
            'end',
            'allYears',
            'allPeople'
        ));
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

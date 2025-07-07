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
    $user = Auth::user();
    $fullName = trim($user->prefix . ' ' . $user->name);

    $mode = $request->input('mode', 'month');
    $year = $request->input('year');
    $person = $request->input('person');
    $start = $request->input('start');
    $end = $request->input('end');

    // Project ที่ user มีสิทธิ์ (permission)
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

    // FTE Query หลัก
    $fteQuery = FteRole::query()
        ->select([
            'id', 'project_id', 'name', 'fte_value',
            'start_date', 'end_date',
            'role_group', 'job_detail'
        ])
        ->whereIn('project_id', $projectIds);

    // รายปี (value พ.ศ. → ค.ศ.)
    if ($mode === 'year' && $year) {
        if ($year > 2500) $year = $year - 543;
        $fteQuery->whereRaw('RIGHT(start_date, 4) = ?', [$year]);
    }

    // รายเดือน (เดือนนี้) [เทียบแบบ string]
    if ($mode === 'month') {
        $now = now();
        $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);
        $yearAD = $now->year; // ค.ศ.
        $fteQuery->whereRaw('RIGHT(start_date, 7) = ?', ["{$month}-{$yearAD}"]);
    }

    // ค้นหาช่วงวันที่ (วันที่ไทย → ค.ศ.)
    if ($mode === 'custom' && $start && $end) {
        $from = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->format('d-m-Y');
        $to = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->format('d-m-Y');
        $fteQuery->whereBetween(DB::raw("STR_TO_DATE(start_date, '%d-%m-%Y')"), [
            \Carbon\Carbon::createFromFormat('d-m-Y', $from)->toDateString(),
            \Carbon\Carbon::createFromFormat('d-m-Y', $to)->toDateString(),
        ]);
    }

    if ($person) $fteQuery->where('name', $person);

    $fteRows = $fteQuery->with(['project:id,project_name_th'])->get();

    // ==== Profile Map ====
    $users = \App\Models\User::select('prefix', 'name', 'profile_photo_path')->get();
    $profiles = $users->mapWithKeys(function($user) {
        $full = trim($user->prefix . ' ' . $user->name);
        return [$full => $user->profile_photo_path];
    });

    // Summary FTE
    $fteSummary = $fteRows->groupBy('name')->map(function($rows) {
        return (object)[
            'name' => $rows[0]->name,
            'total_fte' => $rows->sum('fte_value'),
            'fte_this_month' => $rows->where(function ($item) {
                // เดือนเดียวกับวันนี้? (เทียบ string)
                $now = now();
                $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);
                $yearAD = $now->year;
                return substr($item->start_date, 3) === "{$month}-{$yearAD}";
            })->sum('fte_value'),
        ];
    })->sortByDesc('total_fte')->values();

    // Group by name, month
    $monthlyFTE = $fteRows->groupBy('name')->map(function($rows) {
        return $rows->groupBy(function($item) {
            // Month-year grouping
            $dt = \Carbon\Carbon::createFromFormat('d-m-Y', $item->start_date);
            return $dt->format('Y-m');
        });
    });

    // ปี พ.ศ. ทั้งหมดที่มีใน FteRole
    $years = FteRole::selectRaw('DISTINCT RIGHT(start_date, 4) as year')
        ->orderByDesc('year')
        ->pluck('year')
        ->map(function ($year) { return (int)$year + 543; }); // แสดงเป็น พ.ศ.

    $personList = FteRole::whereIn('project_id', $projectIds)
        ->distinct()->orderBy('name')->pluck('name');

    return view('fte.index', [
        'fteSummary'  => $fteSummary,
        'years'       => $years,
        'monthlyFTE'  => $monthlyFTE,
        'personList'  => $personList,
        'profiles'    => $profiles,
        'mode'        => $mode,
        'year'        => $request->input('year'), // แสดงพ.ศ. เดิมในฟอร์ม
        'person'      => $person,
        'start'       => $start,
        'end'         => $end,
    ]);
}
public function detail(Request $request, $person)
{
    $mode = $request->input('mode', 'year');
    $year = $request->input('year', now()->year + 543); // Default พ.ศ.
    $start = $request->input('start');
    $end = $request->input('end');

    // หา User Profile
    $profile = \App\Models\User::whereRaw("CONCAT(TRIM(prefix), ' ', TRIM(name)) = ?", [$person])->first();

    // ดึงรายการ FTE ของ user
    $fteQuery = FteRole::with('project')->where('name', $person);

    // Filter (year/selectyear/custom)
    if ($mode === 'year') {
        $yearAD = ($year > 2500) ? $year - 543 : $year;
        $fteQuery->whereRaw('RIGHT(start_date, 4) = ?', [$yearAD]);
    } elseif ($mode === 'selectyear' && $year) {
        $yearAD = ($year > 2500) ? $year - 543 : $year;
        $fteQuery->whereRaw('RIGHT(start_date, 4) = ?', [$yearAD]);
    } elseif ($mode === 'custom' && $start && $end) {
        $from = \Carbon\Carbon::createFromFormat('d/m/Y', $start)->format('d-m-Y');
        $to   = \Carbon\Carbon::createFromFormat('d/m/Y', $end)->format('d-m-Y');
        $fteQuery->whereRaw('STR_TO_DATE(start_date, "%d-%m-%Y") >= ?', [$from])
                 ->whereRaw('STR_TO_DATE(end_date, "%d-%m-%Y") <= ?', [$to]);
    }

    $fteRows = $fteQuery->get();

    // Group by Month
    $fteMonthly = $fteRows->groupBy(function($item) {
        return \Carbon\Carbon::createFromFormat('d-m-Y', $item->start_date)->format('Y-m');
    });

    // Calendar Events
    $calendarEvents = $fteRows->map(function($row){
        return [
            'title' => $row->project->project_name_th ?? '',
            'start' => \Carbon\Carbon::createFromFormat('d-m-Y', $row->start_date)->toDateString(),
            'end'   => \Carbon\Carbon::createFromFormat('d-m-Y', $row->end_date)->addDay()->toDateString(), // FullCalendar ไม่รวม end
        ];
    });

    // Chart Data
    $chartLabels = $fteMonthly->keys()->map(function($key){
        return \Carbon\Carbon::createFromFormat('Y-m', $key)->locale('th')->isoFormat('MMM YY');
    });
    $chartData = $fteMonthly->map(function($rows){
        return $rows->sum('fte_value');
    })->values();

    // Years for Dropdown
    $allYears = FteRole::where('name', $person)
        ->get()
        ->map(function($f){ return (int)substr($f->start_date, -4) + 543; })
        ->unique()
        ->sortDesc()
        ->values();

    return view('fte.detail', compact(
        'person','fteRows','fteMonthly','calendarEvents','chartLabels','chartData','profile','mode','year','start','end','allYears'
    ));
}

    
}

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

        $projects = Project::query();
        if ($user->role !== 'admin') {
            $projects->where(function ($q) use ($fullName, $user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
        }

        if ($request->filled('year')) {
            $projects->where('start_year', $request->year);
        }

        $projectIds = $projects->pluck('id');

        $fteSummary = FteRole::select('name', DB::raw('SUM(fte_value) as total_fte'))
            ->whereIn('project_id', $projectIds)
            ->when($user->role !== 'admin', fn($q) => $q->where('name', $fullName))
            ->groupBy('name')
            ->orderByDesc('total_fte')
            ->get();

        // 🔍 Monthly Detail Data
        $monthlyFTE = FteRole::with('project')
            ->whereIn('project_id', $projectIds)
            ->when($request->filled('year'), fn($q) => $q->whereYear('start_date', $request->year))
            ->when($user->role !== 'admin', fn($q) => $q->where('name', $fullName))
            ->get()
            ->groupBy('name')
            ->map(function ($items) {
                return $items->groupBy(function ($item) {
                    return \Carbon\Carbon::parse($item->start_date)->format('Y-m');
                });
            });

        $years = Project::whereIn('id', $projectIds)
            ->select('start_year')->distinct()->orderBy('start_year', 'desc')->pluck('start_year');

        return view('fte.index', compact('fteSummary', 'years', 'monthlyFTE'));
    }

    /**
     * แสดงรายละเอียดของ FTE รายบุคคล
     */
    public function detail(Request $request)
    {
        $user = Auth::user();
        $name = $request->name;
        $month = $request->month;
        $year = $request->year;

        // 🔐 หา project ที่ user มีสิทธิ์
        $projectQuery = Project::query();
        if ($user->role !== 'admin') {
            $fullName = trim($user->prefix . ' ' . $user->name);
            $projectQuery->where(function ($q) use ($fullName, $user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
        }

        $projectIds = $projectQuery->pluck('id');

        // 🔍 ค้นหาเฉพาะ FTE ที่ชื่อถูกต้อง + มีสิทธิ์เห็น
        $query = FteRole::with('project')
            ->where('name', $name)
            ->whereIn('project_id', $projectIds);

        if ($month) $query->where('month', $month);
        if ($year) $query->where('year', $year);

        // ✅ ถ้าไม่ใช่ admin ต้องห้ามดูชื่ออื่น
        if ($user->role !== 'admin' && $name !== trim($user->prefix . ' ' . $user->name)) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงข้อมูลนี้');
        }

        $rows = $query->get();
        $totalFTE = $rows->sum('fte_value');

        return view('fte.detail', compact('rows', 'name', 'month', 'year', 'totalFTE'));
    }
}

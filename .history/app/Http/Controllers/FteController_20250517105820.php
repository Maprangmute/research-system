<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FteRole;
use App\Models\Project;

class FteController extends Controller
{
    public function index(Request $request)
    {
        // กรอง project id ที่ user สามารถเห็น
        $projects = Project::query();
        if (Auth::user()->role !== 'admin') {
            $projects->where('user_id', Auth::id());
        }

        if ($request->filled('year')) {
            $projects->where('start_year', $request->year);
        }

        $projectIds = $projects->pluck('id');

        // ดึง FTE และรวมค่า FTE ตามชื่อ
        $fteSummary = FteRole::select('name', 'role_group', DB::raw('SUM(fte_value) as total_fte'))
            ->whereIn('project_id', $projectIds)
            ->groupBy('name', 'role_group')
            ->orderBy('total_fte', 'desc')
            ->get();

        $years = Project::select('start_year')->distinct()->orderBy('start_year', 'desc')->pluck('start_year');

        return view('fte.index', compact('fteSummary', 'years'));
    }
    
    public function detail(Request $request)
    {
        $name = $request->name;
        $month = $request->month;
        $year = $request->year;

        $query = FteRole::with('project')->where('name', $name);

        if ($month) $query->where('month', $month);
        if ($year) $query->where('year', $year);

        $rows = $query->get();
        $totalFTE = $rows->sum('fte_value');

        return view('fte.detail', compact('rows', 'name', 'month', 'year', 'totalFTE'));
    }
}

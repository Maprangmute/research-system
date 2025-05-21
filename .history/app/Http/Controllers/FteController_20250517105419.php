<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FteRole;
use App\Models\Project;

class FteController extends Controller
{
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

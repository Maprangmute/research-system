<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use Illuminate\Http\Request;


class OverviewController extends Controller
{public function index(Request $request)
    {
        $projects = Project::query();
    
        if (Auth::user()->role !== 'admin') {
            $projects->where('user_id', Auth::id());
        }
    
        // Apply filters
        if ($request->filled('year')) {
            $projects->where('start_year', $request->year);
        }
    
        if ($request->filled('type')) {
            $projects->where('project_type', $request->type);
        }
    
        $filteredProjects = $projects->get();
    
        // ข้อมูลกราฟ
        $projectsByYear = $filteredProjects->groupBy('start_year')->map->count();
        $projectsByType = $filteredProjects->groupBy('project_type')->map->count();
    
        $timeline = $filteredProjects->map(function ($project) {
            return [
                'name' => $project->project_name_th,
                'start' => $project->start_year,
                'end' => $project->end_year ?? $project->start_year,
            ];
        });
    
        // สำหรับ dropdown filter
        $allYears = Project::select('start_year')->distinct()->pluck('start_year');
        $allTypes = Project::select('project_type')->distinct()->pluck('project_type');
    
        return view('overview.index', compact(
            'projectsByYear',
            'projectsByType',
            'timeline',
            'allYears',
            'allTypes',
            'request'
        ));
    }
    
}


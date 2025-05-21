<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Project;

class OverviewController extends Controller
{
    public function index()
    {
        $projects = Project::query();

        if (Auth::user()->role !== 'admin') {
            $projects->where('user_id', Auth::id());
        }

        $projects = $projects->get();

        // เตรียมข้อมูลสำหรับกราฟ
        $projectsByYear = $projects->groupBy('start_year')->map->count();
        $projectsByType = $projects->groupBy('project_type')->map->count();

        $timeline = $projects->map(function ($project) {
            return [
                'name' => $project->project_name_th,
                'start' => $project->start_year,
                'end' => $project->end_year ?? $project->start_year,
            ];
        });

        return view('overview.index', compact('projectsByYear', 'projectsByType', 'timeline'));
    }
}


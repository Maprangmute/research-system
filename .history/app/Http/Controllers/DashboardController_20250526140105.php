<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::select('project_name_th', 'budget', 'start_year')
            ->orderBy('start_year', 'desc')
            ->get();

        return view('dashboard', compact('projects'));
    }
}


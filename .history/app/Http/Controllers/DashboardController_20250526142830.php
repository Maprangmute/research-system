<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        $projects = Project::with('outputs') // <-- เพิ่ม outputs ด้วย
            ->select('id', 'project_name_th', 'funding_name', 'budget', 'start_year')
            ->get();

        return view('dashboard', compact('projects'));
    }
}

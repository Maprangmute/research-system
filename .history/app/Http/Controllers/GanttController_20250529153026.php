<?php

namespace App\Http\Controllers;

use App\Models\Project;

class GanttController extends Controller
{
    public function index(Project $project)
    {
        return view('gantt.index', compact('project'));
    }
}


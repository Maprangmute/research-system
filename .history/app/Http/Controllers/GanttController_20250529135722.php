<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class GanttController extends Controller
{
    public function index(Project $project)
{
    return view('gantt.index', compact('project'));
}

}

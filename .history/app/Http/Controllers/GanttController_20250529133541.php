<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class GanttController extends Controller
{
    public function index(Project $project)
    {
        $user = auth()->user();

        $query = $project->tasks();

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $tasks = $query->get();

        return view('gantt.index', compact('project', 'tasks'));
    }
}

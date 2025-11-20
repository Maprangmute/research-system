<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;


class GanttController extends Controller
{
    public function index(Project $project)
    {
        $fullName = trim(Auth::user()->prefix . ' ' . Auth::user()->name);
        $hasPermission = Auth::user()->role === 'admin'
            || Auth::id() === $project->user_id
            || $project->fteRoles->contains('name', $fullName);

        if (!$hasPermission) {
            abort(403);
        }
        return view('gantt.index', compact('project'));
    }
}


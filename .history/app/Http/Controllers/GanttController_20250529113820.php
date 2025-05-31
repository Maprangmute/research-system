<?php


// GanttController.php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\GanttTaskResource;

class GanttController extends Controller
{
    public function show($projectId)
    {
        $project = Project::findOrFail($projectId);
        return view('gantt.project', compact('project'));
    }

    public function tasks($projectId)
    {
        $tasks = Task::where('project_id', $projectId)->get();
        return GanttTaskResource::collection($tasks);
    }

    public function store(Request $request)
    {
        $task = Task::create($request->validate([
            'title' => 'required|string',
            'start' => 'required|date',
            'end' => 'required|date',
            'project_id' => 'required|exists:projects,id',
            'parent_id' => 'nullable|exists:tasks,id'
        ]) + ['user_id' => Auth::id()]);

        return response()->json(['data' => new GanttTaskResource($task)], 201);
    }

    public function update(Request $request, Task $task)
    {
        $task->update($request->only(['start', 'end', 'parent_id', 'title']));
        return response()->json(['data' => new GanttTaskResource($task)]);
    }
}
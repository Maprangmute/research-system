<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        $tasks = Task::where('project_id', $project->id)->get()->map(function ($task) {
            return [
                'id' => $task->id,
                'name' => $task->title,
                'start' => $task->start,
                'end' => $task->end,
                'progress' => $task->progress ?? 0,
                'dependencies' => $task->parent_id ? [$task->parent_id] : [],
            ];
        });

        return response()->json(['data' => $tasks]);
    }

    public function store(Request $request)
    {
        $task = Task::create($request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'project_id' => 'required|exists:projects,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'progress' => 'nullable|integer|min:0|max:100'
        ]));

        return response()->json(['data' => [
            'id' => $task->id,
            'name' => $task->title,
            'start' => $task->start,
            'end' => $task->end,
            'progress' => $task->progress ?? 0,
            'dependencies' => $task->parent_id ? [$task->parent_id] : []
        ]]);
    }

    public function update(Request $request, Task $task)
    {
        $task->update($request->only(['start', 'end', 'title', 'parent_id', 'progress']));

        return response()->json(['data' => [
            'id' => $task->id,
            'name' => $task->title,
            'start' => $task->start,
            'end' => $task->end,
            'progress' => $task->progress ?? 0,
            'dependencies' => $task->parent_id ? [$task->parent_id] : []
        ]]);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }
}
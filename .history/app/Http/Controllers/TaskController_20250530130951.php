<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index($projectId)
    {
        $tasks = Task::where('project_id', $projectId)->get()->map(function ($task) {
            return [
                'id' => $task->id,
                'text' => $task->title,
                'start_date' => $task->start->format('Y-m-d'),
                'end_date' => $task->end->format('Y-m-d'),
                'parent' => $task->parent_id ?? 0,
                'progress' => $task->progress,
                'status' => $task->status,
                'owner' => $task->owner,
                'open' => true
            ];
        });

        return response()->json(['data' => $tasks]);
    }

    public function bulkSave(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'tasks' => 'required|array',
        ]);

        $projectId = $validated['project_id'];

        DB::transaction(function () use ($validated, $projectId) {
            // ลบ tasks เก่าทั้งหมด
            Task::where('project_id', $projectId)->delete();

            foreach ($validated['tasks'] as $task) {
                Task::create([
                    'id' => $task['id'],
                    'title' => $task['text'],
                    'start' => $task['start_date'],
                    'end' => $task['end_date'],
                    'parent_id' => $task['parent'] !== 0 ? $task['parent'] : null,
                    'status' => $task['status'] ?? 'ongoing',
                    'progress' => $task['progress'] ?? 0,
                    'owner' => $task['owner'] ?? null,
                    'project_id' => $projectId,
                    'user_id' => auth()->id()
                ]);
            }
        });

        return response()->json(['message' => 'Tasks saved successfully']);
    }
}

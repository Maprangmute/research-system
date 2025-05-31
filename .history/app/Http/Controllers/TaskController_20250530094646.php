<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // public function index($projectId)
    // {
    //     $tasks = Task::where('project_id', $projectId)->get();

    //     return response()->json([
    //         "data" => $tasks->map(function ($task) {
    //             return [
    //                 "id" => $task->id,
    //                 "text" => $task->title,
    //                 "start_date" => $task->start->format('Y-m-d'),
    //                 "end_date" => $task->end->format('Y-m-d'),
    //                 "duration" => $task->start->diffInDays($task->end) + 1,
    //                 "parent" => $task->parent_id ?? 0,
    //                 "progress" => ($task->progress ?? 0) / 100,
    //             ];
    //         })
    //     ]);
    // }
    

    public function store(Request $request)
    {
        $data = $request->all();
        $task = Task::create([
            'title' => $data['text'],
            'start' => $data['start_date'],
            'end' => date('Y-m-d', strtotime("+" . ((int)$data['duration'] - 1) . " days", strtotime($data['start_date']))),
            'project_id' => $data['project_id'],
            'parent_id' => $data['parent'] ?? null,
            'progress' => $data['progress'] * 100,
        ]);

        return response()->json(["action" => "inserted", "tid" => $task->id]);
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->all();
        $task->update([
            'title' => $data['text'],
            'start' => $data['start_date'],
            'end' => date('Y-m-d', strtotime("+" . ((int)$data['duration'] - 1) . " days", strtotime($data['start_date']))),
            'parent_id' => $data['parent'] ?? null,
            'progress' => $data['progress'] * 100,
        ]);

        return response()->json(["action" => "updated"]);
    }


    public function move(Request $request, Task $task)
    {
        $data = $request->validate([
            'parent_id' => 'nullable|exists:tasks,id',
            'order' => 'nullable|integer'
        ]);

        $task->update($data);
        return response()->json(['message' => 'Task moved']);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate(['status' => 'required|in:ongoing,done']);
        $task->status = $request->status;
        $task->save();
        return response()->json(['message' => 'Status updated']);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }
}

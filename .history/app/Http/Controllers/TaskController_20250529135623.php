<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
 public function index($projectId)
{
    $tasks = Task::where('project_id', $projectId)->get();

    $data = $tasks->map(function ($task) {
        return [
            'id' => $task->id,
            'name' => $task->title,
            'values' => [[
                'from' => $task->start . ' 00:00',
                'to' => $task->end . ' 23:59',
                'label' => $task->title,
                'customClass' => $task->status === 'done' ? 'ganttGreen' : 'ganttBlue'
            ]]
        ];
    });

    return response()->json($data);
}




    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'project_id' => 'required|exists:projects,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'progress' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|in:ongoing,done',
        ]);

        $data['user_id'] = Auth::id();
        $data['order'] = Task::where('project_id', $data['project_id'])
                            ->where('parent_id', $data['parent_id'])->count();

        $task = Task::create($data);
        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        $task->update($request->only(['title', 'start', 'end', 'parent_id', 'progress', 'status']));
        return response()->json($task);
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

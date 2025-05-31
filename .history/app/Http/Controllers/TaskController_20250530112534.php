<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{


    public function store(Request $request)
    {
        $task = Task::create([
            'title' => $request->text,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'project_id' => $request->project_id,
            'user_id' => Auth::id(),
            'parent_id' => $request->parent != 0 ? $request->parent : null,
            'status' => $request->status ?? 'ongoing',
            'progress' => $request->progress ?? 0,
            'owner' => $request->owner ?? null,
            'order' => $request->order ?? 0,
        ]);

        return response()->json(['action' => 'inserted', 'tid' => $task->id]);
    }

    public function update(Request $request, Task $task)
    {
        $task->update([
            'title' => $request->text,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'parent_id' => $request->parent != 0 ? $request->parent : null,
            'status' => $request->status ?? $task->status,
            'progress' => $request->progress ?? $task->progress,
            'owner' => $request->owner ?? $task->owner,
            'order' => $request->order ?? $task->order,
        ]);

        return response()->json(['action' => 'updated']);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['action' => 'deleted']);
    }


    public function storeLink(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'source' => 'required|exists:tasks,id',
            'target' => 'required|exists:tasks,id',
            'type' => 'required|string|max:10',
        ]);

        $link = TaskLink::create($validated);
        return response()->json(["action" => "inserted", "tid" => $link->id]);
    }

    public function destroyLink($id)
    {
        TaskLink::destroy($id);
        return response()->json(["action" => "deleted"]);
    }
}

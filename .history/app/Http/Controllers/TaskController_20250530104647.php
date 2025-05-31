<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
                'progress' => 0,
                'status' => $task->status,
                'owner' => $task->owner,
            ];
        });

        $links = TaskLink::where('project_id', $projectId)->get()->map(function ($link) {
            return [
                'id' => $link->id,
                'source' => $link->source,
                'target' => $link->target,
                'type' => $link->type,
            ];
        });

        return response()->json(['data' => $tasks, 'links' => $links]);
    }

    public function store(Request $request)
    {
        $task = Task::create($request->all());
        return response()->json(["action" => "inserted", "tid" => $task->id]);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->update($request->all());
        return response()->json(["action" => "updated"]);
    }

    public function destroy($id)
    {
        Task::destroy($id);
        return response()->json(["action" => "deleted"]);
    }

    public function storeLink(Request $request)
    {
        $link = TaskLink::create($request->all());
        return response()->json(["action" => "inserted", "tid" => $link->id]);
    }

    public function destroyLink($id)
    {
        TaskLink::destroy($id);
        return response()->json(["action" => "deleted"]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;


class TaskController extends Controller
{
    public function index($projectId)
    {
        $tasks = Task::where('project_id', $projectId)->get();

        $data = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'text' => $task->title,
                'start_date' => $task->start->format('Y-m-d'),
                'end_date' => $task->end->format('Y-m-d'),
                'parent' => $task->parent_id ?? 0,
                'progress' => $task->progress,
                'status' => $task->status,
                'owner' => $task->owner,
                'order' => $task->order,
                'open' => true
            ];
        });

        return response()->json(['data' => $data]);
    }

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

// public function bulkSave(Request $request)
// {
//     $projectId = $request->project_id;
//     $tasks = $request->tasks;

//     foreach ($tasks as $taskData) {
//         Task::updateOrCreate(
//             ['id' => $taskData['id']],
//             [
//                 'project_id' => $projectId,
//                 'title' => $taskData['text'],
//                 'start' => $taskData['start_date'],
//                 'end' => $taskData['end_date'],
//                 'parent_id' => $taskData['parent'] != 0 ? $taskData['parent'] : null,
//                 'status' => $taskData['status'] ?? 'ongoing',
//                 'progress' => $taskData['progress'] ?? 0,
//                 'owner' => $taskData['owner'] ?? null,
//                 'user_id' => auth()->id(), // หรือปรับตาม logic ของคุณ
//             ]
//         );
//     }

//     return response()->json(['status' => 'success']);
// }

use App\Models\TaskLink;

public function bulkSave(Request $request)
{
    $tasks = json_decode($request->get('tasks'), true);
    $links = json_decode($request->get('links'), true);
    $projectId = $request->project_id;

    if (!$projectId) {
        return response()->json(['status' => 'error', 'message' => 'Project ID missing'], 400);
    }

    // ✅ Save Tasks
    foreach ($tasks ?? [] as $taskData) {
        if (empty($taskData['start_date']) || empty($taskData['end_date'])) continue;

        $data = [
            'project_id' => $projectId,
            'title' => $taskData['text'] ?? 'ไม่มีชื่อ',
            'start' => $taskData['start_date'],
            'end' => $taskData['end_date'],
            'parent_id' => is_numeric($taskData['parent']) ? $taskData['parent'] : null,
            'status' => $taskData['status'] ?? 'ongoing',
            'progress' => intval($taskData['progress'] ?? 0),
            'owner' => $taskData['owner'] ?? null,
            'user_id' => auth()->id(),
            'order' => $taskData['order'] ?? 0,
        ];

        if (!empty($taskData['id']) && \App\Models\Task::where('id', $taskData['id'])->exists()) {
            \App\Models\Task::where('id', $taskData['id'])->update($data);
        } else {
            \App\Models\Task::create($data);
        }
    }

    // ✅ Save Links
    TaskLink::where('project_id', $projectId)->delete(); // ลบของเดิมทั้งหมดก่อน
    foreach ($links ?? [] as $link) {
        TaskLink::create([
            'project_id' => $projectId,
            'source' => $link['source'],
            'target' => $link['target'],
            'type' => $link['type']
        ]);
    }

    return response()->json(['status' => 'success']);
}


public function loadData($projectId)
{
    $tasks = Task::where('project_id', $projectId)->get();
    $links = TaskLink::where('project_id', $projectId)->get();

    return response()->json([
        'data' => $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'text' => $task->title,
                'start_date' => $task->start->format('Y-m-d'),
                'end_date' => $task->end->format('Y-m-d'),
                'parent' => $task->parent_id ?? 0,
                'status' => $task->status,
                'progress' => $task->progress,
                'owner' => $task->owner,
                'open' => true
            ];
        }),
        'links' => $links->map(function ($link) {
            return [
                'id' => $link->id,
                'source' => $link->source,
                'target' => $link->target,
                'type' => $link->type,
            ];
        })
    ]);
}







    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['action' => 'deleted']);
    }

    public function storeLink(Request $request)
    {
        $link = TaskLink::create([
            'project_id' => $request->project_id,
            'source' => $request->source,
            'target' => $request->target,
            'type' => $request->type ?? 0
        ]);

        return response()->json(['action' => 'inserted', 'tid' => $link->id]);
    }

    public function destroyLink($id)
    {
        TaskLink::where('id', $id)->delete();
        return response()->json(['action' => 'deleted']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLink;
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

    public function bulkSave(Request $request)
{
    $tasks = json_decode($request->get('tasks'), true);
    $projectId = $request->project_id;

    if (!$tasks || !$projectId) {
        return response()->json(['status' => 'error', 'message' => 'Invalid data'], 400);
    }

    foreach ($tasks as $taskData) {
        try {
            $data = [
                'project_id' => $projectId,
                'title' => $taskData['text'] ?? 'ไม่มีชื่อ',
                'start' => $taskData['start_date'],
                'end' => $taskData['end_date'],
                'parent_id' => $taskData['parent'] ?? null,
                'status' => $taskData['status'] ?? 'ongoing',
                'progress' => intval($taskData['progress'] ?? 0),
                'owner' => $taskData['owner'] ?? null,
                'user_id' => auth()->id(),
                'order' => $taskData['order'] ?? 0,
            ];

            if (isset($taskData['id']) && $taskData['id']) {
                Task::where('id', $taskData['id'])->update($data);
            } else {
                Task::create($data);
            }
        } catch (\Exception $e) {
            \Log::error('[GANTT Bulk Save] Task error: ' . $e->getMessage(), $taskData);
            return response()->json(['status' => 'error', 'message' => 'Save failed. Check log.'], 500);
        }
    }

    return response()->json(['status' => 'success']);
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

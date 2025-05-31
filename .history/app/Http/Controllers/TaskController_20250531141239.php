<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;



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
    $links = json_decode($request->get('links'), true);
    $projectId = $request->project_id;

    if (!$tasks || !$projectId) {
        return response()->json(['status' => 'error', 'message' => 'Invalid data'], 400);
    }

    DB::beginTransaction();

    try {
        foreach ($tasks as $taskData) {
            if (empty($taskData['start_date']) || empty($taskData['end_date'])) {
                continue;
            }

            $start = \Carbon\Carbon::parse($taskData['start_date'])->format('Y-m-d');
            $end = \Carbon\Carbon::parse($taskData['end_date'])->format('Y-m-d');

            $data = [
                'project_id' => $projectId,
                'title' => $taskData['text'] ?? 'ไม่มีชื่อ',
                'start' => $start,
                'end' => $end,
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

        // ✅ ลบ link เดิมทั้งหมดของโปรเจกต์ แล้วเพิ่มใหม่
        TaskLink::where('project_id', $projectId)->delete();

        foreach ($links as $linkData) {
            TaskLink::create([
                'project_id' => $projectId,
                'source' => $linkData['source'],
                'target' => $linkData['target'],
                'type' => $linkData['type']
            ]);
        }

        DB::commit();
        return response()->json(['status' => 'success']);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('[GANTT Bulk Save] Error: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Save failed'], 500);
    }
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

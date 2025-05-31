<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // ✅ โหลด Task ทั้งหมดของโปรเจกต์
    public function index($projectId)
    {
        $tasks = Task::where('project_id', $projectId)->get();

        return response()->json([
            "data" => $tasks->map(function ($task) {
                return [
                    "id" => $task->id,
                    "text" => $task->title,
                    "start_date" => $task->start->format('Y-m-d'),
                    "end_date" => $task->end->format('Y-m-d'),
                    "parent" => $task->parent_id ?? 0,
                    "progress" => ($task->progress ?? 0) / 100,
                    "status" => $task->status ?? 'ongoing',
                ];
            })
        ]);
    }

    // ✅ เพิ่มงานใหม่
    public function store(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'project_id' => 'required|exists:projects,id',
            'parent' => 'nullable|integer',
            'progress' => 'nullable|numeric|min:0|max:1',
            'status' => 'nullable|in:ongoing,done',
        ]);

        $task = Task::create([
            'title' => $data['text'],
            'start' => $data['start_date'],
            'end' => $data['end_date'],
            'project_id' => $data['project_id'],
            'parent_id' => $data['parent'] ?? null,
            'progress' => ($data['progress'] ?? 0) * 100,
            'status' => $data['status'] ?? 'ongoing',
        ]);

        return response()->json(["action" => "inserted", "tid" => $task->id]);
    }

    // ✅ อัปเดตงาน
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'text' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'parent' => 'nullable|integer',
            'progress' => 'nullable|numeric|min:0|max:1',
            'status' => 'nullable|in:ongoing,done',
        ]);

        $task->update([
            'title' => $data['text'] ?? $task->title,
            'start' => $data['start_date'] ?? $task->start,
            'end' => $data['end_date'] ?? $task->end,
            'parent_id' => $data['parent'] ?? $task->parent_id,
            'progress' => isset($data['progress']) ? $data['progress'] * 100 : $task->progress,
            'status' => $data['status'] ?? $task->status,
        ]);

        return response()->json(["action" => "updated"]);
    }

    // ✅ ลบงาน (ถ้าจำเป็น)
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(["action" => "deleted"]);
    }
}

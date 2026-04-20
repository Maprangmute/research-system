<?php

namespace App\Http\Controllers;

use App\Models\FteRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;



class TaskController extends Controller
{
    public function index($projectId)
    {
        $project = $this->resolveProjectFromParam($projectId);
        $this->authorizeProjectAccess($project);

        $tasks = Task::where('project_id', $project->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $data = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'text' => $task->title,
                'start_date' => $task->start->format('Y-m-d'),
                'end_date' => $task->end->format('Y-m-d'),
                'parent' => $task->parent_id ?? 0,
                'progress' => $task->progress,
                'status' => $task->status,
                'workflow_status' => $this->normalizeWorkflowStatus($task->workflow_status ?? $task->status, (int) $task->progress),
                'owner' => $task->owner,
                'order' => $task->order,
                'open' => true
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function fteTasks($projectId)
    {
        $project = $this->resolveProjectFromParam($projectId);
        $this->authorizeProjectAccess($project);

        $rows = FteRole::query()
            ->where('project_id', $project->id)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();

        if ($rows->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $fteTasks = [];
        $minStart = null;
        $maxEnd = null;
        $today = now()->startOfDay();

        foreach ($rows as $row) {
            $start = $this->parseFlexibleDate($row->start_date);
            $end = $this->parseFlexibleDate($row->end_date);
            if (! $start || ! $end) {
                continue;
            }

            if ($start->gt($end)) {
                [$start, $end] = [$end, $start];
            }

            $minStart = $minStart ? $minStart->min($start) : $start->copy();
            $maxEnd = $maxEnd ? $maxEnd->max($end) : $end->copy();

            $fteProgress = (float) ($row->fte_value_research ?? $row->fte_value ?? 0);
            if ($fteProgress <= 1) {
                $fteProgress *= 100;
            }
            $fteProgress = max(0.0, min(100.0, $fteProgress));

            $name = trim((string) ($row->name ?? ''));
            $taskDetail = trim((string) ($row->task_detail ?? ''));
            $roleGroup = trim((string) ($row->role_group ?? ''));

            $title = $taskDetail !== ''
                ? $taskDetail
                : ($roleGroup !== '' ? $roleGroup : 'ภาระงาน #'.$row->id);

            $fteTasks[] = [
                'id' => 'fte-'.$row->id,
                'text' => $title,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'parent' => 'fte-root',
                'progress' => $fteProgress,
                'status' => $end->lt($today) ? 'done' : 'ongoing',
                'workflow_status' => $end->lt($today) ? 'completed' : 'in_progress',
                'owner' => $name,
                'order' => (int) $row->id,
                'open' => true,
                'readonly' => true,
                'is_fte' => true,
                'type' => 'fte',
            ];
        }

        if ($fteTasks === []) {
            return response()->json(['data' => []]);
        }

        $rootStart = ($minStart ?? now())->format('Y-m-d');
        $rootEnd = ($maxEnd ?? now())->format('Y-m-d');

        array_unshift($fteTasks, [
            'id' => 'fte-root',
            'text' => 'งานภาระงาน (จาก FTE)',
            'start_date' => $rootStart,
            'end_date' => $rootEnd,
            'parent' => 0,
            'progress' => 0,
            'status' => 'ongoing',
            'workflow_status' => 'in_progress',
            'owner' => '',
            'order' => -1,
            'open' => true,
            'readonly' => true,
            'is_fte' => true,
            'type' => 'summary',
        ]);

        return response()->json(['data' => $fteTasks]);
    }

    public function store(Request $request)
    {
        $progress = $this->normalizeProgress($request->progress ?? 0);
        $workflowStatus = $this->normalizeWorkflowStatus($request->workflow_status ?? $request->status, $progress);
        $supportsWorkflowStatus = $this->supportsWorkflowStatus();

        $payload = [
            'title' => $request->text,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'project_id' => $request->project_id,
            'user_id' => Auth::id(),
            'parent_id' => $request->parent != 0 ? $request->parent : null,
            'status' => $workflowStatus === 'completed' ? 'done' : 'ongoing',
            'progress' => $progress,
            'owner' => $request->owner ?? null,
            'order' => $request->order ?? 0,
        ];

        if ($supportsWorkflowStatus) {
            $payload['workflow_status'] = $workflowStatus;
        }

        $task = Task::create($payload);

        return response()->json(['action' => 'inserted', 'tid' => $task->id]);
        
    }

public function update(Request $request, Task $task)
{
    $progress = $this->normalizeProgress($request->progress ?? $task->progress);
    $workflowStatus = $this->normalizeWorkflowStatus($request->workflow_status ?? $request->status, $progress);
    $supportsWorkflowStatus = $this->supportsWorkflowStatus();

    $payload = [
        'title' => $request->text,
        'start' => $request->start_date,
        'end' => $request->end_date,
        'parent_id' => $request->parent != 0 ? $request->parent : null,
        'status' => $workflowStatus === 'completed' ? 'done' : 'ongoing',
        'progress' => $progress,
        'owner' => $request->owner ?? $task->owner,
        'order' => $request->order ?? $task->order,
    ];

    if ($supportsWorkflowStatus) {
        $payload['workflow_status'] = $workflowStatus;
    }

    $task->update($payload);

    return response()->json(['action' => 'updated']);
}


// public function bulkSave(Request $request)
// {
//     $tasks = json_decode($request->get('tasks'), true);
//     $links = json_decode($request->get('links'), true);
//     $projectId = $request->project_id;

//     if (!$tasks || !$projectId) {
//         return response()->json(['status' => 'error', 'message' => 'Invalid data'], 400);
//     }

//     DB::beginTransaction();

//     try {
//         foreach ($tasks as $taskData) {
//             if (empty($taskData['start_date']) || empty($taskData['end_date'])) {
//                 continue;
//             }

//             $start = \Carbon\Carbon::parse($taskData['start_date'])->format('Y-m-d');
//             $end = \Carbon\Carbon::parse($taskData['end_date'])->format('Y-m-d');

//             $data = [
//                 'project_id' => $projectId,
//                 'title' => $taskData['text'] ?? 'ไม่มีชื่อ',
//                 'start' => $start,
//                 'end' => $end,
//                 'parent_id' => is_numeric($taskData['parent']) ? $taskData['parent'] : null,
//                 'status' => $taskData['status'] ?? 'ongoing',
//                 'progress' => intval($taskData['progress'] ?? 0),
//                 'owner' => $taskData['owner'] ?? null,
//                 'user_id' => auth()->id(),
//                 'order' => $taskData['order'] ?? 0,
//             ];

//             if (!empty($taskData['id']) && \App\Models\Task::where('id', $taskData['id'])->exists()) {
//                 \App\Models\Task::where('id', $taskData['id'])->update($data);
//             } else {
//                 \App\Models\Task::create($data);
//             }
//         }

//         // ✅ ลบ link เดิมทั้งหมดของโปรเจกต์ แล้วเพิ่มใหม่
//         TaskLink::where('project_id', $projectId)->delete();

//         foreach ($links as $linkData) {
//             TaskLink::create([
//                 'project_id' => $projectId,
//                 'source' => $linkData['source'],
//                 'target' => $linkData['target'],
//                 'type' => $linkData['type']
//             ]);
//         }

//         DB::commit();
//         return response()->json(['status' => 'success']);

//     } catch (\Exception $e) {
//         DB::rollBack();
//         \Log::error('[GANTT Bulk Save] Error: ' . $e->getMessage());
//         return response()->json(['status' => 'error', 'message' => 'Save failed'], 500);
//     }
// }



public function bulkSave(Request $request)
{
    $projectParam = $request->input('project_id');
    $project = $this->resolveProjectFromParam($projectParam);
    $this->authorizeProjectAccess($project);

    $projectId = $project->id;
    $supportsWorkflowStatus = $this->supportsWorkflowStatus();
    $tasks = json_decode((string) $request->input('tasks', '[]'), true) ?: [];
    $links = json_decode((string) $request->input('links', '[]'), true) ?: [];
    $deletedIds = json_decode((string) $request->input('deleted_ids', '[]'), true) ?: [];
    $idMap = [];

    DB::transaction(function () use ($projectId, $tasks, $links, $deletedIds, &$idMap, $supportsWorkflowStatus) {
        $safeDeletedIds = collect($deletedIds)
            ->filter(fn($id) => $this->extractPositiveInt($id) !== null)
            ->map(fn($id) => $this->extractPositiveInt($id))
            ->values();

        if ($safeDeletedIds->isNotEmpty()) {
            Task::query()
                ->where('project_id', $projectId)
                ->whereIn('id', $safeDeletedIds->all())
                ->delete();
        }

        $knownProjectTaskIds = Task::query()
            ->where('project_id', $projectId)
            ->pluck('id')
            ->mapWithKeys(static fn($id) => [(string) $id => (int) $id])
            ->all();

        foreach ($tasks as $index => $taskData) {
            $rawId = $taskData['id'] ?? null;
            $clientIdKey = trim((string) ($rawId ?? ''));
            if ($clientIdKey === '') {
                $clientIdKey = 'new:'.$index;
            }

            if (is_string($rawId) && str_starts_with($rawId, 'fte-')) {
                continue;
            }

            $title = trim((string) ($taskData['text'] ?? ''));
            if ($title === '') {
                continue;
            }

            $start = $this->parseFlexibleDate($taskData['start_date'] ?? null);
            $end = $this->parseFlexibleDate($taskData['end_date'] ?? null);
            if (! $start || ! $end) {
                continue;
            }
            if ($start->gt($end)) {
                [$start, $end] = [$end, $start];
            }

            $progress = $this->normalizeProgress($taskData['progress'] ?? 0);
            $workflowStatus = $this->normalizeWorkflowStatus($taskData['workflow_status'] ?? ($taskData['status'] ?? null), $progress);
            $status = $workflowStatus === 'completed' ? 'done' : 'ongoing';

            $payload = [
                'project_id' => $projectId,
                'title' => $title,
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'parent_id' => null,
                'status' => $status,
                'progress' => $progress,
                'owner' => trim((string) ($taskData['owner'] ?? '')) ?: null,
                'user_id' => auth()->id(),
                'order' => is_numeric($taskData['order'] ?? null) ? (int) $taskData['order'] : (int) $index,
            ];

            if ($supportsWorkflowStatus) {
                $payload['workflow_status'] = $workflowStatus;
            }

            $mappedTaskId = $this->extractPositiveInt(
                Cache::get($this->temporaryTaskMapCacheKey($projectId, $clientIdKey))
            );

            if ($mappedTaskId) {
                $mappedTask = Task::query()
                    ->where('id', $mappedTaskId)
                    ->where('project_id', $projectId)
                    ->first();

                if ($mappedTask) {
                    $mappedTask->update($payload);
                    $idMap[$clientIdKey] = (int) $mappedTask->id;
                    $knownProjectTaskIds[(string) $mappedTask->id] = (int) $mappedTask->id;
                    continue;
                }
            }

            $numericId = $this->extractPositiveInt($rawId);
            if ($numericId) {
                $existing = Task::query()
                    ->where('id', $numericId)
                    ->where('project_id', $projectId)
                    ->first();

                if ($existing) {
                    $existing->update($payload);
                    $idMap[$clientIdKey] = (int) $existing->id;
                    $knownProjectTaskIds[(string) $existing->id] = (int) $existing->id;
                    continue;
                }
            }

            $newTask = new Task();
            $newTask->fill($payload);
            $newTask->save();

            $idMap[$clientIdKey] = (int) $newTask->id;
            $knownProjectTaskIds[(string) $newTask->id] = (int) $newTask->id;
        }

        foreach ($tasks as $index => $taskData) {
            $clientIdKey = trim((string) ($taskData['id'] ?? ''));
            if ($clientIdKey === '') {
                $clientIdKey = 'new:'.$index;
            }

            if (! isset($idMap[$clientIdKey])) {
                continue;
            }

            $resolvedParent = $this->resolveTaskReference(
                $taskData['parent'] ?? null,
                $idMap,
                $knownProjectTaskIds
            );

            Task::query()
                ->where('project_id', $projectId)
                ->where('id', $idMap[$clientIdKey])
                ->update(['parent_id' => $resolvedParent]);
        }

        TaskLink::query()
            ->where('project_id', $projectId)
            ->delete();

        foreach ($links as $link) {
            $source = $this->resolveTaskReference(
                $link['source'] ?? null,
                $idMap,
                $knownProjectTaskIds
            );
            $target = $this->resolveTaskReference(
                $link['target'] ?? null,
                $idMap,
                $knownProjectTaskIds
            );

            if (! $source || ! $target || $source === $target) {
                continue;
            }

            TaskLink::create([
                'project_id' => $projectId,
                'source' => $source,
                'target' => $target,
                'type' => $this->normalizeLinkType($link['type'] ?? 0),
            ]);
        }
    });

    foreach ($idMap as $clientIdKey => $resolvedTaskId) {
        $safeClientId = trim((string) $clientIdKey);
        $safeTaskId = $this->extractPositiveInt($resolvedTaskId);

        if ($safeClientId === '' || ! $safeTaskId) {
            continue;
        }

        Cache::put(
            $this->temporaryTaskMapCacheKey($projectId, $safeClientId),
            $safeTaskId,
            now()->addHours(6)
        );
    }

    $savedAt = now()->toIso8601String();

    return response()->json([
        'status' => 'success',
        'saved_at' => $savedAt,
        'id_map' => $idMap,
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

    private function extractPositiveInt($value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $id = (int) $value;

        return $id > 0 ? $id : null;
    }

    private function normalizeProgress($value): int
    {
        $progress = (float) $value;
        if ($progress <= 1) {
            $progress *= 100;
        }

        return (int) max(0, min(100, round($progress)));
    }

    private function normalizeWorkflowStatus($value, int $progress = 0): string
    {
        $status = mb_strtolower(trim((string) $value), 'UTF-8');

        $map = [
            'in_progress' => 'in_progress',
            'completed' => 'completed',
            'delayed' => 'delayed',
            'starting' => 'starting',
            'paused' => 'paused',
            'not_started' => 'not_started',
            'ongoing' => 'in_progress',
            'done' => 'completed',
        ];

        if (isset($map[$status])) {
            return $map[$status];
        }

        return $progress >= 100 ? 'completed' : 'in_progress';
    }

    private function supportsWorkflowStatus(): bool
    {
        return Schema::hasColumn('tasks', 'workflow_status');
    }

    private function resolveTaskReference($rawId, array $idMap, array $knownTaskIds): ?int
    {
        $key = trim((string) $rawId);
        if ($key === '' || $key === '0') {
            return null;
        }

        if (isset($idMap[$key])) {
            return (int) $idMap[$key];
        }

        $numericId = $this->extractPositiveInt($rawId);
        if (! $numericId) {
            return null;
        }

        if (! isset($knownTaskIds[(string) $numericId])) {
            return null;
        }

        return $numericId;
    }

    private function normalizeLinkType($rawType): int
    {
        if (is_numeric($rawType)) {
            $value = (int) $rawType;
            if ($value >= 0 && $value <= 3) {
                return $value;
            }
        }

        $type = mb_strtolower(trim((string) $rawType), 'UTF-8');
        return match ($type) {
            'e2s' => 0,
            's2s' => 1,
            'e2e' => 2,
            's2e' => 3,
            default => 0,
        };
    }

    private function temporaryTaskMapCacheKey(int $projectId, string $clientIdKey): string
    {
        return 'gantt:task-map:'.$projectId.':'.sha1($clientIdKey);
    }

    private function resolveProjectFromParam($projectParam): Project
    {
        $lookup = trim((string) $projectParam);
        if ($lookup === '') {
            abort(404);
        }

        $project = null;
        if (ctype_digit($lookup)) {
            $project = Project::query()->whereKey((int) $lookup)->first();
        }

        if (! $project) {
            $project = Project::query()
                ->whereRaw('LOWER(TRIM(project_code)) = ?', [mb_strtolower($lookup, 'UTF-8')])
                ->first();
        }

        if (! $project) {
            abort(404);
        }

        return $project;
    }

    private function authorizeProjectAccess(Project $project): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if ($user->isAdminLike()) {
            return;
        }

        $fullName = trim(($user->prefix ?? '').' '.$user->name);
        $hasPermission = (int) $user->id === (int) $project->user_id
            || $project->fteRoles()->where('name', $fullName)->exists();

        if (! $hasPermission) {
            abort(403);
        }
    }

    private function parseFlexibleDate($value): ?Carbon
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $raw)->startOfDay();
            } catch (\Throwable $e) {
                // continue
            }
        }

        try {
            return Carbon::parse($raw)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }
}

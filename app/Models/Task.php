<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectLog;

class Task extends Model
{
    protected $fillable = [
        'title', 'start', 'end', 'project_id', 'user_id',
        'parent_id', 'status', 'progress', 'owner'
    ];

    protected $casts = [
        'start' => 'date',
        'end' => 'date',
    ];

    // ==== Log ทุก action ที่เกี่ยวกับ Task ====
    protected static function booted()
    {
        // Log create
        static::created(function ($task) {
            ProjectLog::create([
                'project_id'      => $task->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $task->project->project_name_th ?? '',
                'action'          => 'task_create',
                'changes'         => ProjectLog::sanitizeChanges($task->toArray()),
            ]);
        });

        // Log update
        static::updating(function ($task) {
            $changes = ProjectLog::buildUpdateChanges($task);
            if (!empty($changes)) {
                ProjectLog::create([
                    'project_id'      => $task->project_id,
                    'user_id'         => Auth::id() ?? null,
                    'project_name_th' => $task->project->project_name_th ?? '',
                    'action'          => 'task_update',
                    'changes'         => $changes,
                ]);
            }
        });

        // Log delete
        static::deleting(function ($task) {
            ProjectLog::create([
                'project_id'      => $task->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $task->project->project_name_th ?? '',
                'action'          => 'task_delete',
                'changes'         => ProjectLog::sanitizeChanges($task->toArray()),
            ]);
        });
    }

    // ===== Relation =====
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Task::class, 'parent_id');
    }
}

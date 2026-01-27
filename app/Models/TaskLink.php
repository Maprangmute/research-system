<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectLog;

class TaskLink extends Model
{
    protected $fillable = [
        'project_id',
        'source',
        'target',
        'type',
    ];

    // ==== Log ทุก action ที่เกี่ยวกับ TaskLink ====
    protected static function booted()
    {
        // Log create
        static::created(function ($link) {
            ProjectLog::create([
                'project_id'      => $link->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $link->project->project_name_th ?? '',
                'action'          => 'tasklink_create',
                'changes'         => ProjectLog::sanitizeChanges($link->toArray()),
            ]);
        });

        // Log update
        static::updating(function ($link) {
            $changes = ProjectLog::buildUpdateChanges($link);
            if (!empty($changes)) {
                ProjectLog::create([
                    'project_id'      => $link->project_id,
                    'user_id'         => Auth::id() ?? null,
                    'project_name_th' => $link->project->project_name_th ?? '',
                    'action'          => 'tasklink_update',
                    'changes'         => $changes,
                ]);
            }
        });

        // Log delete
        static::deleting(function ($link) {
            ProjectLog::create([
                'project_id'      => $link->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $link->project->project_name_th ?? '',
                'action'          => 'tasklink_delete',
                'changes'         => ProjectLog::sanitizeChanges($link->toArray()),
            ]);
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectLog;
use App\Models\User;

class CoResearch extends Model
{
    protected $touches = ['project'];

    protected $fillable = [
        'project_id',
        'user_id',
        'name',
        'affiliation',
        'unit_status'
    ];

    // ==== Log ทุก action ที่เกี่ยวกับ CoResearch ====
    protected static function booted()
    {
        // Log create
        static::created(function ($co) {
            ProjectLog::create([
                'project_id'      => $co->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $co->project->project_name_th ?? '',
                'action'          => 'coresearch_create',
                'changes'         => ProjectLog::withRecordContext(
                    ProjectLog::sanitizeChanges($co->toArray()),
                    'coresearch',
                    $co->id
                ),
            ]);
        });

        // Log update
        static::updating(function ($co) {
            $changes = ProjectLog::buildUpdateChanges($co);
            if (!empty($changes)) {
                ProjectLog::create([
                    'project_id'      => $co->project_id,
                    'user_id'         => Auth::id() ?? null,
                    'project_name_th' => $co->project->project_name_th ?? '',
                    'action'          => 'coresearch_update',
                    'changes'         => ProjectLog::withRecordContext($changes, 'coresearch', $co->id),
                ]);
            }
        });

        // Log delete
        static::deleting(function ($co) {
            ProjectLog::create([
                'project_id'      => $co->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $co->project->project_name_th ?? '',
                'action'          => 'coresearch_delete',
                'changes'         => ProjectLog::withRecordContext(
                    ProjectLog::sanitizeChanges($co->toArray()),
                    'coresearch',
                    $co->id
                ),
            ]);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

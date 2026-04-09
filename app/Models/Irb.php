<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectLog;

class Irb extends Model
{
    protected $touches = ['project'];

    protected $fillable = ['project_id', 'code', 'source', 'exp_date'];

    // ==== Log ทุก action ที่เกี่ยวกับ IRB ====
    protected static function booted()
    {
        // Log create
        static::created(function ($irb) {
            ProjectLog::create([
                'project_id'      => $irb->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $irb->project->project_name_th ?? '',
                'action'          => 'irb_create',
                'changes'         => ProjectLog::withRecordContext(
                    ProjectLog::sanitizeChanges($irb->toArray()),
                    'irb',
                    $irb->id
                ),
            ]);
        });

        // Log update
        static::updating(function ($irb) {
            $changes = ProjectLog::buildUpdateChanges($irb);
            if (!empty($changes)) {
                ProjectLog::create([
                    'project_id'      => $irb->project_id,
                    'user_id'         => Auth::id() ?? null,
                    'project_name_th' => $irb->project->project_name_th ?? '',
                    'action'          => 'irb_update',
                    'changes'         => ProjectLog::withRecordContext($changes, 'irb', $irb->id),
                ]);
            }
        });

        // Log delete
        static::deleting(function ($irb) {
            ProjectLog::create([
                'project_id'      => $irb->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $irb->project->project_name_th ?? '',
                'action'          => 'irb_delete',
                'changes'         => ProjectLog::withRecordContext(
                    ProjectLog::sanitizeChanges($irb->toArray()),
                    'irb',
                    $irb->id
                ),
            ]);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

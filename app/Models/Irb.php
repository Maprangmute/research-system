<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectLog;

class Irb extends Model
{
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
                'changes'         => $irb->toArray(),
            ]);
        });

        // Log update
        static::updating(function ($irb) {
            $diff = $irb->getDirty();
            unset($diff['updated_at']);
            if (!empty($diff)) {
                ProjectLog::create([
                    'project_id'      => $irb->project_id,
                    'user_id'         => Auth::id() ?? null,
                    'project_name_th' => $irb->project->project_name_th ?? '',
                    'action'          => 'irb_update',
                    'changes'         => $diff,
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
                'changes'         => $irb->toArray(),
            ]);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

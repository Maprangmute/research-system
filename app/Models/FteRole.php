<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectLog;

class FteRole extends Model
{
        protected $fillable = [
        'project_id',
        'role_group',           // ถ้ามีไว้ใช้ก็ปล่อยไว้ได้
        'name',
        'fte_value',
        'fte_value_research',   // ⬅️ เพิ่ม
        'job_detail',
        'start_date',
        'end_date',
        'task_detail'
    ];

    protected $casts = [
        'fte_value' => 'float',
        'fte_value_research' => 'float', // ⬅️ เพิ่ม
    ];
    // ==== Log ทุก action ที่เกี่ยวกับ FTE ====
    protected static function booted()
    {
        // Log create
        static::created(function ($fte) {
            ProjectLog::create([
                'project_id'      => $fte->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $fte->project->project_name_th ?? '',
                'action'          => 'fte_create',
                'changes'         => $fte->toArray(),
            ]);
        });

        // Log update
        static::updating(function ($fte) {
            $diff = $fte->getDirty();
            unset($diff['updated_at']);
            if (!empty($diff)) {
                ProjectLog::create([
                    'project_id'      => $fte->project_id,
                    'user_id'         => Auth::id() ?? null,
                    'project_name_th' => $fte->project->project_name_th ?? '',
                    'action'          => 'fte_update',
                    'changes'         => $diff,
                ]);
            }
        });

        // Log delete
        static::deleting(function ($fte) {
            ProjectLog::create([
                'project_id'      => $fte->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $fte->project->project_name_th ?? '',
                'action'          => 'fte_delete',
                'changes'         => $fte->toArray(),
            ]);
        });

        
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectLog;

class Copi extends Model
{
    protected $fillable = [
        'project_id', 'name', 'affiliation'
    ];

    // ==== Log ทุก action ที่เกี่ยวกับ Copi ====
    protected static function booted()
    {
        // Create log เมื่อเพิ่ม Co-PI
        static::created(function ($copi) {
            ProjectLog::create([
                'project_id'      => $copi->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $copi->project->project_name_th ?? '',
                'action'          => 'copi_create',
                'changes'         => $copi->toArray(),
            ]);
        });

        // Create log เมื่อแก้ไข Co-PI
        static::updating(function ($copi) {
            $diff = $copi->getDirty();
            unset($diff['updated_at']);
            if (!empty($diff)) {
                ProjectLog::create([
                    'project_id'      => $copi->project_id,
                    'user_id'         => Auth::id() ?? null,
                    'project_name_th' => $copi->project->project_name_th ?? '',
                    'action'          => 'copi_update',
                    'changes'         => $diff,
                ]);
            }
        });

        // Create log เมื่อลบ Co-PI
        static::deleting(function ($copi) {
            ProjectLog::create([
                'project_id'      => $copi->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $copi->project->project_name_th ?? '',
                'action'          => 'copi_delete',
                'changes'         => $copi->toArray(),
            ]);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

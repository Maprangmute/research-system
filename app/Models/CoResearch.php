<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectLog;

class CoResearch extends Model
{
    protected $fillable = [
        'project_id',
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
                'changes'         => $co->toArray(),
            ]);
        });

        // Log update
        static::updating(function ($co) {
            $diff = $co->getDirty();
            unset($diff['updated_at']);
            if (!empty($diff)) {
                ProjectLog::create([
                    'project_id'      => $co->project_id,
                    'user_id'         => Auth::id() ?? null,
                    'project_name_th' => $co->project->project_name_th ?? '',
                    'action'          => 'coresearch_update',
                    'changes'         => $diff,
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
                'changes'         => $co->toArray(),
            ]);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

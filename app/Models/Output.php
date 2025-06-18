<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectLog;

class Output extends Model
{
    protected $fillable = [
        'project_id',
        'type',
        'title',
        'year',
        'project_output'
    ];

    // ==== Log ทุก action ที่เกี่ยวกับ Output ====
    protected static function booted()
    {
        // Log create
        static::created(function ($output) {
            ProjectLog::create([
                'project_id'      => $output->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $output->project->project_name_th ?? '',
                'action'          => 'output_create',
                'changes'         => $output->toArray(),
            ]);
        });

        // Log update
        static::updating(function ($output) {
            $diff = $output->getDirty();
            unset($diff['updated_at']);
            if (!empty($diff)) {
                ProjectLog::create([
                    'project_id'      => $output->project_id,
                    'user_id'         => Auth::id() ?? null,
                    'project_name_th' => $output->project->project_name_th ?? '',
                    'action'          => 'output_update',
                    'changes'         => $diff,
                ]);
            }
        });

        // Log delete
        static::deleting(function ($output) {
            ProjectLog::create([
                'project_id'      => $output->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $output->project->project_name_th ?? '',
                'action'          => 'output_delete',
                'changes'         => $output->toArray(),
            ]);
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OutputConference extends Model
{
    protected $touches = ['output'];

    protected $fillable = [
        'output_id',
        'user_id',
        'name',
        'presentation_type',
        'location',
        'note',
    ];

    public function output()
    {
        return $this->belongsTo(Output::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::created(function ($conference) {
            ProjectLog::create([
                'project_id' => $conference->output?->project_id,
                'user_id' => Auth::id() ?? null,
                'project_name_th' => $conference->output?->project?->project_name_th ?? '',
                'action' => 'output_conference_create',
                'changes' => ProjectLog::withRecordContext(
                    ProjectLog::sanitizeChanges($conference->toArray()),
                    'output_conference',
                    $conference->id
                ),
            ]);
        });

        static::updating(function ($conference) {
            $changes = ProjectLog::buildUpdateChanges($conference);
            if (!empty($changes)) {
                ProjectLog::create([
                    'project_id' => $conference->output?->project_id,
                    'user_id' => Auth::id() ?? null,
                    'project_name_th' => $conference->output?->project?->project_name_th ?? '',
                    'action' => 'output_conference_update',
                    'changes' => ProjectLog::withRecordContext($changes, 'output_conference', $conference->id),
                ]);
            }
        });

        static::deleting(function ($conference) {
            ProjectLog::create([
                'project_id' => $conference->output?->project_id,
                'user_id' => Auth::id() ?? null,
                'project_name_th' => $conference->output?->project?->project_name_th ?? '',
                'action' => 'output_conference_delete',
                'changes' => ProjectLog::withRecordContext(
                    ProjectLog::sanitizeChanges($conference->toArray()),
                    'output_conference',
                    $conference->id
                ),
            ]);
        });
    }
}

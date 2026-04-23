<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OutputAwardRecipient extends Model
{
    protected $touches = ['output'];

    protected $table = 'output_award_recipients';

    protected $fillable = [
        'output_id',
        'user_id',
        'name',
    ];

    public function output()
    {
        return $this->belongsTo(Output::class, 'output_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::created(function ($recipient) {
            ProjectLog::create([
                'project_id' => $recipient->output?->project_id ?? null,
                'user_id' => Auth::id() ?? null,
                'project_name_th' => $recipient->output?->project?->project_name_th ?? '',
                'action' => 'output_award_recipient_create',
                'changes' => ProjectLog::sanitizeChanges($recipient->toArray()),
            ]);
        });

        static::updating(function ($recipient) {
            $changes = ProjectLog::buildUpdateChanges($recipient);
            if (! empty($changes)) {
                ProjectLog::create([
                    'project_id' => $recipient->output?->project_id ?? null,
                    'user_id' => Auth::id() ?? null,
                    'project_name_th' => $recipient->output?->project?->project_name_th ?? '',
                    'action' => 'output_award_recipient_update',
                    'changes' => $changes,
                ]);
            }
        });

        static::deleting(function ($recipient) {
            ProjectLog::create([
                'project_id' => $recipient->output?->project_id ?? null,
                'user_id' => Auth::id() ?? null,
                'project_name_th' => $recipient->output?->project?->project_name_th ?? '',
                'action' => 'output_award_recipient_delete',
                'changes' => ProjectLog::sanitizeChanges($recipient->toArray()),
            ]);
        });
    }
}

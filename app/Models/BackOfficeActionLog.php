<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackOfficeActionLog extends Model
{
    protected $fillable = [
        'user_id',
        'task_id',
        'entry_id',
        'output_id',
        'action',
        'subject_type',
        'subject_label',
        'task_title',
        'entry_person_name',
        'output_title',
        'route_name',
        'http_method',
        'url',
        'ip_address',
        'user_agent',
        'device_type',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(BackOfficeTask::class, 'task_id');
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(BackOfficeEntry::class, 'entry_id');
    }

    public function output(): BelongsTo
    {
        return $this->belongsTo(BackOfficeTaskOutput::class, 'output_id');
    }
}

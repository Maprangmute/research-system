<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackOfficeEntry extends Model
{
    protected $fillable = [
        'task_id',
        'person_name',
        'category',
        'job_detail',
        'start_date',
        'end_date',
        'document_link',
        'hours',
        'fte_value',
    ];

    protected $casts = [
        'hours' => 'float',
        'fte_value' => 'float',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(BackOfficeTask::class, 'task_id');
    }

    public function outputs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BackOfficeEntryOutput::class, 'entry_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackOfficeTaskOutput extends Model
{
    protected $fillable = [
        'task_id',
        'title',
        'type',
        'project_output',
        'year',
        'journal',
        'quartile',
        'impact_factor',
        'delivered_at',
        'description',
        'document_link',
        'authors',
        'conferences',
    ];

    protected $casts = [
        'authors' => 'array',
        'conferences' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(BackOfficeTask::class, 'task_id');
    }
}

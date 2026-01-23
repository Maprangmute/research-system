<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackOfficeTaskOutput extends Model
{
    protected $fillable = [
        'task_id',
        'title',
        'delivered_at',
        'description',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(BackOfficeTask::class, 'task_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class BackOfficeEntry extends Model
{
    protected $fillable = [
        'task_id',
        'person_name',
        'category',
        'job_name',
        'job_detail',
        'start_date',
        'end_date',
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

}

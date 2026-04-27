<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackOfficeOutputAuthor extends Model
{
    protected $table = 'back_office_output_authors';

    protected $fillable = [
        'back_office_task_output_id',
        'user_id',
        'role',
        'person_type',
        'name',
    ];

    public function output(): BelongsTo
    {
        return $this->belongsTo(BackOfficeTaskOutput::class, 'back_office_task_output_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

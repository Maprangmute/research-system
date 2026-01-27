<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackOfficeTask extends Model
{
    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'description',
        'purpose',
        'leaders',
        'created_by',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(BackOfficeEntry::class, 'task_id');
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(BackOfficeTaskOutput::class, 'task_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected $casts = [
        'leaders' => 'array',
    ];
}

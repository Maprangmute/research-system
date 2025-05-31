<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'title',
        'start',
        'end',
        'progress',
        'project_id',
        'parent_id',
        'user_id',
        'status' // ถ้าใช้ status ongoing/done
    ];

    // 💡 ความสัมพันธ์กับ Project
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // 💡 ความสัมพันธ์กับผู้ใช้
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 💡 ความสัมพันธ์กับ Subtasks
    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    // 💡 ความสัมพันธ์กับ Parent Task
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }


public function children()
{
    return $this->hasMany(Task::class, 'parent_id');
}

}

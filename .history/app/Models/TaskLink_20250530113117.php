<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLink extends Model
{
    protected $fillable = [
        'project_id',
        'source',
        'target',
        'type',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
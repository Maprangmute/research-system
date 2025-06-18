<?php

// app/Models/ProjectLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectLog extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'action', 'changes'
    ];

    protected $casts = [
        'changes' => 'array'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function project() { return $this->belongsTo(Project::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'start', 'end', 'project_id', 'parent_id', 'progress'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

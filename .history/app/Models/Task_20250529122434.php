<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'start',
        'end',
        'progress',
        'project_id',
        'parent_id'
    ];
}

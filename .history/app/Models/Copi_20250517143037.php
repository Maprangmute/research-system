<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Copi extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'affiliation',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}


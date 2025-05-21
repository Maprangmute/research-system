<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Output extends Model
{
    protected $fillable = ['project_id', 'type', 'title', 'year'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}


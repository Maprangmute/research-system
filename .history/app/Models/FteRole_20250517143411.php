<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FteRole extends Model
{
    protected $fillable = ['project_id', 'role_group', 'name', 'fte_value'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

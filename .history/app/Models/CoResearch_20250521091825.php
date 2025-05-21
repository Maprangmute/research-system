<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class CoResearch extends Model
{
    //
    protected $fillable = [
        'project_id', 'name', 'affiliation'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

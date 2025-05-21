<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FteRole extends Model
{
    protected $fillable = [
        'project_id', 'role_group', 'name', 'fte_value' , 'start_date', 'end_date'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

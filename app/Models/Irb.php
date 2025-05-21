<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Irb extends Model
{
    protected $fillable = ['project_id', 'code', 'source' , 'exp_date'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

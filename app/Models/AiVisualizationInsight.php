<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiVisualizationInsight extends Model
{
    protected $fillable = [
        'insight_type',
        'scope_key',
        'input_hash',
        'provider',
        'model',
        'summary',
        'payload',
        'input_snapshot',
        'generated_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'input_snapshot' => 'array',
        'generated_at' => 'datetime',
    ];
}

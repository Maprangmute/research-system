<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProfessionClassification extends Model
{
    protected $fillable = [
        'name_key',
        'display_name',
        'profession_group',
        'confidence',
        'evidence',
        'source_context',
        'raw_response',
        'classified_at',
    ];

    protected $casts = [
        'confidence' => 'float',
        'raw_response' => 'array',
        'classified_at' => 'datetime',
    ];
}

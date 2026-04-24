<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'activity_type',
        'subject_type',
        'subject_label',
        'file_name',
        'route_name',
        'http_method',
        'url',
        'ip_address',
        'user_agent',
        'device_type',
        'session_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

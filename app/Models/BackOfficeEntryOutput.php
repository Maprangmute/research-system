<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackOfficeEntryOutput extends Model
{
    protected $fillable = [
        'entry_id',
        'title',
        'type',
        'project_output',
        'year',
        'journal',
        'quartile',
        'impact_factor',
        'delivered_at',
        'description',
        'document_link',
        'authors',
        'conferences',
    ];

    protected $casts = [
        'authors' => 'array',
        'conferences' => 'array',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(BackOfficeEntry::class, 'entry_id');
    }
}

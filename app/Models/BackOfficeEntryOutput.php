<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackOfficeEntryOutput extends Model
{
    protected $fillable = [
        'entry_id',
        'title',
        'delivered_at',
        'description',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(BackOfficeEntry::class, 'entry_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackOfficeOutputConference extends Model
{
    protected $table = 'back_office_output_conferences';

    protected $fillable = [
        'back_office_task_output_id',
        'user_id',
        'name',
        'presentation_type',
        'location',
        'presenter_person_type',
        'note',
    ];

    public function output(): BelongsTo
    {
        return $this->belongsTo(BackOfficeTaskOutput::class, 'back_office_task_output_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function presenters(): HasMany
    {
        return $this->hasMany(BackOfficeOutputConferencePresenter::class, 'back_office_output_conference_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackOfficeOutputConferencePresenter extends Model
{
    protected $table = 'back_office_output_conference_presenters';

    protected $fillable = [
        'back_office_output_conference_id',
        'user_id',
        'person_type',
        'name',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(BackOfficeOutputConference::class, 'back_office_output_conference_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

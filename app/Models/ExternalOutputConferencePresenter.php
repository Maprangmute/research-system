<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalOutputConferencePresenter extends Model
{
    protected $fillable = [
        'external_output_conference_id',
        'user_id',
        'name',
    ];

    public function conference()
    {
        return $this->belongsTo(ExternalOutputConference::class, 'external_output_conference_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutputConferencePresenter extends Model
{
    protected $touches = ['conference'];

    protected $fillable = [
        'output_conference_id',
        'user_id',
        'name',
    ];

    public function conference()
    {
        return $this->belongsTo(OutputConference::class, 'output_conference_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

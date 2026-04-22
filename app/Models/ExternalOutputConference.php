<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ExternalOutputConference extends Model
{
    protected $fillable = [
        'external_output_id',
        'user_id',
        'name',
        'presentation_type',
        'location',
        'presenter_name',
    ];

    public function externalOutput()
    {
        return $this->belongsTo(ExternalOutput::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}

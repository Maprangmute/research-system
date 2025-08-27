<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutputConference extends Model
{
    protected $fillable = [
        'output_id',
        'name',
        'presentation_type',
        'location',
        'note',
    ];

    public function output()
    {
        return $this->belongsTo(Output::class);
    }
}

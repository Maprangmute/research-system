<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalOutputBookAuthor extends Model
{
    protected $fillable = [
        'external_output_id',
        'user_id',
        'name',
    ];

    public function externalOutput()
    {
        return $this->belongsTo(ExternalOutput::class, 'external_output_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

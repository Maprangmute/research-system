<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalOutputAuthor extends Model
{
    protected $fillable = [
        'external_output_id',
        'role',
        'name',
    ];

    public function externalOutput()
    {
        return $this->belongsTo(ExternalOutput::class);
    }
}

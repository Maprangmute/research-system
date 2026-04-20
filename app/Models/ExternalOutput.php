<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ExternalOutput extends Model
{
    protected $fillable = [
        'output_code',
        'type',
        'job_category',
        'title',
        'note',
        'publish_date',
        'journal',
        'quartile',
        'impact_factor',
        'book_title',
        'publisher_name',
        'chapter_pages',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'publish_date' => 'date',
        'impact_factor' => 'decimal:3',
    ];

    public function authors()
    {
        return $this->hasMany(ExternalOutputAuthor::class);
    }

    public function conferences()
    {
        return $this->hasMany(ExternalOutputConference::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function formatOutputCode(int $id): string
    {
        return 'px_' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OutputBookAuthor extends Model
{
    protected $touches = ['output'];

    protected $table = 'output_book_authors';

    protected $fillable = [
        'output_id',
        'user_id',
        'name',
    ];

    public function output()
    {
        return $this->belongsTo(Output::class, 'output_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::created(function ($author) {
            ProjectLog::create([
                'project_id' => $author->output?->project_id,
                'user_id' => Auth::id() ?? null,
                'project_name_th' => $author->output?->project?->project_name_th ?? '',
                'action' => 'output_book_author_create',
                'changes' => ProjectLog::withRecordContext(
                    ProjectLog::sanitizeChanges($author->toArray()),
                    'output_book_author',
                    $author->id
                ),
            ]);
        });

        static::updating(function ($author) {
            $changes = ProjectLog::buildUpdateChanges($author);
            if (!empty($changes)) {
                ProjectLog::create([
                    'project_id' => $author->output?->project_id,
                    'user_id' => Auth::id() ?? null,
                    'project_name_th' => $author->output?->project?->project_name_th ?? '',
                    'action' => 'output_book_author_update',
                    'changes' => ProjectLog::withRecordContext($changes, 'output_book_author', $author->id),
                ]);
            }
        });

        static::deleting(function ($author) {
            ProjectLog::create([
                'project_id' => $author->output?->project_id,
                'user_id' => Auth::id() ?? null,
                'project_name_th' => $author->output?->project?->project_name_th ?? '',
                'action' => 'output_book_author_delete',
                'changes' => ProjectLog::withRecordContext(
                    ProjectLog::sanitizeChanges($author->toArray()),
                    'output_book_author',
                    $author->id
                ),
            ]);
        });
    }
}

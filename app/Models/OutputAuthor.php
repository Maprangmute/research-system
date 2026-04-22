<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectLog;

class OutputAuthor extends Model
{
    protected $touches = ['output'];

    protected $table = 'output_authors';

    protected $fillable = [
        'output_id',
        'user_id',
        'role', // first|co|corresponding
        'name',
    ];

    public function output()
    {
        return $this->belongsTo(\App\Models\Output::class, 'output_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    // สะดวกเรียก filter ตาม role
    public function scopeRole($q, string $role)
    {
        return $q->where('role', strtolower(trim($role)));
    }

    protected static function booted()
    {
        // บันทึก log พอประมาณ (เลือกทำ/ไม่ทำก็ได้ หาก log เยอะเกินไปให้ตัดออก)
        static::created(function ($author) {
            ProjectLog::create([
                'project_id'      => $author->output->project_id ?? null,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => optional($author->output->project)->project_name_th ?? '',
                'action'          => 'output_author_create',
                'changes'         => ProjectLog::sanitizeChanges($author->toArray()),
            ]);
        });

        static::updating(function ($author) {
            $changes = ProjectLog::buildUpdateChanges($author);
            if (!empty($changes)) {
                ProjectLog::create([
                    'project_id'      => $author->output->project_id ?? null,
                    'user_id'         => Auth::id() ?? null,
                    'project_name_th' => optional($author->output->project)->project_name_th ?? '',
                    'action'          => 'output_author_update',
                    'changes'         => $changes,
                ]);
            }
        });

        static::deleting(function ($author) {
            ProjectLog::create([
                'project_id'      => $author->output->project_id ?? null,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => optional($author->output->project)->project_name_th ?? '',
                'action'          => 'output_author_delete',
                'changes'         => ProjectLog::sanitizeChanges($author->toArray()),
            ]);
        });
    }
}

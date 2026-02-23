<?php

// app/Models/ProgressReport.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReport extends Model
{
    protected $fillable = [
        'project_id',
        'submitter_name',
        'submit_date',
        'progress_title',
        'progress_subtitle',
        'file_info',
        'progress_rows',
        'issue_rows',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'submit_date' => 'date',
        'progress_rows' => 'array',
        'issue_rows' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

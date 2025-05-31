<?php
protected $fillable = [
    'title', 'start', 'end',
    'project_id', 'parent_id',
    'progress', 'status'
];

protected $casts = [
    'start' => 'date',
    'end' => 'date',
];

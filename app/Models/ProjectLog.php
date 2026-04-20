<?php

// app/Models/ProjectLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Model;

class ProjectLog extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'action', 'changes', 'project_name_th'
    ];

    protected $casts = [
        'changes' => 'array'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function project() { return $this->belongsTo(Project::class); }

    public static function sanitizeChanges(array $changes): array
    {
        foreach (['id', 'project_id', 'created_at', 'updated_at', 'deleted_at'] as $key) {
            unset($changes[$key]);
        }

        return $changes;
    }

    public static function buildUpdateChanges(EloquentModel $model): array
    {
        $diff = $model->getDirty();
        unset($diff['updated_at']);

        $changes = [];
        foreach ($diff as $key => $newValue) {
            $changes[$key] = [
                'from' => $model->getOriginal($key),
                'to' => $newValue,
            ];
        }

        return $changes;
    }

    public static function withRecordContext(array $changes, string $entity, $recordId): array
    {
        $changes['record_entity'] = $entity;
        $changes['record_id'] = $recordId;

        return $changes;
    }

    public function getActionVerbAttribute(): string
    {
        $meta = $this->actionMeta();
        return $meta['verb'];
    }

    public function getEntityLabelAttribute(): string
    {
        $meta = $this->actionMeta();
        $entity = $meta['entity'];

        $map = [
            'project' => 'โครงการ',
            'output' => 'ผลผลิต',
            'output_author' => 'ผู้เขียนผลงาน',
            'output_book_author' => 'ผู้เขียนบท/หนังสือ',
            'output_conference' => 'ข้อมูลการประชุม',
            'back_office_task' => 'Back Office',
            'back_office_entry' => 'ภาระงาน Back Office',
            'back_office_output' => 'ผลงาน Back Office',
            'external_output' => 'Extra',
            'external_output_author' => 'ผู้เขียน Extra',
            'external_output_conference' => 'การนำเสนอ Extra',
            'fte' => 'FTE',
            'task' => 'งาน',
            'tasklink' => 'ลิงก์งาน',
            'copi' => 'ผู้ร่วมวิจัย',
            'coresearch' => 'นักวิจัยร่วม',
            'irb' => 'IRB',
        ];

        return $map[$entity] ?? ucfirst(str_replace('_', ' ', $entity));
    }

    public function getActionLabelAttribute(): string
    {
        $map = [
            'create' => 'สร้าง',
            'update' => 'แก้ไข',
            'delete' => 'ลบ',
            'restore' => 'กู้คืน',
        ];

        return $map[$this->action_verb] ?? ucfirst($this->action_verb);
    }

    public function getActionColorClassAttribute(): string
    {
        $map = [
            'create' => 'bg-green-100 text-green-700',
            'update' => 'bg-blue-100 text-blue-700',
            'delete' => 'bg-red-100 text-red-700',
            'restore' => 'bg-amber-100 text-amber-700',
        ];

        return $map[$this->action_verb] ?? 'bg-slate-100 text-slate-700';
    }

    private function actionMeta(): array
    {
        $action = (string) $this->action;
        if (str_contains($action, '_')) {
            $parts = explode('_', $action);
            $verb = array_pop($parts);
            $entity = implode('_', $parts);
        } else {
            $entity = 'project';
            $verb = $action;
        }

        return ['entity' => $entity, 'verb' => $verb];
    }
}

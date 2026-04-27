<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackOfficeTask extends Model
{
    protected $fillable = [
        'backoffice_id',
        'title',
        'start_date',
        'end_date',
        'description',
        'purpose',
        'leaders',
        'created_by',
        'updated_by',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(BackOfficeEntry::class, 'task_id');
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(BackOfficeTaskOutput::class, 'task_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected $casts = [
        'leaders' => 'array',
    ];

    protected static function booted(): void
    {
        static::created(function (BackOfficeTask $task) {
            if ($task->backoffice_id) {
                return;
            }

            $task->forceFill([
                'backoffice_id' => static::formatBackOfficeId((int) $task->id),
            ])->saveQuietly();
        });
    }

    public static function formatBackOfficeId(int $id): string
    {
        return 'BO_' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    public function getRouteKeyName(): string
    {
        return 'backoffice_id';
    }

    public function getRouteKey()
    {
        return $this->backoffice_id ?: static::formatBackOfficeId((int) $this->id);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $query = $this->newQuery();

        if (is_string($value) && preg_match('/^BO_\\d+$/i', $value)) {
            return $query->where('backoffice_id', strtoupper($value))->first();
        }

        if (is_numeric($value)) {
            return $query->whereKey((int) $value)
                ->orWhere('backoffice_id', static::formatBackOfficeId((int) $value))
                ->first();
        }

        return $query->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

}

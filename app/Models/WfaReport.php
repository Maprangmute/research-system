<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WfaReport extends Model
{
    protected $fillable = [
        'wfa_code',
        'user_id',
        'person_name',
        'title',
        'work_date',
        'filter_mode',
        'filter_year',
        'filter_month',
        'filter_start',
        'filter_end',
        'output_html',
        'output_text',
    ];

    protected $casts = [
        'work_date' => 'date',
        'filter_start' => 'date',
        'filter_end' => 'date',
        'filter_year' => 'integer',
        'filter_month' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function formatCode(int $id): string
    {
        return 'WFA_' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }

    protected static function booted(): void
    {
        static::created(function (WfaReport $report): void {
            if (! $report->wfa_code) {
                $report->forceFill(['wfa_code' => self::formatCode((int) $report->id)])->saveQuietly();
            }
        });
    }
}

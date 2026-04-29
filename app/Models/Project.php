<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ProjectLog;
use Illuminate\Support\Facades\Auth;

class Project extends Model
{
    use SoftDeletes; // ถ้าต้องการ soft delete

    private const LEGACY_PROJECT_LIST_MAP = [
        'โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภา' => 'โครงการภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ',
    ];

    protected $fillable = [
        'project_code',
        'project_name_th',
        'project_name_en',
        'project_type',
        'theme',
        'shortcut_link',
        'size',
        'r_code',
        'funding_type',
        'funding_source',
        'funding_org_type',
        'funding_name',
        'budget',
        'start_year',
        'planning_status',
        'proposal_date',
        'execution_status',
        'contract_date',
        'start_date',
        'end_date',
        'review_status',
        'closure_status',
        'user_id',
        'team_start_date',
        'team_end_date',
        'pi_name',
        'pi_user_id',
        'pi_affiliation',
        'task_detail',
        // Strategy flags
        'strategy_1',
        'strategy_1_1',
        'strategy_1_2',
        'strategy_1_3',
        'strategy_1_4',
        'strategy_1_5',
        'strategy_2',
        'strategy_2_1',
        'strategy_2_2',
        'strategy_2_3',
        'strategy_3',
        'strategy_3_1',
        'strategy_3_2',
        'strategy_3_3',
        'strategy_3_4',
        'strategy_3_5',
        'strategy_4',
        'strategy_4_1',
        'strategy_4_2',
        'strategy_5',
        'strategy_5_1',
        'strategy_5_2',
        'project_list'
    ];

    public function getProjectListAttribute($value): ?string
    {
        return $this->normalizeProjectListValue($value);
    }

    public function setProjectListAttribute($value): void
    {
        $this->attributes['project_list'] = $this->normalizeProjectListValue($value);
    }

    private function normalizeProjectListValue($value): ?string
    {
        $normalized = trim((string) ($value ?? ''));
        if ($normalized === '') {
            return null;
        }

        return self::LEGACY_PROJECT_LIST_MAP[$normalized] ?? $normalized;
    }

    /**
     * Use project_code as the primary route key for URL generation.
     * When a record has no code yet, fall back to numeric id.
     */
    public function getRouteKeyName(): string
    {
        return 'project_code';
    }

    public function getRouteKey(): mixed
    {
        $code = trim((string) ($this->project_code ?? ''));

        return $code !== '' ? $code : $this->getAttribute($this->getKeyName());
    }

    /**
     * Support both /projects/{id} and /projects/{project_code} while transitioning.
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $lookup = trim((string) $value);
        if ($lookup === '') {
            return null;
        }

        if ($field !== null && $field !== 'project_code') {
            return parent::resolveRouteBinding($value, $field);
        }

        if (ctype_digit($lookup)) {
            $foundById = $this->newQuery()->whereKey((int) $lookup)->first();
            if ($foundById) {
                return $foundById;
            }
        }

        return $this->newQuery()
            ->whereRaw('LOWER(TRIM(project_code)) = ?', [mb_strtolower($lookup, 'UTF-8')])
            ->first();
    }

    // ====== Log events: create, update, delete, restore ======
    protected static function booted()
    {
        // Log create
        static::created(function ($project) {
            ProjectLog::create([
                'project_id'      => $project->id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $project->project_name_th,
                'action'          => 'create',
                'changes'         => ProjectLog::sanitizeChanges($project->toArray()),
            ]);
        });

        // Log update (เฉพาะ field ที่เปลี่ยน ไม่รวม updated_at)
        static::updating(function ($project) {
            $changes = ProjectLog::buildUpdateChanges($project);
            if (!empty($changes)) {
                ProjectLog::create([
                    'project_id'      => $project->id,
                    'user_id'         => Auth::id() ?? null,
                    'project_name_th' => $project->project_name_th,
                    'action'          => 'update',
                    'changes'         => $changes,
                ]);
            }
        });

        // Log delete (Soft Delete หรือ Hard Delete)
        static::deleting(function ($project) {
            ProjectLog::create([
                'project_id'      => $project->id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $project->project_name_th,
                'action'          => 'delete',
                'changes'         => ProjectLog::sanitizeChanges($project->toArray()),
            ]);
        });

        // Log restore (Soft Delete)
        static::restored(function ($project) {
            ProjectLog::create([
                'project_id'      => $project->id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $project->project_name_th,
                'action'          => 'restore',
                'changes'         => [],
            ]);
        });
    }

    // ========== Relation ==========
    public function copis(): HasMany
    {
        return $this->hasMany(Copi::class);
    }

    public function fteRoles(): HasMany
    {
        return $this->hasMany(FteRole::class);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(Output::class);
    }

    public function irbs(): HasMany
    {
        return $this->hasMany(Irb::class);
    }

    public function coResearches(): HasMany
    {
        return $this->hasMany(CoResearch::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProjectLog::class, 'project_id');
    }

    public function latestLog(): HasOne
    {
        return $this->hasOne(ProjectLog::class, 'project_id')->latestOfMany();
    }

    // public function updatedBy()
    // {
    //     return $this->belongsTo(User::class, 'updated_by');
    // }

    // public function createdBy()
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }
    public function createdBy() { return $this->belongsTo(User::class, 'user_id'); }
public function updatedBy() { return $this->belongsTo(User::class, 'updated_by'); }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function piUser()
    {
        return $this->belongsTo(User::class, 'pi_user_id');
    }
    // app/Models/Project.php
    public function progressReports()
    {
        return $this->hasMany(ProgressReport::class);
    }

    
}

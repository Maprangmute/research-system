<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'project_name_th',
        'project_name_en',
        'project_type',
        'theme',
        'shortcut_link',
        'size',
        'funding_type',
        'funding_source',
        'funding_org_type',
        'funding_name',
        'budget',
        // 'start_month',
        'start_year',
        // 'end_month',
        // 'end_year',
        'planning_status',
        'proposal_date',
        'execution_status',
        'contract_date',
        'start_date',
        'review_status',
        'closure_status',
        'io_code',
        'course_code',
        'r_codes',
        'irb_codes',
        'irb_sources',
        'user_id',
        'team_start_date',
        'team_end_date',

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
    ];

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
}

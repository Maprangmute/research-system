<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // ← สำคัญสำหรับ Intelephense

class Project extends Model
{
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
}

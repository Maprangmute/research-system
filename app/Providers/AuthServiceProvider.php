<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\FteRole;
use App\Policies\FteRolePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        FteRole::class => FteRolePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}

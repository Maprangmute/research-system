<?php

namespace App\Providers;

use App\Support\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event) {
            AuditLogger::logUserActivity('login', [
                'user_id' => $event->user?->id,
                'subject_type' => 'auth',
                'subject_label' => 'เข้าสู่ระบบ',
            ]);
        });

        Event::listen(Logout::class, function (Logout $event) {
            AuditLogger::logUserActivity('logout', [
                'user_id' => $event->user?->id,
                'subject_type' => 'auth',
                'subject_label' => 'ออกจากระบบ',
            ]);
        });
    }
}

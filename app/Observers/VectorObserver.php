<?php

namespace App\Observers;

/**
 * Vector sync was intentionally disabled.
 * Keep this observer as a no-op so existing observe() bindings
 * do not break when legacy code paths are still present.
 */
class VectorObserver
{
    public function created($model): void {}

    public function updated($model): void {}

    public function deleted($model): void {}

    public function restored($model): void {}

    public function forceDeleted($model): void {}

    public function syncUpsert($model): void {}

    public function syncDelete($model): void {}
}

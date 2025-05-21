<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\FteController;
use App\Http\Controllers\OverviewController;

// หน้าแรก
Route::get('/', function () {
    return view('welcome');
});

// Dashboard หลัง login
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// ✅ กลุ่มเส้นทาง /rm
Route::middleware(['auth'])->prefix('rm')->group(function () {

    // 🔁 Projects ใช้ resource ครบ
    Route::resource('projects', ProjectController::class);

    // 📊 FTE
    Route::get('/fte', [FteController::class, 'index'])->name('fte.index');
    Route::get('/fte/detail', [FteController::class, 'detail'])->name('fte.detail');

    // 📈 Overview
    Route::get('/overview', [OverviewController::class, 'index'])->name('overview');
});

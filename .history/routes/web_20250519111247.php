<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\FteController;
use App\Http\Controllers\OverviewController;

// 🏠 หน้าแรก
Route::get('/', function () {
    return view('welcome');
});

// 🧑‍💻 ระบบ Auth + Dashboard
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// 📦 กลุ่ม /rm สำหรับ Resource Management
Route::middleware(['auth'])->prefix('rm')->group(function () {

    // 🔁 Projects (ใช้ resource)
    Route::resource('projects', ProjectController::class);

    // 📊 FTE
    Route::get('/fte', [FteController::class, 'index'])->name('fte.index');
    Route::get('/fte/detail', [FteController::class, 'detail'])->name('fte.detail');

    // 🧭 Overview
    Route::get('/overview', [OverviewController::class, 'index'])->name('overview');
});

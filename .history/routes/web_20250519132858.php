<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\FteController;
use App\Http\Controllers\OverviewController;

// 🌐 หน้าหลัก
Route::get('/', function () {
    return view('welcome');
});

// 🔐 Auth + Dashboard
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
});

// 📁 กลุ่มงานวิจัย (prefix: /rm)
Route::middleware(['auth'])->prefix('rm')->group(function () {

    // ✅ หน้าแรกของ /rm = index ของโครงการ
    Route::get('/', [ProjectController::class, 'index'])->name('projects.index');

    // ✅ สร้างโครงการ
    Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/', [ProjectController::class, 'store'])->name('projects.store');

    // ✅ แก้ไขโครงการ
    Route::get('/edit/{project}', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/update/{project}', [ProjectController::class, 'update'])->name('projects.update');

    // ✅ เส้นทางอื่น ๆ
    Route::get('/fte', [FteController::class, 'index'])->name('fte.index');
    Route::get('/fte/detail', [FteController::class, 'detail'])->name('fte.detail');
    Route::get('/overview', [OverviewController::class, 'index'])->name('overview');

    // 🆕 เพิ่มรายละเอียดโครงการ
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

    // 🆕 PDF Export
    Route::get('/projects/{project}/pdf', [ProjectController::class, 'exportPdf'])->name('projects.pdf');

    // delete data 
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\FteController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;


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
    // Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

});


use App\Http\Controllers\TaskController;
use App\Http\Controllers\GanttController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Gantt View Page
    Route::get('/projects/{project}/gantt', [GanttController::class, 'index'])->name('projects.gantt');

    // Gantt Task API
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']); // เพิ่มงานหลัก/ย่อย
    Route::put('/tasks/{task}', [TaskController::class, 'update']); // Drag วันที่, เปลี่ยน parent_id, order, status
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // Drag reorder task หรือย้าย parent
    Route::put('/tasks/{task}/move', [TaskController::class, 'move']);

    // อัปเดตสถานะงาน (เช่น ongoing, done)
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
});





Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/user', [UserController::class, 'index'])->name('users.index');
    Route::get('/user/manage', [UserController::class, 'manageList'])->name('users.manage');
    Route::put('/user/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');

    Route::get('/user/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/user', [UserController::class, 'store'])->name('users.store');

    Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/user/{user}', [UserController::class, 'update'])->name('users.update');

    Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('users.destroy');
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

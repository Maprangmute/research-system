<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\FteController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskLinkController;


// 🌐 หน้าหลัก
Route::get('/', function () {
    // return view('welcome');
    
});
Route::redirect('/', '/login');           // 302 ชั่วคราว (แนะนำ)

// 🔐 Auth + Dashboard
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

use App\Http\Controllers\ProjectLogController;

Route::middleware(['auth'])->group(function () {
    Route::get('/project-logs', [ProjectLogController::class, 'index'])->name('project_logs.index');
    Route::get('/project-logs/{id}', [ProjectLogController::class, 'show'])->name('project_logs.show');
    Route::delete('/project-logs/{id}', [ProjectLogController::class, 'destroy'])->name('project_logs.destroy');
    // ดู log เฉพาะโปรเจค
    Route::get('/projects/{project}/logs', [ProjectLogController::class, 'projectLogs'])->name('project_logs.project');
});


use App\Http\Controllers\ProgressReportController;
Route::middleware(['auth'])->group(function () {
   Route::resource('projects.progress_reports', ProgressReportController::class);
});


use App\Http\Controllers\TaskController;
use App\Http\Controllers\GanttController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/rm/projects/{project}/gantt', [GanttController::class, 'index'])->name('projects.gantt');

    // Gantt Tasks
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    Route::post('/tasks/bulk-save', [TaskController::class, 'bulkSave']); // ✅ เพิ่มตรงนี้

    // Gantt Links

    // Route::post('/task-links', [TaskController::class, 'storeLink']);
    // Route::delete('/task-links/{id}', [TaskController::class, 'destroyLink']);
    // Route::resource('/task-links', TaskLinkController::class)->only([
    //     'store',
    //     'update',
    //     'destroy'

    // ]);
    Route::resource('/task-links', TaskLinkController::class)->only([
        'index',
        'store',
        'update',
        'destroy'
    ]);
    Route::get('/projects/{project}/task-links', [TaskLinkController::class, 'projectLinks']);
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

    // เปรียบเทียบข้อมูล FTE API AJAX
    Route::get('/fte/compare-data', [FteController::class, 'compareData'])->name('fte.compareData');


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
    Route::get('/fte/{person}/detail', [FteController::class, 'detail'])->name('fte.detail');



    // 🆕 เพิ่มรายละเอียดโครงการ
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

    // 🆕 PDF Export
    Route::get('/projects/{project}/pdf', [ProjectController::class, 'exportPdf'])->name('projects.pdf');

    // delete data 
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    // check project name
    Route::post('/projects/check-name', [ProjectController::class, 'checkProjectName'])->name('projects.checkName');

});


use App\Http\Controllers\FteRoleController;

Route::middleware(['auth'])->group(function () {
    // หน้าแสดงฟอร์ม FTE ซ้าย/ขวา
    Route::get('/projects/{project}/fte', [FteRoleController::class, 'index'])
        ->name('projects.fte.index');

    // (มีแล้วจากก่อนหน้า) เซฟแบบ bulk
    Route::put('/projects/{project}/fte', [FteRoleController::class, 'bulkUpdate'])
        ->name('projects.fte.update');
});


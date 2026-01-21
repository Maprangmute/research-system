<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\FteController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskLinkController;
use App\Http\Controllers\AiDashboardController;
use App\Http\Controllers\OutputController;


use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Auth\OtpPasswordResetController;

Route::get('/falldocs', [DocumentController::class, 'gate'])->name('docs.index');
Route::get('/falldocs/login', [DocumentController::class, 'login'])->name('docs.login'); // <= GET
Route::get('/falldocs/secure', [DocumentController::class, 'index'])
     ->name('docs.secure')->middleware('signed');
Route::get('/docs/logout', [DocumentController::class, 'logout'])->name('docs.logout');   // แนะนำให้เป็น GET


// 🌐 หน้าหลัก
Route::get('/', function () {
    // return view('welcome');
    
});
Route::redirect('/', '/login');           // 302 ชั่วคราว (แนะนำ)

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [OtpPasswordResetController::class, 'showRequestForm'])->name('password.request');
    Route::post('/forgot-password', [OtpPasswordResetController::class, 'sendOtp'])->name('password.email');
    Route::get('/reset-password', [OtpPasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [OtpPasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware(['auth'])->group(function () {
    Route::view('/user-guide', 'user_guide')->name('user_guide');
});



Route::middleware(['auth'])->group(function () {

    Route::prefix('rm/ai-dashboard')->name('rm.ai_dashboard.')->group(function () {

        // 🧠 หน้าเว็บ (ROOT ของ dashboard)
        Route::get('/', function () {
            return view('rm.ai_dashboard');
        })->name('index');

        // 🔥 endpoint กลาง (AI วิเคราะห์หลายมิติ)
        Route::post('/analyze', [AiDashboardController::class, 'analyze'])
            ->name('analyze');

        // 🔹 endpoint ตามมิติ
        Route::post('/metrics/projects', [AiDashboardController::class, 'projectsMetric'])->name('metrics.projects');
        Route::post('/metrics/budget',   [AiDashboardController::class, 'budgetMetric'])->name('metrics.budget');
        Route::post('/metrics/fte',      [AiDashboardController::class, 'fteMetric'])->name('metrics.fte');
        Route::post('/metrics/output',   [AiDashboardController::class, 'outputMetric'])->name('metrics.output');
        Route::post('/metrics/status',   [AiDashboardController::class, 'statusMetric'])->name('metrics.status');
        Route::post('/metrics/irb',      [AiDashboardController::class, 'irbMetric'])->name('metrics.irb');
    });
});




use App\Http\Controllers\AgentController;

Route::middleware(['auth'])->group(function () {

    // สำหรับ Blade เดิม
    Route::prefix('rm/agent')->name('rm.agent.')->group(function () {
        Route::post('/ask',  [AgentController::class, 'ask'])->name('ask');   // POST /rm/agent/ask
        Route::get('/diag',  [AgentController::class, 'diagGemini'])->name('diag'); // GET /rm/agent/diag
    });

    // API ภายใน (เรียกจากหน้าเดิมได้ด้วย)
    Route::prefix('agent')->name('agent.')->group(function () {
        Route::post('/rag/reindex', [AgentController::class, 'ragReindex'])->name('rag.reindex');
        Route::post('/rag/ask',     [AgentController::class, 'ragAsk'])->name('rag.ask');
        Route::post('/fc/fte',      [AgentController::class, 'fcFteOverrun'])->name('fc.fte');
        Route::post('/fc/irb',      [AgentController::class, 'fcIrbExpiry'])->name('fc.irb');
        Route::post('/fc/delay',    [AgentController::class, 'fcDelayRisk'])->name('fc.delay');
        Route::post('/fc/output',   [AgentController::class, 'fcOutputProb'])->name('fc.output');
    });

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

use App\Http\Controllers\ProjectLogController;

Route::middleware(['auth'])->group(function () {
    Route::get('/project-logs', [ProjectLogController::class, 'index'])->name('project_logs.index');
    Route::delete('/project-logs/purge-old', [ProjectLogController::class, 'purgeOld'])->name('project_logs.purge_old');
    Route::get('/project-logs/{id}', [ProjectLogController::class, 'show'])->name('project_logs.show')->whereNumber('id');
    Route::delete('/project-logs/{id}', [ProjectLogController::class, 'destroy'])->name('project_logs.destroy')->whereNumber('id');
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
    Route::get('/fte/projects', [FteController::class, 'projectIndex'])->name('fte.project.index');
    Route::get('/fte/{person}/detail', [FteController::class, 'detail'])->name('fte.detail');
    Route::get('/fte/project/{project}', [FteController::class, 'projectDetail'])->name('fte.project.detail');
    Route::get('/fte/project/{project}/pdf', [FteController::class, 'projectPdf'])->name('fte.project.pdf');
    Route::get('/fte/{person}/pdf', [FteController::class, 'exportPdf'])->name('fte.pdf');
    Route::get('/outputs', [OutputController::class, 'index'])->name('outputs.index');



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

<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});


use App\Http\Controllers\ProjectController;

Route::middleware(['auth'])->prefix('rm')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('projects.index'); // Dashboard
    Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/edit/{project}', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/update/{project}', [ProjectController::class, 'update'])->name('projects.update');
});


Route::get('/edit/{project}', [ProjectController::class, 'edit'])->name('projects.edit');
Route::put('/update/{project}', [ProjectController::class, 'update'])->name('projects.update');

use App\Http\Controllers\FteController;

Route::middleware(['auth'])->prefix('rm')->group(function () {
    Route::get('/fte', [FteController::class, 'index'])->name('fte.index');
    Route::get('/fte/detail', [FteController::class, 'detail'])->name('fte.detail');
});


use App\Http\Controllers\OverviewController;

Route::middleware(['auth'])->prefix('rm')->group(function () {
    Route::get('/overview', [OverviewController::class, 'index'])->name('overview');
});


Route::get('/rm/projects/{id}/edit', [ProjectController::class, 'edit'])->name('projects.edit');


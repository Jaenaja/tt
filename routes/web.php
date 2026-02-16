<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LotteryBetController;
use App\Http\Controllers\LotteryDrawController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RiskSettingsController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Lottery Bets (General + Admin)
    Route::prefix('bets')->name('bets.')->group(function () {
        Route::get('/', [LotteryBetController::class, 'index'])->name('index');
        Route::post('/store', [LotteryBetController::class, 'store'])->name('store');
        Route::get('/history', [LotteryBetController::class, 'history'])->name('history');
        Route::delete('/{id}', [LotteryBetController::class, 'destroy'])->name('destroy');
    });

    // Admin Only Routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {

        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'createUser'])->name('users.create');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');

        // Config Management (เก่า - อาจลบทิ้งได้)
        Route::get('/config', [AdminController::class, 'config'])->name('config');
        Route::post('/config', [AdminController::class, 'updateConfig'])->name('config.update');

        // Risk Settings (ใหม่ - แทนที่ Config)
        Route::get('/risk-settings', [RiskSettingsController::class, 'index'])->name('risk-settings');
        Route::put('/risk-settings', [RiskSettingsController::class, 'update'])->name('risk-settings.update');

        // Lottery Draw Results
        Route::get('/draws', [LotteryDrawController::class, 'index'])->name('draws');
        Route::post('/draws', [LotteryDrawController::class, 'store'])->name('draws.store');
        Route::get('/draws/{id}/results', [LotteryDrawController::class, 'results'])->name('draws.results');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/summary/{drawId}', [ReportController::class, 'summary'])->name('summary');
            Route::get('/pdf/{drawId}', [ReportController::class, 'exportPDF'])->name('pdf');
            Route::get('/statistics', [ReportController::class, 'statistics'])->name('statistics');
            // เพิ่ม Route สำหรับลบ Bet (เฉพาะงวดที่ยังไม่ประกาศผล)
            Route::delete('/bets/{betId}', [ReportController::class, 'deleteBet'])->name('bets.delete');
            Route::delete('/admin/reports/bets/{betId}', [ReportController::class, 'deleteBet'])
                ->name('admin.reports.bets.delete');
        });
    });
});
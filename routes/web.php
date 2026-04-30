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

    // API สำหรับดึงงวดที่เปิดรับแทง
    Route::get('/api/open-draws', [DashboardController::class, 'getOpenDraws'])->name('api.open-draws');

    // [FIX #7] เพิ่ม route สำหรับ realtimeSales ที่มี method แต่ขาด route
    Route::get('/api/realtime-sales', [DashboardController::class, 'realtimeSales'])->name('api.realtime-sales');

    // Lottery Bets (General + Admin)
    Route::prefix('bets')->name('bets.')->group(function () {
        Route::get('/', [LotteryBetController::class, 'index'])->name('index');
        Route::post('/store', [LotteryBetController::class, 'store'])->name('store');
        Route::get('/history', [LotteryBetController::class, 'history'])->name('history');
        Route::delete('/{id}', [LotteryBetController::class, 'destroy'])->name('destroy');
        Route::get('/export-excel', [LotteryBetController::class, 'exportExcel'])->name('export-excel');
    });

    // Reports — เปิดให้พนักงาน (general) ดูได้ด้วย — ต้องอยู่นอก admin middleware group
    Route::middleware(['staff_or_admin'])->prefix('admin/reports')->name('admin.reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/summary/{drawId}', [ReportController::class, 'summary'])->name('summary');
    });

    // Admin Only Routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {

        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'createUser'])->name('users.create');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');

        // Config Management
        Route::get('/config', [AdminController::class, 'config'])->name('config');
        Route::post('/config', [AdminController::class, 'updateConfig'])->name('config.update');

        // Risk Settings
        Route::get('/risk-settings', [RiskSettingsController::class, 'index'])->name('risk-settings');
        Route::put('/risk-settings', [RiskSettingsController::class, 'update'])->name('risk-settings.update');

        // Lottery Draw Results
        Route::get('/draws', [LotteryDrawController::class, 'index'])->name('draws');
        Route::post('/draws', [LotteryDrawController::class, 'store'])->name('draws.store');
        Route::get('/draws/{id}/results', [LotteryDrawController::class, 'results'])->name('draws.results');

        // Reports actions — admin เท่านั้น
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/pdf/{drawId}', [ReportController::class, 'exportPDF'])->name('pdf');
            Route::get('/statistics', [ReportController::class, 'statistics'])->name('statistics');
            Route::delete('/bets/{betId}', [ReportController::class, 'deleteBet'])->name('bets.delete');
            Route::get('/export-excel/{drawId}', [ReportController::class, 'exportExcel'])->name('export-excel');
            Route::get('/export-customer-summary/{drawId}', [ReportController::class, 'exportCustomerSummary'])->name('export-customer-summary');
            Route::get('/export-over-limit/{drawId}', [ReportController::class, 'exportOverLimit'])->name('export-over-limit');
        });
    });
});

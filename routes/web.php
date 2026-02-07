<?php
// routes/web.php

use App\Http\Controllers\BetController;
use App\Http\Controllers\LotteryResultController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// หน้าแรก
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// เส้นทางการเดิมพัน
Route::prefix('bets')->name('bets.')->group(function () {
    Route::get('/', [BetController::class, 'index'])->name('index');
    Route::post('/', [BetController::class, 'store'])->name('store');
    Route::get('/statistics', [BetController::class, 'statistics'])->name('statistics');
    Route::get('/sales', [BetController::class, 'sales'])->name('sales');
    Route::get('/history', [BetController::class, 'history'])->name('history');
});

// เส้นทางผลหวย
Route::prefix('lottery')->name('lottery.')->group(function () {
    Route::get('/', [LotteryResultController::class, 'index'])->name('index');
    Route::post('/', [LotteryResultController::class, 'store'])->name('store');
});

// API routes สำหรับ real-time updates
Route::get('/api/sales/realtime', [DashboardController::class, 'realtimeSales']);
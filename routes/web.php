<?php
// routes/web.php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LotteryBetController;
use App\Http\Controllers\LotteryDrawController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReportController;
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

        // Config Management
        Route::get('/config', [AdminController::class, 'config'])->name('config');
        Route::post('/config', [AdminController::class, 'updateConfig'])->name('config.update');

        // Lottery Draw Results
        Route::get('/draws', [LotteryDrawController::class, 'index'])->name('draws');
        Route::post('/draws', [LotteryDrawController::class, 'store'])->name('draws.store');
        Route::get('/draws/{id}/results', [LotteryDrawController::class, 'results'])->name('draws.results');

        // Reports
        Route::get('/reports/summary/{drawId}', [ReportController::class, 'summary'])->name('reports.summary');
        Route::get('/reports/pdf/{drawId}', [ReportController::class, 'exportPDF'])->name('reports.pdf');
        Route::get('/reports/statistics', [ReportController::class, 'statistics'])->name('reports.statistics');
    });
});
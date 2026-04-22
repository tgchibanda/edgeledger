<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TradeDatabaseController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\PairController;
use App\Http\Controllers\Api\TradingSessionController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\TrainingImageController;
use App\Http\Controllers\Api\ScannerController;

use App\Http\Controllers\Api\CandleController;

Route::post('/login', [AuthController::class, 'login']);

// Public routes — no auth needed
Route::get('images/{path}',   [ImageController::class, 'show'])->where('path', '.*');
Route::get('debug-storage',   [ImageController::class, 'debugStorage']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::get('/user',     [AuthController::class, 'me']);

    // Categories
    Route::get('categories/suggest', [CategoryController::class, 'suggest']);
    Route::apiResource('categories', CategoryController::class);

    // Trade Database
    Route::post('trade-database/filter',           [TradeDatabaseController::class, 'filter']);
    Route::post('trade-database/{trade}/promote',  [TradeDatabaseController::class, 'promote']);
    Route::apiResource('trade-database', TradeDatabaseController::class);

    // Journals
    Route::post('journals/{journal}/complete',  [JournalController::class, 'complete']);
    Route::post('journals/{journal}/promote',   [JournalController::class, 'promoteToDatabase']);
    Route::apiResource('journals', JournalController::class);

    // Analytics
    Route::get('analytics/win-rates',  [AnalyticsController::class, 'winRates']);
    Route::get('analytics/summary',    [AnalyticsController::class, 'summary']);
    Route::get('analytics/streaks',    [AnalyticsController::class, 'streaks']);
    Route::get('analytics/expectancy', [AnalyticsController::class, 'expectancy']);

    // Pairs & Sessions
    Route::apiResource('pairs', PairController::class);
    Route::get('trading-sessions', [TradingSessionController::class, 'index']);

    // Images
    // Training images
    Route::get('training-images/stats',    [TrainingImageController::class, 'stats']);
    Route::get('training-images/export',   [TrainingImageController::class, 'exportManifest']);
    Route::apiResource('training-images',  TrainingImageController::class)->only(['index','store','destroy']);

    // Candles / Replay
    Route::get('candles',       [CandleController::class, 'index']);
    Route::get('candles/range', [CandleController::class, 'range']);

    // Scanner
    Route::get('scanner/bias',          [ScannerController::class, 'getBias']);
    Route::post('scanner/bias',         [ScannerController::class, 'setBias']);
    Route::delete('scanner/bias',       [ScannerController::class, 'cancelBias']);
    Route::get('scanner/history',       [ScannerController::class, 'history']);

    // Superuser
    Route::middleware('superuser')->group(function () {
        Route::apiResource('admin/users', UserManagementController::class);
        Route::post('admin/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive']);
    });
});

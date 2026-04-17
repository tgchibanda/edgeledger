<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController, CategoryController,
    TradeDatabaseController, JournalController, AnalyticsController,
    UserManagementController, PairController, TradingSessionController,};


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Trade Database
    Route::apiResource('trade-database', TradeDatabaseController::class);
    Route::post('trade-database/{trade}/promote', [TradeDatabaseController::class, 'promote']);
    Route::post('trade-database/filter', [TradeDatabaseController::class, 'filter']);

    // Journals
    Route::apiResource('journals', JournalController::class);
    Route::post('journals/{journal}/complete', [JournalController::class, 'complete']);
    Route::post('journals/{journal}/promote', [JournalController::class, 'promoteToDatabase']);

    // Analytics
    Route::get('analytics/win-rates', [AnalyticsController::class, 'winRates']);
    Route::get('analytics/summary', [AnalyticsController::class, 'summary']);

    // Pairs & Sessions
    Route::apiResource('pairs', PairController::class);
    Route::get('trading-sessions', [TradingSessionController::class, 'index']);

    // Superuser only
    Route::middleware('can:superuser')->group(function () {
        Route::apiResource('users', UserManagementController::class);
        Route::post('users/{user}/toggle-active', [UserManagementController::class, 'toggleActive']);
    });
});

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
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\AdminSubscriptionController;

// Public
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('referral/track', [ReferralController::class, 'track']);
Route::get('images/{path}',  [ImageController::class,   'show'])->where('path', '.*');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [AuthController::class, 'me']);

    // Categories
    Route::get('categories/suggest', [CategoryController::class, 'suggest']);
    Route::apiResource('categories', CategoryController::class);

    // Trade Database
    Route::post('trade-database/filter',          [TradeDatabaseController::class, 'filter']);
    Route::post('trade-database/{trade}/promote', [TradeDatabaseController::class, 'promote']);
    Route::apiResource('trade-database', TradeDatabaseController::class);

    // Journals
    Route::post('journals/{journal}/complete', [JournalController::class, 'complete']);
    Route::post('journals/{journal}/promote',  [JournalController::class, 'promoteToDatabase']);
    Route::apiResource('journals', JournalController::class);

    // Analytics
    Route::get('analytics/win-rates',  [AnalyticsController::class, 'winRates']);
    Route::get('analytics/summary',    [AnalyticsController::class, 'summary']);
    Route::get('analytics/streaks',    [AnalyticsController::class, 'streaks']);
    Route::get('analytics/expectancy', [AnalyticsController::class, 'expectancy']);

    // Pairs & Sessions
    Route::apiResource('pairs', PairController::class);
    Route::get('trading-sessions', [TradingSessionController::class, 'index']);

    // Subscription
    Route::get('subscription',         [SubscriptionController::class, 'plan']);
    Route::post('subscription',        [SubscriptionController::class, 'subscribe']);
    Route::post('subscription/renew',  [SubscriptionController::class, 'renew']);
    Route::post('subscription/cancel', [SubscriptionController::class, 'cancel']);
    Route::get('subscription/history', [SubscriptionController::class, 'history']);

    // Referral
    Route::get('referral/stats',              [ReferralController::class, 'stats']);
    Route::post('referral/redeem',            [ReferralController::class, 'redeem']);
    Route::get('referral/redemptions',        [ReferralController::class, 'redemptionHistory']);

    // Superuser — users + subscription admin
    Route::middleware('superuser')->group(function () {
        Route::apiResource('admin/users', UserManagementController::class);
        Route::post('admin/users/{user}/toggle-active',   [UserManagementController::class,    'toggleActive']);
        Route::post('admin/users/{user}/activate-sub',    [AdminSubscriptionController::class, 'activateSubscription']);
        Route::get('admin/subscription/plan',             [AdminSubscriptionController::class, 'getPlan']);
        Route::put('admin/subscription/plan',             [AdminSubscriptionController::class, 'updatePlan']);
        Route::get('admin/subscription/subscriptions',    [AdminSubscriptionController::class, 'subscriptions']);
        Route::get('admin/subscription/redemptions',      [AdminSubscriptionController::class, 'redemptions']);
        Route::patch('admin/subscription/redemptions/{redemption}', [AdminSubscriptionController::class, 'processRedemption']);
        Route::get('admin/subscription/overview',         [AdminSubscriptionController::class, 'overview']);
    });
});

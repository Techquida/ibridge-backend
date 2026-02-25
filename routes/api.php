<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Enums\RoleEnum;

// ─── Public routes ────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ─── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout',         [AuthController::class, 'logout']);
    Route::get('/profile',         [AuthController::class, 'profile']);
    Route::patch('/profile',       [AuthController::class, 'updateProfile']);

    // ── Student routes ────────────────────────────────────────────────────────
    Route::middleware('role:' . RoleEnum::STUDENT->value)->group(function () {

        // Sessions
        Route::get('/sessions',       [SessionController::class, 'index']);
        Route::post('/sessions',      [SessionController::class, 'store']);
        Route::get('/sessions/{id}',  [SessionController::class, 'show']);

        // Analytics
        Route::get('/analytics',     [AnalyticsController::class, 'index']);

        // Leaderboard
        Route::get('/leaderboard',   [LeaderboardController::class, 'index']);

        // Subscription renewal via Paystack
        Route::post('/subscription/verify', [SubscriptionController::class, 'verify']);
    });

    // ── Partner routes ────────────────────────────────────────────────────────
    Route::middleware('role:' . RoleEnum::PARTNER->value)->prefix('partner')->group(function () {
        Route::get('/dashboard', [PartnerController::class, 'dashboard']);
    });

    // ── School Admin routes ───────────────────────────────────────────────────
    Route::middleware('role:' . RoleEnum::SCHOOL_ADMIN->value)->prefix('school')->group(function () {
        Route::get('/students',              [SchoolController::class, 'students']);
        Route::get('/students/active-count', [SchoolController::class, 'activeStudentsCount']);
        Route::get('/summary',               [SchoolController::class, 'summary']);
    });
});

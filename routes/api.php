<?php

use App\Enums\RoleEnum;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\PaystackWebhookController;
use App\Http\Controllers\Api\QuestionCheckController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\StudyGroupController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─── Public routes ────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/paystack/webhook', [PaystackWebhookController::class, 'handle']);
Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);

// ─── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', App\Http\Middleware\EnsureNotSuspended::class])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::patch('/profile', [AuthController::class, 'updateProfile']);

    // ── Student routes ────────────────────────────────────────────────────────
    Route::middleware('role:'.RoleEnum::STUDENT->value)->group(function () {

        // Questions (randomized per request — no correct answers in response)
        Route::get('/questions', [QuestionController::class, 'index']);

        // Per-question answer check (Light Mode instant feedback)
        Route::post('/questions/{id}/check', [QuestionCheckController::class, 'check']);

        // Subjects (distinct active subjects from questions table)
        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::get('/departments', [DepartmentController::class, 'index']);

        // Sessions (submit answers → server grades → returns GradedSessionResource)
        Route::get('/sessions', [SessionController::class, 'index']);
        Route::post('/sessions', [SessionController::class, 'store']);
        Route::get('/sessions/{id}', [SessionController::class, 'show']);

        // Analytics
        Route::get('/analytics', [AnalyticsController::class, 'index']);

        // Leaderboard
        Route::get('/leaderboard', [LeaderboardController::class, 'index']);

        // Subscription renewal via Paystack
        Route::post('/subscription/initialize', [SubscriptionController::class, 'initialize']);
        Route::post('/subscription/verify', [SubscriptionController::class, 'verify']);

        // AI Chats (Gemini-powered, topic-scoped)
        Route::middleware('throttle:10,1')->group(function () {
            Route::get('/ai-chats', [AiChatController::class, 'index']);
            Route::post('/ai-chats', [AiChatController::class, 'store']);
            Route::get('/ai-chats/{id}', [AiChatController::class, 'show']);
            Route::post('/ai-chats/{id}/messages', [AiChatController::class, 'sendMessage']);
        });

        // Study Groups
        Route::get('/groups', [StudyGroupController::class, 'index']);
        Route::get('/groups/browse', [StudyGroupController::class, 'browse']);
        Route::post('/groups', [StudyGroupController::class, 'store']);
        Route::post('/groups/join', [StudyGroupController::class, 'join'])->middleware('throttle:5,1');
        Route::get('/groups/{id}', [StudyGroupController::class, 'show']);
        Route::post('/groups/{id}/leave', [StudyGroupController::class, 'leave']);
        Route::get('/groups/{id}/messages', [StudyGroupController::class, 'messages']);
        Route::post('/groups/{id}/messages', [StudyGroupController::class, 'sendMessage']);
    });

    // ── Partner routes ────────────────────────────────────────────────────────
    Route::middleware('role:'.RoleEnum::PARTNER->value)->prefix('partner')->group(function () {
        Route::get('/dashboard', [PartnerController::class, 'dashboard']);
    });

    // ── School Admin routes ───────────────────────────────────────────────────
    Route::middleware('role:'.RoleEnum::SCHOOL_ADMIN->value)->prefix('school')->group(function () {
        Route::get('/students', [SchoolController::class, 'students']);
        Route::get('/students/active-count', [SchoolController::class, 'activeStudentsCount']);
        Route::get('/summary', [SchoolController::class, 'summary']);
    });
});

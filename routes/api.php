<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LearningPlanController;
use App\Http\Controllers\ReflectionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeekItemController;
use App\Http\Controllers\Api\FocusSessionController;
use App\Http\Controllers\ExamModeController;
use App\Http\Controllers\NotificationController;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/profile',  [AuthController::class, 'profile']);
        Route::post('/setup',   [AuthController::class, 'setup']);
        Route::post('/logout',  [AuthController::class, 'logout']);
        Route::patch('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/streak', [AuthController::class, 'updateStreak']);
        });

    Route::apiResource('semesters',   SemesterController::class);
    Route::apiResource('courses',     CourseController::class);
    Route::apiResource('tasks',       TaskController::class);
    Route::apiResource('reflections', ReflectionController::class);

    Route::prefix('self-learning')->group(function () {
        Route::get('/',    [LearningPlanController::class, 'index']);
        Route::post('/',   [LearningPlanController::class, 'store']);
        Route::get('/{id}',    [LearningPlanController::class, 'show']);
        Route::patch('/{id}',  [LearningPlanController::class, 'update']);
        Route::delete('/{id}', [LearningPlanController::class, 'destroy']);

        Route::post('/{id}/stages',                    [LearningPlanController::class, 'storeStage']);
        Route::patch('/{id}/stages/{stageId}',         [LearningPlanController::class, 'updateStage']);
        Route::post('/{id}/milestones',                [LearningPlanController::class, 'storeMilestone']);
        Route::patch('/{id}/milestones/{milestoneId}', [LearningPlanController::class, 'updateMilestone']);

        Route::post('/courses/{courseId}/week-items', [WeekItemController::class, 'store']);
        Route::patch('/courses/{courseId}/week-items/{id}', [WeekItemController::class, 'update']);
        Route::delete('/courses/{courseId}/week-items/{id}', [WeekItemController::class, 'destroy']);
        });
        Route::prefix('focus')->group(function () {
        Route::get('/sessions', [FocusSessionController::class, 'index']);
        Route::post('/sessions', [FocusSessionController::class, 'store']);
        Route::get('/sessions/{id}', [FocusSessionController::class, 'show']);
        Route::delete('/sessions/{id}', [FocusSessionController::class, 'destroy']);
        Route::get('/analytics', [FocusSessionController::class, 'analytics']);
        });

    Route::prefix('courses/{courseId}/exam-mode/{examId}')->group(function () {
        Route::get('/', [ExamModeController::class, 'show']);
        Route::patch('/', [ExamModeController::class, 'update']);
        Route::post('/topics', [ExamModeController::class, 'storeTopic']);
        Route::patch('/topics/{topicId}', [ExamModeController::class, 'updateTopic']);
        Route::delete('/topics/{topicId}', [ExamModeController::class, 'destroyTopic']);
    });
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/', [NotificationController::class, 'store']);
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllRead']);
        Route::patch('/{id}', [NotificationController::class, 'update']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'destroyAll']);
    });
});

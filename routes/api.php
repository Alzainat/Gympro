<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberTrainerSubscriptionController;

// Auth
use App\Http\Controllers\Api\Auth\AuthController;

// Member Controllers
use App\Http\Controllers\Api\Member\ProfileController;
use App\Http\Controllers\Api\Member\WorkoutController;
use App\Http\Controllers\Api\Member\DietController;
use App\Http\Controllers\Api\Member\ChatController;
use App\Http\Controllers\Api\Member\ProgressPhotoController;
use App\Http\Controllers\Api\MemberHealthProfileController;

// Member extra
use App\Http\Controllers\Api\Member\HealthConditionController;
use App\Http\Controllers\Api\Member\PaymentController;
use App\Http\Controllers\Api\Member\BookingController;

// Trainer Controllers
use App\Http\Controllers\Api\Trainer\TrainerScheduleController;
use App\Http\Controllers\Api\Trainer\TrainingSessionController;

// Trainer extra
use App\Http\Controllers\Api\Trainer\TrainerBookingsController;

// Common Controllers
use App\Http\Controllers\Api\Common\ExerciseController;
use App\Http\Controllers\Api\Common\TrainerDirectoryController;
use App\Http\Controllers\Api\Common\NotificationsController;

use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Auth)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('/reset-password', [NewPasswordController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth / User
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Notifications (member + trainer)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:member,trainer')->group(function () {
        Route::get('/notifications', [NotificationsController::class, 'index']);
        Route::get('/notifications/unread', [NotificationsController::class, 'unread']);
        Route::get('/notifications/unread-count', [NotificationsController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [NotificationsController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationsController::class, 'markAllAsRead']);
    });

    /*
    |--------------------------------------------------------------------------
    | Common (member + trainer) - Exercises
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:member,trainer')->group(function () {
        Route::get('/exercises', [ExerciseController::class, 'index']);
        Route::get('/exercises/{id}', [ExerciseController::class, 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Common (member only here) - Trainers Directory & Sessions
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:member')->group(function () {
        Route::get('/trainers', [TrainerDirectoryController::class, 'index']);
        Route::get('/trainers/{trainerId}', [TrainerDirectoryController::class, 'show']);
        Route::get('/trainers/{trainerId}/sessions', [TrainerDirectoryController::class, 'sessions']);
        Route::get('/trainers/{trainerId}/schedule', [TrainerDirectoryController::class, 'schedule']);

        Route::post('/member/trainers/{trainer}/subscribe', [MemberTrainerSubscriptionController::class, 'store']);
        Route::delete('/member/trainer-subscription', [MemberTrainerSubscriptionController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Member Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:member')->group(function () {

        // Profile
        Route::get('/member/me', [ProfileController::class, 'me']);

        // Workouts
        Route::get('/member/workouts', [WorkoutController::class, 'index']);
        Route::get('/member/workouts/{id}', [WorkoutController::class, 'show']);
        Route::post('/member/workouts/{id}/assign', [WorkoutController::class, 'assign']);
        Route::post('/member/workouts/log', [WorkoutController::class, 'saveExerciseLog']);

        // Meals / Diet
        Route::get('/member/diet-plans', [DietController::class, 'myPlans']);
        Route::get('/member/meals', [DietController::class, 'myMeals']);

        // Health conditions
        Route::get('/member/health-conditions', [HealthConditionController::class, 'index']);
        Route::post('/member/health-conditions', [HealthConditionController::class, 'store']);
        Route::delete('/member/health-conditions/{id}', [HealthConditionController::class, 'destroy']);
        Route::post('/member/health-conditions/check', [HealthConditionController::class, 'check']);

        // Booking
        Route::get('/member/bookings', [BookingController::class, 'index']);
        Route::post('/member/bookings/sessions', [BookingController::class, 'bookSession']);
        Route::post('/member/bookings/sessions/{id}/cancel', [BookingController::class, 'cancelSession']);

        // Payments / Plans
        Route::get('/member/plans', [PaymentController::class, 'plans']);
        Route::post('/member/subscribe', [PaymentController::class, 'subscribe']);
        Route::get('/member/payments', [PaymentController::class, 'myPayments']);
        Route::get('/member/plan-details', [PaymentController::class, 'planDetails']);

        // Chat
        Route::get('/member/chat/allowed-trainers', [ChatController::class, 'allowedTrainers']);
        Route::get('/member/chat/inbox', [ChatController::class, 'inbox']);
        Route::get('/member/chat/thread/{trainerId}', [ChatController::class, 'thread']);
        Route::post('/member/chat/send', [ChatController::class, 'send']);
        Route::post('/member/chat/{trainerId}/read', [ChatController::class, 'markRead']);

        // Health Profile
        Route::get('/member/health-profile', [MemberHealthProfileController::class, 'show']);
        Route::post('/member/health-profile', [MemberHealthProfileController::class, 'upsert']);

        // Progress Photos
        Route::get('/member/progress-photos', [ProgressPhotoController::class, 'index']);
        Route::post('/member/progress-photos', [ProgressPhotoController::class, 'store']);
        Route::delete('/member/progress-photos/{id}', [ProgressPhotoController::class, 'destroy']);
        Route::get('/member/progress-photos/comparison', [ProgressPhotoController::class, 'comparison']);
    });

    /*
    |--------------------------------------------------------------------------
    | Trainer Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:trainer')->group(function () {

        // Schedule
        Route::get('/trainer/schedule', [TrainerScheduleController::class, 'index']);
        Route::post('/trainer/schedule', [TrainerScheduleController::class, 'store']);
        Route::patch('/trainer/schedule/{id}/toggle', [TrainerScheduleController::class, 'toggle']);

        // Sessions
        Route::get('/trainer/sessions', [TrainingSessionController::class, 'index']);
        Route::post('/trainer/sessions', [TrainingSessionController::class, 'store']);
        Route::patch('/trainer/sessions/{id}/toggle', [TrainingSessionController::class, 'toggle']);

        // Bookings
        Route::get('/trainer/bookings', [TrainerBookingsController::class, 'index']);
    });
});

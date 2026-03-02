<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Api\Auth\AuthController;

// Member Controllers
use App\Http\Controllers\Api\Member\ProfileController;
use App\Http\Controllers\Api\Member\WorkoutController;
use App\Http\Controllers\Api\Member\DietController;
use App\Http\Controllers\Api\Member\ChatController;
use App\Http\Controllers\Api\MemberHealthProfileController;

// ✅ NEW: Member extra
use App\Http\Controllers\Api\Member\HealthConditionController;
use App\Http\Controllers\Api\Member\PaymentController;
use App\Http\Controllers\Api\Member\BookingController;

// Trainer Controllers
use App\Http\Controllers\Api\Trainer\TrainerScheduleController;
use App\Http\Controllers\Api\Trainer\TrainingSessionController;

// ✅ NEW: Trainer extra
use App\Http\Controllers\Api\Trainer\TrainerBookingsController;

// Common Controllers
use App\Http\Controllers\Api\Common\ExerciseController;

// ✅ NEW: trainers listing (common)
use App\Http\Controllers\Api\Common\TrainerDirectoryController;

// ✅ Notifications (Common)
use App\Http\Controllers\Api\Common\NotificationsController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Auth)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

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
    | المطلوب: صفحة trainers + صفحة booking
    */
    Route::middleware('role:member')->group(function () {
        Route::get('/trainers', [TrainerDirectoryController::class, 'index']);           // cards للمدربين
        Route::get('/trainers/{trainerId}', [TrainerDirectoryController::class, 'show']); // تفاصيل
        Route::get('/trainers/{trainerId}/sessions', [TrainerDirectoryController::class, 'sessions']); // جلسات مدرب
        Route::get('/trainers/{trainerId}/schedule', [TrainerDirectoryController::class, 'schedule']); // (اختياري) availability
    });

    /*
    |--------------------------------------------------------------------------
    | Member Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:member')->group(function () {

        // Profile
        Route::get('/member/me', [ProfileController::class, 'me']);

        // Workouts (جدول التمارين)
        Route::get('/member/workouts', [WorkoutController::class, 'index']);
        Route::get('/member/workouts/{id}', [WorkoutController::class, 'show']);
        Route::post('/member/workouts/{id}/assign', [WorkoutController::class, 'assign']); // (موجود عندك)

        // Meals / Diet
        Route::get('/member/diet-plans', [DietController::class, 'myPlans']);
        Route::get('/member/meals', [DietController::class, 'myMeals']); // ✅ NEW: جدول الأكل (member_meals + meals)

        // Health conditions + blocked exercises (simple)
        Route::post('/member/health-conditions', [HealthConditionController::class, 'store']);
        Route::post('/member/health-conditions/check', [HealthConditionController::class, 'check']);
        // check: ترجع blocked_exercises بناءً على contraindications + exercises

        // Booking (جلسات تدريب)
        Route::get('/member/bookings', [BookingController::class, 'index']); // حجوزاتي
        Route::post('/member/bookings/sessions', [BookingController::class, 'bookSession']); // ✅ يحجز session_id
        Route::post('/member/bookings/sessions/{id}/cancel', [BookingController::class, 'cancelSession']);

        // Payments / Plans
        Route::get('/member/plans', [PaymentController::class, 'plans']); // 3 خطط
        Route::post('/member/subscribe', [PaymentController::class, 'subscribe']);
        // subscribe: يسجل payment + يوزع workouts + meals حسب الخطة
        Route::get('/member/payments', [PaymentController::class, 'myPayments']);
        Route::get('/member/plan-details', [PaymentController::class, 'planDetails']);

        // Chat (member)
        Route::get('/member/chat/allowed-trainers', [ChatController::class, 'allowedTrainers']);
        Route::get('/member/chat/inbox', [ChatController::class, 'inbox']);
        Route::get('/member/chat/thread/{trainerId}', [ChatController::class, 'thread']);
        Route::post('/member/chat/send', [ChatController::class, 'send']);
        Route::post('/member/chat/{trainerId}/read', [ChatController::class, 'markRead']);


        Route::middleware('auth:sanctum')->group(function () {
    Route::get('/member/health-profile', [MemberHealthProfileController::class, 'show']);
    Route::post('/member/health-profile', [MemberHealthProfileController::class, 'upsert']);
});


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

        // ✅ NEW: Bookings that came from members
        Route::get('/trainer/bookings', [TrainerBookingsController::class, 'index']);
        // index: يعرض session_bookings + بيانات العضو + الجلسة
    });
});

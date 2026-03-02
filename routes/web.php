<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Services\TrainerNotifier;
use App\Models\Profile;

Route::get('/', function () {
    return view('auth.choose-role');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
     
});

Route::get('/trainer/test-notif', function () {
    $trainerProfile = Profile::where('role', 'trainer')->firstOrFail();

    TrainerNotifier::notifyTrainerProfile(
        $trainerProfile,
        'Bell Works ✅',
        'If you see this in the bell, everything is correct.',
        'info',
        url('/trainer')
    );

    return 'sent';
});

require __DIR__.'/auth.php';

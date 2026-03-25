<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

// أضف هذين السطرين
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        // 👇 هذا هو المهم: تخصيص رابط reset ليذهب للـ React
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return 'http://localhost:5174/reset-password?token='
                . $token
                . '&email='
                . urlencode($user->email);
        });
    }
}

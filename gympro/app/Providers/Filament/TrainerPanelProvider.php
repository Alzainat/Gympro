<?php

namespace App\Providers\Filament;

use App\Filament\Trainer\Resources\MemberResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TrainerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('trainer')
            ->path('trainer')
            ->login()
            ->authGuard('web')
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->brandName('GymPro Trainer')
            ->sidebarCollapsibleOnDesktop()
            

            ->colors([
                'primary' => Color::Indigo,
            ])

            /**
             * ✅ Register critical resources manually (guaranteed to show)
             */
            ->resources([
                MemberResource::class,
            ])

            /**
             * ✅ Keep auto-discovery for the rest
             */
            ->discoverResources(
                in: app_path('Filament/Trainer/Resources'),
                for: 'App\\Filament\\Trainer\\Resources'
            )

            ->discoverWidgets(
                in: app_path('Filament/Trainer/Widgets'),
                for: 'App\\Filament\\Trainer\\Widgets',
            )
            ->widgets([
                \App\Filament\Trainer\Widgets\WorkoutsMealsChart::class,
                \App\Filament\Trainer\Widgets\MembersGrowthChart::class,
                \App\Filament\Trainer\Widgets\SessionsChart::class,
            ])

            

            ->discoverPages(
                in: app_path('Filament/Trainer/Pages'),
                for: 'App\\Filament\\Trainer\\Pages'
            )
            ->pages([
                \App\Filament\Trainer\Pages\TrainerDashboard::class,
            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])

            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
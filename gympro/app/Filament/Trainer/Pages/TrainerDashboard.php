<?php

namespace App\Filament\Trainer\Pages;

use Filament\Pages\Dashboard;

class TrainerDashboard extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Trainer Dashboard';
    protected static ?int $navigationSort = 1;

    /**
     * Dashboard widgets (same layout as Admin)
     */
    public function getWidgets(): array
    {
        return [
            \Filament\Widgets\AccountWidget::class,
            \Filament\Widgets\FilamentInfoWidget::class,

            // Trainer-specific widgets later
            \App\Filament\Trainer\Widgets\TrainerStats::class,
            \App\Filament\Trainer\Widgets\WorkoutsMealsChart::class,
            \App\Filament\Trainer\Widgets\MembersGrowthChart::class,
            \App\Filament\Trainer\Widgets\SessionsChart::class,
            \App\Filament\Trainer\Widgets\AvailabilityCalendar::class,
        ];
    }

    /**
     * Same grid behavior as Admin
     */
    public function getColumns(): int | string | array
    {
        return 2;
    }
}
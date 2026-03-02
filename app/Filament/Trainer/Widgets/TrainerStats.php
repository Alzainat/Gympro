<?php

namespace App\Filament\Trainer\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Profile;
use App\Models\WorkoutRoutine;
use App\Models\TrainingSession;
use App\Models\Meal;
use Carbon\Carbon;

class TrainerStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $trainerId = auth()->user()->profile->id;

        return [

            Stat::make(
                'My Members',
                Profile::where('trainer_id', $trainerId)
                    ->where('role', 'member')
                    ->count()
            )
            ->icon('heroicon-o-users'),

            Stat::make(
                'Workout Routines',
                WorkoutRoutine::where('creator_id', $trainerId)->count()
            )
            ->icon('heroicon-o-fire'),

            Stat::make(
                'Today Sessions',
                TrainingSession::where('trainer_id', $trainerId)
                    ->whereDate('session_date', Carbon::today())
                    ->count()
            )
            ->icon('heroicon-o-calendar-days'),

            Stat::make(
                'Meals Created',
                Meal::where('trainer_id', $trainerId)->count()
            )
            ->icon('heroicon-o-cake'),

        ];
    }
}

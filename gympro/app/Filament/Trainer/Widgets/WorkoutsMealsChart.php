<?php

namespace App\Filament\Trainer\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\WorkoutRoutine;
use App\Models\Meal;

class WorkoutsMealsChart extends ChartWidget
{
    protected static ?string $heading = 'Workouts vs Meals';

    protected function getData(): array
    {
        $trainerId = auth()->user()->profile->id;

        return [
            'datasets' => [
                [
                    'label' => 'Total',
                    'data' => [
                        WorkoutRoutine::where('creator_id', $trainerId)->count(),
                        Meal::where('trainer_id', $trainerId)->count(),
                    ],
                ],
            ],
            'labels' => ['Workouts', 'Meals'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
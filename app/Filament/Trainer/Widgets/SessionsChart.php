<?php

namespace App\Filament\Trainer\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\TrainingSession;
use Carbon\Carbon;

class SessionsChart extends ChartWidget
{
    protected static ?string $heading = 'Sessions (Last 7 Days)';

    protected function getData(): array
    {
        $trainerId = auth()->user()->profile->id;

        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $labels[] = $date->format('D');

            $data[] = TrainingSession::where('trainer_id', $trainerId)
                ->whereDate('session_date', $date)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sessions',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
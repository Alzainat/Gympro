<?php

namespace App\Filament\Trainer\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Profile;
use Carbon\Carbon;

class MembersGrowthChart extends ChartWidget
{
    protected static ?string $heading = 'Members Growth (Last 7 Days)';

    protected function getData(): array
    {
        $trainerId = auth()->user()->profile->id;

        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $labels[] = $date->format('D');

            $data[] = Profile::where('trainer_id', $trainerId)
                ->where('role', 'member')
                ->whereDate('created_at', '<=', $date)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Members',
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
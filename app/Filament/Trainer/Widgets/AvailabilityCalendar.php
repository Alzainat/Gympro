<?php

namespace App\Filament\Trainer\Widgets;

use Filament\Widgets\Widget;
use App\Models\TrainerAvailability;

class AvailabilityCalendar extends Widget
{
    protected static string $view = 'filament.trainer.widgets.availability-calendar';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'availability' => TrainerAvailability::where(
                'trainer_id',
                auth()->user()->profile->id
            )
            ->where('is_available', true)
            ->get(),
        ];
    }
}
<?php

namespace App\Filament\Admin\Resources\WorkoutRoutineResource\Pages;

use App\Filament\Admin\Resources\WorkoutRoutineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkoutRoutines extends ListRecords
{
    protected static string $resource = WorkoutRoutineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

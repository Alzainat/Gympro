<?php

namespace App\Filament\Trainer\Resources\TrainingSessionResource\Pages;

use App\Filament\Trainer\Resources\TrainingSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTrainingSession extends CreateRecord
{
    protected static string $resource = TrainingSessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['trainer_id'] = auth()->id(); // أو auth()->user()->trainer->id حسب نظامك
        return $data;
    }
}

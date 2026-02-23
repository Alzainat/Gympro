<?php

namespace App\Filament\Trainer\Resources\TrainerAvailabilityResource\Pages;

use App\Filament\Trainer\Resources\TrainerAvailabilityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTrainerAvailability extends CreateRecord
{
    protected static string $resource = TrainerAvailabilityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['trainer_id'] = auth()->user()->profile->id;

        return $data;
    }
}
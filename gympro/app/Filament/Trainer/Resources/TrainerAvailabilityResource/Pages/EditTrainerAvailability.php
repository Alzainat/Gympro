<?php

namespace App\Filament\Trainer\Resources\TrainerAvailabilityResource\Pages;

use App\Filament\Trainer\Resources\TrainerAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrainerAvailability extends EditRecord
{
    protected static string $resource = TrainerAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

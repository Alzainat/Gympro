<?php

namespace App\Filament\Trainer\Resources\TrainingSessionResource\Pages;

use App\Filament\Trainer\Resources\TrainingSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrainingSession extends EditRecord
{
    protected static string $resource = TrainingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

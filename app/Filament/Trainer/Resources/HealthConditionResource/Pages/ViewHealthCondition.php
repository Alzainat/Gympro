<?php

namespace App\Filament\Trainer\Resources\HealthConditionResource\Pages;

use App\Filament\Trainer\Resources\HealthConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHealthCondition extends ViewRecord
{
    protected static string $resource = HealthConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

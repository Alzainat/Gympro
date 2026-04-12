<?php

namespace App\Filament\Trainer\Resources\HealthConditionResource\Pages;

use App\Filament\Trainer\Resources\HealthConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHealthCondition extends EditRecord
{
    protected static string $resource = HealthConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

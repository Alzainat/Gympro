<?php

namespace App\Filament\Trainer\Resources\HealthConditionResource\Pages;

use App\Filament\Trainer\Resources\HealthConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHealthConditions extends ListRecords
{
    protected static string $resource = HealthConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

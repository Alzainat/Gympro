<?php

namespace App\Filament\Trainer\Resources\MealResource\Pages;

use App\Filament\Trainer\Resources\MealResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeal extends EditRecord
{
    protected static string $resource = MealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

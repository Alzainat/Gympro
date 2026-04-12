<?php

namespace App\Filament\Admin\Resources\MealResource\Pages;

use App\Filament\Admin\Resources\MealResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMeals extends ListRecords
{
    protected static string $resource = MealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create New Meal'),
        ];
    }
}

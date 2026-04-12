<?php

namespace App\Filament\Admin\Resources\MealResource\Pages;

use App\Filament\Admin\Resources\MealResource;
use Filament\Resources\Pages\CreateRecord;
use App\Helpers\AdminLogger;

class CreateMeal extends CreateRecord
{
    protected static string $resource = MealResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->user()?->profile?->id;

        if (isset($data['ingredients']) && is_array($data['ingredients'])) {
            $data['ingredients'] = array_map(
                fn ($item) => $item['item'] ?? $item,
                $data['ingredients']
            );
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        AdminLogger::log(
            action: 'create_meal',
            targetType: 'Meal',
            targetId: $this->record->id,
            newValues: $this->record->toArray(),
        );
    }
}

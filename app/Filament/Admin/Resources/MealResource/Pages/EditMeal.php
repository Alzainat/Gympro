<?php

namespace App\Filament\Admin\Resources\MealResource\Pages;

use App\Filament\Admin\Resources\MealResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Helpers\AdminLogger;

class EditMeal extends EditRecord
{
    protected static string $resource = MealResource::class;

    protected array $oldData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['ingredients']) && is_array($data['ingredients'])) {
            $data['ingredients'] = array_map(function ($item) {
                return ['item' => $item];
            }, $data['ingredients']);
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->oldData = $this->record->toArray();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['ingredients']) && is_array($data['ingredients'])) {
            $data['ingredients'] = array_map(
                fn ($item) => $item['item'] ?? $item,
                $data['ingredients']
            );
        }

        return $data;
    }

    protected function afterSave(): void
    {
        AdminLogger::log(
            action: 'update_meal',
            targetType: 'Meal',
            targetId: $this->record->id,
            oldValues: $this->oldData,
            newValues: $this->record->fresh()->toArray(),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    AdminLogger::log(
                        action: 'delete_meal',
                        targetType: 'Meal',
                        targetId: $this->record->id,
                        oldValues: $this->record->toArray(),
                    );
                }),
        ];
    }
}

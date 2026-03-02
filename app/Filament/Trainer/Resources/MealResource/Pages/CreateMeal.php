<?php

namespace App\Filament\Trainer\Resources\MealResource\Pages;

use App\Filament\Trainer\Resources\MealResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMeal extends CreateRecord
{
    protected static string $resource = MealResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['trainer_id'] = auth()->user()->profile->id;
        $profileId = auth()->user()?->profile?->id;

        if (!$profileId) {
        abort(403, 'No profile found for this user.');
    }

        $data['created_by'] = $profileId;       // ✅ هذا اللي ناقص
        $data['trainer_id'] = $profileId;       // ✅ إذا بدك trainer_id نفس الشخص

        return $data;
    }
}

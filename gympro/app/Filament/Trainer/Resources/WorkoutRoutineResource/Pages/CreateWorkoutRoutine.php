<?php

namespace App\Filament\Trainer\Resources\WorkoutRoutineResource\Pages;

use App\Filament\Trainer\Resources\WorkoutRoutineResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkoutRoutine extends CreateRecord
{
    protected static string $resource = WorkoutRoutineResource::class;

    /**
     * ربط الـ routine بالمدرب تلقائيًا
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = auth()->user()->profile->id;

        return $data;
    }

    /**
     * Redirect بعد الإنشاء
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
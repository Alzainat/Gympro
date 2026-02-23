<?php

namespace App\Filament\Trainer\Resources\WorkoutRoutineResource\Pages;

use App\Filament\Trainer\Resources\WorkoutRoutineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkoutRoutine extends EditRecord
{
    protected static string $resource = WorkoutRoutineResource::class;

    /**
     * تأكيد أن الـ routine يبقى مربوط بالمدرب
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['creator_id'] = auth()->user()->profile->id;

        return $data;
    }

    /**
     * Actions في الهيدر
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Redirect بعد التعديل
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
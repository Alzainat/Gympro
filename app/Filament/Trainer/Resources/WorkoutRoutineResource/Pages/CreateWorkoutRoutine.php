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
     * بعد الإنشاء: نحفظ تمارين الأيام
     */
    protected function afterCreate(): void
    {
        $this->syncExercisesByDay();
    }

    private function syncExercisesByDay(): void
    {
        $state = $this->form->getState();
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

        $this->record->routineExercises()->delete();

        foreach ($days as $day) {
            foreach (($state["routineExercises_{$day}"] ?? []) as $row) {
                $this->record->routineExercises()->create([
                    'exercise_id'   => $row['exercise_id'],
                    'day_of_week'   => $day,
                    'sets'          => $row['sets'] ?? 3,
                    'reps'          => $row['reps'] ?? 12,
                    'rest_seconds'  => $row['rest_seconds'] ?? 60,
                    'order_index'   => $row['order_index'] ?? null,
                    'notes'         => $row['notes'] ?? null,
                ]);
            }
        }
    }

    /**
     * Redirect بعد الإنشاء
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

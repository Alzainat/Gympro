<?php

namespace App\Filament\Admin\Resources\WorkoutRoutineResource\Pages;

use App\Filament\Admin\Resources\WorkoutRoutineResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkoutRoutine extends CreateRecord
{
    protected static string $resource = WorkoutRoutineResource::class;

    protected function afterCreate(): void
    {
        $this->syncExercises();
    }

    private function syncExercises(): void
    {
        $state = $this->form->getState();

        $this->record->routineExercises()->delete();

        foreach (($state['routineExercises'] ?? []) as $index => $row) {
            if (empty($row['exercise_id'])) {
                continue;
            }

            $this->record->routineExercises()->create([
                'exercise_id'   => $row['exercise_id'],
                'day_of_week'   => $row['day_of_week'] ?? 'Sunday',
                'sets'          => $row['sets'] ?? 3,
                'reps'          => $row['reps'] ?? 12,
                'rest_seconds'  => $row['rest_seconds'] ?? 60,
                'order_index'   => $row['order_index'] ?? ($index + 1),
                'notes'         => $row['notes'] ?? null,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

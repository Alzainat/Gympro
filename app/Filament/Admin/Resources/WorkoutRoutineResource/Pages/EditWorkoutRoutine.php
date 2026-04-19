<?php

namespace App\Filament\Admin\Resources\WorkoutRoutineResource\Pages;

use App\Filament\Admin\Resources\WorkoutRoutineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkoutRoutine extends EditRecord
{
    protected static string $resource = WorkoutRoutineResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['selected_day'] = 'Sunday';
        $data['selected_muscle'] = 'chest';

        $data['routineExercises'] = $this->record->routineExercises()
            ->with('exercise')
            ->orderByRaw("
                CASE day_of_week
                    WHEN 'Sunday' THEN 1
                    WHEN 'Monday' THEN 2
                    WHEN 'Tuesday' THEN 3
                    WHEN 'Wednesday' THEN 4
                    WHEN 'Thursday' THEN 5
                    WHEN 'Friday' THEN 6
                    WHEN 'Saturday' THEN 7
                    ELSE 8
                END
            ")
            ->orderBy('order_index')
            ->get()
            ->map(function ($re) {
                return [
                    'exercise_id'   => $re->exercise_id,
                    'day_of_week'   => $re->day_of_week,
                    'muscle_group'  => $re->exercise?->target_muscle,
                    'day_label'     => $re->day_of_week,
                    'muscle_label'  => $re->exercise?->target_muscle,
                    'sets'          => $re->sets,
                    'reps'          => $re->reps,
                    'rest_seconds'  => $re->rest_seconds,
                    'order_index'   => $re->order_index,
                    'notes'         => $re->notes,
                ];
            })
            ->toArray();

        return $data;
    }

    protected function afterSave(): void
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

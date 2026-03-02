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
     * تعبئة التمارين حسب اليوم عند فتح صفحة التعديل
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

        foreach ($days as $day) {
            $data["routineExercises_{$day}"] = $this->record->routineExercises()
                ->where('day_of_week', $day)
                ->orderBy('order_index')
                ->get()
                ->map(fn ($re) => [
                    'exercise_id'  => $re->exercise_id,
                    'sets'         => $re->sets,
                    'reps'         => $re->reps,
                    'rest_seconds' => $re->rest_seconds,
                    'order_index'  => $re->order_index,
                    'notes'        => $re->notes,
                ])
                ->toArray();
        }

        return $data;
    }

    /**
     * بعد الحفظ: إعادة بناء جدول routine_exercises حسب الأيام
     */
    protected function afterSave(): void
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberExerciseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'routine_id',
        'routine_exercise_id',
        'exercise_id',
        'workout_date',
        'day_of_week',
        'weight',
        'sets_done',
        'reps_done',
        'note',
    ];

    protected $casts = [
        'workout_date' => 'date',
        'weight' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Profile::class, 'member_id');
    }

    public function routine()
    {
        return $this->belongsTo(WorkoutRoutine::class, 'routine_id');
    }

    public function routineExercise()
    {
        return $this->belongsTo(RoutineExercise::class, 'routine_exercise_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }
}

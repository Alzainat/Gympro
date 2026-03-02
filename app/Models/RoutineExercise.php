<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutineExercise extends Model
{
    use HasFactory;

    protected $table = 'routine_exercises';

    public $timestamps = false;

    protected $fillable = [
        'routine_id',
        'exercise_id',
        'day_of_week',
        'sets',
        'reps',
        'rest_seconds',
        'order_index',
        'notes',
    ];

    /**
     * 🔗 الروتين
     */
    public function routine()
    {
        return $this->belongsTo(WorkoutRoutine::class, 'routine_id');
    }

    /**
     * 🔗 التمرين
     */
    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }
}

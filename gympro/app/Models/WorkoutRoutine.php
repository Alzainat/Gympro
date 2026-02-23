<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutRoutine extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $table = 'workout_routines';

    protected $fillable = [
        'creator_id',
        'name',
        'description',
        'difficulty_level',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * 🧑‍🏫 المدرب صاحب الروتين
     */
    public function creator()
    {
        return $this->belongsTo(Profile::class, 'creator_id');
    }

    /**
     * 🏋️‍♂️ التمارين داخل الروتين
     */
    public function routineExercises()
    {
        return $this->hasMany(RoutineExercise::class, 'routine_id')
            ->orderBy('order_index');
    }

    /**
     * 👥 الأعضاء المربوطين بهذا الروتين
     */
    public function memberRoutines()
    {
        return $this->hasMany(MemberRoutine::class, 'routine_id');
    }
}
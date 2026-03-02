<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name','description','target_muscle','equipment','difficulty','video_url','image_url'
    ];

    public function routineExercises()
    {
        return $this->hasMany(RoutineExercise::class, 'exercise_id');
    }
}
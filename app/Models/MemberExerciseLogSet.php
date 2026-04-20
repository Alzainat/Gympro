<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberExerciseLogSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_exercise_log_id',
        'round',
        'weight',
        'reps',
        'done',
    ];

    protected $casts = [
        'round' => 'integer',
        'weight' => 'decimal:2',
        'reps' => 'integer',
        'done' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function log()
    {
        return $this->belongsTo(MemberExerciseLog::class, 'member_exercise_log_id');
    }
}

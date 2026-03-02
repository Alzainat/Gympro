<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberRoutine extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['member_id','routine_id','assigned_by','start_date','status'];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Profile::class, 'member_id');
    }

    public function routine()
    {
        return $this->belongsTo(WorkoutRoutine::class, 'routine_id');
    }

    public function trainer()
    {
        return $this->belongsTo(Profile::class, 'assigned_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'trainer_id','class_name','description','start_time','end_time','capacity','is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function trainer()
    {
        return $this->belongsTo(Profile::class, 'trainer_id');
    }

    public function bookings()
    {
        return $this->hasMany(ClassBooking::class, 'schedule_id');
    }
}
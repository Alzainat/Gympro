<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassBooking extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['schedule_id','member_id','booking_time','status'];

    protected $casts = [
        'booking_time' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }

    public function member()
    {
        return $this->belongsTo(Profile::class, 'member_id');
    }
}
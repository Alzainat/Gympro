<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionBooking extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['session_id','member_id','status','booked_at'];

    protected $casts = [
        'booked_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'session_id');
    }

    public function member()
    {
        return $this->belongsTo(Profile::class, 'member_id');
    }
}
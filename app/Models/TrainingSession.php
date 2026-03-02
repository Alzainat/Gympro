<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'trainer_id','title','description','session_date','duration_minutes','max_participants','is_active'
    ];

    protected $casts = [
        'session_date' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function trainer()
    {
        return $this->belongsTo(Profile::class, 'trainer_id');
    }

    public function bookings()
    {
        return $this->hasMany(SessionBooking::class, 'session_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','full_name','role','avatar_url','bio','trainer_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Self reference: trainer_id -> profiles.id
    public function trainer()
    {
        return $this->belongsTo(Profile::class, 'trainer_id');
    }

    public function trainees()
    {
        return $this->hasMany(Profile::class, 'trainer_id');
    }

    // One-to-one extensions
    public function adminProfile()
    {
        return $this->hasOne(AdminProfile::class, 'profile_id');
    }

    public function trainerProfile()
    {
        return $this->hasOne(TrainerProfile::class, 'profile_id');
    }

    public function memberProfile()
    {
        return $this->hasOne(MemberProfile::class, 'profile_id');
    }

    // Health
    public function healthProfile()
    {
        return $this->hasOne(HealthProfile::class, 'user_id');
    }

    public function healthConditions()
    {
        return $this->hasMany(HealthCondition::class, 'user_id');
    }

    // Workouts & nutrition
    public function createdRoutines()
    {
        return $this->hasMany(WorkoutRoutine::class, 'creator_id');
    }

    public function memberRoutines()
    {
        return $this->hasMany(MemberRoutine::class, 'member_id');
    }

    public function assignedRoutines()
    {
        return $this->hasMany(MemberRoutine::class, 'assigned_by');
    }

    public function dietPlans()
    {
        return $this->hasMany(DietPlan::class, 'member_id');
    }

    // Scheduling
    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class, 'trainer_id');
    }

    public function classBookings()
    {
        return $this->hasMany(ClassBooking::class, 'member_id');
    }

    public function trainingSessions()
    {
        return $this->hasMany(TrainingSession::class, 'trainer_id');
    }

    public function sessionBookings()
    {
        return $this->hasMany(SessionBooking::class, 'member_id');
    }



    // Payments
    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function processedPayments()
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }

    // Communication
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    
}

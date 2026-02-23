<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberMeal extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'meal_id',
        'assigned_by',
        'meal_time',
        'start_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'is_active'  => 'boolean',
    ];

    public function member()
    {
        return $this->belongsTo(Profile::class, 'member_id');
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Profile::class, 'assigned_by');
    }
}
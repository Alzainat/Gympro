<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainerProfile extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'profile_id','specializations','certification_url','rating',
        'review_count','hourly_rate','is_available','work_schedule'
    ];

    protected $casts = [
        'specializations' => 'array',
        'work_schedule' => 'array',
        'is_available' => 'boolean',
        'rating' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
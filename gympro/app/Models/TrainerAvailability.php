<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainerAvailability extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'trainer_availability';

    protected $fillable = ['trainer_id','day_of_week','start_time','end_time','is_available'];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function trainer()
    {
        return $this->belongsTo(Profile::class, 'trainer_id');
    }
}
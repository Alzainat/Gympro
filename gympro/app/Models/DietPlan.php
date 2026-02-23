<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DietPlan extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'member_id','assigned_by','title','description',
        'target_calories','target_protein','target_carbs','target_fats',
        'start_date','end_date','is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Profile::class, 'member_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(Profile::class, 'assigned_by');
    }
}
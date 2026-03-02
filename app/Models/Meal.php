<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'name',
        'description',
        'calories',
        'protein',
        'carbs',
        'fats',
        'ingredients',
        'image_url',
        'category',
        'created_by',
    ];

    protected $casts = [
        'ingredients' => 'array',
        'protein'     => 'decimal:2',
        'carbs'       => 'decimal:2',
        'fats'        => 'decimal:2',
    ];

    /**
     * Trainer (profile) who created the meal
     */
    public function trainer()
    {
        return $this->belongsTo(Profile::class, 'trainer_id');
    }

    /**
     * Member meal assignments
     */
    public function assignments()
    {
        return $this->hasMany(MemberMeal::class);
    }
}
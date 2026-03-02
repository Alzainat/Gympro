<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','blood_type','height','weight','body_fat_percentage','medical_notes',
    ];
    public $timestamps = true;

    protected $casts = [
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'body_fat_percentage' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function user()
    {
        return $this->belongsTo(Profile::class, 'user_id');
    }
}

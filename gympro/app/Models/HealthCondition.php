<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthCondition extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id','type','name','severity','notes','detected_at'];

    protected $casts = [
        'detected_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Profile::class, 'user_id');
    }
}
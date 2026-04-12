<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberProgressPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'image_path',
        'photo_type',
        'pose',
        'notes',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Profile::class, 'member_id');
    }
}

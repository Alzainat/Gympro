<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberProfile extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['profile_id','membership_tier','join_date','last_checkin','status'];

    protected $casts = [
        'join_date' => 'date',
        'last_checkin' => 'datetime',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
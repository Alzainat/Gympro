<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['sender_id','receiver_id','content','sent_at','is_read'];

    protected $casts = [
        'sent_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(Profile::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Profile::class, 'receiver_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id','amount','payment_type','payment_method','reference_id',
        'transaction_id','status','payment_date','notes','processed_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Profile::class, 'user_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(Profile::class, 'processed_by');
    }
}
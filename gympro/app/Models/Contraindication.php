<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contraindication extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'condition_keyword','target_type','blocked_keyword','match_type','reason','severity_level'
    ];
}
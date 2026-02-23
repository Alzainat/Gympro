<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 🔥 حل مشكلة Filament timeout
     * إجبار تحميل profile مع كل User
     */
    protected $with = ['profile'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at'        => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Profile relation
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Filament access control ONLY
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin'   => $this->profile?->role === 'admin',
            'trainer' => $this->profile?->role === 'trainer',
            default   => false,
        };
    }
}
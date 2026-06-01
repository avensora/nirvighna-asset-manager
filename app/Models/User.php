<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'password'                => 'hashed',
            'role'                    => UserRole::class,
            'is_active'               => 'boolean',
            'two_factor_required_at'  => 'datetime',
        ];
    }

    public function isMasterAdmin(): bool
    {
        return $this->role === UserRole::MasterAdmin;
    }

    /** Returns true for Master Admin and Manager (rank >= 2). */
    public function isManager(): bool
    {
        return $this->role->rank() >= UserRole::Manager->rank();
    }

    public function isTeamLead(): bool
    {
        return $this->role === UserRole::TeamLead;
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_required_at !== null
            && $this->twoFactorConfig !== null
            && $this->twoFactorConfig->isConfirmed();
    }

    public function twoFactorConfig(): HasOne
    {
        return $this->hasOne(TwoFactorConfig::class);
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'created_by');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }
}

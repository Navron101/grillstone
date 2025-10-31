<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as BaseUser; // aliased to avoid conflicts
use Illuminate\Notifications\Notifiable;

class User extends BaseUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',   // keep if the column exists
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = ['role'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->role?->hasPermission($permissionName) ?? false;
    }

    public function hasModule(string $module): bool
    {
        return $this->role?->hasModule($module) ?? false;
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin';
    }

    public function isDirector(): bool
    {
        return $this->role?->name === 'director';
    }

    public function canAccessUserAdmin(): bool
    {
        return $this->isAdmin() || $this->isDirector();
    }
}

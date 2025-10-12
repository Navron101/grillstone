<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}

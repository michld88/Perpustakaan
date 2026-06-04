<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'role_id', 'nip_nim', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin(): bool
    {
        return $this->role->name === 'admin';
    }

    public function isPustakawan(): bool
    {
        return $this->role->name === 'pustakawan';
    }

    public function isAnggota(): bool
    {
        return $this->role->name === 'anggota';
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role->name === $roleName;
    }

    public function anggota(): HasOne
    {
        return $this->hasOne(Anggota::class);
    }
}
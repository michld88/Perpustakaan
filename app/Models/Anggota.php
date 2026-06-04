<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggota extends Model
{
    use HasFactory;

    protected $table = 'anggota';

    protected $fillable = [
        'user_id', 'nim_nip', 'alamat',
        'telepon', 'foto', 'tanggal_daftar', 'status',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
    ];

    // Fix route binding - paksa pakai 'anggota' bukan 'anggotum'
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kartu(): HasOne
    {
        return $this->hasOne(KartuAnggota::class);
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class);
    }
}
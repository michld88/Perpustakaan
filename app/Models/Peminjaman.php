<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    protected $fillable = [
        'anggota_id', 'pustakawan_id',
        'tanggal_pinjam', 'tanggal_jatuh_tempo',
        'tanggal_kembali', 'status', 'catatan',
        'sudah_diperpanjang', 'tanggal_perpanjangan',
    ];

    protected $casts = [
        'tanggal_pinjam'       => 'date',
        'tanggal_jatuh_tempo'  => 'date',
        'tanggal_kembali'      => 'date',
        'tanggal_perpanjangan' => 'date',
        'sudah_diperpanjang'   => 'boolean',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function pustakawan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pustakawan_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class);
    }

    public function denda(): HasOne
    {
        return $this->hasOne(Denda::class);
    }

    public function isTerlambat(): bool
    {
        if ($this->status !== 'dipinjam') return false;
        return now()->startOfDay()->gt($this->tanggal_jatuh_tempo->startOfDay());
    }

    public function hitungDenda(): int
    {
        if (!$this->isTerlambat()) return 0;
        return now()->startOfDay()->diffInDays($this->tanggal_jatuh_tempo->startOfDay());
    }
}
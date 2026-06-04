<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservasi extends Model
{
    use HasFactory;

    protected $table = 'reservasi';

    protected $fillable = [
        'anggota_id', 'buku_id', 'status',
        'tanggal_reservasi', 'tanggal_kadaluarsa',
        'tanggal_notifikasi',
    ];

    protected $casts = [
        'tanggal_reservasi'   => 'date',
        'tanggal_kadaluarsa'  => 'date',
        'tanggal_notifikasi'  => 'date',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class);
    }
}
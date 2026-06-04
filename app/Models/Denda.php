<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Denda extends Model
{
    use HasFactory;

    protected $table = 'denda';

    protected $fillable = [
        'peminjaman_id', 'hari_terlambat',
        'tarif_per_hari', 'total_denda',
        'status', 'tanggal_bayar',
    ];

    protected $casts = [
        'tanggal_bayar'  => 'datetime',
        'total_denda'    => 'decimal:2',
        'tarif_per_hari' => 'decimal:2',
    ];

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function formatRupiah(): string
    {
        return 'Rp ' . number_format($this->total_denda, 0, ',', '.');
    }
}
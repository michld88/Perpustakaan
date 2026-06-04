<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuAnggota extends Model
{
    use HasFactory;

    protected $table = 'kartu_anggota';
    
    protected $fillable = [
        'anggota_id', 'nomor_kartu', 'qr_code_path', 'tanggal_cetak'
    ];

    protected $casts = [
        'tanggal_cetak' => 'datetime',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }
}
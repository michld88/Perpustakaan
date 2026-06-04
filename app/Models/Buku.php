<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'buku';
    protected $fillable = [
        'judul', 'isbn', 'kategori_id', 'penerbit_id',
        'tahun_terbit', 'jumlah_eksemplar', 'jumlah_tersedia',
        'deskripsi', 'cover', 'status',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function penerbit(): BelongsTo
    {
        return $this->belongsTo(Penerbit::class);
    }

    public function penulis(): BelongsToMany
    {
        return $this->belongsToMany(Penulis::class, 'buku_penulis', 'buku_id', 'penulis_id');
    }

    public function isAvailable(): bool
    {
        return $this->jumlah_tersedia > 0;
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class);
    }
}
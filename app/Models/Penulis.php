<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Penulis extends Model
{
    use HasFactory;

    protected $table = 'penulis';
    protected $fillable = ['nama', 'biografi'];

    public function buku(): BelongsToMany
    {
        return $this->belongsToMany(Buku::class, 'buku_penulis', 'penulis_id', 'buku_id');
    }
}
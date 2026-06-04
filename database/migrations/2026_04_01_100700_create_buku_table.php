<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('isbn')->unique();
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $table->foreignId('penerbit_id')->constrained('penerbit')->restrictOnDelete();
            $table->year('tahun_terbit');
            $table->integer('jumlah_eksemplar')->default(1);
            $table->integer('jumlah_tersedia')->default(1);
            $table->text('deskripsi')->nullable();
            $table->string('cover')->nullable();
            $table->enum('status', ['tersedia', 'dipinjam', 'rusak', 'hilang'])->default('tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku');
    }
};
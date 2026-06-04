<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('denda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->constrained('peminjaman')->cascadeOnDelete();
            $table->integer('hari_terlambat')->default(0);
            $table->decimal('tarif_per_hari', 10, 2)->default(1000);
            $table->decimal('total_denda', 10, 2)->default(0);
            $table->enum('status', ['belum_bayar', 'sudah_bayar'])->default('belum_bayar');
            $table->timestamp('tanggal_bayar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('denda');
    }
};
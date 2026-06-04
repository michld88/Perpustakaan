<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku_penulis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buku_id')->constrained('buku')->cascadeOnDelete();
            $table->foreignId('penulis_id')->constrained('penulis')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_penulis');
    }
};
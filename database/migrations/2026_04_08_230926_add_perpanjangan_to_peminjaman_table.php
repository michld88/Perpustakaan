<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->boolean('sudah_diperpanjang')->default(false)->after('catatan');
            $table->date('tanggal_perpanjangan')->nullable()->after('sudah_diperpanjang');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn(['sudah_diperpanjang', 'tanggal_perpanjangan']);
        });
    }
};
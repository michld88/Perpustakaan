<?php

namespace App\Console\Commands;

use App\Models\Peminjaman;
use App\Notifications\PengingatJatuhTempo;
use Illuminate\Console\Command;

class KirimPengingatPeminjaman extends Command
{
    protected $signature   = 'peminjaman:kirim-pengingat';
    protected $description = 'Kirim email pengingat jatuh tempo peminjaman';

    public function handle(): void
    {
        // Ambil peminjaman yang jatuh tempo 2 hari lagi
        $peminjaman = Peminjaman::with(['anggota.user', 'detail.buku'])
            ->where('status', 'dipinjam')
            ->whereDate('tanggal_jatuh_tempo', now()->addDays(2)->toDateString())
            ->get();

        if ($peminjaman->isEmpty()) {
            $this->info('Tidak ada peminjaman yang perlu diingatkan.');
            return;
        }

        foreach ($peminjaman as $p) {
            $user = $p->anggota->user;
            $user->notify(new PengingatJatuhTempo($p));
            $this->info("Email terkirim ke: {$user->email}");
        }

        $this->info("Total {$peminjaman->count()} email terkirim.");
    }
}
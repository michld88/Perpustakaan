<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengingatJatuhTempo extends Notification
{
    use Queueable;

    public function __construct(public Peminjaman $peminjaman)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $peminjaman = $this->peminjaman;
        $buku = $peminjaman->detail->map(fn ($d) => $d->buku->judul)->join(', ');

        return (new MailMessage)
            ->subject('Pengingat Jatuh Tempo Peminjaman Buku')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Ini adalah pengingat bahwa buku yang Anda pinjam akan segera jatuh tempo.')
            ->line('**Buku:** ' . $buku)
            ->line('**Tanggal Jatuh Tempo:** ' . $peminjaman->tanggal_jatuh_tempo)
            ->action('Lihat Detail Peminjaman', url('/peminjaman/' . $peminjaman->id))
            ->line('Harap kembalikan buku tepat waktu untuk menghindari denda keterlambatan.')
            ->salutation('Terima kasih, Sistem Informasi Perpustakaan');
    }
}
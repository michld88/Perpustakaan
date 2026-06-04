<?php

namespace App\Notifications;

use App\Models\Reservasi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservasiBukuTersedia extends Notification
{
    use Queueable;

    public function __construct(public Reservasi $reservasi)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Buku Reservasi Anda Sudah Tersedia!')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Kabar baik! Buku yang Anda reservasi sudah tersedia.')
            ->line('**Judul Buku:** ' . $this->reservasi->buku->judul)
            ->line('**Tersedia hingga:** ' . now()->addDays(2)->format('d/m/Y'))
            ->action('Ambil Sekarang', url('/reservasi'))
            ->line('Segera ambil buku Anda sebelum reservasi kadaluarsa (2 hari).')
            ->salutation('Terima kasih, Sistem Informasi Perpustakaan');
    }
}
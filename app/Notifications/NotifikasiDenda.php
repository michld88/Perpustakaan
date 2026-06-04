<?php

namespace App\Notifications;

use App\Models\Denda;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifikasiDenda extends Notification
{
    use Queueable;

    public function __construct(public Denda $denda) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Informasi Denda Keterlambatan')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Anda memiliki denda keterlambatan pengembalian buku.')
            ->line('**Hari Terlambat:** ' . $this->denda->hari_terlambat . ' hari')
            ->line('**Total Denda:** Rp ' . number_format($this->denda->total_denda, 0, ',', '.'))
            ->action('Lihat Detail', url('/denda'))
            ->line('Harap segera melunasi denda Anda.')
            ->salutation('Terima kasih, Sistem Informasi Perpustakaan');
    }
}
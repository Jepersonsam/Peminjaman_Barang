<?php

namespace App\Notifications;

use App\Models\RoomLoan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class RoomLoanStatusNotification extends Notification
{
    use Queueable;

    public $roomLoan;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(RoomLoan $roomLoan, string $message)
    {
        $this->roomLoan = $roomLoan;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $roomName = $this->roomLoan->room->name ?? 'Ruangan';
        $status = $this->roomLoan->status;
        $subject = 'Update Status Peminjaman Ruangan: ' . $roomName . ' - ' . ucfirst($status);

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->line("Halo {$notifiable->name},")
            ->line($this->message)
            ->line("Detail Peminjaman Ruangan:")
            ->line("- Ruangan: {$roomName}")
            ->line("- Waktu Mulai: " . Carbon::parse($this->roomLoan->start_time)->format('d F Y H:i'))
            ->line("- Waktu Selesai: " . Carbon::parse($this->roomLoan->end_time)->format('d F Y H:i'))
            ->line("- Keperluan: " . ($this->roomLoan->purpose ?? '-'))
            ->line("- Status: " . ucfirst($status));

        if ($this->roomLoan->emails && count($this->roomLoan->emails) > 0) {
            $mailMessage->line("- Peserta Lainnya: " . implode(', ', $this->roomLoan->emails));
        }

        return $mailMessage
            ->line('Terima kasih telah menggunakan layanan kami.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'room_loan_id' => $this->roomLoan->id,
            'status' => $this->roomLoan->status,
            'message' => $this->message,
        ];
    }
}


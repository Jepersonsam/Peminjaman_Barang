<?php

namespace App\Notifications;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class BorrowingStatusNotification extends Notification
{
    use Queueable;

    public $borrowing;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Borrowing $borrowing, string $message)
    {
        $this->borrowing = $borrowing;
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
        $itemName = $this->borrowing->item->name ?? 'Barang';
        $status = $this->borrowing->approval_status;
        $subject = 'Update Status Peminjaman: ' . $itemName . ' - ' . ucfirst($status);

        return (new MailMessage)
            ->subject($subject)
            ->line("Halo {$notifiable->name},")
            ->line($this->message)
            ->line("Detail Peminjaman:")
            ->line("- Barang: {$itemName}")
            ->line("- Tanggal Pinjam: " . Carbon::parse($this->borrowing->borrow_date)->format('d F Y'))
            ->line("- Tanggal Kembali: " . Carbon::parse($this->borrowing->return_date)->format('d F Y'))
            ->line("- Status: " . ucfirst($status))
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
            'borrowing_id' => $this->borrowing->id,
            'status' => $this->borrowing->approval_status,
            'message' => $this->message,
        ];
    }
}


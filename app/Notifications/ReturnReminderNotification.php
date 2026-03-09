<?php

namespace App\Notifications;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class ReturnReminderNotification extends Notification
{
    use Queueable;

    public $borrowing;
    public $daysRemaining;

    /**
     * Create a new notification instance.
     */
    public function __construct(Borrowing $borrowing, $daysRemaining = null)
    {
        $this->borrowing = $borrowing;
        
        // Hitung hari tersisa jika tidak diberikan
        if ($daysRemaining === null) {
            $today = Carbon::today();
            $returnDate = Carbon::parse($borrowing->return_date)->startOfDay();
            // Positif jika masih ada hari tersisa, negatif jika sudah lewat
            $this->daysRemaining = $today->diffInDays($returnDate, false);
        } else {
            $this->daysRemaining = $daysRemaining;
        }
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
        $returnDate = Carbon::parse($this->borrowing->return_date)->format('d F Y');
        
        $message = new MailMessage();
        $message->subject('Pengingat Pengembalian Barang - ' . $itemName);
        
        // Jika masih ada hari tersisa atau hari yang sama (belum terlambat)
        if ($this->daysRemaining >= 0) {
            if ($this->daysRemaining == 0) {
                // Hari yang sama dengan tanggal kembali
                $message->line("Halo {$notifiable->name},")
                        ->line("Ini adalah pengingat bahwa hari ini adalah tanggal pengembalian barang yang Anda pinjam.")
                        ->line("Detail Peminjaman:")
                        ->line("- Barang: {$itemName}")
                        ->line("- Tanggal Pinjam: " . Carbon::parse($this->borrowing->borrow_date)->format('d F Y'))
                        ->line("- Tanggal Kembali: {$returnDate} (Hari Ini)")
                        ->line("- Status: Belum Dikembalikan")
                        ->line("Mohon segera kembalikan barang hari ini ke ruang admin untuk menghindari keterlambatan.")
                        ->line('Terima kasih atas kerja samanya.');
            } else {
                // Masih ada hari tersisa
                $message->line("Halo {$notifiable->name},")
                        ->line("Ini adalah pengingat bahwa Anda memiliki peminjaman barang yang akan jatuh tempo dalam {$this->daysRemaining} hari.")
                        ->line("Detail Peminjaman:")
                        ->line("- Barang: {$itemName}")
                        ->line("- Tanggal Pinjam: " . Carbon::parse($this->borrowing->borrow_date)->format('d F Y'))
                        ->line("- Tanggal Kembali: {$returnDate} (Jatuh tempo dalam {$this->daysRemaining} hari)")
                        ->line("- Status: Belum Dikembalikan")
                        ->line("Mohon segera kembalikan barang ke ruang admin sebelum tanggal jatuh tempo.")
                        ->line('Terima kasih atas kerja samanya.');
            }
        } else {
            // Sudah melewati tanggal kembali (terlambat)
            $message->line("Halo {$notifiable->name},")
                    ->line("PERINGATAN: Barang yang Anda pinjam sudah melewati tanggal pengembalian!")
                    ->line("Detail Peminjaman:")
                    ->line("- Barang: {$itemName}")
                    ->line("- Tanggal Pinjam: " . Carbon::parse($this->borrowing->borrow_date)->format('d F Y'))
                    ->line("- Tanggal Kembali: {$returnDate}")
                    ->line("- Status: Belum Dikembalikan")
                    ->line("- Keterlambatan: Sudah lewat " . abs($this->daysRemaining) . " hari")
                    ->line("Mohon segera kembalikan barang ke ruang admin untuk menghindari sanksi lebih lanjut.")
                    ->line('Terima kasih atas perhatiannya.');
        }
        
        return $message;
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
            'item_name' => $this->borrowing->item->name ?? 'Barang',
            'return_date' => $this->borrowing->return_date,
            'days_remaining' => $this->daysRemaining,
        ];
    }
}

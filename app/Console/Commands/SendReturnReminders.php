<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Borrowing;
use App\Notifications\ReturnReminderNotification;
use Illuminate\Support\Facades\Log;

class SendReturnReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-return-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan pengingat pengembalian barang...');

        // Kita akan cek untuk 3 kondisi:
        // 1. Jatuh tempo dalam 2 hari (H-2)
        // 2. Jatuh tempo besok (H-1)
        // 3. Jatuh tempo hari ini (Hari H)
        
        $today = Carbon::today();
        $targetDates = [
            '2 hari' => $today->copy()->addDays(2),
            '1 hari' => $today->copy()->addDays(1),
            'hari ini' => $today->copy()
        ];

        $totalSent = 0;

        foreach ($targetDates as $label => $date) {
            // Cari data peminjaman yang tanggal pengembaliannya sama dengan $date
            // Statusnya harus 'approved' dan barangnya 'belum dikembalikan'
            $borrowings = Borrowing::with(['user', 'item'])
                ->where('is_returned', false)
                ->where(function ($query) {
                    $query->where('approval_status', Borrowing::STATUS_APPROVED)
                          ->orWhereNull('approval_status');
                })
                ->whereDate('return_date', $date)
                ->get();

            $this->info("Ditemukan {$borrowings->count()} peminjaman yang jatuh tempo {$label} (" . $date->format('Y-m-d') . ").");

            foreach ($borrowings as $borrowing) {
                if ($borrowing->user && $borrowing->user->email) {
                    try {
                        // Hitung hari tersisa (supaya template email tahu ini H-sekian atau H-0)
                        $returnDate = Carbon::parse($borrowing->return_date)->startOfDay();
                        $daysRemaining = $today->diffInDays($returnDate, false);

                        // Kirim notifikasi
                        $borrowing->user->notify(new ReturnReminderNotification($borrowing, $daysRemaining));
                        
                        $this->info("- Mengirim pengingat ke {$borrowing->user->email} untuk barang {$borrowing->item->name}");
                        $totalSent++;
                    } catch (\Exception $e) {
                        Log::error("Gagal mengirim notifikasi pengingat ke {$borrowing->user->email}: " . $e->getMessage());
                        $this->error("- Gagal mengirim ke {$borrowing->user->email}: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Selesai. Total {$totalSent} email pengingat berhasil dikirim.");
    }
}

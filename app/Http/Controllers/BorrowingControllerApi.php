<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Http\Requests\StoreBorrowingRequest;
use App\Http\Requests\UpdateBorrowingRequest;
use App\Http\Resources\BorrowingResource;
use App\Notifications\ReturnReminderNotification;
use App\Notifications\BorrowingStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;




class BorrowingControllerApi extends Controller
{
    public function index(): JsonResponse
    {
        $borrowings = Borrowing::with(['user', 'item'])->latest()->get();
        return response()->json([
            'data' => BorrowingResource::collection($borrowings)
        ]);
    }

    public function PublicIndex(): JsonResponse
    {
        $borrowings = Borrowing::with(['item'])->latest()->get();
        return response()->json([
            'data' => BorrowingResource::collection($borrowings)
        ]);
    }


    public function publicStore(Request $request)
    {
        $request->validate([
            'user_code' => 'required|exists:users,code',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:items,id',
            'borrow_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:borrow_date',
        ]);

        $user = \App\Models\User::where('code', $request->user_code)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        $borrowed = [];
        $skipped = [];

        foreach ($request->item_ids as $itemId) {
            $item = \App\Models\Item::find($itemId);

            if (!$item || !$item->is_active) {
                $skipped[] = [
                    'item_id' => $itemId,
                    'message' => 'Barang tidak ditemukan atau tidak aktif.'
                ];
                continue;
            }

            // Jika item perlu approval
            if ($item->is_approval) {
                $borrowing = \App\Models\Borrowing::create([
                    'users_id' => $user->id,
                    'item_id' => $item->id,
                    'borrow_date' => $request->borrow_date,
                    'return_date' => $request->return_date,
                    'is_returned' => false,
                    'approval_status' => 'pending', // 👈 status menunggu persetujuan
                ]);

                $skipped[] = [
                    'item_id' => $itemId,
                    'message' => 'Barang memerlukan persetujuan admin.'
                ];

                $borrowed[] = $borrowing; // tetap masukkan ke "borrowed" agar front-end bisa menampilkan permintaan yang berhasil dicatat
                continue;
            }

            // Jika tidak perlu approval, tapi tidak tersedia
            if (!$item->is_available) {
                $skipped[] = [
                    'item_id' => $itemId,
                    'message' => 'Barang tidak tersedia.'
                ];
                continue;
            }

            // Barang bisa langsung dipinjam
            $borrowing = \App\Models\Borrowing::create([
                'users_id' => $user->id,
                'item_id' => $item->id,
                'borrow_date' => $request->borrow_date,
                'return_date' => $request->return_date,
                'is_returned' => false,
                'approval_status' => 'approved', // 👈 langsung disetujui
            ]);

            $item->update(['is_available' => false]);

            $borrowed[] = $borrowing;
        }


        return response()->json([
            'message' => count($borrowed)
                ? 'Peminjaman berhasil dicatat.'
                : 'Tidak ada barang yang berhasil dipinjam.',
            'borrowed_count' => count($borrowed),
            'skipped_items' => $skipped,
            'data' => $borrowed,
        ]);
    }




    public function store(StoreBorrowingRequest $request): JsonResponse
    {
        $item = Item::findOrFail($request->item_id);

        if (!$item->is_available) {
            return response()->json([
                'message' => 'Item is not available for borrowing.'
            ], 400);
        }

        if ($item->is_approval) {
            return response()->json([
                'message' => 'Item Memerlukan Persetujuan Admin Sebelum Dapat Dipinjam.'
            ], 403);
        }

        // Tandai item sebagai tidak tersedia
        $item->is_available = false;
        $item->save();

        $borrowing = Borrowing::create($request->validated());

        return response()->json(new BorrowingResource($borrowing), 201);
    }

    public function show($id): JsonResponse
    {
        $borrowing = Borrowing::with(['user', 'item'])->findOrFail($id);
        return response()->json(new BorrowingResource($borrowing));
    }


    public function update(UpdateBorrowingRequest $request, $id): JsonResponse
    {
        $borrowing = Borrowing::with(['item', 'user'])->findOrFail($id);

        $originalApprovalStatus = $borrowing->approval_status;
        $originalIsReturned = $borrowing->is_returned;

        $borrowing->update($request->validated());

        // === 1. Jika status menjadi APPROVED ===
        if (
            $originalApprovalStatus !== Borrowing::STATUS_APPROVED &&
            $borrowing->approval_status === Borrowing::STATUS_APPROVED
        ) {
            // Tandai barang tidak tersedia
            if ($borrowing->item && $borrowing->item->is_available) {
                $borrowing->item->is_available = false;
                $borrowing->item->save();
            }

            $this->sendEmailNotification($borrowing, 'Peminjaman barang Anda telah disetujui.');
        }

        // === 2. Jika status menjadi REJECTED ===
        if (
            $originalApprovalStatus !== Borrowing::STATUS_REJECTED &&
            $borrowing->approval_status === Borrowing::STATUS_REJECTED
        ) {
            $this->sendEmailNotification($borrowing, 'Mohon maaf, permintaan peminjaman barang Anda telah ditolak.');
        }

        // === 3. Jika dikembalikan ===
        if (!$originalIsReturned && $borrowing->is_returned) {
            if ($borrowing->item && !$borrowing->item->is_available) {
                $borrowing->item->is_available = true;
                $borrowing->item->save();
            }
        }

        $borrowing->load(['user', 'item']);

        return response()->json(new BorrowingResource($borrowing));
    }

    private function sendEmailNotification(Borrowing $borrowing, string $message): void
    {
        try {
            if ($borrowing->user && $borrowing->user->email) {
                $borrowing->user->notify(new BorrowingStatusNotification($borrowing, $message));
            }
        } catch (\Exception $e) {
            Log::error("Gagal mengirim email notifikasi: " . $e->getMessage());
        }
    }



    public function destroy($id): JsonResponse
    {
        $borrowing = Borrowing::findOrFail($id);
        $borrowing->delete();
        return response()->json(['message' => 'Borrowing deleted successfully.']);
    }

    public function returnItem(Request $request)
    {
        $request->validate([
            'serial_code' => 'required|string',
        ]);

        $item = Item::where('serial_code', $request->serial_code)->first();

        if (!$item) {
            return response()->json(['message' => 'Item tidak ditemukan.'], 404);
        }

        if ($item->is_available) {
            return response()->json(['message' => 'Barang ini sudah tersedia.'], 400);
        }

        $borrowing = Borrowing::where('item_id', $item->id)
            ->where('is_returned', false)
            ->latest()
            ->first();

        if (!$borrowing) {
            return response()->json(['message' => 'Peminjaman tidak ditemukan atau sudah dikembalikan.'], 404);
        }

        // Optional: pastikan approval sudah diberikan sebelum dikembalikan
        if ($borrowing->approval_status !== Borrowing::STATUS_APPROVED) {
            return response()->json(['message' => 'Barang belum disetujui, tidak dapat dikembalikan.'], 400);
        }

        // Lakukan pengembalian
        $borrowing->is_returned = true;
        $borrowing->save();

        $item->is_available = true;
        $item->save();

        return response()->json([
            'message' => 'Barang berhasil dikembalikan.',
            'data' => [
                'item' => [
                    'name' => $item->name
                ]
            ]
        ]);
    }


    public function approve($id): JsonResponse
    {
        $borrowing = Borrowing::with(['item', 'user'])->findOrFail($id);

        if ($borrowing->approval_status !== 'pending') {
            return response()->json(['message' => 'Peminjaman ini sudah diproses.'], 400);
        }

        $borrowing->approval_status = 'approved';
        $borrowing->save();

        if ($borrowing->item) {
            $borrowing->item->is_available = false;
            $borrowing->item->save();
        }

        // Kirim email notifikasi
        $this->sendEmailNotification($borrowing, "Peminjaman barang Anda telah disetujui.");
 
        
        return response()->json([
            'message' => 'Peminjaman disetujui.',
            'data' => new BorrowingResource($borrowing)
        ]);
    }


    public function reject($id): JsonResponse
    {
        $borrowing = Borrowing::findOrFail($id);

        if ($borrowing->approval_status !== 'pending') {
            return response()->json(['message' => 'Peminjaman ini sudah diproses.'], 400);
        }

        $borrowing->approval_status = 'rejected';
        $borrowing->save();

        // Kirim email notifikasi
        $this->sendEmailNotification($borrowing, "Mohon maaf, permintaan peminjaman barang Anda telah ditolak.");

        return response()->json([
            'message' => 'Peminjaman ditolak.',
            'data' => new BorrowingResource($borrowing)
        ]);
    }

    public function userBorrowings($userId): JsonResponse
    {
        $borrowings = Borrowing::with(['user', 'item'])
            ->where('users_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => BorrowingResource::collection($borrowings)
        ]);
    }

    /**
     * Kirim notifikasi pengingat pengembalian untuk satu borrowing
     */
    public function sendReturnReminder($id): JsonResponse
    {
        $borrowing = Borrowing::with(['user', 'item'])->findOrFail($id);

        // Validasi
        if ($borrowing->is_returned) {
            return response()->json([
                'message' => 'Barang sudah dikembalikan, tidak perlu dikirim pengingat.'
            ], 400);
        }

        if ($borrowing->approval_status !== Borrowing::STATUS_APPROVED && $borrowing->approval_status !== null) {
            return response()->json([
                'message' => 'Peminjaman belum disetujui, tidak dapat dikirim pengingat.'
            ], 400);
        }

        if (!$borrowing->user || !$borrowing->user->email) {
            return response()->json([
                'message' => 'User atau email tidak ditemukan.'
            ], 404);
        }

        try {
            // Hitung hari tersisa (hari ini ke tanggal kembali)
            // Positif jika masih ada hari tersisa, negatif jika sudah lewat
            $today = Carbon::today();
            $returnDate = Carbon::parse($borrowing->return_date)->startOfDay();
            $daysRemaining = $today->diffInDays($returnDate, false);
            
            // Kirim notifikasi
            $borrowing->user->notify(new ReturnReminderNotification($borrowing, $daysRemaining));

            return response()->json([
                'message' => 'Notifikasi pengingat berhasil dikirim.',
                'data' => [
                    'borrowing_id' => $borrowing->id,
                    'user_email' => $borrowing->user->email,
                    'item_name' => $borrowing->item->name,
                    'return_date' => $borrowing->return_date,
                    'days_remaining' => $daysRemaining,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi pengingat: " . $e->getMessage());
            return response()->json([
                'message' => 'Gagal mengirim notifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim notifikasi pengingat untuk semua borrowing yang perlu diingatkan
     */
    public function sendBulkReturnReminders(Request $request): JsonResponse
    {
        $request->validate([
            'days_before' => 'nullable|integer|min:0|max:30',
            'include_overdue' => 'nullable|boolean',
        ]);

        $daysBefore = $request->input('days_before', 3);
        $includeOverdue = $request->input('include_overdue', true);
        
        $today = Carbon::today();
        $targetDate = $today->copy()->addDays($daysBefore);
        
        // Query untuk borrowing yang perlu dikirim pengingat
        $query = Borrowing::with(['user', 'item'])
            ->where('is_returned', false)
            ->where(function ($q) {
                $q->where('approval_status', Borrowing::STATUS_APPROVED)
                  ->orWhereNull('approval_status');
            })
            ->whereDate('return_date', '<=', $targetDate);
        
        // Jika tidak include overdue, hanya yang belum lewat
        if (!$includeOverdue) {
            $query->whereDate('return_date', '>=', $today);
        }
        
        $borrowings = $query->get();
        
        if ($borrowings->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada peminjaman yang perlu dikirim pengingat.',
                'sent_count' => 0,
                'failed_count' => 0,
            ]);
        }
        
        $sentCount = 0;
        $failedCount = 0;
        $failedItems = [];
        
        foreach ($borrowings as $borrowing) {
            try {
                // Pastikan user dan email ada
                if (!$borrowing->user || !$borrowing->user->email) {
                    $failedCount++;
                    $failedItems[] = [
                        'borrowing_id' => $borrowing->id,
                        'reason' => 'User atau email tidak ditemukan'
                    ];
                    continue;
                }
                
                // Hitung hari tersisa (hari ini ke tanggal kembali)
                // Positif jika masih ada hari tersisa, negatif jika sudah lewat
                $returnDate = Carbon::parse($borrowing->return_date)->startOfDay();
                $daysRemaining = $today->diffInDays($returnDate, false);
                
                // Kirim notifikasi
                $borrowing->user->notify(new ReturnReminderNotification($borrowing, $daysRemaining));
                
                $sentCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $failedItems[] = [
                    'borrowing_id' => $borrowing->id,
                    'reason' => $e->getMessage()
                ];
                Log::error("Gagal mengirim notifikasi untuk borrowing ID {$borrowing->id}: " . $e->getMessage());
            }
        }
        
        return response()->json([
            'message' => "Notifikasi pengingat berhasil dikirim untuk {$sentCount} peminjaman.",
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'failed_items' => $failedItems,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Http\Requests\StoreBorrowingRequest;
use App\Http\Requests\UpdateBorrowingRequest;
use App\Http\Resources\BorrowingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;




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

            $this->sendWebhookNotification($borrowing, 'Peminjaman barang Anda telah disetujui.');
        }

        // === 2. Jika status menjadi REJECTED ===
        if (
            $originalApprovalStatus !== Borrowing::STATUS_REJECTED &&
            $borrowing->approval_status === Borrowing::STATUS_REJECTED
        ) {
            $this->sendWebhookNotification($borrowing, 'Mohon maaf, permintaan peminjaman barang Anda telah ditolak.');
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

    private function sendWebhookNotification(Borrowing $borrowing, string $message): void
    {
        try {
            Http::post('https://workflow.tiketux.id/webhook-test/3b6a443c-df0b-4dec-bad1-260f0cc389a7', [
                'user_email'   => $borrowing->user->email,
                'user_name'    => $borrowing->user->name,
                'item_name'    => $borrowing->item->name,
                'borrow_date'  => $borrowing->borrow_date,
                'return_date'  => $borrowing->return_date,
                'message'      => $message,
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal mengirim webhook ke n8n: " . $e->getMessage());
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

        // Kirim data ke n8n dengan Laravel Http Client
        try {
            Http::post('https://workflow.tiketux.id/webhook-test/3b6a443c-df0b-4dec-bad1-260f0cc389a7', [
                'user_email'   => $borrowing->user->email,
                'user_name'    => $borrowing->user->name,
                'item_name'    => $borrowing->item->name,
                'borrow_date'  => $borrowing->borrow_date,
                'return_date'  => $borrowing->return_date,
                'message'      => "Peminjaman barang Anda telah disetujui."
            ]);
        } catch (\Exception $e) {
            // Jangan hentikan proses jika gagal kirim ke n8n
        }

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
}

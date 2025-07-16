<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\Borrowing;
use App\Http\Resources\BorrowingResource;

class PublicBorrowingControllerApi extends Controller
{
    public function show($code)
    {
        $user = User::where('code', $code)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        $borrowings = $user->borrowings()->with('item')->latest()->get();

        $availableItems = Item::where('is_available', true)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'user' => $user,
            'borrowings' => $borrowings,
            'available_items' => $availableItems,
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
}

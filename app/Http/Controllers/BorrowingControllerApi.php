<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Http\Requests\StoreBorrowingRequest;
use App\Http\Requests\UpdateBorrowingRequest;
use App\Http\Resources\BorrowingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Item;

class BorrowingControllerApi extends Controller
{
    public function index(): JsonResponse
    {
        $borrowings = Borrowing::with(['user', 'item'])->latest()->get();
        return response()->json([
            'data' => BorrowingResource::collection($borrowings)
        ]);
    }

    public function publicStore(Request $request)
    {
        $request->validate([
            'user_code' => 'required|exists:users,code',
            'item_id' => 'required|exists:items,id',
            'borrow_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:borrow_date',
        ]);

        // Ambil user dan item berdasarkan ID
        $user = \App\Models\User::where('code', $request->user_code)->first();
        $item = \App\Models\Item::find($request->item_id);

        if (!$item->is_available) {
            return response()->json(['message' => 'Barang sedang tidak tersedia'], 400);
        }

        // Simpan peminjaman
        $borrowing = \App\Models\Borrowing::create([
            'users_id' => $user->id,
            'item_id' => $item->id,
            'borrow_date' => $request->borrow_date,
            'return_date' => $request->return_date,
            'is_returned' => false,
        ]);

        // Ubah status item menjadi tidak tersedia
        $item->is_available = 0;
        $item->save();

        return response()->json([
            'message' => 'Peminjaman berhasil dicatat',
            'data' => $borrowing
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
    $borrowing = Borrowing::with('item')->findOrFail($id);
    $borrowing->update($request->validated());

    // Jika dikembalikan, tandai item tersedia lagi
    if ($request->has('is_returned') && $request->is_returned) {
        if ($borrowing->item) {
            $borrowing->item->is_available = true;
            $borrowing->item->save();
        }
    }

    // ⬅️ Tambahkan ini agar user dan item terload untuk resource
    $borrowing->load(['user', 'item']);

    return response()->json(new BorrowingResource($borrowing));
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

        // 1. Update status item
        $item->is_available = true;
        $item->save();

        // 2. Cari peminjaman terakhir yang belum dikembalikan
        $borrowing = \App\Models\Borrowing::where('item_id', $item->id)
            ->where('is_returned', false)
            ->latest()
            ->first();

        if ($borrowing) {
            $borrowing->is_returned = true;
            $borrowing->save();
        }

        return response()->json([
            'message' => 'Barang berhasil dikembalikan.',
            'data' => $item
        ]);
    }
}

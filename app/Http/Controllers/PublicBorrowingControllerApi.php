<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Item;
use Illuminate\Http\Request;

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
}

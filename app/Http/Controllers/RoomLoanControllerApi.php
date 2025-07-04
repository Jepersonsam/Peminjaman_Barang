<?php

namespace App\Http\Controllers;

use App\Models\RoomLoan;
use Illuminate\Http\Request;

class RoomLoanControllerApi extends Controller
{
    public function index()
    {
        return response()->json(RoomLoan::with('room')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:room,id',
            'borrower_name' => 'required|string|max:100',
            'borrower_contact' => 'nullable|string|max:100',
            'purpose' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'emails' => 'nullable|string',
            'status' => 'in:pending,approved,rejected,cancelled',
        ]);
        $loan = RoomLoan::create($validated);
        return response()->json($loan, 201);
    }

    public function show($id)
    {
        return response()->json(RoomLoan::with('room')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $loan = RoomLoan::findOrFail($id);
        $loan->update($request->all());
        return response()->json($loan);
    }

    public function destroy($id)
    {
        RoomLoan::findOrFail($id)->delete();
        return response()->json([
            'message' => 'Room Loam Deleted'
        ]);
    }
}

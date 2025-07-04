<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomControllerApi extends Controller
{
    public function index()
    {
        return response()->json(Room::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'nullable|string|max:100',
            'capacity' => 'nullable|integer',
            'descriptiom' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $room = Room::create($validated);
        return response()->json($room, 201);
    }

    public function show($id)
    {
        return response()->json(Room::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $room->update($request->all());
        return response()->json($room);
    }

    public function destroy($id)
    {
        Room::findOrFail($id)->delete();
        return response()->json([
            'message' => 'Room Deleted'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Resources\RoomResource;

class RoomControllerApi extends Controller
{
    public function index()
    {
        return RoomResource::collection(Room::all());
    }

    public function store(StoreRoomRequest $request)
    {
        $room = Room::create($request->validated());
        return new RoomResource($room);
    }

    public function show($id)
    {
        $room = Room::findOrFail($id);
        return new RoomResource($room);
    }

    public function update(UpdateRoomRequest $request, $id)
    {
        $room = Room::findOrFail($id);
        $room->update($request->validated());
        return new RoomResource($room);
    }

    public function destroy($id)
    {
        Room::findOrFail($id)->delete();
        return response()->json([
            'message' => 'Room deleted successfully.'
        ]);
    }
}

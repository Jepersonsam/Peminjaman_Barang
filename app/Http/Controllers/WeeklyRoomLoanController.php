<?php

namespace App\Http\Controllers;

use App\Models\WeeklyRoomLoan;
use App\Models\RoomLoan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WeeklyRoomLoanController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => WeeklyRoomLoan::with('room')->get()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'borrower_name' => 'required|string',
            'borrower_contact' => 'required|string',
            'purpose' => 'nullable|string',
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'emails' => 'nullable|array',
        ]);

        $data['status'] = 'approved'; // Default status
        $weeklyLoan = WeeklyRoomLoan::create($data);

        // Generate jadwal ke table room_loans
        $current = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        while ($current->lte($end)) {
            if ($current->dayOfWeek == $data['day_of_week']) {
                RoomLoan::create([
                    'room_id' => $data['room_id'],
                    'borrower_name' => $data['borrower_name'],
                    'borrow_contact' => $data['borrower_contact'],
                    'purpose' => $data['purpose'],
                    'start_time' => $current->format('Y-m-d') . ' ' . $data['start_time'],
                    'end_time' => $current->format('Y-m-d') . ' ' . $data['end_time'],
                    'status' => 'approved'
                ]);
            }
            $current->addDay();
        }

        return response()->json(['message' => 'Created', 'data' => $weeklyLoan]);
    }

    public function show($id)
    {
        return WeeklyRoomLoan::with('room')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $loan = WeeklyRoomLoan::findOrFail($id);

        $data = $request->validate([
            'room_id' => 'sometimes|exists:rooms,id',
            'borrower_name' => 'sometimes|string',
            'borrower_contact' => 'sometimes|string',
            'purpose' => 'nullable|string',
            'day_of_week' => 'sometimes|integer|min:0|max:6',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'emails' => 'nullable|array',
        ]);

        $loan->update($data);
        return response()->json(['message' => 'Updated', 'data' => $loan]);
    }

    public function destroy($id)
    {
        $loan = WeeklyRoomLoan::findOrFail($id);
        $loan->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function getByRoom(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
        ]);

        $loans = WeeklyRoomLoan::where('room_id', $request->room_id)->get();

        return response()->json($loans);
    }
}

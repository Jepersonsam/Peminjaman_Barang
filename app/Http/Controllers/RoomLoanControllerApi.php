<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRoomLoanRequest;
use App\Models\RoomLoan;
use App\Notifications\RoomLoanStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RoomLoanControllerApi extends Controller
{
    public function index(Request $request)
    {
        $query = RoomLoan::with('room');

        if ($request->has('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->has('date')) {
            $query->whereDate('start_time', $request->date);
        }

        return response()->json($query->orderBy('start_time')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'borrower_name' => 'required|string|max:100',
            'borrower_contact' => 'nullable|string|max:100',
            'purpose' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'emails' => 'nullable|array',
            'emails.*' => 'email',
            'status' => 'in:pending,approved,rejected,cancelled',
        ]);

        $overlap = RoomLoan::where('room_id', $request->room_id)
            ->where('status', 'approved')
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->start_time)
                        ->where('end_time', '>', $request->start_time);
                })
                ->orWhere(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $request->end_time);
                })
                ->orWhere(function ($q) use ($request) {
                    $q->where('start_time', '>', $request->start_time)
                        ->where('end_time', '<', $request->end_time);
                })
                ->orWhere('start_time', $request->start_time)
                ->orWhere(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $request->start_time);
                });
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'message' => '⛔ Ruangan sudah dibooking pada waktu yang dipilih.'
            ], 409);
        }

        $loan = RoomLoan::create($validated);
        return response()->json($loan->fresh('room'), 201);
    }

    public function show($id)
    {
        return response()->json(RoomLoan::with('room')->findOrFail($id));
    }

    public function update(UpdateRoomLoanRequest $request, $id)
    {
        $loan = RoomLoan::findOrFail($id);

        // Cek overlap jika ada perubahan waktu
        $startTime = $request->start_time ?? $loan->start_time;
        $endTime = $request->end_time ?? $loan->end_time;

        if ($request->has('start_time') || $request->has('end_time')) {
            $overlap = RoomLoan::where('room_id', $loan->room_id)
                ->where('id', '!=', $id)
                ->where('status', 'approved')
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $startTime)
                          ->where('end_time', '>', $startTime);
                    })
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $endTime);
                    })
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '>', $startTime)
                          ->where('end_time', '<', $endTime);
                    })
                    ->orWhere('start_time', $startTime)
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                    });
                })
                ->exists();

            if ($overlap) {
                return response()->json([
                    'message' => '⛔ Ruangan sudah dibooking pada waktu yang dipilih.'
                ], 409);
            }
        }

        $loan->update($request->all());

        // === Email Notifikasi jika status berubah ke "approved" ===
        if ($request->status === 'approved') {
            $loan->load(['room', 'user']);
            if ($loan->user) {
                $loan->user->notify(new RoomLoanStatusNotification($loan, 'Peminjaman ruangan Anda telah disetujui.'));
            }
        }

        // === Email Notifikasi jika status berubah ke "rejected" ===
        if ($request->status === 'rejected') {
            $loan->load(['room', 'user']);
            if ($loan->user) {
                $loan->user->notify(new RoomLoanStatusNotification($loan, 'Mohon maaf, peminjaman ruangan Anda telah ditolak.'));
            }
        }

        return response()->json($loan->fresh('room'));
    }

    public function destroy($id)
    {
        RoomLoan::findOrFail($id)->delete();
        return response()->json(['message' => 'Room Loan Deleted']);
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'room_id' => 'required',
            'date' => 'required|date',
        ]);

        $bookings = RoomLoan::with('room')
            ->where('room_id', $request->room_id)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->orderBy('start_time')
            ->get();

        return response()->json($bookings);
    }

    public function getByUser($userId)
    {
        $borrowings = RoomLoan::with('room')->where('user_id', $userId)->get();
        return response()->json($borrowings);
    }
}

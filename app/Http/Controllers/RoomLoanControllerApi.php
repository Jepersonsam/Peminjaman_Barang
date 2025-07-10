<?php

namespace App\Http\Controllers;

use App\Models\RoomLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        // ✅ PERBAIKAN: Periksa overlap untuk semua status kecuali 'rejected' dan 'cancelled'
        $overlap = RoomLoan::where('room_id', $request->room_id)
            ->where('status', 'approved') // Exclude rejected/cancelled
            ->where(function ($query) use ($request) {
                // Kondisi 1: Start time baru berada di antara booking yang ada (tidak termasuk batas)
                $query->where(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->start_time)
                        ->where('end_time', '>', $request->start_time);
                })
                    // Kondisi 2: End time baru berada di antara booking yang ada (tidak termasuk batas)
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                            ->where('end_time', '>', $request->end_time);
                    })
                    // Kondisi 3: Booking baru mencakup seluruh booking yang ada
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '>', $request->start_time)
                            ->where('end_time', '<', $request->end_time);
                    })
                    // Kondisi 4: Waktu mulai yang sama
                    ->orWhere('start_time', $request->start_time)
                    // Kondisi 5: Booking yang ada dimulai sebelum booking baru selesai DAN selesai setelah booking baru dimulai
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

    public function update(Request $request, $id)
    {
        $loan = RoomLoan::findOrFail($id);

        // Jika mengupdate waktu, periksa overlap (kecuali dengan dirinya sendiri)
        if ($request->has('start_time') || $request->has('end_time')) {
            $startTime = $request->start_time ?? $loan->start_time;
            $endTime = $request->end_time ?? $loan->end_time;

            $overlap = RoomLoan::where('room_id', $loan->room_id)
                ->where('id', '!=', $id) // Exclude current record
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

        if ($request->status === 'approved') {
            $loan = $loan->fresh('room');

            $startIso = Carbon::parse($loan->start_time)
                ->setTimezone('Asia/Jakarta')
                ->toIso8601String();

            $endIso = Carbon::parse($loan->end_time)
                ->setTimezone('Asia/Jakarta')
                ->toIso8601String();

            $attendees = collect($loan->emails)
                ->filter()
                ->map(fn($email) => ['email' => trim($email)])
                ->values()
                ->all();


            Http::post('https://workflow.tiketux.id/webhook-test/09f03fce-c493-4ffc-927d-d5bfe7b05b91', [
                'borrower_name' => $loan->borrower_name,
                'attendees' => $attendees,
                'purpose' => $loan->purpose,
                'room_name' => $loan->room->name,
                'start_time' => $startIso,
                'end_time' => $endIso,
            ]);
        }


        return response()->json($loan->fresh('room'));
    }

    public function destroy($id)
    {
        RoomLoan::findOrFail($id)->delete();
        return response()->json([
            'message' => 'Room Loan Deleted'
        ]);
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'room_id' => 'required',
            'date' => 'required|date',
        ]);

        $roomId = $request->room_id;
        $date = $request->date;

        // ✅ PERBAIKAN: Hanya ambil booking di tanggal yang sama
        $bookings = RoomLoan::with('room')
            ->where('room_id', $roomId)
            ->whereNotIn('status', ['rejected', 'cancelled']) // Exclude rejected/cancelled
            ->orderBy('start_time')
            ->get();

        return response()->json($bookings);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // ✅ Tambahkan ini
        'room_id',
        'borrower_name',
        'borrower_contact',
        'purpose',
        'start_time',
        'end_time',
        'emails',
        'status',
        'weekly_room_loan_id',
    ];

    protected $casts = [
        'emails' => 'array', // ✅ Tambahkan ini
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function weeklyRoomLoan()
    {
        return $this->belongsTo(WeeklyRoomLoan::class);
    }
}

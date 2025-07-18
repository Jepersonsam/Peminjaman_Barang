<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyRoomLoan extends Model
{
    protected $fillable = [
        'room_id', 'borrower_name', 'borrower_contact', 'purpose',
        'day_of_week', 'start_time', 'end_time', 'start_date', 'end_date',
        'emails', 'status'
    ];

    protected $casts = [
        'emails' => 'array',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}

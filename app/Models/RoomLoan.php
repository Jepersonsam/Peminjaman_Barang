<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'borrower_name',
        'borrower_contact',
        'purpose',
        'start_time',
        'end_time',
        'emails',
        'status'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}

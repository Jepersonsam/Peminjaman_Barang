<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'users_id',
        'item_id',
        'borrow_date',
        'return_date',
        'is_returned',
        'approval_status', 
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'return_date' => 'date',
        'is_returned' => 'boolean',
        'approval_status' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

}

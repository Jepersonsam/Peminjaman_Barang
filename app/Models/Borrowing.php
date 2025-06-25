<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    protected $fillable = [
        'users_id',
        'item_id',
        'borrow_date',
        'return_date',
        'is_returned',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'return_date' => 'date',
        'is_returned' => 'boolean',
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

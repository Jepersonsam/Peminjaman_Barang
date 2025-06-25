<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    // Jika ingin mass assignment
    protected $fillable = [
        'name',
        'code',
        'serial_code',
        'is_available',
        'is_active',
    ];

    // Jika ingin casting otomatis untuk boolean
    protected $casts = [
        'is_available' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke Borrowing (One to Many)
     */
    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }
}

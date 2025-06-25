<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBorrowingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pastikan untuk membatasi ini jika ada logika autorisasi
    }

    public function rules(): array
    {
        return [
            'users_id' => 'sometimes|required|exists:users,id',
            'item_id' => 'sometimes|required|exists:items,id',
            'borrow_date' => 'sometimes|required|date',
            'return_date' => 'nullable|date',
            'is_returned' => 'sometimes|boolean',
        ];
    }
}

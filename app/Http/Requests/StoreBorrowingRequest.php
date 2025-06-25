<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBorrowingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'users_id' => ['required', 'exists:users,id'],
            'item_id' => ['required', 'exists:items,id'],
            'borrow_date' => ['required', 'date'],
            'return_date' => ['nullable', 'date', 'after_or_equal:borrow_date'],
            'is_returned' => ['boolean'],
        ];
    }
}

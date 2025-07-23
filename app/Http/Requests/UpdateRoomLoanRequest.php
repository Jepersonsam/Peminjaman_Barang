<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|exists:users,id',
            'room_id' => 'sometimes|required|exists:rooms,id',
            'borrower_name' => 'sometimes|required|string|max:100',
            'borrower_contact' => 'sometimes|nullable|string|max:100',
            'purpose' => 'nullable|string',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after:start_time',
            'emails' => 'nullable|array',
            'emails.*' => 'email',
            'status' => 'sometimes|in:pending,approved,rejected,cancelled',
        ];
    }
}

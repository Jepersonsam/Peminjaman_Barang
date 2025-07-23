<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'borrower_name' => 'required|string|max:100',
            'borrower_contact' => 'nullable|string|max:100',
            'purpose' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'emails' => 'nullable|array',
            'emails.*' => 'email',
            'status' => 'in:pending,approved,rejected,cancelled',
        ];
    }
}

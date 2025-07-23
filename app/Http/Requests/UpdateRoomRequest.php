<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:100',
            'location' => 'nullable|string|max:100',
            'capacity' => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'serial_code' => ['required', 'string', 'max:50', 'unique:items,serial_code,' . $this->route('id')],
            'code' => ['nullable', 'string', 'max:50'],
            'is_available' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
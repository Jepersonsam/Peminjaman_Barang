<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
   public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email,' . $this->route('id')],
            'code' => ['required', 'string'],
            'code_nfc' => ['nullable', 'string', 'max:50', 'unique:users,code_nfc,' . $this->route('id')],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'phone' => ['required', 'regex:/^\+62[0-9]{8,13}$/', 'unique:users,phone'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }
}

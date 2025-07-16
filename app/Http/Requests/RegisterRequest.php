<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8|confirmed',
            'code'     => 'nullable|string|max:50',
            'code_nfc' => 'nullable|string|max:50|unique:users,code_nfc', // 🟢 Tambah validasi untuk code_nfc
            'roles'     => 'sometimes|array',
            'roles.*'   => 'exists:roles,name',
        ];
    }
}

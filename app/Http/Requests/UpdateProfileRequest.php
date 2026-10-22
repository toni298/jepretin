<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()?->getAuthIdentifier()),
            ],
            'current_password' => ['required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'required_with:new_password_confirmation', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required_with:new_password', 'string'],
        ];
    }
}

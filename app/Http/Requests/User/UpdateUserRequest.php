<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name'      => ['sometimes', 'string', 'max:100'],
            'email'     => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'phone'     => ['nullable', 'string', 'max:20'],
            'role'      => ['sometimes', Rule::in([
                User::ROLE_OPERASIONAL,
                User::ROLE_STAFF,
            ])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Admin PO bisa tambah staff di PO-nya sendiri
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['required', Rule::in([
                User::ROLE_OPERASIONAL,
                User::ROLE_STAFF,
            ])],
        ];
    }
}
<?php

namespace App\Http\Requests\Po;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:100'],
            'slug'    => ['required', 'string', 'max:100', 'unique:pos,slug'],
            'code'    => ['required', 'string', 'max:20', 'unique:pos,code'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
        ];
    }
}
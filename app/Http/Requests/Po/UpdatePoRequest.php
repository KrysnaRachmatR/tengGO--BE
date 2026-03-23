<?php

namespace App\Http\Requests\Po;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        $poId = $this->route('po');

        return [
            'name'      => ['sometimes', 'string', 'max:100'],
            'slug'      => ['sometimes', 'string', 'max:100', Rule::unique('pos', 'slug')->ignore($poId)],
            'code'      => ['sometimes', 'string', 'max:20', Rule::unique('pos', 'code')->ignore($poId)],
            'phone'     => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email', 'max:100'],
            'address'   => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
<?php

namespace App\Http\Requests\Fleet;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFleetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'string', 'max:100'],
            'license_plate' => ['nullable', 'string', 'max:20'],
            'brand'         => ['nullable', 'string', 'max:50'],
            'model'         => ['nullable', 'string', 'max:50'],
            'year'          => ['nullable', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'is_active'     => ['sometimes', 'boolean'],
            'notes'         => ['nullable', 'string'],
        ];
    }
}
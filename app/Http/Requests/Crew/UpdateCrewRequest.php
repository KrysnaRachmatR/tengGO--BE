<?php

namespace App\Http\Requests\Crew;

use App\Models\Crew;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCrewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        return [
            'name'           => ['sometimes', 'string', 'max:100'],
            'nik'            => ['nullable', 'string', 'max:20'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'role'           => ['sometimes', Rule::in([
                Crew::ROLE_DRIVER,
                Crew::ROLE_CO_DRIVER,
                Crew::ROLE_CONDUCTOR,
                Crew::ROLE_GUIDE,
            ])],
            'license_number' => ['nullable', 'string', 'max:30'],
            'license_expiry' => ['nullable', 'date', 'date_format:Y-m-d'],
            'is_active'      => ['sometimes', 'boolean'],
            'notes'          => ['nullable', 'string'],
        ];
    }
}
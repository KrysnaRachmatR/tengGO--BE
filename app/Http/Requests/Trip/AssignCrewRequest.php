<?php

namespace App\Http\Requests\Trip;

use App\Models\Crew;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignCrewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo() || $this->user()->isOperasional();
    }

    public function rules(): array
    {
        return [
            'crew_id'    => ['required', 'integer', 'exists:crews,id'],
            'role'       => ['required', Rule::in([
                Crew::ROLE_DRIVER,
                Crew::ROLE_CO_DRIVER,
                Crew::ROLE_PRAMUGARARI,
                Crew::ROLE_HELPER,
            ])],
            'is_primary' => ['sometimes', 'boolean'],
            'notes'      => ['nullable', 'string', 'max:200'],
        ];
    }
}
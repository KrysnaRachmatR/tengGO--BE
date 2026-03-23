<?php

namespace App\Http\Requests\Fleet;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFleetFacilitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        return [
            'facilities'   => ['required', 'array'],
            'facilities.*' => ['string', 'max:50'],
        ];
    }
}
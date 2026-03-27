<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo() || $this->user()->isOperasional();
    }

    public function rules(): array
    {
        return [
            'fleet_id'         => ['nullable', 'integer', 'exists:fleets,id'],
            'actual_departure' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'actual_arrival'   => ['nullable', 'date_format:Y-m-d H:i:s', 'after:actual_departure'],
            'notes'            => ['nullable', 'string'],
        ];
    }
}
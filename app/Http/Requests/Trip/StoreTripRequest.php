<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo() || $this->user()->isOperasional();
    }

    public function rules(): array
    {
        return [
            'series_id'   => ['required', 'integer', 'exists:series,id'],
            'fleet_id'    => ['nullable', 'integer', 'exists:fleets,id'],
            'trip_date'   => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'notes'       => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'trip_date.after_or_equal' => 'Tidak bisa membuat trip untuk tanggal yang sudah lewat.',
        ];
    }
}
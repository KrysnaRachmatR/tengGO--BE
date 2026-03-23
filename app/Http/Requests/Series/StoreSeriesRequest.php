<?php

namespace App\Http\Requests\Series;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        $poId = $this->user()->po_id;

        return [
            'name'             => ['required', 'string', 'max:100'],
            'code'             => [
                'nullable', 'string', 'max:50',
                Rule::unique('series')->where(fn($q) => $q->where('po_id', $poId)),
            ],
            'departure_time'   => ['required', 'date_format:H:i:s'],
            'origin_city'      => ['required', 'string', 'max:100'],
            'destination_city' => ['required', 'string', 'max:100'],
            // [0=Minggu, 1=Senin, ..., 6=Sabtu] — null berarti setiap hari
            'operating_days'   => ['nullable', 'array'],
            'operating_days.*' => ['integer', 'between:0,6'],
            'is_active'        => ['sometimes', 'boolean'],
            'notes'            => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'departure_time.date_format' => 'Format jam keberangkatan harus HH:MM:SS, contoh: 13:00:00',
            'operating_days.*.between'   => 'Hari operasi harus antara 0 (Minggu) sampai 6 (Sabtu).',
        ];
    }
}
<?php

namespace App\Http\Requests\Series;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        $poId     = $this->user()->po_id;
        $seriesId = $this->route('series');

        return [
            'name'             => ['sometimes', 'string', 'max:100'],
            'code'             => [
                'nullable', 'string', 'max:50',
                Rule::unique('series')->ignore($seriesId)->where(fn($q) => $q->where('po_id', $poId)),
            ],
            'departure_time'   => ['sometimes', 'date_format:H:i:s'],
            'origin_city'      => ['sometimes', 'string', 'max:100'],
            'destination_city' => ['sometimes', 'string', 'max:100'],
            'operating_days'   => ['nullable', 'array'],
            'operating_days.*' => ['integer', 'between:0,6'],
            'is_active'        => ['sometimes', 'boolean'],
            'notes'            => ['nullable', 'string'],
        ];
    }
}
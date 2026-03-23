<?php

namespace App\Http\Requests\SeriesSchedule;

use Illuminate\Foundation\Http\FormRequest;

class BulkSeriesScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        return [
            'dates'     => ['required', 'array', 'min:1'],
            'dates.*'   => ['required', 'date_format:Y-m-d'],
            'is_active' => ['required', 'boolean'],
            'reason'    => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'dates.required'       => 'Minimal 1 tanggal harus dipilih.',
            'dates.*.date_format'  => 'Format tanggal harus YYYY-MM-DD.',
        ];
    }
}
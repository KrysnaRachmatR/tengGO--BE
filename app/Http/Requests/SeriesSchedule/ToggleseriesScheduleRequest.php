<?php

namespace App\Http\Requests\SeriesSchedule;

use Illuminate\Foundation\Http\FormRequest;

class ToggleSeriesScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
            'reason'    => ['nullable', 'string', 'max:200'],
        ];
    }
}
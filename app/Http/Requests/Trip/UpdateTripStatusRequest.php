<?php

namespace App\Http\Requests\Trip;

use App\Models\Trip;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTripStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo() || $this->user()->isOperasional();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                Trip::STATUS_RUNNING,
                Trip::STATUS_DONE,
                Trip::STATUS_CANCELLED,
            ])],
            // Wajib diisi kalau status = cancelled
            'cancellation_reason' => [
                Rule::requiredIf($this->status === Trip::STATUS_CANCELLED),
                'nullable',
                'string',
                'max:200',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'Alasan pembatalan wajib diisi.',
        ];
    }
}
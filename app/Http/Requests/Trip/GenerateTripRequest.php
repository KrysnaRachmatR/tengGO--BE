<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;

class GenerateTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        return [
            // Kalau hanya 1 tanggal: isi date saja
            // Kalau range: isi start_date & end_date
            'date'       => ['required_without:start_date', 'nullable', 'date_format:Y-m-d'],
            'start_date' => ['required_without:date', 'nullable', 'date_format:Y-m-d'],
            'end_date'   => [
                'required_with:start_date',
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
            'end_date.required_with'  => 'Tanggal akhir wajib diisi jika menggunakan range.',
            'date.required_without'   => 'Isi date untuk 1 tanggal, atau start_date & end_date untuk range.',
        ];
    }
}
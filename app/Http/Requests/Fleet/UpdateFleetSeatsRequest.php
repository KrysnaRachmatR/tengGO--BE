<?php

namespace App\Http\Requests\Fleet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFleetSeatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        return [
            // Array seats wajib ada dan tidak boleh kosong
            'seats'               => ['required', 'array', 'min:1'],
            'seats.*.type'        => ['required', 'string', 'max:30'],
            'seats.*.class_name'  => ['required', 'string', 'max:50'],
            'seats.*.total'       => ['required', 'integer', 'min:1'],
            'seats.*.price_base'  => ['nullable', 'integer', 'min:0'],
            'seats.*.seat_layout' => ['nullable', 'string'],
            'seats.*.sort_order'  => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $types = collect($this->seats)->pluck('type');

            // Tidak boleh ada duplikat type dalam 1 request
            if ($types->count() !== $types->unique()->count()) {
                $validator->errors()->add('seats', 'Setiap tipe kursi hanya boleh muncul satu kali.');
            }
        });
    }
}
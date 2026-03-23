<?php

namespace App\Http\Requests\Fleet;

use App\Models\FleetSeat;
use Illuminate\Foundation\Http\FormRequest;

class StoreFleetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:100'],
            'license_plate' => ['nullable', 'string', 'max:20'],
            'brand'         => ['nullable', 'string', 'max:50'],
            'model'         => ['nullable', 'string', 'max:50'],
            'year'          => ['nullable', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'facilities'    => ['nullable', 'array'],
            'facilities.*'  => ['string', 'max:50'],
            'is_active'     => ['sometimes', 'boolean'],
            'notes'         => ['nullable', 'string'],

            // Seat config — opsional saat create, bisa di-set belakangan
            'seats'               => ['nullable', 'array'],
            'seats.*.type'        => ['required_with:seats', 'string', 'max:30'],
            'seats.*.class_name'  => ['required_with:seats', 'string', 'max:50'],
            'seats.*.total'       => ['required_with:seats', 'integer', 'min:1'],
            'seats.*.price_base'  => ['nullable', 'integer', 'min:0'],
            'seats.*.seat_layout' => ['nullable', 'string'],
            'seats.*.sort_order'  => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'seats.*.type.required_with'       => 'Tipe kursi wajib diisi.',
            'seats.*.class_name.required_with'  => 'Nama kelas kursi wajib diisi.',
            'seats.*.total.required_with'       => 'Jumlah kursi wajib diisi.',
        ];
    }
}
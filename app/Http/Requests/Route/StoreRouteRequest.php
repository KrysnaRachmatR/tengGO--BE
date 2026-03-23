<?php

namespace App\Http\Requests\Route;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        $poId = $this->user()->po_id;

        return [
            'origin'      => [
                'required', 'string', 'max:100',
                // Tidak boleh duplikat arah yang sama dalam 1 PO
                Rule::unique('routes')->where(fn($q) => $q
                    ->where('po_id', $poId)
                    ->where('destination', $this->destination)
                ),
            ],
            'destination' => ['required', 'string', 'max:100'],
            'name'        => ['nullable', 'string', 'max:150'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'origin.unique' => 'Rute :input → ' . $this->destination . ' sudah ada.',
        ];
    }
}
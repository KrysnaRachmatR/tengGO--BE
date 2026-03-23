<?php

namespace App\Http\Requests\Route;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdminPo();
    }

    public function rules(): array
    {
        $poId    = $this->user()->po_id;
        $routeId = $this->route('route');

        return [
            'origin'      => [
                'sometimes', 'string', 'max:100',
                Rule::unique('routes')
                    ->ignore($routeId)
                    ->where(fn($q) => $q
                        ->where('po_id', $poId)
                        ->where('destination', $this->destination ?? $this->route('route'))
                    ),
            ],
            'destination' => ['sometimes', 'string', 'max:100'],
            'name'        => ['nullable', 'string', 'max:150'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
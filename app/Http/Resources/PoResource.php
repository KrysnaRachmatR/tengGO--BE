<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'slug'         => $this->slug,
            'code'         => $this->code,
            'phone'        => $this->phone,
            'email'        => $this->email,
            'address'      => $this->address,
            'logo_path'    => $this->logo_path,
            'is_active'    => $this->is_active,

            // Hanya muncul kalau withCount() dipanggil
            'users_count'  => $this->whenCounted('users'),
            'routes_count' => $this->whenCounted('routes'),
            'series_count' => $this->whenCounted('series'),
            'fleets_count' => $this->whenCounted('fleets'),
            'crews_count'  => $this->whenCounted('crews'),

            'created_at'   => $this->created_at?->toDateTimeString(),
            'updated_at'   => $this->updated_at?->toDateTimeString(),
        ];
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'code'             => $this->code,
            'departure_time'   => $this->departure_time,
            'origin_city'      => $this->origin_city,
            'destination_city' => $this->destination_city,
            'operating_days'   => $this->operating_days,
            'is_active'        => $this->is_active,
            'notes'            => $this->notes,
            'route'            => $this->whenLoaded('route', fn() => [
                'id'           => $this->route->id,
                'display_name' => $this->route->display_name,
            ]),
            'created_at'       => $this->created_at?->toDateTimeString(),
        ];
    }
}
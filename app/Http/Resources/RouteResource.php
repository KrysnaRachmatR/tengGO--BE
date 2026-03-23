<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'origin'       => $this->origin,
            'destination'  => $this->destination,
            'display_name' => $this->display_name,
            'is_active'    => $this->is_active,
            'series_count' => $this->whenCounted('series'),
            'series'       => SeriesResource::collection($this->whenLoaded('series')),
            'created_at'   => $this->created_at?->toDateTimeString(),
        ];
    }
}
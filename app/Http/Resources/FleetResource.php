<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FleetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'license_plate' => $this->license_plate,
            'brand'         => $this->brand,
            'model'         => $this->model,
            'year'          => $this->year,
            'total_seats'   => $this->total_seats,
            'facilities'    => $this->facilities ?? [],
            'is_active'     => $this->is_active,
            'notes'         => $this->notes,
            'seats'         => FleetSeatResource::collection($this->whenLoaded('seats')),
            'created_at'    => $this->created_at?->toDateTimeString(),
        ];
    }
}
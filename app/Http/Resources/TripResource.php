<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'trip_date'            => $this->trip_date?->toDateString(),
            'scheduled_departure'  => $this->scheduled_departure,
            'origin_city'          => $this->origin_city,
            'destination_city'     => $this->destination_city,
            'actual_departure'     => $this->actual_departure?->toDateTimeString(),
            'actual_arrival'       => $this->actual_arrival?->toDateTimeString(),
            'status'               => $this->status,
            'source'               => $this->source,
            'notes'                => $this->notes,
            'cancellation_reason'  => $this->cancellation_reason,

            'series' => $this->whenLoaded('series', fn() => [
                'id'   => $this->series->id,
                'name' => $this->series->name,
                'code' => $this->series->code,
            ]),

            'fleet' => $this->whenLoaded('fleet', fn() => [
                'id'          => $this->fleet->id,
                'name'        => $this->fleet->name,
                'total_seats' => $this->fleet->total_seats,
            ]),

            'crews'      => CrewResource::collection($this->whenLoaded('crews')),
            'created_by' => $this->whenLoaded('createdBy', fn() => [
                'id'   => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ]),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
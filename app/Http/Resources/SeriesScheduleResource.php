<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'series_id'  => $this->series_id,
            'date'       => $this->date?->toDateString(),
            'is_active'  => $this->is_active,
            'reason'     => $this->reason,
            'created_by' => $this->whenLoaded('createdBy', fn() => [
                'id'   => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
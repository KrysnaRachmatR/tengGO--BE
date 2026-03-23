<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FleetSeatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'class_name'     => $this->class_name,
            'total'          => $this->total,
            'price_base'     => $this->price_base,
            'seat_layout'    => $this->seat_layout,
            'parsed_layout'  => $this->parsed_layout,
            'sort_order'     => $this->sort_order,
        ];
    }
}
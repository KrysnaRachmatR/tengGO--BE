<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CrewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'nik'                 => $this->nik,
            'phone'               => $this->phone,
            'role'                => $this->role,
            'license_number'      => $this->license_number,
            'license_expiry'      => $this->license_expiry?->toDateString(),
            'is_license_expired'  => $this->isLicenseExpired(),
            'is_license_expiring' => $this->isLicenseExpiringSoon(),
            'is_active'           => $this->is_active,
            'notes'               => $this->notes,
            'created_at'          => $this->created_at?->toDateTimeString(),
        ];
    }
}
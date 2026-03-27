<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Crew extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'po_id',
        'name',
        'nik',
        'phone',
        'role',
        'license_number',
        'license_expiry',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'is_active'      => 'boolean',
    ];

    const ROLE_DRIVER    = 'driver';
    const ROLE_CO_DRIVER = 'co_driver';
    const ROLE_PRAMUGARARI = 'pramugara/ri';
    const ROLE_HELPER     = 'helper';

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function po(): BelongsTo
    {
        return $this->belongsTo(Po::class, 'po_id');
    }

    public function tripCrews(): HasMany
    {
        return $this->hasMany(TripCrew::class, 'crew_id');
    }

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'trip_crews', 'crew_id', 'trip_id')
                    ->withPivot(['role', 'is_primary', 'notes'])
                    ->withTimestamps();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isLicenseExpired(): bool
    {
        return $this->license_expiry && $this->license_expiry->isPast();
    }

    public function isLicenseExpiringSoon(int $days = 30): bool
    {
        return $this->license_expiry &&
               $this->license_expiry->isFuture() &&
               $this->license_expiry->diffInDays(now()) <= $days;
    }
}
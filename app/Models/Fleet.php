<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fleet extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'po_id',
        'name',
        'license_plate',
        'brand',
        'model',
        'year',
        'total_seats',
        'facilities',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'facilities'  => 'array',
        'is_active'   => 'boolean',
        'total_seats' => 'integer',
        'year'        => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function po(): BelongsTo
    {
        return $this->belongsTo(Po::class, 'po_id');
    }

    public function seats(): HasMany
    {
        return $this->hasMany(FleetSeat::class, 'fleet_id')->orderBy('sort_order');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'fleet_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    // Recalculate & update total_seats dari sum fleet_seats
    public function syncTotalSeats(): void
    {
        $this->update([
            'total_seats' => $this->seats()->sum('total'),
        ]);
    }

    public function hasFacility(string $facility): bool
    {
        return in_array($facility, $this->facilities ?? []);
    }
}
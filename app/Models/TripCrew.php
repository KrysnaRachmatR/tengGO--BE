<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripCrew extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'po_id',
        'trip_id',
        'crew_id',
        'role',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function po(): BelongsTo
    {
        return $this->belongsTo(Po::class, 'po_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class, 'crew_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
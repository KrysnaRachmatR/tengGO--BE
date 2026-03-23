<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FleetSeat extends Model
{
    // FleetSeat tidak pakai BelongsToTenant karena tidak punya po_id langsung.
    // Selalu diakses lewat relasi fleet yang sudah di-scope per tenant.

    protected $fillable = [
        'fleet_id',
        'type',
        'class_name',
        'total',
        'price_base',
        'seat_layout',
        'sort_order',
    ];

    protected $casts = [
        'total'      => 'integer',
        'price_base' => 'integer',
        'sort_order' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Tipe standar yang dikenali sistem
    // Admin PO bebas tambah tipe di luar daftar ini (custom)
    // -------------------------------------------------------------------------

    const TYPE_EXECUTIVE = 'executive';
    const TYPE_SLEEPER   = 'sleeper';
    const TYPE_QUEEN     = 'queen';

    const STANDARD_TYPES = [
        self::TYPE_EXECUTIVE,
        self::TYPE_SLEEPER,
        self::TYPE_QUEEN,
    ];

    // Label tampil default untuk tipe standar
    // Dipakai sebagai fallback kalau class_name tidak diisi
    const TYPE_LABELS = [
        self::TYPE_EXECUTIVE => 'Executive',
        self::TYPE_SLEEPER   => 'Sleeper',
        self::TYPE_QUEEN     => 'Queen',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class, 'fleet_id');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    // Nama tampil: pakai class_name kalau ada, fallback ke label standar, fallback ke type
    public function getDisplayNameAttribute(): string
    {
        return $this->class_name
            ?? self::TYPE_LABELS[$this->type]
            ?? ucfirst($this->type);
    }

    // Parse seat_layout string → array 2D per baris
    // Format input : "1A,1B,1C|2A,2B,2C"
    // Format output: [["1A","1B","1C"], ["2A","2B","2C"]]
    public function getParsedLayoutAttribute(): array
    {
        if (empty($this->seat_layout)) {
            return [];
        }

        return collect(explode('|', $this->seat_layout))
            ->map(fn($row) => explode(',', trim($row)))
            ->toArray();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isStandardType(): bool
    {
        return in_array($this->type, self::STANDARD_TYPES);
    }

    public function isCustomType(): bool
    {
        return ! $this->isStandardType();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeStandard($query)
    {
        return $query->whereIn('type', self::STANDARD_TYPES);
    }

    public function scopeCustom($query)
    {
        return $query->whereNotIn('type', self::STANDARD_TYPES);
    }
}

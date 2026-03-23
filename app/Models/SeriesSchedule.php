<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesSchedule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'po_id',
        'series_id',
        'date',
        'is_active',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'date'      => 'date',
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function po(): BelongsTo
    {
        return $this->belongsTo(Po::class, 'po_id');
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class, 'series_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeOnDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeInMonth($query, string $yearMonth)
    {
        // $yearMonth format: "2025-01"
        return $query->whereYear('date', substr($yearMonth, 0, 4))
                     ->whereMonth('date', substr($yearMonth, 5, 2));
    }
}
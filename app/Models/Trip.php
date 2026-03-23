<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'po_id',
        'series_id',
        'fleet_id',
        'trip_date',
        'scheduled_departure',
        'origin_city',
        'destination_city',
        'actual_departure',
        'actual_arrival',
        'status',
        'source',
        'notes',
        'cancellation_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'trip_date'          => 'date',
        'actual_departure'   => 'datetime',
        'actual_arrival'     => 'datetime',
    ];

    const STATUS_SCHEDULED  = 'scheduled';
    const STATUS_RUNNING    = 'running';
    const STATUS_DONE       = 'done';
    const STATUS_CANCELLED  = 'cancelled';

    const SOURCE_AUTO       = 'auto';
    const SOURCE_MANUAL     = 'manual';

    // Status yang boleh ditransisi dari status tertentu
    const STATUS_TRANSITIONS = [
        self::STATUS_SCHEDULED => [self::STATUS_RUNNING, self::STATUS_CANCELLED],
        self::STATUS_RUNNING   => [self::STATUS_DONE, self::STATUS_CANCELLED],
        self::STATUS_DONE      => [],
        self::STATUS_CANCELLED => [],
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

    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class, 'fleet_id');
    }

    public function tripCrews(): HasMany
    {
        return $this->hasMany(TripCrew::class, 'trip_id');
    }

    public function crews(): BelongsToMany
    {
        return $this->belongsToMany(Crew::class, 'trip_crews', 'trip_id', 'crew_id')
                    ->withPivot(['role', 'is_primary', 'notes'])
                    ->withTimestamps();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeOnDate($query, string $date)
    {
        return $query->where('trip_date', $date);
    }

    public function scopeStatus($query, string|array $status)
    {
        return $query->whereIn('status', (array) $status);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeRunning($query)
    {
        return $query->where('status', self::STATUS_RUNNING);
    }

    public function scopeToday($query)
    {
        return $query->where('trip_date', today()->toDateString());
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::STATUS_TRANSITIONS[$this->status] ?? []);
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isRunning(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Generate trip dari data series (dipanggil oleh TripGeneratorService)
    public static function fromSeries(Series $series, string $date): static
    {
        return new static([
            'po_id'               => $series->po_id,
            'series_id'           => $series->id,
            'trip_date'           => $date,
            'scheduled_departure' => $series->departure_time,
            'origin_city'         => $series->origin_city,
            'destination_city'    => $series->destination_city,
            'status'              => self::STATUS_SCHEDULED,
            'source'              => self::SOURCE_AUTO,
        ]);
    }
}
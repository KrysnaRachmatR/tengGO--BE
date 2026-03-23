<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'po_id',
        'route_id',
        'name',
        'code',
        'departure_time',
        'origin_city',
        'destination_city',
        'operating_days',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'operating_days' => 'array',   // [0,1,2,3,4,5,6] → 0=Minggu, 6=Sabtu
        'is_active'      => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function po(): BelongsTo
    {
        return $this->belongsTo(Po::class, 'po_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class, 'route_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SeriesSchedule::class, 'series_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'series_id');
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

    // Cek apakah seri beroperasi pada hari tertentu
    // Kalau operating_days null → berarti setiap hari
    public function operatesOn(Carbon $date): bool
    {
        if (empty($this->operating_days)) {
            return true;
        }

        return in_array($date->dayOfWeek, $this->operating_days);
    }

    // Cek apakah seri aktif pada tanggal tertentu
    // Urutan prioritas: series_schedules (override) → operating_days → is_active
    public function isActiveOn(Carbon $date): bool
    {
        if (! $this->is_active) {
            return false;
        }

        // Cek apakah ada override di series_schedules
        $schedule = $this->schedules()
                         ->where('date', $date->toDateString())
                         ->first();

        if ($schedule) {
            return $schedule->is_active;
        }

        // Tidak ada override → pakai aturan operating_days
        return $this->operatesOn($date);
    }
}
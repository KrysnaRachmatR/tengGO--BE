<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Po extends Model
{
    use SoftDeletes;

    protected $table = 'pos';

    protected $fillable = [
        'name',
        'slug',
        'code',
        'phone',
        'email',
        'address',
        'logo_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'po_id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class, 'po_id');
    }

    public function fleets(): HasMany
    {
        return $this->hasMany(Fleet::class, 'po_id');
    }

    public function crews(): HasMany
    {
        return $this->hasMany(Crew::class, 'po_id');
    }

    public function series(): HasMany
    {
        return $this->hasMany(Series::class, 'po_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'po_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
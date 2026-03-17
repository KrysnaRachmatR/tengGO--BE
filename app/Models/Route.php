<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'origin',
        'destination',
        'company_id'
    ];

    // 🔗 Relasi
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    // 🧠 Scope
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // 🔥 Helper (biar enak di frontend)
    public function getFullRouteAttribute()
    {
        return $this->origin . ' - ' . $this->destination;
    }

    public function series()
    {
        return $this->belongsToMany(Series::class, 'route_series')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }
}
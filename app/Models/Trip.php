<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = [
        'route_id',
        'series_id',
        'armada_id',
        'departure_date',
        'departure_time',
        'company_id'
    ];

    // 🔗 Relasi
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function armada()
    {
        return $this->belongsTo(Armada::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // 🧠 Scope
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // 🔥 Helper (biar frontend enak)
    public function getFullInfoAttribute()
    {
        return $this->series->name . ' | ' .
               $this->route->origin . ' - ' . $this->route->destination;
    }

}
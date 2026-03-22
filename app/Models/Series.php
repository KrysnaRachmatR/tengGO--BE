<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $fillable = [
        'company_id',
        'route_id',
        'name',
        'departure_time',
        'origin_point',
        'destination_point',
        'is_active'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
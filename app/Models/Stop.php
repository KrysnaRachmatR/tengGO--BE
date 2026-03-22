<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stop extends Model
{
    protected $fillable = [
        'name',
        'type',
        'address',
        'latitude',
        'longitude'
    ];

    public function tripStops()
    {
        return $this->hasMany(TripStop::class);
    }

    public function trips()
    {
        return $this->belongsToMany(Trip::class, 'trip_stops')
            ->withPivot(['order', 'estimated_time', 'type'])
            ->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(CrewAttendance::class);
    }

    public function mealServices()
    {
        return $this->hasMany(MealService::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crew extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'role',
        'phone',
        'status'
    ];

    // RELATION

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function tripCrews()
    {
        return $this->hasMany(TripCrew::class);
    }

    public function trips()
    {
        return $this->belongsToMany(Trip::class, 'trip_crews')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(CrewAttendance::class);
    }
}
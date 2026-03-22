<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrewAttendance extends Model
{
    protected $fillable = [
        'trip_id',
        'crew_id',
        'stop_id',
        'check_in_time',
        'latitude',
        'longitude',
        'status'
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }

    public function stop()
    {
        return $this->belongsTo(Stop::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripCrew extends Model
{
    protected $fillable = [
        'trip_id',
        'crew_id',
        'role'
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }
}
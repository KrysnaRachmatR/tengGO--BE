<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripStop extends Model
{
    protected $fillable = [
        'trip_id',
        'stop_id',
        'order',
        'estimated_time',
        'type'
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function stop()
    {
        return $this->belongsTo(Stop::class);
    }
}
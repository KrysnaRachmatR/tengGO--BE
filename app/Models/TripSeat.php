<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripSeat extends Model
{
    protected $fillable = [
        'trip_id',
        'seat_number',
        'seat_type',
        'price',
        'status',
        'locked_until'
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
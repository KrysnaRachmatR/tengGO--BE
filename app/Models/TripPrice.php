<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripPrice extends Model
{
    protected $fillable = [
        'trip_id',
        'seat_type_id',
        'price',
        'quota'
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function seatType()
    {
        return $this->belongsTo(SeatType::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'armada_id',
        'seat_type_id',
        'seat_number'
    ];

    public function armada()
    {
        return $this->belongsTo(Armada::class);
    }

    public function seatType()
    {
        return $this->belongsTo(SeatType::class);
    }

    public function prices()
    {
        return $this->hasMany(TripPrice::class);
    }
}
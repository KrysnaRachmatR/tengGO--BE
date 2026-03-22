<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description'
    ];

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function tripPrices()
    {
        return $this->hasMany(TripPrice::class);
    }
}
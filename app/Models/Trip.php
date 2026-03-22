<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = [
        'company_id',
        'schedule_id',
        'departure_time',
        'armada_id',
        'status'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function armada()
    {
        return $this->belongsTo(Armada::class);
    }

    public function prices()
    {
        return $this->hasMany(TripPrice::class);
    }

    public function seatTypes()
    {
        return $this->belongsToMany(SeatType::class, 'trip_prices')
            ->withPivot(['price', 'quota'])
            ->withTimestamps();
    }

    public function seats()
    {
        return $this->hasMany(TripSeat::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER (PENTING BANGET BUAT API)
    |--------------------------------------------------------------------------
    */

    // total quota dari semua seat type
    public function getTotalQuotaAttribute()
    {
        return $this->prices->sum('quota');
    }

    // sisa kursi (sementara = quota, nanti dikurangi booking)
    public function getAvailableSeatsAttribute()
    {
        return $this->prices->sum('quota');
    }
}
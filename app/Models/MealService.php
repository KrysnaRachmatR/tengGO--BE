<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealService extends Model
{
    protected $fillable = [
        'trip_id',
        'stop_id',
        'served_at',
        'notes'
    ];

    protected $casts = [
        'served_at' => 'datetime',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function stop()
    {
        return $this->belongsTo(Stop::class);
    }

    public function details()
    {
        return $this->hasMany(MealServiceDetail::class);
    }
}
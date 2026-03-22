<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'company_id',
        'series_id',
        'date',
        'is_open'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}